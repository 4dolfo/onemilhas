(function () {
    'use strict';
    angular.module('app.modal').controller('ModalCreditAnalysisCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', 'partnerObject', function($scope, $rootScope, $modalInstance, logger, partnerObject) {
  
        $scope.partner = partnerObject;
        $scope.creditsAnalysis = [];

        $scope.loadCreditAnalysis = function() {
            $.post("../backend/application/index.php?rota=/loadCreditAnalysisHistory", { data: $scope.partner }, function(result){
                $scope.creditsAnalysis = jQuery.parseJSON(result).dataset;
                $scope.$digest();
            });
        };

        $scope.cancel = function() {
          $modalInstance.dismiss("cancel");
        };

        $scope.removeCreditAnalysis = function(creditAnalysis) {
            $.post("../backend/application/index.php?rota=/removeCreditAnalysisHistory", { data: creditAnalysis }, function(result){
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $scope.loadCreditAnalysis();
            });
        };

        $scope.saveCreditAnalysis = function() {
            $.post("../backend/application/index.php?rota=/saveCreditAnalysisHistory", { client: $scope.partner, data: $scope.partner }, function(result){
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $scope.partner.creditAnalysis = '';
                $scope.partner.registrationCodeCheck = '';
                $scope.partner.adressCheck = '';
                $scope.partner.creditDescription = '';
                $scope.loadCreditAnalysis();
            });
        };

        return $scope.loadCreditAnalysis();

      }
    ]);
  })();
  ;
  