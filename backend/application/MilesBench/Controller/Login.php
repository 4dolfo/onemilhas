<?php
namespace MilesBench\Controller;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

/**
 * Description of Pedido
 *
 * @author tulio
 */

use \Firebase\JWT\JWT;

class Login {

    public function login(Request $request, Response $response) {
        $dados = $request->getRow();
        $error = true;
        $ferias = false;
        $foraHorario = false;
        $hashId = '';
        $permissions = array();

        $em = Application::getInstance()->getEntityManager();

        if (isset($dados['email'])) {
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $dados['email'], 'partnerType' => 'U'));
            if(!$BusinessPartner) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $dados['email'], 'partnerType' => 'U_P'));
            }
            if(!$BusinessPartner) {
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $dados['email'], 'partnerType' => 'U_D'));
            }
            if(!$BusinessPartner) {		
                $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $dados['email'], 'partnerType' => 'C'));		
            }

            if (isset($BusinessPartner)) {

                // if($_GET['rota']) {

                //     $UsersRequests = new \UsersRequests();
                //     $UsersRequests->setRequest($_GET['rota']);
                //     $UsersRequests->setBusinesspartner($BusinessPartner);
                //     $UsersRequests->setIssueDate(new \DateTime());
                //     $UsersRequests->setIp($_SERVER['REMOTE_ADDR']);

                //     $ch = curl_init();
                //     curl_setopt_array($ch, array(
                //         CURLOPT_RETURNTRANSFER => 1,
                //         // curl_setopt($ch, CURLOPT_URL, 'https://ipapi.co/186.206.254.20/json'),
                //         curl_setopt($ch, CURLOPT_URL, 'https://ipapi.co/'.$_SERVER['REMOTE_ADDR'].'/json'),
                //         CURLOPT_USERAGENT => 'Codular Sample cURL Request'
                //     ));
                //     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
                //     curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                //     $result = json_decode(curl_exec($ch), true);
                //     if($result != null) {
                //         if(isset($result['country'])) {
                //             $UsersRequests->setLocation($result['country']);
                //         }
                //         if(isset($result['city'])) {
                //             $UsersRequests->setCity($result['city']);
                //         }
                //         if(isset($result['region'])) {
                //             $UsersRequests->setRegion($result['region']);
                //         }
                //     }
                //     $em->persist($UsersRequests);
                //     $em->flush($UsersRequests);
                // }


                $partnerType = 'gestao';

                $token = array(
                    "email" => $BusinessPartner->getEmail(),
                    "id" => $BusinessPartner->getId(),
                    "password" => md5($BusinessPartner->getPassword()),
                    "partnerType" => $partnerType,
                    "date" => date('Y-m-d')
                );
                $hashId = JWT::encode($token, \MilesBench\Util::key);

                if ($BusinessPartner->getWebserviceLogin() == 'Y') {
                    if ($dados['hashId'] == '461c52e9hs1e197rb3d79c92f97167a7') {
                        $error = false;
                        $dataset = array();
                        $dataset[] = array(
                            'hashId' => $hashId
                        );
                    }
                } else if (isset($dados['password'])) {
                    if ($dados['password'] == $BusinessPartner->getPassword() && $BusinessPartner->getStatus() != 'Bloqueado') {

                        $UserPermission = $em->getRepository('UserPermission')->findOneBy(array('user' => $BusinessPartner->getId()));
                        $error = false;

                        $today = new \DateTime();

                        if($UserPermission && $BusinessPartner->getIsMaster() != 'true' && !strpos($BusinessPartner->getPartnerType(), "D") ) {

                            if($today->format('l') == "Monday") {
                                if(strtotime($today->format('H:i:s')) < strtotime($UserPermission->getMondayIn()->format('H:i:s')) || strtotime($today->format('H:i:s')) > strtotime($UserPermission->getMondayOut()->format('H:i:s'))) {
                                    $error = true;
                                    $foraHorario = true;
                                }
                            } elseif ($today->format('l') == "Tuesday") {
                                if(strtotime($today->format('H:i:s')) < strtotime($UserPermission->getTuesdayIn()->format('H:i:s')) || strtotime($today->format('H:i:s')) > strtotime($UserPermission->getTuesdayOut()->format('H:i:s'))) {
                                    $error = true;
                                    $foraHorario = true;
                                }
                            } elseif ($today->format('l') == "Wednesday") {
                                if(strtotime($today->format('H:i:s')) < strtotime($UserPermission->getWednesdayIn()->format('H:i:s')) || strtotime($today->format('H:i:s')) > strtotime($UserPermission->getWednesdayOut()->format('H:i:s'))) {
                                    $error = true;
                                    $foraHorario = true;
                                }
                            } elseif ($today->format('l') == "Thursday") {
                                if(strtotime($today->format('H:i:s')) < strtotime($UserPermission->getThursdayIn()->format('H:i:s')) || strtotime($today->format('H:i:s')) > strtotime($UserPermission->getThursdayOut()->format('H:i:s'))) {
                                    $error = true;
                                    $foraHorario = true;
                                }
                            } elseif ($today->format('l') == "Friday") {
                                if(strtotime($today->format('H:i:s')) < strtotime($UserPermission->getFridayIn()->format('H:i:s')) || strtotime($today->format('H:i:s')) > strtotime($UserPermission->getFridayOut()->format('H:i:s'))) {
                                    $error = true;
                                    $foraHorario = true;
                                }
                            } elseif ($today->format('l') == "Saturday") {
                                if(strtotime($today->format('H:i:s')) < strtotime($UserPermission->getSaturdayIn()->format('H:i:s')) || strtotime($today->format('H:i:s')) > strtotime($UserPermission->getSaturdayOut()->format('H:i:s'))) {
                                    $error = true;
                                    $foraHorario = true;
                                }
                            } elseif ($today->format('l') == "Sunday") {
                                if(strtotime($today->format('H:i:s')) < strtotime($UserPermission->getSundayIn()->format('H:i:s')) || strtotime($today->format('H:i:s')) > strtotime($UserPermission->getSundayOut()->format('H:i:s'))) {
                                    $error = true;
                                    $foraHorario = true;
                                }
                            } 
                            if(($UserPermission->getOnVacation() == 'true') && (strtotime($today->format('Y-m-d')) < strtotime($UserPermission->getVacationEnd()->format('Y-m-d')))) {
                                $error = true;
                                $ferias = true;
                            }
                        }
                        
                        $purchase = false;
                        $wizardPurchase = false;
                        $sale = false;
                        $wizardSale = false;
                        $milesBench = false;
                        $financial = false;
                        $creditCard = false;
                        $users = false;
                        $changeMiles = false;
                        $changeSale = false;
                        $commercial = false;
                        $permission = false;
                        $dealer = false;
                        $pagseguro = false;
                        $internRefund = false;
                        $internCommercial = false;
                        $humanResources = false;
                        $salePlansEdit = false;
                        $conference = false;
                        $onlineOnlineOrder = false;
                        $onlineBalanceOrder = false;
                        $onlineCardsInUse = false;
                        $purchaseProvider = false;
                        $purchasePaymentPruchase = false;
                        $purchaseEndPruchase = false;
                        $purchasePruchases = false;
                        $purchaseCardsPendency = false;
                        $saleClients = false;
                        $saleBalanceClients = false;
                        $saleFutureBoardings = false;
                        $saleRefundCancel = false;
                        $saleRevertRefund = false;
                        $client = false;
                        $wizardSaleEvent = false;

                        if($UserPermission) {
                            $purchase = ($UserPermission->getPurchase() == 'true');
                            $wizardPurchase = ($UserPermission->getWizardPurchase() == 'true');
                            $sale = ($UserPermission->getSale() == 'true');
                            $wizardSale = ($UserPermission->getWizardSale() == 'true');
                            $milesBench = ($UserPermission->getMilesBench() == 'true');
                            $financial = ($UserPermission->getFinancial() == 'true');
                            $creditCard = ($UserPermission->getCreditCard() == 'true');
                            $users = ($UserPermission->getUsers() == 'true');
                            $changeMiles = ($UserPermission->getChangeMiles() == 'true');
                            $changeSale = ($UserPermission->getChangeSale() == 'true');
                            $commercial = ($UserPermission->getCommercial() == 'true');
                            $permission = ($UserPermission->getPermission() == 'true');
                            $dealer = strpos($BusinessPartner->getPartnerType(), "D") !== false;
                            $pagseguro = ($UserPermission->getPagseguro() == 'true');
                            $internRefund = ($UserPermission->getInternRefund() == 'true');
                            $internCommercial = ($UserPermission->getInternCommercial() == 'true');
                            $humanResources = ($UserPermission->getHumanResources() == 'true');
                            $salePlansEdit = ($UserPermission->getSalePlansEdit() == 'true');
                            $conference = ($UserPermission->getConference() == 'true');
                            $onlineOnlineOrder = ($UserPermission->getOnlineOnlineOrder() == 'true');
                            $onlineBalanceOrder = ($UserPermission->getOnlineBalanceOrder() == 'true');
                            $onlineCardsInUse = ($UserPermission->getOnlineCardsInUse() == 'true');
                            $purchaseProvider = ($UserPermission->getPurchaseProvider() == 'true');
                            $purchasePaymentPruchase = ($UserPermission->getPurchasePaymentPruchase() == 'true');
                            $purchaseEndPruchase = ($UserPermission->getPurchaseEndPruchase() == 'true');
                            $purchasePruchases = ($UserPermission->getPurchasePruchases() == 'true');
                            $purchaseCardsPendency = ($UserPermission->getPurchaseCardsPendency() == 'true');
                            $saleClients = ($UserPermission->getSaleClients() == 'true');
                            $saleBalanceClients = ($UserPermission->getSaleBalanceClients() == 'true');
                            $saleFutureBoardings = ($UserPermission->getSaleFutureBoardings() == 'true');
                            $saleRefundCancel = ($UserPermission->getSaleRefundCancel() == 'true');
                            $saleRevertRefund = ($UserPermission->getSaleRevertRefund() == 'true');
                            $wizardSaleEvent = ($UserPermission->getWizarSaleEvent() == 'true');
                        }
 
                        if($BusinessPartner->getPartnerType() == 'C') {
                            $client = true;
                        }

                        $sales = 0;
                        if(($BusinessPartner->getIsMaster() != 'true') && $wizardSale == "true") {
                            $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');
                            $sql = "select COUNT(s.id) as sales FROM Sale s where s.user = '".$BusinessPartner->getId()."' and s.issueDate >= '".$monthsAgo->format('Y-m-d')."' ";
                            $query = $em->createQuery($sql);
                            $Sales = $query->getResult();

                            $sales = $Sales[0]['sales'];
                        }

                        $purchases = 0;
                        if( ($BusinessPartner->getIsMaster() != 'true') && $wizardPurchase == "true") {
                            $monthsAgo = (new \DateTime())->modify('today')->modify('first day of this month');
                            $sql = "select COUNT(p.id) as purchases FROM Purchase p where p.user = '".$BusinessPartner->getId()."' and p.purchaseDate >= '".$monthsAgo->format('Y-m-d')."' ";
                            $query = $em->createQuery($sql);
                            $Purchases = $query->getResult();

                            $purchases = $Purchases[0]['purchases'];
                        }

                        $city = '';
                        if($BusinessPartner->getCity()) {
                            $city = $BusinessPartner->getCity()->getName();
                        }

                        $permissions = array(
                            'purchase' => $purchase,
                            'wizardPurchase' => $wizardPurchase,
                            'sale' => $sale,
                            'wizardSale' => $wizardSale,
                            'milesBench' => $milesBench,
                            'financial' => $financial,
                            'creditCard' => $creditCard,
                            'users' => $users,
                            'changeMiles' => $changeMiles,
                            'changeSale' => $changeSale,
                            'commercial' => $commercial,
                            'permission' => $permission,
                            'dealer' => $dealer,
                            'pagseguro' => $pagseguro,
                            'internRefund' => $internRefund,
                            'internCommercial' => $internCommercial,
                            'humanResources' => $humanResources,
                            'client' => $client,
                            'salePlansEdit' => $salePlansEdit,
                            'conference' => $conference,
                            'onlineOnlineOrder' => $onlineOnlineOrder,
                            'onlineBalanceOrder' => $onlineBalanceOrder,
                            'onlineCardsInUse' => $onlineCardsInUse,
                            'purchaseProvider' => $purchaseProvider,
                            'purchasePaymentPruchase' => $purchasePaymentPruchase,
                            'purchaseEndPruchase' => $purchaseEndPruchase,
                            'purchasePruchases' => $purchasePruchases,
                            'purchaseCardsPendency' => $purchaseCardsPendency,
                            'saleClients' => $saleClients,
                            'saleBalanceClients' => $saleBalanceClients,
                            'saleFutureBoardings' => $saleFutureBoardings,
                            'saleRefundCancel' => $saleRefundCancel,
                            'saleRevertRefund' => $saleRevertRefund,
                            'wizardSaleEvent' => $wizardSaleEvent,
                        );

                        $lastPasswordDate = '';
                        $date = new \DateTime();
                        $forceChangesPassword = true;
                        if($BusinessPartner->getLastPasswordDate()) {
                            $lastPasswordDate = $BusinessPartner->getLastPasswordDate()->format('Y-m-d H:i:s');
                            if($date->diff($BusinessPartner->getLastPasswordDate())->days <= 60) {
                                $forceChangesPassword = false;
                            }
                        }

                        if(isset($dados['url_parameters']) && $dados['url_parameters'] != '') {
                            $code_token = str_replace('code=', '', $dados['url_parameters']);
                            $BusinessPartner = \MilesBench\Controller\ContaAzul\Sale::exchangeAuthorizationCode($BusinessPartner, $code_token);
                            $em->persist($BusinessPartner);
                            $em->flush($BusinessPartner);
                        }

                        $dataset = array();
                        $dataset[] = array(
                            'id' => $BusinessPartner->getId(),
                            'name' => $BusinessPartner->getName(),
                            'email' => $BusinessPartner->getEmail(),
                            'acessName' => $BusinessPartner->getAcessName(),
                            'is_master' => $BusinessPartner->getIsMaster(),
                            'adress' => $BusinessPartner->getAdress(),
                            'phoneNumber' => $BusinessPartner->getPhoneNumber(),
                            'lastPasswordDate' => $lastPasswordDate,
                            'forceChangesPassword' => $forceChangesPassword,
                            'city' => $city,
                            'date' => date('Y-m-d'),
                            'hashId' => $hashId,
                            'sales' => (int)$sales,
                            'purchases' => (int)$purchases
                        );

                        $_POST['hashId'] = $hashId;
                    }
                }

                if (!($error)) {
                    $desc = "Login no gestão efetuado com sucesso.";
                    if(isset($_SERVER['REMOTE_ADDR'])){
                        $desc .= " IP:".$_SERVER['REMOTE_ADDR']."";
                    }

                    //Log de login no gestão
                    $SystemLog = new \SystemLog();
                    $SystemLog->setBusinesspartner($BusinessPartner);
					$SystemLog->setIssueDate(new \DateTime());
					$SystemLog->setDescription($desc);
					$SystemLog->setLogType('LOGIN');
					$em->persist($SystemLog);
					$em->flush($SystemLog);

                    $message = new \MilesBench\Message();
                    $message->setType(\MilesBench\Message::SUCCESS);
                    $message->setText('Login efetuado com sucesso.');
                    $response->addMessage($message);

                    $UserSession = $em->getRepository('UserSession')->findOneBy(array('email' => $dados['email']));
                    if (!(isset($UserSession))) {
                        $UserSession = new \UserSession();
                        $UserSession->setEmail($BusinessPartner->getEmail());
                    }
                    $UserSession->setHashid($hashId);
                    $em->persist($UserSession);
                    $em->flush($UserSession);

                    // setting header request
                    header('hashId: '.$hashId);

                    header('permissions: '.base64_encode(json_encode($permissions)));

                    $response->setDataset($dataset);
                }
            }
        }

        if ($error) {
            if($ferias){
                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::ERROR);
                $message->setText('Impossível logar durante as férias.');
                $response->addMessage($message);

                $desc = "Tentativa de login no gestão durante férias.";
                if(isset($_SERVER['REMOTE_ADDR'])){
                    $desc .= " IP:".$_SERVER['REMOTE_ADDR']."";
                }

                //Log de login no gestão
                $SystemLog = new \SystemLog();
                $SystemLog->setBusinesspartner($BusinessPartner);
				$SystemLog->setIssueDate(new \DateTime());
				$SystemLog->setDescription($desc);
				$SystemLog->setLogType('LOGIN');
				$em->persist($SystemLog);
				$em->flush($SystemLog);
            }
            elseif($foraHorario){
                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::ERROR);
                $message->setText('Impossível logar fora do horário previsto.');
                $response->addMessage($message);

                $desc = "Tentativa de login no gestão fora do horário previsto.";
                if(isset($_SERVER['REMOTE_ADDR'])){
                    $desc .= " IP:".$_SERVER['REMOTE_ADDR']."";
                }

                //Log de login no gestão
                $SystemLog = new \SystemLog();
                $SystemLog->setBusinesspartner($BusinessPartner);
				$SystemLog->setIssueDate(new \DateTime());
				$SystemLog->setDescription($desc);
				$SystemLog->setLogType('LOGIN');
				$em->persist($SystemLog);
				$em->flush($SystemLog);
            }
            else{
                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::ERROR);
                $message->setText('Usuário ou senha inválidos.');
                $response->addMessage($message);  
            }
        }
    }

    public function userGroup(Request $request, Response $response) {
        $dados = $request->getRow();
        $em = Application::getInstance()->getEntityManager();

        if(isset($dados['email'])){
            $BusinessPartner = $em->getRepository('Businesspartner')->findOneBy(array('email' => $dados['email']));
            if(isset($BusinessPartner)){
                $UserGroup = $em->getRepository('UserGroup')->findOneBy(array('user' => $BusinessPartner));
                if(isset($UserGroup)){
                    $dataset = array(
                        'firstIssue' => $UserGroup->getFirstIssue(),
                        'emissionTrack' => $UserGroup-> getEmissionTrack(),
                        'limitTrack' => $UserGroup-> getLimitTrack(),
                        'futureBoardingsTrack' => $UserGroup->getFutureBoardingsTrack(),
                        'difficultContactTrack' => $UserGroup->getDifficultContactTrack(),
                        'statusPendingReleaseTrack' => $UserGroup->getStatusPendingReleaseTrack(),
                        'statusCreditAnalysisTrack' => $UserGroup->getStatusCreditAnalysisTrack(),
                        'cardsBloqueds' => $UserGroup->getCardsBloqueds(),
                        'clientsTrack' => $UserGroup->getClientsTrack()
                    );
                    $response->setDataset($dataset);
                }
            }
            else{
                $message = new \MilesBench\Message();
                $message->setType(\MilesBench\Message::ERROR);
                $message->setText('Este email não foi encontrado na base de dados.');
                $response->addMessage($message);
            }
        }
    }
}
