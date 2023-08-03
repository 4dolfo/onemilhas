(function () {
  'use strict';
  angular.module('app.marketing').controller('CampanhasB2CCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.searchKeywordsMiles = '';
      $scope.filteredPromos = [];
      $scope.row = '';
      $scope.clientStatus = ['Aprovado', 'Bloqueado'];

      $scope.setSelected = function() {
        $scope.selected = this.campanha;
        $scope.tabindex = 1;
      };

      $scope.newDealer = function() {
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadCampanha();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadCampanha();
      };

      $scope.saveCampanha = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/saveCampanha", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadCampanha();
          $scope.$apply();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
        });
      };

      $scope.numPerPageOptMiles = [10, 30, 50, 100];
      $scope.numPerPageMiles = $scope.numPerPageOptMiles[2];
      $scope.currentPageMiles = 1;
      $scope.currentMiles = [];

      $scope.cancelEdit = function() {
        $scope.loadCampanha();
        $scope.tabindex = 0;
      };

      $scope.loadCampanha = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/loadCampanha", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown }, function(result){
            $scope.campanhas = jQuery.parseJSON(result).dataset.campanhas;
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
        $scope.loadCampanha();

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