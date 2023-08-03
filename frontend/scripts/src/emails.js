(function () {
	'use strict';
	angular.module('app.maintenance').controller('EmailsCtrl', [
		'$scope', '$rootScope', '$filter', 'cfpLoadingBar', 'logger', 'FileUploader', function($scope, $rootScope, $filter, cfpLoadingBar, logger, FileUploader) {
			var init;

			$scope.status = ['PENDENTE', 'ENVIADO'];
			$scope.searchKeywords = '';
			$scope.currentPageEmails = [];
			$scope.row = '';

			$scope.fillEmailContent = function() {
				$scope.selected = angular.copy(this.email);
				$scope.uploader.formData = {hashId: $scope.session.hashId, data: $scope.selected};

				$scope.webEmail.emailpartner = $scope.selected.partner;
				$scope.webEmail.subject = $scope.selected.subject;
				$scope.webEmail.emailContent = $scope.selected.content;

				$scope.tabindex = 1;
			};

			$scope.select = function(page) {
				var end, start;
				start = (page - 1) * $scope.numPerPage;
				end = start + $scope.numPerPage;
				$scope.currentPageEmails = $scope.filteredEmails.slice(start, end);
				$scope.$digest();
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
				$scope.filteredEmails = $filter('filter')($scope.Emails, $scope.searchKeywords);
				return $scope.onFilterChange();
			};

			$scope.findDate = function(date) {
				var data = new Date(date);
				return data.setDate(data.getDate() + 1);
			};

			$scope.removeEmail = function(email) {
				$.post("../backend/application/index.php?rota=/removeEmailScheduled", {hashId: $scope.session.hashId, data: email }, function(result){
					if (jQuery.parseJSON(result).message.type == 'S'){
						logger.logSuccess(jQuery.parseJSON(result).message.text);
						$scope.loadEmails();
					} else {
						logger.logError(jQuery.parseJSON(result).message.text);
					}
				});
			};

			$scope.removeItem = function(item) {
				$.post("../backend/application/index.php?rota=/removeFileScheduled", {hashId: $scope.session.hashId, data: $scope.selected, file: item}, function(result){
					if (jQuery.parseJSON(result).message.type == 'S'){
						logger.logSuccess(jQuery.parseJSON(result).message.text);
						$scope.loadFilesSelected();
					} else {
						logger.logError(jQuery.parseJSON(result).message.text);
					}
				});
			};

			$scope.loadFilesSelected = function() {
				$.post("../backend/application/index.php?rota=/loadFilesSelected", { hashId: $scope.session.hashId, data: $scope.selected }, function(result){
					$scope.selected.attachments = jQuery.parseJSON(result).dataset;
					$scope.$digest();
				});
			};

			$scope.mailOrder = function(){
				$.post("../backend/application/index.php?rota=/sendMailScheduled", {hashId: $scope.session.hashId, data: $scope.selected, mail: $scope.webEmail}, function(result){
					if (jQuery.parseJSON(result).message.type == 'S'){
						logger.logSuccess(jQuery.parseJSON(result).message.text);
						$scope.loadFilesSelected();
					} else {
						logger.logError(jQuery.parseJSON(result).message.text);
					}
				});
			};

			$scope.cancelEdit = function() {
				$scope.tabindex = 0;
				$scope.selected = {};
				$scope.loadEmails();
			};

			$scope.loadEmails = function() {
				$.post("../backend/application/index.php?rota=/loadEmails", { hashId: $scope.session.hashId, data: $scope.filter }, function(result){
					$scope.Emails = jQuery.parseJSON(result).dataset;
					$scope.search();
				});
			};

			$scope.getStatusOrder = function(status) {
				switch (status) {
					case 'PENDENTE':
						return "Pendente";
					case 'RESERVA':
						return "Reserva";
					case 'EMITIDO':
						return "Emitido";
					case 'ENVIADO':
						return "Enviado";
					case 'CANCELADO':
						return "Cancelado";
					case 'ESPERA':
						return "Espera";
				}
			};

			$scope.orderTag = function(status) {
				switch (status) {
					case 'PENDENTE':
						return "label label-warning";
					case 'RESERVA':
						return "label label-info";
					case 'ENVIADO':
						return "label label-success";
					case 'EMITIDO':
						return "label label-success";
					case 'CANCELADO':
						return "label label-danger";
					case 'ESPERA':
						return "label label-default";
				}
			};

			$scope.sendEmails = function() {
				$.post("../backend/application/index.php?rota=/sendAllEmails", { hashId: $scope.session.hashId }, function(result){
					if (jQuery.parseJSON(result).message.type == 'S'){
						logger.logSuccess("Emails Na fila para envio!");
						$scope.loadFilesSelected();
					} else {
						logger.logError(jQuery.parseJSON(result).message.text);
					}
				});
			};

			$scope.numPerPageOpt = [10, 30, 50, 100];
			$scope.numPerPage = $scope.numPerPageOpt[2];
			$scope.currentPage = 1;
			$scope.currentPageEmails = [];

			init = function() {
				$scope.checkValidRoute();
				$scope.tabindex = 0;
				$scope.loadEmails();
				$scope.filter = {};
				$scope.webEmail = {};

				$scope.uploader = new FileUploader();
				$scope.uploader.url = "../backend/application/index.php?rota=/saveFileScheduled";
				$scope.uploader.autoUpload = true;
				$scope.uploader.filters.push({
						name: 'customFilter',
						fn: function(item /*{File|FileLikeObject}*/, options) {
								return this.queue.length < 10;
						}
				});

				$scope.uploader.onCompleteItem = function(fileItem, response, status, headers) {
					$scope.loadFilesSelected();
				};

			};

			return init();
		}
	]).controller('EmailsModalCtrl', [
	'$scope', '$rootScope', '$modal', '$log', function($scope, $rootScope, $modal, $log) {

		$scope.open = function() {
			$scope.status = $scope.$parent.status;
			$scope.filter = $scope.$parent.filter;
			var modalInstance;
			modalInstance = $modal.open({
				templateUrl: "EmailsScheduled.html",
				controller: 'EmailsScheduledModalInstanceCtrl',
				periods: $scope.$parent.periods,
				resolve: {
					filter: function() {
						return $scope.filter;
					},
					status: function() {
						return $scope.status;
					}
				}
			});
			modalInstance.result.then((function(filter) {
				if (filter !== undefined) {
					filter._dateFrom = $rootScope.formatServerDate(filter.dateFrom);
					filter._dateTo = $rootScope.formatServerDate(filter.dateTo);
				}

				$scope.$parent.filter = filter;
				$scope.$parent.loadEmails();
			}));
		};
	}
	]).controller('EmailsScheduledModalInstanceCtrl', [
	'$scope', '$rootScope', '$modalInstance', 'filter', 'status', function($scope, $rootScope, $modalInstance, filter, status) {
		$scope.filter = filter;
		$scope.status = status;

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