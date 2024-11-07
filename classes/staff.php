<?php
if (file_exists('./config.php')){
    require_once('./config.php');
}

if (file_exists('../config.php')){
    require_once('../config.php');
}

class Staff{
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
        $q = "SELECT $column FROM staff WHERE $constraints LIMIT $limit";

        $resultSet = $this->dbCon->query($q);
        return $resultSet;
    }

    public function get_inner_join_data($column="*", $constraints="1",$limit=10){
        $q = "SELECT $column FROM staff s INNER JOIN branch b ON s.BranchId = b.Id LEFT JOIN staff modifier ON s.LastModifier = modifier.Id WHERE $constraints LIMIT $limit";

        $resultSet = $this->dbCon->query($q);
        return $resultSet;
    }

    public function insert_data($data = array()){
        global $salt;
        foreach($data as $key => $value){
            if ($key=="Password"){
                $data[$key]="SHA2('".$value.$salt."', 256)";
            }
            else {
                $data[$key] = "'".$value."'";
            }
        }

        $columns = implode(",", array_keys($data));
        $values = implode(",", $data);

        $new_id = $this->get_new_id();
        $q = "INSERT INTO staff(Id, $columns) VALUES('$new_id', $values)"; // TODO ADD LAST MODIFIER
        return $this->dbCon->query($q);
    }

    public function update_data($id, $update_data = array()){
        $updates = array();
        global $salt;
        foreach($update_data as $column => $value){
            if ($column=="Password"){
                $updates[]= "$column = SHA2('".$value.$salt."', 256)";
            }
            else{
                $updates[] = "$column = '$value'";
            }
        }

        $q = "UPDATE staff SET ".implode(',',$updates)." WHERE Id = $id";
        return $this->dbCon->query($q);
    }

    public function remove_data($id){
        $q = "DELETE FROM staff WHERE Id = $id";
        return $this->dbCon->query($q);
    }
}

?>