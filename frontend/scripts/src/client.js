(function () {
  "use strict";
  angular
    .module("app.sale", ["angularFileUpload", "ui.mask", "ui.select"])
    .controller("ClientCtrl", [
      "$scope",
      "$rootScope",
      "$filter",
      "$modal",
      "cfpLoadingBar",
      "logger",
      "FileUploader",
      "$http",
      function (
        $scope,
        $rootScope,
        $filter,
        $modal,
        cfpLoadingBar,
        logger,
        FileUploader,
        $http
      ) {
        var init;
        var original;
        $scope.clientStatus = [
          "Pendente",
          "Aprovado",
          "Bloqueado",
          "Reprovado",
          "Arquivado",
          "Coberto",
          "Analise prazo",
          "Pendente Liberacao",
          "Antecipado/Bloqueado",
        ];
        $scope.clientPayments = ["Boleto", "Antecipado"];
        $scope.clientBilling = [
          "Diario",
          "Semanal",
          "Quinzenal",
          "Mensal",
          "Outro",
        ];
        $scope.origins = ["MMS", "SRM"];
        $scope.searchKeywords = "";
        $scope.filteredClients = [];
        $scope.row = "";
        $scope.partners = [{}, {}];
        $scope.filters = [
          { name: "id", label: "id", check: true },
          { name: "company_name", label: "Razao Social" },
          { name: "name", label: "Nome", check: true },
          { name: "registrationCode", label: "Cnpj" },
          { name: "city", label: "Cidade" },
          { name: "state", label: "Estado" },
          { name: "cityfullname", label: "Cidade - Estado", check: true },
          { name: "adress", label: "Endereço" },
          { name: "partnerType", label: "" },
          { name: "email", label: "email" },
          { name: "phoneNumber", label: "Telefone Celular" },
          { name: "phoneNumber2", label: "Telefone Comercial" },
          { name: "phoneNumber3", label: "Telefone Residencial" },
          { name: "status", label: "Status", check: true },
          { name: "paymentType", label: "Forma de Pagamento", check: true },
          { name: "paymentDays", label: "Dias de Pagamento", check: true },
          { name: "description", label: "Observação" },
          { name: "creditAnalysis", label: "Score" },
          { name: "registrationCodeCheck", label: "CNPJ Regular" },
          { name: "adressCheck", label: "Endereço Confere" },
          { name: "creditDescription", label: "Registro de débito" },
          { name: "partnerLimit", label: "Limite Cliente", check: true },
          { name: "last_emission", label: "Ultima emissão", check: true },
          {
            name: "countd",
            label: "Quantidade de emissão(total)",
            check: true,
          },
          {
            name: "last_month_emission",
            label: "Quantidade de emissão(ultimo mês)",
          },
          { name: "workingDays", label: "Dias Úteis" },
          { name: "billingPeriod", label: "Faturamento" },
          { name: "birthdate", label: "Data Fundação" },
          { name: "typeSociety", label: "Tipo Sociedade" },
          { name: "mulct", label: "Multa atraso" },
          { name: "registerDate", label: "Data Registro" },
          { name: "adressNumber", label: "Número Endereço" },
          { name: "adressComplement", label: "Complemento Endereço" },
          { name: "zipCode", label: "CEP" },
          { name: "adressDistrict", label: "Bairro" },
          { name: "account", label: "Tipo Cliente" },
          { name: "financialContact", label: "Responsavel Financeiro" },
          { name: "limitMargin", label: "Margem de Limite(%)" },
          { name: "salePlan", label: "Plano Commercial" },
          { name: "finnancialEmail", label: "Emails Financeiro" },
          { name: "interest", label: "Juros" },
          { name: "operationPlan", label: "Plano Multa/Custo" },
          { name: "dealer", label: "Representante" },
          { name: "lastCreditAnalysis", label: "Data Ultima Analise" },
          { name: "daysToBoarding", label: "Dias Embarque" },
        ];
        $scope.modification = [];

        $scope.changeFilds = function () {
          $scope.filteredFilds = $filter("filter")($scope.filters, true);
        };

        $scope.select = function (page) {
          // var end, start;
          // start = (page - 1) * $scope.numPerPage;
          // end = start + $scope.numPerPage;
          // return $scope.currentPageClients = $scope.filteredClients.slice(start, end);
          $scope.loadClient();
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

        $scope.search = function () {
          $scope.filteredClients = $filter("filter")(
            $scope.clients,
            $scope.searchKeywords
          );
          return $scope.onFilterChange();
        };

        $scope.findColor = function (date, fild) {
          if (date && fild == "last_emission") {
            date = $rootScope.findDate(date);
            if (date != "" && date != "Invalid Date") {
              date.setDate(date.getDate() + 2);
              date.setHours(0, 0, 0, 0);
              if (date > $scope.actual_date) {
                return "#50A52F";
              } else {
                date.setDate(date.getDate() + 1);
                if (date > $scope.actual_date) {
                  return "#D6CE62";
                } else {
                  return "#E04545";
                }
              }
            }
          }
        };

        $scope.getCEP = function () {
          $.post(
            "../backend/application/index.php?rota=/getCep",
            { data: $scope.selected },
            function (result) {
              $scope.searchCep = jQuery.parseJSON(result).dataset;
              if ($scope.searchCep.status == "success") {
                $scope.selected.adress = $scope.searchCep.data.logradouro;
                $scope.selected.adressDistrict = $scope.searchCep.data.bairro;
                $scope.selected.state = $scope.searchCep.data.uf;
                $scope.selected.city = $scope.searchCep.data.localidade;
              } else {
                logger.logError("Erro na busca pelo CEP");
              }
              $scope.$digest();
            }
          );
        };

        $scope.removeClient = function () {
          $.post(
            "../backend/application/index.php?rota=/removeClient",
            { hashId: $scope.session.hashId, data: $scope.selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.cancelEdit();
            }
          );
        };

        $scope.searchRegistrationCode = function () {
          if ($scope.selected) {
            if ($scope.selected.registrationCode) {
              $.post(
                "../backend/application/index.php?rota=/searchRegistrationCode",
                {
                  hashId: $scope.session.hashId,
                  data: $scope.selected,
                  partnerType: "C",
                },
                function (result) {
                  if (jQuery.parseJSON(result).message.type == "S") {
                    logger.logError("Cliente ja cadastrado");
                  }
                }
              );
            }
          }
          $scope.currentSuggestions = $filter("filter")(
            $scope.suggestions,
            $scope.selected.registrationCode
          );
        };

        $scope.showBillingPeriod = function () {
          if ($scope.selected) {
            return (
              $scope.selected.billingPeriod == "Diario" ||
              $scope.selected.billingPeriod == "Semanal" ||
              $scope.selected.billingPeriod == "Quinzenal" ||
              $scope.selected.billingPeriod == "Mensal" ||
              $scope.selected.billingPeriod == "" ||
              $scope.selected.billingPeriod == undefined
            );
          }
        };

        $scope.setSelected = function () {
          // if(!$scope.main.dealer || $scope.main.internCommercial) {

          $scope.partners = [{}, {}];
          $("#partner_limit").number(true, 2, ",", ".");
          original = angular.copy(this.client);

          $scope.clientDocs = [];
          $scope.modification = [];
          $scope.authorized = false;

          $scope.selected = this.client;
          if ($scope.selected.last_emission) {
            $scope.selected.last_emission = $rootScope.findDate(
              $scope.selected.last_emission
            );
          }
          if ($scope.selected.birthdate) {
            $scope.selected.birthdate = $rootScope.findDate(
              $scope.selected.birthdate
            );
          }
          if ($scope.selected.registerDate) {
            $scope.selected.registerDate = $rootScope.findDate(
              $scope.selected.registerDate
            );
          }
          if ($scope.selected.lastCreditAnalysis) {
            $scope.selected._lastCreditAnalysis = $rootScope.findDate(
              $scope.selected.lastCreditAnalysis
            );
          }
          // if($scope.selected.partnerData.birthDate) {
          //   $scope.selected.partnerData._birthDate = $rootScope.findDate($scope.selected.partnerData.birthDate);
          // }

          $scope.selected._status = $scope.selected.status;
          $scope.selected._billingPeriod = $scope.selected.billingPeriod;
          $scope.selected._paymentType = $scope.selected.paymentType;
          $scope.selected._paymentDays = $scope.selected.paymentDays;
          $scope.selected._partnerLimit = $scope.selected.partnerLimit;

          $.post(
            "../backend/application/index.php?rota=/loadPartnersClient",
            { hashId: $scope.session, data: $scope.selected },
            function (result) {
              $scope.partners = jQuery.parseJSON(result).dataset;
              for (var i in $scope.partners) {
                if ($scope.partners[i].birthdate != "")
                  $scope.partners[i].birthdate = $rootScope.findDate(
                    $scope.partners[i].birthdate
                  );
              }

              if ($scope.partners.length < 1) $scope.partners = [{}, {}];
              else if ($scope.partners.length == 1) $scope.partners.push({});
              $scope.loadIssuers();
            }
          );

          $scope.currentSuggestions = $filter("filter")(
            $scope.suggestions,
            $scope.selected.registrationCode
          );

          $.post(
            "../backend/application/index.php?rota=/loadSalePlans",
            { hashId: $scope.session, data: $scope.selected },
            function (result) {
              $scope.SalePlans = jQuery.parseJSON(result).dataset;
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadCommercialProgress",
            { hashId: $scope.session, data: $scope.selected },
            function (result) {
              $scope.ClientLog = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadControlPlans",
            $scope.session,
            function (result) {
              $scope.ControlPlans = jQuery.parseJSON(result).dataset;
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadActualClientLimit",
            { hashId: $scope.session.hashId, data: $scope.selected },
            function (result) {
              $scope.ActualLimit = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadClientAnalisys",
            { hashId: $scope.session.hashId, data: $scope.selected },
            function (result) {
              $scope.finnacialInfo = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadClientDocs",
            { hashId: $scope.session.hashId, data: $scope.selected },
            function (result) {
              $scope.clientDocs = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadClientNotifications",
            { data: $scope.selected },
            function (result) {
              $scope.clientNotifications = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadCupons",
            { data: $scope.selected },
            function (result) {
              $scope.cupons = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );

          $scope.loadClientRegistrionUpdates();
          // }
        };

        $scope.loadClientRegistrionUpdates = function () {
          $.post(
            "../backend/application/index.php?rota=/client/loadClientRegistrionUpdates",
            { data: $scope.selected },
            function (result) {
              $scope.clientRegistrationUpdates = jQuery.parseJSON(
                result
              ).dataset;
              $scope.$digest();
            }
          );
        };

        $scope.openModalRegistrationUpdate = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/client_adress_update.html",
            controller: "ModalRegistrationUpdateInstanceCtrl",
            periods: $scope.$parent.periods,
            resolve: {
              clientRegistrationUpdates: function () {
                return $scope.clientRegistrationUpdates;
              },
              selected: function () {
                return $scope.selected;
              },
            },
          });
        };

        $scope.openModalCreditAnalysis = function (partner) {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/credit_analysis.html",
            controller: "ModalCreditAnalysisCtrl",
            periods: $scope.periods,
            size: "lg",
            resolve: {
              partnerObject: function () {
                return partner;
              },
            },
          });
        };

        $scope.setStatusNotification = function (not) {
          $.post(
            "../backend/application/index.php?rota=/setStatusNotification",
            { data: $scope.selected, notification: not },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $.post(
                "../backend/application/index.php?rota=/loadClientNotifications",
                { data: $scope.selected },
                function (result) {
                  $scope.clientNotifications = jQuery.parseJSON(result).dataset;
                  $scope.$digest();
                }
              );
            }
          );
        };

        $scope.checkOurAnalisys = function () {
          if ($scope.selected) {
            if ($scope.selected.creditAnalysis) {
              if ($scope.selected.creditAnalysis.length >= 5) {
                var score = $scope.selected.creditAnalysis.slice(2, 5);
                // console.log(score);
              }
            }
          }
        };

        $scope.registerLog = function () {
          $.post(
            "../backend/application/index.php?rota=/saveCommercialProgress",
            {
              hashId: $scope.session.hashId,
              data: $scope.selected,
              progress: $scope.newProgress,
            },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.newProgress = {};
              $.post(
                "../backend/application/index.php?rota=/loadCommercialProgress",
                { hashId: $scope.session, data: $scope.selected },
                function (result) {
                  $scope.ClientLog = jQuery.parseJSON(result).dataset;
                  $scope.$digest();
                }
              );
            }
          );
        };

        $scope.loadIssuers = function () {
          $.post(
            "../backend/application/index.php?rota=/loadIssuers",
            { data: $scope.selected },
            function (result) {
              $scope.issuers = jQuery.parseJSON(result).dataset;
              $scope.isTable = false;
              $scope.uploader.formData = {
                hashId: $scope.session.hashId,
                data: $scope.selected,
              };
              $scope.loadFiles();
              $scope.$apply();
            }
          );
        };

        $scope.toggleFormTable = function () {
          $scope.isTable = !$scope.isTable;
          return console.log($scope.isTable);
        };

        $scope.order = function (rowName) {
          $scope.searchOrder = rowName;
          $scope.searchOrderDown = undefined;
          $scope.loadClient();
        };

        $scope.orderDown = function (rowName) {
          $scope.searchOrder = undefined;
          $scope.searchOrderDown = rowName;
          $scope.loadClient();
        };

        $scope.intencionToSave = function () {
          $scope.saveClient();
        };

        $scope.openAutorization = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/autorization.html",
            controller: "AutorizationCtrl",
            periods: $scope.periods,
            resolve: {
              main: function () {
                return $scope.main;
              },
              permissions: function () {
                return { type: "Comercial" };
              },
            },
          });
          modalInstance.result.then(function (result) {
            $scope.authorized = result;
          });
        };

        $scope.analiseAntiga = function (date, registerDate) {
          if (date) {
            if (date !== "" && date != "Invalid Date") {
              var data = new Date();
              data.setMonth(data.getMonth() - 6);
              if (date < data) {
                return true;
              }
            }
          }
          if (registerDate) {
            if (registerDate !== "" && registerDate != "Invalid Date") {
              var data = new Date();
              data.setMonth(data.getMonth() - 6);
              if (registerDate < data) {
                return true;
              }
            }
          }

          return false;
        };

        $scope.saveClient = function () {
          $scope.selected.cityfullname =
            $scope.selected.city + ", " + $scope.selected.state;
          if ($scope.selected.noRegistrationCode != true) {
            if ($scope.selected.registrationCode.length <= 11) {
              if (!$rootScope.ValidaCPF($scope.selected.registrationCode)) {
                $scope.openAutorization();
                return logger.logError("CPF Inválido");
              }
            } else {
              if (!$rootScope.ValidaCNPJ($scope.selected.registrationCode)) {
                $scope.openAutorization();
                return logger.logError("CNPJ Inválido");
              }
            }
          }
          if ($scope.selected.name.length > 40 && $scope.authorized == false) {
            $scope.openAutorization();
            return logger.logError(
              "Nome da agencia nao deve ser superior a 40 caracteres"
            );
          }
          if (!$scope.selected.adress && $scope.authorized == false) {
            $scope.openAutorization();
            return logger.logError("Endereço obrigatorio");
          }
          if (!$scope.selected.city && $scope.authorized == false) {
            $scope.openAutorization();
            return logger.logError("Estado e cidade obrigatorio");
          }
          if ($scope.selected.birthdate) {
            if (
              $scope.selected.birthdate !== "" &&
              $scope.selected.birthdate != "Invalid Date"
            ) {
              $scope.selected._birthdate = $rootScope.formatServerDate(
                $scope.selected.birthdate
              );
            }
          }
          if ($scope.selected._lastCreditAnalysis) {
            if (
              $scope.selected._lastCreditAnalysis !== "" &&
              $scope.selected._lastCreditAnalysis != "Invalid Date"
            ) {
              $scope.selected.lastCreditAnalysis = $rootScope.formatServerDate(
                $scope.selected._lastCreditAnalysis
              );
            }
          }
          if (
            $scope.selected.registerDate !== "" &&
            $scope.selected.registerDate != "Invalid Date"
          ) {
            $scope.selected._registerDate = $rootScope.formatServerDate(
              $scope.selected.registerDate
            );
          }
          var docs = "";
          var and = "";
          for (var i in $scope.selected.docs) {
            if ($scope.selected.docs[i].checked) {
              docs = docs + and + $scope.selected.docs[i].id;
              and = "_";
            }
          }
          $scope.selected.docsSelected = docs;
          // if($scope.selected.partnerData) {
          //   if($scope.selected.partnerData._birthDate !== "" && $scope.selected.partnerData._birthDate != 'Invalid Date') {
          //     $scope.selected.partnerData.birthDate = $rootScope.formatServerDate($scope.selected.partnerData._birthDate);
          //   }
          // }
          for (var i in $scope.partners) {
            if ($scope.partners[i].birthdate) {
              if (
                $scope.partners[i].birthdate !== "" &&
                $scope.partners[i].birthdate != "Invalid Date"
              ) {
                $scope.partners[i]._birthdate = $rootScope.formatServerDate(
                  $scope.partners[i].birthdate
                );
              }
            }
          }
          cfpLoadingBar.start();
          $.post(
            "../backend/application/index.php?rota=/checkRegister",
            {
              hashId: $scope.session.hashId,
              data: $scope.selected,
              partners: $scope.partners,
            },
            function (result) {
              if (jQuery.parseJSON(result).message.type == "S") {
                $.post(
                  "../backend/application/index.php?rota=/saveClient",
                  {
                    hashId: $scope.session.hashId,
                    data: $scope.selected,
                    partners: $scope.partners,
                  },
                  function (result) {
                    logger.logSuccess(jQuery.parseJSON(result).message.text);
                    $scope.loadClient();
                    cfpLoadingBar.complete();
                  }
                );
                $scope.isTable = true;
                $scope.$digest();
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }
            }
          );
        };

        $scope.copyAddress = function () {
          $scope.selected.adressFinnancial = $scope.selected.adress;
          $scope.selected.adressNumberFinnancial = $scope.selected.adressNumber;
          $scope.selected.adressComplementFinnancial =
            $scope.selected.adressComplement;
          $scope.selected.adressDistrictFinnancial =
            $scope.selected.adressDistrict;
          $scope.selected.zipCodeFinnancial = $scope.selected.zipCode;
          $scope.selected.cityFinnancialState = $scope.selected.state;
          $scope.selected.cityFinnancialName = $scope.selected.city;
        };

        $scope.cancelEdit = function () {
          $scope.isTable = true;
          $scope.newUpload = false;
          $scope.mail = false;
          $scope.newRegister = false;
          $scope.loadClient();
        };

        $scope.cancelEditEmail = function () {
          $scope.mail = false;
          $scope.wemail.emailpartner = "";
          $scope.wemail.mailcc = "";
          $scope.wemail.subject = "";
          $scope.wemail.emailContent = "";
        };

        $scope.newClient = function () {
          $scope.selected = {};
          $scope.issuers = {};
          $scope.partners = [{}, {}];
          $scope.selected.type = "C";
          $scope.selected.registerDate = new Date();
          $scope.isTable = !$scope.isTable;
          $scope.newRegister = true;
        };

        $scope.loadClient = function () {
          $scope.newUpload = false;
          cfpLoadingBar.start();
          $.post(
            "../backend/application/index.php?rota=/loadClient",
            {
              page: $scope.currentPage,
              numPerPage: $scope.numPerPage,
              searchKeywords: $scope.searchKeywords,
              order: $scope.searchOrder,
              orderDown: $scope.searchOrderDown,
              data: $scope.filter,
            },
            function (result) {
              $scope.clients = jQuery.parseJSON(result).dataset.clients;
              $scope.totalData = jQuery.parseJSON(result).dataset.total;
              $scope.$digest();
              // $scope.search();
              cfpLoadingBar.complete();
              // return $scope.select($scope.currentPage);
            }
          );
        };

        $scope.toggleNewUpload = function () {
          if ($scope.newUpload) {
            $scope.newUpload = false;
          } else {
            $scope.newUpload = true;
          }
        };

        $scope.loadFiles = function () {
          $scope.filesLoaded = new FileReader();
          $.post(
            "../backend/application/index.php?rota=/loadFiles",
            { hashId: $scope.session.hashId, data: $scope.selected },
            function (result) {
              $scope.filesLoaded = jQuery.parseJSON(result).dataset;
              $scope.$apply();
            }
          );
        };

        $scope.fillEmailContent = function () {
          $scope.mail = true;
          $scope.selected = this.client;
          $scope.wemail.emailpartner = $scope.selected.email;
          $scope.wemail.mailcc = "";
          $scope.wemail.subject = "[COMERCIAL] - One Milhas";
          $scope.wemail.emailContent = "<br><br><br>";
        };

        $scope.mailOrder = function () {
          var file = [];
          for (var j in $scope.uploader.queue) {
            file.push($scope.uploader.queue[j].file.name);
          }
          $.post(
            "../backend/application/index.php?rota=/mailOrder",
            {
              hashId: $scope.session.hashId,
              data: $scope.wemail,
              attachment: file,
              type: "COMERCIAL",
              emailType: $rootScope.emailType,
            },
            function (result) {
              if (jQuery.parseJSON(result).message.type == "S") {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
                $scope.mail = false;
                $scope.uploader.clearQueue();
                $scope.wemail = {};
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }
            }
          );
        };

        $scope.resetPassword = function () {
          $.post(
            "../backend/application/index.php?rota=/busca/resetPassword",
            { data: $scope.selected },
            function (result) {
              if (jQuery.parseJSON(result).message.type == "S") {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
              } else {
                logger.logError(jQuery.parseJSON(result).message.text);
              }
            }
          );
        };

        $scope.removeFile = function (file) {
          $.post(
            "../backend/application/index.php?rota=/removeFile",
            {
              hashId: $scope.session.hashId,
              data: $scope.selected,
              file: file,
            },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.loadFiles();
            }
          );
        };

        $scope.addDealer = function () {
          $scope.selected.dealers.push({ name: "" });
        };

        $scope.removeDealer = function () {
          $scope.selected.dealers.pop();
        };

        $scope.setDocumentStatus = function (doc) {
          $.post(
            "../backend/application/index.php?rota=/setDocumentStatus",
            { hashId: $scope.session.hashId, data: $scope.selected, doc: doc },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $.post(
                "../backend/application/index.php?rota=/loadClientDocs",
                { hashId: $scope.session.hashId, data: $scope.selected },
                function (result) {
                  $scope.clientDocs = jQuery.parseJSON(result).dataset;
                  $scope.$digest();
                }
              );
            }
          );
        };

        $scope.fillEmailIssuer = function (issuer) {
          $scope.mail = true;
          $scope.wemail.emailpartner = $scope.selected.email;
          $scope.wemail.mailcc = "";
          $scope.wemail.subject = "[COMERCIAL] - ONE MILHAS";
          $scope.wemail.emailContent = $scope.returnLoginText(issuer);
          $scope.uploader.formData = { hashId: $scope.session.hashId };
          $rootScope.emailType = "EMAIL-ISSUER->" + issuer.id;
        };

        $scope.fillEmailActivation = function () {
          $scope.mail = true;
          $scope.uploader.formData = { hashId: $scope.session.hashId };
          $scope.wemail.emailpartner = $scope.selected.email;
          $scope.wemail.mailcc = "";
          $scope.wemail.subject = "[COMERCIAL] - ONE MILHAS";
          $scope.wemail.emailContent = $scope.returnActivivationText();
          $rootScope.emailType = "EMAIL-ACTIVATION";
        };

        $scope.findClass = function (issuer) {
          if (!issuer.sendEmail) {
            return "btn btn-danger smallBtn";
          } else {
            return "btn btn-info smallBtn";
          }
        };

        $scope.editCupom = function (cupom) {
          cupom._dataInicio = new Date(cupom.dataInicio);
          cupom._dataExpiracao = new Date(cupom.dataExpiracao);
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "CupomDataCtrl.html",
            controller: "CupomModalInstanceCtrl",
            periods: $scope.periods,
            resolve: {
              cupom: function () {
                return cupom;
              },
            },
          });
          modalInstance.result.then(function (issuer_selected) {
            $.post(
              "../backend/application/index.php?rota=/loadCupons",
              { data: $scope.selected },
              function (result) {
                $scope.cupons = jQuery.parseJSON(result).dataset;
              }
            );
          });
        };

        $scope.openCupomModal = function () {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "CupomDataCtrl.html",
            controller: "CupomModalInstanceCtrl",
            periods: $scope.periods,
            resolve: {
              cupom: function () {
                return { client_id: $scope.selected.id };
              },
            },
          });
          modalInstance.result.then(function (issuer_selected) {
            $.post(
              "../backend/application/index.php?rota=/loadCupons",
              { data: $scope.selected },
              function (result) {
                $scope.cupons = jQuery.parseJSON(result).dataset;
              }
            );
          });
        };

        $scope.returnLoginText = function (issuer) {
          let text =
            "<p class='MsoNormal'><span>Seguindo com processo de ativação segue dados para acesso ao Busca Ideal.<span></span></span></p>" +
            "<p class='MsoNormal'><span>&nbsp;</span>Seu acesso ao Busca Ideal é o seguinte:</p>" +
            "<p class='MsoNormal'><span></span></p>" +
            "<p class='MsoNormal'><span>&nbsp;</span></p>" +
            "<ul type='disc'>" +
            "<li class='MsoNormal'><span>Usuário: <b>" +
            $scope.selected.prefixo +
            ".admin</b></span><span><span></span></span></li>" +
            "<li class='MsoNormal'><span>Senha: <b>admin123</b></span><span><span></span></span></li>" +
            "</ul>" +
            "<p class='MsoNormal'><span><span class='m_-2386000081486288919gmail-apple-converted-space'><br></span></span></p>" +
            "<p class='MsoNormal'><span style='color: rgb(46, 116, 181);'>Link Busca Ideal:<a href='https://busca.buscaideal.com.br'>https://busca.buscaideal.com.br</a><a href='https://busca.buscaideal.com.br' target='_blank'></a></span><br></p>" +
            "<p class='MsoNormal'><span></span></p>" +
            "<br>" +
            "<p class='MsoNormal'><span>Recomendamos que no primeiro acesso seja realizado alteração da senha padrão.</span><span></span></p>" +
            "<p class='MsoNormal'><span>(Meus Dados &gt; Senha e Validar senha)</span><span></span></p>" +
            "<p class='MsoNormal'><span>&nbsp;</span><span></span></p>" +
            "<p class='MsoNormal'><span>Anexo manual de utilização do sistema.</span><span></span></p>" +
            "<p class='MsoNormal'><span>&nbsp;</span><span></span></p>" +
            "<p class='MsoNormal'><b><i>Para criação de novos logins, utilize a aba minha conta em seu login master</i></b><br></p>" +
            "<div><b><i><br></i></b></div>" +
            "<div>Atenciosamente,<br></div>";

          if ($scope.selected.subClient) {
            text = text.replace("Busca Ideal", "VOE B2B");
            text = text.replace(
              "http://buscaideal.com",
              "http://busca.voeb2b.com.br"
            );
          }
          return text;
        };

        $scope.returnActivivationText = function () {
          let text =
            "  <p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span>Caro parceiro nosso cadastro foi concluído, algumas informações sobre procedimentos e contatos estão no anexo desse e-mail.</span><span><br><br><span>Atender a " +
            $scope.selected.name +
            " será um grande prazer, gostaria de desde já nos colocar à disposição para o que precisar, sejam duvidas, criticas, sugestões ou qualquer outra demanda, abaixo seguem nossos telefones de contato.</span></span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span><span><br></span></span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span></span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><b><i><span style='color: rgb(47, 85, 151);'>*Toda demanda inicial como envio de O.P., passagens, informativos e boletos serão enviadas nesse e-mail, havendo interesse de alteração ou inclusão de novos e-mails gentileza responder esse e-mail especificando o e-mail e o que deverá ser encaminhado.</span></i></b><span></span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><b><span style='color: rgb(47, 85, 151);'><br></span></b></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><b><span style='color: rgb(47, 85, 151);'>Gentileza confirmar o recebimento desse e-mail (protocolo)</span></b><br></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span></span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span>&nbsp;</span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span>Fixo: &nbsp; &nbsp; &nbsp; &nbsp; (31) 3972-9601</span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span>Celular: &nbsp;(31) 9 7150-5535</span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (31) 9 9584-9761</span></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'>Um forte abraço e muito sucesso nessa nova parceria!<br></p>" +
            "<p style='color: rgb(34, 34, 34);background-color: rgb(255, 255, 255);'><span><br><span>Atenciosamente,</span><br></span></p>" +
            "<p></p>";
          if ($scope.selected.subClient) {
            text = text.replace("Busca Ideal", "VOE B2B");
            text = text.replace(
              "http://buscaideal.com",
              "http://busca.voeb2b.com.br"
            );
          }
          return text;
        };

        $scope.numPerPageOpt = [10, 30, 50, 100, 2000];
        $scope.numPerPage = $scope.numPerPageOpt[2];
        $scope.currentPage = 1;
        $scope.currentPageClients = [];
        $rootScope.hashId = $scope.session.hashId;

        $scope.getFild = function (client, fild) {
          if (
            new Date(client[fild]) == "Invalid Date" ||
            typeof client[fild] == "number" ||
            client[fild] == "" ||
            client[fild].length <= 9 ||
            fild == "name"
          ) {
            return client[fild];
          } else {
            return $filter("date")(
              $rootScope.findDate(client[fild]),
              "dd/MM/yyyy"
            );
          }
        };

        $scope.searchState = function () {
          cfpLoadingBar.start();
          $.post(
            "../backend/application/index.php?rota=/loadCity",
            { hashId: $scope.session.hashId, state: $scope.selected.state },
            function (result) {
              $scope.cities = jQuery.parseJSON(result).dataset;
              cfpLoadingBar.complete();
            }
          );
        };

        init = function () {
          $scope.actual_date = new Date();
          $scope.isTable = true;
          $scope.checkValidRoute();
          $scope.clientDocs = [];
          cfpLoadingBar.start();
          $scope.mail = false;
          $scope.showFilters = false;
          $scope.changeFilds();
          $rootScope.modalOpen = true;
          $scope.newProgress = {};
          $scope.newRegister = false;
          $scope.suggestions = [];
          $scope.filter = {};

          if (
            $scope.main.dealer &&
            ($scope.main.id == 22091 ||
              $scope.main.id == 37362 ||
              $scope.main.id == 25112 ||
              $scope.main.id == 28341)
          ) {
            $scope.filters = [
              { name: "name", label: "Nome", check: true },
              { name: "registrationCode", label: "CPF", check: true },
              { name: "oabNumber", label: "OAB", check: true },
            ];
            $scope.changeFilds();
          }

          $.post(
            "../backend/application/index.php?rota=/loadState",
            $scope.session,
            function (result) {
              $scope.states = jQuery.parseJSON(result).dataset;
            }
          );

          $.post(
            "../backend/application/index.php?rota=/loadTags",
            {},
            function (result) {
              $scope.tags = jQuery.parseJSON(result).dataset;
            }
          );

          $("#partnerState").on("blur", function (obj, datum) {
            cfpLoadingBar.start();
            $.post(
              "../backend/application/index.php?rota=/loadCity",
              { hashId: $scope.session.hashId, state: $scope.selected.state },
              function (result) {
                $scope.citiesPartners = jQuery.parseJSON(result).dataset;
                cfpLoadingBar.complete();
              }
            );
          });

          $scope.loadClient();

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

          if (!$scope.main.dealer) {
            $.post(
              "../backend/application/index.php?rota=/loadClientSuggestions",
              {},
              function (result) {
                $scope.suggestions = jQuery.parseJSON(result).dataset;
              }
            );

            $.post(
              "../backend/application/index.php?rota=/loadClientsNames",
              {},
              function (result) {
                $scope.clientNames = jQuery.parseJSON(result).dataset;
              }
            );
          }

          $.post(
            "../backend/application/index.php?rota=/loadDealers",
            $scope.session,
            function (result) {
              $scope.dealers = jQuery.parseJSON(result).dataset;
              $scope.$digest();
            }
          );
        };

        $scope.openSearchModal = function () {
          $.post(
            "../backend/application/index.php?rota=/loadDealers",
            {},
            function (result) {
              $scope.dealers = jQuery.parseJSON(result).dataset;
              var modalInstance;
              modalInstance = $modal.open({
                templateUrl: "ClientsModalDemoCtrl.html",
                controller: "ClientsModalInstanceCtrl",
                periods: $scope.$parent.periods,
                resolve: {
                  filter: function () {
                    return $scope.filter;
                  },
                  states: function () {
                    return $scope.states;
                  },
                  dealers: function () {
                    return $scope.dealers;
                  },
                },
              });
              modalInstance.result.then(
                function (filter) {
                  filter._fromDate = $rootScope.formatServerDate(
                    filter.fromDate
                  );
                  filter._toDate = $rootScope.formatServerDate(filter.toDate);
                  filter._notFromDate = $rootScope.formatServerDate(
                    filter.notFromDate
                  );
                  filter._notToDate = $rootScope.formatServerDate(
                    filter.notToDate
                  );
                  filter._registerFromDate = $rootScope.formatServerDate(
                    filter.registerFromDate
                  );
                  filter._registerToDate = $rootScope.formatServerDate(
                    filter.registerToDate
                  );
                  $scope.loadClient();
                },
                function () {
                  $log.info("Modal dismissed at: " + new Date());
                }
              );
            }
          );
        };

        $scope.generateHash = function () {
          $.post(
            "../backend/application/index.php?rota=/generateHashClient",
            {},
            function (result) {
              $scope.test = jQuery.parseJSON(result).dataset;
              $scope.span = true;
              $scope.$digest();
            }
          );
        };

        return init();
      },
    ])
    .controller("CupomModalInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "cupom",
      function ($scope, $rootScope, $modalInstance, logger, cupom) {
        $scope.cupom = cupom;

        $scope.ok = function () {
          $scope.cupom.dataInicio = $rootScope.formatServerDate(
            $scope.cupom._dataInicio
          );
          $scope.cupom.dataExpiracao = $rootScope.formatServerDate(
            $scope.cupom._dataExpiracao
          );
          $.post(
            "../backend/application/index.php?rota=/saveCupons",
            { data: $scope.cupom },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $modalInstance.close(true);
            }
          );
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("ClientsModalInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "filter",
      "states",
      "dealers",
      function ($scope, $rootScope, $modalInstance, filter, states, dealers) {
        $scope.states = states;
        $scope.dealers = dealers;
        $scope.clientStatus = [
          "Pendente",
          "Aprovado",
          "Bloqueado",
          "Reprovado",
          "Arquivado",
          "Analise prazo",
          "Pendente Liberacao",
          "Antecipado/Bloqueado",
        ];
        $scope.clientPayments = ["Boleto", "Antecipado"];
        $scope.filter = filter;

        $scope.ok = function () {
          $modalInstance.close($scope.filter);
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("IssuersDataCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "logger",
      function ($scope, $rootScope, $modal, $log, logger) {
        $scope.saveIssuer = function () {
          $.post(
            "../backend/application/index.php?rota=/saveIssuer",
            {
              hashId: $scope.session.hashId,
              data: $scope.issuer_selected,
              client: $scope.$parent.selected,
            },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.$parent.loadIssuers();
            }
          );
        };
        $scope.span = false;
        $scope.open = function () {
          $scope.issuer_selected = angular.copy(this.issuer);
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "IssuersDataCtrl.html",
            controller: "IssuersModalInstanceCtrl",
            periods: $scope.$parent.periods,
            resolve: {
              issuer_selected: function () {
                return $scope.issuer_selected;
              },
            },
          });
          modalInstance.result.then(function (issuer_selected) {
            if (issuer_selected != undefined) {
              $scope.issuer_selected = issuer_selected;
              $scope.saveIssuer();
            } else {
              $scope.$parent.loadIssuers();
            }
          });
        };
      },
    ])
    .controller("IssuersModalInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "issuer_selected",
      function ($scope, $rootScope, $modalInstance, logger, issuer_selected) {
        $scope.issuer_selected = issuer_selected;
        $scope.clientStatus = [
          "Pendente",
          "Aprovado",
          "Bloqueado",
          "Reprovado",
          "Arquivado",
          "Analise prazo",
          "Pendente Liberacao",
        ];

        $scope.ok = function () {
          $modalInstance.close($scope.issuer_selected);
        };
        $scope.remove = function () {
          $.post(
            "../backend/application/index.php?rota=/removeIssuer",
            { hashId: $rootScope.hashId, data: $scope.issuer_selected },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $modalInstance.close(undefined);
            }
          );
        };
        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ])
    .controller("ClientLogModalCtrl", [
      "$scope",
      "$rootScope",
      "$modal",
      "$log",
      "$filter",
      function ($scope, $rootScope, $modal, $log, $filter) {
        $rootScope.modalOpen = false;

        $rootScope.$on("openClientLogModal", function (event, args) {
          event.stopPropagation();
          if ($rootScope.modalOpen == false) {
            $rootScope.modalOpen = true;
            $scope.open(args);
          }
        });

        $scope.open = function (args) {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "ClientLogModalCtrl.html",
            controller: "ClientLogInstanceCtrl",
            resolve: {},
          });
          modalInstance.result.then(
            function (resolve) {
              args.resolveDescription = resolve.resolveDescription;
              $scope.$parent.saveClient();
              $rootScope.modalOpen = false;
            },
            function () {
              $rootScope.modalOpen = false;
            }
          );
        };
      },
    ])
    .controller("ClientLogInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      function ($scope, $rootScope, $modalInstance, logger) {
        $scope.selectedClient = {
          resolveDescription: "",
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };

        $scope.save = function () {
          if ($scope.selectedClient.resolveDescription.length < 1)
            return logger.logError("Motivos devem ser informados");
          $modalInstance.close($scope.selectedClient);
        };
      },
    ])
    .controller("ModalRegistrationUpdateInstanceCtrl", [
      "$scope",
      "$rootScope",
      "$modalInstance",
      "logger",
      "clientRegistrationUpdates",
      "selected",
      function (
        $scope,
        $rootScope,
        $modalInstance,
        logger,
        clientRegistrationUpdates,
        selected
      ) {
        $scope.clientRegistrationUpdates = clientRegistrationUpdates;
        $scope.selected = selected;

        $scope.reject = function (registration) {
          registration.id = $scope.selected.id;
          $.post(
            "../backend/application/index.php?rota=/client/rejectRegistrationUpdate",
            { data: registration },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.cancel();
            }
          );
        };

        $scope.accept = function (registration) {
          registration.id = $scope.selected.id;
          $.post(
            "../backend/application/index.php?rota=/client/acceptRegistrationUpdate",
            { data: registration },
            function (result) {
              logger.logSuccess(jQuery.parseJSON(result).message.text);
              $scope.cancel();
            }
          );
        };

        $scope.cancel = function () {
          $modalInstance.dismiss("cancel");
        };
      },
    ]);
})();
