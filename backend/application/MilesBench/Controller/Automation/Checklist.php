<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Checklist
{

	public function resetChecklists() {

		try{
			$em = Application::getInstance()->getEntityManager();

			$UserChecklist = $em->getRepository('UserChecklist')->findAll();
			foreach ($UserChecklist as $note) {
				$note->setCheckDate(null);
				$note->setDone('false');

				$em->persist($note);
				$em->flush($note);
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
