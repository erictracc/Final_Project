
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MealWorld</title>
    <link rel="stylesheet" href="Stylesheets/login.css">
</head>
<body>
<div id="login-page">

    <!-- Sign up form section -->
    <div class="login-border">
        <form id="loginSection" action="signup.php" method="POST">
            <h1 id="loginTitle">Create Your Account</h1>
            <p id="loginText">Complete the form below to register.</p>
            <label for="username-box"></label><input id="username-box" class="loginPageArea" type="text" name="username" placeholder="Enter Username"
                                                     value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>
            <label for="password-box"></label><input id="password-box" class="loginPageArea" type="password" name="password" placeholder="Enter Password">
            <br>
            <label for="confirm-password-box"></label><input id="confirm-password-box" class="loginPageArea" type="password" name="confirm_password"
                                                             placeholder="Confirm Password"><br>

            <!-- Link to sign in page -->
            <p>Already own your own account? <a href="login.php">Log in here</a>.</p>
            <br>
            <?php echo $output; ?>

            <!-- Register button -->
            <input id="loginButton" type="submit" name="signup" value="Signup">
        </form>
    </div>
</div>
</body>
</html>
