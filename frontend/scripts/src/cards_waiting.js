(function () {
  'use strict';
  angular.module('app.table').controller('CardsWaitingCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      $scope.searchKeywords = '';
      $scope.filtered = [];
      $scope.row = '';
      
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPage = $scope.filtered.slice(start, end);
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
        if($scope.bloqued) {
          $scope.bloqued = 'W';
        } else {
          $scope.bloqued = 'N';
        }
        $.post("../backend/application/index.php?rota=/saveProviderCards", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.tabindex = 0;
          $scope.$apply();
          cfpLoadingBar.complete();
          $scope.loadCardsWaiting();
        });
      };

      $scope.savePrograss = function() {
        $.post("../backend/application/index.php?rota=/saveCardProgressWainting", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $.post("../backend/application/index.php?rota=/loadCardsProgressWainting", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
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
        for (var i = 0; $scope.cards.length > i; i++) {
          $scope.cards[i].recovery_password = $scope.decript($scope.cards[i].recovery_password);
          if($scope.cards[i].airline == "TAM"){
            $scope.cards[i].access_password = $scope.decript($scope.cards[i].access_password);
          }
          else{
            $scope.cards[i].access_password = " - ";
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
        $scope.filtered = $filter('filter')($scope.cards, $scope.searchKeywords);
        return $scope.onFilterChange();
      };
      
      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filtered = $filter('orderBy')($scope.cards, rowName);
        return $scope.onOrderChange();
      };
      
      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPagePurchases = [];

      $scope.loadCardsWaiting = function() {
        $.post("../backend/application/index.php?rota=/loadCardsWaiting", $scope.session, function(result){
          $scope.cards = jQuery.parseJSON(result).dataset;
          $scope.cardPassword();
          $scope.search();
          cfpLoadingBar.complete();
          return $scope.select($scope.currentPage);
        });
      };
      
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        cfpLoadingBar.start();
        $scope.loadCardsWaiting();
      };
      return init();
    }
  ]).controller('BloquedCardWaitingModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', 'logger', function($scope, $rootScope, $modal, $log, $filter, logger) {
      
      $scope.saveCardStatus = function(card) {
        $.post("../backend/application/index.php?rota=/saveCardProgressCardWaiting", {hashId: $scope.$parent.$parent.$parent.$parent.$parent.session.hashId, data: card}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.open = function() {
        $scope.card = $scope.$parent.$parent.$parent.selected;
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "BloquedCardWaitingModalCtrl.html",
          controller: 'BloquedCardWaitingInstanceCtrl',
          resolve: {
            card: function() {
              return $scope.card;
            }
          }
        });

        modalInstance.result.then(function(card) {
          $scope.saveCardStatus(card);
        });

      };
    }
  ]).controller('BloquedCardWaitingInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'card', function($scope, $rootScope, $modalInstance, card) {
      $scope.card = card;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.ok = function() {
        $modalInstance.close($scope.card);
      };
    }
  ]);
})();
;
