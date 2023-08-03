<?php

namespace MilesBench\Controller\Incodde;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Usuario {

    public function login(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            // fails on validation of the user
            $Agency = $em->getRepository('Businesspartner')->find($dados['client_id']);
            if(!$Agency) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com a Milhas!");
            }

            if($Agency->getPlan()) {
                $SalePlans = $Agency->getPlan();
            } else {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('id' => 1));
            }

            $slides = array();
            $PlansSlides = $em->getRepository('PlansSlides')->findBy( array( 'salePlans' => $SalePlans->getId() ) );
            foreach ($PlansSlides as $key => $value) {
                $slides[] = array(
                    'url' => $value->getUrl()
                );
            }

            // confiança user
            $userConfianca = null;
            if($SalePlans->getPlanUser()) {
                $userConfianca = $SalePlans->getPlanUser()->getUserName();
            }

            // charging methods
            $chargingMethods = array();
            $PlansChargingMethods = $em->getRepository('PlansChargingMethods')->findAll();
            foreach ($PlansChargingMethods as $method) {
                $SalePlansChargingMethods = $em->getRepository('SalePlansChargingMethods')->findOneBy(
                    array(
                        'plansChargingMethods' => $method->getId(),
                        'salePlans' => $SalePlans->getId()
                    )
                );

                $status = false;
                if($SalePlansChargingMethods) {
                    $status = true;
                    $chargingMethods[] = array(
                        'id' => $method->getId(),
                        'metodo' => $method->getName(),
                        'description' => $method->getDescription(),
                        'status' => $status,
                        'interestFree' => $SalePlansChargingMethods->getInterestFree(),
                        'interest' => $SalePlansChargingMethods->getInterestFreeInstallment(),
                        'extraValue' => $SalePlansChargingMethods->getExtraValue(),
                        'extraType' => $SalePlansChargingMethods->getExtraType()
                    );
                }
            }

            // emission methods
            $emissionMethods = array();
            $PlansEmissionMethods = $em->getRepository('PlansEmissionMethods')->findAll();
            foreach ($PlansEmissionMethods as $method) {
                $SalePlansEmissionMethods = $em->getRepository('SalePlansEmissionMethods')->findOneBy(
                    array(
                        'plansEmissionMethods' => $method->getId(),
                        'salePlans' => $SalePlans->getId()
                    )
                );

                $status = false;
                if($SalePlansEmissionMethods) {
                    $status = true;
                }
                $emissionMethods[] = array(
                    'id' => $method->getId(),
                    'metodo' => $method->getName(),
                    'status' => $status
                );
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

            $birthdate = '';
            if($Agency->getBirthdate()) {
                $birthdate = $Agency->getBirthdate()->format('Y-m-d H:i:s');
            }

            $SystemsData = $em->getRepository('SystemsData')->find(1);
            $ClientsMarkups = $em->getRepository('ClientsMarkups')->findOneBy(array('businesspartner' => $Agency->getId() ));
            if(!$ClientsMarkups) {
                $ClientsMarkups = new \ClientsMarkups();
                $ClientsMarkups->setBusinesspartner($Agency);
                $ClientsMarkups->setJson( json_encode(
                    array(
                            'LATAM' => array(
                                'tipo_markup' => 'D',
                                'markup' => 0,
                                'status' => 'ativo'
                            ),
                            'GOL' => array(
                                'tipo_markup' => 'D',
                                'markup' => 0,
                                'status' => 'ativo'
                            ),
                            'AZUL' => array(
                                'tipo_markup' => 'D',
                                'markup' => 0,
                                'status' => 'ativo'
                            ),
                            'AVIANCA' => array(
                                'tipo_markup' => 'D',
                                'markup' => 0,
                                'status' => 'ativo'
                            ),
                            'TAP' => array(
                                'tipo_markup' => 'D',
                                'markup' => 0,
                                'status' => 'ativo'
                            )
                    )
                ) );
                $ClientsMarkups->setUpdateDate(new \DateTime());
                $em->persist($ClientsMarkups);
                $em->flush($ClientsMarkups);
            }

            $dataset = array(
                'nome' => $Agency->getName(),
                'prefixo' => $Agency->getPrefixo(),
                'logoUrl' => $Agency->getLogoUrl(),
                'labelName' => $Agency->getLabelName(),
                'labelDescription' => $Agency->getLabelDescription(),
                'labelAdress' => $Agency->getLabelAdress(),
                'labelPhone' => $Agency->getLabelPhone(),
                'labelEmail' => $Agency->getLabelEmail(),
                'logoUrlSmall' => $Agency->getLogoUrlSmall(),
                'emissionTerm' => "",//$SystemsData->getEmissionTerm(),
                'conclusionTerm' =>"", //$SystemsData->getConclusionTerm(),
                'adress' => $Agency->getAdress(),
                'phoneNumber' => $Agency->getPhoneNumber(),
                'phoneNumber2' => $Agency->getPhoneNumber2(),
                'adressNumber' => $Agency->getAdressNumber(),
                'adressComplement' => $Agency->getAdressComplement(),
                'zipCode' => $Agency->getZipCode(),
                'adressDistrict' => $Agency->getAdressDistrict(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'registrationCode' => $Agency->getRegistrationCode(),
                'birthdate' => $birthdate,
                'slides' => $slides,
                'pendente_sugestao_dados' => $Agency->getSuggestionNewData() != null,
                'companhias' => json_decode($ClientsMarkups->getJson()),
                'perfil' => array(
                    'usuario_confianca' => $userConfianca,
                    'metodos_cobranca' => $chargingMethods,
                    'metodos_emissao' => $emissionMethods,
                    'documentos' => ($SalePlans->getDocumentos() == 'true'),
                    'exibir_milhas' => $SalePlans->getShowMiles(),
                    'exibit_convencional' => $SalePlans->getShowConventional(),
                    'id' => $SalePlans->getId()
                )
            );

            $PlansPromotions = $em->getRepository('PlansPromotions')->findOneBy(
                array(
                    'status' => 'true'
                )
            );
            //if(count($PlansPromotions) > 0) {
            //    $dataset['perfil']['promo_modal'] = array( 'promo' => true, 'url' => $PlansPromotions->getUrlImage() );
            //} else {
                $dataset['perfil']['promo_modal'] = array( 'promo' => false );
            //}

//		header('Content-Type: application/json; charset=utf-8', true,200);
            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Bem vindo!');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $email1 = 'adm@onemilhas.com.br';
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $postfields = array(
                'content' => $content,
                'partner' => $email1,
                'subject' => 'ERROR - BUSCA - LOGIN',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function forgotPassword(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            if(!isset($dados['token'])) {
                throw new \Exception("Token deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );

            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Usuario ou senha invalidos!");
            }

            $Agency = $em->getRepository('Businesspartner')->findOneBy(
                array( 'id' => $BusinessPartner->getClient(), 'partnerType' => 'C' )
            );
            if(!$Agency) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com a Milhas!");
            }

            $content = "<br>Ola,<br><br>O usuario ".$dados['login']." solicitou alteração de senha.<br>clique no link abaixo para realizar o cadastro de uma nova senha:<br><br>https://buscaideal.com/login <br><br>att.<br>Milhas";
            $email1 = 'suporte@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'partner' => $email2,
                'subject' => '[Milhas] - One Milhas- Esqueci minha senha',
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


            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Foi enviado um email para sua agencia com um link para efetuar o cadastro de uma nova senha!');
            $response->addMessage($message);
        } catch(\Exception $e) {

            $email1 = 'suporte@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - BUSCA - ESQUECI SENHA',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Ocorreu um erro, a Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }

    public function changePassword(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            if(!isset($dados['nova_senha'])) {
                throw new \Exception("Nova senha deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );

            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Usuario invalido!");
            }

            if($BusinessPartner->getStatus() == 'Bloqueado') {
                throw new \Exception("Usuario invalido!");
            }

            $Agency = $em->getRepository('Businesspartner')->findOneBy(
                array( 'id' => $BusinessPartner->getClient(), 'partnerType' => 'C' )
            );
            if(!$Agency) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com a Milhas!");
            }

            $BusinessPartner->setPassword(base64_encode($dados['nova_senha']));
            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Senha alterada com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'suporte@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - BUSCA - CHANGE PASSWORD',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Ocorreu um erro, a Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }

    public function loadPassengers(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        try {
            $sql = " SELECT * FROM businesspartner where partner_type = 'X' and id in ( SELECT pax_id from sale where client_id = " . $dados['client_id'] . " ) limit 5 ";
            $stmt = $QueryBuilder->query($sql);

            $dataset = array();
            while ($row = $stmt->fetch()) {
                $dataset[] =  array(
                    'name' => $row['name'],
                    'registration_code' => $row['registration_code'],
                    'birthdate' => $row['birthdate']
                );
            }

            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Senha alterada com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Ocorreu um erro, a Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }

    public function updateClient(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            $Businesspartner = $em->getRepository('Businesspartner')->find($dados['client_id']);
            $Businesspartner->setSuggestionNewData( json_encode($dados['dados']) );

            $em->persist($Businesspartner);
            $em->flush($Businesspartner);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados alterados com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Ocorreu um erro, a Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }

    public function test(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        try {
            $response->setDataset(array( 'hue' => 'xuletinha' ));

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Senha alterada com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Ocorreu um erro, a Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }
}
