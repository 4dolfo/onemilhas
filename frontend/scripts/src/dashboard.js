(function () {
  'use strict';
  angular.module('app.table').controller('MainPageCtrl', [
    '$scope', '$rootScope', '$route', '$filter', 'cfpLoadingBar', 'logger', '$modal', '$element', 'ui.checkbox', function ($scope, $rootScope, $route, $filter, cfpLoadingBar, logger, $modal, $element) {
    }
  ]).controller('NavCtrl', ['$scope', '$rootScope', 'cfpLoadingBar', 'filterFilter', function ($scope, $rootScope, cfpLoadingBar, filterFilter) { }]).controller('DashboardCtrl', [
    '$scope', '$rootScope', 'logger', 'cfpLoadingBar', function ($scope, $rootScope, logger, cfpLoadingBar) {
      var init;
      $scope.filter = {
        days: 30,
        points: 900,
        enterPressed: false
      };
      $scope.SumMilesLef_detalhes = false;
      $scope.SumOrderMiles_detalhes = false;

      $scope.onFilterEnterKey = function(keyCode) {
        //console.log($scope.filter.days, $scope.filter.points);
        //if($scope.filter.days != '' && $scope.filter.points != ''){
          $scope.filter.enterPressed = false;
          if(keyCode == 13){
            $scope.filter.enterPressed = true;
            $scope.search();
          }
        //}
      };

      $scope.onFilterBlur = function() {
        //console.log($scope.filter.days, $scope.filter.points);
        //if($scope.filter.days != '' && $scope.filter.points != ''){
          if(!$scope.filter.enterPressed){
            $scope.search();
          }
          $scope.filter.enterPressed = false;
        //}
      };

      $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
        $scope.response = jQuery.parseJSON(result).dataset;
        $rootScope.notificationsInfo = [];
        $scope.notificationsInfoCheck = [];
        logger.logWarning("Informações em notificações !!! ");
        for (var i in $scope.response) {
          if (!$scope.response[i].checkBox) {
            $rootScope.pushNotificationInfo("Novas Notificações");
            $scope.notificationsInfoCheck.push(i);
          }
        }
      });
      $scope.search = function () {
        
        if ($scope.showFilter) {
          $scope.filter._dateFrom = $rootScope.formatServerDate($scope.filter.dateFrom);
          $scope.filter._dateTo = $rootScope.formatServerDate($scope.filter.dateTo);
        }
        if ($scope.$parent.$parent.main.isMaster || $scope.main.id == 8693) {
          $.post("../backend/application/index.php?rota=/loadSumOrderMiles", { data: $scope.filter }, function (result) {
            $scope.SumOrderMiles = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
        }
        if ($scope.$parent.$parent.main.isMaster) {
          $.post("../backend/application/index.php?rota=/loadSumPurchaseMiles", { data: $scope.filter }, function (result) {
            $scope.SumPurchaseMiles = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
          $.post("../backend/application/index.php?rota=/loadSumMilesLeft", { data: $scope.filter }, function (result) {
            $scope.SumMilesLef = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
          $.post("../backend/application/index.php?rota=/loadAverageMiles", { data: $scope.filter }, function (result) {
            $scope.averageMiles = jQuery.parseJSON(result).dataset;
            $scope.$apply();
          });
          $.post("../backend/application/index.php?rota=/loadTotalBalance", { data: $scope.filter }, function (result) {
            $scope.TotalBalance = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
          // $.post("../backend/application/index.php?rota=/loadPurchasesDueDate", { data: $scope.filter }, function (result) {
          //   $scope.purchases = jQuery.parseJSON(result).dataset;
          //   $scope.$digest();
          // });
        }
        if ($scope.main.id == 5164 || $scope.main.id == 5162) {
          if ($scope.showFilter) {
            $scope.filter._dateFrom = $rootScope.formatServerDate($scope.filter.dateFrom);
            $scope.filter._dateTo = $rootScope.formatServerDate($scope.filter.dateTo);
          }
          $.post("../backend/application/index.php?rota=/loadSumDatas", { data: $scope.filter }, function (result) {
            $scope.sumData = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
        }
      };

      $scope.loadData = function () {
        if ($scope.$parent.$parent.main.isMaster) {
          $.post("../backend/application/index.php?rota=/loadSumMilesLeftGroupBy", { hashId: $scope.session.hashId }, function (result) {
            $scope.Group = jQuery.parseJSON(result).dataset;
            $scope.donutChart2.data = [];
            for (var grop in $scope.Group) {
              $scope.donutChart2.data.push({
                label: $scope.Group[grop].airline,
                data: $scope.Group[grop].miles
              });
            }
          });
          $.post("../backend/application/index.php?rota=/loadBalanceHistory", { hashId: $scope.session.hashId }, function (result) {
            $scope.balance = jQuery.parseJSON(result).dataset;
            $scope.comboData = [];
            for (var grop in $scope.balance) {
              $scope.comboData.push({
                month: $scope.balance[grop].month,
                a: $scope.balance[grop].purchased,
                b: $scope.balance[grop].saled
              });
            }
          });
          $.post("../backend/application/index.php?rota=/checkAirlinesMilesBench", { hashId: $scope.session.hashId }, function (result) {
            $scope.response = jQuery.parseJSON(result).dataset;
            for (var i in $scope.response) {
              logger.logWarning("Estoque de milhas baixo: " + $scope.response[i].miles);
              $rootScope.pushNotification("Estoque de milhas baixo: " + $scope.response[i].miles);
            }
          });

          $.post("../backend/application/index.php?rota=/loadSumMilesLeftSRM", { hashId: $scope.session.hashId }, function (result) {
            $scope.SRMViagens = jQuery.parseJSON(result).dataset;
          });
        }
      };

      $scope.findDate = function (date) {
        return new Date(date);
      };

      $scope.loadBlockedClients = function () {
        $rootScope.notifications = [];
        if (!$scope.$parent.$parent.main.isMaster && $scope.$parent.$parent.main.dealer) {
          cfpLoadingBar.start();
          $.post("../backend/application/index.php?rota=/loadClientsNames", { }, function (result) {
            cfpLoadingBar.complete();
            $scope.clients = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
        }
        // financial advising
        if ($scope.$parent.$parent.main.financial) {
          if (new Date().getDate() == 1) {
            logger.logWarning("Realizar pagamentos mensais");
            $rootScope.pushNotification('Realizar pagamentos mensais');
          }
          if (new Date().getDate() == 1 || new Date().getDate() == 16) {
            logger.logWarning("Realizar pagamentos quinzenais");
            $rootScope.pushNotification('Realizar pagamentos quinzenais');
          }
          $.post("../backend/application/index.php?rota=/checkClientsDeadLine", { hashId: $scope.session.hashId }, function (result) {
            $scope.deadLine = jQuery.parseJSON(result).dataset;
          });
        }
        if ($scope.$parent.$parent.main.milesBench) {
          logger.logWarning("Notificações");
          $rootScope.pushNotification('Novas Informações.');
        }

        // human resources advising
        if ($scope.$parent.$parent.main.humanResources) {
          $.post("../backend/application/index.php?rota=/checkBillsPayCalendar", {}, function (result) {
            $scope.billsPayCalendar = jQuery.parseJSON(result).dataset;
            for (var i in $scope.billsPayCalendar) {
              logger.logWarning('Realizar Pagamento ' + $scope.billsPayCalendar[i].name);
              $rootScope.pushNotification('Realizar Pagamento ' + $scope.billsPayCalendar[i].name);
            }
          });
        }
        if (!$scope.$parent.$parent.main.isMaster && ($scope.$parent.$parent.main.wizardPurchase)) {
          // wizard purchase advising
          if ($scope.$parent.$parent.main.wizardPurchase) {
            $.post("../backend/application/index.php?rota=/checkBlockedCards", {}, function (result) {
              $scope.bloquedCards = jQuery.parseJSON(result).dataset;
              for (var i in $scope.bloquedCards) {
                $rootScope.pushNotification('Cartão Bloqueado: ' + $scope.bloquedCards[i].name);
              }
            });
          }

        }
        if ($scope.$parent.$parent.main.isMaster || $scope.$parent.$parent.main.financial) {
          $.post("../backend/application/index.php?rota=/checkRiskAnalysis", { hashId: $scope.session.hashId }, function (result) {
            $scope.checkRiskAnalysis = jQuery.parseJSON(result).dataset;
            $scope.$digest();
          });
        }
      };

      $scope.countClients = function () {
        return $scope.clients.length;
      };

      init = function () {
        $scope.checkValidRoute();
        $scope.sumData = undefined;
        $scope.search();
        $scope.loadBlockedClients();

        $scope.clients = [];
        $scope.donutChart2 = {};
        $scope.donutChart2.data = [];
        $scope.donutChart2.data = [
          { label: "TAM", data: 1 },
          { label: "GOL", data: 1 },
          { label: "AZUL", data: 1 },
          { label: "AVIANCA", data: 1 }
        ];
        $scope.comboData = [];
        $scope.loadData();
        $scope.donutChart2.options = {
          series: {
            pie: {
              show: true,
              innerRadius: 0.45
            }
          },
          legend: {
            show: false
          },
          grid: {
            hoverable: true,
            clickable: true
          },
          colors: ["#176799", "#2F87B0", "#42A4BB", "#5BC0C4", "#78D6C7"],
          tooltip: true,
          tooltipOpts: {
            content: "%p.0%, %s",
            defaultTheme: false
          }
        };

        if($scope.$parent.$parent.main.financial) {
          $.post("../backend/application/index.php?rota=/checklist/loadNotes", {}, function (result) {
            $scope.checklist = jQuery.parseJSON(result).dataset;
            $rootScope.userTasks = $scope.checklist.filter(function (note) {
              return note.done == false;
            }).length;
            $scope.$digest();
          });
        }

      };
      return init();
    }
  ]).controller('ClientDataCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function ($scope, $rootScope, $modal, $log, $filter) {

      $scope.open = function () {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "ClientData.html",
          controller: 'ClientInstanceCtrl',
          size: 'lg'
        });
      };
    }
  ]).controller('ClientInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'cfpLoadingBar', 'logger', function ($scope, $rootScope, $modalInstance, $filter, cfpLoadingBar, logger) {
      $scope.filter = {
        name: ''
      };

      $scope.search = function () {
        $scope.filteredClients = $filter('filter')($scope.clients, $scope.filter.name);
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadClientCredits", { searchKeywords: $scope.filter.name }, function (result) {
          $scope.clients = jQuery.parseJSON(result).dataset.clients;
          $scope.$digest();
          cfpLoadingBar.complete();
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };

      return $scope.search();
    }
  ]).controller('NotesCtrl', [
    '$scope', '$rootScope', '$filter', 'logger', '$modal', 'cfpLoadingBar', function ($scope, $rootScope, $filter, logger, $modal, cfpLoadingBar) {
      var init;

      $scope.loadNotes = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
          $scope.Notes = jQuery.parseJSON(result).dataset;
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };

      $scope.open = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
          $scope.Notes = jQuery.parseJSON(result).dataset;
          cfpLoadingBar.complete();
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "Notes.html",
            controller: 'NotesInstanceCtrl',
            resolve: {
              notes: function () {
                return $scope.Notes;
              },
              session: function () {
                return $scope.$parent.$parent.session;
              }
            }
          });
        });
      };

      $scope.removeAll = function () {
        $rootScope.notifications = [];
      };

      $scope.toggleOpen = function () {
        $scope.isOpen = !$scope.isOpen;
      };

      init = function () {
        $scope.isOpen = false;
      };

      return init;
    }
  ]).controller('ChecklistCtrl', [
    '$scope', '$rootScope', '$filter', 'logger', '$modal', 'cfpLoadingBar', function ($scope, $rootScope, $filter, logger, $modal, cfpLoadingBar) {
      var init;

      $scope.open = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/checklist/loadNotes", {}, function (result) {
          $scope.checklist = jQuery.parseJSON(result).dataset;
          $rootScope.userTasks = $scope.checklist.filter(function (note) {
            return note.done == false;
          }).length;
          cfpLoadingBar.complete();
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "Checklist.html",
            controller: 'ChecklistInstanceCtrl',
            resolve: {
              checklist: function () {
                return $scope.checklist;
              }
            }
          });
        });
      };

    }
  ]).controller('NotesInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', 'notes', 'session', 'cfpLoadingBar', function ($scope, $rootScope, $modalInstance, $filter, logger, notes, session, cfpLoadingBar) {
      $scope.notes = notes;
      $scope.filteredNotes = notes;
      $scope.session = session;
      $scope.new = {};
      $scope.filter = {};

      $scope.search = function () {
        $scope.filteredNotes = $filter('filter')($scope.notes, $scope.filter.name);
      };

      $scope.createNote = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/createNote", { data: $scope.new }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.new = {};
            $scope.$digest();
          });
        });
      };

      $scope.remove = function (note) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/removeNote", { data: note }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.$digest();
          });
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('NotesInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', 'notes', 'session', 'cfpLoadingBar', function ($scope, $rootScope, $modalInstance, $filter, logger, notes, session, cfpLoadingBar) {
      $scope.notes = notes;
      $scope.filteredNotes = notes;
      $scope.session = session;
      $scope.new = {};
      $scope.filter = {};

      $scope.search = function () {
        $scope.filteredNotes = $filter('filter')($scope.notes, $scope.filter.name);
      };

      $scope.createNote = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/createNote", { data: $scope.new }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.new = {};
            $scope.$digest();
          });
        });
      };

      $scope.remove = function (note) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/removeNote", { data: note }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.$digest();
          });
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('InfoCtrl', [
    '$scope', '$rootScope', '$filter', 'logger', '$modal', 'cfpLoadingBar', function ($scope, $rootScope, $filter, logger, $modal, cfpLoadingBar, ) {
      var init;



      $scope.infoNotification = function () {
        $rootScope.notificationsInfo = [];
        $scope.notificationsInfoCheck = [];

        $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
          $scope.response = jQuery.parseJSON(result).dataset;
          logger.logWarning("Informações em notificações !!! ");
          for (var i in $scope.response) {
            if (!$scope.response[i].checkBox) {
              $rootScope.pushNotificationInfo("Novas Notificações");
              $scope.notificationsInfoCheck.push(i);
            }
          }
        });
      };

      $scope.open = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
          $scope.Info = jQuery.parseJSON(result).dataset;
          $scope.infoNotification();
          cfpLoadingBar.complete();
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "Info.html",
            controller: 'InfoInstanceCtrl',
            resolve: {
              info: function () {
                return $scope.Info;
              },
              session: function () {
                return $scope.$parent.$parent.session;
              },
              main: function () {
                return $scope.$parent.$parent.main;
              }
            }
          });
        });
      };

      $scope.removeAll = function () {
        $rootScope.notifications = [];
      };

      $scope.toggleOpen = function () {
        $scope.isOpen = !$scope.isOpen;
      };

      init = function () {
        $scope.isOpen = false;
      };

      return init;
    }
  ]).controller('InfoInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', 'info', 'session', 'cfpLoadingBar', 'main', function ($scope, $rootScope, $modalInstance, $filter, logger, info, session, cfpLoadingBar, main) {
      $scope.info = info;
      $scope.filteredInfo = info;
      $scope.session = session;
      $scope.news = {};
      $scope.filter = {};
      $scope.main = main;
      $scope.checkVerify = false;

      $scope.search = function () {
        $scope.filteredInfo = $filter('filter')($scope.info, $scope.filter.name);
      };

      $scope.createInfo = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/createInfo", { data: $scope.news }, function (result) {
          $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
            $scope.info = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.news = {};
            $scope.$digest();
          });
        });
      };

      $scope.remove = function (info) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/removeInfo", { data: info }, function (result) {
          $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
            $scope.info = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.$digest();
          });
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('InfoInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', 'info', 'session', 'cfpLoadingBar', 'main', function ($scope, $rootScope, $modalInstance, $filter, logger, info, session, cfpLoadingBar, main) {
      $scope.info = info;
      $scope.filteredInfo = info;
      $scope.session = session;
      $scope.new = {};
      $scope.filter = {};
      $scope.main = main;

      $scope.read = [];


      $scope.search = function () {
        $scope.filteredInfo = $filter('filter')($scope.info, $scope.filter.name);
      };

      $scope.createInfo = function () {
        cfpLoadingBar.start();
        $rootScope.notificationsInfo = [];
        $scope.notificationsInfoCheck = [];

        $.post("../backend/application/index.php?rota=/createInfo", { data: $scope.new }, function (result) {
          $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
            $scope.info = jQuery.parseJSON(result).dataset;
            logger.logWarning("Informações em notificações !!! ");
            for (var i in $scope.info) {
              if (!$scope.info[i].checkBox) {
                $rootScope.pushNotificationInfo("Novas Notificações");
                $scope.notificationsInfoCheck.push(i);
              }
            }
            $scope.search();
            cfpLoadingBar.complete();
            $scope.new = {};
            $scope.$digest();
          });
        });
      };

      $scope.remove = function (info) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/removeInfo", { data: info }, function (result) {
          $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
            $scope.info = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.$digest();
          });
        });
      };

      $scope.checkVerify = false;

      $scope.toggleCheckFunction = function (info) {
        $scope.checkVerify = true;
        $scope.infoCheck = info;
        $scope.infoCheck.check = $scope.checkVerify;
        $scope.infoCheck.check;
        $rootScope.notificationsInfo = [];
        $scope.notificationsInfoCheck = [];

        $.post("../backend/application/index.php?rota=/systemCheck", { data: $scope.infoCheck }, function (result) {
          $scope.check = jQuery.parseJSON(result).dataset;
          console.log('Change!!');
          $.post("../backend/application/index.php?rota=/loadInfo", {}, function (result) {
            $scope.response = jQuery.parseJSON(result).dataset;
            logger.logWarning("Informações em notificações !!! ");
            for (var i in $scope.response) {
              if (!$scope.response[i].checkBox) {
                $rootScope.pushNotificationInfo("Novas Notificações");
                $scope.notificationsInfoCheck.push(i);
              }
            }
          });
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };

    }
  ]).controller('ChecklistInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', 'checklist', 'cfpLoadingBar', function ($scope, $rootScope, $modalInstance, $filter, logger, checklist, cfpLoadingBar) {
      $scope.checklist = checklist;
      $scope.new = {};

      $scope.loadNotes = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/checklist/loadNotes", {}, function (result) {
          $scope.checklist = jQuery.parseJSON(result).dataset;
          $rootScope.userTasks = $scope.checklist.filter(function (note) {
            return note.done == false;
          }).length;
          cfpLoadingBar.complete();
          $scope.$digest();
        });
      };

      $scope.createNote = function () {
        $.post("../backend/application/index.php?rota=/checklist/createNote", { data: $scope.new }, function (result) {
          $scope.new = {};
          $scope.loadNotes();
        });
      };

      $scope.remove = function (note) {
        $.post("../backend/application/index.php?rota=/checklist/removeNote", { data: note }, function (result) {
          $scope.loadNotes();
        });
      };

      $scope.checkNote = function (note) {
        $.post("../backend/application/index.php?rota=/checklist/checkNote", { data: note }, function (result) {
          $scope.loadNotes();
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('NotesInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', 'notes', 'session', 'cfpLoadingBar', function ($scope, $rootScope, $modalInstance, $filter, logger, notes, session, cfpLoadingBar) {
      $scope.notes = notes;
      $scope.filteredNotes = notes;
      $scope.session = session;
      $scope.new = {};
      $scope.filter = {};

      $scope.search = function () {
        $scope.filteredNotes = $filter('filter')($scope.notes, $scope.filter.name);
      };

      $scope.createNote = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/createNote", { data: $scope.new }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.new = {};
            $scope.$digest();
          });
        });
      };

      $scope.remove = function (note) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/removeNote", { data: note }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotes", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.$digest();
          });
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).controller('ProvidersDialogDataCtrl', [
    '$scope', '$rootScope', '$modal', '$log', '$filter', function ($scope, $rootScope, $modal, $log, $filter) {

      $scope.open = function () {
        var modalInstance;
        modalInstance = $modal.open({
          templateUrl: "ProviderData.html",
          controller: 'ProvidersDialogInstanceCtrl',
          resolve: {
            providers: function () {
              return $scope.providers;
            }
          }
        });
      };

    }
  ]).controller('ProvidersDialogInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', '$timeout', 'logger', 'providers', function ($scope, $rootScope, $modalInstance, $filter, $timeout, logger, providers) {
      $scope.providers = providers;
      $scope.showData = false;

      $scope.search = function () {
        if (load) {
          $timeout.cancel(load);
        }
        var load = $timeout($scope.searchProvider(), 1000);
      };

      $scope.searchProvider = function () {
        $.post("../backend/application/index.php?rota=/loadProvider", { searchKeywords: $scope.filter.name }, function (result) {
          $scope.providers = jQuery.parseJSON(result).dataset.providers;
          if ($scope.providers.length == 1) {
            $scope.filtered = $scope.providers[0];
            $scope.showData = true;
          }
          $scope.$digest();
          if (load) {
            load = undefined;
          }
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]).directive('flotChart', [
    function () {
      return {
        restrict: 'A',
        scope: {
          data: '=',
          options: '='
        },
        link: function (scope, ele, attrs, update, finish, updateInterval) {
          var data, options, plot;
          options = scope.options;
          data = scope.$parent.donutChart2.data;
          plot = $.plot(ele[0], data, options);
          update = function () {
            plot.setData(scope.$parent.donutChart2.data);
            plot.draw();
            setTimeout(finish, updateInterval);
          };
          finish = function () {
            plot.setData(scope.$parent.donutChart2.data);
            plot.draw();
          };
          updateInterval = 2000;
          return update();
        }
      };
    }
  ]).directive('morrisChart', [
    function () {
      return {
        restrict: 'A',
        scope: {
          data: '='
        },
        link: function (scope, ele, attrs, update, bar, updateInterval, finish) {
          var colors, data, func, options;
          data = scope.data;
          updateInterval = 2000;
          switch (attrs.type) {
            case 'line':
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
                resize: true
              };
              return new Morris.Line(options);
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
                data: scope.$parent.comboData,
                xkey: attrs.xkey,
                ykeys: JSON.parse(attrs.ykeys),
                labels: JSON.parse(attrs.labels),
                barColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
                stacked: attrs.stacked || null,
                resize: true
              };
              update = function () {
                setTimeout(finish, updateInterval);
                options.data = scope.$parent.comboData;
                // return new Morris.Bar(options);
              };
              finish = function () {
                options.data = scope.$parent.comboData;
                if (scope.$parent.comboData.length > 0) {
                  return new Morris.Bar(options);
                } else {
                  setTimeout(finish, updateInterval);
                }
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
  ]).controller('ResponsesCtrl', [
    '$scope', '$rootScope', '$filter', 'logger', '$modal', 'cfpLoadingBar', function ($scope, $rootScope, $filter, logger, $modal, cfpLoadingBar) {
      var init;

      $scope.open = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/loadNotesCommun", {}, function (result) {
          $scope.Notes = jQuery.parseJSON(result).dataset;
          cfpLoadingBar.complete();
          var modalInstance;
          modalInstance = $modal.open({
            templateUrl: "Responses.html",
            controller: 'ResponsesInstanceCtrl',
            resolve: {
              notes: function () {
                return $scope.Notes;
              }
            }
          });
        });
      };

      init = function () {
      };

      return init;
    }
  ]).controller('ResponsesInstanceCtrl', [
    '$scope', '$rootScope', '$modalInstance', '$filter', 'logger', 'notes', 'cfpLoadingBar', function ($scope, $rootScope, $modalInstance, $filter, logger, notes, cfpLoadingBar) {
      $scope.notes = notes;
      $scope.filteredNotes = notes;
      $scope.new = {};
      $scope.filter = {};

      $scope.search = function () {
        $scope.filteredNotes = $filter('filter')($scope.notes, $scope.filter.name);
      };

      $scope.createNote = function () {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/createNoteCommun", { data: $scope.new }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotesCommun", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.new = {};
            $scope.$digest();
          });
        });
      };

      $scope.remove = function (note) {
        cfpLoadingBar.start();
        $.post("../backend/application/index.php?rota=/removeNoteCommun", { data: note }, function (result) {
          $.post("../backend/application/index.php?rota=/loadNotesCommun", {}, function (result) {
            $scope.notes = jQuery.parseJSON(result).dataset;
            $scope.search();
            cfpLoadingBar.complete();
            $scope.$digest();
          });
        });
      };

      $scope.cancel = function () {
        $modalInstance.dismiss("cancel");
      };
    }
  ]);
})();
;


