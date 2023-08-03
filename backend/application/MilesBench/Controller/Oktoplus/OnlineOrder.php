<?php

namespace MilesBench\Controller\Oktoplus;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class OnlineOrder {

    public function save(Request $request, Response $response) {
        $content = "<br>".$request->getRow()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
        $email1 = 'emissao@onemilhas.com.br';
        $email2 = 'adm@onemilhas.com.br';
        $postfields = array(
            'content' => $content,
            'from' => $email1,
            'partner' => $email2,
            'subject' => 'ERROR - PEDIDOS',
            'type' => ''
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $dados = json_decode($request->getRow(), true);
        $cotacao = $dados['cotacao'];

        try {
            $em = Application::getInstance()->getEntityManager();
            $em->getConnection()->beginTransaction();

            $onlineOrder = new \OnlineOrder();
            $onlineOrder->setAirline('');
            $onlineOrder->setExternalId($dados['id']);
            $onlineOrder->setClientName($dados['usuario']['login']);
            $onlineOrder->setClientLogin($dados['usuario']['login']);
            $onlineOrder->setClientEmail($cotacao['dadosComprador']['email']);
            $onlineOrder->setMilesUsed($cotacao['resumoTotal']['pontos']);
            if(isset($cotacao['resumoTotal']['observacao']) && $cotacao['resumoTotal']['observacao'] != '') {
                $onlineOrder->setComments($cotacao['resumoTotal']['observacao']);
            }
            $onlineOrder->setStatus('PENDENTE');
            $onlineOrder->setBoardingDate(new \Datetime());
            $onlineOrder->setLandingDate(new \Datetime());
            $onlineOrder->setCreatedAt(new \DateTime());
            $onlineOrder->setPaymentMethod($cotacao['dadosComprador']['tpPagamento']);
            $onlineOrder->setEconomy($cotacao['resumoTotal']['economia']);
            $onlineOrder->setEmissionMethod($dados['usuario']['origem']);
            $onlineOrder->setOriginalSystem('oktoplus');

            $onlineOrder->setOrderPost( json_encode( $dados ));

            $airlines = '';

            $valueTax = 0;
            $trecho = $cotacao['resumoIda'];
            foreach($cotacao['passageiros'] as $passageiro){
                if($passageiro['tipo'] != "INF") {
                    if(isset($trecho['taxa'])) {
                        $valueTax = $valueTax + $trecho['taxaEmbarque'];
                    }
                }
            }
            $trecho = $cotacao['resumoVolta'];
            foreach($cotacao['passageiros'] as $passageiro){
                if($passageiro['tipo'] != "INF") {
                    if(isset($trecho['taxa'])) {
                        $valueTax = $valueTax + $trecho['taxaEmbarque'];
                    }
                }
            }
            $onlineOrder->setTotalCost($cotacao['resumoTotal']['precoTotal'] + $valueTax);
            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $flightsDates = array();

            // IDA
            $resumoIda = $cotacao['resumoIda'];
            $vooIda = $cotacao['vooIda'];

            $onlineFlight = new \OnlineFlight();
            $onlineFlight->setOrder($onlineOrder);
            if(explode(' ', $vooIda['ciaAereaParceira'])[0] == 'DELTA' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AIRFRANCE' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'KLM' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'QATAR' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AEROLINHAS' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'ETIHAD' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'TAP' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'ALITALIA' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'COPA' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'KOREAN' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AIR' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AEROMEXICO' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'EMIRATES') {
                $onlineFlight->setAirline('GOL');
            } else if(explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AMERICAN') {
                $onlineFlight->setAirline('LATAM');
            } else {
                $onlineFlight->setAirline(explode(' ', $vooIda['ciaAereaParceira'])[0]);
            }
            $airlines .= $onlineFlight->getAirline();
            $onlineFlight->setAirportCodeFrom($this->getIata($vooIda['origem']));
            $onlineFlight->setAirportCodeTo($this->getIata($vooIda['destino']));
            $onlineFlight->setAirportDescriptionFrom($vooIda['origem']);
            $onlineFlight->setAirportDescriptionTo($vooIda['destino']);


            $data_embarque = substr($vooIda['partida'],6,4).'-'.substr($vooIda['partida'],3,2).'-'.substr($vooIda['partida'],0,2).' '.substr($vooIda['partida'],11);
            $onlineFlight->setBoardingDate(new \Datetime($data_embarque));
            $flightsDates[] = new \Datetime($data_embarque);
            $onlineOrder->setFirstBoardingDate(new \Datetime($data_embarque));

            $data_pouso = substr($vooIda['chegada'],6,4).'-'.substr($vooIda['chegada'],3,2).'-'.substr($vooIda['chegada'],0,2).' '.substr($vooIda['chegada'],11);
            $onlineFlight->setLandingDate(new \Datetime($data_pouso));

            $onlineFlight->setCost($resumoIda['precoAgencia']);
            if($onlineFlight->getAirline() == 'AZUL') {
                $onlineFlight->setCost($resumoIda['precoAgencia'] + 20);
            }
            $onlineFlight->setMilesUsed($resumoIda['pontos']);
            $onlineFlight->setNumberOfAdult($dados['filtros']['adultos']);
            $onlineFlight->setNumberOfChild($dados['filtros']['criancas']);
            $onlineFlight->setNumberOfNewborn($dados['filtros']['bebes']);

            $onlineFlight->setCostPerAdult($resumoIda['precoAgenciaAdulto']);
            if($onlineFlight->getAirline() == 'AZUL') {
                $onlineFlight->setCostPerAdult($resumoIda['precoAgenciaAdulto'] + 20);
            }
            if(isset($resumoIda['precoAgenciaCrianca'])) {
                $onlineFlight->setCostPerChild($resumoIda['precoAgenciaCrianca']);
                if($onlineFlight->getAirline() == 'AZUL') {
                    $onlineFlight->setCostPerChild($resumoIda['precoAgenciaCrianca'] + 20);
                }
            }
            if(isset($resumoIda['precoAgenciaBebe'])) {
                $onlineFlight->setCostPerNewborn($resumoIda['precoAgenciaBebe']);
            }

            $onlineFlight->setMilesPerAdult($resumoIda['pontosAdulto']);
            if(isset($resumoIda['pontosCrianca'])) {
                $onlineFlight->setMilesPerChild($resumoIda['pontosCrianca']);
            }
            if(isset($resumoIda['pontosBebe'])) {
                $onlineFlight->setMilesPerNewborn($resumoIda['pontosBebe']);
            }

            $onlineFlight->setFlight($vooIda['nrVoo']);
            if($onlineFlight->getAirline() == 'LATAM') {
                $onlineFlight->setFlight('JJ'.$vooIda['nrVoo']);
            } else if($onlineFlight->getAirline() == 'AZUL') {
                $onlineFlight->setFlight('AD'.$vooIda['nrVoo']);
            }

            $vooIda['duracao'] = str_replace("m", "", $vooIda['duracao']);
            $vooIda['duracao'] = str_replace("h", ":", $vooIda['duracao']);
            $onlineFlight->setFlightTime($vooIda['duracao']);
            if(isset($vooIda['taxaEmbarque']) && $vooIda['taxaEmbarque'] != null && $vooIda['taxaEmbarque'] != '') {
                $onlineFlight->setTax($vooIda['taxaEmbarque']);
            } else {
                if(isset($resumoIda['taxaEmbarque']) && $resumoIda['taxaEmbarque'] != null && $resumoIda['taxaEmbarque'] != '') {
                    $onlineFlight->setTax($resumoIda['taxaEmbarque']);
                } else {
                    $onlineFlight->setTax(0);
                }
            }

            if(isset($dados['filtros']['cabine']) && $dados['filtros']['cabine'] != '') {
                if($dados['filtros']['cabine'] === "EXECUTIVA") {
                    $dados['filtros']['cabine'] = 'Executiva';
                } else if($dados['filtros']['cabine'] === "ECONOMICA") {
                    $dados['filtros']['cabine'] = 'Economica';
                }
                $onlineFlight->setClass($dados['filtros']['cabine']);
            }

            $em->persist($onlineFlight);
            if(isset($vooIda['trechos'])) {
                $conexaoFlight = '';
                if(count($vooIda['trechos']) == 0) {
                    $conexaoFlight = 'Direto ';
                }

                foreach($vooIda['trechos'] as $conexao){
                    $OnlineConnection = new \OnlineConnection();
                    $OnlineConnection->setFlight($conexao['nrVoo']);
                    if($onlineFlight->getAirline() == 'LATAM') {
                        $OnlineConnection->setFlight('JJ'.$conexao['nrVoo']);
                    } else if($onlineFlight->getAirline() == 'AZUL') {
                        $OnlineConnection->setFlight('AD'.$conexao['nrVoo']);
                    }

                    $hourdiff = round((strtotime($conexao['chegada']) - strtotime($conexao['partida']))/3600, 1);
                    $OnlineConnection->setFlightTime($hourdiff);

                    $OnlineConnection->setBoarding((new \DateTime($conexao['partida']))->format('H:i'));
                    $OnlineConnection->setLanding((new \DateTime($conexao['chegada']))->format('H:i'));
                    $OnlineConnection->setAirportCodeFrom($conexao['origem']);
                    $OnlineConnection->setAirportCodeTo($conexao['destino']);
                    $OnlineConnection->setOnlineFlight($onlineFlight);
                    $em->persist($OnlineConnection);
                    $em->flush($OnlineConnection);
                    $conexaoFlight = $conexaoFlight.' '.
                                    $conexao['nrVoo'];
                }
                $onlineFlight->setConnection($conexaoFlight);
            }
            $em->persist($onlineFlight);
            $em->flush($onlineFlight);



            // VOLTA
            if(isset($cotacao['vooVolta']) && isset($cotacao['resumoVolta'])) {
                $resumoVolta = $cotacao['resumoVolta'];
                $vooVolta = $cotacao['vooVolta'];

                $onlineFlight = new \OnlineFlight();
                $onlineFlight->setOrder($onlineOrder);

                if(explode(' ', $vooIda['ciaAereaParceira'])[0] == 'DELTA' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AIRFRANCE' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'KLM' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'QATAR' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AEROLINHAS' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'ETIHAD' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'TAP' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'ALITALIA' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'COPA' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'KOREAN' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AIR' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AEROMEXICO' || explode(' ', $vooIda['ciaAereaParceira'])[0] == 'EMIRATES') {
                    $onlineFlight->setAirline('GOL');
                } else if(explode(' ', $vooIda['ciaAereaParceira'])[0] == 'AMERICAN') {
                        $onlineFlight->setAirline('LATAM');
                } else {
                    $onlineFlight->setAirline(explode(' ', $vooVolta['ciaAereaParceira'])[0]);
                }
                $airlines .= ' | ' . $onlineFlight->getAirline();
                $onlineFlight->setAirportCodeFrom($this->getIata($vooVolta['origem']));
                $onlineFlight->setAirportCodeTo($this->getIata($vooVolta['destino']));
                $onlineFlight->setAirportDescriptionFrom($vooVolta['origem']);
                $onlineFlight->setAirportDescriptionTo($vooVolta['destino']);


                $data_embarque = substr($vooVolta['partida'],6,4).'-'.substr($vooVolta['partida'],3,2).'-'.substr($vooVolta['partida'],0,2).' '.substr($vooVolta['partida'],11);
                $onlineFlight->setBoardingDate(new \Datetime($data_embarque));
                $flightsDates[] = new \Datetime($data_embarque);

                $data_pouso = substr($vooVolta['chegada'],6,4).'-'.substr($vooVolta['chegada'],3,2).'-'.substr($vooVolta['chegada'],0,2).' '.substr($vooVolta['chegada'],11);
                $onlineFlight->setLandingDate(new \Datetime($data_pouso));

                $onlineFlight->setCost($resumoVolta['precoAgencia']);
                if($onlineFlight->getAirline() == 'AZUL') {
                    $onlineFlight->setCost($resumoVolta['precoAgencia'] + 20);
                }
                $onlineFlight->setMilesUsed($resumoVolta['pontos']);
                $onlineFlight->setNumberOfAdult($dados['filtros']['adultos']);
                $onlineFlight->setNumberOfChild($dados['filtros']['criancas']);
                $onlineFlight->setNumberOfNewborn($dados['filtros']['bebes']);

                $onlineFlight->setCostPerAdult($resumoVolta['precoAgenciaAdulto']);
                if($onlineFlight->getAirline() == 'AZUL') {
                    $onlineFlight->setCostPerAdult($resumoVolta['precoAgenciaAdulto'] + 20);
                }
                if(isset($resumoVolta['precoAgenciaCrianca'])) {
                    $onlineFlight->setCostPerChild($resumoVolta['precoAgenciaCrianca']);
                    if($onlineFlight->getAirline() == 'AZUL') {
                        $onlineFlight->setCostPerChild($resumoVolta['precoAgenciaCrianca'] + 20);
                    }
                }
                if(isset($resumoVolta['precoAgenciaBebe'])) {
                    $onlineFlight->setCostPerNewborn($resumoVolta['precoAgenciaBebe']);
                }

                $onlineFlight->setMilesPerAdult($resumoVolta['pontosAdulto']);
                if(isset($resumoVolta['pontosCrianca'])) {
                    $onlineFlight->setMilesPerChild($resumoVolta['pontosCrianca']);
                }
                if(isset($resumoVolta['pontosBebe'])) {
                    $onlineFlight->setMilesPerNewborn($resumoVolta['pontosBebe']);
                }

                $onlineFlight->setFlight($vooVolta['nrVoo']);
                if($onlineFlight->getAirline() == 'LATAM') {
                    $onlineFlight->setFlight('JJ'.$vooVolta['nrVoo']);
                } else if($onlineFlight->getAirline() == 'AZUL') {
                    $onlineFlight->setFlight('AD'.$vooVolta['nrVoo']);
                }

                $vooVolta['duracao'] = str_replace("m", "", $vooVolta['duracao']);
                $vooVolta['duracao'] = str_replace("h", ":", $vooVolta['duracao']);
                $onlineFlight->setFlightTime($vooVolta['duracao']);
                if(isset($vooVolta['taxaEmbarque']) && $vooVolta['taxaEmbarque'] != null && $vooVolta['taxaEmbarque'] != '') {
                    $onlineFlight->setTax($vooVolta['taxaEmbarque']);
                } else {
                    if(isset($resumoVolta['taxaEmbarque']) && $resumoVolta['taxaEmbarque'] != null && $resumoVolta['taxaEmbarque'] != '') {
                        $onlineFlight->setTax($resumoVolta['taxaEmbarque']);
                    } else {
                        $onlineFlight->setTax(0);
                    }
                }

                if(isset($dados['filtros']['cabine']) && $dados['filtros']['cabine'] != '') {
                    if($dados['filtros']['cabine'] === "EXECUTIVA") {
                        $dados['filtros']['cabine'] = 'Executiva';
                    } else if($dados['filtros']['cabine'] === "ECONOMICA") {
                        $dados['filtros']['cabine'] = 'Economica';
                    }
                    $onlineFlight->setClass($dados['filtros']['cabine']);
                }

                $em->persist($onlineFlight);
                if(isset($vooVolta['trechos'])) {
                    $conexaoFlight = '';
                    if(count($vooVolta['trechos']) == 0) {
                        $conexaoFlight = 'Direto ';
                    }

                    foreach($vooVolta['trechos'] as $conexao){
                        $OnlineConnection = new \OnlineConnection();
                        $OnlineConnection->setFlight($conexao['nrVoo']);
                        if($onlineFlight->getAirline() == 'LATAM') {
                            $OnlineConnection->setFlight('JJ'.$conexao['nrVoo']);
                        } else if($onlineFlight->getAirline() == 'AZUL') {
                            $OnlineConnection->setFlight('AD'.$conexao['nrVoo']);
                        }

                        $hourdiff = round((strtotime($conexao['chegada']) - strtotime($conexao['partida']))/3600, 1);
                        $OnlineConnection->setFlightTime($hourdiff);

                        $OnlineConnection->setBoarding((new \DateTime($conexao['partida']))->format('H:i'));
                        $OnlineConnection->setLanding((new \DateTime($conexao['chegada']))->format('H:i'));
                        $OnlineConnection->setAirportCodeFrom($conexao['origem']);
                        $OnlineConnection->setAirportCodeTo($conexao['destino']);
                        $OnlineConnection->setOnlineFlight($onlineFlight);
                        $em->persist($OnlineConnection);
                        $em->flush($OnlineConnection);
                        $conexaoFlight = $conexaoFlight.' '.
                                        $conexao['nrVoo'];
                    }
                    $onlineFlight->setConnection($conexaoFlight);
                }
                $em->persist($onlineFlight);
                $em->flush($onlineFlight);
            }


            foreach($cotacao['passageiros'] as $passageiro){
                $onlinePax = new \OnlinePax();
                $onlinePax->setOrder($onlineOrder);
                $onlinePax->setPaxName(trim(mb_strtoupper($passageiro['nome'], 'UTF-8')));
                $onlinePax->setPaxLastName(trim(mb_strtoupper($passageiro['sobrenome'], 'UTF-8')));

                $data_nascimento = substr($passageiro['dtNascimento'],6,4).'-'.substr($passageiro['dtNascimento'],3,2).'-'.substr($passageiro['dtNascimento'],0,2);
                $onlinePax->setBirthdate(new \Datetime($data_nascimento));

                $onlinePax->setIdentification($passageiro['cpf']);
                $onlinePax->setGender(substr($passageiro['sexo'], 0, 1));

                if($passageiro['tipo'] == 'ADL') {
                    $onlinePax->setIsNewborn('N');
                    $onlinePax->setIsChild('N');
                } else if($passageiro['tipo'] == 'CHD') {
                    $onlinePax->setIsNewborn('N');
                    $onlinePax->setIsChild('S');
                } else {
                    $onlinePax->setIsNewborn('S');
                    $onlinePax->setIsChild('N');
                }

                $em->persist($onlinePax);
                $em->flush($onlinePax);

                // baggage
                if(isset($passageiro['qtdeBagagensIda']) && $passageiro['qtdeBagagensIda'] != '') {
                    if($passageiro['qtdeBagagensIda'] > 0) {
                        $onlineFlight = $em->getRepository('OnlineFlight')
                            ->findOneBy( array( 'airportCodeFrom' => $this->getIata($vooIda['origem']), 'airportCodeTo' => $this->getIata($vooIda['destino']), 'order' => $onlineOrder->getId() ));
                        $OnlineBaggage = new \OnlineBaggage();
                        $OnlineBaggage->setAmount($passageiro['qtdeBagagensIda']);
                        $OnlineBaggage->setOnlineFlight($onlineFlight);
                        $OnlineBaggage->setOnlinePax($onlinePax);
                        $em->persist($OnlineBaggage);
                        $em->flush($OnlineBaggage);
                    }
                }

                if(isset($passageiro['qtdeBagagensVolta']) && $passageiro['qtdeBagagensVolta'] != '') {
                    if($passageiro['qtdeBagagensVolta'] > 0) {
                        $onlineFlight = $em->getRepository('OnlineFlight')
                            ->findOneBy( array( 'airportCodeFrom' => $this->getIata($vooVolta['origem']), 'airportCodeTo' => $this->getIata($vooVolta['destino']), 'order' => $onlineOrder->getId() ));
                        $OnlineBaggage = new \OnlineBaggage();
                        $OnlineBaggage->setAmount($passageiro['qtdeBagagensVolta']);
                        $OnlineBaggage->setOnlineFlight($onlineFlight);
                        $OnlineBaggage->setOnlinePax($onlinePax);
                        $em->persist($OnlineBaggage);
                        $em->flush($OnlineBaggage);
                    }
                }
            }

            $onlineOrder->setAirline($airlines);
            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $em->getConnection()->commit();

            ///////////////////////////////////////////////////////////////////////////////////////////
            // PRODUCTION ONLY
            $result = updateOrders();
            ///////////////////////////////////////////////////////////////////////////////////////////

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Pedido incluido com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('order_id' => $onlineOrder->getId()));

        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - PEDIDOS',
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
            $message->setText('Erro identificado');
            $response->addMessage($message);
        }
    }

