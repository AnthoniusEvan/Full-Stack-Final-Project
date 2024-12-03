<?php
  defined( 'siteToken' ) or die( 'Restricted access' ); 
  //corresponding class should exist, otherwise exit
	if (!file_exists('./classes/transaction.php')) 
	{
		die( 'Class Transaction Not Found' ); 
	}
  require_once('./classes/transaction.php');
  $order = new Transaction($dbCon);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <?php
    if(!empty($_GET["mode"]))
    {
      $mode=$_GET["mode"];
    }
    else
    {
      die( 'Incorrect Access' );
    }
  ?>
  <h1 class="h3 mb-0 text-gray-800">Transactions - 
    <?php
        $id = '-';
        switch ($mode) {
        case "insert":
            echo("Add New");
            break;
        
        case "update":
            echo("Update");

            $id = $_GET["id"];
            $branch_id = $_GET["branch_id"];

            $columns = "t.Id as id, DATE_FORMAT(t.TransactionDateDate, '%d/%m/%Y') AS date, c.Id as client_id, t.OriginCity as ori_city, t.DestinationCity as dest_city, t.DestinationAddress as address, t.ExpectedArrival as eta, FORMAT(SUM(td.Price*td.Quantity), 0) AS totalsales, t.Status as status";
            $constraint = " t.Id = $id AND t.BranchId = $branch_id";
            $resultSet = $order->get_header($columns, $constraint, 1);
            
            if($resultSet)
            {
                $row = $resultSet->fetch_array();
                if ($row["status"]=="void"){
                    echo("<br><br><div><div class='alert alert-danger'>This transaction is void</div>");
                    die();
                }
            }
            else
            {
            die( 'Incorrect Transaction Id' );
            }

            break;
        
        default:
            die( 'Incorrect Mode of Access' );
        }
    ?>  

  </h1>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="./">Home</a></li>
    <li class="breadcrumb-item"><a href="index.php?page=transactions_list">Transactions</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo(ucfirst($mode)); ?> Transaction</li>
  </ol>
</div>


