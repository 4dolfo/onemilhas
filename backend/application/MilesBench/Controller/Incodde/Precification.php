<?php

namespace MilesBench\Controller\Incodde;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Precification {

    public function pricing(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {

            // get the precification here
            ////////////////////////////

            // monting return json
            $dataset = array();
            $categorys = array('nacional', 'internacional', 'executivo');

            if(isset($dados['promo'])) {
                $promo = $dados['promo'];
                $PlansPromos = $em->getRepository('PlansPromos')->find($promo);
            }

            if(!isset($dados['client_id'])) {
                $dados['client_id'] = 1;
            }
            $SalePlans = $em->getRepository('SalePlans')->findOneBy( array( 'id' => $dados['client_id'] ) );
            $Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));
            foreach ($Airlines as $airline) {

                foreach ($categorys as $category) {
                    $PlansPromoControlConfig = null;
                    if(isset($dados['promo'])) {
                        if(strrpos($PlansPromos->getAirlinesTypes(), $category) !== false && strrpos($PlansPromos->getAirlines(), $airline->getName()) !== false) {
                            $PlansPromoControlConfig = $em->getRepository('PlansPromoControlConfig')->findOneBy(
                                array(
                                    'type' => $category,
                                    'plansPromos' => $PlansPromos->getId(),
                                    'airline' => $airline->getId()
                                )
                            );
                        }
                    }

                    $milesEmission = $em->getRepository('SalePlansEmissionMethods')->findOneBy(
                        array(
                            'plansEmissionMethods' => 1,
                            'salePlans' => $SalePlans->getId()
                        )
                    );

                    $payingEmission = $em->getRepository('SalePlansEmissionMethods')->findOneBy(
                        array(
                            'plansEmissionMethods' => 2,
                            'salePlans' => $SalePlans->getId()
                        )
                    );

                    // pricing miles
                    $PlansControlConfig = $em->getRepository('PlansControlConfig')->findOneBy(
                        array(
                            'type' => $category,
                            'salePlans' => $SalePlans->getId(),
                            'airline' => $airline->getId()
                        )
                    );
                    if(!$PlansControlConfig) {
                        throw new \Exception("Plano não encontrado!");
                    }
                    $plan = array(
                        // 'status' => $PlansControlConfig->getStatus(),
                        'custo' => (float)$PlansControlConfig->getCost(),
                        'emitir_milhas' => isset($milesEmission),
                        'emitir_pagante' => isset($payingEmission),
                        // 'markup' => (float)$PlansControlConfig->getMarkup(),
                        // 'taxa_ing' => (float)$PlansControlConfig->getTaxBaby(),
                        // 'taxa_embarque' => (float)$PlansControlConfig->getBoardingTax(),
                        // 'tipo' => $PlansControlConfig->getType(),
                        // 'companhia' => $airline->getId(),
                        // 'plano_id' => $SalePlans->getId(),
                        'configs' => array(),
                        // 'bagagens' => array(),
                        // 'markup_dias' => array(),
                        'markup_final' => array()
                    );

                    if($PlansPromoControlConfig) {
                        $configs = json_decode( $PlansPromoControlConfig->getConfig(), true);
                        foreach ($configs as $key => $value) {
                            $plan['configs'][] = array(
                                'minimo_pontos' => $value['minimumPoints'],
                                'maximo_pontos' => $value['maximumPoints'],
                                'valor' => (float)$value['value'],
                                'discount_type' => $value['discountType'],
                                'markup_desconto' => (float)$value['discountMarkup'],
                                'use_fixed_value' => false,
                                'fixed_amount' => 0
                            );
                        }

                    } else {
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
                                'discount_type' => $value->getDiscountType(),
                                'markup_desconto' => (float)$value->getDiscountMarkup(),
                                'dias_inicio' => (float)$value->getDaysStart(),
                                'dias_fim' => (float)$value->getDaysEnd(),
                                'porcentagem' => (float)$value->getPercentage(),
                                'use_fixed_value' => $value->getUseFixedValue() == 'true',
                                'fixed_amount' => 0
                            );
    
                            if($value->getUseFixedValue() == 'true') {
                                $plan['configs'][$key]['fixed_amount'] = (float)$value->getFixesAmount();
                            }

                            if(isset($PlansPromos)) {
                                if(strrpos($PlansPromos->getAirlinesTypes(), $category) !== false && strrpos($PlansPromos->getAirlines(), $airline->getName()) !== false) {
                                    $plan['configs'][$key]['markup_desconto'] = (float)$PlansPromos->getDiscountMarkup();
                                    $plan['configs'][$key]['discount_type'] = $PlansPromos->getDiscountType();
                                }
                            }
                        }
                    }

