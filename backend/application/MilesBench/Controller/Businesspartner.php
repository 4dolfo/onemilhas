<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;
use MilesBench\Traits\PartnerLimits;

class Businesspartner {

    use PartnerLimits;

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
            if(isset($dados['origin']) && $dados['origin'] != '') {
                $BusinessPartner->setOrigin($dados['origin']);   
            }
            if(isset($dados['bankSlipSocialName']) && $dados['bankSlipSocialName'] != '') {
                $BusinessPartner->setBankSlipSocialName($dados['bankSlipSocialName']);   
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
            if(isset($dados['bankOperation'])) {
                $BusinessPartner->setBankOperation($dados['bankOperation']);
            }
            if(isset($dados['bankNameOwner'])) {
                $BusinessPartner->setBankNameOwner($dados['bankNameOwner']);
            }
            if(isset($dados['cpfNameOwner'])) {
                $BusinessPartner->setCpfNameOwner($dados['cpfNameOwner']);
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

            if(isset($notificationEmail) && $notificationEmail == true) {
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

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function checkAcessCode(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        if(isset($dados['userEmail'])){
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $dados['userEmail']));
        }

        $dataset = array();
        if(isset($dados['userEmail']) && isset($dados['userPassCode']) && $dados['userEmail'] != '' && isset($BusinessPartner)){
            if($BusinessPartner->getPassword() == $dados['userPassCode']){

                $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $BusinessPartner->getId()));

                if($BusinessPartner->getIsMaster() == "true" || $UserPermission->getChangeSale() == "true"){
                    $dataset[] = array(
                        'valid' => 'true',
                        'userEmail'=> $BusinessPartner->getEmail(),
                        'sales' => ($UserPermission->getSale() == 'true')
                    );
                } else {
                    $dataset[] = array(
                        'valid' => 'false'
                    );
                }
            } else {
                $dataset[] = array(
                    'valid' => 'false'
                );
            }
        } else if(isset($dados['pinCode'])) {
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('masterCode' => $dados['pinCode']));

