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

    if(!empty($_POST["DateFrom"])){
        //convert dd/mm/yyyy to yyyy-mm-dd
        list($orderDate, $orderMonth, $orderYear) = explode("/", $_POST["DateFrom"]);
        $header_data['DateFrom'] = $orderYear."-".$orderMonth."-".$orderDate;
    }

    if(!empty($_POST["DateTo"])){
      //convert dd/mm/yyyy to yyyy-mm-dd
      list($orderDate, $orderMonth, $orderYear) = explode("/", $_POST["DateTo"]);
      $header_data['DateTo'] = $orderYear."-".$orderMonth."-".$orderDate;
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

  if(!empty($_GET["DateFrom"]))
  {
    $constraint.=" AND t.TransactionDateDate >= '".$_GET["DateFrom"]."'";
  }

  if(!empty($_GET["DateTo"]))
  {
    $constraint.=" AND t.TransactionDateDate <= '".$_GET["DateTo"]."'";
  }


  if(!empty($_GET["BranchId"]))
  {
    $constraint.=" AND t.BranchId = '".$_GET["BranchId"]."'";
  }

  if(!empty($_GET["Status"]))
  {
    $status = $_GET["Status"];
    if ($status == "void") $constraint.=" AND t.Status = '$status'";
    else if ($status == "active") $constraint.=" AND t.Status != 'void' OR t.Status IS NULL";
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
        <div class="form-group col-sm-2">
            <label for="pId">Transaction Id</label>
            <input class="form-control" type="text" id="pId" name="pId" value="<?php if(!empty($_GET["pId"])) echo($_GET["pId"]);?>">
        </div>
        <div class="form-group col-sm-5" id="DateFrom">
          <div class="row">
            <div class="form-group col-sm-6">
              <label for="DateFrom">Date From</label>
              <div class="input-group date">
                  <input type="date" class="form-control" value="<?php if(!empty($_GET["DateFrom"])) echo($_GET["DateFrom"]);?>" id="DateFrom" name="DateFrom" >
              </div>
            </div>

            <div class="form-group col-sm-6">
              <label for="DateTo">Date To</label>
              <div class="input-group date">
                  <input type="date" class="form-control" value="<?php if(!empty($_GET["DateTo"])) echo($_GET["DateTo"]);?>" id="DateTo" name="DateTo" >
              </div>
            </div>
          </div>  
        </div>
        <div class="form-group col-sm-3" id="BranchId">
            <label for="BranchId">Branch</label>
            <select class="select2-single form-control" id="BranchId" name="BranchId">
                <option value=''>Search by branch </option>
                <?php
                $q = "SELECT Id, Name FROM branch";

                $resBranches = $dbCon->query($q);
                if($resBranches)
                {
                    while($rBranch = $resBranches->fetch_array())
                    {
                        $selectedStr = "";
                        if (!empty($_GET["BranchId"]) && $rBranch["Id"] == $_GET["BranchId"])
                        {
                            $selectedStr = "selected";
                        }

                        echo("<option value='".$rBranch["Id"]."' ".$selectedStr.">".$rBranch["Name"]."</option>");
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="Status">Status</label>
            <select class="form-control" id="Status" name="Status">
                <option value='any' <?php if(!empty($_GET["Status"]) && $_GET["Status"]=="any") echo("selected");?>>Any</option>
                <option value='active' <?php if(!empty($_GET["Status"]) && $_GET["Status"]=="active") echo("selected");?>>Active</option>
                <option value='void' <?php if(!empty($_GET["Status"]) && $_GET["Status"]=="void") echo("selected");?>>Void</option>
            </select>
        </div>
    </div>
        
    <div class="row">
        <div class="form-group col-sm-4">
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
        
        <div class="form-group col-sm-4">
        <label for="OriginCity">Origin City</label>
            <select class="select2-single form-control" id="OriginCity" name="OriginCity">
                <option value=''>Search by origin city</option>
                <?php
                $q = "SELECT Id, Name FROM city";

                $resStatuses = $dbCon->query($q);
                if($resStatuses)
                {
                    while($rOriCity = $resStatuses->fetch_array())
                    {
                        $selectedStr = "";
                        if ((!empty($_GET["OriginCity"]) || $_GET["OriginCity"]!="") && $rOriCity["Id"] == $_GET["OriginCity"])
                        {
                            $selectedStr = "selected";
                        }

                        echo("<option value='".$rOriCity["Id"]."' ".$selectedStr.">".$rOriCity["Name"]."</option>");
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group col-sm-4">
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
  

    <div class="row mt-2 mb-4">
        <div class="col-sm-4"> 
          <button type="submit" class="btn btn-primary" name="action" value="search" formaction="index.php">Search</button>
          
          <a class="btn btn-outline-primary" href="index.php?page=transactions_list">Clear</a>
        </div>

        <div class="col-sm-4" style="text-align:center"> 
          <button type="submit" class="btn" style="background-color: #187448; color:white;" name="action" value="xls" formaction="report.php?page=transactions_list" formtarget="_blank"><i class="bi bi-file-excel"></i> Excel</button>
        </div>

        <div class="col-sm-4" style='text-align: right;'> 
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
            <th>Branch</th>
            <th>Client</th>
            <th>Origin City</th>
            <th>Destination City</th>
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


            $columns = "t.Id as id, DATE_FORMAT(t.TransactionDateDate, '%d/%m/%Y') AS date, b.Id as branch_id,b.Name as branch_name, c.Name as client_name, t.DestinationAddress as address, oc.Name AS ori_city, dc.Name AS dest_city, FORMAT(SUM(td.Price * td.Quantity), 0) AS totalsales";
            $limit = $pageStart.", ". $maxRows;
            $resultSet = $order->get_header($columns, $constraint, $limit);

            while($row = $resultSet->fetch_array())
            {
              echo("<tr>");
              echo("<td>".$row['id']."</td>");
              echo("<td>".$row['date']."</td>");
              echo("<td>".$row['branch_name']."</td>");
              echo("<td>".$row['client_name']."</td>");
              echo("<td>".$row['ori_city']."</td>");
              echo("<td>".$row['dest_city']."</td>");
              echo("<td style='text-align: right;'>".$row['totalsales']."</td>");
              echo("<td style='text-align: center;'><a href='index.php?page=transaction_detail&mode=update&id=".$row['id']."&branch_id=".$row["branch_id"]."' class='btn-sm btn-primary'><i class='bi bi-pencil-square'></i></a></td>");
              echo("<td style='text-align: center;'><a href='report.php?page=transactions_list&mode=pdf&id=".$row['id']."' class='btn-sm btn-primary'><i class='bi bi-printer'></i></a></td>");
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
    $('.select2-single').select2();    
  });
</script>