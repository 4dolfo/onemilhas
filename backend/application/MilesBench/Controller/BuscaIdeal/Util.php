<?php

namespace MilesBench\Controller\BuscaIdeal;

use MilesBench\Application;
use MilesBench\Model;

class Util {
    public static function validateIssuer($clientId, $issuerName) {
        $em = Application::getInstance()->getEntityManager();

        if(!isset($dados['login'])) {
            throw new \Exception("Login deve ser informado!");
        }

        $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
            array( 'name' => $issuerName, 'partnerType' => 'S' )
        );

        // fails on validation of the user
        if(!$BusinessPartner) {
            throw new \Exception("Login não encontrado!");
        }

        if($BusinessPartner->getId() != $clientId) {
            throw new \Exception("Dados divergentes!");
        }

        return true;
    }
}