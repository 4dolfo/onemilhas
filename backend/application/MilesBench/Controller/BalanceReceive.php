<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class BalanceReceive {

    public function loadClientBilletsChart(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dataset = array();

        $sql = "select COUNT(b) as billets FROM Billetreceive b where b.paymentDate <= b.dueDate and b.client ='".$dados['id']."' and b.actualValue > 0 ";
        $query = $em->createQuery($sql);
        $Billetreceive = $query->getResult();

        $dataset[] = array(
            'data' => (float)$Billetreceive[0]['billets'],
            'label' => 'Não Atrasados/Adiantados - '.$Billetreceive[0]['billets']
        );

        $sql = "select COUNT(b) as billets FROM Billetreceive b where b.paymentDate > b.dueDate and b.client ='".$dados['id']."' ";
        $query = $em->createQuery($sql);
        $Billetreceive = $query->getResult();

        $dataset[] = array(
            'data' => (float)$Billetreceive[0]['billets'],
            'label' => 'Atrasados - '.$Billetreceive[0]['billets']
        );

        $response->setDataset($dataset);
    }

    public function loadBilletsLate(Request $request, Response $response) {
        $dados = $request->getRow();

        $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        $em = Application::getInstance()->getEntityManager();

        $dataset = array();

        $sql = " select DISTINCT(d.billet) as billet_id from BilletsDivision d ";
        $query = $em->createQuery($sql);
        $BilletsIds = $query->getResult();

        $found = "0";
        $and = ",";

        foreach ($BilletsIds as $billet) {
            $found = $found.$and.$billet['billet_id'];
            $and = ', ';
        }

        $sql = "select COUNT(b) as billets FROM Billetreceive b where b.paymentDate <= b.dueDate and b.paymentDate >='".$daysAgo->format('Y-m-d')."' and b.actualValue > 0 and b.id not in ( ".$found." ) ";
        $query = $em->createQuery($sql);
        $Billetreceive = $query->getResult();

        $dataset[] = array(
            'data' => (float)$Billetreceive[0]['billets'],
            'label' => 'Não Atrasados/Adiantados - '.$Billetreceive[0]['billets']
        );

        $sql = "select COUNT(b) as billets FROM Billetreceive b where b.paymentDate > b.dueDate and b.paymentDate >='".$daysAgo->format('Y-m-d')."' and b.actualValue > 0 and b.id not in ( ".$found." ) ";
        $query = $em->createQuery($sql);
        $Billetreceive = $query->getResult();

        $dataset[] = array(
            'data' => (float)$Billetreceive[0]['billets'],
            'label' => 'Atrasados - '.$Billetreceive[0]['billets']
        );

        $response->setDataset($dataset);
    }

    public function loadBilletsPayment(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $sql = " select DISTINCT(d.billet) as billet_id from BilletsDivision d ";
        $query = $em->createQuery($sql);
        $BilletsIds = $query->getResult();

        $found = "0";
        $and = ",";

        foreach ($BilletsIds as $billet) {
            $found = $found.$and.$billet['billet_id'];
            $and = ', ';
        }

        // $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        $dataset = array();
        for ($i=15; $i >= 0; $i--) { 
            $daysAgo = (new \DateTime())->modify('today')->modify('-'.($i - 1).' days');
            $dayAgo = (new \DateTime())->modify('today')->modify('-'.$i.' days');
        
            $sql = "select SUM(b.actualValue) as actualValue FROM Billetreceive b where b.paymentDate > b.dueDate and b.paymentDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and b.actualValue > 0 and b.id not in ( ".$found." )  ";
            $query = $em->createQuery($sql);
            $BilletsLate = $query->getResult();

            $sql = "select SUM(b.actualValue) as actualValue FROM Billetreceive b where b.paymentDate <= b.dueDate and b.paymentDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and b.actualValue > 0 and b.id not in ( ".$found." )  ";
            $query = $em->createQuery($sql);
            $Billets = $query->getResult();

            $dataset[] = array(
                'late' => (float)$BilletsLate[0]['actualValue'],
                'notLate' => (float)$Billets[0]['actualValue'],
                'date' => $dayAgo->format('Y-m-d')
            );
        }
        $response->setDataset($dataset);
    }

    public function loadBilletsDefaultDaily(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $dataset = array();
        for ($i = 29; $i > 1; $i--) { 
            $daysAgo = (new \DateTime())->modify('today')->modify('-'.$i.' days');
        
            $sql = "select SUM(b.actualValue) as actualValue, COUNT(DISTINCT b.client) as clients, COUNT(b) as billets FROM Billetreceive b where ".
                " b.dueDate < '".$daysAgo->modify('-1 days')->format('Y-m-d')."' and b.actualValue > 0 ".
                " and ( b.paymentDate = '' or b.paymentDate is NULL ) and b.status = 'E' ";

            $query = $em->createQuery($sql);
            $BilletsLate = $query->getResult();

            $dataset[] = array(
                'opened' => (float)$BilletsLate[0]['actualValue'],
                'clients' => (float)$BilletsLate[0]['clients'],
                'billets' => (float)$BilletsLate[0]['billets'],
                'date' => $daysAgo->format('Y-m-d')
            );
        }
        $response->setDataset($dataset);
    }

    public function loadBilletsDefaultMonthly(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $dataset = array();
        for ($i = 5; $i >= 0; $i--) { 
            $daysAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month');
        
            $sql = "select SUM(b.actualValue) as actualValue, COUNT(DISTINCT b.client) as clients, COUNT(b) as billets FROM Billetreceive b where ".
                " b.dueDate < '".$daysAgo->modify('-1 days')->format('Y-m-d')."' and b.actualValue > 0 ".
                " and ( b.paymentDate = '' or b.paymentDate is NULL ) and b.status = 'E' ";

            $query = $em->createQuery($sql);
            $BilletsLate = $query->getResult();

            $dataset[] = array(
                'opened' => (float)$BilletsLate[0]['actualValue'],
                'clients' => (float)$BilletsLate[0]['clients'],
                'billets' => (float)$BilletsLate[0]['billets'],
                'date' => $daysAgo->format('Y-m')
            );
        }
        $response->setDataset($dataset);
    }
}