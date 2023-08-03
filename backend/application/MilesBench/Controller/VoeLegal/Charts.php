<?php

namespace MilesBench\Controller\VoeLegal;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Charts
{

	public function loadCharts(Request $request, Response $response) {
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
			$gerencial = Application::getInstance()->getEntityManager();

			$dataset = array();

			$sql = "select COUNT(s) as searchs from AppSearchs s ";
			$query = $voelegal->createQuery($sql);
			$AppSearchs = $query->getResult();

			$sql = "select COUNT(s) as sales from Sale s JOIN s.client c where c.name like 'VOE LEGAL%' and s.status NOT LIKE '%Cancelamento%' ";
			$query = $gerencial->createQuery($sql);
			$Sales = $query->getResult();

			$dataset = array('AppSearchs' => (float)$AppSearchs[0]['searchs'] / 4, 'sales' => (float)$Sales[0]['sales']);

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

	public function loadChartsSearchs(Request $request, Response $response) {
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
			$AppAgreement = $voelegal->getRepository('AppAgreement')->findAll();

			$dataset = array();
			for ( $i = 20; $i >= 0; $i-- ) { 
				$monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' day');
				$monthAgo = (new \DateTime())->modify('today')->modify('-'.($i-1).' day');

				$currentDate = array();
				foreach ($AppAgreement as $agreement) {

					$sql = "select COUNT(s) as searchs FROM AppSearchs s JOIN s.businesspartner b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' and b.agreement = '". $agreement->getId() ."' ";
					$query = $voelegal->createQuery($sql);
					$result = $query->getResult();

					$currentDate[$agreement->getName()] = (float)$query->getResult()[0]['searchs'];
				}
				$currentDate['month'] = $monthsAgo->format('Y-m-d');

				$dataset[] = $currentDate;
			}
			$response->setDataset($dataset);

		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadVoeLegalChartsSales(Request $request, Response $response) {
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
			$AppAgreement = $voelegal->getRepository('AppAgreement')->findAll();

			$dataset = array();
			for ( $i = 20; $i >= 0; $i-- ) { 
				$monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' day');
				$monthAgo = (new \DateTime())->modify('today')->modify('-'.($i-1).' day');

				$currentDate = array();
				foreach ($AppAgreement as $agreement) {
					$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => 'app.voelegal.'.$agreement->getName()));

					if($Businesspartner) {
						$sql = "select COUNT(s) as sales FROM Sale s where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' and s.client = '".$Businesspartner->getClient()."' ";
						$query = $em->createQuery($sql);
						$total = $query->getResult();

						$currentDate[$agreement->getName()] = (float)$query->getResult()[0]['sales'];
					} else {
						$currentDate[$agreement->getName()] = 0;
					}

				}
				$currentDate['month'] = $monthsAgo->format('Y-m-d');

				$dataset[] = $currentDate;

			}
			$response->setDataset($dataset);

		} catch (\Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadUsersCount(Request $request, Response $response) {
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

			$sql = "select COUNT(b) as partners from AppBusinesspartner b where b.lastTermOfUse IS NOT NULL ";
			$query = $voelegal->createQuery($sql);
			$AppBusinesspartner = $query->getResult();

			$dataset = array('count' => (float)$AppBusinesspartner[0]['partners']);
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
}
