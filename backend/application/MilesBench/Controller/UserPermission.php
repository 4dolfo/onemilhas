<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class UserPermission {

	public function load(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
		$sql = "select u FROM UserPermission u JOIN u.user s where s.partnerType not like '%D%' and s.status = 'Aprovado' ";
		$query = $em->createQuery($sql);
        $UserPermission = $query->getResult();
       
        $dataset = array();

		foreach ($UserPermission as $User) {

            $sundayIn = '';
            $mondayIn = '';
            $tuesdayIn = '';
            $wednesdayIn = '';
            $thursdayIn = '';
            $fridayIn = '';
            $saturdayIn = '';

            $sundayOut = '';
            $mondayOut = '';
            $tuesdayOut = '';
            $wednesdayOut = '';
            $thursdayOut = '';
            $fridayOut = '';
            $saturdayOut = '';

            if($User->getSundayIn()) {
                $sundayIn = $User->getSundayIn()->format('Y-m-d H:i:s');                
            }
            if($User->getMondayIn()) {
                $mondayIn = $User->getMondayIn()->format('Y-m-d H:i:s');
            }
            if($User->getTuesdayIn()) {
                $tuesdayIn = $User->getTuesdayIn()->format('Y-m-d H:i:s');
            }
            if($User->getWednesdayIn()) {
                $wednesdayIn = $User->getWednesdayIn()->format('Y-m-d H:i:s');
            }
            if($User->getThursdayIn()) {
                $thursdayIn = $User->getThursdayIn()->format('Y-m-d H:i:s');
            }
            if($User->getFridayIn()) {
                $fridayIn = $User->getFridayIn()->format('Y-m-d H:i:s');
            }
            if($User->getSaturdayIn()) {
                $saturdayIn = $User->getSaturdayIn()->format('Y-m-d H:i:s');
            }

            if($User->getSundayOut()) {
                $sundayOut = $User->getSundayOut()->format('Y-m-d H:i:s');
            }
            if($User->getMondayOut()) {
                $mondayOut = $User->getMondayOut()->format('Y-m-d H:i:s');
            }
            if($User->getTuesdayOut()) {
                $tuesdayOut = $User->getTuesdayOut()->format('Y-m-d H:i:s');
            }
            if($User->getWednesdayOut()) {
                $wednesdayOut = $User->getWednesdayOut()->format('Y-m-d H:i:s');
            }
            if($User->getThursdayOut()) {
                $thursdayOut = $User->getThursdayOut()->format('Y-m-d H:i:s');
            }
            if($User->getFridayOut()) {
                $fridayOut = $User->getFridayOut()->format('Y-m-d H:i:s');
            }
            if($User->getSaturdayOut()) {
                $saturdayOut = $User->getSaturdayOut()->format('Y-m-d H:i:s');
            }

			$dataset[] = array(
				'id' => $User->getId(),
				'userId' => $User->getUser()->getId(),
				'userName' => $User->getUser()->getName(),
				'purchase' => $User->getPurchase(),
                'wizardPurchase' => $User->getWizardPurchase(),                
				'sales' => $User->getSale(),
				'wizardSale' => $User->getWizardSale(),
				'milesBench' => $User->getMilesBench(),
				'financial' => $User->getFinancial(),
				'creditCard' => $User->getCreditCard(),
				'users' => $User->getUsers(),
                'changeSale' => $User->getChangeSale(),
                'changeMiles' => $User->getChangeMiles(),
                'commercial' => $User->getCommercial(),
                'permission' => $User->getPermission(),
                'pagseguro' => $User->getPagseguro(),
                'internRefund' => $User->getInternRefund(),
                'internCommercial' => $User->getInternCommercial(),
                'humanResources' => $User->getHumanResources(),
                'salePlansEdit' => $User->getSalePlansEdit(),
                'conference' => $User->getConference(),

                'onlineOnlineOrder' => $User->getOnlineOnlineOrder(),
                'onlineBalanceOrder' => $User->getOnlineBalanceOrder(),
                'onlineCardsInUse' => $User->getOnlineCardsInUse(),
                'purchaseProvider' => $User->getPurchaseProvider(),
                'purchasePaymentPruchase' => $User->getPurchasePaymentPruchase(),
                'purchaseEndPruchase' => $User->getPurchaseEndPruchase(),
                'purchasePruchases' => $User->getPurchasePruchases(),
                'purchaseCardsPendency' => $User->getPurchaseCardsPendency(),
                'saleClients' => $User->getSaleClients(),
                'saleBalanceClients' => $User->getSaleBalanceClients(),
                'saleFutureBoardings' => $User->getSaleFutureBoardings(),
                'saleRefundCancel' => $User->getSaleRefundCancel(),
                'saleRevertRefund' => $User->getSaleRevertRefund(),
                'wizardSaleEvent' => $User->getWizarSaleEvent(),

                'onVacation' => $User->getOnVacation(),
                'vacationEnd' => $User->getVacationEnd(),
                'isDozeTrintaESeis' => $User->getIsDozeTrintaESeis(),

                'sundayIn' => $sundayIn,
                'mondayIn' => $mondayIn,
                'tuesdayIn' => $tuesdayIn,
                'wednesdayIn' => $wednesdayIn,
                'thursdayIn' => $thursdayIn,
                'fridayIn' => $fridayIn,
                'saturdayIn' => $saturdayIn,
                'sundayOut' => $sundayOut,
                'mondayOut' => $mondayOut,
                'tuesdayOut' => $tuesdayOut,
                'wednesdayOut' => $wednesdayOut,
                'thursdayOut' => $thursdayOut,
                'fridayOut' => $fridayOut,
                'saturdayOut' => $saturdayOut
	        );
        }
        
        $response->setDataset($dataset);
    }

    public function loadDealers(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select b FROM Businesspartner b where b.partnerType like '%D%' ";
        $query = $em->createQuery($sql);
        $Businesspartner = $query->getResult();
        $dataset = array();

        foreach ($Businesspartner as $Partner) {

            $City = $Partner->getCity();
            if ($City) {
                $cityfullname = $City->getName() . ', ' . $City->getState();
                $cityname = $City->getName();
                $citystate = $City->getState();
            } else {
                $cityfullname = '';
                $cityname = '';
                $citystate = '';
            }

            $dataset[] = array(
                'id' => $Partner->getId(),
                'name' => $Partner->getName(),
                'registrationCode' => $Partner->getRegistrationCode(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $Partner->getAdress(),
                'email' => $Partner->getEmail(),
                'phoneNumber' => $Partner->getPhoneNumber(),
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientsDealer(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $agencys = $em->getRepository('Businesspartner')->findBy(array('dealer' => $dados['id']));
        foreach($agencys as $agency) {
            $dataset[] = array(
                'name' => $agency->getName()
            );
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $dados['id']));
        foreach($ClientsDealers as $reference) {
            $dataset[] = array(
                'name' => $reference->getClient()->getName()
            );
        }
        $response->setDataset($dataset);
    }

    public function saveDealer(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['clients'])) {
            $clients = $dados['clients'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try{

            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));

            $ClientsPartner = $em->getRepository('Businesspartner')->findBy(array('dealer' => $UserPartner->getId()));
            foreach ($ClientsPartner as $client) {
                $client->setDealer(NULL);
                $em->persist($client);
                $em->flush($client);
            }

            if($clients) {
                foreach ($clients as $client) {
                    $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $client['name']));
                    if($Businesspartner) {
                        $Businesspartner->setDealer($UserPartner);

                        $em->persist($Businesspartner);
                        $em->flush($Businesspartner);
                    }
                }
            }

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

    public function loadUsers(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();

		$sql = "select b FROM Businesspartner b WHERE b.partnerType like '%U%' ";
		$query = $em->createQuery($sql);
		$UserPermission = $query->getResult();
        $dataset = array();

		foreach ($UserPermission as $User) {
			$dataset[] = array(
				'id' => $User->getId(),
                'name' => $User->getName()
	        );
	    }
        $response->setDataset($dataset);
    }

    public function savePermission(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
        $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

        try{

            if(isset($dados['id'])){
                $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('id' => $dados['id']));
                $Businesspartner = $UserPermission->getUser();
            } else {
                $UserPermission = new \UserPermission();
				$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['userId']));
                $UserPermission->setUser($Businesspartner);
            }

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $changes = '';

            if(isset($dados['purchase']) && $dados['purchase'] != ''){
                if($dados['purchase'] != $UserPermission->getPurchase()){
                    $changes = $changes.' Compras: '.$dados['purchase'].';';
                }
                $UserPermission->setPurchase($dados['purchase']);
            }else{
                $UserPermission->setPurchase('false');
            }

            if(isset($dados['wizardPurchase']) && $dados['wizardPurchase'] != ''){
                if($dados['wizardPurchase'] != $UserPermission->getWizardPurchase()){
                    $changes = $changes.' Nova compra: '.$dados['wizardPurchase'].';';
                }
                $UserPermission->setWizardPurchase($dados['wizardPurchase']);
            }else{
                $UserPermission->setWizardPurchase('false');
            }

            if(isset($dados['sales']) && $dados['sales'] != ''){
                if($dados['sales'] != $UserPermission->getSale()){
                    $changes = $changes.' Vendas: '.$dados['sales'].';';
                }
                $UserPermission->setSale($dados['sales']);
            }else{
                $UserPermission->setSale('false');
            }

            if(isset($dados['wizardSale']) && $dados['wizardSale'] != ''){
                if($dados['wizardSale'] != $UserPermission->getWizardSale()){
                    $changes = $changes.' Nova Venda: '.$dados['wizardSale'].';';
                }
                $UserPermission->setWizardSale($dados['wizardSale']);
            } else{
                $UserPermission->setWizardSale('false');
            }

            if(isset($dados['wizardSaleEvent']) && $dados['wizardSaleEvent'] != '' ){
                if($dados['wizardSaleEvent'] != $UserPermission->getWizarSaleEvent()){
                    $changes = $changes.' Evento de Venda e pedidos: '.$dados['wizardSaleEvent'].';';
                }
                $UserPermission->setWizarSaleEvent($dados['wizardSaleEvent']);
            }else {
                $UserPermission->setWizarSaleEvent('false');
            }

            if(isset($dados['milesBench']) && $dados['milesBench'] != ''){
                if($dados['milesBench'] != $UserPermission->getMilesBench()){
                    $changes = $changes.' Banco de milhas: '.$dados['milesBench'].';';
                }
                $UserPermission->setMilesBench($dados['milesBench']);
            }else {
                $UserPermission->setMilesBench('false');
            }

            if(isset($dados['financial']) && $dados['financial'] != ''){
                if($dados['financial'] != $UserPermission->getFinancial()){
                    $changes = $changes.' Financeiro: '.$dados['financial'].';';
                }
                $UserPermission->setFinancial($dados['financial']);
            }else {
                $UserPermission->setFinancial('false');
            }

            if(isset($dados['pagseguro']) && $dados['pagseguro'] != ''){
                if($dados['pagseguro'] != $UserPermission->getPagseguro()){
                    $changes = $changes.' pagseguro: '.$dados['pagseguro'].';';
                }
                $UserPermission->setPagseguro($dados['pagseguro']);
            }else {
                $UserPermission->setPagseguro('false');
            }

            if(isset($dados['creditCard']) && $dados['creditCard'] != ''){
                if($dados['creditCard'] != $UserPermission->getCreditCard()){
                    $changes = $changes.' Cartoes de credito: '.$dados['creditCard'].';';
                }
                $UserPermission->setCreditCard($dados['creditCard']);
            } else {
                $UserPermission->setCreditCard('false');
            }

            if(isset($dados['users']) && $dados['users'] != ''){
                if($dados['users'] != $UserPermission->getUsers()){
                    $changes = $changes.' Usuarios: '.$dados['users'].';';
                }
                $UserPermission->setUsers($dados['users']);
            } else {
                $UserPermission->setUsers('false');
            }

            if(isset($dados['changeSale']) && $dados['changeSale'] != ''){
                if($dados['changeSale'] != $UserPermission->getChangeSale()){
                    $changes = $changes.' Alteracao de Vendas: '.$dados['changeSale'].';';
                }
                $UserPermission->setChangeSale($dados['changeSale']);
            } else {
                $UserPermission->setChangeSale('false');
            }

            if(isset($dados['changeMiles']) && $dados['changeMiles'] != ''){
                if($dados['changeMiles'] != $UserPermission->getChangeMiles()){
                    $changes = $changes.' Alteracao de Milhas: '.$dados['changeMiles'].';';
                }
                $UserPermission->setChangeMiles($dados['changeMiles']);
            } else {
                $UserPermission->setChangeMiles('false');
            }

            if(isset($dados['commercial']) && $dados['commercial'] != ''){
                if($dados['commercial'] != $UserPermission->getCommercial()){
                    $changes = $changes.' Alteracao de Comercial: '.$dados['commercial'].';';
                }
                $UserPermission->setCommercial($dados['commercial']);
            } else {
                $UserPermission->setCommercial('false');
            }

            if(isset($dados['permission']) && $dados['permission'] != ''){
                if($dados['permission'] != $UserPermission->getPermission()){
                    $changes = $changes.' Alteracao de Permissao: '.$dados['permission'].';';
                }
                $UserPermission->setPermission($dados['permission']);
            } else {
                $UserPermission->setPermission('false');
            }

            if(isset($dados['internRefund']) && $dados['internRefund'] != ''){
                if($dados['internRefund'] != $UserPermission->getInternRefund()){
                    $changes = $changes.' Estagio Reembolso de Permissao: '.$dados['internRefund'].';';
                }
                $UserPermission->setInternRefund($dados['internRefund']);
            } else {
                $UserPermission->setInternRefund('false');
            }

            if(isset($dados['internCommercial']) && $dados['internCommercial'] != ''){
                if($dados['internCommercial'] != $UserPermission->getInternCommercial()){
                    $changes = $changes.' Estagio Comercial de Permissao: '.$dados['internCommercial'].';';
                }
                $UserPermission->setInternCommercial($dados['internCommercial']);
            } else {
                $UserPermission->setInternCommercial('false');
            }

            if(isset($dados['humanResources']) && $dados['humanResources'] != ''){
                if($dados['humanResources'] != $UserPermission->getHumanResources()){
                    $changes = $changes.' Recursos Humanos: '.$dados['humanResources'].';';
                }
                $UserPermission->setHumanResources($dados['humanResources']);
            } else {
                $UserPermission->setHumanResources('false');
            }

            if(isset($dados['salePlansEdit']) && $dados['salePlansEdit'] != ''){
                if($dados['salePlansEdit'] != $UserPermission->getSalePlansEdit()){
                    $changes = $changes.' Recursos Humanos: '.$dados['salePlansEdit'].';';
                }
                $UserPermission->setSalePlansEdit($dados['salePlansEdit']);
            } else {
                $UserPermission->setSalePlansEdit('false');
            }

            if(isset($dados['conference']) && $dados['conference'] != ''){
                if($dados['conference'] != $UserPermission->getConference()){
                    $changes = $changes.' Recursos Humanos: '.$dados['conference'].';';
                }
                $UserPermission->setConference($dados['conference']);
            } else {
                $UserPermission->setConference('false');
            }

            if(isset($dados['onlineOnlineOrder']) && $dados['onlineOnlineOrder'] != ''){
                $UserPermission->setOnlineOnlineOrder($dados['onlineOnlineOrder']);
            } else {
                $UserPermission->setOnlineOnlineOrder('false');
            }

            if(isset($dados['onlineBalanceOrder']) && $dados['onlineBalanceOrder'] != ''){
                $UserPermission->setOnlineBalanceOrder($dados['onlineBalanceOrder']);
            } else {
                $UserPermission->setOnlineBalanceOrder('false');
            }

            if(isset($dados['onlineCardsInUse']) && $dados['onlineCardsInUse'] != ''){
                $UserPermission->setOnlineCardsInUse($dados['onlineCardsInUse']);
            } else {
                $UserPermission->setOnlineCardsInUse('false');
            }

            if(isset($dados['purchaseProvider']) && $dados['purchaseProvider'] != ''){
                $UserPermission->setPurchaseProvider($dados['purchaseProvider']);
            } else {
                $UserPermission->setPurchaseProvider('false');
            }

            if(isset($dados['purchasePaymentPruchase']) && $dados['purchasePaymentPruchase'] != ''){
                $UserPermission->setPurchasePaymentPruchase($dados['purchasePaymentPruchase']);
            } else {
                $UserPermission->setPurchasePaymentPruchase('false');
            }

            if(isset($dados['purchaseEndPruchase']) && $dados['purchaseEndPruchase'] != ''){
                $UserPermission->setPurchaseEndPruchase($dados['purchaseEndPruchase']);
            } else {
                $UserPermission->setPurchaseEndPruchase('false');
            }

            if(isset($dados['purchasePruchases']) && $dados['purchasePruchases'] != ''){
                $UserPermission->setPurchasePruchases($dados['purchasePruchases']);
            } else {
                $UserPermission->setPurchasePruchases('false');
            }

            if(isset($dados['purchaseCardsPendency']) && $dados['purchaseCardsPendency'] != ''){
                $UserPermission->setPurchaseCardsPendency($dados['purchaseCardsPendency']);
            } else {
                $UserPermission->setPurchaseCardsPendency('false');
            }

            if(isset($dados['saleClients']) && $dados['saleClients'] != ''){
                $UserPermission->setSaleClients($dados['saleClients']);
            } else {
                $UserPermission->setSaleClients('false');
            }

            if(isset($dados['saleBalanceClients']) && $dados['saleBalanceClients'] != ''){
                $UserPermission->setSaleBalanceClients($dados['saleBalanceClients']);
            } else {
                $UserPermission->setSaleBalanceClients('false');
            }

            if(isset($dados['saleFutureBoardings']) && $dados['saleFutureBoardings'] != ''){
                $UserPermission->setSaleFutureBoardings($dados['saleFutureBoardings']);
            } else {
                $UserPermission->setSaleFutureBoardings('false');
            }

            if(isset($dados['saleRefundCancel']) && $dados['saleRefundCancel'] != ''){
                $UserPermission->setSaleRefundCancel($dados['saleRefundCancel']);
            } else {
                $UserPermission->setSaleRefundCancel('false');
            }

            if(isset($dados['saleRevertRefund']) && $dados['saleRevertRefund'] != ''){
                $UserPermission->setSaleRevertRefund($dados['saleRevertRefund']);
            } else {
                $UserPermission->setSaleRevertRefund('false');
            }

            //Novas informações (férias e carga horária 12/36)
            if(isset($dados['isDozeTrintaESeis']) && $dados['isDozeTrintaESeis'] != ''){
                $UserPermission->setIsDozeTrintaESeis($dados['isDozeTrintaESeis']);
            } else {
                $UserPermission->setIsDozeTrintaESeis('false');
            }

            if(isset($dados['onVacation']) && $dados['onVacation'] != ''){
                $UserPermission->setOnVacation($dados['onVacation']);
            } else {
                $UserPermission->setOnVacation('false');
            }

            ////////////////////////////////////////////////////////////////////////////////
            // time control
            if(isset($dados['_sundayIn'])) {
                $UserPermission->setSundayIn(new \Datetime($dados['_sundayIn']));
            }
            if(isset($dados['_mondayIn'])) {
                $UserPermission->setMondayIn(new \Datetime($dados['_mondayIn']));
            }
            if(isset($dados['_tuesdayIn'])) {
                $UserPermission->setTuesdayIn(new \Datetime($dados['_tuesdayIn']));
            }
            if(isset($dados['_wednesdayIn'])) {
                $UserPermission->setWednesdayIn(new \Datetime($dados['_wednesdayIn']));
            }
            if(isset($dados['_thursdayIn'])) {
                $UserPermission->setThursdayIn(new \Datetime($dados['_thursdayIn']));
            }
            if(isset($dados['_fridayIn'])) {
                $UserPermission->setFridayIn(new \Datetime($dados['_fridayIn']));
            }
            if(isset($dados['_saturdayIn'])) {
                $UserPermission->setSaturdayIn(new \Datetime($dados['_saturdayIn']));
            }


            if(isset($dados['_sundayOut'])) {
                $UserPermission->setSundayOut(new \Datetime($dados['_sundayOut']));
            }
            if(isset($dados['_mondayOut'])) {
                $UserPermission->setMondayOut(new \Datetime($dados['_mondayOut']));
            }
            if(isset($dados['_tuesdayOut'])) {
                $UserPermission->setTuesdayOut(new \Datetime($dados['_tuesdayOut']));
            }
            if(isset($dados['_wednesdayOut'])) {
                $UserPermission->setWednesdayOut(new \Datetime($dados['_wednesdayOut']));
            }
            if(isset($dados['_thursdayOut'])) {
                $UserPermission->setThursdayOut(new \Datetime($dados['_thursdayOut']));
            }
            if(isset($dados['_fridayOut'])) {
                $UserPermission->setFridayOut(new \Datetime($dados['_fridayOut']));
            }
            if(isset($dados['_saturdayOut'])) {
                $UserPermission->setSaturdayOut(new \Datetime($dados['_saturdayOut']));
            }


            if(isset($dados['_vacationEnd'])) {
                $UserPermission->setVacationEnd(new \Datetime($dados['_vacationEnd']));
            }

            $em->persist($UserPermission);
            $em->flush($UserPermission);
            $SystemLog->setDescription("Permissao Alterada - Usuario: ".$Businesspartner->getName()." - ".$changes);
            $SystemLog->setLogType('PERMISSION');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            if($UserPartner->getIsMaster() != 'true'){
                $email = new Mail();
                $request = new \MilesBench\Request\Request();
                $resp = new \MilesBench\Request\Response();
                $email1 = 'adm@onemilhas.com.br';
                $request->setRow(
                    array('data' => array('subject' => '[MMS VIAGENS] - Notificação do sistema',
                                            'emailContent' => "Permissao Alterada - Usuario: ".$Businesspartner->getName().
                                            " - ".$changes."<br><br>Alterado por: ".$UserPartner->getName().
                                            "<br><br>Atenciosamente,<br>SRM-IT",
                                            'emailpartner' => array($email1) )));

                                            //'emailpartner' => array('paulo@voelegal.com.br') )));
                $email->SendMail($request, $resp);

                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::SUCCESS);
                $message->setText('Registro salvo com sucesso');
                $response->addMessage($message);
            } else {
                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::SUCCESS);
                $message->setText('Registro salvo com sucesso');
                $response->addMessage($message);
            }

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public static function isEmitter($partner_id) {
        $em = Application::getInstance()->getEntityManager();
        $partner = $em->getRepository('Businesspartner')->findOneBy( array( 'id' => $partner_id ) );
        if(!$partner || $partner->getIsMaster() == 'true') {
            return false;
        }

        $UserPermission = $em->getRepository('UserPermission')->findOneBy( array( 'user' => $partner_id ) );
        if(!$UserPermission) {
            return false;
        }

        if($UserPermission->getPurchase() == 'true' || $UserPermission->getWizardPurchase() == 'true' || $UserPermission->getSale() == 'true' || $UserPermission->getSaleClients() == 'true' || $UserPermission->getOnlineCardsInUse() == 'true' || $UserPermission->getCommercial() == 'true' ) {
            return false;
        }

        if($UserPermission->getWizardSale() == 'true') {
            return true;
        }
    }

    public function saveClientPermission(Request $request, Response $response){
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();
        $clientDealer = $dados['data'];
        //var_dump($clientDealer);die;
        $client = $dados['client'];      

        try {
           
            $User = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['userId']));
        
            //$UserDealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $clientDealer['name']));      
            
            for ($i=0; $i < count($clientDealer) ; $i++) { 
                print_r($clientDealer[$i]['read']);
                if(!$clientDealer[$i]['read']){
                    $em->getConnection()->beginTransaction();

                    $UserDealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $clientDealer[$i]['name']));                
                    $custumerLink = new \CustomersLink();
                    $custumerLink->setClientdealer($UserDealer);
                    $custumerLink->setUser($User);
        
                    $em->persist($custumerLink);
                    $em->flush($custumerLink);

                    $em->getConnection()->commit();
                }                
                            
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Conta cadastrada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }


    }

    public function loadCustomersLink(Request $request, Response $response){
        
        $dados = $request->getRow();
        $dados = $dados['data'];
        $em = Application::getInstance()->getEntityManager();

        $customerLink = $em->getRepository('CustomersLink')->findBy(array('user' => $dados['userId']));

        $dataset = array();

        foreach ($customerLink as $value) {


            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $value->getClientdealer()->getId()));

            $dataset[] = array(
				'id' => $BusinessPartner->getId(),
                'name' => $BusinessPartner->getName(),
                'type' => 'Dealer',
                'id_Dealer' => $value->getClientdealer()->getId(),
                'read' => 'true'
	        );
        }
        $response->setDataset($dataset);
    }
}
