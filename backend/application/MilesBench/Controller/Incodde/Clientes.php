<?php

namespace MilesBench\Controller\Incodde;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Clientes {

    public function updateRegistration(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            $Agency = $em->getRepository('Businesspartner')->find($dados['client_id']);
            if(!$Agency) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com o One Milhas!");
            }

            $UpdateRegistration = new \BusinesspartnerUpdateRegistration();
            if(isset($dados['client_name']) && $dados['client_name'] != '') {
                $UpdateRegistration->setClientName($dados['client_name']);
            }
            if(isset($dados['social_name']) && $dados['social_name'] != '') {
                $UpdateRegistration->setSocialName($dados['social_name']);
            }
            if(isset($dados['registration_code']) && $dados['registration_code'] != '') {
                $UpdateRegistration->setRegistrationCode($dados['registration_code']);
            }
            if(isset($dados['adress']) && $dados['adress'] != '') {
                $UpdateRegistration->setAdress($dados['adress']);
            }
            if(isset($dados['adress_number']) && $dados['adress_number'] != '') {
                $UpdateRegistration->setAdressNumber($dados['adress_number']);
            }
            if(isset($dados['adress_complement']) && $dados['adress_complement'] != '') {
                $UpdateRegistration->setAdressComplement($dados['adress_complement']);
            }
            if(isset($dados['adress_district']) && $dados['adress_district'] != '') {
                $UpdateRegistration->setAdressDistrict($dados['adress_district']);
            }
            if(isset($dados['zip_code']) && $dados['zip_code'] != '') {
                $UpdateRegistration->setZipCode($dados['zip_code']);
            }
            if(isset($dados['email']) && $dados['email'] != '') {
                $UpdateRegistration->setEmail($dados['email']);
            }
            if(isset($dados['phone_cel']) && $dados['phone_cel'] != '') {
                $UpdateRegistration->setPhoneCel($dados['phone_cel']);
            }
            if(isset($dados['phone_commercial']) && $dados['phone_commercial'] != '') {
                $UpdateRegistration->setPhoneCommercial($dados['phone_commercial']);
            }
            if(isset($dados['phone_residential']) && $dados['phone_residential'] != '') {
                $UpdateRegistration->setPhoneResidential($dados['phone_residential']);
            }
            if(isset($dados['contact']) && $dados['contact'] != '') {
                $UpdateRegistration->setContact($dados['contact']);
            }
            $UpdateRegistration->setBusinesspartner($Agency);

            $em->persist($UpdateRegistration);
            $em->flush($UpdateRegistration);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Alteração inserida com sucesso!');
            $response->addMessage($message);
            $response->setDataset( array( 'update_id' => $UpdateRegistration->getId() ) );

        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - BUSCA - LOGIN',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Ocorreu um erro na tentativa de login, a equipe One Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }

}