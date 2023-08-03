<?php

namespace MilesBench\Controller\NFSe;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

require dirname(__FILE__) . '/../../../../vendor/php-client/src/eNotasGW.php';

class ENotas {

    public function emit(Request $request, Response $response) {
        // $dados = $request->getRow();
        $ENotas = new ENotas();
        // var_dump('asdas');die;
        $returnENotas = $ENotas->emitArray(array(
            'name' => 'Arthur Fonseca Vilaca',
            'cpf' => '01273870646',
            'address' => 'Rua alabandina',
            'number_address' => '140',
            'complement_address' => '',
            'district_address' => 'Alto Caiçara',
            'city' => 'Belo Horizonte',
            'state' => 'MG',
            'zip_code' => '30775330',
            'email' => 'vilaca.arthur.f@gmail.com',
            'value' => 2
        ));

        var_dump($returnENotas);die;
    }

    public function emitArray($array) {
        $em = Application::getInstance()->getEntityManager();

        \eNotasGW::configure(array(
            //'apiKey' => 'YTE1NzRiNjYtMWY2Yy00OGYyLWE3NmMtZTY2MjVhYmIwMzAw'
            'apiKey' => 'MWE4YjIxMjYtMGQ0MS00ZWM4LWI4YjMtZWFiMDRiMGMwNTAw'
        ));

        $empresaId = '81D81EED-C477-4C32-9105-26AD45C20300';
        $idExterno = date('Y-m-d H:i:s:u');

        try {
            $nfeId = \eNotasGW::$NFeApi->emitir($empresaId, array(
                'tipo' => 'NFS-e',
                'idExterno' => $idExterno,
                'ambienteEmissao' => 'Producao', //'Homologacao' ou 'Producao'		
                'cliente' => array(
                    'nome' => $array['name'],
                    'email' => $array['email'],
                    'cpfCnpj' => $array['cpf'],
                    'endereco' => array(
                        'uf' => $array['state'], 
                        'cidade' => $array['city'],
                        'logradouro' => $array['address'],
                        'numero' => $array['number_address'],
                        'complemento' => $array['complement_address'],
                        'bairro' => $array['district_address'],
                        'cep' => $array['zip_code']
                    )
                ),
                'servico' => array(
                    'descricao' => $array['descricao']
                ),
                'valorTotal' => $array['value'],
                'deducoes' => $array['deductions']
            ));

            return array('type' => 'S', 'id' => $nfeId);
        } catch (\Exception $e) {
            $errors = '';
            foreach ($e->errors as $key => $value) {
                $errors .= ' ' . $value->mensagem;
            }

            $content = "<br>".$errors."<br><br>SRM-IT";
            $email1 = 'adm@onemilhas.com.br';
            $postfields = array(
                'content' => $content,
                'partner' => $email1,
                'subject' => 'ERROR - ENOTAS',
                'type' => ''
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);

            return array('type' => 'E', 'message' => $errors);
        }
    }
}