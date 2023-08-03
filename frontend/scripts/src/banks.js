(function () {
  'use strict';
  angular.module('app.banks').controller('BanksCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.filteredBanks = [];
      $scope.row = '';
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageBanks = $scope.filteredBanks.slice(start, end);
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
        $scope.filteredBanks = $filter('filter')($scope.banks, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        $scope.selected = this.bank;
        $scope.tabindex = 1;
        return console.log(this.bank);
      };

      $scope.newBank = function() {
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredBanks = $filter('orderBy')($scope.banks, rowName);
        return $scope.onOrderChange();
      };

      $scope.cancelEdit = function() {
        $scope.loadBanks();
        $scope.tabindex = 0;
      };

      $scope.loadBanks = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/banks/load", {}, function(result){
          $scope.banks = jQuery.parseJSON(result).dataset;
          cfpLoadingBar.complete();
          $scope.tabindex = 0;
          return $scope.search();
        });
      };

      $scope.save = function() {
        $.post("../backend/application/index.php?rota=/banks/save", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadBanks();
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageBanks = [];
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;

        $scope.loadBanks();
      };
      return init();
    }
  ]);
})();

;