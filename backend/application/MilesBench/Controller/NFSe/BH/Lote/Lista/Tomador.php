<?php

namespace MilesBench\Controller\NFSe\BH\Lote\Lista;

class Tomador {

	private $IdentificacaoTomador = array();
	private $RazaoSocial;
	private $Endereco = array();

	public function getIdentificacaoTomador() {
		return $this->IdentificacaoTomador;
	}

	public function setIdentificacaoTomador($Cnpj, $InscricaoMunicipal) {
		$this->IdentificacaoTomador = array('CpfCnpj' => array('Cnpj' => $Cnpj, 'InscricaoMunicipal' => $InscricaoMunicipal));
	}

	public function setIdentificacaoTomadorByArray($IdentificacaoTomador) {
		$this->IdentificacaoTomador = $IdentificacaoTomador;
	}


	public function getRazaoSocial() {
		return $this->RazaoSocial;
	}

	public function setRazaoSocial($RazaoSocial) {
		$this->RazaoSocial = $RazaoSocial;
	}


	public function getEndereco() {
		return $this->Endereco;
	}

	public function setEndereco($Endereco, $Numero, $Complemento, $Bairro, $CodigoMunicipio, $Uf, $Cep) {
		$this->Endereco = array('Endereco' => $Endereco, 'Numero' => $Numero, 'Complemento' => $Complemento, 'Bairro' => $Bairro, 'CodigoMunicipio' => $CodigoMunicipio, 'Uf' => $Uf, 'Cep' => $Cep);
	}

	public function setEnderecoByArray($Endereco) {
		$this->Endereco = $Endereco;
	}
}