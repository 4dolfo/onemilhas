(function () {
  'use strict';
  angular.module('app.billsReceive').controller('BilletsReceiveCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', '$window', 'cfpLoadingBar', 'logger', 'FileUploader', function($scope, $rootScope, $filter, $modal, $window, cfpLoadingBar, logger, FileUploader) {
      var init;
      var tabindex;
      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.banks = ['BRADESCO', 'SANTANDER'];
      $scope.searchKeywords = '';
      $scope.filteredBilletsReceive = [];
      $scope.row = '';
      $scope.totalFilteredValue = 0;
      $scope.filter = {};
      $scope.filter2 = {
        days: 0,
        daysBils: 0,
        enterPressed: false
      };
      $scope.detalhes = false;
      $scope.pesqSelect = '0';
      $scope.totalPreco = 0;
      $scope.totalDataMark = 0;
      $scope.totalPrecoMark = 0;
      $scope.payment_date = '';
      
      $scope.select = function(page) {
        // var end, start;
        // start = (page - 1) * $scope.numPerPage;
        // end = start + $scope.numPerPage;
        // return $scope.currentPageBilletsReceive = $scope.filteredBilletsReceive.slice(start, end);
        $scope.loadBillets();
      };

      $scope.onFilter2EnterKey = function(keyCode) {
        if($scope.filter2.days){
          $scope.filter2.enterPressed = false;
          if(keyCode == 13){
            $scope.filter2.enterPressed = true;
            $scope.loadData();
          }
        }
      };

      $scope.onFilter2Blur = function() {
        if($scope.filter2.days){
          if(!$scope.filter2.enterPressed){
            $scope.loadData();
          }
        }
      };
      
      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };
      
      $scope.onNumPerPageChange = function() {
        // $scope.select(1);
        // return $scope.currentPage = 1;
        $scope.loadBillets();
      };
      
      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };
      
      $scope.back = function() {
        $scope.tabindex = 0;
        $scope.selected = {};
        $scope.billReceive = {};
        $scope.bill = false;
        $scope.receive = false;
        $scope.debits = false;
        $scope.Anticipated = false;
        $scope.clientAnalisys = false;
        $scope.agreement = false;
        $scope.billetAgreement = {};
      };

      $scope.newBillAdvance = function() {
        $scope.tabindex = 4;
        $scope.bill = true;
        $scope.billReceive = {};
        $scope.billReceive.type = 'advance';
        $("#value").maskMoney({thousands:'.',decimal:',', precision: 2});
        $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;          
        });
      };

      $scope.newBill = function(){
        $scope.tabindex = 4;
        $scope.bill = true;
        $scope.billReceive = {};
        $("#value").maskMoney({thousands:'.',decimal:',', precision: 2});
        $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.newBillReceive = function(){
        $scope.tabindex = 4;
        $scope.receive = true;
        $scope.billReceive = {description: '', client: '', actual_value: 0};
        $("#valueBillReceive").maskMoney({thousands:'.',decimal:',', precision: 2});
        $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.generateBill = function(){
        $scope.billReceive._dueDate = $rootScope.formatServerDate($scope.billReceive.due_date);
        $scope.billReceive.actual_value = $('#value').maskMoney('unmasked')[0];
        $.post("../backend/application/index.php?rota=/generateBillReceive", { data: $scope.billReceive }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadData();
          $scope.billReceive = {};
          $scope.bill = false;
          $scope.tabindex = 0;
        });
      };

      $scope.generateBillReceive = function(){
        $scope.billReceive.actual_value = $('#valueBillReceive').maskMoney('unmasked')[0];
        $.post("../backend/application/index.php?rota=/generateBillClient", {hashId: $scope.session.hashId, data: $scope.billReceive}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadData();
          $scope.billReceive = {};
          $scope.receive = false;
          $scope.tabindex = 0;
        });
      };
      
      $scope.setSelected = function() {
        $scope.selected = angular.copy(this.billetreceive);
        $scope.tabindex = 1;
        $scope.loadBilletBills();
        $scope.selected._due_date = new Date($scope.selected.due_date);
        // $scope.selected._due_date.setDate($scope.selected._due_date.getDate() + 1);

        // if($scope.selected.status == "E" && ($scope.selected._due_date < new Date())) {
        //   // $scope.selected.actual_value += (parseInt((new Date() - $scope.selected._due_date) / 1000 / 60 / 60 / 24) * (($scope.selected.mulct / 100) * $scope.selected.actual_value));
        //   var days = parseInt((new Date() - $scope.selected._due_date) / 1000 / 60 / 60 / 24);
        //   var mulct = parseFloat(($scope.selected.mulct / 100) * $scope.selected.actual_value);

        //   $scope.selected.actual_value = parseFloat($scope.selected.actual_value) + (days * mulct);
        // }

        // $scope.selected.actual_value = parseFloat($scope.selected.actual_value).toFixed(2);

        $('#original').number( true, 2, ',', '.');

        // $('#actual').number( true, 2, ',', '.');
        $scope.selected.actual_value = $rootScope.formatNumber($scope.selected.actual_value);
        $("#actual").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#actual").maskMoney('mask', $scope.selected.actual_value);

        // $('#tax').number( true, 2, ',', '.');
        $("#tax").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#tax").maskMoney('mask', $scope.selected.tax);

        // $('#discount').number( true, 2, ',', '.');
        $("#discount").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#discount").maskMoney('mask', $scope.selected.discount);

        $scope.uploader.formData = {hashId: $scope.session.hashId};

        $.post("../backend/application/index.php?rota=/loadDivisionsBillet", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.billetsDivision = jQuery.parseJSON(result).dataset;
          for(var i in $scope.billetsDivision) {
            $scope.billetsDivision[i].dueDate = new Date($scope.billetsDivision[i].dueDate);
            $scope.billetsDivision[i].dueDate.setDate($scope.billetsDivision[i].dueDate.getDate() + 1);
          }
          $scope.$apply();
        });
      };

      $scope.printReport = function() {
        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(18);

        var columns = [
          {title: "DATA", dataKey: "issuing_date"},
          {title: "LOC", dataKey: "flightLocator"},
          {title: "ORIGEM", dataKey: "from"},
          {title: "CIA", dataKey: "airline"},
          {title: "PAX", dataKey: "pax_name"},
          {title: "VALOR", dataKey: "actual_value"},
          {title: "EMISSOR", dataKey: "issuing"},
          {title: "CLIENTE", dataKey: "client"}
        ];

        var rows = [];
        var total = 0;
        
        for (var i = 0; i < $scope.billsreceive.length; i++) {

          if($scope.billsreceive[i].account_type == "Reembolso" || $scope.billsreceive[i].account_type == "Credito" || $scope.billsreceive[i].account_type == "Credito Adiantamento") {

            total -= $scope.billsreceive[i].actual_value;
            rows.push({
              issuing_date: $filter('date')($scope.billsreceive[i].issuing_date,'dd/MM/yyyy'),
              flightLocator: $scope.billsreceive[i].flightLocator,
              from: $scope.billsreceive[i].account_type,
              airline: $scope.billsreceive[i].airline,
              pax_name: $scope.billsreceive[i].description,
              actual_value: ($scope.billsreceive[i].actual_value * -1),
              issuing: $scope.billsreceive[i].issuing,
              client: $scope.billsreceive[i].client
            });

          } else {

            total += $scope.billsreceive[i].actual_value;
            rows.push({
              issuing_date: $filter('date')($scope.billsreceive[i].issuing_date,'dd/MM/yyyy'),
              flightLocator: $scope.billsreceive[i].flightLocator,
              from: $scope.billsreceive[i].from + '-' + $scope.billsreceive[i].to,
              airline: $scope.billsreceive[i].airline,
              pax_name: $scope.billsreceive[i].pax_name,
              actual_value: $scope.billsreceive[i].actual_value,
              issuing: $scope.billsreceive[i].issuing,
              client: $scope.billsreceive[i].client
            });

          }
        }
        rows.push({});
        rows.push({});
        rows.push({
          pax_name: 'Total:',
          actual_value: $rootScope.formatNumber($scope.selected.actual_value)
        });

        rows.push({});
        if($scope.selected.discount > 0) {
          rows.push({
            pax_name: 'Descontos:',
            actual_value: $rootScope.formatNumber($scope.selected.discount)
          });
        }

        rows.push({});
        rows.push({
          issuing_date: 'Vencimento:',
          flightLocator: $filter('date')($scope.selected.due_date,'dd/MM/yyyy'),
          pax_name: 'Boleto Nº '+$scope.selected.ourNumber
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
        doc.save('BORDERO '+$scope.billsreceive[0].client+'.pdf');
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

      $scope.printReportModel = function() {
        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(16);
        doc.setTextColor(0);
        doc.setFontStyle('bold');
        doc.text('IDEAL VIAGENS', 200, 50);

        var start = 60;
        doc.autoTable([{title: '', dataKey: "text"}], [
          { text: 'PAULO RDRIGUES DOS SANTOS JUNIOR MMS VIAGENS' },
          { text: 'CNPJ: 20.966.716/0001-55' },
          { text: 'Rua: Jurua, 46 Conj. 301 Bairro: Graça' },
          { text: 'CEP: 31.140-020 BELO HORIZONTE / MG' },
          { text: 'Tel: 31.3017-0304  E-mail: ' }
        ], {
          theme: 'plain',
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
            
          },
          startY: start,
          margin: {horizontal: 10},
          bodyStyles: {valign: 'top'}
        });

        start = doc.autoTableEndPosY() + 20;
        doc.autoTable([
          {title: 'Nº Borderô', dataKey: "ourNumber"},
          {title: 'Valor da Fatura / Borderô', dataKey: "actual_value"},
          {title: 'Data Emissão', dataKey: "issue_date"},
          {title: 'Data Vencimento', dataKey: "due_date"}
          ], [
          { ourNumber: $scope.selected.ourNumber,
            actual_value: 'R$ ' + $rootScope.formatNumber($scope.selected.actual_value),
            issue_date: $filter('date')(new Date($scope.selected.issue_date),'dd/MM/yyyy'),
            text: $filter('date')(new Date($scope.selected.due_date),'dd/MM/yyyy')
          }], {
          theme: 'plain',
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
          },
          startY: start,
          margin: {horizontal: 10},
          bodyStyles: {valign: 'top'}
        });

        start = doc.autoTableEndPosY() + 10;
        doc.autoTable([
          {title: '', dataKey: "text"}], [
          { text: 'Sacado: ' + $scope.selected.company_name },
          { text: 'Endereço: ' + $scope.selected.adress },
          { text: 'Tel: ' + $scope.selected.phoneNumber },
          { text: 'CNPJ: ' + $scope.selected.registrationCode },
          { text: '' },
          { text: 'Banco: 237- Bradesco - Agência: 3420 Conta Corrente: 4879-8' }
          ], {
          theme: 'plain',
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
          },
          startY: start,
          margin: {horizontal: 10},
          bodyStyles: {valign: 'top'}
        });

        var columns = [
          {title: "DATA", dataKey: "issuing_date"},
          {title: "LOC", dataKey: "flightLocator"},
          {title: "TRECHO", dataKey: "from"},
          {title: "CIA", dataKey: "airline"},
          {title: "SOLICITANTE", dataKey: "issuing"},
          {title: "PAX", dataKey: "pax_name"},
          {title: "VALOR", dataKey: "actual_value"}
        ];

        var rows = [];
        var total = 0;
        
        for (var i = 0; i < $scope.billsreceive.length; i++) {

          if($scope.billsreceive[i].account_type == "Reembolso" || $scope.billsreceive[i].account_type == "Credito" || $scope.billsreceive[i].account_type == "Credito Adiantamento") {

            total -= $scope.billsreceive[i].actual_value;
            rows.push({
              issuing_date: $filter('date')($scope.billsreceive[i].issuing_date,'dd/MM/yyyy'),
              flightLocator: $scope.billsreceive[i].flightLocator,
              from: $scope.billsreceive[i].account_type,
              airline: $scope.billsreceive[i].airline,
              pax_name: $scope.billsreceive[i].description,
              actual_value: ($scope.billsreceive[i].actual_value * -1),
              issuing: $scope.billsreceive[i].issuing
            });

          } else {

            total += $scope.billsreceive[i].actual_value;
            rows.push({
              issuing_date: $filter('date')($scope.billsreceive[i].issuing_date,'dd/MM/yyyy'),
              flightLocator: $scope.billsreceive[i].flightLocator,
              from: $scope.billsreceive[i].from + '-' + $scope.billsreceive[i].to,
              airline: $scope.billsreceive[i].airline,
              pax_name: $scope.billsreceive[i].pax_name,
              actual_value: $scope.billsreceive[i].actual_value,
              issuing: $scope.billsreceive[i].issuing
            });

          }
        }
        rows.push({});
        rows.push({
          pax_name: 'Total:',
          actual_value: $rootScope.formatNumber(total)
        });

        rows.push({});
        if($scope.selected.discount > 0) {
          rows.push({
            pax_name: 'Descontos:',
            actual_value: $rootScope.formatNumber($scope.selected.discount)
          });
        }

        start = doc.autoTableEndPosY() + 20;
        doc.autoTable(columns, rows, {
          theme: 'plain',
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
          },
          startY: start,
          margin: {horizontal: 10},
          bodyStyles: {valign: 'top'},
          createdCell: function (cell, data) {
            if (data.column.dataKey === 'actual_value') {
              cell.styles.halign = 'right';
            }
          }
        });
        doc.save('BORDERO '+$scope.billsreceive[0].client+'.pdf');
      };
      
      $scope.print = function(){
        var doc = new jsPDF('l', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(18);

        var columns = [
          {title: "DATA", dataKey: "issuing_date"},
          {title: "BORDERO", dataKey: "billet"},
          {title: "CLIENTE", dataKey: "client"},
          {title: "VALOR", dataKey: "actual_value"},
          {title: "VENCIMETO", dataKey: "due_date"},
          {title: "LOC", dataKey: "flightLocator"},
          {title: "ORIGEM", dataKey: "from"},
          {title: "CIA", dataKey: "airline"},
          {title: "PAX", dataKey: "pax_name"},
          {title: "EMISSOR", dataKey: "issuing"}
        ];

        var rows = [];

        $.post("../backend/application/index.php?rota=/loadBilletsReceive", {data: $scope.filter}, function(result){
            $scope.billetsreceive = jQuery.parseJSON(result).dataset.billetreceive;

            for(var j in $scope.billetsreceive) {

              $.post("../backend/application/index.php?rota=/loadBilletBills", {data: $scope.billetsreceive[j]}, function(result){
                $scope.billsreceive = jQuery.parseJSON(result).dataset;

                // rows.push({
                //   issuing_date: $filter('date')($scope.billsreceive[j].issuing_date,'dd/MM/yyyy'),
                //   billet: $scope.billsreceive[j].ourNumber,
                //   client: $scope.billsreceive[j].client,
                // });

                rows.push({});
                for(var i in $scope.billsreceive) {

                  if($scope.billsreceive[i].account_type == "Reembolso" || $scope.billsreceive[i].account_type == "Credito"  || $scope.billsreceive[i].account_type == "Credito Adiantamento") {

                    rows.push({
                      issuing_date: $filter('date')($scope.billsreceive[i].issuing_date,'dd/MM/yyyy'),
                      flightLocator: $scope.billsreceive[i].flightLocator,
                      from: $scope.billsreceive[i].account_type,
                      airline: $scope.billsreceive[i].airline,
                      pax_name: $scope.billsreceive[i].description,
                      actual_value: $scope.billsreceive[i].actual_value,
                      issuing: $scope.billsreceive[i].issuing,
                    });

                  } else {

                    rows.push({
                      issuing_date: $filter('date')($scope.billsreceive[i].issuing_date,'dd/MM/yyyy'),
                      flightLocator: $scope.billsreceive[i].flightLocator,
                      from: $scope.billsreceive[i].from + '-' + $scope.billsreceive[i].to,
                      airline: $scope.billsreceive[i].airline,
                      pax_name: $scope.billsreceive[i].pax_name,
                      actual_value: $scope.billsreceive[i].actual_value,
                      issuing: $scope.billsreceive[i].issuing,
                    });

                  }
                }
              });

              rows.push({});
              rows.push({});
            }
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
            doc.save('Resumo debitos '+$scope.billsreceive[0].client+'.pdf');

        });
      };

      $scope.cancelBillet = function() {
        $.post("../backend/application/index.php?rota=/cancelBillet", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadData();
        });
      };

      $scope.attMarked = function(){
        $scope.totalDataMark = 0;
        $scope.totalPrecoMark = 0;
        for(let i in $scope.billetsreceive){
          if($scope.billetsreceive[i].checked){
            $scope.totalDataMark += 1;
            $scope.totalPrecoMark += $scope.billetsreceive[i].actual_value - $scope.billetsreceive[i].alreadyPaid;
          }
        }
      };

      $scope.addRow = function() {
        if (this.billetreceive.status == 'B') {
          this.billetreceive.checked = false;
        }
        $scope.attMarked();
      };

      $scope.search = function() {
        $scope.filteredBilletsReceive = $filter('filter')($scope.billetsreceive, $scope.searchKeywords);
        if($scope.filteredBilletsReceive.length == 1 && $scope.filter._dueDateFrom != undefined && $scope.filter._dueDateTo != undefined) {
          $scope.filteredBilletsReceive[0].checked = true;
        }
        $scope.totalFilteredValue = $scope.getTotalFiltered();
        $scope.$apply();
        return $scope.onFilterChange();
      };
      
      $scope.order = function(rowName) {
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadBillets();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadBillets();
      };
      
      $scope.loadData = function() {
        if($scope.main.isMaster){
          cfpLoadingBar.start();
          var q = 8;
          $.post("../backend/application/index.php?rota=/loadSumOpenedBillsReceive", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumOpenedbillsreceive = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadSumClosedBilletsReceive", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumClosedbilletsreceive = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadSumBilletsHasBillets", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumBilletsHasBillets = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadSumBilletsDontHasBillets", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumBilletsDontHasBillets = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadSumBilletsLosses", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumBilletsLosses = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadSumBilletsToDue", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumBilletsToDue = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadSumBilletsPast", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumBilletsPast = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadSumBilletBilletsReceive", {data: $scope.filter, days: $scope.filter2.days}, function(result){
              $scope.sumBilletbilletsreceive = jQuery.parseJSON(result).dataset;
              q-=1;
              $scope.$apply();
          });

          //A barra e espera so para de rodar quando todas as chamadas terminarem
          var q_id = setInterval(q_intervalo, 1000);
          function q_intervalo(){ 
            if(q==0){
              cfpLoadingBar.complete();
              clearInterval(q_id);
            } 
          }
        }
      };


      $scope.loadTodaysOpened = function() {
        $scope.advising = true;
        $scope.opened = false;
        $scope.debits = false;
        $scope.filter._dueDateFrom = $rootScope.formatServerDate(new Date());
        $scope.filter._dueDateTo = $rootScope.formatServerDate(new Date());
        $scope.filter.status = 'Em Aberto';
        $scope.loadBillets();
      };

      $scope.loadOpened = function() {
        $scope.advising = false;
        $scope.opened = true;
        $scope.debits = false;
        $scope.filter._dueDateTo = $rootScope.formatServerDate(new Date());
        $scope.filter._dueDateFrom = undefined;
        $scope.filter.status = 'Em Aberto';
        $scope.loadBillets();
      };

      $scope.getHojeMaisDias = function(dias){
        var today = new Date();

        if(dias != 0)
          today.setDate(today.getDate() + dias);

        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        var yyyy = today.getFullYear();
        return yyyy + '-' + mm + '-' + dd;
      };

      $scope.searchAVencer = function(){
        var v = parseInt($scope.pesqSelect, 10);
        if($scope.pesqSelect == "99"){
          v = $scope.filter2.daysBils;
        }
        else if($scope.pesqSelect == "-99"){
          v = $scope.filter2.daysBils * (-1);
        }
        if(v <= 0){
          $scope.filter = {
            dueDateFrom: new Date($scope.getHojeMaisDias(v)),
            dueDateTo: new Date(),
            _dueDateFrom: $scope.getHojeMaisDias(v),
            _dueDateTo: $scope.getHojeMaisDias(0),
            status: "Em aberto"
          };
        }
        else{
          $scope.filter = {
            dueDateFrom: new Date(),
            dueDateTo: new Date($scope.getHojeMaisDias(v)),
            _dueDateFrom: $scope.getHojeMaisDias(0),
            _dueDateTo: $scope.getHojeMaisDias(v),
            status: "Em aberto"
          };
        }
        $scope.loadBillets();
      };

      $scope.searchVencidas = function(){
        var v = parseInt($scope.pesqSelect, 10);
        if($scope.pesqSelect == "99"){
          v = $scope.filter2.daysBils;
        }
        else if($scope.pesqSelect == "-99"){
          v = $scope.filter2.daysBils * (-1);
        }
        if(v <= 0 ){
          $scope.filter = {
            dueDateFrom: new Date($scope.getHojeMaisDias(v)),
            dueDateTo: new Date(),
            _dueDateFrom: $scope.getHojeMaisDias(v),
            _dueDateTo: $scope.getHojeMaisDias(0),
            status: "Em aberto"
          };
        }
        else{
          $scope.filter = {
            dueDateFrom: new Date(),
            dueDateTo: new Date($scope.getHojeMaisDias(v)),
            _dueDateFrom: $scope.getHojeMaisDias(0),
            _dueDateTo: $scope.getHojeMaisDias(v),
            status: "Em aberto"
          };
        }
        $scope.orderDown('due_date');
      };
      
      $scope.searchPagas = function(){
        var v = parseInt($scope.pesqSelect, 10);
        if($scope.pesqSelect == "99"){
          v = $scope.filter2.daysBils;
        }
        else if($scope.pesqSelect == "-99"){
          v = $scope.filter2.daysBils * (-1);
        }
        if(v <= 0){
          $scope.filter = {
            paymentDateFrom: new Date($scope.getHojeMaisDias(v)),
            paymentDateTo: new Date(),
            _paymentDateFrom: $scope.getHojeMaisDias(v),
            _paymentDateTo: $scope.getHojeMaisDias(0),
            status: "Baixada",
            refund: true
          };
        }
        else{
          $scope.filter = {
            paymentDateFrom: new Date(),
            paymentDateTo: new Date($scope.getHojeMaisDias(v)),
            _paymentDateFrom: $scope.getHojeMaisDias(0),
            _paymentDateTo: $scope.getHojeMaisDias(v),
            status: "Baixada",
            refund: true
          };
        }
        $scope.orderDown('payment_date');
      };

      $scope.loadBillets = function() {
        cfpLoadingBar.start();
        if($scope.main.isMaster) {
          $scope.loadData();
        }
        $.post("../backend/application/index.php?rota=/loadBilletsReceive", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter}, function(result){
            $scope.billetsreceive = jQuery.parseJSON(result).dataset.billetreceive;
            $scope.totalData = jQuery.parseJSON(result).dataset.total;
            $scope.totalPreco = jQuery.parseJSON(result).dataset.totalValueNotPaid;
            // $scope.search();
            $scope.debits = false;
            $scope.showTable = true;
            cfpLoadingBar.complete();
            $scope.$digest();
            // return $scope.select($scope.currentPage);
        });
      };

      $scope.loadBilletBills = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadBilletBills", {data: $scope.selected}, function(result){
            $scope.billsreceive = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.saveCloseReceive = function() {
        $scope.checkedrows = [];
        for(var i in $scope.billetsreceive) {
          if($scope.billetsreceive[i].checked) {
            $scope.checkedrows.push($scope.billetsreceive[i]);
          }
        }
        $.post("../backend/application/index.php?rota=/saveCloseReceive", { checkedrows: $scope.checkedrows }, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            //$scope.loadClientsBloquedsWithNoPendency();
            $scope.loadModalInfoBaixa();
            $scope.loadBillets();
            // return $scope.select($scope.currentPage);
        });
      };

      $scope.loadModalInfoBaixa = function() {
        var modalInstance = $modal.open({
          templateUrl: "modalInfoBaixa.html",
          controller: 'infoBaixaModalInstanceCtrl',
          size: 'lg',
          scope: $scope,
        });
        modalInstance.result.then((function() {
          $scope.printBaixa(true);
          $scope.totalDataMark = 0;
          $scope.totalPrecoMark = 0;
        }), function() {
          $scope.totalDataMark = 0;
          $scope.totalPrecoMark = 0;
        });
      };

      $scope.loadClientsBloquedsWithNoPendency = function() {
        $.post("../backend/application/index.php?rota=/loadClientsBloquedsWithNoPendency", {}, function(result){
          $scope.clientsNoPendency = jQuery.parseJSON(result).dataset;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/notification_client_list.html",
            controller: 'NotificationClientListCtrl',
            periods: $scope.periods,
            size: 'lg',
            resolve: {
              data: function() {
                return $scope.clientsNoPendency;
              },
              header: function() {
                return 'Clientes bloqueados sem pendencia no financeiro!';
              }
            }
          });
        });
      };


      $scope.printFillBaixa = function() {
        var columns = [
          { title: "Nome", dataKey: "Nome" },
          { title: "Nº Borderô", dataKey: "Numero" },
          { title: "Valor Baixado", dataKey: "Valor" },
          { title: "Status", dataKey: "Status" },
        ];
        
        var rows = [];
        for (let i in $scope.checkedrows) {
          var c = $scope.checkedrows[i];

          rows.push({
            Nome: c.client,
            Numero: c.ourNumber,
            Valor: $rootScope.formatNumber(c.actual_value - c.alreadyPaid, 2),
            Status: c.client_status,
          });
        } 
        return [columns, rows];
      }

      $scope.printBaixa = function (isSave) {
        var resp = $scope.printFillBaixa();
        if(isSave){
          var doc = new jsPDF("p", "pt");
          doc.margin = 0.5;
          doc.setFontSize(6);

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
          let finalY = doc.autoTableEndPosY() + 12;
          let txt = "Quantidade Total: " + $scope.totalDataMark;
          doc.text(20, finalY, txt);

          let lineHeight = doc.getLineHeight(txt) / doc.internal.scaleFactor;
          txt = "Preço Total: R$" + $rootScope.formatNumber($scope.totalPrecoMark, 2);
          doc.text(20, finalY + lineHeight + 6, txt);

          if($scope.payment_date != ''){
            txt = "Data Pagamento: " + $scope.payment_date;
            doc.text(20, finalY + lineHeight + 18, txt);
          }

          doc.save("Relatório_Baixa.pdf");
        }
        return JSON.stringify(resp);
      };

      $scope.getStatusDesc = function(billetreceive) {
        if(billetreceive.actual_value < 0) {
          if(billetreceive.status == 'T')
            return "Transferencia";
          if(billetreceive.status == 'T')
            return "Cancelamento";
          return "Reembolso";
        }
        switch (billetreceive.status) {
          case 'B':
            return "Pago";
          case 'T':
            return "Transferencia";
          case 'C':
            return "Cancelamento";
          case 'E':
            var dueDate = new Date(billetreceive.due_date);
            // dueDate.setDate(dueDate.getDate() + 1);
            if(dueDate <  new Date()) {
              return "Atrasado";
            }
            return "Em Aberto";
          case 'A':
            var dueDate = new Date(billetreceive.due_date);
            // dueDate.setDate(dueDate.getDate() + 1);
            if(dueDate <  new Date()) {
              return "Atrasado";
            }
            return "Em Aberto";
          case 'C':
            return "Cancelado";
        }
      };

      $scope.billReceiveTag = function(billetreceive) {
        switch (billetreceive.status) {
          case 'B':
            return "label label-success";
          case 'E':
            return "label label-info";
          case 'A':
            return "label label-warning";
          case 'C':
            return "label label-error";
          case 'T':
            return "label label-default";
          case 'C':
            return "label label-default";
        }
      };

      $scope.fillEmailAll = function() {
        $scope.selected = this.billetreceive;

        $scope.billet.emailpartner = $scope.selected.email;
        $scope.billet.client = $scope.selected.client;
        $scope.billet.mailcc = '';
        $scope.billet.subject = 'BOLETO - ONE MILHAS - VENCIMENTO ' + $filter('date')($rootScope.findDate($scope.selected.due_date), 'dd/MM/yyyy') + '  - ' + $scope.selected.ourNumber;

        $.post("../backend/application/index.php?rota=/loadBilletBills", {data: $scope.selected}, function(result){
          $scope.billsreceive = jQuery.parseJSON(result).dataset;

          $scope.billet.emailContent = "<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'>"+
          "<tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'><b>Dados</b></font></td></tr>"+
          "<tr><td>Data</td><td>Passageiro</td><td>Localizador</td><td>Trecho</td><td>CIA</td><td>Valor</td><td>Emissor</td><td>Cliente</td>";
          if($scope.billetGenerated) {
            $scope.billet.emailContent += "<td>Bordero</td>";
          }
          $scope.billet.emailContent += "</tr>";
          $scope.checkedrows = $filter('filter')($scope.billsreceive, true);
          for (var i = 0;$scope.checkedrows.length > i; i++) {
            $scope.billet.emailContent += "<tr><td>"+$filter('date')($scope.checkedrows[i].issuing_date, 'dd/MM/yyyy')+"</td><td>";
            if($scope.checkedrows[i].account_type == "Reembolso" || $scope.checkedrows[i].account_type == "Credito" || $scope.checkedrows[i].account_type == "Credito Adiantamento"){
              $scope.billet.emailContent += $scope.checkedrows[i].description+"</td><td>";
            } else {
              $scope.billet.emailContent += $scope.checkedrows[i].pax_name+"</td><td>";
            }
            $scope.billet.emailContent += $scope.checkedrows[i].flightLocator+"</td><td>"+
            $scope.checkedrows[i].from+"-"+$scope.checkedrows[i].to+"</td><td>"+$scope.checkedrows[i].airline+"</td>";
            
            if($scope.checkedrows[i].account_type == "Reembolso" || $scope.checkedrows[i].account_type == "Credito" || $scope.checkedrows[i].account_type == "Credito Adiantamento"){
              $scope.billet.emailContent += "<td><font color='red'>" + $rootScope.formatNumber(-$scope.checkedrows[i].actual_value)+"</font></td>";
            } else {
              $scope.billet.emailContent += "<td>" + $rootScope.formatNumber($scope.checkedrows[i].actual_value)+"</td>";
            }

            $scope.billet.emailContent += "<td>" + $scope.checkedrows[i].issuing+"</td><td>"+$scope.checkedrows[i].client+"</td>";
            if($scope.billetGenerated) {
              if($scope.checkedrows[i].status == 'E') {
                $scope.billet.emailContent += "<td>"+$scope.checkedrows[i].billet+"<td>";
              } else {
                $scope.billet.emailContent += "<td><td>";
              }
            }
            $scope.billet.emailContent += "</tr>";
          }
          $scope.billet.emailContent += "<tr><td></td><td></td><td></td><td></td><td><b>Total:</b></td><td><b>"+$rootScope.formatNumber($scope.selected.actual_value)+"</b></td><td></td><td></td></tr>";
          $scope.billet.emailContent += "</tbody></table><br><br><b>Vencimento: "+$filter('date')($rootScope.findDate($scope.selected.due_date), 'dd/MM/yyyy')+" </b>"+
          "<br><br><b>Numero boleto: "+$scope.selected.ourNumber+"</b>";

          if($scope.selected.actual_value < 0) {
            $scope.billet.subject = 'BORDERO DE REEMBOLSO ONE MILHAS - ' + $scope.selected.ourNumber;
          }

          if($scope.selected.discount > 0) {
            $scope.billet.emailContent += "<br><br><b>Descontos: " + $rootScope.formatNumber($scope.selected.discount) + "</b>";
          }

          $scope.tabindex = 2;
          $scope.$apply();
        });

      };

      $scope.fillEmailContent = function() {
        $scope.billet.emailpartner = $scope.selected.email;
        $scope.billet.client = $scope.selected.client;
        $scope.billet.mailcc = '';
        $scope.billet.subject = 'BOLETO(S) ATUALIZADO(S) - URGENTE!!';

        $scope.billet.emailContent = "Bom dia,<br><br>Aguardamos o(s) pagamento(s) do(s) boleto(s) em anexo: ";

        $scope.selected.due_date = $rootScope.formatServerDate($scope.selected._due_date);
        $scope.selected.actual_value = $('#actual').maskMoney('unmasked')[0];

        $.post("../backend/application/index.php?rota=/loadBilletsReceive", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.billetsClient = jQuery.parseJSON(result).dataset.billetreceive;          
          var actualDate = new Date();
          for(var i in $scope.billetsClient) {
            if($scope.billetsClient[i].actual_value > 0) {
              var dueDate = new Date($scope.billetsClient[i].due_date);
              dueDate.setDate(dueDate.getDate() + 1);
              if(new Date($scope.billetsClient[i].due_date) < new Date()) {
                if($scope.billetsClient[i].ourNumber == 'ACORDO') {
                  $scope.billet.emailContent += "<br>BORDERO " + $scope.billetsClient[i].ourNumber + " Valor: R$" + $rootScope.formatNumber($scope.billetsClient[i].actual_value);
                } else {
                  $scope.billet.emailContent += "<br>BORDERO " + $scope.billetsClient[i].ourNumber;
                }
              }
            }
          }
        });

        for(var i in $scope.billetsDivision) {
          $scope.billetsDivision[i]._dueDate = $rootScope.formatServerDate($scope.billetsDivision[i].dueDate);
        }
        $.post("../backend/application/index.php?rota=/saveChangeValue", {hashId: $scope.session.hashId, data: $scope.selected, billetsDivision: $scope.billetsDivision}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.tabindex = 2;
          $scope.$apply();
        });
      };

      $scope.removeDivision = function() {
        $scope.billetsDivision.pop();
      };

      $scope.addDivision = function() {
        $scope.billetsDivision.push({
          name: '',
          actualValue: 0,
          dueDate: new Date(),
          paid: false
        });
      };

      $scope.fillEmailAdvising = function() {
        if($scope.advising) {

          $scope.selected = this.billetreceive;
          $scope.billet.emailpartner = ($scope.selected.financial_email) ? $scope.selected.financial_email : $scope.selected.email;
          $scope.billet.client = $scope.selected.client;
          $scope.billet.mailcc = '';
          $scope.billet.subject = 'LEMBRETE VENCIMENTO BOLETO(S)';
          $scope.billet.emailContent = "<br><br>Bom dia,<br><br>Lembramos o(s) vencimento(s) do(s) boleto(s) com as referências abaixo. Pague em dia, evite juros.<br>Caso já tenha pago, favor desconsiderar o e-mail.";

          $scope.selected.type = 'advising';
          $.post("../backend/application/index.php?rota=/loadBilletsReceive", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            $scope.billetsClient = jQuery.parseJSON(result).dataset.billetreceive;
            var actualDate = new Date();
            for(var i in $scope.billetsClient) {
              if($scope.billetsClient[i].actual_value > 0) {
                var dueDate = new Date($scope.billetsClient[i].due_date);
                dueDate.setDate(dueDate.getDate() + 1);
                if((dueDate.getDate() == actualDate.getDate()) && (dueDate.getMonth() == actualDate.getMonth()) && (dueDate.getFullYear() == actualDate.getFullYear())){
                  $scope.billet.emailContent += "<br><br>A VENCER / VENCIDO  -  " + $scope.billetsClient[i].client + "  -  " + $scope.billetsClient[i].ourNumber + "  -  " + $filter('date')($scope.billetsClient[i].due_date, 'dd/MM/yyyy') + "  -  " + $rootScope.formatNumber($scope.billetsClient[i].actual_value - $scope.billetsClient[i].alreadyPaid);
                }
              }
            }
            $scope.billet.emailContent += "<br><br><br>Obrigado pela parceria!";
            $scope.tabindex = 2;
            $scope.$apply();
          });

        } else {

          $scope.selected = this.billetreceive;
          $scope.billet.emailpartner = ($scope.selected.financial_email) ? $scope.selected.financial_email : $scope.selected.email;
          $scope.billet.client = $scope.selected.client;
          $scope.billet.mailcc = '';
          $scope.billet.subject = 'BOLETO(S) ATUALIZADO(S) - URGENTE!!';

          $scope.billet.emailContent = "Bom dia,<br><br>Aguardamos o(s) pagamento(s) do(s) boleto(s) em anexo: <br>";

          $.post("../backend/application/index.php?rota=/loadBilletsReceive", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            $scope.billetsClient = jQuery.parseJSON(result).dataset.billetreceive;
            var actualDate = new Date();
            for(var i in $scope.billetsClient) {
              if($scope.billetsClient[i].actual_value > 0) {
                var dueDate = new Date($scope.billetsClient[i].due_date);
                dueDate.setDate(dueDate.getDate() + 1);
                if(dueDate < actualDate) {
                  if($scope.billetsClient[i].ourNumber == 'ACORDO') {
                    $scope.billet.emailContent += "<br>BORDERO " + $scope.billetsClient[i].ourNumber + " Valor: R$" + $rootScope.formatNumber($scope.billetsClient[i].actual_value);
                  } else {
                    $scope.billet.emailContent += "<br>BORDERO " + $scope.billetsClient[i].ourNumber;
                  }
                }
              }
            }

            $.post("../backend/application/index.php?rota=/saveChangeValue", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.tabindex = 2;
              $scope.$apply();
            });

          });
        }
      };

      $scope.findColor = function(billetreceive){
        var dueDate = new Date(billetreceive.due_date);
        var actualDate = new Date();
        // dueDate.setDate(dueDate.getDate() + 1);
        if(billetreceive.status == 'B'){
          return "#9DCE9D";
        }
        if((dueDate.getDate() == actualDate.getDate()) && (dueDate.getMonth() == actualDate.getMonth()) && (dueDate.getFullYear() == actualDate.getFullYear())){
          return "#F5F5C8";
        }else  if(dueDate < actualDate){
          return "#F38E8E";
        }
      };

      $scope.printB = function(){
        var doc = new jsPDF('l', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(18);
        doc.text(150, 30, 'Borderô - '+$scope.selected.client+' - '+$filter('date')($scope.selected.due_date, 'dd/MM/yyyy'));

        var columns = [
          {title: "Data", dataKey: "due_date"},
          {title: "Descrição", dataKey: "description"},
          {title: "Trecho", dataKey: "airports"},
          {title: "CIA", dataKey: "airline"},
          {title: "Valor", dataKey: "value"},
          {title: "Emissor", dataKey: "issuing"},
          {title: "Cliente", dataKey: "client"}
        ];

        var rows = [];
        for (var i = 0; i < $scope.billsreceive.length; i++) {
          rows.push({
            due_date: $filter('date')($scope.billsreceive[i].issuing_date,'dd/MM/yyyy'),
            description: $scope.billsreceive[i].description,
            airports: $scope.billsreceive[i].from+"-"+$scope.billsreceive[i].to,
            airline: $scope.billsreceive[i].airline,
            value: $rootScope.formatNumber($scope.billsreceive[i].actual_value),
            issuing: $scope.billsreceive[i].issuing,
            client: $scope.billsreceive[i].client
          });
        }
        rows.push({
          due_date: 'Numero:',
          description: $scope.selected.ourNumber
        });
        rows.push({
          due_date: 'Total:',
          description: $rootScope.formatNumber($scope.selected.actual_value)
        });
        rows.push({
          due_date: 'Vencimento:',
          description: $filter('date')($scope.selected.due_date, 'dd/MM/yyyy')
        });

        doc.autoTable(columns, rows, {
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
          },
          createdCell: function (cell, data) {
            if (data.column.dataKey === 'value') {
              cell.styles.halign = 'right';
            }
          }
        });
        doc.save('Bordero_'+$scope.selected.client+'_'+$filter('date')($scope.selected.due_date, 'dd/MM/yyyy')+'.pdf');
      };

      $scope.printTotalDebits = function() {
        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(16);
        doc.setTextColor(0);
        doc.setFontStyle('bold');
        doc.text('IDEAL VIAGENS', 200, 50);

        var columns = [
          {title: "Cliente", dataKey: "client"},
          {title: "Bordero", dataKey: "ourNumber"},
          {title: "VALOR-PAGO", dataKey: "actualValue"},
          {title: "ATUAL", dataKey: "valueBillReceive"},
          {title: "Status", dataKey: "status"}
        ];

        var rows = [];
        for(var i in $scope.billetsClient) {
          rows.push({
            client: $scope.billetsClient[i].client,
            status: $scope.billetsClient[i].status
          });
          for(var j in $scope.billetsClient[i].billets) {
            rows.push({
              ourNumber: $scope.billetsClient[i].billets[j].our_number,
              actualValue: $rootScope.formatNumber($scope.billetsClient[i].billets[j].actualValue) + ' - ' + $rootScope.formatNumber($scope.billetsClient[i].billets[j].alreadyPaid),
              valueBillReceive: $rootScope.formatNumber($scope.billetsClient[i].billets[j].value)
            });
          }
        }

        rows.push({
          ourNumber: 'Total:',
          valueBillReceive: $rootScope.formatNumber($scope.getTotal())
        });

        doc.autoTable(columns, rows, {
          theme: 'plain',
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
          },
          margin: {horizontal: 10},
          bodyStyles: {valign: 'top'},
          createdCell: function (cell, data) {
            if (data.column.dataKey === 'actual_value') {
              cell.styles.halign = 'right';
            }
          }
        });
        doc.save('Debitos.pdf');
      };

      $scope.mailOrder = function(){
        var file = [];
        for(var j in $scope.uploader.queue) {
          file.push($scope.uploader.queue[j].file.name);
        }
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/mailOrder", {data: $scope.billet, attachment: file, type: 'FINANCEIRO', origin: $scope.selected.origin, emailType: 'EMAIL-GMAIL'}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.uploader.clearQueue();
          cfpLoadingBar.complete();
          if($scope.advising) {
            $scope.loadTodaysOpened();
          } else {
            $scope.loadOpened();
          }
          $scope.tabindex = 0;
          $scope.apply();
        });
      };

      $scope.blockClient = function(client) {
        if(client.status =='Bloqueado') {
          client.status = 'Aprovado';
        } else client.status = 'Bloqueado';
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/saveClientStatus", {hashId: $scope.session.hashId, data: client}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadDefaulters();
        });
      };

      $scope.saveBillets = function(clients) {
        $.post("../backend/application/index.php?rota=/saveBilletsValue", { data: clients}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadDefaulters();
        });
      };

      $scope.intentToSaveBillets = function(clients) {
        $rootScope.$emit('openDebitsValuesLogModal', clients);
      };

      $scope.getTotal = function() {
        var total = 0;
        if($scope.debits || $scope.Anticipated){
          if($scope.billetsClient) {
            if($scope.billetsClient.length > 0) {
              for(var i in $scope.billetsClient) {
                total += $scope.billetsClient[i].billets[$scope.billetsClient[i].billets.length - 1].value;
              }
            }
          }
        }
        return total;
      };

      $scope.setActual_Value = function() {
        if($scope.selected.original_value >= $scope.selected.actual_value)
          $scope.selected.discount = $scope.selected.original_value - $scope.selected.actual_value;
        else
          $scope.selected.tax = $scope.selected.actual_value - $scope.selected.original_value;
        $scope.selected.actual_value = parseFloat($scope.selected.original_value) + parseFloat($scope.selected.tax) - parseFloat($scope.selected.discount);
      };

      $scope.setAsLoss = function(billlet) {
        $.post("../backend/application/index.php?rota=/setAsLoss", {hashId: $scope.session.hashId, data: billlet}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadDefaulters();
        });
      };

      $scope.getChecked = function(selected) {
        if(selected.status == 'L') {
          return true;
        }
        return false;
      };

      $scope.getDayOfDelay = function(date) {
        var date1 = new Date();
        var delayed = new Date(date);
        var diffDays = '';
        if(date != '' && delayed != 'Invalid Date') {
          var timeDiff = Math.abs(delayed.getTime() - date1.getTime());
          diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
        }

        return diffDays;
      };

      $scope.loadDefaulters = function() {
        $scope.debits = true;
        $scope.Anticipated = false;
        $scope.showTable = false;
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadClientsDebits", {hashId: $scope.session.hashId, data: $scope.selected, filter: $scope.filterDebits}, function(result){
          $scope.billetsClient = jQuery.parseJSON(result).dataset;
          $scope.filterDebits = {};
          $scope.$digest();
          cfpLoadingBar.complete();
        });
      };

      $scope.setFilter = function(dayFrom, dayTo) {
        $scope.filterDebits.dayFrom = dayFrom;
        $scope.filterDebits.dayTo = dayTo;
        $scope.loadDefaulters();
      };

      $scope.loadAnticipatedDebtis = function() {
        $scope.Anticipated = true;
        $scope.debits = false;
        $scope.showTable = false;
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadAnticipatedDebits", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          $scope.billetsClient = jQuery.parseJSON(result).dataset;
          $scope.$apply();
          cfpLoadingBar.complete();
        });
      };

      $scope.loadClientAnalisys = function() {
        $.post("../backend/application/index.php?rota=/loadClientsNames", {}, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;
        });
        $scope.debits = false;
        $scope.Anticipated = false;
        $scope.clientAnalisys = true;
        $scope.tabindex = -1;
      };

      $scope.findClient = function() {
        if($scope.filterHistoric.client) {
          var request = { type: 'advising', client: $scope.filterHistoric.client };
          $.post("../backend/application/index.php?rota=/loadBilletsReceive", { hashId: $scope.session.hashId, data: request }, function(result){
            $scope.ClientHistoric = jQuery.parseJSON(result).dataset.billetreceive;
            $scope.filteredClientHistoric = $scope.ClientHistoric;
            $scope.$digest();
          });
        } else {
          logger.logError('Cliente deve ser definido');
        }
      };

      $scope.newAgreement = function() {
        $("#valueAgreement").maskMoney({thousands:'.',decimal:',', precision: 2});
        $scope.billetAgreement = {};
        $.post("../backend/application/index.php?rota=/loadClientsNames", {}, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset;
        });
        $scope.agreement = true;
        $scope.debits = false;
        $scope.Anticipated = false;
        $scope.tabindex = -1;
      };

      $scope.generateBilletAgreement = function() {
        $scope.billetAgreement.actualValue = $('#valueAgreement').maskMoney('unmasked')[0];
        $.post("../backend/application/index.php?rota=/saveBilletAgreement", {hashId: $scope.session.hashId, data: $scope.billetAgreement}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.agreement = false;
          $scope.billetAgreement = {};
          $scope.tabindex = 0;
          $scope.$digest();
        });
      };

      $scope.filterClientDebits = function() {
        $scope.filteredClientHistoric = [];
        if($scope.filterClient.refund) {
          for(var i in $scope.ClientHistoric) {
            if($scope.ClientHistoric[i].actual_value > 0) {
              $scope.filteredClientHistoric.push($scope.ClientHistoric[i]);
            }
          }
        } else {
          $scope.filteredClientHistoric = $scope.ClientHistoric;
        }
      };

      $scope.getTotalClient = function() {
        var total = 0;
        for(var i in $scope.filteredClientHistoric){
          if($scope.filteredClientHistoric[i].status == '') {
            total += $scope.filteredClientHistoric[i].actual_value;
          } else {
            total += $scope.filteredClientHistoric[i].actual_value - $scope.filteredClientHistoric[i].alreadyPaid;
          }
        }
        return total;
      };

      $scope.getUrlContaAzul = function() {
        $.post("../backend/application/index.php?rota=/ContaAzul/authorize", {}, function(result){
          $scope.redirectToGoogle(jQuery.parseJSON(result).dataset['url']);
        });
      };

      $scope.redirectToGoogle = function(link){
        $window.open(link, '_blank');
      };

      $scope.getTotalFiltered = function() {
        var total = 0;
        if($scope.filteredBilletsReceive) {
          for(var i in $scope.filteredBilletsReceive) {
            if($scope.filteredBilletsReceive[i].status == 'B') {
              total += $scope.filteredBilletsReceive[i].actual_value;
            } else {
              total += $scope.filteredBilletsReceive[i].actual_value - $scope.filteredBilletsReceive[i].alreadyPaid;
            }
          }
        }
        return total;
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageBilletsReceive = [];
      $scope.currentPageBillsReceive = [];
      $rootScope.hashId = $scope.session.hashId;
      
      $scope.reader = new FileReader();
      init = function() {
        $scope.file;
        $scope.advising = false;
        $scope.opened = false;
        $scope.clientAnalisys = false;
        $scope.agreement = false;
        $scope.billetsDivision = [];
        $scope.filterDebits = {};

        $scope.receive = false;
        $scope.showTable = false;
        $scope.debits = false;
        $scope.Anticipated = false;
        $scope.tabindex = 0;
        $scope.checkValidRoute();
        $scope.loadData();
        $scope.uploader = new FileUploader();
        $scope.uploader.url = "../backend/application/index.php?rota=/saveFile";
        $scope.uploader.autoUpload = true;
        $scope.uploader.filters.push({
            name: 'customFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 10;
            }
        });
        $rootScope.modalOpen = false;
      };

      return init();
    }
  ]).controller('BilletModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {
      $scope.filterclients = $scope.$parent.clients;

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "BilletFilter.html",
          controller: 'BilletModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter !== undefined) {
            filter._dueDateFrom = $rootScope.formatServerDate(filter.dueDateFrom);
            filter._dueDateTo = $rootScope.formatServerDate(filter.dueDateTo);
            filter._issueDateFrom = $rootScope.formatServerDate(filter.issueDateFrom);
            filter._issueDateTo = $rootScope.formatServerDate(filter.issueDateTo);
            filter._paymentDateFrom = $rootScope.formatServerDate(filter.paymentDateFrom);
            filter._paymentDateTo = $rootScope.formatServerDate(filter.paymentDateTo);
          }

          $scope.$parent.filter = filter;
          $scope.$parent.loadBillets();
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };
    }
  ]).controller('BilletModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {
      $scope.billStatus = ['Em aberto','Emitida','Baixada','Cancelado'];
      $scope.filter = filter;
      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('CloseModalCtrl', [
    '$scope', '$rootScope', '$modal', '$filter', '$log', function($scope, $rootScope, $modal, $filter, $log) {
      $scope.filterclients = $scope.$parent.clients;

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "CloseBillet.html",
          controller: 'CloseModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter.payment_date !== undefined) {
            $scope.$parent.payment_date = filter.payment_date.toLocaleDateString();
            $scope.checkedrows = [];
            for(var i in $scope.$parent.billetsreceive) {
              if($scope.$parent.billetsreceive[i].checked) {
                $scope.checkedrows.push($scope.$parent.billetsreceive[i]);
              }
            }
            for(var i in $scope.checkedrows){
              $scope.checkedrows[i].payment_date = $rootScope.formatServerDate(filter.payment_date)
            }
            if($scope.selected) {
              $scope.selected.actual_value = $('#actual').maskMoney('unmasked')[0];
            }
            $scope.$parent.saveCloseReceive();
          }
        }));
      };
    }
  ]).controller('CloseModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {
      $scope.filter = filter;
      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('TodayFinancialEmailsModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {

      $scope.open = function() {
        if($scope.$parent.advising) {
          $scope.filter = 'LEMBRETE VENCIMENTO BOLETO';
        } else if($scope.$parent.opened) {
          $scope.filter = 'BOLETO ATUALIZADO';
        } else {
          $scope.filter = '';
        }
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "TodayFinancialEmailsModalCtrl.html",
          controller: 'TodayFinancialEmailsModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
        }));
      };
    }
  ]).controller('TodayFinancialEmailsModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'filter', function($scope, $rootScope, $modalInstance, $filter, filter) {

      $scope.filter = filter;

      $.post("../backend/application/index.php?rota=/loadTodayEmails", {hashId: $rootScope.hashId}, function(result){
          $scope.emails = jQuery.parseJSON(result).dataset;
          $scope.emails = $filter('filter')($scope.emails, $scope.filter);
          $scope.total = $scope.emails.length;
          $scope.$apply();
      });

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('BillsgeneratedChangeCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', 'logger', function($scope, $rootScope, $modal, $log, $filter, logger) {

      $scope.open = function() {
        $scope.bill = this.billreceive;
        $scope.bill = angular.copy($scope.bill);
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "BillsgeneratedChangeCtrl.html",
          controller: 'BillsgeneratedChangeInstanceCtrl',
          resolve: {
            main: function() {
              return $scope.$parent.$parent.$parent.$parent.main;
            },
            hashId: function() {
              return $scope.$parent.$parent.$parent.$parent.session.hashId;
            },
            bill: function() {
              return $scope.bill;
            }
          }
        });
        modalInstance.result.then(function(bill) {
          if(bill) {
            $.post("../backend/application/index.php?rota=/changeBillValue", {hashId: $scope.$parent.$parent.$parent.session.hashId, data: bill}, function(result){
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.$parent.$parent.loadBilletBills();
              $scope.$parent.$parent.$apply();
            });
          } else {
            $scope.$parent.$parent.loadBilletBills();
            $scope.$parent.$parent.$apply();
          }
        });
      };

    }
  ]).controller('BillsgeneratedChangeInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'main', 'hashId', 'bill', function($scope, $rootScope, $modalInstance, logger, main, hashId, bill) {
      $scope.main = main;
      $scope.hashId = hashId;
      $scope.bill = bill;
      $scope.checked = true;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.remove = function() {
        $.post("../backend/application/index.php?rota=/removeBill", {hashId: $scope.hashId, data: $scope.bill}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $modalInstance.close(undefined);
        });
      };

      $scope.ok = function() {
        $modalInstance.close($scope.bill);
      };
    }
  ]).controller('DebitsValuesModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $rootScope.modalOpen = false;

      $rootScope.$on('openDebitsValuesLogModal', function(event, args) {
        event.stopPropagation();
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function(args) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "DebitsValuesModal.html",
          controller: 'DebitsValuesLogInstanceCtrl',
          resolve: {
            selected: function() {
              return args;
            }
          }
        });
        modalInstance.result.then(function(resolve) {
          $scope.$parent.saveBillets(resolve);
          $rootScope.modalOpen = false;
        }, function() {
          $rootScope.modalOpen = false;
        });
      };

    }
  ]).controller('DebitsValuesLogInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'selected', function($scope, $rootScope, $modalInstance, logger, selected) {
      $scope.selected = selected;
      $scope.selected.resolveDescription = '';

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.save = function() {
        if($scope.selected.resolveDescription.length < 1)
          return logger.logError('Motivos devem ser informados');
        $modalInstance.close($scope.selected);
      };
    }
  ])
  .controller("infoBaixaModalInstanceCtrl", [
    "$scope",
    "$rootScope",
    "$modalInstance",
    function ($scope, $rootScope, $modalInstance) {
      $scope.ok = function () {
        $modalInstance.close("ok");
      };
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    },
  ]);
})();
;
