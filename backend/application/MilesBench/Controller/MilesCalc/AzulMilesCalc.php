<?php

namespace MilesBench\Controller\MilesCalc;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class AzulMilesCalc {

    public function Migrate(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();

        try {
            
            $migration = \MilesBench\Controller\MilesCalc\AzulMilesCalc::migrateSRMCards();
            $migration = \MilesBench\Controller\MilesCalc\AzulMilesCalc::migrateTables();
            $migration = \MilesBench\Controller\MilesCalc\AzulMilesCalc::createCardCategories();
            
        } catch(\Exception $e) {
            var_dump($e);die;
        }

    }

    public static function migrateSRMCards() {
        $em = Application::getInstance()->getEntityManager();

        // 12359 == MMS Card
        // 3100, 5290, 9709, 11799, 12226 === SRM
        $CardSRM = $em->getRepository('Cards')->findOneBy( array( 'id' => 212359 ) );
        $MilesbenchSRM = $em->getRepository('Milesbench')->findOneBy( array( 'cards' => 212359 ) );

        $sql = "select c FROM Cards c where c.id in (6063) ";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();

        foreach ($Cards as $key => $value) {

            $Purchases = $em->getRepository('Purchase')->findBy( array( 'cards' => $value->getId() ) );
            foreach ($Purchases as $purchase) {
                $purchase->setCards($CardSRM);

                $em->persist($purchase);
                $em->flush($purchase);
            }

            $Sales = $em->getRepository('Sale')->findBy( array( 'cards' => $value->getId() ) );
            foreach ($Sales as $sale) {
                $sale->setCards($CardSRM);

                $em->persist($sale);
                $em->flush($sale);
            }

            $Milesbench = $em->getRepository('Milesbench')->findOneBy( array( 'cards' => $value->getId() ) );

            $MilesbenchSRM->setLeftover( $MilesbenchSRM->getLeftover() +  $Milesbench->getLeftover() );
            $em->persist($MilesbenchSRM);
            $em->flush($MilesbenchSRM);

            $Milesbench->setLeftover(0);
            $em->remove($Milesbench);
            $em->flush($Milesbench);
        }

        // 9709 == SRM Card
        $CardSRM = $em->getRepository('Cards')->findOneBy( array( 'id' => 9709 ) );
        $MilesbenchSRM = $em->getRepository('Milesbench')->findOneBy( array( 'cards' => 9709 ) );

        $sql = "select c FROM Cards c where c.id in (9636) ";
        $query = $em->createQuery($sql);
        $Cards = $query->getResult();

        foreach ($Cards as $key => $value) {

            $Purchases = $em->getRepository('Purchase')->findBy( array( 'cards' => $value->getId() ) );
            foreach ($Purchases as $purchase) {
                $purchase->setCards($CardSRM);

                $em->persist($purchase);
                $em->flush($purchase);
            }

            $Sales = $em->getRepository('Sale')->findBy( array( 'cards' => $value->getId() ) );
            foreach ($Sales as $sale) {
                $sale->setCards($CardSRM);

                $em->persist($sale);
                $em->flush($sale);
            }

            $Milesbench = $em->getRepository('Milesbench')->findOneBy( array( 'cards' => $value->getId() ) );

            $MilesbenchSRM->setLeftover( $MilesbenchSRM->getLeftover() +  $Milesbench->getLeftover() );
            $em->persist($MilesbenchSRM);
            $em->flush($MilesbenchSRM);

            $Milesbench->setLeftover(0);
            $em->remove($Milesbench);
            $em->flush($Milesbench);
        }
    }

    public static function migrateTables() {
        $em = Application::getInstance()->getEntityManager();

        try {
            $FlightPathCategory = $em->getRepository('FlightPathCategory')->findAll();
            if(count($FlightPathCategory) == 0) {

                // generating monopoly category
                $AzulFlightCategoryMonopoly = new \AzulFlightCategory();
                $AzulFlightCategoryMonopoly->setName('Monopoly');
                $em->persist($AzulFlightCategoryMonopoly);
                $em->flush($AzulFlightCategoryMonopoly);

                // generating competitive category
                $AzulFlightCategoryCompetitive = new \AzulFlightCategory();
                $AzulFlightCategoryCompetitive->setName('Competitive');
                $em->persist($AzulFlightCategoryCompetitive);
                $em->flush($AzulFlightCategoryCompetitive);

                // generating monopoly paths
                require dirname(__FILE__) . '/Mono.php';
                foreach ($mono as $key => $value) {

                    $FlightPathCategory = new \FlightPathCategory();
                    $FlightPathCategory->setFlightFrom( substr($key, 0, 3) );
                    $FlightPathCategory->setFlightTo( substr($key, 3, 3) );

                    if($value == "mono") {
                        $FlightPathCategory->setFlightCategory($AzulFlightCategoryMonopoly);
                    } else if($value == "compe") {
                        $FlightPathCategory->setFlightCategory($AzulFlightCategoryCompetitive);
                    }

                    $em->persist($FlightPathCategory);
                    $em->flush($FlightPathCategory);
                }
            }

        } catch(\Exception $e) {
            var_dump($e);die;
        }
    }

