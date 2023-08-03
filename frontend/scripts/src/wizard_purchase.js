(function () {
  'use strict';
  angular.module('app.wizardpurchase', ['ui.utils.masks']).controller('WizardPurchaseCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var providerSelected;

      $scope.accountType = [
      { type: 'Conta Corrente' },
      { type: 'Conta Poupança' }];
      
      $scope.nextWizardStage = function() {
        $scope.tabindex = $scope.tabindex + 1;
        return $scope.tabindex;
      };

      $scope.priorWizardStage = function() {
        $scope.tabindex = $scope.tabindex - 1;
        return $scope.tabindex;
      };

      $scope.WizardStageProgress = function() {
        return (($scope.tabindex + 1) * 20);
      };

      $scope.validateProvidersFields = function() {
        if ($scope.wpurchase_formprovider.$valid) {
          $scope.wpurchase.contract_due_date = new Date();
          $scope.wpurchase.contract_due_date.setDate($scope.wpurchase.contract_due_date.getDate() + 120);
          $scope.wpurchase.cityfullname = $scope.wpurchase.city + ', ' + $scope.wpurchase.state;
          // if ($scope.wpurchase.registrationCode.length <= 11) {
          //   if (!$rootScope.ValidaCPF($scope.wpurchase.registrationCode)) {
          //     logger.logError('CPF Inválido');
          //     return false;
          //   }
          // } else {
          //   if (!$rootScope.ValidaCNPJ($scope.wpurchase.registrationCode)) {
          //     logger.logError('CNPJ Inválido');
          //     return false;
          //   }
          // }
          $.post("../backend/application/index.php?rota=/loadPurchaseHistory", {hashId: $scope.session.hashId, data: $scope.wpurchase}, function(result){
            $scope.purchaseHistory = jQuery.parseJSON(result).dataset;
            $scope.$apply();
          });
          $scope.wpurchase.paymentMethod = 'prepaid';
          $('#purchase_miles').number( true, 0, ',', '.');
          // $("#purchase_miles").maskMoney({thousands:'.', precision: 0});
          // $('#cost_per_thousand').number( true, 2, ',', '.');
          $("#cost_per_thousand").maskMoney({thousands:'.',decimal:',', precision: 2});
          $('#total_cost').number( true, 2, ',', '.');
          // $("#total_cost").maskMoney({thousands:'.', decimal:',', precision: 2});
          return $scope.nextWizardStage();
        }
      };

      $scope.findDate = function(date){
        return new Date(date);
      };

      $scope.validPurchaseFields = function() {
        if($scope.wpurchase.paymentMethod != 'after_use') {
          if(!$scope.wpurchase.pay_date || $scope.wpurchase.pay_date == "" || $scope.wpurchase.pay_date == 'Invalid Date') {
            return logger.logError('Data de pagamento obrigatoria');
          }
        }
        if($scope.wpurchase.airline == 'LATAM') {
          if($scope.wpurchase.onlyInter == null || $scope.wpurchase.onlyInter == 'null') {
            return logger.logError('Tipo de emissão obrigatorio!');
          }
        }
        if ($scope.wpurchase_formpurchase.$valid) {
          return $scope.tabindex = 3;
        }
      };

      $scope.validCardsFields = function() {
        if ($scope.wpurchase_formcard.$valid) {
          return $scope.nextWizardStage();
        }
      };

      $scope.fillProvidersFields = function() {
        if (!($scope.wpurchase == undefined)) {
          providerSelected = $scope.provider_index($scope.wpurchase.registrationCode);
          if (!(providerSelected == undefined)) {
            if ($scope.providers[providerSelected].status == 'Bloqueado') {
              logger.logError('Fornecedor bloqueado para compra: ' +  $scope.providers[providerSelected].blockreason);
              return false;
            } else {
              $scope.wpurchase.provider_id = $scope.providers[providerSelected].id;
              $scope.wpurchase.name = $scope.providers[providerSelected].name;
              $scope.wpurchase.registrationCode = $scope.providers[providerSelected].registrationCode;
              $scope.wpurchase.adress = $scope.providers[providerSelected].adress;
              $scope.wpurchase.state = $scope.providers[providerSelected].state;
              $scope.wpurchase.city = $scope.providers[providerSelected].city;
              $scope.wpurchase.email = $scope.providers[providerSelected].email;
              $scope.wpurchase.phoneNumber = $scope.providers[providerSelected].phoneNumber;
              $scope.wpurchase.phoneNumber2 = $scope.providers[providerSelected].phoneNumber2;
              $scope.wpurchase.phoneNumber3 = $scope.providers[providerSelected].phoneNumber3;
              $scope.wpurchase.bank = $scope.providers[providerSelected].bank;
              $scope.wpurchase.agency = $scope.providers[providerSelected].agency;
              $scope.wpurchase.account = $scope.providers[providerSelected].account;
              $scope.wpurchase.card_number = $scope.providers[providerSelected].registrationCode;
              $scope.wpurchase.paymentType = $scope.providers[providerSelected].paymentType;
              $scope.wpurchase.description = $scope.providers[providerSelected].description;
              $scope.wpurchase.phoneNumberAirline = $scope.providers[providerSelected].phoneNumberAirline;
              $scope.wpurchase.celNumberAirline = $scope.providers[providerSelected].celNumberAirline;
            }
          }
        }
        return $scope.nextWizardStage();
      };

      $scope.decript = function(code){
        var data = code.split('320AB');
        var finaly = '';
        for (var j = 0; data.length > j; j++) {
          finaly = finaly + (String.fromCharCode(data[j] / 320));
        }
        return finaly;
      };

      $scope.ecript = function(code){
        if(code != undefined){
          var data = code.split('');
          var finaly = '';
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + (data[j].charCodeAt(0) * 320) + '320AB';
          }
          return finaly;
        }
      };

      $scope.fillCardsFields = function() {
        if (!$scope.wpurchase.airline == '') {
          $.post("../backend/application/index.php?rota=/loadProviderAirlineCard", {hashId: $scope.session.hashId, name: $scope.wpurchase.name, airline: $scope.wpurchase.airline}, function(result){
            $scope.card = jQuery.parseJSON(result).dataset;
            if (!($scope.card[0] == undefined)) {
              $scope.wpurchase.card_number = $scope.card[0].card_number;
              $scope.wpurchase.access_password = $scope.decript($scope.card[0].access_password);
              $scope.wpurchase.access_id = $scope.card[0].access_id;
              $scope.wpurchase.recovery_password = $scope.decript($scope.card[0].recovery_password);
            }
          });
        }
      };

      $scope.getTotalPurchased = function() {
        var total = 0;
        if($scope.purchaseHistory != undefined) {
          if($scope.purchaseHistory.length > 0){
            for(var i in $scope.purchaseHistory){
              total += $scope.purchaseHistory[i].leftover;
            }
          }
        }
        return total;
      };

      $scope.provider_index = function(providerName) {
        if (!providerName=='') {
          for (var i=0;i<$scope.providers.length;i++) {
            if ($scope.providers[i].registrationCode==providerName) {
              return i;
            }
          }
        }
      };

      $scope.setTotalCost = function() {
        var purchase_miles = parseFloat($scope.wpurchase.purchase_miles);
        $scope.wpurchase.total_cost = parseFloat(($scope.wpurchase.cost_per_thousand * ( purchase_miles / 1000 )).toFixed(2));
      };

      $scope.setCosPerThousand = function() {
        $scope.wpurchase.cost_per_thousand = $scope.wpurchase.total_cost / ($scope.wpurchase.purchase_miles/1000);
      };

      $scope.savePurchase = function() {
        $scope.wpurchase._miles_due_date = $rootScope.formatServerDate($scope.wpurchase.miles_due_date);
        $scope.wpurchase._contract_due_date = $rootScope.formatServerDate($scope.wpurchase.contract_due_date);
        if($scope.wpurchase.pay_date !== "" && $scope.wpurchase.pay_date != 'Invalid Date') {
          $scope.wpurchase._pay_date = $rootScope.formatServerDate($scope.wpurchase.pay_date);
        }
        $scope.wpurchase.accessPassword = $scope.ecript($scope.wpurchase.accessPassword);

        for(var i in $scope.milesDueDate) {
          if($scope.milesDueDate[i].dueDate !== "" && $scope.milesDueDate[i].dueDate != 'Invalid Date') {
            $scope.milesDueDate[i]._dueDate = $rootScope.formatServerDate($scope.milesDueDate[i].dueDate);
          }
        }

        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/savePurchase", {hashId: $scope.session.hashId, data: $scope.wpurchase, milesDivisions: $scope.milesDueDate}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
        cfpLoadingBar.complete();
      };

      $scope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
        return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
      };

      $scope.addDueDate = function() {
        if($scope.milesDueDate.length < 3) {
          $scope.milesDueDate.push({
            miles: 0,
            dueDate: new Date()
          })
        }
      };

      $scope.removeDueDate = function() {
        $scope.milesDueDate.pop();
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;

        $scope.milesDueDate = [];
        $scope.wpurchase = {
          onlyInter: 'null'
        };

        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadAirline", $scope.session, function(result){
            $scope.airlines = jQuery.parseJSON(result).dataset;
        });

        $.post("../backend/application/index.php?rota=/loadState", $scope.session, function(result){
            $scope.states = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
        });

        $('#wpurchaseairline').on('blur', function(obj, datum) {
          $scope.fillCardsFields();
        });

        $('#wpurchasestate').on('blur', function(obj, datum) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadCity", {hashId: $scope.session.hashId, state: $scope.wpurchase.state}, function(result){
            $scope.cities = jQuery.parseJSON(result).dataset;
          });
        });

      };

      $scope.searchProvider = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadProvider", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.wpurchase.registrationCode }, function(result){
          $scope.providers = jQuery.parseJSON(result).dataset.providers;
          $scope.$digest();
          cfpLoadingBar.complete();
        });
      };

      return init();
    }
  ]);
})();
;
