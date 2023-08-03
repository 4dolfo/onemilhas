(function () {
    'use strict';
    angular.module('app.modal').controller('ModalChangePasswordCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', function($scope, $rootScope, $modalInstance, logger) {
        $scope.changePassword = {};
        $scope.specialChars = ['!', '@', '#', '$', '%', '¨', '&', '*', '.', ':', '^', '~', '?'];

        $scope.cancel = function() {
            $modalInstance.dismiss("cancel");
        };
        
        $scope.ok = function() {
            if(!$scope.changePassword.password1) {
                return logger.logError('Senha deve ser preenchida!');
            }
            if(!$scope.changePassword.password2) {
                return logger.logError('Senha deve ser preenchida!');
            }

            if($scope.changePassword.password1 !== $scope.changePassword.password2) {
                return logger.logError('Senha não conferem!');
            }

            if($scope.changePassword.password1.length > 6) {
                for(var i in $scope.changePassword.password1) {
                    if(!isNaN($scope.changePassword.password1[i])) {
                        $scope.checkNumber = true;
                    }
                    if(isNaN($scope.changePassword.password1[i])) {
                        $scope.checkString = true;
                    }
                    if($scope.specialChars.indexOf($scope.changePassword.password1[i]) != -1) {
                        $scope.checkscpecialChat = true;
                    }
                }
                if(!$scope.checkNumber) {
                    return logger.logError('A senha deve conter numberos!');
                }
                if(!$scope.checkString) {
                    return logger.logError('A senha deve conter letras!');
                }
                if(!$scope.checkscpecialChat) {
                    return logger.logError('A senha deve conter caracteres especiais!');
                }
                if(!$scope.checkNumber || !$scope.checkString || !$scope.checkscpecialChat) {
                    return logger.logError('Verifique requisitos');
                }
            } else {
                return logger.logError('Senha muito curta, ela deve conter pelo menos 7 caracteres!');
            }

            $.post("../backend/application/index.php?rota=/changePassword", { data: $scope.changePassword }, function(result){
                if (JSON.parse(result).message.type == 'E') {
                    logger.logError(JSON.parse(result).message.text);
                } else {
                    $scope.cancel();
                }
            });
        };

      }
    ]);
  })();
  ;
  