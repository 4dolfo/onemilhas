(function () {
  'use strict';
  angular.module('app.purchase').controller('PlansAirlineCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;

      $scope.searchKeywords = '';
      $scope.filteredPlans = [];
      $scope.row = '';
      $scope.operations = ['Reembolso', 'Remarcação', 'Analise Risco'];

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPagePlans = $scope.filteredPlans.slice(start, end);
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
        $scope.filteredPlans = $filter('filter')($scope.airlinePlans, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        $scope.selected = this.plan;
        $.post("../backend/application/index.php?rota=/loadPlanControl", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.controlAirline = jQuery.parseJSON(result).dataset;
          $scope.tabindex = 1;
          $scope.$apply();
        });
      };

      $scope.newPlan = function() {
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.toggleFormTable = function() {
        $scope.isTable = !$scope.isTable;
        return console.log($scope.isTable);
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredPlans = $filter('orderBy')($scope.airlinePlans, rowName);
        return $scope.onOrderChange();
      };

      $scope.addAirline = function(airline) {
        for(var i in $scope.controlAirline) {
          if($scope.controlAirline[i].airline == airline) {
            return logger.logError('ERROR');
          }
        }
        $scope.controlAirline.push({
          airline: airline,
          plansData: [
            { type: 'Reembolso' },
            { type: 'Remarcação' },
            { type: 'Analise Risco' }
          ]
        });
      };

      $scope.savePlan = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/savePlanControl", {hashId: $scope.session.hashId, data: $scope.selected, control: $scope.controlAirline}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadPlans();
        });
      };

      $scope.cancelEdit = function() {
        $scope.loadPlans();
        $scope.tabindex = 0;
      };

      $scope.loadPlans = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadControlPlans", $scope.session, function(result){
          $scope.airlinePlans = jQuery.parseJSON(result).dataset;
          $scope.search();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[1];
      $scope.currentPage = 1;
      $scope.currentPagePlans = [];

      init = function() {
        $scope.tabindex = 0;
        $scope.controlAirline = [];
        $scope.checkValidRoute();
        $.post("../backend/application/index.php?rota=/loadAirline", $scope.session, function(result){
          $scope.airlines = jQuery.parseJSON(result).dataset;
        });

        $scope.loadPlans();

      };

      return init();
    }
  ]);
})();
;