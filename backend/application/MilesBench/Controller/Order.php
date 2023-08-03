<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class order {

    public function loadOrder(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select s FROM Sale s WHERE s.status not in ('X','C') and s.issueDate >= '".(new \Datetime())->format('Y-m-d')."' ORDER BY s.status DESC, s.id DESC";
        $query = $em->createQuery($sql);
        $order = $query->setMaxResults(1000)->getResult();

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
                'notificationcode' => $notificationcode,
                'taxOnlinePayment' => (float)$item->getTaxOnlinePayment(),
                'taxOnlineValidation' => (float)$item->getTaxOnlineValidation(),
                'baggage_price'  => (float)$item->getBaggagePrice(),
                'special_seat'  => (float)$item->getSpecialSeat(),
                'is_diamond' => $item->getIsDiamond()
            );

        }
        $response->setDataset($dataset);
    }

    public function loadOpened(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $order = $em->getRepository('Sale')->findBy(array('status' => 'Pendente'));

        $dataset = array();
        foreach($order as $item){
            $cards = $item->getCards();
            if (isset($cards)) {
                $cards_id = $item->getCards()->getId();
            } else {
                $cards_id = '';
            }
            
            $dataset[] = array(
                'id' => $item->getId(),
                'status' => $item->getStatus(),
                'client' => $item->getClient()->getName(),
                'email' => $item->getClient()->getEmail(),
                'phoneNumber' => $item->getClient()->getPhoneNumber(),
                'airline' => $item->getAirline()->getName(),
                'paxName'=>$item->getPax()->getName(),
                'from' => $item->getAirportFrom()->getCode(),
                'to' => $item->getAirportTo()->getCode(),
                'milesUsed' => $item->getMilesUsed(),
                'issueDate' => $item->getIssueDate()->format('d/m/y'),
                'boardingDate' => $item->getBoardingDate()->format('d/m/y'),
                'landingDate' => $item->getLandingDate()->format('d/m/y'),
                'airportNamefrom' => $item->getAirportFrom()->getName(),
                'airportNameto' => $item->getAirportTo()->getName(),
                'flight' => $item->getFlight(),
                'flightHour' => $item->getFlightHour(),
                'cards' => $cards_id,
                'miles_money' => $item->getMilesMoney(),
                'saleByThird' => $item->getSaleByThird(),
                'issuing' => $item->getIssuing(),
                'processing_time' => $item->getProcessingTime()
            );

        }
        $response->setDataset($dataset);
    }

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['divers'])) {
            $divers = $dados['divers'];
        }
		if (isset($dados['order'])) {
            $order = $dados['order'];
        }
        if (isset($dados['paxs'])) {
            $paxs = $dados['paxs'];
        }
        if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $changes = '';
        $and = '';

        try {
            $em = Application::getInstance()->getEntityManager();
            $em->getConnection()->beginTransaction();

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            if (isset($dados['id'])) {
                $originalSale = $em->getRepository('Sale')->find($dados['id']);
            }
            if(isset($order['client_name'])) {
                $client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));
            }
            
            if (isset($dados['paxName']) && $dados['paxName'] != ''){
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['paxName']));
                if (!$BusinessPartner) {
                    $BusinessPartner = new \Businesspartner();
                    $BusinessPartner->setName($dados['paxName']);
                    if (isset($dados['paxRegistrationCode'])){
                        $BusinessPartner->setRegistrationCode($dados['paxRegistrationCode']);
                    }
                    $BusinessPartner->setPartnerType('X');
                } else {
                    if (strpos($BusinessPartner->getPartnerType(),'X')) {
                        $BusinessPartner->setPartnerType($BusinessPartner->getPartnerType()+'_X');
                    }
                }
                if (isset($dados['_paxBirthdate'])){
                    $BusinessPartner->setBirthdate(new \Datetime($dados['_paxBirthdate']));
                }
                $em->persist($BusinessPartner);
                $em->flush($BusinessPartner);
                $sale_pax = $BusinessPartner;
            }

            if (isset($dados['cardNumber'])){
                $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
                $sale_cards = $Cards;
            }
            
            if(isset($dados['miles_used']) && $dados['miles_used'] != ''){
                $milesUsed = $dados['miles_used'];
            }

            $onlineOrder = new \OnlineOrder();
            $onlineOrder->setExternalId('');
            $onlineOrder->setClientName($order['client_name']);
            if(isset($order['issuing'])) {
                $onlineOrder->setClientLogin($order['issuing']);
            }
            $onlineOrder->setClientEmail('');
            $onlineOrder->setStatus('EMITIDO');
            $onlineOrder->setMilesUsed(0);
            if(isset($order['comments'])) {
                $onlineOrder->setComments($order['comments']);
            }
            $onlineOrder->setBoardingDate(new \Datetime());
            $onlineOrder->setLandingDate(new \Datetime());
            $onlineOrder->setCreatedAt(new \DateTime());
            $onlineOrder->setAirline('');
            $onlineOrder->setMilesUsed(0);
            $onlineOrder->setTotalCost(0);
            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $onlineFlight = new \OnlineFlight();
            $onlineFlight->setOrder($onlineOrder);
            $onlineFlight->setAirline($dados['airline']);

            if(isset($dados['airport_description_from'])) {
                $AirportFrom = $em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_description_from'],0,3)));
                if(!$AirportFrom){
                    $AirportFrom = new \Airport();
                    $AirportFrom->setName($dados['airport_description_from']);
                    $AirportFrom->setCode($dados['airport_description_from']);
                    $em->persist($AirportFrom);
                    $em->flush($AirportFrom);
                }
                $onlineFlight->setAirportCodeFrom($AirportFrom->getCode());
                $onlineFlight->setAirportDescriptionFrom($AirportFrom->getName());
            } else {
                $onlineFlight->setAirportCodeFrom('');
                $onlineFlight->setAirportDescriptionFrom('');
                $AirportFrom = NULL;
            }

            if(isset($dados['airport_description_to'])) {
                $AirportTo = $em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_description_to'],0,3)));
                if(!$AirportTo){
                    $AirportTo = new \Airport();
                    $AirportTo->setName($dados['airport_description_to']);
                    $AirportTo->setCode($dados['airport_description_to']);
                    $em->persist($AirportTo);
                    $em->flush($AirportTo);
                }
                $onlineFlight->setAirportCodeTo($AirportTo->getCode());
                $onlineFlight->setAirportDescriptionTo($AirportTo->getName());
            } else {
                $onlineFlight->setAirportCodeTo('');
                $onlineFlight->setAirportDescriptionTo('');
                $AirportTo = NULL;
            }

            if(substr($dados['_boardingDate'], 0, 5) != 'NaN-N') {
                $onlineFlight->setBoardingDate(new \Datetime($dados['_boardingDate']));
            } else {
                $onlineFlight->setBoardingDate(new \Datetime());
            }

            if(substr($dados['_boardingDate'], 0, 5) != 'NaN-N') {
                $onlineFlight->setLandingDate(new \Datetime($dados['_landingDate']));
            } else {
                $onlineFlight->setLandingDate(new \Datetime());
            }

            if(isset($dados['flight_time'])) {
                $onlineFlight->setFlightTime($dados['flight_time']);
            } else {
                $onlineFlight->setFlightTime('');
            }
            if(isset($dados['flight'])) {
                $onlineFlight->setFlight($dados['flight']);
            } else {
                $onlineFlight->setFlight('');
            }
            $onlineFlight->setTax($dados['tax']);
            if(isset($dados['connection'])) {
                $onlineFlight->setConnection($dados['connection']);
            } else {
                $onlineFlight->setConnection('');
            }
            if(isset($dados['cost'])) {
                $onlineFlight->setCost($dados['cost']);
            } else {
                $onlineFlight->setCost(0);
            }
            if(isset($dados['miles_original'])) {
                $onlineFlight->setMilesUsed($dados['miles_original']);
            } else {
                $onlineFlight->setMilesUsed(0);
            }

            if(isset($dados['cost'])) {
                $onlineFlight->setCostPerAdult($dados['cost']);
            } else {
                $onlineFlight->setCost(0);
            }
            $onlineFlight->setCostPerChild(0);
            $onlineFlight->setCostPerNewborn(0);

            if(isset($dados['miles_original'])) {
                $onlineFlight->setMilesPerAdult($dados['miles_original']);
            } else {
                $onlineFlight->setMilesPerAdult(0);
            }
            $onlineFlight->setMilesPerChild(0);
            $onlineFlight->setMilesPerNewborn(0);
            $onlineFlight->setNumberOfAdult(0);
            $onlineFlight->setNumberOfChild(0);
            $onlineFlight->setNumberOfNewborn(0);
            $em->persist($onlineFlight);
            $em->flush($onlineFlight);

            $airlines = '';
            $totalMilesOrder = 0;
            $totalCostOrder = 0;
            $adults = 0;
            $childs = 0;
            $newborns = 0;
            $returned = false;
            foreach ($paxs as $pax) {

                if(isset($dados['miles_used'])) {
                    $totalMilesOrder += $dados['miles_used'];
                }
                $totalCostOrder += $dados['amount_paid'];
                if($airlines == '') {
                    $airlines = $dados['airline'];
                } else {
                    $airlines .= $dados['airline'];
                }

                $onlinePax = new \OnlinePax();
                $onlinePax->setOrder($onlineOrder);
                $onlinePax->setPaxName(trim(mb_strtoupper($pax['pax_name'], 'UTF-8')));
                $onlinePax->setIdentification($pax['identification']);
                if(isset($pax['_paxBirthdate'])) {
                    $onlinePax->setBirthdate(new \Datetime($pax['_paxBirthdate']));
                } else {
                    $onlinePax->setBirthdate(new \Datetime());
                }
                if(isset($pax['gender'])) {
                    $onlinePax->setGender($pax['gender']);
                }

                $onlinePax->setEmail('');
                $onlinePax->setPhoneNumber('');

                if(isset($pax['type'])) {
                    if($pax['type'] == 'INF') {
                        $age = 1;
                    } else if($pax['type'] == 'CHD') {
                        $age = 5;
                    } else {
                        $age = 18;
                    }

                } else if(isset($pax['_paxBirthdate'])) {
                    $birthDate = explode("/", (new \Datetime($pax['_paxBirthdate']))->format('m/d/Y'));
                    $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > $onlineFlight->getBoardingDate()->format('md')
                    ? (($onlineFlight->getBoardingDate()->format('Y') - $birthDate[2]) - 1)
                    : ($onlineFlight->getBoardingDate()->format('Y') - $birthDate[2]));
                    $age = 18;
                }

                if($age < 2) {
                    $onlinePax->setIsNewborn('S');
                    $onlinePax->setIsChild('N');

                    if(isset($dados['miles_original'])) {
                        if((float)$dados['miles_original'] > 0) {
                            if(isset($dados['miles_original'])) {
                                $onlineFlight->setMilesPerNewborn($dados['miles_original']);
                            }
                            if(isset($dados['cost'])) {
                                $onlineFlight->setCostPerNewborn($dados['cost']);
                            }
                        } else {
                            if(isset($dados['miles_used'])) {
                                $onlineFlight->setMilesPerNewborn($dados['miles_used']);
                            }
                            if(isset($dados['cost'])) {
                                $onlineFlight->setCostPerNewborn($dados['cost']);
                            }
                        }
                    }
                    $newborns++;
                } else if($age < 12) {
                    $onlinePax->setIsNewborn('N');
                    $onlinePax->setIsChild('S');

                    if(isset($dados['miles_original'])) {
                        if((float)$dados['miles_original'] > 0) {
                            if(isset($dados['miles_original'])) {
                                $onlineFlight->setMilesPerChild($dados['miles_original']);
                            }
                            if(isset($dados['cost'])) {
                                $onlineFlight->setCostPerChild($dados['cost']);
                            }
                        } else {
                            if(isset($dados['miles_used'])) {
                                $onlineFlight->setMilesPerChild($dados['miles_used']);
                            }
                            if(isset($dados['cost'])) {
                                $onlineFlight->setCostPerChild($dados['cost']);
                            }
                        }
                    }
                    $childs++;
                } else {
                    $onlinePax->setIsNewborn('N');
                    $onlinePax->setIsChild('N');
                    $adults++;
                }

                $em->persist($onlinePax);
                $em->flush($onlinePax);

                $Sale = new \Sale();
                $Sale->setOnlinePax($onlinePax);
                $Sale->setOnlineFlightId($onlineFlight->getId());
                $Sale->setExternalId($onlineOrder->getId());
                if (isset($pax['cards_id'])){
                    $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $pax['cards_id']));
                    $sale_cards = $Cards;
                }
                
                $divers = $request->getRow();
                if (isset($divers['noCard'])) {
                    $noCard = $divers['noCard'];
                }
                if (isset($divers['repricing'])) {
                    $repricing = $divers['repricing'];
                }
                if (isset($divers['refound'])) {
                    $refound = $divers['refound'];
                }
                if (isset($divers['partner'])) {
                    $partner = $divers['partner'];
                }
                if (isset($divers['safe'])) {
                    $safe = $divers['safe'];
                }
                if (isset($divers['hotel'])) {
                    $hotel = $divers['hotel'];
                }
                if (isset($divers['baggage'])) {
                    $baggage = $divers['baggage'];
                }
                if (isset($divers['divers'])) {
                    $divers = $divers['divers'];
                }

                if(isset($pax['ticket_code']) && $pax['ticket_code'] != '') {
                    $Sale->setTicketCode($pax['ticket_code']);
                }
                if(($divers == "true") || ($noCard == "true") || ($partner == "true") || ($safe == "true") || ($repricing == "true") || ($baggage == "true") || ($hotel == "true")) {
                    
                    if($divers == "true"){
                        if (isset($pax['cards_id'])){
                            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $pax['cards_id']));
                            $sale_cards = $Cards;
                        }

                        $cost = 0;
                        if (isset($dados['costPerThousand'])){
                            $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $sale_cards));

                            $sql = "select p FROM Purchase p WHERE p.cards = '".$sale_cards->getId()."' and p.status='M'";
                            $query = $em->createQuery($sql);
                            $Purchase = $query->getResult();

                            $left = $pax['miles_used'];

                            foreach ($Purchase as $item) {
                                if($left > 0) {

                                    if($left > $item->getLeftover())
                                    {
                                        $left -= $item->getLeftover();
                                        $cost += ($item->getLeftover() / 1000) * $item->getCostPerThousandPurchase();
                                    }else{
                                        $cost += ($left / 1000) * $item->getCostPerThousandPurchase();
                                        $left = 0;
                                    }
                                }
                            }

                            // $cost = $MilesBench->getCostPerThousandPurchase() * ($pax['miles_used'] / 1000);
                            $amount_paid = $dados['costPerThousand'] * ($pax['miles_used'] / 1000);
                        }
                        
                        $Sale->setCards($sale_cards);
                        $Sale->setMilesUsed($pax['miles_used']);
                        if(isset($dados['miles_original'])) {
                            $Sale->setMilesOriginal($dados['miles_original']);
                        } else {
                            $Sale->setMilesOriginal($pax['miles_original']);
                        }
                        $Sale->setTax(0);
                        $Sale->setAmountPaid($amount_paid);
                        $Sale->setTotalCost($cost);
                        $Sale->setKickback($amount_paid - $cost);
                        $Sale->setIssueDate(new \Datetime());
                        $Sale->setBoardingDate(new \Datetime());
                        $Sale->setLandingDate(new \Datetime());
                        if (isset($order['comments'])) {
                            $Sale->setDescription($order['comments']);
                        }
                        if (isset($pax['du_tax'])) {
                            $Sale->setDuTax($pax['du_tax']);
                        }
                        if (isset($pax['flight_locator'])) {
                            $Sale->setFlightLocator($pax['flight_locator']);
                        }
                        if (isset($order['issuing'])) {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($order['issuing']);
                                $BusinessPartner->setPartnerType('S');
                                $BusinessPartner->setClient($Client);
                                
                                $em->persist($BusinessPartner);
                                $em->flush($BusinessPartner);
                            }
                            $Sale->setIssuing($BusinessPartner);
                        }

                        $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' => "PADRAO")));
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => "PADRAO")));
                        
                        if(!$client){
                            $client = new \Businesspartner();
                            $client->setName(mb_strtoupper($order['client_name']));
                            $client->setPartnerType('C');
                        
                            $em->persist($client);
                            $em->flush($client);
                        }

                        $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $sale_cards));
                        $MilesBench->setLeftover($MilesBench->getLeftover() - $miles_used);
                        $MilesBench->setLastchange(new \Datetime());

                        $sql = "select p FROM Purchase p WHERE p.cards = '".$sale_cards->getId()."' and p.status='M'";
                        $query = $em->createQuery($sql);
                        $Purchase = $query->getResult();

                        $left = $pax['miles_used'];
                        $purchaseSet = false;
                        foreach ($Purchase as $item) {
                            if($left > $item->getLeftover())    
                            {
                                $left -= $item->getLeftover();
                                $item->setLeftover(0);
                                if(!$purchaseSet) {
                                        $purchaseSet = true;
                                        $Sale->setPurchase($item);
                                    }
                            }else{
                                $item->setLeftover($item->getLeftover() - $left);
                                $left = 0;
                                if(!$purchaseSet) {
                                    $purchaseSet = true;
                                    $Sale->setPurchase($item);
                                }
                            }
                            $em->persist($item);
                        }

                        if($MilesBench->getLeftover() < 0)
                            throw new Exception("Saldo insuficiente para o cartão: ".$sale_cards->getCardNumber()." !");

                        $Sale->setPax($MilesBench->getCards()->getBusinesspartner());

                        $em->persist($MilesBench);
                        $em->flush($MilesBench);

                        $em->flush($Purchase);

                        $Sale->setClient($client);
                        $Sale->setStatus($dados['status']);
                        $Sale->setFlight($order['comments']);
                        $Sale->setFlightHour($order['comments']);

                        $Sale->setUser($UserPartner);
                        
                        $em->persist($Sale);
                        $em->flush($Sale);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                    } elseif ($hotel == "true") {

                        $amount_paid = $dados['amount_paid'];
                        if(isset($pax['du_tax']) && $pax['du_tax'] != '') {
                            $cost = $pax['du_tax'];
                        } else $cost = 0;

                        $Sale->setMilesUsed(0);
                        $Sale->setMilesOriginal(0);
                        $Sale->setTax(0);
                        $Sale->setAmountPaid($amount_paid);
                        $Sale->setTotalCost($cost);
                        $Sale->setKickback($amount_paid - $cost);
                        $Sale->setIssueDate(new \Datetime());
                        $Sale->setBoardingDate(new \Datetime($dados['_boardingDate']));
                        $Sale->setLandingDate(new \Datetime($dados['_boardingDate']));
                        if (isset($order['comments'])) {
                            $Sale->setDescription($order['comments']);
                        }
                        if (isset($dados['du_tax'])) {
                            $Sale->setDuTax($dados['du_tax']);
                        }
                        if (isset($pax['flight_locator'])) {
                            $Sale->setFlightLocator($pax['flight_locator']);
                        }
                        if (isset($order['issuing']) && $order['issuing'] != '') {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($order['issuing']);
                                $BusinessPartner->setPartnerType('S');
                                $BusinessPartner->setClient($Client);
                                
                                $em->persist($BusinessPartner);
                                $em->flush($BusinessPartner);
                            }
                            $Sale->setIssuing($BusinessPartner);
                        }

                        if (isset($dados['partner']) && $dados['partner'] != '') {
                            $partner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partner']));
                            if (!$partner) {
                                $partner = new \Businesspartner();
                                $partner->setName($dados['partner']);
                                $partner->setPartnerType('P');
                                $partner->setClient($Client);
                                
                                $em->persist($partner);
                                $em->flush($partner);
                            }
                            $Sale->setSaleByThird('Y');
                            $Sale->setProviderSaleByThird($partner);
                        }
                        
                        if(!$client){
                            $client = new \Businesspartner();
                            $client->setName(mb_strtoupper($order['client_name']));
                            $client->setPartnerType('C');
                        
                            $em->persist($client);
                            $em->flush($client);
                        }

                        $pax_flight = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pax['pax_name']));
                        if(!$pax_flight){
                            $pax_flight = new \Businesspartner();
                            $pax_flight->setName($pax['pax_name']);
                            $pax_flight->setPartnerType('X');
                        
                            $em->persist($pax_flight);
                            $em->flush($pax_flight);
                        }

                        $Sale->setPax($pax_flight);

                        $Sale->setClient($client);
                        $Sale->setStatus($dados['status']);
                        $Sale->setFlight($dados['flight']);
                        $Sale->setFlightHour($dados['flight_time']);

                        $Sale->setUser($UserPartner);
                        
                        $em->persist($Sale);
                        $em->flush($Sale);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                    } elseif ($repricing == "true"){

                        if (isset($pax['card_number'])){
                            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $pax['cards_id']));
                            $sale_cards = $Cards;
                        }

                        $cost = 0;
                        if (isset($dados['costPerThousand'])) {
                            $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $sale_cards));

                            $sql = "select p FROM Purchase p WHERE p.cards = '".$sale_cards->getId()."' and p.status='M'";
                            $query = $em->createQuery($sql);
                            $Purchase = $query->getResult();

                            $left = $pax['miles_used'];
                            foreach ($Purchase as $item) {
                                if($left > 0) {

                                    if($left > $item->getLeftover())
                                    {
                                        $left -= $item->getLeftover();
                                        $cost += ($item->getLeftover() / 1000) * $item->getCostPerThousandPurchase();
                                    }else{
                                        $cost += ($left / 1000) * $item->getCostPerThousandPurchase();
                                        $left = 0;
                                    }
                                }
                            }

                            // $cost = $MilesBench->getCostPerThousandPurchase() * ($pax['miles_used'] / 1000);
                            $amount_paid = $dados['costPerThousand'] * ($pax['miles_used'] / 1000) +  $dados['repricing_cost'];
                        } else {
                            $cost = 0;
                            $amount_paid = $dados['repricing_cost'];
                            if(isset($dados['du_tax']) && $dados['du_tax'] != '') {
                                $cost = $amount_paid - $dados['du_tax'];
                            }
                        }
                        
                        if(isset($sale_cards)) {
                            $Sale->setCards($sale_cards);
                        }
                        if(isset($pax['miles_used']) && $pax['miles_used'] != '') {
                            $Sale->setMilesUsed($pax['miles_used']);
                        } else {
                            $Sale->setMilesUsed(0);
                        }
                        if(isset($pax['miles_used']) && $pax['miles_used'] != '') {
                            $Sale->setMilesOriginal($pax['miles_used']);
                        } else {
                            $Sale->setMilesOriginal(0);
                        }
                        $Sale->setTax(0);
                        $Sale->setAmountPaid($amount_paid);
                        $Sale->setTotalCost($cost);
                        $Sale->setKickback($amount_paid - $cost);
                        $Sale->setIssueDate(new \Datetime());
                        $Sale->setBoardingDate(new \Datetime());
                        $Sale->setLandingDate(new \Datetime());
                        if (isset($order['comments'])) {
                            $Sale->setDescription($order['comments']);
                        }
                        if (isset($dados['du_tax'])) {
                            $Sale->setDuTax($dados['du_tax']);
                        }
                        if (isset($pax['flight_locator'])) {
                            $Sale->setFlightLocator($pax['flight_locator']);
                        }
                        if (isset($order['issuing'])) {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($order['issuing']);
                                $BusinessPartner->setPartnerType('S');
                                $BusinessPartner->setClient($Client);
                                
                                $em->persist($BusinessPartner);
                                $em->flush($BusinessPartner);
                            }
                            $Sale->setIssuing($BusinessPartner);
                        }

                        $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_from'],0,3))));
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_to'],0,3))));
                        
                        if(!$client){
                            $client = new \Businesspartner();
                            $client->setName(mb_strtoupper($order['client_name']));
                            $client->setPartnerType('C');
                        
                            $em->persist($client);
                            $em->flush($client);
                        }

                        if(isset($sale_cards)) {
                            $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $sale_cards));
                            $MilesBench->setLeftover($MilesBench->getLeftover() - $pax['miles_used']);
                            $MilesBench->setLastchange(new \Datetime());

                            $sql = "select p FROM Purchase p WHERE p.cards = '".$sale_cards->getId()."' and p.status='M'";
                            $query = $em->createQuery($sql);
                            $Purchase = $query->getResult();

                            $left = $pax['miles_used'];
                            $purchaseSet = false;

                            foreach ($Purchase as $item) {
                                if($left > $item->getLeftover())    
                                {
                                    $left -= $item->getLeftover();
                                    $item->setLeftover(0);
                                    if(!$purchaseSet) {
                                        $purchaseSet = true;
                                        $Sale->setPurchase($item);
                                    }
                                }else{
                                    $item->setLeftover($item->getLeftover() - $left);
                                    $left = 0;
                                    if(!$purchaseSet) {
                                        $purchaseSet = true;
                                        $Sale->setPurchase($item);
                                    }
                                }
                                $em->persist($item);
                            }

                            if($MilesBench->getLeftover() < 0)
                                throw new Exception("Saldo insuficiente para o cartão: ".$sale_cards->getCardNumber()." !");

                            $em->persist($MilesBench);
                            $em->flush($MilesBench);

                            $em->flush($Purchase);
                        }

                        $pax_flight = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pax['pax_name']));
                        if(!$pax_flight){
                            $pax_flight = new \Businesspartner();
                            $pax_flight->setName($pax['pax_name']);
                            $pax_flight->setPartnerType('X');
                        
                            $em->persist($pax_flight);
                            $em->flush($pax_flight);
                        }

                        $Sale->setPax($pax_flight);

                        $Sale->setClient($client);
                        $Sale->setStatus('Emitido');
                        if(isset($order['comments']) && $order['comments'] != '') {
                            $Sale->setFlight($order['comments']);
                        }
                        $Sale->setFlightHour($dados['flight_time']);

                        $hash = $request->getRow();
                        if(isset($hash['hashId'])){
                            $hash = $hash['hashId'];
                        }

                        $Sale->setUser($UserPartner);
                        
                        $em->persist($Sale);
                        $em->flush($Sale);

                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Remarcação - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$dados['flight_locator']);
                        if(isset($dados['costPerThousand']) && $dados['costPerThousand'] != ''){
                            $value = ($dados['milesDiference'] * ($dados['costPerThousand'] / 1000)) + $dados['repricing_cost'];
                        } else {
                            $value = $dados['repricing_cost'];
                        }
                        $Billsreceive->setOriginalValue($value);
                        $Billsreceive->setActualValue($value);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Remarcação');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate(new \DateTime());
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                    } elseif ($baggage == "true"){

                        $Sale->setMilesUsed(0);
                        $Sale->setMilesOriginal(0);
                        $Sale->setTax(0);
                        $Sale->setAmountPaid($dados['amount_paid']);
                        $Sale->setTotalCost(0);
                        $Sale->setKickback(0);
                        $Sale->setIssueDate(new \Datetime());
                        $Sale->setBoardingDate(new \Datetime());
                        $Sale->setLandingDate(new \Datetime());
                        if (isset($order['comments'])) {
                            $Sale->setDescription($order['comments']);
                        }

                        if(isset($dados['baggage']) && $dados['baggage'] != '') {
                            $Sale->setBaggage($dados['baggage']);
                        }
                        if(isset($dados['special_seat']) && $dados['special_seat'] != '') {
                            $Sale->setSpecialSeat($dados['special_seat']);
                        }

                        if (isset($order['issuing'])) {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($order['issuing']);
                                $BusinessPartner->setPartnerType('S');
                                $BusinessPartner->setClient($Client);
                                
                                $em->persist($BusinessPartner);
                                $em->flush($BusinessPartner);
                            }
                            $Sale->setIssuing($BusinessPartner);
                        }

                        if (isset($pax['flight_locator'])) {
                            $Sale->setFlightLocator($pax['flight_locator']);
                        }

                        $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_description_from'],0,3))));
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_description_to'],0,3))));

                        $client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));
                        if(!$client){
                            $client = new \Businesspartner();
                            $client->setName(mb_strtoupper($order['client_name']));
                            $client->setPartnerType('C');
                        
                            $em->persist($client);
                            $em->flush($client);
                        }

                        $pax_flight = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pax['pax_name']));
                        if(!$pax_flight){
                            $pax_flight = new \Businesspartner();
                            $pax_flight->setName($pax['pax_name']);
                            $pax_flight->setPartnerType('X');
                        
                            $em->persist($pax_flight);
                            $em->flush($pax_flight);
                        }

                        $Sale->setPax($pax_flight);
                        $Sale->setIsExtra('true');

                        $Sale->setClient($client);
                        $Sale->setStatus('Emitido');
                        $Sale->setFlight($dados['flight']);
                        $Sale->setFlightHour('0');

                        $Sale->setUser($UserPartner);
                        
                        $em->persist($Sale);
                        $em->flush($Sale);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                    } elseif ($safe == "true"){

                        $amount_paid = $dados['amount_paid'];
                        $cost = $dados['amount_paid'] - $dados['safe_commission'];
                        $Sale->setSaleType($dados['safeType']);

                        $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partner']));
                        if(!$provider){
                            $provider = new \Businesspartner();
                            $provider->setName($dados['partner']);
                            $provider->setPartnerType('P');
                            
                            $em->persist($provider);
                            $em->flush($provider);
                        }
                        $Sale->setSaleByThird('Y');
                        $Sale->setProviderSaleByThird($provider);

                        $Sale->setMilesUsed(0);
                        $Sale->setMilesOriginal(0);
                        $Sale->setTax(0);
                        $Sale->setAmountPaid($amount_paid);
                        $Sale->setTotalCost($cost);
                        $Sale->setKickback($amount_paid - $cost);
                        $Sale->setIssueDate(new \Datetime());
                        $Sale->setBoardingDate(new \Datetime($dados['_boardingDate']));
                        $Sale->setLandingDate(new \Datetime($dados['_landingDate']));
                        if (isset($order['comments'])) {
                            $Sale->setDescription($order['comments']);
                        }
                        if (isset($order['issuing'])) {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($order['issuing']);
                                $BusinessPartner->setPartnerType('S');
                                $BusinessPartner->setClient($Client);
                                
                                $em->persist($BusinessPartner);
                                $em->flush($BusinessPartner);
                            }
                            $Sale->setIssuing($BusinessPartner);
                        }
                        if (isset($pax['flight_locator'])) {
                            $Sale->setFlightLocator($pax['flight_locator']);
                        }
                        $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_from'],0,3))));
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_to'],0,3))));
                        
                        $client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));
                        if(!$client){
                            $client = new \Businesspartner();
                            $client->setName(mb_strtoupper($order['client_name']));
                            $client->setPartnerType('C');
                        
                            $em->persist($client);
                            $em->flush($client);
                        }

                        $pax_flight = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pax['pax_name']));
                        if(!$pax_flight){
                            $pax_flight = new \Businesspartner();
                            $pax_flight->setName($pax['pax_name']);
                            $pax_flight->setPartnerType('X');
                        
                            $em->persist($pax_flight);
                            $em->flush($pax_flight);
                        }

                        $Sale->setPax($pax_flight);

                        $Sale->setClient($client);
                        $Sale->setStatus($dados['status']);
                        $Sale->setFlight($dados['flight']);
                        $Sale->setFlightHour($dados['flight_time']);
                        
                        $Sale->setUser($UserPartner);
                        
                        $em->persist($Sale);
                        $em->flush($Sale);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                    } elseif ($noCard == "true"){

                        $amount_paid = (float)$dados['tax'] + (float)$dados['cost'] + (float)$dados['commission'];
                        $cost = (float)$dados['tax'] + (float)$dados['cost'];

                        $Sale->setMilesUsed(0);
                        $Sale->setMilesOriginal(0);
                        $Sale->setTax($dados['tax']);
                        $Sale->setAmountPaid($amount_paid);
                        $Sale->setTotalCost($cost);
                        $Sale->setKickback($amount_paid - $cost);
                        $Sale->setIssueDate(new \Datetime());
                        $Sale->setBoardingDate(new \Datetime($dados['_boardingDate']));
                        $Sale->setLandingDate(new \Datetime($dados['_landingDate']));
                        if (isset($order['comments'])) {
                            $Sale->setDescription($order['comments']);
                        }
                        if (isset($order['issuing'])) {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($order['issuing']);
                                $BusinessPartner->setPartnerType('S');
                                $BusinessPartner->setClient($Client);
                                
                                $em->persist($BusinessPartner);
                                $em->flush($BusinessPartner);
                            }
                            $Sale->setIssuing($BusinessPartner);
                        }
                        if (isset($dados['du_tax'])) {
                            $Sale->setDuTax($dados['du_tax']);
                        }
                        if (isset($pax['flight_locator'])) {
                            $Sale->setFlightLocator($pax['flight_locator']);
                        }
                        $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_from'],0,3))));
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_to'],0,3))));
                        
                        $client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));
                        if(!$client){
                            $client = new \Businesspartner();
                            $client->setName(mb_strtoupper($order['client_name']));
                            $client->setPartnerType('C');
                        
                            $em->persist($client);
                            $em->flush($client);
                        }

                        $pax_flight = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pax['pax_name']));
                        if(!$pax_flight){
                            $pax_flight = new \Businesspartner();
                            $pax_flight->setName($pax['pax_name']);
                            $pax_flight->setPartnerType('X');
                        
                            $em->persist($pax_flight);
                            $em->flush($pax_flight);
                        }

                        $Sale->setPax($pax_flight);

                        $Sale->setClient($client);
                        $Sale->setStatus($dados['status']);
                        $Sale->setFlight($dados['flight']);
                        $Sale->setFlightHour($dados['flight_time']);
                        
                        $Sale->setUser($UserPartner);
                        
                        $em->persist($Sale);
                        $em->flush($Sale);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);
                    } else{
                        
                        if ((isset($pax['pax_name'])) && ($pax['pax_name'] != '')){
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pax['pax_name']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($pax['pax_name']);
                                if (isset($pax['identification'])){
                                    $BusinessPartner->setRegistrationCode($pax['identification']);
                                }
                                $BusinessPartner->setPartnerType('X');
                            } else {
                                if (strpos($BusinessPartner->getPartnerType(),'X')) {
                                    $BusinessPartner->setPartnerType($BusinessPartner->getPartnerType()+'_X');
                                }
                            }
                            if ((isset($pax['_paxBirthdate'])) && ($pax['_paxBirthdate'] != '')) {
                                $BusinessPartner->setBirthdate(new \Datetime($pax['_paxBirthdate']));
                            }
                            $em->persist($BusinessPartner);
                            $em->flush($BusinessPartner);
                            $sale_pax = $BusinessPartner;
                        }

                        if($dados['partner'] == "JACK FOR"){
                            if(isset($dados['du_tax'])){
                                $total_cost = $dados['du_tax'] + $dados['tax'] + (32 * ($pax['miles_used'] / 1000));
                            } else {
                                $total_cost = $dados['tax'] + (32 * ($pax['miles_used'] / 1000));
                            }
                        }else{
                            if(isset($dados['du_tax'])){
                                $total_cost = $dados['du_tax'] + $dados['tax'];
                            } else {
                                $total_cost = $dados['tax'];
                            }
                        }

                        $Sale->setPax($sale_pax);
                        $Sale->setMilesUsed($pax['miles_used']);
                        $Sale->setMilesOriginal($pax['miles_original']);
                        $Sale->setTax($dados['tax']);
                        $Sale->setAmountPaid($dados['cost']);
                        $Sale->setTotalCost($total_cost);
                        $Sale->setKickback($dados['kickback']);
                        $Sale->setIssueDate(new \Datetime());
                        $Sale->setBoardingDate(new \Datetime($dados['_boardingDate']));
                        $Sale->setLandingDate(new \Datetime($dados['_landingDate']));
                        if (isset($order['description'])) {
                            $Sale->setDescription($dados['description']);
                        }
                        $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                        $Sale->setAirportFrom($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_from'],0,3))));
                        $Sale->setAirportTo($em->getRepository('Airport')->findOneBy(array('code' => substr($dados['airport_code_to'],0,3))));
                        
                        $client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));
                        if(!$client){
                            $client = new \Businesspartner();
                            $client->setName(mb_strtoupper($order['client_name']));
                            $client->setPartnerType('C');
                        
                            $em->persist($client);
                            $em->flush($client);
                        }
                        if (isset($dados['du_tax'])) {
                            $Sale->setDuTax($dados['du_tax']);
                        }
                        if (isset($pax['flight_locator'])) {
                            $Sale->setFlightLocator($pax['flight_locator']);
                        }
                        
                        $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partner']));
                        $Sale->setSaleByThird('Y');
                        $Sale->setProviderSaleByThird($provider);
                        
                        if (isset($order['issuing'])) {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                            if (!$BusinessPartner) {
                                $BusinessPartner = new \Businesspartner();
                                $BusinessPartner->setName($order['issuing']);
                                $BusinessPartner->setPartnerType('S');
                                $BusinessPartner->setClient($Client);
                                
                                $em->persist($BusinessPartner);
                                $em->flush($BusinessPartner);
                            }
                            $Sale->setIssuing($BusinessPartner);
                        }

                        $Sale->setClient($client);
                        $Sale->setStatus($dados['status']);
                        $Sale->setFlight($dados['flight']);
                        $Sale->setFlightHour($dados['flight_time']);

                        $Sale->setUser($UserPartner);

                        $em->persist($Sale);
                        $em->flush($Sale);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);
                    }

                } else {

                    if ((isset($pax['pax_name'])) && ($pax['pax_name'] != '')){
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pax['pax_name']));
                        if (!$BusinessPartner) {
                            $BusinessPartner = new \Businesspartner();
                            $BusinessPartner->setName($pax['pax_name']);
                            if (isset($pax['identification'])){
                                $BusinessPartner->setRegistrationCode($pax['identification']);
                            }
                            $BusinessPartner->setPartnerType('X');
                        } else {
                            if (strpos($BusinessPartner->getPartnerType(),'X')) {
                                $BusinessPartner->setPartnerType($BusinessPartner->getPartnerType()+'_X');
                            }
                        }
                        if ((isset($pax['_paxBirthdate'])) && ($pax['_paxBirthdate'] != '')) {
                            $BusinessPartner->setBirthdate(new \Datetime($pax['_paxBirthdate']));
                        }
                        $em->persist($BusinessPartner);
                        $em->flush($BusinessPartner);
                        $sale_pax = $BusinessPartner;
                    }
                    if (isset($pax['card_number'])){
                        $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $pax['cards_id']));
                        $sale_cards = $Cards;
                    }
                    $Sale->setPax($sale_pax);

                    if(isset($sale_cards)){
                        $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $sale_cards));
                        $Sale->setCards($sale_cards);
                    }

                    if(isset($pax['miles_used'])){
                        $Sale->setMilesUsed($pax['miles_used']);
                    } else {
                        $Sale->setMilesUsed($dados['miles_original']);
                    }

                    if(isset($pax['miles_original'])) {
                        $Sale->setMilesOriginal($pax['miles_original']);
                    } else {
                        if(isset($pax['miles_used'])) {
                            $Sale->setMilesOriginal($pax['miles_used']);
                        } else {
                            $Sale->setMilesOriginal(0);
                        }
                    }

                    if(isset($dados['tax']) && $dados['tax'] != ''){
                        $Sale->setTax($dados['tax']);
                        $Sale->setTaxBillet($dados['tax']);
                    }
                    if(isset($dados['amount_paid']) && $dados['amount_paid'] != ''){
                        $Sale->setAmountPaid($dados['amount_paid']);
                    }

                    $cost = 0;
                    if(isset($MilesBench)) {

                        $sql = "select p FROM Purchase p WHERE p.cards = '".$sale_cards->getId()."' and p.status='M'";
                        $query = $em->createQuery($sql);
                        $Purchase = $query->getResult();

                        $left = $pax['miles_used'];
                        foreach ($Purchase as $item) {
                            if($left > 0) {

                                if($left > $item->getLeftover())
                                {
                                    $left -= $item->getLeftover();
                                    $cost += ($item->getLeftover() / 1000) * $item->getCostPerThousandPurchase();
                                }else{
                                    $cost += ($left / 1000) * $item->getCostPerThousandPurchase();
                                    $left = 0;
                                }
                            }
                        }
                        // $cost = $MilesBench->getCostPerThousandPurchase() * ($pax['miles_used'] / 1000);
                    }

                    if(isset($dados['partner']) && $dados['partner'] != ''){
                        $Sale->setSaleByThird('Y');
                        $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partner']));
                        if(!$provider){
                            $provider = new \Businesspartner();
                            $provider->setName($dados['partner']);
                            $provider->setPartnerType('P');
                            
                            $em->persist($provider);
                            $em->flush($provider);
                        }
                        $Sale->setProviderSaleByThird($provider);
                        if($dados['partner'] == "JACK FOR"){
                            if(isset($dados['du_tax'])){
                                if($dados['airline'] == 'AVIANCA') {
                                    $total_cost = $dados['du_tax'] + $dados['tax'] + (40 * ($dados['miles_used'] / 1000));
                                } else if($dados['airline'] == 'LATAM') {
                                    $total_cost = $dados['du_tax'] + $dados['tax'] + (32 * ($dados['miles_used'] / 1000));
                                }
                            } else {
                                if($dados['airline'] == 'AVIANCA') {
                                    $total_cost = $dados['tax'] + (40 * ($dados['miles_used'] / 1000));
                                } else if($dados['airline'] == 'LATAM') {
                                    $total_cost = $dados['tax'] + (32 * ($dados['miles_used'] / 1000));
                                }
                            }
                        }else if($dados['partner'] == "Rextur Advance"){
                            $cost = $dados['value'] + $dados['tax'];
                            $dados['du_tax'] = $dados['amount_paid'] - ($dados['value'] + $dados['tax']);
                        } else {
                            if(isset($dados['du_tax'])){
                                $cost = $cost + $dados['du_tax'] + $dados['tax'];
                            } else {
                                $cost = $cost + $dados['tax'];
                            }
                        }
                    }

                    $Sale->setTotalCost($cost);
                    $Sale->setKickback($dados['kickback']);
                    $Sale->setIssueDate(new \Datetime());
                    $Sale->setBoardingDate(new \Datetime($dados['_boardingDate']));
                    $Sale->setLandingDate(new \Datetime($dados['_landingDate']));

                    if(isset($dados['safeType']) && $dados['safeType'] != ''){
                        $Sale->setSaleType($dados['safeType']);
                    }

                    if (isset($order['description'])) {
                        $Sale->setDescription($dados['description']);
                    }
                    $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                    $Sale->setAirportFrom($AirportFrom);
                    $Sale->setAirportTo($AirportTo);

                    $client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));
                    if(!$client){
                        $client = new \Businesspartner();
                        $client->setName(mb_strtoupper($order['client_name']));
                        $client->setPartnerType('C');
                    
                        $em->persist($client);
                        $em->flush($client);
                    }
                    if (isset($dados['du_tax'])) {
                        $Sale->setDuTax($dados['du_tax']);
                    }
                    if (isset($pax['flight_locator'])) {
                        $Sale->setFlightLocator($pax['flight_locator']);
                    }
                    if (isset($order['issuing'])) {
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                        if (!$BusinessPartner) {
                            $BusinessPartner = new \Businesspartner();
                            $BusinessPartner->setName($order['issuing']);
                            $BusinessPartner->setPartnerType('S');
                            $BusinessPartner->setClient($Client);
                            
                            $em->persist($BusinessPartner);
                            $em->flush($BusinessPartner);
                        }
                        $Sale->setIssuing($BusinessPartner);
                    }

                    $Sale->setClient($client);
                    $Sale->setStatus($dados['status']);

                    $em->persist($Sale);
                    $em->flush($Sale);
                    if($refound != "true"){
                        if(isset($pax['card_number'])) {

                            $removedMiles = Miles::removeMiles($em, $sale_cards->getId(), $pax['miles_used'], $Sale->getId());

                        }
                    }
                    
                    if(isset($dados['flight']) && $dados['flight'] != '')
                        $Sale->setFlight($dados['flight']);
                    
                    if(isset($dados['flight_time']) && $dados['flight_time'] != '')
                        $Sale->setFlightHour($dados['flight_time']);
                    
                    if(isset($dados['tax_card']) && $dados['tax_card'] != '' && $dados['tax_card'] != 'OUTRO'){
                        $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $dados['tax_card']));
                        if($InternalCards){
                            $Sale->setCardTax($InternalCards);
                            $sale_cards->setCardTax($InternalCards);

                            $em->persist($sale_cards);
                            $em->flush($sale_cards);
                        }
                    }

                    $Sale->setUser($UserPartner);

                    if($refound == "true"){

                        $Sale->setStatus($dados['refound']);
                        $em->persist($Sale);

                        if($dados['refound'] == "Reembolso Solicitado"){

                            if(isset($dados['value'])){
                                $Billsreceive = new \Billsreceive();
                                $Billsreceive->setStatus('A');
                                $Billsreceive->setClient($Sale->getClient());
                                $Billsreceive->setDescription('Reembolso - Passageiro '.$Sale->getPax()->getName().' - Localizador '.$pax['flight_locator']);
                                $Billsreceive->setOriginalValue($dados['value']);
                                $Billsreceive->setActualValue($dados['value']);
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

                            if (isset($dados['valueRefund'])) {
                                $Billspay = new \Billspay();
                                $Billspay->setStatus('A');
                                $Billspay->setDescription('Passageiro '.$Sale->getPax()->getName().' - Localizador '.$pax['flight_locator']);
                                $Billspay->setOriginalValue($dados['valueRefund']);
                                $Billspay->setActualValue($dados['valueRefund']);
                                $Billspay->setProvider($Sale->getPartner());
                                $Billspay->setTax(0);
                                $Billspay->setDiscount(0);
                                $Billspay->setAccountType('Reembolso');
                                $Billspay->setPaymentType('Reembolso');
                                $Billspay->setDueDate($Sale->getIssueDate());
                                $em->persist($Billspay);
                                $em->flush($Billspay);

                                $SaleBillspay = new \SaleBillspay();
                                $SaleBillspay->setBillspay($Billspay);
                                $SaleBillspay->setSale($Sale);
                                $em->persist($SaleBillspay);
                                $em->flush($SaleBillspay);
                            }

                            $Sale->setStatus('Reembolso Solicitado');
                            $Sale->setRefundDate(new \DateTime());
                            $em->persist($Sale);
                            $em->flush($Sale);

                            $SaleLog = new \SaleLog();
                            $SaleLog->setIssueDate(new \Datetime());
                            $SaleLog->setDescription("Reembolso Solicitado - Usuario:".$BusinessPartner->getName()." ");
                            $SaleLog->setSale($Sale);

                            $em->persist($SaleLog);
                            $em->flush($SaleLog);

                        }else {

                            $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $sale_cards));
                            $MilesBench->setLeftOver($MilesBench->getLeftOver() + $pax['miles_used']);
                            $MilesBench->setLastchange(new \Datetime());
                            $em->persist($MilesBench);
                            $em->flush($MilesBench);

                            $sql = "select p FROM Purchase p WHERE p.cards = '".$sale_cards->getId()."' and p.status='M' order by p.id desc";
                            $query = $em->createQuery($sql);
                            $Purchase = $query->getResult();

                            $lastPurchase = $Purchase[0];
                            $Purchase = $em->getRepository('Purchase')->findOneBy(array('id' => $lastPurchase->getId()));
                            $Purchase->setLeftOver($Purchase->getLeftOver() + $pax['miles_used']);
                            $Sale->setPurchase($Purchase);
                            $Sale->setRefundDate(new \DateTime());
                            $em->persist($Purchase);
                            $em->flush($Purchase);

                        }

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Reembolso Solicitado - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                        $em->flush($Sale);

                    } else {

                        $em->persist($Sale);
                        $em->flush($Sale);

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                        $SystemLog->setLogType('SALE');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);
                    }
                }

                $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('id' => $Sale->getCardTax()));

                $daysToPay = new \Datetime();
                if($refound != "true"){

                    if(isset($dados['safeType']) && $dados['safeType'] == "Cartao") {
                        $Provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partner']));

                        $Billspay = new \Billspay();
                        $Billspay->setStatus('A');
                        $Billspay->setDescription('Passageiro '.$pax['pax_name'].' - Localizador '.$pax['flightLocator']);
                        $Billspay->setOriginalValue(($Sale->getAmountPaid() - $Sale->getTotalCost()) * -1);
                        $Billspay->setActualValue(($Sale->getAmountPaid() - $Sale->getTotalCost()) * -1);
                        $Billspay->setTax(0);
                        $Billspay->setDiscount(0);
                        $Billspay->setAccountType('Cartao');
                        $Billspay->setPaymentType('Cartão de Crédito');
                        $Billspay->setDueDate($daysToPay);
                        $Billspay->setProvider($Provider);

                        $em->persist($Billspay);
                        $em->flush($Billspay);

                        $SaleBillspay = new \SaleBillspay();
                        $SaleBillspay->setBillspay($Billspay);
                        $SaleBillspay->setSale($Sale);
                        $em->persist($SaleBillspay);
                        $em->flush($SaleBillspay);

                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Sale->getClient());
                        $Billsreceive->setDescription('Passageiro '.$pax['pax_name'].' - Localizador '.$pax['flightLocator']);
                        $Billsreceive->setOriginalValue(0);
                        $Billsreceive->setActualValue(0);
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Venda Cartao');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate($daysToPay);
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
                        $Billsreceive->setDescription('Passageiro '.$pax['pax_name'].' - Localizador '.$pax['flight_locator']);
                        $Billsreceive->setOriginalValue($Sale->getAmountPaid());
                        $Billsreceive->setActualValue($Sale->getAmountPaid());
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Venda Bilhete');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate($daysToPay);
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);
                    }

                    $credit_card = 0;
                    if ($Sale->getSaleByThird() == 'Y') {

                        if($dados['safeType'] != 'Cartao') {

                            if(($dados['partner'] == 'Rextur Advance') || ($dados['partner'] == 'Rextur Advance')) {
                                if($dados['safeType'] == 'Faturado') {

                                    $Billspay = new \Billspay();
                                    $Billspay->setStatus('A');
                                    $Billspay->setDescription('Venda por Terceiros - '.'Passageiro '.$pax['pax_name'].' - Localizador '.$pax['flight_locator']);

                                    $Billspay->setOriginalValue($Sale->getTotalCost());
                                    $Billspay->setActualValue($Sale->getTotalCost());

                                    $Billspay->setTax(0);
                                    $Billspay->setDiscount(0);
                                    $Billspay->setAccountType('Venda por Parceiro');
                                    $Billspay->setPaymentType('Cartao Credito');
                                    $Billspay->setDueDate($Sale->getIssueDate());
                                    $Billspay->setProvider($Sale->getProviderSaleByThird()); 
                                    $Billspay->setPaymentType('Cartao Credito');
                                    $em->persist($Billspay);
                                    $em->flush($Billspay);

                                    $SaleBillspay = new \SaleBillspay();
                                    $SaleBillspay->setBillspay($Billspay);
                                    $SaleBillspay->setSale($Sale);
                                    $em->persist($SaleBillspay);
                                    $em->flush($SaleBillspay);

                                }

                            } else {

                                $Billspay = new \Billspay();
                                $Billspay->setStatus('A');
                                $Billspay->setDescription('Venda por Terceiros - '.'Passageiro '.$pax['pax_name'].' - Localizador '.$pax['flight_locator']);
                                if($dados['partner'] == 'JACK FOR'){
                                    if(isset($pax['du_tax']) && $pax['du_tax'] != ''){
                                        $Billspay->setOriginalValue($pax['du_tax'] + $dados['tax'] + (32 * ($dados['miles_original'] / 1000)));
                                        $Billspay->setActualValue($pax['du_tax'] + $dados['tax'] + (32 * ($dados['miles_original'] / 1000)));
                                    } else {
                                        $Billspay->setOriginalValue($dados['tax'] + (32 * ($dados['miles_original'] / 1000)));
                                        $Billspay->setActualValue($dados['tax'] + (32 * ($dados['miles_original'] / 1000)));
                                    }
                                } else {
                                    if(isset($pax['du_tax']) && $pax['du_tax'] != ''){
                                        $Billspay->setOriginalValue($pax['du_tax'] + $dados['tax']);
                                        $Billspay->setActualValue($pax['du_tax'] + $dados['tax']);
                                    } else {
                                        $Billspay->setOriginalValue($dados['tax']);
                                        $Billspay->setActualValue($dados['tax']);
                                    }
                                }
                                $Billspay->setTax(0);
                                $Billspay->setDiscount(0);
                                $Billspay->setAccountType('Venda por Parceiro');
                                $Billspay->setPaymentType('Cartao Credito');
                                $Billspay->setDueDate($Sale->getIssueDate());
                                $Billspay->setProvider($Sale->getProviderSaleByThird()); 
                                $Billspay->setPaymentType('Cartao Credito');
                                $em->persist($Billspay);
                                $em->flush($Billspay);

                                $SaleBillspay = new \SaleBillspay();
                                $SaleBillspay->setBillspay($Billspay);
                                $SaleBillspay->setSale($Sale);
                                $em->persist($SaleBillspay);
                                $em->flush($SaleBillspay);
                            }
                        }
                    } else {

                        if(isset($pax['du_tax'])) {
                            $credit_card += $pax['du_tax'];
                            if($InternalCards){
                                $InternalCards->setCardUsed($InternalCards->getCardUsed() + $pax['du_tax']);
                                $em->persist($InternalCards);
                            }
                        }

                        if(isset($pax['money'])) {
                            $credit_card += $pax['money'];
                            if($InternalCards){
                                $InternalCards->setCardUsed($InternalCards->getCardUsed() + $pax['money']);
                                $em->persist($InternalCards);
                            }
                        }

                        if(isset($dados['tax'])) {
                            $credit_card += $dados['tax'];
                            if($InternalCards){
                                $InternalCards->setCardUsed($InternalCards->getCardUsed() + $dados['tax']);
                                $em->persist($InternalCards);
                            }
                        }

                        $Billspay = new \Billspay();
                        $Billspay->setStatus('A');
                        $Billspay->setDescription('Passageiro '.$pax['pax_name'].' - Localizador '.$pax['flight_locator']);
                        $Billspay->setOriginalValue($credit_card);
                        $Billspay->setActualValue($credit_card);
                        $Billspay->setTax(0);
                        $Billspay->setDiscount(0);
                        $Billspay->setAccountType('Taxas');
                        $Billspay->setPaymentType('Cartao Credito');
                        $Billspay->setDueDate($Sale->getIssueDate());
                        $Billspay->setPaymentType('Cartao Credito');
                        $em->persist($Billspay);
                        $em->flush($Billspay);

                        $SaleBillspay = new \SaleBillspay();
                        $SaleBillspay->setBillspay($Billspay);
                        $SaleBillspay->setSale($Sale);
                        $em->persist($SaleBillspay);
                        $em->flush($SaleBillspay);
                    }

                    if($InternalCards){

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Uso registrado - Cartao de credito n:".$InternalCards->getCardNumber()." - Venda n:".$Sale->getId()." - Valor R$:".$credit_card."");
                        $SystemLog->setLogType('CREDITCARD');
                        $SystemLog->setBusinesspartner($BusinessPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                        $em->persist($InternalCards);
                        $em->flush($InternalCards);
                    }

                    $Sale->setStatus('Emitido');
                    $em->persist($Sale);
                    $em->flush($Sale);
                }
            }

            $onlineOrder->setAirline($airlines);
            $onlineOrder->setMilesUsed($totalMilesOrder);
            $onlineOrder->setTotalCost($totalCostOrder);

            $onlineFlight->setNumberOfAdult($adults);
            $onlineFlight->setNumberOfChild($childs);
            $onlineFlight->setNumberOfNewborn($newborns);

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

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

    public function remove(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        try {
            $em = Application::getInstance()->getEntityManager();
            $Sale = $em->getRepository('Sale')->find($dados['id']);
            $em->remove($Sale);
            $em->flush($Sale);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro removido com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function mail(Request $request, Response $response) {
        $row = $request->getRow();
        try {
            self::SendOrderByMail($row);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Email enviado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}