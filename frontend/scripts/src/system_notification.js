(function () {
  'use strict';
  angular.module('app.table').controller('SystemNotificationCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'notification', 'header', function($scope, $rootScope, $modalInstance, notification, header) {

      $scope.header = header;
      $scope.notification = notification;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;
