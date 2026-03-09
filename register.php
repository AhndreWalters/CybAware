<?php
require_once "config/database.php";
 
$first_name = $last_name = $email = $password = $confirm_password = "";
$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = "";
 
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    if(empty(trim($_POST["first_name"]))){
        $first_name_err = "Please enter your first name.";
    } elseif(!preg_match('/^[a-zA-Z\s]+$/', trim($_POST["first_name"]))){
        $first_name_err = "First name can only contain letters and spaces.";
    } else{
        $first_name = trim($_POST["first_name"]);
    }
    
    if(empty(trim($_POST["last_name"]))){
        $last_name_err = "Please enter your last name.";
    } elseif(!preg_match('/^[a-zA-Z\s]+$/', trim($_POST["last_name"]))){
        $last_name_err = "Last name can only contain letters and spaces.";
    } else{
        $last_name = trim($_POST["last_name"]);
    }
    
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email address.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else{
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    if(empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        
        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssss", $param_first_name, $param_last_name, $param_email, $param_password);
            
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
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
    <title>Sign Up | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="login-container">
                <h1 class="login-title">Create Account</h1>
                <p class="login-subtitle">Please fill this form to create an account.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-input <?php echo (!empty($first_name_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
                            <?php if(!empty($first_name_err)): ?>
                                <span class="error-message"><?php echo $first_name_err; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-input <?php echo (!empty($last_name_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
                            <?php if(!empty($last_name_err)): ?>
                                <span class="error-message"><?php echo $last_name_err; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input <?php echo (!empty($email_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                        <?php if(!empty($email_err)): ?>
                            <span class="error-message"><?php echo $email_err; ?></span>
                        <?php endif; ?>
                        <small class="form-hint">We'll never share your email with anyone else.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input <?php echo (!empty($password_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($password); ?>">
                        <?php if(!empty($password_err)): ?>
                            <span class="error-message"><?php echo $password_err; ?></span>
                        <?php endif; ?>
                        <small class="form-hint">Password must be at least 6 characters long.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input <?php echo (!empty($confirm_password_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($confirm_password); ?>">
                        <?php if(!empty($confirm_password_err)): ?>
                            <span class="error-message"><?php echo $confirm_password_err; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="login-btn">Create Account</button>
                </form>

                <br>

                <div class="login-footer">
                    <p>Already have an account? <a href="login.php" style="color: #1e40af; font-weight: 600; text-decoration: none;">Sign in here</a>.</p>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <script src="js/navigation.js"></script>
</body>
</html>