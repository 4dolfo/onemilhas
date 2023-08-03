<?php

namespace MilesBench\Controller\PaymentSlip;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use OpenBoleto\Banco\Bradesco;
use OpenBoleto\Banco\BancoDoBrasil;
use OpenBoleto\Banco\Santander;
use OpenBoleto\Agente;

use H2P\ConverterFactory;
use H2P\Converter\PhantomJS;
use H2P\TempFile;

use ManoelCampos\RetornoBoleto\LeituraArquivo;
use ManoelCampos\RetornoBoleto\RetornoFactory;
use ManoelCampos\RetornoBoleto\RetornoInterface;
use ManoelCampos\RetornoBoleto\LinhaArquivo;

class ReturnFile {
	public $totalReceived;
	public $numberAFK;
	public $payds = [];
	public $indicativo_dc = [];
	public $carteira;

	//////////////// Bradesco
	public function readFile(Request $request, Response $response) {
		try{
			$em = Application::getInstance()->getEntityManager();
			$QueryBuilder = Application::getInstance()->getQueryBuilder();

			$dados = $request->getRow();
			$file = $dados['file'];
			
			if(is_dir(getcwd()."/MilesBench/files/temp")) {
				$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

				if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
				move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
			} else {
				mkdir(getcwd()."/MilesBench/files/temp", 0777 , true);
				$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

				if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
				move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
			}

			$this->totalReceived = 0;
			$this->numberAFK = "";
			$cod_empresa = 0;
			$salvarDados = function (RetornoInterface $retorno, LinhaArquivo $linha){
				$date = new \DateTime();
				$date->setTime(0, 0, 0);
				$em = Application::getInstance()->getEntityManager();
				if(isset($linha->dados)){
					if( isset($linha->dados) && $linha->dados['registro'] == $retorno->getIdHeaderArquivo() ) {
						$banco = $linha->dados['banco'];
						$cod_empresa = (int)$linha->dados['cod_empresa'];
					}

					if(isset($linha->dados) && $linha->dados['registro'] != $retorno->getIdHeaderArquivo() && $linha->dados["registro"] != $retorno->getIdTrailerArquivo()){

						$nosso_numero = (int)$linha->dados["num_documento"];
						$Remittance = $em->getRepository('Remittance')->findOneBy( array( 'nossoNumero' => $nosso_numero ) );
						$Billetreceive = $em->getRepository('Billetreceive')->findOneBy( array( 'ourNumber' => $nosso_numero ) );

						if($Billetreceive) {
							$Billsreceive = $em->getRepository('Billsreceive')->findBy( array( 'billet' => $Billetreceive->getId() ) );
						}

						if( $linha->dados['id_ocorrencia'] == '02' ) {
							
							if(isset($Remittance) && isset($Billetreceive)) {
								if($Remittance->getStatus() == 'G'){
									$Remittance->setStatus('E');
									$em->persist($Remittance);
									$em->flush($Remittance);
									
									if( $Billetreceive->getDueDate() < $date ) {
										// avoid send to client

										$SystemLog = new \SystemLog();
										$SystemLog->setIssueDate(new \DateTime());
										$SystemLog->setDescription("BRADESCO - Número Documento: ".$linha->dados['num_documento']." - Boleto gerado - vencimento menor que data atual - boleto nao enviado para cliente");
										$SystemLog->setLogType('BILLETLOG');
										$em->persist($SystemLog);
										$em->flush($SystemLog);
									} else {

										if($linha->dados['num_inscr_empresa'] == '20966716000155') {
											$boleto = $this->generateBilletFromFileSRM($linha->dados);
										} else {
											$boleto = $this->generateBilletFromFileMMS($linha->dados);
										}

										file_put_contents(getcwd().'/'.$nosso_numero.'.html', $boleto);
										$file_name_with_full_path = getcwd().'/'.$nosso_numero.'.html';
										$target_url = \MilesBench\Util::email_url.'/save/pdf';
										if (function_exists('curl_file_create')) { // php 5.5+
											$cFile = curl_file_create($file_name_with_full_path);
										} else { // 
											$cFile = '@' . realpath($file_name_with_full_path);
										}
										$post = array('file_contents' => $cFile);
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $target_url);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
										$result = curl_exec($ch);
										curl_close($ch);
		
										$bordero = "<br><br>Bom dia, " . $Billetreceive->getClient()->getName() . "<br>Segue boleto emissões do dia " . $Billetreceive->getIssueDate()->modify('-1 day')->format('d/m/Y') . "<br><br>Borderô:<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'>".
											"<tbody><tr bgcolor='#5D7B9D'><td colspan='10' bgcolor='#5D7B9D'><font color='#ffffff'><b>Dados</b></font></td></tr>".
											"<tr><td>Data</td><td>Passageiro</td><td>Localizador</td><td>Trecho</td><td>CIA</td><td>Valor</td><td>Milhas</td><td>Emissor</td><td>Cliente</td>";
										$bordero .= "</tr>";
		
										$bills = new \MilesBench\Controller\BillsReceive();
										$req = new \MilesBench\Request\Request();
										$resp = new \MilesBench\Request\Response();
										$req->setRow(
											array('data' => array('id' => $Billetreceive->getId())
											)
										);
										$bills->loadBilletBills($req, $resp);
		
										foreach ($resp->getDataSet() as $key => $value) {
											if($value['checked'] == true) {
		
												$bordero .= "<tr><td>" . (new \DateTime($value['issuing_date']))->format('d/m/Y')  . "</td><td>";
		
												if($value['account_type'] == "Reembolso" || $value['account_type'] == "Credito" || $value['account_type'] == 'Credito Adiantamento') {
													$bordero .= $value['description'] . "</td><td>";
												} else {
													$bordero .= $value['pax_name'] . "</td><td>";
												}
												$bordero .= $value['flightLocator'] . "</td><td>" .  $value['from'] . "-" . $value['to'] . "</td><td>" . $value['airline'] . "</td>";
												
												if($value['account_type'] == "Reembolso" || $value['account_type'] == "Credito" || $value['account_type'] == 'Credito Adiantamento'){
													$bordero .= "<td><font color='red'>" . number_format(-$value['actual_value'], 2, ',', '.') . "</font></td><td>" . $value['miles'] . "</td>";
												} else {
													$bordero .= "<td>" . number_format($value['actual_value'], 2, ',', '.') . "</td><td>" . $value['miles'] . "</td>";
												}
		
												$bordero .= "<td>" . $value['issuing'] . "</td><td>" . $value['client'] . "</td>";
		
												$bordero .= "</tr>";
											}
										}
		
										$bordero .= "<tr><td></td><td></td><td></td><td></td><td><b>Total:</b></td><td><b>" . number_format($Billetreceive->getActualValue(), 2, ',', '.') . "</b></td><td></td><td></td><td></td></tr>";
		
										$boleto = $bordero . "</table><br><br>Vencimento: " . $linha->dados['data_vencimento'] . "<br><br>Numero boleto: " . $nosso_numero . "<br> ";
										
										$email = $Billetreceive->getClient()->getEmail();
										if($Billetreceive->getClient()->getFinnancialEmail()) {
											$email = $Billetreceive->getClient()->getFinnancialEmail();
										}

										if($Billetreceive->getClient()->getSubClient() == 'true') {
											$boleto .= "<br><br><br><br>Att.<br>" . $Billetreceive->getClient()->getMasterClient()->getName();
										} else {
											$boleto .= "<br><br><br><br>Att.<br>Financeiro";
										}

										$origin = $Billetreceive->getClient()->getOrigin();
										$subject = 'BOLETO - ONE MILHAS - VENCIMENTO '.$linha->dados['data_vencimento'].' - '.$nosso_numero;

										$email1 = 'financeiro@onemilhas.com.br';
										$email2 = 'adm@onemilhas.com.br';
										$email3 = 'adm@onemilhas.com.br';
										// send grid
										$postfields = array(
											'content' => $boleto,
											'partner' => $email,
											'bcc' => $email1.';'.$email2.';'.$email3,
											'from' => $email1,
											'subject' => $subject,
											'attachment' => $nosso_numero . '.pdf'
										);

										if($Billetreceive->getClient()->getSubClient() == 'true') {
											$email1 = 'financeiro@onemilhas.com.br';
											$email2 = 'adm@onemilhas.com.br';
											$postfields['from'] = $Billetreceive->getClient()->getMasterClient()->getFinnancialEmail();
											$postfields['subject'] = 'BOLETO - '. $Billetreceive->getClient()->getMasterClient()->getName() .' - VENCIMENTO '.$linha->dados['data_vencimento'].' - '.$nosso_numero;
											$postfields['partner'] = $email;
											$postfields['bcc'] = $email1.';'.$email2.';'. $Billetreceive->getClient()->getMasterClient()->getFinnancialEmail();
										}

										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
										$result = curl_exec($ch);
										sleep(2);
		
										$SystemLog = new \SystemLog();
										$SystemLog->setIssueDate(new \DateTime());
										$SystemLog->setDescription("BRADESCO - Número Documento: ".$linha->dados['num_documento']." - Boleto gerado e enviado para o cliente");
										$SystemLog->setLogType('BILLETLOG');
										$em->persist($SystemLog);
										$em->flush($SystemLog);
									}
								}
							}

						} else if ( $linha->dados['id_ocorrencia'] == '06' ) {

							if(isset($Remittance) && isset($Billetreceive)) {
								if($Remittance->getStatus() == 'E') {
									$Remittance->setStatus('B');
									$Billetreceive->setPaymentDate((new \DateTime())->modify('-1 day'));
									$Billetreceive->setAlreadyPaid((float)$Billetreceive->getAlreadyPaid() + (float) $linha->dados['valor_recebido']);
									$Billetreceive->setStatus('B');

									foreach ($Billsreceive as $bill) {
										$bill->setStatus('B');
										$em->persist($bill);
										$em->flush($bill);
									}

									$SystemLog = new \SystemLog();
									$SystemLog->setIssueDate(new \DateTime());
									$SystemLog->setDescription("BRADESCO - Número Documento: ".$linha->dados['num_documento']." - Baixado no sistema");
									$SystemLog->setLogType('BILLETLOG');
									$em->persist($SystemLog);
									$em->flush($SystemLog);
									$em->persist($Remittance);
									$em->flush($Remittance);
									$em->persist($Billetreceive);
									$em->flush($Billetreceive);
								}
							} else if(!isset($Remittance) && isset($Billetreceive) && isset($Billsreceive)) {
								if($Billetreceive->getStatus() != 'B') {
									$Billetreceive->setStatus('B');
									$Billetreceive->setPaymentDate((new \DateTime())->modify('-1 day'));
									$Billetreceive->setAlreadyPaid((float)$Billetreceive->getAlreadyPaid() + (float) $linha->dados['valor_recebido']);
									$em->persist($Billetreceive);
									$em->flush($Billetreceive);

									foreach ($Billsreceive as $bill) {
										$bill->setStatus('B');
										$em->persist($bill);
										$em->flush($bill);
									}

									$SystemLog = new \SystemLog();
									$SystemLog->setIssueDate(new \DateTime());
									$SystemLog->setDescription("BRADESCO - Número Documento: ".$linha->dados['num_documento']." - Boleto sem arquivo remessa, baixado no sistema");
									$SystemLog->setLogType('BILLETLOG');
									$em->persist($SystemLog);
									$em->flush($SystemLog);
								}
							}

							if(!isset( $this->indicativo_dc[$linha->dados['indicativo_dc']] )) {
								$this->indicativo_dc[$linha->dados['indicativo_dc']] = 0;
								$this->payds[$linha->dados['indicativo_dc']] = [];
							}
							$this->indicativo_dc[$linha->dados['indicativo_dc']] += $linha->dados['valor_recebido'];

							$this->payds[$linha->dados['indicativo_dc']][$linha->dados['nosso_numero']] = [ 'valor_recebido' => $linha->dados['valor_recebido'], 'valor_titulo' => $linha->dados['valor_titulo'], 'indicativo_dc' => $linha->dados['indicativo_dc'], 'num_documento' => $linha->dados['num_documento'], 'data_pagamento' => $linha->dados['data_pagamento'] ];
							$this->totalReceived += $linha->dados['valor_recebido'];
							$this->numberAFK = $linha->dados['id_empresa_banco'];
							$this->carteira = $linha->dados['carteira'];

						} else if ( $linha->dados['id_ocorrencia'] == '10' ) {

							$SystemLog = new \SystemLog();
							$SystemLog->setIssueDate(new \DateTime());
							$SystemLog->setDescription("BRADESCO - Número Documento: " . $nosso_numero . " - Ocorrencia detectada - id: " . $linha->dados['id_ocorrencia'] . " - Verificar");
							$SystemLog->setLogType('BILLETLOG');
							$em->persist($SystemLog);
							$em->flush($SystemLog);
						} else {

							$SystemLog = new \SystemLog();
							$SystemLog->setIssueDate(new \DateTime());
							$SystemLog->setDescription("BRADESCO - Número Documento: " . $nosso_numero . " - id: " . $linha->dados['id_ocorrencia'] . " Evento não identificada");
							$SystemLog->setLogType('BILLETLOG');
							$em->persist($SystemLog);
							$em->flush($SystemLog);

						}
					}
				}
			};

			$fileName = "./MilesBench/files/temp/".$file_name;
			$cnab400 = RetornoFactory::getRetorno($fileName);
			
			$leitura = new LeituraArquivo($salvarDados, $cnab400);
			$leitura->lerArquivoRetorno();
		} catch (\Exception $e) {
			$SystemLog = new \SystemLog();
			$SystemLog->setIssueDate(new \DateTime());
			$SystemLog->setDescription("-> ERRO: ".$e->getMessage());
			$SystemLog->setLogType('BILLETLOG');
			$em->persist($SystemLog);
			$em->flush($SystemLog);
		}

