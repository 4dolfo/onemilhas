<?php

namespace MilesBench\Controller\Dealer;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Main {

	public function loadClient(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $subClients = $em->getRepository('Businesspartner')->findBy(array('masterClient' => $dealers->getClient()->getId()));
            foreach ($subClients as $key => $value) {
                $clients .= $andD.$value->getId();
            }
            $clients .= $andD.$dealers->getClient()->getId();
        }

        $SubDealer = $em->getRepository('Businesspartner')->findBy(array('masterClient' => $UserPartner->getId(), 'partnerType' => 'U_D' ));
        foreach ($SubDealer as $dealer) {
            $subClients = $em->getRepository('Businesspartner')->findBy(array('dealer' => $dealer->getId()));
            foreach ($subClients as $key => $value) {
                $clients = $clients.$andD.$value->getId();
                $andD = ',';
            }
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";
		
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select b.*, " .
            " (select MAX(s.issue_date) from sale s where s.client_id = b.id) as last_emission, " .
            " (select COUNT(s.id) from sale s where s.client_id = b.id) as countd, " .
            " d.name as dealer_name " .
            " FROM businesspartner b LEFT JOIN city c on c.id = b.city_id LEFT JOIN businesspartner d on d.id = b.dealer where b.partner_type like '%C%' and b.status<>'Arquivado' " . $dealer;

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." b.id like '%".$dados['searchKeywords']."%' or "
                ." b.company_name like '%".$dados['searchKeywords']."%' or "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registration_code like '%".$dados['searchKeywords']."%' or "
                ." c.name like '%".$dados['searchKeywords']."%' or "
                ." c.state like '%".$dados['searchKeywords']."%' or "
                ." b.adress like '%".$dados['searchKeywords']."%' or "
                ." b.email like '%".$dados['searchKeywords']."%' or "
                ." b.phone_number like '%".$dados['searchKeywords']."%' or "
                ." b.phone_number2 like '%".$dados['searchKeywords']."%' or "
                ." b.phone_number3 like '%".$dados['searchKeywords']."%' or "
                ." b.status like '%".$dados['searchKeywords']."%' or "
                ." b.payment_type like '%".$dados['searchKeywords']."%' or "
                ." b.payment_days like '%".$dados['searchKeywords']."%' or "
                ." b.description like '%".$dados['searchKeywords']."%' or "
                ." b.registration_code_check like '%".$dados['searchKeywords']."%' or "
                ." b.adress_check like '%".$dados['searchKeywords']."%' or "
                ." b.credit_description like '%".$dados['searchKeywords']."%' or "
                ." b.partner_limit like '%".$dados['searchKeywords']."%' or "
                ." b.billing_period like '%".$dados['searchKeywords']."%' or "
                ." b.type_society like '%".$dados['searchKeywords']."%' or "
                ." b.mulct like '%".$dados['searchKeywords']."%' or "
                ." b.adress_number like '%".$dados['searchKeywords']."%' or "
                ." b.adress_complement like '%".$dados['searchKeywords']."%' or "
                ." b.zip_code like '%".$dados['searchKeywords']."%' or "
                ." b.adress_district like '%".$dados['searchKeywords']."%' or "
                ." b.account like '%".$dados['searchKeywords']."%' or "
                ." b.financial_contact like '%".$dados['searchKeywords']."%' or "
                ." b.finnancial_email like '%".$dados['searchKeywords']."%' or "
                ." b.interest like '%".$dados['searchKeywords']."%' or "
                ." b.adress_finnancial like '%".$dados['searchKeywords']."%' or "
                ." b.adress_complement_finnancial like '%".$dados['searchKeywords']."%' or "
                ." b.adress_district_finnancial like '%".$dados['searchKeywords']."%' or "
                ." b.adress_number_finnancial like '%".$dados['searchKeywords']."%' or "
                ." b.zip_code_finnancial like '%".$dados['searchKeywords']."%' ) ";

                $sql .= $where;
		}
		
