<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Client {

    public function saveClient(Request $request, Response $response) {
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
        $warning = false;
        $changes = "";
        $env = getenv('ENV') ? getenv('ENV') : 'production';

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
                    if(!isset($BusinessPartner) || $BusinessPartner == null){
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
            if (isset($dados['prefixo'])) {
                $BusinessPartner->setPrefixo($dados['prefixo']);
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
                $plano = "[vazio]";
                if($BusinessPartner->getPlan()){
                    $plano = $BusinessPartner->getPlan()->getName();
                }
                if($warning && $plano != $dados['salePlan']) {
                    $changes = $changes."<br>Plano Comercial ".$plano." para ".$dados['salePlan'];
                }                
                
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
            if(isset($dados['clientDealer']) && $dados['clientDealer'] != '') {
                $sql = "select b FROM Businesspartner b where b.name = '".$dados['clientDealer']."' and b.partnerType like '%D%' ";
                $query = $em->createQuery($sql);
                $Dealers = $query->getResult();

                if(count($Dealers) > 0) {
                    foreach ($Dealers as $dealer) {
                        if($warning) {
                            if($BusinessPartner->getDealer()) {
                                if($dealer->getName() != $BusinessPartner->getDealer()->getName()) {
                                    $changes = $changes."<br>Representante alterado para ".$dealer->getName();
                                }
                            }
                        }
                        $BusinessPartner->setDealer($dealer);
                    }
                } else {
                    $BusinessPartner->setDealer(NULL);
                }
            }
            if(isset($dados['contact'])) {
                $BusinessPartner->setContact($dados['contact']);
            }
            if(isset($dados['dealer']) && $dados['dealer'] != '') {
                $sql = "select b FROM Businesspartner b where b.name = '".$dados['dealer']."' and b.partnerType like '%D%' ";
                $query = $em->createQuery($sql);
                $Dealers = $query->getResult();

                if(count($Dealers) > 0) {
                    foreach ($Dealers as $dealer) {
                        if($warning) {
                            if($BusinessPartner->getDealer()) {
                                if($dealer->getName() != $BusinessPartner->getDealer()->getName()) {
                                    $changes = $changes."<br>Representante alterado para ".$dealer->getName();
                                }
                            }
                        }
                        $BusinessPartner->setDealer($dealer);
                    }
                } else {
                    $BusinessPartner->setDealer(NULL);
                }
            }
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

            if(isset($dados['origin']) && $dados['origin'] != '') {
                $BusinessPartner->setOrigin($dados['origin']);
            }
            if(isset($dados['bankSlipSocialName']) && $dados['bankSlipSocialName'] != '') {
                $BusinessPartner->setBankSlipSocialName($dados['bankSlipSocialName']);
            }

            if(isset($dados['systemName'])) {
                $BusinessPartner->setSystemName($dados['systemName']);
            }
            if(isset($dados['logoUrl'])) {
                $BusinessPartner->setLogoUrl($dados['logoUrl']);
            }
            if(isset($dados['labelName'])) {
                $BusinessPartner->setLabelName($dados['labelName']);
            }
            if(isset($dados['labelDescription'])) {
                $BusinessPartner->setLabelDescription($dados['labelDescription']);
            }
            if(isset($dados['labelAdress'])) {
                $BusinessPartner->setLabelAdress($dados['labelAdress']);
            }
            if(isset($dados['labelPhone'])) {
                $BusinessPartner->setLabelPhone($dados['labelPhone']);
            }
            if(isset($dados['labelEmail'])) {
                $BusinessPartner->setLabelEmail($dados['labelEmail']);
            }
            if(isset($dados['logoUrlSmall'])) {
                $BusinessPartner->setLogoUrlSmall($dados['logoUrlSmall']);
            }
            if(isset($dados['whitelabel'])) {
                $BusinessPartner->setWhitelabel($dados['whitelabel'] == 'true');
            }
            if(isset($dados['urlWhitelabel'])) {
                $BusinessPartner->setUrlWhitelabel($dados['urlWhitelabel']);
            }
            if(isset($dados['partnerData'])) {
                $BusinessPartner->setSplitPaymentData(json_encode($dados['partnerData']));
            }
            $this->registerUserMoip($BusinessPartner);

            if(isset($dados['salePlan']) && $dados['salePlan'] != '') {
                $salePlans = new \MilesBench\Controller\SalePlans();
                $salePlans->updatePrecificationByClient($BusinessPartner->getId());
            }

            if(isset($dados['commission'])) {
                $BusinessPartner->setCommission($dados['commission']);
            }
            if(isset($dados['client_markup_type'])) {
                $BusinessPartner->setClientMarkupType($dados['client_markup_type']);
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
                                if(isset($partner['zipCode']) && $partner['zipCode'] != '') {
                                    $associate->setZipCode($partner['zipCode']);
                                }
                                if(isset($partner['streetNumber']) && $partner['streetNumber'] != '') {
                                    $associate->setAdressNumber($partner['streetNumber']);
                                }
                                if(isset($partner['district']) && $partner['district'] != '') {
                                    $associate->setAdressDistrict($partner['district']);
                                }
                                if(isset($partner['cpfBankAccount']) && $partner['cpfBankAccount'] != '') {
                                    $associate->setCpfBankAccount($partner['cpfBankAccount']);
                                }
                                if(isset($partner['nameBankAccount']) && $partner['nameBankAccount'] != '') {
                                    $associate->setNameBankAccount($partner['nameBankAccount']);
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
                                if(isset($partner['zipCode']) && $partner['zipCode'] != '') {
                                    $associate->setZipCode($partner['zipCode']);
                                }
                                if(isset($partner['streetNumber']) && $partner['streetNumber'] != '') {
                                    $associate->setAdressNumber($partner['streetNumber']);
                                }
                                if(isset($partner['district']) && $partner['district'] != '') {
                                    $associate->setAdressDistrict($partner['district']);
                                }
                                if(isset($partner['cpfBankAccount']) && $partner['cpfBankAccount'] != '') {
                                    $associate->setCpfBankAccount($partner['cpfBankAccount']);
                                }
                                if(isset($partner['nameBankAccount']) && $partner['nameBankAccount'] != '') {
                                    $associate->setNameBankAccount($partner['nameBankAccount']);
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

            if(isset($notificationEmail) && $notificationEmail == true) {

                if($env == 'production') {
                    $email1 = 'suporte@onemilhas.com.br';
                    $postfields = array(
                        'content' =>    "Agencia Cadastrada - ".
                                        "<br><br>Nome: ".$BusinessPartner->getName().
                                        "<br>Razão Social: ".$BusinessPartner->getCompanyName().
                                        "<br>CNPJ: ".$BusinessPartner->getRegistrationCode().
                                        "<br>Email: ".$BusinessPartner->getEmail().
                                        "<br>Endereço: ".$BusinessPartner->getAdress().
                                        "<br>Prazo: ".$BusinessPartner->getPaymentType().
                                        "<br>Observação: ".$BusinessPartner->getDescription().
                                        "<br><br><br>Atenciosamente,".
                                        "<br>N",
                                        'partner' => $email1,
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
                    if($result != "OK") {
                        $email = new Mail();
                        $req = new \MilesBench\Request\Request();
                        $resp = new \MilesBench\Request\Response();
                        $email1 = 'suporte@onemilhas.com.br';
                        $req->setRow(
                            array('type' => 'COMERCIAL',
                            'data' => array('subject' => '[MMS VIAGENS] - Notificação do sistema',
                                                    'emailContent' => "Agencia Cadastrada - <br><br>Nome: ".$BusinessPartner->getName().
                                                    "<br>CNPJ: ".$BusinessPartner->getRegistrationCode().
                                                    "<br>Email: ".$BusinessPartner->getEmail().
                                                    "<br>Endereço: ".$BusinessPartner->getAdress().
                                                    "<br>Prazo: ".$BusinessPartner->getPaymentType().
                                                    "<br>Observação: ".$BusinessPartner->getDescription().
                                                    "<br><br><br>Atenciosamente,<br>MMS-IT",
                                                    'emailpartner' => array($email1) )));
                        $email->SendMail($req, $resp);
                    }
                }

                $BusinessPartner = \MilesBench\Controller\Client::updateClient($BusinessPartner, 'new');
            } else if($BusinessPartner->getPartnerType() == 'C') {
                $BusinessPartner = \MilesBench\Controller\Client::updateClient($BusinessPartner);
            }

            if($warning && $env == 'production') {

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

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select b.*, " .
            " (select MAX(s.issue_date) from sale s where s.client_id = b.id) as last_emission, " .
            " (select COUNT(s.id) from sale s where s.client_id = b.id) as countd, " .
            " d.name as dealer_name " .
            " FROM businesspartner b LEFT JOIN city c on c.id = b.city_id LEFT JOIN businesspartner d on d.id = b.dealer where b.partner_type like '%C%' and b.status<>'Arquivado' ";

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

        $search_filters = '';
        if(isset($filter['paymentType']) && $filter['paymentType'] != '') {
            $sql = $sql." and b.payment_type = '".$filter['paymentType']."' ";
            $search_filters .= "<br>Forma Pagamento: ".$filter['paymentType'];
        }

        if(isset($filter['status']) && $filter['status'] != '') {
            $sql = $sql." and b.status = '".$filter['status']."' ";
            $search_filters .= "<br>Status: ".$filter['status'];
        }

        if(isset($filter['clientName']) && $filter['clientName'] != '') {
            $sql = $sql." and b.name = '".$filter['clientName']."' ";
            $search_filters .= "<br>Nome: ".$filter['clientName'];
        }

        if(isset($filter['description']) && $filter['description'] != '') {
            $sql = $sql." and b.description like '%".$filter['description']."%' ";
            $search_filters .= "<br>Observação: ".$filter['description'];
        }

        if(isset($filter['_registerFromDate']) && $filter['_registerFromDate'] != '') {
            $sql = $sql." and b.register_date > '".(new \Datetime($filter['_registerFromDate']))->format('Y-m-d')."' ";
            $search_filters .= "<br>Data Registro: ".$filter['_registerFromDate'];
        }

        if(isset($filter['_registerToDate']) && $filter['_registerToDate'] != '') {
            $sql = $sql." and b.register_date <= '".(new \DateTime($filter['_registerToDate']))->format('Y-m-d')."' ";
            $search_filters .= "<br>Data Registro ate: ".$filter['_registerToDate'];
        }

        if(isset($filter['state']) && $filter['state'] != '') {
            $sql = $sql." and c.state = '".$filter['state']."' ";
            $search_filters .= "<br>Estado: ".$filter['state'];
        }

        if(isset($filter['neverSale']) && $filter['neverSale']  == 'true') {
            $sql = $sql." AND (select COUNT(s.id) from sale s where s.client_id = b.id) <= 0 AND c.state NOT IN ('MG', 'PR', 'SC', 'RS') ";
            $sql = $sql." AND b.name not IN ('NORTE VIAGEM','PM TURISMO E CÂMBIO','VERINHA TOUR','ACESSO RÁPIDO','PODER VIAGENS','BIRA TOUR','SEMEARTUR','TOPTUR','MR VIAGENS E EVENTOS','GLOBALTOUR','AGÊNCIA TURISMO PAIVA','GARCIA VIAGENS E TURISMO','MANITUR TURISMO','SOLARES TURISMO','GLOBAL PASSAGENS E TURISMO','GT94','RBA TELEVISÃO','NILTON MILHAS','REQUINTE E TURISMO BEL','SEVENFLY TURISMO','FAROL TURISMO E SERVIÇOS','SEVEN TUR','KLÉBIO MILHAS','NEW PERSONAL','DE MALAS PRONTAS','BELLENZIER TURISMO','J S AGÊNCIA DE VIAGENS','GOMES VIAGENS E TURISMO','DUPRES VIAGENS','TRAVEL DANCE VIAGENS','CULTURAL TURISMO','AKY VOA PASSAGENS AÉREAS','PARADISE PASSAGENS AEREA','TRANSBENTO','VOE COM DESCONTOS','BEST LIVE TURISMO','MAIS TOUR AGÊNCIA DE VIAGENS E TURISMO','VANDA TURISMO','PR-AR CONDICIONADO','SOL E MAR TURISMO','T & E TURISMO','PECÉM VIAGENS','NATURAL TOUR','2! TRAVEL VIAGENS E EVENTOS','LOUNGE VIP','SVT AGENCIA DE VIAGENS','AGENCIA PARAISO TOUR','GOOD FLY','RIOSTOUR VIAGENS E TURISMO','ELI VIAGENS') ";
            $sql = $sql." AND b.master_client IS NULL AND b.register_date < '2018-07-24' AND c.name NOT IN ('Sao Paulo') AND b.dealer NOT IN (19912, 40016) ";
        }
        
        if(isset($filter['dealer']) && $filter['dealer'] != '') {
            $Dealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $filter['dealer'], 'partnerType' => 'U_D'));
            if($Dealer) {

                $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $Dealer->getId()));
                $clients = '0';
                $andD = ',';
                foreach ($ClientsDealers as $dealers) {
                    $clients = $clients.$andD.$dealers->getClient()->getId();
                    $andD = ',';
                }

                $sql = $sql." and (b.dealer = '".$Dealer->getId()."' or b.id in (".$clients.") )";
            }
        }

        // order
        $orderBy = ' order by b.name ASC ';
        if(isset($dados['order']) && $dados['order'] != '') {
            if( $dados['order'] == 'last_emission' || $dados['order'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['order'] . ' ASC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['order']) .' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if( $dados['orderDown'] == 'last_emission' || $dados['orderDown'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['orderDown'] . ' DESC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['orderDown']) .' DESC ';
            }
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            // $query = $em->createQuery($sql);
            $stmt = $QueryBuilder->query($sql);
        }

        $clients = array();
        while ($row = $stmt->fetch()) {
            $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');
            $sql = "select COUNT(s.id) as countd FROM Sale s where s.issueDate >= '".$monthsAgo->format('Y-m-d')."' AND s.client ='". $row['id'] ."' ";
            $query = $em->createQuery($sql);
            $Sale = $query->getResult();

            if($Sale[0]['countd'] != NULL) {
                $last_month_emission = $Sale[0]['countd'];
            } else {
                $last_month_emission = '0';
            }

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

            $salePlan = '';
            if($row['plan_id'] != NULL) {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array( 'id' => $row['plan_id'] ));
                $salePlan = $SalePlans->getName();
            }

            $operationPlan = '';
            if($row['operation_plan_id'] != NULL) {
                $AirlineOperationsPlan = $em->getRepository('AirlineOperationsPlan')->findOneBy(array( 'id' => $row['operation_plan_id'] ));
                $operationPlan = $AirlineOperationsPlan->getDescription();
            }

            if ($row['city_finnancial_id'] != NULL) {
                $cityFinnancial = $em->getRepository('City')->findOneBy(array( 'id' => $row['city_finnancial_id'] ));
                $cityFinnancialName = $cityFinnancial->getName();
                $cityFinnancialState = $cityFinnancial->getState();
            } else {
                $cityFinnancialName = '';
                $cityFinnancialState = '0';
            }

            $dealers = array();
            $dealersPartners = $em->getRepository('ClientsDealers')->findBy(array( 'client' => $row['id'] ));
            foreach($dealersPartners as $dealer) {
                $dealers[] = array(
                    'name' => $dealer->getDealer()->getName()
                );
            }

            $tags = array();
            $BusinesspartnerTags = $em->getRepository('BusinesspartnerTags')->findBy(array( 'businesspartner' => $row['id'] ));
            foreach($BusinesspartnerTags as $value) {
                $tags[] = array(
                    'id' => $value->getTag()->getId(),
                    'name' => $value->getTag()->getName(),
                    'description' => $value->getTag()->getDescription()
                );
            }

            if($row['last_emission'] == NULL) {
                $row['last_emission'] = '';
            }

            $masterClient = '';
            if($row['sub_client'] == 'true') {
                $PartnerMaster = $em->getRepository('Businesspartner')->findOneBy(array( 'id' => $row['master_client'] ));
                if($PartnerMaster) {
                    $masterClient = $PartnerMaster->getName();
                }
            }

            $clients[] = array(
                'id' => $row['id'],
                'company_name' => $row['company_name'],
                'name' => $row['name'],
                'registrationCode' => $row['registration_code'],
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $row['adress'],
                'partnerType' => $row['partner_type'],
                'email' => $row['email'],
                'phoneNumber' => $row['phone_number'],
                'phoneNumber2' => $row['phone_number2'],
                'phoneNumber3' => $row['phone_number3'],
                'status' => $row['status'],
                'paymentType' => $row['payment_type'],
                'paymentDays' => $row['payment_days'],
                'description' => $row['description'],
                'creditAnalysis' => $row['credit_analysis'],
                'registrationCodeCheck' => $row['registration_code_check'],
                'adressCheck' => $row['adress_check'],
                'creditDescription' => $row['credit_description'],
                'partnerLimit' => (float)$row['partner_limit'],
                'last_emission' => $row['last_emission'],
                'countd' => (float)$row['countd'],
                'last_month_emission' => (float)$last_month_emission,
                'workingDays' => $row['working_days'] == 'true',
                'secondWorkingDays' => $row['second_working_days'] == 'true',
                'secondPaymentDays' => $row['second_payment_days'],
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
                'salePlan' => $salePlan,
                'finnancialEmail' => $row['finnancial_email'],
                'interest' => (float) $row['interest'],
                'operationPlan' => $operationPlan,
                'daysToBoarding' => (float)$row['days_to_boarding'],
                'adressFinnancial' => $row['adress_finnancial'],
                'adressComplementFinnancial' => $row['adress_complement_finnancial'],
                'adressDistrictFinnancial' => $row['adress_district_finnancial'],
                'adressNumberFinnancial' => $row['adress_number_finnancial'],
                'zipCodeFinnancial' => $row['zip_code_finnancial'],
                'cityFinnancialName' => $cityFinnancialName,
                'cityFinnancialState' => $cityFinnancialState,
                'dealer' => $row['dealer_name'],
                'dealers' => $dealers,
                'tags' => $tags,
                'contact' => $row['contact'],
                'useCommission' => ( $row['use_commission'] == 'true'),
                'commission' => (float)$row['commission'],
                'client_markup_type' => $row['client_markup_type'],
                'subClient' => ( $row['sub_client'] == 'true'),
                'masterClient' => $masterClient,
                'origin' => $row['origin'],
                'bankSlipSocialName'  => $row['bank_slip_social_name'] == 'true',
                'systemName' => $row['system_name'],
                'logoUrl' => $row['logo_url'],
                'labelName' => $row['label_name'],
                'labelDescription' => $row['label_description'],
                'labelAdress' => $row['label_adress'],
                'labelPhone' => $row['label_phone'],
                'labelEmail' => $row['label_email'],
                'logoUrlSmall' => $row['logo_url_small'],
                'prefixo' => $row['prefixo'],
                'whitelabel' => $row['whitelabel'] == '1',
                'urlWhitelabel' => $row['url_whitelabel'],
                'partnerData' => json_decode($row['split_payment_data'], true)
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

                $sql = "select COUNT(b) as quant FROM Businesspartner b JOIN b.city c where b.partnerType like '%C%' and b.status<>'Arquivado' ".$where;

        } else {
            $sql = "select COUNT(b) as quant FROM Businesspartner b JOIN b.city c where b.partnerType like '%C%' and b.status<>'Arquivado' ";
        }

        if(isset($filter['neverSale']) && $filter['neverSale']  == 'true') {
            $sql = $sql." AND (select COUNT(s.id) from Sale s where s.client = b.id) <= 0 AND c.state NOT IN ('MG', 'PR', 'SC', 'RS') ";
            $sql = $sql." AND b.name not IN ('NORTE VIAGEM','PM TURISMO E CÂMBIO','VERINHA TOUR','ACESSO RÁPIDO','PODER VIAGENS','BIRA TOUR','SEMEARTUR','TOPTUR','MR VIAGENS E EVENTOS','GLOBALTOUR','AGÊNCIA TURISMO PAIVA','GARCIA VIAGENS E TURISMO','MANITUR TURISMO','SOLARES TURISMO','GLOBAL PASSAGENS E TURISMO','GT94','RBA TELEVISÃO','NILTON MILHAS','REQUINTE E TURISMO BEL','SEVENFLY TURISMO','FAROL TURISMO E SERVIÇOS','SEVEN TUR','KLÉBIO MILHAS','NEW PERSONAL','DE MALAS PRONTAS','BELLENZIER TURISMO','J S AGÊNCIA DE VIAGENS','GOMES VIAGENS E TURISMO','DUPRES VIAGENS','TRAVEL DANCE VIAGENS','CULTURAL TURISMO','AKY VOA PASSAGENS AÉREAS','PARADISE PASSAGENS AEREA','TRANSBENTO','VOE COM DESCONTOS','BEST LIVE TURISMO','MAIS TOUR AGÊNCIA DE VIAGENS E TURISMO','VANDA TURISMO','PR-AR CONDICIONADO','SOL E MAR TURISMO','T & E TURISMO','PECÉM VIAGENS','NATURAL TOUR','2! TRAVEL VIAGENS E EVENTOS','LOUNGE VIP','SVT AGENCIA DE VIAGENS','AGENCIA PARAISO TOUR','GOOD FLY','RIOSTOUR VIAGENS E TURISMO','ELI VIAGENS') ";
            $sql = $sql." AND b.masterClient IS NULL AND b.registerDate < '2018-07-24' AND c.name NOT IN ('Sao Paulo') AND b.dealer NOT IN (19912, 40016) ";
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'clients' => $clients,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function loadWait(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select b.*, " .
            " (select MAX(s.issue_date) from sale s where s.client_id = b.id) as last_emission, " .
            " (select COUNT(s.id) from sale s where s.client_id = b.id) as countd, " .
            " d.name as dealer_name " .
            " FROM businesspartner b LEFT JOIN city c on c.id = b.city_id LEFT JOIN businesspartner d on d.id = b.dealer where b.partner_type like '%C%' and  b.status = 'Pendente'";

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

        $search_filters = '';
        if(isset($filter['paymentType']) && $filter['paymentType'] != '') {
            $sql = $sql." and b.payment_type = '".$filter['paymentType']."' ";
            $search_filters .= "<br>Forma Pagamento: ".$filter['paymentType'];
        }

        if(isset($filter['status']) && $filter['status'] != '') {
            $sql = $sql." and b.status = '".$filter['status']."' ";
            $search_filters .= "<br>Status: ".$filter['status'];
        }

        if(isset($filter['clientName']) && $filter['clientName'] != '') {
            $sql = $sql." and b.name = '".$filter['clientName']."' ";
            $search_filters .= "<br>Nome: ".$filter['clientName'];
        }

        if(isset($filter['description']) && $filter['description'] != '') {
            $sql = $sql." and b.description like '%".$filter['description']."%' ";
            $search_filters .= "<br>Observação: ".$filter['description'];
        }

        if(isset($filter['_registerFromDate']) && $filter['_registerFromDate'] != '') {
            $sql = $sql." and b.register_date > '".(new \Datetime($filter['_registerFromDate']))->format('Y-m-d')."' ";
            $search_filters .= "<br>Data Registro: ".$filter['_registerFromDate'];
        }

        if(isset($filter['_registerToDate']) && $filter['_registerToDate'] != '') {
            $sql = $sql." and b.register_date <= '".(new \DateTime($filter['_registerToDate']))->format('Y-m-d')."' ";
            $search_filters .= "<br>Data Registro ate: ".$filter['_registerToDate'];
        }

        if(isset($filter['state']) && $filter['state'] != '') {
            $sql = $sql." and c.state = '".$filter['state']."' ";
            $search_filters .= "<br>Estado: ".$filter['state'];
        }

        if(isset($filter['neverSale']) && $filter['neverSale']  == 'true') {
            $sql = $sql." and (select COUNT(s.id) from sale s where s.client_id = b.id) <= 0 ";
        }

        if($UserPartner) {
            $content = '<br>Ola<br>';
            $content .= 'Tela: Clientes';
            if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
                $content .= '<br>Pesquisa: ' . $dados['searchKeywords'];
            }
            $email1 = 'adm@onemilhas.com.br';

            $postfields = array(
                'content' => $content.$search_filters,
                'partner' => $email1,
                'from' => $email1,
                'subject' => 'BBB - Clientes ' . $UserPartner->getName(),
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
            $SystemLog->setDescription($content.$search_filters);
            $SystemLog->setLogType('BBB');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);
        }

        if(isset($filter['dealer']) && $filter['dealer'] != '') {
            $Dealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $filter['dealer'], 'partnerType' => 'U_D'));
            if($Dealer) {

                $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $Dealer->getId()));
                $clients = '0';
                $andD = ',';
                foreach ($ClientsDealers as $dealers) {
                    $clients = $clients.$andD.$dealers->getClient()->getId();
                    $andD = ',';
                }

                $sql = $sql." and (b.dealer = '".$Dealer->getId()."' or b.id in (".$clients.") )";
            }
        }

        // order
        $orderBy = ' order by b.name ASC ';
        if(isset($dados['order']) && $dados['order'] != '') {
            if( $dados['order'] == 'last_emission' || $dados['order'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['order'] . ' ASC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['order']) .' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if( $dados['orderDown'] == 'last_emission' || $dados['orderDown'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['orderDown'] . ' DESC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['orderDown']) .' DESC ';
            }
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            // $query = $em->createQuery($sql);
            $stmt = $QueryBuilder->query($sql);
        }

        $clients = array();
        while ($row = $stmt->fetch()) {
            $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');
            $sql = "select COUNT(s.id) as countd FROM Sale s where s.issueDate >= '".$monthsAgo->format('Y-m-d')."' AND s.client ='". $row['id'] ."' ";
            $query = $em->createQuery($sql);
            $Sale = $query->getResult();

            if($Sale[0]['countd'] != NULL) {
                $last_month_emission = $Sale[0]['countd'];
            } else {
                $last_month_emission = '0';
            }

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

            $salePlan = '';
            if($row['plan_id'] != NULL) {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array( 'id' => $row['plan_id'] ));
                $salePlan = $SalePlans->getName();
            }

            $operationPlan = '';
            if($row['operation_plan_id'] != NULL) {
                $AirlineOperationsPlan = $em->getRepository('AirlineOperationsPlan')->findOneBy(array( 'id' => $row['operation_plan_id'] ));
                $operationPlan = $AirlineOperationsPlan->getDescription();
            }

            if ($row['city_finnancial_id'] != NULL) {
                $cityFinnancial = $em->getRepository('City')->findOneBy(array( 'id' => $row['city_finnancial_id'] ));
                $cityFinnancialName = $cityFinnancial->getName();
                $cityFinnancialState = $cityFinnancial->getState();
            } else {
                $cityFinnancialName = '';
                $cityFinnancialState = '0';
            }

            $dealers = array();
            $dealersPartners = $em->getRepository('ClientsDealers')->findBy(array( 'client' => $row['id'] ));
            foreach($dealersPartners as $dealer) {
                $dealers[] = array(
                    'name' => $dealer->getDealer()->getName()
                );
            }

            $tags = array();
            $BusinesspartnerTags = $em->getRepository('BusinesspartnerTags')->findBy(array( 'businesspartner' => $row['id'] ));
            foreach($BusinesspartnerTags as $value) {
                $tags[] = array(
                    'id' => $value->getTag()->getId(),
                    'name' => $value->getTag()->getName(),
                    'description' => $value->getTag()->getDescription()
                );
            }

            if($row['last_emission'] == NULL) {
                $row['last_emission'] = '';
            }

            $masterClient = '';
            if($row['sub_client'] == 'true') {
                $PartnerMaster = $em->getRepository('Businesspartner')->findOneBy(array( 'id' => $row['master_client'] ));
                if($PartnerMaster) {
                    $masterClient = $PartnerMaster->getName();
                }
            }

            $clients[] = array(
                'id' => $row['id'],
                'company_name' => $row['company_name'],
                'name' => $row['name'],
                'registrationCode' => $row['registration_code'],
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $row['adress'],
                'partnerType' => $row['partner_type'],
                'email' => $row['email'],
                'phoneNumber' => $row['phone_number'],
                'phoneNumber2' => $row['phone_number2'],
                'phoneNumber3' => $row['phone_number3'],
                'status' => $row['status'],
                'paymentType' => $row['payment_type'],
                'paymentDays' => $row['payment_days'],
                'description' => $row['description'],
                'creditAnalysis' => $row['credit_analysis'],
                'registrationCodeCheck' => $row['registration_code_check'],
                'adressCheck' => $row['adress_check'],
                'creditDescription' => $row['credit_description'],
                'partnerLimit' => (float)$row['partner_limit'],
                'last_emission' => $row['last_emission'],
                'countd' => (float)$row['countd'],
                'last_month_emission' => (float)$last_month_emission,
                'workingDays' => $row['working_days'] == 'true',
                'secondWorkingDays' => $row['second_working_days'] == 'true',
                'secondPaymentDays' => $row['second_payment_days'],
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
                'salePlan' => $salePlan,
                'finnancialEmail' => $row['finnancial_email'],
                'interest' => (float) $row['interest'],
                'operationPlan' => $operationPlan,
                'daysToBoarding' => (float)$row['days_to_boarding'],
                'adressFinnancial' => $row['adress_finnancial'],
                'adressComplementFinnancial' => $row['adress_complement_finnancial'],
                'adressDistrictFinnancial' => $row['adress_district_finnancial'],
                'adressNumberFinnancial' => $row['adress_number_finnancial'],
                'zipCodeFinnancial' => $row['zip_code_finnancial'],
                'cityFinnancialName' => $cityFinnancialName,
                'cityFinnancialState' => $cityFinnancialState,
                'dealer' => $row['dealer_name'],
                'dealers' => $dealers,
                'tags' => $tags,
                'contact' => $row['contact'],
                'useCommission' => ( $row['use_commission'] == 'true'),
                'subClient' => ( $row['sub_client'] == 'true'),
                'masterClient' => $masterClient,
                'origin' => $row['origin'],
                'bankSlipSocialName'  => $row['bank_slip_social_name'] == 'true',
                'systemName' => $row['system_name'],
                'logoUrl' => $row['logo_url'],
                'labelName' => $row['label_name'],
                'labelDescription' => $row['label_description'],
                'labelAdress' => $row['label_adress'],
                'labelPhone' => $row['label_phone'],
                'labelEmail' => $row['label_email'],
                'logoUrlSmall' => $row['logo_url_small']
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

                $sql = "select COUNT(b) as quant FROM Businesspartner b JOIN b.city c where b.partnerType like '%C%' and b.status<>'Arquivado' and b.status = 'Pendente'  ".$where;

        } else {
            $sql = "select COUNT(b) as quant FROM Businesspartner b where b.partnerType like '%C%' and b.status<>'Arquivado' and b.status = 'Pendente' ";
        }

        if(isset($filter['neverSale']) && $filter['neverSale']  == 'true') {
            $sql = $sql." and (select COUNT(s.id) from Sale s where s.client = b.id) <= 0 ";
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'clients' => $clients,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function loadArquivo(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select b.*, " .
            " (select MAX(s.issue_date) from sale s where s.client_id = b.id) as last_emission, " .
            " (select COUNT(s.id) from sale s where s.client_id = b.id) as countd, " .
            " d.name as dealer_name " .
            " FROM businesspartner b LEFT JOIN city c on c.id = b.city_id LEFT JOIN businesspartner d on d.id = b.dealer where b.partner_type like '%C%' and  b.status = 'Arquivado'";

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

        $search_filters = '';
        if(isset($filter['paymentType']) && $filter['paymentType'] != '') {
            $sql = $sql." and b.payment_type = '".$filter['paymentType']."' ";
            $search_filters .= "<br>Forma Pagamento: ".$filter['paymentType'];
        }

        if(isset($filter['status']) && $filter['status'] != '') {
            $sql = $sql." and b.status = '".$filter['status']."' ";
            $search_filters .= "<br>Status: ".$filter['status'];
        }

        if(isset($filter['clientName']) && $filter['clientName'] != '') {
            $sql = $sql." and b.name = '".$filter['clientName']."' ";
            $search_filters .= "<br>Nome: ".$filter['clientName'];
        }

        if(isset($filter['description']) && $filter['description'] != '') {
            $sql = $sql." and b.description like '%".$filter['description']."%' ";
            $search_filters .= "<br>Observação: ".$filter['description'];
        }

        if(isset($filter['_registerFromDate']) && $filter['_registerFromDate'] != '') {
            $sql = $sql." and b.register_date > '".(new \Datetime($filter['_registerFromDate']))->format('Y-m-d')."' ";
            $search_filters .= "<br>Data Registro: ".$filter['_registerFromDate'];
        }

        if(isset($filter['_registerToDate']) && $filter['_registerToDate'] != '') {
            $sql = $sql." and b.register_date <= '".(new \DateTime($filter['_registerToDate']))->format('Y-m-d')."' ";
            $search_filters .= "<br>Data Registro ate: ".$filter['_registerToDate'];
        }

        if(isset($filter['state']) && $filter['state'] != '') {
            $sql = $sql." and c.state = '".$filter['state']."' ";
            $search_filters .= "<br>Estado: ".$filter['state'];
        }

        if(isset($filter['neverSale']) && $filter['neverSale']  == 'true') {
            $sql = $sql." and (select COUNT(s.id) from sale s where s.client_id = b.id) <= 0 ";
        }

        if($UserPartner) {
            $content = '<br>Ola<br>';
            $content .= 'Tela: Clientes';
            if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
                $content .= '<br>Pesquisa: ' . $dados['searchKeywords'];
            }

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription($content.$search_filters);
            $SystemLog->setLogType('BBB');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);
        }

        if(isset($filter['dealer']) && $filter['dealer'] != '') {
            $Dealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $filter['dealer'], 'partnerType' => 'U_D'));
            if($Dealer) {

                $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $Dealer->getId()));
                $clients = '0';
                $andD = ',';
                foreach ($ClientsDealers as $dealers) {
                    $clients = $clients.$andD.$dealers->getClient()->getId();
                    $andD = ',';
                }

                $sql = $sql." and (b.dealer = '".$Dealer->getId()."' or b.id in (".$clients.") )";
            }
        }

        // order
        $orderBy = ' order by b.name ASC ';
        if(isset($dados['order']) && $dados['order'] != '') {
            if( $dados['order'] == 'last_emission' || $dados['order'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['order'] . ' ASC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['order']) .' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if( $dados['orderDown'] == 'last_emission' || $dados['orderDown'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['orderDown'] . ' DESC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['orderDown']) .' DESC ';
            }
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            // $query = $em->createQuery($sql);
            $stmt = $QueryBuilder->query($sql);
        }

        $clients = array();
        while ($row = $stmt->fetch()) {
            $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');
            $sql = "select COUNT(s.id) as countd FROM Sale s where s.issueDate >= '".$monthsAgo->format('Y-m-d')."' AND s.client ='". $row['id'] ."' ";
            $query = $em->createQuery($sql);
            $Sale = $query->getResult();

            if($Sale[0]['countd'] != NULL) {
                $last_month_emission = $Sale[0]['countd'];
            } else {
                $last_month_emission = '0';
            }

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

            $salePlan = '';
            if($row['plan_id'] != NULL) {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array( 'id' => $row['plan_id'] ));
                $salePlan = $SalePlans->getName();
            }

            $operationPlan = '';
            if($row['operation_plan_id'] != NULL) {
                $AirlineOperationsPlan = $em->getRepository('AirlineOperationsPlan')->findOneBy(array( 'id' => $row['operation_plan_id'] ));
                $operationPlan = $AirlineOperationsPlan->getDescription();
            }

            if ($row['city_finnancial_id'] != NULL) {
                $cityFinnancial = $em->getRepository('City')->findOneBy(array( 'id' => $row['city_finnancial_id'] ));
                $cityFinnancialName = $cityFinnancial->getName();
                $cityFinnancialState = $cityFinnancial->getState();
            } else {
                $cityFinnancialName = '';
                $cityFinnancialState = '0';
            }

            $dealers = array();
            $dealersPartners = $em->getRepository('ClientsDealers')->findBy(array( 'client' => $row['id'] ));
            foreach($dealersPartners as $dealer) {
                $dealers[] = array(
                    'name' => $dealer->getDealer()->getName()
                );
            }

            $tags = array();
            $BusinesspartnerTags = $em->getRepository('BusinesspartnerTags')->findBy(array( 'businesspartner' => $row['id'] ));
            foreach($BusinesspartnerTags as $value) {
                $tags[] = array(
                    'id' => $value->getTag()->getId(),
                    'name' => $value->getTag()->getName(),
                    'description' => $value->getTag()->getDescription()
                );
            }

            if($row['last_emission'] == NULL) {
                $row['last_emission'] = '';
            }

            $masterClient = '';
            if($row['sub_client'] == 'true') {
                $PartnerMaster = $em->getRepository('Businesspartner')->findOneBy(array( 'id' => $row['master_client'] ));
                if($PartnerMaster) {
                    $masterClient = $PartnerMaster->getName();
                }
            }

            $clients[] = array(
                'id' => $row['id'],
                'company_name' => $row['company_name'],
                'name' => $row['name'],
                'registrationCode' => $row['registration_code'],
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $row['adress'],
                'partnerType' => $row['partner_type'],
                'email' => $row['email'],
                'phoneNumber' => $row['phone_number'],
                'phoneNumber2' => $row['phone_number2'],
                'phoneNumber3' => $row['phone_number3'],
                'status' => $row['status'],
                'paymentType' => $row['payment_type'],
                'paymentDays' => $row['payment_days'],
                'description' => $row['description'],
                'creditAnalysis' => $row['credit_analysis'],
                'registrationCodeCheck' => $row['registration_code_check'],
                'adressCheck' => $row['adress_check'],
                'creditDescription' => $row['credit_description'],
                'partnerLimit' => (float)$row['partner_limit'],
                'last_emission' => $row['last_emission'],
                'countd' => (float)$row['countd'],
                'last_month_emission' => (float)$last_month_emission,
                'workingDays' => $row['working_days'] == 'true',
                'secondWorkingDays' => $row['second_working_days'] == 'true',
                'secondPaymentDays' => $row['second_payment_days'],
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
                'salePlan' => $salePlan,
                'finnancialEmail' => $row['finnancial_email'],
                'interest' => (float) $row['interest'],
                'operationPlan' => $operationPlan,
                'daysToBoarding' => (float)$row['days_to_boarding'],
                'adressFinnancial' => $row['adress_finnancial'],
                'adressComplementFinnancial' => $row['adress_complement_finnancial'],
                'adressDistrictFinnancial' => $row['adress_district_finnancial'],
                'adressNumberFinnancial' => $row['adress_number_finnancial'],
                'zipCodeFinnancial' => $row['zip_code_finnancial'],
                'cityFinnancialName' => $cityFinnancialName,
                'cityFinnancialState' => $cityFinnancialState,
                'dealer' => $row['dealer_name'],
                'dealers' => $dealers,
                'tags' => $tags,
                'contact' => $row['contact'],
                'useCommission' => ( $row['use_commission'] == 'true'),
                'subClient' => ( $row['sub_client'] == 'true'),
                'masterClient' => $masterClient,
                'origin' => $row['origin'],
                'bankSlipSocialName'  => $row['bank_slip_social_name'] == 'true',
                'systemName' => $row['system_name'],
                'logoUrl' => $row['logo_url'],
                'labelName' => $row['label_name'],
                'labelDescription' => $row['label_description'],
                'labelAdress' => $row['label_adress'],
                'labelPhone' => $row['label_phone'],
                'labelEmail' => $row['label_email'],
                'logoUrlSmall' => $row['logo_url_small']
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

                $sql = "select COUNT(b) as quant FROM Businesspartner b JOIN b.city c where b.partnerType like '%C%' and b.status = 'Arquivado'  ".$where;

        } else {
            $sql = "select COUNT(b) as quant FROM Businesspartner b where b.partnerType like '%C%' and b.status = 'Arquivado' ";
        }

        if(isset($filter['neverSale']) && $filter['neverSale']  == 'true') {
            $sql = $sql." and (select COUNT(s.id) from Sale s where s.client = b.id) <= 0 ";
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'clients' => $clients,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function loadClientsByFilter(Request $request, Response $response) {
        $dados = $request->getRow();
        
		if (isset($dados['impressao_ids'])) {
			$impressao_ids = $dados['impressao_ids'];
        }
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        if(isset($request->getRow()['impressao_ids'])){
            $today = new \DateTime();
            $sql = "select b FROM Businesspartner b WHERE b.id IN ( ";
            $virg = "";
            foreach($impressao_ids as $id){
                $sql .= $virg.$id." ";
                $virg = ",";
            }
            $sql .= " ) ";
        }
        else{
            $sql = "select b FROM Businesspartner b LEFT JOIN b.city c where b.partnerType like '%C%' ".$dealer;

            if(isset($dados['paymentType']) && $dados['paymentType'] != '') {
                $sql = $sql." and b.paymentType = '".$dados['paymentType']."' ";
            }

            if(isset($dados['status']) && $dados['status'] != '') {
                $sql = $sql." and b.status = '".$dados['status']."' ";
            }

            if(isset($dados['clientName']) && $dados['clientName'] != '') {
                $sql = $sql." and b.name = '".$dados['clientName']."' ";
            }

            if(isset($dados['description']) && $dados['description'] != '') {
                $sql = $sql." and b.description like '%".$dados['description']."%' ";
            }

            if(isset($dados['_registerFromDate']) && $dados['_registerFromDate'] != '') {
                $sql = $sql." and b.registerDate > '".(new \Datetime($dados['_registerFromDate']))->format('Y-m-d')."' ";
            }

            if(isset($dados['_registerToDate']) && $dados['_registerToDate'] != '') {
                $sql = $sql." and b.registerDate <= '".(new \DateTime($dados['_registerToDate']))->format('Y-m-d')."' ";
            }

            if(isset($dados['state']) && $dados['state'] != '') {
                $sql = $sql." and c.state = '".$dados['state']."' ";
            }

            if(isset($dados['dealer']) && $dados['dealer'] != '') {
                $Dealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['dealer'], 'partnerType' => 'U_D'));
                if($Dealer) {

                    $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $Dealer->getId()));
                    $clients = '0';
                    $andD = ',';
                    foreach ($ClientsDealers as $dealers) {
                        $clients = $clients.$andD.$dealers->getClient()->getId();
                        $andD = ',';
                    }

                    $sql = $sql." and (b.dealer = '".$Dealer->getId()."' or b.id in (".$clients.") )";
                }
            }
        }

        $query = $em->createQuery($sql.' order by b.name ASC ');
        $BusinessPartner = $query->getResult();

        $dataset = array();
        foreach($BusinessPartner as $Agency){

            $sql = "select MAX(s.issueDate) as issueDate, COUNT(s.id) as countd FROM Sale s WHERE s.client ='".$Agency->getId()."' ";
            $query = $em->createQuery($sql);
            $Sales = $query->getResult();

            if($Sales[0]['issueDate'] != NULL) {
                $last_emission = $Sales[0]['issueDate'];
                $countd = $Sales[0]['countd'];
            } else {
                $last_emission = '';
                $countd = '';
            }

            $valid = true;
            if(isset($dados['_fromDate']) && $dados['_fromDate'] != '') {
                if($last_emission != '') {
                    if(new \Datetime($last_emission) < new \Datetime($dados['_fromDate']))
                        $valid = false;
                    else if(isset($dados['_toDate']) && $dados['_toDate'] != '') {
                        if(new \Datetime($last_emission) > new \Datetime($dados['_toDate']))
                            $valid = false;
                    }
                } else {
                    $valid = false;
                }
            }
            if(isset($dados['neverSale']) && $dados['neverSale'] == true) {
                if($last_emission != '')
                    $valid = false;
            }

            if(isset($dados['_notFromDate']) && $dados['_notFromDate'] != '') {
                $sql = "select s FROM Sale s where s.issueDate >= '".(new \Datetime($dados['_notFromDate']))->format('Y-m-d')."' AND s.client ='".$Agency->getId()."' ";
                if(isset($dados['_notToDate']) && $dados['_notToDate'] != '') {
                    $sql = $sql." and s.issueDate <=  '".(new \Datetime($dados['_notFromDate']))->modify('+1 day')->format('Y-m-d')."' ";
                }

                $query = $em->createQuery($sql);
                $Sale = $query->getResult();

                if(count($Sale) > 0) {
                    $valid = false;
                }
            }

            if($valid) {
                $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');

                $sql = "select COUNT(s.id) as countd FROM Sale s where s.issueDate >= '".$monthsAgo->format('Y-m-d')."' AND s.client ='".$Agency->getId()."' ";
                $query = $em->createQuery($sql);
                $Sale = $query->getResult();

                if($Sale[0]['countd'] != NULL) {
                    $last_month_emission = $Sale[0]['countd'];
                } else {
                    $last_month_emission = '0';
                }

                $City = $Agency->getCity();
                if ($City) {
                    $cityfullname = $City->getName() . ', ' . $City->getState();
                    $cityname = $City->getName();
                    $citystate = $City->getState();
                } else {
                    $cityfullname = '';
                    $cityname = '';
                    $citystate = '0';
                }

                $workingDays = $Agency->getWorkingDays() == "true";
                $secondWorkingDays = $Agency->getSecondWorkingDays() == "true";

                $birthdate = '';
                if($Agency->getBirthdate()) {
                    $birthdate = $Agency->getBirthdate()->format('Y-m-d H:i:s');
                }

                $registerDate = '';
                if($Agency->getRegisterDate()) {
                    $registerDate = $Agency->getRegisterDate()->format('Y-m-d H:i:s');
                }

                $salePlan = '';
                if($Agency->getPlan()) {
                    $salePlan = $Agency->getPlan()->getName();
                }

                $dealer = '';
                if($Agency->getDealer()) {
                    $dealer = $Agency->getDealer()->getName();
                }

                $operationPlan = '';
                if($Agency->getOperationPlan()) {
                    $operationPlan = $Agency->getOperationPlan()->getDescription();
                }

                $cityFinnancial = $Agency->getCityFinnancial();
                if ($cityFinnancial) {
                    $cityFinnancialName = $cityFinnancial->getName();
                    $cityFinnancialState = $cityFinnancial->getState();
                } else {
                    $cityFinnancialName = '';
                    $cityFinnancialState = '0';
                }

                $creditAnalysis = $Agency->getCreditAnalysis();
                if(is_numeric($creditAnalysis)) {
                    $creditAnalysis = (string)$creditAnalysis;
                }

                $dealers = array();
                $dealersPartners = $em->getRepository('ClientsDealers')->findBy(array('client' => $Agency->getId()));
                foreach($dealersPartners as $dealer) {
                    $dealers[] = array(
                        'name' => $dealer->getDealer()->getName()
                    );
                }

                $Raw = "";
                if(isset($request->getRow()['impressao_ids'])){
                    $today = new \DateTime();
                    $sql = "select e.content as content FROM Emails e WHERE e.dateToSend >= '".$today->format('Y-m-d 00:00:01')."' AND e.dateToSend <= '".$today->format('Y-m-d 23:59:59')."' AND e.user = ".$Agency->getId()." ";
                    $query = $em->createQuery($sql);
                    $Raw = $query->getResult();
                    //$Raw = $Raw[0]["content"];
                }

                $dataset[] = array(
                    'id' => $Agency->getId(),
                    'company_name' => $Agency->getCompanyName(),
                    'name' => $Agency->getName(),
                    'registrationCode' => $Agency->getRegistrationCode(),
                    'city' => $cityname,
                    'state' => $citystate,
                    'cityfullname' => $cityfullname,
                    'adress' => $Agency->getAdress(),
                    'partnerType' => $Agency->getPartnerType(),
                    'email' => $Agency->getEmail(),
                    'phoneNumber' => $Agency->getPhoneNumber(),
                    'phoneNumber2' => $Agency->getPhoneNumber2(),
                    'phoneNumber3' => $Agency->getPhoneNumber3(),
                    'status' => $Agency->getStatus(),
                    'paymentType' => $Agency->getPaymentType(),
                    'paymentDays' => $Agency->getPaymentDays(),
                    'description' => $Agency->getDescription(),
                    'creditAnalysis' => $creditAnalysis,
                    'registrationCodeCheck' => $Agency->getRegistrationCodeCheck(),
                    'adressCheck' => $Agency->getAdressCheck(),
                    'creditDescription' => $Agency->getCreditDescription(),
                    'partnerLimit' => (float)$Agency->getPartnerLimit(),
                    'last_emission' => $last_emission,
                    'countd' => (float)$countd,
                    'last_month_emission' => (float)$last_month_emission,
                    'workingDays' => $workingDays,
                    'secondPaymentDays' => $Agency->getSecondPaymentDays(),
                    'secondWorkingDays' => $secondWorkingDays,
                    'billingPeriod' => $Agency->getBillingPeriod(),
                    'birthdate' => $birthdate,
                    'typeSociety' => $Agency->getTypeSociety(),
                    'mulct' => $Agency->getMulct(),
                    'registerDate' => $registerDate,
                    'adressNumber' => $Agency->getAdressNumber(),
                    'adressComplement' => $Agency->getAdressComplement(),
                    'zipCode' => $Agency->getZipCode(),
                    'adressDistrict' => $Agency->getAdressDistrict(),
                    'account' => $Agency->getAccount(),
                    'financialContact' => $Agency->getFinancialContact(),
                    'limitMargin' => (float)$Agency->getLimitMargin(),
                    'salePlan' => $salePlan,
                    'finnancialEmail' => $Agency->getFinnancialEmail(),
                    'interest' => (float)$Agency->getInterest(),
                    'operationPlan' => $operationPlan,
                    'daysToBoarding' => $Agency->getDaysToBoarding(),
                    'adressFinnancial' => $Agency->getAdressFinnancial(),
                    'adressComplementFinnancial' => $Agency->getAdressComplementFinnancial(),
                    'adressDistrictFinnancial' => $Agency->getAdressDistrictFinnancial(),
                    'adressNumberFinnancial' => $Agency->getAdressNumberFinnancial(),
                    'zipCodeFinnancial' => $Agency->getZipCodeFinnancial(),
                    'cityFinnancialName' => $cityFinnancialName,
                    'cityFinnancialState' => $cityFinnancialState,
                    'dealer' => $dealer,
                    'dealers' => $dealers,
                    'contact' => $Agency->getContact(),
                    'useCommission' => ($Agency->getUseCommission() == 'true'),
                    'origin' => $Agency->getOrigin(),
                    'raw_email' => $Raw
                );
            }
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

        $dataset = array();
        $sql = " select b.* from businesspartner b where b.partner_type = 'C' and b.status <> 'Arquivado' ";
        $stmt = $QueryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $dataset[] = $row;
        }
        $response->setDataset($dataset);
    }

    public function saveIssuer(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['client'])) {
            $client = $dados['client'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            $em = Application::getInstance()->getEntityManager();

            if(isset($dados['id']) && $dados['id'] != '') {
                $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id'], 'partnerType' => 'S'));
            } else {
                $Issuer = new \Businesspartner();
                $Issuer->setPartnerType('S');
            }

            $Issuer->setName($dados['name']);
            if(isset($dados['commission']) && $dados['commission'] != '') {
                $Issuer->setCommission($dados['commission']);
            }
            if(isset($dados['password']) && $dados['password'] != '') {
                $Issuer->setPassword($dados['password']);
            }
            if(isset($dados['isMaster']) && $dados['isMaster'] != '') {
                $Issuer->setIsMaster($dados['isMaster']);
            }
            if(isset($dados['status']) && $dados['status'] != '') {
                $Issuer->setStatus($dados['status']);
            }

            $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['id'], 'partnerType' => 'C'));
            if($Client) {
                $Issuer->setClient($Client->getId());
            }

            $em->persist($Issuer);
            $em->flush($Issuer);

            // $Issuer = updateIssuer($Issuer, $Client->getId());

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function removeIssuer(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['client'])) {
            $client = $dados['client'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            $em = Application::getInstance()->getEntityManager();

            if(isset($dados['id']) && $dados['id'] != '') {
                $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id'], 'partnerType' => 'S'));
                $em->remove($Issuer);
                $em->flush($Issuer);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadPartnersClient(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $sql = "select b FROM Businesspartner b where b.partnerType like '%N%' and b.clientId = '".$dados['id']."' ";
        $query = $em->createQuery($sql);
        $BusinessPartner = $query->getResult();

        $dataset = array();
        foreach($BusinessPartner as $Partners){
            $City = $Partners->getCity();
            if ($City) {
                $cityfullname = $City->getName() . ', ' . $City->getState();
                $cityname = $City->getName();
                $citystate = $City->getState();
            } else {
                $cityfullname = '';
                $cityname = '';
                $citystate = '';
            }
            $birthdate = $Partners->getBirthdate();
            if($birthdate){
                $birthdate = $Partners->getBirthdate()->format('Y-m-d H:i:s');
            }else{
                $birthdate = '';
            }
            $dataset[] = array(
                'id' => $Partners->getId(),
                'name' => $Partners->getName(),
                'registrationCode' => $Partners->getRegistrationCode(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $Partners->getAdress(),
                'partnerType' => $Partners->getPartnerType(),
                'email' => $Partners->getEmail(),
                'phoneNumber' => $Partners->getPhoneNumber(),
                'phoneNumber2' => $Partners->getPhoneNumber2(),
                'phoneNumber3' => $Partners->getPhoneNumber3(),
                'status' => $Partners->getStatus(),
                'paymentType' => $Partners->getPaymentType(),
                'paymentDays' => $Partners->getPaymentDays(),
                'description' => $Partners->getDescription(),
                'birthdate' => $birthdate,
                'creditAnalysis' => $Partners->getCreditAnalysis(),
                'registrationCodeCheck' => $Partners->getRegistrationCodeCheck(),
                'adressCheck' => $Partners->getAdressCheck(),
                'creditDescription' => $Partners->getCreditDescription(),
                // 'nameBankAccount' => $Partners->getNameBankAccount(),
                // 'cpfBankAccount' => $Partners->getCpfBankAccount(),
                'account' => $Partners->getAccount(),
                'agency' => $Partners->getAgency(),
                'bank' => $Partners->getBank(),
                'zipCode' => $Partners->getZipCode(),
                'streetNumber' => $Partners->getAdressNumber(),
                'district' => $Partners->getAdressDistrict()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientToReceive(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $dados = $request->getRow();
        $apply_calc = true;
        if(isset($dados['apply_calc'])) {
            $apply_calc = $dados['apply_calc'] === 'true'? true: false;;
        }

        $botao = 'b1';
        if(isset($dados['botao'])) {
            $botao = $dados['botao'];
        }
        $today = (new \DateTime())->format('Y-m-d');

        $sql = "select distinct(b.client) as clients FROM Billsreceive b JOIN b.client c WHERE b.status = 'A' or (b.lastProcessDate BETWEEN '".(new \DateTime())->format('Y-m-d'.' 00:00:01')."' and '".(new \DateTime())->format('Y-m-d 23:59:59')."') order by c.name ";
        $query = $em->createQuery($sql);
        $Billsreceive = $query->getResult();

        $dataset = array();
        foreach($Billsreceive as $receive){
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $receive['clients']));

            $tipo_cliente = '';
            $somente_reembolso = false;
            $acessadoHoje = false;
            $condicao = true;
            $last_emission = '';
            if($apply_calc){
                $sql = "select b FROM Billsreceive b LEFT JOIN b.client c where b.status = 'A' and c.name = '".$BusinessPartner->getName()."' order by b.accountType ASC";
                $query = $em->createQuery($sql);
                $billsReceives2 = $query->getResult();

                $tem_reembolso = false;
                $tem_remarcacao = false;
                $tem_bilhete = false;
                $tem_credito = false;
                $tem_cancelamento = false;
                $somente_reembolso = true;
                foreach($billsReceives2 as $billsReceive2){
                    if($billsReceive2->getAccountType() != 'Reembolso'){
                        $somente_reembolso = false;
                    }
                    if($billsReceive2->getAccountType() == 'Remarcação'){
                        $tem_remarcacao = true;
                    }
                    if($billsReceive2->getAccountType() == 'Reembolso'){
                        $tem_reembolso = true;
                    }
                    if($billsReceive2->getAccountType() == 'Venda Bilhete'){
                        $tem_bilhete = true;
                    }
                    if($billsReceive2->getAccountType() == 'Credito'){
                        $tem_credito = true;
                    }
                    if($billsReceive2->getAccountType() == 'Cancelamento'){
                        $tem_cancelamento = true;
                    }
                }
                if($somente_reembolso && !$tem_reembolso)
                    $somente_reembolso = false;

                $tipo_cliente = '';
                if($tem_bilhete){
                    $tipo_cliente = 'Venda Bilhete';
                }
                else if($tem_remarcacao && $tem_reembolso){
                    $tipo_cliente = 'Reembolso / Remarcação';
                }
                else if($tem_remarcacao){
                    $tipo_cliente = 'Remarcação';
                }
                else if($somente_reembolso){
                    $tipo_cliente = 'Somente Reembolso';
                }
                else if($tem_cancelamento){
                    $tipo_cliente = 'Cancelamento';
                }

                $sql = "select b FROM Billsreceive b LEFT JOIN b.client c where c.name = '".$BusinessPartner->getName()."' and b.lastProcessDate BETWEEN '".(new \DateTime())->format('Y-m-d'.' 00:00:01')."' and '".(new \DateTime())->format('Y-m-d 23:59:59')."' order by b.accountType ASC";
                $query = $em->createQuery($sql);
                $billsReceives3 = $query->getResult();
                if(count($billsReceives3) > 0){
                    $acessadoHoje = true;
                }
                
                $sql = "select MAX(s.issueDate) as issueDate FROM Sale s WHERE s.client ='".$BusinessPartner->getId()."' ";
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();

                if($Sales[0]['issueDate'] != NULL) {
                    $last_emission = $Sales[0]['issueDate'];
                } else {
                    $last_emission = '';
                }

                $condicao = (
                    ($botao == 'b1')||
                    ($botao == 'b2' && $tem_bilhete)||
                    ($botao == 'b3' && !$tem_bilhete && ($tem_credito || $tem_reembolso || $tem_remarcacao))
                );
            }
            if($condicao){
                $dataset[] = array(
                    'id' => $BusinessPartner->getId(),
                    'name' => $BusinessPartner->getName(),
                    'registrationCode' => $BusinessPartner->getRegistrationCode(),
                    'adress' => $BusinessPartner->getAdress(),
                    'partnerType' => $BusinessPartner->getPartnerType(),
                    'email' => $BusinessPartner->getEmail(),
                    'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                    'phoneNumber2' => $BusinessPartner->getPhoneNumber2(),
                    'phoneNumber3' => $BusinessPartner->getPhoneNumber3(),
                    'billingPeriod' => $BusinessPartner->getBillingPeriod(),
                    'paymentType' => $BusinessPartner->getPaymentType(),
                    'last_emission' => $last_emission,
                    'somente_reembolso' => $somente_reembolso,
                    'tipo_cliente' => $tipo_cliente,
                    'acessado_hoje' => $acessadoHoje
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function allBills(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select distinct(b.client) as clients FROM Billsreceive b JOIN b.client c WHERE b.status = 'A' order by c.name ";
        $query = $em->createQuery($sql);
        $Billsreceive = $query->getResult();

        $dataset = array();
        foreach($Billsreceive as $receive){
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $receive['clients']));

            $sql = "select MAX(s.issueDate) as issueDate FROM Sale s WHERE s.client ='".$BusinessPartner->getId()."' ";
            $query = $em->createQuery($sql);
            $Sales = $query->getResult();

            if($Sales[0]['issueDate'] != NULL) {
                $last_emission = $Sales[0]['issueDate'];
            } else {
                $last_emission = '';
            }

            $bills = new BillsReceive;
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();
            $req->setRow(
                array(
                    'data' => array('clientName' => $BusinessPartner->getName())));

            $bills->loadOpenedBills($req, $resp);

            $registrationCode = $BusinessPartner->getRegistrationCode();
            $registrationType = (count($registrationCode) > 11) ? 'CPF' : 'CNPJ';

            $dataset[] = array(
                'isAllSelected'=> true,
                'id' => $BusinessPartner->getId(),
                'name' => $BusinessPartner->getName(),
                'registrationCode' => $registrationCode,
                'registrationType' => $registrationType,
                'adress' => $BusinessPartner->getAdress(),
                'zipCode' => $BusinessPartner->getZipCode(),
                'partnerType' => $BusinessPartner->getPartnerType(),
                'email' => $BusinessPartner->getEmail(),
                'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                'phoneNumber2' => $BusinessPartner->getPhoneNumber2(),
                'phoneNumber3' => $BusinessPartner->getPhoneNumber3(),
                'billingPeriod' => $BusinessPartner->getBillingPeriod(),
                'last_emission' => $last_emission,
                'paymentType' => $BusinessPartner->getPaymentType(),
                'paymentDays' => $BusinessPartner->getpaymentDays(),
                'workingDays' => $BusinessPartner->getWorkingDays(),
                'bills' => $resp->getDataSet()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadYesterdayBills(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $dados = $request->getRow();
        $apply_calc = true;
        if(isset($dados['apply_calc'])) {
            $apply_calc = $dados['apply_calc'] === 'true'? true: false;;;
        }

        /*
        $sql = "select MAX(s.id) as sale from Sale s where s.issueDate < '".(new \DateTime())->format('Y-m-d'.' 03:00:00')."' and s.status = 'Emitido' ";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();
        $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('sale' => $Sales[0]['sale']));
        */
        $days = 1;
        if((new \DateTime())->format('l') == "Monday") {
            $days = 3;
        }
        if(isset($dados['days'])) {
            $days = $dados['days'];
        }
        $today = (new \DateTime())->format('Y-m-d');

        //$sql = "select distinct(b.client) as clients FROM Billsreceive b JOIN b.client c WHERE (b.status = 'A' and b.accountType <> 'Credito' and b.id <= '".$SaleBillsreceive->getBillsreceive()->getId()."') or (b.status = 'A' and b.accountType <> 'Venda Bilhete' and b.accountType <> 'Credito' and b.dueDate BETWEEN '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d'.' 03:00:00')."' and '".(new \DateTime())->format('Y-m-d')."' and b.description not like '%REEMBOLSO REFERENTE AO BORDERO%' ) order by c.name ";
        //$sql = "select distinct(b.client) as clients FROM Billsreceive b JOIN b.client c WHERE b.status = 'A' and b.dueDate BETWEEN '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d'.' 03:00:00')."' and '".(new \DateTime())->format('Y-m-d')."' and b.description not like '%REEMBOLSO REFERENTE AO BORDERO%'  order by c.name ";
        $sql = "select distinct(b.client) as clients FROM SaleBillsreceive sb JOIN sb.billsreceive b JOIN b.client c JOIN sb.sale s  WHERE (s.issueDate BETWEEN '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d 00:00:01')."' and '".(new \DateTime())->modify('-1 day')->format('Y-m-d 23:59:59')."' and s.status = 'Emitido') order by c.name ";
        $query = $em->createQuery($sql);
        $Billsreceive = $query->getResult();

        $dataset = array();
        foreach($Billsreceive as $receive){
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $receive['clients']));

            $tipo_cliente = '';
            $somente_reembolso = false;
            $acessadoHoje = false;
            $last_emission = '';
            if($apply_calc){
                $sql = "select b FROM Billsreceive b LEFT JOIN b.client c where b.status = 'A' and c.name = '".$BusinessPartner->getName()."' order by b.accountType ASC";
                $query = $em->createQuery($sql);
                $billsReceives2 = $query->getResult();

                $tem_reembolso = false;
                $tem_remarcacao = false;
                $tem_bilhete = false;
                $tem_cancelamento = false;
                $somente_reembolso = true;
                foreach($billsReceives2 as $billsReceive2){
                    if($billsReceive2->getAccountType() != 'Reembolso'){
                        $somente_reembolso = false;
                    }
                    if($billsReceive2->getAccountType() == 'Remarcação'){
                        $tem_remarcacao = true;
                    }
                    if($billsReceive2->getAccountType() == 'Reembolso'){
                        $tem_reembolso = true;
                    }
                    if($billsReceive2->getAccountType() == 'Venda Bilhete'){
                        $tem_bilhete = true;
                    }
                    if($billsReceive2->getAccountType() == 'Cancelamento'){
                        $tem_cancelamento = true;
                    }
                }
                if($somente_reembolso && !$tem_reembolso)
                    $somente_reembolso = false;

                $sql = "select b FROM Billsreceive b LEFT JOIN b.client c where c.name = '".$BusinessPartner->getName()."' and b.lastProcessDate BETWEEN '".(new \DateTime())->format('Y-m-d'.' 00:00:01')."' and '".(new \DateTime())->format('Y-m-d 23:59:59')."' order by b.accountType ASC";
                $query = $em->createQuery($sql);
                $billsReceives3 = $query->getResult();
                if(count($billsReceives3) > 0){
                    $acessadoHoje = true;
                }

                if($tem_bilhete){
                    $tipo_cliente = 'Venda Bilhete';
                }
                else if($tem_remarcacao && $tem_reembolso){
                    $tipo_cliente = 'Reembolso / Remarcação';
                }
                else if($tem_remarcacao){
                    $tipo_cliente = 'Remarcação';
                }
                else if($somente_reembolso){
                    $tipo_cliente = 'Somente Reembolso';
                }
                else if($tem_reembolso){
                    $tipo_cliente = 'Reembolso';
                }
                else if($tem_cancelamento){
                    $tipo_cliente = 'Cancelamento';
                }
            
                $sql = "select MAX(s.issueDate) as issueDate FROM Sale s WHERE s.client ='".$BusinessPartner->getId()."' ";
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();

                if($Sales[0]['issueDate'] != NULL) {
                    $last_emission = $Sales[0]['issueDate'];
                } else {
                    $last_emission = '';
                }
            }

            $dataset[] = array(
                'id' => $BusinessPartner->getId(),
                'name' => $BusinessPartner->getName(),
                'registrationCode' => $BusinessPartner->getRegistrationCode(),
                'adress' => $BusinessPartner->getAdress(),
                'partnerType' => $BusinessPartner->getPartnerType(),
                'email' => $BusinessPartner->getEmail(),
                'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                'phoneNumber2' => $BusinessPartner->getPhoneNumber2(),
                'phoneNumber3' => $BusinessPartner->getPhoneNumber3(),
                'billingPeriod' => $BusinessPartner->getBillingPeriod(),
                'paymentType' => $BusinessPartner->getPaymentType(),
                'last_emission' => $last_emission,
                'origin' => $BusinessPartner->getOrigin(),
                'somente_reembolso' => $somente_reembolso,
                'tipo_cliente' => $tipo_cliente,
                'acessado_hoje' => $acessadoHoje
            );
        }
        $response->setDataset($dataset);
    }

    public function yesterdayBills(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $sql = "select MAX(s.id) as sale from Sale s where s.issueDate < '".(new \DateTime())->format('Y-m-d'.' 03:00:00')."' and s.status = 'Emitido' ";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('sale' => $Sales[0]['sale']));
        $days = 1;
        if((new \DateTime())->format('l') == "Monday") {
            $days = 2;
        }

        $sql = "select distinct(b.client) as clients FROM Billsreceive b JOIN b.client c WHERE (b.status = 'A' and b.accountType <> 'Credito' and b.id <= '".$SaleBillsreceive->getBillsreceive()->getId()."') or (b.status = 'A' and b.accountType <> 'Venda Bilhete' and b.accountType <> 'Credito' and b.dueDate BETWEEN '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d'.' 03:00:00')."' and '".(new \DateTime())->format('Y-m-d')."' and b.description not like '%REEMBOLSO REFERENTE AO BORDERO%' ) order by c.name ";
        $query = $em->createQuery($sql);
        $Billsreceive = $query->getResult();

        $dataset = array();
        foreach($Billsreceive as $receive){
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $receive['clients']));

            $sql = "select MAX(s.issueDate) as issueDate FROM Sale s WHERE s.client ='".$BusinessPartner->getId()."' ";
            $query = $em->createQuery($sql);
            $Sales = $query->getResult();

            if($Sales[0]['issueDate'] != NULL) {
                $last_emission = $Sales[0]['issueDate'];
            } else {
                $last_emission = '';
            }

            $bills = new BillsReceive;
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();
            $req->setRow(
                array(
                    'data' => array('clientName' => $BusinessPartner->getName())));

            $bills->loadOpenedBills($req, $resp);

            $registrationCode = $BusinessPartner->getRegistrationCode();
            $registrationType = (strlen($registrationCode) > 11) ? 'CNPJ' : 'CPF';

            $dataset[] = array(
                'isAllSelected'=> true,
                'checked' => true,
                'id' => $BusinessPartner->getId(),
                'name' => $BusinessPartner->getName(),
                'registrationCode' => $registrationCode,
                'registrationType' => $registrationType,
                'adress' => $BusinessPartner->getAdress(),
                'zipCode' => $BusinessPartner->getZipCode(),
                'partnerType' => $BusinessPartner->getPartnerType(),
                'email' => $BusinessPartner->getEmail(),
                'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                'phoneNumber2' => $BusinessPartner->getPhoneNumber2(),
                'phoneNumber3' => $BusinessPartner->getPhoneNumber3(),
                'billingPeriod' => $BusinessPartner->getBillingPeriod(),
                'last_emission' => $last_emission,
                'paymentType' => $BusinessPartner->getPaymentType(),
                'paymentDays' => $BusinessPartner->getpaymentDays(),
                'workingDays' => ($BusinessPartner->getWorkingDays() == 'true'),
                'useCommission' => ($BusinessPartner->getUseCommission() == 'true'),
                'bills' => $resp->getDataSet(),
                'origin' => $BusinessPartner->getOrigin()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadControlClient(Request $request, Response $response) {

        $em = Application::getInstance()->getEntityManager();
        $BusinessPartner = $em->getRepository('Businesspartner')->findBy(array('partnerType' => 'C'));

        $dataset = array();
        foreach($BusinessPartner as $client){

            $sql = "select SUM(b.actualValue) as receive from Billetreceive b where b.status = 'E' AND b.client = '".$client->getId()."' ";
            $query = $em->createQuery($sql);
            $Sale = $query->getResult();

            $limit = '';

            if(isset($Sale)) {
                $firstSale = array_shift($Sale);
                if($firstSale){
                    $limit = $firstSale['receive'];
                }
            }

            $dataset[] = array(
                'id' => $client->getId(),
                'name' => $client->getName(),
                'registrationCode' => $client->getRegistrationCode(),
                'status' => $client->getStatus(),
                'paymentType' => $client->getPaymentType(),
                'paymentDays' => $client->getPaymentDays(),
                'description' => $client->getDescription(),
                'creditAnalysis' => $client->getCreditAnalysis(),
                'registrationCodeCheck' => $client->getRegistrationCodeCheck(),
                'adressCheck' => $client->getAdressCheck(),
                'creditDescription' => $client->getCreditDescription(),
                'alreadGenerated' => $limit,
                'partnerLimit' => $client->getPartnerLimit()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadActualClientLimit(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('partnerType' => 'C', 'id' => $dados['id']));

        $dataset = array();
        if($BusinessPartner) {

            if($BusinessPartner->getStatus() == 'Coberto') {

                $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$BusinessPartner->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                $query = $em->createQuery($sql);
                $Limit = $query->getResult();

                $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA PGTO', 'PRIORIDADE') and o.createdAt = '".(new \DateTime())->format('Y-m-d')."' and o.clientName in (select b.name from businesspartner b where b.clientId = '".$BusinessPartner->getId()."' ) ";
                $query = $em->createQuery($sql);
                $OrdersLimit = $query->getResult();

                $sql = " select DISTINCT(sb.sale) as sale from SaleBillsreceive sb JOIN sb.billsreceive b where b.client = '".$BusinessPartner->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
                $query = $em->createQuery($sql);
                $SalesIds = $query->getResult();

                $found = "0";
                $and = ",";

                foreach ($SalesIds as $sale) {
                    $found = $found.$and.$sale['sale'];
                    $and = ', ';
                }

                $sql = " select s from Sale s where s.id in ( ".$found." ) ";
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();
                $SalesLimit = 0;

                foreach ($Sales as $sale) {
                    if($sale->getIssueDate()->diff($sale->getBoardingDate())->days >= (float)$BusinessPartner->getDaysToBoarding()) {
                        $SalesLimit += (float)$sale->getAmountPaid();
                    }
                }

                $usedValue = ((float)$Limit[0]['partner_limit'] + $SalesLimit + (float)$OrdersLimit[0]['cost']);

                $limit1 = (float)$BusinessPartner->getPartnerLimit() + (((float)$BusinessPartner->getLimitMargin() / 100) * (float)$BusinessPartner->getPartnerLimit());

            } else {

                $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$BusinessPartner->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                $query = $em->createQuery($sql);
                $Limit = $query->getResult();

                $sql = "select SUM(b.actualValue) as partner_limit FROM Billsreceive b WHERE b.client = '".$BusinessPartner->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
                $query = $em->createQuery($sql);
                $SalesLimit = $query->getResult();

                $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA PGTO', 'PRIORIDADE') and o.createdAt = '".(new \DateTime())->format('Y-m-d')."' and o.clientName in (select b.name from businesspartner b where b.clientId = '".$BusinessPartner->getId()."' ) ";
                $query = $em->createQuery($sql);
                $OrdersLimit = $query->getResult();

                $usedValue = ((float)$Limit[0]['partner_limit'] + (float)$SalesLimit[0]['partner_limit'] + (float)$OrdersLimit[0]['cost']);

                $limit1 = (float)$BusinessPartner->getPartnerLimit() + (((float)$BusinessPartner->getLimitMargin() / 100) * (float)$BusinessPartner->getPartnerLimit());

            }

            //limit 2 calculation
            $sql = "select SUM(s.amountPaid) as amountPaid FROM Sale s WHERE s.client = '".$BusinessPartner->getId()."' and s.status = 'Emitido' and s.boardingDate >= '".(new \DateTime())->modify('+19 day')->format('Y-m-d')."' ";
            $query = $em->createQuery($sql);
            $SecondyLimit = $query->getResult();

            if($SecondyLimit) {
                $limit2 = ((float)$SecondyLimit[0]['amountPaid'] * 0.6);
            } else {
                $limit2 = 0;
            }

            //total limit
            $dataset = array(
                'actual' => (float)$usedValue,
                'limit' => (float)($limit1 + $limit2)
            );

        }
        $response->setDataset($dataset);
    }

    public function loadCommercialProgress(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $sql = "select s FROM SystemLog s where s.logType = 'CLIENT' and s.description like '%->CLIENT:".$dados['id']."-%' order by s.id DESC";
        $query = $em->createQuery($sql);
        $SystemLog = $query->getResult();

        $dataset = array();
        foreach ($SystemLog as $log) {

            $description = explode("->CLIENT:".$dados['id']."-", $log->getDescription());
            $description = $description[1];

            $BusinessPartner = 'MMS VIAGENS';
            if($log->getBusinesspartner()) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()))->getName();
            }

            $dataset[] = array(
                'userName' => $BusinessPartner,
                'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                'description' => $description
            );
        }
        $response->setDataset($dataset);
    }

    public function saveCommercialProgress(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['progress'])) {
            $progress = $dados['progress'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            $em = Application::getInstance()->getEntityManager();

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \DateTime());
            $SystemLog->setDescription("->CLIENT:".$dados['id']."-".$progress['description']);
            $SystemLog->setLogType('CLIENT');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadClientContacts(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados, 'partnerType' => 'C'));

        $sql = "select b FROM Businesspartner b where b.partnerType like '%N%' and b.clientId = '".$Client->getId()."' ";
        $query = $em->createQuery($sql);
        $Partners = $query->getResult();

        $dataset = array();
        $partnersData = array();
        foreach($Partners as $partner){
            $partnersData[] = array(
                'name' => $partner->getName(),
                'registrationCode' => $partner->getRegistrationCode(),
                'email' => $partner->getEmail(),
                'phoneNumber' => $partner->getPhoneNumber(),
                'phoneNumber2' => $partner->getPhoneNumber2(),
                'phoneNumber3' => $partner->getPhoneNumber3(),
            );
        }

        $dataset = array(
            'email' => $Client->getEmail(),
            'phoneNumber' => $Client->getPhoneNumber(),
            'phoneNumber2' => $Client->getPhoneNumber2(),
            'phoneNumber3' => $Client->getPhoneNumber3()
        );
        $response->setDataset(array('client' => $dataset, 'partners' => $partnersData));
    }

    public function loadDealerOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dados = $request->getRow();
        $sql = "select s FROM Sale s JOIN s.client x JOIN s.pax p JOIN s.airline a ";
        $whereClause = ' WHERE ';
        $and = ' ';
        $orderBy = ' ORDER BY s.id DESC';

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

        if (isset($dados['_saleDateFrom']) && !($dados['_saleDateFrom'] == '')) {
            // $whereClause = $whereClause.$and. " s.issueDate >= '".$dados['_saleDateFrom']."' ";
            $whereClause = $whereClause.$and. " ( s.issueDate >= '".$dados['_saleDateFrom']."' OR ( s.refundDate >= '".$dados['_saleDateFrom']."' and s.status in ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Confirmado', 'Reembolso CIA', 'Reembolso Pendente', 'Reembolso Nao Solicitado', 'Reembolso Perdido') ) )";
            $and = ' AND ';
        };

        if (isset($dados['_saleDateTo']) && !($dados['_saleDateTo'] == '')) {
            // $whereClause = $whereClause.$and. " s.issueDate <= '".$dados['_saleDateTo']."' ";
            $whereClause = $whereClause.$and. " ( s.issueDate <= '".(new \DateTime($dados['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' OR ( s.refundDate <= '".(new \DateTime($dados['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' and s.status in ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Confirmado', 'Reembolso CIA', 'Reembolso Pendente', 'Reembolso Nao Solicitado', 'Reembolso Perdido') )) ";
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

        if (isset($dados['paxName']) && !($dados['paxName'] == '')) {
            $whereClause = $whereClause.$and. " p.name like '%".$dados['paxName']."%' ";
            $and = ' AND ';
        };

        if(isset($dados['name']) && $dados['name'] != '') {
            $DealerPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['name'], 'partnerType' => 'U_D'));
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
            }
        }

        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause;
        };

        $query = $em->createQuery($sql.$orderBy);
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

            $commission = ((float)$item->getAmountPaid() - (float)$item->getTax() - (float)$item->getDuTax());
            $status = 'Emitido';
            if($item->getStatus() == 'Reembolso Solicitado' || $item->getStatus() == 'Reembolso Pagante Solicitado' || $item->getStatus() == 'Reembolso Confirmado' || $item->getStatus() == 'Reembolso CIA' || $item->getStatus() == 'Reembolso Pendente' || $item->getStatus() == 'Reembolso Nao Solicitado' || $item->getStatus() == 'Reembolso Perdido') {
                $status = 'Reembolso';

                if($item->getRefundDate()) {
                    if($item->getRefundDate()->format('Y-m') == $item->getIssueDate()->format('Y-m')) {
                        $commission = 0;
                    } else {
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
                $age = Businesspartner::getAge($item->getPax()->getBirthdate(), $item->getBoardingDate());
                if($age < 2) {
                    $paxNmae = $paxNmae.' - COLO';
                    $commission = 0;
                }
            }

            $duTax = (float)$item->getDuTax();
            $tax = (float)$item->getTax();
            $amountPaid = (float)$item->getAmountPaid();
            if($item->getSaleByThird() == 'Y') {
                $paxNmae .= ' - Venda Pagante';
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

            $baggage_price = 0;
            if( !is_null($item->getBaggage()) ) {
                $OnlineOrder = new \MilesBench\Controller\OnlineOrder();
                if($item->getAirline() && $item->getIssuing()) {
                    $baggage_price = $OnlineOrder->getValueBaggages($item->getAirline()->getName(), $item->getBaggage(), $item->getIssuing()->getName());
                    if($commission > 0) {
                        $commission -= $baggage_price;
                    } else if($commission < 0) {
                        $commission += $baggage_price;
                    }
                }
            }

            $refundDate = '';
            if($item->getRefundDate()) {
                $refundDate = $item->getRefundDate()->format('Y-m-d H:i:s');
            }

            $dataset[] = array(
                'airline' => $airline,
                'from' => $airportFrom,
                'to' => $airportTo,
                'amountPaid' => (float)$item->getAmountPaid(),
                'paxName' => $paxNmae,
                'flightLocator' => $item->getFlightLocator(),
                'milesOriginal' => (int)$item->getMilesOriginal(),
                'client' => $item->getClient()->getName(),
                'issueDate' => $item->getIssueDate()->format('Y-m-d H:i:s'),
                'boardingDate' => $item->getBoardingDate()->format('Y-m-d H:i:s'),
                'status' => $status,
                'tax' => (float)$item->getTax(),
                'duTax' => (float)$item->getDuTax(),
                'commission' => $commission,
                'baggage_price' => $baggage_price,
                'refundDate' => $refundDate
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientAnalisys(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $queryBuilder = Application::getInstance()->getQueryBuilder();
        $dataset = array();

        $sql = "select COUNT(b) as billets FROM Billetreceive b where b.actualValue > 0 and b.client = '".$dados['id']."' ";
        $query = $em->createQuery($sql);
        $billets = $query->getResult();

        $sql = "select COUNT(*) as billets FROM billetreceive where status = 'B' and actual_value > 0 and client_id = '".$dados['id']."' and DATEDIFF(payment_date, due_date) <= 0 ";
        $stmt = $queryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $Anticipated = $row['billets'];
        }

        $sql = "select COUNT(*) as billets FROM billetreceive where status = 'B' and actual_value > 0 and client_id = '".$dados['id']."' and DATEDIFF(payment_date, due_date) > 0 and DATEDIFF(payment_date, due_date) <= 3 ";
        $stmt = $queryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $god = $row['billets'];
        }

        $sql = "select COUNT(*) as billets FROM billetreceive where status = 'B' and actual_value > 0 and client_id = '".$dados['id']."' and DATEDIFF(payment_date, due_date) > 3 and DATEDIFF(payment_date, due_date) <= 7 ";
        $stmt = $queryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $week = $row['billets'];
        }

        $sql = "select COUNT(*) as billets FROM billetreceive where status = 'B' and actual_value > 0 and client_id = '".$dados['id']."' and DATEDIFF(payment_date, due_date) > 7 ";
        $stmt = $queryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $late = $row['billets'];
        }

        $dataset = array( 'billets' => $billets[0]['billets'], 'anticipated' => $Anticipated, 'god' => $god, 'week' => $week, 'late' => $late );
        $response->setDataset($dataset);
    }

    public function loadClientDocs(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $DocumentsChecking = $em->getRepository('DocumentsChecking')->findBy(array( 'agency' => 'true'));
        foreach ($DocumentsChecking as $documents) {

            $ClientsDocuments = $em->getRepository('ClientsDocuments')->findOneBy(array( 'client' => $dados['id'], 'documents' => $documents->getId() ));
            $user = '';
            if($ClientsDocuments) {
                $datetime = $ClientsDocuments->getDatetime()->format('Y-m-d H:i:s');
                $status = ($ClientsDocuments->getStatus() == 'true' );
                if($ClientsDocuments->getBusinesspartner()) {
                    $user = $ClientsDocuments->getBusinesspartner()->getName();
                }
            } else {
                $datetime = '';
                $status = false;
            }

            $dataset[] = array(
                'document' => $documents->getName(),
                'datetime' => $datetime,
                'status' => $status,
                'id' => $documents->getId(),
                'user' => $user
            );
        }

        $response->setDataset($dataset);
    }

    public function setDocumentStatus(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['doc'])) {
            $doc = $dados['doc'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            if(isset($doc['id']) && $doc['id'] != '') {
                $DocumentsChecking = $em->getRepository('DocumentsChecking')->findOneBy(array('id' => $doc['id']));
            } else {
                $DocumentsChecking = $em->getRepository('DocumentsChecking')->findOneBy(array('name' => $doc['document']));
            }

            $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));
            $ClientsDocuments = $em->getRepository('ClientsDocuments')->findOneBy(array('documents' => $DocumentsChecking, 'client' => $Client));

            if(!$ClientsDocuments) {
                $ClientsDocuments = new \ClientsDocuments();
            }

            $ClientsDocuments->setStatus($doc['status']);
            $ClientsDocuments->setDatetime(new \DateTime());
            $ClientsDocuments->setClient($Client);
            $ClientsDocuments->setDocuments($DocumentsChecking);

            if(isset($hash)) {
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
			    $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('partnerType' => 'U', 'email' => $UserSession->getEmail()));
                $ClientsDocuments->setBusinesspartner($Businesspartner);
            }

            $em->persist($ClientsDocuments);
            $em->flush($ClientsDocuments);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadClientSuggestions(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $SomeDataFutureClients = $em->getRepository('SomeDataFutureClients')->findAll();

        $dataset = array();
        foreach ($SomeDataFutureClients as $data) {

            $paymentDays = '';
            if($data->getPaymentDays()) {
                $paymentDays = $data->getPaymentDays();
            }

            $registrationCode = '';
            if($data->getRegistrationCode()) {
                $registrationCode = $data->getRegistrationCode();
            }

            $dataset[] = array(
                'name' => $data->getName(),
                'paymentDays' => $paymentDays,
                'registrationCode' => $registrationCode
            );
        }
        $response->setDataset($dataset);
    }

    public function getClientsCreditAnalysis(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $Businesspartner = $em->getRepository('Businesspartner')->findBy(array('status' => 'Analise prazo', 'partnerType' => 'C'));

        $dataset = array();
        foreach ($Businesspartner as $client) {
            $dataset[] = array(
                'name' => $client->getName()
            );
        }
        $response->setDataset($dataset);
    }

    public function bloquedsWithNoPendency(Request $request, Response $response) {
        $queryBuilder = Application::getInstance()->getQueryBuilder();
        $sql = "select b.* from businesspartner b where b.status = 'Bloqueado' and b.partner_type = 'C' ".
            " and b.id not in (SELECT x.client_id from billetreceive x where x.client_id = b.id and x.status in ('A', 'E') and x.due_date < '" . (new \DateTime())->format('Y-m-d') . "')";

        $dataset = array();
        $stmt = $queryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $dataset[] = array(
                'id' => $row['id'],
                'name' => $row['name']
            );
        }

        $response->setDataset($dataset);
    }

    public function loadCreditAnalysisHistory(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            $em = Application::getInstance()->getEntityManager();
            $Client = $em->getRepository('Businesspartner')->findOneBy(array( 'id' => $dados['id'] ));
            $CreditAnalysis = $em->getRepository('CreditAnalysis')->findBy(array( 'client' => $dados['id'] ));

            $dataset = array();
            foreach ($CreditAnalysis as $key => $value) {
                $dataset[] = array(
                    'id' => $value->getId(),
                    'score' => $value->getScore(),
                    'registrationCodeCheck' => $value->getRegistrationCodeCheck(),
                    'adressCheck' => $value->getAdressCheck(),
                    'creditDescription' => $value->getCreditDescription()
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

    public function saveCreditAnalysisHistory(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['client'])) {
            $client = $dados['client'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            $em = Application::getInstance()->getEntityManager();
            $Client = $em->getRepository('Businesspartner')->findOneBy(array( 'id' => $client['id'] ));

            $CreditAnalysis = new \CreditAnalysis();
            $CreditAnalysis->setClient($Client);
            $CreditAnalysis->setScore($dados['creditAnalysis']);
            $CreditAnalysis->setRegistrationCodeCheck($dados['registrationCodeCheck']);
            $CreditAnalysis->setAdressCheck($dados['adressCheck']);
            $CreditAnalysis->setCreditDescription($dados['creditDescription']);

            $em->persist($CreditAnalysis);
            $em->flush($CreditAnalysis);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function removeCreditAnalysisHistory(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            $em = Application::getInstance()->getEntityManager();
            if(isset($dados['id']) && $dados['id'] != '') {
                $CreditAnalysis = $em->getRepository('CreditAnalysis')->findOneBy(array( 'id' => $dados['id'] ));

                if($CreditAnalysis) {
                    $em->remove($CreditAnalysis);
                    $em->flush($CreditAnalysis);
                }
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadClientCredits(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $sql = " select b.*, " .
            " (select MAX(s.issue_date) from sale s where s.client_id = b.id) as last_emission, " .
            " (select COUNT(s.id) from sale s where s.client_id = b.id) as countd, " .
            " d.name as dealer_name " .
            " FROM businesspartner b LEFT JOIN city c on c.id = b.city_id LEFT JOIN businesspartner d on d.id = b.dealer where b.partner_type like '%C%' and b.status<>'Arquivado' ";
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
        // order
        $orderBy = ' order by b.name ASC ';
        $sql = $sql.$orderBy;
        $sql .= " limit 0, 10";
        $stmt = $QueryBuilder->query($sql);

        $clients = array();
        while ($row = $stmt->fetch()) {
            if($row['last_emission'] == NULL) {
                $row['last_emission'] = '';
            }

            $total_credits = 0;
            if($row['payment_type'] == 'Antecipado') {
                $sql = "select SUM(b.actualValue) as value FROM Billsreceive b WHERE b.client = " . $row['id'] . " AND b.status = 'A' AND b.accountType IN ('Credito', 'Reembolso') ";
                $query = $em->createQuery($sql);
                $Billsreceive = $query->getResult();
                $total_credits += (float)$Billsreceive[0]['value'];

                $sql = "select SUM(b.actualValue) as value FROM Billsreceive b WHERE b.client = " . $row['id'] . " AND b.status = 'A' AND b.accountType IN ('Débito', 'Venda Bilhete', 'Cancelamento') ";
                $query = $em->createQuery($sql);
                $Billsreceive = $query->getResult();
                $total_credits -= (float)$Billsreceive[0]['value'];
            }

            $clients[] = array(
                'id' => $row['id'],
                'company_name' => $row['company_name'],
                'name' => $row['name'],
                'registrationCode' => $row['registration_code'],
                'adress' => $row['adress'],
                'partnerType' => $row['partner_type'],
                'email' => $row['email'],
                'phoneNumber' => $row['phone_number'],
                'phoneNumber2' => $row['phone_number2'],
                'phoneNumber3' => $row['phone_number3'],
                'status' => $row['status'],
                'paymentType' => $row['payment_type'],
                'paymentDays' => $row['payment_days'],
                'description' => $row['description'],
                'creditAnalysis' => $row['credit_analysis'],
                'registrationCodeCheck' => $row['registration_code_check'],
                'adressCheck' => $row['adress_check'],
                'creditDescription' => $row['credit_description'],
                'partnerLimit' => (float)$row['partner_limit'],
                'last_emission' => $row['last_emission'],
                'countd' => (float)$row['countd'],
                'workingDays' => $row['working_days'] == 'true',
                'secondWorkingDays' => $row['second_working_days'] == 'true',
                'secondPaymentDays' => $row['second_payment_days'],
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
                'interest' => (float) $row['interest'],
                'daysToBoarding' => (float)$row['days_to_boarding'],
                'adressFinnancial' => $row['adress_finnancial'],
                'adressComplementFinnancial' => $row['adress_complement_finnancial'],
                'adressDistrictFinnancial' => $row['adress_district_finnancial'],
                'adressNumberFinnancial' => $row['adress_number_finnancial'],
                'zipCodeFinnancial' => $row['zip_code_finnancial'],
                'dealer' => $row['dealer_name'],
                'contact' => $row['contact'],
                'useCommission' => ( $row['use_commission'] == 'true'),
                'total_credits' => $total_credits
            );
        }
        $dataset = array(
            'clients' => $clients
        );
        $response->setDataset($dataset);
    }

    public function autoRegisterClient(Request $request, Response $response) {
        $dados = $request->getRow();

        try {
            $em = Application::getInstance()->getEntityManager();
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy( array( 'registrationCode' => $dados['registration_code'], 'partnerType' => 'C' ) );
            if(!$BusinessPartner) {
                $BusinessPartner = new \Businesspartner();
                $BusinessPartner->setPartnerType('C');
            }

            if(isset($dados['hash'])){
                $Hash = $em->getRepository('HashValidation')->findOneBy( array ('hash' => $dados['hash']));
                if($Hash){
                    if($Hash->getUsed() == true){
                        throw new \Exception("Hash Ja foi Utilizada!");
                    }
                    else{
                        $Hash->setUsed(true);
                    }
                }
            }
            // if($Businesspartner) {
            //     throw new \Exception("Cliente ja cadastrado!", 1);
            // }
            $BusinessPartner->setName(mb_strtoupper($dados['name']));
            $BusinessPartner->setRegistrationCode($dados['registration_code']);
            $BusinessPartner->setCompanyName(mb_strtoupper($dados['company_name']));
            $BusinessPartner->setZipCode($dados['zip_code']);
            $BusinessPartner->setAdress(mb_strtoupper($dados['address']));
            $BusinessPartner->setAdressDistrict($dados['district']);
            $BusinessPartner->setAdressNumber($dados['number']);
            $BusinessPartner->setAdressComplement( isset($dados['complement']) ? $dados['complement'] : '' );
            $BusinessPartner->setContact($dados['contact']);
            $BusinessPartner->setBirthdate(new \DateTime($dados['submissionDate']));
            $BusinessPartner->setPhoneNumber($dados['phone']);
            $BusinessPartner->setPhoneNumber2($dados['phone_2']);
            $BusinessPartner->setPhoneNumber3($dados['phone_commercial']);
            $BusinessPartner->setEmail($dados['email_commercial']);
            $BusinessPartner->setFinnancialEmail($dados['email_financial']);
            $BusinessPartner->setDescription($dados['comments']);
            $city = $em->getRepository('City')->findOneBy(array('name' => $dados['city'], 'state' => $dados['state']));
            if(!$city) {
                $city = new \City();
                $city->setName($dados['city']);
                $city->setState($dados['state']);
                $em->persist($city);
                $em->flush($city);
            }
            $BusinessPartner->setCity($city);
            $BusinessPartner->setIsMaster(NULL);
            $BusinessPartner->setStatus('Pendente');
            if(isset($dados['dealer'])){
                $Dealer = $em->getRepository('Businesspartner')->findOneBy( array( 'id' => $dados['dealer'], 'partnerType' => 'U_D'));
                if($Dealer) {
                    $BusinessPartner->setDealer($Dealer);
                } else {
                    $Dealer = $em->getRepository('Businesspartner')->findOneBy( array( 'id' => 25379, 'partnerType' => 'U_D'));
                    if($Dealer) {
                        $BusinessPartner->setDealer($Dealer);
                    }
                }
            }
            if(isset($dados['hash'])){
                $em->persist($Hash);
                $em->flush($Hash);
            }
            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);
            $partners = $dados['partners'];
            if(isset($partners)){

                for ($i = 0; $i <= count($partners)-1; $i++) {
                    $partner = $partners[$i];

                    $associate = $em->getRepository('Businesspartner')->findOneBy( array( 'registrationCode' => $partner['registration_code'], 'partnerType' => 'N', 'clientId' => $BusinessPartner->getId() ) );
                    if(!$associate) {
                        $associate = new \Businesspartner();
                        $associate->setPartnerType('N');
                    }

                    if(isset($partner['name'])) {
                        $associate->setName(mb_strtoupper($partner['name']));
                    }
                    if (isset($partner['registration_code'])) {
                            $associate->setRegistrationCode($partner['registration_code']);
                    }
                    if (isset($partner['district'])) {
                            $associate->setAdressDistrict($partner['district']);
                    }
                    if (isset($partner['company_name'])) {
                        $associate->setCompanyName($partner['company_name']);
                    }
                    if (isset($partner['zip_code'])) {
                        $associate->setZipCode($partner['zip_code']);
                    }
                    if (isset($partner['address'])) {
                        $associate->setAdress(mb_strtoupper($partner['address']));
                    }
                    if (isset($partner['number'])) {
                        $associate->setAdressNumber($partner['number']);
                    }
                    if (isset($partner['complement'])) {
                        $associate->setAdressComplement($partner['complement']);
                    }
                    if (isset($partner['contact'])) {
                        $associate->setContact($partner['contact']);
                    }
                    if(isset($partner['submissionDate']) && $partner['submissionDate'] != '') {
                        $associate->setBirthdate(new \Datetime($partner['submissionDate']));
                    }
                    if (isset($partner['phone'])) {
                        $associate->setPhoneNumber($partner['phone']);
                    }
                    if (isset($partner['phone_2'])) {
                        $associate->setPhoneNumber2($partner['phone_2']);
                    }
                    if (isset($partner['phone_commercial'])) {
                        $associate->setPhoneNumber3($partner['phone_commercial']);
                    }
                    if (isset($partner['email_commercial'])) {
                        $associate->setEmail($partner['email_commercial']);
                    }
                    if (isset($partner['email_financial'])) {
                        $associate->setFinnancialEmail($partner['email_financial']);
                    }
                    if (isset($partner['comments'])) {
                        $associate->setDescription($partner['comments']);
                    }
                    if (isset($partner['city']) && $partner['city'] != '') {
                        $city = $em->getRepository('City')->findOneBy(array('name' => $partner['city'], 'state' => $partner['state']));
                        if(!$city){
                            $city = new \City();
                            $city->setName($partner['city']);
                            $city->setState($partner['state']);
                            $em->persist($city);
                        }
                        $associate->setCity($city);
                    }
                    if(isset($partner['description'])){
                        $associate->setDescription($partner['description']);
                    }
                    if(isset($partner['dealer'])){
                        $associate->setDealer($partner['dealer']);
                    }

                    $associate->setClient($BusinessPartner->getId());
                    $em->persist($associate);
                    $em->flush($associate);
                }
            }
            $content = '
                <h1>Confirmação de Cadastro</h1>
                <p>
                   <form>
                        <fieldset>
                            <legend>Dados do Cliente:</legend>
                            Name: '.mb_strtoupper($dados['name']).' <br>
                            CNPJ: '.$dados['registration_code'].' <br>
                            Razão Social: '.mb_strtoupper($dados['company_name']).'<br>
                            Data da Fundação: '.( date('d/m/Y', strtotime($dados['submissionDate']))).'<br>
                            Cep: '.$dados['zip_code'].'<br>
                            Endereço: '.$dados['address'].'<br>
                            Número: '.$dados['number'].'<br>
                            Complemento: '.mb_strtoupper( isset($dados['complement']) ? $dados['complement'] : '' ).'<br>
                            Bairro: '.$dados['district'].'<br>
                            Estado: '.$dados['state'].'<br>
                            Cidade: '.mb_strtoupper($dados['city']).'<br>
                            Contato na Agencia: '.mb_strtoupper($dados['contact']).'<br>
                            Telefone Celular: '.$dados['phone'].'<br>
                            Telefone Fixo: '.$dados['phone_2'].'<br>
                            Telefone Comercial: '.$dados['phone_commercial'].'<br>
                            Email para contato comercial: '.$dados['email_commercial'].'<br>
                            Email para borderos/boletos/contato financeiro: '.$dados['email_financial'].'<br>
                            Observações: '.mb_strtoupper($dados['comments']).'<br>
                        </fieldset>
                         <fieldset>
                            <legend>Dados do Sócios 1:</legend>
                            Name: '.mb_strtoupper($partners[0]['name']).' <br>
                            CPF: '.$partners[0]['registration_code'].' <br>
                            Data de Nasciemento: '.date('d/m/Y', strtotime($partners[0]['submissionDate'])).'<br>
                        </fieldset>
                        <fieldset>
                            <legend>Dados do Sócios 1:</legend>
                            Name: '.mb_strtoupper($partners[1]['name']).' <br>
                            Cpf: '.$partners[1]['registration_code'].' <br>
                            Data de Nascimento: '.date('d/m/Y', strtotime($partners[1]['submissionDate'])).'<br>
                        </fieldset>
                    </form>
                </p>
            ';
            // send grid
            $email1 = 'suporte@onemilhas.com.br';
            $postfields = array(
                'content' => $content,//corpo
                'partner' => $email1,
                'from' => $email1,
                'subject' => 'CONFIRMAÇÃO DE CADASTRO',
                'type' => '',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function registerFile(Request $request, Response $response) {
        $dados = $request->getRow();
        $registrationCode = $dados['registrationCode'];


        try {
            $em = Application::getInstance()->getEntityManager();

            if(isset($dados['data']) && $dados['data'] != ''){

                foreach ($dados['data'] as $value) {

                    if(isset($value) && $value != ''){

                        foreach ($value as $v) {
                            if(isset($v['nameFile'])){
                                $commercialDocuments = new \CommercialDocuments();
                                $commercialDocuments->setNameFile($v['nameFile']);
                                $commercialDocuments->setTypeFile($v['typeFile']);
                                $commercialDocuments->setTagBucket($v['etag']);
                                $commercialDocuments->setClientRegistrationCode($registrationCode);

                                $em->persist($commercialDocuments);
                                $em->flush($commercialDocuments);
                            }
                        }
                    }
                }
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function getFile(Request $request, Response $response) {

        $data = $request->getRow();
        $data = $data['data'];
        $registrationCodeCheck = $data['registration_code'];
        $partnerOne = $data['partners'][0];
        $partnerTwo =  $data['partners'][1];

        $em = Application::getInstance()->getEntityManager();
        $partner = array();

        $CommercialDocuments = $em->getRepository('CommercialDocuments')->findBy(array('clientRegistrationCode' => $registrationCodeCheck));
        $dataset = array();

        $complent = '';
        if(isset($data['complement']) && $data['complement'] != '' ){
            $complent = $data['complement'];
        }
        foreach($CommercialDocuments as $commercialDocuments){
            $dataset[] = array(
                'id' => $commercialDocuments->getId(),
                'name_file' => $commercialDocuments->getNameFile(),
                'type_file' => $commercialDocuments->getTypeFile(),
                'client_registration_code' => $commercialDocuments->getClientRegistrationCode(),
                'tag_bucket' => $commercialDocuments->getTagBucket(),
                'name' => $data['name'],
                'registration_code' => $data['registration_code'] ,
                'company_name' => $data['company_name'] ,
                'submissionDate' => $data['submissionDate'] ,
                'zip_code' => $data['zip_code'] ,
                'address' =>  $data['address'] ,
                'number' => $data['number'] ,
                'complement' => $complent,
                'district' => $data['district'] ,
                'state' => $data['state']  ,
                'city' => $data['city']  ,
                'contact' => $data['contact'] ,
                'phone' => $data['phone'] ,
                'phone_2' => $data['phone_2'] ,
                'phone_commercial' =>  $data['phone_commercial'] ,
                'email_commercial' => $data['email_commercial'] ,
                'email_financial ' => $data['email_financial'] ,
                'comments' => $data['comments'],
                'partnerNameOne' => $partnerOne['name'],
                'partnerNameOneRegister'  => $partnerOne['registration_code'],
                'partnerSubmissionDateOne' => $partnerOne['submissionDate'],
                'partnerTwoName' =>  $partnerTwo['name'],
                'partnerNameTwoRegister'  => $partnerTwo['registration_code'] ,
                'partnerSubmissionDateTwo' => $partnerTwo['submissionDate']
            );
        }
        $response->setDataset($dataset);
    }

    public function generateHash(Request $request, Response $response){
         $dados = $request->getRow();

        if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
        try {
            $em = Application::getInstance()->getEntityManager();
			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            $hashvalidation = new \HashValidation();
            $hashvalidation->setHash(md5(date('Y-m-d H:i:s').$UserSession->getEmail()));
            $hashvalidation->setCreatedAt(new \DateTime());
            $hashvalidation->setBusinesspartner($Businesspartner);
            $hashvalidation->setUsed(false);

            $em->persist($hashvalidation);
            $em->flush($hashvalidation);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            //Retorna algo para o post
            $response->setDataset(
                array(
                    'hash' => $hashvalidation->getHash(),
                    'link' => 'http://cadastro.idealmilhas.com.br?dealer=' . $Businesspartner->getId() . '&hash=' . $hashvalidation->getHash()
                    )
                );
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadAllSpecialBillets(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $sql = " select c.name, c.billingPeriod from Billsreceive b join b.client c where b.status in ('E') and b.accountType not in ('Credito') ".
            " and  c.billingPeriod NOT IN ('Diario', '' ) group by b.client ";
        $query = $em->createQuery($sql);
        $Billsreceive = $query->getResult();

        $dataset = array();
        foreach($Billsreceive as $client){

            $dataset[] = array(
                'name' => $client['name'],
                'billingPeriod' => $client['billingPeriod']
            );
        }
        $response->setDataset($dataset);
    }

    public static function updateClient($Client, $type = 'update') {
        $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
        $postfields = array(
            'client_id' => $Client->getId(),
            'client_name' => $Client->getName(),
            'social_name' => $Client->getCompanyName(),
            'registration_code' => $Client->getRegistrationCode(),
            'adress' => $Client->getAdress(),
            'adress_number' => $Client->getAdressNumber(),
            'adress_complement' => $Client->getAdressComplement(),
            'adress_district' => $Client->getAdressDistrict(),
            'zip_code' => $Client->getZipCode(),
            'email' => $Client->getEmail(),
            'phone_cel' => $Client->getPhoneNumber(),
            'phone_commercial' => $Client->getPhoneNumber2(),
            'phone_residential' => $Client->getPhoneNumber3(),
            'contact' => $Client->getContact(),
            'gestao' => $DirServer
        );

        if($type == 'new') {
            $postfields['usuario_name'] = self::removeAccents(mb_strtolower($Client->getPrefixo() . '.admin','UTF-8'));
            $postfields['usuario_password'] = self::removeAccents(mb_strtolower('admin123','UTF-8'));
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'novoCliente');

        $env = getenv('ENV') ? getenv('ENV') : 'production';
        if($env != 'production') {
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_homologacao . 'novoCliente');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $email1 = 'adm@onemilhas.com.br';
        $email2 = 'suporte@onemilhas.com.br';
        if($type == 'new') {
            $postfields = array(
                'content' => "<br>Novo login master criado:<br><br>Cliente: ".$Client->getName()."<br>Login: ".$postfields['usuario_name']."<br>Senha:".$postfields['usuario_password']."<br><br>att",
                'partner' => $email2,
                'from' => $email1,
                'subject' => 'Novo login Master',
                'type' => '',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);
        }


        $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
        $postfields = array(
            'status' => '99',
            'ativo' => $Client->getWhitelabel(),
            'nome' => $Client->getName(),
            'codigoMMS' => (string)$Client->getId(),
            'url' => $Client->getUrlWhitelabel(),
            'gestao' => $DirServer
        );

        if($Client->getWhitelabel() == '1') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'mms/notificar');
            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_homologacao . 'mms/notificar');
            }
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'mms/notificar');
            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_homologacao . 'mms/notificar');
            }
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        return $Client;
    }

    public static function removeAccents($string){
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç|Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$string);
	}

    public static function registerUserMoip($Client) {
        $partner = json_decode($Client->getSplitPaymentData(), true);
        if(isset($partner['email'])) {

            $postfields = $partner;
            $postfields['agencia'] = $Client->getId();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'criaContaTransparente');

            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_homologacao . 'criaContaTransparente');
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);
        }

        return true;
    }

    public function resetPassword(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
			$dados = $dados['data'];
        }

        $postfields = array(
            'agencia' => $dados['id']
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'resetaMaster');

        $env = getenv('ENV') ? getenv('ENV') : 'production';
        if($env != 'production') {
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_homologacao . 'resetaMaster');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Cadastro atualizado com sucesso');
        $response->addMessage($message);
    }

    public static function updateIssuer($issuer, $client_id) {
        $postfields = array(
            'client_id' => $client_id,
            'usuario_name' => $issuer->getName(),
            'usuario_password' => $issuer->getPassword()
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'novoCliente');

        $env = getenv('ENV') ? getenv('ENV') : 'production';
        if($env != 'production') {
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_homologacao . 'novoCliente');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        return $issuer;
    }

    public function loadClientRegistrion(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();

        $Businesspartner = $em->getRepository('Businesspartner')->find($dados['id']);

        $dataset = json_decode($Businesspartner->getSuggestionNewData());
        $response->setDataset($dataset);
    }

    public function rejectRegistration(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

        try {
            $em = Application::getInstance()->getEntityManager();

            $Businesspartner = $em->getRepository('Businesspartner')->find($dados['id']);
            $Businesspartner->setSuggestionNewData(null);

            $em->remove($Businesspartner);
            $em->flush($Businesspartner);

            $message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Rejeitado salvo com sucesso');
            $response->addMessage($message);

        } catch(\Exception $e){
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
    }

    public function acceptRegistration(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

        try {
            $em = Application::getInstance()->getEntityManager();

            $Agency = $em->getRepository('Businesspartner')->find($dados['id']);
            $array = json_decode($Agency->getSuggestionNewData());

            if(isset($dados['nome'])) {
                $Agency->setName($dados['nome']);
            }
            if(isset($dados['registrationCode'])) {
                $Agency->setRegistrationCode($dados['registrationCode']);
            }
            if(isset($dados['adress'])) {
                $Agency->setAdress($dados['adress']);
            }
            if(isset($dados['adressNumber'])) {
                $Agency->setAdressNumber($dados['adressNumber']);
            }
            if(isset($dados['adressComplement'])) {
                $Agency->setAdressComplement($dados['adressComplement']);
            }
            if(isset($dados['adressDistrict'])) {
                $Agency->setAdressDistrict($dados['adressDistrict']);
            }
            if(isset($dados['zipCode'])) {
                $Agency->setZipCode($dados['zipCode']);
            }
            if(isset($dados['phoneNumber'])) {
                $Agency->setPhoneNumber($dados['phoneNumber']);
            }
            if(isset($dados['phoneNumber2'])) {
                $Agency->setPhoneNumber2($dados['phoneNumber2']);
            }
            $Agency->setSuggestionNewData(null);

            $em->persist($Agency);
            $em->flush($Agency);

            $message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Aceitado salvo com sucesso');
            $response->addMessage($message);

        } catch(\Exception $e){
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadCupons(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();

        $Cupons = $em->getRepository('Cupons')->findBy(array(
            'client' => $dados['id']
        ));

        $dataset = array();
        foreach ($Cupons as $key => $value) {
            $dataInicio = '';
            if($value->getDataInicio()) {
                $dataInicio = $value->getDataInicio()->format('Y-m-d H:i:s');
            }

            $dataExpiracao = '';
            if($value->getDataExpiracao()) {
                $dataExpiracao = $value->getDataExpiracao()->format('Y-m-d H:i:s');
            }

            $dataset[] = array(
                'id' => $value->getId(),
                'nome' => $value->getNome(),
                'value' => (float)$value->getValue(),
                'tipo_cupom' => $value->getTipoCupom(),
                'dataInicio' => $dataInicio,
                'dataExpiracao' => $dataExpiracao,
                'used' => $value->getUsed() == '1',
                'valorMinimo' => (float)$value->getValorMinimo(),
                'quantUsos' => (int)$value->getQuantUsos()
            );
        }

        $response->setDataset($dataset);
    }

    public function saveCupons(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        try {
            $em = Application::getInstance()->getEntityManager();

            if(isset($dados['id'])) {
                $Cupons = $em->getRepository('Cupons')->find($dados['id']);
            } else {
                $Cupons = new \Cupons();
                $Cupons->setUsed(false);
                $Businesspartner = $em->getRepository('Businesspartner')->find($dados['client_id']);
                $Cupons->setClient($Businesspartner);
            }

            $Cupons->setNome($dados['nome']);
            $Cupons->setValorMinimo($dados['valorMinimo']);
            $Cupons->setTipoCupom($dados['tipo_cupom']);
            if(isset($dados['dataInicio'])) {
                $Cupons->setDataInicio(new \DateTime($dados['dataInicio']));
            }
            if(isset($dados['dataExpiracao'])) {
                $Cupons->setDataExpiracao(new \DateTime($dados['dataExpiracao']));
            }
            $Cupons->setValue($dados['value']);
            $Cupons->setQuantUsos($dados['quantUsos']);

            $em->persist($Cupons);
            $em->flush($Cupons);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}