                    // $PlansBaggage = $em->getRepository('PlansBaggage')->findBy(
                    //     array(
                    //         'plansControlConfig' => $PlansControlConfig->getId()
                    //     )
                    // );
                    // foreach ($PlansBaggage as $key => $value) {
                    //     $plan['bagagens'][] = array(
                    //         'quantidade' => (int)$value->getAmount(),
                    //         'valor' => (float)$value->getValue()
                    //     );
                    // }
                    // $DaysMarkupPlans = $em->getRepository('DaysMarkupPlans')->findBy(
                    //     array(
                    //         'plansControlConfig' => $PlansControlConfig->getId()
                    //     )
                    // );
                    // foreach ($DaysMarkupPlans as $key => $value) {
                    //     $plan['markup_dias'][] = array(
                    //         'minimo_dias' => (int)$value->getMinimumDays(),
                    //         'maximo_dias' => (int)$value->getMaximumDays(),
                    //         'valor' => (float)$value->getValue(),
                    //     );
                    // }
                    $FinalMarckupPlans = $em->getRepository('FinalMarckupPlans')->findBy(
                        array(
                            'plansControlConfig' => $PlansControlConfig->getId()
                        )
                    );
                    foreach ($FinalMarckupPlans as $key => $value) {
                        $plan['markup_final'][] = array(
                            'valor' => (float)$value->getValue()
                        );
                    }

                    if(!isset($dataset[$airline->getName()])) {
                        $dataset[$airline->getName()] = array();
                    }
                    $dataset[$airline->getName()][$category]['preco'] = $plan;
                }
            }
            ////////////////////////////
            // end of precification

            $response->setDataset($dataset);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso!');
            $response->addMessage($message);
        } catch(\Exception $e) {

            var_dump($e->getMessage());die;
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
            $message->setText('Ocorreu um erro, a Milhas ja foi notificada e esta trabalhando para solucionar o problema.');
            $response->addMessage($message);
        }
    }

    public function discounts(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {

            // get the precification here
            ////////////////////////////

            // monting return json
            $dataset = array();
            $categorys = array('nacional', 'internacional', 'executivo');

            $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('id' => 1));
            $Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));
            foreach ($Airlines as $airline) {

                foreach ($categorys as $category) {
                    // pricing miles
                    $PlansControlConfig = $em->getRepository('PlansControlConfig')->findOneBy(
                        array(
                            'type' => $category,
                            'salePlans' => $SalePlans->getId(),
                            'airline' => $airline->getId()
                        )
                    );
                    if(!$PlansControlConfig) {
                        throw new \Exception("Plano não encontrado!");
                    }

                    $plan = array(
                        'trechos_descontos' => array()
                    );

                    $PathsMarkupPlans = $em->getRepository('PathsMarkupPlans')->findBy(
                        array(
                            'plansControlConfig' => $PlansControlConfig->getId()
                        )
                    );
                    foreach ($PathsMarkupPlans as $key => $value) {
                        $plan['trechos_descontos'][] = array(
                            'airportCode' => $value->getAirportCode(),
                            'discount' => (float)$value->getDiscount()
                        );
                    }

                    if(!isset($dataset[$airline->getName()])) {
                        $dataset[$airline->getName()] = array();
                    }
                    $dataset[$airline->getName()][$category]['preco'] = $plan;
                }
            }
            ////////////////////////////
            // end of precification

            $response->setDataset($dataset);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso!');
            $response->addMessage($message);
        } catch(\Exception $e) {

            var_dump($e->getMessage());die;
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

    public function airlines(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {

            $dataset = array();
            $categorys = array('nacional', 'internacional', 'executivo');

            // $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('id' => 1));
            // $Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));

            $dataset = array(
                'LATAM' => true,
                'GOL' => true,
                'AZUL' => true,
                'AVIANCA' => true
            );

            $response->setDataset($dataset);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados obtidos com sucesso!');
            $response->addMessage($message);
        } catch(\Exception $e) {

            var_dump($e->getMessage());die;
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

    public function updateMarkupClient(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();

        try {
            $dataset = array();
            if(!isset($dados['client_id'])) {
                throw new \Exception("Cliente invalido!");
            }

            $Businesspartner = $em->getRepository('Businesspartner')->find($dados['client_id']);
            if(!$Businesspartner) {
                throw new \Exception("Cliente invalido! - " . $dados['client_id']);
            }

            $ClientsMarkups = $em->getRepository('ClientsMarkups')->findOneBy(array('businesspartner' => $dados['client_id']));
            if(!$ClientsMarkups) {
                $ClientsMarkups = new \ClientsMarkups();
                $ClientsMarkups->setBusinesspartner($Businesspartner);
            }

            if(isset($dados['markup'])) {
                $ClientsMarkups->setJson( json_encode($dados['markup']) );
            }

            $ClientsMarkups->setUpdateDate(new \DateTime());
            $em->persist($ClientsMarkups);
            $em->flush($ClientsMarkups);

            $response->setDataset($dataset);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Dados atualizados com sucesso!');
            $response->addMessage($message);

        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}