		if($UserPartner) {
			$content = '<br>Ola<br>';
			$content .= 'Tela: Clientes';
			if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
				$content .= '<br>Pesquisa: ' . $dados['searchKeywords'];
			}
            $email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' => $content,
				'partner' => $email1,
				'from' => $email1,
				'subject' => 'BBB - MMS - Representante - Clientes ' . $UserPartner->getName(),
				'type' => '',
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);

			$SystemLog = new \SystemLog();
			$SystemLog->setIssueDate(new \Datetime());
			$SystemLog->setDescription($content);
			$SystemLog->setLogType('BBB');
			$SystemLog->setBusinesspartner($UserPartner);

			$em->persist($SystemLog);
			$em->flush($SystemLog);
		}

        $sql .= ' OR b.master_client in (select x.id from businesspartner x where x.dealer = '.$UserPartner->getId().')';

		// order
        $orderBy = ' order by b.name ASC ';
        if(isset($dados['order']) && $dados['order'] != '') {
            if( $dados['order'] == 'last_emission' || $dados['order'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['order'] . ' ASC ';
            } else {
                $orderBy = ' order by b.' . \MilesBench\Controller\Businesspartner::from_camel_case($dados['order']) .' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if( $dados['orderDown'] == 'last_emission' || $dados['orderDown'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['orderDown'] . ' DESC ';
            } else {
                $orderBy = ' order by b.' . \MilesBench\Controller\Businesspartner::from_camel_case($dados['orderDown']) .' DESC ';
            }
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            $stmt = $QueryBuilder->query($sql);
        }

		$clients = array();
		while ($row = $stmt->fetch()) {

			if ($row['city_id'] != NULL) {
                $City = $em->getRepository('City')->findOneBy(array( 'id' => $row['city_id'] ));
                $cityfullname = $City->getName() . ', ' . $City->getState();
                $cityname = $City->getName();
                $citystate = $City->getState();
            } else {
                $cityfullname = '';
                $cityname = '';
                $citystate = '0';
			}
			
			if($row['last_emission'] == NULL) {
                $row['last_emission'] = '';
            }

			$clients[] = array(
				'id' => $row['id'],
				'name' => $row['name'],
				'city' => $cityname,
				'state' => $citystate,
				'cityfullname' => $cityfullname,
				'partnerType' => $row['partner_type'],
				'paymentDays' => $row['payment_days'],
				'partnerLimit' => (float)$row['partner_limit'],
				'status' => $row['status'],
				'paymentType' => $row['payment_type'],
				'last_emission' => $row['last_emission'],
                'countd' => (float)$row['countd'],
                'company_name' => $row['company_name'],
                'registrationCode' => $row['registration_code'],
                'adress' => $row['adress'],
                'email' => $row['email'],
                'phoneNumber' => $row['phone_number'],
                'phoneNumber2' => $row['phone_number2'],
                'phoneNumber3' => $row['phone_number3'],
                'description' => $row['description'],
                'creditAnalysis' => $row['credit_analysis'],
                'registrationCodeCheck' => $row['registration_code_check'],
                'adressCheck' => $row['adress_check'],
                'creditDescription' => $row['credit_description'],
                'billingPeriod' => $row['billing_period'],
                'birthdate' => $row['birthdate'],
                'typeSociety' => $row['type_society'],
                'mulct' => (float)$row['mulct'],
                'registerDate' => $row['register_date'],
                'adressNumber' => $row['adress_number'],
                'adressComplement' => $row['adress_complement'],
                'zipCode' => $row['zip_code'],
                'adressDistrict' => $row['adress_district'],
                'account' => $row['account'],
                'financialContact' => $row['financial_contact'],
                'limitMargin' => (float) $row['limit_margin'],
                'finnancialEmail' => $row['finnancial_email'],
                'daysToBoarding' => (float)$row['days_to_boarding'],
                'adressFinnancial' => $row['adress_finnancial'],
                'adressComplementFinnancial' => $row['adress_complement_finnancial'],
                'adressDistrictFinnancial' => $row['adress_district_finnancial'],
                'adressNumberFinnancial' => $row['adress_number_finnancial'],
                'zipCodeFinnancial' => $row['zip_code_finnancial'],
                'contact' => $row['contact']
			);
		}

		if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
			$where = " and ( "
				." b.id like '%".$dados['searchKeywords']."%' or "
				." b.companyName like '%".$dados['searchKeywords']."%' or "
				." b.name like '%".$dados['searchKeywords']."%' or "
				." b.registrationCode like '%".$dados['searchKeywords']."%' or "
				." c.name like '%".$dados['searchKeywords']."%' or "
				." c.state like '%".$dados['searchKeywords']."%' or "
				." b.adress like '%".$dados['searchKeywords']."%' or "
				." b.email like '%".$dados['searchKeywords']."%' or "
				." b.phoneNumber like '%".$dados['searchKeywords']."%' or "
				." b.phoneNumber2 like '%".$dados['searchKeywords']."%' or "
				." b.phoneNumber3 like '%".$dados['searchKeywords']."%' or "
				." b.status like '%".$dados['searchKeywords']."%' or "
				." b.paymentType like '%".$dados['searchKeywords']."%' or "
				." b.paymentDays like '%".$dados['searchKeywords']."%' or "
				." b.description like '%".$dados['searchKeywords']."%' or "
				." b.registrationCodeCheck like '%".$dados['searchKeywords']."%' or "
				." b.adressCheck like '%".$dados['searchKeywords']."%' or "
				." b.creditDescription like '%".$dados['searchKeywords']."%' or "
				." b.partnerLimit like '%".$dados['searchKeywords']."%' or "
				." b.billingPeriod like '%".$dados['searchKeywords']."%' or "
				." b.typeSociety like '%".$dados['searchKeywords']."%' or "
				." b.mulct like '%".$dados['searchKeywords']."%' or "
				." b.adressNumber like '%".$dados['searchKeywords']."%' or "
				." b.adressComplement like '%".$dados['searchKeywords']."%' or "
				." b.zipCode like '%".$dados['searchKeywords']."%' or "
				." b.adressDistrict like '%".$dados['searchKeywords']."%' or "
				." b.account like '%".$dados['searchKeywords']."%' or "
				." b.financialContact like '%".$dados['searchKeywords']."%' or "
				." b.finnancialEmail like '%".$dados['searchKeywords']."%' or "
				." b.interest like '%".$dados['searchKeywords']."%' or "
				." b.adressFinnancial like '%".$dados['searchKeywords']."%' or "
				." b.adressComplementFinnancial like '%".$dados['searchKeywords']."%' or "
				." b.adressDistrictFinnancial like '%".$dados['searchKeywords']."%' or "
				." b.adressNumberFinnancial like '%".$dados['searchKeywords']."%' or "
				." b.zipCodeFinnancial like '%".$dados['searchKeywords']."%' ) ";

			$sql = "select COUNT(b) as quant FROM Businesspartner b JOIN b.city c where b.partnerType like '%C%' and b.status<>'Arquivado' ".$dealer.$where;
		} else {
			$sql = "select COUNT(b) as quant FROM Businesspartner b where b.partnerType like '%C%' and b.status<>'Arquivado' ".$dealer;
        }
        $where = ' OR b.masterClient in (select x.id from businesspartner x where x.dealer = '.$UserPartner->getId().')';
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'clients' => $clients,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }
    
	public function loadClientsBalance(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		if(!isset($dados['data'])) {
			$dados['data'] = array();
		}
		if(!isset($dados['data']['days'])) {
			$dados['data']['days'] = 7;
		}
		$daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
		
		$sql = "select COUNT(s.id) as emissions, MAX(s.client) as client from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer." group by s.client";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset = array();
		foreach($Sales as $sale){
			$client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale['client']));
			$dataset[] = array(
				'data' => $sale['emissions'],
				'label' => $client->getName().' - '.$sale['emissions']
			);
		}
		$response->setDataset($dataset);
	}

	public function loadAirlineBalance(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		if(!isset($dados['data'])) {
			$dados['data'] = array();
		}
		if(!isset($dados['data']['days'])) {
			$dados['data']['days'] = 7;
		}
		$daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
		
		$sql = "select COUNT(s.id) as emissions, MAX(s.airline) as airline from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer." group by s.airline";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset = array();
		foreach($Sales as $sale){
			$Airline = $em->getRepository('Airline')->findOneBy(array('id' => $sale['airline']));
			$airlineName = '';
			if($Airline) {
				$airlineName = $Airline->getName();
			}
			$dataset[] = array(
				'data' => $sale['emissions'],
				'label' => $airlineName
			);
		}
		$response->setDataset($dataset);
	}
	
	public function loadAirlineMiles(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		if(!isset($dados['data'])) {
			$dados['data'] = array();
		}
		if(!isset($dados['data']['days'])) {
			$dados['data']['days'] = 7;
		}
		$daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
		
		$sql = "select SUM(s.milesUsed) as emissions, MAX(s.airline) as airline from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer." group by s.airline";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset = array();
		foreach($Sales as $sale){
			$Airline = $em->getRepository('Airline')->findOneBy(array('id' => $sale['airline']));
			$airlineName = '';
			if($Airline) {
				$airlineName = $Airline->getName();
			}
			$dataset[] = array(
				'data' => $sale['emissions'],
				'label' => $airlineName
			);
		}
		$response->setDataset($dataset);
	}

	public function loadClientsTotal(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
		$dataset = array();
		
		$sql = "select COUNT(DISTINCT s.client) as emissions, MAX(s.client) as client from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer;
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		foreach($Sales as $sale){
			$dataset[] = array(
				'data' => $sale['emissions'],
				'label' => 'EMITIRAM - '.$sale['emissions']
			);
		}

		$sql = "select COUNT(DISTINCT s.client) as clients from Sale s JOIN s.client b where b.partnerType = 'C' and b.status<>'Arquivado' and b.paymentType='Antecipado' and s.issueDate < '".$daysAgo->format('Y-m-d')."' ".$dealer;
		$query = $em->createQuery($sql);
		$Businesspartner = $query->getResult();

		foreach($Businesspartner as $partner){
			$dataset[] = array(
				'data' => $partner['clients'],
				'label' => 'N emitiram - Antecipado - '.$partner['clients']
			);
		}

		$sql = "select COUNT(DISTINCT s.client) as clients from Sale s JOIN s.client b where b.partnerType = 'C' and b.status<>'Arquivado' and b.paymentType='Boleto' and s.issueDate < '".$daysAgo->format('Y-m-d')."' ".$dealer;
		$query = $em->createQuery($sql);
		$Businesspartner = $query->getResult();

		foreach($Businesspartner as $partner){
			$dataset[] = array(
				'data' => $partner['clients'],
				'label' => 'N emitiram - Boleto - '.$partner['clients']
			);
		}

		$sql = "select COUNT(DISTINCT s.client) as clients from Sale s JOIN s.client b where b.partnerType = 'C' and b.status<>'Arquivado' and b.paymentType is NULL and s.issueDate < '".$daysAgo->format('Y-m-d')."' ".$dealer;
		$query = $em->createQuery($sql);
		$Businesspartner = $query->getResult();

		foreach($Businesspartner as $partner){
			$dataset[] = array(
				'data' => $partner['clients'],
				'label' => 'N emitiram - Outros - '.$partner['clients']
			);
		}
		$response->setDataset($dataset);
	}

	public function loadClientsTotalChart(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$dataset = array();
		for ($i=12; $i >= 0; $i--) { 
			$monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('first day of this month');
			$monthAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('last day of this month');

			$sql = "select COUNT(s.id) as sales FROM Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' ".$dealer;
			$query = $em->createQuery($sql);
			$Sales = $query->getResult();

			foreach($Sales as $Sale){
				$dataset[] = array(
					'sales' => $Sale['sales'],
					'month' => $monthsAgo->format('Y-m')
				);
			}
		}
		$response->setDataset($dataset);
	}

	public function loadClientsCancelSales(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
		
		$sql = "select COUNT(s.id) as emissions, MAX(s.client) as client from Sale s JOIN s.client b where s.issueDate >= '".$daysAgo->format('Y-m-d')."' and (s.status='Cancelamento Solicitado' or s.status='Cancelamento Efetivado' or s.status='Cancelamento Nao Solicitado') ".$dealer." group by s.client";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset = array();
		foreach($Sales as $sale){
			$client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale['client']));
			$dataset[] = array(
				'data' => $sale['emissions'],
				'label' => $client->getName().' - '.$sale['emissions']
			);
		}
		$response->setDataset($dataset);
	}

	public function loadClientsDaily(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$dataset = array();
		for ($i=14; $i >= 0; $i--) { 
			$monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' day');
			$monthAgo = (new \DateTime())->modify('today')->modify('-'.($i-1).' day');
		
			$sql = "select COUNT(s) as emissions from Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' ".$dealer." ";
			$query = $em->createQuery($sql);
			$emissions = $query->getResult();

			$sql = "select COUNT(DISTINCT s.client) as emissions from Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' ".$dealer." ";
			$query = $em->createQuery($sql);
			$Sales = $query->getResult();

			$dataset[] = array(
				'sales' => $emissions[0]['emissions'],
				'clients' => $Sales[0]['emissions'],
				'date' => $monthsAgo->format('Y-m-d')
			);
		}
		$response->setDataset($dataset);
	}

	public function loadClientsStates(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
		
		$sql = "select COUNT(s.id) as emissions, MAX(c.state) as state  from Sale s JOIN s.client b LEFT JOIN b.city c where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by c.state";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset = array();
		foreach($Sales as $sale){
			$dataset[] = array(
				'data' => $sale['emissions'],
				'label' => $sale['state'].' - '.$sale['emissions']
			);
		}
		$response->setDataset($dataset);
	}

	public function loadCountClientesPerStates(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
		
		$sql = "select COUNT(DISTINCT s.client) as emissions, MAX(c.state) as state  from Sale s JOIN s.client b LEFT JOIN b.city c where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by c.state";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset = array();
		foreach($Sales as $sale){
			$dataset[] = array(
				'data' => $sale['emissions'],
				'label' => $sale['state'].' - '.$sale['emissions']
			);
		}
		$response->setDataset($dataset);
	}

	public function loadTopParts(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }
        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$daysAgo = (new \DateTime())->modify('today')->modify('-30 day');

		$sql = "select MAX(s.airportFrom) as airport, COUNT(s.airportFrom) as quant from Sale s JOIN s.client b where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by s.airportFrom HAVING COUNT(s.airportFrom) > 1 order by quant DESC ";
		$query = $em->createQuery($sql);
		$Sales = $query->setMaxResults(10)->getResult();

		$airportFrom = array();
		foreach ($Sales as $airport) {
			$airportName = '';
			$Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airport']));
			if($Airport) {
				$airportName = $Airport->getCode();
			}
			$airportFrom[] = array(
				'airport' => $airportName,
				'count' => $airport['quant']
			);
		}

		$sql = "select MAX(s.airportTo) as airport, COUNT(s.airportTo) as quant from Sale s JOIN s.client b where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by s.airportTo HAVING COUNT(s.airportTo) > 1 order by quant DESC ";
		$query = $em->createQuery($sql);
		$Sales = $query->setMaxResults(10)->getResult();

		$airportTo = array();
		foreach ($Sales as $airport) {
			$airportName = '';
			$Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airport']));
			if($Airport) {
				$airportName = $Airport->getCode();
			}
			$airportTo[] = array(
				'airport' => $airportName,
				'count' => $airport['quant']
			);
		}

		$sql = "select MAX(s.airportTo) as airportTo, MAX(s.airportFrom) as airportFrom, COUNT(s.id) as quant from Sale s JOIN s.client b where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by s.airportTo, s.airportFrom order by quant DESC ";
		$query = $em->createQuery($sql);
		$Sales = $query->setMaxResults(20)->getResult();

		$trechos = array();
		foreach ($Sales as $airport) {
			$airportFromName = '';
			$airportToName = '';
			$Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airportTo']));
			if($Airport) {
				$airportFromName = $Airport->getCode();
			}
			$Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airportFrom']));
			if($Airport) {
				$airportToName = $Airport->getCode();
			}
			$trechos[] = array(
				'airport' => $airportFromName.'-'.$airportToName,
				'count' => $airport['quant']
			);
		}

		$dataset = array(
			'from' => $airportFrom,
			'to' => $airportTo,
			'trechos' => $trechos
		);
		$response->setDataset($dataset);
	}

	public function loadOrder(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }
        $dealer = " where ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

		$sql = "select s as sale, (case when s.status = 'Emitido' then 1 else 2 end) ordenation FROM Sale s JOIN s.client b ".$dealer." ORDER BY ordenation, s.id DESC";
		$query = $em->createQuery($sql);
		$order = $query->getResult();

		$dataset = array();

        foreach($order as $sale) {
            $item = $sale['sale'];
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

            $commission = ((float)$item->getAmountPaid() - (float)$item->getTax() - (float)$item->getDuTax());
            $status = 'Emitido';
            if($item->getStatus() == 'Reembolso Solicitado' || $item->getStatus() == 'Reembolso Pagante Solicitado' || $item->getStatus() == 'Reembolso Confirmado' || $item->getStatus() == 'Reembolso CIA' || $item->getStatus() == 'Reembolso Pendente' || $item->getStatus() == 'Reembolso Nao Solicitado' || $item->getStatus() == 'Reembolso Perdido') {
                $status = 'Reembolso';
                $commission = 0;
            }
            if($item->getStatus() == 'Remarcação Solicitado' || $item->getStatus() == 'Remarcação Confirmado') {
                $status = 'Remarcação';
            }
            if($item->getStatus() == 'Cancelamento Solicitado' || $item->getStatus() == 'Cancelamento Efetivado' || $item->getStatus() == 'Cancelamento Nao Solicitado' || $item->getStatus() == 'Cancelamento Pendente') {
                $status = 'Cancelamento';
                $commission = 0;

                // $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy(array('sale' => $item->getId()));
                // $change = false;
                // foreach ($SaleBillsreceive as $BillsReceives) {
                // 	if($BillsReceives->getBillsreceive()->getAccountType() == 'Cancelamento') {
                // 		$change = true;
                // 		$commission = (float)$BillsReceives->getBillsreceive()->getActualValue();
                // 	}
                // }
                // if(!$change) {
                // 	$commission = 0;
                // }
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
            
            $duTax = (float)$item->getDuTax();
            $tax = (float)$item->getTax();
            $amountPaid = (float)$item->getAmountPaid();
            if($item->getSaleByThird() == 'Y') {
                $commission = 0;
                if($duTax > 0) {
                    $amountPaid -= $duTax;
                }
                if($tax > 0) {
                    $amountPaid -= $tax;
                }
                $duTax = 0;
                $tax = 0;
            }

            $refundDate = '';
            if($item->getRefundDate()) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }

            $dataset[] = array(
                'airline' => $airline,
                'from' => $airportFrom,
                'to' => $airportTo,
                'amountPaid' => $amountPaid,
                'paxName' => $paxNmae,
                'client' => $item->getClient()->getName(),
                'company_name' => $item->getClient()->getCompanyName(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'status' => $status,
                'tax' => $tax,
                'duTax' => $duTax,
                'commission' => $commission,
                'flightLocator' => $item->getFlightLocator(),
                'refundDate' => $refundDate
            );
        }

		$response->setDataset($dataset);
	}

	public function loadSaleByFilter(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$dealer = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $subClients = $em->getRepository('Businesspartner')->findBy(array('masterClient' => $dealers->getClient()->getId()));
            foreach ($subClients as $key => $value) {
                $clients .= $andD.$value->getId();
            }
            $clients .= $andD.$dealers->getClient()->getId();
        }

        $SubDealer = $em->getRepository('Businesspartner')->findBy(array('masterClient' => $UserPartner->getId(), 'partnerType' => 'U_D' ));
        foreach ($SubDealer as $dealer) {
            $subClients = $em->getRepository('Businesspartner')->findBy(array('dealer' => $dealer->getId()));
            foreach ($subClients as $key => $value) {
                $clients = $clients.$andD.$value->getId();
                $andD = ',';
            }
        }

        $dealer = " ( x.dealer = '".$UserPartner->getId()."' or x.id in (".$clients.") ) ";

		$dados = $request->getRow();
		$sql = "select s as sale, (case when s.status = 'Emitido' then 1 else 2 end) ordenation FROM Sale s JOIN s.client x JOIN s.pax p JOIN s.airline a ";
		$whereClause = ' WHERE '.$dealer;
		$and = ' AND ';
		$orderBy = " ORDER BY ordenation, s.id DESC";

		if (isset($dados['data'])){
			$dados = $dados['data'];    
		}

		if (isset($dados['airline']) && !($dados['airline'] == '')) {
			$whereClause = $whereClause.$and." a.name = '".$dados['airline']."' ";
			$and = ' AND ';
		};

		if (isset($dados['client']) && !($dados['client'] == '')) {
			$whereClause = $whereClause.$and. " x.name = '".$dados['client']."' ";
			$and = ' AND ';
		};

		if (isset($dados['flightLocator']) && !($dados['flightLocator'] == '')) {
			$whereClause = $whereClause.$and. " s.flightLocator like '%".$dados['flightLocator']."%' ";
			$and = ' AND ';
		};

		if (isset($dados['_saleDateFrom']) && $dados['_saleDateFrom'] != '') {
            $_saleDateFrom = new \DateTime($dados['_saleDateFrom']);
            $whereClause = $whereClause.$and. " s.issueDate >= '".$dados['_saleDateFrom']."' ";
			$and = ' AND ';
        };
        
		if (isset($dados['_saleDateTo']) && $dados['_saleDateTo'] != '') {
            $_saleDateTo = (new \DateTime($dados['_saleDateTo']))->modify('+1 day');
            $whereClause = $whereClause.$and. " s.issueDate <= '".(new \DateTime($dados['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
			$and = ' AND ';
        } else {
            $_saleDateTo = (new \DateTime())->modify('+1 day');
        };

        if (isset($dados['_saleDateFrom']) && $dados['_saleDateFrom'] != '') {
            if(isset($dados['_saleDateTo'])) {
                $whereClause .=  " OR ( $dealer AND s.refundDate >= '".$dados['_saleDateFrom']."' ";
                $whereClause .= " AND s.refundDate <= '".(new \DateTime($dados['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
                $whereClause .= " AND s.status in ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Confirmado', 'Reembolso CIA', 'Reembolso Pendente', 'Reembolso Nao Solicitado', 'Reembolso Perdido') )";
            }
        }

		if (isset($dados['_boardingDateFrom']) && !($dados['_boardingDateFrom'] == '')) {
			$whereClause = $whereClause.$and. " s.boardingDate >= '".$dados['_boardingDateFrom']."' ";
			$and = ' AND ';
		};

		if (isset($dados['_boardingDateTo']) && !($dados['_boardingDateTo'] == '')) {
			$whereClause = $whereClause.$and. " s.boardingDate <= '".(new \DateTime($dados['_boardingDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		if (isset($dados['paxName']) && !($dados['paxName'] == '')) {
			$whereClause = $whereClause.$and. " p.name like '%".$dados['paxName']."%' ";
			$and = ' AND ';
		};

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};
        
        $sql .= ' OR x.masterClient in (select y.id from businesspartner y where y.dealer = '.$UserPartner->getId().')';
        
        $query = $em->createQuery($sql.$orderBy);
        $order = $query->getResult();

		$dataset = array();
        foreach($order as $sale) {
            $item = $sale['sale'];
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

            $commission = ((float)$item->getAmountPaid() - (float)$item->getTax() - (float)$item->getDuTax());
            $status = 'Emitido';
            if($item->getStatus() == 'Reembolso Solicitado' || $item->getStatus() == 'Reembolso Pagante Solicitado' || $item->getStatus() == 'Reembolso Confirmado' || $item->getStatus() == 'Reembolso CIA' || $item->getStatus() == 'Reembolso Pendente' || $item->getStatus() == 'Reembolso Nao Solicitado' || $item->getStatus() == 'Reembolso Perdido') {
                $status = 'Reembolso';

                if($item->getRefundDate() && isset($_saleDateFrom) && isset($_saleDateTo)) {
                    if($_saleDateFrom < $item->getRefundDate() && $item->getRefundDate() < $_saleDateTo && $item->getIssueDate() > $_saleDateFrom) {
                        $commission = 0;
                    } else if($item->getRefundDate() < $_saleDateTo) {
                        $commission = $commission * -1;
                    }
                }

            }
            if($item->getStatus() == 'Remarcação Solicitado' || $item->getStatus() == 'Remarcação Confirmado') {
                $status = 'Remarcação';
            }
            if($item->getStatus() == 'Cancelamento Solicitado' || $item->getStatus() == 'Cancelamento Efetivado' || $item->getStatus() == 'Cancelamento Nao Solicitado' || $item->getStatus() == 'Cancelamento Pendente') {
                $status = 'Cancelamento';
                $commission = 0;
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

            $duTax = (float)$item->getDuTax();
            $tax = (float)$item->getTax();
            $amountPaid = (float)$item->getAmountPaid();
            $baggage_price = (float)$item->getBaggagePrice();
            if($item->getSaleByThird() == 'Y' && $item->getMilesUsed() == 0) {
                if($item->getProviderSaleByThird()->getId() != 5715 && $item->getProviderSaleByThird()->getId() != 17105) {
                    $paxNmae .= ' - Venda Pagante';
                    $commission = 0;
                    if($duTax > 0) {
                        $amountPaid -= $duTax;
                    }
                    if($tax > 0) {
                        $amountPaid -= $tax;
                    }
                    if($baggage_price > 0) {
                        $amountPaid -= $baggage_price;
                    }
                    $duTax = 0;
                    $tax = 0;
                }
            }

            $refundDate = '';
            if($item->getRefundDate()) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }

            $origin = '';
            $content = '';
            $medium = '';
            $comissao_vendas = 0;
            $dealer_b2c = '';
            $dealer_b2c_tipo_comissao = '';
            $cupom = '';
            if($item->getExternalId()) {
                $OnlineOrder = $em->getRepository('OnlineOrder')->find($item->getExternalId());
                if($OnlineOrder) {
                    if($OnlineOrder->getUtm()) {
                        $data = json_decode($OnlineOrder->getUtm(), true);
                        if(isset($data['utm_source'])) {
                            $origin = $data['utm_source'];
                            $CampanhasB2c = $em->getRepository('CampanhasB2c')->findOneBy(array('codigo' => $data['utm_source']));
                            if($CampanhasB2c) {
                                $comissao_vendas = $CampanhasB2c->getDealer()->getCommission();
                                $dealer_b2c = $CampanhasB2c->getDealer()->getName();
                                $dealer_b2c_tipo_comissao = $CampanhasB2c->getDealer()->getSystemName();
                            }
                        }
                        if(isset($data['utm_source'])) {
                            $content = $data['utm_content'];
                        }
                        if(isset($data['utm_medium'])) {
                            $medium = $data['utm_medium'];
                        }
                    }
                    if($OnlineOrder->getCupom()) {
                        $cupom = $OnlineOrder->getCupom();
                    }
                    if($dealer_b2c_tipo_comissao == '') {
                        $dealerCupom = $em->getRepository('CuponsB2c')->findOneBy(array('nome' => $cupom));
                        if($dealerCupom) {
                            $dealer_b2c = $dealerCupom->getDealer()->getName();
                            $dealer_b2c_tipo_comissao = $dealerCupom->getDealer()->getSystemName();
                        }
                    }
                    if($dealer_b2c_tipo_comissao == '') {
                        $dealer_b2c_tipo_comissao = $item->getClient()->getClientMarkupType();
                        if($commission > 0) {
                            if($dealer_b2c_tipo_comissao == 'D') {
                                $comissao_vendas = (float)$item->getClient()->getCommission();
                            } else if($dealer_b2c_tipo_comissao == 'P') {
                                $dealer_b2c_tipo_comissao = '%';
                                $comissao_vendas = ($commission / 100) * (float)$item->getClient()->getCommission();
                            }
                        }
                    }
                }
            }

            $dataset[] = array(
                'airline' => $airline,
                'from' => $airportFrom,
                'to' => $airportTo,
                // 'amountPaid' => $amountPaid,
                'paxName' => $paxNmae,
                'company_name' => $item->getClient()->getCompanyName(),
                'milesOriginal' => (int)$item->getMilesOriginal(),
                'client' => $item->getClient()->getName(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'status' => $status,
                'tax' => $tax,
                'duTax' => $duTax,
                'commission' => $commission,
                'flightLocator' => $item->getFlightLocator(),
                'baggage_price' => $baggage_price,
                'refundDate' => $refundDate,
                'externalId' => $item->getExternalId(),
                'origin' => $origin,
                'content' => $content,
                'medium' => $medium,
                'comissao_vendas' => $comissao_vendas,
                'dealer_b2c' => $dealer_b2c,
                'dealer_b2c_tipo_comissao' => $dealer_b2c_tipo_comissao,
                'cupom' => $cupom
            );
        }
		$response->setDataset($dataset);
	}

	public function loadClientsNames(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

		$dealer = '';
		$and = '';

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

        $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $UserPartner->getId()));
        $clients = '0';
        $andD = ',';
        foreach ($ClientsDealers as $dealers) {
            $clients = $clients.$andD.$dealers->getClient()->getId();
            $andD = ',';
        }

        $SubDealer = $em->getRepository('Businesspartner')->findBy(array('masterClient' => $UserPartner->getId(), 'partnerType' => 'U_D' ));
        foreach ($SubDealer as $dealer) {
            $subClients = $em->getRepository('Businesspartner')->findBy(array('dealer' => $dealer->getId()));
            foreach ($subClients as $key => $value) {
                $clients = $clients.$andD.$value->getId();
                $andD = ',';
            }
        }

        $dealer = " and ( b.dealer = '".$UserPartner->getId()."' or b.id in (".$clients.") ) ";

        $dataset = array();
        $sql = " select b.* from businesspartner b where b.partner_type = 'C' and b.status <> 'Arquivado' ".$dealer;
        $stmt = $QueryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $dataset[] = array(
                'id' => $row['id'],
                'company_name' => $row['company_name'],
                'name' => $row['name'],
                'registrationCode' => $row['registration_code'],
                'email' => $row['email'],
                'phoneNumber' => $row['phone_number'],
                'phoneNumber2' => $row['phone_number2'],
                'phoneNumber3' => $row['phone_number3'],
                'status' => $row['status']
            );
        }
		$response->setDataset($dataset);
	}

	public function loadOnlineOrders(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

		if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }

		$users = '';
		$and = '';

        $query = "select b.id from businesspartner b where b.dealer = ".$UserPartner->getId()." and b.partner_type = 'C'  ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {


            $query2 = "select b.name from businesspartner b where b.client_id = ". $row['id'] ." and b.partner_type = 'S'  ";
            $stmt2 = $QueryBuilder->query($query2);
            while ($row2 = $stmt2->fetch()) {
                $users = $users.$and."'". $row2['name'] ."'";
                $and = ',';
            }
        }

		$sql = "select o FROM OnlineOrder o where o.status <> 'EMITIDO' and o.status <> 'CANCELADO' and o.status <> 'FALHA EMISSAO' and o.status <> 'EM ESPERA' and o.status <> 'budget' and o.clientName in (".$users.") order by o.createdAt DESC";
		$query = $em->createQuery($sql);

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                ->setMaxResults($dados['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }
        $onlineOrder = $query->getResult();

		$orders = array();
		foreach($onlineOrder as $Order){
            $status = 'PENDENTE';
            
            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $firstBoardingDate = '';
            if($Order->getFirstBoardingDate()) {
                $firstBoardingDate = $Order->getFirstBoardingDate()->format('Y-m-d H:i:s');
            }

			$orders[] = array(
				'client_name' => $Order->getClientName(),
				'client_email' => $Order->getClientEmail(),
                'status' => $Order->getStatus(),
                'real_status' => $Order->getStatus(),
				'airline' => $Order->getAirline(),
				'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
				'miles_used' => (int)$Order->getMilesUsed(),
				'total_cost' => (float)$Order->getTotalCost(),
				'comments' => $Order->getComments(),
                'userSession' => $comments,
                'firstBoardingDate' => $firstBoardingDate,
                'commercialStatus' => ($Order->getCommercialStatus() == 'true'),
			);
        }
        
        $dataset = array(
            'orders' => $orders
        );
		$response->setDataset($dataset);
	}

	public function saveClient(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['partners'])) {
            $partners = $dados['partners'];
        }
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}
        $warning = false;
        $changes = "";

        try {
            $em = Application::getInstance()->getEntityManager();

            if (isset($dados['id'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->find($dados['id']);
                if($BusinessPartner->getPartnerType() == 'C') {
                    $warning = true;
                    $changes = "";
                } else {
                    $warning = false;
                }
            } else {
                if(isset($dados['type'])) {
                    if(!isset($dados['noRegistrationCode']) || $dados['noRegistrationCode'] == 'false') {
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $dados['registrationCode'], 'partnerType' => $dados['type']));
                    }
                    if(!$BusinessPartner){
                        if($dados['type'] == 'C') {
                            $notificationEmail = true;
                        }
                        $BusinessPartner = new \Businesspartner();
                        $BusinessPartner->setPartnerType($dados['type']);
                    }
                } else {
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['name']));
                }
                if(!$BusinessPartner){
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $dados['registrationCode']));
                    if(!$BusinessPartner){
                        $BusinessPartner = new \Businesspartner();
                        $BusinessPartner->setPartnerType($dados['type']);
                        if (isset($dados['is_partner']) && ($dados['is_partner'] == 'true')) {
                            $BusinessPartner->setPartnerType('PM');
                        }
                    }
                }
                $newInsert = true;
            }

            $BusinessPartner->setName(mb_strtoupper($dados['name']));
            if(isset($dados['phoneNumber']) && $dados['phoneNumber'] != '') {
                if($warning && $BusinessPartner->getPhoneNumber() != $dados['phoneNumber']) {
                    $changes = $changes."<br>Telefone alterado de ".$BusinessPartner->getPhoneNumber()." para ".$dados['phoneNumber'];
                }
                $BusinessPartner->setPhoneNumber($dados['phoneNumber']);
            }

            if (isset($dados['company_name'])) {
                if($warning && $BusinessPartner->getCompanyName() != $dados['company_name']) {
                    $changes = $changes."<br>Razao Social alterado de ".$BusinessPartner->getCompanyName()." para ".$dados['company_name'];
                }
                $BusinessPartner->setCompanyName(mb_strtoupper($dados['company_name']));
            }
            if (isset($dados['phoneNumber2'])) {
                $BusinessPartner->setPhoneNumber2($dados['phoneNumber2']);
            }
            if (isset($dados['phoneNumber3'])) {
                $BusinessPartner->setPhoneNumber3($dados['phoneNumber3']);
            }
            if (isset($dados['adress'])) {
                $BusinessPartner->setAdress(mb_strtoupper($dados['adress']));
            } else {
                $BusinessPartner->setAdress(NULL);
            }
            if (isset($dados['email'])) {
                if($warning && $BusinessPartner->getEmail() != $dados['email']) {
                    $changes = $changes."<br>Email alterado de ".$BusinessPartner->getEmail()." para ".$dados['email'];
                }
                $BusinessPartner->setEmail($dados['email']);
            } else {
                $BusinessPartner->setEmail(NULL);
            }
            if (isset($dados['city']) && $dados['city'] != '') {
                $city = $em->getRepository('City')->findOneBy(array('name' => $dados['city'], 'state' => $dados['state']));
                if(!$city) {
                    $city = new \City();
                    $city->setName($dados['city']);
                    $city->setState($dados['state']);

                    $em->persist($city);
                    $em->flush($city);
                }
                $BusinessPartner->setCity($city);
            } else {
                $BusinessPartner->setCity(NULL);
            }
            if (isset($dados['registrationCode'])) {
                if($warning && $BusinessPartner->getRegistrationCode() != $dados['registrationCode']) {
                    $changes = $changes."<br>CNPJ alterado de ".$BusinessPartner->getRegistrationCode()." para ".$dados['registrationCode'];
                }
                $BusinessPartner->setRegistrationCode($dados['registrationCode']);
            }
            if (isset($dados['type'])) {
                $em->persist($BusinessPartner);

                if(strpos($BusinessPartner->getPartnerType(), "D")) {
                    $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $BusinessPartner->getId()));

                    if(!$UserPermission){
                        $UserPermission = new \UserPermission();
                        $UserPermission->setUser($BusinessPartner);
                        $UserPermission->setCommercial('true');

                    } else {
                        $UserPermission->setCommercial('true');
                    }
                    $em->persist($UserPermission);
                    $em->flush($UserPermission);
                }
                if($dados['type'] == 'U' || $dados['type'] == 'U_D'){
                    $BusinessPartner->setAcessName($dados['name']);
                }
                $BusinessPartner->setPartnerType($dados['type']);
            }
            if (isset($dados['partnerType'])) {
                $BusinessPartner->setPartnerType($dados['partnerType']);
                if($dados['partnerType'] == 'U' || $dados['partnerType'] == 'U_D'){
                    $BusinessPartner->setAcessName($dados['name']);
                }
            }
            if (isset($dados['bank'])) {
                $BusinessPartner->setBank($dados['bank']);
            } else {
                $BusinessPartner->setBank(NULL);
            }
            if (isset($dados['agency'])) {
                $BusinessPartner->setAgency($dados['agency']);
            } else {
                $BusinessPartner->setAgency(NULL);
            }
            if (isset($dados['account'])) {
                $BusinessPartner->setAccount($dados['account']);
            } else {
                $BusinessPartner->setAccount(NULL);
            }
            if (isset($dados['blockreason'])) {
                $BusinessPartner->setBlockReason($dados['blockreason']);
            } else {
                $BusinessPartner->setBlockReason(NULL);
            }
            if (isset($dados['password']) && $dados['password'] != '') {
                if($BusinessPartner->getPassword() !== $dados['password']) {
                    if($BusinessPartner->getId()) {
                        $BusinessPartner->setLastPasswordDate(new \DateTime());
                    }
                    $BusinessPartner->setPassword($dados['password']);
                }
            }
            if (isset($dados['is_master']) && isset($dados['is_master']) != '') {
                $BusinessPartner->setIsMaster($dados['is_master']);
            } else {
                $BusinessPartner->setIsMaster(NULL);
            }
            if (isset($dados['status'])) {
                if($warning && $BusinessPartner->getStatus() != $dados['status']) {
                    $changes = $changes."<br>Status alterado de ".$BusinessPartner->getStatus()." para ".$dados['status'];

                    if($BusinessPartner->getPartnerType() == 'C') {
                        if($dados['status'] == 'Analise prazo' || $dados['status'] == 'Pendente Liberacao') {

                            // status socket track
                            $postfields = array(
                                'client' => $BusinessPartner->getName(),
                                'status' => $dados['status']
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/statusChange');
                            // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/statusChange');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                            $result = curl_exec($ch);

                        }
                    }
                }
                $BusinessPartner->setStatus($dados['status']);
            } else {
                $BusinessPartner->setStatus(NULL);
            }
            if (isset($dados['paymentType'])) {
                if($warning && $BusinessPartner->getPaymentType() != $dados['paymentType']) {
                    $changes = $changes."<br>Pagamento alterado de ".$BusinessPartner->getPaymentType()." para ".$dados['paymentType'];
                }
                $BusinessPartner->setPaymentType($dados['paymentType']);
            } else {
                $BusinessPartner->setIsMaster(NULL);
            }
            if(isset($dados['paymentDays'])){
                if($warning && $BusinessPartner->getPaymentDays() != $dados['paymentDays']) {
                    $changes = $changes."<br>Dias de pagamento alterado de ".$BusinessPartner->getPaymentDays()." para ".$dados['paymentDays'];
                }
                $BusinessPartner->setPaymentDays($dados['paymentDays']);
            } else {
                $BusinessPartner->setPaymentDays(NULL);
            }
            if(isset($dados['description'])){
                $BusinessPartner->setDescription($dados['description']);
            } else {
                $BusinessPartner->setDescription(NULL);
            }
            if(isset($dados['creditAnalysis'])){
                $BusinessPartner->setCreditAnalysis($dados['creditAnalysis']);
            } else {
                $BusinessPartner->setCreditAnalysis(NULL);
            }
            if(isset($dados['registrationCodeCheck'])){
                $BusinessPartner->setRegistrationCodeCheck($dados['registrationCodeCheck']);
            } else {
                $BusinessPartner->setRegistrationCodeCheck(NULL);
            }
            if(isset($dados['adressCheck'])){
                $BusinessPartner->setAdressCheck($dados['adressCheck']);
            } else {
                $BusinessPartner->setAdressCheck(NULL);
            }
            if(isset($dados['creditDescription'])){
                $BusinessPartner->setCreditDescription($dados['creditDescription']);
            } else {
                $BusinessPartner->setCreditDescription(NULL);
            }
            if(isset($dados['partnerLimit'])){
                if($warning && $BusinessPartner->getPartnerLimit() != $dados['partnerLimit']) {
                    $changes = $changes."<br>Limite alterado de ".$BusinessPartner->getPartnerLimit()." para ".$dados['partnerLimit'];
                }
                $BusinessPartner->setPartnerLimit($dados['partnerLimit']);
            } else {
                $BusinessPartner->setPartnerLimit(NULL);
            }
            if(isset($dados['masterCode']) && $dados['masterCode'] != ''){
                $BusinessPartner->setMasterCode($dados['masterCode']);
            }
            if(isset($dados['workingDays'])) {
                $BusinessPartner->setWorkingDays($dados['workingDays']);
            } else {
                $BusinessPartner->setWorkingDays(NULL);
            }
            if(isset($dados['secondPaymentDays'])) {
                $BusinessPartner->setSecondPaymentDays($dados['secondPaymentDays']);
            } else {
                $BusinessPartner->setSecondPaymentDays(NULL);
            }
            if(isset($dados['secondWorkingDays'])) {
                $BusinessPartner->setSecondWorkingDays($dados['secondWorkingDays']);
            } else {
                $BusinessPartner->setSecondWorkingDays(NULL);
            }
            if(isset($dados['billingPeriod'])) {
                $BusinessPartner->setBillingPeriod($dados['billingPeriod']);
            } else {
                $BusinessPartner->setBillingPeriod(NULL);
            }
            if(isset($dados['_birthdate']) && $dados['_birthdate'] != '') {
                $BusinessPartner->setBirthdate(new \Datetime($dados['_birthdate']));
            }
            if(isset($dados['typeSociety'])) {
                $BusinessPartner->setTypeSociety($dados['typeSociety']);
            } else {
                $BusinessPartner->setTypeSociety(NULL);
            }
            if(isset($dados['mulct'])) {
                $BusinessPartner->setMulct($dados['mulct']);
            } else {
                $BusinessPartner->setMulct(NULL);
            }
            if(isset($dados['_registerDate']) && $dados['_registerDate'] != '') {
                $BusinessPartner->setRegisterDate(new \Datetime($dados['_registerDate']));
            } else {
                $BusinessPartner->setRegisterDate(NulL);
            }
            if(isset($dados['adressNumber']) && $dados['adressNumber'] != '') {
                $BusinessPartner->setAdressNumber($dados['adressNumber']);
            } else {
                $BusinessPartner->setAdressNumber(NULL);
            }
            if(isset($dados['adressComplement']) && $dados['adressComplement'] != '') {
                $BusinessPartner->setAdressComplement($dados['adressComplement']);
            } else {
                $BusinessPartner->setAdressComplement(NULL);
            }
            if(isset($dados['zipCode']) && $dados['zipCode'] != '') {
                $BusinessPartner->setZipCode($dados['zipCode']);
            } else {
                $BusinessPartner->setZipCode(NULL);
            }
            if(isset($dados['adressDistrict']) && $dados['adressDistrict'] != '') {
                $BusinessPartner->setAdressDistrict($dados['adressDistrict']);
            } else {
                $BusinessPartner->setAdressDistrict(NULL);
            }
            if(isset($dados['celNumberAirline']) && $dados['celNumberAirline'] != '') {
                $BusinessPartner->setCelNumberAirline($dados['celNumberAirline']);
            } else {
                $BusinessPartner->setCelNumberAirline(NULL);
            }
            if(isset($dados['phoneNumberAirline']) && $dados['phoneNumberAirline'] != '') {
                $BusinessPartner->setPhoneNumberAirline($dados['phoneNumberAirline']);
            } else {
                $BusinessPartner->setPhoneNumberAirline(NULL);
            }
            if(isset($dados['docsSelected']) && $dados['docsSelected'] != '') {
                $BusinessPartner->setDocs($dados['docsSelected']);   
            } else {
                $BusinessPartner->setDocs(NULL);
            }
            if(isset($dados['financialContact']) && $dados['financialContact'] != '') {
                $BusinessPartner->setFinancialContact($dados['financialContact']);   
            } else {
                $BusinessPartner->setFinancialContact(NULL);
            }
            if(isset($dados['nameMother']) && $dados['nameMother'] != '') {
                $BusinessPartner->setNameMother($dados['nameMother']);   
            } else {
                $BusinessPartner->setNameMother(NULL);
            }
            if(isset($dados['limitMargin']) && $dados['limitMargin'] != '') {
                $BusinessPartner->setLimitMargin($dados['limitMargin']);   
            }
            if(isset($dados['salePlan']) && $dados['salePlan'] != '') {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('name' => $dados['salePlan']));
                if($SalePlans) {
                    $BusinessPartner->setPlan($SalePlans);
                }
            }
            if(isset($dados['finnancialEmail']) && $dados['finnancialEmail'] != '') {
                if($warning && $BusinessPartner->getFinnancialEmail() != $dados['finnancialEmail']) {
                    $changes = $changes."<br>Email Financeiro alterado de ".$BusinessPartner->getFinnancialEmail()." para ".$dados['finnancialEmail'];
                }
                $BusinessPartner->setFinnancialEmail($dados['finnancialEmail']);
            } else {
                $BusinessPartner->setFinnancialEmail(NULl);
            }
            if(isset($dados['client']) && $dados['client'] != ''){
                $Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client']));

                $Billetreceives = $em->getRepository('Billetreceive')->findBy(array('client' => $BusinessPartner->getId()));
                foreach ($Billetreceives as $billet) {
                    $billet->setClient($Client);

                    $em->persist($billet);
                    $em->flush($billet);
                }

                $Billsreceives = $em->getRepository('Billsreceive')->findBy(array('client' => $BusinessPartner->getId()));
                foreach ($Billsreceives as $bills) {
                    $bills->setClient($Client);

                    $em->persist($bills);
                    $em->flush($bills);
                }

                $Sales = $em->getRepository('Sale')->findBy(array('client' => $BusinessPartner->getId()));
                foreach ($Sales as $Sale) {
                    $Sale->setClient($Client);

                    $em->persist($Sale);
                    $em->flush($Sale);
                }

                $BusinessPartner->setStatus('Arquivado');
            }
            if(isset($dados['authorizationForSale']) && $dados['authorizationForSale'] != '') {
                $Issuers = $em->getRepository('Businesspartner')->findBy(array('clientId' => $BusinessPartner->getId(), 'partnerType' => 'S'));
                foreach ($Issuers as $Issuer) {
                    $Issuer->setStatus('Pendente');

                    $em->persist($Issuer);
                    $em->flush($Issuer);
                }
            }
            if(isset($dados['interest']) && $dados['interest'] != '') {
                $BusinessPartner->setInterest($dados['interest']);
            }
            if(isset($dados['operationPlan']) && $dados['operationPlan'] != '') {
                $AirlineOperationsPlan = $em->getRepository('AirlineOperationsPlan')->findOneBy(array('description' => $dados['operationPlan']));
                if($AirlineOperationsPlan) {
                    $BusinessPartner->setOperationPlan($AirlineOperationsPlan);
                }
            }
            if(isset($dados['daysToBoarding']) && $dados['daysToBoarding'] != '') {
                $BusinessPartner->setDaysToBoarding($dados['daysToBoarding']);   
            }
            if (isset($dados['cityFinnancialName']) && $dados['cityFinnancialName'] != '') {
                $city = $em->getRepository('City')->findOneBy(array('name' => $dados['cityFinnancialName'], 'state' => $dados['cityFinnancialState']));
                if($city) {
                    $BusinessPartner->setCityFinnancial($city);
                }
            }
            if(isset($dados['adressFinnancial']) && $dados['adressFinnancial'] != '') {
                $BusinessPartner->setAdressFinnancial($dados['adressFinnancial']);   
            }
            if(isset($dados['adressComplementFinnancial']) && $dados['adressComplementFinnancial'] != '') {
                $BusinessPartner->setAdressComplementFinnancial($dados['adressComplementFinnancial']);   
            }
            if(isset($dados['adressDistrictFinnancial']) && $dados['adressDistrictFinnancial'] != '') {
                $BusinessPartner->setAdressDistrictFinnancial($dados['adressDistrictFinnancial']);   
            }
            if(isset($dados['adressNumberFinnancial']) && $dados['adressNumberFinnancial'] != '') {
                $BusinessPartner->setAdressNumberFinnancial($dados['adressNumberFinnancial']);   
            }
            if(isset($dados['zipCodeFinnancial']) && $dados['zipCodeFinnancial'] != '') {
                $BusinessPartner->setZipCodeFinnancial($dados['zipCodeFinnancial']);   
            }
            if(isset($dados['contact'])) {
                $BusinessPartner->setContact($dados['contact']);
            }

            $BusinessPartner->setDealer($UserPartner);
            if((isset($dados['resolveDescription']) && $dados['resolveDescription'] != '') || ($changes != "" && $BusinessPartner->getPartnerType() == 'C')) {

                if(isset($dados['id'])) {
                    $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                    $log = '';
                    if(isset($dados['resolveDescription'])) {
                        $log = $dados['resolveDescription'];
                    }
                    if($changes != '') {
                        $log = $log.' - '.$changes;
                    }

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \DateTime());
                    $SystemLog->setDescription("->CLIENT:".$dados['id']."-".$log);
                    $SystemLog->setLogType('CLIENT');
                    $SystemLog->setBusinesspartner($UserPartner);
                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                }
            }

            if(isset($dados['subClient']) && $dados['subClient'] != '') {
                $BusinessPartner->setSubClient($dados['subClient']);
                if(isset($dados['masterClient']) && $dados['masterClient'] != '') {
                    $masterClient = $em->getRepository('Businesspartner')->findOneBy( array( 'name' => $dados['masterClient'], 'partnerType' => 'C' ) );
                    if($masterClient) {
                        $BusinessPartner->setMasterClient($masterClient);
                    }
                }
            } else {
                $BusinessPartner->setSubClient(NULL);
            }

            if(isset($dados['dealers'])) {
                $Dealers = $em->getRepository('ClientsDealers')->findBy(array('client' => $BusinessPartner->getId()));
                foreach ($Dealers as $dealer) {
                    $em->remove($dealer);
                    $em->flush($dealer);
                }
                foreach ($dados['dealers'] as $dealer) {

                    $DealerPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dealer['name'], 'partnerType' => 'U_D'));
                    if($DealerPartner) {
                        $ClientsDealers = new \ClientsDealers();
                        $ClientsDealers->setClient($BusinessPartner);
                        $ClientsDealers->setDealer($DealerPartner);

                        $em->persist($ClientsDealers);
                        $em->flush($ClientsDealers);
                    }
                }
            }

            if(isset($dados['tags'])) {
                $BusinesspartnerTags = $Tag = $em->getRepository('BusinesspartnerTags')->findBy( array( 'businesspartner' => $BusinessPartner->getId() ) );
                foreach ($BusinesspartnerTags as $key => $value) {
                    $em->remove($value);
                    $em->flush($value);
                }

                foreach ($dados['tags'] as $key => $value) {
                    $Tag = $em->getRepository('Tags')->findOneBy( array( 'id' => $value['id'] ) );
                    $BusinesspartnerTags = new \BusinesspartnerTags();

                    $BusinesspartnerTags->setBusinesspartner($BusinessPartner);
                    $BusinesspartnerTags->setTag($Tag);

                    $em->persist($BusinesspartnerTags);
                    $em->flush($BusinesspartnerTags);
                }
            }
            if(isset($dados['useCommission']) && $dados['useCommission'] != '') {
                $BusinessPartner->setUseCommission($dados['useCommission']);
            }
            if(isset($dados['associatedProvider']) && $dados['associatedProvider'] != '') {
                $associatedProvider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['associatedProvider'], 'partnerType' => 'P'));
                if(!$associatedProvider) {
                    $associatedProvider = new \Businesspartner();
                    $associatedProvider->setPartnerType('P');
                    $associatedProvider->setName($dados['associatedProvider']);

                    $em->persist($associatedProvider);
                    $em->flush($associatedProvider);
                }
                $BusinessPartner->setClient($associatedProvider->getId());
            }

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

            if(isset($partners)){
                
                for ($i = 0; $i <= count($partners)-1; $i++) {
                    $partner = $partners[$i];

                    if(isset($partner['name']) && $partner['name'] != '') {

                        if (isset($partner['id'])) {
                            $associate = $em->getRepository('Businesspartner')->find($partner['id']);

                            if($partner['name'] == '') {
                                $associate->setClient(NULL);
                                $em->remove($associate);
                                $em->flush($associate);

                            } else {
                                if(isset($partner['name'])) {
                                    $associate->setName(mb_strtoupper($partner['name']));
                                }
        
                                if(isset($partner['phoneNumber'])) {
                                    $associate->setPhoneNumber($partner['phoneNumber']);
                                }
        
                                if (isset($partner['phoneNumber2'])) {
                                    $associate->setPhoneNumber2($partner['phoneNumber2']);
                                }
                                if (isset($partner['phoneNumber3'])) {
                                    $associate->setPhoneNumber3($partner['phoneNumber3']);
                                }
                                if (isset($partner['adress'])) {
                                    $associate->setAdress(mb_strtoupper($partner['adress']));
                                }
                                if (isset($partner['email'])) {
                                    $associate->setEmail($partner['email']);
                                }
                                if (isset($partner['city']) && $partner['city'] != '') {
                                    $city = $em->getRepository('City')->findOneBy(array('name' => $partner['city'], 'state' => $partner['state']));
                                    if(!$city){
                                        $city = new \City();
                                        $city->setName($partner['city']);
                                        $city->setState($partner['state']);
        
                                        $em->persist($city);
                                        $em->flush($city);
                                    }
                                    $associate->setCity($city);
                                }
                                if (isset($partner['registrationCode'])) {
                                    $associate->setRegistrationCode($partner['registrationCode']);
                                }
                                if (isset($partner['bank'])) {
                                    $associate->setBank($partner['bank']);
                                }
                                if (isset($partner['agency'])) {
                                    $associate->setAgency($partner['agency']);
                                }
                                if (isset($partner['account'])) {
                                    $associate->setAccount($partner['account']);
                                }
                                if (isset($partner['blockreason'])) {
                                    $associate->setBlockReason($partner['blockreason']);
                                }
                                if (isset($partner['status'])) {
                                    $associate->setStatus($partner['status']);
                                } else {
                                    $associate->setStatus('Aprovado');
                                }
                                if (isset($partner['paymentType'])) {
                                    $associate->setPaymentType($partner['paymentType']);
                                }
                                if(isset($partner['paymentDays'])){
                                    $associate->setPaymentDays($partner['paymentDays']);
                                }
                                if(isset($partner['description'])){
                                    $associate->setDescription($partner['description']);
                                }
                                if(isset($partner['creditAnalysis'])){
                                    $associate->setCreditAnalysis($partner['creditAnalysis']);
                                }
                                if(isset($partner['registrationCodeCheck'])){
                                    $associate->setRegistrationCodeCheck($partner['registrationCodeCheck']);
                                }
                                if(isset($partner['adressCheck'])){
                                    $associate->setAdressCheck($partner['adressCheck']);
                                }
                                if(isset($partner['creditDescription'])){
                                    $associate->setCreditDescription($partner['creditDescription']);
                                }
                                if(isset($partner['_birthdate']) && $partner['_birthdate'] != '') {
                                    $associate->setBirthdate(new \Datetime($partner['_birthdate']));
                                }
        
                                $associate->setClient($BusinessPartner->getId());
                                $em->persist($associate);
                                $em->flush($associate);

                            }
                        } else {
                            if($partner['name'] != '') {
                                $associate = new \Businesspartner();
                                $associate->setPartnerType('N');
                                
                                if(isset($partner['name'])) {
                                    $associate->setName(mb_strtoupper($partner['name']));
                                }
        
                                if(isset($partner['phoneNumber'])) {
                                    $associate->setPhoneNumber($partner['phoneNumber']);
                                }
        
                                if (isset($partner['phoneNumber2'])) {
                                    $associate->setPhoneNumber2($partner['phoneNumber2']);
                                }
                                if (isset($partner['phoneNumber3'])) {
                                    $associate->setPhoneNumber3($partner['phoneNumber3']);
                                }
                                if (isset($partner['adress'])) {
                                    $associate->setAdress(mb_strtoupper($partner['adress']));
                                }
                                if (isset($partner['email'])) {
                                    $associate->setEmail($partner['email']);
                                }
                                if (isset($partner['city']) && $partner['city'] != '') {
                                    $city = $em->getRepository('City')->findOneBy(array('name' => $partner['city'], 'state' => $partner['state']));
                                    if(!$city){
                                        $city = new \City();
                                        $city->setName($partner['city']);
                                        $city->setState($partner['state']);
        
                                        $em->persist($city);
                                        $em->flush($city);
                                    }
                                    $associate->setCity($city);
                                }
                                if (isset($partner['registrationCode'])) {
                                    $associate->setRegistrationCode($partner['registrationCode']);
                                }
                                if (isset($partner['bank'])) {
                                    $associate->setBank($partner['bank']);
                                }
                                if (isset($partner['agency'])) {
                                    $associate->setAgency($partner['agency']);
                                }
                                if (isset($partner['account'])) {
                                    $associate->setAccount($partner['account']);
                                }
                                if (isset($partner['blockreason'])) {
                                    $associate->setBlockReason($partner['blockreason']);
                                }
                                if (isset($partner['status'])) {
                                    $associate->setStatus($partner['status']);
                                } else {
                                    $associate->setStatus('Aprovado');
                                }
                                if (isset($partner['paymentType'])) {
                                    $associate->setPaymentType($partner['paymentType']);
                                }
                                if(isset($partner['paymentDays'])){
                                    $associate->setPaymentDays($partner['paymentDays']);
                                }
                                if(isset($partner['description'])){
                                    $associate->setDescription($partner['description']);
                                }
                                if(isset($partner['creditAnalysis'])){
                                    $associate->setCreditAnalysis($partner['creditAnalysis']);
                                }
                                if(isset($partner['registrationCodeCheck'])){
                                    $associate->setRegistrationCodeCheck($partner['registrationCodeCheck']);
                                }
                                if(isset($partner['adressCheck'])){
                                    $associate->setAdressCheck($partner['adressCheck']);
                                }
                                if(isset($partner['creditDescription'])){
                                    $associate->setCreditDescription($partner['creditDescription']);
                                }
                                if(isset($partner['_birthdate']) && $partner['_birthdate'] != '') {
                                    $associate->setBirthdate(new \Datetime($partner['_birthdate']));
                                }
        
                                $associate->setClient($BusinessPartner->getId());
                                $em->persist($associate);
                                $em->flush($associate);
                            }
                        }
                    }
                }
            }

            if(isset($dados['cardsBloqueds']) || isset($dados['clientsTrack']) || isset($dados['difficultContactTrack']) || isset($dados['emissionTrack']) || isset($dados['firstIssue']) || isset($dados['futureBoardingsTrack']) || isset($dados['limitTrack']) || isset($dados['statusCreditAnalysisTrack']) || isset($dados['statusPendingReleaseTrack']) ) {
                $userGroup = $em->getRepository('UserGroup')->findOneBy(array('user' => $BusinessPartner));
                if(!$userGroup){
                    $userGroup = new \UserGroup;
                }
                if(isset($BusinessPartner)){
                    $userGroup->setUser($BusinessPartner);
                }
                if(isset($dados['cardsBloqueds'])){
                    $userGroup->setCardsBloqueds(getBool($dados['cardsBloqueds']));
                }
                if(isset($dados['clientsTrack'])){
                    $userGroup->setClientsTrack(getBool($dados['clientsTrack']));
                }
                if(isset($dados['difficultContactTrack'])){
                    $userGroup->setDifficultContactTrack(getBool($dados['difficultContactTrack']));
                }
                if(isset($dados['emissionTrack'])){
                    $userGroup->setEmissionTrack(getBool($dados['emissionTrack']));
                }
                if(isset($dados['firstIssue'])){
                    $userGroup->setFirstIssue(getBool(getBool($dados['firstIssue'])));
                }
                if(isset($dados['futureBoardingsTrack'])){
                    $userGroup->setFutureBoardingsTrack(getBool($dados['futureBoardingsTrack']));
                }
                if(isset($dados['limitTrack'])){
                    $userGroup->setLimitTrack(getBool($dados['limitTrack']));
                }
                if(isset($dados['statusCreditAnalysisTrack'])){
                    $userGroup->setStatusCreditAnalysisTrack(getBool($dados['statusCreditAnalysisTrack']));
                }
                if(isset($dados['statusPendingReleaseTrack'])){
                    $userGroup->setStatusPendingReleaseTrack(getBool($dados['statusPendingReleaseTrack']));
                }

                $em->persist($userGroup);
                $em->flush($userGroup);
            }

            if($warning) {

                if($changes != "") {
                    $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                    $email = $UserPartner->getEmail();
                    $postfields = array(
                        'content' =>    "Olá, <br><br> Comprovante de alteração: ".
                                        "<br><br>Alterações: ".$changes.
                                        "<br>Cliente: ".$BusinessPartner->getName().
                                        "<br><br><br>Atenciosamente,".
                                        "<br>MMS-IT<br>N",
                        'partner' => $email,
                        'subject' => '[MMS VIAGENS] - Notificação do sistema',
                        'type' => ''
                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // On dev server only!
                    $result = curl_exec($ch);
                }
            }

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            
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
	
	public function checkRegister(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['partners'])) {
            $partners = $dados['partners'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            $em->getConnection()->beginTransaction();

            if (isset($dados['id'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->find($dados['id']);
            } else {
                if(isset($dados['type'])) {
                    if(!isset($dados['noRegistrationCode']) || $dados['noRegistrationCode'] == 'false') {
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $dados['registrationCode'], 'partnerType' => $dados['type']));
                    }
                    if(!$BusinessPartner){
                        if($dados['type'] == 'C') {
                            $notificationEmail = true;
                        }
                        $BusinessPartner = new \Businesspartner();
                        $BusinessPartner->setPartnerType($dados['type']);
                    }
                } else {
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['name']));
                }
                if(!$BusinessPartner){
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $dados['registrationCode']));
                    if(!$BusinessPartner){
                        $BusinessPartner = new \Businesspartner();
                        $BusinessPartner->setPartnerType($dados['type']);
                        if (isset($dados['is_partner']) && ($dados['is_partner'] == 'true')) {
                            $BusinessPartner->setPartnerType('PM');
                        }
                    }
                }
            }
            $BusinessPartner->setName(mb_strtoupper($dados['name']));
            if(isset($dados['phoneNumber']) && $dados['phoneNumber'] != '') {
                $BusinessPartner->setPhoneNumber($dados['phoneNumber']);
            }
            if (isset($dados['company_name'])) {
                $BusinessPartner->setCompanyName(mb_strtoupper($dados['company_name']));
            }
            if (isset($dados['phoneNumber2'])) {
                $BusinessPartner->setPhoneNumber2($dados['phoneNumber2']);
            }
            if (isset($dados['phoneNumber3'])) {
                $BusinessPartner->setPhoneNumber3($dados['phoneNumber3']);
            }
            if (isset($dados['adress'])) {
                $BusinessPartner->setAdress(mb_strtoupper($dados['adress']));
            }
            if (isset($dados['email'])) {
                $BusinessPartner->setEmail($dados['email']);
            }
            if (isset($dados['city']) && $dados['city'] != '') {
                $city = $em->getRepository('City')->findOneBy(array('name' => $dados['city'], 'state' => $dados['state']));
                if(!$city) {
                    $city = new \City();
                    $city->setName($dados['city']);
                    $city->setState($dados['state']);

                    $em->persist($city);
                    $em->flush($city);
                }
                $BusinessPartner->setCity($city);
            }
            if (isset($dados['registrationCode'])) {
                $BusinessPartner->setRegistrationCode($dados['registrationCode']);
            }
            if (isset($dados['type'])) {
                $em->persist($BusinessPartner);

                if(strpos($BusinessPartner->getPartnerType(), "D")) {
                    $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $BusinessPartner->getId()));

                    if(!$UserPermission){
                        $UserPermission = new \UserPermission();
                        $UserPermission->setUser($BusinessPartner);
                        $UserPermission->setCommercial('true');

                    } else {
                        $UserPermission->setCommercial('true');
                    }
                    $em->persist($UserPermission);
                    $em->flush($UserPermission);
                }
                $BusinessPartner->setPartnerType($dados['type']);
            }
            if (isset($dados['partnerType'])) {
                $BusinessPartner->setPartnerType($dados['partnerType']);
                if($dados['partnerType'] == 'U'){
                    $BusinessPartner->setAcessName($dados['name']);
                }
            }
            if (isset($dados['bank'])) {
                $BusinessPartner->setBank($dados['bank']);
            }
            if (isset($dados['agency'])) {
                $BusinessPartner->setAgency($dados['agency']);
            }
            if (isset($dados['account'])) {
                $BusinessPartner->setAccount($dados['account']);
            }
            if (isset($dados['blockreason'])) {
                $BusinessPartner->setBlockReason($dados['blockreason']);
            }
            if (isset($dados['password'])) {
                $BusinessPartner->setPassword($dados['password']);
            }
            if (isset($dados['is_master'])) {
                $BusinessPartner->setIsMaster($dados['is_master']);
            }
            if (isset($dados['status'])) {
                $BusinessPartner->setStatus($dados['status']);
            } else {
                $BusinessPartner->setStatus('Aprovado');
            }
            if (isset($dados['paymentType'])) {
                $BusinessPartner->setPaymentType($dados['paymentType']);
            }
            if(isset($dados['paymentDays'])){
                $BusinessPartner->setPaymentDays($dados['paymentDays']);
            }
            if(isset($dados['description'])){
                $BusinessPartner->setDescription($dados['description']);
            }
            if(isset($dados['creditAnalysis'])){
                $BusinessPartner->setCreditAnalysis($dados['creditAnalysis']);
            }
            if(isset($dados['registrationCodeCheck'])){
                $BusinessPartner->setRegistrationCodeCheck($dados['registrationCodeCheck']);
            }
            if(isset($dados['adressCheck'])){
                $BusinessPartner->setAdressCheck($dados['adressCheck']);
            }
            if(isset($dados['creditDescription'])){
                $BusinessPartner->setCreditDescription($dados['creditDescription']);
            }
            if(isset($dados['partnerLimit'])){
                $BusinessPartner->setPartnerLimit($dados['partnerLimit']);
            }
            if(isset($dados['masterCode'])){
                $BusinessPartner->setMasterCode($dados['masterCode']);
            }
            if(isset($dados['workingDays'])) {
                $BusinessPartner->setWorkingDays($dados['workingDays']);
            }
            if(isset($dados['secondPaymentDays'])) {
                $BusinessPartner->setSecondPaymentDays($dados['secondPaymentDays']);
            }
            if(isset($dados['secondWorkingDays'])) {
                $BusinessPartner->setSecondWorkingDays($dados['secondWorkingDays']);
            }
            if(isset($dados['billingPeriod'])) {
                $BusinessPartner->setBillingPeriod($dados['billingPeriod']);
            }
            if(isset($dados['_birthdate']) && $dados['_birthdate'] != '') {
                $BusinessPartner->setBirthdate(new \Datetime($dados['_birthdate']));
            }
            if(isset($dados['typeSociety'])) {
                $BusinessPartner->setTypeSociety($dados['typeSociety']);
            }
            if(isset($dados['mulct'])) {
                $BusinessPartner->setMulct($dados['mulct']);
            }
            if(isset($dados['_registerDate']) && $dados['_registerDate'] != '') {
                $BusinessPartner->setRegisterDate(new \Datetime($dados['_registerDate']));
            }
            if(isset($dados['adressNumber']) && $dados['adressNumber'] != '') {
                $BusinessPartner->setAdressNumber($dados['adressNumber']);
            }
            if(isset($dados['adressComplement']) && $dados['adressComplement'] != '') {
                $BusinessPartner->setAdressComplement($dados['adressComplement']);
            }
            if(isset($dados['zipCode']) && $dados['zipCode'] != '') {
                $BusinessPartner->setZipCode($dados['zipCode']);
            }
            if(isset($dados['adressDistrict']) && $dados['adressDistrict'] != '') {
                $BusinessPartner->setAdressDistrict($dados['adressDistrict']);
            }
            if(isset($dados['celNumberAirline']) && $dados['celNumberAirline'] != '') {
                $BusinessPartner->setCelNumberAirline($dados['celNumberAirline']);
            }
            if(isset($dados['phoneNumberAirline']) && $dados['phoneNumberAirline'] != '') {
                $BusinessPartner->setPhoneNumberAirline($dados['phoneNumberAirline']);
            }
            if(isset($dados['docsSelected']) && $dados['docsSelected'] != '') {
                $BusinessPartner->setDocs($dados['docsSelected']);   
            }
            if(isset($dados['financialContact']) && $dados['financialContact'] != '') {
                $BusinessPartner->setFinancialContact($dados['financialContact']);   
            }
            if(isset($dados['nameMother']) && $dados['nameMother'] != '') {
                $BusinessPartner->setNameMother($dados['nameMother']);   
            }
            if(isset($dados['limitMargin']) && $dados['limitMargin'] != '') {
                $BusinessPartner->setLimitMargin($dados['limitMargin']);   
            }
            if(isset($dados['salePlan']) && $dados['salePlan'] != '') {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('name' => $dados['salePlan']));
                if($SalePlans) {
                    $BusinessPartner->setPlan($SalePlans);
                }
            }
            if(isset($dados['finnancialEmail']) && $dados['finnancialEmail'] != '') {
                $BusinessPartner->setFinnancialEmail($dados['finnancialEmail']);   
            }
            if(isset($dados['client']) && $dados['client'] != ''){
                $Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client']));

                $Billetreceives = $em->getRepository('Billetreceive')->findBy(array('client' => $BusinessPartner->getId()));
                foreach ($Billetreceives as $billet) {
                    $billet->setClient($Client);

                    $em->persist($billet);
                    $em->flush($billet);
                }

                $Billsreceives = $em->getRepository('Billsreceive')->findBy(array('client' => $BusinessPartner->getId()));
                foreach ($Billsreceives as $bills) {
                    $bills->setClient($Client);

                    $em->persist($bills);
                    $em->flush($bills);
                }

                $Sales = $em->getRepository('Sale')->findBy(array('client' => $BusinessPartner->getId()));
                foreach ($Sales as $Sale) {
                    $Sale->setClient($Client);

                    $em->persist($Sale);
                    $em->flush($Sale);
                }

                $BusinessPartner->setStatus('Arquivado');
            }
            if(isset($dados['authorizationForSale']) && $dados['authorizationForSale'] != '') {
                $Issuers = $em->getRepository('Businesspartner')->findBy(array('clientId' => $BusinessPartner->getId(), 'partnerType' => 'S'));
                foreach ($Issuers as $Issuer) {
                    $Issuer->setStatus('Pendente');

                    $em->persist($Issuer);
                    $em->flush($Issuer);
                }
            }
            if(isset($dados['interest']) && $dados['interest'] != '') {
                $BusinessPartner->setInterest($dados['interest']);
            }
            if(isset($dados['operationPlan']) && $dados['operationPlan'] != '') {
                $AirlineOperationsPlan = $em->getRepository('AirlineOperationsPlan')->findOneBy(array('description' => $dados['operationPlan']));
                if($AirlineOperationsPlan) {
                    $BusinessPartner->setOperationPlan($AirlineOperationsPlan);
                }
            }
            if(isset($dados['daysToBoarding']) && $dados['daysToBoarding'] != '') {
                $BusinessPartner->setDaysToBoarding($dados['daysToBoarding']);   
            }
            if (isset($dados['cityFinnancialName']) && $dados['cityFinnancialName'] != '') {
                $city = $em->getRepository('City')->findOneBy(array('name' => $dados['cityFinnancialName'], 'state' => $dados['cityFinnancialState']));
                if($city) {
                    $BusinessPartner->setCityFinnancial($city);
                }
            }
            if(isset($dados['adressFinnancial']) && $dados['adressFinnancial'] != '') {
                $BusinessPartner->setAdressFinnancial($dados['adressFinnancial']);   
            }
            if(isset($dados['adressComplementFinnancial']) && $dados['adressComplementFinnancial'] != '') {
                $BusinessPartner->setAdressComplementFinnancial($dados['adressComplementFinnancial']);   
            }
            if(isset($dados['adressDistrictFinnancial']) && $dados['adressDistrictFinnancial'] != '') {
                $BusinessPartner->setAdressDistrictFinnancial($dados['adressDistrictFinnancial']);   
            }
            if(isset($dados['adressNumberFinnancial']) && $dados['adressNumberFinnancial'] != '') {
                $BusinessPartner->setAdressNumberFinnancial($dados['adressNumberFinnancial']);   
            }
            if(isset($dados['zipCodeFinnancial']) && $dados['zipCodeFinnancial'] != '') {
                $BusinessPartner->setZipCodeFinnancial($dados['zipCodeFinnancial']);   
            }
            if(isset($dados['clientDealer'])) {
                $dealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['clientDealer']));
                if($dealer) {
                    $BusinessPartner->setDealer($dealer);
                } else {
                    $BusinessPartner->setDealer(NULL);
                }
            }

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

            if(isset($partners)){
                
                for ($i = 0; $i <= count($partners)-1; $i++) {
                    $partner = $partners[$i];

                    if(isset($partner['name']) && $partner['name'] != '') {

                        if (isset($partner['id'])) {
                            $associate = $em->getRepository('Businesspartner')->find($partner['id']);
                        } else {
                            if (isset($partner['registrationCode'])) {
                                $associate = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $partner['registrationCode']));
                                if(!$associate) {
                                    $associate = new \Businesspartner();
                                    $associate->setPartnerType('N');
                                }
                                if($associate->getPartnerType() != 'N') {
                                    $associate->setPartnerType($associate->getPartnerType().'_N');
                                }
                            } else {
                                $associate = new \Businesspartner();
                                $associate->setPartnerType('N');
                            }
                        }

                        if(isset($partner['name'])) {
                            $associate->setName(mb_strtoupper($partner['name']));
                        }

                        if(isset($partner['phoneNumber'])) {
                            $associate->setPhoneNumber($partner['phoneNumber']);
                        }

                        if (isset($partner['phoneNumber2'])) {
                            $associate->setPhoneNumber2($partner['phoneNumber2']);
                        }
                        if (isset($partner['phoneNumber3'])) {
                            $associate->setPhoneNumber3($partner['phoneNumber3']);
                        }
                        if (isset($partner['adress'])) {
                            $associate->setAdress(mb_strtoupper($partner['adress']));
                        }
                        if (isset($partner['email'])) {
                            $associate->setEmail($partner['email']);
                        }
                        if (isset($partner['city']) && $partner['city'] != '') {
                            $city = $em->getRepository('City')->findOneBy(array('name' => $partner['city'], 'state' => $partner['state']));
                            if(!$city){
                                $city = new \City();
                                $city->setName($partner['city']);
                                $city->setState($partner['state']);

                                $em->persist($city);
                                $em->flush($city);
                            }
                            $associate->setCity($city);
                        }
                        if (isset($partner['registrationCode'])) {
                            $associate->setRegistrationCode($partner['registrationCode']);
                        }
                        if (isset($partner['bank'])) {
                            $associate->setBank($partner['bank']);
                        }
                        if (isset($partner['agency'])) {
                            $associate->setAgency($partner['agency']);
                        }
                        if (isset($partner['account'])) {
                            $associate->setAccount($partner['account']);
                        }
                        if (isset($partner['blockreason'])) {
                            $associate->setBlockReason($partner['blockreason']);
                        }
                        if (isset($partner['status'])) {
                            $associate->setStatus($partner['status']);
                        } else {
                            $associate->setStatus('Aprovado');
                        }
                        if (isset($partner['paymentType'])) {
                            $associate->setPaymentType($partner['paymentType']);
                        }
                        if(isset($partner['paymentDays'])){
                            $associate->setPaymentDays($partner['paymentDays']);
                        }
                        if(isset($partner['description'])){
                            $associate->setDescription($partner['description']);
                        }
                        if(isset($partner['creditAnalysis'])){
                            $associate->setCreditAnalysis($partner['creditAnalysis']);
                        }
                        if(isset($partner['registrationCodeCheck'])){
                            $associate->setRegistrationCodeCheck($partner['registrationCodeCheck']);
                        }
                        if(isset($partner['adressCheck'])){
                            $associate->setAdressCheck($partner['adressCheck']);
                        }
                        if(isset($partner['creditDescription'])){
                            $associate->setCreditDescription($partner['creditDescription']);
                        }
                        if(isset($partner['_birthdate']) && $partner['_birthdate'] != '') {
                            $associate->setBirthdate(new \Datetime($partner['_birthdate']));
                        }

                        $associate->setClient($BusinessPartner->getId());
                        $em->persist($associate);
                        $em->flush($associate);
                    }
                }
            }

            $em->getConnection()->rollback();
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
}