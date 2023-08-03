<?php

$postfields = array(
	'hashId' =>	"9901401e7398b65912d5cae4364da460"
);
$DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/'.$DirServer.'/backend/application/index.php?rota=/automationMorning');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // On dev server only!
$result = curl_exec($ch);
var_dump($result);
