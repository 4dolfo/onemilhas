<?php

namespace MilesBench\Controller\Incodde\BankMilhas;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Status {

    public function update(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);

        try {
            $em = Application::getInstance()->getEntityManager();

            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(
                array( 'registrationCode' => $dados['cpf'], 'partnerType' => 'P' )
            );

            if(!$BusinessPartner) {
                throw new \Exception("Fornecedor nao encontrado");
            }

            $BusinessPartner->setStatus($dados['status']);

            $em->persist($BusinessPartner);
            $em->flush($BusinessPartner);

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