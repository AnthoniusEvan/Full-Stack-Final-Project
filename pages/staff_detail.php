<?php
defined('siteToken') or die('Restricted Access');

if (!empty($_GET["mode"])){
    $mode=$_GET["mode"];
}
else{
    die('Incorrect Access');
}
if (!file_exists('./classes/staff.php')){
    die("Class staff Not Found");
}
require_once('./classes/staff.php');

$staff = new Staff($dbCon);
?>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 card-header">
        <h1 class="h3 mb-0 text-gray-800">Staff - 
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

                    $col= "s.Id as id, b.Id as branch_id, b.Name as branch_name, s.Name as name, s.Username as username, s.LastModifier as last_modifier, s.LastUpdateTime as last_update_time";

                    $resultSet = $staff->get_inner_join_data($col, "s.Id=$id", 1);
                    if ($resultSet){
                        $row=$resultSet->fetch_array();
                    }
                    else{
                        die('Incorrect staff ID');
                    }

                    if ($_SESSION['active_user']->role != "Administrator" && $_SESSION['active_user']->id != $id){
                        die('Restricted Access');
                    }
                    else echo($modeText);
                    break;
                default:
                    die('Incorrect Access');
            }
            
        ?></h1>

        <?php
            if ($mode == "update" && $row["last_modifier"]!=""){
                $resultSet = $staff->get_inner_join_data("s.Name as name", "s.Id=".$row['last_modifier'], 1);
                if ($resultSet){
                    $modifier=$resultSet->fetch_array();
                }
                else{
                    die('Incorrect modifier ID');
                }
                $timestamp = strtotime($row['last_update_time']);

                echo('<span class="badge bg-light-info">Last modified by '.$modifier['name'].' at '.date("d/m/y H:i", $timestamp).'</span><div style="width:10%"></div>');
            }
            
        ?>
        
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=staffs_list">Staffs</a></li>
            <li class="breadcrumb-item active" aria_current="page"><?php echo($modeText) ?></li>
        </ol>
    </div>


    <!-- Input Data -->
    <div class="row card-body" style="padding-bottom:20px;">
        <div class="col-lg-12">
            <form enctype="multipart/form-data" method="POST" action="index.php?page=staffs_list">
                <input type="hidden" id="mode" name="mode" value=<?php echo($mode) ?>>
                <?php
                if ($mode=="update"){
                    echo("<input type='hidden' id='Id' name='Id' value='$id'>");
                }
                ?>
                <div class="form-group mb-3">
                    <label for="BranchId">Branch</label>
                    <select class="select2-single form-control" name="BranchId" id="BranchId">
                        <option value="">Select a branch</option>
                        <?php
                            $q = "SELECT Id, Name FROM branch";
                            $resBranches = $dbCon->query($q);
                            if ($resBranches){
                                while ($rBranches=$resBranches->fetch_array()){
                                    echo("<option value='".$rBranches['Id']."'");
                                    if ($mode=="update" && $row["branch_id"]==$rBranches['Id']) echo(" selected");
                                    echo(">".$rBranches['Name']."</option>");
                                }
                            }
                        ?>
                    </select>
                </div> 


                <div class="form-group mb-3">
                    <label for="Name">Name</label>
                    <input class="form-control mb-3" type="text" placeholder="Full name" id="Name" name="Name" required value="<?php if ($mode=="update") echo($row["name"]);?>">
                </div> 

                <div class="form-group mb-3">
                    <label for="Username">Username</label>
                    <input class="form-control mb-3" type="text" placeholder="Username" id="Username" name="Username" required value="<?php if ($mode=="update") echo($row["username"]);?>">
                </div> 

                <div class="form-group">
                    <label for="Password">Password</label>
                    <div class="input-group mb-3">
                        <input class="form-control" type="text" placeholder="Password" id="Password" name="Password" <?php if ($mode=="update") echo('disabled'); ?> aria-describedby="password" value="<?php if ($mode=="update") echo($censorText); ?>"><button class="btn btn-primary" type="button" id="password"><i class="bi bi-eye" id="toggleIcon"></i></button>
                    </div>
                </div> 
        

                <div class="row" style="margin-top: 30px">
                    <div class="col-sm-4">
                        <a href="index.php?page=staffs_list"><button type="button" class="btn btn-warning">Cancel</button></a>
                    </div>
                    
                    <div class="col-sm-4" style="text-align:center;">
                        <?php
                            if ($mode=="update"){
                                $loggedin_warning="";
                                if ($_SESSION['active_user']->id == $row['id']) $loggedin_warning = "You are currently logged in as the staff that you are going to remove. The system will log you out after the account is deleted.";
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
                                                        Delete staff with username "<?php echo $row['name']; ?>"?
                                                    </h5>
                                                    <button type="button" class="close"
                                                        data-bs-dismiss="modal" aria-label="Close">
                                                        <i data-feather="x"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="text-align:justify;">
                                                    Deleting a staff account might affect other data involved in the interaction
                                                    with the account. Data handled by this staff will be replaced by a label explicitly written as 'deleted user'. <?php echo $loggedin_warning?> Would you like to continue?
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

    document.getElementById('password').addEventListener('click', async (e) => {
        const inputPass = document.getElementById("Password");
        const toggleIcon = document.getElementById("toggleIcon");

        if (!inputPass.disabled || inputPass.dataset.verified == "true"){
            if (inputPass.type === "password"){
                inputPass.type = "text";
                toggleIcon.classList.add("bi-eye-slash");
                toggleIcon.classList.remove("bi-eye");
            }
            else{
                inputPass.type = "password";
                toggleIcon.classList.add("bi-eye");
                toggleIcon.classList.remove("bi-eye-slash");
            }
        }
    });
    

</script>