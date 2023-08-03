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

class SystemLog {

    public function loadHistoric(Request $request, Response $response) {
        $dados = $request->getRow();
        
        $type = "";
        if (isset($dados['type'])) {
            $type = $dados['type'];
        }

        //Histórico de logins
        $days = -1;
        if (isset($dados['days'])) {
            $days = $dados['days'];
        }

        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select l FROM SystemLog l ";
        if($type == "LOGIN" && $days >= 0){
            if($dados >= 0){
                $sql .= "WHERE l.logType = 'LOGIN' AND l.businesspartner = ".$dados." order by l.issueDate desc";
            }
            else{
                $sql .= "WHERE l.logType = 'LOGIN' and l.issueDate BETWEEN '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d 00:00:01')."' and '".(new \DateTime())->format('Y-m-d 23:59:59')."' order by l.issueDate desc";
            }
        }
        else{
            $sql .= "WHERE l.logType = '".$type."' and l.description like '%n:".$dados['id']."%' ";
        }
        
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        foreach ($SystemLog as $log) {

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()));

            $dataset[] = array(
                'userName' => $BusinessPartner->getName(),
                'issue_date' => $log->getIssueDate()->format('d-m-Y  H:i:s'),
                'description' => $log->getDescription()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadCreditHistoric(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['type'])) {
            $type = $dados['type'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select l FROM SystemLog l WHERE l.logType = '".$type."' and l.description like '%n:".$dados['id']."%' ";
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        foreach ($SystemLog as $log) {

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()));

            $dataset[] = array(
                'userName' => $BusinessPartner->getName(),
                'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                'description' => $log->getDescription()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadMilesbenchLog(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select l FROM SystemLog l WHERE l.logType = 'TRIGGER' and l.description like '%cartao ->".$dados['cards_id']."%' ";
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        foreach ($SystemLog as $log) {

            $dataset[] = array(
                'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                'description' => $log->getDescription()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadTodayEmails(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select l FROM SystemLog l WHERE l.logType = 'EMAIL' and l.issueDate >= '".(new \DateTime())->modify('today')->format('Y-m-d')."' ";
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        foreach ($SystemLog as $log) {

            $dataset[] = array(
                'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                'description' => $log->getDescription()
            );
        }
        $response->setDataset($dataset);
    }

    public function saveBillingProgress(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['billing'])) {
            $billing = $dados['billing'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Registro de andamento - cliente->".$dados['id']." -DESCRIPTION- Descrição ".$billing['description']);
            $SystemLog->setLogType('BILLING');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $em->getConnection()->commit();

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

    public function saveCardProgress(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Registro de andamento -BLOQUED->CARD:".$dados['cards_id']."- ".$dados['newProgress']);
            $SystemLog->setLogType('CARDS');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $em->getConnection()->commit();

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

    public function saveCardProgressWainting(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Registro de andamento -WAITING->CARD:".$dados['cards_id']."- ".$dados['newProgress']);
            $SystemLog->setLogType('CARDS');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $em->getConnection()->commit();

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

    public function loadCardsProgress(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $sql = "select s FROM SystemLog s where s.logType = 'CARDS' and s.description like '%-BLOQUED->CARD:".$dados['cards_id']."-%' order by s.id DESC";
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        $dataset = array();
        foreach ($SystemLog as $log) {

            $description = explode("-BLOQUED->CARD:".$dados['cards_id']."-", $log->getDescription());
            $description = $description[1];

            $BusinessPartner = 'MMS VIAGENS';
            if($log->getBusinesspartner()) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()))->getName();
            }

            $dataset[] = array(
                'userName' => $BusinessPartner,
                'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                'description' => $description
            );
        }
        $response->setDataset($dataset);
    }

    public function loadCardsProgressWainting(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $sql = "select s FROM SystemLog s where s.logType = 'CARDS' and s.description like '%-WAITING->CARD:".$dados['cards_id']."-%' order by s.id DESC";
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        $dataset = array();
        foreach ($SystemLog as $log) {

            $description = explode("-WAITING->CARD:".$dados['cards_id']."-", $log->getDescription());
            $description = $description[1];

            $BusinessPartner = 'MMS VIAGENS';
            if($log->getBusinesspartner()) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()))->getName();
            }

            $dataset[] = array(
                'userName' => $BusinessPartner,
                'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                'description' => $description
            );
        }
        $response->setDataset($dataset);
    }

    public function loadBillingProgress(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select l FROM SystemLog l WHERE l.logType = 'BILLING' and l.description like '%cliente->".$dados['id']."%' order by l.id DESC";
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        foreach ($SystemLog as $log) {

            $description = explode("-DESCRIPTION-", $log->getDescription());
            $description = $description[1];

            $user = '';
            if($log->getBusinesspartner()) {
                $user = $log->getBusinesspartner()->getName();
            }

            $dataset[] = array(
                'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                'description' => $description,
                'user' => $user
            );
        }
        $response->setDataset($dataset);
    }

    public function loadCommercialStatusOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $SystemLog = $em->getRepository('SystemLog')->findOneBy(array('logType' => 'LIBERATION-COMMERCIAL', 'description' => "Liberação comercial - Pedido: " . $dados['id']));
        if($SystemLog) {
            $partner = null;
            if($SystemLog->getBusinesspartner()) {
                $partner = $SystemLog->getBusinesspartner()->getName();
            }

            $dataset = array(
                'id' => $SystemLog->getId(),
                'issue_date' => $SystemLog->getIssueDate()->format('Y-m-d H:i:s'),
                'partner' => $partner,
                'description' => $SystemLog->getDescription()
            );
        }

        $response->setDataset($dataset);
    }

    public function navigatorInfo(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        var_dump($dados);die;

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('');
        $response->addMessage($message);
    }
}