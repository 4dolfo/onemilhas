<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Precification
{

	public function checkPromo(Request $request, Response $response) {

		try{
			$em = Application::getInstance()->getEntityManager();

			$sql = " select p from PlansPromos p where p.status = 'true' and p.startDate = '".(new \DateTime())->format('Y-m-d H:i')."' ";
			$query = $em->createQuery($sql);
			$promos = $query->getResult();

			foreach ($promos as $promo) {
				\MilesBench\Controller\SalePlans::startPromotion($promo->getId());
			}

			$sql = " select p from PlansPromos p where p.status = 'true' and p.endDate = '".(new \DateTime())->format('Y-m-d H:i')."' ";
			$query = $em->createQuery($sql);
			$promos = $query->getResult();

			foreach ($promos as $promo) {
				$clients = json_decode($promo->getClients(), true);
				foreach ($clients as $key => $value) {
					\MilesBench\Controller\SalePlans::updatePrecificationByClient($value);
				}
			}

		} catch(\Exception $e){
			$email1 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' =>    "<br>".$e->getMessage()."<br><br>SRM-IT",
				'partner' => $email1,
				'subject' => 'ERROR - UPDATE ',
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
