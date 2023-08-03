(function () {
  'use strict';
  angular.module('app.purchase').controller('BalanceProvidersCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.clientStatus = ['Pendente', 'Aprovado', 'Bloqueado', 'Reprovado', 'Desistencia'];
      $scope.searchKeywords = '';
      $scope.filter = {
        days: 7,
        client: ''
      };

      $scope.search = function() {
        $.post("../backend/application/index.php?rota=/loadPurchaseMiles", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.airlinesData = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadPurchasesAnalysis", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.PurchasesData = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.airlinesData = [];
        $scope.PurchasesData = [];
        $scope.PurchasesTotal = [];

        $scope.search();

        // $.post("../backend/application/index.php?rota=/loadProvider", $scope.session, function(result){
        //   $scope.providers = jQuery.parseJSON(result).dataset.providers;
        // });
        $.post("../backend/application/index.php?rota=/loadMergedMiles", $scope.session, function(result){
          $scope.mergedMiles = jQuery.parseJSON(result).dataset;
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
  ]).directive('flotMilesPurchasesAirline', [
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
          data = scope.$parent.airlinesData;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.airlinesData);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.airlinesData);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotPurchasesAirline', [
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
          data = scope.$parent.PurchasesData;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.PurchasesData);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.PurchasesData);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('morrisLineMergedMiles', [
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
            data: scope.$parent.mergedMiles,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0000ff', '#ff9933', '#ff0000', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.mergedMiles;
            // return new Morris.Bar(options);
          };
          finish = function(){
            options.data = scope.$parent.mergedMiles;
            return new Morris.Line(options);
          };
          return update();
        }
      };
    }
  ]);
})();
;
