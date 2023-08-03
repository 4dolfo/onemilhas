(function () {
    'use strict';
    angular.module('app.modalClientsSelection').controller('ModalClientsSelectionCtrl', [
      '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', function($scope, $rootScope, $modalInstance, $filter, logger) {
  
      $scope.searchKeywords = '';
      $scope.clients = [];
      $scope.filteredClients = [];
      $.post("../backend/application/index.php?rota=/loadClientsNames", {}, function(result){
        $scope.response = jQuery.parseJSON(result).dataset;
        $scope.search();
      });

      $scope.search = function() {
        $scope.filteredClients = $filter('filter')($scope.response, $scope.searchKeywords);
        $scope.$digest();
      };

      $scope.setSelected = function(client) {
        if($scope.clients.indexOf(client.id) > -1) {
          $scope.clients.slice($scope.clients.indexOf(client.id), 1);
        } else {
          $scope.clients.push(client.id);
        }
      };

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
      $scope.ok = function() {
        $modalInstance.close($scope.clients);
      };
      }
    ]);
  })();
  ;
  