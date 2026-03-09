<?php
require_once "config/database.php";
 
session_start();
 
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}
 
$login_input = $password = "";
$login_err = $password_err = "";
 
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    if(empty(trim($_POST["login_input"]))){
        $login_err = "Please enter your email or name.";
    } else{
        $login_input = trim($_POST["login_input"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($login_err) && empty($password_err)){
        if(filter_var($login_input, FILTER_VALIDATE_EMAIL)){
            $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE email = ?";
        } else {
            $name_parts = explode(' ', $login_input, 2);
            
            if(count($name_parts) == 2){
                $first_name = $name_parts[0];
                $last_name = $name_parts[1];
                $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE first_name = ? AND last_name = ?";
            } else {
                $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE first_name = ? OR last_name = ?";
            }
        }
        
        if($stmt = mysqli_prepare($link, $sql)){
            if(filter_var($login_input, FILTER_VALIDATE_EMAIL)){
                mysqli_stmt_bind_param($stmt, "s", $param_login);
                $param_login = $login_input;
            } elseif(count($name_parts) == 2){
                mysqli_stmt_bind_param($stmt, "ss", $first_name, $last_name);
            } else {
                mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
            }
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $first_name, $last_name, $email, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["first_name"] = $first_name;
                            $_SESSION["last_name"] = $last_name;
                            $_SESSION["full_name"] = $first_name . " " . $last_name;
                            
                            header("location: index.php");
                        } else{
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else{
                    $login_err = "No account found with those credentials.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>Sign In | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="login-container">
                <h1 class="login-title">Sign In</h1>
                <p class="login-subtitle">Enter your email or name and password to login.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label class="form-label">Email or Name</label>
                        <input type="text" name="login_input" class="form-input <?php echo (!empty($login_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($login_input); ?>" placeholder="Enter your email or name">
                        <?php if(!empty($login_err)): ?>
                            <span class="error-message"><?php echo $login_err; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input <?php echo (!empty($password_err)) ? 'error' : ''; ?>" placeholder="Enter your password">
                        <?php if(!empty($password_err)): ?>
                            <span class="error-message"><?php echo $password_err; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="login-btn">Sign In</button>
                    
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="passwordreset.php" style="color: #1e40af; font-weight: 600; text-decoration: none;">Forgot password?</a>
                    </div>
                </form>

                <br>

                <div class="login-footer">
                    <p>Don't have an account? <a href="register.php" style="color: #1e40af; font-weight: 600; text-decoration: none;">Sign up here</a>.</p>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <script src="js/navigation.js"></script>
</body>
</html>