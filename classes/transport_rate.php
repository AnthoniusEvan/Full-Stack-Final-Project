<?php
if (file_exists('./config.php')) {
    require_once('./config.php');
}

if (file_exists('../config.php')) {
    require_once('../config.php');
}

class TransportRate {
    private $dbCon;

    public function __construct($dbConnection) {
        $this->dbCon = $dbConnection;
    }

    public function xls($constraint){
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=PriceList.xls");
        ?>
            <html>
                <body>
                    <table border="1">
                        <tr>
                            <th>City Origin</th>
                            <th>City Destination</th>
                            <th>Cage</th>
                            <th>Rate</th>
                        </tr>
                        <?php
                        $columns = "Origin.Name AS CityOrigin, Destination.Name AS CityDestination, c.Name AS Cage, t.Rate";
                        $limit=1000;
                        $resultSet = $this->get_inner_join_data($columns, $constraint, $limit);
                        while($row = $resultSet->fetch_array()){
                            echo("
                            <tr>
                                <td>".$row["CityOrigin"]."</td>
                                <td>".$row["CityDestination"]."</td>
                                <td>".$row["Cage"]."</td>
                                <td>".$row["Rate"]."</td>
                            </tr>
                            ");
                        }
                        ?>
                    </table>
                </body>
            </html>
        <?php
    }

    public function get_data($columns = "*", $constraints = "1", $limit = 10) {
        $query = "
            SELECT $columns 
            FROM transportrate t
            INNER JOIN city origin ON t.CityOrigin = origin.Id
            INNER JOIN city destination ON t.CityDestination = destination.Id
            INNER JOIN cage c ON t.CageId = c.Id
            WHERE $constraints
            LIMIT $limit
        ";
        //echo $query;
        return $this->dbCon->query($query);
    }

    public function insert_data($data = array()) {
        foreach ($data as $key => $value) {
            $data[$key] = "'" . $this->dbCon->real_escape_string($value) . "'";
        }
    
        $columns = implode(",", array_keys($data));
        $values = implode(",", $data);
    
        $query = "
            INSERT INTO transportrate($columns, LastUpdateTime) 
            VALUES($values, CURRENT_TIMESTAMP())
        ";
        return $this->dbCon->query($query);
    }

    public function update_data($update_data = array(), $cityOrigin, $cityDestination, $cageId) {
        $updates = array();
        foreach ($update_data as $column => $value) {
            $updates[] = "$column = '".$this->dbCon->real_escape_string($value)."'";
        }

        $query = "
            UPDATE transportrate 
            SET " . implode(',', $updates) . ", LastUpdateTime = CURRENT_TIMESTAMP() 
            WHERE CityOrigin = '$cityOrigin' AND CityDestination = '$cityDestination' AND CageId = '$cageId'
        ";
        //echo $query;
        return $this->dbCon->query($query);
    }

    public function remove_data( $cityOrigin, $cityDestination, $cageId) {
        $query = "DELETE FROM transportrate WHERE CityOrigin = '$cityOrigin' AND CityDestination = '$cityDestination' AND CageId = '$cageId'";
        return $this->dbCon->query($query);
    }
    public function get_inner_join_data($column="*", $constraints="1",$limit=10){
        $q = "SELECT $column FROM transportrate t
            INNER JOIN city origin ON t.CityOrigin = origin.Id
            INNER JOIN city destination ON t.CityDestination = destination.Id
            INNER JOIN cage c ON t.CageId = c.Id
            WHERE $constraints
            LIMIT $limit";

        $resultSet = $this->dbCon->query($q);
        return $resultSet;
    }
    public function check_rate_exists($cityOrigin, $cityDestination, $cageId) {
        $query = "SELECT COUNT(*) as count 
                  FROM transportrate 
                  WHERE CityOrigin = '$cityOrigin' 
                  AND CityDestination = '$cityDestination' 
                  AND CageId = '$cageId'";
        $result = $this->dbCon->query($query);
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
}
?>
