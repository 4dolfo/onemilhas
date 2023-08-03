<?php

require_once 'autoloader.php';
//require_once 'register.php';
require_once 'routes.php';
require_once 'router.php';

require_once 'MilesBench/.env.php';

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


$ds = DIRECTORY_SEPARATOR;

$rota = null;
if(isset($_GET['rota'])) {
    $rota = $_GET['rota'];
}else{
    $rota = '/login';
}
__route($rota, $routes);