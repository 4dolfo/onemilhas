<?php

namespace MilesBench\Controller\PaymentSlip;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use OpenBoleto\Banco\Santander;
use OpenBoleto\Agente;

use ManoelCampos\RetornoBoleto\LeituraArquivo;
use ManoelCampos\RetornoBoleto\RetornoFactory;
use ManoelCampos\RetornoBoleto\RetornoInterface;
use ManoelCampos\RetornoBoleto\LinhaArquivo;

use Aws\S3\S3Client;

require_once dirname(__FILE__) . '/../../../../vendor/spyc/Spyc.php';

class SantanderSlipSRM {

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

			$config['codigo_empresa'] = '2360864';
			$config['razao_social'] = 'SRM Viagens LTDA';
			$config['numero_remessa'] = $numeroRemessa;
			$config['data_gravacao'] = (new \DateTime())->format('d').(new \DateTime())->format('m').(new \DateTime())->format('y');

			$codigo_banco = \Cnab\Banco::BANCO_DO_BRASIL;
			$arquivo = new \Cnab\Remessa\Cnab400\Arquivo($codigo_banco);
			$arquivo->configure(array(
				'data_geracao'  => new \DateTime(),
				'data_gravacao' => new \DateTime(), 
				'numero_convenio' => '8073945',
				'numero_sequencial' => $numeroRemessa,
				'nome_fantasia' => 'SRM Viagens', // seu nome de empresa
				'razao_social'  => 'SRM VIAGENS LTDA',  // sua razão social
				'cnpj'          => '20966716000155', // seu cnpj completo
				'banco'         => $codigo_banco, //código do banco
				'logradouro'    => 'RUA JURUA',
				'numero'        => '46,',
				'bairro'        => 'Bairro Graça', 
				'cidade'        => 'Belo Horizonte',
				'uf'            => 'MG',
				'cep'           => '31140020',
				'agencia'       => '4230', 
				'agencia_dac'       => '0', 
				'conta'         => '13004595', // número da conta
				'conta_dac'     => '4', // digito da conta
				'tipo_cobranca' => '02VIN'
			));

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

