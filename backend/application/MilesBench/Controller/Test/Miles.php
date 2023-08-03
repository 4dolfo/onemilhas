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

class Miles {

	public function loadCardTest(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
		$Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => 5987));

		$Purchase = $em->getRepository('Purchase')->findBy(array('cards' => 5987, 'status' => 'M'));
		$Purchases = array();
		foreach ($Purchase as $purchase) {
			$Purchases[] = array(
				'id' => $purchase->getId(),
				'leftover' => (float)$purchase->getLeftover()
			);
		}

		$dataset = array(
			'id' => $Milesbench->getCards()->getId(),
			'leftover' => (float)$Milesbench->getLeftover(),
			'purchases' => $Purchases
		);
		$response->setDataset($dataset);
	}


	public function changeCardTest(Request $request, Response $response) {
		$dataset = array();

		// adding operation
		$em = Application::getInstance()->getEntityManager();
		$removedMiles = \MilesBench\Controller\Miles::addMiles($em, 5987, 10000, NULL, 'CHANGE', NULL, 0);

		$em = Application::getInstance()->clearEntity();
		// $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => 5987));
		$sql = "select m FROM Milesbench m where m.cards = 5987 ";
        $query = $em->createQuery($sql);
        $Milesbench = $query->getResult()[0];

		// $Purchase = $em->getRepository('Purchase')->findBy(array('cards' => 5987));
		$sql = "select p FROM Purchase p where p.cards = 5987 and p.status = 'M' ";
        $query = $em->createQuery($sql);
        $Purchase = $query->getResult();

		$Purchases = array();
		foreach ($Purchase as $purchase) {
			$Purchases[] = array(
				'id' => $purchase->getId(),
				'leftover' => (float)$purchase->getLeftover()
			);
		}
		$dataset[] = array(
			'value' => array(
				'id' => $Milesbench->getCards()->getId(),
				'leftover' => (float)$Milesbench->getLeftover(),
				'purchases' => $Purchases
			)
		);

		// removing operation
		$em = Application::getInstance()->getEntityManager();
		$removedMiles = \MilesBench\Controller\Miles::removeMiles($em, 5987, 10000, NULL);

		$em = Application::getInstance()->clearEntity();
		// $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => 5987));
		$sql = "select m FROM Milesbench m where m.cards = 5987 ";
        $query = $em->createQuery($sql);
        $Milesbench = $query->getResult()[0];

		// $Purchase = $em->getRepository('Purchase')->findBy(array('cards' => 5987));
		$sql = "select p FROM Purchase p where p.cards = 5987 and p.status = 'M' ";
        $query = $em->createQuery($sql);
        $Purchase = $query->getResult();

		$Purchases = array();
		foreach ($Purchase as $purchase) {
			$Purchases[] = array(
				'id' => $purchase->getId(),
				'leftover' => (float)$purchase->getLeftover()
			);
		}
		$dataset[] = array(
			'value' => array(
				'id' => $Milesbench->getCards()->getId(),
				'leftover' => (float)$Milesbench->getLeftover(),
				'purchases' => $Purchases
			)
		);

		$response->setDataset($dataset);
	}
}