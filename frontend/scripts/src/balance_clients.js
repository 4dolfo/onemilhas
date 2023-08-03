(function () {
  'use strict';
  angular.module('app.sale').controller('BalanceClientsCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      $scope.searchKeywords = '';
      $scope.filter = {
        days: 7,
        client: ''
      };

      $scope.search = function() {
        $.post("../backend/application/index.php?rota=/loadClientsBalance", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.clientChart = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadAirlineBalance", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.airlineChart = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadAirlineMiles", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.airlineMiles = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadClientsTotal", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.clientsTotal = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadClientsCancelSales", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.clientsCancels = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadClientsStates", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.clientsState = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadCountClientesPerStates", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.clientsCountState = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadTopParts", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.topParts = jQuery.parseJSON(result).dataset;
          $scope.airportsFrom = $scope.topParts.from;
          $scope.airportsTo = $scope.topParts.to;
          $scope.airports = $scope.topParts.trechos;
          $scope.$apply();
        });
      };

      $scope.getMonthDate = function(date) {
        if(date == 'Representantes' || date == 'Totais') {
          return date;
        }

        var day = (new Date(date + ' 00:00:00')).getMonth();
        switch (day) {
          case 0: return 'Janeiro ' + (new Date(date)).getFullYear();
          case 1: return 'Fevereiro' + (new Date(date)).getFullYear();
          case 2: return 'Março ' + (new Date(date)).getFullYear();
          case 3: return 'Abril ' + (new Date(date)).getFullYear();
          case 4: return 'Maio ' + (new Date(date)).getFullYear();
          case 5: return 'Junho ' + (new Date(date)).getFullYear();
          case 6: return 'Julho ' + (new Date(date)).getFullYear();
          case 7: return 'Agosto ' + (new Date(date)).getFullYear();
          case 8: return 'Setembro ' + (new Date(date)).getFullYear();
          case 9: return 'Outubro ' + (new Date(date)).getFullYear();
          case 10: return 'Novembro ' + (new Date(date)).getFullYear();
          case 11: return 'Dezembro ' + (new Date(date)).getFullYear();
        }

      };

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageDealers = $scope.filteredDealers.slice(start, end);
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

      $scope.searchDealers = function() {
        $scope.filteredDealers = $filter('filter')($scope.dealersAnalysis, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredDealers = $filter('orderBy')($scope.dealersAnalysis, rowName);
        return $scope.onOrderChange();
      };

      $scope.loadDealers = function() {
        $.post("../backend/application/index.php?rota=/loadDealersAnalysisMonth", { data: $scope.filterDealer }, function(result){
          $scope.dealersMonth = jQuery.parseJSON(result).dataset;
          $scope.monthDealers = true;
          $scope.$digest();
        });
      };

      $scope.loadleaderBoarding = function() {
        $.post("../backend/application/index.php?rota=/loadleaderBoarding", { data: $scope.filterLeaderBoarding }, function(result){
          $scope.leaderBoardingData = jQuery.parseJSON(result).dataset;
          $scope.leaderBoarding = true;
          $scope.$digest();
        });
      };

      $scope.showLeaderBoarding = function() {
        $scope.showCharts = true;
        $scope.loadleaderBoarding();
      };

      $scope.showMonthDealers = function() {
        $scope.showCharts = true;
        $scope.loadDealers();
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageDealers = [];

      init = function() {
        $scope.checkValidRoute();
        $scope.comboData = [];
        $scope.clientChart = [];
        $scope.airlineChart = [];
        $scope.airlineMiles = [];
        $scope.clientsTotal = [];
        $scope.clientsCancels = [];
        $scope.clientsDaily = [];
        $scope.clientsState = [];
        $scope.clientsCountState = [];
        $scope.airportsTo = [];
        $scope.airportsFrom = [];
        $scope.airports = [];
        $scope.filterDealer = {};
        $scope.showCharts = false;
        $scope.filterLeaderBoarding = {
          days: 1
        }

        $.post("../backend/application/index.php?rota=/loadClientsTotalChart", $scope.session, function(result){
          $scope.comboData = jQuery.parseJSON(result).dataset;
        });
        $scope.search();

        $.post("../backend/application/index.php?rota=/loadClientsDaily", $scope.session, function(result){
          $scope.clientsDaily = jQuery.parseJSON(result).dataset;
        });

        $scope.options = {
          series: {
            pie: {
              show: true,
              innerRadius: 0.25
            }
          },
          legend: {
            show: false
          },
          grid: {
            hoverable: true,
            clickable: false
          },
          colors: ["#176799", "#2F87B0", "#42A4BB", "#5BC0C4", "#78D6C7", "#56B176", "#15582D", "#547D63", "#299431", "#906D38", "#903875", "#E21414", "#E1E41E", "#4CB938"],
          tooltip: true,
          tooltipOpts: {
            content: "%p.0%, %s",
            defaultTheme: false
          }
        };
      };

      return init();
    }
  ]).directive('flotChartClientsAirline', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.airlineChart;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.airlineChart);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.airlineChart);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotMilesClientsAirline', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.airlineMiles;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.airlineMiles);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.airlineMiles);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotClientsCancels', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.clientsCancels;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.clientsCancels);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.clientsCancels);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotChartClientsTotals', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.clientsTotal;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.clientsTotal);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.clientsTotal);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotChartClientsSales', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.clientChart;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.clientChart);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.clientChart);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('barClientsTotal', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 2500;
          switch (attrs.type) {
            case 'line':
              if (attrs.lineColors === void 0 || attrs.lineColors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.lineColors);
              }
              options = {
                element: ele[0],
                data: data,
                xkey: attrs.xkey,
                ykeys: JSON.parse(attrs.ykeys),
                labels: JSON.parse(attrs.labels),
                lineWidth: attrs.lineWidth || 2,
                lineColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
                resize: true
              };
              return new Morris.Line(options);
            case 'area':
              if (attrs.lineColors === void 0 || attrs.lineColors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.lineColors);
              }
              options = {
                element: ele[0],
                data: data,
                xkey: attrs.xkey,
                ykeys: JSON.parse(attrs.ykeys),
                labels: JSON.parse(attrs.labels),
                lineWidth: attrs.lineWidth || 2,
                lineColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
                behaveLikeLine: attrs.behaveLikeLine || false,
                fillOpacity: attrs.fillOpacity || 'auto',
                pointSize: attrs.pointSize || 4,
                resize: true
              };
              return new Morris.Area(options);
            case 'bar':
              if (attrs.barColors === void 0 || attrs.barColors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.barColors);
              }
              options = {
                element: ele[0],
                data: scope.$parent.comboData,
                xkey: attrs.xkey,
                ykeys: JSON.parse(attrs.ykeys),
                labels: JSON.parse(attrs.labels),
                barColors: ['#4da74d', '#c11313', '#e2671b', '#4c69e8', '#e84c4c', '#cb4b4b', '#9440ed'],
                stacked: attrs.stacked || null,
                resize: true
              };
              update = function() {
                setTimeout(finish, updateInterval);
                options.data = scope.$parent.comboData;
              };
              finish = function(){
                if(scope.$parent.comboData.length > 0) {
                  options.data = scope.$parent.comboData;
                  return new Morris.Bar(options);
                } else {
                  setTimeout(finish, updateInterval);
                }
              };
              return update();
            case 'donut':
              if (attrs.colors === void 0 || attrs.colors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.colors);
              }
              options = {
                element: ele[0],
                data: data,
                colors: colors || ['#0B62A4', '#3980B5', '#679DC6', '#95BBD7', '#B0CCE1', '#095791', '#095085', '#083E67', '#052C48', '#042135'],
                resize: true
              };
              if (attrs.formatter) {
                func = new Function('y', 'data', attrs.formatter);
                options.formatter = func;
              }
              return new Morris.Donut(options);
          }
        }
      };
    }
  ]).directive('chartClientsStates', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.clientsState;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.clientsState);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.clientsState);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('chartClientsCountStates', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.clientsCountState;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.clientsCountState);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.clientsCountState);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('morrisClientsDailys', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 1000;
          if (attrs.lineColors === void 0 || attrs.lineColors === '') {
            colors = null;
          } else {
            colors = JSON.parse(attrs.lineColors);
          }
          options = {
            element: ele[0],
            data: scope.$parent.clientsDaily,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0000ff', '#ff9933', '#ff0000', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            updateInterval = 3000;
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.clientsDaily;
          };
          finish = function(){
            if(scope.$parent.clientsDaily.length > 0) {
              options.data = scope.$parent.clientsDaily;
              return new Morris.Line(options);
            } else {
              setTimeout(finish, updateInterval);
            }
          };
          return update();
        }
      };
    }
  ]).directive('morrisAirportsFrom', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 1000;
          if (attrs.lineColors === void 0 || attrs.lineColors === '') {
            colors = null;
          } else {
            colors = JSON.parse(attrs.lineColors);
          }
          options = {
            element: ele[0],
            data: scope.$parent.airportsFrom,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0000ff', '#ff9933', '#ff0000', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            updateInterval = 4000;
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.airportsFrom;
            // return new Morris.Bar(options);
          };
          finish = function(){
            options.data = scope.$parent.airportsFrom;
            return new Morris.Bar(options);
          };
          return update();
        }
      };
    }
  ]).directive('morrisAirportsTo', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 1000;
          if (attrs.lineColors === void 0 || attrs.lineColors === '') {
            colors = null;
          } else {
            colors = JSON.parse(attrs.lineColors);
          }
          options = {
            element: ele[0],
            data: scope.$parent.airportsTo,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0000ff', '#ff9933', '#ff0000', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            updateInterval = 4000;
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.airportsTo;
            // return new Morris.Bar(options);
          };
          finish = function(){
            options.data = scope.$parent.airportsTo;
            return new Morris.Bar(options);
          };
          return update();
        }
      };
    }
  ]).directive('morrisAirportsTrechos', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 1000;
          if (attrs.lineColors === void 0 || attrs.lineColors === '') {
            colors = null;
          } else {
            colors = JSON.parse(attrs.lineColors);
          }
          options = {
            element: ele[0],
            data: scope.$parent.airports,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#afd8f8', '#ff9933', '#ff0000', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            updateInterval = 4000;
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.airports;
            // return new Morris.Bar(options);
          };
          finish = function(){
            options.data = scope.$parent.airports;
            return new Morris.Bar(options);
          };
          return update();
        }
      };
    }
  ]).controller('DealerTableModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {
      $scope.filterproviders = $scope.$parent.providers;

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "DealerTableModal.html",
          controller: 'DealerTableInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filterDealer: function() {
              return $scope.$parent.filterDealer;
            }
          }
        });
        modalInstance.result.then((function(filterDealer) {
          if (filterDealer != undefined) {
            filterDealer._issueDateFrom = $rootScope.formatServerDate(filterDealer.issueDateFrom);
            filterDealer._issueDateTo = $rootScope.formatServerDate(filterDealer.issueDateTo);
          }

          $scope.$parent.filterDealer = filterDealer;
          $scope.$parent.loadDealers();
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };
    }
  ]).controller('DealerTableInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filterDealer', function($scope, $rootScope, $modalInstance, filterDealer) {
      $scope.filterDealer = filterDealer;

      $scope.ok = function() {
        $modalInstance.close($scope.filterDealer);
      };

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

    }
  ]);
})();
;
