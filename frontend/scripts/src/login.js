(function () {
  'use strict';
  angular.module('app.purchase').controller('LoginCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', '$routeParams', '$location', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, $routeParams, $location, cfpLoadingBar, logger) {
      var init;
      var login_form;
      $scope.login = function() {
        if ($scope.form_login == undefined) {
          login_form = $scope.form_lock;
        } else {
          login_form = $scope.form_login;
        }

        if (login_form.$valid) {
          cfpLoadingBar.start();
          if ($scope.data == undefined) {
            logger.logError('Senha deve ser informada');
          } else if ($scope.data.email == undefined) {
            $scope.data.email = $scope.main.email;
          }
          $scope.data.hashId = '5a9b0fde419b522a8b9baede73811369';
          $scope.data.url_parameters = $scope.url_parameters;

          var response = $.post("../backend/application/index.php?rota=/login", $scope.data, function(result){
            if (jQuery.parseJSON(result).message.type == 'E') {
              logger.logError(jQuery.parseJSON(result).message.text);
            } else {
              $scope.dataset = jQuery.parseJSON(result).dataset[0];     
              $scope.main.name = $scope.dataset.acessName;
              $scope.main.id = $scope.dataset.id;
              $scope.main.email = $scope.dataset.email;
              $scope.main.adress = $scope.dataset.adress;
              $scope.main.phoneNumber = $scope.dataset.phoneNumber;
              $scope.main.city = $scope.dataset.city;
              $scope.main.sales = $scope.dataset.sales;
              $scope.main.purchases = $scope.dataset.purchases;
              $scope.main.image = 'images/'+$scope.dataset.id+'.jpg';
              $scope.main.isMaster = ($scope.dataset.is_master == 'true');
              $scope.session.hashId = $scope.dataset.hashId;

              $.ajaxSetup({
                  headers: { 'hashId': response.getResponseHeader('hashId') }
              });

              if($scope.dataset.forceChangesPassword) {
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/change_password.html",
                  controller: 'ModalChangePasswordCtrl',
                  backdrop  : 'static',
                  keyboard  : false,               
                  resolve: {
                  }
                });
                return;
              }

              // var navigator = angular.copy(window.navigator);
              // $.post("../backend/application/index.php?rota=/navigatorInfo", { data: navigator }, function(result){
              //   console.log(window.navigator);
              // });

              var permissions = JSON.parse(window.atob(response.getResponseHeader('permissions')));
              

              $scope.main.purchase = permissions.purchase;
              $scope.main.wizardPurchase = permissions.wizardPurchase;
              $scope.main.sale = permissions.sale;
              $scope.main.wizardSale = permissions.wizardSale;
              $scope.main.milesBench = permissions.milesBench;
              $scope.main.financial = permissions.financial;
              $scope.main.creditCard = permissions.creditCard;
              $scope.main.users = permissions.users;
              $scope.main.changeSale = permissions.changeSale;
              $scope.main.changeMiles = permissions.changeMiles;
              $scope.main.commercial = permissions.commercial;
              $scope.main.permission = permissions.permission;
              $scope.main.pagseguro = permissions.pagseguro;
              $scope.main.internRefund = permissions.internRefund;
              $scope.main.internCommercial = permissions.internCommercial;
              $scope.main.dealer = permissions.dealer;
              $scope.main.humanResources = permissions.humanResources;
              $scope.main.client = permissions.client;
              $scope.main.salePlansEdit = permissions.salePlansEdit;
              $scope.main.conference = permissions.conference;

              $scope.main.onlineOnlineOrder = permissions.onlineOnlineOrder;
              $scope.main.onlineBalanceOrder = permissions.onlineBalanceOrder;
              $scope.main.onlineCardsInUse = permissions.onlineCardsInUse;

              $scope.main.purchaseProvider = permissions.purchaseProvider;
              $scope.main.purchasePaymentPruchase = permissions.purchasePaymentPruchase;
              $scope.main.purchaseEndPruchase = permissions.purchaseEndPruchase;
              $scope.main.purchasePruchases = permissions.purchasePruchases;
              $scope.main.purchaseCardsPendency = permissions.purchaseCardsPendency;

              $scope.main.saleClients = permissions.saleClients;
              $scope.main.saleBalanceClients = permissions.saleBalanceClients;
              $scope.main.saleFutureBoardings = permissions.saleFutureBoardings;
              $scope.main.saleRefundCancel = permissions.saleRefundCancel;
              $scope.main.saleRevertRefund = permissions.saleRevertRefund;
              $scope.main.wizarSaleEvent = permissions.wizardSaleEvent;
              $rootScope.notifications = [];

              window.location = document.getElementById('submit_login').href;

              $rootScope.connectToSocket(response.getResponseHeader('hashId'));

              // var modalInstance;
              // modalInstance = $modal.open({
              //   templateUrl: "app/modals/system_notification.html",
              //   controller: 'SystemNotificationCtrl',
              //   periods: $scope.periods,
              //   size: 'lg',
              //   resolve: {
              //     notification: function() {
              //       return { text: 'Servidor sendo utilizado para testes exclusivos. Favor não utilizar!', description: 'Em caso de duvidas, procurar o Arthur' };
              //     },
              //     header: function() {
              //       return 'Aviso';
              //     }
              //   }
              // });

            }
            cfpLoadingBar.complete();
          });
        }
      };

      init = function() {
        $scope.url_parameters = window.location.search;
        if($scope.url_parameters.indexOf('?') > -1) {
          $scope.url_parameters = $scope.url_parameters.substr(1);
        }
        return true;
      };

      return init();
  }]);
})();
;