            if($BusinessPartner) {

                $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $BusinessPartner->getId()));
                if($BusinessPartner->getIsMaster() == "true" || $UserPermission->getChangeSale() == "true"){
                    $dataset[] = array(
                        'valid' => 'true',
                        'userEmail'=> $BusinessPartner->getEmail(),
                        'sales' => ($UserPermission->getSale() == 'true')
                    );
                } else {
                    $dataset[] = array(
                        'valid' => 'false'
                    );
                }

            } else {
                $dataset[] = array(
                    'valid' => 'false'
                );
            }
        } else {
            $dataset[] = array(
                'valid' => 'false'
            );
        }
        $response->setDataset(array_shift($dataset));
    }

    public function loadClient(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }        

        $em = Application::getInstance()->getEntityManager();
        $conn = Application::getInstance()->getQueryBuilder();
        $dataset = array();

        if(isset($dados['agencia_id']) ) {
            $partner = $em->getRepository('Businesspartner')->find( $dados['agencia_id'] );
        }

        $OnlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        if(!isset($partner)) {
            if(isset($dados['client_login'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client_login'], 'partnerType' => 'S'));
            }
            if(isset($BusinessPartner)) {
                $partner = $em->getRepository('Businesspartner')->find( $BusinessPartner->getClient() );
            }
        }

        $partnerLimit = 'false';
        $emailLimit = false;

        $dataset = null;
        if(isset($partner)) {
            if((float)$partner->getPartnerLimit() != '0'){

                if($partner->getStatus() == 'Coberto') {

                    $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$partner->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                    $query = $em->createQuery($sql);
                    $Limit = $query->getResult();

                    $sql = "SELECT SUM(orders.total_cost) AS cost FROM ( SELECT DISTINCT o.airline, o.miles_used, o.total_cost, o.status, o.client_email, o.client_name, o.commercial_status, f.boarding_date, f.landing_date FROM online_order o JOIN online_flight AS f ON f.order_id=o.id WHERE o.status IN ('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE','ESPERA LIM VLR', 'ANT BLOQ', 'ANT', 'BLOQ', 'SITE_CIA_FORA_AR') AND o.client_name IN ( SELECT b.name FROM businesspartner b WHERE b.client_id = '".$partner->getId()."' ) ) AS orders";
                    $stmt = $conn->query($sql);
                    while ($row = $stmt->fetch()) {
                        $OrdersLimit = $row['cost'];
                    }

                    $sql = " select DISTINCT(sb.sale) as sale from SaleBillsreceive sb JOIN sb.billsreceive b where b.client = '".$partner->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
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
                        if($sale->getIssueDate()->diff($sale->getBoardingDate())->days > (float)$partner->getDaysToBoarding()) {
                            $SalesLimit += (float)$sale->getAmountPaid();
                        }
                    }

                    $usedValue = ((float)$Limit[0]['partner_limit'] + $SalesLimit + (float)$OrdersLimit);

                    $limit1 = (float)$partner->getPartnerLimit() + (((float)$partner->getLimitMargin() / 100) * (float)$partner->getPartnerLimit());
                } else if($partner->getStatus() != 'Antecipado') {

                    //limit 1 calculation
                    $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$partner->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                    $query = $em->createQuery($sql);
                    $Limit = $query->getResult();

                    $sql = "select SUM(b.actualValue) as partner_limit FROM Billsreceive b WHERE b.client = '".$partner->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
                    $query = $em->createQuery($sql);
                    $SalesLimit = $query->getResult();

                    $sql = "SELECT SUM(orders.total_cost) AS cost FROM ( SELECT DISTINCT o.airline, o.miles_used, o.total_cost, o.status, o.client_email, o.client_name, o.commercial_status, f.boarding_date, f.landing_date FROM online_order o JOIN online_flight AS f ON f.order_id=o.id WHERE o.status IN ('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE','ESPERA LIM VLR', 'ANT BLOQ', 'ANT', 'BLOQ', 'SITE_CIA_FORA_AR') AND o.client_name IN ( SELECT b.name FROM businesspartner b WHERE b.client_id = '".$partner->getId()."' ) group by o.id ) AS orders";
                    $stmt = $conn->query($sql);
                    while ($row = $stmt->fetch()) {
                        $OrdersLimit = $row['cost'];
                    }

                    $usedValue = ((float)$Limit[0]['partner_limit'] + (float)$SalesLimit[0]['partner_limit'] + (float)$OrdersLimit);

                    $limit1 = (float)$partner->getPartnerLimit() + (((float)$partner->getLimitMargin() / 100) * (float)$partner->getPartnerLimit());

                    //limit 2 calculation
                    $sql = "select SUM(s.amountPaid) as amountPaid FROM Sale s WHERE s.client = '".$partner->getId()."' and s.status = 'Emitido' and s.boardingDate >= '".(new \DateTime())->modify('+19 day')->format('Y-m-d')."' ";
                    $query = $em->createQuery($sql);
                    $SecondyLimit = $query->getResult();

                    if($SecondyLimit) {
                        $limit2 = ((float)$SecondyLimit[0]['amountPaid'] * 0.6);
                    } else {
                        $limit2 = 0;
                    }

                    //total limit
                    $totalLimit = $limit1 + $limit2;

                    if($usedValue / $totalLimit > 0.8) {
                        $emailLimit = true;
                    }
                    if($totalLimit < $usedValue){
                        $partnerLimit = 'true';
                    }
                    if($partnerLimit == 'true') {
                        $content = "Limite negado para o parceiro " . $partner->getName() . " - ID: " . $partner->getId() . " <br />";
                        $content .= "Limite 1 = " . (float)$Limit[0]['partner_limit'] . " + " . (float)$SalesLimit[0]['partner_limit'] . " + " . (float)$OrdersLimit . " <br />";
                        $content .= "Limite Secundário = " . (float)$SecondyLimit[0]['amountPaid'] . " * 0.6 <br />";
                        $content .= "Limite usado: " . $usedValue . " (total da soma do limite 1 + limite secundário) | Limite permitido total =  " . $totalLimit . "<br />";

                        $email1 = 'emissao@onemilhas.com.br';
                        $email2 = 'adm@onemilhas.com.br';
                        $postfields = array(
                            'content' => $content,
                            'from' => $email1,
                            'partner' => $email2,
                            'subject' => 'ANALISE DE LIMITE - LIMITE NEGADO',
                            'type' => ''
                        );

                    } else {
                        $content = "Limite aceito para o parceiro " . $partner->getName() . " - ID: " . $partner->getId() . " <br />";
                        $content .= "Limite 1 = " . (float)$Limit[0]['partner_limit'] . " + " . (float)$SalesLimit[0]['partner_limit'] . " + " . (float)$OrdersLimit . " <br />";
                        $content .= "Limite Secundário = " . (float)$SecondyLimit[0]['amountPaid'] . " * 0.6 <br />";
                        $content .= "Limite usado: " . $usedValue . " (total da soma do limite 1 + limite secundário) | Limite permitido total =  " . $totalLimit . "<br />";

                        $email1 = 'emissao@onemilhas.com.br';
                        $email2 = 'adm@onemilhas.com.br';
                        $postfields = array(
                            'content' => $content,
                            'from' => $email1,
                            'partner' => $email2,
                            'subject' => 'ANALISE DE LIMITE - LIMITE ACEITO',
                            'type' => ''
                        );
                    }

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);

                }
            }

            $status = $partner->getStatus();
            $paymentType = $partner->getPaymentType();
            if($partner->getStatus() == 'Coberto') {
                $paymentType = 'Coberto';
                $OnlineFlight = $em->getRepository('OnlineFlight')->findBy(array('order' => $dados['id']));
                foreach ($OnlineFlight as $flight) {
                    if((new \DateTime('00:00:00'))->diff($flight->getBoardingDate())->days < (float)$partner->getDaysToBoarding()) {
                        $paymentType = 'Antecipado';
                    }
                }
            }

            $dealer = '';
            $dealerEmails = '';
            $andDealer = '';
            if($partner->getDealer()) {
                $dealer = $dealer.$andDealer.$partner->getDealer()->getName();
                $dealerEmails = $dealerEmails.$andDealer.$partner->getDealer()->getEmail();
                $andDealer = ';';
            }

            $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('client' => $partner->getId()));
            foreach ($ClientsDealers as $dealerClient) {
                $dealer = $dealer.$andDealer.$dealerClient->getDealer()->getName();
                $dealerEmails = $dealerEmails.$andDealer.$dealerClient->getDealer()->getEmail();
                $andDealer = ';';
            }

            if($partner->getCity()) {
                if($partner->getCity()->getState() == 'RJ') {
                    $dealer = 'Rafael Valadares';
                    $dealerEmails = 'rafael.valadares@uaimilhas.com.br';
                }
            }

            if($status == 'Arquivado' || $status == 'Analise prazo' || $status == 'Pendente Liberacao') {
                $status = 'Pendente';
            }

            $subClientEmail = null;
            if($partner->getSubClient() == 'true') {
                $subClientEmail = $partner->getMasterClient()->getEmail();
            }

            $total_credits = 0;
            if($paymentType == 'Antecipado') {
                $sql = "select SUM(b.actualValue) as value FROM Billsreceive b WHERE b.client = " . $partner->getId() . " AND b.status = 'A' AND b.accountType IN ('Credito', 'Reembolso') ";
                $query = $em->createQuery($sql);
                $Billsreceive = $query->getResult();
                $total_credits += (float)$Billsreceive[0]['value'];
    
                $sql = "select SUM(b.actualValue) as value FROM Billsreceive b WHERE b.client = " . $partner->getId() . " AND b.status = 'A' AND b.accountType IN ('Débito', 'Venda Bilhete', 'Cancelamento') ";
                $query = $em->createQuery($sql);
                $Billsreceive = $query->getResult();
                $total_credits -= (float)$Billsreceive[0]['value'];
            }

            $dataset = array(
                'client_name' => $partner->getName(),
                'status' => $status,
                'paymentType' => $paymentType,
                'partner_limit' => $partnerLimit,
                'usedValue' => (isset($usedValue) ? $usedValue : ''),
                'totalLimit' => (isset($totalLimit) ? $totalLimit : ''),
                'dealer' => $dealer,
                'dealerEmails' => $dealerEmails,
                'subClientEmail' => $subClientEmail,
                'origin' => $partner->getOrigin(),
                'total_credits' => $total_credits,
                'notificationurl' => $dados['notificationurl']
            );
        }

        if($OnlineOrder->getPaymentMethod()) {
            if($OnlineOrder->getPaymentMethod() == 'Cartao' || $OnlineOrder->getPaymentMethod() == 'Deposito') {
                $dataset['paymentType'] = 'Boleto';
                $dataset['partner_limit'] = 'false';
                $dataset['status'] = 'Aprovado';
            }
        }

        $response->setDataset($dataset);
    }

    public function checkLimitSale(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client_name']));

        $dataset = array();
        if(isset($BusinessPartner)){

            $partnerLimit = 'false';
            $emailLimit = false;
            if((float)$BusinessPartner->getPartnerLimit() != '0'){

                if($BusinessPartner->getStatus() == 'Coberto') {

                    $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$BusinessPartner->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                    $query = $em->createQuery($sql);
                    $Limit = $query->getResult();

                    $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA') and o.createdAt = '".(new \DateTime())->format('Y-m-d')."' and o.clientName in (select b.name from partner b where b.clientId = '".$BusinessPartner->getId()."' ) ";
                    $query = $em->createQuery($sql);
                    $OrdersLimit = $query->getResult();

                    $sql = " select s from Sale s where s.earlyCovered = 'true' ";
                    $query = $em->createQuery($sql);
                    $Sales = $query->getResult();
                    $SalesLimit = 0;

                    foreach ($Sales as $sale) {
                        if($sale->getIssueDate()->diff($sale->getBoardingDate())->days > (float)$BusinessPartner->getDaysToBoarding()) {
                            $SalesLimit += (float)$sale->getAmountPaid();
                        }
                    }

                    $usedValue = ((float)$Limit[0]['partner_limit'] + $SalesLimit + (float)$OrdersLimit[0]['cost'] + (float)$dados['totalValue']);

                    $limit1 = (float)$BusinessPartner->getPartnerLimit() + (((float)$BusinessPartner->getLimitMargin() / 100) * (float)$BusinessPartner->getPartnerLimit());
                } else {

                    //limit 1 calculation
                    $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$BusinessPartner->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                    $query = $em->createQuery($sql);
                    $Limit = $query->getResult();

                    $sql = "select SUM(b.actualValue) as partner_limit FROM Billsreceive b WHERE b.client = '".$BusinessPartner->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
                    $query = $em->createQuery($sql);
                    $SalesLimit = $query->getResult();

                    $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA') and o.createdAt = '".(new \DateTime())->format('Y-m-d')."' and o.clientName in (select b.name from businesspartner b where b.clientId = '".$BusinessPartner->getId()."' ) ";
                    $query = $em->createQuery($sql);
                    $OrdersLimit = $query->getResult();

                    $usedValue = ((float)$Limit[0]['partner_limit'] + (float)$SalesLimit[0]['partner_limit'] + (float)$OrdersLimit[0]['cost'] + (float)$dados['total_cost'] + (float)$dados['totalValue']);

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
                $totalLimit = $limit1 + $limit2;

                if($usedValue / $totalLimit > 0.8) {
                    $emailLimit = true;
                }
                if($totalLimit < $usedValue){
                    $partnerLimit = 'true';
                }
            }

            $status = $BusinessPartner->getStatus();
            $paymentType = $BusinessPartner->getPaymentType();
            
            if($status == 'Arquivado' || $status == 'Analise prazo' || $status == 'Pendente Liberacao') {
                $status = 'Pendente';
            }

            if($BusinessPartner->getStatus() == 'Coberto') {
                $paymentType = 'Coberto';
                $OnlineFlight = $em->getRepository('OnlineFlight')->findBy(array('order' => $dados['id']));
                foreach ($OnlineFlight as $flight) {
                    if((new \DateTime('00:00:00'))->diff($flight->getBoardingDate())->days < (float)$BusinessPartner->getDaysToBoarding()) {
                        $paymentType = 'Antecipado';
                    }
                }
            }

            $dataset[] = array(
                'client_name' => $BusinessPartner->getName(),
                'status' => $status,
                'paymentType' => $paymentType,
                'partner_limit' => $partnerLimit
            );

        }
        $response->setDataset(array_shift($dataset));
    }

    public function remove(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        try {
            $em = Application::getInstance()->getEntityManager();

            $partners = $em->getRepository('Businesspartner')->findOneBy(array('client' => $dados['id']));
            foreach ($partners as $partner) {
                $em->remove($partner);
                $em->flush($partner);
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->find($dados['id']);
            $em->remove($BusinessPartner);
            $em->flush($BusinessPartner);

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

    public function saveFile(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['file'])) {
            $file = $dados['file'];
        }

        try {

            if(isset($dados['id'])){
                $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
                $extension = explode('.', $file_name);
                $replace = 0;

                try {
                    $s3 = new \Aws\S3\S3Client([
                        'version' => 'latest',
                        'region'  => 'us-east-1',
                        'credentials' => array(
                            'key' => getenv('AWS_KEY'),
                            'secret'  => getenv('AWS_SECRET')
                        )
                    ]);

                    $bucket = 'clients-mmsgestao';
                    $keyname = $dados['id'] . '/' . $extension[0] . '.' . $extension[1];
                    $filepath = $file['tmp_name'];

                    $result = $s3->putObject(array(
                        'Bucket' => $bucket,
                        'Key'    => $keyname,
                        'SourceFile' => $filepath,
                        'Body'   => '',
                        'ACL'    => 'public-read'
                    ));
                } catch (S3Exception $e) {
                    var_dump($e);die;
                }

                $em = Application::getInstance()->getEntityManager();
                $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));

                $Midia = new \Midia();
                $Midia->setUrl($result['ObjectURL']);
                $Midia->setKeyname($keyname);
                $Midia->setBusinesspartner($Client);

                $em->persist($Midia);
                $em->flush($Midia);
            } else {
                if(is_dir(getcwd()."/MilesBench/files/temp")) {
                    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

                    if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
                    move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
                } else {
                    mkdir(getcwd()."/MilesBench/files/temp", 0777 , true);
                    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

                    if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
                    move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
                }                
            }
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Arquivo(s) salvos com sucesso!');
            $response->addMessage($message);

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadFiles(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $scanned_directory = array();

        $em = Application::getInstance()->getEntityManager();
        $Midia = $em->getRepository('Midia')->findBy(array('businesspartner' => $dados['id']));
        foreach ($Midia as $key => $value) {
            $scanned_directory[] = array(
                'id' => $value->getId(),
                'keyname' => $value->getKeyname(),
                'url' => $value->getUrl()
            );
        }
        $response->setDataset($scanned_directory);
    }

    public function removeFile(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['file'])) {
            $file = $dados['file'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            if(is_dir(getcwd()."/MilesBench/files/".$dados['id'])) {
                $path = getcwd()."/MilesBench/files/".$dados['id'];
                unlink($path.'/'.$file);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Arquivo removido com sucesso!');
            $response->addMessage($message);

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveClientStatus(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {

            $em = Application::getInstance()->getEntityManager();

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));
            $BusinessPartner->setStatus($dados['status']);

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Arquivo removido com sucesso!');
            $response->addMessage($message);

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function removeUserSession() {
        $em = Application::getInstance()->getEntityManager();

        $sql = "select o FROM OnlineOrder o WHERE o.userSession <> '' ";
        $query = $em->createQuery($sql);
        $OnlineOrder = $query->getResult();
        foreach ($OnlineOrder as $order) {
            $order->setUserSession('');
            $em->persist($order);
            $em->flush($order);
        }

        $sql = "select c FROM Cards c WHERE c.userSession <> '' ";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();
        foreach ($OnlineOrder as $card) {
            $card->setUserSession('');
            $card->setUserSessionDate(null);
            $em->persist($card);
            $em->flush($card);
        }
    }

    public function searchRegistrationCode(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['partnerType'])) {
            $partnerType = $dados['partnerType'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        if(isset($dados['registrationCode']) && $dados['registrationCode']) {
            if(isset($partnerType)) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                    array(
                        'registrationCode' => $dados['registrationCode'],
                        'partnerType' => $partnerType
                    )
                );
            } else {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                    array(
                        'registrationCode' => $dados['registrationCode']
                    )
                );
            }
            if($BusinessPartner) {
                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::SUCCESS);
                $message->setText("Usuario Registrado");
                $response->addMessage($message);
            } else {
                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::ERROR);
                $message->setText("Usuario Não Registrado");
                $response->addMessage($message);
            }
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
            if(isset($dados['paymentDays']) && !empty($dados['paymentDays'])){
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

    public static function getAge($birthDate, $currentDate) {
        if(!isset($currentDate)) {
            $currentDate = new \DateTime();
        }

        $birthDate = explode("/", $birthDate->format('m/d/Y'));

        //get age from boarding or birthdate
        $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > $currentDate->format('md')
        ? (($currentDate->format('Y') - $birthDate[2]) - 1)
        : ($currentDate->format('Y') - $birthDate[2]));
        return $age;
    }

    public function checkAcessCodeComercial(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        if(isset($dados['userEmail'])){
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $dados['userEmail']));
        }

        $dataset = array();
        if(isset($dados['pinCode'])) {
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('masterCode' => $dados['pinCode']));

            if($BusinessPartner) {

                $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $BusinessPartner->getId()));
                if($BusinessPartner->getIsMaster() == "true" || $UserPermission->getCommercial() == "true"){
                    $dataset[] = array(
                        'valid' => 'true',
                        'userEmail'=> $BusinessPartner->getEmail()
                    );
                } else {
                    $dataset[] = array(
                        'valid' => 'false'
                    );
                }

            } else {
                $dataset[] = array(
                    'valid' => 'false'
                );
            }
        } else {
            $dataset[] = array(
                'valid' => 'false'
            );
        }
        $response->setDataset(array_shift($dataset));
    }

    public function getUserPermissions(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            $em->getConnection()->beginTransaction();

            $dataset = array();
            if($dados['user_id']) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['user_id']));
                if($BusinessPartner) {

                    $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $BusinessPartner->getId()));

                    if($UserPermission) {
                        $dataset = array(
                            'purchase' => ($UserPermission->getPurchase() == 'true'),
                            'wizardPurchase' => ($UserPermission->getWizardPurchase() == 'true'),
                            'sale' => ($UserPermission->getSale() == 'true'),
                            'wizardSale' => ($UserPermission->getWizardSale() == 'true'),
                            'milesBench' => ($UserPermission->getMilesBench() == 'true'),
                            'financial' => ($UserPermission->getFinancial() == 'true'),
                            'creditCard' => ($UserPermission->getCreditCard() == 'true'),
                            'users' => ($UserPermission->getUsers() == 'true'),
                            'changeMiles' => ($UserPermission->getChangeMiles() == 'true'),
                            'changeSale' => ($UserPermission->getChangeSale() == 'true'),
                            'commercial' => ($UserPermission->getCommercial() == 'true'),
                            'permission' => ($UserPermission->getPermission() == 'true'),
                            'dealer' => (strpos($BusinessPartner->getPartnerType(), "D") !== false),
                            'pagseguro' => ($UserPermission->getPagseguro() == 'true'),
                            'internRefund' => ($UserPermission->getInternRefund() == 'true'),
                            'internCommercial' => ($UserPermission->getInternCommercial() == 'true'),
                            'humanResources' => ($UserPermission->getHumanResources() == 'true'),
                            'salePlansEdit' => ($UserPermission->getSalePlansEdit() == 'true'),
                            'conference' => ($UserPermission->getConference() == 'true'),
                            'isMaster' => ($BusinessPartner->getIsMaster() == 'true')
                        );
                    }
                }
            }

            $em->getConnection()->rollback();
            $response->setDataset($dataset);
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

    public static function from_camel_case($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
          $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
      }
}

function getBool($bool){
    if($bool == 'true' || $bool == true){
        return true;
    } else {
        return false;
    }
}
