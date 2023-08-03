<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Notifications {

	public function loadClientNotifications(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}

		$em = Application::getInstance()->getEntityManager();
		$Notifications = $em->getRepository('Notifications')->findAll();

		$dataset = array();
		foreach($Notifications as $notification){

			$status = false;
			$ClientNotification = $em->getRepository('ClientNotification')->findOneBy(
				array(
					'client' => $dados['data']['id'],
					'notification' => $notification->getId()
				)
			);
			if($ClientNotification) {
				$status = true;
			}

			$dataset[] = array(
				'id' => $notification->getId(),
				'name' => $notification->getName(),
				'status' => $status
			);
		}
		$response->setDataset($dataset);
	}

	public function setStatusNotification(Request $request, Response $response) {
		$dados = $request->getRow();

		$em = Application::getInstance()->getEntityManager();
		try {

			if($dados['notification']['status'] == 'true') {
				$Notifications = $em->getRepository('Notifications')->findOneBy(array('id' => $dados['notification']['id']));
				$Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['data']['id']));

				$ClientNotification = new \ClientNotification();
				$ClientNotification->setNotification($Notifications);
				$ClientNotification->setClient($Client);

				$em->persist($ClientNotification);
				$em->flush($ClientNotification);

			} else if($dados['notification']['status'] == 'false') {

				$ClientNotification = $em->getRepository('ClientNotification')->findOneBy(
					array(
						'client' => $dados['data']['id'],
						'notification' => $dados['notification']['id']
					)
				);

				$em->remove($ClientNotification);
				$em->flush($ClientNotification);

			}

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
