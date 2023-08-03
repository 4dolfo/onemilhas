<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class dashboard {

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        var_dump($dados);die;

        $em = Application::getInstance()->getEntityManager();

        try {

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Compra finalizada com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadBlockedClients(Request $request, Response $response) {
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $dataset = array();

        $sql = " select b.* from businesspartner b where b.partner_type = 'C' and b.status = 'Bloqueado' ";
        $stmt = $QueryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $dataset[] = array(
                'name' => $row['name']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadBlockedCards(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $sql = "select i FROM InternalCards i where i.status = 'Bloqueado' or (i.cardLimit / i.cardUsed < 1.1)";
        $query = $em->createQuery($sql);
        $InternalCards = $query->getResult();

        $dataset = array();
        foreach($InternalCards as $Cards){
           $Airline = $Cards->getPriorityAirline();
            if($Airline){
                $airlineName = $Airline->getName();
            }else{
                $airlineName = '';
            }

            $dataset[] = array(
                'id' => $Cards->getId(),
                'card_number' => $Cards->getCardNumber(),
                'password' => $Cards->getCardPassword(),
                'card_type' => $Cards->getCardType(),
                'priority_airline' => $airlineName,
                'status' => $Cards->getStatus(),
                'limit' => (float)$Cards->getCardLimit(),
                'used' => (float)$Cards->getCardUsed(),
                'due_date' => $Cards->getDueDate()->format('Y-m-d'),
                'provider_name' => $Cards->getProvider()->getName(),
                'provider_adress' => $Cards->getProvider()->getAdress(),
                'provider_registration' => $Cards->getProvider()->getRegistrationCode()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadTotalBalance(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        if($dados['data']['days'] == '') {
            $dados['data']['days'] = 0;
        }
        $daysAgo = new \DateTime();
        if($dados['data']['days'] > 0)
            $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        $_dateTo = (new \DateTime())->modify('+1 day');
        if(isset($dados['data']['_dateFrom'])) {
            $daysAgo = (new \DateTime($dados['data']['_dateFrom']));
        }
        if(isset($dados['data']['_dateTo'])) {
            $_dateTo = (new \DateTime($dados['data']['_dateTo']))->modify('+1 day');
        }
        
        $sql = "select SUM(b.actualValue) as receive from Billetreceive b where b.status = 'B' AND b.dueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and b.dueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' ";
        $query = $em->createQuery($sql);
        $Billetreceive = $query->getResult();

        $sql = "select SUM(p.actualValue) as pay from Billspay p where p.status = 'B' AND p.dueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and p.dueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' ";
        $query = $em->createQuery($sql);
        $Billspay = $query->getResult();

        $dataset = array();
        foreach($Billetreceive as $receive){
            foreach($Billspay as $pay){
                $dataset[] = array(
                    'value' => (float)$receive['receive'] - (float)$pay['pay']
                );
            }
        }
        $response->setDataset(array_shift($dataset));
    }

    public function loadSumOrderMiles(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        if($dados['data']['days'] == '') {
            $dados['data']['days'] = 0;
        }
        $daysAgo = new \DateTime();
        if($dados['data']['days'] > 0)
            $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        $_dateTo = (new \DateTime())->modify('+1 day');
        if(isset($dados['data']['_dateFrom'])) {
            $daysAgo = (new \DateTime($dados['data']['_dateFrom']));
        }
        if(isset($dados['data']['_dateTo'])) {
            $_dateTo = (new \DateTime($dados['data']['_dateTo']))->modify('+1 day');
        }
        $sql = "select SUM(s.milesOriginal) as miles, SUM(s.amountPaid) as cost, SUM(s.milesUsed) as used FROM Sale s where s.issueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and s.issueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' and s.status='Emitido' ";
        $query = $em->createQuery($sql);
        $onlineFlight = $query->getResult();

        $dataset = array();
        foreach($onlineFlight as $flight){
                $dataset[] = array(
                    'miles' => $flight['miles'],
                    'used' => $flight['used'],
                    'cost' => $flight['cost']
                );
        }
        $response->setDataset(array_shift($dataset));
    }

    public function loadSumPurchaseMiles(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        if($dados['data']['days'] == '') {
            $dados['data']['days'] = 0;
        }
        $daysAgo = new \DateTime();
        if($dados['data']['days'] > 0)
            $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        $_dateTo = (new \DateTime())->modify('+1 day');
        if(isset($dados['data']['_dateFrom'])) {
            $daysAgo = (new \DateTime($dados['data']['_dateFrom']));
        }
        if(isset($dados['data']['_dateTo'])) {
            $_dateTo = (new \DateTime($dados['data']['_dateTo']))->modify('+1 day');
        }
        
        $sql = "select SUM(p.purchaseMiles) as miles, SUM(p.totalCost) as cost FROM Purchase p where p.purchaseDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and p.purchaseDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' ";
        $query = $em->createQuery($sql);
        $onlineFlight = $query->getResult();

        $dataset = array();
        foreach($onlineFlight as $flight){
                $dataset[] = array(
                    'miles' => $flight['miles'],
                    'cost' => $flight['cost']
                );
        }
        $response->setDataset(array_shift($dataset));
    }

    public function loadPurchasesDueDate(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        if(isset($dados['data']['days'])){
            if($dados['data']['days'] == '') {
                $dados['data']['days'] = 0;
            }
            $daysForward = (new \DateTime())->modify('today');
            $daysAgo = (new \DateTime())->modify('today');
            if($dados['data']['days'] > 0){
                $daysForward = (new \DateTime())->modify('today')->modify('+'.$dados['data']['days'].' day');
                $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
            }
        }else{
            $daysForward = (new \DateTime())->modify('today')->modify('+30 day');
            $daysAgo = (new \DateTime())->modify('today')->modify('-30 day');
        }
        
        $sql = "select m FROM Milesbench m where m.contractDueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and m.contractDueDate <= '".$daysForward->format('Y-m-d 23:59:59')."' and m.leftover > 3500 order by m.contractDueDate";
        $query = $em->createQuery($sql);
        $Purchase = $query->getResult();

        $dataset = array();
        foreach($Purchase as $item){
            $Cards = $item->getCards();
            $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $Cards));
            $Airline = $Cards->getAirline();
            $BusinessPartner = $Cards->getBusinesspartner();
            $dataset[] = array(
                'partnerName' => $BusinessPartner->getName(),
                'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                'airline' => $Airline->getName(),
                'dueDate' => $MilesBench->getContractDueDate()->format('Y-m-d'),
                'leftover' => (float)$item->getLeftover()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadSumMilesLeft(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        if(isset($dados['data']['points'])){
            if($dados['data']['points'] == '') {
                $dados['data']['points'] = 0;
            }
        }
        else{
            $dados['data']['points'] = 0;
        }
        
        /*
        $sql = "select SUM(m.leftover) as miles FROM Milesbench m where m.leftover >= ".$dados['data']['points']."";
        $query = $em->createQuery($sql);
        $online = $query->getResult();
        
        $dataset = array();
        foreach($online as $miles){
                $dataset[] = array(
                    'miles' => $miles['miles']
                );
        }*/

        $Milhas = 0;
        $query = "SELECT SUM(m.leftover) as miles FROM milesbench m WHERE m.leftover >= ".$dados['data']['points']."";
        $stmt = $QueryBuilder->query($query);
		while ($row = $stmt->fetch()) {
            $Milhas = (float)$row['miles'];
        }

        $MilhasDesbloqueadas = 0;
        $query = "SELECT SUM(m.leftover) as miles FROM milesbench m inner join cards c on c.id = m.cards_id WHERE c.blocked = 'N' and m.leftover >= ".$dados['data']['points']."";
        $stmt = $QueryBuilder->query($query);
		while ($row = $stmt->fetch()) {
            $MilhasDesbloqueadas = (float)$row['miles'];
        }

        $MilhasBloqueadas = 0;
        $query = "SELECT SUM(m.leftover) as miles FROM milesbench m inner join cards c on c.id = m.cards_id WHERE c.blocked != 'N' and m.leftover >= ".$dados['data']['points']."";
        $stmt = $QueryBuilder->query($query);
		while ($row = $stmt->fetch()) {
            $MilhasBloqueadas = (float)$row['miles'];
        }

        $MilhasNormal = 0;
        $query = "SELECT SUM(m.leftover) as miles FROM milesbench m inner join cards c on c.id = m.cards_id WHERE c.blocked = 'N' and m.leftover >= ".$dados['data']['points']." and (c.card_type not in ('RED', 'BLACK') or c.card_type is null) ";
        $stmt = $QueryBuilder->query($query);
		while ($row = $stmt->fetch()) {
            $MilhasNormal = (float)$row['miles'];
        }

        $MilhasRB = 0;
        $query = "SELECT SUM(m.leftover) as miles FROM milesbench m inner join cards c on c.id = m.cards_id WHERE c.blocked = 'N' and m.leftover >= ".$dados['data']['points']." and c.card_type in ('RED', 'BLACK') ";
        $stmt = $QueryBuilder->query($query);
		while ($row = $stmt->fetch()) {
            $MilhasRB = (float)$row['miles'];
        }

        $dataset[] = array(
            'miles' => $Milhas,
            'miles_desbloqueadas' => $MilhasDesbloqueadas,
            'miles_bloqueadas' => $MilhasBloqueadas,
            'miles_normal' => $MilhasNormal,
            'miles_rb' => $MilhasRB
        );
        $response->setDataset(array_shift($dataset));
    }

    public function loadSumMilesLeftGroupBy(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select MAX(c.airline) as air, SUM(m.leftover) as miles FROM Milesbench m JOIN m.cards c where m.cards not in (212359, 3100, 5290, 9709, 11799, 12226) group by c.airline";
        $query = $em->createQuery($sql);
        $online = $query->getResult();

        $dataset = array();
        foreach($online as $miles){
            $airline = $em->getRepository('Airline')->findOneBy(array('id' => $miles['air']));
            $dataset[] = array(
                'miles' => (int)$miles['miles'],
                'airline' => $airline->getName().' - '.number_format($miles['miles'], 0, ',', '.')
            );
        }
        $response->setDataset($dataset);
    }

    public function loadSumMilesLeftSRM(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select SUM(m.leftover) as miles FROM Milesbench m where m.cards in (212359, 3100, 5290, 9709, 11799, 12226) ";
        $query = $em->createQuery($sql);
        $online = $query->getResult();

        $dataset = array();
        foreach($online as $miles){
            $dataset[] = array(
                'miles' => number_format($miles['miles'], 0, ',', '.').' - MMS VIAGENS'
            );
        }
        $response->setDataset($dataset);
    }

    public function loadBalanceHistory(Request $request, Response $response) {
        $dados = $request->getRow();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $dataset = array();

        for ($i=5; $i >= 0; $i--) { 
            $monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('first day of this month');
            $monthAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('last day of this month')->modify('+1 day');

            $sql = " select SUM(p.total_cost) as purchased FROM purchase p where p.purchase_date BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' ";
            $stmt = $QueryBuilder->query($sql);
            while ($Purchase = $stmt->fetch()) {
                $purchased = $Purchase['purchased'];
            }

            
            $sql = " select SUM(s.amount_paid) as saled FROM sale s where s.issue_date BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' and s.status='Emitido' ";
            $stmt = $QueryBuilder->query($sql);
            while ($Sale = $stmt->fetch()) {
                $saled = $Sale['saled'];
            }

            $dataset[] = array(
                'purchased' => (float)$purchased,
                'saled' => (float)$saled,
                'month' => $monthsAgo->format('Y-m')
            );
        }
        $response->setDataset($dataset);
    }

    public function loadUserHistory(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $em = Application::getInstance()->getEntityManager();
        $dataset = array();
        $keys = [];
        $names = [];

        for ($i=14; $i >= 0; $i--) { 
            $array = array();
            $monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' day');
            $monthAgo = (new \DateTime())->modify('today')->modify('-'.($i-1).' day');

            $query = " select COUNT(s.id) as sales, s.user_id, b.name FROM sale s inner join businesspartner b on b.id = s.user_id where s.issue_date BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' group by s.user_id ";
            $stmt = $QueryBuilder->query($query);
            while ($row = $stmt->fetch()) {

                if(!in_array($row['user_id'], $keys)) {
                    $keys[] = $row['user_id'];
                    $names[] = $row['name'];
                }
                $array[$row['user_id']] = $row['sales'];
            }
            $array['month'] = $monthsAgo->format('Y-m-d');
            $dataset[] = $array;
        }

        foreach ($dataset as $key => $value) {
            foreach ($keys as $key2 => $value2) {
                if(!isset($dataset[$key][$value2])) {
                    $dataset[$key][$value2] = 0;
                } else {
                    $dataset[$key][$value2] = (float)$dataset[$key][$value2];
                }
            }
        }

        $response->setDataset(array( 'values' => $dataset, 'keys' => $keys, 'names' => $names));
    }

    public function loadUserAirlines(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $daysAgo = (new \DateTime())->modify('-'.$dados['days'].' day');

        $sql = "select COUNT(s.id) as sales, MAX(u.id) as name, MAX(a.name) as airline FROM Sale s LEFT JOIN s.user u LEFT JOIN s.airline a where s.issueDate > '".$daysAgo->format('Y-m-d')."' group by s.user, s.airline ";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        foreach ($Sales as $sale) {
            $dataset[] = array(
                'data' => $sale['sales'],
                'airline' => $sale['airline'],
                'user' => $sale['name']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadUserSales(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $daysAgo = (new \DateTime())->modify('-'.$dados['days'].' day');

        $sql = "select COUNT(s.id) as sales, MAX(u.name) as name FROM Sale s LEFT JOIN s.user u where s.issueDate > '".$daysAgo->format('Y-m-d')."' group by s.user ";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        foreach ($Sales as $sale) {
            $dataset[] = array(
                'data' => $sale['sales'],
                'label' => $sale['name']
            );
        }

        $response->setDataset($dataset);
    }

    public function checkAirlinesMilesBench(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        // $Airline = $em->getRepository('Airline')->findAll();
        $dataset = array();

        // foreach ($Airline as $airline) {
        //     $sql = "select SUM(m.leftover) as coun FROM Milesbench m JOIN m.cards c where c.airline = '".$airline->getId()."' and m.leftover >='".$airline->getCardsLimit()."' and m.cards in (212359, 3100, 5290, 9709, 11799, 12226) ";
        //     $query = $em->createQuery($sql);
        //     $Milesbench = $query->getResult();

        //     foreach ($Milesbench as $miles) {
        //         if($miles['coun'] < (float)$airline->getMilesLimit()) {
        //             $dataset[] = array(
        //                 'miles' => $airline->getName()
        //             );
        //         }
        //     }
        // }
        $response->setDataset($dataset);
    }

    public function loadUsersPannel(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $em = Application::getInstance()->getEntityManager();

        $sql = "select COUNT(s.id) as sales, MAX(s.user) as user FROM Sale s where s.user not in ( 0 )  ";
        if(isset($dados['_dateFrom']) && $dados['_dateFrom'] != '') {
            $sql = $sql." and s.issueDate >= '".$dados['_dateFrom']."' ";
        }
        if(isset($dados['_dateTo']) && $dados['_dateTo'] != '') {
            $dateTo = (new \DateTime($dados['_dateTo']))->modify('+1 day');
            $sql = $sql." and s.issueDate < '".$dateTo->format('Y-m-d')."' ";
        }
        if(isset($dados['airlines'])) {
            $airlines = '0';
            foreach ($dados['airlines'] as $key => $airline) {
                if($airline == 'true') {
                    if($key != 'todas') {
                        if(strlen($airlines) > 0) {
                            $airlines .= ',';
                        }
                        $airlines .= $key;
                    } else {
                        $sql2 = "select id from airline where id > 4 ";
                        $stmt2 = $QueryBuilder->query($sql2);
                        while ($row2 = $stmt2->fetch()) {
                            $airlines .= ',' . $row2['id'];
                        }
                    }
                }
            }
            $sql = $sql." and s.airline in (". $airlines .") ";
        }

        $query = $em->createQuery($sql." group by s.user order by sales desc ");
        $Sale = $query->getResult();

        $dataset = array();
        foreach ($Sale as $sale) {
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale['user']));

            $sql = "select s FROM Sale s where s.user = '".$BusinessPartner->getId()."'  ";
            if(isset($dados['_dateFrom']) && $dados['_dateFrom'] != '') {
                $sql = $sql." and s.issueDate >= '".$dados['_dateFrom']."' ";
            }
            if(isset($dados['_dateTo']) && $dados['_dateTo'] != '') {
                $dateTo = (new \DateTime($dados['_dateTo']))->modify('+1 day');
                $sql = $sql." and s.issueDate < '".$dateTo->format('Y-m-d')."' ";
            }
            $query = $em->createQuery($sql);
            $UserSales = $query->getResult();

            $date = new \DateTime('00:00:00');
            $validSales = 0;
            foreach ($UserSales as $user) {
                if($user->getProviderSaleByThird()) {
                    if($user->getSaleByThird() != "Y" && $user->getExternalId() !== NULL && $user->getProviderSaleByThird()->getId() !== 5715 && $user->getProviderSaleByThird()->getId() !== 17105) {
                        $data = explode(':', $user->getProcessingTime());
                        if($data[0]) {
                            $date->modify("+".$data[0]." hour ");
                        }
                        if(isset($data[1])) {
                            $date->modify("+".$data[1]." minutes ");
                        }
                        $validSales++;
                    }
                } else {
                    if($user->getExternalId() !== NULL) {
                        if($user->getProcessingStartDate()) {
                            $diff = $user->getIssueDate()->diff( $user->getProcessingStartDate() );
                            $date->modify("+".$diff->h." hour ");
                            $date->modify("+".$diff->i." minutes ");
                        } else {
                            $data = explode(':', $user->getProcessingTime());
                            if($data[0]) {
                                $date->modify("+".$data[0]." hour ");
                            }
                            if(isset($data[1])) {
                                $date->modify("+".$data[1]." minutes ");
                            }
                        }
                        $validSales++;
                    }
                }
                
            }

            $total = 0;
            $minutes = 0;
            if($validSales > 0) {
                $hours = (new \DateTime('00:00:00'))->diff($date)->h + (new \DateTime('00:00:00'))->diff($date)->days * 24;
                $minutes = (new \DateTime('00:00:00'))->diff($date)->i;
                $total = (($hours * 60) + $minutes )/ $validSales;

                $minutes = floor(($total - floor($total)) * 100);
                $minutes = ($minutes * 60) / 100;
                // $total = (floor($total) + ($minutes));
            }

            $dataset[] = array(
                'name' => $BusinessPartner->getName(),
                'user_id' => $BusinessPartner->getId(),
                'amount' => (float)$sale['sales'],
                'timeAvarage' => floor($total).'m'.floor($minutes).'sec',
                // 'timeAvarage' =>  number_format($total, 2, 'm', ';').'sec',
                'total' => $total
            );
        }
        $response->setDataset($dataset);
    }

    public function findUserProcessingTime(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['filter'])) {
            $filter = $dados['filter'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        if(isset($filter)) {
            
            if(isset($dados['user_id'])) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['user_id']));
            }

            if(isset($filter['_dateFrom']) && $filter['_dateFrom'] != '') {
                $_dateFrom = new \DateTime($filter['_dateFrom']);
            } else {
                $_dateFrom = new \DateTime();
            }
            if(isset($filter['_dateTo']) && $filter['_dateTo'] != '') {
                $_dateTo = (new \DateTime($filter['_dateTo']))->modify('+1 day');
            } else {
                $_dateTo = (new \DateTime())->modify('+1 day');
            }

            $sales = array();
            while ($_dateTo >= $_dateFrom) {

                $sql = "select s FROM Sale s where ";
                if(isset($BusinessPartner)) {
                    $sql .= " s.user = '".$BusinessPartner->getId()."' and ";
                }
                $sql = $sql." s.issueDate between '".$_dateFrom->format('Y-m-d')."' and '". (new \DateTime($_dateFrom->format('Y-m-d')))->modify('+1 day')->format('Y-m-d')."' ";
                $query = $em->createQuery($sql);
                $UserSales = $query->getResult();

                if(count($UserSales) > 0) {
                    $date = new \DateTime('00:00:00');
                    $validSales = 0;
                    foreach ($UserSales as $sale) {
                        if($sale->getProviderSaleByThird()) {
                            if($sale->getSaleByThird() != "Y" && $sale->getExternalId() !== NULL && $sale->getProviderSaleByThird()->getId() !== 5715 && $sale->getProviderSaleByThird()->getId() !== 17105) {
                                $data = explode(':', $sale->getProcessingTime());
                                if($data[0]) {
                                    $date->modify("+".$data[0]." hour ");
                                }
                                if(isset($data[1])) {
                                    $date->modify("+".$data[1]." minutes ");
                                }
                            }
                        } else {
                            if($sale->getExternalId() !== NULL) {
                                if($sale->getProcessingStartDate()) {
                                    $diff = $sale->getIssueDate()->diff( $sale->getProcessingStartDate() );
                                    $date->modify("+".$diff->h." hour ");
                                    $date->modify("+".$diff->i." minutes ");
                                } else {
                                    $data = explode(':', $sale->getProcessingTime());
                                    if($data[0]) {
                                        $date->modify("+".$data[0]." hour ");
                                    }
                                    if(isset($data[1])) {
                                        $date->modify("+".$data[1]." minutes ");
                                    }
                                }
                                $validSales++;
                            }
                        }
                    }

                    if($validSales > 0) {
                        $hours = (new \DateTime('00:00:00'))->diff($date)->h + (new \DateTime('00:00:00'))->diff($date)->days * 24;
                        $minutes = (new \DateTime('00:00:00'))->diff($date)->i;
                        $total = (($hours * 60) + $minutes ) / $validSales;

                        $minutes = floor(($total - floor($total)) * 100);
                        $minutes = ($minutes * 60) / 100;
                        // $total = (floor($total) + ($minutes / 60));
                    }

                    $sales[] = array(
                        'amount' => (float)count($UserSales),
                        'timeAvarage' => floor($total).'m'.floor($minutes).'sec',
                        'total' => $total,
                        'date' => $_dateFrom->format('Y-m-d')
                    );
                }
                $_dateFrom->modify('+1 day');
            }

            $dataset = array(
                'name' => isset($BusinessPartner) ? $BusinessPartner->getName() : '',
                'user_id' => isset($BusinessPartner) ? $BusinessPartner->getId() : '',
                'sale' => $sales
            );
        }
        $response->setDataset($dataset);
    }

    public function findUserSalesDay(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['filter'])) {
            $filter = $dados['filter'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        if(isset($filter)) {
            $_dateFrom = new \DateTime($dados['date'].' 00:00:00');
            $_dateTo = (new \DateTime($dados['date'].' 00:00:00'))->modify('+1 day');

            $sql = "select s FROM Sale s where ";
            if(isset($filter['user_id'])) {
                $sql .= " s.user = '".$filter['user_id']."' and ";
            }
            $sql = $sql." s.issueDate between '".$_dateFrom->format('Y-m-d')."' and '".$_dateTo->format('Y-m-d')."' ";

            $query = $em->createQuery($sql);
            $UserSales = $query->getResult();

            foreach ($UserSales as $sales) {

                $createdDate = '';
                if($sales->getExternalId()) {
                    $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $sales->getExternalId()));
                    if($OnlineOrder) {
                        $createdDate = $OnlineOrder->getBoardingDate()->format('Y-m-d H:i:s');
                    }
                }

                $ProcessingTime = $sales->getProcessingTime();
                if($sales->getProviderSaleByThird()) {
                    if($sales->getProviderSaleByThird()->getId() == 5715 || $sales->getProviderSaleByThird()->getId() == 17105 || $sales->getExternalId() == NULL) {
                        $ProcessingTime = '-';
                    }
                }

                $dataset[] = array(
                    'issueDate' => $sales->getIssueDate()->format('Y-m-d H:i:s'),
                    'createdDate' => $createdDate,
                    'flightLocator' => $sales->getFlightLocator(),
                    'processingTime' => $ProcessingTime,
                    'airline' => $sales->getAirline()->getName()
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function findUserSalesFilter(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['filter'])) {
            $filter = $dados['filter'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        if(isset($filter)) {

            $sql = "select s FROM Sale s where s.user = '".$dados['user_id']."' ";
            $and = ' and ';

            if(isset($filter['_dateFrom'])) {
                $sql = $sql.$and." s.issueDate >= '".$filter['_dateFrom']."' ";
            }

            if(isset($filter['_dateTo'])) {
                $sql = $sql.$and." s.issueDate <= '".(new \DateTime($filter['_dateTo']))->format('Y-m-d')."' ";
            }

            $query = $em->createQuery($sql);
            $UserSales = $query->getResult();

            foreach ($UserSales as $sales) {

                $createdDate = '';
                if($sales->getExternalId()) {
                    $OnlineOrder = $em->getRepository('OnlineOrder')->findOneBy(array('id' => $sales->getExternalId()));
                    if($OnlineOrder) {
                        $createdDate = $OnlineOrder->getBoardingDate()->format('Y-m-d H:i:s');
                    }
                }

                $ProcessingTime = $sales->getProcessingTime();
                if($sales->getProviderSaleByThird()) {
                    if($sales->getProviderSaleByThird()->getId() == 5715 || $sales->getProviderSaleByThird()->getId() == 17105) {
                        $ProcessingTime = '-';
                    }
                }

                $dataset[] = array(
                    'issueDate' => $sales->getIssueDate()->format('Y-m-d H:i:s'),
                    'createdDate' => $createdDate,
                    'flightLocator' => $sales->getFlightLocator(),
                    'processingTime' => $ProcessingTime,
                    'airline' => $sales->getAirline()->getName()
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function loadSumOrderMilesCancels(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $thisMonth = (new \DateTime())->modify('first day of this month');
        $lastMonth = (new \DateTime())->modify('first day of last month');

        $sql = "select SUM(s.milesOriginal) as miles, SUM(s.amountPaid) as cost FROM Sale s where s.issueDate >= '".$lastMonth->format('Y-m-d')."' and s.issueDate <= '".$thisMonth->format('Y-m-d')."' and s.status='Emitido' or (s.status like '%Cancelamento%' and s.amountPaid > 0 and s.issueDate >= '".$lastMonth->format('Y-m-d')."' and s.issueDate <= '".$thisMonth->format('Y-m-d')."' )  or ( s.issueDate >= '".$lastMonth->format('Y-m-d')."' and s.issueDate <= '".$thisMonth->format('Y-m-d')."' and s.status like '%Reembolso%' and ( s.externalId <> '' and s.externalId is not null) )";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $lastsMonth = array(
            'miles' => $Sales[0]['miles']
        );

        $sql = "select SUM(s.milesOriginal) as miles, SUM(s.amountPaid) as cost FROM Sale s where s.issueDate >= '".$thisMonth->format('Y-m-d')."' and s.status='Emitido' or (s.status like '%Cancelamento%' and s.amountPaid > 0 and s.issueDate >= '".$thisMonth->format('Y-m-d')."' ) or ( s.issueDate >= '".$thisMonth->format('Y-m-d')."' and s.status like '%Reembolso%' and ( s.externalId <> '' and s.externalId is not null) )";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $actualMonth = array(
            'miles' => $Sales[0]['miles']
        );

        $dataset = array(
            'lastsMonth' => $lastsMonth,
            'actualMonth' => $actualMonth
        );

        $response->setDataset($dataset);
    }

    public function loadSumDatas(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        if($dados['data']['days'] == '') {
            $dados['data']['days'] = 0;
        }
        $daysAgo = new \DateTime();
        if($dados['data']['days'] > 0)
            $daysAgo = (new \DateTime())->modify('-'.$dados['data']['days'].' day');
        $_dateTo = (new \DateTime())->modify('+1 day');

        if(isset($dados['data']['_dateFrom'])) {
            $daysAgo = (new \DateTime($dados['data']['_dateFrom']));
        }
        if(isset($dados['data']['_dateTo'])) {
            $_dateTo = (new \DateTime($dados['data']['_dateTo']))->modify('+1 day');
        }

        $refund = " or ( s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.issueDate <= '".$_dateTo->format('Y-m-d')."' and s.status like '%Reembolso%' and ( s.externalId <> '' and s.externalId is not null) )";

        $sql = "select SUM(s.milesOriginal) as miles FROM Sale s where ( s.issueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and s.issueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' and ( s.status = 'Emitido' or s.status like '%Remarcação%') ) or (s.issueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and s.issueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' and s.status like '%Cancelamento%' and s.amountPaid > 0) ";
        $query = $em->createQuery($sql.$refund);
        $SalesAndValuedCancels = $query->getResult();

        $sql = "select SUM(s.milesOriginal) as miles FROM Sale s where ( s.issueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and s.issueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' and ( s.status = 'Emitido' or s.status like '%Remarcação%') ) ";
        $query = $em->createQuery($sql.$refund);
        $Sales = $query->getResult();

        $sql = "select SUM(s.milesOriginal) as miles FROM Sale s where ( s.issueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and s.issueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' and ( s.status = 'Emitido' or s.status like '%Remarcação%') ) or (s.issueDate >= '".$daysAgo->format('Y-m-d 00:00:01')."' and s.issueDate <= '".$_dateTo->format('Y-m-d 23:59:59')."' and s.status like '%Cancelamento%' ) ";
        $query = $em->createQuery($sql.$refund);
        $SalesAndAllCancels = $query->getResult();

        $dataset = array(
            'SalesAndValuedCancels' => (int)$SalesAndValuedCancels[0]['miles'],
            'Sales' => (int)$Sales[0]['miles'],
            'SalesAndAllCancels' => (int)$SalesAndAllCancels[0]['miles']
        );
        $response->setDataset($dataset);
    }

    public function loadOrdersPerHour(Request $request, Response $response) {
        $dados = $request->getRow();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $date = new \DateTime();
        $date->modify('-1 week');

        /*$sql = "SELECT HOUR(o.created_at) as value, COUNT(o.id) as amount FROM online_order o where o.created_at >= '". $date->format('Y-m-d') ."' GROUP BY HOUR(o.created_at) ";*/
        $sql = "SELECT HOUR(o.created_at) as value, COUNT(o.id) as amount FROM online_order o where o.created_at >= '". $date->format('Y-m-d') ."' GROUP BY HOUR(o.created_at) ";

        $stmt = $QueryBuilder->query($sql);
       
        $dataset = array();
        while ($row = $stmt->fetch()) {               

            $dataset[] = array(                
                'amount' => $row['amount'],
                'value' => $row['value']
            );
        }

        $response->setDataset($dataset);
    }
}