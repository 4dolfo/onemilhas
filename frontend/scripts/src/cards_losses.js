(function () {
  'use strict';
  angular.module('app.table').controller('CardsLossesCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      $scope.searchKeywords = '';
      $scope.filtered = [];
      $scope.row = '';
      
      $scope.select = function(page) {
        $scope.loadCardsBloqued();
      };
      
      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };
      
      $scope.onNumPerPageChange = function() {
        $scope.loadCardsBloqued();
      };
      
      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.findColor = function(purchase){
        if(purchase.payment_status == 'A')
          return "#FBA1A1";
        else
          return;
      };

      $scope.setSelected = function() {
        $scope.selected = {};
        $scope.selected = this.cards;
        $scope.tabindex = 1;
        
      };
      
      $scope.saveStatus = function(){
        cfpLoadingBar.start();
        $scope.selected.recovery_password = $scope.ecript($scope.selected.recovery_password);
        $scope.selected.access_password = $scope.ecript($scope.selected.access_password);
        if($scope.selected.bloqued) {
          $scope.selected.bloqued = 'L';
        } else {
          $scope.selected.bloqued = 'N';
        }
        $.post("../backend/application/index.php?rota=/saveProviderCards", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.tabindex = 0;
          $scope.$apply();
          cfpLoadingBar.complete();
          $scope.loadCardsBloqued();
        });
      };

      $scope.savePrograss = function() {
        $.post("../backend/application/index.php?rota=/saveCardProgress", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $.post("../backend/application/index.php?rota=/loadCardsProgress", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            $scope.selected.progress = jQuery.parseJSON(result).dataset;
            $scope.$apply();
          });
        });
      };

      $scope.findDate = function(date) {
        return new Date(date);
      };

      $scope.ecript = function(code){
        if(code != undefined){
          var data = code.split('');
          var finaly = '';
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + (data[j].charCodeAt(0) * 320) + '320AB';
          }
          return finaly;
        }
      };

      $scope.cardPassword = function() {
        for (var i = 0; $scope.cardsBloqued.length > i; i++) {
          $scope.cardsBloqued[i].recovery_password = $scope.decript($scope.cardsBloqued[i].recovery_password);
          if($scope.cardsBloqued[i].airline == "LATAM"){
            $scope.cardsBloqued[i].access_password = $scope.decript($scope.cardsBloqued[i].access_password);
          }
          else{
            $scope.cardsBloqued[i].access_password = " - ";
          }
        }
        $scope.$apply();
      };

      $scope.decript = function(code){
        if(code != null && code != undefined){
          var data = code.split('320AB');
          var finaly = '';
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + (String.fromCharCode(data[j] / 320));
          }
          return finaly;
        }
      };

      $scope.back = function() {
        $scope.tabindex = 0;
      };

      $scope.setTotalCost = function() {
        $scope.selected.totalCost = ($scope.selected.purchaseMiles/1000) * $scope.selected.costPerThousand;
        return $scope.$apply();
      };
      
      $scope.search = function() {
        $scope.loadCardsBloqued();
      };
      
      $scope.order = function(rowName) {
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadCardsBloqued();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadCardsBloqued();
      };
      
      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[1];
      $scope.currentPage = 1;
      $scope.currentPagePurchases = [];

      $scope.loadCardsBloqued = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadCardsLosses", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown }, function(result){
          $scope.cardsBloqued = jQuery.parseJSON(result).dataset.cardsBloqued;
          $scope.totalData = jQuery.parseJSON(result).dataset.total;
          $scope.cardPassword();
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };
      
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.loadCardsBloqued();
      };
      return init();
    }
  ]);
})();
;
