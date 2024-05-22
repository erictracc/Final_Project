<!-- Loads the dashboard script at beginning for proper functionality -->
<script src="Scriptfiles/dashboard.js"></script>


<?php
global $conn;

include "config/util.php"; //has database connections, configurations, and functions for completed, failed, and password hash

session_start();

// Redirect user if they do not select a page or enter an invalid url
if (str_ends_with($_SERVER['REQUEST_URI'], "php") || !str_contains($_SERVER['REQUEST_URI'], "dashboard.php?page=")) {
    header("location:dashboard.php?page=dashboard");
}

// Redirect user to login page if not logged in
if (!isset($_SESSION["logged_in"])) {
    header("location:login.php");
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


// Initialize output variables
$insert_output = $edit_output = $add_to_todays_list_output = $remove_from_todays_list_output = "";


// Retrieve session variables
$user_id = $_SESSION['id'];
$user_name = $_SESSION['username'];


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


// Add item to the food list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'], $_POST['name'], $_POST['calories'], $_POST['carbohydrates'], $_POST['fat'], $_POST['protein'])) {
    $name = $_POST['name'];
    $calories = $_POST['calories'];
    $carbohydrates = $_POST['carbohydrates'];
    $fat = $_POST['fat'];
    $protein = $_POST['protein'];


    $pattern = '/^\d+(\.\d+)?$/';


    if (!preg_match($pattern, $calories) || !preg_match($pattern, $carbohydrates) || !preg_match($pattern, $fat) || !preg_match($pattern, $protein)) {
        $insert_output = failed("Macronutrient values must be numeric.");
    } else {
        $insert_query = $conn->prepare(
            "INSERT INTO `food_items` (`user_id`, `name`, `calories`, `carbohydrates`, `fat`, `protein`) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insert_query->bind_param('issddd', $user_id, $name, $calories, $carbohydrates, $fat, $protein);

        if ($insert_query->execute()) {
            $insert_output = completed("Food item added successfully.");
        } else {
            $insert_output = failed("The food item already exists.");
        }
    }
}

// Delete item from the food list
if (isset($_GET['delete'])) {
    $name = $_GET['delete'];

    $delete_query = $conn->prepare("DELETE FROM `food_items` WHERE user_id = ? AND name = ?");
    $delete_query->bind_param('is', $user_id, $name);

    if ($delete_query->execute()) {
        header("Location: dashboard.php?page=food-list");
        exit();
    }
}

// Enable editing input values for a food item
if (isset($_GET['edit'])) {
    $name = $_GET['edit'];

    $select_item_query = $conn->prepare("SELECT * FROM `food_items` WHERE user_id = ? AND name = ?");
    $select_item_query->bind_param('is', $user_id, $name);
    $select_item_query->execute();
    $result = $select_item_query->get_result();

    if ($row = $result->fetch_assoc()) { ?>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#edit-name").val('<?php echo $row['name']; ?>');
                $("#edit-calories").prop("readonly", false).val('<?php echo $row['calories']; ?>');
                $("#edit-carbohydrates").prop("readonly", false).val('<?php echo $row['carbohydrates']; ?>');
                $("#edit-fat").prop("readonly", false).val('<?php echo $row['fat']; ?>');
                $("#edit-protein").prop("readonly", false).val('<?php echo $row['protein']; ?>');
            });
        </script>
        <?php
    }
}

// Edit a food item in the food list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit-confirm'], $_POST['name'], $_POST['calories'], $_POST['carbohydrates'], $_POST['fat'], $_POST['protein'])) {
    $name = $_POST['name'];
    $calories = $_POST['calories'];
    $carbohydrates = $_POST['carbohydrates'];
    $fat = $_POST['fat'];
    $protein = $_POST['protein'];

    $pattern = '/^\d+(\.\d+)?$/';

    if (!preg_match($pattern, $calories) || !preg_match($pattern, $carbohydrates) || !preg_match($pattern, $fat) || !preg_match($pattern, $protein)) {
        $edit_output = failed("Please enter numeric values for macronutrients.");
    } else {
        $update_query = $conn->prepare(
            "UPDATE `food_items` SET `calories` = ?, `carbohydrates` = ?, `fat` = ?, `protein` = ? WHERE `user_id` = ? AND `name` = ?"
        );
        $update_query->bind_param('dddiss', $calories, $carbohydrates, $fat, $protein, $user_id, $name);

        if ($update_query->execute()) {
            $edit_output = completed("Food item updated successfully.");
        } else {
            $edit_output = failed("Failed to update the food item.");
        }
    }
}

