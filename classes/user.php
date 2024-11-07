<?php
class User{
    private $dbCon;

    public int $id;
    public string $username;

    public function __construct($dbConnection){
        $this->dbCon = $dbConnection;
    }

    function __destruct(){

    }

    public function login($username, $password){
        global $salt;
        
        $q = "SELECT Id, Username FROM staff WHERE username='$username' AND password=SHA2('$password$salt',256)";

        $resultSet = $this->dbCon->query($q);
        if (mysqli_num_rows($resultSet) > 0){
            $row = $resultSet->fetch_array();
            $this->id = $row["Id"];
            $this->usernamename = $row["Username"];

            return true;
        }
        else{
            return false;
        }
    }
}
?>