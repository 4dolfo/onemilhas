<?php

namespace MilesBench\Controller\Incodde;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Pedidos {

    public function load(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $status = array('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE');
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );

            $where = ' ';
            $and = ' ';
            $andOnlineFlight = '';
            $whereOnlineFlight = '';
            $whereOnlinePax = '';

            if(isset($dados['filtros'])) {
                $filtros = $dados['filtros'];
                if(isset($filtros['localizador']) && $filtros['localizador'] != '') {
                    if(!$BusinessPartner) {
                        throw new \Exception("Login não encontrado!");
                    }
                    $where .= $and . " o.id in (select s.externalId from Sale s where s.issuing = '".$BusinessPartner->getId()."' and s.flightLocator = '".$filtros['localizador']."' ) ";
                    $and = ' AND ';
                }

                if(isset($filtros['passageiro']) && $filtros['passageiro'] != '') {
                    $whereOnlinePax = " o.id in ( select y.id from OnlinePax p JOIN p.order y where ( y.clientName = '".$dados['login']."' or y.clientLogin = '".$dados['login']."' ) and ( p.paxName like '%".$filtros['passageiro']."%' or p.paxLastName like '%".$filtros['passageiro']."%' ) ) ";
                }

                if(isset($filtros['data_compra']) && $filtros['data_compra'] != '') {
                    $where .= $and." ( o.clientName = '".$dados['login']."' or o.clientLogin = '".$dados['login']."' ) and o.createdAt >= '".$filtros['data_compra']."' and  o.createdAt <= '".(new \DateTime($filtros['data_compra']))->modify('+1 day')->format('Y-m-d')."' ";
                    $and = ' AND ';
                }

                if(isset($filtros['status']) && $filtros['status'] != '') {
                    $statusPedido = $filtros['status'];
                    if($statusPedido == 'EMISSAO_EM_ANDAMENTO') {
                        $where .= $and . " o.hasBegun = 'true' and o.status IN ('PENDENTE', 'PRIORIDADE') ";
                        $and = ' AND ';

                    } else if($statusPedido == 'EMISSAO_EM_PAUSA') {
                        $where .= $and . " o.status like '%ESPERA%' ";
                        $and = ' AND ';

                    } else if($statusPedido == 'REEMBOLSO_PARCIAL') {
                        if(!$BusinessPartner) {
                            throw new \Exception("Login não encontrado!");
                        }
                        $where .= $and . " o.id in (select s.externalId from Sale s where s.issuing = '".$BusinessPartner->getId()."' and s.status IN ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Confirmado', 'Reembolso Pendente', 'Reembolso CIA') ) ";
                        $and = ' AND ';

                    } else if($statusPedido == 'REEMBOLSO_TOTAL') {
                        if(!$BusinessPartner) {
                            throw new \Exception("Login não encontrado!");
                        }
                        $where .= $and . " o.id in (select s.externalId from Sale s where s.issuing = '".$BusinessPartner->getId()."' and s.status IN ('Reembolso Solicitado', 'Reembolso Pagante Solicitado', 'Reembolso Confirmado', 'Reembolso Pendente', 'Reembolso CIA') ) ";
                        $and = ' AND ';

                    } else if($statusPedido == 'AGUARDANDO_PAGAMENTO') {
                        $where .= $and . " o.status like '%Aguardando Pagamento%' ";
                        $and = ' AND ';

                    } else {
                        $where .= $and . " o.status = '".$statusPedido."' ";
                        $and = ' AND ';
                    }
                }

                if(isset($filtros['data_embarque']) && $filtros['data_embarque'] != '') {
                    $whereOnlineFlight .= $andOnlineFlight . " f.boardingDate >= '".$filtros['data_embarque']."' and  f.boardingDate <= '".(new \DateTime($filtros['data_embarque']))->modify('+1 day')->format('Y-m-d')."' ";
                    $andOnlineFlight = ' AND ';
                }

                if(isset($filtros['sigla_aeroporto_origem']) && $filtros['sigla_aeroporto_origem'] != '' ) {
                    $whereOnlineFlight .= $andOnlineFlight . " f.airportCodeFrom = '".$filtros['sigla_aeroporto_origem']."' ";
                    $andOnlineFlight = ' AND ';
                }

                if(isset($filtros['sigla_aeroporto_destino']) && $filtros['sigla_aeroporto_destino'] != '') {
                    $whereOnlineFlight .= $andOnlineFlight . " f.airportCodeTo = '".$filtros['sigla_aeroporto_destino']."' ";
                    $andOnlineFlight = ' AND ';
                }

                if(isset($filtros['cia']) && $filtros['cia'] != '') {
                    $whereOnlineFlight .= $andOnlineFlight . " f.airline = '".$filtros['cia']."' ";
                    $andOnlineFlight = ' AND ';
                }

                if(isset($filtros['carregarSaldo']) && $filtros['carregarSaldo'] != '') {
                    $where .= $and . " o.dataTransferencia > '". (new \DateTime())->format('Y-m-d') ."' ";
                    $and = ' AND ';
                }
            }

