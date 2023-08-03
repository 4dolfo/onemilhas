<?php

namespace MilesBench\Controller\Marketing;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use Aws\S3\S3Client;

class ModalPromos {

    public function load(Request $request, Response $response) {
        $em = Application::getInstance()->getEntityManager();
        $PlansPromotions = $em->getRepository('PlansPromotions')->findAll();

        $dataset = array();
        foreach($PlansPromotions as $item){

            $dataset[] = array(
                'id' => $item->getId(),
                'status' => $item->getStatus(),
                'startDate' => $item->getStartDate()->format('Y-m-d H:i:s'),
                'endDate' => $item->getEndDate()->format('Y-m-d H:i:s'),
                'urlImage' => $item->getUrlImage(),
                'plans' => $item->getPlans(),
                'airlines' => $item->getAirlines()
            );
        }
        $response->setDataset($dataset);
    }


    public function save(Request $request, Response $response) {
        $dados = $request->getRow();
        if (isset($dados['file'])) {
            $file = $dados['file'];
        }
        if (isset($dados['data'])) {
            $dados = $dados['data'];
        }

        try {
            $em = Application::getInstance()->getEntityManager();
            if (isset($dados['id'])) {
                $PlansPromotions = $em->getRepository('PlansPromotions')->find($dados['id']);
            } else {
                $PlansPromotions = new \PlansPromotions();
            }

            $PlansPromotions->setStatus($dados['status']);
            $PlansPromotions->setStartDate(new \DateTime($dados['startDate']));
            $PlansPromotions->setEndDate(new \DateTime($dados['endDate']));


            if(isset($file)) {
                foreach ($file as $key => $value) {
                    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "", $value);
                    $extension = explode('.', $file_name);
    
                    try {
                        $s3 = new \Aws\S3\S3Client([
                            'version' => 'latest',
                            'region'  => 'us-east-1',
                            'credentials' => array(
                                'key' => getenv('AWS_KEY'),
                                'secret'  => getenv('AWS_SECRET')
                            )
                        ]);
        
                        $bucket = 'mmspromos';
                        $keyname = $PlansPromotions->getId() . '/' . $extension[0] . '.' . $extension[1];
                        $filepath = getcwd()."/MilesBench/files/temp/".$file_name;
        
                        $result = $s3->putObject(array(
                            'Bucket' => $bucket,
                            'Key'    => $keyname,
                            'SourceFile' => $filepath,
                            'Body'   => '',
                            'ACL'    => 'public-read'
                        ));
                    } catch (S3Exception $e) {
                        var_dump($e);die;
                    }
                }
                
                $PlansPromotions->setUrlImage($result['ObjectURL']);
            } else {
                $PlansPromotions->setUrlImage('');
            }

            $PlansPromotions->setPlans($dados['plans']);
            $PlansPromotions->setAirlines($dados['airlines']);

            $em->persist($PlansPromotions);
            $em->flush($PlansPromotions);

            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::SUCCESS);
            $message->setText('Registro alterado com sucesso');
            $response->addMessage($message);

        } catch (\Exception $e) {
            $message = new \MilesBench\Message();
            $message->setType(\MilesBench\Message::ERROR);
            $message->setText($e->getMessage());
            $response->addMessage($message);
        }
    }

}