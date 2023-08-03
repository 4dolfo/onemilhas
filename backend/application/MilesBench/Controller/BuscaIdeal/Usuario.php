<?php

namespace MilesBench\Controller\BuscaIdeal;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Usuario {

    public function login(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            if(!isset($dados['senha'])) {
                throw new \Exception("Senha deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );

            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Usuario ou senha invalidos!");
            }

            if($dados['senha'] !== base64_decode($BusinessPartner->getPassword()) || $BusinessPartner->getStatus() == 'Bloqueado') {
                throw new \Exception("Usuario ou senha invalidos!");
            }

            $Agency = $em->getRepository('Businesspartner')->findOneBy(
                array( 'id' => $BusinessPartner->getClient(), 'partnerType' => 'C' )
            );
            if(!$Agency) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com a One Milhas!");
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
                }
                $chargingMethods[] = array(
                    'metodo' => $method->getName(),
                    'status' => $status
                );
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
                    'metodo' => $method->getName(),
                    'status' => $status
                );
            }

            $dataset = array(
                'nome' => $BusinessPartner->getName(),
                'slides' => $slides,
                'companhias' => array(
                    'LATAM' => array(
                        'status' => 'ativo'
                    ),
                    'GOl' => array(
                        'status' => 'ativo'
                    ),
                    'AZUL' => array(
                        'status' => 'ativo'
                    ),
                    'AVIANCA' => array(
                        'status' => 'ativo'
                    )
                ),
                'perfil' => array(
                    'usuario_confianca' => $userConfianca,
                    'matodos_cobranca' => $chargingMethods,
                    'metodos_emissao' => $emissionMethods,
                    'exibir_milhas' => $SalePlans->getShowMiles(),
                    'exibit_convencional' => $SalePlans->getShowConventional()
                )
            );

            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Bem vindo!');
            $response->addMessage($message);
        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
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
            $message->setText('Ocorreu um erro na tentativa de login, a One Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
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
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com a One Milhas!");
            }

            $content = "<br>Ola,<br><br>O usuario ".$dados['login']." solicitou alteração de senha.<br>clique no link abaixo para realizar o cadastro de uma nova senha:<br><br>https://buscaideal.com/login <br><br>att.<br>One Milhas";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => '[One Milhas] - Esqueci minha senha',
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

            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
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
            $message->setText('Ocorreu um erro, a One Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
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
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com a One Milhas!");
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
            $email1 = 'emissao@onemilhas.com.br';
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
            $message->setText('Ocorreu um erro, a One Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }
}