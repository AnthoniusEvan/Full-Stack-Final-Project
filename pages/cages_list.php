<?php
defined('siteToken') or die('Restricted Access');

if (!file_exists('./classes/cage.php')){
    die("Class Cage Not Found");
}
require_once('./classes/cage.php');

$cage = new Cage($dbCon);
$errorMsg = "";
if (!empty($_POST["mode"])){
    $post_data = array();

    $id="";
    if (!empty($_POST["Id"])){
        list($id, $validation_status) = sanitize($dbCon, $_POST["Id"], "int");
        if(!$validation_status){
            $errorMsg = "ERROR - Invalid cage ID";
        }
    }

    if (!empty($_POST["Name"])){
        list($post_data['Name'], $validation_status) = sanitize($dbCon, $_POST["Name"], "string");
    }

    if (!empty($_POST["Dimension"])){
        list($post_data['Dimensions'], $validation_status) = sanitize($dbCon, $_POST["Dimension"], "string");
    }
    
    $post_data['LastModifier'] = $_SESSION['active_user']->id;

    $errorMsg = "";

    switch($_POST["mode"]){
        case "insert":
            if ($cage->name_already_exists($post_data['Name'])) {
                $errorMsg = 'Cage with name "'.$post_data['Name'].'" already exists!';
                break;
            }

            if (empty($_POST["Dimension"])){
                $errorMsg = 'Please select a dimension!';
                break;
            }
            if ($cage->insert_data($post_data) === FALSE){
                $errorMsg = "Failed to insert cage data.";
            }

            break;
        case "update":
            if (!empty($_POST["delete"])){
                $_POST["mode"]="delete";
                if ($cage->remove_data($id) === FALSE){
                    $errorMsg = "Failed to delete cage data.";
                }
                
                break;
            }

            if ($_POST['previousName'] != $post_data['Name'] && $cage->name_already_exists($post_data['Name'])) {
                $errorMsg = 'Cage with name "'.$post_data['Name'].'" already exists!';
                break;
            }

            if (empty($_POST["Dimension"])){
                $errorMsg = 'Please select a province!';
                break;
            }
            if ($errorMsg == "" && $cage->update_data($id, $post_data) === FALSE){
                $errorMsg = "Failed to update cage data.";
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
            <?php echo(" Successfully ".$_POST["mode"]." cage data!"); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
        <?php
    } else
    {
        ?>
        <div class="alert alert-danger alert-dismissible show fade" role="alert">
            <?php echo(" Fail to ".$_POST["mode"]." cage data! ".$errorMsg); ?>
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
    <h1 class="h3 mb-0 text-gray-800">Cages</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Home</a></li>
        <li class="breadcrumb-item active" aria_current="page">Cages</li>
    </ol>
</div>


<!-- Search Criteria -->
<div class="row" style="padding-bottom:20px;">
    <div class="col-lg-12">
        <form method="GET" action="index.php">
            <input type="hidden" id="page" name="page" value="cages_list">
            
            <div class="form-group mb-3">
                <label for="Name">Name</label>
                <input class="form-control mb-3" type="text" placeholder="Search by cage name" id="Name" name="Name" <?php if(!empty($_GET["Name"])) echo(" value='".$_GET["Name"])."'" ?>>
            </div> 

            <div class="form-group">
                <label for="Dimension">Dimension</label>
                <select class="select2-single form-control" name="Dimension" id="Dimension">
                    <option value="">Select cage dimension</option>
                    <?php
                        $dimensions = ["10x5x4 cm", "8x6x3 cm","12x7x5 cm","9x4x3.5 cm","15x10x8 cm"];

                        foreach ($dimensions as $dimension) {
                            $selected = "";
                            if (!empty($_GET["Dimensions"]) && $dimension == $_GET["Dimensions"]) {
                                $selected = "selected";
                            }
                            echo "<option value='$dimension' $selected>$dimension</option>";
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
                    <a href="index.php?page=cage_detail&mode=insert"><button type="button" class="btn btn-success">Add New</button>
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
                <th>Dimension</th>
                <th style="text-align: center;"><i class="bi bi-pencil-square"></i></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $constraint="1";

                if(!empty($_GET["Name"])){
                    list($name, $validation_status) = sanitize($dbCon, $_GET["Name"], "string");
                    $constraint.=" AND Name LIKE '%".$name."%'";
                }
                if(!empty($_GET["Dimension"])){
                    list($dimension, $validation_status) = sanitize($dbCon, $_GET["Dimension"], "string");
                    $constraint.=" AND Dimensions LIKE '%".$dimension."%'";
                }

                $pageStart = ($pageNum-1) * $maxRows;
 
                $columns = "Id, Name, Dimensions";
                $limit= "$pageStart, $maxRows";

                $resultSet = $cage->get_data($columns, $constraint, $limit);
                if ($resultSet){
                    while($row = $resultSet->fetch_array()){
                        echo("
                        <tr>
                        <td>".$row["Name"]."</td>
                        <td>".$row["Dimensions"]."</td>
                        <td style='text-align: center;'><a href='index.php?page=cage_detail&mode=update&id=".$row["Id"]."'><button class='btn-sm btn-primary'><i class='bi bi-pencil-square'></i></button></a></td>
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
    $resCount=$cage->get_data($columns, $constraint);
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
                    <a class="page-link" href="<?php if($pageNum<=1){ echo '#';} else { echo "?page=cages_list&pageNum=" . ($pageNum-1);} ?>">Previous</a>
                </li>   

                <?php for ($i=1; $i<=$totalPages;$i++){?>
                    <li class="page-item <?php if ($pageNum==$i) {echo 'active';}?>">
                        <a class="page-link" href="?page=cages_list&pageNum=<?php echo($i);?>"><?php echo($i);?></a>
                    </li>
                <?php }?>
                
                    <li class="page-item <?php if($pageNum>=$totalPages){ echo 'disabled';}?>">
                    <a class="page-link" href="<?php if($pageNum>=$totalPages){ echo '#';} else { echo "?page=cages_list&pageNum=" . ($pageNum + 1);}?>">Next</a>
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