<?php

namespace MilesBench\Controller\NFSe\BH\Lote\Lista;

class Servico {

	private $Valores;

	private $ItemListaServico;
	private $CodigoTributacaoMunicipio;
	private $Discriminacao;
	private $CodigoMunicipio;


	public function getValores() {
		return $this->Valores;
	}

	public function setValores($valores) {
		$this->Valores = $valores;
	}


	public function getItemListaServico() {
		return $this->ItemListaServico;
	}

	public function setItemListaServico($ItemListaServico) {
		$this->ItemListaServico = $ItemListaServico;
	}


	public function getCodigoTributacaoMunicipio() {
		return $this->CodigoTributacaoMunicipio;
	}

	public function setCodigoTributacaoMunicipio($CodigoTributacaoMunicipio) {
		$this->CodigoTributacaoMunicipio = $CodigoTributacaoMunicipio;
	}


	public function getDiscriminacao() {
		return $this->Discriminacao;
	}

	public function setDiscriminacao($Discriminacao) {
		$this->Discriminacao = $Discriminacao;
	}


	public function getCodigoMunicipio() {
		return $this->CodigoMunicipio;
	}

	public function setCodigoMunicipio($CodigoMunicipio) {
		$this->CodigoMunicipio = $CodigoMunicipio;
	}

}