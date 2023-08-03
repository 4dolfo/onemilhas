<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;

class OnlineOrder {

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        $pedido = $dados['pedido'];
        $trechos = $dados['trechos'];
        $passageiros = $dados['passageiros'];

        try {

            function validateDate($date, $format = 'Y-m-d H:i:s') {
                $day = 'Y-m-d';
                $d = \DateTime::createFromFormat($format, $date);
                $d2 = \DateTime::createFromFormat($day, $date);
                if($d)
                    return $d->format($format) == $date;
                if($d2)
                    return $d2->format($day) == $date;
                return false;
            }

            $em = Application::getInstance()->getEntityManager();
            $em->getConnection()->beginTransaction();

            $onlineOrder = new \OnlineOrder();
            // $onlineOrder->setAirline($pedido['cia']);
            $onlineOrder->setExternalId($pedido['Id']);
            $onlineOrder->setClientName($pedido['nome_cliente']);
            $onlineOrder->setClientEmail($pedido['email_cliente']);
            $onlineOrder->setMilesUsed($pedido['milhas_total']);
            $onlineOrder->setStatus('PENDENTE');
            $onlineOrder->setComments($pedido['comments']);
            $onlineOrder->setBoardingDate(new \Datetime());
            $onlineOrder->setLandingDate(new \Datetime());
            $onlineOrder->setCreatedAt(new \DateTime());
            if(isset($pedido['marckup_cliente'])) {
                $onlineOrder->setMarckupCliente($pedido['marckup_cliente']);
            }
            if(isset($pedido['hash_pagamento'])) {
                $onlineOrder->setHashCode($pedido['hash_pagamento']);
            }
            if(isset($pedido['nome_cliente'])) {
                $onlineOrder->setClientLogin($pedido['nome_cliente']);
            }
            if(isset($pedido['metodo_pagamento']) && $pedido['metodo_pagamento'] != '') {
                $onlineOrder->setPaymentMethod($pedido['metodo_pagamento']);
            }
            if(isset($pedido['economia']) && $pedido['economia'] != '') {
                $onlineOrder->setEconomy($pedido['economia']);
            }
            if(isset($pedido['metodo_emissao']) && $pedido['metodo_emissao'] != '') {
                $onlineOrder->setEmissionMethod($pedido['metodo_emissao']);
            }

            if(isset($pedido['nomePagador']) && $pedido['nomePagador'] != '') {
                $onlineOrder->setNomePagador($pedido['nomePagador']);
            }
            if(isset($pedido['cpfPagador']) && $pedido['cpfPagador'] != '') {
                $onlineOrder->setCpfPagador($pedido['cpfPagador']);
            }
            if(isset($pedido['enderecoPagador']) && $pedido['enderecoPagador'] != '') {
                $onlineOrder->setEnderecoPagador($pedido['enderecoPagador']);
            }
            if(isset($pedido['numeroEnderecoPagador']) && $pedido['numeroEnderecoPagador'] != '') {
                $onlineOrder->setNumeroEnderecoPagador($pedido['numeroEnderecoPagador']);
            }
            if(isset($pedido['complementoEnderecoPagador']) && $pedido['complementoEnderecoPagador'] != '') {
                $onlineOrder->setComplementoEnderecoPagador($pedido['complementoEnderecoPagador']);
            }
            if(isset($pedido['bairroEnderecoPagador']) && $pedido['bairroEnderecoPagador'] != '') {
                $onlineOrder->setBairroEnderecoPagador($pedido['bairroEnderecoPagador']);
            }
            if(isset($pedido['cidadeEnderecoPagador']) && $pedido['cidadeEnderecoPagador'] != '') {
                $onlineOrder->setCidadeEnderecoPagador($pedido['cidadeEnderecoPagador']);
            }
            if(isset($pedido['estadoEnderecoPagador']) && $pedido['estadoEnderecoPagador'] != '') {
                $onlineOrder->setEstadoEnderecoPagador($pedido['estadoEnderecoPagador']);
            }
            if(isset($pedido['cepEnderecoPagador']) && $pedido['cepEnderecoPagador'] != '') {
                $onlineOrder->setCepEnderecoPagador($pedido['cepEnderecoPagador']);
            }
            if(isset($pedido['telefone_cliente']) && $pedido['telefone_cliente'] != '') {
                $onlineOrder->setClientPhone($pedido['telefone_cliente']);
            }
            if(isset($pedido['descontos']) && $pedido['descontos'] != '') {
                $onlineOrder->setDiscounts($pedido['descontos']);
            }
            if(isset($pedido['taxa_pagamento']) && $pedido['taxa_pagamento'] != '') {
                $onlineOrder->setTaxPayment($pedido['taxa_pagamento']);
            }
            if(isset($pedido['taxa_aprovacao']) && $pedido['taxa_aprovacao'] != '') {
                $onlineOrder->setTaxApproval($pedido['taxa_aprovacao']);
            }
            if(isset($pedido['valor_pagamento']) && $pedido['valor_pagamento'] != '') {
                $onlineOrder->setValuePayment($pedido['valor_pagamento']);
            }
            if(isset($pedido['valor_aprovacao']) && $pedido['valor_aprovacao'] != '') {
                $onlineOrder->setValueApproval($pedido['valor_aprovacao']);
            }
            if(isset($pedido['agencia_id']) && $pedido['agencia_id'] != '') {
                $onlineOrder->setAgenciaId($pedido['agencia_id']);
            }
            $onlineOrder->setOrderPost( json_encode( $request->getRow() ));

            $valueTax = 0;
            $airlines = '';
            foreach($trechos as $trecho){
                foreach($passageiros as $passageiro){
                    if($passageiro['passageiro_bebe'] == "N") {
						if(isset($trecho['taxa'])) {
                            $valueTax = $valueTax + $trecho['taxa'];
                        }
					}
                }
                if($airlines == '') {
                    $airlines .= $trecho['cia'];
                } else {
                    $airlines .= ' | ' . $trecho['cia'];
                }
            }
            $onlineOrder->setAirline($airlines);
            $onlineOrder->setTotalCost($pedido['valor_total'] + $valueTax);

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $company = 0;
            $miles = 0;
            $i = 0;
            $iC = 0;
            $iM = 0;
            $flightsDates = array();
            $miles_total =0;

            foreach($trechos as $trecho){
                $onlineFlight = new \OnlineFlight();
                $onlineFlight->setOrder($onlineOrder);
                $onlineFlight->setAirline($trecho['cia']);
                $onlineFlight->setAirportCodeFrom($trecho['sigla_aeroporto_origem']);
                $onlineFlight->setAirportCodeTo($trecho['sigla_aeroporto_destino']);
                $onlineFlight->setAirportDescriptionFrom($trecho['descricao_aeroporto_origem']);
                $onlineFlight->setAirportDescriptionTo($trecho['descricao_aeroporto_destino']);

                // $data_embarque = substr($trecho['data_embarque'],0,5).substr($trecho['data_embarque'],8,2).substr($trecho['data_embarque'],4,3).substr($trecho['data_embarque'],-9);
                if(validateDate($trecho['data_embarque']) == true) {
                    $onlineFlight->setBoardingDate(new \Datetime($trecho['data_embarque']));

                    //flight date to conference
                    $flightsDates[] = new \Datetime($trecho['data_embarque']);

                    if(!$onlineOrder->getFirstBoardingDate()) {
                        $onlineOrder->setFirstBoardingDate(new \Datetime($trecho['data_embarque']));
                    }

                } else {
                    $onlineFlight->setBoardingDate(new \Datetime('0000-00-00 00:00:00'));
                }

                // $data_pouso = substr($trecho['data_pouso'],0,5).substr($trecho['data_pouso'],8,2).substr($trecho['data_pouso'],4,3).substr($trecho['data_pouso'],-9);
                if(validateDate($trecho['data_pouso']) == true) {
                    $onlineFlight->setLandingDate(new \Datetime($trecho['data_pouso']));
                } else {
                    $onlineFlight->setLandingDate(new \Datetime('0000-00-00 00:00:00'));
                }

                $onlineFlight->setCost($trecho['valor_trecho']);
                $onlineFlight->setMilesUsed($trecho['milhas_trecho']);
                $onlineFlight->setCostPerAdult($trecho['valor_adultos']);
                $onlineFlight->setCostPerChild($trecho['valor_criancas']);
                $onlineFlight->setCostPerNewborn($trecho['valor_bebes']);
                $onlineFlight->setMilesPerAdult($trecho['milhas_adultos']);
                $onlineFlight->setMilesPerChild($trecho['milhas_criancas']);
                $onlineFlight->setMilesPerNewborn($trecho['milhas_bebes']);
                $onlineFlight->setNumberOfAdult($trecho['numero_de_adultos']);
                $onlineFlight->setNumberOfChild($trecho['numero_de_criancas']);
                $onlineFlight->setNumberOfNewborn($trecho['numero_de_bebes']);
                $onlineFlight->setFlight($trecho['numero_voo']);
                if(isset($trecho['miles_money']) && $trecho['miles_money'] != '') {
                    $onlineFlight->setMilesMoney($trecho['miles_money']);
                }

                $onlineFlight->setFlightTime($trecho['duracao_voo']);
                if(isset($trecho['taxa']) && $trecho['taxa'] != '') {
                    $onlineFlight->setTax($trecho['taxa']);
                } else {
                    $onlineFlight->setTax(0);
                }
                if(isset($trecho['classe']) && $trecho['classe'] != '') {
                    if($trecho['classe'] === 'executiva') {
                        $trecho['classe'] = 'Executiva';
                    }
                    $onlineFlight->setClass($trecho['classe']);
                }

                $em->persist($onlineFlight);

                if(isset($trecho['conexao'])) {
                    if (is_array($trecho['conexao'])) {
                        $conexaoFlight = '';
                        foreach($trecho['conexao'] as $conexao){

                            // if( isset($conexao['NumeroVoo']) && isset($conexao['Duracao']) && isset($conexao['Embarque']) && isset($conexao['Desembarque']) && isset($conexao['Origem']) && isset($conexao['Destino']) ) {
                                $OnlineConnection = new \OnlineConnection();
                                $OnlineConnection->setFlight($conexao['NumeroVoo']);
                                $OnlineConnection->setFlightTime($conexao['Duracao']);
                                $OnlineConnection->setBoarding($conexao['Embarque']);
                                $OnlineConnection->setLanding($conexao['Desembarque']);
                                $OnlineConnection->setAirportCodeFrom($conexao['Origem']);
                                $OnlineConnection->setAirportCodeTo($conexao['Destino']);
                                $OnlineConnection->setOnlineFlight($onlineFlight);

                                $em->persist($OnlineConnection);
                                $em->flush($OnlineConnection);
                            // }

                            $conexaoFlight = $conexaoFlight.' '.
                                            $conexao['NumeroVoo'];
                        }

                        $onlineFlight->setConnection($conexaoFlight);
                    } else {
                        $onlineFlight->setConnection($trecho['conexao']);
                    }
                }

                foreach($passageiros as $passageiro){
                    if ($passageiro['passageiro_crianca'] == 'S') {
                        $miles_total += $onlineFlight->getMilesPerChild();
                    } elseif ($passageiro['passageiro_bebe'] == 'S') {
                        $miles_total += $onlineFlight->getMilesPerNewborn();
                    } else {
                        $miles_total += $onlineFlight->getMilesPerAdult();
                    }
                }

                if(isset($trecho['metodo_emissao']) && $trecho['metodo_emissao'] != '') {
                    $onlineFlight->setEmissionMethod($trecho['metodo_emissao']);
                    if($trecho['metodo_emissao'] == "Companhia"){
                        foreach($passageiros as $passageiro){
                            if ($passageiro['passageiro_crianca'] == 'S') {
                                $miles_total -= $onlineFlight->getMilesPerChild();
                            } elseif ($passageiro['passageiro_bebe'] == 'S') {
                                $miles_total -= $onlineFlight->getMilesPerNewborn();
                            } else {
                                $miles_total -= $onlineFlight->getMilesPerAdult();
                            }
                        }

                        $onlineFlight->setMilesUsed("0");
                        $onlineFlight->setMilesPerAdult("0");
                        $onlineFlight->setMilesPerChild("0");
                        $onlineFlight->setMilesPerNewborn("0");
                        $company ++;
                        $iC = $i;
                    } else {
                        $miles ++;
                        $iM = $i;
                    }
                    $i++;
                }

                if(isset($trecho['classe_tarifaria']) && $trecho['classe_tarifaria'] != '') {
                    $onlineFlight->setFareClass($trecho['classe_tarifaria']);
                }

                $em->persist($onlineFlight);
                $em->flush($onlineFlight);
            }

            $onlineOrder->setMilesUsed($miles_total);
            $onlineOrder->setIdcompany($iC);
            $onlineOrder->setIdmiles($iM);
            $onlineOrder->setEmissionmethodcompany($company);
            $onlineOrder->setEmissionmethodmiles($miles);
            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            foreach($passageiros as $passageiro){
                $onlinePax = new \OnlinePax();
                $onlinePax->setOrder($onlineOrder);
                $onlinePax->setPaxName(trim(mb_strtoupper($passageiro['nome'], 'UTF-8')));
                if(isset($passageiro['sobrenome']) && $passageiro['sobrenome'] != '') {
                    $onlinePax->setPaxLastName(trim(mb_strtoupper($passageiro['sobrenome'], 'UTF-8')));
                }
                if(isset($passageiro['agnome']) && $passageiro['agnome'] != '') {
                    $onlinePax->setPaxAgnome(trim(mb_strtoupper($passageiro['agnome'], 'UTF-8')));
                }
                if(isset($passageiro['email']) && $passageiro['email'] != '') {
                    $onlinePax->setEmail($passageiro['email']);
                } else {
                    $onlinePax->setEmail('');
                }

                // $data_nascimento = substr($passageiro['data_nascimento'],0,5).substr($passageiro['data_nascimento'],8,2).substr($passageiro['data_nascimento'],4,3);
                if(validateDate($passageiro['data_nascimento']) == true || validateDate($passageiro['data_nascimento']) == "true") {
                    $onlinePax->setBirthdate(new \Datetime($passageiro['data_nascimento']));
                } else {
                    $onlinePax->setBirthdate(new \Datetime('0000-00-00 00:00:00'));
                }

                if(isset($passageiro['telefone']) && $passageiro['telefone'] != '') {
                    $onlinePax->setPhoneNumber($passageiro['telefone']);
                } else {
                    $onlinePax->setPhoneNumber('');
                }
                $onlinePax->setGender($passageiro['genero']);
                $onlinePax->setIsNewborn($passageiro['passageiro_bebe']);
                $onlinePax->setIsChild($passageiro['passageiro_crianca']);
                $onlinePax->setIdentification($passageiro['identification']);

                $em->persist($onlinePax);
                $em->flush($onlinePax);



                // baggage
                if(isset($passageiro['bagagens'])) {
                    foreach ($passageiro['bagagens'] as $key => $value) {

                        $OnlineBaggage = new \OnlineBaggage();

                        $flight = explode('_', $key);
                        $onlineFlight = $em->getRepository('OnlineFlight')
                            ->findOneBy( array( 'airportCodeFrom' => $flight[0], 'airportCodeTo' => $flight[1], 'order' => $onlineOrder->getId() ) );

                        if(is_array($value)) {
                            $OnlineBaggage->setAmount($value['value']);
                            $OnlineBaggage->setPrice($value['price']);
                        } else {
                            $OnlineBaggage->setAmount($value);
                        }
                        $OnlineBaggage->setOnlineFlight($onlineFlight);
                        $OnlineBaggage->setOnlinePax($onlinePax);

                        $em->persist($OnlineBaggage);
                        $em->flush($OnlineBaggage);
                    }
                }
            }
            $em->getConnection()->commit();

            $dealer = null;
            $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pedido['nome_cliente'], 'partnerType' => 'S'));
            if($Issuer) {

                $BusinesspartnerTags = $em->getRepository('BusinesspartnerTags')->findOneBy(
                    array('businesspartner' => $Issuer->getClient(), 'tag' => 1)
                );
                if($BusinesspartnerTags) {
                    $onlineOrder->setStatus('PRIORIDADE');
                    $em->persist($onlineOrder);
                    $em->flush($onlineOrder);
                }

                // $BusinesspartnerTags = $em->getRepository('BusinesspartnerTags')->findOneBy(
                //     array('businesspartner' => $Issuer->getClient(), 'tag' => 3)
                // );
                // if($BusinesspartnerTags) {
                //     $onlineOrder->setStatus('VOEB2B');
                //     $em->persist($onlineOrder);
                //     $em->flush($onlineOrder);
                // }

                $Client = $em->getRepository('Businesspartner')->find($Issuer->getClient());
                if($Client) {
                    $dealer = $Client->getDealer()->getId();
                    // self::validateOrder($Client, ($pedido['valor_total'] + $valueTax), $flightsDates, $onlineOrder->getAirline(), $onlineOrder->getId());
                }
            }

            ///////////////////////////////////////////////////////////////////////////////////////////
            // PRODUCTION ONLY
            $result = updateOrders($dealer);
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

                        $sql = "select SUM(o.totalCost) as cost FROM OnlineOrder o WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE','ESPERA LIM VLR', 'ANT BLOQ', 'ANT', 'BLOQ', 'SITE_CIA_FORA_AR') and o.clientName in (select b.name from businesspartner b where b.clientId = '".$Client->getId()."' ) ";
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

            $email1 = 'adm@onemilhas.com.br';

            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";
            $postfields = array(
                'content' => $content,
                'partner' => $email1,
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

    public function updateUserSession(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        if(isset($dados['order_id']) && $dados['order_id'] != '') {

            // adding partner
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('acessName' => $dados['name']));
            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $dados['order_id']));
            if($OnlineOrder->getHasBegun() == 'false') {
                if(UserPermission::isEmitter($UserPartner->getId())) {
                    $OnlineOrder->setHasBegun('true');
                }
            }
            $em->persist($OnlineOrder);
            $em->flush($OnlineOrder);

        } else {

            // removing user
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('acessName' => $dados['name']));

            $sql = "select c FROM Cards c WHERE c.userSession like '%".$UserPartner->getAcessName()."%' ";
            $query = $em->createQuery($sql);
            $Cards = $query->getResult();
            foreach ($Cards as $card) {
                $card->setUserSession('');
                $card->setUserSessionDate(null);
                $em->persist($card);
                $em->flush($card);
            }

        }
    }

    public function loadOnlineOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $data = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        if(isset($dados['businesspartner'])) {
            $BusinessPartner = $dados['businesspartner'];
            //self::removeUserSession($BusinessPartner->getId());
        }
        if(isset($BusinessPartner)) {
            $UserPermission = $em->getRepository('UserPermission')->findOneBy( array( 'user' => $BusinessPartner->getId() ) );
            if($UserPermission) {
                if($UserPermission->getCommercial() == 'true') {
                    return self::loadOnlineOrdersCommercial($request, $response);
                }if($UserPermission->getWizarSaleEvent() == 'true'){
                    return self::loadOnlineOrdersVoeLegal($request, $response, $BusinessPartner);
                }
            }
        }

        $where = '';
        $and = ' ';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= $and." ( "
                ." o.id like '%".$dados['searchKeywords']."%' or "
                ." o.clientLogin like '%".$dados['searchKeywords']."%' or "
                ." o.airline like '%".$dados['searchKeywords']."%' or "
                ." o.clientName like '%".$dados['searchKeywords']."%' ) ";
            $and = ' AND ';
        }

        if(isset($data['status']) && !($data['status'] == '')){
            if($data['status'] == 'EMITIDO') {
                $data['status'] = " 'EMITIDO', 'CANCELADO' ";
            } else {
                $data['status'] = "'" . $data['status'] . "'";
            }
            $where = $where.$and. " o.status IN (".$data['status'].") ";
            $and = ' AND ';
        }

        if(isset($data['_issueDateFrom']) && !($data['_issueDateFrom'] == '')){
            $where = $where.$and. " o.createdAt >= '".$data['_issueDateFrom']."' ";
            $and = ' AND ';
        }

        if(isset($data['_issueDateTo']) && !($data['_issueDateTo'] == '')){
            $where = $where.$and. " o.createdAt <= '".$data['_issueDateTo']."' ";
            $and = ' AND ';
        }

        if( !isset($data['status']) && !isset($data['_issueDateFrom']) && !isset($data['_issueDateFrom']) ) {
            $where = $where.$and. " o.status in ('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE','ESPERA LIM VLR', 'ANT BLOQ', 'ANT', 'BLOQ', 'SITE_CIA_FORA_AR') ";
            $and = ' AND ';
        }

	    $QueryBuilder = Application::getInstance()->getQueryBuilder();
        // $sql = "select DISTINCT(s.external_id) as externalId FROM sale s where s.airline_id = '2' and s.partner_sms = 'false' and s.issue_date >= '2017-07-05' ";
        // $stmt = $QueryBuilder->query($sql);

        // $ids = '0';
        // while ($row = $stmt->fetch()) {
        //     if($row['externalId'] != '') {
        //         $ids = $ids.','.$row['externalId'];
        //     }
        // }

        $sql = "select o FROM OnlineOrder o where o.id in (0) or ".$where." order by o.createdAt DESC";

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                ->setMaxResults($dados['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }
        $onlineOrder = $query->getResult();

        $dataset = array();
        $orders = array();
        foreach($onlineOrder as $Order){

            if($Order->getCancelReason() != 'NULL' && $Order->getCancelReason() != NULL) {
                $cancelReason = $Order->getCancelReason();
            } else {
                $cancelReason = '';
            }

            $partnerReservationCode = '';
            $status = $Order->getStatus();
            $sms = false;
            $showSms = false;
            $check_2 = false;

            if($status == 'EMITIDO') {
                // change to new query
                $query = "select s.sale_checked, s.sale_checked_2, s.partner_sms, a.name from sale s inner join airline a on a.id = s.airline_id where s.external_id = ".$Order->getId()." ";
                $stmt = $QueryBuilder->query($query);
                $check = NULL;
                while ($sale = $stmt->fetch()) {
                    if(!isset($check)) {
                        $check = true;
                    }
                    if($sale['sale_checked'] == 'false') {
                        $check = false;
                    }
                    if($sale['name'] == 'GOL') {
                        $showSms = true;
                        if($sale['partner_sms'] == 'true') {
                            $sms = true;
                        }
                    }
                    $check_2 = $sale['sale_checked_2'] == 'true';
                }
                if($check) {
                    $status = 'VERIFICADO';
                }
                $client_name = $Order->getClientName();
            } else {
                $client_name = '';
                if($Order->getAgenciaId()) {

                    $query = "select b.name as name from businesspartner b where id = ".$Order->getAgenciaId()." ";
                    $stmt = $QueryBuilder->query($query);
                    while ($row = $stmt->fetch()) {
                        $client_name = $row['name'];
                    }
                } else {

                    $query = "select b.client_id as name from businesspartner b where name = '".$Order->getClientLogin()."' and partner_type = 'S' ";
                    $stmt = $QueryBuilder->query($query);
                    while ($row2 = $stmt->fetch()) {

                        $query = "select b.name as name from businesspartner b where id = ".$row2['name']." ";
                        $stmt = $QueryBuilder->query($query);
                        while ($row3 = $stmt->fetch()) {
                            $client_name = $row3['name'];
                        }

                    }
                }
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $notificationCode = '';
            if($Order->getNotificationcode()) {
                $notificationCode = $Order->getNotificationcode();
            }

            $firstBoardingDate = '';
            if($Order->getFirstBoardingDate()) {
                $firstBoardingDate = $Order->getFirstBoardingDate()->format('Y-m-d H:i:s');
            }

            $comprovanteTransferencia = array();
            if($Order->getComprovantetransferencia()) {
                $comprovanteTransferencia = json_decode($Order->getComprovantetransferencia(), true);
            }

            $orders[] = array(
                'id' => $Order->getId(),
                'client_name' => $client_name,
                'client_email' => $Order->getClientEmail(),
                'client_login' => $Order-> getClientLogin(),
                'status' => $status,
                'airline' => mb_strtoupper($Order->getAirline(), 'UTF-8'),
                'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'miles_used' => (int)$Order->getMilesUsed(),
                'total_cost' => (float)$Order->getTotalCost(),
                'external_id' => $Order->getExternalId(),
                'comments' => $comments,
                'cancelReason' => $cancelReason,
                'userSession' => $Order->getUserSession(),
                'partnerReservationCode' => $partnerReservationCode,
                'showSms' => $showSms,
                'sms' => $sms,
                'commercialStatus' => ($Order->getCommercialStatus() == 'true'),
                'check_2' => $check_2,
                'notificationCode' => $notificationCode,
                'emissionMethodCompany' => $Order->getEmissionmethodcompany(),
                'emissionMethodMiles' => $Order->getEmissionmethodmiles(),
                'idCompany' => $Order->getIdcompany(),
                'idMiles' => $Order->getIdmiles(),
                'notificationurl' => $Order->getNotificationurl(),
                'firstBoardingDate' => $firstBoardingDate,
                'agencia_id' => $Order->getAgenciaId(),
                'cupom' => (float)$Order->getValorcupom(),
                'nomeCupom' => $Order->getCupom(),
                'indicacao' => $Order->getIndicacao(),
                'creditoUsado' => (float)$Order->getCreditoUsado(),
                'tipocupom' => $Order->getTipocupom(),
                'acrescimo' => (float)$Order->getAcrescimo(),
                'totalParcelas' => $Order->getTotalParcelas(),
                'totalReal' => (float)$Order->getTotalreal(),
                'comprovanteTransferencia' => $comprovanteTransferencia
            );
        }

        $sql = " select COUNT(o.id) as quant FROM OnlineOrder o where o.id in (0) or " . $where;
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'orders' => $orders,
            'total' => $Quant[0]['quant']
        );

        $response->setDataset($dataset);
    }

    public function loadOnlineOrderWaiting(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
	    $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = "select o FROM OnlineOrder o where o.status in ('Aguardando Pagamento', 'RETARIFADA', 'DADOS_PASSAGEIRO_INVALIDO', 'EM_ANALISE') order by o.createdAt DESC";
        $query = $em->createQuery($sql);
        $onlineOrder = $query->getResult();

        $dataset = array();
        foreach($onlineOrder as $Order){
            if($Order->getCancelReason() != 'NULL' && $Order->getCancelReason() != NULL) {
                $cancelReason = $Order->getCancelReason();
            } else {
                $cancelReason = '';
            }

            $status = $Order->getStatus();
            $sms = false;
            $showSms = false;
            $check = NULL;
            $check_2 = false;

            $client_name = '';
            if($Order->getAgenciaId()) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Order->getAgenciaId()));
                if(isset($BusinessPartner)) {
                    $client_name = $BusinessPartner->getName();
                }
            } else {
                $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $Order-> getClientLogin(), 'partnerType' => 'S'));
                $BusinessPartner = null;
                if($Issuer) {
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Issuer->getClient()));
                }
                if(isset($BusinessPartner)) {
                    $client_name = $BusinessPartner->getName();
                }
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $notificationCode = '';
            if($Order->getNotificationcode()) {
                $notificationCode = $Order->getNotificationcode();
            }

            $dataset[] = array(
                'id' => $Order->getId(),
                'client_name' => $client_name,
                'client_email' => $Order->getClientEmail(),
                'client_login' => $Order-> getClientLogin(),
                'status' => $status,
                'airline' => $Order->getAirline(),
                'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'miles_used' => (int)$Order->getMilesUsed(),
                'total_cost' => (float)$Order->getTotalCost(),
                'external_id' => $Order->getExternalId(),
                'comments' => $comments,
                'cancelReason' => $cancelReason,
                'userSession' => $Order->getUserSession(),
                'showSms' => $showSms,
                'sms' => $sms,
                'commercialStatus' => ($Order->getCommercialStatus() == 'true'),
                'check_2' => $check_2,
                'notificationCode' => $notificationCode,
                'notificationurl' => $Order->getNotificationurl(),
                'agencia_id' => $Order->getAgenciaId(),
            );
        }
        $response->setDataset($dataset);
    }

    public function loadOnlineOrdersVoeLegal(Request $request, Response $response, $BusinessPartner) {
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $data = $dados['data'];
        }

        $where = '';
        $and = ' AND ';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= $and." ( "
                ." o.id like '%".$dados['searchKeywords']."%' or "
                ." o.clientLogin like '%".$dados['searchKeywords']."%' or "
                ." o.airline like '%".$dados['searchKeywords']."%' or "
                ." o.clientName like '%".$dados['searchKeywords']."%' ) ";
            $and = ' AND ';
        }

        if(isset($data['status']) && !($data['status'] == '')){
            if($data['status'] == 'EMITIDO') {
                $data['status'] = " 'EMITIDO', 'CANCELADO' ";
            } else {
                $data['status'] = "'" . $data['status'] . "'";
            }
            $where = $where.$and. " o.status IN (".$data['status'].") ";
            $and = ' AND ';
        }

        if(isset($data['_issueDateFrom']) && !($data['_issueDateFrom'] == '')){
            $where = $where.$and. " o.createdAt >= '".$data['_issueDateFrom']."' ";
            $and = ' AND ';
        }

        if(isset($data['_issueDateTo']) && !($data['_issueDateTo'] == '')){
            $where = $where.$and. " o.createdAt <= '".$data['_issueDateTo']."' ";
            $and = ' AND ';
        }

        if( !isset($data['status']) && !isset($data['_issueDateFrom']) && !isset($data['_issueDateFrom']) ) {
            $where = $where.$and. " o.status in ('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE','ESPERA LIM VLR', 'ANT BLOQ', 'ANT', 'BLOQ', 'SITE_CIA_FORA_AR') ";
            $and = ' AND ';
        }

        $sql = "select o FROM OnlineOrder o where o.agenciaId in ( select d.id from CustomersLink c JOIN c.clientdealer d where c.user = ".$BusinessPartner->getId()." ) $where order by o.createdAt DESC";
        $query = $em->createQuery($sql);
        $onlineOrder = $query->getResult();

        $dataset = array();
        $orders = array();

        foreach($onlineOrder as $Order){
            $client_name = $Order->getClientName();

            if($Order->getCancelReason() != 'NULL' && $Order->getCancelReason() != NULL) {
                $cancelReason = $Order->getCancelReason();
            } else {
                $cancelReason = '';
            }

            $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Order->getAgenciaId()));
            if(isset($Client)) {
                $client_name = $Client->getName();
            }

            $tax = array();
            $boardings = array();
            $OnlineFlight = $em->getRepository('OnlineFlight')->findBy( array( 'order' => $Order->getId() ) );
            foreach ($OnlineFlight as $key => $value) {
                $tax[] = (float)$value->getTax();
                $boardings[] = $value->getBoardingDate()->format('Y-m-d H:i:s');
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $firstBoardingDate = '';
            if($Order->getFirstBoardingDate()) {
                $firstBoardingDate = $Order->getFirstBoardingDate()->format('Y-m-d H:i:s');
            }

            $notificationCode = '';
            if($Order->getNotificationcode()) {
                $notificationCode = $Order->getNotificationcode();
            }

            $orders[] = array(
                'id' => $Order->getId(),
                'client_name' => $client_name,
                'client_email' => $Order->getClientEmail(),
                'client_login' => $Order-> getClientLogin(),
                'status' => $Order->getStatus(),
                'airline' => mb_strtoupper($Order->getAirline(), 'UTF-8'),
                'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'miles_used' => (int)$Order->getMilesUsed(),
                'total_cost' => (float)$Order->getTotalCost(),
                'external_id' => $Order->getExternalId(),
                'comments' => $comments,
                'userSession' => $Order->getUserSession(),
                'commercialStatus' => ($Order->getCommercialStatus() == 'true'),
                'tax' => $tax,
                'firstBoardingDate' => $firstBoardingDate,
                'boardings' => $boardings,
                'cancelReason' => $cancelReason,
                'notificationCode' => $notificationCode,
                'notificationurl' => $Order->getNotificationurl(),
                'agencia_id' => $Order->getAgenciaId(),
                'cupom' => (float)$Order->getValorcupom(),
                'nomeCupom' => $Order->getCupom(),
                'indicacao' => $Order->getIndicacao(),
                'creditoUsado' => (float)$Order->getCreditoUsado(),
                'tipocupom' => $Order->getTipocupom()
            );
        }

        $dataset = array(
            'orders' => $orders,
            'total' => 0
        );

        $response->setDataset($dataset);
    }

    public function loadOnlineOrdersCommercial(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $sql = "select o FROM OnlineOrder o where o.status in ('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE', 'ESPERA LIM VLR', 'ANT BLOQ', 'ANT', 'BLOQ', 'SITE_CIA_FORA_AR') order by o.createdAt DESC";
        $query = $em->createQuery($sql);
        $onlineOrder = $query->getResult();

        $dataset = array();
        $orders = array();
        foreach($onlineOrder as $Order){
            $client_name = $Order->getClientName();

            if($Order->getAgenciaId()) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Order->getAgenciaId()));
                if(isset($BusinessPartner)) {
                    $client_name = $BusinessPartner->getName();
                }
            } else {
                $Issuer = $em->getRepository('Businesspartner')->findOneBy( array( 'name' => $client_name ) );
                if( $Issuer ) {
                    $Client = $em->getRepository('Businesspartner')->findOneBy( array( 'id' => $Issuer->getClient() ) );
                    if( $Client ) {
                        $client_name = $Client->getName();
                    }
                }
            }

            $tax = array();
            $boardings = array();
            $OnlineFlight = $em->getRepository('OnlineFlight')->findBy( array( 'order' => $Order->getId() ) );
            foreach ($OnlineFlight as $key => $value) {
                $tax[] = (float)$value->getTax();
                $boardings[] = $value->getBoardingDate()->format('Y-m-d H:i:s');
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $firstBoardingDate = '';
            if($Order->getFirstBoardingDate()) {
                $firstBoardingDate = $Order->getFirstBoardingDate()->format('Y-m-d H:i:s');
            }

            $orders[] = array(
                'id' => $Order->getId(),
                'client_name' => $client_name,
                'client_email' => $Order->getClientEmail(),
                'client_login' => $Order-> getClientLogin(),
                'status' => $Order->getStatus(),
                'airline' => $Order->getAirline(),
                'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'miles_used' => (int)$Order->getMilesUsed(),
                'total_cost' => (float)$Order->getTotalCost(),
                'external_id' => $Order->getExternalId(),
                'comments' => $comments,
                'userSession' => $Order->getUserSession(),
                'commercialStatus' => ($Order->getCommercialStatus() == 'true'),
                'tax' => $tax,
                'firstBoardingDate' => $firstBoardingDate,
                'boardings' => $boardings
            );
        }

        $dataset = array(
            'orders' => $orders,
            'total' => 0
        );

        $response->setDataset($dataset);
    }

    public function loadWaintingOrder(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();
        if(isset($dados['hashId'])) {
            $hashId = $dados['hashId'];
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            self::removeUserSession($UserPartner->getId());
        }

        $sql = "select o FROM OnlineOrder o where o.status = 'EM ESPERA' order by o.createdAt DESC";
        $query = $em->createQuery($sql);
        $onlineOrder = $query->getResult();

        $dataset = array();
        foreach($onlineOrder as $Order){
            if($Order->getCancelReason() != 'NULL' && $Order->getCancelReason() != NULL) {
                $cancelReason = $Order->getCancelReason();
            } else {
                $cancelReason = '';
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $dataset[] = array(
                'id' => $Order->getId(),
                'client_name' => $Order->getClientName(),
                'client_email' => $Order->getClientEmail(),
                'status' => $Order->getStatus(),
                'airline' => $Order->getAirline(),
                'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'boarding_date' => $Order->getBoardingDate()->format('Y-m-d H:i:s'),
                'landing_date' => $Order->getLandingDate()->format('Y-m-d H:i:s'),
                'miles_used' => (int)$Order->getMilesUsed(),
                'total_cost' => (float)$Order->getTotalCost(),
                'external_id' => $Order->getExternalId(),
                'comments' => $comments,
                'userSession' => $Order->getUserSession(),
                'cancelReason' => $cancelReason
            );
        }
        $response->setDataset($dataset);
    }

    public function loadByFilter(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $BusinessPartner = $dados['businesspartner'];
        }
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }

        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $sql = "select o FROM OnlineOrder o ";
        $whereClause = ' WHERE ';
        $and = '';
        $orderBy = ' order by o.createdAt DESC';

        if(isset($dados['status']) && !($dados['status'] == '')){
            $whereClause = $whereClause.$and. " o.status IN ('".$dados['status']."') ";
            $and = ' AND ';
        }

        if(isset($dados['_issueDateFrom']) && !($dados['_issueDateFrom'] == '')){
            $whereClause = $whereClause.$and. " o.createdAt >= '".$dados['_issueDateFrom']."' ";
            $and = ' AND ';
        }

        if(isset($dados['_issueDateTo']) && !($dados['_issueDateTo'] == '')){
            $whereClause = $whereClause.$and. " o.createdAt <= '".$dados['_issueDateTo']."' ";
            $and = ' AND ';
        }

        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause;
        };

        $query = $em->createQuery($sql.$orderBy);
        $onlineOrder = $query->getResult();

        $dataset = array();
        foreach($onlineOrder as $Order){
            if($Order->getCancelReason() != 'NULL' && $Order->getCancelReason() != NULL) {
                $cancelReason = $Order->getCancelReason();
            } else {
                $cancelReason = '';
            }

            $status = $Order->getStatus();
            $sms = false;
            $showSms = false;
            $check_2 = false;

            $query = "select s.sale_checked, s.sale_checked_2, s.partner_sms, a.name from sale s inner join airline a on a.id = s.airline_id where s.external_id = ".$Order->getId()." ";
            $stmt = $QueryBuilder->query($query);
            $check = NULL;
            while ($sale = $stmt->fetch()) {

                if(!isset($check)) {
                    $check = true;
                }

                if($sale['sale_checked'] == 'false') {
                    $check = false;
                }
                if($sale['name'] == 'GOL') {
                    $showSms = true;
                    if($sale['partner_sms'] == 'true') {
                        $sms = true;
                    }
                }
                $check_2 = ($sale['sale_checked_2'] == 'true');
            }
            if($check) {
                $status = 'VERIFICADO';
            }

            $client_name = '';
            $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $Order->getClientLogin(), 'partnerType' => 'S'));
            $ClientName = null;
            if($Issuer) {
                $ClientName = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Issuer->getClient()));
            }
            if(isset($ClientName)) {
                $client_name = $ClientName->getName();
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $dataset[] = array(
                'id' => $Order->getId(),
                'client_name' => $client_name,
                'client_email' => $Order->getClientEmail(),
                'client_login' => $Order-> getClientLogin(),
                'status' => $status,
                'airline' => $Order->getAirline(),
                'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'boarding_date' => $Order->getBoardingDate()->format('Y-m-d H:i:s'),
                'landing_date' => $Order->getLandingDate()->format('Y-m-d H:i:s'),
                'miles_used' => (int)$Order->getMilesUsed(),
                'total_cost' => (float)$Order->getTotalCost(),
                'external_id' => $Order->getExternalId(),
                'comments' => $comments,
                'cancelReason' => $cancelReason,
                'showSms' => $showSms,
                'sms' => $sms,
                'commercialStatus' => ($Order->getUserSession() == 'true'),
                'check_2' => $check_2
            );

        }
        $response->setDataset($dataset);
    }

    public function loadOnlineWaitingByFilter(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }

        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $sql = "select o FROM OnlineOrder o ";
        $whereClause = ' WHERE ';
        $and = '';
        $orderBy = ' order by o.id DESC';

        if(isset($dados['status']) && !($dados['status'] == '')){
            $whereClause = $whereClause.$and. " o.status IN ('".$dados['status']."') ";
            $and = ' AND ';
        }

        if(isset($dados['_issueDateFrom']) && !($dados['_issueDateFrom'] == '')){
            $whereClause = $whereClause.$and. " o.createdAt >= '".$dados['_issueDateFrom']."' ";
            $and = ' AND ';
        }

        if(isset($dados['_issueDateTo']) && !($dados['_issueDateTo'] == '')){
            $whereClause = $whereClause.$and. " o.createdAt <= '".$dados['_issueDateTo']."' ";
            $and = ' AND ';
        }

        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause;
        };

        $query = $em->createQuery($sql.$orderBy);
        $onlineOrder = $query->getResult();

        $dataset = array();
        foreach($onlineOrder as $Order){
            if($Order->getCancelReason() != 'NULL' && $Order->getCancelReason() != NULL) {
                $cancelReason = $Order->getCancelReason();
            } else {
                $cancelReason = '';
            }

            $status = $Order->getStatus();
            $sms = false;
            $showSms = false;
            $check = NULL;
            $check_2 = false;

            $client_name = '';
            $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $Order->getClientLogin(), 'partnerType' => 'S'));
            $ClientName = null;
            if($Issuer) {
                $ClientName = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Issuer->getClient()));
            }
            if(isset($ClientName)) {
                $client_name = $ClientName->getName();
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $dataset[] = array(
                'id' => $Order->getId(),
                'client_name' => $client_name,
                'client_email' => $Order->getClientEmail(),
                'client_login' => $Order-> getClientLogin(),
                'status' => $status,
                'airline' => $Order->getAirline(),
                'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'boarding_date' => $Order->getBoardingDate()->format('Y-m-d H:i:s'),
                'landing_date' => $Order->getLandingDate()->format('Y-m-d H:i:s'),
                'miles_used' => (int)$Order->getMilesUsed(),
                'total_cost' => (float)$Order->getTotalCost(),
                'external_id' => $Order->getExternalId(),
                'comments' => $comments,
                'cancelReason' => $cancelReason,
                'showSms' => $showSms,
                'sms' => $sms,
                'commercialStatus' => ($Order->getUserSession() == 'true'),
                'check_2' => $check_2
            );

        }
        $response->setDataset($dataset);
    }

    public function loadAirlineFlight(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select f FROM OnlineFlight f WHERE f.order = '".$dados['data']['id']."' group by f.airline";
        $query = $em->createQuery($sql);
        $onlineFlight = $query->getResult();

        $dataset = array();
        foreach($onlineFlight as $flight){
                $dataset[] = array(
                    'airline' => $flight->getAirline(),
                    'card' => " Escolher Cartao da Companhia"
                );
        }
        $response->setDataset($dataset);
    }

    public function removeUserSession($partner) {
        $em = Application::getInstance()->getEntityManager();

        $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $partner));
        $sql = "select c FROM Cards c WHERE c.userSession like '%".$UserPartner->getName()."%' ";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();
        foreach ($Cards as $card) {
            $card->setUserSession('');
            $card->setUserSessionDate(null);
            $em->persist($card);
            $em->flush($card);
        }
    }

    public function loadFlights(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $BusinessPartner = null;
        if(isset($dados['businesspartner'])) {
            $BusinessPartner = $dados['businesspartner'];
        }

        $dados = $request->getRow();

        $sql = "select f FROM OnlineFlight f WHERE f.order = '".$dados['data']['id']."' ";
        $query = $em->createQuery($sql);
        $onlineFlight = $query->getResult();

        $sql = "select p FROM OnlinePax p WHERE p.order = '".$dados['data']['id']."' ";
        $query = $em->createQuery($sql);
        $onlinePax = $query->getResult();

        $OnlineOrder = $em->getRepository('OnlineOrder')->find($dados['data']['id']);
        //console.log($dados, $BusinessPartner, $OnlineOrder);
        if($OnlineOrder->getHasBegun() == 'false') {
            if($BusinessPartner) {
                if(UserPermission::isEmitter($BusinessPartner->getId())) {
                    $OnlineOrder->setHasBegun('true');
                    $em->persist($OnlineOrder);
                    $em->flush($OnlineOrder);
                }
            }
        }

        $adt = 0;
        $chd = 0;
        $inf = 0;
        foreach($onlinePax as $pax){
            if($pax->getIsNewborn() == "S") {
                $inf++;
            } else if($pax->getIsChild() == "S") {
                $chd++;
            } else {
                $adt++;
            }
        }

        $dataset = array();
        $pax_filter = 'FILTER_PAX';
        foreach($onlineFlight as $flight){
            $flight_filter = 'FILTER_FLIGHT';
            foreach($onlinePax as $pax){
                if ($pax->getIsChild() == 'S') {
                    $miles_used = $flight->getMilesPerChild();
                    $amount_paid = $flight->getCostPerChild();
                } elseif ($pax->getIsNewborn() == 'S') {
                    $miles_used = $flight->getMilesPerNewborn();
                    $amount_paid = $flight->getCostPerNewborn();
                } else {
                    $miles_used = $flight->getMilesPerAdult();
                    $amount_paid = $flight->getCostPerAdult();
                }

                if ($flight->getConnection() == null) {
                    $connection = ' Direto';
                } else {
                    $connection = $flight->getConnection();
                }

                if ($pax->getGender() == 'M') {
                    $gender = 'Masculino';
                } else {
                    $gender = 'Feminino';
                }

                if($flight->getCards() === null) {
                    $card = " - ";
                    $card_type = null;
                    $cards_id = null;
                    $providerName = null;
                    $provider_phone = null;
                    $phoneNumberAirline = null;
                    $celNumberAirline = null;
                    $provider_adress = null;
                    $card_registrationCode = null;
                } else {
                    $card = $flight->getCards()->getCardNumber();
                    $card_type = $flight->getCards()->getCardType();
                    $cards_id = $flight->getCards()->getId();
                    $card_registrationCode = $flight->getCards()->getBusinesspartner()->getRegistrationCode();
                    $providerName = $flight->getCards()->getBusinesspartner()->getName();
                    $provider_phone = $flight->getCards()->getBusinesspartner()->getPhoneNumber();
                    $phoneNumberAirline = $flight->getCards()->getBusinesspartner()->getPhoneNumberAirline();
                    $celNumberAirline = $flight->getCards()->getBusinesspartner()->getCelNumberAirline();
                    $provider_adress = $flight->getCards()->getBusinesspartner()->getAdress();
                }

                if($flight->getProvider() === null) {
                    $provider = "";
                } else {
                    $provider = $flight->getProvider()->getName();
                }

                $partnerReservationCode = '';
                if($flight->getReservationCode()) {
                    $partnerReservationCode = $flight->getReservationCode();
                }

                $baggage = 0;
                $OnlineBaggage = $em->getRepository('OnlineBaggage')
                            ->findOneBy( array( 'onlineFlight' => $flight, 'onlinePax' => $pax ) );

                if($OnlineBaggage) {
                    $baggage = $OnlineBaggage->getAmount();
                }

                $category = '';
                if($flight->getOrder()->getStatus() == 'EMITIDO' && $flight->getAirline() && 'AZUL') {
                    $category = 'Competitive';
                    $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy(
                        array( 'flightFrom' => $flight->getAirportCodeFrom(), 'flightTo' => $flight->getAirportCodeTo() )
                    );
                    if($FlightPathCategory) {
                        $category = $FlightPathCategory->getFlightCategory()->getName();
                    }
                }

                $emissionMethod = 'Milhas';
                if($flight->getEmissionMethod()) {
                    $emissionMethod = $flight->getEmissionMethod();
                }

                $du_tax = 0;
                $baggage_price = 0;
                if($pax->getIsNewborn() == "N") {
                    if(mb_strtoupper($flight->getAirline(), 'UTF-8') == 'AZUL') {
                        if( $flight->getDuTax() && (float)$flight->getDuTax() != 0 ) {
                            $du_tax += (float)$flight->getDuTax() / ($adt + $chd);
                        } else {
                            $du_tax += 25;
                        }
                    }
                    if($baggage > 0) {
                        $baggage_price += $this->getValueBaggages($flight->getAirline(), $baggage, $dados['data']['client_login']);
                    }
                }

                $cupom = 0;
                if((float)$OnlineOrder->getValorcupom() > 0) {
                    if($OnlineOrder->getTipocupom() != 'porcentagem') {
                        $cupom = (float)$OnlineOrder->getValorcupom() / (count($onlineFlight) + count($onlinePax));
                    } else {
                        $total_value = $amount_paid + (float)$flight->getTax() + $du_tax;
                        if($OnlineOrder->getIndicacao()) {
                            $cupom = ($total_value / 100) * 10;
                        }
                        $cupom += (($total_value - $cupom) / 100) * (float)$OnlineOrder->getValorcupom();
                    }
                }
                if($pax->getIsNewborn() == "N") {
                    if( $flight->getDuTax() && (float)$flight->getDuTax() != 0 ) {
                        $amount_paid += (float)$flight->getDuTax() / ($adt + $chd);
                    }
                }

                $data_passaporte = '';
                if($pax->getDataPassaporte()) {
                    $data_passaporte = $pax->getDataPassaporte()->format('Y-m-d');
                }

                $dataset[] = array(
                    'id' => $flight->getId(),
                    'order_id' => $flight->getOrder()->getId(),
                    'airline' => mb_strtoupper($flight->getAirline(), 'UTF-8'),
                    'flight' => $flight->getFlight(),
                    'connection' => $connection,
                    'boarding_date' => $flight->getBoardingDate()->format('Y-m-d H:i:s'),
                    'landing_date' => $flight->getLandingDate()->format('Y-m-d H:i:s'),
                    'airport_code_from' => $flight->getAirportCodeFrom(),
                    'airport_code_to' => $flight->getAirportCodeTo(),
                    'airport_description_from' => $flight->getAirportDescriptionFrom(),
                    'airport_description_to' => $flight->getAirportDescriptionTo(),
                    'flight_time' => $flight->getFlightTime(),
                    'miles_used' => (int)$miles_used,
                    'original_miles' => (int)$miles_used,
                    'cost' => (float)$amount_paid,
                    'tax' => (float)$flight->getTax(),
                    'pax_id' => $pax->getId(),
                    'pax_name' => $pax->getPaxName(),
                    'paxLastName' => $pax->getPaxLastName(),
                    'paxAgnome' => $pax->getPaxAgnome(),
                    'is_child' => $pax->getIsChild(),
                    'is_diamond' => false, // Workaround (Famosa gambiarra necessária =()
                    'pax_email'=>$pax->getEmail(),
                    'pax_phone'=>$pax->getPhoneNumber(),
                    'is_newborn' => $pax->getIsNewborn(),
                    'birhtdate' => ($pax->getBirthdate()) ? $pax->getBirthdate()->format('Y-m-d') : null,
                    'data_passaporte' => $data_passaporte,
                    'passaporte' => $pax->getPassaporte(),
                    'identification' => $pax->getIdentification(),
                    'miles_per_child' => $flight->getMilesPerChild(),
                    'cost_per_child' => (float)$flight->getCostPerChild(),
                    'miles_per_newborn' => $flight->getMilesPerNewborn(),
                    'cost_per_newborn' => (float)$flight->getCostPerNewborn(),
                    'miles_per_adult' => $flight->getMilesPerAdult(),
                    'cost_per_adult' => (float)$flight->getCostPerAdult(),
                    'gender' => $gender,
                    'flight_filter' => $flight_filter,
                    'pax_filter' => $pax_filter,
                    'card_number' => $card,
                    'card_registrationCode' => $card_registrationCode,
                    'card_type' => $card_type,
                    'cards_id' => $cards_id,
                    'providerName' => $providerName,
                    'provider_phone' => $provider_phone,
                    'phoneNumberAirline' => $phoneNumberAirline,
                    'celNumberAirline' => $celNumberAirline,
                    'provider_adress' => $provider_adress,
                    'discount' => (float)0,
                    'provider' => $provider,
                    'isBreak' => 'Integrado',
                    'partnerReservationCode' => $partnerReservationCode,
                    'seat' => ' --- ',
                    'tax_billet' => (float)$flight->getTax(),
                    'class' => $flight->getClass(),
                    'baggage' => $baggage,
                    'flight_category' => $category,
                    'emissionMethod' => $emissionMethod,
                    'du_tax' => $du_tax,
                    'baggage_price' => $baggage_price,
                    'special_seat' => 0,
                    'cupom' => $cupom,
                    'low_points' => 'false'
                );
                $flight_filter = '_FLIGHT';
            }
            $pax_filter = '_PAX';
        }
        $response->setDataset($dataset);
    }

    public function getValueBaggages($airline, $amount, $client_login) {
        $em = Application::getInstance()->getEntityManager();

        $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $airline));

        $planSale = 3;
        if( in_array($client_login, \MilesBench\PlansBaggages::logins_plan_3) ) {
            $planSale = 1;
        }

        $PlansControlConfig = $em->getRepository('PlansControlConfig')->findOneBy(
            array(
                'type' => 'nacional',
                'salePlans' => $planSale,
                'airline' => $Airline->getId()
            )
        );

        if($PlansControlConfig) {
            $PlansBaggage = $em->getRepository('PlansBaggage')->findOneBy(
                array(
                    'plansControlConfig' => $PlansControlConfig->getId(),
                    'amount' => $amount
                )
            );
            if($PlansBaggage) {
                return $PlansBaggage->getValue();
            }
        }
        return 0;
    }

    public function generateOrder(Request $request, Response $response) {
        $dados = $request->getRow();

        $order = $dados['order_data'];
        $flights = $dados['flight_data'];
        if (isset($dados['wsale_data'])) {
          $dados = $dados['wsale_data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();
        $content = '';

        try {

            if(isset($order['id']) && $order['id'] != '') {
                $Sales = $em->getRepository('Sale')->findBy(array('externalId' => $order['id']));
                if(count($Sales) > 0) {
                    throw new \Exception("VENDA JA REGISTRADA - ORDER ID: " . $order['id']);
                }
            }

            if (isset($dados['partnername']) && $dados['partnername'] != '') {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partnername']));
                if (!$BusinessPartner) {
                    $BusinessPartner = new \Businesspartner();
                    $BusinessPartner->setName($dados['partnername']);
                    $BusinessPartner->setEmail($order['client_email']);
                    $BusinessPartner->setPartnerType('M');

                    $em->persist($BusinessPartner);
                    $em->flush($BusinessPartner);
                }
            }

            if (isset($order['client_name'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_name'], 'partnerType' => 'C'));
                if (!$BusinessPartner) {
                    $BusinessPartner = new \Businesspartner();
                    $BusinessPartner->setName(mb_strtoupper($order['client_name']));
                    $Partner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['partnername']));
                    if(!$Partner) {
                        $BusinessPartner->setEmail(mb_strtoupper($order['client_email']));
                    }
                    $BusinessPartner->setStatus('Aprovado');
                    $BusinessPartner->setPartnerType('C');
                    $BusinessPartner->setBirthdate(new \Datetime());

                    $em->persist($BusinessPartner);
                    $em->flush($BusinessPartner);
                }
            }

            $Client = $BusinessPartner;

            if (isset($order['issuing']) && $order['issuing'] != '') {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                if (!$BusinessPartner) {
                    $BusinessPartner = new \Businesspartner();
                    $BusinessPartner->setName($order['issuing']);
                    $BusinessPartner->setPartnerType('S');
                    $BusinessPartner->setClient($Client->getId());

                    $em->persist($BusinessPartner);
                    $em->flush($BusinessPartner);
                }
            }

            $test = false;
            $Cards = array();
            for ($i = 0; $i <= count($flights)-1; $i++) {
                $flight = $flights[$i];
                if(isset($flight['card_number']) && ($flight['card_number'] != '') && ($flight['card_number'] != ' - ')){
                    $test = false;
                    for ($j = 0; $j <= count($Cards)-1; $j++) {
                        $card = $Cards[$j];
                        if($card['cards_id'] == $flight['cards_id'])
                        {
                            $test = true;
                            $diference = (int)$card['leftOver'] - (int)$flight['miles_used'];
                            $Cards[$j]['leftOver'] = $diference;
                        }
                        if($card['leftOver'] < 0)
                            throw new \Exception("Saldo insuficiente para o cartão: ".$card['card_number']." !");
                    }
                    if($test == false){
                        $sale_cards = $em->getRepository('Cards')->findOneBy(array('id' => $flight['cards_id']));
                        $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' =>  $sale_cards->getId()));

                        $Cards[] = array(
                            'cards_id' => $flight['cards_id'],
                            'leftOver' => (int)$MilesBench->getLeftover() - (int)$flight['miles_used']
                        );
                    }
                }
            }

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $order['id']));

            // new voe legal post api
            $postData = array();
            $postData['order_id'] = $order['id'];
            $postData['trechos'] = array();

            // checking if sale is valid to send sms
            $smsSale = array();

            for ($i = 0; $i <= count($flights)-1; $i++) {
                $flight = $flights[$i];

                if($flight['isBreak'] == "Integrado"){

                    if(isset($flight['paxLastName']) && $flight['paxLastName'] != '') {
                        $flight['pax_name'] .= ' ' . $flight['paxLastName'];
                    }
                    if(isset($flight['paxAgnome']) && $flight['paxAgnome'] != '') {
                        $flight['pax_name'] .= ' ' . $flight['paxAgnome'];
                    }

                    $OnlinePax = $em->getRepository('OnlinePax')->findOneBy(array('id' => $flight['pax_id']));
                    if($OnlinePax) {
                        $OnlinePax->setPaxName(trim(mb_strtoupper($flight['pax_name'], 'UTF-8')));
                        $OnlinePax->setPaxLastName(NULL);
                        $OnlinePax->setPaxAgnome(NULL);
                        $em->persist($OnlinePax);
                        $em->flush($OnlinePax);
                    }

                    if (isset($flight['identification'])) {
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                            array(
                                'name' => trim(mb_strtoupper($flight['pax_name'], 'UTF-8')),
                                'registrationCode' => $flight['identification'],
                                'partnerType' => 'X'
                            )
                        );
                    } else {
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => mb_strtoupper($flight['pax_name'], 'UTF-8'), 'partnerType' => 'X'));
                    }
                    if (!$BusinessPartner) {
                        $BusinessPartner = new \Businesspartner();
                        $BusinessPartner->setName(trim(mb_strtoupper($flight['pax_name'], 'UTF-8')));
                        if (isset($flight['birhtdate'])) {
                            $BusinessPartner->setBirthdate(new \Datetime($flight['birhtdate']));
                        }
                        if (isset($flight['identification'])) {
                            $BusinessPartner->setRegistrationCode($flight['identification']);
                        }
                        $BusinessPartner->setPartnerType('X');
                    }
                    $em->persist($BusinessPartner);
                    $em->flush($BusinessPartner);
                    $sale_pax = $BusinessPartner;

                    $AirportFrom = $em->getRepository('Airport')->findOneBy(array('code' => $flight['airport_code_from']));
                    if (!$AirportFrom) {
                        $AirportFrom = new \Airport();
                        $AirportFrom->setName($flight['airport_description_from']);
                        $AirportFrom->setCode($flight['airport_code_from']);
                        $em->persist($AirportFrom);
                        $em->flush($AirportFrom);
                    }

                    $AirportTo = $em->getRepository('Airport')->findOneBy(array('code' => $flight['airport_code_to']));
                    if (!$AirportTo) {
                        $AirportTo = new \Airport();
                        $AirportTo->setName($flight['airport_description_to']);
                        $AirportTo->setCode($flight['airport_code_to']);
                        $em->persist($AirportTo);
                        $em->flush($AirportTo);
                    }

                    $miles_used = $flight['miles_used'];
                    if ($flight['is_child'] == 'S') {
                        // $miles_used = $flight['miles_per_child'];
                        if(isset($flight['discount']) && $flight['discount'] != ''){
                            $amount_paid = $flight['cost_per_child'] - $flight['discount'];
                        } else {
                            $amount_paid = $flight['cost_per_child'];
                        }
                    } elseif ($flight['is_newborn'] == 'S') {
                        // $miles_used = $flight['miles_per_newborn'];
                        if(isset($flight['discount']) && $flight['discount'] != ''){
                            $amount_paid = $flight['cost_per_newborn'] - $flight['discount'];
                        } else {
                            $amount_paid = $flight['cost_per_newborn'];
                        }
                    } else {
                        // $miles_used = $flight['miles_per_adult'];
                        if(isset($flight['discount']) && $flight['discount'] != ''){
                            $amount_paid = $flight['cost_per_adult'] - $flight['discount'];
                        }else{
                            $amount_paid = $flight['cost_per_adult'];
                        }
                    }

                    $sale_cards = null;
                    $total_cost = 0;
                    $cost_provider = 0;

                    if(isset($flight['card_number']) && ($flight['card_number'] != '') && ($flight['card_number'] != ' - ') || isset($order['card_number'])) {
                        if (isset($flight['card_number'])) {
                            $sale_cards = $em->getRepository('Cards')->findOneBy(array('id' => $flight['cards_id']));
                        } else {
                            $sale_cards = $em->getRepository('Cards')->findOneBy(array('id' => $order['cards_id']));
                        }
                        $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $sale_cards));

                        $sql = "select p FROM Purchase p WHERE p.cards = '".$sale_cards->getId()."' and p.leftover > 0 and p.status='M' order by p.id  ";
                        $query = $em->createQuery($sql);
                        $Purchase = $query->getResult();

                        $left = $miles_used;
                        foreach ($Purchase as $item) {
                            if($left > 0) {

                                $cost_per_thousand = 0;
                                if((float)$item->getCostPerThousandPurchase() != 0) {
                                    $cost_per_thousand = (float)$item->getCostPerThousandPurchase();
                                } else {
                                    $cost_per_thousand = (float)$item->getCostPerThousand();
                                }

                                if($left > (float)$item->getLeftover()) {
                                    $left -= (float)$item->getLeftover();
                                    $total_cost += ((float)$item->getLeftover() / 1000) * $cost_per_thousand;
                                    $cost_provider += ((float)$item->getLeftover() / 1000) * $cost_per_thousand;
                                } else {
                                    $total_cost += ($left / 1000) * $cost_per_thousand;
                                    $cost_provider += ($left / 1000) * $cost_per_thousand;
                                    $left = 0;
                                }
                            }
                        }

                        // $total_cost = $flight['miles_used'] * ($MilesBench->getCostPerThousandPurchase() / 1000);
                        $provider = null;
                    }

                    if(isset($flight['provider']) && $flight['provider'] != '') {
                        $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $flight['provider'], 'partnerType' => 'P'));
                        if(!$provider){
                            $provider = new \Businesspartner();
                            $provider->setName($flight['provider']);
                            $provider->setPartnerType('P');
                            $em->persist($provider);
                            $em->flush($provider);
                        }
                        if($flight['provider'] == "JACK FOR"){
                            if ($flight['is_newborn'] != 'S') {
                                if($flight['airline'] == 'AVIANCA') {
                                    $total_cost = $total_cost + (31 * ($flight['miles_used'] / 1000));
                                } else if($flight['airline'] == 'LATAM') {
                                    $total_cost = $total_cost + (32 * ($flight['miles_used'] / 1000));
                                } else if($flight['airline'] == 'GOL') {
                                    $total_cost = $total_cost + (30 * ($flight['miles_used'] / 1000));
                                } else if($flight['airline'] == 'AZUL') {
                                    $total_cost = $total_cost + (30 * ($flight['miles_used'] / 1000));
                                }
                            } else {
                                $total_cost = 0;
                            }
                        } else if($flight['provider'] == "Rextur Advance" || $flight['provider'] == "CONFIANÇA" || $flight['provider'] == "TAP" ){
                            $total_cost = $amount_paid;
                        }
                    }

                    if (isset($flight['connection']) && $flight['connection'] != '') {
                        $connection = $flight['connection'];
                    } else {
                        $connection = ' Direto';
                    }

                    $Sale = new \Sale();
                    $Sale->setPax($sale_pax);
                    $Sale->setCards($sale_cards);
                    $Sale->setIssueDate(new \Datetime());
                    $Sale->setBoardingDate(new \Datetime($flight['boarding_date']));
                    $Sale->setLandingDate(new \Datetime($flight['landing_date']));
                    $Sale->setMilesUsed($flight['miles_used']);
                    $Sale->setMilesOriginal($flight['original_miles']);
                    $Sale->setPartnerSms('true');
                    if (isset($order['comments'])) {
                        $Sale->setDescription($order['comments']);
                    }
                    if (isset($flight['is_diamond']) && ($flight['is_diamond'] == 'true')) {
                        $Sale->setIsDiamond(true);
                    }
                    if (isset($flight['flightLocator'])) {
                        $Sale->setFlightLocator(mb_strtoupper($flight['flightLocator'], 'UTF-8'));
                    }
                    if(isset($order['early_covered']) && $order['early_covered'] != '') {
                        $Sale->setEarlyCovered($order['early_covered']);
                    }
                    $Sale->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $flight['airline'])));
                    $Sale->setAirportFrom($AirportFrom);
                    $Sale->setAirportTo($AirportTo);

                    if(isset($flight['baggage']) && $flight['baggage'] != '') {
                        $Sale->setBaggage($flight['baggage']);
                    }
                    if(isset($flight['class']) && $flight['class'] != '') {
                        $Sale->setClass($flight['class']);
                    }
                    if($OnlinePax) {
                        $Sale->setOnlinePax($OnlinePax);
                    }

                    if((isset($flight['provider'])) && ($flight['provider'] != '')){
                        $Sale->setSaleByThird('Y');
                        if(isset($flight['safeType']) && $flight['safeType'] != ''){
                            $Sale->setSaleType($flight['safeType']);
                        }
                    }

                    if((isset($flight['du_tax'])) && ($flight['du_tax'] != '')){
                        $Sale->setDuTax($flight['du_tax']);
                    }

                    if (isset($order['issuing'])) {
                         $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['issuing']));
                        if (!$BusinessPartner) {
                            $BusinessPartner = new \Businesspartner();
                            $BusinessPartner->setName($order['issuing']);
                            $BusinessPartner->setPartnerType('S');
                            $BusinessPartner->setClient($Client);

                            $em->persist($BusinessPartner);
                            $em->flush($BusinessPartner);
                        }
                        $Sale->setIssuing($BusinessPartner);
                    }

                    $Sale->setClient($Client);
                    $Sale->setFlight($flight['flight']);
                    $Sale->setFlightHour($flight['flight_time'].'hs '.$connection);
                    $Sale->setExternalId($order['id']);
                    $Sale->setProviderSaleByThird($provider);

                    if(isset($flight['money']) && $flight['money'] != ''){
                        $Sale->setMilesMoney($flight['money']);
                    }

                    if(isset($flight['partnerReservationCode']) && $flight['partnerReservationCode'] != '') {
                        $Sale->setReservationCode($flight['partnerReservationCode']);
                    }

                    if ($flight['is_newborn'] == 'S') {
                        $Sale->setAmountPaid($flight['cost'] - $flight['discount'] - $flight['cupom']);
                        $Sale->setTax(0);
                        $Sale->setTaxBillet(0);

                        if(isset($flight['du_tax'])) {
                            $Sale->setTotalCost($total_cost + $flight['du_tax']);
                        } else {
                            $Sale->setTotalCost($total_cost + 0);
                        }

                        $Sale->setKickback($amount_paid - $total_cost);
                    }else{
                        $Sale->setTax($flight['tax']);
                        $Sale->setTaxBillet($flight['tax_billet']);
                        $Sale->setSpecialSeat($flight['special_seat']);
                        $Sale->setBaggagePrice($flight['baggage_price']);
                        $Sale->setAmountPaid($flight['cost'] + $flight['tax_billet'] + $flight['baggage_price'] + $flight['special_seat'] - $flight['discount'] - $flight['cupom']);

                        if(isset($flight['du_tax'])) {
                            $Sale->setTotalCost($total_cost + $flight['du_tax'] + $flight['tax_billet']);
                        } else {
                            $Sale->setTotalCost($total_cost + $flight['tax_billet']);
                        }

                        $Sale->setKickback($amount_paid - $total_cost - $flight['tax_billet']);
                    }

                    $Sale->setOnlineFlightId($flight['id']);
                    if (isset($flight['ticket_code'])){
                        $Sale->setTicketCode($flight['ticket_code']);
                    }

                    if($OnlineOrder->getPaymentDays() != null) {
                        if( (int)$OnlineOrder->getPaymentDays() > 0 ) {
                            $Sale->setPaymentDays($OnlineOrder->getPaymentDays());
                        }
                    }

                    if($OnlineOrder->getPaymentMethod()) {
                        $Sale->setPaymentMethod($OnlineOrder->getPaymentMethod());
                    }

                    if($OnlineOrder->getStatus() != 'PENDENTE') {
                        $diff = $Sale->getIssueDate()->diff(new \DateTime($order['_startedDate']));
                        $Sale->setProcessingTime($diff->h.':'.$diff->i);
                        $Sale->setProcessingStartDate( new \DateTime($order['_startedDate']) );
                    } else {
                        $diff = $Sale->getIssueDate()->diff($OnlineOrder->getBoardingDate());
                        $Sale->setProcessingTime($diff->h.':'.$diff->i);
                        $Sale->setProcessingStartDate($OnlineOrder->getBoardingDate());
                    }

                    if($OnlineOrder->getValuePayment() != 0 && $OnlineOrder->getValuePayment() != '0.00') {
                        $tax_online_payment = (float)$OnlineOrder->getTotalCost() / $Sale->getAmountPaid();
                        $Sale->setTaxOnlinePayment( $OnlineOrder->getValuePayment() / $tax_online_payment );
                    }

                    if(isset($flight['tax_card']) && $flight['tax_card'] != '' && $flight['tax_card'] != 'OUTRO'){
                        $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('cardNumber' => $flight['tax_card']));
                        if($InternalCards){
                            $Sale->setCardTax($InternalCards);
                            $sale_cards->setCardTax($InternalCards);

                            $em->persist($sale_cards);
                            $em->flush($sale_cards);
                        }
                    }

                    $Sale->setStatus('Emitido');

                    $hash = $request->getRow();
                    if(isset($hash['hashId'])){
                        $hash = $hash['hashId'];
                    }

                    $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                    if(isset($flight['discount']) && $flight['discount'] != ''){
                        $Sale->setDiscount($flight['discount']);
                    }
                    $Sale->setUser($UserPartner);
                    $em->persist($Sale);
                    $em->flush($Sale);

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    if(isset($flight['userEmail']) && $flight['userEmail'] != ''){
                        $UserPermission = $em->getRepository('Businesspartner')->findOneBy(array('email' => $flight['userEmail']));
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()." >>> Venda Recebeu permissoes de alteracao de dados de: ".$UserPermission->getName());
                    } else {
                        $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()." - Usuario:".$UserPartner->getName()."");
                    }
                    $SystemLog->setLogType('SALE');
                    $SystemLog->setBusinesspartner($UserPartner);

                    $em->persist($SystemLog);
                    $em->flush($SystemLog);

                    if(isset($flight['card_number']) && ($flight['card_number'] != '') && ($flight['card_number'] != ' - ')) {
                        $removedMiles = Miles::removeMiles($em, $sale_cards->getId(), $miles_used, $Sale->getId());
                    }

                    $em->persist($Sale);
                    $em->flush($Sale);

                    $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('id' => $Sale->getCardTax()));

                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Sale->getClient()->getId()));
                    $daysToPay = new \Datetime();

                    if(isset($flight['safeType']) && $flight['safeType'] == "Cartao") {
                        $Provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['SaleProvider']));
                        $Billsreceive = new \Billsreceive();
                        $Billsreceive->setStatus('A');
                        $Billsreceive->setClient($Provider);
                        $Billsreceive->setDescription('Passageiro '.$flight['pax_name'].' - Localizador '.$flight['flightLocator']);
                        $Billsreceive->setOriginalValue($Sale->getAmountPaid() - $Sale->getTotalCost());
                        $Billsreceive->setActualValue($Sale->getAmountPaid() - $Sale->getTotalCost());
                        $Billsreceive->setTax(0);
                        $Billsreceive->setDiscount(0);
                        $Billsreceive->setAccountType('Venda Bilhete');
                        $Billsreceive->setReceiveType('Boleto Bancario');
                        $Billsreceive->setDueDate($daysToPay);
                        $em->persist($Billsreceive);
                        $em->flush($Billsreceive);

                        $SaleBillsreceive = new \SaleBillsreceive();
                        $SaleBillsreceive->setBillsreceive($Billsreceive);
                        $SaleBillsreceive->setSale($Sale);
                        $em->persist($SaleBillsreceive);
                        $em->flush($SaleBillsreceive);

                    } else {

                        if($flight['provider'] == "Rextur Advance" || $flight['provider'] == "CONFIANÇA" || $flight['provider'] == "TAP") {

                            $Billsreceive = new \Billsreceive();
                            $Billsreceive->setStatus('A');
                            $Billsreceive->setClient($Sale->getClient());
                            $Billsreceive->setDescription('Passageiro '.$flight['pax_name'].' - Localizador '.$flight['flightLocator']);
                            $Billsreceive->setOriginalValue($Sale->getAmountPaid());
                            $Billsreceive->setActualValue($Sale->getAmountPaid());
                            $Billsreceive->setTax(0);
                            $Billsreceive->setDiscount(0);
                            $Billsreceive->setAccountType('Venda Bilhete');
                            $Billsreceive->setReceiveType('Boleto Bancario');
                            $Billsreceive->setDueDate($daysToPay);
                            $em->persist($Billsreceive);
                            $em->flush($Billsreceive);

                            $SaleBillsreceive = new \SaleBillsreceive();
                            $SaleBillsreceive->setBillsreceive($Billsreceive);
                            $SaleBillsreceive->setSale($Sale);
                            $em->persist($SaleBillsreceive);
                            $em->flush($SaleBillsreceive);

                        } else {
                            $Billsreceive = new \Billsreceive();
                            $Billsreceive->setStatus('A');
                            $Billsreceive->setClient($Sale->getClient());
                            $Billsreceive->setDescription('Passageiro '.$flight['pax_name'].' - Localizador '.$flight['flightLocator']);
                            $Billsreceive->setOriginalValue($Sale->getAmountPaid());
                            $Billsreceive->setActualValue($Sale->getAmountPaid());
                            $Billsreceive->setTax(0);
                            $Billsreceive->setDiscount(0);
                            $Billsreceive->setAccountType('Venda Bilhete');
                            $Billsreceive->setReceiveType('Boleto Bancario');
                            $Billsreceive->setDueDate($daysToPay);
                            $em->persist($Billsreceive);
                            $em->flush($Billsreceive);

                            $SaleBillsreceive = new \SaleBillsreceive();
                            $SaleBillsreceive->setBillsreceive($Billsreceive);
                            $SaleBillsreceive->setSale($Sale);
                            $em->persist($SaleBillsreceive);
                            $em->flush($SaleBillsreceive);
                        }
                    }

                    $credit_card = 0;
                    if ($Sale->getSaleByThird() == 'Y') {
                        if(!isset($flight['safeType']) || ( isset($flight['safeType']) && $flight['safeType'] != "Cartao" )) {
                            if($flight['provider'] == 'Rextur Advance' || $flight['provider'] == "CONFIANÇA" || $flight['provider'] == "TAP") {
                                if(isset($flight['safeType']) && $flight['safeType'] == 'Faturado') {
                                    $Billspay = new \Billspay();
                                    $Billspay->setStatus('A');
                                    $Billspay->setDescription('Venda por Terceiros - '.'Passageiro '.$flight['pax_name'].' - Localizador '.$flight['flightLocator']);
                                    $Billspay->setOriginalValue($total_cost);
                                    $Billspay->setActualValue($total_cost);
                                    $Billspay->setTax(0);
                                    $Billspay->setDiscount(0);
                                    $Billspay->setAccountType('Venda por Parceiro');
                                    $Billspay->setPaymentType('Cartao Credito');
                                    $Billspay->setDueDate($Sale->getIssueDate());
                                    $Billspay->setProvider($provider);
                                    $Billspay->setPaymentType('Cartao Credito');
                                    $em->persist($Billspay);
                                    $em->flush($Billspay);

                                    $SaleBillspay = new \SaleBillspay();
                                    $SaleBillspay->setBillspay($Billspay);
                                    $SaleBillspay->setSale($Sale);
                                    $em->persist($SaleBillspay);
                                    $em->flush($SaleBillspay);
                                }
                            } else {
                                if ($flight['is_newborn'] != 'S') {
                                    $Billspay = new \Billspay();
                                    $Billspay->setStatus('A');
                                    $Billspay->setDescription('Venda por Terceiros - '.'Passageiro '.$flight['pax_name'].' - Localizador '.$flight['flightLocator']);
                                    if($flight['provider'] == 'JACK FOR') {
                                        if(isset($flight['du_tax'])){
                                            $Billspay->setOriginalValue($flight['du_tax'] + $flight['tax_billet'] + (32 * ($flight['miles_used'] / 1000)));
                                            $Billspay->setActualValue($flight['du_tax'] + $flight['tax_billet'] + (32 * ($flight['miles_used'] / 1000)));
                                        } else {
                                            $Billspay->setOriginalValue($flight['tax_billet'] + (32 * ($flight['miles_used'] / 1000)));
                                            $Billspay->setActualValue($flight['tax_billet'] + (32 * ($flight['miles_used'] / 1000)));
                                        }
                                    } else {
                                        if(isset($flight['du_tax'])){
                                            $Billspay->setOriginalValue($flight['du_tax'] + $flight['tax_billet']);
                                            $Billspay->setActualValue($flight['du_tax'] + $flight['tax_billet']);
                                        } else {
                                            $Billspay->setOriginalValue($flight['tax_billet']);
                                            $Billspay->setActualValue($flight['tax_billet']);
                                        }
                                    }
                                    $Billspay->setTax(0);
                                    $Billspay->setDiscount(0);
                                    $Billspay->setAccountType('Venda por Parceiro');
                                    $Billspay->setPaymentType('Cartao Credito');
                                    $Billspay->setDueDate($Sale->getIssueDate());
                                    $Billspay->setProvider($provider);
                                    $Billspay->setPaymentType('Cartao Credito');
                                    $em->persist($Billspay);
                                    $em->flush($Billspay);

                                    $SaleBillspay = new \SaleBillspay();
                                    $SaleBillspay->setBillspay($Billspay);
                                    $SaleBillspay->setSale($Sale);
                                    $em->persist($SaleBillspay);
                                    $em->flush($SaleBillspay);
                                }
                            }
                        }
                    }

                    if ($Sale->getSaleByThird() != 'Y') {

                        if(isset($flight['du_tax'])) {
                            $credit_card += $flight['du_tax'];
                            if($InternalCards){
                                $InternalCards->setCardUsed($InternalCards->getCardUsed() + $flight['du_tax']);
                                $em->persist($InternalCards);
                            }
                        }

                        if(isset($flight['money'])) {
                            $credit_card += $flight['money'];
                            if($InternalCards){
                                $InternalCards->setCardUsed($InternalCards->getCardUsed() + $flight['money']);
                                $em->persist($InternalCards);
                            }
                        }

                        if(isset($flight['tax'])) {
                            $credit_card += $flight['tax'];
                            if($InternalCards){
                                $InternalCards->setCardUsed($InternalCards->getCardUsed() + $flight['tax']);
                                $em->persist($InternalCards);
                            }
                        }

                        $Billspay = new \Billspay();
                        $Billspay->setStatus('A');
                        $Billspay->setDescription('Passageiro '.$flight['pax_name'].' - Localizador '.$flight['flightLocator']);
                        $Billspay->setOriginalValue($credit_card);
                        $Billspay->setActualValue($credit_card);
                        $Billspay->setTax(0);
                        $Billspay->setDiscount(0);
                        $Billspay->setAccountType('Taxas');
                        $Billspay->setPaymentType('Cartao Credito');
                        $Billspay->setDueDate($Sale->getIssueDate());
                        $Billspay->setPaymentType('Cartao Credito');
                        $em->persist($Billspay);
                        $em->flush($Billspay);

                        $SaleBillspay = new \SaleBillspay();
                        $SaleBillspay->setBillspay($Billspay);
                        $SaleBillspay->setSale($Sale);
                        $em->persist($SaleBillspay);
                        $em->flush($SaleBillspay);
                    }

                    if($InternalCards){

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Uso registrado - Cartao de credito n:".$InternalCards->getCardNumber()." - Venda n:".$Sale->getId()." - Valor R$:".$credit_card."");
                        $SystemLog->setLogType('CREDITCARD');
                        $SystemLog->setBusinesspartner($UserPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                        $em->persist($InternalCards);
                        $em->flush($InternalCards);
                    }

                    if($Sale->getClient()->getId() == 5105) {
                        $content = $content."<br><br>Compra OAB realizada:".
                                    "<br><br>Nome: ".$flight['pax_name'].
                                    "<br>Localizador: ".$flight['flightLocator'].
                                    "<br>Cia: ".$flight['airline'].
                                    "<br>Valor: ".($amount_paid + $flight['tax_billet']).
                                    "<br>Data de Embarque: ".date_format($Sale->getBoardingDate(), 'd-m-Y').
                                    "<br>Trecho:".
                                    "<br> | De: ".$flight['airport_description_from']." (".$flight['airport_description_to'].")".
                                    "<br> | Para: ".$flight['airport_description_to']." (".$flight['airport_code_to'].")";
                    }

                    $t = $Sale->getClient()->getId();
                    if($Sale->getClient()->getId() == 50907 || $Sale->getClient()->getId() == 250907 || $Sale->getClient()->getId() == 16262 ) {
                        $postData['trechos'][] = array(
                            'flightCode' => $flight['flight'],
                            'fromCode' => $flight['airport_code_from'],
                            'toCode' => $flight['airport_code_to'],
                            'boardingDate' => $flight['boarding_date'],
                            'paxIdentification' => $sale_pax->getRegistrationCode(),
                            'locator' => mb_strtoupper($flight['flightLocator'], 'UTF-8')
                        );
                    }

                    if($Sale->getAirline()->getName() == 'GOL') {
                        if($Sale->getCards()) {

                            if(isset($smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()])) {

                                if(!isset($smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()]['loc'][$Sale->getFlightLocator()])) {
                                    if ($flight['is_newborn'] == 'S') {
                                        $smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()]['loc'][$Sale->getFlightLocator()] = array(
                                            'brand' => $InternalCards->getCardType(),
                                            'holder_name' => $InternalCards->getShowName(),
                                            'tax' => 0,
                                            'points' => (int)$Sale->getMilesUsed()
                                        );
                                    } else {
                                        $smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()]['loc'][$Sale->getFlightLocator()] = array(
                                            'brand' => $InternalCards->getCardType(),
                                            'holder_name' => $InternalCards->getShowName(),
                                            'tax' => $credit_card,
                                            'points' => (int)$Sale->getMilesUsed()
                                        );
                                    }
                                } else {
                                    if ($flight['is_newborn'] != 'S') {
                                        $smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()]['loc'][$Sale->getFlightLocator()]['tax'] += $credit_card;
                                        $smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()]['loc'][$Sale->getFlightLocator()]['points'] += (int)$Sale->getMilesUsed();
                                    }
                                }
                            } else {
                                if ($flight['is_newborn'] == 'S') {
                                    $smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()] = array(
                                        'loc' => array(
                                            $Sale->getFlightLocator() => array(
                                                'brand' => $InternalCards->getCardType(),
                                                'holder_name' => $InternalCards->getShowName(),
                                                'tax' => 0,
                                                'points' => (int)$Sale->getMilesUsed()
                                            )
                                        )
                                    );
                                } else {
                                    $smsSale[$Sale->getCards()->getBusinesspartner()->getRegistrationCode()] = array(
                                        'loc' => array(
                                            $Sale->getFlightLocator() => array(
                                                'brand' => $InternalCards->getCardType(),
                                                'holder_name' => $InternalCards->getShowName(),
                                                'tax' => $credit_card,
                                                'points' => (int)$Sale->getMilesUsed()
                                            )
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
            }

            if($OnlineOrder->getPaymentDays() != null) {

                $locs = "";
                foreach ($flights as $key => $value) {
                    $locs .= $value["flightLocator"] . " ";
                }

                if( (int)$OnlineOrder->getPaymentDays() > 0 ) {
                    $email = new Mail();
                    $req = new \MilesBench\Request\Request();
                    $resp = new \MilesBench\Request\Response();
                    $email1 = 'financeiro@onemilhas.com.br';
                    $email2 = 'adm@onemilhas.com.br';
                    $req->setRow(
                        array(
                            'data' => array(
                                'subject' => '[ONE MILHAS] liberação faturado',
                                'emailContent' => "Cliente: ".$order['client_name']."<br>".
                                    "Prazo: ".(int)$OnlineOrder->getPaymentDays()." dias".
                                    "Localizadores: ".$locs."<br><br><br>Atenciosamente,<br>SRM-IT",
                                'emailpartner' => array($email1, $email2),
                                'type' => 'EMISSAO'
                            ),
                            'hashId' => $hash
                        ));
                    $email->SendMail($req, $resp);
                }
            }

            if(count($smsSale) > 0) {
                foreach ($smsSale as $key => $value) {

                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('registrationCode' => $key));

                    foreach ($value["loc"] as $keyLoc => $valueLoc) {
                        $pax = "Passageiro: ";
                        $text = "Ola " . explode(" ", $UserPartner->getName())[0] . ". Uso do smiles, " . ((int)$valueLoc["points"] / 1000). " mil pontos " . "\n";

                        $sql = "select s FROM Sale s where s.flightLocator = '" . $keyLoc . "' group by s.flightLocator ";
                        $query = $em->createQuery($sql);
                        $Sale = $query->getResult();

                        $turn = 'Ida: ';
                        $trecho = '';
                        foreach ($Sale as $saleUnic) {
                            $text .= $turn . $saleUnic->getBoardingDate()->format("d/m/Y") . "\n";
                            if($turn == 'Ida: ') {
                                $trecho .= $saleUnic->getAirportFrom()->getCode() . ' / ' . $saleUnic->getAirportTo()->getCode();
                            } else {
                                $trecho .= ' / ' . $saleUnic->getAirportTo()->getCode();
                            }
                            $turn = 'Volta: ';
                        }

                        $OnlinePax = $em->getRepository("OnlinePax")->findBy(array("order" => $order['id']));
                        foreach ($OnlinePax as $onlineP) {
                            $pax .= $onlineP->getPaxName();
                            if($onlineP->getPaxLastName()) {
                                $pax .= ' ' . $onlineP->getPaxLastName();
                            }
                            if($onlineP->getPaxAgnome()) {
                                $pax .= ' ' . $onlineP->getPaxAgnome();
                            }
                            $pax .= "\n";
                        }

                        $brand = $valueLoc["brand"];
                        $holder = $valueLoc["holder_name"];
                        $tax = $valueLoc["tax"];

                        $text .= "TRECHO: " . $trecho . "\n";
                        $text .= $pax;

                        $text .= "Loc: " . key($value["loc"]) . "\n";
                        $text .= $brand . "\n";
                        $text .= $holder . "\n";
                        $text .= "TAXA: " . $tax . "\n";

                        // gettting client phone number
                        $celNumberAirline = $UserPartner->getCelNumberAirline();
                        if(!$UserPartner->getCelNumberAirline()) {

                            $celNumberAirline = $UserPartner->getPhoneNumber();
                            if(!$UserPartner->getPhoneNumber()) {

                                $celNumberAirline = $UserPartner->getPhoneNumber2();
                                if(!$UserPartner->getPhoneNumber2()) {

                                    $celNumberAirline = $UserPartner->getPhoneNumberAirline();
                                    if(!$UserPartner->getPhoneNumberAirline()) {

                                        $celNumberAirline = \MilesBench\Util::emission_number;
                                    }
                                }
                            }
                        }

                        $sendSms = Sms::send($celNumberAirline, $text, 'One Milhas');
                    }
                }
            }

            $onlineOrder = ($em->getRepository('OnlineOrder')->find($order['id']));
            if($onlineOrder->getStatus() == 'ENVIADO'){
                $onlineOrder->setStatus('ENVIADO');
            }else{
                $onlineOrder->setStatus('EMITIDO');
            }
            $onlineOrder->setClientName($order['client_name']);
            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $em->getConnection()->commit();

            $result = updateOrders();

            Miles::checkMilesAfterUse($onlineOrder->getId());

            if($onlineOrder->getOriginalSystem() == 'oktoplus') {
                $jsonToPost = array(
                    'status' => 'emitida'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Controller\Oktoplus\OktoplusConstants::notification_url . $onlineOrder->getExternalId() .  '/status');
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json",
                    'Authorization: '. \MilesBench\Controller\Oktoplus\OktoplusConstants::token,
                    'hashId: '. \MilesBench\Controller\Oktoplus\OktoplusConstants::token));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
                $result = curl_exec($ch);
                $content = "<br>".$result."<br><br>SRM-IT";

                $email1 = 'adm@onemilhas.com.br';
                $email2 = 'adm@onemilhas.com.br';
                $postfields = array(
                    'content' => $content,
                    'partner' => $email2,
                    'from' => $email1,
                    'subject' => 'PEDIDOS - OKTOPLUS - EMISSAO',
                    'type' => ''
                );
                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    $postfields['subject'] = '[HOMOLOGAÇÃO] PEDIDOS - [HOMOLOGAÇÃO] - OKTOPLUS - [HOMOLOGAÇÃO] - EMISSAO';
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);
            }

            if($onlineOrder->getNotificationurl()) {
                if($onlineOrder->getNotificationurl()) {
                    $returnBillets = $this->sendBillets($onlineOrder->getId());
                }

                $Sale = $em->getRepository('Sale')->findOneBy(array('externalId' => $onlineOrder->getId()));

                if(strpos($onlineOrder->getNotificationurl(), 'skymilhas') !== false) {
                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Entrando na geração da nota - Parte 1 online order id: " . $onlineOrder->getId() . " - Nome pagador: " . $onlineOrder->getNomePagador() . "Sale id: " . $Sale->getId());
                    $SystemLog->setLogType('NFE');

                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                }

                if($Sale->getPurchase() && $onlineOrder->getNomePagador() && $onlineOrder->getNomePagador() != 'null' && strpos($onlineOrder->getNotificationurl(), 'skymilhas') !== false) {
                    $Purchase = $Sale->getPurchase();
                    $Cards = $Purchase->getCards();
                    $Provider = $Cards->getBusinesspartner();

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Entrando na geração da nota - Parte 2 online order id: " . $onlineOrder->getId() . " - Purchase: " . $Purchase->getId() . " - Cards:" . $Cards->getId() . " - Provider: " . $Provider->getId());
                    $SystemLog->setLogType('NFE');

                    $em->persist($SystemLog);
                    $em->flush($SystemLog);

                    $cost_provider = (float)$onlineOrder->getTotalCost() * 0.9;

                    $ENotas = new \MilesBench\Controller\NFSe\ENotas();
                    $returnENotas = $ENotas->emitArray(array(
                        'name' => $onlineOrder->getNomePagador(),
                        'cpf' => $onlineOrder->getCpfPagador(),
                        'address' => $onlineOrder->getEnderecoPagador(),
                        'number_address' => $onlineOrder->getNumeroEnderecoPagador(),
                        'complement_address' => $onlineOrder->getComplementoEnderecoPagador(),
                        'district_address' => $onlineOrder->getBairroEnderecoPagador(),
                        'city' => $onlineOrder->getCidadeEnderecoPagador(),
                        'state' => $onlineOrder->getEstadoEnderecoPagador(),
                        'zip_code' => $onlineOrder->getCepEnderecoPagador(),
                        'email' => $onlineOrder->getClientEmail(),
                        'value' => $onlineOrder->getTotalCost(),
                        'deductions' => $cost_provider,
                        'descricao' => 'PRESTAÇÃO DE SERVIÇOS REFERENTE A COMISSÃO DE INTERMEDIAÇÃO DE PASSAGENS AÉREAS. REPASSE DE R$ '. number_format($cost_provider, 2, ',', '.') .' PARA FORNECEDOR '. $Provider->getName() .' CPF:'. $Provider->getRegistrationCode() .' VALOR APROXIMADO DOS TRIBUTOS FEDERAIS E MUNICIPAIS CONFORME AS ALÍQUOTAS 13,45% E 2,01% DA TABELA IBPT 2018.1.'
                    ));

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Entrando na geração da nota - Parte 3 - Retornou do ENOTAS da seguinte forma: " . json_encode($returnENotas));
                    $SystemLog->setLogType('NFE');

                    $em->persist($SystemLog);
                    $em->flush($SystemLog);

                    if($returnENotas['type'] == 'S') {

                        $em = Application::getInstance()->getEntityManager();
                        $onlineOrder = $em->getRepository('OnlineOrder')->find($onlineOrder->getId());
                        $onlineOrder->setNfeid($returnENotas['id']);
                        $em->persist($onlineOrder);
                        $em->flush($onlineOrder);
                    }
                }
            }

            //self::removeUserSession($UserPartner->getId());

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            $content = "<br>".$e->getMessage()."<br><br>SRM-IT";

            $email1 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'partner' => $email1,
                'subject' => 'ERROR - GENERATE ORDER <br />' . var_dump($order),
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

    public function cancelOrder(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        if(isset($request->getRow()['hashId'])) {
			$hashId = $request->getRow()['hashId'];
        }
        if(isset($request->getRow()['businesspartner'])) {
            $Businesspartner = $request->getRow()['businesspartner'];
        }

        $em = Application::getInstance()->getEntityManager();

        $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        try {
            $onlineOrder->setStatus('CANCELADO');
            if(isset($dados['cancelReason']) && $dados['cancelReason'] != ''){
                $onlineOrder->setCancelReason($dados['cancelReason']);
            }
            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("OP Cancelada - Pedido: " . $dados['id']);
            $SystemLog->setLogType('CANCEL-ORDER');
            $SystemLog->setBusinesspartner($Businesspartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            if($onlineOrder->getOriginalSystem() == 'oktoplus') {
                $jsonToPost = array(
                    'status' => 'cancelada'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Controller\Oktoplus\OktoplusConstants::notification_url . $onlineOrder->getExternalId() .  '/status');
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json",
                    'Authorization: '.\MilesBench\Controller\Oktoplus\OktoplusConstants::token,
                    'hashId: '.\MilesBench\Controller\Oktoplus\OktoplusConstants::token));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
                $result = curl_exec($ch);
                $content = "<br>".$result."<br><br>SRM-IT";
                
                $email1 = 'adm@onemilhas.com.br';
                $email2 = 'adm@onemilhas.com.br';

                $postfields = array(
                    'content' => $content,
                    'partner' => $email2,
                    'from' => $email1,
                    'subject' => 'PEDIDOS - OKTOPLUS - CANCELAMENTO',
                    'type' => ''
                );
                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    $postfields['subject'] = '[HOMOLOGAÇÃO] PEDIDOS - [HOMOLOGAÇÃO] - OKTOPLUS - [HOMOLOGAÇÃO] - CANCELAMENTO';
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);
            }

            if($onlineOrder->getNotificationurl()) {
                $jsonToPost = array(
                    'notificationCode' => $onlineOrder->getNotificationcode(),
                    'status' => '2',
                    'hashId' => "fd0ab7097fb7119900febac7e3875218"
                );

                $OnlineFlight = $em->getRepository('OnlineFlight')->findBy(array('order' => $onlineOrder->getId()));
                foreach ($OnlineFlight as $key => $value) {
                    if($key === 0 || $key === '0') {
                        $jsonToPost['StatusIda'] = '2';
                    } else {
                        $jsonToPost['StatusVolta'] = '2';
                    }
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $onlineOrder->getNotificationurl());
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json"));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
                $result = curl_exec($ch);

                $content = "<br>".json_encode($jsonToPost)."<br><br>SRM-IT";
                $email1 = 'emissao@onemilhas.com.br';
                $email2 = 'adm@onemilhas.com.br';
                $postfields = array(
                    'content' => $content,
                    'from' => $email1,
                    'partner' => $email2,
                    'subject' => 'PEDIDOS - INCODDE - CANCELAMENTO',
                    'type' => ''
                );

                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    $postfields['subject'] = '[HOMOLOGAÇÃO] PEDIDOS - [HOMOLOGAÇÃO] - INCODDE - [HOMOLOGAÇÃO] - CANCELAMENTO';
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);

                $OnlineNotificationStatus = new \OnlineNotificationStatus();
                $OnlineNotificationStatus->setStatus('2');
                $OnlineNotificationStatus->setUser($Businesspartner->getName());
                $OnlineNotificationStatus->setIssueDate(new \Datetime());
                $OnlineNotificationStatus->setOrder($onlineOrder);
                $em->persist($OnlineNotificationStatus);
                $em->flush($OnlineNotificationStatus);
            }

            $result = updateOrders();

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

    public function setStatusOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['status'])) {
            $status = $dados['status'];
        }
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        try {

            $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

            if($onlineOrder->getStatus() == 'ESPERA' || $onlineOrder->getStatus() == 'PENDENTE' || $onlineOrder->getStatus() == 'ESPERA VLR' || $onlineOrder->getStatus() == 'ESPERA PGTO' || $onlineOrder->getStatus() == 'ESPERA LIM' || $onlineOrder->getStatus() == 'PRIORIDADE' || $onlineOrder->getStatus() == 'ESPERA LIM VLR' || $onlineOrder->getStatus() == 'ANT' || $onlineOrder->getStatus() == 'BLOQ' || $onlineOrder->getStatus() == 'SITE_CIA_FORA_AR' || $onlineOrder->getStatus() == 'ANT BLOQ') {
                if($status == 'EMISSAOIDEAL') {

                    $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
                    $postfields = json_decode($onlineOrder->getOrderPost(), true);
                    $postfields['pedido']['notificationurl'] = 'http://34.207.228.97/' + $DirServer + '/backend/application/index.php?rota=/incodde/updateMMS';
                    $postfields['pedido']['notificationtype'] = '3';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://34.207.228.97/'.$DirServer.'/backend/application/index.php?rota=/incodde/geraPedido');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json",
                        'hashId: fd0ab7097fb7119900febac7e3875218'));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);
                    curl_close ($ch);

                    $onlineOrder->setStatus('ESPERA');
                    $onlineOrder->setBoardingDate(new \DateTime());

                    $em->persist($onlineOrder);
                    $em->flush($onlineOrder);

                } else {
                    $onlineOrder->setStatus($status);
                    $onlineOrder->setBoardingDate(new \DateTime());

                    $em->persist($onlineOrder);
                    $em->flush($onlineOrder);

                    $postfields = array();
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/orderChange');
                    // curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServerTeste.'/orderChange');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array("Content-type: application/json"));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);
                    curl_close ($ch);
                }
            }

            $em->getConnection()->commit();

            $result = updateOrders();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function changeStatus(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        $flights = $request->getRow()['flights'];

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        try {
            $onlineOrder->setStatus($dados['status']);
            $onlineOrder->setBoardingDate(new \DateTime());

            for ($i = 0; $i <= count($flights)-1; $i++) {
                $flight = $flights[$i];
                $OnlineFlight = $em->getRepository('OnlineFlight')->findOneBy(array('id' => $flight['id']));

                if(isset($flight['card_number']) && $flight['card_number'] != '' && isset($flight['provider']) && $flight['provider'] != ''){

                    $Card = $em->getRepository('Cards')->findOneBy(array('cardNumber' => $flight['card_number']));
                    $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $flight['provider']));
                    if(!$provider){
                        $Businesspartner = new \Businesspartner();
                        $Businesspartner->setName($flight['provider']);
                        $Businesspartner->setPartnerType('P');
                        $em->persist($Businesspartner);
                        $em->flush($Businesspartner);
                    }

                    $OnlineFlight->setCards($Card);
                    $OnlineFlight->setProvider($Businesspartner);
                }

                if(isset($flight['partnerReservationCode']) && $flight['partnerReservationCode'] != '') {
                    $OnlineFlight->setReservationCode($flight['partnerReservationCode']);
                }

                $em->persist($OnlineFlight);
                $em->flush($OnlineFlight);
            }

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $em->getConnection()->commit();

            $result = updateOrders();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveStatusOrder(Request $request, Response $response) {
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        try {
            $onlineOrder->setStatus('PENDENTE');
            $onlineOrder->setBoardingDate(new \DateTime());

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $em->getConnection()->commit();

            $result = updateOrders();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadConnectionsFlight(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();
        if(isset($dados['id'])) {

            $OnlineConnection = $em->getRepository('OnlineConnection')->findBy(array('onlineFlight' => $dados['id']));

            foreach($OnlineConnection as $connection) {

                $airport_description_from = '';
                $airportFrom = $em->getRepository('Airport')->findOneBy(array('code' => $connection->getAirportCodeFrom()));
                if($airportFrom) {
                    $airport_description_from = $airportFrom->getName();
                    if(count(explode('/', $airportFrom->getName())) == 2) {
                        $airport_description_from = explode('/', $airportFrom->getName())[1];
                    }
                }

                $airport_description_to = '';
                $AirportTo = $em->getRepository('Airport')->findOneBy(array('code' => $connection->getAirportCodeTo()));
                if($AirportTo) {
                    $airport_description_to = $AirportTo->getName();
                    if(count(explode('/', $AirportTo->getName())) == 2) {
                        $airport_description_to = explode('/', $AirportTo->getName())[1];
                    }
                }

                $dataset[] = array(
                    'id' => $connection->getId(),
                    'flight' => $connection->getFlight(),
                    'flightTime' => $connection->getFlightTime(),
                    'boarding' => $connection->getBoarding(),
                    'landing' => $connection->getLanding(),
                    'airportCodeFrom' => $connection->getAirportCodeFrom(),
                    'airport_description_from' => $airport_description_from,
                    'airportCodeTo' => $connection->getAirportCodeTo(),
                    'airport_description_to' => $airport_description_to
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function setStatusOrderCommercial(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        try {
            $onlineOrder->setCommercialStatus('true');

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Liberação comercial - Pedido: " . $dados['id']);
            $SystemLog->setLogType('LIBERATION-COMMERCIAL');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $em->getConnection()->commit();
            $result = updateOrders();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function setStatusOrderCommercialFaturado(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        try {
            $onlineOrder->setCommercialStatus('true');
            $onlineOrder->setPaymentDays($request->getRow()['faturado']);

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Liberação comercial - Pedido: " . $dados['id']);
            $SystemLog->setLogType('LIBERATION-COMMERCIAL');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $em->getConnection()->commit();
            $result = updateOrders();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function updateAllSocket(Request $request, Response $response) {
        $result = updateOrders();
    }

    public function updatePaxName(Request $request, Response $response) {
        $dados = $request->getRow();

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        $OnlinePax = $em->getRepository('OnlinePax')->find($dados['paxId']);

        try {

            $OnlinePax->setPaxName($dados['paxData']['pax_name']);
            $OnlinePax->setPaxLastName($dados['paxData']['paxLastName']);
            if(isset($dados['paxData']['paxAgnome']) && $dados['paxData']['paxAgnome'] != '') {
                $OnlinePax->setPaxAgnome($dados['paxData']['paxAgnome']);
            } else {
                $OnlinePax->setPaxAgnome(null);
            }
            $OnlinePax->setIdentification($dados['paxData']['identification']);

            $em->persist($OnlinePax);
            $em->flush($OnlinePax);

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function automaticEmissionRobot(Request $request, Response $response) {
        $dados = $request->getRow();

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        try {

            if(!isset($dados['order_id']) || $dados['order_id'] == '') {
                throw new \Exception("Id do pedido obrigatoria!", 1);
            }

            $OnlineOrder = $em->getRepository('OnlineOrder')->find($dados['order_id']);
            if(!$OnlineOrder) {
                throw new \Exception("Pedido não encontrado!", 1);
            }

            $RobotEmissionIn8 = $em->getRepository('RobotEmissionIn8')->findBy(array( 'order' => $dados['order_id'] ));
            if(count($RobotEmissionIn8) > 0) {
                throw new \Exception("Emissão ja iniciado!", 1);
            }

            // validação de metodo de pagamento e status do cliente
            $Issuer = $em->getRepository('Businesspartner')->findOneBy( array( 'name' => $OnlineOrder->getClientLogin() ) );
            if(!$Issuer) {
                throw new \Exception("Login nao cadastrado!", 1);
            }
            $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Issuer->getClient()));
            if(!$Client) {
                throw new \Exception("Cliente não encontrado!", 1);
            }
            if($Client->getStatus() != 'Aprovado' || $Client->getPaymentType() != 'Boleto') {
                throw new \Exception("Cliente nao valido para emissão!", 1);
            }

            // getting airline
            $Airline = $em->getRepository('Airline')->findOneBy(array('id' => '2'));
            if($Airline->getRobotStatus() == 'false') {
                throw new \Exception("Emissao automatica desativada!", 1);
            }

            // getting card
            $Cards = $Airline->getRobotCards();
            if(!$Cards) {
                throw new \Exception("Nao existe cartao vinculado para a emissão!", 1);
            }

            // getting credit card
            $InternalCards = $Cards->getCardTax();
            if(!$InternalCards) {
                $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('priorityAirline' => '2'));
            }
            if(!$InternalCards) {
                throw new \Exception("Sem cartao de credito vinculado!", 1);
            }

            $postfields = array(
                'order' => array( 'id' => $dados['order_id'] ),
                'onlineflights' => array(
                    array(
                        'tax_card' => $InternalCards->getCardNumber(),
                        'tax_password' => decript($InternalCards->getCardPassword()),
                        'tax_providerName' => $InternalCards->getShowName(),
                        'tax_cardType' => $InternalCards->getCardType(),
                        'cards_id' => $Cards->getId(),
                        'celNumberAirline' => $Cards->getBusinesspartner()->getCelNumberAirline(),
                        'recovery_password' => decript($Cards->getRecoveryPassword())
                    )
                ),
                'hashId' => '9901401e7398b65912d5cae4364da460'
            );

            $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://gestao.srm.systems/'.$DirServer.'/backend/application/index.php?rota=/in8Bot/newOrder');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            // sending update of status to order
            $email1 = 'adm@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $content = '<br>Ola,<br><br>Novo pedido enviado para emissão automatica' .
                        '<br>ID do pedido: ' . $dados['order_id'] .
                        '<br>Metodo de Pagamento:' . 'Boleto';
                        $return = Mail::sendTransactional($email1, $email2, $content, 'Emissão automatica - Pedido: ' . $dados['order_id']);

            $em->getConnection()->commit();

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

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Pedido enviado com sucesso!');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function getOrdersWaiting(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select o FROM OnlineOrder o where o.status in ('ESPERA VLR', 'ESPERA LIM', 'ESPERA', 'ESPERA PGTO','ESPERA LIM VLR', 'ANT BLOQ','ANT','BLOQ') order by o.createdAt DESC";
        $query = $em->createQuery($sql);
        $onlineOrder = $query->getResult();

        $dataset = array();
        foreach ($onlineOrder as $order) {
            $dataset[] = array(
                'id' => $order->getId()
            );
        }
        $response->setDataset($dataset);
    }

    public function saveBilletOrder(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['file'])) {
            $file = $dados['file'];
        }

        $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
        $extension = explode('.', $file_name);
        $replace = 0;

        $s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => array(
                'key' => getenv('AWS_KEY'),
                'secret'  => getenv('AWS_SECRET')
            )
        ]);

        $bucket = 'bilhetes-mmsgestao';
        $keyname = $dados['id'] . '/' . $extension[0] . '.' . $extension[1];
        $filepath = $file['tmp_name'];

        $result = $s3->putObject(array(
            'Bucket' => $bucket,
            'Key'    => $keyname,
            'SourceFile' => $filepath,
            'Body'   => '',
            'ACL'    => 'public-read'
        ));

        $em = Application::getInstance()->getEntityManager();
        $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $dados['id']));

        $OnlineBillets = new \OnlineBillets();
        $OnlineBillets->setKeyname($keyname);
        $OnlineBillets->setUrl($result['ObjectURL']);
        $OnlineBillets->setOrder($OnlineOrder);

        $em->persist($OnlineBillets);
        $em->flush($OnlineBillets);
    }

    public function setNotificationOrder(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        $status = $request->getRow()['status'];
        if(isset($request->getRow()['retarifation'])) {
            $retarifation = $request->getRow()['retarifation'];
        }
        if(isset($request->getRow()['hashId'])) {
			$hashId = $request->getRow()['hashId'];
		}

        // RETARIFADA               	= 3
        // DADOS_PASSAGEIRO_INVALIDO 	= 4
        // SITE_CIA_FORA_AR 	        = 5
        // INDISPONIVEL              	= 6

        $em = Application::getInstance()->getEntityManager();
        $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $dados['id']));

        if($OnlineOrder->getNotificationurl()) {
            $jsonToPost = array(
                'notificationCode' => $OnlineOrder->getNotificationcode(),
                'status' => $status,
                'hashId' => "fd0ab7097fb7119900febac7e3875218"
            );

            if(isset($request->getRow()['retarifation'])) {
                foreach ($request->getRow()['retarifation'] as $key => $value) {
                    if($value['retarifation'] == 'true') {
                        if($value['path'] == 'IDA') {
                            $jsonToPost['StatusIda'] = $status;
                            $jsonToPost['ida'] = array(
                                "milhas_adulto" => $value['milhas_adulto'],
                                "milhas_crianca" => $value['milhas_crianca'],
                                "milhas_bebe" => $value['milhas_bebe'],
                                "data_embarque" => $value['data_embarque'],
                                "voo" => $value['voo'],
                                "origem" => $value['origem'],
                                "destino" => $value['destino'],
                                "retarifation" => $value['retarifation'],
                                "path" => $value['path']
                            );
                        } else {
                            $jsonToPost['StatusVolta'] = $status;
                            $jsonToPost['volta'] = array(
                                "milhas_adulto" => $value['milhas_adulto'],
                                "milhas_crianca" => $value['milhas_crianca'],
                                "milhas_bebe" => $value['milhas_bebe'],
                                "data_embarque" => $value['data_embarque'],
                                "voo" => $value['voo'],
                                "origem" => $value['origem'],
                                "destino" => $value['destino'],
                                "retarifation" => $value['retarifation'],
                                "path" => $value['path']
                            );
                        }
                    } else {
                        if($value['path'] == 'IDA') {
                            $jsonToPost['StatusIda'] = 0;
                        } else {
                            $jsonToPost['StatusVolta'] = 0;
                        }
                    }
                }
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $OnlineOrder->getNotificationurl());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
            $result = curl_exec($ch);

            $content = "<br>".json_encode($jsonToPost)."<br><br>SRM-IT";

            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'PEDIDOS - INCODDE - UPDATE',
                'type' => ''
            );

            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                $postfields['subject'] = '[HOMOLOGAÇÃO] PEDIDOS - [HOMOLOGAÇÃO] - INCODDE - [HOMOLOGAÇÃO] - UPDATE';
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            if($status == 3 || $status == '3') {
                $OnlineOrder->setStatus('RETARIFADA');
                // $OnlineOrder->setStatus('CANCELADO');
            } else if($status == 4 || $status == '4') {
                $OnlineOrder->setStatus('DADOS_PASSAGEIRO_INVALIDO');
            } else if($status == 5 || $status == '5') {
                $OnlineOrder->setStatus('SITE_CIA_FORA_AR');
            } else if($status == 6 || $status == '6') {
                $OnlineOrder->setStatus('CANCELADO');
            }

            $em->persist($OnlineOrder);
            $em->flush($OnlineOrder);

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
            $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            $OnlineNotificationStatus = new \OnlineNotificationStatus();
            $OnlineNotificationStatus->setStatus($status);
            $OnlineNotificationStatus->setUser($Businesspartner->getName());
            $OnlineNotificationStatus->setIssueDate(new \Datetime());
            $OnlineNotificationStatus->setOrder($OnlineOrder);
            $em->persist($OnlineNotificationStatus);
            $em->flush($OnlineNotificationStatus);

            $result = updateOrders();
            $result = updateOrdersWaiting();
        }

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro salvo com sucesso');
        $response->addMessage($message);
    }

    public function loadNotificationOrder(Request $request, Response $response) {
        $dados = $request->getRow()['data'];

        // RETARIFADA               	= 3
        // DADOS_PASSAGEIRO_INVALIDO 	= 4
        // SITE_CIA_FORA_AR 	        = 5
        // INDISPONIVEL              	= 6

        $em = Application::getInstance()->getEntityManager();

        $dataset = array();
        $OnlineNotificationStatus = $em->getRepository('OnlineNotificationStatus')->findBy(array('order' => $dados['id']));
        foreach ($OnlineNotificationStatus as $key => $value) {

            $descrition = '';
            if($value->getUser() == 'SKYMILHAS') {
                if($value->getStatus() == '1') {
                    $descrition = 'AGUARDANDO_PAGAMENTO';
                } else if($value->getStatus() == '2') {
                    $descrition = 'EM_ANALISE';
                } else if($value->getStatus() == '3') {
                    $descrition = 'PAGA';
                } else if($value->getStatus() == '4') {
                    $descrition = 'DISPONIVEL';
                } else if($value->getStatus() == '5') {
                    $descrition = 'EM_DISPUTA';
                } else if($value->getStatus() == '6') {
                    $descrition = 'DEVOLVIDA';
                } else if($value->getStatus() == '7') {
                    $descrition = 'CANCELADA';
                } else if($value->getStatus() == '8') {
                    $descrition = 'DEBITADO';
                } else if($value->getStatus() == '9') {
                    $descrition = 'RETENCAO_TEMPORARIA';
                }
            } else {
                if($value->getStatus() == '1') {
                    $descrition = 'EMITIDA';
                } else if($value->getStatus() == '2') {
                    $descrition = 'CANCELADA';
                } else if($value->getStatus() == '3') {
                    $descrition = 'RETARIFADA';
                } else if($value->getStatus() == '4') {
                    $descrition = 'DADOS_PASSAGEIRO_INVALIDO';
                } else if($value->getStatus() == '5') {
                    $descrition = 'SITE_CIA_FORA_AR';
                } else if($value->getStatus() == '6') {
                    $descrition = 'INDISPONIVEL';
                } else if($value->getStatus() == '9') {
                    $descrition = 'FORCADO_PAGAMENTO';
                } else if($value->getStatus() == '8') {
                    $descrition = 'PAGAMENTO_CONFIRMADO';
                }
            }

            $dataset[] = array(
                'id' => $value->getId(),
                'status' => $value->getStatus(),
                'user' => $value->getUser(),
                'issueDate' => $value->getIssueDate()->format('Y-m-d H:i:s'),
                'descrition' => $descrition
            );
        }
        $response->setDataset($dataset);
    }

    public function confirOrderToEmit(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        if(isset($request->getRow()['hashId'])) {
			$hashId = $request->getRow()['hashId'];
		}

        $em = Application::getInstance()->getEntityManager();
        $em->getConnection()->beginTransaction();

        $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        try {
            $onlineOrder->setStatus('PENDENTE');
            $onlineOrder->setBoardingDate(new \DateTime());

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
            if($UserSession) {
                $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
                if($Businesspartner) {
                    $OnlineNotificationStatus = new \OnlineNotificationStatus();
                    $OnlineNotificationStatus->setStatus('9');
                    $OnlineNotificationStatus->setUser($Businesspartner->getName());
                    $OnlineNotificationStatus->setIssueDate(new \Datetime());
                    $OnlineNotificationStatus->setOrder($onlineOrder);
                    $em->persist($OnlineNotificationStatus);
                    $em->flush($OnlineNotificationStatus);
                }
            }

            $em->getConnection()->commit();

            $result = updateOrders();
            $result = updateOrdersWaiting();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function confirmPayment(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        if(isset($request->getRow()['businesspartner'])) {
            $BusinessPartner = $request->getRow()['businesspartner'];
        }

        if(isset($request->getRow()['hashId'])) {
			$hashId = $request->getRow()['hashId'];
        }

        $em = Application::getInstance()->getEntityManager();

        $onlineOrder = $em->getRepository('OnlineOrder')->find($dados['id']);

        try {
            if($onlineOrder->getStatus() == 'EMITIDO') {
                throw new \Exception("PEDIDO JA BAIXADO");
            }

            $onlineOrder->setStatus('PENDENTE');
            $onlineOrder->setBoardingDate(new \DateTime());

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            if(isset($BusinessPartner)) {
                $OnlineNotificationStatus = new \OnlineNotificationStatus();
                $OnlineNotificationStatus->setStatus('8');
                $OnlineNotificationStatus->setUser($BusinessPartner->getName());
                $OnlineNotificationStatus->setIssueDate(new \Datetime());
                $OnlineNotificationStatus->setOrder($onlineOrder);
                $em->persist($OnlineNotificationStatus);
                $em->flush($OnlineNotificationStatus);
            }

            $jsonToPost = array(
                'notificationCode' => $onlineOrder->getNotificationcode(),
                'status' => '8',
                'hashId' => "fd0ab7097fb7119900febac7e3875218"
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $onlineOrder->getNotificationurl());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $jsonToPost ));
            $result = curl_exec($ch);

            $result = updateOrders();
            $result = updateOrdersWaiting();

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

    public function reSendBillets(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        $returnBillets = $this->sendBillets($dados['id']);

        if($returnBillets) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);
        } else {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function reSendOrder(Request $request, Response $response) {
        $dados = $request->getRow()['data'];

        $em = Application::getInstance()->getEntityManager();
        $onlineOrder = $em->getRepository('OnlineOrder')->find($id);

        $jsonToPost = array(
            'notificationCode' => $onlineOrder->getNotificationcode()
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'mms/reenviarop');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
        $result = curl_exec($ch);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro alterado com sucesso');
        $response->addMessage($message);
    }

    public function sendBillets($id) {
        $em = Application::getInstance()->getEntityManager();
        $onlineOrder = $em->getRepository('OnlineOrder')->find($id);

        try {
            $billets = array();
            $OnlineBillets = $em->getRepository('OnlineBillets')->findBy(array('order' => $onlineOrder->getId()));
            foreach ($OnlineBillets as $key => $value) {
                $billets[] = array( 'url' => $value->getUrl() );
            }
            $sales = array();
            $Sale = $em->getRepository('Sale')->findBy(array('externalId' => $onlineOrder->getId()));
            foreach ($Sale as $key => $value) {
                $sales[] = array(
                    'id' => $value->getId(),
                    'localizador' => $value->getFlightLocator(),
                    'pax' => $value->getPax()->getName(),
                    'cpf' => $value->getPax()->getRegistrationCode(),
                    'embarque' => $value->getBoardingDate()->format('Y-m-d H:i:s')
                );
            }

            $jsonToPost = array(
                'notificationCode' => $onlineOrder->getNotificationcode(),
                'status' => '1',
                'vendas' => $sales,
                'bilhetes' => $billets,
                'hashId' => "fd0ab7097fb7119900febac7e3875218"
            );

            $OnlineFlight = $em->getRepository('OnlineFlight')->findBy(array('order' => $onlineOrder->getId()));
            foreach ($OnlineFlight as $key => $value) {
                $Sale = $em->getRepository('Sale')->findOneBy(array('onlineFlightId' => $value->getId()));
                if($Sale) {
                    if($key === 0 || $key === '0') {
                        $jsonToPost['StatusIda'] = '1';
                    } else {
                        $jsonToPost['StatusVolta'] = '1';
                    }
                } else {
                    if($key === 0 || $key === '0') {
                        $jsonToPost['StatusIda'] = '2';
                    } else {
                        $jsonToPost['StatusVolta'] = '2';
                    }
                }
            }

            if(isset($_POST['hashId'])) {
                $hashId = $_POST['hashId'];
            }
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
            if($UserSession) {
                $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
                if($Businesspartner) {
                    $OnlineNotificationStatus = new \OnlineNotificationStatus();
                    $OnlineNotificationStatus->setStatus('1');
                    $OnlineNotificationStatus->setUser($Businesspartner->getName());
                    $OnlineNotificationStatus->setIssueDate(new \Datetime());
                    $OnlineNotificationStatus->setOrder($onlineOrder);
                    $em->persist($OnlineNotificationStatus);
                    $em->flush($OnlineNotificationStatus);
                }
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $onlineOrder->getNotificationurl());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
            $result = curl_exec($ch);

            $email1 = 'suporte@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => json_encode($jsonToPost),
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'PEDIDOS - JSON - VENDA',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function loadOnlineOrderDealer(Request $request, Response $response) {
		$dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

		$users = '';
		$and = '';

        $query = "select b.id from businesspartner b where b.dealer = ".$dados['dealer']." and b.partner_type = 'C'  ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {

            $query2 = "select b.name from businesspartner b where b.client_id = ". $row['id'] ." and b.partner_type = 'S'  ";
            $stmt2 = $QueryBuilder->query($query2);
            while ($row2 = $stmt2->fetch()) {
                $users = $users.$and."'". $row2['name'] ."'";
                $and = ',';
            }
        }

		$sql = "SELECT o FROM OnlineOrder o WHERE o.status NOT IN ('EMITIDO', 'CANCELADO', 'FALHA EMISSAO', 'EM ESPERA', 'budget') and o.clientName in (".$users.") order by o.createdAt DESC";
		$query = $em->createQuery($sql);

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                ->setMaxResults($dados['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }
        $onlineOrder = $query->getResult();

		$orders = array();
		foreach($onlineOrder as $Order){

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $firstBoardingDate = '';
            if($Order->getFirstBoardingDate()) {
                $firstBoardingDate = $Order->getFirstBoardingDate()->format('Y-m-d H:i:s');
            }

			$orders[] = array(
				'client_name' => $Order->getClientName(),
				'client_email' => $Order->getClientEmail(),
                'status' => $Order->getStatus(),
                'real_status' => $Order->getStatus(),
				'airline' => $Order->getAirline(),
				'created_at' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
				'miles_used' => (int)$Order->getMilesUsed(),
				'total_cost' => (float)$Order->getTotalCost(),
				'comments' => $Order->getComments(),
                'userSession' => $comments,
                'firstBoardingDate' => $firstBoardingDate,
                'commercialStatus' => ($Order->getCommercialStatus() == 'true'),
			);
        }

        $dataset = array(
            'orders' => $orders
        );
		$response->setDataset($dataset);
	}
}

function updateOrders($dealer = null){
    $env = getenv('ENV') ? getenv('ENV') : 'production';
    if($env == 'production') {
        // emission socket table update
        $req = new \MilesBench\Request\Request();
        $resp = new \MilesBench\Request\Response();
        $onlineOrder = new OnlineOrder();
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

        if($dealer) {
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();

            $req->setRow(
                array(
                    'dealer' => $dealer
                )
            );

            $onlineOrder = new OnlineOrder();
            $onlineOrder->loadOnlineOrderDealer($req, $resp);
            $postfields = array(
                'orders' => $resp->getDataset(),
                'dealer' => $dealer
            );
            $postfields = json_encode($postfields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/updateOrdersDealer');
            // curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/updateOrdersDealer');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);
            curl_close ($ch);
        }

        return $result;
    }
}

function decript($args) {
    $data = explode('320AB', $args);
    $finaly = "";
    for ($i=0; $i < count($data); $i++) {
        $finaly = $finaly.(chr($data[$i] / 320));
    }
    return $finaly;
}

function updateOrdersWaiting(){
    // // emission socket table update
    $env = getenv('ENV') ? getenv('ENV') : 'production';
    if($env == 'production') {
        $req = new \MilesBench\Request\Request();
        $resp = new \MilesBench\Request\Response();
        $onlineOrder = new \MilesBench\Controller\OnlineOrder();
        $onlineOrder->loadOnlineOrderWaiting($req, $resp);
        $postfields = array(
            'orders' => $resp->getDataset()
        );
        $postfields = json_encode($postfields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/updateOrdersWaiting');
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
