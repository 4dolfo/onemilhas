<?php

namespace MilesBench\Controller\NFSe\BH\Lote\Lista;

class InfRps {

	private $IdentificacaoRps;
	private $DataEmissao;
	private $NaturezaOperacao;
	private $RegimeEspecialTributacao;
	private $OptanteSimplesNacional;
	private $IncentivadorCultural;
	private $Status;

	private $Servico;
	private $Prestador;
	private $Tomador;
	private $IntermediarioServico;
	private $ConstrucaoCivil;

	public function __construct() {
		$this->IdentificacaoRps = array();
	}

	public function getIdentificacaoRps() {
		return $this->IdentificacaoRps;
	}

	public function setIdentificacaoFromArray($array) {
		$this->IdentificacaoRps = $array;
	}

	public function setIdentificacaoFromNames($Numero, $Serie, $Tipo) {
		$this->IdentificacaoRps = array('Numero' => $Numero, 'Serie' => $Serie, 'Tipo' => $Tipo);
	}


	public function getDataEmissao() {
		return $this->DataEmissao;
	}


	public function getNaturezaOperacao() {
		return $this->NaturezaOperacao;
	}

	public function setNaturezaOperacao($natureza) {
		$this->NaturezaOperacao = $natureza;
	}


	public function getRegimeEspecialTributacao() {
		return $this->RegimeEspecialTributacao;
	}

	public function setRegimeEspecialTributacao($regime) {
		$this->RegimeEspecialTributacao = $regime;
	}


	public function getOptanteSimplesNacional() {
		return $this->OptanteSimplesNacional;
	}

	public function setOptanteSimplesNacional($optante) {
		$this->OptanteSimplesNacional = $optante;
	}


	public function getIncentivadorCultural() {
		return $this->IncentivadorCultural;
	}

	public function setIncentivadorCultural($incentivador) {
		$this->IncentivadorCultural = $incentivador;
	}


	public function getStatus() {
		return $this->Status;
	}

	public function setStatus($status) {
		$this->Status = $status;
	}


	public function getPrestador() {
		return $this->Prestador;
	}

	public function setPrestador($Prestador) {
		$this->Prestador = $Prestador;
	}


	public function getTomador() {
		return $this->Tomador;
	}

	public function setTomador($Tomador) {
		$this->Tomador = $Tomador;
	}


	public function getIntermediarioServico() {
		return $this->IntermediarioServico;
	}

	public function setIntermediarioServico($IntermediarioServico) {
		$this->IntermediarioServico = $IntermediarioServico;
	}


	public function getConstrucaoCivil() {
		return $this->ConstrucaoCivil;
	}

	public function setConstrucaoCivil($ConstrucaoCivil) {
		$this->ConstrucaoCivil = $ConstrucaoCivil;
	}


	public function getServico() {
		return $this->Servico;
	}

	public function setServico($Servico) {
		$this->Servico = $Servico;
	}
}