<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class miles {

    public function load(Request $request, Response $response) {
        $path_to_file = './MilesBench/json/brazil-airports.json';
        $json = json_decode(file_get_contents($path_to_file), true);

        $dados = $request->getRow();
        $requestData = $request->getRow();
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        // searching params
        $where = '';
        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        $startDate = '2018-08-09';

        $date =  new \DateTime();
        
        if(isset($requestData['searchKeywords']) && $requestData['searchKeywords'] != '') {
            $where .= " AND ( "
            ." b.id like '%".$requestData['searchKeywords']."%' or "
            ." b.name like '%".$requestData['searchKeywords']."%' or "
            ." b.email like '%".$requestData['searchKeywords']."%' or "
            ." b.phone_number like '%".$requestData['searchKeywords']."%' or "
            ." b.registration_code like '%".$requestData['searchKeywords']."%' or "
            ." c.card_number like '%".$requestData['searchKeywords']."%' or "
            ." c.card_type like '%".$requestData['searchKeywords']."%' or "
            ." a.name like '%".$requestData['searchKeywords']."%' or "
            ." c.id like '%".$requestData['searchKeywords']."%' or "
            ." c.token like '%".$requestData['searchKeywords']."%' ) ";
        }
        
        if(isset($dados['airline']) && $dados['airline'] != ''){
            $where .= " AND a.name = '".$dados['airline']."' ";
        }

        if(isset($dados['miles']) && $dados['miles'] != ''){
            $where .= " AND m.leftover >= '".$dados['miles']."' ";
        }

        if(isset($dados['email']) && $dados['email'] != '') {
            $where .= " AND b.email like '%" . $dados['email'] . "%' ";
        }

        if(isset($dados['milesEqual']) && $dados['milesEqual'] != '') {
            $where .= " AND m.leftover = " . $dados['milesEqual'] . " ";
        }

        if(isset($dados['milesSmaller']) && $dados['milesSmaller'] != '') {
            $where .= " AND m.leftover < " . $dados['milesSmaller'] . " ";
        }

        if(isset($dados['providerName']) && $dados['providerName'] != '') {
            $where .= " AND b.name like '%" . $dados['providerName'] . "%' ";
        }

        if(isset($dados['includeZero']) && ( $dados['includeZero'] == false && $dados['miles'] > 0 )) {
            $where .= " AND m.leftover <> 0 ";
        }

        if(isset($dados['withNotes']) && ( $dados['withNotes'] == true || $dados['withNotes'] == 'true' )) {
            $where .= " AND ( c.notes is not null AND c.notes <> '' ) ";
        }

        if(isset($dados['usedPaxMin']) && $dados['usedPaxMin'] != '' && isset($dados['airline']) && $dados['airline'] != '') {
            $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $dados['airline'] ) );
            $where .= " AND ( select COUNT(DISTINCT p.name) from sale s INNER JOIN businesspartner p on p.id = s.pax_id ".
                " where s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.cards_id = c.id ) < ". $dados['usedPaxMin'] ." " ;
        }

        if(isset($dados['usedPaxMax']) && $dados['usedPaxMax'] != '' && isset($dados['airline']) && $dados['airline'] != '') {
            $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $dados['airline'] ) );
            $where .= " AND ( select COUNT(DISTINCT p.name) from sale s INNER JOIN businesspartner p on p.id = s.pax_id ".
                " where s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.cards_id = c.id ) > ". $dados['usedPaxMax'] ." " ;
        }

        $sql = "SELECT m.*, c.is_priority, b.name as provider, b.email as provider_email, b.phone_number as provider_phoneNumber, c.card_number, c.id as cards_id, c.card_type, a.name as airline_name, a.id as airline_id, c.only_inter as only_inter, ".
            " c.blocked, c.token as cards_token, c.notes as cards_notes, c.max_per_pax, c.max_diamond_pax, ".
            "(c.max_diamond_pax - (SELECT count(pax_id) FROM sale WHERE cards_id = c.id AND is_diamond = 1)) as diamond_free".
            " FROM milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id INNER JOIN airline a on a.id = c.airline_id ";
        if(isset($dados['blocked']) || isset($dados['losses'])) {
            if( isset($dados['blocked']) && ( $dados['blocked'] === 'true' || $dados['blocked'] === true)) {
                $sql .= " WHERE c.blocked IN ( 'Y' )  " . $where;
            } else if( isset($dados['losses']) && ( $dados['losses'] === 'true' || $dados['losses'] === true)) {
                $sql .= " WHERE c.blocked IN ( 'L' )  " . $where;
            } else {
                $sql .= " WHERE c.blocked NOT IN ( 'Y',  'L' )  " . $where;
            }
        } else {
            $sql .= " WHERE c.blocked NOT IN ( 'Y',  'L' )  " . $where;
        }

        // order
        $orderBy = '';
        if(isset($requestData['order']) && $requestData['order'] != '') {
            if($requestData['order'] == 'airline') {
                $orderBy = ' order by a.name ASC ';
            } else if($requestData['order'] == 'leftover') {
                $orderBy = ' order by m.leftover ASC ';
            } else if($requestData['order'] == 'contract_due_date' || $requestData['order'] == 'due_date') {
                $orderBy = ' order by m.'.$requestData['order'].' ASC ';
            } else if($requestData['order'] == 'diamond_pax_max') {
                $orderBy = ' order by c.max_diamond_pax ASC ';
            } else if($requestData['order'] == 'diamond_free') {
                $orderBy = ' order by diamond_free ASC ';
            } else {
                $orderBy = ' order by b.'.$requestData['order'].' ASC ';
            }
        }
        if(isset($requestData['orderDown']) && $requestData['orderDown'] != '') {
            if($requestData['orderDown'] == 'airline') {
                $orderBy = ' order by a.name DESC ';
            } else if($requestData['orderDown'] == 'leftover') {
                $orderBy = ' order by m.leftover DESC ';
            } else if($requestData['orderDown'] == 'contract_due_date' || $requestData['orderDown'] == 'due_date') {
                $orderBy = ' order by m.'.$requestData['orderDown'].' DESC ';
            } else if($requestData['orderDown'] == 'diamond_pax_max') {
                $orderBy = ' order by c.max_diamond_pax DESC ';
            } else if($requestData['orderDown'] == 'diamond_free') {
                $orderBy = ' order by diamond_free DESC ';
            } else {
                $orderBy = ' order by b.'.$requestData['orderDown'].' DESC ';
            }
        }
        $sql = $sql.$orderBy;

        if(isset($requestData['page']) && isset($requestData['numPerPage'])) {
            $sql .= " limit " . ( ($requestData['page'] - 1) * $requestData['numPerPage'] ) . ", " . $requestData['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            $stmt = $QueryBuilder->query($sql);
        }

        $milesArray = array();
        // foreach($milesbench as $miles){
        while ($row = $stmt->fetch()) {
            $priority = '4';
            if($row['is_priority'] == 'true') {
                $priority = '-1';
            }elseif (($date->diff( new \DateTime( $row['due_date'] ) )->days <= 20 || $date->diff( new \DateTime( $row['contract_due_date'] ) )->days <= 20) &&  new \DateTime( $row['contract_due_date'] )  >= $date) {
                $priority = '0';
            }elseif (($date->diff( new \DateTime( $row['due_date'] ) )->days > 20 || $date->diff( new \DateTime( $row['contract_due_date'] ) )->days > 20) && ($date->diff( new \DateTime( $row['due_date'] ) )->days <= 40 || $date->diff( new \DateTime( $row['contract_due_date'] ) )->days <= 40) &&  new \DateTime( $row['contract_due_date'] )  >= $date) {
                $priority = '1';
            }

            $datePriority = '';
            if($row['date_priority']) {
                $datePriority = $row['date_priority'];
            }

            $paxUsed = 0;
            $maxPax = 0;
            $diamondPaxMax = 0;
            $diamondPaxUsed = 0;

            if($row['airline_name'] == 'LATAM' || $row['airline_name'] == 'AZUL' || $row['airline_name'] == 'GOL') {
                if($row['airline_name'] == 'LATAM') {
                    $startDate = '2018-08-09'; // deixar contando do dia 09 depois considerar um ano
                    $maxPax = 18;
                } else if($row['airline_name'] == 'AZUL') {
                    $startDate = '2018-12-15'; // 
                    $maxPax = 16;
                } else if($row['airline_name'] == 'GOL') {
                    $startDate = new \DateTime();
                    $startDate = $startDate->format('Y-01-01');
                    $maxPax = 22;
                }

                $diamondPaxMax = (int)$row['max_diamond_pax'];

                if((int)$row['max_per_pax'] != 0) {
                    $maxPax = (float)$row['max_per_pax'];
                }

                $namesUsed = [];

                $sq2 = " select p.name, f.code as origem, t.code as destino, s.is_diamond from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join milesbench m on m.cards_id = s.cards_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " inner join cards c on m.cards_id = c.id ".
                    " inner join airport f on f.id = s.airport_from ".
                    " inner join airport t on t.id = s.airport_to ".
                    " where s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
                    " and s.cards_id = ". $row['cards_id'] ." ".
                    " and w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' ".
                    " group by  SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
                $stmt2 = $QueryBuilder->query($sq2);
                //nacional
                while ($row2 = $stmt2->fetch()) {
                    if( strtoupper(getFirstName($row2['name'], '', '')) == strtoupper(getFirstName($row['provider'], '', '')) &&
                        strtoupper(getLastName($row2['name'], '', '')) == strtoupper(getLastName($row['provider'], '', '')) ) {
                            $valid = true;
                    } else {
                        if(!isset($namesUsed[$row2['name']])) {
                            $paxUsed++;
                            $namesUsed[$row2['name']] = 1;
                        } else {
                            $namesUsed[$row2['name']]++;
                        }
                    }
                }
                $sq2 = " select p.name, f.code as origem, t.code as destino, s.is_diamond from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join milesbench m on m.cards_id = s.cards_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " inner join cards c on m.cards_id = c.id ".
                    " inner join airport f on f.id = s.airport_from ".
                    " inner join airport t on t.id = s.airport_to ".
                    " where s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
                    " and s.cards_id = ". $row['cards_id'] ." ".
                    " and (f.international = 'true' or t.international = 'true') ";
                if($row['airline_id'] == 3){ //AZUL
                    $sq2 .= " and w.is_newborn = 'N' ";
                }
                $sq2 .= " group by  p.name ";

                $stmt2 = $QueryBuilder->query($sq2);
                //inter
                while ($row2 = $stmt2->fetch()) {
                    if( strtoupper($row2['name'] == $row['provider']) &&
                        strtoupper($row2['name'] == $row['provider'])) {
                            $valid = true;
                    } else {
                        if(!isset($namesUsed[$row2['name']])) {
                            $paxUsed++;
                            $namesUsed[$row2['name']] = 1;
                        } else {
                            $namesUsed[$row2['name']]++;
                        }
                    }
                }

                $sqlDiamondUsed = " select p.name, s.is_diamond from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " where s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
                    " and s.cards_id = ". $row['cards_id'] .
                    " and w.is_newborn = 'N' and s.is_diamond = true";

                $stmt2 = $QueryBuilder->query($sqlDiamondUsed);

                while($row2 = $stmt2->fetch()) {
                    if(!isset($namesUsed[$row2['name']])) {
                        $namesUsed[$row2['name']] = 1;
                    } else {
                        $namesUsed[$row2['name']]++;
                    }
                    
                    $diamondPaxUsed++;
                }
            }

            $milesArray[] = array(
                'name' => $row['provider'],
                'email' => $row['provider_email'],
                'phoneNumber' => $row['provider_phoneNumber'],
                'card_number' => $row['card_number'],
                'card_type' => $row['card_type'],
                'airline' => $row['airline_name'],
                'cards_id' => $row['cards_id'],
                'leftover' => (float)$row['leftover'],
                'due_date' => $row['due_date'],
                'contract_due_date' => $row['contract_due_date'],
                'cost_per_thousand' => (float)$row['cost_per_thousand'],
                'id' => $row['id'],
                'priority' => $priority,
                'isPriority' => ($row['is_priority'] == 'true'),
                'blocked' => ($row['blocked'] == 'Y'),
                'token' => $row['cards_token'],
                'lastchange' => (new \DateTime())->format('Y-m-d H:i:s'),
                'milesPriority' => (float)$row['miles_priority'],
                '_datePriority' => $datePriority,
                'notes' => $row['cards_notes'],
                'maxPerPax' => (float)$row['max_per_pax'],
                'onlyInter' => $row['only_inter'],
                'paxUsed' => $paxUsed,
                'maxPax' => $maxPax,
                'diamondPaxUsed' => $diamondPaxUsed,
                'diamondPaxMax' => $diamondPaxMax,
                'diamond_free' => $row['diamond_free']
            );
        }

        if(isset($requestData['searchKeywords']) && $requestData['searchKeywords'] != '') {
            $where = " AND ( "
            ." b.id like '%".$requestData['searchKeywords']."%' or "
            ." b.name like '%".$requestData['searchKeywords']."%' or "
            ." b.email like '%".$requestData['searchKeywords']."%' or "
            ." b.phoneNumber like '%".$requestData['searchKeywords']."%' or "
            ." b.registrationCode like '%".$requestData['searchKeywords']."%' or "
            ." c.cardNumber like '%".$requestData['searchKeywords']."%' or "
            ." c.cardType like '%".$requestData['searchKeywords']."%' or "
            ." a.name like '%".$requestData['searchKeywords']."%' or "
            ." c.id like '%".$requestData['searchKeywords']."%' or "
            ." c.token like '%".$requestData['searchKeywords']."%' ) ";
        }

        if(isset($dados['airline']) && $dados['airline'] != ''){
            $where .= " AND a.name = '".$dados['airline']."' ";
        }

        if(isset($dados['miles']) && $dados['miles'] != ''){
            $where .= " AND m.leftover >= '".$dados['miles']."' ";
        }

        if(isset($dados['email']) && $dados['email'] != '') {
            $where .= " AND b.email like '%" . $dados['email'] . "%' ";
        }

        if(isset($dados['milesEqual']) && $dados['milesEqual'] != '') {
            $where .= " AND m.leftover = " . $dados['milesEqual'] . " ";
        }

        if(isset($dados['milesSmaller']) && $dados['milesSmaller'] != '') {
            $where .= " AND m.leftover < " . $dados['milesSmaller'] . " ";
        }

        if(isset($dados['providerName']) && $dados['providerName'] != '') {
            $where .= " AND b.name like '%" . $dados['providerName'] . "%' ";
        }

        if(isset($dados['includeZero']) && ( $dados['includeZero'] == false && $dados['miles'] > 0 )) {
            $where .= " AND m.leftover <> 0 ";
        }

        if(isset($dados['withNotes']) && ( $dados['withNotes'] == true || $dados['withNotes'] == 'true' )) {
            $where .= " AND ( c.notes is not null AND c.notes <> '' ) ";
        }

        if(isset($dados['usedPaxMin']) && $dados['usedPaxMin'] != '' && isset($dados['airline']) && $dados['airline'] != '') {
            $oneYear = new \DateTime();
            $oneYear->modify('-1 year');
            $startDate = '2018-08-09';

            $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $dados['airline'] ) );
            $where .= " AND ( select COUNT(DISTINCT p.name) from Sale s JOIN s.pax p ".
                " where s.isExtra <> 'true' and s.issueDate >= '" . $startDate . "' and s.issueDate >= '". $oneYear->format('Y-m-d') ."' and s.cards = c.id ) < ". $dados['usedPaxMin'] ." " ;
        }

        if(isset($dados['usedPaxMax']) && $dados['usedPaxMax'] != '' && isset($dados['airline']) && $dados['airline'] != '') {
            $oneYear = new \DateTime();
            $oneYear->modify('-1 year');
            $startDate = '2018-08-09';

            $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $dados['airline'] ) );
            $where .= " AND ( select COUNT(DISTINCT p.name) from Sale s JOIN s.pax p ".
                " where s.isExtra <> 'true' and s.issueDate >= '" . $startDate . "' and s.issueDate >= '". $oneYear->format('Y-m-d') ."' and s.cards = c.id ) > ". $dados['usedPaxMax'] ." " ;
        }

        // getting total of data
        $sql = "select COUNT(m) as quant, SUM(m.leftover) as totalFiltered FROM Milesbench m JOIN m.cards c JOIN c.businesspartner b JOIN c.airline a ";
        if(isset($dados['blocked']) || isset($dados['losses'])) {
            if( isset($dados['blocked']) && ( $dados['blocked'] === 'true' || $dados['blocked'] === true)) {
                $sql .= " WHERE c.blocked IN ( 'Y' ) and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) " . $where;
            } else if( isset($dados['losses']) && ( $dados['losses'] === 'true' || $dados['losses'] === true)) {
                $sql .= " WHERE c.blocked IN ( 'L' ) and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) " . $where;
            } else {
                $sql .= " WHERE c.blocked NOT IN ( 'Y',  'L' ) and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) " . $where;
            }
        } else {
            $sql .= " WHERE c.blocked NOT IN ( 'Y',  'L' ) and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) " . $where;
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $MilesRB = 0;
        if(isset($dados['airline']) && $dados['airline'] != ''){
            if($dados['airline'] == 'LATAM'){
                $sqlRB = "SELECT SUM(m.leftover) as lef FROM milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id INNER JOIN airline a on a.id = c.airline_id ";
                $sqlRB .= " where c.blocked = 'N' and c.airline_id = 1 ";
                $sqlRB .= " and m.leftover >= " . $dados['miles'] . " "; 
                $sqlRB .= " and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and c.card_type in ('RED', 'BLACK') ";
                $stmtRB = $QueryBuilder->query($sqlRB);
                while ($rowRB = $stmtRB->fetch()) {
                    $MilesRB .= $rowRB['lef'];
                }
            }
        }

        $dataset = array(
            'milebench' => $milesArray,
            'total' => (float)$Quant[0]['quant'],
            'totalFiltered' => (float)$Quant[0]['totalFiltered'],
            'totalMilesRB' => $MilesRB
        );

        $response->setDataset($dataset);
    }

    public function saveCardStatus(Request $request, Response $response) {
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

            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
            $Cards->setIsPriority($dados['isPriority']);

            $em->persist($Cards);
            $em->flush($Cards);

            $em->getConnection()->commit();

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

    public function loadByMilesUsed(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        } else if(isset($dados['hashId'])) {
            $hashId = $dados['hashId'];
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
        }
        if(isset($dados['provider'])){
            $provider = $dados['provider'];
        }
        if(isset($dados['cardType'])){
            $cardType = $dados['cardType'];
        }
        if(isset($dados['cardNumber'])){
            $cardNumber = $dados['cardNumber'];
        }
        if(isset($dados['boarding_date'])){
            $boarding_date = $dados['boarding_date'];
        }
        if(isset($dados['from'])){
            $from = $dados['from'];
        }
        if(isset($dados['to'])){
            $to = $dados['to'];
        }
        if(isset($dados['order'])){
            $order = $dados['order'];
        }
        if(isset($dados['pax_quant'])) {
            $pax_quant = $dados['pax_quant'];
        }
        if(isset($dados['paxes'])) {
            $paxes = $dados['paxes'];
        }
        if(isset($dados['searchKeywordsMiles'])) {
            $searchKeywordsMiles = $dados['searchKeywordsMiles'];
        }
        if(isset($boarding_date)) {
            $diff = date_diff(new \DateTime(), new \DateTime($boarding_date));
            $daysToFlight = $diff->d;
        }

        $date =  new \DateTime();
        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        $startDate = '2018-08-09';
        if($dados['airline'] == 'AZUL') {
            $startDate = '2018-12-15';
        } else if($dados['airline'] == 'GOL') {
            $startDate = new \DateTime();
            $startDate = $startDate->format('Y-01-01');
        }

        $biggestMiles = $dados['milesUsed'];
        $validCards = '';
        $where = '';
        $sql = "SELECT m.id as id, prov.name as name, prov.registration_code as card_registrationCode, prov.phone_number as provider_phone, ".
            " c.card_number as card_number, a.name as airline, m.leftover as leftover, m.due_date as due_date, m.contract_due_date as contract_due_date, ".
            " m.cost_per_thousand as cost_per_thousand, c.access_id as access_id, c.access_password as access_password, c.id as cards_id, c.recovery_password as recovery_password, ".
            " c.card_type as card_type, c.token as token, prov.phone_number_airline as phoneNumberAirline, prov.cel_number_airline as celNumberAirline,  ".
            " prov.adress as provider_adress, c.notes as notes, prov.chip_number as chip_number, c.is_priority as is_priority, m.date_priority as date_priority, m.miles_priority as miles_priority, ".
            " if(c.max_per_pax = 0, a.max_per_pax, c.max_per_pax)  as max_per_pax, c.max_diamond_pax, if(c.max_per_pax = 0, a.max_per_pax, c.max_per_pax)  as max_per_pax_total, a.id as airline_id, ".
            " ( ".
                " select COUNT(DISTINCT ps.name) from sale s ".
                " inner join businesspartner ps on ps.id = s.pax_id ".
                " inner join milesbench ms on ms.cards_id = s.cards_id ".
                " inner join online_pax ws on ws.id = s.online_pax_id ".
                " inner join cards cs on ms.cards_id = cs.id ".
                " inner join airport fs on fs.id = s.airport_from ".
                " inner join airport ts on ts.id = s.airport_to ".
                " where s.airline_id = 1 and s.cards_id = m.cards_id ".
                " and s.issue_date >= '".$startDate."' and s.is_extra <> 'true' ".
                " and ws.is_newborn = 'N'  ".
            " ) as paxUsed, ( c.max_diamond_pax - (".
            "SELECT count(pax_id) FROM sale WHERE cards_id = c.id AND is_diamond = 1".
            " )) as diamond_free FROM milesbench m ".
            " inner join cards c on c.id = m.cards_id ".
            " inner join businesspartner prov on prov.id = c.businesspartner_id ".
            " inner join airline a on a.id = c.airline_id ";
        $where =  " where m.leftover >= ".$dados['milesUsed']." and a.name = '".$dados['airline']."' and c.blocked = 'N' AND ( c.user_session in ('', '".$UserPartner->getName()."') ) "; 

        if (isset($dados['cards'])) {
            $where .= " and c.id = ".$dados['cards']." ";
        } else {
            if($dados['airline'] == "AZUL") {
                if(isset($boarding_date) && isset($from) && isset($to)) {
                    $boardingDate = new \DateTime($boarding_date);
                    if(isset($pax_quant)) {
                        $searchMiles = $dados['milesUsed'] * $pax_quant;
                    } else {
                        $searchMiles = $dados['milesUsed'];
                    }
                    /*if(Miles::checkSRMAzulPossibility($dados['airline'], $searchMiles, $boardingDate, $from, $to) === true) {
                        $where .= " AND ( c.id IN (5290, 9709, 11799, 12226) ";
                        if(Miles::checkMMSAzulPossibility($dados['airline'], $searchMiles, $boardingDate, $from, $to) === true) {
                            $where .= " OR c.id IN (212359) ";
                        }
                        $where .= " ) ";
                    } else if(Miles::checkMMSAzulPossibility($dados['airline'], $searchMiles, $boardingDate, $from, $to) === true) {
                        $where .= " AND c.id IN (212359) ";
                    } else {
                        $where .= " AND c.id NOT IN (212359, 3100, 5290, 9709, 11799, 12226) ";
                    }*/
                }

                $idsNot = Miles::checkNotValidCardsPerPax($dados['airline'], isset($pax_quant) ? $pax_quant : 1, $dados['milesUsed'], isset($paxes) ? $paxes : []);
                $validCards = " AND ( c.id NOT IN ($idsNot) ) ";
            } else if($dados['airline'] == 'AVIANCA') {
                if(isset($boarding_date)) {
                    $dayPlusSeven = $date->modify('+2 day');
                    $boarding_date = new \DateTime($boarding_date);
                    if($boarding_date < $dayPlusSeven) {
                        $where .= " and c.id='0' ";
                    }
                }
            } else if($dados['airline'] == 'LATAM') {
                $idsNot = Miles::checkNotValidCardsPerPax($dados['airline'], isset($pax_quant) ? $pax_quant : 1, $dados['milesUsed'], isset($paxes) ? $paxes : []);
                // $idsIn = Miles::checkValidCardsPerPax($dados['airline'], isset($pax_quant) ? $pax_quant : 1, $dados['milesUsed'], isset($paxes) ? $paxes : []);
                // if($dados['milesUsed'] > 1) {
                //     $validCards = " AND ( c.id IN ($idsIn) OR ( ( m.leftover = ".$dados['milesUsed']." OR c.isPriority = 'true' ) AND c.id NOT IN ($idsNot) ) ) ";
                // } else {
                    $validCards = " AND ( c.id NOT IN ($idsNot) ) ";
                // }
            }
        }
        $international = false;
        if(isset($from) && $from != ''){
            $AirportoFrom = $em->getRepository('Airport')->findOneBy(array('code' => $from));
            if($AirportoFrom) {
                if($AirportoFrom->getInternational() == 'true') {
                    $international = true;
                }
            }
        }
        if(isset($to) && $to != ''){
            $AirportoTo = $em->getRepository('Airport')->findOneBy(array('code' => $to));
            if($AirportoTo) {
                if($AirportoTo->getInternational() == 'true') {
                    $international = true;
                }
            }
        }

        if(isset($searchKeywordsMiles)) {
            $where .= " AND prov.name like '%".$searchKeywordsMiles."%' ";
        }

        if($international) {
            if($dados['airline'] == 'LATAM') {
                $where .= " AND c.only_inter in ('true', 'todas') ";
            }
            $where .= " and c.id NOT IN (212359, 3100, 5290, 9709, 11799, 12226) ";
        } else {
            if($dados['airline'] == 'LATAM') {
                $where .= " AND c.only_inter in ('false', 'todas') ";
            }
            // $sql = $sql." OR ( (c.cardType = 'RED' or c.cardType = 'BLACK') and c.blocked = 'N' and m.leftover >= 10000 and a.name = '".$dados['airline']."' ) ";
        }
        if(isset($cardType) && $cardType == 'HighValue') {
            $where .= " AND b.status = 'Aprovado' and c.blocked = 'N' and (c.card_type = 'RED' or c.card_type = 'BLACK') and m.leftover >= 10000  ";
        }
        if(isset($cardNumber) && $cardNumber != '') {
            $where .= " AND c.cardNumber = '".$cardNumber."'  ";
        }
        $where .= $validCards;

        $orderBy = ' ORDER BY m.contract_due_date';
        if(isset($dados['ordenation']) && $dados['ordenation'] != '') {
            if($dados['ordenation'] == 'wave') {
                $orderBy = ' order by (max_per_pax_total - paxUsed) ASC ';
            } else if($dados['ordenation'] == 'media') {
                $orderBy = ' order by (leftover / (max_per_pax_total - paxUsed)) ASC ';
            } else if($dados['ordenation'] == 'chip_number') {
                $orderBy = ' order by prov.chip_number ASC ';
            } else if($dados['ordenation'] == 'diamond_free') {
                $orderBy = ' order by diamond_free ASC';
            } else {
                $orderBy = ' order by m.'.str_replace('-', '', $dados['ordenation']).' ASC ';
            }
        }
        if(isset($dados['ordernationDown']) && $dados['ordernationDown'] != '') {
            if($dados['ordernationDown'] == 'wave') {
                $orderBy = ' order by (max_per_pax_total - paxUsed) DESC ';
            } else if($dados['ordernationDown'] == 'media') {
                $orderBy = ' order by (leftover / (max_per_pax_total - paxUsed)) DESC ';
            } else if($dados['ordernationDown'] == 'chip_number') {
                $orderBy = ' order by prov.chip_number DESC ';
            } else if($dados['ordernationDown'] == 'diamond_free') {
                $orderBy = ' order by diamond_free DESC';
            } else {
                $orderBy = ' order by m.'.str_replace('-', '', $dados['ordernationDown']).' DESC ';
            }
        }
        $sql .= $where;
        $sql .= $orderBy;
        // var_dump($sql);die;
        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            $stmt = $QueryBuilder->query($sql);
        }

        $milesArray = array();
        while ($row = $stmt->fetch()) {
            $priority = '4';
            if($row['is_priority'] == 'true') {
                $priority = '-1';
            } if( $row['date_priority'] ) {
                if( new \DateTime($row['date_priority'])  >= new \DateTime()) {
                    $priority = '-1';
                }
            } elseif ( (float)$row['miles_priority'] >= $dados['milesUsed'] ) {
                $priority = '-1';
            } elseif ((int)$row['leftover'] == (int)$dados['milesUsed']) {
                $priority = '0';
            } elseif (($date->diff( new \DateTime($row['due_date']) )->days <= 20 || $date->diff( new \DateTime($row['contract_due_date']) )->days <= 20) &&  new \DateTime($row['contract_due_date'])  >= $date) {
                $priority = '1';
            } elseif (($date->diff( new \DateTime($row['due_date']) )->days > 20 || $date->diff( new \DateTime($row['contract_due_date']) )->days > 20) && ($date->diff( new \DateTime($row['due_date']) )->days <= 40 || $date->diff( new \DateTime($row['contract_due_date']) )->days <= 40) &&  new \DateTime($row['contract_due_date'])  >= $date) {
                $priority = '2';
            }

            $row['priority'] = $priority;

            $wave = 0;
            $paxUsed = 0;

            $diamondPaxMax = 0;
            $diamondPaxUsed = 0;
            $diamondFree = 0;

            if($row['airline'] == 'LATAM' || $row['airline'] == 'AZUL' || $row['airline'] == 'GOL') {
                if($row['airline'] == 'LATAM') {
                    $startDate = '2018-08-09';
                } else if($row['airline'] == 'AZUL') {
                    $startDate = '2018-12-15';
                } else if($row['airline'] == 'GOL') {
                    $startDate = new \DateTime();
                    $startDate = $startDate->format('Y-01-01');
                }

                $maxPax = $row['max_per_pax'];
                $diamondPaxMax = (int) $row['max_diamond_pax'];

                $namesUsed = [];

                $sq2 = " select p.name, f.code as origem, t.code as destino, s.is_diamond from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join milesbench m on m.cards_id = s.cards_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " inner join cards c on m.cards_id = c.id ".
                    " inner join airport f on f.id = s.airport_from ".
                    " inner join airport t on t.id = s.airport_to ".
                    " where s.airline_id = ". $row['airline_id'] ." and s.is_extra <> 'true' ".
                    " and s.issue_date >= '" . $startDate . "' ".
                    " and w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' ".
                    " and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
                $stmt2 = $QueryBuilder->query($sq2);
                //nacional
                while ($row2 = $stmt2->fetch()) {
                    if( strtoupper(getFirstName($row2['name'], '', '')) == strtoupper(getFirstName($row['name'], '', '')) &&
                        strtoupper(getLastName($row2['name'], '', '')) == strtoupper(getLastName($row['name'], '', '')) ) {
                            $valid = true;
                    } else {
                        if(!isset($namesUsed[$row2['name']])) {
                            $paxUsed++;

                            $namesUsed[$row2['name']] = 1;
                        } else {
                            $namesUsed[$row2['name']]++;
                        }
                    }
                }

                $sq2 = " select p.name, f.code as origem, t.code as destino, s.is_diamond from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join milesbench m on m.cards_id = s.cards_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " inner join cards c on m.cards_id = c.id ".
                    " inner join airport f on f.id = s.airport_from ".
                    " inner join airport t on t.id = s.airport_to ".
                    " where s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
                    " and s.cards_id = ". $row['cards_id'] ." ".
                    " and (f.international = 'true' or t.international = 'true') ".
                    " group by  p.name ";
                $stmt2 = $QueryBuilder->query($sq2);
                //inter
                while ($row2 = $stmt2->fetch()) {
                    if( strtoupper($row2['name'] == $row['name']) &&
                        strtoupper($row2['name'] == $row['name'])) {
                            $valid = true;
                    } else {
                        if(!isset($namesUsed[$row2['name']])) {
                            $paxUsed++;

                            $namesUsed[$row2['name']] = 1;
                        } else {
                            $namesUsed[$row2['name']]++;
                        }
                    }
                }
                $wave = $maxPax - $paxUsed;
                $row['wave'] = $wave;
            }

            $milesArray[] = $row;
        }
        function array_orderby() {
            $args = func_get_args();
            $data = array_shift($args);
            foreach ($args as $n => $field) {
                if (is_string($field)) {
                    $tmp = array();
                    foreach ($data as $key => $row)
                        $tmp[$key] = $row[$field];
                    $args[$n] = $tmp;
                    }
            }
            $args[] = &$data;
            call_user_func_array('array_multisort', $args);
            return array_pop($args);
        }
        if( (!isset($dados['ordenation']) || $dados['ordenation'] == '') && (!isset($dados['ordernationDown']) || $dados['ordernationDown'] == '')) {
            $milesArray = array_orderby($milesArray, 'priority', SORT_ASC, 'contract_due_date', SORT_ASC);
        }
        ini_set('memory_limit', '1024M');
        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql = " select COUNT(m.id) as quant FROM milesbench m ".
                " inner join cards c on c.id = m.cards_id ".
                " inner join businesspartner prov on prov.id = c.businesspartner_id ".
                " inner join airline a on a.id = c.airline_id ".
                $where;
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {
                $dataset = array(
                    'miles' => $milesArray,
                    'total' => (float)$row['quant']
                );
            }
            
        } else {
            $dataset = $milesArray;
        }
        $response->setDataset($dataset);
    }

    public function saveMilesChanges(Request $request, Response $response) {
        $dados = $request->getRow();

        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['purchases'])) {
            $purchases = $dados['purchases'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        //print_r($dados);
        
        $em = Application::getInstance()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Compra finalizada com sucesso');

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('id' => $dados['id']));

            if(new \Datetime($dados['lastchange']) > $Milesbench->getLastchange()) {
                if(isset($purchases)) {
                    foreach ($purchases as $purchase) {
                        $Item = $em->getRepository('Purchase')->findOneBy(array('id' => $purchase['id']));

                        $SystemLog = new \SystemLog();
                        $SystemLog->setIssueDate(new \Datetime());
                        $SystemLog->setDescription("Milhas Alteradas - Milhas alteradas do cartao: ".$dados['card_number']." de: ".$Item->getLeftover()." para ".$purchase['leftover']);
                        $SystemLog->setLogType('PURCHASE');
                        $SystemLog->setBusinesspartner($BusinessPartner);

                        $em->persist($SystemLog);
                        $em->flush($SystemLog);

                        $Item->setLeftover($purchase['leftover']);

                        $em->persist($Item);
                        $em->flush($Item);
                    }
                }

                $SystemLog = new \SystemLog();
                $SystemLog->setIssueDate(new \Datetime());
                $SystemLog->setDescription("Milhas Alteradas - Milhas alteradas do cartao: ".$dados['card_number']." de: ".$Milesbench->getLeftover()." para ".$dados['leftover']);
                $SystemLog->setLogType('MILESBENCH');
                $SystemLog->setBusinesspartner($BusinessPartner);

                $em->persist($SystemLog);
                $em->flush($SystemLog);

                if(isset($dados['_datePriority']) && $dados['_datePriority']) {
                    $Milesbench->setDatePriority( new \DateTime($dados['_datePriority']));
                }
                if(isset($dados['milesPriority']) && $dados['milesPriority']) {
                    $Milesbench->setMilesPriority($dados['milesPriority']);
                }

                $Milesbench->setLeftover($dados['leftover']);
                $Milesbench->setLastChange(new \Datetime());
                $em->persist($Milesbench);
                $em->flush($Milesbench);

                $Cards = $Milesbench->getCards();
                if(isset($dados['notes']) && $dados['notes'] != '') {
                    $Cards->setNotes($dados['notes']);
                } else {
                    $Cards->setNotes('');
                }
                if(isset($dados['maxPerPax']) && $dados['maxPerPax'] != '') {
                    $Cards->setMaxPerPax($dados['maxPerPax']);
                }

                if(isset($dados['diamondPaxMax']) && $dados['diamondPaxMax'] != '') {
                    $Cards->setMaxDiamondPax($dados['diamondPaxMax']);
                }
                if(isset($dados['minimumMiles']) && $dados['minimumMiles'] != '') {
                    $Cards->setMinimumMiles($dados['minimumMiles']);
                }
                if(isset($dados['onlyInter']) && $dados['onlyInter'] != '') {
                    $Cards->setOnlyInter($dados['onlyInter']);
                }
                $em->persist($Cards);
                $em->flush($Cards);

            } else {
                $message->setType(\MilesBench\Message::ERROR);
                $message->setText('Dados desatualizados');
            }

            $em->getConnection()->commit();

            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public static function checkMilesAfterUse($order_id) {
        $em = Application::getInstance()->clearEntity();
        $Sales = $em->getRepository('Sale')->findBy(array('externalId' => $order_id));
        $compras = [];

        foreach ($Sales as $key => $value) {

            $SalePurchases = $em->getRepository('SalePurchases')->findBy(array('sale' => $value->getId()));
            foreach ($SalePurchases as $salePurchase) {

                $purchase = $salePurchase->getPurchase();
                if($purchase->getPaymentMethod() == 'after_use') {
                    if(!isset($compras[$purchase->getId()])) {
                        $compras[$purchase->getId()] = [
                            'pts' => $purchase->getLeftover()
                        ];
                    }
                }
            }
        }

        Miles::enviarFimPontuacao($compras);
    }
    
    public static function enviarFimPontuacao($array) {
        $em = Application::getInstance()->clearEntity();
        
        foreach ($array as $key => $value) {
            $purchase = $em->getRepository('Purchase')->find($key);
            $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $purchase->getCards()->getId()));
            $id = 'Id compra: '.$purchase->getId();
            $PurchaseBillspay = $em->getRepository('PurchaseBillspay')->findOneBy(array('purchase' => $purchase->getId()));
            if($PurchaseBillspay) {
                if($PurchaseBillspay->getBillspay()) {
                    $billspay = $PurchaseBillspay->getBillspay();
                    $id = 'Id pagamento: '.$billspay->getId();
                }
            }
            $email1 = 'onemilhas@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';

            $emailPartner = $email1.';'.$email2;
            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                $emailPartner = $email2;
            }

            $postfields = array(
                'content' => 'Nova Utilização da Compra: <br>'.$id.'<br>Fornecedor: '.$purchase->getCards()->getBusinesspartner()->getName().'<br>Cpf: '.$purchase->getCards()->getBusinesspartner()->getRegistrationCode().'<br>Saldo Compra: '.number_format((float)$value['pts'], 0, ',', '.').'<br>Saldo Estoque: '.number_format((float)$Milesbench->getLeftover(), 0, ',', '.'),
                'partner' => $emailPartner,
                'from' => $email1,
                'subject' => 'USO FICHA - pgto apos uso',
                'type' => '',
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            if((float)$value['pts'] <= 4000) {
                $postfields = array(
                    'content' => 'Nova Utilização da Compra: <br>'.$id.'<br>Fornecedor:'.$purchase->getCards()->getBusinesspartner()->getName().'<br>Cpf:'.$purchase->getCards()->getBusinesspartner()->getRegistrationCode().'<br>Saldo Compra: '.number_format((float)$value['pts'], 0, ',', '.').'<br>Valor a ser pago: '.number_format((float)$purchase->getTotalCost(), 2, ',', '.'),
                    'partner' => $emailPartner,
                    'from' => $email1,
                    'subject' => 'FIM DE USO - agendar pagamento',
                    'type' => '',
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
    }

    public static function removeMiles($em, $cards_id, $miles, $sale_id) {

        $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $cards_id));

        if(isset($sale_id)) {
            $Sale = $em->getRepository('Sale')->findOneBy(array('id' => $sale_id));
        }
        
        //Milesbench
        // $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $cards_id));
        $sqlMiles = "UPDATE Milesbench m SET m.leftover = (m.leftover - $miles) WHERE m.cards = $cards_id ";
        $queryMiles = $em->createQuery($sqlMiles);
        $resultMiles = $queryMiles->getResult();

        $sqlMiles = "UPDATE Milesbench m SET m.lastchange = '".(new \DateTime())->format('Y-m-d H:i:s')."' WHERE m.cards = $cards_id ";
        $queryMiles = $em->createQuery($sqlMiles);
        $resultMiles = $queryMiles->getResult();

        $sqlMiles = " UPDATE Milesbench m set m.milesPriority = CASE WHEN m.milesPriority - $miles < 0 THEN 0 ELSE m.milesPriority - $miles END where m.cards = $cards_id ";
        $queryMiles = $em->createQuery($sqlMiles);
        $resultMiles = $queryMiles->getResult();

        $MileageConsumption = new \MileageConsumption();
        $MileageConsumption->setMilesUsed($miles);
        $MileageConsumption->setIssueDate(new \DateTime());
        $MileageConsumption->setCards($Cards);
        $MileageConsumption->setType('usage');
        if($_SERVER['HTTP_HASHID']) {
            
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_SERVER['HTTP_HASHID']));
            if($UserSession) {
                
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U'));
                if($BusinessPartner) {
                    $MileageConsumption->setBusinesspartner($BusinessPartner);
                }
            }
        }
        if($Sale) {
            $checkSRM = Miles::checkAzulSRM($em, $cards_id, $miles, $Sale->getIssueDate(), $Sale->getBoardingDate(), $Sale->getAirportFrom()->getCode(), $Sale->getAirportTo()->getCode() );
            $checkMMS = Miles::checkAzulMMS($em, $cards_id, $miles, $Sale->getIssueDate(), $Sale->getBoardingDate(), $Sale->getAirportFrom()->getCode(), $Sale->getAirportTo()->getCode() );
            $MileageConsumption->setSale($Sale);
        }
        $em->persist($MileageConsumption);
        $em->flush($MileageConsumption);

        //purchase
        $sql = "select p FROM Purchase p WHERE p.cards = '".$cards_id."' and p.status = 'M' and p.leftover > 0 order by p.id ";
        $query = $em->createQuery($sql);
        $Purchases = $query->getResult();

        $left = $miles;
        foreach ($Purchases as $purchase) {
            if($purchase->getLeftover() < 0) {
                $purchase->setLeftover(0);
            } else if($purchase->getLeftover() > 0 && $left > 0) {

                if($purchase->getLeftover() - $left > 0) {

                    $milesUsed = $left;
                    $sqlPurchase = "UPDATE Purchase p SET p.leftover = (p.leftover - $left) WHERE p.id =  ".$purchase->getId()." ";
			        $queryPurchase = $em->createQuery($sqlPurchase);
			        $resultPurchase = $queryPurchase->getResult();
                    $left = 0;
                } else {

                    $milesUsed = $purchase->getLeftover();
                    $left -= $purchase->getLeftover();
                    $purchase->setLeftover(0);
                }

                if($Sale) {
                    $Sale->setPurchase($purchase);

                    $SalePurchases = new \SalePurchases();
                    $SalePurchases->setPurchase($purchase);
                    $SalePurchases->setSale($Sale);
                    $SalePurchases->setMilesUsed($milesUsed);
                    $em->persist($SalePurchases);
                    $em->flush($SalePurchases);

                    $sql = "select p FROM PurchaseMilesDueDate p WHERE p.purchase = '".$purchase->getId()."' order by p.milesDueDate ";
                    $query = $em->createQuery($sql);
                    $PurchaseMilesDueDate = $query->getResult();

                    if(count($PurchaseMilesDueDate) > 0) {
                        foreach ($PurchaseMilesDueDate as $division) {
                            if($division->getMiles() > 0 && $milesUsed > 0) {

                                if($milesUsed >= $division->getMiles()) {

                                    $MilesDueSaleUse = new \MilesDueSaleUse();
                                    $MilesDueSaleUse->setMilesUsed($division->getMiles());
                                    $MilesDueSaleUse->setMilesDueDate($division);
                                    $MilesDueSaleUse->setSale($Sale);

                                    $em->persist($MilesDueSaleUse);
                                    $em->flush($MilesDueSaleUse);

                                    $milesUsed = $milesUsed - $division->getMiles();
                                    $division->setMiles(0);

                                    $em->persist($division);
                                } else {

                                    $MilesDueSaleUse = new \MilesDueSaleUse();
                                    $MilesDueSaleUse->setMilesUsed($milesUsed);
                                    $MilesDueSaleUse->setMilesDueDate($division);
                                    $MilesDueSaleUse->setSale($Sale);

                                    $em->persist($MilesDueSaleUse);
                                    $em->flush($MilesDueSaleUse);

                                    $division->setMiles($division->getMiles() - $milesUsed);
                                    $milesUsed = 0;

                                    $em->persist($division);
                                }
                            }
                        }
                        $em->flush($PurchaseMilesDueDate);
                    }

                    if($purchase->getPaymentMethod() == 'after_payment') {

                        $cost_per_thousand = 0;
                        if((float)$purchase->getCostPerThousandPurchase() != 0) {
                            $cost_per_thousand = (float)$purchase->getCostPerThousandPurchase();
                        } else {
                            $cost_per_thousand = (float)$purchase->getCostPerThousand();
                        }
                        $totalCost = ($milesUsed / 1000) * $cost_per_thousand;

                        if($purchase->getPaymentBy() == 'boarding_date') {
                            // boarding_date
                            $pay_date = $Sale->getBoardingDate();
                            $pay_date->modify('+'. $purchase->getPaymentDays() .' day');
                        } else {
                            // issue_date
                            $pay_date = $Sale->getIssueDate();
                            $pay_date->modify('+'. $purchase->getPaymentDays() .' day');
                        }

                        Purchase::generateBillsPay($purchase->getId(), $cards_id, $milesUsed, $totalCost, $pay_date);
                    }                    
                }
            }
            $em->persist($purchase);
        }
        $em->flush($Purchases);

        return true;
    }

    public static function addMiles($em, $cards_id, $miles, $sale_id, $type, $BusinessPartner, $value) {

        $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $cards_id));

        // $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $cards_id));
        $sqlMiles = "UPDATE Milesbench m SET m.leftover = (m.leftover + $miles) WHERE m.cards = $cards_id ";
        $queryMiles = $em->createQuery($sqlMiles);
        $resultMiles = $queryMiles->getResult();

        $sqlMiles = "UPDATE Milesbench m SET m.lastchange = '".(new \DateTime())->format('Y-m-d H:i:s')."' WHERE m.cards = $cards_id ";
        $queryMiles = $em->createQuery($sqlMiles);
        $resultMiles = $queryMiles->getResult();
        
        $Purchase = '';
        if(isset($sale_id)) {
            $Sale = $em->getRepository('Sale')->findOneBy(array('id' => $sale_id));
            if($Sale) {
                $checkSRM = Miles::updateAzulSRM($em, $cards_id, $miles, $Sale->getIssueDate(), $Sale->getBoardingDate(), $Sale->getAirportFrom()->getCode(), $Sale->getAirportTo()->getCode() );
                $checkMMS = Miles::updateAzulMMS($em, $cards_id, $miles, $Sale->getIssueDate(), $Sale->getBoardingDate(), $Sale->getAirportFrom()->getCode(), $Sale->getAirportTo()->getCode() );
                if($Sale->getPurchase()) {
                    $Purchase = $Sale->getPurchase();
                }
            }
            $MilesDueSaleUse = $em->getRepository('MilesDueSaleUse')->findBy(array('sale' => $Sale->getId()));
            foreach ($MilesDueSaleUse as $use) {
                $division = $use->getMilesDueDate();
                $division->setMiles($division->getMiles() + $use->getMilesUsed());

                $em->persist($division);
                $em->flush($division);

                $em->remove($use);
                $em->flush($use);
            }
        }

        $MileageConsumption = new \MileageConsumption();
        $MileageConsumption->setMilesUsed($miles * -1);
        $MileageConsumption->setIssueDate(new \DateTime());
        $MileageConsumption->setCards($Cards);
        $MileageConsumption->setType('return');
        if($_SERVER['HTTP_HASHID']) {

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_SERVER['HTTP_HASHID']));
            if($UserSession) {

                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U'));
                if($BusinessPartner) {
                    $MileageConsumption->setBusinesspartner($BusinessPartner);
                }
            }
        }
        if($Sale) {
            $MileageConsumption->setSale($Sale);
        }
        $em->persist($MileageConsumption);
        $em->flush($MileageConsumption);

        $left = $miles;
        if($Purchase != '') {
            if($Purchase->getLeftover() + $left <= $Purchase->getPurchaseMiles()) {

                if(isset($type) && $type == 'REFUND' && isset($value) && $value != 0) {

                    $total_miles = ($Purchase->getLeftOver() + $left);
                    $total_cost = (($Purchase->getLeftOver()/1000) * $Purchase->getCostPerThousand()) + (($left/1000) * ($value / ($left / 1000) ));
                    $Purchase->setCostPerThousand($total_cost / ($total_miles/1000));

                }

                $Purchase->setLeftover($Purchase->getLeftover() + $left);
                $left = 0;

            } else {
                if(isset($type) && $type == 'REFUND' && isset($value) && $value != 0) {

                    $miles = ($Purchase->getPurchaseMiles() - $Purchase->getLeftover());
                    $total_miles = ($Purchase->getLeftOver() + $miles);
                    $total_cost = (($Purchase->getLeftOver()/1000) * $Purchase->getCostPerThousand()) + (($miles/1000) * ($value / ($miles / 1000) ));
                    $Purchase->setCostPerThousand($total_cost / ($total_miles/1000));

                }

                $left = $left - ($Purchase->getPurchaseMiles() - $Purchase->getLeftover());
                $Purchase->setLeftover($Purchase->getPurchaseMiles());

            }
            $em->persist($Purchase);
            $em->flush($Purchase);
        }

        if($left > 0) {
            $sql = "select p FROM Purchase p WHERE p.cards = '".$cards_id."' and p.status = 'M' order by p.id desc ";
            $query = $em->createQuery($sql);
            $Purchases = $query->getResult();

            foreach ($Purchases as $purchase) {
                if($purchase->getLeftover() + $left <= $purchase->getPurchaseMiles()) {

                    if(isset($type) && $type == 'REFUND' && isset($value) && $value != 0) {

                        $total_miles = ($purchase->getLeftOver() + $left);
                        $total_cost = (($purchase->getLeftOver()/1000) * $purchase->getCostPerThousand()) + (($left/1000) * ($value / ($left / 1000) ));
                        $purchase->setCostPerThousand($total_cost / ($total_miles/1000));

                    }
                    $sqlPurchase = "UPDATE Purchase p SET p.leftover = (p.leftover + $left) WHERE p.id = ".$purchase->getId()." ";
			        $queryPurchase = $em->createQuery($sqlPurchase);
			        $resultPurchase = $queryPurchase->getResult();
                    $left = 0;
                } else {

                    if(isset($type) && $type == 'REFUND' && isset($value) && $value != 0) {

                        $miles = ($purchase->getPurchaseMiles() - $purchase->getLeftover());
                        $total_miles = ($purchase->getLeftOver() + $miles);
                        $total_cost = (($purchase->getLeftOver()/1000) * $purchase->getCostPerThousand()) + (($miles/1000) * ($value / ($miles / 1000) ));
                        $purchase->setCostPerThousand($total_cost / ($total_miles/1000));

                    }

                    $left = $left - ($purchase->getPurchaseMiles() - $purchase->getLeftover());
                    $purchase->setLeftover($purchase->getPurchaseMiles());
                }
                $em->persist($purchase);
            }
            $em->flush($Purchases);
        }

        return true;
    }

    public function loadMilesConference(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['filter'])) {
            $filter = $dados['filter'];
        }
        $em = Application::getInstance()->getEntityManager();
        $dataset = array();
        if(isset($dados['filter']) && isset($filter['_dateFrom']) && isset($filter['_dateTo'])) {
            $filter = $dados['filter'];

            $sql = "select DISTINCT(m.cards) as cards from MilesConference m where m.issueDate >= '".(new \DateTime($filter['_dateFrom'].' 00:00:00'))->format('Y-m-d').' 00:00:00'."' and m.issueDate <= '".(new \DateTime($filter['_dateTo'].' 00:00:00'))->format('Y-m-d').' 00:00:00'."' ";
            $query = $em->createQuery($sql);
            $MilesConference = $query->getResult();

            foreach($MilesConference as $conference){

                $sql = "select s from Sale s JOIN s.cards c where s.issueDate >= '".(new \DateTime($filter['_dateFrom'].' 00:00:00'))->format('Y-m-d').' 00:00:00'."' and s.cards = '".$conference['cards']."' and s.issueDate <= '".(new \DateTime($filter['_dateTo'].' 00:00:00'))->format('Y-m-d').' 00:00:00'."' ";
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();

                $CardsSales = array();
                foreach ($Sales as $sale) {

                    $airportFrom = '';
                    if($sale->getAirportFrom()){
                        $airportFrom = $sale->getAirportFrom()->getCode();
                    }

                    $airportTo = '';
                    if($sale->getAirportTo()){
                        $airportTo = $sale->getAirportTo()->getCode();
                    }

                    $category = '';
                    if($sale->getAirline()) {
                        if($sale->getAirline()->getName() == 'AZUL') {
                            $category = 'Competitive';
                            $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
                                array( 'flightFrom' => $airportFrom, 'flightTo' => $airportTo )
                            );
                            if($FlightPathCategory) {
                                $category = $FlightPathCategory->getFlightCategory()->getName();
                            }
                        }
                    }

                    $CardsSales[] = array(
                        'id' => $sale->getId(),
                        'flightLocator' => $sale->getFlightLocator(),
                        'ticket_code' => $sale->getTicketCode(),
                        'status' => $sale->getStatus(),
                        'client' => $sale->getClient()->getName(),
                        'paxName' => $sale->getPax()->getName(),
                        'airportFrom' => $airportFrom,
                        'airportTo' => $airportTo,
                        'milesused' => (int)$sale->getMilesUsed(),
                        'ticket_code' => $sale->getTicketCode(),
                        'boarding_date' => $sale->getBoardingDate()->format('Y-m-d H:i:s'),
                        'flight' => $sale->getFlight(),
                        'flight_category' => $category
                    );
                }

                $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $conference['cards']));
                $Partner = $Cards->getBusinesspartner();

                $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $conference['cards']));

                $sql = "select m from MilesConference m where m.issueDate >= '".(new \DateTime($filter['_dateFrom'].' 00:00:00'))->format('Y-m-d 00:00:00')."' and m.cards = '".$conference['cards']."' and m.issueDate <= '".(new \DateTime($filter['_dateTo'].' 00:00:00'))->format('Y-m-d 00:00:00')."' ";
                $query = $em->createQuery($sql);
                $MilesConferenceQuery = $query->getResult();
                $MilesConferenceQuery = $MilesConferenceQuery[0];

                $dataset[] = array(
                    'cards_id' => $Cards->getId(),
                    'airline' => $Cards->getAirline()->getName(),
                    'providerId' => $Partner->getId(),
                    'providerName' => $Partner->getName(),
                    'leftover' => $Milesbench->getLeftover(),
                    'lastchange' => $Milesbench->getLastchange()->format('Y-m-d H:i:s'),
                    'sales' => $CardsSales,
                    'checked' => ($MilesConferenceQuery->getChecked() == 'true')
                );
            }

        } else {
            $days = 1;
            if((new \DateTime())->format('l') == "Monday") {
                $days = 2;
            }

            $sql = "select DISTINCT(c.id) as cards_id from Sale s JOIN s.cards c where s.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d').' 00:00:00'."' and s.issueDate <= '".(new \DateTime())->format('Y-m-d').' 00:00:00'."' ";
            $query = $em->createQuery($sql);
            $Cards = $query->getResult();

            $dataset = array();
            foreach($Cards as $card){
                $CardsSales = array();

                $sql = "select s from Sale s JOIN s.cards c where s.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d').' 00:00:00'."' and s.cards = '".$card['cards_id']."' and s.issueDate <= '".(new \DateTime())->format('Y-m-d').' 00:00:00'."' ";
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();

                foreach ($Sales as $sale) {

                    $airportFrom = '';
                    if($sale->getAirportFrom()){
                        $airportFrom = $sale->getAirportFrom()->getCode();
                    }

                    $airportTo = '';
                    if($sale->getAirportTo()){
                        $airportTo = $sale->getAirportTo()->getCode();
                    }

                    $category = '';
                    if($sale->getAirline()) {
                        if($sale->getAirline()->getName() == 'AZUL') {
                            $category = 'Competitive';
                            $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
                                array( 'flightFrom' => $airportFrom, 'flightTo' => $airportTo )
                            );
                            if($FlightPathCategory) {
                                $category = $FlightPathCategory->getFlightCategory()->getName();
                            }
                        }
                    }

                    $CardsSales[] = array(
                        'id' => $sale->getId(),
                        'flightLocator' => $sale->getFlightLocator(),
                        'ticket_code' => $sale->getTicketCode(),
                        'status' => $sale->getStatus(),
                        'client' => $sale->getClient()->getName(),
                        'paxName' => $sale->getPax()->getName(),
                        'airportFrom' => $airportFrom,
                        'airportTo' => $airportTo,
                        'milesused' => (int)$sale->getMilesUsed(),
                        'ticket_code' => $sale->getTicketCode(),
                        'boarding_date' => $sale->getBoardingDate()->format('Y-m-d H:i:s'),
                        'flight' => $sale->getFlight(),
                        'flight_category' => $category
                    );
                }

                $sql = "select m from MilesConference m where m.issueDate < '".(new \DateTime())->format('Y-m-d').' 00:00:00'."' and m.cards = '".$card['cards_id']."' and m.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d').' 00:00:00'."' ";
                $query = $em->createQuery($sql);
                $MilesConference = $query->getResult();
                if(count($MilesConference) == 0) {

                    $postfields = array(
                        'hashId' =>    "9901401e7398b65912d5cae4364da460"
                    );

                    $HostServer = getenv('HostServer') ? getenv('HostServer') : '52.70.119.195';
                    $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://'.$HostServer.'/'.$DirServer.'/backend/application/index.php?rota=/checkMilesToConference');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);

                    $sql = "select m from MilesConference m where m.issueDate < '".(new \DateTime())->format('Y-m-d').' 00:00:00'."' and m.cards = '".$card['cards_id']."' and m.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d').' 00:00:00'."' ";
                    $query = $em->createQuery($sql);
                    $MilesConference = $query->getResult();
                    $MilesConference = $MilesConference[0];
                } else {
                    
                    $MilesConference = $MilesConference[0];
                }

                $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $card['cards_id']));
                $Partner = $Cards->getBusinesspartner();

                $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $card['cards_id']));

                $dataset[] = array(
                    'cards_id' => $Cards->getId(),
                    'airline' => $Cards->getAirline()->getName(),
                    'providerId' => $Partner->getId(),
                    'providerName' => $Partner->getName(),
                    'leftover' => $Milesbench->getLeftover(),
                    'lastchange' => $Milesbench->getLastchange()->format('Y-m-d H:i:s'),
                    'sales' => $CardsSales,
                    'checked' => ($MilesConference->getChecked() == 'true')
                );
            }
        }
        $response->setDataset($dataset);
    }

    public function saveMilesCheck(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        $em = Application::getInstance()->getEntityManager();
        if(isset($request->getRow()['filter'])){
            $filter = $request->getRow()['filter'];
            try {
                $em->getConnection()->beginTransaction();

                $sql = "select m from MilesConference m where m.issueDate >= '".(new \DateTime($filter['_dateFrom'].' 00:00:00'))->format('Y-m-d 00:00:00')."' and m.cards = '".$dados['cards_id']."' and m.issueDate <= '".(new \DateTime($filter['_dateTo'].' 00:00:00'))->format('Y-m-d 00:00:00')."' ";
                $query = $em->createQuery($sql);
                $MilesConference = $query->getResult();
                $MilesConference = $MilesConference[0];

                $MilesConference->setChecked($dados['checked']);

                $em->persist($MilesConference);
                $em->flush($MilesConference);

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
        } else {
            try {
                $em->getConnection()->beginTransaction();
                $days = 1;
                if((new \DateTime())->format('l') == "Monday") {
                    $days = 2;
                }

                $sql = "select m from MilesConference m where m.issueDate < '".(new \DateTime())->format('Y-m-d 00:00:00')."' and m.cards = '".$dados['cards_id']."' and m.issueDate >= '".(new \DateTime())->modify('-'.$days.' day')->format('Y-m-d 00:00:00')."' ";
                $query = $em->createQuery($sql);
                $MilesConference = $query->getResult();
                $MilesConference = $MilesConference[0];

                $MilesConference->setChecked($dados['checked']);

                $em->persist($MilesConference);
                $em->flush($MilesConference);

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
    }

    public function loadMilesbenchReportData(Request $request, Response $response) {
        $dados = $request->getRow();
        $requestData = $request->getRow();
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        // searching params
        $where = '';
        
        $date =  new \DateTime();
        
        if(isset($requestData['searchKeywords']) && $requestData['searchKeywords'] != '') {
            $where .= " AND ( "
            ." b.id like '%".$requestData['searchKeywords']."%' or "
            ." b.name like '%".$requestData['searchKeywords']."%' or "
            ." b.email like '%".$requestData['searchKeywords']."%' or "
            ." b.phoneNumber like '%".$requestData['searchKeywords']."%' or "
            ." b.registrationCode like '%".$requestData['searchKeywords']."%' or "
            ." c.cardNumber like '%".$requestData['searchKeywords']."%' or "
            ." c.cardType like '%".$requestData['searchKeywords']."%' or "
            ." a.name like '%".$requestData['searchKeywords']."%' or "
            ." c.id like '%".$requestData['searchKeywords']."%' or "
            ." c.token like '%".$requestData['searchKeywords']."%' ) ";
        }
        
        if(isset($dados['airline']) && $dados['airline'] != ''){
            $where .= " AND a.name = '".$dados['airline']."' ";
        }

        if(isset($dados['miles']) && $dados['miles'] != ''){
            $where .= " AND m.leftover >= '".$dados['miles']."' ";
        }

        if(isset($dados['email']) && $dados['email'] != '') {
            $where .= " AND b.email like '%" . $dados['email'] . "%' ";
        }

        if(isset($dados['milesEqual']) && $dados['milesEqual'] != '') {
            $where .= " AND m.leftover = " . $dados['milesEqual'] . " ";
        }

        if(isset($dados['milesSmaller']) && $dados['milesSmaller'] != '') {
            $where .= " AND m.leftover < " . $dados['milesSmaller'] . " ";
        }

        $sql = "select c, b, a, m FROM Milesbench m JOIN m.cards c JOIN c.businesspartner b JOIN c.airline a ";
        if(isset($dados['blocked']) && ($dados['blocked'] === 'true' || $dados['blocked'] === true)) {
            $sql .= " WHERE c.blocked = 'Y' " . $where;
        } else {
            $sql .= " WHERE c.blocked <> 'Y' " . $where;
        }

        // order
        $orderBy = '';
        if(isset($requestData['order']) && $requestData['order'] != '') {
            if($requestData['order'] == 'airline') {
                $orderBy = ' order by a.name ASC ';
            } else if($requestData['order'] == 'leftover') {
                $orderBy = ' order by m.leftover ASC ';
            } else {
                $orderBy = ' order by b.'.$requestData['order'].' ASC ';
            }
        }
        if(isset($requestData['orderDown']) && $requestData['orderDown'] != '') {
            if($requestData['orderDown'] == 'airline') {
                $orderBy = ' order by a.name DESC ';
            } else if($requestData['orderDown'] == 'leftover') {
                $orderBy = ' order by m.leftover DESC ';
            } else {
                $orderBy = ' order by b.'.$requestData['orderDown'].' DESC ';
            }
        }
        $sql = $sql.$orderBy;

        $query = $em->createQuery($sql);
        $milesbench = $query->getResult();

        $dataset = array();
        foreach($milesbench as $miles){
            $dataset[] = array(
                'name' => $miles->getCards()->getBusinesspartner()->getName(),
                'email' => $miles->getCards()->getBusinesspartner()->getEmail(),
                'phoneNumber' => $miles->getCards()->getBusinesspartner()->getPhoneNumber(),
                'airline' => $miles->getCards()->getAirline()->getName(),
                'card_type' => $miles->getCards()->getCardType(),
                'card_number' => $miles->getCards()->getCardNumber(),
                'leftover' => (float)$miles->getLeftover(),
                'due_date' => $miles->getDueDate()->format('Y-m-d'),
                'contract_due_date' => $miles->getContractDueDate()->format('Y-m-d'),
                'status' => $miles->getCards()->getBlocked()
            );
        }

        $response->setDataset($dataset);
    }

    // Special cards functions
    public static function checkAzulMMS($em, $cards_id, $miles, $issue_date, $boarding_date, $from, $to) {
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        if ($cards_id != 212359) {
            return null;
        }

        if( (new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') ))->modify('first day of this month') > $issue_date ) {
            return null;
        }

        $date =  new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') );
        $dDiff = $boarding_date->diff($date);

        // getting flight path category
        $category = 'Competitive';
        $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
            array( 'flightFrom' => $from, 'flightTo' => $to )
        );
        if($FlightPathCategory) {
            $category = $FlightPathCategory->getFlightCategory()->getName();
        }

        // getting milesbench
        $AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => $category ) );
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'flightCategory' => $AzulFlightCategory, 'control' => 'MMS' )
        );

        foreach ($MilesbenchCategory as $key => $value) {

            if($dDiff->days <= (int)$value->getDays()) {
                // removing miles shold be the entire value
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.used = ( m.used + $miles ), m.toFree = ( m.toFree - $miles ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }

            if($dDiff->days > 21) {
                // adding miles shold be percent
                $milesUsedByDaysPercent = ( $miles / 100 ) * (float)$value->getPercentage();
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.toFree = ( m.toFree + $milesUsedByDaysPercent ), m.originalToFree = ( m.originalToFree + $milesUsedByDaysPercent ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }
        }

        return true;
    }

    public static function checkAzulSRM($em, $cards_id, $miles, $issue_date, $boarding_date, $from, $to) {
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        if ($cards_id != 3100 && $cards_id != 5290 && $cards_id != 9709 && $cards_id != 11799  && $cards_id != 12226) {
            return null;
        }

        if( (new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') ))->modify('first day of this month') > $issue_date ) {
            return null;
        }

        $date =  new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') );
        $dDiff = $boarding_date->diff($date);

        // getting flight path category
        $category = 'Competitive';
        $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
            array( 'flightFrom' => $from, 'flightTo' => $to )
        );
        if($FlightPathCategory) {
            $category = $FlightPathCategory->getFlightCategory()->getName();
        }

        // getting milesbench
        $AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => $category ) );
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'flightCategory' => $AzulFlightCategory, 'control' => 'SRM' )
        );

        foreach ($MilesbenchCategory as $key => $value) {

            if($dDiff->days <= (int)$value->getDays()) {
                // removing miles shold be the entire value
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.used = ( m.used + $miles ), m.toFree = ( m.toFree - $miles ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }

            if($dDiff->days > 21) {
                // adding miles shold be percent
                $milesUsedByDaysPercent = ( $miles / 100 ) * (float)$value->getPercentage();
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.toFree = ( m.toFree + $milesUsedByDaysPercent ), m.originalToFree = ( m.originalToFree + $milesUsedByDaysPercent ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }
        }

        return true;
    }

    // updating both control cards
    public static function updateAzulSRMAll(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        // getting milesbench MMS
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'control' => 'MMS' )
        );
        foreach ($MilesbenchCategory as $key => $value) {

            if( $value->getFlightCategory()->getName() == 'Competitive' ) {
                $sqlCategory = " and ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
            } else {
                $sqlCategory = " and ( CONCAT(f.code, t.code) in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
            }
            $sqlCategory .= " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', " .
                " 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";

            $month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
            $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
                " s.cards_id IN (212359) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) > 21 " .
            $sqlCategory;
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {
                $milesUsed21Days = (float)$row['milesUsed'];
            }

            $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
                " s.cards_id IN (212359) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) <= " . (int)$value->getDays() . " " .
            $sqlCategory;
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {
                $milesUsedByDays = (float)$row['milesUsed'];
            }
            
            $sqlMiles = "UPDATE MilesbenchCategory m " .
                " SET m.used = ( $milesUsedByDays ) " .
                " WHERE m.id = " . $value->getId() . " ";
            $queryMiles = $em->createQuery($sqlMiles);
            $resultMiles = $queryMiles->getResult();
            
            $milesUsedByDaysPercent = ( $milesUsed21Days / 100 ) * (float)$value->getPercentage();
            $sqlMiles = "UPDATE MilesbenchCategory m " .
                " SET m.toFree = ( $milesUsedByDaysPercent - m.used ), m.originalToFree = ( $milesUsedByDaysPercent ) " .
                " WHERE m.id = " . $value->getId() . " ";
            $queryMiles = $em->createQuery($sqlMiles);
            $resultMiles = $queryMiles->getResult();
        }

        // getting milesbench SRM
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'control' => 'SRM' )
        );
        foreach ($MilesbenchCategory as $key => $value) {

            if( $value->getFlightCategory()->getName() == 'Competitive' ) {
                $sqlCategory = " and ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
            } else {
                $sqlCategory = " and ( CONCAT(f.code, t.code) in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
            }
            $sqlCategory .= " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', " .
                " 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";

            $month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
            $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
                " s.cards_id IN (3100, 5290, 9709, 11799, 12226) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) > 21 " .
            $sqlCategory;
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {
                $milesUsed21Days = (float)$row['milesUsed'];
            }

            $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
                " s.cards_id IN (3100, 5290, 9709, 11799, 12226) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) <= " . (int)$value->getDays() . " " .
            $sqlCategory;
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {
                $milesUsedByDays = (float)$row['milesUsed'];
            }
            
            $sqlMiles = "UPDATE MilesbenchCategory m " .
                " SET m.used = ( $milesUsedByDays ) " .
                " WHERE m.id = " . $value->getId() . " ";
            $queryMiles = $em->createQuery($sqlMiles);
            $resultMiles = $queryMiles->getResult();
            
            $milesUsedByDaysPercent = ( $milesUsed21Days / 100 ) * (float)$value->getPercentage();
            $sqlMiles = "UPDATE MilesbenchCategory m " .
                " SET m.toFree = ( $milesUsedByDaysPercent - m.used ), m.originalToFree = ( $milesUsedByDaysPercent ) " .
                " WHERE m.id = " . $value->getId() . " ";
            $queryMiles = $em->createQuery($sqlMiles);
            $resultMiles = $queryMiles->getResult();
        }
    }

    public static function updateAzulMMS($em, $cards_id, $miles, $issue_date, $boarding_date, $from, $to) {
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        if ($cards_id != 212359) {
            return null;
        }

        if( (new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') ))->modify('first day of this month') > $issue_date ) {
            return null;
        }

        $date =  new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') );
        $dDiff = $boarding_date->diff($date);

        // getting flight path category
        $category = 'Competitive';
        $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
            array( 'flightFrom' => $from, 'flightTo' => $to )
        );
        if($FlightPathCategory) {
            $category = $FlightPathCategory->getFlightCategory()->getName();
        }

        // getting milesbench
        $AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => $category ) );
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'flightCategory' => $AzulFlightCategory, 'control' => 'MMS' )
        );

        foreach ($MilesbenchCategory as $key => $value) {

            if($dDiff->days <= (int)$value->getDays()) {
                // removing miles shold be the entire value
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.used = ( m.used - $miles ), m.toFree = ( m.toFree + $miles ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }

            if($dDiff->days > 21) {
                // adding miles shold be percent
                $milesUsedByDaysPercent = ( $miles / 100 ) * (float)$value->getPercentage();
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.toFree = ( m.toFree - $milesUsedByDaysPercent ), m.originalToFree = ( m.originalToFree - $milesUsedByDaysPercent ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }
        }

        return true;
    }

    public static function updateAzulSRM($em, $cards_id, $miles, $issue_date, $boarding_date, $from, $to) {
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        if ($cards_id != 3100 && $cards_id != 5290 && $cards_id != 9709 && $cards_id != 11799 && $cards_id != 12226) {
            return null;
        }

        if( (new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') ))->modify('first day of this month') > $issue_date ) {
            return null;
        }

        $date =  new \DateTime( $issue_date->format('Y') . '-' . $issue_date->format('m') . '-' . $issue_date->format('d') );
        $dDiff = $boarding_date->diff($date);

        // getting flight path category
        $category = 'Competitive';
        $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
            array( 'flightFrom' => $from, 'flightTo' => $to )
        );
        if($FlightPathCategory) {
            $category = $FlightPathCategory->getFlightCategory()->getName();
        }

        // getting milesbench
        $AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => $category ) );
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'flightCategory' => $AzulFlightCategory, 'control' => 'SRM' )
        );

        foreach ($MilesbenchCategory as $key => $value) {

            if($dDiff->days <= (int)$value->getDays()) {
                // removing miles shold be the entire value
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.used = ( m.used - $miles ), m.toFree = ( m.toFree + $miles ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }

            if($dDiff->days > 21) {
                // adding miles shold be percent
                $milesUsedByDaysPercent = ( $miles / 100 ) * (float)$value->getPercentage();
                $sqlMiles = "UPDATE MilesbenchCategory m " .
                    " SET m.toFree = ( m.toFree - $milesUsedByDaysPercent ), m.originalToFree = ( m.originalToFree - $milesUsedByDaysPercent ) " .
                    " WHERE m.id = " . $value->getId() . " ";
                $queryMiles = $em->createQuery($sqlMiles);
                $resultMiles = $queryMiles->getResult();
            }
        }

        return true;
    }

    // checking availability
    public static function checkMMSAzulPossibility($airline, $miles, $boardingDate, $from, $to) {
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $date =  new \DateTime();
        $date->modify('today');
        $dDiff = $boardingDate->diff($date);

        // the base of the calculation
        if($dDiff->days > 21) {
            return true;
        }

        // getting flight path category
        $category = 'Competitive';
        $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
            array( 'flightFrom' => $from, 'flightTo' => $to )
        );
        if($FlightPathCategory) {
            $category = $FlightPathCategory->getFlightCategory()->getName();
        }

        // getting milesbench
        $AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => $category ) );
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'flightCategory' => $AzulFlightCategory, 'control' => 'MMS' )
        );

        if( $category == 'Competitive' ) {
            $sqlCategory = " and ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
        } else {
            $sqlCategory = " and ( CONCAT(f.code, t.code) in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
        }
        $sqlCategory .= " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', " .
            " 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";

        $toFree = 0;
        $percent = 0;
        $days = 0;
        $toNegative = 0;
        foreach ($MilesbenchCategory as $key => $value) {
            if((int)$value->getDays() >= $dDiff->days && $days == 0) {
                $toFree = (float)$value->getToFree();
                $toNegative = (float)$value->getToNegative();
                $percent = (float)$value->getPercentage();
                $days = (int)$value->getDays();
            }
        }

        $milesUsedByDaysSales = 0;
        $month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
        $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
            " s.cards_id IN (212359) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) <= " . $days . " " .
            $sqlCategory;
        $stmt = $QueryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $milesUsedByDaysSales = $row['milesUsed'];
        }

        $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
            " s.cards_id IN (212359) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) > 21 " .
            $sqlCategory;
        $stmt = $QueryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $milesUsed21Days = $row['milesUsed'];
        }

        if( (($milesUsed21Days / 100 ) * $percent) + $toNegative < ( $milesUsedByDaysSales + $toFree  )) {
            return false;
        }
        
        if($miles <= $toFree + $toNegative) {
            return true;
        }

        return false;
    }

    public static function checkSRMAzulPossibility($airline, $miles, $boardingDate, $from, $to) {
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $date =  new \DateTime();
        $date->modify('today');
        $dDiff = $boardingDate->diff($date);

        // the base of the calculation
        if($dDiff->days > 21) {
            return true;
        }

        $sql = "select m from Milesbench m JOIN m.cards c where m.cards IN (3100, 5290, 9709, 11799, 12226) and m.leftover > ".$miles." and c.blocked = 'N' ";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();
        if(count($Cards) == 0) {
            return false;
        }

        // getting flight path category
        $category = 'Competitive';
        $FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
            array( 'flightFrom' => $from, 'flightTo' => $to )
        );
        if($FlightPathCategory) {
            $category = $FlightPathCategory->getFlightCategory()->getName();
        }

        // getting milesbench
        $AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => $category ) );
        $MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
            array( 'flightCategory' => $AzulFlightCategory, 'control' => 'SRM' )
        );

        if( $category == 'Competitive' ) {
            $sqlCategory = " and ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
        } else {
            $sqlCategory = " and ( CONCAT(f.code, t.code) in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
        }
        $sqlCategory .= " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', " .
            " 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";

        $toFree = 0;
        $percent = 0;
        $days = 0;
        $toNegative = 0;
        foreach ($MilesbenchCategory as $key => $value) {
            if((int)$value->getDays() >= $dDiff->days && $days == 0) {
                $toFree = (float)$value->getToFree();
                $toNegative = (float)$value->getToNegative();
                $percent = (float)$value->getPercentage();
                $days = (int)$value->getDays();
            }
        }

        $milesUsedByDaysSales = 0;
        $month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
        $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
            " s.cards_id IN (3100, 5290, 9709, 11799, 12226) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) <= " . $days . " " .
            $sqlCategory;
        $stmt = $QueryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $milesUsedByDaysSales = $row['milesUsed'];
        }

        $sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
            " s.cards_id IN (3100, 5290, 9709, 11799, 12226) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) > 21 " .
            $sqlCategory;
        $stmt = $QueryBuilder->query($sql);
        while ($row = $stmt->fetch()) {
            $milesUsed21Days = $row['milesUsed'];
        }

        if( (($milesUsed21Days / 100 ) * $percent) + $toNegative < ( $milesUsedByDaysSales + $toFree  )) {
            return false;
        }
        
        if($miles <= $toFree + $toNegative) {
            return true;
        }

        return false;
    }

    public static function checkNotValidCardsPerPax($airline, $pax_quant, $searchMiles, $paxes) {
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $ids = '0';
        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        if($airline == 'LATAM') {
            $startDate = '2018-08-09';
        } else if($airline == 'AZUL') {
            $startDate = '2018-12-15';
        } else if($airline == 'GOL') {
            $startDate = new \DateTime();
            $startDate = $startDate->format('Y-01-01');
        }
        $cards = [];

        $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $airline ) );
        $sql = " select p.id, s.cards_id, c.max_per_pax, b.name, p.name as pax_name from sale s ".
            " inner join milesbench m on m.cards_id = s.cards_id ".
            " inner join online_pax w on w.id = s.online_pax_id ".
            " inner join cards c on c.id = m.cards_id ".
            " inner join businesspartner p on p.id = s.pax_id ".
            " inner join businesspartner b on b.id = c.businesspartner_id ".
            " inner join airport f on f.id = s.airport_from ".
            " inner join airport t on t.id = s.airport_to ".
            " where s.airline_id = ".$Airline->getId()." and s.issue_date >= '" . $startDate . "' and s.is_extra <> 'true' ".
            " and b.status = 'Aprovado' and c.blocked = 'N' ".
            " and w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' ".
            " and m.leftover > ". $searchMiles ." GROUP by s.cards_id, SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
        // nacional
        $stmt2 = $QueryBuilder->query($sql);
        while ($row2 = $stmt2->fetch()) {
            if(!isset($cards[$row2['cards_id']])) {
                $cards[$row2['cards_id']] = [
                    'passageiros' => 0,
                    'namesUsed' => [],
                    'cards_id' => $row2['cards_id'],
                    'max_per_pax' => $row2['max_per_pax'],
                    'name' => $row2['name']
                ];
            }
            if(!isset($cards[$row2['cards_id']]['namesUsed'][$row2['pax_name']])) {
                $cards[$row2['cards_id']]['namesUsed'][$row2['pax_name']] = 1;
                $cards[$row2['cards_id']]['passageiros']++;
            }

        }

        $sql = " select p.id, s.cards_id, c.max_per_pax, b.name, p.name as pax_name from sale s ".
            " inner join milesbench m on m.cards_id = s.cards_id ".
            " inner join online_pax w on w.id = s.online_pax_id ".
            " inner join cards c on c.id = m.cards_id ".
            " inner join businesspartner p on p.id = s.pax_id ".
            " inner join businesspartner b on b.id = c.businesspartner_id ".
            " inner join airport f on f.id = s.airport_from ".
            " inner join airport t on t.id = s.airport_to ".
            " where s.airline_id = ".$Airline->getId()." and s.issue_date >= '" . $startDate . "' and s.is_extra <> 'true' ".
            " and b.status = 'Aprovado' and c.blocked = 'N' ".
            " and (f.international = 'true' or t.international = 'true') ".
            " and m.leftover > ". $searchMiles ." group by  p.name ";
        // inter
        $stmt2 = $QueryBuilder->query($sql);
        while ($row2 = $stmt2->fetch()) {
            if(!isset($cards[$row2['cards_id']])) {
                $cards[$row2['cards_id']] = [
                    'passageiros' => 0,
                    'namesUsed' => [],
                    'cards_id' => $row2['cards_id'],
                    'max_per_pax' => $row2['max_per_pax'],
                    'name' => $row2['name']
                ];
            }
            if(!isset($cards[$row2['cards_id']]['namesUsed'][$row2['pax_name']])) {
                $cards[$row2['cards_id']]['namesUsed'][$row2['pax_name']] = 1;
                $cards[$row2['cards_id']]['passageiros']++;
            }
        }

        foreach ($cards as $key => $row2) {
            $maxCount = (int)$Airline->getMaxPerPax();
            if((int)$row2['max_per_pax'] != 0) {
                $maxCount = (int)$row2['max_per_pax'];
            }

            foreach ($paxes as $key => $value) {
                if( strtoupper(getFirstName($row2['name'], '', '')) == strtoupper(getFirstName($value['pax_name'], $value['paxLastName'], $value['paxAgnome'])) &&
                    strtoupper(getLastName($row2['name'], '', '')) == strtoupper(getLastName($value['pax_name'], $value['paxLastName'], $value['paxAgnome'])) ) {
                    $maxCount++;
                }

                if($Airline->getMaxPaxField() == 'name') {
                    $where = " p.name like '". getFirstName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ." %' and p.name like '% ". getLastName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ."' ";
                } else {
                    $where = " REPLACE(REPLACE(p.". $Airline->getMaxPaxField() .", '.', ''), '-', '') = '". clean($value['identification'], $Airline->getMaxPaxField()) . "' ";
                }
                $sql = " select DISTINCT(s.cards_id) as cards_id from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join cards c on c.id = s.cards_id ".
                    " inner join milesbench m on c.id = m.cards_id ".
                    " inner join businesspartner b on b.id = c.businesspartner_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and c.id = ".$row2['cards_id']." ".
                    " and w.is_newborn = 'N' ".
                    " and s.issue_date >= '" . $startDate . "' ".
                    " and  ". $where ." group by s.cards_id ";
                $stmt3 = $QueryBuilder->query($sql);
                while ($row3 = $stmt3->fetch()) {
                    if( $row3['cards_id'] == $row2['cards_id'] ) {
                        $maxCount++;
                    }
                }

                if( strtoupper(getFirstName($row2['name'], '', '')) == strtoupper(getFirstName($value['pax_name'], $value['paxLastName'], $value['paxAgnome'])) &&
                    strtoupper(getLastName($row2['name'], '', '')) == strtoupper(getLastName($value['pax_name'], $value['paxLastName'], $value['paxAgnome'])) ) {
                    $maxCount++;
                }
            }

            if( $row2['passageiros'] + (int)$pax_quant > $maxCount ) {
                $ids .= ',' . $row2['cards_id'];
            }
        }

        return $ids;
    }

    public static function checkValidCardsPerPax($airline, $pax_quant, $searchMiles, $paxes) {
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $ids = '0';
        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        if($airline == 'LATAM') {
            $startDate = '2018-08-09';
        } else if($airline == 'AZUL') {
            $startDate = '2018-12-15';
        } else if($airline == 'GOL') {
            $startDate = new \DateTime();
            $startDate = $startDate->format('Y-01-01');
        }

        $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $airline ) );

        foreach ($paxes as $key => $value) {
            if($Airline->getMaxPaxField() == 'name') {
                $where = " p.name like '". getFirstName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ." %' and p.name like '% ". getLastName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ."' ";
            } else {
                $where = " REPLACE(REPLACE(p.". $Airline->getMaxPaxField() .", '.', ''), '-', '') = '". clean($value['identification'], $Airline->getMaxPaxField()) . "' ";
            }
            $sql = " select DISTINCT(s.cards_id) as cards_id from sale s ".
                " inner join businesspartner p on p.id = s.pax_id ".
                " inner join cards c on c.id = s.cards_id ".
                " inner join online_pax w on w.id = s.online_pax_id ".
                " inner join milesbench m on c.id = m.cards_id ".
                " inner join businesspartner b on b.id = c.businesspartner_id ".
                " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
                " and w.is_newborn = 'N' ".
                " and  ". $where ." group by s.cards_id ";
            $stmt3 = $QueryBuilder->query($sql);
            while ($row3 = $stmt3->fetch()) {
                $ids .= ',' . $row3['cards_id'];
            }
        }

        return $ids;
    }

}

function clean($string, $field = 'registration_code') {
    if($field == 'name') {
        $newName = '';
        $arrayName = explode(' ', $string);
        foreach ($arrayName as $key => $value) {
            if($key == 0) {
                $newName .= $value;
            }
            
            if(count($arrayName) -1 == $key) {
                if(blackListNames($value)) {
                    $newName .= ' ' . $arrayName[$key -1] . ' ' . $value;
                } else {
                    $newName .= ' ' . $value;
                }
            }
        }
        $string = $newName;
    }

    $string = str_replace(' ', '-', $string);
    $string = str_replace('-', '', $string);
    $string = str_replace('.', '', $string);
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    return preg_replace('/-+/', '-', $string);
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
