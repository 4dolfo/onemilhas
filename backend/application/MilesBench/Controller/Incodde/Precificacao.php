<?php

namespace MilesBench\Controller\Incodde;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Precificacao {


    public function pricing(Request $request, Response $response) {
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
                throw new \Exception("Usuario invalido!");
            }

            if($BusinessPartner->getStatus() == 'Bloqueado') {
                throw new \Exception("Usuario invalido!");
            }

            $Agency = $em->getRepository('Businesspartner')->findOneBy(
                array( 'id' => $BusinessPartner->getClient(), 'partnerType' => 'C' )
            );
            if(!$Agency) {
                throw new \Exception("Erro encontrado no cadastro, por favor entre em contato com a One Milhas!");
            }

            
            // get the precification here
            ////////////////////////////

            if(!isset($dados['tipo'])) {
                throw new \Exception("Tipo de voo deve ser informado!");
            }
            if(!isset($dados['companhia'])) {
                throw new \Exception("Tipo de voo deve ser informado!");
            }
            $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $dados['companhia']));

            if($Agency->getPlan()) {
                $SalePlans = $Agency->getPlan();
            } else {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('id' => 1));
            }

            // monting return json
            $dataset = array();

            // pricing miles
            $PlansControlConfig = $em->getRepository('PlansControlConfig')->findOneBy(
                array(
                    'type' => $dados['tipo'],
                    'salePlans' => $SalePlans->getId(),
                    'airline' => $Airline->getId()
                )
            );
            if(!$PlansControlConfig) {
                throw new \Exception("Plano não encontrado!");
            }
            $plan = array(
                'status' => $PlansControlConfig->getStatus(),
                'custo' => (float)$PlansControlConfig->getCost(),
                'markup' => (float)$PlansControlConfig->getMarkup(),
                'taxa_ing' => (float)$PlansControlConfig->getTaxBaby(),
                'taxa_embarque' => (float)$PlansControlConfig->getBoardingTax(),
                'tipo' => $PlansControlConfig->getType(),
                'companhia' => $Airline->getId(),
                'plano_id' => $SalePlans->getId(),
                'configs' => array(),
                'bagagens' => array(),
                'markup_dias' => array(),
                'referencia' => $SalePlans->getReferencia()
            );
            $PlansControl = $em->getRepository('PlansControl')->findBy(
                array(
                    'plansControlConfig' => $PlansControlConfig->getId()
                )
            );
            foreach ($PlansControl as $key => $value) {
                $plan['configs'][] = array(
                    'minimo_pontos' => (int)$value->getMinimumPoints(),
                    'maximo_pontos' => (int)$value->getMaximumPoints(),
                    'valor' => (float)$value->getValue(),
                    'markup_disconto' => (float)$value->getDiscountMarkup(),
                    'dias_inicio' => (float)$value->getDaysStart(),
                    'dias_fim' => (float)$value->getDaysEnd(),
                    'porcentagem' => (float)$value->getPercentage(),
                );
            }
            $PlansBaggage = $em->getRepository('PlansBaggage')->findBy(
                array(
                    'plansControlConfig' => $PlansControlConfig->getId()
                )
            );
            foreach ($PlansBaggage as $key => $value) {
                $plan['bagagens'][] = array(
                    'quantidade' => (int)$value->getAmount(),
                    'valor' => (float)$value->getValue()
                );
            }
            $DaysMarkupPlans = $em->getRepository('DaysMarkupPlans')->findBy(
                array(
                    'plansControlConfig' => $PlansControlConfig->getId()
                )
            );
            foreach ($DaysMarkupPlans as $key => $value) {
                $plan['markup_dias'][] = array(
                    'minimo_dias' => (int)$value->getMinimumDays(),
                    'maximo_dias' => (int)$value->getMaximumDays(),
                    'valor' => (float)$value->getValue(),
                );
            }

            $dataset['preco'] = $plan;
            ////////////////////////////
            // end of precification

            $response->setDataset($dataset);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso!');
            $response->addMessage($message);
        } catch(\Exception $e) {

            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";
            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - BUSCA - PRECIFICACAO',
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
            $message->setText('Ocorreu um erro, a One Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }
}