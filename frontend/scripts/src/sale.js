(function () {
  'use strict';
  angular.module('app.table').controller('SaleCtrl', [
    '$scope', '$rootScope', '$route', '$filter', 'cfpLoadingBar', 'logger', '$modal', '$element', function($scope, $rootScope, $route, $filter, cfpLoadingBar, logger, $modal, $element) {
      var init;
      $scope.saleStatus = [
        'Pendente',
        'Emitido',
        'Cancelamento Solicitado',
        'Cancelamento Efetivado',
        'Cancelamento Pendente',
        'Cancelamento Nao Solicitado',
        'Remarcação Solicitado',
        'Remarcação Confirmado',
        'Reembolso No-show Solicitado',
        'Reembolso No-show Confirmado',
        'Reembolso Nao Solicitado',
        'Reembolso Pendente',
        'Reembolso Solicitado',
        'Reembolso Pagante Solicitado',
        'Reembolso CIA',
        'Reembolso Confirmado',
        'Reembolso Perdido'
      ];
      $scope.parceiros = [
        'JACK FOR',
        'Loja TAM Ponta Grossa',
        'Loja TAM Contagem',
        'Loja TAM M',
        'CONFIANÇA',
        'Flytour',
        'TAP',
        'Rextur Advance',
        'CNT Consolidadora',
        'HAST Viagens',
        'XML Viagens',
        'Milhas Alpass',
        'Alfa Rondonia Milhas',
        'Outros'
      ];
      $scope.searchKeywords = '';
      $scope.saleMethods = [
        'JACK FOR',
        'Loja TAM Ponta Grossa',
        'Loja TAM Contagem',
        'Loja TAM M',
        'CONFIANÇA',
        'Flytour',
        'TAP',
        'Rextur Advance',
        'CNT Consolidadora',
        'HAST Viagens',
        'XML Viagens',
        'Milhas Alpass',
        'Alfa Rondonia Milhas',
        'Outros'
      ];
      $scope.searchKeywordsMiles = '';
      $scope.filteredSales = [];
      $scope.row = '';
      $scope.total_cost_calculated = 0;
      $scope.total_miles_calculated = 0;
      $scope.noValueDealers = [22091, 25112, 23950, 28341, 21185, 23386, 25113, 25375, 25376, 25377, 25378, 25608, 27132, 31517, 31518, 32317, 33040, 33041, 34400, 34597, 34600, 35228, 35267, 35493, 35688, 35770, 35785, 35880, 37362, 39489, 39492, 39991, 40269, 40432, 40649, 43400, 44985, 45895, 47526, 509517];

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageSales = $scope.filteredSales.slice(start, end);
      };

      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };

      $scope.getCommissions = function() {
        var total = 0;
        for(var i in $scope.filteredSales) {
          total += $scope.filteredSales[i].commission;
        }
        return total;
      };

      $scope.getAllCommissions = function() {
        var total = 0;
        for(var i in $scope.filteredSales) {
          total += $scope.filteredSales[i].comissao_vendas;
        }
        return total;
      };

      $scope.onNumPerPageChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.printSynthetic = function(){
        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(20);
        doc.text(150, 30, 'Vendas Realizadas - Sintetico');

        var columns = [
          {title: "Cliente", dataKey: "client"},
          {title: "Telefone", dataKey: "client_phone"},
          {title: "Email", dataKey: "client_email"},
          {title: "Quantidade", dataKey: "count"},
          {title: "Milhas", dataKey: "miles"},
          {title: "Valor Total", dataKey: "value"}
        ];
        
        var rows = [];
        
        $.post("../backend/application/index.php?rota=/loadClientsSales", {hashId: $scope.session.hashId, data: $scope.filter, filter: $scope.searchKeywords}, function(result){
          $scope.clientSales = jQuery.parseJSON(result).dataset;
          for (var i = 0; i < $scope.clientSales.length; i++) {
            rows.push({
              client: $scope.clientSales[i].client_name,
              client_phone: $scope.clientSales[i].client_phone,
              client_email: $scope.clientSales[i].client_email,
              count: $scope.clientSales[i].count,
              miles: $rootScope.formatNumber($scope.clientSales[i].miles, 0),
              value: $rootScope.formatNumber($scope.clientSales[i].total_cost)
            });
          }

          doc.autoTable(columns, rows, {
            styles: {
              overflow: 'linebreak',
              fontSize: 8
            },
            createdCell: function (cell, data) {
              if (data.column.dataKey === 'miles') {
                cell.styles.halign = 'right';
              }
              if (data.column.dataKey === 'value') {
                cell.styles.halign = 'right';
              }
            }
          });
          doc.save('Relatorio_vendas_Sintetico.pdf');
        });
      };

      $scope.printAnalytical = function(){
        var doc = new jsPDF('l', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(18);
        doc.text(150, 30, 'Vendas Realizadas - Analitico');

        var columns = [
          {title: "Data", dataKey: "issueDate"},
          {title: "Cliente", dataKey: "client_name"},
          {title: "Pax", dataKey: "pax"},
          {title: "Loc", dataKey: "flightLocator"},
          {title: "Trecho", dataKey: "fromTo"},
          {title: "Status", dataKey: "status"},
          {title: "Embarque", dataKey: "boardingDate"},
          {title: "Cartão", dataKey: "cardNumber"},
          {title: "Milhas Usadas", dataKey: "miles"},
          {title: "M+M", dataKey: "m_m"},
          {title: "Desc", dataKey: "discount"},
          {title: "Taxa", dataKey: "tax"},
          {title: "Val Final", dataKey: "value"},
          {title: "Emissor", dataKey: "issuer"},
        ];
        
        var rows = [];
        var total = 0;
        
        for (var i = 0; i < $scope.filteredSales.length; i++) {

          if($scope.filteredSales[i].providerName == ""){
            $scope.provider = $scope.filteredSales[i].sale_method;
          } else {
            $scope.provider = $scope.filteredSales[i].providerName;
          }

          total += $scope.filteredSales[i].amountPaid;

          rows.push({
            issueDate: $filter('date')(new Date($scope.filteredSales[i].issueDate), 'dd/MM/yyyy hh:mm:ss'),
            client_name: $scope.filteredSales[i].client,
            pax: $scope.filteredSales[i].paxName,
            flightLocator: $scope.filteredSales[i].flightLocator,
            fromTo: $scope.filteredSales[i].from + '-' + $scope.filteredSales[i].to + ' - ' + $scope.filteredSales[i].airline,
            status: $scope.filteredSales[i].status,
            boardingDate: $filter('date')(new Date($scope.filteredSales[i].boardingDate), 'dd/MM/yyyy hh:mm:ss'),
            cardNumber: $scope.filteredSales[i].providerName,
            miles: $rootScope.formatNumber($scope.filteredSales[i].milesused, 0),
            m_m: $rootScope.formatNumber($scope.filteredSales[i].miles_money),
            discount: $rootScope.formatNumber($scope.filteredSales[i].discount),
            tax: $rootScope.formatNumber($scope.filteredSales[i].tax),
            value: $rootScope.formatNumber($scope.filteredSales[i].amountPaid),
            issuer: $scope.filteredSales[i].user
          });

        }

        rows.push({
          value: $rootScope.formatNumber(total)
        });

        doc.autoTable(columns, rows, {
          styles: {
            fontSize: 6,
            overflow: 'linebreak',
            margin: {horizontal: 4},
            columnWidth: 'auto'
          },
          createdCell: function (cell, data) {
            if (data.column.dataKey === 'miles' || data.column.dataKey === 'value' || data.column.dataKey === 'tax' || data.column.dataKey === 'du_tax' || data.column.dataKey === 'm_m' || data.column.dataKey === 'discount') {
              cell.styles.halign = 'right';
            }
          }
        });
        doc.save('Relatorio_vendas_Analitico.pdf');
        $scope.filteredSales = $filter('filter')($scope.sales, $scope.searchKeywords);
      };

      $scope.backToSale = function() {
        $scope.tabindex = 0;
      };

      $scope.getTotalOrders = function() {
        return Object.keys(_.groupBy($scope.sales, 'externalId')).length
      };

      $scope.setSelected = function() {
        $scope.selected = {};
        if(this.sale.status != "Cancelado"){  
          $scope.selected = angular.copy(this.sale);

          var totalCost_float = $scope.selected.totalCost;
          $scope.selected.custoProd = "buscando...";
          $scope.selected.custoProd_display = true;

          $('#salemilesused').number( true, 0, ',', '.');
          $('#salemilesoriginal').number( true, 0, ',', '.');
          $('#saletotalcost').number( true, 2, ',', '.');
          // $('#saleamountpaid').number( true, 2, ',', '.');
          $scope.selected.amountPaid = $rootScope.formatNumber($scope.selected.amountPaid);
          $("#saleamountpaid").maskMoney({thousands:'.',decimal:',', precision: 2});
          $('#saleextrafee').number( true, 2, ',', '.');
          $('#salekickback').number( true, 2, ',', '.');
          $('#milesMoney').number( true, 2, ',', '.');
          $scope.selected.totalCost = $rootScope.formatNumber($scope.selected.totalCost);
          $('#totalCost').maskMoney({thousands:'.',decimal:',', precision: 2});
          $('#custoProd').maskMoney({thousands:'.',decimal:',', precision: 2});
          $('#taxOnlinePayment').maskMoney({thousands:'.',decimal:',', precision: 2});
          // $.post("../backend/application/index.php?rota=/loadProvider", $scope.session, function(result){
          //   $scope.providers = jQuery.parseJSON(result).dataset;
          // });
          $.post("../backend/application/index.php?rota=/loadLog", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            $scope.saleLog = jQuery.parseJSON(result).dataset;
            $scope.tabindex = 1;
            $scope.airlineShowFields = ($scope.selected.airline == 'TAM' || $scope.selected.airline == 'LATAM');
            $scope.selected._boardingDate = new Date($scope.selected.boardingDate);
            $scope.selected._landingDate = new Date($scope.selected.landingDate);
            if($scope.selected.paxBirthdate != '') {
              $scope.selected._paxBirthdate = new Date($scope.selected.paxBirthdate + 'T12:00:00Z');
            }
            $scope.$apply();
            return true;
          });
          $.post("../backend/application/index.php?rota=/loadBilletFinancial", { hashId: $scope.session.hashId, data: $scope.selected }, function(result){
            $scope.selected.billetStatus = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
          $.post("../backend/application/index.php?rota=/loadInternalCards", { hashId: $scope.session.hashId }, function(result){
            $scope.internalCards = jQuery.parseJSON(result).dataset;
          });
          //$.post("../backend/application/index.php?rota=/loadSalesMiles", { milesUsed: $scope.selected.milesused, airline: $scope.selected.airline, pax_quant: 1, paxes: [{ pax_name: $scope.selected.paxName, paxLastName: '', paxAgnome: '' }], from: $scope.selected.from, to: $scope.selected.to }, function(result){
          
          if($scope.selected.cards_id && $scope.selected.cards_id != ""){
            $.post("../backend/application/index.php?rota=/loadSalesMiles", { milesUsed: 0, airline: $scope.selected.airline}, function(result){ 
            //$.post("../backend/application/index.php?rota=/loadSalesMiles", { milesUsed: $scope.selected.milesused, airline: $scope.selected.airline, pax_quant: 1, paxes: [{ pax_name: $scope.selected.paxName, paxLastName: '', paxAgnome: '' }], from: $scope.selected.from, to: $scope.selected.to }, function(result){
              var flight_mile = $filter('filter')(jQuery.parseJSON(result).dataset, function(Row){
                return (Row.cards_id == $scope.selected.cards_id);
              });

              //console.log("result", jQuery.parseJSON(result).dataset);
              //console.log("flight_mile", flight_mile);
              //console.log("$scope.selected", $scope.selected);
              
              if(flight_mile.length > 0){
                flight_mile = flight_mile[0];
                $scope.selected.custoProd  = parseFloat($scope.selected.milesused/1000);
                $scope.selected.custoProd *= parseFloat(flight_mile.cost_per_thousand);
                $scope.selected.custoProd += $scope.selected.tax;
                $scope.selected.custoProd += $scope.selected.duTax;
                $scope.selected.custoProd += $scope.selected.baggage_price;
                $scope.selected.custoProd += $scope.selected.special_seat;
                $scope.selected.custoProd  = $rootScope.formatNumber($scope.selected.custoProd);
              }
              else{
                $scope.selected.custoProd = "";
                $scope.selected.custoProd_display = false;
              }
              $scope.loadSaleHistory()
              //$scope.$apply();
            });
          }

        }
        else{
          logger.logError("Esta venda foi cancelada!");
        }

        console.log($scope.selected);
      };

      $scope.setKickBack = function() {
        $scope.selected.kickback = ($scope.selected.amountPaid - $scope.selected.totalCost - $scope.selected.tax - $scope.selected.duTax - $scope.selected.extraFee);
      };

      $scope.toggleFormTable = function() {
        if ($scope.tabindex == 0) {
          return $scope.tabindex = 1;
        }
        return $scope.tabindex = 0;
      };

      $scope.search = function() {        
        $scope.filteredSales = $filter('filter')($scope.sales, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.search_miles = function() {
        $scope.filtered_flight_miles = $filter('filter')($scope.flight_miles, $scope.searchKeywordsMiles);
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredSales = $filter('orderBy')($scope.sales, rowName);
        return $scope.onOrderChange();
      };

      $scope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
        return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
      };

      $scope.saleTag = function(status) {
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

      $scope.openFindAllCardsCtrl = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "app/modals/find_all_cards.html",
          controller: 'FindAllCardsInstanceCtrl',
          resolve: {
            main: function () {
              return $scope.main;
            }
          }
        });
        modalInstance.result.then(function (permissions) {
          var milesUsed = -50000;
          if (!permissions.sales && !permissions.isMaster) {
            milesUsed = 1;
          }
          $scope.operation = 2;
          $.post("../backend/application/index.php?rota=/loadSalesMiles", { milesUsed: milesUsed, airline: $scope.selected.airline, pax_quant: 1, from: $scope.selected.from, to: $scope.selected.to }, function (result) {
            $scope.flight_miles = jQuery.parseJSON(result).dataset;
            $scope.search_miles();
            $scope.$digest();
          });
        });
      };

      $scope.getSaleStatus = function (sale) {
        var date = new Date(sale.issueDate);
        if(sale.saleChecked == true && (date.getMonth() == (new Date()).getMonth() && date.getFullYear() == (new Date()).getFullYear() && date.getDate() == (new Date()).getDate())) {
          return 'Venda Verificada';
        }
        return sale.status;
      };

      $scope.numPerPageOpt = [10, 25, 50, 150, 2000];
      $scope.numPerPage = $scope.numPerPageOpt[1];
      $scope.currentPage = 1;
      $scope.currentPageSales = [];
      $rootScope.hashId = $scope.session.hashId;

      $scope.saveOrder = function() {
        cfpLoadingBar.start();
        $scope.selected.amountPaid = $('#saleamountpaid').maskMoney('unmasked')[0];
        $scope.selected.totalCost = $('#totalCost').maskMoney('unmasked')[0];

        $scope.selected.boardingDate = $rootScope.formatServerDateTime($scope.selected._boardingDate);
        $scope.selected.landingDate = $rootScope.formatServerDateTime($scope.selected._landingDate);
        if ($scope.selected._paxBirthdate !== "" && $scope.selected._paxBirthdate != 'Invalid Date') {
          $scope.selected.paxBirthdate = $rootScope.formatServerDate($scope.selected._paxBirthdate);
        }

        $.post("../backend/application/index.php?rota=/saveSale", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $.post("../backend/application/index.php?rota=/loadOrder", { }, function(result){
            $scope.sales = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
            $scope.search();
            $scope.$apply();
            return $scope.select($scope.currentPage);
          });
        });
     
        $scope.toggleFormTable();
      };

      $scope.openFlightLocator = function() {
        $scope.selectedSale = $filter('filter')($scope.sales, 'true');
        $scope.tabindex = 2;
      };

      $scope.saveFlightLocator = function() {
        $.post("../backend/application/index.php?rota=/saveFlightLocator", {hashId: $scope.$parent.hashId, flightLocator: $scope.editsale.flightLocator, sales: $scope.selectedSale}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.tabindex = 0;
          $scope.$apply();
        });
      };

      $scope.addRow = function() {
        if (this.sale.status !== 'Pendente') {
          this.sale.checked = false;
        }
      };

      $scope.verifyFilter = function(){
        for(let i in $scope.filter.statuses){
          if($scope.filter.statuses[i] == true)
            return false;
        }
        return (
          (!$scope.filter.boardingDateFrom)        &&(!$scope.filter.boardingDateTo)&&
          (!$scope.filter.saleDateFrom)            &&(!$scope.filter.saleDateFrom)&&
          (!$scope.filter.providerName)            &&(!$scope.filter.flightLocator)&&
          (!$scope.filter.providerRegistrationCode)&&(!$scope.filter.client)&&
          (!$scope.filter.issuer)                  &&(!$scope.filter.user)&&
          (!$scope.filter.state)                   &&(!$scope.filter.cardNumber)&&
          (!$scope.filter.minMiles)                &&(!$scope.filter.maxMiles)&&
          (!$scope.filter.paxName)                 &&(!$scope.filter.airline)&&
          (!$scope.filter.dealer)                  &&(!$scope.filter.ticket_code)&&
          (!$scope.filter.externalid)
        );
      };

      $scope.loadSalesByFilter = function() {
        if(!$scope.verifyFilter()){
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadSaleByFilter", { data: $scope.filter, dealer: $scope.dealerKeywords }, function(result){
            $scope.sales = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
            $scope.search();
            $scope.$apply();
            return $scope.select($scope.currentPage);
          });
        }
        else{
          alert("Insira um valor em algum campo da caixa de pesquisa avançada e redefina sua busca.");
        }
      };

      $scope.findCard = function() {
        $.post("../backend/application/index.php?rota=/loadSalesMiles", { milesUsed: $scope.selected.milesused, airline: $scope.selected.airline, pax_quant: 1, paxes: [{ pax_name: $scope.selected.paxName, paxLastName: '', paxAgnome: '' }], from: $scope.selected.from, to: $scope.selected.to }, function(result){
          $scope.flight_miles = jQuery.parseJSON(result).dataset;
          $scope.search_miles();
          $scope.tabindex = 3;
          $scope.$apply();
        });
      };

      $scope.loadSaleHistory = function() {
        $.post("../backend/application/index.php?rota=/loadSaleHistory", { data:{'cards_id': $scope.selected.cards_id} }, function(result){
          let all_sale_purchases = jQuery.parseJSON(result).dataset;

          if(all_sale_purchases.sales)
            all_sale_purchases = all_sale_purchases.sales;
          for(let i in all_sale_purchases){
            if(all_sale_purchases[i].id == $scope.selected.id){
              $scope.sale_purchases = all_sale_purchases[i].sale_purchases;

              $scope.total_cost_calculated = 0;
              $scope.total_miles_calculated = 0;
              for(let j in $scope.sale_purchases){
                $scope.sale_purchases[j].cost_calculated = 
                  $scope.sale_purchases[j].miles_used / 1000 *
                  $scope.sale_purchases[j].cost_per_thousand;
                $scope.total_cost_calculated += $scope.sale_purchases[j].cost_calculated;
                $scope.total_miles_calculated += $scope.sale_purchases[j].miles_used;
              }

              console.log($scope.sale_purchases);
              break;
            }
          }
          $scope.$apply();
        });
      };

      $scope.setCard = function() {
        $scope.selected.providerName = this.mile.name;
        $scope.selected.cards_id = this.mile.cards_id;
        $scope.tabindex = 1;
      };

      $scope.findMiles = function() {
        var total = 0;
        if($scope.filteredSales) {
          if($scope.filteredSales.length > 0) {
            for(var i in $scope.filteredSales) {
              total += $scope.filteredSales[i].milesused;
            }
          }
        }
        return $rootScope.formatNumber(total, 0);
      };

      $scope.findMilesOriginal = function() {
        var total = 0;
        if($scope.filteredSales) {
          if($scope.filteredSales.length > 0) {
            for(var i in $scope.filteredSales) {
              total += $scope.filteredSales[i].milesOriginal;
            }
          }
        }
        return $rootScope.formatNumber(total, 0);
      };

      $scope.checkSale = function() {
        $.post("../backend/application/index.php?rota=/saveSaleCheck", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess('Salvo com sucesso');
          $.post("../backend/application/index.php?rota=/loadOrder", $scope.session, function(result){
            $scope.sales = jQuery.parseJSON(result).dataset;
          });
        });
      };

      $scope.saveAsDiamond = function(event) {
        $.post("../backend/application/index.php?rota=/sale/saveAsDiamond", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.response = jQuery.parseJSON(result).dataset;

          if($scope.response.type_message == 'success'){
            logger.logSuccess($scope.response.message);
          } else if($scope.response.type_message == 'error') {
            logger.logError($scope.response.message);
            $scope.selected.is_diamond = false;
          }
        });
      };

      $scope.printCompiled = function() {
          var doc = new jsPDF('p', 'pt');
          doc.margin = 0.5;
          doc.setFontSize(18);
          doc.text(150, 30, 'Vendas Realizadas');

          $scope.reportOrders = $filter('orderBy')($scope.sales, 'client');

          var columns = [
            {title: "Data", dataKey: "issueDate"},
            {title: "Cliente", dataKey: "client_name"},
            {title: "Pax", dataKey: "pax"},
            {title: "Cobrado", dataKey: "value"}
          ];
          
          var rows = [];
          var total = 0;
          var lastClient = '';
          for(var i in $scope.reportOrders) {
            if(lastClient != $scope.filteredSales[i].client) {
              rows.push({
                value: $rootScope.formatNumber(total)
              });
              total = 0;
              lastClient = $scope.filteredSales[i].client;
            }

            rows.push({
              issueDate: $filter('date')(new Date($scope.filteredSales[i].issueDate), 'dd/MM/yyyy hh:mm:ss'),
              client_name: $scope.filteredSales[i].client,
              pax: $scope.filteredSales[i].paxName,
              value: $rootScope.formatNumber($scope.filteredSales[i].amountPaid)
            });

            total += $scope.filteredSales[i].amountPaid;

          }

          doc.autoTable(columns, rows, {
            styles: {
              fontSize: 6,
              overflow: 'linebreak',
              margin: {horizontal: 4},
              columnWidth: 'auto'
            },
            createdCell: function (cell, data) {
              if (data.column.dataKey === 'value') {
                cell.styles.halign = 'right';
              }
            }
          });
          doc.save('Relatorio_vendas_Analitico.pdf');
      };

      $scope.loadDealerOrders = function() {
        if(!$scope.dealerKeywords._saleDateFrom) {
          var date = new Date();
          $scope.dealerKeywords._saleDateFrom = $rootScope.formatServerDate(new Date(date.getFullYear(), date.getMonth(), 1));
          $scope.dealerKeywords._saleDateFrom = $rootScope.formatServerDate(new Date());
        }
        $.post("../backend/application/index.php?rota=/loadDealerOrder", { hashId: $scope.session.hashId, data: $scope.dealerKeywords }, function(result){
            $scope.sales = jQuery.parseJSON(result).dataset;
            $scope.search();
            $scope.tabindex = 99;
            $scope.$apply();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.changeFilds = function() {
        $scope.filteredFilds = $filter('filter')($scope.filters, true);
      };

      $scope.orderDown = function(rowName) {
        rowName = '-' + rowName
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredSales = $filter('orderBy')($scope.sales, rowName);
        return $scope.onOrderChange();
      };

      $scope.getFild = function(sale, fild) {
        if(fild == 'from') {
          return sale[fild] + '-' + sale['to']
        }
        if(!sale[fild]) {
          return ''
        }
        if(new Date(sale[fild]) == 'Invalid Date' || typeof sale[fild] == "number" || sale[fild] == "" || sale[fild].length <= 9) {

          if(typeof sale[fild] == "number") {
            return $rootScope.formatNumber(sale[fild]);
          }

          return sale[fild];
        } else {
          return $filter('date')(new Date(sale[fild]),'dd/MM/yyyy');
        }
      };

      $scope.listOrders = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/listOrders", { data: $scope.filter, dealer: $scope.dealerKeywords }, function(result){
          cfpLoadingBar.complete();
          $scope.orders = jQuery.parseJSON(result).dataset.orders;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/orders_listing.html",
            controller: 'OrdersListingCtrl',
            periods: $scope.periods,
            size: 'lg',
            resolve: {
              orders: function() {
                return $scope.orders;
              }
            }
          });
        });
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.filter = {
          statuses: {}
        };
        if($scope.main.id == '') {
          return;
        }
        $scope.tabindex = 0;
        $scope.dealerKeywords = {};

        $scope.filters = [
          { name: 'client', label: 'Cliente', check: true },
          { name: 'issueDate', label: 'Data Emissão', check: true },
          { name: 'airline', label: 'CIA', check: true },
          { name: 'from', label: 'Trecho', check: true },
          { name: 'commission', label: 'Valor', check: true },
          { name: 'paxName', label: 'Passageiro', check: true },
          { name: 'boardingDate', label: 'Embarque', check: true },
          { name: 'flightLocator', label: 'Localizador', check: true },
          { name: 'status', label: 'Status', check: true },
          { name: 'refundDate', label: 'Reembolso', check: true },
          { name: 'externalId', label: 'Pedido', check: true },
          { name: 'origin', label: 'Origem', check: true },
          { name: 'dealer_b2c', label: 'Representante', check: true },
          { name: 'comissao_vendas', label: 'Comissao', check: true },
          { name: 'dealer_b2c_tipo_comissao', label: 'tipo comissao', check: true },
          { name: 'cupom', label: 'cupom', check: true },
        ];

        $scope.changeFilds();
        if($scope.main.dealer || $scope.main.client) {

          if($scope.noValueDealers.indexOf($scope.main.id) > -1) {
            $scope.filters = [
              { name: 'airline', label: 'CIA', check: true },
              { name: 'from', label: 'Trecho', check: true },
              { name: 'paxName', label: 'Passageiro', check: true },
              { name: 'client', label: 'Cliente', check: true },
              { name: 'issueDate', label: 'Data Emissão', check: true },
              { name: 'boardingDate', label: 'Embarque', check: true },
              { name: 'flightLocator', label: 'Localizador', check: true },
              { name: 'status', label: 'Status', check: true },
              { name: 'refundDate', label: 'Reembolso', check: true },
            ];
          }

          $scope.tabindex = 99;

          if($scope.main.id == 21141) {
            $scope.filters.unshift({ name: 'company_name', label: 'Razão Social', check: true })
          }

          $scope.changeFilds();
          var date = new Date();
          $scope.filter._saleDateFrom = $rootScope.formatServerDate(new Date(date.getFullYear(), date.getMonth(), 1));
          $scope.loadSalesByFilter();
        } else {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
            $scope.clients = jQuery.parseJSON(result).dataset;
          });
          $.post("../backend/application/index.php?rota=/loadAirport", $scope.session, function(result){
              $scope.airports = jQuery.parseJSON(result).dataset;
          });
          $.post("../backend/application/index.php?rota=/loadOrder", $scope.session, function(result){
              $scope.sales = jQuery.parseJSON(result).dataset;
              $scope.search();
              cfpLoadingBar.complete();
              return $scope.select($scope.currentPage);
          });
        }

        if($scope.main.isMaster || $scope.main.commercial) {
          $.post("../backend/application/index.php?rota=/loadDealers", { hashId: $scope.session.hashId }, function(result){
            $scope.dealers = jQuery.parseJSON(result).dataset;
          });
        } else {
          $.post("../backend/application/index.php?rota=/marketing/loadDealers", { }, function(result){
            $scope.dealers = jQuery.parseJSON(result).dataset.dealers;
            $scope.$digest();
          });
        }

      };
      return init();
    }
  ]).controller('SaleModalDemoCtrl', [
    '$scope', '$rootScope', '$modal', 'cfpLoadingBar', '$log', function($scope, $rootScope, $modal, cfpLoadingBar, $log) {

      $scope.verifyFilter = function(){
        for(let i in $scope.filter.statuses){
          if($scope.filter.statuses[i] == true)
            return false;
        }
        return (
          (!$scope.filter.boardingDateFrom)        &&(!$scope.filter.boardingDateTo)&&
          (!$scope.filter.saleDateFrom)            &&(!$scope.filter.saleDateFrom)&&
          (!$scope.filter.providerName)            &&(!$scope.filter.flightLocator)&&
          (!$scope.filter.providerRegistrationCode)&&(!$scope.filter.client)&&
          (!$scope.filter.issuer)                  &&(!$scope.filter.user)&&
          (!$scope.filter.state)                   &&(!$scope.filter.cardNumber)&&
          (!$scope.filter.minMiles)                &&(!$scope.filter.maxMiles)&&
          (!$scope.filter.paxName)                 &&(!$scope.filter.airline)&&
          (!$scope.filter.dealer)                  &&(!$scope.filter.ticket_code)&&
          (!$scope.filter.externalid)
        );
      };

      $scope.loadSalesByFilter = function() {
        if(!$scope.verifyFilter()){
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadSaleByFilter", { data: $scope.$parent.filter, dealer: $scope.$parent.dealerKeywords }, function(result){
            if(!$scope.$parent.dealerKeywords.name) {
              $scope.$parent.sales = jQuery.parseJSON(result).dataset;
              cfpLoadingBar.complete();
              $scope.$parent.search();
              $scope.$parent.$apply();
              return $scope.$parent.select($scope.$parent.currentPage);
            } else {
              $scope.$parent.$parent.sales = jQuery.parseJSON(result).dataset;
              cfpLoadingBar.complete();
              $scope.$parent.$parent.search();
              $scope.$parent.$parent.$apply();
              return $scope.$parent.$parent.select($scope.$parent.$parent.currentPage);
            }
          });
        }
        else{
          alert("Insira um valor em algum campo da caixa de pesquisa avançada e redefina sua busca.");
        }
      };

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "Sale.html",
          controller: 'SaleModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.$parent.filter;
            },
            main: function() {
              return $scope.main
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter != undefined) {
            filter._boardingDateFrom = $rootScope.formatServerDate(filter.boardingDateFrom);
            filter._boardingDateTo = $rootScope.formatServerDate(filter.boardingDateTo);
            filter._saleDateFrom = $rootScope.formatServerDate(filter.saleDateFrom);
            filter._saleDateTo = $rootScope.formatServerDate(filter.saleDateTo);
          }

          $scope.$parent.filter = filter;
          $scope.loadSalesByFilter();
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };
    }
  ]).controller('SaleModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', 'main', function($scope, $rootScope, $modalInstance, filter, main) {

      $.post("../backend/application/index.php?rota=/loadState", {}, function(result){
        $scope.states = jQuery.parseJSON(result).dataset;
      });

      $scope.main = main;

      $scope.saleStatus = [
        'Cancelamento Efetivado',
        'Cancelamento Nao Solicitado',
        'Cancelamento Solicitado',
        'Emitido',
        'Reembolso Confirmado',
        'Reembolso Nao Solicitado',
        'Reembolso No-show Confirmado',
        'Reembolso No-show Solicitado',
        'Reembolso Pendente',
        'Reembolso Solicitado',
        'Remarcação Confirmado',
        'Remarcação Solicitado',
        'Reembolso Perdido'
      ];
      $scope.filter = filter;

      $scope.searchProviders = function() {
        $.post("../backend/application/index.php?rota=/loadProvider", { searchKeywords: $scope.filter.providerName }, function(result){
            $scope.providers = jQuery.parseJSON(result).dataset.providers;
            $scope.$digest();
        });
      };

      $.post("../backend/application/index.php?rota=/loadClientsNames", { hashId: $scope.$parent.hashId }, function(result){
        $scope.clients = jQuery.parseJSON(result).dataset;
      });
      $.post("../backend/application/index.php?rota=/loadProfile", { hashId: $scope.$parent.hashId }, function(result){
        $scope.profiles = jQuery.parseJSON(result).dataset;
      });

      if(($scope.main.isMaster || $scope.main.commercial) && !$scope.main.dealer) {
        $.post("../backend/application/index.php?rota=/loadDealers", { hashId: $scope.$parent.hashId }, function(result){
          $scope.dealers = jQuery.parseJSON(result).dataset;
        });
      } else {
        $.post("../backend/application/index.php?rota=/marketing/loadDealers", { }, function(result){
          $scope.dealers = jQuery.parseJSON(result).dataset.dealers;
          $scope.$digest();
        });
      }

      $scope.findIssuers = function() {
        $.post("../backend/application/index.php?rota=/loadIssuers", {hashId: $scope.$parent.hashId, data: $scope.filter}, function(result){
          $scope.issuers = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('ReplicateSaleCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {

      $scope.replicateSale = function(replicate) {
        $.post("../backend/application/index.php?rota=/replicateSale", { data: $scope.$parent.selected, replicate: replicate }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "ReplicateSaleModal.html",
          controller: 'ReplicateSaleInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
          }
        });
        modalInstance.result.then((function(replicate) {
          $scope.replicateSale(replicate);
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };
    }
  ]).controller('ReplicateSaleInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', function($scope, $rootScope, $modalInstance) {
      $scope.replicate = {};

      $.post("../backend/application/index.php?rota=/loadAirline", {}, function(result){
          $scope.airlines = jQuery.parseJSON(result).dataset;
      });

      $scope.searchProviders = function() {
        $.post("../backend/application/index.php?rota=/loadProvider", {searchKeywords: $scope.replicate.providerName}, function(result){
            $scope.providers = jQuery.parseJSON(result).dataset.providers;
            $scope.$digest();
        });
      };

      $.post("../backend/application/index.php?rota=/loadClientsNames", {}, function(result){
        $scope.clients = jQuery.parseJSON(result).dataset;
      });

      $scope.ok = function() {
        $modalInstance.close($scope.replicate);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('SaleModalDealerCtrl', [
    '$scope', '$rootScope', '$modal', 'cfpLoadingBar', '$log', function($scope, $rootScope, $modal, cfpLoadingBar, $log) {

      $scope.verifyFilter = function(){
        for(let i in $scope.filter.statuses){
          if($scope.filter.statuses[i] == true)
            return false;
        }
        return (
          (!$scope.filter.boardingDateFrom)        &&(!$scope.filter.boardingDateTo)&&
          (!$scope.filter.saleDateFrom)            &&(!$scope.filter.saleDateFrom)&&
          (!$scope.filter.providerName)            &&(!$scope.filter.flightLocator)&&
          (!$scope.filter.providerRegistrationCode)&&(!$scope.filter.client)&&
          (!$scope.filter.issuer)                  &&(!$scope.filter.user)&&
          (!$scope.filter.state)                   &&(!$scope.filter.cardNumber)&&
          (!$scope.filter.minMiles)                &&(!$scope.filter.maxMiles)&&
          (!$scope.filter.paxName)                 &&(!$scope.filter.airline)&&
          (!$scope.filter.dealer)                  &&(!$scope.filter.ticket_code)&&
          (!$scope.filter.externalid)
        );
      };

      $scope.loadSalesByFilter = function() {
        if(!$scope.verifyFilter()){
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadSaleByFilter", { data: $scope.$parent.filter, dealer: $scope.$parent.dealerKeywords }, function(result){
            cfpLoadingBar.complete();
            console.log($scope.$parent);
            $scope.$parent.$parent.sales = jQuery.parseJSON(result).dataset;
            $scope.$parent.$parent.search();
            $scope.$parent.$parent.$apply();
            return $scope.$parent.$parent.select($scope.$parent.$parent.currentPage);
          });
        }
        else{
          alert("Insira um valor em algum campo da caixa de pesquisa avançada e redefina sua busca.");
        }
      };

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "Sale.html",
          controller: 'SaleModalDealerInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            },
            main: function() {
              return $scope.main
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter != undefined) {
            filter._boardingDateFrom = $rootScope.formatServerDate(filter.boardingDateFrom);
            filter._boardingDateTo = $rootScope.formatServerDate(filter.boardingDateTo);
            filter._saleDateFrom = $rootScope.formatServerDate(filter.saleDateFrom);
            filter._saleDateTo = $rootScope.formatServerDate(filter.saleDateTo);
          }

          $scope.$parent.filter = filter;
          $scope.loadSalesByFilter();
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };
    }
  ]).controller('SaleModalDealerInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', 'main', function($scope, $rootScope, $modalInstance, filter, main) {

      $.post("../backend/application/index.php?rota=/loadState", {}, function(result){
        $scope.states = jQuery.parseJSON(result).dataset;
      });

      $scope.main = main;

      $scope.saleStatus = [
        'Pendente',
        'Emitido',
        'Reembolso Solicitado',
        'Reembolso Pagante Solicitado',
        'Reembolso CIA',
        'Reembolso Confirmado',
        'Cancelamento Solicitado',
        'Cancelamento Efetivado',
        'Remarcação Solicitado',
        'Remarcação Confirmado',
        'Cancelamento Pendente',
        'Cancelamento Nao Solicitado',
        'Reembolso Pendente',
        'Reembolso No-show Solicitado',
        'Reembolso No-show Confirmado',
        'Reembolso Nao Solicitado',
        'Reembolso Perdido'
      ];
      $scope.filter = filter;

      $scope.searchProviders = function() {
        $.post("../backend/application/index.php?rota=/loadProvider", { searchKeywords: $scope.filter.providerName }, function(result){
            $scope.providers = jQuery.parseJSON(result).dataset.providers;
            $scope.$digest();
        });
      };

      $.post("../backend/application/index.php?rota=/loadClientsNames", { hashId: $scope.$parent.hashId }, function(result){
        $scope.clients = jQuery.parseJSON(result).dataset;
      });
      $.post("../backend/application/index.php?rota=/loadProfile", { hashId: $scope.$parent.hashId }, function(result){
        $scope.profiles = jQuery.parseJSON(result).dataset;
      });

      if($scope.main.isMaster || $scope.main.commercial) {
        $.post("../backend/application/index.php?rota=/loadDealers", { hashId: $scope.$parent.hashId }, function(result){
          $scope.dealers = jQuery.parseJSON(result).dataset;
        });
      }

      $scope.findIssuers = function() {
        $.post("../backend/application/index.php?rota=/loadIssuers", {hashId: $scope.$parent.hashId, data: $scope.filter}, function(result){
          $scope.issuers = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('OrdersListingCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'orders', function($scope, $rootScope, $modalInstance, orders) {
      $scope.orders = orders;
      $scope.selected = {};
      $scope.setSelectedOrder = function(order) {
        $scope.selected = order;
      };

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;
