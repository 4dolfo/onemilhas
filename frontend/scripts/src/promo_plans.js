(function () {
  'use strict';
  angular.module('app.marketing').controller('PlansPromoCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.searchKeywordsMiles = '';
      $scope.filteredPromos = [];
      $scope.row = '';

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
        $scope.selected = this.promo;
        $scope.selected._startDate = new Date($scope.selected.startDate);
        $scope.selected._endDate = new Date($scope.selected.endDate);
        $.post("../backend/application/index.php?rota=/marketing/loadPlansControl", {data: $scope.selected}, function(result){
          $scope.controlAirline = jQuery.parseJSON(result).dataset.airlines;
          $scope.$digest();
        });
        $scope.tabindex = 1;
      };

      $scope.newPromo = function() {
        $scope.selected = {};
        $scope.selected.airlines = ['LATAM', 'GOL', 'AZUL', 'AVIANCA'];
        $scope.selected.airlinesTypes = ['nacional', 'internacional', 'executivo'];
        $scope.selected.clients = '';
        $scope.controlAirline = {
          LATAM: {
            executivo: {
              configs: []
            },
            internacional: {
              configs: []
            },
            nacional: {
              configs: []
            },
          }, GOL: {
            executivo: {
              configs: []
            },
            internacional: {
              configs: []
            },
            nacional: {
              configs: []
            },
          }, 
          AZUL: {
            executivo: {
              configs: []
            },
            internacional: {
              configs: []
            },
            nacional: {
              configs: []
            },
          }, 
          AVIANCA: {
            executivo: {
              configs: []
            },
            internacional: {
              configs: []
            },
            nacional: {
              configs: []
            },
          }
        }
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

      $scope.savePromo = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "app/modals/modal_confirmation.html",
          controller: 'ModalConfirmationCtrl',
          resolve: {
            header: function() {
              return 'Salvar Promoção';
            }
          }
        });
        modalInstance.result.then((function(f) {
          cfpLoadingBar.start();
          $scope.selected.startDate = $rootScope.formatServerDateTime($scope.selected._startDate);
          $scope.selected.endDate = $rootScope.formatServerDateTime($scope.selected._endDate);
          $.post("../backend/application/index.php?rota=/marketing/savePromoPlans", { data: $scope.selected, controlAirline: $scope.controlAirline }, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadModalData();
            $scope.$apply();
            $scope.tabindex = 0;
            cfpLoadingBar.complete();
          });
        }));
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
        $.post("../backend/application/index.php?rota=/marketing/loadPromoPlans", {}, function(result){
            $scope.promos = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.loadClients = function() {
        $.post("../backend/application/index.php?rota=/loadClientsNames", { searchKeywords: $scope.searchKeywords }, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;
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
        $scope.loadModalData();

        $.post("../backend/application/index.php?rota=/loadSalePlans", $scope.session, function(result){
          $scope.salePlans = jQuery.parseJSON(result).dataset;
          $scope.salePlans.push({ name: 'Valor Fixo' });
        });

        $scope.loadClients();
      };
      return init();
    }
  ]);
})();

;