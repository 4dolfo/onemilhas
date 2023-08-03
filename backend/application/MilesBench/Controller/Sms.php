<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Twilio\Rest\Client;

use Aws\Sns\SnsClient;

class Sms {

    public static function send($to, $message, $from) {
        $em = Application::getInstance()->getEntityManager();

        try {

            $Sms = new \Sms();
            $Sms->setToNumber($to);
            $Sms->setMessage($message);

            // removing spaces
            while (strrpos($to, " ") !== false) {
                $to = str_replace(" ", "", $to);
            }

            // removing parentheses
            $to = str_replace("(", "", $to);
            $to = str_replace(")", "", $to);

            // removing dash
            while (strrpos($to, "-") !== false) {
                $to = str_replace("-", "", $to);
            }

            // validations
            if(strlen($to) == 8) {
                $to = '9' . $to;
            }
            if(strlen($to) == 9) {
                $to = '5531' . $to;
            }
            if(strlen($to) == 11) {
                $to = '55' . $to;
            }

            // return Sms::twilio($to, $message, $from);
            // return Sms::infoBip($to, $message, $from);
            return Sms::zenvia($to, $message, $from);
            // return Sms::amazon($to, $message, $from);

        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>mensagem:".$message."<br><br>para:".$to."<br><br>SRM-IT";

            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - SMS',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            return array('statusDescription', 'ERRO', 'statusCode' => '-1');
        }
    }

    public static function twilio($to, $message, $from) {
        $em = Application::getInstance()->getEntityManager();

        try {

            $Sms = new \Sms();
            $Sms->setToNumber($to);
            $Sms->setMessage($message);

            // sending sms
            $sid = \MilesBench\Util::twilio_sid;
            $token = \MilesBench\Util::twilio_token;

            $client = new \Twilio\Rest\Client($sid, $token);
            $message = $client->messages->create(
                '+' . $to,
                array(
                    'from' => '12167162388',
                    'body' => $message
                )
            );

            $Sms->setStatusCode('');
            $Sms->setStatusDescription('');
            $Sms->setDetailCode('');
            $Sms->setDetailDescription('');
            $Sms->setIssueDate(new \Datetime());

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_POST['hashId']));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            $Sms->setSystemUser($UserPartner);

            $em->persist($Sms);
            $em->flush($Sms);

            return array('statusDescription', 'ok', 'statusCode' => '00');

        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>mensagem:".$message."<br><br>para:".$to."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - SMS',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            return array('statusDescription', 'ERRO', 'statusCode' => '-1');
        }
    }

    public static function zenvia($to, $message, $from) {
        $em = Application::getInstance()->getEntityManager();

        try {

            $Sms = new \Sms();
            $Sms->setToNumber($to);
            $Sms->setMessage($message);

            $postfields = array(
                'sendSmsRequest' => array(
                    "to" => $to,
                    "msg" => $message,
                    "aggregateId" => "23525",
                    "from" => $from
                )
            );

            // sending sms
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::sms_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic '.\MilesBench\Util::sms_key,
                'Accept: application/json'
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            // json to array
            $return = json_decode($result, true)['sendSmsResponse'];

            $Sms->setStatusCode($return['statusCode']);
            $Sms->setStatusDescription($return['statusDescription']);
            $Sms->setDetailCode($return['detailCode']);
            $Sms->setDetailDescription($return['detailDescription']);
            $Sms->setIssueDate(new \Datetime());

            $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_POST['hashId']));
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            $Sms->setSystemUser($UserPartner);

            $em->persist($Sms);
            $em->flush($Sms);

            return array('statusDescription', $return['statusDescription'], 'statusCode' => $return['statusCode']);

        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>mensagem:".$message."<br><br>para:".$to."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - SMS',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            return array('statusDescription', 'ERRO', 'statusCode' => '-1');
        }
    }

    public static function amazon($to, $message, $from) {
        $em = Application::getInstance()->getEntityManager();

        try {

            $Sms = new \Sms();
            $Sms->setToNumber($to);
            $Sms->setMessage($message);

            $params = array(
                'credentials' => array(
                    'key' => getenv('AWS_KEY'),
                    'secret' => getenv('AWS_SECRET'),
                ),
                'region' => 'us-east-1',
                'version' => 'latest'
            );
            $sns = new \Aws\Sns\SnsClient($params);
            
            $args = array(
                // "SenderID" => "SenderName",
                "SMSType" => "Transactional",
                "Message" => $message,
                "PhoneNumber" => '+' . $to
            );
            
            $result = $sns->publish($args);

            // $Sms->setStatusCode('ok');
            // $Sms->setStatusDescription('');
            // $Sms->setDetailCode('');
            // $Sms->setDetailDescription('');
            // $Sms->setIssueDate(new \Datetime());

            // $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $_POST['hashId']));
            // if($UserSession) {
            //     $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
            //     if($UserPartner) {
            //         $Sms->setSystemUser($UserPartner);
            //     }
            // }

            // $em->persist($Sms);
            // $em->flush($Sms);

            // return array('statusDescription', '', 'statusCode' => $return['statusCode']);
            return array('statusDescription', '', 'statusCode' => 'OK');
        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>mensagem:".$message."<br><br>para:".$to."<br><br>SRM-IT";

            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - SMS',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            return array('statusDescription', 'ERRO', 'statusCode' => '-1');
        }
    }

    public static function infoBip($to, $message, $from) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{ \"from\":\"InfoSMS\", \"to\":\"41793026727\", \"text\":\"Test SMS.\" }",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }

    public function build(Request $request, Response $response) {
        $dados = $request->getRow();

        try {
            if(!isset($dados['to']) || $dados['to'] == '') {
                throw new Exception("Destinatatio deve ser enviado", 1);
            }
            if(!isset($dados['message']) || $dados['message'] == '') {
                throw new Exception("Mensagem deve ser enviada", 1);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Operação realizada com sucesso!');
            $response->addMessage(Sms::send($dados['to'], $dados['message'], $dados['from']));
        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function buildAmazon(Request $request, Response $response) {
        $dados = $request->getRow();
        try {

            if(!isset($dados['to']) || $dados['to'] == '') {
                throw new Exception("Destinatatio deve ser enviado", 1);
            }
            if(!isset($dados['message']) || $dados['message'] == '') {
                throw new Exception("Mensagem deve ser enviada", 1);
            }

            // removing spaces
            while (strrpos($dados['to'], " ") !== false) {
                $dados['to'] = str_replace(" ", "", $dados['to']);
            }

            // removing parentheses
            $dados['to'] = str_replace("(", "", $dados['to']);
            $dados['to'] = str_replace(")", "", $dados['to']);

            // removing dash
            while (strrpos($dados['to'], "-") !== false) {
                $dados['to'] = str_replace("-", "", $dados['to']);
            }

            // validations
            if(strlen($dados['to']) == 8) {
                $dados['to'] = '9' . $dados['to'];
            }
            if(strlen($dados['to']) == 9) {
                $dados['to'] = '5531' . $dados['to'];
            }
            if(strlen($dados['to']) == 11) {
                $dados['to'] = '55' . $dados['to'];
            }

            Sms::amazon($dados['to'], $dados['message'], '');
        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}