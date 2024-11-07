<?php
session_start();

if (!file_exists('sanitizers.php')){
    die("Function Sanitizers Not Found");
}
require_once('sanitizers.php');

if (!file_exists('../classes/staff.php')){
    die("Class staff Not Found");
}
require_once('../classes/staff.php');

$staff = new Staff($dbCon);
$id = $_POST["id"];
list($password, $validation_status) = sanitize($dbCon, $_POST["password"], "string");

$resultSet=$staff->get_data("Id", "Id=$id AND Password=SHA2('".$password.$salt."',256)");

$status="INCORRECT";
if (mysqli_num_rows($resultSet)>0){
    $status = "OK";
}

echo($status);
?>