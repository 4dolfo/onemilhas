(function () {
  'use strict';
  angular.module('app.balance').controller('BalanceOrdesCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function ($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      $scope.filter = {
        days: 7,
      };

      $scope.order = function (rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.UsersSales = $filter('orderBy')($scope.UsersSales, rowName);
      };

      $scope.search = function () {
        $.post("../backend/application/index.php?rota=/loadUserSales", { data: $scope.filter }, function (result) {
          $scope.userSales = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.loadUsersDescription = function () {
        $scope.sales = [];
        $.post("../backend/application/index.php?rota=/loadUsersPannel", { data: $scope.filterSales }, function (result) {
          $scope.UsersSales = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.findUserProcessingTime = function (user) {
        $scope.sales = [];
        $.post("../backend/application/index.php?rota=/findUserProcessingTime", { data: user, filter: $scope.filterSales }, function (result) {
          $scope.UserProcessingTime = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.findUserSalesDay = function (day) {
        $scope.sales = [];
        $.post("../backend/application/index.php?rota=/findUserSalesDay", { data: day, filter: $scope.UserProcessingTime }, function (result) {
          $scope.sales = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.findUserSalesFilter = function (UserProcessingTime) {
        $scope.sales = [];
        $.post("../backend/application/index.php?rota=/findUserSalesFilter", { data: $scope.UserProcessingTime, filter: $scope.filterSales }, function (result) {
          $scope.sales = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.loadOrdersPerHour = function (UserProcessingTime) {
        $scope.ordersPerHour = [];
        $.post("../backend/application/index.php?rota=/loadOrdersPerHour", {}, function (result) {
          $scope.ordersPerHour = jQuery.parseJSON(result).dataset;                   
          $scope.$digest();
          $scope.ordersPerHour = $scope.formatHour($scope.ordersPerHour);
        });
      };

      $scope.formatHour = function(ordersPerHour){
        var ordersPerHour = ordersPerHour;

        ordersPerHour.forEach(element => {

          if(parseInt(element.value) <10){
            element.value = '0'+element.value + ':' + '00'; 
          }
          else{
            element.value = element.value + ':' + '00'; 
          }
        });

        return ordersPerHour;
      };

      $scope.findDate = function (date) {
        if (date == '') {
          return '';
        }
        if (date.length > 10)
          return new Date(date);
        var date = new Date(date);
        return date.setDate(date.getDate() + 1);
      };

      init = function () {
        $scope.checkValidRoute();
        $scope.userSales = [];
        $scope.users = {
          values: []
        };
        $scope.showCharts = false;
        $scope.filterSales = {
          airlines: {},
          days: 7
        };
        $scope.ordersPerHour = [];

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

        $scope.search();
        $scope.loadOrdersPerHour();
        $.post("../backend/application/index.php?rota=/loadUserHistory", { data: $scope.filter }, function (result) {
          $scope.users = jQuery.parseJSON(result).dataset;
        });

        $.post("../backend/application/index.php?rota=/loadSumOrderMilesCancels", {}, function (result) {
          $scope.SumOrderMiles = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });

        $.post("../backend/application/index.php?rota=/airline/loadValidAirlines", {}, function (result) {
          $scope.airlines = jQuery.parseJSON(result).dataset;
          for(var i in $scope.airlines) {
            $scope.filterSales.airlines[$scope.airlines[i].id] = true;
          }
          $scope.filterSales.airlines['todas'] = true;
          $scope.airlines.push({
            id: 'todas',
            name: 'todas'
          });
          $scope.$digest();
        });

      };

      $scope.open = function () {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "BalanceModalCtrl.html",
          controller: 'BalanceModalInstanceCtrl',
          resolve: {
            filterSales: function() {
              return $scope.filterSales;
            }
          }
        });
        modalInstance.result.then((function (filterSales) {
          filterSales._dateFrom = $rootScope.formatServerDate(filterSales.dateFrom);
          filterSales._dateTo = $rootScope.formatServerDate(filterSales.dateTo);
          $scope.filterSales = filterSales;
          $scope.loadUsersDescription();
        }));
      };

      return init();
    }
  ]).controller('BalanceModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filterSales', function ($scope, $rootScope, $modalInstance, filterSales) {
      $scope.filterSales = filterSales;

      $scope.ok = function () {
        $modalInstance.close($scope.filterSales);
      };
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };

    }
  ]).directive('morrisUserEmissionsAnalysis', [
    function () {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function (scope, ele, attrs, update, bar, updateInterval, finish) {
          
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
            data: scope.$parent.users.values,
            xkey: attrs.xkey,
            ykeys: scope.$parent.users.keys,
            labels: scope.$parent.users.names,
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0b62a4', '#ff0066', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function () {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.users.values;
          };
          finish = function () {
            options.data = scope.$parent.users.values;
            if (scope.$parent.users.values.length > 0) {
              options.ykeys = scope.$parent.users.keys;
              options.labels = scope.$parent.users.names;
              return new Morris.Line(options);
            } else {
              setTimeout(finish, updateInterval);
            }
          };
          return update();
        }
      };
    }
  ]).directive('morrisemissionsperhour', [
    function () {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function (scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;

          data = scope.$parent.userSales;
          plot = $.plot(ele[0], data, options);
          
          update = function () {
            plot.setData(scope.$parent.userSales);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function () {
            plot.setData(scope.$parent.userSales);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('morrisEmissionsPerHour', [
    function () {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function (scope, ele, attrs, update, bar, updateInterval, finish) {
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
            data: scope.$parent.ordersPerHour,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            parseTime: false,
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0b62a4', '#ff0066', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };

          update = function () {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.ordersPerHour;
          };
          finish = function () {
            if (scope.$parent.ordersPerHour.length > 0) {
              options.data = scope.$parent.ordersPerHour;
              return new Morris.Line(options);
            } else {
              setTimeout(finish, updateInterval);
            }
          };
          return update();
        }
      };
    }
  ]);
})();
;
