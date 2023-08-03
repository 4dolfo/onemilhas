<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

/**
* @author Arthur
*/
class Marketing
{

	private $BELPrice;
	private $BPSPrice;
	private $BSBPrice;
	private $CGRPrice;
	private $RIOPrice;
	private $FLNPrice;
	private $RECPrice;
	private $UDIPrice;

	public function dailyMarketingPricing() {

		$date = (new \DateTime())->modify('+5 day')->format('d/m/Y');
		$this->BELPrice = self::searchDestiny('BEL', $date);
		$this->BPSPrice = self::searchDestiny('BPS', $date);
		$this->BSBPrice = self::searchDestiny('BSB', $date);
		$this->CGRPrice = self::searchDestiny('CGR', $date);
		$this->RIOPrice = self::searchDestiny('RIO', $date);
		$this->FLNPrice = self::searchDestiny('FLN', $date);
		$this->RECPrice = self::searchDestiny('REC', $date);
		$this->UDIPrice = self::searchDestiny('UDI', $date);

		self::sendEmail();
	}

	// function to Search
	public function searchDestiny($destiny, $date) {
		try{

			$lowestAzul = self::searchAzul($destiny, $date);
			$lowestLatam = self::searchLatam($destiny, $date);

			$lowestPrice = 0;
			if($lowestAzul <= $lowestLatam && $lowestAzul != 0) {
				$lowestPrice = $lowestAzul;
			} else if($lowestLatam != 0) {
				$lowestPrice = $lowestLatam;
			}

			return $lowestPrice;

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

	// search Latam
	public function searchLatam($destiny, $date) {
		$requestAPI = array(
			'companhias_aereas' => array('latam'),
			'data' => array(
				'trip' => 0,
				'from' => array(
					'iataCode' => 'BHZ',
					'international' => '0'
				),
				'to' =>  array(
					'iataCode' => $destiny,
					'international' => '0'
				),
				'departureDate' => $date,
				'adults' => 1,
				'children' => 0,
				'babies' => 0
			)
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://flights.srm.systems:4000/api/crawler/find');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestAPI));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$result = curl_exec($ch);

		$returnAPI = json_decode($result, true);

		$lowestPrice = 0;
		if(isset($returnAPI['results']['Trechos']) && count($returnAPI['results']['Trechos']) > 0) {

			foreach ($returnAPI['results']['Trechos'] as $key => $value) {

				// get lowest price to email
				// $lowestPrice = self::returnLowestPriceFlights($value['Voos']);
				$lowestPrice = self::returnLowestPriceWeek($value['Semana']);
			}
		}
		return $lowestPrice;
	}

	// search Azul
	public function searchAzul($destiny, $date) {
		$requestAPI = array(
			'companhias_aereas' => array('azul'),
			'data' => array(
				'trip' => 0,
				'from' => array(
					'iataCode' => 'BHZ',
					'international' => '0'
				),
				'to' =>  array(
					'iataCode' => $destiny,
					'international' => '0'
				),
				'departureDate' => $date,
				'adults' => 1,
				'children' => 0,
				'babies' => 0
			)
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://flights.srm.systems:4000/api/crawler/find');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestAPI));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$result = curl_exec($ch);

		$returnAPI = json_decode($result, true);

		$lowestPrice = 0;
		if(isset($returnAPI['results']['Trechos']) && count($returnAPI['results']['Trechos']) > 0) {

			foreach ($returnAPI['results']['Trechos'] as $key => $value) {

				// get lowest price to email
				// $lowestPrice = self::returnLowestPriceFlights($value['Voos']);
				$lowestPrice = self::returnLowestPriceWeek($value['Semana']);
			}
		}
		return $lowestPrice;
	}

	// lowest price week
	public function returnLowestPriceWeek($array) {
		$lowest = '';
		foreach ($array as $key => $data) {
			if(isset($data[0]['Preco'])) {
				if($data[0]['Preco'] != 0 && ( $data[0]['Preco'] < $lowest || $lowest == '') ) {
					$lowest = $data[0]['Preco'];
				}
			}
		}
		return $lowest;
	}

	// function to get lowest price from arrays
	public function returnLowestPriceFlights($array) {
		$lowest = '';
		foreach ($array as $data) {
			if(isset($data['Milhas']) && count($data['Milhas']) > 0) {
				$lowestArray = self::returnLowestPrice($data['Milhas']);
				if($lowestArray != 0 && ( $lowestArray < $lowest || $lowest == '') ) {
					$lowest = $lowestArray;
				}
			}
		}
		return $lowest;
	}

	// function to get lowest price
	public function returnLowestPrice($array) {
		$lowest = '';
		foreach ($array as $data) {
			if(isset($data['PrecoAdulto'])) {
				if($data['PrecoAdulto'] != 0 && ( $data['PrecoAdulto'] < $lowest || $lowest == '') ) {
					$lowest = $data['PrecoAdulto'];
				}
			}
		}
		return $lowest;
	}

	// function to send email
	public function sendEmail() {

		try{

			$em = Application::getInstance()->getEntityManagerVoeLegal();

			$AppBestPrices = new \AppBestPrices();
			$AppBestPrices->setBel((float)$this->BELPrice);
			$AppBestPrices->setBps((float)$this->BPSPrice);
			$AppBestPrices->setBsb((float)$this->BSBPrice);
			$AppBestPrices->setCgr((float)$this->CGRPrice);
			$AppBestPrices->setRio((float)$this->RIOPrice);
			$AppBestPrices->setFln((float)$this->FLNPrice);
			$AppBestPrices->setRec((float)$this->RECPrice);
			$AppBestPrices->setUdi((float)$this->UDIPrice);
			$AppBestPrices->setDate(new \DateTime());
			$em->persist($AppBestPrices);
			$em->flush($AppBestPrices);

			// create the new version
			copy('./MilesBench/Controller/Marketing/VoeLegal/HTML_PADRAO.html', './MilesBench/Controller/Marketing/VoeLegal/versions/HTML_PADRAO_'.(new \DateTime())->format('Y-m-d').'.html');

			// getting new file
			$path_to_file = './MilesBench/Controller/Marketing/VoeLegal/versions/HTML_PADRAO_'.(new \DateTime())->format('Y-m-d').'.html';
			$content = file_get_contents($path_to_file);

			// replace VALUE in content
			$content = str_replace("REPLACEVALUEBEL", number_format((float)$this->BELPrice, 2, ',', '.'), $content);
			$content = str_replace("REPLACEVALUEBPS", number_format((float)$this->BPSPrice, 2, ',', '.'), $content);
			$content = str_replace("REPLACEVALUEBSB", number_format((float)$this->BSBPrice, 2, ',', '.'), $content);
			$content = str_replace("REPLACEVALUECGR", number_format((float)$this->CGRPrice, 2, ',', '.'), $content);
			$content = str_replace("REPLACEVALUERIO", number_format((float)$this->RIOPrice, 2, ',', '.'), $content);
			$content = str_replace("REPLACEVALUEFLN", number_format((float)$this->FLNPrice, 2, ',', '.'), $content);
			$content = str_replace("REPLACEVALUEREC", number_format((float)$this->RECPrice, 2, ',', '.'), $content);
			$content = str_replace("REPLACEVALUEUDI", number_format((float)$this->UDIPrice, 2, ',', '.'), $content);

			// saving file
			file_put_contents($path_to_file, $content);

			// getting email
			$content = self::requireToVar('./MilesBench/Controller/Marketing/VoeLegal/versions/HTML_PADRAO_'.(new \DateTime())->format('Y-m-d').'.html');
			//$email1 = 'emissao@onemilhas.com.br';
            //$email2 = 'adm@onemilhas.com.br';
			$email1 = '';
			$email2 = '';
			$postfields = array(
				'content' => $content,
				'from' => $email1,
				'partner' => $email2,
				'subject' => 'PROMOÇÃO DA SEMANA - VOE LEGAL',
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

	public function requireToVar($file){
		ob_start();
		require($file);
		return ob_get_clean();
	}

}
