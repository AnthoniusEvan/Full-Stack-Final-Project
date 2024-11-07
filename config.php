<?php
$dbHost = 'localhost';
$dbName = 'petvoyage';

$dbUser = 'root';
$dbPass = '';

$dbCon = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($dbCon->connect_errno){
    echo "Failed to connect to MySQL: " . $mysqli->$connect_errno;
}

$maxRows = 10;
$salt = "INwuDV9S3b3UaQlCzVuy69UqebmxYsvaSKhbaw";
$censorText = "********";
?>