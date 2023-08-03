(function () {
  'use strict';
  angular.module('app.miles').controller('MilesBenchCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      $scope.searchKeywords = '';
      $scope.filteredMilesBenchs = [];
      $scope.row = '';
      $scope.rowSale = '';
      
      $scope.select = function(page) {
        // var end, start;
        // start = (page - 1) * $scope.numPerPage;
        // end = start + $scope.numPerPage;
        // return $scope.currentPageMilesBench = $scope.filteredMilesBenchs.slice(start, end);
        $scope.loadMiles();
      };

      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };
      
      $scope.onNumPerPageChange = function() {
        // $scope.select(1);
        // return $scope.currentPage = 1;
        $scope.loadMiles();
      };
      
      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };
      
      $scope.onNumPerPageChangeSale = function(numPSale) {
        $scope.numPerPageSale =  numPSale;
        // $scope.select(1);
        // return $scope.currentPage = 1;
        $scope.loadSelected();
      };

      $scope.saveChanges = function() {
        if($scope.selected.datePriority !== "" && $scope.selected.datePriority != 'Invalid Date') {
          $scope.selected._datePriority = $rootScope.formatServerDate($scope.selected.datePriority);
        }
        if($scope.selected.airline == 'LATAM') {
          if($scope.selected.onlyInter == null || $scope.selected.onlyInter == 'null') {
            return logger.logError('Tipo de emissão obrigatorio!');
          }
        }
        
        $.post("../backend/application/index.php?rota=/saveMilesChanges", {hashId: $scope.session.hashId, data: $scope.selected, purchases: $scope.purchaseHistory}, function(result){
          if (jQuery.parseJSON(result).message.type == 'S'){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          } else {
            logger.logError(jQuery.parseJSON(result).message.text);
          }
          $scope.loadMiles();
        });
      };

      $scope.selectPageSale = function(currentPage) {
        $scope.currentPageSale = currentPage;
        $scope.loadSelected();
      }

      $scope.setPriority = function() {
        $.post("../backend/application/index.php?rota=/saveCardStatusMiles", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };
      
      $scope.setSelected = function() {
        $scope.paxPerCards = [];
        $('#left_over').number( true, 0, ',', '.');
        $('#leftOver').number( true, 0, ',', '.');
        $('#milesPriority').number( true, 0, ',', '.');
        $scope.selected = this.miles;
        if($scope.selected._datePriority) {
          $scope.selected.datePriority = new Date($scope.selected._datePriority);
        }

        $.post("../backend/application/index.php?rota=/loadSaleHistory", { page: $scope.currentPageSale, numPerPage: $scope.numPerPageSale,  order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.selected }, function(result){
          $scope.saleHistory = jQuery.parseJSON(result).dataset.sales;
          $scope.totalDataSale = jQuery.parseJSON(result).dataset.total;
          $scope.calculateHystoryTotalPrice();
          $.post("../backend/application/index.php?rota=/loadPurchaseHistory", { data: $scope.selected }, function(result){
            $scope.purchaseHistory = jQuery.parseJSON(result).dataset;
            $scope.tabIndex = 1;
            $scope.$apply();
          });
          $scope.$digest();
        });
        $.post("../backend/application/index.php?rota=/loadMilesbenchLog", { data: $scope.selected }, function(result){
          $scope.dataBaseHistoric = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
        $.post("../backend/application/index.php?rota=/loadSaleRefunds", { data: $scope.selected }, function(result){
          $scope.saleRefunds = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
        $.post("../backend/application/index.php?rota=/cards/loadPaxPerCard", { data: $scope.selected }, function(result){
          $scope.paxPerCards = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.calculateHystoryTotalPrice = function(){
        for(let i=0; i<$scope.saleHistory.length; i++){
          $scope.saleHistory[i].total_coast_calculated = 0;
          if($scope.saleHistory[i].miles_used != '---'){
            for(let j=0; j<$scope.saleHistory[i].sale_purchases.length; j++){
              let milhas = parseFloat($scope.saleHistory[i].sale_purchases[j].miles_used);
              let preco  = parseFloat($scope.saleHistory[i].sale_purchases[j].cost_per_thousand);
              $scope.saleHistory[i].total_coast_calculated += (milhas / 1000 * preco);
            }
          }
        }
      }

       $scope.loadSelected = function() {
          console.log($scope.currentPageSale)
          $.post("../backend/application/index.php?rota=/loadSaleHistory", { page: $scope.currentPageSale, numPerPage:  $scope.numPerPageSale, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.selected }, function(result){
            $scope.saleHistory = jQuery.parseJSON(result).dataset.sales;
            $scope.totalDataSale = jQuery.parseJSON(result).dataset.total;
            $scope.calculateHystoryTotalPrice();

            $scope.$apply();
          });
      };

      $scope.change = function(data) {
       // = new Date();
        let d = new Date(data);
        return $scope.test = (("0"+d.getDate()).substr(-2,2) +"-"+ ("0"+(d.getMonth()+1)).substr(-2,2) +"-"+ d.getFullYear() ) ;
    };

    $scope.reportPurchaseCsv = function() {
      var data = [['Companhia','Data da Compra','Descricao' , 'Tipo Cartao', 'Custo em (R$) por 100', 'Vencimento', 'Custo Total (R$)', 'Milhas', 'Adicionado no banco de milhas','Restante']];
      

      $.post("../backend/application/index.php?rota=/loadPurchaseHistory", { data: $scope.selected }, function(result){
            $scope.purchaseH = jQuery.parseJSON(result).dataset;
            //$scope.tabIndex = 1;
            for(var i in $scope.purchaseH) {
            data.push([
              $scope.purchaseH[i].airline,
              $filter('date')(new Date($scope.purchaseH[i].purchase_date), 'dd/MM/yyyy HH:mm:ss'), 
              $scope.purchaseH[i].description,
              $scope.purchaseH[i].card_type,
              $scope.purchaseH[i].cost_per_thousand,
              $filter('date')($scope.findDate($scope.purchaseH[i].miles_due_date), 'dd/MM/yyyy HH:mm:ss'),
             $scope.purchaseH[i].total_cost,
              $scope.purchaseH[i].purchase_miles,
              $scope.purchaseH[i].realPurchased 
            ]);
          }
          var csvContent = "data:text/csv;charset=utf-8,";
          var dataString;
          data.forEach(function(infoArray, index){
             dataString = infoArray.join(";");
             csvContent += index < data.length ? dataString+ "\n" : dataString;

          }); 
          var encodedUri = encodeURI(csvContent);
          var link = document.createElement("a");
          link.setAttribute("href", encodedUri);
          link.setAttribute("download", "historico_compra.csv");
          document.body.appendChild(link);

          link.click();
            $scope.$apply();
      });
    };

      $scope.reportSaleCsv = function() {
        var data = [['Data de Venda','Status','Passageiro' , 'Voo', 'Localizador', 'Data de Embarque', 'Data de Desembarque', 'De', 'Para','Milhas Usadas','Reembolso', 'Retorno Pontos', 'e-ticket']];
        
        $.post("../backend/application/index.php?rota=/loadSaleHistory", { data:      $scope.selected }, function(result){
          $scope.saleHist = jQuery.parseJSON(result).dataset.sales;

          $.post("../backend/application/index.php?rota=/loadSaleRefunds", { data: $scope.selected }, function(result){
            $scope.refundSales = jQuery.parseJSON(result).dataset;
              
            for(var i in $scope.saleHist) {
              if(($scope.saleHist[i].refundDate != '')|| ($scope.saleHist[i].returnDate != '')){
                data.push([
                  $filter('date')($scope.findDate($scope.saleHist[i].issue_date), 'dd/MM/yyyy HH:mm:ss'),
                  $scope.saleHist[i].status, 
                  $scope.saleHist[i].pax_name,
                  $scope.saleHist[i].flight,
                  $scope.saleHist[i].flight_locator,
                  $filter('date')(new Date($scope.saleHist[i].boardingDate), 'dd/MM/yyyy HH:mm:ss'),
                  $filter('date')(new Date($scope.saleHist[i].landingDate), 'dd/MM/yyyy HH:mm:ss'),
                  $scope.saleHist[i].from,
                  $scope.saleHist[i].to,
                  $rootScope.formatNumber($scope.saleHist[i].miles_used,0),
                  $filter('date')(new Date($scope.saleHist[i].refundDate), 'dd/MM/yyyy HH:mm:ss'),
                  $filter('date')(new Date($scope.saleHist[i].returnDate), 'dd/MM/yyyy HH:mm:ss'),
                  $scope.saleHist[i].ticket_code,
                ]);
              }else{
                data.push([
                  $filter('date')($scope.findDate($scope.saleHist[i].issue_date), 'dd/MM/yyyy HH:mm:ss'),
                  $scope.saleHist[i].status, 
                  $scope.saleHist[i].pax_name,
                  $scope.saleHist[i].flight,
                  $scope.saleHist[i].flight_locator,
                  $filter('date')(new Date($scope.saleHist[i].boardingDate), 'dd/MM/yyyy HH:mm:ss'),
                  $filter('date')(new Date($scope.saleHist[i].landingDate), 'dd/MM/yyyy HH:mm:ss'),
                  $scope.saleHist[i].from,
                  $scope.saleHist[i].to,
                  $rootScope.formatNumber($scope.saleHist[i].miles_used,0),
                  '',
                  '',
                  $scope.saleHist[i].ticket_code,
                ]);
              }
            }

            for(var i in $scope.refundSales) {
              if(($scope.refundSales[i].refundDate != '')|| ($scope.refundSales[i].returnDate != '')){
                data.push([
                  $scope.refundSales[i].issue_date,
                  $scope.refundSales[i].status, 
                  $scope.refundSales[i].pax_name,
                  $scope.refundSales[i].flight,
                  $scope.refundSales[i].flight_locator,
                  $scope.refundSales[i].boardingDate,
                  $scope.refundSales[i].landingDate,
                  $scope.refundSales[i].from,
                  $scope.refundSales[i].to,              
                  $rootScope.formatNumber($scope.refundSales[i].miles_used,0),
                  $scope.refundSales[i].refundDate,
                  $scope.refundSales[i].returnDate,
                  $scope.refundSales[i].ticket_code,
                ]);
              }else{
                data.push([
                  $scope.refundSales[i].issue_date,
                  $scope.refundSales[i].status, 
                  $scope.refundSales[i].pax_name,
                  $scope.refundSales[i].flight,
                  $scope.refundSales[i].flight_locator,
                  $scope.refundSales[i].boardingDate,
                  $scope.refundSales[i].landingDate,
                  $scope.refundSales[i].from,
                  $scope.refundSales[i].to,              
                  $rootScope.formatNumber($scope.refundSales[i].miles_used,0),
                  '',
                  '',
                  $scope.refundSales[i].ticket_code,
                ]);
              }
            }
            var csvContent = "data:text/csv;charset=utf-8,";
            var dataString;
            data.forEach(function(infoArray, index){
                dataString = infoArray.join(";");
                csvContent += index < data.length ? dataString+ "\n" : dataString;

            }); 
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "historico_vendas.csv");
            document.body.appendChild(link);

            link.click();
            $scope.$apply();
          });
        });
      };

      $scope.back = function(){
        $scope.selected = undefined;
        $scope.tabIndex = 0;
        $scope.loadMiles();
      };

      $scope.findDate = function(date){
        if(new Date(date) != 'Invalid Date') {
          return new Date(date);
        } else {
          return '';
        }
      };

      $scope.printHistory = function() {
        var doc = new jsPDF('l', 'pt');
        doc.margin = 0.2;
        doc.setFontSize(10);
        doc.text(60, 20, 'Extrato do Cartão: - ' + $scope.selected.card_number + ' de ' + $scope.selected.name);
        doc.text(60, 35, 'Compras');
        var columns = [];
        
        //purchases
        columns = [
          {title: "Companhia", dataKey: "airline"},
          {title: "Data Compra", dataKey: "purchase_date"},
          {title: "Descrição", dataKey: "description"},
          {title: "Tipo Cartão", dataKey: "card_type"},
          {title: "Custo p/ 1000", dataKey: "cost_per_thousand"},
          {title: "Vencimento", dataKey: "due_date"},
          {title: "Custo total", dataKey: "total_cost"},
          {title: "Milhas", dataKey: "miles"}
        ];

        var rows = [];

        for (var i = 0; i < $scope.purchaseHistory.length; i++) {
          rows.push({
            airline: $scope.purchaseHistory[i].airline,
            purchase_date: $filter('date')($scope.findDate($scope.purchaseHistory[i].purchase_date), 'dd/MM/yyyy HH:mm:ss'),
            description: $scope.purchaseHistory[i].description,
            card_type: $scope.purchaseHistory[i].card_type,
            cost_per_thousand: $scope.purchaseHistory[i].cost_per_thousand,
            due_date: $filter('date')($scope.findDate($scope.purchaseHistory[i].miles_due_date), 'dd/MM/yyyy HH:mm:ss'),
            total_cost: $rootScope.formatNumber($scope.purchaseHistory[i].total_cost),
            miles: $rootScope.formatNumber($scope.purchaseHistory[i].purchase_miles, 0)
          });
        }
        
        var columns2 = [];
        columns2 = [
          {title: "Data da Venda", dataKey: "issue_date"},
          {title: "Status", dataKey: "status"},
          {title: "Passageiro", dataKey: "pax_name"},
          {title: "Voo", dataKey: "flight"},
          {title: "Data Embarque", dataKey: "boardingDate"},
          {title: "Data Desembarque", dataKey: "landingDate"},
          {title: "De", dataKey: "from"},
          {title: "Para", dataKey: "to"},
          {title: "Milhas", dataKey: "miles_used"}
        ];

        var rows2 = [];
        if($scope.saleHistory != null){
          for (var i = 0; i < $scope.saleHistory.length; i++) {
            rows2.push({
              issue_date: $filter('date')($scope.findDate($scope.saleHistory[i].issue_date), 'dd/MM/yyyy HH:mm:ss'),
              status: $scope.saleHistory[i].status,
              pax_name: $scope.saleHistory[i].pax_name,
              flight: $scope.saleHistory[i].flight,
              boardingDate: $filter('date')($scope.findDate($scope.saleHistory[i].boardingDate), 'dd/MM/yyyy HH:mm:ss'),
              landingDate: $filter('date')($scope.findDate($scope.saleHistory[i].landingDate), 'dd/MM/yyyy HH:mm:ss'),
              from: $scope.saleHistory[i].from,
              to: $scope.saleHistory[i].to,
              miles_used: $rootScope.formatNumber($scope.saleHistory[i].miles_used, 0)
            });
          }
        }
        
        doc.autoTable(columns, rows, {
          margin: {horizontal: 10},
          styles: {
            fontSize: 8
          },
          createdCell: function (cell, data) {
            if((data.column.dataKey === 'total_cost') || (data.column.dataKey === 'miles') || (data.column.dataKey === 'cost_per_thousand')){
              cell.styles.halign = 'right';
            }
          }
        });
        doc.text(60, doc.autoTableEndPosY() + 20, 'Vendas');
        doc.autoTable(columns2, rows2, {
          startY: doc.autoTableEndPosY() + 30,
          margin: {horizontal: 10},
          styles: {
            fontSize: 8
          },
          createdCell: function (cell, data) {
            if(data.column.dataKey === 'miles_used'){
              cell.styles.halign = 'right';
            }
          }
        });

        doc.save('historico_cartao.pdf');
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

      $scope.printHistoryToProvider = function() {
        var doc = new jsPDF('l', 'pt');
        doc.margin = 0.2;
        doc.setFontSize(10);

        doc.text(350, 50, 'Extrato do fornecedor: ' + $scope.selected.name);
        $scope.toDataUrl('images/bank.jpg', function(imgData) {
          doc.addImage(imgData, 'JPEG', 30, 15, 230, 50);

          doc.text(60, 80, 'Compras');
          var columns = [];

          columns = [
            {title: "Companhia", dataKey: "airline"},
            {title: "Data Compra", dataKey: "purchase_date"},
            {title: "Custo p/ 1000", dataKey: "cost_per_thousand"},
            {title: "Vencimento", dataKey: "due_date"},
            {title: "Custo total", dataKey: "total_cost"},
            {title: "Milhas", dataKey: "miles"}
          ];

          var rows = [];

          for (var i = 0; i < $scope.purchaseHistory.length; i++) {
            rows.push({
              airline: $scope.purchaseHistory[i].airline,
              purchase_date: $filter('date')($scope.findDate($scope.purchaseHistory[i].purchase_date), 'dd/MM/yyyy HH:mm:ss'),
              cost_per_thousand: $scope.purchaseHistory[i].cost_per_thousand,
              due_date: $filter('date')($scope.findDate($scope.purchaseHistory[i].miles_due_date), 'dd/MM/yyyy HH:mm:ss'),
              total_cost: $rootScope.formatNumber($scope.purchaseHistory[i].total_cost),
              miles: $rootScope.formatNumber($scope.purchaseHistory[i].purchase_miles, 0)
            });
          }
          
          var columns2 = [];
          columns2 = [
            {title: "Data da Venda", dataKey: "issue_date"},
            {title: "Status", dataKey: "status"},
            {title: "Passageiro", dataKey: "pax_name"},
            {title: "Voo", dataKey: "flight"},
            {title: "Data Embarque", dataKey: "boardingDate"},
            {title: "Data Desembarque", dataKey: "landingDate"},
            {title: "De", dataKey: "from"},
            {title: "Para", dataKey: "to"},
            {title: "Milhas", dataKey: "miles_used"}
          ];

          var rows2 = [];
          if($scope.saleHistory != null){
            for (var i = 0; i < $scope.saleHistory.length; i++) {
              rows2.push({
                issue_date: $filter('date')($scope.findDate($scope.saleHistory[i].issue_date), 'dd/MM/yyyy HH:mm:ss'),
                status: $scope.saleHistory[i].status,
                pax_name: $scope.saleHistory[i].pax_name,
                flight: $scope.saleHistory[i].flight,
                boardingDate: $filter('date')($scope.findDate($scope.saleHistory[i].boardingDate), 'dd/MM/yyyy HH:mm:ss'),
                landingDate: $filter('date')($scope.findDate($scope.saleHistory[i].landingDate), 'dd/MM/yyyy HH:mm:ss'),
                from: $scope.saleHistory[i].from,
                to: $scope.saleHistory[i].to,
                miles_used: $rootScope.formatNumber($scope.saleHistory[i].miles_used, 0)
              });
            }
          }
          
          doc.autoTable(columns, rows, {
            margin: { horizontal: 10 },
            startY: 90,
            styles: {
              fontSize: 8
            },
            createdCell: function (cell, data) {
              if((data.column.dataKey === 'total_cost') || (data.column.dataKey === 'miles') || (data.column.dataKey === 'cost_per_thousand')){
                cell.styles.halign = 'right';
              }
            }
          });

          doc.text(60, doc.autoTableEndPosY() + 20, 'Vendas');
          doc.autoTable(columns2, rows2, {
            startY: doc.autoTableEndPosY() + 30,
            margin: {horizontal: 10},
            styles: {
              fontSize: 8
            },
            createdCell: function (cell, data) {
              if(data.column.dataKey === 'miles_used'){
                cell.styles.halign = 'right';
              }
            }
          });

          doc.save($scope.selected.name.split(' ')[0] + '_' + $scope.selected.airline + '.pdf');
        });
      };

      $scope.print = function() {
        $.post("../backend/application/index.php?rota=/loadMilesbenchReportData", { searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function(result){
          $scope.reportData = jQuery.parseJSON(result).dataset;
          var doc = new jsPDF('l', 'pt');
          doc.margin = 0.5;
          doc.setFontSize(22);
          doc.text(350, 30, 'Estoque de Milhas');

          var columns = [
            {title: "Fornecedor", dataKey: "name"},
            {title: "Email", dataKey: "email"},
            {title: "Telefone", dataKey: "phoneNumber"},
            {title: "Cia", dataKey: "airline"},
            {title: "Tipo Cartão", dataKey: "card_type"},
            {title: "Cartao", dataKey: "card_number"},
            {title: "Saldo", dataKey: "leftover"},
            {title: "Vencimento", dataKey: "due_date"},
            {title: "Limite", dataKey: "contract_due_date"},
            {title: "Status", dataKey: "status"}
          ];

          var rows = [];
          var type;
          var status;
          for (var i = 0; i < $scope.reportData.length; i++) {
            if($scope.reportData[i].card_type === null)
              type = "";
            else
              type = $scope.reportData[i].card_type;

            if($scope.reportData[i].status == 'N')
              status = 'Liberado';
            else
              status = 'Bloqueado';

            rows.push({
              name: $scope.reportData[i].name,
              email: $scope.reportData[i].email,
              phoneNumber: $scope.reportData[i].phoneNumber,
              airline: $scope.reportData[i].airline,
              card_type: type,
              card_number: $scope.reportData[i].card_number,
              leftover: $rootScope.formatNumber($scope.reportData[i].leftover, 0),
              due_date: $filter('date')($scope.reportData[i].due_date ,'dd/MM/yyyy'),
              contract_due_date: $filter('date')($scope.reportData[i].contract_due_date ,'dd/MM/yyyy'),
              status: status
            });
          }
      
          doc.autoTable(columns, rows, {
            styles: {
              fontSize: 8
            },
            createdCell: function (cell, data) {
              if (data.column.dataKey === 'leftover') {
                cell.styles.halign = 'right';
              }
              if (data.column.dataKey === 'cost_per_thousand') {
                cell.styles.halign = 'right';
              }
            }
          });

          doc.save('estoque_de_milhas.pdf');
        });
      };

      $scope.orderMiles = function(mile) {
        switch (mile.priority) {
          case '-1':
            return "label label-success";
          case '0':
            return "label label-danger";
          case '1':
            return "label label-warning";
          default:
            return "";

        }
      };
      
      $scope.search = function() {
        // $scope.filteredMilesBenchs = $filter('filter')($scope.milebench, $scope.searchKeywords);
        // return $scope.onFilterChange();
        $scope.loadMiles();
      };
      
      $scope.order = function(rowName) {
        // if ($scope.row === rowName) {
        //   return;
        // }
        // $scope.row = rowName;
        // $scope.filteredMilesBenchs = $filter('orderBy')($scope.milebench, rowName);
        // return $scope.onOrderChange();
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadMiles();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadMiles();
      };
      
      $scope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
        return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
      };
      
      $scope.numPerPageOptSale = [10, 25, 50, 100, 2000, 5000, 10000];
      $scope.numPerPageSale = $scope.numPerPageOptSale[2];
      $scope.currentPageSale = 1;


      $scope.numPerPageOpt = [10, 30, 50, 100, 2000, 5000, 10000];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageMilesBench = [];

      $scope.loadMiles = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadMiles", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function(result){
            $scope.milebench = jQuery.parseJSON(result).dataset.milebench;
            $scope.totalData = jQuery.parseJSON(result).dataset.total;
            $scope.totalFiltered = jQuery.parseJSON(result).dataset.totalFiltered;
            $scope.totalMilesRB = jQuery.parseJSON(result).dataset.totalMilesRB;
            $scope.$digest();
            console.log(jQuery.parseJSON(result).dataset);
            cfpLoadingBar.complete();
        });
      };

      $scope.open = function() {
        $.post("../backend/application/index.php?rota=/loadAirline", {}, function(result){
          $scope.airlines = jQuery.parseJSON(result).dataset;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "MilesBenchModalCtrl.html",
            controller: 'MilesBenchDataInstanceCtrl',
            resolve: {
              airlines: function() {
                return $scope.airlines;
              },
              filter: function() {
                return $scope.filter;
              }
            }
          });

          modalInstance.result.then(function(filter) {
            $scope.filter = filter;
            $scope.loadMiles();
          });
        });
      };

      $scope.openPassengersList = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "app/modals/modal_names_list.html",
          controller: 'ModalNamesLIstCtrl',
          resolve: {
            selected: function() {
              return $scope.selected;
            },
            paxPerCards: function() {
              return $scope.paxPerCards;
            }
          }
        });
      };

      $scope.saveCardStatus = function(card) {
        $.post("../backend/application/index.php?rota=/saveCardProgressMilesBench", {data: card}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
      };

      $scope.openCardModalCtrl = function() {
        $scope.card = $scope.selected;
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "MilesbenchCardModalCtrl.html",
          controller: 'MilesbenchCardInstanceCtrl',
          resolve: {
            card: function() {
              return $scope.card;
            }
          }
        });

        modalInstance.result.then(function(card) {
          $scope.saveCardStatus(card);
        });

      };
      
      init = function() {
        $scope.tabIndex = 0;
        $scope.totalFiltered = 0;
        $scope.filter = { airline: '', miles: 900, includeZero: false };
        $scope.checkValidRoute();
        $scope.loadMiles();
      };
      return init();

    }
  ]).controller('MilesBenchDataInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'airlines', 'filter', function($scope, $rootScope, $modalInstance, airlines, filter) {
      $scope.airlines = airlines;
      $scope.providers = [];
      
      $scope.loadProvider = function() {
        $.post("../backend/application/index.php?rota=/loadProvider", { searchKeywords: $scope.filter.providerName }, function(result){
          $scope.providers = jQuery.parseJSON(result).dataset.providers;
          $scope.$digest();
        });
      };
      
      $scope.filter = filter;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
    }
  ]).controller('MilesbenchCardInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'card', function($scope, $rootScope, $modalInstance, card) {
      $scope.card = card;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.ok = function() {
        $modalInstance.close($scope.card);
      };
    }
  ]).controller('ModalNamesLIstCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'selected', 'paxPerCards', function($scope, $rootScope, $modalInstance, selected, paxPerCards) {
      $scope.selected = selected;
      $scope.array = paxPerCards;
      console.log($scope.array)

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

    }
  ]);
})();
;
