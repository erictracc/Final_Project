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
            <h1 id="loginTitle">Login</h1>
            <p id="loginText">Please fill in your credentials to login.</p>
            <input id="username-box" class="loginPageArea" type="text" name="username" placeholder="Username"
                   value="<?php echo $_SESSION['username'] ?? ''; ?>">
            <br>
            <input id="password-box" class="loginPageArea" type="password" name="password" placeholder="Password">
            <br>
            <!-- Link to sign up page -->
            <p>Need to create an account? <a href="signUp.php">Sign up now</a>.</p>
            <br>
            <?php echo $output; ?>
            <!-- Login button -->
            <input id="loginButton" type="submit" name="login" value="Login">
        </form>
    </div>
</div>
</body>
</html>



