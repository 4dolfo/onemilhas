<?php

namespace MilesBench\Controller\PaymentSlip;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use OpenBoleto\Banco\Bradesco;
use OpenBoleto\Agente;

use H2P\ConverterFactory;
use H2P\Converter\PhantomJS;
use H2P\TempFile;

use Aws\S3\S3Client;

include dirname(__FILE__) . '/../../../../vendor/GARB/src/Arquivo.php';

class BradescoSlipSRM {

	public function generateRemittance(Request $request, Response $response) {
		$clientes = $request->getRow();
		if(isset($clientes['businesspartner'])) {
            $UserPartner = $clientes['businesspartner'];
        }
        if (isset($clientes['data'])) {
            $clientes = $clientes['data'];
        }

		try {
			$em = Application::getInstance()->getEntityManager();
			$em->getConnection()->beginTransaction();
			$sql = "SELECT MAX(r.numeroRemessa) AS numeroRemessa FROM Remittance r";
            $query = $em->createQuery($sql);
            $numeroRemessa = $query->getResult()[0]['numeroRemessa'];
			if($numeroRemessa) {
				$numeroRemessa = (string)($numeroRemessa+1);
				while (strlen($numeroRemessa) < 6) {
					$numeroRemessa = "0".$numeroRemessa;
				}
			}
			else $numeroRemessa = "000011";
			// $numeroRemessa = "000005";

			$config['codigo_empresa'] = '5090185';
			$config['razao_social'] = 'SRM Viagens LTDA';
			$config['numero_remessa'] = $numeroRemessa;
			$config['data_gravacao'] = (new \DateTime())->format('d').(new \DateTime())->format('m').(new \DateTime())->format('y');
			
			$arquivo = new \Arquivo();
			//configurando remessa
			$arquivo->config($config);

			$QueryBuilder = Application::getInstance()->getQueryBuilder();
			$maxNumber = 0;
			$query = "select MAX(CAST(b.our_number as UNSIGNED)) as ourNumber FROM billetreceive b where concat('',b.our_number * 1) = b.our_number";
			$stmt = $QueryBuilder->query($query);
			while ($row = $stmt->fetch()) {
				$maxNumber = $row['ourNumber'];
			}

			$sql = "SELECT MAX(r.remittanceSequential) AS sequential FROM Remittance r WHERE r.issueDate = '". (new \DateTime())->format('Y-m-d') . "'";
            $query = $em->createQuery($sql);
            $variavel = $query->getResult();
			if($variavel) {
				$variavel = (strlen((string)($variavel[0]['sequential']+1)) <= 1) ? '0'.(string)($variavel[0]['sequential']+1) : (string)($variavel[0]['sequential']+1);
			} else {
				$variavel = "01";
			}

			// $total_value_remittance = 0;

			foreach ($clientes as $key => $value) {

				if($value['paymentType'] == 'Boleto' && $value['billingPeriod'] == 'Diario' && $value['billToReceive']['valueFloat'] > 20 && is_numeric($value['registrationCode']) ) {

					$Businesspartner = $em->getRepository('Businesspartner')->findOneBy(array('id' => $value['id']));

					$registrationCode = $value['registrationCode'];
					$registrationType = $value['registrationType'];
					$zipCode = $value['zipCode'];
					$zipCode = preg_replace('/\D/', '', $zipCode);
					$zipCode = str_split($zipCode, 5);
					$cep = $zipCode[0];
					$sufixo_cep = $zipCode[1];
					if(strlen($cep.$sufixo_cep) != 8){
						var_dump($cep.$sufixo_cep);
						throw new \Exception("CEP invalido do cliente: ".$value['name']);
					}
					
					$maxNumber++;

					//adicionando boleto
					$boleto['agencia'] = '3420';
					$boleto['agencia_dv'] = '7';
					$boleto['razao_conta_corrente'] = '0000';
					$boleto['carteira'] = '002';
					$boleto['conta'] = '31939';
					$boleto['conta_dv'] = '2';
					// $boleto['identificacao_empresa'] = '0 009   / 0 009 03420 0004879 8';

					// nosso numero -> bordero sequencial
					$boleto['numero_controle'] = $maxNumber;
					$boleto['habilitar_debito_compensacao'] = false;

					// multa -> bordero
					$boleto['habilitar_multa'] = true;

					$mulct = 2;
					if((float)$Businesspartner->getMulct() != 0) {
						$mulct = (float)$Businesspartner->getMulct();
					}
					$boleto['percentual_multa'] = $mulct.'00' ;

					$boleto['nosso_numero'] = $maxNumber;
					$boleto['nosso_numero_dv'] = '0';
					$boleto['desconto_dia'] = '0';
					$boleto['rateio'] = false;

					// nosso numero -> bordero sequencial
					$boleto['numero_documento'] = $maxNumber;
					// vencimento -> bordero ex: 200217 = 20/02/2017
					$boleto['vencimento'] = (new \DateTime($value['billToReceive']['due_date']))->format('dmy');//gerar com dados do banco


					// valor -> bordero ex: 200 = 2.00
					// $total_value_remittance += $value['billToReceive']['valueFloat'];
					$value['billToReceive']['actual_value'] = number_format($value['billToReceive']['valueFloat'], 2);
					$boleto['valor'] = str_replace(array('.', ','), '' , $value['billToReceive']['actual_value']);

					$boleto['data_emissao_titulo'] = (new \DateTime())->format('d').(new \DateTime())->format('m').(new \DateTime())->format('y');

					// esse é o campo que deve ser alterado
					$interest = 1;
					if((float)$Businesspartner->getInterest() != 0) {
						$interest = (float)$Businesspartner->getInterest();
					}
					$valuePerCent = $value['billToReceive']['valueFloat'] / 100;
					$boleto['valor_dia_atraso'] = str_replace(array('.', ','), '' , number_format( ( $valuePerCent * $interest ) / 30, 2, ',', '.'));

					// nao tera desconto
					$boleto['data_limite_desconto'] = (new \DateTime())->format('d').(new \DateTime())->format('m').(new \DateTime())->format('y');
					$boleto['valor_desconto'] = '0';
					$boleto['valor_iof'] = '0';
					$boleto['valor_abatimento_concedido'] = '0';

					$boleto['tipo_inscricao_pagador'] = $registrationType;//registration type
					$boleto['numero_inscricao'] = $registrationCode;//registration code

					$nome_pagador = $value['name'];
					if($Businesspartner->getBankSlipSocialName() == 'true') {
						$nome_pagador = $Businesspartner->getCompanyName();
					}

					$boleto['nome_pagador'] = $nome_pagador;//cliente

					$boleto['endereco_pagador'] = $value['adress'];//cliente
					$boleto['primeira_mensagem'] = '';
					$boleto['cep_pagador'] = $cep;//cliente
					$boleto['sufixo_cep_pagador'] = $sufixo_cep;//cliente
					$boleto['sacador_segunda_mensagem'] = '';
					//adicionando boleto
					$arquivo->add_boleto($boleto);

					//Salvar remessa
					$Remittance = new \Remittance();
					$Remittance->setAgencia($boleto['agencia']);
					$Remittance->setAgenciaDv($boleto['agencia_dv']);
					$Remittance->setRazaoContaCorrente($boleto['razao_conta_corrente']);
					$Remittance->setCarteira($boleto['carteira']);
					$Remittance->setConta($boleto['conta']);
					$Remittance->setContaDv($boleto['conta_dv']);
					$Remittance->setIdentificacaoEmpresa("");
					$Remittance->setNumeroControle($boleto['numero_controle']);
					$Remittance->setHabilitarDebitoCompensacao($boleto['habilitar_debito_compensacao']);
					$Remittance->setHabilitarMulta($boleto['habilitar_multa']);
					$Remittance->setPercentualMulta($boleto['percentual_multa']);
					$Remittance->setNossoNumero($boleto['nosso_numero']);
					$Remittance->setNossoNumeroDv($boleto['nosso_numero_dv']);
					$Remittance->setDescontoDia($boleto['desconto_dia']);
					$Remittance->setRateio($boleto['rateio']);
					$Remittance->setNumeroDocumento($boleto['numero_documento']);
					$Remittance->setVencimento($boleto['vencimento']);
					$Remittance->setValor($boleto['valor']);
					$Remittance->setDataEmissaoTitulo($boleto['data_emissao_titulo']);
					$Remittance->setValorDiaAtraso($boleto['valor_dia_atraso']);
					$Remittance->setDataLimiteDesconto($boleto['data_limite_desconto']);
					$Remittance->setValorDesconto($boleto['valor_desconto']);
					$Remittance->setValorIof($boleto['valor_iof']);
					$Remittance->setValorAbatimentoConcedido($boleto['valor_abatimento_concedido']);
					$Remittance->setTipoInscricaoPagador($boleto['tipo_inscricao_pagador']);
					$Remittance->setNumeroInscricao($boleto['numero_inscricao']);
					$Remittance->setNomePagador($boleto['nome_pagador']);
					$Remittance->setEnderecoPagador($boleto['endereco_pagador']);
					$Remittance->setPrimeiraMensagem($boleto['primeira_mensagem']);
					$Remittance->setCepPagador($boleto['cep_pagador']);
					$Remittance->setSufixoCepPagador($boleto['sufixo_cep_pagador']);
					$Remittance->setSacadorSegundaMensagem($boleto['sacador_segunda_mensagem']);
					$Remittance->setNumeroRemessa($config['numero_remessa']);
					$Remittance->setIssueDate(new \DateTime());
					$Remittance->setRemittanceSequential($variavel);
					$Remittance->setStatus('G');
					$em->persist($Remittance);
					$em->flush($Remittance);

					//Gerar boleto no banco de dados
					$Billetreceive = new \Billetreceive();
					$Billetreceive->setStatus("E");
					$Billetreceive->setOriginalValue($value['billToReceive']['valueFloat']);
					$Billetreceive->setActualValue($value['billToReceive']['valueFloat']);
					$Billetreceive->setTax($value['billToReceive']['tax']);
					$Billetreceive->setDiscount($value['billToReceive']['discount']);
					$Billetreceive->setDueDate(new \DateTime($value['billToReceive']['due_date']));
					$Billetreceive->setClient($Businesspartner);
					$Billetreceive->setDocNumber($boleto['numero_documento']);
					$Billetreceive->setOurNumber($boleto['numero_documento']);
					$Billetreceive->setIssueDate(new \DateTime());
					$Billetreceive->setAlreadyPaid($value['billToReceive']['alreadyPaid']);
					$Billetreceive->setBank('BRADESCO');
					$Billetreceive->setHasBillet("true");

					$Client = \MilesBench\Controller\ContaAzul\Sale::registerClient($Billetreceive->getClient(), $UserPartner);
					$em->persist($Client);
					$em->flush($Client);

					$em->persist($UserPartner);
					$em->flush($UserPartner);

					$dt_verificador = $this->digito_verificador_nosso_numero('02'.$this->add_zeros($boleto['nosso_numero'], 11));
					$novo_nosso_numero = $this->add_zeros($boleto['nosso_numero'].$dt_verificador, 12);
					$Billetreceive = \MilesBench\Controller\ContaAzul\Sale::createSaleByArray($Billetreceive, $UserPartner, $Client->getContaAzulId(), $novo_nosso_numero);

					$em->persist($Billetreceive);
					$em->flush($Billetreceive);

					$em->persist($UserPartner);
					$em->flush($UserPartner);

					for ($j=0; $j < count($value['bills']); $j++) { 
						$Billsreceive = $em->getRepository('Billsreceive')->findOneBy(array('id' => $value['bills'][$j]['id']));
						$Billsreceive->setStatus("E");
						$Billsreceive->setBillet($Billetreceive);

						if($value['bills'][$j]['account_type'] == 'Credito' && strpos($value['bills'][$j]['description'], 'REEMBOLSO REFERENTE AO BORDERO ') !== false) {
							$our_number = substr($value['bills'][$j]['description'], 31);
							$BilletreceiveToClose = $em->getRepository('Billetreceive')->findOneBy( array( 'ourNumber' => $our_number, 'client' => $Client->getId() ) );
							if($BilletreceiveToClose) {
								$BilletreceiveToClose->setStatus('B');
								$em->persist($BilletreceiveToClose);
								$em->flush($BilletreceiveToClose);
							}
						}

						$em->persist($Billsreceive);
						$em->flush($Billsreceive);
					}

					// sending email
					$bordero = "<br><br>Boa tarde, " . $Billetreceive->getClient()->getName() . "<br>Prezado(a), seguem emissões. Muito obrigado pela parceria! " .
						$Billetreceive->getIssueDate()->modify('-1 day')->format('d/m/Y') . "<br>" .
						"<br>Borderô:<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'>".
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

					foreach ($resp->getDataSet() as $key => $bill) {
						if($bill['checked'] == true) {

							$bordero .= "<tr><td>" . (new \DateTime($bill['issuing_date']))->format('d/m/Y')  . "</td><td>";

							if($bill['account_type'] == "Reembolso" || $bill['account_type'] == "Credito" || $bill['account_type'] == 'Credito Adiantamento') {
								$bordero .= $bill['description'] . "</td><td>";
							} else {
								$bordero .= $bill['pax_name'] . "</td><td>";
							}
							$bordero .= $bill['flightLocator'] . "</td><td>" .  $bill['from'] . "-" . $bill['to'] . "</td><td>" . $bill['airline'] . "</td>";
							
							if($bill['account_type'] == "Reembolso" || $bill['account_type'] == "Credito" || $bill['account_type'] == 'Credito Adiantamento'){
								$bordero .= "<td><font color='red'>" . number_format(-$bill['actual_value'], 2, ',', '.') . "</font></td><td>" . $bill['miles'] . "</td>";
							} else {
								$bordero .= "<td>" . number_format($bill['actual_value'], 2, ',', '.') . "</td><td>" . $bill['miles'] . "</td>";
							}

							$bordero .= "<td>" . $bill['issuing'] . "</td><td>" . $bill['client'] . "</td>";

							$bordero .= "</tr>";
						}
					}

					$bordero .= "<tr><td></td><td></td><td></td><td></td><td><b>Total:</b></td><td><b>" . number_format($Billetreceive->getActualValue(), 2, ',', '.') . "</b></td><td></td><td></td><td></td></tr>";

					$bordero .= "</table><br><br>Vencimento: " . (new \DateTime($value['billToReceive']['due_date']))->format('d/m/Y') . "<br><br>Numero boleto: " . $maxNumber . "<br> ";
					
					$email = $Billetreceive->getClient()->getEmail();
					if($Billetreceive->getClient()->getFinnancialEmail()) {
						$email = $Billetreceive->getClient()->getFinnancialEmail();
					}

					$dataBoleto = (new \DateTime());
					$dayofweek = (int)date('w', strtotime($dataBoleto->format('Y-m-d')));
					if($dayofweek == 6) {
						$dataBoleto->modify('+3 day');
					} else {
						$dataBoleto->modify('+1 day');
					}

					$bordero .= "<br><br>Seu boleto será enviado no dia " . $dataBoleto->format('d/m/Y');

					$bordero .= "<br><br><br><br>Att.<br>Financeiro One Milhas";
					
					// send grid
					$email1 = 'adm@onemilhas.com.br';
					$email2 = 'financeiro@onemilhas.com.br';
					$postfields = array(
						'content' => $bordero,
						'partner' => $email,
						'bcc' => $email1.';'.$email2,
						'from' => $email2,
						'subject' => 'BORDERO DE EMISSÃO ONE MILHAS',
						'type' => 'FINANCEIRO',
						'errorEmail' => $email2
					);
					$email1 = 'adm@onemilhas.com.br';
					$email2 = 'financeiro@onemilhas.com.br';
					if($Businesspartner->getSubClient() == 'true') {
						$postfields['from'] = $email2;
						$postfields['subject'] = 'BORDERO DE EMISSÃO ONE MILHAS';
						$postfields['partner'] = $email;
						$postfields['bcc'] = $email1.';'.$email2;
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
			}
			$fileName = "CB".(new \DateTime())->format('d').(new \DateTime())->format('m').$variavel;

			$arquivo->setFilename(getcwd().'/'.$fileName);
			$arquivo->save();

			$file_name_with_full_path = getcwd().'/'.$fileName.'.REM';

			$s3 = new \Aws\S3\S3Client([
				'version' => 'latest',
				'region'  => 'us-east-1',
				'credentials' => array(
					'key' => getenv('AWS_KEY'),
                    'secret'  => getenv('AWS_SECRET')
				)
			]);
			$bucket = 'mmsremessas';
			$keyname = 'srm/' . $fileName . '.REM';
			$result = $s3->putObject(array(
				'Bucket' => $bucket,
				'Key'    => $keyname,
				'SourceFile' => $file_name_with_full_path,
				'Body'   => '',
				'ACL'    => 'public-read'
			));

			$target_url = \MilesBench\Util::email_url.'/save';
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
			$resultEmail = curl_exec($ch);
			curl_close($ch);


			// send grid
			$email1 = 'adm@onemilhas.com.br';
			$email2 = 'financeiro@onemilhas.com.br';
			$postfields = array(
				'content' => "Ola,<br>Segue arquivo remessa para armazenamento em caso de erro!<br><br>".$result['ObjectURL'],
				'partner' => $email1.';'.$email2,
				'from' => $email1,
				'subject' => 'Remessa',
				'attachment' => $fileName.'.REM',
				'errorEmail' => $email1
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$resultEmail = curl_exec($ch);

			$em->getConnection()->commit();

			$message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro gerado com sucesso');
            $response->addMessage($message);
			$response->setDataset(
				array(
					'status' => 'success',
					'message' => 'Arquivo gerado com sucesso.',
					'fileName' => $fileName,
					'arquivo' => $arquivo->get_text(),
					'arquivo_path' => $result['ObjectURL']
				)
			);

		} catch(\Exception $e) {
			$email1 = 'adm@onemilhas.com.br';
			$em->getConnection()->rollback();
			$mailBody = "<p>".$e->getMessage()."</p>";
			$postfields = array(
				'content' => $mailBody,
				'partner' => $email1,
				'subject' => "Erro na geração do arquivo remessa - ".(new \DateTime())->format('d/m/Y'),
				'type' => ''
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);

			$message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText('Erro ao gerar o arquivo remessa, verifique seu email para saber qual foi o erro.');
            $response->addMessage($message);
			$response->setDataset(array('status' => 'error', 'message' => 'Erro ao gerar o arquivo remessa, verifique seu email para saber qual foi o erro.'));
		}
	}

	public function digito_verificador_nosso_numero($nosso_numero) {
		$modulo = self::modulo11($nosso_numero, 7);
		$digito = 11 - $modulo['resto'];
	
		$dv = '';
		if ($digito == 10) {
			// $dv = "P";
		} elseif($digito == 11) {
			$dv = 0;
		} else {
			$dv = $digito;
		}
	
		return $dv;
	}

	public function add_zeros($string, $tamanho, $posicao = 'left') {
		//contanto tamanho da string
		$qtd_value = (int) strlen($string);
		
		//verificando se existem numeros
		if($tamanho > 0 && $qtd_value <= $tamanho) {
			
			$result = '';
			$qtd_zeros = $tamanho - $qtd_value;
	
			for ($i = 0; $i < $qtd_zeros; $i++) {
				$result .= '0' ; 
			}
			
			//verificando posi��o dos zeros
			if($posicao == 'left') {
				$result = $result . $string;
			}elseif($posicao == 'right') {
				$result = $string . $result;
			}
			
			return $result;
		}else {
			return false;
		}
	}

	public static function modulo11( $num, $base=9)
	{
		$fator = 2;
		$soma  = 0;
		// Separacao dos numeros.
		for ($i = strlen($num); $i > 0; $i--) {
			//  Pega cada numero isoladamente.
			$numeros[$i] = substr($num,$i-1,1);
			//  Efetua multiplicacao do numero pelo falor.
			$parcial[$i] = $numeros[$i] * $fator;
			//  Soma dos digitos.
			$soma += $parcial[$i];
			if ($fator == $base) {
				//  Restaura fator de multiplicacao para 2.
				$fator = 1;
			}
			$fator++;
		}

		$result = array(
				'digito' => ($soma * 10) % 11,
				'resto'  => $soma % 11,
		);
		if ($result['digito'] == 10){
			$result['digito'] = 0;
		}
		return $result;
	}

	public function generateBilletFromFile($rowData) {
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
			$attachment_location = str_replace('<html lang="pt-BR">', '<html lang="pt-BR" style="zoom: 95%;">', $attachment_location);
			
			return $attachment_location;

		} catch (\Exception $e) {
			print_r($e->getMessage());die;
		}
	}
}
