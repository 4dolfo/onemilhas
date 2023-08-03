(function () {
  'use strict';
  angular.module('app.marketing').controller('CuponsB2CCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.searchKeywordsMiles = '';
      $scope.filteredPromos = [];
      $scope.row = '';
      $scope.clientStatus = ['Aguardando Aprov'];

      $scope.setSelected = function() {
        $scope.selected = this.cupon;
        $scope.selected._dataInicio = new Date($scope.selected.dataInicio);
        $scope.selected._dataFim = new Date($scope.selected.dataFim);
        $scope.tabindex = 1;
      };

      $scope.newDealer = function() {
        $scope.selected = {};
        $scope.selected.status = 'Aguardando Aprov';
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadCupons();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadCupons();
      };

      $scope.saveCupom = function() {
        cfpLoadingBar.start();
        if($scope.selected._dataInicio !== "" && $scope.selected._dataInicio != 'Invalid Date') {
          $scope.selected.dataInicio = $rootScope.formatServerDate($scope.selected._dataInicio);
        }
        if($scope.selected._dataFim !== "" && $scope.selected._dataFim != 'Invalid Date') {
          $scope.selected.dataFim = $rootScope.formatServerDate($scope.selected._dataFim);
        }
        $.post("../backend/application/index.php?rota=/marketing/saveCupom", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadCupons();
          $scope.$apply();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
        });
      };

      $scope.aprovar = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/aprovarCupom", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadCupons();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };

      $scope.cancelarCupom = function(cupon) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/inativarCupom", { data: cupon }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadCupons();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };

      $scope.numPerPageOptMiles = [10, 30, 50, 100];
      $scope.numPerPageMiles = $scope.numPerPageOptMiles[2];
      $scope.currentPageMiles = 1;
      $scope.currentMiles = [];

      $scope.cancelEdit = function() {
        $scope.loadCupons();
        $scope.tabindex = 0;
      };

      $scope.loadCupons = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/loadCupons", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown }, function(result){
            $scope.cupons = jQuery.parseJSON(result).dataset.cupons;
            $scope.totalData = jQuery.parseJSON(result).dataset.total;
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

        $.post("../backend/application/index.php?rota=/marketing/loadDealers", { }, function(result){
          $scope.dealers = jQuery.parseJSON(result).dataset.dealers;
          $scope.$digest();
        });
      };
      return init();
    }
  ]);
})();

;