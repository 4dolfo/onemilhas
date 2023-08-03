<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class InternalCards {

    public function loadInternalCards(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $sql = "select i FROM InternalCards i ";
        $where = '';
        if(isset($dados)) {
            if(isset($dados['archived']) && $dados['archived'] != '') {;
                if($dados['archived'] === true || $dados['archived'] === 'true') {
                    $where .= " where i.status in ('Aprovado', 'Bloqueado', 'Arquivado') ";
                }
            }
        }
        if($where == '') {
            $where .= " where i.status <> 'Arquivado' ";
        }

        $query = $em->createQuery($sql . $where);
        $InternalCards = $query->getResult();

        $dataset = array();
        foreach($InternalCards as $Cards){
            $priorityAirline = $Cards->getPriorityAirline();
            
            $Airlines = explode("_", $priorityAirline);
            
            $Airline = array();
            for ($i = 0; $i <= count($Airlines)-1; $i++) {
                $airline = $Airlines[$i];

                $priorityAirline = $em->getRepository('Airline')->findOneBy(array('id' => $airline));
                if(isset($priorityAirline)){
                    $Airline[] = $priorityAirline->getName();
                }
            }

            if($Cards->getShowBirthdate() != null){
                $birthdate = $Cards->getShowBirthdate()->format('Y-m-d');
            } else {
                $birthdate = '';
            }

            $prodider_exclusive = '';
            if($Cards->getProvider()) {
                $prodider_exclusive = $Cards->getProvider()->getName();
            }

            $dataset[] = array(
                'id' => $Cards->getId(),
                'card_number' => $Cards->getCardNumber(),
                'provider_name' => $Cards->getShowName(),
                'provider_adress' => $Cards->getShowAdress(),
                'provider_registration' => $Cards->getShowRegistration(),
                'birthdate' => $birthdate,
                'password' => $Cards->getCardPassword(),
                'card_type' => $Cards->getCardType(),
                'priority_airline' => array_merge($Airline),
                'status' => $Cards->getStatus(),
                'limit' => (float)$Cards->getCardLimit(),
                'used' => (float)$Cards->getCardUsed(),
                'providerPhone' => $Cards->getPhone(),
                'providerEmail' => $Cards->getProviderEmail(),
                'due_date' => $Cards->getDueDate()->format('Y-m-d'),
                'providerAdress' => $Cards->getProviderAdress(),
                'prodider_exclusive' => $prodider_exclusive
            );
        }
        $response->setDataset($dataset);
    }

    public function loadCardProvider(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();
        if(isset($dados['card_number'])){
            $Cards = $em->getRepository('Cards')->findOneBy(array('cardNumber' => $dados['card_number']));

            if($Cards) {
                $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('provider' => $Cards->getBusinesspartner()->getId()));
                if($InternalCards) {
                    if($InternalCards->getShowBirthdate() != null){
                        $birthdate = $InternalCards->getShowBirthdate()->format('Y-m-d H:i:s');
                    } else {
                        $birthdate = '';
                    }

                    $dataset[] = array(
                        'id' => $InternalCards->getId(),
                        'card_number' => $InternalCards->getCardNumber(),
                        'password' => $InternalCards->getCardPassword(),
                        'card_type' => $InternalCards->getCardType(),
                        'status' => $InternalCards->getStatus(),
                        'limit' => (float)$InternalCards->getCardLimit(),
                        'used' => (float)$InternalCards->getCardUsed(),
                        'due_date' => $InternalCards->getDueDate()->format('Y-m-d H:i:s'),
                        'provider_name' => $InternalCards->getShowName(),
                        'provider_adress' => $InternalCards->getShowAdress(),
                        'provider_registration' => $InternalCards->getShowRegistration(),
                        'providerPhone' => $InternalCards->getPhone(),
                        'providerEmail' => $InternalCards->getProviderEmail(),
                        'birthdate' => $birthdate,
                        'providerAdress' => $InternalCards->getProviderAdress()
                    );
                }
            }

            if( $Cards && $Cards->getCardTax() && !isset($InternalCards)) {
                $InternalCards = $Cards->getCardTax();

                if($InternalCards->getShowBirthdate() != null){
                    $birthdate = $InternalCards->getShowBirthdate()->format('Y-m-d H:i:s');
                } else {
                    $birthdate = '';
                }

                $dataset[] = array(
                    'id' => $InternalCards->getId(),
                    'card_number' => $InternalCards->getCardNumber(),
                    'password' => $InternalCards->getCardPassword(),
                    'card_type' => $InternalCards->getCardType(),
                    'status' => $InternalCards->getStatus(),
                    'limit' => (float)$InternalCards->getCardLimit(),
                    'used' => (float)$InternalCards->getCardUsed(),
                    'due_date' => $InternalCards->getDueDate()->format('Y-m-d H:i:s'),
                    'provider_name' => $InternalCards->getShowName(),
                    'provider_adress' => $InternalCards->getShowAdress(),
                    'provider_registration' => $InternalCards->getShowRegistration(),
                    'providerPhone' => $InternalCards->getPhone(),
                    'providerEmail' => $InternalCards->getProviderEmail(),
                    'birthdate' => $birthdate,
                    'providerAdress' => $InternalCards->getProviderAdress()
                );
            }else{
                $InternalCards = $em->getRepository('InternalCards')->findBy(array('priorityAirline' => $dados['airline'], 'status' => 'Aprovado'));
                if(!($InternalCards)){
                    $InternalCards = $em->getRepository('InternalCards')->findBy(array('status' => 'Aprovado'));
                }
        
                foreach($InternalCards as $Internals){
                    if($Internals->getShowBirthdate() != null){
                        $birthdate = $Internals->getShowBirthdate()->format('Y-m-d H:i:s');
                    } else {
                        $birthdate = '';
                    }

                    $dataset[] = array(
                        'id' => $Internals->getId(),
                        'card_number' => $Internals->getCardNumber(),
                        'password' => $Internals->getCardPassword(),
                        'card_type' => $Internals->getCardType(),
                        'status' => $Internals->getStatus(),
                        'limit' => (float)$Internals->getCardLimit(),
                        'used' => (float)$Internals->getCardUsed(),
                        'due_date' => $Internals->getDueDate()->format('Y-m-d H:i:s'),
                        'provider_name' => $Internals->getShowName(),
                        'provider_adress' => $Internals->getShowAdress(),
                        'provider_registration' => $Internals->getShowRegistration(),
                        'providerPhone' => $Internals->getPhone(),
                        'providerEmail' => $Internals->getProviderEmail(),
                        'birthdate' => $birthdate,
                        'providerAdress' => $Internals->getProviderAdress()
                    );
                }
            }
        }
        $response->setDataset($dataset);
    }

    public function saveInternal(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        
        try{

            if(isset($dados['id'])){
                $InternalCards = $em->getRepository('InternalCards')->findOneBy(array('id' => $dados['id']));
            }else{
                $InternalCards = new \InternalCards();
            }

            $InternalCards->setCardNumber($dados['card_number']);
            $InternalCards->setCardPassword($dados['password']);
            $InternalCards->setCardType($dados['card_type']);
            $InternalCards->setStatus($dados['status']);
            $InternalCards->setCardLimit($dados['limit']);
            $InternalCards->setPhone($dados['providerPhone']);
            $InternalCards->setProviderEmail($dados['providerEmail']);
            $InternalCards->setDueDate(new \Datetime($dados['_due_date']));

            $InternalCards->setShowName($dados['provider_name']);
            $InternalCards->setShowAdress($dados['provider_adress']);
            $InternalCards->setShowRegistration($dados['provider_registration']);
            if(isset($dados['_birthdate']) && $dados['_birthdate'] != '') {
                $InternalCards->setShowBirthdate(new \Datetime($dados['_birthdate']));
            }

            if(isset($dados['providerAdress']) && $dados['providerAdress'] != '') {
                $InternalCards->setProviderAdress($dados['providerAdress']);
            }

            if(isset($dados['priority_airline']) && $dados['priority_airline'] != ''){
                $airlines = '';
                $and = '';
                for ($i = 0; $i <= count($dados['priority_airline'])-1; $i++) {
                    $airline = $dados['priority_airline'][$i];
                    $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $airline));
                    if(isset($Airline) && $Airline != null){
                        $airlines = $airlines.$and.$Airline->getId();
                        $and = '_';
                    }
                }
                $InternalCards->setPriorityAirline($airlines);
            }

            if(isset($dados['prodider_exclusive']) && $dados['prodider_exclusive'] != '') {
                $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['prodider_exclusive']));
                if($Businesspartner) {
                    $InternalCards->setProvider($Businesspartner);
                }
            }

            if(isset($dados['used']) && $dados['used'] != ''){
                $InternalCards->setCardUsed($dados['used']);
            }else
            {
                $InternalCards->setCardUsed(0);
            }

            $em->persist($InternalCards);
            $em->flush($InternalCards);

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
}