    public function validateDate($date, $format = 'Y-m-d H:i:s') {
        $day = 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $date);
        $d2 = \DateTime::createFromFormat($day, $date);
        if($d)
            return $d->format($format) == $date;
        if($d2)
            return $d2->format($day) == $date;
        return false;
    }

    public static function validateOrder($Client, $amount, $flightsData, $cia, $order_id) {
        try {

            $em = Application::getInstance()->getEntityManager();

            // first emission track
            $Notification = $em->getRepository('ClientNotification')->findOneBy(
                array(
                    'client' => $Client->getId(),
                    'notification' => 1
                )
            );
            if($Notification) {
                $Sales = $em->getRepository('Sale')->findBy(array('client' => $Client->getId()));
                if(count($Sales) == 0) {
                    $postfields = array(
                        'client' => $Client->getName()
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/firstIssue');
                    // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/firstIssue');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);
                }
            }


            // limit calc track
            $NotificationLimit = $em->getRepository('ClientNotification')->findOneBy(
                array(
                    'client' => $Client->getId(),
                    'notification' => 2
                )
            );
            if($NotificationLimit) {
                if((float)$Client->getPartnerLimit() != '0') {

                    if($Client->getStatus() == 'Coberto') {

                        $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                        $query = $em->createQuery($sql);
                        $Limit = $query->getResult();

                        $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE') and o.clientName in (select b.name from businesspartner b where b.clientId = '".$Client->getId()."' ) ";
                        $query = $em->createQuery($sql);
                        $OrdersLimit = $query->getResult();

                        $sql = " select DISTINCT(sb.sale) as sale from SaleBillsreceive sb JOIN sb.billsreceive b where b.client = '".$Client->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
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
                            if($sale->getIssueDate()->diff($sale->getBoardingDate())->days > (float)$Client->getDaysToBoarding()) {
                                $SalesLimit += (float)$sale->getAmountPaid();
                            }
                        }

                        $usedValue = ((float)$Limit[0]['partner_limit'] + $SalesLimit + (float)$OrdersLimit[0]['cost']);

                        $limit1 = (float)$Client->getPartnerLimit() + (((float)$Client->getLimitMargin() / 100) * (float)$Client->getPartnerLimit());
                    } else {

                        //limit 1 calculation
                        $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                        $query = $em->createQuery($sql);
                        $Limit = $query->getResult();

                        $sql = "select SUM(b.actualValue) as partner_limit FROM Billsreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
                        $query = $em->createQuery($sql);
                        $SalesLimit = $query->getResult();

                        $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE') and o.clientName in (select b.name from businesspartner b where b.clientId = '".$Client->getId()."' ) ";
                        $query = $em->createQuery($sql);
                        $OrdersLimit = $query->getResult();

                        $usedValue = ((float)$Limit[0]['partner_limit'] + (float)$SalesLimit[0]['partner_limit'] + (float)$OrdersLimit[0]['cost']);

                        $limit1 = (float)$Client->getPartnerLimit() + (((float)$Client->getLimitMargin() / 100) * (float)$Client->getPartnerLimit());
                    }

                    //limit 2 calculation
                    $sql = "select SUM(s.amountPaid) as amountPaid FROM Sale s WHERE s.client = '".$Client->getId()."' and s.status = 'Emitido' and s.boardingDate >= '".(new \DateTime())->modify('+19 day')->format('Y-m-d')."' ";
                    $query = $em->createQuery($sql);
                    $SecondyLimit = $query->getResult();

                    if($SecondyLimit) {
                        $limit2 = ((float)$SecondyLimit[0]['amountPaid'] * 0.6);
                    } else {
                        $limit2 = 0;
                    }

                    //total limit
                    $totalLimit = $limit1 + $limit2;

                    if($totalLimit < $usedValue){
                        $postfields = array(
                            'client' => $Client->getName()
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/limitBroke');
                        // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/limitBroke');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        $result = curl_exec($ch);

                    } else {

                        // limit avaliable
                        // if($cia == 'GOL') {
                        //     $postfields = array(
                        //         'order_id' => $order_id,
                        //         'hashId' => '9901401e7398b65912d5cae4364da460'
                        //     );
                        //     $ch = curl_init();
                        //     curl_setopt($ch, CURLOPT_URL, 'https://gestao.srm.systems/cml-gestao/backend/application/index.php?rota=/automaticEmissionRobot');
                        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        //     curl_setopt($ch, CURLOPT_POST, 1);
                        //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        //     $result = curl_exec($ch);
                        // }
                    }
                }
            } else {

                if($Client->getStatus() == 'Coberto') {

                    $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                    $query = $em->createQuery($sql);
                    $Limit = $query->getResult();

                    $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE') and o.clientName in (select b.name from businesspartner b where b.clientId = '".$Client->getId()."' ) ";
                    $query = $em->createQuery($sql);
                    $OrdersLimit = $query->getResult();

                    $sql = " select DISTINCT(sb.sale) as sale from SaleBillsreceive sb JOIN sb.billsreceive b where b.client = '".$Client->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
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
                        if($sale->getIssueDate()->diff($sale->getBoardingDate())->days > (float)$Client->getDaysToBoarding()) {
                            $SalesLimit += (float)$sale->getAmountPaid();
                        }
                    }

                    $usedValue = ((float)$Limit[0]['partner_limit'] + $SalesLimit + (float)$OrdersLimit[0]['cost']);

                    $limit1 = (float)$Client->getPartnerLimit() + (((float)$Client->getLimitMargin() / 100) * (float)$Client->getPartnerLimit());
                } else {

                    //limit 1 calculation
                    $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
                    $query = $em->createQuery($sql);
                    $Limit = $query->getResult();

                    $sql = "select SUM(b.actualValue) as partner_limit FROM Billsreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
                    $query = $em->createQuery($sql);
                    $SalesLimit = $query->getResult();

                    $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE') and o.clientName in (select b.name from businesspartner b where b.clientId = '".$Client->getId()."' ) ";
                    $query = $em->createQuery($sql);
                    $OrdersLimit = $query->getResult();

                    $usedValue = ((float)$Limit[0]['partner_limit'] + (float)$SalesLimit[0]['partner_limit'] + (float)$OrdersLimit[0]['cost']);

                    $limit1 = (float)$Client->getPartnerLimit() + (((float)$Client->getLimitMargin() / 100) * (float)$Client->getPartnerLimit());
                }

                //limit 2 calculation
                $sql = "select SUM(s.amountPaid) as amountPaid FROM Sale s WHERE s.client = '".$Client->getId()."' and s.status = 'Emitido' and s.boardingDate >= '".(new \DateTime())->modify('+19 day')->format('Y-m-d')."' ";
                $query = $em->createQuery($sql);
                $SecondyLimit = $query->getResult();

                if($SecondyLimit) {
                    $limit2 = ((float)$SecondyLimit[0]['amountPaid'] * 0.6);
                } else {
                    $limit2 = 0;
                }

                //total limit
                $totalLimit = $limit1 + $limit2;

                if($totalLimit < $usedValue){
                } else {

                    // limit avaliable
                    // if($cia == 'GOL') {
                    //     $postfields = array(
                    //         'order_id' => $order_id,
                    //         'hashId' => '9901401e7398b65912d5cae4364da460'
                    //     );
                    //     $ch = curl_init();
                    //     curl_setopt($ch, CURLOPT_URL, 'https://gestao.srm.systems/cml-gestao/backend/application/index.php?rota=/automaticEmissionRobot');
                    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    //     curl_setopt($ch, CURLOPT_POST, 1);
                    //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    //     $result = curl_exec($ch);
                    // }
                }
            }


            // flight boarding date track
            $NotificationFlights = $em->getRepository('ClientNotification')->findOneBy(
                array(
                    'client' => $Client->getId(),
                    'notification' => 3
                )
            );
            if($NotificationFlights) {
                $notifif = false;
                foreach ($flightsData as $data) {
                    $data = strtotime($data->format('Y-m-d'));

                    $diff = $data - time();
                    $diff = floor($diff / (60 * 60 * 24));

                    if($diff + 1 >= $Client->getPaymentDays()) {
                        $notifif = true;
                    }
                }
                if($notifif) {
                    $postfields = array(
                        'client' => $Client->getName()
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/futureBoardings');
                    // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/futureBoardings');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);
                }
            }



            // No Contact track
            $NotificationContact = $em->getRepository('ClientNotification')->findOneBy(
                array(
                    'client' => $Client->getId(),
                    'notification' => 4
                )
            );
            if($NotificationContact) {
               $postfields = array(
                    'client' => $Client->getName()
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/difficultContact');
                // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/difficultContact');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);
            }

            // emission socket track
            $postfields = array(
                'client' => $Client->getName().' - '.$cia
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/newOrder');
            // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/newOrder');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

        } catch (\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - PEDIDOS',
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

    public static function getIata($string) {
        $iata = explode('(', $string)[1];
        $iata = substr($iata, 0, 3);
        return $iata;
    }

}

function updateOrders(){
    // // emission socket table update
    $env = getenv('ENV') ? getenv('ENV') : 'production';
    if($env == 'production') {
        $req = new \MilesBench\Request\Request();
        $resp = new \MilesBench\Request\Response();
        $onlineOrder = new \MilesBench\Controller\OnlineOrder();
        $onlineOrder->loadOnlineOrder($req, $resp);
        $postfields = array(
            'orders' => $resp->getDataset()
        );
        $postfields = json_encode($postfields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/updateOrders');
        // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/updateOrders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);
        curl_close ($ch);
        return $result;
    }
}
