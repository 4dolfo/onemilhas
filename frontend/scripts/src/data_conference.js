(function () {
  'use strict';
  angular.module('app.table').controller('DataConferenceCtrl', [
    '$scope', '$rootScope', '$route', '$filter', 'cfpLoadingBar', 'logger', '$modal', '$element', function($scope, $rootScope, $route, $filter, cfpLoadingBar, logger, $modal, $element) {
      var init;

      $scope.saleStatus = ['Pendente','Emitido','Reembolso Solicitado','Reembolso Confirmado','Cancelamento Solicitado', 'Cancelamento Efetivado'];
      $scope.searchKeywords = '';
      $scope.filteredSales = [];
      $scope.row = '';

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

      $scope.onNumPerPageChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.findDate = function(date){
        return new Date(date);
      };

      $scope.setSelected = function() {
        $scope.selected = {};
        if(this.sale.status != "Cancelado"){
          $scope.selected = this.sale;
          $('#salemilesused').number( true, 0, ',', '.');
          $('#saletax').number( true, 2, ',', '.');
          $('#saledutax').number( true, 2, ',', '.');
          $('#saletotalcost').number( true, 2, ',', '.');
          // $('#saleamountpaid').number( true, 2, ',', '.');
          $("#saleamountpaid").maskMoney({thousands:'.',decimal:',', precision: 2});
          $('#saleextrafee').number( true, 2, ',', '.');
          $('#salekickback').number( true, 2, ',', '.');
          $('#milesMoney').number( true, 2, ',', '.');
          $('#totalCost').number( true, 2, ',', '.');
          // $.post("../backend/application/index.php?rota=/loadProvider", $scope.session, function(result){
          //   $scope.providers = jQuery.parseJSON(result).dataset.providers;
          // });
          $.post("../backend/application/index.php?rota=/loadLog", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            $scope.saleLog = jQuery.parseJSON(result).dataset;
            $scope.tabindex = 1;
            $scope.airlineShowFields = ($scope.selected.airline == 'TAM');
            $scope.boardingDate = new Date($scope.selected.boardingDate);
            $scope.landingDate = new Date($scope.selected.landingDate);
            $scope.paxBirthdate = new Date($scope.selected.paxBirthdate + 'T12:00:00Z');
            $scope.$apply();
            return true;
          });
        }
        else{
          logger.logError("Esta venda foi cancelada!");
        }
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

      $scope.findAmountPaid = function() {
        var total = 0;
        if($scope.filteredSales) {
          if($scope.filteredSales.length > 0) {
            for(var i in $scope.filteredSales) {
              total += $scope.filteredSales[i].amountPaid;
            }
          }
        }
        return $rootScope.formatNumber(total);
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
          case 'Reembolso Solicitado':
            return "label label-warning";
          case 'Reembolso Confirmado':
            return "label label-default";
          case 'Cancelamento Solicitado':
            return "label label-warning";
          case 'Cancelamento Efetivado':
            return "label label-danger";
        }
      };

      $scope.numPerPageOpt = [10, 25, 50, 150];
      $scope.numPerPage = $scope.numPerPageOpt[1];
      $scope.currentPage = 1;
      $scope.currentPageSales = [];
      $rootScope.hashId = $scope.session.hashId;

      $scope.saveOrder = function() {
        cfpLoadingBar.start();
        $scope.selected.amountPaid = $('#saleamountpaid').maskMoney('unmasked')[0];
        $.post("../backend/application/index.php?rota=/saveOrder", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $.post("../backend/application/index.php?rota=/loadSaleByFilter", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
            $scope.sales = jQuery.parseJSON(result).dataset;
            $scope.search();
            $scope.$apply();
            return $scope.select($scope.currentPage);
          });
        });
     
        cfpLoadingBar.complete();
        $scope.toggleFormTable();
      };

      $scope.saveFlightLocator = function() {
        $.post("../backend/application/index.php?rota=/saveFlightLocator", {hashId: $scope.$parent.hashId, flightLocator: $scope.editsale.flightLocator, sales: $scope.selectedSale}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.tabindex = 0;
          $scope.$apply();
        });
      };

      $scope.loadSalesByFilter = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadSaleByFilter", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.sales = jQuery.parseJSON(result).dataset;
          cfpLoadingBar.complete();
          $scope.search();
          $scope.$apply();
          return $scope.select($scope.currentPage);
        });
      };

      $scope.findCard = function() {
        $.post("../backend/application/index.php?rota=/loadSalesMiles", {hashId: $scope.session.hashId, milesUsed: $scope.selected.milesused, airline: $scope.selected.airline}, function(result){
          $scope.flight_miles = jQuery.parseJSON(result).dataset;
          $scope.tabindex = 3;
          $scope.$apply();
        });
      };

      $scope.setCard = function() {
        $scope.selected.providerName = this.mile.name;
        $scope.selected.cards_id = this.mile.cards_id;
        $scope.tabindex = 1;
      };

      $scope.validateData = function() {
        $scope.divergence = [];
        var check = false;

        $.post("../backend/application/index.php?rota=/loadDataConference", {hashId: $scope.session.hashId}, function(result){
          $scope.dataConference = jQuery.parseJSON(result).dataset;

          for(var i in $scope.dataConference) {

            $scope.filtered = $filter('filter')($scope.sales, $scope.dataConference[i].loc);
            $scope.filtered = $filter('filter')($scope.filtered, $scope.dataConference[i].paxName);
            $scope.filtered = $filter('filter')($scope.filtered, $scope.dataConference[i].valorPago);
            $scope.filtered = $filter('filter')($scope.filtered, $scope.dataConference[i].amountPaid);
            $scope.filtered = $filter('filter')($scope.filtered, $scope.dataConference[i].dU);
            check = false;
            if($scope.filtered.length > 0) {
              check = true;
            }
            // for(var j in $scope.filtered) {
            //   if($scope.filtered[j].flightLocator == $scope.dataConference[i].loc && $scope.filtered[j].paxName == $scope.dataConference[i].pax && $scope.filtered[j].valorPago == $scope.dataConference[i].amountPaid) {
            //     check = true;
            //   }
            // }
            if(!check) {
              $scope.divergence.push($scope.dataConference[i]);
            }
          }
          $rootScope.$emit('openDataConference', $scope.divergence);
          console.log($scope.divergence);
        });
      };

      $scope.dataConferenceSales = function() {
        $.post("../backend/application/index.php?rota=/dataConferenceSales", {hashId: $scope.session.hashId}, function(result){
          $scope.dataConference = jQuery.parseJSON(result).dataset;
          console.log($scope.dataConference);
        });
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;

        $scope.from = true;
        $scope.to = true;
        $scope.paxName = true;
        $scope.issuing = true;
        $scope.providerName = true;
        $scope.user = true;

        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadClient", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset.clients;
        });
        $.post("../backend/application/index.php?rota=/loadAirport", $scope.session, function(result){
            $scope.airports = jQuery.parseJSON(result).dataset;
        });
      };
      return init();
    }
  ]).controller('DataConferenceModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', 'logger', function($scope, $rootScope, $modal, $log, logger) {

      $rootScope.$on('openDataConference', function(event, args) {
        $scope.open(args);
      });

      $scope.open = function(args) {
        $scope.divergences = args;
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "DataConferenceModalCtrl.html",
          controller: 'DataConferenceInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            divergences: function() {
              return $scope.divergences;
            }
          }
        });
        modalInstance.result.then((function(billing) {
          $scope.saveBilling(billing);
        }));

      };
    }
  ]).controller('DataConferenceInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'divergences', function($scope, $rootScope, $modalInstance, divergences) {
      $scope.divergences = divergences;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;
