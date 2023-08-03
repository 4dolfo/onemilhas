(function () {
  "use strict";
  angular
    .module("app.table", ["ui.calendar"])
    .controller("PurchaseBillsPayCtrl", [
      "$scope",
      "$rootScope",
      "$filter",
      "$modal",
      "cfpLoadingBar",
      "logger",
      "FileUploader",
      function (
        $scope,
        $rootScope,
        $filter,
        $modal,
        cfpLoadingBar,
        logger,
        FileUploader
      ) {
        var init;
        $scope.accountType = ["Compra Milhas"];
        $scope.paymentType = [
          "Cartão de Crédito",
          "Deposito em Conta",
          "Boleto Bancario",
          "Reembolso",
        ];
        $scope.periods = [
          "Ultimos 30 dias",
          "Mes Corrente",
          "Semana Corrente",
          "Hoje",
        ];
        $scope.searchKeywords = "";
        $scope.filteredBillsPay = [];
        $scope.row = "";

        /*window.$scope = $scope;
        $scope.saveClosePayInterval = function(baixo, alto, apply){
          var dados = {
            'baixo': baixo + " 00:00:00",
            'alto': alto + " 23:59:59"
          }
          console.log(dados);
          $.post(
            "../backend/application/index.php?rota=/saveClosePayInterval",
            { data: dados, hashId: $scope.session.hashId, apply: apply },
            function (result) {
              $scope.resposta_query = jQuery.parseJSON(result).dataset;
              console.log($scope.resposta_query);
              logger.logSuccess(jQuery.parseJSON(result).message.text);
            }
          );
        };*/

        $scope.select = function (page) {
          // var end, start;
          // start = (page - 1) * $scope.numPerPage;
          // end = start + $scope.numPerPage;
          // return $scope.currentPageBillsPay = $scope.filteredBillsPay.slice(start, end);
          $scope.loadData();
        };

        $scope.onFilterChange = function () {
          $scope.select(1);
          $scope.currentPage = 1;
          return ($scope.row = "");
        };

        $scope.onNumPerPageChange = function () {
          // $scope.select(1);
          // return $scope.currentPage = 1;
          $scope.loadData();
        };

        $scope.findColor = function (billpay) {
          var dueDate = new Date(billpay.due_date);
          var actualDate = new Date();
          dueDate.setDate(dueDate.getDate() + 1);

          if (billpay.status == "B") {
            return "#9DCE9D";
          }
          if (
            dueDate.getDate() == actualDate.getDate() &&
            dueDate.getMonth() == actualDate.getMonth() &&
            dueDate.getFullYear() == actualDate.getFullYear()
          ) {
            return "#E2E298";
          }
          if (dueDate < actualDate) {
            return "#FBA1A1";
          }
        };

        $scope.findDate = function (date) {
          if (date == "") {
            return "";
          }
          return new Date(date);
        };

        $scope.onOrderChange = function () {
          $scope.select(1);
          return ($scope.currentPage = 1);
        };

        $scope.setSelected = function () {
          $scope.selected = this.billpay;
          $scope.selected._due_date = new Date($scope.selected.due_date);
          $scope.selected._due_date.setDate(
            $scope.selected._due_date.getDate() + 1
          );
          $scope.tabindex = 1;
          $("#bpay_actualvalue").number(true, 2, ",", ".");
          $("#bpay_tax").number(true, 2, ",", ".");
          $("#bpay_discount").number(true, 2, ",", ".");
          return $scope.selected;
        };

        $scope.print = function () {
          var doc = new jsPDF("l", "pt");
          doc.margin = 0.5;
          doc.setFontSize(20);
          doc.text(150, 30, "Contas a Pagar");

          var columns = [
            { title: "Status", dataKey: "status" },
            { title: "Fornecedor", dataKey: "partner" },
            { title: "Email", dataKey: "email" },
            { title: "Telefone", dataKey: "phone" },
            { title: "Tipo de Conta", dataKey: "account_type" },
            { title: "Valor original", dataKey: "original_value" },
            { title: "Desconto", dataKey: "discount" },
            { title: "Valor Atual", dataKey: "actual_value" },
            { title: "Vencimento", dataKey: "due_date" },
            { title: "Tipo de Pagamento", dataKey: "payment_type" },
          ];

          var rows = [];

          for (var i = 0; i < $scope.filteredBillsPay.length; i++) {
            rows.push({
              status: $scope.getStatusDesc($scope.filteredBillsPay[i].status),
              partner: $scope.filteredBillsPay[i].provider,
              email: $scope.filteredBillsPay[i].email,
              phone: $scope.filteredBillsPay[i].phoneNumber,
              account_type: $scope.filteredBillsPay[i].account_type,
              original_value: $rootScope.formatNumber(
                $scope.filteredBillsPay[i].original_value
              ),
              discount: $rootScope.formatNumber(
                $scope.filteredBillsPay[i].discount
              ),
              actual_value: $rootScope.formatNumber(
                $scope.filteredBillsPay[i].actual_value
              ),
              due_date: $filter("date")(
                $scope.filteredBillsPay[i].due_date,
                "dd/MM/yyyy"
              ),
              payment_type: $scope.filteredBillsPay[i].payment_type,
            });
          }

          doc.autoTable(columns, rows, {
            styles: {
              fontSize: 8,
            },
            createdCell: function (cell, data) {
              if (data.column.dataKey === "original_value") {
                cell.styles.halign = "right";
              }
              if (data.column.dataKey === "discount") {
                cell.styles.halign = "right";
              }
              if (data.column.dataKey === "actual_value") {
                cell.styles.halign = "right";
              }
            },
          });
          doc.save("Contas_A_Pagar.pdf");
        };

        $scope.getStatusDesc = function (status) {
          if (status == "B") {
            return "Baixada";
          } else {
            return "Em Aberto";
          }
        };

        $scope.toggleFormTable = function () {
          $scope.tabindex = 0;
        };

        $scope.cancelBillsPay = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/modal_justification.html",
            controller: "ModalJustificationCtrl",
            resolve: {
              header: function () {
                return "Digite o motivo da exclusão";
              },
            },
          });

          modalInstance.result.then(function (filter) {
            $scope.selected.deleteDescription = filter;
            $.post(
              "../backend/application/index.php?rota=/cancelBillsPay",
              { data: $scope.selected },
              function (result) {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $scope.tabindex = 0;
                $scope.loadData();
              }
            );
          });
        };

        $scope.billPayTag = function (status) {
          if (status == "B") {
            return "label label-success";
          } else {
            return "label label-warning";
          }
        };

        $scope.addRow = function () {
          if (!$scope.main.isMaster) {
            if (this.billpay.status == "B") {
              this.billpay.checked = false;
            }
          }
        };

        $scope.getDataHoje = function(){
          var hoje = new Date();
          var dd = String(hoje.getDate()).padStart(2, '0');
          var mm = String(hoje.getMonth() + 1).padStart(2, '0'); //January is 0!
          var yyyy = hoje.getFullYear();

          return(yyyy + '-' + mm + '-' + dd);
        }

        $scope.filtroHoje = function (){
          var from = new Date();
          from.setHours(0, 0, 1);

          var to = new Date();
          from.setHours(23, 59, 59);

          $scope.filter = {
            dueDateFrom: from,
            _dueDateFrom: $scope.getDataHoje(),
            dueDateTo: to,
            _dueDateTo: $scope.getDataHoje()
          }
          $scope.loadData();
        };

        $scope.search = function () {
          // $scope.filteredBillsPay = $filter('filter')($scope.billspay, $scope.searchKeywords);
          // return $scope.onFilterChange();
          $scope.loadData();
        };

        $scope.order = function (rowName) {
          // if ($scope.row === rowName) {
          //   return;
          // }
          // $scope.row = rowName;
          // $scope.filteredBillsPay = $filter('orderBy')($scope.billspay, rowName);
          // return $scope.onOrderChange();
          $scope.searchOrder = rowName;
          $scope.searchOrderDown = undefined;
          $scope.loadData();
        };

        $scope.orderDown = function (rowName) {
          $scope.searchOrder = undefined;
          $scope.searchOrderDown = rowName;
          $scope.loadData();
        };

        $scope.loadData = function () {
          //O usuario deve fornecer um dado para poder pesquisar
          if( 
              (!$scope.filter.providerName) && (!$scope.filter.payment_id)   &&
              (!$scope.filter.status)       && (!$scope.filter.airline)      &&
              (!$scope.filter.account_type) && (!$scope.filter.payment_type) &&
              (!$scope.filter.dueDateFrom)  && (!$scope.filter.dueDateTo)
            ){
              alert("Insira um valor em algum campo da caixa de pesquisa avançada e redefina sua busca.");
              return ;
          }

          cfpLoadingBar.start();
          $.post(
            "../backend/application/index.php?rota=/loadSumOpenedBillsPayPurchase",
            { data: $scope.filter },
            function (result) {
              $scope.sumOpenedbillspay = jQuery.parseJSON(result).dataset;
            }
          );
          
          //if($scope.filter || $scope.searchOrderDown || $scope.searchOrder){
            $.post(
              "../backend/application/index.php?rota=/loadBillsPayPurchase",
              {
                page: $scope.currentPage,
                numPerPage: $scope.numPerPage,
                searchKeywords: $scope.searchKeywords,
                order: $scope.searchOrder,
                orderDown: $scope.searchOrderDown,
                data: $scope.filter,
              },
              function (result) {
                $scope.billpays = jQuery.parseJSON(result).dataset.billpays;
                $scope.totalData = jQuery.parseJSON(result).dataset.total;
                cfpLoadingBar.complete();
                $scope.$digest();
              }
            );
          /*} else {
            cfpLoadingBar.complete();
          }*/

        };

        $scope.filterproviders = $scope.$parent.providers;

        $scope.saveClosePay = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "BillPayTwo.html",
            controller: "BillPayModalInstanceCtrl",
            periods: $scope.$parent.periods,
            resolve: {
              filter: function () {
                return { dueDateFromPay: new Date() };
              },
            },
          });
          modalInstance.result.then(
            function (filter) {
              if (filter != undefined) {
                filter._dueDateFrom = $rootScope.formatServerDate(
                  filter.dueDateFromPay
                );
                //filter._dueDateTo = $rootScope.formatServerDate(filter.dueDateTo);
              }

              $scope.checkedrows = $filter("filter")($scope.billpays, true);
              $.post(
                "../backend/application/index.php?rota=/saveClosePay",
                {
                  hashId: $scope.session.hashId,
                  checkedrows: $scope.checkedrows,
                  filter: filter,
                },
                function (result) {
                  logger.logSuccess(jQuery.parseJSON(result).message.text);
                  $scope.fillEmail();
                }
              );
            },
            function () {
              $log.info("Modal dismissed at: " + new Date());
            }
          );
        };

        $scope.saveBillPay = function () {
          $scope.checkedrows = $filter("filter")($scope.billspay, true);
          $scope.selected.due_date = $rootScope.formatServerDate(
            $scope.selected._due_date
          );
          $.post(
            "../backend/application/index.php?rota=/saveBillPay",
            { hashId: $scope.session.hashId, data: $scope.selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.back();
            }
          );
        };

        $scope.setActual_Value = function () {
          $scope.selected.actual_value =
            parseFloat($scope.selected.original_value) +
            parseFloat($scope.selected.tax) -
            parseFloat($scope.selected.discount);
        };

        $scope.sendEmail = function () {
          $scope.checkedrows = [];
          $scope.checkedrows.push(this.billpay);
          $scope.fillEmail();
        };

        $scope.fillEmail = function () {
          $scope.email.emailpartner = "";
          $scope.email.mailcc = "";
          $scope.email.subject = "[Aviso de Pagamento] - One Milhas";

          $scope.email.emailContent = "";
          var and = "";
          for (var i in $scope.checkedrows) {
            $scope.email.emailpartner += and + $scope.checkedrows[i].email;
            and = ";";

            if (
              $scope.checkedrows[i].airline == "TAM" ||
              $scope.checkedrows[i].airline == "LATAM"
            ) {
              if (
                $scope.checkedrows[i].card_type == "RED" ||
                $scope.checkedrows[i].card_type == "BLACK"
              ) {
                $scope.email.emailContent +=
                  "Olá!<br><br>" +
                  "Segue em anexo comprovante de pagamento referente à compra de " +
                  $scope.checkedrows[i].purchased_miles +
                  " do programa Latam Pass.<br>" +
                  "Assim que confirmado a disponibilidade do valor em conta, nos responda esse e-mail com a SENHA DE ACESSO e a SENHA DE RESGATE. Entraremos em contato para incluir um telefone nosso eu seu cadastro Latam Pass, para que possamos utilizar os pontos.<br>" +
                  "<br>ATENÇÃO: caso a companhia aérea entre em contato, confirme as emissões e os demais dados solicitados, caso tenha qualquer tipo de problema na confirmação nos comunique de imediato.<br>" +
                  "O prazo de utilização dos pontos é de 180 dias da data de envio da senha. Caso tenha que alterar a senha dentro do prazo de 180 dias, pedimos que nos comunique imediatamente para que possamos atualizar em nosso sistema. Mesmo que tenhamos utilizado todos os pontos.<br><br>" +
                  "Gostou da One Milhas? Faça uma breve avaliação e ajude mais pessoas a venderem pontos!!!" +
                  "<br>Facebook: <a href=''>Clique aqui</a> <br>" +
                  "Instagram: <a href='https://instagram.com/one.milhas?igshid=19fbm6umf8q66'>Clique aqui</a><br>" +
                  "<br><br>Atenciosamente<br>" +
                  "Equipe One Milhas";
              } else {
                $scope.email.emailContent +=
                  "Olá!<br><br>" +
                  "Segue em anexo comprovante de pagamento referente à compra de " +
                  $scope.checkedrows[i].purchased_miles +
                  " do programa Latam Pass.<br>" +
                  "Assim que confirmado a disponibilidade do valor em conta, nos responda esse e-mail com a SENHA DE ACESSO e a SENHA DE RESGATE. Entraremos em contato para incluir um telefone nosso eu seu cadastro Latam Pass, para que possamos utilizar os pontos.<br><br>" +
                  "ATENÇÃO: caso a companhia aérea entre em contato, confirme as emissões e os demais dados solicitados, caso tenha qualquer tipo de problema na confirmação nos comunique de imediato.<br>" +
                  "O prazo de utilização dos pontos é de 180 dias da data de envio da senha. Caso tenha que alterar a senha dentro do prazo de 180 dias, pedimos que nos comunique imediatamente para que possamos atualizar em nosso sistema. Mesmo que tenhamos utilizado todos os pontos.<br><br>" +
                  "Gostou da One Milhas? Faça uma breve avaliação e ajude mais pessoas a venderem pontos!!!" +
                  "<br>Facebook: <a href=''>Clique aqui</a> <br>" +
                  "Instagram: <a href='https://instagram.com/one.milhas?igshid=19fbm6umf8q66'>Clique aqui</a><br>" +
                  "<br><br>Atenciosamente<br>" +
                  "Equipe One Milhas";
              }
            } else if ($scope.checkedrows[i].airline == "GOL") {
              $scope.email.emailContent +=
                "Olá!<br><br>" +
                "Segue em anexo comprovante de pagamento referente à compra de " +
                $scope.checkedrows[i].purchased_miles +
                " pontos do programa SMILES/GOL.<br>" +
                "Assim que confirmado a disponibilidade do valor em conta, nos responda esse e-mail com a SENHA DE ACESSO.<br><br>" +
                "ATENÇÃO: caso a companhia aérea entre em contato, confirme as emissões e os demais dados solicitados, caso tenha qualquer tipo de problema na confirmação nos comunique de imediato. O prazo de utilização dos pontos é de 180 dias da data de envio da senha. Caso tenha que alterar a senha dentro do prazo de 180 dias, pedimos que nos comunique imediatamente para que possamos atualizar em nosso sistema. Mesmo que tenhamos utilizado todos os pontos.<br><br>" +
                "Gostou da One Milhas? Faça uma breve avaliação e ajude mais pessoas a venderem pontos!!!" +
                "<br>Facebook: <a href=''>Clique aqui</a> <br>" +
                "Instagram: <a href='https://instagram.com/one.milhas?igshid=19fbm6umf8q66'>Clique aqui</a><br>" +
                "<br><br>Atenciosamente, <br>" +
                "Equipe One Milhas";
            } else if ($scope.checkedrows[i].airline == "AVIANCA") {
              $scope.email.emailContent +=
                "Olá!<br><br>" +
                "Segue em anexo comprovante de pagamento referente à compra de " +
                $scope.checkedrows[i].purchased_miles +
                " pontos do programa AMIGO/AVIANCA.<br>" +
                "Pedimos, assim que confirmado a disponibilidade do valor em conta, nos responda esse e-mail com a SENHA.<br>" +
                "Pedimos também atenção, caso a CIA  Aérea entre em contato, confirme as emissões e os demais dados solicitados, caso tenha qualquer tipo de problema na confirmação nos comunique de imediato.<br>" +
                "Atenção: O prazo de utilização dos pontos é de 180 dias da data de envio da senha. Caso tenha que alterar a senha dentro do prazo de 180 dias, pedimos que nos comunique imediatamente para que possamos atualizar em nosso sistema. Mesmo que tenhamos utilizado todos os pontos. <br>" +
                "Para nós da One Milhas foi um prazer atendê-lo, estamos à disposição caso tenha alguma dúvida.<br>" +
                "<br><br>Atenciosamente";
            } else if ($scope.checkedrows[i].airline == "AZUL") {
              $scope.email.emailContent +=
                "Olá!<br><br>" +
                "Segue em anexo comprovante de pagamento referente à compra de " +
                $scope.checkedrows[i].purchased_miles +
                " pontos do programa TUDO AZUL.<br>" +
                "Assim que confirmado a disponibilidade do valor em conta, nos responda esse e-mail com a SENHA DE ACESSO.<br><br>" +
                "ATENÇÃO: caso a companhia aérea entre em contato, confirme as emissões e os demais dados solicitados, caso tenha qualquer tipo de problema na confirmação nos comunique de imediato.<br>" +
                "O prazo de utilização dos pontos é de 180 dias da data de envio da senha. Caso tenha que alterar a senha dentro do prazo de 180 dias, pedimos que nos comunique imediatamente para que possamos atualizar em nosso sistema. Mesmo que tenhamos utilizado todos os pontos. <br><br>" +
                "Gostou da One Milhas? Faça uma breve avaliação e ajude mais pessoas a venderem pontos!!!" +
                "<br>Facebook: <a href=''>Clique aqui</a> <br>" +
                "Instagram: <a href='https://instagram.com/one.milhas?igshid=19fbm6umf8q66'>Clique aqui</a><br>" +
                "<br><br>Atenciosamente<br>" +
                "Equipe One Milhas";
            } else if ($scope.checkedrows[i].airline == "TAP") {
              $scope.email.emailContent +=
                "Olá!<br><br>" +
                "Segue em anexo comprovante de pagamento referente à compra de " +
                $scope.checkedrows[i].purchased_miles +
                " pontos do programa TAP.<br>" +
                "Assim que confirmado a disponibilidade do valor em conta, nos responda esse e-mail com a SENHA DE ACESSO.<br><br>" +
                "ATENÇÃO: caso a companhia aérea entre em contato, confirme as emissões e os demais dados solicitados, caso tenha qualquer tipo de problema na confirmação nos comunique de imediato. O prazo de utilização dos pontos é de 180 dias da data de envio da senha. Caso tenha que alterar a senha dentro do prazo de 180 dias, pedimos que nos comunique imediatamente para que possamos atualizar em nosso sistema. Mesmo que tenhamos utilizado todos os pontos.<br><br>" +
                "Gostou da One Milhas? Faça uma breve avaliação e ajude mais pessoas a venderem pontos!!!" +
                "<br>Facebook: <a href=''>Clique aqui</a> <br>" +
                "Instagram: <a href='https://instagram.com/one.milhas?igshid=19fbm6umf8q66'>Clique aqui</a><br>" +
                "<br><br>Atenciosamente<br>" +
                "Equipe One Milhas";
            }
          }

          $scope.tabindex = 2;
          $scope.$apply();
        };

        $scope.mailOrder = function () {
          $scope.back();
          for (var i in $scope.checkedrows) {
            $scope.sendEmailForChecked($scope.email);
          }
        };

        $scope.sendEmailForChecked = function (mail) {
          var file = [];
          for (var j in $scope.uploader.queue) {
            file.push($scope.uploader.queue[j].file.name);
          }
          $.post(
            "../backend/application/index.php?rota=/mailOrder",
            {
              hashId: $scope.session.hashId,
              data: mail,
              attachment: file,
              type: "COMPRAS",
              emailType: 'EMAIL-GMAIL'
            },
            function (result) {
              if (jQuery.parseJSON(result).message.type == "S") {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $scope.uploader.clearQueue();
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }
            }
          );
        };

        $scope.deleteBillsPay = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/modal_justification.html",
            controller: "ModalJustificationCtrl",
            resolve: {
              header: function () {
                return "Digite o motivo da exclusão";
              },
            },
          });

          modalInstance.result.then(function (filter) {
            $scope.selected.deleteDescription = filter;
            $.post(
              "../backend/application/index.php?rota=/deleteBillsPayAndProvider",
              { data: $scope.selected },
              function (result) {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $scope.tabindex = 0;
                $scope.loadData();
              }
            );
          });
        };

        $scope.toCalendar = function () {
          $scope.tabindex = 3;
        };

        $scope.back = function () {
          $scope.tabindex = 0;
          $scope.loadData();
          return $scope.select($scope.currentPage);
        };

        $scope.numPerPageOpt = [10, 30, 50, 100];
        $scope.numPerPage = $scope.numPerPageOpt[2];
        $scope.currentPage = 1;
        $scope.currentPageBillsPay = [];
        $rootScope.hashId = $scope.session.hashId;

        init = function () {
          $scope.tabindex = 0;
          $scope.checkValidRoute();
          //$scope.loadData();
          $scope.filtroHoje();

          $scope.uploader = new FileUploader();
          $scope.uploader.url =
            "../backend/application/index.php?rota=/saveFile";
          $scope.uploader.formData = { hashId: $scope.session.hashId };
          $scope.uploader.autoUpload = true;
          $scope.uploader.filters.push({
            name: "customFilter",
            fn: function (item /*{File|FileLikeObject}*/, options) {
              return this.queue.length < 10;
            },
          });
        };

        $scope.events = [];
        $scope.loadEvents = function () {
          $.post(
            "../backend/application/index.php?rota=/loadEventsBillsPayPurchaseCalendar",
            { month: $scope.currentMonth, year: $scope.currentYear },
            function (data) {
              $scope.UserEvents = JSON.parse(data).dataset;

              for (var i in $scope.events) {
                $scope.events.splice(i, 1);
              }
              for (var i in $scope.UserEvents) {
                $scope.UserEvents[i].start = new Date(
                  $scope.UserEvents[i].start
                );
                $scope.events.push($scope.UserEvents[i]);
              }
              $scope.$digest();
            }
          );
        };

        $scope.saveEvent = function () {
          if ($scope.selected.start != "Invalid Date") {
            $scope.selected._start = $rootScope.formatServerDate(
              $scope.selected.start
            );
          }
          if ($scope.selected.end != "Invalid Date") {
            $scope.selected._end = $rootScope.formatServerDate(
              $scope.selected.end
            );
          }
          $scope.selected.calendar = $scope.selectedCalendar;
          $.post(
            "../backend/application/index.php?rota=/saveEvent",
            { hash: $scope.session.hash, data: $scope.selected },
            function (data) {
              $scope.loadEvents();
            }
          );
        };

        $scope.uiConfig = {
          calendar: {
            lang: "pt-br",
            height: "100%",
            editable: false,
            viewRender: function (view, element) {
              $scope.currentMonth = view.start._d.getMonth() + 2;
              $scope.currentYear = view.start._d.getFullYear();
              $scope.loadEvents();
            },
            header: {
              left: "month agendaWeek agendaDay",
              center: "",
              right: "prev,next today",
            },
            eventClick: function (date, jsEvent, view) {
              $scope.alertEventOnClick(date, jsEvent, view);
            },
            eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
              $scope.alertOnDrop(event, delta, revertFunc, jsEvent, ui, view);
            },
            eventResize: function (
              event,
              delta,
              revertFunc,
              jsEvent,
              ui,
              view
            ) {
              $scope.alertOnResize(event, delta, revertFunc, jsEvent, ui, view);
            },
          },
        };

        $scope.eventRender = function (event, element, view) {
          element.attr({
            tooltip: event.title,
            "tooltip-append-to-body": true,
          });
          $compile(element)($scope);
        };

        $scope.alertOnDrop = function (
          event,
          delta,
          revertFunc,
          jsEvent,
          ui,
          view
        ) {
          event.start = event._start._d;
          if (event.end) {
            event.end = event._end._d;
          }
          $scope.saveEvent(event);
        };

        $scope.alertOnResize = function (
          event,
          delta,
          revertFunc,
          jsEvent,
          ui,
          view
        ) {
          event.start = event._start._d;
          if (event.end) {
            event.end = event._end._d;
          }
          $scope.saveEvent(event);
        };

        $scope.alertEventOnClick = function (date, jsEvent, view) {
          date.start = date._start._d;
          if (date.end) {
            date.end = date._end._d;
          }
          $scope.selected = date;
          $("#modal-event").click();
          // $rootScope.$emit('openCalendarModal', date );
        };

        $scope.eventSource = {
          url:
            "http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic",
          className: "gcal-event",
          currentTimezone: "America/Sao_Paulo",
        };

        $scope.uiConfig.calendar.dayNames = [
          "Domingo",
          "Segunda-Feira",
          "Terça-Feira",
          "Quarta-Feira",
          "Quinta-Feira",
          "Sexta-Feira",
          "Sábado",
        ];
        $scope.uiConfig.calendar.dayNamesShort = [
          "Dom",
          "Seg",
          "Ter",
          "Qua",
          "Qui",
          "Sex",
          "Sab",
        ];

        $scope.eventSources = [$scope.events, $scope.eventSource];

        return init();
      },
    ])
    .controller("BillPayModalDemoCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      function ($scope, $rootScope, $modal, $log) {
        $scope.filterproviders = $scope.$parent.providers;

        $scope.open = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "BillPay.html",
            controller: "BillPayModalInstanceCtrl",
            periods: $scope.$parent.periods,
            resolve: {
              filter: function () {
                if($scope.filter.dueDateFrom){
                  $scope.filter.dueDateFrom = undefined;
                  $scope.filter._dueDateFrom = undefined;
                }
                if($scope.filter.dueDateTo){
                  $scope.filter.dueDateTo = undefined;
                  $scope.filter._dueDateTo = undefined;
                }
                return $scope.filter;
              },
            },
          });
          modalInstance.result.then(
            function (filter) {
              if (filter != undefined) {
                filter._dueDateFrom = $rootScope.formatServerDate(
                  filter.dueDateFrom
                );
                filter._dueDateTo = $rootScope.formatServerDate(
                  filter.dueDateTo
                );
              }

              //console.log(filter);

              $scope.$parent.filter = filter;
              $scope.$parent.loadData();
            },
            function () {
              $log.info("Modal dismissed at: " + new Date());
            }
          );
        };
      },
    ])
    .controller("BillPayModalInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "$timeout",
      "filter",
      function ($scope, $rootScope, $modalInstance, $timeout, filter) {
        $scope.billStatus = ["Em aberto", "Baixada"];
        $scope.accountType = [
          "Taxa DU",
          "Taxa Extra",
          "Taxa Aeroporto",
          "Compra Milhas",
          "Reembolso",
          "Milhas + Money",
          "Contas Comuns",
        ];
        $scope.paymentType = [
          "Cartão de Crédito",
          "Deposito em Conta",
          "Boleto Bancario",
          "Reembolso",
        ];
        $scope.filter = filter;
        $scope.providers = [];
        var load = undefined;

        $scope.loadProvider = function () {
          if (load) {
            $timeout.cancel(load);
          }
          load = $timeout($scope.searchProviders(), 1000);
        };

        $scope.searchProviders = function () {
          $.post(
            "../backend/application/index.php?rota=/loadProvider",
            { searchKeywords: $scope.filter.providerName },
            function (result) {
              $scope.providers = jQuery.parseJSON(result).dataset.providers;
              if (load) {
                load = undefined;
              }
              $scope.$digest();
            }
          );
        };

        $.post(
          "../backend/application/index.php?rota=/loadAirline",
          { hashId: $scope.$parent.hashId },
          function (result) {
            $scope.airlines = jQuery.parseJSON(result).dataset;
          }
        );

        $scope.ok = function () {
          $modalInstance.close($scope.filter);
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ]);
})();
