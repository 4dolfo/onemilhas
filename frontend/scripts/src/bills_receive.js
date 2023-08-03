(function () {
  'use strict';
  angular.module('app.table').controller('BillsReceiveCtrl', [
    '$scope', '$filter', function($scope, $filter) {
      var init;
      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.billsreceive = [
        {
            id: 41,
            status: "Em Aberto",
            client: "Roberto Augusto Lopes",
            email: "robertoalmartins@yahoo.com.br",
            phoneNumber: "(86)89899009",
            billType: "Compra de Milhas",
            description: "10.000 milhas TAM - Fidelidade 45889988",
            due_date: "31/08/2015",
            value: "290,00"
        },
        {
            id: 41,
            status: "Em Aberto",
            client: "Carlos Augusto dos Santos Sampaio",
            email: "cass@gmail.com",
            phoneNumber: "(86)89898788",
            billType: "Compra de Milhas",
            description: "20.000 milhas GOL - Fidelidade 5435444",
            due_date: "31/08/2015",
            value: "580,00"
        },
        {
            id: 41,
            status: "Em Aberto",
            client: "Gustavo José dos Reis",
            email: "gjr@yahoo.com.br",
            phoneNumber: "(31)87889009",
            billType: "Compra de Milhas",
            description: "30.000 milhas TAM - Fidelidade 45889988",
            due_date: "31/08/2015",
            value: "870,00"
        },
                {
            id: 41,
            status: "Em Aberto",
            client: "Roberto Augusto Lopes",
            email: "robertoalmartins@yahoo.com.br",
            phoneNumber: "(86)89899009",
            billType: "Compra de Milhas",
            description: "10.000 milhas TAM - Fidelidade 45889988",
            due_date: "31/08/2015",
            value: "290,00"
        },
        {
            id: 41,
            status: "Em Aberto",
            client: "Carlos Augusto dos Santos Sampaio",
            email: "cass@gmail.com",
            phoneNumber: "(86)89898788",
            billType: "Compra de Milhas",
            description: "20.000 milhas GOL - Fidelidade 5435444",
            due_date: "31/08/2015",
            value: "580,00"
        },
        {
            id: 41,
            status: "Em Aberto",
            client: "Gustavo José dos Reis",
            email: "gjr@yahoo.com.br",
            phoneNumber: "(31)87889009",
            billType: "Compra de Milhas",
            description: "30.000 milhas TAM - Fidelidade 45889988",
            due_date: "31/08/2015",
            value: "870,00"
        }
      ];
      $scope.searchKeywords = '';
      $scope.filteredBillsReceive = [];
      $scope.row = '';
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageBillsReceive = $scope.filteredBillsReceive.slice(start, end);
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
      $scope.setSelected = function() {
        $scope.selected = this.billreceive;
        $scope.isTable = false;
        return $scope.selected;
      };
      $scope.toggleFormTable = function() {
        $scope.isTable = !$scope.isTable;
        return $scope.isTable;
      };
      $scope.search = function() {
        $scope.filteredBillsReceive = $filter('filter')($scope.billsreceive, $scope.searchKeywords);
        return $scope.onFilterChange();
      };
      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredBillsReceive = $filter('orderBy')($scope.billsreceive, rowName);
        return $scope.onOrderChange();
      };
      $scope.numPerPageOpt = [3, 5, 10, 20];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageBillsReceive = [];
      init = function() {
//        $scope.checkValidRoute();
        $scope.search();
        $scope.isTable = true;
        return $scope.select($scope.currentPage);
      };
      return init();
    }
  ]);
})(); 
;
