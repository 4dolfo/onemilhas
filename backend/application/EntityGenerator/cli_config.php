<?php
/**
 * Created by PhpStorm.
 * User: robertomartins
 * Date: 1/3/2015
 * Time: 12:18 AM
 */

require_once 'bootstrap.php';

// Any way to access the EntityManager from  your application
$em = GetMyEntityManager();

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));