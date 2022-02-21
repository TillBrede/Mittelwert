<?php

declare(strict_types=1);
	class Mittelwert extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();


			//Properties
			$this->RegisterPropertyInteger('Source', 0);
			$t = time();
			$startTime = sprintf('{"year":%s,"month":%s,"day":%s}', date('Y', $t), date('n', $t), date('j', $t), );
			$this->RegisterPropertyString('StartDate', $startTime);
			$this->RegisterPropertyInteger('Target', 0);
			$this->RegisterPropertyInteger('Days', 0);

		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function Calculate() {
			$startTime = $this->propertyToTimestamp('StartDate');
			$days = $this->ReadPropertyInteger('Days');
			$averages = [];
			while ($startTime < time()) {
				$averages[] = $this->getAverage($startTime);
				$startTime += 86400;
			}
			$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
			AC_ReAggregateVariable($archiveID, $this->ReadPropertyInteger('Target'));
						
		}

		private function getAverage($startTime) {
			$days = $this->ReadPropertyInteger('Days');
			$past = $startTime - ($days * 86400);
			$sourceID = $this->ReadPropertyInteger('Source');
			$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
			$loggedValues = AC_GetAggregatedValues($archiveID, $sourceID, 1, $past, $startTime, 0);
			//Sum up the average values of the last $days days
			$sum = 0;
			foreach($loggedValues as $value) {
				$sum += $value['Avg'];
			}
			$average = $sum / $days;
			AC_AddLoggedValues($archiveID, $this->ReadPropertyInteger('Target'), [['Value' => $average, 'TimeStamp' => $startTime]]);
			return $average;
		}
		
		private function propertyToTimestamp($property) {
			$time = json_decode($this->ReadPropertyString($property), true);
			return mktime(0, 0, 0, $time['month'], $time['day'], $time['year']);
		}
	}