<?php
require_once("config.php");
try{
	$conn	=	mssql_connect($dbhost,$dbuser,$dbpasswd);
}catch(Exception $e){
	die("Failed to get DB conn: " . $e->getMessage() . "\n");
}

?>