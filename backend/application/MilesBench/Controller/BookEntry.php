<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class BookEntry {

    public function load(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        } else {
            $dados = array(
                '_dateFrom' => (new \DateTime())->modify('first day of this month')->format('Y-m-d'),
                '_dateTo' => (new \DateTime())->modify('last day of this month')->format('Y-m-d')
            );
        }

        $em = Application::getInstance()->getEntityManager();

        $sql = "select b FROM BookEntry b ";
        $whereClause = ' WHERE ';
        $and = '';

        if(isset($dados['_dateFrom']) && $dados['_dateFrom'] != '') {
            $whereClause = $whereClause.$and. " b.date >= '".$dados['_dateFrom']."' ";
            $and = ' AND ';
        }

        if(isset($dados['_dateTo']) && $dados['_dateTo'] != '') {
            $dateTo = (new \DateTime($dados['_dateTo']))->modify('+1 day');
            $whereClause = $whereClause.$and. " b.date < '".$dateTo->format('Y-m-d')."' ";
            $and = ' AND ';
        }

        if (!($whereClause == ' WHERE ')) {
           $sql = $sql.$whereClause; 
        };

        $query = $em->createQuery($sql);
        $BookEntry = $query->getResult();
        $dataset = array();
        foreach($BookEntry as $item) {
            $dataset[] = array(
                'id' => $item->getId(),
                'cost_center_name' => $item->getCostCenter()->getName(),
                'cost_center_type' => $item->getCostCenter()->getType(),
                'date' => $item->getDate()->format('d-m-Y'),
                'value' => (float)$item->getValue(),
                'description' => $item->getDescription()
            );
        }
        $response->setDataset($dataset);
    }

    public function loadBookEntryCurrentMonth(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        $day = (new \DateTime())->modify('first day of this month');
        $Lastday = (new \DateTime())->modify('first day of this month')->modify('+1 month');

        $sql = "select sum(b.value) as totalCost FROM BookEntry b JOIN b.costCenter c where b.date >= '".$day->format('Y-m-d')."' and b.date < '".$Lastday->format('Y-m-d')."' and c.type = 'C' ";
        $query = $em->createQuery($sql.$whereClause);
        $totalCost = $query->getResult();

        $sql = "select sum(b.value) as value FROM BookEntry b JOIN b.costCenter c where b.date >= '".$day->format('Y-m-d')."' and b.date < '".$Lastday->format('Y-m-d')."' and c.type = 'R' ";
        $query = $em->createQuery($sql.$whereClause);
        $value = $query->getResult();

        $response->setDataset($value[0]['value'] - $totalCost[0]['totalCost']);
    }

    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        if (isset($dados['id'])) {
            $BookEntry = $em->getRepository('BookEntry')->findOneBy(array('id' => $dados['id']));
        } else {
            $BookEntry = new \BookEntry();
        }

        try {

            $em->getConnection()->beginTransaction();

            $BookEntry->setCostCenter(
                $em->getRepository('CostCenter')->findOneBy(array('id' => $dados['cost_center_id']))
            );

            $BookEntry->setDate(new \DateTime($dados['_date']));
            $BookEntry->setValue($dados['value']);
            $BookEntry->setDescription($dados['description']);

            $em->persist($BookEntry);
            $em->flush($BookEntry);

            $em->getConnection()->commit();

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

    public function loadTimeLine(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        } else {
            $dados = array(
                '_dateFrom' => (new \DateTime())->modify('first day of this month')->modify('-2 month')->format('Y-m-d'),
                '_dateTo' => (new \DateTime())->modify('last day of this month')->modify('-2 month')->format('Y-m-d')
            );
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();
        $months = array();
        $dateFrom = (new \DateTime($dados['_dateFrom']));

        while ($dateFrom < new \DateTime()) {
            $HigherCosts = array();

            $dateFrom = (new \DateTime($dados['_dateFrom']));
            $dateTo = (new \DateTime($dados['_dateTo']))->modify('+1 day');

            $sql = "select SUM(b.value) as costs FROM BookEntry b JOIN b.costCenter c WHERE b.date >= '".$dados['_dateFrom']."' and b.date < '".$dateTo->format('Y-m-d')."' and c.type in ('C') ";
            $query = $em->createQuery($sql);
            $SUMCosts = $query->getResult();

            $sql = "select SUM(b.value) as income FROM BookEntry b JOIN b.costCenter c WHERE b.date >= '".$dados['_dateFrom']."' and b.date < '".$dateTo->format('Y-m-d')."' and c.type in ('R') ";
            $query = $em->createQuery($sql);
            $SUMIncome = $query->getResult();


            // get higuer costs
            $sql = "select b FROM BookEntry b JOIN b.costCenter c WHERE b.date >= '".$dados['_dateFrom']."' and b.date < '".$dateTo->format('Y-m-d')."' and c.type in ('C') group by b.costCenter order by b.value ";
            $query = $em->createQuery($sql);
            $Costs = $query->setMaxResults(5)->getResult();

            foreach ($Costs as $cost) {
                $HigherCosts[] = array(
                    'name' => $cost->getCostCenter()->getName()
                );
            }

            // JSON estructure
            $months[] = array(
                'SUMCosts' => (float)$SUMCosts[0]['costs'],
                'SUMIncome' => (float)$SUMIncome[0]['income'],
                'HigherCosts' => $HigherCosts,
                'month' => $dateFrom->format('m-Y') 
            );

            $dados['_dateFrom'] = $dateFrom->modify('+1 month')->format('Y-m-d');
            $dados['_dateTo'] = $dateTo->modify('+1 month')->modify('-1 day')->format('Y-m-d');
        }

        $dataset = array( 
            'months' => $months
        );

        $response->setDataset($dataset);
    }

    public function loadGridMonth(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        } else {
            $dados = array(
                '_dateFrom' => (new \DateTime())->modify('first day of this month')->format('Y-m-d'),
                '_dateTo' => (new \DateTime())->modify('last day of this month')->format('Y-m-d')
            );
        }

        $em = Application::getInstance()->getEntityManager();
        $dataset = array();
        $days = array();

        $dateFrom = (new \DateTime($dados['_dateFrom']));
        $dateTo = (new \DateTime($dados['_dateTo']))->modify('+1 day');


        $Income = $em->getRepository('CostCenter')->findBy(array('type' => 'R'));
        $Costs = $em->getRepository('CostCenter')->findBy(array('type' => 'C'));

        $line = 0;

        while ($dateFrom < $dateTo) {
            $dateFrom = (new \DateTime($dados['_dateFrom']));
            $days[] = array(
                'date' => $dateFrom->format('Y-m-d'),
                'name' => $dateFrom->format('d/m/Y'),
                'width' => 80,
                'enableCellEdit' => false,
                'cellTemplate' => '<div><div ng-click="grid.appScope.cellClicked(row,col)" class="ui-grid-cell-contents" title="TOOLTIP">{{COL_FIELD CUSTOM_FILTERS}}</div></div>'
            );

            $dados['_dateFrom'] = $dateFrom->modify('+1 day')->format('Y-m-d');
        }

        array_unshift($days, array(
            'date' => "FLUXO_DE_CAIXA",
            'name' => "FLUXO_DE_CAIXA",
            'width' => 200,
            'enableCellEdit' => false,
            'pinnedLeft' => true
        ));

        $dataset = array();

        // costs
        foreach ($days as $day) {
            if($day['date'] == 'FLUXO_DE_CAIXA') {
                $dataset[$line][$day['name']] = 'CUSTOS';
            } else {
                $dataset[$line][$day['name']] = '';
            }
        }
        $line++;

        foreach ($Costs as $costCenter) {
            foreach ($days as $day) {

                if($day['date'] == 'FLUXO_DE_CAIXA') {
                    $dataset[$line][$day['name']] = $costCenter->getName();
                } else {
                    $sql = "select SUM(b.value) as costs FROM BookEntry b JOIN b.costCenter c WHERE b.date >= '".$day['date']."' and b.date < '".(new \DateTime($day['date']))->modify('+1 day')->format('Y-m-d')."' and c.name = '".$costCenter->getName()."'  ";
                    $query = $em->createQuery($sql);
                    $SUM = $query->getResult();

                    $dataset[$line][$day['name']] = -(float)$SUM[0]['costs'];
                }
            }
            $line++;
        }

        foreach ($days as $day) {

            if($day['date'] == 'FLUXO_DE_CAIXA') {
                $dataset[$line][$day['name']] = 'SUBTOTAL';
            } else {
                $sql = "select SUM(b.value) as costs FROM BookEntry b JOIN b.costCenter c WHERE b.date >= '".$day['date']."' and b.date < '".(new \DateTime($day['date']))->modify('+1 day')->format('Y-m-d')."' and c.type = 'C' ";
                $query = $em->createQuery($sql);
                $SUM = $query->getResult();

                $dataset[$line][$day['name']] = -(float)$SUM[0]['costs'];
            }
        }
        $line++;

        // incoming
        foreach ($days as $day) {
            if($day['date'] == 'FLUXO_DE_CAIXA') {
                $dataset[$line][$day['name']] = 'RECEITAS';
            } else {
                $dataset[$line][$day['name']] = '';
            }
        }
        $line++;

        foreach ($Income as $costCenter) {
            foreach ($days as $day) {

                if($day['date'] == 'FLUXO_DE_CAIXA') {
                    $dataset[$line][$day['name']] = $costCenter->getName();
                } else {
                    $sql = "select SUM(b.value) as costs FROM BookEntry b JOIN b.costCenter c WHERE b.date >= '".$day['date']."' and b.date < '".(new \DateTime($day['date']))->modify('+1 day')->format('Y-m-d')."' and c.name = '".$costCenter->getName()."'  ";
                    $query = $em->createQuery($sql);
                    $SUM = $query->getResult();

                    $dataset[$line][$day['name']] = (float)$SUM[0]['costs'];
                }
            }
            $line++;
        }

        foreach ($days as $day) {

            if($day['date'] == 'FLUXO_DE_CAIXA') {
                $dataset[$line][$day['name']] = 'SUBTOTAL';
            } else {
                $sql = "select SUM(b.value) as costs FROM BookEntry b JOIN b.costCenter c WHERE b.date >= '".$day['date']."' and b.date < '".(new \DateTime($day['date']))->modify('+1 day')->format('Y-m-d')."' and c.type = 'R' ";
                $query = $em->createQuery($sql);
                $SUM = $query->getResult();

                $dataset[$line][$day['name']] = (float)$SUM[0]['costs'];
            }
        }
        $line++;

        // total

        $response->setDataset(array('data' => $dataset, 'coluns' => $days));
    }

    public function saveGridValue(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        try {

            $CostCenter = $em->getRepository('CostCenter')->findOneBy(array('name' => $dados['type']));
            if(!$CostCenter) {
                throw new \Exception("Centro de custo divergente!", 1);
            }

            $date = (new \Datetime(substr($dados['date'],6,4).'-'.substr($dados['date'],3,2).'-'.substr($dados['date'],0,2)))->format('Y-m-d');

            $sql = "select b FROM BookEntry b JOIN b.costCenter c WHERE b.date = '".$date."' and b.costCenter = '".$CostCenter->getId()."' ";
            $query = $em->createQuery($sql);
            $BookEntry = $query->getResult();

            if(count($BookEntry) == 0) {
                $BookEntry = new \BookEntry();
                $BookEntry->setDate(new \DateTime($date));
                $BookEntry->setDescription('');
                $BookEntry->setCostCenter($CostCenter);
            } else {
                $BookEntry = $BookEntry[0];
            }

            $BookEntry->setValue($dados['value']);

            $em->persist($BookEntry);
            $em->flush($BookEntry);

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

    public function loadBookEntryMonth(Request $request, Response $response) {
        try {

            $em = Application::getInstance()->getEntityManager();

            $dataset = array();
            for ( $i = 12; $i >= 0; $i-- ) { 
                $monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('first day of this month');
                $monthAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('last day of this month')->modify('+1 day');


                $sql = "select SUM(b.value) as costs FROM BookEntry b JOIN b.costCenter c WHERE b.date BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' and c.type = 'C' ";
                $query = $em->createQuery($sql);
                $Costs = $query->getResult();

                $sql = "select SUM(b.value) as costs FROM BookEntry b JOIN b.costCenter c WHERE b.date BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' and c.type = 'R' ";
                $query = $em->createQuery($sql);
                $income = $query->getResult();

                $dataset[] = array(
                    'total' => (float)$income[0]['costs'] - (float)$Costs[0]['costs'],
                    'incoming' => (float)$income[0]['costs'],
                    'costs' => -(float)$Costs[0]['costs'],
                    'month' => $monthsAgo->format('m-Y')
                );
            }
            $response->setDataset($dataset);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}