				if($value['paymentType'] == 'Boleto' && $value['billingPeriod'] == 'Diario' && $value['billToReceive']['valueFloat'] > 0 && is_numeric($value['registrationCode']) ) {

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
					$boleto['agencia'] = '4230';
					$boleto['agencia_dv'] = '';
					$boleto['razao_conta_corrente'] = '0000';
					$boleto['carteira'] = '101';
					$boleto['conta'] = '13004595';
					$boleto['conta_dv'] = '4';
					// $boleto['identificacao_empresa'] = '0 101   / 0 101 04230 00013004595 8';

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
					$boleto['nome_pagador'] = $value['name'];//cliente
					$boleto['endereco_pagador'] = $value['adress'];//cliente
					$boleto['primeira_mensagem'] = '';
					$boleto['cep_pagador'] = $cep;//cliente
					$boleto['sufixo_cep_pagador'] = $sufixo_cep;//cliente
					$boleto['sacador_segunda_mensagem'] = '';

					// você pode adicionar vários boletos em uma remessa
					$arquivo->insertDetalhe(array(
						'codigo_de_ocorrencia' => 7, // 1 = Entrada de título, futuramente poderemos ter uma constante
						'nosso_numero'      => '8073945' . $this->add_zeros($maxNumber, 10),
						'numero_documento'  => $maxNumber,
						'numero_convenio'   => '8073945',
						'carteira'          => '5',
						'variacao_carteira' => '019',
						'especie'           => 1, // Você pode consultar as especies Cnab\Especie
						'valor'             => $value['billToReceive']['valueFloat'], // Valor do boleto
						'instrucao1'        => 1, // 1 = Protestar com (Prazo) dias, 2 = Devolver após (Prazo) dias, futuramente poderemos ter uma constante
						'instrucao2'        => 0, // preenchido com zeros
						'sacado_nome'       => $value['name'], // O Sacado é o cliente, preste atenção nos campos abaixo
						'sacado_tipo'       => mb_strtolower($registrationType, 'UTF-8'), //campo fixo, escreva 'cpf' (sim as letras cpf) se for pessoa fisica, cnpj se for pessoa juridica
						'sacado_cpf'        => $registrationCode,
						'sacado_cnpj'       => $registrationCode,
						'sacado_razao_social' => $value['name'],
						'sacado_logradouro' => $value['adress'],
						'sacado_bairro'     => $Businesspartner->getAdressDistrict(),
						'sacado_cep'        => $cep.$sufixo_cep, // sem hífem
						'sacado_cidade'     => $Businesspartner->getCity()->getName(),
						'sacado_uf'         => $Businesspartner->getCity()->getState(),
						'data_vencimento'   => new \DateTime($value['billToReceive']['due_date']),
						'data_cadastro'     => new \DateTime(),
						'juros_de_um_dia'     => ($valuePerCent * $interest ) / 30, // Valor do juros de 1 dia'
						'data_desconto'       => new \DateTime(),
						'valor_desconto'      => 0, // Valor do desconto
						'prazo'               => 0, // prazo de dias para o cliente pagar após o vencimento
						'taxa_de_permanencia' => '0', //00 = Acata Comissão por Dia (recomendável), 51 Acata Condições de Cadastramento na CAIXA
						'mensagem'            => 'Descrição do boleto',
						'data_multa'          => (new \DateTime($value['billToReceive']['due_date']))->modify('+1 days'), // data da multa
						'valor_multa'         => $mulct * $valuePerCent, // valor da multa
						'tipo_multa' => 'porcentagem',
						'tipo_cobranca' => '02VIN'
					));

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
					$Billetreceive->setBank('Santander');
					$Billetreceive->setHasBillet("true");

					// $Client = \MilesBench\Controller\ContaAzul\Sale::registerClient($Billetreceive->getClient(), $UserPartner);
					// $em->persist($Client);
					// $em->flush($Client);

					// $Billetreceive = \MilesBench\Controller\ContaAzul\Sale::createSaleByArray($Billetreceive, $UserPartner, $Client->getContaAzulId());

					$em->persist($Billetreceive);
					$em->flush($Billetreceive);
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
					$bordero = "<br><br>Boa tarde, " . $Billetreceive->getClient()->getName() . "<br>Segue borderô com detalhamento das emissões realizadas " .
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

					if($Businesspartner->getSubClient() == 'true') {
						$bordero .= "<br><br><br><br>Att.<br>" . $Businesspartner->getMasterClient()->getName();
					} else {
						$bordero .= "<br><br><br><br>Att.<br>Financeiro";
					}

					$origin = $Businesspartner->getOrigin();
					$subject = 'BORDERO DE EMISSÃO ONE MILHAS';
					
					$email1 = 'financeiro@onemilhas.com.br';
					$email2 = 'adm@onemilhas.com.br';
					// send grid
					$postfields = array(
						'content' => $bordero,
						'partner' => $email,
						'bcc' => $email2,
						'from' => $email1,
						'subject' => $subject,
						'type' => 'FINANCEIRO',
					);

					if($Businesspartner->getSubClient() == 'true') {
						$postfields['from'] = $Businesspartner->getMasterClient()->getFinnancialEmail();
						$postfields['subject'] = 'BORDERO DE EMISSÃO ' . $Businesspartner->getMasterClient()->getName();
						$postfields['partner'] = $email;
						$postfields['bcc'] = $email1.';'.$email2.';'.$Businesspartner->getMasterClient()->getFinnancialEmail();
					}

					// $ch = curl_init();
					// curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
					// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					// curl_setopt($ch, CURLOPT_POST, 1);
					// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
					// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					// $result = curl_exec($ch);
					// sleep(1);
				}
			}
			$fileName = "SA".(new \DateTime())->format('d').(new \DateTime())->format('m').$variavel;

			$arquivo->save($fileName . '.REM');

			$file_name_with_full_path = getcwd().'/'.$fileName.'.REM';

			$handle = fopen($file_name_with_full_path, "r");
			$writing = fopen($file_name_with_full_path.'.tmp', 'w');
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					if( strrpos($line, 'REMESSA') === false && strrpos($line, '9           ') === false) {
						$has_discount = substr($line, 157, 3);
						$discount_date = substr($line, 173, 18);
						$tipo_cobranca = '     02VIN';

						$replacing_has_discount = str_replace('100', '100', $has_discount);
						$replacing_discount_date = str_replace((new \DateTime())->format('dmy').'000000000000', '000000000000000000', $discount_date);
						$string = substr($line, 0, 97) . $tipo_cobranca . '0501' . substr($line, 111, 49) . substr($line, 159, 14) . $replacing_discount_date . substr($line, 191);

						// doing
						$string = str_replace('01300459', '08073945', $string);
						$string = '1'.substr($string, 1);
						$string = str_replace('54807394', '01300459', $string);
						$string = str_replace(' 8073945', '08073945', $string);
						$string = str_replace('       019', '0000000019', $string);
						$string = str_replace('0010000', '0330000', $string);
						$string = str_replace('         0  ', 'I54      00 ', $string);
						$string = str_replace('SRM VIAGENS                   ', substr($string, 235, 30), $string);
						$string = str_replace('01N07011901', '01N07011906', $string);
						$string = str_replace('01N100119010000000000000000', '01N10011901000000000000', $string);
						$string = substr($string, 0, 76).' 4'.substr($string, 78);

						// multa
						$multa = substr($string, 120, 6);

						$dateMulta = new \DateTime('20'.substr($multa, 4, 2).'-'.substr($multa, 2, 2).'-'.substr($multa, 0, 2));
						$string = str_replace('02VIN0', $dateMulta->modify('+1 days')->format('dmy'), $string);

						$string = substr($string, 0, 85).'000000000000'.substr($string, 98);
						$string = substr($string, 0, 110).substr($string, 45, 10).substr($string, 119);
						$string = substr($string, 0, 158).substr($string, 159);

						$line = $string;
					} else {
						$string = str_replace('001BANCO DO BRASIL', '033SANTANDER      ', $line);
						$string = str_replace('42300130045954000000', '42300807394501300459', $string);
						$line = $string;
					}
					fputs($writing, $line);
				}
				fclose($writing);
				fclose($handle);
			} else {
				// error opening the file.
			} 

			rename($file_name_with_full_path, $file_name_with_full_path.'.tmp2');
			rename($file_name_with_full_path.'.tmp', $file_name_with_full_path);

			$s3 = new \Aws\S3\S3Client([
				'version' => 'latest',
				'region'  => 'us-east-1',
				'credentials' => array(
					'key' => getenv('AWS_KEY'),
                    'secret'  => getenv('AWS_SECRET')
				)
			]);
			$bucket = 'mmsremessas';
			$keyname = 'SRM/' . $fileName . '.REM';
			$result = $s3->putObject(array(
				'Bucket' => $bucket,
				'Key'    => $keyname,
				'SourceFile' => $file_name_with_full_path,
				'Body'   => '',
				'ACL'    => 'public-read'
			));

			// $target_url = \MilesBench\Util::email_url.'/save';
			// if (function_exists('curl_file_create')) { // php 5.5+
			// 	$cFile = curl_file_create($file_name_with_full_path);
			// } else { // 
			// 	$cFile = '@' . realpath($file_name_with_full_path);
			// }
			// $post = array('file_contents' => $cFile);
			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, $target_url);
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_POST, 1);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			// $resultEmail = curl_exec($ch);
			// curl_close($ch);


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

			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_POST, 1);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			// $resultEmail = curl_exec($ch);

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
					'arquivo_path' => $result['ObjectURL']
				)
			);

		} catch(\Exception $e) {
			var_dump($e);die;
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
}
