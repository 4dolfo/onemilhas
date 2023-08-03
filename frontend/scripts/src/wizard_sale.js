(function () {
  'use strict';
  angular.module('app.wizardsale', ['ui.utils.masks']).controller('WizardSaleCtrl', [
    '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger) {
      var init;
      var isReturned;
      var partnerSelected;

      $scope.periods = ['Ultimos 30 dias', 'Mes Corrente', 'Semana Corrente', 'Hoje'];
      $scope.searchKeywords = '';
      $scope.filteredMiles = [];
      $scope.row = '';
      $scope.flight = {};
      $scope.paxs = [{pax_name: '', identification: '', paxBirthdate: '', gender: 'M', type: 'ADT'}];

      $scope.saleMethods = [
        { id: 2, method: 'JACK FOR' },
        { id: 3, method: 'Loja TAM Ponta Grossa' },
        { id: 4, method: 'Loja TAM Contagem' },
        { id: 4, method: 'Loja TAM M' },
        { id: 4, method: 'CONFIANÇA' },
        { id: 8, method: 'Flytour' },
        { id: 4, method: 'TAP' },
        { id: 5, method: 'Rextur Advance'},
        { id: 6, method: 'Outros'}
      ];

      $scope.refoundPossibilities = [
        { id: 0, method: 'Reembolso Solicitado' },
        { id: 1, method: 'Reembolso Confirmado' }
      ];

      $scope.safePossibilities = [
        { id: 0, method: 'Faturado' },
        { id: 1, method: 'Cartao' }
      ];

      $scope.flight.repricing_cost = 210;

      $scope.select = function(page) {
        var end, start;
        start = (page - 1) * $scope.numPerPage;
        end = start + $scope.numPerPage;
        return $scope.currentPageMiles = $scope.filteredMiles.slice(start, end);
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
        $scope.filteredMiles = $filter('filter')($scope.miles, $scope.searchKeywords);
        return $scope.onFilterChange();
      };

      $scope.order = function(rowName) {
        if ($scope.row === rowName) {
          return;
        }
        $scope.row = rowName;
        $scope.filteredMiles = $filter('orderBy')($scope.miles, rowName);
        return $scope.onOrderChange();
      };

      $scope.nextWizardStage = function() {
        $scope.tabindex = $scope.tabindex + 1;
        return $scope.tabindex;
      };

      $scope.priorWizardStage = function() {
        $scope.tabindex = $scope.tabindex - 1;
        return $scope.tabindex;
      };

      $scope.WizardStageProgress = function() {
        return (($scope.tabindex + 1) * 25);
      };

      $scope.getTotal =function(){
        return (parseFloat($scope.flight.commission) + parseFloat($scope.flight.cost) + parseFloat($scope.flight.tax));
      };

      $scope.getParcial = function(){
        return (parseFloat(parseFloat($scope.flight.cost) + parseFloat($scope.flight.tax)));
      };

      $scope.loadCards = function() {

        if(($scope.flight.partner != 'Rextur Advance' && $scope.flight.partner != 'JACK FOR' && $scope.flight.partner != 'CONFIANÇA' && $scope.flight.partner != 'Flytour') && $scope.flight.miles_original <= 0)
          return logger.logError("Milhas não podem ser 0");

        console.log($scope.wsale_formairline)
        //if($scope.wsale_formairline.$valid){
        if($scope.validarForm()){
          
          $scope.flight.cost_per_thousand = $('#cost_per_thousand').maskMoney('unmasked')[0];
          $scope.flight.commission = $('#commission').maskMoney('unmasked')[0];
          $scope.flight.du_tax = $('#du_tax').maskMoney('unmasked')[0];
          $scope.flight.repricing_cost = $('#repricing_cost').maskMoney('unmasked')[0];
          $scope.flight.safe_commission = $('#safe_commission').maskMoney('unmasked')[0];

          if($scope.common == true){
            if($scope.flight.airline == "TAM" || $scope.flight.airline == "LATAM"){
              if($scope.flight.partner == "Loja TAM Ponta Grossa"){
                for(var i in $scope.paxs) {
                  $scope.paxs[i].du_tax = 40;
                }
                $scope.flight.du_tax = 40;
              } else if($scope.flight.partner == "Loja TAM Contagem"){
                for(var i in $scope.paxs) {
                  $scope.paxs[i].du_tax = 25;
                }
                $scope.flight.du_tax = 25;
              } else if($scope.flight.partner == "Loja TAM M"){
                for(var i in $scope.paxs) {
                  $scope.paxs[i].du_tax = 28;
                }
                $scope.flight.du_tax = 28;
              }
            } else if ($scope.flight.airline == "AZUL"){
              for(var i in $scope.paxs) {
                  $scope.paxs[i].du_tax = 20;
                }
              $scope.flight.du_tax = 20;
            }
          }

          $scope.flight.miles_used = $scope.flight.miles_original;
          if(($scope.noCard == false) && ($scope.partner == false) && ($scope.safe == false) && ($scope.baggage == false) && ($scope.flight.partner != "JACK FOR") && ($scope.flight.partner != "Rextur Advance") && ($scope.flight.partner != "CONFIANÇA") && ($scope.flight.partner != "Flytour")) {
            cfpLoadingBar.start();
            if($scope.flight.miles_used == undefined){
              $scope.flight.miles_used = $scope.flight.miles_original;
            }
            if($scope.refound == true) {
              $scope.flight.miles_refound = $scope.flight.miles_used;
              $scope.flight.miles_used = -50000;
            }
            else if($scope.repricing == true) {
              $scope.flight.miles_used = $scope.flight.milesDiference;
            }

            $scope.resume_paxs = [];
            for(var i in $scope.paxs) {
              $scope.resume_paxs.push({
                pax_name: $scope.paxs[i].pax_name,
                paxLastName: '',
                paxAgnome: ''
              });
            }

            $.post("../backend/application/index.php?rota=/loadSalesMiles", { milesUsed: ($scope.flight.miles_used * $scope.paxs.length), airline: $scope.flight.airline, pax_quant: $scope.paxs.length, paxes: $scope.resume_paxs, from: $scope.flight.airport_description_from.substring(0, 3), to: $scope.flight.airport_description_to.substring(0, 3) }, function(result){
              $scope.miles = jQuery.parseJSON(result).dataset;
              console.log('MILES',$scope.miles);
              cfpLoadingBar.complete();
              if(!($scope.divers)){
                $scope.flight._boardingDate = new Date($scope.flight.boardingDate);
                $scope.flight._landingDate = new Date($scope.flight.landing_date);
              }
              $scope.tabindex = 2;
              $scope.search();
              return $scope.select($scope.currentPage);
            });
          } else {
            if(!($scope.divers)){
              $scope.flight._boardingDate = new Date($scope.flight.boardingDate);
              $scope.flight._landingDate = new Date($scope.flight.landing_date);
            }
            if($scope.safe) {
              $scope.flight.safeType = $scope.safePossibilities[0].method;
            }
            $scope.tabindex = 3;
          }
        } else {
          logger.logError("Favor preencher todos os campos obrigatorios!");
        }
      };

      $scope.$watch('[tabindex]', function () {
        if($scope.tabindex == 3) {
          $scope.loadPaxesUsedCards();
        }
      });

      $scope.loadPaxUsedCards = function(pax) {
        $.post("../backend/application/index.php?rota=/cards/loadPaxUsedCards", { pax_name: pax.pax_name, paxLastName: '', paxAgnome: '', identification: pax.identification, airline: $scope.flight.airline }, function (result) {
          $scope.cardsUsed = jQuery.parseJSON(result).dataset;
          pax.quant = $scope.cardsUsed.length;
          $scope.$digest();
        });
      };

      $scope.loadPaxesUsedCards = function () {
        $scope.resume_paxs = [];
        for(var i in $scope.paxs) {
          $scope.resume_paxs.push({
            pax_name: $scope.paxs[i].pax_name,
            paxLastName: '',
            paxAgnome: '',
            airline: $scope.flight.airline
          });
        }

        $.post("../backend/application/index.php?rota=/cards/loadAllPaxesCardsUsed", { data: $scope.resume_paxs }, function (result) {
          $scope.resume_paxs = jQuery.parseJSON(result).dataset;
          for(var i in $scope.resume_paxs) {
            $scope.paxs[i].quant = parseFloat($scope.resume_paxs[i].quant);
            $scope.paxs[i].code = $scope.resume_paxs[i].code;
          }
          $scope.$digest();
        });
      };

      $scope.openPaxUsedCards = function (pax) {
        $.post("../backend/application/index.php?rota=/cards/loadPaxUsedCards", { pax_name: pax.pax_name, paxLastName: '', paxAgnome: '', identification: pax.identification }, function (result) {
          $scope.cardsUsed = jQuery.parseJSON(result).dataset;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/modal_names_list.html",
            controller: 'ModalNamesLIstCtrl',
            resolve: {
              selected: function() {
                return $scope.selected;
              },
              paxPerCards: function() {
                return $scope.cardsUsed;
              }
            }
          });
        });
      };

      $scope.openAllPaxUsedCards = function() {
        $scope.resume_paxs = [];
        for(var i in $scope.paxs) {
          $scope.resume_paxs.push({
            pax_name: $scope.paxs[i].pax_name,
            paxLastName: '',
            paxAgnome: ''
          });
        }

        $.post("../backend/application/index.php?rota=/cards/loadAllPaxUsedCards", { data: $scope.resume_paxs }, function (result) {
          $scope.cardsUsed = jQuery.parseJSON(result).dataset;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/modal_names_list.html",
            controller: 'ModalNamesLIstCtrl',
            resolve: {
              selected: function() {
                return $scope.selected;
              },
              paxPerCards: function() {
                return $scope.cardsUsed;
              }
            }
          });
        });
      };

      $scope.$watch('order.client_name', function() {
        var client = _.find($scope.clients, function(o) { return o.name == $scope.order.client_name; })
        if(client && client != undefined && client != null) {
          $scope.showNextStep = true;
        } else {
          $scope.showNextStep = false;
        }
      });

      $scope.setCards = function() {
        $scope.cards = this.mile;
        // console.log($scope.flight_selected);
        if($scope.flight_selected.pax_name !== undefined) {
          $scope.flight_selected.card_number = $scope.cards.card_number;
          $scope.flight_selected.cards_id = $scope.cards.cards_id;
          $scope.flight_selected.card_registrationCode = $scope.cards.card_registrationCode;
          $scope.flight_selected.providerName = $scope.cards.name;
          $scope.flight_selected.provider_phone = $scope.cards.provider_phone;
          $scope.flight_selected.phoneNumberAirline = $scope.cards.phoneNumberAirline;
          $scope.flight_selected.celNumberAirline = $scope.cards.celNumberAirline;
        } else {
          for(var i in $scope.paxs) {
            $scope.paxs[i].cards_id = $scope.cards.cards_id;
            $scope.paxs[i].card_registrationCode = $scope.cards.card_registrationCode;
            $scope.paxs[i].card_number = $scope.cards.card_number;
            $scope.paxs[i].providerName = $scope.cards.name;
            $scope.paxs[i].provider_phone = $scope.cards.provider_phone;
            $scope.paxs[i].phoneNumberAirline = $scope.cards.phoneNumberAirline;
            $scope.paxs[i].celNumberAirline = $scope.cards.celNumberAirline;
          }
        }
        for(var j in $scope.paxs) {
          $scope.paxs[j].miles_used = $scope.flight.miles_original;
          $scope.paxs[j].airline = $scope.flight.airline;
        }
        if($scope.refound){
          $scope.flight.value = $scope.getMulta();
          $scope.flight.miles_used = $scope.flight.miles_refound;
        }
        $scope.isReturned = false;
        $.post("../backend/application/index.php?rota=/loadCardsData", {hashId: $scope.session.hashId, data: $scope.paxs}, function(result){
          $scope.cards= jQuery.parseJSON(result).dataset;
          $scope.tabindex = 3;
          $scope.cardPassword();
          $scope.codeAirport();
        });
      };

      $scope.codeAirport = function(){
        for (var i = 0; i < $scope.airports.length - 1; i++) {
          if($scope.airports[i].label == $scope.flight.airport_description_from)
            $scope.flight.airport_code_from = $scope.airports[i].code;
          if($scope.airports[i].label == $scope.flight.airport_description_to)
            $scope.flight.airport_code_to = $scope.airports[i].code;
          if(($scope.flight.airport_code_to != undefined) && ($scope.flight.airport_code_from != undefined))
            return null;
        }
      };

      $scope.cardPassword = function() {
        for (var j = 0; $scope.cards.length > j; j++) {
          for (var i = 0; $scope.paxs.length > i; i++) {
            if($scope.cards[j].cards_id == $scope.paxs[i].cards_id) {
              $scope.paxs[i].recovery_password = $scope.decript($scope.cards[j].recovery_password);
              $scope.paxs[i].token = $scope.cards[j].token;
              if($scope.paxs[i].airline == "TAM" || $scope.paxs[i].airline == "LATAM"){
                $scope.paxs[i].access_id = $scope.cards[j].access_id;
                $scope.paxs[i].access_password = $scope.decript($scope.cards[j].access_password);
              }
              else{
                $scope.paxs[i].access_id = " - ";
                $scope.paxs[i].access_password = " - ";
              }
            }
          }
        }
        $scope.$apply();
      };

      $scope.decript = function(code){
        var data = code.split('320AB');
        var finaly = '';
        for (var j = 0; data.length > j; j++) {
          finaly = finaly + (String.fromCharCode(data[j] / 320));
        }
        return finaly;
      };

      $scope.checkOperation = function(value){
        switch (value) {
          case 'D':
            $scope.noCard = false;
            $scope.partner = false;
            $scope.common = false;
            $scope.divers = true;
            $scope.safe = false;
            $scope.refound = false;
            $scope.repricing = false;
            $scope.hotel = false;
            $scope.baggage = false;
            return ;
          case 'C':
            $scope.divers = false;
            $scope.partner = false;
            $scope.common = false;
            $scope.noCard = true;
            $scope.safe = false;
            $scope.refound = false;
            $scope.repricing = false;
            $scope.hotel = false;
            $scope.baggage = false;
            return ;
          case 'P':
            $scope.divers = false;
            $scope.noCard = false;
            $scope.common = false;
            $scope.partner = true;
            $scope.safe = false;
            $scope.refound = false;
            $scope.repricing = false;
            $scope.hotel = false;
            $scope.baggage = false;
            return ;
          case 'S':
            $scope.divers = false;
            $scope.noCard = false;
            $scope.common = false;
            $scope.partner = false;
            $scope.safe = true;
            $scope.refound = false;
            $scope.repricing = false;
            $scope.hotel = false;
            $scope.baggage = false;
            return ;
          case 'R':
            $scope.divers = false;
            $scope.noCard = false;
            $scope.common = false;
            $scope.partner = false;
            $scope.safe = false;
            $scope.refound = true;
            $scope.repricing = false;
            $scope.hotel = false;
            $scope.baggage = false;
            return ;
          case 'X':
            $scope.divers = false;
            $scope.noCard = false;
            $scope.common = false;
            $scope.partner = false;
            $scope.safe = false;
            $scope.refound = false;
            $scope.hotel = false;
            $scope.repricing = true;
            $scope.baggage = false;
            return ;
          case 'H':
            $scope.divers = false;
            $scope.noCard = false;
            $scope.common = false;
            $scope.partner = false;
            $scope.safe = false;
            $scope.refound = false;
            $scope.repricing = false;
            $scope.hotel = true;
            $scope.baggage = false;
            return ;
          case 'B':
            $scope.divers = false;
            $scope.noCard = false;
            $scope.common = false;
            $scope.partner = false;
            $scope.safe = false;
            $scope.refound = false;
            $scope.repricing = false;
            $scope.hotel = false;
            $scope.baggage = true;
            return ;
          default:
            $scope.divers = false;
            $scope.noCard = false;
            $scope.partner = false;
            $scope.common = true;
            $scope.safe = false;
            $scope.refound = false;
            $scope.repricing = false;
            $scope.hotel = false;
            $scope.baggage = false;
            return ;
        }
        //$scope.$apply();
      };

      $scope.validarForm = function(){
        if($scope.flight.airline == '')
          return false;
        if($scope.flight.airport_description_from == '' && ($scope.noCard||$scope.partner||$scope.common||$scope.refound||$scope.repricing||$scope.baggage)){
          return false;
        }
        if($scope.flight.airport_description_to == '' && ($scope.noCard||$scope.partner||$scope.common||$scope.safe||$scope.refound||$scope.repricing||$scope.baggage)){
          return false;
        }
        if($scope.flight.boardingDate == '' && ($scope.noCard||$scope.partner||$scope.common||$scope.safe||$scope.refound||$scope.repricing||$scope.hotel)){
          return false;
        }
        if($scope.flight.landing_date == '' && ($scope.noCard||$scope.partner||$scope.common||$scope.safe||$scope.refound||$scope.repricing||$scope.hotel)){
          return false;
        }
        if($scope.flight.flight_time == '' && ($scope.noCard||$scope.partner||$scope.common||$scope.refound||$scope.repricing||$scope.hotel)){
          return false;
        }
        for(let i in $scope.paxs){
          if($scope.paxs[i].pax_name == '' && ($scope.noCard||$scope.partner||$scope.common||$scope.safe||$scope.refound||$scope.repricing||$scope.hotel)){
            return false;
          }
        }
        if($scope.flight.miles_original == '' && ($scope.divers||$scope.partner||$scope.common||$scope.refound)){
          return false;
        }
        if($scope.flight.cost == '' && ($scope.divers||$scope.partner||$scope.common||$scope.refound)){
          return false;
        }
        if($scope.flight.tax == '' && ($scope.divers||$scope.partner||$scope.common||$scope.refound)){
          return false;
        }
        if($scope.flight.commission == '' && ($scope.noCard)){
          return false;
        }
        if($scope.flight.amount_paid == '' && ($scope.partner||$scope.common||$scope.safe||$scope.refound)){
          return false;
        }
        if($scope.flight.safe_commission == '' && ($scope.safe)){
          return false;
        }
        if($scope.flight.milesDiference == '' && ($scope.repricing)){
          return false;
        }
        if($scope.flight.repricing_cost == '' && ($scope.repricing)){
          return false;
        }
        if($scope.flight.costPerThousand == '' && ($scope.divers||$scope.repricing)){
          return false;
        }
        return true;
      };

      $scope.validCardUsed = function() {
        if ($scope.cards == undefined) {
          logger.logError('Cartão deve ser selecionado');
          return false;
        }
      };

      $('#wsalereturnDate').on('blur', function(obj, datum) {
        $scope.isReturned = !($scope.wsale.returnDate == undefined);
        if ($scope.wsale.returnDate == undefined) {
          $scope.wsale.return_flight = '';
          $scope.wsale.return_flightHour = '';
        }
        $scope.$apply();
        return $scope.isReturned;
      });

      $scope.numPerPageOpt = [3, 5, 10, 20];
      $scope.numPerPage = $scope.numPerPageOpt[2];
      $scope.currentPage = 1;
      $scope.currentPageMiles = [];

      $scope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
        return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
      };

      $scope.setToClientDate = function(datetime) {
        return $rootScope.formatClientDate(datetime);
      };

      $scope.getMulta = function(){
        var actualDate = new Date();
        var selectedDate = new Date($scope.flight._boardingDate);
        if($scope.flight.airline == "AZUL"){
          if(actualDate < selectedDate){
            return (parseFloat($scope.flight.amount_paid).toFixed(2) - 190);
          } else return (parseFloat($scope.flight.amount_paid).toFixed(2) - 240);
        }else if($scope.flight.airline == "GOL"){
          if(actualDate < selectedDate){
            return (parseFloat($scope.flight.amount_paid).toFixed(2) - 250);
          } else return (parseFloat($scope.flight.amount_paid).toFixed(2) - 0);
        }else if($scope.flight.airline == "TAM" || $scope.flight.airline == "LATAM"){
          if(actualDate < selectedDate){
            return (parseFloat($scope.flight.amount_paid).toFixed(2) - 250);
          } else return (parseFloat($scope.flight.amount_paid).toFixed(2) - 290);
        }
      };

      $scope.saveOrder = function() {
        for(var i in $scope.paxs) {
          $scope.paxs[i]._paxBirthdate = $rootScope.formatServerDate($scope.paxs[i].paxBirthdate);
        }
        if(!($scope.flight._boardingDate)){
          $scope.flight._boardingDate = new Date($scope.flight.boardingDate);
          $scope.flight._landingDate = new Date($scope.flight.landing_date);
        }
        $scope.flight._boardingDate = $rootScope.formatServerDateTime($scope.flight._boardingDate);
        $scope.flight._landingDate = $rootScope.formatServerDateTime($scope.flight._landingDate);
        $scope.flight.kickback = ($scope.flight.amount_paid - $scope.flight.cost - $scope.flight.tax);

        $scope.flight.status = 'Pendente';
        $scope.codeAirport();
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/saveOrder", {data: $scope.flight, paxs: $scope.paxs, order: $scope.order, divers: $scope.divers, noCard: $scope.noCard, partner: $scope.partner, safe: $scope.safe, baggage: $scope.baggage, refound: $scope.refound, repricing: $scope.repricing, hotel: $scope.hotel}, function(result){
          logger.logSuccess(jQuery.parseJSON(result).message.text);
        });
        cfpLoadingBar.complete();
      };

      $scope.mailOrder = function() {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/mailOrder", {hashId: $scope.session.hashId, data: $scope.wsalemail, emailType: 'EMAIL-GMAIL'}, function(result){
          if (jQuery.parseJSON(result).message.type == 'S'){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.saveOrder();
          } else {
            logger.logError(jQuery.parseJSON(result).message.text);
          }
        });
        cfpLoadingBar.complete();
      };

      $scope.checkLimit = function() {
        $scope.order.totalValue = $('#total_value').maskMoney('unmasked')[0];
        $.post("../backend/application/index.php?rota=/checkLimitSale", {hashId: $scope.session.hashId, data: $scope.order}, function(result){
          $scope.check = jQuery.parseJSON(result).dataset;
          if($scope.check.partner_limit == 'true') {
            logger.logError("Limite sera ultrapassado com emissão, consulte o comercial");
          }
          if($scope.check.paymentType == 'Antecipado') {
            logger.logError("Cliente Antecipado, consulte o comercial");
          }
          if($scope.check.status == 'Bloqueado') {
            logger.logError("Cliente Bloqueado, consulte o comercial");
          }
        });
      };

      $scope.getWeekdayGol = function() {
        var day = new Date(date).getDay();
        console.log(date);
        switch (day) {
          case 0: return 'Domingo';
          case 1: return 'Segunda-feira';
          case 2: return 'Terça-feira';
          case 3: return 'Quarta-feira';
          case 4: return 'Quinta-feira';
          case 5: return 'Sexta-feira';
          case 6: return 'Sábado';
        }
      };

      $scope.getMonth = function(date) {
        var month = date.getMonth();
        switch (month) {
          case 1: return 'Janeiro';
          case 2: return 'Fevereiro';
          case 3: return 'Março';
          case 4: return 'Abril';
          case 5: return 'Maio';
          case 6: return 'Junho';
          case 7: return 'Julho';
          case 8: return 'Agosto';
          case 9: return 'Setembro';
          case 10: return 'Outubro';
          case 11: return 'Novembro';
          case 12: return 'Dezembro';
        }
        
      };

      $scope.getTotalTax = function() {
        var total = 0;
        for(var i in $scope.paxs) {
          total += $scope.flight.tax;
        }
        return total;
      };

      $scope.validClient = function() {
        if ($scope.wsale_client.$valid) {
          $scope.nextWizardStage();
          $scope.checkLimit();
        }
      };

      $scope.$watch('tabindex', function() {
        if($scope.tabindex == 1) {
          $scope.flight.tax = $rootScope.formatNumber($scope.flight.tax);
          $scope.flight.amount_paid = $rootScope.formatNumber($scope.flight.amount_paid);
        }
      });

      $scope.ecript = function(code){
        var data = code.split("");
        var finaly = '';
        for (var j = 0; data.length > j; j++) {
          finaly = finaly + (data[j].charCodeAt(0) * 320) + '320AB';
        }
        return finaly;
      };

      $scope.setSafe = function () {
        $scope.flight.safe_commission = $scope.flight.amount_paid * 0.1;
      };

      $scope.removePax = function() {
        if($scope.paxs.length > 1) {
          $scope.paxs.pop();
        }
      };

      $scope.addPax = function() {
        if($scope.paxs.length < 9) {
          $scope.paxs.push({
            pax_name: '',
            identification: '',
            paxBirthdate: '',
            gender: 'M',
            type: 'ADT'
          });
        }
      };

      $scope.flightLocator = function() {
        for(var i in $scope.paxs) {
          $scope.paxs[i].flight_locator = $scope.flight.flight_locator;
        }
      };

      init = function() {
        $scope.checkValidRoute();
        $scope.tabindex = 0;
        $scope.isReturned = true;
        $scope.order = {};
        $scope.divers = false;
        $scope.noCard = false;
        $scope.partner = false;
        $scope.common = true;
        $scope.safe = false;
        $scope.refound = false;
        $scope.repricing = false;
        $scope.hotel = false;
        $scope.baggage = false;
        $scope.flight_selected = {};

        $('#miles').number( true, 0, ',', '.');
        $('#miles_card').number( true, 0, ',', '.');
        $('#milesDiference').number( true, 0, ',', '.');
        $('#time').number( true, 2, ':');

        $("#value").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#tax").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#amount_paid").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#cost_per_thousand").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#commission").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#du_tax").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#repricing_cost").maskMoney({thousands:'.',decimal:',', precision: 2});
        $("#safe_commission").maskMoney({thousands:'.',decimal:',', precision: 2});

        $("#total_value").maskMoney({thousands:'.',decimal:',', precision: 2})

        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadAirline", $scope.session, function(result){
            $scope.airlines = jQuery.parseJSON(result).dataset;
        });

        $.post("../backend/application/index.php?rota=/loadClientsNames", $scope.session, function(result){
            $scope.clients = jQuery.parseJSON(result).dataset;
            cfpLoadingBar.complete();
        });

        $.post("../backend/application/index.php?rota=/loadAirport", $scope.session, function(result){
            $scope.airports = jQuery.parseJSON(result).dataset;
        });

        $.post("../backend/application/index.php?rota=/loadIssuing", $scope.session, function(result){
          $scope.issuings = jQuery.parseJSON(result).dataset;
        });

        $('#wsalemilesused').number( true, 0, ',', '.');
      };
      return init();
    }
  ]).controller('CardTaxCardData', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {


      $scope.open = function() {

        $.post("../backend/application/index.php?rota=/loadInternalCards", $scope.session, function(result){
          $scope.internalCards = jQuery.parseJSON(result).dataset;

          $scope.flight_selected = $scope.$parent.flight;
          $scope.decript = function(code){
            var data = code.split('320AB');
            var finaly = '';
            for (var j = 0; data.length > j; j++) {
              finaly = finaly + (String.fromCharCode(data[j] / 320));
            }
            return finaly;
          };

          for(var i in $scope.internalCards){
            $scope.internalCards[i].password = $scope.decript($scope.internalCards[i].password);
          }
          $scope.type = $scope.$parent.common;

          if($scope.flight_selected.card_number != ' - '){

            $.post("../backend/application/index.php?rota=/loadCardProvider", {hashId: $scope.session.hashId, data: $scope.flight_selected}, function(result){
              $scope.TaxCard = jQuery.parseJSON(result).dataset;
              if(($scope.TaxCard.length == 1) && ($scope.flight_selected.tax_card == undefined)){
                $scope.flight_selected.tax_card = $scope.TaxCard[0].card_number;
                $scope.flight_selected.tax_password = $scope.decript($scope.TaxCard[0].password);
                $scope.flight_selected.tax_cardType = $scope.TaxCard[0].card_type;
                $scope.flight_selected.tax_dueDate = $scope.TaxCard[0].due_date;
                $scope.flight_selected.tax_providerName = $scope.TaxCard[0].provider_name;
                $scope.flight_selected.provider_registration = $scope.TaxCard[0].provider_registration;
                $scope.flight_selected.provider_adress = $scope.TaxCard[0].provider_adress;
                $scope.flight_selected.birthdate = $scope.TaxCard[0].birthdate;
                $scope.flight_selected.providerAdress = $scope.TaxCard[0].providerAdress;
              }
              else{
                $scope.TaxCard = {};
              }
              $scope.originalFlight = angular.copy($scope.flight_selected);
              var modalInstance;
              modalInstance = $modal.open({
                templateUrl: "CardTaxCardData.html",
                controller: 'CardTaxCardInstanceCtrl',
                resolve: {
                  internalCards: function() {
                    return $scope.internalCards;
                  },
                  originalFlight: function(){
                    return $scope.originalFlight;
                  },
                  flight_selected: function() {
                    return $scope.flight_selected;
                  },
                  TaxCard: function(){
                    return $scope.TaxCard;
                  }
                }
              });

              modalInstance.result.then((function(TaxCard) {
                $scope.flight_selected.tax_card = TaxCard.tax_card;
                $scope.flight_selected.tax_password = TaxCard.tax_password;
                $scope.flight_selected.tax_cardType = TaxCard.tax_cardType;
                $scope.flight_selected.tax_dueDate = TaxCard.tax_dueDate;
                $scope.flight_selected.tax_providerName = TaxCard.tax_providerName;
              }));
            });
          }
        });
      };
    }
  ]).controller('CardTaxCardInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'internalCards', 'originalFlight', 'flight_selected', 'TaxCard', function($scope, $rootScope, $modalInstance, $filter, internalCards, originalFlight, flight_selected, TaxCard) {
      $scope.internalCards = internalCards;
      $scope.flight_selected = originalFlight;
      $scope.TaxCard = TaxCard;
      $scope.resume_creditCards = $filter('filter')($scope.internalCards, $scope.flight_selected.airline);
      $scope.resume_creditCards.push({card_number: 'OUTRO'});

      $scope.findDate = function(date) {
        var data = new Date(date);
        data.setDate(data.getDate() + 1);
        console.log(data);
        return data;
      };

      $scope.search = function(){
        $scope.TaxCard = $filter('filter')($scope.resume_creditCards, $scope.filter);
        $scope.flight_selected.tax_card = $scope.TaxCard[0].card_number;
        $scope.flight_selected.tax_password = $scope.TaxCard[0].password;
        $scope.flight_selected.tax_cardType = $scope.TaxCard[0].card_type;
        $scope.flight_selected.tax_dueDate =  new Date($scope.TaxCard[0].due_date);
        $scope.flight_selected.tax_providerName = $scope.TaxCard[0].provider_name;
        $scope.flight_selected.provider_registration = $scope.TaxCard[0].provider_registration;
        $scope.flight_selected.providerAdress = $scope.TaxCard[0].providerAdress;
        $scope.flight_selected.provider_adress = $scope.TaxCard[0].provider_adress;
        $scope.flight_selected.birthdate =  new Date($scope.TaxCard[0].birthdate);
      };
      $scope.ok = function() {
        flight_selected = $scope.flight_selected;
        $modalInstance.close($scope.flight_selected);
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('FlightData', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $scope.open = function() {
        $scope.flight_selected = this.pax;
        $scope.originalFlight = angular.copy($scope.flight_selected);

        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "FlightData.html",
          controller: 'FlightDataInstanceCtrl',
          resolve: {
            flight_selected: function() {
              return $scope.flight_selected;
            },
            originalFlight: function() {
              return $scope.originalFlight;
            }
          }
        });
        modalInstance.result.then(function(flight_selected) {
          for(var i in $scope.$parent.paxs) {
            if($scope.$parent.paxs[i].pax_name == flight_selected.pax_name) {
              $scope.$parent.paxs[i].miles_used = flight_selected.miles_used;
              $scope.$parent.paxs[i].du_tax = flight_selected.du_tax;
              $scope.$parent.paxs[i].money = flight_selected.money;
              $scope.$parent.paxs[i].flight_locator = flight_selected.flight_locator;
              $scope.$parent.paxs[i].ticket_code = flight_selected.ticket_code;
              $scope.$parent.paxs[i].baggage = flight_selected.baggage;
              $scope.$parent.paxs[i].special_seat = flight_selected.special_seat;
            }
          }
          $scope.$parent.flight.du_tax = flight_selected.du_tax;
        });
      };
    }
  ]).controller('FlightDataInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'flight_selected', 'originalFlight', function($scope, $rootScope, $modalInstance, flight_selected, originalFlight) {
      $scope.flight_selected = originalFlight;
      $scope.ok = function() {
        flight_selected = $scope.flight_selected;
        $modalInstance.close($scope.flight_selected);
      };

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('FindAllCardsManualCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

      $scope.open = function() {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "FindAllCardsManualCtrl.html",
          controller: 'FindAllCardsManualInstanceCtrl',
          resolve: {
            main: function() {
              return $scope.$parent.$parent.main;
            },
            hashId: function() {
              return $scope.$parent.$parent.$parent.session.hashId;
            }
          }
        });
        modalInstance.result.then(function() {
          $.post("../backend/application/index.php?rota=/loadSalesMiles", {hashId: $scope.session.hashId, milesUsed: -50000, airline: $scope.$parent.flight.airline}, function(result){
            $scope.$parent.miles = jQuery.parseJSON(result).dataset;
            $scope.$parent.search();
            $scope.$parent.$apply();
          });
        });
      };

    }
  ]).controller('FindAllCardsManualInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', 'logger', 'main', 'hashId', function($scope, $rootScope, $modalInstance, logger, main, hashId) {
      $scope.main = main;
      $scope.hashId = hashId;
      $scope.flight_selected = {pinCode: ''};

      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };

      $scope.check = function() {
        $.post("../backend/application/index.php?rota=/checkAcessCode", {hashId: $scope.hashId, data: $scope.flight_selected}, function(result){
          $scope.response= jQuery.parseJSON(result).dataset;

          if($scope.response.valid == 'true'){
            $modalInstance.close();
          } else {
            logger.logError("Dados não conferem!");
          }
        });
      };
    }
  ]);
})();
;
