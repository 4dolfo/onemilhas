(function () {
'use strict';
angular.module('app.miles').controller('BalanceCardsCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
    var init;
    var original;

    $scope.load = function() {
        $.post("../backend/application/index.php?rota=/loadCardsAirlinesStatus", {}, function(result){
            $scope.airlinesStatus = jQuery.parseJSON(result).dataset;
            $scope.$digest();
        });
        $.post("../backend/application/index.php?rota=/loadCardsAirlinesRange", {}, function(result){
            $scope.airlinesRange = jQuery.parseJSON(result).dataset;
            $scope.$digest();
        });
    };

    $scope.findCards = function(airline) {
        $scope.cardsAirlines = null;
        $.post("../backend/application/index.php?rota=/loadCardsAirline", {airline: airline}, function(result){
            $scope.cards = jQuery.parseJSON(result).dataset;
            $scope.$digest();
        });
    };

    $scope.findCardsAirlines = function(range) {
        $scope.cards = null;
        $.post("../backend/application/index.php?rota=/loadCardsAirlineByMiles", {range: range}, function(result){
            $scope.cardsAirlines = jQuery.parseJSON(result).dataset;
            $scope.$digest();
        });
    };

    init = function() {
        $scope.checkValidRoute();
        $scope.airlinesStatus = [];
        $scope.airlinesRange = [];

        $scope.load();
    };

    return init();
    }
]);
})();
;
