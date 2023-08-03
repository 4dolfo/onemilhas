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

class Airline {

    public function load(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $Airline = $em->getRepository('Airline')->findAll();

        $dataset = array();
        foreach($Airline as $item){
            $cancelCost = '0.00';
            if($item->getCancelCost()){
                $cancelCost = $item->getCancelCost();
            }

            $provider_name = '';
            $cards_id = '';
            if($item->getRobotCards()) {
                $provider_name = $item->getRobotCards()->getBusinesspartner()->getName();
                $cards_id = $item->getRobotCards()->getId();
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'cancelCost' => $cancelCost,
                'cards_limit' => (float)$item->getCardsLimit(),
                'miles_limit' => (float)$item->getMilesLimit(),
                'robotStatus' => ($item->getRobotStatus() == 'true'),
                'provider_name' => $provider_name,
                'cards_id' => $cards_id,
                'baggage' => (float)$item->getBaggage(),
                'baggageInternational' => (float)$item->getBaggageInternational()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadValidAirlines(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));

        $dataset = array();
        foreach($Airlines as $item){
            $cancelCost = '0.00';
            if($item->getCancelCost()){
                $cancelCost = $item->getCancelCost();
            }

            $provider_name = '';
            $cards_id = '';
            if($item->getRobotCards()) {
                $provider_name = $item->getRobotCards()->getBusinesspartner()->getName();
                $cards_id = $item->getRobotCards()->getId();
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'cancelCost' => $cancelCost,
                'cards_limit' => (float)$item->getCardsLimit(),
                'miles_limit' => (float)$item->getMilesLimit(),
                'robotStatus' => ($item->getRobotStatus() == 'true'),
                'provider_name' => $provider_name,
                'cards_id' => $cards_id,
                'baggage' => (float)$item->getBaggage(),
                'baggageInternational' => (float)$item->getBaggageInternational()
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
                $Airline = $em->getRepository('Airline')->find($dados['id']);
            } else {
                $Airline = new \Airline();
            }

            $Airline->setName($dados['name']);
            if(isset($dados['provider']) && $dados['provider'] != ''){
                $Airline->setProvider($dados['provider']);
            }
            if(isset($dados['cancelCost']) && $dados['cancelCost'] != ''){
                $Airline->setCancelCost($dados['cancelCost']);
            }
            if(isset($dados['cards_limit']) && $dados['cards_limit'] != ''){
                $Airline->setCardsLimit($dados['cards_limit']);
            }
            if(isset($dados['miles_limit']) && $dados['miles_limit'] != ''){
                $Airline->setMilesLimit($dados['miles_limit']);
            }
            if(isset($dados['robotStatus']) && $dados['robotStatus'] != '') {
                $Airline->setRobotStatus($dados['robotStatus']);
            }
            if(isset($dados['baggage']) && $dados['baggage'] != '') {
                $Airline->setBaggage($dados['baggage']);
            }
            if(isset($dados['baggageInternational']) && $dados['baggageInternational'] != '') {
                $Airline->setBaggageInternational($dados['baggageInternational']);
            }

            if( isset($dados['cards_id']) && $dados['cards_id'] != "" ) {
                $Cards = $em->getRepository('Cards')->find($dados['cards_id']);
                if($Cards) {
                    $Airline->setRobotCards($Cards);
                }
            }

            $em->persist($Airline);
            $em->flush($Airline);

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

    public function loadControlPlans(Request $request, Response $response) {
        $dados = $request->getRow();
        
        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $AirlineOperationsPlan = $em->getRepository('AirlineOperationsPlan')->findAll();

        foreach ($AirlineOperationsPlan as $plan) {

            $dataset[] = array(
                'id' => $plan->getId(),
                'description' => $plan->getDescription()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadPlanControl(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $sql = "select DISTINCT(p.airline) as airline FROM RefundRepricing p WHERE p.operationPlan = '".$dados['id']."' ";
        $query = $em->createQuery($sql);
        $RefundRepricing = $query->getResult();

        $dataset = array();
        foreach ($RefundRepricing as $airline) {
            $Airline = $em->getRepository('Airline')->findOneBy(array('id' => $airline['airline']));

            $RefundRepricing = $em->getRepository('RefundRepricing')->findBy(array('operationPlan' => $dados['id'], 'airline' => $airline['airline']));
            $planData = array();
            foreach ($RefundRepricing as $plan) {
                $planData[] = array(
                    'id' => $plan->getId(),
                    'type' => $plan->getType(),
                    'nationalBeforeBoarding' => (float)$plan->getNationalBeforeBoarding(),
                    'nationalAfterBoarding' => (float)$plan->getNationalAfterBoarding(),
                    'internationalBeforeBoarding' => (float)$plan->getInternationalBeforeBoarding(),
                    'internationalAfterBoarding' => (float)$plan->getInternationalAfterBoarding(),
                    'northAmericaBeforeBoarding' => (float)$plan->getNorthAmericaBeforeBoarding(),
                    'northAmericaAfterBoarding' => (float)$plan->getNorthAmericaAfterBoarding(),
                    'southAmericaBeforeBoarding' => (float)$plan->getSouthAmericaBeforeBoarding(),
                    'southAmericaAfterBoarding' => (float)$plan->getSouthAmericaAfterBoarding(),
                    'nationalBeforeBoardingCost' => (float)$plan->getNationalBeforeBoardingCost(),
                    'nationalAfterBoardingCost' => (float)$plan->getNationalAfterBoardingCost(),
                    'internationalBeforeBoardingCost' => (float)$plan->getInternationalBeforeBoardingCost(),
                    'internationalAfterBoardingCost' => (float)$plan->getInternationalAfterBoardingCost(),
                    'northAmericaBeforeBoardingCost' => (float)$plan->getNorthAmericaBeforeBoardingCost(),
                    'northAmericaAfterBoardingCost' => (float)$plan->getNorthAmericaAfterBoardingCost(),
                    'southAmericaBeforeBoardingCost' => (float)$plan->getSouthAmericaBeforeBoardingCost(),
                    'southAmericaAfterBoardingCost' => (float)$plan->getSouthAmericaAfterBoardingCost()
                );
            }
            $dataset[] = array(
                'airline' => $Airline->getName(),
                'plansData' => $planData
            );
        }
        $response->setDataset($dataset);
    }

    public function savePlanControl(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['control'])) {
            $control = $dados['control'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        try {

            if(!isset($dados['id'])) {
                $AirlineOperationsPlan = new \AirlineOperationsPlan();
                $AirlineOperationsPlan->setDescription($dados['description']);
                $em->persist($AirlineOperationsPlan);
                $em->flush($AirlineOperationsPlan);
            } else {
                $AirlineOperationsPlan = $em->getRepository('AirlineOperationsPlan')->find($dados['id']);
                $AirlineOperationsPlan->setDescription($dados['description']);
                $em->persist($AirlineOperationsPlan);
                $em->flush($AirlineOperationsPlan);
            }

            $found = '';
            $and = '';
            foreach($control as $operation){
                $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $operation['airline']));

                foreach ($operation['plansData'] as $value) {

                    if(isset($value['id'])) {
                        $RefundRepricing = $em->getRepository('RefundRepricing')->find($value['id']);
                    } else {
                        $RefundRepricing = new \RefundRepricing();
                    }

                    $RefundRepricing->setType($value['type']);
                    $RefundRepricing->setNationalBeforeBoarding($value['nationalBeforeBoarding']);
                    $RefundRepricing->setNationalAfterBoarding($value['nationalAfterBoarding']);
                    $RefundRepricing->setInternationalBeforeBoarding($value['internationalBeforeBoarding']);
                    $RefundRepricing->setInternationalAfterBoarding($value['internationalAfterBoarding']);
                    $RefundRepricing->setNorthAmericaBeforeBoarding($value['northAmericaBeforeBoarding']);
                    $RefundRepricing->setNorthAmericaAfterBoarding($value['northAmericaAfterBoarding']);
                    $RefundRepricing->setSouthAmericaBeforeBoarding($value['southAmericaBeforeBoarding']);
                    $RefundRepricing->setSouthAmericaAfterBoarding($value['southAmericaAfterBoarding']);

                    $RefundRepricing->setNationalBeforeBoardingCost($value['nationalBeforeBoardingCost']);
                    $RefundRepricing->setNationalAfterBoardingCost($value['nationalAfterBoardingCost']);
                    $RefundRepricing->setNorthAmericaBeforeBoardingCost($value['northAmericaBeforeBoardingCost']);
                    $RefundRepricing->setNorthAmericaAfterBoardingCost($value['northAmericaAfterBoardingCost']);
                    $RefundRepricing->setInternationalBeforeBoardingCost($value['internationalBeforeBoardingCost']);
                    $RefundRepricing->setInternationalAfterBoardingCost($value['internationalAfterBoardingCost']);
                    $RefundRepricing->setSouthAmericaBeforeBoardingCost($value['southAmericaBeforeBoardingCost']);
                    $RefundRepricing->setSouthAmericaAfterBoardingCost($value['southAmericaAfterBoardingCost']);

                    $RefundRepricing->setAirline($Airline);
                    $RefundRepricing->setOperationPlan($AirlineOperationsPlan);

                    $em->persist($RefundRepricing);
                    $em->flush($RefundRepricing);

                    $found = $found.$and.$RefundRepricing->getId();
                    $and = ',';
                }

                $sql = "delete FROM RefundRepricing r WHERE r.id not in (".$found.") and r.airline = ".$Airline->getId()." and r.operationPlan = '".$AirlineOperationsPlan->getId()."' ";
                $query = $em->createQuery($sql);
                $result = $query->getResult();
            }

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

    public function loadPlanControlDetails(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $Sale = $em->getRepository('Sale')->findOneBy(array('id' => $dados['id']));
        $Businesspartner = $Sale->getClient();
        $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $dados['airline']));
        if($Businesspartner) {

            if($Businesspartner->getOperationPlan()) {
                $Repricing = $em->getRepository('RefundRepricing')->findOneBy(
                    array(
                        'operationPlan' => $Businesspartner->getOperationPlan()->getId(),
                        'airline' => $Airline->getId(),
                        'type' => 'Remarcação'
                    )
                );
                $Refund = $em->getRepository('RefundRepricing')->findOneBy(
                    array(
                        'operationPlan' => $Businesspartner->getOperationPlan()->getId(),
                        'airline' => $Airline->getId(),
                        'type' => 'Reembolso'
                    )
                );
            }
        }
        if(!isset($Repricing)) {
            $Repricing = $em->getRepository('RefundRepricing')->findOneBy(
                array(
                    'operationPlan' => 1,
                    'airline' => $Airline->getId(),
                    'type' => 'Remarcação'
                )
            );
        }
        if(!isset($Refund)) {
            $Refund = $em->getRepository('RefundRepricing')->findOneBy(
                array(
                    'operationPlan' => 1,
                    'airline' => $Airline->getId(),
                    'type' => 'Reembolso'
                )
            );
        }

        $actualDate = (new \DateTime())->modify('+3 hour');
        if($dados['airline'] == 'AVIANCA') {
            $actualDate = (new \DateTime())->modify('+5 hour');
        }
        $selected = new \DateTime($dados['boardingDate']);

        $refund = 0;
        $repricing = 0;

        if($Refund) {
            if($actualDate < $selected) {
                if($dados['international'] == 'true') {
                    if($dados['airportLocation'] == "America Norte" && $Refund->getNorthAmericaBeforeBoarding() != 0) {
                        $refund = $Refund->getNorthAmericaBeforeBoarding();
                    } else if($dados['airportLocation'] == "America Sul" && $Refund->getSouthAmericaBeforeBoarding() != 0) {
                        $refund = $Refund->getSouthAmericaBeforeBoarding();
                    } else {
                        $refund = $Refund->getInternationalBeforeBoarding();
                    }
                } else {
                    $refund = $Refund->getNationalBeforeBoarding();
                }
            } else {
                if($dados['international'] == 'true') {
                    if($dados['airportLocation'] == "America Norte" && $Refund->getNorthAmericaAfterBoarding() != 0) {
                        $refund = $Refund->getNorthAmericaAfterBoarding();
                    } else if($dados['airportLocation'] == "America Sul" && $Refund->getSouthAmericaAfterBoarding() != 0) {
                        $refund = $Refund->getSouthAmericaAfterBoarding();
                    } else {
                        $refund = $Refund->getInternationalAfterBoarding();
                    }
                } else {
                    $refund = $Refund->getNationalAfterBoarding();
                }
            }
        }

        if($Repricing) {
            if($actualDate < $selected) {
                if($dados['international'] == 'true') {
                    if($dados['airportLocation'] == "America Norte" && $Repricing->getNorthAmericaBeforeBoarding() != 0) {
                        $repricing = $Repricing->getNorthAmericaBeforeBoarding();
                    } else if($dados['airportLocation'] == "America Sul" && $Repricing->getSouthAmericaBeforeBoarding() != 0) {
                        $repricing = $Repricing->getSouthAmericaBeforeBoarding();
                    } else {
                        $repricing = $Repricing->getInternationalBeforeBoarding();
                    }
                } else {
                    $repricing = $Repricing->getNationalBeforeBoarding();
                }
            } else {
                if($dados['international'] == 'true') {
                    if($dados['airportLocation'] == "America Norte" && $Repricing->getNorthAmericaAfterBoarding() != 0) {
                        $repricing = $Repricing->getNorthAmericaAfterBoarding();
                    } else if($dados['airportLocation'] == "America Sul" && $Repricing->getSouthAmericaAfterBoarding() != 0) {
                        $repricing = $Repricing->getSouthAmericaAfterBoarding();
                    } else {
                        $repricing = $Repricing->getInternationalAfterBoarding();
                    }
                } else {
                    $repricing = $Repricing->getNationalAfterBoarding();
                }
            }
        }

        $dataset = array(
            'refund' => $refund,
            'repricing' => $repricing
        );

        $response->setDataset($dataset);
    }
}