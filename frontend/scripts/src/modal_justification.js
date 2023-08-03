(function () {
    'use strict';
    angular.module('app.purchase').controller('ModalJustificationCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', 'header', function($scope, $rootScope, $modalInstance, logger, header) {
  
        $scope.header = header;
        $scope.description = '';

        $scope.cancel = function() {
          $modalInstance.dismiss("cancel");
        };

        $scope.ok = function() {
            if(!$scope.description) {
                return logger.logError("Descrição é obrigatoria!");
            }

            if($scope.description == '') {
                return logger.logError("Descrição é obrigatoria!");
            }
            $modalInstance.close($scope.description);
        };
      }
    ]);
  })();
  ;
  