            if($whereOnlineFlight != '') {
                if($where != ' ') {
                    $whereOnlineFlight = " and o.id in ( select x.id from OnlineFlight f JOIN f.order x where ( x.clientName = '".$dados['login']."' or x.clientLogin = '".$dados['login']."' )  and " . $whereOnlineFlight . " ) ";
                } else {
                    $whereOnlineFlight = " o.id in ( select x.id from OnlineFlight f JOIN f.order x where ( x.clientName = '".$dados['login']."' or x.clientLogin = '".$dados['login']."' ) and " . $whereOnlineFlight . " ) ";
                }
            }
            $where = $where . $whereOnlineFlight;

            if($whereOnlinePax != '') {
                if($whereOnlineFlight != '') {
                    $where = $where . ' and ';
                }
                $where = $where . $whereOnlinePax;
            }

            if($dados['is_master'] == true) {
                $where .= " 1=1 or o.agenciaId = ". $dados['agencia'] ." ";
            }

            if($where != ' ') {
                $sql = "select o FROM OnlineOrder o ".
                " where (o.clientName = '".$dados['login']."' or o.clientLogin = '".$dados['login']."') AND " . $where;
            } else {
                $sql = "select o FROM OnlineOrder o where o.clientName = '".$dados['login']."' or o.clientLogin = '".$dados['login']."' ";
            }

            // ordenation of the data
            $orderBy = ' order by o.createdAt DESC ';
            $sql = $sql.$orderBy;

            // pagination
            if(isset($dados['pagina']) && isset($dados['numPorPagina'])) {
                $query = $em->createQuery($sql)
                    ->setFirstResult((($dados['pagina'] - 1) * $dados['numPorPagina']))
                    ->setMaxResults($dados['numPorPagina']);
            } else {
                $query = $em->createQuery($sql);
            }

            $OnlineOrders = $query->getResult();

            $orders = array();
            foreach ($OnlineOrders as $key => $order) {

                $trecho = '';
                $OnlineFlight = $em->getRepository('OnlineFlight')->findBy( array( 'order' => $order->getId() ) );
                foreach ($OnlineFlight as $keyTrecho => $flight) {
                    if($keyTrecho > 0) {
                        $trecho .= '-'.$flight->getAirportCodeTo();
                    } else {
                        $trecho .= $flight->getAirportCodeFrom().'-'.$flight->getAirportCodeTo();
                    }
                }

                $paxs = array();
                $OnlinePax = $em->getRepository('OnlinePax')->findBy( array( 'order' => $order->getId() ) );
                foreach ($OnlinePax as $keyPax => $pax) {
                    $name = $pax->getPaxName();
                    if($pax->getPaxLastName()) {
                        $name .= ' ' . $pax->getPaxLastName();
                    }

                    if($pax->getPaxAgnome()) {
                        $name .= ' ' . $pax->getPaxAgnome();
                    }

                    $paxs[] = $name;
                }

                $cancel = true;
                if(!in_array( $order->getStatus(), $status ) || $order->getHasBegun() == 'true') {
                    $cancel = false;
                }

                $confirm = true;
                if($order->getStatus() != 'EM_ANALISE') {
                    $confirm = false;
                }

                $locs = array();
                // $sql = "select DISTINCT(s.flightLocator) as flightLocator FROM Sale s where s.externalId = '" . $order->getId() . "' ";
                // $query = $em->createQuery($sql);
                // $Sales = $query->getResult();
                // foreach ($Sales as $key => $value) {
                //     $locs[] = $value['flightLocator'];
                // }

                $statusOrder = $order->getStatus();
                if( ($statusOrder == 'PENDENTE' || $statusOrder == 'PRIORIDADE') && $order->getHasBegun() == 'true') {
                    $statusOrder = 'EMISSAO EM ANDAMENTO';
                } else if( strpos($statusOrder, 'ESPERA') !== false || strpos($statusOrder, 'ANT') !== false || strpos($statusOrder, 'BLOQ') !== false ) {
                    $statusOrder = 'EMISSAO EM FILA';
                } else if( $statusOrder == 'PRIORIDADE' || $statusOrder == 'PENDENTE' ) {
                    $statusOrder = 'AGUARDANDO EMISSAO';
                } else if( $statusOrder == 'EM_ANALISE' ) {
                    $statusOrder = 'AGUARDANDO APROVAÇÃO';
                }

                $transferencia = '';
                if($order->getDataTransferencia()) {
                    $transferencia = $order->getDataTransferencia()->format('Y-m-d H:i:s');
                }
                $orders[] = array(
                    'id' => $order->getId(),
                    'notificationcode' => $order->getNotificationcode(),
                    'data_pedido' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                    'valor_total' => (float)$order->getTotalCost(),
                    'total_real' => (float)$order->getTotalreal(),
                    'cupom' => (float)$order->getValorcupom(),
                    'milhas' => (float)$order->getMilesUsed(),
                    'status' => $statusOrder,
                    'trecho' => $trecho,
                    'paxs' => $paxs,
                    'locs' => $locs,
                    'cancelamento' => $cancel,
                    'confirmar' => $confirm,
                    'markup' => (float)$order->getMarckupCliente(),
                    'transferencia' => $transferencia,
                );
            }

