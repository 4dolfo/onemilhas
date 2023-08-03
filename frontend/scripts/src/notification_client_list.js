(function () {
  'use strict';
  angular.module('app.purchase').controller('NotificationClientListCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'data', 'header', function($scope, $rootScope, $modalInstance, data, header) {

      function UniqueArraybyId(collection, keyname) {
        var output = [], 
        keys = [];

        angular.forEach(collection, function(item) {
          var key = item[keyname];
          if(keys.indexOf(key) === -1) {
            keys.push(key);
            output.push(item);
          }
        });
        return output;
      };

      $scope.header = header;
      $scope.data = UniqueArraybyId(data ,"id");

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;
