<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class CostCenter {

    public function load(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $CostCenter = $em->getRepository('CostCenter')->findAll(array('code' => 'ASC'));

        $dataset = array();
        foreach($CostCenter as $item){
            $dataset[] = array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'timestamp' => $item->getTimestamp()->format('d-m-Y'),
                'type' => $item->getType()
            );

        }
        $response->setDataset($dataset);
    }

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        if (isset($dados['id'])) {
            $CostCenter = $em->getRepository('CostCenter')->findOneBy(array('id' => $dados['id']));
        } else {
            $CostCenter = new \CostCenter();
        }

        try {

            $em->getConnection()->beginTransaction();

            $CostCenter->setName($dados['name']);
            $CostCenter->setType($dados['type']);
            $CostCenter->setTimestamp(new \Datetime());
            
            $em->persist($CostCenter);
            $em->flush($CostCenter);

            $em->getConnection()->commit();

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