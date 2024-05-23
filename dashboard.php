
<!-- Loads the dashboard script at beginning for proper functionality -->

<script src="Scriptfiles/dashboard.js"></script>

<?php
global $conn;

include "config/util.php"; //has database connections, configurations, and functions for completed, failed, and password hash


session_start();


// Redirect user to login page if not logged in
if (!isset($_SESSION["logged_in"])) {
    header("location:login.php");
}

// Redirect user if they do not select a page or enter an invalid url
if (str_ends_with($_SERVER['REQUEST_URI'], "php") || !str_contains($_SERVER['REQUEST_URI'], "dashboard.php?page=")) {
    header("location:dashboard.php?page=dashboard");
}


// Output initializations
$insert_output = $edit_output = $add_to_todays_list_output = $remove_from_todays_list_output = "";


// Session variables
$user_id = $_SESSION['id'];
$user_name = $_SESSION['username'];

if (isset($_GET['page'])) {
    $page = $_GET['page'];

    // Redirect user if they do not enter a valid page
    if ($page != "dashboard" && $page != "food-list" && $page != "todays-list" && $page != "charts") {
        header("location:dashboard.php?page=dashboard");
    }

    ?>


    <script>
        displayPage('#<?php echo $page;?>')
    </script>


    <?php
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("location:login.php");
    //exit();
}


// Redirect to login page if the user is not logged in
if (!isset($_SESSION["logged_in"])) {
    header("Location: login.php");
    exit();
}


// Validate the 'page' parameter
if (isset($_GET['page'])) {
    $page = $_GET['page'];


    // Redirect to the default page if an invalid page is requested
    if (!in_array($page, ["dashboard", "food-list", "todays-list", "charts"])) {
        header("Location: dashboard.php?page=dashboard");
        exit();
    }

    echo "<script>displayPage('#$page');</script>";
}

include 'tables_config.php';

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="Stylesheets/dashboard.css">
    <script src="Scriptfiles/dashboard.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
</head>
<body>
<!-- Header section displaying the current date and time -->
<?php include 'includes/header.php'; ?>


<div class="box">
    <!-- Navigation menu -->
    <?php include 'navigation_menu.php'; ?>

    <!-- Dashboard section -->
    <?php include 'dashboard_home.php'; ?>

    <!-- Food list section -->
    <?php include 'food-list.php'; ?>

    <!-- Today's list section -->
    <?php include 'todays-list.php'; ?>

    <!-- Chart section -->
    <?php include 'includes/chart.php'; ?>

</div>

<?php include 'includes/footer.php'; ?>

</body>

</html>