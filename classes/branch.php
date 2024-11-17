<?php
if (file_exists('./config.php')){
    require_once('./config.php');
}

if (file_exists('../config.php')){
    require_once('../config.php');
}

class Branch{
    private $dbCon;

    public function __construct($dbConnection){
        $this->dbCon = $dbConnection;
    }

    function __destruct(){

    }
    private function get_new_id(){
        $columns = "max(Id) as maxId";
        $resCount=$this->get_data($columns);
        $rCount=$resCount->fetch_array();
        return $rCount["maxId"] + 1;
    }
    public function get_data($column="*", $constraints="1",$limit=10){
        $q = "SELECT $column FROM branch WHERE $constraints LIMIT $limit";

        $resultSet = $this->dbCon->query($q);
        return $resultSet;
    }

    public function get_inner_join_data($column="*", $constraints="1",$limit=10){
        $q = "SELECT $column FROM branch b INNER JOIN city c ON b.CityId = c.Id WHERE $constraints LIMIT $limit";

        $resultSet = $this->dbCon->query($q);
        return $resultSet;
    }

    public function insert_data($data = array()){
        foreach($data as $key => $value){
            $data[$key] = "'".$value."'";
        }

        $columns = implode(",", array_keys($data));
        $values = implode(",", $data);

        $newId = $this->get_new_id();
        $q = "INSERT INTO branch(Id, $columns, LastUpdateTime) VALUES($newId, $values, CURRENT_TIMESTAMP())";
        return $this->dbCon->query($q);
    }

    public function name_already_exists($name){
        $q = "SELECT Name FROM branch WHERE Name = '$name'";
        $resultSet = $this->dbCon->query($q);

        return mysqli_num_rows($resultSet)>0;
    }

    public function update_data($id, $update_data = array()){
        $updates = array();
        foreach($update_data as $column => $value){
            $updates[] = "$column = '$value'";
        }

        $q = "UPDATE branch SET ".implode(',',$updates).", LastUpdateTime=CURRENT_TIMESTAMP() WHERE Id = $id";
        return $this->dbCon->query($q);
    }

    public function remove_data($id){
        $q = "DELETE FROM branch WHERE Id = $id";
        return $this->dbCon->query($q);
    }
}

?>