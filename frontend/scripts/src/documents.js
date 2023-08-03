(function () {
  'use strict';
  angular.module('app.internal').controller('DocumentsCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.filtered = [];
      $scope.row = '';
      $scope.selected = {};
      
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

      $scope.search = function() {
        $scope.filtered = $filter('filter')($scope.documents, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        original = angular.copy(this.doc);
        $scope.selected = this.doc;
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filtered = $filter('orderBy')($scope.documents, rowName);
        return $scope.onOrderChange();
      };

      $scope.saveDocument = function() {
        $.post("../backend/application/index.php?rota=/saveDocuments", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadDocuments();
        });
      };

      $scope.cancelEdit = function() {
        $scope.selected = {};
        $scope.loadDocuments();
      };

      $scope.loadDocuments = function() {
        $.post("../backend/application/index.php?rota=/loadDocuments", $scope.session, function(result){
          $scope.documents = jQuery.parseJSON(result).dataset;
          $scope.search();
          $scope.tabindex = 0;
          $scope.$apply();
        });
      };

      $scope.newDocument = function(){
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPage = [];
      init = function() {
        $scope.tabindex = 0;
        $scope.checkValidRoute();
        $scope.loadDocuments();
      };
      return init();
    }
  ]);

})();

;