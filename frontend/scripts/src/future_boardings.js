(function () {
  'use strict';
  angular.module('app.table').controller('FutureBoardingsCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var original;

      $scope.searchKeywords = '';
      $scope.filteredBoardings = [];
      $scope.row = '';

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageBoardings = $scope.filteredBoardings.slice(start, end);
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

      $scope.search = function() {
        $scope.filteredBoardings = $filter('filter')($scope.Boardings, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.saveSelected = function() {
        $.post("../backend/application/index.php?rota=/saveCheckInStatus", {hashId: $scope.session.hashId, data: $scope.Boardings}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredBoardings = $filter('orderBy')($scope.Boardings, rowName);
        return $scope.onOrderChange();
      };

      $scope.getClass = function(boardings) {
        if(boardings.occurrence != 'Ocorrencia') {
          return 'btn btn-danger smallBtn';
        } else {
          return 'btn btn-line-info smallBtn';
        }
      };

      $scope.print = function(){
        var doc = new jsPDF('l', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(20);
        doc.text(150, 30, 'Embarques Futuros');

        var columns = [
          {title: "Cliente", dataKey: "client"},
          {title: "CIA", dataKey: "airline"},
          {title: "Data Embarque", dataKey: "boardingDate"},
          {title: "Pax", dataKey: "paxName"},
          {title: "Voo", dataKey: "flight"},
          {title: "Check-In", dataKey: "checkinState"},
          {title: "Localizador", dataKey: "flightLocator"},
          {title: "Ticket", dataKey: "ticket_code"},
          {title: "De", dataKey: "from"},
          {title: "Para", dataKey: "to"}
        ];

        var rows = [];
        for (var i = 0; i < $scope.filteredBoardings.length; i++) {
          var checkin = '';
          if($scope.filteredBoardings[i].checkinState == "true"){
            checkin = "Realizado";
          }else{
            checkin = "Pendente";
          }
          rows.push({
            client: $scope.filteredBoardings[i].client,
            airline: $scope.filteredBoardings[i].airline,
            boardingDate: $filter('date')($scope.filteredBoardings[i].boardingDate,'dd/MM/yyyy hh:mm:ss'),
            paxName: $scope.filteredBoardings[i].paxName,
            flight: $scope.filteredBoardings[i].flight,
            checkinState: checkin,
            flightLocator: $scope.filteredBoardings[i].flightLocator,
            ticket_code: $scope.filteredBoardings[i].ticket_code,
            from: $scope.filteredBoardings[i].from,
            to: $scope.filteredBoardings[i].to
          });
        }

        doc.autoTable(columns, rows, {
          styles: {
            fontSize: 8
          },
          createdCell: function (cell, data) {
          }
        });
        doc.save('embarques_futuros.pdf');
      };

      $scope.loadFutureBoardings = function() {
        $.post("../backend/application/index.php?rota=/loadFutureBoardings", $scope.session, function(result) {
            $scope.Boardings = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.saveOccurrence = function(saleSelected) {
        $.post("../backend/application/index.php?rota=/saveSaleOccurrence", {hashId: $scope.session.hashId, data: saleSelected}, function(result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.markAll = function() {
        for(var i in $scope.Boardings) {
          $scope.Boardings[i].checkinState = true;
        }
      };

      $scope.removeAll = function() {
        for(var i in $scope.Boardings) {
          $scope.Boardings[i].checkinState = false;
        }
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageClients = [];
      init = function() {
        $scope.filter = {};
        $scope.isTable = true;
        $scope.checkValidRoute();
        cfpLoadingBar.start();
        $scope.loadFutureBoardings();
      };

      $scope.openModalLog = function() {
        $scope.saleSelected = this.boardings;
        $.post("../backend/application/index.php?rota=/loadLog", {hashId: $scope.session.hashId, data: $scope.saleSelected}, function(result){
          $scope.logDescriptions = jQuery.parseJSON(result).dataset;

          $scope.saleSelected._boardingDate = new Date($scope.saleSelected.boardingDate);
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "FutureBoardingsModalCtrl.html",
            controller: 'FutureBoardingsModalInstanceCtrl',
            periods: $scope.periods,
            size: 'lg',
            resolve: {
              saleSelected: function() {
                return $scope.saleSelected;
              },
              logDescriptions: function() {
                return $scope.logDescriptions;
              }
            }
          });
          modalInstance.result.then((function(saleSelected) {
            $scope.saveOccurrence(saleSelected);
          }));
        });
      };

      return init();
    }
  ]).controller('BoardingsModalDemoCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {

      $scope.loadFutureBoardings = function() {
        $.post("../backend/application/index.php?rota=/loadFutureBoardings", {hashId: $scope.session.hashId, data: $scope.$parent.filter}, function(result){
          $scope.$parent.Boardings = jQuery.parseJSON(result).dataset;
          $scope.$parent.search();
          $scope.$parent.$apply();
          return $scope.$parent.select($scope.$parent.currentPage);
        });
      };

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "Boardings.html",
          controller: 'BoardingsModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter != undefined) {
            filter._boardingDateFrom = $rootScope.formatServerDate(filter.boardingDateFrom);
            filter._boardingDateTo = $rootScope.formatServerDate(filter.boardingDateTo);
          }

          $scope.$parent.filter = filter;
          $scope.loadFutureBoardings();
        }));
      };
    }
  ]).controller('BoardingsModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {

      $scope.filter = filter;
      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('FutureBoardingsModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'saleSelected', 'logDescriptions', function($scope, $rootScope, $modalInstance, saleSelected, logDescriptions) {

      $scope.saleSelected = saleSelected;
      $scope.logDescriptions = logDescriptions;
      $scope.salesOccurrences = ['Remarcação', 'Reembolso', 'Check-In Fechado', 'Outros'];

      $scope.decript = function(code){
        var data = code.split('320AB');
        var finaly = '';
        for (var j = 0; data.length > j; j++) {
          finaly = finaly + (String.fromCharCode(data[j] / 320));
        }
        return finaly;
      };

      $scope.findDate = function(date) {
        return new Date(date);
      };

      $scope.ok = function() {
        $modalInstance.close($scope.saleSelected);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();

;