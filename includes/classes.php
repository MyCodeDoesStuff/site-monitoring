<?php
	class test {
		protected $databaseHost;
		protected $databaseName;
		protected $databaseUsername;
		protected $databasePassword;
		protected $testURL;
		protected $needle;
		protected $clientName;
		protected $textBeltAPIKey;
		protected $recipientPhone;
		protected $timestamp;
		protected $result;
		protected $notify;

		public function __construct($testURL, $needle, $clientName, $textBeltAPIKey, $recipientPhone, $databaseHost, $databaseName, $databaseUsername, $databasePassword) {
				$this->databaseHost = $databaseHost;
				$this->databaseName = $databaseName;
				$this->databaseUsername = $databaseUsername;
				$this->databasePassword = $databasePassword;
				$date = new DateTime();
				$this->timestamp = $date->format('Y-m-d H:i:s');
				$this->testURL = $testURL;
				$this->needle = $needle;
				$this->clientName = $clientName;
				$this->textBeltAPIKey = $textBeltAPIKey;
				$this->recipientPhone = $recipientPhone;
				$this->result = 1;
				$this->notify = 0;
		}

		public function execute() {
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $this->testURL);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			$response = curl_exec($ch);
			curl_close($ch);

			if(!$this->analyze($response)) {
				$this->result = 0;
				if($this->shouldNotify()) {
					$this->notify();
				}
			}

			$this->save();

		}

		protected function analyze($response) {
			if(stripos($response, $this->needle) !== FALSE) {
				return true;
			}
			return false;
		}

		protected function save() {
			$databaseConnection = databaseConnect($this->databaseHost, $this->databaseName, $this->databaseUsername, $this->databasePassword);
			if($statement = $databaseConnection->prepare("INSERT INTO `tests` (`test_timestamp`, `test_result`, `test_notify`) VALUES(?, ?, ?)")) {
				$statement->bind_param("sii", $this->timestamp, $this->result, $this->notify);
				$statement->execute();
				$statement->close();
			}
			databaseClose($databaseConnection);
		}

		protected function shouldNotify() {
			$resultCount = 0;
			$one = 1;
			$cutoffTime = (new DateTime($this->timestamp))->sub(DateInterval::createFromDateString('15 minutes'))->format('Y-m-d H:i:s');
			$databaseConnection = databaseConnect($this->databaseHost, $this->databaseName, $this->databaseUsername, $this->databasePassword);
			if($statement = $databaseConnection->prepare("SELECT `test_timestamp` FROM `tests` WHERE `test_timestamp` > ? AND `test_notify` = ? LIMIT 1;")) {
				$statement->bind_param('si', $cutoffTime, $one);
				$statement->execute();
				$statement->bind_result($testTimestamp);
				$xPosition = 0;
				while($statement->fetch()) {
					$resultCount++;
				}
				$statement->close();
			}
			databaseClose($databaseConnection);
			if($resultCount == 0) {
				return true;
			}
			return false;
		}

		protected function notify() {
			$message = "We detected a problem with " . $this->clientName;
			$fields = array(
				'phone' => urlencode($this->recipientPhone),
				'message' => urlencode($message),
				'key' => urlencode($this->textBeltAPIKey)
			);
			$fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&'; 
			}
			$fields_string = rtrim($fields_string, '&');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://textbelt.com/text");
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			curl_close($ch);

			$this->notify = 1;
		}


	}

?>