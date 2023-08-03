<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Emails {

	public function load(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();
			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));

			if(!$UserSession) {
				throw new Exception("Usuario não encontrado!");
			}

			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
			if(!$Businesspartner) {
				throw new Exception("Usuario não encontrado!");
			}

			$where = ' WHERE ';
			$and = '';
			if($Businesspartner->getIsMaster() == 'true') {
				$sql = "select e FROM Emails e ";
			} else {
				$sql = "select e FROM Emails e where e.user = '".$Businesspartner->getId()."' ";
				$where = '';
				$and = ' AND ';
			}

			if(isset($dados['_dateFrom']) && $dados['_dateFrom'] != '') {
				$sql = $sql.$where.$and." e.dateToSend >= '".$dados['_dateFrom']."' ";
				$where = '';
				$and = ' AND ';
			}

			if(isset($dados['_dateTo']) && $dados['_dateTo'] != '') {
				$sql = $sql.$where.$and." e.dateToSend <= '".$dados['_dateTo']."' ";
				$where = '';
				$and = ' AND ';
			}

			if(isset($dados['status']) && $dados['status'] != '') {
				$sql = $sql.$where.$and." e.status = '".$dados['status']."' ";
				$where = '';
				$and = ' AND ';
			}

			if($where == ' WHERE ') {
				$sql = $sql." where e.status = 'PENDENTE' ";
			}

			$query = $em->createQuery($sql);
			$Emails = $query->getResult();

			$dataset = array();
			foreach($Emails as $email){
				$files = array();
				if(is_dir(getcwd()."/MilesBench/files/scheduled/".$email->getId())) {
					$path = getcwd()."/MilesBench/files/scheduled/".$email->getId();
					$scanned_directory = array_diff(scandir($path), array('..', '.'));
					foreach ($scanned_directory as $key => $value) {
						$files[] = $value;
					}
				}
				$dataset[] = array(
					'id' => $email->getId(),
					'status' => $email->getStatus(),
					'partner' => $email->getPartner(),
					'content' => $email->getContent(),
					'bcc' => $email->getBcc(),
					'subject' => $email->getSubject(),
					'type' => $email->getType(),
					'dateToSend' => $email->getDateToSend()->format('Y-m-d'),
					'user' => $email->getUser()->getName(),
					'attachments' => $files
				);
			}
			$response->setDataset($dataset);
		} catch(\Exception $e){
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function saveEmail(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['client'])) {
			$client = $dados['client'];
		}
		if(isset($dados['type'])) {
			$type = $dados['type'];
		}
		if(isset($dados['attachment'])) {
			$attachment = $dados['attachment'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			$Emails = new \Emails();
			$Emails->setStatus('PENDENTE');
			$Emails->setPartner($dados['emailpartner']);
			$Emails->setContent($dados['emailContent']);
			$Emails->setSubject($dados['subject']);
			$Emails->setBcc('');
			$Emails->setType($type);
			$Emails->setDateToSend(new \Datetime($dados['_sendDate']));
			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));
			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
			$Emails->setUser($Businesspartner);

			$em->persist($Emails);
			$em->flush($Emails);

			if(!is_dir(getcwd()."/MilesBench/files/scheduled/".$Emails->getId())) {
				mkdir(getcwd()."/MilesBench/files/scheduled/".$Emails->getId(), 0777 , true);
			}
			if(isset($attachment)) {
				foreach ($attachment as $file) {
					$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file);
					$path = getcwd()."/MilesBench/files/";
					$scanned_directory = array_diff(scandir($path."/temp"), array('..', '.'));
					foreach ($scanned_directory as $key => $value) {
						if($value === $file_name) {
							copy($path."temp/".$value, ($path."/scheduled/".$Emails->getId())."/".$value);
							unlink($path."temp/".$value);
						}
					}
				}
			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Email salvo com sucesso');
			$response->addMessage($message);
		} catch(\Exception $e){
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function removeFileScheduled(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['file'])) {
			$file = $dados['file'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			$Emails = $em->getRepository('Emails')->findOneBy(array('id' => $dados['id']));
			if($Emails) {

				if(is_dir(getcwd()."/MilesBench/files/scheduled/".$dados['id'])) {
					$path = getcwd()."/MilesBench/files/scheduled/".$dados['id'];
					unlink($path.'/'.$file);
				}

			} else {
				throw new Exception("Dado não encontrado!");
			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Status Alterado com sucesso');
			$response->addMessage($message);
		} catch(\Exception $e){
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function loadFilesSelected(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$files = array();
		if(is_dir(getcwd()."/MilesBench/files/scheduled/".$dados['id'])) {
			$path = getcwd()."/MilesBench/files/scheduled/".$dados['id'];
			$scanned_directory = array_diff(scandir($path), array('..', '.'));
			foreach ($scanned_directory as $key => $value) {
				$files[] = $value;
			}
		}
		$response->setDataset($files);
	}

	public function saveFileScheduled(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['file'])) {
			$file = $dados['file'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			if(isset($dados['id'])) {

				if(is_dir(getcwd()."/MilesBench/files/scheduled/".$dados['id'])) {
				}
				else {
					mkdir(getcwd()."/MilesBench/files/scheduled/".$dados['id'], 0777 , true);
				}
				$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
                $extension = explode('.', $file_name);
                $replace = 0;

                while (file_exists(getcwd()."/MilesBench/files/scheduled/".$dados['id']."/".$extension[0].$replace.'.'.$extension[1])) {
                    $replace++;
                }
                move_uploaded_file($file['tmp_name'],(getcwd()."/MilesBench/files/scheduled/".$dados['id']).'/'.$extension[0].$replace.'.'.$extension[1]);
			}
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Arquivo(s) salvos com sucesso!');
			$response->addMessage($message);

		} catch (Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function sendMailScheduled(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['mail'])) {
			$mail = $dados['mail'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {

			$em = Application::getInstance()->getEntityManager();

			$files = array();
			if(is_dir(getcwd()."/MilesBench/files/scheduled/".$dados['id'])) {
				$path = getcwd()."/MilesBench/files/scheduled/".$dados['id'];
				$scanned_directory = array_diff(scandir($path), array('..', '.'));
				foreach ($scanned_directory as $key => $value) {
					$files[] = $value;
					copy(getcwd()."/MilesBench/files/scheduled/".$dados['id'].'/'.$value, getcwd()."/MilesBench/files/temp/".$value);
				}
			}

			$email = new Mail();
			$req = new \MilesBench\Request\Request();
			$resp = new \MilesBench\Request\Response();
			$req->setRow(
				array('type' => $dados['type'],
					'data' => array(
						'subject' => $mail['subject'],
						'emailContent' => $mail['emailContent'],
						'emailpartner' => $mail['emailpartner']
					),
					'attachment' => $files
				)
			);
			$email->SendMail($req, $resp);

			$Emails = $em->getRepository('Emails')->findOneBy(array('id' => $dados['id']));
			$Emails->setStatus('ENVIADO');
			$em->persist($Emails);
			$em->flush($Emails);

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Arquivo(s) salvos com sucesso!');
			$response->addMessage($message);

		} catch (Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function sendAllEmails(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();
			$UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $hashId));

			if(!$UserSession) {
				throw new Exception("Usuario não encontrado!");
			}

			$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail()));
			if(!$Businesspartner) {
				throw new Exception("Usuario não encontrado!");
			}

			$sql = "select e FROM Emails e where e.user = '".$Businesspartner->getId()."' and e.status = 'PENDENTE' ";
			$query = $em->createQuery($sql);
			$Emails = $query->getResult();

			$req = new \MilesBench\Request\Request();
			$resp = new \MilesBench\Request\Response();
			$Mail = new Mail();

			foreach ($Emails as $email) {
				$files = array();
				if(is_dir(getcwd()."/MilesBench/files/scheduled/".$email->getId())) {
					$path = getcwd()."/MilesBench/files/scheduled/".$email->getId();
					$scanned_directory = array_diff(scandir($path), array('..', '.'));
					foreach ($scanned_directory as $key => $value) {
						$files[] = $value;
						copy(getcwd()."/MilesBench/files/scheduled/".$email->getId().'/'.$value, getcwd()."/MilesBench/files/temp/".$value);
					}
				}

				$req->setRow(
					array('type' => $email->getType(),
						'data' => array(
							'subject' => $email->getSubject(),
							'emailContent' => $email->getContent(),
							'emailpartner' => $email->getPartner()
						),
						'attachment' => $files
					)
				);
				$Mail->SendMail($req, $resp);
				if($resp->getMessages()[0]->getType() == 'S') {
					$email->setStatus('ENVIADO');
					$em->persist($email);
					$em->flush($email);
				}
			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Arquivo(s) salvos com sucesso!');
			$response->addMessage($message);

		} catch (Exception $e) {
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}

	public function removeEmailScheduled(Request $request, Response $response) {
		$dados = $request->getRow();
		if(isset($dados['hashId'])) {
			$hashId = $dados['hashId'];
		}
		if(isset($dados['file'])) {
			$file = $dados['file'];
		}
		if(isset($dados['data'])) {
			$dados = $dados['data'];
		}

		try {
			$em = Application::getInstance()->getEntityManager();

			$Emails = $em->getRepository('Emails')->findOneBy(array('id' => $dados['id']));
			if($Emails) {

				if(is_dir(getcwd()."/MilesBench/files/scheduled/".$dados['id'])) {
					$path = getcwd()."/MilesBench/files/scheduled/".$dados['id'];
					rmdir($path);
				}

				$em->remove($Emails);
                $em->flush($Emails);

			} else {
				throw new Exception("Dado não encontrado!");
			}

			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::SUCCESS);
			$message->setText('Removido com sucesso');
			$response->addMessage($message);
		} catch(\Exception $e){
			$message = new \MilesBench\Message();
			$message->setType(\MilesBench\Message::ERROR);
			$message->setText($e->getMessage());
			$response->addMessage($message);
		}
	}
}
