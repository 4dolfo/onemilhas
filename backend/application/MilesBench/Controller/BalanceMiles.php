<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class BalanceMiles {

	public function loadMilesAnalysis(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
            $hash = $dados['hashId'];
        }
		$em = Application::getInstance()->getEntityManager();

		$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
		$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
		
		$dataset = array();
		for ($i=15; $i >= 0; $i--) { 
			$daysAgo = (new \DateTime())->modify('today')->modify('-'.($i - 1).' days');
			$dayAgo = (new \DateTime())->modify('today')->modify('-'.$i.' days');
		
			$sql = "select SUM(s.milesUsed) as milesUsed FROM Sale s JOIN s.cards c where s.issueDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and ( s.status='Emitido' or s.status='Cancelamento Solicitado' ) and s.airline='1' and ( c.cardType is null or c.cardType = '') ";
			$query = $em->createQuery($sql);
			$SalesTam = $query->getResult();

			$sql = "select SUM(s.milesUsed) as milesUsed FROM Sale s JOIN s.cards c where s.issueDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and ( s.status='Emitido' or s.status='Cancelamento Solicitado' ) and s.airline='1' and ( c.cardType is not null and c.cardType <> '') ";
			$query = $em->createQuery($sql);
			$SalesRed = $query->getResult();

			$sql = "select SUM(s.milesUsed) as milesUsed FROM Sale s JOIN s.cards c where s.issueDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and ( s.status='Emitido' or s.status='Cancelamento Solicitado' ) and s.airline='2' ";
			$query = $em->createQuery($sql);
			$SalesGol = $query->getResult();

			$sql = "select SUM(s.milesUsed) as milesUsed FROM Sale s JOIN s.cards c where s.issueDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and ( s.status='Emitido' or s.status='Cancelamento Solicitado' ) and s.airline='3' and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) ";
			$query = $em->createQuery($sql);
			$SalesAzul = $query->getResult();

			$sql = "select SUM(s.milesUsed) as milesUsed FROM Sale s JOIN s.cards c where s.issueDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and ( s.status='Emitido' or s.status='Cancelamento Solicitado' ) and s.airline='3' and c.id in (212359, 3100, 5290, 9709, 11799, 12226) ";
			$query = $em->createQuery($sql);
			$SalesSrm = $query->getResult();

			$sql = "select SUM(s.milesUsed) as milesUsed FROM Sale s JOIN s.cards c where s.issueDate BETWEEN '".$dayAgo->format('Y-m-d H:i:s')."' AND '".$daysAgo->format('Y-m-d H:i:s')."' and ( s.status='Emitido' or s.status='Cancelamento Solicitado' ) and s.airline='4' ";
			$query = $em->createQuery($sql);
			$SalesAvianca = $query->getResult();

			$dataset[] = array(
				'SalesAzul' => (float)$SalesAzul[0]['milesUsed'],
				'SalesSrm' => (float)$SalesSrm[0]['milesUsed'],
				'SalesGol' => (float)$SalesGol[0]['milesUsed'],
				'SalesTam' => (float)$SalesTam[0]['milesUsed'],
				'SalesRed' => (float)$SalesRed[0]['milesUsed'],
				'SalesAvianca' => (float)$SalesAvianca[0]['milesUsed'],
				'date' => $dayAgo->format('Y-m-d')
			);
		}
		$response->setDataset($dataset);
	}

	public function loadLatamSales(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();

		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{ // Days ago option
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}

		$sql = "select COUNT(s) as srm FROM Sale s JOIN s.cards c 
				where s.issueDate BETWEEN '".$_dateFrom->format('Y-m-d')."' AND '".$_dateTo->format('Y-m-d')."'  
				and s.airline = '1' and c.cardType in ('RED', 'BLACK') ";
		$query = $em->createQuery($sql);
		$SalesSrm = $query->getResult();

		$dataset[] = array(
			'data' => (float)$SalesSrm[0]['srm'],
			'label' => 'BLACK/RED  ('.number_format((float)$SalesSrm[0]['srm'], 0, ',', '.').')'
		);

		$sql = "select COUNT(s) as sales FROM Sale s JOIN s.cards c 
				where s.issueDate BETWEEN '".$_dateFrom->format('Y-m-d')."' AND '".$_dateTo->format('Y-m-d')."' 
				and s.airline = '1' and c.cardType not in ('RED', 'BLACK') ";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset[] = array(
			'data' => (float)$Sales[0]['sales'],
			'label' => '('.number_format((float)$Sales[0]['sales'], 0, ',', '.').')'
		);
		$response->setDataset($dataset);
	}

	public function loadLatamPoints(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();

		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{ // Days ago option
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}
		
		$sql = "select SUM(s.milesUsed) as srm FROM Sale s JOIN s.cards c 
				where s.issueDate BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."'  
				and s.airline = '1' and c.cardType in ('RED', 'BLACK') ";
		$query = $em->createQuery($sql);
		$SalesSrm = $query->getResult();

		$dataset[] = array(
			'data' => (float)$SalesSrm[0]['srm'],
			'label' => 'BLACK/RED  ('.number_format((float)$SalesSrm[0]['srm'], 0, ',', '.').')'
		);

		$sql = "select SUM(s.milesUsed) as sales FROM Sale s JOIN s.cards c 
				where s.issueDate BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."'  
				and s.airline = '1' and c.cardType not in ('RED', 'BLACK') ";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		$dataset[] = array(
			'data' => (float)$Sales[0]['sales'],
			'label' => '('.number_format((float)$Sales[0]['sales'], 0, ',', '.').')'
		);
		$response->setDataset($dataset);
	}

	public function loadCancelSalesWaitingChart(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();

		$sql = "select SUM(s.milesUsed) as miles, MAX(a.name) as airline FROM Sale s JOIN s.airline a where s.status='Cancelamento Solicitado' group by s.airline ";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		foreach ($Sales as $sale) {
			$dataset[] = array(
				'data' => (float)$sale['miles'],
				'label' => $sale['airline'].' ('.number_format($sale['miles'], 0, ',', '.').')'
			);
		}

		$response->setDataset($dataset);
	}

	public function loadRefoundSalesWaitingChart(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();

		$sql = "select SUM(s.milesUsed) as miles, MAX(a.name) as airline FROM Sale s JOIN s.airline a where s.status='Reembolso Solicitado' group by s.airline ";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		foreach ($Sales as $sale) {
			$dataset[] = array(
				'data' => (float)$sale['miles'],
				'label' => $sale['airline'].' ('.number_format($sale['miles'], 0, ',', '.').')'
			);
		}

		$response->setDataset($dataset);
	}

	public function loadTotalMiles(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();

		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{ // Days ago option
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}

		$sql = "select SUM(s.milesUsed) as miles, MAX(a.name) as airline FROM Sale s JOIN s.airline a 
				where s.issueDate BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."'   
				and s.status='Emitido' group by s.airline ";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();

		foreach ($Sales as $sale) {
			$dataset[] = array(
				'data' => (float)$sale['miles'],
				'label' => $sale['airline'].' ('.number_format($sale['miles'], 0, ',', '.').')'
			);
		}

		$response->setDataset($dataset);
	}

	public function loadAverageMiles(Request $request, Response $response) {
		$dados = $request->getRow();
        if(isset($dados['data']['points'])){
            if($dados['data']['points'] == '') {
                $dados['data']['points'] = 4000;
            }
        }
        else{
            $dados['data']['points'] = 4000;
        }

		$em = Application::getInstance()->getEntityManager();
		$QueryBuilder = Application::getInstance()->getQueryBuilder();
		$Airline = $em->getRepository('Airline')->findAll();

		$dataset = array();
		foreach ($Airline as $key => $value) {

			$MilesAVGRED = 0;
			$MilesBenchRED = 0;
			$MilesAVGSRM = 0;
			$MilesBenchSRM = 0;
			$MilesAVGPROMO = 0;
			$MilesBenchPROMO = 0;
			$total_cost = 0;
			$purchase_miles = 0;
			if($value->getId() == 1 ) {
				// RED AND BLACK CARDS
				$query = " select SUM(m.leftover / m.cost_per_thousand) as totalCost FROM milesbench m INNER JOIN cards c on c.id = m.cards_id WHERE c.airline_id = " . $value->getId() . " and m.leftover > ".$dados['data']['points']." and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and c.card_type in ('RED', 'BLACK') and c.blocked = 'N' ";
				$stmt = $QueryBuilder->query($query);
				while ($row = $stmt->fetch()) {
					$MilesAVGRED = (float)$row['totalCost'];
				}

				if($MilesAVGRED != 0){
					//$sql = "select SUM(m.leftover) as leftover FROM Milesbench m JOIN m.cards c WHERE c.airline = " . $value->getId() . " and m.leftover > ".$dados['data']['points']." and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and c.cardType in ('RED', 'BLACK') and c.blocked = 'N' ";
					$query = "SELECT SUM(a.leftover) as leftover, SUM(a.purchase_miles) as purchase_miles, SUM(a.total_cost) as total_cost FROM (SELECT p.total_cost, p.purchase_miles, m.leftover FROM milesbench m inner join cards c on m.cards_id = c.id inner join purchase as p on p.cards_id = c.id where m.leftover >= ".$dados['data']['points']." and c.airline_id = " . $value->getId() . " and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and c.card_type in ('RED', 'BLACK') and c.blocked = 'N' group by m.id) a ";
					$stmt = $QueryBuilder->query($query);
					//$MilesBenchRED = (float)$query->getResult()[0]['leftover'];
					while ($row = $stmt->fetch()) {
						$MilesBenchRED = (float)$row['leftover'];
						$custo = (float)$row['total_cost'];
						$milhas = (float)$row['purchase_miles'];
						$MilesAVGRED = $custo * 1000 / $milhas;
					}
				}

				// PROMO CARDS
				$query = "SELECT AVG(p.cost_per_thousand_purchase) as costPerThousand FROM purchase p inner join cards c on c.id = p.cards_id inner join milesbench m on c.id = m.cards_id WHERE c.airline_id = " . $value->getId() . " and m.leftover > ".$dados['data']['points']." and p.leftover > 4000 and p.cost_per_thousand_purchase <> 0 and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and p.is_promo = 'true' and p.status = 'M' and c.blocked = 'N' ";
				$stmt = $QueryBuilder->query($query);
				while ($row = $stmt->fetch()) {
					$MilesAVGPROMO = $row['costPerThousand'];
				}

				// PROMO CARDS MILES BENCH
				$query = "SELECT SUM(m.leftover) as leftover FROM purchase p inner join cards c on c.id = p.cards_id inner join milesbench m on c.id = m.cards_id WHERE c.airline_id = " . $value->getId() . " and m.leftover > ".$dados['data']['points']." and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and p.is_promo = 'true' and p.status = 'M' and c.blocked = 'N' ";
				$stmt = $QueryBuilder->query($query);
				while ($row = $stmt->fetch()) {
					$MilesBenchPROMO = $row['leftover'];
				}

			} else if($value->getId() == 3 ) {

				$query = " select SUM(m.leftover / m.cost_per_thousand) as totalCost FROM milesbench m INNER JOIN cards c on c.id = m.cards_id WHERE c.airline_id = " . $value->getId() . " and m.leftover > ".$dados['data']['points']." and c.id in (212359, 3100, 5290, 9709, 11799, 12226) and c.card_type not in ('RED', 'BLACK') and c.blocked = 'N' ";
				$stmt = $QueryBuilder->query($query);
				while ($row = $stmt->fetch()) {
					$MilesAVGSRM = (float)$row['totalCost'];
				}

				if($MilesAVGSRM != 0){
					$sql = "select SUM(distinct m.leftover) as leftover FROM Milesbench m JOIN m.cards c WHERE c.airline = " . $value->getId() . " and m.leftover > ".$dados['data']['points']." and c.id in (212359, 3100, 5290, 9709, 11799, 12226) and c.cardType not in ('RED', 'BLACK') and c.blocked = 'N' ";
					$query = $em->createQuery($sql);
					$MilesBenchSRM = (float)$query->getResult()[0]['leftover'];
					$MilesAVGSRM = $MilesBenchSRM / $MilesAVGSRM;
				}
			}

			$query = " select SUM(m.leftover / m.cost_per_thousand) as totalCost FROM milesbench m INNER JOIN cards c on c.id = m.cards_id WHERE c.airline_id = " . $value->getId() . " and m.leftover > ".$dados['data']['points']." and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and c.card_type not in ('RED', 'BLACK') and c.blocked = 'N' ";
			
			$stmt = $QueryBuilder->query($query);
			while ($row = $stmt->fetch()) {
				$MilesAVG = (float)$row['totalCost'];
			}

			//$query = "SELECT SUM(distinct m.leftover) as leftover, SUM(distinct p.total_cost) as total_cost, SUM(distinct p.purchase_miles) as purchase_miles FROM milesbench m inner join cards c on c.id = m.cards_id inner join purchase p on c.id = p.cards_id WHERE c.airline_id = " . $value->getId() . " and c.blocked = 'N' and m.leftover > ".$dados['data']['points']." and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and c.card_type not in ('RED', 'BLACK') and c.blocked <> 'Y' ";
			$query = "SELECT SUM(a.leftover) as leftover, SUM(a.purchase_miles) as purchase_miles, SUM(a.total_cost) as total_cost FROM (SELECT p.total_cost, p.purchase_miles, m.leftover FROM milesbench m inner join cards c on m.cards_id = c.id inner join purchase as p on p.cards_id = c.id where m.leftover >= ".$dados['data']['points']." and c.airline_id = " . $value->getId() . " and c.id not in (212359, 3100, 5290, 9709, 11799, 12226) and (c.card_type not in ('RED', 'BLACK') or c.card_type is null) and c.blocked = 'N' group by m.id) a ";
			$stmt = $QueryBuilder->query($query);
			while ($row = $stmt->fetch()) {
				$MilesBench = (float)$row['leftover'];
				$total_cost = (float)$row['total_cost'];
				$purchase_miles = (float)$row['purchase_miles'];
			}
			
			//if((float)$MilesAVG != 0) {
			if((float)$purchase_miles != 0) {
				//$MilesAVG = $MilesBench / $MilesAVG;
				$MilesAVG = $total_cost*1000/$purchase_miles;

				$dataset[] = array(
					'name' => $value->getName(),
					'milesavg' => $MilesAVG,
					'MilesAVGRED' => $MilesAVGRED,
					'MilesBenchRED' => $MilesBenchRED,
					'MilesAVGSRM' => $MilesAVGSRM,
					'MilesBenchSRM' => $MilesBenchSRM,
					'MilesAVGPROMO' => (float)$MilesAVGPROMO,
					'MilesBenchPROMO' => (float)$MilesBenchPROMO,
					'miles' => $MilesBench,
					'total_cost' => $total_cost,
					'purchase_miles' => $purchase_miles
				);
			}
		}
		$response->setDataset($dataset);
	}

	public function saveChangeMilesAZULSRM(Request $request, Response $response) {
		$dados = $request->getRow()['data'];
		$em = Application::getInstance()->getEntityManager();

		$MilesbenchCategory = $em->getRepository('MilesbenchCategory')->find($dados['id']);
		$MilesbenchCategory->setToNegative($dados['to_negative']);

		$em->persist($MilesbenchCategory);
		$em->flush($MilesbenchCategory);

		$message = new \MilesBench\Message();
		$message->setType(\MilesBench\Message::SUCCESS);
		$message->setText('Registro salvo com sucesso');
		$response->addMessage($message);
	}

	public function loadCardsAirlinesStatus(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
		$dataset = array();

		$Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));
		foreach ($Airlines as $key => $value) {
			$airline = array( 'name' => $value->getName() );

			$sqlPriority1 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." AND m.leftover >= 4000 AND  c.is_priority = 'true' ";
			$stmt = $QueryBuilder->query($sqlPriority1);
			while ($row = $stmt->fetch()) {
				$airline['priority1'] = (float)$row['quant'];
			}

			$sqlPriority2 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." AND m.leftover >= 4000 AND  ( m.date_priority >= '". (new \DateTime())->format('Y-m-d') ."' OR m.miles_priority > 0 )";
			$stmt = $QueryBuilder->query($sqlPriority2);
			while ($row = $stmt->fetch()) {
				$airline['priority2'] = (float)$row['quant'];
			}

			$sqlPriority3 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." AND m.leftover >= 5000 AND c.is_priority = 'false' ".
				" AND DATEDIFF(m.due_date, '". (new \DateTime())->format('Y-m-d') ."') <= 20 ";
			$stmt = $QueryBuilder->query($sqlPriority3);
			while ($row = $stmt->fetch()) {
				$airline['priority3'] = (float)$row['quant'];
			}

			$sqlPriority4 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." AND m.leftover >= 4000 AND c.is_priority = 'false' ".
				" AND DATEDIFF(m.contract_due_date, '". (new \DateTime())->format('Y-m-d') ."') <= 20 ";
			$stmt = $QueryBuilder->query($sqlPriority4);
			while ($row = $stmt->fetch()) {
				$airline['priority4'] = (float)$row['quant'];
			}

			$sqlPriority5 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." AND m.leftover >= 4000 ".
				" AND ( c.notes IS NOT NULL AND c.notes <> '' ) ";
			$stmt = $QueryBuilder->query($sqlPriority5);
			while ($row = $stmt->fetch()) {
				$airline['priority5'] = (float)$row['quant'];
			}

			$dataset[] = $airline;
		}
		$response->setDataset($dataset);
	}

	public function loadCardsAirline(Request $request, Response $response) {
		$airline = $request->getRow()['airline'];
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
		$dataset = array();

		$Airline = $em->getRepository('Airline')->findOneBy(array('name' => $airline));
		$oneYear = new \DateTime();
        $oneYear->modify('-1 year');
		$startDate = '2018-08-09';

		$ArrayAirline = array();
		$ArrayAirline['priority1'] = array();
		$airline = array();
		$sqlPriority1 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
			" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $Airline->getId() ." AND m.leftover >= 4000 AND  c.is_priority = 'true' order by m.leftover ";
		$stmt = $QueryBuilder->query($sqlPriority1);
		while ($row = $stmt->fetch()) {

			if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
				$paxes = [];
				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where (f.international = 'true' or t.international = 'true') and s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$maxPerPax = 18;
				if((int)$row['max_per_pax'] != 0) {
					$maxPerPax = (int)$row['max_per_pax'];
				}
				$row['maxPerPax'] = $maxPerPax;
				$row['paxes'] = $paxes;
			}

			$row['leftover'] = (float)$row['leftover'];
			$ArrayAirline['priority1'][] = $row;
		}

		$ArrayAirline['priority2'] = array();
		$sqlPriority2 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
			" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $Airline->getId() ." AND m.leftover >= 4000 AND  ( m.date_priority >= '". (new \DateTime())->format('Y-m-d') ."' OR m.miles_priority > 0 ) order by m.leftover ";
		$stmt = $QueryBuilder->query($sqlPriority2);
		while ($row = $stmt->fetch()) {

			if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
				$paxes = [];
				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$maxPerPax = 18;
				if((int)$row['max_per_pax'] != 0) {
					$maxPerPax = (int)$row['max_per_pax'];
				}
				$row['maxPerPax'] = $maxPerPax;
				$row['paxes'] = $paxes;
			}

			$row['leftover'] = (float)$row['leftover'];
			$ArrayAirline['priority2'][] = $row;
		}

		$ArrayAirline['priority3'] = array();
		$sqlPriority3 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
			" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $Airline->getId() ." AND m.leftover >= 5000 AND c.is_priority = 'false' ".
			" AND DATEDIFF(m.due_date, '". (new \DateTime())->format('Y-m-d') ."') <= 20  order by m.leftover ";
		$stmt = $QueryBuilder->query($sqlPriority3);
		while ($row = $stmt->fetch()) {

			if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
				$paxes = [];
				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$maxPerPax = 18;
				if((int)$row['max_per_pax'] != 0) {
					$maxPerPax = (int)$row['max_per_pax'];
				}
				$row['maxPerPax'] = $maxPerPax;
				$row['paxes'] = $paxes;
			}

			$row['leftover'] = (float)$row['leftover'];
			$ArrayAirline['priority3'][] = $row;
		}

		$ArrayAirline['priority4'] = array();
		$sqlPriority4 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
			" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $Airline->getId() ." AND m.leftover >= 4000 AND c.is_priority = 'false' ".
			" AND DATEDIFF(m.contract_due_date, '". (new \DateTime())->format('Y-m-d') ."') <= 20  order by m.leftover ";
		$stmt = $QueryBuilder->query($sqlPriority4);
		while ($row = $stmt->fetch()) {

			if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
				$paxes = [];
				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$maxPerPax = 18;
				if((int)$row['max_per_pax'] != 0) {
					$maxPerPax = (int)$row['max_per_pax'];
				}
				$row['maxPerPax'] = $maxPerPax;
				$row['paxes'] = $paxes;
			}

			$row['leftover'] = (float)$row['leftover'];
			$ArrayAirline['priority4'][] = $row;
		}

		$ArrayAirline['priority5'] = array();
		$sqlPriority5 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
			" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $Airline->getId() ." AND m.leftover >= 4000 AND c.is_priority = 'false' ".
			" AND ( c.notes IS NOT NULL AND c.notes <> '' ) order by m.leftover ";
		$stmt = $QueryBuilder->query($sqlPriority5);
		while ($row = $stmt->fetch()) {

			if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
				$paxes = [];
				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
					" inner join businesspartner p on p.id = s.pax_id ".
					" inner join milesbench m on m.cards_id = s.cards_id ".
					" inner join airport f on f.id = s.airport_from ".
					" inner join airport t on t.id = s.airport_to ".
					" inner join online_pax w on w.id = s.online_pax_id ".
					" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
				$stmt2 = $QueryBuilder->query($sql);
				while ($row2 = $stmt2->fetch()) {
					$paxes[] = array(
						'pax_id' => $row2['pax_id'],
						'name' => $row2['name'],
						'registration_code' => $row2['registration_code'],
					);
				}

				$maxPerPax = 18;
				if((int)$row['max_per_pax'] != 0) {
					$maxPerPax = (int)$row['max_per_pax'];
				}
				$row['maxPerPax'] = $maxPerPax;
				$row['paxes'] = $paxes;
			}

			$row['leftover'] = (float)$row['leftover'];
			$ArrayAirline['priority5'][] = $row;
		}

		$dataset = $ArrayAirline;
		$response->setDataset($dataset);
	}

	public function loadCardsAirlinesRange(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
		$dataset = array();

		$Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));
		foreach ($Airlines as $key => $value) {

			$airline = array( 'name' => $value->getName() );

			$sqlRagens1 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
				" AND ( m.leftover >= 4000 AND m.leftover <= 9999 )";
			$stmt = $QueryBuilder->query($sqlRagens1);
			while ($row = $stmt->fetch()) {
				$airline['range1'] = (float)$row['quant'];
			}

			$sqlRagens2 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
				" AND ( m.leftover >= 10000 AND m.leftover <= 49999 )";
			$stmt = $QueryBuilder->query($sqlRagens2);
			while ($row = $stmt->fetch()) {
				$airline['range2'] = (float)$row['quant'];
			}

			$sqlRagens3 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
				" AND ( m.leftover >= 50000 AND m.leftover <= 99999 )";
			$stmt = $QueryBuilder->query($sqlRagens3);
			while ($row = $stmt->fetch()) {
				$airline['range3'] = (float)$row['quant'];
			}

			$sqlRagens4 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
				" AND ( m.leftover >= 100000 AND m.leftover <= 999999 )";
			$stmt = $QueryBuilder->query($sqlRagens4);
			while ($row = $stmt->fetch()) {
				$airline['range4'] = (float)$row['quant'];
			}

			$sqlRagens5 = "SELECT COUNT(m.id) as quant from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
				" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
				" AND m.leftover >= 1000000 ";
			$stmt = $QueryBuilder->query($sqlRagens5);
			while ($row = $stmt->fetch()) {
				$airline['range5'] = (float)$row['quant'];
			}

			$dataset[] = $airline;
		}
		$response->setDataset($dataset);
	}

	public function loadCardsAirlineByMiles(Request $request, Response $response) {
		$range = $request->getRow()['range'];
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
		$dataset = array();

		$oneYear = new \DateTime();
        $oneYear->modify('-1 year');
		$startDate = '2018-08-09';

		$Airlines = $em->getRepository('Airline')->findBy(array('salePlansStatus' => true));
		foreach ($Airlines as $key => $value) {
			$airline = array( 'name' => $value->getName() );
			
			if($range == '1') {
				$sqlRagens1 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
					" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
					" AND ( m.leftover >= 4000 AND m.leftover <= 9999 ) order by m.leftover ";
				$stmt = $QueryBuilder->query($sqlRagens1);
				while ($row = $stmt->fetch()) {

					if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
						$paxes = [];
						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}

						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}
	
						$maxPerPax = 18;
						if((int)$row['max_per_pax'] != 0) {
							$maxPerPax = (int)$row['max_per_pax'];
						}
						$row['maxPerPax'] = $maxPerPax;
						$row['paxes'] = $paxes;
					}

					$row['leftover'] = (float)$row['leftover'];
					$airline['range'][] = $row;
				}

			} else if($range == '2') {
				$sqlRagens2 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
					" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
					" AND ( m.leftover >= 10000 AND m.leftover <= 49999 ) order by m.leftover ";
				$stmt = $QueryBuilder->query($sqlRagens2);
				while ($row = $stmt->fetch()) {

					if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
						$paxes = [];
						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}

						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}
	
						$maxPerPax = 18;
						if((int)$row['max_per_pax'] != 0) {
							$maxPerPax = (int)$row['max_per_pax'];
						}
						$row['maxPerPax'] = $maxPerPax;
						$row['paxes'] = $paxes;
					}

					$row['leftover'] = (float)$row['leftover'];
					$airline['range'][] = $row;
				}

			} else if($range == '3') {
				$sqlRagens3 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
					" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
					" AND ( m.leftover >= 50000 AND m.leftover <= 99999 ) order by m.leftover ";
				$stmt = $QueryBuilder->query($sqlRagens3);
				while ($row = $stmt->fetch()) {

					if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
						$paxes = [];
						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}

						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}
	
						$maxPerPax = 18;
						if((int)$row['max_per_pax'] != 0) {
							$maxPerPax = (int)$row['max_per_pax'];
						}
						$row['maxPerPax'] = $maxPerPax;
						$row['paxes'] = $paxes;
					}

					$row['leftover'] = (float)$row['leftover'];
					$airline['range'][] = $row;
				}

			} else if($range == '4') {
				$sqlRagens4 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
					" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
					" AND ( m.leftover >= 100000 AND m.leftover <= 999999 ) order by m.leftover ";
				$stmt = $QueryBuilder->query($sqlRagens4);
				while ($row = $stmt->fetch()) {

					if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
						$paxes = [];
						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}

						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}
	
						$maxPerPax = 18;
						if((int)$row['max_per_pax'] != 0) {
							$maxPerPax = (int)$row['max_per_pax'];
						}
						$row['maxPerPax'] = $maxPerPax;
						$row['paxes'] = $paxes;
					}

					$row['leftover'] = (float)$row['leftover'];
					$airline['range'][] = $row;
				}

			} else if($range == '5') {
				$sqlRagens5 = "SELECT b.name, b.registration_code, c.card_type, m.due_date, m.contract_due_date, m.leftover, c.max_per_pax, c.id as cards_id, c.airline_id from milesbench m INNER JOIN cards c on c.id = m.cards_id INNER JOIN businesspartner b on b.id = c.businesspartner_id ".
					" WHERE c.blocked = 'N' and b.status = 'Aprovado' and c.airline_id = ". $value->getId() ." ".
					" AND m.leftover >= 1000000 order by m.leftover ";
				$stmt = $QueryBuilder->query($sqlRagens5);
				while ($row = $stmt->fetch()) {

					if($row['airline_id'] == 1 || $row['airline_id'] == 3) {
						$paxes = [];
						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where w.is_newborn = 'N' and f.international = 'false' and t.international = 'false' and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by SUBSTRING_INDEX(p.name, ' ', 1), SUBSTRING_INDEX(p.name, ' ', -1) ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}

						$sql = " select s.cards_id, p.name, p.registration_code, p.id as pax_id from sale s ".
							" inner join businesspartner p on p.id = s.pax_id ".
							" inner join milesbench m on m.cards_id = s.cards_id ".
							" inner join airport f on f.id = s.airport_from ".
							" inner join airport t on t.id = s.airport_to ".
							" inner join online_pax w on w.id = s.online_pax_id ".
							" where (f.international = 'true' or t.international = 'true') and  s.airline_id = ".$row['airline_id']." and s.is_extra <> 'true' and s.issue_date >= '" . $startDate . "' and s.issue_date >= '". $oneYear->format('Y-m-d') ."' and s.cards_id = ". $row['cards_id'] ." group by p.name ";
						$stmt2 = $QueryBuilder->query($sql);
						while ($row2 = $stmt2->fetch()) {
							$paxes[] = array(
								'pax_id' => $row2['pax_id'],
								'name' => $row2['name'],
								'registration_code' => $row2['registration_code'],
							);
						}
	
						$maxPerPax = 18;
						if((int)$row['max_per_pax'] != 0) {
							$maxPerPax = (int)$row['max_per_pax'];
						}
						$row['maxPerPax'] = $maxPerPax;
						$row['paxes'] = $paxes;
					}

					$row['leftover'] = (float)$row['leftover'];
					$airline['range'][] = $row;
				}
			}

			$dataset[] = $airline;
		}
		$response->setDataset($dataset);
	}

	public function loadSalesAzulSRMLastMonth(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
		$dataset = array();

		$month_date = (new \DateTime())->modify('first day of last month')->format('Y-m-d');
		$month_date_end = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
		$sqlCategory = " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', " .
		" 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";
		$sql = "select s.*, f.code as airport_from, t.code as airport_to, DATEDIFF(s.boarding_date, s.issue_date) as date_diff FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
		" s.cards_id IN (212359) and s.issue_date >= '" . $month_date . "' and s.issue_date < '" . $month_date_end . "' " . $sqlCategory;
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {

			$row['category'] = 'Competitive';
			$row['controle'] = 'MMS';
			$FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
				array( 'flightFrom' => $row['airport_from'], 'flightTo' => $row['airport_to'] )
			);
			if($FlightPathCategory) {
				$row['category'] = $FlightPathCategory->getFlightCategory()->getName();
			}
			$dataset[] = $row;
		}

		$month_date = (new \DateTime())->modify('first day of last month')->format('Y-m-d');
		$month_date_end = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
		$sqlCategory = " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', " .
		" 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";
		$sql = "select s.*, f.code as airport_from, t.code as airport_to, DATEDIFF(s.boarding_date, s.issue_date) as date_diff FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
		" s.cards_id IN (3100, 5290, 9709, 11799, 12226) and s.issue_date >= '" . $month_date . "' and s.issue_date < '" . $month_date_end . "' " . $sqlCategory;
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {

			$row['category'] = 'Competitive';
			$row['controle'] = 'SRM';
			$FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
				array( 'flightFrom' => $row['airport_from'], 'flightTo' => $row['airport_to'] )
			);
			if($FlightPathCategory) {
				$row['category'] = $FlightPathCategory->getFlightCategory()->getName();
			}
			$dataset[] = $row;
		}
		$response->setDataset($dataset);
	}

	public function loadbluePoints(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
		$QueryBuilder = Application::getInstance()->getQueryBuilder();

		$dataset = array();

		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{ // Days ago option
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}

		$sql = "SELECT SUM(s.miles_used) as srm FROM sale s 
				INNER JOIN airport f on f.id = s.airport_from
				INNER JOIN airport t on t.id = s.airport_to 
				where s.issue_date BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."' 
				and s.airline_id = '3' and s.cards_id NOT in (212359, 3100, 5290, 9709, 11799, 12226) ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$dataset[] = array(
				'data' => (float)$row['srm'],
				'label' => 'Normal'
			);
		}

		$sql = "SELECT SUM(s.miles_used) as srm FROM sale s 
				INNER JOIN airport f on f.id = s.airport_from
				INNER JOIN airport t on t.id = s.airport_to 
				where s.issue_date BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."' 
				and s.airline_id = '3' and s.cards_id in (212359, 3100, 5290, 9709, 11799, 12226) ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$dataset[] = array(
				'data' => (float)$row['srm'],
				'label' => 'SRM'
			);
		}
		$response->setDataset($dataset);
	}

	public function loadbluePointsCompetitive(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
		$QueryBuilder = Application::getInstance()->getQueryBuilder();

		$dataset = array();

		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{ // Days ago option
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}

		$sql = "SELECT SUM(s.miles_used) as srm FROM sale s 
				INNER JOIN airport f on f.id = s.airport_from
				INNER JOIN airport t on t.id = s.airport_to 
				where s.issue_date BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."' 
				and s.airline_id = '3' and s.cards_id in (212359, 3100, 5290, 9709, 11799, 12226) 
				AND ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )
				AND DATEDIFF(boarding_date, issue_date) > 21 ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$dataset[] = array(
				'data' => (float)$row['srm'],
				'label' => '+ 21'
			);
		}

		$sql = "SELECT SUM(s.miles_used) as srm FROM sale s 
				INNER JOIN airport f on f.id = s.airport_from
				INNER JOIN airport t on t.id = s.airport_to 
				where s.issue_date BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."' 
				and s.airline_id = '3' and s.cards_id in (212359, 3100, 5290, 9709, 11799, 12226) 
				AND ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )
				AND DATEDIFF(boarding_date, issue_date) <= 21 ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$dataset[] = array(
				'data' => (float)$row['srm'],
				'label' => '<= 21'
			);
		}
		$response->setDataset($dataset);
	}

	public function loadbluePointsMonopoly(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
		$QueryBuilder = Application::getInstance()->getQueryBuilder();

		$dataset = array();

		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{ // Days ago option
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}

		$sql = "SELECT SUM(s.miles_used) as srm FROM sale s 
				INNER JOIN airport f on f.id = s.airport_from
				INNER JOIN airport t on t.id = s.airport_to 
				where s.issue_date BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."' 
				and s.airline_id = '3' and s.cards_id in (212359, 3100, 5290, 9709, 11799, 12226) 
				AND ( CONCAT(f.code, t.code) IN ( select CONCAT(flight_from, flight_to) from flight_path_category ) )
				AND DATEDIFF(boarding_date, issue_date) > 21 ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$dataset[] = array(
				'data' => (float)$row['srm'],
				'label' => '+ 21'
			);
		}

		$sql = "SELECT SUM(s.miles_used) as srm FROM sale s 
				INNER JOIN airport f on f.id = s.airport_from
				INNER JOIN airport t on t.id = s.airport_to 
				where s.issue_date BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."' 
				and s.airline_id = '3' and s.cards_id in (212359, 3100, 5290, 9709, 11799, 12226) 
				AND ( CONCAT(f.code, t.code) IN ( select CONCAT(flight_from, flight_to) from flight_path_category ) )
				AND DATEDIFF(boarding_date, issue_date) <= 21 ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$dataset[] = array(
				'data' => (float)$row['srm'],
				'label' => '<= 21'
			);
		}
		$response->setDataset($dataset);
	}

	public function loadAZULMMSUsage(Request $request, Response $response) {
		$Miles = new Miles();
		$Miles->updateAzulSRMAll(new \MilesBench\Request\Request(), new \MilesBench\Request\Response());

		$QueryBuilder = Application::getInstance()->getQueryBuilder();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();
		$AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findAll();
		foreach ($AzulFlightCategory as $category) {
			$MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
				array('flightCategory' => $category->getId(), 'control' => 'MMS')
			);

			$month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
			$sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to " .
				" where s.cards_id IN (212359) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) > 21 ";

			$FlightPathCategory = $em->getRepository('FlightPathCategory')->findBy(array( 'flightCategory' => $category->getId() ));
			if( count($FlightPathCategory) > 0 ) {
				$sql .= " and ( CONCAT(f.code, t.code) in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
			} else {
				$sql .= " and ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
			}
			$sql .= " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";

			$stmt = $QueryBuilder->query($sql);
			while ($row = $stmt->fetch()) {
				$milesUsed21Days = (float)$row['milesUsed'];
			}

			$dataset[$category->getName()] = array();
			$dataset[$category->getName()]['miles_21_days'] = $milesUsed21Days;
			$dataset[$category->getName()]['options'] = array();
			foreach ($MilesbenchCategory as $key => $value) {
				$dataset[$category->getName()]['options'][] = array(
					'id' => $value->getId(),
					'percentage' => (float)$value->getPercentage(),
					'days' => (int)$value->getDays(),
					'toFree' => (float)$value->getToFree(),
					'used' => (float)$value->getUsed(),
					'to_negative' => (float)$value->getToNegative()
				);
			}
		}

		$response->setDataset($dataset);
	}

	public function loadAZULSRMUsage(Request $request, Response $response) {
		$Miles = new Miles();
		$Miles->updateAzulSRMAll(new \MilesBench\Request\Request(), new \MilesBench\Request\Response());

		$QueryBuilder = Application::getInstance()->getQueryBuilder();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();
		$AzulFlightCategory = $em->getRepository('AzulFlightCategory')->findAll();
		foreach ($AzulFlightCategory as $category) {
			$MilesbenchCategory = $em->getRepository('MilesbenchCategory')->findBy(
				array('flightCategory' => $category->getId(), 'control' => 'SRM')
			);

			$month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
			$sql = "select SUM(s.miles_used) as milesUsed FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to " .
				" where s.cards_id IN (3100, 5290, 9709, 11799, 12226) and s.issue_date >= '" . $month_date . "' and DATEDIFF(boarding_date, issue_date) > 21 ";

			$FlightPathCategory = $em->getRepository('FlightPathCategory')->findBy(array( 'flightCategory' => $category->getId() ));
			if( count($FlightPathCategory) > 0 ) {
				$sql .= " and ( CONCAT(f.code, t.code) in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
			} else {
				$sql .= " and ( CONCAT(f.code, t.code) NOT in ( select CONCAT(flight_from, flight_to) from flight_path_category ) )";
			}
			$sql .= " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";

			$stmt = $QueryBuilder->query($sql);
			while ($row = $stmt->fetch()) {
				$milesUsed21Days = (float)$row['milesUsed'];
			}

			$dataset[$category->getName()] = array();
			$dataset[$category->getName()]['miles_21_days'] = $milesUsed21Days;
			$dataset[$category->getName()]['options'] = array();
			foreach ($MilesbenchCategory as $key => $value) {
				$dataset[$category->getName()]['options'][] = array(
					'id' => $value->getId(),
					'percentage' => (float)$value->getPercentage(),
					'days' => (int)$value->getDays(),
					'toFree' => (float)$value->getToFree(),
					'used' => (float)$value->getUsed(),
					'to_negative' => (float)$value->getToNegative()
				);
			}
		}

		$response->setDataset($dataset);
	}

	public function loadAZULSRMInUse(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();
		$CardSRM = $em->getRepository('Cards')->findOneBy( array( 'id' => 212359 ) );
		if($CardSRM && $CardSRM->getUserSession() != NULL) {
			$sql = "select o FROM OnlineOrder o where o.userSession like '" . $CardSRM->getUserSession() . "' ";
			$query = $em->createQuery($sql);
			$Orders = $query->getResult();

			foreach ($Orders as $key => $value) {
				$OnlineFlight = $em->getRepository('OnlineFlight')->findOneBy( array( 'order' => $value->getId() ) );
				foreach ($OnlineFlight as $flight) {
					
					if($flight->getAirline() == 'AZUL') {
						$dataset[] = array(
							'boardingDate' => $flight->getBoardingDate()->format('Y-m-d H:i:s'),
							'milesPerAdult' => (float)$flight->getMilesPerAdult(),
							'milesPerChild' => (float)$flight->getMilesPerChild(),
							'milesPerNewborn' => (float)$flight->getMilesPerNewborn(),
							'orderId' => $value->getId()
						);
					}
				}
			}
		}
		
		$response->setDataset($dataset);
	}

	public function loadSalesAzulSRM(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
		$dataset = array();

		$month_date = (new \DateTime())->modify('first day of this month')->format('Y-m-d');
		$sqlCategory = " and s.status NOT in ('Cancelamento Efetivado', 'Cancelamento Pendente', " .
            " 'Reembolso No-show Confirmado', 'Reembolso CIA', 'Reembolso Confirmado' ) ";
		$sql = "select s.*, f.code as airport_from, t.code as airport_to, DATEDIFF(s.boarding_date, s.issue_date) as date_diff FROM sale s INNER JOIN airport f on f.id = s.airport_from INNER JOIN airport t on t.id = s.airport_to where " .
			" s.cards_id IN (212359, 3100, 5290, 9709, 11799, 12226) and s.issue_date >= '" . $month_date . "' " . $sqlCategory;
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {

			$row['category'] = 'Competitive';
			$FlightPathCategory = $em->getRepository('FlightPathCategory')->findOneBy( 
				array( 'flightFrom' => $row['airport_from'], 'flightTo' => $row['airport_to'] )
			);
			if($FlightPathCategory) {
				$row['category'] = $FlightPathCategory->getFlightCategory()->getName();
			}
			$dataset[] = $row;
		}
		$response->setDataset($dataset);
	}

	public function loadBlueSalesSRM(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
		$dataset = array();
		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}
		$sql = "select COUNT(s) as srm FROM Sale s 
				where s.issueDate BETWEEN '".$_dateFrom->format('Y-m-d')."' AND '".$_dateTo->format('Y-m-d')."' 
				and s.airline = '3' and s.cards in (212359, 3100, 5290, 9709, 11799, 12226) ";
		$query = $em->createQuery($sql);
		$SalesSrm = $query->getResult();
		$dataset[] = array(
			'data' => (float)$SalesSrm[0]['srm'],
			'label' => 'SRM  ('.number_format((float)$SalesSrm[0]['srm'], 0, ',', '.').')'
		);
 
		$sql = "select COUNT(s) as sales FROM Sale s 
				where s.issueDate BETWEEN '".$_dateFrom->format('Y-m-d')."' AND '".$_dateTo->format('Y-m-d')."' 
				and s.airline = '3' and s.cards not in (212359, 3100, 5290, 9709, 11799, 12226) ";
		$query = $em->createQuery($sql);
		$Sales = $query->getResult();
		$dataset[] = array(
			'data' => (float)$Sales[0]['sales'],
			'label' => 'Branco  ('.number_format((float)$Sales[0]['sales'], 0, ',', '.').')'
		);
		$response->setDataset($dataset);
	}

	public function loadbluePointsSRM(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();
		$QueryBuilder = Application::getInstance()->getQueryBuilder();
		$dataset = array();
		if(($dados['searchType'] == 'true') && (isset($dados['data']['_dateFrom']) && isset($dados['data']['_dateTo']))){
			$_dateFrom = (new \DateTime($dados['data']['_dateFrom']));
			$_dateTo = (new \DateTime($dados['data']['_dateTo']));
		}
		else{ // Days ago option
			$_dateFrom = (new \DateTime())->modify('today')->modify('-'.$dados['data']['days'].' day');
			$_dateTo = (new \DateTime())->modify('today')->modify('+1 day');;
		}
		$sql = "select SUM(s.miles_used) as srm FROM sale s where s.issue_date 
				BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."'  
				and s.airline_id = '3' and s.cards_id not in (212359, 3100, 5290, 9709, 11799, 12226) ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$SalesSrm = (float)$row['srm'];
		}
		$dataset[] = array(
			'data' => $SalesSrm,
			'label' => 'Branco  ('.number_format($SalesSrm, 0, ',', '.').')'
		);
		$sql = "select SUM(s.miles_used) as sales FROM sale s where s.issue_date 
				BETWEEN '".$_dateFrom->format('Y-m-d H:i:s')."' AND '".$_dateTo->format('Y-m-d H:i:s')."'  
				and s.airline_id = '3' and s.cards_id in (212359, 3100, 5290, 9709, 11799, 12226) ";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$Sales = (float)$row['sales'];
		}
		$dataset[] = array(
			'data' => $Sales,
			'label' => 'SRM  ('.number_format($Sales, 0, ',', '.').')'
		);
		$response->setDataset($dataset);
	}
}