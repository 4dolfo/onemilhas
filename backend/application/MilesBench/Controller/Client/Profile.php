<?php

namespace MilesBench\Controller\Client;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Profile {

	public function loadCity(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$Cities = $em->getRepository('City')->findBy(array('state' => $dados['state']));

		$dataset = array();
		foreach($Cities as $City){
			$dataset[] = array(
				'name' => $City->getName(),
				'id' => $City->getId(),
				'state' => $City->getState()
			);

		}
		$response->setDataset($dataset);
	}

	public function loadState(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
		$sql = "select c FROM City c GROUP BY c.state ORDER BY c.state";
		$query = $em->createQuery($sql);
		$Cities = $query->getResult();

		$dataset = array();
		foreach($Cities as $City){
			$dataset[] = array(
				'state' => $City->getState()
			);

		}
		$response->setDataset($dataset);
	}

	public function save(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		if (isset($dados['partners'])) {
			$partners = $dados['partners'];
		}
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();
			if (isset($dados['id'])) {
				$BusinessPartner = $em->getRepository('Businesspartner')->find($dados['id']);
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
			if(isset($dados['description'])){
				$BusinessPartner->setDescription($dados['description']);
			}
			if(isset($dados['creditAnalysis'])){
				$BusinessPartner->setCreditAnalysis($dados['creditAnalysis']);
			}
			if(isset($dados['registrationCodeCheck'])){
				$BusinessPartner->setRegistrationCodeCheck($dados['registrationCodeCheck']);
			}
			if(isset($dados['_birthdate']) && $dados['_birthdate'] != '') {
				$BusinessPartner->setBirthdate(new \Datetime($dados['_birthdate']));
			}
			if(isset($dados['typeSociety'])) {
				$BusinessPartner->setTypeSociety($dados['typeSociety']);
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

			$em->persist($BusinessPartner);
			$em->flush($BusinessPartner);

			// voe legal validadtion
			$voelegal = Application::getInstance()->getEntityManagerVoeLegal();
			$AppBusinesspartner = $voelegal->getRepository('AppBusinesspartner')->findOneBy(array('email' => $BusinessPartner->getEmail()));
			if(!$AppBusinesspartner) {
				$AppBusinesspartner = new \AppBusinesspartner();

				$AppBusinesspartner->setName($BusinessPartner->getName());
				$AppBusinesspartner->setEmail($BusinessPartner->getEmail());
				$AppBusinesspartner->setRegisterDate(new \DateTime());
				$AppBusinesspartner->setRegistrationCode($BusinessPartner->getRegistrationCode());
				$AppBusinesspartner->setPhone($BusinessPartner->getPhoneNumber());
				$AppBusinesspartner->setValidationLogin('dealer');

				$agreement = '';
				$names = explode(' ', $BusinessPartner->getName());
				foreach ($names as $name) {
					$agreement = $agreement.strtolower($name);
				}

				$AppAgreement = new \AppAgreement();
				$AppAgreement->setName($agreement);
				$AppAgreement->setDescription($BusinessPartner->getName().' - Representante');

				$voelegal->persist($AppAgreement);
				$voelegal->flush($AppAgreement);

				$AppBusinesspartner->setAgreement($AppAgreement);
			}

			$AppBusinesspartner->setPassword($dados['password']);

			$voelegal->persist($AppBusinesspartner);
			$voelegal->flush($AppBusinesspartner);

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

	public function loadProfile(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();

		$Profile = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));

		$dataset = array();

		$City = $Profile->getCity();
		if ($City) {
			$cityfullname = $City->getName() . ', ' . $City->getState();
			$cityname = $City->getName();
			$citystate = $City->getState();
		} else {
			$cityfullname = '';
			$cityname = '';
			$citystate = '';
		}

		$sales = 0;
		$purchases = 0;

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

		$UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $Profile->getId()));

		if($UserPermission){
		
			$monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');

			if($UserPermission->getWizardSale() == "true") {
				$sql = "select COUNT(s.id) as sales FROM Sale s where s.user = '".$Profile->getId()."' and s.issueDate >= '".$monthsAgo->format('Y-m-d')."' ";
				$query = $em->createQuery($sql);
				$Sales = $query->getResult();

				$sales = $Sales[0]['sales'];
			}

			if($UserPermission->getWizardPurchase() == "true") {
				$sql = "select COUNT(p.id) as purchases FROM Purchase p where p.user = '".$Profile->getId()."' and p.purchaseDate >= '".$monthsAgo->format('Y-m-d')."' ";
				$query = $em->createQuery($sql);
				$Purchases = $query->getResult();

				$purchases = $Purchases[0]['purchases'];
			}

			
			if($UserPermission->getSundayIn()) {
				$sundayIn = $UserPermission->getSundayIn()->format('Y-m-d H:i:s');                
			}
			if($UserPermission->getMondayIn()) {
				$mondayIn = $UserPermission->getMondayIn()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getTuesdayIn()) {
				$tuesdayIn = $UserPermission->getTuesdayIn()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getWednesdayIn()) {
				$wednesdayIn = $UserPermission->getWednesdayIn()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getThursdayIn()) {
				$thursdayIn = $UserPermission->getThursdayIn()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getFridayIn()) {
				$fridayIn = $UserPermission->getFridayIn()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getSaturdayIn()) {
				$saturdayIn = $UserPermission->getSaturdayIn()->format('Y-m-d H:i:s');
			}

			if($UserPermission->getSundayOut()) {
				$sundayOut = $UserPermission->getSundayOut()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getMondayOut()) {
				$mondayOut = $UserPermission->getMondayOut()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getTuesdayOut()) {
				$tuesdayOut = $UserPermission->getTuesdayOut()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getWednesdayOut()) {
				$wednesdayOut = $UserPermission->getWednesdayOut()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getThursdayOut()) {
				$thursdayOut = $UserPermission->getThursdayOut()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getFridayOut()) {
				$fridayOut = $UserPermission->getFridayOut()->format('Y-m-d H:i:s');
			}
			if($UserPermission->getSaturdayOut()) {
				$saturdayOut = $UserPermission->getSaturdayOut()->format('Y-m-d H:i:s');
			}
		}

		$state = '0';
		$city = '';
		if($Profile->getCity()) {
			$state = $Profile->getCity()->getState();
			$city = $Profile->getCity()->getName();
		}

		$dataset[] = array(
			'sales' => (int)$sales,
			'purchases' => (int)$purchases,
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
			'saturdayOut' => $saturdayOut,
			'id' => $Profile->getId(),
			'name' => $Profile->getName(),
			'email' => $Profile->getEmail(),
			'acessName' => $Profile->getAcessName(),
			'is_master' => $Profile->getIsMaster(),
			'adress' => $Profile->getAdress(),
			'phoneNumber' => $Profile->getPhoneNumber(),
			'phoneNumber2' => $Profile->getPhoneNumber2(),
			'phoneNumber3' => $Profile->getPhoneNumber3(),
			'registrationCode' => $Profile->getRegistrationCode(),
			'state' => $state,
			'city' => $city
		);

		$response->setDataset(array_shift($dataset));
	}

}
