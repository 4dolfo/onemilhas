(function () {
	'use strict';
	angular.module('app.pagSeguro').controller('TransactionsCtrl', [
		'$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', function($scope, $rootScope, $filter, cfpLoadingBar, logger) {
		var init;
		var original;
		$scope.searchKeywords = '';
		$scope.filteredTransaction = [];
		$scope.row = '';

		$scope.select = function(page) {
			var end, start;
			start = (page - 1) * $scope.numPerPage;
			end = start + $scope.numPerPage;
			return $scope.currentPageTransactions = $scope.filteredTransaction.slice(start, end);
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
			$scope.filteredTransaction = $filter('filter')($scope.transactions, $scope.searchKeywords);
			return $scope.onFilterChange();
		};

		$scope.setSelected = function() {
			var original = angular.copy(this.transaction);
			$rootScope.$emit('openPagSeguroTransaction', {
				main: $scope.$parent.$parent.main,
				hashId: $scope.$parent.$parent.session.hashId,
				transaction: original
			});
		};

		$scope.loadTransactions = function() {
			$.post("../backend/application/index.php?rota=/loadPagSeguroTransactions", $scope.session, function(result){
				$scope.transactions = jQuery.parseJSON(result).dataset;
				cfpLoadingBar.complete();
				$scope.search();
			});
		};

		$scope.order = function(rowName) {
	        if ($scope.row === rowName) {
	          return;
	        }
	        $scope.row = rowName;
	        $scope.filteredTransaction = $filter('orderBy')($scope.transactions, rowName);
	        return $scope.onOrderChange();
	      };

		$scope.findDate = function(date) {
			if(date == '') {
				return '';
			}
			return new Date(date);
		};

		$scope.numPerPageOpt = [10, 30, 50, 100];
		$scope.numPerPage = $scope.numPerPageOpt[2];
		$scope.currentPage = 1;
		$scope.currentPageTransactions = [];
		init = function() {
			$scope.checkValidRoute();
			$scope.tabindex = 0;
			cfpLoadingBar.start();
			$rootScope.modalOpen = false;
			$scope.loadTransactions();
		};
		return init();
	}
	]).controller('PagSeguroTransactionModalCtrl', [
		'$scope', '$rootScope', '$modal', '$log', '$filter', function($scope, $rootScope, $modal, $log, $filter) {

		$rootScope.$on('openPagSeguroTransaction', function(event, args) {
			event.stopPropagation();
			if($rootScope.modalOpen == false) {
				$rootScope.modalOpen = true;
				$scope.open(args);
			}
		});

		$scope.open = function(args) {
			var modalInstance;
			modalInstance = $modal.open({
				templateUrl: "PagSeguroTransactionModalCtrl.html",
				controller: 'PagSeguroTransactionInstanceCtrl',
				resolve: {
					args: function() {
						return args;
					}
				}
			});
			modalInstance.result.then(function(args) {
				$rootScope.modalOpen = false;
			}, function() {
				$rootScope.modalOpen = false;
			});
		};
	}
	]).controller('PagSeguroTransactionInstanceCtrl', [
		'$scope', '$rootScope', '$modalInstance', 'logger', 'args', function($scope, $rootScope, $modalInstance, logger, args) {
		$scope.args = args;
		$scope.selected = $scope.args.transaction;

		if($scope.selected.issueDate != '') {
			$scope.selected._issueDate = new Date($scope.selected.issueDate);
		}
		$("#pagseguro_refund_value").maskMoney({thousands:'.',decimal:',', precision: 2});

		$scope.cancel = function() {
			$modalInstance.dismiss("cancel");
		};

		$scope.confirRefund = function() {
			$.post("../backend/application/index.php?rota=/generatePagSegurorefund", {hashId: $scope.args.hashId, data: $scope.selected}, function(result){
				if (jQuery.parseJSON(result).message.type == 'S'){
					logger.logSuccess(jQuery.parseJSON(result).message.text);
				} else {
					logger.logError(jQuery.parseJSON(result).message.text);
				}
				$modalInstance.dismiss("cancel");
			});
		};

		$scope.confirCancel = function() {
			$.post("../backend/application/index.php?rota=/generatePagSeguroCancel", {hashId: $scope.args.hashId, data: $scope.selected}, function(result){
				if (jQuery.parseJSON(result).message.type == 'S'){
					logger.logSuccess(jQuery.parseJSON(result).message.text);
				} else {
					logger.logError(jQuery.parseJSON(result).message.text);
				}
				$modalInstance.dismiss("cancel");
			});
		};

	}
	]);
})();
;