(function () {
  'use strict';
  angular.module('app.chart').controller('MorrisChartCtrl', [
    '$scope', function ($scope) {
      $scope.mainData = [
        {
          month: '2013-01',
          xbox: 294000,
          will: 136000,
          playstation: 244000
        }, {
          month: '2013-02',
          xbox: 228000,
          will: 335000,
          playstation: 127000
        }, {
          month: '2013-03',
          xbox: 199000,
          will: 159000,
          playstation: 130000
        }, {
          month: '2013-04',
          xbox: 174000,
          will: 160000,
          playstation: 82000
        }, {
          month: '2013-05',
          xbox: 255000,
          will: 318000,
          playstation: 82000
        }, {
          month: '2013-06',
          xbox: 298400,
          will: 401800,
          playstation: 98600
        }, {
          month: '2013-07',
          xbox: 370000,
          will: 225000,
          playstation: 159000
        }, {
          month: '2013-08',
          xbox: 376700,
          will: 303600,
          playstation: 130000
        }, {
          month: '2013-09',
          xbox: 527800,
          will: 301000,
          playstation: 119400
        }
      ];
      $scope.simpleData = [
        {
          year: '2008',
          value: 20
        }, {
          year: '2009',
          value: 10
        }, {
          year: '2010',
          value: 5
        }, {
          year: '2011',
          value: 5
        }, {
          year: '2012',
          value: 20
        }, {
          year: '2013',
          value: 19
        }
      ];
      $scope.comboData = [
        {
          month: 'Jan',
          a: 50,
          b: 70,
          c: 7
        }, {
          month: 'Fev',
          a: 70,
          b: 80,
          c: 8
        }, {
          month: 'Mar',
          a: 78,
          b: 89,
          c: 9
        }, {
          month: 'Apr',
          a: 70,
          b: 140,
          c: 14
        }, {
          month: 'Mai',
          a: 120,
          b: 90,
          c: 9
        }, {
          month: 'Jun',
          a: 110,
          b: 120,
          c: 12
        }
      ];
      $scope.donutData = [
        {
          label: "Download Sales",
          value: 12
        }, {
          label: "In-Store Sales",
          value: 30
        }, {
          label: "Mail-Order Sales",
          value: 20
        }, {
          label: "Online Sales",
          value: 19
        }
      ];
    }
  ]);
})();


