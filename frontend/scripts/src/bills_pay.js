(function () {
  'use strict';
  angular.module('app.billsPayList', ['ui.utils.masks']).controller('BillsPayCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      $scope.accountType = ['Taxa DU','Taxa Extra', 'Taxa Aeroporto', 'Reembolso', 'Milhas + Money', 'Contas Comuns', 'Taxas'];
      $scope.paymentType = ['Cartão de Crédito', 'Deposito em Conta', 'Boleto Bancario', 'Reembolso'];
      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.searchKeywords = '';
      $scope.filteredBillsPay = [];
      $scope.row = '';
      
      $scope.select = function(page) {
        // var end, start;
        // start = (page - 1) * $scope.numPerPage;
        // end = start + $scope.numPerPage;
        // return $scope.currentPageBillsPay = $scope.filteredBillsPay.slice(start, end);
        $scope.loadBillsPay();
      };
      
      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };
      
      $scope.onNumPerPageChange = function() {
        // $scope.select(1);
        // return $scope.currentPage = 1;
        $scope.loadBillsPay();
      };
      
      $scope.onOrderChange = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };
      
      $scope.setSelected = function() {
        $scope.selected = this.billpay;
        $scope.tabindex = 1;
        return $scope.selected;
      };

      $scope.getStatusDesc = function(status) {
        if (status == 'B') {
          return 'Baixada';
        } else {
          return 'Em Aberto';
        }
      };

      $scope.billPayTag = function(status) {
        if (status == 'B') {
          return "label label-success";
        } else {
          return "label label-warning";
        }
      };

      $scope.addRow = function() {
        if (this.billpay.status == 'B') {
          this.billpay.checked = false;
        }
      };
      
      $scope.loadSynthetic = function() {
        $.post("../backend/application/index.php?rota=/billspay/loadSynthetic", { data: $scope.filter }, function(result){
          $scope.data = jQuery.parseJSON(result).dataset;

          var doc = new jsPDF('p', 'pt');
          doc.margin = 0.5;
          doc.setFontSize(18);

          var columns = [
            {title: "Valor", dataKey: "value"},
            {title: "Quantidade", dataKey: "quant"},
            {title: "Cartão", dataKey: "card_number"},
          ];
          
          var rows = [];
          doc.autoTable(columns, $scope.data, {
          theme: 'grid',
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
          },
          startY: 90,
          margin: {horizontal: 10},
          bodyStyles: {valign: 'top'},
          createdCell: function (cell, data) {
            if (data.column.dataKey === 'value') {
              cell.styles.halign = 'right';
            }
          }
        });
        doc.save('sintetico.pdf');

        });
      };

      $scope.loadAnalytical = function() {
        $.post("../backend/application/index.php?rota=/billspay/loadAnalytical", { data: $scope.filter }, function(result){
          $scope.data = jQuery.parseJSON(result).dataset;

          var doc = new jsPDF('p', 'pt');
          doc.margin = 0.5;
          doc.setFontSize(18);

          var columns = [
            {title: "Cartão", dataKey: "card_number"},
            {title: "Data", dataKey: "issue_date"},
            {title: "Companhia", dataKey: "airline_name"},
            {title: "Valor", dataKey: "amount_paid"},
          ];

          let obj = {
            card_number: $scope.filter.credit_card,
            issue_date: 'De ' + $scope.filter.dueDateFrom.getDate() + '/' + $scope.filter.dueDateFrom.getMonth() + 1,
          }
          if($scope.filter.dueDateTo) {
            obj['airline_name'] = ' a ' + $scope.filter.dueDateTo.getDate() + '/' + $scope.filter.dueDateTo.getMonth() + 1;
          }
          $scope.data.unshift(obj);
          doc.autoTable(columns, $scope.data, {
          theme: 'grid',
          styles: {
            fontSize: 8,
            overflow: 'linebreak'
          },
          startY: 90,
          margin: {horizontal: 10},
          bodyStyles: {valign: 'top'},
          createdCell: function (cell, data) {
            if (data.column.dataKey === 'value') {
              cell.styles.halign = 'right';
            }
          }
        });
        doc.save('analitico.pdf');

        });
      };

      $scope.search = function() {
        $scope.filteredBillsPay = $filter('filter')($scope.billspay, $scope.searchKeywords);
        return $scope.onFilterChange();
      };
      
      $scope.order = function(rowName) {
        $scope.searchOrder = rowName;
        $scope.searchOrderDown = undefined;
        $scope.loadBillsPay();
      };

      $scope.orderDown = function(rowName) {
        $scope.searchOrder = undefined;
        $scope.searchOrderDown = rowName;
        $scope.loadBillsPay();
      };
      
      $scope.loadData = function() {
        $.post("../backend/application/index.php?rota=/loadSumOpenedBillsPay", { data: $scope.filter }, function(result){
          $scope.sumOpenedbillspay = jQuery.parseJSON(result).dataset;
        });
        $.post("../backend/application/index.php?rota=/loadSumClosedBillsPay", { data: $scope.filter }, function(result){
          $scope.sumClosedbillspay = jQuery.parseJSON(result).dataset;
        });
        $scope.loadBillsPay();
      };
      
      $scope.loadBillsPay = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadBillsPay", { page: $scope.currentPage, numPerPage: $scope.numPerPage, searchKeywords: $scope.searchKeywords, order: $scope.searchOrder, orderDown: $scope.searchOrderDown, data: $scope.filter }, function(result){
            $scope.billspays = jQuery.parseJSON(result).dataset.billspay;
            $scope.totalData = jQuery.parseJSON(result).dataset.total;
            cfpLoadingBar.complete();
            $scope.$digest();
        });
      };

      $scope.loadFixed = function() {
        $.post("../backend/application/index.php?rota=/loadFixedBillsPay", { }, function(result){
          $scope.fixedBillsPay = jQuery.parseJSON(result).dataset;
          $scope.searchFixedBillsPay();
          $scope.loadEvents();
          $scope.selectFixedBillsPay($scope.currentPageFixed);
        });
        $.post("../backend/application/index.php?rota=/loadChartCalendarBillsPay", { }, function(result){
          $scope.chartCalendar = jQuery.parseJSON(result).dataset;
        });
      };

      $scope.searchFixedBillsPay = function() {
        $scope.filteredFixedBillsPay = $filter('filter')($scope.fixedBillsPay, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.toggleFormTable = function() {
        $scope.tabindex = 0;
      };

      $scope.selectFixedBillsPay = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageFixedBillsPay = $scope.filteredFixedBillsPay.slice(start, end);
      };
      
      $scope.onFilterChange = function() {
        $scope.select(1);
        $scope.currentPage = 1;
        return $scope.row = '';
      };
      
      $scope.onNumPerPageChangeFixedBillsPay = function() {
        $scope.select(1);
        return $scope.currentPage = 1;
      };

      $scope.saveClosePay = function() {
        $scope.checkedrows = $filter('filter')($scope.billspay, true);
        $.post("../backend/application/index.php?rota=/saveClosePay", {hashId: $scope.session.hashId, checkedrows: $scope.checkedrows}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.loadData();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.saveBillPay = function() {
        $scope.checkedrows = $filter('filter')($scope.billspay, true);
        $scope.selected.actual_value = $('#bpay_actualvalue').maskMoney('unmasked')[0];
        $scope.selected.tax = $('#bpay_tax').maskMoney('unmasked')[0];
        $scope.selected.discount = $('#bpay_discount').maskMoney('unmasked')[0];

        $.post("../backend/application/index.php?rota=/saveBillPay", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.tabindex = 0;
            $scope.loadData();
            return $scope.select($scope.currentPage);
        });
      };

      $scope.setActual_Value = function() {
        $scope.selected.actual_value = parseFloat($scope.selected.original_value) + parseFloat($scope.selected.tax) - parseFloat($scope.selected.discount);
      };

      $scope.findDate = function(date){
        var actualDate = new Date(date);
        actualDate.setDate(actualDate.getDate() + 1);
        return actualDate;
      };

      $scope.returnDate = function(date) {
        return new Date(date);
      };

      $scope.newBill = function(){
        $scope.tabindex = 2;
        $('#value').number( true, 2, ',', '.');
      };

      $scope.generateBill = function(){
        $scope.billpay._dueDate = $rootScope.formatServerDate($scope.billpay.due_date);
        $.post("../backend/application/index.php?rota=/generateBill", {hashId: $scope.session.hashId, data: $scope.billpay}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          $scope.loadData();
        });
        $scope.back();
      };

      $scope.back = function(){
        $scope.tabindex = 0;
        $scope.billpay = undefined;
      };

      $scope.loadEvents = function(view) {
        
        $.post("../backend/application/index.php?rota=/loadEventsBillsPay", { }, function(result){
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

          for(var i in $scope.fixedBillsPay) {
            $scope.fixedBillsPay[i].start = new Date();
            $scope.fixedBillsPay[i].date = new Date($scope.fixedBillsPay[i].date);
            $scope.fixedBillsPay[i].start.setDate($scope.fixedBillsPay[i].date.getDate());

            if(view) {
              $scope.fixedBillsPay[i].start.setMonth($scope.getMonth(view));
            }

            var object = {
              title: $scope.fixedBillsPay[i].title,
              start: $scope.fixedBillsPay[i].start,
              allDay: true
            }

            $scope.events.push(object);
          }
          $scope.$digest();
        });
      };

      $scope.AddFixed = function() {
        $rootScope.$emit('openFixedBillsPayModal', { } );
      };

      $scope.setSelectedFixed = function(fixed) {
        fixed.date = new Date(fixed.date);
        $rootScope.$emit('openFixedBillsPayModal', fixed );
      };

      $scope.toCalendar = function() {
        $scope.tabindex = 3;
        $scope.loadEvents();
      };

      $scope.numPerPageOpt = [10, 30, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageBillsPay = [];

      $scope.numPerPageFixedBillsPay = $scope.numPerPageOpt[2];
      $scope.currentPageFixed = 1;
      $scope.currentPageFixedBillsPay = [];

      $rootScope.hashId = $scope.session.hashId;
      
      init = function() {
        $scope.tabindex = 0;
        $scope.checkValidRoute();
        $scope.loadData();
        $rootScope.modalOpen = false;
        $scope.chartCalendar = [];
        $scope.loadFixed();

        $('#calendar').fullCalendar({
            googleCalendarApiKey: 'AIzaSyAAtkhpiuH-hBzvaGp8QP6C_VqBlE37t6I'
        });

      };

      $scope.getMonth = function(date) {
        if(date.title.indexOf('January') > -1) return 0;
        if(date.title.indexOf('February') > -1) return 1;
        if(date.title.indexOf('March') > -1) return 2;
        if(date.title.indexOf('April') > -1) return 3;
        if(date.title.indexOf('May') > -1) return 4;
        if(date.title.indexOf('June') > -1) return 5;
        if(date.title.indexOf('July') > -1) return 6;
        if(date.title.indexOf('August') > -1) return 7;
        if(date.title.indexOf('September') > -1) return 8;
        if(date.title.indexOf('October') > -1) return 9;
        if(date.title.indexOf('November') > -1) return 10;
        if(date.title.indexOf('December') > -1) return 11;
      };

      $scope.events = [];

      $scope.uiConfigBillsPay = {
        calendar:{
          lang: 'pt-br',
          height: 550,
          editable: true,
          customButtons: {
            myCustomButton: {
              text: '+',
              click: function() {
                $rootScope.$emit('openCalendarBillsPayModal', { allDay: true } );
              }
            }
          },
          viewRender: function (view, element) {
              $scope.loadEvents(view);
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
        date.start = date._start._i;
        if(date.end) {
          date.end = date._end._i;
        }
        $rootScope.$emit('openCalendarBillsPayModal', date );
      };

      $scope.saveEvent = function (event) {
        cfpLoadingBar.start();
        if(event.start) {
          event._start = $rootScope.formatServerDateTime(event.start);
        }

        $.post("../backend/application/index.php?rota=/saveEventBillsPay", { data: event }, function(result){
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

      $scope.uiConfigBillsPay.calendar.dayNames = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado"];
      $scope.uiConfigBillsPay.calendar.dayNamesShort = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"];

      $scope.eventSources = [$scope.events, $scope.eventSource];

      $scope.saveFixed = function(fixed) {
        cfpLoadingBar.start();
        fixed._date = $rootScope.formatServerDate(fixed.date);

        $.post("../backend/application/index.php?rota=/saveFixedBillsPay", { data: fixed }, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
          cfpLoadingBar.complete();
          $scope.loadFixed();
        });
      };

      return init();
    }
  ]).controller('BillPayModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {
      $scope.filterproviders = $scope.$parent.providers;

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "BillPay.html",
          controller: 'BillPayInstanceCtrl',
          periods: $scope.$parent.periods,
          resolve: {
            filter: function() {
              return $scope.filter;
            }
          }
        });
        modalInstance.result.then((function(filter) {
          if (filter != undefined) {
            filter._dueDateFrom = $rootScope.formatServerDate(filter.dueDateFrom);
            filter._dueDateTo = $rootScope.formatServerDate(filter.dueDateTo);
          }

          $scope.$parent.filter = filter;
          $scope.$parent.loadData();
        }), function() {
          $log.info("Modal dismissed at: " + new Date());
        });
      };
    }
  ]).controller('BillPayInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {

      $scope.billStatus = ['Em aberto','Baixada'];
      $scope.accountType = ['Taxa DU','Taxa Extra', 'Taxa Aeroporto', 'Compra Milhas', 'Reembolso', 'Milhas + Money', 'Contas Comuns', 'Taxas'];
      $scope.paymentType = ['Cartão de Crédito', 'Deposito em Conta', 'Boleto Bancario', 'Reembolso'];

      $.post("../backend/application/index.php?rota=/loadInternalCards", {hashId: $scope.$parent.hashId}, function(result){
        $scope.internalCards = jQuery.parseJSON(result).dataset;
      });

      $.post("../backend/application/index.php?rota=/loadProvider", {hashId: $scope.$parent.hashId}, function(result){
        $scope.providers = jQuery.parseJSON(result).dataset.providers;
      });

      $scope.filter = filter;
      $scope.ok = function() {
        $modalInstance.close($scope.filter);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('CalendarBillsPayModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $rootScope.modalOpen = false;

      $rootScope.$on('openCalendarBillsPayModal', function(event, args) {
        event.stopPropagation();
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function(args) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "CalendarBillsPayModal.html",
          controller: 'CalendarBillsPayInstanceCtrl',
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
  ]).controller('CalendarBillsPayInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'event', 'logger', function($scope, $rootScope, $modalInstance, event, logger) {
      $scope.event = event;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.save = function() {
        $modalInstance.close($scope.event);
      };

    }
  ]).directive('morrisAmountBillspay', [
    function() {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function(scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 2000;
          if (attrs.lineColors === void 0 || attrs.lineColors === '') {
            colors = null;
          } else {
            colors = JSON.parse(attrs.lineColors);
          }
          options = {
            element: ele[0],
            data: scope.$parent.chartCalendar,
            xkey: attrs.xkey,
            ykeys: JSON.parse(attrs.ykeys),
            labels: JSON.parse(attrs.labels),
            lineWidth: attrs.lineWidth || 2,
            lineColors: colors || ['#0b62a4', '#ff0066', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
            resize: true
          };
          update = function() {
            setTimeout(finish, updateInterval);
            options.data = scope.$parent.chartCalendar;
          };
          finish = function(){
            if(scope.$parent.chartCalendar.length > 0) {
              options.data = scope.$parent.chartCalendar;
              return new Morris.Line(options);
            } else {
              setTimeout(finish, updateInterval);
            }
          };
          return update();
        }
      };
    }
  ]).controller('FixedBillsPayModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $rootScope.modalOpen = false;

      $rootScope.$on('openFixedBillsPayModal', function(event, args) {
        event.stopPropagation();
        if($rootScope.modalOpen == false) {
          $rootScope.modalOpen = true;
          $scope.open(args);
        }
      });

      $scope.open = function(args) {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "FixedBillsPayModal.html",
          controller: 'FixedBillsPayInstanceCtrl',
          resolve: {
            fixed: function() {
              return args;
            }
          }
        });
        modalInstance.result.then(function(resolve) {
          $scope.$parent.saveFixed(resolve);
          $rootScope.modalOpen = false;

        }, function() {
          $rootScope.modalOpen = false;
        });
      };

    }
  ]).controller('FixedBillsPayInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'fixed', 'logger', function($scope, $rootScope, $modalInstance, fixed, logger) {
      $scope.fixed = fixed;

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.save = function() {
        $modalInstance.close($scope.fixed);
      };

    }
  ]);
})();
;
