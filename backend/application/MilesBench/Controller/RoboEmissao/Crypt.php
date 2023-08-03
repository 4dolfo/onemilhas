<?php

namespace MilesBench\Controller\RoboEmissao;

class Crypt {

    private $key;
    public function __construct() {
        $this->key = $this->getKey();
    }

    public function encrypt($str) {
        $crypted = null;
        openssl_public_encrypt($str, $crypted, $this->getKey());
        return base64_encode($crypted);
    }

    private function getKey() {
        if ($this->key) {
            return $this->key;
        }
        return file_get_contents("./MilesBench/Controller/RoboEmissao/public.pem");
    }

}

// $crypt = new Crypt();
// $cartao = $crypt->encrypt(‘1234432112344321’);
// echo $cartao;