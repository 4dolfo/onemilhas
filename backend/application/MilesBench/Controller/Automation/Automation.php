<?php

namespace MilesBench\Controller\Automation;

use MilesBench\Application;
use MilesBench\Model;
use MilesBench\Request\Request;
use MilesBench\Request\Response;

class Automation {

	public function automationMorning() {
		$env = getenv('ENV') ? getenv('ENV') : 'production';
        if($env == 'production') {

			$Billets = new Billets();
			//billets reminder
			$Billets->dailyReminder();

			//Status 'Coberto' reminder
			$Billets->checkCovered();

			//Clientes Bloqueados sem pendencias no financeiro
			$Billets->checkClientsBloqued();

			//Boletos emitidos com data de vencimento menor do que o dia atual 
			$Billets->dailyReminderLessDays();

			// $Miles = new Miles();
			//sending stock
			// $Miles->sendStock();

			//searching for divergences
			// $Miles->searchForDivergences();

			$Clients = new Clients();
			$Clients->dailyReminder();

			// Miles
			$Miles = new Miles();
			$Miles->vencimentos30Dias();
		}
	}

	public function startOfTheDay() {
		$Billets = new Billets();
		// Saving historical payment of customers
		$Billets->saveHistoricalCustomers();

		$Checklist = new Checklist();
		// Saving historical payment of customers
		$Checklist->resetChecklists();

		// $Marketing = new Marketing();
		// $Marketing->dailyMarketingPricing();
	}

	public function automationEveryMinute() {
		$Precification = new Precification();
		$Precification->checkPromo();
	}

	public function startOfTheMonth() {
		$MilesSRMAzul = new MilesSRMAzul();
		$MilesSRMAzul->sendLogs();
		$MilesSRMAzul->cleanTables();
	}
}
