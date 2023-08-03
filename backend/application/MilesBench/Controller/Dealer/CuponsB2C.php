<?php

namespace MilesBench\Controller\Dealer;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;

class CuponsB2C {

    public function saveCupom(Request $request, Response $response) {
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
                $CuponsB2c = $em->getRepository('CuponsB2c')->find($dados['id']);
            } else {
                $CuponsB2c = new \CuponsB2c();
                $CuponsB2c->setCriadoB2c('false');
            }

            $CuponsB2c->setNome($dados['nome']);
            $CuponsB2c->setValor($dados['valor']);
            $CuponsB2c->setPorcentagem($dados['porcentagem']);
            $CuponsB2c->setStatus($dados['status']);
            $CuponsB2c->setDataInicio(new \DateTime($dados['dataInicio']));
            $CuponsB2c->setDataFim(new \DateTime($dados['dataFim']));

            if(isset($dados['userAprovacao'])) {
                $UserAprovacao = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['dealer']));
                $CuponsB2c->setUserAprovacao($UserAprovacao);
            }

            if(isset($dados['dealer'])) {
                $UserDealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['dealer']));
                $CuponsB2c->setDealer($UserDealer);
            } else {
                $CuponsB2c->setDealer($UserPartner);
            }

            $em->persist($CuponsB2c);
            $em->flush($CuponsB2c);

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

    public function loadCupons(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select c.* FROM cupons_b2c c INNER JOIN businesspartner x on x.id = c.dealer_id where c.dealer_id = ".$UserPartner->getId()." ";

        $where = '';
        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where .= " and ( "
                ." c.nome like '%".$dados['searchKeywords']."%' ) ";
                $sql .= $where;
        }
        
        $orderBy = ' order by c.nome ASC ';
        
        $sql = $sql.' OR x.master_client = '.$UserPartner->getId().' ';
        $sql = $sql.$orderBy;

        if(isset($dados['page']) && isset($dados['numPerPage'])) {
            $sql .= " limit " . ( ($dados['page'] - 1) * $dados['numPerPage'] ) . ", " . $dados['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
        } else {
            $stmt = $QueryBuilder->query($sql);
        }
        
        $cupons = array();
        while ($row = $stmt->fetch()) {
            $dealer = '';
            if(isset($row['dealer_id'])) {
                $DealerObj = $em->getRepository('Businesspartner')->find($row['dealer_id']);
                $dealer = $DealerObj->getName();
            }

            $cupons[] = array(
                'id' => $row['id'],
                'nome' => $row['nome'],
                'valor' => (float)$row['valor'],
                'porcentagem' => $row['porcentagem'] == 'true',
                'status' => $row['status'],
                'criadoB2c' => $row['criado_b2c'] == 'true',
                'dataInicio' => $row['data_inicio'],
                'dataFim' => $row['data_fim'],
                'dealer' => $dealer
            );
        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where = " and ( b.nome like '%".$dados['searchKeywords']."%' ) ";
            $sql = "select COUNT(b) as quant FROM CuponsB2c b JOIN b.dealer x where b.dealer = ".$UserPartner->getId()." ".$where;
        } else {
            $sql = "select COUNT(b) as quant FROM CuponsB2c b JOIN b.dealer x where b.dealer = ".$UserPartner->getId()." ";
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'cupons' => $cupons,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function inativarCupom(Request $request, Response $response) {
        $dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

        try {
            $em = Application::getInstance()->getEntityManager();
            if (isset($dados['id'])) {
                $CuponsB2c = $em->getRepository('CuponsB2c')->find($dados['id']);
            }

            $CuponsB2c->setStatus('Cancelado');

            $em->persist($CuponsB2c);
            $em->flush($CuponsB2c);

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