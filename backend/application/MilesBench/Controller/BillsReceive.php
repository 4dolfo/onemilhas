<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class BillsReceive {

	public function load(Request $request, Response $response) {
		$dados = $request->getRow();
		$content_post = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$QueryBuilder = Application::getInstance()->getQueryBuilder();

		$sql = "select b.*, c.name as client_name, c.email as client_email, c.company_name, c.adress as client_adress, c.phone_number as client_phone_number, c.registration_code, ".
			" c.payment_type, c.mulct, c.interest, c.origin, c.finnancial_email as client_financial_email, c.status as client_status ".
			" FROM billetreceive b INNER JOIN businesspartner c on c.id = b.client_id ";

		$whereClause = ' WHERE ';
		$and = '';
		$dataset = array();

		// if (isset($dados['description']) && !($dados['description'] == '')) {
		// 	$whereClause = $whereClause. "b.description like '%".$dados['description']."%'";
		// 	$and = ' AND ';
		// };

		if (isset($dados['client']) && !($dados['client'] == '')) {
			$whereClause = $whereClause.$and. " c.name = '".$dados['client']."' ";
			$and = ' AND ';
		};

		if (isset($dados['status']) && $dados['status'] != '') {
			$status = 'E';
			if ($dados['status'] == 'Baixada') {
				$status = 'B';
			}

			$whereClause = $whereClause.$and. "b.status = '".$status."' ";
			$and = ' AND ';
		};

		if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
			$_dueDateTo = $dados['_dueDateFrom'];
			if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
				$_dueDateTo = $dados['_dueDateTo'];
			}
			$whereClause = $whereClause.$and. "b.due_date BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
			$and = ' AND ';
		};

		if (isset($dados['_issueDateFrom']) && !($dados['_issueDateFrom'] == '')) {
			$_issueDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_issueDateTo']) && !($dados['_issueDateTo'] == '')) {
				$_issueDateTo = new \Datetime($dados['_issueDateTo']);
			}
			$whereClause = $whereClause.$and. "b.issue_date BETWEEN '".$dados['_issueDateFrom']."' AND '".$_issueDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		if (isset($dados['_paymentDateFrom']) && !($dados['_paymentDateFrom'] == '')) {
			$_paymentDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_paymentDateTo']) && !($dados['_paymentDateTo'] == '')) {
				$_paymentDateTo = new \Datetime($dados['_paymentDateTo']);
			}
			$whereClause = $whereClause.$and. "b.payment_date BETWEEN '".$dados['_paymentDateFrom']."' AND '".$_paymentDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		if(isset($dados['type']) && $dados['type'] == 'advising') {
			$sqlDivision = " select DISTINCT(d.billet) as billet_id from BilletsDivision d ";
			$query = $em->createQuery($sqlDivision);
			$BilletsIds = $query->getResult();

			$found = "";
			$a = "";

			foreach ($BilletsIds as $billet) {
				$found = $found.$a.$billet['billet_id'];
				$a = ', ';
			}

			$sqlDivision = " select d from BilletsDivision d JOIN d.billet b JOIN b.client c where d.paid = 'false' and d.dueDate = '".(new \DateTime())->format('Y-m-d')."' and c.name = '".$dados['client']."' ";
			$query = $em->createQuery($sqlDivision);
			$BilletsDivision = $query->getResult();

			foreach($BilletsDivision as $division){
				$dataset[] = array(
					'client' => $dados['client'],
					'due_date' => $division->getDueDate()->format('Y-m-d'),
					'actual_value' => (float)$division->getActualValue(),
					'docNumber' => $division->getName(),
					'ourNumber' => $division->getName(),
					'alreadyPaid' => 0
				);
			}

			$whereClause = $whereClause.$and. " b.id not in ( ".$found." ) ";
			$and = ' AND ';
		}

		if (isset($dados['refund']) && ($dados['refund'] == 'true')) {
			$whereClause = $whereClause.$and. " b.actual_value > 0 ";
			$and = ' AND ';
		};

		if (isset($dados['noDebit']) && ($dados['noDebit'] == 'true')) {

			$sqlAux = " select DISTINCT(d.billet) as billet_id from Billsreceive d ";
			$query = $em->createQuery($sqlAux);
			$BilletsIds = $query->getResult();

			$found = "";
			$andAux = "";

			foreach ($BilletsIds as $billet) {
				if($billet['billet_id'] != NULL) {
					$found = $found.$andAux.$billet['billet_id'];
					$andAux = ', ';
				}
			}

			$whereClause = $whereClause.$and." b.id NOT in (".$found.") ";
			$and = ' AND ';
		};

		if(isset($dados['bank']) && $dados['bank'] != '') {
			if(!isset($dados['client'])) {
				$whereClause = $whereClause.$and. " b.bank = '".$dados['bank']."' ";
				$and = ' AND ';
			}
		}
		
		if(isset($content_post['searchKeywords']) && $content_post['searchKeywords'] != '') {
			$whereClause .= $and." ( "
				." b.our_number like '%".$content_post['searchKeywords']."%' or "
				." c.name like '%".$content_post['searchKeywords']."%' ) ";

			$and = ' AND ';
		}

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};
 
        $orderBy = ' order by b.id ASC ';
        if(isset($content_post['order']) && $content_post['order'] != '') {
			if($content_post['order'] == 'due_date') {
				$orderBy = ' order by b.due_date ASC ';
			} else {
				$orderBy = ' order by b.' . $content_post['order'] . ' ASC ';
			}
        }
        if(isset($content_post['orderDown']) && $content_post['orderDown'] != '') {
			if($content_post['orderDown'] == 'due_date') {
				$orderBy = ' order by b.due_date DESC ';
			} else {
				$orderBy = ' order by b.' . $content_post['orderDown'] . ' DESC ';
			}
        }
        $sql = $sql.$orderBy;

        if(isset($content_post['page']) && isset($content_post['numPerPage'])) {
			$sql .= " limit " . ( ($content_post['page'] - 1) * $content_post['numPerPage'] ) . ", " . $content_post['numPerPage'];
            $stmt = $QueryBuilder->query($sql);
            // $query = $em->createQuery($sql)
            //     ->setFirstResult((($content_post['page'] - 1) * $content_post['numPerPage']))
            //     ->setMaxResults($content_post['numPerPage']);
        } else {
			$stmt = $QueryBuilder->query($sql);
            // $query = $em->createQuery($sql);
        }

		$billetReceiveArray = array();
		// $billetReceives = $query->getResult();
		// foreach($billetReceives as $billetReceive){
		while ($row = $stmt->fetch()) {
			$billetReceiveArray[] = array(
				'id' => $row['id'],
				'status' => $row['status'],
				'client' => $row['client_name'],
				'client_id' => $row['client_id'],
				'client_status' => $row['client_status'],
				'email' => $row['client_email'],
				'financial_email' => $row['client_financial_email'],
				'company_name' => $row['company_name'],
				'adress' => $row['client_adress'],
				'phoneNumber' => $row['client_phone_number'],
				'registrationCode' => $row['registration_code'],
				'paymentType' => $row['payment_type'],
				'mulct' => $row['mulct'],
				'interest' => $row['interest'],
				'origin' => $row['origin'],
				'description' => $row['description'],
				'due_date' => $row['due_date'],
				'issue_date' => $row['issue_date'],
				'actual_value' => (float)$row['actual_value'],
				'original_value' => (float)$row['original_value'],
				'tax' => (float)$row['tax'],
				'discount' => (float)$row['discount'],
				'docNumber' => $row['our_number'],
				'ourNumber' => $row['doc_number'],
				'payment_date' => $row['payment_date'],
				'alreadyPaid' => (float)$row['already_paid'],
				'checkinState' => ($row['checkin_state'] == 'true'),
				'bank' => $row['bank'],
				'hasBillet' => ($row['has_billet'] == 'true'),
				'usedCommission' => ($row['used_commission'] == 'true'),
			);
		}

		$whereClause = ' WHERE ';
		$and = '';

		if (isset($dados['client']) && !($dados['client'] == '')) {
			$whereClause = $whereClause.$and. " c.name = '".$dados['client']."' ";
			$and = ' AND ';
		};

		if (isset($dados['status']) && $dados['status'] != '') {
			$status = 'E';
			if ($dados['status'] == 'Baixada') {
				$status = 'B';
			}

			$whereClause = $whereClause.$and. "b.status = '".$status."' ";
			$and = ' AND ';
		};

		if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
			$_dueDateTo = $dados['_dueDateFrom'];
			if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
				$_dueDateTo = $dados['_dueDateTo'];
			}
			$whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
			$and = ' AND ';
		};

		if (isset($dados['_issueDateFrom']) && !($dados['_issueDateFrom'] == '')) {
			$_issueDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_issueDateTo']) && !($dados['_issueDateTo'] == '')) {
				$_issueDateTo = new \Datetime($dados['_issueDateTo']);
			}
			$whereClause = $whereClause.$and. "b.issueDate BETWEEN '".$dados['_issueDateFrom']."' AND '".$_issueDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		if (isset($dados['_paymentDateFrom']) && !($dados['_paymentDateFrom'] == '')) {
			$_paymentDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_paymentDateTo']) && !($dados['_paymentDateTo'] == '')) {
				$_paymentDateTo = new \Datetime($dados['_paymentDateTo']);
			}
			$whereClause = $whereClause.$and. "b.paymentDate BETWEEN '".$dados['_paymentDateFrom']."' AND '".$_paymentDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		if(isset($dados['type']) && $dados['type'] == 'advising') {
			$sqlDivision = " select DISTINCT(d.billet) as billet_id from BilletsDivision d ";
			$query = $em->createQuery($sqlDivision);
			$BilletsIds = $query->getResult();

			$found = "";
			$a = "";

			foreach ($BilletsIds as $billet) {
				$found = $found.$a.$billet['billet_id'];
				$a = ', ';
			}

			$sqlDivision = " select d from BilletsDivision d JOIN d.billet b JOIN b.client c where d.paid = 'false' and d.dueDate = '".(new \DateTime())->format('Y-m-d')."' and c.name = '".$dados['client']."' ";
			$query = $em->createQuery($sqlDivision);
			$BilletsDivision = $query->getResult();

			foreach($BilletsDivision as $division){
				$dataset[] = array(
					'client' => $dados['client'],
					'due_date' => $division->getDueDate()->format('Y-m-d'),
					'actual_value' => (float)$division->getActualValue(),
					'docNumber' => $division->getName(),
					'ourNumber' => $division->getName(),
					'alreadyPaid' => 0
				);
			}

			$whereClause = $whereClause.$and. " b.id not in ( ".$found." ) ";
			$and = ' AND ';
		}

		if (isset($dados['refund']) && ($dados['refund'] == 'true')) {
			$whereClause = $whereClause.$and. " b.actualValue > 0 ";
			$and = ' AND ';
		};

		if (isset($dados['noDebit']) && ($dados['noDebit'] == 'true')) {

			$sqlAux = " select DISTINCT(d.billet) as billet_id from Billsreceive d ";
			$query = $em->createQuery($sqlAux);
			$BilletsIds = $query->getResult();

			$found = "";
			$andAux = "";

			foreach ($BilletsIds as $billet) {
				if($billet['billet_id'] != NULL) {
					$found = $found.$andAux.$billet['billet_id'];
					$andAux = ', ';
				}
			}

			$whereClause = $whereClause.$and." b.id NOT in (".$found.") ";
			$and = ' AND ';
		};

		if(isset($dados['bank']) && $dados['bank'] != '') {
			if(!isset($dados['client'])) {
				$whereClause = $whereClause.$and. " b.bank = '".$dados['bank']."' ";
				$and = ' AND ';
			}
		}
		
		if(isset($content_post['searchKeywords']) && $content_post['searchKeywords'] != '') {
			$whereClause .= $and." ( "
				." b.ourNumber like '%".$content_post['searchKeywords']."%' or "
				." c.name like '%".$content_post['searchKeywords']."%' ) ";

			$and = ' AND ';
		}

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};

		$sql = "select COUNT(b) as quant, SUM(b.actualValue - b.alreadyPaid) as totalValueNotPaid FROM Billetreceive b JOIN b.client c ";
		if (!($whereClause == ' WHERE ')) {
			$sql = $sql.$whereClause;
		};

        $query = $em->createQuery($sql);
		$Quant = $query->getResult();

		$sql = "select SUM(b.actualValue) as totalValueNotPaid FROM Billetreceive b JOIN b.client c ";
		if (!($whereClause == ' WHERE ')) {
			$sql = $sql.$whereClause;
			$sql .= " AND (b.actualValue = b.alreadyPaid) ";
		};

        $query = $em->createQuery($sql);
        $totalValueNotPaid2 = $query->getResult();

        $dataset = array(
            'billetreceive' => $billetReceiveArray,
			'total' => $Quant[0]['quant'],
			'totalValueNotPaid' => $Quant[0]['totalValueNotPaid'] + $totalValueNotPaid2[0]['totalValueNotPaid']
        );

		$response->setDataset($dataset);
	}

	public function loadSumOpened(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();

		$sql = "select sum(b.actualValue) as actualValue FROM Billsreceive b ";

		$whereClause = "  where b.status = 'A' and b.actualValue > 0 and b.accountType = 'Venda Bilhete' ";

		$query = $em->createQuery($sql.$whereClause);
		$billsReceives = $query->getResult();

		$dataset = $billsReceives[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadSumBilletsLosses(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();

		$sql = "select sum(b.actualValue - b.alreadyPaid) as actualValue FROM Billetreceive b ";
		$whereClause = "  where b.status = 'L' and b.actualValue > 0 ";
		$query = $em->createQuery($sql.$whereClause);
		$Billetreceive = $query->getResult();

		$sql = "select sum(b.actualValue) as actualValue FROM BilletsDivision b JOIN b.billet x ";
		$whereClause = "  where b.paid = 'L' and x.actualValue > 0 and x.status in ('A', 'L') ";
		$query = $em->createQuery($sql.$whereClause);
		$BilletsDivision = $query->getResult();

		$dataset = $Billetreceive[0]['actualValue'] + $BilletsDivision[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadSumBilletsHasBillets(Request $request, Response $response) {
		$dados = $request->getRow();
		$days = 0;
		if (isset($dados['days'])) {
			$days = $dados['days'];
		}
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}
        $daysAgo = (new \DateTime())->modify('-'.$days.' day');
        $_dateTo = (new \DateTime())->modify('+1 day');

		$em = Application::getInstance()->getEntityManager();

		$sql = " select DISTINCT(d.billet) as billet_id from Billsreceive d ";
		$query = $em->createQuery($sql);
		$BilletsIds = $query->getResult();

		$found = "";
		$and = "";

		foreach ($BilletsIds as $billet) {
			if($billet['billet_id'] != NULL) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}
		}

		$sql = "select sum(b.actualValue - b.alreadyPaid) actualValue FROM Billetreceive b LEFT JOIN b.client c";
		$whereClause = "  where b.status <> 'B' and b.id in (".$found.") ";
		$and = ' AND ';

		$whereClause = $whereClause.$and." b.actualValue > 0 ";
		if($days > 0)
			$whereClause = $whereClause.$and." b.dueDate >= '".$daysAgo->format('Y-m-d')."' and b.dueDate <= '".$_dateTo->format('Y-m-d')."'  ";

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};


		$query = $em->createQuery($sql);
		$billsReceives = $query->getResult();

		$dataset = $billsReceives[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadSumBilletsToDue(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$sql = " select DISTINCT(d.billet) as billet_id from Billsreceive d ";
		$query = $em->createQuery($sql);
		$BilletsIds = $query->getResult();

		$found = "";
		$and = "";

		foreach ($BilletsIds as $billet) {
			if($billet['billet_id'] != NULL) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}
		}

		$sql = "select sum(b.actualValue - b.alreadyPaid) actualValue FROM Billetreceive b LEFT JOIN b.client c";
		$whereClause = "  where b.status <> 'B' and b.id in (".$found.") ";
		$and = ' AND ';

		$whereClause = $whereClause.$and." b.actualValue > 0 and b.dueDate >= '".(new \DateTime())->format('Y-m-d')."' ";

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};

		$query = $em->createQuery($sql);
		$billsReceives = $query->getResult();

		$dataset = $billsReceives[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadSumBilletsPast(Request $request, Response $response) {
		$dados = $request->getRow();
		$days = 0;
		if (isset($dados['days'])) {
			$days = $dados['days'];
		}
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}
        $daysAgo = (new \DateTime())->modify('-'.$days.' day');
		$_dateTo = (new \DateTime())->modify('+1 day');
	

		$em = Application::getInstance()->getEntityManager();
		$sql = " select DISTINCT(d.billet) as billet_id from Billsreceive d ";
		$query = $em->createQuery($sql);
		$BilletsIds = $query->getResult();

		$found = "";
		$and = "";

		foreach ($BilletsIds as $billet) {
			if($billet['billet_id'] != NULL) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}
		}

		$sql = "select sum(b.actualValue - b.alreadyPaid) actualValue FROM Billetreceive b ";
		$whereClause = "  where b.status <> 'B' and b.id in (".$found.") ";
		$and = ' AND ';

		$whereClause = $whereClause.$and." b.actualValue > 0 ";
		if($days > 0)
			$whereClause = $whereClause.$and." b.dueDate >= '".$daysAgo->format('Y-m-d')."' and b.dueDate <= '".$_dateTo->format('Y-m-d')."'  ";

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};

		$query = $em->createQuery($sql);
		$billsReceives = $query->getResult();

		$dataset = $billsReceives[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadSumBilletsDontHasBillets(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();

		$sql = " select DISTINCT(d.billet) as billet_id from Billsreceive d ";
		$query = $em->createQuery($sql);
		$BilletsIds = $query->getResult();

		$found = "";
		$and = "";

		foreach ($BilletsIds as $billet) {
			if($billet['billet_id'] != NULL) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}
		}

		$sql = "select sum(b.actualValue - b.alreadyPaid) actualValue FROM Billetreceive b LEFT JOIN b.client c";
		$whereClause = "  where b.status <> 'B' and b.id NOT in (".$found.") ";
		$and = ' AND ';

		$whereClause = $whereClause.$and." b.actualValue > 0 ";

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};

		$query = $em->createQuery($sql);
		$billsReceives = $query->getResult();

		$dataset = $billsReceives[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadSumBillet(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$sql = "select sum(b.actualValue - b.alreadyPaid) actualValue FROM Billetreceive b LEFT JOIN b.client c";

		$whereClause = "  where b.status = 'E' ";
		$and = ' AND ';

		if (isset($dados['description']) && !($dados['description'] == '')) {
			$whereClause = $whereClause. "b.description like '%".$dados['description']."%'";
			$and = ' AND ';
		};

		if (isset($dados['client']) && !($dados['client'] == '')) {
			$whereClause = $whereClause. " c.name = '".$dados['client']."' ";
			$and = ' AND ';
		};

		if (isset($dados['status']) && !($dados['status'] == '')) {
			$status = 'E';
			if ($dados['status'] == 'Baixada') {
				$status = 'B';
			}

			$whereClause = $whereClause.$and. "b.status = '".$status."' ";
			$and = ' AND ';
		};

		if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
			$_dueDateTo = $dados['_dueDateFrom'];
			if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
				$_dueDateTo = $dados['_dueDateTo'];
			}
			$whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
			$and = ' AND ';
		};

		if (isset($dados['_issueDateFrom']) && !($dados['_issueDateFrom'] == '')) {
			$_issueDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_issueDateTo']) && !($dados['_issueDateTo'] == '')) {
				$_issueDateTo = new \Datetime($dados['_issueDateTo']);
			}
			$whereClause = $whereClause.$and. "b.issueDate BETWEEN '".$dados['_issueDateFrom']."' AND '".$_issueDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		if (isset($dados['_paymentDateFrom']) && !($dados['_paymentDateFrom'] == '')) {
			$_paymentDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_paymentDateTo']) && !($dados['_paymentDateTo'] == '')) {
				$_paymentDateTo = new \Datetime($dados['_paymentDateTo']);
			}
			$whereClause = $whereClause.$and. "b.paymentDate BETWEEN '".$dados['_paymentDateFrom']."' AND '".$_paymentDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		$whereClause = $whereClause.$and." b.actualValue > 0 ";

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};

		$query = $em->createQuery($sql);
		$billsReceives = $query->getResult();

		$dataset = $billsReceives[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadSumClosed(Request $request, Response $response) {
		$dados = $request->getRow();
		$days = 0;
		if (isset($dados['days'])) {
			$days = $dados['days'];
		}
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}
        $daysAgo = (new \DateTime())->modify('-'.$days.' day');
        $_dateTo = (new \DateTime())->modify('+1 day');

		$em = Application::getInstance()->getEntityManager();
		$sql = "select sum(b.actualValue) actualValue FROM Billetreceive b LEFT JOIN b.client c";

		$whereClause = "  where b.status = 'B' ";
		$and = ' AND ';

		if (isset($dados['description']) && !($dados['description'] == '')) {
			$whereClause = $whereClause. "b.description like '%".$dados['description']."%'";
			$and = ' AND ';
		};

		if (isset($dados['client']) && !($dados['client'] == '')) {
			$whereClause = $whereClause. " c.name = '".$dados['client']."' ";
			$and = ' AND ';
		};

		if (isset($dados['status']) && !($dados['status'] == '')) {
			$status = 'E';
			if ($dados['status'] == 'Baixada') {
				$status = 'B';
			}

			$whereClause = $whereClause.$and. "b.status = '".$status."' ";
			$and = ' AND ';
		};

		if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
			$_dueDateTo = $dados['_dueDateFrom'];
			if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
				$_dueDateTo = $dados['_dueDateTo'];
			}
			$whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
			$and = ' AND ';
		};

		if (isset($dados['_issueDateFrom']) && !($dados['_issueDateFrom'] == '')) {
			$_issueDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_issueDateTo']) && !($dados['_issueDateTo'] == '')) {
				$_issueDateTo = new \Datetime($dados['_issueDateTo']);
			}
			$whereClause = $whereClause.$and. "b.issueDate BETWEEN '".$dados['_issueDateFrom']."' AND '".$_issueDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		if (isset($dados['_paymentDateFrom']) && !($dados['_paymentDateFrom'] == '')) {
			$_paymentDateTo = (new \Datetime())->modify('+1 day');
			if (isset($dados['_paymentDateTo']) && !($dados['_paymentDateTo'] == '')) {
				$_paymentDateTo = new \Datetime($dados['_paymentDateTo']);
			}
			$whereClause = $whereClause.$and. "b.paymentDate BETWEEN '".$dados['_paymentDateFrom']."' AND '".$_paymentDateTo->format('Y-m-d')."' ";
			$and = ' AND ';
		};

		$whereClause = $whereClause.$and." b.actualValue > 0 ";
		if($days > 0)
			$whereClause = $whereClause.$and." b.dueDate >= '".$daysAgo->format('Y-m-d')."' and b.dueDate <= '".$_dateTo->format('Y-m-d')."'  ";

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};

		$query = $em->createQuery($sql);
		$billsReceives = $query->getResult();

		$dataset = $billsReceives[0]['actualValue'];
		$response->setDataset($dataset);
	}

	public function loadOpenedBills(Request $request, Response $response) {
		$dados = $request->getRow();
		
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$sql = "select b FROM Billsreceive b LEFT JOIN b.client c ";

		$whereClause = "  where b.status = 'A' ";
		$and = ' AND ';

		if (isset($dados['clientName']) && !($dados['clientName'] == '')) {
			$whereClause = $whereClause.$and. "c.name = '".$dados['clientName']."' ";
			$and = ' AND ';
		};

		if (isset($dados['_dueDateFrom']) && !($dados['_dueDateFrom'] == '')) {
			$_dueDateTo = $dados['_dueDateFrom'];
			if (isset($dados['_dueDateTo']) && !($dados['_dueDateTo'] == '')) {
				$_dueDateTo = $dados['_dueDateTo'];
			}
			$whereClause = $whereClause.$and. "b.dueDate BETWEEN '".$dados['_dueDateFrom']."' AND '".$_dueDateTo."' ";
			$and = ' AND ';
		};

		if (!($whereClause == ' WHERE ')) {
		   $sql = $sql.$whereClause; 
		};

		$query = $em->createQuery($sql.' order by b.accountType ASC ');
		$billsReceives = $query->getResult();
		$today = new \Datetime();
		$today->setTime(0, 0, 0);

		$dataset = array();
		foreach($billsReceives as $billsReceive){
			$SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('billsreceive' => $billsReceive));

			$BusinessPartner = $billsReceive->getClient();
			if ($BusinessPartner) {
				$client = $BusinessPartner->getName();
				$email = $BusinessPartner->getEmail();
				if($BusinessPartner->getFinnancialEmail()) {
					$email = $BusinessPartner->getFinnancialEmail();
				}
				$phoneNumber = $BusinessPartner->getPhoneNumber();
			} else {
				$client = '';
				$email = '';
				$phoneNumber = '';
			}

			$paymentDays = 0;
			$alreadBilled = 0;
			if($SaleBillsreceive){
				$sale = $SaleBillsreceive->getSale();

				$paymentDays = (int)$sale->getPaymentDays();
				if($sale->getPaymentMethod()) {
					if($sale->getPaymentMethod() == 'Cartao' && ( $billsReceive->getAccountType() != 'Reembolso' && $billsReceive->getAccountType() != 'Credito' )) {
						$alreadBilled = (float)$sale->getAmountPaid();
					}
				}

				$issuing = '';
				$comission = 0;
				if($sale->getIssuing()){
					$comission = $sale->getIssuing()->getCommission();
					$issuing = $sale->getIssuing()->getName();
				}

				$airportFrom = '';
				if($sale->getAirportFrom() != null){
					$airportFrom = $sale->getAirportFrom()->getCode();
				}
				$flightLocator = $sale->getFlightLocator();
				$pax_name = $sale->getPax()->getName();
				$airline = '';
				if($sale->getAirline()) {
					$airline = $sale->getAirline()->getName();
				}
				$issuing_date = $sale->getIssueDate()->format('Y-m-d');
				$airportTo = '';
				if($sale->getAirportTo()) {
					$airportTo = $sale->getAirportTo()->getCode();
				}

				$checked = false;
				if($sale->getIssueDate() < $today) {
					// $checked = true;
					if($sale->getSaleChecked() == 'true' || $sale->getSaleChecked2() == 'true') {
						$checked = true;
					}
					if($sale->getExternalId() == null) {
						if($sale->getSaleChecked() == 'true') {
							$checked = true;
						}
					}
				}

				$SaleDescription = $sale->getDescription();

				if($sale->getEarlyCovered() == 'true') {
					$paymentType = 'Boleto';
				} else {
					$paymentType = 'Comum';
				}

				$miles = number_format($sale->getMilesOriginal(), 0, ',', '.');
				$miles_tax = number_format($sale->getTax(), 2, ',', '.');

			} else {

				$flightLocator = '';
				$pax_name = '';
				$issuing = '';
				$airline = '';
				$issuing_date = $billsReceive->getDueDate()->format('Y-m-d');
				$airportFrom = '';
				$airportTo = '';
				$checked = true;
				$SaleDescription = '';
				$paymentType = 'Comum';
				$miles = '';
				$miles_tax = '';
				$comission = 0;
			}

			if($billsReceive->getAccountType() == 'Reembolso' || $billsReceive->getAccountType() == 'Credito' || $billsReceive->getAccountType() == 'Remarcação' || $billsReceive->getAccountType() == 'Débito') {
				$issuing_date = $billsReceive->getDueDate()->format('Y-m-d');
				$pax_name = $billsReceive->getDescription();
				$checked = false;
				if($issuing_date < $today->format('Y-m-d')) {
					$checked = true;
				}
			}

			$dataset[] = array(
				'checked' => $checked,
				'id' => $billsReceive->getId(),
				'status' => $billsReceive->getStatus(),
				'client' => $client,
				'email' => $email,
				'phoneNumber' => $phoneNumber,
				'description' => $billsReceive->getDescription(),
				'account_type' => $billsReceive->getAccountType(),
				'due_date' => $billsReceive->getDueDate()->format('Y-m-d'),
				'actual_value' => (float)$billsReceive->getActualValue(),
				'original_value' => (float)$billsReceive->getOriginalValue(),
				'tax' => (float)$billsReceive->getTax(),
				'discount' => (float)$billsReceive->getDiscount(),
				'to' => $airportTo,
				'from' => $airportFrom,
				'issuing_date' => $issuing_date,
				'airline' => $airline,
				'issuing' => $issuing,
				'pax_name' => $pax_name,
				'flightLocator' => $flightLocator,
				'SaleDescription' => $SaleDescription,
				'paymentType' => $paymentType,
				'miles' => $miles,
				'miles_tax' => $miles_tax,
				'comission' => (float)$comission,
				'paymentDays' => $paymentDays,
				'alreadBilled' => $alreadBilled
			);
		}
		$response->setDataset($dataset);
	}

	public function loadBillGenerated(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$sql = "select b FROM Billsreceive b WHERE b.client = '".$dados['id']."' and b.status = 'E' and b.description not like '%REEMBOLSO REFERENTE AO BORDERO%' ";
		$query = $em->createQuery($sql);
		$Billsreceive = $query->getResult();

		$today = new \Datetime();
		$dataset = array();

		foreach($Billsreceive as $billsReceive){
			$SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('billsreceive' => $billsReceive));

			$BusinessPartner = $billsReceive->getClient();
			if ($BusinessPartner) {
				$client = $BusinessPartner->getName();
				$email = $BusinessPartner->getEmail();
				$phoneNumber = $BusinessPartner->getPhoneNumber();
			} else {
				$client = '';
				$email = '';
				$phoneNumber = '';
			}
			$alreadBilled = 0;
			
			if($SaleBillsreceive) {
				$sale = $SaleBillsreceive->getSale();
				$issuing = '';
				if($sale->getIssuing()){
					$issuing = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale->getIssuing()->getId()));
					$issuing = $issuing->getName();
				}

				$airportFrom = '';
				if($sale->getAirportFrom() != null){
					$airportFrom = $sale->getAirportFrom()->getCode();
				}
				$flightLocator = $sale->getFlightLocator();
				$pax_name = $sale->getPax()->getName();
				$airline = '';
				if($sale->getAirline()) {
					$airline = $sale->getAirline()->getName();
				}
				$issuing_date = $sale->getIssueDate()->format('Y-m-d');
				$airportTo = '';
				if($sale->getAirportTo()) {
					$airportTo = $sale->getAirportTo()->getCode();
				}

				$checked = false;
				if($issuing_date < $today->format('Y-m-d')) {
					$checked = true;
				}

				if($sale->getPaymentMethod()) {
					if($sale->getPaymentMethod() == 'Cartao') {
						$alreadBilled = (float)$sale->getAmountPaid();
					}
				}
			} else {

				$flightLocator = '';
				$pax_name = '';
				$issuing = '';
				$airline = '';
				$issuing_date = $billsReceive->getDueDate()->format('Y-m-d');
				$airportFrom = '';
				$airportTo = '';
				$checked = true;
			}

			$issuing_date = $billsReceive->getBillet()->getIssueDate()->format('Y-m-d');

			$dataset[] = array(
				'checked' => $checked,
				'id' => $billsReceive->getId(),
				'status' => $billsReceive->getStatus(),
				'client' => $client,
				'email' => $email,
				'phoneNumber' => $phoneNumber,
				'description' => $billsReceive->getDescription(),
				'account_type' => $billsReceive->getAccountType(),
				'due_date' => $billsReceive->getDueDate()->format('Y-m-d'),
				'actual_value' => (float)$billsReceive->getActualValue(),
				'original_value' => (float)$billsReceive->getOriginalValue(),
				'tax' => (float)$billsReceive->getTax(),
				'discount' => (float)$billsReceive->getDiscount(),
				'to' => $airportTo,
				'from' => $airportFrom,
				'issuing_date' => $issuing_date,
				'airline' => $airline,
				'issuing' => $issuing,
				'pax_name' => $pax_name,
				'flightLocator' => $flightLocator,
				'billet' => $billsReceive->getBillet()->getOurNumber(),
				'alreadBilled' => $alreadBilled
			);
		}
		$response->setDataset($dataset);
	}

	public function loadBilletBills(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$sql = "select b FROM Billsreceive b where b.billet = ".$dados['id']." ORDER BY b.dueDate";

		$query = $em->createQuery($sql);
		$billsReceives = $query->getResult();

		$dataset = array();
		foreach($billsReceives as $billsReceive){
			$SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('billsreceive' => $billsReceive));
			if($SaleBillsreceive) {
				$sale = $SaleBillsreceive->getSale();

				$issuing = '';
				if($sale->getIssuing()) {
					$issuing = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale->getIssuing()->getId()));
					$issuing = $issuing->getName();
				}
				$BusinessPartner = $billsReceive->getClient();
				if ($BusinessPartner) {
					$client = $BusinessPartner->getName();
					$email = $BusinessPartner->getEmail();
					$phoneNumber = $BusinessPartner->getPhoneNumber();
					if($BusinessPartner->getFinnancialEmail()) {
						$email = $BusinessPartner->getFinnancialEmail();
					}
				} else {
					$client = '';
					$email = '';
					$phoneNumber = '';
				}
				$flightLocator = $sale->getFlightLocator();
				$pax_name = $sale->getPax()->getName();
				$airline = '';
				if($sale->getAirline()) {
					$airline = $sale->getAirline()->getName();
				}
				$issuing_date = $sale->getIssueDate()->format('Y-m-d');

				if($sale->getAirportFrom()) {
					$airportFrom = $sale->getAirportFrom()->getCode();
				} else {
					$airportFrom = '';
				}

				if($sale->getAirportTo()) {
					$airportTo = $sale->getAirportTo()->getCode();
				} else {
					$airportTo = '';
				}

				$SaleDescription = $sale->getDescription();
				if($sale->getEarlyCovered() == 'true') {
					$paymentType = 'Boleto';
				} else {
					$paymentType = 'Comum';
				}

				$miles = number_format($sale->getMilesOriginal(), 0, ',', '.');
				$miles_tax = number_format($sale->getTax(), 2, ',', '.');

			} else {

				$BusinessPartner = $billsReceive->getClient();
				if ($BusinessPartner) {
					$client = $BusinessPartner->getName();
					$email = $BusinessPartner->getEmail();
					$phoneNumber = $BusinessPartner->getPhoneNumber();
					if($BusinessPartner->getFinnancialEmail()) {
						$email = $BusinessPartner->getFinnancialEmail();
					}
				} else {
					$client = '';
					$email = '';
					$phoneNumber = '';
				}
				$flightLocator = '';
				$pax_name = '';
				$issuing = '';
				$SaleDescription = '';
				$airline = '';
				$paymentType = 'Comum';
				$issuing_date = $billsReceive->getDueDate()->format('Y-m-d');
				$airportFrom = '';
				$airportTo = '';
				$miles = '';
				$miles_tax = '';
			}

			if($billsReceive->getAccountType() == 'Reembolso' || $billsReceive->getAccountType() == 'Credito' || $billsReceive->getAccountType() == 'Remarcação' || $billsReceive->getAccountType() == 'Débito') {
				$issuing_date = $billsReceive->getDueDate()->format('Y-m-d');
				$pax_name = $billsReceive->getDescription();
			}

			$dataset[] = array(
				'checked' => true,
				'id' => $billsReceive->getId(),
				'status' => $billsReceive->getStatus(),
				'client' => $client,
				'email' => $email,
				'phoneNumber' => $phoneNumber,
				'description' => $billsReceive->getDescription(),
				'account_type' => $billsReceive->getAccountType(),
				'due_date' => $billsReceive->getDueDate()->format('Y-m-d'),
				'actual_value' => (float)$billsReceive->getActualValue(),
				'original_value' => (float)$billsReceive->getOriginalValue(),
				'tax' => (float)$billsReceive->getTax(),
				'discount' => (float)$billsReceive->getDiscount(),
				'to' => $airportTo,
				'from' => $airportFrom,
				'issuing_date' => $issuing_date,
				'airline' => $airline,
				'issuing' => $issuing,
				'pax_name' => $pax_name,
				'flightLocator' => $flightLocator,
				'SaleDescription' => $SaleDescription,
				'paymentType' => $paymentType,
				'miles' => $miles,
				'miles_tax' => $miles_tax
			);
		}
		$response->setDataset($dataset);
	}

	public function loadLastBillet(Request $request, Response $response) {
		$dados = $request->getRow();

		$QueryBuilder = Application::getInstance()->getQueryBuilder();
		$maxNumber = 0;
		$query = "select MAX(CAST(b.our_number as UNSIGNED)) as ourNumber FROM billetreceive b where concat('',b.our_number) = b.our_number";
		$stmt = $QueryBuilder->query($query);
 		while ($row = $stmt->fetch()) {
 			$maxNumber = $row['ourNumber'];
		}

		if(!$maxNumber || $maxNumber == null) {
			$maxNumber = 0;
		}


		$em = Application::getInstance()->getEntityManager();
		$sql = "select COUNT(b) as todays FROM Billetreceive b where b.issueDate >= '".(new \Datetime())->format('Y-m-d')."' ";
		$query = $em->createQuery($sql);
		$todays = $query->getResult();

		$dataset = array();

		$dataset[] = array(
			'id' => $maxNumber,
			'todays' => $todays[0]['todays']
		);
		$response->setDataset(array_shift($dataset));
	}

	public function setAsLoss(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])){
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			if($dados['type'] == 'billet') {
				$billetReceive = $em->getRepository('Billetreceive')->find($dados['id']);
				$billetReceive->setStatus('L');

				$em->persist($billetReceive);
				$em->flush($billetReceive);
			} else {

				$BilletsDivision = $em->getRepository('BilletsDivision')->find($dados['id']);
				$BilletsDivision->setPaid('L');

				$em->persist($BilletsDivision);
				$em->flush($BilletsDivision);

			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function close(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		$dados = $request->getRow()['checkedrows'];
		$em = Application::getInstance()->getEntityManager();

		try {
			$em->getConnection()->beginTransaction();
			foreach($dados as $checkedData){
				$billetReceive = $em->getRepository('Billetreceive')->find($checkedData['id']);
				if(isset($checkedData['actual_value']) && $checkedData['actual_value'] != '') {
					$billetReceive->setActualValue($checkedData['actual_value']);
				}
				$billetReceive->setStatus('B');

				if(isset($checkedData['payment_date']) && $checkedData['payment_date'] != ''){
					$billetReceive->setPaymentDate(new \Datetime($checkedData['payment_date']));
				}

				if(isset($dados['alreadyPaid']) && $dados['alreadyPaid'] != '') {
					$billetReceive->setAlreadyPaid($dados['alreadyPaid']);
				} else {
					$billetReceive->setAlreadyPaid($checkedData['actual_value']);
				}

				$em->persist($billetReceive);
				$em->flush($billetReceive);

				$BilletsDivision = $em->getRepository('BilletsDivision')->findBy(array('billet' => $billetReceive));
				foreach ($BilletsDivision as $division) {
					$division->setPaid('true');

					$em->persist($division);
					$em->flush($division);
				}

				$billsReceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $billetReceive));
				foreach($billsReceive as $bill){
					$bill->setStatus('B');
					$em->persist($bill);
					$em->flush($bill);
				}

				$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
				$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

				$SystemLog = new \SystemLog();
				$SystemLog->setIssueDate(new \Datetime());
				$SystemLog->setDescription("Baixa Realizada - BilletId->".$checkedData['id']);
				$SystemLog->setLogType('CLOSE-BILL');
				$SystemLog->setBusinesspartner($UserPartner);

				$em->persist($SystemLog);
				$em->flush($SystemLog);

				$sql = "select COUNT(b.id) as billets FROM Billetreceive b WHERE b.client = '".$billetReceive->getClient()->getId()."' and b.status = 'B' and b.paymentDate > b.dueDate ";
				$query = $em->createQuery($sql);
				$Limit = $query->getResult();
				if($Limit[0]['billets'] > 4) {
					$email1 = 'adm@onemilhas.com.br';
					$postfields = array(
						'content' =>    "Olá,<br><br>".
										"Acaba de ser realizada a ".$Limit[0]['billets']."ª baixa em atraso do cliente.<br><br>".
										$billetReceive->getClient()->getName().
										"<br><br><br>Atenciosamente,".
										"<br>SRM-IT",
						'partner' => $email1,
						'subject' => '[ONE MILHAS] - Notificação do sistema',
						'type' => ''
					);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					$result = curl_exec($ch);
				}

			}
			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function saveChangeValue(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		if(isset($dados['billetsDivision'])) {
			$billetsDivision = $dados['billetsDivision'];
		}
		if(isset($dados['data'])){
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();

		try {
			$em->getConnection()->beginTransaction();
			$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('id' => $dados['id']));

			if($Billetreceive){
				$Billetreceive->setActualValue($dados['actual_value']);
				$Billetreceive->setTax($dados['tax']);
				$Billetreceive->setDiscount($dados['discount']);
				$Billetreceive->setOurNumber($dados['ourNumber']);
				$Billetreceive->setDocNumber($dados['ourNumber']);
				$Billetreceive->setDueDate(new \Datetime($dados['due_date']));

				if(isset($dados['description'])) {
					$Billetreceive->setDescription($dados['description']);
				}

				if(isset($dados['alreadyPaid']) && $dados['alreadyPaid'] != '') {
					$Billetreceive->setAlreadyPaid($dados['alreadyPaid']);
				} else {
					$Billetreceive->setAlreadyPaid(0);
				}
				if(isset($dados['bank']) && $dados['bank'] != '') {
					$Billetreceive->setBank($dados['bank']);
				}
				if(isset($dados['hasBillet']) && $dados['hasBillet'] != '') {
					$Billetreceive->setHasBillet($dados['hasBillet']);
				}

				$em->persist($Billetreceive);
				$em->flush($Billetreceive);

				if(isset($billetsDivision)) {
					$BilletsDivision = $em->getRepository('BilletsDivision')->findBy(array('billet' => $Billetreceive->getId()));
					foreach ($BilletsDivision as $division) {
						$em->remove($division);
						$em->flush($division);
					}

					foreach ($billetsDivision as $division) {
						$BilletsDivision = new \BilletsDivision();
						$BilletsDivision->setDueDate(new \Datetime($division['_dueDate']));
						$BilletsDivision->setActualValue((float)$division['actualValue']);
						$BilletsDivision->setBillet($Billetreceive);
						$BilletsDivision->setName($division['name']);
						$BilletsDivision->setPaid($division['paid']);

						$em->persist($BilletsDivision);
						$em->flush($BilletsDivision);
					}
				}

				if(isset($hash)) {
					$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
					$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

					$SystemLog = new \SystemLog();
					$SystemLog->setIssueDate(new \Datetime());
					$SystemLog->setDescription("BAIXA PARCIAL ALTERADA PARA ".$Billetreceive->getAlreadyPaid()." - BilletId->".$Billetreceive->getId());
					$SystemLog->setLogType('CHANGE-BILL');
					$SystemLog->setBusinesspartner($UserPartner);

					$em->persist($SystemLog);
					$em->flush($SystemLog);
				}
			}

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Alteração realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function cancelBillet(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])){
			$dados = $dados['data'];
		}
		$em = Application::getInstance()->getEntityManager();

		try {
			$em->getConnection()->beginTransaction();

			$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('id' => $dados['id']));
			$Billsreceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $Billetreceive->getId()));
			
			$Cliente = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Billetreceive->getClient()));
			$Billsreceive2 = $em->getRepository('Billsreceive')->findBy(array('client' => $Cliente->getId()));

			if(isset($request->getRow()['hashId'])) {

				$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $request->getRow()['hashId']));
				if($UserSession) {
					$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
					if($UserPartner) {

						$SystemLog = new \SystemLog();
						$SystemLog->setIssueDate(new \Datetime());
						$SystemLog->setDescription("Bordero deletado - Bordero: " . $Billetreceive->getOurNumber() . " -  Cliente: " . $Billetreceive->getClient()->getName());
						$SystemLog->setLogType('CANCEL-BILLET');
						$SystemLog->setBusinesspartner($UserPartner);
			
						$em->persist($SystemLog);
						$em->flush($SystemLog);
					}
				}
			}

			foreach ($Billsreceive as $bills) {
				$bills->setBillet();
				$bills->setStatus('A');

				$em->persist($bills);
				$em->flush($bills);
			}

			foreach ($Billsreceive2 as $bills) {
				$bills->setLastProcessDate(null);
				$em->persist($bills);
				$em->flush($bills);
			}

			$BilletsDivision = $em->getRepository('BilletsDivision')->findBy(array('billet' => $Billetreceive->getId()));
			foreach ($BilletsDivision as $division) {
				$em->remove($division);
				$em->flush($division);
			}

			$em->remove($Billetreceive);
			$em->flush($Billetreceive);

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Alteração realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function save(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['businesspartner'])) {
            $BusinessPartner = $dados['businesspartner'];
        }
		if(isset($dados['billetCredit'])) {
			$billetCredit = $dados['billetCredit'];
		}
		if(isset($dados['billetsDivision'])) {
			$billetsDivision = $dados['billetsDivision'];
		}
		if(isset($dados['wbillet'])) {
			$dados = $dados['wbillet'];
		}

		$checkedrows = $request->getRow()['checkedrows'];
		$em = Application::getInstance()->getEntityManager();

		try {

			$Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client']));

			$Billetreceive = new \Billetreceive();
			$Billetreceive->setStatus('E');
			$Billetreceive->setIssueDate(new \Datetime());
			$Billetreceive->setClient($Client);
			$Billetreceive->setOriginalValue($dados['actual_value']);
			$Billetreceive->setActualValue($dados['actual_value']);
			$Billetreceive->setTax($dados['tax']);
			$Billetreceive->setDiscount($dados['discount']);
			if(isset($dados['due_date']) && $dados['due_date'] != ''){
				$Billetreceive->setDueDate(new \Datetime($dados['due_date']));
			} else {
				$Billetreceive->setDueDate(new \Datetime());
			}
			if(isset($dados['doc_number']) && $dados['doc_number']){
				$Billetreceive->setDocNumber($dados['doc_number']);
			}
			if(isset($dados['our_number']) && $dados['our_number']){
				$Billetreceive->setOurNumber($dados['our_number']);
			}
			if(isset($dados['alreadyPaid']) && $dados['alreadyPaid']){
				$Billetreceive->setAlreadyPaid($dados['alreadyPaid']);
			}
			if(isset($dados['bank']) && $dados['bank']){
				$Billetreceive->setBank($dados['bank']);
			}
			if(isset($dados['hasBillet']) && $dados['hasBillet'] == 'true') {
				$Billetreceive->setHasBillet($dados['hasBillet']);
			} else {
				$Billetreceive->setHasBillet('false');
			}
			if(isset($dados['billingPartner']) && $dados['billingPartner'] != '') {
				$billingPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['billingPartner']));
				if($billingPartner) {
					$Billetreceive->setBillingPartner($billingPartner);
				}
			}

			if(isset($dados['description'])) {
				$Billetreceive->setDescription($dados['description']);
			}

			if(isset($dados['usedCommission']) && $dados['usedCommission'] != ''){
				$Billetreceive->setUsedCommission($dados['usedCommission']);
			}

			$em->persist($Billetreceive);

			$oldBilletArray = array();
			foreach($checkedrows as $checkedData) {
				if($checkedData['account_type'] == 'Credito' && strpos($checkedData['description'], 'REEMBOLSO REFERENTE AO BORDERO ') !== false && $checkedData['status'] == 'E') {
					$billsToDelete = $em->getRepository('Billsreceive')->find($checkedData['id']);
					if($billsToDelete) {
						$em->remove($billsToDelete);
						$em->flush($billsToDelete);
					}
				} else {
					$billsReceive = $em->getRepository('Billsreceive')->find($checkedData['id']);
					$billsReceive->setLastProcessDate(new \Datetime());
					if($billsReceive->getBillet()) {
						$billsReceive->setStatus('G');
	
						$oldBillet = $billsReceive->getBillet();
						if($oldBillet->getId() != null && !in_array($oldBillet->getId(), $oldBilletArray)) {
							$oldBilletArray[] = $oldBillet->getId();
						}
	
						$em->persist($billsReceive);
						$em->flush($billsReceive);
					} else {
						$billsReceive->setStatus('E');

						if($checkedData['account_type'] == 'Credito' && strpos($checkedData['description'], 'REEMBOLSO REFERENTE AO BORDERO ') !== false) {
							$our_number = substr($checkedData['description'], 31);
							$BilletreceiveToClose = $em->getRepository('Billetreceive')->findOneBy( array( 'ourNumber' => $our_number, 'client' => $Billetreceive->getClient()->getId() ) );
							if($BilletreceiveToClose) {
								$BilletreceiveToClose->setStatus('B');
								$em->persist($BilletreceiveToClose);
								$em->flush($BilletreceiveToClose);
							}
						}
					}
					$billsReceive->setBillet($Billetreceive);
					$em->persist($billsReceive);
					$em->flush($billsReceive);
				}
			}

			foreach ($oldBilletArray as $billetId) {
				$BillToDelete = $em->getRepository('Billetreceive')->findOneBy(array( 'id' => $billetId ));
				if($BillToDelete) {
					$em->remove($BillToDelete);
					$em->flush($BillToDelete);
				}
			}

			if(isset($billetsDivision)) {
				foreach ($billetsDivision as $division) {

					$BilletsDivision = new \BilletsDivision();
					$BilletsDivision->setDueDate(new \Datetime($division['_dueDate']));
					$BilletsDivision->setActualValue((float)$division['actualValue']);
					$BilletsDivision->setBillet($Billetreceive);
					$BilletsDivision->setName($division['name']);

					$em->persist($BilletsDivision);
					$em->flush($BilletsDivision);
				}
			}

			if((isset($dados['early']) && $dados['early'] != '') || ($dados['actual_value'] < 0)) {
				if($dados['early'] == 'true') {
					if($Billetreceive->getActualValue() - $Billetreceive->getAlreadyPaid() <= 0) {
						$Billetreceive->setStatus('B');
						$Billetreceive->setPaymentDate(new \Datetime());

						foreach($checkedrows as $checkedData){
							$bill = $em->getRepository('Billsreceive')->find($checkedData['id']);
							$bill->setStatus('B');
							$em->persist($bill);
							$em->flush($bill);
						}
					}
				}
			}

			if(isset($dados['transfer']) && $dados['transfer'] != '') {
				if($dados['transfer'] == 'true') {
					$Billetreceive->setStatus('T');
					$Billetreceive->setPaymentDate(new \Datetime());

					foreach($checkedrows as $checkedData){
						$bill = $em->getRepository('Billsreceive')->find($checkedData['id']);
						$bill->setStatus('B');

						$em->persist($bill);
						$em->flush($bill);
					}
				}
			}

			if(isset($dados['cancelBillets']) && $dados['cancelBillets'] != '') {
				if($dados['cancelBillets'] == 'true') {
					$Billetreceive->setStatus('C');
					$Billetreceive->setPaymentDate(new \Datetime());

					foreach($checkedrows as $checkedData){
						$bill = $em->getRepository('Billsreceive')->find($checkedData['id']);
						$bill->setStatus('B');

						$em->persist($bill);
						$em->flush($bill);
					}
				}
			}

			$Client = \MilesBench\Controller\ContaAzul\Sale::registerClient($Billetreceive->getClient(), $BusinessPartner);
			$em->persist($Client);
			$em->flush($Client);

			$em->persist($BusinessPartner);
			$em->flush($BusinessPartner);

			$Billetreceive = \MilesBench\Controller\ContaAzul\Sale::createSaleByArray($Billetreceive, $BusinessPartner, $Client->getContaAzulId());

			$em->persist($Billetreceive);
			$em->flush($Billetreceive);

			$em->persist($BusinessPartner);
			$em->flush($BusinessPartner);


			if($dados['actual_value'] < 0 && !(isset($dados['transfer']) && $dados['transfer'] == 'true') && !(isset($dados['cancelBillets']) && $dados['cancelBillets'] == 'true')) {

				if(isset($dados['removerFromDelayed']) && $dados['removerFromDelayed'] == 'true' ) {

					$sql = "select DISTINCT(b.billet) as billet_id FROM Billsreceive b WHERE b.client = '".$Client->getId()."' and b.status IN ('E', 'G') ";
					$query = $em->createQuery($sql);
					$Billsreceive = $query->getResult();

					$found = "0";
					$and = ",";

					foreach ($Billsreceive as $billet) {
						$found = $found.$and.$billet['billet_id'];
						$and = ', ';
					}

					$sql = "select b FROM Billetreceive b WHERE b.client = '".$Client->getId()."' and b.dueDate < '".(new \DateTime())->format('Y-m-d')."' and b.status = 'E' and b.id in (".$found.") order by b.dueDate ";
					$query = $em->createQuery($sql);
					$BilletsDelead = $query->getResult();
					$left = $dados['actual_value'];
					foreach ($BilletsDelead as $billet) {
						if($left <= ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid())) {
							$billet->setAlreadyPaid($billet->getAlreadyPaid() + $left);
							$left = 0;
							$em->persist($billet);
							$em->flush($billet);
						} else {
							$left = $left + ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid());
							$billet->setAlreadyPaid($billet->getAlreadyPaid() + ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid()));
							$billet->setStatus('B');
							$billsReceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $billet->getId()));
							foreach($billsReceive as $bill){
								$bill->setStatus('B');
								$em->persist($bill);
								$em->flush($bill);
							}
							$em->persist($billet);
							$em->flush($billet);
						}
						if($billet->getAlreadyPaid() == $billet->getActualValue()) {
							$billet->setStatus('B');
							$billsReceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $billet->getId()));
							foreach($billsReceive as $bill){
								$bill->setStatus('B');
								$em->persist($bill);
								$em->flush($bill);
							}
						}
						else{
							$billet->setStatus('E');
							$billsReceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $billet->getId()));
							foreach($billsReceive as $bill){
								$bill->setStatus('E');
								$em->persist($bill);
								$em->flush($bill);
							}
						}
					}

					if($left < 0) {

						$Billsreceive = new \Billsreceive();
						$Billsreceive->setStatus('A');
						$Billsreceive->setClient($Client);
						$Billsreceive->setDescription('REEMBOLSO REFERENTE AO BORDERO '.$Billetreceive->getOurNumber());
						$Billsreceive->setOriginalValue($left * -1);
						$Billsreceive->setActualValue($left * -1);
						$Billsreceive->setTax(0);
						$Billsreceive->setDiscount(0);
						$Billsreceive->setAccountType('Credito');
						$Billsreceive->setReceiveType('Boleto Bancario');
						$Billsreceive->setDueDate(new \Datetime());
						$Billsreceive->setLastProcessDate(new \Datetime());
						$em->persist($Billsreceive);
						$em->flush($Billsreceive);

					}
				} else if(isset($billetCredit) && isset($billetCredit['id'])) {

					$left = $dados['actual_value'] * -1;
					$billet = $em->getRepository('Billetreceive')->findOneBy(array('id' => $billetCredit['id']));

					if($left <= ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid())) {
						$billet->setAlreadyPaid($billet->getAlreadyPaid() + $left);
						$left = 0;
						$em->persist($billet);
						$em->flush($billet);
					} else {
						$left = $left + ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid());
						$billet->setAlreadyPaid($billet->getAlreadyPaid() + ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid()));
						$billet->setStatus('B');
						$billsReceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $billet->getId()));
						foreach($billsReceive as $bill){
							$bill->setStatus('B');
							$em->persist($bill);
							$em->flush($bill);
						}
						$em->persist($billet);
						$em->flush($billet);
					}
					if($billet->getAlreadyPaid() == $billet->getActualValue()) {
						$billet->setStatus('B');
						$billsReceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $billet->getId()));
						foreach($billsReceive as $bill){
							$bill->setStatus('B');
							$em->persist($bill);
							$em->flush($bill);
						}
					}

					if($left < 0) {

						$Billsreceive = new \Billsreceive();
						$Billsreceive->setStatus('A');
						$Billsreceive->setClient($Client);
						$Billsreceive->setDescription('REEMBOLSO REFERENTE AO BORDERO '.$Billetreceive->getOurNumber());
						$Billsreceive->setOriginalValue($left * -1);
						$Billsreceive->setActualValue($left * -1);
						$Billsreceive->setTax(0);
						$Billsreceive->setDiscount(0);
						$Billsreceive->setAccountType('Credito');
						$Billsreceive->setReceiveType('Boleto Bancario');
						$Billsreceive->setDueDate(new \Datetime());
						$Billsreceive->setLastProcessDate(new \Datetime());
						$em->persist($Billsreceive);
						$em->flush($Billsreceive);

					}
				} else {

					$Billsreceive = new \Billsreceive();
					$Billsreceive->setStatus('A');
					$Billsreceive->setClient($Client);
					$Billsreceive->setDescription('REEMBOLSO REFERENTE AO BORDERO '.$Billetreceive->getOurNumber());
					$Billsreceive->setOriginalValue($dados['actual_value'] * -1);
					$Billsreceive->setActualValue($dados['actual_value'] * -1);
					$Billsreceive->setTax(0);
					$Billsreceive->setDiscount(0);
					$Billsreceive->setAccountType('Credito');
					$Billsreceive->setReceiveType('Boleto Bancario');
					$Billsreceive->setDueDate(new \Datetime());
					$Billsreceive->setLastProcessDate(new \Datetime());
					$em->persist($Billsreceive);
					$em->flush($Billsreceive);
				}

			}

			// if( $Client->getPaymentType() == 'Antecipado' ) {
			// 	if( $dados['actual_value'] > $dados['alreadyPaid'] ) {

			// 		$Billsreceive = new \Billsreceive();
			// 		$Billsreceive->setStatus('A');
			// 		$Billsreceive->setClient($Client);
			// 		$Billsreceive->setDescription('DEBITO REFERENTE AO BORDERO '.$Billetreceive->getOurNumber());
			// 		$Billsreceive->setOriginalValue($dados['actual_value'] - $dados['alreadyPaid']);
			// 		$Billsreceive->setActualValue($dados['actual_value'] - $dados['alreadyPaid']);
			// 		$Billsreceive->setTax(0);
			// 		$Billsreceive->setDiscount(0);
			// 		$Billsreceive->setAccountType('Débito');
			// 		$Billsreceive->setReceiveType('Boleto Bancario');
			// 		$Billsreceive->setDueDate(new \Datetime());
			// 		$em->persist($Billsreceive);
			// 		$em->flush($Billsreceive);

			// 	} else if( $dados['actual_value'] < $dados['alreadyPaid'] ) {

			// 		$Billsreceive = new \Billsreceive();
			// 		$Billsreceive->setStatus('A');
			// 		$Billsreceive->setClient($Client);
			// 		$Billsreceive->setDescription('CREDITO REFERENTE AO BORDERO '.$Billetreceive->getOurNumber());
			// 		$Billsreceive->setOriginalValue($dados['alreadyPaid'] - $dados['actual_value']);
			// 		$Billsreceive->setActualValue($dados['alreadyPaid'] - $dados['actual_value']);
			// 		$Billsreceive->setTax(0);
			// 		$Billsreceive->setDiscount(0);
			// 		$Billsreceive->setAccountType('Credito');
			// 		$Billsreceive->setReceiveType('Boleto Bancario');
			// 		$Billsreceive->setDueDate(new \Datetime());
			// 		$em->persist($Billsreceive);
			// 		$em->flush($Billsreceive);

			// 	}
			// }

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function saveCheckInStatus(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])){
			$hash = $dados['hashId'];
		}
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();

		try {
			$em->getConnection()->beginTransaction();

			foreach ($dados as $billetReceiveData) {

				$Billetreceive = $em->getRepository('Billetreceive')->find($billetReceiveData['id']);
				$Billetreceive->setCheckinState($billetReceiveData['checkinState']);

				$em->persist($Billetreceive);
				$em->flush($Billetreceive);
			}

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('CheckIn Realizado com sucesso!');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function changeBillValue(Request $request, Response $response) {
		$hash = $request->getRow()['hashId'];
		$dados = $request->getRow()['data'];

		$em = Application::getInstance()->getEntityManager();

		try {

			$em->getConnection()->beginTransaction();

			$Billsreceive = $em->getRepository('Billsreceive')->findOneBy(array('id' => $dados['id']));
			if($Billsreceive) {
				$Billsreceive->setActualValue($dados['actual_value']);

				$em->persist($Billsreceive);
				$em->flush($Billsreceive);
			}

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function removeBill(Request $request, Response $response) {
		$hash = $request->getRow()['hashId'];
		$dados = $request->getRow()['data'];

		$em = Application::getInstance()->getEntityManager();

		try {

			$em->getConnection()->beginTransaction();

			$Billsreceive = $em->getRepository('Billsreceive')->findOneBy(array('id' => $dados['id']));
			if($Billsreceive) {
				$SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('billsreceive' => $dados['id']));

				if($SaleBillsreceive) {
					$em->remove($SaleBillsreceive);
					$em->flush($SaleBillsreceive);
				}

				$em->remove($Billsreceive);
				$em->flush($Billsreceive);

				$UserSession = $em->getRepository('UserSession')->findOneBy(array( 'hashid' => $request->getRow()['hashId'] ));
				$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
				$SystemLog = new \SystemLog();
				$SystemLog->setIssueDate(new \Datetime());
				$SystemLog->setDescription("Cobrança removida - " . $Billsreceive->getClient()->getName());
				$SystemLog->setLogType('EVENT');
				$SystemLog->setBusinesspartner($UserPartner);

				$em->persist($SystemLog);
				$em->flush($SystemLog);
			}

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function generateBillReceive(Request $request, Response $response) {
		$hash = $request->getRow()['hashId'];
		$dados = $request->getRow()['data'];

		$em = Application::getInstance()->getEntityManager();

		try {

			$em->getConnection()->beginTransaction();

			$Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client']));

			$Billsreceive = new \Billsreceive();
			$Billsreceive->setStatus('A');
			$Billsreceive->setClient($Client);
			$Billsreceive->setDescription($dados['description']);
			$Billsreceive->setOriginalValue($dados['actual_value']);
			$Billsreceive->setActualValue($dados['actual_value']);
			$Billsreceive->setTax(0);
			$Billsreceive->setDiscount(0);
			$Billsreceive->setAccountType('Credito');
			if(isset($dados['type']) && $dados['type'] == 'advance') {
				$Billsreceive->setAccountType('Credito Adiantamento');
			}
			$Billsreceive->setReceiveType('Boleto Bancario');
			$Billsreceive->setDueDate(new \Datetime());
			$Billsreceive->setLastProcessDate(new \Datetime());
			$em->persist($Billsreceive);
			$em->flush($Billsreceive);

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
			if($UserSession) {
				$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

				$SystemLog = new \SystemLog();
				$SystemLog->setIssueDate(new \Datetime());
				$SystemLog->setDescription("Credito Cadastrado - Usuario:".$UserPartner->getName()." - Valor: R$ ".$dados['actual_value']." - Cliente: ".$Client->getName());
				$SystemLog->setLogType('BILLSRECEIVE');
				$SystemLog->setBusinesspartner($UserPartner);

				$em->persist($SystemLog);
				$em->flush($SystemLog);
			}

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function generateBillClient(Request $request, Response $response) {
		$hash = $request->getRow()['hashId'];
		$dados = $request->getRow()['data'];

		$em = Application::getInstance()->getEntityManager();

		try {

			$em->getConnection()->beginTransaction();
			$Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client']));

			$Billsreceive = new \Billsreceive();
			$Billsreceive->setStatus('A');
			$Billsreceive->setClient($Client);
			$Billsreceive->setDescription($dados['description']);
			$Billsreceive->setOriginalValue($dados['actual_value']);
			$Billsreceive->setActualValue($dados['actual_value']);
			$Billsreceive->setTax(0);
			$Billsreceive->setDiscount(0);
			$Billsreceive->setAccountType('Débito');
			$Billsreceive->setReceiveType('Boleto Bancario');
			$Billsreceive->setDueDate(new \Datetime());
			$Billsreceive->setLastProcessDate(new \Datetime());
			$em->persist($Billsreceive);
			$em->flush($Billsreceive);

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
			if($UserSession) {
				$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

				$SystemLog = new \SystemLog();
				$SystemLog->setIssueDate(new \Datetime());
				$SystemLog->setDescription("Credito Cadastrado - Usuario:".$UserPartner->getName()." - Valor: R$ ".$dados['actual_value']);
				$SystemLog->setLogType('BILLSRECEIVE');
				$SystemLog->setBusinesspartner($UserPartner);

				$em->persist($SystemLog);
				$em->flush($SystemLog);
			}

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadClientsDebits(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['filter'])) {
			$filter = $dados['filter'];
		}
		$em = Application::getInstance()->getEntityManager();
		$QueryBuilder = Application::getInstance()->getQueryBuilder();

		$sql = "select distinct(b.client_id) as clients, c.* FROM billetreceive b INNER JOIN businesspartner c on c.id = b.client_id WHERE b.status in ('E', 'L') ".
		" and b.due_date <= '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' and b.actual_value > 0 ";
		
		if(isset($filter['dayFrom']) && $filter['dayFrom'] != '+') {
			$sql = $sql." and b.due_date >= '".(new \DateTime())->modify('-'.$filter['dayFrom'].' day')->format('Y-m-d')."' ";
		}

		if(isset($filter['dayTo']) && $filter['dayTo'] != '+') {
			$sql = $sql." and b.due_date <= '".(new \DateTime())->modify('-'.$filter['dayTo'].' day')->format('Y-m-d')."' ";
		}

		$dataset = array();
        $stmt = $QueryBuilder->query($sql." order by c.name ");
        while ($client = $stmt->fetch()) {

			$billets = array();
			$total = 0;
			$billingPeriod = false;

			if($client['billing_period'] == 'Semanal' || $client['billing_period'] == 'Quinzenal' || $client['billing_period'] == 'Mensal') {
				$billingPeriod = true;
			}

			$sql = "select b FROM Billetreceive b where b.status in ('E', 'L') and b.client = '".$client['clients']."' and b.dueDate <= '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' and b.actualValue > 0 ";
			$query = $em->createQuery($sql);
			$Bills = $query->getResult();

			foreach ($Bills as $bill) {

				if($billingPeriod) {
					$sql = "select b FROM Billsreceive b where b.billet = '".$bill->getId()."' and b.status = 'E' ";
					$query = $em->createQuery($sql);
					$Billsreceive = $query->getResult();
				}

				$sql = "select d FROM BilletsDivision d where d.billet = '".$bill->getId()."' ";
				$query = $em->createQuery($sql);
				$hasDivisions = $query->getResult();

				if((count($hasDivisions) == 0) && !$billingPeriod) {

					$total += (float)$bill->getActualValue() - (float)$bill->getAlreadyPaid();
					$billets[] = array(
						'id' => $bill->getId(),
						'our_number' => $bill->getOurNumber(),
						'actualValue' => (float)$bill->getActualValue(),
						'alreadyPaid' => (float)$bill->getAlreadyPaid(),
						'value' => ((float)$bill->getActualValue() - (float)$bill->getAlreadyPaid()),
						'due_date' => $bill->getDueDate()->format('Y-m-d H:i:s'),
						'status' => $bill->getStatus(),
						'type' => 'billet'
					);
				} else if($billingPeriod && count($Billsreceive) == 0 && count($hasDivisions) == 0) {
					$total += (float)$bill->getActualValue() - (float)$bill->getAlreadyPaid();
					$billets[] = array(
						'id' => $bill->getId(),
						'our_number' => $bill->getOurNumber(),
						'actualValue' => (float)$bill->getActualValue(),
						'alreadyPaid' => (float)$bill->getAlreadyPaid(),
						'value' => ((float)$bill->getActualValue() - (float)$bill->getAlreadyPaid()),
						'due_date' => $bill->getDueDate()->format('Y-m-d H:i:s'),
						'status' => $bill->getStatus(),
						'type' => 'billet'
					);

				}
			}

			$sql = "select b FROM BilletsDivision b JOIN b.billet w where w.status = 'E' and b.dueDate < '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' and w.actualValue > 0 and w.client = '".$client['clients']."' ";
			$query = $em->createQuery($sql);
			$Bills = $query->getResult();

			foreach ($Bills as $bill) {

				$alreadyPaid = 0;
				if($bill->getBillet()->getAlreadyPaid() > 0) {
					$sql = "select SUM(b.actualValue) as actualValue FROM BilletsDivision b where b.dueDate < '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' and b.billet = '".$bill->getBillet()->getId()."' and b.id < '".$bill->getId()."' ";
					$query = $em->createQuery($sql);
					$actualValue = $query->getResult();
					if($bill->getBillet()->getAlreadyPaid() - $actualValue[0]['actualValue'] > 0) {
						if($bill->getActualValue() < ($bill->getBillet()->getAlreadyPaid() - $actualValue[0]['actualValue'])) {
							$alreadyPaid = $bill->getActualValue();
						} else {
							$alreadyPaid = $bill->getBillet()->getAlreadyPaid() - $actualValue[0]['actualValue'];
						}
					}
				}

				$total += (float)$bill->getActualValue() - $alreadyPaid;
				$billets[] = array(
					'id' => $bill->getId(),
					'our_number' => $bill->getName(),
					'actualValue' => (float)$bill->getActualValue(),
					'alreadyPaid' => $alreadyPaid,
					'value' => (float)$bill->getActualValue() - $alreadyPaid,
					'due_date' => $bill->getDueDate()->format('Y-m-d H:i:s'),
					'status' => $bill->getPaid(),
					'type' => 'division'
				);
			}

			$billets[] = array(
				'our_number' => 'Total:',
				'value' => $total
			);

			// $Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client));
			if($total > 0)  {
				$dataset[] = array(
					'id' => $client['clients'],
					'client' => $client['name'],
					'status' => $client['status'],
					'billets' => $billets
				);
			}
		}
		$response->setDataset($dataset);
	}

	public function loadBilletFinancial(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();
		$SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findBy(array('sale' => $dados['id']));
		foreach ($SaleBillsreceive as $Sales) {
			$billsreceive = $Sales->getBillsreceive();

			if($billsreceive) {

				$status = "Não Enviado";
				$value = (float)$billsreceive->getActualValue();
				if($billsreceive->getBillet()){
					$status = "Enviado";
					if($billsreceive->getBillet()->getStatus() == "B"){
						$status = "Pago";
					}
				}

				$dataset[] = array(
					'status' => $status,
					'value' => $value,
					'type' => $billsreceive->getAccountType()
				);
			}
		}
		$response->setDataset($dataset);
	}

	public function loadBillsBillets(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['checkedrows'])) {
			$dados = $dados['checkedrows'];
		}

		$em = Application::getInstance()->getEntityManager();

		$dataset = array();
		foreach ($dados as $billet) {

			$Billsreceive = $em->getRepository('Billsreceive')->findOneBy(array('id' => $billet['id']));
			$Billetreceive = $Billsreceive->getBillet();
			$Billsreceive = $em->getRepository('Billsreceive')->findBy(array('billet' => $Billetreceive->getId()));

			foreach($Billsreceive as $billsReceive){
				if( strpos($billsReceive->getDescription(), 'REEMBOLSO REFERENTE AO BORDERO ') === false ) {
					$SaleBillsreceive = $em->getRepository('SaleBillsreceive')->findOneBy(array('billsreceive' => $billsReceive));
	
					$BusinessPartner = $billsReceive->getClient();
					if ($BusinessPartner) {
						$client = $BusinessPartner->getName();
						$email = $BusinessPartner->getEmail();
						if($BusinessPartner->getFinnancialEmail()) {
							$email = $BusinessPartner->getFinnancialEmail();
						}
						$phoneNumber = $BusinessPartner->getPhoneNumber();
					} else {
						$client = '';
						$email = '';
						$phoneNumber = '';
					}
					
					if($SaleBillsreceive){
						$sale = $SaleBillsreceive->getSale();
						$issuing = '';
						if($sale->getIssuing()){
							$issuing = $em->getRepository('Businesspartner')->findOneBy(array('id' => $sale->getIssuing()->getId()));
							$issuing = $issuing->getName();
						}
	
						$airportFrom = '';
						if($sale->getAirportFrom() != null){
							$airportFrom = $sale->getAirportFrom()->getCode();
						}
						$flightLocator = $sale->getFlightLocator();
						$pax_name = $sale->getPax()->getName();
						$airline = '';
						if($sale->getAirline()) {
							$airline = $sale->getAirline()->getName();
						}
						$issuing_date = $sale->getIssueDate()->format('Y-m-d');
						$airportTo = '';
						if($sale->getAirportTo()) {
							$airportTo = $sale->getAirportTo()->getCode();
						}
	
						$checked = true;
						$SaleDescription = $sale->getDescription();
	
						$miles = number_format($sale->getMilesOriginal(), 0, ',', '.');
						$miles_tax = number_format($sale->getTax(), 2, ',', '.');
					} else {
						$flightLocator = '';
						$pax_name = '';
						$issuing = '';
						$airline = '';
						$issuing_date = $billsReceive->getDueDate()->format('Y-m-d');
						$airportFrom = '';
						$airportTo = '';
						$checked = true;
						$SaleDescription = '';
						$miles = '';
						$miles_tax = '';
					}
	
					if($billsReceive->getAccountType() == 'Reembolso' || $billsReceive->getAccountType() == 'Credito' || $billsReceive->getAccountType() == 'Remarcação' || $billsReceive->getAccountType() == 'Débito') {
						$issuing_date = $billsReceive->getDueDate()->format('Y-m-d');
						$pax_name = $billsReceive->getDescription();
						$checked = true;
					}
	
					$dataset[] = array(
						'checked' => $checked,
						'id' => $billsReceive->getId(),
						'status' => $billsReceive->getStatus(),
						'client' => $client,
						'email' => $email,
						'phoneNumber' => $phoneNumber,
						'description' => $billsReceive->getDescription(),
						'account_type' => $billsReceive->getAccountType(),
						'due_date' => $billsReceive->getDueDate()->format('Y-m-d'),
						'actual_value' => (float)$billsReceive->getActualValue(),
						'original_value' => (float)$billsReceive->getOriginalValue(),
						'tax' => (float)$billsReceive->getTax(),
						'discount' => (float)$billsReceive->getDiscount(),
						'to' => $airportTo,
						'from' => $airportFrom,
						'issuing_date' => $issuing_date,
						'airline' => $airline,
						'issuing' => $issuing,
						'pax_name' => $pax_name,
						'flightLocator' => $flightLocator,
						'SaleDescription' => $SaleDescription,
						'miles' => $miles,
						'miles_tax' => $miles_tax
					);
				}
			}
		}
		$response->setDataset($dataset);
	}

	public function checkClientsDeadLine(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$dataset = array();
		$sql = "select distinct(b.client) as clients FROM Billsreceive b JOIN b.client c WHERE c.billingPeriod <> 'Diario' and c.billingPeriod <> '' ";
		$query = $em->createQuery($sql);
		$Clients = $query->getResult();

		foreach ($Clients as $client) {
			$sql = "select MIN(b.dueDate) as dueDate FROM Billsreceive b WHERE b.client = '".$client['clients']."' and b.status = 'E' ";
			$query = $em->createQuery($sql);
			$Billsreceive = $query->getResult();
			$valid = false;


			if($Billsreceive[0]['dueDate'] != NULL) {

				$sql = " select DISTINCT(b.billet) as billet_id from Billsreceive b where b.client = '".$client['clients']."' and b.status = 'E' ";
				$query = $em->createQuery($sql);
				$BilletsIds = $query->getResult();

				$found = "0";
				$and = ",";

				foreach ($BilletsIds as $billet) {
					$found = $found.$and.$billet['billet_id'];
					$and = ', ';
				}

				$sql = "select MIN(b.issueDate) as issueDate FROM Billetreceive b WHERE b.status = 'E' and b.client ='".$client['clients']."' and b.id in (".$found.") and b.actualValue > 0 ";
				$query = $em->createQuery($sql);
				$mixBillet = $query->getResult();

				$sql = "select MAX(b.issueDate) as issueDate FROM Billetreceive b WHERE b.status = 'E' and b.client ='".$client['clients']."' and b.id in (".$found.") and b.actualValue > 0 ";
				$query = $em->createQuery($sql);
				$maxBillet = $query->getResult();

				if($mixBillet[0]['issueDate'] != NULL) {
					$min_emission = $mixBillet[0]['issueDate'];
				} else {
					$min_emission = '';
				}

				if($maxBillet[0]['issueDate'] != NULL) {
					$max_emission = $maxBillet[0]['issueDate'];
				} else {
					$max_emission = '';
				}

				$Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['clients']));

				if($Client->getBillingPeriod() == 'Semanal') {
					if((new \DateTime($min_emission))->diff(new \Datetime())->days >= 7) {

						$dataset[] = array(
							'name' => $Client->getName(),
							'billingPeriod' => $Client->getBillingPeriod(),
	                		'min_emission' => $min_emission,
	                		'max_emission' => $max_emission
						);
					}
				} elseif ( $Client->getBillingPeriod() == 'Quinzenal' ) {
					if((new \DateTime())->modify('first day of last month')->modify('+15 day') >= (new \DateTime($min_emission))
						|| (new \DateTime())->modify('first day of this month') >= (new \DateTime($min_emission))
						) {

						$dataset[] = array(
							'name' => $Client->getName(),
							'billingPeriod' => $Client->getBillingPeriod(),
							'min_emission' => $min_emission,
							'max_emission' => $max_emission
						);
					}
				} elseif ( $Client->getBillingPeriod() == 'Mensal' ) {
					if((new \DateTime())->modify('first day of this month') >= (new \DateTime($min_emission))) {


						$dataset[] = array(
							'name' => $Client->getName(),
							'billingPeriod' => $Client->getBillingPeriod(),
							'min_emission' => $min_emission,
							'max_emission' => $max_emission
						);
					}
				} elseif ( is_int((int)$Client->getBillingPeriod()) ) {
					if ((new \DateTime($min_emission))->diff(new \Datetime())->days >= (int)$Client->getBillingPeriod()) {

						$dataset[] = array(
							'name' => $Client->getName(),
							'billingPeriod' => $Client->getBillingPeriod(),
                			'min_emission' => $min_emission,
                			'max_emission' => $max_emission
						);
					}
				}
			}
		}
		$response->setDataset($dataset);
	}

	public function loadDivisionsBillet(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		$BilletsDivision = $em->getRepository('BilletsDivision')->findBy(array('billet' => $dados['id']));

		$dataset = array();
		foreach ($BilletsDivision as $division) {
			$dataset[] = array(
				'dueDate' => $division->getDueDate()->format('Y-m-d'),
				'actualValue' => (float)$division->getActualValue(),
				'name' => $division->getName(),
				'paid' => ($division->getPaid() == 'true')
			);
		}
		$response->setDataset($dataset);
	}

	public function saveBilletsValue(Request $request, Response $response) {
		$hash = $request->getRow()['hashId'];
		$dados = $request->getRow()['data'];

		$em = Application::getInstance()->getEntityManager();

		try {

			$em->getConnection()->beginTransaction();

			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hash));
			if($UserSession) {
				$UserPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));

				$SystemLog = new \SystemLog();
				$SystemLog->setIssueDate(new \Datetime());
				$SystemLog->setDescription("Baixa Realizada - Usuario:".$UserPartner->getName()." - Valor: R$ ".$dados['value']);
				$SystemLog->setLogType('BILLET');
				$SystemLog->setBusinesspartner($UserPartner);

				$em->persist($SystemLog);
				$em->flush($SystemLog);
			}

			$sql = "select b FROM Billetreceive b where b.client = '".$dados['id']."' and b.status = 'E' and b.actualValue > 0 order by b.dueDate ";
			$query = $em->createQuery($sql);
			$Billetreceive = $query->getResult();

			$left = $dados['value'];
			foreach ($Billetreceive as $billet) {

				if($left > 0) {
					if($left < ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid())) {
						$billet->setAlreadyPaid($billet->getAlreadyPaid() + $left);
						$left = 0;
					} else {
						$left = $left - ((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid());
						$billet->setAlreadyPaid((float)$billet->getActualValue());
						$billet->setStatus('B');
						$billet->setPaymentDate(new \DateTime());
					}
					if(isset($dados['resolveDescription']) && $dados['resolveDescription'] != '') {
						$billet->setDescription($billet->getDescription().'  -- Motivo Baixa -> '.$dados['resolveDescription']);
					}
				}

				$em->persist($billet);
				$em->flush($billet);
			}

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Baixa realizada com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadAnticipatedDebits(Request $request, Response $response) {
		$dados = $request->getRow();
		$em = Application::getInstance()->getEntityManager();

		$sql = "select distinct(b.client) as clients FROM Billetreceive b JOIN b.client c WHERE c.paymentType = 'Antecipado' order by c.name ";
		$query = $em->createQuery($sql);
		$Billetreceive = $query->getResult();

		$dataset = array();
		foreach ($Billetreceive as $client) {
			$billets = array();
			$total = 0;

			$sql = "select b FROM Billetreceive b where b.client = '".$client['clients']."' ";
			$query = $em->createQuery($sql);
			$Bills = $query->getResult();

			foreach ($Bills as $bill) {
				if((float)$bill->getActualValue() > 0) {
					$total += (float)$bill->getActualValue();
				}
				$billets[] = array(
					'our_number' => $bill->getOurNumber(),
					'value' => (float)$bill->getActualValue(),
					'issueDate' => $bill->getIssueDate()->format('Y-m-d')
				);
			}

			$billets[] = array(
				'our_number' => 'Total:',
				'value' => $total
			);

			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client));
			$dataset[] = array(
				'id' => $client['clients'],
				'client' => $Businesspartner->getName(),
				'status' => $Businesspartner->getStatus(),
				'billets' => $billets
			);
		}
		$response->setDataset($dataset);
	}

	public function saveBilletAgreement(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$em = Application::getInstance()->getEntityManager();
		try {

			$em->getConnection()->beginTransaction();

			$Client = $em->getRepository('Businesspartner')->findOneBy(array('name' => $dados['client']));

			$Billetreceive = new \Billetreceive();
			$Billetreceive->setStatus('E');
			$Billetreceive->setIssueDate(new \Datetime());
			$Billetreceive->setClient($Client);
			$Billetreceive->setOriginalValue($dados['actualValue']);
			$Billetreceive->setActualValue($dados['actualValue']);
			$Billetreceive->setTax(0);
			$Billetreceive->setDiscount(0);
			$Billetreceive->setDueDate((new \Datetime())->modify('-2 day'));
			$Billetreceive->setDocNumber('ACORDO');
			$Billetreceive->setOurNumber('ACORDO');
			$Billetreceive->setAlreadyPaid(0);

			$em->persist($Billetreceive);
			$em->flush($Billetreceive);

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Acordo cadastrado com sucesso');
			$response->addMessage($message);

		} catch (Exception $e) {
			$em->getConnection()->rollback();
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadOpenedBillets(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}
		$em = Application::getInstance()->getEntityManager();

		$sql = "select b FROM Billetreceive b where b.client = '".$dados['id']."' and b.status = 'E' and b.actualValue > 0 ";
		$query = $em->createQuery($sql);
		$Billetreceive = $query->getResult();

		$dataset = array();
		foreach ($Billetreceive as $billets) {
			$dataset[] = array(
				'id' => $billets->getId(),
				'ourNumber' => $billets->getOurNumber(),
				'actuanValue' => $billets->getActualValue()
			);
		}
		$response->setDataset($dataset);
	}
	
}
