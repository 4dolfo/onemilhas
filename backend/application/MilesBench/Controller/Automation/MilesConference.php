<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class MilesConference
{
	public function checkMilesToConference() {

		try {

			$em = Application::getInstance()->getEntityManager();
			$days = 1;
			if((new \DateTime())->format('l') == "Monday") {
				$days = 2;
			}

			$geraCheckin = true;
			$sql = "select DISTINCT(c.id) as cards_id from Sale s JOIN s.cards c where s.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d').' 00:00:00'."' and s.issueDate <= '".(new \DateTime())->format('Y-m-d').' 00:00:00'."' ";
			$query = $em->createQuery($sql);
			$CardsConference = $query->getResult();

			foreach($CardsConference as $card){

				$sql = "select m from MilesConference m where m.issueDate < '".(new \DateTime())->format('Y-m-d').' 00:00:00'."' and m.cards = '".$card['cards_id']."' and m.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d').' 00:00:00'."' ";
				$query = $em->createQuery($sql);
				$MilesConference = $query->getResult();
				if(count($MilesConference) == 0) {

					$Cards = $em->getRepository('Cards')->findOneBy(array('id' => $card['cards_id']));

					$MilesConference = new \MilesConference();
					$MilesConference->setIssueDate((new \DateTime())->modify('-1 day'));
					$MilesConference->setChecked('false');
					$MilesConference->setCards($Cards);

					$em->persist($MilesConference);
					$em->flush($MilesConference);

				} else {
					$geraCheckin = false;
				}
			}

			if($geraCheckin) {
				//$future = new \MilesBench\Controller\FutureBoardings;
				//$req = new \MilesBench\Request\Request();
				//$resp = new \MilesBench\Request\Response();
				//$req->setRow(array());
				//$future->checkAvianca($req, $resp);
				//$future->checkGol($req, $resp);
				//$future->checkLatam($req, $resp);
				//$future->checkAzul($req, $resp);
			}

		} catch(\Exception $e) {
			$email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'from' => $email1,
				'partner' => $email2,
				'subject' => 'ERROR - AUTOMATION - CONFERENCE',
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

	public function checkMilesToConference2Days(Request $request, Response $response) {
		try {

			$em = Application::getInstance()->getEntityManager();
			$days = 3;

			$sql = "select DISTINCT(c.id) as cards_id from Sale s JOIN s.cards c where s.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d').' 00:00:00'."' and s.issueDate <= '".(new \DateTime())->format('Y-m-d').' 00:00:00'."' ";
			$query = $em->createQuery($sql);
			$CardsConference = $query->getResult();

			foreach($CardsConference as $card){
				$Cards = $em->getRepository('Cards')->findOneBy(array('id' => $card['cards_id']));

				$daysCard = 3;
				while ($daysCard > 0) {
					$sql = "select m from MilesConference m where m.issueDate < '".(new \DateTime())->modify('-'.$daysCard.' day')->format('Y-m-d') .
						' 00:00:00'."' and m.cards = '".$card['cards_id'] .
						"' and m.issueDate >= '".(new \DateTime())->modify('-'. ($daysCard - 1 ).' day')->format('Y-m-d').' 00:00:00'."' ";
					$query = $em->createQuery($sql);
					$MilesConference = $query->getResult();

					if(count($MilesConference) == 0) {
						$MilesConference = new \MilesConference();
						$MilesConference->setIssueDate((new \DateTime())->modify('-'.$daysCard.' day'));
						$MilesConference->setChecked('false');
						$MilesConference->setCards($Cards);

						$em->persist($MilesConference);
						$em->flush($MilesConference);
					}
					$daysCard--;
				}
			}

		} catch(\Exception $e) {
			$email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'from' => $email1,
				'partner' => $email2,
				'subject' => 'ERROR - AUTOMATION - CONFERENCE',
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
