<?php

namespace MilesBench\Controller\Marketing;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;

class PlansPromos {

    public function load(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $PlansPromos = $em->getRepository('PlansPromos')->findAll();

        $dataset = array();
        foreach($PlansPromos as $item){
            $clients = array();
            $arrayClients = json_decode($item->getClients(), true);
            foreach ($arrayClients as $key => $value) {
                $Client = $em->getRepository('Businesspartner')->find($value);

                $clients[] = array(
                    'id' => $value,
                    'name' => $Client->getName()
                );
            }

            $plans = 'Valor Fixo';
            if($item->getPlan()) {
                $plans = $item->getPlan()->getName();
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'startDate' => $item->getStartDate()->format('Y-m-d H:i:s'),
                'endDate' => $item->getEndDate()->format('Y-m-d H:i:s'),
                'status' => $item->getStatus() == 'true',
                'clients' => $clients,
                'plans' => $plans,
                'for_all_clients' => $item->getForAllClients() == 'false',
                'discountType' => $item->getDiscountType(),
                'discountMarkup' => (float)$item->getDiscountMarkup(),
                'airlines' => json_decode($item->getAirlines(), true),
                'airlinesTypes' => json_decode($item->getAirlinesTypes(), true),
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

        $PlansPromos = $em->getRepository('PlansPromos')->findOneBy(array('id' => $dados['id']));
        $Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));

        $dataset = array();

        $airlinesArray = array();
        foreach ($Airlines as $keyAirline => $valueAirline) {

            // nacional
            $nacional = $this->loadArrayPricing('nacional', $em, $PlansPromos, $valueAirline);

            // executivo
            $executivo = $this->loadArrayPricing('executivo', $em, $PlansPromos, $valueAirline);

            // internacional
            $internacional = $this->loadArrayPricing('internacional', $em, $PlansPromos, $valueAirline);

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

    function loadArrayPricing($type, $em, $plansPromos, $valueAirline) {

        // monting array
        $PlansPromoControlConfig = $em->getRepository('PlansPromoControlConfig')->findOneBy(
            array(
                'type' => $type,
                'plansPromos' => $plansPromos->getId(),
                'airline' => $valueAirline->getId()
            )
        );
        if(!$PlansPromoControlConfig) {
            $PlansPromoControlConfig = new \PlansPromoControlConfig();
            $PlansPromoControlConfig->setPlansPromos($plansPromos);
            $PlansPromoControlConfig->setAirline($valueAirline);
            $PlansPromoControlConfig->setType($type);
            $PlansPromoControlConfig->setCost(0);
            $PlansPromoControlConfig->setConfig(json_encode(array()));

            $em->persist($PlansPromoControlConfig);
            $em->flush($PlansPromoControlConfig);
        }
        $array = array(
            'id' => $PlansPromoControlConfig->getId(),
            'cost' => (float)$PlansPromoControlConfig->getCost(),
            'type' => $PlansPromoControlConfig->getType(),
            'airline' => $valueAirline->getId(),
            'plansPromos' => $plansPromos->getId(),
            'configs' => json_decode( $PlansPromoControlConfig->getConfig(), true)
        );

        foreach ($array['configs'] as $key => $value) {
            $array['configs'][$key]['id'] = (float)$value['id'];
            $array['configs'][$key]['minimumPoints'] = (float)$value['minimumPoints'];
            $array['configs'][$key]['maximumPoints'] = (float)$value['maximumPoints'];
            $array['configs'][$key]['value'] = (float)$value['value'];
            $array['configs'][$key]['discountMarkup'] = (float)$value['discountMarkup'];
            $array['configs'][$key]['daysStart'] = (float)$value['daysStart'];
            $array['configs'][$key]['daysEnd'] = (float)$value['daysEnd'];
            $array['configs'][$key]['percentage'] = (float)$value['percentage'];
            $array['configs'][$key]['discountType'] = $value['discountType'];
        }

        return $array;
    }

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['controlAirline'])) {
            $control = $dados['controlAirline'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            if (isset($dados['id'])) {
                $PlansPromos = $em->getRepository('PlansPromos')->find($dados['id']);
            } else {
                $PlansPromos = new \PlansPromos();
            }

            $PlansPromos->setStatus($dados['status']);
            $PlansPromos->setStartDate(new \DateTime($dados['startDate']));
            $PlansPromos->setEndDate(new \DateTime($dados['endDate']));

            $clients = array();
            if(isset($dados['clients'])) {
                foreach ($dados['clients'] as $key => $value) {
                    $clients[] = $value['id'];
                }
                $PlansPromos->setForAllClients('false');
            }
            $PlansPromos->setClients(json_encode($clients));
            $PlansPromos->setForAllClients($dados['for_all_clients']);
            $PlansPromos->setDiscountType($dados['discountType']);
            $PlansPromos->setDiscountMarkup($dados['discountMarkup']);

            $PlansPromos->setPlan(NULL);

            $PlansPromos->setAirlines( json_encode( $dados['airlines']) );
            $PlansPromos->setAirlinesTypes( json_encode( $dados['airlinesTypes']) );

            $em->persist($PlansPromos);
            $em->flush($PlansPromos);

            if(isset($control)) {
                foreach ($control as $keyAirline => $valueAirline) {
                    $Airline = $em->getRepository('Airline')->findOneBy(array('name' => $keyAirline));
                    
                    foreach ($valueAirline as $keyType => $valueType) {
                        $PlansPromoControlConfig = $em->getRepository('PlansPromoControlConfig')->findOneBy(
                            array(
                                'type' => $keyType,
                                'plansPromos' => $PlansPromos->getId(),
                                'airline' => $Airline->getId()
                            )
                        );
                        
                        $PlansPromoControlConfig->setCost($valueType['cost']);
                        $PlansPromoControlConfig->setConfig( json_encode($valueType['configs']) );
                        
                        $em->persist($PlansPromoControlConfig);
                        $em->flush($PlansPromoControlConfig);
                    }
                }
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}