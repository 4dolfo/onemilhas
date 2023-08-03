(function () {
  'use strict';
  angular.module('app.table').controller('RiskAnalysisCtrl', [
    '$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
      var init;
      var tabindex;
      $scope.searchKeywords = '';
      $scope.selected = {};
      $scope.Billets = [];
      $scope.Sales = [];

      $scope.setSelected = function(){
        $scope.tabindex = true;
        $scope.showReport = false;
        $scope.client = $filter('filter')($scope.clients, $scope.selected.name);
        for(var i in $scope.client) {
            if($scope.client[i].name == $scope.selected.name) {
              $scope.selectedClient = angular.copy($scope.client[i]);
              $.post("../backend/application/index.php?rota=/loadBilletsClient", {data: $scope.selectedClient}, function(result){
                $scope.Billets = jQuery.parseJSON(result).dataset;
                $scope.$apply();
                $.post("../backend/application/index.php?rota=/loadSalesClient", {data: $scope.selectedClient}, function(result){
                  $scope.Sales = jQuery.parseJSON(result).dataset;
                  $scope.showReport = true;
                  $scope.$apply();
                });
              });
            }
        }
      };

      $scope.getTotal = function() {
        var total = 0;
        if($scope.Sales) {
          if($scope.Sales.length > 0) {
            for(var i in $scope.Sales) {
              if($scope.Sales[i].checked) {
                total += $scope.Sales[i].amountPaid;
              }
            }
          }
        }
        return total;
      };

      $scope.getTotalCancelCost = function() {
        var total = 0;
        if($scope.Sales) {
          if($scope.Sales.length > 0) {
            for(var i in $scope.Sales) {
              if($scope.Sales[i].checked) {
                total += $scope.Sales[i].cancelCost;
              }
            }
          }
        }
        return total;
      };

      $scope.getDueValue = function(){
        var total = 0;
        $scope.count = 0;
        if($scope.Billets.length > 0){
          var date = new Date($rootScope.formatServerDate(new Date()) + 'T12:00:00Z');
          var dueDate;
          for (var i = 0; i < $scope.Billets.length; i++) {
            if($scope.Billets[i].status == 'E' && $scope.Billets[i].actual_value > 0){
              dueDate = new Date($scope.Billets[i].due_date + 'T12:00:00Z');
              if(dueDate < date){
                $scope.count++;
                total = total + ($scope.Billets[i].actual_value - $scope.Billets[i].alreadyPaid);
              }
            }
          }
        }
        return total;
      };

      $scope.getStatusDesc = function(status) {
        switch (status) {
          case 'B':
            return "Pago";
          case 'E':
            return "Em Aberto";
          case 'A':
            return "Em Aberto";
          case 'C':
            return "Cancelado";
        }
      };

      $scope.exportAnalysis = function() {
        var doc = new jsPDF('l', 'pt');
        doc.margin = 0.5;
        doc.setFontSize(16);
        doc.text($scope.selected.name, 400, 20);

        var coluns = [
          {title: "Boletos - Atrasados", dataKey: "issuing_date"},
          {title: "Vencimento", dataKey: "due_date"},
          {title: "Valor", dataKey: "actual_value"},
          {title: "Pago", dataKey: "alreadyPaid"},
          {title: "Nosso Numero", dataKey: "our_number"}];

        var start = 50;
        var date = new Date();
        date.setDate(date.getDate() - 1);

        var data = [];
        for(var i in $scope.Billets){
            var dueDate = new Date($scope.Billets[i].due_date + 'T12:00:00Z');
            if(dueDate < date) {
              data.push({
                issuing_date: $filter('date')(new Date($scope.Billets[i].IssueDate + 'T12:00:00Z'),'dd/MM/yyyy'),
                due_date: $filter('date')(new Date($scope.Billets[i].due_date + 'T12:00:00Z'),'dd/MM/yyyy'),
                actual_value: $rootScope.formatNumber($scope.Billets[i].actual_value),
                alreadyPaid: $rootScope.formatNumber($scope.Billets[i].alreadyPaid),
                our_number: $scope.Billets[i].ourNumber
              });
            }
        }

        data.push({
          issuing_date: 'Total',
          actual_value: $rootScope.formatNumber($scope.getDueValue())
        });

        doc.autoTable(coluns, data, {
          startY: start,
          margin: {horizontal: 15}
        });
        start = doc.autoTableEndPosY() + 20;

        var coluns = [
          {title: "Boletos - Gerados", dataKey: "issuing_date"},
          {title: "Vencimento", dataKey: "due_date"},
          {title: "Valor", dataKey: "actual_value"},
          {title: "Pago", dataKey: "alreadyPaid"},
          {title: "Nosso Numero", dataKey: "our_number"}];

        data = [];
        for(var i in $scope.Billets){
            var dueDate = new Date($scope.Billets[i].due_date + 'T12:00:00Z');
            if(dueDate >= date) {
              data.push({
                issuing_date: $filter('date')(new Date($scope.Billets[i].IssueDate + 'T12:00:00Z'),'dd/MM/yyyy'),
                due_date: $filter('date')(new Date($scope.Billets[i].due_date + 'T12:00:00Z'),'dd/MM/yyyy'),
                actual_value: $rootScope.formatNumber($scope.Billets[i].actual_value),
                alreadyPaid: $rootScope.formatNumber($scope.Billets[i].alreadyPaid),
                our_number: $scope.Billets[i].ourNumber
              });
            }
        }

        data.push({
          issuing_date: 'Total',
          actual_value: $rootScope.formatNumber($scope.alreadyGenerated())
        });

        doc.autoTable(coluns, data, {
          startY: start,
          margin: {horizontal: 15}
        });
        start = doc.autoTableEndPosY() + 20;

        coluns = [
          {title: "Valor", dataKey: "name"},
          {title: "Totais", dataKey: "total"}];

        data = [];
        data.push({
          name: 'Bilhetes Futuros',
          total: $rootScope.formatNumber($scope.getTotal())
        });

        data.push({
          name: 'Cancelamento',
          total: $rootScope.formatNumber($scope.getTotalCancelCost())
        });

        data.push({
          name: 'Total',
          total: $rootScope.formatNumber($scope.cancelCost())
        });

        doc.autoTable(coluns, data, {
          startY: start,
          margin: {horizontal: 15}
        });
        start = doc.autoTableEndPosY() + 20;

        coluns = [
          {title: "Balanço", dataKey: "total"},
          {title: "Status", dataKey: "status"}];

        data = [];
        data.push({
          total: $rootScope.formatNumber(($scope.getDueValue() + $scope.alreadyGenerated()) - $scope.cancelCost()),
          status: $scope.getStatus()
        });

        doc.autoTable(coluns, data, {
          startY: start,
          margin: {horizontal: 15}
        });
        start = doc.autoTableEndPosY() + 20;

        coluns = [
          {title: "Bilhetes", dataKey: "issuing_date"},
          {title: "CIA", dataKey: "airline"},
          {title: "Pax", dataKey: "pax"},
          {title: "Embarque", dataKey: "boardingDate"},
          {title: "Trecho", dataKey: "from_to"},
          {title: "Localizador", dataKey: "flightLocator"},
          {title: "Pago", dataKey: "amountPaid"},
          {title: "Cancelamento", dataKey: "cancelCost"}];

        data = [];
        for(var i in $scope.Sales) {
          if($scope.Sales[i].checked) {
            data.push({
              issuing_date: $filter('date')(new Date($scope.Sales[i].issueDate + 'T12:00:00Z'),'dd/MM/yyyy'),
              airline: $scope.Sales[i].airline,
              pax: $scope.Sales[i].paxName,
              boardingDate: $filter('date')(new Date($scope.Sales[i].boardingDate + 'T12:00:00Z'),'dd/MM/yyyy'),
              from_to: $scope.Sales[i].from + '-' + $scope.Sales[i].to,
              flightLocator: $scope.Sales[i].flightLocator,
              amountPaid: $rootScope.formatNumber($scope.Sales[i].amountPaid),
              cancelCost: $rootScope.formatNumber($scope.Sales[i].cancelCost)
            });
          }
        }

        data.push({
          amountPaid: $rootScope.formatNumber($scope.getTotal()),
          cancelCost: $rootScope.formatNumber($scope.getTotalCancelCost())
        });

        doc.autoTable(coluns, data, {
          startY: start,
          margin: {horizontal: 15}
        });
        start = doc.autoTableEndPosY() + 20;

        doc.save('Analise de Risco.pdf');
      };

      $scope.cancelCost = function(){
        var cost = 0;
        if($scope.tabindex === true) {
          if($scope.Sales.length > 0) {
            for (var i = 0; i < $scope.Sales.length; i++) {
              cost = cost + $scope.Sales[i].totalCost;
            }
          }
        }
        return $scope.getTotal() - $scope.getTotalCancelCost();
      };

      $scope.getClass = function() {
        if( ($scope.getDueValue() + $scope.alreadyGenerated()) - $scope.cancelCost() > 0)
          return "label label-danger";
        else
          return "label label-info";
      };

      $scope.getStatus = function () {
        if( ($scope.getDueValue() + $scope.alreadyGenerated()) - $scope.cancelCost() > 0)
          return "NEGATIVO";
        else
          return "POSITIVO";
      };

      $scope.alreadyGenerated = function(){
        var total = 0;
        $scope.count2 = 0;
        if($scope.Billets.length > 0){
          var date = new Date($rootScope.formatServerDate(new Date()) + 'T12:00:00Z');
          var dueDate;
          for (var i = 0; i < $scope.Billets.length; i++) {
            if($scope.Billets[i].status == 'E' && $scope.Billets[i].actual_value > 0){
              dueDate = new Date($scope.Billets[i].due_date + 'T12:00:00Z');
              if(dueDate >= date){
                $scope.count2++;
                total = total + ($scope.Billets[i].actual_value - $scope.Billets[i].alreadyPaid);
              }
            }
          }
        }
        return total;
      };

      $scope.findDate = function(date){
        return new Date(date);
      };

      $scope.loadClients = function(){
        $.post("../backend/application/index.php?rota=/loadClient", $scope.session, function(result){
          $scope.clients = jQuery.parseJSON(result).dataset.clients;
        });
      };

      init = function() {
        $scope.tabindex = false;
        $scope.showReport = false;
        $scope.checkValidRoute();
        $('#gol').number( true, 2, ',', '.');
        $('#tam').number( true, 2, ',', '.');
        $('#avianca').number( true, 2, ',', '.');
        $('#azul').number( true, 2, ',', '.');
        $scope.loadClients();
      };
      return init();
    }
  ]).controller('RiskAnalisysContactModalCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $scope.open = function() {
        console.log($scope);
        $scope.selected = $scope.$parent.selected.name;
        $scope.main = $scope.$parent.$parent.$parent.main;
        $scope.session = $scope.$parent.$parent.$parent.session;
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "RiskAnalisysContactModalCtrl.html",
          controller: 'RiskAnalisysContactInstanceCtrl',
          resolve: {
            selected: function() {
              return $scope.selected;
            },
            main: function() {
              return $scope.main;
            },
            session: function() {
              return $scope.session;
            }
          }
        });
        modalInstance.result.then(function() {
        });
      };
    }
  ]).controller('RiskAnalisysContactInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'selected', 'main', 'session', function($scope, $rootScope, $modalInstance, logger, selected, main, session) {

      $scope.selected = selected;
      $scope.main = main;
      $scope.session = session;

      $.post("../backend/application/index.php?rota=/loadClientContacts", { hashId: $scope.session.hashId, data: $scope.selected }, function(result){
        $scope.contacts = jQuery.parseJSON(result).dataset;
        $scope.$digest();
      });

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;