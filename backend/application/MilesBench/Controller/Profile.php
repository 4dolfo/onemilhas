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

class Profile {

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}


        $em = Application::getInstance()->getEntityManager();
        $where = '';
        if (isset($dados['id'])) {
            $where = "and b.id = ".$dados['id'];
        }

        $sql = "select b FROM Businesspartner b WHERE b.partnerType like '%U%' ".$where;
        $query = $em->createQuery($sql);
        $BusinessPartner = $query->getResult();

        $dataset = array();
        foreach($BusinessPartner as $Profile){
            $City = $Profile->getCity();
            if ($City) {
                $cityfullname = $City->getName() . ', ' . $City->getState();
                $cityname = $City->getName();
                $citystate = $City->getState();
            } else {
                $cityfullname = '';
                $cityname = '';
                $citystate = '';
            }

            $dealer = false;
            $types = explode('_', $Profile->getPartnerType());
            foreach($types as $type){
                if($type == 'D') {
                    $dealer = true;
                }
            }

            $dataset[] = array(
                'id' => $Profile->getId(),
                'name' => $Profile->getName(),
                'registrationCode' => $Profile->getRegistrationCode(),
                'city' => $cityname,
                'state' => $citystate,
                'cityfullname' => $cityfullname,
                'adress' => $Profile->getAdress(),
                'partnerType' => $Profile->getPartnerType(),
                'email' => $Profile->getEmail(),
                'is_master' => ($Profile->getIsMaster() == 'true'),
                'phoneNumber' => $Profile->getPhoneNumber(),
                'phoneNumber2' => $Profile->getPhoneNumber2(),
                'phoneNumber3' => $Profile->getPhoneNumber3(),
                'masterCode' => $Profile->getMasterCode(),
                'password' => $Profile->getPassword(),
                'bloqued' => ($Profile->getStatus() == 'Bloqueado'),
                'dealer' => $dealer
            );

        }
        $response->setDataset($dataset);
    }

    public function loadProfile(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $Profile = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));

        $dataset = array();

        $City = $Profile->getCity();
        if ($City) {
            $cityfullname = $City->getName() . ', ' . $City->getState();
            $cityname = $City->getName();
            $citystate = $City->getState();
        } else {
            $cityfullname = '';
            $cityname = '';
            $citystate = '';
        }

        $sales = 0;
        $purchases = 0;

        $sundayIn = '';
        $mondayIn = '';
        $tuesdayIn = '';
        $wednesdayIn = '';
        $thursdayIn = '';
        $fridayIn = '';
        $saturdayIn = '';

        $sundayOut = '';
        $mondayOut = '';
        $tuesdayOut = '';
        $wednesdayOut = '';
        $thursdayOut = '';
        $fridayOut = '';
        $saturdayOut = '';

        $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $Profile->getId()));

        if($UserPermission){
        
            $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');

            if($UserPermission->getWizardSale() == "true") {
                $sql = "select COUNT(s.id) as sales FROM Sale s where s.user = '".$Profile->getId()."' and s.issueDate >= '".$monthsAgo->format('Y-m-d')."' ";
                $query = $em->createQuery($sql);
                $Sales = $query->getResult();

                $sales = $Sales[0]['sales'];
            }

            if($UserPermission->getWizardPurchase() == "true") {
                $sql = "select COUNT(p.id) as purchases FROM Purchase p where p.user = '".$Profile->getId()."' and p.purchaseDate >= '".$monthsAgo->format('Y-m-d')."' ";
                $query = $em->createQuery($sql);
                $Purchases = $query->getResult();

                $purchases = $Purchases[0]['purchases'];
            }

            
            if($UserPermission->getSundayIn()) {
                $sundayIn = $UserPermission->getSundayIn()->format('Y-m-d H:i:s');                
            }
            if($UserPermission->getMondayIn()) {
                $mondayIn = $UserPermission->getMondayIn()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getTuesdayIn()) {
                $tuesdayIn = $UserPermission->getTuesdayIn()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getWednesdayIn()) {
                $wednesdayIn = $UserPermission->getWednesdayIn()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getThursdayIn()) {
                $thursdayIn = $UserPermission->getThursdayIn()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getFridayIn()) {
                $fridayIn = $UserPermission->getFridayIn()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getSaturdayIn()) {
                $saturdayIn = $UserPermission->getSaturdayIn()->format('Y-m-d H:i:s');
            }

            if($UserPermission->getSundayOut()) {
                $sundayOut = $UserPermission->getSundayOut()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getMondayOut()) {
                $mondayOut = $UserPermission->getMondayOut()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getTuesdayOut()) {
                $tuesdayOut = $UserPermission->getTuesdayOut()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getWednesdayOut()) {
                $wednesdayOut = $UserPermission->getWednesdayOut()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getThursdayOut()) {
                $thursdayOut = $UserPermission->getThursdayOut()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getFridayOut()) {
                $fridayOut = $UserPermission->getFridayOut()->format('Y-m-d H:i:s');
            }
            if($UserPermission->getSaturdayOut()) {
                $saturdayOut = $UserPermission->getSaturdayOut()->format('Y-m-d H:i:s');
            }
        }

        $state = '0';
        $city = '';
        if($Profile->getCity()) {
            $state = $Profile->getCity()->getState();
            $city = $Profile->getCity()->getName();
        }

        $dataset[] = array(
            'sales' => (int)$sales,
            'purchases' => (int)$purchases,
            'sundayIn' => $sundayIn,
            'mondayIn' => $mondayIn,
            'tuesdayIn' => $tuesdayIn,
            'wednesdayIn' => $wednesdayIn,
            'thursdayIn' => $thursdayIn,
            'fridayIn' => $fridayIn,
            'saturdayIn' => $saturdayIn,
            'sundayOut' => $sundayOut,
            'mondayOut' => $mondayOut,
            'tuesdayOut' => $tuesdayOut,
            'wednesdayOut' => $wednesdayOut,
            'thursdayOut' => $thursdayOut,
            'fridayOut' => $fridayOut,
            'saturdayOut' => $saturdayOut,
            'id' => $Profile->getId(),
            'name' => $Profile->getName(),
            'email' => $Profile->getEmail(),
            'acessName' => $Profile->getAcessName(),
            'is_master' => $Profile->getIsMaster(),
            'adress' => $Profile->getAdress(),
            'phoneNumber' => $Profile->getPhoneNumber(),
            'phoneNumber2' => $Profile->getPhoneNumber2(),
            'phoneNumber3' => $Profile->getPhoneNumber3(),
            'registrationCode' => $Profile->getRegistrationCode(),
            'state' => $state,
            'city' => $city
        );

        $response->setDataset(array_shift($dataset));
    }

    public function loadSelfSales(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $Profile = $em->getRepository('Businesspartner')->findOneBy(array('id' => $dados['id']));

        for ($i=15; $i >= 0; $i--) { 
            $monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' day');
            $monthAgo = (new \DateTime())->modify('today')->modify('-'.($i-1).' day');

            $sql = "select COUNT(s.id) as sales FROM Sale s where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' and s.user = '".$Profile->getId()."' ";
            $query = $em->createQuery($sql);
            $UserSales = $query->getResult();

            $dataset[] = array(
                'UserSales' => $UserSales[0]['sales'],
                'month' => $monthsAgo->format('Y-m-d')
            );
        }
        $response->setDataset($dataset);
    }

    public function saveProfilePicture(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['file'])) {
            $file = $dados['file'];
        }

        try {

            move_uploaded_file($file['tmp_name'], getcwd().'/../../frontend/images/'.$dados['id'].'.jpg');

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Arquivo(s) salvos com sucesso!');
            $response->addMessage($message);

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function changePassword(Request $request, Response $response) {
        $hashId = $request->getRow()['hashId'];
        $dados = $request->getRow()['data'];

        try {
            $em = Application::getInstance()->getEntityManager();
            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
            if($UserSession) {
                // $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
                $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U'));
                if(!$UserPartner) {
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U_P'));
                }
                if(!$UserPartner) {
                    $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U_D'));
                }
                if($UserPartner) {

                    $BusinesspartnerPasswords = $em->getRepository('BusinesspartnerPasswords')->findOneBy(array('businesspartner' => $UserPartner->getId(), 'password' => $dados['password1']));
                    if($BusinesspartnerPasswords) {
                        throw new \Exception("A senha não pode ja ter sido utilizada!");
                    }

                    $BusinesspartnerPasswords = new \BusinesspartnerPasswords();
                    $BusinesspartnerPasswords->setIssueDate(new \DateTime());
                    $BusinesspartnerPasswords->setPassword($dados['password1']);
                    $BusinesspartnerPasswords->setBusinesspartner($UserPartner);
                    $em->persist($BusinesspartnerPasswords);
                    $em->flush($BusinesspartnerPasswords);

                    $UserPartner->setPassword($dados['password1']);
                    $UserPartner->setLastPasswordDate(new \DateTime());
                    $em->persist($UserPartner);
                    $em->flush($UserPartner);
                }
            }
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Senha alterada com sucesso!');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}