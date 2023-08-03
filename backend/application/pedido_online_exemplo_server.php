<?php
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////// Login //////////////////////////////////////////////////////
//////////////////////////////// Passa email para retornar o hash de conexÃ£o /////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
$postfields = array(
	'email' => 'pedidosonline@uaimilhas.com.br',
	'hashId' => '461c52e9hs1e197rb3d79c92f97167a7'
);

$DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/'.$DirServer.'/backend/application/index.php?rota=/login');
//curl_setopt($ch, CURLOPT_URL, 'http://34.207.228.97/'.$DirServer.'/backend/application/index.php?rota=/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
$result = curl_exec($ch);
$dataset = json_decode($result);
$hashId = $dataset->{'dataset'}[0]->{'hashId'};
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////// Gera Pedido ////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
$postfields = array(
	'hashId' => $hashId,
	'pedido' => array(
		'cia' => 'GOL',
		'Id' => '', //Numero do pedido caso exista
		'milhas_total' => '149600',
		'valor_total' => '6456.28',
		'nome_cliente' => 'arthur',
		'email_cliente' => 'teste@teste.com.br',
		'data_embarque' => '2015-10-07 08:00:00',
		'data_pouso' => '2015-10-07 20:00:00',
		'comments' => 'TESTE ARTHUR',
		'marckup_cliente' => '200.00',
		'hash_pagamento' => '',
		'metodo_pagamento' => 'Faturado',
		'economia' => '200.00',
		'metodo_emissao' => 'Milhas'
	),
	'trechos' => array(
		array(
			'cia' => 'GOL',
			'sigla_aeroporto_origem' => 'CNF',
			'sigla_aeroporto_destino' => 'GRU',
			'descricao_aeroporto_origem' => 'Belo Horizonte - Confins Intl',
			'descricao_aeroporto_destino' => 'SÃ£o Paulo - Guarulhos Intl',
			'data_embarque' => '2015-10-07 08:00:00', //Utilizar formato YYYY-MM-DD
			'data_pouso' => '2015-10-07 14:00:00', //Utilizar formato YYYY-MM-DD
			'conexao' => array(
				array(
					'Embarque' => '02:12',
					'Desembarque' => '03:15',
					'Origem' => 'CNF',
					'Destino' => 'GRU',
					'NumeroVoo' => 'ASD123',
					'Duracao' => '02hrs',
				),
				array(
					'NumeroVoo' => 'ASD123',
					'Duracao' => '02hrs',
					'Embarque' => '02:12',
					'Desembarque' => '03:15',
					'Origem' => 'GRU',
					'Destino' => 'PLU'
				)
			),
			'valor_trecho' => '1490.7',
			'milhas_trecho' => '34000',
			'valor_adultos' => '370',
			'valor_criancas' => '359',
			'valor_bebes' => '40',
			'milhas_adultos' => '10000',
			'milhas_criancas' => '7000',
			'milhas_bebes' => '0',
			'numero_de_adultos' => '2',
			'numero_de_criancas' => '2',
			'numero_de_bebes' => '2',
			'numero_voo' => 'JJ3441',
			'duracao_voo' => '01:25',
			'taxa' => '25.45',
			'classe' => 'Economica',
			'metodo_emissao' => 'Companhia',
			'classe_tarifaria' => 'W'
		),
		array(
			'cia' => 'GOL',
			'sigla_aeroporto_origem' => 'GRU',
			'sigla_aeroporto_destino' => 'CNF',
			'descricao_aeroporto_origem' => 'SÃ£o Paulo - Congonhas',
			'descricao_aeroporto_destino' => 'Rio de Janeiro - Santos Dumont',
			'data_embarque' => '2015-10-07 16:00:00', //Utilizar formato YYYY-MM-DD
			'data_pouso' => '2015-10-07 20:00:00', //Utilizar formato YYYY-MM-DD
			'conexao' => '',
			'valor_trecho' => '1485.84',
			'milhas_trecho' => '34000',
			'valor_adultos' => '370',
			'valor_criancas' => '359',
			'valor_bebes' => '40',
			'milhas_adultos' => '10000',
			'milhas_criancas' => '7000',
			'milhas_bebes' => '0',
			'numero_de_adultos' => '2',
			'numero_de_criancas' => '2',
			'numero_de_bebes' => '2',
			'numero_voo' => 'JJ3900',
			'duracao_voo' => '01:04',
			'taxa' => '25.64',
			'metodo_emissao' => 'Companhia',
			'classe_tarifaria' => 'W'
		)
	),
	'passageiros' => array(
		array(
			'nome'=>'Roberval Antunes',
			'sobrenome'=> 'Pereira',
			'agnome'=> 'Filho',
			'email'=>'emissao@uaimilhas.com.br',
			'data_nascimento'=>'1977-08-10 08:00:00', //Utilizar formato YYYY-MM-DD
			'telefone'=>'3189989990',
			'passageiro_bebe'=>'N',
			'genero'=>'M',
			'passageiro_crianca'=>'N',
			'identification'=>'01234567890',
			'bagagens' => array(
				'CNF_GRU' => array(
					'value' => 2,
					'price' => 60
				),
				'GRU_CNF' => array(
					'value' => 1,
					'price' => 30
				)
			)
		),
		array(
			'nome'=>'Juliana Novaes',
			'sobrenome'=> 'Cardoso',
			'agnome'=> '',
			'email'=>'emissao@uaimilhas.com.br',
			'data_nascimento'=>'1983-08-15 08:00:00', //Utilizar formato YYYY-MM-DD
			'telefone'=>'3189989990',
			'genero'=>'F',
			'passageiro_bebe'=>'N',
			'passageiro_crianca'=>'N',
			'identification'=>'01234567890',
			'bagagens' => array(
				'CNF_GRU' => array(
					'value' => 2,
					'price' => 60
				),
				'GRU_CNF' => array(
					'value' => 1,
					'price' => 30
				)
			)
		)
	)
);
$post_string = http_build_query($postfields);
$ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, 'http://localhost/'.$DirServer.'/backend/application/index.php?rota=/geraPedido');
//curl_setopt($ch, CURLOPT_URL, 'http://34.207.228.97/'.$DirServer.'/backend/application/index.php?rota=/geraPedido');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
$result = curl_exec($ch);
var_dump($result);
