<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	require_once('includes/config.php');
	require_once('includes/db.php');
	require_once('includes/functions.php');
	require_once('includes/classes.php');

	date_default_timezone_set('America/New_York');

	$test = new test($testURL, $needle, $clientName, $textBeltAPIKey, $recipientPhone, $databaseHost, $databaseName, $databaseUsername, $databasePassword);
	$test->execute();

?>