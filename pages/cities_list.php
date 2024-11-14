<?php
defined('siteToken') or die('Restricted Access');

if (!file_exists('./classes/city.php')){
    die("Class City Not Found");
}
require_once('./classes/city.php');

$city = new City($dbCon);
$errorMsg = "";
if (!empty($_POST["mode"])){
    $post_data = array();

    $id="";
    if (!empty($_POST["Id"])){
        list($id, $validation_status) = sanitize($dbCon, $_POST["Id"], "int");
        if(!$validation_status){
            $errorMsg = "ERROR - Invalid city ID";
        }
    }

    if (!empty($_POST["Name"])){
        list($post_data['Name'], $validation_status) = sanitize($dbCon, $_POST["Name"], "string");
    }

    if (!empty($_POST["Province"])){
        list($post_data['Province'], $validation_status) = sanitize($dbCon, $_POST["Province"], "string");
    }
    
    $post_data['LastModifier'] = $_SESSION['active_user']->id;

    $errorMsg = "";

    switch($_POST["mode"]){
        case "insert":
            if ($city->name_already_exists($post_data['Name'])) {
                $errorMsg = 'City with name "'.$post_data['Name'].'" already exists!';
                break;
            }

            if (empty($_POST["Province"])){
                $errorMsg = 'Please select a province!';
                break;
            }
            if ($city->insert_data($post_data) === FALSE){
                $errorMsg = "Failed to insert city data.";
            }

            break;
        case "update":
            if (!empty($_POST["delete"])){
                $_POST["mode"]="delete";
                if ($city->remove_data($id) === FALSE){
                    $errorMsg = "Failed to delete city data.";
                }
                
                break;
            }

            if ($_POST['previousName'] != $post_data['Name'] && $city->name_already_exists($post_data['Name'])) {
                $errorMsg = 'City with name "'.$post_data['Name'].'" already exists!';
                break;
            }

            if (empty($_POST["Province"])){
                $errorMsg = 'Please select a province!';
                break;
            }
            if ($errorMsg == "" && $city->update_data($id, $post_data) === FALSE){
                $errorMsg = "Failed to update city data.";
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
            <?php echo(" Successfully ".$_POST["mode"]." city data!"); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
        <?php
    } else
    {
        ?>
        <div class="alert alert-danger alert-dismissible show fade" role="alert">
            <?php echo(" Fail to ".$_POST["mode"]." city data! ".$errorMsg); ?>
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
    <h1 class="h3 mb-0 text-gray-800">Cities</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Home</a></li>
        <li class="breadcrumb-item active" aria_current="page">Cities</li>
    </ol>
</div>


<!-- Search Criteria -->
<div class="row" style="padding-bottom:20px;">
    <div class="col-lg-12">
        <form method="GET" action="index.php">
            <input type="hidden" id="page" name="page" value="cities_list">
            
            <div class="form-group mb-3">
                <label for="Name">Name</label>
                <input class="form-control mb-3" type="text" placeholder="Search by city name" id="Name" name="Name" <?php if(!empty($_GET["Name"])) echo(" value='".$_GET["Name"])."'" ?>>
            </div> 

            <div class="form-group">
                <label for="Province">Province</label>
                <select class="select2-single form-control" name="Province" id="Province">
                    <option value="">Select a province</option>
                    <?php
                        $provinces = ["Aceh","Sumatera Utara","Sumatera Barat","Riau","Kepulauan Riau","Jambi","Sumatera Selatan","Kepulauan Bangka Belitung","Bengkulu","Lampung","DKI Jakarta","Jawa Barat","Jawa Tengah","DI Yogyakarta","Jawa Timur","Banten","Bali","Nusa Tenggara Barat","Nusa Tenggara Timur","Kalimantan Barat","Kalimantan Tengah","Kalimantan Selatan","Kalimantan Timur","Kalimantan Utara","Sulawesi Utara","Gorontalo","Sulawesi Tengah","Sulawesi Barat","Sulawesi Selatan","Sulawesi Tenggara","Maluku","Maluku Utara","Papua Barat","Papua"];

                        foreach ($provinces as $province){
                            $selected="";
                            if (!empty($_GET["Province"]) && $province == $_GET["Province"]) $selected = "selected";
                            echo "<option value='$province' $selected>$province</option>";
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
                    <a href="index.php?page=city_detail&mode=insert"><button type="button" class="btn btn-success">Add New</button>
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
                <th>Province</th>
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
                if(!empty($_GET["Province"])){
                    list($province, $validation_status) = sanitize($dbCon, $_GET["Province"], "string");
                    $constraint.=" AND Province LIKE '%".$province."%'";
                }

                $pageStart = ($pageNum-1) * $maxRows;
 
                $columns = "Id, Name, Province";
                $limit= "$pageStart, $maxRows";

                $resultSet = $city->get_data($columns, $constraint, $limit);
                if ($resultSet){
                    while($row = $resultSet->fetch_array()){
                        echo("
                        <tr>
                        <td>".$row["Name"]."</td>
                        <td>".$row["Province"]."</td>
                        <td style='text-align: center;'><a href='index.php?page=city_detail&mode=update&id=".$row["Id"]."'><button class='btn-sm btn-primary'><i class='bi bi-pencil-square'></i></button></a></td>
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
    $resCount=$city->get_data($columns, $constraint);
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
                    <a class="page-link" href="<?php if($pageNum<=1){ echo '#';} else { echo "?page=cities_list&pageNum=" . ($pageNum-1);} ?>">Previous</a>
                </li>   

                <?php for ($i=1; $i<=$totalPages;$i++){?>
                    <li class="page-item <?php if ($pageNum==$i) {echo 'active';}?>">
                        <a class="page-link" href="?page=cities_list&pageNum=<?php echo($i);?>"><?php echo($i);?></a>
                    </li>
                <?php }?>
                
                    <li class="page-item <?php if($pageNum>=$totalPages){ echo 'disabled';}?>">
                    <a class="page-link" href="<?php if($pageNum>=$totalPages){ echo '#';} else { echo "?page=cities_list&pageNum=" . ($pageNum + 1);}?>">Next</a>
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