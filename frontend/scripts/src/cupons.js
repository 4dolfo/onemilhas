(function () {
  'use strict';
  angular.module('app.marketing').controller('CuponsCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.searchKeywordsMiles = '';
      $scope.filteredPromos = [];
      $scope.row = '';

      // Implementando checkboxes no angular
      $scope.aereas = ['GOL', 'LATAM', 'AZUL', 'AVIANCA'];
      $scope.selectedAereas = [];

      $scope.toggleSelectedAereas = function(aerea) {
        var idx = $scope.selectedAereas.indexOf(aerea);

        if(idx > -1) {
            $scope.selectedAereas.splice(idx, 1);
        } else {
            $scope.selectedAereas.push(aerea);
        }
      }

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPagePromos = $scope.filteredPromos.slice(start, end);
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
        $scope.filteredPromos = $filter('filter')($scope.promos, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        $scope.selected = this.cupom;
        $scope.selected._dataExpiracao = new Date($scope.selected.dataExpiracao);
        $scope.selected._dataInicio = new Date($scope.selected.dataInicio);
        $scope.selectedAereas = this.cupom.aereas.slice();
        $scope.tabindex = 1;
      };

      $scope.newPromo = function() {
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredPromos = $filter('orderBy')($scope.promos, rowName);
        return $scope.onOrderChange();
      };

      $scope.saveCupon = function() {
        cfpLoadingBar.start();
        $scope.selected.dataInicio = $rootScope.formatServerDateTime($scope.selected._dataInicio);
        $scope.selected.dataExpiracao = $rootScope.formatServerDateTime($scope.selected._dataExpiracao);

        // Enviando aéreas para o servidor
        $scope.selected.selectedAereas = $scope.selectedAereas;

        $.post("../backend/application/index.php?rota=/marketing/saveCupon", { data: $scope.selected }, function(result){
          console.log($scope.selected);
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadCupons();
          $scope.$apply();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
        });
      };

      $scope.deleteCupons = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/deleteCupons", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadCupons();
          $scope.tabindex = 0;
          $scope.$digest();
        });
      };

      $scope.numPerPageOptMiles = [10, 30, 50, 100];
      $scope.numPerPageMiles = $scope.numPerPageOptMiles[2];
      $scope.currentPageMiles = 1;
      $scope.currentMiles = [];

      $scope.onNumPerPageChangeMiles = function() {
        $scope.selectMiles(1);
        return $scope.currentPageMiles = 1;
      };

      $scope.cancelEdit = function() {
        $scope.loadCupons();
        $scope.tabindex = 0;
      };

      $scope.loadCupons = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/cupons", {}, function(result){
            $scope.cupons = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
            $scope.$digest();
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPagePromos = [];
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.loadCupons();

      };
      return init();
    }
  ]);
})();

;