<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Cupons {

    public function loadCupons(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

        $em = Application::getInstance()->getEntityManager();
        $Cupons = $em->getRepository('Cupons')->findAll();

        $dataset = array();
        foreach ($Cupons as $key => $value) {
            $dataInicio = '';
            if($value->getDataInicio()) {
                $dataInicio = $value->getDataInicio()->format('Y-m-d H:i:s');
            }

            $dataExpiracao = '';
            if($value->getDataExpiracao()) {
                $dataExpiracao = $value->getDataExpiracao()->format('Y-m-d H:i:s');
            }

            $dataset[] = array(
                'id' => $value->getId(),
                'nome' => $value->getNome(),
                'value' => (float)$value->getValue(),
                'tipo_cupom' => $value->getTipoCupom(),
                'dataInicio' => $dataInicio,
                'dataExpiracao' => $dataExpiracao,
                'used' => $value->getUsed() == '1',
                'valorMinimo' => (float)$value->getValorMinimo(),
                'quantUsos' => (int)$value->getQuantUsos(),
                'valid_voos' => $value->getValidVoos(),
                'aereas' => explode(',', $value->getAereas()),
                'pagante' => $value->getPagante(),
                'milhas' => $value->getMilhas()
            );
        }

        $response->setDataset($dataset);
    }

    public function saveCupons(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();

            if(isset($dados['id'])) {
                $Cupons = $em->getRepository('Cupons')->find($dados['id']);
            } else {
                $Cupons = new \Cupons();
                $Cupons->setUsed(false);
            }

            $Cupons->setValue($dados['value']);
            if(isset($dados['valorMinimo'])) {
                $Cupons->setValorMinimo($dados['valorMinimo']);
            }
            $Cupons->setTipoCupom($dados['tipo_cupom']);
            if(isset($dados['dataInicio'])) {
                $Cupons->setDataInicio(new \DateTime($dados['dataInicio']));
            }
            if(isset($dados['dataExpiracao'])) {
                $Cupons->setDataExpiracao(new \DateTime($dados['dataExpiracao']));
            }
            $Cupons->setNome($dados['nome']);
            if(isset($dados['quantUsos'])) {
                $Cupons->setQuantUsos($dados['quantUsos']);
            }
            if(isset($dados['valid_voos'])) {
                $Cupons->setValidVoos($dados['valid_voos']);
            }
            if(isset($dados['selectedAereas'])) {
                // Array filter para remover empty do array
                $aereas = implode(',', array_filter($dados['selectedAereas']));

                $Cupons->setAereas($aereas);
            } else {
                $Cupons->setAereas(null);
            }

            if(isset($dados['milhas'])) {
                if($dados['milhas'] == 'true')
                    $Cupons->setMilhas(true);
                else
                    $Cupons->setMilhas(false);
            }
            if(isset($dados['pagante'])) {
                if($dados['pagante'] == 'true')
                    $Cupons->setPagante(true);
                else
                    $Cupons->setPagante(false);
            }

            $em->persist($Cupons);
            $em->flush($Cupons);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function deleteCupons(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }
        try {
            $em = Application::getInstance()->getEntityManager();

            if(isset($dados['id'])) {
                $Cupons = $em->getRepository('Cupons')->find($dados['id']);
                $em->remove($Cupons);
                $em->flush($Cupons);
            }

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Cadastro atualizado com sucesso');
            $response->addMessage($message);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

    public function loadCuponsB2C(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['businesspartner'])) {
            $UserPartner = $dados['businesspartner'];
        }
        if(isset($dados['data'])){
            $filter = $dados['data'];
        }
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $sql = " select c.* FROM cupons_b2c c INNER JOIN businesspartner x on x.id = c.dealer_id where c.dealer_id <> 0 ";

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
            $cupons[] = array(
                'id' => $row['id'],
                'nome' => $row['nome'],
                'valor' => (float)$row['valor'],
                'porcentagem' => $row['porcentagem'] == 'true',
                'status' => $row['status'],
                'criadoB2c' => $row['criado_b2c'] == 'true',
                'dataInicio' => $row['data_inicio'],
                'dataFim' => $row['data_fim']
            );
        }

        if(isset($dados['searchKeywords']) && $dados['searchKeywords'] != '') {
            $where = " ( b.nome like '%".$dados['searchKeywords']."%' ) ";
            $sql = "select COUNT(b) as quant FROM CuponsB2c b where ".$where;
        } else {
            $sql = "select COUNT(b) as quant FROM CuponsB2c b ";
        }

        $query = $em->createQuery($sql);
        $Quant = $query->getResult();

        $dataset = array(
            'cupons' => $cupons,
            'total' => $Quant[0]['quant']
        );
        $response->setDataset($dataset);
    }

    public function saveCupomB2C(Request $request, Response $response) {
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
                $Dealer = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['dealer']));
                $CuponsB2c->setDealer($Dealer);
            }

            $CuponsB2c->setNome($dados['nome']);
            $CuponsB2c->setValor($dados['valor']);
            $CuponsB2c->setPorcentagem($dados['porcentagem']);
            $CuponsB2c->setStatus($dados['status']);
            $CuponsB2c->setCriadoB2c($dados['criadoB2c']);
            $CuponsB2c->setDataInicio(new \DateTime($dados['dataInicio']));
            $CuponsB2c->setDataFim(new \DateTime($dados['dataFim']));

            if(isset($dados['userAprovacao'])) {
                $UserAprovacao = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['dealer']));
                $CuponsB2c->setUserAprovacao($UserAprovacao);
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

    public function aprovarCupom(Request $request, Response $response) {
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
            }

            $CuponsB2c->setStatus('Aprovado');
            $CuponsB2c->setUserAprovacao($UserPartner);

            $em->persist($CuponsB2c);
            $em->flush($CuponsB2c);

            $jsonToPost = array(
                'cupom' => $dados
            );

            $ch = curl_init();
            $url = \MilesBench\Util::skymilhas_url_production.'cupom/ativar';
            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                $url = \MilesBench\Util::skymilhas_url_homologacao.'cupom/ativar';
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

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

            $jsonToPost = array(
                'cupom' => $dados
            );

            $ch = curl_init();
            $url = \MilesBench\Util::skymilhas_url_production.'cupom/inativarCupom';
            $env = getenv('ENV') ? getenv('ENV') : 'production';
            if($env != 'production') {
                $url = \MilesBench\Util::skymilhas_url_homologacao.'cupom/inativarCupom';
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonToPost));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

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
