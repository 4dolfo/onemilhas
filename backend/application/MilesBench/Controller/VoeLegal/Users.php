<?php

namespace MilesBench\Controller\VoeLegal;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Users
{

	public function loadVoeLegaUsers(Request $request, Response $response) {
		$dados = $request->getRow();
		try {

			if(isset($dados['hashId'])){
				if($dados['hashId'] == '1851f8359de4f4ced724e47f777072f3'){
					$voelegal = Application::getInstance()->getEntityManagerVoe10Contos();
				} else {
					$voelegal = Application::getInstance()->getEntityManagerVoeLegal();
				}
			} else {	
				$voelegal = Application::getInstance()->getEntityManagerVoeLegal();
			}
			$dataset = array();

			$AppBusinesspartner = $voelegal->getRepository('AppBusinesspartner')->findAll();
			foreach ($AppBusinesspartner as $partner) {

				$agreement = '';
				if($partner->getAgreement()) {
					$agreement = $partner->getAgreement()->getName();
				}

				$dataset[] = array(
					'id' => $partner->getId(),
					'name' => $partner->getName(),
					'email' => $partner->getEmail(),
					'phone' => $partner->getPhone(),
					'agreement' => $agreement
				);
			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Dados obtidos com sucesso.');
			$response->addMessage($message);

			$response->setDataset($dataset);

		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadVoeLegalPendencies(Request $request, Response $response) {
		$dados = $request->getRow();
		try {

			if(isset($dados['hashId'])){
				if($dados['hashId'] == '1851f8359de4f4ced724e47f777072f3'){
					$voelegal = Application::getInstance()->getEntityManagerVoe10Contos();
				} else {
					$voelegal = Application::getInstance()->getEntityManagerVoeLegal();
				}
			} else {	
				$voelegal = Application::getInstance()->getEntityManagerVoeLegal();
			}
			$dataset = array();

			$sql = "SELECT a FROM AppBusinesspartner a WHERE a.validationLogin = 'pendency' ";
			$query = $voelegal->createQuery($sql);
			$AppBusinesspartner = $query->getResult();

			foreach ($AppBusinesspartner as $partner) {

				$agreement = '';
				if($partner->getAgreement()) {
					$agreement = $partner->getAgreement()->getName();
				}

				$status = '';
				switch ($partner->getValidationLogin()) {
					case "bloqued":
						$status = newObject("Bloqueado", "bloqued");
					case "approved":
						$status = newObject("Aprovado", "approved");
					case null:
					case "pendency":
					default:
						$status = newObject("Pendente", "pendency");
						break;
				}

				$number = '';
				$AppBusinesspartnerOab = $voelegal->getRepository('AppBusinesspartnerOab')->findOneBy(array('businesspartner' => $partner->getId()));
				if($AppBusinesspartnerOab) {
					$number = $AppBusinesspartnerOab->getOabnumber();
				}

				$dataset[] = array(
					'id' => $partner->getId(),
					'name' => $partner->getName(),
					'email' => $partner->getEmail(),
					'phone' => $partner->getPhone(),
					'agreement' => $agreement,
					'status' => $status,
					'number' => $number
				);
			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Dados obtidos com sucesso.');
			$response->addMessage($message);

			$response->setDataset($dataset);

		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function saveVoeLegalPendencies(Request $request, Response $response){
		$dados = $request->getRow();

		try {

			if(isset($dados['hashId'])){
				if($dados['hashId'] == '1851f8359de4f4ced724e47f777072f3'){
					$em = Application::getInstance()->getEntityManagerVoe10Contos();
				} else {
					$em = Application::getInstance()->getEntityManagerVoeLegal();
				}
			} else {	
				$em = Application::getInstance()->getEntityManagerVoeLegal();
			}
			$AppBusinesspartner = $em->getRepository('AppBusinesspartner')->findOneBy(array('id' => $dados['data']['id']));
			if(!$AppBusinesspartner) {
				throw new \Exception("Usuário não encontrado");
			}

			$AppBusinesspartner->setValidationLogin($dados['data']['status']['value']);

			$em->persist($AppBusinesspartner);
			$em->flush($AppBusinesspartner);

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Atualizado com sucesso.');
			$response->addMessage($message);
		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}
}

function newObject($key, $value){
	$object = (object) [];
	$object->key = $key;
	$object->value = $value;
	return $object;
}
