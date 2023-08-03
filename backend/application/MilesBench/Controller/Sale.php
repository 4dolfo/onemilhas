<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class sale {

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['order'])) {
            $order = $dados['order'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $changes = '';
        $and = '';

        try {
            $em = Application::getInstance()->getEntityManager();
            $em->getConnection()->beginTransaction();

            if (isset($dados['id'])) {
                $originalSale = $em->getRepository('Sale')->find($dados['id']);
            }
            if(isset($order['client_name'])) {
                $client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));    
            }

            if (isset($dados['cardNumber'])){
                $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
                $sale_cards = $Cards;
            }
            
            if(isset($dados['miles_used']) && $dados['miles_used'] != ''){
                $milesUsed = $dados['miles_used'];
            }

            if (isset($dados['id'])) {
                $Sale = $em->getRepository('Sale')->find($dados['id']);

                $BusinessPartner = $originalSale->getPax();
                $sale_pax = $BusinessPartner;
                if (isset($dados['paxName']) && $dados['paxName'] != ''){

                    if($originalSale->getPax()->getName() != $dados['paxName']) {
                        $BusinessPartnerPax = $em->getRepository('Businesspartner')->findOneBy(array('partnerType' => 'X', 'name' => $dados['paxName']));
                        if(!$BusinessPartnerPax) {
                            $BusinessPartnerPax = new \Businesspartner();
                            $BusinessPartnerPax->setName($dados['paxName']);
                            $BusinessPartnerPax->setBirthdate($originalSale->getPax()->getBirthdate());
                            $BusinessPartnerPax->setRegistrationCode($originalSale->getPax()->getRegistrationCode());
                            $BusinessPartnerPax->setPartnerType('X');
                            $em->persist($BusinessPartnerPax);
                            $em->flush($BusinessPartnerPax);
                        }
                        if($BusinessPartnerPax) {
                            $changes = $changes.$and." Nome Pax alterado de: '".$originalSale->getPax()->getName()."'' para: '".$dados['paxName']."'";
                            $and = ';';
    
                            if($originalSale->getExternalId()) {
                                $OnlinePax = $em->getRepository('OnlinePax')->findBy(array('order' => $originalSale->getExternalId(), 'paxName' => $originalSale->getPax()->getName()));
                                foreach ($OnlinePax as $pax) {
                                    $pax->setPaxName($dados['paxName']);
                                    $em->persist($pax);
                                    $em->flush($pax);
                                }
                            }
                            $sale_pax = $BusinessPartnerPax;
                        }
                    }
                }
            
                if (isset($dados['partnername'])){
                    $MilesPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partnername']));
                    $Sale->setPartner($MilesPartner);
                }
                
                if($originalSale->getFlightLocator() != $dados['flightLocator']){
                    $changes = $changes.$and." Localizador alterado de: '".$originalSale->getFlightLocator()."'' para: '".$dados['flightLocator']."'";
                    $and = ';';
                    $Sale->setFlightLocator($dados['flightLocator']);
                }

                if($originalSale->getDescription() != $dados['description']){
                    $changes = $changes.$and." Observação alterado de: '".$originalSale->getDescription()."'' para: '".$dados['description']."'";
                    $and = ';';
                    $Sale->setDescription($dados['description']);
                }

                if($originalSale->getFlight() != $dados['flight']){
                    $changes = $changes.$and." Voo alterado de: '".$originalSale->getFlight()."'' para: '".$dados['flight']."'";
                    $and = ';';
                    $Sale->setFlight($dados['flight']);
                }

                if($originalSale->getBoardingDate()->format('Y-m-d H:i:s') != $dados['boardingDate']){
                    $changes = $changes.$and." Embarque alterado de: '".$originalSale->getBoardingDate()->format('Y-m-d H:i:s')."'' para: '".$dados['boardingDate']."'";
                    $and = ';';
                    $Sale->setBoardingDate(new \DateTime($dados['boardingDate']));
                }

                if($originalSale->getLandingDate()->format('Y-m-d H:i:s') != $dados['landingDate']){
                    $changes = $changes.$and." Desembarque alterado de: '".$originalSale->getLandingDate()->format('Y-m-d H:i:s')."'' para: '".$dados['landingDate']."'";
                    $and = ';';
                    $Sale->setLandingDate(new \DateTime($dados['landingDate']));
                }

                if(isset($dados['paxBirthdate'])) {
                    if($dados['paxBirthdate'] != '') {
                        if($originalSale->getPax()->getBirthdate()) {
                            if($originalSale->getPax()->getBirthdate()->format('Y-m-d') != $dados['paxBirthdate']){
                                $changes = $changes.$and." Data nascimento pax alterado de: '".$originalSale->getPax()->getBirthdate()->format('Y-m-d')."'' para: '".$dados['paxBirthdate']."'";
                                $and = ';';
            
                                $OnlinePax = $originalSale->getOnlinePax();
                                if($OnlinePax) {
                                    $OnlinePax->setBirthdate(new \DateTime($dados['paxBirthdate']));
                                    $em->persist($OnlinePax);
                                    $em->flush($OnlinePax);
                                }
            
                                $BusinessPartnerPax = $originalSale->getPax();
                                $BusinessPartnerPax->setBirthdate(new \DateTime($dados['paxBirthdate']));
                                $em->persist($BusinessPartnerPax);
                                $em->flush($BusinessPartnerPax);
                            }
                        } else {
                            if(isset($dados['paxBirthdate'])) {
                                $OnlinePax = $originalSale->getOnlinePax();
                                if($OnlinePax) {
                                    $OnlinePax->setBirthdate(new \DateTime($dados['paxBirthdate']));
                                    $em->persist($OnlinePax);
                                    $em->flush($OnlinePax);
                                }
            
                                $BusinessPartnerPax = $originalSale->getPax();
                                $BusinessPartnerPax->setBirthdate(new \DateTime($dados['paxBirthdate']));
                                $em->persist($BusinessPartnerPax);
                                $em->flush($BusinessPartnerPax);
                            }
                        }
                    }
                }

                if($originalSale->getPax()->getRegistrationCode() != $dados['paxRegistrationCode']){
                    $changes = $changes.$and." CPF PAX alterado de: '".$originalSale->getPax()->getRegistrationCode()."'' para: '".$dados['paxRegistrationCode']."'";
                    $and = ';';

                    $BusinessPartnerPax = $originalSale->getPax();
                    $BusinessPartnerPax->setRegistrationCode($dados['paxRegistrationCode']);
                    $em->persist($BusinessPartnerPax);
                    $em->flush($BusinessPartnerPax);
                }

                if($originalSale->getFlightHour() != $dados['flightHour']){
                    $changes = $changes.$and." Horário/Trajeto alterado de: '".$originalSale->getFlightHour()."'' para: '".$dados['flightHour']."'";
                    $and = ';';
                    $Sale->setFlightHour($dados['flightHour']);
                }

                if($originalSale->getCheckinState() != $dados['checkinState']){
                    $changes = $changes.$and." Checkin alterado";
                    $and = ';';
                    $Sale->setCheckinState($dados['checkinState']);
                }

                if($originalSale->getMilesOriginal() != $dados['milesOriginal']) {
                    $changes = $changes.$and." Milhas originais alteradas de '".$originalSale->getMilesOriginal()."' para '".$dados['milesOriginal']."' ";
                    $and = ';';
                    $Sale->setMilesOriginal($dados['milesOriginal']);
                }

                if($originalSale->getTax() != $dados['tax']){
                    $changes = $changes.$and." Taxa de aeroporto alterada de: '".$originalSale->getTax()."'' para: '".$dados['tax']."'";
                    $and = ';';
                    $Sale->setTax($dados['tax']);
                }

                if($originalSale->getTaxBillet() != $dados['tax_billet']){
                    $changes = $changes.$and." Taxa real alterada de: '".$originalSale->getTaxBillet()."'' para: '".$dados['tax_billet']."'";
                    $and = ';';
                    $Sale->setTaxBillet($dados['tax_billet']);
                }

                if($originalSale->getSaleByThird() == 'Y') {
                    if($originalSale->getProviderSaleByThird()->getName() != $dados['sale_method']) {
                        $changes = $changes.$and." Venda por terceiros alterada de: '".$originalSale->getProviderSaleByThird()->getName()."'' para: '".$dados['sale_method']."'";
                        $and = ';';
                        $Sale->setProviderSaleByThird($em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['sale_method'])));
                    }
                }

                if($originalSale->getDuTax() != $dados['duTax']){
                    $changes = $changes.$and." Taxa DU alterada de: '".$originalSale->getDuTax()."'' para: '".$dados['duTax']."'";
                    $and = ';';
                    $Sale->setDuTax($dados['duTax']);
                }

                if($originalSale->getMilesMoney() != $dados['miles_money']){
                    $changes = $changes.$and." Milhas Money alterada de: '".$originalSale->getMilesMoney()."'' para: '".$dados['miles_money']."'";
                    $and = ';';
                    $Sale->setMilesMoney($dados['miles_money']);
                }

                if($originalSale->getTotalCost() != $dados['totalCost']){
                    $changes = $changes.$and." Custo total alterado de: '".$originalSale->getTotalCost()."'' para: '".$dados['totalCost']."'";
                    $and = ';';

                    $SaleBillspay = $em->getRepository('SaleBillspay')->findBy(array('sale' => $originalSale->getId()));
                    foreach ($SaleBillspay as $sales) {
                        if($sales->getBillspay()->getAccountType() == 'Venda por Parceiro' || $sales->getBillspay()->getAccountType() == 'Taxas') {
                            $Billspay = $sales->getBillspay();
                        }
                    }
                    if($Billspay) {
                        $Billspay->setActualValue($dados['totalCost']);
                        $em->persist($Billspay);
                        $em->flush($Billspay);
                    }

                    $Sale->setTotalCost($dados['totalCost']);
                }

                if($originalSale->getKickback() != $dados['kickback']){
                    if($dados['kickback'] != 'NaN') {
                        $changes = $changes.$and."";
                        $and = ';';
                        $Sale->setKickback($dados['kickback']);
                    }
                }

                if($originalSale->getExtraFee() != $dados['extraFee']){
                    $changes = $changes.$and." Taxa extra alterada de: '".$originalSale->getExtraFee()."'' para: '".$dados['extraFee']."'";
                    $and = ';';
                    $Sale->setExtraFee($dados['extraFee']);
                }

                if($originalSale->getMilesUsed() != $dados['milesused']){
                    $changes = $changes.$and." Milhas utilizadas alterada de: '".$originalSale->getMilesUsed()."'' para: '".$dados['milesused']."'";
                    $and = ';';
                    $Cards = $originalSale->getCards();
                    if($Cards) {
                        $removedMiles = Miles::addMiles($em, $Cards->getId(), $originalSale->getMilesUsed(), $originalSale->getId(), 'CHANGE', $UserPartner, 0);
                        $removedMiles = Miles::removeMiles($em, $Cards->getId(), $dados['milesused'], $originalSale->getId());

                        $sale_cards = $Cards;
                    }
                    $Sale->setMilesUsed($dados['milesused']);
                }

                if(isset($dados['ticket_code']) && $originalSale->getTicketCode() != $dados['ticket_code']){
                    $changes = $changes.$and." E-Ticket alterado de: '".$originalSale->getTicketCode()."'' para: '".$dados['ticket_code']."'";
                    $and = ';';
                    $Sale->setTicketCode($dados['ticket_code']);
                }

                if(isset($dados['reservation_code']) && $originalSale->getReservationCode() != $dados['reservation_code']){
                    $changes = $changes.$and." Codigo de reserva de: '".$originalSale->getReservationCode()."'' para: '".$dados['reservation_code']."'";
                    $and = ';';
                    $Sale->setReservationCode($dados['reservation_code']);
                }

                if($originalSale->getAirportFrom()) {
                    if(isset($dados['airportNamefrom']) && $dados['airportNamefrom'] != '' && $originalSale->getAirportFrom()->getName() != $dados['airportNamefrom']){
                        $changes = $changes.$and." Aeroporto Origem alterado de: '".$originalSale->getAirportFrom()->getName()."'' para: '".$dados['airportNamefrom']."'";
                        $and = ';';
    
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' =>  substr($dados['airportNamefrom'],0,3))));
                    }
                } else {
                    if(isset($dados['airportNamefrom']) && $dados['airportNamefrom'] != '') {
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' =>  substr($dados['airportNamefrom'],0,3))));
                    }
                }

                if($originalSale->getAirportTo()) {
                    if(isset($dados['airportNameto']) && $dados['airportNameto'] != '' && $originalSale->getAirportTo()->getName() != $dados['airportNameto']){
                        $changes = $changes.$and." Aeroporto destino alterado de: '".$originalSale->getAirportTo()->getName()."'' para: '".$dados['airportNameto']."'";
                        $and = ';';
    
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airportNameto'],0,3) )));
                    }
                } else {
                    if(isset($dados['airportNameto']) && $dados['airportNameto'] != '') {
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airportNameto'],0,3) )));
                    }
                }

                if(isset($dados['baggage_price']) && $originalSale->getBaggagePrice() != $dados['baggage_price']){
                    $changes = $changes.$and." Valor Bagagem de: '".$originalSale->getBaggagePrice()."'' para: '".$dados['baggage_price']."'";
                    $and = ';';
                    $Sale->setBaggagePrice($dados['baggage_price']);
                }

                if(isset($dados['special_seat']) && $originalSale->getSpecialSeat() != $dados['special_seat']){
                    $changes = $changes.$and." Assento conforto de: '".$originalSale->getSpecialSeat()."'' para: '".$dados['special_seat']."'";
                    $and = ';';
                    $Sale->setSpecialSeat($dados['special_seat']);
                }

                if($originalSale->getCardTax()) {
                    if(isset($dados['cardTax']) && $originalSale->getCardTax()->getCardNumber() != $dados['cardTax']){
                        $creditCard = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $dados['cardTax']));
                        if($creditCard) {
                            $changes = $changes.$and." Cartão de Credito alterado de: '".$originalSale->getCardTax()->getCardNumber()."'' para: '".$dados['cardTax']."'";
                            $and = ';';

                            $sql = "select s FROM SystemLog s WHERE s.logType = 'CREDITCARD' and s.description like '%Venda n:".$Sale->getId()."%' ";
                            $query = $em->createQuery($sql);
                            $SystemLog = $query->getResult();

                            foreach ($SystemLog as $log) {
                                $em->remove($log);
                                $em->flush($log);
                            }

                            $Sale->setCardTax($creditCard);
                        }
                    }
                }
                else if (isset($dados['cardTax'])){
                    $creditCard = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $dados['cardTax']));
                    if($creditCard) {
                            $changes = $changes.$and." Cartão de Credito alterado de: 'null' para: '".$dados['cardTax']."'";
                            $and = ';';

                            $sql = "select s FROM SystemLog s WHERE s.logType = 'CREDITCARD' and s.description like '%Venda n:".$Sale->getId()."%' ";
                            $query = $em->createQuery($sql);
                            $SystemLog = $query->getResult();

                            foreach ($SystemLog as $log) {
                                $em->remove($log);
                                $em->flush($log);
                            }

                        $Sale->setCardTax($creditCard);
                    }
                }

                if($originalSale->getAmountPaid() != $dados['amountPaid']){
                    $changes = $changes.$and." Valor Alterado de: '".$originalSale->getAmountPaid()."'' para: '".$dados['amountPaid']."'";
                    $and = ';';

                    $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('sale' => $originalSale->getId()));
                    $billsreceive = $SaleBillsreceive->getBillsreceive();

                    if($billsreceive->getStatus() == "A") {
                        $billsreceive->setOriginalValue($dados['amountPaid']);
                        $billsreceive->setActualValue($dados['amountPaid']);

                        $em->persist($billsreceive);
                        $em->flush($billsreceive);
                    } else {
                        $daysToPay = new \Datetime();

                        if($originalSale->getAmountPaid() > (float)$dados['amountPaid']){

                            $Receive = new \Billsreceive();
                            $Receive->setStatus('A');
                            $Receive->setClient($originalSale->getClient());
                            $Receive->setDescription('Passageiro '.$originalSale->getPax()->getName().' - Localizador '.$originalSale->getFlightLocator());
                            $Receive->setOriginalValue($originalSale->getAmountPaid() - $dados['amountPaid']);
                            $Receive->setActualValue($originalSale->getAmountPaid() - $dados['amountPaid']);
                            $Receive->setTax(0);
                            $Receive->setDiscount(0);
                            $Receive->setAccountType('Credito');
                            $Receive->setReceiveType('Boleto Bancario');
                            $Receive->setDueDate($daysToPay);

                        } else {

                            $Receive = new \Billsreceive();
                            $Receive->setStatus('A');
                            $Receive->setClient($originalSale->getClient());
                            $Receive->setDescription('Passageiro '.$originalSale->getPax()->getName().' - Localizador '.$originalSale->getFlightLocator());
                            $Receive->setOriginalValue($dados['amountPaid'] - $originalSale->getAmountPaid());
                            $Receive->setActualValue($dados['amountPaid'] - $originalSale->getAmountPaid());
                            $Receive->setTax(0);
                            $Receive->setDiscount(0);
                            $Receive->setAccountType('Débito');
                            $Receive->setReceiveType('Boleto Bancario');
                            $Receive->setDueDate($daysToPay);

                        }

                        $em->persist($Receive);
                        $em->flush($Receive);
                    }

                    $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $originalSale->getExternalId()));

                    if($OnlineOrder) {
                        $OnlineOrder->setTotalCost($OnlineOrder->getTotalCost() - ($originalSale->getAmountPaid() - $dados['amountPaid']));

                        $em->persist($OnlineOrder);
                        $em->flush($OnlineOrder);
                    }

                    $Sale->setAmountPaid($dados['amountPaid']);
                }

                if($originalSale->getCards()) {
                    if($originalSale->getCards()->getBusinesspartner()->getName() != $dados['providerName']) {

                        $Cards = $originalSale->getCards();
                        if($Cards) {
                            $changes = $changes.$and." Fornecedor alterado de: '".$originalSale->getCards()->getBusinesspartner()->getName()."'' para: '".$dados['providerName']."'";
                            $and = ';';

                            $removedMiles = Miles::addMiles($em, $Cards->getId(), $Sale->getMilesUsed(), $Sale->getId(), 'CHANGE', $UserPartner, 0);

                            $sale_cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
                            $removedMiles = Miles::removeMiles($em, $sale_cards->getId(), $Sale->getMilesUsed(), $originalSale->getId());

                            $Sale->setCards($sale_cards);
                        }
                    }
                }

                $ClientPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client'], 'partnerType' => 'C'));
                if($ClientPartner && $originalSale->getClient() != $ClientPartner){

                    $changes = $changes.$and." Cliente alterado de: '".$originalSale->getClient()->getName()."'' para: '".$dados['client']."'";
                    $and = ';';

                    $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('sale' => $originalSale->getId()));
                    if($SaleBillsreceive) {

                        $billsreceive = $SaleBillsreceive->getBillsreceive();

                        if($billsreceive->getStatus() == "A") {
                            $billsreceive->setClient($ClientPartner);

                            $em->persist($billsreceive);
                            $em->flush($billsreceive);
                        } else {
                            $Billsreceive = new \Billsreceive();
                            $Billsreceive->setStatus('A');
                            $Billsreceive->setClient($ClientPartner);
                            $Billsreceive->setDescription('Passageiro '.$originalSale->getPax()->getName().' - Localizador '.$originalSale->getFlightLocator());
                            $Billsreceive->setOriginalValue($Sale->getAmountPaid());
                            $Billsreceive->setActualValue($Sale->getAmountPaid());
                            $Billsreceive->setTax(0);
                            $Billsreceive->setDiscount(0);
                            $Billsreceive->setAccountType('Venda Bilhete');
                            $Billsreceive->setReceiveType('Boleto Bancario');
                            $Billsreceive->setDueDate(new \Datetime());
                            $em->persist($Billsreceive);
                            $em->flush($Billsreceive);

                            $Billsreceive = new \Billsreceive();
                            $Billsreceive->setStatus('A');
                            $Billsreceive->setClient($Sale->getClient());
                            $Billsreceive->setDescription('Passageiro '.$originalSale->getPax()->getName().' - Localizador '.$originalSale->getFlightLocator());
                            $Billsreceive->setOriginalValue($Sale->getAmountPaid());
                            $Billsreceive->setActualValue($Sale->getAmountPaid());
                            $Billsreceive->setTax(0);
                            $Billsreceive->setDiscount(0);
                            $Billsreceive->setAccountType('Credito');
                            $Billsreceive->setReceiveType('Boleto Bancario');
                            $Billsreceive->setDueDate(new \Datetime());
                            $em->persist($Billsreceive);
                            $em->flush($Billsreceive);
                        }

                    }

                    $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $originalSale->getExternalId()));
                    if($OnlineOrder) {
                        $OnlineOrder->setClientName($ClientPartner->getName());

                        $em->persist($OnlineOrder);
                        $em->flush($OnlineOrder);
                    }

                    $Sale->setClient($ClientPartner);
                }

                $Sale->setPax($sale_pax);
                $Sale->setCards($sale_cards);
                if(isset($milesUsed)){
                    $Sale->setMilesUsed($milesUsed);
                }

                if (isset($dados['description'])) {
                    $Sale->setDescription($dados['description']);
                }

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                // reopening sale to conference
                if($Sale->getSaleChecked() == 'true' || $Sale->getSaleChecked2() == 'true') {
                    $Sale->setSaleChecked('false');
                    $Sale->setSaleChecked2('false');
                    $Sale->setSaleCheckedDate(null);
                    $Sale->setSaleCheckedDate2(null);
                }

                $email1 = 'adm@onemilhas.com.br';
                $postfields = array(
                    'content' => "Alteração de dados <br> Localizador:".$Sale->getFlightLocator()." <br> Venda n:".$Sale->getId()." <br> Usuario:".$BusinessPartner->getName().$and.' '.$changes,
                    'partner' => $email1,
                    'subject' => 'Alteração em venda',
                    'from' => $email1,
                    'type' => ''
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);

                if(isset($dados['status']) && $dados['status'] != '') {
                    if($dados['status'] != $originalSale->getStatus()) {

                        $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('sale' => $originalSale->getId()));
                        foreach ($SaleBillsreceive as $item) {
                            $Billsreceive = $item->getBillsreceive();
                            if($Billsreceive->getStatus() == 'A') {

                                $em->remove($item);
                                $em->flush($item);

                                $em->remove($Billsreceive);
                                $em->flush($Billsreceive);
                            }
                        }
                        $Sale->setStatus($dados['status']);
                    }
                }

                $Sale->setFlight($dados['flight']);
                $Sale->setFlightHour($dados['flightHour']);
                $em->persist($Sale);
                $em->flush($Sale);

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Alteração de dados - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName().$and.' '.$changes);
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function ClosecancelSale(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $BusinessPartner = $dados['businesspartner'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $Sale = $em->getRepository('Sale')->find($dados['id']);

            if($Sale->getStatus() != 'Cancelamento Efetivado') {
                $Sale->setStatus('Cancelamento Efetivado');

                if(isset($dados['_returnPointsDate']) && $dados['_returnPointsDate'] != '') {
                    $Sale->setReturnDate(new \Datetime($dados['_returnPointsDate'] .' '.date('H:i:s')));
                }

                $em->persist($Sale);
                $em->flush($Sale);

                if(isset($dados['cardNumber']) && $dados['cardNumber'] != ''){
                    $removedMiles = Miles::addMiles($em, $dados['cards_id'], (float)$Sale->getMilesUsed(), $Sale->getId(), 'CANCEL', $BusinessPartner, 0);
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Cancelamento - Venda n:".$dados['id']." - Confirmação de Cancelamento");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
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

    public function cancelSale(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
		if (isset($dados['data'])) {
			$dados = $dados['data'];
        }
        
        $em = Application::getInstance()->getEntityManager();
        $newOrder = false;

        try {
            $em->getConnection()->beginTransaction();            
            $Sale = $em->getRepository('Sale')->find($dados['id']);
            
            if($Sale->getStatus() != 'Cancelamento Solicitado') {

                $Sale->setStatus('Cancelamento Solicitado');

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $description = "Cancelamento - Venda n:".$dados['id'];
                if(isset($dados['CancelReason']) && $dados['CancelReason'] != ''){
                    $description .= " Motivo: ".$dados['CancelReason'];
                }
                if(isset($dados['ourCost']) && $dados['ourCost'] != ''){
                    $description .= " Custo: ".$dados['ourCost'];
                }
                $SystemLog->setDescription($description);
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($UserPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);

                if(isset($dados['ourCost']) && $dados['ourCost'] != ''){
                    $Sale->setTotalCost($dados['ourCost']);
                } else {
                    $Sale->setTotalCost(0);
                }

                if(isset($dados['_cancelDate']) && $dados['_cancelDate'] != '') {
                    $Sale->setRefundDate(new \Datetime($dados['_cancelDate'].' '.date('H:i:s')));
                }

                $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('sale' => $Sale));
                if($SaleBillsreceive) {
                    $Billsreceive = $SaleBillsreceive->getBillsreceive();
                    if($Billsreceive->getStatus() == "A"){
                        
                        if(isset($dados['CancelCost']) && $dados['CancelCost'] != ''){
                            $Sale->setAmountPaid($dados['CancelCost']);
    
                            $Billsreceive->setDescription('Cancelamento - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$dados['flightLocator']);
                            $Billsreceive->setOriginalValue($dados['CancelCost']);
                            $Billsreceive->setActualValue($dados['CancelCost']);
                            $Billsreceive->setTax(0);
                            $Billsreceive->setDiscount(0);
                            $Billsreceive->setAccountType('Cancelamento');
                            
                            $em->persist($Billsreceive);
                            $em->flush($Billsreceive);
                        }
                    }
                }

                $em->persist($Sale);
                $em->flush($Sale);
            }

            // if($Sale->getExternalId()) {
            //     $onlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $Sale->getExternalId()));
            //     if($onlineOrder) {
            //         if($onlineOrder->getNotificationurl()) {
            //             $jsonToPost = array(
            //                 'notificationCode' => $onlineOrder->getNotificationcode(),
            //                 'status' => '3',
            //                 'hashId' => "fd0ab7097fb7119900febac7e3875218"
            //             );
            //             $ch = curl_init();
            //             curl_setopt($ch, CURLOPT_URL, $onlineOrder->getNotificationurl());
            //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            //             curl_setopt($ch, CURLOPT_POST, 1);
            //             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
            //             $result = curl_exec($ch);
            //         }
            //     }
            // }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveCheckInStatus(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            foreach ($dados as $saleData) {

                $Sale = $em->getRepository('Sale')->find($saleData['id']);
                if($Sale) {
                    if($saleData['checkinState'] == 'false' && $Sale->getCheckinDate() == NULL) {
                    } else {
                        if($saleData['checkinState'] == 'true' && ($Sale->getCheckinDate() == NULL || $Sale->getCheckinDate()->format('Y-m-d') != (new \DateTime())->format('Y-m-d'))) {

                            $SystemLog = new \SystemLog();
                            $SystemLog->setIssueDate(new \Datetime());
                            $SystemLog->setDescription("Checkin Realizado - Venda n:".$saleData['id']." - Usuario:".$BusinessPartner->getName());
                            $SystemLog->setLogType('SALE');
                            $SystemLog->setBusinesspartner($BusinessPartner);

                            $em->persist($SystemLog);
                            $em->flush($SystemLog);

                            $Sale->setCheckinDate(new \DateTime());
                            $Sale->setCheckinState($saleData['checkinState']);

                            $em->persist($Sale);
                            $em->flush($Sale);
                        }
                    }
                }
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('CheckIn Realizado com sucesso!');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function getDateReference($textData, $field) {
        $sql = '';
        switch ($textData) {
            case 'Ultimos 30 dias':
                $sql = $field." BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()";
            case 'Mes Corrente':
                $sql = "date_format(".$field.",'%Y-%m') = date_format(NOW(),'%Y-%m')";
            case 'Semana Corrente':
                $sql = "date_format(".$field.",'%X-%V') = date_format(NOW(),'%X-%V')";
            case 'Hoje':
                $sql = "date_format(".$field.",'%Y-%m-%d') = date_format(NOW(),'%Y-%m-%d')";
        }
        return $sql;
    }

    public function loadCardFlight(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $dataset = array();
        foreach($dados as $flight){

            $em = Application::getInstance()->getEntityManager();
            $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $UserPartner->getId()));

            if(isset($flight['identification']) && $flight['identification'] != '') {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                    array('name' => $flight['pax_name'], 'registrationCode' => $flight['identification'], 'partnerType' => 'X')
                );
            } else {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $flight['pax_name'], 'partnerType' => 'X'));
            }
            
            if(isset($BusinessPartner) || isset($flight['pax_id']) ){
                if(isset($flight['pax_id'])) {
                    $sql = "select s FROM Sale s WHERE s.onlineFlightId = ".$flight['id']." and s.onlinePax = " . $flight['pax_id'] . " ";
                } else {
                    $sql = "select s FROM Sale s WHERE s.onlineFlightId = ".$flight['id']." and s.pax = '".$BusinessPartner->getId()."' ";
                }
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();
                foreach ($Sales as $item) {
                    if($item->getSaleByThird() == 'Y' || $item->getIsExtra() == 'true'){
                        if($item->getProviderSaleByThird()){
                            $card = $item->getProviderSaleByThird()->getName();
                        } else {
                            $card = '';
                        }
                        $cards_id = '';
                        $token = '';
                        $providerName = '';
                        $provider_phone = '';
                        $tax_cardType = '';
                        $tax_providerName = '';
                        $tax_card = '';
                        $access_password = '';
                        $recovery_password = '';
                        $card_type = '';
                        $phoneNumberAirline = '';
                        $celNumberAirline = '';
                    } else {
                        $card = $item->getCards()->getCardNumber();
                        $cards_id = $item->getCards()->getId();
                        $token = $item->getCards()->getToken();
                        $providerName = $item->getCards()->getBusinesspartner()->getName();
                        $provider_phone = $item->getCards()->getBusinesspartner()->getPhoneNumber();
                        $card_type = $item->getCards()->getCardType();
                        $tax_card = '';
                        $tax_cardType = '';
                        $tax_providerName = '';
                        if($item->getCardTax()) {
                            $tax_card = $item->getCardTax()->getCardNumber();
                            $tax_cardType = $item->getCardTax()->getCardType();
                            $tax_providerName = $item->getCardTax()->getShowName();
                        }
                        if(($UserPartner->getIsMaster() == 'true' || $UserPermission->getSale() == 'true' || $UserPermission->getConference() == 'true') && $item->getAirline()->getName() == 'GOL') {
                            $access_password = $item->getCards()->getAccessPassword();
                            $recovery_password = $item->getCards()->getRecoveryPassword();
                        } else {
                            $access_password = '';
                            $recovery_password = '';
                        }

                        $phoneNumberAirline = $item->getCards()->getBusinesspartner()->getPhoneNumber2();
                        if($item->getCards()->getBusinesspartner()->getPhoneNumberAirline() != NULL) {
                            $phoneNumberAirline = $item->getCards()->getBusinesspartner()->getPhoneNumberAirline();
                        }

                        $celNumberAirline = $item->getCards()->getBusinesspartner()->getPhoneNumber();
                        if($item->getCards()->getBusinesspartner()->getCelNumberAirline() != NULL) {
                            $celNumberAirline = $item->getCards()->getBusinesspartner()->getCelNumberAirline();
                        }
                    }

                    $saleCheckedDate = '';
                    if($item->getSaleChecked() == 'true') {
                        $saleCheckedDate = $item->getSaleCheckedDate()->format('Y-m-d H:i:s');
                    }

                    $issuing = '';
                    if($item->getIssuing()) {
                        $issuing = $item->getIssuing()->getName();
                    }

                    $partnerReservationCode = '';
                    if($item->getReservationCode()) {
                        $partnerReservationCode = $item->getReservationCode();
                    }

                    $tax_billet = (float)$item->getTax();
                    if((float)$item->getTaxBillet() > 0) {
                        $tax_billet = (float)$item->getTaxBillet();
                    }

                    $baggage = 0;
                    if($item->getBaggage()) {
                        $baggage = $item->getBaggage();
                    }

                    $class = '';
                    if($item->getClass()) {
                        $class = $item->getClass();
                    }

                    $saleCheckedDate2 = '';
                    if($item->getSaleChecked2() == 'true') {
                        $saleCheckedDate2 = $item->getSaleCheckedDate2()->format('Y-m-d H:i:s');
                    }

                    $pax_id = NULL;
                    if($item->getOnlinePax()) {
                        $pax_id = $item->getOnlinePax()->getId();
                    }

                    $subClientEmail = null;
                    if($item->getClient()->getSubClient() == 'true') {
                        $subClientEmail = $item->getClient()->getMasterClient()->getEmail();
                    }

                    $airline = '';
                    if($item->getAirline()) {
                        $airline = $item->getAirline()->getName();
                    }

                    $user = '';
                    if($item->getUser()) {
                        $user = $item->getUser()->getName();
                    }

                    $dataset[] = array(
                        'flight' => $item->getFlight(),
                        'pax_name' => $item->getPax()->getName(),
                        'airline' => $airline,
                        'online_flight_id' => $item->getOnlineFlightId(),
                        'cardNumber' => $card,
                        'cards_id' => $cards_id,
                        'token' => $token,
                        'miles_used' => $item->getMilesUsed(),
                        'amountPaid' => (float)$item->getAmountPaid(),
                        'miles_money' => (float)$item->getMilesMoney(),
                        'flightLocator' => $item->getFlightLocator(),
                        'duTax' => (float)$item->getDuTax(),
                        'discount' => (float)$item->getDiscount(),
                        'providerName' => $providerName,
                        'provider_phone' => $provider_phone,
                        'tax_cardType' => $tax_cardType,
                        'tax_providerName' => $tax_providerName,
                        'tax_card' => $tax_card,
                        'user' => $user,
                        'ticket_code' => $item->getTicketCode(),
                        'processing_time' => $item->getProcessingTime(),
                        'saleChecked' => ($item->getSaleChecked() == 'true'),
                        'saleCheckedDate' => $saleCheckedDate,
                        'sale_id' => $item->getId(),
                        'issuing' => $issuing,
                        'status' => $item->getStatus(),
                        'access_password' => $access_password,
                        'recovery_password' => $recovery_password,
                        'partnerReservationCode' => $partnerReservationCode,
                        'sms' => ( $item->getPartnerSms() == 'true' ),
                        'money' => (float)$item->getMilesMoney(),
                        'card_type' => $card_type,
                        'phoneNumberAirline' => $phoneNumberAirline,
                        'celNumberAirline' => $celNumberAirline,
                        'tax' => (float)$item->getTax(),
                        'tax_billet' => $tax_billet,
                        'baggage' => $baggage,
                        'class' => $class,
                        'saleChecked2' => ($item->getSaleChecked2() == 'true'),
                        'saleCheckedDate2' => $saleCheckedDate2,
                        'pax_id' => $pax_id,
                        'subClientEmail' => $subClientEmail,
                        'taxOnlinePayment' => (float)$item->getTaxOnlinePayment(),
                        'taxOnlineValidation' => (float)$item->getTaxOnlineValidation(),
                        'baggage_price'  => (float)$item->getBaggagePrice(),
                        'special_seat'  => (float)$item->getSpecialSeat()
                    );
                }
            }
        }
        $response->setDataset($dataset);
    }

    public function saveSaleCheck(Request $request, Response $response) {
        $hash = $request->getRow()['hashId'];
        $saleData = $request->getRow()['sale'];
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $saleData['airline']));
            $pax = $em->getRepository('Businesspartner')->findOneBy(array('id' => $saleData['pax_id']));
            
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $Sale = $em->getRepository('Sale')->findOneBy(
                array(
                    'id' => $saleData['sale_id']
                )
            );

            if(!$Sale) {
                $Sale = $em->getRepository('Sale')->findOneBy(
                    array(
                        'id' => $dados['id']
                    )
                );
                if($dados['saleChecked'] == 'true') {
                    $Sale->setSaleCheckedDate(new \DateTime());

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Venda Verificada - Venda n:".$dados['id']." - Usuario: ".$UserPartner->getName());
                    $SystemLog->setLogType('SALE');
                    $SystemLog->setBusinesspartner($UserPartner);
                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                } else {
                    $Sale->setSaleCheckedDate(null);
                }
                $Sale->setSaleChecked($dados['saleChecked']);
            } else {
                if($saleData['saleChecked'] == 'true') {
                    $Sale->setSaleCheckedDate(new \DateTime());

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Venda Verificada - Venda n:".$saleData['sale_id']." - Usuario: ".$UserPartner->getName());
                    $SystemLog->setLogType('SALE');
                    $SystemLog->setBusinesspartner($UserPartner);
                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                } else {
                    $Sale->setSaleCheckedDate(null);
                }
                $Sale->setSaleChecked($saleData['saleChecked']);
            }

            $em->persist($Sale);
            $em->flush($Sale);

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveSaleDoubleCheck(Request $request, Response $response) {
        if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }
        $hash = $request->getRow()['hashId'];
        $saleData = $request->getRow()['sale'];
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $saleData['airline']));
            $pax = $em->getRepository('Businesspartner')->findOneBy(array('id' => $saleData['pax_id']));

            $Sale = $em->getRepository('Sale')->findOneBy(
                array(
                    'id' => $saleData['sale_id']
                )
            );
            
            if(!$Sale) {
                $Sale = $em->getRepository('Sale')->findOneBy(
                    array(
                        'id' => $dados['id']
                    )
                );
                if($dados['saleChecked2'] == 'true') {
                    $Sale->setSaleCheckedDate2(new \DateTime());

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Venda Verificada - Venda n:".$dados['id']." - Usuario: ".$UserPartner->getName());
                    $SystemLog->setLogType('SALE');
                    $SystemLog->setBusinesspartner($UserPartner);
                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                } else {
                    $Sale->setSaleCheckedDate2(null);
                }
                $Sale->setSaleChecked2($dados['saleChecked2']);
            } else {
                if($saleData['saleChecked2'] == 'true') {
                    $Sale->setSaleCheckedDate2(new \DateTime());

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Venda Verificada - Venda n:".$saleData['sale_id']." - Usuario: ".$UserPartner->getName());
                    $SystemLog->setLogType('SALE');
                    $SystemLog->setBusinesspartner($UserPartner);
                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                } else {
                    $Sale->setSaleCheckedDate2(null);
                }
                $Sale->setSaleChecked2($saleData['saleChecked2']);
            }

            $em->persist($Sale);
            $em->flush($Sale);

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveSaleSMS(Request $request, Response $response) {
        if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }
        $hash = $request->getRow()['hashId'];
        $saleData = $request->getRow()['sale'];
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $Sale = $em->getRepository('Sale')->findOneBy(
                array(
                    'id' => $saleData['sale_id']
                )
            );

            if(!$Sale) {
                $Sale = $em->getRepository('Sale')->findOneBy(
                    array(
                        'id' => $dados['id']
                    )
                );
                $Sale->setPartnerSms($dados['sms']);
            } else {
                $Sale->setPartnerSms($saleData['sms']);
            }

            $em->persist($Sale);
            $em->flush($Sale);

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("SMS Confirmado - Venda n:".$saleData['sale_id']." - Usuario: ".$UserPartner->getName());
            $SystemLog->setLogType('SALE');
            $SystemLog->setBusinesspartner($UserPartner);
            $em->persist($SystemLog);
            $em->flush($SystemLog);


            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadSaleHistory(Request $request, Response $response) {

        $DataModficacao_hml = new \Datetime("2021-02-25 12:00:00");
        $DataModficacao = new \Datetime("2021-03-10 09:25:00");

        $dados = $request->getRow();
        $requestData = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();

        $Card = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
        $em = Application::getInstance()->getEntityManager();
        $sql = "select s FROM Sale s WHERE s.cards = '".$dados['cards_id']."' and s.status not in ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Pendente', 'Reembolso CIA', 'Reembolso No-show Solicitado', 'Cancelamento Solicitado') order by s.id DESC";

        if(isset($requestData['page']) && isset($requestData['numPerPage'])) {
            $query = $em->createQuery($sql)
                    ->setFirstResult((($requestData['page'] - 1) * $requestData['numPerPage']))
                    ->setMaxResults($requestData['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }

        $Sales = $query->getResult();
        
        $salesArray = array();
        foreach ($Sales as $sale) {
            $em = Application::getInstance()->getEntityManager();
            $businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale->getPax()->getId()));

            $refundDate = '';
            if($sale->getRefundDate()) {
                $refundDate = $sale->getRefundDate()->format('Y-m-d H:i:s');
            }

            $returnDate = '';
            if($sale->getReturnDate()) {
                $returnDate = $sale->getReturnDate()->format('Y-m-d H:i:s');
            }

            $to = '';
            $to_cod = '';
            if($sale->getAirportTo()) {
                $to = $sale->getAirportTo()->getName();
                $to_cod = $sale->getAirportTo()->getCode();
            }

            $from = '';
            $from_cod = '';
            if($sale->getAirportFrom()) {
                $from = $sale->getAirportFrom()->getName();
                $from_cod = $sale->getAirportFrom()->getCode();
            }
            
            $ticket_code = '';
            if($sale->getTicketCode()) {
                $ticket_code = $sale->getTicketCode();
            }

            //$SalePurchases = $em->getRepository('SalePurchases')->findBy(array('sale' => $sale->getId()));

            $sql2 = "select sb FROM SalePurchases sb WHERE sb.sale = '".$sale->getId()."'  ";
            $query = $em->createQuery($sql2);
            $SalePurchases = $query->getResult();

            $last = $sale->getMilesUsed();

            $spArray = array();
            if(count($SalePurchases) > 0){
                foreach($SalePurchases as $sp){
                    $Purchase = $em->getRepository('Purchase')->findOneBy(array('id' => $sp->getPurchase()->getId()));
                    $Card = $em->getRepository('Cards')->findOneBy(array('id' => $Purchase->getCards()));
                    $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Card->getBusinesspartner()));

                    $cost_per_thousand = $Purchase->getCostPerThousand();
                    if($Purchase->getCostPerThousandPurchase() != '0.00') {
                        $cost_per_thousand = $Purchase->getCostPerThousandPurchase();
                    }

                    $miles_used = $sp->getMilesUsed();
                    $is_corrigido = true;

                    if($sale->getIssueDate() < $DataModficacao){
                        $miles_used = $last - $sp->getMilesUsed();
                        if($miles_used == 0)
                            $miles_used = $sp->getMilesUsed();

                        $last = $sp->getMilesUsed();
                        $is_corrigido = false;
                    }

                    $spArray[] = array(
                        'purchase_id' => $sp->getPurchase()->getId(),
                        'miles_used' => $miles_used,
                        'miles_used_sp' => $sp->getMilesUsed(),
                        'cost_per_thousand' => $cost_per_thousand,
                        'provider_name' => $Businesspartner->getName(),
                        'is_sp' => true,
                        'is_corrigido' => $is_corrigido
                    );
                }
            } elseif($sale->getPurchase()){
                $Purchase = $em->getRepository('Purchase')->findOneBy(array('id' => $sale->getPurchase()->getId()));
                $Card = $em->getRepository('Cards')->findOneBy(array('id' => $Purchase->getCards()));
                $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Card->getBusinesspartner()));
                
                $cost_per_thousand = $Purchase->getCostPerThousand();
                if($Purchase->getCostPerThousandPurchase() != '0.00') {
                    $cost_per_thousand = $Purchase->getCostPerThousandPurchase();
                }
                
                $spArray[] = array(
                    'purchase_id' => $Purchase->getId(),
                    'miles_used' => 'ANTIGO',
                    'miles_used_sp' => 'ANTIGO',
                    'cost_per_thousand' => $cost_per_thousand,
                    'provider_name' => $Businesspartner->getName(),
                    'is_sp' => false,
                    'is_corrigido' => false
                );
            } else {
                $spArray[] = array(
                    'purchase_id' => '---',
                    'miles_used' => '---',
                    'miles_used_sp' => '---',
                    'cost_per_thousand' => '---',
                    'provider_name' => '---',
                    'is_sp' => false,
                    'is_corrigido' => false
                );
            }

            $salesArray[] = array(
                'id' => $sale->getId(),
                'issue_date' => $sale->getIssueDate()->format('Y-m-d H:i:s'),
                'pax_name' => $businesspartner->getName(),
                'flight' => $sale->getFlight(),
                'boardingDate' => $sale->getBoardingDate()->format('Y-m-d H:i:s'),
                'landingDate' => $sale->getLandingDate()->format('Y-m-d H:i:s'),
                'from' => $from,
                'to' => $to,
                'from_cod' => $from_cod,
                'to_cod' => $to_cod,
                'miles_used' => $sale->getMilesUsed(),
                'status' => $sale->getStatus(),
                'flight_locator' => $sale->getFlightLocator(),
                'refundDate' => $refundDate,
                'returnDate' => $returnDate,
                'ticket_code' => $ticket_code,
                'sale_purchases' => $spArray
            );
        }

        $sql2 = "select COUNT(s) as quant FROM sale s  WHERE s.cards = '".$dados['cards_id']."'  ";

        $query = $em->createQuery($sql2);
        $Quant = $query->getResult();
        
        $dataset = array(
            'total' => (float)$Quant[0]['quant'],
            'sales' => $salesArray
        );
        $response->setDataset($dataset);   
    }

    public function loadFutureBoardings(Request $request, Response $response) {
        if(isset($request->getRow()['data'])) {
            $dados = $request->getRow()['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        if(!isset($dados) || $dados == NULL) {
            $sql = "select s FROM Sale s ".
                " WHERE ((s.boardingDate BETWEEN '".(new \DateTime())->format('Y-m-d')."' AND '".(new \DateTime())->modify('+3 day')->format('Y-m-d')."' ".
                " and s.airline <> '2') or (s.boardingDate BETWEEN '".(new \DateTime())->format('Y-m-d')."' AND '".(new \DateTime())->modify('+7 day')->format('Y-m-d')."' and s.airline = '2') ) ".
                " and s.issueDate >= '" . (new \DateTime())->format('Y-m-d') . "' ".
                " and s.status not in ('Cancelamento Nao Solicitado', 'Cancelamento Solicitado', 'Cancelamento Efetivado', 'Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Confirmado', 'Reembolso No-show Solicitado', 'Reembolso No-show Confirmado', 'Reembolso Nao Solicitado', 'Reembolso Perdido', 'Reembolso CIA') ORDER BY s.airline DESC, s.flightLocator ";
            $query = $em->createQuery($sql);
            $order = $query->getResult();
        } else {
            $sql = "select s, a FROM Sale s JOIN s.airline a WHERE s.issueDate < '" . (new \DateTime())->format('Y-m-d') . "' ".
            " and s.status not in ('Cancelamento Nao Solicitado', 'Cancelamento Solicitado', 'Cancelamento Efetivado', 'Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Confirmado', 'Reembolso No-show Solicitado', 'Reembolso No-show Confirmado', 'Reembolso Nao Solicitado', 'Reembolso Perdido', 'Reembolso CIA') ";
            $orderBy = " ORDER BY s.airline DESC, s.flightLocator ";

            if(isset($dados['airline']) && $dados['airline'] != '') {
                $sql = $sql." and a.name like '%".$dados['airline']."%' ";
            }

            if(isset($dados['_boardingDateFrom']) && $dados['_boardingDateFrom'] != '') {
                $sql = $sql." and s.boardingDate >= '".$dados['_boardingDateFrom']."' ";
            } else {
                $sql = $sql." and (s.boardingDate >= '".(new \DateTime())->format('Y-m-d')."' ) ";
            }

            if(isset($dados['_boardingDateTo']) && $dados['_boardingDateTo'] != '') {
                $sql = $sql." and s.boardingDate <= '".$dados['_boardingDateTo']."' ";
            } else {
                $sql = $sql." and ((s.boardingDate <= '".(new \DateTime())->modify('+3 day')->format('Y-m-d')."' and s.airline <> '2') ".
                " or (s.boardingDate <= '".(new \DateTime())->modify('+7 day')->format('Y-m-d')."' and s.airline = '2') ) ";
            }

            $query = $em->createQuery($sql.$orderBy);
            $order = $query->getResult();
        }

        $dataset = array();
        foreach($order as $item){

            $checkinState = null;
            if($item->getCheckinState() == 'true') {
                if($item->getCheckinDate()->format('Y-m-d') == (new \DateTime())->format('Y-m-d')) {
                    $checkinState = true;
                }
            } else {
                $checkinState = false;
            }

            $occurrence = 'Ocorrencia';
            if($item->getOccurrenceStatus() != NULL) {
                $occurrence = $item->getOccurrenceStatus();
            }

            $airportFrom = '';
            if($item->getAirportFrom()) {
                $airportFrom = $item->getAirportFrom()->getCode();
            }
            $airportTo = '';
            if($item->getAirportTo()) {
                $airportTo = $item->getAirportTo()->getCode();
            }

            $providerName = '';
            $providerRegistrationCode = '';
            $providerPassword = '';
            $providerEmail = '';
            $providerPhone = '';
            $providerCardNumber = '';
            $providerAccessPassword = '';
            if($item->getCards()) {
                $providerName = $item->getCards()->getBusinesspartner()->getName();
                $providerRegistrationCode = $item->getCards()->getBusinesspartner()->getRegistrationCode();
                $providerEmail = $item->getCards()->getBusinesspartner()->getEmail();
                $providerPhone = $item->getCards()->getBusinesspartner()->getPhoneNumber();
                $providerCardNumber = $item->getCards()->getCardNumber();
                $providerAccessPassword = $item->getCards()->getAccessPassword();
                $providerPassword = $item->getCards()->getRecoveryPassword();
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'airline' => $item->getAirline()->getName(),
                'paxName' => $item->getPax()->getName(),
                'from' => $airportFrom,
                'to' => $airportTo,
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'flight' => $item->getFlight(),
                'flightLocator' => $item->getFlightLocator(),
                'checkinState' => $checkinState,
                'ticket_code' => $item->getTicketCode(),
                'occurrence' => $occurrence,
                'providerName' => $providerName,
                'providerPassword' => $providerPassword,
                'providerEmail' => $providerEmail,
                'providerPhone' => $providerPhone,
                'providerRegistrationCode' => $providerRegistrationCode,
                'providerCardNumber' => $providerCardNumber,
                'providerAccessPassword' => $providerAccessPassword
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientsSales(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        
        if (isset($dados['filter'])) {
            $filter = $dados['filter'];
        }

        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $sql = "select count(s) as cont, SUM(s.totalCost) as custo, MAX(s.client) as client, SUM(s.milesOriginal) as miles FROM Sale s JOIN s.cards c JOIN c.businesspartner b ";
        $whereClause = ' WHERE ';
        $and = '';
        $orderBy = ' GROUP by s.client';

        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause. " b.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['cardNumber']) && !($dados['cardNumber'] == '')) {
            $whereClause = $whereClause.$and. "c.cardNumber = '".$dados['cardNumber']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_boardingDateFrom']) && !($dados['_boardingDateFrom'] == '')) {
            $whereClause = $whereClause.$and. " s.boardingDate >= '".$dados['_boardingDateFrom']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_boardingDateTo']) && !($dados['_boardingDateTo'] == '')) {
            $whereClause = $whereClause.$and. " s.boardingDate <= '".$dados['_boardingDateTo']."' ";
            $and = ' AND ';
        };

        if (isset($dados['status']) && !($dados['status'] == '')) {
            $whereClause = $whereClause.$and. " s.status = '".$dados['status']."' ";
            $and = ' AND ';
        };

        if (isset($dados['flightLocator']) && !($dados['flightLocator'] == '')) {
            $whereClause = $whereClause.$and. " s.flightLocator = '".$dados['flightLocator']."' ";
            $and = ' AND ';
        };

        if (isset($dados['externalid']) && !($dados['externalid'] == '')) {
            $whereClause = $whereClause.$and. " s.externalId = '".$dados['externalid']."' ";
            $and = ' AND ';
        };

        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause; 
        };

        $query = $em->createQuery($sql.$orderBy);
        $Sales = $query->getResult();
        
        $dataset = array();
        foreach ($Sales as $item) {
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $item['client']));
            if (isset($filter) && !($filter == '')) {
                if (strpos(strtoupper($BusinessPartner->getName()), strtoupper($filter)) !== false) {
                    $dataset[] = array(
                        'client_name' => $BusinessPartner->getName(),
                        'client_phone' => $BusinessPartner->getPhoneNumber(),
                        'client_email' => $BusinessPartner->getEmail(),
                        'count' => $item['cont'],
                        'miles' => $item['miles'],
                        'total_cost' => $item['custo']
                    );
                }
            }
            else{
                $dataset[] = array(
                        'client_name' => $BusinessPartner->getName(),
                        'client_phone' => $BusinessPartner->getPhoneNumber(),
                        'client_email' => $BusinessPartner->getEmail(),
                        'count' => $item['cont'],
                        'miles' => $item['miles'],
                        'total_cost' => $item['custo']
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function loadSaleByFilter(Request $request, Response $response) {
        $dados = $request->getRow();

        if(isset($dados['dealer']) && $dados['dealer'] != '') {
            $dealer = $dados['dealer'];
        }

        $em = Application::getInstance()->getEntityManager();
        $sql = "select s FROM Sale s ";

        // joins 
        $join = "";

        $whereClause = ' WHERE ';
        $and = '';
        $orderBy = ' ORDER BY s.status DESC, s.id DESC';

        if (isset($dados['data'])){
            $dados = $dados['data'];    
        }

        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause. " b.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };
        if (isset($dados['providerRegistrationCode']) && !($dados['providerRegistrationCode'] == '')) {
            $whereClause = $whereClause.$and. "b.registrationCode = '".$dados['providerRegistrationCode']."' ";
            $and = ' AND ';
        };
        if( (isset($dados['providerRegistrationCode']) && !($dados['providerRegistrationCode'] == '')) || (isset($dados['providerName']) && !($dados['providerName'] == '')) ) {
            if(!isset($dados['cardNumber'])) {
                $join .= ' LEFT JOIN s.cards c ';
            }
            $join .= ' LEFT JOIN c.businesspartner b ';
        }

        if (isset($dados['airline']) && !($dados['airline'] == '')) {
            $whereClause = $whereClause.$and. " a.name like '%".$dados['airline']."%' ";
            $join .= ' LEFT JOIN s.airline a ';
            $and = ' AND ';
        };

        if (isset($dados['dealer']) && $dados['dealer'] != '') {
            if(is_string($dados['dealer'])) {
                $whereClause = $whereClause.$and. " d.name like '%".$dados['dealer']."%' ";
                if(!isset($dados['client'])) {
                    $join .= ' LEFT JOIN s.client x ';
                }
                $join .= ' LEFT JOIN x.dealer d ';
                $and = ' AND ';
            }
        };

        if (isset($dados['state']) && !($dados['state'] == '')) {
            $whereClause = $whereClause.$and. " t.state = '".$dados['state']."' ";
            if(!isset($dados['client'])) {
                $join .= ' LEFT JOIN s.client x ';
            }
            $join .= ' LEFT JOIN x.city t ';
            $and = ' AND ';
        };

        if (isset($dados['client']) && !($dados['client'] == '')) {
            $whereClause = $whereClause.$and. "x.name = '".$dados['client']."' ";
            $join .= ' LEFT JOIN s.client x ';
            $and = ' AND ';
        };

        if (isset($dados['cardNumber']) && !($dados['cardNumber'] == '')) {
            $whereClause = $whereClause.$and. "t.cardNumber = '".$dados['cardNumber']."' ";
            $join .= ' LEFT JOIN s.cardTax t ';
            $and = ' AND ';
        };

        if (isset($dados['paxName']) && !($dados['paxName'] == '')) {
            $whereClause = $whereClause.$and. "p.name like '%".$dados['paxName']."%' ";
            $join .= ' LEFT JOIN s.pax p ';
            $and = ' AND ';
        };


        // 
        // sale table filters
        if(!empty($dados['statuses'])) {
            $whereClause = $whereClause . $and . ' (';

            foreach($dados['statuses'] as $status => $value) {
                if ($value == 'true')
                    $whereClause = $whereClause . " s.status = '".$status."' OR ";
            }
            
            $whereClause = substr($whereClause, 0, -3) . ')';
            $and = ' AND ';
        }

        if (isset($dados['status']) && !($dados['status'] == '')) {
            $whereClause = $whereClause.$and. "s.status = '".$dados['status']."' ";
            $and = ' AND ';
        };

        if (isset($dados['minMiles']) && !($dados['minMiles'] == '')) {
            $whereClause = $whereClause.$and. " s.milesUsed >= '".$dados['minMiles']."' ";
            $and = ' AND ';
        };

        if (isset($dados['maxMiles']) && !($dados['maxMiles'] == '')) {
            $whereClause = $whereClause.$and. " s.milesUsed <= '".$dados['maxMiles']."' ";
            $and = ' AND ';
        };

        if (isset($dados['issuer']) && !($dados['issuer'] == '')) {
            $issuer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['issuer'], 'partnerType' => 'S'));
            if($issuer) {
                $whereClause = $whereClause.$and. " s.issuing = '".$issuer->getId()."' ";
                $and = ' AND ';
            }
        };

        if (isset($dados['ticket_code']) && !($dados['ticket_code'] == '')) {
            $whereClause = $whereClause.$and. " s.ticketCode LIKE '%".$dados['ticket_code']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['user']) && !($dados['user'] == '')) {
            $user = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['user'], 'partnerType' => 'U'));
            if($user) {
                $whereClause = $whereClause.$and. " s.user = '".$user->getId()."' ";
                $and = ' AND ';
            }
        };

        if (isset($dados['flightLocator']) && !($dados['flightLocator'] == '')) {
            $whereClause = $whereClause.$and. " s.flightLocator like '%".$dados['flightLocator']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['externalid']) && !($dados['externalid'] == '')) {
            $whereClause = $whereClause.$and. "s.externalId = '".$dados['externalid']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_boardingDateFrom']) && !($dados['_boardingDateFrom'] == '')) {         
            $whereClause = $whereClause.$and. "s.boardingDate >= '".$dados['_boardingDateFrom']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_boardingDateTo']) && !($dados['_boardingDateTo'] == '')) {
            $whereClause = $whereClause.$and. "s.boardingDate <= '".(new \Datetime($dados['_boardingDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
            $and = ' AND ';
        }

        if (isset($dados['_saleDateFrom']) && ($dados['_saleDateFrom'] != '')) {
            $whereClause = $whereClause.$and. "s.issueDate >= '".$dados['_saleDateFrom']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_saleDateTo']) && ($dados['_saleDateTo'] != '')) {
            $whereClause = $whereClause.$and. "s.issueDate <= '".(new \Datetime($dados['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
            $and = ' AND ';
        }

        if (isset($dados['_refundDateFrom']) && ($dados['_refundDateFrom'] != '')) {
            $whereClause = $whereClause.$and. "s.refundDate >= '".$dados['_refundDateFrom']."' ";
            $and = ' AND ';
            $orderBy = ' ORDER BY s.refundDate DESC';
        }

        if (isset($dados['_refundDateTo']) && ($dados['_refundDateTo'] != '')) {
            $whereClause = $whereClause.$and. "s.refundDate <= '".(new \Datetime($dados['_refundDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
            $and = ' AND ';
            $orderBy = ' ORDER BY s.refundDate DESC';
        }

        if (isset($dados['_returnDateFrom']) && ($dados['_returnDateFrom'] != '')) {
            $whereClause = $whereClause.$and. "s.refundDate >= '".$dados['_returnDateFrom']."' ";
            $and = ' AND ';
            $orderBy = ' ORDER BY s.refundDate DESC';
        }

        if (isset($dados['_returnDateTo']) && ($dados['_returnDateTo'] != '')) {
            $whereClause = $whereClause.$and. "s.returnDate <= '".(new \Datetime($dados['_returnDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
            $and = ' AND ';
            $orderBy = ' ORDER BY s.refundDate DESC';
        }

        // if (isset($dados['daysBoarding']) && ($dados['daysBoarding'] != '')) {
        //     $whereClause = $whereClause.$and. " (s.boardingDate - s.issueDate) >= '".$dados['daysBoarding']."' ";
        //     $and = ' AND ';
        // }

        // if (isset($dados['daysBoardingTo']) && ($dados['daysBoardingTo'] != '')) {
        //     $whereClause = $whereClause.$and. " (s.boardingDate - s.issueDate) <= '".$dados['daysBoardingTo']."' ";
        //     $and = ' AND ';
        // }

        if(isset($dealer)) {
            $DealerPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dealer['name'], 'partnerType' => 'U_D'));
            if($DealerPartner) {

                $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $DealerPartner->getId()));
                $clients = '0';
                $andD = ',';
                foreach ($ClientsDealers as $dealers) {
                    $clients = $clients.$andD.$dealers->getClient()->getId();
                    $andD = ',';
                }

                $whereClause = $whereClause.$and. " ( x.dealer = '".$DealerPartner->getId()."' or x.id in (".$clients.") ) ";
                $and = ' AND ';
                $orderBy = ' ORDER BY s.refundDate DESC';

                if (!isset($dados['dealer'])) {
                    $join .= ' LEFT JOIN s.client x ';
                }
            }
        }

        // joins
        $sql = $sql.$join;
        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause; 
        };
        
        $query = $em->createQuery($sql.$orderBy);
        $order = $query->getResult();

        $dataset = array();
        foreach($order as $item){
            $cards = $item->getCards();
            $cards_id = '';
            $blocked = false;
            $cards_number = '';
            $cards_provider = '';
            $cards_provider_email = '';
            $cards_type = '';
            if ($cards) {
                $cards_id = $item->getCards()->getId();
                $blocked = ($item->getCards()->getBlocked() == 'W');
                $cards_number = $item->getCards()->getCardNumber();
                $cards_provider = $item->getCards()->getBusinesspartner()->getName();
                $cards_provider_email = $item->getCards()->getBusinesspartner()->getEmail();
                $cards_type = $item->getCards()->getCardType();
            };
            
            $partner = $item->getPartner();
            $partner_name = '';
            if (isset($partner)) {
               $partner_name = $partner->getName(); 
            };

            $birthdate = '';
            if ($item->getPax()) {
                if($item->getPax()->getBirthdate()) {
                    $birthdate = $item->getPax()->getBirthdate()->format('Y-m-d');
                }
            }

            if($item->getSaleByThird() == 'Y'){
                $saleByThird = $item->getSaleByThird();
                $saleMethod = '';
                if($item->getProviderSaleByThird()) {
                    $saleMethod = $item->getProviderSaleByThird()->getName();
                }
            }else{
                $saleMethod = 'Site';
            }

            $airportFrom = '';
            $airportFromName = '';
            $airportLocation = '';
            $international = false;
            if($item->getAirportFrom() != null){
                $airportFrom = $item->getAirportFrom()->getCode();
                $airportFromName = $item->getAirportFrom()->getName();
                $international = ($item->getAirportFrom()->getInternational() == 'true');
                if($international) {
                    if($item->getAirportFrom()->getLocation() != NULL) {
                        $airportLocation = $item->getAirportFrom()->getLocation();
                    }
                }
            }

            $user = '';
            if($item->getUser() != null) {
                $user = $item->getUser()->getName();
            }

            $airportTo = '';
            $airportToName = '';
            if($item->getAirportTo() != null){
                $airportTo = $item->getAirportTo()->getCode();
                $airportToName = $item->getAirportTo()->getName();
                if(!$international) {
                    $international = ($item->getAirportTo()->getInternational() == 'true');
                    if($international) {
                        if($item->getAirportTo()->getLocation() != NULL && $airportLocation == '') {
                            $airportLocation = $item->getAirportTo()->getLocation();
                        }
                    }
                }
            }

            $airline = '';
            if($item->getAirline()) {
                $airline = $item->getAirline()->getName();
            }

            $issuing = '';
            if($item->getIssuing()) {
                $issuing = $item->getIssuing()->getName();
            }

            $daysToFly = $item->getIssueDate()->diff($item->getBoardingDate())->days;
            $refundDate = '';
            if($item->getRefundDate() != null) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }
            $returnDate = '';
            if($item->getReturnDate() != null) {
                $returnDate = $item->getReturnDate()->format('Y-m-d H:i:s');
            }

            $expirationDate = '';
            if($item->getPurchase()) {
                $expirationDate = $item->getPurchase()->getMilesDueDate()->format('Y-m-d');
            }

            $cardTax = '';
            if($item->getCardTax()) {
                $cardTax = $item->getCardTax()->getCardNumber();
            }

            $saleCheckedDate = '';
            if($item->getSaleChecked() == 'true') {
                $saleCheckedDate = $item->getSaleCheckedDate()->format('Y-m-d H:i:s');
            }

            $amountPaid = (float)$item->getAmountPaid();
            $commission = ((float)$item->getAmountPaid() - (float)$item->getTax() - (float)$item->getDuTax());
            $paxName = $item->getPax()->getName();

            if(isset($dealer) && isset($dealer['name'])) {

                if($item->getStatus() == 'Cancelamento Solicitado' || $item->getStatus() == 'Cancelamento Nao Solicitado' || $item->getStatus() == 'Cancelamento Efetivado' || $item->getStatus() == 'Cancelamento Pendente') {
                    $amountPaid = 0;
                    $commission = 0;
                }

                if($item->getStatus() == 'Reembolso Solicitado' || $item->getStatus() == 'Reembolso Pagante Solicitado' || $item->getStatus() == 'Reembolso Confirmado' || $item->getStatus() == 'Reembolso CIA' || $item->getStatus() == 'Reembolso Pendente' || $item->getStatus() == 'Reembolso Nao Solicitado' || $item->getStatus() == 'Reembolso Perdido') {
                    
                    if($item->getRefundDate()) {
                        if($item->getRefundDate()->format('Y-m') == $item->getIssueDate()->format('Y-m')) {
                            $amountPaid = 0;
                            $commission = 0;
                        } else {
                            $commission = $commission * -1;
                            $amountPaid = $amountPaid * -1;
                        }
                    }
                }

                $paxName = $item->getPax()->getName();
                if($item->getPax()->getBirthdate()) {

                    $birthDate = explode("/", $item->getPax()->getBirthdate()->format('m/d/Y'));

                    //get age from boarding or birthdate
                    $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > $item->getBoardingDate()->format('md')
                    ? (($item->getBoardingDate()->format('Y') - $birthDate[2]) - 1)
                    : ($item->getBoardingDate()->format('Y') - $birthDate[2]));

                    if($age < 2) {
                        $paxName = $paxName.' - COLO';
                        $commission = 0;
                    }
                }

                // if( !is_null($item->getBaggage()) ) {
                //     $OnlineOrder = new \MilesBench\Controller\OnlineOrder();
                //     if($commission > 0) {
                //         $commission -= $baggage_price;
                //     } else if($commission < 0) {
                //         $commission += $baggage_price;
                //     }
                // }
            }

            $notificationcode = '';
            if($item->getExternalId()) {
                $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $item->getExternalId()));
                if($OnlineOrder) {
                    $notificationcode = $OnlineOrder->getNotificationcode();
                }
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'status' => $item->getStatus(),
                'client' => $item->getClient()->getName(),
                'providerName' => $cards_provider,
                'cards_provider_email' => $cards_provider_email,
                'email' => $item->getClient()->getEmail(),
                'phoneNumber' => $item->getClient()->getPhoneNumber(),
                'airline' => $airline,
                'paxName' => $paxName,
                'paxBirthdate' => $birthdate,
                'from' => $airportFrom,
                'to' => $airportTo,
                'milesOriginal' => (int)$item->getMilesOriginal(),
                'milesused' => (int)$item->getMilesUsed(),
                'description' => $item->getDescription(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'landingDate' => $item->getLandingDate()->format('Y-m-d H:i:s'),
                'airportNamefrom' => $airportFromName,
                'airportNameto' => $airportToName,
                'flight' => $item->getFlight(),
                'flightHour' => $item->getFlightHour(),
                'cards_id' => $cards_id,
                'cardNumber' => $cards_number,
                'partnername' => $partner_name,
                'flightLocator' => $item->getFlightLocator(),
                'checkinState' => $item->getCheckinState(),
                'tax' => (float)$item->getTax(),
                'duTax' => (float)$item->getDuTax(),
                'totalCost' => (float)$item->getTotalCost(),
                'amountPaid' => $amountPaid,
                'kickback' => (float)$item->getKickback(),
                'extraFee' => (float)$item->getExtraFee(),
                'externalId' => $item->getExternalId(),
                'online_flight_id' => $item->getOnlineFlightId(),
                'paxRegistrationCode' => $item->getPax()->getRegistrationCode(),
                'miles_money' => (float)$item->getMilesMoney(),
                'issuing' => $issuing,
                'reservation_code' => $item->getReservationCode(),
                'sale_method' => $saleMethod,
                'saleByThird' => $item->getSaleByThird(),
                'user' => $user,
                'processing_time' => $item->getProcessingTime(),
                'ticket_code' => $item->getTicketCode(),
                'daysToFly' => $daysToFly,
                'refundDate' => $refundDate,
                'returnDate' => $returnDate,
                'international' => $international,
                'airportLocation' => $airportLocation,
                'blocked' => $blocked,
                'expirationDate' => $expirationDate,
                'saleType' => $item->getSaleType(),
                'cardTax' => $cardTax,
                'saleChecked' => ($item->getSaleChecked() == 'true'),
                'saleCheckedDate' => $saleCheckedDate,
                'cards_type' => $cards_type,
                'commission' => $commission,
                'tax_billet' => (float)$item->getTaxBillet(),
                'notificationcode' => $notificationcode,
                'baggage_price'  => (float)$item->getBaggagePrice(),
                'special_seat'  => (float)$item->getSpecialSeat()
            );
        }
        $response->setDataset($dataset);
    }

    public function refund(Request $request, Response $response) {
        if(isset($request->getRow()['businesspartner'])) {
            $BusinessPartner = $request->getRow()['businesspartner'];
        }
        $hash = $request->getRow();
        if(isset($hash['hashId'])){
            $hash = $hash['hashId'];
        }
        $refund = $request->getRow()['refund'];
        $datasale = $request->getRow()['sale'];
        $em = Application::getInstance()->getEntityManager();
        $Sale = $em->getRepository('Sale')->find($datasale['id']);

        try {
            $em->getConnection()->beginTransaction();
            // $Sale->setFlightLocator($datasale['flightLocator']);
            
            if(!(isset($refund['noShow']) && $refund['noShow'] == 'true')) {
                if($Sale->getStatus() == 'Emitido' || $Sale->getStatus() == 'Reembolso Pendente') {
                    if(isset($refund['value'])) {
                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Reembolso - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billsreceive->setOriginalValue($refund['value']);
                        $Billsreceive->setActualValue($refund['value']);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Reembolso');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate(new \Datetime());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);
                    } else {
                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Reembolso - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billsreceive->setOriginalValue(0);
                        $Billsreceive->setActualValue(0);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Reembolso');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate(new \Datetime());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);
                    }
                    $Sale->setStatus('Reembolso Solicitado');
                    $Sale->setRefundDate(new \Datetime());
                    $em->persist($Sale);
                    $em->flush($Sale);
                    if (isset($refund['valueRefund'])) {
                        $Billspay = new \Billspay();
                        $Billspay->setStatus('A');
                        $Billspay->setDescription('Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billspay->setOriginalValue($refund['valueRefund']);
                        $Billspay->setActualValue($refund['valueRefund']);
                        $Billspay->setProvider($Sale->getPartner());
                        $Billspay->setTax(0);
                        $Billspay->setDiscount(0);
                        $Billspay->setAccountType('Reembolso');
                        $Billspay->setPaymentType('Reembolso');
                        $Billspay->setDueDate($Sale->getIssueDate());
                        if(isset($refund['partner']) && $refund['partner'] != '') {
                            $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $refund['partner']));
                            if(!$provider){
                                $provider = new \Businesspartner();
                                $provider->setName($refund['partner']);
                                $provider->setPartnerType('P');
                                $em->persist($provider);
                                $em->flush($provider);
                            }
                            $Billspay->setProvider($provider);
                        }
                        if(isset($InternalCards)) {
                            $Billspay->setCardsId($InternalCards);

                            $InternalCards->setCardUsed($InternalCards->getCardUsed() + $refund['valueRefund']);

                            $SystemLog = new \SystemLog();
                            $SystemLog->setIssueDate(new \Datetime());
                            $SystemLog->setDescription("Uso registrado - Cartao de credito n:".$InternalCards->getCardNumber()." - Reembolso n:".$Sale->getId()." - Valor R$:".$refund['valueRefund']."");
                            $SystemLog->setLogType('CREDITCARD');
                            $SystemLog->setBusinesspartner($BusinessPartner);

                            $em->persist($SystemLog);
                            $em->flush($SystemLog);

                            $em->persist($InternalCards);
                            $em->flush($InternalCards);
                        }

                        $em->persist($Billspay);
                        $em->flush($Billspay);

                        $SaleBillspay = new \SaleBillspay();
                        $SaleBillspay->setBillspay($Billspay);
                        $SaleBillspay->setSale($Sale);
                        $em->persist($SaleBillspay);
                        $em->flush($SaleBillspay);
                    }
                }
                if($Sale->getStatus() == 'Reembolso No-show Solicitado') {
                    if(isset($refund['value'])) {
                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Reembolso - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billsreceive->setOriginalValue($refund['value']);
                        $Billsreceive->setActualValue($refund['value']);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Reembolso');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate(new \Datetime());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);
                    } else {
                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Reembolso - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billsreceive->setOriginalValue(0);
                        $Billsreceive->setActualValue(0);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Reembolso');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate(new \Datetime());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);
                    }
                    $Sale->setStatus('Reembolso Solicitado');
                    $em->persist($Sale);
                    $em->flush($Sale);
                }
                if($Sale->getStatus() == 'Reembolso No-show Confirmado') {
                    if(isset($refund['value'])) {
                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Reembolso - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billsreceive->setOriginalValue($refund['value']);
                        $Billsreceive->setActualValue($refund['value']);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Reembolso');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate(new \Datetime());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);
                    } else {
                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Reembolso - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billsreceive->setOriginalValue(0);
                        $Billsreceive->setActualValue(0);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Reembolso');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate(new \Datetime());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);
                    }
                    $Sale->setStatus('Reembolso Confirmado');
                    $em->persist($Sale);
                    $em->flush($Sale);
                }
            } else {
                if($Sale->getStatus() == 'Reembolso No-show Solicitado') {
                    $Sale->setStatus('Reembolso Solicitado');
                    $Sale->setRefundDate(new \Datetime());
                    $em->persist($Sale);
                    $em->flush($Sale);
                }
                if($Sale->getStatus() == 'Emitido' || $Sale->getStatus() == 'Remarcação Confirmado') {
                    $Sale->setStatus('Reembolso No-show Solicitado');
                    $Sale->setRefundDate(new \Datetime());
                    $em->persist($Sale);
                    $em->flush($Sale);
                    if (isset($refund['valueRefund'])) {
                        $Billspay = new \Billspay();
                        $Billspay->setStatus('A');
                        $Billspay->setDescription('Passageiro '.$Sale->getPax()->getName().' - Localizador '.$datasale['flightLocator']);
                        $Billspay->setOriginalValue($refund['valueRefund']);
                        $Billspay->setActualValue($refund['valueRefund']);
                        $Billspay->setProvider($Sale->getPartner());
                        $Billspay->setTax(0);
                        $Billspay->setDiscount(0);
                        $Billspay->setAccountType('Reembolso');
                        $Billspay->setPaymentType('Reembolso');
                        $Billspay->setDueDate($Sale->getIssueDate());
                        if(isset($refund['partner']) && $refund['partner'] != '') {
                            $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $refund['partner']));
                            if(!$provider){
                                $provider = new \Businesspartner();
                                $provider->setName($refund['partner']);
                                $provider->setPartnerType('P');
                                $em->persist($provider);
                                $em->flush($provider);
                            }
                            $Billspay->setProvider($provider);
                        }
                        if(isset($InternalCards)) {
                            $Billspay->setCardsId($InternalCards);

                            $InternalCards->setCardUsed($InternalCards->getCardUsed() + $refund['valueRefund']);

                            $SystemLog = new \SystemLog();
                            $SystemLog->setIssueDate(new \Datetime());
                            $SystemLog->setDescription("Uso registrado - Cartao de credito n:".$InternalCards->getCardNumber()." - Reembolso n:".$Sale->getId()." - Valor R$:".$refund['valueRefund']."");
                            $SystemLog->setLogType('CREDITCARD');
                            $SystemLog->setBusinesspartner($BusinessPartner);

                            $em->persist($SystemLog);
                            $em->flush($SystemLog);

                            $em->persist($InternalCards);
                            $em->flush($InternalCards);
                        }

                        $em->persist($Billspay);
                        $em->flush($Billspay);

                        $SaleBillspay = new \SaleBillspay();
                        $SaleBillspay->setBillspay($Billspay);
                        $SaleBillspay->setSale($Sale);
                        $em->persist($SaleBillspay);
                        $em->flush($SaleBillspay);
                    }
                }
            }

            if(isset($refund['tax_card']) && $refund['tax_card'] != '') {
                $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $refund['tax_card']));
            }

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription($Sale->getStatus()." - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()."");
            $SystemLog->setLogType('SALE');
            $SystemLog->setBusinesspartner($BusinessPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('id' => $Sale->getCardTax()));
            if($InternalCards){
                $InternalCards->setCardUsed($InternalCards->getCardUsed() - $refund['valueRefund']);

                $em->persist($InternalCards);
                $em->flush($InternalCards);

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Reembolso Solicitado - Cartao de credito n:".$InternalCards->getCardNumber()." - Venda n:".$Sale->getId()." - Valor R$:".($refund['valueRefund']+$Sale->getTax())."");
                $SystemLog->setLogType('CREDITCARD');
                $SystemLog->setBusinesspartner($BusinessPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            // if($Sale->getExternalId()) {
            //     $onlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $Sale->getExternalId()));
            //     if($onlineOrder) {
            //         if($onlineOrder->getNotificationurl()) {
            //             $jsonToPost = array(
            //                 'notificationCode' => $onlineOrder->getNotificationcode(),
            //                 'status' => '2',
            //                 'hashId' => "fd0ab7097fb7119900febac7e3875218"
            //             );
            //             $ch = curl_init();
            //             curl_setopt($ch, CURLOPT_URL, $onlineOrder->getNotificationurl());
            //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            //             curl_setopt($ch, CURLOPT_POST, 1);
            //             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
            //             $result = curl_exec($ch);
            //         }
            //     }
            // }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function noRefund(Request $request, Response $response) {
        $dados = $request->getRow();

        $is_perdido = false;
        if (isset($dados['is_perdido'])) {
            $is_perdido = $dados['is_perdido'] === 'true'? true:false;
        }

        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $newOrder = false;

        try {
            $em->getConnection()->beginTransaction();

            $Sale = $em->getRepository('Sale')->find($dados['id']);
            // $Sale->setFlightLocator($datasale['flightLocator']);
            if($is_perdido)
                $Sale->setStatus('Reembolso Perdido');
            else
                $Sale->setStatus('Reembolso Nao Solicitado');
            $em->persist($Sale);
            $em->flush($Sale);

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            if($is_perdido)
                $SystemLog->setDescription("Reembolso Perdido - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()."");
            else
                $SystemLog->setDescription("Reembolso Nao Solicitado - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()."");
            $SystemLog->setLogType('SALE');
            $SystemLog->setBusinesspartner($BusinessPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $em->getConnection()->commit();
            
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function notConfirmedCancelSale(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {

            $Sale = $em->getRepository('Sale')->find($dados['id']);
            $Sale->setStatus('Cancelamento Nao Solicitado');
            $em->persist($Sale);
            $em->flush($Sale);

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Cancelamento Nao Solicitado - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()."");
            $SystemLog->setLogType('SALE');
            $SystemLog->setBusinesspartner($BusinessPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function closeRefund(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();
        $newOrder = false;

        try {
            $em->getConnection()->beginTransaction();

            $Sale = $em->getRepository('Sale')->find($dados['id']);
            // $Sale->setFlightLocator($datasale['flightLocator']);
            if($Sale->getStatus() != 'Reembolso Confirmado' && $Sale->getStatus() != 'Reembolso No-show Confirmado') {

                if(isset($dados['_returnDate']) && $dados['_returnDate'] != '') {
                    $Sale->setReturnDate(new \Datetime($dados['_returnDate']));
                } else {
                    $Sale->setReturnDate(new \Datetime());
                }

                if($Sale->getStatus() == 'Reembolso No-show Solicitado') {
                    $Sale->setStatus('Reembolso No-show Confirmado');
                }
                if($Sale->getStatus() == 'Reembolso Solicitado' || $Sale->getStatus() == 'Reembolso CIA') {
                    $Sale->setStatus('Reembolso Confirmado');
                }
                $em->persist($Sale);
                $em->flush($Sale);

                if($Sale->getCardTax() && $Sale->getAirline()->getName() != 'AZUL' && $Sale->getAirline()->getName() != 'GOL') {
                    $CardTax = $Sale->getCardTax();

                    $Billspay = new \Billspay();
                    $Billspay->setStatus('A');
                    $Billspay->setDescription('Passageiro '.$Sale->getPax()->getName().' - Localizador '.$Sale->getFlightLocator());
                    $Billspay->setOriginalValue((float)$Sale->getTax() * -1);
                    $Billspay->setActualValue((float)$Sale->getTax() * -1);
                    $Billspay->setTax(0);
                    $Billspay->setDiscount(0);
                    $Billspay->setAccountType('Retorno Reembolso');
                    $Billspay->setPaymentType('Retorno Reembolso');
                    $Billspay->setDueDate($Sale->getIssueDate());

                    $em->persist($Billspay);
                    $em->flush($Billspay);
                }

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
                if($Cards) {

                    $value = 0;

                    if($Sale->getRefundDate() && isset($dados['billsPayValue'])) {

                        $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy(array('sale' => $Sale->getId()));
                        foreach ($SaleBillsreceive as $item) {
                            if($item->getBillsreceive()->getAccountType() == 'Reembolso') {
                                $value = (float)$item->getBillsreceive()->getActualValue() - (float)$dados['tax'] + (float)$dados['billsPayValue'];
                            }
                        }
                    }

                    $removedMiles = Miles::addMiles($em, $dados['cards_id'], (float)$Sale->getMilesUsed(), $Sale->getId(), 'REFUND', $BusinessPartner, $value);
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Reembolso Confirmado - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()."");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);

                if(isset($dados['tax_card']) && $dados['tax_card'] != '') {
                    $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $dados['tax_card']));

                    if($InternalCards) {

                        $SaleBillspay = $em->getRepository('SaleBillspay')->findBy(array('sale' => $Sale->getId()));
                        foreach ($SaleBillspay as $SBillspay) {
                            $Billspay = $SBillspay->getBillspay();
                            if($Billspay->getAccountType() == "Reembolso") {
                                $Billspay->setCards($InternalCards);
                            }

                            $em->persist($Billspay);
                            $em->flush($Billspay);
                        }
                    }
                }

                if(isset($dados['billsPayValue']) && $dados['billsPayValue'] != '') {
                    $SaleBillspay = $em->getRepository('SaleBillspay')->findBy(array('sale' => $dados['id']));

                    foreach ($SaleBillspay as $payment) {
                        $Billspay = $payment->getBillspay();
                        if($Billspay->getAccountType() == 'Reembolso') {
                            $Billspay->setActualValue($dados['billsPayValue']);
                            $Billspay->setOriginalValue($dados['billsPayValue']);

                            $em->persist($Billspay);
                            $em->flush($Billspay);
                        }
                    }
                }
            }

            $em->getConnection()->commit();
            
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function generateRepricing(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['repricing'])) {
            $repricing = $dados['repricing'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $hash = $request->getRow();
        if(isset($hash['hashId'])){
            $hash = $hash['hashId'];
        }

        $em = Application::getInstance()->getEntityManager();
        $newOrder = false;

        try {
            $em->getConnection()->beginTransaction();

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $sale = $em->getRepository('Sale')->findOneBy(array('id' => $dados['id']));
            $change = '';

            if(isset($repricing['airportNamefrom']) && $repricing['airportNamefrom'] != ''){
                if($sale->getAirportFrom()->getName() != substr($repricing['airportNamefrom'],0,3)) {
                    $change = $change.' - Aeroporto origem alterado de '.$sale->getAirportFrom()->getCode().' para '.substr($repricing['airportNamefrom'],0,3);
                    $airport = $em->getRepository('Airport')->findOneBy(array('code' => substr($repricing['airportNamefrom'],0,3)));
                    $sale->setAirportFrom($airport);
                }
            }

            if(isset($repricing['airportNameto']) && $repricing['airportNameto'] != ''){
                if($sale->getAirportTo()->getName() !=  substr($repricing['airportNameto'],0,3)){
                    $change = $change.' - Aeroporto destino alterado de '.$sale->getAirportTo()->getCode().' para '. substr($repricing['airportNameto'],0,3);
                    $airport = $em->getRepository('Airport')->findOneBy(array('code' => substr($repricing['airportNameto'],0,3)));
                    $sale->setAirportTo($airport);
                }
            }

            if(isset($repricing['_boardingDate']) && $repricing['_boardingDate'] != ''){
                if($sale->getBoardingDate()->format('Y-m-d H:i:s') != $repricing['_boardingDate']){
                    $change = $change.' - Data de embarque alterado de '.$sale->getBoardingDate()->format('Y-m-d H:i:s').' para '.$repricing['_boardingDate'];
                    $sale->setBoardingDate(new \Datetime($repricing['_boardingDate']));
                }
            }

            if(isset($repricing['_landingDate']) && $repricing['_landingDate'] != ''){
                if($sale->getLandingDate()->format('Y-m-d H:i:s') != $repricing['_landingDate']){
                    $change = $change.' - Data de desembarque alterado de '.$sale->getLandingDate()->format('Y-m-d H:i:s').' para '.$repricing['_landingDate'];
                    $sale->setLandingDate(new \Datetime($repricing['_landingDate']));
                }
            }

            if(isset($repricing['flight']) && $repricing['flight'] != ''){
                if($sale->getFlight() != $repricing['flight']){
                    $change = $change.' - Voo alterado de '.$sale->getFlight().' para '.$repricing['flight'];
                    $sale->setFlight($repricing['flight']);
                }
            }

            if(isset($repricing['flightHour']) && $repricing['flightHour'] != ''){
                if($sale->getFlightHour() != $repricing['flightHour']){
                    $change = $change.' - Tempo de Voo alterado de '.$sale->getFlightHour().' para '.$repricing['flightHour'];
                    $sale->setFlightHour($repricing['flightHour']);
                }
            }

            if(isset($repricing['milesused']) && $repricing['milesused'] != ''){
                $change = $change.' - Milhas usadas alterada de '.$sale->getMilesUsed().' para '.($sale->getMilesUsed() + $repricing['milesused']);
                $sale->setMilesUsed($sale->getMilesUsed() + $repricing['milesused']);
            }

            if(isset($repricing['flightLocator']) && $repricing['flightLocator'] != ''){
                if($sale->getFlightLocator() != $repricing['flightLocator']){
                    $change = $change.' - Localizador alterado de '.$sale->getFlightLocator().' para '.$repricing['flightLocator'];
                    $sale->setFlightLocator($repricing['flightLocator']);
                }
            }

            if(isset($repricing['ticket_code']) && $repricing['ticket_code'] != ''){
                if($sale->getTicketCode() != $repricing['ticket_code']){
                    $change = $change.' - E-ticket alterado de '.$sale->getTicketCode().' para '.$repricing['ticket_code'];
                    $sale->setTicketCode($repricing['ticket_code']);
                }
            }

            if(isset($repricing['method']) && $repricing['method'] == 'Cartao') {
                $change = $change.' - Metodo de venda alterado de '.$sale->getSaleType().' para '.$repricing['method'];
                $sale->setSaleType($repricing['method']);

            } else {
                $change = $change.' - Metodo de venda alterado de '.$sale->getSaleType().' para '.$repricing['method'];
                $sale->setSaleType($repricing['method']);

                $Billsreceive = new \Billsreceive();
                $Billsreceive->setStatus('A');
                $Billsreceive->setClient($sale->getClient());
                $Billsreceive->setDescription('Remarcação - Passageiro '.$sale->getPax()->getName().' - Localizador '.$sale->getFlightLocator());
                $value = ($repricing['milesused'] * ($repricing['newValue'] / 1000)) + $repricing['cost'];
                $Billsreceive->setOriginalValue($repricing['value']);
                $Billsreceive->setActualValue($repricing['value']);
                $Billsreceive->setTax(0);
                $Billsreceive->setDiscount(0);
                $Billsreceive->setAccountType('Remarcação');
                $Billsreceive->setReceiveType('Boleto Bancario');
                $Billsreceive->setDueDate(new \DateTime());
                $em->persist($Billsreceive);
                $em->flush($Billsreceive);

                $SaleBillsreceive = new \SaleBillsreceive();
                $SaleBillsreceive->setBillsreceive($Billsreceive);
                $SaleBillsreceive->setSale($sale);
                $em->persist($SaleBillsreceive);
                $em->flush($SaleBillsreceive);
            }

            if(isset($repricing['newValue']) && $repricing['newValue'] != ''){
                $change = $change.' - Valor alterado de '.$sale->getAmountPaid().' para '.($repricing['newValue'] + $repricing['cost']);
                $value = ($repricing['milesused'] * ($repricing['newValue'] / 1000));
                $sale->setAmountPaid($sale->getAmountPaid() + $value);
            }

            if(isset($repricing['partner']) && $repricing['partner'] != '') {
                $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $repricing['partner']));
                if(!$provider){
                    $provider = new \Businesspartner();
                    $provider->setName($repricing['partner']);
                    $provider->setPartnerType('P');
                    $em->persist($provider);
                    $em->flush($provider);
                }
            }

            if(isset($repricing['tax_card']) && $repricing['tax_card'] != '') {
                $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $repricing['tax_card']));
            }

            $Billspay = new \Billspay();
            $Billspay->setStatus('A');
            $Billspay->setDescription('Remarcação  - '.'Passageiro '.$sale->getPax()->getName().' - Localizador '.$sale->getFlightLocator());
            $Billspay->setOriginalValue($repricing['valuerepricing']);
            $Billspay->setActualValue($repricing['valuerepricing']);
            $Billspay->setTax(0);
            $Billspay->setDiscount(0);
            $Billspay->setAccountType('Remarcação');
            $Billspay->setPaymentType('Cartao Credito');
            $Billspay->setDueDate($sale->getIssueDate());

            if(isset($provider)) {
                $Billspay->setProvider($provider);
            }

            if(isset($InternalCards)) {
                $Billspay->setCards($InternalCards);
            }

            $Billspay->setPaymentType('Cartao Credito');
            $em->persist($Billspay);
            $em->flush($Billspay);

            $SaleBillspay = new \SaleBillspay();
            $SaleBillspay->setBillspay($Billspay);
            $SaleBillspay->setSale($sale);
            $em->persist($SaleBillspay);
            $em->flush($SaleBillspay);

            if((isset($repricing['returnMiles']) && $repricing['returnMiles'] == 'true') || ($repricing['milesused'] > 0)) {

                $Cards = $sale->getCards();
                if($Cards) {
                    $removedMiles = Miles::addMiles($em, $Cards->getId(), $dados['milesused'], $sale->getId(), 'REPRICING', $BusinessPartner, 0);
                    $removedMiles = Miles::removeMiles($em, $Cards->getId(), $sale->getMilesUsed(), $sale->getId());
                }
                $sale->setStatus('Remarcação Confirmado');
            } else {
                $sale->setStatus('Remarcação Solicitado');
                $sale->setPointsWaiting($repricing['milesused']);
            }

            $sale->setRepricingChecked('false');
            $sale->setRepricingDate(new \DateTime());

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Remarcação - Venda n:".$sale->getId()." - Usuario:".$BusinessPartner->getName()." >>> ".$change);
            $SystemLog->setLogType('SALE');
            $SystemLog->setBusinesspartner($BusinessPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);
            
            $em->persist($sale);
            $em->flush($sale);

            $em->getConnection()->commit();
            
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function confirmRepricing(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();            
            $sale = $em->getRepository('Sale')->findOneBy(array('id' => $dados['id']));

            $hash = $request->getRow();
            if(isset($hash['hashId'])){
                $hash = $hash['hashId'];
            }

            $Cards = $em->getRepository('Cards')->findOneBy(array('cardNumber' => $dados['cardNumber']));
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            if($Cards) {

                $removedMiles = Miles::addMiles($em, $Cards->getId(), ($sale->getMilesUsed() - $sale->getPointsWaiting()), $sale->getId(), 'REPRICING', $BusinessPartner, 0);
                $removedMiles = Miles::removeMiles($em, $Cards->getId(), $sale->getMilesUsed(), $sale->getId());

            }
            $sale->setStatus('Remarcação Confirmado');

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Remarcação Confirmada - Venda n:".$sale->getId()." - Usuario:".$BusinessPartner->getName()." >>> ".$change);
            $SystemLog->setLogType('SALE');
            $SystemLog->setBusinesspartner($BusinessPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);
            
            $em->persist($sale);
            $em->flush($sale);

            $em->getConnection()->commit();
            
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveFlightLocator(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['flightLocator'])) {
            $flightLocator = $dados['flightLocator'];
        }

        if (isset($dados['sales'])) {
            $sales = $dados['sales'];
        }

        $em = Application::getInstance()->getEntityManager();
        try {
            // $em->getConnection()->beginTransaction();            

            // for ($i = 0; $i <= count($sales)-1; $i++) {
            //     $Sale = $em->getRepository('Sale')->find($sales[$i]['id']);
            //     $Sale->setFlightLocator($flightLocator);
            //     $em->persist($Sale);
            //     $em->flush($Sale);
            // }   

            // $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadDataConference(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $DataConference = $em->getRepository('DataConference')->findAll();

        $dataset = array();
        foreach($DataConference as $data){

            $dataset[] = array(
                'vendaN' => $data->getVendaN(),
                'pax' => $data->getPax(),
                'loc' => $data->getLoc(),
                'eTicket' => $data->getETicket(),
                'checkplani' => $data->getCheckplani(),
                'cia' => $data->getCia(),
                'cartao' => $data->getCartao(),
                'dataEmissao' => $data->getDataEmissao(),
                'ida' => $data->getIda(),
                'origem' => $data->getOrigem(),
                'volta' => $data->getVolta(),
                'tVForn' => $data->getTVForn(),
                'loja' => $data->getLoja(),
                'pagamento' => $data->getPagamento(),
                'qtd' => $data->getQtd(),
                'taxa' => $data->getTaxa(),
                'cartaoRepasse' => $data->getCartaoRepasse(),
                'realp' => $data->getRealp(),
                'pontos' => $data->getPontos(),
                'coeficiente' => $data->getCoeficiente(),
                'dU' => (float)$data->getDU(),
                'taxa2' => $data->getTaxa2(),
                'valorCusto' => (float)$data->getValorCusto(),
                'valorPago' => (float)$data->getValorPago(),
                'comissao' => $data->getComissao(),
                'emissor' => $data->getEmissor(),
                'cliente' => $data->getCliente(),
                'variados' => $data->getVariados(),
                'emissor2' => $data->getEmissor2(),
                'tempo' => $data->getTempo(),
                'email' => $data->getEmail(),
                'obs' => $data->getObs(),
                'confDcTv' => $data->getConfDcTv()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadLog(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        if(isset($dados['externalId']) && $dados['externalId'] != '') {
            $sql = "select l FROM SystemLog l WHERE l.logType = 'LIBERATION-COMMERCIAL' and l.description like '%Pedido: ".$dados['externalId']."%' ";
            $query = $em->createQuery($sql);
            $SaleLogs = $query->getResult();
            foreach ($SaleLogs as $item) {
                $partner = null;
                if($item->getBusinesspartner()) {
                    $partner = $item->getBusinesspartner()->getName();
                }
                $dataset[] = array(
                    'id' => $item->getId(),
                    'issue_date' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                    'partner' => $partner,
                    'description' => $item->getDescription()
                );
            }
        }


        $sql = "select l FROM SystemLog l WHERE l.logType = 'SALE' and l.description like '%Venda n:".$dados['id']."%' ";
        $query = $em->createQuery($sql);
        $SaleLogs = $query->getResult();
        foreach ($SaleLogs as $item) {
            $partner = null;
            if($item->getBusinesspartner()) {
                $partner = $item->getBusinesspartner()->getName();
            }
            
            $dataset[] = array(
                'id' => $item->getId(),
                'issue_date' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'partner' => $partner,
                'description' => $item->getDescription()
            );
        }
        
        $sql = "select l FROM SystemLog l WHERE l.logType = 'SALE-BOARDING' and l.description like '%-SALE->".$dados['id']."-%' ";
        $query = $em->createQuery($sql);
        $SaleLogs = $query->getResult();
        foreach ($SaleLogs as $item) {
            $partner = null;
            if($item->getBusinesspartner()) {
                $partner = $item->getBusinesspartner()->getName();
            }

            $description = explode("-SALE->".$dados['id']."-", $item->getDescription());
            $description = $description[1];

            $dataset[] = array(
                'id' => $item->getId(),
                'issue_date' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'partner' => $partner,
                'description' => $description
            );
        }
        $response->setDataset($dataset);
    }

    public function loadRefundByFilter(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        for ($i=11; $i >= 0; $i--) { 
            $monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('first day of this month');
            $monthAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('last day of this month')->modify('+1 day');

            $sql = "select COUNT(s.id) as sales FROM Sale s where s.refundDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."'";
            $query = $em->createQuery($sql);
            $refunds = $query->getResult();

            $sql = "select COUNT(s.id) as sales FROM Sale s where s.returnDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."'";
            $query = $em->createQuery($sql);
            $returns = $query->getResult();

            $dataset[] = array(
                'refunds' => $refunds[0]['sales'],
                'returns' => $returns[0]['sales'],
                'month' => $monthsAgo->format('Y-m')
            );
        }
        $response->setDataset($dataset);
    }

    public function saveSaleOccurrence(Request $request, Response $response) {
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

            $Sale = $em->getRepository('Sale')->find($dados['id']);
            if(isset($dados['occurrence'])) {
                $Sale->setOccurrenceStatus($dados['occurrence']);
                $Sale->setOccurrenceDate(new \DateTime());
            }

            $em->persist($Sale);
            $em->flush($Sale);

            if(isset($dados['saleDescription']) && $dados['saleDescription'] != '') {
                
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Registro de andamento -SALE->".$dados['id']."- ".$dados['saleDescription']);
                $SystemLog->setLogType('SALE-BOARDING');
                $SystemLog->setBusinesspartner($UserPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            if(isset($dados['saleLog']) && $dados['saleLog'] != '') {
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Registro de andamento - Venda n:".$dados['id']." - ".$dados['saleLog']);
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($UserPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function confirmRefundSolicitation(Request $request, Response $response) {
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

            $Sale = $em->getRepository('Sale')->find($dados['id']);

            if($Sale->getStatus() != 'Reembolso CIA') {
                $Sale->setStatus('Reembolso CIA');
                $Sale->setAirlineSolicitation(new \DateTime($dados['_airlineSolicitation']));

                $em->persist($Sale);
                $em->flush($Sale);
                
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Reembolso Solicitado CIA - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($UserPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadRefundSales(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $sql = "select s FROM Sale s where ".
            " s.status in ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso CIA', 'Reembolso Confirmado', 'Reembolso Pendente', 'Reembolso No-show Solicitado', 'Reembolso No-show Confirmado', 'Reembolso Nao Solicitado', 'Reembolso Perdido') ";
        $query = $em->createQuery($sql);
        $order = $query->getResult();

        $dataset = array();
        foreach($order as $item){

            $refundDate = '';
            if($item->getRefundDate()) {
                $refundDate = $item->getRefundDate()->format('d-m-Y');
            }

            if($item->getCards()) {
                $cards_type = $item->getCards()->getCardType();
                $providerName = $item->getCards()->getBusinesspartner()->getName();
            } else {
                $cards_type = '';
                $providerName = '';
            }

            $Amount = '';
            $cardTax = '';
            $valueCia = '';
            $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy(array('sale' => $item->getId()));
            foreach ($SaleBillsreceive as $BillsReceives) {
                if($BillsReceives->getBillsreceive()->getAccountType() == 'Reembolso') {
                    $Amount = (float)$BillsReceives->getBillsreceive()->getActualValue();
                }
            }

            $SaleBillspay = $em->getRepository('SaleBillspay')->findBy(array('sale' => $item->getId()));
            foreach ($SaleBillspay as $payment) {
                if($payment->getBillspay()->getAccountType() == 'Reembolso') {
                    if($payment->getBillspay()->getCards()) {
                        $cardTax = $payment->getBillspay()->getCards()->getCardNumber();
                    }
                    $valueCia = (float)$payment->getBillspay()->getActualValue();
                }
            }

            $airlineSolicitation = '';
            if($item->getAirlineSolicitation()) {
                $airlineSolicitation = $item->getAirlineSolicitation()->format('d-m-Y');
            }

            $airportFrom = '';
            if($item->getAirportFrom()){
                $airportFrom = $item->getAirportFrom()->getCode();
            }

            $airportTo = '';
            if($item->getAirportTo()){
                $airportTo = $item->getAirportTo()->getCode();
            }

            $returnDate = '';
            if($item->getReturnDate()) {
                $returnDate = $item->getReturnDate()->format('d-m-Y');
            }

            $dataset[] = array(
                'issueDate' => $item->getIssueDate()->format('d-m-Y'),
                'status' => $item->getStatus(),
                'refundDate' => $refundDate,
                'client' => $item->getClient()->getName(),
                'paxName' => $item->getPax()->getName(),
                'ticket_code' => $item->getTicketCode(),
                'flightLocator' => $item->getFlightLocator(),
                'cards_type' => $cards_type,
                'milesused' => (int)$item->getMilesUsed(),
                'providerName' => $providerName,
                'cardTax' => $cardTax,
                'airlineSolicitation' => $airlineSolicitation,
                'valueCia' => $valueCia,
                'boardingDate' => $item->getBoardingDate()->format('d-m-Y'),
                'airportFrom' => $airportFrom,
                'airportTo' => $airportTo,
                'returnDate' => $returnDate,
                'multc' => 0,
                'amount' => $Amount
            );
        }
        $response->setDataset($dataset);
    }

    public function dataConferenceSales(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        try {

            $DataConference = $em->getRepository('DataConference')->findAll();

            $flightLocatorChecked = array();
            $dataset = array();

            foreach ($DataConference as $data) {

                $conferences = $em->getRepository('DataConference')->findBy(array('loc' => $data->getLoc()));
                $Sale = $em->getRepository('Sale')->findBy(array('flightLocator' => $data->getLoc()));

                if(!$flightLocatorChecked[$data->getLoc()]) {

                    if(count($conferences) == count($Sale)) {

                        $sql = "select s FROM Sale s JOIN s.pax p where ".
                            " s.flightLocator = '".$data->getLoc()."' and p.name = '".$data->getPax()."' and s.amountPaid = '".$data->getValorPago()."'  ";
                        // $sql = "select s FROM Sale s JOIN s.pax p where ".
                        //     " s.flightLocator = '".$data->getLoc()."' and p.name = '".$data->getPax()."' and s.amountPaid = '".$data->getValorPago()."'  and (s.amountPaid - s.totalCost ) = '".$data->getComissao()."' ";
                        $query = $em->createQuery($sql);
                        $Sale = $query->getResult();

                        if(count($Sale) <= 0) {
                            $flightLocatorChecked[] = $data->getLoc();
                            $dataset[] = array(
                                'reason' => 'name: '.$data->getPax().'; amountPaid: '.$data->getValorPago().'; totalCost: '.$data->getValorCusto().'; duTax: '.$data->getDU(),
                                'flightLocator' => $data->getLoc()
                            );
                        }

                    } else {
                        $flightLocatorChecked[] = $data->getLoc();
                        $dataset[] = array(
                            'reason' => 'Quantidade de Trechos',
                            'flightLocator' => $data->getLoc()
                        );
                    }

                }
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);
            $response->setDataset($dataset);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadRefundsToConference(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['filter'])) {
            $filter = $dados['filter'];
        }
        $em = Application::getInstance()->getEntityManager();

        $whereClause = " s.status in ('Cancelamento Nao Solicitado', 'Cancelamento Solicitado', 'Cancelamento Efetivado', 'Cancelamento Pendente', 'Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso CIA', 'Reembolso Pendente', 'Reembolso No-show Solicitado', 'Reembolso No-show Confirmado', 'Reembolso Nao Solicitado', 'Reembolso Perdido', 'Reembolso Confirmado', 'Remarcação Confirmado', 'Remarcação Solicitado') ";
        $orderBy = ' ORDER BY s.status DESC, s.id DESC';
        $and = " AND ";
        $join = "";
        if(isset($filter)) {
            $sql = "select s FROM Sale s ";
    
            if (isset($filter['airline']) && !($filter['airline'] == '')) {
                $whereClause = $whereClause.$and. " a.name like '%".$filter['airline']."%' ";
                $join .= ' LEFT JOIN s.airline a ';
                $and = ' AND ';
            };

            if (isset($filter['status']) && !($filter['status'] == '')) {
                $whereClause = $whereClause.$and. "s.status = '".$filter['status']."' ";
                $and = ' AND ';
            };

            if (isset($filter['flightLocator']) && !($filter['flightLocator'] == '')) {
                $whereClause = $whereClause.$and. " s.flightLocator like '%".$filter['flightLocator']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['_boardingDateFrom']) && !($filter['_boardingDateFrom'] == '')) {         
                $whereClause = $whereClause.$and. "s.boardingDate >= '".$filter['_boardingDateFrom']."' ";
                $and = ' AND ';
            };
    
            if (isset($filter['_boardingDateTo']) && !($filter['_boardingDateTo'] == '')) {
                $whereClause = $whereClause.$and. "s.boardingDate <= '".(new \Datetime($filter['_boardingDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $and = ' AND ';
            }
    
            if (isset($filter['_saleDateFrom']) && ($filter['_saleDateFrom'] != '')) {
                $whereClause = $whereClause.$and. "s.issueDate >= '".$filter['_saleDateFrom']."' ";
                $and = ' AND ';
            };
    
            if (isset($filter['_saleDateTo']) && ($filter['_saleDateTo'] != '')) {
                $whereClause = $whereClause.$and. "s.issueDate <= '".(new \Datetime($filter['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $and = ' AND ';
            }
    
            if (isset($filter['_refundDateFrom']) && ($filter['_refundDateFrom'] != '')) {
                $whereClause = $whereClause.$and. "s.refundDate >= '".$filter['_refundDateFrom']."' ";
                $and = ' AND ';
                $orderBy = ' ORDER BY s.refundDate DESC';
            }
    
            if (isset($filter['_refundDateTo']) && ($filter['_refundDateTo'] != '')) {
                $whereClause = $whereClause.$and. "s.refundDate <= '".(new \Datetime($filter['_refundDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $and = ' AND ';
                $orderBy = ' ORDER BY s.refundDate DESC';
            }
    
            if (isset($filter['_returnDateFrom']) && ($filter['_returnDateFrom'] != '')) {
                $whereClause = $whereClause.$and. "s.returnDate >= '".$filter['_returnDateFrom']."' ";
                $and = ' AND ';
                $orderBy = ' ORDER BY s.refundDate DESC';
            }
    
            if (isset($filter['_returnDateTo']) && ($filter['_returnDateTo'] != '')) {
                $whereClause = $whereClause.$and. "s.returnDate <= '".(new \Datetime($filter['_returnDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $and = ' AND ';
                $orderBy = ' ORDER BY s.refundDate DESC';
            }

            $sql = $sql.$join;
            if($whereClause != "") {
                $sql = $sql ."  WHERE " . $whereClause;
            }

            if(isset($dados['order']) && $dados['order'] != '') {
                if($dados['order'] == 'paxName') {
                    $orderBy = ' order by s.pax ASC ';
                }
                if($dados['order'] == 'milesused') {
                    $orderBy = ' order by s.milesUsed ASC ';
                }
                if($dados['order'] == 'id' || $dados['order'] == 'issueDate' || $dados['order'] == 'status' || $dados['order'] == 'client' || $dados['order'] == 'airline' || $dados['order'] == 'flightLocator' || $dados['order'] == 'from' || $dados['order'] == 'to' || $dados['order'] == 'boardingDate' || $dados['order'] == 'externalId') {
                    $orderBy = ' order by s.'.$dados['order'].' ASC ';
                }
            }
            if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
                if($dados['orderDown'] == 'paxName') {
                    $orderBy = ' order by s.pax DESC ';
                }
                if($dados['orderDown'] == 'milesused') {
                    $orderBy = ' order by s.milesUsed DESC ';
                }
                if($dados['orderDown'] == 'id' || $dados['orderDown'] == 'issueDate' || $dados['orderDown'] == 'status' || $dados['orderDown'] == 'client' || $dados['orderDown'] == 'airline' || $dados['orderDown'] == 'flightLocator' || $dados['orderDown'] == 'from' || $dados['orderDown'] == 'to' || $dados['orderDown'] == 'boardingDate' || $dados['orderDown'] == 'externalId') {
                    $orderBy = ' order by s.'.$dados['orderDown'].' DESC ';
                }
            }

            $sql = $sql.$orderBy;
            if(isset($dados['page']) && isset($dados['numPerPage'])) {
                $query = $em->createQuery($sql)
                    ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                    ->setMaxResults($dados['numPerPage']);
            } else {
                $query = $em->createQuery($sql);
            }

            $order = $query->getResult();
        } else {
            $order = array();
        }

        $dataset = array();
        foreach($order as $item){
            $cards = $item->getCards();
            $cards_id = '';
            $blocked = false;
            $cards_number = '';
            $cards_provider = '';
            $cards_type = '';
            if (isset($cards)) {
                $cards_id = $item->getCards()->getId();
                $blocked = ($item->getCards()->getBlocked() == 'W');
                $cards_number = $item->getCards()->getCardNumber();
                $cards_provider = $item->getCards()->getBusinesspartner()->getName();
                $cards_type = $item->getCards()->getCardType();
            };
            
            $partner = $item->getPartner();
            $partner_name = '';
            if (isset($partner)) {
               $partner_name = $partner->getName(); 
            };

            $birthdate = '';
            if ($item->getPax()) {
                if($item->getPax()->getBirthdate()) {
                    $birthdate = $item->getPax()->getBirthdate()->format('Y-m-d');
                }
            }
            
            $SaleProvider = null;
            $saleByThird = $item->getSaleByThird();
            if(isset($saleByThird) && ($saleByThird == 'Y')) {
                $SaleProvider = $item->getProviderSaleByThird()->getName();
                $saleMethod = $item->getProviderSaleByThird()->getName();
            }else{
                $saleMethod = 'Site';
            }

            $airportFrom = '';
            $airportFromName = '';
            $airportLocation = '';
            $international = false;
            if($item->getAirportFrom() != null){
                $airportFrom = $item->getAirportFrom()->getCode();
                $airportFromName = $item->getAirportFrom()->getName();
                $international = ($item->getAirportFrom()->getInternational() == 'true');
                if($international) {
                    if($item->getAirportFrom()->getLocation() != NULL) {
                        $airportLocation = $item->getAirportFrom()->getLocation();
                    }
                }
            }

            $airportTo = '';
            $airportToName = '';
            if($item->getAirportTo() != null){
                $airportTo = $item->getAirportTo()->getCode();
                $airportToName = $item->getAirportTo()->getName();
                if(!$international) {
                    $international = ($item->getAirportTo()->getInternational() == 'true');
                    if($international) {
                        if($item->getAirportTo()->getLocation() != NULL && $airportLocation == '') {
                            $airportLocation = $item->getAirportTo()->getLocation();
                        }
                    }
                }
            }

            $airline = '';
            if($item->getAirline()) {
                $airline = $item->getAirline()->getName();
            }

            $user = '';
            if($item->getUser() != null) {
                $user = $item->getUser()->getName();
            }

            $issuing = '';
            if($item->getIssuing()) {
                $issuing = $item->getIssuing()->getName();
            }

            $daysToFly = $item->getIssueDate()->diff($item->getBoardingDate())->days;
            $refundDate = '';
            if($item->getRefundDate() != null) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }
            $returnDate = '';
            if($item->getReturnDate() != null) {
                $returnDate = $item->getReturnDate()->format('Y-m-d H:i:s');
            }

            $expirationDate = '';
            if($item->getPurchase()) {
                $expirationDate = $item->getPurchase()->getMilesDueDate()->format('Y-m-d');
            }

            $saleCheckedDate = '';
            if($item->getSaleChecked() == 'true') {
                $saleCheckedDate = $item->getSaleCheckedDate()->format('Y-m-d H:i:s');
            }

            $Amount = '';
            $cardTax = '';
            $valueCia = '';
            $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy(array('sale' => $item->getId()));
            foreach ($SaleBillsreceive as $BillsReceives) {
                if($BillsReceives->getBillsreceive()->getAccountType() == 'Reembolso') {
                    $Amount = (float)$BillsReceives->getBillsreceive()->getActualValue();
                }
            }

            $SaleBillspay = $em->getRepository('SaleBillspay')->findBy(array('sale' => $item->getId()));
            foreach ($SaleBillspay as $payment) {
                if($payment->getBillspay()->getAccountType() == 'Reembolso') {
                    if($payment->getBillspay()->getCards()) {
                        $cardTax = $payment->getBillspay()->getCards()->getCardNumber();
                    }
                    $valueCia = (float)$payment->getBillspay()->getActualValue();
                }
            }

            $airlineSolicitation = '';
            if($item->getAirlineSolicitation()) {
                $airlineSolicitation = $item->getAirlineSolicitation()->format('d-m-Y');
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'status' => $item->getStatus(),
                'client' => $item->getClient()->getName(),
                'providerName' => $cards_provider,
                'SaleProvider' => $SaleProvider,
                'email' => $item->getClient()->getEmail(),
                'phoneNumber' => $item->getClient()->getPhoneNumber(),
                'airline' => $airline,
                'paxName' => $item->getPax()->getName(),
                'paxBirthdate' => $birthdate,
                'from' => $airportFrom,
                'to' => $airportTo,
                'milesOriginal' => (int)$item->getMilesOriginal(),
                'milesused' => (int)$item->getMilesUsed(),
                'description' => $item->getDescription(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'landingDate' => $item->getLandingDate()->format('Y-m-d H:i:s'),
                'airportNamefrom' => $airportFromName,
                'airportNameto' => $airportToName,
                'flight' => $item->getFlight(),
                'flightHour' => $item->getFlightHour(),
                'cards_id' => $cards_id,
                'cardNumber' => $cards_number,
                'partnername' => $partner_name,
                'flightLocator' => $item->getFlightLocator(),
                'checkinState' => $item->getCheckinState(),
                'tax' => (float)$item->getTax(),
                'duTax' => (float)$item->getDuTax(),
                'totalCost' => (float)$item->getTotalCost(),
                'amountPaid' => (float)$item->getAmountPaid(),
                'kickback' => (float)$item->getKickback(),
                'extraFee' => (float)$item->getExtraFee(),
                'externalId' => $item->getExternalId(),
                'ticket_code' => $item->getTicketCode(),
                'reservation_code' => $item->getReservationCode(),
                'paxRegistrationCode' => $item->getPax()->getRegistrationCode(),
                'miles_money' => (float)$item->getMilesMoney(),
                'saleByThird' => $item->getSaleByThird(),
                'issuing' => $issuing,
                'processing_time' => $item->getProcessingTime(),
                'sale_method' => $saleMethod,
                'safeType' => $item->getSaleType(),
                'discount' => (float)$item->getDiscount(),
                'user' => $user,
                'daysToFly' => $daysToFly,
                'refundDate' => $refundDate,
                'returnDate' => $returnDate,
                'international' => $international,
                'airportLocation' => $airportLocation,
                'blocked' => $blocked,
                'expirationDate' => $expirationDate,
                'saleType' => $item->getSaleType(),
                'cardTax' => $cardTax,
                'saleChecked' => ($item->getSaleChecked() == 'true'),
                'saleCheckedDate' => $saleCheckedDate,
                'cards_type' => $cards_type,
                'checkinState' => ($item->getRefundChecked() == 'true'),
                'airlineSolicitation' => $airlineSolicitation,
                'valueCia' => $valueCia,
                'cardTax' => $cardTax,
                'amount' => $Amount
            );
        }

        // repricing search
        $sql = "select s FROM Sale s where ".
            " s.status in ('Remarcação Confirmado', 'Remarcação Solicitado', 'Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso CIA', 'Reembolso Pendente', 'Reembolso No-show Solicitado', 'Reembolso No-show Confirmado', 'Reembolso Nao Solicitado', 'Reembolso Perdido', 'Reembolso Confirmado') ";
        $sql = $sql ." and s.repricingDate BETWEEN '".(new \DateTime())->format('Y-m-d')."' and '".(new \DateTime())->modify('+1 day')->format('Y-m-d')."' ";
        $query = $em->createQuery($sql);
        $order = $query->getResult();

        foreach($order as $item){
            $cards = $item->getCards();
            $cards_id = '';
            $blocked = false;
            $cards_number = '';
            $cards_provider = '';
            $cards_type = '';
            if (isset($cards)) {
                $cards_id = $item->getCards()->getId();
                $blocked = ($item->getCards()->getBlocked() == 'W');
                $cards_number = $item->getCards()->getCardNumber();
                $cards_provider = $item->getCards()->getBusinesspartner()->getName();
                $cards_type = $item->getCards()->getCardType();
            };
            
            $partner = $item->getPartner();
            $partner_name = '';
            if (isset($partner)) {
               $partner_name = $partner->getName(); 
            };

            $birthdate = '';
            if ($item->getPax()) {
                if($item->getPax()->getBirthdate()) {
                    $birthdate = $item->getPax()->getBirthdate()->format('Y-m-d');
                }
            }
            
            $SaleProvider = null;
            $saleByThird = $item->getSaleByThird();
            if(isset($saleByThird) && ($saleByThird == 'Y')) {
                $SaleProvider = $item->getProviderSaleByThird()->getName();
                $saleMethod = $item->getProviderSaleByThird()->getName();
            }else{
                $saleMethod = 'Site';
            }

            $airportFrom = '';
            $airportFromName = '';
            $airportLocation = '';
            $international = false;
            if($item->getAirportFrom() != null){
                $airportFrom = $item->getAirportFrom()->getCode();
                $airportFromName = $item->getAirportFrom()->getName();
                $international = ($item->getAirportFrom()->getInternational() == 'true');
                if($international) {
                    if($item->getAirportFrom()->getLocation() != NULL) {
                        $airportLocation = $item->getAirportFrom()->getLocation();
                    }
                }
            }

            $airportTo = '';
            $airportToName = '';
            if($item->getAirportTo() != null){
                $airportTo = $item->getAirportTo()->getCode();
                $airportToName = $item->getAirportTo()->getName();
                if(!$international) {
                    $international = ($item->getAirportTo()->getInternational() == 'true');
                    if($international) {
                        if($item->getAirportTo()->getLocation() != NULL && $airportLocation == '') {
                            $airportLocation = $item->getAirportTo()->getLocation();
                        }
                    }
                }
            }

            $airline = '';
            if($item->getAirline()) {
                $airline = $item->getAirline()->getName();
            }

            $user = '';
            if($item->getUser() != null) {
                $user = $item->getUser()->getName();
            }

            $issuing = '';
            if($item->getIssuing()) {
                $issuing = $item->getIssuing()->getName();
            }

            $daysToFly = $item->getIssueDate()->diff($item->getBoardingDate())->days;
            $refundDate = '';
            if($item->getRefundDate() != null) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }
            $returnDate = '';
            if($item->getReturnDate() != null) {
                $returnDate = $item->getReturnDate()->format('Y-m-d H:i:s');
            }

            $expirationDate = '';
            if($item->getPurchase()) {
                $expirationDate = $item->getPurchase()->getMilesDueDate()->format('Y-m-d');
            }

            $saleCheckedDate = '';
            if($item->getSaleChecked() == 'true') {
                $saleCheckedDate = $item->getSaleCheckedDate()->format('Y-m-d H:i:s');
            }

            $Amount = '';
            $cardTax = '';
            $valueCia = '';
            $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy(array('sale' => $item->getId()));
            foreach ($SaleBillsreceive as $BillsReceives) {
                if($BillsReceives->getBillsreceive()->getAccountType() == 'Remarcação') {
                    $Amount = (float)$BillsReceives->getBillsreceive()->getActualValue();
                }
            }

            $SaleBillspay = $em->getRepository('SaleBillspay')->findBy(array('sale' => $item->getId()));
            foreach ($SaleBillspay as $payment) {
                if($payment->getBillspay()->getAccountType() == 'Remarcação') {
                    if($payment->getBillspay()->getCards()) {
                        $cardTax = $payment->getBillspay()->getCards()->getCardNumber();
                    }
                    $valueCia = (float)$payment->getBillspay()->getActualValue();
                }
            }

            $airlineSolicitation = '';
            if($item->getAirlineSolicitation()) {
                $airlineSolicitation = $item->getAirlineSolicitation()->format('d-m-Y');
            }

            $dataset[] = array(
                                'id' => $item->getId(),
                'status' => $item->getStatus(),
                'client' => $item->getClient()->getName(),
                'providerName' => $cards_provider,
                'SaleProvider' => $SaleProvider,
                'email' => $item->getClient()->getEmail(),
                'phoneNumber' => $item->getClient()->getPhoneNumber(),
                'airline' => $airline,
                'paxName' => $item->getPax()->getName(),
                'paxBirthdate' => $birthdate,
                'from' => $airportFrom,
                'to' => $airportTo,
                'milesOriginal' => (int)$item->getMilesOriginal(),
                'milesused' => (int)$item->getMilesUsed(),
                'description' => $item->getDescription(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'landingDate' => $item->getLandingDate()->format('Y-m-d H:i:s'),
                'airportNamefrom' => $airportFromName,
                'airportNameto' => $airportToName,
                'flight' => $item->getFlight(),
                'flightHour' => $item->getFlightHour(),
                'cards_id' => $cards_id,
                'cardNumber' => $cards_number,
                'partnername' => $partner_name,
                'flightLocator' => $item->getFlightLocator(),
                'checkinState' => $item->getCheckinState(),
                'tax' => (float)$item->getTax(),
                'duTax' => (float)$item->getDuTax(),
                'totalCost' => (float)$item->getTotalCost(),
                'amountPaid' => (float)$item->getAmountPaid(),
                'kickback' => (float)$item->getKickback(),
                'extraFee' => (float)$item->getExtraFee(),
                'externalId' => $item->getExternalId(),
                'ticket_code' => $item->getTicketCode(),
                'reservation_code' => $item->getReservationCode(),
                'paxRegistrationCode' => $item->getPax()->getRegistrationCode(),
                'miles_money' => (float)$item->getMilesMoney(),
                'saleByThird' => $item->getSaleByThird(),
                'issuing' => $issuing,
                'processing_time' => $item->getProcessingTime(),
                'sale_method' => $saleMethod,
                'safeType' => $item->getSaleType(),
                'discount' => (float)$item->getDiscount(),
                'user' => $user,
                'daysToFly' => $daysToFly,
                'refundDate' => $refundDate,
                'returnDate' => $returnDate,
                'international' => $international,
                'airportLocation' => $airportLocation,
                'blocked' => $blocked,
                'expirationDate' => $expirationDate,
                'saleType' => $item->getSaleType(),
                'cardTax' => $cardTax,
                'saleChecked' => ($item->getSaleChecked() == 'true'),
                'saleCheckedDate' => $saleCheckedDate,
                'cards_type' => $cards_type,
                'valueCia' => $valueCia,
                'amount' => $Amount,
                'cardTax' => $cardTax,
                'airlineSolicitation' => $airlineSolicitation,
                'checkinState' => ($item->getRepricingChecked() == 'true')
            );
        }

        $response->setDataset($dataset);
    }

    public function setRefundSaleCheck(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $refundStatus = array(
            'Reembolso Solicitado',
            'Reembolso Pagante Solicitado',
            'Reembolso CIA',
            'Reembolso Pendente',
            'Reembolso No-show Solicitado',
            'Reembolso No-show Confirmado',
            'Reembolso Nao Solicitado',
            'Reembolso Perdido'
        );

        $repricingStatus = array(
            'Remarcação Confirmado',
            'Remarcação Solicitado'
        );

        $em = Application::getInstance()->getEntityManager();
        try {
            $Sale = $em->getRepository('Sale')->find($dados['id']);

            $pos = in_array($Sale->getStatus(), $refundStatus);
            if( !$pos === false ) {
                $Sale->setRepricingChecked($dados['checkinState']);
            } else {
                $Sale->setRefundChecked($dados['checkinState']);
            }

            $em->persist($Sale);
            $em->flush($Sale);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function replicateSale(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['replicate'])) {
            $replicate = $dados['replicate'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        try {
            $OldSale = $em->getRepository('Sale')->find($dados['id']);

            $Sale = clone $OldSale;

            $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $replicate['airline']));
            $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $replicate['providerName'], 'partnerType' => 'P'));

            $Cards = $em->getRepository('Cards')->findOneBy(array('airline' => $Airline->getId(), 'businesspartner' => $Businesspartner->getId()));

            $Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $replicate['client'], 'partnerType' => 'C'));

            $removedMiles = Miles::removeMiles($em, $Cards->getId(), $Sale->getMilesUsed(), $Sale->getId());

            $Sale->setCards($Cards);
            $Sale->setAirline($Airline);
            $Sale->setFlightLocator($replicate['flightLocator']);
            $Sale->setClient($Client);

            $em->persist($Sale);
            $em->flush($Sale);

            $Billsreceive = new \Billsreceive();
            $Billsreceive->setStatus('A');
            $Billsreceive->setClient($Client);
            $Billsreceive->setDescription('Passageiro '.$Sale->getPax()->getName().' - Localizador '.$Sale->getFlightLocator());
            $Billsreceive->setOriginalValue($Sale->getAmountPaid());
            $Billsreceive->setActualValue($Sale->getAmountPaid());
            $Billsreceive->setTax(0);
            $Billsreceive->setDiscount(0);
            $Billsreceive->setAccountType('Venda Bilhete');
            $Billsreceive->setReceiveType('Boleto Bancario');
            $Billsreceive->setDueDate(new \Datetime());
            $em->persist($Billsreceive);
            $em->flush($Billsreceive);

            //
            $SaleBillsreceive = new \SaleBillsreceive();
            $SaleBillsreceive->setBillsreceive($Billsreceive);
            $SaleBillsreceive->setSale($Sale);
            $em->persist($SaleBillsreceive);
            $em->flush($SaleBillsreceive);

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Venda Duplicada - Venda n:".$Sale->getId());
            $SystemLog->setLogType('SALE');
            $SystemLog->setBusinesspartner($BusinessPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function setSalePendencyStatus(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        try {

            $Sale = $em->getRepository('Sale')->find($dados['id']);
            $Sale->setIsPendency($dados['isPendency']);

            $em->persist($Sale);
            $em->flush($Sale);

            if(isset($dados['pendencyDescription']) && $dados['isPendency'] == 'true') {

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Pendencia - Venda n:".$Sale->getId()." - ".$dados['pendencyDescription']);
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadSalePendencys(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        try {

            $sql = "select s FROM SystemLog s where s.logType = 'SALE' and s.description like '%".'Pendencia - Venda n:'.$dados['id']."%' ";
            $query = $em->createQuery($sql);
            $Logs = $query->getResult();

            foreach ($Logs as $log) {

                $BusinessPartner = '';
                if($log->getBusinesspartner()) {
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()))->getName();
                }

                $dataset[] = array(
                    'userName' => $BusinessPartner,
                    'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                    'description' => $log->getDescription()
                );
            }
            $response->setDataset($dataset);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadPendencys(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

        // if($BusinessPartner->getIsMaster() == 'false' && ($BusinessPartner->getIsMaster() == 'false' && $BusinessPartner->getId() != 6500)) {
        //     throw new \Exception("Error Processing Request", 1);
        // }

        $sql = "select s FROM Sale s WHERE s.isPendency = 'true' ";
        $query = $em->createQuery($sql);
        $order = $query->getResult();

        $dataset = array();
        foreach($order as $item){
            $cards = $item->getCards();
            $cards_id = '';
            $blocked = false;
            $cards_number = '';
            $cards_provider = '';
            $cards_type = '';
            if (isset($cards)) {
                $cards_id = $item->getCards()->getId();
                $blocked = ($item->getCards()->getBlocked() == 'W');
                $cards_number = $item->getCards()->getCardNumber();
                $cards_provider = $item->getCards()->getBusinesspartner()->getName();
                $cards_type = $item->getCards()->getCardType();
            };
            
            $partner = $item->getPartner();
            $partner_name = '';
            if (isset($partner)) {
               $partner_name = $partner->getName(); 
            };

            $birthdate = '';
            if ($item->getPax()) {
                if($item->getPax()->getBirthdate()) {
                    $birthdate = $item->getPax()->getBirthdate()->format('Y-m-d');
                }
            }
            
            $SaleProvider = null;
            $saleByThird = $item->getSaleByThird();
            if(isset($saleByThird) && ($saleByThird == 'Y')) {
                $SaleProvider = $item->getProviderSaleByThird()->getName();
                $saleMethod = $item->getProviderSaleByThird()->getName();
            }else{
                $saleMethod = 'Site';
            }

            $airportFrom = '';
            $airportFromName = '';
            $airportLocation = '';
            $international = false;
            if($item->getAirportFrom() != null){
                $airportFrom = $item->getAirportFrom()->getCode();
                $airportFromName = $item->getAirportFrom()->getName();
                $international = ($item->getAirportFrom()->getInternational() == 'true');
                if($international) {
                    if($item->getAirportFrom()->getLocation() != NULL) {
                        $airportLocation = $item->getAirportFrom()->getLocation();
                    }
                }
            }

            $airportTo = '';
            $airportToName = '';
            if($item->getAirportTo() != null){
                $airportTo = $item->getAirportTo()->getCode();
                $airportToName = $item->getAirportTo()->getName();
                if(!$international) {
                    $international = ($item->getAirportTo()->getInternational() == 'true');
                    if($international) {
                        if($item->getAirportTo()->getLocation() != NULL && $airportLocation == '') {
                            $airportLocation = $item->getAirportTo()->getLocation();
                        }
                    }
                }
            }

            $airline = '';
            if($item->getAirline()) {
                $airline = $item->getAirline()->getName();
            }

            $user = '';
            if($item->getUser() != null) {
                $user = $item->getUser()->getName();
            }

            $issuing = '';
            if($item->getIssuing()) {
                $issuing = $item->getIssuing()->getName();
            }

            $daysToFly = $item->getIssueDate()->diff($item->getBoardingDate())->days;
            $refundDate = '';
            if($item->getRefundDate() != null) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }
            $returnDate = '';
            if($item->getReturnDate() != null) {
                $returnDate = $item->getReturnDate()->format('Y-m-d H:i:s');
            }

            $expirationDate = '';
            if($item->getPurchase()) {
                $expirationDate = $item->getPurchase()->getMilesDueDate()->format('Y-m-d');
            }

            $cardTax = '';
            if($item->getCardTax()) {
                $cardTax = $item->getCardTax()->getCardNumber();
            }

            $saleCheckedDate = '';
            if($item->getSaleChecked() == 'true') {
                $saleCheckedDate = $item->getSaleCheckedDate()->format('Y-m-d H:i:s');
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'status' => $item->getStatus(),
                'client' => $item->getClient()->getName(),
                'providerName' => $cards_provider,
                'SaleProvider' => $SaleProvider,
                'email' => $item->getClient()->getEmail(),
                'phoneNumber' => $item->getClient()->getPhoneNumber(),
                'airline' => $airline,
                'paxName' => $item->getPax()->getName(),
                'paxBirthdate' => $birthdate,
                'from' => $airportFrom,
                'to' => $airportTo,
                'milesOriginal' => (int)$item->getMilesOriginal(),
                'milesused' => (int)$item->getMilesUsed(),
                'description' => $item->getDescription(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'landingDate' => $item->getLandingDate()->format('Y-m-d H:i:s'),
                'airportNamefrom' => $airportFromName,
                'airportNameto' => $airportToName,
                'flight' => $item->getFlight(),
                'flightHour' => $item->getFlightHour(),
                'cards_id' => $cards_id,
                'cardNumber' => $cards_number,
                'partnername' => $partner_name,
                'flightLocator' => $item->getFlightLocator(),
                'checkinState' => $item->getCheckinState(),
                'tax' => (float)$item->getTax(),
                'duTax' => (float)$item->getDuTax(),
                'totalCost' => (float)$item->getTotalCost(),
                'amountPaid' => (float)$item->getAmountPaid(),
                'kickback' => (float)$item->getKickback(),
                'extraFee' => (float)$item->getExtraFee(),
                'externalId' => $item->getExternalId(),
                'ticket_code' => $item->getTicketCode(),
                'reservation_code' => $item->getReservationCode(),
                'paxRegistrationCode' => $item->getPax()->getRegistrationCode(),
                'miles_money' => (float)$item->getMilesMoney(),
                'saleByThird' => $item->getSaleByThird(),
                'issuing' => $issuing,
                'processing_time' => $item->getProcessingTime(),
                'sale_method' => $saleMethod,
                'safeType' => $item->getSaleType(),
                'discount' => (float)$item->getDiscount(),
                'user' => $user,
                'daysToFly' => $daysToFly,
                'refundDate' => $refundDate,
                'returnDate' => $returnDate,
                'international' => $international,
                'airportLocation' => $airportLocation,
                'blocked' => $blocked,
                'expirationDate' => $expirationDate,
                'saleType' => $item->getSaleType(),
                'cardTax' => $cardTax,
                'saleChecked' => ($item->getSaleChecked() == 'true'),
                'saleCheckedDate' => $saleCheckedDate,
                'cards_type' => $cards_type,
                'isPendency' => ($item->getIsPendency() == 'true')
            );

        }
        $response->setDataset($dataset);
    }

    public function loadSaleRefunds(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $Card = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));

        $em = Application::getInstance()->getEntityManager();
        $sql = "select s FROM Sale s WHERE s.cards = '".$dados['cards_id']."' and s.status in ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Pendente', 'Reembolso CIA', 'Reembolso No-show Solicitado', 'Cancelamento Solicitado', 'Cancelamento Nao Solicitado', 'Remarcação Solicitado') order by s.id DESC";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();
        $dataset = array();
        foreach ($Sales as $sale) {
            $em = Application::getInstance()->getEntityManager();
            $businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale->getPax()->getId()));

            $refundDate = '';
            if($sale->getRefundDate()) {
                $refundDate = $sale->getRefundDate()->format('Y-m-d H:i:s');
            }

            $returnDate = '';
            if($sale->getReturnDate()) {
                $returnDate = $sale->getReturnDate()->format('Y-m-d H:i:s');
            }

            $to = '';
            if($sale->getAirportTo()) {
                $to = $sale->getAirportTo()->getName();
            }

            $from = '';
            if($sale->getAirportFrom()) {
                $from = $sale->getAirportFrom()->getName();
            }

            $miles_used = (float)$sale->getMilesUsed();
            if($sale->getStatus() == 'Remarcação Solicitado') {
                $miles_used = (float)$sale->getPointsWaiting();
            }

            $dataset[] = array(
                'issue_date' => $sale->getIssueDate()->format('Y-m-d H:i:s'),
                'pax_name' => $businesspartner->getName(),
                'flight' => $sale->getFlight(),
                'boardingDate' => $sale->getBoardingDate()->format('Y-m-d H:i:s'),
                'landingDate' => $sale->getLandingDate()->format('Y-m-d H:i:s'),
                'from' => $from,
                'to' => $to,
                'miles_used' => $miles_used,
                'status' => $sale->getStatus(),
                'flight_locator' => $sale->getFlightLocator(),
                'refundDate' => $refundDate,
                'returnDate' => $returnDate,
                'ticket_code' => $sale->getTicketCode()
            );
        }
        $response->setDataset($dataset);
    }

    public function revertStatus(Request $request, Response $response) {
        if(isset($request->getRow()['businesspartner'])) {
            $BusinessPartner = $request->getRow()['businesspartner'];
        }
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        try {

            $hash = $request->getRow()['hashId'];

            $Sale = $em->getRepository('Sale')->find($dados['id']);

            if($Sale->getStatus() == 'Reembolso Solicitado') {
                $Sale->setStatus('Emitido');

                $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy( array( 'sale' => $dados['id'] ) );
                foreach ($SaleBillsreceive as $key => $value) {
                    if($value->getBillsreceive()->getAccountType() == 'Reembolso' && $value->getBillsreceive()->getStatus() == 'A') {
                        $Billsreceive = $value->getBillsreceive();

                        $em->remove($value);
                        $em->flush($value);

                        $em->remove($Billsreceive);
                        $em->flush($Billsreceive);
                    }
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Reembolso Solicitado' para 'Emitido'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);

            } else if ($Sale->getStatus() == 'Reembolso CIA') {
                $Sale->setStatus('Reembolso Solicitado');

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Reembolso CIA' para 'Reembolso Solicitado'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            } else if ($Sale->getStatus() == 'Reembolso Confirmado') {
                $Sale->setStatus('Reembolso Solicitado');

                if($Sale->getSaleByThird() != 'Y') {
                    $removedMiles = Miles::removeMiles($em, $dados['cards_id'], (float)$Sale->getMilesUsed(), $Sale->getId());
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Reembolso Confirmado' para 'Reembolso Solicitado'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            } else if($Sale->getStatus() == 'Cancelamento Solicitado') {
                $Sale->setStatus('Emitido');

                $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy( array( 'sale' => $dados['id'] ) );
                foreach ($SaleBillsreceive as $key => $value) {
                    if($value->getBillsreceive()->getAccountType() == 'Cancelamento' && $value->getBillsreceive()->getStatus() == 'A') {
                        $Billsreceive = $value->getBillsreceive();

                        $Billsreceive->setAccountType('Venda Bilhete');
                        $Billsreceive->setOriginalValue($Sale->getAmountPaid());
                        $Billsreceive->setActualValue($Sale->getAmountPaid());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);
                    }
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Cancelamento Solicitado' para 'Emitido'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);

            } else if ($Sale->getStatus() == 'Cancelamento Efetivado') {
                $Sale->setStatus('Cancelamento Solicitado');

                if($Sale->getSaleByThird() != 'Y') {
                    $removedMiles = Miles::removeMiles($em, $dados['cards_id'], (float)$Sale->getMilesUsed(), $Sale->getId());
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Cancelamento Efetivado' para 'Cancelamento Solicitado'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            } else if ($Sale->getStatus() == 'Reembolso No-show Solicitado') {
                $Sale->setStatus('Emitido');

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Reembolso No-show Solicitado' para 'Emitido'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            } else if ($Sale->getStatus() == 'Reembolso No-show Confirmado') {
                $Sale->setStatus('Reembolso No-show Solicitado');

                if($Sale->getSaleByThird() != 'Y') {
                    $removedMiles = Miles::removeMiles($em, $dados['cards_id'], (float)$Sale->getMilesUsed(), $Sale->getId());
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Reembolso No-show Confirmado' para 'Reembolso No-show Solicitado'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            } else if ($Sale->getStatus() == 'Reembolso Nao Solicitado') {
                $Sale->setStatus('Reembolso Solicitado');

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Reembolso Nao Solicitado' para 'Reembolso Solicitado'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            } else if ($Sale->getStatus() == 'Reembolso Perdido') {
                $Sale->setStatus('Reembolso Solicitado');

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Reembolso Perdido' para 'Reembolso Solicitado'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            } else if ($Sale->getStatus() == 'Remarcação Solicitado' ) {
                $Sale->setStatus('Emitido');

                $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy( array( 'sale' => $dados['id'] ) );
                foreach ($SaleBillsreceive as $key => $value) {
                    if($value->getBillsreceive()->getAccountType() == 'Remarcação' && $value->getBillsreceive()->getStatus() == 'A') {
                        $Billsreceive = $value->getBillsreceive();

                        $em->remove($value);
                        $em->flush($value);

                        $em->remove($Billsreceive);
                        $em->flush($Billsreceive);
                    }
                }

                $SaleBillspay = $em->getRepository('SaleBillspay')->findBy( array( 'sale' => $dados['id'] ) );
                foreach ($SaleBillspay as $key => $value) {
                    if($value->getBillspay()->getAccountType() == 'Remarcação' && $value->getBillspay()->getStatus() == 'A') {
                        $Billsreceive = $value->getBillspay();

                        $em->remove($value);
                        $em->flush($value);

                        $em->remove($Billsreceive);
                        $em->flush($Billsreceive);
                    }
                }

                $hash = $request->getRow()['hashId'];
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $Cards = $Sale->getCards();
                if($Cards && $Sale->getSaleByThird() != 'Y') {
                    $removedMiles = Miles::addMiles($em, $Cards->getId(), $Sale->getPointsWaiting(), $Sale->getId(), 'REPRICING', $BusinessPartner, 0);
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Remarcação Solicitado' para 'Emitido'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);

            } else if ($Sale->getStatus() == 'Remarcação Confirmado' ) {
                $Sale->setStatus('Remarcação Solicitado');

                $Cards = $Sale->getCards();
                if($Cards && $Sale->getSaleByThird() != 'Y') {
                    $removedMiles = Miles::addMiles($em, $Cards->getId(), $Sale->getPointsWaiting(), $Sale->getId(), 'REPRICING', $BusinessPartner, 0);
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Revertida - Venda n:".$Sale->getId()." - Usuario:".$BusinessPartner->getName()." <br> De 'Remarcação Confirmado' para 'Remarcação Solicitado'");
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($BusinessPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);

            }

            $em->persist($Sale);
            $em->flush($Sale);

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

    public function saveSaleCheckAll(Request $request, Response $response) {
        if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }
        $hash = $request->getRow()['hashId'];
        $saleData = $request->getRow();
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $Sales = $em->getRepository('Sale')->findBy(
                array(
                    'externalId' => $dados['id']
                )
            );

            foreach ($Sales as $key => $value) {
                $value->setSaleCheckedDate(new \DateTime());
                $value->setSaleChecked('true');

                $em->persist($value);
                $em->flush($value);

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Verificada - Venda n:".$value->getId()." - Usuario: ".$UserPartner->getName());
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($UserPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveSaleDoubleCheckAll(Request $request, Response $response) {
        if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }
        $hash = $request->getRow()['hashId'];
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $Sales = $em->getRepository('Sale')->findBy(
                array(
                    'externalId' => $dados['id']
                )
            );

            foreach ($Sales as $key => $value) {
                $value->setSaleCheckedDate2(new \DateTime());
                $value->setSaleChecked2('true');

                $em->persist($value);
                $em->flush($value);


                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Venda Verificada - Venda n:".$value->getId()." - Usuario: ".$UserPartner->getName());
                $SystemLog->setLogType('SALE');
                $SystemLog->setBusinesspartner($UserPartner);
                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadSalesToOperations(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        
        $whereClause = "";
        $orderBy = ' ORDER BY s.status DESC, s.id DESC';
        $and = "";
        $join = "";
        if(isset($filter)) {
            $sql = "select s FROM Sale s ";

            if (isset($filter['providerName']) && !($filter['providerName'] == '')) {
                $whereClause = $whereClause.$and. " b.name like '%".$filter['providerName']."%' ";
                $and = ' AND ';
            };
            if (isset($filter['providerRegistrationCode']) && !($filter['providerRegistrationCode'] == '')) {
                $whereClause = $whereClause.$and. "b.registrationCode = '".$filter['providerRegistrationCode']."' ";
                $and = ' AND ';
            };

            if( (isset($filter['providerRegistrationCode']) && !($filter['providerRegistrationCode'] == '')) || (isset($filter['providerName']) && !($filter['providerName'] == '')) ) {
                if(!isset($filter['cardNumber'])) {
                    $join .= ' LEFT JOIN s.cards c ';
                }
                $join .= ' LEFT JOIN c.businesspartner b ';
            }
    
            if (isset($filter['airline']) && !($filter['airline'] == '')) {
                $whereClause = $whereClause.$and. " a.name like '%".$filter['airline']."%' ";
                $join .= ' LEFT JOIN s.airline a ';
                $and = ' AND ';
            };
    
            if (isset($filter['client']) && !($filter['client'] == '')) {
                $whereClause = $whereClause.$and. "x.name = '".$filter['client']."' ";
                $join .= ' LEFT JOIN s.client x ';
                $and = ' AND ';
            };
    
            if (isset($filter['cardNumber']) && !($filter['cardNumber'] == '')) {
                $whereClause = $whereClause.$and. "c.cardNumber = '".$filter['cardNumber']."' ";
                $join .= ' LEFT JOIN s.cards c ';
                $and = ' AND ';
            };
    
            if (isset($filter['paxName']) && !($filter['paxName'] == '')) {
                $whereClause = $whereClause.$and. "p.name like '%".$filter['paxName']."%' ";
                $join .= ' LEFT JOIN s.pax p ';
                $and = ' AND ';
            };

            if (isset($filter['status']) && !($filter['status'] == '')) {
                $whereClause = $whereClause.$and. "s.status = '".$filter['status']."' ";
                $and = ' AND ';
            };
    
            if (isset($filter['ticket_code']) && !($filter['ticket_code'] == '')) {
                $whereClause = $whereClause.$and. " s.ticketCode LIKE '%".$filter['ticket_code']."%' ";
                $and = ' AND ';
            };
    
            if (isset($filter['flightLocator']) && !($filter['flightLocator'] == '')) {
                $whereClause = $whereClause.$and. " s.flightLocator like '%".$filter['flightLocator']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['externalid']) && !($filter['externalid'] == '')) {
                $whereClause = $whereClause.$and. "s.externalId = '".$filter['externalid']."' ";
                $and = ' AND ';
            };

            if (isset($filter['_boardingDateFrom']) && !($filter['_boardingDateFrom'] == '')) {         
                $whereClause = $whereClause.$and. "s.boardingDate >= '".$filter['_boardingDateFrom']."' ";
                $and = ' AND ';
            };
    
            if (isset($filter['_boardingDateTo']) && !($filter['_boardingDateTo'] == '')) {
                $whereClause = $whereClause.$and. "s.boardingDate <= '".(new \Datetime($filter['_boardingDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $and = ' AND ';
            }
    
            if (isset($filter['_saleDateFrom']) && ($filter['_saleDateFrom'] != '')) {
                $whereClause = $whereClause.$and. "s.issueDate >= '".$filter['_saleDateFrom']."' ";
                $and = ' AND ';
            };
    
            if (isset($filter['_saleDateTo']) && ($filter['_saleDateTo'] != '')) {
                $whereClause = $whereClause.$and. "s.issueDate <= '".(new \Datetime($filter['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $and = ' AND ';
            }
    
            if (isset($filter['_refundDateFrom']) && ($filter['_refundDateFrom'] != '')) {
                $whereClause = $whereClause.$and. "s.refundDate >= '".$filter['_refundDateFrom']."' ";
                $and = ' AND ';
            }
    
            if (isset($filter['_refundDateTo']) && ($filter['_refundDateTo'] != '')) {
                $whereClause = $whereClause.$and. "s.refundDate <= '".(new \Datetime($filter['_refundDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $and = ' AND ';
            }

            if( !array_key_exists("status", $filter) && ( (isset($filter['_returnDateFrom']) && ($filter['_returnDateFrom'] != '')) 
                || (isset($filter['_returnDateTo']) && ($filter['_returnDateTo'] != ''))  ) ) {

                $whereClause = $whereClause.$and. "s.status  IN('Reembolso Confirmado' ,'Remarcação Confirmado' ,'Reembolso No-show Confirmado' , 'Cancelamento Efetivado') ";
                $and = ' AND ';

                if (isset($filter['_returnDateFrom']) && ($filter['_returnDateFrom'] != '')) {
                    $whereClause = $whereClause.$and. "s.refundDate >= '".$filter['_returnDateFrom']."' ";
                    $and = ' AND ';
                }
            
                if (isset($filter['_returnDateTo']) && ($filter['_returnDateTo'] != '')) {
                    $whereClause = $whereClause.$and. "s.returnDate <= '".(new \Datetime($filter['_returnDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                    $and = ' AND ';
                }
            } else {
                if (isset($filter['_returnDateFrom']) && ($filter['_returnDateFrom'] != '')) {
                    $whereClause = $whereClause.$and. "s.refundDate >= '".$filter['_returnDateFrom']."' ";
                    $and = ' AND ';
                }
        
                if (isset($filter['_returnDateTo']) && ($filter['_returnDateTo'] != '')) {
                    $whereClause = $whereClause.$and. "s.returnDate <= '".(new \Datetime($filter['_returnDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                    $and = ' AND ';
                }
            }

            if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
                $whereClause = $whereClause.$and . " ( "
                    ." s.flightLocator like '%".$dados['searchKeywords']."%' ) ";
                $and = ' AND ';
            }

            $sql = $sql.$join;
            if($whereClause != "") {
                $sql = $sql ."  WHERE " . $whereClause;
            }

            if(isset($dados['order']) && $dados['order'] != '') {
                if($dados['order'] == 'paxName') {
                    $orderBy = ' order by s.pax ASC ';
                }
                if($dados['order'] == 'milesused') {
                    $orderBy = ' order by s.milesUsed ASC ';
                }
                if($dados['order'] == 'id' || $dados['order'] == 'issueDate' || $dados['order'] == 'status' || $dados['order'] == 'client' || $dados['order'] == 'airline' || $dados['order'] == 'flightLocator' || $dados['order'] == 'from' || $dados['order'] == 'to' || $dados['order'] == 'boardingDate' || $dados['order'] == 'externalId') {
                    $orderBy = ' order by s.'.$dados['order'].' ASC ';
                }
            }
            if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
                if($dados['orderDown'] == 'paxName') {
                    $orderBy = ' order by s.pax DESC ';
                }
                if($dados['orderDown'] == 'milesused') {
                    $orderBy = ' order by s.milesUsed DESC ';
                }
                if($dados['orderDown'] == 'id' || $dados['orderDown'] == 'issueDate' || $dados['orderDown'] == 'status' || $dados['orderDown'] == 'client' || $dados['orderDown'] == 'airline' || $dados['orderDown'] == 'flightLocator' || $dados['orderDown'] == 'from' || $dados['orderDown'] == 'to' || $dados['orderDown'] == 'boardingDate' || $dados['orderDown'] == 'externalId') {
                    $orderBy = ' order by s.'.$dados['orderDown'].' DESC ';
                }
            }

            $sql = $sql.$orderBy;
            if(isset($dados['page']) && isset($dados['numPerPage'])) {
                $query = $em->createQuery($sql)
                    ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                    ->setMaxResults($dados['numPerPage']);
            } else {
                $query = $em->createQuery($sql);
            }

            $order = $query->getResult();
        } else {
            $order = array();
        }

        $sales = array();
        foreach($order as $item){
            $cards = $item->getCards();
            $cards_id = '';
            $blocked = false;
            $cards_number = '';
            $cards_provider = '';
            $cards_type = '';
            if (isset($cards)) {
                $cards_id = $item->getCards()->getId();
                $blocked = ($item->getCards()->getBlocked() == 'W');
                $cards_number = $item->getCards()->getCardNumber();
                $cards_provider = $item->getCards()->getBusinesspartner()->getName();
                $cards_type = $item->getCards()->getCardType();
            };
            
            $partner = $item->getPartner();
            $partner_name = '';
            if (isset($partner)) {
               $partner_name = $partner->getName(); 
            };

            $birthdate = '';
            if ($item->getPax()) {
                if($item->getPax()->getBirthdate()) {
                    $birthdate = $item->getPax()->getBirthdate()->format('Y-m-d');
                }
            }
            
            $SaleProvider = null;
            $saleByThird = $item->getSaleByThird();
            if(isset($saleByThird) && ($saleByThird == 'Y')) {
                if($item->getProviderSaleByThird()) {
                    $SaleProvider = $item->getProviderSaleByThird()->getName();
                }
                if($item->getProviderSaleByThird()) {
                    $saleMethod = $item->getProviderSaleByThird()->getName();
                }
            }else{
                $saleMethod = 'Site';
            }

            $airportFrom = '';
            $airportFromName = '';
            $airportLocation = '';
            $international = false;
            if($item->getAirportFrom() != null){
                $airportFrom = $item->getAirportFrom()->getCode();
                $airportFromName = $item->getAirportFrom()->getName();
                $international = ($item->getAirportFrom()->getInternational() == 'true');
                if($international) {
                    if($item->getAirportFrom()->getLocation() != NULL) {
                        $airportLocation = $item->getAirportFrom()->getLocation();
                    }
                }
            }

            $airportTo = '';
            $airportToName = '';
            if($item->getAirportTo() != null){
                $airportTo = $item->getAirportTo()->getCode();
                $airportToName = $item->getAirportTo()->getName();
                if(!$international) {
                    $international = ($item->getAirportTo()->getInternational() == 'true');
                    if($international) {
                        if($item->getAirportTo()->getLocation() != NULL && $airportLocation == '') {
                            $airportLocation = $item->getAirportTo()->getLocation();
                        }
                    }
                }
            }

            $airline = '';
            if($item->getAirline()) {
                $airline = $item->getAirline()->getName();
            }

            $user = '';
            if($item->getUser() != null) {
                $user = $item->getUser()->getName();
            }

            $issuing = '';
            if($item->getIssuing()) {
                $issuing = $item->getIssuing()->getName();
            }

            $daysToFly = $item->getIssueDate()->diff($item->getBoardingDate())->days;
            $refundDate = '';
            if($item->getRefundDate() != null) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }
            $returnDate = '';
            if($item->getReturnDate() != null) {
                $returnDate = $item->getReturnDate()->format('Y-m-d H:i:s');
            }

            $expirationDate = '';
            if($item->getPurchase()) {
                $expirationDate = $item->getPurchase()->getMilesDueDate()->format('Y-m-d');
            }

            $cardTax = '';
            if($item->getCardTax()) {
                $cardTax = $item->getCardTax()->getCardNumber();
            }

            $saleCheckedDate = '';
            if($item->getSaleChecked() == 'true') {
                $saleCheckedDate = $item->getSaleCheckedDate()->format('Y-m-d H:i:s');
            }

            $category = '';
            if( $item->getAirportFrom() && $item->getAirportTo() && $airline == 'AZUL' ) {
                $category = 'Competitive';
                $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
                    array( 'flightFrom' => $item->getAirportFrom()->getCode(), 'flightTo' => $item->getAirportTo()->getCode() )
                );
                if($FlightPathCategory) {
                    $category = $FlightPathCategory->getFlightCategory()->getName();
                }
            }

            $notificationcode = '';
            if($item->getExternalId()) {
                $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $item->getExternalId()));
                if($OnlineOrder) {
                    $notificationcode = $OnlineOrder->getNotificationcode();
                }
            }

            $sales[] = array(
                'id' => $item->getId(),
                'status' => $item->getStatus(),
                'client' => $item->getClient()->getName(),
                'providerName' => $cards_provider,
                'SaleProvider' => $SaleProvider,
                'email' => $item->getClient()->getEmail(),
                'phoneNumber' => $item->getClient()->getPhoneNumber(),
                'airline' => $airline,
                'paxName' => $item->getPax()->getName(),
                'paxBirthdate' => $birthdate,
                'from' => $airportFrom,
                'to' => $airportTo,
                'milesOriginal' => (int)$item->getMilesOriginal(),
                'milesused' => (int)$item->getMilesUsed(),
                'description' => $item->getDescription(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'landingDate' => $item->getLandingDate()->format('Y-m-d H:i:s'),
                'airportNamefrom' => $airportFromName,
                'airportNameto' => $airportToName,
                'flight' => $item->getFlight(),
                'flightHour' => $item->getFlightHour(),
                'cards_id' => $cards_id,
                'cardNumber' => $cards_number,
                'partnername' => $partner_name,
                'flightLocator' => $item->getFlightLocator(),
                'checkinState' => $item->getCheckinState(),
                'tax' => (float)$item->getTax(),
                'duTax' => (float)$item->getDuTax(),
                'totalCost' => (float)$item->getTotalCost(),
                'amountPaid' => (float)$item->getAmountPaid(),
                'kickback' => (float)$item->getKickback(),
                'extraFee' => (float)$item->getExtraFee(),
                'externalId' => $item->getExternalId(),
                'ticket_code' => $item->getTicketCode(),
                'reservation_code' => $item->getReservationCode(),
                'paxRegistrationCode' => $item->getPax()->getRegistrationCode(),
                'miles_money' => (float)$item->getMilesMoney(),
                'saleByThird' => $item->getSaleByThird(),
                'issuing' => $issuing,
                'processing_time' => $item->getProcessingTime(),
                'sale_method' => $saleMethod,
                'safeType' => $item->getSaleType(),
                'discount' => (float)$item->getDiscount(),
                'user' => $user,
                'daysToFly' => $daysToFly,
                'refundDate' => $refundDate,
                'returnDate' => $returnDate,
                'international' => $international,
                'airportLocation' => $airportLocation,
                'blocked' => $blocked,
                'expirationDate' => $expirationDate,
                'saleType' => $item->getSaleType(),
                'cardTax' => $cardTax,
                'saleChecked' => ($item->getSaleChecked() == 'true'),
                'saleCheckedDate' => $saleCheckedDate,
                'cards_type' => $cards_type,
                'tax_billet' => (float)$item->getTaxBillet(),
                'flight_category' => $category,
                'baggage_price'  => (float)$item->getBaggagePrice(),
                'special_seat'  => (float)$item->getSpecialSeat(),
                'isExtra' => ($item->getIsExtra() == 'true'),
                'notificationcode' => $notificationcode,
                'cardTax' => $cardTax
            );

        }

        if(isset($filter)) {
            $sql = "select COUNT(s) as quant FROM Sale s ";

            $sql = $sql.$join;
            if($whereClause != "") {
                $sql = $sql ."  WHERE " . $whereClause;
            }

            $query = $em->createQuery($sql);
            $Quant = $query->getResult();
        } else {
            $Quant[0]['quant'] = 0;
        }

        function array_orderby() {
            $args = func_get_args();
            $data = array_shift($args);
            foreach ($args as $n => $field) {
                if (is_string($field)) {
                    $tmp = array();
                    foreach ($data as $key => $row)
                        $tmp[$key] = $row[$field];
                        $args[$n] = $tmp;
                    }
            }
            $args[] = &$data;
            call_user_func_array('array_multisort', $args);
            return array_pop($args);
        }
        if(isset($dados['refund_report']) && $dados['refund_report'] == true) {
            foreach ($sales as $key => $value) {
                if($value['saleByThird'] == 'Y') {
                    $value['providerName'] = $value['sale_method'];
                }
            }
            $sales = array_orderby($sales, 'providerName', SORT_ASC, 'flightLocator', SORT_ASC);
        }

        $dataset = array(
            'sales' => $sales,
            'total' => $Quant[0]['quant']
        );

        $response->setDataset($dataset);
    }

    function saveAsDiamond(Request $request, Response $response) {
        $hash = $request->getRow()['hashId'];
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();
        
        $card = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
        $sale = $em->getRepository('Sale')->findOneBy(array('id' => $dados['id']));

        $dados['is_diamond'] = filter_var($dados['is_diamond'], FILTER_VALIDATE_BOOLEAN);

        if(!$dados['is_diamond']) {
            // Setar venda como não diamante
            $sale->setIsDiamond(false);
            $em->persist($sale);
            $em->flush();

            $dataset = array(
                'type_message' => 'success',
                'message' => 'A venda deixou de ser diamante'
            );
        } else {
            $maxDiamondPax = $card->getMaxDiamondPax();
            $maxDiamondPax = (!empty($maxDiamondPax) && $maxDiamondPax > 0) ? $maxDiamondPax : 0;

            $diamondCount = 0;

            foreach($card->getSales() as $diamondSales) {
                if($diamondSales->getIsDiamond())
                    $diamondCount++;
            }

            if($maxDiamondPax == 0 || $diamondCount >= $maxDiamondPax) {
                $dataset = array(
                    'type_message' => 'error',
                    'message' => 'Não existem mais vagas diamantes no cartão/fornecedor selecionado'
                );
            } else {
                // Setar venda como diamante
                $sale->setIsDiamond(true);
                $em->persist($sale);
                $em->flush();

                $dataset = array(
                    'type_message' => 'success',
                    'message' => 'A venda agora é diamante!'
                );
            }
        }

        $response->setDataset($dataset);
    }
}
