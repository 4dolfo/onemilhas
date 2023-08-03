(function () {
  'use strict';
  angular.module('app.sale').controller('DealersOrdersCtrl', [
    '$scope', '$rootScope', '$filter', '$timeout', '$modal', 'cfpLoadingBar', 'logger', function ($scope, $rootScope, $filter, $timeout, $modal, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.searchKeywords = '';
      $scope.filteredProviders = [];
      $scope.row = '';

      $scope.loadDealersOrders = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadDealersOrders", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function (result) {
          cfpLoadingBar.complete();
          $scope.orders = jQuery.parseJSON(result).dataset.orders;
          $scope.totalData = jQuery.parseJSON(result).dataset.total;
          $scope.$digest();
        });
      };

      $scope.export = function() {
        $.post("../backend/application/index.php?rota=/loadDealersOrders", {}, function (result) {
          $scope.salesToReport = jQuery.parseJSON(result).dataset.orders;

          var find = '<br>';
          var re = new RegExp(find, 'g');

          var data = [['id', 'Cliente', 'Status', 'Milhas', 'Valor', 'Data', 'Origem', 'Trecho', 'Companhia', 'Embarque', 'Observacao', 'Evento']];
          for (var i in $scope.salesToReport) {
            data.push([
              $scope.salesToReport[i].id,
              $scope.salesToReport[i].client,
              $scope.salesToReport[i].status,
              $rootScope.formatNumber($scope.salesToReport[i].miles, 0),
              $scope.salesToReport[i].amount,
              $filter('date')(new Date($scope.salesToReport[i].issue_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.salesToReport[i].origin,
              $scope.salesToReport[i].path,
              $scope.salesToReport[i].airlines,
              $filter('date')(new Date($scope.salesToReport[i].firstBoardingDate), 'dd/MM/yyyy hh:mm:ss'),
              $scope.salesToReport[i].comments,
              $scope.salesToReport[i].event.replace(re, '|'),
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
          link.setAttribute("download", "my_data.csv");
          document.body.appendChild(link);

          link.click();
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[0];
      $scope.currentPage = 1;
      $scope.currentPageProviders = [];
      init = function () {
        $scope.tabindex = 0;
        $scope.checkValidRoute();
        $scope.loadDealersOrders();
      };
      return init();
    }
  ]);
})();