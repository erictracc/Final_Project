
<?php
global $conn;

include "config/util.php";

$output = "";
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("location:dashboard.php");
}

// Check if login form is submitted
if (isset($_POST['login'])) {

    // Sanitize and retrieve the input data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if username is empty
    if (empty(trim($username))) {
        $output = failed("Username is required.");

        // Check if password is empty
    } else if (empty(trim($password))) {
        $output = failed("Password is required.");
    } else {

        // Query to find the user by username
        $queryUserResult = $conn->query("SELECT * FROM users WHERE username='$username'");

        if ($queryUserResult) {

            // find if the user is real
            if (mysqli_num_rows($queryUserResult) == 0) {
                $output = failed("No account found with that username.");
            } else {

                // find the password if the username is real
                $dbPasswordResult = $conn->query("SELECT password, id FROM users WHERE username='$username'");

                if ($dbPasswordResult) {
                    $row = $dbPasswordResult->fetch_assoc();
                    $database_password = $row['password'];
                    $id = $row['id'];

                    // Verify the password
                    if (password_verify($password, $database_password)) {
                        // Set session variables
                        $_SESSION["logged_in"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;

                        $output = completed("Login successful! Redirecting...");
                        header("location:dashboard.php");
                    } else {
                        $output = failed("The password you entered is incorrect.");
                    }
                }
            }
        } else {
            $output = failed("An error has occurred: " . $conn->error);
        }

        $conn->close();
    }
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MealWorld</title>
    <link rel="stylesheet" href="Stylesheets/login.css">
</head>
<body>
<div id="login-page">

    <!-- Login form section -->
    <div class="login-border">
        <form id="loginSection" action="login.php" method="POST">
            <h1 id="loginTitle">Log In</h1>
            <p id="loginText">Please provide your credentials to log in.</p>
            <label for="username-box"></label><input id="username-box" class="loginPageArea" type="text" name="username" placeholder="Your Username"
                                                     value="<?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>
            <label for="password-box"></label><input id="password-box" class="loginPageArea" type="password" name="password" placeholder="Your Password">
            <br>

            <!-- Link to sign up page -->
            <p>Don't own your own account yet? <a href="signup.php">Register here</a>.</p>
            <br>
            <?php echo $output; ?>

            <!-- Login button -->
            <input id="loginButton" type="submit" name="login" value="Log In">
        </form>
    </div>
</div>
</body>
</html>
