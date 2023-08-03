<?php
/**
 * Created by PhpStorm.
 * User: robertomartins
 * Date: 1/19/2015
 * Time: 11:04 PM
 */

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Provider {

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." b.id like '%".$dados['searchKeywords']."%' or "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCode like '%".$dados['searchKeywords']."%' or "
                ." b.adress like '%".$dados['searchKeywords']."%' or "
                ." b.email like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber2 like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber3 like '%".$dados['searchKeywords']."%' or "
                ." b.status like '%".$dados['searchKeywords']."%' or "
                ." b.bank like '%".$dados['searchKeywords']."%' or "
                ." b.agency like '%".$dados['searchKeywords']."%' or "
                ." b.account like '%".$dados['searchKeywords']."%' or "
                ." b.blockReason like '%".$dados['searchKeywords']."%' or "
                ." b.paymentType like '%".$dados['searchKeywords']."%' or "
                ." b.description like '%".$dados['searchKeywords']."%' or "
                ." b.creditAnalysis like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCodeCheck like '%".$dados['searchKeywords']."%' or "
                ." b.adressCheck like '%".$dados['searchKeywords']."%' or "
                ." b.creditDescription like '%".$dados['searchKeywords']."%' or "
                ." b.companyName like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumberAirline like '%".$dados['searchKeywords']."%' or "
                ." b.celNumberAirline like '%".$dados['searchKeywords']."%' or "
                ." b.typeSociety like '%".$dados['searchKeywords']."%' or "
                ." b.nameMother like '%".$dados['searchKeywords']."%' ) ";
                // ." c.name like '%".$dados['searchKeywords']."%' or "
                // ." c.state like '%".$dados['searchKeywords']."%' ) ";

            // $sql = "select b FROM Businesspartner b JOIN b.city c WHERE b.partnerType like '%P%' ".$where;
        }

        $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%P%' ".$where;

        if(isset($dados['data'])) {
            if(isset($dados['data']['findSRM'])) {
                if($dados['data']['findSRM'] == 'true') {
                    $sql .= " and b.name like 'SRM %' ";
                } else {
                    $sql .= " and b.name NOT LIKE 'SRM %' ";
                }
            } else {
                $sql .= " and b.name NOT LIKE 'SRM %' ";
            }
        } else {
            $sql .= " and b.name NOT LIKE 'SRM %' ";
        }

         // order
        $orderBy = '';
        if(isset($dados['order']) && $dados['order'] != '') {
            $orderBy = ' order by b.'.$dados['order'].' ASC ';
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            $orderBy = ' order by b.'.$dados['orderDown'].' DESC ';
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                ->setMaxResults($dados['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }

        $BusinessPartner = $query->getResult();
        $partner = false;

        $providers = array();
        foreach($BusinessPartner as $Provider){
            $City = $Provider->getCity();
            if ($City) {
                $cityfullname = $City->getName() . ', ' . $City->getState();
                $cityname = $City->getName();
                $citystate = $City->getState();
            } else {
                $cityfullname = '';
                $cityname = '';
                $citystate = '';
            }

            if ($Provider->getPartnerType() == 'PM') {
               $partner = true; 
            }

            $birthdate = '';
            if($Provider->getBirthdate()) {
                $birthdate = $Provider->getBirthdate()->format('Y-m-d');
            }

            $registerDate = '';
            if($Provider->getRegisterDate()) {
                $registerDate = $Provider->getRegisterDate()->format('Y-m-d');
            }

            $nameMother = '';
            if($Provider->getNameMother() != NULL) {
                $nameMother = $Provider->getNameMother();
            }

            $associatedProvider = '';
            if($Provider->getClient() != NULL) {
                $associatedProviderBusinessPartner = $em->getRepository('Businesspartner')->findOneBy( array( 'id' => $Provider->getClient() ) );
                if($associatedProviderBusinessPartner) {
                    $associatedProvider = $associatedProviderBusinessPartner->getName();
                }
            }

            $providers[] = array(
                'id' => $Provider->getId(),
                'name' => $Provider->getName(),
                'registrationCode' => $Provider->getRegistrationCode(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $Provider->getAdress(),
                'partnerType' => $Provider->getPartnerType(),
                'is_partner' => $partner,
                'email' => $Provider->getEmail(),
                'phoneNumber' => $Provider->getPhoneNumber(),
                'phoneNumber2' => $Provider->getPhoneNumber2(),
                'phoneNumber3' => $Provider->getPhoneNumber3(),
                'status' => $Provider->getStatus(),
                'bank' => $Provider->getBank(),
                'agency' => $Provider->getAgency(),
                'account' => $Provider->getAccount(),
                'blockreason' => $Provider->getBlockReason(),
                'paymentType' => $Provider->getPaymentType(),
                'description' => $Provider->getDescription(),
                'creditAnalysis' => $Provider->getCreditAnalysis(),
                'registrationCodeCheck' => $Provider->getRegistrationCodeCheck(),
                'adressCheck' => $Provider->getAdressCheck(),
                'creditDescription' => $Provider->getCreditDescription(),
                'company_name' => $Provider->getCompanyName(),
                'birthdate' => $birthdate,
                'registerDate' => $registerDate,
                'phoneNumberAirline' => $Provider->getPhoneNumberAirline(),
                'celNumberAirline' => $Provider->getCelNumberAirline(),
                'nameMother' => $nameMother,
                'typeSociety' => $Provider->getTypeSociety(),
                'associatedProvider' => $associatedProvider,
                'adressNumber' => $Provider->getAdressNumber(),
                'adressComplement' => $Provider->getAdressComplement(),
                'zipCode' => $Provider->getZipCode(),
                'adressDistrict' => $Provider->getAdressDistrict(),
                'bankOperation' => $Provider->getBankOperation(),
                'bankNameOwner' => $Provider->getBankNameOwner(),
                'cpfNameOwner' => $Provider->getCpfNameOwner()
            );

        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $sql = "select COUNT(b) as quant FROM Businesspartner b JOIN b.city c WHERE b.partnerType like '%P%' ".$where;
        } else {
            $sql = "select COUNT(b) as quant FROM Businesspartner b WHERE b.partnerType like '%P%' ".$where;
        }

        if(isset($dados['data'])) {
            if(isset($dados['data']['findSRM'])) {
                if($dados['data']['findSRM'] == 'true') {
                    $sql .= " and b.name like 'SRM %' ";
                } else {
                    $sql .= " and b.name NOT LIKE 'SRM %' ";
                }
            } else {
                $sql .= " and b.name NOT LIKE 'SRM %' ";
            }
        } else {
            $sql .= " and b.name NOT LIKE 'SRM %' ";
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'providers' => $providers,
            'total' => $Quant[0]['quant']
        );

        $response->setDataset($dataset);
    }

    public function loadPartner(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%M%'";
        $query = $em->createQuery($sql);
        $BusinessPartner = $query->getResult();

        $dataset = array();
        foreach($BusinessPartner as $Provider){
            $City = $Provider->getCity();
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
                'id' => $Provider->getId(),
                'name' => $Provider->getName(),
                'registrationCode' => $Provider->getRegistrationCode(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $Provider->getAdress(),
                'partnerType' => $Provider->getPartnerType(),
                'email' => $Provider->getEmail(),
                'phoneNumber' => $Provider->getPhoneNumber(),
                'phoneNumber2' => $Provider->getPhoneNumber2(),
                'phoneNumber3' => $Provider->getPhoneNumber3(),
                'status' => $Provider->getStatus(),
                'bank' => $Provider->getBank(),
                'agency' => $Provider->getAgency(),
                'account' => $Provider->getAccount(),
                'blockreason' => $Provider->getBlockReason()
            );

        }
        $response->setDataset($dataset);
    }

    public function loadIssuing(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%S%'";
        $query = $em->createQuery($sql);
        $BusinessPartner = $query->getResult();

        $dataset = array();
        foreach($BusinessPartner as $Provider){
            $City = $Provider->getCity();
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
                'id' => $Provider->getId(),
                'name' => $Provider->getName(),
                'registrationCode' => $Provider->getRegistrationCode(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $Provider->getAdress(),
                'partnerType' => $Provider->getPartnerType(),
                'email' => $Provider->getEmail(),
                'phoneNumber' => $Provider->getPhoneNumber(),
                'phoneNumber2' => $Provider->getPhoneNumber2(),
                'phoneNumber3' => $Provider->getPhoneNumber3(),
                'status' => $Provider->getStatus(),
                'bank' => $Provider->getBank(),
                'agency' => $Provider->getAgency(),
                'account' => $Provider->getAccount(),
                'blockreason' => $Provider->getBlockReason()
            );

        }
        $response->setDataset($dataset);
    }

    public function loadIssuers(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        if(isset($dados['id']) && $dados['id'] != '') {
            $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%S%' and b.clientId = '".$dados['id']."'";
            $query = $em->createQuery($sql);
            $BusinessPartner = $query->getResult();
        } else {
            $Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client'], 'partnerType' => 'C'));
            if($Client) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findBy(array('clientId' => $Client->getId(), 'partnerType' => 'S'));
            }
        }

        $dataset = array();
        if(isset($BusinessPartner)) {

            foreach($BusinessPartner as $Provider){
                $City = $Provider->getCity();
                if ($City) {
                    $cityfullname = $City->getName() . ', ' . $City->getState();
                    $cityname = $City->getName();
                    $citystate = $City->getState();
                } else {
                    $cityfullname = '';
                    $cityname = '';
                    $citystate = '';
                }

                $sql = "select s FROM SystemLog s WHERE s.logType = 'EMAIL-CLIENT' and s.description LIKE '%EMAIL-ISSUER->".$Provider->getId()."%' ";
                $query = $em->createQuery($sql);
                $SystemLog = $query->getResult();

                if(count($SystemLog) > 0) {
                    $mailIssuer = true;
                } else {
                    $mailIssuer = false;
                }

                $dataset[] = array(
                    'id' => $Provider->getId(),
                    'name' => $Provider->getName(),
                    'registrationCode' => $Provider->getRegistrationCode(),
                    'city' => $cityname,
                    'state' => $citystate,
                    'cityfullname' => $cityfullname,
                    'adress' => $Provider->getAdress(),
                    'partnerType' => $Provider->getPartnerType(),
                    'email' => $Provider->getEmail(),
                    'phoneNumber' => $Provider->getPhoneNumber(),
                    'phoneNumber2' => $Provider->getPhoneNumber2(),
                    'phoneNumber3' => $Provider->getPhoneNumber3(),
                    'status' => $Provider->getStatus(),
                    'bank' => $Provider->getBank(),
                    'agency' => $Provider->getAgency(),
                    'account' => $Provider->getAccount(),
                    'blockreason' => $Provider->getBlockReason(),
                    'commission' => (float)$Provider->getCommission(),
                    'password' => $Provider->getPassword(),
                    'isMaster' => ($Provider->getIsMaster() == 'true'),
                    'sendEmail' => $mailIssuer
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function loadClients(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%C%'";
        $query = $em->createQuery($sql);
        $BusinessPartner = $query->getResult();

        $dataset = array();
        foreach($BusinessPartner as $Provider){
            $City = $Provider->getCity();
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
                'id' => $Provider->getId(),
                'name' => $Provider->getName(),
                'registrationCode' => $Provider->getRegistrationCode(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $Provider->getAdress(),
                'partnerType' => $Provider->getPartnerType(),
                'email' => $Provider->getEmail(),
                'phoneNumber' => $Provider->getPhoneNumber(),
                'phoneNumber2' => $Provider->getPhoneNumber2(),
                'phoneNumber3' => $Provider->getPhoneNumber3(),
                'status' => $Provider->getStatus(),
                'bank' => $Provider->getBank(),
                'agency' => $Provider->getAgency(),
                'account' => $Provider->getAccount(),
                'blockreason' => $Provider->getBlockReason(),
                'paymentType' => $Provider->getPaymentType()
            );

        }
        $response->setDataset($dataset);
    }

    public function remove(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
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

    public function validadteProviderProfile(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            $validation = '';

            if(isset($dados['id'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->find($dados['id']);
            }


            // email validation
            if(isset($dados['email']) && $dados['email'] != '') {
                $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%P%' and b.email like '%".$dados['email']."%' ";
                $query = $em->createQuery($sql);
                $partner = $query->getResult();
                if(count($partner) == 1) {

                    if(isset($dados['id'])) {
                        if($partner[0]->getId() != $BusinessPartner->getId()) {
                            $validation = $validation.' - Email existente na base';
                        }
                    } else {
                        $validation = $validation.' - Email existente na base';
                    }
                } else if(count($partner) > 1) {
                    $validation = $validation.' - Multiplos emails na base';
                }
            }


            // phone validation
            if(isset($dados['phoneNumber']) && $dados['phoneNumber'] != '') {
                $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%P%' and b.phoneNumber like '%".$dados['phoneNumber']."%' ";
                $query = $em->createQuery($sql);
                $partner = $query->getResult();
                if(count($partner) == 1) {

                    if(isset($dados['id'])) {
                        if($partner[0]->getId() != $BusinessPartner->getId()) {
                            $validation = $validation.' - Telefone existente na base';
                        }
                    } else {
                        $validation = $validation.' - Telefone existente na base';
                    }
                } else if(count($partner) > 1) {
                    $validation = $validation.' - Multiplos Telefones na base';
                }
            }


            // name validation
            if(isset($dados['name']) && $dados['name'] != '') {
                $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%P%' and b.name like '%".$dados['name']."%' ";
                $query = $em->createQuery($sql);
                $partner = $query->getResult();
                if(count($partner) == 1) {

                    if(isset($dados['id'])) {
                        if($partner[0]->getId() != $BusinessPartner->getId()) {
                            $validation = $validation.' - Nome existente na base';
                        }
                    } else {
                        $validation = $validation.' - Nome existente na base';
                    }
                } else if(count($partner) > 1) {
                    $validation = $validation.' - Multiplos Nomes na base';
                }
            }


            // bank data validation
            if(isset($dados['bank']) && isset($dados['agency']) && isset($dados['account'])) {
                if($dados['bank'] != '' && $dados['agency'] != '' && $dados['account'] != '') {
                    $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%P%' and b.bank like '%".$dados['bank']."%' and b.agency like '%".$dados['agency']."%' and b.account like '%".$dados['account']."%' ";
                    $query = $em->createQuery($sql);
                    $partner = $query->getResult();
                    if(count($partner) == 1) {

                        if(isset($dados['id'])) {
                            if($partner[0]->getId() != $BusinessPartner->getId()) {
                                $validation = $validation.' - Dados bancarios existente na base';
                            }
                        } else {
                            $validation = $validation.' - Dados bancarios existente na base';
                        }
                    } else if(count($partner) > 1) {
                        $validation = $validation.' - Multiplos Dados bancarios na base';
                    }
                }
            }


            // name of mother validation
            if(isset($dados['nameMother']) && $dados['nameMother'] != '') {
                $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%P%' and b.nameMother like '%".$dados['nameMother']."%' ";
                $query = $em->createQuery($sql);
                $partner = $query->getResult();
                if(count($partner) == 1) {

                    if(isset($dados['id'])) {
                        if($partner[0]->getId() != $BusinessPartner->getId()) {
                            $validation = $validation.' - Nome da Mãe existente na base';
                        }
                    } else {
                        $validation = $validation.' - Nome da Mãe existente na base';
                    }
                } else if(count($partner) > 1) {
                    $validation = $validation.' - Multiplos Nomes da mãe na base';
                }
            }

            if($validation !=  '') {
                throw new \Exception($validation, 1);
                
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro Unico');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function deleteBillsPayAndProvider(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            $Billspay = $em->getRepository('Billspay')->findOneBy(array('id' => $dados['id']));
            
            if($Billspay) {
                
                $PurchaseBillspay = $em->getRepository('PurchaseBillspay')->findOneBy(array('billspay' => $Billspay->getId()));
                $Purchase = $PurchaseBillspay->getPurchase();
                
                $PurchasesM = $em->getRepository('Purchase')->findBy( array(
                    'cards' => $Purchase->getCards()->getId(),
                    'status' => 'M'
                ));

                $PurchasesW = $em->getRepository('Purchase')->findBy( array(
                    'cards' => $Purchase->getCards()->getId(),
                    'status' => 'W'
                ));

                if(count($PurchasesW) + count($PurchasesM) == 1) {

                    $PurchasesC = $em->getRepository('Purchase')->findBy( array(
                        'cards' => $Purchase->getCards()->getId(),
                        'status' => 'C'
                    ));
                    foreach ($PurchasesC as $key => $value) {
                        $PurchaseBillspayC = $em->getRepository('PurchaseBillspay')->findBy(array('purchase' => $value->getId()));
                        foreach ($PurchaseBillspayC as $Pbillspay) {
                            $BillspayC = $Pbillspay->getBillspay();

                            $em->remove($Pbillspay);
                            $em->flush($Pbillspay);
                        }

                        if(isset($BillspayC)) {
                            $em->remove($BillspayC);
                            $em->flush($BillspayC);
                        }

                        $em->remove($value);
                        $em->flush($value);
                    }


                    if($Purchase->getStatus() == "M") {
                        $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Purchase->getCards()->getId()));
                        if($Milesbench) {
                            $Milesbench->setLeftover($Milesbench->getLeftover() - $Purchase->getLeftover());
                            $Milesbench->setLastchange(new \Datetime());
                            
                            $em->persist($Milesbench);
                            $em->flush($Milesbench);
                        }
                    }
                
                    $em->remove($PurchaseBillspay);
                    $em->flush($PurchaseBillspay);
                    
                    $em->remove($Billspay);
                    $em->flush($Billspay);
                    
                    $Cards = $Purchase->getCards();
                    $BusinessPartner = $Cards->getBusinesspartner();
                    
                    $em->remove($Purchase);
                    $em->flush($Purchase);
                    
                    $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Cards->getId()));
                    if($Milesbench) {
                        $em->remove($Milesbench);
                        $em->flush($Milesbench);
                    }
                    
                    if($Cards) {
                        $em->remove($Cards);
                        $em->flush($Cards);
                    }
                    
                    
                    if($BusinessPartner) {
                        $UserGroup = $em->getRepository('UserGroup')->findOneBy(array('user' => $BusinessPartner->getId()));
                        if($UserGroup) {
                            $em->remove($UserGroup);
                            $em->flush($UserGroup);
                        }
                        
                        $em->remove($BusinessPartner);
                        $em->flush($BusinessPartner);
                    }
                    
                    $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $request->getRow()['hashId']));
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
                    
                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("EVENTO - Compra removida do fornecedor: '".$dados['provider']."' referente a ".$dados['purchased_miles']." pontos da CIA ".$dados['airline']);
                    $SystemLog->setLogType('EVENT');
                    $SystemLog->setBusinesspartner($BusinessPartner);
                    
                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                    
                    if(isset($dados['deleteDescription']) && $dados['deleteDescription'] != '') {
                        $mailBody = "<br>Exclusão de compra: " . $dados['provider'] . "<br><br>Motivo: " . $dados['deleteDescription'] . "<br>";
                        $email1 = 'onemilhas@onemilhas.com.br';
                        $postfields = array(
                            'content' => $mailBody,
                            'partner' => $email1,
                            'subject' => "Exclusão de compra e Fornecedor - " . ( new \DateTime() )->format( 'd/m/Y' ),
                            'type' => ''
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        $result = curl_exec($ch);
                    }
                }
            }
            
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
}