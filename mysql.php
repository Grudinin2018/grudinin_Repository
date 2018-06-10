<?php
	
	$url = "localhost";
	$name = "vdimitrya_fialki";
	$pass = "g0t0x0560addeff123";
	$base = "vdimitrya_fialki";

	function mysqlQuery($query) {
		global $url, $name, $pass, $base;
		$mysqli = new mysqli($url, $name, $pass, $base); 
		if (mysqli_connect_errno()) { 
    		printf("Подключение невозможно: %s\n", mysqli_connect_error()); 
    		exit(); 
		}
		$mysqli->set_charset('utf8');
		$result = $mysqli->query($query);
		$mysqli->close(); 
		return $result;
	}

?>