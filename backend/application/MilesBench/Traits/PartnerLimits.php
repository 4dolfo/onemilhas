<?php

namespace MilesBench\Traits;

use MilesBench\Application;

trait PartnerLimits
{
    public function checkPartnerLimit($partner)
    {
        $em = Application::getInstance()->getEntityManager();
        $conn = Application::getInstance()->getQueryBuilder();

        // Arquivo para futuras extrações de lógica da análise de limites
    }
}