<?php
if (file_exists('./config.php')){
    require_once('./config.php');
}

if (file_exists('../config.php')){
    require_once('../config.php');
}

class City{
    private $dbCon;

    public function __construct($dbConnection){
        $this->dbCon = $dbConnection;
    }

    function __destruct(){

    }

    public function get_data($column="*", $constraints="1",$limit=10){
        $q = "SELECT $column FROM city WHERE $constraints LIMIT $limit";

        $resultSet = $this->dbCon->query($q);
        return $resultSet;
    }

    public function insert_data($data = array()){
        foreach($data as $key => $value){
            $data[$key] = "'".$value."'";
        }

        $columns = implode(",", array_keys($data));
        $values = implode(",", $data);

        $q = "INSERT INTO city($columns, LastUpdateTime) VALUES($values, CURRENT_TIMESTAMP())";
        return $this->dbCon->query($q);
    }

    public function name_already_exists($name){
        $q = "SELECT Name FROM city WHERE Name = '$name'";
        $resultSet = $this->dbCon->query($q);

        return mysqli_num_rows($resultSet)>0;
    }

    public function update_data($id, $update_data = array()){
        $updates = array();
        foreach($update_data as $column => $value){
            $updates[] = "$column = '$value'";
        }

        $q = "UPDATE city SET ".implode(',',$updates).", LastUpdateTime=CURRENT_TIMESTAMP() WHERE Id = $id";
        return $this->dbCon->query($q);
    }

    public function remove_data($id){
        $q = "DELETE FROM city WHERE Id = $id";
        return $this->dbCon->query($q);
    }
}

?>