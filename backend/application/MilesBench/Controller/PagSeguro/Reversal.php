<?php

namespace MilesBench\Controller\PagSeguro;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Reversal {

	public function loadTransactions(Request $request, Response $response) {

		$em = Application::getInstance()->getEntityManagerVoeLegal();
		$AppTransactions = $em->getRepository('AppTransactions')->findAll();

		$dataset = array();
		foreach($AppTransactions as $item){

			$order = '';
			if($item->getOrder()) {
				$order = $item->getOrder()->getId();
			}
			$issueDate = '';
			if($item->getIssueDate()) {
				$issueDate = $item->getIssueDate()->format('Y-m-d H:i:s');
			}
			$refundDate = '';
			if($item->getRefundDate()) {
				$refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
			}

			$dataset[] = array(
				'id' => $item->getId(),
				'code' => $item->getCode(),
				'value' => (float)$item->getValue(),
				'status' => $item->getStatus(),
				'businesspartner' => $item->getBusinesspartner()->getName(),
				'order' => $order,
				'refundValue' => (float)$item->getRefundValue(),
				'issueDate' => $issueDate,
				'refundDate' => $refundDate
			);
		}
		$response->setDataset($dataset);
	}

	public function generateRefund(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$post_string = array(
				'transactionCode' => $dados['code'],
				'token' => 'F5A01DE233CD4EE980FFCA183B99E680',
				'email' => 'pagseguro@voelegal.com.br',
				'refundValue' => number_format($dados['refundValue'], 2, '.', ''),
				'hashId' => '79fbe9b577f0a4e31bae4753449dfd4c'
			);

			$ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, 'http://localhost/pagamento-idealMilhas/backend/application/index.php?rota=/App/generateRefund');
			curl_setopt($ch, CURLOPT_URL, 'http://payment.srm.systems/pagamento-idealMilhas/backend/application/index.php?rota=/App/generateRefund');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
			$result = curl_exec($ch);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($result));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
			$emailResult = curl_exec($ch);

			$responsePagSeguro = json_decode($result, true);
			if(strrpos($responsePagSeguro['dataset']['response'], 'OK')) {

				$em = Application::getInstance()->getEntityManagerVoeLegal();
				$AppTransactions = $em->getRepository('AppTransactions')->findOneBy(array('id' => $dados['id']));

				$AppTransactions->setRefundValue($dados['refundValue']);
				$AppTransactions->setRefundDate(new \DateTime());
				$AppTransactions->setStatus('Reembolsado');

				$em->persist($AppTransactions);
				$em->flush($AppTransactions);

				$message = new \MilesBench\Message();
				$message->setType(\MilesBench\Message::SUCCESS);
				$message->setText('Pedido solicitado com sucesso!');
				$response->addMessage($message);
			} else {
				$message = new \MilesBench\Message();
				$message->setType(\MilesBench\Message::ERROR);
				$message->setText('error');
				$response->addMessage($message);
			}
		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function generateCancel(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$post_string = array(
				'transactionCode' => $dados['code'],
				'token' => 'F5A01DE233CD4EE980FFCA183B99E680',
				'email' => 'pagseguro@voelegal.com.br',
				'hashId' => '79fbe9b577f0a4e31bae4753449dfd4c'
			);

			$ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, 'http://localhost/pagamento-idealMilhas/backend/application/index.php?rota=/App/generateCancel');
			curl_setopt($ch, CURLOPT_URL, 'http://payment.srm.systems/pagamento-idealMilhas/backend/application/index.php?rota=/App/generateCancel');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
			$result = curl_exec($ch);

			$responsePagSeguro = json_decode($result, true);
			if(strrpos($responsePagSeguro['dataset']['response'], 'OK')) {

				$em = Application::getInstance()->getEntityManagerVoeLegal();
				$AppTransactions = $em->getRepository('AppTransactions')->findOneBy(array('id' => $dados['id']));

				$AppTransactions->setRefundValue($dados['refundValue']);
				$AppTransactions->setRefundDate(new \DateTime());
				$AppTransactions->setStatus('Cancelada');

				$em->persist($AppTransactions);
				$em->flush($AppTransactions);

				$message = new \MilesBench\Message();
				$message->setType(\MilesBench\Message::SUCCESS);
				$message->setText('Pedido solicitado com sucesso!');
				$response->addMessage($message);
			} else {
				$message = new \MilesBench\Message();
				$message->setType(\MilesBench\Message::ERROR);
				$message->setText('error');
				$response->addMessage($message);
			}
		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}
}