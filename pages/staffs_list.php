<?php
defined('siteToken') or die('Restricted Access');

if (!file_exists('./classes/staff.php')){
    die("Class Staff Not Found");
}
require_once('./classes/staff.php');

$staff = new Staff($dbCon);
$errorMsg = "";
if (!empty($_POST["mode"])){
    $post_data = array();

    $id="";
    if (!empty($_POST["Id"])){
        list($id, $validation_status) = sanitize($dbCon, $_POST["Id"], "int");
        if(!$validation_status){
            $errorMsg = "ERROR - Invalid staff ID";
        }
    }

    if (!empty($_POST["BranchId"])){
        list($post_data['BranchId'], $validation_status) = sanitize($dbCon, $_POST["BranchId"], "int");
        if(!$validation_status){
            $errorMsg = "ERROR - Invalid Branch ID";
        }
    }
   
    if (!empty($_POST["Name"])){
        list($post_data['Name'], $validation_status) = sanitize($dbCon, $_POST["Name"], "string");
    }

    if (!empty($_POST["Username"])){
        list($post_data['Username'], $validation_status) = sanitize($dbCon, $_POST["Username"], "string");
    }


    if (!empty($_POST["Password"]) && $_POST["Password"]!="" && $_POST["Password"]!=$censorText){
        list($post_data['Password'], $validation_status) = sanitize($dbCon, $_POST["Password"], "string");
    }
    
    $post_data['LastModifier'] = $_SESSION['active_user']->id;

    $errorMsg = "";

    switch($_POST["mode"]){
        case "insert":
            if ($staff->username_already_exists($post_data['Username'])) {
                $errorMsg = 'Staff with username "'.$post_data['Username'].'" already exists!';
                break;
            }

            if (empty($_POST["BranchId"])){
                $errorMsg = 'Please select a branch!';
                break;
            }

            if ($staff->insert_data($post_data) === FALSE){
                $errorMsg = "Failed to insert staff data.";
            }

            break;
        case "update":
            if (!empty($_POST["delete"])){
                $_POST["mode"]="delete";
                if ($staff->remove_data($id) === FALSE){
                    $errorMsg = "Failed to delete staff data.";
                }
                else{
                    if ($_SESSION['active_user']->id == $id) {
                        echo "<script>location.href = 'index.php?logout=1';</script>";
                    }
                }
                
                break;
            }

            if ($errorMsg == "" && $staff->update_data($id, $post_data) === FALSE){
                $errorMsg = "Failed to update staff data.";
            }
            
            break;
        case "delete":
            break;

        default:
            die('Incorrect Access');
    }

    if ($errorMsg == ""){
        ?>
        <div class="alert alert-success alert-dismissible show fade" role="alert">
            <?php echo(" Successfully ".$_POST["mode"]." staff data!"); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
        <?php
    } else
    {
        ?>
        <div class="alert alert-danger alert-dismissible show fade" role="alert">
            <?php echo(" Fail to ".$_POST["mode"]." staff data! ".$errorMsg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
        <?php
    }
}

$pageNum = (isset($_GET["pageNum"]) && is_numeric($_GET["pageNum"])) ? $_GET["pageNum"] : 1;
                
list($pageNum, $validation_status) = sanitize($dbCon, $pageNum, "int");
if (!$validation_status){
    $errorMsg = "ERROR - Invalid page number";
} 


?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Staffs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Home</a></li>
        <li class="breadcrumb-item active" aria_current="page">Staffs</li>
    </ol>
</div>


<!-- Search Criteria -->
<div class="row" style="padding-bottom:20px;">
    <div class="col-lg-12">
        <form method="GET" action="index.php">
            <input type="hidden" id="page" name="page" value="staffs_list">
            
            <div class="form-group">
                <label for="Branch">Branch</label>
                <select class="select2-single form-control" name="Branch" id="Branch">
                    <option value="">Select a branch</option>
                    <?php
                        if(!empty($_GET["Branch"])){
                            list($branch, $validation_status) = sanitize($dbCon, $_GET["Branch"], "int");
                            if(!$validation_status){
                                $branch="";
                            }
                        }
                        
                        $q = "SELECT Id, Name FROM branch";
                        $resBranches = $dbCon->query($q);
                        if ($resBranches){
                            while ($rBranches=$resBranches->fetch_array()){
                                echo("<option value='".$rBranches['Id']."'");
                                if (isset($branch) && $branch==$rBranches['Id']) echo(" selected");
                                echo(">".$rBranches['Name']."</option>");
                            }
                        }
                    ?>
                </select>
            </div> 


            <div class="form-group">
                <label for="Name">Name</label>
                <input class="form-control mb-3" type="text" placeholder="Search by staff name" id="Name" name="Name" <?php if(!empty($_GET["Name"])) echo(" value=".$_GET["Name"])?>>
            </div> 

            <div class="form-group">
                <label for="Username">Username</label>
                <input class="form-control mb-3" type="text" placeholder="Search by staff username" id="Username" name="Username" <?php if(!empty($_GET["Username"])) echo(" value=".$_GET["Username"])?>>
            </div> 

         
            <br>
            <div class="row">
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="col-sm-6" style="text-align:right;">
                    <a href="index.php?page=staff_detail&mode=insert"><button type="button" class="btn btn-success">Add New</button>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-light align-items-center table-flush table-hover">
        <thead class="thread-light">
            <tr>
                <th>Branch</th>
                <th>Full Name</th>
                <th>Username</th>
                <th style="text-align: center;"><i class="bi bi-pencil-square"></i></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $constraint="1";
                if(!empty($_GET["Branch"])){
                    list($branch, $validation_status) = sanitize($dbCon, $_GET["Branch"], "int");
                    if($validation_status){
                        $constraint.=" AND s.BranchId ='$branch'";
                    }
                }
                if(!empty($_GET["Name"])){
                    list($name, $validation_status) = sanitize($dbCon, $_GET["Name"], "string");
                    $constraint.=" AND s.Name LIKE '%".$name."%'";
                }
                if(!empty($_GET["Username"])){
                    list($username, $validation_status) = sanitize($dbCon, $_GET["Username"], "string");
                    $constraint.=" AND s.Username LIKE '%".$username."%'";
                }

                $pageStart = ($pageNum-1) * $maxRows;
 
                $columns = "s.Id as id, b.Name as branch_name, s.Name as name, s.Username as username";
                $limit= "$pageStart, $maxRows";

                $resultSet = $staff->get_inner_join_data($columns, $constraint, $limit);
                if ($resultSet){
                    while($row = $resultSet->fetch_array()){

                        $outline="";
                        $disabled="";
                        if ($_SESSION['active_user']->role != "Administrator" && $_SESSION['active_user']->id != $row["id"]){
                            $outline="-outline";
                            $disabled="disabled";
                        }

                        echo("
                        <tr>
                        <td>".$row["branch_name"]."</td>
                        <td>".$row["name"]."</td>
                        <td>".$row["username"]."</td>
                        <td style='text-align: center;'><a href='index.php?page=staff_detail&mode=update&id=".$row["id"]."'><button class='btn-sm btn$outline-primary' $disabled><i class='bi bi-pencil-square'></i></button></a></td>
                        </tr>
                        ");
                    }
                }
            ?>
        </tbody>
    </table>
</div>
<?php
    $columns = "count(*) as totalRows";
    $resCount=$staff->get_inner_join_data($columns, $constraint);
    $rCount=$resCount->fetch_array();
    $totalRows = $rCount["totalRows"];
    $totalPages = ceil($totalRows/$maxRows);
?>

<div class="row">
    <div class="col-sm-4">
        <?php

            echo("Showing ".($pageStart + 1)." to ".($pageStart + $maxRows)." of ".$totalRows." records");
        ?>
    </div>

    <div class="col-sm-8">
        <nav>
            <ul class="pagination justify-content-end">
                <li class="page-item <?php if($pageNum<=1){ echo 'disabled';}?>">
                    <a class="page-link" href="<?php if($pageNum<=1){ echo '#';} else { echo "?page=staffs_list&pageNum=" . ($pageNum-1);} ?>">Previous</a>
                </li>   

                <?php for ($i=1; $i<=$totalPages;$i++){?>
                    <li class="page-item <?php if ($pageNum==$i) {echo 'active';}?>">
                        <a class="page-link" href="?page=staffs_list&pageNum=<?php echo($i);?>"><?php echo($i);?></a>
                    </li>
                <?php }?>
                
                    <li class="page-item <?php if($pageNum>=$totalPages){ echo 'disabled';}?>">
                    <a class="page-link" href="<?php if($pageNum>=$totalPages){ echo '#';} else { echo "?page=staffs_list&pageNum=" . ($pageNum + 1);}?>">Next</a>
                </li>  
            </ul>
        </nav>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.select2-single').select2();
    });
</script>