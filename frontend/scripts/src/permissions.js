(function () {
  'use strict';
  angular.module('app.internal').controller('PermissionsCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.types = ['Sim', 'Não'];
      $scope.searchKeywords = '';
      $scope.searchKeywordsTags = '';
      $scope.searchDealer = '';
      $scope.filteredPermissions = [];
      $scope.filteredDealers = [];
      $scope.filteredEmails = [];
      $scope.row = '';
      $scope.selected = {};
      $scope.horarioDozeTrintaESeis = new Date("1970-01-02 00:00:00");
      
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPagePermissions = $scope.filteredPermissions.slice(start, end);
      };

      $scope.selectDealers = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageDealers = $scope.filteredDealers.slice(start, end);
      };

      $scope.selectEmail = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageEmails = $scope.filteredEmails.slice(start, end);
      }

      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };

      $scope.onFilterChangeEmails = function() {
        $scope.selectEmail(1);
        return $scope.row = '';
      };

      $scope.onNumPerPageChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.onNumPerPageChangeDealers = function() {
        $scope.selectDealers(1);
        return $scope.currentPage = 1;
      };

      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.search = function() {
        $scope.filteredPermissions = $filter('filter')($scope.permissions, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.searchDealers = function() {
        $scope.filteredDealers = $filter('filter')($scope.dealers, $scope.searchDealer);
      };

      $scope.searchEmails = function() {
        $scope.filteredEmails = $filter('filter')($scope.Emails, $scope.searchKeywords);
        return $scope.onFilterChangeEmails();
      }

      $scope.setSelected = function() {
        original = angular.copy(this.permission);
        $scope.selected = this.permission;

        $scope.clientsDealer = [];
        $.post("../backend/application/index.php?rota=/loadClientsDealer", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.clientsDealer = jQuery.parseJSON(result).dataset;
          $scope.showClientsControl = true;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;
        });

        $.post("../backend/application/index.php?rota=/loadCustomersLink", {data: $scope.selected}, function(result){
          $scope.clientsD = jQuery.parseJSON(result).dataset;          
          $scope.clientsDealer = $scope.clientsD;
        });

        $scope.tabindex = 2;
      };

      $scope.setSelectedDealers = function() {
        original = angular.copy(this.dealers);
        $scope.selected = this.dealers;
        $scope.clientsDealer = [];
        $.post("../backend/application/index.php?rota=/loadClientsDealer", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.clientsDealer = jQuery.parseJSON(result).dataset;
          if($scope.clientsDealer.length < 1) {
            $scope.clientsDealer.push({name: ''});
          }
          $scope.tabindex = 3;
          $scope.$apply();
          $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
            $scope.clients = jQuery.parseJSON(result).dataset;
          });          
        });
      };

      $scope.addClient = function() {       
        if($scope.clientsDealer.length < 300) {
          $scope.clientsDealer.push({name: ''});
        } else {
          logger.logError('Maximo atingido');
        }      
      };      

      $scope.removeClient = function() {
        if($scope.clientsDealer.length > 0) {
          $scope.clientsDealer.pop();
        }
      };

      $scope.linkCustomers = function () {
        $.post("../backend/application/index.php?rota=/saveClientPermission",{ data: $scope.clientsDealer, client: $scope.selected }, function(result){
          $scope.clientSave = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.saveClientsDealers = function() {
        $.post("../backend/application/index.php?rota=/saveClientsDealer", {hashId: $scope.session.hashId, data: $scope.selected, clients: $scope.clientsDealer}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.cancelEdit();
        });
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredPermissions = $filter('orderBy')($scope.permissions, rowName);
        return $scope.onOrderChange();
      };

      $scope.setDates = function(){
        if($scope.selected.isDozeTrintaESeis){
          var s = $scope.horarioDozeTrintaESeis.getSeconds();
          var m = $scope.horarioDozeTrintaESeis.getMinutes();
          var h = $scope.horarioDozeTrintaESeis.getHours();

          var hSaida = h + 12; 

          var mEntrada = m - 10;
          if(mEntrada < 0){
            mEntrada += 60;
            h--;
          }

          var mSaida = m + 10;
          if(mSaida > 59){
            mSaida -= 60;
            hSaida++;
          }

          var entrada = '1970-01-01 ' + String(h)      + ':' + String(mEntrada) + ':' + String(s);
          var saida   = '1970-01-01 ' + String(hSaida) + ':' + String(mSaida)   + ':' + String(s);

          $scope.selected.sundayIn    = new Date(entrada);
          $scope.selected.mondayIn    = new Date(entrada);
          $scope.selected.tuesdayIn   = new Date(entrada);
          $scope.selected.wednesdayIn = new Date(entrada);
          $scope.selected.thursdayIn  = new Date(entrada);
          $scope.selected.fridayIn    = new Date(entrada);
          $scope.selected.saturdayIn  = new Date(entrada);
  
          $scope.selected.sundayOut    = new Date(saida);
          $scope.selected.mondayOut    = new Date(saida);
          $scope.selected.tuesdayOut   = new Date(saida);
          $scope.selected.wednesdayOut = new Date(saida);
          $scope.selected.thursdayOut  = new Date(saida);
          $scope.selected.fridayOut    = new Date(saida);
          $scope.selected.saturdayOut  = new Date(saida);
        }
        $scope.selected._sundayIn    = $rootScope.formatServerDateTime($scope.selected.sundayIn);
        $scope.selected._mondayIn    = $rootScope.formatServerDateTime($scope.selected.mondayIn);
        $scope.selected._tuesdayIn   = $rootScope.formatServerDateTime($scope.selected.tuesdayIn);
        $scope.selected._wednesdayIn = $rootScope.formatServerDateTime($scope.selected.wednesdayIn);
        $scope.selected._thursdayIn  = $rootScope.formatServerDateTime($scope.selected.thursdayIn);
        $scope.selected._fridayIn    = $rootScope.formatServerDateTime($scope.selected.fridayIn);
        $scope.selected._saturdayIn  = $rootScope.formatServerDateTime($scope.selected.saturdayIn);

        $scope.selected._sundayOut    = $rootScope.formatServerDateTime($scope.selected.sundayOut);
        $scope.selected._mondayOut    = $rootScope.formatServerDateTime($scope.selected.mondayOut);
        $scope.selected._tuesdayOut   = $rootScope.formatServerDateTime($scope.selected.tuesdayOut);
        $scope.selected._wednesdayOut = $rootScope.formatServerDateTime($scope.selected.wednesdayOut);
        $scope.selected._thursdayOut  = $rootScope.formatServerDateTime($scope.selected.thursdayOut);
        $scope.selected._fridayOut    = $rootScope.formatServerDateTime($scope.selected.fridayOut);
        $scope.selected._saturdayOut  = $rootScope.formatServerDateTime($scope.selected.saturdayOut);

        if(!$scope.selected._vacationEnd){
          $scope.selected._vacationEnd = new Date("2010-01-01 00:00:00");
          $scope.selected._vacationEnd = "";
        }
        $scope.selected._vacationEnd  = $rootScope.formatServerDateTime($scope.selected.vacationEnd);

      };

      $scope.savePermission = function() {
        if ($scope.form_permission.$valid && (!$scope.selected.onVacation || ($scope.selected.onVacation && $scope.selected.vacationEnd))) {
          $scope.setDates();

          for(var i in $scope.Users) {
            if($scope.Users[i].userName == $scope.selected.name) {
              $scope.selected.userId = $scope.Users[i].id;
            }
          }

          $.post("../backend/application/index.php?rota=/savePermission", {hashId: $scope.session.hashId, data: $scope.selected }, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadPermissions();            
          });
          if($scope.clientsDealer){
            $scope.linkCustomers();
          }
          /*$.post("../backend/application/index.php?rota=/saveClientsDealer", {hashId: $scope.session.hashId, data: $scope.selected, clients: $scope.clientsDealer}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          });*/
        }else {
          logger.logError("Favor preencher todos os campos!");
        }
      };

      $scope.cancelEdit = function() {
        $scope.selected = {};
        $scope.showClientsControl = false;
        $scope.loadPermissions();
      };

      $scope.nextWizard = function() {
        if ($scope.form_newPermission.$valid) {
          var original = angular.copy($scope.selected);
          $scope.selected = $filter('filter')($scope.permissions, $scope.selected.userName);
          if($scope.selected.length > 0)
            $scope.selected = $scope.selected[0];
          else
            $scope.selected = original;
          $scope.tabindex = 2;
        }else{
          logger.logError("Favor vincular um usuário valido!");
        }
      };

      $scope.getValue = function() {        
        for(var i in $scope.permissions){
          $scope.permissions[i].purchase = ($scope.permissions[i].purchase == 'true');
          $scope.permissions[i].wizardPurchase = ($scope.permissions[i].wizardPurchase == 'true');
          $scope.permissions[i].sales = ($scope.permissions[i].sales == 'true');
          $scope.permissions[i].wizardSale = ($scope.permissions[i].wizardSale == 'true');
          $scope.permissions[i].milesBench = ($scope.permissions[i].milesBench == 'true');
          $scope.permissions[i].financial = ($scope.permissions[i].financial == 'true');
          $scope.permissions[i].creditCard = ($scope.permissions[i].creditCard == 'true');
          $scope.permissions[i].users = ($scope.permissions[i].users == 'true');
          $scope.permissions[i].changeSale = ($scope.permissions[i].changeSale == 'true');
          $scope.permissions[i].changeMiles = ($scope.permissions[i].changeMiles == 'true');
          $scope.permissions[i].commercial = ($scope.permissions[i].commercial == 'true');
          $scope.permissions[i].permission = ($scope.permissions[i].permission == 'true');
          $scope.permissions[i].pagseguro = ($scope.permissions[i].pagseguro == 'true');
          $scope.permissions[i].internRefund = ($scope.permissions[i].internRefund == 'true');
          $scope.permissions[i].internCommercial = ($scope.permissions[i].internCommercial == 'true');
          $scope.permissions[i].humanResources = ($scope.permissions[i].humanResources == 'true');
          $scope.permissions[i].salePlansEdit = ($scope.permissions[i].salePlansEdit == 'true');
          $scope.permissions[i].conference = ($scope.permissions[i].conference == 'true');

          $scope.permissions[i].onlineOnlineOrder = ( $scope.permissions[i].onlineOnlineOrder == 'true' );
          $scope.permissions[i].onlineBalanceOrder = ( $scope.permissions[i].onlineBalanceOrder == 'true' );
          $scope.permissions[i].onlineCardsInUse = ( $scope.permissions[i].onlineCardsInUse == 'true' );

          $scope.permissions[i].purchaseProvider = ( $scope.permissions[i].purchaseProvider == 'true' );
          $scope.permissions[i].purchasePaymentPruchase = ( $scope.permissions[i].purchasePaymentPruchase == 'true' );
          $scope.permissions[i].purchaseEndPruchase = ( $scope.permissions[i].purchaseEndPruchase == 'true' );
          $scope.permissions[i].purchasePruchases = ( $scope.permissions[i].purchasePruchases == 'true' );
          $scope.permissions[i].purchaseCardsPendency = ( $scope.permissions[i].purchaseCardsPendency == 'true' );

          $scope.permissions[i].saleClients = ( $scope.permissions[i].saleClients == 'true' );
          $scope.permissions[i].saleBalanceClients = ( $scope.permissions[i].saleBalanceClients == 'true' );
          $scope.permissions[i].saleFutureBoardings = ( $scope.permissions[i].saleFutureBoardings == 'true' );
          $scope.permissions[i].saleRefundCancel = ( $scope.permissions[i].saleRefundCancel == 'true' );
          $scope.permissions[i].saleRevertRefund = ( $scope.permissions[i].saleRevertRefund == 'true' );
          $scope.permissions[i].wizardSaleEvent = ( $scope.permissions[i].wizardSaleEvent == 'true' );

          $scope.permissions[i].onVacation = ( $scope.permissions[i].onVacation == 'true' );
          $scope.permissions[i].isDozeTrintaESeis = ( $scope.permissions[i].isDozeTrintaESeis == 'true' );


          $scope.permissions[i].sundayIn = new Date($scope.permissions[i].sundayIn);
          $scope.permissions[i].mondayIn = new Date($scope.permissions[i].mondayIn);
          $scope.permissions[i].tuesdayIn = new Date($scope.permissions[i].tuesdayIn);
          $scope.permissions[i].wednesdayIn = new Date($scope.permissions[i].wednesdayIn);
          $scope.permissions[i].thursdayIn = new Date($scope.permissions[i].thursdayIn);
          $scope.permissions[i].fridayIn = new Date($scope.permissions[i].fridayIn);
          $scope.permissions[i].saturdayIn = new Date($scope.permissions[i].saturdayIn);

          $scope.permissions[i].sundayOut = new Date($scope.permissions[i].sundayOut);
          $scope.permissions[i].mondayOut = new Date($scope.permissions[i].mondayOut);
          $scope.permissions[i].tuesdayOut = new Date($scope.permissions[i].tuesdayOut);
          $scope.permissions[i].wednesdayOut = new Date($scope.permissions[i].wednesdayOut);
          $scope.permissions[i].thursdayOut = new Date($scope.permissions[i].thursdayOut);
          $scope.permissions[i].fridayOut = new Date($scope.permissions[i].fridayOut);
          $scope.permissions[i].saturdayOut = new Date($scope.permissions[i].saturdayOut);

          if($scope.permissions[i].vacationEnd)
            $scope.permissions[i].vacationEnd = new Date($scope.permissions[i].vacationEnd.date);
          else
            $scope.permissions[i].vacationEnd = "";

          if($scope.selected.isDozeTrintaESeis){
            var s = $scope.permissions[i].sundayIn.getSeconds();
            var m = $scope.permissions[i].sundayIn.getMinutes();
            var h = $scope.permissions[i].sundayIn.getHours();
    
            m += 10;
            if(m > 59){
              mSaida -= 60;
              h++;
            }
            var str = '1970-01-01 ' + String(h) + ':' + String(m) + ':' + String(s);
            $scope.horarioDozeTrintaESeis = new Date(str);
          }
        }
        $scope.search();
        $scope.tabindex = 0;
        $scope.$apply();
        return $scope.select($scope.currentPage);
      };

      $scope.loadPermissions = function() {
        $.post("../backend/application/index.php?rota=/loadPermissions", $scope.session, function(result){
          $scope.permissions = jQuery.parseJSON(result).dataset;
          $scope.getValue();
        });
        $.post("../backend/application/index.php?rota=/loadDealers", $scope.session, function(result){
          $scope.dealers = jQuery.parseJSON(result).dataset;
          $scope.searchDealers();
        });
      };

      window.$scope = $scope;

      $scope.loadConfigEmails = function() {
        $.post("../backend/application/index.php?rota=/loadConfigEmails", $scope.session, function(result){
          $scope.Emails = jQuery.parseJSON(result).dataset;
          $scope.searchEmails();
        });
      };

      $scope.newPermission = function(){
        $scope.selected = {};
        $.post("../backend/application/index.php?rota=/loadUsers", $scope.session, function(result){
          $scope.Users = jQuery.parseJSON(result).dataset;
          $scope.tabindex = 1;
          $scope.$apply();
        });
      };

      $scope.setSelectedEmail = function() {
        $scope.selectedEmail = this.emails;
        $scope.tabindex = 10;
      };

      $scope.controlClients = function() {
        $scope.showClientsControl = true;
      };

      $scope.cancelEditEmail = function() {
        $scope.tabindex = 0;
      };

      $scope.saveConfigEmail = function () {
        $.post("../backend/application/index.php?rota=/saveConfigEmails", { hashId: $scope.session.hashId, data: $scope.selectedEmail }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadConfigEmails();
          $scope.tabindex = 0;
        });
      };

      $scope.newConfigEmail = function () {
        $scope.selectedEmail = {};
        $scope.tabindex = 10;
      };

      $scope.loadTags = function() {
        $.post("../backend/application/index.php?rota=/loadTags", { }, function(result){
          $scope.tags = jQuery.parseJSON(result).dataset;
          $scope.tabindex = 4;
          $scope.searchTags();
        });
      };

      $scope.searchTags = function() {
        $scope.filteredTags = $filter('filter')($scope.tags, $scope.searchKeywordsTags);
      };

      $scope.setSelectedTag = function() {
        $scope.selectedTag = this.tag;
        $scope.tabindex = 5;
      };

      $scope.saveTag = function() {
        $.post("../backend/application/index.php?rota=/saveTag", { data: $scope.selectedTag }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadTags();
          $scope.tabindex = 0;
        });
      };

      $scope.newTag = function() {
        $scope.selectedTag = {};
        $scope.tabindex = 5;
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPagePermissions = [];
      init = function() {
        $scope.tabindex = 0;
        $scope.showClientsControl = false;
        $scope.checkValidRoute();
        $scope.loadPermissions();
        $scope.loadConfigEmails();
        $scope.loadTags();
      };
      return init();
    }
  ]);

})();

;