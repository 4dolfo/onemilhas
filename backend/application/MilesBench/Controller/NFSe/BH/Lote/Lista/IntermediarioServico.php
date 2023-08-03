<?php

namespace MilesBench\Controller\NFSe\BH\Lote\Lista;

class IntermediarioServico {

	private $RazaoSocial;
	private $CpfCnpj;
	private $InscricaoMunicipal;

	public function getCpfCnpj() {
		return $this->CpfCnpj;
	}

	public function setCpfCnpj($Cnpj) {
		$this->CpfCnpj = array('Cnpj' => $Cnpj);
	}


	public function getRazaoSocial() {
		return $this->RazaoSocial;
	}

	public function setRazaoSocial($RazaoSocial) {
		$this->RazaoSocial = $RazaoSocial;
	}


	public function getInscricaoMunicipal() {
		return $this->InscricaoMunicipal;
	}

	public function setInscricaoMunicipal($InscricaoMunicipal) {
		$this->InscricaoMunicipal = $InscricaoMunicipal;
	}
}