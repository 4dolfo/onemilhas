(function () {
  'use strict';
  angular.module('app.table').controller('CustomerCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', 'FileUploader', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger, FileUploader) {
      var init;
      var original;
      $scope.clientStatus = ['Pendente', 'Aprovado', 'Bloqueado', 'Reprovado', 'Arquivado', 'Antecipado/Bloqueado'];
      $scope.clientPayments = ['Boleto', 'Antecipado'];
      $scope.clientBilling = ['Diario', 'Semanal', 'Quinzenal', 'Mensal', 'Outro'];
      $scope.searchKeywords = '';
      $scope.searchBilletsClient = '';
      $scope.filteredClients = [];
      $scope.row = '';
      $scope.partners = [{}, {}];

      $scope.select = function(page) {
        // var end, start;
        // start = (page - 1) * $scope.numPerPage;
        // end = start + $scope.numPerPage;
        // return $scope.currentPageClients = $scope.filteredClients.slice(start, end);
        $scope.loadClient();
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

      $scope.showBillingPeriod = function() {
        if($scope.selected) {
          return $scope.selected.billingPeriod == 'Diario' || $scope.selected.billingPeriod == 'Semanal' || $scope.selected.billingPeriod == 'Quinzenal' || $scope.selected.billingPeriod == 'Mensal' || $scope.selected.billingPeriod == '' || $scope.selected.billingPeriod == undefined;
        }
      };

      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.search = function() {
        $scope.filteredClients = $filter('filter')($scope.clients, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.findColor = function(date){
        if(date) {
          date = new Date(date);
          date.setDate(date.getDate() + 2);
          date.setHours(0,0,0,0);
          if(date > $scope.actual_date) {
            return '#50A52F';
          } else {
            date.setDate(date.getDate() + 1);
            if(date > $scope.actual_date) {
              return '#D6CE62';
            } else {
              return '#E04545';
            }
          }
        }
      };

      $scope.removeClient = function() {
       $.post("../backend/application/index.php?rota=/removeClient", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.cancelEdit();
        });
      };

      $scope.setSelected = function() {
        original = angular.copy(this.client);
        $scope.selected = this.client;
        $scope.selected.last_emission = new Date($scope.selected.last_emission);
        $scope.selected.birthdate = new Date($scope.selected.birthdate);
        $scope.selected.registerDate = new Date($scope.selected.registerDate);
        $scope.selected.registerDate.setDate($scope.selected.registerDate.getDate() + 1);
        $scope.uploader.formData = {hashId: $scope.session.hashId};

        $scope.modification = [];
        $scope.selected._status = $scope.selected.status;
        $scope.selected._billingPeriod = $scope.selected.billingPeriod;
        $scope.selected._paymentType = $scope.selected.paymentType;
        $scope.selected._paymentDays = $scope.selected.paymentDays;
        $scope.selected._partnerLimit = $scope.selected.partnerLimit;

        $.post("../backend/application/index.php?rota=/loadBilletsReceive", {hashId: $scope.session, data: {client: $scope.selected.name}}, function(result){
          $scope.billets = jQuery.parseJSON(result).dataset.billetreceive;
          $scope.buildCharts();
          $scope.searchBillets();
          $scope.loadBillingProgress();
          $scope.isTable = false;
          $scope.uploader.formData = {hashId: $scope.session.hashId, data: $scope.selected};
          $scope.$apply();
        });

        $.post("../backend/application/index.php?rota=/loadActualClientLimit", { hashId: $scope.session.hashId, data: $scope.selected }, function(result){
          $scope.ActualLimit = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });

        $.post("../backend/application/index.php?rota=/loadCommercialProgress", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.ClientLog = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });

        $.post("../backend/application/index.php?rota=/loadClientAnalisys", { data: $scope.selected }, function(result){
          $scope.finnacialInfo = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.clientModify = function(data) {
        if($scope.modification.indexOf(data) < 0) {
          $scope.modification.push(data);
        }
      };

      $scope.buildCharts = function () {
        $.post("../backend/application/index.php?rota=/loadClientBilletsChart", {hashId: $scope.session, data: $scope.selected}, function(result){
          $scope.clientBillets = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.searchBillets = function () {
        $scope.filteredBillets = $filter('filter')($scope.billets, $scope.searchBilletsClient);
      };

      $scope.loadBillingProgress = function() {
        $.post("../backend/application/index.php?rota=/loadBillingProgress", {hashId: $scope.session, data: $scope.selected}, function(result){
          $scope.billingRecord = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.getTag = function (client) {
        switch (client.billingPeriod) {
          case 'Quinzenal':
            return "label label-warning";
          case 'Mensal':
            return "label label-warning";
          default: return "label label-info";
        }
      };

      $scope.printFill = function() {
        var columns = [
          { title: "ID", dataKey: "ID" },
          { title: "Nome", dataKey: "Nome" },
          { title: "Prazo", dataKey: "Prazo" },
          { title: "Multa", dataKey: "Multa" },
          { title: "Email", dataKey: "Email" },
          { title: "Telefone", dataKey: "Telefone" },
          { title: "Status", dataKey: "Status" },
        ];
        
        var rows = [];
        for (let i in $scope.clients) {
          var c = $scope.clients[i];
          
          let tel = c.phoneNumber;
          if(c.phoneNumber2 != ''){
            tel += "\n" + c.phoneNumber2;
            if(c.phoneNumber3 != ''){
              tel += "\n" + c.phoneNumber3;
            }
          }
          tel = tel.replaceAll("|", "\n");
          tel = tel.replaceAll("/", "\n");
          let email = c.email.replaceAll(";", "\n").toLowerCase();

          rows.push({
            ID: c.id,
            Nome: c.name,
            Prazo: c.paymentType.toString() + "-" + c.paymentDays.toString(),
            Multa: c.mulct,
            Email: email,
            Telefone: tel,
            Status: c.status,
          });
        } 
        return [columns, rows];
      }

      $scope.print = function (isSave) {
        var resp = $scope.printFill();
        if(isSave){
          var doc = new jsPDF("p", "pt");
          doc.margin = 0.5;
          doc.setFontSize(18);

          var columns = resp[0];
          var rows = resp[1];

          doc.autoTable(columns, rows, {
            theme: "grid",
            styles: {
              fontSize: 6,
              overflow: "linebreak",
            },
            //startY: 90,
            margin: { horizontal: 10 },
            bodyStyles: { valign: "top", halign: "left" }
          });
          doc.save("Relatório_Clientes.pdf");
        }
        return JSON.stringify(resp);
      };

      $scope.toggleFormTable = function() {
        $scope.isTable = !$scope.isTable;
        return console.log($scope.isTable);
      };

      $scope.order = function(rowName) {
        // if ($scope.row === rowName) {
        //   return;
        // }
        // $scope.row = rowName;
        // $scope.filteredClients = $filter('orderBy')($scope.clients, rowName);
        // return $scope.onOrderChange();
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadClient();
      };

      $scope.orderDown = function(rowName) {
        // rowName = '-' + rowName
        // if ($scope.row === rowName) {
        //   return;
        // }
        // $scope.row = rowName;
        // $scope.filteredClients = $filter('orderBy')($scope.clients, rowName);
        // return $scope.onOrderChange();
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadClient();
      };

      $scope.loadClient = function() {
        $scope.newUpload = false;
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadClient", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function(result){
            $scope.clients = jQuery.parseJSON(result).dataset.clients;
            $scope.totalData = jQuery.parseJSON(result).dataset.total;
            $scope.$digest();
            // $scope.search();
            cfpLoadingBar.complete();
            // return $scope.select($scope.currentPage);
        });
      };

      $scope.blockClient = function(client) {
        if(client.status =='Bloqueado') {
          client.status = 'Aprovado';
        } else client.status = 'Bloqueado';
        $.post("../backend/application/index.php?rota=/saveClient", {hashId: $scope.session.hashId, data: client, partners: $scope.partners}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadClient();
        });
      };

      $scope.intencionToChangeStatus = function(client) {
        console.log("click");
        $rootScope.$emit('openCustomerStatusLogModal', client);
      };

      $scope.saveClient = function() {
        // if ($scope.form_client.$valid) {
          $scope.selected.cityfullname = $scope.selected.city + ', ' + $scope.selected.state;
          if ($scope.selected.registrationCode.length <= 11) {
            if (!$rootScope.ValidaCPF($scope.selected.registrationCode)) {
              return logger.logError('CPF Inválido');
            }
          } else {
            if (!$rootScope.ValidaCNPJ($scope.selected.registrationCode)) {
              return logger.logError('CNPJ Inválido');
            }
          }
          if($scope.selected.birthdate !== "" && $scope.selected.birthdate != 'Invalid Date') {
            $scope.selected._birthdate = $rootScope.formatServerDate($scope.selected.birthdate);
          }
          if($scope.selected.registerDate !== "" && $scope.selected.registerDate != 'Invalid Date') {
            $scope.selected._registerDate = $rootScope.formatServerDate($scope.selected.registerDate);
          }
          for(var i in $scope.partners){
            if($scope.partners[i].birthdate !== "")
              $scope.partners[i]._birthdate = $rootScope.formatServerDate($scope.partners[i].birthdate);
          }
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/saveClient", {hashId: $scope.session.hashId, data: $scope.selected, partners: $scope.partners}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadClient();
          });
          $scope.isTable = !$scope.isTable;
          cfpLoadingBar.complete();
        // }
      };

      $scope.intencionToSave = function() {
        var changeDetected = false;
        $scope.saveClient();
      };

      $scope.getStatusDesc = function(status) {
        switch (status) {
          case 'B':
            return "Pago";
          case 'E':
            return "Em Aberto";
          case 'A':
            return "Em Aberto";
          case 'C':
            return "Cancelado";
        }
      };

      $scope.findDate = function(date){
        if(date != '')
          return new Date(date);
        else return ''
      };

      $scope.cancelEdit = function() {
        $scope.isTable = true;
        $scope.newUpload = false;
        $scope.mail = false;
        $scope.partners = [{}, {}];
      };

      $scope.toggleNewUpload = function(){
        if ($scope.newUpload) {
          $scope.newUpload = false;
        } else{
          $scope.newUpload = true;
        }
      };

      $scope.loadFiles = function(){
        $scope.filesLoaded = new FileReader();
        $.post("../backend/application/index.php?rota=/loadFiles", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.filesLoaded = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.fillEmailContent = function() {
        $scope.selected = this.client;
        $scope.mail = true;
        $scope.partner.emailpartner = $scope.selected.email;
        $scope.partner.mailcc = "financeiro@onemilhas.com.br";
        $scope.partner.subject = "[FINANCEIRO] - One Milhas";
        $scope.partner.emailContent = "<br><br><br>";
      };

      $scope.mailOrder = function() {
        var file = [];
        for(var j in $scope.uploader.queue) {
          file.push($scope.uploader.queue[j].file.name);
        }

        if($scope.uploader.queue > 0) {
          $scope.uploader.uploadAll();
          $scope.uploader.onCompleteAll = function() {

            $.post("../backend/application/index.php?rota=/mailOrder", {hashId: $scope.session.hashId, data: $scope.partner, attachment: file, type: 'FINANCEIRO', emailType: 'EMAIL-GMAIL'}, function(result){
              if (jQuery.parseJSON(result).message.type == 'S'){
                logger.logSuccess(jQuery.parseJSON(result).message.text);
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }
              $scope.cancelEdit();
              $scope.partner = {};
              $scope.uploader.clearQueue();
            });
          };

        } else {

          $.post("../backend/application/index.php?rota=/mailOrder", {hashId: $scope.session.hashId, data: $scope.partner, attachment: file, type: 'FINANCEIRO', emailType: 'EMAIL-GMAIL'}, function(result){
            if (jQuery.parseJSON(result).message.type == 'S'){
              logger.logSuccess(jQuery.parseJSON(result).message.text);
            } else {
              logger.logError(jQuery.parseJSON(result).message.text);
            }
            $scope.cancelEdit();
            $scope.partner = {};
            $scope.uploader.clearQueue();
          });
        }

      };

      $scope.removeFile = function (file) {
        $.post("../backend/application/index.php?rota=/removeFile", {hashId: $scope.session.hashId, data: $scope.selected, file: file}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadFiles();
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageClients = [];
      init = function() {
        $scope.actual_date = new Date();
        $scope.isTable = true;
        $rootScope.modalOpen = false;
        $scope.checkValidRoute();
        $scope.newProgress = {};
        cfpLoadingBar.start();
        $scope.mail = false;

        $scope.clientBillets = [
          {data: 0, label: "Não Atrasados / Adiantados"},
          {data: 0, label: "Atrasados"}
        ];

        $scope.options = {
          series: {
            pie: {
              show: true,
              innerRadius: 0.25
            }
          },
          legend: {
            show: false
          },
          grid: {
            hoverable: true,
            clickable: false
          },
          colors: ["#176799", "#2F87B0", "#42A4BB", "#5BC0C4", "#78D6C7", "#56B176", "#15582D", "#547D63", "#299431", "#906D38", "#903875", "#E21414", "#E1E41E", "#4CB938"],
          tooltip: true,
          tooltipOpts: {
            content: "%p.0%, %s",
            defaultTheme: false
          }
        };

        $.post("../backend/application/index.php?rota=/loadState", $scope.session, function(result){
            $scope.states = jQuery.parseJSON(result).dataset;
        });

        $('#clientstate').on('blur', function(obj, datum) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadCity", {hashId: $scope.session.hashId, state: $scope.selected.state}, function(result){
            $scope.cities = jQuery.parseJSON(result).dataset;
          });
          cfpLoadingBar.complete();
        });

        $('#partnerState').on('blur', function(obj, datum) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadCity", {hashId: $scope.session.hashId, state: $scope.selected.state}, function(result){
            $scope.citiesPartners = jQuery.parseJSON(result).dataset;
          });
          cfpLoadingBar.complete();
        });

        $('#clientstatefinnancial').on('blur', function(obj, datum) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadCity", {hashId: $scope.session.hashId, state: $scope.selected.cityFinnancialState}, function(result){
            $scope.citiesFinnancial = jQuery.parseJSON(result).dataset;
          });
          cfpLoadingBar.complete();
        });

        $scope.filter = { status: 'Bloqueado'} ;
        $scope.loadClient();

        $scope.uploader = new FileUploader();
        $scope.uploader.url = "../backend/application/index.php?rota=/saveFile";

        $scope.uploader.filters.push({
            name: 'customFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 10;
            }
        });

      };

      $scope.registerLog = function() {
        $.post("../backend/application/index.php?rota=/saveCommercialProgress", {data: $scope.selected, progress: $scope.newProgress}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.newProgress = {};
            $.post("../backend/application/index.php?rota=/loadCommercialProgress", {data: $scope.selected}, function(result){
                $scope.ClientLog = jQuery.parseJSON(result).dataset;
                $scope.$digest();
            });
        });
      };

      $scope.openModalSearch = function() {
        $.post("../backend/application/index.php?rota=/loadDealers", {}, function(result){
          $scope.dealers = jQuery.parseJSON(result).dataset;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "ClientsModalDemoCtrl.html",
            controller: 'ClientsModalInstanceCtrl',
            periods: $scope.periods,
            resolve: {
              filter: function() {
                return $scope.filter;
              },
              states: function() {
                return $scope.states;
              },
              dealers: function() {
                return $scope.dealers;
              }
            }
          });
          modalInstance.result.then((function(filter) {
            filter._fromDate = $rootScope.formatServerDate(filter.fromDate);
            filter._toDate = $rootScope.formatServerDate(filter.toDate);
            filter._notFromDate = $rootScope.formatServerDate(filter.notFromDate);
            filter._notToDate = $rootScope.formatServerDate(filter.notToDate);
            filter._registerFromDate = $rootScope.formatServerDate(filter.registerFromDate);
            filter._registerToDate = $rootScope.formatServerDate(filter.registerToDate);
            $scope.loadClient();
          }), function() {
            $log.info("Modal dismissed at: " + new Date());
          });
        });
      };

      return init();
    }
  ]).controller('BillingRecordModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', 'logger', function($scope, $rootScope, $modal, $log, logger) {
      
      $scope.saveBilling = function(billing) {
        $.post("../backend/application/index.php?rota=/saveBillingProgress", {hashId: $scope.$parent.$parent.$parent.$parent.session.hashId, data: $scope.clientSelected, billing: billing}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.open = function() {
        $scope.clientSelected = this.client;

        $.post("../backend/application/index.php?rota=/loadBillingProgress", {hashId: $scope.session, data: $scope.clientSelected}, function(result){
          $scope.billingRecord = jQuery.parseJSON(result).dataset;

          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "BillingRecordModalCtrl.html",
            controller: 'BillingRecordModalInstanceCtrl',
            periods: $scope.$parent.periods,
            resolve: {
              billingRecord: function() {
                return $scope.billingRecord;
              }
            }
          });
          modalInstance.result.then((function(billing) {
            $scope.saveBilling(billing);
          }));

        });

      };
    }
  ]).controller('BillingRecordModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'billingRecord', function($scope, $rootScope, $modalInstance, billingRecord) {

      $scope.billingRecord = billingRecord;
      $scope.findDate = function(date) {
        return new Date(date);
      };

      $scope.billing = {description: ''};
      $scope.ok = function() {
        $modalInstance.close($scope.billing);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).directive('flotClientBilletsChart', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function(scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.$parent.$parent.clientBillets;
          plot = $.plot(ele[0], data, options);
          update = function() {
            plot.setData(scope.$parent.$parent.$parent.clientBillets);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function(){
            plot.setData(scope.$parent.$parent.$parent.clientBillets);
            plot.draw();
            updateInterval = 2000;
            setTimeout(finish, updateInterval);
          };
          updateInterval = 1000;
          return update();
        }
      };
    }
  ]).controller('CustomerLogModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $rootScope.modalOpen = false;

      $rootScope.$on('openCustomerLogModal', function(event, args) {
        event.stopPropagation();
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function(args) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "CustomerLogModalCtrl.html",
          controller: 'CustomerLogInstanceCtrl',
          resolve: {
          }
        });
        modalInstance.result.then(function(resolve) {
          $scope.$parent.selected.resolveDescription = resolve.resolveDescription;
          $scope.$parent.saveClient();
          $rootScope.modalOpen = false;
        }, function() {
          $rootScope.modalOpen = false;
        });
      };

    }
  ]).controller('CustomerLogInstanceCtrl', [
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
  ]).controller('CustomerStatusLogModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $rootScope.$on('openCustomerStatusLogModal', function(event, args) {
        console.log("click");
        console.log($rootScope.modalOpen);
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function(args) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "CustomerStatusLogModal.html",
          controller: 'CustomerStatusLogInstanceCtrl',
          resolve: {
            selected: function() {
              return args;
            }
          }
        });
        modalInstance.result.then(function(resolve) {
          // $scope.$parent.selected.resolveDescription = resolve.resolveDescription;
          $scope.$parent.blockClient(resolve);
          $rootScope.modalOpen = false;
        }, function() {
          $rootScope.modalOpen = false;
        });
      };

    }
  ]).controller('CustomerStatusLogInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'selected', function($scope, $rootScope, $modalInstance, logger, selected) {
      $scope.selected = selected;

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