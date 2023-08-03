<?php

namespace MilesBench\Controller\Dealer;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Notes {

	public function loadNotes(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			if(isset($dados['businesspartner'])) {
				$Businesspartner = $dados['businesspartner'];
			}

			$Notes = $em->getRepository('Notes')->findBy(array('businesspartner' => $Businesspartner->getId()));

			$dataset = array();
			foreach($Notes as $note){
				$dataset[] = array(
					'id' => $note->getId(),
					'reminder' => $note->getReminder(),
					'date' => $note->getDate()->format('d-m-Y'),
					'status' => $note->getStatus()
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

	public function createNote(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['businesspartner'])) {
			$Businesspartner = $dados['businesspartner'];
		}
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$Notes = new \Notes();
			$Notes->setReminder($dados['description']);
			$Notes->setDate(new \DateTime());
			$Notes->setStatus('Aberta');
			$Notes->setBusinesspartner($Businesspartner);

			$em->persist($Notes);
            $em->flush($Notes);

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

	public function removeNote(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['businesspartner'])) {
			$Businesspartner = $dados['businesspartner'];
		}
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$Notes = $em->getRepository('Notes')->findOneBy(
				array(
					'id' => $dados['id'],
					'businesspartner' => $Businesspartner->getId()
				)
			);

			if($Notes) {
				$em->remove($Notes);
            	$em->flush($Notes);
			}

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
