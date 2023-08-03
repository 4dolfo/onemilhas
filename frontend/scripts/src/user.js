(function () {
  'use strict';
  angular.module('app.purchase').controller('UserCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', '$modal', function($scope, $rootScope, $filter, cfpLoadingBar, logger, $modal) {
      var init;
      var original;
      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.userStatus = ['Pendente', 'Aprovado', 'Bloqueado', 'Reprovado'];
      $scope.searchKeywords = '';
      $scope.filteredUsers = [];
      $scope.row = '';
      $scope.days = 0;
      $scope.ocultar_bloqueados = true;
      $scope.loginsHistoric = [];
      $scope.loginsHistoricCrop = 25;
      $scope.isOnlyUser = false;

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageUsers = $scope.filteredUsers.slice(start, end);
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

      $scope.filtra_bloqueados = function(){
        $scope.users = $scope.users_historico;
        if($scope.ocultar_bloqueados){
          $scope.users = $scope.users_historico.filter(function(Row){
              return !Row.bloqued;
          });
        }
        $scope.search();
      }

      $scope.search = function() {
        $scope.filteredUsers = $filter('filter')($scope.users, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        original = angular.copy(this.user);
        $scope.selected = this.user;
        $scope.isTable = false;
        return console.log(this.user);
      };

      $scope.newUser = function() {
        $scope.selected = {};
        $scope.selected.type = 'U';
        $scope.isTable = false;
      };

      $scope.toggleFormTable = function() {
        $scope.isTable = !$scope.isTable;
        return console.log($scope.isTable);
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredUsers = $filter('orderBy')($scope.users, rowName);
        return $scope.onOrderChange();
      };

      $scope.saveUser = function() {
        if ($scope.form_user.$valid) {
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

          cfpLoadingBar.start();
          if($scope.selected.bloqued) {
            $scope.selected.status = 'Bloqueado';
          } else {
            $scope.selected.status = 'Aprovado';
          }
          if($scope.selected.dealer) {
            $scope.selected.type = 'U_D';
            $scope.selected.partnerType = 'U_D';
          }
          $.post("../backend/application/index.php?rota=/saveProfile", { data: $scope.selected }, function(result){
            $scope.users.push($scope.selected);
            $scope.$apply();
            $scope.isTable = !$scope.isTable;
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          });
          cfpLoadingBar.complete();
        }
      };

      $scope.blockuser = function(user){
        if(!user.bloqued) {
          user.status = 'Bloqueado';
        } else {
          user.status = 'Aprovado';
        }
        $.post("../backend/application/index.php?rota=/saveProfile", {hashId: $scope.session.hashId, data: user}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadProfile();
        });
      }

      $scope.cancelEdit = function() {
        $scope.loadProfile();
        $scope.isTable = !$scope.isTable;
      };

      window.$scope = $scope;

      $scope.loadLoginHistoric = function(id, openModal, savePDF) {
        $scope.isOnlyUser = false;
        if(id > 0){
          $scope.isOnlyUser = true;
        }
        $.post("../backend/application/index.php?rota=/loadHistoric", {hashId: $scope.session.hashId, data: id, type: "LOGIN", days: $scope.days}, function(result){
          $scope.loginsHistoricOriginal = jQuery.parseJSON(result).dataset;
          
          for(let i=0; i<$scope.loginsHistoricOriginal.length; i++){
            var spl = $scope.loginsHistoricOriginal[i].description.split(":");
            if(spl[1])
              $scope.loginsHistoricOriginal[i].ip = spl[1];
            else
              $scope.loginsHistoricOriginal[i].ip = "";
            $scope.loginsHistoricOriginal[i].description = spl[0].split(".")[0];
          }
          
          $scope.loginsHistoric = $scope.loginsHistoricOriginal.filter((row,index) => index < $scope.loginsHistoricCrop);

          $scope.$apply();

          if(openModal){
            $modal.open({
              size: "lg",
              templateUrl: "login.html",
              controller: "loginModalInstanceCtrl",
              scope: $scope
            });
          }

          if(savePDF == 1){
            $scope.printLogs(false, id);
          }
          else if(savePDF == 2){
            $scope.printLogs(true, id);
          }
        });
      }

      $scope.printLogs = function(todos, id) {
        var columns = [
          { title: "Nome", dataKey: "name" },
          { title: "IP", dataKey: "ip" },
          { title: "Data e Horário", dataKey: "date" },
          { title: "Descrição", dataKey: "description" }
        ];

        var rows = [];

        if(todos){
          for (let i = 0; i < $scope.loginsHistoricOriginal.length; i++) {
            rows.push({
              name: $scope.loginsHistoricOriginal[i].userName,
              ip: $scope.loginsHistoricOriginal[i].ip,
              date: $scope.loginsHistoricOriginal[i].issue_date,
              description: $scope.loginsHistoricOriginal[i].description
            });
          }
        }
        else{
          for (let i = 0; i < $scope.loginsHistoric.length; i++) {
            rows.push({
              name: $scope.loginsHistoric[i].userName,
              ip: $scope.loginsHistoric[i].ip,
              date: $scope.loginsHistoric[i].issue_date,
              description: $scope.loginsHistoric[i].description
            });
          }
        }

        var doc = new jsPDF("p", "pt");
        doc.margin = 0.5;
        doc.setFontSize(18);
        doc.autoTable(columns, rows, {
          theme: "grid",
          styles: {
            fontSize: 8,
            overflow: "linebreak",
          },
          startY: 90,
          margin: { horizontal: 10 },
          bodyStyles: { valign: "top" },
        });

        let arq = "Relatório_Logs";
        if(id >= 0)
          arq += "_" + id + "_";
        if(todos)
          arq += "(todos)";

        arq += ".pdf";
        doc.save(arq);
      }

      $scope.loadProfile = function() {
        $.post("../backend/application/index.php?rota=/loadProfile", $scope.session, function(result){
            $scope.users = jQuery.parseJSON(result).dataset;
            $scope.users_historico = jQuery.parseJSON(result).dataset;
            $scope.filtra_bloqueados();
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.loadProfileOrdered = function() {
        $.post("../backend/application/index.php?rota=/loadProfile", $scope.session, function(result){
            $scope.users = jQuery.parseJSON(result).dataset;
            $scope.users_historico = jQuery.parseJSON(result).dataset;
            $scope.filtra_bloqueados();
            $scope.order('name');
            cfpLoadingBar.complete();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageUsers = [];
      init = function() {
        $scope.checkValidRoute();
        $scope.isTable = true;
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadState", $scope.session, function(result){
            $scope.states = jQuery.parseJSON(result).dataset;
        });

        $('#userstate').on('blur', function(obj, datum) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadCity", {hashId: $scope.session.hashId, state: $scope.selected.state}, function(result){
            $scope.cities = jQuery.parseJSON(result).dataset;
          });
          cfpLoadingBar.complete();
        });
        $scope.loadProfileOrdered();
      };
      return init();
    }
  ])

  .controller("loginModalInstanceCtrl", [
    "$scope",
    "$rootScope",
    "$modalInstance",
    function ($scope, $rootScope, $modalInstance) {
      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    },
  ]);
})();

;