		$mailBody = "";
		$SystemLogs = array();

		$sql = "SELECT COUNT(*) as quant FROM system_log as sl WHERE DATE_FORMAT(sl.issue_date, '%Y-%m-%d') = CURDATE() AND log_type = 'BILLETLOG' and description like '%Boleto gerado e enviado para o cliente%' ORDER BY sl.id;";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$mailBody .= "<p><b>Quantidade de boletos registrados: ".$row['quant']."</b></p>";
		}

		$sql = "SELECT * FROM system_log as sl WHERE DATE_FORMAT(sl.issue_date, '%Y-%m-%d') = CURDATE() AND log_type = 'BILLETLOG' ORDER BY sl.id;";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$mailBody .= "<p>".$row['description']."</p>";
		}

		if($mailBody != "") {
			$email1 = 'financeiro@onemilhas.com.br';
			$email2 = 'adm@onemilhas.com.br';
			$postfields = array(
				'content' => $mailBody,
				'partner' => $email1.';'.$email2,
				'subject' => "Log de geração de boletos - " . ( new \DateTime() )->format( 'd/m/Y' ),
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

		if(count($this->payds) != 0) {
			$mailBody = "id: ".$this->numberAFK."<br>Carteira: ".$this->carteira."<br>Valor Total: R$".number_format($this->totalReceived, 2, ',', '.')."<br>";
			$csv = [['Num Titulo', 'Num Bordero', 'dt pagamento', 'Ordem', 'Recebido', 'Titulo', 'Juros/Multa']];

			foreach ($this->payds as $doc => $array) {
				$mailBody .= "<br><p>Valor Total: " . number_format($this->indicativo_dc[$doc], 2, ',', '.') . "</p>";
				$csv[] = ['Valor Total', number_format($this->indicativo_dc[$doc], 2, ',', '.')];

				foreach ($array as $key => $value) {
					$csv[] = [
						$key, (int)$value['num_documento'], $value['data_pagamento'], $value['indicativo_dc'], number_format($value['valor_recebido'], 2, ',', '.'), number_format($value['valor_titulo'], 2, ',', '.'), ( $value['valor_recebido'] != $value['valor_titulo'] ? number_format(($value['valor_recebido'] - $value['valor_titulo']), 2, ',', '.') : ' ' )
					];
				}
			}

			$fp = fopen('recebimentos.csv', 'w');
			foreach ($csv as $fields) {
				fputcsv($fp, $fields, ';');
			}
			fclose($fp);

			$file_name_with_full_path = getcwd().'/recebimentos.csv';
			$target_url = \MilesBench\Util::email_url.'/save';
			if (function_exists('curl_file_create')) { 
				$cFile = curl_file_create($file_name_with_full_path);
			} else { // 
				$cFile = '@' . realpath($file_name_with_full_path);
			}
			$post = array('file_contents' => $cFile);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $target_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			$result = curl_exec($ch);
			curl_close($ch);

			$email1 = 'adm@onemilhas.com.br';
			$email2 = 'financeiro@onemilhas.com.br';
			$email3 = 'adm@onemilhas.com.br';

			$postfields = array(
				'content' => $mailBody,
				'partner' => $email1.';'.$email2.';'.$email3,
				'subject' => "Log de recebimentos - ".$this->numberAFK." - " . ( new \DateTime() )->format( 'd/m/Y' ),
				'from' => $email1,
				'type' => '',
				'attachment' => 'recebimentos.csv'
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);
		}

		$message = new \MilesBench\Message();
		$message->setType(\MilesBench\Message::SUCCESS);
		$message->setText('Arquivo salvo com sucesso. Você receberá um email com a lista dos boletos que foram gerados.');
		$response->addMessage($message);
	}

	public function generateBilletFromFileSRM($rowData) {
		$em = Application::getInstance()->getEntityManager();

		$num_documento = (int)$rowData['num_documento'];
		$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('ourNumber' => $num_documento));
		$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Billetreceive->getClient()->getId()));
		$City = $em->getRepository('City')->findOneBy(array('id' => $Businesspartner->getCity()));

		$sacado = new Agente($Businesspartner->getName(), $Businesspartner->getRegistrationCode(), $Businesspartner->getAdress(), $Businesspartner->getZipCode(), $City->getName(), $City->getState());
		$cedente = new Agente('SRM VIAGENS LTDA', '20.966.716/0001-55', 'Andar G1 Sala 8', '31140-020', 'Belo Horizonte', 'MG');

		try {
			$data = explode('/', $rowData['data_vencimento']);
			$boleto = new Bradesco(array(

				// Parâmetros obrigatórios
				'dataVencimento' => (new \DateTime($data[2].'-'.$data[1].'-'.$data[0])),
				'valor' => $rowData['valor_titulo'],
				'nosso_numero' => $rowData['nosso_numero'],
				'sequencial' => $rowData['num_documento'],
				'sacado' => $sacado,
				'cedente' => $cedente,
				'agencia' => 3420,
				'carteira' => 2,
				'conta' => 31939,

				// Parâmetros recomendáveis
				'agenciaDv' => 7,
				'contaDv' => 2,
				'descricaoDemonstrativo' => array(
					'Compra de passagem(s) aéreas'
				),
				'instrucoes' => array(
					''
				),

				// Parâmetros opcionais

				//'resourcePath' => '../resources',
				//'cip' => '000', // Apenas para o Bradesco
				//'moeda' => Bradesco::MOEDA_REAL,
				'dataDocumento' => (new \DateTime()),
				'dataProcessamento' => (new \DateTime())
				//'contraApresentacao' => true,
				//'pagamentoMinimo' => 23.00,
				//'aceite' => 'N',
				//'especieDoc' => 'ABC',
				//'numeroDocumento' => '123.456.789',
				//'usoBanco' => 'Uso banco',
				//'layout' => 'layout.phtml',
				//'descontosAbatimentos' => 123.12,
				//'moraMulta' => 123.12,
				//'outrasDeducoes' => 123.12,
				//'outrosAcrescimos' => 123.12,
				//'valorCobrado' => 123.12,
				//'valorUnitario' => 123.12,
				//'quantidade' => 1,
			));

			$attachment_location = $boleto->getOutput();

			$attachment_location = str_replace("Sacado", "Pagador", $attachment_location);
			$attachment_location = str_replace("Cedente", "Beneficiário", $attachment_location);
			$attachment_location = str_replace('<html lang="pt-BR">', '<html lang="pt-BR" style="zoom: 70%;">', $attachment_location);
			
			return $attachment_location;

		} catch (\Exception $e) {
			print_r($e->getMessage());die;
		}
	}

	public function generateBilletFromFileMMS($rowData) {
		$em = Application::getInstance()->getEntityManager();

		$num_documento = (int)$rowData['num_documento'];
		$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('ourNumber' => $num_documento));
		$Remittance = $em->getRepository('Remittance')->findOneBy( array( 'nossoNumero' => $num_documento ) );
		$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Billetreceive->getClient()->getId()));
		$City = $em->getRepository('City')->findOneBy(array('id' => $Businesspartner->getCity()));

		$sacado = new Agente($Businesspartner->getName(), $Businesspartner->getRegistrationCode(), $Businesspartner->getAdress(), $Businesspartner->getZipCode(), $City->getName(), $City->getState());
		$cedente = new Agente('MMS VIAGENS LTDA', '29.632.355/0001-85', 'Andar G1 Sala 8', '31140-020', 'Belo Horizonte', 'MG');

		try {
			$data = explode('/', $rowData['data_vencimento']);
			$boleto = new Bradesco(array(

				// Parâmetros obrigatórios
				'dataVencimento' => (new \DateTime($data[2].'-'.$data[1].'-'.$data[0])),
				'valor' => $rowData['valor_titulo'],
				'nosso_numero' => $rowData['nosso_numero'],
				'sequencial' => $rowData['num_documento'],
				'sacado' => $sacado,
				'cedente' => $cedente,
				'agencia' => 3420,
				'carteira' => 9,
				'conta' => 29429,

				// Parâmetros recomendáveis
				'agenciaDv' => 7,
				'contaDv' => 2,
				'descricaoDemonstrativo' => array(
					'Compra de passagem(s) aéreas'
				),
				'instrucoes' => array(
					'**VALORES EXPRESSOS EM REAIS**',
					'JUROS POR DIA DE ATRASO......... ' . substr($Remittance->getValorDiaAtraso(), -3, 1) . ',' . substr($Remittance->getValorDiaAtraso(), -2),
					'APOS ' . $rowData['data_vencimento'] . ' MULTA .........' . (float)substr($Remittance->getPercentualMulta(), 0, 1) * ( $rowData['valor_titulo'] / 100 )
				),

				// Parâmetros opcionais

				//'resourcePath' => '../resources',
				//'cip' => '000', // Apenas para o Bradesco
				//'moeda' => Bradesco::MOEDA_REAL,
				'dataDocumento' => (new \DateTime()),
				'dataProcessamento' => (new \DateTime())
				//'contraApresentacao' => true,
				//'pagamentoMinimo' => 23.00,
				//'aceite' => 'N',
				//'especieDoc' => 'ABC',
				//'numeroDocumento' => '123.456.789',
				//'usoBanco' => 'Uso banco',
				//'layout' => 'layout.phtml',
				//'descontosAbatimentos' => 123.12,
				//'moraMulta' => 123.12,
				//'outrasDeducoes' => 123.12,
				//'outrosAcrescimos' => 123.12,
				//'valorCobrado' => 123.12,
				//'valorUnitario' => 123.12,
				//'quantidade' => 1,
			));

			$attachment_location = $boleto->getOutput();

			$attachment_location = str_replace("Sacado", "Pagador", $attachment_location);
			$attachment_location = str_replace("Cedente", "Beneficiário", $attachment_location);
			$attachment_location = str_replace('<html lang="pt-BR">', '<html lang="pt-BR" style="zoom: 70%;">', $attachment_location);
			
			return $attachment_location;

		} catch (\Exception $e) {
			print_r($e->getMessage());die;
		}
	}

	///////////////// BBrasil
	public function readFileBB(Request $request, Response $response) {
		try{
			$em = Application::getInstance()->getEntityManager();
			$QueryBuilder = Application::getInstance()->getQueryBuilder();

			$dados = $request->getRow();
			$file = $dados['file'];
			
			if(is_dir(getcwd()."/MilesBench/files/temp")) {
				$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

				if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
				move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
			} else {
				mkdir(getcwd()."/MilesBench/files/temp", 0777 , true);
				$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

				if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
				move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
			}
			
			$this->totalReceived = 0;
			$this->numberAFK = "";
			$salvarDados = function (RetornoInterface $retorno, LinhaArquivo $linha){
				$date = new \DateTime();
				$date->setTime(0, 0, 0);
				$em = Application::getInstance()->getEntityManager();
				if(isset($linha->dados)){
					if(isset($linha->dados) && $linha->dados['registro'] != $retorno->getIdHeaderArquivo() && $linha->dados["registro"] != $retorno->getIdTrailerArquivo()){

						$nosso_numero = (int)$linha->dados["num_titulo"];
						$Remittance = $em->getRepository('Remittance')->findOneBy( array( 'nossoNumero' => $nosso_numero ) );
						$Billetreceive = $em->getRepository('Billetreceive')->findOneBy( array( 'ourNumber' => $nosso_numero ) );

						if($Billetreceive) {
							$Billsreceive = $em->getRepository('Billsreceive')->findBy( array( 'billet' => $Billetreceive->getId() ) );
						}

						if( $linha->dados['comando'] == '02' ) {

							if(isset($Remittance) && isset($Billetreceive)) {
								if($Remittance->getStatus() == 'G'){
									$Remittance->setStatus('E');
									$em->persist($Remittance);
									$em->flush($Remittance);

									if( $Billetreceive->getDueDate() < $date ) {
										// avoid send to client

										$SystemLog = new \SystemLog();
										$SystemLog->setIssueDate(new \DateTime());
										$SystemLog->setDescription("BBRASIL - Número Documento: ".$linha->dados['num_titulo']." - Boleto gerado - vencimento menor que data atual - boleto nao enviado para cliente");
										$SystemLog->setLogType('BILLETLOG');
										$em->persist($SystemLog);
										$em->flush($SystemLog);
									} else {

										if($linha->dados['convenio'] == '2679188') {
											$boleto = $this->generateBilletFromFileBBSRM($linha->dados);
										} else {
											$boleto = $this->generateBilletFromFileBBMMS($linha->dados);
										}
		
										file_put_contents(getcwd().'/'.$nosso_numero.'.html', $boleto);
										$file_name_with_full_path = getcwd().'/'.$nosso_numero.'.html';
										$target_url = \MilesBench\Util::email_url.'/save/pdf';
										if (function_exists('curl_file_create')) { // php 5.5+
											$cFile = curl_file_create($file_name_with_full_path);
										} else { // 
											$cFile = '@' . realpath($file_name_with_full_path);
										}
										$post = array('file_contents' => $cFile);
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $target_url);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
										$result = curl_exec($ch);
										curl_close($ch);
		
										$bordero = "<br><br>Bom dia, " . $Billetreceive->getClient()->getName() . "<br>Segue boleto emissões do dia " . $Billetreceive->getIssueDate()->modify('-1 day')->format('d/m/Y') . "<br><br>Borderô:<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'>".
											"<tbody><tr bgcolor='#5D7B9D'><td colspan='10' bgcolor='#5D7B9D'><font color='#ffffff'><b>Dados</b></font></td></tr>".
											"<tr><td>Data</td><td>Passageiro</td><td>Localizador</td><td>Trecho</td><td>CIA</td><td>Valor</td><td>Milhas</td><td>Emissor</td><td>Cliente</td>";
		
										$bordero .= "</tr>";
		
										$bills = new \MilesBench\Controller\BillsReceive();
										$req = new \MilesBench\Request\Request();
										$resp = new \MilesBench\Request\Response();
										$req->setRow(
											array('data' => array('id' => $Billetreceive->getId())
											)
										);
										$bills->loadBilletBills($req, $resp);
		
										foreach ($resp->getDataSet() as $key => $value) {
											if($value['checked'] == true) {
		
												$bordero .= "<tr><td>" . (new \DateTime($value['issuing_date']))->format('d/m/Y')  . "</td><td>";
		
												if($value['account_type'] == "Reembolso" || $value['account_type'] == "Credito" || $value['account_type'] == 'Credito Adiantamento') {
													$bordero .= $value['description'] . "</td><td>";
												} else {
													$bordero .= $value['pax_name'] . "</td><td>";
												}
												$bordero .= $value['flightLocator'] . "</td><td>" .  $value['from'] . "-" . $value['to'] . "</td><td>" . $value['airline'] . "</td>";
												
												if($value['account_type'] == "Reembolso" || $value['account_type'] == "Credito" || $value['account_type'] == 'Credito Adiantamento'){
													$bordero .= "<td><font color='red'>" . number_format(-$value['actual_value'], 2, ',', '.') . "</font></td><td>" . $value['miles'] . "</td>";
												} else {
													$bordero .= "<td>" . number_format($value['actual_value'], 2, ',', '.') . "</td><td>" . $value['miles'] . "</td>";
												}
		
												$bordero .= "<td>" . $value['issuing'] . "</td><td>" . $value['client'] . "</td>";
		
												$bordero .= "</tr>";
											}
										}
		
										$bordero .= "<tr><td></td><td></td><td></td><td></td><td><b>Total:</b></td><td><b>" . number_format($Billetreceive->getActualValue(), 2, ',', '.') . "</b></td><td></td><td></td><td></td></tr>";
		
										$boleto = $bordero . "</table><br><br>Vencimento: " . $Billetreceive->getDueDate()->format('d/m/Y')  . "<br><br>Numero boleto: " . $nosso_numero . "<br> ";
										
										$email = $Billetreceive->getClient()->getEmail();
										if($Billetreceive->getClient()->getFinnancialEmail()) {
											$email = $Billetreceive->getClient()->getFinnancialEmail();
										}

										$boleto .= "<br><br><br><br>Att.<br>Financeiro";

										$origin = $Billetreceive->getClient()->getOrigin();
										$subject = 'BOLETO - ONE MILHAS - VENCIMENTO '.$Billetreceive->getDueDate()->format('d/m/Y') .' - '.$nosso_numero;

										$email1 = 'financeiro@onemilhas.com.br';
										$email2 = 'adm@onemilhas.com.br';
										$email3 = 'adm@onemilhas.com.br';
										// send grid
										$postfields = array(
											'content' => $boleto,
											'partner' => $email,
											'bcc' => $email1.';'.$email2.';'.$email3,
											'from' => $email1,
											'subject' => $subject,
											'attachment' => $nosso_numero . '.pdf'
										);
										$email1 = 'financeiro@onemilhas.com.br';
										$email2 = 'adm@onemilhas.com.br';
										if($Billetreceive->getClient()->getSubClient() == 'true') {
											$postfields['from'] = $Billetreceive->getClient()->getMasterClient()->getFinnancialEmail();
											$postfields['subject'] = 'BOLETO - '. $Billetreceive->getClient()->getMasterClient()->getName() .' - VENCIMENTO '.$Billetreceive->getDueDate()->format('d/m/Y') .' - '.$nosso_numero;
											$postfields['partner'] = $email;
											$postfields['bcc'] = $email1.';'.$email2.';'. $Billetreceive->getClient()->getMasterClient()->getFinnancialEmail();
										}

										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
										$result = curl_exec($ch);
										sleep(2);
		
										$SystemLog = new \SystemLog();
										$SystemLog->setIssueDate(new \DateTime());
										$SystemLog->setDescription("BBRASIL - Número Documento: ".$nosso_numero." - Boleto gerado e enviado para o cliente");
										$SystemLog->setLogType('BILLETLOG');
										$em->persist($SystemLog);
										$em->flush($SystemLog);
									}
								}
							}

						} else if ( $linha->dados['comando'] == '06' ) {

							if(isset($Remittance) && isset($Billetreceive)) {
								if($Remittance->getStatus() == 'E') {
									$Remittance->setStatus('B');
									$Billetreceive->setPaymentDate((new \DateTime())->modify('-1 day'));
									$Billetreceive->setAlreadyPaid((float)$Billetreceive->getAlreadyPaid() + (float)$linha->dados['valor_pagamento']);
									$Billetreceive->setStatus('B');

									foreach ($Billsreceive as $bill) {
										$bill->setStatus('B');
										$em->persist($bill);
										$em->flush($bill);
									}

									$SystemLog = new \SystemLog();
									$SystemLog->setIssueDate(new \DateTime());
									$SystemLog->setDescription("BBRASIL - Número Documento: ".$linha->dados['num_titulo']." - Baixado no sistema");
									$SystemLog->setLogType('BILLETLOG');
									$em->persist($SystemLog);
									$em->flush($SystemLog);
									$em->persist($Remittance);
									$em->flush($Remittance);
									$em->persist($Billetreceive);
									$em->flush($Billetreceive);
								}
							} else if(!isset($Remittance) && isset($Billetreceive) && isset($Billsreceive)) {
								if($Billetreceive->getStatus() != 'B') {
									$Billetreceive->setStatus('B');
									$Billetreceive->setPaymentDate((new \DateTime())->modify('-1 day'));
									$Billetreceive->setAlreadyPaid((float)$Billetreceive->getAlreadyPaid() + (float)$linha->dados['valor_pagamento']);
									$em->persist($Billetreceive);
									$em->flush($Billetreceive);

									foreach ($Billsreceive as $bill) {
										$bill->setStatus('B');
										$em->persist($bill);
										$em->flush($bill);
									}

									$SystemLog = new \SystemLog();
									$SystemLog->setIssueDate(new \DateTime());
									$SystemLog->setDescription("BBRASIL - Número Documento: ".$linha->dados['num_titulo']." - Boleto sem arquivo remessa, baixado no sistema");
									$SystemLog->setLogType('BILLETLOG');
									$em->persist($SystemLog);
									$em->flush($SystemLog);
								}
							}

							if(!isset( $this->indicativo_dc[$linha->dados['indicativo_dc']] )) {
								$this->indicativo_dc[$linha->dados['indicativo_dc']] = 0;
								$this->payds[$linha->dados['indicativo_dc']] = [];
							}
							$this->indicativo_dc[$linha->dados['indicativo_dc']] += $linha->dados['valor_pagamento'];

							$this->payds[$linha->dados['indicativo_dc']][$linha->dados['nosso_numero']] = [ 'valor_pagamento' => $linha->dados['valor_pagamento'], 'valor_titulo' => $linha->dados['valor_titulo'], 'indicativo_dc' => $linha->dados['indicativo_dc'], 'num_titulo' => $linha->dados['num_titulo'], 'data_pagamento' => $linha->dados['data_pagamento'] ];
							$this->totalReceived += $linha->dados['valor_pagamento'];
							//$this->numberAFK = $linha->dados['id_empresa_banco'];
							$this->carteira = $linha->dados['carteira'];

						} else if ( $linha->dados['comando'] == '10' ) {

							$SystemLog = new \SystemLog();
							$SystemLog->setIssueDate(new \DateTime());
							$SystemLog->setDescription("BBRASIL - Número Documento: " . $nosso_numero . " - Ocorrencia detectada - id: " . $linha->dados['id_ocorrencia'] . " - Verificar");
							$SystemLog->setLogType('BILLETLOG');
							$em->persist($SystemLog);
							$em->flush($SystemLog);
						} else {

							$SystemLog = new \SystemLog();
							$SystemLog->setIssueDate(new \DateTime());
							$SystemLog->setDescription("BBRASIL - Número Documento: " . $nosso_numero . " - id: " . $linha->dados['id_ocorrencia'] . " Evento não identificada");
							$SystemLog->setLogType('BILLETLOG');
							$em->persist($SystemLog);
							$em->flush($SystemLog);

						}
					}
				}
			};

			$fileName = "./MilesBench/files/temp/".$file_name;
			$cnab400 = RetornoFactory::getRetorno($fileName);
			
			$leitura = new LeituraArquivo($salvarDados, $cnab400);
			$leitura->lerArquivoRetorno();
		} catch (\Exception $e) {
			$SystemLog = new \SystemLog();
			$SystemLog->setIssueDate(new \DateTime());
			$SystemLog->setDescription("-> ERRO: ".$e->getMessage());
			$SystemLog->setLogType('BILLETLOG');
			$em->persist($SystemLog);
			$em->flush($SystemLog);
		}

		$mailBody = "";
		$SystemLogs = array();

		$sql = "SELECT COUNT(*) as quant FROM system_log as sl WHERE DATE_FORMAT(sl.issue_date, '%Y-%m-%d') = CURDATE() AND log_type = 'BILLETLOG' and description like '%Boleto gerado e enviado para o cliente%' ORDER BY sl.id;";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$mailBody .= "<p><b>Quantidade de boletos registrados: ".$row['quant']."</b></p>";
		}

		$sql = "SELECT * FROM system_log as sl WHERE DATE_FORMAT(sl.issue_date, '%Y-%m-%d') = CURDATE() AND log_type = 'BILLETLOG' ORDER BY sl.id;";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$mailBody .= "<p>".$row['description']."</p>";
		}

		if($mailBody != "") {
			$email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$email3 = 'financeiro@onemilhas.com.br';
			$postfields = array(
				'content' => $mailBody,
				'from' => $email1,
				'partner' => $email2.';'.$email3,
				'subject' => "Log de geração de boletos - " . ( new \DateTime() )->format( 'd/m/Y' ),
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

		if(count($this->payds) != 0) {
			$mailBody = "<br>Carteira: ".$this->carteira."<br>Valor Total: R$".number_format($this->totalReceived, 2, ',', '.')."<br>";
			$csv = [['Num Titulo', 'Num Bordero', 'dt pagamento', 'Ordem', 'Recebido', 'Titulo', 'Juros/Multa']];

			foreach ($this->payds as $doc => $array) {
				$mailBody .= "<br><p>Valor Total: " . number_format($this->indicativo_dc[$doc], 2, ',', '.') . "</p>";
				$csv[] = ['Valor Total', number_format($this->indicativo_dc[$doc], 2, ',', '.')];

				foreach ($array as $key => $value) {
					$csv[] = [
						$key, (int)$value['num_titulo'], $value['data_pagamento'], $value['indicativo_dc'], number_format($value['valor_pagamento'], 2, ',', '.'), number_format($value['valor_titulo'], 2, ',', '.'), ( $value['valor_pagamento'] != $value['valor_titulo'] ? number_format(($value['valor_pagamento'] - $value['valor_titulo']), 2, ',', '.') : ' ' )
					];
				}
			}

			$fp = fopen('recebimentos.csv', 'w');
			foreach ($csv as $fields) {
				fputcsv($fp, $fields, ';');
			}
			fclose($fp);

			$file_name_with_full_path = getcwd().'/recebimentos.csv';
			$target_url = \MilesBench\Util::email_url.'/save';
			if (function_exists('curl_file_create')) { 
				$cFile = curl_file_create($file_name_with_full_path);
			} else { // 
				$cFile = '@' . realpath($file_name_with_full_path);
			}
			$post = array('file_contents' => $cFile);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $target_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			$result = curl_exec($ch);
			curl_close($ch);

			$email1 = 'adm@onemilhas.com.br';
			$email2 = 'financeiro@onemilhas.com.br';
			$email3 = 'adm@onemilhas.com.br';

			$postfields = array(
				'content' => $mailBody,
				'partner' => $email2.';'.$email3,
				'subject' => "Log de recebimentos - ".$this->numberAFK." - " . ( new \DateTime() )->format( 'd/m/Y' ),
				'from' => $email1,
				'type' => '',
				'attachment' => 'recebimentos.csv'
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);
		}

		$message = new \MilesBench\Message();
		$message->setType(\MilesBench\Message::SUCCESS);
		$message->setText('Arquivo salvo com sucesso. Você receberá um email com a lista dos boletos que foram gerados.');
		$response->addMessage($message);
	}

	public function generateBilletFromFileBBMMS($rowData) {
		$em = Application::getInstance()->getEntityManager();

		$num_documento = (int)$rowData['num_titulo'];
		$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('ourNumber' => $num_documento));
		$Remittance = $em->getRepository('Remittance')->findOneBy( array( 'nossoNumero' => $num_documento ) );
		$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Billetreceive->getClient()->getId()));
		$City = $em->getRepository('City')->findOneBy(array('id' => $Businesspartner->getCity()));

		$sacado = new Agente($Businesspartner->getName(), $Businesspartner->getRegistrationCode(), $Businesspartner->getAdress(), $Businesspartner->getZipCode(), $City->getName(), $City->getState());
		$cedente = new Agente('MMS VIAGENS LTDA', '29.632.355/0001-85', 'Av. Raja Gabaglia, 2000', '30494-170', 'Belo Horizonte', 'MG');

		try {
			$boleto = new BancoDoBrasil(array(

				// Parâmetros obrigatórios
				'dataVencimento' => (new \DateTime(  '20' . substr($rowData['data_vencimento'], 4, 2) .'-'.substr($rowData['data_vencimento'], 2, 2).'-'. substr($rowData['data_vencimento'], 0, 2) )),
				'valor' => $rowData['valor_titulo'],
				'nosso_numero' => $num_documento,
				'sequencial' => $num_documento,
				'sacado' => $sacado,
				'cedente' => $cedente,
				'agencia' => 1614,
				'carteira' => 17,
				'conta' => 400000,
				'agenciaDv' => 4,
				'contaDv' => 5,
				'descricaoDemonstrativo' => array(
					'Compra de passagem(s) aéreas'
				),
				'instrucoes' => array(
					'**VALORES EXPRESSOS EM REAIS**',
					'JUROS POR DIA DE ATRASO......... ' . substr($Remittance->getValorDiaAtraso(), -3, 1) . ',' . substr($Remittance->getValorDiaAtraso(), -2),
					'APOS ' . $rowData['data_vencimento'] . ' MULTA .........' . (float)substr($Remittance->getPercentualMulta(), 0, 1) * ( $rowData['valor_titulo'] / 100 ),
					'Sujeito a negativação junto aos orgães SPC/SERASA após 5 dias do vencimento.',
					'Pagavel em qualquer banco mesmo após o vencimento.'
				),
				'convenio' => 3094413,
				'moeda' => BancoDoBrasil::MOEDA_REAL,
				'numeroDocumento' => $num_documento,

				// Parâmetros opcionais
				//'resourcePath' => '../resources',
				//'cip' => '000', // Apenas para o Bradesco
				//'moeda' => Bradesco::MOEDA_REAL,
				'dataDocumento' => (new \DateTime()),
				'dataProcessamento' => (new \DateTime()),
				//'contraApresentacao' => true,
				'localPagamento' => 'Pagavel em qualquer banco mesmo após o vencimento.',
				'especieDoc' => 'DM'
				//'pagamentoMinimo' => 23.00,
				//'aceite' => 'N',
				//'especieDoc' => 'ABC',
				//'numeroDocumento' => '123.456.789',
				//'usoBanco' => 'Uso banco',
				//'layout' => 'layout.phtml',
				//'descontosAbatimentos' => 123.12,
				//'moraMulta' => 123.12,
				//'outrasDeducoes' => 123.12,
				//'outrosAcrescimos' => 123.12,
				//'valorCobrado' => 123.12,
				//'valorUnitario' => 123.12,
				//'quantidade' => 1,
			));

			$attachment_location = $boleto->getOutput();

			$attachment_location = str_replace("Sacado", "Pagador", $attachment_location);
			$attachment_location = str_replace("Cedente", "Beneficiário", $attachment_location);
			$attachment_location = str_replace('<html lang="pt-BR">', '<html lang="pt-BR" style="zoom: 70%;">', $attachment_location);
			
			return $attachment_location;

		} catch (\Exception $e) {
			print_r($e->getMessage());die;
		}
	}

	public function generateBilletFromFileBBSRM($rowData) {
		$em = Application::getInstance()->getEntityManager();

		$num_documento = (int)$rowData['num_titulo'];
		$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('ourNumber' => $num_documento));
		$Remittance = $em->getRepository('Remittance')->findOneBy( array( 'nossoNumero' => $num_documento ) );
		$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $Billetreceive->getClient()->getId()));
		$City = $em->getRepository('City')->findOneBy(array('id' => $Businesspartner->getCity()));

		$sacado = new Agente($Businesspartner->getName(), $Businesspartner->getRegistrationCode(), $Businesspartner->getAdress(), $Businesspartner->getZipCode(), $City->getName(), $City->getState());
		$cedente = new Agente('SRM VIAGENS LTDA', '20.966.716/0001-55', 'Andar G1 Sala 8', '31140-020', 'Belo Horizonte', 'MG');

		try {
			$boleto = new BancoDoBrasil(array(

				// Parâmetros obrigatórios
				'dataVencimento' => (new \DateTime(  '20' . substr($rowData['data_vencimento'], 4, 2) .'-'.substr($rowData['data_vencimento'], 2, 2).'-'. substr($rowData['data_vencimento'], 0, 2) )),
				'valor' => $rowData['valor_titulo'],
				'nosso_numero' => $num_documento,
				'sequencial' => $num_documento,
				'sacado' => $sacado,
				'cedente' => $cedente,
				'agencia' => 1614,
				'carteira' => 17,
				'conta' => 15750,
				'agenciaDv' => 4,
				'contaDv' => 3,
				'descricaoDemonstrativo' => array(
					'Compra de passagem(s) aéreas'
				),
				'instrucoes' => array(
					'**VALORES EXPRESSOS EM REAIS**',
					'JUROS POR DIA DE ATRASO......... ' . substr($Remittance->getValorDiaAtraso(), -3, 1) . ',' . substr($Remittance->getValorDiaAtraso(), -2),
					'APOS ' . $rowData['data_vencimento'] . ' MULTA .........' . (float)substr($Remittance->getPercentualMulta(), 0, 1) * ( $rowData['valor_titulo'] / 100 ),
					'Sujeito a negativação junto aos orgães SPC/SERASA após 5 dias do vencimento.',
					'Pagavel em qualquer banco mesmo após o vencimento.'
				),
				'convenio' => 2679188,
				'moeda' => BancoDoBrasil::MOEDA_REAL,
				'numeroDocumento' => $num_documento,

				// Parâmetros opcionais
				//'resourcePath' => '../resources',
				//'cip' => '000', // Apenas para o Bradesco
				//'moeda' => Bradesco::MOEDA_REAL,
				'dataDocumento' => (new \DateTime()),
				'dataProcessamento' => (new \DateTime()),
				//'contraApresentacao' => true,
				'localPagamento' => 'Pagavel em qualquer banco mesmo após o vencimento.',
				'especieDoc' => 'DM'
				//'pagamentoMinimo' => 23.00,
				//'aceite' => 'N',
				//'especieDoc' => 'ABC',
				//'numeroDocumento' => '123.456.789',
				//'usoBanco' => 'Uso banco',
				//'layout' => 'layout.phtml',
				//'descontosAbatimentos' => 123.12,
				//'moraMulta' => 123.12,
				//'outrasDeducoes' => 123.12,
				//'outrosAcrescimos' => 123.12,
				//'valorCobrado' => 123.12,
				//'valorUnitario' => 123.12,
				//'quantidade' => 1,
			));

			$attachment_location = $boleto->getOutput();

			$attachment_location = str_replace("Sacado", "Pagador", $attachment_location);
			$attachment_location = str_replace("Cedente", "Beneficiário", $attachment_location);
			$attachment_location = str_replace('<html lang="pt-BR">', '<html lang="pt-BR" style="zoom: 70%;">', $attachment_location);
			
			return $attachment_location;

		} catch (\Exception $e) {
			print_r($e->getMessage());die;
		}
	}

	///////////////// Santander
	public function readFileSA(Request $request, Response $response) {
		try{
			$em = Application::getInstance()->getEntityManager();
			$QueryBuilder = Application::getInstance()->getQueryBuilder();

			$dados = $request->getRow();
			$file = $dados['file'];

			if(is_dir(getcwd()."/MilesBench/files/temp")) {
				$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

				if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
				move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
			} else {
				mkdir(getcwd()."/MilesBench/files/temp", 0777 , true);
				$file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);

				if(file_exists(getcwd()."/MilesBench/files/temp/".$file_name)) unlink(getcwd()."/MilesBench/files/temp/".$file_name);
				move_uploaded_file($file['tmp_name'],getcwd()."/MilesBench/files/temp/".$file_name);
			}

			$salvarDados = function (RetornoInterface $retorno, LinhaArquivo $linha){
				$date = new \DateTime();
				$date->setTime(0, 0, 0);
				$em = Application::getInstance()->getEntityManager();
				if(isset($linha->dados)){
					if(isset($linha->dados) && $linha->dados['registro'] != $retorno->getIdHeaderArquivo() && $linha->dados["registro"] != $retorno->getIdTrailerArquivo()){

						$nosso_numero = (int)$linha->dados["num_titulo"];
						$Remittance = $em->getRepository('Remittance')->findOneBy( array( 'nossoNumero' => $nosso_numero ) );
						$Billetreceive = $em->getRepository('Billetreceive')->findOneBy( array( 'ourNumber' => $nosso_numero ) );

						if($Billetreceive) {
							$Billsreceive = $em->getRepository('Billsreceive')->findBy( array( 'billet' => $Billetreceive->getId() ) );
						}

						if( $linha->dados['comando'] == '02' ) {

							if(isset($Remittance) && isset($Billetreceive)) {
								// if($Remittance->getStatus() == 'G'){
									$Remittance->setStatus('E');
									$em->persist($Remittance);
									$em->flush($Remittance);

									// if( $Billetreceive->getDueDate() < $date ) {
									if( false ) {
										// avoid send to client

										$SystemLog = new \SystemLog();
										$SystemLog->setIssueDate(new \DateTime());
										$SystemLog->setDescription("SANTANDER - Número Documento: ".$linha->dados['num_titulo']." - Boleto gerado - vencimento menor que data atual - boleto nao enviado para cliente");
										$SystemLog->setLogType('BILLETLOG');
										$em->persist($SystemLog);
										$em->flush($SystemLog);
									} else {

										$boleto = $this->generateBilletFromFileSASRM($linha->dados);
		
										file_put_contents(getcwd().'/'.$nosso_numero.'.html', $boleto);
										$file_name_with_full_path = getcwd().'/'.$nosso_numero.'.html';
										$target_url = \MilesBench\Util::email_url.'/save/pdf';
										if (function_exists('curl_file_create')) { // php 5.5+
											$cFile = curl_file_create($file_name_with_full_path);
										} else { // 
											$cFile = '@' . realpath($file_name_with_full_path);
										}
										$post = array('file_contents' => $cFile);
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $target_url);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
										$result = curl_exec($ch);
										curl_close($ch);
		
										$bordero = "<br><br>Bom dia, " . $Billetreceive->getClient()->getName() . "<br>Segue boleto emissões do dia " . $Billetreceive->getIssueDate()->modify('-1 day')->format('d/m/Y') . "<br><br>Borderô:<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'>".
											"<tbody><tr bgcolor='#5D7B9D'><td colspan='10' bgcolor='#5D7B9D'><font color='#ffffff'><b>Dados</b></font></td></tr>".
											"<tr><td>Data</td><td>Passageiro</td><td>Localizador</td><td>Trecho</td><td>CIA</td><td>Valor</td><td>Milhas</td><td>Emissor</td><td>Cliente</td>";
		
										$bordero .= "</tr>";
		
										$bills = new \MilesBench\Controller\BillsReceive();
										$req = new \MilesBench\Request\Request();
										$resp = new \MilesBench\Request\Response();
										$req->setRow(
											array('data' => array('id' => $Billetreceive->getId())
											)
										);
										$bills->loadBilletBills($req, $resp);
		
										foreach ($resp->getDataSet() as $key => $value) {
											if($value['checked'] == true) {
		
												$bordero .= "<tr><td>" . (new \DateTime($value['issuing_date']))->format('d/m/Y')  . "</td><td>";
		
												if($value['account_type'] == "Reembolso" || $value['account_type'] == "Credito" || $value['account_type'] == 'Credito Adiantamento') {
													$bordero .= $value['description'] . "</td><td>";
												} else {
													$bordero .= $value['pax_name'] . "</td><td>";
												}
												$bordero .= $value['flightLocator'] . "</td><td>" .  $value['from'] . "-" . $value['to'] . "</td><td>" . $value['airline'] . "</td>";
												
												if($value['account_type'] == "Reembolso" || $value['account_type'] == "Credito" || $value['account_type'] == 'Credito Adiantamento'){
													$bordero .= "<td><font color='red'>" . number_format(-$value['actual_value'], 2, ',', '.') . "</font></td><td>" . $value['miles'] . "</td>";
												} else {
													$bordero .= "<td>" . number_format($value['actual_value'], 2, ',', '.') . "</td><td>" . $value['miles'] . "</td>";
												}
		
												$bordero .= "<td>" . $value['issuing'] . "</td><td>" . $value['client'] . "</td>";
		
												$bordero .= "</tr>";
											}
										}
		
										$bordero .= "<tr><td></td><td></td><td></td><td></td><td><b>Total:</b></td><td><b>" . number_format($Billetreceive->getActualValue(), 2, ',', '.') . "</b></td><td></td><td></td><td></td></tr>";
		
										$boleto = $bordero . "</table><br><br>Vencimento: " . $Billetreceive->getDueDate()->format('d/m/Y')  . "<br><br>Numero boleto: " . $nosso_numero . "<br> ";
										
										$email = $Billetreceive->getClient()->getEmail();
										if($Billetreceive->getClient()->getFinnancialEmail()) {
											$email = $Billetreceive->getClient()->getFinnancialEmail();
										}

										$boleto .= "<br><br><br><br>Att.<br>Financeiro";

										$origin = $Billetreceive->getClient()->getOrigin();
										$subject = 'BOLETO - ONE MILHAS - VENCIMENTO '.$Billetreceive->getDueDate()->format('d/m/Y') .' - '.$nosso_numero;

										$email1 = 'financeiro@onemilhas.com.br';
										$email2 = 'adm@onemilhas.com.br';
										$email3 = 'adm@onemilhas.com.br';
										// send grid
										$postfields = array(
											'content' => $boleto,
											'partner' => $email,
											'bcc' => $email1.';'.$email2.';'.$email3,
											'from' => $email1,
											'subject' => $subject,
											'attachment' => $nosso_numero . '.pdf'
										);

										$postfields['partner'] = 'adm@onemilhas.com.br';

										if($Billetreceive->getClient()->getSubClient() == 'true') {
											$email1 = 'financeiro@onemilhas.com.br';
											$email2 = 'adm@onemilhas.com.br';
											$postfields['from'] = $Billetreceive->getClient()->getMasterClient()->getFinnancialEmail();
											$postfields['subject'] = 'BOLETO - '. $Billetreceive->getClient()->getMasterClient()->getName() .' - VENCIMENTO '.$Billetreceive->getDueDate()->format('d/m/Y') .' - '.$nosso_numero;
											$postfields['partner'] = $email;
											$postfields['bcc'] = $email1.';'.$email2.';'. $Billetreceive->getClient()->getMasterClient()->getFinnancialEmail();
										}

										// $ch = curl_init();
										// curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
										// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										// curl_setopt($ch, CURLOPT_POST, 1);
										// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
										// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
										// $result = curl_exec($ch);
										// sleep(2);
		
										$SystemLog = new \SystemLog();
										$SystemLog->setIssueDate(new \DateTime());
										$SystemLog->setDescription("SANTANDER - Número Documento: ".$nosso_numero." - Boleto gerado e enviado para o cliente");
										$SystemLog->setLogType('BILLETLOG');
										$em->persist($SystemLog);
										$em->flush($SystemLog);
									}
								// }
							}

						} else if ( $linha->dados['comando'] == '06' ) {

							if(isset($Remittance) && isset($Billetreceive)) {
								if($Remittance->getStatus() == 'E') {
									$Remittance->setStatus('B');
									$Billetreceive->setPaymentDate((new \DateTime())->modify('-1 day'));
									$Billetreceive->setAlreadyPaid((float)$Billetreceive->getAlreadyPaid() + (float)$linha->dados['valor_pagamento']);
									$Billetreceive->setStatus('B');

									foreach ($Billsreceive as $bill) {
										$bill->setStatus('B');
										$em->persist($bill);
										$em->flush($bill);
									}

									$SystemLog = new \SystemLog();
									$SystemLog->setIssueDate(new \DateTime());
									$SystemLog->setDescription("SANTANDER - Número Documento: ".$linha->dados['num_titulo']." - Baixado no sistema");
									$SystemLog->setLogType('BILLETLOG');
									$em->persist($SystemLog);
									$em->flush($SystemLog);
									$em->persist($Remittance);
									$em->flush($Remittance);
									$em->persist($Billetreceive);
									$em->flush($Billetreceive);
								}
							} else if(!isset($Remittance) && isset($Billetreceive) && isset($Billsreceive)) {
								if($Billetreceive->getStatus() != 'B') {
									$Billetreceive->setStatus('B');
									$Billetreceive->setPaymentDate((new \DateTime())->modify('-1 day'));
									$Billetreceive->setAlreadyPaid((float)$Billetreceive->getAlreadyPaid() + (float)$linha->dados['valor_pagamento']);
									$em->persist($Billetreceive);
									$em->flush($Billetreceive);

									foreach ($Billsreceive as $bill) {
										$bill->setStatus('B');
										$em->persist($bill);
										$em->flush($bill);
									}

									$SystemLog = new \SystemLog();
									$SystemLog->setIssueDate(new \DateTime());
									$SystemLog->setDescription("SANTANDER - Número Documento: ".$linha->dados['num_titulo']." - Boleto sem arquivo remessa, baixado no sistema");
									$SystemLog->setLogType('BILLETLOG');
									$em->persist($SystemLog);
									$em->flush($SystemLog);
								}
							}

						} else if ( $linha->dados['comando'] == '10' ) {

							$SystemLog = new \SystemLog();
							$SystemLog->setIssueDate(new \DateTime());
							$SystemLog->setDescription("SANTANDER - Número Documento: " . $nosso_numero . " - Ocorrencia detectada - id: " . $linha->dados['id_ocorrencia'] . " - Verificar");
							$SystemLog->setLogType('BILLETLOG');
							$em->persist($SystemLog);
							$em->flush($SystemLog);
						} else {

							$SystemLog = new \SystemLog();
							$SystemLog->setIssueDate(new \DateTime());
							$SystemLog->setDescription("SANTANDER - Número Documento: " . $nosso_numero . " - id: " . $linha->dados['id_ocorrencia'] . " Evento não identificada");
							$SystemLog->setLogType('BILLETLOG');
							$em->persist($SystemLog);
							$em->flush($SystemLog);

						}
					}
				}
			};

			$fileName = "./MilesBench/files/temp/".$file_name;
			$cnab400 = RetornoFactory::getRetorno($fileName);
			
			$leitura = new LeituraArquivo($salvarDados, $cnab400);
			$leitura->lerArquivoRetorno();
		} catch (\Exception $e) {
			var_dump($e);die;
			$SystemLog = new \SystemLog();
			$SystemLog->setIssueDate(new \DateTime());
			$SystemLog->setDescription("-> ERRO: ".$e->getMessage());
			$SystemLog->setLogType('BILLETLOG');
			$em->persist($SystemLog);
			$em->flush($SystemLog);
		}
		var_dump('asd');die;

		$mailBody = "";
		$SystemLogs = array();

		$sql = "SELECT COUNT(*) as quant FROM system_log as sl WHERE DATE_FORMAT(sl.issue_date, '%Y-%m-%d') = CURDATE() AND log_type = 'BILLETLOG' and description like '%Boleto gerado e enviado para o cliente%' ORDER BY sl.id;";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$mailBody .= "<p><b>Quantidade de boletos registrados: ".$row['quant']."</b></p>";
		}

		$sql = "SELECT * FROM system_log as sl WHERE DATE_FORMAT(sl.issue_date, '%Y-%m-%d') = CURDATE() AND log_type = 'BILLETLOG' ORDER BY sl.id;";
		$stmt = $QueryBuilder->query($sql);
		while ($row = $stmt->fetch()) {
			$mailBody .= "<p>".$row['description']."</p>";
		}

		if($mailBody != "") {
			$email1 = 'emissao@onemilhas.com.br';
            $email2 = 'adm@onemilhas.com.br';
			$email3 = 'financeiro@onemilhas.com.br';
			$postfields = array(
				'content' => $mailBody,
				'from' => $email1,
				'partner' => $email2.';'.$email3,
				'subject' => "Log de geração de boletos - " . ( new \DateTime() )->format( 'd/m/Y' ),
				'type' => ''
			);
			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_POST, 1);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			// $result = curl_exec($ch);
		}

		$message = new \MilesBench\Message();
		$message->setType(\MilesBench\Message::SUCCESS);
		$message->setText('Arquivo salvo com sucesso. Você receberá um email com a lista dos boletos que foram gerados.');
		$response->addMessage($message);
	}

	public function generateBilletFromFileSASRM($rowData) {
		$em = Application::getInstance()->getEntityManager();

		$num_documento = (int)$rowData['num_titulo'];
		$Billetreceive = $em->getRepository('Billetreceive')->findOneBy(array('ourNumber' => $num_documento));
		$Remittance = $em->getRepository('Remittance')->findOneBy( array( 'nossoNumero' => $num_documento ) );
		$Businesspartner = $Billetreceive->getClient();
		$City = $Businesspartner->getCity();

		$sacado = new Agente($Businesspartner->getName(), $Businesspartner->getRegistrationCode(), $Businesspartner->getAdress(), $Businesspartner->getZipCode(), $City->getName(), $City->getState());
		$cedente = new Agente('SRM VIAGENS LTDA', '20.966.716/0001-55', 'Andar G1 Sala 8', '31140-020', 'Belo Horizonte', 'MG');

		// removere
		// $num_documento = '47623';
		// $num_documento = '47624';
		// $num_documento = '47625';
		// $num_documento = '47626';
		// $num_documento = '47627';
		// $num_documento = '47628';

		try {
			$boleto = new Santander(array(

				// Parâmetros obrigatórios
				'dataVencimento' => (new \DateTime(  '20' . substr($rowData['data_vencimento'], 4, 2) .'-'.substr($rowData['data_vencimento'], 2, 2).'-'. substr($rowData['data_vencimento'], 0, 2) )),
				'valor' => $rowData['valor_titulo'],
				'nosso_numero' => $num_documento,
				'sequencial' => $num_documento,
				'sacado' => $sacado,
				'cedente' => $cedente,
				'agencia' => 4230,
				'carteira' => 101,
				'conta' => 13004595,
				// 'agenciaDv' => 4,
				'contaDv' => 4,
				'descricaoDemonstrativo' => array(
					'Compra de passagem(s) aéreas'
				),
				'instrucoes' => array(
					'Pagável em qualquer banco até o vencimento.'
				),
				'convenio' => 8073945,
				'moeda' => Santander::MOEDA_REAL,
				'numeroDocumento' => $num_documento,

				// Parâmetros opcionais
				//'resourcePath' => '../resources',
				//'cip' => '000', // Apenas para o Bradesco
				//'moeda' => Bradesco::MOEDA_REAL,
				'dataDocumento' => (new \DateTime()),
				'dataProcessamento' => (new \DateTime()),
				//'contraApresentacao' => true,
				'localPagamento' => 'Pagável em qualquer banco até o vencimento.',
				'especieDoc' => 'DM'
				//'pagamentoMinimo' => 23.00,
				//'aceite' => 'N',
				//'especieDoc' => 'ABC',
				//'numeroDocumento' => '123.456.789',
				//'usoBanco' => 'Uso banco',
				//'layout' => 'layout.phtml',
				//'descontosAbatimentos' => 123.12,
				//'moraMulta' => 123.12,
				//'outrasDeducoes' => 123.12,
				//'outrosAcrescimos' => 123.12,
				//'valorCobrado' => 123.12,
				//'valorUnitario' => 123.12,
				//'quantidade' => 1,
			));

			$attachment_location = $boleto->getOutput();

			$attachment_location = str_replace("Sacado", "Pagador", $attachment_location);
			$attachment_location = str_replace("Cedente", "Beneficiário", $attachment_location);
			$attachment_location = str_replace('<html lang="pt-BR">', '<html lang="pt-BR" style="zoom: 70%;">', $attachment_location);
			
			return $attachment_location;

		} catch (\Exception $e) {
			print_r($e->getMessage());die;
		}
	}
}
