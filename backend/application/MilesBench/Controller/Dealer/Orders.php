<?php

namespace MilesBench\Controller\Dealer;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Orders {

	public function listOrders(Request $request, Response $response) {
        $dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

		if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }

		$users = '';
		$and = '';

        $query = "select b.id from businesspartner b where b.dealer = ".$UserPartner->getId()." and b.partner_type = 'C'  ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {
            $users = $users.$and."'". $row['id'] ."'";
            $and = ',';
        }

        $whereClause = '';
        $and = ' ';
        if (isset($request->getRow()['_saleDateFrom']) && $request->getRow()['_saleDateFrom'] != '') {
            $_saleDateFrom = new \DateTime($request->getRow()['_saleDateFrom']);
            $whereClause = $whereClause.$and. " o.createdAt >= '".$request->getRow()['_saleDateFrom']."' ";
			$and = ' AND ';
        };

        
		if (isset($request->getRow()['_saleDateTo']) && $request->getRow()['_saleDateTo'] != '') {
            $_saleDateTo = (new \DateTime($request->getRow()['_saleDateTo']))->modify('+1 day');
            $whereClause = $whereClause.$and. " s.createdAt <= '".(new \DateTime($request->getRow()['_saleDateTo']))->modify('+1 day')->format('Y-m-d')."' ";
			$and = ' AND ';
        };

		$sql = "SELECT o FROM OnlineOrder o WHERE o.agenciaId in (".$users.") ".$whereClause." order by o.createdAt DESC";
		$query = $em->createQuery($sql);

        if(isset($request->getRow()['page']) && isset($request->getRow()['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($request->getRow()['page'] - 1) * $request->getRow()['numPerPage']))
                ->setMaxResults($request->getRow()['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }
        $onlineOrder = $query->getResult();

		$orders = array();
		foreach($onlineOrder as $Order){
            $status = 'PENDENTE';
            
            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $firstBoardingDate = '';
            if($Order->getFirstBoardingDate()) {
                $firstBoardingDate = $Order->getFirstBoardingDate()->format('Y-m-d H:i:s');
            }

            $notifications = [];
            $OnlineNotificationStatus = $em->getRepository('OnlineNotificationStatus')->findBy(array('order' => $Order->getId() ));
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

                $notifications[] = array(
                    'id' => $value->getId(),
                    'status' => $value->getStatus(),
                    'user' => $value->getUser(),
                    'issueDate' => $value->getIssueDate()->format('Y-m-d H:i:s'),
                    'descrition' => $descrition
                );
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
                'notifications' => $notifications,
                'notificationcode' => $Order->getNotificationcode()
			);
        }
        
        $dataset = array(
            'orders' => $orders
        );
		$response->setDataset($dataset);
	}

    public function loadDealersOrders(Request $request, Response $response) {
        $dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

		if(isset($request->getRow()['businesspartner'])) {
            $UserPartner = $request->getRow()['businesspartner'];
        }

		$users = '0';
		$and = ',';

        $query = "select b.client_id from clients_dealers b where b.dealer_id = ".$UserPartner->getId()." ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {
            $users = $users.$and."'". $row['client_id'] ."'";
        }

        $query = "select id from businesspartner b where b.dealer = ".$UserPartner->getId()." ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {
            $users = $users.$and."'". $row['id'] ."'";
        }

        $and = ' ';

		$sql = "SELECT o FROM OnlineOrder o WHERE o.agenciaId in (".$users.") order by o.createdAt DESC";
		$query = $em->createQuery($sql);

        // if(isset($request->getRow()['page']) && isset($request->getRow()['numPerPage'])) {
        //     $query = $em->createQuery($sql)
        //         ->setFirstResult((($request->getRow()['page'] - 1) * $request->getRow()['numPerPage']))
        //         ->setMaxResults($request->getRow()['numPerPage']);
        // } else {
        // }
        $query = $em->createQuery($sql);
        $onlineOrder = $query->getResult();

		$orders = array();
		foreach($onlineOrder as $Order){
            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $origin = '';
            $content = '';
            $medium = '';
            if($Order->getUtm()) {
                $data = json_decode($Order->getUtm(), true);
                if(isset($data['utm_source'])) {
                    $origin = $data['utm_source'];
                }
                if(isset($data['utm_content'])) {
                    $content = $data['utm_content'];
                }
                if(isset($data['utm_medium'])) {
                    $medium = $data['utm_medium'];
                }
            }

            $trecho = '';
            $airlines = '';
            $OnlineFlight = $em->getRepository('OnlineFlight')->findBy( array( 'order' => $Order->getId() ) );
            foreach ($OnlineFlight as $keyTrecho => $flight) {
                if($keyTrecho > 0) {
                    $trecho .= '-'.$flight->getAirportCodeTo();
                    $airlines .= '-' . $flight->getAirline();
                } else {
                    $trecho .= $flight->getAirportCodeFrom().'-'.$flight->getAirportCodeTo();
                    $airlines .= $flight->getAirline();
                }
            }

            $firstBoardingDate = '';
            if($Order->getFirstBoardingDate()) {
                $firstBoardingDate = $Order->getFirstBoardingDate()->format('Y-m-d H:i:s');
            }

            $event = '';
            $OnlineNotificationStatus = $em->getRepository('OnlineNotificationStatus')->findBy(array('order' => $Order->getId() ));
            foreach ($OnlineNotificationStatus as $key => $value) {
                if($event != '') {
                    $event .= '<br>';
                }
                if($value->getReason()) {
                    $event .= $value->getReason() . ' - ';
                }
                if($value->getUser() == 'SKYMILHAS') {
                    if($value->getStatus() == '1') {
                    } else if($value->getStatus() == '2') {
                    } else if($value->getStatus() == '3') {
                    } else if($value->getStatus() == '4') {
                    } else if($value->getStatus() == '5') {
                    } else if($value->getStatus() == '6') {
                    } else if($value->getStatus() == '7') {
                        $event .= 'CANCELADA';
                    } else if($value->getStatus() == '8') {
                    } else if($value->getStatus() == '9') {
                    }
                } else {
                    if($value->getStatus() == '1') {
                    } else if($value->getStatus() == '2') {
                        $event .= 'CANCELADA';
                    } else if($value->getStatus() == '3') {
                        $event .= 'RETARIFADA';
                    } else if($value->getStatus() == '4') {
                        $event .= 'DADOS_PASSAGEIRO_INVALIDO';
                    } else if($value->getStatus() == '5') {
                        $event .= 'SITE_CIA_FORA_AR';
                    } else if($value->getStatus() == '6') {
                        $event .= 'INDISPONIVEL';
                    } else if($value->getStatus() == '9') {
                    } else if($value->getStatus() == '8') {
                    }
                }
            }

            $comments = '';
            if($Order->getComments()) {
                $comments = $Order->getComments();
            }

            $dealer = '';
            $cupom = '';
            if($Order->getCupom()) {
                $cupom = $Order->getCupom();
                $dealerCupom = $em->getRepository('CuponsB2c')->findOneBy(array('nome' => $cupom));
                if($dealerCupom) {
                    $dealer = $dealerCupom->getDealer()->getName();
                }
            }

			$orders[] = array(
                'id' => $Order->getId(),
				'client' => $Order->getClientName(),
				'status' => $Order->getStatus(),
				'miles' => (int)$Order->getMilesUsed(),
				'amount' => (float)$Order->getTotalCost(),
				'issue_date' => $Order->getCreatedAt()->format('Y-m-d H:i:s'),
                'origin' => $origin,
                'content' => $content,
                'path' => $trecho,
                'event' => $event,
                'airlines' => $airlines,
                'firstBoardingDate' => $firstBoardingDate,
                'comments' => $comments,
                'medium' => $medium,
                'cupom' => $cupom,
                'dealer' => $dealer
			);
        }
        
        $dataset = array(
            'orders' => $orders
        );
		$response->setDataset($dataset);
	}
}