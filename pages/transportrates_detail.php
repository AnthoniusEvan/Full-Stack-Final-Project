<?php
defined('siteToken') or die('Restricted Access');


if (!empty($_GET["mode"])) {
    $mode = $_GET["mode"];
} else {
    die('Incorrect Access');
}

if (!file_exists('./classes/transport_Rate.php')) {
    die("Class transportRate Not Found");
}
require_once('./classes/transport_Rate.php');
$transportRate = new TransportRate($dbCon);
?>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 card-header">
        <h1 class="h3 mb-0 text-gray-800">Transport Rate - 
        <?php
            $modeText = "";
            switch ($mode) {
                case "insert":
                    $modeText = "Add New";
                    echo($modeText);
                    break;
                case "update":
                    $modeText = "Update";
                    $cityOrigin = $_GET["CityOrigin"];
                    $cityDestination = $_GET["CityDestination"];
                    $cageId = $_GET["CageId"];
                    $col = "origin.name, destination.name, c.name, t.Rate, t.LastModifier, t.LastUpdateTime";
                    $condition = "origin.name='$cityOrigin' AND destination.name='$cityDestination' AND c.name='$cageId'";
                    $resultSet = $transportRate->get_data($col, $condition, 1);
                    if ($resultSet) {
                        $row = $resultSet->fetch_array();
                    } else {
                        die('Incorrect transport rate data');
                    }
                    echo($modeText);
                    break;
                default:
                    die('Incorrect Access');
            }
        ?></h1>

        <?php
            // if ($mode == "update" && $row["LastModifier"]!=""){
            //     $resultSet = $staff->get_inner_join_data("s.Name as name", "s.Id=".$row['LastModifier'], 1);
            //     if ($resultSet){
            //         $modifier=$resultSet->fetch_array();
            //     }
            //     else{
            //         die('Incorrect modifier ID');
            //     }
            //     $timestamp = strtotime($row['LastUpdateTime']);

            //     echo('<span class="badge bg-light-info">Last modified by '.$modifier['name'].' at '.date("d/m/y H:i", $timestamp).'</span><div style="width:5%"></div>');
            // }
            
        ?>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=transportRates_list">Transport Rates</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo($modeText) ?></li>
        </ol>
    </div>

    <!-- Input Data -->
    <div class="row card-body" style="padding-bottom:20px;">
        <div class="col-lg-12">
            <form id="rateForm" enctype="multipart/form-data" method="POST" action="index.php?page=transportRates_list">
                <input type="hidden" id="mode" name="mode" value="<?php echo($mode) ?>">
                <?php
                if ($mode == "update") {
                    echo("<input type='hidden' id='CityOrigin' name='CityOrigin' value='$cityOrigin'>");
                    echo("<input type='hidden' id='CityDestination' name='CityDestination' value='$cityDestination'>");
                    echo("<input type='hidden' id='CageId' name='CageId' value='$cageId'>");
                }
                ?>  
                <div class="form-group" id = "txtCityOri" name = "txtCityOri">
                <label for="cityOrigin">City Origin</label>
                <select class="select2-single form-control" name="cityOrigin" id="cityOrigin" required>
                    <option value="">Select a City Origin</option>
                    <?php
                    $q = "SELECT id, name FROM city";
                    $resCities = $dbCon->query($q);
                    if ($resCities) {
                    while ($rCity = $resCities->fetch_array()) {
                        echo("<option value='" . $rCity['id'] . "'");
                        if ($mode=="update" && $cityOrigin == $rCity['name']) echo(" selected");
                        echo(">" . $rCity['name'] . "</option>");
                    }
                    }
                    ?>
                </select>
                </div>

                <div class="form-group" id = "txtCityDes" name = "txtCityDes">
                <label for="cityDestination">City Destination</label>
                <select class="select2-single form-control" name="cityDestination" id="cityDestination" required>
                    <option value="">Select a City Destination</option>
                    <?php
                    if (!empty($_GET["cityDestination"])) {
                    list($cityDestination, $validation_status) = sanitize($dbCon, $_GET["cityDestination"], "int");
                    if (!$validation_status) {
                        $cityDestination = "";
                    }
                    }
                    $resCities->data_seek(0);
                    while ($rCity = $resCities->fetch_array()) {
                    echo("<option value='" . $rCity['id'] . "'");
                    if ($mode=="update" && $cityDestination == $rCity['name']) echo(" selected");
                    echo(">" . $rCity['name'] . "</option>");
                    }
                    ?>
                </select>
                </div>

                <div class="form-group" id = "txtCage" name = "txtCage"> 
                <label for="cageId">Cage</label>
                <select class="select2-single form-control" name="cageId" id="cageId" required>
                    <option value="">Select a Cage</option>
                    <?php
                    if (!empty($_GET["cageId"])) {
                    list($cageId, $validation_status) = sanitize($dbCon, $_GET["cageId"], "int");
                    if (!$validation_status) {
                        $cageId = "";
                    }
                    }

                    $q = "SELECT id, name FROM cage";
                    $resCages = $dbCon->query($q);
                    if ($resCages) {
                        while ($rCage = $resCages->fetch_array()) {
                            echo("<option value='".$rCage['id']."'");
                            if ($mode == "update" && $cageId == $rCage['name']) echo(" selected");
                            echo(">".$rCage['name']."</option>");
                        }
                    }
                    ?>
                </select>
                </div>


                <div class="form-group mb-3">
                    <label for="Rate">Rate</label>
                    <input class="form-control" type="number" step="0.01" id="Rate" name="Rate" required value="<?php if ($mode == "update") echo($row["Rate"]); ?>">
                </div>
                <!-- <?php if ($mode == "update") { ?>
                    <div class="form-group mb-3">
                        <label for="LastUpdateTime">Last Update Time</label>
                        <input class="form-control" type="text" id="LastUpdateTime" name="LastUpdateTime" readonly value="<?php echo($row["LastUpdateTime"]); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="LastModifier">Last Modifier</label>
                        <input class="form-control" type="text" id="LastModifier" name="LastModifier" readonly value="<?php echo($modifier['name']); ?>">
                    </div>
                <?php } ?> -->


                <div class="row" style="margin-top: 30px">
                    <div class="col-sm-4">
                        <a href="index.php?page=transportRates_list"><button type="button" class="btn btn-warning">Cancel</button></a>
                    </div>
                    <div class="col-sm-4" style="text-align:center;">
                        <?php
                        if ($mode == "update") {
                        ?>
                            <div class="modal-danger me-1 mb-1 d-inline-block">
                                <!-- Button trigger for danger theme modal -->
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#danger">Delete</button>
                                <div class="modal fade text-left" id="danger" tabindex="-1" role="dialog" aria-labelledby="myModalLabel120" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger">
                                                <h5 class="modal-title white" id="myModalLabel120">Delete this rate ?</h5>
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i data-feather="x"></i></button>
                                            </div>
                                            <div class="modal-body" style="text-align:justify;">
                                                Deleting a transport rate related to other data might cause an error. Would you like to continue?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger ml-1" name="delete" value="Delete">Yes</button>
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
                        <button type="submit" id="saveBtn"class="btn btn-primary" name="save" value="Save">
                            <?php
                            switch ($mode) {
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
