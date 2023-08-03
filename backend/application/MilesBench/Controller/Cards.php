<?php
/**
 * Created by PhpStorm.
 * User: robertomartins
 * Date: 1/19/2015
 * Time: 11:04 PM
 */

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class cards {
    public function loadProviderAirline(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $sql = "select c FROM Cards c JOIN c.businesspartner b JOIN c.airline a WHERE b.name = '".$dados['name']."' AND a.name = '".$dados['airline']."'";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();


        $dataset = array();
        foreach($Cards as $card){

            $dataset[] = array(
                'card_number' => $card->getCardNumber(),
                'access_id' => $card->getAccessId(),
                'access_password' => $card->getAccessPassword(),
                'recovery_password' => $card->getRecoveryPassword(),
                'card_type' => $card->getCardType(),
                'token' => $card->getToken()
            );

        }
        $response->setDataset($dataset);
    }

    public function encriptDataBase(Request $request, Response $response) {
        function ecript($args){
            $password = '';
            $args = str_split($args);
            for ($j = 0; $j <= count($args)-1; $j++) {
                $pas = $args[$j];
                $code = ord($pas) * 320;
                $password = $password.$code.'320AB';
            }
            return $password;
        }

        $em = Application::getInstance()->getEntityManager();

        try{
            // $sql = "select c FROM Cards c ";
            // $query = $em->createQuery($sql);
            // $Cards = $query->getResult();

            // $dataset = array();
            // foreach($Cards as $card){
            //     $card->setRecoveryPassword(ecript($card->getRecoveryPassword()));
            //     $card->setAccessPassword(ecript($card->getAccessPassword()));

            //     $em->persist($card);
            //     $em->flush($card);
            // }


            // $sql = "select i FROM InternalCards i ";
            // $query = $em->createQuery($sql);
            // $InternalCards = $query->getResult();

            // $dataset = array();
            // foreach($InternalCards as $card){
            //     $card->setCardPassword(ecript($card->getCardPassword()));
                
            //     $em->persist($card);
            //     $em->flush($card);
            // }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
    
    public function loadCardsData(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        
        $clear_session = false;
        if(isset($dados['clear_session'])){
            $clear_session = $dados['clear_session'] === 'true'? true: false;
        }

        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if (isset($dados['type'])) {
            $type = $dados['type'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        if (isset($dados['sale'])) {
            $sale = $dados['sale'];
        }

        $dataset = array();
        if((isset($sale['saleByThird']) && $sale['saleByThird'] == 'Y') || (isset($sale['isExtra']) && ($sale['isExtra'] === 'true' || $sale['isExtra'] === true))) {
        }else if(isset($sale['cardNumber']) && $sale['cardNumber'] != '') {
            $card = $em->getRepository('Cards')->findOneBy(array('id' => $sale['cards_id']));
            if(isset($card)){
                $provider = $card->getBusinesspartner();
                $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $card->getId()));
                
                $dataset[] = array(
                    'card_number' => $card->getCardNumber(),
                    'access_id' => $card->getAccessId(),
                    'access_password' => $card->getAccessPassword(),
                    'recovery_password' => $card->getRecoveryPassword(),
                    'card_type' => $card->getCardType(),
                    'cards_id' => $card->getId(),
                    'token' => $card->getToken(),
                    'chip_number' => $provider->getChipNumber(),
                    'leftOver' => $MilesBench->getLeftover(),
                    'name_provider' => $provider->getName(),
                    'email_provider' => $provider->getEmail(),
                    'card_registrationCode' => $provider->getRegistrationCode(),
                    'adress_provider' => $provider->getAdress(),
                    'phone_provider' => $provider->getPhoneNumber(),
                    'celNumberAirline' => $provider->getPhoneNumber2()
                );

            }
            $dataset = array_shift($dataset);
        }else if(isset($dados['card_number'])){
            $card = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
            if(isset($card)){
                $provider = $card->getBusinesspartner();
                $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $card->getId()));

                $dataset[] = array(
                    'card_number' => $card->getCardNumber(),
                    'access_id' => $card->getAccessId(),
                    'access_password' => $card->getAccessPassword(),
                    'recovery_password' => $card->getRecoveryPassword(),
                    'card_type' => $card->getCardType(),
                    'cards_id' => $card->getId(),
                    'token' => $card->getToken(),
                    'chip_number' => $provider->getChipNumber(),
                    'leftOver' => $MilesBench->getLeftover(),
                    'name_provider' => $provider->getName(),
                    'email_provider' => $provider->getEmail(),
                    'card_registrationCode' => $provider->getRegistrationCode(),
                    'adress_provider' => $provider->getAdress(),
                    'phone_provider' => $provider->getPhoneNumber(),
                    'celNumberAirline' => $provider->getPhoneNumber2()
                );

            }
        }else{
            foreach ($dados as $dado) {
                if(isset($dado['card_number']) && $dado['card_number'] != ''){
                    if(isset($dado['cards_id'])) {
                        $card = $em->getRepository('Cards')->findOneBy(array('id' => $dado['cards_id']));
                        if(isset($card)){
                            $provider = $card->getBusinesspartner();
                            $MilesBench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $card->getId()));
                            
                            $dataset[] = array(
                                'card_number' => $card->getCardNumber(),
                                'access_id' => $card->getAccessId(),
                                'access_password' => $card->getAccessPassword(),
                                'recovery_password' => $card->getRecoveryPassword(),
                                'card_type' => $card->getCardType(),
                                'cards_id' => $card->getId(),
                                'token' => $card->getToken(),
                                'chip_number' => $provider->getChipNumber(),
                                'leftOver' => $MilesBench->getLeftover(),
                                'name_provider' => $provider->getName(),
                                'email_provider' => $provider->getEmail(),
                                'card_registrationCode' => $provider->getRegistrationCode(),
                                'adress_provider' => $provider->getAdress(),
                                'phone_provider' => $provider->getPhoneNumber(),
                                'celNumberAirline' => $provider->getPhoneNumber2()
                            );
                            if(isset($UserPartner)) {
                                if(!isset($type) || $type != 'SALEDONE') {
                                    $card->setUserSession($UserPartner->getName());
                                    $card->setUserSessionDate(new \Datetime());
                                    $em->persist($card);
                                    $em->flush($card);
                                }
                            }
                        }
                    }
                }
            }
        }
        if($clear_session){
            self::removeUserSession($UserPartner->getId());
        }
        $response->setDataset($dataset);
    }

    public function removeUserSession($partner) {
        $em = Application::getInstance()->getEntityManager();

        $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $partner));
        $Cards = $em->getRepository('Cards')->findBy(array('userSession' => $UserPartner->getName()));
        foreach ($Cards as $card) {
            $card->setUserSession('');
            $card->setUserSessionDate(null);
            $em->persist($card);
            $em->flush($card);
        }
    }

    public function loadCardsInUse(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();

        $nome = '';
        if(isset($dados['nome'])) {
            $nome = $dados['nome'];
        }

        $id = '';
        if(isset($dados['id'])) {
            $id = $dados['id'];
        }
        
        $sql = "select c FROM Cards c WHERE c.userSession <> '' ";
        if ($nome != '') {
            $sql = "select c FROM Cards c WHERE c.userSession = ".$nome." ";
        }
        if($id != ''){
            $sql .= " and c.id = ".$id." ";
        }

        $query = $em->createQuery($sql);
        $Cards = $query->getResult();

        $dataset = array();
        foreach($Cards as $card){
            $dataset[] = array(
                'cards_id' => $card->getId(),
                'card_number' => $card->getCardNumber(),
                'airline' => $card->getAirline()->getName(),
                'provider' => $card->getBusinesspartner()->getName(),
                'access_id' => $card->getAccessId(),
                'access_password' => $card->getAccessPassword(),
                'recovery_password' => $card->getRecoveryPassword(),
                'card_type' => $card->getCardType(),
                'token' => $card->getToken(),
                'userSession' => $card->getUserSession()
            );
        }
        $response->setDataset($dataset);
    }

    public function removeCardUse(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $card = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
        if($card) {
            $card->setUserSession('');
            $card->setUserSessionDate(null);
            $em->persist($card);
            $em->flush($card);
        }
        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro alterado com sucesso');
        $response->addMessage($message);
    }

    public function removeCardUseByTime(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['hashId'])) {
            $hashId = $dados['hashId'];
        }
        if (isset($dados['name'])) {
            $name = $dados['name'];
        }
        if (isset($dados['minutes'])) {
            $minutes = $dados['minutes'];
        }

        $date = (new \DateTime())->modify("-".$minutes." minutes");

        $em = Application::getInstance()->getEntityManager();

        $cards = $em->getRepository('Cards')->findBy(array('userSession' => $name));
        $free_cards = array();
        if($cards) {
            foreach($cards as $card){
                if($date >= $card->getUserSessionDate()){
                    $card->setUserSession('');
                    $card->setUserSessionDate(null);
                    $em->persist($card);
                    $em->flush($card);
                    $free_cards[] = array('id' => $card->getId());
                }
            }
        }
        $response->setDataset($free_cards);
        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registros alterados com sucesso');
        $response->addMessage($message);
    }

    public function loadProvider(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        
        $em = Application::getInstance()->getEntityManager();
        $sql = "select m, c, a FROM Milesbench m JOIN m.cards c JOIN c.businesspartner b JOIN c.airline a WHERE b.registrationCode = '".$dados['registrationCode']."'";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();

        $dataset = array();
        foreach($Cards as $card){
            $dataset[] = array(
                'id' => $card->getCards()->getId(),
                'card_number' => $card->getCards()->getCardNumber(),
                'airline' => $card->getCards()->getAirline()->getName(),
                'card_number' => $card->getCards()->getCardNumber(),
                'access_id' => $card->getCards()->getAccessId(),
                'access_password' => $card->getCards()->getAccessPassword(),
                'recovery_password' => $card->getCards()->getRecoveryPassword(),
                'miles_leftover' => $card->getLeftover(),
                'card_type' => $card->getCardType(),
                'token' => $card->getToken()
            );
        }
        $response->setDataset($dataset);
    }       

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        if(isset($dados['id'])) {
            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['id']));
        } else {
            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
        }
        if ($Cards) {
            $Cards->setCardNumber($dados['card_number']);
            if(isset($dados['access_password'])) {
                $Cards->setAccessPassword($dados['access_password']);
            }
            // $Cards->setAccessId($dados['access_id']);
            if(isset($dados['recovery_password'])) {
                $Cards->setRecoveryPassword($dados['recovery_password']);
            }
            if(isset($dados['card_type'])){
                $Cards->setCardType($dados['card_type']);
            }
            if(isset($dados['priority']) && $dados['priority'] != '') {
                $Cards->setIsPriority($dados['priority']);
            }
            if(isset($dados['bloqued']) && $dados['bloqued'] != '') {
                $Cards->setBlocked($dados['bloqued']);
            }

            $em->persist($Cards);
            $em->flush($Cards);
        }    
        
        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro alterado com sucesso');
        $response->addMessage($message);        
    }

    public function loadCardsBloqued(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." b.id like '%".$dados['searchKeywords']."%' or "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCode like '%".$dados['searchKeywords']."%' or "
                ." b.adress like '%".$dados['searchKeywords']."%' or "
                ." b.email like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber2 like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber3 like '%".$dados['searchKeywords']."%' or "
                ." b.status like '%".$dados['searchKeywords']."%' or "
                ." b.bank like '%".$dados['searchKeywords']."%' or "
                ." b.agency like '%".$dados['searchKeywords']."%' or "
                ." b.account like '%".$dados['searchKeywords']."%' or "
                ." b.blockReason like '%".$dados['searchKeywords']."%' or "
                ." b.paymentType like '%".$dados['searchKeywords']."%' or "
                ." b.description like '%".$dados['searchKeywords']."%' or "
                ." b.creditAnalysis like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCodeCheck like '%".$dados['searchKeywords']."%' or "
                ." b.adressCheck like '%".$dados['searchKeywords']."%' or "
                ." b.creditDescription like '%".$dados['searchKeywords']."%' or "
                ." b.companyName like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumberAirline like '%".$dados['searchKeywords']."%' or "
                ." b.celNumberAirline like '%".$dados['searchKeywords']."%' or "
                ." b.typeSociety like '%".$dados['searchKeywords']."%' or "
                ." b.nameMother like '%".$dados['searchKeywords']."%' or "
                ." c.cardNumber like '%".$dados['searchKeywords']."%' or "
                ." c.cardType like '%".$dados['searchKeywords']."%' or "
                ." c.token like '%".$dados['searchKeywords']."%' ) ";
        }

        $sql = "select c FROM Cards c JOIN c.businesspartner b where c.blocked = 'Y' ".$where;

        // order
        $orderBy = '';
        if(isset($dados['order']) && $dados['order'] != '') {
            $orderBy = ' order by b.'.$dados['order'].' ASC ';
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            $orderBy = ' order by b.'.$dados['orderDown'].' DESC ';
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                ->setMaxResults($dados['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }

        $Cards = $query->getResult();

        $cardsBloqued = array();
        foreach($Cards as $card){

            $logData = array();
            $sql = "select s FROM SystemLog s where s.logType = 'CARDS' and s.description like '%-BLOQUED->CARD:".$card->getId()."-%' order by s.id DESC";
            $query = $em->createQuery($sql);
            $SystemLog = $query->getResult();

            $leftOver = '';
            $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $card->getId()));
            if($Milesbench) {
                $leftOver = (float)$Milesbench->getLeftover();
            }

            foreach ($SystemLog as $log) {

                $description = explode("-BLOQUED->CARD:".$card->getId()."-", $log->getDescription());
                $description = $description[1];

                $BusinessPartner = 'MMS VIAGENS';
                if($log->getBusinesspartner()) {
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()))->getName();
                }

                $logData[] = array(
                    'userName' => $BusinessPartner,
                    'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                    'description' => $description
                );
            }

            $cardsBloqued[] = array(
                'cards_id' => $card->getId(),
                'partnerName' => $card->getBusinesspartner()->getName(),
                'email' => $card->getBusinesspartner()->getPhoneNumber(),
                'registrationCode' => $card->getBusinesspartner()->getRegistrationCode(),
                'airline' => $card->getAirline()->getName(),
                'bloqued'=> ($card->getBlocked() == 'Y'),
                'card_number' => $card->getCardNumber(),
                'access_password' => $card->getAccessPassword(),
                'recoveryPassword' => $card->getRecoveryPassword(),
                'progress' => $logData,
                'priority' => ($card->getIsPriority() == 'true'),
                'leftOver' => $leftOver
            );
        }

        $sql = "select COUNT(c) as quant FROM Cards c JOIN c.businesspartner b where c.blocked = 'Y' ".$where;
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'cardsBloqued' => $cardsBloqued,
            'total' => $Quant[0]['quant']
        );

        $response->setDataset($dataset);
    }

    public function saveCardProgressMilesBench(Request $request, Response $response) {
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

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Registro de andamento -BLOQUED->CARD:".$dados['cards_id']."- ".$dados['description']);
            $SystemLog->setLogType('CARDS');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
            if($Cards) {
                if($dados['blocked'] == 'true') {
                    $Cards->setBlocked('Y');

                    $em->persist($Cards);
                    $em->flush($Cards);

                    $email1 = 'onemilhas@onemilhas.com.br';
                    $postfields = array(
                        'content' =>    "Olá,<br><br>".
                                        "Acaba de ser bloqueado o cartão de: ".$Cards->getBusinesspartner()->getName().".<br>".
                                        "CIA: ".$Cards->getAirline()->getName().".<br>".
                                        "Observação: ".$dados['description'].".<br>".
                                        "<br><br><br>Atenciosamente,".
                                        "<br>SRM-IT",
                        'partner' => $email1,
                        'subject' => '[MMS VIAGENS] - Notificação do sistema',
                        'type' => ''
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);

                    // status socket track
                    $postfields = array(
                        'provider' => $Cards->getBusinesspartner()->getName(),
                        'airline' => $Cards->getAirline()->getName()
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/cardBloqued');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);

                }

                if($dados['losses'] == 'true') {
                    $Cards->setBlocked('L');

                    $em->persist($Cards);
                    $em->flush($Cards);

                    $email1 = 'onemilhas@onemilhas.com.br';
                    $postfields = array(
                        'content' =>    "Olá,<br><br>".
                                        "Acaba de marcado como perda o cartão de: ".$Cards->getBusinesspartner()->getName().".<br>".
                                        "CIA: ".$Cards->getAirline()->getName().".<br>".
                                        "Observação: ".$dados['description'].".<br>".
                                        "<br><br><br>Atenciosamente,".
                                        "<br>SRM-IT",
                        'partner' => $email1,
                        'subject' => '[MMS VIAGENS] - Notificação do sistema',
                        'type' => ''
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);

                    // status socket track
                    $postfields = array(
                        'provider' => $Cards->getBusinesspartner()->getName(),
                        'airline' => $Cards->getAirline()->getName()
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::socketServer.'/cardBloqued');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $result = curl_exec($ch);

                }
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveCardProgressCardWaiting(Request $request, Response $response) {
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

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Registro de andamento -WAITING->CARD:".$dados['cards_id']."- ".$dados['description']);
            $SystemLog->setLogType('CARDS');
            $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);

            $Cards = $em->getRepository('Cards')->findOneBy(array('id' => $dados['cards_id']));
            if($Cards) {
                if($dados['blocked'] == 'true') {
                    $Cards->setBlocked('W');

                    $em->persist($Cards);
                    $em->flush($Cards);
                }
            }

            $em->getConnection()->commit();

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadCardsWaiting(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select c FROM Cards c where c.blocked = 'W' ";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();

        $dataset = array();
        foreach($Cards as $card){

            $logData = array();
            $sql = "select s FROM SystemLog s where s.logType = 'CARDS' and s.description like '%-WAITING->CARD:".$card->getId()."-%' order by s.id DESC";
            $query = $em->createQuery($sql);
            $SystemLog = $query->getResult();

            $leftOver = '';
            $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $card->getId()));
            if($Milesbench) {
                $leftOver = (float)$Milesbench->getLeftover();
            }

            foreach ($SystemLog as $log) {

                $description = explode("-BLOQUED->CARD:".$card->getId()."-", $log->getDescription());
                $description = $description[1];

                $BusinessPartner = 'MMS VIAGENS';
                if($log->getBusinesspartner()) {
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()))->getName();
                }

                $logData[] = array(
                    'userName' => $BusinessPartner,
                    'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                    'description' => $description
                );
            }

            $dataset[] = array(
                'cards_id' => $card->getId(),
                'partnerName' => $card->getBusinesspartner()->getName(),
                'email' => $card->getBusinesspartner()->getPhoneNumber(),
                'registrationCode' => $card->getBusinesspartner()->getRegistrationCode(),
                'airline' => $card->getAirline()->getName(),
                'bloqued'=> ($card->getBlocked() == 'Y' || $card->getBlocked() == 'W'),
                'card_number' => $card->getCardNumber(),
                'access_password' => $card->getAccessPassword(),
                'recoveryPassword' => $card->getRecoveryPassword(),
                'progress' => $logData,
                'priority' => ($card->getIsPriority() == 'true'),
                'leftOver' => $leftOver
            );
        }
        $response->setDataset($dataset);
    }

    public function checkBlockedCards(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $sql = "select c FROM Cards c where c.blocked = 'Y' ";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();

        $dataset = array();
        foreach($Cards as $card){
            $dataset[] = array(
                'name' => $card->getBusinesspartner()->getName().' - '.$card->getAirline()->getName()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadCardsLosses(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." b.id like '%".$dados['searchKeywords']."%' or "
                ." b.name like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCode like '%".$dados['searchKeywords']."%' or "
                ." b.adress like '%".$dados['searchKeywords']."%' or "
                ." b.email like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber2 like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumber3 like '%".$dados['searchKeywords']."%' or "
                ." b.status like '%".$dados['searchKeywords']."%' or "
                ." b.bank like '%".$dados['searchKeywords']."%' or "
                ." b.agency like '%".$dados['searchKeywords']."%' or "
                ." b.account like '%".$dados['searchKeywords']."%' or "
                ." b.blockReason like '%".$dados['searchKeywords']."%' or "
                ." b.paymentType like '%".$dados['searchKeywords']."%' or "
                ." b.description like '%".$dados['searchKeywords']."%' or "
                ." b.creditAnalysis like '%".$dados['searchKeywords']."%' or "
                ." b.registrationCodeCheck like '%".$dados['searchKeywords']."%' or "
                ." b.adressCheck like '%".$dados['searchKeywords']."%' or "
                ." b.creditDescription like '%".$dados['searchKeywords']."%' or "
                ." b.companyName like '%".$dados['searchKeywords']."%' or "
                ." b.phoneNumberAirline like '%".$dados['searchKeywords']."%' or "
                ." b.celNumberAirline like '%".$dados['searchKeywords']."%' or "
                ." b.typeSociety like '%".$dados['searchKeywords']."%' or "
                ." b.nameMother like '%".$dados['searchKeywords']."%' or "
                ." c.cardNumber like '%".$dados['searchKeywords']."%' or "
                ." c.cardType like '%".$dados['searchKeywords']."%' or "
                ." c.token like '%".$dados['searchKeywords']."%' ) ";
        }

        $sql = "select c FROM Cards c JOIN c.businesspartner b where c.blocked = 'L' ".$where;

        // order
        $orderBy = '';
        if(isset($dados['order']) && $dados['order'] != '') {
            $orderBy = ' order by b.'.$dados['order'].' ASC ';
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            $orderBy = ' order by b.'.$dados['orderDown'].' DESC ';
        }
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $query = $em->createQuery($sql)
                ->setFirstResult((($dados['page'] - 1) * $dados['numPerPage']))
                ->setMaxResults($dados['numPerPage']);
        } else {
            $query = $em->createQuery($sql);
        }

        $Cards = $query->getResult();

        $cardsBloqued = array();
        foreach($Cards as $card){

            $logData = array();
            $sql = "select s FROM SystemLog s where s.logType = 'CARDS' and s.description like '%-BLOQUED->CARD:".$card->getId()."-%' order by s.id DESC";
            $query = $em->createQuery($sql);
            $SystemLog = $query->getResult();

            $leftOver = '';
            $Milesbench = $em->getRepository('Milesbench')->findOneBy(array('cards' => $card->getId()));
            if($Milesbench) {
                $leftOver = (float)$Milesbench->getLeftover();
            }

            foreach ($SystemLog as $log) {

                $description = explode("-BLOQUED->CARD:".$card->getId()."-", $log->getDescription());
                $description = $description[1];

                $BusinessPartner = 'MMS VIAGENS';
                if($log->getBusinesspartner()) {
                    $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $log->getBusinesspartner()->getId()))->getName();
                }

                $logData[] = array(
                    'userName' => $BusinessPartner,
                    'issue_date' => $log->getIssueDate()->format('Y-m-d H:i:s'),
                    'description' => $description
                );
            }

            $cardsBloqued[] = array(
                'cards_id' => $card->getId(),
                'partnerName' => $card->getBusinesspartner()->getName(),
                'email' => $card->getBusinesspartner()->getPhoneNumber(),
                'registrationCode' => $card->getBusinesspartner()->getRegistrationCode(),
                'airline' => $card->getAirline()->getName(),
                'bloqued'=> ($card->getBlocked() == 'L'),
                'card_number' => $card->getCardNumber(),
                'access_password' => $card->getAccessPassword(),
                'recoveryPassword' => $card->getRecoveryPassword(),
                'progress' => $logData,
                'priority' => ($card->getIsPriority() == 'true'),
                'leftOver' => $leftOver
            );
        }

        $sql = "select COUNT(c) as quant FROM Cards c JOIN c.businesspartner b where c.blocked = 'L' ".$where;
        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'cardsBloqued' => $cardsBloqued,
            'total' => $Quant[0]['quant']
        );

        $response->setDataset($dataset);
    }

    public function loadPaxPerCard(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
            $dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        $startDate = '2018-08-09';

        if($dados['airline'] == 'AZUL') {
            $startDate = '2018-12-15';
        }
        if($dados['airline'] == 'GOL') {
            $startDate = new \DateTime();
            $startDate = $startDate->format('Y-01-01');
        }
        $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $dados['airline'] ) );
        $dataset = array();
        $namesUsed = [];

        $sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id, s.is_diamond from sale s ".
            " inner join businesspartner p on p.id = s.pax_id ".
            " inner join milesbench m on m.cards_id = s.cards_id ".
            " inner join online_pax w on w.id = s.online_pax_id ".
            " inner join cards c on m.cards_id = c.id ".
            " inner join airport f on f.id = s.airport_from ".
            " inner join airport t on t.id = s.airport_to ".
            " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
            " and s.cards_id = ". $dados['cards_id'] ." ".
            " and w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' ".
            " group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
        $stmt2 = $QueryBuilder->query($sql);
        while ($row2 = $stmt2->fetch()) {
            if( strtoupper(getFirstName($row2['name'], '', '')) == strtoupper(getFirstName($dados['name'], '', '')) &&
                strtoupper(getLastName($row2['name'], '', '')) == strtoupper(getLastName($dados['name'], '', '')) ) {
            } else {
                if(!isset($namesUsed[$row2['name']])) {
                    $dataset[] = array(
                        'pax_id' => $row2['pax_id'],
                        'name' => $row2['name'],
                        'registration_code' => $row2['registration_code'],
                        'is_diamond' => $row2['is_diamond'],
                    );
                    $namesUsed[$row2['name']] = 1;
                } else {
                    $namesUsed[$row2['name']]++;
                }
            }
        }

        $sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id, s.is_diamond from sale s ".
            " inner join businesspartner p on p.id = s.pax_id ".
            " inner join milesbench m on m.cards_id = s.cards_id ".
            " inner join online_pax w on w.id = s.online_pax_id ".
            " inner join cards c on m.cards_id = c.id ".
            " inner join airport f on f.id = s.airport_from ".
            " inner join airport t on t.id = s.airport_to ".
            " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
            " and s.cards_id = ". $dados['cards_id'] ." ".
            " and ( f.international = 'true' or t.international = 'true') ".
            " group by p.name ";
        $stmt2 = $QueryBuilder->query($sql);
        while ($row2 = $stmt2->fetch()) {
            if( strtoupper($row2['name']) == strtoupper($dados['name']) &&
                strtoupper($row2['name']) == strtoupper($dados['name'])) {
            } else {
                if(!isset($namesUsed[$row2['name']])) {
                    $dataset[] = array(
                        'pax_id' => $row2['pax_id'],
                        'name' => $row2['name'],
                        'registration_code' => $row2['registration_code'],
                        'is_diamond' => $row2['is_diamond'],
                    );
                    $namesUsed[$row2['name']] = 1;
                } else {
                    $namesUsed[$row2['name']]++;
                }
            }
        }

        $response->setDataset($dataset);
    }

    public function loadPaxUsedCards(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        if(isset($dados['internacional'])) {
            $internacional = $dados['internacional'];
        } else {
            $internacional = false;
        }

        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        $dataset = array();

        $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $dados['airline'] ) );
        $startDate = '2018-01-01';
        if($dados['airline'] == 'LATAM') {
            $startDate = '2018-08-09';
        } else if($dados['airline'] == 'AZUL') {
            $startDate = '2018-12-15';
        } else if($dados['airline'] == 'GOL') {
            $startDate = new \DateTime();
            $startDate = $startDate->format('Y-01-01');
        }

        if($internacional == false || $internacional == 'false') {
            if($Airline->getMaxPaxField() == 'name') {
                $where = " p.name like '". getFirstName($dados['pax_name'], $dados['paxLastName'], $dados['paxAgnome']) ." %' and p.name like '% ". getLastName($dados['pax_name'], $dados['paxLastName'], $dados['paxAgnome']) ."' ";
            } else {
                $where = " REPLACE(REPLACE(p.". $Airline->getMaxPaxField() .", '.', ''), '-', '') = '". clean($dados['identification'], $Airline->getMaxPaxField()) . "' ";
            }
    
            $sql = " select s.cards_id, b.name, b.registration_code, m.leftover from sale s ".
                " inner join businesspartner p on p.id = s.pax_id ".
                " inner join cards c on c.id = s.cards_id ".
                " inner join milesbench m on c.id = m.cards_id ".
                " inner join online_pax w on w.id = s.online_pax_id ".
                " inner join businesspartner b on b.id = c.businesspartner_id ".
                " inner join airport f on f.id = s.airport_from ".
                " inner join airport t on t.id = s.airport_to ".
                " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate .
                "' and ". $where .
                " and w.is_newborn = 'N' ".
                " group by s.cards_id ";
            // nacional

            error_log ("Query: ".$sql);
            $stmt2 = $QueryBuilder->query($sql);
            while ($row2 = $stmt2->fetch()) {
                $dataset[] = array(
                    'name' => $row2['name'],
                    'registration_code' => $row2['registration_code'],
                    'leftover' => (float)$row2['leftover'],
                );
            }
        } else {
            $sql = " select s.cards_id, b.name, b.registration_code, m.leftover from sale s ".
                " inner join businesspartner p on p.id = s.pax_id ".
                " inner join cards c on c.id = s.cards_id ".
                " inner join milesbench m on c.id = m.cards_id ".
                " inner join online_pax w on w.id = s.online_pax_id ".
                " inner join businesspartner b on b.id = c.businesspartner_id ".
                " inner join airport f on f.id = s.airport_from ".
                " inner join airport t on t.id = s.airport_to ".
                " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate .
                "' and p.name = '". $dados['pax_name'] . ($dados['paxLastName'] ? (' '.$dados['paxLastName']) : '') ."' ".
                " and w.is_newborn = 'N' ".
                " group by s.cards_id ";
            // inter
            $stmt2 = $QueryBuilder->query($sql);
            while ($row2 = $stmt2->fetch()) {
                $dataset[] = array(
                    'name' => $row2['name'],
                    'registration_code' => $row2['registration_code'],
                    'leftover' => (float)$row2['leftover'],
                );
            }
        }


        $response->setDataset($dataset);
    }

    public function loadAllPaxUsedCards(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        $dataset = array();
        $person = array();
        
        foreach ($dados as $key => $value) {
            
            $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $value['airline'] ) );
            if($value['airline'] == 'LATAM') {
                $startDate = '2018-08-09';
            } else if($value['airline'] == 'AZUL') {
                $startDate = '2018-12-15';
            } else if($value['airline'] == 'GOL') {
                $startDate = new \DateTime();
                $startDate = $startDate->format('Y-01-01');
            }

            if($Airline->getMaxPaxField() == 'name') {
                $where = " p.name like '". getFirstName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ." %' and p.name like '% ". getLastName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ."' ";
            } else {
                $where = " REPLACE(REPLACE(p.". $Airline->getMaxPaxField() .", '.', ''), '-', '') = '". clean($value['identification'], $Airline->getMaxPaxField()) ."' ";
            }

            $dataset[] = (object)[];
            $sql = " select s.cards_id, b.name, b.registration_code, m.leftover from sale s ".
                " inner join businesspartner p on p.id = s.pax_id ".
                " inner join cards c on c.id = s.cards_id ".
                " inner join milesbench m on c.id = m.cards_id ".
                " inner join businesspartner b on b.id = c.businesspartner_id ".
                " inner join online_pax w on w.id = s.online_pax_id ".
                " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" .
                $startDate . "' and  ". $where .
                " and w.is_newborn = 'N' ".
                " group by s.cards_id ";
    
            $stmt2 = $QueryBuilder->query($sql);
            while ($row2 = $stmt2->fetch()) {
                $dataset[] = array(
                    'name' => $row2['name'],
                    'registration_code' => $row2['registration_code'],
                    'leftover' => (float)$row2['leftover'],
                );
            }
        }

        $response->setDataset($dataset);
    }

    public function loadAllPaxesCardsUsed(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        if(isset($request->getRow()['internacional'])) {
            $internacional = $request->getRow()['internacional'];
        } else {
            $internacional = false;
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $oneYear = new \DateTime();
        $oneYear->modify('-1 year');
        $startDate = '2018-08-09';
        $count = 0;

        $baseSql = " select COUNT(s.cards_id) as quant from sale s ".
            " inner join businesspartner p on p.id = s.pax_id ".
            " inner join cards c on c.id = s.cards_id ".
            " inner join milesbench m on c.id = m.cards_id ".
            " inner join online_pax w on w.id = s.online_pax_id ".
            " inner join businesspartner b on b.id = c.businesspartner_id ".
            " inner join airport f on f.id = s.airport_from ".
            " inner join airport t on t.id = s.airport_to ";

        foreach ($dados as $key => $value) {
            $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $value['airline'] ));
            if($value['airline'] == 'LATAM' || $value['airline'] == 'AZUL' || $value['airline'] == 'GOL') {

                if($value['airline'] == 'AZUL') {
                    $startDate = '2018-12-15';
                } else if($value['airline'] == 'LATAM') {
                    $startDate = '2018-08-09';
                } else if($value['airline'] == 'GOL') {
                    $startDate = new \DateTime();
                    $startDate = $startDate->format('Y-01-01');
                }

                if($Airline->getMaxPaxField() == 'name') {
                    if($internacional == false || $internacional == 'false') {
                        $where = " p.name like '". getFirstName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ." %' and p.name like '% ". getLastName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ."' ";
                        $sql = $baseSql.
                            " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' ".
                            " and s.issue_date >= '" .
                            $startDate . "' and ". $where .
                            " and w.is_newborn = 'N' ".
                            " group by s.cards_id ";
                    } else {
                        $string = $value['pax_name'];
                        if(isset($value['paxLastName']) && $value['paxLastName'] != '') {
                            $string .= ' ' . $value['paxLastName'];
                        }
                        if(isset($value['paxAgnome']) && $value['paxAgnome'] != '') {
                            $string .= ' ' . $value['paxAgnome'];
                        }
                        $where = " p.name = '".$string."' ";

                        $sql = $baseSql.
                            " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' ".
                            " and s.issue_date >= '" .
                            $startDate . "' and ". $where .
                            " and w.is_newborn = 'N' ".
                            " group by s.cards_id ";
                    }
                } else {
                    $where = " REPLACE(REPLACE(p.". $Airline->getMaxPaxField() .", '.', ''), '-', '') = '". clean($value['identification'], $Airline->getMaxPaxField()) ."' ";
                    $sql = $baseSql.
                        " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' ".
                        " and s.issue_date >= '" .$startDate . "' and ". $where .
                        " and w.is_newborn = 'N' ".
                        " group by s.cards_id ";
                }

                $stmt2 = $QueryBuilder->query($sql);
                while ($row2 = $stmt2->fetch()) {
                    $dados[$key]['quant'] = $row2['quant'];
                    if((float)$row2['quant'] > 0) {
                        $dados[$key]['code'] = (float)((new \DateTime())->getTimestamp()) + $count;
                        $count++;
                    }
                }
            }
        }
        $response->setDataset($dados);
    }

    public function checkValidMaxPaxPerCard(Request $request, Response $response) {
        $dados = $request->getRow()['data'];
        $onlineflights = $request->getRow()['onlineflights'];
        $operation = $request->getRow()['operation'];
        $flight_selected = $request->getRow()['flight_selected'];
        if($operation == '2' && ($dados['airline'] == 'LATAM' || $dados['airline'] == 'AZUL' || $dados['airline'] == 'GOL')) {
            $em = Application::getInstance()->getEntityManager();
            $QueryBuilder = Application::getInstance()->getQueryBuilder();

            $Airline = $em->getRepository('Airline')->findOneBy( array( 'name' => $dados['airline'] ));
            $Cards = $em->getRepository('Cards')->find($dados['cards_id']);
            $oneYear = new \DateTime();
            $oneYear->modify('-1 year');

            if($dados['airline'] == 'AZUL') {
                $startDate = '2018-12-15';
            } else if($dados['airline'] == 'LATAM') {
                $startDate = '2018-08-09';
            } else if($dados['airline'] == 'GOL') {
                $startDate = new \DateTime();
                $startDate = $startDate->format('Y-01-01');
            }

            if($Airline->getMaxPaxField() == 'name') {
                $where = " p.name like '". getFirstName($flight_selected['pax_name'], $flight_selected['paxLastName'], $flight_selected['paxAgnome']) ." %' and p.name like '% ". getLastName($flight_selected['pax_name'], $flight_selected['paxLastName'], $flight_selected['paxAgnome']) ."' ";
            } else {
                $where = " REPLACE(REPLACE(p.". $Airline->getMaxPaxField() .", '.', ''), '-', '') = '". clean($flight_selected['identification'], $Airline->getMaxPaxField()) ."' ";
            }

            $valid = false;
            $sql = " select s.cards_id, p.name, p.registration_code from sale s ".
                " inner join businesspartner p on p.id = s.pax_id ".
                " inner join online_pax w on w.id = s.online_pax_id ".
                " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" .
                $startDate . "'".
                " and w.is_newborn = 'N' ".
                " and s.cards_id = ". $dados['cards_id'] ." and ". $where ." ";
            $stmt = $QueryBuilder->query($sql);
            while ($row = $stmt->fetch()) {
                $valid = true;
            }

            if( strtoupper(getFirstName($dados['name'], '', '')) == strtoupper(getFirstName($flight_selected['pax_name'], $flight_selected['paxLastName'], $flight_selected['paxAgnome'])) &&
                strtoupper(getLastName($dados['name'], '', '')) == strtoupper(getLastName($flight_selected['pax_name'], $flight_selected['paxLastName'], $flight_selected['paxAgnome'])) ) {
                    $valid = true;
            }

            if($valid) {

                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::SUCCESS);
                $message->setText('Valido');
                $response->addMessage($message);
            } else {

                $namesUsed = [];
                $sales = 0;
                $count = 0;
                $sql = " select p.id as passageiros, p.name from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " inner join airport f on f.id = s.airport_from ".
                    " inner join airport t on t.id = s.airport_to ".
                    " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.cards_id = ". $dados['cards_id'] .
                    " and s.issue_date >= '" . $startDate . "'".
                    " and w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' ".
                    " GROUP by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
                $stmt2 = $QueryBuilder->query($sql);
                while ($row2 = $stmt2->fetch()) {
                    if(!isset($namesUsed[$row2['name']])) {
                        $namesUsed[$row2['name']] = 1;
                        $sales++;
                    } else {
                        $namesUsed[$row2['name']]++;
                    }
                }

                $sql = " select p.id as passageiros, p.name from sale s ".
                    " inner join businesspartner p on p.id = s.pax_id ".
                    " inner join online_pax w on w.id = s.online_pax_id ".
                    " inner join airport f on f.id = s.airport_from ".
                    " inner join airport t on t.id = s.airport_to ".
                    " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.cards_id = ". $dados['cards_id'] .
                    " and s.issue_date >= '" . $startDate . "'".
                    " and (f.international = 'true' or t.international = 'true') ".
                    " GROUP by p.name ";
                $stmt2 = $QueryBuilder->query($sql);
                while ($row2 = $stmt2->fetch()) {
                    if(!isset($namesUsed[$row2['name']])) {
                        $namesUsed[$row2['name']] = 1;
                        $sales++;
                    } else {
                        $namesUsed[$row2['name']]++;
                    }
                }

                $alrealdSumed = [];
                foreach ($onlineflights as $key => $value) {
                    if($Airline->getMaxPaxField() == 'name') {
                        $where = " p.name like '". getFirstName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ." %' and p.name like '% ". getLastName($value['pax_name'], $value['paxLastName'], $value['paxAgnome']) ."' ";
                    } else {
                        $where = " REPLACE(REPLACE(p.". $Airline->getMaxPaxField() .", '.', ''), '-', '') = '". clean($value['identification'], $Airline->getMaxPaxField()) ."' ";
                    }

                    $validToSum = true;
                    $sql = " select s.cards_id, p.name, p.registration_code from sale s ".
                        " inner join businesspartner p on p.id = s.pax_id ".
                        " inner join online_pax w on w.id = s.online_pax_id ".
                        " where s.airline_id = ".$Airline->getId()." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' ".
                        " and w.is_newborn = 'N' ".
                        " and s.cards_id = ". $dados['cards_id'] ." and ". $where ." ";
                    $stmt = $QueryBuilder->query($sql);
                    while ($row = $stmt->fetch()) {
                        $validToSum = false;
                    }

                    if($value['cards_id'] == $dados['cards_id'] && $validToSum == true) {
                        if( !isset($alrealdSumed[$value['identification']]) ) {
                            $alrealdSumed[$value['identification']] = 0;
                            $count++;
                        }
                    }
                }

                $maxCount = (int)$Airline->getMaxPerPax();
                if((int)$Cards->getMaxPerPax() != 0) {
                    $maxCount = (int)$Cards->getMaxPerPax();
                }

                if($flight_selected['is_newborn'] == 'S') {
                    $count--;
                }

                if($count + $sales >= $maxCount && !isset($alrealdSumed[$flight_selected['identification']]) ) {
                    $message = new \MilesBench\Message();
                    $message->setType(\MilesBench\Message::ERROR);
                    $message->setText('Não valido');
                    $response->addMessage($message);
                } else {
                    $message = new \MilesBench\Message();
                    $message->setType(\MilesBench\Message::SUCCESS);
                    $message->setText('Valido');
                    $response->addMessage($message);
                }
            }

        } else {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Valido');
            $response->addMessage($message);
        }
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