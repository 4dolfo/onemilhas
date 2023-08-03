(function () {
  'use strict';
  angular.module('app.purchase').controller('AirlineCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.searchKeywordsMiles = '';
      $scope.filteredAirlines = [];
      $scope.row = '';

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageAirlines = $scope.filteredAirlines.slice(start, end);
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
        $scope.filteredAirlines = $filter('filter')($scope.airlines, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        original = angular.copy(this.airline);
        $scope.selected = this.airline;
        $scope.tabindex = 1;
        $('#value').number( true, 2, ',', '.');
        $('#cards_limit').number( true, 0, ',', '.');
        $('#miles_limit').number( true, 0, ',', '.');
        return this.airline;
      };

      $scope.newAirline = function() {
        $scope.selected = {};
        $scope.selected.type = 'U';
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredAirlines = $filter('orderBy')($scope.airlines, rowName);
        return $scope.onOrderChange();
      };

      $scope.saveAirline = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/saveAirline", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadAirport();
          $scope.$apply();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
        });
      };

      $scope.searchMiles = function() {
        $.post("../backend/application/index.php?rota=/loadSalesMiles", { milesUsed: 50000, airline: $scope.selected.name }, function(result){
          $scope.miles = jQuery.parseJSON(result).dataset;
          $scope.search_miles();
          $scope.tabindex = 2;
          $scope.$digest();
        });
      };

      $scope.setCard = function() {
        $scope.selected.cards_id = this.mile.cards_id;
        $scope.selected.provider_name = this.mile.name;
        $scope.tabindex = 1;
      };

      $scope.search_miles = function() {
        $scope.currentMiles = $filter('filter')($scope.miles, $scope.searchKeywordsMiles);
        $scope.$digest();
      };
      
      $scope.order_miles = function(rowName) {
        $scope.rowMiles = rowName;
        $scope.filteredflight_miles = $filter('orderBy')($scope.flight_miles, rowName);
        $scope.onOrderMilesChange();
      };

      $scope.onOrderMilesChange = function() {
        $scope.selectMiles(1);
        return $scope.currentPageMiles = 1;
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
        $scope.loadAirport();
        $scope.tabindex = 0;
      };

      $scope.loadAirport = function() {
        $.post("../backend/application/index.php?rota=/loadAirline", $scope.session, function(result){
            $scope.airlines = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.removeOperation = function() {
        if($scope.selected.operations.length > 0) {
          $scope.selected.operations.pop();
        }
      };

      $scope.addOperation = function() {
        if($scope.selected.operations.length < 3) {
          $scope.selected.operations.push({
            type: '',
            nationalBeforeBoarding: 0,
            nationalAfterBoarding: 0,
            internationalBeforeBoarding: 0,
            internationalAfterBoarding: 0,
            northAmericaBeforeBoarding: 0,
            northAmericaAfterBoarding: 0,
            southAmericaBeforeBoarding: 0,
            southAmericaAfterBoarding: 0
          });
        }
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageAirlines = [];
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        cfpLoadingBar.start();
        
        $scope.loadAirport();
      };
      return init();
    }
  ]);
})();

;