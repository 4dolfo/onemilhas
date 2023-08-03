<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Maintenance {

	public function loadEmails(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}

		$em = Application::getInstance()->getEntityManager();
		$dataset = array();

		$EmailsConfig = $em->getRepository('EmailsConfig')->findAll();
		foreach($EmailsConfig as $email){
			$dataset[] = array(
				'id' => $email->getId(),
				'email' => $email->getEmail(),
				'password' => $email->getPassword()
			);
		}
		$response->setDataset($dataset);
	}

	public function saveEmail(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			if(isset($dados['id']) && $dados['id'] != '') {
				$EmailsConfig = $em->getRepository('EmailsConfig')->findOneBy(array('id' => $dados['id']));
			} else {
				$EmailsConfig = new \EmailsConfig();
			}

			$EmailsConfig->setEmail($dados['email']);
			$EmailsConfig->setPassword($dados['password']);

			$em->persist($EmailsConfig);
            $em->flush($EmailsConfig);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

		} catch(\Exception $e){
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}
}
