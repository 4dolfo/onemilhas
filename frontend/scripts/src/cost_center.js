(function () {
  'use strict';
  angular.module('app.cashFlow').controller('CostCenterCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.filteredValueDescriptions = [];
      $scope.row = '';
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageValueDescription = $scope.filteredValueDescriptions.slice(start, end);
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
        $scope.filteredValueDescriptions = $filter('filter')($scope.valueDescriptions, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        original = angular.copy(this.valueDescription);
        $scope.valueDescription = original;
        $scope.isTable = false;
        return this.valueDescription;
      };

      $scope.newValueDescription = function() {
        $scope.valueDescription = {};
        $scope.valueDescription.type = 'C';
        $scope.isTable = false;
      };

      $scope.toggleFormTable = function() {
        $scope.isTable = !$scope.isTable;
        return $scope.isTable;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredValueDescriptions = $filter('orderBy')($scope.valueDescriptions, rowName);
        return $scope.onOrderChange();
      };

      $scope.getType = function(type){
        if(type == "C") return "Custo";
        if(type == "R") return "Receita";
        if(type == "T") return "Transito";
      }

      $scope.getTypeColorClass = function(type){
        if(type == "C" || type == "T") return "warning";
        if(type == "R") return "success";
      }

      $scope.saveValueDescription = function() {
        if ($scope.form_cost_center.$valid) {
         
          $.post("../backend/application/index.php?rota=/saveCostCenter", {hashId: $scope.session.hashId, data: $scope.valueDescription}, function(result){
            $scope.isTable = !$scope.isTable;
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadValueDescription();
          });
        }
      };

      $scope.cancelEdit = function() {
        $scope.isTable = !$scope.isTable;
      };

      $scope.loadValueDescription = function() {
        $.post("../backend/application/index.php?rota=/loadCostCenter", $scope.session, function(result){
            $scope.valueDescriptions = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageValueDescription = [];
      
      init = function() {
        $scope.checkValidRoute();
        $scope.isTable = true;
        cfpLoadingBar.start();

        $scope.loadValueDescription();
      };
      return init();
    }
  ]);
})();

;