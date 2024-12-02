<?php
  defined( 'siteToken' ) or die( 'Restricted access' ); 

  //corresponding class should exist, otherwise exit
	if (!file_exists('./classes/transaction.php')) 
	{
		die( 'Class Transaction Not Found' ); 
	}
  require_once('./classes/transaction.php');
  $order = new Transaction($dbCon);

$errorMessage="";
  if(!empty($_POST["mode"]))
  {
    $header_data = array();

    if(!empty($_POST["TransactionDate"])){
        // list($header_data['TransactionDateDate'],  $validation_status) = sanitize($dbCon, $_POST["TransactionDate"], "string");
        
        //convert dd/mm/yyyy to yyyy-mm-dd
        list($orderDate, $orderMonth, $orderYear) = explode("/", $_POST["TransactionDate"]);
        $header_data['TransactionDateDate'] = $orderYear."-".$orderMonth."-".$orderDate;
    }


    if(!empty($_POST["ClientId"])){
      list($header_data['ClientId'], $validation_status) = sanitize($dbCon, $_POST["ClientId"], "int");
      if(!$validation_status) {
        $errorMessage = "ERROR - Invalid Client Id";
      }
    }

    if(!empty($_POST["DestinationCity"]) )
    {
      list($header_data['DestinationCity'], $validation_status) = sanitize($dbCon, $_POST["DestinationCity"], "int");
      if(!$validation_status) {
        $errorMessage = "ERROR - Invalid Destination City Id";
      }
    }

    if(!empty($_POST["OriginCity"]) )
    {
      list($header_data['OriginCity'], $validation_status) = sanitize($dbCon, $_POST["OriginCity"], "int");
      if(!$validation_status) {
        $errorMessage = "ERROR - Invalid Origin City Id";
      }
    }

    if(!empty($_POST["DestinationAddress"]) )
    {
      list($header_data['DestinationAddress'], $validation_status) = sanitize($dbCon, $_POST["DestinationAddress"], "string");
    }

    if(!empty($_POST["ExpectedArrival"])){
      list($header_data['ExpectedArrival'],  $validation_status) = sanitize($dbCon, $_POST["ExpectedArrival"], "string");
    }
    $header_data['CreatedBy'] = $_SESSION['active_user']->id;
    $header_data['BranchId'] = $_SESSION['active_user']->branch_id;

    // //set order status to 0 (New)
    // $header_data['status_id'] = 0;

    if($errorMessage=="")
    {
      switch ($_POST["mode"]) 
      {
        case "pdf":
          $dId = "";
          if (!empty($_GET["id"])){
            list($dId, $validation_status) = sanitize($dbCon, $_GET["id"], "int");
            if (!$validation_status){
              $errorMessage = "ERROR - Invalid Order ID";
            }
          }

          $order->pdf_order($dId);
          break;

        case "insert":
            //insert order header

          $results = array();
        
          if ($order->insert_header($header_data) === TRUE){
            array_push($results, true);
            //Get the new inserted order id
            $newOrderId = $order->get_new_transaction_id($header_data['BranchId']) - 1;

            $productId = $_POST['productId'];
            $qty = $_POST['qty'];
            $price = $_POST['price'];
            $del = $_POST['del'];

            foreach($productId as $key => $product_id)
            {
                if($del[$key] != 1) {
                    $detail_data['TransactionId'] = $newOrderId;
                    $detail_data['BranchId'] = $_SESSION['active_user']->branch_id;
                    $detail_data['CageId'] = $product_id;
                    $detail_data['Quantity'] = $qty[$key];
                    $detail_data['Price'] = $price[$key];
                    if ($order->insert_detail($detail_data) === FALSE){
                      array_push($results, false);
                    }
                    else array_push($results, true);
                }
            }
          }
          else{
            array_push($results, false);
          }
          //print_r($results);
  
          if(!$order->commit_transaction($results)){
            $errorMessage = "Failed to insert transaction data!";
          }
          break;
  
        case "update":
            $dId = "";
            if(!empty($_POST["pId"])){
                list($dId, $validation_status) = sanitize($dbCon, $_POST["pId"], "int");
                if(!$validation_status) {
                $errorMessage = "ERROR - Invalid Order Id";
                }
            }
            //an order can not be updated, can only be voided
            if(!empty($_POST['void']))
            {
                $_POST["mode"] = "void";
                
                //remove the product from database
                if ($errorMessage=="" && $order->void_order($dId) === TRUE){
                }
                else{
                  $errorMessage==" Failed to VOID order.";
                }
            }
  
          break;
          
        default:
          die( 'Incorrect Access' );
      }
    }
  
    if ($errorMessage == ""){
      ?>
      <div class="alert alert-success alert-dismissible show fade" role="alert">
          <?php echo(" Successfully ".$_POST["mode"]." transaction data!"); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"
              aria-label="Close"></button>
      </div>
      <?php
  } else
  {
    $errorMessage="";
      ?>
      <div class="alert alert-danger alert-dismissible show fade" role="alert">
          <?php echo(" Fail to ".$_POST["mode"]." transaction data! ".$errorMessage); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"
              aria-label="Close"></button>
      </div>
      <?php
  }
  }

  
  if (!empty($_GET["mode"])){
    if($errorMessage=="")
    {
      switch ($_GET["mode"]) 
      {
        case "pdf":
          $dId = "";
          if (!empty($_GET["id"])){
            list($dId, $validation_status) = sanitize($dbCon, $_GET["id"], "int");
            if (!$validation_status){
              $errorMessage = "ERROR - Invalid Transaction ID";
            }
          }

          $order->pdf_order($dId);
        break;

        default: die('Incorrect Access');
      }
    }
  }

  $constraint = "1";

  if(!empty($_GET["pId"]))
  {
    $pId = $_GET["pId"];
    $constraint.=" AND t.Id = '$pId'";
  }

  if(!empty($_GET["TransactionDate"]))
  {
    $constraint.=" AND t.TransactionDateDate = '".$_GET["TransactionDate"]."'";
  }

  if(!empty($_GET["ClientId"]))
  {
    $ClientId = $_GET["ClientId"];
    $constraint.=" AND t.ClientId = '$ClientId'";
  }

  if(!empty($_GET["DestinationCity"]) )
  {
    $DestinationCity = $_GET["DestinationCity"];
    $constraint.=" AND t.DestinationCity = '$DestinationCity'";
  }

  if(!empty($_GET["OriginCity"]) )
  {
    $OriginCity = $_GET["OriginCity"];
    $constraint.=" AND t.OriginCity = '$OriginCity'";
  }


  
  if (!empty($_GET["action"])){
    if($_GET["action"]=="xls"){
      $order->xls_orders($constraint);
      exit();
    }
  }

  if ($errorMessage!="") {
    ?>
    <div class="alert alert-danger alert-dismissible show fade" role="alert">
        <?php echo($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"
            aria-label="Close"></button>
    </div>
    <?php
  }

  
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Sales Orders</h1>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="./">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transactions</li>
  </ol>
</div>


<!--Search Criteria-->
<div class="row" style="padding-bottom: 20px;">
  <div class="col-lg-12">
  <form method="GET" action="index.php">
    <input type="hidden" id="page" name="page" value="transactions_list">

    <div class="row">
        <div class="form-group col-sm-7">
            <label for="pId">Transaction Id</label>
            <input class="form-control" type="text" placeholder="Transaction Id" id="pId" name="pId" value="<?php if(!empty($_GET["pId"])) echo($_GET["pId"]);?>">
        </div>
        <div class="form-group col-sm-5" id="TransactionDate">
            <label for="TransactionDate">Transaction Date</label>
            <div class="input-group date">
                <input type="date" class="form-control" value="<?php if(!empty($_GET["TransactionDate"])) echo($_GET["TransactionDate"]);?>" id="TransactionDate" name="TransactionDate" >
            </div>
            
        </div>
    </div>
        
    <div class="row">
        <div class="form-group col-sm-7">
            <label for="ClientId">Client</label>
            <select class="select2-single form-control" id="ClientId" name="ClientId">
                <option value=''>Search by client </option>
                <?php
                $q = "SELECT Id, Name FROM client";

                $resCustomers = $dbCon->query($q);
                if($resCustomers)
                {
                    while($rCustomer = $resCustomers->fetch_array())
                    {
                        $selectedStr = "";
                        if (!empty($_GET["ClientId"]) && $rCustomer["Id"] == $_GET["ClientId"])
                        {
                            $selectedStr = "selected";
                        }

                        echo("<option value='".$rCustomer["Id"]."' ".$selectedStr.">".$rCustomer["Name"]."</option>");
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="form-group col-sm-5">
        <label for="DestinationCity">Destination City</label>
            <select class="select2-single form-control" id="DestinationCity" name="DestinationCity">
                <option value=''>Search by destination city</option>
                <?php
                $q = "SELECT Id, Name FROM city";

                $resStatuses = $dbCon->query($q);
                if($resStatuses)
                {
                    while($rDesCity = $resStatuses->fetch_array())
                    {
                        $selectedStr = "";
                        if ((!empty($_GET["DestinationCity"]) || $_GET["DestinationCity"]!="") && $rDesCity["Id"] == $_GET["DestinationCity"])
                        {
                            $selectedStr = "selected";
                        }

                        echo("<option value='".$rDesCity["Id"]."' ".$selectedStr.">".$rDesCity["Name"]."</option>");
                    }
                }
                ?>
            </select>
        </div>
    </div>
  

    <div class="row">
        <div class="col-sm-2"> 
          <button type="submit" class="btn btn-primary" name="action" value="search" formaction="index.php">Search</button>
        </div>

        <div class="col-sm-4"> 
          <button type="submit" class="btn btn-primary" name="action" value="xls" formaction="index2.php" formtarget="_blank"><i class="fas fa-file-excel"></i> Excel</button>
        </div>

        <div class="col-sm-6" style='text-align: right;'> 
          <a href="index.php?page=transaction_detail&mode=insert"><button type="button" class="btn btn-success">Add New</button></a>
        </div>
    </div>
   
  </form>
  </div>
</div>
<!--Search Criteria-->

<!--Data table-->
<div class="row">
  <div class="col-lg-12">
    <div class="table-responsive">
      <table class="table align-items-center table-flush">
        <thead class="thead-light">
          <tr>
            <th>Id</th>
            <th>Date</th>
            <th>Client</th>
            <th>Destination Address</th>
            <th>Expected Arrival</th>
            <th style='text-align: right;'>Total Sales</th>
            <th style='text-align: center;'><i class="bi bi-pencil-square"></i></th>
            <th style='text-align: center;'><i class="bi bi-printer"></i></th>
          </tr>
        </thead>
        <tbody>
        <?php
          $pagingError = "";
          $pageNum = (isset($_GET['pageNum'])) ? $_GET['pageNum'] : 1;
          list($pageNum, $validation_status) = sanitize($dbCon, $pageNum, "int");
          if(!$validation_status) {
            $pagingError = "ERROR - Invalid page num";
          }
          else {
            $pageStart = ($pageNum - 1) * $maxRows;


            $columns = "t.Id as id, DATE_FORMAT(t.TransactionDateDate, '%d/%m/%Y') AS date, c.Name as client_name, t.DestinationAddress as address, DATE_FORMAT(t.ExpectedArrival, '%d/%m/%Y') as eta, FORMAT(SUM(td.Price * td.Quantity), 0) AS totalsales";
            $limit = $pageStart.", ". $maxRows;
            $resultSet = $order->get_header($columns, $constraint, $limit);

            while($row = $resultSet->fetch_array())
            {
              echo("<tr>");
              echo("<td>".$row['id']."</td>");
              echo("<td>".$row['date']."</td>");
              echo("<td>".$row['client_name']."</td>");
              echo("<td>".$row['address']."</td>");
              echo("<td>".$row['eta']."</td>");
              echo("<td style='text-align: right;'>".$row['totalsales']."</td>");
              echo("<td style='text-align: center;'><a href='index.php?page=transaction_detail&mode=update&id=".$row['id']."' class='btn-sm btn-primary'><i class='bi bi-pencil-square'></i></a></td>");
              echo("<td style='text-align: center;'><a href='index2.php?page=transactions_list&mode=pdf&id=".$row['id']."' class='btn-sm btn-primary'><i class='bi bi-printer'></i></a></td>");
              echo("</tr>");
            }
          }
          
        ?> 
        </tbody>
      </table>
    </div>
  </div>
</div>
<!--Data table-->

<!-- Paging Start-->
<?php
  if($pagingError == "") {
    $resCount = $order->get_count($constraint);
    
    $rCount = $resCount->fetch_array();
    $totalRows = $rCount["count"];
    $totalPages = ceil($totalRows / $maxRows);
?>

  <div class="row">
    <div class="col-sm-4">
      <?php
         $pageEnd = min($totalRows,$pageStart + $maxRows);
         echo("Showing ".min($pageStart + 1, $pageEnd)." to ".$pageEnd." of ".$totalRows." records");
      ?>
    </div>
    <div class="col-sm-8" >
      <nav>
        <ul class="pagination justify-content-end" >
            <li class="page-item <?php if($pageNum <= 1){ echo 'disabled'; } ?>">
                <a class="page-link"
                    href="<?php if($pageNum <= 1){ echo '#'; } else { echo "?page=transactions_list&pageNum=" . $pageNum - 1; } ?>">Previous</a>
            </li>

            <?php for($i = 1; $i <= $totalPages; $i++ ): ?>
            <li class="page-item <?php if($pageNum == $i) {echo 'active'; } ?>">
                <a class="page-link" href="index.php?page=transactions_list&pageNum=<?php echo($i); ?>"> <?php echo($i); ?> </a>
            </li>
            <?php endfor; ?>

            <li class="page-item <?php if($pageNum >= $totalPages) { echo 'disabled'; } ?>">
                <a class="page-link"
                    href="<?php if($pageNum >= $totalPages){ echo '#'; } else {echo "?page=transactions_list&pageNum=". $pageNum + 1; } ?>">Next</a>
            </li>
        </ul>
      </nav>
    </div>
  </div>
<?php
  }
?>
<!-- Paging Start-->

<!-- Javascript for this page -->
<script>
    $(document).ready(function () {
      $('#TransactionDate .input-group.date').datepicker({
        format: 'dd/mm/yyyy',
        todayBtn: 'linked',
        todayHighlight: true,
        autoclose: true,        
      });
    });
</script>

<script>
  $(document).ready(function () {
    $('.select2-single').select2();    
  });
</script>