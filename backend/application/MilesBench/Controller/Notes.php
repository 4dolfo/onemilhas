<?php

namespace MilesBench\Controller;

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

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

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
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

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
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

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

	public function loadNotesCommun(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$Notes = $em->getRepository('Notes')->findBy(
				array(
					'status' => 'emissao',
					'businesspartner' => NULL
				)
			);

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

	public function createNoteCommun(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$Notes = new \Notes();
			$Notes->setReminder($dados['description']);
			$Notes->setDate(new \DateTime());
			$Notes->setStatus('emissao');
			$Notes->setBusinesspartner(NULL);

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

	public function removeNoteCommun(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$Notes = $em->getRepository('Notes')->findOneBy(
				array(
					'id' => $dados['id']
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
