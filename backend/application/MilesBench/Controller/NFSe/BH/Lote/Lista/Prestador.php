<?php

namespace MilesBench\Controller\NFSe\BH\Lote\Lista;

class Prestador {

	private $Cnpj;
	private $InscricaoMunicipal;

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
}