<?php
namespace MilesBench\Config;

class connectionManager {

    static $devParams = array(
        'mms_gestao' => array(
            'dbname' => 'mms_gestao',
            'user' => 'milesbenchdb',
            'password' => '1234Admin',
            'host' => 'mmsdbinstance.c1oy29a2adal.us-east-1.rds.amazonaws.com',
            'charset' => 'utf8',
            'driver' => 'pdo_mysql'
        )
    );

    public static function getParams() {
        $dbname = getenv('dbname') ? getenv('dbname') : 'mms_gestao';
        $user = getenv('user') ? getenv('user') : 'milesbenchdb';
        $password = getenv('password') ? getenv('password') : '1234Admin';
        $host = getenv('host') ? getenv('host') : 'mmsdbinstance.c1oy29a2adal.us-east-1.rds.amazonaws.com';

        return array(
            'mms_gestao' => array(
                'dbname' => $dbname,
                'user' => $user,
                'password' => $password,
                'host' => $host,
                'charset' => 'utf8',
                'driver' => 'pdo_mysql'
            )
        );
        return self::$devParams;
    }
}