;
(function () {
  'use strict';
  angular.module('app.chart').controller('FlotChartCtrl', [
    '$scope', function ($scope) {
      var areaChart, barChart;
      areaChart = {};
      areaChart.data1 = [[2007, 15], [2008, 20], [2009, 10], [2010, 5], [2011, 5], [2012, 20], [2013, 28]];
      areaChart.data2 = [[2007, 15], [2008, 16], [2009, 22], [2010, 14], [2011, 12], [2012, 19], [2013, 22]];
      $scope.area = {};
      $scope.area.data = [
        {
          data: areaChart.data1,
          label: "Value A",
          lines: {
            fill: true
          }
        }, {
          data: areaChart.data2,
          label: "Value B",
          points: {
            show: true
          },
          yaxis: 2
        }
      ];
      $scope.area.options = {
        series: {
          lines: {
            show: true,
            fill: false
          },
          points: {
            show: true,
            lineWidth: 2,
            fill: true,
            fillColor: "#ffffff",
            symbol: "circle",
            radius: 5
          },
          shadowSize: 0
        },
        grid: {
          hoverable: true,
          clickable: true,
          tickColor: "#f9f9f9",
          borderWidth: 1,
          borderColor: "#eeeeee"
        },
        colors: ["#23AE89", "#6A55C2"],
        tooltip: true,
        tooltipOpts: {
          defaultTheme: false
        },
        xaxis: {
          mode: "time"
        },
        yaxes: [
          {}, {
            position: "right"
          }
        ]
      };
      barChart = {};
      barChart.data1 = [[2008, 20], [2009, 10], [2010, 5], [2011, 5], [2012, 20], [2013, 28]];
      barChart.data2 = [[2008, 16], [2009, 22], [2010, 14], [2011, 12], [2012, 19], [2013, 22]];
      barChart.data3 = [[2008, 12], [2009, 30], [2010, 20], [2011, 19], [2012, 13], [2013, 20]];
      $scope.barChart = {};
      $scope.barChart.data = [
        {
          label: "Value A",
          data: barChart.data1
        }, {
          label: "Value B",
          data: barChart.data2
        }, {
          label: "Value C",
          data: barChart.data3
        }
      ];
      $scope.barChart.options = {
        series: {
          stack: true,
          bars: {
            show: true,
            fill: 1,
            barWidth: 0.3,
            align: "center",
            horizontal: false,
            order: 1
          }
        },
        grid: {
          hoverable: true,
          borderWidth: 1,
          borderColor: "#eeeeee"
        },
        tooltip: true,
        tooltipOpts: {
          defaultTheme: false
        },
        colors: ["#23AE89", "#2EC1CC", "#FFB61C", "#E94B3B"]
      };
      $scope.pieChart = {};
      $scope.pieChart.data = [
        {
          label: "Download Sales",
          data: 12
        }, {
          label: "In-Store Sales",
          data: 30
        }, {
          label: "Mail-Order Sales",
          data: 20
        }, {
          label: "Online Sales",
          data: 19
        }
      ];
      $scope.pieChart.options = {
        series: {
          pie: {
            show: true
          }
        },
        legend: {
          show: true
        },
        grid: {
          hoverable: true,
          clickable: true
        },
        colors: ["#23AE89", "#2EC1CC", "#FFB61C", "#E94B3B"],
        tooltip: true,
        tooltipOpts: {
          content: "%p.0%, %s",
          defaultTheme: false
        }
      };
      $scope.donutChart = {};
      $scope.donutChart.data = [
        {
          label: "Download Sales",
          data: 12
        }, {
          label: "In-Store Sales",
          data: 30
        }, {
          label: "Mail-Order Sales",
          data: 20
        }, {
          label: "Online Sales",
          data: 19
        }
      ];
      $scope.donutChart.options = {
        series: {
          pie: {
            show: true,
            innerRadius: 0.5
          }
        },
        legend: {
          show: true
        },
        grid: {
          hoverable: true,
          clickable: true
        },
        colors: ["#23AE89", "#2EC1CC", "#FFB61C", "#E94B3B"],
        tooltip: true,
        tooltipOpts: {
          content: "%p.0%, %s",
          defaultTheme: false
        }
      };
      $scope.donutChart2 = {};
      $scope.donutChart2.data = [
        {
          label: "TAM",
          data: 0
        }, {
          label: "GOL",
          data: 0
        }, {
          label: "AZUL",
          data: 0
        }, {
          label: "AVIANCA",
          data: 0
        }
      ];
      return $scope.donutChart2.options = {
        series: {
          pie: {
            show: true,
            innerRadius: 0.45
          }
        },
        legend: {
          show: false
        },
        grid: {
          hoverable: true,
          clickable: true
        },
        colors: ["#176799", "#2F87B0", "#42A4BB", "#5BC0C4", "#78D6C7"],
        tooltip: true,
        tooltipOpts: {
          content: "%p.0%, %s",
          defaultTheme: false
        }
      };
    }
  ]).controller('FlotChartCtrlRealtime', ['$scope', function ($scope) { }
  ]);
})();


;