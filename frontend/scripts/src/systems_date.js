(function () {
  'use strict';
  angular.module('app.marketing').controller('SystemsDataCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.searchKeywordsMiles = '';
      $scope.filteredSystems = [];
      $scope.row = '';

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageSystems = $scope.filteredSystems.slice(start, end);
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
        $scope.filteredSystems = $filter('filter')($scope.systems, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        $scope.selected = this.system;
        $scope.tabindex = 1;
      };

      $scope.newSystem = function() {
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredSystems = $filter('orderBy')($scope.systems, rowName);
        return $scope.onOrderChange();
      };

      $scope.saveSystem = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/systems/save", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadModalData();
          $scope.$apply();
          $scope.tabindex = 0;
        });
      };

      $scope.numPerPageOptMiles = [10, 30, 50, 100];
      $scope.numPerPageMiles = $scope.numPerPageOptMiles[2];
      $scope.currentPageMiles = 1;
      $scope.currentMiles = [];

      $scope.selectMiles = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPageMiles;
        end = start + $scope.numPerPageMiles;
        return $scope.currentMiles = $scope.filteredflight_miles.slice(start, end);
      };

      $scope.onNumPerPageChangeMiles = function() {
        $scope.selectMiles(1);
        return $scope.currentPageMiles = 1;
      };

      $scope.cancelEdit = function() {
        $scope.loadModalData();
        $scope.tabindex = 0;
      };

      $scope.loadModalData = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/systems/load", {}, function(result){
            $scope.systems = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageSystems = [];
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.loadModalData();
      };
      return init();
    }
  ]);
})();

;