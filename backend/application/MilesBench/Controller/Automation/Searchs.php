<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Searchs
{
	public function dailySearchs() {

		try{
			$em = Application::getInstance()->getQueryBuilderVoeLegal();
			$departures = '';
			$arrivals = '';

			$sql = "SELECT COUNT(*) as quant, search_from FROM app_searchs where issue_date >= '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' GROUP by search_from ORDER by quant desc LIMIT 0 , 20";
			$stmt = $em->query($sql);

			while ($row = $stmt->fetch()) {
				$departures = $departures."<br>De:".$row['search_from']."  - Quant:".$row['quant'];
			}

			$sql = "SELECT COUNT(*) as quant, search_to FROM app_searchs where issue_date >= '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' GROUP by search_to ORDER by quant desc LIMIT 0 , 20";
			$stmt = $em->query($sql);

			while ($row = $stmt->fetch()) {
				$arrivals = $arrivals."<br>Para:".$row['search_to']."  - Quant:".$row['quant'];
			}

			//$email1 = 'emissao@onemilhas.com.br';
            //$email2 = 'adm@onemilhas.com.br';
			$email1 = '';
			$email2 = '';
			$postfields = array(
				'content' =>    "Partidas:".$departures."<br><br>Destinos".$arrivals."<br><br>SRM-IT",
				'from' => $email1,
				'partner' => $email2,
				'subject' => 'Melhores Destinos',
				'type' => ''
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);

		} catch(\Exception $e){
			//$email1 = 'emissao@onemilhas.com.br';
            //$email2 = 'adm@onemilhas.com.br';
			$email1 = '';
			$email2 = '';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'from' => $email1,
				'partner' => $email2,
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
		}
	}
}
