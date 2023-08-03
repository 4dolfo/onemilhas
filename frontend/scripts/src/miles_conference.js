(function () {
  'use strict';
  angular.module('app.table').controller('MilesConferenceCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      $scope.searchKeywords = '';
      $scope.filteredMilesConference = [];
      $scope.row = '';
      
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageMilesConference = $scope.filteredMilesConference.slice(start, end);
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

      $scope.orderMiles = function(mile) {
        switch (mile.priority) {
          case '-1':
            return "label label-success";
          case '0':
            return "label label-danger";
          case '1':
            return "label label-warning";
          default:
            return "";
        }
      };
      
      $scope.search = function() {
        $scope.filteredMilesConference = $filter('filter')($scope.milesConference, $scope.searchKeywords);
        return $scope.onFilterChange();
      };
      
      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredMilesConference = $filter('orderBy')($scope.milesConference, rowName);
        return $scope.onOrderChange();
      };
      
      $scope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
        return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
      };
      
      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageMilesConference = [];

      $scope.loadMilesConference = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadMilesConference", { filter: $scope.filter }, function(result){
            $scope.milesConference = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.checkMiles = function(milebench) {
        $.post("../backend/application/index.php?rota=/saveMilesCheck", { data: milebench, filter: $scope.filter }, function(result){
          logger.logSuccess('Salvo com sucesso');
          $scope.loadMilesConference();
        });
      };

      $scope.findDate = function(date) {
        return new Date(date);
      };

      $scope.setSelected = function() {
        $scope.selected = angular.copy(this.milebench);
        $rootScope.$emit('openMilesConferenceModal', $scope.selected);
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.loadMilesConference();

        $scope.tabIndex = 0;
        $scope.selected = { cards_id: 0 };
        $rootScope.modalOpen = false;
      };

      $scope.openSearchModal = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "MilesConferenceModalCtrl.html",
          controller: 'MilesConferenceInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter != undefined) {
            filter._dateFrom = $rootScope.formatServerDate(filter.dateFrom);
            filter._dateTo = $rootScope.formatServerDate(filter.dateTo);
          }

          $scope.filter = filter;
          $scope.loadMilesConference();
        }), function() {
          $console.log("Modal dismissed at: " + new Date());
        });
      };

      return init();
    }

  ]).controller('ShowCardMilesConferenceCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {

      $rootScope.modalOpen = false;

      $rootScope.$on('openMilesConferenceModal', function(event, args) {
        event.stopPropagation();
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });


      $scope.open = function(args) {
        $scope.selected = args;
        $scope.selected.cardNumber = '000000';
        var modalInstance;
        $.post("../backend/application/index.php?rota=/loadCardsData", {hashId: $scope.session.hashId, sale: $scope.selected}, function(result){
          $scope.cards = jQuery.parseJSON(result).dataset;

          $scope.decript = function(code){
            var data = code.split('320AB');
            var finaly = '';
            for (var j = 0; data.length > j; j++) {
              finaly = finaly + (String.fromCharCode(data[j] / 320));
            }
            return finaly;
          };

          $scope.cards.recovery_password = $scope.decript($scope.cards.recovery_password);
          $scope.cards.access_password = $scope.decript($scope.cards.access_password);
          
          modalInstance = $modal.open({
            templateUrl: "ShowCardMilesConference.html",
            controller: 'ShowCardMilesConferenceInstanceCtrl',
            resolve: {
              cards: function() {
                return $scope.cards;
              },
              selected: function() {
                return $scope.selected;
              }
            }
          });
          
          modalInstance.result.then(function(resolve) {
            $rootScope.modalOpen = false;
          }, function() {
            $rootScope.modalOpen = false;
          });

        });
      };
    }
  ]).controller('ShowCardMilesConferenceInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'cards', 'selected', function($scope, $rootScope, $modalInstance, cards, selected) {
      $scope.cards = cards;
      $scope.selected = selected;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('MilesConferenceInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {

      $scope.filter = filter;

      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;
