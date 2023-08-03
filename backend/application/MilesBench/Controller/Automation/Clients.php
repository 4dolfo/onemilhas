<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

/**
* @author Arthur
*/
class Clients
{

	
	public function dailyReminder() {

		try{
			$em = Application::getInstance()->getEntityManager();
			$QueryBuilder = Application::getInstance()->getQueryBuilder();

			$sql = "SELECT b FROM Businesspartner b where b.partnerType = 'U_D' and b.status = 'Aprovado' ";
			$query = $em->createQuery($sql);
			$Dealers = $query->getResult();

			$weekAgo = new \DateTime();
			$weekAgo->modify('-1 week');

			$lastDay = new \DateTime();
			$lastDay->modify('-1 days');

			foreach ($Dealers as $dealer) {

				$clients = array();

				$sql = "SELECT * FROM businesspartner b where partner_type = 'C' and b.status = 'Aprovado' and id  in (select client_id from sale where issue_date >= '". $weekAgo->format('Y-m-d') ."') and b.dealer = '".$dealer->getId()."' ";
				/*$sql = "SELECT b.name as name, b.id as id, s.issue_date as issue_date FROM businesspartner b INNER JOIN sale s ON s.client_id = b.id  where partner_type = 'C' and b.dealer = '".$dealer->getId()."' and b.status = 'Aprovado' and s.issue_date >= '". $weekAgo->format('Y-m-d') ."'  ";*/

				$stmt = $QueryBuilder->query($sql);
				while ($row = $stmt->fetch()) {

					$valid = true;
					$sql = "SELECT * FROM system_log s where s.log_type = 'CLIENT-WARNING-7-DAYS' and s.description LIKE '%>CLIENT:" .$row['id']. "%' and s.issue_date >= '". $weekAgo->format('Y-m-d') ."' ";
					$stmt2 = $QueryBuilder->query($sql);
					while ($row2 = $stmt2->fetch()) {
						$valid = false;
					}
					
					if($valid) {
						$clients[] = $row;
					}
				}

				if(count($clients) > 0) {
					$data = '';
					foreach ($clients as $key => $value) {
						$data .= 'Nome: '.$value['name'].'<br>';
						//$data .= 'Nome: '.$value['name'].' - '.'Última emissão: '.$value['issue_date'].'<br>';
					}

					$email1 = 'adm@onemilhas.com.br';
					$email2 = 'financeiro@onemilhas.com.br';

					$postfields = array(
					'content' =>    "<br><br>Bom dia,<br><br>Lembramos que os seguintes clientes se encontram a mais de 7 dias sem emitir<br><br>".
						$data.
						"<br><br>Obrigado pela parceria!".
						"<br>Comercial",
						'partner' => $email2,
						'bcc' => $email1,
						'from' => $email1,
						'subject' => 'LEMBRETE EMISSÃO DE CLIENTES',
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

					$SystemLog = new \SystemLog();
					$SystemLog->setIssueDate(new \DateTime());
					$SystemLog->setDescription("->CLIENT:".$value['id']);
					$SystemLog->setLogType('CLIENT-WARNING-7-DAYS');
					$em->persist($SystemLog);
					$em->flush($SystemLog);
				}
			}

		} catch(\Exception $e){
			$email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR',
				'type' => ''
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);
			var_dump($e->getMessage());die;
		}
	}

}
