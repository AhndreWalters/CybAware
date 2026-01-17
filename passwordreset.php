<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: signin.php");
    exit;
}
 
// Include config file
require_once "config/database.php";
 
// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate new password
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password must have at least 6 characters.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm the password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
        
    // Check input errors before updating the database
    if(empty($new_password_err) && empty($confirm_password_err)){
        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
            
            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                header("location: signin.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="login-container">
                <h1 class="login-title">Reset Password</h1>
                <p class="login-subtitle">Please fill out this form to reset your password.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input <?php echo (!empty($new_password_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($new_password); ?>">
                        <?php if(!empty($new_password_err)): ?>
                            <span class="error-message"><?php echo $new_password_err; ?></span>
                        <?php endif; ?>
                        <small class="form-hint">Password must be at least 6 characters long.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input <?php echo (!empty($confirm_password_err)) ? 'error' : ''; ?>">
                        <?php if(!empty($confirm_password_err)): ?>
                            <span class="error-message"><?php echo $confirm_password_err; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="login-btn">Reset Password</button>
                        <a href="welcome.php" class="reset-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <script src="js/navigation.js"></script>
</body>
</html>