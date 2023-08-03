<?php

namespace MilesBench\Controller\Dealer;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;

class CampanhasB2C {

    public function saveCampanha(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        try {
            $em = Application::getInstance()->getEntityManager();
            if (isset($dados['id'])) {
                $CampanhasB2c = $em->getRepository('CampanhasB2c')->find($dados['id']);
            } else {
                $CampanhasB2c = new \CampanhasB2c();
                $CampanhasB2c->setCodigo($dados['codigo']);

                $jsonToPost = array(
                    "long_url" => "https://www.skymilhas.com.br/?utm_source=".$dados['codigo'],
                    "title" => $dados['nome']
                );
    
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::bitly_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-type: application/json",
                        "Authorization: Bearer ".\MilesBench\Util::bitly_key));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $result = curl_exec($ch);

                $CampanhasB2c->setUrl(json_decode($result, true)['link']);
            }

            if(isset($dados['dealer'])) {
                $UserDealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['dealer']));
                $CampanhasB2c->setDealer($UserDealer);
            } else {
                $CampanhasB2c->setDealer($UserPartner);
            }

            $CampanhasB2c->setNome($dados['nome']);
            $em->persist($CampanhasB2c);
            $em->flush($CampanhasB2c);

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

    public function loadCampanha(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select c.* FROM campanhas_b2c c INNER JOIN businesspartner x on x.id = c.dealer_id where c.dealer_id = ".$UserPartner->getId()." ";

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." c.nome like '%".$dados['searchKeywords']."%' ) ";
                $sql .= $where;
        }
        
        // order
        $orderBy = ' order by c.nome ASC ';
        if(isset($dados['order']) && $dados['order'] != '') {
            if( $dados['order'] == 'last_emission' || $dados['order'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['order'] . ' ASC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['order']) .' ASC ';
            }
        }
        if(isset($dados['orderDown']) && $dados['orderDown'] != '') {
            if( $dados['orderDown'] == 'last_emission' || $dados['orderDown'] == 'countd' ) {
                $orderBy = ' order by ' . $dados['orderDown'] . ' DESC ';
            } else {
                $orderBy = ' order by b.' . Businesspartner::from_camel_case($dados['orderDown']) .' DESC ';
            }
        }
        
        $sql = $sql.' OR x.master_client = '.$UserPartner->getId().' ';
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            $stmt = $QueryBuilder->query($sql);
        }
        
        $campanhas = array();
        while ($row = $stmt->fetch()) {
            $dealer = '';
            if(isset($row['dealer_id'])) {
                $DealerObj = $em->getRepository('Businesspartner')->find($row['dealer_id']);
                $dealer = $DealerObj->getName();
            }

            $campanhas[] = array(
                'id' => $row['id'],
                'nome' => $row['nome'],
                'codigo' => $row['codigo'],
                'url' => $row['url'],
                'dealer' => $dealer
            );
        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where = " and ( b.nome like '%".$dados['searchKeywords']."%' ) ";
            $sql = "select COUNT(b) as quant FROM CampanhasB2c b JOIN b.dealer x where b.dealer = ".$UserPartner->getId()." ".$where;
        } else {
            $sql = "select COUNT(b) as quant FROM CampanhasB2c b JOIN b.dealer x where b.dealer = ".$UserPartner->getId()." ";
        }
        $sql = $sql.' AND x.masterClient = '.$UserPartner->getId().' ';

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'campanhas' => $campanhas,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }
}