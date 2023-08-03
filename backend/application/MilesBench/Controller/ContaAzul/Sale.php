<?php

namespace MilesBench\Controller\ContaAzul;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Sale {

    public static function registerClient($Client, $Businesspartner) {
        if( !is_null($Client->getContaAzulId()) ) {
            return $Client;
        }

        if( (new \DateTime())->diff($Businesspartner->getContaAzulLastUpdate())->h > 0 ) {
            $Businesspartner = \MilesBench\Controller\ContaAzul\Sale::refreshAuthorizationCode($Businesspartner->getContaAzulRefreshToken(), $Businesspartner);
        }

        $postFields = array(
            "name" => $Client->getName(),
            "company_name" => $Client->getCompanyName(),
            "person_type" => "LEGAL"
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Controller\ContaAzul\Constants::url_customers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json",
            'Authorization: Bearer ' . $Businesspartner->getContaAzulToken()));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $content = "<br>".$result."<br><brSRM-IT";
        $email1 = 'emissao@onemilhas.com.br';
        $email2 = 'adm@onemilhas.com.br';
        $postfields = array(
            'content' => $content,
            'from' => $email1,
            'partner' => $email2,
            'subject' => 'CONTAAZUL - NEWCUSTOMER',
            'type' => ''
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $resultEmail = curl_exec($ch);

        $responseJson = json_decode($result, true);
        $Client->setContaAzulId($responseJson['id']);

        return $Client;
    }

    public static function createSaleByArray($billetreceive, $Businesspartner, $customer_id, $nosso_numero = null) {
        if($billetreceive->getSentContaAzul() == 'true' || (float)$billetreceive->getActualValue() <= 0) {
            return $billetreceive;
        }

        if( (new \DateTime())->diff($Businesspartner->getContaAzulLastUpdate())->h > 0 ) {
            $Businesspartner = \MilesBench\Controller\ContaAzul\Sale::refreshAuthorizationCode($Businesspartner->getContaAzulRefreshToken(), $Businesspartner);
        }

        $status = 'PENDING';
        if($billetreceive->getStatus() == 'B') {
            $status = 'ACQUITTED';
        }

        $postFields = array(
            "number" => $nosso_numero == null ? $billetreceive->getOurNumber() : $nosso_numero,
            "emission" => $billetreceive->getIssueDate()->format('Y-m-d') .'T'.$billetreceive->getIssueDate()->format('H:i:s').'.000Z',
            "status" => "COMMITTED",
            "customer_id" => $customer_id,
            "products" => array(
                array(
                    "quantity" => 1,
                    "value" => (float)$billetreceive->getActualValue(),
                    "product_id" => "7e578a4c-934d-47b6-afbd-95590ee0c2a3"
                )
            ),
            "payment" => array(
                "type" => "CASH",
                "installments" => array(
                    array(
                        "number" => 0,
                        "value" => (float)$billetreceive->getActualValue(),
                        "due_date" => $billetreceive->getDueDate()->format('Y-m-d') .'T04:00:00.000Z',
                        "status" => 'PENDING'
                    )
                ),
            ),
            "notes" => "Bordero do cliente " . $billetreceive->getClient()->getName(),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Controller\ContaAzul\Constants::url_sales);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json",
            'Authorization: Bearer ' . $Businesspartner->getContaAzulToken()));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $content = "<br>".$result."<br><br><br>".json_encode($postFields)."<br>SRM-IT";
        $email1 = 'emissao@onemilhas.com.br';
        $email2 = 'adm@onemilhas.com.br';
        $postfields = array(
            'content' => $content,
            'from' => $email1,
            'partner' => $email2,
            'subject' => 'CONTAAZUL - NOVAVENDA',
            'type' => ''
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $resultEmail = curl_exec($ch);

        $billetreceive->setSentContaAzul('true');

        return $billetreceive;
    }

    public function authorize(Request $request, Response $response) {
        $HostServer = getenv('HostServer') ? getenv('HostServer') : '52.70.119.195';
        $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';

        $url = \MilesBench\Controller\ContaAzul\Constants::url_authorize;

        $url = str_replace('52.70.119.195', $HostServer, $url);
        $url = str_replace('cml-gestao', $DirServer, $url);
        $response->setDataset(array(
            'url' => $url. $this->generateRandomString()
        ));
    }

    public static function exchangeAuthorizationCode($Businesspartner, $code_token) {
        $HostServer = getenv('HostServer') ? getenv('HostServer') : '52.70.119.195';
        $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
        
        $url = \MilesBench\Controller\ContaAzul\Constants::url_oauth;
        
        $url = str_replace('52.70.119.195', $HostServer, $url);
        $url = str_replace('cml-gestao', $DirServer, $url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $code_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json",
            'Authorization: Basic ' .base64_encode( \MilesBench\Controller\ContaAzul\Constants::client_id . ':' . \MilesBench\Controller\ContaAzul\Constants::client_secret )));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $json_response = json_decode($result, true);
        if(isset($json_response['access_token'])) {
            $Businesspartner->setContaAzulToken($json_response['access_token']);
            $Businesspartner->setContaAzulRefreshToken($json_response['refresh_token']);
            $Businesspartner->setContaAzulLastUpdate(new \DateTime());
        }
        return $Businesspartner;
    }

    public static function refreshAuthorizationCode($refresh_token, $Businesspartner) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Controller\ContaAzul\Constants::url_refresh . $refresh_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json",
            'Authorization: Basic ' .base64_encode( \MilesBench\Controller\ContaAzul\Constants::client_id . ':' . \MilesBench\Controller\ContaAzul\Constants::client_secret )));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $content = "<br>".$result."<br><brSRM-IT";
        $email1 = 'emissao@onemilhas.com.br';
        $email2 = 'adm@onemilhas.com.br';
        $postfields = array(
            'content' => $content,
            'from' => $email1,
            'partner' => $email2,
            'subject' => 'CONTAAZUL - REFRESHTOKEN',
            'type' => ''
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $resultEmail = curl_exec($ch);

        $json_response = json_decode($result, true);
        if(isset($json_response['access_token'])) {
            $Businesspartner->setContaAzulToken($json_response['access_token']);
            $Businesspartner->setContaAzulRefreshToken($json_response['refresh_token']);
            $Businesspartner->setContaAzulLastUpdate(new \DateTime());
        }
        return $Businesspartner;
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}