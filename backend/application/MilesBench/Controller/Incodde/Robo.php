<?php

namespace MilesBench\Controller\Incodde;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Robo {

    public function removeAccents($string){
        $string = str_replace('ç', 'c', $string);
        $string = str_replace('Ç', 'C', $string);
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç|Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$string);
    }
    
    function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public function newOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        try {
            if(isset($dados['businesspartner'])) {
                $UserPartner = $dados['businesspartner'];
            }
            if(!isset($dados['order'])) {
                throw new \Exception("Erro!");
            }

            $order = $dados['order'];
            $onlineflights = $dados['onlineflights'];
            $em = Application::getInstance()->getEntityManager();

            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findBy(array('order' => $order['id']));
            if(count($RobotEmissionIn8) > 0) {
                throw new \Exception("EMISSAO EM ANDAMENTO");
            }

            $fichas = [];
            foreach ($onlineflights as $key => $value) {
                if(!isset($fichas[self::clean($value['card_number'])])) {
                    $fichas[self::clean($value['card_number'])] = ['quant' => 1, 'trechos' => []];
                } else {
                    $fichas[self::clean($value['card_number'])]['quant']++;
                }
            }

            foreach ($onlineflights as $flight) {
                foreach ($fichas as $key => $value) {
                    if((int)$flight['card_number'] == (int)$key) {
                        $fichas[$key]['trechos'][] = $flight;
                    }
                }
            }

            foreach ($fichas as $ficha => $trechos) {
                $RobotEmissionIn8 = new \RobotEmissionIn8();
                $onlineflight = $trechos['trechos'][0];

                $passengers = [];
                $flights = [];
                foreach ($trechos['trechos'] as $trecho) {
                    if(!isset($passengers[$trecho['pax_id']])) {
                        $passengers[$trecho['pax_id']] = $trecho;
                    }
                    if(!isset($flights[$trecho['id']])) {
                        $flights[$trecho['id']] = $trecho;
                    }
                }

                $paxs = 0;
                $total_tax = 0;

                // cartao credito
                $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $onlineflight['tax_card']));
                $card_brand_code = "MC";
                if($onlineflight['tax_cardType'] == 'VISA' || $onlineflight['tax_cardType'] == 'VISA - PJ') {
                    $card_brand_code = "VI";
                }
                if($onlineflight['tax_cardType'] == 'AMERICAN EXPRESS' || $onlineflight['tax_cardType'] == 'AMERICAN') {
                    $card_brand_code = "AX";
                }
                if($onlineflight['tax_cardType'] == 'ELO') {
                    $card_brand_code = "EL";
                }
    
                $CartaoPagamento = array(
                    "card_brand_code" => $card_brand_code,
                    "card_number" => $onlineflight['tax_card'],
                    "card_security_code" => self::clean($onlineflight['tax_password']),
                    "card_name" => $onlineflight['tax_providerName'],
                    "card_exp_date" => $InternalCards->getDueDate()->format('m/Y'),
                    "cpf" => $InternalCards->getShowRegistration()
                );
    
                $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $onlineflight['cards_id']));
                $credentials = array(
                    "login" => self::clean($onlineflight['card_number']),
                    "password" => str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', str_replace('\u0000', '', $onlineflight['recovery_password'] ))))))))))))))))),
                    "cpf" => self::clean($onlineflight['card_registrationCode']),
                    "token" => [
                        "area_code" => $onlineflight['token'] != "" ? explode('-', $onlineflight['token'])[0] : "",
                        "number" => $onlineflight['token'] != "" ? explode('-', $onlineflight['token'])[1] : ""
                    ],
                    "email" => 'adm@onemilhas.com.br',
                    "special_card" => (strpos($onlineflight['card_type'], 'RED') !== false || strpos($onlineflight['card_type'], 'BLACK') !== false || strpos($onlineflight['card_type'], 'CLUBE') !== false || strpos($onlineflight['card_type'], 'DIAMANTE') !== false),
                    "low_points" => $onlineflight['low_points'] == 'true' ? true : false
                );

                $Passageiros = array();
                foreach ($passengers as $passenger) {
                    $firstName = '';
                    $lastName = '';

                    // gol e avianca - nome completo || azul e latam inter - nome completo
                    $string = self::removeAccents($passenger['pax_name']);
                    if($passenger['paxLastName'] && $passenger['paxLastName'] != '') {
                        $string .= ' ' . self::removeAccents($passenger['paxLastName']);
                    }
                    if($passenger['paxAgnome'] && $passenger['paxAgnome'] != '') {
                        $string .= ' ' . self::removeAccents($passenger['paxAgnome']);
                    }
                
                    $newName = explode(' ', $string);
                    foreach ($newName as $keyString => $passengerString) {
                        if($keyString == 0) {
                            $firstName = $passengerString;
                        } else {
                            if($keyString == 1) {
                                $lastName .= $passengerString;
                            } else {
                                $lastName .= ' '. $passengerString;
                            }
                        }
                    }

                    $international = false;
                    $AirportoFrom = $em->getRepository('Airport')->findOneBy(array('code' => $passenger['airport_code_from']));
                    if($AirportoFrom) {
                        if($AirportoFrom->getInternational() == 'true') {
                            $international = true;
                        }
                    }
                    $AirportoTo = $em->getRepository('Airport')->findOneBy(array('code' => $passenger['airport_code_to']));
                    if($AirportoTo) {
                        if($AirportoTo->getInternational() == 'true') {
                            $international = true;
                        }
                    }
                    if((strtolower($passenger["airline"]) == 'latam' || strtolower($passenger["airline"]) == 'azul') && !$international) {
                        $firstName = getFirstName(self::removeAccents($passenger['pax_name']), self::removeAccents($passenger['paxLastName']), '');
                        $lastName = getLastName(self::removeAccents($passenger['pax_name']), self::removeAccents($passenger['paxLastName']), '');
                    }

                    $paxs++;
                    $FaixaEtaria = "ADT";
                    if($passenger['is_child'] == "S") {
                        $FaixaEtaria = "CHD";
                    }
                    if($passenger['is_newborn'] == "S") {
                        $FaixaEtaria = "INF";
                        $paxs--;
                    }

                    $Passageiros[] = array(
                        "birth_date" => $passenger['birhtdate'],
                        "gender" => $passenger['gender'] == 'Masculino' ? 'M' : 'F',
                        "name" => array(
                            "first" => $firstName,
                            "last" => $lastName,
                        ),
                        "type" => $FaixaEtaria
                    );
                }

                $going_flight_id = null;
                $returning_flight_id = null;
                $request_id = null;

                $OnlineFlights = $em->getRepository('OnlineFlight')->findBy(array('order' => $order['id']));
                foreach ($OnlineFlights as $key => $value) {
                    $total_tax += (float)$value->getTax() * $paxs;
                    $request_id = $value->getVooOfferId();
                    foreach ($flights as $keyflights => $flight) {
                        if($key == 0) {
                            if($flight['airport_code_from'] ==  $value->getAirportCodeFrom()) {
                                $going_flight_id = $value->getVooId();
                            }
                        } else {
                            if($flight['airport_code_from'] ==  $value->getAirportCodeFrom()) {
                                $returning_flight_id = $value->getVooId();
                            }
                        }
                    }
                    $RobotEmissionIn8->setOrder($value->getOrder());
                }

                // assembling array
                $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
                $postData = array(
                    "request_id" => $request_id,
                    "credentials" => $credentials,
                    "payment" => $CartaoPagamento,
                    "going_flight_id" => $going_flight_id,
                    "returning_flight_id" => $returning_flight_id,
                    "passengers" => $Passageiros,
                    "total_tax" => $total_tax,
                    'url' => "http://34.207.228.97/" . $DirServer . "/backend/application/index.php?rota=/incodde/robo/atualizacao"
                );

                // sending data
                $jsonToPost = str_replace('\u0000', '', json_encode($postData));
                $RobotEmissionIn8->setPost($jsonToPost);
                $RobotEmissionIn8->setAirline(strtolower($onlineflight["airline"]));
                $RobotEmissionIn8->setFicha($ficha);
    
                $ch = curl_init();
                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    curl_setopt($ch, CURLOPT_URL, "http://3.82.220.239:8081/api/".$RobotEmissionIn8->getAirline()."/issue_ticket");
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json",
                    "Authorization: 92F85D8BFDEFEDF5214688CC6A5AA"));
                } else {
                    curl_setopt($ch, CURLOPT_URL, "http://apiemission.sa-east-1.elasticbeanstalk.com/api/".$RobotEmissionIn8->getAirline()."/issue_ticket");
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json",
                    "Authorization: 8B5E86D5FE1FED3CE1E7887D9CB67"));
                }
    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonToPost);
                $result = curl_exec($ch);
                $return = json_decode($result, true);
    
    
                if(isset($return['_id'])) {
                    $RobotEmissionIn8->setIdentificador($return['_id']);
                }
                if(isset($return['results'])) {
                    $RobotEmissionIn8->setFlightLocator( json_encode($return['results']) );
                }
                $RobotEmissionIn8->setBusinesspartner($UserPartner);
                $RobotEmissionIn8->setIssueDate(new \Datetime());
                $RobotEmissionIn8->setRetorno($result);
    
                // saving status
                if(isset($return['progress']['done'])) {
                    $RobotEmissionIn8->setStatus($return['progress']['done']);
                }
                if(isset($return['progress']['total'])) {
                    $RobotEmissionIn8->setAlerta($return['progress']['total']);
                }
                if(isset($return['progress']['label'])) {
                    $RobotEmissionIn8->setAlerta($return['progress']['label']);
                }

                $RobotEmissionIn8->setOnlinePaxId(0);
                $RobotEmissionIn8->setOnlineFlightId(0);
                
                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('Status' => $return['progress']['done']));

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br><br>SRM-IT";

            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ROBO - EMISSAO - POST',
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
            $message->setText('ERROR');
            $response->addMessage($message);
        }
    }

    public function updateOrderStatus(Request $request, Response $response) {
        $dados = $request->getRow();
        $order = $dados['order'];

        try {
            $em = Application::getInstance()->getEntityManager();
            $QueryBuilder = Application::getInstance()->getQueryBuilder();

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));
            $sql = " select * from robot_emission_in8 r where r.order_id = ".$order['id']." group by r.ficha ";
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {

                $curl = curl_init();
                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => 1,
                        curl_setopt($curl, CURLOPT_URL, 'http://3.82.220.239:8081/api/'.$row['airline'].'/emission_report/'.$row['Identificador']),
                        CURLOPT_USERAGENT => 'Codular Sample cURL Request'
                    ));
                    curl_setopt($curl, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json",
                    "Authorization: 92F85D8BFDEFEDF5214688CC6A5AA"));
                } else {
                    curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => 1,
                        curl_setopt($curl, CURLOPT_URL, "http://apiemission.sa-east-1.elasticbeanstalk.com/api/".$row['airline'].'/emission_report/'.$row['Identificador']),
                        CURLOPT_USERAGENT => 'Codular Sample cURL Request'
                    ));
                    curl_setopt($curl, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json",
                    "Authorization: 8B5E86D5FE1FED3CE1E7887D9CB67"));
                }

                $result = curl_exec($curl);
                curl_close($curl);
                $return = json_decode($result, true);

                $RobotEmissionIn8 = new \RobotEmissionIn8();
                $RobotEmissionIn8->setRetorno($result);
                if(isset($return['_id'])) {
                    $RobotEmissionIn8->setIdentificador($return['_id']);
                }
                if(isset($return['results'])) {
                    $RobotEmissionIn8->setFlightLocator( json_encode($return['results']) );
                }

                // saving order reference
                $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));
                $RobotEmissionIn8->setOrder($OnlineOrder);
                $RobotEmissionIn8->setIssueDate(new \Datetime());
                $RobotEmissionIn8->setAirline(strtolower($row['airline']));
                $RobotEmissionIn8->setFicha($row['ficha']);
                $RobotEmissionIn8->setOnlinePaxId(0);
                $RobotEmissionIn8->setOnlineFlightId(0);

                // saving status
                if(isset($return['progress']['done'])) {
                    $RobotEmissionIn8->setStatus($return['progress']['done']);
                }
                if(isset($return['progress']['total'])) {
                    $RobotEmissionIn8->setAlerta($return['progress']['total']);
                }
                if(isset($return['progress']['label'])) {
                    $RobotEmissionIn8->setSucesso($return['progress']['label']);
                }
                if(isset($return['end']) && $return['end'] != null) {
                    if($return['progress']['done'] != $return['progress']['total']) {
                        if($return['end'] != null && $return['end'] != 'null') {
                            $RobotEmissionIn8->setSucesso($RobotEmissionIn8->getSucesso() . ' -- Erro na emissao');
                        }
                    }
                }

                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array());

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";

            $email1 = 'suporte@onemilhas.com.br';
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
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $dataset = array();
        if(isset($order['id'])) {

            $sql = " SELECT DISTINCT ficha FROM robot_emission_in8 where order_id = ".$order['id'];
            $stmt = $QueryBuilder->query($sql);
            while ($row2 = $stmt->fetch()) {

                $sql = " select * from robot_emission_in8 r where r.ficha = '".$row2['ficha']."' and r.order_id = ".$order['id']." ORDER by r.issue_date DESC limit 1 ";
                $stmt = $QueryBuilder->query($sql);
                while ($row = $stmt->fetch()) {
                    $dataset[] = array(
                        'id' => $row['id'],
                        'ficha' => $row['ficha'],
                        'airline' => $row['airline'],
                        'identificador' => $row['Identificador'],
                        'flightLocator' => $row['flight_locator'],
                        'fileId' => $row['file_id'],
                        'status' => $row['status'],
                        'alerta' => $row['alerta'],
                        'erro' => $row['erro'],
                        'sucesso' => $row['sucesso']
                    );
                }
            }
        }
        $response->setDataset($dataset);
    }

    public function removerRobo(Request $request, Response $response) {
        $dados = $request->getRow();
        $order = $dados['order'];
        $em = Application::getInstance()->getEntityManager();

        $dataset = array();
        if(isset($order['id'])) {
            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findBy(array('order' => $order['id']));
            foreach ($RobotEmissionIn8 as $key => $value) {
                $em->remove($value);
				$em->flush($value);
            }
        }

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Operação realizada com sucesso');
        $response->addMessage($message);
    }

    public function update(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);

        try {
            $em = Application::getInstance()->getEntityManager();
            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findOneBy(array('identificador' => $dados['_id']), array('id' => 'DESC'));

            if(!$RobotEmissionIn8) {
                throw new Exception("Emissao não encontrada no banco de dados!", 1);
            }

            $OnlineOrder = $RobotEmissionIn8->getOrder();
            $OnlineFlights = $em->getRepository('OnlineFlight')->findBy(array('order' => $OnlineOrder->getId()));
            $OnlineFlight = $OnlineFlights[0];

            // Get cURL resource
            $curl = curl_init();
            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    curl_setopt($curl, CURLOPT_URL, 'http://3.82.220.239:8081/api/azul/emission_report/'.$RobotEmissionIn8->getIdentificador()),
                    CURLOPT_USERAGENT => 'Codular Sample cURL Request'
                ));
                curl_setopt($curl, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json",
                "Authorization: 92F85D8BFDEFEDF5214688CC6A5AA"));
            } else {
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    curl_setopt($curl, CURLOPT_URL, \MilesBench\Util::api_voos_url_production."/api/".strtolower($OnlineFlight->getAirline()).'/emission_report/'.$RobotEmissionIn8->getIdentificador()),
                    curl_setopt($curl, CURLOPT_URL, "http://apiemission.sa-east-1.elasticbeanstalk.com/api/".strtolower($OnlineFlight->getAirline()).'/emission_report/'.$RobotEmissionIn8->getIdentificador()),
                    CURLOPT_USERAGENT => 'Codular Sample cURL Request'
                ));
                curl_setopt($curl, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json",
                "Authorization: 8B5E86D5FE1FED3CE1E7887D9CB67"));
            }

            // Send the request & save response to $resp
            $result = curl_exec($curl);
            // Close request to clear up some resources
            curl_close($curl);

            $email1 = 'suporte@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $result,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ROBO - EMISSAO - UPDATE ',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_exec($ch);

            $return = json_decode($result, true);

            if(isset($return['_id'])) {
                $RobotEmissionIn8 = new \RobotEmissionIn8();

                if(isset($return['_id'])) {
                    $RobotEmissionIn8->setIdentificador($return['_id']);
                }
                if(isset($return['results'])) {
                    $RobotEmissionIn8->setFlightLocator( json_encode($return['results']) );
                }

                // saving order reference
                $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));
                $RobotEmissionIn8->setOrder($OnlineOrder);
                $RobotEmissionIn8->setIssueDate(new \Datetime());

                // saving status
                if(isset($return['progress']['done'])) {
                    $RobotEmissionIn8->setStatus($return['progress']['done']);
                }
                if(isset($return['progress']['total'])) {
                    $RobotEmissionIn8->setAlerta($return['progress']['total']);
                }
                if(isset($return['progress']['label'])) {
                    $RobotEmissionIn8->setSucesso($return['progress']['label']);
                }
                if(isset($return['end'])) {
                    if($return['progress']['done'] != $return['progress']['total']) {
                        if($return['end'] != null && $return['end'] != 'null') {
                            $RobotEmissionIn8->setSucesso('Erro na emissao');
                        }
                    }
                }

                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('Status' => $return['progress']['done']));

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";

            $email1 = 'suporte@onemilhas.com.br';
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

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('ERROR');
            $response->addMessage($message);
        }
    }

    public function removeFromRobo(Request $request, Response $response) {
        $dados = $request->getRow();
        $order = $dados['order'];

        try {
            $em = Application::getInstance()->getEntityManager();
            $QueryBuilder = Application::getInstance()->getQueryBuilder();

            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findBy(array('order' => $order['id']));
            if(!$RobotEmissionIn8) {
                throw new Exception("Emissao não encontrada no banco de dados!", 1);
            }
            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));

            $sql = " select * from robot_emission_in8 r where r.order_id = ".$order['id']." group by r.ficha ";
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {

                $jsonToPost = array(
                    'id' => $row['Identificador']
                );
    
                $ch = curl_init();
                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    curl_setopt($ch, CURLOPT_URL, "http://3.82.220.239:8081/api/emissions/cancel");
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json",
                    "Authorization: 92F85D8BFDEFEDF5214688CC6A5AA"));
                } else {
                    curl_setopt($ch, CURLOPT_URL, "http://apiemission.sa-east-1.elasticbeanstalk.com/api/emissions/cancel");
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json",
                    "Authorization: 8B5E86D5FE1FED3CE1E7887D9CB67"));
                }
    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonToPost);
                $result = curl_exec($ch);
                $return = json_decode($result, true);

                $RobotEmissionIn8 = new \RobotEmissionIn8();
                $RobotEmissionIn8->setRetorno($result);
                if(isset($return['_id'])) {
                    $RobotEmissionIn8->setIdentificador($return['_id']);
                }
                if(isset($return['results'])) {
                    $RobotEmissionIn8->setFlightLocator( json_encode($return['results']) );
                }

                // saving order reference
                $RobotEmissionIn8->setOrder($OnlineOrder);
                $RobotEmissionIn8->setIssueDate(new \Datetime());
                $RobotEmissionIn8->setAirline(strtolower($row['airline']));
                $RobotEmissionIn8->setFicha($row['ficha']);

                $RobotEmissionIn8->setOnlinePaxId(0);
                $RobotEmissionIn8->setOnlineFlightId(0);

                // saving status
                if(isset($return['progress']['done'])) {
                    $RobotEmissionIn8->setStatus($return['progress']['done']);
                }
                if(isset($return['progress']['total'])) {
                    $RobotEmissionIn8->setAlerta($return['progress']['total']);
                }
                if(isset($return['progress']['label'])) {
                    $RobotEmissionIn8->setSucesso($return['progress']['label']);
                }
                if(isset($return['end']) && $return['end'] != null) {
                    if($return['progress']['done'] != $return['progress']['total']) {
                        if($return['end'] != null && $return['end'] != 'null') {
                            $RobotEmissionIn8->setSucesso($RobotEmissionIn8->getSucesso() . ' -- Erro na emissao');
                        }
                    }
                }

                $em->persist($RobotEmissionIn8);
                $em->flush($RobotEmissionIn8);

            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso');
            $response->addMessage($message);
            $response->setDataset(array());

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";

            echo $content;
            $email1 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'partner' => $email1,
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

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('ERROR');
            $response->addMessage($message);
        }
    }
}

function blackListNames($name) {
    $array = ['JUNIOR' => true, 'NETO' => true, 'FILHO' => true, 'SOBRINHO' => true];
    return isset( $array[$name] );
}

function getFirstName($string, $paxLastName, $agnome) {
    $arrayName = explode(' ', $string);
    foreach ($arrayName as $key => $value) {
        if($value != 'MMS' && $value != '-')
            return $value;
    }
}

function getLastName($string, $paxLastName, $agnome) {
    if(isset($paxLastName) && $paxLastName != '') {
        $string .= ' ' . $paxLastName;
    }

    if(isset($agnome) && $agnome != '') {
        $string .= ' ' . $agnome;
    }

    $newName = '';
    $arrayName = explode(' ', $string);
    foreach ($arrayName as $key => $value) {
        if(count($arrayName) -1 == $key) {
            if(blackListNames($value)) {
                $newName = $arrayName[$key -1] . ' ' . $value;
            } else {
                $newName = $value;
            }
        }
    }

    return str_replace("'", "", $newName);
}
