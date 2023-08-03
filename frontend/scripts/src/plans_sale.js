(function () {
  'use strict';
  angular.module('app.plans_sale', ['angularFileUpload', 'ui.utils.masks']).controller('PlansSaleCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'FileUploader', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, FileUploader, logger) {
      var init;
      var original;
      window.PLAN_SALE_SCOPE = $scope;

      $scope.userStatus = ['Aprovado', 'Bloqueado'];
      $scope.searchKeywords = '';
      $scope.filteredPlans = [];
      $scope.row = '';

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPagePlans = $scope.filteredPlans.slice(start, end);
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

      $scope.search = function() {
        $scope.filteredPlans = $filter('filter')($scope.salePlans, $scope.searchKeywords);
        if($scope.ocultar_bloqueados)
          $scope.filteredPlans = $scope.filteredPlans.filter(function(Row){
            return(Row.sistemaDisp) ;
          });
        return $scope.onFilterChange();
      };

      $scope.$watch('ocultar_bloqueados', function() {
        $scope.search();
      });

      $scope.togglePlan = function(plan){
        if(!plan.sistemaDisp) {
          plan.sistemaDisp = true;
          plan.sistemaDispStr = 'NÃO';
        } else {
          plan.sistemaDisp = false;
          plan.sistemaDispStr = 'SIM';
        }
        $scope.search();
      }

      $scope.setSelected = function() {
        $scope.selected = this.plan;
        $.post("../backend/application/index.php?rota=/loadPlansControl", {data: $scope.selected}, function(result){
          $scope.profilePlan = jQuery.parseJSON(result).dataset.profile;
          $scope.controlAirline = jQuery.parseJSON(result).dataset.airlines;
          $scope.profilePlan.clients = [];
          $scope.uploader.formData = {data: $scope.selected};
          $scope.tabindex = 1;
          $scope.loadClientsPlan();
          $scope.$apply();
        });
      };

      $scope.loadClientsPlan = function() {
        $.post("../backend/application/index.php?rota=/SalePlans/loadClients", {data: $scope.selected}, function(result){
          $scope.profilePlan.clients = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.removeClient = function(id) {
        $.post("../backend/application/index.php?rota=/SalePlans/removeClient", {data: id}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadClientsPlan();
        });
      };

      $scope.newPlan = function() {
        $scope.selected = {};
        $scope.tabindex = 1;
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
        $scope.filteredPlans = $filter('orderBy')($scope.salePlans, rowName);
        return $scope.onOrderChange();
      };

      $scope.savePlan = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/saveSalePlan", {data: $scope.selected, control: $scope.controlAirline, profile: $scope.profilePlan}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadPlans();
        });
      };

      $scope.cancelEdit = function() {
        $scope.loadPlans();
        $scope.tabindex = 0;
      };

      $scope.loadPlans = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadSalePlans", $scope.session, function(result){
          $scope.salePlans = jQuery.parseJSON(result).dataset;
          $scope.search();
          $scope.tabindex = 0;
          cfpLoadingBar.complete();
        });
      };

      $scope.moveCLients = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/SalePlans/moveCLients", {data: $scope.selected, newPlan: $scope.newPlan}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadPlans();
        });
      };

      $scope.addAllClients = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/SalePlans/addAllClients", {data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadPlans();
        });
      };

      $scope.removeAllClients = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/SalePlans/removeAllClients", {data: $scope.selected}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadPlans();
        });
      };

      $scope.removeSlide = function(id) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/SalePlans/removeSlide", {data: id}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadPlans();
        });
      };

      $scope.openModalSelection = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "app/modals/modal_clients_selection.html",
          controller: 'ModalClientsSelectionCtrl',
          size: 'lg',
          resolve: {
            clients: function() {
              return $scope.profilePlan.clients;
            },
          }
        });

        modalInstance.result.then(function(clients) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/SalePlans/addClients", { clients: clients, data: $scope.selected }, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            cfpLoadingBar.complete();
            $scope.loadClientsPlan();
          });
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[1];
      $scope.currentPage = 1;
      $scope.currentPagePlans = [];

      init = function() {
        $scope.tabindex = 0;
        $scope.controlAirline = [];
        // $scope.newPlan = undefined;

        $scope.checkValidRoute();
        $scope.loadPlans();

        $.post("../backend/application/index.php?rota=/loadConfiancaUsers", {}, function(result){
          $scope.users = jQuery.parseJSON(result).dataset;
        });

        $scope.uploader = new FileUploader();
        $scope.uploader.url = "../backend/application/index.php?rota=/SalePlans/saveFilePlan";
        $scope.uploader.filters.push({
            name: 'customFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 10;
            }
        });
        $scope.uploader.onCompleteItem = function(fileItem, response, status, headers) {
          console.log(response);
          $scope.profilePlan.slides = response.dataset;
          $scope.$digest();
        };

      };

      return init();
    }
  ]);
})();
;