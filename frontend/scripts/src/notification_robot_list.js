(function () {
  'use strict';
  angular.module('app.purchase').controller('NotificationRobotListCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logs', 'header', 'order', 'onlineflights', function($scope, $rootScope, $modalInstance, logs, header, order, onlineflights) {

      $scope.header = header;
      $scope.logs = logs;
      $scope.order = order;
      $scope.onlineflights = onlineflights;

      $scope.updateOrder = function() {
        $.post("../backend/application/index.php?rota=/incodde/updateOrderStatus", { order: $scope.order }, function(result){
          $scope.checkOrderBot();
        });
      };

      $scope.checkOrderBot = function() {
        $.post("../backend/application/index.php?rota=/incodde/checkOrderBot", { order: $scope.order }, function(result){
          $scope.logs = jQuery.parseJSON(result).dataset;
          $scope.$apply();
        });
      };

      $scope.in8Bot = function() {
        $.post("../backend/application/index.php?rota=/incodde/newOrder", { order: $scope.order, onlineflights: $scope.onlineflights }, function(result){
          var response = jQuery.parseJSON(result).dataset;
          $scope.checkOrderBot();
        });
      };

      $scope.cancelOrder = function() {
        $.post("../backend/application/index.php?rota=/incodde/cancelOrder", { order: $scope.order }, function(result){
          var response = jQuery.parseJSON(result).dataset;
          $scope.checkOrderBot();
        });
      };

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;
