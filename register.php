<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
// Load the database connection
require_once "config/database.php";

// Set up empty variables to hold form field values and any error messages
$first_name = $last_name = $email = $password = $confirm_password = "";
$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = "";

// Only run the registration logic if the form has been submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check the first name field is not empty and only contains letters and spaces
    if(empty(trim($_POST["first_name"]))){
        $first_name_err = "Please enter your first name.";
    } elseif(!preg_match('/^[a-zA-Z\s]+$/', trim($_POST["first_name"]))){
        $first_name_err = "First name can only contain letters and spaces.";
    } else{
        $first_name = trim($_POST["first_name"]);
    }

    // Check the last name field is not empty and only contains letters and spaces
    if(empty(trim($_POST["last_name"]))){
        $last_name_err = "Please enter your last name.";
    } elseif(!preg_match('/^[a-zA-Z\s]+$/', trim($_POST["last_name"]))){
        $last_name_err = "Last name can only contain letters and spaces.";
    } else{
        $last_name = trim($_POST["last_name"]);
    }

    // Check the email field is not empty and is a valid email format
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email address.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else{
        // Check the database to make sure this email address is not already registered
        $sql = "SELECT id FROM users WHERE email = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                // If a matching email is found, show an error - otherwise accept it
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                // The SQL query itself failed to execute
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Check the password field is not empty and meets the minimum length requirement
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Check the confirm password field is not empty and matches the password field
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm your password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // Only insert the new user into the database if all five fields passed validation
    if(empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){

        // Prepare the INSERT statement to prevent SQL injection
        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssss", $param_first_name, $param_last_name, $param_email, $param_password);

            // Assign the validated values and hash the password before storing it
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);

            // Execute the insert and redirect to the login page if it was successful
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else{
                // The SQL query itself failed to execute
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Close the database connection once all registration processing is done
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

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        <?php // Side by side layout for the first and last name fields on wider screens ?>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        <?php // On small screens the two name fields stack on top of each other instead ?>
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="login-container">

                <?php // Page heading and subtitle shown above the registration form ?>
                <h1 class="login-title">Create Account</h1>
                <p class="login-subtitle">Please fill in the form below to create your account.</p>

                <?php // Registration form that submits back to this same page for processing ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                    <?php // Two column row containing the first and last name fields side by side ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-input <?php echo (!empty($first_name_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>" placeholder="John">
                            <?php if(!empty($first_name_err)): ?>
                                <span class="error-message"><?php echo $first_name_err; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-input <?php echo (!empty($last_name_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="Doe">
                            <?php if(!empty($last_name_err)): ?>
                                <span class="error-message"><?php echo $last_name_err; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php // Email address field with a privacy hint and error message if validation failed ?>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input <?php echo (!empty($email_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email address">
                        <?php if(!empty($email_err)): ?>
                            <span class="error-message"><?php echo $email_err; ?></span>
                        <?php endif; ?>
                        <?php // Small reassurance note beneath the email field ?>
                        <small class="form-hint">We'll never share your email with anyone else.</small>
                    </div>

                    <?php // Password field with a minimum length hint and error message if validation failed ?>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input <?php echo (!empty($password_err)) ? 'error' : ''; ?>" placeholder="Create a strong password">
                        <?php if(!empty($password_err)): ?>
                            <span class="error-message"><?php echo $password_err; ?></span>
                        <?php endif; ?>
                        <?php // Small hint reminding the user of the minimum password length ?>
                        <small class="form-hint">Must be at least 6 characters long.</small>
                    </div>

                    <?php // Confirm password field with an error message if the passwords do not match ?>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input <?php echo (!empty($confirm_password_err)) ? 'error' : ''; ?>" placeholder="Re-enter your password">
                        <?php if(!empty($confirm_password_err)): ?>
                            <span class="error-message"><?php echo $confirm_password_err; ?></span>
                        <?php endif; ?>
                    </div>

                    <?php // Submit button that creates the account when clicked ?>
                    <button type="submit" class="login-btn">Create Account</button>
                </form>

                <br>

                <?php // Link at the bottom for users who already have an account ?>
                <div class="login-footer">
                    <p>Already have an account? <a href="login.php" style="color: #1e40af; font-weight: 600; text-decoration: none;">Sign in here</a>.</p>
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