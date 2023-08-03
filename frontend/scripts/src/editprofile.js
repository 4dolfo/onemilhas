(function () {
  'use strict';
  angular.module('app.purchase').controller('EditProfileCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', 'FileUploader', function($scope, $rootScope, $filter, cfpLoadingBar, logger, FileUploader) {
      var init;
      var original;
      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.userStatus = ['Pendente', 'Aprovado', 'Bloqueado', 'Reprovado'];
      $scope.specialChars = ['!', '@', '#', '$', '%', '¨', '&', '*', '.', ':', '^', '~', '?'];
      $scope.searchKeywords = '';
      $scope.filteredUsers = [];
      $scope.row = '';
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

      $scope.search = function() {
        $scope.filteredUsers = $filter('filter')($scope.users, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.setSelected = function() {
        $scope.selected = this.users[0];
        $scope.isTable = false;
        $scope.uploader.formData = {hashId: $scope.session.hashId, data: $scope.selected};
        return console.log(this.user);
      };

      $scope.newUser = function() {
        $scope.selected = {};
        $scope.isTable = false;
      };

      $scope.toggleFormTable = function() {
        $scope.isTable = false;
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

      $scope.verify = function() {
        $scope.checkscpecialChat = false;
        $scope.checkNumber = false;
        $scope.checkString = false;

        for(var i in $scope.selected.password) {
          if(!isNaN($scope.selected.password[i])) {
            $scope.checkNumber = true;
          }
          if(isNaN($scope.selected.password[i])) {
            $scope.checkString = true;
          }
          if($scope.specialChars.indexOf($scope.selected.password[i]) != -1) {
            $scope.checkscpecialChat = true;
          }
        }
      };

      $scope.saveUser = function() {
        if ($scope.form_user.$valid) {
          if(!$scope.main.isMaster) {
            if($scope.selected.password.length > 6) {
              for(var i in $scope.selected.password) {
                if(!isNaN($scope.selected.password[i])) {
                  $scope.checkNumber = true;
                }
                if(isNaN($scope.selected.password[i])) {
                  $scope.checkString = true;
                }
                if($scope.specialChars.indexOf($scope.selected.password[i]) != -1) {
                  $scope.checkscpecialChat = true;
                }
              }
              if(!$scope.checkNumber || !$scope.checkString || !$scope.checkscpecialChat) {
                return logger.logError('Verifique requisitos');
              }
            } else {
              return logger.logError('Senha Muito Curta');
            }
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
          }
          $scope.uploader.uploadAll();
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/saveProfile", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            window.location = '#/page/profile';
            $scope.isTable = false;
            cfpLoadingBar.complete();
          });
        }
      };

      $scope.cancelEdit = function() {
        // $scope.loadProfile();
        $scope.isTable = false;
      };

      $scope.loadProfile = function() {
        $.post("../backend/application/index.php?rota=/loadProfile", {hashId: $scope.session.hashId, id: $scope.main.id}, function(result){
          $scope.users = jQuery.parseJSON(result).dataset;
          $scope.search();
          
          $scope.setSelected();
          return $scope.select($scope.currentPage);
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageUsers = [];
      init = function() {

        $scope.checkNumber = false;
        $scope.checkscpecialChat = false;
        $scope.checkString = false;

        $scope.checkValidRoute();
        $scope.isTable = false;
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

        $scope.uploader = new FileUploader();
        $scope.uploader.url = "../backend/application/index.php?rota=/saveProfilePicture";
        $scope.uploader.filters.push({
            name: 'imageFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|'.indexOf(type) !== -1;
            }
        });

        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadSelfProfile", { hashId: $scope.session.hashId, data: $scope.main }, function(result){
            $scope.selected = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
        });

        // $scope.loadProfile();
      };
      return init();
    }
  ]).directive('ngThumb', ['$window', function($window) {
    var helper = {
        support: !!($window.FileReader && $window.CanvasRenderingContext2D),
        isFile: function(item) {
            return angular.isObject(item) && item instanceof $window.File;
        },
        isImage: function(file) {
            var type =  '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    };

    return {
        restrict: 'A',
        template: '<canvas/>',
        link: function(scope, element, attributes) {
            if (!helper.support) return;

            var params = scope.$eval(attributes.ngThumb);

            if (!helper.isFile(params.file)) return;
            if (!helper.isImage(params.file)) return;

            var canvas = element.find('canvas');
            var reader = new FileReader();

            reader.onload = onLoadFile;
            reader.readAsDataURL(params.file);

            function onLoadFile(event) {
                var img = new Image();
                img.onload = onLoadImage;
                img.src = event.target.result;
            }

            function onLoadImage() {
                var width = params.width || this.width / this.height * params.height;
                var height = params.height || this.height / this.width * params.width;
                canvas.attr({ width: width, height: height });
                canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
            }
        }
    };
}]);
})();

;