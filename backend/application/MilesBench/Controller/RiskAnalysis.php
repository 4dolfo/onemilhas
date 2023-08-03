<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class RiskAnalysis {

	public function loadSalesClient(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
        
        $dataset = array();
        $date = (new \Datetime())->modify('+1 day');

        $sql = "select s FROM Sale s WHERE s.client = '".$dados['id']."' and s.boardingDate >= '".$date->format('Y-m-d')."' and (s.status = 'Emitido' or s.status = 'Remarcação Solicitado' or s.status = 'Remarcação Confirmado') order by s.boardingDate ";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));

        foreach ($Sales as $Sale) {

            $airportLocation = '';
            $international = false;
            if($Sale->getAirportFrom() != null){
                $international = ($Sale->getAirportFrom()->getInternational() == 'true');
                if($international) {
                    if($Sale->getAirportFrom()->getLocation() != NULL) {
                        $airportLocation = $Sale->getAirportFrom()->getLocation();
                    }
                }
            }

            if($Sale->getAirportTo() != null){
                if(!$international) {
                    $international = ($Sale->getAirportTo()->getInternational() == 'true');
                    if($international) {
                        if($Sale->getAirportTo()->getLocation() != NULL && $airportLocation == '') {
                            $airportLocation = $Sale->getAirportTo()->getLocation();
                        }
                    }
                }
            }

            $cancelCost = 0;
            if($client->getOperationPlan()) {
                $CancelCosts = $em->getRepository('RefundRepricing')->findOneBy(
                    array(
                        'airline' => $Sale->getAirline()->getId(),
                        'type' => 'Analise Risco',
                        'operationPlan' => $client->getOperationPlan()->getId()
                    )
                );
            } else {
                $CancelCosts = $em->getRepository('RefundRepricing')->findOneBy(
                    array(
                        'airline' => $Sale->getAirline()->getId(),
                        'type' => 'Analise Risco',
                        'operationPlan' => '1'
                    )
                );
            }

            if($CancelCosts) {
                if($international) {
                    if($airportLocation && $CancelCosts->getNorthAmericaBeforeBoarding() != 0) {
                        $cancelCost = $CancelCosts->getNorthAmericaBeforeBoarding();
                    } else if($airportLocation && $CancelCosts->getSouthAmericaBeforeBoarding() != 0) {
                        $cancelCost = $CancelCosts->getSouthAmericaBeforeBoarding();
                    } else {
                        $cancelCost = $CancelCosts->getInternationalBeforeBoarding();
                    }
                } else {
                    $cancelCost = $CancelCosts->getNationalBeforeBoarding();
                }
            }

//            if($cancelCost == 0) {
//                var_dump($Sale->getAirline());die;
//                var_dump(isset($CancelCosts));die;
//            }

            if($Sale->getCards()) {
                if($Sale->getCards()->getCardType() == 'RED' || $Sale->getCards()->getCardType() == 'BLACK') {
                    $cancelCost = 0;
                }
            }

            $dataset[] = array(
                'airline' => $Sale->getAirline()->getName(),
                'client' => $Sale->getClient()->getName(),
                'paxName' => $Sale->getPax()->getName(),
                'from' => $Sale->getAirportFrom()->getCode(),
                'to' => $Sale->getAirportTo()->getCode(),
                'issueDate' => $Sale->getIssueDate()->format('Y-m-d'),
                'boardingDate' => $Sale->getBoardingDate()->format('Y-m-d'),
                'landingDate' => $Sale->getLandingDate()->format('Y-m-d'),
                'flight' => $Sale->getFlight(),
                'flightLocator' => $Sale->getFlightLocator(),
                'amountPaid' => (float)$Sale->getAmountPaid(),
                'totalCost' => (float)$Sale->getTotalCost(),
                'cancelCost' => (float)$cancelCost,
                'airportLocation' => $airportLocation,
                'international' => $international,
                'checked' => true
            );
        }
        $response->setDataset($dataset);
    }

    public function loadBilletsClient(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));

        $sql = "select b FROM Billetreceive b WHERE b.client = '".$BusinessPartner->getId()."' and b.status = 'E' and b.actualValue > 0 ";
        $query = $em->createQuery($sql);
        $Billetreceive = $query->getResult();

        $dataset = array();
        foreach($Billetreceive as $billetReceive){
            $billingPeriod = false;

            if($billetReceive->getClient()->getBillingPeriod() == 'Semanal' || $billetReceive->getClient()->getBillingPeriod() == 'Quinzenal' || $billetReceive->getClient()->getBillingPeriod() == 'Mensal') {
                $billingPeriod = true;
            }

            if($billingPeriod) {
                $sql = "select b FROM Billsreceive b where b.billet = '".$billetReceive->getId()."' and b.status = 'E' ";
                $query = $em->createQuery($sql);
                $Billsreceive = $query->getResult();
            }

            $sql = "select d FROM BilletsDivision d where d.billet = '".$billetReceive->getId()."' ";
            $query = $em->createQuery($sql);
            $hasDivisions = $query->getResult();

            if((count($hasDivisions) == 0) && !$billingPeriod) {
                $orNumb = '';
                if($billetReceive->getOurNumber()){
                    $orNumb = $billetReceive->getOurNumber();
                }
                $docNumb = '';
                if($billetReceive->getDocNumber()){
                    $docNumb = $billetReceive->getDocNumber();
                }
                $dataset[] = array(
                    'id' => $billetReceive->getId(),
                    'IssueDate' => $billetReceive->getIssueDate()->format('Y-m-d'),
                    'status' => $billetReceive->getStatus(),
                    'description' => $billetReceive->getDescription(),
                    'due_date' => $billetReceive->getDueDate()->format('Y-m-d'),
                    'actual_value' => (float)$billetReceive->getActualValue(),
                    'original_value' => (float)$billetReceive->getOriginalValue(),
                    'tax' => (float)$billetReceive->getTax(),
                    'discount' => (float)$billetReceive->getDiscount(),
                    'alreadyPaid' => (float)$billetReceive->getAlreadyPaid(),
                    'docNumber' => $orNumb,
                    'ourNumber' => $docNumb
                );
            } else if($billingPeriod && count($Billsreceive) == 0) {
                $orNumb = '';
                if($billetReceive->getOurNumber()){
                    $orNumb = $billetReceive->getOurNumber();
                }
                $docNumb = '';
                if($billetReceive->getDocNumber()){
                    $docNumb = $billetReceive->getDocNumber();
                }
                $dataset[] = array(
                    'id' => $billetReceive->getId(),
                    'IssueDate' => $billetReceive->getIssueDate()->format('Y-m-d'),
                    'status' => $billetReceive->getStatus(),
                    'description' => $billetReceive->getDescription(),
                    'due_date' => $billetReceive->getDueDate()->format('Y-m-d'),
                    'actual_value' => (float)$billetReceive->getActualValue(),
                    'original_value' => (float)$billetReceive->getOriginalValue(),
                    'tax' => (float)$billetReceive->getTax(),
                    'discount' => (float)$billetReceive->getDiscount(),
                    'alreadyPaid' => (float)$billetReceive->getAlreadyPaid(),
                    'docNumber' => $orNumb,
                    'ourNumber' => $docNumb
                );
            }
        }

        $sql = "select b FROM BilletsDivision b JOIN b.billet w where b.paid = 'false' and w.client = '".$BusinessPartner->getId()."' ";
        $query = $em->createQuery($sql);
        $Bills = $query->getResult();
        foreach ($Bills as $bill) {

            $alreadyPaid = 0;
            if($bill->getBillet()->getAlreadyPaid() > 0) {
                $sql = "select SUM(b.actualValue) as actualValue FROM BilletsDivision b where b.dueDate < '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' and b.billet = '".$bill->getBillet()->getId()."' and b.id < '".$bill->getId()."' ";
                $query = $em->createQuery($sql);
                $actualValue = $query->getResult();
                if($bill->getBillet()->getAlreadyPaid() - $actualValue[0]['actualValue'] > 0) {
                    if($bill->getActualValue() < ($bill->getBillet()->getAlreadyPaid() - $actualValue[0]['actualValue'])) {
                        $alreadyPaid = $bill->getActualValue();
                    } else {
                        $alreadyPaid = $bill->getBillet()->getAlreadyPaid() - $actualValue[0]['actualValue'];
                    }
                }
            }

            $dataset[] = array(
                'id' => $bill->getId(),
                'IssueDate' => $bill->getBillet()->getIssueDate()->format('Y-m-d'),
                'status' => 'E',
                'description' => '',
                'due_date' => $bill->getDueDate()->format('Y-m-d'),
                'actual_value' => (float)$bill->getActualValue(),
                'original_value' => (float)$bill->getActualValue(),
                'tax' => 0,
                'discount' => 0,
                'alreadyPaid' => $alreadyPaid,
                'docNumber' => $bill->getName(),
                'ourNumber' => $bill->getName()
            );

        }

        $response->setDataset($dataset);
    }

    public function checkRiskAnalysis(Request $request, Response $response) {
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $sql = " select distinct(b.client_id) as clients, c.name as name FROM billetreceive b ".
            " INNER JOIN businesspartner c on c.id = b.client_id ".
            " where b.status = 'E' and b.due_date < '".(new \Datetime())->format('Y-m-d')."' and b.actual_value > 0 order by c.name  ";
        $stmt = $QueryBuilder->query($sql);
        while ($client = $stmt->fetch()) {
            $totalCost = 0;
            $totalPaid = 0;
            $Billetreceive = 0;

            $sql = " select SUM(b.actual_value) as actualValue FROM billetreceive b WHERE b.client_id = '".$client['clients']."' and b.status = 'E' and b.actual_value > 0 ";
            $stmt3 = $QueryBuilder->query($sql);
            while ($row3 = $stmt3->fetch()) {
                if(isset($row3['actualValue'])) {
                    $Billetreceive = $row3['actualValue'];
                }
            }

            $sql = " select s.*, t.international as to_international, f.international as from_international, c.card_type as card_type FROM sale s ".
                " INNER JOIN airline a on a.id = s.airline_id ".
                " LEFT JOIN airport f on f.id = s.airport_from ".
                " LEFT JOIN airport t on t.id = s.airport_to ".
                " LEFT JOIN cards c on c.id = s.cards_id ".
                " WHERE s.client_id = '".$client['clients']."' and s.boarding_date >= '".(new \Datetime())->modify('+1 day')->format('Y-m-d')."' and (s.status = 'Emitido' or s.status = 'Remarcação Solicitado' or s.status = 'Remarcação Confirmado') ";
            $stmt2 = $QueryBuilder->query($sql);
            while ($Sale = $stmt2->fetch()) {

                $airportLocation = '';
                $international = false;
                if(isset($Sale['to_international'])) {
                    $international = $Sale['to_international'] == 'true';
                }
                if(isset($Sale['from_international'])) {
                    if(!$international) {
                        $international = $Sale['from_international'] == 'true';
                    }
                }

                $cancelCost = 0;
                $CancelCosts = $em->getRepository('RefundRepricing')->findOneBy(array('airline' => $Sale['airline_id'], 'type' => 'Cancelamento'));
                if($CancelCosts) {
                    if($international) {
                        if($airportLocation && $CancelCosts->getNorthAmericaBeforeBoarding() != 0) {
                            $cancelCost = $CancelCosts->getNorthAmericaBeforeBoarding();
                        } else if($airportLocation && $CancelCosts->getSouthAmericaBeforeBoarding() != 0) {
                            $cancelCost = $CancelCosts->getSouthAmericaBeforeBoarding();
                        } else {
                            $cancelCost = $CancelCosts->getInternationalBeforeBoarding();
                        }
                    } else {
                        $cancelCost = $CancelCosts->getNationalBeforeBoarding();
                    }
                }

                if($Sale['card_type'] == 'RED' || $Sale['card_type'] == 'BLACK') {
                    $cancelCost = 0;
                }

                $totalCost = $totalCost + $cancelCost;
                $totalPaid = $totalPaid + (float)$Sale['amount_paid'];
            }

            $calculation = false;
            if((float)$Billetreceive > 0) {
                $calculation = ((float)$Billetreceive - ($totalPaid - $totalCost) > 0);
            }

            if($calculation) {
                $dataset[] = array(
                    'client' => $client['name']
                );
            }
        }

        $response->setDataset($dataset);
    }
}
