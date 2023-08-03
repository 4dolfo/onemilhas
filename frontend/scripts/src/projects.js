(function () {
  'use strict';
  angular.module('app.purchase').controller('ProjectsCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;

      $scope.loadData = function() {
        cfpLoadingBar.start();
        cfpLoadingBar.complete();
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.loadData();

      };

      return init();

    }
  ]);
})();
;