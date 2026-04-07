<?php

$servername="localhost";
$username="root";
$password="";
$dbname="elder_care";

try {
	$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
	$options = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
		];
		$conn = new PDO($dsn, $username, $password, $options);
		
} catch (PDOexceptions $e) {
	die ("Connection Failed: " . $e->getMessage());
}
?>
