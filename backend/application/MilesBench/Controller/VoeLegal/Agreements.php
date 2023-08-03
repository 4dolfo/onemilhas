<?php

namespace MilesBench\Controller\VoeLegal;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Agreements
{

	public function loadVoeLegaAgreements(Request $request, Response $response) {
		$dados = $request->getRow();
		try {

			$em = Application::getInstance()->getEntityManager();
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

			$AppAgreement = $voelegal->getRepository('AppAgreement')->findAll();
			foreach ($AppAgreement as $agreement) {

				$salePlan = '';
				if($agreement->getSalePlan()) {
					$salePlan = $em->getRepository('SalePlans')->findOneBy(array('id' => $agreement->getSalePlan()))->getName();
				}

				$dataset[] = array(
					'id' => $agreement->getId(),
					'name' => $agreement->getName(),
					'description' => $agreement->getDescription(),
					'salePlan' => $salePlan,
					'status' => $agreement->getStatus()
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

	public function saveVoeLegalAgreement(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

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
			$em = Application::getInstance()->getEntityManager();

			if (isset($dados['id'])) {
				$AppAgreement = $voelegal->getRepository('AppAgreement')->find($dados['id']);
			} else {
				$AppAgreement = new \AppAgreement();

                $Dealer = new \Businesspartner();
                $Dealer->setName('VOE LEGAL '.$dados['description']);
                $Dealer->setPartnerType('U_D');
                $em->persist($Dealer);
                $em->flush($Dealer);

                $UserPermission = new \UserPermission();
				$UserPermission->setUser($Dealer);
				$UserPermission->setCommercial('true');
				$em->persist($UserPermission);
            	$em->flush($UserPermission);

				$Client = new \Businesspartner();
                $Client->setName('VOE LEGAL '.$dados['description']);
                $Client->setPartnerType('C');
                $Client->setStatus('Aprovado');
                $Client->setDealer($Dealer);
                $em->persist($Client);
                $em->flush($Client);

                $Issuer = new \Businesspartner();
                $Issuer->setName('app.voelegal.'.$dados['name']);
                $Issuer->setPartnerType('S');
                $Issuer->setClient($Client->getId());
                $em->persist($Issuer);
                $em->flush($Issuer);

			}

			$AppAgreement->setName($dados['name']);
			$AppAgreement->setDescription($dados['description']);

			if(isset($dados['status'])) {
				$AppAgreement->setStatus($dados['status']);
			} else {
				$AppAgreement->setStatus(NULL);
			}

			if(isset($dados['salePlan'])) {
				$SalePlans = $em->getRepository('SalePlans')->findOneBy(array('name' => $dados['salePlan']));
				if($SalePlans) {
					$AppAgreement->setSalePlan($SalePlans->getId());
				}
			} else {
				$AppAgreement->setSalePlan(NULL);
			}

			$voelegal->persist($AppAgreement);
			$voelegal->flush($AppAgreement);

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Registro alterado com sucesso');
			$response->addMessage($message);

		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

}
