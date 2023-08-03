(function () {
    'use strict';
    angular.module('app.modal').controller('ModalConfirmationCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', 'header', function($scope, $rootScope, $modalInstance, logger, header) {
  
        $scope.header = header;

        $scope.cancel = function() {
          $modalInstance.dismiss("cancel");
        };

        $scope.ok = function() {
            $modalInstance.close(true);
        };
      }
    ]);
  })();
  ;
  