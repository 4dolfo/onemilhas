<?php
/**
 * Created by PhpStorm.
 * User: robertomartins
 * Date: 1/26/2015
 * Time: 9:31 PM
 */

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class City {

    public function loadCity(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();
        $Cities = $em->getRepository('City')->findBy(array('state' => $dados['state']));

        $dataset = array();
        foreach($Cities as $City){
            $dataset[] = array(
                'name' => $City->getName(),
                'id' => $City->getId(),
                'state' => $City->getState()
            );

        }
        $response->setDataset($dataset);
    }

    public function loadState(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select c FROM City c GROUP BY c.state ORDER BY c.state";
        $query = $em->createQuery($sql);
        $Cities = $query->getResult();

        $dataset = array();
        foreach($Cities as $City){
            $dataset[] = array(
                'state' => $City->getState()
            );

        }
        $response->setDataset($dataset);
    }
}