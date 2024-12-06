<?php
require_once('classes/user.php');
session_start();

define('siteToken',1);

if (!file_exists('config.php')){
  die('Restricted Access');
}
require_once('./functions/sanitizers.php');
require_once('config.php');

if (isset($_GET['logout'])){
  session_unset();

  header('Location: index.php?successMessage=Successfully Logged Out!');
}

$waitDuration = 0.1;
$error = "";
if(!isset($_SESSION["last_request_time"])){
$_SESSION["last_request_time"] = time();
}
else{
if (!isset($_GET["successMessage"]) && (time() - $_SESSION["last_request_time"]) < $waitDuration){
    $error = "To frequent requests, please wait for $waitDuration seconds before submitting another request.";
}
else{
    $_SESSION["last_request_time"] = time();
}
}

if ($error != "" && !isset($_GET["success"])){
?>
<div class="alert alert-danger alert-dismissible show fade" style="z-index: 100000; top:0; position: sticky" role="alert">
    <?php echo($error)?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"
        aria-label="Close"></button>
</div>

<?php
}

if (!isset($_SESSION['active_user'])){ 
require_once('pages/login.php');
}
else if (isset($_GET["page"])){
require_once('pages/'.$_GET["page"].".php");
}

$dbCon->close();
?>