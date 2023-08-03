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

require dirname(__FILE__) . '/../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require dirname(__FILE__) . '/../../../vendor/phpmailer/phpmailer/src/Exception.php';
require dirname(__FILE__) . '/../../../vendor/phpmailer/phpmailer/src/SMTP.php';

class mail {

    public function __construct() {
        // PHPMailerAutoload('PHPMailer');
        // PHPMailerAutoload('pop3');
        // PHPMailerAutoload('SMTP');
    }

    public function decript($args) {
        $data = explode('320AB', $args);
        $finaly = "";
        for ($i=0; $i < count($data); $i++) {
            $finaly = $finaly.(chr($data[$i] / 320));
        }
        return $finaly;
    }

    public function SendMail(Request $request, Response $response){
        $dados = $request->getRow();
        if(isset($dados['raw_content'])){
            $raw_content = $dados['raw_content'];
        }
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['attachment'])) {
            $attachment = $dados['attachment'];
        }
        if (isset($dados['signiture'])) {
            $signiture = $dados['signiture'];
        }
        if (isset($dados['type'])) {
            $type = $dados['type'];
        }
        if(isset($dados['cards_data'])) {
            $cards_data = $dados['cards_data'];
        }
        $emailType = 'GMAIL';
        if(isset($dados['emailType'])) {
            $emailType = $dados['emailType'];
        }
        if (isset($dados['origin'])) {
			$origin = $dados['origin'];
        }
		if (isset($dados['data'])) {
			$dados = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();

        if (isset($request->getRow()['order'])) {
            $order = $request->getRow()['order'];
            
            if($order) {
                $Issuer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $order['client_login'], 'partnerType' => 'S'));
                if($Issuer) {
                    $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Issuer->getClient()));
                    $origin = $Client->getOrigin();
                }
            }
        }
        if (isset($request->getRow()['client'])) {
            $client = $request->getRow()['client'];
            if($client) {
                $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['id']));
                $origin = $Client->getOrigin();
            }
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        //Send mail using gmail
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
        $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
        $mail->Port = 465; // set the SMTP port for the GMAIL server
        
        $body = $dados['emailContent'];

        $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
        if($UserSession) {
            $UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
        }

        if(isset($type) && $type != '') {
            if($type == 'EMISSAO') {
                $email1 = 'emissao@onemilhas.com.br';
    
                $EmailsConfig = $em->getRepository('EmailsConfig')->findOneBy(array('email' => $email1));
                if($EmailsConfig) {
                    $mail->Password = $EmailsConfig->getPassword();
                } else {
                    $mail->Password = 'One@emissao#';
                }
                
            } else if($type == 'COMPRAS') {
                $email1 = 'onemilhas@onemilhas.com.br';
                $EmailsConfig = $em->getRepository('EmailsConfig')->findOneBy(array('email' => $email1));
                if($EmailsConfig) {
                    $mail->Password = $EmailsConfig->getPassword();
                } else {
                    $mail->Password = 'One@2021#';
                }
                
            } else if($type == 'COMERCIAL') {
                $email1 = 'suporte@onemilhas.com.br';

                $EmailsConfig = $em->getRepository('EmailsConfig')->findOneBy(array('email' => $email1));
                if($EmailsConfig) {
                    $mail->Password = $EmailsConfig->getPassword();
                } else {
                    $mail->Password = 'One@suporte#';
                }

            } else if($type == 'FINANCEIRO') {
                $email1 = 'financeiro@onemilhas.com.br';
    
                $EmailsConfig = $em->getRepository('EmailsConfig')->findOneBy(array('email' => $email1));
                if($EmailsConfig) {
                    $mail->Password = $EmailsConfig->getPassword();
                } else {
                    $mail->Password = 'One@financeiro2021!';
                }

            } else {
                $email1 = 'adm@onemilhas.com.br';
                $EmailsConfig = $em->getRepository('EmailsConfig')->findOneBy(array('email' => $email1));
                if($EmailsConfig) {
                    $mail->Password = $EmailsConfig->getPassword();
                } else {
                    $mail->Password = "One@adm100916*";
                }

            }
        } else {
            $email1 = 'adm@onemilhas.com.br';
            $EmailsConfig = $em->getRepository('EmailsConfig')->findOneBy(array('email' => $email1));
            if($EmailsConfig) {
                $mail->Password = $EmailsConfig->getPassword();
            } else {
                $mail->Password = 'One@adm100916*';
            }
        }

        error_log("chegou1");

        $mail->Username = $email1;
        $mail->SetFrom($email1);
        //$mail->AddReplyTo($email1);
        $mail->AddAddress($email1);

        $mail->IsHTML(true);
        $mail->CharSet = "UTF-8";
        //Typical mail data
        if(isset($dados['emailpartner']) && $dados['emailpartner'] != ''){
            if (is_array($dados['emailpartner'])) {
                foreach($dados['emailpartner'] as $emailpartner){
                    $mail->AddAddress($emailpartner);
                }
            } else {
                $email = explode(';', $dados['emailpartner']);
                foreach($email as $emailpartner){
                    $mail->AddAddress($emailpartner);
                }
            }
        } else {
            $email1 = 'adm@onemilhas.com.br';
            $mail->AddAddress($email1);
        }
        if(isset($dados['mailcc']) && $dados['mailcc'] != ''){
            $email = explode(';', $dados['mailcc']);
            foreach($email as $emailpartner){
                if($emailpartner != '') {
                    $mail->AddAddress($emailpartner);
                }
            }
        }
        if(isset($dados['mailcco'])){
            $email = explode(';', $dados['mailcco']);
            foreach($email as $emailpartner){
                if($emailpartner != '') {
                    $mail->addBCC($emailpartner);
                }
            }
        }

        if(is_dir(getcwd()."/MilesBench/files/temp")) {
            $path = getcwd()."/MilesBench/files/temp";
            $findFile = false;
            if(isset($attachment)) {
                $attachments = $attachment;
                if(is_array($attachments)) {
                    while (0 != count($attachments)) {

                        foreach ($attachments as $key => $fileAttachment) {
                            $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $fileAttachment);
                            $scanned_directory = array_diff(scandir($path), array('..', '.'));
                            foreach ($scanned_directory as $file) {
                                if($file == $file_name) {
                                    $mail->addAttachment($path.'/'.$file);
                                    unset($attachments[$key]);
                                }
                            }
                        }
                    }
                } else {
                    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $attachments);
                    while ($findFile == false) {
                        $scanned_directory = array_diff(scandir($path), array('..', '.'));
                        foreach ($scanned_directory as $file) {
                            if($file == $file_name) {
                                $mail->addAttachment($path.'/'.$file);
                                $findFile = true;
                            }
                        }
                    }
                }
            }
        }

        $searchForFiles = false;

        if( isset($emailType)) {
            $emails = '';
            $virgula = '';

            if(isset($dados['emailpartner']) && $dados['emailpartner'] != ''){
                if (is_array($dados['emailpartner'])) {
                    foreach($dados['emailpartner'] as $emailpartner){
                        $emails .= $virgula.$emailpartner;
                        $virgula = ',';
                    }
                } else {
                    $email = explode(';', $dados['emailpartner']);
                    foreach($email as $emailpartner){
                        $emails .= $virgula.$emailpartner;
                        $virgula = ', ';
                    }
                }
            } else {
                $emails = 'adm@onemilhas.com.br';
            }

            $SystemLog = new \SystemLog();
            $SystemLog->setIssueDate(new \Datetime());
            $SystemLog->setDescription("Email enviado para: ".$emails." - ".$emailType);
            $SystemLog->setLogType('EMAIL-CLIENT');

            if(isset($UserPartner))
                $SystemLog->setBusinesspartner($UserPartner);

            $em->persist($SystemLog);
            $em->flush($SystemLog);
        }

        if(isset($cards_data)) {
            $searchForFiles = true;
            $businessPartner = array();
            $checkPartner = true;
            foreach ($cards_data as $card) {
                $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $card['email_provider'], 'name' => $card['name_provider'], 'registrationCode' => $card['card_registrationCode']));
                if($Businesspartner) {
                    foreach ($businessPartner as $patrner) {
                        if($patrner == $Businesspartner->getId()) {
                            $checkPartner = false;
                        }
                    }
                    if($checkPartner) {
                        $businessPartner[] = $Businesspartner->getId();
                        $path = getcwd()."/MilesBench/files/".$Businesspartner->getId();
                        if(is_dir($path)) {
                            $scanned_directory = array_diff(scandir($path), array('..', '.'));
                            foreach ($scanned_directory as $file) {
                                $mail->addAttachment($path.'/'.$file);
                            }
                        }
                    }
                }
            }
        }


        $mail->Subject = $dados['subject'];
        $mail->Body = $body;

        error_log("chegou2");
        try{
            // $mail->Send();
            if(isset($attachment)){
                if(is_array($attachment)) {

                    if(!$mail->Send()) {

                        foreach ($attachment as $file) {
                            $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file);
                            if(is_dir(getcwd()."/MilesBench/files/temp")) {
                                $path = getcwd()."/MilesBench/files/temp";
                                $dir = dir($path);
                                while($file = $dir->read())
                                {
                                    if(($file != '.') && ($file != '..') && ($file == $file_name)) {
                                        unlink($path.'/'.$file);
                                    }
                                }
                                $dir->close();
                            }
                        }
                    } else {

                        foreach ($attachment as $file) {
                            $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file);
                            if(is_dir(getcwd()."/MilesBench/files/temp")) {
                                $path = getcwd()."/MilesBench/files/temp";
                                $dir = dir($path);
                                while($file = $dir->read())
                                {
                                    if(($file != '.') && ($file != '..') && ($file == $file_name)) {
                                        unlink($path.'/'.$file);
                                    }
                                }
                                $dir->close();
                            }
                        }
                    }

                } else {
                    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $attachment);
                    if(!$mail->Send()) {
                        if(is_dir(getcwd()."/MilesBench/files/temp")) {
                            $path = getcwd()."/MilesBench/files/temp";
                            $dir = dir($path);
                            while($file = $dir->read())
                            {
                                if(($file != '.') && ($file != '..') && ($file == $file_name)) {
                                    unlink($path.'/'.$file);
                                }
                            }
                            $dir->close();
                        }
                    } else {
                        if(is_dir(getcwd()."/MilesBench/files/temp")) {
                            $path = getcwd()."/MilesBench/files/temp";
                            $dir = dir($path);
                            while($file = $dir->read())
                            {
                                if(($file != '.') && ($file != '..') && ($file == $file_name)) {
                                    unlink($path.'/'.$file);
                                }
                            }
                            $dir->close();
                        }
                    }
                }
            } else {
                //error_log (print_r($mail, TRUE));
                error_log("chegou3");
                $mail->Send();
            }

            if(isset($type) && $type == 'FINANCEIRO') {
                if(isset($dados['client'])) {
                    $SystemLog = new \SystemLog();
                    $SystemLog->setIssueDate(new \Datetime());
                    $SystemLog->setDescription("Email enviado - ".$dados['subject']." - ".$dados['client']);
                    $SystemLog->setLogType('EMAIL');

                    $em->persist($SystemLog);
                    $em->flush($SystemLog);
                }

                if(isset($request->getRow()['raw_content']) && isset($request->getRow()['client'])) {
                    $raw_email = new \Emails();
                    $raw_email->setUser($Client);
                    $raw_email->setStatus('ENVIADO');
                    $raw_email->setPartner('');
                    $raw_email->setSubject($dados['subject']);
                    $raw_email->setType('RAW');
                    $raw_email->setContent($raw_content);
                    $raw_email->setDateToSend(new \DateTime());

                    $em->persist($raw_email);
                    $em->flush($raw_email);
                }
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Email enviado com sucesso');
            $response->addMessage($message);           

        } catch(Exception $e){
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($mail->ErrorInfo);
        }
    }

    public static function sendTransactional($from, $partner, $content, $subject, $bcc = null) {

        // send grid on emailServiceSRM
        $postfields = array(
            'content' => $content,
            'partner' => $partner,
            'bcc' => $bcc,
            'from' => $from,
            'subject' => $subject
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
