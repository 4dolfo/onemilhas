(function () {
  'use strict';
  angular.module('app.marketing').controller('ModalPromoCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', 'FileUploader', function($scope, $rootScope, $filter, cfpLoadingBar, logger, FileUploader) {
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

      $scope.saveModalPromo = function() {
        cfpLoadingBar.start();
        $scope.selected.startDate = $rootScope.formatServerDateTime($scope.selected._startDate);
        $scope.selected.endDate = $rootScope.formatServerDateTime($scope.selected._endDate);

        var file = [];
        for(var j in $scope.uploader.queue) {
          file.push($scope.uploader.queue[j].file.name);
        }

        $.post("../backend/application/index.php?rota=/marketing/saveModalPromo", { data: $scope.selected, file: file }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadModalData();
          $scope.$apply();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
        });
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
        $.post("../backend/application/index.php?rota=/marketing/loadModalPromo", {}, function(result){
            $scope.promos = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
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

        $scope.uploader = new FileUploader();
        $scope.uploader.url = "../backend/application/index.php?rota=/saveFile";
        $scope.uploader.autoUpload = true;
        $scope.uploader.filters.push({
            name: 'customFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 10;
            }
        });        

      };
      return init();
    }
  ]);
})();

;