<?php
// Global connection variable
global $conn;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include utility functions
include "config/util.php";

$output = "";

// Handle registration form submission
if (isset($_POST['signup'])) { // Changed 'register' to 'signup'
    // Sanitize user inputs
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = $_POST['confirm_password']; // Changed 'confirmPassword' to 'confirm_password'

    // Regular expression to validate username (letters, numbers, and underscores)
    $pattern = "/^[a-zA-Z0-9_]+$/";

    // Check if username matches the pattern
    if (!preg_match($pattern, $username)) {
        $output = failed("Username can only contain letters, numbers, and underscores."); // Changed 'failed' to 'fail'
    } else {
        // Query to check if the username already exists
        $user_query_result = $conn->query("SELECT * FROM users WHERE username='$username'");

        if ($user_query_result) {
            // Check if the username is already taken
            if (mysqli_num_rows($user_query_result) == 0) {
                // If passwords match, hash the password and insert into database
                if ($password == $confirmPassword) {
                    $hash = setPasswordHash($password);

                    // Insert new user into the database
                    $insert_result = $conn->query("INSERT INTO `users` (`username`, `password`) VALUES('$username','$hash')");

                    if ($insert_result) {
                        $output = completed("Account created successfully!<br>Redirecting to login page..."); // Changed 'completed' to 'success'
                        session_start();
                        $_SESSION['username'] = $username;

                        unset($_POST['username']);
                        header("refresh:2; url=login.php");
                    } else {
                        $output = failed("Error creating your account. Please try again later.");
                    }
                } else {
                    $output = failed("Passwords do not match. Please try again.");
                }
            } else {
                $output = failed("An account with this username already exists.");
            }
        } else {
            $output = failed("Error processing your request. Please try again later.");
        }
    }

    // Close database connection
    $conn->close();
}
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="stylesheet" href="../Stylesheets/login.css">
</head>
<body>
<div id="login-page">

    <!-- Sign up form section -->
    <div class="login-border">
        <form id="loginSection" action="signup.php" method="POST">
            <h1 id="loginTitle">Create Your Account</h1>
            <p id="loginText">Complete the form below to register.</p>
            <label for="username-box"></label>
            <input id="username-box" class="loginPageArea" type="text" name="username" placeholder="Enter Username"
                   value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>
            <label for="password-box"></label>
            <input id="password-box" class="loginPageArea" type="password" name="password" placeholder="Enter Password">
            <br>
            <label for="confirm-password-box"></label>
            <input id="confirm-password-box" class="loginPageArea" type="password" name="confirm_password" placeholder="Confirm Password">
            <br>

            <!-- Link to sign in page -->
            <p>Already own your own account? <a href="login.php">Log in here</a>.</p>
            <br>
            <?php echo $output; ?>

            <!-- Register button -->
            <input class="loginButton" type="submit" name="signup" value="Sign Up">
        </form>
    </div>
</div>
</body>
</html>
