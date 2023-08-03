<?php
/**
 * Created by PhpStorm.
 * User: robertomartins
 * Date: 1/19/2015
 * Time: 11:04 PM
 */

namespace MilesBench\Controller\Test;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class UserGroup {

	public function loadUser(Request $request, Response $response) {
		$data = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
		$BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $data['registrationCode']));
		$UserGroup = $em->getRepository('UserGroup')->findOneBy(array('user' => $BusinessPartner));

		$group = array();
		if($UserGroup) {
			$group = array(
				'firstIssue' => $UserGroup->getFirstIssue(),
				'futureBoardingsTrack' => $UserGroup->getFutureBoardingsTrack(),
				'statusCreditAnalysisTrack' => $UserGroup->getStatusCreditAnalysisTrack(),
				'cardsBloqueds' => $UserGroup->getCardsBloqueds(),
				'difficultContactTrack' => $UserGroup->getDifficultContactTrack(),
				'emissionTrack' => $UserGroup->getEmissionTrack(),
				'limitTrack' => $UserGroup->getLimitTrack(),
				'statusPendingReleaseTrack' => $UserGroup->getStatusPendingReleaseTrack(),
				'clientsTrack' => $UserGroup->getClientsTrack()
			);
		} else {
			$group = array(
				'firstIssue' => false,
				'futureBoardingsTrack' => false,
				'statusCreditAnalysisTrack' => false,
				'cardsBloqueds' => false,
				'difficultContactTrack' => false,
				'emissionTrack' => false,
				'limitTrack' => false,
				'statusPendingReleaseTrack' => false,
				'clientsTrack' => false
			);
		}

		$dataset = array(
			'name' => $BusinessPartner->getName(),
			'registrationCode' => $BusinessPartner->getRegistrationCode(),
		);

		$response->setDataset( array_merge($dataset, $group) );
	}

	public function deleteUser(Request $request, Response $response) {
		$data = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
		$BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $data['registrationCode']));

		if($BusinessPartner) {
			$UserGroup = $em->getRepository('UserGroup')->findOneBy(array('user' => $BusinessPartner));

			if($UserGroup) {
				$em->remove($UserGroup);
			}

			$em->remove($BusinessPartner);
			$em->flush();
		}

		$message = new \MilesBench\Message();
		$message->setType(\MilesBench\Message::SUCCESS);
		$message->setText('Registro deletado com sucesso');
		$response->addMessage($message);
	}
}