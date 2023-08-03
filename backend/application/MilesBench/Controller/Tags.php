<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Tags {

	public function load(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
		$Tags = $em->getRepository('Tags')->findAll();
        $dataset = array();

		foreach ($Tags as $tag) {

			$dataset[] = array(
				'id' => $tag->getId(),
				'name' => $tag->getName(),
				'description' => $tag->getDescription()
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

        try{

            if(isset($dados['id'])){
                $Tags = $em->getRepository('Tags')->findOneBy(array('id' => $dados['id']));
            }else{
                $Tags = new \Tags();
            }

            $Tags->setName($dados['name']);
            $Tags->setDescription($dados['description']);

            $em->persist($Tags);
            $em->flush($Tags);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}
