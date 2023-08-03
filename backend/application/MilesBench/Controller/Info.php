<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Info {

	public function loadInfo(Request $request, Response $response) {
		$dados = $request->getRow();

		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();
			$QueryBuilder = Application::getInstance()->getQueryBuilder();

			if(isset($dados['businesspartner'])) {
				$Businesspartner = $dados['businesspartner'];
			}

			//$Info = $em->getRepository('Info')->findBy(array('businesspartner' => $Businesspartner->getId()));
			$Info = $em->getRepository('Info')->findAll();
			
			$dataset = array();
			foreach($Info as $info){
				$datasetUser = array();

				$SystemCheck = $em->getRepository('SystemCheck')->findOneBy(array('businesspartner' => $Businesspartner->getId(),'info' => $info->getId()));
				if($SystemCheck){
					$bool = true;
				}else{
					$bool = false;
				}

				$sql = "select DISTINCT(s.businesspartner_id) as user, b.name as name from system_check s inner join info f on f.id = s.info_id  inner join businesspartner b on b.id = s.businesspartner_id where f.id = '".$info->getId()."' ";
				$stmt2 = $QueryBuilder->query($sql);
				
				while ($row2 = $stmt2->fetch()) {
					$datasetUser[] = array(
						'user' => $row2['user'],
						'name' => $row2['name']						
					);
				}
				
				$dataset[] = array(
					'id' => $info->getId(),
					'reminder' => $info->getReminder(),
					'date' => $info->getDate()->format('d-m-Y'),
					'status' => $info->getStatus(),
					'checkBox' => $bool,
					'read' => array($datasetUser)
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

	public function createInfo(Request $request, Response $response) {
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

			$Info = new \Info();
			$Info->setReminder($dados['description']);
			$Info->setDate(new \DateTime());
			$Info->setStatus('Aberta');
			$Info->setBusinesspartner($Businesspartner);

			$em->persist($Info);
			$em->flush($Info);

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

	public function removeInfo(Request $request, Response $response) {
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

			$Info = $em->getRepository('Info')->findOneBy( array( 'id' => $dados['id']));
			
			$SystemCheck = $em->getRepository('SystemCheck')->findOneBy(array('info' => $Info->getId()));
			
			if($SystemCheck){
				$em->remove($SystemCheck);
				$em->flush($SystemCheck);
			}
			
			if($Info) {
				$em->remove($Info);
				$em->flush($Info);
			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Registro removido com sucesso');
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

			$Info = $em->getRepository('Info')->findBy(
				array(
					'status' => 'emissao',
					'businesspartner' => NULL
				)
			);

			$dataset = array();
			foreach($Info as $info){
				$dataset[] = array(
					'id' => $info->getId(),
					'reminder' => $info->getReminder(),
					'date' => $info->getDate()->format('d-m-Y'),
					'status' => $info->getStatus()
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

			$Info = new \Info();
			$Info->setReminder($dados['description']);
			$Info->setDate(new \DateTime());
			$Info->setStatus('emissao');
			$Info->setBusinesspartner(NULL);

			$em->persist($Info);
			$em->flush($Info);

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

			$Info = $em->getRepository('Info')->findOneBy(
				array(
					'id' => $dados['id']
				)
			);

			if($Info) {
				$em->remove($Info);
				$em->flush($Info);
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

	public function saveCheck(Request $request, Response $response) {
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

			$Info = $em->getRepository('Info')->findOneBy(array('id' => $dados['id']));			
			$SystemCheck = new \SystemCheck();
			$SystemCheck->setIssueDate(new \DateTime());
			$SystemCheck->setCheckInfo($dados['check']);
			$SystemCheck->setBusinesspartner($Businesspartner);
			$SystemCheck->setInfo($Info);

			$em->persist($SystemCheck);
			$em->flush($SystemCheck);

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
