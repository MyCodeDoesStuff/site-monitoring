<?php
	function databaseConnect($host, $database, $username, $password) {
		return mysqli_connect($host, $username, $password, $database);
	}

	function databaseClose($connection) {
		return mysqli_close($connection);
	}
?>