<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class purchase {

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
       
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['milesDivisions'])) {
            $milesDivisions = $dados['milesDivisions'];
        }
		if (isset($dados['data'])) {
			$dados = $dados['data'];
        }
        
        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            if(isset($dados['provider_id'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['provider_id']));
            } else if (isset($dados['registrationCode'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $dados['registrationCode'], 'partnerType' => 'P'));
            } else {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['name'], 'partnerType' => 'P'));
            }
            
            if (!$BusinessPartner) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $dados['registrationCode'], 'partnerType' => 'X_P'));
            }

            if (!$BusinessPartner) {
                $BusinessPartner = new \Businesspartner();
                $BusinessPartner->setName($dados['name']);
                $BusinessPartner->setPhoneNumber($dados['phoneNumber']);
                $BusinessPartner->setCity($em->getRepository('City')->findOneBy(array('name' => $dados['city'])));
                $BusinessPartner->setRegistrationCode($dados['registrationCode']);
                $BusinessPartner->setAdress($dados['adress']);
                $BusinessPartner->setEmail($dados['email']);
                $BusinessPartner->setStatus($dados['status']);
                $BusinessPartner->setPaymentType($dados['accountType']);
                $BusinessPartner->setPartnerType('P');
            }

            if(isset($dados['bank']) && $dados['bank'] != ''){
                $BusinessPartner->setBank($dados['bank']);
            }
            if(isset($dados['agency']) && $dados['agency'] != ''){
                $BusinessPartner->setAgency($dados['agency']);
            }
            if(isset($dados['account']) && $dados['account'] != ''){
                $BusinessPartner->setAccount($dados['account']);
            }
            if(isset($dados['paymentType']) && $dados['paymentType'] != ''){
                $BusinessPartner->setPaymentType($dados['paymentType']);
            }
            // if(isset($dados['description']) && $dados['description'] != ''){
            //     $BusinessPartner->setDescription($dados['description']);
            // }
            if(isset($dados['chip_number']) && $dados['chip_number'] != ''){
                $BusinessPartner->setChipNumber($dados['chip_number']);
            }
            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

            if(isset($dados['card_number']) && $dados['card_number'] != '' && $dados['card_number'] != null){
                $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $dados['airline']));
                $Cards = $em->getRepository('Cards')->findOneBy(array('businesspartner' => $BusinessPartner->getId(), 'airline' => $Airline->getId()));
                if (!$Cards) {
                    $Cards = new \Cards();
                    $Cards->setCardNumber($dados['card_number']);
                }
                
                $Cards->setBlocked('N');
                $Cards->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
                $Cards->setBusinesspartner($BusinessPartner);
                if(isset($dados['card_type']) && $dados['card_type'] != ''){
                    $Cards->setCardType($dados['card_type']);
                }
                if(isset($dados['accessPassword']) && $dados['accessPassword'] != ''){
                    $Cards->setAccessPassword($dados['accessPassword']);
                }
                if(isset($dados['token']) && $dados['token'] != ''){
                    $Cards->setToken($dados['token']);
                }
                if(isset($dados['onlyInter']) && $dados['onlyInter'] != 'null'){
                    $Cards->setOnlyInter($dados['onlyInter']);
                }
                $em->persist($Cards);
                $em->flush($Cards);
            }

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $Purchase = new \Purchase();
            $Purchase->setPurchaseMiles($dados['purchase_miles']);
            $Purchase->setPurchaseDate(new \Datetime());
            $Purchase->setCostPerThousand($dados['cost_per_thousand']);
            $Purchase->setCostPerThousandPurchase($dados['cost_per_thousand']);
            $Purchase->setTotalCost($dados['total_cost']);
            if (isset($dados['description'])) {
                $Purchase->setDescription($dados['description']);
            }
            $Purchase->setAproved('Y');
            $Purchase->setMilesDueDate(new \Datetime($dados['_miles_due_date']));
            if(isset($dados['_pay_date'])) {
                $Purchase->setPayDate(new \Datetime($dados['_pay_date']));
            }
            if($Cards)
                $Purchase->setCards($Cards);
            $Purchase->setLeftover($dados['purchase_miles']);
            $Purchase->setContractDueDate(new \Datetime($dados['_contract_due_date']));
            $Purchase->setUser($UserPartner);
            if(isset($dados['card_type']) && $dados['card_type'] != ''){
                $Purchase->setCardType($dados['card_type']);
            }
            if(isset($dados['isPromo']) && $dados['isPromo'] != '') {
                $Purchase->setIsPromo($dados['isPromo']);
            }
            if(isset($dados['paymentMethod']) && $dados['paymentMethod'] != '') {
                $Purchase->setPaymentMethod($dados['paymentMethod']);
            }
            if(isset($dados['paymentBy']) && $dados['paymentBy'] != '') {
                $Purchase->setPaymentBy($dados['paymentBy']);
            }
            if(isset($dados['paymentDays']) && $dados['paymentDays'] != '') {
                $Purchase->setPaymentDays($dados['paymentDays']);
            }
            $em->persist($Purchase);
            $em->flush($Purchase);
            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Compra Cadastrada - Compra n:".$Purchase->getId()." - Usuario:".$UserPartner->getName());
            $SystemLog->setLogType('PURCHASE');
            $SystemLog->setBusinesspartner($UserPartner);
            
            $em->persist($SystemLog);
            $em->flush($SystemLog);


            if(!isset($dados['paymentMethod']) || $dados['paymentMethod'] == 'prepaid' || $dados['paymentMethod'] == 'after_use') {
                $Billspay = new \Billspay();
                $Billspay->setStatus('A');
                $Billspay->setProvider($BusinessPartner);
                $Billspay->setDescription('CIA '. $dados['airline'].' - '.number_format($dados['purchase_miles'] , 0, ',', '.'));
                $Billspay->setOriginalValue($dados['total_cost']);
                $Billspay->setActualValue($dados['total_cost']);
                $Billspay->setTax(0);
                $Billspay->setDiscount(0);
                $Billspay->setAccountType('Compra Milhas');
                $Billspay->setPaymentType('Deposito em Conta');
                if($dados['paymentMethod'] != 'after_use') {
                    $Billspay->setDueDate(new \Datetime($dados['_pay_date']));
                }
                $Billspay->setIssueDate(new \Datetime());
                $em->persist($Billspay);
                $em->flush($Billspay);

                $PurchaseBillspay = new \PurchaseBillspay();
                $PurchaseBillspay->setBillspay($Billspay);
                $PurchaseBillspay->setPurchase($Purchase);
                $em->persist($PurchaseBillspay);
                $em->flush($PurchaseBillspay);
            }

            if(isset($milesDivisions)) {
                foreach ($milesDivisions as $division) {
                    $PurchaseMilesDueDate = new \PurchaseMilesDueDate();
                    $PurchaseMilesDueDate->setMilesDueDate(new \DateTime($division['_dueDate']));
                    $PurchaseMilesDueDate->setMiles($division['miles']);
                    $PurchaseMilesDueDate->setPurchase($Purchase);
                    $PurchaseMilesDueDate->setMilesOriginal($division['miles']);

                    $em->persist($PurchaseMilesDueDate);
                    $em->flush($PurchaseMilesDueDate);
                }
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Compra finalizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public static function generateBillsPay($purchase_id, $cards_id, $miles, $totalCost, $pay_date) {
        $em = Application::getInstance()->getEntityManager();

        $Cards = $em->getRepository('Cards')->find($cards_id);
        $Purchase = $em->getRepository('Purchase')->find($purchase_id);

        $Billspay = new \Billspay();
        $Billspay->setStatus('A');
        $Billspay->setProvider($Cards->getBusinesspartner());
        $Billspay->setDescription('POS PAGO - CIA '. $Cards->getAirline()->getName().' - '.number_format($miles , 0, ',', '.'));
        $Billspay->setOriginalValue($totalCost);
        $Billspay->setActualValue($totalCost);
        $Billspay->setTax(0);
        $Billspay->setDiscount(0);
        $Billspay->setAccountType('Compra Milhas');
        $Billspay->setPaymentType('Deposito em Conta');
        $Billspay->setDueDate($pay_date);
        $Billspay->setIssueDate(new \Datetime());
        $em->persist($Billspay);
        $em->flush($Billspay);

        $PurchaseBillspay = new \PurchaseBillspay();
        $PurchaseBillspay->setBillspay($Billspay);
        $PurchaseBillspay->setPurchase($Purchase);
        $em->persist($PurchaseBillspay);
        $em->flush($PurchaseBillspay);
    }

    public function generatePurchase(Request $request, Response $response) {
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

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partnerName'], 'email' => $dados['email'], 'partnerType' => 'P'));
		
          if (!$BusinessPartner) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partnerName'], 'email' => $dados['email'], 'partnerType' => 'X_P'));
            }
            if(isset($dados['chip_number']) && $dados['chip_number'] != ''){
                $BusinessPartner->setChipNumber($dados['chip_number']);
            }

            $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $dados['airline']));
            $Cards = $em->getRepository('Cards')->findOneBy(array('businesspartner' => $BusinessPartner->getId(), 'airline' => $Airline->getId()));
            if (!$Cards) {
                $Cards = new \Cards();
            }
            $Cards->setCardNumber($dados['cardNumber']);
            $Cards->setAccessPassword($dados['accessPassword']);
            $Cards->setAccessId($dados['accessId']);
            $Cards->setRecoveryPassword($dados['recoveryPassword']);
            $Cards->setBlocked('N');
            $Cards->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['airline'])));
            $Cards->setBusinesspartner($BusinessPartner);
            $Cards->setCardType($dados['card_type']);
            $Cards->setToken($dados['token']);

            if(isset($dados['daysPriority']) && $dados['daysPriority'] != '') {
                $Cards->setDaysPriority($dados['daysPriority']);
            }

            if(isset($dados['isPriority']) && $dados['isPriority'] == 'true') {
                $Cards->setIsPriority('true');
            }

            if(isset($dados['peopleUsedByTheCard']) && $dados['peopleUsedByTheCard'] != '') {
                $Cards->setPeopleUsedByTheCard($dados['peopleUsedByTheCard']);
            }

            if(isset($dados['maxPerPax']) && $dados['maxPerPax'] != '') {
                $Cards->setMaxPerPax($dados['maxPerPax']);
            } else {
                // SETANDO MAX PER PAX NO BANCO TAMBÉM
                if($dados['airline'] == 'LATAM') {
                    $Cards->setMaxPerPax(18);
                } else if($dados['airline'] == 'AZUL') {
                    $Cards->setMaxPerPax(16);
                } else if($dados['airline'] == 'GOL') {
                    $Cards->setMaxPerPax(22);

                    if(isset($dados['card_type']) && strtoupper($dados['card_type']) == 'DIAMANTE')
                        $Cards->setMaxDiamondPax(0);
                }
            }


            if(isset($dados['onlyInter']) && $dados['onlyInter'] != 'null'){
                $Cards->setOnlyInter($dados['onlyInter']);
            }

            $em->persist($Cards);
            $em->flush($Cards);

            $Purchase = $em->getRepository('Purchase')->findOneBy(array('id' => $dados['id']));
            if($Purchase->getStatus() != 'M') {

                $Purchase->setStatus('M');
                $Purchase->setRealPurchased($dados['purchaseMiles']);
                $Purchase->setLeftover($dados['purchaseMiles']);
                $Purchase->setContractDueDate(new \Datetime($dados['contract_due_date']));
                if(isset($dados['_milesDueDate']) && $dados['_milesDueDate'] != '') {
                    $Purchase->setMilesDueDate(new \Datetime( $dados['_milesDueDate'] ));
                }
                $Purchase->setMergeDate(new \Datetime());
                if(isset($dados['card_type']) && $dados['card_type'] != ''){
                    $Purchase->setCardType($dados['card_type']);
                }
                if(isset($dados['isPromo']) && $dados['isPromo'] != '') {
                    $Purchase->setIsPromo($dados['isPromo']);
                }
                $em->persist($Purchase);
                $em->flush($Purchase);

                $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Cards->getId()));
                if (!$MilesBench) {
                    $MilesBench = new \MilesBench();
                    $MilesBench->setCards($Cards);
                    $MilesBench->setContractDueDate($Purchase->getContractDueDate());
                }

                $total_miles = ($MilesBench->getLeftOver() + $dados['purchaseMiles']);
                $total_cost = (($MilesBench->getLeftOver()/1000) * $MilesBench->getCostPerThousand()) + (($dados['purchaseMiles']/1000) * $dados['costPerThousand']);
                $MilesBench->setLastChange(new \Datetime());
                $MilesBench->setCostPerThousand($total_cost / ($total_miles/1000));
                $MilesBench->setLeftOver($total_miles);
                $MilesBench->setContractDueDate(new \Datetime($dados['contract_due_date']));
                $MilesBench->setDueDate(new \Datetime($dados['_milesDueDate']));

                $em->persist($MilesBench);
                $em->flush($MilesBench);

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Compra Finalizada - Compra n:".$Purchase->getId()." - Usuario:".$UserPartner->getName().". Foram adicionados ".number_format($dados['purchaseMiles'], 0, ',', '.')." milhas para utilização.");
                $SystemLog->setLogType('PURCHASE');
                $SystemLog->setBusinesspartner($UserPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Compra finalizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadPurchaseHistory(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $dataset = array();
        try {

            $em = Application::getInstance()->getEntityManager();
            if(isset($dados['cards_id']) && $dados['cards_id'] != '') {
                $Card = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
            }
            if(isset($Card)){
                $sql = "select p FROM Purchase p WHERE p.cards = '".$dados['cards_id']."' and p.status='M' order by p.id DESC";
            }else{
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['name']));
                if($BusinessPartner) {
                    $Card = $em->getRepository('Cards')->findOneBy(array('businesspartner' => $BusinessPartner->getId()));
                    if($Card) {
                        $sql = "select p FROM Purchase p WHERE p.cards = '".$Card->getId()."' and p.status='M' order by p.id DESC";
                    } else {
                        throw new Exception("Erro de proccessamento", 1);
                    }
                } else {
                    throw new Exception("Erro de proccessamento", 1);
                }
            }
            $query = $em->createQuery($sql);
            $Purchases = $query->getResult();

            foreach ($Purchases as $item) {
                $Card = $em->getRepository('Cards')->findOneBy(array('id' => $item->getCards()->getId()));
                $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Card->getId()));
                if($item->getContractDueDate()){
                    $contract_due_date = $item->getContractDueDate()->format('Y-m-d');
                } else {
                    $contract_due_date = $MilesBench->getContractDueDate()->format('Y-m-d');
                }

                $cost_per_thousand = $item->getCostPerThousand();
                if($item->getCostPerThousandPurchase() != '0.00') {
                    $cost_per_thousand = $item->getCostPerThousandPurchase();
                }

                $dataset[] = array(
                    'purchase_date' => $item->getPurchaseDate()->format('Y-m-d'),
                    'purchase_miles' => $item->getPurchaseMiles(),
                    'airline' => $Card->getAirline()->getName(),
                    'card_type' => $item->getCardType(),
                    'cost_per_thousand' => $cost_per_thousand,
                    'miles_due_date' => $contract_due_date,
                    'total_cost' => $item->getTotalCost(),
                    'description' => $item->getDescription(),
                    'leftover' => (float)$item->getLeftover(),
                    'realPurchased' => $item->getRealPurchased(),
                    'id' => $item->getId()
                );
            }
            $response->setDataset($dataset);
        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";

            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - loadPurchaseHistory',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Erro identificado');
            $response->addMessage($message);
        }
    }

    public function saveCardStatus(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Alterações salvas com sucesso');

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $Purchase = $em->getRepository('Purchase')->findOneBy(array('id' => $dados['id']));
            $Card = $em->getRepository('Cards')->findOneBy(array('id' => $Purchase->getCards()));
            $BusinessPartner = $Card->getBusinesspartner();

            if(isset($dados['purchaseMiles']) && $dados['purchaseMiles'] != ''){
                $Purchase->setPurchaseMiles($dados['purchaseMiles']);
            }
            if(isset($dados['description']) && $dados['description'] != ''){
                $Purchase->setDescription($dados['description']);
            }
            if(isset($dados['milesDueDate']) && $dados['milesDueDate'] != ''){
                $Purchase->setMilesDueDate(new \Datetime($dados['milesDueDate']));
            }
            if(isset($dados['totalCost']) && $dados['totalCost'] != ''){
                $Purchase->setTotalCost($dados['totalCost']);
            }
            if(isset($dados['losses']) && $dados['losses'] != ''){
                $Purchase->setLosses($dados['losses']);
            }
            if(isset($dados['costPerThousand']) && $dados['costPerThousand'] != ''){
                $Purchase->setCostPerThousand($dados['costPerThousand']);
                $Purchase->setCostPerThousandPurchase($dados['costPerThousand']);
            }
            if(isset($dados['isPromo']) && $dados['isPromo'] != '') {
                $Purchase->setIsPromo($dados['isPromo']);
            }
            if(isset($dados['leftover']) && $dados['leftover'] != '' && $UserPartner->getIsMaster() == 'true'){
                $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Card));

                if(new \Datetime($dados['lastchange']) > $MilesBench->getLastchange()) {
                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Milhas Alteradas - Milhas alteradas do cartao: ".$Card->getId()." de: ".$MilesBench->getLeftover()." para ".($MilesBench->getLeftover() - $Purchase->getLeftover()) + $dados['leftover']);
                    $SystemLog->setLogType('MILESBENCH');
                    $SystemLog->setBusinesspartner($UserPartner);

                    $em->persist($SystemLog);
                    $em->flush($SystemLog);

                    $MilesBench->setLeftover(($MilesBench->getLeftover() - $Purchase->getLeftover()) + $dados['leftover']);
                    $MilesBench->setLastChange(new \Datetime());

                    $em->persist($MilesBench);
                    $em->flush($MilesBench);

                    $Purchase->setLeftover($dados['leftover']);
                }  else {
                    $message->setType(\MilesBench\Message::ERROR);
                    $message->setText('Dados desatualizados');
                }
            }
            if(isset($dados['_milesDueDate']) && $dados['_milesDueDate'] != '') {
                $Purchase->setMilesDueDate(new \Datetime($dados['_milesDueDate']));

                $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Card->getId()));
                if($MilesBench) {
                    if($MilesBench->getDueDate() < $Purchase->getMilesDueDate()) {

                        $MilesBench->setDueDate(new \Datetime($dados['_milesDueDate']));

                        $em->persist($MilesBench);
                        $em->flush($MilesBench);
                    }
                }
            }

            if(isset($dados['resolveDescription']) && $dados['resolveDescription'] != '') {

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Compra Alterada - Compra n:".$Purchase->getId()." - Usuario:".$UserPartner->getName().". Motivo: ".$dados['resolveDescription']);
                $SystemLog->setLogType('PURCHASE');
                $SystemLog->setBusinesspartner($UserPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            if(isset($dados['cardNumber']) && $dados['cardNumber'] != ''){
                $Card->setCardNumber($dados['cardNumber']);
            }
            if(isset($dados['accessId']) && $dados['accessId'] != ''){
                $Card->setAccessId($dados['accessId']);
            }
            if(isset($dados['accessPassword']) && $dados['accessPassword'] != ''){
                $Card->setAccessPassword($dados['accessPassword']);
            }
            if(isset($dados['recoveryPassword']) && $dados['recoveryPassword'] != ''){
                $Card->setRecoveryPassword($dados['recoveryPassword']);
            }
            if(isset($dados['card_type']) && $dados['card_type'] != ''){
                $Card->setCardType($dados['card_type']);
                $Purchase->setCardType($dados['card_type']);
            }
            if(isset($dados['token']) && $dados['token'] != ''){
                $Card->setToken($dados['token']);
            }
            if(isset($dados['isPriority']) && $dados['isPriority'] != '') {
                $Card->setIsPriority($dados['isPriority']);
            }
            if(isset($dados['daysPriority'])) {
                if($dados['daysPriority'] == '') {
                    $Card->setDaysPriority(NULL);
                } else {
                    $Card->setDaysPriority($dados['daysPriority']);
                }
            }
            if(isset($dados['onlyInter']) && $dados['onlyInter'] != 'null'){
                $Card->setOnlyInter($dados['onlyInter']);
            }

            if(isset($dados['chip_number']) && $dados['chip_number'] != ''){
                $BusinessPartner->setChipNumber($dados['chip_number']);
            }

            $em->persist($Purchase);
            $em->flush($Purchase);

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

            $em->persist($Card);
            $em->flush($Card);


            $em->getConnection()->beginTransaction();
            
            if(isset($dados['bloqued']) && $dados['bloqued'] != '') {
                if($Card->getBlocked() == 'N' && $dados['bloqued'] == 'Y') {

                    // status socket track
                    $postfields = array(
                        'provider' => $BusinessPartner->getName(),
                        'airline' => $Card->getAirline()->getName()
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/cardBloqued');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);

                }

                $Card->setBlocked($dados['bloqued']);
                $em->persist($Card);
                $em->flush($Card);
            }

            if(isset($dados['removeFromMilesbench']) && ($dados['removeFromMilesbench'] == "true")){
                $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Card->getId()));
                $MilesBench->setLeftover((float)$MilesBench->getLeftover() - (float)$dados['leftover']);
                $em->persist($MilesBench);
                $em->flush($MilesBench);

                $Purchase = $em->getRepository('Purchase')->findOneBy(array('id' => $dados['id']));
                $Purchase->setLeftover((float)$Purchase->getLeftover() - (float)$dados['leftover']);
                $em->persist($Purchase);
                $em->flush($Purchase);
            }

            if(isset($dados['card_tax']) && ($dados['card_tax'] != '')){
                $InternalCard = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $dados['card_tax']));
                if($InternalCard){
                    $Card->setCardTax($InternalCard);

                    $em->persist($Card);
                    $em->flush($Card);
                }
            }

            $PurchaseBillspay = $em->getRepository('PurchaseBillspay')->findOneBy(array('purchase' => $dados['id']));
            if($PurchaseBillspay) {
                $billspay = $PurchaseBillspay->getBillspay();

                $billspay->setDescription('CIA '. $dados['airline'].' - '.number_format($dados['purchaseMiles'] , 0, ',', '.'));
                $billspay->setActualValue($dados['totalCost']);

                $em->persist($billspay);
                $em->flush($billspay);
            }

            $em->getConnection()->commit();

            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $and = '';
        $whereClause = '';
        if (isset($dados['data'])){
            $filter = $dados['data'];    
            if (isset($filter['providerName']) && !($filter['providerName'] == '')) {
                $whereClause = $whereClause. "b.name like '%".$filter['providerName']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['airline']) && !($filter['airline'] == '')) {
                $whereClause = $whereClause. "a.name like '%".$filter['airline']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['paymentMethod']) && !($filter['paymentMethod'] == '')) {
                $whereClause = $whereClause. "p.paymentMethod like '%".$filter['paymentMethod']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['_purchaseDateFrom']) && !($filter['_purchaseDateFrom'] == '')) {
                $_purchaseDateFrom = $filter['_purchaseDateFrom'];
                if (isset($filter['_purchaseDateTo']) && $filter['_purchaseDateTo'] != '') {
                    $_purchaseDateTo = $filter['_purchaseDateTo'];
                    $whereClause = $whereClause.$and. "p.purchaseDate BETWEEN '".$filter['_purchaseDateFrom']."' AND '".$_purchaseDateTo."' ";
                } else {
                    $whereClause = $whereClause.$and. "p.purchaseDate > '".$filter['_purchaseDateFrom']."' ";
                }
                $and = ' AND ';
            };

            if (isset($filter['_dueDateFrom']) && !($filter['_dueDateFrom'] == '')) {
                $_dueDateFrom = $filter['_dueDateFrom'];
                if (isset($filter['_dueDateTo']) && !($filter['_dueDateTo'] == '')) {
                    $_dueDateTo = $filter['_dueDateTo'];
                }
                $whereClause = $whereClause.$and. "p.milesDueDate BETWEEN '".$filter['_dueDateFrom']."' AND '".$_dueDateTo."' ";
                $and = ' AND ';
            };
        }

        if(isset($filter['status']) && $filter['status'] != '') {
            if($filter['status'] == 'Confirmadas') {
                $whereClause = $whereClause.$and." p.status = 'M'";
            } else if($filter['status'] == 'Canceladas') {
                $whereClause = $whereClause.$and." p.status = 'C'";
            } else if($filter['status'] == 'Pendentes') {
                $whereClause = $whereClause.$and." p.status = 'W'";
            } else if($filter['status'] == 'Todas') {
                $whereClause = $whereClause.$and." p.status IN ('C', 'M', 'W')";
            }
            $and = ' AND ';
        } else {
            $whereClause = $whereClause.$and." p.status = 'M'";
            $and = ' AND ';
        }

        if($whereClause != '') {
            $where = ' WHERE ';
            $and = ' ';
        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where = " WHERE ( "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCode like '%".$dados['searchKeywords']."%' or "
                ." b.email like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber like '%".$dados['searchKeywords']."%' or "
                ." c.cardNumber like '%".$dados['searchKeywords']."%' or "
                ." a.name like '%".$dados['searchKeywords']."%' or "
                ." p.purchaseMiles like '%".$dados['searchKeywords']."%' or "
                ." p.totalCost like '%".$dados['searchKeywords']."%' or "
                ." c.accessId like '%".$dados['searchKeywords']."%' or "
                ." c.accessPassword like '%".$dados['searchKeywords']."%' or "
                ." c.recoveryPassword like '%".$dados['searchKeywords']."%' or "
                ." p.description like '%".$dados['searchKeywords']."%' or "
                ." p.cardType like '%".$dados['searchKeywords']."%' or "
                ." c.token like '%".$dados['searchKeywords']."%' or "
                ." c.token like '%".$dados['searchKeywords']."%' or "
                ." c.id like '%".$dados['searchKeywords']."%' ) ";
            $and = ' AND ';
        }

        $sql = "select p FROM Purchase p JOIN p.cards c JOIN c.airline a JOIN c.businesspartner b ".$where.$and.$whereClause;

         // order
        $orderBy = '';
        if(isset($dados['order']) && $dados['order'] != '') {
            if($dados['order'] == 'card_type') {
                $orderBy = ' order by c.cardType ASC ';
            } else if($dados['order'] == 'purchaseDate') {
                $orderBy = ' order by p.purchaseDate ASC ';
            } else if($dados['order'] == 'leftover') {
                $orderBy = ' order by p.leftover ASC ';
            } else {
                $orderBy = ' order by b.'.$dados['order'].' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if($dados['orderDown'] == 'card_type') {
                $orderBy = ' order by c.cardType DESC ';
            } else if($dados['orderDown'] == 'purchaseDate') {
                $orderBy = ' order by p.purchaseDate DESC ';
            } else if($dados['orderDown'] == 'leftover') {
                $orderBy = ' order by p.leftover DESC ';
            } else {
                $orderBy = ' order by b.'.$dados['orderDown'].' DESC ';
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
        $Purchases = $query->getResult();

        $purchases = array();
        foreach($Purchases as $Purchase){
            $Cards = $Purchase->getCards();
            $BusinessPartner = $Cards->getBusinesspartner();
            if($Cards->getCardTax() != null){
                $InternalCard = $Cards->getCardTax()->getCardNumber();
            }else{
                $InternalCard = '';
            }

            $costPerThousand = (float)$Purchase->getCostPerThousand();
            if((float)$Purchase->getCostPerThousandPurchase() != 0) {
                $costPerThousand = (float)$Purchase->getCostPerThousandPurchase();
            }

            $milesDueDate = '';
            if($Purchase->getMilesDueDate()) {
                $milesDueDate = $Purchase->getMilesDueDate()->format('Y-m-d');
            }

            $purchases[] = array(
                'id' => $Purchase->getId(),
                'partnerName' => $BusinessPartner->getName(),
                'partnerRegistrationCode' => $BusinessPartner->getRegistrationCode(),
                'email' => $BusinessPartner->getEmail(),
                'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                'cardNumber' => $Cards->getCardNumber(),
                'airline' => $Cards->getAirline()->getName(),
                'purchaseMiles' => (int)$Purchase->getPurchaseMiles(),
                'milesDueDate' => $milesDueDate,
                'purchaseDate' => $Purchase->getPurchaseDate()->format('Y-m-d'),
                'costPerThousand' => $costPerThousand,
                'totalCost' => (float)$Purchase->getTotalCost(),
                'accessId' => $Cards->getAccessId(),
                'accessPassword' => $Cards->getAccessPassword(),
                'recoveryPassword' => $Cards->getRecoveryPassword(),
                'bloqued'=> $Cards->getBlocked(),
                'description' => $Purchase->getDescription(),
                'card_type' => $Purchase->getCardType(),
                'token' => $Cards->getToken(),
                'leftover' => (float)$Purchase->getLeftover(),
                'card_tax' => $InternalCard,
                'isPriority' => $Cards->getIsPriority(),
                'cards_id' => $Cards->getId(),
                'losses' => (float)$Purchase->getLosses(),
                'lastchange' => (new \DateTime())->format('Y-m-d H:i:s'),
                'daysPriority' => $Cards->getDaysPriority(),
                'isPromo' => ($Purchase->getIsPromo() == 'true'),
                'chip_number' => $BusinessPartner->getChipNumber(),
                'paymentMethod' => $Purchase->getPaymentMethod(),
                'onlyInter' => $Cards->getOnlyInter()
            );
        }

        $sql = "select COUNT(p) as quant FROM Purchase p JOIN p.cards c JOIN c.airline a JOIN c.businesspartner b ".$where.$and.$whereClause;
        
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'purchases' => $purchases,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function loadMilesDueDate(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $PurchaseMilesDueDate = $em->getRepository('PurchaseMilesDueDate')->findBy(array('purchase' => $dados['id']));
        $dataset = array();

        foreach($PurchaseMilesDueDate as $Division){
            $dataset[] = array(
                'id' => $Division->getId(),
                'milesDueDate' => $Division->getMilesDueDate()->format('Y-m-d H:i:s'),
                'miles' => (float)$Division->getMiles(),
                'milesOriginal' => (float)$Division->getMilesOriginal()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadPurchasesWaiting(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $and = '';
        $whereClause = '';
        if (isset($dados['data'])){
            $filter = $dados['data'];    
            if (isset($filter['providerName']) && !($filter['providerName'] == '')) {
                $whereClause = $whereClause. "b.name like '%".$filter['providerName']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['airline']) && !($filter['airline'] == '')) {
                $whereClause = $whereClause. "a.name like '%".$filter['airline']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['paymentMethod']) && !($filter['paymentMethod'] == '')) {
                $whereClause = $whereClause. "p.paymentMethod like '%".$filter['paymentMethod']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['_purchaseDateFrom']) && !($filter['_purchaseDateFrom'] == '')) {
                $_purchaseDateFrom = $filter['_purchaseDateFrom'];
                if (isset($filter['_purchaseDateTo']) && $filter['_purchaseDateTo'] != '') {
                    $_purchaseDateTo = $filter['_purchaseDateTo'];
                    $whereClause = $whereClause.$and. "p.purchaseDate BETWEEN '".$filter['_purchaseDateFrom']."' AND '".$_purchaseDateTo."' ";
                } else {
                    $whereClause = $whereClause.$and. "p.purchaseDate > '".$filter['_purchaseDateFrom']."' ";
                }
                $and = ' AND ';
            };

            if (isset($filter['_dueDateFrom']) && !($filter['_dueDateFrom'] == '')) {
                $_dueDateFrom = $filter['_dueDateFrom'];
                if (isset($filter['_dueDateTo']) && !($filter['_dueDateTo'] == '')) {
                    $_dueDateTo = $filter['_dueDateTo'];
                }
                $whereClause = $whereClause.$and. "p.milesDueDate BETWEEN '".$filter['_dueDateFrom']."' AND '".$_dueDateTo."' ";
                $and = ' AND ';
            };
        }

        $whereClause = $whereClause.$and." p.status = 'W'";
        $and = ' AND ';

        if($whereClause != '') {
            $where = ' WHERE ';
            $and = ' ';
        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where = " WHERE ( "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCode like '%".$dados['searchKeywords']."%' or "
                ." b.email like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber like '%".$dados['searchKeywords']."%' or "
                ." c.cardNumber like '%".$dados['searchKeywords']."%' or "
                ." a.name like '%".$dados['searchKeywords']."%' or "
                ." p.purchaseMiles like '%".$dados['searchKeywords']."%' or "
                ." p.totalCost like '%".$dados['searchKeywords']."%' or "
                ." c.accessId like '%".$dados['searchKeywords']."%' or "
                ." c.accessPassword like '%".$dados['searchKeywords']."%' or "
                ." c.recoveryPassword like '%".$dados['searchKeywords']."%' or "
                ." p.description like '%".$dados['searchKeywords']."%' or "
                ." p.cardType like '%".$dados['searchKeywords']."%' or "
                ." c.token like '%".$dados['searchKeywords']."%' or "
                ." c.token like '%".$dados['searchKeywords']."%' or "
                ." c.id like '%".$dados['searchKeywords']."%' ) ";
            $and = ' AND ';
        }

        $sql = "select p FROM Purchase p JOIN p.cards c JOIN c.airline a JOIN c.businesspartner b ".$where.$and.$whereClause;

         // order
        $orderBy = '';
        if(isset($dados['order']) && $dados['order'] != '') {
            if($dados['order'] == 'card_type') {
                $orderBy = ' order by c.cardType ASC ';
            } else if($dados['order'] == 'purchaseDate') {
                $orderBy = ' order by p.purchaseDate ASC ';
            } else if($dados['order'] == 'leftover') {
                $orderBy = ' order by p.leftover ASC ';
            } else {
                $orderBy = ' order by b.'.$dados['order'].' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if($dados['orderDown'] == 'card_type') {
                $orderBy = ' order by c.cardType DESC ';
            } else if($dados['orderDown'] == 'purchaseDate') {
                $orderBy = ' order by p.purchaseDate DESC ';
            } else if($dados['orderDown'] == 'leftover') {
                $orderBy = ' order by p.leftover DESC ';
            } else {
                $orderBy = ' order by b.'.$dados['orderDown'].' DESC ';
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
        $Purchases = $query->getResult();

        $purchases = array();
        foreach($Purchases as $Purchase){
            $Cards = $Purchase->getCards();
            $BusinessPartner = $Cards->getBusinesspartner();

            $Billspay_status = '';
            $Billspay_DueDate = '';
            $PurchaseBillspay = $em->getRepository('PurchaseBillspay')->findOneBy(array('purchase' => $Purchase->getId()));
            if($PurchaseBillspay) {
                $Billspay = $PurchaseBillspay->getBillspay();
                $Billspay_status = $Billspay->getStatus();
                if($Billspay->getDueDate()) {
                    $Billspay_DueDate = $Billspay->getDueDate()->format('Y-m-d H:i:s');
                }
            }

            if($Cards->getCardTax() != null){
                $InternalCard = $Cards->getCardTax()->getCardNumber();
            }else{
                $InternalCard = '';
            }

            $purchaseDueDate = '';
            if($Purchase->getContractDueDate()){
                $purchaseDueDate = $Purchase->getContractDueDate()->format('Y-m-d');
            }

            $milesDueDate = '';
            if($Purchase->getMilesDueDate()) {
                $milesDueDate = $Purchase->getMilesDueDate()->format('Y-m-d');
            }

            $cost_per_thousand = $Purchase->getCostPerThousand();
            if($Purchase->getCostPerThousandPurchase() != '0.00') {
                $cost_per_thousand = $Purchase->getCostPerThousandPurchase();
            }

            $purchases[] = array(
                'id' => $Purchase->getId(),
                'partnerName' => $BusinessPartner->getName(),
                'email' => $BusinessPartner->getEmail(),
                'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                'partner_status' => $BusinessPartner->getStatus(),
                'cardNumber' => $Cards->getCardNumber(),
                'airline' => $Cards->getAirline()->getName(),
                'purchaseMiles' => (int)$Purchase->getPurchaseMiles(),
                'milesDueDate' => $milesDueDate,
                'purchaseDate' => $Purchase->getPurchaseDate()->format('Y-m-d'),
                'costPerThousand' => (float)$cost_per_thousand,
                'totalCost' => (float)$Purchase->getTotalCost(),
                'accessId' => $Cards->getAccessId(),
                'accessPassword' => $Cards->getAccessPassword(),
                'recoveryPassword' => $Cards->getRecoveryPassword(),
                'bloqued'=> $Cards->getBlocked(),
                'description' => $Purchase->getDescription(),
                'card_type' => $Cards->getCardType(),
                'token' => $Cards->getToken(),
                'leftover' => (float)$Purchase->getLeftover(),
                'card_tax' => $InternalCard,
                'contract_due_date' => $purchaseDueDate,
                'daysPriority' => $Cards->getDaysPriority(),
                'registrationCode' => $BusinessPartner->getRegistrationCode(),
                'payment_status' => $Billspay_status,
                'payment_date' => $Billspay_DueDate,
                'onlyInter' => $Cards->getOnlyInter(),
                'isPromo' => ( $Purchase->getIsPromo() == 'true' ),
                'bank' => $BusinessPartner->getBank(),
                'agency' => $BusinessPartner->getAgency(),
                'account' => $BusinessPartner->getAccount(),
                'paymentType' => $BusinessPartner->getPaymentType(),
                'bankOperation' => $BusinessPartner->getBankOperation(),
                'bankNameOwner' => $BusinessPartner->getBankNameOwner(),
                'cpfNameOwner' => $BusinessPartner->getCpfNameOwner()
            );

        }
        $sql = "select COUNT(p) as quant FROM Purchase p JOIN p.cards c JOIN c.airline a JOIN c.businesspartner b ".$where.$and.$whereClause;
        
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'purchases' => $purchases,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function loadPurchasePartner(Request $request, Response $response){
        $dados = $request->getRow();

        $dados = $dados['data'];

        $em = Application::getInstance()->getEntityManager();
        $partner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partnerName']));
        $dataset = array();

        $dataset[] = array(
            'id' => $partner->getId(),
            'name' => $partner->getName(),
            'registrationCode' => $partner->getRegistrationCode(),
            'birthdate' => $partner->getBirthdate(),
            'companyName' => $partner->getCompanyName(),
            'adress' => $partner->getAdress(),
            'adressDistrict' => $partner->getAdressDistrict(),
            'city' => $partner->getCity(),
            'phoneNumber' => $partner->getPhoneNumber(),
            'phoneNumber2' => $partner->getPhoneNumber2(),
            'phoneNumber3' => $partner->getPhoneNumber3(),
            'email' => $partner->getEmail()

        );
        $response->setDataset($dataset);
    }

    public function loadPurchaseByFilter(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $sql = "select p, c, b, a FROM Purchase p JOIN p.cards c JOIN c.businesspartner b JOIN c.airline a";
        $whereClause = ' WHERE ';
        $and = '';

        if (isset($dados['data'])){
            $dados = $dados['data'];    
        }
        
        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause. "b.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['airline']) && !($dados['airline'] == '')) {
            $whereClause = $whereClause. "a.name like '%".$dados['airline']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['_purchaseDateFrom']) && !($dados['_purchaseDateFrom'] == '')) {
            $_purchaseDateFrom = $dados['_purchaseDateFrom'];
            if (isset($dados['_purchaseDateTo']) && !($dados['_purchaseDateTo'] == '')) {
                $_purchaseDateTo = $dados['_purchaseDateTo'];
            }
            $whereClause = $whereClause.$and. "p.purchaseDate BETWEEN '".$dados['_purchaseDateFrom']."' AND '".$_purchaseDateTo."' ";
            $and = ' AND ';
        };

        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $_dueDateFrom = $dados['_dueDateFrom'];
            if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
                $_dueDateTo = $dados['_dueDateTo'];
            }
            $whereClause = $whereClause.$and. "p.milesDueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
            $and = ' AND ';
        };

        if(isset($dados['status']) && $dados['status'] != '') {
            if($dados['status'] == 'Confirmadas') {
                $whereClause = $whereClause.$and." p.status = 'M'";
            } else if($dados['status'] == 'Canceladas') {
                $whereClause = $whereClause.$and." p.status = 'C'";
            }
        } else {
            $whereClause = $whereClause.$and." p.status = 'M'";
        }
        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause; 
        };
        
        $query = $em->createQuery($sql);
        $Purchase = $query->getResult();

        $dataset = array();
        foreach($Purchase as $item){
            $cost_per_thousand = $item->getCostPerThousand();
            if($item->getCostPerThousandPurchase() != '0.00') {
                $cost_per_thousand = $item->getCostPerThousandPurchase();
            }
            $Cards = $item->getCards();
            $BusinessPartner = $Cards->getBusinesspartner();
            $dataset[] = array(
                'id' => $item->getId(),
                'partnerName' => $BusinessPartner->getName(),
                'email' => $BusinessPartner->getEmail(),
                'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                'cardNumber' => $Cards->getCardNumber(),
                'airline' => $Cards->getAirline()->getName(),
                'purchaseMiles' => (int)$item->getPurchaseMiles(),
                'milesDueDate' => $item->getMilesDueDate()->format('Y-m-d'),
                'purchaseDate' => $item->getPurchaseDate()->format('Y-m-d'),
                'costPerThousand' => (float)$cost_per_thousand,
                'totalCost' => (float)$item->getTotalCost(),
                'accessId' => $Cards->getAccessId(),
                'accessPassword' => $Cards->getAccessPassword(),
                'recoveryPassword' => $Cards->getRecoveryPassword(),
                'bloqued'=> $Cards->getBlocked(),
                'card_type' => $item->getCardType(),
                'token' => $Cards->getToken(),
                'cards_id' => $Cards->getId(),
                'leftover' => (float)$item->getLeftover(),
                'losses' => (float)$item->getLosses(),
                'lastchange' => (new \DateTime())->format('Y-m-d H:i:s'),
                'daysPriority' => $Cards->getDaysPriority(),
                'isPromo' => ( $item->getIsPromo() == 'true' )
            );

        }
        $response->setDataset($dataset);
    }

    public function loadLastPurchaseData(Request $request, Response $response) {
        $dados = $request->getRow();

        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $sql = "select MAX(p.purchaseDate) FROM Purchase p where p.cards = '".$dados['cards_id']."' ";
        $query = $em->createQuery($sql);
        $Purchases = $query->getResult();

        $dataset = array( $Purchases[0][1] );
        $response->setDataset($dataset);
    }

    public function removePurchase(Request $request, Response $response) {
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
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $Purchase = $em->getRepository('Purchase')->find($dados['id']);
            $Cards = $Purchase->getCards();
            $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Cards->getId() ));
            $BusinessPartner = $Cards->getBusinesspartner();
            
            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Compra removida - Fornecedor: " . $dados['partnerName'] . " - Pontos: " . $dados['purchaseMiles']);
            $SystemLog->setLogType('REMOVE-PURCHASE');
            $SystemLog->setBusinesspartner($UserPartner);
            
            $em->remove($Purchase);
            $em->flush($Purchase);
            
            $AllPurchase = $em->getRepository('Purchase')->findBy(array('cards' => $Cards->getId() ));
            if(count($AllPurchase) > 1) {
                throw new \Exception("Mais de uma compra detectada para esse fornecedor", 1);
            } else {

                $em->remove($Milesbench);
                $em->flush($Milesbench);

                $em->remove($Cards);
                $em->flush($Cards);
            }

            $AllCards = $em->getRepository('Cards')->findBy(array('businesspartner' => $BusinessPartner->getId() ));
            if(count($AllCards) > 1) {
                throw new \Exception("Mais de um cartao detectado para esse fornecedor", 1);
            } else {
                $em->remove($BusinessPartner);
                $em->flush($BusinessPartner);
            }
            
            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Compra removida com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    // 
    public function loadPurchasesToOperations(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $and = '';
        $whereClause = '';
        if (isset($dados['data'])){
            $filter = $dados['data'];    
            if (isset($filter['providerName']) && !($filter['providerName'] == '')) {
                $whereClause = $whereClause. "b.name like '%".$filter['providerName']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['airline']) && !($filter['airline'] == '')) {
                $whereClause = $whereClause. "a.name like '%".$filter['airline']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['paymentMethod']) && !($filter['paymentMethod'] == '')) {
                $whereClause = $whereClause. "p.payment_method like '%".$filter['paymentMethod']."%' ";
                $and = ' AND ';
            };

            if (isset($filter['_purchaseDateFrom']) && !($filter['_purchaseDateFrom'] == '')) {
                $_purchaseDateFrom = $filter['_purchaseDateFrom'];
                if (isset($filter['_purchaseDateTo']) && $filter['_purchaseDateTo'] != '') {
                    $_purchaseDateTo = $filter['_purchaseDateTo'];
                    $whereClause = $whereClause.$and. "p.purchase_date BETWEEN '".$filter['_purchaseDateFrom']."' AND '".$_purchaseDateTo."' ";
                } else {
                    $whereClause = $whereClause.$and. "p.purchase_date > '".$filter['_purchaseDateFrom']."' ";
                }
                $and = ' AND ';
            };

            if (isset($filter['_dueDateFrom']) && !($filter['_dueDateFrom'] == '')) {
                $_dueDateFrom = $filter['_dueDateFrom'];
                if (isset($filter['_dueDateTo']) && !($filter['_dueDateTo'] == '')) {
                    $_dueDateTo = $filter['_dueDateTo'];
                }
                $whereClause = $whereClause.$and. "p.miles_due_date BETWEEN '".$filter['_dueDateFrom']."' AND '".$_dueDateTo."' ";
                $and = ' AND ';
            };
        }

        if(isset($filter['status']) && $filter['status'] != '') {
            if($filter['status'] == 'Confirmadas') {
                $whereClause = $whereClause.$and." p.status = 'M'";
            } else if($filter['status'] == 'Canceladas') {
                $whereClause = $whereClause.$and." p.status = 'C'";
            } else if($filter['status'] == 'Pendentes') {
                $whereClause = $whereClause.$and." p.status = 'W'";
            } else if($filter['status'] == 'Todas') {
                $whereClause = $whereClause.$and." p.status IN ('C', 'M', 'W')";
            }
            $and = ' AND ';
        } else {
            $whereClause = $whereClause.$and." p.status = 'M'";
            $and = ' AND ';
        }

        if($whereClause != '') {
            $where = ' WHERE ';
            $and = ' ';
        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where = " WHERE ( "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registration_code like '%".$dados['searchKeywords']."%' or "
                ." b.email like '%".$dados['searchKeywords']."%' ) ";
            $and = ' AND ';
        }

        $sql = "SELECT p.purchase_date, p.leftover, b.status as provider_status, p.purchase_miles, p.cost_per_thousand, p.total_cost,
            p.miles_due_date, b.name as providerName, c.card_number as card_number, c.recovery_password as recovery_password,
            b.registration_code as registration_code, c.token, c.access_password as access_password, p.id as id, c.id as cards_id, ".
            " p.merge_date as merge_date, " .
            " b.phone_number as phone_number, b.phone_number2 as phone_number2, b.email as email, a.name as airline  ".
            " FROM purchase p ".
            " INNER JOIN cards c on c.id = p.cards_id ".
            " INNER JOIN airline a on a.id = c.airline_id ".
            " INNER JOIN businesspartner b on b.id = c.businesspartner_id ".$where.$and.$whereClause;

         // order
        $orderBy = '';
        if(isset($dados['order']) && $dados['order'] != '') {
            if($dados['order'] == 'card_type') {
                $orderBy = ' order by c.card_type ASC ';
            } else if($dados['order'] == 'purchase_date') {
                $orderBy = ' order by p.purchase_date ASC ';
            } else if($dados['order'] == 'leftover') {
                $orderBy = ' order by p.leftover ASC ';
            } else {
                $orderBy = ' order by b.'.$dados['order'].' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if($dados['orderDown'] == 'card_type') {
                $orderBy = ' order by c.card_type DESC ';
            } else if($dados['orderDown'] == 'purchase_date') {
                $orderBy = ' order by p.purchase_date DESC ';
            } else if($dados['orderDown'] == 'leftover') {
                $orderBy = ' order by p.leftover DESC ';
            } else {
                $orderBy = ' order by b.'.$dados['orderDown'].' DESC ';
            }
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            $stmt = $QueryBuilder->query($sql);
        }

        $purchases = array();
        while ($row = $stmt->fetch()) {
            $Billspay_DueDate = '';
            $PurchaseBillspay = $em->getRepository('PurchaseBillspay')->findOneBy(array('purchase' => $row['id']));
            if($PurchaseBillspay) {
                $Billspay = $PurchaseBillspay->getBillspay();
                $Billspay_status = $Billspay->getStatus();
                if($Billspay->getDueDate()) {
                    $Billspay_DueDate = $Billspay->getDueDate()->format('Y-m-d H:i:s');
                }
            }

            $Cadastro = 'Novo';
            $sql2 = "SELECT * from purchase where cards_id = ".$row['cards_id']." and purchase_date < '".$row['purchase_date']."' ";
            $stmt2 = $QueryBuilder->query($sql2);
            while ($row2 = $stmt2->fetch()) {
                $Cadastro = 'Recorrente';
            }

            $row['Cadastro'] = $Cadastro;
            $row['Billspay_DueDate'] = $Billspay_DueDate;
            $purchases[] = $row;
        }

        $dataset = array(
            'purchases' => $purchases
        );
        $response->setDataset($dataset);
    }
}