// Add items to today's list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-todays-list'], $_POST['checkbox'])) {
    $checkbox = $_POST['checkbox'];

    foreach ($checkbox as $name) {
        $select_item_query = $conn->prepare("SELECT * FROM `food_items` WHERE user_id = ? AND name = ?");
        $select_item_query->bind_param('is', $user_id, $name);
        $select_item_query->execute();
        $result = $select_item_query->get_result();

        if ($row = $result->fetch_assoc()) {
            $insert_query = $conn->prepare(
                "INSERT INTO `todays_items` (`user_id`, `name`, `calories`, `carbohydrates`, `fat`, `protein`) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $insert_query->bind_param('issddd', $user_id, $row['name'], $row['calories'], $row['carbohydrates'], $row['fat'], $row['protein']);

            if ($insert_query->execute()) {
                $add_to_todays_list_output = completed("Items added to today's list successfully.");
            } else {
                $add_to_todays_list_output = failed("Error adding items to today's list.");
            }
        } else {
            $add_to_todays_list_output = failed("Error adding items to today's list.");
        }
    }
}

// Remove items from today's list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove-todays-items'], $_POST['checkbox'])) {
    $checkbox = $_POST['checkbox'];

    foreach ($checkbox as $name) {
        $remove_query = $conn->prepare("DELETE FROM `todays_items` WHERE `user_id` = ? AND `name` = ?");
        $remove_query->bind_param('is', $user_id, $name);

        if ($remove_query->execute()) {
            $remove_from_todays_list_output = completed("Items removed from today's list successfully.");
        } else {
            $remove_from_todays_list_output = failed("Error removing items from today's list.");
            error_log("Error removing item '$name': " . $conn->error);
        }
    }
} else {
    $remove_from_todays_list_output = failed("No items selected or invalid request.");
}
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
<div class="head">
    <div class="date-time-box">
        <h2 id="date-time"></h2>
    </div>
</div>

