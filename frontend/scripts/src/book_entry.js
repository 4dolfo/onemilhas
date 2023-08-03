(function () {
  'use strict';
  angular.module('app.cashFlow', ['ngTouch', 'ui.grid', 'ui.grid.edit', 'ui.grid.pinning']).controller('BookEntryCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', '$timeout', '$interval', function($scope, $rootScope, $filter, cfpLoadingBar, logger, $timeout, $interval) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.filteredBookEntries = [];
      $scope.row = '';
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageBookEntry = $scope.filteredBookEntries.slice(start, end);
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

      $scope.search = function() {
        $scope.filteredBookEntries = $filter('filter')($scope.bookEntries, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        var original = angular.copy(this.bookEntry);
        $scope.bookEntry = original;
        $scope.bookEntry.value = $rootScope.formatNumber($scope.bookEntry.value);
        $("#amount").maskMoney({thousands:'.',decimal:',', precision: 2});
        $scope.tabindex = 1;
        return this.bookEntry;
      };

      $scope.newBookEntry = function() {
        $scope.bookEntry = {};
        $("#amount").maskMoney({thousands:'.',decimal:',', precision: 2});
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredBookEntries = $filter('orderBy')($scope.bookEntries, rowName);
        return $scope.onOrderChange();
      };

      $scope.getType = function(type){
        if(type == "C") return "Custo";
        if(type == "R") return "Receita";
      };

      $scope.getTypeColorClass = function(type){
        if(type == "C") return "warning";
        if(type == "R") return "success";
      };

      $scope.saveBookEntry = function() {
        if ($scope.form_book_entry.$valid) {

          $scope.bookEntry.value = $('#amount').maskMoney('unmasked')[0];
          $scope.bookEntry._date = $rootScope.formatServerDate($scope.bookEntry.date);
          $.post("../backend/application/index.php?rota=/saveBookEntry", {hashId: $scope.session.hashId, data: $scope.bookEntry}, function(result){
            $scope.tabindex = 0;
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadBookEntry();
          });
        }
      };

      $scope.sumFilteredEntrys = function() {
        var total = 0;
        if($scope.filteredBookEntries.length > 0) {
          for(var i in $scope.filteredBookEntries) {
            if($scope.filteredBookEntries[i].cost_center_type == 'C') {
              total -= $scope.filteredBookEntries[i].value;
            } else {
              total += $scope.filteredBookEntries[i].value;
            }
          }
        }
        return total;
      };

      $scope.cancelEdit = function() {
        $scope.tabindex = 0;
      };

      $scope.loadBookEntry = function() {
        $.post("../backend/application/index.php?rota=/loadBookEntryCurrentMonth", { hashId: $scope.session.hashId, data: $scope.filter }, function(result){
            $scope.currentMonth = jQuery.parseJSON(result).dataset;
        });
        $.post("../backend/application/index.php?rota=/loadBookEntry", { hashId: $scope.session.hashId, data: $scope.filter }, function(result){
            $scope.bookEntries = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.loadCostCenter = function() {
        $.post("../backend/application/index.php?rota=/loadCostCenter", { hashId: $scope.session.hashId, data: $scope.filter }, function(result){
            $scope.valueDescriptions = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.toTimeLine = function() {
        $scope.tabindex = 2;
        $.post("../backend/application/index.php?rota=/loadTimeLine", { hashId: $scope.session.hashId, data: $scope.filter }, function(result){
            $scope.timeLine = jQuery.parseJSON(result).dataset;
            $scope.$digest();
        });
      };

      $scope.toCharts = function() {
        $scope.tabindex = 4;
        $.post("../backend/application/index.php?rota=/loadBookEntryMonth", $scope.session, function(result){
          $scope.bookEntryAnalysis = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.toGrid = function() {
        $scope.tabindex = 3;

        if($scope.filterGrid.dateFrom) {
          $scope.filterGrid._dateFrom = $rootScope.formatServerDate($scope.filterGrid.dateFrom);

          $scope.filterGrid.dateTo = $scope.filterGrid.dateFrom;
          $scope.filterGrid.dateTo.setMonth($scope.filterGrid.dateTo.getMonth() + 1);
          $scope.filterGrid.dateTo.setDate($scope.filterGrid.dateTo.getDate() - 1);
          $scope.filterGrid._dateTo = $rootScope.formatServerDate($scope.filterGrid.dateTo);
        }

        $.post("../backend/application/index.php?rota=/loadGridMonth", { hashId: $scope.session.hashId, data: $scope.filterGrid }, function(result){
            var returnData = jQuery.parseJSON(result).dataset;
            console.log('returnData',returnData);

            $scope.gridOptions.columnDefs = returnData.coluns;
            $scope.gridOptions.data = returnData.data;
            $scope.$digest();
        });
      };

      $scope.saveGridValue = function( data ) {
        $.post("../backend/application/index.php?rota=/saveGridValue", { hashId: $scope.session.hashId, data: data }, function(result){
          if (jQuery.parseJSON(result).message.type == 'S'){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.toGrid();
          } else {
            logger.logError(jQuery.parseJSON(result).message.text);
          }
        });
      };

      $scope.cellClicked = function (row, col){
        var data = {
          entity: row.entity,
          date: col.field
        }
        $rootScope.$emit('openEditBookEntryValueModal', data);
      };

      $scope.gridOptions = {};
      $scope.gridOptions.columnDefs = [];
      $scope.gridOptions.rowHeight = 40;
      $scope.gridOptions.enableFiltering = true;

      // grid functions
      $scope.gridOptions.onRegisterApi = function(gridApi){

        //set gridApi on scope
        $scope.gridApi = gridApi;
        gridApi.edit.on.afterCellEdit($scope,function( rowEntity, colDef, newValue, oldValue ){
          // $scope.saveGridValue(rowEntity, colDef, newValue, oldValue);
          // console.log(rowEntity);
          // console.log(colDef);
          // console.log(newValue);
          // console.log(oldValue);
        });

        $interval( function() {
          $scope.gridApi.core.handleWindowResize();
        }, 200);

      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageBookEntry = [];
      
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.filterGrid = {};
        $scope.filter = {};
        $rootScope.modalOpen = false;
        cfpLoadingBar.start();
        $scope.bookEntryAnalysis = [];

        $scope.loadBookEntry();
        $scope.loadCostCenter();
      };
      return init();
    }
  ]).controller('BookEntryModalCtrl', ['$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {
    $scope.open = function() {
      $scope.filter = $scope.$parent.filter;
      var modalInstance;
      modalInstance = $modal.open({
        templateUrl: "BookEntryModalCtrl.html",
        controller: 'BookEntryModalInstanceCtrl',
        periods: $scope.$parent.periods,
        resolve: {
          filter: function() {
            return $scope.filter;
          }
        }
      });
      modalInstance.result.then((function(filter) {
        if (filter !== undefined) {
          filter._dateFrom = $rootScope.formatServerDate(filter.dateFrom);
          filter._dateTo = $rootScope.formatServerDate(filter.dateTo);
        }
        $scope.$parent.filter = filter;
        $scope.$parent.loadBookEntry();
      }));
    };
  }
  ]).controller('BookEntryModalInstanceCtrl', ['$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {
    $scope.filter = filter;

    $scope.ok = function() {
      $modalInstance.close($scope.filter);
    };
    $scope.cancel = function() {
      $modalInstance.dismiss("cancel");
    };
  }
  ]).controller('EditBookEntryValueModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $rootScope.modalOpen = false;

      $rootScope.$on('openEditBookEntryValueModal', function(event, args) {
        event.stopPropagation();
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function(args) {

        $scope.selected = {};
        $scope.selected.date = args.date;
        $scope.selected.value = args.entity[args.date];
        $scope.selected.type = args.entity['FLUXO_DE_CAIXA'];

        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "EditBookEntryValueModal.html",
          controller: 'EditBookEntryValueInstanceCtrl',
          resolve: {
            selected: function() {
              return $scope.selected;
            }
          }
        });
        modalInstance.result.then(function(resolve) {
          $scope.$parent.saveGridValue(resolve);
          $rootScope.modalOpen = false;
        }, function() {
          $rootScope.modalOpen = false;
        });
      };

    }
  ]).controller('EditBookEntryValueInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'selected', function($scope, $rootScope, $modalInstance, logger, selected) {
      $scope.selected = selected;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.save = function() {
        $modalInstance.close($scope.selected);
      };
    }
  ]).directive('morrisBookEntryAnalysis', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 2000;
          if (attrs.lineColors === void 0 || attrs.lineColors === '') {
          colors = null;
          } else {
          colors = JSON.parse(attrs.lineColors);
          }
          options = {
          element: ele[0],
          data: scope.$parent.bookEntryAnalysis,
          xkey: attrs.xkey,
          ykeys: JSON.parse(attrs.ykeys),
          labels: JSON.parse(attrs.labels),
          lineWidth: attrs.lineWidth || 2,
          lineColors: ["#565252","#4CAD29","#DD2E2E"],
          resize: true
          };
          update = function() {
          setTimeout(finish, updateInterval);
          options.data = scope.$parent.bookEntryAnalysis;
          };
          finish = function(){
          options.data = scope.$parent.bookEntryAnalysis;
          if(scope.$parent.bookEntryAnalysis.length > 0) {
            return new Morris.Bar(options);
          } else {
            setTimeout(finish, updateInterval);
          }
          };
          return update();
        }
      };
  }]);
})();
;