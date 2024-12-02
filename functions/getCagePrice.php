<?php
include_once("../config.php");
$cage_id = $_POST['cage_id'];
$origin_city = $_POST['origin_city'];
$dest_city = $_POST['dest_city'];

$q1 = "SELECT FORMAT(Rate, 0) AS price, CityOrigin, CityDestination FROM TransportRate WHERE CityOrigin = $origin_city AND CityDestination = $dest_city AND CageId = $cage_id";

$q2 = "SELECT FORMAT(Rate, 0) AS price, CityOrigin, CityDestination FROM TransportRate WHERE CityOrigin = $dest_city AND CityDestination = $origin_city AND CageId = $cage_id";

$resultSet1 = $dbCon->query($q1);
$resultSet2 = $dbCon->query($q2);
$result = 0;
if (mysqli_num_rows($resultSet1) > 0) { //if found
    $status = "OK";

    $row = $resultSet1->fetch_array();
    $result =  $row['price'];
      
}
else if  (mysqli_num_rows($resultSet2) > 0) { //if found
    $status = "OK";

    $row = $resultSet2->fetch_array();
    $result =  $row['price'];
}
else { //if not found
    $status = "Price rate for that item does not exist";
}

echo(json_encode(array("status"=>$status, "price"=>$result)));
?>