<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class BillsPay {

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();
        $sql = "select b FROM Billspay b LEFT JOIN b.provider p LEFT JOIN b.cards c WHERE b.accountType <> 'Retorno Reembolso' ";

        $whereClause = ' ';
        $and = ' and ';
        $orderBy = ' ORDER BY b.dueDate';

        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause. "p.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['status']) && !($dados['status'] == '')) {
            $status = 'A';
            if ($dados['status'] == 'Baixada') {
                $status = 'B';
            }
            $whereClause = $whereClause.$and. "b.status = '".$status."' ";
            $and = ' AND ';
        };

        if (isset($dados['account_type']) && !($dados['account_type'] == '')) {
            $whereClause = $whereClause.$and. "b.accountType = '".$dados['account_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['payment_type']) && !($dados['payment_type'] == '')) {
            $whereClause = $whereClause.$and. "b.paymentType = '".$dados['payment_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['credit_card']) && !($dados['credit_card'] == '')) {
            $whereClause = $whereClause.$and. "c.cardNumber = '".$dados['credit_card']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $_dueDateTo = $dados['_dueDateFrom'];
            if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
                $_dueDateTo = $dados['_dueDateTo'];
            }
            $whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
            $and = ' AND ';
        };

        $whereClause = $whereClause.$and." b.accountType <> 'Compra Milhas'";
        if (!($whereClause == ' ')) {
           $sql = $sql.$whereClause;
        };

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." b.id like '%".$dados['searchKeywords']."%' or "
                ." p.name like '%".$dados['searchKeywords']."%' ) ";

                $sql .= $where;
        }

        $orderBy = ' order by b.id ASC ';
        if(isset($dados['order']) && $dados['order'] != '') {
            $orderBy = ' order by ' . $dados['order'] . ' ASC ';
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            $orderBy = ' order by ' . $dados['orderDown'] . ' DESC ';
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                ->setMaxResults($dados['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }

        $billsPays = $query->getResult();
        $billspayArray = array();
        foreach($billsPays as $billsPay){

            $SaleBillspay = $em->getRepository('SaleBillspay')->findOneBy(array('billspay' => $billsPay->getId()));
            if($SaleBillspay) {
                $Sale = $SaleBillspay->getSale();
                $tax = (float)$Sale->getTax();
                $du_tax = (float)$Sale->getDuTax();
                $CreditCard = $Sale->getCardTax();

                $flightLocator = $Sale->getFlightLocator();
                $issueDate = $Sale->getIssueDate()->format('Y-m-d');
                $pax_name = $Sale->getPax()->getName();
                $ticketCode = $Sale->getTicketCode();
            } else {
                $tax = 0;
                $du_tax = 0;

                $flightLocator = '';
                $issueDate = $billsPay->getDueDate()->format('Y-m-d');
                $pax_name = '';
                $ticketCode = '';
            }

            $BusinessPartner = $billsPay->getProvider();
            if ($BusinessPartner) {
                $provider = $BusinessPartner->getName();
                $email = $BusinessPartner->getEmail();
                $phoneNumber = $BusinessPartner->getPhoneNumber();
            } else {
                $provider = '';
                $email = '';
                $phoneNumber = '';
            }


            if($provider == "Rextur Advance") {
                $du_tax = '';
            } else if ($provider == "Loja TAM Ponta Grossa" || $provider == "Loja TAM Contagem") {
            }

            if($billsPay->getAccountType() == "Remarcação") {
                $tax = '';
                $du_tax = '';
            }

            if(isset($CreditCard)){
                $CreditCardNumber = $CreditCard->getCardNumber();
            } else {
                $CreditCardNumber = '';
            }

            if($billsPay->getCards()) {
                $CreditCardNumber = $billsPay->getCards()->getCardNumber();
            }

            $billspayArray[] = array(
                'id' => $billsPay->getId(),
                'status' => $billsPay->getStatus(),
                'provider' => $provider,
                'email' => $email,
                'phoneNumber' => $phoneNumber,
                'account_type' => $billsPay->getAccountType(),
                'description' => $billsPay->getDescription(),
                'due_date' => $billsPay->getDueDate()->format('Y-m-d'),
                'actual_value' => (float)$billsPay->getActualValue(),
                'original_value' => (float)$billsPay->getOriginalValue(),
                'discount' => (float)$billsPay->getDiscount(),
                'payment_type' => $billsPay->getPaymentType(),
                'flightLocator' => $flightLocator,
                'issueDate' => $issueDate,
                'pax_name' => $pax_name,
                'ticketCode' => $ticketCode,
                'tax' => $tax,
                'du_tax' => $du_tax,
                'credit_card' => $CreditCardNumber
            );
        }

        $sql = "select COUNT(b) as quant FROM Billspay b LEFT JOIN b.provider p LEFT JOIN b.cards c WHERE b.accountType <> 'Retorno Reembolso' ";
        $sql.$whereClause;
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'billspay' => $billspayArray,
            'total' => $Quant[0]['quant']
        );

        $response->setDataset($dataset);
    }

    public function loadBillsPayPurchase(Request $request, Response $response) {
        $dados = $request->getRow();
        $requestData = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $where = '';
        if(isset($requestData['searchKeywords']) && $requestData['searchKeywords'] != '') {
            $where .= " and ( "
                ." p.id like '%".$requestData['searchKeywords']."%' or "
                ." b.id like '%".$requestData['searchKeywords']."%' or "
                ." p.name like '%".$requestData['searchKeywords']."%' or "
                ." p.registrationCode like '%".$requestData['searchKeywords']."%' or "
                ." p.adress like '%".$requestData['searchKeywords']."%' or "
                ." p.email like '%".$requestData['searchKeywords']."%' or "
                ." p.phoneNumber like '%".$requestData['searchKeywords']."%' or "
                ." p.phoneNumber2 like '%".$requestData['searchKeywords']."%' or "
                ." p.phoneNumber3 like '%".$requestData['searchKeywords']."%' or "
                ." p.status like '%".$requestData['searchKeywords']."%' or "
                ." p.bank like '%".$requestData['searchKeywords']."%' or "
                ." p.agency like '%".$requestData['searchKeywords']."%' or "
                ." p.account like '%".$requestData['searchKeywords']."%' or "
                ." p.blockReason like '%".$requestData['searchKeywords']."%' or "
                ." p.paymentType like '%".$requestData['searchKeywords']."%' or "
                ." p.description like '%".$requestData['searchKeywords']."%' or "
                ." p.creditAnalysis like '%".$requestData['searchKeywords']."%' or "
                ." p.registrationCodeCheck like '%".$requestData['searchKeywords']."%' or "
                ." p.adressCheck like '%".$requestData['searchKeywords']."%' or "
                ." p.creditDescription like '%".$requestData['searchKeywords']."%' or "
                ." p.companyName like '%".$requestData['searchKeywords']."%' or "
                ." p.phoneNumberAirline like '%".$requestData['searchKeywords']."%' or "
                ." p.celNumberAirline like '%".$requestData['searchKeywords']."%' or "
                ." p.typeSociety like '%".$requestData['searchKeywords']."%' or "
                ." p.nameMother like '%".$requestData['searchKeywords']."%' or "
                ." c.cardNumber like '%".$requestData['searchKeywords']."%' or "
                ." c.cardType like '%".$requestData['searchKeywords']."%' or "
                ." c.token like '%".$requestData['searchKeywords']."%' ) ";
        }

        $sql = "select s FROM PurchaseBillspay s JOIN s.billspay b JOIN s.purchase x LEFT JOIN b.provider p LEFT JOIN x.cards c LEFT JOIN c.airline a ";

        $whereClause = ' WHERE ';
        $and = '';

        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause. "p.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['payment_id']) && !($dados['payment_id'] == '')) {
            $whereClause = $whereClause. "b.id like '%".$dados['payment_id']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['airline']) && !($dados['airline'] == '')) {
            $whereClause = $whereClause. "a.name = '".$dados['airline']."' ";
            $and = ' AND ';
        };

        if (isset($dados['status']) && !($dados['status'] == '')) {
            $status = 'A';
            if ($dados['status'] == 'Baixada') {
                $status = 'B';
            }
            $whereClause = $whereClause.$and. "b.status = '".$status."' ";
            $and = ' AND ';
        };

        if (isset($dados['payment_type']) && !($dados['payment_type'] == '')) {
            $whereClause = $whereClause.$and. "b.paymentType = '".$dados['payment_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $_dueDateTo = (new \DateTime($dados['_dueDateFrom']))->modify('+1 day')->format('Y-m-d');
            if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
                $_dueDateTo = $dados['_dueDateTo'];
            }
            $whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
            $and = ' AND ';
        };

        $whereClause = $whereClause.$and." b.accountType = 'Compra Milhas' ".$where;
        $sql = $sql.$whereClause; 

        // order
        $orderBy = ' ORDER BY b.status ASC, p.name, b.dueDate';
        if(isset($requestData['order']) && $requestData['order'] != '') {
            $orderBy = ' order by b.'.$requestData['order'].' ASC ';
        }
        if(isset($requestData['orderDown']) && $requestData['orderDown'] != '') {
            $orderBy = ' order by b.'.$requestData['orderDown'].' DESC ';
        }
        $sql = $sql.$orderBy;

        //var_dump($sql); die;

        // paginatio
        if(isset($requestData['page']) && isset($requestData['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($requestData['page'] - 1) * $requestData['numPerPage']))
                ->setMaxResults($requestData['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }

        $PurchaseBillspay = $query->getResult();

        $billpays = array();
        foreach($PurchaseBillspay as $PbillsPay){
            
            // $PurchaseBillspay = $em->getRepository('PurchaseBillspay')->findOneBy(array('billspay' => $billsPay->getId()));
            $Purchase = $PbillsPay->getPurchase();
            $billsPay = $PbillsPay->getBillspay();
            $Card = $Purchase->getCards();

            $BusinessPartner = $billsPay->getProvider();
            if ($BusinessPartner) {
                $provider = $BusinessPartner->getName();
                $registrationCode = $BusinessPartner->getRegistrationCode();
                $email = $BusinessPartner->getEmail();
                $phoneNumber = $BusinessPartner->getPhoneNumber();
                $agency = $BusinessPartner->getAgency();
                $account = $BusinessPartner->getAccount();
                $bank = $BusinessPartner->getBank();
                $paymentTypePartner = $BusinessPartner->getPaymentType();
                $description = $BusinessPartner->getDescription();
            } else {
                $provider = '';
                $registrationCode = '';
                $email = '';
                $phoneNumber = '';
                $agency = '';
                $account = '';
                $bank = '';
                $paymentTypePartner = '';
                $description = '';
            }

            $paymentDate = '';
            if($billsPay->getPaymentDate()) {
                $paymentDate = $billsPay->getPaymentDate()->format('Y-m-d H:i:s');
            }

            $issueDate = '';
            if($billsPay->getIssueDate()) {
                $issueDate = $billsPay->getIssueDate()->format('Y-m-d H:i:s');
            }


            $leftOver = 0;
            if($billsPay->getStatus() == 'A') {
                $leftOver = (float)$billsPay->getActualValue() - (float)$billsPay->getAlreadyPaid();
            }

            $due_date = '';
            if($billsPay->getDueDate()) {
                $due_date = $billsPay->getDueDate()->format('Y-m-d');
            }

            $billpays[] = array(
                'id' => $billsPay->getId(),
                'status' => $billsPay->getStatus(),
                'provider' => $provider,
                'registrationCode' => $registrationCode,
                'email' => $email,
                'phoneNumber' => $phoneNumber,
                'account_type' => $billsPay->getAccountType(),
                'description' => $billsPay->getDescription(),
                'due_date' => $due_date,
                'actual_value' => (float)$billsPay->getActualValue(),
                'original_value' => (float)$billsPay->getOriginalValue(),
                'tax' => (float)$billsPay->getTax(),
                'discount' => (float)$billsPay->getDiscount(),
                'payment_type' => $billsPay->getPaymentType(),
                'paymentTypePartner' => $paymentTypePartner,
                'agency' => $agency,
                'account' => $account,
                'bank' => $bank,
                'partnerDescription' => $description,
                'airline' => $Card->getAirline()->getName(),
                'card_type' => $Card->getCardType(),
                'purchased_miles' => number_format($Purchase->getPurchaseMiles(), 0, ',', '.'),
                'alreadyPaid' => (float)$billsPay->getAlreadyPaid(),
                'paymentDate' => $paymentDate,
                'issueDate' => $issueDate,
                'leftOver' => $leftOver
            );
        }

        $sql = "select COUNT(s) as quant FROM PurchaseBillspay s JOIN s.billspay b JOIN s.purchase x LEFT JOIN b.provider p LEFT JOIN x.cards c LEFT JOIN c.airline a ".$whereClause;
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'billpays' => $billpays,
            'total' => $Quant[0]['quant']
        );

        $response->setDataset($dataset);
    }

    public function loadEventsPurchase(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select s FROM PurchaseBillspay s JOIN s.billspay b JOIN s.purchase x LEFT JOIN b.provider p where b.accountType = 'Compra Milhas' ".
            " and b.dueDate >= '" . $dados['year'] . '-' . $this->add_zeros($dados['month'], 2) . "-01' and b.dueDate <= '" . $dados['year'] . '-' . $this->add_zeros( ((int)$dados['month'] + 1 ), 2) . "-01' ";

        $query = $em->createQuery($sql);
        $PurchaseBillspay = $query->getResult();

        $dataset = array();
        foreach ($PurchaseBillspay as $key => $value) {
            $billsPay = $value->getBillspay();
            $BusinessPartner = $billsPay->getProvider();

            $dataset[] = array(
                'start' => $billsPay->getDueDate()->format('Y-m-d H:i:s'),
                'allDay' => true,
                'provider' => $BusinessPartner->getName(),
                'registrationCode' => $BusinessPartner->getRegistrationCode(),
                'email' => $BusinessPartner->getEmail()
            );
        }

        $response->setDataset($dataset);
    }

    public function cancelBillsPay(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();
            $Billspay = $em->getRepository('Billspay')->findOneBy(array('id' => $dados['id']));

            if($Billspay) {

                $PurchaseBillspay = $em->getRepository('PurchaseBillspay')->findOneBy(array('billspay' => $Billspay->getId()));
                $Purchase = $PurchaseBillspay->getPurchase();

                if($Purchase->getStatus() == "M") {
                    $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Purchase->getCards()->getId()));
                    if($Milesbench) {
                        $Milesbench->setLeftover($Milesbench->getLeftover() - $Purchase->getLeftover());
                        $Milesbench->setLastchange(new \Datetime());

                        $em->persist($Milesbench);
                        $em->flush($Milesbench);
                    }
                }

                $em->remove($PurchaseBillspay);
                $em->flush($PurchaseBillspay);

                $em->remove($Billspay);
                $em->flush($Billspay);

                $Purchase->setStatus('C');

                $em->persist($Purchase);
                $em->flush($Purchase);

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("EVENTO - Compra removida do fornecedor: '".$dados['provider']."' referente a ".$dados['purchased_miles']." pontos da CIA ".$dados['airline']);
                $SystemLog->setLogType('EVENT');
                $SystemLog->setBusinesspartner($BusinessPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro removido com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadSumOpenedBillsPayPurchase(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $sql = "select sum(b.actualValue) actualValue FROM Billspay b LEFT JOIN b.provider p ";

        $whereClause = "  where b.status = 'A' ";
        $and = ' AND ';

        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause.$and. "p.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['payment_type']) && !($dados['payment_type'] == '')) {
            $whereClause = $whereClause.$and. "b.paymentType = '".$dados['payment_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $_dueDateTo = $dados['_dueDateFrom'];
            if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
                $_dueDateTo = $dados['_dueDateTo'];
            }
            $whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
            $and = ' AND ';
        };

        $whereClause = $whereClause.$and." b.accountType = 'Compra Milhas'";
        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause; 
        };

        $query = $em->createQuery($sql);
        $billsPays = $query->getResult();

        $dataset = $billsPays[0]['actualValue'];
        $response->setDataset($dataset);
    }

    public function loadSumOpened(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();
        $sql = "select sum(b.actualValue) actualValue FROM Billspay b LEFT JOIN b.provider p ";

        $whereClause = "  where b.status = 'A' ";
        $and = ' AND ';

        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause. "p.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['account_type']) && !($dados['account_type'] == '')) {
            $whereClause = $whereClause.$and. "b.accountType = '".$dados['account_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['payment_type']) && !($dados['payment_type'] == '')) {
            $whereClause = $whereClause.$and. "b.paymentType = '".$dados['payment_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $_dueDateTo = $dados['_dueDateFrom'];
            if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
                $_dueDateTo = $dados['_dueDateTo'];
            }
            $whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
            $and = ' AND ';
        };

        $whereClause = $whereClause.$and." b.accountType <> 'Compra Milhas'";
        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause; 
        };

        $query = $em->createQuery($sql);
        $billsPays = $query->getResult();

        $dataset = $billsPays[0]['actualValue'];
        $response->setDataset($dataset);
    }

    public function loadSumClosed(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();
        $sql = "select sum(b.actualValue) actualValue FROM Billspay b LEFT JOIN b.provider p";

        $whereClause = "  where b.status = 'B' ";
        $and = ' AND ';

        if (isset($dados['providerName']) && !($dados['providerName'] == '')) {
            $whereClause = $whereClause. "p.name like '%".$dados['providerName']."%' ";
            $and = ' AND ';
        };

        if (isset($dados['account_type']) && !($dados['account_type'] == '')) {
            $whereClause = $whereClause.$and. "b.accountType = '".$dados['account_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['payment_type']) && !($dados['payment_type'] == '')) {
            $whereClause = $whereClause.$and. "b.paymentType = '".$dados['payment_type']."' ";
            $and = ' AND ';
        };

        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $_dueDateTo = $dados['_dueDateFrom'];
            if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
                $_dueDateTo = $dados['_dueDateTo'];
            }
            $whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
            $and = ' AND ';
        };

        $whereClause = $whereClause.$and." b.accountType <> 'Compra Milhas'";
        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause; 
        };

        $query = $em->createQuery($sql);
        $billsPays = $query->getResult();

        $dataset = $billsPays[0]['actualValue'];
        $response->setDataset($dataset);
    }

    public function close(Request $request, Response $response) {
        $hash = $request->getRow()['hashId'];
        $dados = $request->getRow()['checkedrows'];
        $filter = $request->getRow()['filter']['_dueDateFrom'];
        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();            
            foreach($dados as $checkedData){
                $billsPay = $em->getRepository('Billspay')->find($checkedData['id']);
                $billsPay->setStatus('B');
                $billsPay->setPaymentDate(new \Datetime($filter));
                $em->persist($billsPay);
                $em->flush($billsPay);

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function generateBill(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();            

            $Billspay = new \Billspay();
            $Billspay->setStatus('A');
            $Billspay->setDescription($dados['description']);
            $Billspay->setOriginalValue($dados['actual_value']);
            $Billspay->setActualValue($dados['actual_value']);
            $Billspay->setTax(0);
            $Billspay->setDiscount(0);
            $Billspay->setAccountType($dados['account_type']);
            $Billspay->setPaymentType($dados['payment_type']);
            $Billspay->setDueDate(new \Datetime($dados['_dueDate']));
            $em->persist($Billspay);
            $em->flush($Billspay);

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Conta cadastrada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();

        try {

            $em->getConnection()->beginTransaction();            
            $billsPay = $em->getRepository('Billspay')->find($dados['id']);
            $billsPay->setActualValue($dados['actual_value']);
            $billsPay->setTax($dados['tax']);
            $billsPay->setDiscount($dados['discount']);

            if(isset($dados['due_date']) && $dados['due_date'] != '') {
                $billsPay->setDueDate(new \Datetime($dados['due_date']));
            }

            if(isset($dados['alreadyPaid']) && $dados['alreadyPaid'] != '') {
                $billsPay->setAlreadyPaid($dados['alreadyPaid']);
            }

            $em->persist($billsPay);
            $em->flush($billsPay);

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixa realizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadBillsPayRefund(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = 0;

        $SaleBillspay = $em->getRepository('SaleBillspay')->findBy(array('sale' => $dados['id']));
        foreach ($SaleBillspay as $payment) {
            if($payment->getBillspay()->getAccountType() == 'Reembolso') {
                $dataset = (float)$payment->getBillspay()->getActualValue();
            }
        }

        $response->setDataset($dataset);
    }

    public function loadChartCalendarBillsPay(Request $request, Response $response) {
        $dataset = array();

        $em = Application::getInstance()->getEntityManager();
        $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');
        $monthAgo = (new \DateTime())->modify('today')->modify('last day of this month')->modify('+1 day');

        while ($monthsAgo < $monthAgo) { 
            $nextDate = clone $monthsAgo;

            $sql = "select SUM(e.amount) as amount FROM Events e where e.start BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$nextDate->modify('+1 day')->format('Y-m-d')."' ";
            $query = $em->createQuery($sql);
            $Value = $query->getResult();

            $sql = "select SUM(f.amount) as amount FROM FixedBillsPay f where f.date BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$nextDate->modify('+1 day')->format('Y-m-d')."' ";
            $query = $em->createQuery($sql);
            $FixedBillsPay = $query->getResult();


            $dataset[] = array(
                'amount' => (float)$Value[0]['amount'] + (float)$FixedBillsPay[0]['amount'],
                'month' => $monthsAgo->format('Y-m-d')
            );

            $monthsAgo = $monthsAgo->modify('+1 day');
        }

        $response->setDataset($dataset);
    }

    public function loadFixedBillsPay(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $FixedBillsPay = $em->getRepository('FixedBillsPay')->findAll();
        foreach ($FixedBillsPay as $fixed) {

            $dataset[] = array(
                'id' => $fixed->getId(),
                'title' => $fixed->getTitle(),
                'amount' => (float)$fixed->getAmount(),
                'date' => $fixed->getDate()->format('Y-m-d H:i:s')
            );
        }

        $response->setDataset($dataset);
    }

    public function saveFixedBillsPay(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {

            if(isset($dados['id'])) {
                $FixedBillsPay = $em->getRepository('FixedBillsPay')->find($dados['id']);
            } else {
                $FixedBillsPay = new \FixedBillsPay();
            }

            $FixedBillsPay->setTitle($dados['title']);
            $FixedBillsPay->setAmount($dados['amount']);
            $FixedBillsPay->setDate(new \DateTime($dados['_date']));

            $em->persist($FixedBillsPay);
            $em->flush($FixedBillsPay);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function checkBillsPayCalendar(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $days = 2;
        if((new \DateTime())->format('l') == "Friday") {
            $days = 4;
        }

        $sql = "select e FROM Events e where e.type = 'BILLSPAY' and ".
            " e.start BETWEEN '".(new \DateTime())->format('Y-m-d')."' and '".(new \DateTime())->modify('+'.$days.' day')->format('Y-m-d')."' ";
        $query = $em->createQuery($sql);
        $Events = $query->getResult();

        foreach ($Events as $event) {
            $dataset[] = array(
                'name' => $event->getTitle()
            );
        }

        $sql = "select e FROM FixedBillsPay e where  ".
            " e.date BETWEEN '".(new \DateTime())->format('Y-m-d')."' and '".(new \DateTime())->modify('+'.$days.' day')->format('Y-m-d')."' ";
        $query = $em->createQuery($sql);
        $FixedBillsPay = $query->getResult();

        foreach ($FixedBillsPay as $event) {
            $dataset[] = array(
                'name' => $event->getTitle()
            );
        }

        $response->setDataset($dataset);
    }

    public function add_zeros($string, $tamanho, $posicao = 'left') {
		$qtd_value = (int) strlen($string);
		
		if($tamanho > 0 && $qtd_value <= $tamanho) {
			
			$result = '';
			$qtd_zeros = $tamanho - $qtd_value;
	
			for ($i = 0; $i < $qtd_zeros; $i++) {
				$result .= '0' ; 
			}
			
			if($posicao == 'left') {
				$result = $result . $string;
			}elseif($posicao == 'right') {
				$result = $string . $result;
			}
			
			return $result;
		}else {
			return false;
		}
	}

    public function loadSynthetic(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $whereClause = ' where ';
        $and = ' ';
        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $whereClause = $whereClause.$and." s.issue_date >= '" . $dados['_dueDateFrom'] . "' ";
            $and = ' AND ';
        };
        if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
            $whereClause = $whereClause.$and." s.issue_date < '" . $dados['_dueDateTo'] . "' ";
            $and = ' AND ';
        }
        if (isset($dados['credit_card']) && !($dados['credit_card'] == '')) {
            $whereClause = $whereClause.$and." i.card_number = '" . $dados['credit_card'] . "' ";
            $and = ' AND ';
        }

        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " SELECT SUM(s.tax) as value, COUNT(s.id) as quant, i.card_number FROM sale s INNER JOIN internal_cards i on i.id = s.card_tax ";
        if($whereClause != ' where ') {
            $sql.= $whereClause;
        }
        $stmt = $QueryBuilder->query($sql . " GROUP BY i.card_number ORDER BY s.issue_date ");

        $dataset = array();
        while ($row = $stmt->fetch()) {
            $row['value'] =  number_format((float)$row['value'], 2, ',', '.');
            $dataset[] = $row;
        }

        $response->setDataset($dataset);
    }

    public function loadAnalytical(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $whereClause = ' where ';
        $and = ' ';
        if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
            $whereClause = $whereClause.$and." s.issue_date >= '" . $dados['_dueDateFrom'] . "' ";
            $and = ' AND ';
        };
        if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
            $whereClause = $whereClause.$and." s.issue_date < '" . $dados['_dueDateTo'] . "' ";
            $and = ' AND ';
        }
        if (isset($dados['credit_card']) && !($dados['credit_card'] == '')) {
            $whereClause = $whereClause.$and." i.card_number = '" . $dados['credit_card'] . "' ";
            $and = ' AND ';
        }

        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " SELECT s.issue_date,  SUM(s.tax) as amount_paid, a.name as airline_name, i.card_number as card_number FROM sale s INNER JOIN internal_cards i on i.id = s.card_tax INNER JOIN airline a on a.id = s.airline_id ";
        if($whereClause != ' where ') {
            $sql.= $whereClause;
        }
        $stmt = $QueryBuilder->query($sql . ' GROUP BY s.flight_locator ORDER BY s.issue_date ');

        $dataset = array();
        while ($row = $stmt->fetch()) {
            $row['amount_paid'] =  number_format((float)$row['amount_paid'], 2, ',', '.');
            $row['issue_date'] = (new \DateTime($row['issue_date']))->format('d/m/Y');
            $dataset[] = $row;
        }

        $response->setDataset($dataset);
    }

    /*
    public function saveClosePayInterval(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
        $apply = false;
        if (isset($dados['apply'])) {
			$apply = $dados['apply'] === 'true'?true:false;
		}
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}
        $today = new \Datetime();
        $em = Application::getInstance()->getEntityManager();

        try {
            $sql = "select b FROM Billspay b WHERE b.dueDate >= '".$dados['baixo']."' and b.dueDate <= '".$dados['alto']."' and b.status = 'A' and b.accountType = 'Compra Milhas' ";
            $query = $em->createQuery($sql);
            $billsPays = $query->getResult();
            
            $em->getConnection()->beginTransaction();
            if($apply){
                foreach($billsPays as $bill){
                    $bill->setStatus('B');
                    $bill->setPaymentDate($today);
                    $em->persist($bill);
                    $em->flush($bill);
                }
                $em->getConnection()->commit();

                $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("EVENTO - Compras entre as datas ".$dados['baixo']." e ".$dados['alto']." baixadas.");
                $SystemLog->setLogType('EVENT');
                $SystemLog->setBusinesspartner($BusinessPartner);
            }
            $dataset = array();
            foreach($billsPays as $bill){
                $dataset[] = array(
                    'id' => $bill->getId(),
                    'status' => $bill->getStatus(),
                    'description' => $bill->getDescription(),
                    'due_date' => $bill->getDueDate(),
                    'account_type' => $bill->getAccountType(),
                );
            }
            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Baixas realizadas com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
    */
}