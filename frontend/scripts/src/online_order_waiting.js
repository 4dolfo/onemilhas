(function () {
    'use strict';
    angular.module('app.table').controller('OnlineOrderWaitingCtrl', [
      '$scope', '$rootScope', '$filter', '$modal', 'cfpLoadingBar', 'logger', 'FileUploader', function($scope, $rootScope, $filter, $modal, cfpLoadingBar, logger, FileUploader) {
        var init;
        var partnerSelected;
        $scope.searchKeywords = '';
        $scope.searchKeywordsMiles = '';
        $scope.filteredOnlineOrders = [];
        $scope.row = '';      
        $scope.previousIndex = 0;
        $scope.currentPage = 1;
        $scope.card = [];
  
        $scope.select = function(page) {
          var end, start;
          start = (page - 1) * $scope.numPerPage;
          end = start + $scope.numPerPage;
          return $scope.currentPageOnlineOrder = $scope.filteredOnlineOrders.slice(start, end);
        };
        
        $scope.onFilterChange = function() {
          $scope.select($scope.currentPage);
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
  
        $rootScope.$on('checkedPermission', function(event, args) {
          $scope.setSelected(true);
        });
  
        $rootScope.$on('fillEmailBLoqued', function(event, args) {
          $scope.fillEmailBLoqued(args);
        });
  
        $scope.fillEmailBLoqued = function(args) {
          $scope.wsalemail.emailpartner = args.email;
          $scope.wsalemail.mailcco = undefined;
          $scope.wsalemail.subject = '[One Milhas] - Pedido de emissão';
          
          var date = new Date();
          date.setUTCHours(date.getUTCHours() - 2);
          
          if($scope.response) {
            if($scope.response.subClientEmail) {
              $scope.wsalemail.emailpartner = $scope.response.subClientEmail;
            }
            if($scope.check48hours()) {
              $scope.wsalemail.emailContent = "Prezado Parceiro, <br><br>"+
                "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>"+
                "Atenciosamente.<br>One Milhas";
            } else if ($scope.response.status == "Bloqueado") {
  
              $scope.wsalemail.emailContent = "Olá, <br><br>"+
                "Antes de seguirmos com a emissão, nosso setor financeiro solicita contato para as devidas atualizações.<br>"+
                "Horário de atendimento do setor financeiro: Segunda a Sexta de 09:00 as 18:00 ( horário de Brasília )  <br><br>"+
                "Email: financeiro@onemilhas.com.br <br>Telefones: (31) 3972-1929 <br><br>";
  
              $scope.wsalemail.mailcco = "comercial@onemilhas.com.br";
  
              $scope.wsalemail.emailContent += "Atenciosamente.<br>One Milhas";
  
            } else if ($scope.response.status == "Pendente") {
              $scope.wsalemail.emailContent = "Caro Parceiro, <br><br>"+
                "Cliente com status PENDENTE!"+
                "<br>"+$scope.response.client_name+"<br>"+
                "Atenciosamente.<br>One Milhas";
  
              if((date.getDay() > 0 && date.getDay() < 6) && (date.getUTCHours() >= 9 && date.getUTCHours() <= 21)) {
                $scope.wsalemail.emailpartner = 'comercial@onemilhas.com.br';
              } else if(date.getDay() > 0 && date.getDay() < 6) {
                $scope.wsalemail.mailcco = "comercial@onemilhas.com.br";
              } else {
                $scope.wsalemail.emailpartner = 'comercial@onemilhas.com.br';
              }
  
              if((date.getDay() > 0 && date.getDay() < 6) && (date.getUTCHours() > 21 || date.getUTCHours() <= 2)) {
                $scope.wsalemail.mailcco = "comercial@onemilhas.com.br";
                $scope.wsalemail.emailContent = "Caro Parceiro.<br><br>" +
                    "Agradecemos a confiança ao enviar a 1º emissão.Dentre as próximas horas iremos enviar nosso protocolo de ativação finalizando nosso cadastro.<br>" +
                    "Para seguirmos com essa emissão gentileza entrar em contato com nosso setor comercial para alinharmos os últimos detalhes.<br><br>" +
                    "(31) 3972-1929<br><br>" +
                    "comercial@onemilhas.com.br<br><br>" +
                    "Atenciosamente<br>Equipe One Milhas";
              }
  
            } else if($scope.response.paymentType == "Antecipado") {
  
              $scope.wsalemail.mailcco = "comercial@onemilhas.com.br";
              $scope.wsalemail.emailContent = "Prezado Parceiro, <br><br>"+
                "Para darmos andamento a sua solicitação, pedimos que nos encaminhe o comprovante de pagamento neste mesmo e-mail: emissao@onemilhas.com.br . Aguardamos o recebimento do comprovante e liberação do setor financeiro para seguirmos com o processo..<br>"+               
                "Email: financeiro@onemilhas.com.br<br> Telefones: (31) 3972-1929<br><br>";
  
              $scope.wsalemail.emailContent += "<table style='text-align: center; border-collapse: collapse;' border='1' width='95%' align='center'><tbody>" +
                "<tr bgcolor='#FFFFFF'><td>&nbsp;</td><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Caixa</strong></span></td><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Bradesco</strong></span></td>" +
                "<td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Ita&uacute;</strong></span></td><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Brasil</strong></span></td>" +
                "<td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Santander</strong></span></td></tr><tr><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Banco:</strong></span></td>"+
                "<td>104</td><td>237</td><td>341</td><td>001</td><td>033</td></tr><tr><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Ag&ecirc;ncia:</strong></span></td>"+
                "<td>2922</td><td>3420</td><td>1582</td><td>-</td><td>4232</td></tr><tr><td bgcolor='#5D7B9D'><span style='color: #ffffff;'><strong>Conta:</strong></span></td>"+
                "<td>2643-6</td><td>29429-2</td><td>33678-8</td><td>-</td>"+
                "<td>13004689-8</td></tr><tr><td colspan='6'><strong>MMS VIAGENS LTDA</strong><br /><strong>CNPJ: 29.632.355/0001-85</strong></td></tr></tbody></table><br><br>";
  
              $scope.wsalemail.emailContent += "Atenciosamente.<br>One Milhas";
            } else if($scope.response.partner_limit == 'true') {
  
              $scope.wsalemail.emailContent = "Caro Parceiro, <br><br>"+
                "Seu limite foi excedido, com isso pedimos que entre em contato com o setor financeiro para seguirmos com a emissão.<br><br>"+
                "Email: financeiro@onemilhas.com.br<br> Telefones: 3972-1929<br><br>"+
                "Atenciosamente.<br>One Milhas";
  
                if(date.getDay() == 0 || date.getDay() == 6) {
                  $scope.wsalemail.emailpartner = 'comercial@onemilhas.com.br;financeiro@onemilhas.com.br';
                }
  
                if((date.getDay() > 0 && date.getDay() < 6) && (date.getUTCHours() >= 9 && date.getUTCHours() <= 23)) {
                  $scope.wsalemail.emailpartner = 'comercial@onemilhas.com.br;financeiro@onemilhas.com.br';
                }
  
                if((date.getDay() > 0 && date.getDay() < 6) && (date.getUTCHours() > 23 || date.getUTCHours() <= 2) ) {
                  $scope.wsalemail.emailpartner = args.email;
                  $scope.wsalemail.mailcco = "comercial@onemilhas.com.br";
                }
  
              }
          } else {
            $scope.wsalemail.emailContent = "Prezado Parceiro, <br><br>"+
              "Conforme procedimentos internos, não emitimos trechos GOL com menos de 48 horas para o embarque.<br><br>"+
              "Atenciosamente.<br>One Milhas";
          }
          $scope.uploader.clearQueue();
  
          if($scope.response) {
            if($scope.response.client_name) {
              $scope.wsalemail.emailContent += "<br><br>Cliente: " + $scope.response.client_name;
            }
          }
          $scope.wsalemail.emailContent += "<br><br><br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Trechos</font></td></tr>"+
          "<tr><td>CIA</td><td>VOO</td><td>Conexões</td><td>Embarque</td><td>Desembarque</td><td>Duração</td><td>Origem</td><td>Destino</td></tr>";
          for (var i = 0;$scope.resume_flights.length > i; i++) {
            var testes = $scope.resume_flights[i].connection.split(' ');
            var connections = '';
            for (var j = 0; j < testes.length; j++) {
              connections += testes[j] + "<br>";
            }
            $scope.wsalemail.emailContent += "<tr><td>"+$scope.resume_flights[i].airline+"</td><td>"+$scope.resume_flights[i].flight+"</td><td>"+connections+"</td><td>"+$filter('date')($rootScope.findDate($scope.resume_flights[i].boarding_date), 'dd/MM/yyyy')+"<br>"+$filter('date')($rootScope.findDate($scope.resume_flights[i].boarding_date), 'HH:mm:ss')+"</td><td>"+$filter('date')($rootScope.findDate($scope.resume_flights[i].landing_date), 'dd/MM/yyyy')+"<br>"+$filter('date')($rootScope.findDate($scope.resume_flights[i].landing_date), 'HH:mm:ss')+"</td><td>"+$scope.resume_flights[i].flight_time+"</td><td>"+$scope.resume_flights[i].airport_description_from+"</td><td>"+$scope.resume_flights[i].airport_description_to+"</td></tr>";
          }
          $scope.wsalemail.emailContent += "</tbody></table>"+
            "<br><table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'>Passageiros</font></td></tr>";
          for (var i = 0;$scope.resume_paxs.length > i; i++) {
            var pax_name = $scope.resume_paxs[i].pax_name;
            if($scope.resume_paxs[i].paxLastName) {
              pax_name += ' ' + $scope.resume_paxs[i].paxLastName;
            }
            if($scope.resume_paxs[i].paxAgnome) {
              pax_name += ' ' + $scope.resume_paxs[i].paxAgnome;
            }
            $scope.wsalemail.emailContent += "<tr><td colspan='2' bgcolor='#5D7B9D'><font color='#ffffff'> Passageiro "+(i+1)+" </font></td></tr><tr><td>Nome:</td><td>" + pax_name + "</td></tr><tr><td>Identificação:</td><td>"+$scope.resume_paxs[i].identification+"</td></tr><tr><td>Data Nascimento:</td><td>"+$filter('date')($scope.resume_paxs[i].birhtdate, 'dd/MM/yyyy')+"</td></tr>";
            if($scope.resume_paxs[i].is_child != "N")
              $scope.wsalemail.emailContent += "<tr><td></td><td>CHD</td></tr>";
            if($scope.resume_paxs[i].is_newborn != "N")
              $scope.wsalemail.emailContent += "<tr><td></td><td>INF</td></tr>";
          }
          $scope.wsalemail.emailContent += "</tbody></table><br><br>";
  
          if($scope.response) {
            if($scope.response.status == 'Bloqueado' || $scope.response.paymentType == 'Antecipado' || $scope.response.partner_limit == 'true') {
  
              $scope.wsalemail.emailContent += "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>"+
              "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
              var miles = '';
              var pricing = '';
              var NumberOfAdult = 0;
              var NumberOfChild = 0;
              var NumberOfNewborn = 0;
  
              for(var a in $scope.resume_paxs) {
                if($scope.resume_paxs[a].is_child == 'S') {
                  NumberOfChild++;
                } else if($scope.resume_paxs[a].is_newborn == 'S') {
                  NumberOfNewborn++;
                } else {
                  NumberOfAdult++;
                }
              }
  
              var connections;
              for(var b in $scope.resume_flights) {
                var miles = '';
                var pricing = '';
  
                var testes = $scope.resume_flights[b].connection.split(' ');
                connections = '';
                for (var j = 0; j < testes.length; j++) {
                  connections += testes[j] + "<br>";
                }
  
                if(NumberOfAdult > 0) {
                  miles = miles + 'ADT: ' + $rootScope.formatNumber($scope.resume_flights[b].miles_per_adult, 0) +' (x' + $rootScope.formatNumber(NumberOfAdult, 0) + ')';
                  pricing = pricing + 'ADT: ' + $rootScope.formatNumber($scope.resume_flights[b].cost_per_adult) + ' (x' + $rootScope.formatNumber(NumberOfAdult, 0) + ')';
                }
                if(NumberOfChild > 0) {
                  miles = miles + '<br>CHD: ' + $rootScope.formatNumber($scope.resume_flights[b].miles_per_child, 0) +' (x' + $rootScope.formatNumber(NumberOfChild, 0) + ')';
                  pricing = pricing + '<br>CHD: ' + $rootScope.formatNumber($scope.resume_flights[b].cost_per_child) + ' (x' + $rootScope.formatNumber(NumberOfChild, 0) + ')';
                }
                if(NumberOfNewborn > 0) {
                  miles = miles + '<br>INF: ' + $rootScope.formatNumber($scope.resume_flights[b].miles_per_newborn, 0) +' (x' + $rootScope.formatNumber(NumberOfNewborn, 0) + ')';
                  pricing = pricing + '<br>INF: ' + $rootScope.formatNumber($scope.resume_flights[b].cost_per_newborn) + ' (x' + $rootScope.formatNumber(NumberOfNewborn, 0) + ')';
                }
  
                $scope.wsalemail.emailContent += "<tr><td>"+$scope.resume_flights[b].airline+"</td><td>"+$scope.resume_flights[b].flight+"</td>" +
                  "<td>"+miles+"</td><td>"+pricing+"</td><td>"+$rootScope.formatNumber($scope.resume_flights[b].tax)+"</td><td>"+ NumberOfAdult + " ADT<br>" + NumberOfChild + " CHD<br>" + NumberOfNewborn + " INF" +"</td>" +
                  "<td>"+$rootScope.formatNumber($scope.resume_flights[b].original_miles, 0)+"</td><td>"+ $rootScope.formatNumber($scope.resume_flights[b].cost + $scope.resume_flights[b].tax)+"</td></tr>";
              }
  
              $scope.wsalemail.emailContent += "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>"+
                "<td bgcolor='#5D7B9D'><font color='#ffffff'>" + $rootScope.formatNumber($rootScope.onlineOrder.miles_used, 0) + "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" + $rootScope.formatNumber($rootScope.onlineOrder.total_cost) + "</font></td></tr>"+
                "</tbody></table>";
  
            }
          } else {
            $scope.wsalemail.emailContent += "<table width='95%' align='center' border='1' style='text-align: center; border-collapse: collapse'><tbody><tr bgcolor='#5D7B9D'><td colspan='8' bgcolor='#5D7B9D'><font color='#ffffff'>Financeiro</font></td></tr>"+
              "<tr><td>CIA</td><td>#VOO</td><td>Milhas(Trecho)</td><td>Tarifa(Trecho)</td><td>Taxas(Trecho)</td><td>PAX(Trecho)</td><td>Milhas Total</td><td>Valor Total</td></tr>";
              var miles = '';
              var pricing = '';
              var NumberOfAdult = 0;
              var NumberOfChild = 0;
              var NumberOfNewborn = 0;
  
              for(var a in $scope.onlineflights) {
                if($scope.onlineflights[a].is_child == 'S') {
                  NumberOfChild++;
                } else if($scope.onlineflights[a].is_newborn == 'S') {
                  NumberOfNewborn++;
                } else {
                  NumberOfAdult++;
                }
              }
  
              var connections;
              for(var b in $scope.resume_flights) {
                var testes = $scope.resume_flights[b].connection.split(' ');
                connections = '';
                for (var j = 0; j < testes.length; j++) {
                  connections += testes[j] + "<br>";
                }
  
                if(NumberOfAdult > 0) {
                  miles = miles + 'ADT: ' + $rootScope.formatNumber($scope.resume_flights[b].miles_per_adult) +' (x' + $rootScope.formatNumber(NumberOfAdult, 0) + ')';
                  pricing = pricing + 'ADT: ' + $rootScope.formatNumber($scope.resume_flights[b].cost_per_adult) + ' (x' + $rootScope.formatNumber(NumberOfAdult, 0) + ')';
                }
                if(NumberOfChild > 0) {
                  miles = miles + '<br>CHD: ' + $rootScope.formatNumber($scope.resume_flights[b].miles_per_child) +' (x' + $rootScope.formatNumber(NumberOfChild, 0) + ')';
                  pricing = pricing + '<br>CHD: ' + $rootScope.formatNumber($scope.resume_flights[b].cost_per_child) + ' (x' + $rootScope.formatNumber(NumberOfChild, 0) + ')';
                }
                if(NumberOfNewborn > 0) {
                  miles = miles + '<br>INF: ' + $rootScope.formatNumber($scope.resume_flights[b].miles_per_newborn) +' (x' + $rootScope.formatNumber(NumberOfNewborn, 0) + ')';
                  pricing = pricing + '<br>INF: ' + $rootScope.formatNumber($scope.resume_flights[b].cost_per_newborn) + ' (x' + $rootScope.formatNumber(NumberOfNewborn, 0) + ')';
                }
  
                $scope.wsalemail.emailContent += "<tr><td>"+$scope.resume_flights[b].airline+"</td><td>"+$scope.resume_flights[b].flight+"</td>" +
                  "<td>"+miles+"</td><td>"+pricing+"</td><td>"+$rootScope.formatNumber($scope.resume_flights[b].tax)+"</td><td>"+ NumberOfAdult + " ADT<br>" + NumberOfChild + " CHD<br>" + NumberOfNewborn + " INF" +"</td>" +
                  "<td>"+$rootScope.formatNumber($scope.resume_flights[b].original_miles, 0)+"</td><td>"+ $rootScope.formatNumber($scope.resume_flights[b].cost + $scope.resume_flights[b].tax)+"</td></tr>";
              }
  
              $scope.wsalemail.emailContent += "<tr bgcolor='#5D7B9D'><td colspan='6' bgcolor='#5D7B9D'><font color='#ffffff'>Total</font></td>"+
                "<td bgcolor='#5D7B9D'><font color='#ffffff'>" + $rootScope.formatNumber($rootScope.onlineOrder.miles_used, 0) + "</font></td><td bgcolor='#5D7B9D'><font color='#ffffff'>" + $rootScope.formatNumber($rootScope.onlineOrder.total_cost) + "</font></td></tr>"+
                "</tbody></table>";
          }
  
          $scope.previousIndex = $scope.tabindex;
          $scope.tabindex = 5;
        };
  
        $scope.check48hours = function (argument) {
          var date = new Date();
          date.setDate(date.getDate() + 2);
          for (var i = 0; $scope.onlineflights.length > i; i++) {
            if($scope.onlineflights[i].airline == "GOL" && new Date($scope.onlineflights[i].boarding_date) < date) {
              return true;
            }
          }
          return false;
        };
  
        $scope.getTotalTaxFlight = function(flightLocator) {
          var tax = 0;
          $scope.selectedFlights = $filter('filter')($scope.onlineflights, flightLocator);
          for(var i in $scope.selectedFlights) {
            if($scope.selectedFlights[i].is_newborn != "S") {
                tax += $scope.selectedFlights[i].tax_billet;
                if($scope.selectedFlights[i].money) {
                  tax += $scope.selectedFlights[i].money;
                }
            }
          }
          return tax;
        };

        $scope.getTotalMilesFlight = function(flightLocator, flight) {
          var miles = 0;
          $scope.selectedFlights = $filter('filter')($scope.onlineflights, flightLocator);
          for(var i in $scope.selectedFlights) {
            if($scope.selectedFlights[i].flight == flight) {
                miles += $scope.selectedFlights[i].miles_used;
            }
          }
          return miles;
        };
  
        $scope.getTotalMilesOrder = function(flightLocator) {
          var miles = 0;
          $scope.selectedFlights = $filter('filter')($scope.onlineflights, flightLocator);
          for(var i in $scope.selectedFlights) {
            miles += $scope.selectedFlights[i].miles_used;
          }
          return miles;
        };
  
        $scope.getTotalTaxFlightFlight = function(flightLocator, flight) {
          var tax = 0;
          $scope.selectedFlights = $filter('filter')($scope.onlineflights, flightLocator);
          for(var i in $scope.selectedFlights) {
            if($scope.selectedFlights[i].flight == flight) {
              if($scope.selectedFlights[i].is_newborn != "S") {
                tax += $scope.selectedFlights[i].tax;
                if($scope.selectedFlights[i].money) {
                  tax += $scope.selectedFlights[i].money;
                }
              }
            }
          }
          return tax;
        };
  
        $scope.checkPaxName = function(name) {
          var listNames = ['JUNIOR', 'NETO', 'FILHO', 'SOBRINHO'];
          return (listNames.indexOf(name) > -1);
        };
  
        $scope.changeClassFlights = function(flight) {
          for(var i in $scope.onlineflights) {
            if($scope.onlineflights[i].boarding_date == flight.boarding_date && $scope.onlineflights[i].airport_code_from == flight.airport_code_from && $scope.onlineflights[i].airport_code_to == flight.airport_code_to ) {
              $scope.onlineflights[i].class = flight.class;
            }
          }
        };
  
        $scope.search = function() {
          $scope.filteredOnlineOrders = $filter('filter')($scope.onlineorder, $scope.searchKeywords);
          return $scope.onFilterChange();
        };
  
        $scope.order = function(rowName) {
          if ($scope.row === rowName) {
            return;
          }
          $scope.row = rowName;
          $scope.filteredOnlineOrders = $filter('orderBy')($scope.onlineorder, rowName);
          return $scope.onOrderChange();
        };
        
        $scope.search_flight = function() {
          $scope.filteredonlineflights = $filter('filter')($scope.onlineflights, $scope.searchKeywords);
        };
        
        $scope.order_flight = function(rowName) {
          if ($scope.row === rowName) {
            return;
          }
          $scope.row = rowName;
          $scope.filteredonlineflights = $filter('orderBy')($scope.onlineflights, rowName);
        };
  
        $scope.search_miles = function() {
          $scope.currentMiles = $filter('filter')($scope.flight_miles, $scope.searchKeywordsMiles);
        };
        
        $scope.order_miles = function(rowName) {
          $scope.rowMiles = rowName;
          $scope.filteredflight_miles = $filter('orderBy')($scope.flight_miles, rowName);
          $scope.onOrderMilesChange();
        };
  
        $scope.onOrderMilesChange = function() {
          $scope.selectMiles(1);
          return $scope.currentPageMiles = 1;
        };
  
        $scope.back = function() {
          $scope.tabindex = $scope.tabindex - 1;
          $scope.searchKeywords = '';
        };
  
        $scope.next = function() {
          $scope.tabindex = $scope.tabindex + 1;
        };

        $scope.findColor = function(order){
          switch (order.status) {
            case 'PENDENTE':
              return "";
            case 'PRIORIDADE':
              return "";
            case 'VOEB2B':
              return "";
            case 'ESPERA VLR':
              return "";
            case 'ESPERA LIM VLR':
              return "";
            case 'ANT':
              return "";
            case 'BLOQ':
              return "";
            case 'ESPERA LIM':
              return "";
            case 'ESPERA PGTO':
              return "";
            case 'EMITIDO':
              if(order.showSms && !order.sms) {
                return "#FFA26D";
              }
              return "#9DCE9D";
            case 'ENVIADO':
              return "#9DCE9D";
            case 'RESERVA':
              return "#9BB3DC";
            case 'CANCELADO':
              return "#F38E8E";
            case 'FALHA EMISSAO':
              return "#F38E8E";
            case 'VERIFICADO':
              if(order.showSms && !order.sms) {
                return "#FFA26D";
              }
          }
        };

        $scope.findClass = function(onlineflight){
          if((onlineflight.flightLocator == undefined) || (onlineflight.flightLocator == '') || ((onlineflight.airline == 'TAM' || onlineflight.airline == 'LATAM') && ((onlineflight.ticket_code == undefined) || (onlineflight.ticket_code == ''))))
            return 'btn btn-danger smallBtn';
          else
            return 'btn btn-line-info smallBtn';
        };

        $scope.orderTag = function(status) {
          switch (status) {
            case 'PENDENTE':
              return "label label-warning";
            case 'ESPERA VLR':
              return "label label-default";
            case 'PRIORIDADE':
              return "label label-purple";
            case 'VOEB2B':
              return "label label-purple";
            case 'ESPERA PGTO':
              return "label label-default";
            case 'ESPERA LIM':
              return "label label-default";
            case 'RESERVA':
              return "label label-info";
            case 'ENVIADO':
              return "label label-success";
            case 'EMITIDO':
              return "label label-success";
            case 'VERIFICADO':
              return "label label-success";
            case 'CANCELADO':
              return "label label-danger";
            case 'FALHA EMISSAO':
              return "label label-danger";
            case 'ESPERA':
              return "label label-default";
            case 'ESPERA LIM VLR':
              return "label label-default";
            case 'ANT':
              return "label label-default";
            case 'BLOQ':
              return "label label-default";
          }
        };

        $scope.orderTagClient = function(order) {
          if(order.commercialStatus) {
            return "label label-warning";
          }
          if(order.check_2 == true) {
            return "label label-info";
          }
          switch (order.status) {
            case 'PENDENTE':
              return "label label-warning";
            case 'ESPERA VLR':
              return "label label-default";
            case 'PRIORIDADE':
              return "label label-purple";
            case 'VOEB2B':
              return "label label-purple";
            case 'ESPERA PGTO':
              return "label label-default";
            case 'ESPERA LIM':
              return "label label-default";
            case 'RESERVA':
              return "label label-info";
            case 'ENVIADO':
              return "label label-success";
            case 'EMITIDO':
              return "label label-success";
            case 'VERIFICADO':
              return "label label-success";
            case 'CANCELADO':
              return "label label-danger";
            case 'FALHA EMISSAO':
              return "label label-danger";
            case 'ESPERA':
              return "label label-default";
            case 'ESPERA LIM VLR':
              return "label label-default";
            case 'ANT':
              return "label label-default";
            case 'BLOQ':
              return "label label-default";
          }
        };

        $scope.orderMiles = function(mile) {
          switch (mile.priority) {
            case '-1':
              return "label label-success";
            case '0':
              return "label label-primary";
            case '1':
              return "label label-danger";
            case '2':
              return "label label-warning";
            default:
              return "";
  
          }
        };

        $scope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
          return $rootScope.formatNumber(number, decimalsLength, decimalSeparator, thousandSeparator);
        };
  
        $scope.numPerPageOpt = [10, 30, 50, 100, 200];
        $scope.numPerPage = $scope.numPerPageOpt[2];
        $scope.currentPage = 1;
        $scope.currentPageOnlineOrder = [];
  
        $scope.numPerPageOptMiles = [10, 30, 50, 100, 200];
        $scope.numPerPageMiles = $scope.numPerPageOptMiles[2];
        $scope.currentPageMiles = 1;
        $scope.currentMiles = [];
  
        $scope.selectMiles = function(page) {
          var end, start;
          start = (page - 1) * $scope.numPerPageMiles;
          end = start + $scope.numPerPageMiles;
          return $scope.currentMiles = $scope.filteredflight_miles.slice(start, end);
        };
  
        $scope.onNumPerPageChangeMiles = function() {
          $scope.selectMiles(1);
          return $scope.currentPageMiles = 1;
        };
  
        $scope.atualization = function () {
          if(document.getElementById('online_page') && ($scope.tabindex === 0 || $scope.tabindex === 99 || $scope.tabindex === 6) && $scope.atualizations == true) {
            $scope.loadOnlineOrderWaiting();
          }
        };
  
        $scope.$watch('[tabindex, atualizations]', function() {
          if(($scope.tabindex === 0 || $scope.tabindex === 99 || $scope.tabindex === 6 ) && $scope.atualizations == true) {
            $scope.reserve = false;
            $scope.atualization();
          }
          if($scope.tabindex != 6) {
            if($scope.tabindex == 0) {
              $rootScope.socket.emit('order_update', { order_id: undefined, name: $scope.main.name });
            } else {
              $rootScope.socket.emit('order_update', { order_id: $scope.selected.id, name: $scope.main.name });
            }
          }
        });
  
        init = function() {
          $scope.tabindex = 0;
          if($scope.main.dealer) {
            $scope.tabindex = 99;
          } else if($scope.main.commercial) {
            $scope.tabindex = 6;
          }
          $scope.checkValidRoute();
          if($scope.main.id == '') {
            return;
          }
          $scope.wsale = {};
          $('#discount').number( true, 2, ',', '.');
          $scope.milesMoney = false;
          $scope.third = false;
          $scope.atualizations = true;
          $rootScope.modalOpen = false;
          $scope.multiDownloads = false;

          $.post("../backend/application/index.php?rota=/loadAirline", {}, function(result){
            $rootScope.airlines = jQuery.parseJSON(result).dataset;
          });
  
          $scope.uploader = new FileUploader();
          $scope.uploader.url = "../backend/application/index.php?rota=/saveFile";
  
          $scope.uploader.autoUpload = true;
          $scope.uploader.filters.push({
              name: 'customFilter',
              fn: function(item /*{File|FileLikeObject}*/, options) {
                  return this.queue.length < 18;
              }
          });
  
          $rootScope.socket.on('updateOrdersWaiting', function (data) {
            if ($scope.atualizations) {
              $scope.onlineorder = data.orders;
              $scope.search();
              $scope.$digest();
            }
          });
  
          $rootScope.socket.on('order_update', function (data) {
            if(data.order_id) {
              var tempOrder = $filter('filter')($scope.onlineorder, data.order_id);
              $scope.addSessionName(data.order_id, data.name);
            } else {
              $scope.removeSessionName(data.name);
            }
            $scope.search();
            $scope.$digest();
          });
        };
  
        $scope.addSessionName = function(order_id, name) {
          var tempOrders = $filter('filter')($scope.onlineorder, order_id);
          for(var idOrder in tempOrders) {
            var newSessionName = '';
            var and = '';
            if(tempOrders[idOrder].userSession) {
              var sessionName = tempOrders[idOrder].userSession.split(';');
              for(var idName in sessionName) {
                if(sessionName[idName] != name) {
                  newSessionName += and + sessionName[idName];
                  and = ';';
                }
              }
            }
            tempOrders[idOrder].userSession = newSessionName + and + name;
          }
        };
  
        $scope.removeSessionName = function(name) {
          var tempOrders = $filter('filter')($scope.onlineorder, name);
          for(var idOrder in tempOrders) {
            var newSessionName = '';
            var and = '';
            if(tempOrders[idOrder].userSession) {
              var sessionName = tempOrders[idOrder].userSession.split(';');
              for(var idName in sessionName) {
                if(sessionName[idName] != name) {
                  newSessionName += and + sessionName[idName];
                  and = ';';
                }
              }
            }
            tempOrders[idOrder].userSession = newSessionName;
          }
        };
  
        $scope.setStatusOrder = function(order, status) {
          if(!order) {
            order = $rootScope.onlineOrder;
          }
          $.post("../backend/application/index.php?rota=/setStatusOrder", {hashId: $scope.session.hashId, data: order, status: status}, function(result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.tabindex = 0;
          });
        };
  
        $scope.backToList = function() {
          $scope.tabindex = 0;
        };
  
        $scope.loadOnlineOrderWaiting = function() {
          cfpLoadingBar.start();
          if($scope.tabindex == 0 || $scope.tabindex == 99 || $scope.tabindex == 6) {
            $scope.selected = {};
          }
          $.post("../backend/application/index.php?rota=/loadOnlineOrderWaiting", {hashId: $scope.session.hashId, data: $scope.selected}, function(result){
              $scope.onlineorder = jQuery.parseJSON(result).dataset;            
              if($scope.tabindex == 0 || $scope.tabindex == 99 || $scope.tabindex == 6) {
                $scope.search();
                $scope.cancel = false;
                cfpLoadingBar.complete();
              }
          });
        };
  
        $scope.taxByFlight = function (onlineflight) {
          var tax = 0;
          if(onlineflight.is_newborn != 'S') {
            tax += onlineflight.tax;
          }
          tax += onlineflight.du_tax;
          return tax;
        };
  
        $scope.checkTimeFlight = function(args) {
          if(args == undefined) {
            var date = new Date();
            date.setDate(date.getDate() + 2);
            for (var i = 0; $scope.onlineflights.length > i; i++) {
              if($scope.onlineflights[i].airline == "GOL" && new Date($scope.onlineflights[i].boarding_date) < date) {
                logger.logError("Voo GOL com embarque inferior a 48Hrs!");
                $scope.previousIndex = 0;
                $scope.tabindex = 0;
                $rootScope.$emit('openPermission', {main: $scope.$parent.$parent.main, hashId: $scope.$parent.$parent.session.hashId});
                $scope.$apply();
                return;
              }
            }
          }
        };
  
        $scope.getAllCheckend = function() {
          if($scope.selected) {
            if($scope.selected.status == 'EMITIDO' || $scope.selected.status == 'VERIFICADO') {
              for(var i in $scope.onlineflights) {
                if($scope.onlineflights[i].saleChecked == false)
                  return false;
              }
              return true;
            }
            return false;
          }
          return false;
        };
  
        $scope.findMilesOrder = function() {
          var miles = 0;
          for(var i in $scope.filteredOnlineOrders) {
            miles += $scope.filteredOnlineOrders[i].miles_used;
          }
          return $rootScope.formatNumber(miles, 0);
        };
  
        $scope.loadOnlineFlight = function(args) {
          $scope.args = args;
          $scope.selected.startedDate = new Date();
          $scope.tabindex = 3;
          $scope.showNextStep = false;
          cfpLoadingBar.start();
          $scope.commercial_free = {};
          $.post("../backend/application/index.php?rota=/loadCommercialStatusOrder", {data: $scope.selected}, function(result){
            $scope.commercial_free = jQuery.parseJSON(result).dataset;
          });
          $.post("../backend/application/index.php?rota=/loadOnlineFlight", { hashId: $scope.session.hashId, data: $scope.selected }, function(result){
            $scope.onlineflights = jQuery.parseJSON(result).dataset;
            $scope.filteredonlineflights = jQuery.parseJSON(result).dataset;
            $scope.search();
            $scope.resume_flights = $filter('filter')($scope.onlineflights, 'FILTER_FLIGHT');
            $scope.resume_paxs = $filter('filter')($scope.onlineflights, 'FILTER_PAX');
  
            if($scope.onlineflights[0].card_number != ''){
                $.post("../backend/application/index.php?rota=/loadCardsData", {data: $scope.onlineflights}, function(result){
                $scope.cards= jQuery.parseJSON(result).dataset;
                $scope.cardPassword();
                });
            }
            $.post("../backend/application/index.php?rota=/loadClientName", {data: $scope.selected}, function(result){
                $scope.response = jQuery.parseJSON(result).dataset;
                cfpLoadingBar.complete();
                if($scope.response != undefined){
                if($scope.response.status == "Coberto" && $scope.response.paymentType == "Coberto") {
                    $scope.selected.early_covered = true;
                }
                $scope.selected.subClientEmail = $scope.response.subClientEmail;
                if($scope.check48hours() && args == undefined) {
                    $scope.checkTimeFlight(args);
                    $scope.previousIndex = 0;
                    $scope.tabindex = 0;
                } else  if($scope.response.status == "Bloqueado" && args == undefined && $scope.selected.commercialStatus == false){
                    logger.logError("Cliente Bloqueado! - Favor consultar o Financeiro");
                    $scope.previousIndex = 0;
                    $scope.tabindex = 0;
                    $scope.$emit('openPermission', {main: $scope.$parent.$parent.main, hashId: $scope.$parent.$parent.session.hashId});
                } else if($scope.response.status == "Pendente" && args == undefined && $scope.selected.commercialStatus == false) {
                    logger.logError("Cliente Pendente! - Favor consultar o Comercial");
                    $scope.previousIndex = 0;
                    $scope.tabindex = 0;
                    $scope.$emit('openPermission', {main: $scope.$parent.$parent.main, hashId: $scope.$parent.$parent.session.hashId});
                    return;
                } else if($scope.response.paymentType == "Antecipado" && args == undefined && $scope.selected.commercialStatus == false){
                    logger.logError("Cliente Antecipado! - Favor consultar o Financeiro");
                    $scope.previousIndex = 0;
                    $scope.tabindex = 0;
                    $scope.$emit('openPermission', {main: $scope.$parent.$parent.main, hashId: $scope.$parent.$parent.session.hashId});
                    return;
                } else if($scope.response.partner_limit == "true" && args == undefined && $scope.selected.commercialStatus == false){
                    logger.logError("Cliente Atingiu 'limite de emissões! - Favor consultar o Financeiro");
                    $scope.previousIndex = 0;
                    $scope.tabindex = 0;
                    $scope.$emit('openPermission', {main: $scope.$parent.$parent.main, hashId: $scope.$parent.$parent.session.hashId});
                    return;
                } else {
                    $scope.wsale.client = $scope.response.client_name;
                    $scope.selected.client_name = $scope.wsale.client;
                    $scope.selected.subClientEmail = $scope.response.subClientEmail;
                    $scope.previousIndex = $scope.tabindex;
                    $scope.$apply();
                    $scope.checkTimeFlight(args);
                    $scope.showNextStep = true;

                    $.post("../backend/application/index.php?rota=/in8Bot/checkOrderBot", { order: $scope.selected }, function(result){
                    $scope.robotLog = jQuery.parseJSON(result).dataset;
                    });
                }
                } else {
                    logger.logWarning('Cliente não vinculado!');
                    $scope.previousIndex = 0;
                    $.post("../backend/application/index.php?rota=/loadClientsNames", { }, function(result){
                        $scope.clients = jQuery.parseJSON(result).dataset;
                    });
                }
            });

            return $scope.select($scope.currentPage);
          });
        };
  
        $scope.$watch('wsale.client', function() {
          var client = _.find($scope.clients, function(o) { return o.name == $scope.wsale.client; })
          if(client && client != undefined && client != null) {
            $scope.showNextStep = true;
          } else {
            $scope.showNextStep = false;
          }
        });
  
        $scope.checkPaxNames = function(pax_name) {
          if(pax_name && pax_name != '') {
            if($scope.blackListOfNames.indexOf(pax_name.split(' ')[pax_name.split(' ').length - 1]) > -1) {
              return true;
            }
          }
          return false;
        };
  
        $scope.getTotalValue = function(){
          if($scope.onlineflights != undefined){
            var cont = 0;
            for (var i = 0; i< $scope.onlineflights.length; i++) {
              cont += $scope.onlineflights[i].cost - $scope.onlineflights[i].discount;
              if($scope.onlineflights[i].is_newborn != 'S'){
                cont += $scope.onlineflights[i].tax;
              }
            }
            return cont;
          }
        };
  
        $scope.getTotalDiscount = function(){
          if($scope.onlineflights != undefined){
            var cont = 0;
            for (var i = 0; i< $scope.onlineflights.length; i++) {
              cont += $scope.onlineflights[i].discount;
            }
            return cont;
          }
        };
  
        $scope.getTotal = function(){
          if($scope.onlineflights != undefined){
            var cont = 0;
            for (var i = 0; i< $scope.onlineflights.length; i++) {
              cont += $scope.onlineflights[i].cost;
            }
            return cont;
          }
        };
  
        $scope.getFlightTotal = function(onlineflight) {
          if($scope.selected.status == 'EMITIDO' || $scope.selected.status == 'VERIFICADO') {
            return onlineflight.amountPaid;
          } else {
            if(onlineflight.is_newborn == "N"){
              return onlineflight.cost + onlineflight.tax - onlineflight.discount;
            } else {
              return onlineflight.cost - onlineflight.discount;
            }
          }
        };
  
        $scope.getTotalTax = function(){
          if($scope.onlineflights != undefined){
            var cont = 0;
            for (var i = 0; i< $scope.onlineflights.length; i++) {
              if($scope.onlineflights[i].is_newborn != 'S'){
                cont += $scope.onlineflights[i].tax;
              }
            }
            return cont;
          }
        };
  
        $scope.getPaxType = function(onlineflight){
          if($scope.onlineflights != undefined){
            if(onlineflight.is_child == "S")
              return "CHD";
            if(onlineflight.is_newborn == "S")
              return "INF";
            return "ADT";
          }
        };
  
        $scope.saveStatusOrder = function() {
          $.post("../backend/application/index.php?rota=/saveStatusOrder", {data: $scope.selected}, function(result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.tabindex = 0;
            $scope.loadSalesByFilter();
          });
        };

        $scope.sendEmail = function(onlineflights){
          $scope.previousIndex = $scope.tabindex;
          $scope.tabindex = 5;
          $scope.fillEmailContent(onlineflights);
        };
  
        $scope.decript = function(code){
          var data = code.split('320AB');
          var finaly = '';
          for (var j = 0; data.length > j; j++) {
            finaly = finaly + (String.fromCharCode(data[j] / 320));
          }
          return finaly;
        };
  
        $scope.findBaggagePrice = function(airline, baggages) {
          return _.find($rootScope.airlines, function(o) { return o.name == airline; }).baggage * baggages;
        };

        $scope.isValidOrder = function() {
          if ($scope.selected) {
            if(($scope.validFlights()) === true){
              return true;
            }
            else{
              return false;
            }
          }
        };

        $scope.validFlights = function(){
          if ($scope.onlineflights) {
            for (var j = 0; $scope.onlineflights.length > j; j++) {
              if($scope.onlineflights[j].isBreak == "Integrado"){
                if(($scope.onlineflights[j].card_number == " - " && ($scope.onlineflights[j].provider == 'Loja TAM Contagem' || $scope.onlineflights[j].provider == 'Loja TAM Ponta Grossa' || $scope.onlineflights[j].provider == 'Loja TAM M' || $scope.onlineflights[j].provider === '')) || $scope.onlineflights[j].flightLocator == undefined || (($scope.onlineflights[j].airline=="TAM" || $scope.onlineflights[j].airline=="LATAM") && $scope.onlineflights[j].ticket_code == undefined))
                  return false;
                if(($scope.onlineflights[j].provider == '' && $scope.onlineflights[j].tax_card == undefined) || $scope.onlineflights[j].flightLocator == undefined)
                  return false;
              }
            }
            return true;
          }
        };
  
        $scope.validOrder = function() {
          if ($scope.wsale.client) {
            $scope.selected.client_name = $scope.wsale.client;
  
            if($scope.response) {
              if($scope.response.status == 'Bloqueado' || $scope.response.paymentType == 'Antecipado') {
                $scope.openClientWarning($scope.response);
              }
            }
  
            $scope.previousIndex = $scope.tabindex;
            $scope.tabindex++;
          }
        };

        $scope.openOrdersNotificationModal = function() {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "app/modals/order_notification_status.html",
            controller: 'OrdersNotificationModalCtrl',
            periods: $scope.periods,
            size: 'lg',
            resolve: {
              order: function() {
                return $scope.selected;
              }
            }
          });
          modalInstance.result.then((function(filter) {
          }), function() {
            console.log("Modal dismissed at: " + new Date());
          });
        };
  
        $scope.openClientWarning = function(args) {
          $scope.ClientWarning = args
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "ClientWarningModalCtrl.html",
            controller: 'ClientWarningInstanceCtrl',
            resolve: {
              selected: function() {
                return $scope.ClientWarning;
              }
            }
          });
          modalInstance.result.then(function() {
          });
        };
  
        $scope.cancelOrder = function() {
          $scope.cancel = true;
        };
  
        $scope.cancelOnlineOrder = function() {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/cancelOnlineOrder", {data: $scope.selected}, function(result){
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.tabindex = 0;
          });
        };
  
        $scope.loadSalesByFilter = function() {
          $.post("../backend/application/index.php?rota=/loadOnlineWaitingByFilter", {data: $scope.filter}, function(result){
            if($scope.tabindex == 0) {
              $scope.onlineorder = jQuery.parseJSON(result).dataset;           
              $scope.search();
              $scope.$apply();
              return $scope.select($scope.currentPage);
            }
          });
        };

        $scope.filter_by_key = function(array, key) {
          var clear = []
          for(var filterIndex in array) {
            if(clear.indexOf(array[filterIndex][key]) == -1){
              clear.push(array[filterIndex]);
            }
          }
          return clear;
        };

        $scope.setSelected = function(args) {
          $scope.args = args;
          $scope.flight_selected = undefined;
          $scope.wsale = {};
          $scope.robotLog = [];
          $scope.multiDownloads = false;
          if(!args){
            $scope.selected = angular.copy(this.onlineorder);
            $rootScope.setOnlineOrder($scope.selected);
          }
          if(args === true) {
            $scope.selected = $rootScope.onlineOrder;
          }
          $scope.uploader.formData = { hashId: $scope.session.hashId };
          $scope.loadOnlineFlight(args);
        };

        $scope.setSelectedFlight = function() {
          if($scope.selected.status != "EMITIDO"){
            $scope.flight_selected = this.onlineflight;
          }else{
            $scope.flight_selected = undefined;
          }
        };
  
        $scope.undo = function() {
          if($scope.selected) {
            if($scope.selected.status == "EMITIDO" || $scope.selected.status == "VERIFICADO"){
              $scope.tabindex = 0;
              $scope.previousIndex = 0;
            }
            else
            {
              $scope.tabindex = $scope.previousIndex;
              $scope.previousIndex--;
            }
          } else {
            $scope.tabindex = 0;
            $scope.previousIndex = 0;
          }
          $scope.cancel = false;
        };
  
        $scope.findSpecialCards = function() {
          if($scope.onlineflights) {
            if($scope.onlineflights.length > 0) {
              for(var i in $scope.onlineflights) {
                if($scope.onlineflights[i].card_type == 'RED' || $scope.onlineflights[i].card_type == 'BLACK' || $scope.onlineflights[i].card_type == 'DIAMANTE' || $scope.onlineflights[i].card_type == 'CLUBE DIAMANTE')
                  return true;
              }
            }
          }
          return false;
        };

        $scope.openSearchModal = function() {
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "ModalCtrl.html",
            controller: 'OnlineOrderModalInstanceCtrl',
            resolve: {
              filter: function() {
                return $scope.filter;
              }
            }
          });
          modalInstance.result.then((function(filter) {
  
            if($scope.main.isMaster || $scope.main.sale || $scope.main.changeMiles || $scope.main.conference) {
              filter._issueDateFrom = $rootScope.formatServerDate(filter.issueDateFrom);
              filter._issueDateTo = $rootScope.formatServerDate(filter.issueDateTo);
            } else {
              var date = new Date();
              date.setDate(date.getDate() - 3);
              if(filter.issueDateFrom && filter.issueDateFrom != 'Invalid Date') {
                if(filter.issueDateFrom < date) {
                  filter.issueDateFrom = new Date();
                  filter.issueDateFrom.setDate(filter.issueDateFrom.getDate() - 2);
                }
              } else {
                filter.issueDateFrom = new Date();
                filter.issueDateFrom.setDate(filter.issueDateFrom.getDate() - 2);
              }
  
              if(filter.issueDateTo && filter.issueDateTo != 'Invalid Date') {
                filter._issueDateTo = $rootScope.formatServerDate(filter.issueDateTo);
              }
  
              filter._issueDateFrom = $rootScope.formatServerDate(filter.issueDateFrom);
            }
  
            $scope.filter = filter;
            $scope.loadSalesByFilter();
          }), function() {
            console.log("Modal dismissed at: " + new Date());
          });
        };

        $scope.confirOrderToEmit = function() {
          $.post("../backend/application/index.php?rota=/confirOrderToEmit", {data: $scope.selected}, function(result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.tabindex = 0;
            $scope.$digest();
          });
        };

        $scope.confirmPayment = function() {
          $.post("../backend/application/index.php?rota=/onlineOrder/confirmPayment", {data: $scope.selected}, function(result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
            $scope.tabindex = 0;
            $scope.$digest();
          });
        };
  
        return init();
      }
    ]).controller('OnlineOrderModalInstanceCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'filter', function($scope, $rootScope, $modalInstance, filter) {
        $scope.saleStatus = ['RESERVA','PENDENTE','EMITIDO', 'EM ESPERA'];
        $scope.filter = filter;
        $scope.ok = function() {
          $modalInstance.close($scope.filter);
        };
        $scope.cancel = function() {
          $modalInstance.dismiss("cancel");
        };
      }
    ]).controller('OnlineModalPermissionCtrl', [
      '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {
  
        $rootScope.modalOpen = false;
  
        $rootScope.$on('openPermission', function(event, args) {
          event.stopPropagation();
          if($rootScope.modalOpen == false) {
            $rootScope.modalOpen = true;
            $scope.open(args);
          }
        });
  
        $scope.open = function(args) {
          args.main.userEmail = '';
          args.main.userPassCode = '';
          args.main.pinCode = '';
          args.main.type = args.type;
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "OnlineModalPermissionCtrl.html",
            controller: 'OnlineModalPermissionInstanceCtrl',
            resolve: {
              permissions: function() {
                return args.main;
              },
              hashId: function() {
                return args.hashId;
              },
              selected: function() {
                return $scope.selected;
              }
            }
          });
          modalInstance.result.then(function(permissions) {
            $rootScope.modalOpen = false;
          }, function() {
            $rootScope.modalOpen = false;
          });
        };
  
      }
    ]).controller('OnlineModalPermissionInstanceCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', 'permissions', 'hashId', 'selected', function($scope, $rootScope, $modalInstance, logger, permissions, hashId, selected) {
        $scope.permissions = permissions;
        $scope.hashId = hashId;
        $scope.selected = selected;
  
        $scope.cancel = function() {
          $modalInstance.dismiss("cancel");
        };
  
        $scope.email = function() {
          $rootScope.$emit('fillEmailBLoqued', {email: $rootScope.onlineOrder.client_email});
          $scope.cancel();
        };
  
        $scope.emailCommecial = function() {
          $rootScope.$emit('fillEmailBLoqued', { email: 'comercial@onemilhas.com.br' });
          $scope.cancel();
        };
  
        $scope.setStatusOrder = function(order, status) {
          if(!order) {
            order = $rootScope.onlineOrder;
          }
          $.post("../backend/application/index.php?rota=/setStatusOrder", {hashId: $scope.hashId, data: order, status: status}, function(result) {
            logger.logSuccess(jQuery.parseJSON(result).message.text);
          });
        };
  
        $scope.checkCommercial = function() {
          $.post("../backend/application/index.php?rota=/checkAcessCode", { data: $scope.permissions }, function(result) {
            $scope.response= jQuery.parseJSON(result).dataset;
  
            if($scope.response.valid == 'true') {
              $.post("../backend/application/index.php?rota=/setStatusOrderCommercial", { data: $rootScope.onlineOrder }, function(result) {
                logger.logSuccess(jQuery.parseJSON(result).message.text);
              });
              $modalInstance.close($scope.permissions);
            } else {
              logger.logError("Dados não conferem!");
            }
          });
        };
  
        $scope.check = function() {
          $.post("../backend/application/index.php?rota=/checkAcessCode", {hashId: $scope.hashId, data: $scope.permissions}, function(result) {
            $scope.response= jQuery.parseJSON(result).dataset;
  
            if($scope.response.valid == 'true') {
              $modalInstance.close($scope.permissions);
              $rootScope.$emit('checkedPermission', {});
            } else {
              logger.logError("Dados não conferem!");
            }
          });
        };
      }
    ]).controller('CloseOrder', [
      '$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {
  
        $scope.open = function() {
          $scope.flight_selected = $scope.$parent.selected;
          if($scope.flight_selected.comments.length > 0 || $scope.$parent.findSpecialCards()) {
            var modalInstance;
            modalInstance = $modal.open({
              templateUrl: "CloseOrder.html",
              controller: 'CloseOrderInstanceCtrl',
              resolve: {
                flight_selected: function() {
                  return $scope.flight_selected;
                },
                onlineflights: function() {
                  return $scope.$parent.onlineflights;
                }
              }
            });
            modalInstance.result.then(function() {
              $scope.$parent.saveOrder();
            });
          } else {
            $scope.$parent.saveOrder();
          }
        };
  
      }
    ]).controller('CloseOrderInstanceCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', 'flight_selected', 'onlineflights', function($scope, $rootScope, $modalInstance, logger, flight_selected, onlineflights) {
        $scope.flight_selected = flight_selected;
        $scope.onlineflights = onlineflights;
        
        $scope.findSpecialCardsLATAM = function() {
          if($scope.onlineflights) {
            if($scope.onlineflights.length > 0) {
              for(var i in $scope.onlineflights) {
                if($scope.onlineflights[i].card_type == 'RED' || $scope.onlineflights[i].card_type == 'BLACK')
                  return true;
              }
            }
          }
          return false;
        };
  
        $scope.findSpecialCardsGOL = function() {
          if($scope.onlineflights) {
            if($scope.onlineflights.length > 0) {
              for(var i in $scope.onlineflights) {
                if($scope.onlineflights[i].card_type == 'DIAMANTE' || $scope.onlineflights[i].card_type == 'CLUBE DIAMANTE')
                  return true;
              }
            }
          }
          return false;
        };
  
        $scope.cancel = function() {
          $modalInstance.dismiss("cancel");
        };
  
        $scope.ok = function() {
          $modalInstance.close();
        };
  
      }
    ]).controller('ClientWarningInstanceCtrl', [
      '$scope', '$rootScope', '$modalInstance', 'logger', 'selected', function($scope, $rootScope, $modalInstance, logger, selected) {
        $scope.selected = selected;
  
        $scope.cancel = function() {
          $modalInstance.dismiss("cancel");
        };
      }
    ]);
  })();
  ;
  