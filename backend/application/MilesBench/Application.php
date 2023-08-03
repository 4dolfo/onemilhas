<?php

namespace MilesBench;
/**
 * Description of Application
 *
 * @author tulio
 */

require 'Config/connectionManager.php';
class Application {

    /**
     *
     * @var Application
     */
    private static $instance;

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $VoeLegal;
    private $queryBuilder;
    private $queryBuilderVoeLegal;

    /**
     * Singleton Pattern
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    private function __construct() {

        $config = new \Doctrine\ORM\Configuration();
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(__DIR__ . '/MilesBench/Model/mms_gestao', false));
        //$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
        $config->setProxyDir(__DIR__ . '/MilesBench/Model/mms_gestao');
        $config->setProxyNamespace('Model\mms_gestao');
        $this->entityManager = \Doctrine\ORM\EntityManager::create(\MilesBench\Config\connectionManager::getParams()['mms_gestao'], $config);
    }

    /**
     * 
     * @return Application
     */
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new Application();
        }

        return self::$instance;
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->entityManager;
    }

    public function getQueryBuilder() {
        $config = new \Doctrine\DBAL\Configuration();
        $this->queryBuilder = \Doctrine\DBAL\DriverManager::getConnection(\MilesBench\Config\connectionManager::getParams()['mms_gestao'], $config);
        return $this->queryBuilder;
    }

    /**
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function setEntityManager(\Doctrine\ORM\EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function clearEntity() {
        $this->entityManager->clear();

        $config = new \Doctrine\ORM\Configuration();
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(__DIR__ . '/MilesBench/Model/mms_gestao', false));
        //$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
        $config->setProxyDir(__DIR__ . '/MilesBench/Model/mms_gestao');
        $config->setProxyNamespace('Model\mms_gestao');
        $this->entityManager = \Doctrine\ORM\EntityManager::create(\MilesBench\Config\connectionManager::getParams()['mms_gestao'], $config);
        return $this->entityManager;
    }

}