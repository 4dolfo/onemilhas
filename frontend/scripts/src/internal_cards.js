(function () {
  'use strict';
  angular.module('app.internal').controller('InternalCardsCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var original;
      $scope.providerStatus = ['Aprovado', 'Bloqueado', 'Arquivado'];
      $scope.searchKeywords = '';
      $scope.filteredCards = [];
      $scope.row = '';
      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageCards = $scope.filteredCards.slice(start, end);
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
        $scope.filteredCards = $filter('filter')($scope.internalCards, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.decript = function(code){
        var data = code.split('320AB');
        var finaly = '';
        for (var j = 0; data.length > j; j++) {
          finaly = finaly + (String.fromCharCode(data[j] / 320));
        }
        return finaly;
      };

      $scope.ecript = function(code){
        var data = code.split("");
        var finaly = '';
        for (var j = 0; data.length > j; j++) {
          finaly = finaly + (data[j].charCodeAt(0) * 320) + '320AB';
        }
        return finaly;
      };

      $scope.findDate = function(date){
        return new Date(date);
      };

      $scope.setSelected = function() {
        original = angular.copy(this.card);
        $scope.selected = original;
        $('#limit').number( true, 2, ',', '.');
        $('#used').number( true, 2, ',', '.');
        $scope.selected._due_date = new Date($scope.selected.due_date);
        $scope.selected._due_date.setDate($scope.selected._due_date.getDate() + 1);
        $scope.selected._birthdate = new Date($scope.selected.birthdate);
        $scope.selected._birthdate.setDate($scope.selected._birthdate.getDate() + 1);

        $scope.card = {id: $scope.selected.card_number};
        $.post("../backend/application/index.php?rota=/loadCreditHistoric", {hashId: $scope.session.hashId, type: 'CREDITCARD', data: $scope.card}, function(result){
          $scope.CardLog = jQuery.parseJSON(result).dataset;
        });
        $scope.tabindex = 1;
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredCards = $filter('orderBy')($scope.internalCards, rowName);
        return $scope.onOrderChange();
      };

      $scope.saveInternal = function() {
        // if ($scope.form_internal.$valid) {
          $scope.selected._due_date = $rootScope.formatServerDate($scope.selected._due_date);
          $scope.selected._birthdate = $rootScope.formatServerDate($scope.selected._birthdate);
          $scope.selected.password = $scope.ecript($scope.selected.password);
          $.post("../backend/application/index.php?rota=/saveInternal", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadInternalCards();
          });
        // }
      };

      $scope.providerTag = function(status) {
        switch (status) {
          case 'Aprovado':
            return "label label-success";
          case 'Bloqueado':
            return "label label-warning";
        }
      };

      $scope.cancelEdit = function() {
        $scope.loadInternalCards();
        $scope.selected = {};
        $scope.tabindex = 0;
      };

      $scope.loadInternalCards = function() {
        $.post("../backend/application/index.php?rota=/loadInternalCards", { data: $scope.filter }, function(result){
          $scope.internalCards = jQuery.parseJSON(result).dataset;
          for(var i in $scope.internalCards){
            $scope.internalCards[i].password = $scope.decript($scope.internalCards[i].password);
          }
          $scope.search();
          cfpLoadingBar.complete();
          $scope.tabindex = 0;
          $scope.$apply();
          return $scope.select($scope.currentPage);
        });
      };

      $scope.exists = function(item) {
        if($scope.selected) {
          if($scope.selected.priority_airline.length > 0){
            return $scope.selected.priority_airline.indexOf(item) > -1;
          }
        }
      };

      $scope.isChecked = function() {
        if($scope.selected){
          if($scope.selected.priority_airline)
            return $scope.selected.priority_airline.length === $scope.airlines.length;
        }
        return false;
      };

      $scope.toggleAll = function() {
        if ($scope.selected.priority_airline.length === $scope.airlines.length) {
          $scope.selected.priority_airline = [];
        } else {
          $scope.selected.priority_airline = [];
          for(var i in $scope.airlines){
            $scope.selected.priority_airline.push($scope.airlines[i].name);
          }
        }
      };

      $scope.toggle = function(item) {
        if($scope.selected.priority_airline.indexOf(item) > -1) {
          $scope.selected.priority_airline.splice($scope.selected.priority_airline.indexOf(item), 1);
        } else {
          $scope.selected.priority_airline.push(item);
        }
      };

      $scope.newInternal = function(){
        $scope.selected = {};
        $scope.tabindex = 1;
      };

      $scope.searchProviders = function() {
        $.post("../backend/application/index.php?rota=/loadProvider", { searchKeywords: $scope.selected.prodider_exclusive }, function(result){
          $scope.providers = jQuery.parseJSON(result).dataset.providers;
          $scope.$digest();
        });
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageCards = [];
      init = function() {
        $scope.tabindex = 0;
        $scope.newUpload = false;
        $scope.newFile = undefined;
        $scope.filter = {
          archived: false
        };
        $scope.checkValidRoute();
        $scope.selected = {};

        $.post("../backend/application/index.php?rota=/loadAirline", $scope.session, function(result){
          $scope.airlines = jQuery.parseJSON(result).dataset;
        });

        $scope.searchProviders();

        $scope.loadInternalCards();

      };

      $scope.openSearchModal = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "CreditCardSearchModal.html",
          controller: 'CreditCardSearchModalInstanceCtrl',
          periods: $scope.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
          $scope.filter = filter;
          $scope.loadInternalCards();
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };

      return init();
    }
  ]).controller('CreditCardSearchModalInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter ) {

      $scope.filter = filter;
      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);

})();

;