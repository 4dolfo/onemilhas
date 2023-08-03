<?php

namespace MilesBench\Controller\Functions;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class OneTime {

	public function updateOnlineOrder(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
		$response->setDataset(array('ok'));
	}

}