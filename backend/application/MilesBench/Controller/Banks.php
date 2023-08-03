<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Banks {

	public function load(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();
			$Banks = $em->getRepository('Banks')->findAll();

			$dataset = array();
			foreach($Banks as $bank){
				$dataset[] = array(
					'id' => $bank->getId(),
					'name' => $bank->getName(),
					'bank' => $bank->getBank(),
                    'agency' => $bank->getAgency(),
                    'account' => $bank->getAccount()
				);
			}
			$response->setDataset($dataset);
		} catch(\Exception $e){
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function save(Request $request, Response $response) {
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
                $Banks = $em->getRepository('Banks')->find($dados['id']);
            } else {
                $Banks = new \Banks();
            }

			$Banks->setName($dados['name']);
			if(isset($dados['bank']) && $dados['bank'] != '') {
				$Banks->setBank($dados['bank']);
			}
			if(isset($dados['agency']) && $dados['agency'] != '') {
				$Banks->setAgency($dados['agency']);
			}
			if(isset($dados['account']) && $dados['account'] != '') {
				$Banks->setAccount($dados['account']);
			}

			$em->persist($Banks);
			$em->flush($Banks);

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
