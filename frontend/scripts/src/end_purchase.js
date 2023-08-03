(function () {
  'use strict';
  angular.module('app.table').controller('EndPurchaseCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function ($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      $scope.searchKeywords = '';
      $scope.filteredPurchases = [];
      $scope.row = '';

      $scope.select = function (page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPagePurchases = $scope.filteredPurchases.slice(start, end);
      };

      $scope.onFilterChange = function () {
        // $scope.select(1);
        // $scope.currentPage = 1;
        // return $scope.row = '';
        $scope.loadPurchasesWaiting();
      };

      $scope.onNumPerPageChange = function () {
        // $scope.select(1);
        // return $scope.currentPage = 1;
        $scope.loadPurchasesWaiting();
      };

      $scope.onOrderChange = function () {
        // $scope.select(1);
        // return $scope.currentPage = 1;
        $scope.loadPurchasesWaiting();
      };

      $scope.findColor = function (purchase) {
        if (purchase.payment_status == 'A')
          return "#FBA1A1";
        else
          return;
      };

      $scope.findDate = function (date) {
        return new Date(date);
      };

      $scope.setSelected = function () {
        $scope.selected = {};
        $scope.selected = this.purchase;
        //$scope.isTable = false;
        $scope.airlineShowFields = (this.purchase.airline == 'TAM' || this.purchase.airline == 'LATAM');
        $scope.purchaseDate = new Date($scope.selected.purchaseDate + 'T12:00:00Z');
        $scope.selected.milesDueDate = new Date($scope.selected.milesDueDate + 'T12:00:00Z');
        $scope.selected.payment_date = new Date($scope.selected.payment_date);
        $scope.contractDueDate = new Date($scope.selected.contract_due_date + 'T12:00:00Z');
        $('#purchasemiles').number(true, 0, ',', '.');
        $('#purchasecostperthousand').number(true, 2, ',', '.');
        $('#purchasetotalcost').number(true, 2, ',', '.');
        if ($scope.selected.bloqued == "Y")
          $scope.selected.is_bloqued = true;
        else
          $scope.selected.is_bloqued = false;

        if ($scope.selected.status_purchase == 'T') {
          $scope.openModalTransfer();
        } else {
          $scope.isTable = false;
        }

        $scope.selected.isPriority = false;
        if ($scope.selected.partner_status == 'Pendente' || $scope.selected.partner_status == 'Bloqueado') {
          logger.logError("Status do cliente: " + $scope.selected.partner_status);
        }
        return true;
      };

      $scope.saveStatus = function () {
        if ($scope.selected.airline == 'LATAM') {
          if ($scope.selected.onlyInter == null || $scope.selected.onlyInter == 'null') {
            return logger.logError('Tipo de emissão obrigatorio!');
          }
        }
        if($scope.loading == true) {
          return logger.logError('processo em andamento!');
        }

        if ($scope.form_purchase.$valid) {
          $scope.loading = true;
          cfpLoadingBar.start();
          $scope.selected.recoveryPassword = $scope.ecript($scope.selected.recoveryPassword);
          $scope.selected.accessPassword = $scope.ecript($scope.selected.accessPassword);
          $scope.selected.contract_due_date = $rootScope.formatServerDate($scope.contractDueDate);
          $scope.selected._milesDueDate = $rootScope.formatServerDate($scope.selected.milesDueDate);
          $.post("../backend/application/index.php?rota=/generatePurchase", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            $scope.loading = false;
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.isTable = true;
            $scope.$apply();
            cfpLoadingBar.complete();
            $scope.loadPurchasesWaiting();
          });
        } else {
          logger.logError("Existem dados necessarios não vinculados!");
        }
      };

      $scope.print = function(){
        // LATAM
        $scope.filter.airline = 'LATAM';
        $.post("../backend/application/index.php?rota=/loadPurchasesToOperations", { order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter, searchKeywords: $scope.searchKeywords }, function (result) {
          $scope.purchasesReport = jQuery.parseJSON(result).dataset.purchases;
        
          var data = [['Dt finalizado', 'PG', 'Cadastro', 'Data Comp.', 'Cliente', 'token', 'Nº Fidelidade', 'CARTÃO',	'Ass Eletronica',	'Senha Resgate', 'CPF/Multiplus',	'Senha Multiplus', 'VENCIMENTO', 'Qtd. de Milhas', 'Milhas Utilizadas', 'TOTAL', 'Valor p/ 1.000 pts', 'Valor Total', 'OBS.', 'Telefone Contato', 'E-mail Contato']];
          
          for(let i in $scope.purchasesReport) {
            data.push([
              ($scope.purchasesReport[i].merge_date != '' ? $filter('date')(new Date($scope.purchasesReport[i].merge_date), 'dd/MM/yyyy hh:mm:ss') : ''),
              ($scope.purchasesReport[i].Billspay_DueDate != '' ? $filter('date')(new Date($scope.purchasesReport[i].Billspay_DueDate), 'dd/MM/yyyy hh:mm:ss') : ''),
              $scope.purchasesReport[i].Cadastro,
              $filter('date')(new Date($scope.purchasesReport[i].purchase_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.purchasesReport[i].providerName,
              $scope.purchasesReport[i].token,
              $scope.purchasesReport[i].card_number,
              '',
              '',
              $scope.decript($scope.purchasesReport[i].recovery_password),
              $scope.purchasesReport[i].registration_code,
              $scope.decript($scope.purchasesReport[i].access_password),
              $filter('date')(new Date($scope.purchasesReport[i].miles_due_date), 'dd/MM/yyyy hh:mm:ss'),
              $rootScope.formatNumber($scope.purchasesReport[i].leftover, 0),
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles - $scope.purchasesReport[i].leftover, 0),
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles, 0),
              $scope.purchasesReport[i].cost_per_thousand,
              $scope.purchasesReport[i].total_cost,
              '',
              $scope.purchasesReport[i].phone_number+' / '+$scope.purchasesReport[i].phone_number2,
              $scope.purchasesReport[i].email,
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
          link.setAttribute("download", "latam.csv");
          document.body.appendChild(link);

          link.click();
        })

        // AZUL
        $scope.filter.airline = 'AZUL';
        $.post("../backend/application/index.php?rota=/loadPurchasesToOperations", { order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter, searchKeywords: $scope.searchKeywords }, function (result) {
          $scope.purchasesReport = jQuery.parseJSON(result).dataset.purchases;
        
          var data = [['Dt finalizado', 'PG', 'Cadastro', 'Data Comp.', 'Cliente', 'Nº  Cartão', 'Senha', 'CPF', 'Qtd de Milhas', 'Milhas Utilizadas', 'REAL Valor p/ 1.000 pts', 'Valor Total', 'Análise', 'OBS.', 'Telefone Contato', 'E-mail Contato', 'CONTA', 'CARTÃO', 'BANDEIRA', 'VALIDADE', 'CÓD.', 'NOME', 'obs 2:']];
          
          for(let i in $scope.purchasesReport) {
            data.push([
              ($scope.purchasesReport[i].merge_date != '' ? $filter('date')(new Date($scope.purchasesReport[i].merge_date), 'dd/MM/yyyy hh:mm:ss') : ''),
              ($scope.purchasesReport[i].Billspay_DueDate != '' ? $filter('date')(new Date($scope.purchasesReport[i].Billspay_DueDate), 'dd/MM/yyyy hh:mm:ss') : ''),
              $scope.purchasesReport[i].Cadastro,
              $filter('date')(new Date($scope.purchasesReport[i].purchase_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.purchasesReport[i].providerName,
              $scope.purchasesReport[i].card_number,
              $scope.decript($scope.purchasesReport[i].recovery_password),
              $scope.purchasesReport[i].registration_code,
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles, 0),
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles - $scope.purchasesReport[i].leftover, 0),
              $scope.purchasesReport[i].cost_per_thousand,
              $scope.purchasesReport[i].total_cost,
              '',
              '',
              $scope.purchasesReport[i].phone_number+' / '+$scope.purchasesReport[i].phone_number2,
              $scope.purchasesReport[i].email,
              '',
              '',
              '',
              '',
              '',
              '',
              '',
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
          link.setAttribute("download", "azul.csv");
          document.body.appendChild(link);

          link.click();
        })

        // AVIANCA
        $scope.filter.airline = 'AVIANCA';
        $.post("../backend/application/index.php?rota=/loadPurchasesToOperations", { order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter, searchKeywords: $scope.searchKeywords }, function (result) {
          $scope.purchasesReport = jQuery.parseJSON(result).dataset.purchases;
        
          var data = [['Dt finalizado', 'PG', 'Cadastro', 'Data Comp.', 'Cliente', 'Nº  Cartão', 'Senha', 'CPF', 'Qtd de Milhas', 'Milhas Utilizadas', 'REAL Valor p/ 1.000 pts', 'Valor Total', 'Situação da Análise', 'Telefone Contato', 'E-mail Contato', 'PG', 'EXPIRAR']];
          
          for(let i in $scope.purchasesReport) {
            data.push([
              ($scope.purchasesReport[i].merge_date != '' ? $filter('date')(new Date($scope.purchasesReport[i].merge_date), 'dd/MM/yyyy hh:mm:ss') : ''),
              ($scope.purchasesReport[i].Billspay_DueDate != '' ? $filter('date')(new Date($scope.purchasesReport[i].Billspay_DueDate), 'dd/MM/yyyy hh:mm:ss') : ''),
              $scope.purchasesReport[i].Cadastro,
              $filter('date')(new Date($scope.purchasesReport[i].purchase_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.purchasesReport[i].providerName,
              $scope.purchasesReport[i].card_number,
              $scope.decript($scope.purchasesReport[i].recovery_password),
              $scope.purchasesReport[i].registration_code,
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles, 0),
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles - $scope.purchasesReport[i].leftover, 0),
              $scope.purchasesReport[i].cost_per_thousand,
              $scope.purchasesReport[i].total_cost,
              '',
              $scope.purchasesReport[i].phone_number+' / '+$scope.purchasesReport[i].phone_number2,
              $scope.purchasesReport[i].email,
              '',
              '',
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
          link.setAttribute("download", "avianca.csv");
          document.body.appendChild(link);

          link.click();
        })

        // GOL
        $scope.filter.airline = 'GOL';
        $.post("../backend/application/index.php?rota=/loadPurchasesToOperations", { order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter, searchKeywords: $scope.searchKeywords }, function (result) {
          $scope.purchasesReport = jQuery.parseJSON(result).dataset.purchases;
        
          var data = [['Dt finalizado', 'PG', 'Cadastro', 'Data Comp.', 'Cliente', 'Nº  Cartão', 'Senha', 'CARTAO', 'CPF', 'Qtd de Milhas', 'Milhas Utilizadas', 'REAL Valor p/ 1.000 pts', 'Valor Total', 'Situação da Análise', 'OBS.', 'Telefone Contato', 'E-mail Contato', 'EXPIRAR']];
          
          for(let i in $scope.purchasesReport) {
            data.push([
              ($scope.purchasesReport[i].merge_date != '' ? $filter('date')(new Date($scope.purchasesReport[i].merge_date), 'dd/MM/yyyy hh:mm:ss') : ''),
              ($scope.purchasesReport[i].Billspay_DueDate != '' ? $filter('date')(new Date($scope.purchasesReport[i].Billspay_DueDate), 'dd/MM/yyyy hh:mm:ss') : ''),
              $scope.purchasesReport[i].Cadastro,
              $filter('date')(new Date($scope.purchasesReport[i].purchase_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.purchasesReport[i].providerName,
              $scope.purchasesReport[i].card_number,
              $scope.decript($scope.purchasesReport[i].recovery_password),
              $scope.purchasesReport[i].registration_code,
              $scope.purchasesReport[i].registration_code,
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles, 0),
              $rootScope.formatNumber($scope.purchasesReport[i].purchase_miles - $scope.purchasesReport[i].leftover, 0),
              $scope.purchasesReport[i].cost_per_thousand,
              $scope.purchasesReport[i].total_cost,
              '',
              '',
              $scope.purchasesReport[i].phone_number+' / '+$scope.purchasesReport[i].phone_number2,
              $scope.purchasesReport[i].email,
              '',
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
          link.setAttribute("download", "gol.csv");
          document.body.appendChild(link);

          link.click();
        })
      };

      $scope.ecript = function (code) {
        if (code != undefined) {
          var data = code.split('');
          var finaly = '';
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + (data[j].charCodeAt(0) * 320) + '320AB';
          }
          return finaly;
        }
      };

      $scope.cardPassword = function () {
        for (var i = 0; $scope.purchases.length > i; i++) {
          $scope.purchases[i].recoveryPassword = $scope.decript($scope.purchases[i].recoveryPassword);
          if ($scope.purchases[i].airline == "TAM" || $scope.purchases[i].airline == "LATAM") {
            $scope.purchases[i].accessPassword = $scope.decript($scope.purchases[i].accessPassword);
          }
          else {
            $scope.purchases[i].accessPassword = " - ";
          }
        }
        $scope.$apply();
      };

      $scope.decript = function (code) {
        if (code != null && code != undefined) {
          var data = code.split('320AB');
          var finaly = '';
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + (String.fromCharCode(data[j] / 320));
          }
          return finaly;
        }
      };

      $scope.setTotalCost = function () {
        $scope.selected.totalCost = ($scope.selected.purchaseMiles / 1000) * $scope.selected.costPerThousand;
        return $scope.$apply();
      };

      $scope.toggleFormTable = function () {
        $scope.isTable = !$scope.isTable;
        return $scope.isTable;
      };

      $scope.search = function () {
        $scope.loadPurchasesWaiting();
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPagePurchases = [];

      $scope.order = function (rowName) {
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadPurchasesWaiting();
      };

      $scope.orderDown = function (rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadPurchasesWaiting();
      };

      $scope.loadPurchasesWaiting = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadPurchasesWaiting", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function (result) {
          $scope.purchases = jQuery.parseJSON(result).dataset.purchases;
          $scope.totalData = jQuery.parseJSON(result).dataset.total;
          $scope.cardPassword();
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };

      $scope.toDataUrl = function (url, callback) {
        var xhr = new XMLHttpRequest();
        xhr.responseType = 'blob';
        xhr.onload = function () {
          var reader = new FileReader();
          reader.onloadend = function () {
            callback(reader.result);
          };
          reader.readAsDataURL(xhr.response);
        };
        xhr.open('GET', url);
        xhr.send();
      };

      $scope.printUtilizacao = function () {
        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFont("times");
        doc.setFontSize(10);

        // doc.rect(20, 70, 100, 10)
        // doc.rect(20, 80, 100, 10)
        doc.line(30, 80, 100, 80)
        doc.text(30, 78, "TOKEN");
        doc.text(30, 88, $scope.selected.token);

        // doc.rect(20, 100, 100, 10)
        // doc.rect(20, 110, 100, 10)
        doc.line(30, 110, 100, 110)
        doc.text(30, 108, "COMPRA");
        doc.text(30, 118, $filter('date')(new Date($scope.selected.purchaseDate + 'T12:00:00Z'), 'dd/MM/yyyy'));

        // doc.rect(20, 130, 100, 10)
        // doc.rect(20, 140, 100, 10)
        doc.line(30, 140, 100, 140)
        doc.text(30, 138, "VENCIMENTO");
        doc.text(30, 148, $filter('date')($scope.selected.milesDueDate, 'dd/MM/yyyy'));


        $scope.toDataUrl('images/bank.jpg', function (base64Img) {
          doc.addImage(base64Img, 'JPEG', 50, 20, 150, 40);
          doc.setFontSize(28);
          doc.text(300, 50, "FICHA DE USO ");
          doc.setFontSize(10);
          var start = 100;
          
          // doc.rect(300, start, 100, 10)
          // doc.rect(300, start + 10, 100, 10)
          doc.line(305, start + 10, 400, start + 10)
          doc.text(305, start + 8, "CPF");
          doc.text(305, start + 18, $scope.selected.registrationCode);

          start += 30;

          // doc.rect(300, start, 270, 10)
          // doc.rect(300, start + 10, 270, 10)
          doc.line(305, start + 10, 500, start + 10)
          doc.text(305, start + 8, "NOME DO TITULAR");
          doc.text(305, start + 18, $scope.selected.partnerName);

          start += 50;

          doc.setDrawColor(91, 0, 255)
          doc.setFillColor(91, 0, 255)
          doc.rect(20, start, 550, 10, 'FD')

          start += 20;

          doc.autoTable(
            [{ title: "Cia", dataKey: "cia" }, { title: "Pontos", dataKey: "pts" }, { title: "Nº FIDELIDADE", dataKey: "numero" }, { title: "SENHA", dataKey: 'senha' }],
            [{ cia: $scope.selected.airline, pts: $rootScope.formatNumber($scope.selected.purchaseMiles, 0), numero: $scope.selected.cardNumber, senha: $scope.selected.recoveryPassword }],
            {
              startY: start,
              margin: { left: 150, right: 150 },
              theme: 'striped',
              styles: { overflow: 'linebreak' },
              headerStyles: {
                height: 8,
                fontSize: 8,
              }
            });
          start = doc.autoTableEndPosY() + 20;

          for (let index = 0; index < 10; index++) {


            doc.rect(20, start, 70, 20)
            doc.text(25, start + 10, "PEDIDO " + (parseInt(index) + 1));

            doc.rect(90, start, 150, 20)
            doc.text(95, start + 10, "DATA: ");

            doc.rect(240, start, 150, 20)
            doc.text(245, start + 10, "LOC: ");

            doc.rect(390, start, 150, 20)
            doc.text(395, start + 10, "Ptos Vendidos: ");


            start += 30;
          }

          doc.autoTable(
            [
              { title: "", dataKey: "nada" },
              { title: "PEDIDO 1", dataKey: "1" },
              { title: "PEDIDO 2", dataKey: "2" },
              { title: "PEDIDO 3", dataKey: "3" },
              { title: "PEDIDO 4", dataKey: "4" },
              { title: "PEDIDO 5", dataKey: '5' }
            ],
            [
              { nada: "PTS UTILIZADOS", 1: "", 2: "", 3: "", 4: "", 5: "" },
              { nada: "SALDO", 1: "", 2: "", 3: "", 4: "", 5: "" }
            ],
            {
              startY: start,
              margin: { left: 20, right: 20 },
              theme: 'grid',
              styles: { overflow: 'linebreak' },
              headerStyles: {
                height: 8,
                fontSize: 8,
              }
            });
          start = doc.autoTableEndPosY() + 40;

          doc.autoTable(
            [
              { title: "", dataKey: "nada" },
              { title: "PEDIDO 6", dataKey: "6" },
              { title: "PEDIDO 7", dataKey: "7" },
              { title: "PEDIDO 8", dataKey: "8" },
              { title: "PEDIDO 9", dataKey: "9" },
              { title: "PEDIDO 10", dataKey: '10' }
            ],
            [
              { nada: "PTS UTILIZADOS", 6: "", 7: "", 8: "", 9: "", 10: "" },
              { nada: "SALDO", 6: "", 7: "", 8: "", 9: "", 10: "" }
            ],
            {
              startY: start,
              margin: { left: 20, right: 20 },
              theme: 'grid',
              styles: { overflow: 'linebreak' },
              headerStyles: {
                height: 8,
                fontSize: 8,
              }
            });
          start = doc.autoTableEndPosY() + 40;

          doc.save($scope.selected.registrationCode + '_utilizacao_' + '.pdf');
        });
      };

      $scope.printReport = function () {
        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFont("times");

        $scope.toDataUrl('images/bank.jpg', function (base64Img) {
          doc.addImage(base64Img, 'JPEG', 50, 20, 110, 40);
          doc.text(220, 40, "FICHA DE PAGAMENTO ");
          var start = 70;
          doc.autoTable(
            [{ title: "Nome", dataKey: "name" }, { title: "CPF", dataKey: "cpf" }, { title: "Email", dataKey: "email" }],
            [{ name: $scope.selected.partnerName, cpf: mCPF($scope.selected.registrationCode), email: $scope.selected.email }],
            {
              startY: start,
              margin: { left: 100, rigth },
              theme: 'plain',
              styles: { overflow: 'linebreak' },
              headerStyles: {
                height: 8,
                fontSize: 8,
                fillColor: [44, 74, 175],
                textColor: 255
              }
            });
          start = doc.autoTableEndPosY() + 15;

          doc.autoTable(
            [{ title: "Companhia", dataKey: "cia" }, { title: "Data Compra", dataKey: "date" }],
            [{ cia: $scope.selected.airline, date: $filter('date')(new Date($scope.selected.purchaseDate + 'T12:00:00Z'), 'dd/MM/yyyy') }],
            {
              startY: start,
              margin: { left: 50, right: 50 },
              theme: 'plain',
              styles: { overflow: 'linebreak' },
              columnStyles: {
                cia: { columnWidth: '100' },
                date: { columnWidth: '100' },
                0: { columnWidth: '100' },
                1: { columnWidth: '100' },
                title: { halign: 'center', fillColor: [0, 0, 255] }
              },
              headerStyles: {
                height: 8,
                fontSize: 8,
                fillColor: [44, 74, 175],
                textColor: 255
              }
            });
          start = doc.autoTableEndPosY() + 15;

          doc.autoTable(
            [{ title: "Milhas", dataKey: "miles" }, { title: "Valor Por Milhar", dataKey: "milhar" }, { title: "Valor Total", dataKey: "total" }],
            [{ miles: $rootScope.formatNumber($scope.selected.purchaseMiles, 0), milhar: 'R$ ' + $rootScope.formatNumber($scope.selected.costPerThousand, 2), total: 'R$ ' + $rootScope.formatNumber($scope.selected.totalCost, 2) }],
            {
              startY: start,
              margin: { left: 50, right: 50 },
              theme: 'plain',
              columnStyles: {
                title: { halign: 'center', fillColor: [0, 0, 255] }
              },
              headerStyles: {
                fillColor: [44, 74, 175],
                textColor: 255
              }
            });
          start = doc.autoTableEndPosY() + 15;

          doc.autoTable(
            [{ title: "Vencimento", dataKey: "vencimento" }, { title: "Limite Comercialização", dataKey: "limite" }, { title: "Data Pagamento", dataKey: "pagamento" }],
            [{ vencimento: $filter('date')($scope.selected.milesDueDate, 'dd/MM/yyyy'), limite: $filter('date')($scope.contractDueDate, 'dd/MM/yyyy'), pagamento: $filter('date')(new Date($scope.selected.payment_date), 'dd/MM/yyyy') }],
            {
              startY: start,
              margin: { left: 50, right: 50 },
              theme: 'plain',
              columnStyles: {
                title: { halign: 'center', fillColor: [0, 0, 255] }
              },
              headerStyles: {
                fillColor: [44, 74, 175],
                textColor: 255
              }
            });
          start = doc.autoTableEndPosY() + 15;

          // doc.autoTable(
          //   [{ title: "Fidelidade", dataKey: "fidelidade" }, { title: "Senha resgate", dataKey: "resgate" }, { title: "Senha Multiplus", dataKey: "multiplus" }],
          //   [{ fidelidade: $scope.selected.cardNumber, resgate: $scope.selected.recoveryPassword, multiplus: $scope.selected.accessPassword }],
          //   {
          //     startY: start,
          //     margin: { left: 50, right: 50 },
          //     theme: 'plain',
          //     columnStyles: {
          //       title: { halign: 'center', fillColor: [0, 0, 255] }
          //     },
          //     headerStyles: {
          //       fillColor: [44, 74, 175],
          //       textColor: 255
          //     }
          //   });
          // start = doc.autoTableEndPosY() + 15;

          var onlyInter = '';
          if ($scope.selected.onlyInter == 'true') {
            onlyInter = 'Internacional';
          }
          if ($scope.selected.onlyInter == 'false') {
            onlyInter = 'Nacional';
          }
          // doc.autoTable(
          //   [{ title: "Tipo Emissao", dataKey: "emissao" }, { title: "Tipo Cartao", dataKey: "cartao" }, { title: "Token", dataKey: "token" }],
          //   [{ emissao: onlyInter, cartao: $scope.selected.card_type, token: $scope.selected.token }],
          //   {
          //     startY: start,
          //     margin: { left: 50, right: 50 },
          //     theme: 'plain',
          //     columnStyles: {
          //       title: { halign: 'center', fillColor: [0, 0, 255] }
          //     },
          //     headerStyles: {
          //       fillColor: [44, 74, 175],
          //       textColor: 255
          //     }
          //   });
          // start = doc.autoTableEndPosY() + 15;

          doc.line(0, start, 600, start);
          start += 15;

          doc.autoTable(
            [{ title: "Banco", dataKey: "bank" }, { title: "Agencia", dataKey: "agency" }, { title: "Conta", dataKey: "account" }, { title: "Operacao", dataKey: "bankOperation" }],
            [{ emissao: ($scope.selected.bank ? $scope.selected.bank : ''), cartao: ($scope.selected.agency ? $scope.selected.agency : ''), token: ($scope.selected.account ? $scope.selected.account : ''), bankOperation: ($scope.selected.bankOperation ? $scope.selected.bankOperation : '') }],
            {
              startY: start,
              margin: { left: 50, right: 50 },
              theme: 'plain',
              columnStyles: {
                title: { halign: 'center', fillColor: [0, 0, 255] }
              },
              headerStyles: {
                height: 8,
                fontSize: 8,
                fillColor: [44, 74, 175],
                textColor: 255
              }
            });
          start = doc.autoTableEndPosY() + 15;

          doc.autoTable(
            [{ title: "Nome Titular", dataKey: "bankNameOwner" }, { title: "CPF Titular", dataKey: "cpfNameOwner" }],
            [{ emissao: ($scope.selected.bankNameOwner ? $scope.selected.bankNameOwner : ''), cartao: mCPF($scope.selected.cpfNameOwner) }],
            {
              startY: start,
              margin: { left: 50, right: 50 },
              theme: 'plain',
              columnStyles: {
                title: { halign: 'center', fillColor: [0, 0, 255] }
              },
              headerStyles: {
                height: 8,
                fontSize: 8,
                fillColor: [44, 74, 175],
                textColor: 255
              }
            });
          start = doc.autoTableEndPosY() + 15;

          doc.save($scope.selected.registrationCode + '.pdf');
        })
      };

      $scope.openSearchModal = function () {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "EndPurchase.html",
          controller: 'EndPurchaseModalInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function () {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function (filter) {
          if (filter != undefined) {
            filter._purchaseDateFrom = $rootScope.formatServerDate(filter.purchaseDateFrom);
            filter._purchaseDateTo = $rootScope.formatServerDate(filter.purchaseDateTo);
          }
          $scope.filter = filter;
          $scope.loadPurchasesWaiting();
        }), function () {
          console.log("Modal dismissed at: " + new Date());
        });
      };

      function mCPF(cpf) {
        if (!cpf || cpf == '') {
          return ''
        }
        cpf = cpf.replace(/\D/g, "")
        cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2")
        cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2")
        cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2")
        return cpf
      }

      $scope.loadPurchasesBusinessPartner = function () {

      };

      init = function () {
        $scope.checkValidRoute();
        $scope.isTable = true;
        $scope.loading = false;
        $scope.loadPurchasesWaiting();
      };
      return init();
    }
  ]).controller('EndPurchaseModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function ($scope, $rootScope, $modalInstance, filter) {

      $scope.statusPrucases = ['Confirmadas', 'Canceladas', 'Pendentes'];
      $scope.filter = filter;

      $scope.searchProviders = function () {
        $.post("../backend/application/index.php?rota=/loadProvider", { searchKeywords: $scope.filter.providerName }, function (result) {
          $scope.providers = jQuery.parseJSON(result).dataset.providers;
          $scope.$digest();
        });
      };

      $scope.ok = function () {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;
