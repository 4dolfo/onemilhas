<?php

require_once dirname(__FILE__) . '/autoloader.php';

require_once dirname(__FILE__) . '/routes.php';
require_once dirname(__FILE__) . '/router.php';

require_once dirname(__FILE__) . '/MilesBench/.env.php';

require dirname(__FILE__) . '/../vendor/aws-sdk-php/src/functions.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/psr7/src/functions.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/promises/src/functions.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/functions.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Handler/Proxy.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Handler/CurlFactoryInterface.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Handler/CurlHandler.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Handler/StreamHandler.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Handler/EasyHandle.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/promises/src/PromiseInterface.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/promises/src/Promise.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/promises/src/TaskQueueInterface.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/promises/src/TaskQueue.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/promises/src/RejectedPromise.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Exception/GuzzleException.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Exception/TransferException.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Exception/RequestException.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Exception/BadResponseException.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Exception/ClientException.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/RequestOptions.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/PrepareBodyMiddleware.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/RedirectMiddleware.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Middleware.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/HandlerStack.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/ClientInterface.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/guzzle/src/Client.php';
require dirname(__FILE__) . '/../vendor/guzzlehttp/promises/src/FulfilledPromise.php';

// cron para envio de emails
$postfields = array(
	'hashId' =>	"9901401e7398b65912d5cae4364da460"
);

$HostServer = getenv('HostServer') ? getenv('HostServer') : '52.70.119.195';
$DirServer = getenv('DirServer') ? getenv('DirServer') : 'cml-gestao';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://'.$HostServer.'/'.$DirServer.'/backend/application/index.php?rota=/automationMorning');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // On dev server only!
$result = curl_exec($ch);


$em = \MilesBench\Application::getInstance()->getEntityManager();
$conn = \MilesBench\Application::getInstance()->getQueryBuilder();

$date = new DateTime();
$date->sub(new DateInterval('P1D'));
$date = $date->format('Y-m-d 00:00:00');

$qb = $em->createQueryBuilder();
$qb->select('o')
    ->from('OnlineOrder', 'o')
    ->where('o.createdAt > :when AND o.status = :status AND o.notificationurl LIKE \'%skymilhas%\'')
    ->setParameters(array('when' => $date, 'status' => 'EMITIDO'));

$skyOrders = $qb->getQuery()->getResult();
$numSkyOrders = count($skyOrders);

$qb = $em->createQueryBuilder();
$qb->select('o')
    ->from('OnlineOrder', 'o')
    ->where('o.createdAt > :when AND o.status = :status AND o.notificationurl LIKE \'%skymilhas%\' AND o.nfeid IS NOT NULL')
    ->setParameters(array('when' => $date, 'status' => 'EMITIDO'));

$skyOrdersWithNfe = $qb->getQuery()->getResult();
$numSkyOrdersWithNfe = count($skyOrdersWithNfe);

$numSkyOrdersWithoutNfe = $numSkyOrders - $numSkyOrdersWithNfe;

$content = "<body style='margin:0; padding:0'>";
$content .= "<div style='font-family:sans-serif; background-color:#f7f7f7; padding:20px; color: #3f3f3f;'>";
$content .= "<img src='https://skymilhas.com.br/img/logo.png' style='width:300px;height:70px;' alt='SkyMilhas' />";
$content .= "<h2>Relatório diário de vendas</h2>";
$content .= "Número de pedidos emitidos: <strong>{$numSkyOrders}</strong><br />";
$content .= "Número de pedidos emitidos com nota fiscal: <strong>{$numSkyOrdersWithNfe}</strong><br />";
$content .= "Número de pedidos emitidos sem nota fiscal: <strong>{$numSkyOrdersWithoutNfe}</strong><br />";
$content .= "</div></body>";

$email1 = 'adm@onemilhas.com.br';
$postfields = array(
    'content' => $content,
    'partner' => $email1,
    'subject' => 'Relatório diário de pedidos - SkyMilhas',
    'type' => ''
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result = curl_exec($ch);

echo $result;
