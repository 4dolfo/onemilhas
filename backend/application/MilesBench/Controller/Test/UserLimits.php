<?php

namespace MilesBench\Controller\Test;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

use MilesBench\Traits\PartnerLimits;

class UserLimits {
    use PartnerLimits;

	public function checkLimit(Request $request, Response $response) {
		$em = Application::getInstance()->getEntityManager();
        $conn = Application::getInstance()->getQueryBuilder();
        $Client = $em->getRepository('Businesspartner')->findOneBy(array('id' => 51844));

        //limit 1 calculation
        $sql = "select SUM(b.actualValue - b.alreadyPaid) as partner_limit FROM Billetreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'E' and b.actualValue > 0 ";
        $query = $em->createQuery($sql);
        $Limit = $query->getResult();

        $sql = "select SUM(b.actualValue) as partner_limit FROM Billsreceive b WHERE b.client = '".$Client->getId()."' AND b.status = 'A' and b.accountType = 'Venda Bilhete' ";
        $query = $em->createQuery($sql);
        $SalesLimit = $query->getResult();

        $sql = "SELECT SUM(orders.total_cost) AS cost FROM ( SELECT DISTINCT o.airline, o.miles_used, o.total_cost, o.status, o.client_email, o.client_name, o.commercial_status, f.boarding_date, f.landing_date FROM online_order o JOIN online_flight AS f ON f.order_id=o.id WHERE o.status IN ('RESERVA', 'PENDENTE', 'ESPERA', 'ESPERA VLR', 'ESPERA LIM', 'ESPERA PGTO', 'PRIORIDADE','ESPERA LIM VLR', 'ANT BLOQ', 'ANT', 'BLOQ', 'SITE_CIA_FORA_AR') AND o.client_name IN ( SELECT b.name FROM businesspartner b WHERE b.client_id = '".$Client->getId()."' ) group by o.id ) AS orders";
        $stmt = $conn->query($sql);
        while ($row = $stmt->fetch()) {
            $OrdersLimit = $row['cost'];
        }

        $usedValue = ((float)$Limit[0]['partner_limit'] + (float)$SalesLimit[0]['partner_limit'] + (float)$OrdersLimit);

        $limit1 = (float)$Client->getPartnerLimit() + (((float)$Client->getLimitMargin() / 100) * (float)$Client->getPartnerLimit());

        $response->setDataset(
            array(
                'Variáveis' => array('$Limit' => $Limit, '$SalesLimit' => $SalesLimit, '$OrdersLimit' => $OrdersLimit),
                'usedValue' => $usedValue,
                'limit1' => $limit1)
        );
	}
}