<?php
// Start the session so we can access the logged in user's data
session_start();

// If the user is not logged in, redirect them to the login page and stop the script
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: signin.php");
    exit;
}

// Load the database connection
require_once "config/database.php";

// Set up empty variables to hold the new password, confirmation and any error messages
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

// Only run the password reset logic if the form has been submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check the new password field is not empty and meets the minimum length requirement
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password must have at least 6 characters.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }

    // Check the confirm password field is not empty and matches the new password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm the password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }

    // Only update the database if both fields passed validation with no errors
    if(empty($new_password_err) && empty($confirm_password_err)){

        // Prepare the SQL update statement to prevent SQL injection
        $sql = "UPDATE users SET password = ? WHERE id = ?";

        if($stmt = mysqli_prepare($link, $sql)){

            // Bind the hashed password and the user ID to the prepared statement
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

            // Hash the new password before storing it and get the user ID from the session
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];

            // Execute the update and log the user out if it was successful
            if(mysqli_stmt_execute($stmt)){
                // Password updated successfully so destroy the session and redirect to the login page
                session_destroy();
                header("location: signin.php");
                exit();
            } else{
                // The SQL query itself failed to execute
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Close the database connection once all processing is done
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | CybAware</title>

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="login-container">

                <?php // Page heading and subtitle shown above the reset password form ?>
                <h1 class="login-title">Reset Password</h1>
                <p class="login-subtitle">Please fill out this form to reset your password.</p>

                <?php // Form that submits back to this same page for processing ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                    <?php // New password field - shows a red error message below if validation failed ?>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input <?php echo (!empty($new_password_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($new_password); ?>">
                        <?php if(!empty($new_password_err)): ?>
                            <span class="error-message"><?php echo $new_password_err; ?></span>
                        <?php endif; ?>
                        <?php // Small hint beneath the field reminding the user of the minimum length requirement ?>
                        <small class="form-hint">Password must be at least 6 characters long.</small>
                    </div>

                    <?php // Confirm password field - shows a red error message if the passwords do not match ?>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input <?php echo (!empty($confirm_password_err)) ? 'error' : ''; ?>">
                        <?php if(!empty($confirm_password_err)): ?>
                            <span class="error-message"><?php echo $confirm_password_err; ?></span>
                        <?php endif; ?>
                    </div>

                    <?php // Submit button to confirm the reset and a cancel link to go back without changing anything ?>
                    <div class="form-buttons">
                        <button type="submit" class="login-btn">Reset Password</button>
                        <a href="welcome.php" class="reset-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <?php // Load the shared footer at the bottom of the page ?>
        <?php include 'includes/footer.php'; ?>

        <?php // Invisible overlay that darkens the page when the mobile menu is open ?>
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <?php // Load the JavaScript file that controls the navigation menu behaviour ?>
    <script src="js/navigation.js"></script>
</body>
</html>