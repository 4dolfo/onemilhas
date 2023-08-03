<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class MilesSRMAzul {

	public function sendLogs() {
		try {
            $QueryBuilder = Application::getInstance()->getQueryBuilder();
            $em = Application::getInstance()->getEntityManager();
            
            $content = "";
            $content .= "<br>Segue atualização mensal dos dados do cartão SRM Azul: <br><br>";

            $AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findAll();
            foreach ($AzulFlightCategory as $category) {
                $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
                    array('flightCategory' => $category->getId())
                );
    
                $month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
                $CardSRM = $em->getRepository('Cards')->findOneBy( array( 'id' => 212359 ) );
                $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to " .
                    " where s.cards_id = '" . $CardSRM->getId() . "' and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) > 21 ";
    
                $FlightPathCategory = $em->getRepository('FlightPathCategory')->findBy(array( 'flightCategory' => $category->getId() ));
                if( count($FlightPathCategory) > 0 ) {
                    $sql .= " and ( CONCAT(f.code, t.code) in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
                } else {
                    $sql .= " and ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
                }
    
                $stmt = $QueryBuilder->query($sql);
                while ($row = $stmt->fetch()) {
                    $milesUsed21Days = (float)$row['milesUsed'];
                }

                $content .= "<strong>" . $category->getName() . "</strong> - Vendas 21 > dias: " . number_format($milesUsed21Days, 0, ',', '.') . "<br>";
                $content .= "<table style='width: 90%; text-align: center;' class='table table-bordered table-striped table-responsive'><thead>" .
                    "<tr><th>Dias</th><th>Porcentagem</th><th>A liberar</th><th>Usado</th></tr></thead><tbody>";
                    
                    foreach ($MilesbenchCategory as $key => $value) {
                        
                        $content .= "<tr><td>" . (int)$value->getDays() . "</td>" .
                            "<td>" . (float)$value->getPercentage() . "</td>" .
                            "<td>" . number_format((float)$value->getToFree(), 0, ',', '.') . "</td>" .
                            "<td>" . number_format((float)$value->getUsed(), 0, ',', '.') . "</td></tr>";
                    }
                    $content .= "</tbody></table><br><br>";
                }

                $email1 = 'adm@onemilhas.com.br';
                $email2 = 'adm@onemilhas.com.br';
                $postfields = array(
					'content' => $content,
					'partner' => $email2,
					'from' => $email1,
					'subject' => 'Atualização utilização SRM AZUL',
					'type' => ''
				);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result = curl_exec($ch);
				sleep(1);

		} catch(\Exception $e) {
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$postfields = array(
                'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
                'from' => $email1,
				'partner' => $email2,
				'subject' => 'ERROR - AUTOMATION - MilesSRMAzul - sendLogs',
				'type' => ''
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);
		}
    }
    
    public function cleanTables() {
        $em = Application::getInstance()->getEntityManager();
        try {

            $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findAll();
            foreach ($MilesbenchCategory as $key => $value) {
                $value->setToFree(0);
                $value->setUsed(0);
                $value->setOriginalToFree(0);
                $em->persist($value);
                $em->flush($value);
            }

        } catch(\Exception $e) {
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$postfields = array(
                'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
                'from' => $email1,
				'partner' => $email2,
				'subject' => 'ERROR - AUTOMATION - MilesSRMAzul - cleanTables',
				'type' => ''
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);
		}
    }
}
