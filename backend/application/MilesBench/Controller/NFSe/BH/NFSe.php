<?php

namespace MilesBench\Controller\NFSe\BH;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class NFSe {

	public function testXml(Request $request, Response $response) {
		$dados = $request->getRow();
		if (isset($dados['data'])) {
			$dados = $dados['data'];
		}

		$ListRps = $dados['ListRps'];

		$Lote = new Lote\LoteRps();
		$InfRps = new Lote\Lista\InfRps();
		$Servico = new Lote\Lista\Servico();
		$Valores = new Lote\Lista\Valores();
		$Prestador = new Lote\Lista\Prestador();
		$IntermediarioServico = new Lote\Lista\IntermediarioServico();
		$Tomador = new Lote\Lista\Tomador();
		$ConstrucaoCivil = new Lote\Lista\ConstrucaoCivil();
		$Signature = new \MilesBench\Controller\NFSe\BH\Signature\Sig();

		$Lote->setNumeroLote($dados['NumeroLote']);
		$Lote->setCnpj($dados['Cnpj']);
		$Lote->setInscricaoMunicipal($dados['InscricaoMunicipal']);
		$Lote->setQuantidadeRps(count($ListRps));


		// foreach start
		foreach ($ListRps as $rps) {

			$InfRpsData = $rps['InfRps'];
			$InfRps->setIdentificacaoFromNames(
				$InfRpsData['IdentificacaoFromNames']['Numero'],
				$InfRpsData['IdentificacaoFromNames']['Serie'],
				$InfRpsData['IdentificacaoFromNames']['Tipo']
			);
			$InfRps->setNaturezaOperacao($InfRpsData['NaturezaOperacao']);
			$InfRps->setRegimeEspecialTributacao($InfRpsData['RegimeEspecialTributacao']);
			$InfRps->setOptanteSimplesNacional($InfRpsData['OptanteSimplesNacional']);
			$InfRps->setIncentivadorCultural($InfRpsData['IncentivadorCultural']);
			$InfRps->setStatus($InfRpsData['Status']);

			$ValoresData = $rps['Valores'];
			$Valores->setValorServicos($ValoresData['ValorServicos']);
			$Valores->setValorDeducoes($ValoresData['ValorDeducoes']);
			$Valores->setValorPis($ValoresData['ValorPis']);
			$Valores->setValorCofins($ValoresData['ValorCofins']);
			$Valores->setValorInss($ValoresData['ValorInss']);
			$Valores->setValorIr($ValoresData['ValorIr']);
			$Valores->setValorCsll($ValoresData['ValorCsll']);
			$Valores->setIssRetido($ValoresData['IssRetido']);
			$Valores->setValorIss($ValoresData['ValorIss']);
			$Valores->setOutrasRetencoes($ValoresData['OutrasRetencoes']);
			$Valores->setAliquota($ValoresData['Aliquota']);
			$Valores->setDescontoIncondicionado($ValoresData['DescontoIncondicionado']);
			$Valores->setDescontoCondicionado($ValoresData['DescontoCondicionado']);

			$ServicoData = $rps['Servico'];
			$Servico->setValores($Valores);
			$Servico->setItemListaServico($ServicoData['ItemListaServico']);
			$Servico->setCodigoTributacaoMunicipio($ServicoData['CodigoTributacaoMunicipio']);
			$Servico->setDiscriminacao($ServicoData['Discriminacao']);
			$Servico->setCodigoMunicipio($ServicoData['CodigoMunicipio']);

			$InfRps->setServico($Servico);

			$PrestadorData = $rps['Prestador'];
			$Prestador->setCnpj($PrestadorData['Cnpj']);
			$Prestador->setInscricaoMunicipal($PrestadorData['InscricaoMunicipal']);

			$TomadorData = $rps['Tomador'];
			$Tomador->setRazaoSocial($TomadorData['RazaoSocial']);
			$Tomador->setIdentificacaoTomador($TomadorData['Cnpj'], $TomadorData['InscricaoMunicipal']);
			$Tomador->setEndereco(
				$TomadorData['Endereco']['rua'],
				$TomadorData['Endereco']['numero'],
				$TomadorData['Endereco']['complemento'],
				$TomadorData['Endereco']['bairro'],
				$TomadorData['Endereco']['CodigoMunicipio'],
				$TomadorData['Endereco']['estado'],
				$TomadorData['Endereco']['cep']
			);

			$IntermediarioServicoData = $rps['IntermediarioServico'];
			$IntermediarioServico->setCpfCnpj($IntermediarioServicoData['CpfCnpj']);
			$IntermediarioServico->setRazaoSocial($IntermediarioServicoData['RazaoSocial']);
			$IntermediarioServico->setInscricaoMunicipal($IntermediarioServicoData['InscricaoMunicipal']);

			$ConstrucaoCivilData = $rps['ConstrucaoCivil'];
			$ConstrucaoCivil->setCodigoObra($ConstrucaoCivilData['CodigoObra']);
			$ConstrucaoCivil->setArt($ConstrucaoCivilData['Art']);

			$InfRps->setPrestador($Prestador);
			$InfRps->setTomador($Tomador);
			$InfRps->setIntermediarioServico($IntermediarioServico);
			$InfRps->setConstrucaoCivil($ConstrucaoCivil);


			$Lote->addRps($InfRps);
		}
		// foreach end


		// xml
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd"></EnviarLoteRpsEnvio>');
		$LoteRps = $xml->addChild('LoteRps');

		$LoteRps->addChild('NumeroLote', $Lote->getNumeroLote());
		$LoteRps->addChild('Cnpj', $Lote->getCnpj());
		$LoteRps->addChild('InscricaoMunicipal', $Lote->getInscricaoMunicipal());
		$LoteRps->addChild('QuantidadeRps', $Lote->getQuantidadeRps());

		$list = $Lote->ToXml();
		$list = substr($list, 39, strlen($list));

		$LoteRps->addChild('ListaRps', $list);
		$SignatureXML = $xml->addChild('Signature');
				$SignedInfo = $SignatureXML->addChild('SignedInfo');
					$SignedInfo->addChild('CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments"');
					$SignedInfo->addChild('SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1" ');
						$Reference = $SignedInfo->addChild('Reference URI="#rps:1ABCDZ" ');
							$Transforms = $Reference->addChild('Transforms');
								$Transforms->addChild('Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"');
						$Reference->addChild('DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"');
						$Reference->addChild('DigestValue', $Signature->getDigestValue());

			$SignatureXML->addChild('SignatureValue', $Signature->getSignatureValue());

			$KeyInfo = $SignatureXML->addChild('KeyInfo');
				$X509Data = $KeyInfo->addChild('X509Data');
					$X509Data->addChild('X509Certificate', $Signature->getX509Certificate());


		$xml = $xml->asXML();
		$xml = substr($xml, 39, strlen($xml));

		$xml = str_replace("&lt;", '<', $xml);
		$xml = str_replace("&gt;", '>', $xml);

		// self::sendToEmail($xml);
		header("Content-type: text/xml");
		header('Content-Disposition: attachment; filename="test.xml"');
		echo $xml;die;
		var_dump($xml);die;
	}

	public function sendToEmail($xml) {
		$email1 = 'emissao@onemilhas.com.br';
        $email2 = 'adm@onemilhas.com.br';
		$postfields = array(
			'content' => $xml,
			'from' => $email1,
			'partner' => $email2,
			'subject' => 'XML',
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

	public function send($xml) {


		//sending xml
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::Homologacao_NFSe);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$result = curl_exec($ch);

		var_dump($result);die;
	}

}
