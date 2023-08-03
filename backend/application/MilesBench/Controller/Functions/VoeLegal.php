<?php

namespace MilesBench\Controller\Functions;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class VoeLegal {

	public function savePortoTable(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])){
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManagerVoeLegal();

		foreach ($dados as $data) {
			try {
				$AppDiscountCoupons = new \AppDiscountCoupons();
				$AppDiscountCoupons->setValue($data);
				$AppDiscountCoupons->setCreateTime(new \DateTime());

				$em->persist($AppDiscountCoupons);
				$em->flush($AppDiscountCoupons);
			} catch (\Exception $e) {
			}
		}

		$message = new \MilesBench\Message();
		$message->setType(\MilesBench\Message::SUCCESS);
		$message->setText('Dados salvo com sucesso');
		$response->addMessage($message);
	}
}