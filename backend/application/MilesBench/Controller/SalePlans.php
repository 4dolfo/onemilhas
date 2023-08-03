<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;

class SalePlans {

    public function loadSalePlans(Request $request, Response $response) {
        $dados = $request->getRow();

        $dataset = array();
        $em = Application::getInstance()->getEntityManager();

        $SalePlans = $em->getRepository('SalePlans')->findAll();

        foreach ($SalePlans as $plan) {
            $disp_str = 'SIM';
            if($plan->getSistemaDisplay() == 'true')
                $disp_str = 'NÃO';

            $dataset[] = array(
                'id' => $plan->getId(),
                'name' => $plan->getName(),
                'description' => $plan->getDescription(),
                'documentos' => ($plan->getDocumentos() == 'true'),
                'sistemaDisp' => ($plan->getSistemaDisplay() == 'true'),
                'sistemaDispStr' => $disp_str
            );
        }
        $response->setDataset($dataset);
    }

    public function loadPlansControl(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('id' => $dados['id']));
        $Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));

        // confiança user
        $userConfianca = null;
        if($SalePlans->getPlanUser()) {
            $userConfianca = $SalePlans->getPlanUser()->getUserName();
        }

        // charging methods
        $chargingMethods = array();
        $PlansChargingMethods = $em->getRepository('PlansChargingMethods')->findAll();
        foreach ($PlansChargingMethods as $method) {
            $SalePlansChargingMethods = $em->getRepository('SalePlansChargingMethods')->findOneBy(
                array(
                    'plansChargingMethods' => $method->getId(),
                    'salePlans' => $SalePlans->getId()
                )
            );

            $interestFreeInstallment = 0;
            $interestFree = 3;
            $status = false;
            $extraValue = 0;
            $extraType = 'D';
            if($SalePlansChargingMethods) {
                $status = true;
                $interestFreeInstallment = $SalePlansChargingMethods->getInterestFreeInstallment();
                $interestFree = (int)$SalePlansChargingMethods->getInterestFree();
                $extraValue = $SalePlansChargingMethods->getExtraValue();
                $extraType = $SalePlansChargingMethods->getExtraType();
            }
            $chargingMethods[] = array(
                'method' => $method->getName(),
                'status' => $status,
                'interestFreeInstallment' => $interestFreeInstallment,
                'interestFree' => $interestFree,
                'extraValue' => $extraValue,
                'extraType' => $extraType
            );
        }

        // emission methods
        $emissionMethods = array();
        $PlansEmissionMethods = $em->getRepository('PlansEmissionMethods')->findAll();
        foreach ($PlansEmissionMethods as $method) {
            $SalePlansEmissionMethods = $em->getRepository('SalePlansEmissionMethods')->findOneBy(
                array(
                    'plansEmissionMethods' => $method->getId(),
                    'salePlans' => $SalePlans->getId()
                )
            );

            $status = false;
            if($SalePlansEmissionMethods) {
                $status = true;
            }
            $emissionMethods[] = array(
                'method' => $method->getName(),
                'status' => $status
            );
        }

        // slides
        $slides = array();
        $PlansSlides = $em->getRepository('PlansSlides')->findBy(
            array(
                'salePlans' => $SalePlans->getId()
            )
        );
        foreach ($PlansSlides as $key => $value) {
            $slides[] = array(
                'id' => $value->getId(),
                'url' => $value->getUrl(),
                'type' => $value->getType()
            );
        }
        if(count($slides) == 0) {
            $slides[] = array(
                'url' => '',
                'type' => ''
            );
        }

        // monting return json
        $profile = array(
            'user' => $userConfianca,
            'chargingMethods' => $chargingMethods,
            'emissionMethods' => $emissionMethods,
            'slides' => $slides,
            'showMiles' => $SalePlans->getShowMiles(),
            'showConventional' => $SalePlans->getShowConventional(),
            'documentos' => ($SalePlans->getDocumentos() == 'true'),
            'referencia' => $SalePlans->getReferencia()
        );

        $dataset = array(
            'profile' => $profile
        );

        $airlinesArray = array();
        foreach ($Airlines as $keyAirline => $valueAirline) {

            // nacional
            $nacional = $this->loadArrayPricing('nacional', $em, $SalePlans, $valueAirline);

            // executivo
            $executivo = $this->loadArrayPricing('executivo', $em, $SalePlans, $valueAirline);

            // internacional
            $internacional = $this->loadArrayPricing('internacional', $em, $SalePlans, $valueAirline);

            $airlinesArray[$valueAirline->getName()] = array(
                'nacional' => $nacional,
                'internacional' => $internacional,
                'executivo' => $executivo
            );
        }

        // fill airlines array
        $dataset['airlines'] = $airlinesArray;

        // send response
        $response->setDataset($dataset);
    }

    function loadArrayPricing($type, $em, $SalePlans, $valueAirline) {

        // monting array
        $PlansControlConfig = $em->getRepository('PlansControlConfig')->findOneBy(
            array(
                'type' => $type,
                'salePlans' => $SalePlans->getId(),
                'airline' => $valueAirline->getId()
            )
        );
        if(!$PlansControlConfig) {
            $PlansControlConfig = new \PlansControlConfig();
            $PlansControlConfig->setSalePlans($SalePlans);
            $PlansControlConfig->setAirline($valueAirline);
            $PlansControlConfig->setType($type);
            $PlansControlConfig->setStatus(true);
            $PlansControlConfig->setCost(0);
            $PlansControlConfig->setMarkup(0);
            $PlansControlConfig->setTaxBaby(0);
            $PlansControlConfig->setBoardingTax(0);

            $em->persist($PlansControlConfig);
            $em->flush($PlansControlConfig);
        }
        $array = array(
            'id' => $PlansControlConfig->getId(),
            'status' => $PlansControlConfig->getStatus(),
            'cost' => (float)$PlansControlConfig->getCost(),
            'markup' => (float)$PlansControlConfig->getMarkup(),
            'taxBaby' => (float)$PlansControlConfig->getTaxBaby(),
            'boardingTax' => (float)$PlansControlConfig->getBoardingTax(),
            'type' => $PlansControlConfig->getType(),
            'airline' => $valueAirline->getId(),
            'salePlans' => $SalePlans->getId(),
            'configs' => array(),
            'baggages' => array(),
            'daysMarkup' => array(),
            'pathsMarkup' => array(),
            'markup_final' => array()
        );

        // miles pricing
        $PlansControl = $em->getRepository('PlansControl')->findBy(
            array(
                'plansControlConfig' => $PlansControlConfig->getId()
            )
        );
        foreach ($PlansControl as $key => $value) {
            $array['configs'][] = array(
                'id' => $value->getId(),
                'minimumPoints' => (int)$value->getMinimumPoints(),
                'maximumPoints' => (int)$value->getMaximumPoints(),
                'value' => (float)$value->getValue(),
                'discountMarkup' => (float)$value->getDiscountMarkup(),
                'daysStart' => (float)$value->getDaysStart(),
                'daysEnd' => (float)$value->getDaysEnd(),
                'percentage' => (float)$value->getPercentage(),
                'useFixedValue' => $value->getUseFixedValue() == 'true',
                'fixesAmount' => $value->getFixesAmount(),
                'discountType' => $value->getDiscountType(),
            );
        }
        if(count($array['configs']) == 0) {
            $array['configs'][] = array(
                'id' => 0,
                'minimumPoints' => 0,
                'maximumPoints' => 1000000,
                'value' => 0,
                'discountMarkup' => 0,
                'daysStart' => 0,
                'daysEnd' => 0,
                'percentage' => 0,
                'discountType' => 'D'
            );
        }

        // baggages
        $PlansBaggage = $em->getRepository('PlansBaggage')->findBy(
            array(
                'plansControlConfig' => $PlansControlConfig->getId()
            )
        );
        foreach ($PlansBaggage as $key => $value) {
            $array['baggages'][] = array(
                'id' => $value->getId(),
                'amount' => (int)$value->getAmount(),
                'value' => (float)$value->getValue()
            );
        }
        if(count($array['baggages']) == 0) {
            $array['baggages'][] = array(
                'id' => 0,
                'amount' => 1,
                'value' => 0
            );
        }

        // days markup
        $DaysMarkupPlans = $em->getRepository('DaysMarkupPlans')->findBy(
            array(
                'plansControlConfig' => $PlansControlConfig->getId()
            )
        );
        foreach ($DaysMarkupPlans as $key => $value) {
            $array['daysMarkup'][] = array(
                'id' => $value->getId(),
                'minimumDays' => (int)$value->getMinimumDays(),
                'maximumDays' => (int)$value->getMaximumDays(),
                'value' => (float)$value->getValue(),
            );
        }
        if(count($array['daysMarkup']) == 0) {
            $array['daysMarkup'][] = array(
                'minimumDays' => 0,
                'maximumDays' => 0,
                'value' => 0
            );
        }

        // paths markup
        $PathsMarkupPlans = $em->getRepository('PathsMarkupPlans')->findBy(
            array(
                'plansControlConfig' => $PlansControlConfig->getId()
            )
        );
        foreach ($PathsMarkupPlans as $key => $value) {
            $array['pathsMarkup'][] = array(
                'id' => $value->getId(),
                'airportCode' => $value->getAirportCode(),
                'discount' => (float)$value->getDiscount()
            );
        }
        if(count($array['pathsMarkup']) == 0) {
            $array['pathsMarkup'][] = array(
                'airportCode' => '',
                'discount' => 0
            );
        }

        // Final marckup
        $FinalMarckupPlans = $em->getRepository('FinalMarckupPlans')->findBy(
            array(
                'plansControlConfig' => $PlansControlConfig->getId()
            )
        );
        foreach ($FinalMarckupPlans as $key => $value) {
            $array['markup_final'][] = array(
                'id' => $value->getId(),
                'value' => (float)$value->getValue()
            );
        }
        if(count($array['markup_final']) == 0) {
            $array['markup_final'][] = array(
                'value' => 0
            );
        }
        return $array;
    }

    public function saveSalePlan(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        if (isset($dados['control'])) {
            $control = $dados['control'];
        }
        if (isset($dados['profile'])) {
            $profile = $dados['profile'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        try {
            if(isset($dados['id'])) {
                $SalePlans = $em->getRepository('SalePlans')->findOneBy(array('id' => $dados['id']));
            } else {
                $SalePlans = new \SalePlans();
            }

            $SalePlans->setName($dados['name']);
            $SalePlans->setDescription($dados['description']);

            $em->persist($SalePlans);
            $em->flush($SalePlans);

            if(isset($profile)) {
                if(isset($profile['user']) && $profile['user'] != '') {
                    $PlansUsers = $em->getRepository('PlansUsers')->findOneBy(array('userName' => $profile['user']));
                    if($PlansUsers) {
                        $SalePlans->setPlanUser($PlansUsers);
                    }
                }

                // charging methods
                foreach ($profile['chargingMethods'] as $key => $value) {
                    $PlansChargingMethods = $em->getRepository('PlansChargingMethods')->findOneBy(
                        array( 'name' => $value['method'] )
                    );

                    $SalePlansChargingMethods = $em->getRepository('SalePlansChargingMethods')->findOneBy(
                        array(
                            'plansChargingMethods' => $PlansChargingMethods->getId(),
                            'salePlans' => $SalePlans->getId()
                        )
                    );

                    if($value['status'] === true || $value['status'] === 'true') {
                        if(!$SalePlansChargingMethods) {
                            $SalePlansChargingMethods = new \SalePlansChargingMethods();
                            $SalePlansChargingMethods->setPlansChargingMethods($PlansChargingMethods);
                            $SalePlansChargingMethods->setSalePlans($SalePlans);
                        }

                        if(isset($value['interestFreeInstallment'])) {
                            $SalePlansChargingMethods->setInterestFreeInstallment($value['interestFreeInstallment']);
                        }

                        if(isset($value['interestFree'])) {
                            $SalePlansChargingMethods->setInterestFree($value['interestFree']);
                        }

                        if(isset($value['extraValue'])) {
                            $SalePlansChargingMethods->setExtraValue($value['extraValue']);
                        }

                        if(isset($value['extraType'])) {
                            $SalePlansChargingMethods->setExtraType($value['extraType']);
                        }

                        $em->persist($SalePlansChargingMethods);
                        $em->flush($SalePlansChargingMethods);
                    } else {
                        if($SalePlansChargingMethods) {
                            $em->remove($SalePlansChargingMethods);
                            $em->flush($SalePlansChargingMethods);
                        }
                    }
                }

                // emission methods
                foreach ($profile['emissionMethods'] as $key => $value) {
                    $PlansEmissionMethods = $em->getRepository('PlansEmissionMethods')->findOneBy(
                        array( 'name' => $value['method'] )
                    );

                    $SalePlansEmissionMethods = $em->getRepository('SalePlansEmissionMethods')->findOneBy(
                        array(
                            'plansEmissionMethods' => $PlansEmissionMethods->getId(),
                            'salePlans' => $SalePlans->getId()
                        )
                    );

                    if($value['status'] === 'true' || $value['status'] === true) {
                        if(!$SalePlansEmissionMethods) {
                            $SalePlansEmissionMethods = new \SalePlansEmissionMethods();
                            $SalePlansEmissionMethods->setPlansEmissionMethods($PlansEmissionMethods);
                            $SalePlansEmissionMethods->setSalePlans($SalePlans);

                            $em->persist($SalePlansEmissionMethods);
                            $em->flush($SalePlansEmissionMethods);
                        }
                    } else {
                        if($SalePlansEmissionMethods) {
                            $em->remove($SalePlansEmissionMethods);
                            $em->flush($SalePlansEmissionMethods);
                        }
                    }
                }

                $SalePlans->setShowMiles($profile['showMiles'] == 'true');
                $SalePlans->setShowConventional($profile['showConventional'] == 'true');
                $SalePlans->setReferencia($profile['referencia']);
                $SalePlans->setDocumentos($profile['documentos']);
                $em->persist($SalePlans);
                $em->flush($SalePlans);
            }

            if(isset($control)) {
                foreach ($control as $keyAirline => $valueAirline) {
                    $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $keyAirline));

                    foreach ($valueAirline as $keyType => $valueType) {
                        $PlansControlConfig = $em->getRepository('PlansControlConfig')->findOneBy(
                            array(
                                'type' => $keyType,
                                'salePlans' => $SalePlans->getId(),
                                'airline' => $Airline->getId()
                            )
                        );

                        $PlansControlConfig->setStatus($valueType['status'] == true || $valueType['status'] == 'true');
                        $PlansControlConfig->setCost($valueType['cost']);
                        $PlansControlConfig->setMarkup($valueType['markup']);
                        $PlansControlConfig->setTaxBaby($valueType['taxBaby']);
                        $PlansControlConfig->setBoardingTax($valueType['boardingTax']);

                        $em->persist($PlansControlConfig);
                        $em->flush($PlansControlConfig);

                        // getting and removing actual controls
                        $PlansControl = $em->getRepository('PlansControl')->findBy(
                            array(
                                'plansControlConfig' => $PlansControlConfig->getId()
                            )
                        );
                        foreach ($PlansControl as $key => $value) {
                            $em->remove($value);
                            $em->flush($value);
                        }

                        // creating new controls
                        foreach ($valueType['configs'] as $key => $value) {
                            $PlansControl = new \PlansControl();
                            $PlansControl->setMinimumPoints($value['minimumPoints']);
                            $PlansControl->setMaximumPoints($value['maximumPoints']);
                            $PlansControl->setValue($value['value']);
                            $PlansControl->setDiscountMarkup($value['discountMarkup']);
                            $PlansControl->setDaysStart($value['daysStart']);
                            $PlansControl->setDaysEnd($value['daysEnd']);
                            $PlansControl->setPercentage($value['percentage']);
                            $PlansControl->setDiscountType($value['discountType']);
                            if(isset($value['fixesAmount'])) {
                                $PlansControl->setFixesAmount($value['fixesAmount']);
                            }
                            if(isset($value['useFixedValue'])) {
                                $PlansControl->setUseFixedValue($value['useFixedValue']);
                            }
                            $PlansControl->setPlansControlConfig($PlansControlConfig);
                            $em->persist($PlansControl);
                            $em->flush($PlansControl);
                        }

                        // baggages
                        $PlansBaggage = $em->getRepository('PlansBaggage')->findBy(
                            array(
                                'plansControlConfig' => $PlansControlConfig->getId()
                            )
                        );
                        foreach ($PlansBaggage as $key => $value) {
                            $em->remove($value);
                            $em->flush($value);
                        }

                        // creating new baggages
                        foreach ($valueType['baggages'] as $key => $value) {
                            $PlansBaggage = new \PlansBaggage();
                            $PlansBaggage->setAmount($value['amount']);
                            $PlansBaggage->setValue($value['value']);
                            $PlansBaggage->setPlansControlConfig($PlansControlConfig);
                            $em->persist($PlansBaggage);
                            $em->flush($PlansBaggage);
                        }


                        // days markup
                        $DaysMarkupPlans = $em->getRepository('DaysMarkupPlans')->findBy(
                            array(
                                'plansControlConfig' => $PlansControlConfig->getId()
                            )
                        );
                        foreach ($DaysMarkupPlans as $key => $value) {
                            $em->remove($value);
                            $em->flush($value);
                        }
                        foreach ($valueType['daysMarkup'] as $key => $value) {
                            $DaysMarkupPlans = new \DaysMarkupPlans();
                            $DaysMarkupPlans->setMinimumDays($value['minimumDays']);
                            $DaysMarkupPlans->setMaximumDays($value['maximumDays']);
                            $DaysMarkupPlans->setValue($value['value']);
                            $DaysMarkupPlans->setPlansControlConfig($PlansControlConfig);
                            $em->persist($DaysMarkupPlans);
                            $em->flush($DaysMarkupPlans);
                        }


                        // paths markup
                        $PathsMarkupPlans = $em->getRepository('PathsMarkupPlans')->findBy(
                            array(
                                'plansControlConfig' => $PlansControlConfig->getId()
                            )
                        );
                        foreach ($PathsMarkupPlans as $key => $value) {
                            $em->remove($value);
                            $em->flush($value);
                        }
                        foreach ($valueType['pathsMarkup'] as $key => $value) {
                            $PathsMarkupPlans = new \PathsMarkupPlans();
                            $PathsMarkupPlans->setAirportCode($value['airportCode']);
                            $PathsMarkupPlans->setDiscount($value['discount']);
                            $PathsMarkupPlans->setPlansControlConfig($PlansControlConfig);
                            $em->persist($PathsMarkupPlans);
                            $em->flush($PathsMarkupPlans);
                        }

                        // paths markup
                        $FinalMarckupPlans = $em->getRepository('FinalMarckupPlans')->findBy(
                            array(
                                'plansControlConfig' => $PlansControlConfig->getId()
                            )
                        );
                        foreach ($FinalMarckupPlans as $key => $value) {
                            $em->remove($value);
                            $em->flush($value);
                        }
                        foreach ($valueType['markup_final'] as $key => $value) {
                            $FinalMarckupPlans = new \FinalMarckupPlans();
                            $FinalMarckupPlans->setValue($value['value']);
                            $FinalMarckupPlans->setPlansControlConfig($PlansControlConfig);
                            $em->persist($FinalMarckupPlans);
                            $em->flush($FinalMarckupPlans);
                        }
                    }
                }
            }

            $this->updatePrecification($SalePlans->getId());

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Plano salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {

            var_dump($e->getMessage());die;
            $content = "<br>".$e->getMessage()."<br><br>POST:".http_build_query($_POST)."<br><br>SRM-IT";

            $email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'from' => $email1,
                'partner' => $email2,
                'subject' => 'ERROR - SALVANDO PRECIFICACAO',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);
            var_dump($result);die;

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Erro identificado');
            $response->addMessage($message);
        }
    }

    public function loadConfiancaUsers(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $PlansUsers = $em->getRepository('PlansUsers')->findAll();
        foreach ($PlansUsers as $key => $value) {
            $dataset[] = array(
                'id' => $value->getId(),
                'name' => $value->getUserName()
            );
        }

        // send response
        $response->setDataset($dataset);
    }

    public function loadClients(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();

        $Businesspartner = $em->getRepository('Businesspartner')->findBy(array( 'plan' => $dados['id'] ));
        foreach ($Businesspartner as $key => $value) {
            $dataset[] = array(
                'id' => $value->getId(),
                'name' => $value->getName()
            );
        }

        $response->setDataset($dataset);
    }

    public function moveCLients(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['newPlan'])) {
            $newPlan = $dados['newPlan'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            $NewSalePlans = $em->getRepository('SalePlans')->findOneBy(array('name' => $newPlan));

            $dataset = array();

            $Businesspartner = $em->getRepository('Businesspartner')->findBy(array( 'plan' => $dados['id'] ));
            foreach ($Businesspartner as $key => $value) {
                $value->setPlan($NewSalePlans);
                $em->persist($value);
                $em->flush($value);

                $this->updatePrecificationByClient($value->getId());
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function addAllClients(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            $SalePlans = $em->getRepository('SalePlans')->find($dados['id']);

            $dataset = array();

            $Businesspartner = $em->getRepository('Businesspartner')->findBy(array( 'partnerType' => 'C' ));
            foreach ($Businesspartner as $key => $value) {
                $value->setPlan($SalePlans);
                $em->persist($value);
                $em->flush($value);

                $this->updatePrecificationByClient($value->getId());
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function removeAllClients(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            $dataset = array();
            $Businesspartner = $em->getRepository('Businesspartner')->findBy(array( 'plan' => $dados['id'] ));
            foreach ($Businesspartner as $key => $value) {
                $value->setPlan(NULL);
                $em->persist($value);
                $em->flush($value);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function removeClient(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            $dataset = array();
            $Businesspartner = $em->getRepository('Businesspartner')->find($dados);
            $Businesspartner->setPlan(NULL);
            $em->persist($Businesspartner);
            $em->flush($Businesspartner);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function saveFile(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['file'])) {
            $file = $dados['file'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
            $extension = explode('.', $file_name);
            $replace = 0;

            $s3 = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1',
                'credentials' => array(
                    'key' => getenv('AWS_KEY'),
                    'secret'  => getenv('AWS_SECRET')
                )
            ]);

            $bucket = 'planos-mmsgestao';
            $keyname = $dados['id'] . '/' . $extension[0] . '.' . $extension[1];
            $filepath = $file['tmp_name'];

            $result = $s3->putObject(array(
                'Bucket' => $bucket,
                'Key'    => $keyname,
                'SourceFile' => $filepath,
                'Body'   => '',
                'ACL'    => 'public-read'
            ));

            $SalePlans = $em->getRepository('SalePlans')->find($dados['id']);
            $PlansSlides = new \PlansSlides();
            $PlansSlides->setUrl($result['ObjectURL']);
            $PlansSlides->setType('');
            $PlansSlides->setSalePlans($SalePlans);
            $em->persist($PlansSlides);
            $em->flush($PlansSlides);

            $slides = array();
            $PlansSlides = $em->getRepository('PlansSlides')->findBy(
                array(
                    'salePlans' => $SalePlans->getId()
                )
            );
            foreach ($PlansSlides as $key => $value) {
                $slides[] = array(
                    'id' => $value->getId(),
                    'url' => $value->getUrl(),
                    'type' => $value->getType()
                );
            }

            $response->setDataset($slides);
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function removeSlide(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            $dataset = array();
            $PlansSlides = $em->getRepository('PlansSlides')->find($dados);
            $em->remove($PlansSlides);
            $em->flush($PlansSlides);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function addClients(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['clients'])) {
            $clients = $dados['clients'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            $SalePlans = $em->getRepository('SalePlans')->find($dados['id']);

            foreach ($clients as $key => $value) {
                $Businesspartner = $em->getRepository('Businesspartner')->find($value);
                if($Businesspartner) {
                    $Businesspartner->setPlan($SalePlans);
                    $em->persist($Businesspartner);
                    $em->flush($Businesspartner);

                    $this->updatePrecificationByClient($Businesspartner->getId());
                }
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro salvo com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function updatePrecification($id) {
        try {
            $em = Application::getInstance()->getEntityManager();
            $Businesspartner = $em->getRepository('Businesspartner')->findBy( array( 'plan' => $id ) );

            $precification = new \MilesBench\Controller\Incodde\Precification();
            foreach ($Businesspartner as $key => $value) {
                $req = new \MilesBench\Request\Request();
                $resp = new \MilesBench\Request\Response();
                $req->setRow(json_encode( array( 'client_id' => $id ) ) );

                $precification->pricing($req, $resp);

                $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
                $jsonToPost = array(
                    'message' => array(
                        'text' => 'Dados obtidos com sucesso!',
                        'type' => 'S'
                    ),
                    'jsonPreco' => json_encode($resp->getDataset()),
                    'gestao' => $DirServer,
                    'idCliente' => $value->getId()
                );

                if($value->getPlan()->getReferencia()) {
                    $jsonToPost['referencia'] = $value->getPlan()->getReferencia();
                }

                $ch = curl_init();

                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    $url = \MilesBench\Util::precificacao_url_homologacao;
                } else {
                    $url = \MilesBench\Util::precificacao_url_production;
                }

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json")
                );
                $resultGetClient = curl_exec($ch);

                $ch = curl_init();
                // if( count( json_decode($resultGetClient, true)) > 0 ) {
                //     $url .= json_decode($resultGetClient, true)[0]['id'];
                //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                // }

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json")
                );
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
                $result = curl_exec($ch);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updatePrecificationByClient($id) {
        try {
            $em = Application::getInstance()->getEntityManager();
            $Businesspartner = $em->getRepository('Businesspartner')->find($id);

            $precification = new \MilesBench\Controller\Incodde\Precification();
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();
            $req->setRow(json_encode( array( 'client_id' => $Businesspartner->getPlan()->getId() ) ) );

            $precification->pricing($req, $resp);

            $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
            $jsonToPost = array(
                'message' => array(
                    'text' => 'Dados obtidos com sucesso!',
                    'type' => 'S'
                ),
                'jsonPreco' => json_encode($resp->getDataset()),
                'gestao' => $DirServer,
                'idCliente' => $id
            );

            $ch = curl_init();

            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                $url = \MilesBench\Util::precificacao_url_homologacao;
            } else {
                $url = \MilesBench\Util::precificacao_url_production;
            }

            curl_setopt($ch, CURLOPT_URL, $url . $id);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json")
            );
            $resultGetClient = curl_exec($ch);

            $ch = curl_init();
            if( count( json_decode($resultGetClient, true)) > 0 ) {
                $url .= json_decode($resultGetClient, true)[0]['id'];
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json")
            );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
            $result = curl_exec($ch);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function startPromotion($id) {
        try {
            $em = Application::getInstance()->getEntityManager();
            $PlansPromos = $em->getRepository('PlansPromos')->find($id);

            $precification = new \MilesBench\Controller\Incodde\Precification();
            if($PlansPromos->getForAllClients() == 'true') {
                $Clients = $em->getRepository('Businesspartner')->findBy(array( 'partnerType' => 'C' ));
            } else {
                $clientesArray = json_decode($PlansPromos->getClients(), true);
                $clietnesString = '';
                foreach ($clientesArray as $key => $value) {
                    if($key > 0) {
                        $clietnesString .= ',';
                    }
                    $clietnesString .= $value;
                }

                $sql = " select b from Businesspartner b where b.id in ( " . $clietnesString . " ) ";
                $query = $em->createQuery($sql);
                $Clients = $query->getResult();
            }

            foreach ($Clients as $key => $value) {
                $req = new \MilesBench\Request\Request();
                $resp = new \MilesBench\Request\Response();
                $req->setRow(json_encode( array( 'client_id' => $value->getPlan()->getId(), 'promo' => $id ) ) );

                $precification->pricing($req, $resp);

                $DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
                $jsonToPost = array(
                    'message' => array(
                        'text' => 'Dados obtidos com sucesso!',
                        'type' => 'S'
                    ),
                    'jsonPreco' => json_encode($resp->getDataset()),
                    'gestao' => $DirServer,
                    'idCliente' => $value->getId()
                );

                $ch = curl_init();

                $env = getenv('ENV') ? getenv('ENV') : 'production';
                if($env != 'production') {
                    $url = \MilesBench\Util::precificacao_url_homologacao;
                } else {
                    $url = \MilesBench\Util::precificacao_url_production;
                }

                curl_setopt($ch, CURLOPT_URL, $url . $value->getId());
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json")
                );
                $resultGetClient = curl_exec($ch);

                $ch = curl_init();
                if( count( json_decode($resultGetClient, true)) > 0 ) {
                    $url .= json_decode($resultGetClient, true)[0]['id'];
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                }

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json")
                );
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
                $result = curl_exec($ch);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
