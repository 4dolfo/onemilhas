<?php

namespace MilesBench\Controller\BuscaIdeal;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Reembolso {

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

            $refund = false;
            if($sale->getIssueDate()->modify('+60 day') >= (new \DateTime()) && 
			  ($sale->getStatus() != 'Reembolso Solicitado' && $sale->getStatus() != 'Reembolso Pagante Solicitado' && $sale->getStatus() != 'Reembolso Confirmado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Reembolso Pendente' && 
			   $sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
				$refund = true;
			}

            if(!$refund) {
                throw new \Exception("Venda não valida para reembolso!");
            }

            $international = false;
			if($sale->getAirportFrom() != null){
				$international = ($sale->getAirportFrom()->getInternational() == 'true');
			}
			if($sale->getAirportTo() != null){
				if(!$international) {
					$international = ($sale->getAirportTo()->getInternational() == 'true');
				}
			}

            $Airline = $sale->getAirline();
            $RefundRepricing = $em->getRepository('RefundRepricing')->findOneBy(array('airline' => $Airline->getId(), 'type' => 'Reembolso'));

            $actualDate = new \DateTime();
            $selected = (new \DateTime($sale->getBoardingDate()->format('Y-m-d H:i:s')))->modify('+3 hour');

            $dataset = array('valor' => 'error');
            if($RefundRepricing) {
                if($actualDate < $selected) {

                    if($international) {
                        if($dados['airportLocation'] == "America Norte" && $RefundRepricing->getNorthAmericaBeforeBoarding() != 0) {
                            $dataset = array('valor' => $RefundRepricing->getNorthAmericaBeforeBoarding());
                        } else if($dados['airportLocation'] == "America Sul" && $RefundRepricing->getSouthAmericaBeforeBoarding() != 0) {
                            $dataset = array('valor' => $RefundRepricing->getSouthAmericaBeforeBoarding());
                        } else {
                            $dataset = array('valor' => $RefundRepricing->getInternationalBeforeBoarding());
                        }
                    } else {
                        $dataset = array('valor' => $RefundRepricing->getNationalBeforeBoarding());
                    }

                } else {

                    if($international) {
                        if($dados['airportLocation'] == "America Norte" && $RefundRepricing->getNorthAmericaAfterBoarding() != 0) {
                            $dataset = array('valor' => $RefundRepricing->getNorthAmericaAfterBoarding());
                        } else if($dados['airportLocation'] == "America Sul" && $RefundRepricing->getSouthAmericaAfterBoarding() != 0) {
                            $dataset = array('valor' => $RefundRepricing->getSouthAmericaAfterBoarding());
                        } else {
                            $dataset = array('valor' => $RefundRepricing->getInternationalAfterBoarding());
                        }
                    } else {
                        $dataset = array('valor' => $RefundRepricing->getNationalAfterBoarding());
                    }

                }

                $response->setDataset($dataset);
            } else {
                $response->setDataset($dataset);
            }


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

    public function reembolso(Request $request, Response $response) {
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

            $refund = false;
            if($sale->getIssueDate()->modify('+60 day') >= (new \DateTime()) && 
			  ($sale->getStatus() != 'Reembolso Solicitado' && $sale->getStatus() != 'Reembolso Pagante Solicitado' && $sale->getStatus() != 'Reembolso Confirmado' && $sale->getStatus() != 'Cancelamento Nao Solicitado' && $sale->getStatus() != 'Reembolso Pendente' && 
			   $sale->getStatus() != 'Cancelamento Solicitado' && $sale->getStatus() != 'Cancelamento Efetivado' && $sale->getStatus() != 'Cancelamento Pendente' )) {
				$refund = true;
			}

            if(!$refund) {
                throw new \Exception("Venda não valida para reembolso!");
            }

            $sale->setStatus("Reembolso Pendente");
            // $em->persist($sale);
            // $em->flush($sale);

            $content = "<br>Ola,<br><br><b>Nova solicitação de reembolso</b>".
                "Venda id: <b>" . $sale->getId() . "</b><br>".
                "Data da venda: <b>" . $sale->getIssueDate()->format('d/m/Y') . "</b><br>".
                "LOCALIZADOR: <b>" . $sale->getFlightLocator() . "</b><br><br>".
                "<br><br>SRM-IT";

            $email = 'suporte@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'partner' => $email,
                'subject' => 'SOLICITAÇÃO - REEMBOLSO',
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
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Reembolso confirmado com sucesso');
            $response->addMessage($message);
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}