(function () {
    'use strict';

    angular
        .module('app')
        .controller('ReturnFileCtrl', ReturnFileCtrl);

    ReturnFileCtrl.inject = ['$scope', 'cfpLoadingBar', 'logger', 'FileUploader'];
    function ReturnFileCtrl($scope, cfpLoadingBar, logger, FileUploader) {
        var vm = this;
        $scope.banks = ['BBRASIL', 'BRADESCO', 'SANTANDER'];
        $scope.bank = 'BRADESCO';

        activate();

        function activate() {
            $scope.uploader = new FileUploader();
            $scope.uploader.url = "../backend/application/index.php?rota=/PaymentSlip/readFile";
            $scope.uploader.filters.push({
                name: 'customFilter',
                fn: function (item /*{File|FileLikeObject}*/, options) {
                    return this.queue.length < 1;
                }
            });
            $scope.uploader.onSuccessItem = function(response, status) {
                logger.logSuccess(status.message.text);
            };
            $scope.uploader.onErrorItem = function(response, status) {
                logger.logError(status.message.text);
            };
        }

        $scope.setBank = function(bank) {
            if(bank == 'BBRASIL') {
                $scope.uploader.url = "../backend/application/index.php?rota=/PaymentSlip/readFileBB";
            } else if(bank == 'SANTANDER') {
                $scope.uploader.url = "../backend/application/index.php?rota=/PaymentSlip/readFileSA";
            } else if(bank == 'BRADESCO') {
                $scope.uploader.url = "../backend/application/index.php?rota=/PaymentSlip/readFile";
            }
            console.log(bank);
        };
    }
})();
