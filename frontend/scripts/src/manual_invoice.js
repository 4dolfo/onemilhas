(function () {
  'use strict';
  angular.module('app.table').controller('ManualInvoiceCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {

      var init;

      $scope.filter = {client: "", dateFrom: ""};
      $scope.selectedClient = {code: "-",
                               name: "-", adress: "-",
                                          cityfullname: "-", phone1: "-",
                                                             phone2: "-",
                                                             phone3: "-", email: "-", zip: "-"};
      $scope.billsreceive = [];
      $scope.serviceValues = 0.00;
      $scope.searchKeywords = '';
      $scope.row = '';
      $scope.rowSale = '';
      $scope.getTotal = function() {
        $scope.serviceValues = 0;

        if($scope.billsreceive)
          if($scope.billsreceive.length > 0)
            for(var i in $scope.billsreceive)
                $scope.serviceValues += $scope.billsreceive[i].original_value;
      };

      $scope.getManualInvoiceText = function(){

        var fullText = "-";

        if($scope.billsreceive)
          if($scope.billsreceive.length > 0)
          {
            fullText = "COMISSOES DE INTERMEDIACAO DE PASSAGENS AEREAS - " + $filter('date')( $rootScope.findDate($scope.billsreceive[0].due_date), 'MM/yyyy');

            for(var i in $scope.billsreceive)
            {
                fullText += "\rBORDERO " + $scope.billsreceive[i].ourNumber;
                fullText += " - " + $filter('date')($rootScope.findDate($scope.billsreceive[i].due_date), 'dd/MM/yyyy');
                fullText += " - R$" + $rootScope.formatNumber($scope.billsreceive[i].original_value);
            }
          }

        return fullText;
      };

      $scope.quebraLinha = function(billreceive){
        return "\r";
      };

      $scope.loadReportMensal = function() {

        var dateFrom = angular.copy($scope.filter.dateFrom);
        var dateTo = angular.copy(dateFrom);
        dateTo.setMonth(dateTo.getMonth() + 1);
        dateTo.setDate(0);

        $.post("../backend/application/index.php?rota=/loadInvoiceMonthly", {
            hashId: $scope.session.hashId,
            data: $scope.filter,
            _dueDateFrom: $rootScope.formatServerDate(dateFrom),
            _dueDateTo: $rootScope.formatServerDate(dateTo),
          }, function(result){
            $scope.manualInvoices = jQuery.parseJSON(result).dataset;
            $scope.invoiceToReport();
        });
      };

      $scope.invoiceToReport = function() {
        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(20);
        doc.text(150, 30, 'Contas a Pagar');

        var columns = [
          { title: "Cliente", dataKey: "client" },
          { title: "Valor", dataKey: "amount" }
        ];
        
        var rows = [];
        var total;
        for(var i in $scope.manualInvoices) {
          total += $scope.manualInvoices[i].amount;
          rows.push({
            client: $scope.manualInvoices[i].client,
            amount: $rootScope.formatNumber($scope.manualInvoices[i].amount)
          });
        }

        rows.push({
          amount: $rootScope.formatNumber(total)
        });

        doc.autoTable(columns, rows, {
          styles: {
            fontSize: 8
          },
          createdCell: function (cell, data) {
            if (data.column.dataKey === 'amount') {
              cell.styles.halign = 'right';
            }
          }
        });
        doc.save('Relatorio.pdf');
      }

      $scope.getClientText = function(){
        return "CPF/CNPJ: "+$scope.selectedClient.code+"\r"+
               $scope.selectedClient.name+"\r"+
               $scope.selectedClient.company_name+"\r"+
               $scope.selectedClient.adressFinnancial+"\r"+
               "CEP: "+$scope.selectedClient.zipCodeFinnancial+"\r"+
               $scope.selectedClient.city+"\r"+
               "Telefone: "+$scope.selectedClient.phone1+$scope.selectedClient.phone2+$scope.selectedClient.phone3+"\r"+
               "Email: "+$scope.selectedClient.finnancialEmail;
      };


      function compareClient(value){
        return value.name == $scope.filter.client;
      }

      $scope.updateClientInfo = function(){
        var filtered = $scope.clients.filter(compareClient);

        if(filtered.length < 0)
          return;

        $scope.selectedClient.code = filtered[0].registrationCode;
        $scope.selectedClient.name = filtered[0].name + " (" +filtered[0].company_name+") ";
        $scope.selectedClient.adress = filtered[0].adressFinnancial + " " +
                                        filtered[0].adressNumberFinnancial + " " +
                                       filtered[0].adressComplementFinnancial + " " +
                                       filtered[0].adressDistrictFinnancial + " " +
                                       filtered[0].zipCodeFinnancial;
        $scope.selectedClient.city = filtered[0].cityFinnancialName + filtered[0].cityFinnancialState;
        $scope.selectedClient.phone1 = filtered[0].phoneNumber;
        $scope.selectedClient.phone2 = filtered[0].phoneNumber2;
        $scope.selectedClient.phone3 = filtered[0].phoneNumber3;
        $scope.selectedClient.email = filtered[0].finnancialEmail;
        $scope.selectedClient.zip = filtered[0].zipCodeFinnancial;
        $scope.selectedClient.company_name = filtered[0].company_name;
      };

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageBillsReceive = $scope.billsreceive.slice(start, end);
      };

      $scope.selectSale = function(pageSale) {
        var end, start;
        start = (pageSale - 1) * $scope.numPerPageSale;
        end = start + $scope.numPerPageSale;
        return $scope.currentPageSales = $scope.sales.slice(start, end);
      };

      $scope.onFilterChangeSale = function() {
        $scope.selectSale(1);
        $scope.currentPageSale = 1;
        return $scope.rowSale = '';
      };
      
      $scope.onNumPerPageChangeSale = function() {
        // $scope.selectSale(1);
        // return $scope.currentPageSale = 1;
        $scope.loadOrders();
      };
      
      $scope.onOrderChangeSale = function() {
        $scope.selectSale(1);
        return $scope.currentPageSale = 1;
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
      
      $scope.toWebService = function() {
        $scope.tabindex = 1;
        $scope.webservice.ListRps = $scope.billsreceive;
        $scope.fillWebServiceValues();
      };
      
      $scope.statusText = function(status) {
        var statusFormat = status == 'E' ? "Emitido" : "Baixada";
        return statusFormat;
      };

      $scope.search = function() {
        $scope.loadBilletsReceive();
        $scope.loadOrders();
      };
      
      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.billsreceive = $filter('orderBy')($scope.billsreceive, rowName);
        return $scope.onOrderChange();
      };
       $scope.orderSale = function(rowName) {
        if ($scope.rowSale === rowName) {
          return;
        }
        $scope.rowSale = rowName;
        $scope.sales = $filter('orderBy')($scope.sales, rowName);
        return $scope.onOrderChangeSale();
      };
      $scope.loadClients = function(){
        $.post("../backend/application/index.php?rota=/loadClient", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset.clients;
        });
      };

      $scope.loadBilletsReceive = function() {

        var dateFrom = angular.copy($scope.filter.dateFrom);
        var dateTo = angular.copy(dateFrom);
        dateTo.setMonth(dateTo.getMonth() + 1);
        dateTo.setDate(0);

        $.post("../backend/application/index.php?rota=/loadBilletsReceive", {
            hashId: $scope.session.hashId,
            client: $scope.filter.client,
            _dueDateFrom: $rootScope.formatServerDate(dateFrom),
            _dueDateTo: $rootScope.formatServerDate(dateTo),
            refund: $scope.refund },
          function(result){
            $scope.billsreceive = JSON.parse(result).dataset.billetreceive;
            $scope.webservice.ListRps = $scope.billsreceive;
            if($scope.tabindex == 1) {
              $scope.fillWebServiceValues();
            }
            cfpLoadingBar.complete();
            $scope.getTotal();
            
            return $scope.onFilterChange();
        });
      };

      $scope.loadOrders = function() {
        
        var dateFromSales = angular.copy($scope.filter.dateFrom);
        var dateToSales = angular.copy(dateFromSales);
        dateToSales.setMonth(dateToSales.getMonth() + 1);
        dateToSales.setDate(0);
        $scope.filter.saleDateFrom = dateFromSales;
        $scope.filter.saleDateTo = dateToSales;
        $scope.filter._saleDateFrom = $rootScope.formatServerDate($scope.filter.saleDateFrom);
        $scope.filter._saleDateTo = $rootScope.formatServerDate($scope.filter.saleDateTo);
        $scope.sumAmountPaid = 0;
        $scope.sumTotalCost = 0;
        $scope.sumProfit = 0;

        $.post("../backend/application/index.php?rota=/loadSalesToOperations", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function(result){
          $scope.sales = jQuery.parseJSON(result).dataset.sales;
          for(var i in $scope.sales){
            $scope.sales[i].dateT = new Date(angular.copy($scope.sales[i].issueDate));
            $scope.sumAmountPaid = $scope.sumAmountPaid + $scope.sales[i].amountPaid;
            $scope.sumTotalCost = $scope.sumTotalCost + $scope.sales[i].totalCost;
          }
          $scope.profit = $scope.sumAmountPaid - $scope.sumTotalCost;
          $scope.totalData = jQuery.parseJSON(result).dataset.total;
          $scope.tabindex = 0;
          $scope.currentPageSale = 1;
          $scope.selectSale(1);
          $scope.$digest();
        });
      };

       $scope.load = function() {

        var dateFrom = angular.copy($scope.filter.dateFrom);
        var dateTo = angular.copy(dateFrom);
        dateTo.setMonth(dateTo.getMonth() + 1);
        dateTo.setDate(0);

        $.post("../backend/application/index.php?rota=/loadBilletsReceive", {
            hashId: $scope.session.hashId,
            client: $scope.filter.client,
            _dueDateFrom: $rootScope.formatServerDate(dateFrom),
            _dueDateTo: $rootScope.formatServerDate(dateTo),
            refund: $scope.refund },
          function(result){
            $scope.billsreceive = JSON.parse(result).dataset.billetreceive;
            $scope.webservice.ListRps = $scope.billsreceive;
            if($scope.tabindex == 1) {
              $scope.fillWebServiceValues();
            }
            cfpLoadingBar.complete();
            $scope.getTotal();
            
            return $scope.onFilterChange();
        });
      };

      $scope.saveSelected = function() {
        $.post("../backend/application/index.php?rota=/checkBillsReceiveStatus", {hashId: $scope.session.hashId, data: $scope.billsreceive}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.toManual = function() {
        $scope.tabindex = 0;
      };

      $scope.generateNFSe = function() {
        $.post("../backend/application/index.php?rota=/saveNFSe", { data: $scope.webservice }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.fillWebServiceValues = function() {
        for(var i in $scope.webservice.ListRps) {

          var InfRps = {
            IdentificacaoFromNames: {
              Numero: '1',
              Serie: 'ABCDE',
              Tipo: '1'
            },
            NaturezaOperacao: '1',
            RegimeEspecialTributacao: '6',
            OptanteSimplesNacional: '1',
            IncentivadorCultural: '1',
            Status: '1'
          }
          $scope.webservice.ListRps[i].InfRps = InfRps;

          var Valores = {
            ValorServicos: $scope.webservice.ListRps[i].original_value,
            ValorDeducoes: ( $scope.webservice.ListRps[i].original_value / 100 ) * 5,
            ValorPis: 0,
            ValorCofins: 0,
            ValorInss: 0,
            ValorIr: 0,
            ValorCsll: 0,
            IssRetido: 1,
            ValorIss: 0,
            OutrasRetencoes: 0,
            Aliquota: 0,
            DescontoIncondicionado: 0,
            DescontoCondicionado: 0
          }
          $scope.webservice.ListRps[i].Valores = Valores;

          var Servico = {
            ItemListaServico: 0,
            CodigoTributacaoMunicipio: 0,
            Discriminacao: 0,
            CodigoMunicipio: 0
          }
          $scope.webservice.ListRps[i].Servico = Servico;

          var Prestador = {
            Cnpj: '99999999000191',
            InscricaoMunicipal: '1733160024'
          }
          $scope.webservice.ListRps[i].Prestador = Prestador;

          var Tomador = {
            RazaoSocial: 'INSCRICAO DE TESTE SIATU - DAGUA -PAULINOS',
            IdentificacaoTomador: {
              Cnpj: '99999999000191',
              InscricaoMunicipal: 'DAGUA'
            },
            Endereco: {
              rua: 'DA BAHIA',
              numero: '200',
              complemento: 'ANDAR 14',
              bairro: 'CENTRO',
              CodigoMunicipio: '3106200',
              estado: 'MG',
              cep: '30160010'
            }
          }
          $scope.webservice.ListRps[i].Tomador = Tomador;

          var IntermediarioServico = {
            CpfCnpj: '99999999000191',
            RazaoSocial: 'INSCRICAO DE TESTE SIATU - DAGUA -PAULINOS',
            InscricaoMunicipal: '8041700010'
          }
          $scope.webservice.ListRps[i].IntermediarioServico = IntermediarioServico;

          var ConstrucaoCivil = {
            CodigoObra: '1234',
            Art: '1234'
          }
          $scope.webservice.ListRps[i].ConstrucaoCivil = ConstrucaoCivil;

        }
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.numPerPageOptSale = [10, 30, 50, 100];
      $scope.numPerPageSale = $scope.numPerPageOptSale[0];
      $scope.currentPage = 1;
      $scope.currentPageSale = 1;
      $scope.currentPageBillsReceive = [];
      $scope.sales = [];
      $rootScope.hashId = $scope.session.hashId;
      
      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.showme = false;
        $scope.refund = true;

        $scope.webservice = {};
        $scope.webservice.NumeroLote = 1;
        $scope.webservice.Cnpj = 99999999000191;
        $scope.webservice.InscricaoMunicipal = 1733160024;
        $scope.webservice.ListRps = [];

        $scope.loadClients();

      };
      return init();
    }
  ]).directive('copyToClipboard', ['$window',
      function($window) {
        var body = angular.element($window.document.body);
        var textarea = angular.element('<textarea/>');
        textarea.css({
            position: 'fixed',
            opacity: '0'
        });

        function copy(toCopy) {
            textarea.val(toCopy);
            body.append(textarea);
            textarea[0].select();

            try {
                var successful = document.execCommand('copy');
                if (!successful) throw successful;
            } catch (err) {
                console.log("Erro ao realizar a cópia via atalho.", toCopy);
            }
            textarea.remove();
        }

        return {
          restrict: 'A',
          link: function (scope, element, attrs, $window) {
              element.bind('click', function (e) {
                  copy(attrs.copyToClipboard);
              });
          }
        };
    }
  ]);
})();
;
