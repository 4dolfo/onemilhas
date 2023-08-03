<?php
//function __autoload($class)
//ADOLFO CHANGE
spl_autoload_register(function (string $class)
{
    $ds = DIRECTORY_SEPARATOR;
    $vendorDir = dirname(__FILE__) . '/..'.$ds.'vendor';
    $namespace = array(
        'Doctrine\\ORM' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Repository' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Event' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Proxy' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Id' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Query' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Query\\Expr' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Query\\AST' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Query\\Exec' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Persisters' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Mapping' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Mapping\\Driver' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Internal\\Hydration' => '..'.$ds.'lib'.$ds,
        'Doctrine\\ORM\\Internal' => '..'.$ds.'lib'.$ds,
        'Doctrine\\DBAL' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\DBAL\\Driver' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\DBAL\\Driver\\PDOMySql' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\DBAL\\Query\\Expression' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\DBAL\\Query' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\DBAL\\Platforms' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\DBAL\\Exception' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\DBAL\\Types' => $vendorDir . $ds.'doctrine'.$ds.'dbal'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Lexer' => $vendorDir . $ds.'doctrine'.$ds.'lexer'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Inflector' => $vendorDir . $ds.'doctrine'.$ds.'inflector'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Collections' => $vendorDir . $ds.'doctrine'.$ds.'collections'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Collections\\Expr' => $vendorDir . $ds.'doctrine'.$ds.'collections'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Cache' => $vendorDir . $ds.'doctrine'.$ds.'cache'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Annotations' => $vendorDir . $ds.'doctrine'.$ds.'annotations'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Annotations\\Annotation' => $vendorDir . $ds.'doctrine'.$ds.'annotations'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Persistence' => $vendorDir . $ds.'doctrine'.$ds.'common'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Persistence\\Mapping' => $vendorDir . $ds.'doctrine'.$ds.'common'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Persistence\\Mapping\\Driver' => $vendorDir . $ds.'doctrine'.$ds.'common'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Proxy' => $vendorDir . $ds.'doctrine'.$ds.'common'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Proxy\\Exception' => $vendorDir . $ds.'doctrine'.$ds.'common'.$ds.'lib'.$ds,
        'Doctrine\\Common\\Util' => $vendorDir . $ds.'doctrine'.$ds.'common'.$ds.'lib'.$ds,
        'Doctrine\\Common' => $vendorDir . $ds.'doctrine'.$ds.'common'.$ds.'lib'.$ds
    );

    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    if (substr($path,0,8) == "Doctrine") {
        $namespaceClass = substr($path,0,strrpos($path,strrchr($path,DIRECTORY_SEPARATOR)));
        $pathClass = $namespace[str_replace('/','\\',$namespaceClass)];
        require $pathClass.$path.'.php';
    } else if(substr($path,0,13) == 'Firebase/JWT/' || substr($path,0,13) == 'Firebase\\JWT\\') {
        require $vendorDir . $ds . 'firebase' . $ds . 'php-jwt' . $ds . 'src' . $ds . 'JWT.php';
    } else if(substr($path,0,11) == 'OpenBoleto/' || substr($path,0,11) == 'OpenBoleto\\') {
        require $vendorDir . $ds . 'openboleto' . $ds . 'src' . $ds . $path . '.php';
    } else if(substr($path,0,4) == 'H2P/' || substr($path,0,4) == 'H2P\\') {
        require $vendorDir . $ds . 'h2p' . $ds . 'src' . $ds . $path . '.php';
    } else if(substr($path,0,4) == 'GARB' || substr($path,0,4) == 'GARB') {  //arquivo de remessa
        require $vendorDir . $ds . $path . '.php';

    } else if(substr($path,0,4) == 'Aws/') {
        $file = substr($path, 4);
        require $vendorDir . $ds . 'aws-sdk-php' . $ds . 'src' . $ds . $file . '.php';
    } else if(substr($path,0,11) == 'GuzzleHttp/') {
        $file = substr($path, 11);
        if(substr($file,0,5) == 'Psr7/') {
            $file = substr($file, 5);
            require $vendorDir . $ds . 'guzzlehttp' . $ds . 'psr7' . $ds . 'src' . $ds . $file . '.php';
        } else if(substr($file,0,5) == 'Psr7/') {
            $file = substr($file, 5);
            require $vendorDir . $ds . 'guzzlehttp' . $ds . 'psr7' . $ds . 'src' . $ds . $file . '.php';
        }
    } else if(substr($path,0,17) == 'Psr/Http/Message/') {
        $file = substr($path, 17);
        require $vendorDir . $ds . 'psr' . $ds . 'http-message' . $ds . 'src' . $ds . $file . '.php';

    } else if(substr($path,0,12) == 'ManoelCampos' || substr($path,0,12) == 'ManoelCampos') {  //arquivo de retorno do boleto
        require $vendorDir . $ds . 'retorno-boletophp' . $ds . 'src' . $ds . $path . '.php';

    } else if(substr($path,0,5) == 'Cnab/') {
        require $vendorDir . $ds . 'CnabPHP' . $ds . 'src' . $ds . $path . '.php';
    } else if(substr($path,0,4) == 'Spyc') {
        require $vendorDir . $ds . 'spyc' . $ds . 'Spyc.php';

    } else if(substr($path,0,6) == 'Twilio') { //sms Twilio
        require $vendorDir . $ds . 'twilio-php' . $ds . $path . '.php';

    } else if (substr($path,0,10) == "MilesBench") {
        require $path . '.php';
    } else {
        if(file_exists('../application/MilesBench/Model/mms_gestao/'.$path . '.php')) {
            require '../application/MilesBench/Model/mms_gestao/'.$path . '.php';
        } else {
            var_dump('require');
            var_dump($class);
            var_dump($path);die;
        }
    }
});

