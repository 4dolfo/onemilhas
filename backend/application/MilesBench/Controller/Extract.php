<?php
/**
 * Created by PhpStorm.
 * User: robertomartins
 * Date: 1/19/2015
 * Time: 11:04 PM
 */

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Extract {

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}


        $em = Application::getInstance()->getEntityManager();
        $sql = "select p FROM purchase p JOIN p.cards c WHERE c.id = ".$dados['id'] . " ORDER BY p.purchaseDate";
        $query = $em->createQuery($sql);
        $purchase = $query->getResult();


        $sql = "select s FROM sale s JOIN s.cards c WHERE c.id = ".$dados['id'] . " ORDER BY s.boardingDate";
        $query = $em->createQuery($sql);
        $sale = $query->getResult();

        $dataset = array();
        foreach($purchase as $data){
            $dataset[] = array(
                'id' => 'Compra',
                'date' => $data->getPurchaseDate()->format('d/m/y'),
                'description' => $data->getDescription(),
                'value' => $data->getTotalCost(),
                'miles' => $data->getPurchaseMiles(),
                'name' => ''
            );
        }

        foreach($sale as $data){
            $dataset[] = array(
                'id' => 'Venda',
                'date' => $data->getBoardingDate()->format('d/m/y'),
                'description' => $data->getFlightLocator(),
                'value' => $data->getAmountPaid(),
                'miles' => $data->getMilesUsed(),
                'name' => $data->getPax()->getName()
            );
        }


        $response->setDataset($dataset);
    }
}