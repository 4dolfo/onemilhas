(function () {
  'use strict';
  angular.module('app.maintenance', ['ui.calendar']).controller('ProfileCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', '$compile', function($scope, $rootScope, $filter, cfpLoadingBar, logger, $compile) {
      var init;
      var original;

      $scope.findDate = function() {
        $scope.main.sales = $scope.profile.sales;
        $scope.main.purchases = $scope.profile.purchases;
        $scope.$apply();
      };

      $scope.loadProfile = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadSelfProfile", { hashId: $scope.session.hashId, data: $scope.main }, function(result){
          $scope.profile = jQuery.parseJSON(result).dataset;
          $scope.main.adress = $scope.profile.adress;
          $scope.main.phoneNumber = $scope.profile.phoneNumber;
          $scope.main.city = $scope.profile.city;
          $scope.main.name = $scope.profile.name;
          cfpLoadingBar.complete();
          $scope.findDate();
        });
        if($scope.main.wizardSale) {
          $.post("../backend/application/index.php?rota=/loadSelfSales", {hashId: $scope.session.hashId, data: $scope.main}, function(result){
            $scope.profileSales = jQuery.parseJSON(result).dataset;
            $scope.$apply();
          });
        }
      };

      $scope.loadEvents = function() {
        $.post("../backend/application/index.php?rota=/loadEvents", { hashId: $scope.session.hashId }, function(result){
          $scope.eventsLoaded = jQuery.parseJSON(result).dataset;
          for(var i in $scope.events) {
            $scope.events.splice(i, 1);
          }

          for(var i in $scope.eventsLoaded) {
            $scope.eventsLoaded[i].start = new Date($scope.eventsLoaded[i].start);

            if($scope.eventsLoaded[i].end != '') {
              $scope.eventsLoaded[i].end = new Date($scope.eventsLoaded[i].end);
            }

            $scope.events.push($scope.eventsLoaded[i]);
          }
          $scope.$digest();
        });
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.loadProfile();
        $scope.profileSales = [];
        $rootScope.modalOpen = false;
      };

      $scope.events = [];

      $scope.uiConfig = {
        calendar:{
          lang: 'pt-br',
          height: 450,
          editable: true,
          customButtons: {
            myCustomButton: {
              text: '+',
              click: function() {
                $rootScope.$emit('openCalendarModal', {} );
              }
            }
          },
          viewRender: function (view, element) {
              $scope.loadEvents();
          },
          header:{
            left: 'month agendaWeek agendaDay, myCustomButton',
            center: '',
            right: 'prev,next today'
          },
          eventClick: function( date, jsEvent, view ){
            $scope.alertEventOnClick( date, jsEvent, view )
          },
          eventDrop: function( event, delta, revertFunc, jsEvent, ui, view ) {
            $scope.alertOnDrop( event, delta, revertFunc, jsEvent, ui, view )
          },
          eventResize: function( event, delta, revertFunc, jsEvent, ui, view ) {
            $scope.alertOnResize( event, delta, revertFunc, jsEvent, ui, view )
          }
        }
      };

      $scope.eventRender = function( event, element, view ) { 
        element.attr({'tooltip': event.title,
                      'tooltip-append-to-body': true});
        $compile(element)($scope);
      };

      $scope.alertOnDrop = function(event, delta, revertFunc, jsEvent, ui, view){
        event.start = event._start._d;
        if(event.end) {
          event.end = event._end._d;
        }
        $scope.saveEvent(event);
      };

      $scope.alertOnResize = function(event, delta, revertFunc, jsEvent, ui, view ){
        event.start = event._start._d;
        if(event.end) {
          event.end = event._end._d;
        }
        $scope.saveEvent(event);
      };

      $scope.alertEventOnClick = function( date, jsEvent, view ) {
        date.start = date._start._d;
        if(date.end) {
          date.end = date._end._d;
        }
        $rootScope.$emit('openCalendarModal', date );
      };

      $scope.saveEvent = function (event) {
        cfpLoadingBar.start();
        if(event.start) {
          event._start = $rootScope.formatServerDateTime(event.start);
        }

        if(event.end) {
          event._end = $rootScope.formatServerDateTime(event.end);
        }

        $.post("../backend/application/index.php?rota=/saveEvent", { hashId: $scope.session.hashId, data: event }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadEvents();
        });
      };

      $scope.eventSource = {
        url: "http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic",
        className: 'gcal-event',
        currentTimezone: 'America/Sao_Paulo'
      };

      $scope.uiConfig.calendar.dayNames = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado"];
      $scope.uiConfig.calendar.dayNamesShort = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"];

      $scope.eventSources = [$scope.events, $scope.eventSource];

      return init();

    }
  ]).directive('morrisUserSales', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 1000;
          switch (attrs.type) {
            case 'line':
              if (attrs.lineColors === void 0 || attrs.lineColors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.lineColors);
              }
              options = {
                element: ele[0],
                data: scope.$parent.profileSales,
                xkey: attrs.xkey,
                ykeys: JSON.parse(attrs.ykeys),
                labels: JSON.parse(attrs.labels),
                lineWidth: attrs.lineWidth || 2,
                lineColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
                resize: true
              };
              update = function() {
                setTimeout(finish, updateInterval);
                options.data = scope.$parent.profileSales;
                // return new Morris.Bar(options);
              };
              finish = function(){
                options.data = scope.$parent.profileSales;
                return new Morris.Line(options);
              };
              return update();
            case 'area':
              if (attrs.lineColors === void 0 || attrs.lineColors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.lineColors);
              }
              options = {
                element: ele[0],
                data: data,
                xkey: attrs.xkey,
                ykeys: JSON.parse(attrs.ykeys),
                labels: JSON.parse(attrs.labels),
                lineWidth: attrs.lineWidth || 2,
                lineColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
                behaveLikeLine: attrs.behaveLikeLine || false,
                fillOpacity: attrs.fillOpacity || 'auto',
                pointSize: attrs.pointSize || 4,
                resize: true
              };
              return new Morris.Area(options);
            case 'bar':
              if (attrs.barColors === void 0 || attrs.barColors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.barColors);
              }
              options = {
                element: ele[0],
                data: scope.$parent.milesAnalysis,
                xkey: attrs.xkey,
                ykeys: JSON.parse(attrs.ykeys),
                labels: JSON.parse(attrs.labels),
                barColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
                stacked: attrs.stacked || null,
                resize: true
              };
              update = function() {
                setTimeout(finish, updateInterval);
                options.data = scope.$parent.milesAnalysis;
                // return new Morris.Bar(options);
              };
              finish = function(){
                options.data = scope.$parent.milesAnalysis;
                return new Morris.Bar(options);
              };
              return update();
            case 'donut':
              if (attrs.colors === void 0 || attrs.colors === '') {
                colors = null;
              } else {
                colors = JSON.parse(attrs.colors);
              }
              options = {
                element: ele[0],
                data: data,
                colors: colors || ['#0B62A4', '#3980B5', '#679DC6', '#95BBD7', '#B0CCE1', '#095791', '#095085', '#083E67', '#052C48', '#042135'],
                resize: true
              };
              if (attrs.formatter) {
                func = new Function('y', 'data', attrs.formatter);
                options.formatter = func;
              }
              return new Morris.Donut(options);
          }
        }
      };
    }
  ]).controller('CalendarEventModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $rootScope.modalOpen = false;

      $rootScope.$on('openCalendarModal', function(event, args) {
        event.stopPropagation();
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function(args) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "CalendarEventModal.html",
          controller: 'CalendarEventInstanceCtrl',
          resolve: {
            event: function() {
              return args;
            }
          }
        });
        modalInstance.result.then(function(resolve) {
          $scope.$parent.saveEvent(resolve);
          $rootScope.modalOpen = false;

        }, function() {
          $rootScope.modalOpen = false;
        });
      };

    }
  ]).controller('CalendarEventInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'event', 'logger', function($scope, $rootScope, $modalInstance, event, logger) {
      $scope.event = event;
      $scope.eventOptions = [
        { label: 'PUBLICO', type: 'PUBLIC' },
        { label: 'PRIVADO', type: 'PRIVATED' }
      ];

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.save = function() {
        $modalInstance.close($scope.event);
      };

    }
  ]);
})();
;