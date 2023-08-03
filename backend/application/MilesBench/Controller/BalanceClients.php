<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class BalanceClients {

    public function loadClientsBalance(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select COUNT(s.id) as emissions, MAX(s.client) as client from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer." group by s.client";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $dataset = array();
        foreach($Sales as $sale){
            $client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale['client']));
            $dataset[] = array(
                'data' => $sale['emissions'],
                'label' => $client->getName().' - '.$sale['emissions']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadAirlineBalance(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';


        $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select COUNT(s.id) as emissions, MAX(s.airline) as airline from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer." group by s.airline";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $dataset = array();
        foreach($Sales as $sale){
            $Airline = $em->getRepository('Airline')->findOneBy(array('id' => $sale['airline']));
            $airlineName = '';
            if($Airline) {
                $airlineName = $Airline->getName();
            }
            $dataset[] = array(
                'data' => $sale['emissions'],
                'label' => $airlineName
            );
        }
        $response->setDataset($dataset);
    }
    
    public function loadAirlineMiles(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select SUM(s.milesUsed) as emissions, MAX(s.airline) as airline from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer." group by s.airline";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $dataset = array();
        foreach($Sales as $sale){
            $Airline = $em->getRepository('Airline')->findOneBy(array('id' => $sale['airline']));
            $airlineName = '';
            if($Airline) {
                $airlineName = $Airline->getName();
            }
            $dataset[] = array(
                'data' => $sale['emissions'],
                'label' => $airlineName
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientsTotal(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
        $dataset = array();
        
        $sql = "select COUNT(DISTINCT s.client) as emissions, MAX(s.client) as client from Sale s JOIN s.client b where b.status<>'Arquivado' and s.issueDate >= '".$daysAgo->format('Y-m-d')."' and s.status='Emitido' ".$dealer;
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        foreach($Sales as $sale){
            $dataset[] = array(
                'data' => $sale['emissions'],
                'label' => 'EMITIRAM - '.$sale['emissions']
            );
        }

        $sql = "select COUNT(DISTINCT s.client) as clients from Sale s JOIN s.client b where b.partnerType = 'C' and b.status<>'Arquivado' and b.paymentType='Antecipado' and s.issueDate < '".$daysAgo->format('Y-m-d')."' ".$dealer;
        $query = $em->createQuery($sql);
        $Businesspartner = $query->getResult();

        foreach($Businesspartner as $partner){
            $dataset[] = array(
                'data' => $partner['clients'],
                'label' => 'N emitiram - Antecipado - '.$partner['clients']
            );
        }

        $sql = "select COUNT(DISTINCT s.client) as clients from Sale s JOIN s.client b where b.partnerType = 'C' and b.status<>'Arquivado' and b.paymentType='Boleto' and s.issueDate < '".$daysAgo->format('Y-m-d')."' ".$dealer;
        $query = $em->createQuery($sql);
        $Businesspartner = $query->getResult();

        foreach($Businesspartner as $partner){
            $dataset[] = array(
                'data' => $partner['clients'],
                'label' => 'N emitiram - Boleto - '.$partner['clients']
            );
        }

        $sql = "select COUNT(DISTINCT s.client) as clients from Sale s JOIN s.client b where b.partnerType = 'C' and b.status<>'Arquivado' and b.paymentType is NULL and s.issueDate < '".$daysAgo->format('Y-m-d')."' ".$dealer;
        $query = $em->createQuery($sql);
        $Businesspartner = $query->getResult();

        foreach($Businesspartner as $partner){
            $dataset[] = array(
                'data' => $partner['clients'],
                'label' => 'N emitiram - Outros - '.$partner['clients']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientsTotalChart(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $dataset = array();
        for ($i=12; $i >= 0; $i--) { 
            $monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('first day of this month');
            $monthAgo = (new \DateTime())->modify('today')->modify('-'.$i.' month')->modify('last day of this month');

            $sql = "select COUNT(s.id) as sales FROM Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' ".$dealer;
            $query = $em->createQuery($sql);
            $Sales = $query->getResult();

            $sql = "select COUNT(s.id) as sales FROM Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' AND s.airline = '1' ".$dealer;
            $query = $em->createQuery($sql);
            $LATAM = $query->getResult();

            $sql = "select COUNT(s.id) as sales FROM Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' AND s.airline = '2' ".$dealer;
            $query = $em->createQuery($sql);
            $GOL = $query->getResult();

            $sql = "select COUNT(s.id) as sales FROM Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' AND s.airline = '3' ".$dealer;
            $query = $em->createQuery($sql);
            $AZUL = $query->getResult();

            $sql = "select COUNT(s.id) as sales FROM Sale s JOIN s.client b where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' AND s.airline = '4' ".$dealer;
            $query = $em->createQuery($sql);
            $AVIANCA = $query->getResult();

            $dataset[] = array(
                'sales' => (float)$Sales[0]['sales'],
                'LATAM' => (float)$LATAM[0]['sales'],
                'GOL' => (float)$GOL[0]['sales'],
                'AZUL' => (float)$AZUL[0]['sales'],
                'AVIANCA' => (float)$AVIANCA[0]['sales'],
                'month' => $monthsAgo->format('Y-m')
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientsCancelSales(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select COUNT(s.id) as emissions, MAX(s.client) as client from Sale s JOIN s.client b where s.issueDate >= '".$daysAgo->format('Y-m-d')."' and (s.status='Cancelamento Solicitado' or s.status='Cancelamento Efetivado' or s.status='Cancelamento Nao Solicitado') ".$dealer." group by s.client";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $dataset = array();
        foreach($Sales as $sale){
            $client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale['client']));
            $dataset[] = array(
                'data' => $sale['emissions'],
                'label' => $client->getName().' - '.$sale['emissions']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientsDaily(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $dataset = array();
        for ($i=14; $i >= 0; $i--) { 
            $monthsAgo = (new \DateTime())->modify('today')->modify('-'.$i.' day');
            $monthAgo = (new \DateTime())->modify('today')->modify('-'.($i-1).' day');
        
            $sql = "select COUNT(s) as emissions from Sale s where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' ".$dealer." ";
            $query = $em->createQuery($sql);
            $emissions = $query->getResult();

            $sql = "select COUNT(DISTINCT s.client) as emissions from Sale s where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' AND  '".$monthAgo->format('Y-m-d')."' ".$dealer." ";
            $query = $em->createQuery($sql);
            $Sales = $query->getResult();

            $dataset[] = array(
                'sales' => $emissions[0]['emissions'],
                'clients' => $Sales[0]['emissions'],
                'date' => $monthsAgo->format('Y-m-d')
            );
        }
        $response->setDataset($dataset);
    }

    public function loadClientsStates(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select COUNT(s.id) as emissions, MAX(c.state) as state  from Sale s JOIN s.client b LEFT JOIN b.city c where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by c.state";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $dataset = array();
        foreach($Sales as $sale){
            $dataset[] = array(
                'data' => $sale['emissions'],
                'label' => $sale['state'].' - '.$sale['emissions']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadCountClientesPerStates(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $daysAgo = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
        
        $sql = "select COUNT(DISTINCT s.client) as emissions, MAX(c.state) as state  from Sale s JOIN s.client b LEFT JOIN b.city c where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by c.state";
        $query = $em->createQuery($sql);
        $Sales = $query->getResult();

        $dataset = array();
        foreach($Sales as $sale){
            $dataset[] = array(
                'data' => $sale['emissions'],
                'label' => $sale['state'].' - '.$sale['emissions']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadTopParts(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $dealer = '';
        $and = '';

        $daysAgo = (new \DateTime())->modify('today')->modify('-30 day');
        
        $sql = "select MAX(s.airportFrom) as airport, COUNT(s.airportFrom) as quant from Sale s where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by s.airportFrom HAVING COUNT(s.airportFrom) > 1 order by quant DESC ";
        $query = $em->createQuery($sql);
        $Sales = $query->setMaxResults(10)->getResult();

        $airportFrom = array();
        foreach ($Sales as $airport) {
            $airportName = '';
            $Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airport']));
            if($Airport) {
                $airportName = $Airport->getCode();
            }
            $airportFrom[] = array(
                'airport' => $airportName,
                'count' => $airport['quant']
            );
        }

        $sql = "select MAX(s.airportTo) as airport, COUNT(s.airportTo) as quant from Sale s where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by s.airportTo HAVING COUNT(s.airportTo) > 1 order by quant DESC ";
        $query = $em->createQuery($sql);
        $Sales = $query->setMaxResults(10)->getResult();

        $airportTo = array();
        foreach ($Sales as $airport) {
            $airportName = '';
            $Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airport']));
            if($Airport) {
                $airportName = $Airport->getCode();
            }
            $airportTo[] = array(
                'airport' => $airportName,
                'count' => $airport['quant']
            );
        }

        $sql = "select MAX(s.airportTo) as airportTo, MAX(s.airportFrom) as airportFrom, COUNT(s.id) as quant from Sale s where s.issueDate >= '".$daysAgo->format('Y-m-d')."' ".$dealer." group by s.airportTo, s.airportFrom order by quant DESC ";
        $query = $em->createQuery($sql);
        $Sales = $query->setMaxResults(20)->getResult();

        $trechos = array();
        foreach ($Sales as $airport) {
            $airportFromName = '';
            $airportToName = '';
            $Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airportTo']));
            if($Airport) {
                $airportFromName = $Airport->getCode();
            }
            $Airport = $em->getRepository('Airport')->findOneBy(array('id' => $airport['airportFrom']));
            if($Airport) {
                $airportToName = $Airport->getCode();
            }
            $trechos[] = array(
                'airport' => $airportFromName.'-'.$airportToName,
                'count' => $airport['quant']
            );
        }

        $dataset = array(
            'from' => $airportFrom,
            'to' => $airportTo,
            'trechos' => $trechos
        );
        $response->setDataset($dataset);
    }

    public function loadDealersAnalysis(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
        $em = Application::getInstance()->getEntityManager();

        $sql = "select b FROM Businesspartner b where b.partnerType like '%D%'";
        $query = $em->createQuery($sql);
        $Dealers = $query->getResult();

        $dataset = array();
        foreach($Dealers as $dealer){

            $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $dealer->getId()));
            $clients = '0';
            $andD = ',';
            foreach ($ClientsDealers as $dealers) {
                $clients = $clients.$andD.$dealers->getClient()->getId();
                $andD = ',';
            }

            $sql = "select COUNT(c) as clients FROM Businesspartner c where c.partnerType like '%C%' and c.dealer = '".$dealer->getId()."' or c.id in (".$clients.") ";
            $query = $em->createQuery($sql);
            $partners = $query->getResult();

            $sql = "select COUNT(s) as sales, SUM(s.milesOriginal) as milesOriginal, SUM(s.amountPaid - s.tax - s.duTax) as values FROM Sale s JOIN s.client c where c.dealer = '".$dealer->getId()."' or c.id in (".$clients.") ";
            $query = $em->createQuery($sql);
            $sales = $query->getResult();

            $dataset[] = array(
                'name' => $dealer->getName(),
                'clients' => (float)$partners[0]['clients'],
                'sales' => (float)$sales[0]['sales'],
                'milesOriginal' => (float)$sales[0]['milesOriginal'],
                'values' => (float)$sales[0]['values']
            );
        }
        $response->setDataset($dataset);
    }

    public function loadDealersAnalysisMonth(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();

        $namesDealers = array(
            'Ana Flavia',
            'ANDRE D. MATTIOLI L.',
            'Cleiton',
            'Edvaldo',
            'Gleisa',
            'JUNIOR LINS',
            'Rafaelle Representante'
        );

        $sql = "select b FROM Businesspartner b where b.partnerType like '%D%'";
        $query = $em->createQuery($sql);
        $Dealers = $query->getResult();

        $line = 0;

        $months = array();
        $dataset = array();

        if( isset($dados)  && (isset($dados['_issueDateFrom']) || isset($dados['_issueDateTo'])) ) {
            if(!isset($dados['_issueDateFrom'])) {
                $dados['_issueDateFrom'] = (new \DateTime())->modify('-6 month')->modify('first day of this month')->format('Y-m-d');
            }

            if(!isset($dados['_issueDateTo'])) {
                $dados['_issueDateTo'] = (new \DateTime())->modify('last day of this month')->modify('+1 day')->format('Y-m-d');
            }

            $monthsAgo = (new \DateTime($dados['_issueDateFrom']));
            $lastMonth = (new \DateTime($dados['_issueDateTo']));

            while ($monthsAgo <= $lastMonth) {
                $months[] = array( 'name' => $monthsAgo->format('Y-m-d'), 'label' => $monthsAgo->format('m-Y'));

                $monthsAgo->modify('+1 month');
            }

        } else {
            for ($i=5; $i >= 0; $i--) { 
                $monthsAgo = (new \DateTime())->modify('-'.$i.' month')->modify('first day of this month');
                $monthAgo = (new \DateTime())->modify('-'.$i.' month')->modify('last day of this month')->modify('+1 day');;

                $months[] = array( 'name' => $monthsAgo->format('Y-m-d'), 'label' => $monthsAgo->format('m-Y'));
            }
        }

        array_unshift($months, array( 'name' => "Representantes"));

        $months[] = array( 'name' => 'Totais');

        foreach($Dealers as $dealer){

            $ClientsDealers = $em->getRepository('ClientsDealers')->findBy(array('dealer' => $dealer->getId()));
            $clients = '0';
            $andD = ',';
            foreach ($ClientsDealers as $dealers) {
                $clients = $clients.$andD.$dealers->getClient()->getId();
                $andD = ',';
            }

            foreach ($months as $month) {

                if($month['name'] == 'Representantes') {
                    $dataset[$line][$month['name']] = $dealer->getName();

                    $pos = in_array($dealer->getName(), $namesDealers);

                    if( !$pos === false ) {
                        $dataset[$line]['priority'] = 0;
                    } else {
                        $dataset[$line]['priority'] = 1;
                    }

                } else if($month['name'] == 'Totais') {

                    $sql = "select COUNT(c) as clients FROM Businesspartner c ".
                        " where c.partnerType like '%C%' ".
                        " and c.dealer = '".$dealer->getId()."' or c.id in (".$clients.") ";
                    $query = $em->createQuery($sql);
                    $partners = $query->getResult();

                    $sql = "select COUNT(s) as sales, SUM(s.milesOriginal) as milesOriginal, SUM(s.amountPaid - s.tax - s.duTax) as values FROM Sale s ".
                        " JOIN s.client c ".
                        " where (c.dealer = '".$dealer->getId()."' or c.id in (".$clients.") ) ";

                    $query = $em->createQuery($sql);
                    $sales = $query->getResult();

                    $dataset[$line][$month['name']] = array(
                        'clients' => (float)$partners[0]['clients'],
                        'sales' => (float)$sales[0]['sales'],
                        'milesOriginal' => (float)$sales[0]['milesOriginal'],
                        'values' => (float)$sales[0]['values']
                    );

                } else {

                    $monthsAgo = (new \DateTime($month['name']));
                    $monthAgo = (new \DateTime($month['name']))->modify('+1 month');

                    $sql = "select COUNT(c) as clients FROM Businesspartner c ".
                        " where c.partnerType like '%C%' ".
                        " and c.registerDate BETWEEN '".$monthsAgo->format('Y-m-d')."' and '".$monthAgo->format('Y-m-d')."' ".
                        " and c.dealer = '".$dealer->getId()."' or c.id in (".$clients.") ";
                    $query = $em->createQuery($sql);
                    $partners = $query->getResult();

                    $sql = "select COUNT(s) as sales, SUM(s.milesOriginal) as milesOriginal, SUM(s.amountPaid - s.tax - s.duTax) as values FROM Sale s ".
                        " JOIN s.client c ".
                        " where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' and '".$monthAgo->format('Y-m-d')."'  ".
                        " and (c.dealer = '".$dealer->getId()."' or c.id in (".$clients.") ) ";

                    $query = $em->createQuery($sql);
                    $sales = $query->getResult();

                    $dataset[$line][$month['name']] = array(
                        'clients' => (float)$partners[0]['clients'],
                        'sales' => (float)$sales[0]['sales'],
                        'milesOriginal' => (float)$sales[0]['milesOriginal'],
                        'values' => (float)$sales[0]['values']
                    );
                }
            }

            $line++;
        }

        foreach ($months as $month) {

            if($month['name'] == 'Representantes') {

                $dataset[$line][$month['name']] = 'Totais';
                $dataset[$line]['priority'] = 2;

            } else if($month['name'] == 'Totais') {

                $sql = "select COUNT(c) as clients FROM Businesspartner c ".
                    " where c.partnerType like '%C%' ".
                    " and c.registerDate BETWEEN '".$months[1]['name']."' and '".$months[count($months) - 2]['name']."' ";
                $query = $em->createQuery($sql);
                $partners = $query->getResult();

                $sql = "select COUNT(s) as sales, SUM(s.milesOriginal) as milesOriginal, SUM(s.amountPaid - s.tax - s.duTax) as values FROM Sale s ".
                    " JOIN s.client c ".
                    " where s.issueDate BETWEEN '".$months[1]['name']."' and '".$months[count($months) - 2]['name']."' ";

                $query = $em->createQuery($sql);
                $sales = $query->getResult();

                $dataset[$line][$month['name']] = array(
                    'clients' => (float)$partners[0]['clients'],
                    'sales' => (float)$sales[0]['sales'],
                    'milesOriginal' => (float)$sales[0]['milesOriginal'],
                    'values' => (float)$sales[0]['values']
                );

            } else {

                $monthsAgo = (new \DateTime($month['name']));
                $monthAgo = (new \DateTime($month['name']))->modify('+1 month');

                $sql = "select COUNT(c) as clients FROM Businesspartner c ".
                    " where c.partnerType like '%C%' ".
                    " and c.registerDate BETWEEN '".$monthsAgo->format('Y-m-d')."' and '".$monthAgo->format('Y-m-d')."' ";
                $query = $em->createQuery($sql);
                $partners = $query->getResult();

                $sql = "select COUNT(s) as sales, SUM(s.milesOriginal) as milesOriginal, SUM(s.amountPaid - s.tax - s.duTax) as values FROM Sale s ".
                    " JOIN s.client c ".
                    " where s.issueDate BETWEEN '".$monthsAgo->format('Y-m-d')."' and '".$monthAgo->format('Y-m-d')."'  ";

                $query = $em->createQuery($sql);
                $sales = $query->getResult();

                $dataset[$line][$month['name']] = array(
                    'clients' => (float)$partners[0]['clients'],
                    'sales' => (float)$sales[0]['sales'],
                    'milesOriginal' => (float)$sales[0]['milesOriginal'],
                    'values' => (float)$sales[0]['values']
                );
            }
        }

        function array_orderby() {
            $args = func_get_args();
            $data = array_shift($args);
            foreach ($args as $n => $field) {
                if (is_string($field)) {
                    $tmp = array();
                    foreach ($data as $key => $row)
                        $tmp[$key] = $row[$field];
                    $args[$n] = $tmp;
                    }
            }
            $args[] = &$data;
            call_user_func_array('array_multisort', $args);
            return array_pop($args);
        }
        $dataset = array_orderby($dataset, 'priority');

        $response->setDataset(array( 'data' => $dataset, 'months' => $months));
    }

    public function leaderBoarding(Request $request, Response $response) {
        $dados = $request->getRow();
        if(isset($dados['data'])){
            $dados = $dados['data'];
        }

        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();

        $where = '';
        if(isset($dados['days'])) {
            $where = " where s.issue_date >= '".(new \DateTime())->modify('-'.$dados['days'].' days')->format('Y-m-d')."' ";
        }

		$query = "SELECT SUM(s.miles_used) as milhas, SUM(s.amount_paid) as valor, d.name as dealer FROM sale s ".
            " inner join businesspartner c on c.id = s.client_id ".
            " inner join businesspartner d on d.id = c.dealer ".
            $where . " AND s.status IN ('Emitido', 'Cancelamento Solicitado', 'Cancelamento Nao Solicitado', 'Cancelamento Efetivado', 'Cancelamento Pendente') ".
            " GROUP by c.dealer order by valor DESC ";

		$stmt = $QueryBuilder->query($query);
        $dataset = array();
 		while ($row = $stmt->fetch()) {
            $dataset[] = array(
                'dealer' => $row['dealer'],
                'miles' => (float)$row['milhas'],
                'value' => (float)$row['valor']
            );
		}

        $response->setDataset($dataset);
    }
}