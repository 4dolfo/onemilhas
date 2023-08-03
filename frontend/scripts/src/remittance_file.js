(function () {
  'use strict';
  angular.module('app').controller('RemittanceFileCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', 'FileUploader', '$window', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger, FileUploader, $window) {
      var init;
      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.banks = ['BRADESCO', 'SANTANDER'];
      $scope.searchKeywords = '';
      $scope.filteredBillsReceive = [];
      $scope.row = '';

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageBillsReceive = $scope.filteredBillsReceive.slice(start, end);
      };

      $scope.getStatusDesc = function(status) {
        switch (status) {
          case 'B':
            return "Baixada";
          case 'E':
            return "Emitida";
          case 'A':
            return "Em Aberto";
          case 'T':
            return "Transferencia";
        }
      };

      $scope.redirectToGoogle = function(link){
          $window.open(link, '_blank');
      };

      $scope.findColor = function(billreceive) {
        if(billreceive.paymentType == 'Boleto') {
          return '#98d1f5';
        }
      };

      $scope.billReceiveTag = function(status) {
        switch (status) {
          case 'B':
            return "label label-success";
          case 'E':
            return "label label-info";
          case 'A':
            return "label label-warning";
          case 'T':
            return "label label-default";
        }
      };

      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };

      $scope.onNumPerPageChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.setSelected = function() {
        $scope.selected = this.billreceive;
        $scope.nextWizardStage();
        return $scope.selected;
      };

      $scope.search = function() {
        $scope.filteredBillsReceive = $filter('filter')($scope.billsreceive, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredBillsReceive = $filter('orderBy')($scope.billsreceive, rowName);
        return $scope.onOrderChange();
      };

      $scope.nextWizardStage = function() {
        $scope.tabindex = $scope.tabindex + 1;
        return $scope.tabindex;
      };

      $scope.priorWizardStage = function() {
        $scope.tabindex = $scope.tabindex - 1;
        return $scope.tabindex;
      };

      $scope.WizardStageProgress = function() {
        return (($scope.tabindex + 1) * 34);
      };

      $scope.addRow = function(_bill) {
        if (_bill.status == 'B') {
          _bill.checked = false;
        }
        return _bill;
      };

      $scope.loadBills = function() {
        if ($scope.wbillet_client.$valid) {
          $scope.loadBillsReceive();
          $scope.nextWizardStage();
        }
      };

      $scope.addRow = function() {
        this.client.checked != this.client.checked;
      };

      var getSumBills = function (clients, callback) {
        var newClients = [];
        var clientsNotValid = [];
        for(var i in clients) {
          var _client = angular.copy(clients[i]);
          var newBills = [];
          for(var j in _client.bills) {
            var _bill = angular.copy(_client.bills[j]);
            if (_bill.checked) newBills.push(_bill);
          }
          if (_client.registrationCode && _client.zipCode && _client.bills.length > 0 && _client.name && _client.adress && _client.checked == true && _client.paymentType == 'Boleto' && _client.billingPeriod == 'Diario') {
            if(newBills.length > 0) {
              _client.bills = angular.copy(newBills);
              newClients.push(_client);
            }
          } else {
            clientsNotValid.push(_client);
          }
        }
        var doc_number;
        var our_number;

        $.post("../backend/application/index.php?rota=/loadLastBillet", $scope.session, function (result) {
          var lastBillet = jQuery.parseJSON(result).dataset;
          doc_number = parseInt(lastBillet.id);
          our_number = parseInt(lastBillet.id);
          for (var index in newClients) {
            doc_number++;
            our_number++;

            newClients[index].billToReceive = angular.copy(newClients[index].bills[0]);
            newClients[index].billToReceive.description = '';

            newClients[index].billToReceive.alreadyPaid = 0;
            newClients[index].billToReceive.actual_value = 0;
            newClients[index].billToReceive.discount = 0;

            if (newClients[index].paymentType == 'Antecipado') {

              for (var i in newClients[index].bills) {

                if (newClients[index].bills[i].account_type == "Reembolso" || newClients[index].bills[i].account_type == "Credito" || newClients[index].bills[i].account_type == "Credito Adiantamento") {
                  newClients[index].billToReceive.actual_value = newClients[index].billToReceive.actual_value - newClients[index].bills[i].actual_value;
                  if (newClients[index].bills[i].paymentType == 'Comum') {
                    newClients[index].billToReceive.alreadyPaid = newClients[index].billToReceive.alreadyPaid - newClients[index].bills[i].actual_value;
                  }
                } else {
                  newClients[index].billToReceive.actual_value = newClients[index].billToReceive.actual_value + newClients[index].bills[i].actual_value;
                  if (newClients[index].bills[i].paymentType == 'Comum') {
                    newClients[index].billToReceive.alreadyPaid = newClients[index].billToReceive.alreadyPaid + newClients[index].bills[i].actual_value;
                  }
                }

              }
            } else {
              for (var i in newClients[index].bills) {
                if (newClients[index].bills[i].account_type == "Reembolso" || newClients[index].bills[i].account_type == "Credito" || newClients[index].bills[i].account_type == 'Credito Adiantamento') {
                  newClients[index].billToReceive.actual_value = newClients[index].billToReceive.actual_value - newClients[index].bills[i].actual_value;
                } else {
                  newClients[index].billToReceive.actual_value = newClients[index].billToReceive.actual_value + newClients[index].bills[i].actual_value;
                  if(newClients[index].useCommission == true) {
                    newClients[index].billToReceive.actual_value -= newClients[index].bills[i].comission;
                    newClients[index].billToReceive.discount += newClients[index].bills[i].comission;
                  }
                }
              }
            }

            newClients[index].billToReceive.valueFloat = newClients[index].billToReceive.actual_value;
            newClients[index].billToReceive.actual_value = $rootScope.formatNumber(newClients[index].billToReceive.actual_value);
            newClients[index].billToReceive.doc_number = doc_number;
            newClients[index].billToReceive.our_number = our_number;
            newClients[index].billToReceive.transfer = false;
            newClients[index].billToReceive.due_date = new Date(newClients[index].billToReceive.due_date + 'T12:00:00Z');
            var lastBank = window.localStorage.getItem("last-bank");
            if (lastBank) {
              newClients[index].billToReceive.bank = lastBank;
            } else {
              newClients[index].billToReceive.bank = 'BRADESCO';
            }

            if (newClients[index].paymentType == 'Antecipado' && !newClients[index].billToReceive.early) {
              newClients[index].billToReceive.early = true;
              newClients[index].billToReceive.hasBillet = false;
            } else {
              newClients[index].billToReceive.early = false;
              newClients[index].billToReceive.hasBillet = true;
            }

            if (newClients[index].paymentType == 'Antecipado') {
              for (var i in newClients[index].bills) {
                if (newClients[index].bills[i].paymentType == 'Boleto') {
                  newClients[index].billToReceive.early = false;
                  newClients[index].billToReceive.hasBillet = true;
                }
              }
            }
            if (newClients[index].workingDays == true || newClients[index].workingDays == 'true' ) {

              var paymentDays = 0;
              if (newClients[index].paymentDays > 7) {
                paymentDays = (parseInt(newClients[index].paymentDays / 5) * 2) + newClients[index].paymentDays;
                newClients[index].billToReceive.due_date = new Date();
                newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + paymentDays);
              } else {
                paymentDays = newClients[index].paymentDays;
                newClients[index].billToReceive.due_date = new Date();
                if (newClients[index].billToReceive.due_date.getDay() == 0) {
                  newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + 1);
                  paymentDays--;
                }
                if (newClients[index].billToReceive.due_date.getDay() == 6) {
                  newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + 2);
                  paymentDays--;
                }
                for (var j = 0; j < paymentDays; j++) {
                  if (newClients[index].billToReceive.due_date.getDay() == 0) {
                    newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + 1);
                  }
                  if (newClients[index].billToReceive.due_date.getDay() == 6) {
                    newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + 2);
                  }
                  newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + 1);
                }
              }
            } else {
              newClients[index].billToReceive.due_date = new Date();
              newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + newClients[index].paymentDays);
            }
            if (newClients[index].billToReceive.due_date.getDay() == 6) {
              newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + 2);
            }
            if (newClients[index].billToReceive.due_date.getDay() == 0) {
              newClients[index].billToReceive.due_date.setDate(newClients[index].billToReceive.due_date.getDate() + 1);
            }

            var object = {
              clientsToReceive: newClients,
              clientsNotValid: clientsNotValid
            };
          }
          callback(object);
        });
      };

      $scope.getTotalChecked = function() {
        let total = 0;
        for(var i in $scope.clientsToReceive) {
          if($scope.clientsToReceive[i].checked == true && $scope.clientsToReceive[i].alreadBilled == 0 && $scope.clientsToReceive[i].paymentType != 'Antecipado' && $scope.clientsToReceive[i].totalValue > 0) {
            total += $scope.clientsToReceive[i].totalValue;
          }
        }
        return total;
      };

      $scope.filterList = function(item) {
        return item.paymentType != 'Antecipado' && item.totalValue > 0;
      };

      $scope.filterListPreview = function(item) {
        return item.paymentType == 'Antecipado' || item.totalValue <= 0;
      };

      $scope.loadOpenedBillets = function() {
        $.post("../backend/application/index.php?rota=/loadOpenedBillets", { data: $scope.client }, function(result){
          $scope.openedBillets = jQuery.parseJSON(result).dataset;
          if($scope.openedBillets.length > 0) {
            $scope.possibleCloseBillets = true;
            $scope.$digest();
          }
        });
      };

      $scope.setBilletToClose = function(billet) {
        $scope.currentBillet = billet;
      };

      $scope.openModalCloseBillets = function() {
        $rootScope.$emit('openCloseBillesWtihCreditModal', $scope.openedBillets);
      };

      $scope.setActual_Value = function() {
        $scope.wbillet.actual_value = parseFloat($scope.wbillet.original_value) + parseFloat($scope.wbillet.tax) - parseFloat($scope.wbillet.discount);
      };

      $scope.findBillets = function() {
        $scope.billsreceive = [];
        $.post("../backend/application/index.php?rota=/loadBillGenerated", {hashId: $scope.session.hashId, data: $scope.client}, function(result){
          $scope.billsgenerated = jQuery.parseJSON(result).dataset;

          for(var i in $scope.billsgenerated) {
            $scope.fillBillets($scope.billsgenerated[i]);
          }
          $scope.billetGenerated = true;
          $scope.search();
          $scope.$apply();
        });
      };

      $scope.fillBillets = function(bill) {
        var alreadFill = false;
        bill.pax_name = '';
        bill.flightLocator = '';
        for(var j in $scope.billsreceive) {
          if($scope.billsreceive[j].billet === bill.billet) {
            alreadFill = true;
            if(bill.account_type == "Reembolso" || bill.account_type == "Credito" || bill.account_type == 'Credito Adiantamento'){
              $scope.billsreceive[j].actual_value -= bill.actual_value;
            } else {
              $scope.billsreceive[j].actual_value += bill.actual_value;
            }
          }
        }
        if(!alreadFill) {
          if(bill.account_type == "Reembolso" || bill.account_type == "Credito" || bill.account_type == 'Credito Adiantamento'){
            console.log("bew re");
            bill.actual_value = bill.actual_value * -1;
          }
          bill.account_type = 'BORDERO';
          $scope.billsreceive.push(bill);
        }
      };

      $scope.addDivision = function() {
        $scope.billetsDivision.push({
          actualValue: 0,
          dueDate: $scope.wbillet.due_date,
          name: $scope.wbillet.our_number
        });
      };

      $scope.removeDivision = function() {
        $scope.billetsDivision.pop();
      };

      $scope.getSumBillsReceive = function () {
        var value = 0;
        if($scope.billsreceive) {
          if($scope.billsreceive.length > 0) {
            $scope.checkedrows = $filter('filter')($scope.billsreceive, true);
            for(var i in $scope.checkedrows) {
              if($scope.checkedrows[i].account_type == "Reembolso" || $scope.checkedrows[i].account_type == "Credito" || $scope.checkedrows[i].account_type == 'Credito Adiantamento'){
                value -= $scope.checkedrows[i].actual_value;
              } else {
                value += $scope.checkedrows[i].actual_value;
              }
            }
          }
        } return $rootScope.formatNumber(value);
      };

      $scope.markAll = function() {
        for(var i in $scope.billsreceive) {
          $scope.billsreceive[i].checked = true;
        }
      };

      $scope.unMarkAll = function() {
        for(var i in $scope.billsreceive) {
          $scope.billsreceive[i].checked = false;
        }
      };

      $scope.loadBillsReceive = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadBillsReceive", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
          $scope.billsreceive = jQuery.parseJSON(result).dataset;
          $scope.search();
          cfpLoadingBar.complete();
          $.post("../backend/application/index.php?rota=/loadClientsByFilter", {hashId: $scope.session.hashId, data: $scope.filter}, function(result){
            $scope.client = jQuery.parseJSON(result).dataset;
            $scope.client = $scope.client[0];
          });
          return $scope.select($scope.currentPage);
        });
      };

      $scope.setSelectedClient = function(_client){
        if($scope.selectedClient){
          if($scope.selectedClient == _client) $scope.selectedClient = {};
          else  $scope.selectedClient = _client;
        }else $scope.selectedClient = _client;
      };

      $scope.toggleAll = function(_client) {
        var toggleStatus = _client.isAllSelected;
        angular.forEach(_client.bills, function(itm){ itm.checked = toggleStatus; });
        return _client;
      };

      $scope.optionToggled = function(_client){
        _client.isAllSelected = _client.bills.every(function(bill){ return bill.selected; });
        return _client;
      };

      $scope.toDataUrl = function(url, callback) {
        var xhr = new XMLHttpRequest();
        xhr.responseType = 'blob';
        xhr.onload = function() {
          var reader = new FileReader();
          reader.onloadend = function() {
            callback(reader.result);
          };
          reader.readAsDataURL(xhr.response);
        };
        xhr.open('GET', url);
        xhr.send();
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageBillsReceive = [];
      $rootScope.hashId = $scope.session.hashId;

      $scope.yesterdayBills = function() {
        $scope.buttonEnable = false;
        $.post("../backend/application/index.php?rota=/yesterdayBills", $scope.session, function(result){
          $scope.clientsToReceive = jQuery.parseJSON(result).dataset;
          $scope.buttonEnable = true;
          $scope.$apply();
          $scope.caclValuesAndDueDate();
        });
      };

      $scope.caclValuesAndDueDate = function() {
        for(var index in $scope.clientsToReceive) {
          // calc of total value
          if ($scope.clientsToReceive[index].paymentType == 'Antecipado') {
            $scope.clientsToReceive[index].totalValue = 0;
          } else {
            $scope.clientsToReceive[index].totalValue = 0;
            for (var i in $scope.clientsToReceive[index].bills) {
              if($scope.clientsToReceive[index].bills[i].checked && $scope.clientsToReceive[index].bills[i].alreadBilled == 0) {
                if ($scope.clientsToReceive[index].bills[i].account_type == "Reembolso" || $scope.clientsToReceive[index].bills[i].account_type == "Credito" || $scope.clientsToReceive[index].bills[i].account_type == 'Credito Adiantamento') {
                  $scope.clientsToReceive[index].totalValue = $scope.clientsToReceive[index].totalValue - $scope.clientsToReceive[index].bills[i].actual_value;
                } else {
                  $scope.clientsToReceive[index].totalValue = $scope.clientsToReceive[index].totalValue + $scope.clientsToReceive[index].bills[i].actual_value;
                }
              }
            }
          }

          // calc of due date
          if ($scope.clientsToReceive[index].workingDays == true || $scope.clientsToReceive[index].workingDays == 'true') {
            var paymentDays = 0;
            if ($scope.clientsToReceive[index].paymentDays > 7) {
              paymentDays = (parseInt($scope.clientsToReceive[index].paymentDays / 5) * 2) + $scope.clientsToReceive[index].paymentDays;
              $scope.clientsToReceive[index].due_date = new Date();
              $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + paymentDays);
            } else {
              paymentDays = $scope.clientsToReceive[index].paymentDays;
              $scope.clientsToReceive[index].due_date = new Date();
              if ($scope.clientsToReceive[index].due_date.getDay() == 0) {
                $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + 1);
                paymentDays--;
              }
              if ($scope.clientsToReceive[index].due_date.getDay() == 6) {
                $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + 2);
                paymentDays--;
              }
              for (var j = 0; j < paymentDays; j++) {
                if ($scope.clientsToReceive[index].due_date.getDay() == 0) {
                  $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + 1);
                }
                if ($scope.clientsToReceive[index].due_date.getDay() == 6) {
                  $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + 2);
                }
                $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + 1);
              }
            }
          } else {
            $scope.clientsToReceive[index].due_date = new Date();
            $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + $scope.clientsToReceive[index].paymentDays);
          }
          if ($scope.clientsToReceive[index].due_date.getDay() == 6) {
            $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + 2);
          }
          if ($scope.clientsToReceive[index].due_date.getDay() == 0) {
            $scope.clientsToReceive[index].due_date.setDate($scope.clientsToReceive[index].due_date.getDate() + 1);
          }
        }
        $scope.$apply();
      };

      $scope.findBilling = function() {
        $.post("../backend/application/index.php?rota=/checkClientsDeadLine", {hashId: $scope.session.hashId}, function(result){
          $scope.clientsDeadLine = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.findDate = function(date) {
        return new Date(date);
      };

      $scope.findAll = function() {
        $.post("../backend/application/index.php?rota=/loadClientToReceive", $scope.session, function(result){
          $scope.clientsToReceive = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.markAll = function() {
        for(var i in $scope.clientsToReceive) {
          for(var j in $scope.clientsToReceive[i].bills) {
            $scope.clientsToReceive[i].bills[j].checked = true;
          }
        }
      };

      $scope.uMarkAll = function() {
        for(var i in $scope.clientsToReceive) {
          for(var j in $scope.clientsToReceive[i].bills) {
            $scope.clientsToReceive[i].bills[j].checked = false;
          }
        }
      };

      $scope.markAllClients = function() {
        for(var i in $scope.clientsToReceive) {
          $scope.clientsToReceive[i].checked = true;
        }
      };

      $scope.uMarkAllClients = function() {
        for(var i in $scope.clientsToReceive) {
          $scope.clientsToReceive[i].checked = false;
        }
      };

      $scope.generateRemittanceBradescoSRM = function () {
        $scope.buttonEnable = false;
        getSumBills(angular.copy($scope.clientsToReceive), function (clients) {
          if (clients.clientsToReceive.length > 0) {
            clients.clientsToReceive.forEach(function(element) {
              element.billToReceive.due_date = $rootScope.formatServerDate(element.billToReceive.due_date);
            }, this);
            $.post("../backend/application/index.php?rota=/SRM/generateRemittanceBradesco", { data: clients.clientsToReceive }, function (result) {
              $scope.printAll(clients.clientsToReceive);
              var status = jQuery.parseJSON(result).dataset['status'];
              var message = jQuery.parseJSON(result).dataset['message'];
              if(status == 'success'){
                $scope.redirectToGoogle(jQuery.parseJSON(result).dataset['arquivo_path']);
                logger.logSuccess(message);
                $scope.yesterdayBills();
              } else {
                logger.logError(message);
              }
              $scope.$apply();
              if(clients.clientsNotValid.length > 0){
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/notification_client_list.html",
                  controller: 'NotificationClientListCtrl',
                  periods: $scope.$parent.periods,
                  size: 'lg',
                  resolve: {
                    data: function() {
                      return clients.clientsNotValid;
                    },
                    header: function() {
                      return 'Clientes não gerados';
                    }
                  }
                });
                logger.logError("Alguns boletos não foram emitidos. Favor verificar os dados cadastrais dos clientes e tente novamente.");
              }
            });
          }
          else {
            logger.logError("Favor verificar se todas as contas que foram selecionadas, estão com os dados cadastrais do cliente preenchidos corretamente.");
            $scope.buttonEnable = true;
            $scope.$apply();
          }
        });
      };

      $scope.generateRemittanceBradescoMMS = function () {
        $scope.buttonEnable = false;
        getSumBills(angular.copy($scope.clientsToReceive), function (clients) {
          if (clients.clientsToReceive.length > 0) {
            clients.clientsToReceive.forEach(function(element) {
              element.billToReceive.due_date = $rootScope.formatServerDate(element.billToReceive.due_date);
            }, this);
            $.post("../backend/application/index.php?rota=/MMS/generateRemittanceBradesco", { data: clients.clientsToReceive }, function (result) {
              $scope.printAll(clients.clientsToReceive);
              var status = jQuery.parseJSON(result).dataset['status'];
              var message = jQuery.parseJSON(result).dataset['message'];
              if(status == 'success'){
                $scope.redirectToGoogle(jQuery.parseJSON(result).dataset['arquivo_path']);
                logger.logSuccess(message);
                $scope.yesterdayBills();
              } else {
                logger.logError(message);
              }
              $scope.$apply();
              if(clients.clientsNotValid.length > 0){
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/notification_client_list.html",
                  controller: 'NotificationClientListCtrl',
                  periods: $scope.$parent.periods,
                  size: 'lg',
                  resolve: {
                    data: function() {
                      return clients.clientsNotValid;
                    },
                    header: function() {
                      return 'Clientes não gerados';
                    }
                  }
                });
                logger.logError("Alguns boletos não foram emitidos. Favor verificar os dados cadastrais dos clientes e tente novamente.");
              }
            });
          }
          else {
            logger.logError("Favor verificar se todas as contas que foram selecionadas, estão com os dados cadastrais do cliente preenchidos corretamente.");
            $scope.buttonEnable = true;
            $scope.$apply();
          }
        });
      };

      $scope.generateRemittanceBBrasilSRM = function () {
        $scope.buttonEnable = false;
        getSumBills(angular.copy($scope.clientsToReceive), function (clients) {
          if (clients.clientsToReceive.length > 0) {
            clients.clientsToReceive.forEach(function(element) {
              element.billToReceive.due_date = $rootScope.formatServerDate(element.billToReceive.due_date);
            }, this);
            $.post("../backend/application/index.php?rota=/SRM/generateRemittanceBBrasil", { data: clients.clientsToReceive }, function (result) {
              $scope.printAll(clients.clientsToReceive);
              var status = jQuery.parseJSON(result).dataset['status'];
              var message = jQuery.parseJSON(result).dataset['message'];
              if(status == 'success'){
                $scope.redirectToGoogle(jQuery.parseJSON(result).dataset['arquivo_path']);
                logger.logSuccess(message);
                $scope.yesterdayBills();
              } else {
                logger.logError(message);
              }
              $scope.$apply();
              if(clients.clientsNotValid.length > 0){
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/notification_client_list.html",
                  controller: 'NotificationClientListCtrl',
                  periods: $scope.$parent.periods,
                  size: 'lg',
                  resolve: {
                    data: function() {
                      return clients.clientsNotValid;
                    },
                    header: function() {
                      return 'Clientes não gerados';
                    }
                  }
                });
                logger.logError("Alguns boletos não foram emitidos. Favor verificar os dados cadastrais dos clientes e tente novamente.");
              }
            });
          }
          else {
            logger.logError("Favor verificar se todas as contas que foram selecionadas, estão com os dados cadastrais do cliente preenchidos corretamente.");
            $scope.buttonEnable = true;
            $scope.$apply();
          }
        });
      };

      $scope.generateRemittanceBBrasilMMS = function () {
        $scope.buttonEnable = false;
        getSumBills(angular.copy($scope.clientsToReceive), function (clients) {
          if (clients.clientsToReceive.length > 0) {
            clients.clientsToReceive.forEach(function(element) {
              element.billToReceive.due_date = $rootScope.formatServerDate(element.billToReceive.due_date);
            }, this);
            $.post("../backend/application/index.php?rota=/MMS/generateRemittanceBBrasil", { data: clients.clientsToReceive }, function (result) {
              $scope.printAll(clients.clientsToReceive);
              var status = jQuery.parseJSON(result).dataset['status'];
              var message = jQuery.parseJSON(result).dataset['message'];
              if(status == 'success'){
                $scope.redirectToGoogle(jQuery.parseJSON(result).dataset['arquivo_path']);
                logger.logSuccess(message);
                $scope.yesterdayBills();
              } else {
                logger.logError(message);
              }
              $scope.$apply();
              if(clients.clientsNotValid.length > 0){
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/notification_client_list.html",
                  controller: 'NotificationClientListCtrl',
                  periods: $scope.$parent.periods,
                  size: 'lg',
                  resolve: {
                    data: function() {
                      return clients.clientsNotValid;
                    },
                    header: function() {
                      return 'Clientes não gerados';
                    }
                  }
                });
                logger.logError("Alguns boletos não foram emitidos. Favor verificar os dados cadastrais dos clientes e tente novamente.");
              }
            });
          }
          else {
            logger.logError("Favor verificar se todas as contas que foram selecionadas, estão com os dados cadastrais do cliente preenchidos corretamente.");
            $scope.buttonEnable = true;
            $scope.$apply();
          }
        });
      };

      $scope.generateRemittanceSantanderSRM = function () {
        $scope.buttonEnable = false;
        getSumBills(angular.copy($scope.clientsToReceive), function (clients) {
          if (clients.clientsToReceive.length > 0) {
            clients.clientsToReceive.forEach(function(element) {
              element.billToReceive.due_date = $rootScope.formatServerDate(element.billToReceive.due_date);
            }, this);
            $.post("../backend/application/index.php?rota=/SRM/generateRemittanceSantander", { data: clients.clientsToReceive }, function (result) {
              $scope.printAll(clients.clientsToReceive);
              var status = jQuery.parseJSON(result).dataset['status'];
              var message = jQuery.parseJSON(result).dataset['message'];
              if(status == 'success'){
                $scope.redirectToGoogle(jQuery.parseJSON(result).dataset['arquivo_path']);
                logger.logSuccess(message);
                $scope.yesterdayBills();
              } else {
                logger.logError(message);
              }
              $scope.$apply();
              if(clients.clientsNotValid.length > 0){
                var modalInstance;
                modalInstance = $modal.open({
                  templateUrl: "app/modals/notification_client_list.html",
                  controller: 'NotificationClientListCtrl',
                  periods: $scope.$parent.periods,
                  size: 'lg',
                  resolve: {
                    data: function() {
                      return clients.clientsNotValid;
                    },
                    header: function() {
                      return 'Clientes não gerados';
                    }
                  }
                });
                logger.logError("Alguns boletos não foram emitidos. Favor verificar os dados cadastrais dos clientes e tente novamente.");
              }
            });
          }
          else {
            logger.logError("Favor verificar se todas as contas que foram selecionadas, estão com os dados cadastrais do cliente preenchidos corretamente.");
            $scope.buttonEnable = true;
            $scope.$apply();
          }
        });
      };

      $scope.printAll = function(array){
        var doc = new jsPDF('p', 'pt');

        console.log(array);
        for(var client in array) {
          doc.margin = 0.5;
          doc.setFontSize(18);
          var columns = [
            {title: "DATA", dataKey: "issuing_date"},
            {title: "LOC", dataKey: "flightLocator"},
            {title: "ORIGEM", dataKey: "from"},
            {title: "CIA", dataKey: "airline"},
            {title: "PAX", dataKey: "pax_name"},
            {title: "VALOR", dataKey: "actual_value"},
            {title: "Taxa", dataKey: "miles_tax"},
            {title: "Milhas", dataKey: "miles"},
            {title: "EMISSOR", dataKey: "issuing"},
            {title: "CLIENTE", dataKey: "client"}
          ];
  
          var rows = [];
          for (var i = 0; i < array[client].bills.length; i++) {
  
            if(array[client].bills[i].account_type == "Reembolso" || array[client].bills[i].account_type == "Credito" || array[client].bills[i].account_type == 'Credito Adiantamento') {
              rows.push({
                issuing_date: $filter('date')(array[client].bills[i].issuing_date,'dd/MM/yyyy'),
                flightLocator: array[client].bills[i].flightLocator,
                from: array[client].bills[i].account_type,
                airline: array[client].bills[i].airline,
                pax_name: array[client].bills[i].description,
                actual_value: (array[client].bills[i].actual_value * -1),
                miles_tax: array[client].bills[i].miles_tax,
                miles: array[client].bills[i].miles,
                issuing: array[client].bills[i].issuing,
                client: array[client].bills[i].client
              });
            } else {
              rows.push({
                issuing_date: $filter('date')(array[client].bills[i].issuing_date,'dd/MM/yyyy'),
                flightLocator: array[client].bills[i].flightLocator,
                from: array[client].bills[i].from + '-' + array[client].bills[i].to,
                airline: array[client].bills[i].airline,
                pax_name: array[client].bills[i].pax_name,
                actual_value: $rootScope.formatNumber(array[client].bills[i].actual_value),
                miles_tax: array[client].bills[i].miles_tax,
                miles: array[client].bills[i].miles,
                issuing: array[client].bills[i].issuing,
                client: array[client].bills[i].client
              });
            }
          }
          rows.push({});
          rows.push({});
          
          rows.push({
            pax_name: 'Total:',
            actual_value: $rootScope.formatNumber(array[client].billToReceive.valueFloat)
          });
  
          if(array[client].billToReceive.discount > 0) {
            rows.push({
              pax_name: 'Descontos:',
              actual_value: $rootScope.formatNumber(array[client].billToReceive.discount)
            });
          }
  
          rows.push({});
          rows.push({
            issuing_date: 'Vencimento:',
            flightLocator: $filter('date')(array[client].billToReceive.due_date,'dd/MM/yyyy'),
            pax_name: 'Boleto Nº '+array[client].billToReceive.doc_number
          });
  
          doc.autoTable(columns, rows, {
            theme: 'grid',
            styles: {
              fontSize: 8,
              overflow: 'linebreak'
            },
            startY: 90,
            margin: {horizontal: 10},
            bodyStyles: {valign: 'top'},
            createdCell: function (cell, data) {
              if (data.column.dataKey === 'actual_value') {
                cell.styles.halign = 'right';
              }
            }
          });
          doc.addPage();
        }

        doc.save('BORDEROS.pdf');
      };

      $scope.fildDateNotBlack = function(date) {
        return new Date(date);
      }

      $scope.getClass = function(billreceive) {
        if(billreceive.account_type == "Reembolso" || billreceive.account_type == "Credito" || billreceive.account_type == 'Credito Adiantamento') {
          return "label label-danger";
        }
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.filter = {};
        $scope.wizardBillet = false;
        $scope.billetGenerated = false;
        $scope.possibleCloseBillets = false;
        $rootScope.modalOpen = false;
        cfpLoadingBar.start();
        $scope.billetsDivision = [];
        $scope.buttonEnable = false;

        $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;
          cfpLoadingBar.complete();
        });

        $scope.yesterdayBills();
        $scope.findBilling();

        $scope.uploader = new FileUploader();
        $scope.uploader.url = "../backend/application/index.php?rota=/saveFile";
        $scope.uploader.autoUpload = true;
        $scope.uploader.filters.push({
            name: 'customFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 10;
            }
        });
      };
      return init();
    }
  ]);
})();
;
