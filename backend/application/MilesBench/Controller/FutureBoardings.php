<?php

namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class FutureBoardings {
    public function removeAccents($string){
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç|Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$string);
	}

    public function check(Request $request, Response $response) {
        $dados = $request->getRow();
        
        $req = new \MilesBench\Request\Request();
        $resp = new \MilesBench\Request\Response();
        $this->checkAvianca($req, $resp);
        $this->checkGol($req, $resp);
        $this->checkLatam($req, $resp);
        $this->checkAzul($req, $resp);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro salvo com sucesso');
        $response->addMessage($message);
    }

    public function checkAvianca(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $html = "Companhia: AVIANCA<br>Data: ".(new \DateTime())->format('d/m/Y')."<br><br>";

        $csv = [['localizador', 'dt embarque', 'dt emissao', 'status', 'observacoes', 'Erro']];
        $nok = 0;
        $ok = 0;

        // AVIANCA
        $query = " select s.* from sale s ".
            " where s.status = 'Emitido' AND s.airline_id = 4 and s.boarding_date BETWEEN '".(new \DateTime())->format('Y-m-d')."' and ".
            " '".(new \DateTime())->modify('+3 day')->format('Y-m-d')."' group by s.flight_locator ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {
            
            $sale = new Sale;
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();
            $req->setRow(
                array( 
                    'data' => array('flightLocator' => $row['flight_locator'])
                )
            );
            $sale->loadSaleByFilter($req, $resp);
            $sales = $resp->getDataSet();

            $show = false;

            $names = explode(' ', $sales[0]['paxName']);

            $url = "http://api-voos-prd-clone2.sa-east-1.elasticbeanstalk.com/api/avianca/checkin?";
            $url .= "locator=".$row['flight_locator']."&date=".(new \DateTime($row['boarding_date']))->format('d/m/Y');
            $url .= "&firstName=".$names[0]."&lastName=".$names[ count($names) -1 ];

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => 1,
                curl_setopt($ch, CURLOPT_URL, $url),
                CURLOPT_USERAGENT => 'Codular Sample cURL Request'
            ));
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Content-type: application/json",
            "Authorization: 92F85D8BFDEFEDF5214688CC6A5AA"));

            $result = curl_exec($ch);
            $return = json_decode($result, true);

            $namePax = '';
            if(!isset($return['paxs']) || count($return['paxs']) == 0) {
                $show = true;
            }

            foreach ($return['paxs'] as $key => $value) {
                $paxCheck = false;
                $namePax = $value['name'] . ' ' . $value['lastName'];
                foreach ($sales as $sale) {
                    if($namePax == $sale['paxName']) {
                        $paxCheck = true;
                    }
                }
                if(!$paxCheck) {
                    $show = true;
                }
            }

            if(!isset($return['flights']) || count($return['flights']) == 0) {
                $show = true;
            }
            foreach ($return['flights'] as $flight) {
                foreach ($flight['paths'] as $path) {
                    $flightCheck = false;
                    foreach ($sales as $sale) {
                        if($sale['to'].(new \DateTime($sale['landingDate']))->format('Y-m-d H:i:s') == $path['destino'].(new \DateTime($path['desembarque']))->format('Y-m-d H:i:s')) {
                            $flightCheck = true;
                        }
                    }
                    if(!$flightCheck) {
                        $show = true;
                    }
                }
            }

            
            $csv[] = [
                $row['flight_locator'],
                (new \DateTime($row['boarding_date']))->format('d/m/Y'),
                (new \DateTime($row['issue_date']))->format('d/m/Y'),
                ($show ? 'ERRO' : 'OK'),
                '',
                ( isset($return['err']) ? $return['err'] : '' )
            ];

            if($show) {
                $nok++;
            } else {
                $ok++;
            }
        }

        $html .= "Ok: $ok<br>Não: $nok";

        $fp = fopen('conferencias_avianca_'.(new \DateTime())->format('dmY').'.csv', 'w');
        foreach ($csv as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $file_name_with_full_path = getcwd().'/conferencias_avianca_'.(new \DateTime())->format('dmY').'.csv';
        $target_url = \MilesBench\Util::email_url.'/save';
        if (function_exists('curl_file_create')) { 
            $cFile = curl_file_create($file_name_with_full_path);
        } else { // 
            $cFile = '@' . realpath($file_name_with_full_path);
        }
        $post = array('file_contents' => $cFile);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        curl_close($ch);

        $email1 = 'adm@onemilhas.com.br';
        $email2 = 'emissao@onemilhas.com.br';
        $postfields = array(
            'content' => $html,
            'partner' => $email2,
            'from' => $email1,
            'subject' => 'CONFERENCIA - AVIANCA - '.(new \DateTime())->format('d/m/Y'),
            'type' => '',
            'attachment' => 'conferencias_avianca_'.(new \DateTime())->format('dmY').'.csv'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro salvo com sucesso');
        $response->addMessage($message);
    }

    public function checkGol(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $html = "Companhia: GOL<br>Data: ".(new \DateTime())->format('d/m/Y')."<br><br>";

        $csv = [['localizador', 'dt embarque', 'dt emissao', 'status', 'observacoes', 'Erro']];
        $nok = 0;
        $ok = 0;

        // GOL
        $query = " select s.* from sale s ".
            " where s.status = 'Emitido' AND s.airline_id = 2 and s.boarding_date BETWEEN '".(new \DateTime())->modify('+3 hours')->format('Y-m-d H:i:s')."' and ".
            " '".(new \DateTime())->modify('+2 day')->format('Y-m-d')."' group by s.flight_locator ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {

            $sale = new Sale;
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();
            $req->setRow(
                array( 
                    'data' => array('flightLocator' => $row['flight_locator'])
                )
            );
            $sale->loadSaleByFilter($req, $resp);
            $sales = $resp->getDataSet();
            $OnlinePax = $em->getRepository('OnlinePax')->findBy( array( 'order' => $sales[0]['externalId'] ) );

            $show = false;

            $names = explode(' ', $sales[0]['paxName']);

            $url = "http://api-voos-prd-clone2.sa-east-1.elasticbeanstalk.com/api/gol/checkin?";
            $url .= "locator=".$row['flight_locator']."&date=".(new \DateTime($row['boarding_date']))->format('d/m/Y');
            $url .= "&firstName=".self::removeAccents($names[0])."&lastName=".self::removeAccents($names[ count($names) -1 ]);

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => 1,
                curl_setopt($ch, CURLOPT_URL, $url)
            ));
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Authorization: 92F85D8BFDEFEDF5214688CC6A5AA")
            );

            $result = curl_exec($ch);
            $return = json_decode($result, true);

            $namePax = '';
            if(isset($return['err']) || $result == '' ) {
                $show = true;
            } else {
                if(count($OnlinePax) != count($return['paxs'])) {
                    $show = true;
                }

                if(!isset($return['paxs']) || count($return['paxs']) == 0) {
                    $show = true;
                }
                if(isset($return['paxs'])) {
                    foreach ($return['paxs'] as $key => $value) {
                        // $paxCheck = false;
                        // $namePax = $value['name'] . ' ' . $value['lastName'];
                        // foreach ($sales as $sale) {
                        //     if($namePax == $sale['paxName']) {
                        //         $paxCheck = true;
                        //     }
                        // }
                        // if(!$paxCheck) {
                        //     $show = true;
                        // }
                    }
                }
    
                if(!isset($return['flights']) || count($return['flights']) == 0) {
                    $show = true;
                }
                if(isset($return['flights'])) {
                    foreach ($return['flights'] as $flight) {
                        // foreach ($flight['paths'] as $path) {
                        //     $flightCheck = false;
                        //     foreach ($sales as $sale) {
                        //         if($sale['to'].(new \DateTime($sale['landingDate']))->format('Y-m-d H:i:s') == $path['destino'].(new \DateTime($path['desembarque']))->format('Y-m-d H:i:s')) {
                        //             $flightCheck = true;
                        //         }
                        //     }
                        //     if(!$flightCheck) {
                        //         $show = true;
                        //     }
                        // }
                    }
                }
            }

            $csv[] = [
                $row['flight_locator'],
                (new \DateTime($row['boarding_date']))->format('d/m/Y'),
                (new \DateTime($row['issue_date']))->format('d/m/Y'),
                ($show ? 'ERRO' : 'OK' ),
                ( strpos($row['flight'],'G3') === false ? 'Companhia parceira' : '' ),
                ( isset($return['err']) ? $return['err'] : '' )
            ];

            if($show) {
                $nok++;
            } else {
                $ok++;
            }
        }

        $html .= "Ok: $ok<br>Não: $nok";

        $fp = fopen('conferencias_gol_'.(new \DateTime())->format('dmY').'.csv', 'w');
        foreach ($csv as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $file_name_with_full_path = getcwd().'/conferencias_gol_'.(new \DateTime())->format('dmY').'.csv';
        $target_url = \MilesBench\Util::email_url.'/save';
        if (function_exists('curl_file_create')) { 
            $cFile = curl_file_create($file_name_with_full_path);
        } else { // 
            $cFile = '@' . realpath($file_name_with_full_path);
        }
        $post = array('file_contents' => $cFile);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        curl_close($ch);

        $email1 = 'adm@onemilhas.com.br';
        $email2 = 'emissao@onemilhas.com.br';
        $postfields = array(
            'content' => $html,
            'partner' => $email2,
            'from' => $email1,
            'subject' => 'CONFERENCIA - GOL - '.(new \DateTime())->format('d/m/Y'),
            'type' => '',
            'attachment' => 'conferencias_gol_'.(new \DateTime())->format('dmY').'.csv'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro salvo com sucesso');
        $response->addMessage($message);
    }

    public function checkLatam(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $html = "Companhia: LATAM<br>Data: ".(new \DateTime())->format('d/m/Y')."<br><br>";

        $csv = [['localizador', 'dt embarque', 'dt emissao', 'status', 'observacoes', 'Erro']];
        $nok = 0;
        $ok = 0;

        // LATAM
        $query = " select s.* from sale s ".
            " where s.status = 'Emitido' AND s.airline_id = 1 and s.boarding_date BETWEEN '".(new \DateTime())->modify('+3 hours')->format('Y-m-d H:i:s')."' and ".
            " '".(new \DateTime())->modify('+3 day')->format('Y-m-d')."' group by s.flight_locator ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {

            $sale = new Sale;
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();
            $req->setRow(
                array( 
                    'data' => array('flightLocator' => $row['flight_locator'])
                )
            );
            $sale->loadSaleByFilter($req, $resp);
            $sales = $resp->getDataSet();

            $show = false;

            $names = explode(' ', $sales[0]['paxName']);

            $url = "http://api-voos-prd-clone2.sa-east-1.elasticbeanstalk.com/api/latam/checkin?";
            $url .= "locator=".$row['flight_locator']."&date=".(new \DateTime($row['boarding_date']))->format('d/m/Y');
            $url .= "&firstName=".self::removeAccents($names[0])."&lastName=".self::removeAccents($names[ count($names) -1 ]);

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => 1,
                curl_setopt($ch, CURLOPT_URL, $url)
            ));
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Authorization: 92F85D8BFDEFEDF5214688CC6A5AA")
            );

            $result = curl_exec($ch);
            $return = json_decode($result, true);

            $namePax = '';
            if(isset($return['err'])) {
                $show = true;
            } else {
                if(!isset($return['paxs']) || count($return['paxs']) == 0) {
                    $show = true;
                }
                if(isset($return['paxs'])) {
                    foreach ($return['paxs'] as $key => $value) {
                        // $paxCheck = false;
                        // $namePax = $value['name'] . ' ' . $value['lastName'];
                        // foreach ($sales as $sale) {
                        //     if($namePax == $sale['paxName']) {
                        //         $paxCheck = true;
                        //     }
                        // }
                        // if(!$paxCheck) {
                        //     $show = true;
                        // }
                    }
                }
    
                if(!isset($return['flights']) || count($return['flights']) == 0) {
                    $show = true;
                }
                if(isset($return['flights'])) {
                    foreach ($return['flights'] as $flight) {
                        // foreach ($flight['paths'] as $path) {
                        //     $flightCheck = false;
                        //     foreach ($sales as $sale) {
                        //         if($sale['to'].(new \DateTime($sale['landingDate']))->format('Y-m-d H:i:s') == $path['destino'].(new \DateTime($path['desembarque']))->format('Y-m-d H:i:s')) {
                        //             $flightCheck = true;
                        //         }
                        //     }
                        //     if(!$flightCheck) {
                        //         $show = true;
                        //     }
                        // }
                    }
                }
            }

            $csv[] = [
                $row['flight_locator'],
                (new \DateTime($row['boarding_date']))->format('d/m/Y'),
                (new \DateTime($row['issue_date']))->format('d/m/Y'),
                ($show ? 'ERRO' : 'OK'),
                '',
                ( isset($return['err']) ? $return['err'] : '' )
            ];

            if($show) {
                $nok++;
            } else {
                $ok++;
            }
        }

        $html .= "Ok: $ok<br>Não: $nok";

        $fp = fopen('conferencias_latam_'.(new \DateTime())->format('dmY').'.csv', 'w');
        foreach ($csv as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $file_name_with_full_path = getcwd().'/conferencias_latam_'.(new \DateTime())->format('dmY').'.csv';
        $target_url = \MilesBench\Util::email_url.'/save';
        if (function_exists('curl_file_create')) { 
            $cFile = curl_file_create($file_name_with_full_path);
        } else { // 
            $cFile = '@' . realpath($file_name_with_full_path);
        }
        $post = array('file_contents' => $cFile);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        curl_close($ch);

        $email1 = 'adm@onemilhas.com.br';
        $email2 = 'emissao@onemilhas.com.br';
        $postfields = array(
            'content' => $html,
            'partner' => $email2,
            'from' => $email1,
            'subject' => 'CONFERENCIA - LATAM - '.(new \DateTime())->format('d/m/Y'),
            'type' => '',
            'attachment' => 'conferencias_latam_'.(new \DateTime())->format('dmY').'.csv'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro salvo com sucesso');
        $response->addMessage($message);
    }

    public function checkAzul(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();
        $QueryBuilder = Application::getInstance()->getQueryBuilder();
        $html = "Companhia: AZUL<br>Data: ".(new \DateTime())->format('d/m/Y')."<br><br>";

        $csv = [['localizador', 'dt embarque', 'dt emissao', 'status', 'observacoes', 'Erro']];
        $nok = 0;
        $ok = 0;

        // AZUL
        $query = " select s.* from sale s ".
            " where s.status = 'Emitido' AND s.airline_id = 3 and s.boarding_date BETWEEN '".(new \DateTime())->modify('+3 hours')->format('Y-m-d H:i:s')."' and ".
            " '".(new \DateTime())->modify('+3 day')->format('Y-m-d')."' group by s.flight_locator ";
        $stmt = $QueryBuilder->query($query);
        while ($row = $stmt->fetch()) {

            $sale = new Sale;
            $req = new \MilesBench\Request\Request();
            $resp = new \MilesBench\Request\Response();
            $req->setRow(
                array( 
                    'data' => array('flightLocator' => $row['flight_locator'])
                )
            );
            $sale->loadSaleByFilter($req, $resp);
            $sales = $resp->getDataSet();

            $show = false;

            $names = explode(' ', $sales[0]['paxName']);

            // $url = "http://api-voos-prd-clone2.sa-east-1.elasticbeanstalk.com/api/azul/checkin?";
            $url = "http://api-voos-hml.us-east-1.elasticbeanstalk.com/api/azul/checkin?";
            $url .= "locator=".$row['flight_locator']."&date=".(new \DateTime($row['boarding_date']))->format('d/m/Y');
            $url .= "&firstName=".self::removeAccents($names[0])."&lastName=".self::removeAccents($names[ count($names) -1 ]);

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => 1,
                curl_setopt($ch, CURLOPT_URL, $url)
            ));
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("Authorization: 92F85D8BFDEFEDF5214688CC6A5AA")
            );

            $result = curl_exec($ch);
            $return = json_decode($result, true);

            $namePax = '';
            if(isset($return['err'])) {
                $show = true;
            } else {
                if(!isset($return['paxs']) || count($return['paxs']) == 0) {
                    $show = true;
                }
                if(isset($return['paxs'])) {
                    foreach ($return['paxs'] as $key => $value) {
                    }
                }
    
                if(!isset($return['flights']) || count($return['flights']) == 0) {
                    $show = true;
                }
                if(isset($return['flights'])) {
                    foreach ($return['flights'] as $flight) {
                    }
                }
            }

            $csv[] = [
                $row['flight_locator'],
                (new \DateTime($row['boarding_date']))->format('d/m/Y'),
                (new \DateTime($row['issue_date']))->format('d/m/Y'),
                ($show ? 'ERRO' : 'OK'),
                '',
                ( isset($return['err']) ? $return['err'] : '' )
            ];

            if($show) {
                $nok++;
            } else {
                $ok++;
            }
        }

        $html .= "Ok: $ok<br>Não: $nok";

        $fp = fopen('conferencias_azul_'.(new \DateTime())->format('dmY').'.csv', 'w');
        foreach ($csv as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $file_name_with_full_path = getcwd().'/conferencias_azul_'.(new \DateTime())->format('dmY').'.csv';
        $target_url = \MilesBench\Util::email_url.'/save';
        if (function_exists('curl_file_create')) { 
            $cFile = curl_file_create($file_name_with_full_path);
        } else { // 
            $cFile = '@' . realpath($file_name_with_full_path);
        }
        $post = array('file_contents' => $cFile);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        curl_close($ch);

        $email1 = 'adm@onemilhas.com.br';
        $email2 = 'emissao@onemilhas.com.br';
        $postfields = array(
            'content' => $html,
            'partner' => $email2,
            'from' => $email1,
            'subject' => 'CONFERENCIA - AZUL - '.(new \DateTime())->format('d/m/Y'),
            'type' => '',
            'attachment' => 'conferencias_azul_'.(new \DateTime())->format('dmY').'.csv'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \MilesBench\Util::email_url_ses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);

        $message = new \MilesBench\Message();
        $message->setType(\MilesBench\Message::SUCCESS);
        $message->setText('Registro salvo com sucesso');
        $response->addMessage($message);
    }
}
