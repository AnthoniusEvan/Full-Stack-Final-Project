<?php
class User{
    private $dbCon;

    public int $id;
    public string $username;
    public string $name;
    public string $role;

    public function __construct($dbConnection){
        $this->dbCon = $dbConnection;
    }

    function __destruct(){

    }

    public function login($username, $password){
        global $salt;
        
        $q = "SELECT Id, Username, Name, Role FROM staff WHERE Username='$username' AND Password=SHA2('$password$salt',256)";

        $resultSet = $this->dbCon->query($q);
        if (mysqli_num_rows($resultSet) > 0){
            $row = $resultSet->fetch_array();
            $this->id = $row["Id"];
            $this->username = $row["Username"];
            $this->name = $row["Name"];
            $this->role = $row["Role"];

            return true;
        }
        else{
            return false;
        }
    }
}
?>