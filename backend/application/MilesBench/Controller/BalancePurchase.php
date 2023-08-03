<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class BalancePurchase {

    public function loadPurchaseMiles(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select SUM(p.purchaseMiles) as purchases, MAX(c.airline) as airline FROM Purchase p JOIN p.cards c JOIN c.airline a where p.purchaseDate >= '".$daysAgo->format('Y-m-d')."' and p.status='M' and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) group by c.airline";
        $query = $em->createQuery($sql);
        $Purchases = $query->getResult();

        $dataset = array();
        foreach($Purchases as $purchase){
            $Airline = $em->getRepository('Airline')->findOneBy(array('id' => $purchase['airline']));
            $dataset[] = array(
                'data' => $purchase['purchases'],
                'label' => $Airline->getName().' - '.number_format($purchase['purchases'], 0, ',', '.')
            );
        }
        $response->setDataset($dataset);
    }

    public function loadPurchasesAnalysis(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select COUNT(p.id) as purchases, MAX(c.airline) as airline FROM Purchase p JOIN p.cards c JOIN c.airline a where p.purchaseDate >= '".$daysAgo->format('Y-m-d')."' and p.status='M' group by c.airline";
        $query = $em->createQuery($sql);
        $Purchases = $query->getResult();

        $dataset = array();
        foreach($Purchases as $purchase){
            $Airline = $em->getRepository('Airline')->findOneBy(array('id' => $purchase['airline']));
            $dataset[] = array(
                'data' => $purchase['purchases'],
                'label' => $Airline->getName().' - '.$purchase['purchases']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadMergedMiles(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        // $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        $dataset = array();
        for ($i=15; $i >= 0; $i--) { 
            $daysAgo = (new \DateTime())->modify('today')->modify('-'.($i - 1).' days');
            $dayAgo = (new \DateTime())->modify('today')->modify('-'.$i.' days');
        
            $sql = "select SUM(p.purchaseMiles) as purchaseMiles FROM Purchase p JOIN p.cards c where p.mergeDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and c.airline='1' ";
            $query = $em->createQuery($sql);
            $PurchasesTam = $query->getResult();

            $sql = "select SUM(p.purchaseMiles) as purchaseMiles FROM Purchase p JOIN p.cards c where p.mergeDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and c.airline='2' ";
            $query = $em->createQuery($sql);
            $PurchasesGol = $query->getResult();

            $sql = "select SUM(p.purchaseMiles) as purchaseMiles FROM Purchase p JOIN p.cards c where p.mergeDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and c.airline='3' ";
            $query = $em->createQuery($sql);
            $PurchasesAzul = $query->getResult();

            $dataset[] = array(
                'PurchasesAzul' => (float)$PurchasesAzul[0]['purchaseMiles'],
                'PurchasesGol' => (float)$PurchasesGol[0]['purchaseMiles'],
                'PurchasesTam' => (float)$PurchasesTam[0]['purchaseMiles'],
                'date' => $dayAgo->format('Y-m-d')
            );
        }
        $response->setDataset($dataset);
    }   
}