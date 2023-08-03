<?php

namespace MilesBench\Controller\NFSe\BH\Lote;

class LoteRps {

	private $NumeroLote;
	private $Cnpj;
	private $InscricaoMunicipal;
	private $QuantidadeRps;
	private $Rps = array();

	public function getNumeroLote() {
		return $this->NumeroLote;
	}

	public function setNumeroLote($numero) {
		$this->NumeroLote = $numero;
	}


	public function getCnpj() {
		return $this->Cnpj;
	}

	public function setCnpj($cnpj) {
		$this->Cnpj = $cnpj;
	}


	public function getInscricaoMunicipal() {
		return $this->InscricaoMunicipal;
	}

	public function setInscricaoMunicipal($inscricao) {
		$this->InscricaoMunicipal = $inscricao;
	}


	public function getQuantidadeRps() {
		return $this->QuantidadeRps;
	}

	public function setQuantidadeRps($quantidade) {
		$this->QuantidadeRps = $quantidade;
	}


	public function getRps() {
		return $this->Rps;
	}

	public function addRps($rps) {
		$this->Rps[] = $rps;
	}

	public function ToXml() {
		$xml = '';
		$Signature = new \MilesBench\Controller\NFSe\BH\Signature\Sig();

		foreach ($this->Rps as $value) {

			$Rps = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><Rps></Rps>');

			$text = $this->RpsToXml($value);
			$text = substr($text, 39, strlen($text));

			$Rps->addChild('InfRps', $text);

			$SignatureXML = $Rps->addChild('Signature');
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

var_dump($Rps->asXML());die;

			$xml = $xml.$Rps->asXML();
		}

		$xml = str_replace("&lt;", '<', $xml);
		$xml = str_replace("&gt;", '>', $xml);
		return $xml;
	}

	public function RpsToXml($rps) {

		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><InfRps></InfRps>');

		$IdentificacaoRps = $xml->addChild('IdentificacaoRps');

			$IdentificacaoRps->addChild('Numero', $rps->getIdentificacaoRps()['Numero']);
			$IdentificacaoRps->addChild('Serie', $rps->getIdentificacaoRps()['Serie']);
			$IdentificacaoRps->addChild('Tipo', $rps->getIdentificacaoRps()['Tipo']);

		$xml->addChild('DataEmissao', (new \DateTime())->format('Y-m-d H:i:s'));
		$xml->addChild('NaturezaOperacao', $rps->getNaturezaOperacao());

		$xml->addChild('NaturezaOperacao', $rps->getNaturezaOperacao());
		$xml->addChild('RegimeEspecialTributacao', $rps->getRegimeEspecialTributacao());
		$xml->addChild('OptanteSimplesNacional', $rps->getOptanteSimplesNacional());
		$xml->addChild('IncentivadorCultural', $rps->getIncentivadorCultural());
		$xml->addChild('Status', $rps->getStatus());

		$Servico = $xml->addChild('Servico');
		$servicos = $rps->getServico();

			$Servico->addChild('ItemListaServico', $servicos->getItemListaServico());
			$Servico->addChild('CodigoTributacaoMunicipio', $servicos->getCodigoTributacaoMunicipio());
			$Servico->addChild('Discriminacao', $servicos->getDiscriminacao());
			$Servico->addChild('CodigoMunicipio', $servicos->getCodigoMunicipio());

		$Valores = $Servico->addChild('Valores');
		$values = $servicos->getValores();

			$Valores->addChild('ValorServicos', $values->getValorServicos());
			$Valores->addChild('ValorDeducoes', $values->getValorDeducoes());
			$Valores->addChild('ValorPis', $values->getValorPis());
			$Valores->addChild('ValorCofins', $values->getValorCofins());
			$Valores->addChild('ValorInss', $values->getValorInss());
			$Valores->addChild('ValorIr', $values->getValorIr());
			$Valores->addChild('ValorCsll', $values->getValorCsll());
			$Valores->addChild('IssRetido', $values->getIssRetido());
			$Valores->addChild('ValorIss', $values->getValorIss());
			$Valores->addChild('OutrasRetencoes', $values->getOutrasRetencoes());
			$Valores->addChild('Aliquota', $values->getAliquota());
			$Valores->addChild('DescontoIncondicionado', $values->getDescontoIncondicionado());
			$Valores->addChild('DescontoCondicionado', $values->getDescontoCondicionado());


		$Prestador = $xml->addChild('Prestador');
		$partner = $rps->getPrestador();
			$Prestador->addChild('Cnpj', $partner->getCnpj());
			$Prestador->addChild('InscricaoMunicipal', $partner->getInscricaoMunicipal());


		$Tomador = $xml->addChild('Tomador');
		$partner = $rps->getTomador();
			$IdentificacaoTomador = $Tomador->addChild('IdentificacaoTomador');
				$CpfCnpj = $IdentificacaoTomador->addChild('CpfCnpj');
					$CpfCnpj->addChild('Cnpj', $partner->getIdentificacaoTomador()['CpfCnpj']['Cnpj']);
			$IdentificacaoTomador->addChild('InscricaoMunicipal', $partner->getIdentificacaoTomador()['CpfCnpj']['InscricaoMunicipal']);

			$Tomador->addChild('RazaoSocial', $partner->getRazaoSocial());

			$Endereco = $Tomador->addChild('Endereco');
				$Endereco->addChild('Endereco', $partner->getEndereco()['Endereco']);
				$Endereco->addChild('Numero', $partner->getEndereco()['Numero']);
				$Endereco->addChild('Complemento', $partner->getEndereco()['Complemento']);
				$Endereco->addChild('Bairro', $partner->getEndereco()['Bairro']);
				$Endereco->addChild('CodigoMunicipio', $partner->getEndereco()['CodigoMunicipio']);
				$Endereco->addChild('Uf', $partner->getEndereco()['Uf']);
				$Endereco->addChild('Cep', $partner->getEndereco()['Cep']);


		$IntermediarioServico = $xml->addChild('IntermediarioServico');
		$services = $rps->getIntermediarioServico();
			$IntermediarioServico->addChild('RazaoSocial', $services->getRazaoSocial());
			$CpfCnpj = $IntermediarioServico->addChild('CpfCnpj');
				$CpfCnpj->addChild('Cnpj', $services->getCpfCnpj()['Cnpj']);

			$IntermediarioServico->addChild('InscricaoMunicipal', $services->getInscricaoMunicipal());


		$ConstrucaoCivil = $xml->addChild('ConstrucaoCivil');
		$civil = $rps->getConstrucaoCivil();
			$ConstrucaoCivil->addChild('CodigoObra', $civil->getCodigoObra());
			$ConstrucaoCivil->addChild('Art', $civil->getArt());

		return $xml->asXML();

	}

}