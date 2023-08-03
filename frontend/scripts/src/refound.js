(function () {
  'use strict';
  angular.module('app.table').controller('RefoundCtrl', [
    '$scope', '$rootScope', '$route', '$filter', 'cfpLoadingBar', 'logger', '$modal', '$element', function ($scope, $rootScope, $route, $filter, cfpLoadingBar, logger, $modal, $element) {
      var init;

      $scope.saleStatus = [
        'Pendente',
        'Emitido',
        'Reembolso Solicitado',
        'Reembolso Pagante Solicitado',
        'Reembolso CIA',
        'Reembolso Confirmado',
        'Cancelamento Solicitado',
        'Cancelamento Efetivado',
        'Cancelamento Nao Solicitado',
        'Remarcação Solicitado',
        'Remarcação Confirmado',
        'Cancelamento Pendente',
        'Reembolso Pendente',
        'Reembolso No-show Solicitado',
        'Reembolso No-show Confirmado',
        'Reembolso Nao Solicitado',
        'Reembolso Perdido'
      ];
      $scope.searchKeywords = '';
      $scope.filteredSales = [];
      $scope.row = '';
      $scope.saleMethods = [
        'JACK FOR',
        'Loja TAM Ponta Grossa',
        'Loja TAM Contagem',
        'Rextur Advance',
        'Loja TAM M',
        'CONFIANÇA',
        'TAP',
        'Outros'
      ];

      $scope.safePossibilities = [
        { method: 'Faturado' },
        { method: 'Cartao' }
      ];

      $scope.removeFromRefundReport = [
        'BASE CFTUR',
        'cftur@gmail.com',
        'CFTUR@GMAIL.COM',
        'turismostar@hotmail.com',
        'TURISMOSTAR@HOTMAIL.COM'
      ];

      $scope.select = function (page) {
        $scope.currentPage = page;
        // var end, start;
        // start = (page - 1) * $scope.numPerPage;
        // end = start + $scope.numPerPage;
        // $scope.$apply();
        // return $scope.currentPageSales = $scope.filteredSales.slice(start, end);
        $scope.loadOrders();
      };

      $scope.onFilterChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };

      $scope.onNumPerPageChange = function () {
        // $scope.select(1);
        // return $scope.currentPage = 1;
        $scope.loadOrders();
      };

      $scope.onOrderChange = function () {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.findDate = function (date) {
        if (date != '') {
          return new Date(date);
        } else {
          return '';
        }
      };

      $scope.setSelected = function () {
        $scope.selected = {};
        $scope.repricing = {};
        if (this.sale.status != "Cancelado") {
          $scope.selected = this.sale;
          $('#salemilesused').number(true, 0, ',', '.');
          $('#saletax').number(true, 2, ',', '.');
          $('#saledutax').number(true, 2, ',', '.');
          $('#saletotalcost').number(true, 2, ',', '.');
          $('#saleamountpaid').number(true, 2, ',', '.');
          $('#saleextrafee').number(true, 2, ',', '.');
          $('#salekickback').number(true, 2, ',', '.');
          $('#milesMoney').number(true, 2, ',', '.');
          $('#totalCost').number(true, 2, ',', '.');
          $('#newMiles').number(true, 0, ',', '.');
          // $('#newValue').number( true, 2, ',', '.');
          $("#newValue").maskMoney({ thousands: '.', decimal: ',', precision: 2 });
          // $('#repricingCost').number( true, 2, ',', '.');
          $("#repricingCost").maskMoney({ thousands: '.', decimal: ',', precision: 2 });

          $scope.ValueAirlines = {};
          $.post("../backend/application/index.php?rota=/loadPlanControlDetails", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            $scope.ValueAirlines = jQuery.parseJSON(result).dataset;
            $scope.repricing.cost = $scope.ValueAirlines.repricing;
            $scope.$digest();
          });

          $scope.tabindex = 1;

          $.post("../backend/application/index.php?rota=/loadBilletFinancial", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            $scope.selected.billetStatus = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });

          $.post("../backend/application/index.php?rota=/loadLog", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            $scope.saleLog = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });

          $scope.airlineShowFields = ($scope.selected.airline == 'TAM' || $scope.selected.airline == 'LATAM');
          $scope.boardingDate = new Date($scope.selected.boardingDate);
          $scope.landingDate = new Date($scope.selected.landingDate);
          $scope.paxBirthdate = new Date($scope.selected.paxBirthdate + 'T12:00:00Z');
          if ($scope.selected.status == "Reembolso Solicitado" || $scope.selected.status == "Reembolso Pagante Solicitado" || $scope.selected.status == "Reembolso No-show Solicitado" || $scope.selected.status == "Reembolso CIA") {
            $.post("../backend/application/index.php?rota=/loadBillsPayRefund", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
              $scope.selected.billsPayValue = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            });

          }
          $.post("../backend/application/index.php?rota=/loadLastPurchaseData", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            $scope.selected.lastPurchase = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });


          $scope.airports.filter(function(a) {
            return a.code == $scope.selected.from
          })[0].label

          $scope.repricing.airportNamefrom = $scope.airports.filter(function(a) {
            return a.code == $scope.selected.from
          })[0].label;
          $scope.repricing.airportNameto = $scope.airports.filter(function(a) {
            return a.code == $scope.selected.to
          })[0].label;
          $scope.repricing.boardingDate = $scope.boardingDate;
          $scope.repricing.landingDate = $scope.landingDate;
          $scope.repricing.flight = $scope.selected.flight;
          $scope.repricing.flightLocator = $scope.selected.flightLocator;
          $scope.repricing.flightHour = $scope.selected.flightHour;
          //$scope.repricing.milesused = $scope.selected.milesused;
          $scope.repricing.milesused = 0;

          return true;
        }
        else {
          logger.logError("Esta venda foi cancelada!");
        }
      };

      $scope.milesusedBlur = function(){
        if($scope.repricing.milesused == undefined || $scope.repricing.milesused == ''){
          $scope.repricing.milesused = 0;
        }
      }

      $scope.registerLog = function () {
        $.post("../backend/application/index.php?rota=/saveSaleOccurrence", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $.post("../backend/application/index.php?rota=/loadLog", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            $scope.saleLog = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
        });
      };

      $scope.repricingMulct = function () {
        var operation = {};
        var operations = [];

        for (var x in $scope.airlines) {
          if ($scope.airlines[x].name == $scope.selected.airline) {
            operations = $scope.airlines[x].operations;
            for (var x in operations) {
              if (operations[x].type == "Remarcação") {
                operation = operations[x];
              }
            }
          }
        }

        var actualDate = new Date();
        var selectedDate = new Date($scope.selected.boardingDate);
        if ($scope.selected.airline == "AVIANCA") {
          selectedDate.setHours(selectedDate.getHours() + 5);
        } else {
          selectedDate.setHours(selectedDate.getHours() + 3);
        }

        if (actualDate < selectedDate) {
          if ($scope.selected.international) {
            if ($scope.selected.airportLocation == "America Norte" && operation.northAmericaBeforeBoarding != 0) {
              return operation.northAmericaBeforeBoarding;

            } else if ($scope.selected.airportLocation == "America Sul" && operation.southAmericaBeforeBoarding != 0) {
              return operation.southAmericaBeforeBoarding;

            } else {
              return operation.internationalBeforeBoarding;
            }
          } else {
            return operation.nationalBeforeBoarding;
          }
        } else {
          if ($scope.selected.international) {
            if ($scope.selected.airportLocation == "America Norte" && operation.northAmericaAfterBoarding != 0) {
              return operation.northAmericaAfterBoarding;

            } else if ($scope.selected.airportLocation == "America Sul" && operation.southAmericaAfterBoarding != 0) {
              return operation.southAmericaAfterBoarding;

            } else {
              return operation.internationalAfterBoarding;
            }
          } else {
            return operation.nationalAfterBoarding;
          }
        }
      };

      $scope.toggleFormTable = function () {
        $scope.tabindex = 0;
      };

      $scope.search = function () {
        // $scope.filteredSales = $filter('filter')($scope.sales, $scope.searchKeywords);
        // return $scope.onFilterChange();
        $scope.loadOrders();
      };

      $scope.order = function (rowName) {
        // if ($scope.row === rowName) {
        //   return;
        // }
        // $scope.row = rowName;
        // $scope.filteredSales = $filter('orderBy')($scope.sales, rowName);
        // return $scope.onOrderChange();
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadOrders();
      };

      $scope.formatNumber = function (number, decimalsLength, decimalSeparator, thousandSeparator) {
        return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
      };

      $scope.saleTag = function (status) {
        switch (status) {
          case 'Pendente':
            return "label label-info";
          case 'Emitido':
            return "label label-success";
          case 'Remarcação Confirmado':
            return "label label-success";
          case 'Reembolso Solicitado':
            return "label label-warning";
          case 'Reembolso Pagante Solicitado':
            return "label label-warning";
          case 'Reembolso CIA':
            return "label label-warning";
          case 'Remarcação Solicitado':
            return "label label-warning";
          case 'Reembolso Confirmado':
            return "label label-default";
          case 'Cancelamento Solicitado':
            return "label label-warning";
          case 'Cancelamento Efetivado':
            return "label label-danger";
          case 'Reembolso No-show Solicitado':
            return "label label-default";
          case 'Reembolso No-show Confirmado':
            return "label label-default";
          case 'Reembolso Nao Solicitado':
            return "label label-default";
          case 'Cancelamento Nao Solicitado':
            return "label label-default";
          case 'Reembolso Perdido':
              return "label label-default";
        }
      };

      $scope.noRefundModal = function (is_perdido) {
        $scope.is_perdido = is_perdido;
        var modalInstance = $modal.open({
          size: "lg",
          templateUrl: "reembolsoConfirm.html",
          controller: "reembolsoModalInstanceCtrl",
          scope: $scope
        });

        modalInstance.result.then(function(resolve) {
          $scope.noRefund();
        }, function() {
          //$log.info("Modal dismissed at: " + new Date());
        });
      };

      $scope.noRefund = function () {
        $.post("../backend/application/index.php?rota=/noRefund", { hashId: $scope.session.hashId, data: $scope.selected, is_perdido:$scope.is_perdido }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadOrders();
        });
      };

      $scope.confirmRepricing = function () {
        $.post("../backend/application/index.php?rota=/confirmRepricing", { data: $scope.selected }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadOrders();
        });
      };

      $scope.numPerPageOpt = [10, 25, 50, 150];
      $scope.numPerPage = $scope.numPerPageOpt[1];
      $scope.currentPage = 1;
      $scope.currentPageSales = [];
      $rootScope.hashId = $scope.session.hashId;

      $scope.closeRefund = function () {
        if(!$scope.selected.returnDate){
          logger.logError('O campo de data de retorno deve ser informado.');
        }
        else{
          cfpLoadingBar.start();
          $scope.selected._returnDate = $rootScope.formatServerDate($scope.selected.returnDate);
          $.post("../backend/application/index.php?rota=/closeRefund", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadOrders();
          });
          cfpLoadingBar.complete();
          $scope.toggleFormTable();
        }
      };

      $scope.addRow = function () {
        if (this.sale.status !== 'Pendente') {
          this.sale.checked = false;
        }
      };

      $scope.ClosecancelSale = function () {
        cfpLoadingBar.start();
        console.log($scope.selected.returnPointsDate);
        if (!$scope.selected.returnPointsDate) {
          cfpLoadingBar.complete();
          return logger.logError('Necessario marcar a data do retorno dos pontos');
        }
        if ($scope.selected.returnPointsDate !== "" && $scope.selected.returnPointsDate != 'Invalid Date') {
          $scope.selected._returnPointsDate = $rootScope.formatServerDate($scope.selected.returnPointsDate);
        } else {
          cfpLoadingBar.complete();
          return logger.logError('Necessario marcar a data do retorno dos pontos');
        }
        $.post("../backend/application/index.php?rota=/ClosecancelSale", { data: $scope.selected }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadOrders();
        });
      };

      $scope.notConfirmedCancelSale = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/notConfirmedCancelSale", { data: $scope.selected }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadOrders();
        });
      };

      $scope.cancelSale = function () {
        cfpLoadingBar.start();
        if ($scope.selected.cancelDate !== "" && $scope.selected.cancelDate != 'Invalid Date') {
          $scope.selected._cancelDate = $rootScope.formatServerDate($scope.selected.cancelDate);
        }
        $.post("../backend/application/index.php?rota=/cancelSale", { data: $scope.selected }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadOrders();
        });
      };

      $scope.back = function () {
        $scope.tabindex--;
      };

      $scope.getMulta = function (selected) {
        return $scope.ValueAirlines.refund;
      };

      $scope.mailOrder = function () {
        $.post("../backend/application/index.php?rota=/mailOrder", { hashId: $scope.session.hashId, data: $scope.wsalemail, emailType: 'EMAIL-GMAIL'}, function (result) {
          if (jQuery.parseJSON(result).message.type == 'S') {
            $scope.loadOrders();
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          } else {
            $scope.loadOrders();
            logger.logError(jQuery.parseJSON(result).message.text);
          }
        });
      };

      $scope.getvalue = function (selected) {
        var mulct = $scope.getMulta(selected);
        var data = "";
        if (selected.amountPaid - mulct > 0)
          data = "Crédito: R$ " + $rootScope.formatNumber(selected.amountPaid - mulct);
        else
          data = "Débito: R$ " + $rootScope.formatNumber(selected.amountPaid - mulct);
        return data;
      };

      $scope.fillEmailCancel = function () {
        $scope.wsalemail.emailpartner = 'arthur.srmviagens@gmail.com';
        $scope.wsalemail.mailcc = 'arthur.srmviagens@gmail.com';
        $scope.wsalemail.subject = '[Orçamento] - Orçamento de Cancelamento';
        $scope.wsalemail.emailContent = "Ola!<br><br>" +
          "Caso de cancelamento.<br><br>" +
          "Trecho: " + $scope.selected.airportNamefrom + " - " + $scope.selected.airportNameto + "<br>" +
          "Data embarque: " + $filter('date')(new Date($scope.selected.boardingDate), 'dd/MM/yyyy hh:mm:ss') + '-' + $filter('date')(new Date($scope.selected.landingDate), 'dd/MM/yyyy hh:mm:ss') + "<br>" +
          "Conforme solicitado o custo para cancelamento de bilhetes é:<br><br>" +
          "R$ 60,00 por trecho/pax<br><br>" +
          "Favor responder com OK para dar seguimento no processo de cancelamento.<br><br>" +
          "Att,";
        $scope.tabindex = 2;
      };

      $scope.fillEmailRepricing = function () {
        $scope.wsalemail.emailpartner = 'arthur.srmviagens@gmail.com';
        $scope.wsalemail.mailcc = 'arthur.srmviagens@gmail.com';
        $scope.wsalemail.subject = '[Orçamento] - Orçamento de Remarcação';

        $scope.wsalemail.emailContent = "Ola!<br><br>" +
          "Caso de Remarcação.<br><br>" +
          "Trecho: " + $scope.repricing.airportNamefrom + '-' + $scope.repricing.airportNameto + "<br>" +
          "Data embarque: " + $filter('date')(new Date($scope.repricing.boardingDate), 'dd/MM/yyyy hh:mm:ss') + '-' + $filter('date')(new Date($scope.repricing.landingDate), 'dd/MM/yyyy hh:mm:ss') + "<br>" +
          "Diferença Trecho: R$" + $rootScope.formatNumber($scope.repricing.milesused * ($scope.repricing.newValue / 1000)) + " - (" + $rootScope.formatNumber($scope.repricing.milesused, 0) + ")<br>" +
          "Multa: R$ " + $rootScope.formatNumber($scope.repricing.cost) + "<br><br>" +
          "Valor total para remarcação: R$ " + $rootScope.formatNumber($scope.repricing.cost + ($scope.repricing.milesused * ($scope.repricing.newValue / 1000))) + "<br><br>" +
          "Favor responder com OK para dar seguimento no processo de remarcação.<br><br>Att,";
        $scope.tabindex = 2;
      };

      $scope.fillEmailRefund = function () {
        $scope.wsalemail.emailpartner = 'arthur.srmviagens@gmail.com';
        $scope.wsalemail.mailcc = 'arthur.srmviagens@gmail.com';
        $scope.wsalemail.subject = '[Orçamento] - Orçamento de Reembolso';
        $scope.wsalemail.emailContent = "Ola!<br><br>";

        var issueDate = new Date($scope.selected.issueDate);
        if ($scope.selected.issuing == 'app.voelegal' || $scope.selected.issuing == 'srmapp.voelegal') {
          issueDate.setDate(issueDate.getDate() + 365);
        } else {
          issueDate.setDate(issueDate.getDate() + 60);
        }

        if (issueDate < new Date()) {
          logger.logError("REEMBOLSO SUPERIOR A 60 DIAS");
          $scope.wsalemail.emailContent += "<br><br><font color='red'>REEMBOLSO SUPERIOR A 60 DIAS</font><br><br>";
        }
        if ($scope.selected.saleType == 'Cartao') {
          logger.logError("REEMBOLSO VENDA CARTÃO");
          $scope.wsalemail.emailContent += "<br><br><font color='red'>REEMBOLSO DE VENDA POR CARTÃO</font><br><br>";
        }

        $scope.wsalemail.emailContent += "Caso de reembolso.<br><br>";
        $scope.wsalemail.emailContent = $scope.wsalemail.emailContent + "O valor pago no bilhete terá o desconto referente a multa e o restante ficará de crédito.<br><br>" +
          "PAX: " + $scope.selected.paxName + "  ( " + $scope.selected.flightLocator;
        if ($scope.selected.airline == "TAM" || $scope.selected.airline == "LATAM")
          $scope.wsalemail.emailContent += " - " + $scope.selected.ticket_code;
        $scope.wsalemail.emailContent += " )  " + $scope.selected.airline + "<br>" +
          "VOO : " + $filter('date')(new Date($scope.selected.boardingDate), 'dd/MM/yyyy') + "<br>" +
          "Pago: R$ " + $rootScope.formatNumber($scope.selected.amountPaid) + " - " + $rootScope.formatNumber($scope.selected.milesOriginal, 0) + " PTS<br>" +
          "Multa: - R$ " + $rootScope.formatNumber($scope.getMulta($scope.selected)) + "<br>" +
          $scope.getvalue($scope.selected) + "<br><br>" +
          "Para agência o crédito será descontado do próximo borderô.<br><br>" +
          "Favor responder com OK para dar seguimento no processo de reembolso.<br><br>" +
          "Att,";
        $scope.tabindex = 2;
      };

      $scope.fillEmailContent = function () {
        if ($scope.selected.airline == 'GOL') {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/modal_confirmation_gol.html",
            controller: 'ModalConfirmationCtrl',
            resolve: {
              header: function () {
                return 'Orçamento de Reembolso';
              }
            }
          });
          modalInstance.result.then((function (filterSales) {
            $scope.fillEmailRefund();
          }));
        }
        if ($scope.selected.sale_method == 'CONFIANÇA' || $scope.selected.sale_method == 'Rextur Advance') {
          var modalInstanceconfianca;
          modalInstanceconfianca = $modal.open({
            templateUrl: "app/modals/modal_confirmation.html",
            controller: 'ModalConfirmationCtrl',
            resolve: {
              header: function () {
                return 'Bilhete pagante. Confira tarifas!';
              }
            }
          });
          modalInstanceconfianca.result.then((function (filterSales) {
            $scope.fillEmailRefund();
          }));

        } else {
          $scope.fillEmailRefund();
        }

      };

      $scope.findRefundByFilter = function () {
        if ($scope.showRefunds) {
          $scope.showRefunds = false;
          $scope.filter.status = '';
          $scope.filter.status2 = '';
        } else {
          $scope.showRefunds = true;
          $.post("../backend/application/index.php?rota=/loadRefundByFilter", { hashId: $scope.session.hashId }, function (result) {
            $scope.refundsChart = jQuery.parseJSON(result).dataset;
          });
          var date = new Date();
          $scope.filter._refundDateFrom = $rootScope.formatServerDate(new Date(date.getFullYear(), date.getMonth(), 1));
          $scope.filter._returnDateFrom = $rootScope.formatServerDate(new Date(date.getFullYear(), date.getMonth(), 1));
          $scope.loadOrders();
        }
      };

      $scope.ConfirmSolicitation = function () {
        $scope.selected._airlineSolicitation = $rootScope.formatServerDate($scope.selected.airlineSolicitation);
        $.post("../backend/application/index.php?rota=/confirmRefundSolicitation", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadOrders();
        });
      };

      $scope.printNoShowListOrderBy = function () {
        $.post("../backend/application/index.php?rota=/loadSalesToOperations", { searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter, refund_report: true }, function (result) {
          $scope.salesToReport = jQuery.parseJSON(result).dataset.sales;
          var doc = new jsPDF('p', 'pt');
          doc.margin = 0.5;
          doc.setFontSize(18);
          doc.text(150, 30, 'Conferencia');

          var columns = [
            { title: "Fornecedor", dataKey: "partner" },
            { title: "Pax", dataKey: "pax" },
            { title: "Embarque", dataKey: "boardingDate" },
            { title: "ETicket", dataKey: "ticket_code" },
            { title: "Trecho", dataKey: "fromTo" },
            { title: "Localizador", dataKey: "flightLocator" },
            { title: "Pontos", dataKey: "miles" }
          ];

          var rows = [];
          // $scope.filteredSales = _.orderBy($scope.salesToReport, ['providerName', 'flightLocator']);
          $scope.filteredSales = $scope.salesToReport;

          for (var i in $scope.filteredSales) {

            var partnerName = $scope.filteredSales[i].providerName;
            if ($scope.filteredSales[i].saleByThird == 'Y') {
              partnerName = $scope.filteredSales[i].sale_method;
            }

            if ($scope.removeFromRefundReport.indexOf($scope.filteredSales[i].cards_provider_email) > -1) {
              console.log('avoid');
            } else {
              rows.push({
                partner: partnerName,
                pax: $scope.filteredSales[i].paxName,
                boardingDate: $filter('date')(new Date($scope.filteredSales[i].boardingDate), 'dd/MM/yyyy hh:mm:ss'),
                ticket_code: $scope.filteredSales[i].ticket_code,
                fromTo: $scope.filteredSales[i].from + '-' + $scope.filteredSales[i].to,
                flightLocator: $scope.filteredSales[i].flightLocator,
                miles: $rootScope.formatNumber($scope.filteredSales[i].milesused, 0)
              });
            }
          }

          doc.autoTable(columns, rows, {
            styles: {
              overflow: 'linebreak',
              fontSize: 6,
              margin: { horizontal: 4 },
              columnWidth: 'auto'
            },
            createdCell: function (cell, data) {
              if (data.column.dataKey === 'miles') {
                cell.styles.halign = 'right';
              }
            }
          });
          doc.save('Relatorio.pdf');
        });
      };

      $scope.printNoShowList = function () {
        $.post("../backend/application/index.php?rota=/loadSalesToOperations", { searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function (result) {
          $scope.filteredSales = jQuery.parseJSON(result).dataset.sales;
          var doc = new jsPDF('p', 'pt');
          doc.margin = 0.5;
          doc.setFontSize(18);
          doc.text(150, 30, 'Conferencia');

          var columns = [
            { title: "Fornecedor", dataKey: "partner" },
            { title: "Pax", dataKey: "pax" },
            { title: "Embarque", dataKey: "boardingDate" },
            { title: "ETicket", dataKey: "ticket_code" },
            { title: "Trecho", dataKey: "fromTo" },
            { title: "Localizador", dataKey: "flightLocator" },
            { title: "Pontos", dataKey: "miles" }
          ];

          var rows = [];

          for (var i in $scope.filteredSales) {

            var partnerName = $scope.filteredSales[i].providerName;
            if ($scope.filteredSales[i].saleByThird == 'Y') {
              partnerName = $scope.filteredSales[i].sale_method;
            }

            if ($scope.removeFromRefundReport.indexOf($scope.filteredSales[i].cards_provider_email) > -1) {
              console.log('avoid');
            } else {
              rows.push({
                partner: partnerName,
                pax: $scope.filteredSales[i].paxName,
                boardingDate: $filter('date')(new Date($scope.filteredSales[i].boardingDate), 'dd/MM/yyyy hh:mm:ss'),
                ticket_code: $scope.filteredSales[i].ticket_code,
                fromTo: $scope.filteredSales[i].from + '-' + $scope.filteredSales[i].to,
                flightLocator: $scope.filteredSales[i].flightLocator,
                miles: $rootScope.formatNumber($scope.filteredSales[i].milesused, 0)
              });
            }
          }

          doc.autoTable(columns, rows, {
            styles: {
              fontSize: 6,
              overflow: 'linebreak',
              margin: { horizontal: 4 },
              columnWidth: 'auto'
            },
            createdCell: function (cell, data) {
              if (data.column.dataKey === 'miles') {
                cell.styles.halign = 'right';
              }
            }
          });
          doc.save('Relatorio.pdf');
        });
      };

      $scope.reportSales = function () {
        $.post("../backend/application/index.php?rota=/loadSalesToOperations", { order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function (result) {
          $scope.salesToReport = jQuery.parseJSON(result).dataset.sales;
          var data = [['Data Venda', 'Fornecedor', 'Pax', 'Embarque', 'ETicket', 'Localizador', 'Milhas usadas', 'Milhas vendidas', 'Trecho', 'Empresa', 'Emissor', 'Usuario', 'Valor', 'Companhia', 'Categoria', 'Tipo Cartao', 'notificationcode', 'Taxa Embarque', 'Taxa DU', 'Bagagem', 'Conforto', 'Custo cancelamento', 'cartao de credito', 'nacional\internacional']];

          for (var i in $scope.salesToReport) {

            var partnerName = $scope.salesToReport[i].providerName;
            if ($scope.salesToReport[i].saleByThird == 'Y') {
              partnerName = $scope.salesToReport[i].sale_method;
            }

            data.push([
              $filter('date')(new Date($scope.salesToReport[i].issueDate), 'dd/MM/yyyy hh:mm:ss'),
              partnerName,
              $scope.salesToReport[i].paxName,
              $filter('date')(new Date($scope.salesToReport[i].boardingDate), 'dd/MM/yyyy hh:mm:ss'),
              $scope.salesToReport[i].ticket_code ? $scope.salesToReport[i].ticket_code : ' ',
              $scope.salesToReport[i].flightLocator,
              $rootScope.formatNumber($scope.salesToReport[i].milesused, 0),
              $rootScope.formatNumber($scope.salesToReport[i].milesOriginal, 0),
              $scope.salesToReport[i].from + '-' + $scope.salesToReport[i].to,
              $scope.salesToReport[i].client,
              $scope.salesToReport[i].issuing,
              $scope.salesToReport[i].user,
              $scope.salesToReport[i].amountPaid,
              $scope.salesToReport[i].airline,
              $scope.salesToReport[i].flight_category,
              $scope.salesToReport[i].cards_type ? $scope.salesToReport[i].cards_type : ' ',
              $scope.salesToReport[i].notificationcode ? $scope.salesToReport[i].notificationcode : ' ',
              $scope.salesToReport[i].tax,
              $scope.salesToReport[i].duTax,
              $scope.salesToReport[i].baggage_price ? $scope.salesToReport[i].baggage_price : ' ',
              $scope.salesToReport[i].special_seat ? $scope.salesToReport[i].special_seat : ' ',
              ($scope.salesToReport[i].status.indexOf('Cancelamento') > -1) ? $scope.salesToReport[i].totalCost : '0',
              "=" + '"' + parseInt($scope.salesToReport[i].cardTax) + '"',
              $scope.salesToReport[i].international == true ? 'Internacional' : 'Nacional'
            ]);
          }

          var csvContent = "data:text/csv;charset=utf-8,";
          var dataString;

          data.forEach(function (infoArray, index) {

            dataString = infoArray.join(";");
            csvContent += index < data.length ? dataString + "\n" : dataString;

          });

          //console.log(csvContent);

          var encodedUri = encodeURI(csvContent);

          var link = document.createElement("a");
          link.setAttribute("href", encodedUri);
          link.setAttribute("download", "my_data.csv");
          document.body.appendChild(link);

          link.click();
        });
      };

      $scope.refundsToCsv = function () {
        var data = [['Data_Venda', 'Status', 'Data', 'Agencia', 'PAX', 'E-TICKET', 'LOC', 'TIP CART', 'PONTOS', 'CARTAO', 'CREDITO', 'PEDIDO', 'VALOR', 'DATA VOO', 'TRECHO', 'RETORNO', 'PROCESSO', 'MULTA', 'VALOR CLIENTE']];

        $.post("../backend/application/index.php?rota=/loadRefundSales", { hashId: $scope.session.hashId }, function (result) {
          $scope.refundSales = jQuery.parseJSON(result).dataset;

          for (var i in $scope.refundSales) {
            data.push([
              $scope.refundSales[i].issueDate,
              $scope.refundSales[i].status,
              $scope.refundSales[i].refundDate,
              $scope.refundSales[i].client,
              $scope.refundSales[i].paxName,
              $scope.refundSales[i].ticket_code,
              $scope.refundSales[i].flightLocator,
              $scope.refundSales[i].cards_type,
              $rootScope.formatNumber($scope.refundSales[i].milesused, 0),
              $scope.refundSales[i].providerName,
              $scope.refundSales[i].cardTax,
              $scope.refundSales[i].airlineSolicitation,
              $rootScope.formatNumber($scope.refundSales[i].valueCia),
              $scope.refundSales[i].boardingDate,
              $scope.refundSales[i].airportFrom + '-' + $scope.refundSales[i].airportTo,
              $scope.refundSales[i].returnDate,
              $scope.refundSales[i].multc,
              $scope.refundSales[i].amount
            ]);
          }

          var csvContent = "data:text/csv;charset=utf-8,";
          var dataString;
          data.forEach(function (infoArray, index) {

            dataString = infoArray.join(";");
            csvContent += index < data.length ? dataString + "\n" : dataString;

          });

          var encodedUri = encodeURI(csvContent);
          var link = document.createElement("a");
          link.setAttribute("href", encodedUri);
          link.setAttribute("download", "my_data.csv");
          document.body.appendChild(link);

          link.click();
        });
      };

      $scope.backToSale = function () {
        $scope.tabindex = 0;
      };

      $scope.toRefundConference = function () {
        $.post("../backend/application/index.php?rota=/loadRefundsToConference", { filter: $scope.filterConference }, function (result) {
          $scope.refunds = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
        $scope.tabindex = 3;
      };

      $scope.setRefundCheck = function (refund) {
        $.post("../backend/application/index.php?rota=/setRefundSaleCheck", { data: refund }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.toRefundConference();
        });
      };

      $scope.loadPendencys = function () {
        $.post("../backend/application/index.php?rota=/loadPendencys", {}, function (result) {
          $scope.pendencys = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.orderDown = function (rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadOrders();
      };

      $scope.loadOrders = function () {
        $.post("../backend/application/index.php?rota=/loadSalesToOperations", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function (result) {
          $scope.sales = jQuery.parseJSON(result).dataset.sales;
          $scope.totalData = jQuery.parseJSON(result).dataset.total;
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };

      init = function () {
        $scope.checkValidRoute();
        if ($scope.main.id == '') {
          return;
        }
        $scope.tabindex = 0;
        $scope.showRefunds = false;
        $rootScope.modalOpen = true;
        $scope.filter = {};
        $scope.refundsChart = [];
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadAirport", $scope.session, function (result) {
          $scope.airports = jQuery.parseJSON(result).dataset;
        });
        $.post("../backend/application/index.php?rota=/loadAirline", $scope.session, function (result) {
          $scope.airlines = jQuery.parseJSON(result).dataset;
        });

        $scope.loadOrders();

        $.post("../backend/application/index.php?rota=/loadInternalCards", $scope.session, function (result) {
          $scope.internalCards = jQuery.parseJSON(result).dataset;
        });
        $scope.loadPendencys();
      };

      $scope.setSelectedRefund = function () {
        $scope.selected = angular.copy(this.refund);
        $('#salemilesused').number(true, 0, ',', '.');
        $('#saletax').number(true, 2, ',', '.');
        $('#saledutax').number(true, 2, ',', '.');
        $('#saletotalcost').number(true, 2, ',', '.');
        $('#saleamountpaid').number(true, 2, ',', '.');
        $('#saleextrafee').number(true, 2, ',', '.');
        $('#salekickback').number(true, 2, ',', '.');
        $('#milesMoney').number(true, 2, ',', '.');
        $('#totalCost').number(true, 2, ',', '.');
        $('#newMiles').number(true, 0, ',', '.');
        // $('#newValue').number( true, 2, ',', '.');
        $("#newValue").maskMoney({ thousands: '.', decimal: ',', precision: 2 });
        // $('#repricingCost').number( true, 2, ',', '.');
        $("#repricingCost").maskMoney({ thousands: '.', decimal: ',', precision: 2 });

        $.post("../backend/application/index.php?rota=/loadBilletFinancial", { data: $scope.selected }, function (result) {
          $scope.selected.billetStatus = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });

        $.post("../backend/application/index.php?rota=/loadLog", { data: $scope.selected }, function (result) {
          $scope.saleLog = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });

        $scope.airlineShowFields = ($scope.selected.airline == 'TAM' || $scope.selected.airline == 'LATAM');
        $scope.boardingDate = new Date($scope.selected.boardingDate);
        $scope.landingDate = new Date($scope.selected.landingDate);
        $scope.paxBirthdate = new Date($scope.selected.paxBirthdate + 'T12:00:00Z');
        if ($scope.selected.status == "Reembolso Solicitado" || $scope.selected.status == "Reembolso Pagante Solicitado" || $scope.selected.status == "Reembolso No-show Solicitado" || $scope.selected.status == "Reembolso CIA") {
          $.post("../backend/application/index.php?rota=/loadBillsPayRefund", { data: $scope.selected }, function (result) {
            $scope.selected.billsPayValue = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
        }

        $scope.tabindex = 4;
        return true;
      };

      $scope.backToConference = function () {
        $scope.selected = {};
        $scope.tabindex = 3;
      };

      $scope.setSelectedPendency = function (selected) {
        $rootScope.$emit('openSalePendencyModal', selected);
      };

      $scope.revertStatus = function () {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "app/modals/modal_confirmation.html",
          controller: 'ModalConfirmationCtrl',
          resolve: {
            header: function () {
              return 'Reverter Venda';
            }
          }
        });
        modalInstance.result.then((function (filterSales) {
          $.post("../backend/application/index.php?rota=/revertStatusSale", { data: $scope.selected }, function (result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadOrders();

          });
        }));
      };

      $scope.openRepricingWarning = function () {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "app/modals/card_data.html",
          controller: 'ModalCardDataCtrl',
          resolve: {
            cards: function () {
              return $scope.selected;
            }
          }
        });
      };

      $scope.openSearchModal = function () {
        var modalInstance;
        $scope.filter = {};
        modalInstance = $modal.open({
          templateUrl: "SaleRefoundModalDemoCtrl.html",
          controller: 'SaleRefoundModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function () {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function (filter) {
          if (filter != undefined) {
            if ($scope.main.isMaster || $scope.main.sale || $scope.main.changeMiles || $scope.main.internRefund || $scope.main.conference || $scope.main.wizarSaleEvent) {
              filter._boardingDateFrom = $rootScope.formatServerDate(filter.boardingDateFrom);
              filter._boardingDateTo = $rootScope.formatServerDate(filter.boardingDateTo);
              filter._saleDateFrom = $rootScope.formatServerDate(filter.saleDateFrom);
              filter._saleDateTo = $rootScope.formatServerDate(filter.saleDateTo);
              filter._refundDateFrom = $rootScope.formatServerDate(filter.refundDateFrom);
              filter._refundDateTo = $rootScope.formatServerDate(filter.refundDateTo);
              filter._returnDateFrom = $rootScope.formatServerDate(filter.returnDateFrom);
              filter._returnDateTo = $rootScope.formatServerDate(filter.returnDateTo);
            } else {
              let today = new Date();
              today.setDate(today.getDate() - 1);
              filter._saleDateFrom = $rootScope.formatServerDate(today);
              filter._saleDateTo = $rootScope.formatServerDate();
            }
          }

          $scope.filter = filter;
          $scope.loadOrders();
        }), function () {
          $log.info("Modal dismissed at: " + new Date());
        });
      };

      $scope.openSearchModalTwo = function () {
        var modalInstance;
        $scope.filterConference = {};
        modalInstance = $modal.open({
          templateUrl: "SaleRefoundConferenceModalDemoCtrl.html",
          controller: 'SaleRefoundModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function () {
              return $scope.filterConference;
            }
          }
        });
        modalInstance.result.then((function (filterConference) {
          if (filterConference != undefined) {
            if ($scope.main.isMaster || $scope.main.sale || $scope.main.changeMiles || $scope.main.internRefund || $scope.main.conference || $scope.main.wizarSaleEvent) {
              filterConference._boardingDateFrom = $rootScope.formatServerDate(filterConference.boardingDateFrom);
              filterConference._boardingDateTo = $rootScope.formatServerDate(filterConference.boardingDateTo);
              filterConference._saleDateFrom = $rootScope.formatServerDate(filterConference.saleDateFrom);
              filterConference._saleDateTo = $rootScope.formatServerDate(filterConference.saleDateTo);
              filterConference._refundDateFrom = $rootScope.formatServerDate(filterConference.refundDateFrom);
              filterConference._refundDateTo = $rootScope.formatServerDate(filterConference.refundDateTo);
              filterConference._returnDateFrom = $rootScope.formatServerDate(filterConference.returnDateFrom);
              filterConference._returnDateTo = $rootScope.formatServerDate(filterConference.returnDateTo);
            } else {
              filterConference._saleDateFrom = $rootScope.formatServerDate(new Date());
              filterConference._saleDateTo = $rootScope.formatServerDate();
            }
          }

          $scope.filterConference = filterConference;
          $scope.toRefundConference();
        }), function () {
        });
      };

      $scope.generateRepricing = function (repricing) {
        $scope.repricing._boardingDate = $rootScope.formatServerDateTime($scope.repricing.boardingDate);
        $scope.repricing._landingDate = $rootScope.formatServerDateTime($scope.repricing.landingDate);
        $.post("../backend/application/index.php?rota=/generateRepricing", { data: $scope.selected, repricing: $scope.repricing }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.toggleFormTable();
          $scope.loadOrders();
        });
      };

      $scope.openRepricingModal = function () {
        $.post("../backend/application/index.php?rota=/loadInternalCards", {}, function (result) {
          $scope.internalCards = jQuery.parseJSON(result).dataset;

          $scope.flight_selected = $scope.selected;
          $scope.decript = function (code) {
            var data = code.split('320AB');
            var finaly = '';
            for (var j = 0; data.length > j; j++) {
              finaly = finaly + (String.fromCharCode(data[j] / 320));
            }
            return finaly;
          };

          for (var i in $scope.internalCards) {
            $scope.internalCards[i].password = $scope.decript($scope.internalCards[i].password);
          }

          $scope.repricing.newValue = $('#newValue').maskMoney('unmasked')[0];
          $scope.repricing.repricingCost = $('#repricingCost').maskMoney('unmasked')[0];
          $scope.repricing.returnMiles = false;
          var cost = $scope.repricing.repricingCost + (($scope.repricing.milesused / 1000) * $scope.repricing.newValue);

          $scope.repricing.tax_card = '';
          $scope.repricing.value = parseFloat(cost);
          $scope.repricing.valueRefund = 0;

          $.post("../backend/application/index.php?rota=/loadCardProvider", { data: $scope.flight_selected }, function (result) {
            $scope.TaxCard = jQuery.parseJSON(result).dataset;
            
            if (($scope.TaxCard.length == 1) && ($scope.flight_selected.tax_card == undefined)) {
              $scope.repricing.tax_card = $scope.TaxCard[0].card_number;
              $scope.repricing.tax_password = $scope.decript($scope.TaxCard[0].password);
              $scope.repricing.tax_cardType = $scope.TaxCard[0].card_type;
              $scope.repricing.tax_dueDate = $scope.TaxCard[0].due_date;
              $scope.repricing.tax_providerName = $scope.TaxCard[0].provider_name;
              $scope.repricing.provider_registration = $scope.TaxCard[0].provider_registration;
              $scope.repricing.provider_adress = $scope.TaxCard[0].provider_adress;
              $scope.repricing.birthdate = $scope.TaxCard[0].birthdate;
            } else {
              $scope.TaxCard = {};
            }

            var modalInstance;
            if($scope.repricing.method){
              modalInstance = $modal.open({
                templateUrl: "RepricingCtrl.html",
                controller: 'RepricingInstanceCtrl',
                resolve: {
                  repricing: function () {
                    return $scope.repricing;
                  },
                  internalCards: function () {
                    return $scope.internalCards;
                  },
                  TaxCard: function () {
                    return $scope.TaxCard;
                  }
                }
              });
              modalInstance.result.then((function (repricing) {
                $scope.generateRepricing(repricing);

              }));
            }

          });
        });
      };

      return init();
    }
  ]).controller('SaleRefoundModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function ($scope, $rootScope, $modalInstance, filter) {
      /*Confirmado / Efetivado */
      $scope.saleStatus = [
        'Pendente',
        'Emitido',
        'Reembolso Solicitado',
        'Reembolso Pagante Solicitado',
        'Reembolso CIA',
        'Reembolso Confirmado',
        'Cancelamento Solicitado',
        'Cancelamento Efetivado',
        'Cancelamento Nao Solicitado',
        'Remarcação Solicitado',
        'Remarcação Confirmado',
        'Cancelamento Pendente',
        'Reembolso Pendente',
        'Reembolso No-show Solicitado',
        'Reembolso No-show Confirmado',
        'Reembolso Nao Solicitado',
        'Reembolso Perdido'
      ];

      $.post("../backend/application/index.php?rota=/loadProvider", { hashId: $scope.$parent.hashId }, function (result) {
        $scope.providers = jQuery.parseJSON(result).dataset;
      });
      $.post("../backend/application/index.php?rota=/loadClientsNames", {}, function (result) {
        $scope.clients = jQuery.parseJSON(result).dataset;
      });

      $scope.filter = filter;
      $scope.ok = function () {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };

      //Modal Conference
      $scope.filterConference = filter;
      $scope.okConference = function () {
        $modalInstance.close($scope.filterConference);
      };
      $scope.cancelConference = function () {
        $modalInstance.dismiss("cancel");
      };


    }
  ]).controller('RefundCtrl', [
    '$scope', '$rootScope', '$modal', '$log', 'logger', function ($scope, $rootScope, $modal, $log, logger) {

      $scope.generateRefund = function (Refund) {
        $.post("../backend/application/index.php?rota=/generateRefund", { hashId: $scope.$parent.hashId, refund: Refund, sale: $scope.$parent.selected }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.$parent.$parent.$parent.toggleFormTable();
          $scope.$parent.$parent.$parent.loadOrders();
        });
      };

      $scope.open = function () {

        $.post("../backend/application/index.php?rota=/loadInternalCards", $scope.session, function (result) {
          $scope.internalCards = jQuery.parseJSON(result).dataset;

          $scope.flight_selected = $scope.$parent.selected;
          $scope.decript = function (code) {
            var data = code.split('320AB');
            var finaly = '';
            for (var j = 0; data.length > j; j++) {
              finaly = finaly + (String.fromCharCode(data[j] / 320));
            }
            return finaly;
          };

          for (var i in $scope.internalCards) {
            $scope.internalCards[i].password = $scope.decript($scope.internalCards[i].password);
          }

          if ($scope.$parent.getMulta($scope.flight_selected)) {
            var valueRefund = 0;
            if ($scope.flight_selected.SaleProvider == 'Loja TAM M') {
              valueRefund = 178;
            } else if ($scope.flight_selected.SaleProvider == 'Loja TAM Contagem') {
              valueRefund = 175;
            } else if ($scope.flight_selected.SaleProvider == 'Loja TAM Ponta Grossa') {
              valueRefund = 190;
            }
            $scope.refund = { value: parseFloat(($scope.flight_selected.amountPaid - $scope.$parent.getMulta($scope.flight_selected)).toFixed(2)), valueRefund: valueRefund };
          } else {
            $scope.refund = { value: 0, valueRefund: 0 };
          }
          $.post("../backend/application/index.php?rota=/loadCardProvider", { hashId: $scope.session.hashId, data: $scope.flight_selected }, function (result) {
            $scope.TaxCard = jQuery.parseJSON(result).dataset;
            if (($scope.TaxCard.length == 1) && ($scope.flight_selected.tax_card == undefined)) {
              $scope.refund.tax_card = $scope.TaxCard[0].card_number;
              $scope.refund.tax_password = $scope.decript($scope.TaxCard[0].password);
              $scope.refund.tax_cardType = $scope.TaxCard[0].card_type;
              $scope.refund.tax_dueDate = $scope.TaxCard[0].due_date;
              $scope.refund.tax_providerName = $scope.TaxCard[0].provider_name;
              $scope.refund.provider_registration = $scope.TaxCard[0].provider_registration;
              $scope.refund.provider_adress = $scope.TaxCard[0].provider_adress;
              $scope.refund.birthdate = $scope.TaxCard[0].birthdate;
              
            }
            else {
              $scope.TaxCard = {};
            }            

            var modalInstance;
            modalInstance = $modal.open({
              templateUrl: "Refund.html",
              controller: 'RefundInstanceCtrl',
              resolve: {
                Refund: function () {
                  return $scope.refund;
                },
                internalCards: function () {
                  return $scope.internalCards;
                },
                TaxCard: function () {
                  return $scope.TaxCard;
                },
                selected: function () {
                  return $scope.flight_selected;
                }
              }
            });
            modalInstance.result.then((function (Refund) {
              $scope.generateRefund(Refund);
            }));

          });
        });
      };
    }
  ]).controller('RefundInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'Refund', 'internalCards', 'TaxCard', 'selected', function ($scope, $rootScope, $modalInstance, $filter, Refund, internalCards, TaxCard, selected) {
      $scope.refund = Refund;
      $scope.internalCards = internalCards;
      $scope.TaxCard = TaxCard;

      $scope.selected = selected;      
      
      $scope.findDate = function(date) {
        if(date != '' ) {
          return new Date(date + 'T12:00:00Z');
        } else {
          return '';
        }
      };

      $scope.saleMethods = [
        'JACK FOR',
        'Loja TAM Ponta Grossa',
        'Loja TAM Contagem',
        'Loja TAM M',
        'Rextur Advance',
        'Outros'
      ];

      $scope.getTextRefund = function () {
        var issueDate = new Date($scope.selected.issueDate);
        issueDate.setDate(issueDate.getDate() + 60);
        if (issueDate < new Date()) {
          return 'Reembolso invalido !!! Data posterior a XYZ dias';
        }
        if ($scope.selected.saleType == 'Cartao') {
          return 'Reembolso de venda cartão';
        }
      };


      $scope.search = function(){
        $scope.TaxCard = $filter('filter')($scope.internalCards, $scope.filter); 
        $scope.refund.tax_card = $scope.TaxCard[0].card_number;
        $scope.refund.tax_password = $scope.TaxCard[0].password;
        $scope.refund.tax_cardType = $scope.TaxCard[0].card_type;
        $scope.refund.tax_dueDate = $scope.findDate($scope.TaxCard[0].due_date);
        $scope.refund.tax_providerName = $scope.TaxCard[0].provider_name;
        $scope.refund.provider_registration = $scope.TaxCard[0].provider_registration;
        $scope.refund.provider_adress = $scope.TaxCard[0].provider_adress;
        $scope.refund.birthdate = $scope.findDate($scope.TaxCard[0].birthdate);
      };

      $scope.ok = function () {
        $modalInstance.close($scope.refund);
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('CancelCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function ($scope, $rootScope, $modal, $log) {
      $scope.open = function () {
        var modalInstance;
        $.post("../backend/application/index.php?rota=/loadCardsData", { sale: $scope.$parent.selected }, function (result) {
          $scope.cards = jQuery.parseJSON(result).dataset;

          $scope.decript = function (code) {
            var data = code.split('320AB');
            var finaly = '';
            for (var j = 0; data.length > j; j++) {
              finaly = finaly + (String.fromCharCode(data[j] / 320));
            }
            return finaly;
          };

          if ($scope.cards.recovery_password) {
            $scope.cards.recovery_password = $scope.decript($scope.cards.recovery_password);
          }
          if ($scope.cards.access_password) {
            $scope.cards.access_password = $scope.decript($scope.cards.access_password);
          }

          modalInstance = $modal.open({
            templateUrl: "Cancel.html",
            controller: 'CancelInstanceCtrl',
            resolve: {
              cards: function () {
                return $scope.cards;
              }
            }
          });

          modalInstance.result.then((function (selected) {
            $scope.$parent.$parent.$parent.selected.CancelCost = selected.CancelCost;
            $scope.$parent.$parent.$parent.selected.CancelReason = selected.CancelReason;
            $scope.$parent.$parent.$parent.selected.cancelDate = selected.cancelDate;
            $scope.$parent.$parent.$parent.selected.ourCost = selected.ourCost;
            $scope.$parent.$parent.$parent.cancelSale();
          }));
        });
      };
    }
  ]).controller('CancelInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'cards', function ($scope, $rootScope, $modalInstance, logger, cards) {
      $scope.cards = cards;
      $scope.selected = {
        cancelDate: new Date()
      };
      $scope.ok = function () {
        if(!$scope.selected.ourCost) {
          return logger.logError("Nosso Custo obrigatorio");
        }
        $modalInstance.close($scope.selected);
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('ShowCardCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function ($scope, $rootScope, $modal, $log) {

      $scope.open = function () {
        var modalInstance;
        $scope.selected = $scope.$parent.$parent.$parent.selected;
        $.post("../backend/application/index.php?rota=/loadCardsData", { hashId: $scope.session.hashId, sale: $scope.$parent.selected }, function (result) {
          $scope.cards = jQuery.parseJSON(result).dataset;

          $scope.decript = function (code) {
            var data = code.split('320AB');
            var finaly = '';
            for (var j = 0; data.length > j; j++) {
              finaly = finaly + (String.fromCharCode(data[j] / 320));
            }
            return finaly;
          };

          $scope.cards.recovery_password = $scope.decript($scope.cards.recovery_password);
          $scope.cards.access_password = $scope.decript($scope.cards.access_password);

          modalInstance = $modal.open({
            templateUrl: "Card.html",
            controller: 'ShowCardInstanceCtrl',
            resolve: {
              cards: function () {
                return $scope.cards;
              },
              selected: function () {
                return $scope.selected;
              }
            }
          });

          modalInstance.result.then((function (selected) {
          }));

        });
      };
    }
  ]).controller('ShowCardInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'cards', 'selected', function ($scope, $rootScope, $modalInstance, cards, selected) {
      $scope.cards = cards;
      $scope.selected = selected;

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('RepricingInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'repricing', 'internalCards', 'TaxCard', function ($scope, $rootScope, $modalInstance, $filter, repricing, internalCards, TaxCard) {
      $scope.repricing = repricing;
      $scope.internalCards = internalCards;
      $scope.TaxCard = TaxCard;
      $scope.saleMethods = [
        'JACK FOR',
        'Loja TAM Ponta Grossa',
        'Loja TAM Contagem',
        'Loja TAM M',
        'Rextur Advance',
        'Outros'
      ];
      $scope.safePossibilities = [
        { method: 'Faturado' },
        { method: 'Cartao' }
      ];

      $('#refundvalue').number(true, 0, ',', '.');
      $('#value').number(true, 0, ',', '.');

      $scope.search = function () {
        $scope.TaxCard = $filter('filter')($scope.internalCards, $scope.filter);
        $scope.repricing.tax_card = $scope.TaxCard[0].card_number;
        $scope.repricing.tax_password = $scope.TaxCard[0].password;
        $scope.repricing.tax_cardType = $scope.TaxCard[0].card_type;
        $scope.repricing.tax_dueDate =  new Date($scope.TaxCard[0].due_date + 'T12:00:00Z');
        $scope.repricing.tax_providerName = $scope.TaxCard[0].provider_name;
        $scope.repricing.provider_registration = $scope.TaxCard[0].provider_registration;
        $scope.repricing.provider_adress = $scope.TaxCard[0].provider_adress;
        $scope.repricing.birthdate =  new Date($scope.TaxCard[0].birthdate + 'T12:00:00Z');

      };

      $scope.ok = function () {
        $modalInstance.close($scope.repricing);
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).directive('morrisRefundChart', [
    function () {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function (scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 3000;
          if (attrs.lineColors === void 0 || attrs.lineColors === '') {
            colors = null;
          } else {
            colors = JSON.parse(attrs.lineColors);
          }
          options = {
            element: ele[0],
            data: scope.$parent.refundsChart,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function () {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.refundsChart;
          };
          finish = function () {
            if ((scope.$parent.refundsChart != undefined) && (scope.$parent.refundsChart.length > 0)) {
              options.data = scope.$parent.refundsChart;
              return new Morris.Line(options);
            } else {
              setTimeout(finish, updateInterval);
            }
          };
          return update();
        }
      };
    }
  ]).controller('SalePendencyModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', 'logger', function ($scope, $rootScope, $modal, $log, logger) {

      $scope.setSalePendencyStatus = function (sale) {
        $.post("../backend/application/index.php?rota=/setSalePendencyStatus", { data: sale }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.$parent.loadPendencys();
        });
      };

      $scope.open = function () {
        var modalInstance;
        $scope.selected = $scope.$parent.selected;
        modalInstance = $modal.open({
          templateUrl: "SalePendencyModal.html",
          controller: 'SalePendencyModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            selected: function () {
              return $scope.selected;
            }
          }
        });
        modalInstance.result.then((function (selected) {
          $scope.setSalePendencyStatus(selected);
        }), function () {
          $log.info("Modal dismissed at: " + new Date());
        });
      };
    }
  ]).controller('SalePendencyModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'selected', function ($scope, $rootScope, $modalInstance, selected) {
      $scope.selected = selected;

      $scope.ok = function () {
        $modalInstance.close($scope.selected);
      };
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('SalePendencyDescriptionModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', 'logger', function ($scope, $rootScope, $modal, $log, logger) {

      $scope.setSalePendencyStatus = function (sale) {
        $.post("../backend/application/index.php?rota=/setSalePendencyStatus", { data: sale }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.$parent.loadPendencys();
        });
      };

      $rootScope.modalOpen = false;
      $rootScope.$on('openSalePendencyModal', function (event, args) {
        if ($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function (args) {
        var modalInstance;
        if (args) {
          $scope.selected = args;
        }
        modalInstance = $modal.open({
          templateUrl: "SalePendencyModalDescription.html",
          controller: 'SalePendencyModalDescriptionInstanceCtrl',
          periods: $scope.$parent.periods,
          size: 'lg',
          resolve: {
            selected: function () {
              return $scope.selected;
            }
          }
        });
        modalInstance.result.then((function (selected) {
          $scope.setSalePendencyStatus(selected);
          $rootScope.modalOpen = false;
        }), function () {
          $log.info("Modal dismissed at: " + new Date());
          $rootScope.modalOpen = false;
        });
      };
    }
  ]).controller('SalePendencyModalDescriptionInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'selected', function ($scope, $rootScope, $modalInstance, selected) {
      $scope.selected = selected;

      $.post("../backend/application/index.php?rota=/loadSalePendencys", { data: $scope.selected }, function (result) {
        $scope.logsPendencys = jQuery.parseJSON(result).dataset;
        $scope.$digest();
      });

      $scope.findDate = function (date) {
        return new Date(date);
      };

      $scope.ok = function () {
        $modalInstance.close($scope.selected);
      };
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ])
  .controller("reembolsoModalInstanceCtrl", [
    "$scope",
    "$rootScope",
    "$modalInstance",
    function ($scope, $rootScope, $modalInstance) {
      $scope.ok = function() {
        $modalInstance.close();
      };
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    },
  ]);
})();
;
