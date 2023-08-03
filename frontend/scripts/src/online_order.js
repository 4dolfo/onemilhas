(function () {
  "use strict";
  angular
    .module("app.table")
    .controller("OnlineOrderCtrl", [
      "$scope",
      "$rootScope",
      "$filter",
      "$modal",
      "cfpLoadingBar",
      "logger",
      "FileUploader",
      function (
        $scope,
        $rootScope,
        $filter,
        $modal,
        cfpLoadingBar,
        logger,
        FileUploader
      ) {
        var init;
        var partnerSelected;
        $scope.searchKeywords = "";
        $scope.searchKeywordsMiles = "";
        $scope.filteredOnlineOrders = [];
        $scope.row = "";
        $scope.previousIndex = 0;
        $scope.currentPage = 1;
        $scope.card = [];
        $scope.saleEmails = [
          "tamvpg@gmail.com",
          "loja.tv@hotmail.com",
          "setorpt@gmail.com",
        ];
        $scope.saleStatus = ["RESERVA", "PENDENTE", "EMITIDO", "EM ESPERA"];
        $scope.classOptions = ["Economica", "Executiva"];
        $scope.removeFromEmail = [
          "JACK FOR",
          "Loja TAM Ponta Grossa",
          "Loja TAM Contagem",
          "Loja TAM M",
          "CONFIANÇA",
          "Flytour",
          "TAP",
          "Rextur Advance",
          "CNT Consolidadora",
          "HAST Viagens",
          "XML Viagens",
          "Milhas Alpass",
          "Alfa Rondonia Milhas",
          "Outros",
        ];
        $scope.blackListOfNames = ["JUNIOR", "NETO", "FILHO", "SOBRINHO"];

        $scope.card_previous_selected = "";
        $scope.card_now_selected = "";
        $rootScope.watchDogPID = 0;
        $scope.watchDogSelected = true;

        $scope.select = function (page) {
          // var end, start;
          // start = (page - 1) * $scope.numPerPage;
          // end = start + $scope.numPerPage;
          // return $scope.currentPageOnlineOrder = $scope.filteredOnlineOrders.slice(start, end);
          $scope.loadOnlineOrder();
        };

        $scope.onFilterChange = function () {
          // $scope.select($scope.currentPage);
          // return $scope.row = '';
          $scope.loadOnlineOrder();
        };

        $scope.onNumPerPageChange = function () {
          // $scope.select(1);
          // return $scope.currentPage = 1;
          $scope.loadOnlineOrder();
        };

        $scope.onOrderChange = function () {
          $scope.select(1);
          return ($scope.currentPage = 1);
        };

        $rootScope.$on("checkedPermission", function (event, args) {
          $scope.setSelected(true);
        });

        $scope.setSelected = function (args) {
          $scope.searchMilesOrder = undefined;
          $scope.searchMilesOrderDown = undefined;
          if ($scope.main.dealer) {
            return;
          }
          $scope.args = args;
          $scope.flight_selected = undefined;
          $scope.wsale = {};
          $scope.robotLog = [];
          $scope.multiDownloads = false;
          $scope.wsalemail = {};
          if (!args) {
            $scope.selected = angular.copy(this.onlineorder);
            $rootScope.setOnlineOrder($scope.selected);
          }
          if (args === true) {
            $scope.selected = $rootScope.onlineOrder;
          }
          //console.log($scope.selected);

          $scope.uploader.clearQueue();
          $scope.uploader_billets.clearQueue();
          $scope.uploader.formData = { hashId: $scope.session.hashId };
          $scope.uploader_billets.formData = {
            hashId: $scope.session.hashId,
            order: $scope.selected,
          };
          if ($scope.main.commercial && !$scope.main.isMaster) {
            if (
              ($scope.selected.status.indexOf("ESPERA") > -1 ||
                $scope.selected.status == "ANT" ||
                $scope.selected.status == "BLOQ" ||
                $scope.selected.status == "ANT BLOQ") &&
              !args
            ) {
              $rootScope.$emit("openPermission", {
                main: $scope.$parent.$parent.main,
                hashId: $scope.$parent.$parent.session.hashId,
                type: "Comercial",
              });
              return;
            } else {
              return;
            }
          }
          if (
            ($scope.selected.status.indexOf("ESPERA") < 0 &&
              $scope.selected.status != "ANT" &&
              $scope.selected.status != "BLOQ" &&
              $scope.selected.status != "ANT BLOQ") ||
            $scope.selected.commercialStatus ||
            $scope.$parent.$parent.main.isMaster ||
            args === true
          ) {
            if (
              ($scope.selected.status != "CANCELADO" &&
                $scope.selected.status != "FALHA EMISSAO") ||
              $scope.$parent.$parent.main.isMaster ||
              args === true
            ) {
              $scope.selected.issuing = $scope.selected.client_login;
              $scope.loadOnlineFlight(args);
              if (
                $scope.selected.status == "CANCELADO" ||
                $scope.selected.status == "FALHA EMISSAO"
              ) {
                logger.logWarning(
                  "Motivo cancelamento: " + $scope.selected.cancelReason
                );
              }
            } else {
              $rootScope.$emit("openPermission", {
                main: $scope.$parent.$parent.main,
                hashId: $scope.$parent.$parent.session.hashId,
              });
              logger.logError("Este pedido foi cancelado!");
            }
          } else {
            $rootScope.$emit("openPermission", {
              main: $scope.$parent.$parent.main,
              hashId: $scope.$parent.$parent.session.hashId,
            });
            logger.logError("Este pedido esta em Espera!");
          }
        };

        $scope.getFlightsAirlines = function () {
          if ($scope.selected) {
            if ($scope.selected.airline !== "") {
              return false;
            }
          }
          return true;
        };

        $rootScope.$on("fillEmailBLoqued", function (event, args) {
          $scope.fillEmailBLoqued(args);
        });

        $scope.fillEmailBLoqued = function (args) {
          if ($scope.response) {
            if ($scope.response.origin == "MMS") {
              $scope.wsalemail.emailpartner = args.email;
              $scope.wsalemail.mailcco = undefined;
              $scope.wsalemail.subject = "[] - Pedido de emissão";

              var date = new Date();
              date.setUTCHours(date.getUTCHours() - 2);

              if ($scope.response) {
                if ($scope.response.subClientEmail) {
                  // $scope.wsalemail.emailpartner = $scope.response.subClientEmail;
                  $scope.wsalemail.subClientEmail =
                    $scope.response.subClientEmail;
                }
                if ($scope.check48hours()) {
                  $scope.wsalemail.emailContent =
                    "Prezado Parceiro, <br><br>" +
                    "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>" +
                    "Atenciosamente.<br>";
                } else if ($scope.response.status == "Bloqueado") {
                  $scope.wsalemail.emailContent =
                    "Olá, <br><br>" +
                    "Antes de seguirmos com a emissão, nosso setor financeiro solicita contato para as devidas atualizações.<br>" +
                    "Horário de atendimento do setor financeiro: Segunda a Sexta de 09:00 as 18:00 ( horário de Brasília )  <br><br>" +
                    "Email: financeiro@onemilhas.com.br <br>Telefones: (31) 3972-1929 <br><br>";

                  $scope.wsalemail.mailcco = 'suporte@onemilhas.com.br';

                  $scope.wsalemail.emailContent += "Atenciosamente.<br>";
                } else if ($scope.response.status == "Pendente") {
                  $scope.wsalemail.emailContent =
                    "Caro Parceiro, <br><br>" +
                    "Cliente com status PENDENTE!" +
                    "<br>" +
                    $scope.response.client_name +
                    "<br>" +
                    "Atenciosamente.<br>";

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    date.getUTCHours() >= 9 &&
                    date.getUTCHours() <= 21
                  ) {
                    $scope.wsalemail.emailpartner = 'suporte@onemilhas.com.br';
                  } else if (date.getDay() > 0 && date.getDay() < 6) {
                    $scope.wsalemail.mailcco = 'suporte@onemilhas.com.br';
                  } else {
                    $scope.wsalemail.emailpartner = 'suporte@onemilhas.com.br';
                  }

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    (date.getUTCHours() > 21 || date.getUTCHours() <= 2)
                  ) {
                    $scope.wsalemail.mailcco = 'suporte@onemilhas.com.br';
                    $scope.wsalemail.emailContent =
                      "Caro Parceiro.<br><br>" +
                      "Agradecemos a confiança ao enviar a 1º emissão.Dentre as próximas horas iremos enviar nosso protocolo de ativação finalizando nosso cadastro.<br>" +
                      "Para seguirmos com essa emissão gentileza entrar em contato com nosso setor comercial para alinharmos os últimos detalhes.<br><br>" +
                      "(31) 3972-1929<br><br>" +
                      "suporte@onemilhas.com.br<br><br>" +
                      "Atenciosamente<br>Equipe";
                  }
                } else if ($scope.response.paymentType == "Antecipado") {
                  $scope.wsalemail.mailcco = 'suporte@onemilhas.com.br';
                  $scope.wsalemail.emailContent =
                    "Prezado Parceiro, <br><br>" +
                    "Para darmos andamento a sua solicitação, pedimos que nos encaminhe o comprovante de pagamento neste mesmo e-mail: suporte@onemilhas.com.br . Aguardamos o recebimento do comprovante e liberação do setor financeiro para seguirmos com o processo.<br>" +
                    "Email: financeiro@onemilhas.com.br<br> Telefones: (31) 3972-1929<br><br>";

                  $scope.wsalemail.emailContent +=
                    "<table style='text-align: center; border-collapse: collapse;' border='1' width='95%' align='center'><tbody>" +
                    "<tr bgcolor='#FFFFFF'><td>&nbsp;</td><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Caixa</strong></span></td><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Bradesco</strong></span></td>" +
                    "<td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Ita&uacute;</strong></span></td><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Brasil</strong></span></td>" +
                    "<td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Santander</strong></span></td></tr><tr><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Banco:</strong></span></td>" +
                    "<td>104</td><td>237</td><td>341</td><td>001</td><td>033</td></tr><tr><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Ag&ecirc;ncia:</strong></span></td>" +
                    "<td>2922</td><td>3420</td><td>1582</td><td>-</td><td>4232</td></tr><tr><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Conta:</strong></span></td>" +
                    "<td>2643-6</td><td>29429-2</td><td>33678-8</td><td>-</td>" +
                    "<td>13004689-8</td></tr><tr><td colspan='6'><strong>MMS VIAGENS LTDA</strong><br /><strong>CNPJ: 29.632.355/0001-85</strong></td></tr></tbody></table><br><br>";

                  $scope.wsalemail.emailContent += "Atenciosamente.<br>";
                } else if ($scope.response.partner_limit == "true") {
                  $scope.wsalemail.emailContent =
                    "Caro Parceiro, <br><br>" +
                    "Seu limite foi excedido, com isso pedimos que entre em contato com o setor financeiro para seguirmos com a emissão.<br><br>" +
                    "Email: financeiro@onemilhas.com.br<br> Telefones: 3972-1929<br><br>" +
                    "Atenciosamente.<br>";

                  if (date.getDay() == 0 || date.getDay() == 6) {
                    $scope.wsalemail.emailpartner =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  }

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    date.getUTCHours() >= 9 &&
                    date.getUTCHours() <= 23
                  ) {
                    $scope.wsalemail.emailpartner =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  }

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    (date.getUTCHours() > 23 || date.getUTCHours() <= 2)
                  ) {
                    $scope.wsalemail.emailpartner = args.email;
                    $scope.wsalemail.mailcco = 'suporte@onemilhas.com.br';
                  }
                }
              } else {
                $scope.wsalemail.emailContent =
                  "Prezado Parceiro, <br><br>" +
                  "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>" +
                  "Atenciosamente.<br>";
              }
              $scope.uploader.clearQueue();
              $scope.uploader_billets.clearQueue();

              if ($scope.response) {
                if ($scope.response.client_name) {
                  $scope.wsalemail.emailContent +=
                    "<br><br>Cliente: " + $scope.response.client_name;
                }
              }
              $scope.wsalemail.emailContent +=
                "<br><br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Trechos</font></td></tr>" +
                "<tr><td>CIA</td><td>VOO</td><td>Conexões</td><td>Embarque</td><td>Desembarque</td><td>Duração</td><td>Origem</td><td>Destino</td></tr>";
              for (var i = 0; $scope.resume_flights.length > i; i++) {
                var testes = $scope.resume_flights[i].connection.split(" ");
                var connections = "";
                for (var j = 0; j < testes.length; j++) {
                  connections += testes[j] + "<br>";
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td>" +
                  $scope.resume_flights[i].airline +
                  "</td><td>" +
                  $scope.resume_flights[i].flight +
                  "</td><td>" +
                  connections +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $scope.resume_flights[i].flight_time +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_from +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_to +
                  "</td></tr>";
              }
              $scope.wsalemail.emailContent +=
                "</tbody></table>" +
                "<br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'>Passageiros</font></td></tr>";
              for (var i = 0; $scope.resume_paxs.length > i; i++) {
                var pax_name = $scope.resume_paxs[i].pax_name;
                if ($scope.resume_paxs[i].paxLastName) {
                  pax_name += " " + $scope.resume_paxs[i].paxLastName;
                }
                if ($scope.resume_paxs[i].paxAgnome) {
                  pax_name += " " + $scope.resume_paxs[i].paxAgnome;
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'> Passageiro " +
                  (i + 1) +
                  " </font></td></tr><tr><td>Nome:</td><td>" +
                  pax_name +
                  "</td></tr><tr><td>Identificação:</td><td>" +
                  $scope.resume_paxs[i].identification +
                  "</td></tr><tr><td>Data Nascimento:</td><td>" +
                  $filter("date")(
                    $scope.resume_paxs[i].birhtdate,
                    "dd/MM/yyyy"
                  ) +
                  "</td></tr>";
                if ($scope.resume_paxs[i].is_child != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>CHD</td></tr>";
                if ($scope.resume_paxs[i].is_newborn != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>INF</td></tr>";
              }
              $scope.wsalemail.emailContent += "</tbody></table><br><br>";

              if ($scope.response) {
                if (
                  $scope.response.status == "Bloqueado" ||
                  $scope.response.paymentType == "Antecipado" ||
                  $scope.response.partner_limit == "true"
                ) {
                  $scope.wsalemail.emailContent +=
                    "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>" +
                    "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
                  var miles = "";
                  var pricing = "";
                  var NumberOfAdult = 0;
                  var NumberOfChild = 0;
                  var NumberOfNewborn = 0;

                  for (var a in $scope.resume_paxs) {
                    if ($scope.resume_paxs[a].is_child == "S") {
                      NumberOfChild++;
                    } else if ($scope.resume_paxs[a].is_newborn == "S") {
                      NumberOfNewborn++;
                    } else {
                      NumberOfAdult++;
                    }
                  }

                  var connections;
                  for (var b in $scope.resume_flights) {
                    var miles = "";
                    var pricing = "";

                    var testes = $scope.resume_flights[b].connection.split(" ");
                    connections = "";
                    for (var j = 0; j < testes.length; j++) {
                      connections += testes[j] + "<br>";
                    }

                    if (NumberOfAdult > 0) {
                      miles =
                        miles +
                        "ADT: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].miles_per_adult,
                          0
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfAdult, 0) +
                        ")";
                      pricing =
                        pricing +
                        "ADT: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].cost_per_adult
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfAdult, 0) +
                        ")";
                    }
                    if (NumberOfChild > 0) {
                      miles =
                        miles +
                        "<br>CHD: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].miles_per_child,
                          0
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfChild, 0) +
                        ")";
                      pricing =
                        pricing +
                        "<br>CHD: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].cost_per_child
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfChild, 0) +
                        ")";
                    }
                    if (NumberOfNewborn > 0) {
                      miles =
                        miles +
                        "<br>INF: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].miles_per_newborn,
                          0
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfNewborn, 0) +
                        ")";
                      pricing =
                        pricing +
                        "<br>INF: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].cost_per_newborn
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfNewborn, 0) +
                        ")";
                    }

                    $scope.wsalemail.emailContent +=
                      "<tr><td>" +
                      $scope.resume_flights[b].airline +
                      "</td><td>" +
                      $scope.resume_flights[b].flight +
                      "</td>" +
                      "<td>" +
                      miles +
                      "</td><td>" +
                      pricing +
                      "</td><td>" +
                      $rootScope.formatNumber($scope.resume_flights[b].tax) +
                      "</td><td>" +
                      NumberOfAdult +
                      " ADT<br>" +
                      NumberOfChild +
                      " CHD<br>" +
                      NumberOfNewborn +
                      " INF" +
                      "</td>" +
                      "<td>" +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].original_miles,
                        0
                      ) +
                      "</td><td>" +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost +
                          $scope.resume_flights[b].tax
                      ) +
                      "</td></tr>";
                  }

                  $scope.wsalemail.emailContent +=
                    "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>" +
                    "<td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                    $rootScope.formatNumber(
                      $rootScope.onlineOrder.miles_used,
                      0
                    ) +
                    "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                    $rootScope.formatNumber($rootScope.onlineOrder.total_cost) +
                    "</font></td></tr>" +
                    "</tbody></table>";
                }
              } else {
                $scope.wsalemail.emailContent +=
                  "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>" +
                  "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
                var miles = "";
                var pricing = "";
                var NumberOfAdult = 0;
                var NumberOfChild = 0;
                var NumberOfNewborn = 0;

                for (var a in $scope.onlineflights) {
                  if ($scope.onlineflights[a].is_child == "S") {
                    NumberOfChild++;
                  } else if ($scope.onlineflights[a].is_newborn == "S") {
                    NumberOfNewborn++;
                  } else {
                    NumberOfAdult++;
                  }
                }

                var connections;
                for (var b in $scope.resume_flights) {
                  var testes = $scope.resume_flights[b].connection.split(" ");
                  connections = "";
                  for (var j = 0; j < testes.length; j++) {
                    connections += testes[j] + "<br>";
                  }

                  if (NumberOfAdult > 0) {
                    miles =
                      miles +
                      "ADT: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_adult
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfAdult, 0) +
                      ")";
                    pricing =
                      pricing +
                      "ADT: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_adult
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfAdult, 0) +
                      ")";
                  }
                  if (NumberOfChild > 0) {
                    miles =
                      miles +
                      "<br>CHD: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_child
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfChild, 0) +
                      ")";
                    pricing =
                      pricing +
                      "<br>CHD: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_child
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfChild, 0) +
                      ")";
                  }
                  if (NumberOfNewborn > 0) {
                    miles =
                      miles +
                      "<br>INF: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_newborn
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfNewborn, 0) +
                      ")";
                    pricing =
                      pricing +
                      "<br>INF: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_newborn
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfNewborn, 0) +
                      ")";
                  }

                  $scope.wsalemail.emailContent +=
                    "<tr><td>" +
                    $scope.resume_flights[b].airline +
                    "</td><td>" +
                    $scope.resume_flights[b].flight +
                    "</td>" +
                    "<td>" +
                    miles +
                    "</td><td>" +
                    pricing +
                    "</td><td>" +
                    $rootScope.formatNumber($scope.resume_flights[b].tax) +
                    "</td><td>" +
                    NumberOfAdult +
                    " ADT<br>" +
                    NumberOfChild +
                    " CHD<br>" +
                    NumberOfNewborn +
                    " INF" +
                    "</td>" +
                    "<td>" +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].original_miles,
                      0
                    ) +
                    "</td><td>" +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].cost +
                        $scope.resume_flights[b].tax
                    ) +
                    "</td></tr>";
                }

                $scope.wsalemail.emailContent +=
                  "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>" +
                  "<td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                  $rootScope.formatNumber(
                    $rootScope.onlineOrder.miles_used,
                    0
                  ) +
                  "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                  $rootScope.formatNumber($rootScope.onlineOrder.total_cost) +
                  "</font></td></tr>" +
                  "</tbody></table>";
              }
            } else {
              $scope.wsalemail.emailpartner = args.email;
              $scope.wsalemail.mailcco = undefined;
              $scope.wsalemail.subject = "Pedido de emissão";

              var date = new Date();
              date.setUTCHours(date.getUTCHours() - 2);

              if ($scope.response) {
                if ($scope.response.subClientEmail) {
                  // $scope.wsalemail.emailpartner = $scope.response.subClientEmail;
                  $scope.wsalemail.subClientEmail =
                    $scope.response.subClientEmail;
                }
                if ($scope.check48hours()) {
                  $scope.wsalemail.emailContent =
                    "Prezado Parceiro, <br><br>" +
                    "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>" +
                    "Atenciosamente.<br>Equipe One Milhas";
                } else if ($scope.response.status == "Bloqueado") {
                  $scope.wsalemail.emailContent =
                    "Olá, <br><br>" +
                    "Antes de seguirmos com a emissão, nosso setor financeiro solicita contato para as devidas atualizações.<br>" +
                    "Horário de atendimento do setor financeiro: Segunda a Sexta de 09:00 as 18:00 ( horário de Brasília )  <br><br>" +
                    "Email: financeiro@onemilhas.com.br <br>Telefones: (31) 9 9766-6636 / 3972-9601 (opção 2)<br><br>";

                  $scope.wsalemail.mailcco =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  if (
                    date.getDay() == 0 ||
                    date.getDay() == 6 ||
                    date.getUTCHours() >= 17 ||
                    date.getUTCHours() <= 2
                  ) {
                    $scope.wsalemail.emailContent +=
                      "Email: suporte@onemilhas.com.br ou financeiro@onemilhas.com.br <br>Telefone: (31) 9 9656-9292 <br><br>";
                  }

                  $scope.wsalemail.emailContent +=
                    "Atenciosamente.<br>Equipe One Milhas";
                } else if ($scope.response.status == "Antecipado/Bloqueado") {
                  $scope.wsalemail.emailContent =
                    "Prezado parceiro, <br><br>" +
                    "Para darmos andamento a sua solicitação, pedimos que nos encaminhe o comprovante de pagamento no valor da OP mais o mínimo de R$50,00, <br>" +
                    "conforme acordo fechado com o financeiro.<br>" +
                    "O comprovante deverá ser enviado neste mesmo e-mail: emissao@onemilhas.com.br . Aguardamos o recebimento do comprovante e liberação do setor financeiro para seguirmos com o processo.<br><br>";

                  $scope.wsalemail.mailcco =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  if (
                    date.getDay() == 0 ||
                    date.getDay() == 6 ||
                    date.getUTCHours() >= 17 ||
                    date.getUTCHours() <= 2
                  ) {
                    $scope.wsalemail.emailContent +=
                      "Email: suporte@onemilhas.com.br ou financeiro@onemilhas.com.br <br>Telefone: (31) 9 9656-9292 <br><br>";
                  }

                  $scope.wsalemail.emailContent +=
                    "Atenciosamente.<br>Equipe One Milhas";
                } else if ($scope.response.status == "Pendente") {
                  $scope.wsalemail.emailContent =
                    "Caro Parceiro, <br><br>" +
                    "Cliente com status PENDENTE!" +
                    "<br>" +
                    $scope.response.client_name +
                    "<br>" +
                    "Atenciosamente.<br>Equipe One Milhas";

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    date.getUTCHours() >= 9 &&
                    date.getUTCHours() <= 21
                  ) {
                    $scope.wsalemail.emailpartner =
                      'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  } else if (date.getDay() > 0 && date.getDay() < 6) {
                    $scope.wsalemail.mailcco =
                      'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  } else {
                    $scope.wsalemail.emailpartner =
                      'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  }

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    (date.getUTCHours() > 21 || date.getUTCHours() <= 2)
                  ) {
                    $scope.wsalemail.mailcco =
                      'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                    $scope.wsalemail.emailContent =
                      "Caro Parceiro.<br><br>" +
                      "Agradecemos a confiança ao enviar a 1º emissão.Dentre as próximas horas iremos enviar nosso protocolo de ativação finalizando nosso cadastro.<br>" +
                      "Para seguirmos com essa emissão gentileza entrar em contato com nosso setor comercial para alinharmos os últimos detalhes.<br><br>" +
                      "(31) 9 9467-1030<br><br>" +
                      'suporte@onemilhas.com.br' + "<br><br>" +
                      "Atenciosamente<br>Equipe Ideal";
                  }
                } else if ($scope.response.paymentType == "Antecipado") {
                  $scope.wsalemail.mailcco =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  $scope.wsalemail.emailContent =
                    "Prezado Parceiro, <br><br>" +
                    "Para darmos andamento a sua solicitação, pedimos que nos encaminhe o comprovante de pagamento neste mesmo e-mail: emissao@onemilhas.com.br . Aguardamos o recebimento do comprovante e liberação do setor financeiro para seguirmos com o processo.<br>" +
                    "Email: financeiro@onemilhas.com.br<br> Telefones: (31) 9 9766-6636 / 3972-9601(opção 2)<br><br>";

                  $scope.wsalemail.emailContent +=
                    "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody>" +
                    "<tr bgcolor='#FFFFFF'><td></td><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Caixa</b></font></td><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Brasil</b></font></td>" +
                    "<td bgcolor='#5D7B9D'><font color='#ffffff'><b>Caixa</b></font></td>" +
                    "<td bgcolor='#5D7B9D'><font color='#ffffff'><b>Bradesco</b></font></td><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Itaú</b></font></td></tr>" +
                    "<tr><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Banco:</b></font></td><td>C.E.F - 104</td><td>001</td><td>033</td><td>237</td><td>341</td></tr><tr>" +
                    "<td bgcolor='#5D7B9D'><font color='#ffffff'><b>Agência:</b></font></td><td>0087 - OP 003</td><td>4403-2</td><td>0944</td><td>2943</td><td>3101</td></tr>" +
                    "<tr><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Conta:</b></font></td><td>3324-2</td><td>20123-5</td><td>13002164-6</td><td>0017241-3</td><td>43300-5</td></tr>" +
                    "<tr><td colspan='6'><br><b>CML VIAGENS E TURISMO LTDA</b><br><b>CNPJ: 35.823.529-0001-90</b></td></tr></tbody></table><br><br>";

                  $scope.wsalemail.emailContent +=
                    "Atenciosamente.<br>Equipe One Milhas";
                } else if ($scope.response.partner_limit == "true") {
                  $scope.wsalemail.emailContent =
                    "Caro Parceiro, <br><br>" +
                    "Seu limite foi excedido, com isso pedimos que entre em contato com o setor financeiro para seguirmos com a emissão.<br><br>" +
                    "Email: financeiro@onemilhas.com.br<br> Telefones: (31) 9 9766-6636 / 3972-9601(opção 2)<br><br>" +
                    "Atenciosamente.<br>Equipe One Milhas";

                  if (date.getDay() == 0 || date.getDay() == 6) {
                    $scope.wsalemail.emailpartner =
                      'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  }

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    date.getUTCHours() >= 9 &&
                    date.getUTCHours() <= 23
                  ) {
                    $scope.wsalemail.emailpartner =
                      'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  }

                  if (
                    date.getDay() > 0 &&
                    date.getDay() < 6 &&
                    (date.getUTCHours() > 23 || date.getUTCHours() <= 2)
                  ) {
                    $scope.wsalemail.emailpartner = args.email;
                    $scope.wsalemail.mailcco =
                      'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  }
                }
              } else {
                $scope.wsalemail.emailContent =
                  "Prezado Parceiro, <br><br>" +
                  "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>" +
                  "Atenciosamente.<br>Equipe One Milhas";
              }
              $scope.uploader.clearQueue();

              if ($scope.response) {
                if ($scope.response.client_name) {
                  $scope.wsalemail.emailContent +=
                    "<br><br>Cliente: " + $scope.response.client_name;
                }
              }
              $scope.wsalemail.emailContent +=
                "<br><br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Trechos</font></td></tr>" +
                "<tr><td>CIA</td><td>VOO</td><td>Conexões</td><td>Embarque</td><td>Desembarque</td><td>Duração</td><td>Origem</td><td>Destino</td></tr>";
              for (var i = 0; $scope.resume_flights.length > i; i++) {
                var testes = $scope.resume_flights[i].connection.split(" ");
                var connections = "";
                for (var j = 0; j < testes.length; j++) {
                  connections += testes[j] + "<br>";
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td>" +
                  $scope.resume_flights[i].airline +
                  "</td><td>" +
                  $scope.resume_flights[i].flight +
                  "</td><td>" +
                  connections +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $scope.resume_flights[i].flight_time +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_from +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_to +
                  "</td></tr>";
              }
              $scope.wsalemail.emailContent +=
                "</tbody></table>" +
                "<br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'>Passageiros</font></td></tr>";
              for (var i = 0; $scope.resume_paxs.length > i; i++) {
                var pax_name = $scope.resume_paxs[i].pax_name;
                if ($scope.resume_paxs[i].paxLastName) {
                  pax_name += " " + $scope.resume_paxs[i].paxLastName;
                }
                if ($scope.resume_paxs[i].paxAgnome) {
                  pax_name += " " + $scope.resume_paxs[i].paxAgnome;
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'> Passageiro " +
                  (i + 1) +
                  " </font></td></tr><tr><td>Nome:</td><td>" +
                  pax_name +
                  "</td></tr><tr><td>Identificação:</td><td>" +
                  $scope.resume_paxs[i].identification +
                  "</td></tr><tr><td>Data Nascimento:</td><td>" +
                  $filter("date")(
                    $scope.resume_paxs[i].birhtdate,
                    "dd/MM/yyyy"
                  ) +
                  "</td></tr>";
                if ($scope.resume_paxs[i].is_child != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>CHD</td></tr>";
                if ($scope.resume_paxs[i].is_newborn != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>INF</td></tr>";
              }
              $scope.wsalemail.emailContent += "</tbody></table><br><br>";

              if ($scope.response) {
                if (
                  $scope.response.status == "Bloqueado" ||
                  $scope.response.paymentType == "Antecipado" ||
                  $scope.response.partner_limit == "true"
                ) {
                  $scope.wsalemail.emailContent +=
                    "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>" +
                    "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
                  var miles = "";
                  var pricing = "";
                  var NumberOfAdult = 0;
                  var NumberOfChild = 0;
                  var NumberOfNewborn = 0;

                  for (var a in $scope.resume_paxs) {
                    if ($scope.resume_paxs[a].is_child == "S") {
                      NumberOfChild++;
                    } else if ($scope.resume_paxs[a].is_newborn == "S") {
                      NumberOfNewborn++;
                    } else {
                      NumberOfAdult++;
                    }
                  }

                  var connections;
                  for (var b in $scope.resume_flights) {
                    var miles = "";
                    var pricing = "";

                    var testes = $scope.resume_flights[b].connection.split(" ");
                    connections = "";
                    for (var j = 0; j < testes.length; j++) {
                      connections += testes[j] + "<br>";
                    }

                    if (NumberOfAdult > 0) {
                      miles =
                        miles +
                        "ADT: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].miles_per_adult,
                          0
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfAdult, 0) +
                        ")";
                      pricing =
                        pricing +
                        "ADT: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].cost_per_adult
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfAdult, 0) +
                        ")";
                    }
                    if (NumberOfChild > 0) {
                      miles =
                        miles +
                        "<br>CHD: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].miles_per_child,
                          0
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfChild, 0) +
                        ")";
                      pricing =
                        pricing +
                        "<br>CHD: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].cost_per_child
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfChild, 0) +
                        ")";
                    }
                    if (NumberOfNewborn > 0) {
                      miles =
                        miles +
                        "<br>INF: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].miles_per_newborn,
                          0
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfNewborn, 0) +
                        ")";
                      pricing =
                        pricing +
                        "<br>INF: " +
                        $rootScope.formatNumber(
                          $scope.resume_flights[b].cost_per_newborn
                        ) +
                        " (x" +
                        $rootScope.formatNumber(NumberOfNewborn, 0) +
                        ")";
                    }

                    $scope.wsalemail.emailContent +=
                      "<tr><td>" +
                      $scope.resume_flights[b].airline +
                      "</td><td>" +
                      $scope.resume_flights[b].flight +
                      "</td>" +
                      "<td>" +
                      miles +
                      "</td><td>" +
                      pricing +
                      "</td><td>" +
                      $rootScope.formatNumber($scope.resume_flights[b].tax) +
                      "</td><td>" +
                      NumberOfAdult +
                      " ADT<br>" +
                      NumberOfChild +
                      " CHD<br>" +
                      NumberOfNewborn +
                      " INF" +
                      "</td>" +
                      "<td>" +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].original_miles,
                        0
                      ) +
                      "</td><td>" +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost +
                          $scope.resume_flights[b].tax
                      ) +
                      "</td></tr>";
                  }

                  $scope.wsalemail.emailContent +=
                    "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>" +
                    "<td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                    $rootScope.formatNumber(
                      $rootScope.onlineOrder.miles_used,
                      0
                    ) +
                    "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                    $rootScope.formatNumber($rootScope.onlineOrder.total_cost) +
                    "</font></td></tr>" +
                    "</tbody></table>";
                }
              } else {
                $scope.wsalemail.emailContent +=
                  "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>" +
                  "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
                var miles = "";
                var pricing = "";
                var NumberOfAdult = 0;
                var NumberOfChild = 0;
                var NumberOfNewborn = 0;

                for (var a in $scope.onlineflights) {
                  if ($scope.onlineflights[a].is_child == "S") {
                    NumberOfChild++;
                  } else if ($scope.onlineflights[a].is_newborn == "S") {
                    NumberOfNewborn++;
                  } else {
                    NumberOfAdult++;
                  }
                }

                var connections;
                for (var b in $scope.resume_flights) {
                  var testes = $scope.resume_flights[b].connection.split(" ");
                  connections = "";
                  for (var j = 0; j < testes.length; j++) {
                    connections += testes[j] + "<br>";
                  }

                  if (NumberOfAdult > 0) {
                    miles =
                      miles +
                      "ADT: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_adult
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfAdult, 0) +
                      ")";
                    pricing =
                      pricing +
                      "ADT: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_adult
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfAdult, 0) +
                      ")";
                  }
                  if (NumberOfChild > 0) {
                    miles =
                      miles +
                      "<br>CHD: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_child
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfChild, 0) +
                      ")";
                    pricing =
                      pricing +
                      "<br>CHD: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_child
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfChild, 0) +
                      ")";
                  }
                  if (NumberOfNewborn > 0) {
                    miles =
                      miles +
                      "<br>INF: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_newborn
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfNewborn, 0) +
                      ")";
                    pricing =
                      pricing +
                      "<br>INF: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_newborn
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfNewborn, 0) +
                      ")";
                  }

                  $scope.wsalemail.emailContent +=
                    "<tr><td>" +
                    $scope.resume_flights[b].airline +
                    "</td><td>" +
                    $scope.resume_flights[b].flight +
                    "</td>" +
                    "<td>" +
                    miles +
                    "</td><td>" +
                    pricing +
                    "</td><td>" +
                    $rootScope.formatNumber($scope.resume_flights[b].tax) +
                    "</td><td>" +
                    NumberOfAdult +
                    " ADT<br>" +
                    NumberOfChild +
                    " CHD<br>" +
                    NumberOfNewborn +
                    " INF" +
                    "</td>" +
                    "<td>" +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].original_miles,
                      0
                    ) +
                    "</td><td>" +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].cost +
                        $scope.resume_flights[b].tax
                    ) +
                    "</td></tr>";
                }

                $scope.wsalemail.emailContent +=
                  "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>" +
                  "<td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                  $rootScope.formatNumber(
                    $rootScope.onlineOrder.miles_used,
                    0
                  ) +
                  "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                  $rootScope.formatNumber($rootScope.onlineOrder.total_cost) +
                  "</font></td></tr>" +
                  "</tbody></table>";
              }
            }
          } else {
            $scope.wsalemail.emailpartner = args.email;
            $scope.wsalemail.mailcco = undefined;
            $scope.wsalemail.subject = "Pedido de emissão";

            var date = new Date();
            date.setUTCHours(date.getUTCHours() - 2);

            if ($scope.response) {
              if ($scope.response.subClientEmail) {
                // $scope.wsalemail.emailpartner = $scope.response.subClientEmail;
                $scope.wsalemail.subClientEmail =
                  $scope.response.subClientEmail;
              }
              if ($scope.check48hours()) {
                $scope.wsalemail.emailContent =
                  "Prezado Parceiro, <br><br>" +
                  "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>" +
                  "Atenciosamente.<br>Equipe One Milhas";
              } else if ($scope.response.status == "Bloqueado") {
                $scope.wsalemail.emailContent =
                  "Olá, <br><br>" +
                  "Antes de seguirmos com a emissão, nosso setor financeiro solicita contato para as devidas atualizações.<br>" +
                  "Horário de atendimento do setor financeiro: Segunda a Sexta de 09:00 as 18:00 ( horário de Brasília )  <br><br>" +
                  "Email: financeiro@onemilhas.com.br <br>Telefones: (31) 9 9766-6636 / 3972-9601 (opção 2)<br><br>";

                $scope.wsalemail.mailcco =
                  'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                if (
                  date.getDay() == 0 ||
                  date.getDay() == 6 ||
                  date.getUTCHours() >= 17 ||
                  date.getUTCHours() <= 2
                ) {
                  $scope.wsalemail.emailContent +=
                    "Email: suporte@onemilhas.com.br ou financeiro@onemilhas.com.br <br>Telefone: (31) 9 9656-9292 <br><br>";
                }

                $scope.wsalemail.emailContent +=
                  "Atenciosamente.<br>Equipe One Milhas";
              } else if ($scope.response.status == "Antecipado/Bloqueado") {
                $scope.wsalemail.emailContent =
                  "Prezado parceiro,<br><br> " +
                  "Para darmos andamento a sua solicitação, pedimos que nos encaminhe o comprovante de pagamento no valor da OP mais o mínimo de R$50,00, <br>" +
                  "conforme acordo fechado com o financeiro.<br>" +
                  "O comprovante deverá ser enviado neste mesmo e-mail: emissao@onemilhas.com.br . Aguardamos o recebimento do comprovante e liberação do setor financeiro para seguirmos com o processo.<br><br>";

                $scope.wsalemail.mailcco =
                  'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                if (
                  date.getDay() == 0 ||
                  date.getDay() == 6 ||
                  date.getUTCHours() >= 17 ||
                  date.getUTCHours() <= 2
                ) {
                  $scope.wsalemail.emailContent +=
                    "Email: suporte@onemilhas.com.br ou financeiro@onemilhas.com.br <br>Telefone: (31) 9 9656-9292 <br><br>";
                }

                $scope.wsalemail.emailContent +=
                  "Atenciosamente.<br>Equipe One Milhas";
              } else if ($scope.response.status == "Pendente") {
                $scope.wsalemail.emailContent =
                  "Caro Parceiro, <br><br>" +
                  "Cliente com status PENDENTE!" +
                  "<br>" +
                  $scope.response.client_name +
                  "<br>" +
                  "Atenciosamente.<br>Equipe One Milhas";

                if (
                  date.getDay() > 0 &&
                  date.getDay() < 6 &&
                  date.getUTCHours() >= 9 &&
                  date.getUTCHours() <= 21
                ) {
                  $scope.wsalemail.emailpartner =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                } else if (date.getDay() > 0 && date.getDay() < 6) {
                  $scope.wsalemail.mailcco =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                } else {
                  $scope.wsalemail.emailpartner =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                }

                if (
                  date.getDay() > 0 &&
                  date.getDay() < 6 &&
                  (date.getUTCHours() > 21 || date.getUTCHours() <= 2)
                ) {
                  $scope.wsalemail.mailcco =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                  $scope.wsalemail.emailContent =
                    "Caro Parceiro.<br><br>" +
                    "Agradecemos a confiança ao enviar a 1º emissão.Dentre as próximas horas iremos enviar nosso protocolo de ativação finalizando nosso cadastro.<br>" +
                    "Para seguirmos com essa emissão gentileza entrar em contato com nosso setor comercial para alinharmos os últimos detalhes.<br><br>" +
                    "(31) 9 9467-1030<br><br>" +
                    'suporte@onemilhas.com.br' + "<br><br>" +
                    "Atenciosamente<br>Equipe One Milhas";
                }
              } else if ($scope.response.paymentType == "Antecipado") {
                $scope.wsalemail.mailcco =
                  'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                $scope.wsalemail.emailContent =
                  "Prezado Parceiro, <br><br>" +
                  "Para darmos andamento a sua solicitação, pedimos que nos encaminhe o comprovante de pagamento neste mesmo e-mail: emissao@onemilhas.com.br . Aguardamos o recebimento do comprovante e liberação do setor financeiro para seguirmos com o processo.<br>" +
                  "Email: financeiro@onemilhas.com.br<br> Telefones: (31) 9 9766-6636 / 3972-9601(opção 2)<br><br>";

                $scope.wsalemail.emailContent +=
                  "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody>" +
                  "<tr bgcolor='#FFFFFF'><td></td><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Caixa</b></font></td><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Brasil</b></font></td>" +
                  "<td bgcolor='#5D7B9D'><font color='#ffffff'><b>Caixa</b></font></td>" +
                  "<td bgcolor='#5D7B9D'><font color='#ffffff'><b>Bradesco</b></font></td><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Itaú</b></font></td></tr>" +
                  "<tr><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Banco:</b></font></td><td>C.E.F - 104</td><td>001</td><td>033</td><td>237</td><td>341</td></tr><tr>" +
                  "<td bgcolor='#5D7B9D'><font color='#ffffff'><b>Agência:</b></font></td><td>0087 - OP 003</td><td>4403-2</td><td>0944</td><td>2943</td><td>3101</td></tr>" +
                  "<tr><td bgcolor='#5D7B9D'><font color='#ffffff'><b>Conta:</b></font></td><td>3324-2</td><td>20123-5</td><td>13002164-6</td><td>0017241-3</td><td>43300-5</td></tr>" +
                  "<tr><td colspan='6'><b>CML VIAGENS E TURISMO LTDA</b><br><b>CNPJ: 35.823.529-0001-90</b></td></tr></tbody></table><br><br>";

                $scope.wsalemail.emailContent +=
                  "Atenciosamente.<br>Equipe One Milhas";
              } else if ($scope.response.partner_limit == "true") {
                $scope.wsalemail.emailContent =
                  "Caro Parceiro, <br><br>" +
                  "Seu limite foi excedido, com isso pedimos que entre em contato com o setor financeiro para seguirmos com a emissão.<br><br>" +
                  "Email: financeiro@onemilhas.com.br<br> Telefones: (31) 9 9766-6636 / 3972-9601(opção 2)<br><br>" +
                  "Atenciosamente.<br>Equipe One Milhas";

                if (date.getDay() == 0 || date.getDay() == 6) {
                  $scope.wsalemail.emailpartner =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                }

                if (
                  date.getDay() > 0 &&
                  date.getDay() < 6 &&
                  date.getUTCHours() >= 9 &&
                  date.getUTCHours() <= 23
                ) {
                  $scope.wsalemail.emailpartner =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                }

                if (
                  date.getDay() > 0 &&
                  date.getDay() < 6 &&
                  (date.getUTCHours() > 23 || date.getUTCHours() <= 2)
                ) {
                  $scope.wsalemail.emailpartner = args.email;
                  $scope.wsalemail.mailcco =
                    'suporte@onemilhas.com.br' + ";" + 'financeiro@onemilhas.com.br';
                }
              }
            } else {
              $scope.wsalemail.emailContent =
                "Prezado Parceiro, <br><br>" +
                "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>" +
                "Atenciosamente.<br>EquipeOne Milhas";
            }
            $scope.uploader.clearQueue();

            if ($scope.response) {
              if ($scope.response.client_name) {
                $scope.wsalemail.emailContent +=
                  "<br><br>Cliente: " + $scope.response.client_name;
              }
            }
            $scope.wsalemail.emailContent +=
              "<br><br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Trechos</font></td></tr>" +
              "<tr><td>CIA</td><td>VOO</td><td>Conexões</td><td>Embarque</td><td>Desembarque</td><td>Duração</td><td>Origem</td><td>Destino</td></tr>";
            for (var i = 0; $scope.resume_flights.length > i; i++) {
              var testes = $scope.resume_flights[i].connection.split(" ");
              var connections = "";
              for (var j = 0; j < testes.length; j++) {
                connections += testes[j] + "<br>";
              }
              $scope.wsalemail.emailContent +=
                "<tr><td>" +
                $scope.resume_flights[i].airline +
                "</td><td>" +
                $scope.resume_flights[i].flight +
                "</td><td>" +
                connections +
                "</td><td>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].boarding_date),
                  "dd/MM/yyyy"
                ) +
                "<br>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].boarding_date),
                  "HH:mm:ss"
                ) +
                "</td><td>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].landing_date),
                  "dd/MM/yyyy"
                ) +
                "<br>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].landing_date),
                  "HH:mm:ss"
                ) +
                "</td><td>" +
                $scope.resume_flights[i].flight_time +
                "</td><td>" +
                $scope.resume_flights[i].airport_code_from +
                "</td><td>" +
                $scope.resume_flights[i].airport_code_to +
                "</td></tr>";
            }
            $scope.wsalemail.emailContent +=
              "</tbody></table>" +
              "<br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'>Passageiros</font></td></tr>";
            for (var i = 0; $scope.resume_paxs.length > i; i++) {
              var pax_name = $scope.resume_paxs[i].pax_name;
              if ($scope.resume_paxs[i].paxLastName) {
                pax_name += " " + $scope.resume_paxs[i].paxLastName;
              }
              if ($scope.resume_paxs[i].paxAgnome) {
                pax_name += " " + $scope.resume_paxs[i].paxAgnome;
              }
              $scope.wsalemail.emailContent +=
                "<tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'> Passageiro " +
                (i + 1) +
                " </font></td></tr><tr><td>Nome:</td><td>" +
                pax_name +
                "</td></tr><tr><td>Identificação:</td><td>" +
                $scope.resume_paxs[i].identification +
                "</td></tr><tr><td>Data Nascimento:</td><td>" +
                $filter("date")($scope.resume_paxs[i].birhtdate, "dd/MM/yyyy") +
                "</td></tr>";
              if ($scope.resume_paxs[i].is_child != "N")
                $scope.wsalemail.emailContent +=
                  "<tr><td></td><td>CHD</td></tr>";
              if ($scope.resume_paxs[i].is_newborn != "N")
                $scope.wsalemail.emailContent +=
                  "<tr><td></td><td>INF</td></tr>";
            }
            $scope.wsalemail.emailContent += "</tbody></table><br><br>";

            if ($scope.response) {
              if (
                $scope.response.status == "Bloqueado" ||
                $scope.response.paymentType == "Antecipado" ||
                $scope.response.partner_limit == "true"
              ) {
                $scope.wsalemail.emailContent +=
                  "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>" +
                  "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
                var miles = "";
                var pricing = "";
                var NumberOfAdult = 0;
                var NumberOfChild = 0;
                var NumberOfNewborn = 0;

                for (var a in $scope.resume_paxs) {
                  if ($scope.resume_paxs[a].is_child == "S") {
                    NumberOfChild++;
                  } else if ($scope.resume_paxs[a].is_newborn == "S") {
                    NumberOfNewborn++;
                  } else {
                    NumberOfAdult++;
                  }
                }

                var connections;
                for (var b in $scope.resume_flights) {
                  var miles = "";
                  var pricing = "";

                  var testes = $scope.resume_flights[b].connection.split(" ");
                  connections = "";
                  for (var j = 0; j < testes.length; j++) {
                    connections += testes[j] + "<br>";
                  }

                  if (NumberOfAdult > 0) {
                    miles =
                      miles +
                      "ADT: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_adult,
                        0
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfAdult, 0) +
                      ")";
                    pricing =
                      pricing +
                      "ADT: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_adult
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfAdult, 0) +
                      ")";
                  }
                  if (NumberOfChild > 0) {
                    miles =
                      miles +
                      "<br>CHD: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_child,
                        0
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfChild, 0) +
                      ")";
                    pricing =
                      pricing +
                      "<br>CHD: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_child
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfChild, 0) +
                      ")";
                  }
                  if (NumberOfNewborn > 0) {
                    miles =
                      miles +
                      "<br>INF: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].miles_per_newborn,
                        0
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfNewborn, 0) +
                      ")";
                    pricing =
                      pricing +
                      "<br>INF: " +
                      $rootScope.formatNumber(
                        $scope.resume_flights[b].cost_per_newborn
                      ) +
                      " (x" +
                      $rootScope.formatNumber(NumberOfNewborn, 0) +
                      ")";
                  }

                  $scope.wsalemail.emailContent +=
                    "<tr><td>" +
                    $scope.resume_flights[b].airline +
                    "</td><td>" +
                    $scope.resume_flights[b].flight +
                    "</td>" +
                    "<td>" +
                    miles +
                    "</td><td>" +
                    pricing +
                    "</td><td>" +
                    $rootScope.formatNumber($scope.resume_flights[b].tax) +
                    "</td><td>" +
                    NumberOfAdult +
                    " ADT<br>" +
                    NumberOfChild +
                    " CHD<br>" +
                    NumberOfNewborn +
                    " INF" +
                    "</td>" +
                    "<td>" +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].original_miles,
                      0
                    ) +
                    "</td><td>" +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].cost +
                        $scope.resume_flights[b].tax
                    ) +
                    "</td></tr>";
                }

                $scope.wsalemail.emailContent +=
                  "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>" +
                  "<td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                  $rootScope.formatNumber(
                    $rootScope.onlineOrder.miles_used,
                    0
                  ) +
                  "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                  $rootScope.formatNumber($scope.getTotalValue()) +
                  "</font></td></tr>" +
                  "</tbody></table>";
              }
            } else {
              $scope.wsalemail.emailContent +=
                "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>" +
                "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
              var miles = "";
              var pricing = "";
              var NumberOfAdult = 0;
              var NumberOfChild = 0;
              var NumberOfNewborn = 0;

              for (var a in $scope.onlineflights) {
                if ($scope.onlineflights[a].is_child == "S") {
                  NumberOfChild++;
                } else if ($scope.onlineflights[a].is_newborn == "S") {
                  NumberOfNewborn++;
                } else {
                  NumberOfAdult++;
                }
              }

              var connections;
              for (var b in $scope.resume_flights) {
                var testes = $scope.resume_flights[b].connection.split(" ");
                connections = "";
                for (var j = 0; j < testes.length; j++) {
                  connections += testes[j] + "<br>";
                }

                if (NumberOfAdult > 0) {
                  miles =
                    miles +
                    "ADT: " +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].miles_per_adult
                    ) +
                    " (x" +
                    $rootScope.formatNumber(NumberOfAdult, 0) +
                    ")";
                  pricing =
                    pricing +
                    "ADT: " +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].cost_per_adult
                    ) +
                    " (x" +
                    $rootScope.formatNumber(NumberOfAdult, 0) +
                    ")";
                }
                if (NumberOfChild > 0) {
                  miles =
                    miles +
                    "<br>CHD: " +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].miles_per_child
                    ) +
                    " (x" +
                    $rootScope.formatNumber(NumberOfChild, 0) +
                    ")";
                  pricing =
                    pricing +
                    "<br>CHD: " +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].cost_per_child
                    ) +
                    " (x" +
                    $rootScope.formatNumber(NumberOfChild, 0) +
                    ")";
                }
                if (NumberOfNewborn > 0) {
                  miles =
                    miles +
                    "<br>INF: " +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].miles_per_newborn
                    ) +
                    " (x" +
                    $rootScope.formatNumber(NumberOfNewborn, 0) +
                    ")";
                  pricing =
                    pricing +
                    "<br>INF: " +
                    $rootScope.formatNumber(
                      $scope.resume_flights[b].cost_per_newborn
                    ) +
                    " (x" +
                    $rootScope.formatNumber(NumberOfNewborn, 0) +
                    ")";
                }

                $scope.wsalemail.emailContent +=
                  "<tr><td>" +
                  $scope.resume_flights[b].airline +
                  "</td><td>" +
                  $scope.resume_flights[b].flight +
                  "</td>" +
                  "<td>" +
                  miles +
                  "</td><td>" +
                  pricing +
                  "</td><td>" +
                  $rootScope.formatNumber($scope.resume_flights[b].tax) +
                  "</td><td>" +
                  NumberOfAdult +
                  " ADT<br>" +
                  NumberOfChild +
                  " CHD<br>" +
                  NumberOfNewborn +
                  " INF" +
                  "</td>" +
                  "<td>" +
                  $rootScope.formatNumber(
                    $scope.resume_flights[b].original_miles,
                    0
                  ) +
                  "</td><td>" +
                  $rootScope.formatNumber(
                    $scope.resume_flights[b].cost + $scope.resume_flights[b].tax
                  ) +
                  "</td></tr>";
              }

              $scope.wsalemail.emailContent +=
                "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>" +
                "<td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                $rootScope.formatNumber($rootScope.onlineOrder.miles_used, 0) +
                "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" +
                $rootScope.formatNumber($scope.getTotalValue()) +
                "</font></td></tr>" +
                "</tbody></table>";
            }
          }

          $scope.previousIndex = $scope.tabindex;
          $scope.tabindex = 5;
        };

        $scope.mailOrder = function () {
          var file = [];
          for (var j in $scope.uploader.queue) {
            file.push($scope.uploader.queue[j].file.name);
          }
          // file = $scope.uploader.queue[$scope.uploader.queue.length -1].file.name;
          if ($scope.uploader.queue.length > 0) {
            $.post(
              "../backend/application/index.php?rota=/mailOrder",
              {
                hashId: $scope.session.hashId,
                data: $scope.wsalemail,
                attachment: file,
                type: "EMISSAO",
                order: $rootScope.onlineOrder,
                emailType: 'EMAIL-GMAIL'
              },
              function (result) {
                if (jQuery.parseJSON(result).message.type == "S") {
                  logger.logSuccess(jQuery.parseJSON(result).message.text);
                  $scope.undo();
                  $scope.uploader.clearQueue();
                } else {
                  logger.logError(jQuery.parseJSON(result).message.text);
                }
              }
            );
          } else {
            if ($scope.tabindex == 2) {
              $.post(
                "../backend/application/index.php?rota=/mailOrder",
                {
                  hashId: $scope.session.hashId,
                  data: $scope.wsalemail,
                  attachment: file,
                  type: "EMISSAO",
                  order: $rootScope.onlineOrder,
                  cards_data: $scope.cards,
                  emailType: 'EMAIL-GMAIL'
                },
                function (result) {
                  if (jQuery.parseJSON(result).message.type == "S") {
                    logger.logSuccess(jQuery.parseJSON(result).message.text);
                    $scope.uploader.clearQueue();
                    if ($scope.reserve) $scope.selected.status = "RESERVA";
                    if ($scope.tabindex != 5) {
                      $.post(
                        "../backend/application/index.php?rota=/changeStatus",
                        {
                          hashId: $scope.session.hashId,
                          data: $scope.selected,
                          flights: $scope.onlineflights,
                        },
                        function (result) {
                          logger.logSuccess(
                            jQuery.parseJSON(result).message.text
                          );
                          $scope.selected = undefined;
                          $scope.loadSalesByFilter();
                        }
                      );
                    } else {
                      $scope.selected = undefined;
                      $scope.loadSalesByFilter();
                    }
                  } else {
                    logger.logError(jQuery.parseJSON(result).message.text);
                  }
                }
              );
            } else {
              $.post(
                "../backend/application/index.php?rota=/mailOrder",
                {
                  hashId: $scope.session.hashId,
                  data: $scope.wsalemail,
                  attachment: file,
                  type: "EMISSAO",
                  order: $rootScope.onlineOrder,
                  emailType: 'EMAIL-GMAIL'
                },
                function (result) {
                  if (jQuery.parseJSON(result).message.type == "S") {
                    logger.logSuccess(jQuery.parseJSON(result).message.text);
                    $scope.uploader.clearQueue();
                    $scope.selected = undefined;
                    $scope.loadSalesByFilter();
                  } else {
                    logger.logError(jQuery.parseJSON(result).message.text);
                  }
                }
              );
            }
          }
        };

        $scope.check48hours = function (argument) {
          var date = new Date();
          date.setDate(date.getDate() + 2);
          for (var i = 0; $scope.onlineflights.length > i; i++) {
            if (
              $scope.onlineflights[i].airline == "GOL" &&
              new Date($scope.onlineflights[i].boarding_date) < date
            ) {
              if ($scope.onlineflights[i].emissionMethod != "Companhia") {
                return true;
              }
            }
          }
          return false;
        };

        $scope.getReturnFlight = function (
          airline,
          cards_id,
          flight,
          airport_code_from,
          airport_code_to,
          flightLocator
        ) {
          $scope.filterBillet = $filter("filter")(
            $scope.onlineflights,
            airline
          );
          for (var i in $scope.filterBillet) {
            if (
              $scope.filterBillet[i].flightLocator == flightLocator &&
              $scope.filterBillet[i].flight != flight &&
              $scope.filterBillet[i].isBreak != "Quebrado"
            ) {
              return $scope.filterBillet[i];
            }
          }
          return null;
        };

        $scope.spaceLetter = function (flight_selected) {
          var letter = " ";
          var i = 0;
          while (flight_selected[i] != " ") {
            letter += flight_selected[i];
            i++;
          }

          return letter;
        };

        $scope.printBilletAzul2 = function (flight_selected) {
          $scope.billetCanbas = true;
          $scope.flight_selected = flight_selected || this.onlineflight;

          if ($scope.getIsReturn($scope.flight_selected)) {
            $scope.returnFlight = $scope.getReturnFlight(
              $scope.flight_selected.airline,
              $scope.flight_selected.cards_id,
              $scope.flight_selected.flight,
              $scope.flight_selected.airport_code_from,
              $scope.flight_selected.airport_code_to,
              $scope.flight_selected.flightLocator
            );
            if ($scope.returnFlight) {
              if (
                $rootScope.findDate($scope.returnFlight.boarding_date) <
                $rootScope.findDate($scope.flight_selected.boarding_date)
              ) {
                $scope.flight_selected = angular.copy($scope.returnFlight);
              }
            }
          }

          if ($scope.flight_selected.isBreak == "Quebrado") {
            return logger.logError("Este trecho esta quebrado!");
          }

          var start = 0;
          var doc = new jsPDF("p", "pt");
          doc.margin = 0.5;
          doc.setFont("helvetica");

          $scope.toDataUrl("images/azul/AZUL.png", function (base64Img) {
            doc.addImage(base64Img, "JPEG", 100, 15, 100, 35);

            start = 70;
            doc.setLineWidth(0.5);
            doc.setFontSize(12);
            doc.rect(100, start, 400, 40);
            doc.setTextColor(100);

            $scope.toDataUrl("images/azul/check.png", function (base64Img) {
              doc.addImage(base64Img, "JPEG", 110, start + 10, 30, 20);

              doc.text(
                150,
                start + 15,
                "Sua compra foi realizada com sucesso! Obrigada por escolher\n a Azul,"
              );
              doc.setTextColor(20);
              doc.text(200, start + 30, $scope.flight_selected.pax_name);
              // doc.text(200, start + 30, $scope.spaceLetter($scope.flight_selected.pax_name));
              start += 55;

              doc.setTextColor(100);
              doc.text(100, start, "Código da Reserva: ");
              doc.setFontType("bold");
              doc.setTextColor(20);
              doc.text(220, start, $scope.flight_selected.flightLocator);
              doc.setFontType("normal");
              start += 10;

              doc.setLineWidth(0.7);
              doc.line(100, start, 500, start);
              start += 20;

              doc.setFontType("bold");
              doc.setFontSize(8);
              doc.setTextColor(100);

              var initText =
                $scope.getWeekday($scope.flight_selected.boarding_date) +
                ", " +
                $filter("date")(
                  $rootScope.findDate($scope.flight_selected.boarding_date),
                  "dd"
                ) +
                " " +
                $scope.getLaTamMonth(
                  new Date($scope.flight_selected.boarding_date)
                ) +
                " " +
                $filter("date")(
                  $rootScope.findDate($scope.flight_selected.boarding_date),
                  "yyyy"
                );
              initText +=
                " " +
                $filter("date")(
                  new Date($scope.flight_selected.boarding_date),
                  "HH:mm"
                ) +
                " - ";
              if ($scope.getIsReturn($scope.flight_selected)) {
                initText += "IDA e VOLTA";
              } else {
                initText += "Somente IDA";
              }

              doc.text(100, start, initText);
              doc.setFontType("normal");
              start += 25;

              var from = $rootScope.airports.filter(function (airport) {
                return (
                  airport.iataCode == $scope.flight_selected.airport_code_from
                );
              })[0];
              var to = $rootScope.airports.filter(function (airport) {
                return (
                  airport.iataCode == $scope.flight_selected.airport_code_to
                );
              })[0];

              doc.setFontSize(10);
              doc.setTextColor(90);
              doc.text(
                100,
                start,
                from.city.name +
                  " (" +
                  $scope.flight_selected.airport_code_from +
                  ")                   " +
                  to.city.name +
                  " (" +
                  $scope.flight_selected.airport_code_to +
                  ")"
              );

              $scope.toDataUrl("images/azul/aviao.png", function (base64Img) {
                doc.addImage(
                  base64Img,
                  "JPEG",
                  116 +
                    (from.city.name.length +
                      $scope.flight_selected.airport_code_from.length +
                      1) *
                      5,
                  start - 10,
                  20,
                  10
                );

                start += 35;

                doc.setFontType("bold");
                doc.setFontSize(7);
                doc.setTextColor(100);
                doc.text(
                  250,
                  start,
                  "Utilize o código de barras para agilizar o check-in nos "
                );
                doc.setFontSize(10);
                doc.setTextColor(20);
                doc.text(430, start, "Totens da Azul");
                doc.setFontType("normal");

                $.post(
                  "../backend/application/index.php?rota=/loadConnectionsFlight",
                  {
                    hashId: $scope.session.hashId,
                    data: $scope.flight_selected,
                  },
                  function (result) {
                    $scope.connections = jQuery.parseJSON(result).dataset;

                    $.post(
                      "../backend/application/index.php?rota=/loadConnectionsFlight",
                      {
                        hashId: $scope.session.hashId,
                        data: $scope.getReturnFlight(
                          $scope.flight_selected.airline,
                          $scope.flight_selected.cards_id,
                          $scope.flight_selected.flight,
                          $scope.flight_selected.airport_code_from,
                          $scope.flight_selected.airport_code_to,
                          $scope.flight_selected.flightLocator
                        ),
                      },
                      function (result) {
                        $scope.connectionsReturn = jQuery.parseJSON(
                          result
                        ).dataset;

                        $("#miscCanvas").barcode(
                          $scope.flight_selected.flightLocator,
                          "code128",
                          { barWidth: 2, barHeight: 30, showHRI: false }
                        );
                        doc.setTextColor(20);
                        html2canvas($("#miscCanvas"), {
                          onrendered: function (canvas) {
                            var barcode = canvas.toDataURL("image/png");
                            doc.addImage(
                              barcode,
                              "PNG",
                              90,
                              start - 15,
                              150,
                              35
                            );

                            doc.setTextColor(0);
                            doc.setDrawColor(0);
                            doc.setTextColor(0);
                            doc.setLineWidth(0.5);

                            start = start + 30;
                            doc.autoTable(
                              [
                                {
                                  title:
                                    "IDA --- " +
                                    $scope.getWeekday(
                                      $scope.flight_selected.boarding_date
                                    ) +
                                    ", " +
                                    $filter("date")(
                                      $rootScope.findDate(
                                        $scope.flight_selected.boarding_date
                                      ),
                                      "dd"
                                    ) +
                                    " " +
                                    $scope.getLaTamMonth(
                                      new Date(
                                        $scope.flight_selected.boarding_date
                                      )
                                    ) +
                                    " " +
                                    $filter("date")(
                                      $rootScope.findDate(
                                        $scope.flight_selected.boarding_date
                                      ),
                                      "yyyy"
                                    ),
                                  dataKey: "left",
                                },
                                { title: "PONTOS TUDOAZUL", dataKey: "right" },
                              ],
                              [
                                {
                                  left:
                                    from.city.name +
                                    " (" +
                                    $scope.flight_selected.airport_code_from +
                                    ") > " +
                                    to.city.name +
                                    " (" +
                                    $scope.flight_selected.airport_code_to +
                                    ")",
                                },
                              ],
                              {
                                startY: start,
                                margin: { left: 100, right: 100 },
                                theme: "plain",
                                createdCell: function (cell, data) {
                                  if (data.column.dataKey === "right") {
                                    cell.styles.halign = "right";
                                  }
                                },
                              }
                            );
                            doc.rect(
                              100,
                              start,
                              400,
                              doc.autoTableEndPosY() - start
                            );
                            start = doc.autoTableEndPosY();

                            var toConnections = "";
                            var time = "";
                            if ($scope.connections.length > 0) {
                              toConnections =
                                $scope.connections[0].airportCodeTo;

                              time = $scope.connections[0].landing;
                              if (
                                $scope.selected.notificationurl != "" &&
                                $scope.selected.notificationurl != null
                              ) {
                                time = $scope.connections[0].landing.split(
                                  " "
                                )[1];
                              }
                            } else {
                              toConnections =
                                $scope.flight_selected.airport_code_to;
                              time = $filter("date")(
                                new Date($scope.flight_selected.landing_date),
                                "HH:mm"
                              );
                            }

                            var startFLight = start;
                            doc.autoTable(
                              [
                                {
                                  title: "Voo " + $scope.flight_selected.flight,
                                  dataKey: "left",
                                },
                                {
                                  title:
                                    $filter("date")(
                                      new Date(
                                        $scope.flight_selected.boarding_date
                                      ),
                                      "HH:mm"
                                    ) +
                                    " > " +
                                    time,
                                  dataKey: "center",
                                },
                                { title: "", dataKey: "right" },
                              ],
                              [
                                {
                                  right: "",
                                  center:
                                    $scope.flight_selected.airport_code_from +
                                    "       " +
                                    toConnections,
                                },
                              ],
                              {
                                startY: start,
                                margin: { left: 100, right: 100 },
                                theme: "plain",
                                createdCell: function (cell, data) {
                                  if (data.column.dataKey === "right") {
                                    cell.styles.halign = "right";
                                  }
                                },
                              }
                            );
                            start = doc.autoTableEndPosY();

                            for (
                              var i = 1;
                              i <= $scope.connections.length - 1;
                              i++
                            ) {
                              let boarding = $scope.connections[i].boarding;
                              let landing = $scope.connections[i].landing;
                              if (
                                $scope.selected.notificationurl != "" &&
                                $scope.selected.notificationurl != null
                              ) {
                                boarding = $scope.connections[i].boarding.split(
                                  " "
                                )[1];
                                landing = $scope.connections[i].landing.split(
                                  " "
                                )[1];
                              }

                              doc.autoTable(
                                [
                                  {
                                    title:
                                      "Voo " + $scope.connections[i].flight,
                                    dataKey: "left",
                                  },
                                  {
                                    title: boarding + " > " + landing,
                                    dataKey: "center",
                                  },
                                  { title: "", dataKey: "right" },
                                ],
                                [
                                  {
                                    right: "",
                                    center:
                                      $scope.connections[i].airportCodeFrom +
                                      "       " +
                                      $scope.connections[i].airportCodeTo,
                                  },
                                ],
                                {
                                  startY: start,
                                  margin: { left: 100, right: 100 },
                                  theme: "plain",
                                  createdCell: function (cell, data) {
                                    if (data.column.dataKey === "right") {
                                      cell.styles.halign = "right";
                                    }
                                  },
                                }
                              );
                              start = doc.autoTableEndPosY();
                            }
                            doc.rect(
                              100,
                              startFLight,
                              400,
                              doc.autoTableEndPosY() - startFLight
                            );

                            doc.autoTable(
                              [
                                { title: "Passageiro", dataKey: "pax" },
                                { title: "Trecho", dataKey: "center" },
                                { title: "Assento", dataKey: "sit" },
                                { title: "Bagagens", dataKey: "cardNumber" },
                              ],
                              $scope.getPax(
                                $scope.flight_selected.airline,
                                $scope.flight_selected.cards_id,
                                $scope.flight_selected.flight,
                                $scope.flight_selected.flightLocator
                              ),
                              {
                                startY: start,
                                margin: { left: 100, right: 100 },
                                theme: "plain",
                                headerStyles: {
                                  fontStyle: "normal",
                                },
                                bodyStyles: {
                                  rowHeight: 20,
                                  cellPadding: 5,
                                },
                                drawRow: function (row, data) {
                                  // doc.rect(15, row.y, 400, 20);
                                  doc.line(100, row.y, 500, row.y);
                                },
                              }
                            );
                            doc.rect(
                              100,
                              start,
                              400,
                              doc.autoTableEndPosY() - start
                            );
                            start = doc.autoTableEndPosY() + 20;

                            //return
                            if ($scope.getIsReturn($scope.flight_selected)) {
                              $scope.returnFlight = $scope.getReturnFlight(
                                $scope.flight_selected.airline,
                                $scope.flight_selected.cards_id,
                                $scope.flight_selected.flight,
                                $scope.flight_selected.airport_code_from,
                                $scope.flight_selected.airport_code_to,
                                $scope.flight_selected.flightLocator
                              );

                              var fromReturn = $rootScope.airports.filter(
                                function (airport) {
                                  return (
                                    airport.iataCode ==
                                    $scope.returnFlight.airport_code_from
                                  );
                                }
                              )[0];
                              var toReturn = $rootScope.airports.filter(
                                function (airport) {
                                  return (
                                    airport.iataCode ==
                                    $scope.returnFlight.airport_code_to
                                  );
                                }
                              )[0];

                              doc.autoTable(
                                [
                                  {
                                    title:
                                      "VOLTA --- " +
                                      $scope.getWeekday(
                                        $scope.returnFlight.boarding_date
                                      ) +
                                      ", " +
                                      $filter("date")(
                                        $rootScope.findDate(
                                          $scope.returnFlight.boarding_date
                                        ),
                                        "dd"
                                      ) +
                                      " " +
                                      $scope.getLaTamMonth(
                                        new Date(
                                          $scope.returnFlight.boarding_date
                                        )
                                      ) +
                                      " " +
                                      $filter("date")(
                                        $rootScope.findDate(
                                          $scope.returnFlight.boarding_date
                                        ),
                                        "yyyy"
                                      ),
                                    dataKey: "left",
                                  },
                                  {
                                    title: "PONTOS TUDOAZUL",
                                    dataKey: "right",
                                  },
                                ],
                                [
                                  {
                                    left:
                                      fromReturn.city.name +
                                      " (" +
                                      $scope.returnFlight.airport_code_from +
                                      ") > " +
                                      toReturn.city.name +
                                      " (" +
                                      $scope.returnFlight.airport_code_to +
                                      ")",
                                  },
                                ],
                                {
                                  startY: start,
                                  margin: { left: 100, right: 100 },
                                  theme: "plain",
                                  createdCell: function (cell, data) {
                                    if (data.column.dataKey === "right") {
                                      cell.styles.halign = "right";
                                    }
                                  },
                                }
                              );
                              doc.rect(
                                100,
                                start,
                                400,
                                doc.autoTableEndPosY() - start
                              );
                              start = doc.autoTableEndPosY();

                              $scope.connections;
                              var toConnections = "";
                              var time = "";
                              if ($scope.connectionsReturn.length > 0) {
                                toConnections =
                                  $scope.connectionsReturn[0].airportCodeTo;
                                time = $scope.connectionsReturn[0].landing;
                              } else {
                                toConnections =
                                  $scope.returnFlight.airport_code_to;
                                time = $filter("date")(
                                  new Date($scope.returnFlight.landing_date),
                                  "HH:mm"
                                );
                              }

                              var startFLight = start;
                              doc.autoTable(
                                [
                                  {
                                    title: "Voo " + $scope.returnFlight.flight,
                                    dataKey: "left",
                                  },
                                  {
                                    title:
                                      $filter("date")(
                                        new Date(
                                          $scope.returnFlight.boarding_date
                                        ),
                                        "HH:mm"
                                      ) +
                                      " > " +
                                      time,
                                    dataKey: "center",
                                  },
                                  { title: "", dataKey: "right" },
                                ],
                                [
                                  {
                                    right: "",
                                    center:
                                      $scope.returnFlight.airport_code_from +
                                      "       " +
                                      toConnections,
                                  },
                                ],
                                {
                                  startY: start,
                                  margin: { left: 100, right: 100 },
                                  theme: "plain",
                                  createdCell: function (cell, data) {
                                    if (data.column.dataKey === "right") {
                                      cell.styles.halign = "right";
                                    }
                                  },
                                }
                              );
                              start = doc.autoTableEndPosY();

                              for (
                                var i = 1;
                                i <= $scope.connectionsReturn.length - 1;
                                i++
                              ) {
                                doc.autoTable(
                                  [
                                    {
                                      title:
                                        "Voo " +
                                        $scope.connectionsReturn[i].flight,
                                      dataKey: "left",
                                    },
                                    {
                                      title:
                                        $scope.connectionsReturn[i].boarding +
                                        " > " +
                                        $scope.connectionsReturn[i].landing,
                                      dataKey: "center",
                                    },
                                    { title: "", dataKey: "right" },
                                  ],
                                  [
                                    {
                                      right: "",
                                      center:
                                        $scope.connectionsReturn[i]
                                          .airportCodeFrom +
                                        "       " +
                                        $scope.connectionsReturn[i]
                                          .airportCodeTo,
                                    },
                                  ],
                                  {
                                    startY: start,
                                    margin: { left: 100, right: 100 },
                                    theme: "plain",
                                    createdCell: function (cell, data) {
                                      if (data.column.dataKey === "right") {
                                        cell.styles.halign = "right";
                                      }
                                    },
                                  }
                                );
                                start = doc.autoTableEndPosY();
                              }
                              doc.rect(
                                100,
                                startFLight,
                                400,
                                doc.autoTableEndPosY() - startFLight
                              );

                              doc.autoTable(
                                [
                                  { title: "Passageiro", dataKey: "pax" },
                                  { title: "Trecho", dataKey: "center" },
                                  { title: "Assento", dataKey: "sit" },
                                  { title: "Bagagens", dataKey: "cardNumber" },
                                ],
                                $scope.getPaxReturn(
                                  $scope.returnFlight.airline,
                                  $scope.returnFlight.cards_id,
                                  $scope.returnFlight.flight,
                                  $scope.flight_selected.flightLocator
                                ),
                                {
                                  startY: start,
                                  margin: { left: 100, right: 100 },
                                  theme: "plain",
                                  headerStyles: {
                                    fontStyle: "normal",
                                  },
                                  bodyStyles: {
                                    rowHeight: 20,
                                    cellPadding: 5,
                                  },
                                  drawRow: function (row, data) {
                                    // doc.rect(15, row.y, 400, 20);
                                    doc.line(100, row.y, 500, row.y);
                                  },
                                }
                              );
                              doc.rect(
                                100,
                                start,
                                400,
                                doc.autoTableEndPosY() - start
                              );
                              start = doc.autoTableEndPosY() + 20;
                            }

                            start = doc.autoTableEndPosY() + 30;
                            doc.setTextColor(100);
                            doc.setFontSize(12);
                            doc.text(110, start, "Lembretes para sua viagem");
                            start += 15;

                            $scope.toDataUrl(
                              "images/azul/personIcol.png",
                              function (base64Img) {
                                doc.addImage(
                                  base64Img,
                                  "JPEG",
                                  110,
                                  start,
                                  25,
                                  15
                                );
                                doc.text(
                                  140,
                                  start + 15,
                                  "Leve documento de identificação com foto"
                                );
                                start += 20;

                                $scope.toDataUrl(
                                  "images/azul/timeIco.png",
                                  function (base64Img) {
                                    doc.addImage(
                                      base64Img,
                                      "JPEG",
                                      110,
                                      start,
                                      25,
                                      15
                                    );
                                    doc.text(
                                      140,
                                      start + 15,
                                      "Chegue com antecedência"
                                    );
                                    start += 20;

                                    $scope.toDataUrl(
                                      "images/azul/seatIco.png",
                                      function (base64Img) {
                                        doc.addImage(
                                          base64Img,
                                          "JPEG",
                                          110,
                                          start,
                                          25,
                                          15
                                        );
                                        doc.text(
                                          140,
                                          start + 15,
                                          "Marque seu assento"
                                        );
                                        start += 20;

                                        $scope.toDataUrl(
                                          "images/azul/pcIco.png",
                                          function (base64Img) {
                                            doc.addImage(
                                              base64Img,
                                              "JPEG",
                                              110,
                                              start,
                                              25,
                                              15
                                            );
                                            doc.text(
                                              140,
                                              start + 15,
                                              "Faça seu check-in pela Internet"
                                            );
                                            start += 25;

                                            doc.autoTable(
                                              [
                                                {
                                                  title:
                                                    "Outros avisos importantes",
                                                  dataKey: "text",
                                                },
                                              ],
                                              [
                                                {
                                                  text:
                                                    "1. Comparecer para o embarque 1 (uma) hora antes da partida do voo, munido de documento de",
                                                },
                                                {
                                                  text:
                                                    " identificação original com fotografia.",
                                                },
                                                {
                                                  text:
                                                    "2. O bilhete não é endossável.",
                                                },
                                                {
                                                  text:
                                                    "3. O bilhete eletrônico é válido por um ano após a data de emissão. No entanto, a tarifa é válida somente para",
                                                },
                                                {
                                                  text:
                                                    " as datas do voo especificado.",
                                                },
                                                {
                                                  text:
                                                    "4. Para remarcação, remissão, reembolso e não comparecimento ao embarque (noshow), consulte",
                                                },
                                                {
                                                  text:
                                                    " penalidades no site voeazul.com.br, na seção Dúvidas Frequentes.",
                                                },
                                                {
                                                  text:
                                                    "5. No caso de reembolso, contatar a companhia aérea no telefone 4003-1118",
                                                },
                                                {
                                                  text:
                                                    "O contrato de transporte de passagens encontra-se disponível em nossas lojas ou em nosso site.",
                                                },
                                                {
                                                  text:
                                                    "A sua escolha é muito importante para nós!",
                                                },
                                              ],
                                              {
                                                startY: start,
                                                margin: {
                                                  left: 100,
                                                  right: 100,
                                                },
                                                styles: {
                                                  fontSize: 8,
                                                },
                                                theme: "plain",
                                                headerStyles: {
                                                  fontSize: 12,
                                                  fontStyle: "normal",
                                                },
                                                bodyStyles: {
                                                  rowHeight: 10,
                                                  cellPadding: 2,
                                                },
                                                drawRow: function (row, data) {
                                                  if (row.index === 0) {
                                                    doc.line(
                                                      100,
                                                      row.y,
                                                      450,
                                                      row.y
                                                    );
                                                  }
                                                },
                                              }
                                            );

                                            start = doc.autoTableEndPosY() + 10;
                                            $scope.billetCanbas = false;
                                            doc.save(
                                              $scope.flight_selected
                                                .flightLocator + ".pdf"
                                            );

                                            if (
                                              $scope.multiDownloads !== true
                                            ) {
                                              if ($scope.response) {
                                                if (
                                                  $scope.response
                                                    .notificationurl != null &&
                                                  $scope.response.notificationurl.indexOf(
                                                    "http"
                                                  ) == -1
                                                ) {
                                                  $scope.fillEmailBillet();
                                                }
                                              } else {
                                                $scope.fillEmailBillet();
                                              }
                                              $scope.$apply();
                                            }
                                          }
                                        );
                                      }
                                    );
                                  }
                                );
                              }
                            );
                          },
                        });
                      }
                    );
                  }
                );
              });
            });
          });
        };

        $scope.printBilletAvianca = function (flight_selected) {
          $scope.flight_selected = flight_selected || this.onlineflight;

          if ($scope.flight_selected.isBreak == "Quebrado") {
            return logger.logError("Este trecho esta quebrado!");
          }

          var start = 0;
          var doc = new jsPDF("p", "pt");
          doc.margin = 0.5;
          doc.setFont("helvetica");
          doc.setFontSize(16);
          var bagagge = 0;
          var con,
            ret = 0;

          var flights = $scope.getAviancaFlights($scope.flight_selected);

          $scope.toDataUrl("images/avianca/avianca.png", function (base64Img) {
            doc.addImage(base64Img, "JPEG", 15, 17, 565, 90);

            doc.setFontType("bold");
            doc.text(20, 120, "Recibo do Etkt / Electronic Ticket Receipt");

            doc.setDrawColor(0);
            doc.setFillColor(240, 240, 240);
            doc.rect(15, 140, 360, 30, "F");

            doc.setFontSize(12);
            doc.setTextColor(0);
            doc.setFontType("bold");
            doc.text(
              20,
              160,
              "Localizador / Booking Reference: " +
                $scope.flight_selected.flightLocator
            );

            doc.setFontSize(11);
            doc.text(
              20,
              180,
              "No check-in você deverá apresentar um documento original com foto."
            );
            doc.setFontType("normal");
            doc.text(20, 190, "At check-in, you must show a photo ID.");

            doc.setFontType("bold");
            doc.setDrawColor(0);
            doc.setFillColor(240, 240, 240);
            doc.rect(390, 140, 200, 70, "F");

            doc.setFontSize(12);
            doc.text(400, 160, "Escritório / Office");
            doc.setFontSize(9);
            doc.setFontType("normal");
            doc.text(400, 170, "AVIANCA BRASIL");
            doc.text(400, 180, "AV WASHINGTON LUIS, 7059 CAMPO BELO");
            doc.text(400, 190, "SAO PAULO");
            doc.text(400, 200, "TELEFONE / TELEPHONE: 4004 4040");

            //passanger and ticket descriptions
            doc.setFontSize(12);
            doc.text(31, 240, "Passageiro / Passenger");
            // doc.text(220, 240, 'Número do bilhete / Ticket number');

            doc.setLineWidth(1);
            doc.line(20, 250, 180, 250);
            // doc.line(210, 250, 400, 250);
            var fullName = $scope.flight_selected.pax_name;
            if ($scope.flight_selected.paxLastName) {
              fullName += " " + $scope.flight_selected.paxLastName;
            }
            if ($scope.flight_selected.paxAgnome) {
              fullName += " " + $scope.flight_selected.paxAgnome;
            }

            bagagge = $scope.flight_selected.baggage;
            bagagge = 0;

            var paxName = fullName.split(" ");
            var name = "";

            if (
              paxName[paxName.length - 1] == "" ||
              paxName[paxName.length - 1] == " "
            ) {
              paxName.splice(paxName.length - 1, 1);
            }

            var lastName = 0;
            if (paxName.length > 2) {
              if ($scope.checkPaxName(paxName[paxName.length - 1])) {
                name =
                  paxName[paxName.length - 2] +
                  " " +
                  paxName[paxName.length - 1] +
                  " ";
                lastName = 2;
              } else {
                name = paxName[paxName.length - 1] + " ";
                lastName = 1;
              }
            } else {
              name = paxName[paxName.length - 1] + " ";
              lastName = 1;
            }

            for (var i = 0; i <= paxName.length - 1 - lastName; i++) {
              name += paxName[i] + " ";
            }

            doc.setFontSize(10);
            doc.text(25, 265, name + $scope.getPaxSex($scope.flight_selected));
            doc.text(
              25,
              280,
              "(" + $scope.getPaxType($scope.flight_selected) + ")"
            );
            // doc.text(220, 265, $scope.flight_selected.ticket_code);

            doc.setFontSize(16);
            doc.setFontType("bold");
            doc.text(20, 310, "Itinerário / Itinerary");
            start = 330;

            var rows = [
              {
                from: "From",
                to: "To",
                Flight: "Flight",
                Class: "Class",
                Date: "Date",
                Departure: "Departure",
                Arrival: "Arrival",
                Resa1: "Resa (1)",
                NVB2: "NVB(2)",
                NVA3: "NVA(3)",
                check_in: "Last check-in",
                Baggage: "Baggage",
                Seat: "Seat",
              },
            ];

            rows.push({
              from: $scope.flight_selected.airport_code_from,
              to: $scope.flight_selected.airport_code_to,
              Flight: $scope.flight_selected.flight,
              Class: "X",
              Date:
                $filter("date")(
                  new Date($scope.flight_selected.boarding_date),
                  "dd"
                ) +
                $scope.getAviancaMonth(
                  new Date($scope.flight_selected.boarding_date)
                ),
              Departure: $filter("date")(
                new Date($scope.flight_selected.boarding_date),
                "HH:mm"
              ),
              Arrival: $filter("date")(
                new Date($scope.flight_selected.landing_date),
                "HH:mm"
              ),
              Resa1: "Ok",
              Baggage: bagagge + "PC",
              Seat: $scope.flight_selected.seat,
            });

            $.post(
              "../backend/application/index.php?rota=/loadConnectionsFlight",
              { data: $scope.flight_selected },
              function (result) {
                $scope.connectionsAvianca = jQuery.parseJSON(result).dataset;
                var connect = new Array();
                connect.push(angular.copy($scope.flight_selected.seat));

                if ($scope.flight_selected.connections) {
                  for (
                    var i = 0;
                    i < $scope.flight_selected.connections.length;
                    i++
                  ) {
                    connect.push($scope.flight_selected.connections[i].seat);
                  }
                }

                var dateReportDeparture = new Date(
                  $scope.flight_selected.boarding_date
                );

                for (var i = 0; i < $scope.connectionsAvianca.length; i++) {
                  rows.push({});
                  rows.push({});
                  rows.push({ from: "Frequent flyer number" });

                  if (i > 0) {
                    if (
                      parseInt(
                        $scope.connectionsAvianca[i].boarding.split(":")[0]
                      ) <
                      parseInt(
                        $scope.connectionsAvianca[i - 1].landing.split(":")[0]
                      )
                    ) {
                      dateReportDeparture.setDate(
                        dateReportDeparture.getDate() + 1
                      );
                    }
                  }
                  rows.push({
                    from: $scope.connectionsAvianca[i].airport_code_from,
                    to: $scope.connectionsAvianca[i].airport_code_to,
                    Flight: $scope.connectionsAvianca[i].flight,
                    Class: "X",
                    //Date: $filter('date')(new Date($scope.flight_selected.boarding_date),'dd') + $scope.getAviancaMonth(new Date($scope.flight_selected.boarding_date)),
                    Date:
                      $filter("date")(dateReportDeparture, "dd") +
                      $scope.getAviancaMonth(new Date(dateReportDeparture)),
                    Departure: $scope.connectionsAvianca[i].boarding,
                    Arrival: $scope.connectionsAvianca[i].landing,
                    Resa1: "Ok",
                    Baggage: bagagge + "PC",
                    Seat: connect[i],
                  });
                }

                rows.push({});
                rows.push({});
                rows.push({ from: "Frequent flyer number" });

                if (flights.length > 0) {
                  $.post(
                    "../backend/application/index.php?rota=/loadConnectionsFlight",
                    { data: flights[0] },
                    function (result) {
                      $scope.connectionsAvianca = jQuery.parseJSON(
                        result
                      ).dataset;

                      var connectReturn = new Array();
                      connectReturn.push(angular.copy(flights[0].seat));

                      con = 0;
                      for (var i in flights) {
                        //connectReturn.push(angular.copy($scope.flights[i].seat));

                        con = flights[i].baggage;
                        con = 0;

                        if (flights[i].connections) {
                          for (
                            var j = 0;
                            j < flights[i].connections.length;
                            j++
                          ) {
                            connectReturn.push(flights[i].connections[j].seat);
                          }
                        }

                        rows.push({
                          from: flights[i].airport_code_from,
                          to: flights[i].airport_code_to,
                          Flight: flights[i].flight,
                          Class: "X",
                          Date:
                            $filter("date")(
                              new Date(flights[i].boarding_date),
                              "dd"
                            ) +
                            $scope.getAviancaMonth(
                              new Date(flights[i].boarding_date)
                            ),
                          Departure: $filter("date")(
                            new Date(flights[i].boarding_date),
                            "HH:mm"
                          ),
                          Arrival: $filter("date")(
                            new Date(flights[i].landing_date),
                            "HH:mm"
                          ),
                          Resa1: "Ok",
                          Baggage: con + "PC",
                          Seat: flights[i].seat,
                        });

                        rows.push({});
                        rows.push({});
                        rows.push({ from: "Frequent flyer number" });
                      }

                      var dateReportDepartureReturn = new Date(
                        flights[0].boarding_date
                      );
                      for (var i in $scope.connectionsAvianca) {
                        rows.push({});
                        rows.push({});
                        rows.push({ from: "Frequent flyer number" });

                        if (i > 0) {
                          if (
                            parseInt(
                              $scope.connectionsAvianca[i].boarding.split(
                                ":"
                              )[0]
                            ) <
                            parseInt(
                              $scope.connectionsAvianca[i - 1].landing.split(
                                ":"
                              )[0]
                            )
                          ) {
                            dateReportDepartureReturn.setDate(
                              dateReportDeparture.getDate() + 1
                            );
                          }
                        }

                        rows.push({
                          from: $scope.connectionsAvianca[i].airport_code_from,
                          to: $scope.connectionsAvianca[i].airport_code_to,
                          Flight: $scope.connectionsAvianca[i].flight,
                          Class: "X",
                          // Date: $filter('date')(new Date(flights[0].boarding_date),'dd') + $scope.getAviancaMonth(new Date(flights[0].boarding_date)),
                          Date:
                            $filter("date")(dateReportDepartureReturn, "dd") +
                            $scope.getAviancaMonth(
                              new Date(dateReportDepartureReturn)
                            ),
                          Departure: $scope.connectionsAvianca[i].boarding,
                          Arrival: $scope.connectionsAvianca[i].landing,
                          Resa1: "Ok",
                          Baggage: con + "PC",
                          Seat: connectReturn[i],
                        });
                      }

                      rows.push({});
                      rows.push({});
                      rows.push({ from: "Frequent flyer number" });

                      doc.autoTable(
                        [
                          { title: "De", dataKey: "from" },
                          { title: "Para", dataKey: "to" },
                          { title: "Voo", dataKey: "Flight" },
                          { title: "Classe", dataKey: "Class" },
                          { title: "Data", dataKey: "Date" },
                          { title: "Partida", dataKey: "Departure" },
                          { title: "Chegada", dataKey: "Arrival" },
                          { title: "Resa", dataKey: "Resa1" },
                          { title: "NVB", dataKey: "NVB2" },
                          { title: "NVA", dataKey: "NVA3" },
                          {
                            title: "Prazo para efetuar o Check-in",
                            dataKey: "check_in",
                          },
                          { title: "Bagagem", dataKey: "Baggage" },
                          { title: "Assento", dataKey: "Seat" },
                        ],
                        rows,
                        {
                          startY: start,
                          margin: { horizontal: 15 },
                          fontSize: 8,
                          styles: {
                            overflow: "linebreak",
                            cellPadding: 1,
                          },
                          columnStyles: {
                            from: { columnWidth: 60 },
                            to: { columnWidth: 60 },
                          },
                          theme: "plain",
                          drawRow: function (row, data) {
                            if (
                              row.index === 2 ||
                              row.index === 6 ||
                              row.index === 10 ||
                              row.index === 14 ||
                              row.index === 18 ||
                              row.index === 22 ||
                              row.index === 26 ||
                              row.index === 30 ||
                              row.index === 34 ||
                              row.index === 38 ||
                              row.index === 42
                            ) {
                              doc.autoTableText(
                                "Operado Por / Operated by AVIANCA BRASIL  Comercializado por / Marketed by AVIANCA BRASIL",
                                data.settings.margin.left + 5,
                                row.y + 10,
                                {
                                  valign: "middle",
                                }
                              );
                              data.cursor.y += 20;
                            }
                            if (
                              row.index === 3 ||
                              row.index === 7 ||
                              row.index === 11 ||
                              row.index === 15 ||
                              row.index === 19 ||
                              row.index === 23 ||
                              row.index === 27 ||
                              row.index === 31 ||
                              row.index === 35 ||
                              row.index === 39 ||
                              row.index === 43
                            ) {
                              doc.autoTableText(
                                "Número de Passageiro Frequente /  " +
                                  $scope.flight_selected.card_number,
                                data.settings.margin.left + 5,
                                row.y + 10,
                                {
                                  valign: "middle",
                                }
                              );
                              data.cursor.y += 20;
                            }
                          },
                        }
                      );
                      start = doc.autoTableEndPosY() + 10;

                      doc.setDrawColor(0);
                      doc.setLineWidth(1);
                      doc.line(15, start, 565, start);

                      start += 10;

                      doc.autoTable(
                        [{ title: "", dataKey: "text" }],
                        [
                          {
                            text:
                              "(1) Ok = confirmado / confirmed (2) NVB = Sem validade antes de / Not valid before (3) NVA = Sem validade depois de / Not valid after ",
                          },
                        ],
                        {
                          startY: start,
                          pageBreak: "avoid",
                          cellPadding: 5,
                          margin: { horizontal: 10 },
                          fontSize: 8,
                          theme: "plain",
                          headerStyles: {
                            fontSize: 10,
                          },
                          styles: {
                            cellPadding: 2,
                          },
                        }
                      );
                      start = doc.autoTableEndPosY();

                      $scope.toDataUrl(
                        "images/avianca/aviancaCheckIn.png",
                        function (base64Img) {
                          doc.addImage(base64Img, "JPEG", 15, start, 565, 90);

                          start += 110;
                          if (start >= 295) {
                            doc.addPage();
                            start = 20;
                          }

                          doc.setFontSize(16);
                          doc.setFontType("bold");
                          doc.text(20, start, "Recibo / Receipt");

                          var taxCard = "";
                          if ($scope.flight_selected.tax_card) {
                            for (
                              var i = 0;
                              i < $scope.flight_selected.tax_card.length - 5;
                              i++
                            ) {
                              taxCard += "X";
                            }

                            taxCard +=
                              $scope.flight_selected.tax_card[
                                $scope.flight_selected.tax_card.length - 4
                              ];
                            taxCard +=
                              $scope.flight_selected.tax_card[
                                $scope.flight_selected.tax_card.length - 3
                              ];
                            taxCard +=
                              $scope.flight_selected.tax_card[
                                $scope.flight_selected.tax_card.length - 2
                              ];
                            taxCard +=
                              $scope.flight_selected.tax_card[
                                $scope.flight_selected.tax_card.length - 1
                              ];
                          }

                          start += 20;

                          var taxCalculation = "";
                          taxCalculation +=
                            ": " +
                            $scope.flight_selected.airport_code_from +
                            " 06 ";
                          if (flights.length > 0) {
                            for (var i in flights) {
                              taxCalculation +=
                                " X/" + flights[i].airport_code_from + " 06 ";
                            }
                            taxCalculation +=
                              flights[flights.length - 1].airport_code_to;
                          } else {
                            taxCalculation +=
                              $scope.flight_selected.airport_code_to;
                          }

                          doc.autoTable(
                            [
                              { title: "Nome / Name", dataKey: "text" },
                              {
                                title:
                                  ": " +
                                  name +
                                  $scope.getPaxSex($scope.flight_selected) +
                                  " ( " +
                                  $scope.getPaxType($scope.flight_selected) +
                                  ")",
                                dataKey: "pax",
                              },
                            ],
                            [
                              // {text: "Número do bilhete / Ticket number" , pax: ": " + $scope.flight_selected.ticket_code + ""},
                              {
                                text: "Forma de pagamento / Form of payment",
                                pax:
                                  ": CC " +
                                  taxCard +
                                  " Exp " +
                                  $filter("date")(
                                    new Date(
                                      $scope.flight_selected.tax_dueDate
                                    ),
                                    "mm"
                                  ) +
                                  $filter("date")(
                                    new Date(
                                      $scope.flight_selected.tax_dueDate
                                    ),
                                    "yy"
                                  ) +
                                  " S207728 : " +
                                  $scope.flight_selected.tax_billet,
                              },
                              {
                                text: "Forma de pagamento / Form of payment",
                                pax: ": FFR : 0.00 : BRL2",
                              },
                              { text: "Tarifa / Fare", pax: ": USD 0.00" },
                              {
                                text: "Tarifa Equivalente / Fare Equivalent",
                                pax: ": BRL 0.00",
                              },
                              {
                                text: "tax_billetas / Taxes",
                                pax:
                                  ": BRL " +
                                  $scope.flight_selected.tax_billet +
                                  " BR",
                              },
                              {
                                text: "Valor Total / Total Amount",
                                pax:
                                  ": BRL " +
                                  $scope.getTotalTaxFlight(
                                    $scope.flight_selected.flightLocator
                                  ) +
                                  "",
                              },
                              {
                                text:
                                  "Cia Aérea Emissora e data / Issuing Airline and date",
                                pax:
                                  ": AVIANCA BRASIL " +
                                  $filter("date")(new Date(), "dd") +
                                  $scope.getAviancaMonth(new Date()) +
                                  $filter("date")(new Date(), "yy"),
                              },
                              { text: "IATA / IATA", pax: ": 57996643" },
                              {
                                text: "Cálculo de Tarifa / Fare Calculation",
                                pax: taxCalculation + " 0.00USD0.00END",
                              },
                            ],
                            {
                              startY: start,
                              margin: { horizontal: 10 },
                              fontSize: 9,
                              pageBreak: "auto",
                              theme: "plain",
                              styles: {
                                cellPadding: 3,
                              },
                            }
                          );
                          start = doc.autoTableEndPosY() + 10;

                          doc.autoTable(
                            [{ title: "", dataKey: "text" }],
                            [
                              {
                                text:
                                  "A tarifa aplicada na data da compra só é válida para o itinerário e as datas específicas mencionadas no bilhete.",
                              },
                              {
                                text:
                                  "The fare that applies on the date of purchase is only valid for the entire itinerary and the specific travel dates mentioned on the ticket.",
                              },
                            ],
                            {
                              startY: start,
                              pageBreak: "avoid",
                              margin: { horizontal: 5 },
                              theme: "plain",
                              fontSize: 9,
                              styles: {
                                cellPadding: 2,
                              },
                            }
                          );
                          start = doc.autoTableEndPosY() + 10;

                          $scope.toDataUrl(
                            "images/avianca/aviancaCentral.png",
                            function (base64Img) {
                              doc.addImage(
                                base64Img,
                                "JPEG",
                                15,
                                start,
                                565,
                                90
                              );

                              start += 100;

                              doc.setDrawColor(0);
                              doc.setLineWidth(1.5);
                              doc.line(15, start, 565, start);

                              doc.autoTable(
                                [
                                  {
                                    title:
                                      "Ocean Air Linhas Aéreas S.A., ('Avianca Brasil')",
                                    dataKey: "text",
                                  },
                                ],
                                [
                                  {
                                    text:
                                      "Av. Washington Luis, 7059, Campo Belo, São Paulo - SP - 04627-006",
                                  },
                                  { text: "CNPJ/MF n°. 02.575.829/0001-48" },
                                ],
                                {
                                  startY: start,
                                  styles: {
                                    cellPadding: 2,
                                    halign: "center",
                                    valign: "middle",
                                  },
                                  headerStyles: { rowHeight: 15, fontSize: 8 },
                                  bodyStyles: {
                                    rowHeight: 12,
                                    fontSize: 8,
                                    valign: "middle",
                                  },
                                  margin: { horizontal: 5 },
                                  theme: "plain",
                                }
                              );
                              start = doc.autoTableEndPosY() + 10;

                              doc.setDrawColor(0);
                              doc.line(15, start, 565, start);

                              start += 10;

                              doc.autoTable(
                                [
                                  {
                                    title:
                                      "O transporte de certos materiais perigosos, como aerossóis, fogos de artifício, e líquidos inflamáveis, a bordo da aeronave é proibido. Se você tem algumas dúvidas com estas restrições, pode obter mais informações junto a sua companhia aérea.",
                                    dataKey: "text",
                                  },
                                ],
                                [
                                  {
                                    text:
                                      "The carriage of certain hazardous materials, like aerosols, fireworks,and flammable liquids, aboard the aircraft is forbidden. If you do not understand these restrictions, further information may be obtained from your airline.",
                                  },
                                ],
                                {
                                  startY: start,
                                  pageBreak: "avoid",
                                  margin: { horizontal: 5 },
                                  theme: "plain",
                                  styles: { overflow: "linebreak" },
                                  fontSize: 10,
                                }
                              );
                              start = doc.autoTableEndPosY() + 10;

                              doc.save(
                                $scope.flight_selected.flightLocator + ".pdf"
                              );
                              if ($scope.response) {
                                if (
                                  $scope.response.notificationurl != null &&
                                  $scope.response.notificationurl.indexOf(
                                    "http"
                                  ) == -1
                                ) {
                                  $scope.fillEmailBillet();
                                }
                              } else {
                                $scope.fillEmailBillet();
                              }
                            }
                          );
                        }
                      );
                    }
                  );
                } else {
                  doc.autoTable(
                    [
                      { title: "De", dataKey: "from" },
                      { title: "Para", dataKey: "to" },
                      { title: "Voo", dataKey: "Flight" },
                      { title: "Classe", dataKey: "Class" },
                      { title: "Data", dataKey: "Date" },
                      { title: "Partida", dataKey: "Departure" },
                      { title: "Chegada", dataKey: "Arrival" },
                      { title: "Resa", dataKey: "Resa1" },
                      { title: "NVB", dataKey: "NVB2" },
                      { title: "NVA", dataKey: "NVA3" },
                      {
                        title: "Prazo para efetuar o Check-in",
                        dataKey: "check_in",
                      },
                      { title: "Bagagem", dataKey: "Baggage" },
                      { title: "Assento", dataKey: "Seat" },
                    ],
                    rows,
                    {
                      startY: start,
                      margin: { horizontal: 15 },
                      fontSize: 7,
                      styles: {
                        overflow: "linebreak",
                        cellPadding: 2,
                        //columnWidth: 46
                      },
                      columnStyles: {
                        from: { columnWidth: 80 },
                        to: { columnWidth: 80 },
                      },
                      theme: "plain",
                      drawRow: function (row, data) {
                        if (
                          row.index === 2 ||
                          row.index === 6 ||
                          row.index === 10 ||
                          row.index === 14 ||
                          row.index === 18 ||
                          row.index === 22 ||
                          row.index === 26 ||
                          row.index === 30 ||
                          row.index === 34 ||
                          row.index === 38 ||
                          row.index === 42
                        ) {
                          doc.autoTableText(
                            "Operado Por / Operated by AVIANCA BRASIL   Comercializado por / Marketed by     AVIANCA BRASIL",
                            data.settings.margin.left + 10,
                            row.y + 10,
                            {
                              valign: "middle",
                            }
                          );
                          data.cursor.y += 20;
                        }
                        if (
                          row.index === 3 ||
                          row.index === 7 ||
                          row.index === 11 ||
                          row.index === 15 ||
                          row.index === 19 ||
                          row.index === 23 ||
                          row.index === 27 ||
                          row.index === 31 ||
                          row.index === 35 ||
                          row.index === 39 ||
                          row.index === 43
                        ) {
                          doc.autoTableText(
                            "Número de Passageiro Frequente /     " +
                              $scope.flight_selected.card_number,
                            data.settings.margin.left + 10,
                            row.y + 10,
                            {
                              valign: "middle",
                            }
                          );
                          data.cursor.y += 20;
                        }
                      },
                    }
                  );
                  start = doc.autoTableEndPosY() + 10;

                  doc.setDrawColor(0);
                  doc.setLineWidth(1);
                  doc.line(15, start, 565, start);

                  start += 10;

                  doc.autoTable(
                    [{ title: "", dataKey: "text" }],
                    [
                      {
                        text:
                          "(1) Ok = confirmado / confirmed (2) NVB = Sem validade antes de / Not valid before (3) NVA = Sem validade depois de / Not valid after",
                      },
                    ],
                    {
                      startY: start,
                      pageBreak: "avoid",
                      cellPadding: 5,
                      margin: { horizontal: 10 },
                      fontSize: 8,
                      theme: "plain",
                      headerStyles: {
                        fontSize: 10,
                      },
                      styles: {
                        cellPadding: 2,
                      },
                    }
                  );
                  start = doc.autoTableEndPosY();

                  $scope.toDataUrl(
                    "images/avianca/aviancaCheckIn.png",
                    function (base64Img) {
                      doc.addImage(base64Img, "JPEG", 15, start, 565, 90);

                      start += 110;
                      if (start >= 295) {
                        doc.addPage();
                        start = 20;
                      }

                      doc.setFontSize(16);
                      doc.setFontType("bold");
                      doc.text(20, start, "Recibo / Receipt");

                      var taxCard = "";
                      if ($scope.flight_selected.tax_card) {
                        for (
                          var i = 0;
                          i < $scope.flight_selected.tax_card.length - 5;
                          i++
                        ) {
                          taxCard += "X";
                        }

                        taxCard +=
                          $scope.flight_selected.tax_card[
                            $scope.flight_selected.tax_card.length - 4
                          ];
                        taxCard +=
                          $scope.flight_selected.tax_card[
                            $scope.flight_selected.tax_card.length - 3
                          ];
                        taxCard +=
                          $scope.flight_selected.tax_card[
                            $scope.flight_selected.tax_card.length - 2
                          ];
                        taxCard +=
                          $scope.flight_selected.tax_card[
                            $scope.flight_selected.tax_card.length - 1
                          ];
                      }

                      start += 20;

                      var taxCalculation = "";
                      taxCalculation +=
                        ": " +
                        $scope.flight_selected.airport_code_from +
                        " 06 ";
                      if (flights.length > 0) {
                        for (var i in flights) {
                          taxCalculation +=
                            " X/" + flights[i].airport_code_from + " 06 ";
                        }
                        taxCalculation +=
                          flights[flights.length - 1].airport_code_to;
                      } else {
                        taxCalculation +=
                          $scope.flight_selected.airport_code_to;
                      }

                      doc.autoTable(
                        [
                          { title: "Nome / Name", dataKey: "text" },
                          {
                            title:
                              ": " +
                              name +
                              $scope.getPaxSex($scope.flight_selected) +
                              " ( " +
                              $scope.getPaxType($scope.flight_selected) +
                              ")",
                            dataKey: "pax",
                          },
                        ],
                        [
                          // {text: "Número do bilhete / Ticket number" , pax: ": " + $scope.flight_selected.ticket_code + ""},
                          {
                            text: "Forma de pagamento / Form of payment",
                            pax:
                              ": CC " +
                              taxCard +
                              " Exp " +
                              $filter("date")(
                                new Date($scope.flight_selected.tax_dueDate),
                                "mm"
                              ) +
                              $filter("date")(
                                new Date($scope.flight_selected.tax_dueDate),
                                "yy"
                              ) +
                              " S207728 : " +
                              $scope.flight_selected.tax_billet,
                          },
                          {
                            text: "Forma de pagamento / Form of payment",
                            pax: ": FFR : 0.00 : BRL2",
                          },
                          { text: "Tarifa / Fare", pax: ": USD 0.00" },
                          {
                            text: "Tarifa Equivalente / Fare Equivalent",
                            pax: ": BRL 0.00",
                          },
                          {
                            text: "Taxas / Taxes",
                            pax:
                              ": BRL " +
                              $scope.flight_selected.tax_billet +
                              " BR",
                          },
                          {
                            text: "Valor Total / Total Amount",
                            pax:
                              ": BRL " +
                              $scope.getTotalTaxFlight(
                                $scope.flight_selected.flightLocator
                              ) +
                              "",
                          },
                          {
                            text:
                              "Cia Aérea Emissora e data / Issuing Airline and date",
                            pax:
                              ": AVIANCA BRASIL " +
                              $filter("date")(new Date(), "dd") +
                              $scope.getAviancaMonth(new Date()) +
                              $filter("date")(new Date(), "yy"),
                          },
                          { text: "IATA / IATA", pax: ": 57996643" },
                          {
                            text: "Cálculo de Tarifa / Fare Calculation",
                            pax: taxCalculation + " 0.00USD0.00END",
                          },
                        ],
                        {
                          startY: start,
                          margin: { horizontal: 10 },
                          fontSize: 9,
                          pageBreak: "auto",
                          theme: "plain",
                          styles: {
                            cellPadding: 3,
                          },
                        }
                      );
                      start = doc.autoTableEndPosY() + 10;

                      doc.autoTable(
                        [{ title: "", dataKey: "text" }],
                        [
                          {
                            text:
                              "A tarifa aplicada na data da compra só é válida para o itinerário e as datas específicas mencionadas no bilhete.",
                          },
                          {
                            text:
                              "The fare that applies on the date of purchase is only valid for the entire itinerary and the specific travel dates mentioned on the ticket.",
                          },
                        ],
                        {
                          startY: start,
                          pageBreak: "avoid",
                          margin: { horizontal: 5 },
                          theme: "plain",
                          fontSize: 9,
                          styles: {
                            cellPadding: 2,
                          },
                        }
                      );
                      start = doc.autoTableEndPosY() + 10;

                      $scope.toDataUrl(
                        "images/avianca/aviancaCentral.png",
                        function (base64Img) {
                          doc.addImage(base64Img, "JPEG", 15, start, 565, 90);

                          start += 100;

                          doc.setDrawColor(0);
                          doc.setLineWidth(1.5);
                          doc.line(15, start, 565, start);

                          doc.autoTable(
                            [
                              {
                                title:
                                  "Ocean Air Linhas Aéreas S.A., ('Avianca Brasil')",
                                dataKey: "text",
                              },
                            ],
                            [
                              {
                                text:
                                  "Av. Washington Luis, 7059, Campo Belo, São Paulo - SP - 04627-006",
                              },
                              { text: "CNPJ/MF n°. 02.575.829/0001-48" },
                            ],
                            {
                              startY: start,
                              styles: {
                                cellPadding: 2,
                                halign: "center",
                                valign: "middle",
                              },
                              headerStyles: { rowHeight: 15, fontSize: 8 },
                              bodyStyles: {
                                rowHeight: 12,
                                fontSize: 8,
                                valign: "middle",
                              },
                              margin: { horizontal: 5 },
                              theme: "plain",
                            }
                          );
                          start = doc.autoTableEndPosY() + 10;

                          doc.setDrawColor(0);
                          doc.line(15, start, 565, start);

                          start += 10;

                          doc.autoTable(
                            [
                              {
                                title:
                                  "O transporte de certos materiais perigosos, como aerossóis, fogos de artifício, e líquidos inflamáveis, a bordo da aeronave é proibido. Se você tem algumas dúvidas com estas restrições, pode obter mais informações junto a sua companhia aérea.",
                                dataKey: "text",
                              },
                            ],
                            [
                              {
                                text:
                                  "The carriage of certain hazardous materials, like aerosols, fireworks,and flammable liquids, aboard the aircraft is forbidden. If you do not understand these restrictions, further information may be obtained from your airline.",
                              },
                            ],
                            {
                              startY: start,
                              pageBreak: "avoid",
                              margin: { horizontal: 5 },
                              theme: "plain",
                              styles: { overflow: "linebreak" },
                              fontSize: 10,
                            }
                          );
                          start = doc.autoTableEndPosY() + 10;

                          doc.save(
                            $scope.flight_selected.flightLocator + ".pdf"
                          );

                          if ($scope.multiDownloads !== true) {
                            if ($scope.response) {
                              if (
                                $scope.response.notificationurl != null &&
                                $scope.response.notificationurl.indexOf(
                                  "http"
                                ) == -1
                              ) {
                                $scope.fillEmailBillet();
                              }
                            } else {
                              $scope.fillEmailBillet();
                            }
                          }
                        }
                      );
                    }
                  );
                }
              }
            );
          });
        };

        $scope.getAviancaFlights = function (flight_selected) {
          var flights = [];
          for (var i in $scope.onlineflights) {
            if (
              $scope.onlineflights[i].flightLocator ==
                flight_selected.flightLocator &&
              $scope.onlineflights[i].cards_id == flight_selected.cards_id &&
              $scope.onlineflights[i].pax_name == flight_selected.pax_name &&
              $scope.onlineflights[i].paxLastName ==
                flight_selected.paxLastName &&
              $scope.onlineflights[i].paxAgnome == flight_selected.paxAgnome &&
              $scope.onlineflights[i].flight != flight_selected.flight
            ) {
              flights.push($scope.onlineflights[i]);
            }
          }
          return flights;
        };

        $scope.getPaxSex = function (flight) {
          if (flight.gender == "Masculino") {
            return "Mr";
          }
          return "Ms";
        };

        $scope.getAviancaMonth = function (date) {
          var day = date.getMonth();
          switch (day) {
            case 0:
              return "Jan";
            case 1:
              return "Feb";
            case 2:
              return "Mar";
            case 3:
              return "Apr";
            case 4:
              return "May";
            case 5:
              return "Jun";
            case 6:
              return "Jul";
            case 7:
              return "Aug";
            case 8:
              return "Sep";
            case 9:
              return "Oct";
            case 10:
              return "Nov";
            case 11:
              return "Dec";
          }
        };

        $scope.getPaxType = function (flight) {
          if (flight.is_child == "S") {
            return "CHD";
          } else if (flight.is_newborn == "S") {
            return "INF";
          }
          return "ADT";
        };

        $scope.getWeekdayGol = function (date) {
          var day = new Date(date).getDay();
          switch (day) {
            case 0:
              return "Domingo";
            case 1:
              return "Segunda";
            case 2:
              return "Terça";
            case 3:
              return "Quarta";
            case 4:
              return "Quinta";
            case 5:
              return "Sexta";
            case 6:
              return "Sábado";
          }
        };

        $scope.golNovo = function (flight_selected) {

          if ($scope.getIsReturn($scope.flight_selected)) {
            $scope.returnFlight = $scope.getReturnFlight(
              $scope.flight_selected.airline,
              $scope.flight_selected.cards_id,
              $scope.flight_selected.flight,
              $scope.flight_selected.airport_code_from,
              $scope.flight_selected.airport_code_to,
              $scope.flight_selected.flightLocator
            );
            if ($scope.returnFlight) {
              if (
                $rootScope.findDate($scope.returnFlight.boarding_date) <
                $rootScope.findDate($scope.flight_selected.boarding_date)
              ) {
                $scope.flight_selected = angular.copy($scope.returnFlight);
              }
            }
          }

          $.post(
            "../backend/application/index.php?rota=/loadConnectionsFlight",
            { hashId: $scope.session.hashId, data: $scope.flight_selected },
            function (result) {
              $scope.connections = jQuery.parseJSON(result).dataset;
              $.post(
                "../backend/application/index.php?rota=/loadConnectionsFlight",
                {
                  hashId: $scope.session.hashId,
                  data: $scope.getReturnFlight(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.cards_id,
                    $scope.flight_selected.flight,
                    $scope.flight_selected.airport_code_from,
                    $scope.flight_selected.airport_code_to,
                    $scope.flight_selected.flightLocator
                  ),
                },
                function (result) {
                  $scope.connectionsReturn = jQuery.parseJSON(result).dataset;

                  $scope.filterBillet = $filter("filter")(
                    $scope.onlineflights,
                    $scope.flight_selected.flightLocator
                  );

                  var doc = new jsPDF("p", "pt");

                  var start = 0;
                  var pageHeight = doc.internal.pageSize.height;
                  $scope.toDataUrl("images/smileBillet.png", function (
                    base64Img
                  ) {
                    $scope.toDataUrl("images/roda.png", function (base14Img) {
                      $scope.toDataUrl("images/back.png", function (base59Img) {
                        $scope.toDataUrl("images/bar.png", function (
                          base61Img
                        ) {
                          $scope.toDataUrl("images/conection.png", function (
                            base62Img
                          ) {
                            $scope.toDataUrl("images/Golmini.png", function (
                              base63Img
                            ) {
                              $scope.toDataUrl("images/set.png", function (
                                base44Img
                              ) {
                                $scope.toDataUrl("images/ida.png", function (
                                  base34Img
                                ) {
                                  start = 80;
                                  doc.addImage(
                                    base64Img,
                                    "PNG",
                                    120,
                                    start - 20,
                                    88,
                                    47
                                  );
                                  doc.setFontSize(7);
                                  doc.setFontStyle("bold");
                                  doc.setTextColor(255, 128, 0);
                                  var name = $scope.flight_selected.providerName.split(
                                    " "
                                  );
                                  doc.text(
                                    430,
                                    start,
                                    " Olá " + name[0] + " ;)"
                                  );
                                  doc.setTextColor(64);
                                  start = start + 15;
                                  doc.text(
                                    370,
                                    start,
                                    "Seu número Smiles é: " +
                                      $scope.flight_selected.card_number
                                  );
                                  start = start + 10;
                                  doc.text(430, start, "Acesse sua conta.");
                                  start = start + 10;
                                  doc.setLineWidth(0.5);
                                  doc.line(120, start, 490, start);
                                  start = start + 30;
                                  doc.setFontSize(9);
                                  doc.setTextColor(255, 128, 0);
                                  doc.text(
                                    260,
                                    start,
                                    " Olá " + name[0] + " ,"
                                  );
                                  doc.setFontSize(8.5);
                                  doc.setTextColor(64);
                                  start = start + 20;
                                  doc.text(
                                    200,
                                    start,
                                    "A Smiles deseja a você uma excelente viagem."
                                  );
                                  start = start + 10;
                                  doc.text(
                                    160,
                                    start,
                                    "Confira abaixo os dados da sua passagem com o seu código de reserva."
                                  );
                                  start = start + 30;
                                  doc.addImage(
                                    base34Img,
                                    "PNG",
                                    120,
                                    start,
                                    42,
                                    17
                                  );
                                  start = start + 40;

                                  doc.setLineWidth(0.1);
                                  doc.setFontSize(9);
                                  doc.setTextColor(64);
                                  doc.text(
                                    180,
                                    start,
                                    "Código Localizador Smiles"
                                  );
                                  doc.text(
                                    390,
                                    start,
                                    $scope.flight_selected.flightLocator
                                  );

                                  if (
                                    $scope.filterBillet[0].connection.trim() ==
                                    "Direto"
                                  ) {
                                    var x = "DIRETO";
                                  } else {
                                    var x = "PARADA(S)";
                                  }

                                  start = start + 30;
                                  doc.text(250, start, "DE");
                                  doc.text(290, start - 10, x);
                                  doc.addImage(
                                    base44Img,
                                    "PNG",
                                    280,
                                    start - 5,
                                    67,
                                    10
                                  );
                                  doc.text(360, start, "PARA");
                                  var hour = $scope.filterBillet[0].flight_time.split(
                                    ":"
                                  );
                                  doc.setTextColor(0);
                                  doc.text(
                                    300,
                                    start + 10,
                                    hour[0] + "h" + hour[1] + "m"
                                  );
                                  start = start + 10;

                                  doc.setFontSize(12);
                                  doc.text(
                                    244,
                                    start,
                                    $scope.filterBillet[0].airport_code_from
                                  );
                                  doc.text(
                                    360,
                                    start,
                                    $scope.filterBillet[0].airport_code_to
                                  );
                                  start = start + 30;

                                  var columns = [
                                    { title: "", dataKey: "name" },
                                    { title: "", dataKey: "data" },
                                    { title: "", dataKey: "hour" },
                                  ];
                                  var rows = [{ name: "", data: "", hour: "" }];

                                  doc.autoTable(columns, rows, {
                                    columnStyles: {},
                                    styles: {
                                      columnWidth: "auto",
                                      fontSize: 12,
                                      overflow: "linebreak",
                                    },
                                    margin: { top: start, left: 40 },
                                    theme: "plain",
                                    pageBreak: "auto",
                                    tableWidth: "auto",
                                    addPageContent: function (data) {
                                      if ($scope.connections.length > 0) {
                                        var board = new Date(
                                          $scope.flight_selected.boarding_date
                                        );

                                        $scope.connections.newboarding = new Date();
                                        $scope.connections.newlanding = new Date();

                                        $scope.days = new Array();

                                        for (
                                          var ind = 0;
                                          ind < $scope.connections.length;
                                          ind++
                                        ) {
                                          $scope.connections[
                                            ind
                                          ].newboarding = new Date();
                                          $scope.connections[
                                            ind
                                          ].newlanding = new Date();

                                          var land = new Date(
                                            board.getFullYear(),
                                            board.getMonth(),
                                            board.getDate()
                                          );
                                          //var verify = $scope.connections[ind].boarding instanceof Date && !isNaN($scope.connections[ind].boarding.valueOf());
                                          var verify =
                                            $scope.connections[ind].boarding
                                              .length;

                                          if (ind > 0) {
                                            if (verify <= 5) {
                                              if (
                                                parseInt(
                                                  $scope.connections[
                                                    ind
                                                  ].boarding.split(":")[0]
                                                ) <
                                                  parseInt(
                                                    $scope.connections[
                                                      ind - 1
                                                    ].landing.split(":")[0]
                                                  ) ||
                                                $scope.connections[
                                                  ind - 1
                                                ].landing.split(":")[0] <
                                                  $scope.connections[
                                                    ind - 1
                                                  ].boarding.split(":")[0]
                                              ) {
                                                land.setDate(
                                                  land.getDate() + 1
                                                );
                                              } else {
                                                /*if ($scope.connections[ind].boarding.getHours() < $scope.connections[ind - 1].landing.getHours() ||
										  $scope.connections[ind - 1].landing.getHours() < $scope.connections[ind - 1].boarding.getHours()) {
										  land.setDate(land.getDate() + 1);*/
                                              }
                                            }
                                          }

                                          if (verify <= 5) {
                                            board.setHours(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].boarding.split(":")[0]
                                              )
                                            );
                                            board.setMinutes(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].boarding.split(":")[1]
                                              )
                                            );

                                            land.setHours(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].landing.split(":")[0]
                                              )
                                            );
                                            land.setMinutes(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].landing.split(":")[1]
                                              )
                                            );

                                            $scope.connections[
                                              ind
                                            ].newboarding = board;
                                            $scope.connections[
                                              ind
                                            ].newlanding = land;

                                            $scope.connections[
                                              ind
                                            ].newboarding.setDate(
                                              $scope.connections[
                                                ind
                                              ].newlanding.getDate()
                                            );

                                            var c =
                                              $scope.connections[ind]
                                                .newboarding;
                                            var l =
                                              $scope.connections[ind]
                                                .newlanding;

                                            $scope.days.push({
                                              boarding: new Date(c),
                                              landing: new Date(l),
                                            });

                                            $scope.days.forEach(function (
                                              element,
                                              index
                                            ) {
                                              $scope.connections[
                                                index
                                              ].newboarding = new Date(
                                                element.boarding
                                              );
                                              $scope.connections[
                                                index
                                              ].newlanding = new Date(
                                                element.landing
                                              );
                                            });
                                          } else {
                                            var b = $scope.connections[
                                              ind
                                            ].boarding.split("/");
                                            var cr = $scope.connections[
                                              ind
                                            ].landing.split("/");
                                            var y;
                                            var j;
                                            var bd;
                                            var bd2;

                                            var ht = b[2].split(" ");
                                            var ct = cr[2].split(" ");
                                            j = b[2].split(":");
                                            y = cr[2].split(":");

                                            if (ht && j[1]) {
                                              bd = new Date(ht[0], b[1], b[0]);
                                              bd.setHours(j[0]);
                                              bd.setMinutes(j[1]);

                                              bd2 = new Date(
                                                ct[0],
                                                cr[1],
                                                cr[0]
                                              );
                                              bd2.setHours(y[0]);
                                              bd2.setMinutes(y[1]);

                                              $scope.days.push({
                                                boarding: bd,
                                                landing: bd2,
                                              });
                                            } else {
                                              $scope.days.push({
                                                boarding: new Date(
                                                  b[2],
                                                  b[1],
                                                  b[0]
                                                ),
                                                landing: new Date(
                                                  cr[2],
                                                  cr[1],
                                                  cr[0]
                                                ),
                                              });
                                            }

                                            $scope.days.forEach(function (
                                              element,
                                              index
                                            ) {
                                              $scope.connections[
                                                index
                                              ].newboarding = element.boarding;
                                              $scope.connections[
                                                index
                                              ].newlanding = element.landing;
                                            });
                                          }
                                        }

                                        for (
                                          var i = 0;
                                          i < $scope.connections.length;
                                          i++
                                        ) {
                                          doc.addImage(
                                            base63Img,
                                            "PNG",
                                            180,
                                            start,
                                            50,
                                            32
                                          );
                                          doc.setFontSize(8);
                                          doc.setTextColor(0);
                                          doc.text(
                                            300,
                                            start,
                                            "(" +
                                              $scope.connections[i]
                                                .airportCodeFrom +
                                              ")"
                                          );
                                          doc.setTextColor(64);

                                          var dates =
                                            $scope.connections[i].boarding;
                                          doc.text(260, start + 10, dates);
                                          doc.addImage(
                                            base62Img,
                                            "PNG",
                                            335,
                                            start - 5,
                                            17,
                                            22
                                          );
                                          doc.setTextColor(0);
                                          doc.text(
                                            360,
                                            start,
                                            "(" +
                                              $scope.connections[i]
                                                .airportCodeTo +
                                              ")"
                                          );
                                          doc.setTextColor(64);

                                          var datesReturn =
                                            $scope.connections[i].landing;
                                          doc.text(
                                            360,
                                            start + 10,
                                            datesReturn
                                          );

                                          start = start + 40;
                                          doc.setTextColor(96);
                                          doc.text(
                                            185,
                                            start,
                                            $scope.connections[i].flight
                                          );

                                          doc.addImage(
                                            base61Img,
                                            "PNG",
                                            300,
                                            start - 10,
                                            12,
                                            12
                                          );
                                          doc.text(
                                            315,
                                            start - 5,
                                            "Cabine " +
                                              $scope.filterBillet[0].class
                                          );
                                          start = start + 20;
                                        }
                                      } else {
                                        doc.addImage(
                                          base63Img,
                                          "PNG",
                                          180,
                                          start,
                                          55,
                                          32
                                        );
                                        doc.setFontSize(8);
                                        doc.setTextColor(0);
                                        doc.text(
                                          300,
                                          start,
                                          "(" +
                                            $scope.filterBillet[0]
                                              .airport_code_from +
                                            ")"
                                        );
                                        var dateBoard = $scope.flight_selected.boarding_date.split(
                                          "-"
                                        );
                                        var dateBoard1 = dateBoard[2].split(
                                          " "
                                        );
                                        var hour = dateBoard1[1].split(":");
                                        doc.setTextColor(64);
                                        doc.text(
                                          260,
                                          start + 10,
                                          hour[0] +
                                            ":" +
                                            hour[1] +
                                            " " +
                                            dateBoard1[0] +
                                            "/" +
                                            dateBoard[1] +
                                            "/" +
                                            dateBoard[0]
                                        );
                                        doc.addImage(
                                          base62Img,
                                          "PNG",
                                          335,
                                          start - 5,
                                          17,
                                          22
                                        );
                                        doc.setTextColor(0);
                                        doc.text(
                                          360,
                                          start,
                                          "(" +
                                            $scope.filterBillet[0]
                                              .airport_code_to +
                                            ")"
                                        );
                                        var dateLand = $scope.flight_selected.landing_date.split(
                                          "-"
                                        );
                                        var dateLand1 = dateLand[2].split(" ");
                                        var hourL = dateLand1[1].split(":");
                                        doc.setTextColor(64);
                                        doc.text(
                                          360,
                                          start + 10,
                                          hourL[0] +
                                            ":" +
                                            hourL[1] +
                                            " " +
                                            dateLand1[0] +
                                            "/" +
                                            dateLand[1] +
                                            "/" +
                                            dateLand[0]
                                        );

                                        start = start + 40;
                                        doc.setTextColor(96);
                                        doc.text(
                                          185,
                                          start,
                                          $scope.filterBillet[0].flight
                                        );

                                        doc.addImage(
                                          base61Img,
                                          "PNG",
                                          300,
                                          start - 10,
                                          12,
                                          12
                                        );
                                        doc.text(
                                          315,
                                          start - 5,
                                          "Cabine " +
                                            $scope.filterBillet[0].class
                                        );
                                        start = start + 20;
                                      }

                                      start = start + 20;

                                      doc.setTextColor(32);
                                      doc.setFontSize(9);

                                      var columns = [
                                        {
                                          title: "Passageiro",
                                          dataKey: "name",
                                        },
                                        {
                                          title: "Bagagem",
                                          dataKey: "baggage",
                                        },
                                        { title: "Assento", dataKey: "seat" },
                                      ];
                                      var rows = [];

                                      start = start + 20;
                                      var conectSeat = "";
                                      var paxs = $scope.getPaxLaTam(
                                        $scope.flight_selected.airline,
                                        $scope.flight_selected.cards_id,
                                        $scope.flight_selected.flight,
                                        $scope.flight_selected.flightLocator
                                      );

                                      var group = $filter("groupBy")(
                                        paxs,
                                        "pax"
                                      );

                                      const res = Object.keys(group).map(
                                        function (key) {
                                          return group[key];
                                        }
                                      );

                                      var inteRes = new Array();

                                      for (var j in res) {
                                        var pax = "";
                                        var seat = new Array();
                                        var baggage = "";

                                        pax = res[j][0].pax;
                                        baggage = res[j][0].baggage;

                                        for (var f in res[j]) {
                                          pax = res[j][f].pax;
                                          baggage = res[j][f].baggage;
                                          seat.push(res[j][f].sit);
                                        }

                                        inteRes.push({
                                          pax: pax,
                                          seat: seat,
                                          baggage: baggage,
                                        });
                                      }
                                      for (var i = 0; i < inteRes.length; i++) {
                                        var str = "";
                                        var seat = "";
                                        var element = inteRes[i];

                                        if (
                                          $scope.filterBillet[0].connection.trim() !=
                                          "Direto"
                                        ) {
                                          for (var g in element.seat) {
                                            conectSeat +=
                                              element.seat[g] + " / ";
                                            str = conectSeat;
                                          }
                                        } else {
                                          conectSeat = element.seat[0];
                                          str = conectSeat;
                                        }

                                        if (
                                          !element.baggage ||
                                          element.baggage == "0"
                                        ) {
                                          element.baggage = "Comprar Bagagem";
                                        }

                                        var seat = element.seat;
                                        if (!seat || seat == "---") {
                                          seat = "Escolher Assento";
                                        }

                                        rows.push({
                                          name: element.pax,
                                          baggage: element.baggage,
                                          seat: str,
                                        });
                                      }

                                      doc.autoTable(columns, rows, {
                                        columnStyles: {
                                          name: {
                                            columnWidth: 200,
                                          },
                                          baggage: {
                                            columnWidth: 100,
                                            textColor: [255, 128, 0],
                                          },
                                          seat: {
                                            columnWidth: 100,
                                            textColor: [255, 128, 0],
                                          },
                                        },
                                        showHeader: "firstPage",
                                        startY: start,
                                        headerStyles: {
                                          fillColor: [255, 255, 255],
                                          textColor: [0],
                                          fontSize: 9,
                                          lineWidth: 1,
                                          lineColor: [255, 255, 255],
                                        },
                                        styles: {
                                          fontSize: 8,
                                          textColor: 96,
                                        },
                                        margin: { left: 120 },
                                        theme: "plain",
                                        pageBreak: "avoid",
                                      });
                                    },
                                    drawCell: function (cell, data) {},
                                  });
                                  if (start >= 470) {
                                    start = 30;
                                    doc.autoTableAddPage();
                                  } else {
                                    start = start + 200;
                                  }

                                  if (
                                    $scope.getIsReturn($scope.flight_selected)
                                  ) {
                                    $scope.returnFlight = $scope.getReturnFlight(
                                      $scope.flight_selected.airline,
                                      $scope.flight_selected.cards_id,
                                      $scope.flight_selected.flight,
                                      $scope.flight_selected.airport_code_from,
                                      $scope.flight_selected.airport_code_to,
                                      $scope.flight_selected.flightLocator
                                    );
                                    $scope.RetConn =
                                      $scope.returnFlight.connection;

                                    doc.addImage(
                                      base59Img,
                                      "PNG",
                                      120,
                                      start,
                                      42,
                                      17
                                    );
                                    start = start + 40;

                                    var columns = [
                                      { title: "", dataKey: "name" },
                                      { title: "", dataKey: "data" },
                                      { title: "", dataKey: "hour" },
                                    ];
                                    var rows = [
                                      { name: "", data: "", hour: "" },
                                    ];

                                    doc.autoTable(columns, rows, {
                                      columnStyles: {},
                                      styles: {
                                        columnWidth: "auto",
                                        fontSize: 12,
                                        overflow: "linebreak",
                                        overflowColumns: false,
                                      },
                                      startY: start,
                                      margin: { left: 40 },
                                      theme: "plain",
                                      pageBreak: "auto",
                                      showHeader: "firstPage",
                                      addPageContent: function (datas) {
                                        doc.setLineWidth(0.1);
                                        doc.setFontSize(9);
                                        doc.setTextColor(64);
                                        doc.text(
                                          180,
                                          start,
                                          "Código Localizador Smiles"
                                        );
                                        doc.text(
                                          390,
                                          start,
                                          $scope.returnFlight.flightLocator
                                        );

                                        if (
                                          $scope.returnFlight.connection.trim() ==
                                          "Direto"
                                        ) {
                                          var y = "DIRETO";
                                        } else {
                                          var y = "PARADA(S)";
                                        }

                                        start = start + 30;
                                        doc.text(250, start, "DE");
                                        doc.text(290, start - 10, y);
                                        doc.addImage(
                                          base44Img,
                                          "PNG",
                                          280,
                                          start - 5,
                                          67,
                                          10
                                        );
                                        doc.text(360, start, "PARA");
                                        var hour = $scope.returnFlight.flight_time.split(
                                          ":"
                                        );
                                        doc.setTextColor(0);
                                        doc.text(
                                          300,
                                          start + 10,
                                          hour[0] + "h" + hour[1] + "m"
                                        );
                                        start = start + 10;
                                        doc.setTextColor(0);
                                        doc.setFontSize(12);
                                        doc.text(
                                          244,
                                          start,
                                          $scope.returnFlight.airport_code_from
                                        );
                                        doc.text(
                                          360,
                                          start,
                                          $scope.returnFlight.airport_code_to
                                        );
                                        start = start + 30;

                                        if (
                                          $scope.connectionsReturn.length > 0
                                        ) {
                                          var board = new Date(
                                            $scope.returnFlight.boarding_date
                                          );

                                          $scope.connectionsReturn.newboarding = new Date();
                                          $scope.connectionsReturn.newlanding = new Date();

                                          $scope.daysReturn = new Array();

                                          for (
                                            var index = 0;
                                            index <
                                            $scope.connectionsReturn.length;
                                            index++
                                          ) {
                                            $scope.connectionsReturn[
                                              index
                                            ].newboarding = new Date();
                                            $scope.connectionsReturn[
                                              index
                                            ].newlanding = new Date();

                                            var land = new Date(
                                              board.getFullYear(),
                                              board.getMonth(),
                                              board.getDate()
                                            );
                                            //var verifyReturn = $scope.connectionsReturn[index].boarding instanceof Date && !isNaN($scope.connectionsReturn[index].boarding.valueOf());
                                            var verifyReturn =
                                              $scope.connectionsReturn[index]
                                                .boarding.length;

                                            if (index > 0) {
                                              if (verifyReturn == 5) {
                                                if (
                                                  parseInt(
                                                    $scope.connectionsReturn[
                                                      index
                                                    ].boarding.split(":")[0]
                                                  ) <
                                                    parseInt(
                                                      $scope.connectionsReturn[
                                                        index - 1
                                                      ].landing.split(":")[0]
                                                    ) ||
                                                  parseInt(
                                                    $scope.connectionsReturn[
                                                      index - 1
                                                    ].boarding.split(":")[0]
                                                  ) >
                                                    parseInt(
                                                      $scope.connectionsReturn[
                                                        index - 1
                                                      ].landing.split(":")[0]
                                                    )
                                                ) {
                                                  land.setDate(
                                                    land.getDate() + 1
                                                  );
                                                }
                                              }
                                            }

                                            if (verifyReturn == 5) {
                                              board.setHours(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].boarding.split(":")[0]
                                                )
                                              );
                                              board.setMinutes(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].boarding.split(":")[1]
                                                )
                                              );

                                              land.setHours(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].landing.split(":")[0]
                                                )
                                              );
                                              land.setMinutes(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].landing.split(":")[1]
                                                )
                                              );

                                              $scope.connectionsReturn[
                                                index
                                              ].newboarding = board;
                                              $scope.connectionsReturn[
                                                index
                                              ].newlanding = land;

                                              $scope.connectionsReturn[
                                                index
                                              ].newboarding.setDate(
                                                $scope.connectionsReturn[
                                                  index
                                                ].newlanding.getDate()
                                              );

                                              var cv =
                                                $scope.connectionsReturn[index]
                                                  .newboarding;
                                              var lv =
                                                $scope.connectionsReturn[index]
                                                  .newlanding;

                                              $scope.daysReturn.push({
                                                boarding: new Date(cv),
                                                landing: new Date(lv),
                                              });
                                            } else {
                                              var br = $scope.connectionsReturn[
                                                index
                                              ].boarding.split("/");
                                              var crr = $scope.connectionsReturn[
                                                index
                                              ].landing.split("/");
                                              var yr;
                                              var jr;
                                              var bdr;
                                              var bd2r;

                                              var htr = br[2].split(" ");
                                              var ctr = crr[2].split(" ");
                                              jr = br[2].split(":");
                                              yr = crr[2].split(":");

                                              if (htr && jr[1]) {
                                                bdr = new Date(
                                                  htr[0],
                                                  br[1],
                                                  br[0]
                                                );
                                                bdr.setHours(jr[0]);
                                                bdr.setMinutes(jr[1]);

                                                bd2r = new Date(
                                                  ctr[0],
                                                  crr[1],
                                                  crr[0]
                                                );
                                                bd2r.setHours(yr[0]);
                                                bd2r.setMinutes(yr[1]);

                                                $scope.daysReturn.push({
                                                  boarding: bdr,
                                                  landing: bd2r,
                                                });
                                              } else {
                                                $scope.daysReturn.push({
                                                  boarding: new Date(
                                                    br[2],
                                                    br[1],
                                                    br[0]
                                                  ),
                                                  landing: new Date(
                                                    crr[2],
                                                    crr[1],
                                                    crr[0]
                                                  ),
                                                });
                                              }
                                            }
                                          }

                                          $scope.daysReturn.forEach(function (
                                            el,
                                            index
                                          ) {
                                            ($scope.connectionsReturn[
                                              index
                                            ].newboarding = new Date(
                                              el.boarding
                                            )),
                                              ($scope.connectionsReturn[
                                                index
                                              ].newlanding = new Date(
                                                el.landing
                                              ));
                                          });

                                          for (
                                            var i = 0;
                                            i < $scope.connectionsReturn.length;
                                            i++
                                          ) {
                                            doc.addImage(
                                              base63Img,
                                              "PNG",
                                              180,
                                              start,
                                              50,
                                              32
                                            );
                                            doc.setFontSize(8);
                                            doc.setTextColor(0);
                                            doc.text(
                                              300,
                                              start,
                                              "(" +
                                                $scope.connectionsReturn[i]
                                                  .airportCodeFrom +
                                                ")"
                                            );
                                            doc.setTextColor(64);
                                            var datesR =
                                              $scope.connectionsReturn[i]
                                                .boarding;
                                            doc.text(260, start + 10, datesR);
                                            doc.addImage(
                                              base62Img,
                                              "PNG",
                                              335,
                                              start - 5,
                                              17,
                                              22
                                            );
                                            doc.setTextColor(0);
                                            doc.text(
                                              360,
                                              start,
                                              "(" +
                                                $scope.connectionsReturn[i]
                                                  .airportCodeTo +
                                                ")"
                                            );
                                            doc.setTextColor(64);

                                            var datesR1 =
                                              $scope.connectionsReturn[i]
                                                .landing;
                                            doc.text(360, start + 10, datesR1);
                                            start = start + 40;
                                            doc.setTextColor(96);
                                            doc.text(
                                              185,
                                              start,
                                              $scope.connectionsReturn[i].flight
                                            );

                                            doc.addImage(
                                              base61Img,
                                              "PNG",
                                              300,
                                              start - 10,
                                              12,
                                              12
                                            );
                                            doc.text(
                                              315,
                                              start - 5,
                                              "Cabine " +
                                                $scope.returnFlight.class
                                            );
                                            start = start + 20;
                                          }
                                        } else {
                                          doc.addImage(
                                            base63Img,
                                            "PNG",
                                            180,
                                            start,
                                            55,
                                            32
                                          );
                                          doc.setFontSize(8);
                                          doc.setTextColor(0);
                                          doc.text(
                                            300,
                                            start,
                                            "(" +
                                              $scope.returnFlight
                                                .airport_code_from +
                                              ")"
                                          );
                                          var dateBoard = $scope.returnFlight.boarding_date.split(
                                            "-"
                                          );
                                          var dateBoard1 = dateBoard[2].split(
                                            " "
                                          );
                                          var hour = dateBoard1[1].split(":");
                                          doc.setTextColor(64);
                                          doc.text(
                                            260,
                                            start + 10,
                                            hour[0] +
                                              ":" +
                                              hour[1] +
                                              " " +
                                              dateBoard1[0] +
                                              "/" +
                                              dateBoard[1] +
                                              "/" +
                                              dateBoard[0]
                                          );
                                          doc.addImage(
                                            base62Img,
                                            "PNG",
                                            335,
                                            start - 5,
                                            17,
                                            22
                                          );
                                          doc.setTextColor(0);
                                          doc.text(
                                            360,
                                            start,
                                            "(" +
                                              $scope.returnFlight
                                                .airport_code_to +
                                              ")"
                                          );
                                          var dateLand = $scope.returnFlight.landing_date.split(
                                            "-"
                                          );
                                          var dateLand1 = dateLand[2].split(
                                            " "
                                          );
                                          var hourL = dateLand1[1].split(":");
                                          doc.setTextColor(64);
                                          doc.text(
                                            360,
                                            start + 10,
                                            hourL[0] +
                                              ":" +
                                              hourL[1] +
                                              " " +
                                              dateLand1[0] +
                                              "/" +
                                              dateLand[1] +
                                              "/" +
                                              dateLand[0]
                                          );
                                          start = start + 40;
                                          doc.setTextColor(96);
                                          doc.text(
                                            185,
                                            start,
                                            $scope.returnFlight.flight
                                          );

                                          doc.addImage(
                                            base61Img,
                                            "PNG",
                                            300,
                                            start - 10,
                                            12,
                                            12
                                          );
                                          doc.text(
                                            315,
                                            start - 5,
                                            "Cabine " +
                                              $scope.returnFlight.class
                                          );
                                          start = start + 20;
                                        }

                                        start = start + 20;

                                        doc.setTextColor(0);
                                        doc.setFontSize(9);

                                        var columns = [
                                          {
                                            title: "Passageiro",
                                            dataKey: "name",
                                          },
                                          {
                                            title: "Bagagem",
                                            dataKey: "baggage",
                                          },
                                          { title: "Assento", dataKey: "seat" },
                                        ];

                                        var rows = [];

                                        start = start + 30;
                                        var conectSeatRet = "";

                                        $scope.paxReturn = $scope.getPaxLaTamReturn(
                                          $scope.returnFlight.airline,
                                          $scope.returnFlight.cards_id,
                                          $scope.returnFlight.flight,
                                          $scope.returnFlight.flightLocator
                                        );

                                        var letsR = $filter("groupBy")(
                                          $scope.paxReturn,
                                          "pax"
                                        );

                                        const resultsRet = Object.keys(
                                          letsR
                                        ).map(function (key) {
                                          return letsR[key];
                                        });

                                        var inteR = new Array();

                                        for (var jn in resultsRet) {
                                          var seatReturn = new Array();
                                          var pax = "";
                                          var baggage = "";
                                          for (var fn in resultsRet[jn]) {
                                            pax = resultsRet[jn][fn].pax;
                                            baggage =
                                              resultsRet[jn][fn].baggage;

                                            seatReturn.push(
                                              resultsRet[jn][fn].sit
                                            );
                                          }

                                          inteR.push({
                                            pax: pax,
                                            seat: seatReturn,
                                            baggage: baggage,
                                          });
                                        }

                                        for (var i = 0; i < inteR.length; i++) {
                                          var strRet = "";
                                          var seatRet = "";
                                          var elementRet = inteR[i];

                                          if (
                                            $scope.RetConn.trim() != "Direto"
                                          ) {
                                            for (var h in elementRet.seat) {
                                              conectSeatRet +=
                                                elementRet.seat[h] + " / ";
                                              strRet = conectSeatRet;
                                            }

                                            //strRet = conectSeatRet.concat("/" + conectSeatRet);
                                          } else {
                                            conectSeatRet = elementRet.seat[0];
                                            strRet = conectSeatRet;
                                          }
                                          elementRet.baggage.toString();

                                          if (
                                            !elementRet.baggage ||
                                            elementRet.baggage == "0"
                                          ) {
                                            elementRet.baggage =
                                              "Comprar Bagagem";
                                          }
                                          if (!strRet || strRet == "---") {
                                            strRet = "Escolher Assento";
                                          }

                                          rows.push({
                                            name: elementRet.pax,
                                            baggage: elementRet.baggage,
                                            seat: strRet,
                                          });
                                        }

                                        doc.autoTable(columns, rows, {
                                          startY: start,
                                          margin: { left: 120 },
                                          theme: "plain",
                                          pageBreak: "avoid",
                                          columnStyles: {
                                            name: {
                                              columnWidth: 200,
                                            },
                                            baggage: {
                                              columnWidth: 100,
                                              textColor: [255, 128, 0],
                                            },
                                            seat: {
                                              columnWidth: 100,
                                              textColor: [255, 128, 0],
                                            },
                                          },
                                          showHeader: "firstPage",
                                          drawRow: function (row, data) {
                                            /*doc.setDrawColor(192, 192, 192); // draw red lines
									  doc.setLineWidth(0.9);
									  doc.line(100, start + 30, 500, start + 30);*/
                                          },
                                          headerStyles: {
                                            fillColor: [255, 255, 255],
                                            textColor: 0,
                                            fontSize: 9,
                                            lineWidth: 1,
                                            lineColor: [255, 255, 255],
                                          },
                                          styles: {
                                            fontSize: 8,
                                            textColor: 96,
                                            overflowColumns: false,
                                          },
                                        });

                                        for (var r in rows) {
                                          start = start + 15;
                                        }
                                      },
                                    });
                                    start = start + 50;
                                    /*if (start >= 600) {
								start = 30;
								doc.autoTableAddPage();
							  } else {
								start = start + 200;
							  }*/
                                    if (start >= 600) {
                                      start = (start * 20) / 100;
                                    }
                                  }
                                  start = start + 20;

                                  var card_number = $scope.filterBillet[0]
                                    .tax_card
                                    ? $scope.filterBillet[0].tax_card.replace(
                                        /.(?=.{4})/g,
                                        "*"
                                      )
                                    : "";
                                  var n = $rootScope.formatNumber(
                                    $scope.getTotalTaxFlight(
                                      $scope.filterBillet[0].flightLocator
                                    )
                                  );
                                  n = n.toString();

                                  var columns = [
                                    {
                                      title: " Forma de Pagamento (R$) ",
                                      dataKey: "name",
                                    },
                                  ];
                                  var rows = [
                                    { name: " Cartão de Crédito " },
                                    {
                                      name:
                                        " " +
                                        ($scope.filterBillet[0].tax_providerName
                                          ? $scope.filterBillet[0]
                                              .tax_providerName
                                          : ""),
                                    },
                                    { name: " R$ " + n + "  em 1x" },
                                  ];

                                  var images = [];

                                  doc.autoTable(columns, rows, {
                                    columnStyles: {},
                                    startY: start,
                                    styles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    headerStyles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    margin: { left: 115 },
                                    theme: "plain",
                                    pageBreak: "avoid",
                                    showHeader: "firstPage",
                                    addPageContent: function (data) {
                                      doc.setFontSize(11);
                                      doc.setTextColor(0);
                                    },
                                  });

                                  start = start + 90;

                                  var columns = [
                                    {
                                      title: " Dados do Comprador",
                                      dataKey: "name",
                                    },
                                  ];

                                  var rows = [
                                    {
                                      name:
                                        "Nome: " +
                                        $scope.flight_selected.providerName,
                                    },
                                    {
                                      name:
                                        "Telefone: " +
                                        $scope.flight_selected.provider_phone,
                                    },
                                  ];

                                  var images = [];

                                  doc.autoTable(columns, rows, {
                                    columnStyles: {},
                                    startY: start,
                                    styles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    headerStyles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    margin: { left: 115 },
                                    theme: "plain",
                                    overflow: "linebreak",
                                    tableWidth: "auto",
                                    pageBreak: "auto",
                                    showHeader: "firstPage",
                                    addPageContent: function (data) {
                                      start = start + 70;
                                    },
                                  });

                                  start = doc.autoTableEndPosY() + 10;

                                  var columns = [
                                    { title: " ", dataKey: "names" },
                                  ];

                                  var rows = [{ names: "" }];

                                  if (start >= 600) {
                                    start = 20;
                                    doc.autoTableAddPage();
                                  }
                                  doc.autoTable(columns, rows, {
                                    startY: start,
                                    margin: { left: 120 },
                                    theme: "plain",
                                    pageBreak: "avoid",
                                    columnStyles: {
                                      names: { columnWidth: 200 },
                                      baggage: { columnWidth: 100 },
                                      seat: { columnWidth: 70 },
                                    },
                                    //showHeader: 'firstPage',
                                    drawCell: function (cell, opts) {
                                      if (opts.column.dataKey == "names") {
                                        images.push({
                                          x: cell.textPos.x,
                                          y: cell.textPos.y,
                                        });
                                      }
                                    },
                                    addPageContent: function () {
                                      var y = images[0].y;
                                      doc.addImage(
                                        base14Img,
                                        "PNG",
                                        images[0].x - 40,
                                        start,
                                        450,
                                        390
                                      );
                                    },
                                  });

                                  doc.save(
                                    $scope.flight_selected.flightLocator +
                                      ".pdf"
                                  );
                                  if ($scope.multiDownloads !== true) {
                                    if ($scope.response) {
                                      if (
                                        $scope.response.notificationurl !=
                                          null &&
                                        $scope.response.notificationurl.indexOf(
                                          "http"
                                        ) == -1
                                      ) {
                                        $scope.fillEmailBillet();
                                      }
                                    } else {
                                      $scope.fillEmailBillet();
                                    }
                                  }
                                });
                              });
                            });
                          });
                        });
                      });
                    });
                  });
                }
              );
            }
          );
        };


        $scope.golvelho = function (flight_selected) {
          if ($scope.getIsReturn($scope.flight_selected)) {
            $scope.returnFlight = $scope.getReturnFlight(
              $scope.flight_selected.airline,
              $scope.flight_selected.cards_id,
              $scope.flight_selected.flight,
              $scope.flight_selected.airport_code_from,
              $scope.flight_selected.airport_code_to,
              $scope.flight_selected.flightLocator
            );
            if ($scope.returnFlight) {
              if (
                $rootScope.findDate($scope.returnFlight.boarding_date) <
                $rootScope.findDate($scope.flight_selected.boarding_date)
              ) {
                $scope.flight_selected = angular.copy($scope.returnFlight);
              }
            }
          }

          $.post(
            "../backend/application/index.php?rota=/loadConnectionsFlight",
            { hashId: $scope.session.hashId, data: $scope.flight_selected },
            function (result) {
              $scope.connections = jQuery.parseJSON(result).dataset;
              $.post(
                "../backend/application/index.php?rota=/loadConnectionsFlight",
                {
                  hashId: $scope.session.hashId,
                  data: $scope.getReturnFlight(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.cards_id,
                    $scope.flight_selected.flight,
                    $scope.flight_selected.airport_code_from,
                    $scope.flight_selected.airport_code_to,
                    $scope.flight_selected.flightLocator
                  ),
                },
                function (result) {
                  $scope.connectionsReturn = jQuery.parseJSON(result).dataset;

                  $scope.filterBillet = $filter("filter")(
                    $scope.onlineflights,
                    $scope.flight_selected.flightLocator
                  );

                  var doc = new jsPDF("p", "pt");

                  var start = 0;
                  var pageHeight = doc.internal.pageSize.height;
                  $scope.toDataUrl("images/smileBillet.png", function (
                    base64Img
                  ) {
                    $scope.toDataUrl("images/roda.png", function (base14Img) {
                      $scope.toDataUrl("images/back.png", function (base59Img) {
                        $scope.toDataUrl("images/bar.png", function (
                          base61Img
                        ) {
                          $scope.toDataUrl("images/conection.png", function (
                            base62Img
                          ) {
                            $scope.toDataUrl("images/Golmini.png", function (
                              base63Img
                            ) {
                              $scope.toDataUrl("images/set.png", function (
                                base44Img
                              ) {
                                $scope.toDataUrl("images/ida.png", function (
                                  base34Img
                                ) {
                                  start = 80;
                                  doc.addImage(
                                    base64Img,
                                    "PNG",
                                    120,
                                    start - 20,
                                    88,
                                    47
                                  );
                                  doc.setFontSize(7);
                                  doc.setFontStyle("bold");
                                  doc.setTextColor(255, 128, 0);
                                  var name = $scope.flight_selected.providerName.split(
                                    " "
                                  );
                                  doc.text(
                                    430,
                                    start,
                                    " Olá " + name[0] + " ;)"
                                  );
                                  doc.setTextColor(64);
                                  start = start + 15;
                                  doc.text(
                                    370,
                                    start,
                                    "Seu número Smiles é: " +
                                      $scope.flight_selected.card_number
                                  );
                                  start = start + 10;
                                  doc.text(430, start, "Acesse sua conta.");
                                  start = start + 10;
                                  doc.setLineWidth(0.5);
                                  doc.line(120, start, 490, start);
                                  start = start + 30;
                                  doc.setFontSize(9);
                                  doc.setTextColor(255, 128, 0);
                                  doc.text(
                                    260,
                                    start,
                                    " Olá " + name[0] + " ,"
                                  );
                                  doc.setFontSize(8.5);
                                  doc.setTextColor(64);
                                  start = start + 20;
                                  doc.text(
                                    200,
                                    start,
                                    "A Smiles deseja a você uma excelente viagem."
                                  );
                                  start = start + 10;
                                  doc.text(
                                    160,
                                    start,
                                    "Confira abaixo os dados da sua passagem com o seu código de reserva."
                                  );
                                  start = start + 30;
                                  doc.addImage(
                                    base34Img,
                                    "PNG",
                                    120,
                                    start,
                                    42,
                                    17
                                  );
                                  start = start + 40;

                                  doc.setLineWidth(0.1);
                                  doc.setFontSize(9);
                                  doc.setTextColor(64);
                                  doc.text(
                                    180,
                                    start,
                                    "Código Localizador Smiles"
                                  );
                                  doc.text(
                                    390,
                                    start,
                                    $scope.flight_selected.flightLocator
                                  );

                                  if (
                                    $scope.filterBillet[0].connection.trim() ==
                                    "Direto"
                                  ) {
                                    var x = "DIRETO";
                                  } else {
                                    var x = "PARADA(S)";
                                  }

                                  start = start + 30;
                                  doc.text(250, start, "DE");
                                  doc.text(290, start - 10, x);
                                  doc.addImage(
                                    base44Img,
                                    "PNG",
                                    280,
                                    start - 5,
                                    67,
                                    10
                                  );
                                  doc.text(360, start, "PARA");
                                  var hour = $scope.filterBillet[0].flight_time.split(
                                    ":"
                                  );
                                  doc.setTextColor(0);
                                  doc.text(
                                    300,
                                    start + 10,
                                    hour[0] + "h" + hour[1] + "m"
                                  );
                                  start = start + 10;

                                  doc.setFontSize(12);
                                  doc.text(
                                    244,
                                    start,
                                    $scope.filterBillet[0].airport_code_from
                                  );
                                  doc.text(
                                    360,
                                    start,
                                    $scope.filterBillet[0].airport_code_to
                                  );
                                  start = start + 30;

                                  var columns = [
                                    { title: "", dataKey: "name" },
                                    { title: "", dataKey: "data" },
                                    { title: "", dataKey: "hour" },
                                  ];
                                  var rows = [{ name: "", data: "", hour: "" }];

                                  doc.autoTable(columns, rows, {
                                    columnStyles: {},
                                    styles: {
                                      columnWidth: "auto",
                                      fontSize: 12,
                                      overflow: "linebreak",
                                    },
                                    margin: { top: start, left: 40 },
                                    theme: "plain",
                                    pageBreak: "auto",
                                    tableWidth: "auto",
                                    addPageContent: function (data) {
                                      if ($scope.connections.length > 0) {
                                        var board = new Date(
                                          $scope.flight_selected.boarding_date
                                        );

                                        $scope.connections.newboarding = new Date();
                                        $scope.connections.newlanding = new Date();

                                        $scope.days = new Array();

                                        for (
                                          var ind = 0;
                                          ind < $scope.connections.length;
                                          ind++
                                        ) {
                                          $scope.connections[
                                            ind
                                          ].newboarding = new Date();
                                          $scope.connections[
                                            ind
                                          ].newlanding = new Date();

                                          var land = new Date(
                                            board.getFullYear(),
                                            board.getMonth(),
                                            board.getDate()
                                          );
                                          //var verify = $scope.connections[ind].boarding instanceof Date && !isNaN($scope.connections[ind].boarding.valueOf());
                                          var verify =
                                            $scope.connections[ind].boarding
                                              .length;

                                          if (ind > 0) {
                                            if (verify <= 5) {
                                              if (
                                                parseInt(
                                                  $scope.connections[
                                                    ind
                                                  ].boarding.split(":")[0]
                                                ) <
                                                  parseInt(
                                                    $scope.connections[
                                                      ind - 1
                                                    ].landing.split(":")[0]
                                                  ) ||
                                                $scope.connections[
                                                  ind - 1
                                                ].landing.split(":")[0] <
                                                  $scope.connections[
                                                    ind - 1
                                                  ].boarding.split(":")[0]
                                              ) {
                                                land.setDate(
                                                  land.getDate() + 1
                                                );
                                              } else {
                                                /*if ($scope.connections[ind].boarding.getHours() < $scope.connections[ind - 1].landing.getHours() ||
										  $scope.connections[ind - 1].landing.getHours() < $scope.connections[ind - 1].boarding.getHours()) {
										  land.setDate(land.getDate() + 1);*/
                                              }
                                            }
                                          }

                                          if (verify <= 5) {
                                            board.setHours(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].boarding.split(":")[0]
                                              )
                                            );
                                            board.setMinutes(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].boarding.split(":")[1]
                                              )
                                            );

                                            land.setHours(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].landing.split(":")[0]
                                              )
                                            );
                                            land.setMinutes(
                                              parseInt(
                                                $scope.connections[
                                                  ind
                                                ].landing.split(":")[1]
                                              )
                                            );

                                            $scope.connections[
                                              ind
                                            ].newboarding = board;
                                            $scope.connections[
                                              ind
                                            ].newlanding = land;

                                            $scope.connections[
                                              ind
                                            ].newboarding.setDate(
                                              $scope.connections[
                                                ind
                                              ].newlanding.getDate()
                                            );

                                            var c =
                                              $scope.connections[ind]
                                                .newboarding;
                                            var l =
                                              $scope.connections[ind]
                                                .newlanding;

                                            $scope.days.push({
                                              boarding: new Date(c),
                                              landing: new Date(l),
                                            });

                                            $scope.days.forEach(function (
                                              element,
                                              index
                                            ) {
                                              $scope.connections[
                                                index
                                              ].newboarding = new Date(
                                                element.boarding
                                              );
                                              $scope.connections[
                                                index
                                              ].newlanding = new Date(
                                                element.landing
                                              );
                                            });
                                          } else {
                                            var b = $scope.connections[
                                              ind
                                            ].boarding.split("/");
                                            var cr = $scope.connections[
                                              ind
                                            ].landing.split("/");
                                            var y;
                                            var j;
                                            var bd;
                                            var bd2;

                                            var ht = b[2].split(" ");
                                            var ct = cr[2].split(" ");
                                            j = b[2].split(":");
                                            y = cr[2].split(":");

                                            if (ht && j[1]) {
                                              bd = new Date(ht[0], b[1], b[0]);
                                              bd.setHours(j[0]);
                                              bd.setMinutes(j[1]);

                                              bd2 = new Date(
                                                ct[0],
                                                cr[1],
                                                cr[0]
                                              );
                                              bd2.setHours(y[0]);
                                              bd2.setMinutes(y[1]);

                                              $scope.days.push({
                                                boarding: bd,
                                                landing: bd2,
                                              });
                                            } else {
                                              $scope.days.push({
                                                boarding: new Date(
                                                  b[2],
                                                  b[1],
                                                  b[0]
                                                ),
                                                landing: new Date(
                                                  cr[2],
                                                  cr[1],
                                                  cr[0]
                                                ),
                                              });
                                            }

                                            $scope.days.forEach(function (
                                              element,
                                              index
                                            ) {
                                              $scope.connections[
                                                index
                                              ].newboarding = element.boarding;
                                              $scope.connections[
                                                index
                                              ].newlanding = element.landing;
                                            });
                                          }
                                        }

                                        for (
                                          var i = 0;
                                          i < $scope.connections.length;
                                          i++
                                        ) {
                                          doc.addImage(
                                            base63Img,
                                            "PNG",
                                            180,
                                            start,
                                            50,
                                            32
                                          );
                                          doc.setFontSize(8);
                                          doc.setTextColor(0);
                                          doc.text(
                                            300,
                                            start,
                                            "(" +
                                              $scope.connections[i]
                                                .airportCodeFrom +
                                              ")"
                                          );
                                          doc.setTextColor(64);

                                          var dates =
                                            $rootScope.pad(
                                              $scope.connections[
                                                i
                                              ].newboarding.getHours()
                                            ) +
                                            ":" +
                                            $rootScope.pad(
                                              $scope.connections[
                                                i
                                              ].newboarding.getMinutes()
                                            ) +
                                            " " +
                                            $rootScope
                                              .pad(
                                                $scope.connections[
                                                  i
                                                ].newboarding.getDate()
                                              )
                                              .toString() +
                                            "/" +
                                            $rootScope
                                              .pad(
                                                $scope.connections[
                                                  i
                                                ].newboarding.getMonth() + 1
                                              )
                                              .toString() +
                                            "/" +
                                            $scope.connections[i].newboarding
                                              .getFullYear()
                                              .toString();
                                          doc.text(260, start + 10, dates);
                                          doc.addImage(
                                            base62Img,
                                            "PNG",
                                            335,
                                            start - 5,
                                            17,
                                            22
                                          );
                                          doc.setTextColor(0);
                                          doc.text(
                                            360,
                                            start,
                                            "(" +
                                              $scope.connections[i]
                                                .airportCodeTo +
                                              ")"
                                          );
                                          doc.setTextColor(64);

                                          var datesReturn =
                                            $rootScope.pad(
                                              $scope.connections[
                                                i
                                              ].newlanding.getHours()
                                            ) +
                                            ":" +
                                            $rootScope.pad(
                                              $scope.connections[
                                                i
                                              ].newlanding.getMinutes()
                                            ) +
                                            " " +
                                            $rootScope
                                              .pad(
                                                $scope.connections[
                                                  i
                                                ].newlanding.getDate()
                                              )
                                              .toString() +
                                            "/" +
                                            $rootScope
                                              .pad(
                                                $scope.connections[
                                                  i
                                                ].newlanding.getMonth() + 1
                                              )
                                              .toString() +
                                            "/" +
                                            $scope.connections[i].newlanding
                                              .getFullYear()
                                              .toString();
                                          doc.text(
                                            360,
                                            start + 10,
                                            datesReturn
                                          );

                                          start = start + 40;
                                          doc.setTextColor(96);
                                          doc.text(
                                            185,
                                            start,
                                            $scope.connections[i].flight
                                          );

                                          doc.addImage(
                                            base61Img,
                                            "PNG",
                                            300,
                                            start - 10,
                                            12,
                                            12
                                          );
                                          doc.text(
                                            315,
                                            start - 5,
                                            "Cabine " +
                                              $scope.filterBillet[0].class
                                          );
                                          start = start + 20;
                                        }
                                      } else {
                                        doc.addImage(
                                          base63Img,
                                          "PNG",
                                          180,
                                          start,
                                          55,
                                          32
                                        );
                                        doc.setFontSize(8);
                                        doc.setTextColor(0);
                                        doc.text(
                                          300,
                                          start,
                                          "(" +
                                            $scope.filterBillet[0]
                                              .airport_code_from +
                                            ")"
                                        );
                                        var dateBoard = $scope.flight_selected.boarding_date.split(
                                          "-"
                                        );
                                        var dateBoard1 = dateBoard[2].split(
                                          " "
                                        );
                                        var hour = dateBoard1[1].split(":");
                                        doc.setTextColor(64);
                                        doc.text(
                                          260,
                                          start + 10,
                                          hour[0] +
                                            ":" +
                                            hour[1] +
                                            " " +
                                            dateBoard1[0] +
                                            "/" +
                                            dateBoard[1] +
                                            "/" +
                                            dateBoard[0]
                                        );
                                        doc.addImage(
                                          base62Img,
                                          "PNG",
                                          335,
                                          start - 5,
                                          17,
                                          22
                                        );
                                        doc.setTextColor(0);
                                        doc.text(
                                          360,
                                          start,
                                          "(" +
                                            $scope.filterBillet[0]
                                              .airport_code_to +
                                            ")"
                                        );
                                        var dateLand = $scope.flight_selected.landing_date.split(
                                          "-"
                                        );
                                        var dateLand1 = dateLand[2].split(" ");
                                        var hourL = dateLand1[1].split(":");
                                        doc.setTextColor(64);
                                        doc.text(
                                          360,
                                          start + 10,
                                          hourL[0] +
                                            ":" +
                                            hourL[1] +
                                            " " +
                                            dateLand1[0] +
                                            "/" +
                                            dateLand[1] +
                                            "/" +
                                            dateLand[0]
                                        );

                                        start = start + 40;
                                        doc.setTextColor(96);
                                        doc.text(
                                          185,
                                          start,
                                          $scope.filterBillet[0].flight
                                        );

                                        doc.addImage(
                                          base61Img,
                                          "PNG",
                                          300,
                                          start - 10,
                                          12,
                                          12
                                        );
                                        doc.text(
                                          315,
                                          start - 5,
                                          "Cabine " +
                                            $scope.filterBillet[0].class
                                        );
                                        start = start + 20;
                                      }

                                      start = start + 20;

                                      doc.setTextColor(32);
                                      doc.setFontSize(9);

                                      var columns = [
                                        {
                                          title: "Passageiro",
                                          dataKey: "name",
                                        },
                                        {
                                          title: "Bagagem",
                                          dataKey: "baggage",
                                        },
                                        { title: "Assento", dataKey: "seat" },
                                      ];
                                      var rows = [];

                                      start = start + 20;
                                      var conectSeat = "";
                                      var paxs = $scope.getPaxLaTam(
                                        $scope.flight_selected.airline,
                                        $scope.flight_selected.cards_id,
                                        $scope.flight_selected.flight,
                                        $scope.flight_selected.flightLocator
                                      );

                                      var group = $filter("groupBy")(
                                        paxs,
                                        "pax"
                                      );

                                      const res = Object.keys(group).map(
                                        function (key) {
                                          return group[key];
                                        }
                                      );

                                      var inteRes = new Array();

                                      for (var j in res) {
                                        var pax = "";
                                        var seat = new Array();
                                        var baggage = "";

                                        pax = res[j][0].pax;
                                        baggage = res[j][0].baggage;

                                        for (var f in res[j]) {
                                          pax = res[j][f].pax;
                                          baggage = res[j][f].baggage;
                                          seat.push(res[j][f].sit);
                                        }

                                        inteRes.push({
                                          pax: pax,
                                          seat: seat,
                                          baggage: baggage,
                                        });
                                      }
                                      for (var i = 0; i < inteRes.length; i++) {
                                        var str = "";
                                        var seat = "";
                                        var element = inteRes[i];

                                        if (
                                          $scope.filterBillet[0].connection.trim() !=
                                          "Direto"
                                        ) {
                                          for (var g in element.seat) {
                                            conectSeat +=
                                              element.seat[g] + " / ";
                                            str = conectSeat;
                                          }
                                        } else {
                                          conectSeat = element.seat[0];
                                          str = conectSeat;
                                        }

                                        if (
                                          !element.baggage ||
                                          element.baggage == "0"
                                        ) {
                                          element.baggage = "Comprar Bagagem";
                                        }

                                        var seat = element.seat;
                                        if (!seat || seat == "---") {
                                          seat = "Escolher Assento";
                                        }

                                        rows.push({
                                          name: element.pax,
                                          baggage: element.baggage,
                                          seat: str,
                                        });
                                      }

                                      doc.autoTable(columns, rows, {
                                        columnStyles: {
                                          name: {
                                            columnWidth: 200,
                                          },
                                          baggage: {
                                            columnWidth: 100,
                                            textColor: [255, 128, 0],
                                          },
                                          seat: {
                                            columnWidth: 100,
                                            textColor: [255, 128, 0],
                                          },
                                        },
                                        showHeader: "firstPage",
                                        startY: start,
                                        headerStyles: {
                                          fillColor: [255, 255, 255],
                                          textColor: [0],
                                          fontSize: 9,
                                          lineWidth: 1,
                                          lineColor: [255, 255, 255],
                                        },
                                        styles: {
                                          fontSize: 8,
                                          textColor: 96,
                                        },
                                        margin: { left: 120 },
                                        theme: "plain",
                                        pageBreak: "avoid",
                                      });
                                    },
                                    drawCell: function (cell, data) {},
                                  });
                                  if (start >= 470) {
                                    start = 30;
                                    doc.autoTableAddPage();
                                  } else {
                                    start = start + 200;
                                  }

                                  if (
                                    $scope.getIsReturn($scope.flight_selected)
                                  ) {
                                    $scope.returnFlight = $scope.getReturnFlight(
                                      $scope.flight_selected.airline,
                                      $scope.flight_selected.cards_id,
                                      $scope.flight_selected.flight,
                                      $scope.flight_selected.airport_code_from,
                                      $scope.flight_selected.airport_code_to,
                                      $scope.flight_selected.flightLocator
                                    );
                                    $scope.RetConn =
                                      $scope.returnFlight.connection;

                                    doc.addImage(
                                      base59Img,
                                      "PNG",
                                      120,
                                      start,
                                      42,
                                      17
                                    );
                                    start = start + 40;

                                    var columns = [
                                      { title: "", dataKey: "name" },
                                      { title: "", dataKey: "data" },
                                      { title: "", dataKey: "hour" },
                                    ];
                                    var rows = [
                                      { name: "", data: "", hour: "" },
                                    ];

                                    doc.autoTable(columns, rows, {
                                      columnStyles: {},
                                      styles: {
                                        columnWidth: "auto",
                                        fontSize: 12,
                                        overflow: "linebreak",
                                        overflowColumns: false,
                                      },
                                      startY: start,
                                      margin: { left: 40 },
                                      theme: "plain",
                                      pageBreak: "auto",
                                      showHeader: "firstPage",
                                      addPageContent: function (datas) {
                                        doc.setLineWidth(0.1);
                                        doc.setFontSize(9);
                                        doc.setTextColor(64);
                                        doc.text(
                                          180,
                                          start,
                                          "Código Localizador Smiles"
                                        );
                                        doc.text(
                                          390,
                                          start,
                                          $scope.returnFlight.flightLocator
                                        );

                                        if (
                                          $scope.returnFlight.connection.trim() ==
                                          "Direto"
                                        ) {
                                          var y = "DIRETO";
                                        } else {
                                          var y = "PARADA(S)";
                                        }

                                        start = start + 30;
                                        doc.text(250, start, "DE");
                                        doc.text(290, start - 10, y);
                                        doc.addImage(
                                          base44Img,
                                          "PNG",
                                          280,
                                          start - 5,
                                          67,
                                          10
                                        );
                                        doc.text(360, start, "PARA");
                                        var hour = $scope.returnFlight.flight_time.split(
                                          ":"
                                        );
                                        doc.setTextColor(0);
                                        doc.text(
                                          300,
                                          start + 10,
                                          hour[0] + "h" + hour[1] + "m"
                                        );
                                        start = start + 10;
                                        doc.setTextColor(0);
                                        doc.setFontSize(12);
                                        doc.text(
                                          244,
                                          start,
                                          $scope.returnFlight.airport_code_from
                                        );
                                        doc.text(
                                          360,
                                          start,
                                          $scope.returnFlight.airport_code_to
                                        );
                                        start = start + 30;

                                        if (
                                          $scope.connectionsReturn.length > 0
                                        ) {
                                          var board = new Date(
                                            $scope.returnFlight.boarding_date
                                          );

                                          $scope.connectionsReturn.newboarding = new Date();
                                          $scope.connectionsReturn.newlanding = new Date();

                                          $scope.daysReturn = new Array();

                                          for (
                                            var index = 0;
                                            index <
                                            $scope.connectionsReturn.length;
                                            index++
                                          ) {
                                            $scope.connectionsReturn[
                                              index
                                            ].newboarding = new Date();
                                            $scope.connectionsReturn[
                                              index
                                            ].newlanding = new Date();

                                            var land = new Date(
                                              board.getFullYear(),
                                              board.getMonth(),
                                              board.getDate()
                                            );
                                            //var verifyReturn = $scope.connectionsReturn[index].boarding instanceof Date && !isNaN($scope.connectionsReturn[index].boarding.valueOf());
                                            var verifyReturn =
                                              $scope.connectionsReturn[index]
                                                .boarding.length;

                                            if (index > 0) {
                                              if (verifyReturn == 5) {
                                                if (
                                                  parseInt(
                                                    $scope.connectionsReturn[
                                                      index
                                                    ].boarding.split(":")[0]
                                                  ) <
                                                    parseInt(
                                                      $scope.connectionsReturn[
                                                        index - 1
                                                      ].landing.split(":")[0]
                                                    ) ||
                                                  parseInt(
                                                    $scope.connectionsReturn[
                                                      index - 1
                                                    ].boarding.split(":")[0]
                                                  ) >
                                                    parseInt(
                                                      $scope.connectionsReturn[
                                                        index - 1
                                                      ].landing.split(":")[0]
                                                    )
                                                ) {
                                                  land.setDate(
                                                    land.getDate() + 1
                                                  );
                                                }
                                              }
                                            }

                                            if (verifyReturn == 5) {
                                              board.setHours(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].boarding.split(":")[0]
                                                )
                                              );
                                              board.setMinutes(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].boarding.split(":")[1]
                                                )
                                              );

                                              land.setHours(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].landing.split(":")[0]
                                                )
                                              );
                                              land.setMinutes(
                                                parseInt(
                                                  $scope.connectionsReturn[
                                                    index
                                                  ].landing.split(":")[1]
                                                )
                                              );

                                              $scope.connectionsReturn[
                                                index
                                              ].newboarding = board;
                                              $scope.connectionsReturn[
                                                index
                                              ].newlanding = land;

                                              $scope.connectionsReturn[
                                                index
                                              ].newboarding.setDate(
                                                $scope.connectionsReturn[
                                                  index
                                                ].newlanding.getDate()
                                              );

                                              var cv =
                                                $scope.connectionsReturn[index]
                                                  .newboarding;
                                              var lv =
                                                $scope.connectionsReturn[index]
                                                  .newlanding;

                                              $scope.daysReturn.push({
                                                boarding: new Date(cv),
                                                landing: new Date(lv),
                                              });
                                            } else {
                                              var br = $scope.connectionsReturn[
                                                index
                                              ].boarding.split("/");
                                              var crr = $scope.connectionsReturn[
                                                index
                                              ].landing.split("/");
                                              var yr;
                                              var jr;
                                              var bdr;
                                              var bd2r;

                                              var htr = br[2].split(" ");
                                              var ctr = crr[2].split(" ");
                                              jr = br[2].split(":");
                                              yr = crr[2].split(":");

                                              if (htr && jr[1]) {
                                                bdr = new Date(
                                                  htr[0],
                                                  br[1],
                                                  br[0]
                                                );
                                                bdr.setHours(jr[0]);
                                                bdr.setMinutes(jr[1]);

                                                bd2r = new Date(
                                                  ctr[0],
                                                  crr[1],
                                                  crr[0]
                                                );
                                                bd2r.setHours(yr[0]);
                                                bd2r.setMinutes(yr[1]);

                                                $scope.daysReturn.push({
                                                  boarding: bdr,
                                                  landing: bd2r,
                                                });
                                              } else {
                                                $scope.daysReturn.push({
                                                  boarding: new Date(
                                                    br[2],
                                                    br[1],
                                                    br[0]
                                                  ),
                                                  landing: new Date(
                                                    crr[2],
                                                    crr[1],
                                                    crr[0]
                                                  ),
                                                });
                                              }
                                            }
                                          }

                                          $scope.daysReturn.forEach(function (
                                            el,
                                            index
                                          ) {
                                            ($scope.connectionsReturn[
                                              index
                                            ].newboarding = new Date(
                                              el.boarding
                                            )),
                                              ($scope.connectionsReturn[
                                                index
                                              ].newlanding = new Date(
                                                el.landing
                                              ));
                                          });

                                          for (
                                            var i = 0;
                                            i < $scope.connectionsReturn.length;
                                            i++
                                          ) {
                                            doc.addImage(
                                              base63Img,
                                              "PNG",
                                              180,
                                              start,
                                              50,
                                              32
                                            );
                                            doc.setFontSize(8);
                                            doc.setTextColor(0);
                                            doc.text(
                                              300,
                                              start,
                                              "(" +
                                                $scope.connectionsReturn[i]
                                                  .airportCodeFrom +
                                                ")"
                                            );
                                            doc.setTextColor(64);
                                            var datesR =
                                              $rootScope.pad(
                                                $scope.connectionsReturn[
                                                  i
                                                ].newboarding.getHours()
                                              ) +
                                              ":" +
                                              $rootScope.pad(
                                                $scope.connectionsReturn[
                                                  i
                                                ].newboarding.getMinutes()
                                              ) +
                                              " " +
                                              $rootScope
                                                .pad(
                                                  $scope.connectionsReturn[
                                                    i
                                                  ].newboarding.getDate()
                                                )
                                                .toString() +
                                              "/" +
                                              $rootScope
                                                .pad(
                                                  $scope.connectionsReturn[
                                                    i
                                                  ].newboarding.getMonth() + 1
                                                )
                                                .toString() +
                                              "/" +
                                              $scope.connectionsReturn[
                                                i
                                              ].newboarding
                                                .getFullYear()
                                                .toString();
                                            doc.text(260, start + 10, datesR);
                                            doc.addImage(
                                              base62Img,
                                              "PNG",
                                              335,
                                              start - 5,
                                              17,
                                              22
                                            );
                                            doc.setTextColor(0);
                                            doc.text(
                                              360,
                                              start,
                                              "(" +
                                                $scope.connectionsReturn[i]
                                                  .airportCodeTo +
                                                ")"
                                            );
                                            doc.setTextColor(64);

                                            var datesR1 =
                                              $rootScope.pad(
                                                $scope.connectionsReturn[
                                                  i
                                                ].newlanding.getHours()
                                              ) +
                                              ":" +
                                              $rootScope.pad(
                                                $scope.connectionsReturn[
                                                  i
                                                ].newlanding.getMinutes()
                                              ) +
                                              " " +
                                              $rootScope
                                                .pad(
                                                  $scope.connectionsReturn[
                                                    i
                                                  ].newlanding.getDate()
                                                )
                                                .toString() +
                                              "/" +
                                              $rootScope
                                                .pad(
                                                  $scope.connectionsReturn[
                                                    i
                                                  ].newlanding.getMonth() + 1
                                                )
                                                .toString() +
                                              "/" +
                                              $scope.connectionsReturn[
                                                i
                                              ].newlanding
                                                .getFullYear()
                                                .toString();
                                            doc.text(360, start + 10, datesR1);
                                            start = start + 40;
                                            doc.setTextColor(96);
                                            doc.text(
                                              185,
                                              start,
                                              $scope.connectionsReturn[i].flight
                                            );

                                            doc.addImage(
                                              base61Img,
                                              "PNG",
                                              300,
                                              start - 10,
                                              12,
                                              12
                                            );
                                            doc.text(
                                              315,
                                              start - 5,
                                              "Cabine " +
                                                $scope.returnFlight.class
                                            );
                                            start = start + 20;
                                          }
                                        } else {
                                          doc.addImage(
                                            base63Img,
                                            "PNG",
                                            180,
                                            start,
                                            55,
                                            32
                                          );
                                          doc.setFontSize(8);
                                          doc.setTextColor(0);
                                          doc.text(
                                            300,
                                            start,
                                            "(" +
                                              $scope.returnFlight
                                                .airport_code_from +
                                              ")"
                                          );
                                          var dateBoard = $scope.returnFlight.boarding_date.split(
                                            "-"
                                          );
                                          var dateBoard1 = dateBoard[2].split(
                                            " "
                                          );
                                          var hour = dateBoard1[1].split(":");
                                          doc.setTextColor(64);
                                          doc.text(
                                            260,
                                            start + 10,
                                            hour[0] +
                                              ":" +
                                              hour[1] +
                                              " " +
                                              dateBoard1[0] +
                                              "/" +
                                              dateBoard[1] +
                                              "/" +
                                              dateBoard[0]
                                          );
                                          doc.addImage(
                                            base62Img,
                                            "PNG",
                                            335,
                                            start - 5,
                                            17,
                                            22
                                          );
                                          doc.setTextColor(0);
                                          doc.text(
                                            360,
                                            start,
                                            "(" +
                                              $scope.returnFlight
                                                .airport_code_to +
                                              ")"
                                          );
                                          var dateLand = $scope.returnFlight.landing_date.split(
                                            "-"
                                          );
                                          var dateLand1 = dateLand[2].split(
                                            " "
                                          );
                                          var hourL = dateLand1[1].split(":");
                                          doc.setTextColor(64);
                                          doc.text(
                                            360,
                                            start + 10,
                                            hourL[0] +
                                              ":" +
                                              hourL[1] +
                                              " " +
                                              dateLand1[0] +
                                              "/" +
                                              dateLand[1] +
                                              "/" +
                                              dateLand[0]
                                          );
                                          start = start + 40;
                                          doc.setTextColor(96);
                                          doc.text(
                                            185,
                                            start,
                                            $scope.returnFlight.flight
                                          );

                                          doc.addImage(
                                            base61Img,
                                            "PNG",
                                            300,
                                            start - 10,
                                            12,
                                            12
                                          );
                                          doc.text(
                                            315,
                                            start - 5,
                                            "Cabine " +
                                              $scope.returnFlight.class
                                          );
                                          start = start + 20;
                                        }

                                        start = start + 20;

                                        doc.setTextColor(0);
                                        doc.setFontSize(9);

                                        var columns = [
                                          {
                                            title: "Passageiro",
                                            dataKey: "name",
                                          },
                                          {
                                            title: "Bagagem",
                                            dataKey: "baggage",
                                          },
                                          { title: "Assento", dataKey: "seat" },
                                        ];

                                        var rows = [];

                                        start = start + 30;
                                        var conectSeatRet = "";

                                        $scope.paxReturn = $scope.getPaxLaTamReturn(
                                          $scope.returnFlight.airline,
                                          $scope.returnFlight.cards_id,
                                          $scope.returnFlight.flight,
                                          $scope.returnFlight.flightLocator
                                        );

                                        var letsR = $filter("groupBy")(
                                          $scope.paxReturn,
                                          "pax"
                                        );

                                        const resultsRet = Object.keys(
                                          letsR
                                        ).map(function (key) {
                                          return letsR[key];
                                        });

                                        var inteR = new Array();

                                        for (var jn in resultsRet) {
                                          var seatReturn = new Array();
                                          var pax = "";
                                          var baggage = "";
                                          for (var fn in resultsRet[jn]) {
                                            pax = resultsRet[jn][fn].pax;
                                            baggage =
                                              resultsRet[jn][fn].baggage;

                                            seatReturn.push(
                                              resultsRet[jn][fn].sit
                                            );
                                          }

                                          inteR.push({
                                            pax: pax,
                                            seat: seatReturn,
                                            baggage: baggage,
                                          });
                                        }

                                        for (var i = 0; i < inteR.length; i++) {
                                          var strRet = "";
                                          var seatRet = "";
                                          var elementRet = inteR[i];

                                          if (
                                            $scope.RetConn.trim() != "Direto"
                                          ) {
                                            for (var h in elementRet.seat) {
                                              conectSeatRet +=
                                                elementRet.seat[h] + " / ";
                                              strRet = conectSeatRet;
                                            }

                                            //strRet = conectSeatRet.concat("/" + conectSeatRet);
                                          } else {
                                            conectSeatRet = elementRet.seat[0];
                                            strRet = conectSeatRet;
                                          }
                                          elementRet.baggage.toString();

                                          if (
                                            !elementRet.baggage ||
                                            elementRet.baggage == "0"
                                          ) {
                                            elementRet.baggage =
                                              "Comprar Bagagem";
                                          }
                                          if (!strRet || strRet == "---") {
                                            strRet = "Escolher Assento";
                                          }

                                          rows.push({
                                            name: elementRet.pax,
                                            baggage: elementRet.baggage,
                                            seat: strRet,
                                          });
                                        }

                                        doc.autoTable(columns, rows, {
                                          startY: start,
                                          margin: { left: 120 },
                                          theme: "plain",
                                          pageBreak: "avoid",
                                          columnStyles: {
                                            name: {
                                              columnWidth: 200,
                                            },
                                            baggage: {
                                              columnWidth: 100,
                                              textColor: [255, 128, 0],
                                            },
                                            seat: {
                                              columnWidth: 100,
                                              textColor: [255, 128, 0],
                                            },
                                          },
                                          showHeader: "firstPage",
                                          drawRow: function (row, data) {
                                            /*doc.setDrawColor(192, 192, 192); // draw red lines
									  doc.setLineWidth(0.9);
									  doc.line(100, start + 30, 500, start + 30);*/
                                          },
                                          headerStyles: {
                                            fillColor: [255, 255, 255],
                                            textColor: 0,
                                            fontSize: 9,
                                            lineWidth: 1,
                                            lineColor: [255, 255, 255],
                                          },
                                          styles: {
                                            fontSize: 8,
                                            textColor: 96,
                                            overflowColumns: false,
                                          },
                                        });

                                        for (var r in rows) {
                                          start = start + 15;
                                        }
                                      },
                                    });
                                    start = start + 50;
                                    /*if (start >= 600) {
								start = 30;
								doc.autoTableAddPage();
							  } else {
								start = start + 200;
							  }*/
                                    if (start >= 600) {
                                      start = (start * 20) / 100;
                                    }
                                  }
                                  start = start + 20;

                                  var card_number = $scope.filterBillet[0]
                                    .tax_card
                                    ? $scope.filterBillet[0].tax_card.replace(
                                        /.(?=.{4})/g,
                                        "*"
                                      )
                                    : "";
                                  var n = $rootScope.formatNumber(
                                    $scope.getTotalTaxFlight(
                                      $scope.filterBillet[0].flightLocator
                                    )
                                  );
                                  n = n.toString();

                                  var columns = [
                                    {
                                      title: " Forma de Pagamento (R$) ",
                                      dataKey: "name",
                                    },
                                  ];
                                  var rows = [
                                    { name: " Cartão de Crédito " },
                                    {
                                      name:
                                        " " +
                                        ($scope.filterBillet[0].tax_providerName
                                          ? $scope.filterBillet[0]
                                              .tax_providerName
                                          : ""),
                                    },
                                    { name: " R$ " + n + "  em 1x" },
                                  ];

                                  var images = [];

                                  doc.autoTable(columns, rows, {
                                    columnStyles: {},
                                    startY: start,
                                    styles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    headerStyles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    margin: { left: 115 },
                                    theme: "plain",
                                    pageBreak: "avoid",
                                    showHeader: "firstPage",
                                    addPageContent: function (data) {
                                      doc.setFontSize(11);
                                      doc.setTextColor(0);
                                    },
                                  });

                                  start = start + 90;

                                  var columns = [
                                    {
                                      title: " Dados do Comprador",
                                      dataKey: "name",
                                    },
                                  ];

                                  var rows = [
                                    {
                                      name:
                                        "Nome: " +
                                        $scope.flight_selected.providerName,
                                    },
                                    {
                                      name:
                                        "Telefone: " +
                                        $scope.flight_selected.provider_phone,
                                    },
                                  ];

                                  var images = [];

                                  doc.autoTable(columns, rows, {
                                    columnStyles: {},
                                    startY: start,
                                    styles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    headerStyles: {
                                      columnWidth: "auto",
                                      fontSize: 9,
                                    },
                                    margin: { left: 115 },
                                    theme: "plain",
                                    overflow: "linebreak",
                                    tableWidth: "auto",
                                    pageBreak: "auto",
                                    showHeader: "firstPage",
                                    addPageContent: function (data) {
                                      start = start + 70;
                                    },
                                  });

                                  start = doc.autoTableEndPosY() + 10;

                                  var columns = [
                                    { title: " ", dataKey: "names" },
                                  ];

                                  var rows = [{ names: "" }];

                                  if (start >= 600) {
                                    start = 20;
                                    doc.autoTableAddPage();
                                  }
                                  doc.autoTable(columns, rows, {
                                    startY: start,
                                    margin: { left: 120 },
                                    theme: "plain",
                                    pageBreak: "avoid",
                                    columnStyles: {
                                      names: { columnWidth: 200 },
                                      baggage: { columnWidth: 100 },
                                      seat: { columnWidth: 70 },
                                    },
                                    //showHeader: 'firstPage',
                                    drawCell: function (cell, opts) {
                                      if (opts.column.dataKey == "names") {
                                        images.push({
                                          x: cell.textPos.x,
                                          y: cell.textPos.y,
                                        });
                                      }
                                    },
                                    addPageContent: function () {
                                      var y = images[0].y;
                                      doc.addImage(
                                        base14Img,
                                        "PNG",
                                        images[0].x - 40,
                                        start,
                                        450,
                                        390
                                      );
                                    },
                                  });

                                  doc.save(
                                    $scope.flight_selected.flightLocator +
                                      ".pdf"
                                  );
                                  if ($scope.multiDownloads !== true) {
                                    if ($scope.response) {
                                      if (
                                        $scope.response.notificationurl !=
                                          null &&
                                        $scope.response.notificationurl.indexOf(
                                          "http"
                                        ) == -1
                                      ) {
                                        $scope.fillEmailBillet();
                                      }
                                    } else {
                                      $scope.fillEmailBillet();
                                    }
                                  }
                                });
                              });
                            });
                          });
                        });
                      });
                    });
                  });
                }
              );
            }
          );
        };

        $scope.printBilletGol4 = function (flight_selected) {
          $scope.flight_selected = flight_selected || this.onlineflight;
          // if ($scope.selected.notificationurl != '' && $scope.selected.notificationurl != null) {
          $scope.golNovo($scope.flight_selected);
          // } else {
          //   $scope.golvelho($scope.flight_selected);
          // }
        };

        $scope.getTotalTaxFlight = function (flightLocator) {
          var tax = 0;
          $scope.selectedFlights = $filter("filter")(
            $scope.onlineflights,
            flightLocator
          );
          for (var i in $scope.selectedFlights) {
            if (
              $scope.selectedFlights[i].is_newborn != "S" &&
              $scope.selectedFlights[i].flightLocator
            ) {
              //tax += $scope.selectedFlights[i].tax_billet;
              tax += $scope.selectedFlights[i].tax;
              if ($scope.selectedFlights[i].money) {
                tax += $scope.selectedFlights[i].money;
              }
            }
          }
          return tax;
        };

        $scope.getTotalTaxFlightLatam = function (flightLocator, pax_name) {
          var tax = 0;
          $scope.selectedFlights = $filter("filter")(
            $scope.onlineflights,
            flightLocator
          );
          for (var i in $scope.selectedFlights) {
            if (
              $scope.selectedFlights[i].is_newborn != "S" &&
              pax_name == $scope.selectedFlights[i].pax_name
            ) {
              tax += $scope.selectedFlights[i].tax_billet;
              if ($scope.selectedFlights[i].money) {
                tax += $scope.selectedFlights[i].money;
              }
            }
          }
          return tax;
        };

        $scope.getTotalMilesFlight = function (flightLocator, flight) {
          var miles = 0;
          $scope.selectedFlights = $filter("filter")(
            $scope.onlineflights,
            flightLocator
          );
          for (var i in $scope.selectedFlights) {
            if ($scope.selectedFlights[i].flight == flight) {
              miles += $scope.selectedFlights[i].miles_used;
            }
          }
          return miles;
        };

        $scope.getTotalMilesOrder = function (flightLocator) {
          var miles = 0;
          $scope.selectedFlights = $filter("filter")(
            $scope.onlineflights,
            flightLocator
          );
          for (var i in $scope.selectedFlights) {
            miles += $scope.selectedFlights[i].miles_used;
          }
          return miles;
        };

        $scope.getTotalTaxFlightFlight = function (flightLocator, flight) {
          var tax = 0;
          $scope.selectedFlights = $filter("filter")(
            $scope.onlineflights,
            flightLocator
          );
          for (var i in $scope.selectedFlights) {
            if ($scope.selectedFlights[i].flight == flight) {
              if ($scope.selectedFlights[i].is_newborn != "S") {
                tax += $scope.selectedFlights[i].tax_billet;
                if ($scope.selectedFlights[i].money) {
                  tax += $scope.selectedFlights[i].money;
                }
              }
            }
          }
          return tax;
        };

        $scope.toDataUrl = function (url, callback) {
          var xhr = new XMLHttpRequest();
          xhr.responseType = "blob";
          xhr.onload = function () {
            var reader = new FileReader();
            reader.onloadend = function () {
              callback(reader.result);
            };
            reader.readAsDataURL(xhr.response);
          };
          xhr.open("GET", url);
          xhr.send();
        };

        $scope.fillEmailBillet = function (onlineflight) {
          if (onlineflight) {
            $scope.flight_selected = onlineflight;
          }
          if ($scope.response) {
            if ($scope.response.origin == "MMS") {
              $scope.wsalemail.emailpartner = $scope.selected.client_email;
              $scope.wsalemail.mailcco = undefined;
              if ($scope.selected.issuing.indexOf("app.voelegal") > -1) {
                $scope.wsalemail.emailpartner = 'adm@onemilhas.com.br';
              }
              if ($scope.selected.subClientEmail) {
                // $scope.wsalemail.emailpartner = $scope.selected.subClientEmail;
                $scope.wsalemail.subClientEmail =
                  $scope.selected.subClientEmail;
              }
              $scope.wsalemail.mailcc = "";
              $scope.wsalemail.subject = "] - Bilhete - ";

              $scope.wsalemail.emailContent =
                "Olá " + $scope.selected.client_name + ",<br><br>";

              for (var i in $scope.onlineflights) {
                $scope.wsalemail.subject +=
                  "LOC " + $scope.onlineflights[i].flightLocator + " - ";
              }
              $scope.wsalemail.subject += " - ID: " + $scope.selected.id;

              if ($scope.flight_selected.airline == "GOL") {
                $scope.selectedFlights = $filter("filter")(
                  $scope.onlineflights,
                  "GOL"
                );

                if ($scope.selectedFlights.length > 0) {
                  $scope.wsalemail.emailContent +=
                    "Segue bilhete em anexo.<br>" +
                    "Obs.:  Informamos que a GOL, como medida de segurança, pode solicitar os dados do proprietário das milhas no momento de seu embarque. Segue os dados abaixo e também no seu bilhete em anexo.<br>" +
                    "Caso o passageiro tenha algum problema no momento do embarque, entre em contato<br>" +
                    "imediatamente com a equipe, providenciaremos a regularização do<br>" +
                    "embarque, ou reacomodação. Bilhetes comprados no balcão sem<br>" +
                    "autorização da nossa equipe, não serão ressarcidos pela. Essas medidas<br>" +
                    "visam evitar transtornos para os nossos clientes. Grato pela compreensão.<br><br>";
                }

                for (var i in $scope.selectedFlights) {
                  var card_number =
                    $scope.removeFromEmail.indexOf(
                      $scope.selectedFlights[i].card_number
                    ) > -1
                      ? " - "
                      : $scope.selectedFlights[i].card_number;
                  var providerName =
                    $scope.removeFromEmail.indexOf(
                      $scope.selectedFlights[i].providerName
                    ) > -1
                      ? " - "
                      : $scope.selectedFlights[i].providerName;
                  $scope.wsalemail.emailContent +=
                    "SMILES:" +
                    card_number +
                    "<br>NOME:" +
                    providerName +
                    "<br><br>";

                  if (
                    $scope.flight_selected.airline ==
                      $scope.selectedFlights[i].airline &&
                    $scope.flight_selected.flightLocator ==
                      $scope.selectedFlights[i].flightLocator
                  ) {
                    if ($scope.selectedFlights[i].seat != " --- ") {
                      var pax_name = $scope.selectedFlights[i].pax_name;
                      if ($scope.selectedFlights[i].paxLastName) {
                        pax_name += " " + $scope.selectedFlights[i].paxLastName;
                      }
                      if ($scope.selectedFlights[i].paxAgnome) {
                        pax_name += " " + $scope.selectedFlights[i].paxAgnome;
                      }
                      //$scope.wsalemail.emailContent += "<br>Voo: " + $scope.selectedFlights[i].flight + ', pax:' + pax_name + ', assento: ' + $scope.selectedFlights[i].seat + '<br>';
                    }
                  }
                }
              } else if ($scope.flight_selected.airline == "AZUL") {
                $scope.wsalemail.emailContent +=
                  "Segue em anexo bilhete Azul, conforme solicitado!<br>" +
                  "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                  "E-mail: suporte@onemilhas.com.br<br>" +
                  "Tel: (31) 3972-1929<br><br>";
                ("Att<br>Equipe");
              } else if ($scope.flight_selected.airline == "AVIANCA") {
                $scope.wsalemail.emailContent +=
                  "Segue em anexo bilhete Avianca, conforme solicitado!<br>";

                for (var i in $scope.onlineflights) {
                  if (
                    $scope.flight_selected.airline ==
                      $scope.onlineflights[i].airline &&
                    $scope.flight_selected.flightLocator ==
                      $scope.onlineflights[i].flightLocator
                  ) {
                    if ($scope.onlineflights[i].seat != " --- ") {
                      var pax_name = $scope.onlineflights[i].pax_name;
                      if ($scope.onlineflights[i].paxLastName) {
                        pax_name += " " + $scope.onlineflights[i].paxLastName;
                      }
                      if ($scope.onlineflights[i].paxAgnome) {
                        pax_name += " " + $scope.onlineflights[i].paxAgnome;
                      }
                      $scope.wsalemail.emailContent +=
                        "<br>Voo: " +
                        $scope.onlineflights[i].flight +
                        ", pax:" +
                        pax_name +
                        ", assento: " +
                        $scope.onlineflights[i].seat +
                        "<br>";
                    }
                  }
                }

                $scope.wsalemail.emailContent +=
                  "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                  "E-mail: suporte@onemilhas.com.br<br>" +
                  "Tel: (31) 3972-1929<br><br>";
                ("Att<br>Equipe");
              } else if ($scope.flight_selected.airline == "LATAM") {
                $scope.wsalemail.emailContent +=
                  "Segue em anexo confirmação de compra Latam, conforme solicitado!<br>";
                "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                  "E-mail: suporte@onemilhas.com.br<br>" +
                  "Tel: (31) 3972-1929<br><br>";
                ("Att<br>Equipe");
              }

              $scope.wsalemail.emailContent +=
                "<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Trechos</font></td></tr>" +
                "<tr><td>CIA</td><td>VOO</td><td>Conexões</td><td>Embarque</td><td>Desembarque</td><td>Duração</td><td>Origem</td><td>Destino</td></tr>";
              for (var i = 0; $scope.resume_flights.length > i; i++) {
                var testes = $scope.resume_flights[i].connection.split(" ");
                var connections = "";
                for (var j = 0; j < testes.length; j++) {
                  connections += testes[j] + "<br>";
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td>" +
                  $scope.resume_flights[i].airline +
                  "</td><td>" +
                  $scope.resume_flights[i].flight +
                  "</td><td>" +
                  connections +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $scope.resume_flights[i].flight_time +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_from +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_to +
                  "</td></tr>";
              }
              $scope.wsalemail.emailContent +=
                "</tbody></table>" +
                "<br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'>Passageiros</font></td></tr>";
              for (var i = 0; $scope.resume_paxs.length > i; i++) {
                var pax_name = $scope.resume_paxs[i].pax_name;
                if ($scope.resume_paxs[i].paxLastName) {
                  pax_name += " " + $scope.resume_paxs[i].paxLastName;
                }
                if ($scope.resume_paxs[i].paxAgnome) {
                  pax_name += " " + $scope.resume_paxs[i].paxAgnome;
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'> Passageiro " +
                  (i + 1) +
                  " </font></td></tr><tr><td>Nome:</td><td>" +
                  pax_name +
                  "</td></tr><tr><td>Identificação:</td><td>" +
                  $scope.resume_paxs[i].identification +
                  "</td></tr><tr><td>Data Nascimento:</td><td>" +
                  $filter("date")(
                    $scope.resume_paxs[i].birhtdate,
                    "dd/MM/yyyy"
                  ) +
                  "</td></tr>";
                if ($scope.resume_paxs[i].is_child != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>CHD</td></tr>";
                if ($scope.resume_paxs[i].is_newborn != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>INF</td></tr>";
              }
              $scope.wsalemail.emailContent += "</tbody></table><br><br>";
            } else {
              $scope.wsalemail.emailpartner = $scope.selected.client_email;
              $scope.wsalemail.mailcco = undefined;
              if ($scope.selected.issuing.indexOf("app.voelegal") > -1) {
                $scope.wsalemail.emailpartner = 'adm@onemilhas.com.br';
              }
              if ($scope.selected.subClientEmail) {
                // $scope.wsalemail.emailpartner = $scope.selected.subClientEmail;
                $scope.wsalemail.subClientEmail =
                  $scope.selected.subClientEmail;
              }
              $scope.wsalemail.mailcc = "";
              $scope.wsalemail.subject = "Bilhete - ";

              $scope.wsalemail.emailContent =
                "Olá " + $scope.selected.client_name + ",<br><br>";

              for (var i in $scope.onlineflights) {
                $scope.wsalemail.subject +=
                  "LOC " + $scope.onlineflights[i].flightLocator + " - ";
              }
              $scope.wsalemail.subject += " - ID: " + $scope.selected.id;

              if ($scope.flight_selected.airline == "GOL") {
                $scope.selectedFlights = $filter("filter")(
                  $scope.onlineflights,
                  "GOL"
                );

                if ($scope.selectedFlights.length > 0) {
                  $scope.wsalemail.emailContent +=
                    "Segue bilhete em anexo.<br>" +
                    "Obs.:  Informamos que a GOL, como medida de segurança, pode solicitar os dados do proprietário das milhas no momento de seu embarque. Segue os dados abaixo e também no seu bilhete em anexo.<br>" +
                    "Caso o passageiro tenha algum problema no momento do embarque, entre em contato<br>" +
                    "imediatamente com a equipe ONE MILHAS, providenciaremos a regularização do<br>" +
                    "embarque, ou reacomodação. Bilhetes comprados no balcão sem<br>" +
                    "autorização da nossa equipe, não serão ressarcidos pela ONE MILHAS. Essas medidas<br>" +
                    "visam evitar transtornos para os nossos clientes. Grato pela compreensão.<br><br>";

                  /*if ($scope.selectedFlights[0].baggage > 0) {
				  $scope.wsalemail.emailContent += "*Seu bilhete possui " + $scope.selectedFlights[0].baggage + " bagagen(s) despachada(s) incluído!<br><br>";
				} else {
				  $scope.wsalemail.emailContent += "*Seu bilhete não possui bagagem despachada incluído!<br><br>";
				}*/
                }

                for (var i in $scope.selectedFlights) {
                  var card_number =
                    $scope.removeFromEmail.indexOf(
                      $scope.selectedFlights[i].card_number
                    ) > -1
                      ? " - "
                      : $scope.selectedFlights[i].card_number;
                  var providerName =
                    $scope.removeFromEmail.indexOf(
                      $scope.selectedFlights[i].providerName
                    ) > -1
                      ? " - "
                      : $scope.selectedFlights[i].providerName;
                  $scope.wsalemail.emailContent +=
                    "SMILES:" +
                    card_number +
                    "<br>NOME:" +
                    providerName +
                    "<br><br>";

                  if (
                    $scope.flight_selected.airline ==
                      $scope.selectedFlights[i].airline &&
                    $scope.flight_selected.flightLocator ==
                      $scope.selectedFlights[i].flightLocator
                  ) {
                    if ($scope.selectedFlights[i].seat != " --- ") {
                      var pax_name = $scope.selectedFlights[i].pax_name;
                      if ($scope.selectedFlights[i].paxLastName) {
                        pax_name += " " + $scope.selectedFlights[i].paxLastName;
                      }
                      if ($scope.selectedFlights[i].paxAgnome) {
                        pax_name += " " + $scope.selectedFlights[i].paxAgnome;
                      }
                      //$scope.wsalemail.emailContent += "<br>Voo: " + $scope.selectedFlights[i].flight + ', pax:' + pax_name + ', assento: ' + $scope.selectedFlights[i].seat + '<br>';
                    }
                  }
                }
              } else if ($scope.flight_selected.airline == "AZUL") {
                $scope.wsalemail.emailContent +=
                  "Segue em anexo bilhete Azul, conforme solicitado!<br>" +
                  "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                  "E-mail: emissao@onemilhas.com.br<br>" +
                  "Tel: (31) 3972-9601<br><br>";
                ("Att<br>Equipe One Milhas");
              } else if ($scope.flight_selected.airline == "AVIANCA") {
                $scope.wsalemail.emailContent +=
                  "Segue em anexo bilhete Avianca, conforme solicitado!<br>";

                for (var i in $scope.onlineflights) {
                  if (
                    $scope.flight_selected.airline ==
                      $scope.onlineflights[i].airline &&
                    $scope.flight_selected.flightLocator ==
                      $scope.onlineflights[i].flightLocator
                  ) {
                    if ($scope.onlineflights[i].seat != " --- ") {
                      var pax_name = $scope.onlineflights[i].pax_name;
                      if ($scope.onlineflights[i].paxLastName) {
                        pax_name += " " + $scope.onlineflights[i].paxLastName;
                      }
                      if ($scope.onlineflights[i].paxAgnome) {
                        pax_name += " " + $scope.onlineflights[i].paxAgnome;
                      }
                      $scope.wsalemail.emailContent +=
                        "<br>Voo: " +
                        $scope.onlineflights[i].flight +
                        ", pax:" +
                        pax_name +
                        ", assento: " +
                        $scope.onlineflights[i].seat +
                        "<br>";
                    }
                  }
                }

                $scope.wsalemail.emailContent +=
                  "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                  "E-mail: emissao@onemilhas.com.br<br>" +
                  "Tel: (31) 3972-9601<br><br>";
                ("Att<br>Equipe One Milhas");
              } else if ($scope.flight_selected.airline == "LATAM") {
                $scope.wsalemail.emailContent +=
                  "Segue em anexo confirmação de compra Latam, conforme solicitado!<br>";
                "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                "E-mail: emissao@onemilhas.com.br<br>" +
                  "Tel: (31) 3972-9601<br><br>";
                ("Att<br>Equipe One Milhas");
              }

              $scope.wsalemail.emailContent +=
                "<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Trechos</font></td></tr>" +
                "<tr><td>CIA</td><td>VOO</td><td>Conexões</td><td>Embarque</td><td>Desembarque</td><td>Duração</td><td>Origem</td><td>Destino</td></tr>";
              for (var i = 0; $scope.resume_flights.length > i; i++) {
                var testes = $scope.resume_flights[i].connection.split(" ");
                var connections = "";
                for (var j = 0; j < testes.length; j++) {
                  connections += testes[j] + "<br>";
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td>" +
                  $scope.resume_flights[i].airline +
                  "</td><td>" +
                  $scope.resume_flights[i].flight +
                  "</td><td>" +
                  connections +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].boarding_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "dd/MM/yyyy"
                  ) +
                  "<br>" +
                  $filter("date")(
                    $rootScope.findDate($scope.resume_flights[i].landing_date),
                    "HH:mm:ss"
                  ) +
                  "</td><td>" +
                  $scope.resume_flights[i].flight_time +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_from +
                  "</td><td>" +
                  $scope.resume_flights[i].airport_code_to +
                  "</td></tr>";
              }
              $scope.wsalemail.emailContent +=
                "</tbody></table>" +
                "<br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'>Passageiros</font></td></tr>";
              for (var i = 0; $scope.resume_paxs.length > i; i++) {
                var pax_name = $scope.resume_paxs[i].pax_name;
                if ($scope.resume_paxs[i].paxLastName) {
                  pax_name += " " + $scope.resume_paxs[i].paxLastName;
                }
                if ($scope.resume_paxs[i].paxAgnome) {
                  pax_name += " " + $scope.resume_paxs[i].paxAgnome;
                }
                $scope.wsalemail.emailContent +=
                  "<tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'> Passageiro " +
                  (i + 1) +
                  " </font></td></tr><tr><td>Nome:</td><td>" +
                  pax_name +
                  "</td></tr><tr><td>Identificação:</td><td>" +
                  $scope.resume_paxs[i].identification +
                  "</td></tr><tr><td>Data Nascimento:</td><td>" +
                  $filter("date")(
                    $scope.resume_paxs[i].birhtdate,
                    "dd/MM/yyyy"
                  ) +
                  "</td></tr>";
                if ($scope.resume_paxs[i].is_child != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>CHD</td></tr>";
                if ($scope.resume_paxs[i].is_newborn != "N")
                  $scope.wsalemail.emailContent +=
                    "<tr><td></td><td>INF</td></tr>";
              }
              $scope.wsalemail.emailContent += "</tbody></table><br><br>";
            }
          } else {
            $scope.wsalemail.emailpartner = $scope.selected.client_email;
            $scope.wsalemail.mailcco = undefined;
            if ($scope.selected.issuing.indexOf("app.voelegal") > -1) {
              $scope.wsalemail.emailpartner = 'adm@onemilhas.com.br';
            }
            if ($scope.selected.subClientEmail) {
              // $scope.wsalemail.emailpartner = $scope.selected.subClientEmail;
              $scope.wsalemail.subClientEmail = $scope.selected.subClientEmail;
            }
            $scope.wsalemail.mailcc = "";
            $scope.wsalemail.subject = "Bilhete - ";

            $scope.wsalemail.emailContent =
              "Olá " + $scope.selected.client_name + ",<br><br>";

            for (var i in $scope.onlineflights) {
              $scope.wsalemail.subject +=
                "LOC " + $scope.onlineflights[i].flightLocator + " - ";
            }
            $scope.wsalemail.subject += " - ID: " + $scope.selected.id;

            if ($scope.flight_selected.airline == "GOL") {
              $scope.selectedFlights = $filter("filter")(
                $scope.onlineflights,
                "GOL"
              );

              if ($scope.selectedFlights.length > 0) {
                $scope.wsalemail.emailContent +=
                  "Segue bilhete em anexo.<br>" +
                  "Obs.:  Informamos que a GOL, como medida de segurança, pode solicitar os dados do proprietário das milhas no momento de seu embarque. Segue os dados abaixo e também no seu bilhete em anexo.<br>" +
                  "Caso o passageiro tenha algum problema no momento do embarque, entre em contato<br>" +
                  "imediatamente com a equipe ONE MILHAS, providenciaremos a regularização do<br>" +
                  "embarque, ou reacomodação. Bilhetes comprados no balcão sem<br>" +
                  "autorização da nossa equipe, não serão ressarcidos pela ONE MILHAS. Essas medidas<br>" +
                  "visam evitar transtornos para os nossos clientes. Grato pela compreensão.<br><br>";

                /*if ($scope.selectedFlights[0].baggage > 0) {
				$scope.wsalemail.emailContent += "*Seu bilhete possui " + $scope.selectedFlights[0].baggage + " bagagen(s) despachada(s) incluído!<br><br>";
			  } else {
				$scope.wsalemail.emailContent += "*Seu bilhete não possui bagagem despachada incluído!<br><br>";
			  }*/
              }

              for (var i in $scope.selectedFlights) {
                var card_number =
                  $scope.removeFromEmail.indexOf(
                    $scope.selectedFlights[i].card_number
                  ) > -1
                    ? " - "
                    : $scope.selectedFlights[i].card_number;
                var providerName =
                  $scope.removeFromEmail.indexOf(
                    $scope.selectedFlights[i].providerName
                  ) > -1
                    ? " - "
                    : $scope.selectedFlights[i].providerName;
                $scope.wsalemail.emailContent +=
                  "SMILES:" +
                  card_number +
                  "<br>NOME:" +
                  providerName +
                  "<br><br>";

                if (
                  $scope.flight_selected.airline ==
                    $scope.selectedFlights[i].airline &&
                  $scope.flight_selected.flightLocator ==
                    $scope.selectedFlights[i].flightLocator
                ) {
                  if ($scope.selectedFlights[i].seat != " --- ") {
                    var pax_name = $scope.selectedFlights[i].pax_name;
                    if ($scope.selectedFlights[i].paxLastName) {
                      pax_name += " " + $scope.selectedFlights[i].paxLastName;
                    }
                    if ($scope.selectedFlights[i].paxAgnome) {
                      pax_name += " " + $scope.selectedFlights[i].paxAgnome;
                    }
                    //$scope.wsalemail.emailContent += "<br>Voo: " + $scope.selectedFlights[i].flight + ', pax:' + pax_name + ', assento: ' + $scope.selectedFlights[i].seat + '<br>' //;
                  }
                }
              }
            } else if ($scope.flight_selected.airline == "AZUL") {
              $scope.wsalemail.emailContent +=
                "Segue em anexo bilhete Azul, conforme solicitado!<br>" +
                "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                "E-mail: emissao@onemilhas.com.br<br>" +
                "Tel: (31) 3972-9601<br><br>";
              ("Att<br>Equipe One Milhas");
            } else if ($scope.flight_selected.airline == "AVIANCA") {
              $scope.wsalemail.emailContent +=
                "Segue em anexo bilhete Avianca, conforme solicitado!<br>";

              for (var i in $scope.onlineflights) {
                if (
                  $scope.flight_selected.airline ==
                    $scope.onlineflights[i].airline &&
                  $scope.flight_selected.flightLocator ==
                    $scope.onlineflights[i].flightLocator
                ) {
                  if ($scope.onlineflights[i].seat != " --- ") {
                    var pax_name = $scope.onlineflights[i].pax_name;
                    if ($scope.onlineflights[i].paxLastName) {
                      pax_name += " " + $scope.onlineflights[i].paxLastName;
                    }
                    if ($scope.onlineflights[i].paxAgnome) {
                      pax_name += " " + $scope.onlineflights[i].paxAgnome;
                    }
                    $scope.wsalemail.emailContent +=
                      "<br>Voo: " +
                      $scope.onlineflights[i].flight +
                      ", pax:" +
                      pax_name +
                      ", assento: " +
                      $scope.onlineflights[i].seat +
                      "<br>";
                  }
                }
              }

              $scope.wsalemail.emailContent +=
                "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
                "E-mail: emissao@onemilhas.com.br<br>" +
                "Tel: (31) 3972-9601<br><br>";
              ("Att<br>Equipe One Milhas");
            } else if ($scope.flight_selected.airline == "LATAM") {
              $scope.wsalemail.emailContent +=
                "Segue em anexo confirmação de compra Latam, conforme solicitado!<br>";
              "Qualquer situação envolvendo o bilhete, favor entrar em contato: <br>" +
              "E-mail: emissao@onemilhas.com.br<br>" +
                "Tel: (31) 3972-9601<br><br>";
              ("Att<br>Equipe One Milhas");
            }

            $scope.wsalemail.emailContent +=
              "<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Trechos</font></td></tr>" +
              "<tr><td>CIA</td><td>VOO</td><td>Conexões</td><td>Embarque</td><td>Desembarque</td><td>Duração</td><td>Origem</td><td>Destino</td></tr>";
            for (var i = 0; $scope.resume_flights.length > i; i++) {
              var testes = $scope.resume_flights[i].connection.split(" ");
              var connections = "";
              for (var j = 0; j < testes.length; j++) {
                connections += testes[j] + "<br>";
              }
              $scope.wsalemail.emailContent +=
                "<tr><td>" +
                $scope.resume_flights[i].airline +
                "</td><td>" +
                $scope.resume_flights[i].flight +
                "</td><td>" +
                connections +
                "</td><td>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].boarding_date),
                  "dd/MM/yyyy"
                ) +
                "<br>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].boarding_date),
                  "HH:mm:ss"
                ) +
                "</td><td>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].landing_date),
                  "dd/MM/yyyy"
                ) +
                "<br>" +
                $filter("date")(
                  $rootScope.findDate($scope.resume_flights[i].landing_date),
                  "HH:mm:ss"
                ) +
                "</td><td>" +
                $scope.resume_flights[i].flight_time +
                "</td><td>" +
                $scope.resume_flights[i].airport_code_from +
                "</td><td>" +
                $scope.resume_flights[i].airport_code_to +
                "</td></tr>";
            }
            $scope.wsalemail.emailContent +=
              "</tbody></table>" +
              "<br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'>Passageiros</font></td></tr>";
            for (var i = 0; $scope.resume_paxs.length > i; i++) {
              var pax_name = $scope.resume_paxs[i].pax_name;
              if ($scope.resume_paxs[i].paxLastName) {
                pax_name += " " + $scope.resume_paxs[i].paxLastName;
              }
              if ($scope.resume_paxs[i].paxAgnome) {
                pax_name += " " + $scope.resume_paxs[i].paxAgnome;
              }
              $scope.wsalemail.emailContent +=
                "<tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'> Passageiro " +
                (i + 1) +
                " </font></td></tr><tr><td>Nome:</td><td>" +
                pax_name +
                "</td></tr><tr><td>Identificação:</td><td>" +
                $scope.resume_paxs[i].identification +
                "</td></tr><tr><td>Data Nascimento:</td><td>" +
                $filter("date")($scope.resume_paxs[i].birhtdate, "dd/MM/yyyy") +
                "</td></tr>";
              if ($scope.resume_paxs[i].is_child != "N")
                $scope.wsalemail.emailContent +=
                  "<tr><td></td><td>CHD</td></tr>";
              if ($scope.resume_paxs[i].is_newborn != "N")
                $scope.wsalemail.emailContent +=
                  "<tr><td></td><td>INF</td></tr>";
            }
            $scope.wsalemail.emailContent += "</tbody></table><br><br>";
          }

          $scope.previousIndex = $scope.tabindex;
          $scope.tabindex = 5;
          if ($scope.multiDownloads != true) {
            $scope.$apply();
          }
        };

        $scope.checkPaxName = function (name) {
          var listNames = ["JUNIOR", "NETO", "FILHO", "SOBRINHO"];
          return listNames.indexOf(name) > -1;
        };

        $scope.getPaxName = function (name, segundo_nome, field) {
          if ($scope.selected && name) {
            if ($scope.selected.airline) {
              if (
                $scope.selected.airline.indexOf("LATAM") > -1 ||
                $scope.selected.airline.indexOf("AZUL") > -1 ||
                $scope.selected.airline.indexOf("GOL") > -1
              ) {
                var from = $rootScope.airports.filter(function (airport) {
                  return (
                    airport.iataCode ==
                    $scope.onlineflights[0].airport_code_from
                  );
                })[0];
                var to = $rootScope.airports.filter(function (airport) {
                  return (
                    airport.iataCode == $scope.onlineflights[0].airport_code_to
                  );
                })[0];
                var novo_nome = "";
                var nome = name.split(" ");
                for (var i in nome) {
                  if (from && to) {
                    if (from.internacional == "1" || to.internacional == "1") {
                      novo_nome += nome[i];
                    } else {
                      if (
                        (i == "0" && field == "primeiro") ||
                        (i == nome.length - 1 && field == "segundo") ||
                        $scope.checkPaxName(nome[parseInt(i) + 1])
                      ) {
                        novo_nome +=
                          "<font color='#F70909'>" + nome[i] + "</font>";
                      } else if (!segundo_nome && i == nome.length - 1) {
                        novo_nome +=
                          "<font color='#F70909'>" + nome[i] + "</font>";
                      } else {
                        novo_nome += nome[i];
                      }
                    }
                  } else {
                    if (
                      (i == "0" && field == "primeiro") ||
                      (i == nome.length - 1 && field == "segundo") ||
                      $scope.checkPaxName(nome[parseInt(i) + 1])
                    ) {
                      novo_nome +=
                        "<font color='#F70909'>" + nome[i] + "</font>";
                    } else if (!segundo_nome && i == nome.length - 1) {
                      novo_nome +=
                        "<font color='#F70909'>" + nome[i] + "</font>";
                    } else {
                      novo_nome += nome[i];
                    }
                  }
                  if (i != nome.length) {
                    novo_nome += " ";
                  }
                }
                return novo_nome;
              }
            }
          }
          return name;
        };

        $scope.latamNovo = function (flight_selected) {
          if ($scope.getIsReturn($scope.flight_selected)) {
            $scope.returnFlight = $scope.getReturnFlight(
              $scope.flight_selected.airline,
              $scope.flight_selected.cards_id,
              $scope.flight_selected.flight,
              $scope.flight_selected.airport_code_from,
              $scope.flight_selected.airport_code_to,
              $scope.flight_selected.flightLocator
            );
            if ($scope.returnFlight) {
              if (
                $rootScope.findDate($scope.returnFlight.boarding_date) <
                $rootScope.findDate($scope.flight_selected.boarding_date)
              ) {
                $scope.flight_selected = angular.copy($scope.returnFlight);
              }
            }
          }
          $.post(
            "../backend/application/index.php?rota=/loadConnectionsFlight",
            { data: $scope.flight_selected },
            function (result) {
              $scope.connections = jQuery.parseJSON(result).dataset;

              $.post(
                "../backend/application/index.php?rota=/loadConnectionsFlight",
                {
                  hashId: $scope.session.hashId,
                  data: $scope.getReturnFlight(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.cards_id,
                    $scope.flight_selected.flight,
                    $scope.flight_selected.airport_code_from,
                    $scope.flight_selected.airport_code_to,
                    $scope.flight_selected.flightLocator
                  ),
                },
                function (result) {
                  $scope.connectionsReturn = jQuery.parseJSON(result).dataset;

                  $scope.connecty = "";

                  $scope.seats = [];
                  var toTime = $filter("date")(
                    $rootScope.findDate($scope.flight_selected.landing_date),
                    "HH:mm"
                  );
                  var flightTime = $scope.flight_selected.flight_time;

                  $scope.paxLatamConn = $scope.getPaxLaTam(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.cards_id,
                    $scope.flight_selected.flight,
                    $scope.flight_selected.flightLocator
                  );

                  $scope.seat = [];

                  $scope.connect = "";

                  var doc = new jsPDF("p", "pt");
                  var start = 10;

                  var connection = $scope.flight_selected.connection.split(" ");
                  $scope.connectionx = [];

                  connection.forEach((element) => {
                    if (
                      element != "" &&
                      element != "" &&
                      element != " " &&
                      element != " "
                    ) {
                      $scope.connectionx.push(element);
                    }
                  });

                  doc.margin = 0.5;
                  doc.setFont("times");

                  $scope.toDataUrl("images/LATAM2.png", function (base64Img) {
                    $scope.toDataUrl("images/AVIAO.png", function (base32Img) {
                      $scope.toDataUrl("images/ONEWORLD.jpg", function (
                        base16Img
                      ) {
                        $scope.toDataUrl("images/SETA.png", function (
                          base8Img
                        ) {
                          var from = $rootScope.airports.filter(function (
                            airport
                          ) {
                            return (
                              airport.iataCode ==
                              $scope.flight_selected.airport_code_from
                            );
                          })[0];
                          var to = $rootScope.airports.filter(function (
                            airport
                          ) {
                            return (
                              airport.iataCode ==
                              $scope.flight_selected.airport_code_to
                            );
                          })[0];

                          start = start + 60;
                          doc.setFontSize(12);
                          doc.setFontType("bold");
                          var dat = new Date(
                            angular.copy($scope.flight_selected.boarding_date)
                          );
                          var datRet = new Date(
                            angular.copy($scope.flight_selected.landing_date)
                          );
                          doc.text(
                            $filter("date")(
                              $rootScope.findDate(
                                $scope.flight_selected.boarding_date
                              ),
                              "dd"
                            ) +
                              " " +
                              $scope.getLaTamMonth(
                                new Date($scope.flight_selected.boarding_date)
                              ) +
                              " " +
                              dat.getFullYear(),
                            40,
                            start
                          );
                          doc.addImage(base8Img, "JPEG", 120, start - 10, 7, 7);
                          doc.text(
                            $filter("date")(
                              $rootScope.findDate(
                                $scope.flight_selected.landing_date
                              ),
                              "dd"
                            ) +
                              " " +
                              $scope.getLaTamMonth(
                                new Date($scope.flight_selected.landing_date)
                              ) +
                              " " +
                              datRet.getFullYear(),
                            140,
                            start
                          );

                          doc.setFontType("normal");
                          doc.text("Viagem para ", 220, start);
                          doc.setFontType("bold");
                          doc.setFontSize(10);
                          doc.text(
                            $scope.flight_selected.airport_code_to.toUpperCase(),
                            290,
                            start
                          );
                          doc.setFontType("normal");
                          start = start + 5;
                          doc.setDrawColor(32);
                          doc.rect(40, start, 500, 1);
                          start = start + 10;
                          doc.text(40, start, "PREPARADO PARA");
                          start = start + 15;
                          doc.setFontType("bold");
                          doc.setFontSize(12);

                          doc.addImage(
                            base64Img,
                            "JPEG",
                            320,
                            start - 22,
                            160,
                            60
                          );
                          doc.addImage(
                            base16Img,
                            "JPEG",
                            460,
                            start - 12,
                            70,
                            40
                          );

                          $scope.paxLatam = $scope.getPaxLaTam(
                            $scope.flight_selected.airline,
                            $scope.flight_selected.cards_id,
                            $scope.flight_selected.flight,
                            $scope.flight_selected.flightLocator
                          );

                          var group = $filter("groupBy")(
                            $scope.paxLatam,
                            "pax"
                          );

                          const res = Object.keys(group).map(function (key) {
                            return group[key];
                          });

                          var inteRes = new Array();

                          for (var jn in res) {
                            var pax = "",
                              ticket_code = "";
                            var seat = "";

                            pax = res[jn][0].pax;
                            ticket_code = res[jn][0].ticket_code;

                            inteRes.push({
                              pax: pax,
                              seat: seat.toString(),
                              ticket_code: ticket_code,
                            });
                          }

                          for (var i in inteRes) {
                            var element = inteRes[i];

                            doc.text(40, start, element.pax);
                            start = start + 10;
                          }

                          start = start + 10;
                          doc.setFontType("normal");
                          doc.setFontSize(10);
                          doc.text(40, start, "CÓDIGO DA RESERVA");
                          doc.setFontSize(9);
                          doc.text(
                            180,
                            start,
                            $scope.flight_selected.flightLocator
                          );

                          start = start + 5;
                          doc.setDrawColor(32);
                          doc.rect(40, start, 500, 1);

                          start = start + 20;
                          var columns = [{ title: "", dataKey: "name" }];
                          var rows = [];

                          var columns = [
                            { title: "", dataKey: "name" },
                            { title: "", dataKey: "data" },
                            { title: "", dataKey: "hour" },
                          ];
                          var rows = [{ name: "", data: "", hour: "" }];

                          doc.autoTable(columns, rows, {
                            columnStyles: {},
                            styles: {
                              columnWidth: "auto",
                              fontSize: 12,
                            },
                            margin: { top: start - 30, left: 40 },
                            theme: "plain",
                            pageBreak: "avoid",
                            addPageContent: function (data) {
                              doc.setFontSize(12);
                              doc.addImage(
                                base32Img,
                                "JPEG",
                                40,
                                start + 20,
                                20,
                                20
                              );
                              doc.text("SAÍDA: ", 60, start + 30);
                              doc.setFontType("bold");
                              doc.text(
                                $scope.getWeekday(
                                  new Date($scope.flight_selected.boarding_date)
                                ) +
                                  " " +
                                  $filter("date")(
                                    $rootScope.findDate(
                                      $scope.flight_selected.boarding_date
                                    ),
                                    "dd"
                                  ) +
                                  " " +
                                  $scope.getLaTamMonth(
                                    new Date(
                                      $scope.flight_selected.boarding_date
                                    )
                                  ),
                                100,
                                start + 30
                              );
                              doc.setFontType("normal");
                              doc.setFontSize(9);
                              doc.setTextColor(170);
                              doc.text(
                                "Por favor, verifique o horário da decolagem dos vôos.",
                                240,
                                start + 30
                              );
                              doc.setTextColor(0);

                              doc.setFontSize(10);
                              start = start + 40;

                              doc.setDrawColor(224);
                              doc.setFillColor(224, 224, 224);
                              doc.rect(40, start, 500, 190, "FD");

                              doc.setDrawColor(128);
                              doc.setFillColor(255, 255, 255);
                              doc.rect(210, start, 340, 190, "FD");

                              doc.setFontSize(13);
                              doc.setTextColor(32);
                              doc.text(
                                $scope.flight_selected.airport_code_from,
                                220,
                                start + 20
                              );
                              doc.addImage(
                                base8Img,
                                "JPEG",
                                320,
                                start + 15,
                                7,
                                7
                              );
                              doc.text(
                                $scope.flight_selected.airport_code_to,
                                340,
                                start + 20
                              );
                              doc.setTextColor(0);

                              doc.setFontSize(10);
                              doc.setTextColor(96);
                              doc.text(from.city.name, 220, start + 30);
                              doc.text(to.city.name, 340, start + 30);
                              doc.setTextColor(0);
                              var timeBoarding = new Date(
                                $scope.flight_selected.boarding_date
                              );
                              var timeLanding = new Date(
                                $scope.flight_selected.landing_date
                              );
                              doc.setFontSize(10);
                              doc.setTextColor(96);
                              doc.text(
                                "Partindo às (hora local):",
                                220,
                                start + 50
                              );
                              doc.setFontSize(13);
                              doc.setTextColor(32);
                              doc.text(
                                $rootScope.pad(timeBoarding.getHours()) +
                                  ":" +
                                  $rootScope.pad(timeBoarding.getMinutes()),
                                220,
                                start + 70
                              );
                              doc.setFontSize(9);
                              doc.setTextColor(96);
                              doc.text(
                                "Terminal:" + "\n" + "Não disponível",
                                220,
                                start + 90
                              );

                              doc.setTextColor(96);
                              doc.setFontSize(9);
                              doc.text(
                                "Chegando às (hora local):",
                                335,
                                start + 50
                              );
                              doc.setFontSize(13);
                              doc.setTextColor(32);
                              doc.text(
                                $rootScope.pad(timeLanding.getHours()) +
                                  ":" +
                                  $rootScope.pad(timeLanding.getMinutes()),
                                335,
                                start + 70
                              );
                              doc.setFontSize(9);
                              doc.setTextColor(96);
                              doc.text(
                                "Terminal:" + "\n" + "Não disponível",
                                335,
                                start + 90
                              );
                              doc.setTextColor(0);
                              doc.setDrawColor(192, 192, 192); // draw red lines
                              doc.setLineWidth(0.1);
                              doc.line(210, start + 40, 450, start + 40); // horizontal line

                              doc.setDrawColor(192, 192, 192); // draw red lines
                              doc.setLineWidth(0.1);
                              doc.line(330, start + 40, 330, start + 190); // vertical line

                              doc.setDrawColor(192, 192, 192); // draw red lines
                              doc.setLineWidth(0.1);
                              doc.line(450, start, 450, start + 190); // vertical line

                              doc.setFontSize(9);
                              doc.setTextColor(96);
                              doc.text("Aeronave: " + "", 460, start + 10);
                              doc.text(
                                "Distância (em milhas" +
                                  "\n" +
                                  "ORIGEM/DESTINO): " +
                                  "\n" +
                                  "",
                                460,
                                start + 30
                              );
                              doc.text("Escala: " + "", 460, start + 60);
                              doc.text("Refeições: " + "", 460, start + 80);
                              doc.setTextColor(0);
                              doc.setFontSize(12);
                              start = start + 10;
                              doc.text(
                                50,
                                start,
                                "LATAM AIRLINES" + "\n" + "GROUP"
                              );
                              start = start + 30;
                              doc.setFontType("bold");
                              doc.text(
                                50,
                                start,
                                $scope.flight_selected.flight
                              );
                              doc.setFontType("normal");
                              start = start + 10;
                              doc.setFontSize(10);
                              doc.setTextColor(64);
                              start = start + 20;
                              doc.text(
                                50,
                                start,
                                "Operado por\n" +
                                  $scope.flight_selected.airline +
                                  " AIRLINES"
                              );
                              start = start + 30;
                              var fl = $scope.flight_selected.flight_time.split(
                                ":"
                              );
                              doc.text(
                                50,
                                start,
                                "Duração\n" +
                                  fl[0] +
                                  "hr(s)" +
                                  " " +
                                  fl[1] +
                                  "min(s)"
                              );
                              start = start + 30;
                              doc.text(
                                50,
                                start,
                                "Classe\n" + $scope.flight_selected.class
                              );
                              start = start + 30;
                              doc.text(50, start, "Status\n Confirmado");
                              start = start + 40;

                              var columns = [
                                {
                                  title: "Nome do Passageiro",
                                  dataKey: "name",
                                },
                                { title: "Assentos", dataKey: "seat" },
                                {
                                  title:
                                    "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                  dataKey: "ticket",
                                },
                              ];

                              var rows = [];

                              var group = $filter("groupBy")(
                                $scope.paxLatam,
                                "pax"
                              );

                              const resultGroup = Object.keys(group).map(
                                function (key) {
                                  return group[key];
                                }
                              );

                              for (var jn in resultGroup) {
                                rows.push({
                                  name: resultGroup[jn][0].pax,
                                  seat: resultGroup[jn][0].sit,
                                  ticket: resultGroup[jn][0].ticket_code,
                                });
                              }

                              doc.autoTable(columns, rows, {
                                columnStyles: {
                                  name: { columnWidth: 200 },
                                  seat: { columnWidth: 155 },
                                  ticket: { columnWidth: 155 },
                                },
                                showHeader: "firstPage",
                                headerStyles: {
                                  fillColor: [224, 224, 224],
                                  fontSize: 8,
                                },
                                styles: {
                                  fontSize: 8,
                                  textColor: 96,
                                },
                                margin: { top: start, left: 40 },
                                theme: "grid",
                                pageBreak: "avoid",
                              });

                              start = doc.autoTableEndPosY();
                              doc.setDrawColor(64);
                              doc.setLineWidth(2);
                              start = start + 10;
                              doc.line(40, start, 550, start);
                              if (start >= 570) {
                                start = 20;
                                doc.autoTableAddPage();
                              }
                            },
                          });

                          doc.setFontSize(8);

                          start = start + 40;

                          if ($scope.connections.length > 0) {
                            $scope.seat.push($scope.flight_selected.seat);

                            if ($scope.flight_selected.connections) {
                              for (
                                var i = 0;
                                i < $scope.flight_selected.connections.length;
                                i++
                              ) {
                                $scope.seat.push(
                                  $scope.flight_selected.connections[i].seat
                                );
                              }
                            }

                            var classFlight = $scope.flight_selected.class;
                            if (classFlight == "Executiva") {
                              classFlight = "Premium Business ( U )";
                            } else {
                              classFlight += " ( T ) ";
                            }

                            var dateReportDeparture = new Date(
                              $scope.flight_selected.boarding_date
                            );

                            for (
                              var conn = 0;
                              conn < $scope.connections.length;
                              conn++
                            ) {
                              var newboarding = new Date(
                                $scope.connections[conn].boarding
                                  .split(" ")[0]
                                  .split("/")[2] +
                                  "-" +
                                  $scope.connections[conn].boarding
                                    .split(" ")[0]
                                    .split("/")[1] +
                                  "-" +
                                  $scope.connections[conn].boarding
                                    .split(" ")[0]
                                    .split("/")[0] +
                                  " " +
                                  $scope.connections[conn].boarding.split(
                                    " "
                                  )[1]
                              );
                              var newlanding = new Date(
                                $scope.connections[conn].landing
                                  .split(" ")[0]
                                  .split("/")[2] +
                                  "-" +
                                  $scope.connections[conn].landing
                                    .split(" ")[0]
                                    .split("/")[1] +
                                  "-" +
                                  $scope.connections[conn].landing
                                    .split(" ")[0]
                                    .split("/")[0] +
                                  " " +
                                  $scope.connections[conn].landing.split(" ")[1]
                              );
                              $scope.connections[
                                conn
                              ].newboarding = newboarding;
                              $scope.connections[conn].newlanding = newlanding;

                              var from = $rootScope.airports.filter(function (
                                airport
                              ) {
                                return (
                                  airport.iataCode ==
                                  $scope.connections[conn].airportCodeFrom
                                );
                              })[0];
                              var to = $rootScope.airports.filter(function (
                                airport
                              ) {
                                return (
                                  airport.iataCode ==
                                  $scope.connections[conn].airportCodeTo
                                );
                              })[0];

                              dateReportDeparture =
                                $scope.connections[conn].newboarding;
                              $scope.connect = $scope.connectionx[conn];

                              var columns = [
                                { title: "", dataKey: "name" },
                                { title: "", dataKey: "data" },
                                { title: "", dataKey: "hour" },
                              ];
                              var rows = [{ name: "", data: "", hour: "" }];
                              doc.autoTable(columns, rows, {
                                columnStyles: {},
                                styles: {
                                  fontSize: 12,
                                },
                                margin: { top: start, left: 40 },
                                theme: "plain",
                                pageBreak: "avoid",
                                addPageContent: function (data) {
                                  doc.setFontSize(12);
                                  doc.addImage(
                                    base32Img,
                                    "JPEG",
                                    40,
                                    start + 10,
                                    20,
                                    20
                                  );
                                  doc.text("SAÍDA: ", 60, start + 20);
                                  doc.setFontType("bold");
                                  doc.text(
                                    $scope.getWeekday(dateReportDeparture) +
                                      " " +
                                      $filter("date")(
                                        dateReportDeparture,
                                        "dd"
                                      ) +
                                      " " +
                                      $scope.getLaTamMonth(dateReportDeparture),
                                    100,
                                    start + 20
                                  );
                                  doc.setFontType("normal");
                                  doc.setFontSize(9);
                                  doc.setTextColor(160);
                                  doc.text(
                                    "Por favor, verifique o horário da decolagem dos vôos.",
                                    240,
                                    start + 20
                                  );

                                  doc.setFontSize(10);
                                  start = start + 30;

                                  doc.setDrawColor(224);
                                  doc.setFillColor(224, 224, 224);
                                  doc.rect(40, start, 500, 190, "FD");

                                  doc.setDrawColor(128);
                                  doc.setFillColor(255, 255, 255);
                                  doc.rect(200, start, 350, 190, "FD");

                                  doc.setDrawColor(192, 192, 192); // draw red lines
                                  doc.setLineWidth(0.1);
                                  doc.line(200, start + 40, 450, start + 40); // horizontal line

                                  doc.setDrawColor(192, 192, 192); // draw red lines
                                  doc.setLineWidth(0.1);
                                  doc.line(320, start + 40, 320, start + 190); // vertical line

                                  doc.setDrawColor(192, 192, 192); // draw red lines
                                  doc.setLineWidth(0.1);
                                  doc.line(450, start, 450, start + 190); // vertical line
                                  doc.setFontSize(13);
                                  doc.setTextColor(32);
                                  doc.text(
                                    $scope.connections[conn].airportCodeFrom,
                                    210,
                                    start + 20
                                  );
                                  doc.addImage(
                                    base8Img,
                                    "JPEG",
                                    320,
                                    start + 15,
                                    7,
                                    7
                                  );
                                  doc.text(
                                    $scope.connections[conn].airportCodeTo,
                                    340,
                                    start + 20
                                  );
                                  doc.setTextColor(0);
                                  doc.setFontSize(8);

                                  doc.setFontSize(10);
                                  doc.setTextColor(96);
                                  doc.text(from.city.name, 210, start + 30);
                                  doc.text(to.city.name, 340, start + 30);
                                  doc.setTextColor(0);

                                  start = start + 10;

                                  doc.setTextColor(96);
                                  doc.setFontSize(10);
                                  doc.text(
                                    "Partindo às (hora local):",
                                    210,
                                    start + 40
                                  );
                                  doc.setFontSize(13);
                                  doc.setTextColor(32);
                                  doc.text(
                                    $scope.connections[conn].boarding.split(
                                      " "
                                    )[1],
                                    210,
                                    start + 60
                                  );
                                  doc.setFontSize(9);
                                  doc.setTextColor(96);
                                  doc.text(
                                    "Terminal:" + "\n" + "Não disponível",
                                    210,
                                    start + 80
                                  );

                                  doc.text(
                                    "Chegando às (hora local):",
                                    330,
                                    start + 40
                                  );
                                  doc.setFontSize(13);
                                  doc.setTextColor(32);
                                  doc.text(
                                    $scope.connections[conn].landing.split(
                                      " "
                                    )[1],
                                    330,
                                    start + 60
                                  );
                                  doc.setFontSize(9);
                                  doc.setTextColor(96);
                                  doc.text(
                                    "Terminal:" + "\n" + "Não disponível",
                                    330,
                                    start + 80
                                  );
                                  doc.setTextColor(0);

                                  doc.setFontSize(9);
                                  doc.setTextColor(96);
                                  doc.text("Aeronave: " + "", 460, start + 10);
                                  doc.text(
                                    "Distância (em milhas" +
                                      "\n" +
                                      "ORIGEM/DESTINO): " +
                                      "\n" +
                                      "",
                                    460,
                                    start + 30
                                  );
                                  doc.text("Escala: " + "", 460, start + 60);
                                  doc.text("Refeições: " + "", 460, start + 80);
                                  doc.setTextColor(0);

                                  doc.setFontSize(12);
                                  start = start + 10;
                                  doc.text(
                                    50,
                                    start,
                                    "LATAM AIRLINES" + "\n" + "GROUP"
                                  );
                                  start = start + 30;
                                  doc.setFontType("bold");
                                  doc.text(
                                    50,
                                    start,
                                    $scope.connections[conn].flight
                                  );
                                  doc.setFontType("normal");
                                  doc.setFontSize(10);
                                  doc.setTextColor(64);
                                  start = start + 20;
                                  doc.text(
                                    50,
                                    start,
                                    "Operado por\n" +
                                      $scope.flight_selected.airline +
                                      " AIRLINES"
                                  );
                                  start = start + 30;
                                  var fl = $scope.connections[
                                    conn
                                  ].flightTime.split(":");
                                  doc.text(
                                    50,
                                    start,
                                    "Duração\n" +
                                      fl[0] +
                                      "hr(s)" +
                                      " " +
                                      fl[1] +
                                      "min(s)"
                                  );
                                  start = start + 30;
                                  doc.text(
                                    50,
                                    start,
                                    "Classe\n" + $scope.flight_selected.class
                                  );
                                  start = start + 30;
                                  doc.text(50, start, "Status\n Confirmado");
                                  start = start + 40;

                                  var fullName =
                                    $scope.flight_selected.pax_name +
                                    " " +
                                    $scope.flight_selected.paxLastName;
                                  var columns = [
                                    {
                                      title: "Nome do Passageiro",
                                      dataKey: "name",
                                    },
                                    { title: "Assentos", dataKey: "seat" },
                                    {
                                      title:
                                        "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                      dataKey: "ticket",
                                    },
                                  ];

                                  var rows = [];

                                  var lets = $filter("groupBy")(
                                    $scope.paxLatamConn,
                                    "pax"
                                  );

                                  const results = Object.keys(lets).map(
                                    function (key) {
                                      return lets[key];
                                    }
                                  );

                                  var inter = new Array();

                                  for (var j in results) {
                                    var seat = new Array();
                                    var pax = "",
                                      ticket_code = "";

                                    for (var f in results[j]) {
                                      pax = results[j][f].pax;
                                      ticket_code = results[j][f].ticket_code;
                                      seat.push(results[j][f].sit);
                                    }

                                    inter.push({
                                      pax: pax,
                                      seat: seat,
                                      ticket_code: ticket_code,
                                    });
                                  }

                                  for (var i in inter) {
                                    var element = inter[i];
                                    rows.push({
                                      name: element.pax,
                                      seat: element.seat[conn],
                                      ticket: element.ticket_code,
                                    });
                                  }

                                  doc.autoTable(columns, rows, {
                                    startY: start,
                                    pageBreak: "auto",
                                    margin: { left: 40 },
                                    theme: "grid",
                                    showHeader: "firstPage",
                                    columnStyles: {
                                      name: { columnWidth: 200 },
                                      seat: { columnWidth: 155 },
                                      ticket: { columnWidth: 155 },
                                    },
                                    styles: {
                                      overflow: "linebreak",
                                      overflowColumns: false,
                                      fontSize: 8,
                                      textColor: 96,
                                    },
                                    headerStyles: {
                                      fillColor: [224, 224, 224],
                                      fontSize: 8,
                                    },
                                    createdCell: function (cell, data) {},
                                  });

                                  doc.setDrawColor(64);
                                  doc.setLineWidth(2);
                                  start = doc.autoTableEndPosY();
                                  start = start + 10;

                                  doc.line(40, start, 550, start);
                                  start = start + 20;
                                  if (start >= 610) {
                                    start = 60;
                                    doc.autoTableAddPage();
                                  }
                                },
                                drawCell: function (cell, data) {},
                              });
                            }
                          }

                          if ($scope.getIsReturn($scope.flight_selected)) {
                            $scope.returnFlight = $scope.getReturnFlight(
                              $scope.flight_selected.airline,
                              $scope.flight_selected.cards_id,
                              $scope.flight_selected.flight,
                              $scope.flight_selected.airport_code_from,
                              $scope.flight_selected.airport_code_to,
                              $scope.flight_selected.flightLocator
                            );
                            $scope.Return = angular.copy($scope.returnFlight);
                            var from = $rootScope.airports.filter(function (
                              airport
                            ) {
                              return (
                                airport.iataCode ==
                                $scope.returnFlight.airport_code_from
                              );
                            })[0];

                            var to = $rootScope.airports.filter(function (
                              airport
                            ) {
                              return (
                                airport.iataCode ==
                                $scope.returnFlight.airport_code_to
                              );
                            })[0];

                            if ($scope.flight_selected.seat) {
                              var seat = $scope.flight_selected.seat;
                            } else {
                              var seat = "";
                            }
                            start = start + 10;

                            var columns = [
                              { title: "", dataKey: "name" },
                              { title: "", dataKey: "data" },
                              { title: "", dataKey: "hour" },
                            ];
                            var rows = [{ name: "", data: "", hour: "" }];

                            doc.autoTable(columns, rows, {
                              columnStyles: {},
                              styles: {
                                fontSize: 12,
                              },
                              margin: { top: start, left: 40 },
                              theme: "plain",
                              pageBreak: "avoid",
                              addPageContent: function (data) {
                                doc.setFontSize(12);
                                doc.addImage(
                                  base32Img,
                                  "JPEG",
                                  40,
                                  start + 20,
                                  20,
                                  20
                                );
                                doc.text("SAÍDA: ", 60, start + 30);
                                doc.setFontType("bold");
                                doc.text(
                                  $scope.getWeekday(
                                    new Date($scope.returnFlight.boarding_date)
                                  ) +
                                    " " +
                                    $filter("date")(
                                      new Date(
                                        $scope.returnFlight.boarding_date
                                      ),
                                      "dd"
                                    ) +
                                    " " +
                                    $scope.getLaTamMonth(
                                      new Date(
                                        $scope.returnFlight.boarding_date
                                      )
                                    ),
                                  100,
                                  start + 30
                                );
                                doc.setFontType("normal");
                                doc.setFontSize(9);
                                doc.setTextColor(160);
                                doc.text(
                                  "Por favor, verifique o horário da decolagem dos vôos.",
                                  240,
                                  start + 30
                                );

                                doc.setFontSize(10);
                                start = start + 40;

                                doc.setDrawColor(224);
                                doc.setFillColor(224, 224, 224);
                                doc.rect(40, start, 500, 190, "FD");

                                doc.setDrawColor(128);
                                doc.setFillColor(255, 255, 255);
                                doc.rect(200, start, 350, 190, "FD");

                                doc.setDrawColor(192, 192, 192); // draw red lines
                                doc.setLineWidth(0.1);
                                doc.line(200, start + 40, 450, start + 40); // horizontal line

                                doc.setDrawColor(192, 192, 192); // draw red lines
                                doc.setLineWidth(0.1);
                                doc.line(320, start + 40, 320, start + 190); // vertical line

                                doc.setDrawColor(192, 192, 192); // draw red lines
                                doc.setLineWidth(0.1);
                                doc.line(450, start, 450, start + 190); // vertical line

                                doc.setFontSize(13);
                                doc.setTextColor(32);
                                doc.text(
                                  $scope.returnFlight.airport_code_from,
                                  210,
                                  start + 20
                                );
                                doc.addImage(
                                  base8Img,
                                  "JPEG",
                                  320,
                                  start + 15,
                                  7,
                                  7
                                );
                                doc.text(
                                  $scope.returnFlight.airport_code_to,
                                  340,
                                  start + 20
                                );
                                doc.setTextColor(0);
                                doc.setFontSize(8);
                                start = start + 10;

                                doc.setFontSize(10);
                                doc.setTextColor(96);
                                doc.text(from.city.name, 210, start + 20);
                                doc.text(to.city.name, 340, start + 20);
                                doc.setTextColor(0);
                                var timeBoarding = new Date(
                                  $scope.returnFlight.boarding_date
                                );
                                var timeLanding = new Date(
                                  $scope.returnFlight.landing_date
                                );

                                doc.setTextColor(96);
                                doc.setFontSize(10);
                                doc.text(
                                  "Partindo às (hora local):",
                                  210,
                                  start + 40
                                );
                                doc.setFontSize(13);
                                doc.setTextColor(32);
                                doc.text(
                                  $rootScope.pad(timeBoarding.getHours()) +
                                    ":" +
                                    $rootScope.pad(timeBoarding.getMinutes()),
                                  210,
                                  start + 60
                                );
                                doc.setFontSize(9);
                                doc.setTextColor(96);
                                doc.text(
                                  "Terminal:" + "\n" + "Não disponível",
                                  210,
                                  start + 80
                                );

                                doc.text(
                                  "Chegando às (hora local):",
                                  330,
                                  start + 40
                                );
                                doc.setFontSize(13);
                                doc.setTextColor(32);
                                doc.text(
                                  $rootScope.pad(timeLanding.getHours()) +
                                    ":" +
                                    $rootScope.pad(timeLanding.getMinutes()),
                                  330,
                                  start + 60
                                );
                                doc.setFontSize(9);
                                doc.setTextColor(96);
                                doc.text(
                                  "Terminal:" + "\n" + "Não disponível",
                                  330,
                                  start + 80
                                );
                                doc.setTextColor(0);

                                doc.setFontSize(9);
                                doc.setTextColor(96);
                                doc.text("Aeronave: " + "", 460, start + 10);
                                doc.text(
                                  "Distância (em milhas" +
                                    "\n" +
                                    "ORIGEM/DESTINO): " +
                                    "\n" +
                                    "",
                                  460,
                                  start + 30
                                );
                                doc.text("Escala: " + "", 460, start + 60);
                                doc.text("Refeições: " + "", 460, start + 80);
                                doc.setTextColor(0);

                                doc.setFontSize(12);
                                start = start + 20;
                                doc.text(
                                  50,
                                  start,
                                  "LATAM AIRLINES" + "\n" + "GROUP"
                                );
                                start = start + 30;
                                doc.setFontType("bold");
                                doc.text(50, start, $scope.returnFlight.flight);
                                doc.setFontType("normal");
                                doc.setFontSize(10);
                                doc.setTextColor(64);
                                start = start + 20;
                                doc.text(
                                  50,
                                  start,
                                  "Operado por\n" +
                                    $scope.returnFlight.airline +
                                    " AIRLINES"
                                );
                                start = start + 30;
                                var fl = $scope.returnFlight.flight_time.split(
                                  ":"
                                );
                                doc.text(
                                  50,
                                  start,
                                  "Duração\n" +
                                    fl[0] +
                                    "hr(s)" +
                                    " " +
                                    fl[1] +
                                    "min(s)"
                                );
                                start = start + 30;
                                doc.text(
                                  50,
                                  start,
                                  "Classe\n" + $scope.returnFlight.class
                                );
                                start = start + 30;
                                doc.text(50, start, "Status\n Confirmado");
                                start = start + 30;

                                var fullName =
                                  $scope.returnFlight.pax_name +
                                  " " +
                                  $scope.returnFlight.paxLastName;

                                var columns = [
                                  {
                                    title: "Nome do Passageiro",
                                    dataKey: "name",
                                  },
                                  { title: "Assentos", dataKey: "seat" },
                                  {
                                    title:
                                      "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                    dataKey: "ticket",
                                  },
                                ];

                                var rows = [];

                                $scope.paxReturn = $scope.getPaxLaTamReturn(
                                  $scope.returnFlight.airline,
                                  $scope.returnFlight.cards_id,
                                  $scope.returnFlight.flight,
                                  $scope.returnFlight.flightLocator
                                );

                                var letsR = $filter("groupBy")(
                                  $scope.paxReturn,
                                  "pax"
                                );

                                const resultsRet = Object.keys(letsR).map(
                                  function (key) {
                                    return letsR[key];
                                  }
                                );

                                var inteR = new Array();

                                for (var jn in resultsRet) {
                                  var seatReturn = new Array();
                                  var pax = "",
                                    ticket_code = "";
                                  for (var fn in resultsRet[jn]) {
                                    pax = resultsRet[jn][fn].pax;
                                    ticket_code =
                                      resultsRet[jn][fn].ticket_code;

                                    seatReturn.push(resultsRet[jn][fn].sit);
                                  }

                                  inteR.push({
                                    pax: pax,
                                    seat: seatReturn,
                                    ticket_code: ticket_code,
                                  });
                                }

                                for (var i in inteR) {
                                  var element = inteR[i];
                                  rows.push({
                                    name: element.pax,
                                    seat: element.seat[0],
                                    ticket: element.ticket_code,
                                  });
                                }

                                doc.autoTable(columns, rows, {
                                  columnStyles: {
                                    name: { columnWidth: 200 },
                                    seat: { columnWidth: 155 },
                                    ticket: { columnWidth: 155 },
                                  },
                                  showHeader: "firstPage",
                                  headerStyles: {
                                    fillColor: [224, 224, 224],
                                    fontSize: 8,
                                  },
                                  styles: {
                                    fontSize: 8,
                                    textColor: 96,
                                  },
                                  startY: start,
                                  margin: { left: 40 },
                                  theme: "grid",
                                  pageBreak: "avoid",
                                });

                                doc.setDrawColor(64);
                                doc.setLineWidth(2);

                                start = doc.autoTableEndPosY();
                                start = start + 10;
                                doc.line(40, start, 550, start);
                                start = start + 20;
                              },
                            });
                            if (start >= 610) {
                              start = 20;
                              doc.autoTableAddPage();
                            }
                            if ($scope.connectionsReturn.length > 0) {
                              $scope.seats.push($scope.returnFlight.seat);

                              if ($scope.returnFlight.connections) {
                                for (
                                  var i = 0;
                                  i < $scope.returnFlight.connections.length;
                                  i++
                                ) {
                                  $scope.seats.push(
                                    $scope.returnFlight.connections[i].seat
                                  );
                                }
                              }

                              var ConnectionsReturn = $scope.connectionsReturn;
                              var classFlight = $scope.returnFlight.class;

                              if (classFlight == "Executiva") {
                                classFlight = "Premium Business ( U )";
                              } else {
                                classFlight += " ( T ) ";
                              }

                              var dateReportReturn = new Date(
                                $scope.Return.boarding_date
                              );
                              //console.log("dateReportReturn", dateReportReturn);
                              var counts = 0;

                              const board = new Date(
                                $scope.Return.boarding_date
                              );

                              $scope.daysReturn = new Array();

                              for (var connection in $scope.connectionsReturn) {
                                var newboarding = new Date(
                                  $scope.connectionsReturn[connection].boarding
                                    .split(" ")[0]
                                    .split("/")[2] +
                                    "-" +
                                    $scope.connectionsReturn[
                                      connection
                                    ].boarding
                                      .split(" ")[0]
                                      .split("/")[1] +
                                    "-" +
                                    $scope.connectionsReturn[
                                      connection
                                    ].boarding
                                      .split(" ")[0]
                                      .split("/")[0] +
                                    " " +
                                    $scope.connectionsReturn[
                                      connection
                                    ].boarding.split(" ")[1]
                                );
                                var newlanding = new Date(
                                  $scope.connectionsReturn[connection].landing
                                    .split(" ")[0]
                                    .split("/")[2] +
                                    "-" +
                                    $scope.connectionsReturn[connection].landing
                                      .split(" ")[0]
                                      .split("/")[1] +
                                    "-" +
                                    $scope.connectionsReturn[connection].landing
                                      .split(" ")[0]
                                      .split("/")[0] +
                                    " " +
                                    $scope.connectionsReturn[
                                      connection
                                    ].landing.split(" ")[1]
                                );
                                $scope.connectionsReturn[
                                  connection
                                ].newboarding = newboarding;
                                $scope.connectionsReturn[
                                  connection
                                ].newlanding = newlanding;

                                var from = $rootScope.airports.filter(function (
                                  airport
                                ) {
                                  return (
                                    airport.iataCode ==
                                    $scope.connectionsReturn[connection]
                                      .airportCodeFrom
                                  );
                                })[0];
                                var to = $rootScope.airports.filter(function (
                                  airport
                                ) {
                                  return (
                                    airport.iataCode ==
                                    $scope.connectionsReturn[connection]
                                      .airportCodeTo
                                  );
                                })[0];

                                dateReportReturn =
                                  $scope.connectionsReturn[connection]
                                    .newboarding;

                                counts++;
                                var columns = [
                                  { title: "", dataKey: "name" },
                                  { title: "", dataKey: "data" },
                                  { title: "", dataKey: "hour" },
                                ];
                                var rows = [{ name: "", data: "", hour: "" }];
                                start = start + 10;

                                doc.autoTable(columns, rows, {
                                  columnStyles: {},
                                  styles: {
                                    fontSize: 12,
                                  },
                                  margin: { top: start, left: 40 },
                                  theme: "plain",
                                  pageBreak: "avoid",
                                  addPageContent: function (data) {
                                    doc.setFontSize(12);
                                    doc.addImage(
                                      base32Img,
                                      "JPEG",
                                      40,
                                      start + 20,
                                      20,
                                      20
                                    );
                                    doc.text("SAÍDA: ", 60, start + 30);
                                    doc.setFontType("bold");
                                    doc.text(
                                      $scope.getWeekday(dateReportReturn) +
                                        " " +
                                        $filter("date")(
                                          new Date(dateReportReturn),
                                          "dd"
                                        ) +
                                        " " +
                                        $scope.getLaTamMonth(dateReportReturn),
                                      100,
                                      start + 30
                                    );
                                    doc.setFontType("normal");
                                    doc.setFontSize(9);
                                    doc.setTextColor(160);
                                    doc.text(
                                      "Por favor, verifique o horário da decolagem dos vôos.",
                                      240,
                                      start + 30
                                    );

                                    doc.setFontSize(10);
                                    start = start + 40;

                                    doc.setDrawColor(224);
                                    doc.setFillColor(224, 224, 224);
                                    doc.rect(40, start, 500, 190, "FD");

                                    doc.setDrawColor(128);
                                    doc.setFillColor(255, 255, 255);
                                    doc.rect(200, start, 350, 190, "FD");

                                    doc.setDrawColor(192, 192, 192); // draw red lines
                                    doc.setLineWidth(0.1);
                                    doc.line(200, start + 40, 450, start + 40); // horizontal line

                                    doc.setDrawColor(192, 192, 192); // draw red lines
                                    doc.setLineWidth(0.1);
                                    doc.line(320, start + 40, 320, start + 190); // vertical line

                                    doc.setDrawColor(192, 192, 192); // draw red lines
                                    doc.setLineWidth(0.1);
                                    doc.line(450, start, 450, start + 190); // vertical line

                                    doc.setFontSize(13);
                                    doc.setTextColor(32);
                                    doc.text(
                                      $scope.connectionsReturn[connection]
                                        .airportCodeFrom,
                                      210,
                                      start + 20
                                    );
                                    doc.addImage(
                                      base8Img,
                                      "JPEG",
                                      320,
                                      start + 15,
                                      7,
                                      7
                                    );
                                    doc.text(
                                      $scope.connectionsReturn[connection]
                                        .airportCodeTo,
                                      340,
                                      start + 20
                                    );
                                    doc.setTextColor(0);
                                    doc.setFontSize(8);

                                    doc.setFontSize(10);
                                    doc.setTextColor(96);
                                    start = start + 10;
                                    doc.text(from.city.name, 210, start + 20);
                                    doc.text(to.city.name, 340, start + 20);

                                    doc.setTextColor(96);
                                    doc.setFontSize(10);
                                    doc.text(
                                      "Partindo às (hora local):",
                                      210,
                                      start + 40
                                    );
                                    doc.setFontSize(13);
                                    doc.setTextColor(32);
                                    $scope.connectionsReturn[
                                      connection
                                    ].newboarding = new Date(
                                      $scope.connectionsReturn[
                                        connection
                                      ].newboarding
                                    );
                                    doc.text(
                                      $scope.connectionsReturn[
                                        connection
                                      ].boarding.split(" ")[1],
                                      210,
                                      start + 60
                                    );
                                    doc.setFontSize(9);
                                    doc.setTextColor(96);
                                    doc.text(
                                      "Terminal:" + "\n" + "Não disponível",
                                      210,
                                      start + 80
                                    );

                                    doc.text(
                                      "Chegando às (hora local):",
                                      330,
                                      start + 40
                                    );
                                    doc.setFontSize(13);
                                    doc.setTextColor(32);
                                    $scope.connectionsReturn[
                                      connection
                                    ].newlanding = new Date(
                                      $scope.connectionsReturn[
                                        connection
                                      ].newlanding
                                    );
                                    doc.text(
                                      $scope.connectionsReturn[
                                        connection
                                      ].landing.split(" ")[1],
                                      330,
                                      start + 60
                                    );
                                    doc.setFontSize(9);
                                    doc.setTextColor(96);
                                    doc.text(
                                      "Terminal:" + "\n" + "Não disponível",
                                      330,
                                      start + 80
                                    );
                                    doc.setTextColor(0);

                                    doc.setTextColor(0);

                                    doc.setFontSize(9);
                                    doc.setTextColor(96);
                                    doc.text(
                                      "Aeronave: " + "",
                                      460,
                                      start + 10
                                    );
                                    doc.text(
                                      "Distância (em milhas" +
                                        "\n" +
                                        "ORIGEM/DESTINO): " +
                                        "\n" +
                                        "",
                                      460,
                                      start + 30
                                    );
                                    doc.text("Escala: " + "", 460, start + 60);
                                    doc.text(
                                      "Refeições: " + "",
                                      460,
                                      start + 80
                                    );
                                    doc.setTextColor(0);

                                    doc.setFontSize(12);
                                    doc.text(
                                      50,
                                      start,
                                      "LATAM AIRLINES" + "\n" + "GROUP"
                                    );
                                    start = start + 30;
                                    doc.setFontType("bold");
                                    doc.text(
                                      50,
                                      start,
                                      $scope.connectionsReturn[connection]
                                        .flight
                                    );
                                    doc.setFontType("normal");
                                    doc.setFontSize(10);
                                    doc.setTextColor(64);
                                    start = start + 20;
                                    doc.text(
                                      50,
                                      start,
                                      "Operado por\n" +
                                        $scope.Return.airline +
                                        " AIRLINES"
                                    );
                                    start = start + 30;
                                    var fl = $scope.connectionsReturn[
                                      connection
                                    ].flightTime.split(":");
                                    doc.text(
                                      50,
                                      start,
                                      "Duração\n" +
                                        fl[0] +
                                        "hr(s)" +
                                        " " +
                                        fl[1] +
                                        "min(s)"
                                    );
                                    start = start + 30;
                                    doc.text(
                                      50,
                                      start,
                                      "Classe\n" + $scope.Return.class
                                    );
                                    start = start + 30;
                                    doc.text(50, start, "Status\n Confirmado");
                                    start = start + 40;
                                    doc.setFontSize(8);

                                    start = start + 10;

                                    var columns = [
                                      {
                                        title: "Nome do Passageiro",
                                        dataKey: "name",
                                      },
                                      { title: "Assentos", dataKey: "seat" },
                                      {
                                        title:
                                          "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                        dataKey: "ticket",
                                      },
                                    ];

                                    var rows = [];

                                    var lets = $filter("groupBy")(
                                      $scope.paxReturn,
                                      "pax"
                                    );

                                    const resultsReturn = Object.keys(lets).map(
                                      function (key) {
                                        return lets[key];
                                      }
                                    );

                                    var inter = new Array();

                                    for (var jn in resultsReturn) {
                                      var seatReturn = new Array();
                                      var pax = "",
                                        ticket_code = "";

                                      for (var fn in resultsReturn[jn]) {
                                        pax = resultsReturn[jn][fn].pax;
                                        ticket_code =
                                          resultsReturn[jn][fn].ticket_code;
                                        seatReturn.push(
                                          resultsReturn[jn][fn].sit
                                        );
                                      }

                                      inter.push({
                                        pax: pax,
                                        seat: seatReturn,
                                        ticket_code: ticket_code,
                                      });
                                    }

                                    for (var i in inter) {
                                      var element = inter[i];
                                      rows.push({
                                        name: element.pax,
                                        seat: element.seat[connection],
                                        ticket: element.ticket_code,
                                      });
                                    }

                                    doc.autoTable(columns, rows, {
                                      columnStyles: {
                                        name: { columnWidth: 200 },
                                        seat: { columnWidth: 155 },
                                        ticket: { columnWidth: 155 },
                                      },
                                      showHeader: "firstPage",
                                      headerStyles: {
                                        fillColor: [224, 224, 224],
                                        fontSize: 8,
                                      },
                                      styles: {
                                        fontSize: 8,
                                        textColor: 96,
                                      },
                                      startY: start,
                                      margin: { left: 40 },
                                      theme: "grid",
                                      pageBreak: "avoid",
                                    });
                                    doc.setDrawColor(64);
                                    doc.setLineWidth(2);

                                    start = doc.autoTableEndPosY();
                                    start = start + 10;
                                    doc.line(40, start, 550, start);
                                    start = start + 20;
                                  },
                                  drawCell: function (cell, data) {},
                                });

                                if (start > 610) {
                                  doc.autoTableAddPage();
                                  start = 60;
                                  counts = 0;
                                }
                              }
                            }
                          }

                          if ($scope.flight_selected.isBreak == "Quebrado") {
                            return logger.logError(
                              "Este trecho esta quebrado!"
                            );
                          }
                          doc.save(
                            $scope.flight_selected.flightLocator + ".pdf"
                          );
                          if ($scope.multiDownloads !== true) {
                            if ($scope.response) {
                              if (
                                $scope.response.notificationurl != null &&
                                $scope.response.notificationurl.indexOf(
                                  "http"
                                ) == -1
                              ) {
                                $scope.fillEmailBillet();
                              }
                            } else {
                              $scope.fillEmailBillet();
                            }
                          }
                        });
                      });
                    });
                  });
                }
              );
            }
          );
        };

        $scope.latamVelho = function (flight_selected) {
          if ($scope.getIsReturn($scope.flight_selected)) {
            $scope.returnFlight = $scope.getReturnFlight(
              $scope.flight_selected.airline,
              $scope.flight_selected.cards_id,
              $scope.flight_selected.flight,
              $scope.flight_selected.airport_code_from,
              $scope.flight_selected.airport_code_to,
              $scope.flight_selected.flightLocator
            );
            if ($scope.returnFlight) {
              if (
                $rootScope.findDate($scope.returnFlight.boarding_date) <
                $rootScope.findDate($scope.flight_selected.boarding_date)
              ) {
                $scope.flight_selected = angular.copy($scope.returnFlight);
              }
            }
          }
          $.post(
            "../backend/application/index.php?rota=/loadConnectionsFlight",
            { data: $scope.flight_selected },
            function (result) {
              $scope.connections = jQuery.parseJSON(result).dataset;

              $.post(
                "../backend/application/index.php?rota=/loadConnectionsFlight",
                {
                  hashId: $scope.session.hashId,
                  data: $scope.getReturnFlight(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.cards_id,
                    $scope.flight_selected.flight,
                    $scope.flight_selected.airport_code_from,
                    $scope.flight_selected.airport_code_to,
                    $scope.flight_selected.flightLocator
                  ),
                },
                function (result) {
                  $scope.connectionsReturn = jQuery.parseJSON(result).dataset;

                  $scope.connecty = "";

                  $scope.seats = [];
                  var toTime = $filter("date")(
                    $rootScope.findDate($scope.flight_selected.landing_date),
                    "HH:mm"
                  );
                  var flightTime = $scope.flight_selected.flight_time;

                  $scope.paxLatamConn = $scope.getPaxLaTam(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.cards_id,
                    $scope.flight_selected.flight,
                    $scope.flight_selected.flightLocator
                  );

                  $scope.seat = [];

                  $scope.connect = "";

                  var doc = new jsPDF("p", "pt");
                  var start = 10;

                  var connection = $scope.flight_selected.connection.split(" ");
                  $scope.connectionx = [];

                  connection.forEach((element) => {
                    if (
                      element != "" &&
                      element != "" &&
                      element != " " &&
                      element != " "
                    ) {
                      $scope.connectionx.push(element);
                    }
                  });

                  doc.margin = 0.5;
                  doc.setFont("times");

                  $scope.toDataUrl("images/LATAM2.png", function (base64Img) {
                    $scope.toDataUrl("images/AVIAO.png", function (base32Img) {
                      $scope.toDataUrl("images/ONEWORLD.jpg", function (
                        base16Img
                      ) {
                        $scope.toDataUrl("images/SETA.png", function (
                          base8Img
                        ) {
                          var from = $rootScope.airports.filter(function (
                            airport
                          ) {
                            return (
                              airport.iataCode ==
                              $scope.flight_selected.airport_code_from
                            );
                          })[0];
                          var to = $rootScope.airports.filter(function (
                            airport
                          ) {
                            return (
                              airport.iataCode ==
                              $scope.flight_selected.airport_code_to
                            );
                          })[0];

                          start = start + 60;
                          doc.setFontSize(12);
                          doc.setFontType("bold");
                          var dat = new Date(
                            angular.copy($scope.flight_selected.boarding_date)
                          );
                          var datRet = new Date(
                            angular.copy($scope.flight_selected.landing_date)
                          );
                          doc.text(
                            $filter("date")(
                              $rootScope.findDate(
                                $scope.flight_selected.boarding_date
                              ),
                              "dd"
                            ) +
                              " " +
                              $scope.getLaTamMonth(
                                new Date($scope.flight_selected.boarding_date)
                              ) +
                              " " +
                              dat.getFullYear(),
                            40,
                            start
                          );
                          doc.addImage(base8Img, "JPEG", 120, start - 10, 7, 7);
                          doc.text(
                            $filter("date")(
                              $rootScope.findDate(
                                $scope.flight_selected.landing_date
                              ),
                              "dd"
                            ) +
                              " " +
                              $scope.getLaTamMonth(
                                new Date($scope.flight_selected.landing_date)
                              ) +
                              " " +
                              datRet.getFullYear(),
                            140,
                            start
                          );

                          doc.setFontType("normal");
                          doc.text("Viagem para ", 220, start);
                          doc.setFontType("bold");
                          doc.setFontSize(10);
                          doc.text(
                            $scope.flight_selected.airport_code_to.toUpperCase(),
                            290,
                            start
                          );
                          doc.setFontType("normal");
                          start = start + 5;
                          doc.setDrawColor(32);
                          doc.rect(40, start, 500, 1);
                          start = start + 10;
                          doc.text(40, start, "PREPARADO PARA");
                          start = start + 15;
                          doc.setFontType("bold");
                          doc.setFontSize(12);

                          doc.addImage(
                            base64Img,
                            "JPEG",
                            320,
                            start - 22,
                            160,
                            60
                          );
                          doc.addImage(
                            base16Img,
                            "JPEG",
                            460,
                            start - 12,
                            70,
                            40
                          );

                          $scope.paxLatam = $scope.getPaxLaTam(
                            $scope.flight_selected.airline,
                            $scope.flight_selected.cards_id,
                            $scope.flight_selected.flight,
                            $scope.flight_selected.flightLocator
                          );

                          var group = $filter("groupBy")(
                            $scope.paxLatam,
                            "pax"
                          );

                          const res = Object.keys(group).map(function (key) {
                            return group[key];
                          });

                          var inteRes = new Array();

                          for (var jn in res) {
                            var pax = "",
                              ticket_code = "";
                            var seat = "";

                            pax = res[jn][0].pax;
                            ticket_code = res[jn][0].ticket_code;

                            inteRes.push({
                              pax: pax,
                              seat: seat.toString(),
                              ticket_code: ticket_code,
                            });
                          }

                          for (var i in inteRes) {
                            var element = inteRes[i];

                            doc.text(40, start, element.pax);
                            start = start + 10;
                          }

                          start = start + 10;
                          doc.setFontType("normal");
                          doc.setFontSize(10);
                          doc.text(40, start, "CÓDIGO DA RESERVA");
                          doc.setFontSize(9);
                          doc.text(
                            180,
                            start,
                            $scope.flight_selected.flightLocator
                          );

                          start = start + 5;
                          doc.setDrawColor(32);
                          doc.rect(40, start, 500, 1);

                          start = start + 20;
                          var columns = [{ title: "", dataKey: "name" }];
                          var rows = [];

                          var columns = [
                            { title: "", dataKey: "name" },
                            { title: "", dataKey: "data" },
                            { title: "", dataKey: "hour" },
                          ];
                          var rows = [{ name: "", data: "", hour: "" }];

                          doc.autoTable(columns, rows, {
                            columnStyles: {},
                            styles: {
                              columnWidth: "auto",
                              fontSize: 12,
                            },
                            margin: { top: start - 30, left: 40 },
                            theme: "plain",
                            pageBreak: "avoid",
                            addPageContent: function (data) {
                              doc.setFontSize(12);
                              doc.addImage(
                                base32Img,
                                "JPEG",
                                40,
                                start + 20,
                                20,
                                20
                              );
                              doc.text("SAÍDA: ", 60, start + 30);
                              doc.setFontType("bold");
                              doc.text(
                                $scope.getWeekday(
                                  new Date($scope.flight_selected.boarding_date)
                                ) +
                                  " " +
                                  $filter("date")(
                                    $rootScope.findDate(
                                      $scope.flight_selected.boarding_date
                                    ),
                                    "dd"
                                  ) +
                                  " " +
                                  $scope.getLaTamMonth(
                                    new Date(
                                      $scope.flight_selected.boarding_date
                                    )
                                  ),
                                100,
                                start + 30
                              );
                              doc.setFontType("normal");
                              doc.setFontSize(9);
                              doc.setTextColor(170);
                              doc.text(
                                "Por favor, verifique o horário da decolagem dos vôos.",
                                240,
                                start + 30
                              );
                              doc.setTextColor(0);

                              doc.setFontSize(10);
                              start = start + 40;

                              doc.setDrawColor(224);
                              doc.setFillColor(224, 224, 224);
                              doc.rect(40, start, 500, 190, "FD");

                              doc.setDrawColor(128);
                              doc.setFillColor(255, 255, 255);
                              doc.rect(210, start, 340, 190, "FD");

                              doc.setFontSize(13);
                              doc.setTextColor(32);
                              doc.text(
                                $scope.flight_selected.airport_code_from,
                                220,
                                start + 20
                              );
                              doc.addImage(
                                base8Img,
                                "JPEG",
                                320,
                                start + 15,
                                7,
                                7
                              );
                              doc.text(
                                $scope.flight_selected.airport_code_to,
                                340,
                                start + 20
                              );
                              doc.setTextColor(0);

                              doc.setFontSize(10);
                              doc.setTextColor(96);
                              doc.text(from.city.name, 220, start + 30);
                              doc.text(to.city.name, 340, start + 30);
                              doc.setTextColor(0);
                              var timeBoarding = new Date(
                                $scope.flight_selected.boarding_date
                              );
                              var timeLanding = new Date(
                                $scope.flight_selected.landing_date
                              );
                              doc.setFontSize(10);
                              doc.setTextColor(96);
                              doc.text(
                                "Partindo às (hora local):",
                                220,
                                start + 50
                              );
                              doc.setFontSize(13);
                              doc.setTextColor(32);
                              doc.text(
                                $rootScope.pad(timeBoarding.getHours()) +
                                  ":" +
                                  $rootScope.pad(timeBoarding.getMinutes()),
                                220,
                                start + 70
                              );
                              doc.setFontSize(9);
                              doc.setTextColor(96);
                              doc.text(
                                "Terminal:" + "\n" + "Não disponível",
                                220,
                                start + 90
                              );

                              doc.setTextColor(96);
                              doc.setFontSize(9);
                              doc.text(
                                "Chegando às (hora local):",
                                335,
                                start + 50
                              );
                              doc.setFontSize(13);
                              doc.setTextColor(32);
                              doc.text(
                                $rootScope.pad(timeLanding.getHours()) +
                                  ":" +
                                  $rootScope.pad(timeLanding.getMinutes()),
                                335,
                                start + 70
                              );
                              doc.setFontSize(9);
                              doc.setTextColor(96);
                              doc.text(
                                "Terminal:" + "\n" + "Não disponível",
                                335,
                                start + 90
                              );
                              doc.setTextColor(0);
                              doc.setDrawColor(192, 192, 192); // draw red lines
                              doc.setLineWidth(0.1);
                              doc.line(210, start + 40, 450, start + 40); // horizontal line

                              doc.setDrawColor(192, 192, 192); // draw red lines
                              doc.setLineWidth(0.1);
                              doc.line(330, start + 40, 330, start + 190); // vertical line

                              doc.setDrawColor(192, 192, 192); // draw red lines
                              doc.setLineWidth(0.1);
                              doc.line(450, start, 450, start + 190); // vertical line

                              doc.setFontSize(9);
                              doc.setTextColor(96);
                              doc.text("Aeronave: " + "", 460, start + 10);
                              doc.text(
                                "Distância (em milhas" +
                                  "\n" +
                                  "ORIGEM/DESTINO): " +
                                  "\n" +
                                  "",
                                460,
                                start + 30
                              );
                              doc.text("Escala: " + "", 460, start + 60);
                              doc.text("Refeições: " + "", 460, start + 80);
                              doc.setTextColor(0);
                              doc.setFontSize(12);
                              start = start + 10;
                              doc.text(
                                50,
                                start,
                                "LATAM AIRLINES" + "\n" + "GROUP"
                              );
                              start = start + 30;
                              doc.setFontType("bold");
                              doc.text(
                                50,
                                start,
                                $scope.flight_selected.flight
                              );
                              doc.setFontType("normal");
                              start = start + 10;
                              doc.setFontSize(10);
                              doc.setTextColor(64);
                              start = start + 20;
                              doc.text(
                                50,
                                start,
                                "Operado por\n" +
                                  $scope.flight_selected.airline +
                                  " AIRLINES"
                              );
                              start = start + 30;
                              var fl = $scope.flight_selected.flight_time.split(
                                ":"
                              );
                              doc.text(
                                50,
                                start,
                                "Duração\n" +
                                  fl[0] +
                                  "hr(s)" +
                                  " " +
                                  fl[1] +
                                  "min(s)"
                              );
                              start = start + 30;
                              doc.text(
                                50,
                                start,
                                "Classe\n" + $scope.flight_selected.class
                              );
                              start = start + 30;
                              doc.text(50, start, "Status\n Confirmado");
                              start = start + 40;

                              var columns = [
                                {
                                  title: "Nome do Passageiro",
                                  dataKey: "name",
                                },
                                { title: "Assentos", dataKey: "seat" },
                                {
                                  title:
                                    "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                  dataKey: "ticket",
                                },
                              ];

                              var rows = [];

                              var group = $filter("groupBy")(
                                $scope.paxLatam,
                                "pax"
                              );

                              const resultGroup = Object.keys(group).map(
                                function (key) {
                                  return group[key];
                                }
                              );

                              for (var jn in resultGroup) {
                                rows.push({
                                  name: resultGroup[jn][0].pax,
                                  seat: resultGroup[jn][0].sit,
                                  ticket: resultGroup[jn][0].ticket_code,
                                });
                              }

                              doc.autoTable(columns, rows, {
                                columnStyles: {
                                  name: { columnWidth: 200 },
                                  seat: { columnWidth: 155 },
                                  ticket: { columnWidth: 155 },
                                },
                                showHeader: "firstPage",
                                headerStyles: {
                                  fillColor: [224, 224, 224],
                                  fontSize: 8,
                                },
                                styles: {
                                  fontSize: 8,
                                  textColor: 96,
                                },
                                margin: { top: start, left: 40 },
                                theme: "grid",
                                pageBreak: "avoid",
                              });

                              start = doc.autoTableEndPosY();
                              doc.setDrawColor(64);
                              doc.setLineWidth(2);
                              start = start + 10;
                              doc.line(40, start, 550, start);
                              if (start >= 570) {
                                start = 20;
                                doc.autoTableAddPage();
                              }
                            },
                          });

                          doc.setFontSize(8);

                          start = start + 40;

                          if ($scope.connections.length > 0) {
                            $scope.seat.push($scope.flight_selected.seat);

                            if ($scope.flight_selected.connections) {
                              for (
                                var i = 0;
                                i < $scope.flight_selected.connections.length;
                                i++
                              ) {
                                $scope.seat.push(
                                  $scope.flight_selected.connections[i].seat
                                );
                              }
                            }

                            var classFlight = $scope.flight_selected.class;
                            if (classFlight == "Executiva") {
                              classFlight = "Premium Business ( U )";
                            } else {
                              classFlight += " ( T ) ";
                            }

                            var dateReportDeparture = new Date(
                              $scope.flight_selected.boarding_date
                            );
                            var count = 0;

                            var board = new Date(
                              $scope.flight_selected.boarding_date
                            );

                            $scope.connections.newboarding = new Date();
                            $scope.connections.newlanding = new Date();

                            $scope.days = new Array();

                            for (
                              var ind = 0;
                              ind < $scope.connections.length;
                              ind++
                            ) {
                              $scope.connections[ind].newboarding = new Date();
                              $scope.connections[ind].newlanding = new Date();

                              var land = new Date(
                                board.getFullYear(),
                                board.getMonth(),
                                board.getDate()
                              );

                              board.setHours(
                                parseInt(
                                  $scope.connections[ind].boarding.split(":")[0]
                                )
                              );
                              board.setMinutes(
                                parseInt(
                                  $scope.connections[ind].boarding.split(":")[1]
                                )
                              );

                              land.setHours(
                                parseInt(
                                  $scope.connections[ind].landing.split(":")[0]
                                )
                              );
                              land.setMinutes(
                                parseInt(
                                  $scope.connections[ind].landing.split(":")[1]
                                )
                              );

                              if (ind > 0) {
                                if (
                                  parseInt(
                                    $scope.connections[ind].boarding.split(
                                      ":"
                                    )[0]
                                  ) <
                                    parseInt(
                                      $scope.connections[ind - 1].landing.split(
                                        ":"
                                      )[0]
                                    ) ||
                                  $scope.connections[ind - 1].landing.split(
                                    ":"
                                  )[0] <
                                    $scope.connections[ind - 1].boarding.split(
                                      ":"
                                    )[0]
                                ) {
                                  land.setDate(land.getDate() + 1);
                                }
                              }

                              $scope.connections[ind].newboarding = board;
                              $scope.connections[ind].newlanding = land;

                              $scope.connections[ind].newboarding.setDate(
                                $scope.connections[ind].newlanding.getDate()
                              );

                              var c = $scope.connections[ind].newboarding;
                              var l = $scope.connections[ind].newlanding;
                              $scope.days.push({
                                boarding: new Date(c),
                                landing: new Date(l),
                              });
                            }

                            $scope.days.forEach(function (element, index) {
                              $scope.connections[index].newboarding = new Date(
                                element.boarding
                              );
                              $scope.connections[index].newlanding = new Date(
                                element.landing
                              );
                            });

                            for (
                              var conn = 0;
                              conn < $scope.connections.length;
                              conn++
                            ) {
                              var from = $rootScope.airports.filter(function (
                                airport
                              ) {
                                return (
                                  airport.iataCode ==
                                  $scope.connections[conn].airportCodeFrom
                                );
                              })[0];
                              var to = $rootScope.airports.filter(function (
                                airport
                              ) {
                                return (
                                  airport.iataCode ==
                                  $scope.connections[conn].airportCodeTo
                                );
                              })[0];

                              dateReportDeparture =
                                $scope.connections[conn].newboarding;
                              $scope.connect = $scope.connectionx[conn];

                              count++;
                              var columns = [
                                { title: "", dataKey: "name" },
                                { title: "", dataKey: "data" },
                                { title: "", dataKey: "hour" },
                              ];
                              var rows = [{ name: "", data: "", hour: "" }];
                              doc.autoTable(columns, rows, {
                                columnStyles: {},
                                styles: {
                                  fontSize: 12,
                                },
                                margin: { top: start, left: 40 },
                                theme: "plain",
                                pageBreak: "avoid",
                                addPageContent: function (data) {
                                  doc.setFontSize(12);
                                  doc.addImage(
                                    base32Img,
                                    "JPEG",
                                    40,
                                    start + 10,
                                    20,
                                    20
                                  );
                                  doc.text("SAÍDA: ", 60, start + 20);
                                  doc.setFontType("bold");
                                  doc.text(
                                    $scope.getWeekday(dateReportDeparture) +
                                      " " +
                                      $filter("date")(
                                        dateReportDeparture,
                                        "dd"
                                      ) +
                                      " " +
                                      $scope.getLaTamMonth(dateReportDeparture),
                                    100,
                                    start + 20
                                  );
                                  doc.setFontType("normal");
                                  doc.setFontSize(9);
                                  doc.setTextColor(160);
                                  doc.text(
                                    "Por favor, verifique o horário da decolagem dos vôos.",
                                    240,
                                    start + 20
                                  );

                                  doc.setFontSize(10);
                                  start = start + 30;

                                  doc.setDrawColor(224);
                                  doc.setFillColor(224, 224, 224);
                                  doc.rect(40, start, 500, 190, "FD");

                                  doc.setDrawColor(128);
                                  doc.setFillColor(255, 255, 255);
                                  doc.rect(200, start, 350, 190, "FD");

                                  doc.setDrawColor(192, 192, 192); // draw red lines
                                  doc.setLineWidth(0.1);
                                  doc.line(200, start + 40, 450, start + 40); // horizontal line

                                  doc.setDrawColor(192, 192, 192); // draw red lines
                                  doc.setLineWidth(0.1);
                                  doc.line(320, start + 40, 320, start + 190); // vertical line

                                  doc.setDrawColor(192, 192, 192); // draw red lines
                                  doc.setLineWidth(0.1);
                                  doc.line(450, start, 450, start + 190); // vertical line
                                  doc.setFontSize(13);
                                  doc.setTextColor(32);
                                  doc.text(
                                    $scope.connections[conn].airportCodeFrom,
                                    210,
                                    start + 20
                                  );
                                  doc.addImage(
                                    base8Img,
                                    "JPEG",
                                    320,
                                    start + 15,
                                    7,
                                    7
                                  );
                                  doc.text(
                                    $scope.connections[conn].airportCodeTo,
                                    340,
                                    start + 20
                                  );
                                  doc.setTextColor(0);
                                  doc.setFontSize(8);

                                  doc.setFontSize(10);
                                  doc.setTextColor(96);
                                  doc.text(from.city.name, 210, start + 30);
                                  doc.text(to.city.name, 340, start + 30);
                                  doc.setTextColor(0);

                                  start = start + 10;

                                  doc.setTextColor(96);
                                  doc.setFontSize(10);
                                  doc.text(
                                    "Partindo às (hora local):",
                                    210,
                                    start + 40
                                  );
                                  doc.setFontSize(13);
                                  doc.setTextColor(32);
                                  doc.text(
                                    $rootScope.pad(
                                      $scope.connections[
                                        conn
                                      ].newboarding.getHours()
                                    ) +
                                      ":" +
                                      $rootScope.pad(
                                        $scope.connections[
                                          conn
                                        ].newboarding.getMinutes()
                                      ),
                                    210,
                                    start + 60
                                  );
                                  doc.setFontSize(9);
                                  doc.setTextColor(96);
                                  doc.text(
                                    "Terminal:" + "\n" + "Não disponível",
                                    210,
                                    start + 80
                                  );

                                  doc.text(
                                    "Chegando às (hora local):",
                                    330,
                                    start + 40
                                  );
                                  doc.setFontSize(13);
                                  doc.setTextColor(32);
                                  doc.text(
                                    $rootScope.pad(
                                      $scope.connections[
                                        conn
                                      ].newlanding.getHours()
                                    ) +
                                      ":" +
                                      $rootScope.pad(
                                        $scope.connections[
                                          conn
                                        ].newlanding.getMinutes()
                                      ),
                                    330,
                                    start + 60
                                  );
                                  doc.setFontSize(9);
                                  doc.setTextColor(96);
                                  doc.text(
                                    "Terminal:" + "\n" + "Não disponível",
                                    330,
                                    start + 80
                                  );
                                  doc.setTextColor(0);

                                  doc.setFontSize(9);
                                  doc.setTextColor(96);
                                  doc.text("Aeronave: " + "", 460, start + 10);
                                  doc.text(
                                    "Distância (em milhas" +
                                      "\n" +
                                      "ORIGEM/DESTINO): " +
                                      "\n" +
                                      "",
                                    460,
                                    start + 30
                                  );
                                  doc.text("Escala: " + "", 460, start + 60);
                                  doc.text("Refeições: " + "", 460, start + 80);
                                  doc.setTextColor(0);

                                  doc.setFontSize(12);
                                  start = start + 10;
                                  doc.text(
                                    50,
                                    start,
                                    "LATAM AIRLINES" + "\n" + "GROUP"
                                  );
                                  start = start + 30;
                                  doc.setFontType("bold");
                                  doc.text(
                                    50,
                                    start,
                                    $scope.connections[conn].flight
                                  );
                                  doc.setFontType("normal");
                                  doc.setFontSize(10);
                                  doc.setTextColor(64);
                                  start = start + 20;
                                  doc.text(
                                    50,
                                    start,
                                    "Operado por\n" +
                                      $scope.flight_selected.airline +
                                      " AIRLINES"
                                  );
                                  start = start + 30;
                                  var fl = $scope.connections[
                                    conn
                                  ].flightTime.split(":");
                                  doc.text(
                                    50,
                                    start,
                                    "Duração\n" +
                                      fl[0] +
                                      "hr(s)" +
                                      " " +
                                      fl[1] +
                                      "min(s)"
                                  );
                                  start = start + 30;
                                  doc.text(
                                    50,
                                    start,
                                    "Classe\n" + $scope.flight_selected.class
                                  );
                                  start = start + 30;
                                  doc.text(50, start, "Status\n Confirmado");
                                  start = start + 40;

                                  var fullName =
                                    $scope.flight_selected.pax_name +
                                    " " +
                                    $scope.flight_selected.paxLastName;
                                  var columns = [
                                    {
                                      title: "Nome do Passageiro",
                                      dataKey: "name",
                                    },
                                    { title: "Assentos", dataKey: "seat" },
                                    {
                                      title:
                                        "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                      dataKey: "ticket",
                                    },
                                  ];

                                  var rows = [];

                                  var lets = $filter("groupBy")(
                                    $scope.paxLatamConn,
                                    "pax"
                                  );

                                  const results = Object.keys(lets).map(
                                    function (key) {
                                      return lets[key];
                                    }
                                  );

                                  var inter = new Array();

                                  for (var j in results) {
                                    var seat = new Array();
                                    var pax = "",
                                      ticket_code = "";

                                    for (var f in results[j]) {
                                      pax = results[j][f].pax;
                                      ticket_code = results[j][f].ticket_code;
                                      seat.push(results[j][f].sit);
                                    }

                                    inter.push({
                                      pax: pax,
                                      seat: seat,
                                      ticket_code: ticket_code,
                                    });
                                  }

                                  for (var i in inter) {
                                    var element = inter[i];
                                    rows.push({
                                      name: element.pax,
                                      seat: element.seat[conn],
                                      ticket: element.ticket_code,
                                    });
                                  }

                                  doc.autoTable(columns, rows, {
                                    startY: start,
                                    pageBreak: "auto",
                                    margin: { left: 40 },
                                    theme: "grid",
                                    showHeader: "firstPage",
                                    columnStyles: {
                                      name: { columnWidth: 200 },
                                      seat: { columnWidth: 155 },
                                      ticket: { columnWidth: 155 },
                                    },
                                    styles: {
                                      overflow: "linebreak",
                                      overflowColumns: false,
                                      fontSize: 8,
                                      textColor: 96,
                                    },
                                    headerStyles: {
                                      fillColor: [224, 224, 224],
                                      fontSize: 8,
                                    },
                                    createdCell: function (cell, data) {},
                                  });

                                  doc.setDrawColor(64);
                                  doc.setLineWidth(2);
                                  start = doc.autoTableEndPosY();
                                  start = start + 10;

                                  doc.line(40, start, 550, start);
                                  start = start + 20;
                                  if (start >= 610) {
                                    start = 60;
                                    doc.autoTableAddPage();
                                  }
                                },
                                drawCell: function (cell, data) {},
                              });
                            }
                          }

                          if ($scope.getIsReturn($scope.flight_selected)) {
                            $scope.returnFlight = $scope.getReturnFlight(
                              $scope.flight_selected.airline,
                              $scope.flight_selected.cards_id,
                              $scope.flight_selected.flight,
                              $scope.flight_selected.airport_code_from,
                              $scope.flight_selected.airport_code_to,
                              $scope.flight_selected.flightLocator
                            );
                            $scope.Return = angular.copy($scope.returnFlight);
                            var from = $rootScope.airports.filter(function (
                              airport
                            ) {
                              return (
                                airport.iataCode ==
                                $scope.returnFlight.airport_code_from
                              );
                            })[0];

                            var to = $rootScope.airports.filter(function (
                              airport
                            ) {
                              return (
                                airport.iataCode ==
                                $scope.returnFlight.airport_code_to
                              );
                            })[0];

                            if ($scope.flight_selected.seat) {
                              var seat = $scope.flight_selected.seat;
                            } else {
                              var seat = "";
                            }
                            start = start + 10;

                            var columns = [
                              { title: "", dataKey: "name" },
                              { title: "", dataKey: "data" },
                              { title: "", dataKey: "hour" },
                            ];
                            var rows = [{ name: "", data: "", hour: "" }];

                            doc.autoTable(columns, rows, {
                              columnStyles: {},
                              styles: {
                                fontSize: 12,
                              },
                              margin: { top: start, left: 40 },
                              theme: "plain",
                              pageBreak: "avoid",
                              addPageContent: function (data) {
                                doc.setFontSize(12);
                                doc.addImage(
                                  base32Img,
                                  "JPEG",
                                  40,
                                  start + 20,
                                  20,
                                  20
                                );
                                doc.text("SAÍDA: ", 60, start + 30);
                                doc.setFontType("bold");
                                doc.text(
                                  $scope.getWeekday(
                                    new Date($scope.returnFlight.boarding_date)
                                  ) +
                                    " " +
                                    $filter("date")(
                                      new Date(
                                        $scope.returnFlight.boarding_date
                                      ),
                                      "dd"
                                    ) +
                                    " " +
                                    $scope.getLaTamMonth(
                                      new Date(
                                        $scope.returnFlight.boarding_date
                                      )
                                    ),
                                  100,
                                  start + 30
                                );
                                doc.setFontType("normal");
                                doc.setFontSize(9);
                                doc.setTextColor(160);
                                doc.text(
                                  "Por favor, verifique o horário da decolagem dos vôos.",
                                  240,
                                  start + 30
                                );

                                doc.setFontSize(10);
                                start = start + 40;

                                doc.setDrawColor(224);
                                doc.setFillColor(224, 224, 224);
                                doc.rect(40, start, 500, 190, "FD");

                                doc.setDrawColor(128);
                                doc.setFillColor(255, 255, 255);
                                doc.rect(200, start, 350, 190, "FD");

                                doc.setDrawColor(192, 192, 192); // draw red lines
                                doc.setLineWidth(0.1);
                                doc.line(200, start + 40, 450, start + 40); // horizontal line

                                doc.setDrawColor(192, 192, 192); // draw red lines
                                doc.setLineWidth(0.1);
                                doc.line(320, start + 40, 320, start + 190); // vertical line

                                doc.setDrawColor(192, 192, 192); // draw red lines
                                doc.setLineWidth(0.1);
                                doc.line(450, start, 450, start + 190); // vertical line

                                doc.setFontSize(13);
                                doc.setTextColor(32);
                                doc.text(
                                  $scope.returnFlight.airport_code_from,
                                  210,
                                  start + 20
                                );
                                doc.addImage(
                                  base8Img,
                                  "JPEG",
                                  320,
                                  start + 15,
                                  7,
                                  7
                                );
                                doc.text(
                                  $scope.returnFlight.airport_code_to,
                                  340,
                                  start + 20
                                );
                                doc.setTextColor(0);
                                doc.setFontSize(8);
                                start = start + 10;

                                doc.setFontSize(10);
                                doc.setTextColor(96);
                                doc.text(from.city.name, 210, start + 20);
                                doc.text(to.city.name, 340, start + 20);
                                doc.setTextColor(0);
                                var timeBoarding = new Date(
                                  $scope.returnFlight.boarding_date
                                );
                                var timeLanding = new Date(
                                  $scope.returnFlight.landing_date
                                );

                                doc.setTextColor(96);
                                doc.setFontSize(10);
                                doc.text(
                                  "Partindo às (hora local):",
                                  210,
                                  start + 40
                                );
                                doc.setFontSize(13);
                                doc.setTextColor(32);
                                doc.text(
                                  $rootScope.pad(timeBoarding.getHours()) +
                                    ":" +
                                    $rootScope.pad(timeBoarding.getMinutes()),
                                  210,
                                  start + 60
                                );
                                doc.setFontSize(9);
                                doc.setTextColor(96);
                                doc.text(
                                  "Terminal:" + "\n" + "Não disponível",
                                  210,
                                  start + 80
                                );

                                doc.text(
                                  "Chegando às (hora local):",
                                  330,
                                  start + 40
                                );
                                doc.setFontSize(13);
                                doc.setTextColor(32);
                                doc.text(
                                  $rootScope.pad(timeLanding.getHours()) +
                                    ":" +
                                    $rootScope.pad(timeLanding.getMinutes()),
                                  330,
                                  start + 60
                                );
                                doc.setFontSize(9);
                                doc.setTextColor(96);
                                doc.text(
                                  "Terminal:" + "\n" + "Não disponível",
                                  330,
                                  start + 80
                                );
                                doc.setTextColor(0);

                                doc.setFontSize(9);
                                doc.setTextColor(96);
                                doc.text("Aeronave: " + "", 460, start + 10);
                                doc.text(
                                  "Distância (em milhas" +
                                    "\n" +
                                    "ORIGEM/DESTINO): " +
                                    "\n" +
                                    "",
                                  460,
                                  start + 30
                                );
                                doc.text("Escala: " + "", 460, start + 60);
                                doc.text("Refeições: " + "", 460, start + 80);
                                doc.setTextColor(0);

                                doc.setFontSize(12);
                                start = start + 20;
                                doc.text(
                                  50,
                                  start,
                                  "LATAM AIRLINES" + "\n" + "GROUP"
                                );
                                start = start + 30;
                                doc.setFontType("bold");
                                doc.text(50, start, $scope.returnFlight.flight);
                                doc.setFontType("normal");
                                doc.setFontSize(10);
                                doc.setTextColor(64);
                                start = start + 20;
                                doc.text(
                                  50,
                                  start,
                                  "Operado por\n" +
                                    $scope.returnFlight.airline +
                                    " AIRLINES"
                                );
                                start = start + 30;
                                var fl = $scope.returnFlight.flight_time.split(
                                  ":"
                                );
                                doc.text(
                                  50,
                                  start,
                                  "Duração\n" +
                                    fl[0] +
                                    "hr(s)" +
                                    " " +
                                    fl[1] +
                                    "min(s)"
                                );
                                start = start + 30;
                                doc.text(
                                  50,
                                  start,
                                  "Classe\n" + $scope.returnFlight.class
                                );
                                start = start + 30;
                                doc.text(50, start, "Status\n Confirmado");
                                start = start + 30;

                                var fullName =
                                  $scope.returnFlight.pax_name +
                                  " " +
                                  $scope.returnFlight.paxLastName;

                                var columns = [
                                  {
                                    title: "Nome do Passageiro",
                                    dataKey: "name",
                                  },
                                  { title: "Assentos", dataKey: "seat" },
                                  {
                                    title:
                                      "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                    dataKey: "ticket",
                                  },
                                ];

                                var rows = [];

                                $scope.paxReturn = $scope.getPaxLaTamReturn(
                                  $scope.returnFlight.airline,
                                  $scope.returnFlight.cards_id,
                                  $scope.returnFlight.flight,
                                  $scope.returnFlight.flightLocator
                                );

                                var letsR = $filter("groupBy")(
                                  $scope.paxReturn,
                                  "pax"
                                );

                                const resultsRet = Object.keys(letsR).map(
                                  function (key) {
                                    return letsR[key];
                                  }
                                );

                                var inteR = new Array();

                                for (var jn in resultsRet) {
                                  var seatReturn = new Array();
                                  var pax = "",
                                    ticket_code = "";
                                  for (var fn in resultsRet[jn]) {
                                    pax = resultsRet[jn][fn].pax;
                                    ticket_code =
                                      resultsRet[jn][fn].ticket_code;

                                    seatReturn.push(resultsRet[jn][fn].sit);
                                  }

                                  inteR.push({
                                    pax: pax,
                                    seat: seatReturn,
                                    ticket_code: ticket_code,
                                  });
                                }

                                for (var i in inteR) {
                                  var element = inteR[i];
                                  rows.push({
                                    name: element.pax,
                                    seat: element.seat[0],
                                    ticket: element.ticket_code,
                                  });
                                }

                                doc.autoTable(columns, rows, {
                                  columnStyles: {
                                    name: { columnWidth: 200 },
                                    seat: { columnWidth: 155 },
                                    ticket: { columnWidth: 155 },
                                  },
                                  showHeader: "firstPage",
                                  headerStyles: {
                                    fillColor: [224, 224, 224],
                                    fontSize: 8,
                                  },
                                  styles: {
                                    fontSize: 8,
                                    textColor: 96,
                                  },
                                  startY: start,
                                  margin: { left: 40 },
                                  theme: "grid",
                                  pageBreak: "avoid",
                                });

                                doc.setDrawColor(64);
                                doc.setLineWidth(2);

                                start = doc.autoTableEndPosY();
                                start = start + 10;
                                doc.line(40, start, 550, start);
                                start = start + 20;
                              },
                            });
                            if (start >= 610) {
                              start = 20;
                              doc.autoTableAddPage();
                            }
                            if ($scope.connectionsReturn.length > 0) {
                              $scope.seats.push($scope.returnFlight.seat);

                              if ($scope.returnFlight.connections) {
                                for (
                                  var i = 0;
                                  i < $scope.returnFlight.connections.length;
                                  i++
                                ) {
                                  $scope.seats.push(
                                    $scope.returnFlight.connections[i].seat
                                  );
                                }
                              }

                              var ConnectionsReturn = $scope.connectionsReturn;
                              var classFlight = $scope.returnFlight.class;

                              if (classFlight == "Executiva") {
                                classFlight = "Premium Business ( U )";
                              } else {
                                classFlight += " ( T ) ";
                              }

                              var dateReportReturn = new Date(
                                $scope.Return.boarding_date
                              );
                              //console.log("dateReportReturn", dateReportReturn);
                              var counts = 0;

                              const board = new Date(
                                $scope.Return.boarding_date
                              );

                              $scope.daysReturn = new Array();

                              for (
                                var index = 0;
                                index < $scope.connectionsReturn.length;
                                index++
                              ) {
                                var land = new Date(
                                  board.getFullYear(),
                                  board.getMonth(),
                                  board.getDate()
                                );

                                board.setHours(
                                  parseInt(
                                    $scope.connectionsReturn[
                                      index
                                    ].boarding.split(":")[0]
                                  )
                                );
                                board.setMinutes(
                                  parseInt(
                                    $scope.connectionsReturn[
                                      index
                                    ].boarding.split(":")[1]
                                  )
                                );

                                land.setHours(
                                  parseInt(
                                    $scope.connectionsReturn[
                                      index
                                    ].landing.split(":")[0]
                                  )
                                );
                                land.setMinutes(
                                  parseInt(
                                    $scope.connectionsReturn[
                                      index
                                    ].landing.split(":")[1]
                                  )
                                );

                                if (index > 0) {
                                  if (
                                    parseInt(
                                      $scope.connectionsReturn[
                                        index
                                      ].boarding.split(":")[0]
                                    ) <
                                      parseInt(
                                        $scope.connectionsReturn[
                                          index - 1
                                        ].landing.split(":")[0]
                                      ) ||
                                    parseInt(
                                      $scope.connectionsReturn[
                                        index - 1
                                      ].boarding.split(":")[0]
                                    ) >
                                      parseInt(
                                        $scope.connectionsReturn[
                                          index - 1
                                        ].landing.split(":")[0]
                                      )
                                  ) {
                                    land.setDate(land.getDate() + 1);
                                  }
                                }

                                $scope.connectionsReturn[
                                  index
                                ].newboarding = board;
                                $scope.connectionsReturn[
                                  index
                                ].newlanding = land;

                                $scope.connectionsReturn[
                                  index
                                ].newboarding.setDate(
                                  $scope.connectionsReturn[
                                    index
                                  ].newlanding.getDate()
                                );

                                var cv =
                                  $scope.connectionsReturn[index].newboarding;

                                $scope.daysReturn.push({
                                  boarding: new Date(cv),
                                  landing: new Date(
                                    $scope.connectionsReturn[index].newlanding
                                  ),
                                });
                              }

                              $scope.daysReturn.forEach(function (el, index) {
                                ($scope.connectionsReturn[
                                  index
                                ].newboarding = new Date(el.boarding)),
                                  ($scope.connectionsReturn[
                                    index
                                  ].newlanding = new Date(el.landing));
                              });

                              for (var connection in $scope.connectionsReturn) {
                                var from = $rootScope.airports.filter(function (
                                  airport
                                ) {
                                  return (
                                    airport.iataCode ==
                                    $scope.connectionsReturn[connection]
                                      .airportCodeFrom
                                  );
                                })[0];
                                var to = $rootScope.airports.filter(function (
                                  airport
                                ) {
                                  return (
                                    airport.iataCode ==
                                    $scope.connectionsReturn[connection]
                                      .airportCodeTo
                                  );
                                })[0];

                                /*console.log(
                                  "TypeOf",
                                  $scope.connectionsReturn[connection]
                                );*/
                                dateReportReturn =
                                  $scope.connectionsReturn[connection]
                                    .newboarding;

                                counts++;
                                var columns = [
                                  { title: "", dataKey: "name" },
                                  { title: "", dataKey: "data" },
                                  { title: "", dataKey: "hour" },
                                ];
                                var rows = [{ name: "", data: "", hour: "" }];
                                start = start + 10;

                                doc.autoTable(columns, rows, {
                                  columnStyles: {},
                                  styles: {
                                    fontSize: 12,
                                  },
                                  margin: { top: start, left: 40 },
                                  theme: "plain",
                                  pageBreak: "avoid",
                                  addPageContent: function (data) {
                                    doc.setFontSize(12);
                                    doc.addImage(
                                      base32Img,
                                      "JPEG",
                                      40,
                                      start + 20,
                                      20,
                                      20
                                    );
                                    doc.text("SAÍDA: ", 60, start + 30);
                                    doc.setFontType("bold");
                                    doc.text(
                                      $scope.getWeekday(dateReportReturn) +
                                        " " +
                                        $filter("date")(
                                          new Date(dateReportReturn),
                                          "dd"
                                        ) +
                                        " " +
                                        $scope.getLaTamMonth(dateReportReturn),
                                      100,
                                      start + 30
                                    );
                                    doc.setFontType("normal");
                                    doc.setFontSize(9);
                                    doc.setTextColor(160);
                                    doc.text(
                                      "Por favor, verifique o horário da decolagem dos vôos.",
                                      240,
                                      start + 30
                                    );

                                    doc.setFontSize(10);
                                    start = start + 40;

                                    doc.setDrawColor(224);
                                    doc.setFillColor(224, 224, 224);
                                    doc.rect(40, start, 500, 190, "FD");

                                    doc.setDrawColor(128);
                                    doc.setFillColor(255, 255, 255);
                                    doc.rect(200, start, 350, 190, "FD");

                                    doc.setDrawColor(192, 192, 192); // draw red lines
                                    doc.setLineWidth(0.1);
                                    doc.line(200, start + 40, 450, start + 40); // horizontal line

                                    doc.setDrawColor(192, 192, 192); // draw red lines
                                    doc.setLineWidth(0.1);
                                    doc.line(320, start + 40, 320, start + 190); // vertical line

                                    doc.setDrawColor(192, 192, 192); // draw red lines
                                    doc.setLineWidth(0.1);
                                    doc.line(450, start, 450, start + 190); // vertical line

                                    doc.setFontSize(13);
                                    doc.setTextColor(32);
                                    doc.text(
                                      $scope.connectionsReturn[connection]
                                        .airportCodeFrom,
                                      210,
                                      start + 20
                                    );
                                    doc.addImage(
                                      base8Img,
                                      "JPEG",
                                      320,
                                      start + 15,
                                      7,
                                      7
                                    );
                                    doc.text(
                                      $scope.connectionsReturn[connection]
                                        .airportCodeTo,
                                      340,
                                      start + 20
                                    );
                                    doc.setTextColor(0);
                                    doc.setFontSize(8);

                                    doc.setFontSize(10);
                                    doc.setTextColor(96);
                                    start = start + 10;
                                    doc.text(from.city.name, 210, start + 20);
                                    doc.text(to.city.name, 340, start + 20);

                                    doc.setTextColor(96);
                                    doc.setFontSize(10);
                                    doc.text(
                                      "Partindo às (hora local):",
                                      210,
                                      start + 40
                                    );
                                    doc.setFontSize(13);
                                    doc.setTextColor(32);
                                    $scope.connectionsReturn[
                                      connection
                                    ].newboarding = new Date(
                                      $scope.connectionsReturn[
                                        connection
                                      ].newboarding
                                    );
                                    /*doc.text($rootScope.pad($scope.connectionsReturn[connection].newboarding.getHours()) + ':' + $rootScope.pad($scope.connectionsReturn[connection].newboarding.getMinutes()), 210, start + 60);*/
                                    doc.text(
                                      $scope.connectionsReturn[connection]
                                        .boarding,
                                      210,
                                      start + 60
                                    );
                                    doc.setFontSize(9);
                                    doc.setTextColor(96);
                                    doc.text(
                                      "Terminal:" + "\n" + "Não disponível",
                                      210,
                                      start + 80
                                    );

                                    doc.text(
                                      "Chegando às (hora local):",
                                      330,
                                      start + 40
                                    );
                                    doc.setFontSize(13);
                                    doc.setTextColor(32);
                                    $scope.connectionsReturn[
                                      connection
                                    ].newlanding = new Date(
                                      $scope.connectionsReturn[
                                        connection
                                      ].newlanding
                                    );
                                    /*doc.text($rootScope.pad($scope.connectionsReturn[connection].newlanding.getHours()) + ':' + $rootScope.pad($scope.connectionsReturn[connection].newlanding.getMinutes()), 330, start + 60);*/
                                    doc.text(
                                      $scope.connectionsReturn[connection]
                                        .landing,
                                      330,
                                      start + 60
                                    );
                                    doc.setFontSize(9);
                                    doc.setTextColor(96);
                                    doc.text(
                                      "Terminal:" + "\n" + "Não disponível",
                                      330,
                                      start + 80
                                    );
                                    doc.setTextColor(0);

                                    doc.setTextColor(0);

                                    doc.setFontSize(9);
                                    doc.setTextColor(96);
                                    doc.text(
                                      "Aeronave: " + "",
                                      460,
                                      start + 10
                                    );
                                    doc.text(
                                      "Distância (em milhas" +
                                        "\n" +
                                        "ORIGEM/DESTINO): " +
                                        "\n" +
                                        "",
                                      460,
                                      start + 30
                                    );
                                    doc.text("Escala: " + "", 460, start + 60);
                                    doc.text(
                                      "Refeições: " + "",
                                      460,
                                      start + 80
                                    );
                                    doc.setTextColor(0);

                                    doc.setFontSize(12);
                                    doc.text(
                                      50,
                                      start,
                                      "LATAM AIRLINES" + "\n" + "GROUP"
                                    );
                                    start = start + 30;
                                    doc.setFontType("bold");
                                    doc.text(
                                      50,
                                      start,
                                      $scope.connectionsReturn[connection]
                                        .flight
                                    );
                                    doc.setFontType("normal");
                                    doc.setFontSize(10);
                                    doc.setTextColor(64);
                                    start = start + 20;
                                    doc.text(
                                      50,
                                      start,
                                      "Operado por\n" +
                                        $scope.Return.airline +
                                        " AIRLINES"
                                    );
                                    start = start + 30;
                                    var fl = $scope.connectionsReturn[
                                      connection
                                    ].flightTime.split(":");
                                    doc.text(
                                      50,
                                      start,
                                      "Duração\n" +
                                        fl[0] +
                                        "hr(s)" +
                                        " " +
                                        fl[1] +
                                        "min(s)"
                                    );
                                    start = start + 30;
                                    doc.text(
                                      50,
                                      start,
                                      "Classe\n" + $scope.Return.class
                                    );
                                    start = start + 30;
                                    doc.text(50, start, "Status\n Confirmado");
                                    start = start + 40;
                                    doc.setFontSize(8);

                                    start = start + 10;

                                    var columns = [
                                      {
                                        title: "Nome do Passageiro",
                                        dataKey: "name",
                                      },
                                      { title: "Assentos", dataKey: "seat" },
                                      {
                                        title:
                                          "Recibo(s) de Bilhete(s) Eletrônico(s):",
                                        dataKey: "ticket",
                                      },
                                    ];

                                    var rows = [];

                                    var lets = $filter("groupBy")(
                                      $scope.paxReturn,
                                      "pax"
                                    );

                                    const resultsReturn = Object.keys(lets).map(
                                      function (key) {
                                        return lets[key];
                                      }
                                    );

                                    var inter = new Array();

                                    for (var jn in resultsReturn) {
                                      var seatReturn = new Array();
                                      var pax = "",
                                        ticket_code = "";

                                      for (var fn in resultsReturn[jn]) {
                                        pax = resultsReturn[jn][fn].pax;
                                        ticket_code =
                                          resultsReturn[jn][fn].ticket_code;
                                        seatReturn.push(
                                          resultsReturn[jn][fn].sit
                                        );
                                      }

                                      inter.push({
                                        pax: pax,
                                        seat: seatReturn,
                                        ticket_code: ticket_code,
                                      });
                                    }

                                    for (var i in inter) {
                                      var element = inter[i];
                                      rows.push({
                                        name: element.pax,
                                        seat: element.seat[connection],
                                        ticket: element.ticket_code,
                                      });
                                    }

                                    doc.autoTable(columns, rows, {
                                      columnStyles: {
                                        name: { columnWidth: 200 },
                                        seat: { columnWidth: 155 },
                                        ticket: { columnWidth: 155 },
                                      },
                                      showHeader: "firstPage",
                                      headerStyles: {
                                        fillColor: [224, 224, 224],
                                        fontSize: 8,
                                      },
                                      styles: {
                                        fontSize: 8,
                                        textColor: 96,
                                      },
                                      startY: start,
                                      margin: { left: 40 },
                                      theme: "grid",
                                      pageBreak: "avoid",
                                    });
                                    doc.setDrawColor(64);
                                    doc.setLineWidth(2);

                                    start = doc.autoTableEndPosY();
                                    start = start + 10;
                                    doc.line(40, start, 550, start);
                                    start = start + 20;
                                  },
                                  drawCell: function (cell, data) {},
                                });

                                if (start > 610) {
                                  doc.autoTableAddPage();
                                  start = 60;
                                  counts = 0;
                                }
                              }
                            }
                          }

                          if ($scope.flight_selected.isBreak == "Quebrado") {
                            return logger.logError(
                              "Este trecho esta quebrado!"
                            );
                          }
                          doc.save(
                            $scope.flight_selected.flightLocator + ".pdf"
                          );
                          if ($scope.multiDownloads !== true) {
                            if ($scope.response) {
                              if (
                                $scope.response.notificationurl != null &&
                                $scope.response.notificationurl.indexOf(
                                  "http"
                                ) == -1
                              ) {
                                $scope.fillEmailBillet();
                              }
                            } else {
                              $scope.fillEmailBillet();
                            }
                          }
                        });
                      });
                    });
                  });
                }
              );
            }
          );
        };

        $scope.printBilletLaTam2 = function (flight_selected) {
          $scope.flight_selected = flight_selected || this.onlineflight;
          // if ($scope.selected.notificationurl != '' && $scope.selected.notificationurl != null && $scope.selected.notificationurl != undefined) {
          $scope.latamNovo($scope.flight_selected);
          // } else {
          // $scope.latamVelho($scope.flight_selected);
          // }
        };

        $scope.trim = function (str) {
          return str.replace(/^\s+|\s+$/g, "");
        };

        $scope.groupBy = function (xs, key) {
          return xs.reduce(function (rv, x) {
            (rv[x[key]] = rv[x[key]] || []).push(x);
            return rv;
          }, {});
        };

        $scope.changeClassFlights = function (flight) {
          for (var i in $scope.onlineflights) {
            if (
              $scope.onlineflights[i].boarding_date == flight.boarding_date &&
              $scope.onlineflights[i].airport_code_from ==
                flight.airport_code_from &&
              $scope.onlineflights[i].airport_code_to == flight.airport_code_to
            ) {
              $scope.onlineflights[i].class = flight.class;
            }
          }
        };

        $scope.getLaTamMonth = function (date) {
          var month = date.getMonth();
          switch (month) {
            case 0:
              return "JAN";
            case 1:
              return "FEV";
            case 2:
              return "MAR";
            case 3:
              return "ABR";
            case 4:
              return "MAI";
            case 5:
              return "JUN";
            case 6:
              return "JUL";
            case 7:
              return "AGO";
            case 8:
              return "SET";
            case 9:
              return "OUT";
            case 10:
              return "NOV";
            case 11:
              return "DEZ";
          }
        };

        $scope.getWeekday = function (date) {
          var day = new Date(date).getDay();
          switch (day) {
            case 0:
              return "Domingo";
            case 1:
              return "Segunda-feira";
            case 2:
              return "Terça-feira";
            case 3:
              return "Quarta-feira";
            case 4:
              return "Quinta-feira";
            case 5:
              return "Sexta-feira";
            case 6:
              return "Sábado";
          }
        };

        $scope.getBlueWeekday = function (date) {
          var day = date.getDay();
          switch (day) {
            case 0:
              return "Dom";
            case 1:
              return "Seg";
            case 2:
              return "Ter";
            case 3:
              return "Qua";
            case 4:
              return "Qui";
            case 5:
              return "Sex";
            case 6:
              return "Sáb";
          }
        };

        $scope.getMonth = function (date) {
          var month = new Date(date).getMonth();
          switch (month) {
            case 0:
              return "Janeiro";
            case 1:
              return "Fevereiro";
            case 2:
              return "Março";
            case 3:
              return "Abril";
            case 4:
              return "Maio";
            case 5:
              return "Junho";
            case 6:
              return "Julho";
            case 7:
              return "Agosto";
            case 8:
              return "Setembro";
            case 9:
              return "Outubro";
            case 10:
              return "Novembro";
            case 11:
              return "Dezembro";
          }
        };

        $scope.getReturn = function (airline, cards_id, flight) {
          var pax = [];
          $scope.filterBillet = $filter("filter")(
            $scope.onlineflights,
            airline
          );
          for (var i in $scope.filterBillet) {
            if (
              $scope.filterBillet[i].cards_id == cards_id &&
              $scope.filterBillet[i].flight != flight
            ) {
              if ($scope.connectionsReturn.length > 0) {
                pax.push({
                  from: "   ",
                  pax:
                    $scope.filterBillet[i].pax_name +
                    " " +
                    $scope.filterBillet[i].paxLastName +
                    " " +
                    $scope.filterBillet[i].paxAgnome,
                  froTo:
                    $scope.filterBillet[i].airport_code_from +
                    " - " +
                    $scope.connectionsReturn[0].airportCodeTo,
                  number: "  ",
                  sit: " " + $scope.getSeat($scope.filterBillet[i]) + " ",
                });
                for (var j = 1; j <= $scope.connectionsReturn.length - 1; j++) {
                  pax.push({
                    from: "   ",
                    pax: "Conexão (" + j + ")",
                    froTo:
                      $scope.connectionsReturn[j].airportCodeFrom +
                      " - " +
                      $scope.connectionsReturn[j].airportCodeTo,
                    number: "  ",
                    sit: $scope.getConnectionSeat($scope.filterBillet[i], j),
                  });
                }
              } else {
                pax.push({
                  from: "   ",
                  pax:
                    $scope.filterBillet[i].pax_name +
                    " " +
                    $scope.filterBillet[i].paxLastName +
                    " " +
                    $scope.filterBillet[i].paxAgnome,
                  froTo:
                    $scope.filterBillet[i].airport_code_from +
                    " - " +
                    $scope.filterBillet[i].airport_code_to,
                  number: "  ",
                  sit: " " + $scope.getSeat($scope.filterBillet[i]) + " ",
                });
              }
            }
          }
          return pax;
        };

        $scope.getSeat = function (flight) {
          for (var s in $scope.onlineflights) {
            if (
              $scope.onlineflights[s].flight == flight.flight &&
              $scope.onlineflights[s].boarding_date == flight.boarding_date &&
              $scope.onlineflights[s].pax_name == flight.pax_name &&
              $scope.onlineflights[s].paxLastName == flight.paxLastName &&
              $scope.onlineflights[s].paxAgnome == flight.paxAgnome &&
              $scope.onlineflights[s].airport_code_from ==
                flight.airport_code_from
            ) {
              if ($scope.onlineflights[s].is_newborn == "S") {
                return "Colo";
              }
              return $scope.onlineflights[s].seat;
            }
          }
          return " --- ";
        };

        $scope.getConnectionSeat = function (flight, j) {
          for (var s in $scope.onlineflights) {
            if (
              $scope.onlineflights[s].flight == flight.flight &&
              $scope.onlineflights[s].boarding_date == flight.boarding_date &&
              $scope.onlineflights[s].pax_name == flight.pax_name &&
              $scope.onlineflights[s].paxLastName == flight.paxLastName &&
              $scope.onlineflights[s].paxAgnome == flight.paxAgnome &&
              $scope.onlineflights[s].airport_code_from ==
                flight.airport_code_from
            ) {
              if ($scope.onlineflights[s].is_newborn == "S") {
                return "Colo";
              }
              if ($scope.onlineflights[s].connections) {
                return $scope.onlineflights[s].connections[j - 1].seat;
              } else {
                return " --- ";
              }
            }
          }
          return " --- ";
        };

        $scope.getIsReturn = function (flight_selected) {
          for (var i in $scope.onlineflights) {
            if (
              $scope.onlineflights[i].flightLocator ==
                flight_selected.flightLocator &&
              $scope.onlineflights[i].airline == flight_selected.airline &&
              $scope.onlineflights[i].flight != flight_selected.flight &&
              $scope.onlineflights[i].boarding_date !=
                flight_selected.boarding_date
            )
              return true;
          }
          return false;
        };

        $scope.getPax = function (airline, cards_id, flight, flightLocator) {
          var pax = [];
          $scope.bagagge = "";
          $scope.filterBillet = $filter("filter")(
            $scope.onlineflights,
            airline
          );
          for (var i in $scope.filterBillet) {
            if (
              $scope.filterBillet[i].cards_id == cards_id &&
              $scope.filterBillet[i].flight == flight &&
              $scope.filterBillet[i].flightLocator == flightLocator
            ) {
              var fullName = $scope.filterBillet[i].pax_name;
              if ($scope.filterBillet[i].paxLastName) {
                fullName += " " + $scope.filterBillet[i].paxLastName;
              }
              if ($scope.filterBillet[i].paxAgnome) {
                fullName += " " + $scope.filterBillet[i].paxAgnome;
              }

              var paxName = fullName.split(" ");
              var name = "";

              if (
                paxName[paxName.length - 1] == "" ||
                paxName[paxName.length - 1] == " "
              ) {
                paxName.splice(paxName.length - 1, 1);
              }

              if (paxName.length > 2) {
                if ($scope.checkPaxName(paxName[paxName.length - 1])) {
                  paxName.splice(paxName.length - 1, 1);
                }
              }

              for (var n in paxName) {
                name += paxName[n] + " ";
              }

              if ($scope.filterBillet[i].baggage == 0) {
                $scope.bagagge = "";
              } else {
                $scope.bagagge = "23Kg";
              }

              if ($scope.connections.length > 0) {
                pax.push({
                  pax: name,
                  center:
                    $scope.filterBillet[i].airport_code_from +
                    "-" +
                    $scope.connections[0].airportCodeTo,
                  cardNumber: $scope.bagagge,
                  sit: " " + $scope.getSeat($scope.filterBillet[i]) + " ",
                });
                for (var j = 1; j <= $scope.connections.length - 1; j++) {
                  pax.push({
                    pax: "Conexão",
                    center:
                      $scope.connections[j].airportCodeFrom +
                      " - " +
                      $scope.connections[j].airportCodeTo,
                    cardNumber: $scope.bagagge,
                    sit:
                      " " +
                      $scope.getConnectionSeat($scope.filterBillet[i], j) +
                      " ",
                  });
                }
              } else {
                pax.push({
                  pax: name,
                  center:
                    $scope.filterBillet[i].airport_code_from +
                    "-" +
                    $scope.filterBillet[i].airport_code_to,
                  cardNumber: $scope.bagagge,
                  sit: " " + $scope.getSeat($scope.filterBillet[i]) + " ",
                });
              }
            }
          }
          return pax;
        };

        $scope.getPaxLaTam = function (
          airline,
          cards_id,
          flight,
          flightLocator
        ) {
          var pax = [];
          $scope.bagagge = "";

          $scope.filterBillet = $filter("filter")(
            $scope.onlineflights,
            airline
          );
          for (var i in $scope.filterBillet) {
            if (
              $scope.filterBillet[i].cards_id == cards_id &&
              $scope.filterBillet[i].flight == flight &&
              $scope.filterBillet[i].flightLocator == flightLocator
            ) {
              var fullName = $scope.filterBillet[i].pax_name;
              if ($scope.filterBillet[i].paxLastName) {
                fullName += " " + $scope.filterBillet[i].paxLastName;
              }
              if ($scope.filterBillet[i].paxAgnome) {
                fullName += " " + $scope.filterBillet[i].paxAgnome;
              }
              var paxName = fullName.split(" ");
              var name = "";

              if (
                paxName[paxName.length - 1] == "" ||
                paxName[paxName.length - 1] == " "
              ) {
                paxName.splice(paxName.length - 1, 1);
              }

              /* if (paxName.length > 2) {
				if ($scope.checkPaxName(paxName[paxName.length - 1])) {
				paxName.splice(paxName.length - 1, 1);
				}
			}*/

              for (var n in paxName) {
                name += paxName[n] + " ";
              }

              if ($scope.filterBillet[i].baggage == 0) {
                $scope.bagagge = "";
              } else {
                $scope.bagagge = "23Kg";
              }

              if (
                $scope.connections &&
                $scope.connections != "" &&
                $scope.connections != null
              ) {
                if ($scope.connections.length > 0) {
                  pax.push({
                    pax: name,
                    center:
                      $scope.filterBillet[i].airport_code_from +
                      "-" +
                      $scope.connections[0].airportCodeTo,
                    cardNumber: $scope.bagagge,
                    sit: $scope.getSeat($scope.filterBillet[i]) + " ",
                    ticket_code: $scope.filterBillet[i].ticket_code,
                    baggage: $scope.filterBillet[i].baggage,
                    connection: $scope.filterBillet[i].connection,
                  });

                  for (var j = 1; j <= $scope.connections.length - 1; j++) {
                    pax.push({
                      pax: name,
                      center:
                        $scope.connections[j].airportCodeFrom +
                        " - " +
                        $scope.connections[j].airportCodeTo,
                      cardNumber: $scope.bagagge,
                      sit:
                        $scope.getConnectionSeat($scope.filterBillet[i], j) +
                        " ",
                      ticket_code: $scope.filterBillet[i].ticket_code,
                      baggage: $scope.filterBillet[i].baggage,
                      connection: $scope.filterBillet[i].connection,
                    });
                  }
                } else {
                  pax.push({
                    pax: name,
                    center:
                      $scope.filterBillet[i].airport_code_from +
                      "-" +
                      $scope.filterBillet[i].airport_code_to,
                    cardNumber: $scope.bagagge,
                    sit: $scope.getSeat($scope.filterBillet[i]) + " ",
                    ticket_code: $scope.filterBillet[i].ticket_code,
                    baggage: $scope.filterBillet[i].baggage,
                    connection: $scope.filterBillet[i].connection,
                  });
                }
              } else {
                pax.push({
                  pax: name,
                  center:
                    $scope.filterBillet[i].airport_code_from +
                    "-" +
                    $scope.filterBillet[i].airport_code_to,
                  cardNumber: $scope.bagagge,
                  sit: $scope.getSeat($scope.filterBillet[i]) + " ",
                  ticket_code: $scope.filterBillet[i].ticket_code,
                  baggage: $scope.filterBillet[i].baggage,
                  connection: $scope.filterBillet[i].connection,
                });
              }
            }
          }
          //console.log(pax);
          return pax;
        };

        $scope.getPaxLaTamReturn = function (
          airline,
          cards_id,
          flight,
          flightLocator
        ) {
          var pax = [];
          $scope.baggage = "";

          $scope.returnFlight = $filter("filter")(
            $scope.onlineflights,
            airline
          );

          for (var i in $scope.returnFlight) {
            if ($scope.returnFlight[i].is_newborn != "S") {
              if (
                $scope.returnFlight[i].cards_id == cards_id &&
                $scope.returnFlight[i].flight == flight &&
                $scope.returnFlight[i].flightLocator == flightLocator
              ) {
                var fullName = $scope.returnFlight[i].pax_name;
                if ($scope.returnFlight[i].paxLastName) {
                  fullName += " " + $scope.returnFlight[i].paxLastName;
                }
                if ($scope.returnFlight[i].paxAgnome) {
                  fullName += " " + $scope.returnFlight[i].paxAgnome;
                }

                var paxName = fullName.split(" ");
                var name = "";

                if (
                  paxName[paxName.length - 1] == "" ||
                  paxName[paxName.length - 1] == " "
                ) {
                  paxName.splice(paxName.length - 1, 1);
                }

                /*if (paxName.length > 2) {
				if ($scope.checkPaxName(paxName[paxName.length - 1])) {
				  paxName.splice(paxName.length - 1, 1);
				}
			  }*/

                for (var n in paxName) {
                  name += paxName[n] + " ";
                }

                if ($scope.returnFlight[i].baggage == 0) {
                  $scope.baggage = "";
                } else {
                  $scope.baggage = "23Kg";
                }

                if (
                  $scope.connectionsReturn &&
                  $scope.connectionsReturn != "" &&
                  $scope.connectionsReturn != null
                ) {
                  if ($scope.connectionsReturn.length > 0) {
                    pax.push({
                      pax: name,
                      center:
                        $scope.returnFlight[i].airport_code_from +
                        "-" +
                        $scope.connectionsReturn[0].airportCodeTo,
                      cardNumber: $scope.bagagge,
                      sit: $scope.getSeat($scope.returnFlight[i]) + " ",
                      ticket_code: $scope.returnFlight[i].ticket_code,
                      baggage: $scope.returnFlight[i].baggage,
                    });
                    for (
                      var j = 1;
                      j <= $scope.connectionsReturn.length - 1;
                      j++
                    ) {
                      pax.push({
                        pax: name,
                        center:
                          $scope.connectionsReturn[j].airportCodeFrom +
                          " - " +
                          $scope.connectionsReturn[j].airportCodeTo,
                        cardNumber: $scope.bagagge,
                        sit:
                          $scope.getConnectionSeat($scope.returnFlight[i], j) +
                          " ",
                        ticket_code: $scope.returnFlight[i].ticket_code,
                        baggage: $scope.returnFlight[i].baggage,
                      });
                    }
                  } else {
                    pax.push({
                      pax: name,
                      center:
                        $scope.returnFlight[i].airport_code_from +
                        "-" +
                        $scope.returnFlight[i].airport_code_to,
                      cardNumber: $scope.bagagge,
                      sit: $scope.getSeat($scope.returnFlight[i]) + " ",
                      ticket_code: $scope.returnFlight[i].ticket_code,
                      baggage: $scope.returnFlight[i].baggage,
                    });
                  }
                } else {
                  pax.push({
                    pax: name,
                    center:
                      $scope.returnFlight[i].airport_code_from +
                      "-" +
                      $scope.returnFlight[i].airport_code_to,
                    cardNumber: $scope.bagagge,
                    sit: $scope.getSeat($scope.returnFlight[i]) + " ",
                    ticket_code: $scope.returnFlight[i].ticket_code,
                    baggage: $scope.returnFlight[i].baggage,
                  });
                }
              }
            }
          }
          return pax;
        };

        $scope.getPaxReturn = function (
          airline,
          cards_id,
          flight,
          flightLocator
        ) {
          var pax = [];
          $scope.baggage = "";
          $scope.filterBillet = $filter("filter")(
            $scope.onlineflights,
            airline
          );
          for (var i in $scope.filterBillet) {
            if (
              $scope.filterBillet[i].cards_id == cards_id &&
              $scope.filterBillet[i].flight == flight &&
              $scope.filterBillet[i].flightLocator == flightLocator
            ) {
              var fullName = $scope.filterBillet[i].pax_name;
              if ($scope.filterBillet[i].paxLastName) {
                fullName += " " + $scope.filterBillet[i].paxLastName;
              }
              if ($scope.filterBillet[i].paxAgnome) {
                fullName += " " + $scope.filterBillet[i].paxAgnome;
              }

              var paxName = fullName.split(" ");
              var name = "";

              if (
                paxName[paxName.length - 1] == "" ||
                paxName[paxName.length - 1] == " "
              ) {
                paxName.splice(paxName.length - 1, 1);
              }

              if (paxName.length > 2) {
                if ($scope.checkPaxName(paxName[paxName.length - 1])) {
                  paxName.splice(paxName.length - 1, 1);
                }
              }

              for (var n in paxName) {
                name += paxName[n] + " ";
              }

              if ($scope.filterBillet[i].baggage == 0) {
                $scope.baggage = "";
              } else {
                $scope.baggage = "23Kg";
              }

              if ($scope.connectionsReturn.length > 0) {
                pax.push({
                  pax: name,
                  center:
                    $scope.filterBillet[i].airport_code_from +
                    "-" +
                    $scope.connectionsReturn[0].airportCodeTo,
                  cardNumber: $scope.baggage,
                  sit: " " + $scope.getSeat($scope.filterBillet[i]) + " ",
                });
                for (var j = 1; j <= $scope.connectionsReturn.length - 1; j++) {
                  pax.push({
                    pax: "Conexão",
                    center:
                      $scope.connectionsReturn[j].airportCodeFrom +
                      " - " +
                      $scope.connectionsReturn[j].airportCodeTo,
                    cardNumber: $scope.baggage,
                    sit:
                      " " +
                      $scope.getConnectionSeat($scope.filterBillet[i], j) +
                      " ",
                  });
                }
              } else {
                pax.push({
                  pax: name,
                  center:
                    $scope.filterBillet[i].airport_code_from +
                    "-" +
                    $scope.filterBillet[i].airport_code_to,
                  cardNumber: $scope.baggage,
                  sit: " " + $scope.getSeat($scope.filterBillet[i]) + " ",
                });
              }
            }
          }
          return pax;
        };

        // $scope.search = function() {
        //   $scope.filteredOnlineOrders = $filter('filter')($scope.onlineorder, $scope.searchKeywords);
        //   return $scope.onFilterChange();
        // };

        $scope.order = function (rowName) {
          if ($scope.row === rowName) {
            return;
          }
          $scope.row = rowName;
          $scope.filteredOnlineOrders = $filter("orderBy")(
            $scope.onlineorder,
            rowName
          );
          return $scope.onOrderChange();
        };

        $scope.search_flight = function () {
          $scope.filteredonlineflights = $filter("filter")(
            $scope.onlineflights,
            $scope.searchKeywords
          );
        };

        $scope.order_flight = function (rowName) {
          if ($scope.row === rowName) {
            return;
          }
          $scope.row = rowName;
          $scope.filteredonlineflights = $filter("orderBy")(
            $scope.onlineflights,
            rowName
          );
        };

        $scope.search_miles = function () {
          $scope.currentMiles = $filter("filter")(
            $scope.flight_miles,
            $scope.searchKeywordsMiles
          );
        };

        $scope.order_miles = function (rowName) {
          $scope.searchMilesOrder = rowName;
          $scope.searchMilesOrderDown = undefined;
          $scope.currentPageMiles = 1;
          $scope.loadOrderMilesFunction();
        };

        $scope.order_miles_down = function (rowName) {
          $scope.searchMilesOrder = undefined;
          $scope.searchMilesOrderDown = rowName;
          $scope.currentPageMiles = 1;
          $scope.loadOrderMilesFunction();
        };

        $scope.onOrderMilesChange = function () {
          $scope.selectMiles(1);
          return ($scope.currentPageMiles = 1);
        };

        $scope.back = function () {
          $scope.tabindex = $scope.tabindex - 1;
          $scope.searchKeywords = "";
        };

        $scope.next = function () {
          $scope.tabindex = $scope.tabindex + 1;
        };

        $scope.getStatusOrder = function (order) {
          if (order.status == "ESPERA") {
            return "Espera";
          }
          if (order.commercialStatus && order.status != "EMITIDO") {
            if (order.status == "PRIORIDADE") {
              return "PRIORIDADE";
            }
            return "Liberado Comercial";
          }

          switch (order.status) {
            case "PENDENTE":
              return "Pendente";
            case "RESERVA":
              return "Reserva";
            case "EMITIDO":
              return "Emitido";
            case "PRIORIDADE":
              return "Pendente";
            case "VOEB2B":
              return "VOEB2B";
            case "ENVIADO":
              return "Enviado";
            case "CANCELADO":
              return "Cancelado";
            case "FALHA EMISSAO":
              return "FALHA EMISSAO";
            case "ESPERA":
              return "Espera";
            case "VERIFICADO":
              return "Verificado";
            case "ESPERA VLR":
              return "Espera comercial - Valor";
            case "ESPERA LIM":
              return "Espera Comercial - Limite";
            case "ESPERA PGTO":
              return "Espera pagamento";
            case "ESPERA LIM VLR":
              return "Comercial Limite/Valor";
            case "DADOS_PASSAGEIRO_INVALIDO":
              return "DADOS PASSAGEIRO INVALIDO";
            case "SITE_CIA_FORA_AR":
              return "SITE CIA FORA AR";
            case "RETARIFADA":
              return "RETARIFADA";
            case "ANT":
              return "Antecipado";
            case "BLOQ":
              return "Bloqueado";
            case "ANT BLOQ":
              return "Antecipado/Bloqueado";
          }
        };

        $scope.findColor = function (order) {
          switch (order.status) {
            case "PENDENTE":
              return "";
            case "PRIORIDADE":
              return "";
            case "VOEB2B":
              return "";
            case "ESPERA VLR":
              return "";
            case "ESPERA LIM VLR":
              return "";
            case "DADOS_PASSAGEIRO_INVALIDO":
              return "";
            case "SITE_CIA_FORA_AR":
              return "";
            case "RETARIFADA":
              return "";
            case "ANT":
              return "";
            case "BLOQ":
              return "";
            case "ESPERA LIM":
              return "";
            case "ESPERA PGTO":
              return "";
            case "ANT BLOQ":
              return "";
            case "EMITIDO":
              if (order.showSms && !order.sms) {
                return "#FFA26D";
              }
              return "#9DCE9D";
            case "ENVIADO":
              return "#9DCE9D";
            case "RESERVA":
              return "#9BB3DC";
            case "CANCELADO":
              return "#F38E8E";
            case "FALHA EMISSAO":
              return "#F38E8E";
            case "VERIFICADO":
              if (order.showSms && !order.sms) {
                return "#FFA26D";
              }
          }
        };

        $scope.findClass = function (onlineflight) {
          if (
            onlineflight.flightLocator == undefined ||
            onlineflight.flightLocator == "" ||
            ((onlineflight.airline == "TAM" ||
              onlineflight.airline == "LATAM") &&
              (onlineflight.ticket_code == undefined ||
                onlineflight.ticket_code == ""))
          )
            return "btn btn-danger smallBtn";
          else return "btn btn-line-info smallBtn";
        };

        $scope.orderTag = function (status) {
          switch (status) {
            case "PENDENTE":
              return "label label-warning";
            case "ESPERA VLR":
              return "label label-default";
            case "PRIORIDADE":
              return "label label-warning";
            case "VOEB2B":
              return "label label-purple";
            case "ESPERA PGTO":
              return "label label-default";
            case "ESPERA LIM":
              return "label label-default";
            case "RESERVA":
              return "label label-info";
            case "ENVIADO":
              return "label label-success";
            case "EMITIDO":
              return "label label-success";
            case "VERIFICADO":
              return "label label-success";
            case "CANCELADO":
              return "label label-danger";
            case "FALHA EMISSAO":
              return "label label-danger";
            case "ESPERA":
              return "label label-default";
            case "ESPERA LIM VLR":
              return "label label-default";
            case "DADOS_PASSAGEIRO_INVALIDO":
              return "label label-default";
            case "SITE_CIA_FORA_AR":
              return "label label-default";
            case "RETARIFADA":
              return "label label-default";
            case "ANT":
              return "label label-default";
            case "BLOQ":
              return "label label-default";
            case "ANT BLOQ":
              return "label label-default";
          }
        };

        $scope.testGroup = function (order) {
          var test_group = [
            "adm@onemilhas.com.br",
          ];
          if(order.notificationurl != null){
            if (order.notificationurl.includes("skymilhas")) {
              return test_group.includes(order.client_email.toLowerCase());
            }
          }

          return false;
        };

        $scope.isPriority = function (order) {
          if (
            order.status == "PENDENTE PRIORIDADE" ||
            order.status == "PRIORIDADE"
          ) {
            return true;
          } else {
            return false;
          }
        };

        $scope.isFlightToday = function (order) {
          if (
            order.status == "EMITIDO" ||
            order.status == "VERIFICADO" ||
            order.firstBoardingDate == ""
          ) {
            return false;
          }
          var date = new Date(order.firstBoardingDate);
          var today = new Date();
          if (
            date.getFullYear() == today.getFullYear() &&
            date.getMonth() == today.getMonth() &&
            date.getDate() == today.getDate()
          ) {
            return true;
          }

          return false;
        };

        $scope.isFlightTomorrow = function (order) {
          if (
            order.status == "EMITIDO" ||
            order.status == "VERIFICADO" ||
            order.firstBoardingDate == ""
          ) {
            return false;
          }
          var date = new Date(order.firstBoardingDate);
          var today = new Date();
          today.setDate(today.getDate() + 1);
          if (
            date.getFullYear() == today.getFullYear() &&
            date.getMonth() == today.getMonth() &&
            date.getDate() == today.getDate()
          ) {
            return true;
          }

          return false;
        };

        $scope.orderTagClient = function (order) {
          if (order.check_2 == true) {
            return "label label-info";
          }
          if (order.commercialStatus) {
            return "label label-warning";
          }
          switch (order.status) {
            case "PENDENTE":
              return "label label-warning";
            case "ESPERA VLR":
              return "label label-default";
            case "PRIORIDADE":
              return "label label-warning";
            case "VOEB2B":
              return "label label-purple";
            case "ESPERA PGTO":
              return "label label-default";
            case "ESPERA LIM":
              return "label label-default";
            case "RESERVA":
              return "label label-info";
            case "ENVIADO":
              return "label label-success";
            case "EMITIDO":
              return "label label-success";
            case "VERIFICADO":
              return "label label-success";
            case "CANCELADO":
              return "label label-danger";
            case "FALHA EMISSAO":
              return "label label-danger";
            case "ESPERA":
              return "label label-default";
            case "ESPERA LIM VLR":
              return "label label-default";
            case "DADOS_PASSAGEIRO_INVALIDO":
              return "label label-default";
            case "SITE_CIA_FORA_AR":
              return "label label-default";
            case "RETARIFADA":
              return "label label-default";
            case "ANT":
              return "label label-default";
            case "BLOQ":
              return "label label-default";
            case "ANT BLOQ":
              return "label label-default";
          }
        };

        $scope.orderMiles = function (mile) {
          switch (mile.priority) {
            case "-1":
              return "label label-success";
            case "0":
              return "label label-primary";
            case "1":
              return "label label-danger";
            case "2":
              return "label label-warning";
            default:
              return "";
          }
        };

        $scope.formatNumber = function (
          number,
          decimalsLength,
          decimalSeparator,
          thousandSeparator
        ) {
          return $rootScope.formatNumber(
            number,
            decimalsLength,
            decimalSeparator,
            thousandSeparator
          );
        };

        $scope.numPerPageOpt = [10, 30, 50, 100, 200, 500];
        $scope.numPerPage = $scope.numPerPageOpt[2];
        $scope.currentPage = 1;
        $scope.currentPageOnlineOrder = [];

        $scope.numPerPageOptMiles = [10, 30, 50, 100, 200];
        $scope.numPerPageMiles = $scope.numPerPageOptMiles[2];
        $scope.currentPageMiles = 1;
        $scope.currentMiles = [];

        $scope.selectMiles = function (page) {
          var end, start;
          start = (page - 1) * $scope.numPerPageMiles;
          end = start + $scope.numPerPageMiles;
          return ($scope.currentMiles = $scope.filteredflight_miles.slice(
            start,
            end
          ));
        };

        $scope.onNumPerPageChangeMiles = function () {
          $scope.selectMiles(1);
          return ($scope.currentPageMiles = 1);
        };

        $scope.atualization = function () {
          if (
            document.getElementById("online_page") &&
            ($scope.tabindex === 0 ||
              $scope.tabindex === 99 ||
              $scope.tabindex === 6) &&
            $scope.atualizations == true
          ) {
            $scope.loadOnlineOrder();
          }
        };

        $scope.$watch("[tabindex, atualizations]", function () {
          if (
            ($scope.tabindex === 0 ||
              $scope.tabindex === 99 ||
              $scope.tabindex === 6) &&
            $scope.atualizations == true
          ) {
            $scope.atualization();
            $scope.reserve = false;
          }
          if ($scope.main.dealer) {
            return;
          }
          if ($scope.tabindex != 6 && $scope.tabindex != 99) {
            if ($scope.tabindex == 0) {
              $rootScope.socket.emit("order_update", {
                order_id: undefined,
                name: $scope.main.name,
              });
            } else {
              if ($scope.tabindex != 99 && $scope.tabindex != 6) {
                $rootScope.socket.emit("order_update", {
                  order_id: $scope.selected.id,
                  name: $scope.main.name,
                });
              }
            }
          }
          if ($scope.tabindex == 4) {
            $scope.loadPaxesUsedCards();
          }
        });

        $scope.autoUpdateName = function () {
          setTimeout(function () {
            // if ($scope.myOrder) {
            //   if ($scope.selected.id) {
            //     $scope.myOrder = $scope.selected.id;
            //   }
            // }
            if ($scope.selected.id) {
              if ($scope.tabindex != 99 && $scope.tabindex != 6) {
                $rootScope.socket.emit("order_update", {
                  order_id: $scope.selected.id,
                  name: $scope.main.name,
                });
              }
            } else {
              if ($scope.tabindex != 99 && $scope.tabindex != 6) {
                $rootScope.socket.emit("order_update", {
                  order_id: undefined,
                  name: $scope.main.name,
                });
              }
            }
            return $scope.autoUpdateName();
          }, 5000);
        };

        init = function () {
          $scope.tabindex = 0;
          if ($scope.main.dealer) {
            $scope.tabindex = 99;
          } else if ($scope.main.commercial) {
            $scope.tabindex = 6;
          }
          $scope.checkValidRoute();
          if ($scope.main.id == "") {
            return;
          }
          $scope.myOrder = undefined;
          if (!$scope.main.dealer) {
            $scope.autoUpdateName();
          }
          $scope.wsale = {};
          $("#discount").number(true, 2, ",", ".");
          $scope.milesMoney = false;
          $scope.third = false;
          $scope.atualizations = true;
          $scope.selected = {};
          $rootScope.modalOpen = false;
          $scope.multiDownloads = false;

          $.post(
            "../backend/application/index.php?rota=/loadAirline",
            {},
            function (result) {
              $rootScope.airlines = jQuery.parseJSON(result).dataset;
            }
          );

          $scope.watchDogCards();

          $scope.uploader = new FileUploader();
          $scope.uploader_billets = new FileUploader();
          $scope.uploader.url =
            "../backend/application/index.php?rota=/saveFile";
          $scope.uploader_billets.url =
            "../backend/application/index.php?rota=/saveBilletOrder";

          $scope.uploader.autoUpload = true;
          $scope.uploader.filters.push({
            name: "customFilter",
            fn: function (item /*{File|FileLikeObject}*/, options) {
              return this.queue.length < 18;
            },
          });

          $scope.uploader_billets.autoUpload = true;
          $scope.uploader_billets.filters.push({
            name: "customFilter",
            fn: function (item /*{File|FileLikeObject}*/, options) {
              return this.queue.length < 18;
            },
          });

          if (!$scope.main.dealer && !$scope.main.wizarSaleEvent) {
            $rootScope.socket.on("updateOrders", function (data) {
              if ($scope.atualizations) {
                $scope.onlineorders = data.orders.orders;
                $scope.$digest();
              }
            });
            $rootScope.socket.on("order_update", function (data) {
              if (data.name == $scope.main.name) {
                $scope.myOrder = data.order_id;
              }
              if (data.order_id) {
                $scope.addSessionName(data.order_id, data.name);
              } else {
                $scope.removeSessionName(data.name);
              }
              $scope.$digest();
            });

            $rootScope.socket.on("globalOrders", function (data) {
              if ($scope.atualizations) {
                $scope.onlineorders = data.orders;
                $scope.$digest();
              }
            });
          } else if ($scope.main.dealer) {
            $rootScope.socket.on("updateOrdersDealer", function (data) {
              $scope.onlineorders = data.orders.orders;
              $scope.$digest();
            });
          }
        };

        $scope.addSessionName = function (order_id, name) {
          var tempOrders = $filter("filter")($scope.onlineorders, order_id);
          for (var idOrder in tempOrders) {
            var newSessionName = "";
            var and = "";
            if (tempOrders[idOrder].userSession) {
              var sessionName = tempOrders[idOrder].userSession.split(";");
              for (var idName in sessionName) {
                if (sessionName[idName] != name) {
                  newSessionName += and + sessionName[idName];
                  and = ";";
                }
              }
            }
            tempOrders[idOrder].userSession = newSessionName + and + name;
          }
        };

        $scope.removeSessionName = function (name) {
          var tempOrders = $filter("filter")($scope.onlineorders, name);
          for (var idOrder in tempOrders) {
            var newSessionName = "";
            var and = "";
            if (tempOrders[idOrder].userSession) {
              var sessionName = tempOrders[idOrder].userSession.split(";");
              for (var idName in sessionName) {
                if (sessionName[idName] != name) {
                  newSessionName += and + sessionName[idName];
                  and = ";";
                }
              }
            }
            tempOrders[idOrder].userSession = newSessionName;
          }
        };

        $scope.setStatusOrder = function (order, status) {
          if (!order) {
            order = $rootScope.onlineOrder;
          }
          $.post(
            "../backend/application/index.php?rota=/setStatusOrder",
            { hashId: $scope.session.hashId, data: order, status: status },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.tabindex = 0;
            }
          );
        };

        $scope.backToList = function () {
          $scope.tabindex = 0;
        };

        $scope.loadOnlineOrder = function () {
          cfpLoadingBar.start();
          if ($scope.tabindex == 3) {
            return;
          }
          if (
            $scope.tabindex == 0 ||
            $scope.tabindex == 99 ||
            $scope.tabindex == 6
          ) {
            $scope.selected = {};
          }
          $.post(
            "../backend/application/index.php?rota=/loadOnlineOrder",
            {
              page: $scope.currentPage,
              numPerPage: $scope.numPerPage,
              searchKeywords: $scope.searchKeywords,
              order: $scope.searchOrder,
              orderDown: $scope.searchOrderDown,
              data: $scope.filter,
              main: $scope.main
            },
            function (result) {
              $scope.onlineorders = jQuery.parseJSON(result).dataset.orders;
              $scope.totalData = jQuery.parseJSON(result).dataset.total;
              cfpLoadingBar.complete();
            }
          );
        };

        $scope.taxByFlight = function (onlineflight) {
          var tax = 0;
          if (onlineflight.is_newborn != "S") {
            //tax += onlineflight.tax_billet;
            tax += onlineflight.tax;
          }
          tax += onlineflight.du_tax;
          return tax;
        };

        $scope.checkTimeFlight = function (args) {
          if (args == undefined) {
            var date = new Date();
            date.setDate(date.getDate() + 2);
            for (var i = 0; $scope.onlineflights.length > i; i++) {
              if (
                $scope.onlineflights[i].airline == "GOL" &&
                new Date($scope.onlineflights[i].boarding_date) < date
              ) {
                logger.logError("Voo GOL com embarque inferior a 48Hrs!");
                $scope.previousIndex = 0;
                $scope.tabindex = 0;
                $rootScope.$emit("openPermission", {
                  main: $scope.$parent.$parent.main,
                  hashId: $scope.$parent.$parent.session.hashId,
                });
                $scope.$apply();
                return;
              }
            }
          }
        };

        $scope.getAllCheckend = function () {
          if ($scope.selected) {
            if (
              $scope.selected.status == "EMITIDO" ||
              $scope.selected.status == "VERIFICADO"
            ) {
              for (var i in $scope.onlineflights) {
                if ($scope.onlineflights[i].saleChecked == false) return false;
              }
              return true;
            }
            return false;
          }
          return false;
        };

        $scope.checkAllAsCheck = function () {
          var check = $scope.getAllCheckend();
          for (var i in $scope.onlineflights) {
            $scope.onlineflights[i].saleChecked == !check;
          }
        };

        $scope.checkSale = function (flight) {
          $.post(
            "../backend/application/index.php?rota=/saveSaleCheck",
            { data: $scope.selected, sale: flight },
            function (result) {
              logger.logSuccess("Salvo com sucesso");
              $scope.loadOnlineFlight();
            }
          );
        };

        $scope.checkAllSales = function () {
          $.post(
            "../backend/application/index.php?rota=/saveSaleCheckAll",
            { data: $scope.selected },
            function (result) {
              logger.logSuccess("Salvo com sucesso");
              $scope.loadOnlineFlight();
            }
          );
        };

        $scope.checkSale2 = function (flight) {
          $.post(
            "../backend/application/index.php?rota=/saveSaleDoubleCheck",
            { data: $scope.selected, sale: flight },
            function (result) {
              logger.logSuccess("Salvo com sucesso");
              $scope.loadOnlineFlight();
            }
          );
        };

        $scope.checkAllSale2 = function () {
          $.post(
            "../backend/application/index.php?rota=/saveSaleDoubleCheckAll",
            { data: $scope.selected },
            function (result) {
              logger.logSuccess("Salvo com sucesso");
              $scope.loadOnlineFlight();
            }
          );
        };

        $scope.checkSMS = function (flight) {
          $.post(
            "../backend/application/index.php?rota=/saveSaleSMS",
            {
              hashId: $scope.session.hashId,
              data: $scope.selected,
              sale: flight,
            },
            function (result) {
              logger.logSuccess("Salvo com sucesso");
              $scope.loadOnlineFlight();
            }
          );
        };

        $scope.findMilesOrder = function () {
          var miles = 0;
          for (var i in $scope.onlineorders) {
            miles += $scope.onlineorders[i].miles_used;
          }
          return $rootScope.formatNumber(miles, 0);
        };

        $scope.loadOnlineFlight = function (args) {
          $scope.args = args;
          $scope.selected.startedDate = new Date();
          $scope.tabindex = 3;
          $scope.showNextStep = false;
          cfpLoadingBar.start();
          $scope.commercial_free = {};
          $.post(
            "../backend/application/index.php?rota=/loadCommercialStatusOrder",
            { data: $scope.selected },
            function (result) {
              $scope.commercial_free = jQuery.parseJSON(result).dataset;
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadOnlineFlight",
            { hashId: $scope.session.hashId, data: $scope.selected },
            function (result) {
              $scope.onlineflights = jQuery.parseJSON(result).dataset;
              if (args) {
                for (var i in $scope.onlineflights) {
                  $scope.onlineflights[i].userEmail = $scope.main.email;
                }
              }
              $scope.filteredonlineflights = jQuery.parseJSON(result).dataset;
              // $scope.search();
              $scope.resume_flights = $filter("filter")(
                $scope.onlineflights,
                "FILTER_FLIGHT"
              );

              $scope.resume_paxs = $filter("filter")(
                $scope.onlineflights,
                "FILTER_PAX"
              );

              if (
                $scope.selected.status == "EMITIDO" ||
                $scope.selected.status == "ENVIADO" ||
                $scope.selected.status == "VERIFICADO"
              ) {
                $.post(
                  "../backend/application/index.php?rota=/loadCardFlight",
                  { hashId: $scope.session.hashId, data: $scope.onlineflights },
                  function (result) {
                    $scope.sale = jQuery.parseJSON(result).dataset;
                    cfpLoadingBar.complete();

                    $scope.resume_flights = angular.copy($scope.resume_flights);

                    for (var i = 0; $scope.onlineflights.length > i; i++) {
                      for (var j = 0; $scope.sale.length > j; j++) {
                        if (
                          $scope.sale[j].subClientEmail != undefined &&
                          $scope.sale[j].subClientEmail != null
                        ) {
                          $scope.selected.subClientEmail =
                            $scope.sale[j].subClientEmail;
                        }

                        if (
                          $scope.sale[j].pax_id &&
                          $scope.sale[j].online_flight_id
                        ) {
                          if (
                            $scope.sale[j].pax_id ==
                              $scope.onlineflights[i].pax_id &&
                            $scope.sale[j].online_flight_id ==
                              $scope.onlineflights[i].id
                          ) {
                            $scope.onlineflights[i].card_number =
                              $scope.sale[j].cardNumber;
                            $scope.onlineflights[i].miles_used =
                              $scope.sale[j].miles_used;
                            $scope.onlineflights[i].flightLocator =
                              $scope.sale[j].flightLocator;
                            $scope.onlineflights[i].du_tax =
                              $scope.sale[j].duTax;
                            $scope.onlineflights[i].discount =
                              $scope.sale[j].discount;
                            $scope.onlineflights[i].providerName =
                              $scope.sale[j].providerName;
                            $scope.onlineflights[i].provider_phone =
                              $scope.sale[j].provider_phone;
                            $scope.onlineflights[i].tax_card =
                              $scope.sale[j].tax_card;
                            $scope.onlineflights[i].tax_cardType =
                              $scope.sale[j].tax_cardType;
                            $scope.onlineflights[i].tax_providerName =
                              $scope.sale[j].tax_providerName;
                            $scope.onlineflights[i].user = $scope.sale[j].user;
                            $scope.onlineflights[i].ticket_code =
                              $scope.sale[j].ticket_code;
                            $scope.onlineflights[i].processing_time =
                              $scope.sale[j].processing_time;
                            $scope.onlineflights[i].cards_id =
                              $scope.sale[j].cards_id;
                            $scope.onlineflights[i].saleChecked =
                              $scope.sale[j].saleChecked;
                            $scope.onlineflights[i].saleCheckedDate =
                              $scope.sale[j].saleCheckedDate;
                            $scope.onlineflights[i].sale_id =
                              $scope.sale[j].sale_id;
                            $scope.onlineflights[i].amountPaid =
                              $scope.sale[j].amountPaid;
                            $scope.onlineflights[i].status =
                              $scope.sale[j].status;
                            $scope.onlineflights[
                              i
                            ].access_password = $scope.decript(
                              $scope.sale[j].access_password
                            );
                            $scope.onlineflights[
                              i
                            ].recovery_password = $scope.decript(
                              $scope.sale[j].recovery_password
                            );
                            $scope.selected.issuing = $scope.sale[j].issuing;
                            $scope.onlineflights[i].partnerReservationCode =
                              $scope.sale[j].partnerReservationCode;
                            $scope.onlineflights[i].sms = $scope.sale[j].sms;
                            if ($scope.sale[j].money > 0) {
                              $scope.onlineflights[i].money =
                                $scope.sale[j].money;
                            }
                            $scope.onlineflights[i].card_type =
                              $scope.sale[j].card_type;
                            $scope.onlineflights[i].phoneNumberAirline =
                              $scope.sale[j].phoneNumberAirline;
                            $scope.onlineflights[i].celNumberAirline =
                              $scope.sale[j].celNumberAirline;
                            $scope.onlineflights[i].tax = $scope.sale[j].tax;
                            $scope.onlineflights[i].tax_billet =
                              $scope.sale[j].tax_billet;
                            $scope.onlineflights[i].baggage =
                              $scope.sale[j].baggage;
                            $scope.onlineflights[i].class =
                              $scope.sale[j].class;
                            $scope.onlineflights[i].saleChecked2 =
                              $scope.sale[j].saleChecked2;
                            $scope.onlineflights[i].saleCheckedDate2 =
                              $scope.sale[j].saleCheckedDate2;
                            $scope.onlineflights[i].taxOnlinePayment =
                              $scope.sale[j].taxOnlinePayment;
                            $scope.onlineflights[i].taxOnlineValidation =
                              $scope.sale[j].taxOnlineValidation;
                            $scope.onlineflights[i].baggage_price =
                              $scope.sale[j].baggage_price;
                            $scope.onlineflights[i].special_seat =
                              $scope.sale[j].special_seat;
                          }
                        } else {
                          if (
                            $scope.sale[j].flight ==
                              $scope.onlineflights[i].flight &&
                            $scope.sale[j].pax_name ==
                              $scope.onlineflights[i].pax_name &&
                            $scope.sale[j].online_flight_id ==
                              $scope.onlineflights[i].id
                          ) {
                            $scope.onlineflights[i].card_number =
                              $scope.sale[j].cardNumber;
                            $scope.onlineflights[i].miles_used =
                              $scope.sale[j].miles_used;
                            $scope.onlineflights[i].flightLocator =
                              $scope.sale[j].flightLocator;
                            $scope.onlineflights[i].du_tax =
                              $scope.sale[j].duTax;
                            $scope.onlineflights[i].discount =
                              $scope.sale[j].discount;
                            $scope.onlineflights[i].providerName =
                              $scope.sale[j].providerName;
                            $scope.onlineflights[i].provider_phone =
                              $scope.sale[j].provider_phone;
                            $scope.onlineflights[i].tax_card =
                              $scope.sale[j].tax_card;
                            $scope.onlineflights[i].tax_cardType =
                              $scope.sale[j].tax_cardType;
                            $scope.onlineflights[i].tax_providerName =
                              $scope.sale[j].tax_providerName;
                            $scope.onlineflights[i].user = $scope.sale[j].user;
                            $scope.onlineflights[i].ticket_code =
                              $scope.sale[j].ticket_code;
                            $scope.onlineflights[i].processing_time =
                              $scope.sale[j].processing_time;
                            $scope.onlineflights[i].cards_id =
                              $scope.sale[j].cards_id;
                            $scope.onlineflights[i].saleChecked =
                              $scope.sale[j].saleChecked;
                            $scope.onlineflights[i].saleCheckedDate =
                              $scope.sale[j].saleCheckedDate;
                            $scope.onlineflights[i].sale_id =
                              $scope.sale[j].sale_id;
                            $scope.onlineflights[i].amountPaid =
                              $scope.sale[j].amountPaid;
                            $scope.onlineflights[i].status =
                              $scope.sale[j].status;
                            $scope.onlineflights[
                              i
                            ].access_password = $scope.decript(
                              $scope.sale[j].access_password
                            );
                            $scope.onlineflights[
                              i
                            ].recovery_password = $scope.decript(
                              $scope.sale[j].recovery_password
                            );
                            $scope.selected.issuing = $scope.sale[j].issuing;
                            $scope.onlineflights[i].partnerReservationCode =
                              $scope.sale[j].partnerReservationCode;
                            $scope.onlineflights[i].sms = $scope.sale[j].sms;
                            if ($scope.sale[j].money > 0) {
                              $scope.onlineflights[i].money =
                                $scope.sale[j].money;
                            }
                            $scope.onlineflights[i].card_type =
                              $scope.sale[j].card_type;
                            $scope.onlineflights[i].phoneNumberAirline =
                              $scope.sale[j].phoneNumberAirline;
                            $scope.onlineflights[i].celNumberAirline =
                              $scope.sale[j].celNumberAirline;
                            $scope.onlineflights[i].tax = $scope.sale[j].tax;
                            $scope.onlineflights[i].tax_billet =
                              $scope.sale[j].tax_billet;
                            $scope.onlineflights[i].baggage =
                              $scope.sale[j].baggage;
                            $scope.onlineflights[i].class =
                              $scope.sale[j].class;
                            $scope.onlineflights[i].saleChecked2 =
                              $scope.sale[j].saleChecked2;
                            $scope.onlineflights[i].saleCheckedDate2 =
                              $scope.sale[j].saleCheckedDate2;
                            $scope.onlineflights[i].taxOnlinePayment =
                              $scope.sale[j].taxOnlinePayment;
                            $scope.onlineflights[i].taxOnlineValidation =
                              $scope.sale[j].taxOnlineValidation;
                            $scope.onlineflights[i].baggage_price =
                              $scope.sale[j].baggage_price;
                            $scope.onlineflights[i].special_seat =
                              $scope.sale[j].special_seat;
                          }
                        }
                      }
                    }
                    $.post(
                      "../backend/application/index.php?rota=/loadCardsData",
                      { data: $scope.onlineflights, type: "SALEDONE", clear_session: "false" },
                      function (result) {
                        $scope.cards = jQuery.parseJSON(result).dataset;
                        cfpLoadingBar.complete();
                        $scope.cardPassword();
                        $scope.tabindex = 4;
                        $scope.$apply();
                      }
                    );
                  }
                );

                $.post(
                  "../backend/application/index.php?rota=/incodde/checkOrderBot",
                  { order: $scope.selected },
                  function (result) {
                    $scope.robotLog = jQuery.parseJSON(result).dataset;
                  }
                );
              } else {
                if ($scope.onlineflights[0].card_number != "") {
                  $.post(
                    "../backend/application/index.php?rota=/loadCardsData",
                    { data: $scope.onlineflights, clear_session: "false"  },
                    function (result) {
                      $scope.cards = jQuery.parseJSON(result).dataset;
                      $scope.cardPassword();
                    }
                  );
                }
                $.post(
                  "../backend/application/index.php?rota=/loadClientName",
                  { data: $scope.selected },
                  function (result) {
                    $scope.response = jQuery.parseJSON(result).dataset;
                    cfpLoadingBar.complete();
                    if ($scope.response != undefined) {
                      if (
                        $scope.response.status == "Coberto" &&
                        $scope.response.paymentType == "Coberto"
                      ) {
                        $scope.selected.early_covered = true;
                      }
                      $scope.selected.subClientEmail =
                        $scope.response.subClientEmail;
                      if ($scope.check48hours() && args == undefined) {
                        $scope.checkTimeFlight(args);
                        $scope.previousIndex = 0;
                        $scope.tabindex = 0;
                      } else if (
                        $scope.response.status == "Bloqueado" &&
                        args == undefined
                      ) {
                        logger.logError(
                          "Cliente Bloqueado! - Favor consultar o Financeiro"
                        );
                        $scope.previousIndex = 0;
                        $scope.tabindex = 0;
                        $scope.$emit("openPermission", {
                          main: $scope.$parent.$parent.main,
                        });
                      } else if (
                        $scope.response.status == "Antecipado/Bloqueado" &&
                        args == undefined
                      ) {
                        logger.logError(
                          "Cliente Antecipado/Bloqueado! - Favor consultar o Financeiro"
                        );
                        $scope.previousIndex = 0;
                        $scope.tabindex = 0;
                        $scope.$emit("openPermission", {
                          main: $scope.$parent.$parent.main,
                        });
                        return;
                      } else if (
                        $scope.response.status == "Pendente" &&
                        args == undefined
                      ) {
                        logger.logError(
                          "Cliente Pendente! - Favor consultar o Comercial"
                        );
                        $scope.previousIndex = 0;
                        $scope.tabindex = 0;
                        $scope.$emit("openPermission", {
                          main: $scope.$parent.$parent.main,
                        });
                        return;
                      } else if (
                        $scope.response.paymentType == "Antecipado" &&
                        args == undefined
                      ) {
                        if (
                          $scope.response.total_credits -
                            $scope.selected.total_cost >=
                          0
                        ) {
                          var modalInstance;
                          modalInstance = $modal.open({
                            templateUrl: "app/modals/modal_confirmation.html",
                            controller: "ModalConfirmationCtrl",
                            resolve: {
                              header: function () {
                                return (
                                  "Cliente Antecipado! Possui Crédito de R$" +
                                  $rootScope.formatNumber(
                                    $scope.response.total_credits
                                  )
                                );
                              },
                            },
                          });
                          modalInstance.result.then(function (filterSales) {
                            $scope.wsale.client = $scope.response.client_name;
                            $scope.selected.client_name = $scope.wsale.client;
                            $scope.selected.subClientEmail =
                              $scope.response.subClientEmail;
                            $scope.previousIndex = $scope.tabindex;
                            $scope.$apply();
                            $scope.checkTimeFlight(args);
                            $scope.showNextStep = true;

                            $.post(
                              "../backend/application/index.php?rota=/incodde/checkOrderBot",
                              { order: $scope.selected },
                              function (result) {
                                $scope.robotLog = jQuery.parseJSON(
                                  result
                                ).dataset;
                              }
                            );
                          });
                        } else {
                          logger.logError(
                            "Cliente Antecipado! - Favor consultar o Financeiro"
                          );
                          $scope.previousIndex = 0;
                          $scope.tabindex = 0;
                          $scope.$emit("openPermission", {
                            main: $scope.$parent.$parent.main,
                          });
                          return;
                        }
                      } else if (
                        $scope.response.partner_limit == "true" &&
                        args == undefined &&
                        $scope.selected.commercialStatus == false
                      ) {
                        logger.logError(
                          "Cliente atingiu limite de emissões! Limite: " +
                            $scope.response.totalLimit +
                            " | Valor usado: " +
                            $scope.response.usedValue +
                            " - Favor consultar o Financeiro"
                        );
                        $scope.previousIndex = 0;
                        $scope.tabindex = 0;
                        $scope.$emit("openPermission", {
                          main: $scope.$parent.$parent.main,
                        });
                        return;
                      } else if (
                        $scope.response.totalLimit > 0 &&
                        $scope.selected.total_cost + $scope.response.usedValue >
                          $scope.response.totalLimit &&
                        args == undefined
                      ) {
                        logger.logError(
                          "Esta OP excederá o limite de emissões do cliente! Limite: " +
                            $scope.response.totalLimit +
                            " | Valor usado: " +
                            $scope.response.usedValue +
                            " | Valor da emissão: " +
                            $scope.selected.total_cost +
                            " - Favor consultar o Financeiro"
                        );
                        $scope.previousIndex = 0;
                        $scope.tabindex = 0;
                        $scope.$emit("openPermission", {
                          main: $scope.$parent.$parent.main,
                        });
                        return;
                      } else {
                        $scope.wsale.client = $scope.response.client_name;
                        $scope.wsale.usedValueAndTotal =
                          $scope.response.usedValue +
                          "/" +
                          $scope.response.totalLimit;
                        $scope.selected.client_name = $scope.wsale.client;
                        $scope.selected.subClientEmail =
                          $scope.response.subClientEmail;
                        $scope.previousIndex = $scope.tabindex;
                        $scope.$apply();
                        $scope.checkTimeFlight(args);
                        $scope.showNextStep = true;

                        $.post(
                          "../backend/application/index.php?rota=/incodde/checkOrderBot",
                          { order: $scope.selected },
                          function (result) {
                            $scope.robotLog = jQuery.parseJSON(result).dataset;
                          }
                        );
                      }
                    } else {
                      logger.logWarning("Cliente não vinculado!");
                      $scope.previousIndex = 0;
                      $.post(
                        "../backend/application/index.php?rota=/loadClientsNames",
                        {},
                        function (result) {
                          $scope.clients = jQuery.parseJSON(result).dataset;
                        }
                      );
                    }
                  }
                );

                return $scope.select($scope.currentPage);
              }
            }
          );
        };

        $scope.$watch("wsale.client", function () {
          var client = _.find($scope.clients, function (o) {
            return o.name == $scope.wsale.client;
          });
          if (client && client != undefined && client != null) {
            $scope.showNextStep = true;
          } else {
            $scope.showNextStep = false;
          }
        });

        $scope.checkPaxNames = function (pax_name) {
          if (pax_name && pax_name != "") {
            if (
              $scope.blackListOfNames.indexOf(
                pax_name.split(" ")[pax_name.split(" ").length - 1]
              ) > -1
            ) {
              return true;
            }
          }
          return false;
        };

        $scope.getTotalValue = function () {
          if ($scope.onlineflights != undefined) {
            var cont = 0;
            for (var i = 0; i < $scope.onlineflights.length; i++) {
              cont +=
                $scope.onlineflights[i].cost +
                $scope.onlineflights[i].baggage_price +
                $scope.onlineflights[i].special_seat -
                $scope.onlineflights[i].discount;
              if ($scope.onlineflights[i].is_newborn != "S") {
                cont += $scope.onlineflights[i].tax_billet;
              }
            }
            return cont;
          }
        };

        $scope.getTotalDiscount = function () {
          if ($scope.onlineflights != undefined) {
            var cont = 0;
            for (var i = 0; i < $scope.onlineflights.length; i++) {
              cont += $scope.onlineflights[i].discount;
            }
            return cont;
          }
        };

        $scope.getTotal = function () {
          if ($scope.onlineflights != undefined) {
            var cont = 0;
            for (var i = 0; i < $scope.onlineflights.length; i++) {
              cont += $scope.onlineflights[i].cost;
            }
            return cont;
          }
        };

        $scope.getFlightTotal = function (onlineflight) {
          if ($scope.selected) {
            if (
              $scope.selected.status == "EMITIDO" ||
              $scope.selected.status == "VERIFICADO"
            ) {
              return onlineflight.amountPaid;
            } else {
              if (onlineflight.is_newborn == "N") {
                return (
                  onlineflight.cost +
                  onlineflight.tax_billet +
                  onlineflight.baggage_price +
                  onlineflight.special_seat -
                  onlineflight.discount -
                  onlineflight.cupom
                );
              } else {
                return (
                  onlineflight.cost - onlineflight.discount - onlineflight.cupom
                );
              }
            }
          }
        };

        $scope.getTotalTax = function () {
          if ($scope.onlineflights != undefined) {
            var cont = 0;
            for (var i = 0; i < $scope.onlineflights.length; i++) {
              if ($scope.onlineflights[i].is_newborn != "S") {
                //cont += $scope.onlineflights[i].tax_billet;
                cont += $scope.onlineflights[i].tax;
              }
            }
            return cont;
          }
        };

        $scope.getPaxType = function (onlineflight) {
          if ($scope.onlineflights != undefined) {
            if (onlineflight.is_child == "S") return "CHD";
            if (onlineflight.is_newborn == "S") return "INF";
            return "ADT";
          }
        };

        $scope.hasNewBorn = function () {
          for (let i in $scope.onlineflights) {
            if ($scope.onlineflights[i].is_newborn == "S") {
              return true;
            }
          }
          return false;
        };

        $scope.hasChild = function () {
          for (let i in $scope.onlineflights) {
            if ($scope.onlineflights[i].is_child == "S") {
              return true;
            }
          }
          return false;
        };

        $scope.saveStatusOrder = function () {
          $.post(
            "../backend/application/index.php?rota=/saveStatusOrder",
            { data: $scope.selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.tabindex = 0;
              $scope.loadSalesByFilter();
            }
          );
        };

        $scope.saveOrder = function () {
          cfpLoadingBar.start();
          if ($scope.validCredit()) {
            $scope.args = undefined;
            $scope.selected._startedDate = $rootScope.formatServerDateTime(
              $scope.selected.startedDate
            );
            // Enviando dado se é diamante ou não
            $scope.selected.is_diamond = $scope.diamondPax;
            //console.log("Diamond pax ta: ");
            //console.log($scope.diamondPax);
            $scope.tabindex = 0;

            $.post(
              "../backend/application/index.php?rota=/generateOrder",
              {
                order_data: $scope.selected,
                flight_data: $scope.onlineflights,
                wsale_data: $scope.wsale,
              },
              function (result) {
                if (jQuery.parseJSON(result).message.type == "S") {
                  logger.logSuccess(jQuery.parseJSON(result).message.text);
                  $scope.uploader_billets.clearQueue();
                } else {
                  logger.logError(jQuery.parseJSON(result).message.text);
                }
                $scope.clearSession();
                cfpLoadingBar.complete();
              }
            );
          } else {
            logger.logError("Milhas Invalidas para Venda");
            $scope.clearSession();
          }
        };

        $scope.clearSession = function(){
          $.post(
            "../backend/application/index.php?rota=/loadCardsData",
            { data: $scope.onlineflights, clear_session: "true"  },
            function (result) {}
          );
        };

        $scope.sendEmail = function (onlineflights) {
          $scope.previousIndex = $scope.tabindex;
          $scope.tabindex = 5;
          $scope.fillEmailContent(onlineflights);
        };

        $scope.decript = function (code) {
          var data = code.split("320AB");
          var finaly = "";
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + String.fromCharCode(data[j] / 320);
          }
          return finaly;
        };

        $scope.cardPassword = function () {
          for (var i = 0; i <= $scope.onlineflights.length - 1; i++) {
            for (var j = 0; j <= $scope.cards.length - 1; j++) {
              if (
                $scope.cards[j].cards_id == $scope.onlineflights[i].cards_id
              ) {
                $scope.onlineflights[i].recovery_password = $scope.decript(
                  $scope.cards[j].recovery_password
                );
                $scope.onlineflights[i].token = $scope.cards[j].token;
                $scope.onlineflights[i].chip_number =
                  $scope.cards[j].chip_number;
                $scope.onlineflights[i].email_provider =
                  $scope.cards[j].email_provider;
                $scope.onlineflights[i].card_registrationCode =
                  $scope.cards[j].card_registrationCode;
                if (
                  $scope.onlineflights[i].airline == "TAM" ||
                  $scope.onlineflights[i].airline == "LATAM"
                ) {
                  $scope.onlineflights[i].access_id = $scope.cards[j].access_id;
                  $scope.onlineflights[i].access_password = $scope.decript(
                    $scope.cards[j].access_password
                  );
                } else {
                  $scope.onlineflights[i].access_id = " - ";
                  $scope.onlineflights[i].access_password = " - ";
                }
              }
            }
          }
          $scope.$apply();
          if (
            $scope.selected.status != "ENVIADO" &&
            $scope.selected.status != "EMITIDO" &&
            $scope.selected.status != "VERIFICADO"
          ) {
            // $scope.duTax();
          }
          if (!$scope.validCredit()) {
            logger.logError("Milhas Invalidas para Venda");
          }
        };

        $scope.findBaggagePrice = function (airline, baggages) {
          return (
            _.find($rootScope.airlines, function (o) {
              return o.name == airline;
            }).baggage * baggages
          );
        };

        $scope.duTax = function () {
          for (var i = 0; $scope.resume_paxs.length > i; i++) {
            var fullName = $scope.resume_paxs[i].pax_name;
            if ($scope.resume_paxs[i].paxLastName) {
              fullName += " " + $scope.resume_paxs[i].paxLastName;
            }
            if ($scope.resume_paxs[i].paxAgnome) {
              fullName += " " + $scope.resume_paxs[i].paxAgnome;
            }

            $scope.resume = $filter("filter")($scope.onlineflights, fullName);
            $scope.resume = $filter("filter")(
              $scope.resume,
              $scope.resume_paxs[i].airline
            );
            for (var j = 0; $scope.resume.length > j; j++) {
              if (
                ($scope.resume[j].airline == "TAM" ||
                  $scope.resume[j].airline == "LATAM") &&
                $scope.resume[j].provider == "Loja TAM Ponta Grossa"
              ) {
                $scope.resume[j].tax_card = undefined;
                $scope.resume[j].du_tax =
                  35 / $scope.resume.length +
                  $scope.findBaggagePrice(
                    $scope.resume[j].airline,
                    $scope.resume[j].baggage
                  );
              } else if (
                ($scope.resume[j].airline == "TAM" ||
                  $scope.resume[j].airline == "LATAM") &&
                $scope.resume[j].provider == "Loja TAM Contagem"
              ) {
                $scope.resume[j].tax_card = undefined;
                $scope.resume[j].du_tax =
                  25 / $scope.resume.length +
                  $scope.findBaggagePrice(
                    $scope.resume[j].airline,
                    $scope.resume[j].baggage
                  );
              } else if (
                ($scope.resume[j].airline == "TAM" ||
                  $scope.resume[j].airline == "LATAM") &&
                $scope.resume[j].provider == "Loja TAM M"
              ) {
                $scope.resume[j].tax_card = undefined;
                $scope.resume[j].du_tax =
                  28 / $scope.resume.length +
                  $scope.findBaggagePrice(
                    $scope.resume[j].airline,
                    $scope.resume[j].baggage
                  );
              } else if (
                $scope.resume[j].airline == "AZUL" &&
                $scope.resume[j].is_newborn == "N"
              ) {
                // $scope.resume[j].tax_card = undefined;
                // $scope.resume[j].du_tax = 20 + $scope.findBaggagePrice($scope.resume[j].airline, $scope.resume[j].baggage);
              }
            }
          }
        };

        $scope.validCredit = function () {
          var cards = [];
          var fill = false;

          for (var j in $scope.onlineflights) {
            for (var i in cards) {
              if (cards[i].cards_id == $scope.onlineflights[j].cards_id) {
                fill = true;
                cards[i].credit -= $scope.onlineflights[j].miles_used;
                if (cards[i].credit < 0) {
                  logger.logError(
                    "Saldo insuficiente para o cartão: " +
                      cards[i].card_number +
                      " !"
                  );
                  for (var x in $scope.onlineflights) {
                    if (cards[i].cards_id == $scope.onlineflights[x].cards_id) {
                      $scope.onlineflights[x].card_number = "";
                      $scope.onlineflights[x].cards_id = "";
                      $scope.onlineflights[x].recovery_password = "";
                      $scope.onlineflights[x].token = "";
                      $scope.onlineflights[x].email_provider = "";
                      $scope.onlineflights[x].card_registrationCode = "";
                      $scope.onlineflights[x].access_id = "";
                      $scope.onlineflights[x].access_password = "";
                      $scope.onlineflights[x].card_leftover = "";
                    }
                  }
                  $scope.$digest();
                  return false;
                }
              }
            }
            if (!fill) {
              cards.push({
                cards_id: $scope.onlineflights[j].cards_id,
                credit:
                  $scope.onlineflights[j].card_leftover -
                  $scope.onlineflights[j].miles_used,
                card_number: $scope.onlineflights[j].card_number,
              });
            }
          }
          return true;
        };

        $scope.isValidOrder = function () {
          if ($scope.selected) {
            if ($scope.validFlights() === true) {
              return true;
            } else {
              return false;
            }
          }
        };

        $scope.getPaxDescription = function () {
          if ($scope.onlineflights) {
            var inf = 0;
            var chd = 0;
            var adt = 0;
            for (var i = 0; $scope.resume_paxs.length > i; i++) {
              if ($scope.resume_paxs[i].is_child == "S") chd++;
              else if ($scope.resume_paxs[i].is_newborn == "S") inf++;
              else adt++;
            }
            var result = "";
            if (adt > 0) result = adt + " Adulto(s)";
            if (chd > 0) result = result + ", " + chd + " Criança(s)";
            if (inf > 0) result = result + ", " + inf + " Recem Nascido(s)";
            return result;
          }
        };

        $scope.validFlights = function () {
          if ($scope.onlineflights) {
            for (var j = 0; $scope.onlineflights.length > j; j++) {
              if ($scope.onlineflights[j].isBreak == "Integrado") {
                if (
                  $scope.onlineflights[j].provider != "CONFIANÇA" ||
                  $scope.onlineflights[j].provider != "Flytour" ||
                  $scope.onlineflights[j].provider != "Rextur Advance" ||
                  $scope.onlineflights[j].provider != "TAP"
                ) {
                  if (
                    $scope.onlineflights[j].card_number == " - " &&
                    ($scope.onlineflights[j].provider == "Loja TAM Contagem" ||
                      $scope.onlineflights[j].provider ==
                        "Loja TAM Ponta Grossa" ||
                      $scope.onlineflights[j].provider == "Loja TAM M" ||
                      $scope.onlineflights[j].provider === "")
                  ) {
                    return false;
                  }
                }

                if ($scope.onlineflights[j].flightLocator == undefined) {
                  return false;
                }

                if (
                  ($scope.onlineflights[j].airline == "TAM" ||
                    $scope.onlineflights[j].airline == "LATAM") &&
                  $scope.onlineflights[j].ticket_code == undefined
                ) {
                  return false;
                }

                if (
                  ($scope.onlineflights[j].provider == "" &&
                    $scope.onlineflights[j].tax_card == undefined) ||
                  $scope.onlineflights[j].flightLocator == undefined
                )
                  return false;
              }
            }
            return true;
          }
        };

        $scope.validOrder = function () {
          if ($scope.wsale.client) {
            $scope.selected.client_name = $scope.wsale.client;

            if ($scope.response) {
              if (
                $scope.response.status == "Bloqueado" ||
                $scope.response.paymentType == "Antecipado"
              ) {
                $scope.openClientWarning($scope.response);
              }
            }

            $scope.previousIndex = $scope.tabindex;
            $scope.tabindex++;
          }
        };

        $scope.openClientWarning = function (args) {
          $scope.ClientWarning = args;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "ClientWarningModalCtrl.html",
            controller: "ClientWarningInstanceCtrl",
            resolve: {
              selected: function () {
                return $scope.ClientWarning;
              },
            },
          });
          modalInstance.result.then(function () {});
        };

        $scope.cancelOrder = function () {
          $scope.cancel = true;
        };

        $scope.cancelOnlineOrder = function () {
          cfpLoadingBar.start();
          $.post(
            "../backend/application/index.php?rota=/cancelOnlineOrder",
            { data: $scope.selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.tabindex = 0;
            }
          );
        };

        $scope.loadSalesByFilter = function () {
          $.post(
            "../backend/application/index.php?rota=/loadOnlineByFilter",
            { data: $scope.filter },
            function (result) {
              if ($scope.tabindex == 0) {
                $scope.onlineorder = jQuery.parseJSON(result).dataset;
                $scope.search();
                $scope.$apply();
                return $scope.select($scope.currentPage);
              }
            }
          );
        };

        $scope.loadOrderMiles = function () {
          $scope.loadOrderMilesFunction = $scope.loadOrderMiles;
          cfpLoadingBar.start();
          var miles_used = 0;
          for (var i in $scope.onlineflights) {
            miles_used += $scope.onlineflights[i].miles_used;
          }
          $scope.flight_selected = { miles_used: miles_used };
          $scope.operation = 3;
          $.post(
            "../backend/application/index.php?rota=/loadSalesMiles",
            {
              searchKeywordsMiles: $scope.searchKeywordsMiles,
              ordenation: $scope.searchMilesOrder,
              ordernationDown: $scope.searchMilesOrderDown,
              page: $scope.currentPageMiles,
              numPerPage: $scope.numPerPageMiles,
              milesUsed: miles_used,
              airline: $scope.selected.airline,
              order: $scope.selected,
              pax_quant: $scope.resume_paxs.length,
              paxes: $scope.resume_paxs,
            },
            function (result) {
              $scope.flight_miles = jQuery.parseJSON(result).dataset.miles;
              $scope.total_miles = jQuery.parseJSON(result).dataset.total;
              $scope.filterCardsInUse();
            }
          );
        };

        $scope.findFlightMiles = function () {
          if (this.onlineflight) {
            $scope.flight_selected = this.onlineflight;
          }
          $scope.loadOrderMilesFunction = $scope.findFlightMiles;
          cfpLoadingBar.start();
          var totalFlight = {
            miles_used: 0,
          };
          for (var i = 0; i < $scope.onlineflights.length; i++) {
            if ($scope.onlineflights[i].flight == $scope.flight_selected.flight)
              totalFlight.miles_used += $scope.onlineflights[i].miles_used;
          }
          $.post(
            "../backend/application/index.php?rota=/loadSalesMiles",
            {
              searchKeywordsMiles: $scope.searchKeywordsMiles,
              ordenation: $scope.searchMilesOrder,
              ordernationDown: $scope.searchMilesOrderDown,
              page: $scope.currentPageMiles,
              numPerPage: $scope.numPerPageMiles,
              milesUsed: totalFlight.miles_used,
              boarding_date: $scope.flight_selected.boarding_date,
              airline: $scope.flight_selected.airline,
              order: $scope.selected,
              provider: $scope.flight_selected.provider,
              from: $scope.flight_selected.airport_code_from,
              to: $scope.flight_selected.airport_code_to,
              pax_quant: $scope.resume_paxs.length,
              paxes: $scope.resume_paxs,
            },
            function (result) {
              $scope.flight_miles = jQuery.parseJSON(result).dataset.miles;
              $scope.total_miles = jQuery.parseJSON(result).dataset.total;
              cfpLoadingBar.complete();
              $scope.tabindex = 1;
              $scope.$digest();
            }
          );
          $scope.operation = 1;
        };

        $scope.findMiles = function () {
          if (this.onlineflight) {
            $scope.flight_selected = this.onlineflight;
          }
          if (
            $scope.flight_selected.airline == "LATAM" ||
            $scope.flight_selected.airline == "AZUL"
          ) {
            if (!$scope.flight_selected.maxCards) {
              $scope.flight_selected.maxCards = 3;
            }
          } else {
            $scope.flight_selected.maxCards = -1;
          }

          var previous_card_id = $scope.flight_selected.cards_id;
          $scope.flight_selected.usedByCurrent = false;

          cfpLoadingBar.start();
          $scope.loadOrderMilesFunction = $scope.findMiles;
          $.post(
            "../backend/application/index.php?rota=/loadSalesMiles",
            {
              searchKeywordsMiles: $scope.searchKeywordsMiles,
              ordenation: $scope.searchMilesOrder,
              ordernationDown: $scope.searchMilesOrderDown,
              page: $scope.currentPageMiles,
              numPerPage: $scope.numPerPageMiles,
              milesUsed: $scope.flight_selected.miles_used,
              boarding_date: $scope.flight_selected.boarding_date,
              airline: $scope.flight_selected.airline,
              provider: $scope.flight_selected.provider,
              from: $scope.flight_selected.airport_code_from,
              to: $scope.flight_selected.airport_code_to,
              order: $scope.selected,
              pax_quant: 1,
              paxes: [$scope.flight_selected],
              maxCards: $scope.flight_selected.maxCards,
            },
            function (result) {
              $scope.flight_miles = jQuery.parseJSON(result).dataset.miles;
              $scope.total_miles = jQuery.parseJSON(result).dataset.total;
              if(previous_card_id){
                $scope.dessetCard(previous_card_id);
              }
              $scope.filterCardsInUse();
            }
          );
          $scope.operation = 2;
        };

        $scope.filterCardsInUse = function(){
          //filtra os dados da lista de cards listados, eliminando os ja usados
          $.post("../backend/application/index.php?rota=/loadCardsInUse", { hashId: $scope.session.hashId }, function(result){
            $scope.CardsUsados = jQuery.parseJSON(result).dataset;

            for(let m in $scope.flight_miles){
              $scope.flight_miles[m].usedByCurrent = false;
              for(let card in $scope.CardsUsados){
                if(($scope.CardsUsados[card].userSession == $scope.main.name)&&
                   ($scope.CardsUsados[card].cards_id    == $scope.flight_miles[m].cards_id)
                ){
                  $scope.flight_miles[m].usedByCurrent = true;
                  break;
                }
              }
            }

            $scope.flight_miles = $scope.flight_miles.filter(function(Row){
              for(let card in $scope.CardsUsados){
                if((!Row.usedByCurrent)&&(Row.cards_id == $scope.CardsUsados[card].cards_id))
                  return false;
              }
              return true;
            });
            cfpLoadingBar.complete();
            $scope.tabindex = 1;
            $scope.$apply();
          });
        };

        $scope.findMoreCards = function () {
          $scope.flight_selected.maxCards += 3;
          $scope.findMiles();
        };

        $scope.downloadAllBillets = function () {
          $scope.multiDownloads = true;

          // filtering airlines
          $scope.airline_flights = _.uniqBy($scope.onlineflights, "airline");

          for (var airline in $scope.airline_flights) {
            if ($scope.airline_flights[airline].airline == "LATAM") {
              $scope.filtered_flights_airline = $filter(
                "filter"
              )($scope.onlineflights, { airline: "LATAM" });

              $scope.filtered_flights = _.uniqBy(
                $scope.filtered_flights_airline,
                "ticket_code"
              );

              for (var flight in $scope.filtered_flights) {
                $scope.printBilletLaTam($scope.filtered_flights[flight]);
              }
            } else if ($scope.airline_flights[airline].airline == "GOL") {
              $scope.filtered_flights_airline = $filter(
                "filter"
              )($scope.onlineflights, { airline: "GOL" });

              $scope.filtered_flights = _.uniqBy(
                $scope.filtered_flights_airline,
                "flightLocator"
              );

              for (var flight in $scope.filtered_flights) {
                $scope.printBilletGol($scope.filtered_flights[flight]);
              }
            } else if ($scope.airline_flights[airline].airline == "AZUL") {
              $scope.filtered_flights_airline = $filter(
                "filter"
              )($scope.onlineflights, { airline: "AZUL" });

              $scope.filtered_flights = _.uniqBy(
                $scope.filtered_flights_airline,
                "flightLocator"
              );

              for (var flight in $scope.filtered_flights) {
                $scope.printBilletAzul2($scope.filtered_flights[flight]);
              }
            } else if ($scope.airline_flights[airline].airline == "AVIANCA") {
              $scope.filtered_flights_airline = $filter(
                "filter"
              )($scope.onlineflights, { airline: "AVIANCA" });

              $scope.filtered_flights = _.uniqBy(
                $scope.filtered_flights_airline,
                "flightLocator"
              );

              for (var flight in $scope.filtered_flights) {
                $scope.printBilletAvianca($scope.filtered_flights[flight]);
              }
            }
          }
          if ($scope.response) {
            if (
              $scope.response.notificationurl != null &&
              $scope.response.notificationurl.indexOf("http") == -1
            ) {
              $scope.fillEmailBillet();
            }
          } else {
            $scope.fillEmailBillet();
          }
        };

        $scope.filter_by_key = function (array, key) {
          var clear = [];
          for (var filterIndex in array) {
            if (clear.indexOf(array[filterIndex][key]) == -1) {
              clear.push(array[filterIndex]);
            }
          }
          return clear;
        };

        $scope.searchHighValueCards = function () {
          $scope.loadOrderMilesFunction = $scope.searchHighValueCards;
          $.post(
            "../backend/application/index.php?rota=/loadSalesMiles",
            {
              searchKeywordsMiles: $scope.searchKeywordsMiles,
              ordenation: $scope.searchMilesOrder,
              ordernationDown: $scope.searchMilesOrderDown,
              page: $scope.currentPageMiles,
              numPerPage: $scope.numPerPageMiles,
              milesUsed: 0,
              airline: "LATAM",
              cardType: "HighValue",
              pax_quant: $scope.resume_paxs.length,
              paxes: $scope.resume_paxs,
            },
            function (result) {
              $scope.flight_miles = jQuery.parseJSON(result).dataset.miles;
              $scope.total_miles = jQuery.parseJSON(result).dataset.total;
              cfpLoadingBar.complete();
              $scope.tabindex = 1;
              $scope.$digest();
            }
          );
        };

        $scope.searchAllCards = function (permissions) {
          $scope.loadOrderMilesFunction = $scope.searchAllCards;
          var milesUsed = -50000;
          if (permissions) {
            var permissions = permissions;
            if (!permissions.sales && !permissions.isMaster) {
              milesUsed = 1;
            }
          }
          $scope.operation = 2;
          $.post(
            "../backend/application/index.php?rota=/loadSalesMiles",
            {
              searchKeywordsMiles: $scope.searchKeywordsMiles,
              ordenation: $scope.searchMilesOrder,
              ordernationDown: $scope.searchMilesOrderDown,
              page: $scope.currentPageMiles,
              numPerPage: $scope.numPerPageMiles,
              milesUsed: milesUsed,
              airline: $scope.flight_selected.airline,
              pax_quant: 1,
              paxes: $scope.resume_paxs,
            },
            function (result) {
              $scope.flight_miles = jQuery.parseJSON(result).dataset.miles;
              $scope.total_miles = jQuery.parseJSON(result).dataset.total;
              $scope.$digest();
            }
          );
        };

        $scope.backToOrder = function () {
          $scope.tabindex = 4;
        };

        $scope.openFindAllCardsCtrl = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "FindAllCardsCtrl.html",
            controller: "FindAllCardsInstanceCtrl",
            resolve: {
              main: function () {
                return $scope.main;
              },
            },
          });
          modalInstance.result.then(function (permissions) {
            $scope.searchAllCards(permissions);
          });
        };

        $scope.setCard = function (mile) {

          var temp_miles = this.mile;
          // TEMP_MILES é o cartão que o funcionário selecionou para consumir as milhas

          $.post(
            "../backend/application/index.php?rota=/cards/checkValidMaxPaxPerCard",
            {
              data: temp_miles,
              flight_selected: $scope.flight_selected,
              onlineflights: $scope.onlineflights,
              operation: $scope.operation,
            },
            function (result) {
              if (jQuery.parseJSON(result).message.type == "E") {
                return logger.logError(
                  "Este cartão não é mais valido para compras."
                );
              }
              $scope.flight_selected.card_number = temp_miles.card_number;
              $scope.flight_selected.card_leftover = temp_miles.leftover;
              $scope.flight_selected.card_registrationCode =
                temp_miles.card_registrationCode;
              $scope.flight_selected.card_type = temp_miles.card_type;
              $scope.flight_selected.cards_id = temp_miles.cards_id;
              $scope.flight_selected.diamond_free = temp_miles.diamond_free;
              $scope.flight_selected.providerName = temp_miles.name;
              $scope.flight_selected.provider_phone = temp_miles.provider_phone;
              $scope.flight_selected.phoneNumberAirline =
                temp_miles.phoneNumberAirline;
              $scope.flight_selected.celNumberAirline =
                temp_miles.celNumberAirline;
              $scope.flight_selected.provider_adress =
                temp_miles.provider_adress;
              $scope.flight_selected.cityfullname = temp_miles.cityfullname;
              $scope.flight_selected.notes = temp_miles.notes;

              if (
                $scope.flight_selected.notes != null &&
                $scope.flight_selected.notes != undefined &&
                $scope.flight_selected.notes != ""
              ) {
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/system_notification.html",
                  controller: "SystemNotificationCtrl",
                  periods: $scope.periods,
                  size: "lg",
                  resolve: {
                    notification: function () {
                      return {
                        text: $scope.flight_selected.notes,
                        description:
                          "Em caso de duvidas, consulte seu gerente!",
                      };
                    },
                    header: function () {
                      return "Aviso";
                    },
                  },
                });
              }

              if ($scope.flight_selected.airline == "AVIANCA") {
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/system_notification.html",
                  controller: "SystemNotificationCtrl",
                  periods: $scope.periods,
                  size: "lg",
                  resolve: {
                    notification: function () {
                      return {
                        text:
                          "Emitir e segurar o pedido ate o e-tiket chegar por e-mail",
                        description:
                          "Em caso de duvidas, consulte seu gerente!",
                      };
                    },
                    header: function () {
                      return "Ficha AVIANCA";
                    },
                  },
                });
              }

              if ($scope.operation == 1) {
                for (var i = 0; i < $scope.onlineflights.length; i++) {
                  if (
                    $scope.onlineflights[i].flight ==
                    $scope.flight_selected.flight
                  ) {
                    $scope.onlineflights[i].card_number =
                      $scope.flight_selected.card_number;
                    $scope.onlineflights[i].card_leftover =
                      $scope.flight_selected.card_leftover;
                    $scope.onlineflights[i].card_registrationCode =
                      $scope.flight_selected.card_registrationCode;
                    $scope.onlineflights[i].card_type =
                      $scope.flight_selected.card_type;
                    $scope.onlineflights[i].cards_id =
                      $scope.flight_selected.cards_id;
                    $scope.onlineflights[i].providerName =
                      $scope.flight_selected.providerName;
                    $scope.onlineflights[i].provider_phone =
                      $scope.flight_selected.provider_phone;
                    $scope.onlineflights[i].phoneNumberAirline =
                      $scope.flight_selected.phoneNumberAirline;
                    $scope.onlineflights[i].celNumberAirline =
                      $scope.flight_selected.celNumberAirline;
                    $scope.onlineflights[i].provider_adress =
                      $scope.flight_selected.provider_adress;
                    $scope.onlineflights[i].cityfullname =
                      $scope.flight_selected.cityfullname;
                  }
                }
              }

              if ($scope.operation == 3) {
                for (var j in $scope.onlineflights) {
                  $scope.onlineflights[j].card_number =
                    $scope.flight_selected.card_number;
                  $scope.onlineflights[j].card_leftover =
                    $scope.flight_selected.card_leftover;
                  $scope.onlineflights[j].card_registrationCode =
                    $scope.flight_selected.card_registrationCode;
                  $scope.onlineflights[j].card_type =
                    $scope.flight_selected.card_type;
                  $scope.onlineflights[j].cards_id =
                    $scope.flight_selected.cards_id;
                  $scope.onlineflights[j].providerName =
                    $scope.flight_selected.providerName;
                  $scope.onlineflights[j].provider_phone =
                    $scope.flight_selected.provider_phone;
                  $scope.onlineflights[j].phoneNumberAirline =
                    $scope.flight_selected.phoneNumberAirline;
                  $scope.onlineflights[j].celNumberAirline =
                    $scope.flight_selected.celNumberAirline;
                  $scope.onlineflights[j].provider_adress =
                    $scope.flight_selected.provider_adress;
                  $scope.onlineflights[j].cityfullname =
                    $scope.flight_selected.cityfullname;
                }
              }

              $.post(
                "../backend/application/index.php?rota=/loadCardsData",
                { data: $scope.onlineflights, clear_session: "false" },
                function (result) {
                  $scope.cards = jQuery.parseJSON(result).dataset;
                  $scope.tabindex = 4;
                  $scope.cardPassword();
                }
              );
              $scope.searchKeywordsMiles = "";
            }
          );
        };

        $scope.dessetCard = function(id){
          for (var j in $scope.onlineflights) {
            if($scope.onlineflights[j].cards_id == id){
              $scope.onlineflights[j].card_number = null;
              $scope.onlineflights[j].card_leftover = null;
              $scope.onlineflights[j].card_registrationCode = null;
              $scope.onlineflights[j].card_type = null;
              $scope.onlineflights[j].cards_id = null;
              $.post("../backend/application/index.php?rota=/removeCardUse",{ data: {"cards_id": id} },
              function (result) {
                $scope.$apply();
              });
            }
          }
        };

        $scope.watchDogCards = function(){
          if($rootScope.watchDogPID != 0){
            clearInterval($rootScope.watchDogPID);
            $rootScope.watchDogPID = 0;
            console.log('WatchDog: Desligado');
          }
          if($scope.watchDogSelected){
            console.log('WatchDog: Ligado');
            $rootScope.watchDogPID = setInterval(function(){
              //console.log('WatchDog: Tick');
              $.post(
                "../backend/application/index.php?rota=/removeCardUseByTime",
                { hashId: $scope.session.hashId, name: $scope.main.name, minutes: "20" },
                function (result) {
                  let resp = jQuery.parseJSON(result).dataset;
                  if (resp.length > 0) {
                    console.log('WatchDog: Ids dos cards desassociados:');
                    for(let i in resp){
                      console.log(resp[i].id);
                    }
                  }
                }
              );
            }, 30000);
          }
        };

        $scope.setSelectedFlight = function () {
          if ($scope.selected.status != "EMITIDO") {
            $scope.flight_selected = this.onlineflight;
          } else {
            $scope.flight_selected = undefined;
          }
        };

        $scope.undo = function () {
          if ($scope.selected) {
            if (
              $scope.selected.status == "EMITIDO" ||
              $scope.selected.status == "VERIFICADO"
            ) {
              $scope.tabindex = 0;
              $scope.previousIndex = 0;
            } else {
              $scope.tabindex = $scope.previousIndex;
              $scope.previousIndex--;
            }
          } else {
            $scope.tabindex = 0;
            $scope.previousIndex = 0;
          }
          $scope.cancel = false;
        };

        $scope.undoBtt = function(){
          $scope.undo();
          $scope.clearSession();
          $scope.tabindex = 0;
          $scope.loadOnlineOrder();
        };

        $scope.findSpecialCards = function () {
          if ($scope.onlineflights) {
            if ($scope.onlineflights.length > 0) {
              for (var i in $scope.onlineflights) {
                if (
                  $scope.onlineflights[i].card_type == "RED" ||
                  $scope.onlineflights[i].card_type == "BLACK" ||
                  $scope.onlineflights[i].card_type == "DIAMANTE" ||
                  $scope.onlineflights[i].card_type == "CLUBE DIAMANTE"
                )
                  return true;
              }
            }
          }
          return false;
        };

        $scope.limpaRobo = function () {
          $.post(
            "../backend/application/index.php?rota=/incodde/removerRobo",
            { order: $scope.selected },
            function (result) {
              $scope.robotLog = [];
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.$digest();
            }
          );
        };

        $scope.bloqueiaRobo = false;
        $scope.in8Bot = function () {
          if (!$scope.onlineflights[0].tax_cardType) {
            return logger.logError("Cartão de credito deve ser vinculado!");
          }
          if (
            !$scope.onlineflights[0].card_registrationCode ||
            $scope.onlineflights[0].card_registrationCode == null ||
            $scope.onlineflights[0].card_registrationCode == "null"
          ) {
            return logger.logError("Ficha deve ser vinculado!");
          }

          $scope.bloqueiaRobo = true;
          $.post(
            "../backend/application/index.php?rota=/incodde/newOrder",
            { order: $scope.selected, onlineflights: $scope.onlineflights },
            function (result) {
              $scope.bloqueiaRobo = false;
              $scope.$digest();

              var response = jQuery.parseJSON(result).dataset;

              if (jQuery.parseJSON(result).message.type == "S") {
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }

              $.post(
                "../backend/application/index.php?rota=/incodde/checkOrderBot",
                { order: $scope.selected },
                function (result) {
                  $scope.robotLog = jQuery.parseJSON(result).dataset;
                  $scope.$digest();
                }
              );
            }
          );
        };

        $scope.openRobotLog = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/notification_robot_list.html",
            controller: "NotificationRobotListCtrl",
            periods: $scope.periods,
            size: "lg",
            resolve: {
              logs: function () {
                return $scope.robotLog;
              },
              header: function () {
                return "Acompanhamento de emissão";
              },
              order: function () {
                return $scope.selected;
              },
              onlineflights: function () {
                return $scope.onlineflights;
              },
            },
          });
        };

        $scope.loadPaxesUsedCards = function () {
          var from = $rootScope.airports.filter(function (airport) {
            return (
              airport.iataCode == $scope.onlineflights[0].airport_code_from
            );
          })[0];
          var to = $rootScope.airports.filter(function (airport) {
            return airport.iataCode == $scope.onlineflights[0].airport_code_to;
          })[0];
          var internacional = false;
          //console.log(from);
          //console.log(to);
          if (from && to) {
            if (from.internacional == "1" || to.internacional == "1") {
              internacional = true;
            }
          }

          $.post(
            "../backend/application/index.php?rota=/cards/loadAllPaxesCardsUsed",
            { data: $scope.resume_paxs, internacional: internacional },
            function (result) {
              $scope.resume_paxs = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );
        };

        $scope.openPaxUsedByCode = function (pax) {
          var from = $rootScope.airports.filter(function (airport) {
            return (
              airport.iataCode == $scope.onlineflights[0].airport_code_from
            );
          })[0];
          var to = $rootScope.airports.filter(function (airport) {
            return airport.iataCode == $scope.onlineflights[0].airport_code_to;
          })[0];
          pax.internacional = false;
          if (from && to) {
            if (from.internacional == "1" || to.internacional == "1") {
              pax.internacional = true;
            }
          }

          $.post(
            "../backend/application/index.php?rota=/cards/loadPaxUsedCards",
            pax,
            function (result) {
              $scope.cardsUsed = jQuery.parseJSON(result).dataset;
              var modalInstance;
              modalInstance = $modal.open({
                templateUrl: "app/modals/modal_names_list.html",
                controller: "ModalNamesLIstCtrl",
                resolve: {
                  selected: function () {
                    return $scope.selected;
                  },
                  paxPerCards: function () {
                    return $scope.cardsUsed;
                  },
                },
              });
            }
          );
        };

        $scope.openPaxUsedCards = function (pax) {
          var from = $rootScope.airports.filter(function (airport) {
            return (
              airport.iataCode == $scope.onlineflights[0].airport_code_from
            );
          })[0];
          var to = $rootScope.airports.filter(function (airport) {
            return airport.iataCode == $scope.onlineflights[0].airport_code_to;
          })[0];
          pax.internacional = false;
          if (from && to) {
            if (from.internacional == "1" || to.internacional == "1") {
              pax.internacional = true;
            }
          }

          $.post(
            "../backend/application/index.php?rota=/cards/loadPaxUsedCards",
            pax,
            function (result) {
              $scope.cardsUsed = jQuery.parseJSON(result).dataset;
              var modalInstance;
              modalInstance = $modal.open({
                templateUrl: "app/modals/modal_names_list.html",
                controller: "ModalNamesLIstCtrl",
                resolve: {
                  selected: function () {
                    return $scope.selected;
                  },
                  paxPerCards: function () {
                    return $scope.cardsUsed;
                  },
                },
              });
            }
          );
        };

        $scope.openAllPaxUsedCards = function () {
          $.post(
            "../backend/application/index.php?rota=/cards/loadAllPaxUsedCards",
            { data: $scope.resume_paxs },
            function (result) {
              $scope.cardsUsed = jQuery.parseJSON(result).dataset;
              var modalInstance;
              modalInstance = $modal.open({
                templateUrl: "app/modals/modal_names_list.html",
                controller: "ModalNamesLIstCtrl",
                resolve: {
                  selected: function () {
                    return $scope.selected;
                  },
                  paxPerCards: function () {
                    return $scope.cardsUsed;
                  },
                },
              });
            }
          );
        };

        $scope.openSearchModal = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "ModalCtrl.html",
            controller: "OnlineOrderModalInstanceCtrl",
            resolve: {
              filter: function () {
                return $scope.filter;
              },
            },
          });
          modalInstance.result.then(
            function (filter) {
              if (
                $scope.main.isMaster ||
                $scope.main.sale ||
                $scope.main.changeMiles ||
                $scope.main.conference ||
                $scope.main.wizarSaleEvent
              ) {
                filter._issueDateFrom = $rootScope.formatServerDate(
                  filter.issueDateFrom
                );
                filter._issueDateTo = $rootScope.formatServerDate(
                  filter.issueDateTo
                );
              } else {
                var date = new Date();
                date.setDate(date.getDate() - 3);
                if (
                  filter.issueDateFrom &&
                  filter.issueDateFrom != "Invalid Date"
                ) {
                  if (filter.issueDateFrom < date) {
                    filter.issueDateFrom = new Date();
                    filter.issueDateFrom.setDate(
                      filter.issueDateFrom.getDate() - 2
                    );
                  }
                } else {
                  filter.issueDateFrom = new Date();
                  filter.issueDateFrom.setDate(
                    filter.issueDateFrom.getDate() - 2
                  );
                }

                if (
                  filter.issueDateTo &&
                  filter.issueDateTo != "Invalid Date"
                ) {
                  filter._issueDateTo = $rootScope.formatServerDate(
                    filter.issueDateTo
                  );
                }

                filter._issueDateFrom = $rootScope.formatServerDate(
                  filter.issueDateFrom
                );
              }

              $scope.filter = filter;
              $scope.loadOnlineOrder();
            },
            function () {
              console.log("Modal dismissed at: " + new Date());
            }
          );
        };

        $scope.openStatusModal = function (onlineorder) {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/order_status.html",
            controller: "OrderStatusModalCtrl",
            periods: $scope.periods,
            size: "lg",
            resolve: {
              order: function () {
                return onlineorder;
              },
              main: function () {
                return $scope.main;
              },
            },
          });
          modalInstance.result.then(
            function (filter) {},
            function () {
              console.log("Modal dismissed at: " + new Date());
            }
          );
        };

        $scope.openNotificationModal = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/order_notifications.html",
            controller: "OrderNotificationModalCtrl",
            periods: $scope.periods,
            size: "lg",
            resolve: {
              order: function () {
                return $scope.selected;
              },
              onlineflights: function () {
                return $scope.resume_flights;
              },
              resume_paxs: function () {
                return $scope.resume_paxs;
              },
            },
          });
          modalInstance.result.then(
            function (filter) {
              if (filter) {
                $scope.tabindex = 0;
                $scope.$digest();
              }
            },
            function () {
              console.log("Modal dismissed at: " + new Date());
            }
          );
        };

        $scope.openOrdersNotificationModal = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/order_notification_status.html",
            controller: "OrdersNotificationModalCtrl",
            periods: $scope.periods,
            size: "lg",
            resolve: {
              order: function () {
                return $scope.selected;
              },
            },
          });
          modalInstance.result.then(
            function (filter) {},
            function () {
              console.log("Modal dismissed at: " + new Date());
            }
          );
        };

        return init();
      },
    ])
    .filter("groupBy", function () {
      return function (data, key) {
        if (!(data && key)) return;
        var result = {};
        for (var i = 0; i < data.length; i++) {
          if (!result[data[i][key]]) result[data[i][key]] = [];
          result[data[i][key]].push(data[i]);
        }
        return result;
      };
    })
    .filter("replaceAll", function () {
      return function (input) {
        return input.replace(/_/g, " ");
      };
    })
    .controller("FlightDataCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $scope.open = function () {
          $scope.flight_selected = this.onlineflight;
          $scope.originalFlight = angular.copy($scope.flight_selected);
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "FlightDataOnline.html",
            controller: "FlightDataOnlineInstanceCtrl",
            resolve: {
              flight_selected: function () {
                return $scope.flight_selected;
              },
              originalFlight: function () {
                return $scope.originalFlight;
              },
            },
          });
          modalInstance.result.then(function (flight_selected) {
            for (
              var i = 0;
              i < $scope.$parent.$parent.onlineflights.length;
              i++
            ) {
              if (
                $scope.$parent.$parent.onlineflights[i].flight ==
                  flight_selected.flight &&
                $scope.$parent.$parent.onlineflights[i].boarding_date ==
                  flight_selected.boarding_date
              ) {
                $scope.$parent.$parent.onlineflights[i].textarea =
                  flight_selected.textarea;
                $scope.$parent.$parent.onlineflights[i].flightLocator =
                  flight_selected.flightLocator;
                $scope.$parent.$parent.onlineflights[i].tax =
                  flight_selected.tax;
                $scope.$parent.$parent.onlineflights[i].tax_billet =
                  flight_selected.tax_billet; // Alteração
                if (flight_selected.provider) {
                  $scope.$parent.$parent.onlineflights[i].provider =
                    flight_selected.provider;
                }
                if (flight_selected.partnerReservationCode) {
                  $scope.$parent.$parent.onlineflights[
                    i
                  ].partnerReservationCode =
                    flight_selected.partnerReservationCode;
                }
                if (flight_selected.provider == "Rextur Advance") {
                  $scope.$parent.$parent.onlineflights[i].card_number = " - ";
                  $scope.$parent.$parent.onlineflights[i].recovery_password =
                    "";
                  $scope.$parent.$parent.onlineflights[i].token = "";
                  $scope.$parent.$parent.onlineflights[i].email_provider = "";
                  $scope.$parent.$parent.onlineflights[
                    i
                  ].card_registrationCode = "";
                  $scope.$parent.$parent.onlineflights[i].access_id = "";
                  $scope.$parent.$parent.onlineflights[i].access_password = "";
                  $scope.$parent.$parent.onlineflights[i].safeType =
                    flight_selected.safeType;
                } else if (
                  flight_selected.provider == "JACK FOR" ||
                  flight_selected.provider == "Outros"
                ) {
                  $scope.$parent.$parent.onlineflights[i].card_number = " - ";
                  $scope.$parent.$parent.onlineflights[i].recovery_password =
                    "";
                  $scope.$parent.$parent.onlineflights[i].token = "";
                  $scope.$parent.$parent.onlineflights[i].email_provider = "";
                  $scope.$parent.$parent.onlineflights[
                    i
                  ].card_registrationCode = "";
                  $scope.$parent.$parent.onlineflights[i].access_id = "";
                  $scope.$parent.$parent.onlineflights[i].access_password = "";
                }
              }
            }
            $scope.$parent.$parent.resume_flights = $filter("filter")(
              $scope.$parent.$parent.onlineflights,
              "FILTER_FLIGHT"
            );
          });
        };
      },
    ])
    .controller("FlightDataOnlineInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "flight_selected",
      "originalFlight",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        logger,
        flight_selected,
        originalFlight
      ) {
        $scope.flight_selected = originalFlight;
        $scope.saleMethods = [
          "JACK FOR",
          // 'Loja TAM Ponta Grossa',
          // 'Loja TAM Contagem',
          // 'Loja TAM M',
          "CONFIANÇA",
          "Flytour",
          "TAP",
          "Rextur Advance",
          "CNT Consolidadora",
          "HAST Viagens",
          "XML Viagens",
          "Milhas Alpass",
          "Alfa Rondonia Milhas",
          "Outros",
        ];
        $scope.safeMethods = ["Cartao", "Faturado"];

        $scope.ok = function () {
          $scope.flight_selected.textarea = angular.copy($scope.textarea);
          $modalInstance.close($scope.flight_selected);
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("PaxDataCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $scope.updatePaxName = function (paxId, paxData) {
          $.post(
            "../backend/application/index.php?rota=/updatePaxName",
            { paxId: paxId, paxData: paxData },
            function (result) {
              $scope.$parent.$parent.loadPaxesUsedCards();
            }
          );
        };

        $scope.open = function () {
          $scope.pax_selected = this.pax;
          $scope.originalPax = angular.copy($scope.pax_selected);
          $scope.original = angular.copy($scope.pax_selected);
          $scope.master =
            $scope.$parent.$parent.$parent.$parent.main.isMaster ||
            $scope.$parent.$parent.$parent.$parent.main.changeSale;
          $scope.hashId = $scope.$parent.$parent.$parent.$parent.session.hashId;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "PaxData.html",
            controller: "PaxDataInstanceCtrl",
            resolve: {
              pax_selected: function () {
                return $scope.pax_selected;
              },
              originalPax: function () {
                return $scope.originalPax;
              },
              master: function () {
                return $scope.master;
              },
              hashId: function () {
                return $scope.hashId;
              },
            },
          });
          modalInstance.result.then(function (pax_selected) {
            for (
              var i = 0;
              i < $scope.$parent.$parent.onlineflights.length;
              i++
            ) {
              if (
                $scope.$parent.$parent.onlineflights[i].pax_id ==
                $scope.original.pax_id
              ) {
                $scope.$parent.$parent.onlineflights[i].pax_name =
                  pax_selected.pax_name;
                $scope.$parent.$parent.onlineflights[i].paxLastName =
                  pax_selected.paxLastName;
                $scope.$parent.$parent.onlineflights[i].paxAgnome =
                  pax_selected.paxAgnome;
                $scope.$parent.$parent.onlineflights[i].identification =
                  pax_selected.identification;
                $scope.$parent.$parent.onlineflights[i].userEmail =
                  pax_selected.userEmail;
                $scope.updatePaxName($scope.original.pax_id, pax_selected);
              }
            }
            $scope.$parent.$parent.resume_paxs = $filter("filter")(
              $scope.$parent.$parent.onlineflights,
              "FILTER_PAX"
            );
          });
        };
      },
    ])
    .controller("PaxDataInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "pax_selected",
      "originalPax",
      "master",
      "hashId",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        logger,
        pax_selected,
        originalPax,
        master,
        hashId
      ) {
        $scope.pax_selected = originalPax;
        $scope.hashId = hashId;
        $scope.change = false;
        $scope.checked = true;
        $scope.master = master;

        $scope.changeValue = function () {
          $scope.change = true;
        };

        $scope.check = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { data: $scope.flight_selected },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $scope.pax_selected.userEmail = $scope.response.userEmail;
                $scope.checked = false;
                $scope.change = false;
                $scope.$apply();
              } else {
                $scope.checked = true;
                logger.logError("Dados não conferem!");
                $scope.$apply();
              }
            }
          );
        };

        $scope.ok = function () {
          $modalInstance.close($scope.pax_selected);
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("FlightSelectedData", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $scope.open = function () {
          $("#discount").maskMoney({
            thousands: ".",
            decimal: ",",
            precision: 2,
          });
          $("#miles_used").number(true, 0, ",", ".");

          $scope.flight_selected = this.onlineflight;
          $scope.originalFlight = angular.copy($scope.flight_selected);
          if ($scope.originalFlight.connection != " Direto") {
            if (!$scope.originalFlight.connections) {
              $scope.originalFlight.connections = [];
              var split = $scope.originalFlight.connection.split(" ");
              for (var i = 1; i < split.length - 1; i++) {
                $scope.originalFlight.connections.push({ seat: "---" });
              }
            }
          }
          $scope.master =
            $scope.$parent.$parent.$parent.$parent.main.isMaster ||
            $scope.$parent.$parent.$parent.$parent.main.changeSale;
          $scope.hashId = $scope.$parent.$parent.$parent.$parent.session.hashId;
          if ($scope.flight_selected.is_newborn == "S") {
            $scope.originalFlight.discount =
              $scope.flight_selected.cost - $scope.flight_selected.discount;
          } else {
            $scope.originalFlight.discount =
              $scope.flight_selected.cost +
              $scope.flight_selected.tax_billet +
              $scope.flight_selected.baggage_price +
              $scope.flight_selected.special_seat -
              $scope.flight_selected.discount;
          }
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "FlightSelectedData.html",
            controller: "FlightSelectedDataInstanceCtrl",
            resolve: {
              flight_selected: function () {
                return $scope.flight_selected;
              },
              originalFlight: function () {
                return $scope.originalFlight;
              },
              master: function () {
                return $scope.master;
              },
              hashId: function () {
                return $scope.hashId;
              },
            },
          });
          modalInstance.result.then(function (flight_selected) {
            if (flight_selected.is_newborn == "S") {
              var discount = flight_selected.cost - flight_selected.discount;
            } else {
              var discount =
                flight_selected.cost +
                flight_selected.tax_billet +
                flight_selected.baggage_price +
                flight_selected.special_seat -
                flight_selected.discount;
            }
            for (
              var i = 0;
              i < $scope.$parent.$parent.onlineflights.length;
              i++
            ) {
              if (
                $scope.$parent.$parent.onlineflights[i].flight ==
                  flight_selected.flight &&
                $scope.$parent.$parent.onlineflights[i].pax_id ==
                  flight_selected.pax_id &&
                $scope.$parent.$parent.onlineflights[i].boarding_date ==
                  flight_selected.boarding_date
              ) {
                $scope.$parent.$parent.onlineflights[i].miles_used =
                  flight_selected.miles_used;
                $scope.$parent.$parent.onlineflights[i].flightLocator =
                  flight_selected.flightLocator;
                $scope.$parent.$parent.onlineflights[i].du_tax =
                  flight_selected.du_tax;
                $scope.$parent.$parent.onlineflights[i].money =
                  flight_selected.money;
                $scope.$parent.$parent.onlineflights[i].is_diamond =
                  flight_selected.is_diamond;
                $scope.$parent.$parent.onlineflights[i].provider =
                  flight_selected.provider;
                $scope.$parent.$parent.onlineflights[i].userEmail =
                  flight_selected.userEmail;
                $scope.$parent.$parent.onlineflights[i].discount = discount;
                $scope.$parent.$parent.onlineflights[i].isBreak =
                  flight_selected.isBreak;
                $scope.$parent.$parent.onlineflights[i].ticket_code =
                  flight_selected.ticket_code;
                $scope.$parent.$parent.onlineflights[i].partnerReservationCode =
                  flight_selected.partnerReservationCode;
                $scope.$parent.$parent.onlineflights[i].seat =
                  flight_selected.seat;
                $scope.$parent.$parent.onlineflights[i].connections =
                  flight_selected.connections;
                $scope.$parent.$parent.onlineflights[i].baggage_price =
                  flight_selected.baggage_price;
                $scope.$parent.$parent.onlineflights[i].special_seat =
                  flight_selected.special_seat;
                if (flight_selected.provider == "Rextur Advance") {
                  $scope.$parent.$parent.onlineflights[i].card_number = " - ";
                  $scope.$parent.$parent.onlineflights[i].recovery_password =
                    "";
                  $scope.$parent.$parent.onlineflights[i].token = "";
                  $scope.$parent.$parent.onlineflights[i].email_provider = "";
                  $scope.$parent.$parent.onlineflights[
                    i
                  ].card_registrationCode = "";
                  $scope.$parent.$parent.onlineflights[i].access_id = "";
                  $scope.$parent.$parent.onlineflights[i].access_password = "";
                  $scope.$parent.$parent.onlineflights[i].safeType =
                    flight_selected.safeType;
                } else if (
                  flight_selected.provider == "JACK FOR" ||
                  flight_selected.provider == "Outros"
                ) {
                  $scope.$parent.$parent.onlineflights[i].card_number = " - ";
                  $scope.$parent.$parent.onlineflights[i].recovery_password =
                    "";
                  $scope.$parent.$parent.onlineflights[i].token = "";
                  $scope.$parent.$parent.onlineflights[i].email_provider = "";
                  $scope.$parent.$parent.onlineflights[
                    i
                  ].card_registrationCode = "";
                  $scope.$parent.$parent.onlineflights[i].access_id = "";
                  $scope.$parent.$parent.onlineflights[i].access_password = "";
                }
              }
            }
            $scope.$parent.$parent.resume_flights = $filter("filter")(
              $scope.$parent.$parent.onlineflights,
              "FILTER_FLIGHT"
            );
          });
        };
      },
    ])
    .controller("FlightSelectedDataInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "$filter",
      "logger",
      "flight_selected",
      "originalFlight",
      "master",
      "hashId",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        $filter,
        logger,
        flight_selected,
        originalFlight,
        master,
        hashId
      ) {
        $scope.breakoptions = [
          { breakOption: "Integrado" },
          { breakOption: "Quebrado" },
        ];
        $scope.saleMethods = [
          "JACK FOR",
          // 'Loja TAM Ponta Grossa',
          // 'Loja TAM Contagem',
          // 'Loja TAM M',
          "CONFIANÇA",
          "Flytour",
          "TAP",
          "Rextur Advance",
          "CNT Consolidadora",
          "HAST Viagens",
          "XML Viagens",
          "Milhas Alpass",
          "Alfa Rondonia Milhas",
          "Outros",
        ];
        $scope.safeMethods = ["Cartao", "Faturado"];

        $scope.flight_selected = originalFlight;
        $scope.flight_selected.pinCode = "";
        $scope.hashId = hashId;
        $scope.change = false;
        $scope.checked = true;
        $scope.master = master;

        $scope.cardType = true;
        if (
          $scope.flight_selected.card_type == "RED" ||
          $scope.flight_selected.card_type == "BLACK"
        ) {
          $scope.cardType = false;
        }

        $("#discount").maskMoney({
          thousands: ".",
          decimal: ",",
          precision: 2,
        });
        $("#miles_used").number(true, 0, ",", ".");
        if ($scope.flight_selected.provider != "") {
          $scope.third = true;
        }

        if ($scope.flight_selected.isBreak != "Integrado") {
          $scope.break = true;
        }

        $scope.findBaggagePrice = function (airline, baggages) {
          return (
            _.find($rootScope.airlines, function (o) {
              return o.name == airline;
            }).baggage * baggages
          );
        };

        // if($scope.flight_selected.airline == "AZUL" && $scope.flight_selected.du_tax == undefined && $scope.flight_selected.is_newborn == "N") {
        //   $scope.flight_selected.du_tax = 20 + $scope.findBaggagePrice($scope.flight_selected.airline, $scope.flight_selected.baggage);
        // }

        $scope.$watch("flight_selected.provider", function () {
          if ($scope.flight_selected.du_tax == undefined) {
            $scope.flight_selected.tax_card = undefined;
            if (
              $scope.flight_selected.airline == "TAM" ||
              $scope.flight_selected.airline == "LATAM"
            ) {
              if ($scope.flight_selected.provider == "Loja TAM Ponta Grossa") {
                $scope.flight_selected.du_tax =
                  35 +
                  $scope.findBaggagePrice(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.baggage
                  );
              } else if (
                $scope.flight_selected.provider == "Loja TAM Contagem"
              ) {
                $scope.flight_selected.du_tax =
                  25 +
                  $scope.findBaggagePrice(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.baggage
                  );
              } else if ($scope.flight_selected.provider == "Loja TAM M") {
                $scope.flight_selected.du_tax =
                  28 +
                  $scope.findBaggagePrice(
                    $scope.flight_selected.airline,
                    $scope.flight_selected.baggage
                  );
              }
            }
            // if( ( $scope.flight_selected.provider == "Rextur Advance" || $scope.flight_selected.provider == "CONFIANÇA" || $scope.flight_selected.provider == "TAP" ) && $scope.$parent.onlineOrder.client_name == "OAB ") {
            //   $scope.flight_selected.du_tax = 33 + $scope.findBaggagePrice($scope.flight_selected.airline, $scope.flight_selected.baggage);
            // }
          }
        });

        $scope.changeValue = function () {
          $scope.change = true;
        };

        $scope.check = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { data: $scope.flight_selected },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $scope.flight_selected.userEmail = $scope.response.userEmail;
                $scope.checked = false;
                $scope.change = false;
                $scope.$apply();
              } else {
                $scope.checked = true;
                logger.logError("Dados não conferem!");
                $scope.$apply();
              }
            }
          );
        };

        $scope.ok = function () {
          flight_selected = $scope.flight_selected;

          $modalInstance.close($scope.flight_selected);
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("OnlineOrderModalInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "filter",
      function ($scope, $rootScope, $modalInstance, filter) {
        $scope.saleStatus = ["RESERVA", "PENDENTE", "EMITIDO", "EM ESPERA"];
        $scope.filter = filter;
        $scope.ok = function () {
          $modalInstance.close($scope.filter);
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("TaxaCardData", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $scope.open = function () {
          $scope.flight_selected = this.onlineflight;
          $.post(
            "../backend/application/index.php?rota=/loadInternalCards",
            { data: $scope.flight_selected },
            function (result) {
              $scope.internalCards = jQuery.parseJSON(result).dataset;

              $scope.decript = function (code) {
                var data = code.split("320AB");
                var finaly = "";
                for (var j = 0; data.length > j; j++) {
                  finaly = finaly + String.fromCharCode(data[j] / 320);
                }
                return finaly;
              };

              for (var i in $scope.internalCards) {
                $scope.internalCards[i].password = $scope.decript(
                  $scope.internalCards[i].password
                );
              }

              if ($scope.flight_selected.card_number != " - ") {
                $.post(
                  "../backend/application/index.php?rota=/loadCardProvider",
                  { data: $scope.flight_selected },
                  function (result) {
                    $scope.TaxCard = jQuery.parseJSON(result).dataset;
                    if (
                      $scope.TaxCard.length == 1 &&
                      $scope.flight_selected.tax_card == undefined
                    ) {
                      $scope.flight_selected.tax_card =
                        $scope.TaxCard[0].card_number;
                      $scope.flight_selected.tax_providerName =
                        $scope.TaxCard[0].provider_name;
                    } else {
                      $scope.TaxCard = {};
                    }
                    $scope.originalFlight = angular.copy(
                      $scope.flight_selected
                    );
                    $scope.master =
                      $scope.$parent.$parent.$parent.$parent.main.isMaster ||
                      $scope.$parent.$parent.$parent.$parent.main.changeSale;
                    $scope.hashId =
                      $scope.$parent.$parent.$parent.$parent.session.hashId;
                    var modalInstance;
                    modalInstance = $modal.open({
                      templateUrl: "TaxaCardData.html",
                      controller: "TaxaCardInstanceCtrl",
                      resolve: {
                        internalCards: function () {
                          return $scope.internalCards;
                        },
                        originalFlight: function () {
                          return $scope.originalFlight;
                        },
                        flight_selected: function () {
                          return $scope.flight_selected;
                        },
                        TaxCard: function () {
                          return $scope.TaxCard;
                        },
                        master: function () {
                          return $scope.master;
                        },
                        hashId: function () {
                          return $scope.hashId;
                        },
                      },
                    });

                    modalInstance.result.then(function (TaxCard) {
                      for (
                        var i = 0;
                        i < $scope.$parent.$parent.onlineflights.length;
                        i++
                      ) {
                        if (
                          $scope.$parent.$parent.onlineflights[i].flight ==
                            $scope.flight_selected.flight &&
                          $scope.$parent.$parent.onlineflights[i].pax_id ==
                            $scope.flight_selected.pax_id
                        ) {
                          $scope.$parent.$parent.onlineflights[i].tax_card =
                            TaxCard.tax_card;
                          $scope.$parent.$parent.onlineflights[i].tax_password =
                            TaxCard.tax_password;
                          $scope.$parent.$parent.onlineflights[i].tax_cardType =
                            TaxCard.tax_cardType;
                          $scope.$parent.$parent.onlineflights[i].tax_dueDate =
                            TaxCard.tax_dueDate;
                          $scope.$parent.$parent.onlineflights[
                            i
                          ].tax_providerName = TaxCard.tax_providerName;
                        }
                      }
                    });
                  }
                );
              }
            }
          );
        };
      },
    ])
    .controller("TaxaCardInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "$filter",
      "internalCards",
      "originalFlight",
      "flight_selected",
      "TaxCard",
      "master",
      "hashId",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        $filter,
        internalCards,
        originalFlight,
        flight_selected,
        TaxCard,
        master,
        hashId
      ) {
        $scope.flight_selected = {};

        $scope.findDate = function (date) {
          var data = new Date(date);
          data.setDate(data.getDate() + 1);
          return data;
        };

        $scope.internalCards = internalCards;
        $scope.flight_selected = originalFlight;
        $scope.resume_creditCards = $filter("filter")(
          $scope.internalCards,
          $scope.flight_selected.airline
        );
        $scope.TaxCard = TaxCard;
        $scope.master = master;
        $scope.hashId = hashId;
        $scope.change = false;
        $scope.checked = true;

        $scope.changeValue = function () {
          $scope.change = true;
        };

        $scope.search = function () {
          $scope.TaxCard = $filter("filter")(
            $scope.resume_creditCards,
            $scope.filter
          );
          $scope.flight_selected.tax_card = $scope.TaxCard[0].card_number;
          $scope.flight_selected.tax_password = $scope.TaxCard[0].password;
          $scope.flight_selected.tax_cardType = $scope.TaxCard[0].card_type;
          $scope.flight_selected.tax_dueDate = new Date(
            $scope.TaxCard[0].due_date
          );
          $scope.flight_selected.tax_providerName =
            $scope.TaxCard[0].provider_name;
          $scope.flight_selected.provider_registration =
            $scope.TaxCard[0].provider_registration;
          $scope.flight_selected.providerPhone =
            $scope.TaxCard[0].providerPhone;
          $scope.flight_selected.providerEmail =
            $scope.TaxCard[0].providerEmail;
          $scope.flight_selected.providerAdress =
            $scope.TaxCard[0].providerAdress;
          $scope.flight_selected.provider_adress =
            $scope.TaxCard[0].provider_adress;
          $scope.flight_selected.birthdate = new Date(
            $scope.TaxCard[0].birthdate
          );
        };

        $scope.check = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { hashId: $scope.hashId, data: $scope.flight_selected },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $scope.flight_selected.userEmail = $scope.response.userEmail;
                $scope.checked = false;
                $scope.change = false;
                $scope.$apply();
              } else {
                $scope.checked = true;
                logger.logError("Dados não conferem!");
                $scope.$apply();
              }
            }
          );
        };

        $scope.ok = function () {
          flight_selected = $scope.flight_selected;
          $modalInstance.close($scope.flight_selected);
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("CardFlightSelectedData", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $scope.open = function () {
          $scope.flight_selected = this.onlineflight;
          var modalInstance;

          modalInstance = $modal.open({
            templateUrl: "CardFlightSelectedData.html",
            controller: "CardFlightSelectedDataInstanceCtrl",
            resolve: {
              flight_selected: function () {
                return $scope.flight_selected;
              },
            },
          });
          modalInstance.result.then(function (flight_selected) {});
        };
      },
    ])
    .controller("CardFlightSelectedDataInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "flight_selected",
      function ($scope, $rootScope, $modalInstance, flight_selected) {
        $scope.flight_selected = flight_selected;

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("OnlineModalPermissionCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $rootScope.modalOpen = false;

        $rootScope.$on("openPermission", function (event, args) {
          event.stopPropagation();
          if ($rootScope.modalOpen == false) {
            $rootScope.modalOpen = true;
            $scope.open(args);
          }
        });

        $scope.open = function (args) {
          args.main.userEmail = "";
          args.main.userPassCode = "";
          args.main.pinCode = "";
          args.main.type = args.type;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "OnlineModalPermissionCtrl.html",
            controller: "OnlineModalPermissionInstanceCtrl",
            resolve: {
              permissions: function () {
                return args.main;
              },
              hashId: function () {
                return args.hashId;
              },
              selected: function () {
                return $scope.selected;
              },
            },
          });
          modalInstance.result.then(
            function (permissions) {
              $rootScope.modalOpen = false;
            },
            function () {
              $rootScope.modalOpen = false;
            }
          );
        };
      },
    ])
    .controller("OnlineModalPermissionInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "permissions",
      "hashId",
      "selected",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        logger,
        permissions,
        hashId,
        selected
      ) {
        $scope.permissions = permissions;
        $scope.hashId = hashId;
        $scope.selected = selected;

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };

        $scope.email = function () {
          $rootScope.$emit("fillEmailBLoqued", {
            email: $rootScope.onlineOrder.client_email,
          });
          $scope.cancel();
        };

        $scope.emailCommecial = function () {
          $rootScope.$emit("fillEmailBLoqued", {
            email: 'suporte@onemilhas.com.br'
          });
          $scope.cancel();
        };

        $scope.setStatusOrder = function (order, status) {
          if (!order) {
            order = $rootScope.onlineOrder;
          }
          $.post(
            "../backend/application/index.php?rota=/setStatusOrder",
            { hashId: $scope.hashId, data: order, status: status },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
            }
          );
        };

        $scope.checkCommercial = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { data: $scope.permissions },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $.post(
                  "../backend/application/index.php?rota=/setStatusOrderCommercial",
                  { data: $rootScope.onlineOrder },
                  function (result) {
                    logger.logSuccess(jQuery.parseJSON(result).message.text);
                  }
                );
                $modalInstance.close($scope.permissions);
              } else {
                logger.logError("Dados não conferem!");
              }
            }
          );
        };

        $scope.checkCommercialFaturado = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { data: $scope.permissions },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $.post(
                  "../backend/application/index.php?rota=/setStatusOrderCommercialFaturado",
                  {
                    data: $rootScope.onlineOrder,
                    faturado: $scope.permissions.days,
                  },
                  function (result) {
                    logger.logSuccess(jQuery.parseJSON(result).message.text);
                  }
                );
                $modalInstance.close($scope.permissions);
              } else {
                logger.logError("Dados não conferem!");
              }
            }
          );
        };

        $scope.check = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { hashId: $scope.hashId, data: $scope.permissions },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $modalInstance.close($scope.permissions);
                $rootScope.$emit("checkedPermission", {});
              } else {
                logger.logError("Dados não conferem!");
              }
            }
          );
        };
      },
    ])
    .controller("CardTaxFlightDataCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $scope.open = function () {
          $scope.flight_selected = this.onlineflight;
          $.post(
            "../backend/application/index.php?rota=/loadInternalCards",
            { hashId: $scope.session.hashId, data: $scope.flight_selected },
            function (result) {
              $scope.internalCards = jQuery.parseJSON(result).dataset;

              $scope.decript = function (code) {
                var data = code.split("320AB");
                var finaly = "";
                for (var j = 0; data.length > j; j++) {
                  finaly = finaly + String.fromCharCode(data[j] / 320);
                }
                return finaly;
              };

              for (var i in $scope.internalCards) {
                $scope.internalCards[i].password = $scope.decript(
                  $scope.internalCards[i].password
                );
              }
              if ($scope.flight_selected.card_number != " - ") {
                $.post(
                  "../backend/application/index.php?rota=/loadCardProvider",
                  {
                    hashId: $scope.session.hashId,
                    data: $scope.flight_selected,
                  },
                  function (result) {
                    $scope.TaxCard = jQuery.parseJSON(result).dataset;
                    if (
                      $scope.TaxCard.length == 1 &&
                      $scope.flight_selected.tax_card == undefined
                    ) {
                      $scope.flight_selected.tax_card =
                        $scope.TaxCard[0].card_number;
                      $scope.flight_selected.tax_providerName =
                        $scope.TaxCard[0].provider_name;
                    } else {
                      $scope.TaxCard = {};
                    }
                    $scope.originalFlight = angular.copy(
                      $scope.flight_selected
                    );
                    $scope.master =
                      $scope.$parent.$parent.$parent.$parent.main.isMaster ||
                      $scope.$parent.$parent.$parent.$parent.main.changeSale;
                    $scope.hashId =
                      $scope.$parent.$parent.$parent.$parent.session.hashId;
                    var modalInstance;
                    modalInstance = $modal.open({
                      templateUrl: "CardTaxFlightDataCtrl.html",
                      controller: "TaxaCardFlightDataInstanceCtrl",
                      resolve: {
                        internalCards: function () {
                          return $scope.internalCards;
                        },
                        originalFlight: function () {
                          return $scope.originalFlight;
                        },
                        flight_selected: function () {
                          return $scope.flight_selected;
                        },
                        TaxCard: function () {
                          return $scope.TaxCard;
                        },
                        master: function () {
                          return $scope.master;
                        },
                        hashId: function () {
                          return $scope.hashId;
                        },
                      },
                    });

                    modalInstance.result.then(function (TaxCard) {
                      for (
                        var i = 0;
                        i < $scope.$parent.$parent.onlineflights.length;
                        i++
                      ) {
                        if (
                          $scope.$parent.$parent.onlineflights[i].flight ==
                            $scope.flight_selected.flight &&
                          $scope.$parent.$parent.onlineflights[i]
                            .boarding_date ==
                            $scope.flight_selected.boarding_date
                        ) {
                          $scope.$parent.$parent.onlineflights[i].tax_card =
                            TaxCard.tax_card;
                          $scope.$parent.$parent.onlineflights[i].tax_password =
                            TaxCard.tax_password;
                          $scope.$parent.$parent.onlineflights[i].tax_cardType =
                            TaxCard.tax_cardType;
                          $scope.$parent.$parent.onlineflights[i].tax_dueDate =
                            TaxCard.tax_dueDate;
                          $scope.$parent.$parent.onlineflights[
                            i
                          ].tax_providerName = TaxCard.tax_providerName;
                        }
                      }
                    });
                  }
                );
              }
            }
          );
        };
      },
    ])
    .controller("TaxaCardFlightDataInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "$filter",
      "internalCards",
      "originalFlight",
      "flight_selected",
      "TaxCard",
      "master",
      "hashId",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        $filter,
        internalCards,
        originalFlight,
        flight_selected,
        TaxCard,
        master,
        hashId
      ) {
        $scope.internalCards = internalCards;
        $scope.flight_selected = originalFlight;
        $scope.flight_selected.pinCode = "";
        $scope.resume_creditCards = $filter("filter")(
          $scope.internalCards,
          $scope.flight_selected.airline
        );
        $scope.resume_creditCards.push({ card_number: "OUTRO" });
        $scope.TaxCard = TaxCard;
        $scope.master = master;
        $scope.hashId = hashId;
        $scope.change = false;
        $scope.checked = true;

        $scope.findDate = function (date) {
          var data = new Date(date);
          data.setDate(data.getDate() + 1);
          return data;
        };

        $scope.changeValue = function () {
          $scope.change = true;
        };

        $scope.check = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { hashId: $scope.hashId, data: $scope.flight_selected },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $scope.flight_selected.userEmail = $scope.response.userEmail;
                $scope.checked = false;
                $scope.change = false;
                $scope.$apply();
              } else {
                $scope.checked = true;
                logger.logError("Dados não conferem!");
                $scope.$apply();
              }
            }
          );
        };

        $scope.search = function () {
          $scope.TaxCard = $filter("filter")(
            $scope.resume_creditCards,
            $scope.filter
          );
          $scope.flight_selected.tax_card = $scope.TaxCard[0].card_number;
          $scope.flight_selected.tax_password = $scope.TaxCard[0].password;
          $scope.flight_selected.tax_cardType = $scope.TaxCard[0].card_type;
          $scope.flight_selected.tax_dueDate = new Date(
            $scope.TaxCard[0].due_date
          );
          $scope.flight_selected.tax_providerName =
            $scope.TaxCard[0].provider_name;
          $scope.flight_selected.provider_registration =
            $scope.TaxCard[0].provider_registration;
          $scope.flight_selected.providerAdress =
            $scope.TaxCard[0].providerAdress;
          $scope.flight_selected.providerPhone =
            $scope.TaxCard[0].providerPhone;
          $scope.flight_selected.providerEmail =
            $scope.TaxCard[0].providerEmail;
          $scope.flight_selected.provider_adress =
            $scope.TaxCard[0].provider_adress;
          $scope.flight_selected.birthdate = new Date(
            $scope.TaxCard[0].birthdate
          );
        };
        $scope.ok = function () {
          flight_selected = $scope.flight_selected;
          $modalInstance.close($scope.flight_selected);
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("FindAllCardsInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "main",
      function ($scope, $rootScope, $modalInstance, logger, main) {
        $scope.main = main;
        $scope.flight_selected = { pinCode: "" };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };

        $scope.check = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { data: $scope.flight_selected },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $modalInstance.close($scope.response);
              } else {
                logger.logError("Dados não conferem!");
              }
            }
          );
        };
      },
    ])
    .controller("CloseOrder", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $scope.open = function () {
          $scope.flight_selected = $scope.$parent.selected;
          if (
            $scope.flight_selected.comments.length > 0 ||
            $scope.$parent.findSpecialCards()
          ) {
            var modalInstance;
            modalInstance = $modal.open({
              templateUrl: "CloseOrder.html",
              controller: "CloseOrderInstanceCtrl",
              resolve: {
                flight_selected: function () {
                  return $scope.flight_selected;
                },
                onlineflights: function () {
                  return $scope.$parent.onlineflights;
                },
              },
            });
            modalInstance.result.then(function () {
              $scope.$parent.saveOrder();
            });
          } else {
            $scope.$parent.saveOrder();
          }
        };
      },
    ])
    .controller("CloseOrderInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "flight_selected",
      "onlineflights",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        logger,
        flight_selected,
        onlineflights
      ) {
        $scope.flight_selected = flight_selected;
        $scope.onlineflights = onlineflights;

        $scope.findSpecialCardsLATAM = function () {
          if ($scope.onlineflights) {
            if ($scope.onlineflights.length > 0) {
              for (var i in $scope.onlineflights) {
                if (
                  $scope.onlineflights[i].card_type == "RED" ||
                  $scope.onlineflights[i].card_type == "BLACK"
                )
                  return true;
              }
            }
          }
          return false;
        };

        $scope.findSpecialCardsGOL = function () {
          if ($scope.onlineflights) {
            if ($scope.onlineflights.length > 0) {
              for (var i in $scope.onlineflights) {
                if (
                  $scope.onlineflights[i].card_type == "DIAMANTE" ||
                  $scope.onlineflights[i].card_type == "CLUBE DIAMANTE"
                )
                  return true;
              }
            }
          }
          return false;
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };

        $scope.ok = function () {
          $modalInstance.close();
        };
      },
    ])
    .controller("ClientWarningInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "selected",
      function ($scope, $rootScope, $modalInstance, logger, selected) {
        $scope.selected = selected;

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("OrderStatusModalCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "order",
      "main",
      function ($scope, $rootScope, $modalInstance, logger, order, main) {
        $scope.main = main;
        $scope.selected = order;

        $scope.setStatusOrder = function (status) {
          $.post(
            "../backend/application/index.php?rota=/setStatusOrder",
            { data: $scope.selected, status: status },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.tabindex = 0;
              $scope.cancel();
            }
          );
        };

        $scope.checkCommercial = function () {
          $.post(
            "../backend/application/index.php?rota=/setStatusOrderCommercial",
            { data: $scope.selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.cancel();
            }
          );
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("OrderNotificationModalCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "order",
      "onlineflights",
      "resume_paxs",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        logger,
        order,
        onlineflights,
        resume_paxs
      ) {
        $scope.selected = order;
        $scope.onlineflights = onlineflights;
        $scope.resume_paxs = resume_paxs;
        $scope.retarifation = false;

        $scope.inf = 0;
        $scope.chd = 0;
        $scope.adt = 0;
        for (var i = 0; $scope.resume_paxs.length > i; i++) {
          if ($scope.resume_paxs[i].is_child == "S") $scope.chd++;
          else if ($scope.resume_paxs[i].is_newborn == "S") $scope.inf++;
          else $scope.adt++;
        }

        $scope.retarifation_opts = [];
        for (let i in onlineflights) {
          let path = "IDA";
          if (i != "0") {
            path = "VOLTA";
          }

          $scope.retarifation_opts.push({
            milhas_adulto: parseFloat(onlineflights[i].miles_per_adult),
            milhas_crianca: parseFloat(onlineflights[i].miles_per_child),
            milhas_bebe: parseFloat(onlineflights[i].miles_per_newborn),
            data_embarque: onlineflights[i].boarding_date,
            voo: onlineflights[i].flight,
            origem: onlineflights[i].airport_code_from,
            destino: onlineflights[i].airport_code_to,
            retarifation: false,
            path: path,
          });
        }

        $scope.setNotificationOrder = function (status) {
          if (status != "3") {
            $scope.retarifation_opts = undefined;
          } else {
            var retarifation = false;
            for (var i in $scope.retarifation_opts) {
              if ($scope.retarifation_opts[i].retarifation == true) {
                retarifation = true;
              }
            }
            if (!retarifation) {
              return logger.logError("Retarifação deve ser marcada");
            }
          }

          $.post(
            "../backend/application/index.php?rota=/setNotificationOrder",
            {
              data: $scope.selected,
              status: status,
              retarifation: $scope.retarifation_opts,
            },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $modalInstance.close(true);
            }
          );
        };

        $scope.setNotification = function () {
          $scope.retarifation = true;
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("OrdersNotificationModalCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "order",
      function ($scope, $rootScope, $modalInstance, logger, order) {
        $scope.selected = order;

        $.post(
          "../backend/application/index.php?rota=/loadNotificationOrder",
          { data: $scope.selected },
          function (result) {
            $scope.notifications = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          }
        );

        $scope.findDate = function (date) {
          return new Date(date);
        };

        $scope.reSendBillets = function () {
          $.post(
            "../backend/application/index.php?rota=/reSendBillets",
            { data: $scope.selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
            }
          );
        };

        $scope.reSendOrder = function () {
          $.post(
            "../backend/application/index.php?rota=/reSendOrder",
            { data: $scope.selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
            }
          );
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ]);
})();
