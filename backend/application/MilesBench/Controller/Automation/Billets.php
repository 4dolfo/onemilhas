<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

/**
* @author Arthur
*/
class Billets
{
	public function dailyReminderLessDays(){

		try{

			$em = Application::getInstance()->getEntityManager();

			$sql = "select DISTINCT(b.client) as client FROM Billetreceive b JOIN b.client c where ".
				" b.status = 'E' and b.dueDate < '".(new \DateTime())->format('Y-m-d')."' and b.actualValue > 0 and ( c.billingPeriod = 'Diario' or c.billingPeriod = '' ) ";
			$query = $em->createQuery($sql);
			$Clients = $query->getResult();			
			//align="left; cellspacing="10"; style ="border: 1px solid black";"
			foreach ($Clients as $client) {
				$table = '<table width="95%"; align="center"; border="1"; style="text-align: center; border-collapse: collapse;">';
				$table .='<tr bgcolor="#5D7B9D";>';
				$table .='<th>Nome</th>';
				$table .='<th>Borderô</th>';
				$table .='<th>Vencimento</th>';
				$table .='<th>Valor</th></tr>';

				$BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['client']));				

				$sqlBillet = "select  c.name as name, b.actualValue as actualValue, b.dueDate as dueDate, b.ourNumber as ourNumber FROM Billetreceive b JOIN b.client c where ".
				" b.status = 'E' and b.dueDate < '".(new \DateTime())->format('Y-m-d')."' and b.actualValue > 0 and ( c.billingPeriod = 'Diario' or c.billingPeriod = '' ) and c.id = '".$BusinessPartner->getId()."'";
				$queryBillet = $em->createQuery($sqlBillet);				
				$Billet = $queryBillet->getResult();
	
				
				foreach ($Billet as $Billetreceive) {
					$table .='<tr font color="#ffffff">';
					$table .='<td>'.$Billetreceive['name'].'</td>';
					$table .='<td>'.$Billetreceive['ourNumber'].'</td>';
					$table .='<td>'.$Billetreceive['dueDate']->format('d-m-Y').'</td>';
					$table .='<td>'.'R$'.$Billetreceive['actualValue'].'</td></tr>';
				}

				$table .= '</table>';				
				$email = $BusinessPartner->getEmail();
				
				if($BusinessPartner->getFinnancialEmail()) {
					$email = $BusinessPartner->getFinnancialEmail();
				}
				
				$email1 = 'adm@onemilhas.com.br';
				$postfields = array(
					'content' => "<br><br>Bom dia,<br><br>Lembramos o(s) vencimento(s) do(s) boleto(s) com as referências abaixo. Pague em dia, evite juros.<br>".
						"<br>Caso já tenha pago, favor desconsiderar o e-mail.".
						" Para retirar a 2ª Via, acesse : https://banco.bradesco/html/classic/produtos-servicos/mais-produtos-servicos/segunda-via-boleto.shtm".
						"<br><br>".$table."<br><br>Obrigado pela parceria!".
						"<br><br><br>Atenciosamente,".
						"<br>Financeiro",
					'partner' => $email1,
					'from' => $email1,
					'subject' => 'BOLETO(S) VENCIDOS',
					'type' => ''
				);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result = curl_exec($ch);
				sleep(1);
			}

		}catch(\Exception $e){
			$email1 = 'financeiro@onemilhas.com.br';
			$postfields = array(
				'content' => "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR',
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

	public function dailyReminder() {

		try{
			$em = Application::getInstance()->getEntityManager();

			$sql = " select DISTINCT(d.billet) as billet_id from BilletsDivision d where d.paid = 'false' and d.dueDate = '".(new \DateTime())->format('Y-m-d')."' ";
			$query = $em->createQuery($sql);
			$BilletsIds = $query->getResult();

			$found = "0";
			$and = ",";

			foreach ($BilletsIds as $billet) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}

			$sql = "select DISTINCT(b.client) as client FROM Billetreceive b JOIN b.client c where ".
				" b.status = 'E' and b.dueDate = '".(new \DateTime())->format('Y-m-d')."' and b.actualValue > 0 and ( c.billingPeriod = 'Diario' or c.billingPeriod = '' ) ".
				" or b.id in ( ".$found." ) ";

			$query = $em->createQuery($sql);
			$Clients = $query->getResult();

			foreach ($Clients as $client) {

				$BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['client']));
				$data = "";

				$email = $BusinessPartner->getEmail();
				if($BusinessPartner->getFinnancialEmail()) {
					$email = $BusinessPartner->getFinnancialEmail();
				}

				$sql = "select b FROM Billetreceive b where b.status = 'E' and b.actualValue > 0 and b.dueDate = '".(new \DateTime())->format('Y-m-d')."' and b.client = '".$client['client']."' and b.id not in ( ".$found." ) ";
				$query = $em->createQuery($sql);
				$Billets = $query->getResult();

				foreach ($Billets as $billet) {
					$sql = "select d FROM BilletsDivision d where d.billet = '".$billet->getId()."' and d.paid = 'false' ";
					$query = $em->createQuery($sql);
					$hasDivisions = $query->getResult();

					if(count($hasDivisions) == 0) {
						$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$billet->getOurNumber()."  -  ".$billet->getDueDate()->format('d-m-Y').
						"  -  ".number_format(((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid()), 2, ',', '.');
					}
				}

				$sql = "select d FROM BilletsDivision d JOIN d.billet b where d.paid = 'false' and d.dueDate = '".(new \DateTime())->format('Y-m-d')."' and b.client = '".$client['client']."' ";
				$query = $em->createQuery($sql);
				$BilletsDivision = $query->getResult();

				foreach ($BilletsDivision as $division) {
					$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$division->getName()."  -  ".$division->getDueDate()->format('d-m-Y').
					"  -  ".number_format((float)$division->getActualValue(), 2, ',', '.');
				}

				$email1 = 'adm@onemilhas.com.br';
				$email2 = 'financeiro@onemilhas.com.br';
				$postfields = array(
					'content' =>    "<br><br>Bom dia,<br><br>Lembramos o(s) vencimento(s) do(s) boleto(s) com as referências abaixo. Pague em dia, evite juros.<br>Caso já tenha pago, favor desconsiderar o e-mail.".
						$data.
						"<br><br><br>Obrigado pela parceria!".
						"<br><br><br>Atenciosamente,".
						"<br>Financeiro",
					'partner' => $email,
					'bcc' => $email1.';'.$email2,
					'from' => $email1,
					'subject' => 'LEMBRETE VENCIMENTO BOLETO(S)',
					'type' => ''
				);

				if($BusinessPartner->getSubClient() == 'true') {
					$postfields['from'] = $BusinessPartner->getMasterClient()->getEmail();
				}

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result = curl_exec($ch);
				sleep(1);
			}


			// week, month
			$sql = " select DISTINCT(b.billet) as billet_id from Billsreceive b JOIN b.billet t where b.status = 'G' and t.status = 'E' and t.dueDate = '".(new \DateTime())->format('Y-m-d')."' and b.actualValue > 0 ";
			$query = $em->createQuery($sql);
			$BilletsIds = $query->getResult();

			$found = "0";
			$and = ",";

			foreach ($BilletsIds as $billet) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}

			$sql = "select DISTINCT(b.client) as client FROM Billetreceive b JOIN b.client c where ".
				" b.dueDate = '".(new \DateTime())->format('Y-m-d')."' and b.actualValue > 0 and b.status = 'E' ".
				" and ( c.billingPeriod in ('Semanal', 'Quinzenal', 'Mensal') ) ".
				" and b.id in ( ".$found." ) ";

			$query = $em->createQuery($sql);
			$Clients = $query->getResult();

			foreach ($Clients as $client) {

				$BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['client']));
				$data = "";

				$email = $BusinessPartner->getEmail();
				if($BusinessPartner->getFinnancialEmail()) {
					$email = $BusinessPartner->getFinnancialEmail();
				}

				$sql = "select b FROM Billetreceive b where b.status = 'E' and b.actualValue > 0 and b.dueDate = '".(new \DateTime())->format('Y-m-d')."' and b.client = '".$client['client']."' and b.id in ( ".$found." ) ";
				$query = $em->createQuery($sql);
				$Billets = $query->getResult();

				foreach ($Billets as $billet) {
					$sql = "select d FROM BilletsDivision d where d.billet = '".$billet->getId()."' and d.paid = 'false' ";
					$query = $em->createQuery($sql);
					$hasDivisions = $query->getResult();

					if(count($hasDivisions) == 0) {
						$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$billet->getOurNumber()."  -  ".$billet->getDueDate()->format('d-m-Y').
						"  -  ".number_format(((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid()), 2, ',', '.');
					}
				}

				$sql = "select d FROM BilletsDivision d JOIN d.billet b where d.paid = 'false' and d.dueDate = '".(new \DateTime())->format('Y-m-d')."' and b.client = '".$client['client']."' ";
				$query = $em->createQuery($sql);
				$BilletsDivision = $query->getResult();

				foreach ($BilletsDivision as $division) {
					$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$division->getNameMensal()."  -  ".$division->getDueDate()->format('d-m-Y').
					"  -  ".number_format((float)$division->getActualValue(), 2, ',', '.');
				}

				$email1 = 'adm@onemilhas.com.br';
				$email2 = 'financeiro@onemilhas.com.br';
				$postfields = array(
					'content' =>    "<br><br>Bom dia,<br><br>Lembramos o(s) vencimento(s) do(s) boleto(s) com as referências abaixo. Pague em dia, evite juros.<br>Caso já tenha pago, favor desconsiderar o e-mail.".
						$data.
						"<br><br><br>Obrigado pela parceria!".
						"<br><br><br>Atenciosamente,".
						"<br>Financeiro",
					'partner' => $email,
					'bcc' => $email1.';'.$email2,
					'subject' => 'LEMBRETE VENCIMENTO BOLETO(S)',
					'from' => $email1,
					'type' => ''
				);

				if($BusinessPartner->getSubClient() == 'true') {
					$postfields['from'] = $BusinessPartner->getMasterClient()->getEmail();
				}

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result = curl_exec($ch);
				sleep(1);
			}

		} catch(\Exception $e){
			$email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR',
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

	public function dailyReminder2Days(Request $request, Response $response) {
		
		try{
			$em = Application::getInstance()->getEntityManager();

			$sql = " select DISTINCT(d.billet) as billet_id from BilletsDivision d where d.paid = 'false' and d.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' ";
			$query = $em->createQuery($sql);
			$BilletsIds = $query->getResult();

			$found = "0";
			$and = ",";

			foreach ($BilletsIds as $billet) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}

			$sql = "select DISTINCT(b.client) as client FROM Billetreceive b JOIN b.client c where ".
				" b.status = 'E' and b.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' and b.actualValue > 0 and ( c.billingPeriod = 'Diario' or c.billingPeriod = '' ) ".
				" or b.id in ( ".$found." ) ";

			$query = $em->createQuery($sql);
			$Clients = $query->getResult();

			foreach ($Clients as $client) {

				$BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['client']));
				$data = "";

				$email = $BusinessPartner->getEmail();
				if($BusinessPartner->getFinnancialEmail()) {
					$email = $BusinessPartner->getFinnancialEmail();
				}

				$sql = "select b FROM Billetreceive b where b.status = 'E' and b.actualValue > 0 and b.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' and b.client = '".$client['client']."' and b.id not in ( ".$found." ) ";
				$query = $em->createQuery($sql);
				$Billets = $query->getResult();

				foreach ($Billets as $billet) {
					$sql = "select d FROM BilletsDivision d where d.billet = '".$billet->getId()."' and d.paid = 'false' ";
					$query = $em->createQuery($sql);
					$hasDivisions = $query->getResult();

					if(count($hasDivisions) == 0) {
						$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$billet->getOurNumber()."  -  ".$billet->getDueDate()->format('d-m-Y').
						"  -  ".number_format(((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid()), 2, ',', '.');
					}
				}

				$sql = "select d FROM BilletsDivision d JOIN d.billet b where d.paid = 'false' and d.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' and b.client = '".$client['client']."' ";
				$query = $em->createQuery($sql);
				$BilletsDivision = $query->getResult();

				foreach ($BilletsDivision as $division) {
					$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$division->getName()."  -  ".$division->getDueDate()->format('d-m-Y').
					"  -  ".number_format((float)$division->getActualValue(), 2, ',', '.');
				}

				$email1 = 'adm@onemilhas.com.br';
				$email2 = 'financeiro@onemilhas.com.br';
				$postfields = array(
					'content' =>    "<br><br>Bom dia,<br><br>Lembramos o(s) vencimento(s) do(s) boleto(s) com as referências abaixo. Pague em dia, evite juros.<br>Caso já tenha pago, favor desconsiderar o e-mail.".
						$data.
						"<br><br><br>Obrigado pela parceria!".
						"<br><br><br>Atenciosamente,".
						"<br>Financeiro",
					'partner' => $email,
					'bcc' => $email1.';'.$email2,
					'from' => $email1,
					'subject' => 'LEMBRETE VENCIMENTO BOLETO(S)',
					'type' => ''
				);

				if($BusinessPartner->getSubClient() == 'true') {
					$postfields['from'] = $BusinessPartner->getMasterClient()->getEmail();
				}

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result = curl_exec($ch);
				sleep(1);
			}


			// week, month
			$sql = " select DISTINCT(b.billet) as billet_id from Billsreceive b JOIN b.billet t where b.status = 'G' and t.status = 'E' and t.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' and b.actualValue > 0 ";
			$query = $em->createQuery($sql);
			$BilletsIds = $query->getResult();

			$found = "0";
			$and = ",";

			foreach ($BilletsIds as $billet) {
				$found = $found.$and.$billet['billet_id'];
				$and = ', ';
			}

			$sql = "select DISTINCT(b.client) as client FROM Billetreceive b JOIN b.client c where ".
				" b.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' and b.actualValue > 0 and b.status = 'E' ".
				" and ( c.billingPeriod in ('Semanal', 'Quinzenal', 'Mensal') ) ".
				" and b.id in ( ".$found." ) ";

			$query = $em->createQuery($sql);
			$Clients = $query->getResult();

			foreach ($Clients as $client) {

				$BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $client['client']));
				$data = "";

				$email = $BusinessPartner->getEmail();
				if($BusinessPartner->getFinnancialEmail()) {
					$email = $BusinessPartner->getFinnancialEmail();
				}

				$sql = "select b FROM Billetreceive b where b.status = 'E' and b.actualValue > 0 and b.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' and b.client = '".$client['client']."' and b.id in ( ".$found." ) ";
				$query = $em->createQuery($sql);
				$Billets = $query->getResult();

				foreach ($Billets as $billet) {
					$sql = "select d FROM BilletsDivision d where d.billet = '".$billet->getId()."' and d.paid = 'false' ";
					$query = $em->createQuery($sql);
					$hasDivisions = $query->getResult();

					if(count($hasDivisions) == 0) {
						$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$billet->getOurNumber()."  -  ".$billet->getDueDate()->format('d-m-Y').
						"  -  ".number_format(((float)$billet->getActualValue() - (float)$billet->getAlreadyPaid()), 2, ',', '.');
					}
				}

				$sql = "select d FROM BilletsDivision d JOIN d.billet b where d.paid = 'false' and d.dueDate = '".(new \DateTime())->modify('-3 day')->format('Y-m-d')."' and b.client = '".$client['client']."' ";
				$query = $em->createQuery($sql);
				$BilletsDivision = $query->getResult();

				foreach ($BilletsDivision as $division) {
					$data = $data."<br><br>A VENCER / VENCIDO  -  ".$BusinessPartner->getName()."  -  ".$division->getNameMensal()."  -  ".$division->getDueDate()->format('d-m-Y').
					"  -  ".number_format((float)$division->getActualValue(), 2, ',', '.');
				}
				
				$email1 = 'adm@onemilhas.com.br';
				$email2 = 'financeiro@onemilhas.com.br';
				$postfields = array(
					'content' =>    "<br><br>Bom dia,<br><br>Lembramos o(s) vencimento(s) do(s) boleto(s) com as referências abaixo. Pague em dia, evite juros.<br>Caso já tenha pago, favor desconsiderar o e-mail.".
						$data.
						"<br><br><br>Obrigado pela parceria!".
						"<br><br><br>Atenciosamente,".
						"<br>Financeiro",
					'partner' => $email,
					'bcc' => $email1.';'.$email2,
					'subject' => 'LEMBRETE VENCIMENTO BOLETO(S)',
					'from' => $email1,
					'type' => ''
				);

				if($BusinessPartner->getSubClient() == 'true') {
					$postfields['from'] = $BusinessPartner->getMasterClient()->getEmail();
				}

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result = curl_exec($ch);
				sleep(1);
			}

		} catch(\Exception $e){

			$email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR',
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

	public function checkCovered() {

		try{

			$em = Application::getInstance()->getEntityManager();

			$sql = "select DISTINCT(sb.billsreceive) as billet_id FROM SaleBillsreceive sb JOIN sb.sale s JOIN sb.billsreceive b JOIN b.billet x ".
				" where s.earlyCovered = 'true' and x.status IN ('A', 'E') and x.dueDate = '".(new \DateTime())->modify('-2 day')->format('Y-m-d')."' ";
			$query = $em->createQuery($sql);
			$Billets = $query->getResult();

			if(count($Billets) > 0) {

				$found = "0";
				$and = ",";

				foreach ($Billets as $billet) {
					$found = $found.$and.$billet['billet_id'];
					$and = ', ';
				}

				$sql = "select DISTINCT(b.billet) as billet_id FROM billsreceive b where b.id in ( ".$found." ) ";
				$query = $em->createQuery($sql);
				$billsreceive = $query->getResult();

				$content = "";
				foreach ($billsreceive as $billet) {
					$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('id' => $billet['billet_id']));

					$content = $content."<br>Cliente: ".$Billetreceive->getClient()->getName()." - Numero: ".$Billetreceive->getOurNumber()." - Valor: R$".number_format($Billetreceive->getActualValue(), 2, ',', '.');
				}

				$email1 = 'financeiro@onemilhas.com.br';
				$postfields = array(
					'content' =>    "<br><br>Bom dia,<br><br>Os seguintes cliente atrasaram o pagamento de status 'Coberto' ".
						$content.
						"<br><br><br>Atenciosamente,".
						"<br>SRM-IT",
						'partner' => $email1,
					'subject' => '[ONE MILHAS] - Notificação do sistema - Atraso',
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

		} catch(\Exception $e){
			$email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR',
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

	public function checkClientsBloqued() {

		try{

			$em = Application::getInstance()->getEntityManager();

			$sql = "select DISTINCT(b.client) as client_id FROM Billetreceive b where b.status IN ('A', 'E') and b.dueDate < '".(new \DateTime())->format('Y-m-d')."' ";
			$query = $em->createQuery($sql);
			$Billets = $query->getResult();

			if(count($Billets) > 0) {

				$found = "0";
				$and = ",";

				foreach ($Billets as $billet) {
					$found = $found.$and.$billet['client_id'];
					$and = ', ';
				}

				$sql = "select b FROM Businesspartner b where b.partnerType = 'C' and b.status = 'Bloqueado' and b.id not in ( ".$found." ) ";
				$query = $em->createQuery($sql);
				$Partners = $query->getResult();

				$content = "";
				foreach ($Partners as $partner) {
					$content = $content."<br>Cliente: ".$partner->getName();
				}

				$email1 = 'adm@onemilhas.com.br';
				$email2 = 'financeiro@onemilhas.com.br';

				$postfields = array(
					'content' =>    "<br><br>Bom dia,<br><br>Os seguintes clientes estão Bloqueados sem nenhuma pendencia de financeiro ".
						$content.
						"<br><br><br>Atenciosamente,".
						"<br>SRM-IT",
					'partner' => $email2,
					'subject' => '[ONE MILHAS] - Notificação do sistema - Clientes Bloqueados',
					'from' => $email1,
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

		} catch(\Exception $e){
			$email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR CHECK CLIENTS BLOCKED',
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

	public function saveHistoricalCustomers() {
		try {
			$em = Application::getInstance()->getEntityManager();

			$sql = "select DISTINCT(b.client) as client_id FROM Billetreceive b where b.status IN ('E') and b.dueDate = '".(new \DateTime())->modify('-1 day')->format('Y-m-d')."' and b.actualValue > 0 ";
			$query = $em->createQuery($sql);
			$Billets = $query->getResult();

			foreach ($Billets as $client) {
				
				$SystemLog = new \SystemLog();
				$SystemLog->setIssueDate(new \DateTime());
				$SystemLog->setDescription("->CLIENT:".$client['client_id']."- Borderô não baixado");
				$SystemLog->setLogType('CLIENT');

				$em->persist($SystemLog);
				$em->flush($SystemLog);
			}

		} catch(\Exception $e){
			$email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR',
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
}
