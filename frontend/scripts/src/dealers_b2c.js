(function () {
  'use strict';
  angular.module('app.marketing').controller('DealersB2CCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.searchKeywordsMiles = '';
      $scope.filteredPromos = [];
      $scope.row = '';
      $scope.clientStatus = ['Aprovado', 'Bloqueado'];

      $scope.setSelected = function() {
        $scope.selected = this.dealer;
        $scope.tabindex = 1;
        $scope.loadSubDealers();
      };

      $scope.newDealer = function() {
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadDealers();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadDealers();
      };

      $scope.saveDealer = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/saveDealer", { data: $scope.selected }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadDealers();
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
        $scope.loadDealers();
        $scope.tabindex = 0;
      };

      $scope.loadDealers = function() {
        cfpLoadingBar.start();
        console.log($scope.searchKeywords)
        $.post("../backend/application/index.php?rota=/marketing/loadDealers", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown }, function(result){
            $scope.dealers = jQuery.parseJSON(result).dataset.dealers;
            $scope.totalData = jQuery.parseJSON(result).dataset.total;
            cfpLoadingBar.complete();
            $scope.$digest();
        });
      };

      $scope.loadSubDealers = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/loadSubDealers", { data: $scope.selected }, function(result){
            $scope.subDealers = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
            $scope.$digest();
        });
      };

      $scope.saveSubDealers = function(dealerSelected) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/saveSubDealers", { data: $scope.selected, subDealer: dealerSelected }, function(result){
          $scope.loadSubDealers();
        });
      };

      $scope.openSubDealerModal = function() {
        if(this.subDealer) {
          $scope.subDealer = this.subDealer
        } else {
          $scope.subDealer = {};
        }
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "app/modals/sub_dealer_modal.html",
          controller: 'SubDealerInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            subDealer: function() {
              return $scope.subDealer;
            }
          }
        });
        modalInstance.result.then((function(dealerSelected) {
          $scope.saveSubDealers(dealerSelected);
        }));
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPagePromos = [];
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.loadDealers();

        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/marketing/loadDealers", { }, function(result){
            $scope.allDealers = jQuery.parseJSON(result).dataset.dealers;
            cfpLoadingBar.complete();
            $scope.$digest();
        });
      };
      return init();
    }
  ]).controller('SubDealerInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'subDealer', function($scope, $rootScope, $modalInstance, logger, subDealer) {
      $scope.clientStatus = ['Aprovado', 'Bloqueado'];
      $scope.subDealer = subDealer;

      $scope.save = function() {
        $modalInstance.close($scope.subDealer);
      };

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();

;