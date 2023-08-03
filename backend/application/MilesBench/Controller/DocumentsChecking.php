<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class DocumentsChecking {

    public function loadDocuments(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $sql = "select d FROM DocumentsChecking d ";

        $where = ' where ';
        $and = '';

        if(isset($dados['partnerType'])) {
            if($dados['partnerType'] == 'C') {
                $sql = $sql.$where." d.agency = 'true' ";
                $where = '';
                $and = ' and ';
            }
        }

        if(isset($dados['partnerType'])) {
            if($dados['partnerType'] == 'P') {
                $sql = $sql.$where." d.provider = 'true' ";
                $where = '';
                $and = ' and ';
            }
        }

        $query = $em->createQuery($sql);
        $DocumentsChecking = $query->getResult();

        $dataset = array();
        foreach($DocumentsChecking as $doc){

            $dataset[] = array(
                'id' => $doc->getId(),
                'name' => $doc->getName(),
                'provider' => ($doc->getProvider() == "true"),
                'agency' => ($doc->getAgency() == "true")
            );
        }
        $response->setDataset($dataset);
    }

    public function saveDocuments(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        
        try{

            if(isset($dados['id'])){
                $DocumentsChecking = $em->getRepository('DocumentsChecking')->findOneBy(array('id' => $dados['id']));
            }else{
                $DocumentsChecking = new \DocumentsChecking();
            }

            $DocumentsChecking->setName($dados['name']);
            if(isset($dados['provider']) && $dados['provider'] != '') {
                $DocumentsChecking->setProvider($dados['provider']);
            } else {
                $DocumentsChecking->setProvider('false');
            }

            if(isset($dados['agency']) && $dados['agency'] != '') {
                $DocumentsChecking->setAgency($dados['agency']);
            } else {
                $DocumentsChecking->setAgency('false');
            }

            $em->persist($DocumentsChecking);
            $em->flush($DocumentsChecking);

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