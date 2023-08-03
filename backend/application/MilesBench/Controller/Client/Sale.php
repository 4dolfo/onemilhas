<?php

namespace MilesBench\Controller\Client;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Sale {

    public function loadOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
        $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'C'));

        $sql = "select s FROM Sale s where s.client = '".$UserPartner->getId()."' ORDER BY s.id DESC";
        $query = $em->createQuery($sql);
        $order = $query->getResult();

        $dataset = array();

        foreach($order as $item) {
            $airportFrom = '';
            if($item->getAirportFrom() != null){
                $airportFrom = $item->getAirportFrom()->getCode();
            }

            $airportTo = '';
            if($item->getAirportTo() != null){
                $airportTo = $item->getAirportTo()->getCode();
            }

            $airline = '';
            if($item->getAirline()) {
                $airline = $item->getAirline()->getName();
            }

            $commission = (float)$item->getAmountPaid();
            $status = 'Emitido';
            if($item->getStatus() == 'Reembolso Solicitado' || $item->getStatus() == 'Reembolso Pagante Solicitado' || $item->getStatus() == 'Reembolso Confirmado' || $item->getStatus() == 'Reembolso CIA' || $item->getStatus() == 'Reembolso Pendente' || $item->getStatus() == 'Reembolso Nao Solicitado' || $item->getStatus() == 'Reembolso Perdido') {
                $status = 'Reembolso';
                $commission = 0;
            }
            if($item->getStatus() == 'Remarcação Solicitado' || $item->getStatus() == 'Remarcação Confirmado') {
                $status = 'Remarcação';
            }
            if($item->getStatus() == 'Cancelamento Solicitado' || $item->getStatus() == 'Cancelamento Nao Solicitado' || $item->getStatus() == 'Cancelamento Efetivado' || $item->getStatus() == 'Cancelamento Pendente') {
                $status = 'Cancelamento';

                $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy(array('sale' => $item->getId()));
                $change = false;
                foreach ($SaleBillsreceive as $BillsReceives) {
                    if($BillsReceives->getBillsreceive()->getAccountType() == 'Cancelamento') {
                        $change = true;
                        $commission = (float)$BillsReceives->getBillsreceive()->getActualValue();
                    }
                }
                if(!$change) {
                    $commission = 0;
                }
            }

            $paxNmae = $item->getPax()->getName();
            if($item->getPax()->getBirthdate()) {

                $birthDate = explode("/", $item->getPax()->getBirthdate()->format('m/d/Y'));

                //get age from boarding or birthdate
                $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > $item->getBoardingDate()->format('md')
                ? (($item->getBoardingDate()->format('Y') - $birthDate[2]) - 1)
                : ($item->getBoardingDate()->format('Y') - $birthDate[2]));

                if($age < 2) {
                    $paxNmae = $paxNmae.' - COLO';
                    $commission = 0;
                }
            }

            $dataset[] = array(
                'airline' => $airline,
                'from' => $airportFrom,
                'to' => $airportTo,
                'amountPaid' => (float)$item->getAmountPaid(),
                'paxName' => $paxNmae,
                'client' => $item->getClient()->getName(),
                'company_name' => $item->getClient()->getCompanyName(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'status' => $status,
                'tax' => (float)$item->getTax(),
                'duTax' => (float)$item->getDuTax(),
                'commission' => $commission,
                'flightLocator' => $item->getFlightLocator()
            );
        }

        $response->setDataset($dataset);
    }
}
