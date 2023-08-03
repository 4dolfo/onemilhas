<?php

namespace MilesBench\Controller\Systems;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class SystemsData {

    public function load(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $SystemsData = $em->getRepository('SystemsData')->findAll();

        $dataset = array();
        foreach($SystemsData as $item){

            $dataset[] = array(
                'id' => $item->getId(),
                'systemName' => $item->getSystemName(),
                'description' => $item->getDescription(),
                'logoUrl' => $item->getLogoUrl(),
                'labelName' => $item->getLabelName(),
                'labelDescription' => $item->getLabelDescription(),
                'labelAdress' => $item->getLabelAdress(),
                'labelPhone' => $item->getLabelPhone(),
                'labelEmail' => $item->getLabelEmail(),
                'logoUrlSmall' => $item->getLogoUrlSmall(),
                'emissionTerm' => $item->getEmissionTerm(),
                'conclusionTerm' => $item->getConclusionTerm()
            );
        }
        $response->setDataset($dataset);
    }

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            if (isset($dados['id'])) {
                $SystemsData = $em->getRepository('SystemsData')->find($dados['id']);
            } else {
                $SystemsData = new \SystemsData();
            }
            
            if(isset($dados['systemName'])) {
                $SystemsData->setSystemName($dados['systemName']);
            }
            if(isset($dados['description'])) {
                $SystemsData->setDescription($dados['description']);
            }
            if(isset($dados['logoUrl'])) {
                $SystemsData->setLogoUrl($dados['logoUrl']);
            }
            if(isset($dados['labelName'])) {
                $SystemsData->setLabelName($dados['labelName']);
            }
            if(isset($dados['labelDescription'])) {
                $SystemsData->setLabelDescription($dados['labelDescription']);
            }
            if(isset($dados['labelAdress'])) {
                $SystemsData->setLabelAdress($dados['labelAdress']);
            }
            if(isset($dados['labelPhone'])) {
                $SystemsData->setLabelPhone($dados['labelPhone']);
            }
            if(isset($dados['labelEmail'])) {
                $SystemsData->setLabelEmail($dados['labelEmail']);
            }
            if(isset($dados['logoUrlSmall'])) {
                $SystemsData->setLogoUrlSmall($dados['logoUrlSmall']);
            }
            if(isset($dados['emissionTerm'])) {
                $SystemsData->setEmissionTerm($dados['emissionTerm']);
            }
            if(isset($dados['conclusionTerm'])) {
                $SystemsData->setConclusionTerm($dados['conclusionTerm']);
            }

            $em->persist($SystemsData);
            $em->flush($SystemsData);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}