(function () {
  'use strict';
  angular.module('app.modal').controller('AutorizationCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'main', 'permissions', function($scope, $rootScope, $modalInstance, logger, main, permissions) {

        $scope.permissions = permissions;
        $scope.main = main;

        $scope.check = function() {
            if($scope.permissions.type == 'Comercial') {
                $.post("../backend/application/index.php?rota=/checkAcessCodeComercial", { data: $scope.permissions }, function(result){
                    $scope.response= jQuery.parseJSON(result).dataset;
                    if($scope.response.valid == 'true' || $scope.response.valid == true){
                        $modalInstance.close(true);
                    } else {
                        logger.logError("Dados não conferem!");
                        $scope.$apply();
                    }
                });
            } else {
                $.post("../backend/application/index.php?rota=/checkAcessCode", { data: $scope.permissions }, function(result){
                    $scope.response= jQuery.parseJSON(result).dataset;
                    if($scope.response.valid == 'true' || $scope.response.valid == true){
                        $modalInstance.close(true);
                    } else {
                        logger.logError("Dados não conferem!");
                        $scope.$apply();
                    }
                });
            }
        };

        $scope.cancel = function() {
            $modalInstance.dismiss("cancel");
        };
    }
  ]);
})();
;
