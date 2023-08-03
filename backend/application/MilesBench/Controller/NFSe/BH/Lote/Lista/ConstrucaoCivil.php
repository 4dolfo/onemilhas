<?php

namespace MilesBench\Controller\NFSe\BH\Lote\Lista;

class ConstrucaoCivil {

	private $CodigoObra;
	private $Art;


	public function getCodigoObra() {
		return $this->CodigoObra;
	}

	public function setCodigoObra($CodigoObra) {
		$this->CodigoObra = $CodigoObra;
	}


	public function getArt() {
		return $this->Art;
	}

	public function setArt($Art) {
		$this->Art = $Art;
	}
}