<?php

namespace MilesBench\Controller\Incodde\BankMilhas;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Pedido {

    public function save(Request $request, Response $response) {
        $content = "<br>".$request->getRow()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
        $email1 = 'emissao@onemilhas.com.br';
        $email2 = 'adm@onemilhas.com.br';
        $postfields = array(
            'content' => $content,
            'from' => $email1,
            'partner' => $email2,
            'subject' => 'BANKMILHAS - INCODDE',
            'type' => ''
        );
        
        $env = getenv('ENV') ? getenv('ENV') : 'production';
        if($env != 'production') {
            $postfields['subject'] = '[HOMOLOGAÇÃO] BANKMILHAS - [HOMOLOGAÇÃO] - INCODDE';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $dados = json_decode($request->getRow(), true);

        try {
            $em = Application::getInstance()->getEntityManager();

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'registrationCode' => $dados['cpf'], 'partnerType' => 'P' )
            );

            if(!$BusinessPartner) {
                $BusinessPartner = new \Businesspartner();
                $BusinessPartner->setPartnerType('P');
                $BusinessPartner->setStatus('Pendente');
            }

            // dados fornecedor
            $BusinessPartner->setName(mb_strtoupper($dados['nome']));
            if(isset($dados['email'])) {
                $BusinessPartner->setEmail($dados['email']);
            }
            if(isset($dados['data_nascimento']) && $dados['data_nascimento'] != '') {
                $BusinessPartner->setBirthdate(new \DateTime($dados['data_nascimento']));
            }
            if(isset($dados['cpf']) && $dados['cpf'] != '') {
                $BusinessPartner->setRegistrationCode($dados['cpf']);
            }
            if(isset($dados['telefone_celular']) && $dados['telefone_celular'] != '') {
                $BusinessPartner->setPhoneNumber($dados['telefone_celular']);
            }
            if(isset($dados['telefone_comercial']) && $dados['telefone_comercial'] != '') {
                $BusinessPartner->setPhoneNumber2($dados['telefone_comercial']);
            }
            if(isset($dados['telefone_residencial']) && $dados['telefone_residencial'] != '') {
                $BusinessPartner->setPhoneNumber3($dados['telefone_residencial']);
            }
            if(isset($dados['cep']) && $dados['cep'] != '') {
                $BusinessPartner->setZipCode($dados['cep']);
            }
            if(isset($dados['rua']) && $dados['rua'] != '') {
                $BusinessPartner->setAdress($dados['rua']);
            }
            if(isset($dados['numero']) && $dados['numero'] != '') {
                $BusinessPartner->setAdressNumber($dados['numero']);
            }
            if(isset($dados['bairro']) && $dados['bairro'] != '') {
                $BusinessPartner->setAdressDistrict($dados['bairro']);
            }
            // if(isset($dados['cidade']) && $dados['cidade'] != '') {
            //     $BusinessPartner->setPhoneNumber($dados['cidade']);
            // }
            // if(isset($dados['estado']) && $dados['estado'] != '') {
            //     $BusinessPartner->setPhoneNumber($dados['estado']);
            // }
            if(isset($dados['complemento']) && $dados['complemento'] != '') {
                $BusinessPartner->setAdressComplement($dados['complemento']);
            }

            // dados bancarios
            if(isset($dados['banco']) && $dados['banco'] != '') {
                $BusinessPartner->setBank($dados['banco']);
            }
            if(isset($dados['agencia']) && $dados['agencia'] != '') {
                $BusinessPartner->setAgency($dados['agencia']);
            }
            if(isset($dados['numero_conta']) && $dados['numero_conta'] != '') {
                $BusinessPartner->setAccount($dados['numero_conta']);
            }
            // if(isset($dados['metodo_pagamento']) && $dados['metodo_pagamento'] != '') {
            //     $BusinessPartner->setPhoneNumber($dados['metodo_pagamento']);
            // }

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

            $Cards = new \Cards();
            $Cards->setCardNumber($dados['cpf']);
            $Cards->setBlocked('N');
            $Cards->setAirline($em->getRepository('Airline')->findOneBy(array('name' => $dados['companhia'])));
            $Cards->setBusinesspartner($BusinessPartner);
            $em->persist($Cards);
            $em->flush($Cards);

            // dados compra
            $Purchase = new \Purchase();
            if(isset($dados['compra']['milhas_compradas']) && $dados['compra']['milhas_compradas'] != '') {
                $Purchase->setPurchaseMiles($dados['compra']['milhas_compradas']);
                $Purchase->setLeftover($dados['compra']['milhas_compradas']);
            }
            $Purchase->setPurchaseDate(new \Datetime());
            if(isset($dados['compra']['custo_por_milhar']) && $dados['compra']['custo_por_milhar'] != '') {
                $Purchase->setCostPerThousand($dados['compra']['custo_por_milhar']);
                $Purchase->setCostPerThousandPurchase($dados['compra']['custo_por_milhar']);
            }
            if(isset($dados['compra']['valor_total']) && $dados['compra']['valor_total'] != '') {
                $Purchase->setTotalCost($dados['compra']['valor_total']);
            }
            if (isset($dados['compra']['description'])) {
                $Purchase->setDescription($dados['compra']['description']);
            }
            if (isset($dados['compra']['id'])) {
                $Purchase->setIdCotacao($dados['compra']['id']);
            }

            $Purchase->setAproved('Y');
            if($dados['status'] == 'Aprovado' || $dados['status'] == 'Aguardando pgto' || $dados['status'] == 'Pagamento realizado') {
                $Purchase->setStatus('W');
            } else if($dados['status'] == 'Bloqueado' || $dados['status'] == 'Desistência' || $dados['status'] == 'Negado') {
                $Purchase->setStatus('C');
            }

            $Purchase->setCards($Cards);

            if (isset($dados['compra']['data_vencimento_milhas'])) {
                $Purchase->setMilesDueDate(new \Datetime($dados['compra']['data_vencimento_milhas']));
            }
            if (isset($dados['compra']['data_pagamento'])) {
                $Purchase->setPayDate(new \Datetime($dados['compra']['data_pagamento']));
            }
            if (isset($dados['compra']['data_contrato'])) {
                $Purchase->setContractDueDate(new \Datetime($dados['compra']['data_contrato']));
            }
            if(isset($dados['compra']['tipo_cartao']) && $dados['compra']['tipo_cartao'] != ''){
                $Purchase->setCardType($dados['compra']['tipo_cartao']);
            }
            if(isset($dados['compra']['metodo_pagamento']) && $dados['compra']['metodo_pagamento'] != '') {
                $Purchase->setPaymentMethod($dados['compra']['metodo_pagamento']);
            }
            if(isset($dados['compra']['razao_pagamento']) && $dados['compra']['razao_pagamento'] != '') {  // boarding_date | issue_date
                $Purchase->setPaymentBy($dados['compra']['razao_pagamento']);
            }
            if(isset($dados['compra']['dias_pagamento']) && $dados['compra']['dias_pagamento'] != '') {
                $Purchase->setPaymentDays($dados['compra']['dias_pagamento']);
            }
            if (isset($dados['id'])) {
                $Purchase->setIdCotacao($dados['id']);
            }
            $em->persist($Purchase);
            $em->flush($Purchase);

            if(isset($dados['compra']['valor_total']) && $dados['compra']['valor_total'] != '') {
                $Billspay = new \Billspay();
                $Billspay->setStatus('A');
                $Billspay->setProvider($BusinessPartner);
                $Billspay->setDescription('CIA '. $dados['companhia'].' - '.number_format($dados['compra']['milhas_compradas'], 0, ',', '.'));
                $Billspay->setOriginalValue($dados['compra']['valor_total']);
                $Billspay->setActualValue($dados['compra']['valor_total']);
                $Billspay->setTax(0);
                $Billspay->setDiscount(0);
                $Billspay->setAccountType('Compra Milhas');
                $Billspay->setPaymentType('Deposito em Conta');
                if(isset($dados['compra']['data_pagamento']) && $dados['compra']['data_pagamento'] != '') {
                    $Billspay->setDueDate(new \Datetime($dados['compra']['data_pagamento']));
                }
                $Billspay->setIssueDate(new \Datetime());
                $em->persist($Billspay);
                $em->flush($Billspay);

                $PurchaseBillspay = new \PurchaseBillspay();
                $PurchaseBillspay->setBillspay($Billspay);
                $PurchaseBillspay->setPurchase($Purchase);
                $em->persist($PurchaseBillspay);
                $em->flush($PurchaseBillspay);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function atualizar(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);

        try {
            $em = Application::getInstance()->getEntityManager();

            $Purchase = $em->getRepository('Purchase')->findOneBy(
                array( 'idCotacao' => $dados['id'] )
            );

            $Purchase->setStatus($dados['status']);

            $em->persist($Purchase);
            $em->flush($Purchase);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}