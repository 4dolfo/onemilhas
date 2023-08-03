(function () {
  'use strict';
  angular.module('app.purchase').controller('AirportCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.filteredAirports = [];
      $scope.row = '';
      $scope.locations = ['America Norte', 'America Sul'];

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageAirports = $scope.filteredAirports.slice(start, end);
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
        $scope.filteredAirports = $filter('filter')($scope.airports, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        original = angular.copy(this.airport);
        $scope.selected = this.airport;
        $scope.isTable = false;
        return this.airport;
      };

      $scope.newAirport = function() {
        $scope.selected = {};
        $scope.selected.type = 'U';
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
        $scope.filteredAirports = $filter('orderBy')($scope.airports, rowName);
        return $scope.onOrderChange();
      };

      $scope.saveAirport = function() {
        if ($scope.form_user.$valid) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/saveAirport", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.airports.push($scope.selected);
            $scope.$apply();
            $scope.isTable = !$scope.isTable;
          });
          cfpLoadingBar.complete();
        }
      };

      $scope.saveInternational = function(airport) {
        $.post("../backend/application/index.php?rota=/saveAirport", {hashId: $scope.session.hashId, data: airport}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadAirport();
        });
      };

      $scope.cancelEdit = function() {
        $scope.loadAirport();
        $scope.isTable = !$scope.isTable;
      };

      $scope.loadAirport = function() {
        $.post("../backend/application/index.php?rota=/loadAirport", $scope.session, function(result){
            $scope.airports = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageAirports = [];
      init = function() {
        $scope.checkValidRoute();
        $scope.isTable = true;
        cfpLoadingBar.start();
        $scope.cities = [];
        $scope.loadAirport();

        $.post("../backend/application/index.php?rota=/loadState", $scope.session, function(result){
            $scope.states = jQuery.parseJSON(result).dataset;
        });

        $('#state').on('blur', function(obj, datum) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadCity", {hashId: $scope.session.hashId, state: $scope.selected.citystate}, function(result){
            $scope.cities = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
          });
        });

      };
      return init();
    }
  ]);
})();

;