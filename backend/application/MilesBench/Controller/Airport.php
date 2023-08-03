<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Airport {

    public function load(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $Airport = $em->getRepository('Airport')->findAll(array('code' => 'ASC'));

        $dataset = array();
        foreach($Airport as $item){

            $City = $item->getCity();
            if ($City) {
                $cityfullname = $City->getName() . ', ' . $City->getState();
                $cityname = $City->getName();
                $citystate = $City->getState();
            } else {
                $cityfullname = '';
                $cityname = '';
                $citystate = '0';
            }

            $dataset[] = array(
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'label' => $item->getCode().' '.$item->getName(),
                'international' => ($item->getInternational() == 'true'),
                'location' => $item->getLocation(),
                'cityfullname' => $cityfullname,
                'cityname' => $cityname,
                'citystate' => $citystate
            );
        }
        $response->setDataset($dataset);
    }


    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            if (isset($dados['id'])) {
                $Airport = $em->getRepository('Airport')->find($dados['id']);
            } else {
                $Airport = new \Airport();
            }

            $Airport->setName($dados['name']);
            $Airport->setCode($dados['code']);
            if(isset($dados['international']) && $dados['international'] != '') {
                $Airport->setInternational($dados['international']);
            }
            if(isset($dados['location']) && $dados['location'] != '') {
                $Airport->setLocation($dados['location']);
            }

            if (isset($dados['cityname']) && $dados['cityname'] != '') {
                $city = $em->getRepository('City')->findOneBy(array('name' => $dados['cityname'], 'state' => $dados['citystate']));
                if($city) {
                    $Airport->setCity($city);
                }
            }

            $em->persist($Airport);
            $em->flush($Airport);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function MigrateAirports(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        require_once './MilesBench/Controller/Json/imports-airports.php';

        foreach ($airports as $key => $value) {
            $WsAirport = new \WsAirport();
            $WsAirport->setLabel($value["label"]);
            $WsAirport->setIatacode($value["iataCode"]);
            $WsAirport->setInternational($value["international"]);
            $WsAirport->setCityName($value["city"]["name"]);
            $WsAirport->setCityIatacode($value["city"]["iataCode"]);
    
            $em->persist($WsAirport);
            $em->flush($WsAirport);
        }

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro alterado com sucesso');
        $response->addMessage($message);
    }

    public function GetJson(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $WsAirport = $em->getRepository('WsAirport')->findAll();

        $airports = array();
        foreach($WsAirport as $item){
            $airports[] = array(
                "label" =>  $item->getLabel(),
                "iataCode" =>  $item->getIatacode(),
                "city" => array(
                   "iataCode" =>  $item->getCityIatacode(),
                   "name" =>  $item->getCityName()
                ),
                "international" =>  $item->getInternational()
            );
        }

        $dataset = array(
            'airports' => $airports
        );
        $response->setDataset($dataset);
    }

    public function SaveJson(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $WsAirport = $em->getRepository('WsAirport')->findOneBy( array( 'iatacode' => $dados["iataCode"] ) );
        if (!$WsAirport) {
            $WsAirport = new \WsAirport();
        }

        $WsAirport->setLabel($dados["label"]);
        $WsAirport->setIatacode($dados["iataCode"]);
        $WsAirport->setInternational($dados["international"]);
        $WsAirport->setCityName($dados["city"]["name"]);
        $WsAirport->setCityIatacode($dados["city"]["iataCode"]);
        $em->persist($WsAirport);
        $em->flush($WsAirport);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro alterado com sucesso');
        $response->addMessage($message);
    }

    public function DeleteJson(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $WsAirport = $em->getRepository('WsAirport')->findOneBy( array( 'iatacode' => $dados["iataCode"] ) );
        if($WsAirport) {
            $em->remove($WsAirport);
            $em->flush($WsAirport);
        }
        
        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro removido com sucesso');
        $response->addMessage($message);
    }

    public function mediaTaxa(Request $request, Response $response) {
        $dados = json_decode($request->getRow(), true);
        $em = Application::getInstance()->getEntityManager();
        $conn = Application::getInstance()->getQueryBuilder();

        try {
            $aeroporto_de = $em->getRepository('Airport')->findOneBy(array(
                'code' => $dados['de']
            ));

            $aeroporto_para = $em->getRepository('Airport')->findOneBy(array(
                'code' => $dados['para']
            ));

            if(!$aeroporto_de || !$aeroporto_para) {
                throw new \Exception("Aeroporto não encontrado!");
            }

            $de = $aeroporto_de->getId();
            $para = $aeroporto_para->getId();
            
            $sql = "SELECT AVG(taxes.tax) AS average_tax FROM (SELECT s.tax FROM sale s WHERE s.airport_from = " . $de . " AND s.airport_to = " . $para . " ORDER BY s.issue_date DESC LIMIT 10) taxes";

            $result = $conn->query($sql);
            $valor = $result->fetch()['average_tax'];
            
            if($valor == null){
                $sql = "SELECT AVG(taxes.tax) AS average_tax FROM (SELECT s.tax FROM sale s ORDER BY s.issue_date DESC LIMIT 1000) taxes";
                
                $result = $conn->query($sql);
                $valor = $result->fetch()['average_tax'];
            }

            $response->setDataset(array('valor' => $valor));
        } catch(\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }
}