<div class="row" style="padding-bottom: 20px;">
  <div class="col-lg-12">
  <form enctype="multipart/form-data" method="POST" action="index.php?page=transactions_list">
    <input type="hidden" id="mode" name="mode" value=<?php echo($mode); ?>>
    <?php

      if($mode=="update")
      {
        echo("<input type='hidden' id='pId' name='pId' value='".$id."'>");
      }
    ?>

    <div class="row">
        <div class="form-group col-sm-2">
            <label for="pId">Transaction Id</label>
            <input class="form-control mb-3" type="text" placeholder="Product Code" id="pId" name="pId" readonly value="<?php if ($mode=="insert") echo($order->get_new_transaction_id($_SESSION['active_user']->branch_id)); else echo($id);?>">
        </div>
        <div class="form-group col-sm-5" >
            <label for="TransactionDate">Transaction Date</label>
            <?php
                if($mode=="insert")
                {
                    $today = getdate();
                    $orderDate = str_pad($today["mday"], 2, "0", STR_PAD_LEFT)."/".str_pad($today["mon"], 2, "0", STR_PAD_LEFT)."/".$today["year"];

                }
                else {
                    $orderDate = $row["date"];
                }
            ?>
            <input class="form-control mb-3" type="text" id="TransactionDate" name="TransactionDate" readonly value="<?php echo($orderDate);?>">
        </div>
        <div class="form-group col-sm-5" >
            <label for="ExpectedArrival">Expected Arrival</label>
            <input class="form-control mb-3" type="date" id="ExpectedArrival" name="ExpectedArrival" <?php if ($mode=="update") echo("value = '".$row["eta"]."' disabled");?>>
        </div>
    </div>

    <div class="row mb-2">
        <div class="form-group col-sm-6">
            <label for="OriginCity">City Origin</label>
            <select class="select2-single form-control" id="OriginCity" name="OriginCity" <?php echo ($mode=="update"?"disabled":"");?>>
                <option value=''>Search city origin</option>
                <?php
                $q = "SELECT Id, Name FROM city";

                $resStatuses = $dbCon->query($q);
                if($resStatuses)
                {
                    while($rOriCity = $resStatuses->fetch_array())
                    {
                        $selectedStr = "";
                        if ($rOriCity["Id"] == $row["ori_city"])
                        {
                            $selectedStr = "selected";
                        }

                        echo("<option value='".$rOriCity["Id"]."' $selectedStr>".$rOriCity["Name"]."</option>");
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group col-sm-6" >
            <label for="DestinationCity">City Destination</label>
            <select class="select2-single form-control" id="DestinationCity" name="DestinationCity" <?php echo ($mode=="update"?"disabled":"");?>>
                <option value=''>Search city destination</option>
                <?php
                $q = "SELECT Id, Name FROM city";

                $resStatuses = $dbCon->query($q);
                if($resStatuses)
                {
                    while($rDesCity = $resStatuses->fetch_array())
                    {
                        $selectedStr = "";
                        if ($rDesCity["Id"] == $row["dest_city"])
                        {
                            $selectedStr = "selected";
                        }
                        echo("<option value='".$rDesCity["Id"]."' $selectedStr>".$rDesCity["Name"]."</option>");
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-sm-6">
            <label for="ClientId">Client</label>
            <select class="select2-single form-control" id="ClientId" name="ClientId" <?php echo ($mode=="update"?"disabled":"");?>>
            <option value=''>Search by client </option>
                <?php
                $q = "SELECT Id, Name FROM client";

                $resCustomers = $dbCon->query($q);
                if($resCustomers)
                {
                    while($rCustomer = $resCustomers->fetch_array())
                    {
                        $selectedStr = "";
                        if ($rCustomer["Id"] == $row["client_id"])
                        {
                            $selectedStr = "selected";
                        }

                        echo("<option value='".$rCustomer["Id"]."' $selectedStr>".$rCustomer["Name"]."</option>");
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group col-sm-6">
            <label for="DestinationAddress">Destination Address</label>
            <input class="form-control" type="text" placeholder="Destination address" id="DestinationAddress" name="DestinationAddress" <?php if($mode=="update") echo "value = '".$row["address"]."' disabled" ?>>
        </div>
    </div>

    <?php if ($mode!="update") { ?>
    <div class="form-group row">
        <div class="col-sm-12" style="text-align: left;">

            <a id="btnConfirm" href='#' style="width: 25%;" class='btn btn-primary mb-2' onclick='javascript:saveOrderHeader();return false;'>Confirm</a>
        </div>
    </div>
    <?php } ?>

    <div class="row">
        <div class="col-md-5" <?php echo ($_GET["mode"]=="edit" && (isset($dataNota) && $dataNota["StatusNota"]!=10)?"style='display:none'":"") ?>>
            <?php if($mode=="insert") { ?>
            <div class="card">
                <div class="card-header">
                    <h5>Add Cage</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label for="CageId" class="col-sm-3 col-form-label" style="text-align: left;">Cage</label>
                                <div class="col-sm-9">
                                    <select class="select2-single form-control" name="CageId" id="CageId" onchange="getCagePrice()" disabled>
                                        <option value="">Select a cage</option>
                                        <?php
                                            $q = "SELECT Id, Name FROM cage";

                                            $resProducts = $dbCon->query($q);
                                            if($resProducts)
                                            {
                                                while($rProduct = $resProducts->fetch_array())
                                                {
                                                    echo("<option value='".$rProduct["Id"]."' ".$selectedStr.">".$rProduct["Name"]."</option>");
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group row" style="vertical-align=middle;">
                                <label for="addQty" class="col-sm-3 col-form-label" style="text-align: left;">Qty</label>
                                <div class="col-sm-4">
                                    <input type="number" class="form-control" min="1" name="addQty" id="addQty" value="1" style="text-align:right;" oninput="updateSubTotal()" onkeyup="if(value<1) value=1;" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6"></div>
                                
                                
                            
                            <div class="form-group row">
                                <label for="addPrice" class="col-sm-3 col-form-label" style="text-align: left;">Price</label>
                                <div class="col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">IDR</span>
                                        <input type="text" class="form-control" name="addPrice" id="addPrice" value="0" style="text-align:right;" readonly aria-describedby="basic-addon1">
                                    </div>
                                </div>

                               
                            </div>

                            <div class="form-group row">
                                <label for="addSubTotal" class="col-sm-3 col-form-label" style="text-align: left;">Sub Total</label>
                                <div class="col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">IDR</span>
                                        <input type="text" class="form-control" name="addSubTotal" id="addSubTotal" value="0" style="text-align:right;" readonly aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-6" style="text-align: left;">

                                    <a href='#' class='btn btn-primary btn-sm' onclick='javascript:addOrderDetail();return false;'>Add Cage</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        
        <div class="col-md-<?php if ($mode=="update") echo"12"; else echo "7" ?>">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="tabelDetil">
                            <thead>
                            <tr>
                                <?php 
                                if($mode != "update")
                                    echo'<th style="width:35%; text-align:left;">Product</th>';
                                else
                                    echo'<th style="width:40%; text-align:left;">Product</th>';
                                ?>

                                <th style="width:15%; text-align:right;">Qty</th>
                                <th style="width:20%; text-align:right;">Price</th>
                                <th style="width:25%; text-align:right;">Sub Total</th>
                                <?php 
                                if($mode != "update")
                                    echo'<th style="width:5%;"><i class="bi bi-trash-fill" ></i></th>'
                                ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                            if($mode == "update")
                            {
                                $columns = "td.CageId as id, c.Name as name, FORMAT(td.Quantity, 0) AS qty, FORMAT(td.Price, 0) AS price, FORMAT(td.Price * td.Quantity, 0) AS subtotal ";
                                $resultSet = $order->get_details($columns, $id, $branch_id);
                                $i = 0;
                                while ($rowd = $resultSet->fetch_array()) {
                                    echo("<tr id='detail_".$i."'>\n");
                                    echo '<td style="white-space:normal">'.$rowd["name"];
                                    echo "<input type='hidden' name='productId[]' id='productId_".$i."' value='".$rowd["id"]."'></n>";
                                    echo '<td style="text-align:right">'.$rowd["qty"].'<input type="hidden" class="form-control" name="qty[]" id="qty_'.$i.'" value="'.$rowd["qty"].'" style="text-align:right"></td>';
                                    echo '<td style="text-align:right">'.$rowd["price"].'<input type="hidden" class="form-control" name="price[]" id="price_'.$i.'" value="'.$rowd["price"].'" style="text-align:right"></td>';
                                    
                                    echo '<td style="text-align:right">'.$rowd["subtotal"].'<input type="hidden" class="form-control" name="subTotal[]" id="subTotal_'.$i.'" value="'.$rowd["subtotal"].'" readonly style="text-align:right"></td>';
                                    
                                    //echo("<input type='hidden' name='del[]' id='del_".$i."' value=0></td>\n");
                                    echo("</tr>\n");
                                    $i++;
                                }
                            }
                            
                            ?>
                            <tr id="total">
                                <?php if($mode=="update") $c = 0; else $c = 1;?>
                                <td colspan="<?php echo 3-$c ?>" align="left"><strong>TOTAL</strong></td>
                                <td colspan="<?php echo 2+$c ?>">
                                    <input type='hidden' name='newProductsCount' id='newProductsCount' value=''>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">IDR</span>
                                        <input type="text" class="form-control" name="txtTotalNominal" id="txtTotalNominal" value="<?php echo ($mode=="update"?$row['totalsales']:'0');?>" readonly style="text-align:right" aria-describedby="basic-addon1">
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br/>

    <div class="row">
        <div class="col-sm-2"><a href="index.php?page=transactions_list">
          <button type="button" class="btn btn-warning">Cancel</button></a>
        </div>

        <div class="col-sm-4">
          <?php
            if($mode=="update")
            { ?>
              <button type="submit" name="void" value="Void" class="btn btn-danger" onclick="return confirm('Void Transaction Id <?php echo($row['id']);?> ?');">Void</button>
          <?php 
            } ?>
        </div>

        <div class="col-sm-6" style='text-align: right;'>
        <?php
            if($mode=="insert")
            { ?>
          <button type="submit" name="save" value="Save" class="btn btn-primary" onclick="sendDisabledFields(); return true;">Save</button>
          <?php 
            } ?>
        </div>
    </div>
    
  </form>
  </div>
</div>

<!-- Javascript for this page -->
<script>
  $(document).ready(function () {
    $('.select2-single').select2();    
  });
</script>

<script>

  function sendDisabledFields(){
    document.getElementById("CageId").disabled = false;
    document.getElementById("OriginCity").disabled = false;
    document.getElementById("DestinationCity").disabled = false;
    document.getElementById("ClientId").disabled = false;
  }
  function getCagePrice() {
      var xhttp;
      xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
              var jsonResults = JSON.parse(this.responseText);
              if(jsonResults.status == "OK") {
                  document.getElementById("addPrice").value = jsonResults.price;
                  document.getElementById("addQty").value = 1;
                  document.getElementById("addSubTotal").value = jsonResults.price;
              }
              else {
                  alert(jsonResults.status);
                  document.getElementById("addPrice").value = '0';
                  document.getElementById("addSubTotal").value = '0';
                  const selectElement = document.getElementById("CageId");
                  selectElement.value = '';
                  selectElement.dispatchEvent(new Event('change'));
              }
          }
      };

      xhttp.open("POST", "./functions/getCagePrice.php", true);

      var params = new FormData();
      params.append("cage_id", document.getElementById("CageId").value);
      params.append("origin_city", document.getElementById("OriginCity").value);
      params.append("dest_city", document.getElementById("DestinationCity").value);

      xhttp.send(params);
  }

  function saveOrderHeader(){
    var status = document.getElementById("CageId").disabled;
    
    if (status){
        if (document.getElementById("ExpectedArrival").value &&
    document.getElementById("OriginCity").value && 
    document.getElementById("DestinationCity").value && 
    document.getElementById("ClientId").value && 
    document.getElementById("DestinationAddress").value) document.getElementById("btnConfirm").innerHTML = "Reset";
        else{
            alert("Please fill in all the required fields!");
            return;
        } 
    }
    else{
        var n = parseInt($('#newProductsCount').val())||0;
        if (n>0){
            if(!confirm("Changing the transaction header will remove all the added products. Would you like to continue?")) return;
        } 

        for (var i = 0; i < n; i++) {
            deleteOrderDetail(i);
        }
        document.getElementById("btnConfirm").innerHTML = "Confirm";
        document.getElementById("addPrice").value = '0';
        document.getElementById("addSubTotal").value = '0';
        document.getElementById("addQty").value = '1';
        const selectElement = document.getElementById("CageId");
        selectElement.value = '';
        selectElement.dispatchEvent(new Event('change'));
    }
    document.getElementById("CageId").disabled = !status;
    document.getElementById("addQty").readOnly = !status;

    document.getElementById("ExpectedArrival").readOnly = status;
    document.getElementById("OriginCity").disabled = status;
    document.getElementById("DestinationCity").disabled = status;
    document.getElementById("ClientId").disabled = status;
    document.getElementById("DestinationAddress").readOnly = status;

  }

  function updateSubTotal() {
      if(!isNaN(document.getElementById("addQty").value)){
        var price = document.getElementById("addPrice").value.replaceAll(',','');
        document.getElementById("addSubTotal").value = (document.getElementById("addQty").value * parseInt(price)).toLocaleString();
      }
      else {
        document.getElementById("addSubTotal").value = 0;
      }
  }

    function addOrderDetail() 
    {
        var productId = $("#CageId").val();
        var sel = document.getElementById("CageId");
        var productName = sel.options[sel.selectedIndex].text;
        var qty = $("#addQty").val();
        var price = $("#addPrice").val();
        var subTotal = $("#addSubTotal").val();

        if(productId!='' && qty>0){
            var n = parseInt($('#newProductsCount').val())||0;
            $('#newProductsCount').val(n+1);
            
            var newRow = "<tr id='detail_"+n+"'>";
            newRow += "<td>"+productName+"<input type='hidden' name='productId[]' id='productId_"+n+"' value='"+productId+"'></td>";
            newRow += "<td style='text-align:right'>"+qty+"<input type='hidden' name='qty[]' id='qty_"+n+"' value='"+qty+"'></td>";
            newRow += "<td style='text-align:right'>"+price+"<input type='hidden' name='price[]' id='price_"+n+"' value='"+price.replaceAll(',','')+"'></td>";
            newRow += "<td style='text-align:right'>"+subTotal+"<input type='hidden' name='subTotal[]' id='subTotal_"+n+"' value='"+subTotal.replaceAll(',','')+"'></td>";

            newRow += "<td align='center'><a href='javascript:;' onclick='deleteOrderDetail("+n+")'><i class='bi bi-trash'></i></a><input type='hidden' name='del[]' id='del_"+n+"' value=0></td>";
            newRow += "</tr>";

            //add the newRow before row "total"
            $("#total").before(newRow);

            //update the order total
            countTotal();

        }else{
            if(productId==''){
                alert("Please specify cage to add!");
            }else if(qty==''){
                alert("Please specify quantity to add!");
            }
        }    
    }

    function countTotal(){
        var total = 0;
        var n = parseInt($('#newProductsCount').val())||0;
        for (var i = 0; i < n; i++) {
            if($("#del_"+i).val()=='0'){
                var subtotal = $("#subTotal_"+i).val().replaceAll(',','');

                total += parseInt(subtotal)||0;
            }
        }
        $("#txtTotalNominal").val(total.toLocaleString());
    }

    function deleteOrderDetail(n){
        $("#detail_"+n).hide();
        $("#del_"+n).val(1);
        countTotal();
    }

</script>