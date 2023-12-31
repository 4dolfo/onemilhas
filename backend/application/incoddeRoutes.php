<?php
$routes = array(
    '/incodde/airports' => '\MilesBench\Controller\Airport::GetJson',
    '/incodde/geraPedido' => '\MilesBench\Controller\Incodde\OnlineOrder::save',
    '/incodde/precificacao' => '\MilesBench\Controller\Incodde\Precification::pricing',
    '/incodde/descontos' => '\MilesBench\Controller\Incodde\Precification::discounts',
    '/incodde/atualizacao' => '\MilesBench\Controller\Incodde\OnlineOrder::update',
    '/incodde/passageiros' => '\MilesBench\Controller\Incodde\OnlineOrder::passageiros',
    '/incodde/companhias' => '\MilesBench\Controller\Incodde\Precification::airlines',
    '/incodde/carregarPedidos' => '\MilesBench\Controller\Incodde\Pedidos::load',
    '/incodde/loadDetalhes' => '\MilesBench\Controller\Incodde\Pedidos::loadDetails',
    '/incodde/cancelamentoPedido' => '\MilesBench\Controller\Incodde\Pedidos::cancelamento',
    '/incodde/confirmarPedido' => '\MilesBench\Controller\Incodde\Pedidos::confirmacao',
    '/incodde/calculoCancelamento' => '\MilesBench\Controller\Incodde\Cancelamento::calculo',
    '/incodde/calculoReembolso' => '\MilesBench\Controller\Incodde\Reembolso::calculo',
    '/incodde/reembolso' => '\MilesBench\Controller\Incodde\Reembolso::reembolso',
    '/incodde/cancelamento' => '\MilesBench\Controller\Incodde\Cancelamento::Cancelamento',
    '/incodde/login' => '\MilesBench\Controller\Incodde\Usuario::login',
    '/incodde/trocarSenha' => '\MilesBench\Controller\Incodde\Usuario::changePassword',
    '/incodde/esqueciSenha' => '\MilesBench\Controller\Incodde\Usuario::forgotPassword',
    '/incodde/buscaideal/precificacao' => '\MilesBench\Controller\Incodde\Precificacao::pricing',
    '/incodde/atualizaMarkup' => '\MilesBench\Controller\Incodde\Precification::updateMarkupClient',
    '/incodde/cliente/passageiros' => '\MilesBench\Controller\Incodde\Usuario::loadPassengers',
    '/incodde/test' => '\MilesBench\Controller\Incodde\Usuario::test',
    '/incodde/atualizaCliente' => '\MilesBench\Controller\Incodde\Usuario::updateClient',
    '/incodde/pedidosPendentes' => '\MilesBench\Controller\Incodde\Pedidos::pedidosPendentes',
    '/incodde/sistema' => '\MilesBench\Controller\Incodde\Sistema::load',
    '/incodde/carregarSaldo' => '\MilesBench\Controller\Incodde\Pedidos::carregarSaldo',
    '/incodde/updateMMS' => '\MilesBench\Controller\Incodde\OnlineOrder::updateMMS',
    '/incodde/validaCredito' => '\MilesBench\Controller\Incodde\OnlineOrder::validaCredito',
    '/incodde/validaCupom' => '\MilesBench\Controller\Incodde\OnlineOrder::validaCupom',
    '/incodde/mediaTaxa' => '\MilesBench\Controller\Airport::mediaTaxa',
    '/incodde/bankmilhas/fornecedor' => '\MilesBench\Controller\Incodde\BankMilhas\Fornecedor::save',
    '/incodde/bankmilhas/compra' => '\MilesBench\Controller\Incodde\BankMilhas\Pedido::save',
    '/incodde/bankmilhas/compra/atualizar' => '\MilesBench\Controller\Incodde\BankMilhas\Pedido::atualizar',
    '/incodde/bankmilhas/status' => '\MilesBench\Controller\Incodde\BankMilhas\Status::update',
    '/incodde/robo/atualizacao' => '\MilesBench\Controller\Incodde\Robo::update',
);
