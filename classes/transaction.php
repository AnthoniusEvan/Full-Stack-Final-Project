<?php
class Transaction {
    //private data member
    private $dbCon;
    
    // constructor
    public function pdf_order($id){
        require_once('./vendor/autoload.php');

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'default_font_size' => 12,
            'default_font' => 'sans-serif',
            'margin_top' => 6,
            'margin_left' => 6,
            'margin_right' => 6,
        ]);
        $mpdf->simpleTables = true;
        $mpdf->WriteHTML("<HTML><BODY>");

        // Print order header
        $columns = "o.id, DATE_FORMAT(o.order_date, '%d/%m/%Y') AS orderdate, o.customer_id, c.company, o.ship_address, o.ship_city, FORMAT(SUM(od.quantity*od.unit_price),0) AS totalsales, os.status_name, DATE_FORMAT(o.paid_date, '%d/%m/%Y') as paiddate";
        $constraint = "o.id = $id";
        $resultSet = $this->get_header($columns, $constraint, 1);
        if ($resultSet) $row = $resultSet->fetch_array();
        else die('Incorrect Order Id');

        $html="<table width='100%' border='0' cellpadding='0' cellspacing='0'>
        <tr><td style='width: 100%; font-size:18pt; font-weight:bold;'>SALES ORDER</td></tr></table>";
        $html.="<table width='100%' border='0' cellpadding='0' cellspacing='0'>
        <tr>
            <td style='height:30px; width:15%;'>Order Id</td>
            <td style='width:25%;'>: ".$row["id"]."</td>
            <td style='width:10%;'>Customer</td>
            <td style='width:57%;'>: ".$row["company"]."</td>
        </tr>
        <tr>
            <td style='height:30px;'>Order Date</td>
            <td>: ".$row["orderdate"]."</td>
            <td>Address</td>
            <td>: ".$row["ship_address"].", ".$row["ship_city"]."</td>
        </tr></table>
        ";

        $columns = "od.product_id, p.product_name, FORMAT(od.quantity,0) AS qty, FORMAT(od.unit_price, 0) AS price, FORMAT(od.unit_price*od.quantity, 0) AS subtotal";
        $resultSet = $this->get_details($columns, $id);

        $html.="<table width='100%' border='1' cellpadding='3' cellspacing='0'>
        <tr>
            <th style='width:5%;'>#</th>
            <th style='width:35%;'>Product</th>
            <th style='width:20%;'>Qty</th>
            <th style='width:15%;'>Price</th>
            <th style='width:20%;'>Subtotal</th>
        </tr>";
        $i=1;
        while($rowd=mysqli_fetch_array($resultSet)){
            $html.="<tr>
                <td style='font-size:10pt;'>".$i."</td>
                <td style='font-size:10pt;'>".$rowd["product_name"]."</td>
                <td style='font-size:10pt; text-align:right;'>".$rowd["qty"]."</td>
                <td style='font-size:10pt; text-align:right;'>".$rowd["price"]."</td>
                <td style='font-size:10pt; text-align:right;'>".$rowd["subtotal"]."</td>
            ";
            $i++;
        }
        $html.="<tr>
        <td colspan='4' align='right'><strong>Total</strong></td>
        <td align='right' style='white-space:nowrap'><strong>Rp. ".$row["totalsales"]."</strong></td></tr></table>";

        $mpdf->WriteHTML($html);
        $mpdf->WriteHTML("</BODY></HTML>");
        $output_filename = "SO_";
        $mpdf->Output($output_filename.'.pdf', 'I');
    }
    public function __construct($dbConnection) {
        $this->dbCon = $dbConnection;
    }

    public function xls_orders($constraint){
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=SalesOrderList.xls");
        ?>
            <html>
                <body>
                    <table border="1">
                        <tr>
                            <th>Id</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Sales</th>
                            <th>Status</th>
                            <th>Paid Date</th>
                        </tr>
                        <?php
                        $columns = "o.id, DATE_FORMAT(o.order_date, '%d/%m/%Y') AS orderdate, c.company, FORMAT(SUM(od.quantity*od.unit_price),0) AS totalsales, os.status_name, DATE_FORMAT(o.paid_date, '%d/%m/%Y') AS paiddate";
                        $limit=1000;
                        $resultSet = $this->get_header($columns, $constraint, $limit);
                        while($row = $resultSet->fetch_array()){
                            echo("
                            <tr>
                                <td>".$row["id"]."</td>
                                <td>".$row["orderdate"]."</td>
                                <td>".$row["company"]."</td>
                                <td>".$row["totalsales"]."</td>
                                <td>".$row["status_name"]."</td>
                                <td>".$row["paiddate"]."</td>
                            </tr>
                            ");
                        }
                        ?>
                    </table>
                </body>
            </html>
        <?php
    }

    public function get_header($columns="*", $constraints = "1 ", $limit = "10") {
        $q = "SELECT $columns FROM transaction t INNER JOIN transactiondetail td ON t.Id=td.TransactionId INNER JOIN client c ON t.ClientId = c.Id WHERE $constraints GROUP BY t.Id LIMIT ".$limit;

        $resultSet = $this->dbCon->query($q);

        //echo($q);
        return $resultSet;
    }

    public function get_details($columns="*", $transaction_id) {
        $q = "SELECT $columns FROM transactiondetail td INNER JOIN cage c ON td.CageId = c.Id WHERE td.TransactionId=$transaction_id";

        $resultSet = $this->dbCon->query($q);
        return $resultSet;
    }

    public function get_count($constraints = "1 ") {
        $q = "SELECT COUNT(DISTINCT t.Id) as count ";
        $q.= " FROM transaction t ";
        $q.= " WHERE ".$constraints;

        $resultSet = $this->dbCon->query($q);

        return $resultSet;
    }

    public function get_new_transaction_id($branch_id){
        $q = "SELECT IFNULL(MAX(Id)+1,1) AS newId FROM transaction WHERE BranchId = $branch_id;";
        return $this->dbCon->query($q)->fetch_array()["newId"];
    }
    public function insert_header($new_data = array())
    {
        $this->dbCon->query("START TRANSACTION;");

        $columns = implode(',', array_keys($new_data));
        $values = "'".implode("','", $new_data)."'";

        $new_id = $this->get_new_transaction_id($new_data["BranchId"]);
        $q = "INSERT INTO transaction(Id, $columns) VALUES($new_id, $values)";
        //echo($q);
        return $this->dbCon->query($q);
    }

    public function insert_detail($new_data = array())
    {
        $columns = implode(',', array_keys($new_data));
        $values = "'".implode("','", $new_data)."'";

        $q = "INSERT INTO transactiondetail($columns) VALUES($values)";

        //echo($q);
        return $this->dbCon->query($q);
    }

    public function commit_transaction($results = array()){
        $no_errors = true;

        foreach ($results as $result){
            if (!$result) {
                $no_errors = false;
                break;
            }
        }
        
        if ($no_errors) {
            $this->dbCon->query("COMMIT");
            return true;
        }
        else {
            $this->dbCon->query("ROLLBACK");
            return false;
        }
    }

    public function update_data($id, $update_data = array())
    {
        $updates = array();
        foreach($update_data as $column => $value) {
            $updates[] = "$column = '$value'";
        }

        $q = "UPDATE products SET ".implode(',', $updates);
        $q.= " WHERE id = $id";

        return $this->dbCon->query($q);
    }

    public function void_order($id)
    {
        $q = "UPDATE transaction SET Status = 'void'";
        $q.= " WHERE Id = $id";

        echo($q);
        return $this->dbCon->query($q);
    }

    // destructor
    function __destruct() {        
    }
}
?>