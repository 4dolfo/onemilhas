<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

/**
* @author Arthur
*/
class Reservations {

	public function automatedFlightConference() {

		try{

			$em = Application::getInstance()->getEntityManager();

			/*
			$sql = "select s FROM Sale s ".
				" WHERE ((s.boardingDate BETWEEN '".(new \DateTime())->format('Y-m-d')."' AND '".(new \DateTime())->modify('+3 day')->format('Y-m-d')."' and s.airline <> '2') or (s.boardingDate BETWEEN '".(new \DateTime())->format('Y-m-d')."' AND '".(new \DateTime())->modify('+7 day')->format('Y-m-d')."' and s.airline = '2') ) and s.status not in ('Cancelamento Solicitado', 'Cancelamento Efetivado', 'Reembolso Solicitado', 'Reembolso Confirmado', 'Reembolso No-show Solicitado', 'Reembolso No-show Confirmado', 'Reembolso Nao Solicitado', 'Reembolso CIA') ORDER BY s.airline, s.id";
			*/

			// azul only
			$sql = "select s FROM Sale s ".
			" WHERE (s.boardingDate BETWEEN '".(new \DateTime())->format('Y-m-d')."' AND '".(new \DateTime())->modify('+3 day')->format('Y-m-d')."' and s.airline <> '2' ) and s.airline = '3' and s.status not in ('Cancelamento Solicitado', 'Cancelamento Efetivado', 'Reembolso Solicitado', 'Reembolso Confirmado', 'Reembolso No-show Solicitado', 'Reembolso No-show Confirmado', 'Reembolso Nao Solicitado', 'Reembolso Perdido', 'Reembolso CIA') ORDER BY s.airline, s.id";

			$query = $em->createQuery($sql);
			$order = $query->getResult();

			$toValidate = '';
			$airline = '';

			$dataset = array();
			foreach($order as $item){

				$from = '';
				if($item->getAirportFrom()){
					$from = $item->getAirportFrom()->getCode();
				}

				$to = '';
				if($item->getAirportTo()){
					$to = $item->getAirportTo()->getCode();
				}

				$jsonFlight = array(
					'airline' => $item->getAirline()->getName(),
					'pax_name' => $item->getPax()->getName(),
					'flight_locator' => $item->getFlightLocator(),
					'from' => $from,
					'to' => $to,
					'tax' => (float)$item->getTax(),
					'miles' => (int)$item->getMilesUsed(),
					'du' => (float)$item->getDuTax(),
					'departure' => $item->getBoardingDate()->format('d/m/Y'),
					'departureTime' => $item->getBoardingDate()->format('H:i'),
					'landing' => $item->getLandingDate()->format('d/m/Y')
				);

				if($jsonFlight['airline'] != $airline) {
					$airline = $jsonFlight['airline'];
					$toValidate = $toValidate.'<br>CIA: '.$jsonFlight['airline'].'<br>';
				}

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::consulta_reservas);
				// curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/api/reservation/check');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($jsonFlight));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result = curl_exec($ch);

				$responseAPI = json_decode($result, true);
				if( $responseAPI['status'] == false || (isset($responseAPI['text']) && $responseAPI['text'] != '' && $responseAPI['text'] != ' ' )) {
					$toValidate = $toValidate.'<br>LOC: '.$jsonFlight['flight_locator'].' PAX: '.$jsonFlight['pax_name'].' Status: '.$responseAPI['status'].' ----- '.$responseAPI['text'];
				}

				sleep(1);
			}
			$email1 = 'emissao@onemilhas.com.br';
            	$email2 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' => $toValidate,
				'from' => $email1,
				'partner' => $email2,
				'subject' => 'CONFERENCIA DE EMBARQUE',
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
			$email1 = 'emissao@onemilhas.com.br';
            	$email2 = 'adm@onemilhas.com.br';
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
