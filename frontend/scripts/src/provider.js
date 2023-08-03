(function () {
  'use strict';
  angular.module('app.purchase', ['angularFileUpload', 'ui.mask']).controller('ProviderCtrl', [
    '$scope', '$rootScope', '$filter', '$timeout', '$modal', 'cfpLoadingBar', 'logger', 'FileUploader', '$http', function ($scope, $rootScope, $filter, $timeout, $modal, cfpLoadingBar, logger, FileUploader, $http) {
      var init;
      var original;
      var load;
      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.providerStatus = ['Pendente', 'Aprovado', 'Bloqueado', 'Reprovado', 'Desistencia'];
      $scope.providerList = ['CFTUR', 'TURISMO STAR'];
      $scope.searchKeywords = '';
      $scope.filteredProviders = [];
      $scope.row = '';
      $scope.accountType = [
        { type: 'Conta Corrente' },
        { type: 'Conta Poupança' },
        { type: 'Conta Conjunta' }
      ];

      $scope.select = function (page) {
        if (load) {
          $timeout.cancel(load);
        }
        load = $timeout($scope.loadProvider(), 1000);
      };

      $scope.onFilterChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };

      $scope.onNumPerPageChange = function () {
        if (load) {
          $timeout.cancel(load);
        }
        load = $timeout($scope.loadProvider(), 1000);
        // $scope.select(1);
        // return $scope.currentPage = 1;
        // $scope.loadProvider();
      };

      $scope.onOrderChange = function () {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.search = function () {
        if (load) {
          $timeout.cancel(load);
        }
        load = $timeout($scope.loadProvider(), 1000);
        // $scope.filteredProviders = $filter('filter')($scope.providers, $scope.searchKeywords);
        // return $scope.onFilterChange();
      };

      $scope.searchRegistrationCode = function () {
        if ($scope.selected) {
          if ($scope.selected.registrationCode) {
            $.post("../backend/application/index.php?rota=/searchRegistrationCode", { hashId: $scope.session.hashId, data: $scope.selected, partnerType: 'P' }, function (result) {
              if (jQuery.parseJSON(result).message.type == 'S') {
                logger.logError("Cliente ja cadastrado");
              }
            });
          }
        }
      };

      $scope.setSelected = function () {
        var original = angular.copy(this.provider);
        $scope.selected = original;
        $scope.selected.birthdate = new Date($scope.selected.birthdate);
        $scope.selected.birthdate.setDate($scope.selected.birthdate.getDate() + 1);
        $scope.selected.registerDate = new Date($scope.selected.registerDate);
        $scope.selected.registerDate.setDate($scope.selected.registerDate.getDate() + 1);
        $scope.uploader.formData = { hashId: $scope.session.hashId, data: $scope.selected };
        $scope.isTable = false;
        $scope.loadFiles();

        return this.provider;
      };

      $scope.toggleFormTable = function () {
        $scope.isTable = !$scope.isTable;
        return console.log($scope.isTable);
      };

      $scope.order = function (rowName) {
        // if ($scope.row === rowName) {
        //   return;
        // }
        // $scope.row = rowName;
        // $scope.filteredProviders = $filter('orderBy')($scope.providers, rowName);
        // return $scope.onOrderChange();
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadProvider();
      };

      $scope.orderDown = function (rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadProvider();
      };

      $scope.removeFile = function (file) {
        $.post("../backend/application/index.php?rota=/removeFile", { hashId: $scope.session.hashId, data: $scope.selected, file: file }, function (result) {
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadFiles();
        });
      };

      $scope.saveProvider = function () {
        if ($scope.form_provider.$valid) {
          $scope.selected.cityfullname = $scope.selected.city + ', ' + $scope.selected.state;
          if ($scope.main.id != 34699) {
            if (!$scope.noRegistrationCode) {
              if ($scope.selected.registrationCode.length <= 11) {
                if (!$rootScope.ValidaCPF($scope.selected.registrationCode)) {
                  return logger.logError('CPF Inválido');
                }
              } else {
                if (!$rootScope.ValidaCNPJ($scope.selected.registrationCode)) {
                  return logger.logError('CNPJ Inválido');
                }
              }
            }

          }
          if ($scope.selected.birthdate !== "" && $scope.selected.birthdate != 'Invalid Date') {
            $scope.selected._birthdate = $rootScope.formatServerDate($scope.selected.birthdate);
          }
          if ($scope.selected.registerDate !== "" && $scope.selected.registerDate != 'Invalid Date') {
            $scope.selected._registerDate = $rootScope.formatServerDate($scope.selected.registerDate);
          }
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/saveProvider", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            cfpLoadingBar.complete();
            if ($scope.selected.status == "Reprovado") {
              $scope.fillEmail();
            } else {
              $scope.cancelEdit();
            }
          });
        }
      };

      $scope.fillEmail = function () {

        $scope.webemail.emailpartner = $scope.selected.email;
        $scope.webemail.mailcc = '';
        $scope.webemail.subject = '[One Milhas] - Analise de Solicitação';

        $scope.webemail.emailContent = "Olá!<br><br>" +
          "Para nós, identificar o seu interesse em negociar com a One Milhas foi muito importante. <br><br>" +
          "Porém, analisando a sua solicitação, verificamos que algumas informações não são compatíveis com os critérios internos da One Milhas.<br><br>" +
          "Por esse motivo, infelizmente, não podemos dar continuidade ao processo de aprovação.<br><br>" +
          "Agradecemos o seu contato.<br><br>" +
          "Att.";

        $scope.mail = true;
        $scope.$apply();
      };

      $scope.providerTag = function (status) {
        switch (status) {
          case 'Pendente':
            return "label label-info";
          case 'Aprovado':
            return "label label-success";
          case 'Desistencia':
            return "label label-default";
          case 'Bloqueado':
            return "label label-warning";
          case 'Reprovado':
            return "label label-danger";
        }
      };

      $scope.cancelEdit = function () {
        $scope.loadProvider();
        $scope.isTable = !$scope.isTable;
        $scope.newUpload = false;
        $scope.mail = false;
      };

      $scope.mailOrder = function () {
        $scope.uploader.uploadAll();
        var file;
        if ($scope.uploader.queue && $scope.uploader.queue[$scope.uploader.queue.length - 1]) {
          file = $scope.uploader.queue[$scope.uploader.queue.length - 1].file.name;
        }
        $scope.cancelEdit();
        $.post("../backend/application/index.php?rota=/mailOrder", { hashId: $scope.session.hashId, data: $scope.webemail, attachment: file, type: 'COMPRAS', signiture: 'signiture', emailType: 'EMAIL-GMAIL'}, function (result) {
          if (jQuery.parseJSON(result).message.type == 'S') {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          } else {
            logger.logError(jQuery.parseJSON(result).message.text);
          }
        });
      };

      $scope.openSearchProvider = function () {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "ProviderModalCtrl.html",
          controller: 'ProviderModalCtrl',
          resolve: {
            filter: function () {
              return $scope.filter;
            }
          }
        });

        modalInstance.result.then(function (filter) {
          $scope.filter = filter;
          $scope.loadProvider();
        });
      }

      $scope.newProvider = function () {
        $scope.selected = {};
        $scope.selected.type = 'P';
        $scope.selected.registerDate = new Date();
        $scope.isTable = !$scope.isTable;
      };

      $scope.loadProvider = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadProvider", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function (result) {
          $scope.providers = jQuery.parseJSON(result).dataset.providers;
          $scope.totalData = jQuery.parseJSON(result).dataset.total;
          $scope.$digest();
          cfpLoadingBar.complete();
          if (load) {
            load = undefined;
          }
          // $scope.search();
          // return $scope.select($scope.currentPage);
        });
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
        $.post("../backend/application/index.php?rota=/loadFiles", { hashId: $scope.session.hashId, data: $scope.selected }, function (result) {
          $scope.filesLoaded = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.changeRegistrationCode = function () {
        $scope.noRegistrationCode = !$scope.noRegistrationCode;
      };

      $scope.validateData = function () {
        $.post("../backend/application/index.php?rota=/validadteProviderProfile", { data: $scope.selected }, function (result) {
          if (jQuery.parseJSON(result).message.type == 'S') {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          } else {
            logger.logError(jQuery.parseJSON(result).message.text);
          }
        });
      };

      $scope.printProvider = function () {
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

      $scope.getCEP = function () {
        $.post("../backend/application/index.php?rota=/getCep", { data: $scope.selected }, function (result) {
          $scope.searchCep = jQuery.parseJSON(result).dataset;
          if ($scope.searchCep.status == 'success') {
            $scope.selected.adress = $scope.searchCep.data.logradouro;
            $scope.selected.adressDistrict = $scope.searchCep.data.bairro;
            $scope.selected.state = $scope.searchCep.data.uf;
            $scope.selected.city = $scope.searchCep.data.localidade;
          } else {
            logger.logError('Erro na busca pelo CEP');
          }
          $scope.$digest();

        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[0];
      $scope.currentPage = 1;
      $scope.currentPageProviders = [];
      init = function () {
        $scope.isTable = true;
        $scope.newUpload = false;
        $scope.newFile = undefined;
        $scope.mail = false;
        $scope.noRegistrationCode = false;
        $scope.filter = {
          findSRM: false
        };
        $scope.checkValidRoute();
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadState", $scope.session, function (result) {
          $scope.states = jQuery.parseJSON(result).dataset;
        });

        $('#providerstate').on('blur', function (obj, datum) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadCity", { hashId: $scope.session.hashId, state: $scope.selected.state }, function (result) {
            $scope.cities = jQuery.parseJSON(result).dataset;
          });
          cfpLoadingBar.complete();
        });
        $scope.loadProvider();

        $scope.uploader = new FileUploader();
        $scope.uploader.url = "../backend/application/index.php?rota=/saveFile";
        $scope.uploader.filters.push({
          name: 'customFilter',
          fn: function (item /*{File|FileLikeObject}*/, options) {
            return this.queue.length < 10;
          }
        });

      };
      return init();
    }
  ]).controller('ProviderModalCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function ($scope, $rootScope, $modalInstance, filter) {
      $scope.filter = filter;

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };

      $scope.ok = function () {
        $modalInstance.close($scope.filter);
      };
    }
  ]).controller('ModalDemoCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function ($scope, $rootScope, $modal, $log, $filter) {

      $scope.open = function (size) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "ModalDemoCtrl.html",
          controller: 'ModalPermissionInstanceCtrl',
          size: size,
          resolve: {
            selected: function () {
              return $scope.selected;
            }
          }
        });
        modalInstance.result.then(function () {

        });
      };

    }
  ]).controller('ModalPermissionInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'selected', function ($scope, $rootScope, $modalInstance, logger, selected) {

      $scope.application = angular.copy(selected);

      $scope.formatNumberMoney = function (milesDueDate, costPerThousand) {
        $scope.application.multipleSelect.totalCost = (milesDueDate * milesDueDate) / 100;
        return $scope.application.multipleSelect.totalCost;
      };

      $scope.ok = function () {
        alert("You clicked the ok button.");
        $uibModalInstance.close();
      };

      $scope.cancel = function () {
        alert("You clicked the cancel button.");
        $uibModalInstance.dismiss('cancel');
      };
      /*$scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };*/

      $scope.printBankStatement = function () {

        var doc = new jsPDF('p', 'pt');
        doc.margin = 0.5;
        doc.setFont("times");

        $scope.toDataUrl('images/bank.jpg', function (base64Img) {
          $scope.toDataUrl('images/tamprint.png', function (base32Img) {
            $scope.toDataUrl('images/smilesprint.png', function (base16Img) {
              $scope.toDataUrl('images/azulprint.png', function (base8Img) {
                $scope.toDataUrl('images/aviancaprint.png', function (base4Img) {

                  doc.addImage(base64Img, 'JPEG', 220, 20, 110, 40);

                  var start = 70;

                  start = start + 20;

                  var images = [];
                  var i = 0;


                  var columns = [
                    { title: " ", dataKey: "first" },
                    { title: "OFERTA DE PONTOS", dataKey: "second" },
                    { title: "VENCIMENTO", dataKey: "third" },
                    { title: "VALOR 1000 Pts", dataKey: "four" },
                    { title: "CONFIRMAÇÃO", dataKey: "five" }
                  ];
                  var rows = [];

                  var miles = ' '; var due_date = ' '; var costPerThousand = ' '; var totalCost = ' ';
                  var airline = ['LATAM', 'GOL', 'AZUL', 'AVIANCA'];

                  for (var i = 0; i < 4; i++) {

                    if (airline[i] == $scope.application.multipleSelect[0].toUpperCase()) {
                      miles = $scope.application.multipleSelect['offer'];
                      due_date = $scope.application.multipleSelect['milesDueDate'];

                      costPerThousand = $scope.application.multipleSelect['costPerThousand'];
                      totalCost = $scope.application.multipleSelect['totalCost'];

                      rows.push({
                        first: airline[i],
                        second: miles,
                        third: $scope.dateFormat(new Date(due_date)),
                        four: costPerThousand,
                        five: totalCost
                      });                                        
                    }                                     
                    miles = ' ', due_date = ' ', costPerThousand = ' ', totalCost = ' ';
                  }

                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                    },
                    margin: { left: 60 },
                    startY: start,
                    pageBreak: 'auto',
                    theme: 'grid',
                    showHeader: 'firstPage',
                    drawCell: function (cell, opts) {
                    },
                    addPageContent: function (data) {
                    }
                  });
                  start = start + 120;



                  var columns = [
                    { title: "DADOS CADASTRAIS", dataKey: "first" },
                    { title: " ", dataKey: "second" },
                    { title: " ", dataKey: "third" }
                  ];
                  var rows = [];

                  for (var i = 0; i < 8; i++) {

                    if (i == 0) {
                      rows.push({
                        first: $scope.application.name
                      });
                    } else if (i == 1) {
                      rows.push({
                        first: 'CPF: ' + $scope.application.registrationCode
                      });
                    } else if (i == 2) {
                      rows.push({
                        first: 'Data de Nascimento: ' + $scope.dateFormat(new Date($scope.application.birthdate)),
                        second: 'Profissão: ' + $scope.application.company_name
                      });
                    } else if (i == 3) {
                      rows.push({
                        first: 'Endereço: ' + $scope.application.adress,
                      });
                    } else if (i == 4) {
                      rows.push({
                        first: 'Cidade: ' + $scope.application.city,
                        second: 'Estado: ' + $scope.application.adressDistrict
                      });
                    } else if (i == 5) {
                      rows.push({
                        first: 'Tel.Fixo: ' + $scope.application.phoneNumber,
                        second: 'Tel.Cel: ' + $scope.application.phoneNumber2,
                        third: 'Tel.Comercial: ' + $scope.application.phoneNumber2
                      });
                    }
                    else if (i == 6) {
                      rows.push({
                        first: 'Email: ' + $scope.application.email
                      });
                    }
                  }

                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                    },
                    startY: start,
                    margin: { left: 60 },
                    theme: 'grid',
                    pageBreak: 'auto',
                    showHeader: 'firstPage',
                    addPageContent: function (data) {
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "ANÁLISE DE CRÉDITO", dataKey: "first" },
                    { title: "                  ", dataKey: "second" }
                  ];
                  var rows = [];

                  rows.push({
                    first: 'Score de Crédito',
                    second:$scope.application.creditAnalysis
                  });

                  start = start + 50;
                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                    },
                    startY: start,
                    margin: { left: 60, right: 100 },
                    theme: 'grid',
                    showHeader: 'firstPage',
                    createdCell: function (cell, data) {
                      if (data.column.dataKey === 'right') {
                        cell.styles.halign = 'right';
                      }
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "", dataKey: "first" },
                    { title: "SIM", dataKey: "second" },
                    { title: "NÃO", dataKey: "third" }
                  ];
                  var rows = [];

                  var letter = ['CPF Regular?', 'Endereço Confere Cadastro?', 'Cliente Recorrente?', 'Mesma Companhia', 'Registro de Débitos?']

                  for (var i = 0; i < letter.length; i++) {

                    let second; let third;
                    if (i == 0) {

                      if ($scope.application.registrationCodeCheck == 'Sim') {
                        second = 'x';
                      } else {
                        third = 'x';
                      }
                    } else if (i == 1) {

                      if ($scope.application.adressCheck == 'Sim') {
                        second = 'x'
                      } else {
                        third = 'x';
                      }
                    } else if (i == 2) {

                      if ($scope.application.clientCheck == 'Sim') {
                        second = 'x'
                      } else {
                        third = 'x';
                      }
                    } else if (i == 3) {

                      if ($scope.application.airlineCheck == 'Sim') {
                        second = 'x'
                      } else {
                        third = 'x';
                      }
                    } else if (i = 4) {

                      if ($scope.application.debitCheck == 'Sim') {
                        second = 'x'
                      } else {
                        third = 'x';
                      }
                    }

                    rows.push({
                      first: letter[i],
                      second: second,
                      third: third
                    });
                  }

                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                    },
                    startY: start,
                    margin: { left: 60, right: 100 },
                    theme: 'grid',
                    showHeader: 'firstPage',
                    createdCell: function (cell, data) {
                      if (data.column.dataKey === 'right') {
                        cell.styles.halign = 'right';
                      }
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;


                  var columns = [
                    { title: "DADOS DO CARTÃO", dataKey: "first" }
                  ];
                  var rows = [];

                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                    },
                    startY: start,
                    margin: { left: 60, right: 100 },
                    theme: 'grid',
                    showHeader: 'firstPage',
                    createdCell: function (cell, data) {
                      if (data.column.dataKey === 'right') {
                        cell.styles.halign = 'right';
                      }
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "TAM", dataKey: "first" },
                    { title: "SMILES", dataKey: "second" },
                    { title: "AZUL", dataKey: "third" },
                    { title: "AVIANCA", dataKey: "four" }
                  ];

                  var rows = [];


                  var airline = ['LATAM', 'GOL', 'AZUL', 'AVIANCA'];
                  let card = ''; let password;
                  let cardAvianca = ' ', passwordAvianca = ' ';
                  let cardLatam = ' ', passwordLatam = ' ', passwordRecovery = ' ';
                  let cardAzul = ' ', passwordAzul = ' ';
                  let cardGol = ' ', passwordGol = ' ';

                  for (var i = 0; i < 3; i++) {

                    if ($scope.application.multipleSelect) {
                      if ($scope.application.multipleSelect == 'latam') {
                        cardLatam = $scope.application.latam.loyaltyLatam;
                        passwordLatam = $scope.application.latam.passwordMultiplus;
                        passwordRecovery = $scope.application.latam.passwordRecovery;
                      } else if ($scope.application.multipleSelect == 'smiles') {
                        cardGol = $scope.application.gol.cardNumber;
                        passwordGol = $scope.application.gol.password;
                      } else if ($scope.application.multipleSelect == 'azul') {
                        cardAzul = $scope.application.azul.cardNumber;
                        passwordAzul = $scope.application.azul.password;
                      } else if ($scope.application.multipleSelect == 'avianca') {
                        cardAvianca = $scope.application.avianca.cardNumber;
                        passwordAvianca = $scope.application.avianca.password;
                      }
                    }
                   
                    if (i == 0) {
                      rows.push({
                        first: 'FIDELIDADE: ' + cardLatam,
                        second: 'Nº.Cartão: ' + cardGol,
                        third: 'Nº.Cartão: ' + cardAzul,
                        four: 'Nº.Cartão: ' + cardAvianca
                      });
                    } else if (i == 1) {
                      rows.push({
                        first: 'SENHA MÚLTIPLAS: ' + passwordLatam,
                        second: 'Senha: ' + passwordGol,
                        third: 'Senha: ' + passwordAzul,
                        four: 'Senha: ' + passwordAvianca
                      });
                    } else {
                      rows.push({
                        first: 'SENHA RESGATE: '+ passwordRecovery,
                        second: '',
                        third: '',
                        four: ''
                      });
                    }
                  }


                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                      lineColor: 255,
                    },
                    startY: start,
                    margin: { left: 60, right: 100 },
                    theme: 'grid',
                    showHeader: 'firstPage',
                    createdCell: function (cell, data) {
                      if (data.column.dataKey === 'right') {
                        cell.styles.halign = 'right';
                      }
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "           ", dataKey: "first" },
                    { title: "Confirmação", dataKey: "second" }
                  ];

                  var rows = [];

                  var a = ["1.Nome Completo ? ", "2.Filiação ? ", "3.CPF ", "4.Idade ", "5.Data de Nascimento? ", "6.Endereço informado na Ficha ? \n Residencial ? ",
                    "7.Confirmação Endereços Antigos. ", "8.Possui empresas por nome ", "9.Confirmação em Telefone fixo ? "];

                  for (var y = 0; y < a.length; y++) {
                    let check = ' ';
                    if ($scope.application.checkbox.fullNameCheck && y == 0) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.affiliationCheck && y == 1) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.registerCheck && y == 2) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.ageCheck && y == 3) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.birthCheck && y == 4) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.recordAddressCheck && y == 5) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.addressOldCheck && y == 6) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.companyNameCheck && y == 7) {
                      check = 'Sim';
                    } else if ($scope.application.checkbox.phoneCheck && y == 8) {
                      check = 'Sim';
                    }

                    rows.push({
                      first: a[y],
                      second: check
                    });
                  }

                  doc.autoTable(columns, rows, {
                    headerStyles: {
                      fillColor: [44, 74, 175],
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                      textColor: 255
                    },
                    margin: { left: 60 },
                    startY: start,
                    theme: 'plain',
                    showHeader: 'firstPage',
                    addPageContent: function (data) {
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "Observação - Questionário Confirmação", dataKey: "first" }
                  ];

                  var rows = [];

                  rows.push({
                    first: $scope.application.checkbox.observation
                  });


                  doc.autoTable(columns, rows, {
                    headerStyles: {
                      fillColor: [44, 74, 175],
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                      textColor: 255
                    },
                    margin: { left: 60 },
                    startY: start,
                    theme: 'plain',
                    showHeader: 'firstPage',
                    addPageContent: function (data) {
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "ONE MILHAS ", dataKey: "first" },
                    { title: " ", dataKey: "second" }
                  ];

                  var rows = [];

                  var b = ["Data de Cotação ", "Data de Ligação ", "Ficha Aprovada ", "Responsável Análise ", "Responsável Aprovação"];

                  var first; var second; var third;
                  for (var y = 0; y < b.length; y++) {
                    first = b[y];

                    if (y == 0) {
                      second = $scope.dateFormat(new Date($scope.application.date_quote));
                    } else if (y == 1) {
                      second = $scope.dateFormat(new Date($scope.application.connection_date))
                    } else if (y == 2) {
                      if ($scope.application.approvedCard) {
                        second = 'Sim'
                      } else {
                        second = 'Não'
                      }
                    } else if (y == 3) {
                      second = $scope.application.responsible_analyst;
                    } else if (y == 4) {
                      second = $scope.application.responsible_approval
                    }

                    rows.push({
                      first: first,
                      second: second
                    });
                  }

                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                      textColor: 255
                    },
                    margin: { left: 60 },
                    startY: start,
                    theme: 'grid',
                    showHeader: 'firstPage',
                    addPageContent: function (data) {
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "PAGAMENTO ", dataKey: "first" },
                    { title: "              ", dataKey: "second" }
                  ];

                  var rows = [];

                  var c = ["BANCO ", "COD.", "AGÊNCIA", "OP.", "CONTA", "TITULAR"];
                  var second;
                  for (var i = 0; i < c.length; i++) {

                    if (i == 0) {
                      if ($scope.application.bank) {
                        second = $scope.application.bank
                      } else {
                        second = "____________________________"
                      }
                    } else if (i == 1) {
                      if ($scope.application.code) {
                        second = $scope.application.code
                      } else {
                        second = "____________________________"
                      }
                    } else if (i == 2) {
                      if ($scope.application.agency) {
                        second = $scope.application.agency
                      } else {
                        second = "____________________________"
                      }
                    } else if (i == 3) {
                      if ($scope.application.op) {
                        second = $scope.application.op
                      } else {
                        second = "____________________________"
                      }
                    } else if (i == 4) {
                      if ($scope.application.account) {
                        second = $scope.application.account
                      } else {
                        second = "____________________________"
                      }
                    } else if (i == 5) {
                      if ($scope.application.holder) {
                        second = $scope.application.holder
                      } else {
                        second = "____________________________"
                      }
                    }

                    rows.push({
                      first: c[i],
                      second: second
                    });
                  }

                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                      textColor: 255
                    },
                    showHeader: 'firstPage',
                    margin: { left: 60 },
                    startY: start,
                    theme: 'plain',
                    addPageContent: function (data) {
                    }
                  });

                  start = doc.autoTableEndPosY();
                  start = start + 50;

                  var columns = [
                    { title: "ANDAMENTO ", dataKey: "first" }
                    //,{ title: " ", dataKey: "second" }
                  ];

                  var rows = [];

                  var n = ['1', '2', '3', '4', '5', '6'];

                  rows.push({
                    first: $scope.application.progress
                  });

                  /*for (var i = 0; i < c.length; i++) {
                    rows.push({
                      first: n[i],
                      second: "____________________________"
                    });
                  }*/

                  doc.autoTable(columns, rows, {
                    columnStyles: {
                      title: { fillColor: [0, 0, 255] }
                    },
                    headerStyles: {
                      fillColor: [44, 74, 175],
                      textColor: 255
                    },
                    margin: { left: 60 },
                    startY: start,
                    theme: 'plain',
                    showHeader: 'firstPage',
                    addPageContent: function (data) {
                    }
                  });

                  doc.save('FichaDeIncricaoBank' + '.pdf');
                });
              });
            });
          });
        });
      };

      $scope.dateFormat = function (date, format) {
        return  $rootScope.pad(date.getDate()) + "/" + $rootScope.pad((date.getMonth() + 1)) + "/" + date.getFullYear();
      };


      $scope.pad = function (n, width, z) {
        if (width == undefined) {
          width = 2;
        }
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
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
    }
  ]);
})();