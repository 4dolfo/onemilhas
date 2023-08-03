<?php

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use \Firebase\JWT\JWT;

function __route($route, $routes) {
// Define HTTP URI
    $uri = isset($_SERVER['REQUEST_URI'])
        ? parse_url($_SERVER['REQUEST_URI'] , PHP_URL_PATH)
        : parse_url('/', PHP_URL_PATH);

    if(isset($routes[$route])) {
        $callController = function ($routeDescription) {
            
            $parts = explode('::', $routeDescription);
            $controller = new $parts[0]();
            $auth = false;

            try {

                if (isset($_POST['hashId']) || isset($_SERVER['HTTP_HASHID'])) {

                    $login = false;
                    if(isset($_POST['hashId'])) {
                        $login = (($_POST['hashId'] == '5a9b0fde419b522a8b9baede73811369') && ($parts[0] == '\MilesBench\Controller\Login'));
                        if ($login == false) {
                            $login = (($_POST['hashId'] == '461c52e9hs1e197rb3d79c92f97167a7') && ($parts[0] == '\MilesBench\Controller\Login'));
                        }
                    }

                    if (!$login) {
                        $em = Application::getInstance()->getEntityManager();

                        // get jwt from request
                        if(isset($_SERVER['HTTP_HASHID'])) {
                            $jwt = $_SERVER['HTTP_HASHID'];
                        } else if(isset($_POST['hashId'])) {
                            $jwt = $_POST['hashId'];
                        }

                        // user session from jwt
                        $UserSession = $em->getRepository('UserSession')->findOneBy(array('hashid' => $jwt));

                        // partner token validation
                        if($UserSession) {
                            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U'));
                            if(!$BusinessPartner) {
                                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U_P'));
                            }
                            if(!$BusinessPartner) {
                                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'U_D'));
                            }
                            if(!$BusinessPartner) {
                                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('name' => $UserSession->getEmail()));
                            }

                            // clients access
                            if(!$BusinessPartner) {
                                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $UserSession->getEmail(), 'partnerType' => 'C'));
                            }

                            if($BusinessPartner) {

                                // $UsersRequests = new \UsersRequests();
                                // $UsersRequests->setRequest($_GET['rota']);
                                // $UsersRequests->setBusinesspartner($BusinessPartner);
                                // $UsersRequests->setIssueDate(new \DateTime());
                                // $UsersRequests->setIp($_SERVER['REMOTE_ADDR']);

                                // $ch = curl_init();
                                // curl_setopt_array($ch, array(
                                //     CURLOPT_RETURNTRANSFER => 1,
                                //     curl_setopt($ch, CURLOPT_URL, 'http://ip-api.com/json/'.$_SERVER['REMOTE_ADDR']),
                                //     CURLOPT_USERAGENT => 'Codular Sample cURL Request'
                                // ));
                                // $result = json_decode(curl_exec($ch), true);
                                // if($result != null) {
                                //     if(isset($result['country'])) {
                                //         $UsersRequests->setLocation($result['country']);
                                //     }
                                //     if(isset($result['city'])) {
                                //         $UsersRequests->setCity($result['city']);
                                //     }
                                //     if(isset($result['regionName'])) {
                                //         $UsersRequests->setRegion($result['regionName']);
                                //     }
                                // }
                                // $em->persist($UsersRequests);
                                // $em->flush($UsersRequests);

                                // jwt validation
                                if(isset($_SERVER['HTTP_HASHID'])) {
                                    $decoded = (array)JWT::decode($jwt, \MilesBench\Util::key, array('HS256'));
                                    if($BusinessPartner->getEmail() != $decoded['email'] || 
                                        $BusinessPartner->getId() != $decoded['id'] || 
                                        md5($BusinessPartner->getPassword()) != $decoded['password']) {
                                        throw new \Exception("Erro na validação do usuário", 1);
                                    }
                                }

                                //user found
                                $auth = true;

                                // dealer access routes
                                if(strpos($BusinessPartner->getPartnerType(), "D") !== false) {
                                    require_once 'dealerRoutes.php';
                                    if(!isset($dealerRoutes[$parts[1]])) {
                                        throw new \Exception("Rota Invalida!", 1);
                                    }
                                    $parts[1] = $dealerRoutes[$parts[1]];
                                    $dealerParts = explode('::', $parts[1]);
                                    $parts[1] = $dealerParts[1];
                                    $controller = new $dealerParts[0]();
                                }

                                // clients access routes
                                if(strpos($BusinessPartner->getPartnerType(), "C") !== false) {
                                    require_once 'clientRoutes.php';
                                    if(!isset($dealerRoutes[$parts[1]])) {
                                        throw new \Exception("Rota Invalida!", 1);
                                    }
                                    $parts[1] = $dealerRoutes[$parts[1]];
                                    $dealerParts = explode('::', $parts[1]);
                                    $parts[1] = $dealerParts[1];
                                    $controller = new $dealerParts[0]();
                                }

                                // busca ideal IN8
                                if (($BusinessPartner->getWebserviceLogin() == 'Y') && !($parts[0] == '\MilesBench\Controller\OnlineOrder')) {
                                    $auth = false;
                                }

                            }
                        }
                    } else {
                        $auth = true;
                    }
                }
            } catch (\Exception $e) {
                $message = array(
                    'message' => array(
                        'text'=> $e->getMessage(),
                        'type'=>'E'
                    )
                );
        
                echo json_encode($message); die;
            }

            // voe legal/ voe 10 contos key
            if(!$auth && isset($_POST['hashId'])) {
                // VOALEGAL -> 79fbe9b577f0a4e31bae4753449dfd4c
                // VOE10CONTOS -> 1851f8359de4f4ced724e47f777072f3
                // VOECASAMENTO -> 706f8d6c5e5d88fec976e0aa420467c9
                if(strrpos($_GET['rota'], "/App/") !== false || strrpos($_GET['rota'], "geraPedido") !== false) {
                    if($_POST['hashId'] == '79fbe9b577f0a4e31bae4753449dfd4c' || $_POST['hashId'] == '1851f8359de4f4ced724e47f777072f3' || $_POST['hashId'] == '706f8d6c5e5d88fec976e0aa420467c9') {
                        $auth = true;
                    }
                }
            }

            // b2b-key
            if(!$auth && isset($_POST['hashId'])) {
                if($_POST['hashId'] == '32b54e4829989d4ab2ac3b7644779eea') {
                    $auth = true;
                }
            }
            
            // websocket, email and gsrm key
            if(!$auth && isset($_POST['hashId'])) {
                if($_POST['hashId'] == '9901401e7398b65912d5cae4364da460') {
                    $auth = true;
                }
            }

            if($parts[1] == "saveFile" || $parts[1] == "saveProfilePicture" || $parts[1] == "saveFileScheduled" || $parts[1] == "readFile" || $parts[1] == "saveBilletOrder" || $parts[1] == "readFileBB" || $parts[1] == "readFileSA"){
                $auth = true;
            }

            if( strrpos($_GET['rota'], "/incodde/") !== false ) {
                $auth = true;
            }

            // forbidden
            if (!$auth) {
                $message = array(
                    'message' => array(
                        'text'=> "Request forbidden!",
                        'type'=>'E'
                    )
                );
                echo json_encode($message); die;
            }
            
            // Authorized requisitions
            $request = new \MilesBench\Request\Request();
                
            if(!isset($_POST['hashId']) && isset($jwt)) {
                $_POST = array_merge($_POST, array('hashId' => $jwt));
            }

            if(isset($BusinessPartner)) {
                $_POST = array_merge($_POST, array('businesspartner' => $BusinessPartner));
            }
            
            // file upload routes
            if($parts[1] == "saveFile" || $parts[1] == "readFile" || $parts[1] == "saveProfilePicture" || $parts[1] == "saveFileScheduled" || $parts[1] == "saveBilletOrder" || $parts[1] == "readFileBB" || $parts[1] == "readFileSA"){
                $request->setRow(array_merge($_POST, $_FILES));
            } else {
                $request->setRow($_POST);
            }
            if( strrpos($_GET['rota'], "/incodde/") !== false 
                && strrpos($_GET['rota'], "/newOrder/") !== false 
                && strrpos($_GET['rota'], "/updateOrderStatus/") !== false 
                && strrpos($_GET['rota'], "/removerRobo/") !== false
                && strrpos($_GET['rota'], "/checkOrderBot/") !== false
                && strrpos($_GET['rota'], "/cancelOrder/") !== false ) {
                $request->setRow(file_get_contents('php://input'));
            }

            $response = new \MilesBench\Request\Response();
            $controller->$parts[1]($request, $response);

            // seding response
            echo $response; die;
        };

        $callController($routes[$route]);

    } else if(isset($_SERVER['HTTP_HASHID']) && $_SERVER['HTTP_HASHID'] == '7b05c92600753671649fd8e539e731b4') {
        // busca ideal key
        require_once 'buscaIdealRoutes.php';

        // forbidden
        if (!isset($routes[$route])) {
            $message = array(
                'message' => array(
                    'text'=> "Request forbidden!",
                    'type'=>'E'
                )
            );
            echo json_encode($message); die;
        }

        // Authorized requisitions
        $parts = explode('::', $routes[$route]);
        $controller = new $parts[0]();

        $request = new \MilesBench\Request\Request();
        $request->setRow(file_get_contents('php://input'));
        $response = new \MilesBench\Request\Response();
        $controller->$parts[1]($request, $response);

        // seding response
        echo $response; die;

    } else if(isset($_SERVER['HTTP_HASHID']) && $_SERVER['HTTP_HASHID'] == '9207cb34d5e697b4ca13c03656b84eef') {
        // oktoplus key
        require_once 'oktoplusRoutes.php';

        $_POST['hashId'] = '9207cb34d5e697b4ca13c03656b84eef';

        // forbidden
        if (!isset($routes[$route])) {
            $message = array(
                'message' => array(
                    'text'=> "Request forbidden!",
                    'type'=>'E'
                )
            );
            echo json_encode($message); die;
        }

        // Authorized requisitions
        $parts = explode('::', $routes[$route]);
        $controller = new $parts[0]();

        $request = new \MilesBench\Request\Request();
        $request->setRow(file_get_contents('php://input'));
        $response = new \MilesBench\Request\Response();
        $controller->$parts[1]($request, $response);

        // seding response
        echo $response; die;

    } else if(isset($_SERVER['HTTP_HASHID']) && $_SERVER['HTTP_HASHID'] == 'fd0ab7097fb7119900febac7e3875218') {
        // oktoplus key
        require_once 'incoddeRoutes.php';

        $_POST['hashId'] = 'fd0ab7097fb7119900febac7e3875218';

        // forbidden
        if (!isset($routes[$route])) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            $message = array(
                'message' => array(
                    'text'=> "Request forbidden!",
                    'type'=>'E'
                )
            );
            echo json_encode($message); die;
        }

        // Authorized requisitions
        $parts = explode('::', $routes[$route]);
        $controller = new $parts[0]();

        $request = new \MilesBench\Request\Request();
        $request->setRow(file_get_contents('php://input'));
        $response = new \MilesBench\Request\Response();
        $controller->$parts[1]($request, $response);

        // seding response
        echo $response; die;

    } else {
        $message = array(
            'message' => array(
                'text'=> "Rota $route inválida!",
                'type'=>'E'
            )
        );

        echo json_encode($message); die;
    }
}
