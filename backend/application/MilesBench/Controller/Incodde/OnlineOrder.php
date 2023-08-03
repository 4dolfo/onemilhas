<?php

namespace MilesBench\Controller\Incodde;

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
            'subject' => 'PEDIDOS - INCODDE',
            'type' => ''
        );

        $env = getenv('ENV') ? getenv('ENV') : 'production';
        if($env != 'production') {
            $postfields['subject'] = '[HOMOLOGAÇÃO] PEDIDOS - [HOMOLOGAÇÃO] - INCODDE';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $dados = json_decode($request->getRow(), true);
        $pedido = $dados['pedido'];
        $trechos = $dados['trechos'];
        $passageiros = $dados['passageiros'];
        $robotStatus = false;

        try {
            if(!$passageiros) {
                throw new \Exception("Passageiros não encontrados");
            }
            if(count($passageiros) == 0) {
                throw new \Exception("Passageiros não encontrados");
            }
            if(!$pedido['valor_total'] || $pedido['valor_total'] == 0) {
                throw new \Exception("Valor total invalido");
            }

            $em = Application::getInstance()->getEntityManager();
            $em->getConnection()->beginTransaction();

            if(isset($pedido['notificationcode'])) {
                $onlineOrder = $em->getRepository('OnlineOrder')->findOneBy(
                    array(
                        'notificationcode' => $pedido['notificationcode'],
                        'notificationurl' => $pedido['notificationurl']
                    )
                );
                if($onlineOrder) {

                    $OnlinePax = $em->getRepository('OnlinePax')->findBy(array('order' => $onlineOrder->getId()));
                    foreach ($OnlinePax as $key => $value) {
                        $OnlineBaggage = $em->getRepository('OnlineBaggage')->findBy(array('onlinePax' => $value->getId()));
                        foreach ($OnlineBaggage as $bag) {
                            $em->remove($bag);
                            $em->flush($bag);
                        }
                        $em->remove($value);
                        $em->flush($value);
                    }

                    $OnlineFlight = $em->getRepository('OnlineFlight')->findBy(array('order' => $onlineOrder->getId()));
                    foreach ($OnlineFlight as $key => $value) {
                        $OnlineConnection = $em->getRepository('OnlineConnection')->findBy(array('onlineFlight' => $value->getId()));
                        foreach ($OnlineConnection as $conn) {
                            $em->remove($conn);
                            $em->flush($conn);
                        }
                        $em->remove($value);
                        $em->flush($value);
                    }
                } else {
                    $onlineOrder = new \OnlineOrder();
                }
            } else {
                $onlineOrder = new \OnlineOrder();
            }

            if(isset($pedido['Id'])) {
                $onlineOrder->setExternalId($pedido['Id']);
            } else {
                $onlineOrder->setExternalId('');
            }

            $onlineOrder->setTotalCost($pedido['valor_total']);
            $onlineOrder->setClientName($pedido['nome_cliente']);
            $onlineOrder->setClientEmail($pedido['email_cliente']);
            $onlineOrder->setMilesUsed($pedido['milhas_total']);
            $onlineOrder->setStatus('PENDENTE');

            $comments = '';
            if(isset($pedido['telefone_cliente']) && $pedido['telefone_cliente']  != 'null' && $pedido['telefone_cliente']  != null) {
                $comments .= ' Telefone contato: ' . $pedido['telefone_cliente'];
            }
            if(isset($pedido['telefone'])) {
                $comments .= ' Telefone pagamento: ' . $pedido['telefone'];
            }
            if(isset($pedido['notificationMotivo']) && $pedido['notificationMotivo'] != '') {
                $comments .= '  - ' . $pedido['notificationMotivo'];
            }

            $onlineOrder->setComments($pedido['comments'] . $comments);
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
            if(isset($pedido['metodo_pagamento'])) {
                $onlineOrder->setPaymentMethod($pedido['metodo_pagamento']);
            }
            if(isset($pedido['economia']) && $pedido['economia'] != '') {
                $onlineOrder->setEconomy($pedido['economia']);
            }
            if(isset($pedido['metodo_emissao']) && $pedido['metodo_emissao'] != '') {
                $onlineOrder->setEmissionMethod($pedido['metodo_emissao']);
            }
            if(isset($pedido['notificationurl']) && $pedido['notificationurl'] != '') {
                $onlineOrder->setNotificationurl($pedido['notificationurl']);
            }
            if(isset($pedido['notificationcode']) && $pedido['notificationcode'] != '') {
                $onlineOrder->setNotificationcode($pedido['notificationcode']);
            }
            if(isset($pedido['notificationtype']) && $pedido['notificationtype'] != '') {
                $onlineOrder->setNotificationtype($pedido['notificationtype']);

                if($pedido['notificationtype'] == '1' || $pedido['notificationtype'] == 1) {
                    $onlineOrder->setStatus('Aguardando Pagamento');
                }
                if($pedido['notificationtype'] == '2' || $pedido['notificationtype'] == 2) {
                    $onlineOrder->setStatus('EM_ANALISE');
                }
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
                $onlineOrder->setTaxPayment($pedido['descontos']);
            }
            if(isset($pedido['taxa_aprovacao']) && $pedido['taxa_aprovacao'] != '') {
                $onlineOrder->setTaxApproval($pedido['descontos']);
            }
            if(isset($pedido['valor_pagamento']) && $pedido['valor_pagamento'] != '') {
                $onlineOrder->setValuePayment($pedido['descontos']);
            }
            if(isset($pedido['valor_aprovacao']) && $pedido['valor_aprovacao'] != '') {
                $onlineOrder->setValueApproval($pedido['descontos']);
            }
            if(isset($pedido['agencia_id']) && $pedido['agencia_id'] != '') {
                $onlineOrder->setAgenciaId($pedido['agencia_id']);
            }
            if(isset($pedido['utm']) && $pedido['utm'] != '') {
                $onlineOrder->setUtm($pedido['utm']);
            }
            if(isset($pedido['cupom']) && $pedido['cupom'] != '') {
                $onlineOrder->setCupom($pedido['cupom']);
            }
            if(isset($pedido['tipocupom']) && $pedido['tipocupom'] != '') {
                $onlineOrder->setTipocupom($pedido['tipocupom']);
            }
            if(isset($pedido['indicacao']) && $pedido['indicacao'] != '') {
                $onlineOrder->setIndicacao($pedido['indicacao']);
            }
            if(isset($pedido['creditoUsado']) && $pedido['creditoUsado'] != '') {
                $onlineOrder->setCreditoUsado($pedido['creditoUsado']);
            }
            if(isset($pedido['valorCupom']) && $pedido['valorCupom'] != '') {
                $onlineOrder->setValorcupom(floatval($pedido['valorCupom']));
            }
            if(isset($pedido['transferencia']) && $pedido['transferencia'] != '' && $pedido['transferencia'] != null && $pedido['transferencia'] != 'null') {
                $onlineOrder->setDataTransferencia(new \Datetime($pedido['transferencia']));
            }
            if(isset($pedido['markup'])) {
                $onlineOrder->setMarckupCliente($pedido['markup']);
            }
            if(isset($pedido['acrescimo'])) {
                $onlineOrder->setAcrescimo((float)$pedido['acrescimo']);
            }
            if(isset($pedido['totalParcelas'])) {
                $onlineOrder->setTotalParcelas($pedido['totalParcelas']);
            }
            if(isset($pedido['totalReal'])) {
                $onlineOrder->setTotalreal((float)$pedido['totalReal']);
            }
            if(isset($pedido['comprovanteTransferencia'])) {
                $onlineOrder->setComprovantetransferencia($pedido['comprovanteTransferencia']);
            }
	    if(isset($pedido['enviaOP']) && ($pedido['enviaOP'] == true || $pedido['enviaOP'] == 'true')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_production . 'mms/enviaOP');
                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::buscaideal_url_homologacao . 'mms/enviaOP');
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json"));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $dados ));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);
                curl_close ($ch);
            }

            $onlineOrder->setOriginalSystem('');
            $onlineOrder->setOrderPost( json_encode( $dados ));

            $valueTax = 0;
            $airlines = '';
            foreach($trechos as $trecho){
                foreach($passageiros as $passageiro){
                    if($passageiro == null || $passageiro == "null" || !isset($passageiro['nome'])) {
                        throw new \Exception("Passageiro Obrigatorio");
                    }
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
                $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $trecho['cia']));
                if($Airline) {
                    if($Airline->getRobotStatus() == 'true') {
                        $robotStatus = true;
                    }
                }
            }
            // $onlineOrder->setTotalCost($pedido['valor_total'] + $valueTax);
            $onlineOrder->setAirline($airlines);
            // $onlineOrder->setTotalCost($pedido['valor_total']);

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $OnlineNotificationStatus = new \OnlineNotificationStatus();
            $OnlineNotificationStatus->setStatus($pedido['notificationtype']);
            $OnlineNotificationStatus->setUser('SKYMILHAS');
            $OnlineNotificationStatus->setIssueDate(new \Datetime());
            $OnlineNotificationStatus->setOrder($onlineOrder);
            $em->persist($OnlineNotificationStatus);
            $em->flush($OnlineNotificationStatus);

            $flightsDates = array();
            foreach($trechos as $trecho){
                $onlineFlight = new \OnlineFlight();
                $onlineFlight->setOrder($onlineOrder);
                $onlineFlight->setAirline($trecho['cia']);
                $onlineFlight->setAirportCodeFrom($trecho['sigla_aeroporto_origem']);
                $onlineFlight->setAirportCodeTo($trecho['sigla_aeroporto_destino']);
                $onlineFlight->setAirportDescriptionFrom($trecho['descricao_aeroporto_origem']);
                $onlineFlight->setAirportDescriptionTo($trecho['descricao_aeroporto_destino']);

                // $data_embarque = substr($trecho['data_embarque'],0,5).substr($trecho['data_embarque'],8,2).substr($trecho['data_embarque'],4,3).substr($trecho['data_embarque'],-9);
                if($this->validateDate($trecho['data_embarque']) == true) {
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
                if($this->validateDate($trecho['data_pouso']) == true) {
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

                if(isset($trecho['metodo_emissao']) && $trecho['metodo_emissao'] != '') {
                    $onlineFlight->setEmissionMethod($trecho['metodo_emissao']);
                }

                if(isset($trecho['classe_tarifaria']) && $trecho['classe_tarifaria'] != '') {
                    $onlineFlight->setFareClass($trecho['classe_tarifaria']);
                }

                if(isset($trecho['_id']) && $trecho['_id'] != '') {
                    $onlineFlight->setVooId($trecho['_id']);
                }
                if(isset($trecho['request_id']) && $trecho['request_id'] != '') {
                    $onlineFlight->setVooOfferId($trecho['request_id']);
                }

                error_log(print_r($onlineFlight, true));

                $em->persist($onlineFlight);
                $em->flush($onlineFlight);

                if(isset($trecho['conexao'])) {
                    if (is_array($trecho['conexao'])) {
                        $conexaoFlight = '';
                        foreach($trecho['conexao'] as $conexao){

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

                            $conexaoFlight = $conexaoFlight.' '.
                                            $conexao['NumeroVoo'];
                        }

                        $onlineFlight->setConnection($conexaoFlight);
                    } else {
                        $onlineFlight->setConnection($trecho['conexao']);
                    }
                }

                if(isset($trecho['conexoes'])) {
                    if (is_array($trecho['conexoes'])) {
                        $conexaoFlight = '';
                        foreach($trecho['conexoes'] as $conexao){

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

                            $conexaoFlight = $conexaoFlight.' '.
                                            $conexao['NumeroVoo'];
                        }

                        $onlineFlight->setConnection($conexaoFlight);
                    } else {
                        $onlineFlight->setConnection($trecho['conexoes']);
                    }
                }

                if(isset($trecho['taxa_conveniencia'])) {
                    $onlineFlight->setDuTax($trecho['taxa_conveniencia']);
                }

                error_log(print_r($onlineFlight, true));

                $em->persist($onlineFlight);
                $em->flush($onlineFlight);
            }

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            foreach($passageiros as $passageiro){
                if(isset($passageiro['nome']) && $passageiro['nome'] != "") {
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
                    if($this->validateDate($passageiro['data_nascimento']) == true || $this->validateDate($passageiro['data_nascimento']) == "true") {
                        $onlinePax->setBirthdate(new \Datetime($passageiro['data_nascimento']));
                    } else {
                        $onlinePax->setBirthdate(null);
                    }

                    if(isset($passageiro['telefone']) && $passageiro['telefone'] != '') {
                        $onlinePax->setPhoneNumber($passageiro['telefone']);
                    } else {
                        $onlinePax->setPhoneNumber('');
                    }

			        if(isset($passageiro['data_passaporte'])) {
                        if($this->validateDate($passageiro['data_passaporte']) == true || $this->validateDate($passageiro['data_passaporte']) == "true") {
                            $onlinePax->setDataPassaporte(new \Datetime($passageiro['data_passaporte']));
                        } else {
                            $onlinePax->setDataPassaporte(null);
                        }
                    }

                    if(isset($passageiro['passaporte']) && $passageiro['passaporte'] != '') {
                        $onlinePax->setPassaporte($passageiro['passaporte']);
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
                            if($value != null) {

                                $flight = explode('_', $key);
                                $onlineFlight = $em->getRepository('OnlineFlight')
                                    ->findOneBy( array( 'airportCodeFrom' => $flight[0], 'airportCodeTo' => $flight[1], 'order' => $onlineOrder->getId() ) );
                                if($onlineFlight) {
                                    $OnlineBaggage = new \OnlineBaggage();

                                    if(is_array($value)) {
                                        $OnlineBaggage->setAmount((int)$value['value']);
                                        $OnlineBaggage->setPrice($value['price']);
                                    } else {
                                        $OnlineBaggage->setAmount((int)$value);
                                    }
                                    $OnlineBaggage->setOnlineFlight($onlineFlight);
                                    $OnlineBaggage->setOnlinePax($onlinePax);

                                    $em->persist($OnlineBaggage);
                                    $em->flush($OnlineBaggage);
                                }
                            }
                        }
                    }
                }
            }
            $em->getConnection()->commit();

            if($onlineOrder->getStatus() != 'Aguardando Pagamento' && $onlineOrder->getStatus() != 'EM_ANALISE') {
                $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $pedido['nome_cliente']));
                if($Issuer) {

                    $BusinesspartnerTags = $em->getRepository('BusinesspartnerTags')->findOneBy(
                        array('businesspartner' => $Issuer->getClient(), 'tag' => 1)
                    );
                    if($BusinesspartnerTags) {
                        $onlineOrder->setStatus('PRIORIDADE');
                        $em->persist($onlineOrder);
                        $em->flush($onlineOrder);
                    }
                }
            }

            ///////////////////////////////////////////////////////////////////////////////////////////
            // PRODUCTION ONLY
            if($onlineOrder->getStatus() == 'Aguardando Pagamento' || $onlineOrder->getStatus() == 'EM_ANALISE') {
                $result = updateOrdersWaiting();
            } else {
                $result = updateOrders();
            }

            if($onlineOrder->getStatus() == 'PENDENTE') {
                // if($robotStatus) {
                //     $postfields = json_decode($onlineOrder->getOrderPost(), true);
                //     $postfields['pedido']['notificationurl'] = "http://34.207.228.97/" . \MilesBench\Util::sistema . "/backend/application/index.php?rota=/incodde/updateMMS";
                //     $postfields['pedido']['notificationtype'] = '3';
                //     $ch = curl_init();
                //     curl_setopt($ch, CURLOPT_URL, 'http://34.207.228.97/cml-gestao/backend/application/index.php?rota=/incodde/geraPedido');
                //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //     curl_setopt($ch, CURLOPT_POST, 1);
                //     curl_setopt($ch, CURLOPT_HTTPHEADER,
                //         array("Content-type: application/json",
                //         'hashId: fd0ab7097fb7119900febac7e3875218'));
                //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
                //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                //     $result = curl_exec($ch);
                //     curl_close ($ch);
                // }
            }
            ///////////////////////////////////////////////////////////////////////////////////////////

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Pedido incluido com sucesso');
            $response->addMessage($message);
            $response->setDataset(array('order_id' => $onlineOrder->getId()));

        } catch (\Exception $e) {

            header('HTTP/1.1 500 Internal Server Error');
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";

            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => '[MMS] - ERROR - PEDIDOS',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            // $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
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

    public function update(Request $request, Response $response) {
        $content = "<br>".$request->getRow()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
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

        try {
            $dados = json_decode($request->getRow(), true);
            $em = Application::getInstance()->getEntityManager();

            $onlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('notificationcode' => $dados['notificationCode']));
            $onlineOrder->setNotificationtype($dados['notificationType']);
            if($dados['notificationType'] == '3' || $dados['notificationType'] == 3) {
                if($onlineOrder->getStatus() != 'EMITIDO') {
                    $onlineOrder->setStatus('PENDENTE');
                    $onlineOrder->setBoardingDate(new \DateTime());

                    $robotStatus = false;
                    $OnlineFlight = $em->getRepository('OnlineFlight')->findBy(array('order' => $onlineOrder->getId()));
                    foreach ($OnlineFlight as $key => $value) {
                        $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $value->getAirline()));
                        if($Airline->getRobotStatus() == 'true') {
                            $robotStatus = true;
                        }
                    }

                    if($robotStatus) {
                        // $postfields = json_decode($onlineOrder->getOrderPost(), true);
                        // $postfields['pedido']['notificationurl'] = "http://34.207.228.97/" . \MilesBench\Util::sistema . "/backend/application/index.php?rota=/incodde/updateMMS";
                        // $postfields['pedido']['notificationtype'] = '3';
                        // $ch = curl_init();
                        // curl_setopt($ch, CURLOPT_URL, 'http://34.207.228.97/cml-gestao/backend/application/index.php?rota=/incodde/geraPedido');
                        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        // curl_setopt($ch, CURLOPT_POST, 1);
                        // curl_setopt($ch, CURLOPT_HTTPHEADER,
                        //     array("Content-type: application/json",
                        //     'hashId: fd0ab7097fb7119900febac7e3875218'));
                        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
                        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        // $result = curl_exec($ch);
                        // curl_close ($ch);
                    }

                }
            } else if($dados['notificationType'] == '7' || $dados['notificationType'] == 7) {
                $onlineOrder->setStatus('CANCELADO');
                // $onlineOrder->setCancelReason('CANCELADO');
            }

            if(isset($dados['transferencia']) && $dados['transferencia'] != '') {
                $onlineOrder->setDataTransferencia(new \DateTime($dados['transferencia']));
            }

            if($dados['notificationType'] == '4' || $dados['notificationType'] == 4) {
                $localizador = $dados['localizador'];

                $req = new \MilesBench\Request\Request();
                $resp = new \MilesBench\Request\Response();
                $req->setRow(
                    array(
                        'data' => array(
                            'id' => $onlineOrder->getId(),
                            'client_login' => $onlineOrder->getClientLogin()
                        )
                    )
                );
                $onlineOrderClass = new \MilesBench\Controller\OnlineOrder();
                $onlineOrderClass->loadFlights($req, $resp);

                $Client = $em->getRepository('Businesspartner')->find($onlineOrder->getAgenciaId());

                $flights = $resp->getDataset();
                foreach ($flights as $key => $flight) {

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
                                'registrationCode' => $flight['identification']
                            )
                        );
                    } else {
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => trim(mb_strtoupper($flight['pax_name'], 'UTF-8'))));
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
                    } else {
                        if (strpos($BusinessPartner->getPartnerType(),'X')) {
                            $BusinessPartner->setPartnerType($BusinessPartner->getPartnerType().'_X');
                        }
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

                    $miles_used = 0;
                    if ($flight['is_child'] == 'S') {
                        if(isset($flight['discount']) && $flight['discount'] != ''){
                            $amount_paid = $flight['cost_per_child'] - $flight['discount'];
                        } else {
                            $amount_paid = $flight['cost_per_child'];
                        }
                    } elseif ($flight['is_newborn'] == 'S') {
                        if(isset($flight['discount']) && $flight['discount'] != ''){
                            $amount_paid = $flight['cost_per_newborn'] - $flight['discount'];
                        } else {
                            $amount_paid = $flight['cost_per_newborn'];
                        }
                    } else {
                        if(isset($flight['discount']) && $flight['discount'] != ''){
                            $amount_paid = $flight['cost_per_adult'] - $flight['discount'];
                        }else{
                            $amount_paid = $flight['cost_per_adult'];
                        }
                    }

                    $sale_cards = null;
                    $total_cost = 0;
                    $cost_provider = 0;

                    $flight['provider'] = 'FLYTOUR';
                    if(isset($flight['provider']) && $flight['provider'] != '') {
                        $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $flight['provider'], 'partnerType' => 'P'));
                        if(!$provider){
                            $provider = new \Businesspartner();
                            $provider->setName($flight['provider']);
                            $provider->setPartnerType('P');
                            $em->persist($provider);
                            $em->flush($provider);
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
                    if ($onlineOrder->getComments()) {
                        $Sale->setDescription($onlineOrder->getComments());
                    }
                    $Sale->setFlightLocator(mb_strtoupper($localizador, 'UTF-8'));
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
                    }

                    if((isset($flight['du_tax'])) && ($flight['du_tax'] != '')){
                        $Sale->setDuTax($flight['du_tax']);
                    }

                    if ($onlineOrder->getClientLogin()) {
                        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $onlineOrder->getClientLogin()));
                        if (!$BusinessPartner) {
                            $BusinessPartner = new \Businesspartner();
                            $BusinessPartner->setName($onlineOrder->getClientLogin());
                            $BusinessPartner->setPartnerType('S');
                            $BusinessPartner->setClient($Client->getId());

                            $em->persist($BusinessPartner);
                            $em->flush($BusinessPartner);
                        }
                        $Sale->setIssuing($BusinessPartner);
                    }

                    $Sale->setClient($Client);
                    $Sale->setFlight($flight['flight']);
                    $Sale->setFlightHour($flight['flight_time'].'hs '.$connection);
                    $Sale->setExternalId($onlineOrder->getId());
                    $Sale->setProviderSaleByThird($provider);

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

                    $Sale->setProcessingTime('');
                    $Sale->setProcessingStartDate(new \DateTime());

                    $Sale->setStatus('Emitido');

                    // $Sale->setUser();
                    $em->persist($Sale);
                    $em->flush($Sale);

                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()."");
                    $SystemLog->setLogType('SALE');

                    $em->persist($SystemLog);
                    $em->flush($SystemLog);

                    $em->persist($Sale);
                    $em->flush($Sale);

                    $Billsreceive = new \Billsreceive();
                    $Billsreceive->setStatus('A');
                    $Billsreceive->setClient($Client);
                    $Billsreceive->setDescription('Passageiro ' . $flight['pax_name'] . ' - Localizador ' . $localizador);
                    $Billsreceive->setOriginalValue($Sale->getAmountPaid());
                    $Billsreceive->setActualValue($Sale->getAmountPaid());
                    $Billsreceive->setTax(0);
                    $Billsreceive->setDiscount(0);
                    $Billsreceive->setAccountType('Venda Bilhete');
                    $Billsreceive->setReceiveType('Boleto Bancario');
                    $Billsreceive->setDueDate(new \DateTime());
                    $em->persist($Billsreceive);
                    $em->flush($Billsreceive);

                    $SaleBillsreceive = new \SaleBillsreceive();
                    $SaleBillsreceive->setBillsreceive($Billsreceive);
                    $SaleBillsreceive->setSale($Sale);
                    $em->persist($SaleBillsreceive);
                    $em->flush($SaleBillsreceive);
                }

                $onlineOrder->setStatus('EMITIDO');
                $onlineOrder->setClientName($Client->getName());
                $em->persist($onlineOrder);
                $em->flush($onlineOrder);
            }

            $OnlineNotificationStatus = new \OnlineNotificationStatus();
            $OnlineNotificationStatus->setStatus($dados['notificationType']);
            $OnlineNotificationStatus->setUser('SKYMILHAS');
            $OnlineNotificationStatus->setIssueDate(new \Datetime());
            $OnlineNotificationStatus->setOrder($onlineOrder);

            if(isset($dados['notificationReason']) && $dados['notificationReason'] != '') {
                $OnlineNotificationStatus->setReason($dados['notificationReason']);
            }

            $em->persist($OnlineNotificationStatus);
            $em->flush($OnlineNotificationStatus);

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $result = updateOrders();
            $result = updateOrdersWaiting();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados atualizados com sucesso!');
            $response->addMessage($message);
        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => '[MMS] - INCODDDE - ERROR - ATUALIZACAO',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            // $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Erro identificado');
            $response->addMessage($message);
        }
    }

    public function updateMMS(Request $request, Response $response) {
        try {
            $dados = json_decode($request->getRow(), true);
            $em = Application::getInstance()->getEntityManager();

            $onlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('notificationcode' => $dados['notificationCode']));
            $onlineOrder->setNotificationtype($dados['status']);
            if($dados['status'] == '2' || $dados['status'] == 2) {
                if($onlineOrder->getStatus() != 'EMITIDO') {
                    $onlineOrder->setStatus('CANCELADO');
                    $onlineOrder->setBoardingDate(new \DateTime());

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
                        $OnlineNotificationStatus->setUser('MMS');
                        $OnlineNotificationStatus->setIssueDate(new \Datetime());
                        $OnlineNotificationStatus->setOrder($onlineOrder);
                        $em->persist($OnlineNotificationStatus);
                        $em->flush($OnlineNotificationStatus);
                    }
                }

            } else if($dados['status'] == '1' || $dados['status'] == 1) {
                $vendas = $dados['vendas'];

                $req = new \MilesBench\Request\Request();
                $resp = new \MilesBench\Request\Response();
                $req->setRow(
                    array(
                        'data' => array(
                            'id' => $onlineOrder->getId(),
                            'client_login' => $onlineOrder->getClientLogin()
                        )
                    )
                );
                $onlineOrderClass = new \MilesBench\Controller\OnlineOrder();
                $onlineOrderClass->loadFlights($req, $resp);

                $Client = $em->getRepository('Businesspartner')->find($onlineOrder->getAgenciaId());

                $flights = $resp->getDataset();
                foreach ($flights as $key => $flight) {
                    foreach ($vendas as $venda) {
                        if($flight['identification'] == $venda['cpf']) {
                            $localizador = $venda['localizador'];
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
                                        'registrationCode' => $flight['identification']
                                    )
                                );
                            } else {
                                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => trim(mb_strtoupper($flight['pax_name'], 'UTF-8'))));
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
                            } else {
                                if (strpos($BusinessPartner->getPartnerType(),'X')) {
                                    $BusinessPartner->setPartnerType($BusinessPartner->getPartnerType().'_X');
                                }
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

                            $miles_used = 0;
                            if ($flight['is_child'] == 'S') {
                                if(isset($flight['discount']) && $flight['discount'] != ''){
                                    $amount_paid = $flight['cost_per_child'] - $flight['discount'];
                                } else {
                                    $amount_paid = $flight['cost_per_child'];
                                }
                            } elseif ($flight['is_newborn'] == 'S') {
                                if(isset($flight['discount']) && $flight['discount'] != ''){
                                    $amount_paid = $flight['cost_per_newborn'] - $flight['discount'];
                                } else {
                                    $amount_paid = $flight['cost_per_newborn'];
                                }
                            } else {
                                if(isset($flight['discount']) && $flight['discount'] != ''){
                                    $amount_paid = $flight['cost_per_adult'] - $flight['discount'];
                                }else{
                                    $amount_paid = $flight['cost_per_adult'];
                                }
                            }

                            $sale_cards = null;
                            $total_cost = 0;
                            $cost_provider = 0;

                            $flight['provider'] = 'FLYTOUR';
                            if(isset($flight['provider']) && $flight['provider'] != '') {
                                $provider = $em->getRepository('Businesspartner')->findOneBy(array('name' => $flight['provider'], 'partnerType' => 'P'));
                                if(!$provider){
                                    $provider = new \Businesspartner();
                                    $provider->setName($flight['provider']);
                                    $provider->setPartnerType('P');
                                    $em->persist($provider);
                                    $em->flush($provider);
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
                            if ($onlineOrder->getComments()) {
                                $Sale->setDescription($onlineOrder->getComments());
                            }
                            $Sale->setFlightLocator(mb_strtoupper($localizador, 'UTF-8'));
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
                            }

                            if((isset($flight['du_tax'])) && ($flight['du_tax'] != '')){
                                $Sale->setDuTax($flight['du_tax']);
                            }

                            if ($onlineOrder->getClientLogin()) {
                                // $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $onlineOrder->getClientLogin()));
                                // // if (!$BusinessPartner) {
                                // //     $BusinessPartner = new \Businesspartner();
                                // //     $BusinessPartner->setName($onlineOrder->getClientLogin());
                                // //     $BusinessPartner->setPartnerType('S');
                                // //     $BusinessPartner->setClient($Client);

                                //     // $em->persist($BusinessPartner);
                                //     // $em->flush($BusinessPartner);
                                // }
                                // $Sale->setIssuing($BusinessPartner);
                            }

                            $Sale->setClient($Client);
                            $Sale->setFlight($flight['flight']);
                            $Sale->setFlightHour($flight['flight_time'].'hs '.$connection);
                            $Sale->setExternalId($onlineOrder->getId());
                            $Sale->setProviderSaleByThird($provider);

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

                            $Sale->setProcessingTime('');
                            $Sale->setProcessingStartDate(new \DateTime());

                            $Sale->setStatus('Emitido');

                            // $Sale->setUser();
                            $em->persist($Sale);
                            $em->flush($Sale);

                            $SystemLog = new \SystemLog();
                            $SystemLog->setIssueDate(new \Datetime());
                            $SystemLog->setDescription("Venda Realizada - Venda n:".$Sale->getId()."");
                            $SystemLog->setLogType('SALE');

                            $em->persist($SystemLog);
                            $em->flush($SystemLog);

                            $em->persist($Sale);
                            $em->flush($Sale);
                        }
                    }
                }

                foreach ($dados['bilhetes'] as $key => $value) {
                    $OnlineBillets = new \OnlineBillets();
                    $OnlineBillets->setKeyname('');
                    $OnlineBillets->setUrl($value['url']);
                    $OnlineBillets->setOrder($onlineOrder);

                    $em->persist($OnlineBillets);
                    $em->flush($OnlineBillets);
                }

                $onlineOrder->setStatus('EMITIDO');
                $onlineOrder->setClientName($Client->getName());
                $em->persist($onlineOrder);
                $em->flush($onlineOrder);

                $onlineOrderClass->sendBillets($onlineOrder->getId());
            }

            $OnlineNotificationStatus = new \OnlineNotificationStatus();
            $OnlineNotificationStatus->setStatus($dados['status']);
            $OnlineNotificationStatus->setUser('SKYMILHAS');
            $OnlineNotificationStatus->setIssueDate(new \Datetime());
            $OnlineNotificationStatus->setOrder($onlineOrder);

            if(isset($dados['notificationReason']) && $dados['notificationReason'] != '') {
                $OnlineNotificationStatus->setReason($dados['notificationReason']);
            }

            $em->persist($OnlineNotificationStatus);
            $em->flush($OnlineNotificationStatus);

            $em->persist($onlineOrder);
            $em->flush($onlineOrder);

            $result = updateOrders();
            $result = updateOrdersWaiting();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados atualizados com sucesso!');
            $response->addMessage($message);
        } catch (\Exception $e) {
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => '[MMS] - INCODDDE - ERROR - ATUALIZACAO',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            // $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Erro identificado');
            $response->addMessage($message);
        }
    }

    public function passageiros(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $passageiros = $dados['passageiros'];
        $em = Application::getInstance()->getEntityManager();

        $onlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('notificationcode' => $dados['notificationCode']));
        $OnlinePax = $em->getRepository('OnlinePax')->findBy(array('order' => $onlineOrder->getId()));
        foreach ($OnlinePax as $key => $value) {
            $OnlineBaggage = $em->getRepository('OnlineBaggage')->findBy(array('onlinePax' => $value->getId()));
            foreach ($OnlineBaggage as $bag) {
                $em->remove($bag);
                $em->flush($bag);
            }

            $em->remove($value);
            $em->flush($value);
        }

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

            if($this->validateDate($passageiro['data_nascimento']) == true || $this->validateDate($passageiro['data_nascimento']) == "true") {
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

                    $flight = explode('_', $key);
                    $onlineFlight = $em->getRepository('OnlineFlight')
                        ->findOneBy( array( 'airportCodeFrom' => $flight[0], 'airportCodeTo' => $flight[1], 'order' => $onlineOrder->getId() ) );

                    if($onlineFlight) {
                        $OnlineBaggage = new \OnlineBaggage();

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
        }

        $onlineOrder->setStatus('PENDENTE');
        $em->persist($onlineOrder);
        $em->flush($onlineOrder);

        $result = updateOrders();

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Dados atualizados com sucesso!');
        $response->addMessage($message);
    }

    public function precificaPedido(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();



        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Dados atualizados com sucesso!');
        $response->addMessage($message);
    }

    public function validaCredito(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();
        $conn = Application::getInstance()->getQueryBuilder();

        $totalPedido = $dados['totalPedido'];
        $client_id = $dados['client_id'];
        $valido = false;

        $partner = $em->getRepository('Businesspartner')->find( $client_id );

        //limit 1 calculation
        $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$partner->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
        $query = $em->createQuery($sql);
        $Limit = $query->getResult();

        $sql = "select SUM(b.actualValue) as partner_limit FROM Billsreceive b WHERE b.client = '".$partner->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
        $query = $em->createQuery($sql);
        $SalesLimit = $query->getResult();

        $sql = "SELECT SUM(orders.total_cost) AS cost FROM ( SELECT DISTINCT o.airline, o.miles_used, o.total_cost, o.status, o.client_email, o.client_name, o.commercial_status, f.boarding_date, f.landing_date FROM online_order o JOIN online_flight AS f ON f.order_id=o.id WHERE o.status IN ('PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA PGTO', 'PRIORIDADE') AND o.client_name IN ( SELECT b.name FROM businesspartner b WHERE b.client_id = '".$partner->getId()."' ) group by o.id ) AS orders";
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

        if($totalLimit < $usedValue + $totalPedido){
            $valido = false;
        } else {
            $valido = true;
        }

        $total_credits = 0;
        if($partner->getPaymentType() == 'Antecipado') {
            $sql = "select SUM(b.actualValue) as value FROM Billsreceive b WHERE b.client = " . $partner->getId() . " AND b.status = 'A' AND b.accountType IN ('Credito', 'Reembolso') ";
            $query = $em->createQuery($sql);
            $Billsreceive = $query->getResult();
            $total_credits += (float)$Billsreceive[0]['value'];

            $sql = "select SUM(b.actualValue) as value FROM Billsreceive b WHERE b.client = " . $partner->getId() . " AND b.status = 'A' AND b.accountType IN ('Débito', 'Venda Bilhete', 'Cancelamento') ";
            $query = $em->createQuery($sql);
            $Billsreceive = $query->getResult();
            $total_credits -= (float)$Billsreceive[0]['value'];

            if($totalLimit + $total_credits >= $usedValue + $totalPedido) {
                $valido = true;
            }
        }

        $response->setDataset(array('valido' => $valido));

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Dados atualizados com sucesso!');
        $response->addMessage($message);
    }

    public function validaCupom(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();
        $conn = Application::getInstance()->getQueryBuilder();

        try {
            $Cupons = $em->getRepository('Cupons')->findOneBy(array(
                'client' => $dados['agencia'],
                'nome' => $dados['nome']
            ));

            if(!$Cupons) {
                $Cupons = $em->getRepository('Cupons')->findOneBy(array(
                    'nome' => $dados['nome']
                ));
            }

            if(!$Cupons) {
                throw new \Exception("Cupom não encontrado!");
            }

            if($Cupons->getUsed() == '1' || $Cupons->getDataExpiracao() < new \DateTime() || $Cupons->getDataInicio() > new \DateTime() || $dados['valor'] < (float)$Cupons->getValorMinimo()) {
                throw new \Exception("Cupom inválido!");
            }

            // pega os dados de aeroportos e cias aéreas
            $aereas = explode(',', $Cupons->getAereas());

            //throw new \Exception(json_encode(empty($Cupons->getAereas())));

            // verifica se a CIA aérea se aplica ao cupom
            // Se a companhia de volta não estiver no array ou se a companhia de volta estiver setada e não
            // estiver no array e as aéreas não estiverem vazias (condição válida para qualquer cia)
            if(!empty($Cupons->getAereas()) && (!in_array($dados['companhiaIda'], $aereas) ||
                (isset($dados['companhiaVolta']) && !in_array($dados['companhiaVolta'], $aereas)))) {
                throw new \Exception("Cupom não é válido para esta CIA Aérea");
            }

            // verifica se os aeroportos envolvidos são nacionais ou internacionais e valida
            if($Cupons->getValidVoos() != null) {
                $aeroportos = $em->getRepository('Airport')->findBy(
                    array('code' => array($dados['idaOrig'], $dados['idaDest']))
                );

                $internacional = false;
                foreach($aeroportos as $aeroporto) {
                    if($aeroporto->getInternational() == 'true')
                        $internacional = true;
                }

                if($Cupons->getValidVoos() == 'N' && $internacional)
                    throw new \Exception("Cupom válido apenas para vôos nacionais");

                if($Cupons->getValidVoos() == 'I' && !$internacional)
                    throw new \Exception("Cupom válido apenas para vôos internacionais");
            }

            if($dados['pagante'] == 'true' && !$Cupons->getPagante()) {
                throw new \Exception("Cupom não é válido para vôos pagantes");
            }

            if($dados['pagante'] == 'false' && !$Cupons->getMilhas()) {
                throw new \Exception("Cupom válido apenas para vôos pagantes");
            }


            $response->setDataset(array('valor' => (float)$Cupons->getValue(), 'tipo_cupom' => $Cupons->getTipoCupom()));
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cupom encontrado!');
            $response->addMessage($message);

        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
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
