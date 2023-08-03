<?php

namespace MilesBench\Controller\BuscaIdeal;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Cancelamento {

    public function calculo(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );
            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Login não encontrado!");
            }

            if(!isset($dados['venda'])) {
                throw new \Exception("Numero da venda deve ser informado!");
            }

            $sale = $em->getRepository('Sale')->findOneBy(array('id' => $dados['venda']));

            $cancel = false;
            if($sale->getIssueDate()->format('Y-m-d') == (new \DateTime())->format('Y-m-d') && 
                ($sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
                $cancel = true;
            }

            if(!$cancel) {
                throw new \Exception("Venda não valida para cancelamento!");
            }

            $dataset = array('valor' => 60);

            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function cancelamento(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            if(!isset($dados['login'])) {
                throw new \Exception("Login deve ser informado!");
            }

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'name' => $dados['login'], 'partnerType' => 'S' )
            );
            // fails on validation of the user
            if(!$BusinessPartner) {
                throw new \Exception("Login não encontrado!");
            }

            if(!isset($dados['venda'])) {
                throw new \Exception("Numero da venda deve ser informado!");
            }

            $sale = $em->getRepository('Sale')->findOneBy(array('id' => $dados['venda']));

            $cancel = false;
            if($sale->getIssueDate()->format('Y-m-d') == (new \DateTime())->format('Y-m-d') && 
                ($sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
                $cancel = true;
            }

            if(!$cancel) {
                throw new \Exception("Venda não valida para cancelamento!");
            }

            $sale->setStatus("Cancelamento Pendente");
            // $em->persist($sale);
            // $em->flush($sale);

            $content = "<br>Ola,<br><br><b>Nova solicitação de cancelamento</b>".
                "Venda id: <b>" . $sale->getId() . "</b><br>".
                "Data da venda: <b>" . $sale->getIssueDate()->format('d/m/Y') . "</b><br>".
                "LOCALIZADOR: <b>" . $sale->getFlightLocator() . "</b><br><br>".
                "<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'partner' => $email1,
                'subject' => 'SOLICITAÇÃO - CANCELAMENTO',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            $response->setDataset($dataset);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cancelamento confirmado com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}