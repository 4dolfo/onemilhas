(function () {
  'use strict';
  angular.module('app.table').controller('PurchaseCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      $scope.searchKeywords = '';
      $scope.filteredPurchases = [];
      $scope.row = '';
      
      $scope.select = function(page) {
        // var end, start;
        // start = (page - 1) * $scope.numPerPage;
        // end = start + $scope.numPerPage;
        $scope.loadPurchases();
        // return $scope.currentPagePurchases = $scope.filteredPurchases.slice(start, end);
      };
      
      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };
      
      $scope.onNumPerPageChange = function() {
        $scope.currentPage = 1;
        $scope.select(1);
        // $scope.loadPurchases();
      };
      
      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.print = function(){
        $.post("../backend/application/index.php?rota=/loadPurchasesToOperations", { order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter, searchKeywords: $scope.searchKeywords }, function (result) {
          $scope.purchasesReport = jQuery.parseJSON(result).dataset.purchases;
        
          var data = [['PG', 'Cadastro', 'Data Comp.', 'Cliente', 'Nº  Cartão', 'Senha', 'CARTÃO', 'CPF', 'Qtd de Milhas', 'Milhas Utilizadas', 'REAL', 'Valor p/ 1.000 pts', 'Valor Total', 'Situação da Análise', 'OBS.', 'Telefone Contato', 'E-mail Contato', 'EXPIRAR', 'Companhia']];

          for(let i in $scope.purchasesReport) {
            data.push([
              '',
              $scope.purchasesReport[i].provider_status,
              $filter('date')(new Date($scope.purchasesReport[i].purchase_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.purchasesReport[i].providerName,
              $scope.purchasesReport[i].card_number,
              $scope.decript($scope.purchasesReport[i].recovery_password),
              '',
              $scope.purchasesReport[i].registration_code,
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles, 0),
              $rootScope.formatNumber($scope.purchasesReport[i].leftover, 0),
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles, 0),
              $scope.purchasesReport[i].cost_per_thousand,
              $scope.purchasesReport[i].total_cost,
              '',
              '',
              $scope.purchasesReport[i].phone_number+' / '+$scope.purchasesReport[i].phone_number2,
              $scope.purchasesReport[i].email,
              $filter('date')(new Date($scope.purchasesReport[i].miles_due_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.purchasesReport[i].airline
            ]);
          }

          var csvContent = "data:text/csv;charset=utf-8,";
          var dataString;
          data.forEach(function (infoArray, index) {
            dataString = infoArray.join(";");
            csvContent += index < data.length ? dataString + "\n" : dataString;
          });

          var encodedUri = encodeURI(csvContent);
          var link = document.createElement("a");
          link.setAttribute("href", encodedUri);
          link.setAttribute("download", "compras.csv");
          document.body.appendChild(link);

          link.click();
        })
      };

      $scope.findDate = function(date){
        return new Date(date);
      };

      $scope.setSelected = function() {
        var original = this.purchase;
        $scope.selected = original;
        $scope.cardPassword();
        $scope.isTable = false;
        $scope.airlineShowFields = (this.purchase.airline == 'TAM' || this.purchase.airline == 'LATAM');
        $scope.purchaseDate = new Date($scope.selected.purchaseDate + 'T12:00:00Z');
        $scope.milesDueDateDate = new Date($scope.selected.milesDueDate + 'T12:00:00Z');
        $scope.selected.isPriority = ($scope.selected.isPriority == 'true');
        $('#purchasemiles').number(true,0,',','.');
        $('#milesLoesses').number(true,0,',','.');
        // $('#purchasecostperthousand').number(true,2,',','.');
        $("#purchasecostperthousand").maskMoney({thousands:'.',decimal:',', precision: 2});
        $('#purchasetotalcost').number(true,2,',','.');
        $.post("../backend/application/index.php?rota=/loadHistoric", {hashId: $scope.session.hashId, data: $scope.selected, type: 'PURCHASE'}, function(result){
          $scope.PurchaseHistory = jQuery.parseJSON(result).dataset;
        });
        $.post("../backend/application/index.php?rota=/loadCardsData", {hashId: $scope.session.hashId, sale: $scope.selected}, function(result){
          $scope.cards = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });

        $scope.MilesDueDate = [];
        $.post("../backend/application/index.php?rota=/loadMilesDueDate", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.MilesDueDate = jQuery.parseJSON(result).dataset;
          for(var i in $scope.MilesDueDate) {
            $scope.MilesDueDate[i]._milesDueDate = new Date($scope.MilesDueDate[i].milesDueDate);
          }
          $scope.$digest();
        });

        $scope.selected._leftover = $scope.selected.leftover;
        $scope.selected._purchaseMiles = $scope.selected.purchaseMiles;

        if($scope.selected.bloqued == "Y")
          $scope.selected.is_bloqued = true;
        else
          $scope.selected.is_bloqued = false;
        return true;
      };
      
      $scope.saveStatus = function(){
        if($scope.selected.airline == 'LATAM') {
          if($scope.selected.onlyInter == null || $scope.selected.onlyInter == 'null') {
            return logger.logError('Tipo de emissão obrigatorio!');
          }
        }
        cfpLoadingBar.start();
        if($scope.selected.is_bloqued !== true){
          $scope.selected.bloqued = "N";
        }
        else{
          $scope.selected.bloqued = "Y";
        }
        $scope.selected._milesDueDate = $rootScope.formatServerDate($scope.milesDueDateDate);
        $scope.selected.recoveryPassword = $scope.ecript($scope.selected.recoveryPassword);
        $scope.selected.accessPassword = $scope.ecript($scope.selected.accessPassword);
        $.post("../backend/application/index.php?rota=/saveCardStatus", { data: $scope.selected }, function(result){
          if (jQuery.parseJSON(result).message.type == 'S'){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          } else {
            logger.logError(jQuery.parseJSON(result).message.text);
          }
          $scope.toggleFormTable();
          $scope.loadPurchases();
        });
      };

      $scope.cardPassword = function() {
        $scope.selected.recoveryPassword = $scope.decript($scope.selected.recoveryPassword);
        $scope.selected.accessPassword = $scope.decript($scope.selected.accessPassword);
      };

      $scope.intencionToSave = function() {
        if($scope.selected._leftover != $scope.selected.leftover || $scope.selected._purchaseMiles != $scope.selected.purchaseMiles || $scope.selected.removeFromMilesbench == true) {
          $scope.openPurchaseLogModal();
        } else {
          $scope.saveStatus();
        }
      };

      $scope.decript = function(code){
        var data
        var finaly = '';
        if(code != null) {
          data = code.split('320AB');
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + (String.fromCharCode(data[j] / 320));
          }
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

      $scope.setCostPerThousand = function() {
        $scope.selected.costPerThousand = ($scope.selected.totalCost / (($scope.selected.purchaseMiles - $scope.selected.losses) / 1000)).toFixed(2);;
        $scope.apply();
      };

      $scope.setTotalCost = function() {
        if($scope.selected._purchaseMiles != $scope.selected.purchaseMiles) {
          $scope.selected.leftover = $scope.selected._leftover - ($scope.selected._purchaseMiles - parseInt($scope.selected.purchaseMiles));
          if($scope.selected.leftover < 0) {
            $scope.selected.leftover = 0;
          }
        }
        $scope.selected.totalCost = ( parseInt($scope.selected.purchaseMiles) / 1000) * $scope.selected.costPerThousand;
      };

      $scope.toggleFormTable = function() {
        $scope.selected.recoveryPassword = $scope.ecript($scope.selected.recoveryPassword);
        $scope.selected.accessPassword = $scope.ecript($scope.selected.accessPassword);
        $scope.isTable = !$scope.isTable;
        return $scope.isTable;
      };
      
      $scope.search = function() {
        $scope.loadPurchases();
        // $scope.filteredPurchases = $filter('filter')($scope.purchases, $scope.searchKeywords);
        // return $scope.onFilterChange();
      };
      
      $scope.order = function(rowName) {
        // if ($scope.row === rowName) {
        //   return;
        // }
        // $scope.row = rowName;
        // $scope.filteredPurchases = $filter('orderBy')($scope.purchases, rowName);
        // return $scope.onOrderChange();
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadPurchases();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadPurchases();
      };
      
      $scope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
        return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
      };
      
      $scope.numPerPageOpt = [10, 30, 50, 100, 1000];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPagePurchases = [];
      
      $scope.loadPurchases = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadPurchase", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function(result){
          $scope.purchases = jQuery.parseJSON(result).dataset.purchases;
          $scope.totalData = jQuery.parseJSON(result).dataset.total;
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.isTable = true;
        $rootScope.modalOpen = false;
        $scope.loadPurchases();
        $.post("../backend/application/index.php?rota=/loadInternalCards", $scope.session, function(result){
          $scope.internalCards = jQuery.parseJSON(result).dataset;
        });
        $scope.totalData = 0;
      };

      $scope.openSearchModal = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "Purchase.html",
          controller: 'PurchaseModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter != undefined) {
            filter._purchaseDateFrom = $rootScope.formatServerDate(filter.purchaseDateFrom);
            filter._purchaseDateTo = $rootScope.formatServerDate(filter.purchaseDateTo);
            filter._dueDateFrom = $rootScope.formatServerDate(filter.dueDateFrom);
            filter._dueDateTo = $rootScope.formatServerDate(filter.dueDateTo);
          }

          $scope.filter = filter;
          $scope.loadPurchases();
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };

      $scope.removePurchase = function(purchase) {
        $.post("../backend/application/index.php?rota=/removePurchase", { data: purchase }, function(result){
          if (jQuery.parseJSON(result).message.type == 'S'){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          } else {
            logger.logError(jQuery.parseJSON(result).message.text);
          }
          $scope.loadPurchases();
        });
      };

      $scope.openPurchaseLogModal = function(args) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "PurchaseLogModalCtrl.html",
          controller: 'PurchaseLogInstanceCtrl',
          resolve: {
          }
        });
        modalInstance.result.then(function(resolve) {
          $scope.selected.resolveDescription = resolve.resolveDescription;
          $scope.saveStatus();
        }, function() {
        });
      };

      return init();
    }
  ]).controller('PurchaseModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {

      $scope.statusPrucases = ['Confirmadas','Canceladas','Pendentes','Todas'];
      $scope.filter = filter;

      $scope.searchProviders = function() {
        $.post("../backend/application/index.php?rota=/loadProvider", { searchKeywords: $scope.filter.providerName }, function(result){
            $scope.providers = jQuery.parseJSON(result).dataset.providers;
            $scope.$digest();
        });
      };

      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('PurchaseLogInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', function($scope, $rootScope, $modalInstance, logger) {
      $scope.selected = {
        resolveDescription: ''
      };

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.save = function() {
        if($scope.selected.resolveDescription.length < 1)
          return logger.logError('Motivos devem ser informados');
        $modalInstance.close($scope.selected);
      };
    }
  ]);
})();
;