<div class="box">
    <!-- Navigation menu -->
    <nav>
        <ul>
            <li>
                <a href="#" class="dashboard_logo">
                    <img src="media/dashboard_logo.png" alt="The Webpage logo: a bowl of fruits and veggies.">
                    <span class="nav_item">FoodTracker</span>
                </a>
            </li>
            <li>
                <a id="dashboard-button" class="selected sidebar-item" href="dashboard.php?page=dashboard">
                    <span class="side-item material-icons-sharp">home</span>
                    <span class="nav_item">Dashboard</span>
                </a>
            </li>
            <li>
                <a id="food-list-button" class="sidebar-item" href="dashboard.php?page=food-list">
                    <span class="side-item material-icons-sharp">fastfood</span>
                    <span class="nav_item">Food List</span>
                </a>
            </li>
            <li>
                <a id="todays-list-button" class="sidebar-item" href="dashboard.php?page=todays-list">
                    <span class="side-item material-icons-sharp">calendar_today</span>
                    <span class="nav_item">Today</span>
                </a>
            </li>
            <li>
                <a id="charts-button" class="sidebar-item" href="dashboard.php?page=charts">
                    <span class="side-item material-icons-sharp">bar_chart</span>
                    <span class="nav_item">Chart</span>
                </a>
            </li>
            <li>
                <a class="sidebar-item" href="dashboard.php?logout=true">
                    <span class="side-item material-icons-sharp">exit_to_app</span>
                    <span class="nav_item">Log out</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Dashboard section -->
    <div id="dashboard">
        <img class="main_page_logo" src="media/dashboard_logo.png" alt="The Webpage logo: a bowl of fruits and veggies.">
        <h1 class="intro_title">Welcome to FoodTracker, <br><?php echo $user_name ?>.</h1>
        <p class="intro">FoodTracker: Your premium diet dashboard. Get nutritional insights and daily meal plans with ease. Accessible navigation on the left.</p>
        <h1 class="latest_news"><br>Latest news:</h1>
        <p class="news">Version 1.0 offers complete access to all the latest bug fixes and safety features for your security. Happy ease of use!</p>
    </div>

    <!-- Food list section -->
    <div id="food-list">
        <div id="food-content">
            <div id="top">
                <!-- Form to add new food items -->
                <div class="form">
                    <form id="add-item-form" method="post" action="dashboard.php?page=food-list">
                        <input type="text" name="name" value="" placeholder="Name">
                        <input type="text" name="calories" value="" placeholder="Calories">
                        <input type="text" name="carbohydrates" value="" placeholder="Carbohydrates">
                        <input type="text" name="fat" value="" placeholder="Fat">
                        <input type="text" name="protein" value="" placeholder="Protein">
                        <br>
                        <?php echo $insert_output; ?>
                        <br>
                        <input id="add_button" class="modify-button" type="submit" name="add" value="Add Food">
                    </form>
                </div>

                <!-- Form to edit existing food items -->
                <div class="form">
                    <form id="edit-item-form" method="post" action="dashboard.php?page=food-list">
                        <label for="edit-name"></label><input id="edit-name" readonly="readonly" type="text" name="name" value="" placeholder="Name">
                        <label for="edit-calories"></label><input id="edit-calories" readonly="readonly" type="text" name="calories" value=""
                                                                  placeholder="Calories">
                        <label for="edit-carbohydrates"></label><input id="edit-carbohydrates" readonly="readonly" type="text" name="carbohydrates" value=""
                                                                       placeholder="Carbohydrates">
                        <label for="edit-fat"></label><input id="edit-fat" readonly="readonly" type="text" name="fat" value="" placeholder="Fat">
                        <label for="edit-protein"></label><input id="edit-protein" readonly="readonly" type="text" name="protein" value=""
                                                                 placeholder="Protein">
                        <br>
                        <?php echo $edit_output; ?>
                        <br>
                        <input id="edit_button" class="modify-button" type="submit" name="edit-confirm" value="Edit Food">
                    </form>
                </div>
            </div>

            <div id="bottom">
                <!-- Table displaying the list of food items -->
                <div class="food-table-box">
                    <form action="dashboard.php?page=food-list" method="post">
                        <table id="food-table" class="food-table">
                            <thead>
                            <tr>
                                <td>Select</td>
                                <td>Name</td>
                                <td>Calories</td>
                                <td>Carbohydrates</td>
                                <td>Fat</td>
                                <td>Protein</td>
                                <td colspan="2">Action</td>
                            </tr>
                            <?php
                            $select_output = $conn->query("SELECT * FROM `food_items` WHERE user_id='$user_id'");

                            if (mysqli_num_rows($select_output) == 0) { ?>
                                <tr class="food-table-item">
                                    <td colspan="8" class="error">Error: no food items were included.</td>
                                </tr>
                                <?php
                            }

                            if ($select_output) {
                                while ($row = mysqli_fetch_assoc($select_output)) { ?>
                                    <tr class="food-table-item">
                                        <td><input type="checkbox" class="select-checkbox" name="checkbox[<?php echo $row['name']; ?>]"
                                                   value="<?php echo $row['name']; ?>"/></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['calories']; ?></td>
                                        <td><?php echo $row['carbohydrates']; ?>g</td>
                                        <td><?php echo $row['fat']; ?>g</td>
                                        <td><?php echo $row['protein']; ?>g</td>
                                        <td>
                                            <a class="edit_btn" href="dashboard.php?page=food-list&edit=<?php echo $row['name']; ?>"><span class="side-item material-icons-sharp">build</span></a>
                                            <a class="delete_btn" href="dashboard.php?page=food-list&delete=<?php echo $row['name']; ?>"><span class="side-item material-icons-sharp">delete_outline</span></a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            <input id="add_todays_chart" class="button" type="submit" name="add-todays-list"
                                   value="Click Here to Add Selected Items to Today's Chart">
                            </thead>
                        </table>
                    </form>
                </div>

                <div id="add-todays-list-output">
                    <?php echo $add_to_todays_list_output ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's list section -->
    <div id="todays-list">
        <div class="food-table-box">
            <form action="dashboard.php?page=todays-list" method="post">
                <table id="todays-list-table" class="food-table">
                    <thead>
                    <tr>
                        <td>Select</td>
                        <td>Name</td>
                        <td>Calories</td>
                        <td>Carbohydrates</td>
                        <td>Fat</td>
                        <td>Protein</td>
                    </tr>
                    <?php
                    $select_output = $conn->query("SELECT * FROM `todays_items` WHERE user_id='$user_id'");

                    $total_calories = $total_carbs = $total_fat = $total_protein = 0;

                    if ($select_output) {
                        if (mysqli_num_rows($select_output) == 0) { ?>
                            <tr class="food-table-item">
                                <td colspan="6" class="error">No food items added!</td>
                            </tr>
                            <?php
                        }

                        while ($row = mysqli_fetch_assoc($select_output)) { ?>
                            <tr class="food-table-item">
                                <td><input type="checkbox" class="select-checkbox" name="checkbox[<?php echo $row['name']; ?>]"
                                           value="<?php echo $row['name']; ?>"/></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['calories']; ?></td>
                                <td><?php echo $row['carbohydrates']; ?>g</td>
                                <td><?php echo $row['fat']; ?>g</td>
                                <td><?php echo $row['protein']; ?>g</td>
                            </tr>
                            <?php
                            // Calculate totals
                            $total_calories += $row['calories'];
                            $total_carbs += $row['carbohydrates'];
                            $total_fat += $row['fat'];
                            $total_protein += $row['protein'];
                        }
                    }
                    ?>
                    <input id="remove-todays-list" class="button" type="submit" name="remove-todays-items"
                           value="Remove Checked Items From Today's List">
                    </thead>
                    <tfoot>
                    <tr>
                        <td class="total_calc" colspan="2">Totals</td>
                        <td><?php echo $total_calories; ?></td>
                        <td><?php echo $total_carbs; ?>g</td>
                        <td><?php echo $total_fat; ?>g</td>
                        <td><?php echo $total_protein; ?>g</td>
                    </tr>
                    </tfoot>
                </table>
            </form>

            <div id="remove-todays-items-output">
                <?php echo $remove_from_todays_list_output; ?>
            </div>
        </div>
    </div>

    <div id="charts">
        <div id="chart-box">
            <canvas id="macronutrients" height="400" width="400"></canvas>
            <script>
                // Update chart data
                let totalCalories = <?php echo $total_calories; ?>;
                let totalCarbs = <?php echo $total_carbs; ?>;
                let totalFat = <?php echo $total_fat; ?>;
                let totalProtein = <?php echo $total_protein; ?>;

                const totalMacronutrients = totalCarbs + totalFat + totalProtein;
                const proteinPercentage = (totalProtein / totalMacronutrients) * 100;
                const carbsPercentage = (totalCarbs / totalMacronutrients) * 100;
                const fatPercentage = (totalFat / totalMacronutrients) * 100;

                const labels = ["Protein", "Carbohydrates", "Fat"];
                const percentages = [proteinPercentage, carbsPercentage, fatPercentage];

                // Update chart colors
                const colours = ["#FF5733", "#33FF57", "#3357FF"]; // Updated to hexadecimal color codes

                // Timeout necessary so chart doesn't display when switching pages
                setTimeout(function () {
                    new Chart("macronutrients", {
                        type: "pie",
                        data: {
                            labels: labels,
                            datasets: [{
                                backgroundColor: colours,
                                data: percentages
                            }]
                        },
                        options: {
                            title: {
                                display: true,
                                text: "Updated Macronutrient Chart" // chart title
                            }
                        }
                    });
                }, 500);
            </script>
        </div>
    </div>


</div>
<div class="foot">
    <p class="foot-cont">&copy; 2023 FoodTracker. All rights are reserved.</p>
</div>
</body>
</html>