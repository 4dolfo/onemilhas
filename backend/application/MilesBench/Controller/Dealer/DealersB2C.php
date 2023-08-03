<?php

namespace MilesBench\Controller\Dealer;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;

class DealersB2C {

    public function saveDealer(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
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
            } else {
                $BusinessPartner = new \Businesspartner();
                $BusinessPartner->setPartnerType('U_D');
                $BusinessPartner->setName(mb_strtoupper($dados['name']));
                $em->persist($BusinessPartner);

                $UserPermission = new \UserPermission();
                $UserPermission->setUser($BusinessPartner);
                $UserPermission->setCommercial('true');
                $em->persist($UserPermission);
                $em->flush($UserPermission);
            }

            $BusinessPartner->setName(mb_strtoupper($dados['name']));
            $BusinessPartner->setEmail($dados['email']);
            if (isset($dados['registrationCode'])) {
                $BusinessPartner->setRegistrationCode($dados['registrationCode']);
            }
            $BusinessPartner->setAcessName($dados['name']);
            if($dados['commission']) {
                $BusinessPartner->setCommission($dados['commission']);
            }
            if($dados['systemName']) {
                $BusinessPartner->setSystemName($dados['systemName']);
            }

            if (isset($dados['password']) && $dados['password'] != '') {
                if($BusinessPartner->getPassword() !== $dados['password']) {
                    if($BusinessPartner->getId()) {
                        $BusinessPartner->setPassword($dados['password']);
                        $BusinessPartner->setLastPasswordDate(new \DateTime());
                    }
                }
            }
            $BusinessPartner->setIsMaster(NULL);
            if (isset($dados['status'])) {
                $BusinessPartner->setStatus($dados['status']);
            }

            $BusinessPartner->setMasterClient($UserPartner);

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

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

    public function loadDealers(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select b.* FROM businesspartner b where b.partner_type like '%U_D%' ";

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." b.id like '%".$dados['searchKeywords']."%' or "
                ." b.company_name like '%".$dados['searchKeywords']."%' or "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registration_code like '%".$dados['searchKeywords']."%' or "
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
        
        $sql = $sql.' AND b.master_client = '.$UserPartner->getId().' ';
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            $stmt = $QueryBuilder->query($sql);
        }
        
        $clients = array();
        while ($row = $stmt->fetch()) {

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
                'password' => $row['password'],
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
                'logoUrlSmall' => $row['logo_url_small'],
                'prefixo' => $row['prefixo'],
                'whitelabel' => $row['whitelabel'] == '1',
                'urlWhitelabel' => $row['url_whitelabel'],
                'partnerData' => json_decode($row['split_payment_data'], true),
                'commission' => (float)$row['commission']
            );
        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where = " and ( "
                ." b.id like '%".$dados['searchKeywords']."%' or "
                ." b.companyName like '%".$dados['searchKeywords']."%' or "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCode like '%".$dados['searchKeywords']."%' or "
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

                $sql = "select COUNT(b) as quant FROM Businesspartner b where b.partnerType like '%U_D%' ".$where;

        } else {
            $sql = "select COUNT(b) as quant FROM Businesspartner b where b.partnerType like '%U_D%' ";
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'dealers' => $clients,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function saveSubDealers(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if (isset($dados['subDealer'])) {
            $subDealer = $dados['subDealer'];
        }
        if (isset($dados['data'])) {
			$dados = $dados['data'];
		}
        $warning = false;
        $changes = "";
        $env = getenv('ENV') ? getenv('ENV') : 'production';

        try {
            $em = Application::getInstance()->getEntityManager();
            if (isset($subDealer['id'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->find($subDealer['id']);
            } else {
                $BusinessPartner = new \Businesspartner();
                $BusinessPartner->setPartnerType('S_D');
                $BusinessPartner->setName(mb_strtoupper($subDealer['name']));
                $em->persist($BusinessPartner);

                $UserPermission = new \UserPermission();
                $UserPermission->setUser($BusinessPartner);
                $UserPermission->setCommercial('true');
                $em->persist($UserPermission);
                $em->flush($UserPermission);
            }

            $BusinessPartner->setName(mb_strtoupper($subDealer['name']));
            if (isset($subDealer['registrationCode'])) {
                $BusinessPartner->setRegistrationCode($subDealer['registrationCode']);
            }
            $BusinessPartner->setAcessName($subDealer['name']);
            $BusinessPartner->setIsMaster(NULL);
            if (isset($subDealer['status'])) {
                $BusinessPartner->setStatus($subDealer['status']);
            }

            if(isset($dados['id']) && $dados['id'] != '') {
                $BusinessPartner->setSubClient('true');
                $masterClient = $em->getRepository('Businesspartner')->find($dados['id']);
                if($masterClient) {
                    $BusinessPartner->setMasterClient($masterClient);
                }
            }

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

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

    public function loadSubDealers(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select b.* FROM businesspartner b where b.partner_type like '%S_D%' and b.master_client = ".$dados['id']." ";
        $stmt = $QueryBuilder->query($sql);
        
        $dataset = array();
        while ($row = $stmt->fetch()) {

            $dataset[] = array(
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
                'contact' => $row['contact'],
                'useCommission' => ( $row['use_commission'] == 'true'),
                'subClient' => ( $row['sub_client'] == 'true'),
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

        $response->setDataset($dataset);
    }
}