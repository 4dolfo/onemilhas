<?php

$hashId = '9207cb34d5e697b4ca13c03656b84eef';
$postfields = file_get_contents('./post.json');
$postfields = json_decode($postfields, true);

$DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/'.$DirServer.'/backend/application/index.php?rota=/oktoplus/geraPedido');
// curl_setopt($ch, CURLOPT_URL, 'http://54.89.146.72/'.$DirServer.'/backend/application/index.php?rota=/oktoplus/geraPedido');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($postfields));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'hashId: '. $hashId
));
$result = curl_exec($ch);
var_dump($result);
