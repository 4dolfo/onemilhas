(function () {
  "use strict";
  angular
    .module("app")
    .controller("WizardBilletCtrl", [
      "$scope",
      "$rootScope",
      "$filter",
      "cfpLoadingBar",
      "logger",
      "FileUploader",
      "$window",
      '$interval',
      function (
        $scope,
        $rootScope,
        $filter,
        cfpLoadingBar,
        logger,
        FileUploader,
        $window,
        $interval
      ) {
        var init;
        $scope.periods = [
          "Ultimos 30 dias",
          "Mes Corrente",
          "Semana Corrente",
          "Hoje",
        ];
        $scope.banks = ["BRADESCO", "SANTANDER"];
        $scope.searchKeywords = "";
        $scope.filteredBillsReceive = [];
        $scope.row = "";
        $scope.ocultar_bloqueados = false;
        $scope.ocultar_bloqueados_disabled = false;
        $scope.botoes_processando = false;
        $scope.botao = "b0";
        $scope.days = 1;
        $scope.enterPressed = false;

        $scope.onDaysEnterKey = function(keyCode) {
            $scope.enterPressed = false;
            if(keyCode == 13){
              $scope.enterPressed = true;
              $scope.findYesterday();
            }
        };
  
        $scope.onDaysBlur = function() {
            if(!$scope.enterPressed){
              $scope.findYesterday();
            }
            $scope.enterPressed = false;
        };

        $scope.select = function (page) {
          var end, start;
          start = (page - 1) * $scope.numPerPage;
          end = start + $scope.numPerPage;
          return ($scope.currentPageBillsReceive = $scope.filteredBillsReceive.slice(
            start,
            end
          ));
        };

        $scope.getStatusDesc = function (status) {
          switch (status) {
            case "B":
              return "Baixada";
            case "E":
              return "Emitida";
            case "A":
              return "Em Aberto";
            case "T":
              return "Transferencia";
          }
        };

        $scope.redirectToGoogle = function (link) {
          $window.open(link, "_blank");
        };

        $scope.findColor = function (billreceive) {
          if (billreceive.paymentType == "Boleto") {
            return "#98d1f5";
          }
        };

        $scope.billReceiveTag = function (status) {
          switch (status) {
            case "B":
              return "label label-success";
            case "E":
              return "label label-info";
            case "A":
              return "label label-warning";
            case "T":
              return "label label-default";
          }
        };

        $scope.onFilterChange = function () {
          $scope.select(1);
          $scope.currentPage = 1;
          return ($scope.row = "");
        };

        $scope.onNumPerPageChange = function () {
          $scope.select(1);
          return ($scope.currentPage = 1);
        };

        $scope.onOrderChange = function () {
          $scope.select(1);
          return ($scope.currentPage = 1);
        };

        $scope.setSelected = function () {
          $scope.selected = this.billreceive;
          $scope.nextWizardStage();
          return $scope.selected;
        };

        $scope.search = function () {
          $scope.filteredBillsReceive = $filter("filter")(
            $scope.billsreceive,
            $scope.searchKeywords
          );
          return $scope.onFilterChange();
        };

        $scope.order = function (rowName) {
          if ($scope.row === rowName) {
            return;
          }
          $scope.row = rowName;
          $scope.filteredBillsReceive = $filter("orderBy")(
            $scope.billsreceive,
            rowName
          );
          return $scope.onOrderChange();
        };

        $scope.nextWizardStage = function () {
          $scope.tabindex = $scope.tabindex + 1;
          return $scope.tabindex;
        };

        $scope.priorWizardStage = function () {
          $scope.tabindex = $scope.tabindex - 1;
          return $scope.tabindex;
        };

        $scope.WizardStageProgress = function () {
          return ($scope.tabindex + 1) * 34;
        };

        $scope.addRow = function () {
          if (this.billreceive.status == "B") {
            this.billreceive.checked = false;
          }
        };

        $scope.loadBills = function () {
          if ($scope.wbillet_client.$valid) {
            $scope.loadBillsReceive();
            $scope.nextWizardStage();
          }
        };

        $scope.getSumBills = function () {
          $scope.possibleCloseBillets = false;
          $scope.currentBillet = {};
          if ($scope.client) {
            $scope.uploader.formData = { hashId: $scope.session.hashId };
            var i = 0;
            $scope.checkedrows = $filter("filter")($scope.billsreceive, true);
            $scope.wbillet = angular.copy($scope.checkedrows[0]);
            $scope.wbillet.description = "";

            $scope.wbillet.alreadyPaid = 0;
            $scope.wbillet.actual_value = 0;
            $scope.wbillet.discount = 0;

            if ($scope.client.paymentType == "Antecipado") {
              for (var i in $scope.checkedrows) {
                if (
                  $scope.checkedrows[i].account_type == "Reembolso" ||
                  $scope.checkedrows[i].account_type == "Credito" ||
                  $scope.checkedrows[i].account_type == "Credito Adiantamento"
                ) {
                  $scope.wbillet.actual_value =
                    $scope.wbillet.actual_value -
                    $scope.checkedrows[i].actual_value;
                  if ($scope.checkedrows[i].paymentType == "Comum") {
                    $scope.wbillet.alreadyPaid =
                      $scope.wbillet.alreadyPaid -
                      $scope.checkedrows[i].actual_value;
                  }
                } else {
                  if ($scope.checkedrows[i].alreadBilled == 0) {
                    $scope.wbillet.actual_value =
                      $scope.wbillet.actual_value +
                      $scope.checkedrows[i].actual_value;
                    if ($scope.checkedrows[i].paymentType == "Comum") {
                      $scope.wbillet.alreadyPaid =
                        $scope.wbillet.alreadyPaid +
                        $scope.checkedrows[i].actual_value;
                    }
                  }
                }
              }
            } else {
              for (var i in $scope.checkedrows) {
                if (
                  $scope.checkedrows[i].account_type == "Reembolso" ||
                  $scope.checkedrows[i].account_type == "Credito" ||
                  $scope.checkedrows[i].account_type == "Credito Adiantamento"
                ) {
                  $scope.wbillet.actual_value =
                    $scope.wbillet.actual_value -
                    $scope.checkedrows[i].actual_value;
                } else {
                  if ($scope.checkedrows[i].alreadBilled == 0) {
                    $scope.wbillet.actual_value =
                      $scope.wbillet.actual_value +
                      $scope.checkedrows[i].actual_value;
                    if ($scope.client.useCommission == true) {
                      $scope.wbillet.actual_value -=
                        $scope.checkedrows[i].comission;
                      $scope.wbillet.discount +=
                        $scope.checkedrows[i].comission;
                    }
                  }
                }
              }
            }
            $scope.wbillet.valueFloat = $scope.wbillet.actual_value;

            // feature to debit credit from opened bills
            if ($scope.wbillet.actual_value < 0) {
              $scope.loadOpenedBillets();
            }

            $scope.wbillet.actual_value = $rootScope.formatNumber(
              $scope.wbillet.actual_value
            );
            $.post(
              "../backend/application/index.php?rota=/loadLastBillet",
              $scope.session,
              function (result) {
                $scope.lastBillet = jQuery.parseJSON(result).dataset;
                if (
                  $scope.wbillet.doc_number === undefined &&
                  $scope.wbillet.our_number === undefined
                ) {
                  $scope.wbillet.doc_number =
                    parseInt($scope.lastBillet.id) + 1;
                  $scope.wbillet.our_number =
                    parseInt($scope.lastBillet.id) + 1;
                }
                $scope.$apply();
              }
            );
            // $scope.wbillet.actual_value = $scope.wbillet.original_value;
            $scope.nextWizardStage();
            $scope.wbillet.transfer = false;
            $("#wbilletdiscount").number(true, 2, ",", ".");
            $("#wbillettax").number(true, 2, ",", ".");
            $("#wbilletoriginal_value").number(true, 2, ",", ".");
            // $('#wbilletactual_value').number(true, 2,',','.');
            $("#wbilletactual_value").maskMoney({
              thousands: ".",
              decimal: ",",
              precision: 2,
            });
            $scope.wbillet.due_date = new Date(
              $scope.wbillet.due_date + "T12:00:00Z"
            );

            var lastBank = window.localStorage.getItem("last-bank");
            if (lastBank) {
              $scope.wbillet.bank = lastBank;
            } else {
              $scope.wbillet.bank = "BRADESCO";
            }

            if (
              $scope.client.paymentType == "Antecipado" &&
              !$scope.wbillet.early
            ) {
              $scope.wbillet.early = true;
              $scope.wbillet.hasBillet = false;
            } else {
              $scope.wbillet.early = false;
              $scope.wbillet.hasBillet = true;
            }
            if ($scope.client.paymentType == "Antecipado") {
              for (var i in $scope.checkedrows) {
                if ($scope.checkedrows[i].paymentType == "Boleto") {
                  $scope.wbillet.early = false;
                  $scope.wbillet.hasBillet = true;
                }
              }
            }
            if ($scope.client.workingDays) {
              var paymentDays = 0;
              if ($scope.client.paymentDays > 7) {
                paymentDays =
                  parseInt($scope.client.paymentDays / 5) * 2 +
                  $scope.client.paymentDays;
                $scope.wbillet.due_date = new Date();
                $scope.wbillet.due_date.setDate(
                  $scope.wbillet.due_date.getDate() + paymentDays
                );
              } else {
                paymentDays = $scope.client.paymentDays;
                $scope.wbillet.due_date = new Date();
                if ($scope.wbillet.due_date.getDay() == 0) {
                  $scope.wbillet.due_date.setDate(
                    $scope.wbillet.due_date.getDate() + 1
                  );
                  paymentDays--;
                }
                if ($scope.wbillet.due_date.getDay() == 6) {
                  $scope.wbillet.due_date.setDate(
                    $scope.wbillet.due_date.getDate() + 2
                  );
                  paymentDays--;
                }
                for (var j = 0; j < paymentDays; j++) {
                  if ($scope.wbillet.due_date.getDay() == 0) {
                    $scope.wbillet.due_date.setDate(
                      $scope.wbillet.due_date.getDate() + 1
                    );
                  }
                  if ($scope.wbillet.due_date.getDay() == 6) {
                    $scope.wbillet.due_date.setDate(
                      $scope.wbillet.due_date.getDate() + 2
                    );
                  }
                  $scope.wbillet.due_date.setDate(
                    $scope.wbillet.due_date.getDate() + 1
                  );
                }
              }
            } else {
              $scope.wbillet.due_date = new Date();
              $scope.wbillet.due_date.setDate(
                $scope.wbillet.due_date.getDate() + $scope.client.paymentDays
              );
            }
            if ($scope.wbillet.due_date.getDay() == 6) {
              $scope.wbillet.due_date.setDate(
                $scope.wbillet.due_date.getDate() + 2
              );
            }
            if ($scope.wbillet.due_date.getDay() == 0) {
              $scope.wbillet.due_date.setDate(
                $scope.wbillet.due_date.getDate() + 1
              );
            }
          }
        };

        $scope.loadOpenedBillets = function () {
          $.post(
            "../backend/application/index.php?rota=/loadOpenedBillets",
            { data: $scope.client },
            function (result) {
              $scope.openedBillets = jQuery.parseJSON(result).dataset;
              if ($scope.openedBillets.length > 0) {
                $scope.possibleCloseBillets = true;
                $scope.$digest();
              }
            }
          );
        };

        $scope.setBilletToClose = function (billet) {
          $scope.currentBillet = billet;
        };

        $scope.openModalCloseBillets = function () {
          $rootScope.$emit(
            "openCloseBillesWtihCreditModal",
            $scope.openedBillets
          );
        };

        $scope.setActual_Value = function () {
          $scope.wbillet.actual_value =
            parseFloat($scope.wbillet.original_value) +
            parseFloat($scope.wbillet.tax) -
            parseFloat($scope.wbillet.discount);
        };

        $scope.findBillets = function () {
          $scope.billsreceive = [];
          $.post(
            "../backend/application/index.php?rota=/loadBillGenerated",
            { hashId: $scope.session.hashId, data: $scope.client },
            function (result) {
              $scope.billsgenerated = jQuery.parseJSON(result).dataset;

              for (var i in $scope.billsgenerated) {
                $scope.fillBillets($scope.billsgenerated[i]);
              }
              $scope.billetGenerated = true;
              $scope.search();
              $scope.$apply();
            }
          );
        };

        $scope.fillBillets = function (bill) {
          var alreadFill = false;
          bill.pax_name = "";
          bill.flightLocator = "";
          for (var j in $scope.billsreceive) {
            if ($scope.billsreceive[j].billet === bill.billet) {
              alreadFill = true;
              if (
                bill.account_type == "Reembolso" ||
                bill.account_type == "Credito" ||
                bill.account_type == "Credito Adiantamento"
              ) {
                $scope.billsreceive[j].actual_value -= bill.actual_value;
              } else {
                if ($scope.billsreceive[j].alreadBilled == 0) {
                  $scope.billsreceive[j].actual_value += bill.actual_value;
                }
              }
            }
          }
          if (!alreadFill) {
            if (
              bill.account_type == "Reembolso" ||
              bill.account_type == "Credito" ||
              bill.account_type == "Credito Adiantamento"
            ) {
              bill.actual_value = bill.actual_value * -1;
            }
            bill.account_type = "BORDERO";
            $scope.billsreceive.push(bill);
          }
        };

        $scope.addDivision = function () {
          $scope.billetsDivision.push({
            actualValue: 0,
            dueDate: $scope.wbillet.due_date,
            name: $scope.wbillet.our_number,
          });
        };

        $scope.removeDivision = function () {
          $scope.billetsDivision.pop();
        };

        $scope.getSumBillsReceive = function () {
          var value = 0;
          if ($scope.billsreceive) {
            if ($scope.billsreceive.length > 0) {
              $scope.checkedrows = $filter("filter")($scope.billsreceive, true);
              for (var i in $scope.checkedrows) {
                if ($scope.checkedrows[i].alreadBilled == 0) {
                  if (
                    $scope.checkedrows[i].account_type == "Reembolso" ||
                    $scope.checkedrows[i].account_type == "Credito" ||
                    $scope.checkedrows[i].account_type == "Credito Adiantamento"
                  ) {
                    value -= $scope.checkedrows[i].actual_value;
                  } else {
                    value += $scope.checkedrows[i].actual_value;
                  }
                }
              }
            }
          }
          return $rootScope.formatNumber(value);
        };

        $scope.markAll = function () {
          for (var i in $scope.billsreceive) {
            $scope.billsreceive[i].checked = true;
          }
        };

        $scope.markYesterday = function () {
          var dias = 1;
          if(new Date().getDay() == 1 ){
            dias = 3;
          }

          var yesterday1 = new Date(new Date().setDate(new Date().getDate()-dias));
          var yesterday2 = new Date(new Date().setDate(new Date().getDate()-1));
          yesterday1.setHours(0,0,1,0);
          yesterday2.setHours(23,59,59,999);

          for(var i in $scope.billsreceive) {
            $scope.billsreceive[i].checked = false;
            var venda = new Date($scope.billsreceive[i].issuing_date.replaceAll("-", "/"));
            venda.setHours(3,0,0,0);
            if(venda >= yesterday1 && venda <= yesterday2){
              $scope.billsreceive[i].checked = true;
            }
          }
        };

        $scope.unMarkAll = function () {
          for (var i in $scope.billsreceive) {
            $scope.billsreceive[i].checked = false;
          }
        };

        $scope.loadBillsReceive = function () {
          cfpLoadingBar.start();
          $.post(
            "../backend/application/index.php?rota=/loadBillsReceive",
            { data: $scope.filter },
            function (result) {
              $scope.billsreceive = jQuery.parseJSON(result).dataset;
              $scope.search();
              cfpLoadingBar.complete();
              $.post(
                "../backend/application/index.php?rota=/loadClientsByFilter",
                { data: $scope.filter },
                function (result) {
                  $scope.client = jQuery.parseJSON(result).dataset;
                  $scope.client = $scope.client[0];
                  $scope.$digest();
                }
              );
              return $scope.select($scope.currentPage);
            }
          );
        };

        $scope.getDate = function (days) {
          var date = new Date();
          date.setDate(date.getDate() + days);
          if (date.getDay() == 6) {
            date.setDate(date.getDate() + 2);
          }
          if (date.getDay() == 0) {
            date.setDate(date.getDate() + 1);
          }
          return date;
        };

        $scope.saveBillet = function () {
          $scope.wizardBillet = true;
          cfpLoadingBar.start();
          if ($scope.wbillet.bank == "SANTANDER") {
            $scope.saveEmail = true;
            $scope.billet.sendDate = new Date();
            $scope.billet.sendDate.setDate(
              $scope.billet.sendDate.getDate() + 1
            );
          }
          window.localStorage.setItem("last-bank", $scope.wbillet.bank);
          for (var i in $scope.billreceive) {
            $scope.billreceive.due_date = $scope.wbillet.due_date;
          }
          for (var j in $scope.billetsDivision) {
            $scope.billetsDivision[j]._dueDate = $rootScope.formatServerDate(
              $scope.billetsDivision[j].dueDate
            );
          }
          $scope.wbillet.actual_value = $("#wbilletactual_value").maskMoney(
            "unmasked"
          )[0];
          $scope.wbillet.due_date = $rootScope.formatServerDate(
            $scope.wbillet.due_date
          );

          if ($scope.wbillet.billingPartner) {
            $scope.client = $filter("filter")(
              $scope.clients,
              $scope.wbillet.billingPartner
            );
            if ($scope.client.length == 1) {
              $scope.client = $scope.client[0];
            } else {
              if ($scope.client.length > 1) {
                for (var i in $scope.client) {
                  if (($scope.client[i].name = $scope.wbillet.billingPartner)) {
                    $scope.client = $scope.client[i];
                    break;
                  }
                }
              }
            }
          }

          if ($scope.billetGenerated) {
            $.post(
              "../backend/application/index.php?rota=/loadBillsBillets",
              {
                hashId: $scope.session.hashId,
                checkedrows: $scope.checkedrows,
              },
              function (result) {
                $scope.billsreceive = jQuery.parseJSON(result).dataset;
                $.post(
                  "../backend/application/index.php?rota=/saveBillet",
                  {
                    hashId: $scope.session.hashId,
                    checkedrows: $scope.billsreceive,
                    wbillet: $scope.wbillet,
                    billetsDivision: $scope.billetsDivision,
                    billetCredit: $scope.currentBillet,
                  },
                  function (result) {
                    logger.logSuccess(jQuery.parseJSON(result).message.text);
                    cfpLoadingBar.complete();
                    $scope.fillEmailContent();
                  }
                );
              }
            );
          } else {
            $.post(
              "../backend/application/index.php?rota=/saveBillet",
              {
                hashId: $scope.session.hashId,
                checkedrows: $scope.checkedrows,
                wbillet: $scope.wbillet,
                billetsDivision: $scope.billetsDivision,
                billetCredit: $scope.currentBillet,
              },
              function (result) {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                cfpLoadingBar.complete();
                $scope.fillEmailContent();
              }
            );
          }
          $scope.carrega_botao();
        };

        $scope.saveEmailBillet = function () {
          var file = [];
          for (var j in $scope.uploader.queue) {
            file.push($scope.uploader.queue[j].file.name);
          }
          $scope.billet._sendDate = $rootScope.formatServerDate(
            $scope.billet.sendDate
          );
          $.post(
            "../backend/application/index.php?rota=/saveEmail",
            {
              hashId: $scope.session.hashId,
              data: $scope.billet,
              attachment: file,
              type: "FINANCEIRO",
              client: $scope.wbillet,
            },
            function (result) {
              if (jQuery.parseJSON(result).message.type == "S") {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $window.location.href = "#/billsReceive/billet";
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }
            }
          );
        };

        $scope.fillEmailModel2 = function () {
          $scope.billet.emailContent =
            "<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'>" +
            "<tbody><tr bgcolor='#5D7B9D'><td colspan='11' bgcolor='#5D7B9D'><font color='#ffffff'><b>Dados</b></font></td></tr>" +
            "<tr><td>Data</td><td>Passageiro</td><td>Localizador</td><td>Trecho</td><td>CIA</td><td>Valor</td><td>Milhas</td><td>Emissor</td><td>Cliente</td><td>Obs</td>";
          if ($scope.billetGenerated) {
            $scope.billet.emailContent += "<td>Bordero</td>";
          }
          $scope.billet.emailContent += "</tr>";
          $scope.checkedrows = $filter("filter")($scope.billsreceive, true);
          for (var i = 0; $scope.checkedrows.length > i; i++) {
            $scope.billet.emailContent +=
              "<tr><td>" +
              $filter("date")(
                $scope.checkedrows[i].issuing_date,
                "dd/MM/yyyy"
              ) +
              "</td><td>";
            if (
              $scope.checkedrows[i].account_type == "Reembolso" ||
              $scope.checkedrows[i].account_type == "Credito" ||
              $scope.checkedrows[i].account_type == "Credito Adiantamento"
            ) {
              $scope.billet.emailContent +=
                $scope.checkedrows[i].description + "</td><td>";
            } else {
              $scope.billet.emailContent +=
                $scope.checkedrows[i].pax_name + "</td><td>";
            }
            $scope.billet.emailContent +=
              $scope.checkedrows[i].flightLocator +
              "</td><td>" +
              $scope.checkedrows[i].from +
              "-" +
              $scope.checkedrows[i].to +
              "</td><td>" +
              $scope.checkedrows[i].airline +
              "</td>";

            if (
              $scope.checkedrows[i].account_type == "Reembolso" ||
              $scope.checkedrows[i].account_type == "Credito" ||
              $scope.checkedrows[i].account_type == "Credito Adiantamento"
            ) {
              $scope.billet.emailContent +=
                "<td><font color='red'>" +
                $rootScope.formatNumber(-$scope.checkedrows[i].actual_value) +
                "</font></td><td>" +
                $scope.checkedrows[i].miles +
                "</td>";
            } else {
              $scope.billet.emailContent +=
                "<td>" +
                $rootScope.formatNumber($scope.checkedrows[i].actual_value) +
                "</td><td>" +
                $scope.checkedrows[i].miles +
                "</td>";
            }

            $scope.billet.emailContent +=
              "<td>" +
              $scope.checkedrows[i].issuing +
              "</td><td>" +
              $scope.checkedrows[i].client +
              "</td><td>" +
              $scope.checkedrows[i].SaleDescription +
              "</td>";
            if ($scope.billetGenerated) {
              if ($scope.checkedrows[i].status == "E") {
                $scope.billet.emailContent +=
                  "<td>" + $scope.checkedrows[i].billet + "<td>";
              } else {
                $scope.billet.emailContent += "<td><td>";
              }
            }
            $scope.billet.emailContent += "</tr>";
          }
          $scope.billet.emailContent +=
            "<tr><td></td><td></td><td></td><td></td><td><b>Total:</b></td><td><b>" +
            $rootScope.formatNumber($scope.wbillet.actual_value) +
            "</b></td><td></td><td></td><td></td><td></td></tr>";

          var due_date =
            "</tbody></table><br><br><b>Vencimento: " +
            $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy") +
            " </b><br><br><b>Numero boleto: " +
            $scope.wbillet.our_number +
            "</b>";
          if ($scope.billetsDivision.length > 0) {
            due_date = "";
            for (var j in $scope.billetsDivision) {
              due_date =
                due_date +
                "</tbody></table><br><br><b>Vencimento: " +
                $filter("date")(
                  $scope.billetsDivision[j].dueDate,
                  "dd/MM/yyyy"
                ) +
                " </b><br><br><b>Numero boleto: " +
                $scope.billetsDivision[j].name +
                "</b>";
            }
          }

          $scope.billet.subject =
            "BOLETO - ONE MILHAS - VENCIMENTO " +
            $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy") +
            "  - " +
            $scope.wbillet.our_number;

          if ($scope.wbillet.early) {
            $scope.billet.emailContent =
              "<br>Prezados,<br><br>Seguem emissões que já estão pagas.<br><br>Obrigado pela parceria.<br>" +
              $scope.billet.emailContent;
            $scope.billet.subject =
              "BORDERO DE EMISSÃO ONE MILHAS  - " + $scope.wbillet.our_number;
            due_date =
              "</tbody></table><br><br><b>Numero Borderô: " +
              $scope.wbillet.our_number +
              "</b>";
          } else {
            $scope.billet.emailContent =
              "<br>Prezado(a), seguem emissões. Obrigado!" +
              $scope.billet.emailContent;
            $scope.billet.subject =
              "BOLETO - ONE MILHAS - VENCIMENTO " +
              $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy") +
              "  - " +
              $scope.wbillet.our_number;
            due_date =
              "</tbody></table><br><br><b>Numero Borderô: " +
              $scope.wbillet.our_number +
              "</b>";
          }

          if ($scope.wbillet.actual_value < 0) {
            $scope.billet.subject =
              "BORDERO DE REEMBOLSO ONE MILHAS - " + $scope.wbillet.our_number;
            due_date =
              "</tbody></table><br><br><b>Numero Borderô: " +
              $scope.wbillet.our_number +
              "</b>";
          }

          if ($scope.wbillet.discount > 0) {
            due_date +=
              "<br><br><b>Descontos: " +
              $rootScope.formatNumber($scope.wbillet.discount) +
              "</b>";
          }

          $scope.billet.emailContent += due_date;
          $scope.$apply();
        };

        $scope.fillEmailContent = function () {
          if (isNaN($scope.wbillet.actual_value)) {
            $scope.wbillet.actual_value = $("#wbilletactual_value").maskMoney(
              "unmasked"
            )[0];
          }
          if ($scope.client.finnancialEmail) {
            if (
              $scope.client.finnancialEmail != undefined &&
              $scope.client.finnancialEmail != null &&
              $scope.client.finnancialEmail != ""
            ) {
              $scope.billet.emailpartner = $scope.client.finnancialEmail;
            } else {
              $scope.billet.emailpartner = $scope.client.email;
            }
          } else {
            $scope.billet.emailpartner = $scope.client.email;
          }
          $scope.billet.mailcc = "";
          $scope.billet.client = $scope.client.name;

          $scope.billet.emailContent =
            "<br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'>" +
            "<tbody><tr bgcolor='#5D7B9D'><td colspan='10' bgcolor='#5D7B9D'><font color='#ffffff'><b>Dados</b></font></td></tr>" +
            "<tr><td>Data</td><td>Passageiro</td><td>Localizador</td><td>Trecho</td><td>CIA</td><td>Valor</td><td>Já Pago</td><td>Milhas</td><td>Emissor</td><td>Cliente</td>";

          $scope.billet.emailContent += "</tr>";
          $scope.checkedrows = $filter("filter")($scope.billsreceive, true);
          for (var i = 0; $scope.checkedrows.length > i; i++) {
            $scope.billet.emailContent +=
              "<tr><td>" +
              $filter("date")(
                $scope.checkedrows[i].issuing_date,
                "dd/MM/yyyy"
              ) +
              "</td><td>";
            if (
              $scope.checkedrows[i].account_type == "Reembolso" ||
              $scope.checkedrows[i].account_type == "Credito" ||
              $scope.checkedrows[i].account_type == "Credito Adiantamento"
            ) {
              $scope.billet.emailContent +=
                $scope.checkedrows[i].description + "</td><td>";
            } else {
              $scope.billet.emailContent +=
                $scope.checkedrows[i].pax_name + "</td><td>";
            }
            $scope.billet.emailContent +=
              $scope.checkedrows[i].flightLocator +
              "</td><td>" +
              $scope.checkedrows[i].from +
              "-" +
              $scope.checkedrows[i].to +
              "</td><td>" +
              $scope.checkedrows[i].airline +
              "</td>";

            if (
              $scope.checkedrows[i].account_type == "Reembolso" ||
              $scope.checkedrows[i].account_type == "Credito" ||
              $scope.checkedrows[i].account_type == "Credito Adiantamento"
            ) {
              $scope.billet.emailContent +=
                "<td><font color='red'>" +
                $rootScope.formatNumber(-$scope.checkedrows[i].actual_value) +
                "</font></td><td>" +
                $rootScope.formatNumber($scope.checkedrows[i].alreadBilled) +
                "</td><td>" +
                $scope.checkedrows[i].miles +
                "</td>";
            } else {
              $scope.billet.emailContent +=
                "<td>" +
                $rootScope.formatNumber($scope.checkedrows[i].actual_value) +
                "</td><td>" +
                $rootScope.formatNumber($scope.checkedrows[i].alreadBilled) +
                "</td><td>" +
                $scope.checkedrows[i].miles +
                "</td>";
            }

            $scope.billet.emailContent +=
              "<td>" +
              $scope.checkedrows[i].issuing +
              "</td><td>" +
              $scope.checkedrows[i].client +
              "</td>";

            $scope.billet.emailContent += "</tr>";
          }
          $scope.billet.emailContent +=
            "<tr><td></td><td></td><td></td><td></td><td><b>Total:</b></td><td><b>" +
            $rootScope.formatNumber($scope.wbillet.actual_value) +
            "</b></td><td></td><td></td><td></td><td></td></tr>";

          var due_date =
            "</tbody></table><br><br><b>Vencimento: " +
            $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy") +
            " </b><br><br><b>Numero boleto: " +
            $scope.wbillet.our_number +
            "</b>";
          if ($scope.billetsDivision.length > 0) {
            due_date = "</tbody></table><br><br>";
            for (var j in $scope.billetsDivision) {
              due_date =
                due_date +
                "<br><br><b>Vencimento: " +
                $filter("date")(
                  $scope.billetsDivision[j].dueDate,
                  "dd/MM/yyyy"
                ) +
                " </b><br><b>Valor: " +
                $rootScope.formatNumber($scope.billetsDivision[j].actualValue) +
                "</b><br><b>Numero boleto: " +
                $scope.billetsDivision[j].name +
                "</b>";
            }
          }

          $scope.billet.subject =
            "BOLETO - ONE MILHAS - VENCIMENTO " +
            $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy") +
            "  - " +
            $scope.wbillet.our_number;

          if ($scope.wbillet.early) {
            $scope.billet.emailContent =
              "<br>Prezados,<br><br>Seguem emissões que já estão pagas.<br><br>Obrigado pela parceria.<br>" +
              $scope.billet.emailContent;

            $scope.billet.subject =
              "BORDERO DE EMISSÃO ONE MILHAS - " + $scope.wbillet.our_number;
            due_date =
              "</tbody></table><br><br><b>Numero Borderô: " +
              $scope.wbillet.our_number +
              "</b>";
          } else {
            $scope.billet.subject =
              "BOLETO - ONE MILHAS - VENCIMENTO " +
              $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy") +
              "  - " +
              $scope.wbillet.our_number;
            $scope.billet.emailContent =
              "<br>Prezado(a), seguem emissões. Obrigado!" +
              $scope.billet.emailContent;
            due_date =
              "</tbody></table><br><br><b>Vencimento: " +
              $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy") +
              " </b><br><br><b>Numero Borderô: " +
              $scope.wbillet.our_number +
              "</b>";
          }

          if ($scope.wbillet.actual_value < 0) {
            $scope.billet.subject =
              "BORDERO DE REEMBOLSO ONE MILHAS - " + $scope.wbillet.our_number;
            due_date =
              "</tbody></table><br><br><b>Numero Borderô: " +
              $scope.wbillet.our_number +
              "</b>";
          }

          if ($scope.wbillet.alreadyPaid > 0) {
            due_date =
              due_date +
              "<br><br><b>Já Pago: " +
              $rootScope.formatNumber($scope.wbillet.alreadyPaid) +
              "</b>";
            if ($scope.wbillet.actual_value - $scope.wbillet.alreadyPaid > 0) {
              due_date =
                due_date +
                "<br><b>Restando: R$ " +
                $rootScope.formatNumber(
                  $scope.wbillet.actual_value - $scope.wbillet.alreadyPaid
                ) +
                "</b>";
            }
          }

          if (
            $scope.client.billingPeriod != "Diario" &&
            $scope.client.billingPeriod != "" &&
            $scope.client.billingPeriod &&
            !$scope.billetGenerated
          ) {
            $scope.billet.subject =
              "BORDERO DE EMISSÃO ONE MILHAS - " + $scope.wbillet.our_number;
            due_date =
              "</tbody></table><br><br><b>Numero Borderô: " +
              $scope.wbillet.our_number +
              "</b>";
          }

          if ($scope.wbillet.discount > 0) {
            due_date +=
              "<br><br><b>Descontos: " +
              $rootScope.formatNumber($scope.wbillet.discount) +
              "</b>";
          }

          $scope.billet.emailContent += due_date;

          $scope.tabindex = 3;
          $scope.$apply();
        };

        $scope.mailOrder = function () {
          var file = [];
          for (var j in $scope.uploader.queue) {
            file.push($scope.uploader.queue[j].file.name);
          }
          // file = $scope.uploader.queue[$scope.uploader.queue.length -1].file.name;

          var raw_content = $scope.print(false);

          $.post(
            "../backend/application/index.php?rota=/mailOrder",
            {
              data: $scope.billet,
              attachment: file,
              type: "FINANCEIRO",
              client: $scope.client,
              raw_content: raw_content,
              emailType: 'EMAIL-GMAIL'
            },
            function (result) {
              if (jQuery.parseJSON(result).message.type == "S") {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $window.location.href = "#/billsReceive/billet";
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }
            }
          );
        };

        $scope.setClient = function () {
          $scope.filter.clientName = this.client.name;
          $scope.loadBills();
        };

        $scope.printBills = function () {
          var doc = new jsPDF("l", "pt");
          doc.margin = 0.5;
          doc.setFontSize(18);
          $scope.reportBillsReceive = $filter("filter")(
            $scope.billsreceive,
            true
          );
          doc.text(
            150,
            30,
            "Resumo Debitos - " + $scope.reportBillsReceive[0].client
          );

          var columns = [
            { title: "Data Venda", dataKey: "issuing_date" },
            { title: "Tipo Conta", dataKey: "account_type" },
            { title: "PAX", dataKey: "pax" },
            { title: "Localizador", dataKey: "flightLocator" },
            { title: "Valor", dataKey: "value" },
            { title: "Já pago", dataKey: "alreadBilled" },
            { title: "Taxa", dataKey: "tax" },
            { title: "Milhas", dataKey: "miles_tax" },
            { title: "due_date", dataKey: "due_date" },
          ];

          var rows = [];
          var total = 0;

          for (var i = 0; i < $scope.reportBillsReceive.length; i++) {
            if (
              $scope.checkedrows[i].account_type == "Reembolso" ||
              $scope.checkedrows[i].account_type == "Credito" ||
              $scope.checkedrows[i].account_type == "Credito Adiantamento"
            ) {
              total -= $scope.reportBillsReceive[i].actual_value;

              rows.push({
                issuing_date: $filter("date")(
                  $scope.reportBillsReceive[i].issuing_date,
                  "dd/MM/yyyy HH:mm:ss"
                ),
                account_type: $scope.reportBillsReceive[i].account_type,
                pax: $scope.reportBillsReceive[i].description,
                flightLocator: $scope.reportBillsReceive[i].flightLocator,
                value: $rootScope.formatNumber(
                  $scope.reportBillsReceive[i].actual_value
                ),
                alreadBilled: $rootScope.formatNumber(
                  $scope.reportBillsReceive[i].alreadBilled
                ),
                tax: $scope.reportBillsReceive[i].tax,
                miles_tax: $scope.reportBillsReceive[i].miles_tax,
                due_date: $filter("date")(
                  $scope.reportBillsReceive[i].due_date,
                  "dd/MM/yyyy"
                ),
              });
            } else {
              if ($scope.reportBillsReceive[i].alreadBilled == 0) {
                total += $scope.reportBillsReceive[i].actual_value;
              }

              rows.push({
                issuing_date: $filter("date")(
                  $scope.reportBillsReceive[i].issuing_date,
                  "dd/MM/yyyy HH:mm:ss"
                ),
                account_type: $scope.reportBillsReceive[i].account_type,
                pax: $scope.reportBillsReceive[i].pax_name,
                flightLocator: $scope.reportBillsReceive[i].flightLocator,
                value: $rootScope.formatNumber(
                  $scope.reportBillsReceive[i].actual_value
                ),
                alreadBilled: $rootScope.formatNumber(
                  $scope.reportBillsReceive[i].alreadBilled
                ),
                tax: $scope.reportBillsReceive[i].tax,
                miles_tax: $scope.reportBillsReceive[i].miles_tax,
                due_date: $filter("date")(
                  $scope.reportBillsReceive[i].due_date,
                  "dd/MM/yyyy"
                ),
              });
            }
          }
          rows.push({
            flightLocator: "Total:",
            value: $rootScope.formatNumber(total),
          });

          if ($scope.wbillet.discount > 0) {
            rows.push({
              pax_name: "Descontos:",
              actual_value: $rootScope.formatNumber($scope.wbillet.discount),
            });
          }

          doc.autoTable(columns, rows, {
            styles: {
              fontSize: 8,
              overflow: "linebreak",
            },
            createdCell: function (cell, data) {
              if (data.column.dataKey === "value") {
                cell.styles.halign = "right";
              }
            },
          });
          doc.save(
            "Resumo Debitos " + $scope.reportBillsReceive[0].client + ".pdf"
          );
        };

        $scope.filterList = function (item) {
          return item.paymentType != "Antecipado";
        };

        $scope.filterListPreview = function (item) {
          return item.paymentType == "Antecipado";
        };

        $scope.printReportModel = function (isSave) {
          var doc = new jsPDF("p", "pt");
          doc.margin = 0.5;
          doc.setFontSize(16);
          doc.setTextColor(0);
          $scope.reportBillsReceive = $filter("filter")(
            $scope.billsreceive,
            true
          );
          doc.setFontStyle("bold");
          doc.text("IDEAL VIAGENS", 200, 50);

          var start = 60;
          doc.autoTable(
            [{ title: "", dataKey: "text" }],
            [
              { text: "PAULO RDRIGUES DOS SANTOS JUNIOR MMS VIAGENS" },
              { text: "CNPJ: 20.966.716/0001-55" },
              { text: "Rua: Jurua, 46 Conj. 301 Bairro: Graça" },
              { text: "CEP: 31.140-020 BELO HORIZONTE / MG" },
              { text: "Tel: 31.3017-0304  E-mail: " },
            ],
            {
              theme: "plain",
              styles: {
                fontSize: 8,
                overflow: "linebreak",
              },
              startY: start,
              margin: { horizontal: 10 },
              bodyStyles: { valign: "top" },
            }
          );

          start = doc.autoTableEndPosY() + 20;
          doc.autoTable(
            [
              { title: "Nº Borderô", dataKey: "ourNumber" },
              { title: "Valor da Fatura / Borderô", dataKey: "actual_value" },
              { title: "Data Emissão", dataKey: "issue_date" },
              { title: "Data Vencimento", dataKey: "due_date" },
            ],
            [
              {
                ourNumber: $scope.wbillet.our_number,
                actual_value:
                  "R$ " + $rootScope.formatNumber($scope.wbillet.actual_value),
                issue_date: $filter("date")(new Date(), "dd/MM/yyyy"),
                text: $filter("date")($scope.wbillet.due_date, "dd/MM/yyyy"),
              },
            ],
            {
              theme: "plain",
              styles: {
                fontSize: 8,
                overflow: "linebreak",
              },
              startY: start,
              margin: { horizontal: 10 },
              bodyStyles: { valign: "top" },
            }
          );

          start = doc.autoTableEndPosY() + 10;
          doc.autoTable(
            [{ title: "", dataKey: "text" }],
            [
              { text: "Sacado: " + $scope.client.company_name },
              {
                text:
                  "Endereço: " +
                  $scope.client.adress +
                  " " +
                  $scope.client.adressNumber +
                  " " +
                  $scope.client.adressComplement +
                  " " +
                  $scope.client.zipCode +
                  " " +
                  $scope.client.adressDistrict,
              },
              { text: "Tel: " + $scope.client.phoneNumber },
              { text: "CNPJ: " + $scope.client.registrationCode },
              { text: "" },
              {
                text:
                  "Banco: 237- Bradesco - Agência: 3420 Conta Corrente: 4879-8",
              },
            ],
            {
              theme: "plain",
              styles: {
                fontSize: 8,
                overflow: "linebreak",
              },
              startY: start,
              margin: { horizontal: 10 },
              bodyStyles: { valign: "top" },
            }
          );

          var columns = [
            { title: "DATA", dataKey: "issuing_date" },
            { title: "LOC", dataKey: "flightLocator" },
            { title: "TRECHO", dataKey: "from" },
            { title: "CIA", dataKey: "airline" },
            { title: "SOLICITANTE", dataKey: "issuing" },
            { title: "PAX", dataKey: "pax_name" },
            { title: "VALOR", dataKey: "actual_value" },
          ];

          var rows = [];
          var total = 0;

          for (var i = 0; i < $scope.reportBillsReceive.length; i++) {
            if ($scope.billetGenerated) {
              if ($scope.reportBillsReceive[i].status == "E") {
                if (
                  $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                  $scope.reportBillsReceive[i].account_type == "Credito" ||
                  $scope.reportBillsReceive[i].account_type ==
                    "Credito Adiantamento"
                ) {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from: $scope.reportBillsReceive[i].account_type,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].description,
                    actual_value:
                      $scope.reportBillsReceive[i].actual_value * -1,
                    issuing: $scope.reportBillsReceive[i].issuing,
                  });
                } else {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from:
                      $scope.reportBillsReceive[i].from +
                      "-" +
                      $scope.reportBillsReceive[i].to,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].pax_name,
                    actual_value: $rootScope.formatNumber(
                      $scope.reportBillsReceive[i].actual_value
                    ),
                    issuing: $scope.reportBillsReceive[i].issuing,
                  });
                }
              } else {
                if (
                  $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                  $scope.reportBillsReceive[i].account_type == "Credito" ||
                  $scope.reportBillsReceive[i].account_type ==
                    "Credito Adiantamento"
                ) {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from: $scope.reportBillsReceive[i].account_type,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].description,
                    actual_value:
                      $scope.reportBillsReceive[i].actual_value * -1,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                  });
                } else {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from:
                      $scope.reportBillsReceive[i].from +
                      "-" +
                      $scope.reportBillsReceive[i].to,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].pax_name,
                    actual_value: $rootScope.formatNumber(
                      $scope.reportBillsReceive[i].actual_value
                    ),
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                  });
                }
              }
            } else {
              if (
                $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                $scope.reportBillsReceive[i].account_type == "Credito" ||
                $scope.reportBillsReceive[i].account_type ==
                  "Credito Adiantamento"
              ) {
                rows.push({
                  issuing_date: $filter("date")(
                    $scope.reportBillsReceive[i].issuing_date,
                    "dd/MM/yyyy"
                  ),
                  flightLocator: $scope.reportBillsReceive[i].flightLocator,
                  from: $scope.reportBillsReceive[i].account_type,
                  airline: $scope.reportBillsReceive[i].airline,
                  pax_name: $scope.reportBillsReceive[i].description,
                  actual_value: $scope.reportBillsReceive[i].actual_value * -1,
                  issuing: $scope.reportBillsReceive[i].issuing,
                });
              } else {
                rows.push({
                  issuing_date: $filter("date")(
                    $scope.reportBillsReceive[i].issuing_date,
                    "dd/MM/yyyy"
                  ),
                  flightLocator: $scope.reportBillsReceive[i].flightLocator,
                  from:
                    $scope.reportBillsReceive[i].from +
                    "-" +
                    $scope.reportBillsReceive[i].to,
                  airline: $scope.reportBillsReceive[i].airline,
                  pax_name: $scope.reportBillsReceive[i].pax_name,
                  actual_value: $rootScope.formatNumber(
                    $scope.reportBillsReceive[i].actual_value
                  ),
                  issuing: $scope.reportBillsReceive[i].issuing,
                });
              }
            }
          }
          rows.push({});
          rows.push({
            pax_name: "Total:",
            actual_value: $rootScope.formatNumber(total),
          });

          start = doc.autoTableEndPosY() + 20;
          doc.autoTable(columns, rows, {
            theme: "plain",
            styles: {
              fontSize: 8,
              overflow: "linebreak",
            },
            startY: start,
            margin: { horizontal: 10 },
            bodyStyles: { valign: "top" },
            createdCell: function (cell, data) {
              if (data.column.dataKey === "actual_value") {
                cell.styles.halign = "right";
              }
            },
          });
          if(isSave)
            doc.save("BORDERO " + $scope.billsreceive[0].client + ".pdf");
          return doc;
        };

        $scope.imprimeTudo = function(){
          var ids = [];
          var inserido = false;
          ids.push(0);
          for(let i=0; i< $scope.clientsToReceive.length; i++){
            if($scope.clientsToReceive[i].acessado_hoje){
              ids.push($scope.clientsToReceive[i].id);
            }
          }
          $scope.filter = {
            clientName: " "
          };
          $.post(
            "../backend/application/index.php?rota=/loadClientsByFilter",
            { data: $scope.filter, impressao_ids: ids },
            function (result) {
              $scope.client = jQuery.parseJSON(result).dataset;
              var doc = new jsPDF("p", "pt");
              doc.margin = 0.5;
              doc.setFontSize(18);
              for(let j=0; j<$scope.client.length; j++){
                if($scope.client[j].raw_email != ""){
                  for(let k=0; k<$scope.client[j].raw_email.length;k++){
                    var resp = JSON.parse($scope.client[j].raw_email[k]);
                    var columns = resp[0];
                    var rows = resp[1];

                    doc.autoTable(columns, rows, {
                      theme: "grid",
                      styles: {
                        fontSize: 8,
                        overflow: "linebreak",
                      },
                      startY: 90,
                      margin: { horizontal: 10 },
                      bodyStyles: { valign: "top" },
                      createdCell: function (cell, data) {
                        if (data.column.dataKey === "actual_value") {
                          cell.styles.halign = "right";
                        }
                      },
                    });
                    doc.addPage();
                    inserido = true;
                  }
                }
              }
              if(inserido)
                doc.save("Relatorio.pdf");
            }
          );
        };

        $scope.printFill = function() {
          $scope.reportBillsReceive = $filter("filter")(
            $scope.billsreceive,
            true
          );

          if ($scope.billetGenerated) {
            var columns = [
              { title: "DATA", dataKey: "issuing_date" },
              { title: "LOC", dataKey: "flightLocator" },
              { title: "ORIGEM", dataKey: "from" },
              { title: "CIA", dataKey: "airline" },
              { title: "PAX", dataKey: "pax_name" },
              { title: "VALOR", dataKey: "actual_value" },
              { title: "Já Pago", dataKey: "alreadBilled" },
              { title: "Taxa", dataKey: "miles_tax" },
              { title: "Milhas", dataKey: "miles" },
              { title: "EMISSOR", dataKey: "issuing" },
              { title: "CLIENTE", dataKey: "client" },
              { title: "BORDERO", dataKey: "billet" },
            ];
          } else {
            var columns = [
              { title: "DATA", dataKey: "issuing_date" },
              { title: "LOC", dataKey: "flightLocator" },
              { title: "ORIGEM", dataKey: "from" },
              { title: "CIA", dataKey: "airline" },
              { title: "PAX", dataKey: "pax_name" },
              { title: "VALOR", dataKey: "actual_value" },
              { title: "Já Pago", dataKey: "alreadBilled" },
              { title: "Taxa", dataKey: "miles_tax" },
              { title: "Milhas", dataKey: "miles" },
              { title: "EMISSOR", dataKey: "issuing" },
              { title: "CLIENTE", dataKey: "client" },
            ];
          }

          var rows = [];

          for (var i = 0; i < $scope.reportBillsReceive.length; i++) {
            if ($scope.billetGenerated) {
              if ($scope.reportBillsReceive[i].status == "E") {
                if (
                  $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                  $scope.reportBillsReceive[i].account_type == "Credito" ||
                  $scope.reportBillsReceive[i].account_type ==
                    "Credito Adiantamento"
                ) {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from: $scope.reportBillsReceive[i].account_type,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].description,
                    actual_value:
                      $scope.reportBillsReceive[i].actual_value * -1,
                    alreadBilled: $scope.reportBillsReceive[i].alreadBilled,
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    billet: $scope.reportBillsReceive[i].billet,
                  });
                } else {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from:
                      $scope.reportBillsReceive[i].from +
                      "-" +
                      $scope.reportBillsReceive[i].to,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].pax_name,
                    actual_value: $rootScope.formatNumber(
                      $scope.reportBillsReceive[i].actual_value
                    ),
                    alreadBilled: $scope.reportBillsReceive[i].alreadBilled,
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    billet: $scope.reportBillsReceive[i].billet,
                  });
                }
              } else {
                if (
                  $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                  $scope.reportBillsReceive[i].account_type == "Credito" ||
                  $scope.reportBillsReceive[i].account_type ==
                    "Credito Adiantamento"
                ) {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from: $scope.reportBillsReceive[i].account_type,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].description,
                    actual_value:
                      $scope.reportBillsReceive[i].actual_value * -1,
                    alreadBilled: $scope.reportBillsReceive[i].alreadBilled,
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    billet: "",
                  });
                } else {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from:
                      $scope.reportBillsReceive[i].from +
                      "-" +
                      $scope.reportBillsReceive[i].to,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].pax_name,
                    actual_value: $rootScope.formatNumber(
                      $scope.reportBillsReceive[i].actual_value
                    ),
                    alreadBilled: $scope.reportBillsReceive[i].alreadBilled,
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    billet: "",
                  });
                }
              }
            } else {
              if (
                $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                $scope.reportBillsReceive[i].account_type == "Credito" ||
                $scope.reportBillsReceive[i].account_type ==
                  "Credito Adiantamento"
              ) {
                rows.push({
                  issuing_date: $filter("date")(
                    $scope.reportBillsReceive[i].issuing_date,
                    "dd/MM/yyyy"
                  ),
                  flightLocator: $scope.reportBillsReceive[i].flightLocator,
                  from: $scope.reportBillsReceive[i].account_type,
                  airline: $scope.reportBillsReceive[i].airline,
                  pax_name: $scope.reportBillsReceive[i].description,
                  actual_value: $scope.reportBillsReceive[i].actual_value * -1,
                  alreadBilled: $scope.reportBillsReceive[i].alreadBilled,
                  miles_tax: $scope.reportBillsReceive[i].miles_tax,
                  miles: $scope.reportBillsReceive[i].miles,
                  issuing: $scope.reportBillsReceive[i].issuing,
                  client: $scope.reportBillsReceive[i].client,
                });
              } else {
                rows.push({
                  issuing_date: $filter("date")(
                    $scope.reportBillsReceive[i].issuing_date,
                    "dd/MM/yyyy"
                  ),
                  flightLocator: $scope.reportBillsReceive[i].flightLocator,
                  from:
                    $scope.reportBillsReceive[i].from +
                    "-" +
                    $scope.reportBillsReceive[i].to,
                  airline: $scope.reportBillsReceive[i].airline,
                  pax_name: $scope.reportBillsReceive[i].pax_name,
                  actual_value: $rootScope.formatNumber(
                    $scope.reportBillsReceive[i].actual_value
                  ),
                  alreadBilled: $scope.reportBillsReceive[i].alreadBilled,
                  miles_tax: $scope.reportBillsReceive[i].miles_tax,
                  miles: $scope.reportBillsReceive[i].miles,
                  issuing: $scope.reportBillsReceive[i].issuing,
                  client: $scope.reportBillsReceive[i].client,
                });
              }
            }
          }
          rows.push({});
          rows.push({});

          rows.push({
            pax_name: "Total:",
            actual_value: $rootScope.formatNumber($scope.wbillet.actual_value),
          });

          if ($scope.wbillet.alreadyPaid > 0) {
            rows.push({
              pax_name: "Já Pago:",
              actual_value: $rootScope.formatNumber($scope.wbillet.alreadyPaid),
            });
            if ($scope.wbillet.actual_value - $scope.wbillet.alreadyPaid > 0) {
              rows.push({
                pax_name: "Restando:",
                actual_value: $rootScope.formatNumber(
                  $scope.wbillet.actual_value - $scope.wbillet.alreadyPaid
                ),
              });
            }
          }

          if ($scope.wbillet.discount > 0) {
            rows.push({
              pax_name: "Descontos:",
              actual_value: $rootScope.formatNumber($scope.wbillet.discount),
            });
          }

          rows.push({});

          if ($scope.billetsDivision.length > 0) {
            for (var j in $scope.billetsDivision) {
              rows.push({
                issuing_date: "Vencimento:",
                flightLocator: $filter("date")(
                  $scope.billetsDivision[j].dueDate,
                  "dd/MM/yyyy"
                ),
                pax_name: "Boleto Nº " + $scope.billetsDivision[j].name,
                actual_value: $rootScope.formatNumber(
                  $scope.billetsDivision[j].actualValue
                ),
              });
            }
          } else {
            rows.push({
              issuing_date: "Vencimento:",
              flightLocator: $filter("date")(
                $scope.wbillet.due_date,
                "dd/MM/yyyy"
              ),
              pax_name: "Boleto Nº " + $scope.wbillet.our_number,
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
                fontSize: 8,
                overflow: "linebreak",
              },
              startY: 90,
              margin: { horizontal: 10 },
              bodyStyles: { valign: "top" },
              createdCell: function (cell, data) {
                if (data.column.dataKey === "actual_value") {
                  cell.styles.halign = "right";
                }
              },
            });
            doc.save("BORDERO " + $scope.billsreceive[0].client + ".pdf");
          }
          return JSON.stringify(resp);
        };

        $scope.toDataUrl = function (url, callback) {
          var xhr = new XMLHttpRequest();
          xhr.responseType = "blob";
          xhr.onload = function () {
            var reader = new FileReader();
            reader.onloadend = function () {
              callback(reader.result);
            };
            reader.readAsDataURL(xhr.response);
          };
          xhr.open("GET", url);
          xhr.send();
        };

        $scope.printModel2 = function (isSave) {
          var doc = new jsPDF("p", "pt");
          doc.margin = 0.5;
          doc.setFontSize(18);
          $scope.reportBillsReceive = $filter("filter")(
            $scope.billsreceive,
            true
          );

          if ($scope.billetGenerated) {
            var columns = [
              { title: "DATA", dataKey: "issuing_date" },
              { title: "LOC", dataKey: "flightLocator" },
              { title: "ORIGEM", dataKey: "from" },
              { title: "CIA", dataKey: "airline" },
              { title: "PAX", dataKey: "pax_name" },
              { title: "VALOR", dataKey: "actual_value" },
              { title: "Taxa", dataKey: "miles_tax" },
              { title: "Milhas", dataKey: "miles" },
              { title: "EMISSOR", dataKey: "issuing" },
              { title: "CLIENTE", dataKey: "client" },
              { title: "OBS", dataKey: "comments" },
              { title: "BORDERO", dataKey: "billet" },
            ];
          } else {
            var columns = [
              { title: "DATA", dataKey: "issuing_date" },
              { title: "LOC", dataKey: "flightLocator" },
              { title: "ORIGEM", dataKey: "from" },
              { title: "CIA", dataKey: "airline" },
              { title: "PAX", dataKey: "pax_name" },
              { title: "VALOR", dataKey: "actual_value" },
              { title: "Taxa", dataKey: "miles_tax" },
              { title: "Milhas", dataKey: "miles" },
              { title: "EMISSOR", dataKey: "issuing" },
              { title: "CLIENTE", dataKey: "client" },
              { title: "OBS", dataKey: "comments" },
            ];
          }

          var rows = [];

          for (var i = 0; i < $scope.reportBillsReceive.length; i++) {
            if ($scope.billetGenerated) {
              if ($scope.reportBillsReceive[i].status == "E") {
                if (
                  $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                  $scope.reportBillsReceive[i].account_type == "Credito" ||
                  $scope.reportBillsReceive[i].account_type ==
                    "Credito Adiantamento"
                ) {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from: $scope.reportBillsReceive[i].account_type,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].description,
                    actual_value:
                      $scope.reportBillsReceive[i].actual_value * -1,
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    comments: $scope.reportBillsReceive[i].SaleDescription,
                    billet: $scope.reportBillsReceive[i].billet,
                  });
                } else {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from:
                      $scope.reportBillsReceive[i].from +
                      "-" +
                      $scope.reportBillsReceive[i].to,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].pax_name,
                    actual_value: $rootScope.formatNumber(
                      $scope.reportBillsReceive[i].actual_value
                    ),
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    comments: $scope.reportBillsReceive[i].SaleDescription,
                    billet: $scope.reportBillsReceive[i].billet,
                  });
                }
              } else {
                if (
                  $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                  $scope.reportBillsReceive[i].account_type == "Credito" ||
                  $scope.reportBillsReceive[i].account_type ==
                    "Credito Adiantamento"
                ) {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from: $scope.reportBillsReceive[i].account_type,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].description,
                    actual_value:
                      $scope.reportBillsReceive[i].actual_value * -1,
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    comments: $scope.reportBillsReceive[i].SaleDescription,
                    billet: "",
                  });
                } else {
                  rows.push({
                    issuing_date: $filter("date")(
                      $scope.reportBillsReceive[i].issuing_date,
                      "dd/MM/yyyy"
                    ),
                    flightLocator: $scope.reportBillsReceive[i].flightLocator,
                    from:
                      $scope.reportBillsReceive[i].from +
                      "-" +
                      $scope.reportBillsReceive[i].to,
                    airline: $scope.reportBillsReceive[i].airline,
                    pax_name: $scope.reportBillsReceive[i].pax_name,
                    actual_value: $rootScope.formatNumber(
                      $scope.reportBillsReceive[i].actual_value
                    ),
                    miles_tax: $scope.reportBillsReceive[i].miles_tax,
                    miles: $scope.reportBillsReceive[i].miles,
                    issuing: $scope.reportBillsReceive[i].issuing,
                    client: $scope.reportBillsReceive[i].client,
                    comments: $scope.reportBillsReceive[i].SaleDescription,
                    billet: "",
                  });
                }
              }
            } else {
              if (
                $scope.reportBillsReceive[i].account_type == "Reembolso" ||
                $scope.reportBillsReceive[i].account_type == "Credito" ||
                $scope.reportBillsReceive[i].account_type ==
                  "Credito Adiantamento"
              ) {
                rows.push({
                  issuing_date: $filter("date")(
                    $scope.reportBillsReceive[i].issuing_date,
                    "dd/MM/yyyy"
                  ),
                  flightLocator: $scope.reportBillsReceive[i].flightLocator,
                  from: $scope.reportBillsReceive[i].account_type,
                  airline: $scope.reportBillsReceive[i].airline,
                  pax_name: $scope.reportBillsReceive[i].description,
                  actual_value: $scope.reportBillsReceive[i].actual_value * -1,
                  miles_tax: $scope.reportBillsReceive[i].miles_tax,
                  miles: $scope.reportBillsReceive[i].miles,
                  issuing: $scope.reportBillsReceive[i].issuing,
                  client: $scope.reportBillsReceive[i].client,
                  comments: $scope.reportBillsReceive[i].SaleDescription,
                });
              } else {
                rows.push({
                  issuing_date: $filter("date")(
                    $scope.reportBillsReceive[i].issuing_date,
                    "dd/MM/yyyy"
                  ),
                  flightLocator: $scope.reportBillsReceive[i].flightLocator,
                  from:
                    $scope.reportBillsReceive[i].from +
                    "-" +
                    $scope.reportBillsReceive[i].to,
                  airline: $scope.reportBillsReceive[i].airline,
                  pax_name: $scope.reportBillsReceive[i].pax_name,
                  actual_value: $rootScope.formatNumber(
                    $scope.reportBillsReceive[i].actual_value
                  ),
                  miles_tax: $scope.reportBillsReceive[i].miles_tax,
                  miles: $scope.reportBillsReceive[i].miles,
                  issuing: $scope.reportBillsReceive[i].issuing,
                  client: $scope.reportBillsReceive[i].client,
                  comments: $scope.reportBillsReceive[i].SaleDescription,
                });
              }
            }
          }
          rows.push({});
          rows.push({});
          rows.push({});
          rows.push({
            pax_name: "Total:",
            actual_value: $rootScope.formatNumber($scope.wbillet.actual_value),
          });

          if ($scope.wbillet.discount > 0) {
            rows.push({
              pax_name: "Descontos:",
              actual_value: $rootScope.formatNumber($scope.wbillet.discount),
            });
          }

          rows.push({
            issuing_date: "Vencimento:",
            flightLocator: $filter("date")(
              $scope.wbillet.due_date,
              "dd/MM/yyyy"
            ),
            pax_name: "Boleto Nº " + $scope.wbillet.our_number,
          });

          doc.autoTable(columns, rows, {
            theme: "grid",
            styles: {
              fontSize: 8,
              overflow: "linebreak",
            },
            startY: 90,
            margin: { horizontal: 10 },
            bodyStyles: { valign: "top" },
            createdCell: function (cell, data) {
              if (data.column.dataKey === "actual_value") {
                cell.styles.halign = "right";
              }
            },
          });
          if(isSave)
            doc.save("BORDERO " + $scope.billsreceive[0].client + ".pdf");
          return doc;
        };

        $scope.numPerPageOpt = [10, 30, 50, 100];
        $scope.numPerPage = $scope.numPerPageOpt[2];
        $scope.currentPage = 1;
        $scope.currentPageBillsReceive = [];
        $rootScope.hashId = $scope.session.hashId;

        $scope.findYesterday = function () {
          $scope.botao = "b0";
          cfpLoadingBar.start();
          $scope.ocultar_bloqueados_disabled = true;
          $scope.botoes_processando = true;
          //$scope.$apply();
          $.post(
            "../backend/application/index.php?rota=/loadYesterdayBills",
            {sesion: $scope.session, days: $scope.days, apply_calc: false},
            function (result) {
              $scope.clientsToReceive = jQuery.parseJSON(result).dataset;
              $scope.clientsToReceive_historico = jQuery.parseJSON(result).dataset;
              $scope.$apply();
              $.post(
                "../backend/application/index.php?rota=/loadYesterdayBills",
                {sesion: $scope.session, days: $scope.days, apply_calc: true},
                function (result) {
                  $scope.clientsToReceive = jQuery.parseJSON(result).dataset;
                  $scope.clientsToReceive_historico = jQuery.parseJSON(result).dataset;
                  $scope.ocultar_bloqueados_disabled = false;
                  $scope.botoes_processando = false;
                  cfpLoadingBar.complete();
                  $scope.$apply();
                }
              );
            }
          );
        };

        $scope.findBilling = function () {
          $.post(
            "../backend/application/index.php?rota=/checkClientsDeadLine",
            { hashId: $scope.session.hashId },
            function (result) {
              $scope.clientsDeadLine = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );
        };

        $scope.findTodos = function (){
          $scope.botao = "b1";
          $scope.findAll();
        };

        $scope.findBilhetes = function (){
          $scope.botao = "b2";
          $scope.findAll();
        };

        $scope.findOutros = function (){
          $scope.botao = "b3";
          $scope.findAll();
        };

        $scope.carrega_botao = function (){
          switch($scope.botao){
            case "b0": $scope.findYesterday(); break;
            case "b1": case "b2": case "b3": $scope.findAll(); break;
          }
        }

        $scope.findAll = function () {
          cfpLoadingBar.start();
          $scope.ocultar_bloqueados_disabled = true;
          $scope.botoes_processando = true;
          //$scope.$apply();
          $.post(
            "../backend/application/index.php?rota=/loadClientToReceive",
            {sesion: $scope.session, apply_calc: false, botao: $scope.botao},
            function (result) {
              $scope.clientsToReceive = jQuery.parseJSON(result).dataset;
              $scope.clientsToReceive_historico = jQuery.parseJSON(result).dataset;
              $scope.$apply();
              $.post(
                "../backend/application/index.php?rota=/loadClientToReceive",
                {sesion: $scope.session, apply_calc: true, botao: $scope.botao},
                function (result) {
                  $scope.clientsToReceive = jQuery.parseJSON(result).dataset;
                  $scope.clientsToReceive_historico = jQuery.parseJSON(result).dataset;
                  $scope.ocultar_bloqueados_disabled = false;
                  $scope.botoes_processando = false;
                  cfpLoadingBar.complete();
                  $scope.$apply();
                }
              );
            }
          );
        };

        $scope.filtra_clientsToReceive = function(){
          $scope.clientsToReceive = $scope.clientsToReceive_historico;
          if($scope.ocultar_bloqueados){
            $scope.clientsToReceive = $scope.clientsToReceive_historico.filter(function(Row){
              if(Row.somente_reembolso == undefined)
                return true;
              return !Row.somente_reembolso;
            });
          }
        }

        $scope.getClass = function (billreceive) {
          if (
            billreceive.account_type == "Reembolso" ||
            billreceive.account_type == "Credito" ||
            billreceive.account_type == "Credito Adiantamento"
          ) {
            return "label label-danger";
          }
        };

        $scope.loadAllSpecialBillets = function () {
          $.post(
            "../backend/application/index.php?rota=/loadAllSpecialBillets",
            {},
            function (result) {
              $scope.specialBillets = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );
        };

        init = function () {
          $scope.checkValidRoute();
          $scope.tabindex = 0;
          $scope.filter = {};
          $scope.wizardBillet = false;
          $scope.billetGenerated = false;
          $scope.possibleCloseBillets = false;
          $rootScope.modalOpen = false;
          cfpLoadingBar.start();
          $scope.billetsDivision = [];

          $.post(
            "../backend/application/index.php?rota=/loadClientsNames",
            $scope.session,
            function (result) {
              $scope.clients = jQuery.parseJSON(result).dataset;
              cfpLoadingBar.complete();
            }
          );

          if(!$scope.clientsToReceive)
            $scope.findYesterday();
          $scope.findBilling();
          $scope.loadAllSpecialBillets();

          $scope.uploader = new FileUploader();
          $scope.uploader.url =
            "../backend/application/index.php?rota=/saveFile";
          $scope.uploader.autoUpload = true;
          $scope.uploader.filters.push({
            name: "customFilter",
            fn: function (item /*{File|FileLikeObject}*/, options) {
              return this.queue.length < 10;
            },
          });
        };
        return init();
      },
    ])
    .controller("WBilletModalDemoCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      function ($scope, $rootScope, $modal, $log) {
        // $scope.filterclients = $scope.$parent.clients;

        $scope.open = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "WBillet.html",
            controller: "WBilletModalInstanceCtrl",
            periods: $scope.$parent.periods,
            resolve: {
              filter: function () {
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
                filter._saleDateFrom = $rootScope.formatServerDate(
                  filter.saleDateFrom
                );
                filter._saleDateTo = $rootScope.formatServerDate(
                  filter.saleDateTo
                );
              }

              $scope.$parent.filter = filter;
              $scope.$parent.loadBillsReceive();
            },
            function () {
              $log.info("Modal dismissed at: " + new Date());
            }
          );
        };
      },
    ])
    .controller("WBilletModalInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "filter",
      function ($scope, $rootScope, $modalInstance, filter) {
        $scope.filter = filter;
        $scope.ok = function () {
          // $modalInstance.close($scope.filter);
        };
        $scope.cancel = function () {
          // $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("WBilletModalAttachmentCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      function ($scope, $rootScope, $modal, $log) {
        $scope.open = function (uploader) {
          if (uploader.queue.length <= 0) {
            var modalInstance;
            modalInstance = $modal.open({
              templateUrl: "atachmmentsAlert.html",
              controller: "WBilletModalAttachmentInstanceCtrl",
              periods: "zxc",
              resolve: {
                filter: function () {},
              },
            });

            modalInstance.result.then(
              function (filter) {
                $scope.$parent.mailOrder();
              },
              function () {}
            );
          } else {
            $scope.$parent.mailOrder();
          }
        };
      },
    ])
    .controller("WBilletModalAttachmentInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "filter",
      function ($scope, $rootScope, $modalInstance, filter) {
        $scope.filter = filter;
        $scope.ok = function () {
          $modalInstance.close($scope.filter);
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("BillsreceiveChangeCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      "logger",
      function ($scope, $rootScope, $modal, $log, $filter, logger) {
        $scope.open = function () {
          $scope.bill = this.billreceive;
          $scope.bill = angular.copy($scope.bill);
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "BillsreceiveChangeCtrl.html",
            controller: "BillsreceiveChangeInstanceCtrl",
            resolve: {
              main: function () {
                return $scope.$parent.$parent.$parent.$parent.main;
              },
              hashId: function () {
                return $scope.$parent.$parent.$parent.$parent.session.hashId;
              },
              bill: function () {
                return $scope.bill;
              },
            },
          });
          modalInstance.result.then(function (bill) {
            if (bill) {
              $.post(
                "../backend/application/index.php?rota=/changeBillValue",
                {
                  hashId: $scope.$parent.$parent.$parent.session.hashId,
                  data: bill,
                },
                function (result) {
                  logger.logSuccess(jQuery.parseJSON(result).message.text);
                  $scope.$parent.$parent.loadBillsReceive();
                  $scope.$parent.$parent.$apply();
                }
              );
            } else {
              $scope.$parent.$parent.loadBillsReceive();
              $scope.$parent.$parent.$apply();
            }
          });
        };
      },
    ])
    .controller("BillsreceiveChangeInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "main",
      "hashId",
      "bill",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        logger,
        main,
        hashId,
        bill
      ) {
        $scope.main = main;
        $scope.hashId = hashId;
        $scope.bill = bill;
        $scope.checked = true;

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };

        $scope.check = function () {
          $.post(
            "../backend/application/index.php?rota=/checkAcessCode",
            { hashId: $scope.hashId, data: $scope.bill },
            function (result) {
              $scope.response = jQuery.parseJSON(result).dataset;

              if ($scope.response.valid == "true") {
                $scope.checked = false;
                $scope.$apply();
              } else {
                logger.logError("Dados não conferem!");
              }
            }
          );
        };

        $scope.remove = function () {
          $.post(
            "../backend/application/index.php?rota=/removeBill",
            { hashId: $scope.hashId, data: $scope.bill },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $modalInstance.close(undefined);
            }
          );
        };

        $scope.ok = function () {
          $modalInstance.close($scope.bill);
        };
      },
    ])
    .controller("CloseBilletsWithCreditCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $rootScope.$on("openCloseBillesWtihCreditModal", function (
          event,
          args
        ) {
          if ($rootScope.modalOpen == false) {
            $rootScope.modalOpen = true;
            $scope.open(args);
          }
        });

        $scope.open = function (args) {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "CloseBilletsWithCreditModal.html",
            controller: "CloseBilletsWithCreditInstanceCtrl",
            resolve: {
              selected: function () {
                return args;
              },
            },
          });
          modalInstance.result.then(
            function (resolve) {
              $scope.$parent.setBilletToClose(resolve);
              $rootScope.modalOpen = false;
            },
            function () {
              $rootScope.modalOpen = false;
            }
          );
        };
      },
    ])
    .controller("CloseBilletsWithCreditInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "selected",
      function ($scope, $rootScope, $modalInstance, logger, selected) {
        $scope.selected = selected;

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };

        $scope.setSelected = function (billet) {
          $modalInstance.close(billet);
        };
      },
    ]);
})();
