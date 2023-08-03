<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

/**
* @author Arthur
*/
class Miles
{
	public function sendStock() {

		try{
			$em = Application::getInstance()->getEntityManager();

			$Airlines = $em->getRepository('Airline')->findAll();

			foreach ($Airlines as $airline) {
				$sql = "select m FROM Milesbench m JOIN m.cards c where m.leftover >= 3000 and c.airline = '".$airline->getId()."' ";
				$query = $em->createQuery($sql);
				$Milesbench = $query->getResult();

				if(count($Milesbench) > 0) {

					$content = "<br>Companhia: ".$airline->getName()."<br>";
					$total = 0;
					foreach ($Milesbench as $miles) {
						$content = $content."<br>Fornecedor: ".$miles->getCards()->getBusinesspartner()->getName().", Pts: ".number_format($miles->getLeftover(), 0, ',', '.');
						$total = $total + (float)$miles->getLeftover();
					}

					$email1 = 'emissao@onemilhas.com.br';
            		$email2 = 'adm@onemilhas.com.br';
					$postfields = array(
						'content' =>    "<br><br>Bom dia,<br><br>Segue estoque de companhia:<br>".
						$content."<br>Total: ".number_format($total, 0, ',', '.').
						"<br><br><br>Atenciosamente,".
						"<br>SRM-IT",
						'from' => $email1,
						'partner' => $email2,
						'subject' => '[MMS VIAGENS] - Notificação do sistema - Atualização de estqoue - '.$airline->getName(),
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
		} catch(\Exception $e){
			$email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'from' => $email1,
				'partner' => $email2,
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

	public function searchForDivergences() {

		try{
			$em = Application::getInstance()->getEntityManager();

			$Airlines = $em->getRepository('Airline')->findAll();

			foreach ($Airlines as $airline) {
				$sql = "select m FROM Milesbench m JOIN m.cards c where m.leftover <> ".
					" ( select SUM(p.leftover) from Purchase p where p.cards = m.cards ) ".
					" and c.airline = '".$airline->getId()."' ";
				$query = $em->createQuery($sql);
				$Milesbench = $query->getResult();

				if(count($Milesbench) > 0) {

					$content = "<br>Companhia: ".$airline->getName()."<br>";
					foreach ($Milesbench as $miles) {
						$content = $content."<br>Fornecedor: ".$miles->getCards()->getBusinesspartner()->getName().", Pts: ".number_format($miles->getLeftover(), 0, ',', '.');
					}

					$email1 = 'emissao@onemilhas.com.br';
            		$email2 = 'adm@onemilhas.com.br';
					$postfields = array(
						'content' =>    "<br><br>Bom dia,<br><br>Segue Divergências:<br>".
						$content.
						"<br><br><br>Atenciosamente,".
						"<br>SRM-IT",
						'from' => $email1,
						'partner' => $email2,
						'subject' => '[MMS VIAGENS] - Notificação do sistema - Divergências - '.$airline->getName(),
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

		} catch(\Exception $e){
			$email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'from' => $email1,
				'partner' => $email2,
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

	public function vencimentos30Dias() {
		try{
			$em = Application::getInstance()->getEntityManager();
			$sql = "select p FROM Purchase p ". 
				" WHERE p.paymentMethod = 'after_use' and p.status = 'M' and p.leftover > 4000 and p.contractDueDate between '".(new \DateTime())->modify('+29 day')->format('Y-m-d')."' and '".(new \DateTime())->modify('+30 day')->format('Y-m-d')."' order by p.id ";
			$query = $em->createQuery($sql);
			$Purchases = $query->getResult();

			foreach ($Purchases as $purchase) {
				$email1 = 'onemilhas@onemilhas.com.br';
				$email2 = 'adm@onemilhas.com.br';
				$postfields = array(
					'content' => '30 Dias para final de prazo: '.$purchase->getId().'<br>Fornecedor:'.$purchase->getCards()->getBusinesspartner()->getName(),
					'partner' => $email1.';'.$email2,
					'from' => $email1,
					'subject' => '30 dias de utilizacao - pagamento apos uso',
					'type' => '',
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
		}
	}
}
