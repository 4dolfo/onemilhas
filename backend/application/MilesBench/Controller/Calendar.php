<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Calendar {

	public function loadEvents(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $dados['hashId']));
		$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

		$sql = "select e FROM Events e where (e.businesspartner = '".$UserPartner->getId()."' and e.type = 'PRIVATED') or e.type = 'PUBLIC' ";
		$query = $em->createQuery($sql);
		$Events = $query->getResult();

		$dataset = array();
		foreach($Events as $event){

			$end = '';
			if($event->getEnd()) {
				$end = $event->getEnd()->format('Y-m-d H:i:s');
			}
			$dataset[] = array(
				'id' => $event->getId(),
				'description' => $event->getDescription(),
				'end' => $end,
				'status' => $event->getType(),
				'allDay' => ($event->getAllDay() == 'true'),
				'start' => $event->getStart()->format('Y-m-d H:i:s'),
				'title' => $event->getTitle(),
				'url' => $event->getUrl(),
				'type' => $event->getType(),
				'partner' => $event->getBusinesspartner()->getName()
			);

		}
		$response->setDataset($dataset);
	}

	public function saveEvent(Request $request, Response $response) {
		$dados = $request->getRow();
		try {
			$em = Application::getInstance()->getEntityManager();

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $dados['hashId']));
			$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

			if(isset($dados['data'])) {
				$dados = $dados['data'];
			}

			if(isset($dados['id'])) {
				$Events = $em->getRepository('Events')->findOneBy(array('id' => $dados['id']));
			} else {
				$Events = new \Events();
			}

			$Events->setDescription($dados['description']);
			$Events->setStart(new \DateTime($dados['_start']));

			if(isset($dados['_end']) && $dados['_end'] != '') {
				$Events->setEnd(new \DateTime($dados['_end']));
			}

			if(isset($dados['type']) && $dados['type'] != '') {
				if(isset($dados['type']['type'])) {
					$Events->setType($dados['type']['type']);
				} else {
					$Events->setType($dados['type']);
				}
			} else {
				$Events->setType('PRIVATED');
			}

			if(isset($dados['allDay']) && $dados['allDay'] != '') {
				$Events->setAllDay($dados['allDay']);
			} else {
				$Events->setAllDay('false');
			}
			$Events->setTitle($dados['title']);
			$Events->setUrl('');

			if(isset($dados['partner']) && $dados['partner'] != '') {
				$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partner']));
				$Events->setBusinesspartner($Businesspartner);
			} else {
				$Events->setBusinesspartner($UserPartner);
			}

			$em->persist($Events);
			$em->flush($Events);

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Registro alterado com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadEventsBillsPay(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$sql = "select e FROM Events e where e.type = 'BILLSPAY' ";
		$query = $em->createQuery($sql);
		$Events = $query->getResult();

		$dataset = array();
		foreach($Events as $event){

			$end = '';
			if($event->getEnd()) {
				$end = $event->getEnd()->format('Y-m-d H:i:s');
			}

			$dataset[] = array(
				'id' => $event->getId(),
				'description' => $event->getDescription(),
				'end' => $end,
				'status' => $event->getType(),
				'allDay' => ($event->getAllDay() == 'true'),
				'start' => $event->getStart()->format('Y-m-d H:i:s'),
				'title' => $event->getTitle(),
				'url' => $event->getUrl(),
				'amount' => (float)$event->getAmount(),
				'partner' => $event->getBusinesspartner()->getName()
			);
		}

		$response->setDataset($dataset);
	}

	public function saveEventBillsPay(Request $request, Response $response) {
		$dados = $request->getRow();
		try {
			$em = Application::getInstance()->getEntityManager();

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $dados['hashId']));
			$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

			if(isset($dados['data'])) {
				$dados = $dados['data'];
			}

			if(isset($dados['id'])) {
				$Events = $em->getRepository('Events')->findOneBy(array('id' => $dados['id']));
			} else {
				$Events = new \Events();
			}

			$Events->setDescription($dados['description']);
			$Events->setStart(new \DateTime($dados['_start']));

			if(isset($dados['_end']) && $dados['_end'] != '') {
				$Events->setEnd(new \DateTime($dados['_end']));
			}

			if(isset($dados['amount'])) {
				$Events->setAmount($dados['amount']);
			}

			$Events->setType('BILLSPAY');

			if(isset($dados['allDay']) && $dados['allDay'] != '') {
				$Events->setAllDay($dados['allDay']);
			} else {
				$Events->setAllDay('false');
			}
			$Events->setTitle($dados['title']);
			$Events->setUrl('');

			if(isset($dados['partner']) && $dados['partner'] != '') {
				$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partner']));
				$Events->setBusinesspartner($Businesspartner);
			} else {
				$Events->setBusinesspartner($UserPartner);
			}

			$em->persist($Events);
			$em->flush($Events);

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Registro alterado com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

}
