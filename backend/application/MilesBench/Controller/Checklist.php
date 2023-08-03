<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Checklist {

	public function loadNotes(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['businesspartner'])) {
			$BusinessPartner = $dados['businesspartner'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			$UserChecklist = $em->getRepository('UserChecklist')->findBy(array('businesspartner' => $BusinessPartner->getId()));

			$dataset = array();
			foreach($UserChecklist as $checklist){
				$checkDate = '';
				if($checklist->getCheckDate()) {
					$checkDate = $checklist->getCheckDate()->format('Y-m-d H:i:s');
				}

				$dataset[] = array(
					'id' => $checklist->getId(),
					'task' => $checklist->getTask(),
					'done' => $checklist->getDone() == 'true',
					'issueDate' => $checklist->getIssueDate()->format('Y-m-d H:i:s'),
					'checkDate' => $checkDate
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
			$BusinessPartner = $dados['businesspartner'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			if(isset($dados['id'])) {
				$UserChecklist = $em->getRepository('UserChecklist')->findOneBy(
					array(
						'id' => $dados['id'],
						'businesspartner' => $BusinessPartner->getId()
					)
				);
			} else {
				$UserChecklist = new \UserChecklist();
				$UserChecklist->setBusinesspartner($BusinessPartner);
			}

			$UserChecklist->setTask($dados['task']);
			$UserChecklist->setDone($dados['done']);
			$UserChecklist->setIssueDate(new \DateTime());

			if($dados['done'] == true) {
				$UserChecklist->setCheckDate(new \DateTime());
			}


			$em->persist($UserChecklist);
			$em->flush($UserChecklist);

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

	public function checkNote(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['businesspartner'])) {
			$BusinessPartner = $dados['businesspartner'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			if(isset($dados['id'])) {
				$UserChecklist = $em->getRepository('UserChecklist')->findOneBy(
					array(
						'id' => $dados['id'],
						'businesspartner' => $BusinessPartner->getId()
					)
				);
				$UserChecklist->setDone($dados['done']);
				if($dados['done'] == true) {
					$UserChecklist->setCheckDate(new \DateTime());
				}

				$em->persist($UserChecklist);
				$em->flush($UserChecklist);
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

	public function removeNote(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['businesspartner'])) {
			$BusinessPartner = $dados['businesspartner'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			$UserChecklist = $em->getRepository('UserChecklist')->findOneBy(
				array(
					'id' => $dados['id'],
					'businesspartner' => $BusinessPartner->getId()
				)
			);

			if($UserChecklist) {
				$em->remove($UserChecklist);
				$em->flush($UserChecklist);
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