            if($where != ' ') {
                $sql = "select COUNT(o) as quant FROM OnlineOrder o ".
                " where (o.clientName = '".$dados['login']."' or o.clientLogin = '".$dados['login']."') AND " . $where;
            } else {
                $sql = "select COUNT(o) as quant FROM OnlineOrder o where o.clientName = '".$dados['login']."' or o.clientLogin = '".$dados['login']."' ";
            }
            $query = $em->createQuery($sql);
            $Quant = $query->getResult();

            $dataset = array(
                'orders' => $orders,
                'total_pedidos' => $Quant[0]['quant']
            );
            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadDetails(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();
        $status = array('EM_ANALISE', 'RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE');

        try {
            if(!isset($dados['pedido'])) {
                throw new \Exception("Numero do pedido deve ser informado!");
            }
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy( array( 'id' => $dados['pedido'] ) );
            if(!$OnlineOrder) {
                throw new \Exception("Pedido não encontrado!");
            }

            if(isset($dados['agencia'])) {
                if($OnlineOrder->getClientLogin() != $dados['login'] && $dados['agencia'] != $OnlineOrder->getAgenciaId()) {
                    throw new \Exception("Erro ao buscar o pedido!");
                }
            }

            $OnlinePax = $em->getRepository('OnlinePax')->findBy( array( 'order' => $dados['pedido'] ) );
            $paxs = array();
            foreach ($OnlinePax as $key => $value) {

                $birthdate = '';
                if($value->getBirthdate()) {
                    $birthdate = $value->getBirthdate()->format('Y-m-d H:i:s');
                }

                $paxs[] = array(
                    'nome' => $value->getPaxName(),
                    'sobrenome' => $value->getPaxLastName(),
                    'agnome' => $value->getPaxAgnome(),
                    'data_nascimento' => $birthdate,
                    'genero' => $value->getGender(),
                    'passageiro_bebe' => $value->getIsNewborn(),
                    'passageiro_crianca' => $value->getIsChild(),
                    'identificacao' => $value->getIdentification()
                );
            }

            $OnlineFlight = $em->getRepository('OnlineFlight')->findBy( array( 'order' => $dados['pedido'] ) );
            $flights = array();
            foreach ($OnlineFlight as $key => $value) {
                $conexao = array();
                $OnlineConnection = $em->getRepository('OnlineConnection')->findBy( array( 'onlineFlight' => $value->getId() ) );
                foreach ($OnlineConnection as $connection) {
                    $conexao[] = array(
                        'NumeroVoo' => $connection->getFlight(),
                        'Duracao' => $connection->getFlightTime(),
                        'Embarque' => $connection->getBoarding(),
                        'Desembarque' => $connection->getLanding(),
                        'Origem' => $connection->getAirportCodeFrom(),
                        'Destino' => $connection->getAirportCodeTo()
                    );
                }

                $valor_trecho = 0;
                foreach ($paxs as $pax) {
                    if($pax['passageiro_bebe'] === "S") {
                        $valor_trecho += (float)$value->getCostPerNewborn();
                    } else if($pax['passageiro_crianca'] === "S") {
                        $valor_trecho += (float)$value->getCostPerChild();
                    } else {
                        $valor_trecho += (float)$value->getCostPerAdult();
                    }
                }

                // if((float)$OnlineOrder->getValorcupom() > 0) {
                //     $valor_trecho -= (float)$OnlineOrder->getValorcupom() / ( count($OnlinePax) + count($OnlineFlight) );
                // }

                $flights[] = array(
                    'cia' => $value->getAirline(),
                    'sigla_aeroporto_origem' => $value->getAirportCodeFrom(),
                    'sigla_aeroporto_destino' => $value->getAirportCodeTo(),
                    'descricao_aeroporto_origem' => $value->getAirportDescriptionFrom(),
                    'descricao_aeroporto_destino' => $value->getAirportDescriptionTo(),
                    'duracao_voo' => $value->getFlightTime(),
                    'data_embarque' => $value->getBoardingDate()->format('Y-m-d H:i:s'),
                    'data_desembarque' => $value->getLandingDate()->format('Y-m-d H:i:s'),
                    'numero_voo' => $value->getFlight(),
                    'classe' => $value->getClass(),
                    'taxa' => $value->getTax(),
                    'conexao' => $conexao,
                    'valor_trecho' => $valor_trecho,
                    'milhas_trecho' => $value->getMilesUsed(),
                    'valor_adultos' => $value->getCostPerAdult(),
                    'valor_criancas' => $value->getCostPerChild(),
                    'valor_bebes' => $value->getCostPerNewborn(),
                    'milhas_adultos' => $value->getMilesPerAdult(),
                    'milhas_criancas' => $value->getMilesPerChild(),
                    'milhas_bebes' => $value->getMilesPerNewborn(),
                    'taxa_conveniencia' => $value->getDuTax()
                );
            }

            $sales = array();
            $billets = array();
            if($OnlineOrder->getStatus() == 'EMITIDO') {
                $Sales = $em->getRepository('Sale')->findBy( array( 'externalId' => $dados['pedido'] ) );
                foreach ($Sales as $sale) {

                    $statusVenda = 'Emitida';
                    $cancel = false;
                    $refund = false;
                    if($sale->getIssueDate()->format('Y-m-d') == (new \DateTime())->format('Y-m-d') && 
                        ($sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
                        $cancel = true;
                    }
                    if( $sale->getStatus() == 'Cancelamento Solicitado' || $sale->getStatus() == 'Cancelamento Efetivado' || $sale->getStatus() == 'Cancelamento Nao Solicitado' || $sale->getStatus() == 'Cancelamento Pendente' ) {
                        $statusVenda = 'Cancelada';
                    }
                    if($sale->getCancellationRequested() == 'true') {
                        $cancel = false;
                    }

                    if($sale->getIssueDate()->modify('+60 day') >= (new \DateTime()) && 
                        $sale->getIssueDate()->format('Y-m-d') != (new \DateTime())->format('Y-m-d') &&
                        ($sale->getStatus() != 'Reembolso Solicitado' && $sale->getStatus() != 'Reembolso Pagante Solicitado' && $sale->getStatus() != 'Reembolso Confirmado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Reembolso Pendente' && 
                        $sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
                        $refund = true;
                    }
                    if( $sale->getStatus() == 'Reembolso Solicitado' || $sale->getStatus() == 'Reembolso Pagante Solicitado' || $sale->getStatus() == 'Reembolso Confirmado' || $sale->getStatus() == 'Cancelamento Nao Solicitado' || $sale->getStatus() == 'Reembolso Pendente' ) {
                        $statusVenda = 'Reembolsada';
                    }
                    if($sale->getRefundRequested() == 'true') {
                        $refund = false;
                    }

                    if($cancel || $sale->getCancellationRequested() == 'true') {
                        $refund = false;
                    }

                    $sales[] = array(
                        'id' => $sale->getId(),
                        'passageiro' => $sale->getPax()->getName(),
                        'localizador' => $sale->getFlightLocator(),
                        'e_ticket' => $sale->getTicketCode(),
                        'possivel_cancelamento' => $cancel,
                        'possivel_reembolso' => $refund,
                        'cia' => $sale->getAirline()->getName(),
                        'sigla_aeroporto_origem' => $sale->getAirportFrom()->getCode(),
                        'sigla_aeroporto_destino' => $sale->getAirportTo()->getCode(),
                        'data_embarque' => $sale->getBoardingDate()->format('Y-m-d H:i:s'),
                        'status' => $statusVenda,
                        'cancelamento_solicitado' => ($sale->getCancellationRequested() == 'true'),
                        'reembolso_solicitado' => ($sale->getRefundRequested() == 'true')
                    );
                }

                $OnlineBillets = $em->getRepository('OnlineBillets')->findBy( array( 'order' => $dados['pedido'] ) );
                foreach ($OnlineBillets as $key => $value) {
                    $billets[] = array(
                        'loc' => $value->getKeyname(),
                        'url' => $value->getUrl()
                    );
                }
            }

            $cancel = true;
            if(!in_array( $OnlineOrder->getStatus(), $status ) || $OnlineOrder->getHasBegun() == 'true') {
                $cancel = false;
            }

            $confirm = true;
            if($OnlineOrder->getStatus() != 'EM_ANALISE') {
                $confirm = false;
            }

            $dataset = array(
                'passageiros' => $paxs,
                'voos' => $flights,
                'dados_vendas' => $sales,
                'billets' => $billets,
                'cancelamento' => $cancel,
                'notificationcode' => $OnlineOrder->getNotificationcode(),
                'confirmar' => $confirm,
                'razao' => $OnlineOrder->getCancelReason(),
                'markup' => (float)$OnlineOrder->getMarckupCliente(),
                'total' => (float)$OnlineOrder->getTotalCost(),
                'total_real' => (float)$OnlineOrder->getTotalreal(),
                'cupom' => (float)$OnlineOrder->getValorcupom(),
            );

            $response->setDataset($dataset);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {

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

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function cancelamento(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            if(!isset($dados['pedido'])) {
                throw new \Exception("Numero do pedido deve ser informado!");
            }

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $dados['pedido']));
            if(!$OnlineOrder) {
                throw new \Exception("Pedido não valido para cancelamento!");
            }

            $status = array('RESERVA', 'EM_ANALISE', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE');
            if(!in_array( $OnlineOrder->getStatus(), $status ) || $OnlineOrder->getHasBegun() == 'true') {
                throw new \Exception("Pedido não valido para cancelamento!");
            }

            $OnlineOrder->setStatus('CANCELADO');
            $em->persist($OnlineOrder);
            $em->flush($OnlineOrder);

            $result = updateOrders();

            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env == 'production') {
                $content = "<br>Ola,<br><br><b>Nova solicitação de cancelamento de OP</b>".
                    "<br>ID: <b>" . $dados['pedido'] . "</b><br>".
                    "<br>Solicitante: <b>" . $dados['login'] . "</b><br><br>".
                    "<br>CIA: <b>" . $OnlineOrder->getAirline() . "</b><br><br>".
                    "<br>Email: <b>" . $dados['email'] . "</b><br><br>".
                    "<br><br>SRM-IT";
                $email1 = 'emissao@onemilhas.com.br';
                $postfields = array(
                    'content' => $content,
                    'partner' => $email1,
                    'subject' => 'SOLICITAÇÃO - CANCELAMENTO DE OP',
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
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Solicitação confirmado com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function confirmacao(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            if(!isset($dados['pedido'])) {
                throw new \Exception("Numero do pedido deve ser informado!");
            }

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $dados['pedido']));
            if($OnlineOrder->getStatus() != 'EM_ANALISE') {
                throw new \Exception("Pedido não valido para confirmação!");
            }

            $OnlineOrder->setStatus('PENDENTE');
            $em->persist($OnlineOrder);
            $em->flush($OnlineOrder);

            $result = updateOrders();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Solicitação confirmado com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function pedidosPendentes(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            $sql = "select COUNT(o) as quant FROM OnlineOrder o where ( o.clientLogin = '".$dados['login']."' or o.agenciaId = '".$dados['agencia']."' ) and o.status = 'EM_ANALISE' ";
            $query = $em->createQuery($sql);
            $Quant = $query->getResult();

            $sql = "select SUM(o.marckupCliente) as quant FROM OnlineOrder o where o.clientLogin = '".$dados['login']."' and o.dataTransferencia > '". (new \DateTime())->format('Y-m-d') ."' ";
            $query = $em->createQuery($sql);
            $Markup = $query->getResult();

            $dataset = array(
                'total_pedidos' => (float)$Quant[0]['quant'],
                'total_saldo' => (float)$Markup[0]['quant']
            );
            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso');
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