<?php
$routes = array(
    '/buscaideal/carregarPedidos' => '\MilesBench\Controller\BuscaIdeal\Pedidos::load',
    '/buscaideal/loadDetalhes' => '\MilesBench\Controller\BuscaIdeal\Pedidos::loadDetails',
    '/buscaideal/cancelamentoPedido' => '\MilesBench\Controller\BuscaIdeal\Pedidos::cancelamento',
    '/buscaideal/calculoCancelamento' => '\MilesBench\Controller\BuscaIdeal\Cancelamento::calculo',
    '/buscaideal/calculoReembolso' => '\MilesBench\Controller\BuscaIdeal\Reembolso::calculo',
    '/buscaideal/reembolso' => '\MilesBench\Controller\BuscaIdeal\Reembolso::reembolso',
    '/buscaideal/cancelamento' => '\MilesBench\Controller\BuscaIdeal\Cancelamento::Cancelamento',
    '/buscaideal/login' => '\MilesBench\Controller\BuscaIdeal\Usuario::login',
    '/buscaideal/trocarSenha' => '\MilesBench\Controller\BuscaIdeal\Usuario::changePassword',
    '/buscaideal/esqueciSenha' => '\MilesBench\Controller\BuscaIdeal\Usuario::forgotPassword',
    '/buscaideal/precificacao' => '\MilesBench\Controller\BuscaIdeal\Precificacao::pricing',
);