    public static function createCardCategories() {
        $em = Application::getInstance()->getEntityManager();
        $CardSRM = $em->getRepository('Cards')->findOneBy( array( 'id' => 9709 ) );
        $AzulFlightCategoryMonopoly = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => 'Monopoly' ) );
        $AzulFlightCategoryCompetitive = $em->getRepository('AzulFlightCategory')->findOneBy( array( 'name' => 'Competitive' ) );

        // generating SRM cards distribuition - monopoly
        $MilesbenchCategory = new \MilesbenchCategory();
        $MilesbenchCategory->setPercentage(1);
        $MilesbenchCategory->setDays(4);
        $MilesbenchCategory->setToFree(0);
        $MilesbenchCategory->setUsed(0);
        $MilesbenchCategory->setOriginalToFree(0);
        $MilesbenchCategory->setCards($CardSRM);
        $MilesbenchCategory->setFlightCategory($AzulFlightCategoryMonopoly);
        $em->persist($MilesbenchCategory);
        $em->flush($MilesbenchCategory);

        $MilesbenchCategory = new \MilesbenchCategory();
        $MilesbenchCategory->setPercentage(10);
        $MilesbenchCategory->setDays(7);
        $MilesbenchCategory->setToFree(0);
        $MilesbenchCategory->setUsed(0);
        $MilesbenchCategory->setOriginalToFree(0);
        $MilesbenchCategory->setCards($CardSRM);
        $MilesbenchCategory->setFlightCategory($AzulFlightCategoryMonopoly);
        $em->persist($MilesbenchCategory);
        $em->flush($MilesbenchCategory);

        $MilesbenchCategory = new \MilesbenchCategory();
        $MilesbenchCategory->setPercentage(30);
        $MilesbenchCategory->setDays(21);
        $MilesbenchCategory->setToFree(0);
        $MilesbenchCategory->setUsed(0);
        $MilesbenchCategory->setOriginalToFree(0);
        $MilesbenchCategory->setCards($CardSRM);
        $MilesbenchCategory->setFlightCategory($AzulFlightCategoryMonopoly);
        $em->persist($MilesbenchCategory);
        $em->flush($MilesbenchCategory);


        // generating SRM cards distribuition - competitive
        $MilesbenchCategory = new \MilesbenchCategory();
        $MilesbenchCategory->setPercentage(1);
        $MilesbenchCategory->setDays(4);
        $MilesbenchCategory->setToFree(0);
        $MilesbenchCategory->setUsed(0);
        $MilesbenchCategory->setOriginalToFree(0);
        $MilesbenchCategory->setCards($CardSRM);
        $MilesbenchCategory->setFlightCategory($AzulFlightCategoryCompetitive);
        $em->persist($MilesbenchCategory);
        $em->flush($MilesbenchCategory);

        $MilesbenchCategory = new \MilesbenchCategory();
        $MilesbenchCategory->setPercentage(25);
        $MilesbenchCategory->setDays(7);
        $MilesbenchCategory->setToFree(0);
        $MilesbenchCategory->setUsed(0);
        $MilesbenchCategory->setOriginalToFree(0);
        $MilesbenchCategory->setCards($CardSRM);
        $MilesbenchCategory->setFlightCategory($AzulFlightCategoryCompetitive);
        $em->persist($MilesbenchCategory);
        $em->flush($MilesbenchCategory);

        $MilesbenchCategory = new \MilesbenchCategory();
        $MilesbenchCategory->setPercentage(50);
        $MilesbenchCategory->setDays(21);
        $MilesbenchCategory->setToFree(0);
        $MilesbenchCategory->setUsed(0);
        $MilesbenchCategory->setOriginalToFree(0);
        $MilesbenchCategory->setCards($CardSRM);
        $MilesbenchCategory->setFlightCategory($AzulFlightCategoryCompetitive);
        $em->persist($MilesbenchCategory);
        $em->flush($MilesbenchCategory);
        
    }
}