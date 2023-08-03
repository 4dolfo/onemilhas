<?php

namespace MilesBench\Controller\RoboEmissao;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Robot {

    public function removeAccents($string){
        $string = str_replace('ç', 'c', $string);
        $string = str_replace('Ç', 'C', $string);
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç|Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$string);
	}

    public function newOrder(Request $request, Response $response) {

        if(new \Datetime() > new \Datetime('2018-01-01')) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Robo desativado');
            $response->addMessage($message);
            return;
        }

        $dados = $request->getRow();
        $order = $dados['order'];
        $onlineflights = $dados['onlineflights'];
        $em = Application::getInstance()->getEntityManager();

        try {

            $crypt = new \MilesBench\Controller\RoboEmissao\Crypt();

            $onlineflight = $onlineflights[0];

            $usuario = array(
                'Chave' => \MilesBench\Util::chave_emissao_in8,
                'Senha' => \MilesBench\Util::senha_emissao_in8
            );

            $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $onlineflight['tax_card']));

            if($onlineflight['tax_cardType'] == 'AMERICAN EXPRESS' || $onlineflight['tax_cardType'] == 'AMERICAN') {
                $onlineflight['tax_cardType'] = 'AMEX';
            }

            $CartaoPagamento = array(
                "CPFTitular" => $crypt->encrypt($InternalCards->getShowRegistration()),
                "CVV" => $crypt->encrypt($onlineflight['tax_password']),
                "Validade" => $crypt->encrypt($InternalCards->getDueDate()->format('m/Y')),
                "Numero" => $crypt->encrypt($onlineflight['tax_card']),
                "NomeTitular" => $crypt->encrypt($onlineflight['tax_providerName']),
                "Bandeira" => $crypt->encrypt(str_replace(" ", "", $onlineflight['tax_cardType']))
            );

            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $onlineflight['cards_id']));
            $email = explode(';', $Cards->getBusinesspartner()->getEmail())[0];
            $CartaoMilhas = array(
                "Telefone" => $crypt->encrypt($onlineflight['celNumberAirline']),
                "Numero" => $crypt->encrypt($Cards->getCardNumber()),
                "CPF" => $crypt->encrypt($Cards->getBusinesspartner()->getRegistrationCode()),
                "Senha" => $crypt->encrypt($onlineflight['recovery_password']),
                "Email" => $crypt->encrypt($email)
            );

            $OnlinePax = $em->getRepository('OnlinePax')->findBy(array('order' => $order['id']));
            $Passageiros = array();
            foreach ($OnlinePax as $key => $value) {
                $nascimento = '';
                if($value->getBirthdate()) {
                    $nascimento = $value->getBirthdate()->format('d/m/Y');
                }

                $FaixaEtaria = "Adulto";
                if($value->getIsChild() == "S") {
                    $FaixaEtaria = "Crianca";
                }
                if($value->getIsNewborn() == "S") {
                    $FaixaEtaria = "Bebe";
                }

                $Agnome = null;
                if($value->getPaxAgnome()) {
                    $Agnome = $crypt->encrypt($value->getPaxAgnome());
                }

                $Passageiros[] = array(
                    "Agnome" => $Agnome,
                    "Nascimento" => $crypt->encrypt($nascimento),
                    "FaixaEtaria" => $crypt->encrypt($FaixaEtaria),
                    "Sexo" => $crypt->encrypt($value->getGender()),
                    "Nome" => $crypt->encrypt(self::removeAccents($value->getPaxName())),
                    "Sobrenome" => $crypt->encrypt(self::removeAccents($value->getPaxLastName())),
                    "Telefone" => $crypt->encrypt($onlineflight['celNumberAirline']),
                    "Email" => $crypt->encrypt($email)
                );
            }

            $OnlineFlight = $em->getRepository('OnlineFlight')->findBy(array('order' => $order['id']));
            $Voo = array();
            foreach ($OnlineFlight as $key => $value) {

                // conexoes
                $OnlineConnection = $em->getRepository('OnlineConnection')->findBy(array('onlineFlight' => $value->getId()));
                $conexao = array();
                foreach ($OnlineConnection as $keyConnection => $connection) {
                    $conexao[] = array(
                        "NumeroVoo" => $connection->getFlight(),
                        "Duracao" => $connection->getFlightTime(),
                        "Embarque" => $connection->getBoarding(),
                        "Destino" => $connection->getAirportCodeTo(),
                        "Origem" => $connection->getAirportCodeFrom(),
                        "Desembarque" => $connection->getLanding()
                    );
                }

                if($key == 0) {
                    $Sentido = 'ida';
                } else {
                    $Sentido = 'volta';
                }

                // flight data
                $Voo[] = array(
                    "NumeroConexoes" => count($conexao),
                    "NumeroVoo" => $value->getFlight(),
                    "Duracao" => $value->getFlightTime(),
                    "Embarque" => $value->getBoardingDate()->format('d/m/Y H:i'),
                    "Destino" => $value->getAirportCodeTo(),
                    "Sentido" => $Sentido,
                    "Conexoes" => $conexao,
                    "Origem" => $value->getAirportCodeFrom(),
                    "Milhas" => array(
                        "Bebe" => (int)$value->getMilesPerNewborn(),
                        "Adulto" => (int)$value->getMilesPerAdult(),
                        "Crianca" => (int)$value->getMilesPerChild()
                    ),
                    "Desembarque" => $value->getLandingDate()->format('d/m/Y H:i'),
                    "Companhia" => $value->getAirline()
                );

                $value->setCards($Cards);
                $em->persist($value);
                $em->flush($value);
            }

            // assembling array
            $postData = array(
                'Usuario' => $usuario,
                'CartaoPagamento' => $CartaoPagamento,
                'CartaoMilhas' => $CartaoMilhas,
                'Passageiros' => $Passageiros,
                'Voo' => $Voo
            );


            // sending data
            $jsonToPost = json_encode($postData);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::url_emissao_in8);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonToPost);
            $result = curl_exec($ch);
            $return = json_decode($result, true);

            if(isset($return['Identificador'])) {
                $RobotEmissionIn8 = new \RobotEmissionIn8();

                if(isset($return['Identificador'])) {
                    $RobotEmissionIn8->setIdentificador($return['Identificador']);
                }
                if(isset($return['Localizador'])) {
                    $RobotEmissionIn8->setFlightLocator($return['Localizador']);
                }

                // saving order reference
                $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));
                $RobotEmissionIn8->setOrder($OnlineOrder);

                // saving the user
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_POST['hashId']));
                if($UserSession) {
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
                    if($UserPartner) {
                        $RobotEmissionIn8->setBusinesspartner($UserPartner);
                    }
                }

                $RobotEmissionIn8->setIssueDate(new \Datetime());

                // saving status
                if(isset($return['Status']['Atual']) && $return['Status']['Atual'] != '') {
                    $RobotEmissionIn8->setStatus($return['Status']['Atual']);
                }
                if(isset($return['Status']['Alerta'][0]) && $return['Status']['Alerta'] != '') {
                    $RobotEmissionIn8->setAlerta($return['Status']['Alerta'][0]);
                }
                if(isset($return['Status']['Erro']) && $return['Status']['Erro'] != '') {
                    $RobotEmissionIn8->setErro($return['Status']['Erro']);
                }
                if(isset($return['Status']['Sucesso']) && $return['Status']['Sucesso'] != '') {
                    $RobotEmissionIn8->setSucesso($return['Status']['Sucesso']);
                }

                if(isset($return['Html']) && $return['Html'] != '') {

                    // saving file
                    $file_name_with_full_path = getcwd().'/'.$return['Localizador'].'.html';
                    file_put_contents($file_name_with_full_path, $return['Html']);

                    if (function_exists('curl_file_create')) { // php 5.5+
                        $cFile = curl_file_create($file_name_with_full_path);
                    } else { //
                        $cFile = '@' . realpath($file_name_with_full_path);
                    }

                    $post = array('file_contents' => $cFile);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://files.srm.systems:9943/file-storage/save');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    $resultFile = curl_exec($ch);
                    curl_close($ch);

                    $RobotEmissionIn8->setFileId(json_decode($resultFile, true)['id']);
                }

                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('Status' => $return['Status']['Atual']));

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - ROBO - EMISSAO',
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
            $message->setText('ERROR');
            $response->addMessage($message);
        }
    }

    public function updateOrderStatus(Request $request, Response $response) {
        $dados = $request->getRow();
        $order = $dados['order'];

        if(new \Datetime() > new \Datetime('2018-01-01')) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Robo desativado');
            $response->addMessage($message);
            return;
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));
            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findOneBy(array('order' => $order['id']), array('id' => 'DESC'));

            if(!$RobotEmissionIn8) {
                throw new Exception("Emissao não encontrada no banco de dados!", 1);
            }

            // Get cURL resource
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => \MilesBench\Util::consulta_url_emissao_in8 . $RobotEmissionIn8->getIdentificador(),
                CURLOPT_USERAGENT => 'Codular Sample cURL Request'
            ));
            // Send the request & save response to $resp
            $result = curl_exec($curl);
            // Close request to clear up some resources
            curl_close($curl);
            $return = json_decode($result, true);

            if(isset($return['Identificador'])) {
                $RobotEmissionIn8 = new \RobotEmissionIn8();

                if(isset($return['Identificador'])) {
                    $RobotEmissionIn8->setIdentificador($return['Identificador']);
                }
                if(isset($return['Localizador'])) {
                    $RobotEmissionIn8->setFlightLocator($return['Localizador']);
                }

                // saving the user
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_POST['hashId']));
                if($UserSession) {
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
                    if($UserPartner) {
                        $RobotEmissionIn8->setBusinesspartner($UserPartner);
                    }
                }

                $RobotEmissionIn8->setIssueDate(new \Datetime());

                // saving order reference
                $RobotEmissionIn8->setOrder($OnlineOrder);

                // saving status
                if(isset($return['Status']['Atual']) && $return['Status']['Atual'] != '') {
                    $RobotEmissionIn8->setStatus($return['Status']['Atual']);
                }
                if(isset($return['Status']['Alerta'][0]) && $return['Status']['Alerta'] != '') {
                    $RobotEmissionIn8->setAlerta($return['Status']['Alerta'][0]);
                }
                if(isset($return['Status']['Erro']) && $return['Status']['Erro'] != '') {
                    $RobotEmissionIn8->setErro($return['Status']['Erro']);
                }
                if(isset($return['Status']['Sucesso']) && $return['Status']['Sucesso'] != '') {
                    $RobotEmissionIn8->setSucesso($return['Status']['Sucesso']);
                }

                if(isset($return['Html']) && $return['Html'] != '') {

                    // saving file
                    $file_name_with_full_path = getcwd().'/'.$return['Localizador'].'.html';
                    file_put_contents($file_name_with_full_path, $return['Html']);

                    if (function_exists('curl_file_create')) { // php 5.5+
                        $cFile = curl_file_create($file_name_with_full_path);
                    } else { //
                        $cFile = '@' . realpath($file_name_with_full_path);
                    }

                    $post = array('file_contents' => $cFile);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://files.srm.systems:9943/file-storage/save');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    $resultFile = curl_exec($ch);
                    curl_close($ch);

                    $RobotEmissionIn8->setFileId(json_decode($resultFile, true)['id']);
                }

                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('Status' => $return['Status']['Atual']));

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - ROBO - CONSULTA',
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
            $message->setText('ERROR');
            $response->addMessage($message);
        }
    }

    public function checkOrderBot(Request $request, Response $response) {
        $dados = $request->getRow();
        $order = $dados['order'];
        $em = Application::getInstance()->getEntityManager();

        $dataset = array();
        if(isset($order['id'])) {

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));

            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findBy(array('order' => $order['id']));
            foreach ($RobotEmissionIn8 as $key => $value) {
                $dataset[] = array(
                    'id' => $value->getId(),
                    'identificador' => $value->getIdentificador(),
                    'flightLocator' => $value->getFlightLocator(),
                    'fileId' => $value->getFileId(),
                    'status' => $value->getStatus(),
                    'alerta' => $value->getAlerta(),
                    'erro' => $value->getErro(),
                    'sucesso' => $value->getSucesso()
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function cancelOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        $order = $dados['order'];

        if(new \Datetime() > new \Datetime('2018-01-01')) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Robo desativado');
            $response->addMessage($message);
            return;
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));
            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findOneBy(array('order' => $order['id']), array('id' => 'DESC'));

            if(!$RobotEmissionIn8) {
                throw new Exception("Emissao não encontrada no banco de dados!", 1);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::consulta_url_emissao_in8 . $RobotEmissionIn8->getIdentificador());
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $return = json_decode($result, true);
            curl_close($ch);

            // enviando dados recebidos
            //////////////////////////////////////////////////////////////////////////////
            $content = "<br><br><br>POST:".$result."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'RETORNO ROBO - CONFERIR',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $resultEmail = curl_exec($ch);
            //////////////////////////////////////////////////////////////////////////////

            if(isset($return['Identificador'])) {
                $RobotEmissionIn8 = new \RobotEmissionIn8();

                if(isset($return['Identificador'])) {
                    $RobotEmissionIn8->setIdentificador($return['Identificador']);
                }
                if(isset($return['Localizador'])) {
                    $RobotEmissionIn8->setFlightLocator($return['Localizador']);
                }

                // saving the user
                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_POST['hashId']));
                if($UserSession) {
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
                    if($UserPartner) {
                        $RobotEmissionIn8->setBusinesspartner($UserPartner);
                    }
                }

                $RobotEmissionIn8->setIssueDate(new \Datetime());

                // saving order reference
                $RobotEmissionIn8->setOrder($OnlineOrder);

                // saving status
                if(isset($return['Status']['Atual']) && $return['Status']['Atual'] != '') {
                    $RobotEmissionIn8->setStatus($return['Status']['Atual']);
                }
                if(isset($return['Status']['Alerta'][0]) && $return['Status']['Alerta'] != '') {
                    $RobotEmissionIn8->setAlerta($return['Status']['Alerta'][0]);
                }
                if(isset($return['Status']['Erro']) && $return['Status']['Erro'] != '') {
                    $RobotEmissionIn8->setErro($return['Status']['Erro']);
                }
                if(isset($return['Status']['Sucesso']) && $return['Status']['Sucesso'] != '') {
                    $RobotEmissionIn8->setSucesso($return['Status']['Sucesso']);
                }

                if(isset($return['Html']) && $return['Html'] != '') {

                    // saving file
                    $file_name_with_full_path = getcwd().'/'.$return['Localizador'].'.html';
                    file_put_contents($file_name_with_full_path, $return['Html']);

                    if (function_exists('curl_file_create')) { // php 5.5+
                        $cFile = curl_file_create($file_name_with_full_path);
                    } else { //
                        $cFile = '@' . realpath($file_name_with_full_path);
                    }

                    $post = array('file_contents' => $cFile);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://files.srm.systems:9943/file-storage/save');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    $resultFile = curl_exec($ch);
                    curl_close($ch);

                    $RobotEmissionIn8->setFileId(json_decode($resultFile, true)['id']);
                }

                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('Status' => $return['Status']['Atual']));

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - ROBO - CONSULTA',
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
            $message->setText('ERROR');
            $response->addMessage($message);
        }
    }

    public function autoUpdateOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        $order = $dados['order'];

        if(new \Datetime() > new \Datetime('2018-01-01')) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Robo desativado');
            $response->addMessage($message);
            return;
        }

        $order['id'] = 40484;

        try {
            $em = Application::getInstance()->getEntityManager();
            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));
            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findOneBy(array('order' => $order['id']), array('id' => 'DESC'));

            if(!$RobotEmissionIn8) {
                throw new \Exception("Emissao não encontrada no banco de dados!", 1);
            }

            // Get cURL resource
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => \MilesBench\Util::consulta_url_emissao_in8 . $RobotEmissionIn8->getIdentificador(),
                CURLOPT_USERAGENT => 'Codular Sample cURL Request'
            ));
            // Send the request & save response to $resp
            $result = curl_exec($curl);
            // Close request to clear up some resources
            curl_close($curl);
            $return = json_decode($result, true);

            // enviando dados recebidos
            //////////////////////////////////////////////////////////////////////////////
            $content = "<br><br><br>POST:".$result."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'RETORNO ROBO - CONFERIR',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $resultEmail = curl_exec($ch);
             //////////////////////////////////////////////////////////////////////////////

            $content = '';
            if(isset($return['Identificador'])) {
                $RobotEmissionIn8 = new \RobotEmissionIn8();

                if(isset($return['Identificador'])) {
                    $RobotEmissionIn8->setIdentificador($return['Identificador']);
                }
                if(isset($return['Localizador'])) {
                    $RobotEmissionIn8->setFlightLocator($return['Localizador']);
                }

                // saving order reference
                $RobotEmissionIn8->setOrder($OnlineOrder);

                // saving status
                if(isset($return['Status']['Atual']) && $return['Status']['Atual'] != '') {
                    $RobotEmissionIn8->setStatus($return['Status']['Atual']);
                    $email1 = 'financeiro@onemilhas.com.br';
                    $email2 = 'adm@onemilhas.com.br';
                    if($return['Status']['Atual'] == 'Emissão na Fila' || $return['Status']['Atual'] == 'Iniciando Emissão') {

                        sleep(30);
                    } else if($return['Status']['Atual'] == 'Finalizada') {

                        $content = '<br>Ola,<br><br>Pedido finalizado' .
                            '<br>ID do pedido: ' . $dados['order_id'];
                            $return = Mail::sendTransactional($email1, $email2, $content, 'Emissão automatica - Pedido: ' . $dados['order_id']);

                    } else if ( strpos($return['Status']['Atual'],'Erro') !== false ) {

                        $return = Mail::sendTransactional($email1, $email2, ' Erro identificado ', 'Emissão automatica - Pedido: ' . $dados['order_id']);
                    } else {

                        sleep(15);
                    }
                }
                if(isset($return['Status']['Alerta'][0]) && $return['Status']['Alerta'] != '') {
                    $RobotEmissionIn8->setAlerta($return['Status']['Alerta'][0]);
                }
                if(isset($return['Status']['Erro']) && $return['Status']['Erro'] != '') {
                    $RobotEmissionIn8->setErro($return['Status']['Erro']);
                }
                if(isset($return['Status']['Sucesso']) && $return['Status']['Sucesso'] != '') {
                    $RobotEmissionIn8->setSucesso($return['Status']['Sucesso']);
                }

                if(isset($return['Html']) && $return['Html'] != '') {

                    // saving file
                    $file_name_with_full_path = getcwd().'/'.$return['Localizador'].'.html';
                    file_put_contents($file_name_with_full_path, $return['Html']);

                    if (function_exists('curl_file_create')) { // php 5.5+
                        $cFile = curl_file_create($file_name_with_full_path);
                    } else { //
                        $cFile = '@' . realpath($file_name_with_full_path);
                    }

                    $post = array('file_contents' => $cFile);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://files.srm.systems:9943/file-storage/save');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    $resultFile = curl_exec($ch);
                    curl_close($ch);

                    $RobotEmissionIn8->setFileId(json_decode($resultFile, true)['id']);
                }

                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);

                $email1 = 'financeiro@onemilhas.com.br';
                    $email2 = 'adm@onemilhas.com.br';

                $return = Mail::sendTransactional($email1, $email2, '<br>Status Atual: ' . $return['Status']['Atual'] . '<br>', 'Emissão automatica - Pedido: ' . $dados['order_id']);
            }

            if($content != '') {
                $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://gestao.srm.systems/'.$DirServer.'/backend/application/index.php?rota=/in8Bot/autoUpdateOrder');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array(
                    'order' => array( 'id' => $dados['order_id'] ),
                    'hashId' => '9901401e7398b65912d5cae4364da460'
                )) );
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('Status' => $return['Status']['Atual']));

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - ROBO - CONSULTA - AUTO',
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
            $message->setText('ERROR');
            $response->addMessage($message);
        }
    }
}
