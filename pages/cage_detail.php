<?php
defined('siteToken') or die('Restricted Access');

if (!empty($_GET["mode"])){
    $mode=$_GET["mode"];
}
else{
    die('Incorrect Access');
}
if (!file_exists('./classes/cage.php')){
    die("Class cage Not Found");
}
require_once('./classes/cage.php');

if (!file_exists('./classes/staff.php')){
    die("Class staff Not Found");
}
require_once('./classes/staff.php');

$staff = new Staff($dbCon);
$cage = new cage($dbCon);
?>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 card-header">
        <h1 class="h3 mb-0 text-gray-800">cage - 
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

                    $col= "Id, Name, Dimensions, LastModifier, LastUpdateTime";

                    $resultSet = $cage->get_data($col, "Id=$id", 1);
                    if ($resultSet){
                        $row=$resultSet->fetch_array();
                    }
                    else{
                        die('Incorrect cage ID');
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

                echo('<span class="badge bg-light-info">Last modified by '.$modifier['name'].' at '.date("d/m/y H:i", $timestamp).'</span><div style="width:10%"></div>');
            }
            
        ?>
        
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=cages_list">Cages</a></li>
            <li class="breadcrumb-item active" aria_current="page"><?php echo($modeText) ?></li>
        </ol>
    </div>


    <!-- Input Data -->
    <div class="row card-body" style="padding-bottom:20px;">
        <div class="col-lg-12">
            <form enctype="multipart/form-data" method="POST" action="index.php?page=cages_list">
                <input type="hidden" id="mode" name="mode" value=<?php echo($mode) ?>>
                <?php
                if ($mode=="update"){
                    echo("<input type='hidden' id='Id' name='Id' value='$id'>");
                    echo("<input type='hidden' id='previousName' name='previousName' value='".$row['Name']."'>");
                }
                ?>

                <div class="form-group mb-3">
                    <label for="Name">Name</label>
                    <input class="form-control mb-3" type="text" placeholder="cage name" id="Name" name="Name" required value="<?php if ($mode=="update") echo($row["Name"]);?>">
                </div> 
                <div class="form-group mb-3">
                    <label for="Dimension">Dimension</label>
                    <input class="form-control mb-3" type="text" placeholder="Cage dimension" id="Dimension" name="Dimension" required value="<?php if ($mode=="update") echo($row["Dimensions"]);?>">
                </div> 

                <div class="row" style="margin-top: 30px">
                    <div class="col-sm-4">
                        <a href="index.php?page=cages_list"><button type="button" class="btn btn-warning">Cancel</button></a>
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
                                                        Delete "<?php echo $row['Name']; ?>"?
                                                    </h5>
                                                    <button type="button" class="close"
                                                        data-bs-dismiss="modal" aria-label="Close">
                                                        <i data-feather="x"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="text-align:justify;">
                                                    Deleting a cage that is related to other data might cause an error. Would you like to continue?
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