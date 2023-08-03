<?php

namespace MilesBench\Controller\NFSe\BH\Lote\Lista;

class Valores {

	private $ValorServicos;
	private $ValorDeducoes;
	private $ValorPis;
	private $ValorCofins;
	private $ValorInss;
	private $ValorIr;
	private $ValorCsll;
	private $IssRetido;
	private $ValorIss;
	private $OutrasRetencoes;
	private $Aliquota;
	private $DescontoIncondicionado;
	private $DescontoCondicionado;


	public function getValorServicos() {
		return $this->ValorServicos;
	}

	public function setValorServicos($valores) {
		$this->ValorServicos = $valores;
	}


	public function getValorDeducoes() {
		return $this->ValorDeducoes;
	}

	public function setValorDeducoes($valores) {
		$this->ValorDeducoes = $valores;
	}


	public function getValorPis() {
		return $this->ValorPis;
	}

	public function setValorPis($valores) {
		$this->ValorPis = $valores;
	}


	public function getValorCofins() {
		return $this->ValorCofins;
	}

	public function setValorCofins($valores) {
		$this->ValorCofins = $valores;
	}


	public function getValorInss() {
		return $this->ValorInss;
	}

	public function setValorInss($valores) {
		$this->ValorInss = $valores;
	}


	public function getValorIr() {
		return $this->ValorIr;
	}

	public function setValorIr($valores) {
		$this->ValorIr = $valores;
	}


	public function getValorCsll() {
		return $this->ValorCsll;
	}

	public function setValorCsll($valores) {
		$this->ValorCsll = $valores;
	}


	public function getIssRetido() {
		return $this->IssRetido;
	}

	public function setIssRetido($valores) {
		$this->IssRetido = $valores;
	}


	public function getValorIss() {
		return $this->ValorIss;
	}

	public function setValorIss($valores) {
		$this->ValorIss = $valores;
	}


	public function getOutrasRetencoes() {
		return $this->OutrasRetencoes;
	}

	public function setOutrasRetencoes($valores) {
		$this->OutrasRetencoes = $valores;
	}


	public function getAliquota() {
		return $this->Aliquota;
	}

	public function setAliquota($valores) {
		$this->Aliquota = $valores;
	}


	public function getDescontoIncondicionado() {
		return $this->DescontoIncondicionado;
	}

	public function setDescontoIncondicionado($valores) {
		$this->DescontoIncondicionado = $valores;
	}


	public function getDescontoCondicionado() {
		return $this->DescontoCondicionado;
	}

	public function setDescontoCondicionado($valores) {
		$this->DescontoCondicionado = $valores;
	}
}