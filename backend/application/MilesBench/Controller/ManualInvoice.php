<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class ManualInvoice {

	public function loadInvoiceMonthly(Request $request, Response $response) {
		$dados = $request->getRow();

		$em = Application::getInstance()->getEntityManager();
		$sql = "select SUM(b.originalValue) as originalValue, c.name as client FROM Billetreceive b JOIN b.client c ";
		$whereClause = ' WHERE ';
		$and = '';

		if(isset($dados['_dueDateFrom']) && $dados['_dueDateFrom']) {
			$whereClause = $whereClause.$and." b.issueDate >= '".$dados['_dueDateFrom']."' ";
			$and = ' and ';
		}

		if(isset($dados['_dueDateTo']) && $dados['_dueDateTo']) {
			$whereClause = $whereClause.$and." b.issueDate <= '".$dados['_dueDateTo']."' ";
			$and = ' and ';
		}

		if(isset($dados['data']['client']) && $dados['data']['client'] != '') {
			$client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client']));
			if($client) {
				$whereClause = $whereClause.$and." c.name = '".$client->getName()."' ";
				$and = ' and ';
			}
		}

		$whereClause = $whereClause.$and." b.originalValue > 0 group by b.client ";

		$dataset = array();
		$query = $em->createQuery($sql.$whereClause);

		$billetReceives = $query->getResult();
		foreach($billetReceives as $billetReceive){

			$dataset[] = array(
				'client' => $billetReceive['client'],
				'amount' => (float)$billetReceive['originalValue']
			);
		}
		$response->setDataset($dataset);
	}
}