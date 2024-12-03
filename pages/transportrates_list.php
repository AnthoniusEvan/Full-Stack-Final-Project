<?php
defined('siteToken') or die('Restricted Access');

if (!file_exists('./classes/transport_rate.php')) {
    die("Class Transporrate Not Found");
}
require_once('./classes/transport_rate.php');

$transporrate = new TransportRate($dbCon);
$errorMsg = "";
$cityOrigin = "";
$cityDestination = "";
$cage = "";
if (!empty($_POST["mode"])) {
    $post_data = array();
    $cityOrigin = $_POST["cityOrigin"];
    if (!empty($cityOrigin)) {
        list($post_data['CityOrigin'], $validation_status) = sanitize($dbCon, $cityOrigin, "int");
    }

    $cityDestination = $_POST["cityDestination"];
    if (!empty($cityDestination)) {
        list($post_data['CityDestination'], $validation_status) = sanitize($dbCon, $cityDestination, "int");
    }

    $cage = $_POST["cageId"];
    if (!empty($cage)) {
        list($post_data['CageId'], $validation_status) = sanitize($dbCon, $cage, "int");
    }

    if (!empty($_POST["Rate"])) {
        list($post_data['Rate'], $validation_status) = sanitize($dbCon, $_POST["Rate"], "int");
    }

    $post_data['LastModifier'] = $_SESSION['active_user']->id;

    $errorMsg = "";

    switch ($_POST["mode"]) {
        case "insert":
            if (empty($_POST["Rate"])) {
                $errorMsg = 'Please enter a transport rate!';
                break;
            }
            if ($transporrate->insert_data($post_data) === FALSE) {
                $errorMsg = "Failed to insert transport rate.";
            }
            break;

        case "update":
            if (!empty($_POST["delete"])) {
                $_POST["mode"] = "delete";
                if ($transporrate->remove_data($cityOrigin, $cityDestination, $cage) === FALSE) {
                    $errorMsg = "Failed to delete transport rate.";
                }
                break;
            }

            if ($errorMsg == "" && $transporrate->update_data($post_data, $cityOrigin, $cityDestination, $cage) === FALSE) {
                $errorMsg = "Failed to update transport rate.";
            }
            break;

        case "delete":
            break;

        default:
            die('Incorrect Access');
    }

    if ($errorMsg == "") {
        ?>
        <div class="alert alert-success alert-dismissible show fade" role="alert">
            <?php echo(" Successfully " . $_POST["mode"] . " transport rate data!"); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
    } else {
        ?>
        <div class="alert alert-danger alert-dismissible show fade" role="alert">
            <?php echo(" Fail to " . $_POST["mode"] . " transport rate data! " . $errorMsg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
    }
}

$pageNum = (isset($_GET["pageNum"]) && is_numeric($_GET["pageNum"])) ? $_GET["pageNum"] : 1;

list($pageNum, $validation_status) = sanitize($dbCon, $pageNum, "int");
if (!$validation_status) {
    $errorMsg = "ERROR - Invalid page number";
}

?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Transport Rates</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Home</a></li>
        <li class="breadcrumb-item active" aria_current="page">Transport Rates</li>
    </ol>
</div>

<!-- Search Criteria -->
<div class="row" style="padding-bottom:20px;">
    <div class="col-lg-12">
        <form method="GET" action="index.php">
            <input type="hidden" id="page" name="page" value="transportrates_list">
            
            <div class="form-group">
                <label for="CityOrigin">City Origin</label>
                <select class="select2-single form-control" name="CityOrigin" id="CityOrigin">
                    <option value="">Select a city</option>
                    <?php
                        if(!empty($_GET["CityOrigin"])){
                            list($city, $validation_status) = sanitize($dbCon, $_GET["CityOrigin"], "int");
                            if(!$validation_status){
                                $cityOri="";
                            }
                        }
                        
                        $q = "SELECT Id, Name FROM city";
                        $res = $dbCon->query($q);
                        if ($res){
                            while ($r=$res->fetch_array()){
                                echo("<option value='".$r['Id']."'");
                                if (isset($cityOri) && $cityOri==$r['Id']) echo(" selected");
                                echo(">".$r['Name']."</option>");
                            }
                        }
                        echo $cityOri;
                    ?>
                </select>
            </div> 
            <div class="form-group">
                <label for="CityDestination">City Destination</label>
                <select class="select2-single form-control" name="CityDestination" id="CityDestination">
                    <option value="">Select a city</option>
                    <?php
                        if(!empty($_GET["CityDestination"])){
                            list($city, $validation_status) = sanitize($dbCon, $_GET["CityDestination"], "int");
                            if(!$validation_status){
                                $cityDes="";
                            }
                        }
                        
                        $q = "SELECT Id, Name FROM city";
                        $res = $dbCon->query($q);
                        if ($res){
                            while ($r=$res->fetch_array()){
                                echo("<option value='".$r['Id']."'");
                                if (isset($cityDes) && $cityDes==$r['Id']) echo(" selected");
                                echo(">".$r['Name']."</option>");
                            }
                        }
                    ?>
                </select>
            </div> 

            <div class="form-group mb-3">
                <label for="rate">Rate</label>
                <input class="form-control mb-3" type="number" placeholder="Search by rate" id="rate" name="rate" <?php if (!empty($_GET["rate"])) echo(" value='" . $_GET["rate"] . "'") ?>>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="col-sm-6" style="text-align:right;">
                    <a href="index.php?page=transportrates_detail&mode=insert">
                        <button type="button" class="btn btn-success">Add New</button>
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
                <th>City Origin</th>
                <th>City Destination</th>
                <th>Cage</th>
                <th>Rate</th>
                <th style="text-align: center;"><i class="bi bi-pencil-square"></i></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $constraint = "1";

            if (!empty($_GET["CityOrigin"])) {
                list($cityOrigin, $validation_status) = sanitize($dbCon, $_GET["CityOrigin"], "int");
                $constraint .= " AND CityOrigin = ".$cityOrigin;
            }
            if (!empty($_GET["CityDestination"])) {
                list($CityDestination, $validation_status) = sanitize($dbCon, $_GET["CityDestination"], "int");
                $constraint .= " AND CityDestination = ".$CityDestination;
            }
            if (!empty($_GET["CageId"])) {
                list($cage, $validation_status) = sanitize($dbCon, $_GET["CageId"], "int");
                $constraint .= " AND cage = ".$cage;
            }
            if (!empty($_GET["rate"])) {
                list($rate, $validation_status) = sanitize($dbCon, $_GET["rate"], "int");
                $constraint .= " AND t.rate = ".$rate;
            }

            $pageStart = ($pageNum - 1) * $maxRows;

            $columns = "Origin.Name AS CityOrigin, Destination.Name AS CityDestination, c.Name AS Cage, t.Rate";
            $limit = "$pageStart, $maxRows";

            $resultSet = $transporrate->get_data($columns, $constraint, $limit);
            
            if ($resultSet) {
                while ($row = $resultSet->fetch_array()) {
                    echo("
                        <tr>
                        <td>" . $row["CityOrigin"] . "</td>
                        <td>" . $row["CityDestination"] . "</td>
                        <td>" . $row["Cage"] . "</td>
                        <td>" . $row["Rate"] . "</td>
                        <td style='text-align: center;'>
                            <a href='index.php?page=transportrates_detail&mode=update&CityOrigin=" . $row["CityOrigin"] . "&CityDestination=" . $row["CityDestination"] . "&CageId=" . $row["Cage"] . "'>
                                <button class='btn-sm btn-primary'><i class='bi bi-pencil-square'></i></button>
                            </a>
                        </td>
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
    $resCount=$transporrate->get_inner_join_data($columns, $constraint);
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
                    <a class="page-link" href="<?php if($pageNum<=1){ echo '#';} else { echo "?page=transportrate_list&pageNum=" . ($pageNum-1);} ?>">Previous</a>
                </li>   

                <?php for ($i=1; $i<=$totalPages;$i++){?>
                    <li class="page-item <?php if ($pageNum==$i) {echo 'active';}?>">
                        <a class="page-link" href="?page=transportrate_list&pageNum=<?php echo($i);?>"><?php echo($i);?></a>
                    </li>
                <?php }?>
                
                    <li class="page-item <?php if($pageNum>=$totalPages){ echo 'disabled';}?>">
                    <a class="page-link" href="<?php if($pageNum>=$totalPages){ echo '#';} else { echo "?page=transportrate_list&pageNum=" . ($pageNum + 1);}?>">Next</a>
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