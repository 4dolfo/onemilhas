(function () {
  'use strict';
  angular.module('app.miles').controller('BalanceMilesCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.filter = {
        days: 7,
        client: '',
        points: 4000
      };
      $scope.showFilter = false;

      $scope.search = function() {
        if($scope.$parent.$parent.main.isMaster){
          if($scope.showFilter) {
            $scope.filter._dateFrom = $rootScope.formatServerDate($scope.filter.dateFrom);
            $scope.filter._dateTo = $rootScope.formatServerDate($scope.filter.dateTo);
          }
        }
        $.post("../backend/application/index.php?rota=/loadBlueSalesSRM", {data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.blueSRMSales = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadbluePointsSRM", {data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.blueSRMPoints = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadbluePoints", {hashId: $scope.session.hashId, data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.bluePoints = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadbluePointsCompetitive", {hashId: $scope.session.hashId, data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.bluePointsCompetitive = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadbluePointsMonopoly", {hashId: $scope.session.hashId, data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.bluePointsMonopoly = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadTotalMiles", {hashId: $scope.session.hashId, data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.totalMiles = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadLatamSales", {hashId: $scope.session.hashId, data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.laTamSales = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadLatamPoints", {hashId: $scope.session.hashId, data: $scope.filter, searchType: $scope.showFilter}, function(result){
          $scope.laTamPoints = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.loadAzulSRM = function() {
        $.post("../backend/application/index.php?rota=/loadAZULSRMUsage", { }, function(result){
          $scope.AZULSRMUsage = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadAZULMMSUsage", { }, function(result){
          $scope.AZULMMSUsage = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.saveChangeMiles = function(data) {
        $.post("../backend/application/index.php?rota=/saveChangeMilesAZULSRM", { data: data }, function(result){
          $scope.loadAzulSRM();
        });
      };

      $scope.attAverageMiles = function(){
        $.post("../backend/application/index.php?rota=/loadAverageMiles", {data: $scope.filter}, function(result){
          $scope.averageMiles = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.milesAnalysis = [];
        $scope.bluePoints = [];
        $scope.blueSRMSales = [];
        $scope.blueSRMPoints = [];
        $scope.bluePointsCompetitive = [];
        $scope.bluePointsMonopoly = [];
        $scope.cancelSales = [];
        $scope.refoundSales = [];
        $scope.totalMiles = [];
        $scope.laTamSales = [];
        $scope.laTamPoints = [];
        $scope.search();
        
        $.post("../backend/application/index.php?rota=/loadMilesAnalysis", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.milesAnalysis = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadCancelSalesChart", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.cancelSales = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadRefoundSalesChart", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.refoundSales = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        
        /*$.post("../backend/application/index.php?rota=/loadAverageMiles", { }, function(result){
          $scope.averageMiles = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });*/
        $scope.attAverageMiles();
        
        $scope.loadAzulSRM();
        
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
  ]).directive('morrisMilesAnalysis', [
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
            data: scope.$parent.milesAnalysis,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0000ff', '#ff9933', '#ff0000', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.milesAnalysis;
            // return new Morris.Bar(options);
          };
          finish = function(){
            if(scope.$parent.milesAnalysis.length > 0) {
              options.data = scope.$parent.milesAnalysis;
              return new Morris.Line(options);
            } else {
              setTimeout(finish, updateInterval);
            }
          };
          return update();
        }
      };
    }
  ]).directive('flotSrmPoints', [
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
          data = scope.$parent.bluePoints;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.bluePoints);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.bluePoints);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotSrmPointsMonopoly', [
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
          data = scope.$parent.bluePointsMonopoly;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.bluePointsMonopoly);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.bluePointsMonopoly);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotSrmPointsCompetitive', [
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
          data = scope.$parent.bluePointsCompetitive;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.bluePointsCompetitive);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.bluePointsCompetitive);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotCancelSalesPoints', [
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
          data = scope.$parent.cancelSales;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.cancelSales);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.cancelSales);
            plot.draw();
          };
          updateInterval = 2000;
          return update();
        }
      };
    }
  ]).directive('flotRefoundSalesPoints', [
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
          data = scope.$parent.refoundSales;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.refoundSales);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.refoundSales);
            plot.draw();
          };
          updateInterval = 2000;
          return update();
        }
      };
    }
  ]).directive('flotTotalMilesUsed', [
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
          data = scope.$parent.totalMiles;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.totalMiles);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.totalMiles);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 2000;
          return update();
        }
      };
    }
  ]).directive('floatLatamSales', [
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
          data = scope.$parent.laTamSales;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.laTamSales);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.laTamSales);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 2000;
          return update();
        }
      };
    }
  ]).directive('floatLatamPoints', [
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
          data = scope.$parent.laTamPoints;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.laTamPoints);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.laTamPoints);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 2000;
          return update();
        }
      };
    }
  ]).directive('flotSrmSales', [
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
          data = scope.$parent.blueSRMSales;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.blueSRMSales);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.blueSRMSales);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).directive('flotSrmPointsSales', [
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
          data = scope.$parent.blueSRMPoints;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.blueSRMPoints);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.blueSRMPoints);
            plot.draw();
            updateInterval = 5000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]);
})();
;
