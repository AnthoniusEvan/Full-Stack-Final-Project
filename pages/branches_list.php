<?php
defined('siteToken') or die('Restricted Access');

if (!file_exists('./classes/branch.php')){
    die("Class branch Not Found");
}
require_once('./classes/branch.php');

$branch = new Branch($dbCon);
$errorMsg = "";
if (!empty($_POST["mode"])){
    $post_data = array();

    if (!empty($_POST["Id"])){
        list($id, $validation_status) = sanitize($dbCon, $_POST["Id"], "int");
        if(!$validation_status){
            $errorMsg = "ERROR - Invalid branch ID";
        }
    }
    if (!empty($_POST["Name"])){
        list($post_data['Name'], $validation_status) = sanitize($dbCon, $_POST["Name"], "string");
    }

    if (!empty($_POST["Address"])){
        list($post_data['Address'], $validation_status) = sanitize($dbCon, $_POST["Address"], "string");
    }

    if (!empty($_POST["PhoneNumber"])){
        list($post_data['PhoneNumber'], $validation_status) = sanitize($dbCon, $_POST["PhoneNumber"], "string");
    }
    
    if (!empty($_POST["CityId"])){
        list($post_data['CityId'], $validation_status) = sanitize($dbCon, $_POST["CityId"], "int");
        if(!$validation_status){
            $errorMsg = "ERROR - Invalid city ID";
        }
    }


    $post_data['LastModifier'] = $_SESSION['active_user']->id;

    $errorMsg = "";

    switch($_POST["mode"]){
        case "insert":
            if ($branch->name_already_exists($post_data['Name'])) {
                $errorMsg = 'branch with name "'.$post_data['Name'].'" already exists!';
                break;
            }

            if (empty($_POST["CityId"])){
                $errorMsg = 'Please select a city!';
                break;
            }
            if ($branch->insert_data($post_data) === FALSE){
                $errorMsg = "Failed to insert branch data.";
            }

            break;
        case "update":
            if (!empty($_POST["delete"])){
                $_POST["mode"]="delete";
                if ($branch->remove_data($id) === FALSE){
                    $errorMsg = "Failed to delete branch data.";
                }
                
                break;
            }

            if ($_POST['previousName'] != $post_data['Name'] && $branch->name_already_exists($post_data['Name'])) {
                $errorMsg = 'branch with name "'.$post_data['Name'].'" already exists!';
                break;
            }

            if (empty($_POST["CityId"])){
                $errorMsg = 'Please select a city!';
                break;
            }
            if ($errorMsg == "" && $branch->update_data($id, $post_data) === FALSE){
                $errorMsg = "Failed to update branch data.";
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
            <?php echo(" Successfully ".$_POST["mode"]." branch data!"); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
        <?php
    } else
    {
        ?>
        <div class="alert alert-danger alert-dismissible show fade" role="alert">
            <?php echo(" Fail to ".$_POST["mode"]." branch data! ".$errorMsg); ?>
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
    <h1 class="h3 mb-0 text-gray-800">Branches</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Home</a></li>
        <li class="breadcrumb-item active" aria_current="page">Branches</li>
    </ol>
</div>


<!-- Search Criteria -->
<div class="row" style="padding-bottom:20px;">
    <div class="col-lg-12">
        <form method="GET" action="index.php">
            <input type="hidden" id="page" name="page" value="branches_list">
            
            <div class="form-group mb-3">
                <label for="Name">Name</label>
                <input class="form-control mb-3" type="text" placeholder="Search by branch name" id="Name" name="Name" <?php if(!empty($_GET["Name"])) echo(" value='".$_GET["Name"])."'" ?>>
            </div> 

            <div class="form-group mb-3">
                <label for="Address">Address</label>
                <input class="form-control mb-3" type="text" placeholder="Search by branch's address" id="Address" name="Address" <?php if(!empty($_GET["Address"])) echo(" value='".$_GET["Address"])."'" ?>>
            </div> 

            <div class="form-group mb-3">
                <label for="PhoneNumber">Phone Number</label>
                <input class="form-control mb-3" type="text" placeholder="Search by branch's phone number" id="PhoneNumber" name="PhoneNumber" <?php if(!empty($_GET["PhoneNumber"])) echo(" value='".$_GET["PhoneNumber"])."'" ?>>
            </div> 

            <div class="form-group">
                <label for="CityId">City</label>
                <select class="select2-single form-control" name="CityId" id="CityId">
                    <option value="">Select a city</option>
                    <?php
                        if(!empty($_GET["CityId"])){
                            list($city, $validation_status) = sanitize($dbCon, $_GET["CityId"], "int");
                            if(!$validation_status){
                                $city="";
                            }
                        }
                        
                        $q = "SELECT Id, Name FROM city";
                        $res = $dbCon->query($q);
                        if ($res){
                            while ($r=$res->fetch_array()){
                                echo("<option value='".$r['Id']."'");
                                if (isset($city) && $city==$r['Id']) echo(" selected");
                                echo(">".$r['Name']."</option>");
                            }
                        }
                    ?>
                </select>
            </div> 

            <br>
            <div class="row">
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="col-sm-6" style="text-align:right;">
                    <a href="index.php?page=branch_detail&mode=insert"><button type="button" class="btn btn-success">Add New</button>
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
                <th>Name</th>
                <th>Address</th>
                <th>Phone Number</th>
                <th>City</th>
                <th style="text-align: center;"><i class="bi bi-pencil-square"></i></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $constraint="1";

                if(!empty($_GET["Name"])){
                    list($name, $validation_status) = sanitize($dbCon, $_GET["Name"], "string");
                    $constraint.=" AND b.Name LIKE '%".$name."%'";
                }
                if(!empty($_GET["Address"])){
                    list($address, $validation_status) = sanitize($dbCon, $_GET["Address"], "string");
                    $constraint.=" AND b.Address LIKE '%".$address."%'";
                }
                if(!empty($_GET["PhoneNumber"])){
                    list($phone, $validation_status) = sanitize($dbCon, $_GET["PhoneNumber"], "string");
                    $constraint.=" AND b.PhoneNumber LIKE '%".$phone."%'";
                }
                if(!empty($_GET["CityId"])){
                    list($city, $validation_status) = sanitize($dbCon, $_GET["CityId"], "int");
                    if($validation_status){
                        $constraint.=" AND c.CityId ='$city'";
                    }
                }

                $pageStart = ($pageNum-1) * $maxRows;
 
                $columns = "b.Id as Id, b.Name as Name, b.Address as Address, b.PhoneNumber as PhoneNumber, c.Name as CityName";
                $limit= "$pageStart, $maxRows";

                $resultSet = $branch->get_inner_join_data($columns, $constraint, $limit);
                if ($resultSet){
                    while($row = $resultSet->fetch_array()){
                        echo("
                        <tr>
                        <td>".$row["Name"]."</td>
                        <td>".$row["Address"]."</td>
                        <td>".$row["PhoneNumber"]."</td>
                        <td>".$row["CityName"]."</td>
                        <td style='text-align: center;'><a href='index.php?page=branch_detail&mode=update&id=".$row["Id"]."'><button class='btn-sm btn-primary'><i class='bi bi-pencil-square'></i></button></a></td>
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
    $resCount=$branch->get_inner_join_data($columns, $constraint);
    $rCount=$resCount->fetch_array();
    $totalRows = $rCount["totalRows"];
    $totalPages = ceil($totalRows/$maxRows);
?>

<div class="row">
    <div class="col-sm-4">
        <?php
            $pageEnd = min($totalRows,$pageStart + $maxRows);
            echo("Showing ".min($pageStart + 1, $pageEnd)." to ".$pageEnd." of ".$totalRows." records");
        ?>
    </div>

    <div class="col-sm-8">
        <nav>
            <ul class="pagination justify-content-end">
                <li class="page-item <?php if($pageNum<=1){ echo 'disabled';}?>">
                    <a class="page-link" href="<?php if($pageNum<=1){ echo '#';} else { echo "?page=branches_list&pageNum=" . ($pageNum-1);} ?>">Previous</a>
                </li>   

                <?php for ($i=1; $i<=$totalPages;$i++){?>
                    <li class="page-item <?php if ($pageNum==$i) {echo 'active';}?>">
                        <a class="page-link" href="?page=branches_list&pageNum=<?php echo($i);?>"><?php echo($i);?></a>
                    </li>
                <?php }?>
                
                    <li class="page-item <?php if($pageNum>=$totalPages){ echo 'disabled';}?>">
                    <a class="page-link" href="<?php if($pageNum>=$totalPages){ echo '#';} else { echo "?page=branches_list&pageNum=" . ($pageNum + 1);}?>">Next</a>
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