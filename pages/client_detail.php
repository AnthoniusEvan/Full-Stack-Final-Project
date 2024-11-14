<?php
defined('siteToken') or die('Restricted Access');

if (!empty($_GET["mode"])){
    $mode=$_GET["mode"];
}
else{
    die('Incorrect Access');
}
if (!file_exists('./classes/client.php')){
    die("Class client Not Found");
}
require_once('./classes/client.php');

if (!file_exists('./classes/staff.php')){
    die("Class staff Not Found");
}
require_once('./classes/staff.php');

$staff = new Staff($dbCon);
$client = new Client($dbCon);
?>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 card-header">
        <h1 class="h3 mb-0 text-gray-800">Client - 
        <?php
            $modeText = "";
            switch($mode){
                case "insert":
                    $modeText = "Add New";
                    echo($modeText);
                    break;
                case "update":
                    $modeText = "Update";
                    
                    $id=$_GET["id"];

                    $col= "Id, Name, Address, PhoneNumber, CityId, LastModifier, LastUpdateTime";

                    $resultSet = $client->get_data($col, "Id=$id", 1);
                    if ($resultSet){
                        $row=$resultSet->fetch_array();
                    }
                    else{
                        die('Incorrect client ID');
                    }

                    echo($modeText);
                    break;
                default:
                    die('Incorrect Access');
            }
            
        ?></h1>

        <?php
            if ($mode == "update" && $row["LastModifier"]!=""){
                $resultSet = $staff->get_inner_join_data("s.Name as name", "s.Id=".$row['LastModifier'], 1);
                if ($resultSet){
                    $modifier=$resultSet->fetch_array();
                }
                else{
                    die('Incorrect modifier ID');
                }
                $timestamp = strtotime($row['LastUpdateTime']);

                echo('<span class="badge bg-light-info">Last modified by '.$modifier['name'].' at '.date("d/m/y H:i", $timestamp).'</span><div style="width:5%"></div>');
            }
            
        ?>
        
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=clients_list">Clients</a></li>
            <li class="breadcrumb-item active" aria_current="page"><?php echo($modeText) ?></li>
        </ol>
    </div>


    <!-- Input Data -->
    <div class="row card-body" style="padding-bottom:20px;">
        <div class="col-lg-12">
            <form enctype="multipart/form-data" method="POST" action="index.php?page=clients_list">
                <input type="hidden" id="mode" name="mode" value=<?php echo($mode) ?>>
                <?php
                if ($mode=="update"){
                    echo("<input type='hidden' id='Id' name='Id' value='$id'>");
                    echo("<input type='hidden' id='previousName' name='previousName' value='".$row['Name']."'>");
                }
                ?>

                <div class="form-group mb-3">
                    <label for="Name">Name</label>
                    <input class="form-control mb-3" type="text" placeholder="Client name" id="Name" name="Name" required value="<?php if ($mode=="update") echo($row["Name"]);?>">
                </div> 
                <div class="form-group mb-3">
                    <label for="Address">Address</label>
                    <input class="form-control mb-3" type="text" placeholder="Address" id="Address" name="Address" required value="<?php if ($mode=="update") echo($row["Address"]);?>">
                </div> 
                <div class="form-group mb-3">
                    <label for="PhoneNumber">Phone Number</label>
                    <input class="form-control mb-3" type="text" placeholder="Phone Number" id="PhoneNumber" name="PhoneNumber" required value="<?php if ($mode=="update") echo($row["PhoneNumber"]);?>">
                </div> 

                <div class="form-group">
                    <label for="CityId">City</label>
                    <select class="select2-single form-control" name="CityId" id="CityId" required>
                        <option value="">Select a city</option>
                        <?php
                            $q = "SELECT Id, Name FROM city";
                            $res = $dbCon->query($q);
                            if ($res){
                                while ($r=$res->fetch_array()){
                                    echo("<option value='".$r['Id']."'");
                                    if ($mode == "update" && $row['CityId']==$r['Id']) echo(" selected");
                                    echo(">".$r['Name']."</option>");
                                }
                            }
                        ?>
                    </select>
                </div> 


                <div class="row" style="margin-top: 30px">
                    <div class="col-sm-4">
                        <a href="index.php?page=clients_list"><button type="button" class="btn btn-warning">Cancel</button></a>
                    </div>
                    
                    <div class="col-sm-4" style="text-align:center;">
                        <?php
                            if ($mode=="update"){
                            ?>
                                <div class="modal-danger me-1 mb-1 d-inline-block">
                                    <!-- Button trigger for danger theme modal -->
                                    <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#danger">
                                        Delete
                                    </button>

                                    <!--Danger theme Modal -->
                                    <div class="modal fade text-left" id="danger" tabindex="-1"
                                        role="dialog" aria-labelledby="myModalLabel120"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"
                                            role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger">
                                                    <h5 class="modal-title white" id="myModalLabel120">
                                                        Delete Client "<?php echo $row['Name']; ?>"?
                                                    </h5>
                                                    <button type="button" class="close"
                                                        data-bs-dismiss="modal" aria-label="Close">
                                                        <i data-feather="x"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="text-align:justify;">
                                                    Deleting a client that is related to other data might cause an error. Would you like to continue?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button"
                                                        class="btn btn-light-secondary"
                                                        data-bs-dismiss="modal">
                                                        <i class="bx bx-x d-block d-sm-none"></i>
                                                        <span class="d-none d-sm-block">Cancel</span>
                                                    </button>
                                                    <button type="submit" class="btn btn-danger ml-1"
                                                        name="delete" value="Delete" >
                                                        <i class="bx bx-check d-block d-sm-none"></i>
                                                        <span class="d-none d-sm-block">Yes</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php    
                            }
                        ?>
                    </div>

                    <div class="col-sm-4" style="text-align:right;">
                        <button type="submit" class="btn btn-primary" name="save" value="Save">
                        <?php
                            switch($mode){
                                case "insert":
                                    echo("Add New");
                                    break;
                                case "update":
                                    echo("Update");
                                    break;

                                default:
                                die('Incorrect Access');
                            }
                        ?>

                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    $(document).ready(function(){
        $('.select2-single').select2();
    });
</script>