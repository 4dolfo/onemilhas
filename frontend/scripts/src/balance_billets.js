(function () {
  'use strict';
  angular.module('app.table').controller('BalanceBilletsCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.clientStatus = ['Pendente', 'Aprovado', 'Bloqueado', 'Reprovado'];
      $scope.clientPayments = ['Boleto', 'Antecipado'];
      $scope.searchKeywords = '';
      $scope.filteredClients = [];
      $scope.row = '';
      $scope.partners = [{}, {}];
      $scope.filter = {
        days: 7,
        client: ''
      };

      $scope.search = function() {
        $.post("../backend/application/index.php?rota=/loadBilletsLate", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.billetsLate = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.billetsLate = [];
        $scope.billetsPayment = [];
        $scope.BilletsDefaultDaily = [];
        $scope.BilletsDefaultMonthly = [];
        $scope.search();

        $.post("../backend/application/index.php?rota=/loadBilletsPayment", $scope.session, function(result){
          $scope.billetsPayment = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });

        $.post("../backend/application/index.php?rota=/loadBilletsDefaultDaily", $scope.session, function(result){
          $scope.BilletsDefaultDaily = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });

        $.post("../backend/application/index.php?rota=/loadBilletsDefaultMonthly", $scope.session, function(result){
          $scope.BilletsDefaultMonthly = jQuery.parseJSON(result).dataset;
          $scope.$apply();
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
          colors: ["#903875, #176799", "#2F87B0", "#42A4BB", "#5BC0C4", "#78D6C7", "#56B176", "#15582D", "#547D63", "#299431", "#906D38", "#E21414", "#E1E41E", "#4CB938"],
          tooltip: true,
          tooltipOpts: {
            content: "%p.0%, %s",
            defaultTheme: false
          }
        };
      };

      return init();
    }
  ]).directive('flotChartBilletsLate', [
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
          data = scope.$parent.billetsLate;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.billetsLate);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.billetsLate);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('morrisBilletsPayment', [
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
            data: scope.$parent.billetsPayment,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0000ff', '#ff9933', '#ff0000', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            setTimeout(finish, updateInterval);
            console.log(scope.$parent);
            options.data = scope.$parent.billetsPayment;
            // return new Morris.Bar(options);
          };
          finish = function(){
            options.data = scope.$parent.billetsPayment;
            return new Morris.Line(options);
          };
          return update();
        }
      };
    }
  ]).directive('morrisBilletsDefaultMonthly', [
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
            data: scope.$parent.BilletsDefaultMonthly,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ["#56B176", "#15582D", "#547D63"],
            resize: true
          };
          update = function() {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.BilletsDefaultMonthly;
          };
          finish = function(){
            options.data = scope.$parent.BilletsDefaultMonthly;
            return new Morris.Line(options);
          };
          return update();
        }
      };
    }
  ]).directive('morrisBilletsDefaultDaily', [
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
            data: scope.$parent.BilletsDefaultDaily,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ["#56B176", "#15582D", "#547D63"],
            resize: true
          };
          update = function() {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.BilletsDefaultDaily;
          };
          finish = function(){
            options.data = scope.$parent.BilletsDefaultDaily;
            return new Morris.Line(options);
          };
          return update();
        }
      };
    }
  ]);
})();
;
