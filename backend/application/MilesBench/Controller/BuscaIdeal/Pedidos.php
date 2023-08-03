<?php

namespace MilesBench\Controller\BuscaIdeal;

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
            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Login não encontrado!");
            }

            $Agency = $em->getRepository('Businesspartner')->findOneBy(
                array( 'id' => $BusinessPartner->getClient(), 'partnerType' => 'C' )
            );
            if(!$Agency) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato!");
            }
            if(!$Agency->getMasterClient()) {
                throw new \Exception("Erro ao validar a agencia, por favor entre em contato!");
            }
            if($Agency->getMasterClient()->getId() != 75320) {
                throw new \Exception("Erro ao validar a agencia, por favor entre em contato!");
            }

            $where = ' ';
            $and = ' ';
            $andOnlineFlight = '';
            $whereOnlineFlight = '';
            $whereOnlinePax = '';

            if(isset($dados['filtros'])) {
                $filtros = $dados['filtros'];
                if(isset($filtros['localizador']) && $filtros['localizador'] != '') {
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
            
            if($where != ' ') {
                $sql = "select o FROM OnlineOrder o ".
                " where " . $where;
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

                $locs = array();
                $sql = "select DISTINCT(s.flightLocator) as flightLocator FROM Sale s where s.externalId = '" . $order->getId() . "' ";
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();
                foreach ($Sales as $key => $value) {
                    $locs[] = $value['flightLocator'];
                }

                $orders[] = array(
                    'id' => $order->getId(),
                    'data_pedido' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                    'valor_total' => (float)$order->getTotalCost(),
                    'milhas' => (float)$order->getMilesUsed(),
                    'status' => $order->getStatus(),
                    'trecho' => $trecho,
                    'paxs' => $paxs,
                    'locs' => $locs,
                    'cancelamento' => $cancel
                );
            }

            if($where != ' ') {
                $sql = "select COUNT(o) as quant FROM OnlineOrder o ".
                " where " . $where;
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
        $status = array('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE');

        try {
            if(!isset($dados['pedido'])) {
                throw new \Exception("Numero do pedido deve ser informado!");
            }
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );
            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Login não encontrado!");
            }

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy( array( 'id' => $dados['pedido'] ) );
            if(!$OnlineOrder) {
                throw new \Exception("Pedido não encontrado!");
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
                    'milhas_bebes' => $value->getMilesPerNewborn()
                );
            }

            $sales = array();
            if($OnlineOrder->getStatus() == 'EMITIDO') {
                $Sales = $em->getRepository('Sale')->findBy( array( 'externalId' => $dados['pedido'] ) );
                foreach ($Sales as $sale) {

                    $cancel = false;
                    $refund = false;
                    if($sale->getIssueDate()->format('Y-m-d') == (new \DateTime())->format('Y-m-d') && 
                        ($sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
                        $cancel = true;
                    }

                    if($sale->getIssueDate()->modify('+60 day') >= (new \DateTime()) && 
                        ($sale->getStatus() != 'Reembolso Solicitado' && $sale->getStatus() != 'Reembolso Pagante Solicitado' && $sale->getStatus() != 'Reembolso Confirmado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Reembolso Pendente' && 
                        $sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
                        $refund = true;
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
                    );
                }
            }

            $cancel = true;
            if(!in_array( $OnlineOrder->getStatus(), $status ) || $OnlineOrder->getHasBegun() == 'true') {
                $cancel = false;
            }

            $dataset = array(
                'passageiros' => $paxs,
                'voos' => $flights,
                'dados_vendas' => $sales,
                'cancelamento' => $cancel
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

    public function cancelamento(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );
            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Login não encontrado!");
            }

            if(!isset($dados['pedido'])) {
                throw new \Exception("Numero do pedido deve ser informado!");
            }

            $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $dados['pedido']));

            $status = array('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE');
            if(!in_array( $OnlineOrder->getStatus(), $status ) || $OnlineOrder->getHasBegun() == 'true') {
                throw new \Exception("Pedido não valido para cancelamento!");
            }

            $content = "<br>Ola,<br><br><b>Nova solicitação de cancelamento de OP</b>".
                "OP ID: <b>" . $sale->getId() . "</b><br>".
                "<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'SOLICITAÇÃO - CANCELAMENTO DE OP',
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
            $message->setText('Reembolso confirmado com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}