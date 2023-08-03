(function () {
  'use strict';
  angular.module('app.table').controller('CardsInUseCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      $scope.filter = {
        days: 7,
      };

      $scope.loadCardsInUse = function() {
        $.post("../backend/application/index.php?rota=/loadCardsInUse", { hashId: $scope.session.hashId }, function(result){
          $scope.Cards = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.removeCardUse = function(card) {
        $.post("../backend/application/index.php?rota=/removeCardUse", { hashId: $scope.session.hashId, data: card }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadCardsInUse();
        });
      };
      
      $scope.saveChangeMiles = function(data) {
        $.post("../backend/application/index.php?rota=/saveChangeMilesAZULSRM", { data: data }, function(result){
          $scope.loadAzulSRM();
        });
      };
      
      $scope.loadAzulSRM = function() {
        $.post("../backend/application/index.php?rota=/loadAZULSRMUsage", { }, function(result){
          $scope.AZULSRMUsage = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
        $.post("../backend/application/index.php?rota=/loadAZULMMSUsage", { }, function(result){
          $scope.AZULMMSUsage = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
  
        $.post("../backend/application/index.php?rota=/loadAZULSRMInUse", { }, function(result){
          $scope.AZULSRMInUSe = jQuery.parseJSON(result).dataset;
          $scope.$digest();
        });
      };

      $scope.printReport = function() {
        $.post("../backend/application/index.php?rota=/loadSalesAzulSRM", {}, function(result){
          $scope.sales = jQuery.parseJSON(result).dataset;

          var data = [['Dt Venda','Dt Embarque', 'Dias p embarque', 'Trecho', 'Tipo Trecho', 'Milhas', 'Loc',  'Status']];
          for(var i in $scope.sales) {
            data.push([
              $filter('date')(new Date($scope.sales[i].issue_date), 'dd/MM/yyyy hh:mm:ss'),
              $filter('date')(new Date($scope.sales[i].boarding_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.sales[i].date_diff,
              $scope.sales[i].airport_from + '-' + $scope.sales[i].airport_to,
              $scope.sales[i].category,
              parseFloat($scope.sales[i].miles_used),
              $scope.sales[i].flight_locator,
              $scope.sales[i].status
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
          link.setAttribute("download", "my_data.csv");
          document.body.appendChild(link);

          link.click();
        });
      };

      $scope.printReportLastMonth = function() {
        $.post("../backend/application/index.php?rota=/loadSalesAzulSRMLastMonth", {}, function(result){
          $scope.sales = jQuery.parseJSON(result).dataset;

          var data = [['Dt Venda','Dt Embarque', 'Dias p embarque', 'Trecho', 'Tipo Trecho', 'Milhas', 'Loc',  'Status']];
          for(var i in $scope.sales) {
            data.push([
              $filter('date')(new Date($scope.sales[i].issue_date), 'dd/MM/yyyy hh:mm:ss'),
              $filter('date')(new Date($scope.sales[i].boarding_date), 'dd/MM/yyyy hh:mm:ss'),
              $scope.sales[i].date_diff,
              $scope.sales[i].airport_from + '-' + $scope.sales[i].airport_to,
              $scope.sales[i].category,
              parseFloat($scope.sales[i].miles_used),
              $scope.sales[i].flight_locator,
              $scope.sales[i].status
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
          link.setAttribute("download", "my_data.csv");
          document.body.appendChild(link);

          link.click();
        });
      };

      $scope.loadAzulSRM();

      init = function() {
        $scope.checkValidRoute();
        $scope.loadCardsInUse();
      };

      return init();
    }
  ]);
})();
;
