<?php
/*
$mysql_server_name = "localhost";
$mysql_username = "root";
$mysql_password = "senhafrt2006";
$mysql_database = "nutri";

$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	die();
}
*/
$bd = new PDO("mysql:host=localhost; dbname=nutri","root","senhafrt2006");

?>