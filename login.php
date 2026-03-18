<?php
// Load the database connection
require_once "config/database.php";

// Start the session so we can read and write session data
session_start();

// If the user is already logged in, send them to the homepage and stop the script
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

// Set up empty variables to hold the login input, password and any error messages
$login_input = $password = "";
$login_err = $password_err = "";

// Only run the login logic if the form has been submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check that the email or name field is not empty
    if(empty(trim($_POST["login_input"]))){
        $login_err = "Please enter your email or name.";
    } else{
        $login_input = trim($_POST["login_input"]);
    }

    // Check that the password field is not empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Only attempt the database lookup if both fields passed validation
    if(empty($login_err) && empty($password_err)){

        // If the input looks like an email address, search by email
        if(filter_var($login_input, FILTER_VALIDATE_EMAIL)){
            $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE email = ?";
        } else {
            // Split the input into parts to check if a full name was entered
            $name_parts = explode(' ', $login_input, 2);

            // If two parts were given treat them as first and last name
            if(count($name_parts) == 2){
                $first_name = $name_parts[0];
                $last_name = $name_parts[1];
                $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE first_name = ? AND last_name = ?";
            } else {
                // If only one part was given search both the first and last name columns
                $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE first_name = ? OR last_name = ?";
            }
        }

        // Prepare the SQL statement to prevent SQL injection
        if($stmt = mysqli_prepare($link, $sql)){

            // Bind the correct parameters depending on how the user chose to log in
            if(filter_var($login_input, FILTER_VALIDATE_EMAIL)){
                mysqli_stmt_bind_param($stmt, "s", $param_login);
                $param_login = $login_input;
            } elseif(count($name_parts) == 2){
                // Bind first and last name separately for a full name search
                mysqli_stmt_bind_param($stmt, "ss", $first_name, $last_name);
            } else {
                // Bind the single name input to both the first and last name columns
                mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
            }

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                // Check that exactly one matching account was found
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $first_name, $last_name, $email, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){

                        // Verify the submitted password against the hashed password stored in the database
                        if(password_verify($password, $hashed_password)){

                            // Password is correct so store the user's details in the session and redirect to the homepage
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["first_name"] = $first_name;
                            $_SESSION["last_name"] = $last_name;
                            $_SESSION["full_name"] = $first_name . " " . $last_name;

                            header("location: index.php");
                        } else{
                            // Password did not match so show an error on the password field
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else{
                    // No account was found matching the submitted email or name
                    $login_err = "No account found with those credentials.";
                }
            } else{
                // The SQL query itself failed to execute
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Close the database connection once all login processing is done
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

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="login-container">

                <?php // Page heading and subtitle shown above the login form ?>
                <h1 class="login-title">Sign In</h1>
                <p class="login-subtitle">Enter your email or name and password to login.</p>

                <?php // Login form that submits back to this same page for processing ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                    <?php // Email or name input field - shows a red error message below if validation failed ?>
                    <div class="form-group">
                        <label class="form-label">Email or Name</label>
                        <input type="text" name="login_input" class="form-input <?php echo (!empty($login_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($login_input); ?>" placeholder="Enter your email or name">
                        <?php if(!empty($login_err)): ?>
                            <span class="error-message"><?php echo $login_err; ?></span>
                        <?php endif; ?>
                    </div>

                    <?php // Password input field - shows a red error message below if validation failed ?>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input <?php echo (!empty($password_err)) ? 'error' : ''; ?>" placeholder="Enter your password">
                        <?php if(!empty($password_err)): ?>
                            <span class="error-message"><?php echo $password_err; ?></span>
                        <?php endif; ?>
                    </div>

                    <?php // Submit button that triggers the login process ?>
                    <button type="submit" class="login-btn">Sign In</button>

                    <?php // Forgot password link shown beneath the submit button ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="passwordreset.php" style="color: #1e40af; font-weight: 600; text-decoration: none;">Forgot password?</a>
                    </div>
                </form>

                <br>

                <?php // Link at the bottom for users who do not yet have an account ?>
                <div class="login-footer">
                    <p>Don't have an account? <a href="register.php" style="color: #1e40af; font-weight: 600; text-decoration: none;">Sign up here</a>.</p>
                </div>
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