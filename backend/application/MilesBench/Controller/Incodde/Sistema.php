<?php

namespace MilesBench\Controller\Incodde;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Sistema {

    public function login(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            // fails on validation of the user
            $SystemsData = $em->getRepository('SystemsData')->find($dados['id']);
            if(!$SystemsData) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato!");
            }


            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dado Obtidos com sucesso!');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function load(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            $dataset = array();
            $sql = "select s FROM SystemsData s where s.urlMath like '%". $dados['url'] ."%' ";
			$query = $em->createQuery($sql);
            $Systems = $query->getResult();
            foreach ($Systems as $key => $value) {
                $dataset = array(
                    'logoHeader' => $value->getLogoUrl(),
                    'logoFooter' => $value->getLogoUrlSmall(),
                    'color' => $value->getColor(),
                    'color_2' => $value->getColor2(),
                    'nome' => $value->getSystemName()
                );
            }

            if(count($Systems) == 0) {
                $sql = "select s FROM SystemsData s where s.id = 1 ";
                $query = $em->createQuery($sql);
                $Systems = $query->getResult();
                foreach ($Systems as $key => $value) {
                    $dataset = array(
                        'logoHeader' => $value->getLogoUrl(),
                        'logoFooter' => $value->getLogoUrlSmall(),
                        'color' => $value->getColor(),
                        'color_2' => $value->getColor2(),
                        'nome' => $value->getSystemName()
                    );
                }
            }

            $response->setDataset($dataset);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dado Obtidos com sucesso!');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}