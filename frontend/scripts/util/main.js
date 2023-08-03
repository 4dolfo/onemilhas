angular.module('app').run(['$rootScope', function($rootScope) {
 
  $rootScope.main = {
    Brand: 'ONE MILHAS',
    Name: '',
    Id: '',
    Email: '',
    IsMaster: ''
  };

  $rootScope.getBrand = function() {
    return $rootScope.main.Brand;
  }

  $rootScope.getName = function() {
    return $rootScope.main.Name;
  }

  $rootScope.getId = function() {
    return $rootScope.main.Id;
  }

  $rootScope.getEmail = function() {
    return $rootScope.main.Email;
  }

  $rootScope.getIsMaster = function() {
    return $rootScope.main.IsMaster;
  }

  $rootScope.setBrand = function(Brand) {
    $rootScope.main.Brand = Brand;
  }

  $rootScope.setName = function(Name) {
    $rootScope.main.Name = Name;
  }

  $rootScope.setId = function(Id) {
    $rootScope.main.Id = Id;
  }

  $rootScope.setEmail = function(Email) {
    $rootScope.main.Email = Email;
  }

  $rootScope.setIsMaster = function(IsMaster) {
    $rootScope.main.IsMaster = IsMaster;
  }
}]);

