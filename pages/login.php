<?php
    defined('siteToken') or die('Restricted Access');

    if(!file_exists("./classes/user.php")) {
      die("Class not found");
    }
    require_once("./classes/user.php");
  
    $user = new User($dbCon);
  
    $errorMessage = "";
    if(!empty($_POST["Username"])&& !empty($_POST["Password"])){
      list($username, $validation_status) = sanitize($dbCon, $_POST["Username"], "string");
      list($userPassword, $validation_status) = sanitize($dbCon, $_POST["Password"], "string");
  
      if($user->login($username, $userPassword)){
          $_SESSION['active_user'] = $user;
          header('Location: index.php');
      }
      else{
          $errorMessage = "Invalid username or password";
      }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mazer Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/pages/auth.css">
</head>

<body>
    <div id="auth">

        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="index.html"><img src="assets/images/logo/logo.png" alt="Logo"></a>
                    </div>
                    
                    <?php
                    if($errorMessage!="") {?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <h6><i class="fas fa-ban"></i><b><?php echo($errorMessage);?></b></h6>
                    </div>
                <?php }
                    if(isset($_GET['successMessage'])){?>
                    <div class="alert alert-success alert-dismissible" role="info">
                    <h6><?php echo($_GET['successMessage']);?></b></h6>
                    </div>
                <?php } ?>
                    <h1 class="auth-title">Log in.</h1>
                    <p class="auth-subtitle mb-5">Log in with your data that you entered during registration.</p>

                    <form class="user" method="POST" action="index.php">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" name="Username" id="Username" class="form-control form-control-xl" placeholder="Username">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" name="Password" id="Password" class="form-control form-control-xl" placeholder="Password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <div class="form-check form-check-lg d-flex align-items-end">
                            <input class="form-check-input me-2" type="checkbox" value="" id="flexCheckDefault">
                            <label class="form-check-label text-gray-600" for="flexCheckDefault">
                                Keep me logged in
                            </label>
                        </div>
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Log in</button>
                    </form>
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class="text-gray-600">Don't have an account? <a href="auth-register.html"
                                class="font-bold">Sign
                                up</a>.</p>
                        <p><a class="font-bold" href="auth-forgot-password.html">Forgot password?</a>.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">

                </div>
            </div>
        </div>

    </div>
</body>

</html>