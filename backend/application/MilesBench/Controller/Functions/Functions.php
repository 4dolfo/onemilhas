<?php

namespace MilesBench\Controller\Functions;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Functions {

	public function getCep(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])){
			$dados = $dados['data'];
		}

		$zipCode = $dados['zipCode'];
		if(strpos($dados['zipCode'], "-")) {
			$zipCode = explode('-', $dados['zipCode']);
			
		}

		$responseClient = array(
			'status' => 'error',
			'data' => array()
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://viacep.com.br/ws/'. $zipCode .'/json/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		$responseCurl = json_decode($result, true);
		if(isset($responseCurl['erro'])) {
			$responseClient['status'] = 'error';
		} else {
			$responseClient['status'] = 'success';
			$responseClient['data'] = $responseCurl;
		}
		$response->setDataset($responseClient);
	}

}