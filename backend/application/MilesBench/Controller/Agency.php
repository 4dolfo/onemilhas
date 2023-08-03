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

class Agency {

    public function load(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $BusinessPartner = $em->getRepository('Businesspartner')->findBy(array('partnerType' => 'C'));

        $dataset = array();
        foreach($BusinessPartner as $Agency){
            $City = $Agency->getCity();
            if ($City) {
                $cityname = $City->getName() . ', ' . $City->getState();
            } else {
                $cityname = '';
            }            
            $dataset[] = array(
                'id' => $Agency->getId(),
                'name' => $Agency->getName(),
                'city' => $cityname,
                'registrationCode' => $Agency->getRegistrationCode(),
                'adress' => $Agency->getAdress(),
                'partnerType' => $Agency->getPartnerType(),
                'email' => $Agency->getEmail(),
                'phoneNumber' => $Agency->getPhoneNumber(),
                'phoneNumber2' => $Agency->getPhoneNumber2(),
                'phoneNumber3' => $Agency->getPhoneNumber3()
            );

        }
        $response->setDataset($dataset);
    }

}