(function () {
    'use strict';
    angular.module('app.modal').controller('ModalCardDataCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', 'cards', function($scope, $rootScope, $modalInstance, logger, cards) {
          
        $.post("../backend/application/index.php?rota=/loadCardsData", { sale: cards }, function(result){
            $scope.cards = jQuery.parseJSON(result).dataset;
            if($scope.cards.recovery_password) {
                $scope.cards.recovery_password = $scope.decript($scope.cards.recovery_password);
            }
            if($scope.cards.access_password) {
                $scope.cards.access_password = $scope.decript($scope.cards.access_password);
            }
            $scope.$digest();
        });

        $scope.cancel = function() {
            $modalInstance.dismiss("cancel");
        };
        
        $scope.decript = function(code){
            var data = code.split('320AB');
            var finaly = '';
            for (var j = 0; data.length > j; j++) {
              finaly = finaly + (String.fromCharCode(data[j] / 320));
            }
            return finaly;
        };

      }
    ]);
  })();
  ;
  