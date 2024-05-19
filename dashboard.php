<script src="Scriptfiles/dashboard.js"></script> <!-- Loads the dashboard script at beginning for proper functionality -->

<?php
global $conn;

include "config/util.php";

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
        viewPage('#<?php echo $page;?>')
    </script>
    <?php
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("location:login.php");
}


// Food List - Add item to food list
if (isset($_POST['add']) && isset($_POST['name']) && isset($_POST['calories']) && isset($_POST['carbohydrates']) && isset($_POST['fat']) && isset($_POST['protein'])) {
    $name = $_POST['name'];
    $calories = $_POST['calories'];
    $carbohydrates = $_POST['carbohydrates'];
    $fat = $_POST['fat'];
    $protein = $_POST['protein'];

    $pattern = '/^\d+(\.\d+)?$/';

    if (!preg_match($pattern, $calories) || !preg_match($pattern, $carbohydrates) || !preg_match($pattern, $fat) || !preg_match($pattern, $protein)) {
        $insert_output = failed("Macronutrients should only contain numbers.");
    } else {
        $insert_result = $conn->query(
            "INSERT INTO `food_items` (`user_id`, `name`, `calories`, `carbohydrates`, `fat`, `protein`) VALUES('$user_id','$name','$calories','$carbohydrates','$fat','$protein')"
        );

        if ($insert_result) {
            $insert_output = completed("Successfully added food item.");
        } else {
            $insert_output = failed("This food already exists.");
        }
    }
}


// Food List - Delete item from food list
if (isset($_GET['delete'])) {
    $name = $_GET['delete'];

    $delete_result = $conn->query("DELETE FROM `food_items` WHERE user_id='$user_id' AND name='$name'");

    if ($delete_result) {
        header("location:dashboard.php?page=food-list");
    }
}


// Food list - Enable editing input values
if (isset($_GET['edit'])) {
    $name = $_GET['edit'];

    $select_item_query = $conn->query("SELECT * FROM `food_items` WHERE user_id='$user_id' AND name='$name'");

    if ($select_item_query) {
        $row = mysqli_fetch_assoc($select_item_query); ?>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#edit-name").attr("value", '<?php echo $row['name']?>');

                $("#edit-calories").attr("readonly", false).show()
                    .attr("value", '<?php echo $row['calories']?>');

                $("#edit-carbohydrates").attr("readonly", false)
                    .attr("value", '<?php echo $row['carbohydrates']?>');

                $("#edit-fat").attr("readonly", false)
                    .attr("value", '<?php echo $row['fat']?>');

                $("#edit-protein").attr("readonly", false)
                    .attr("value", '<?php echo $row['protein']?>');
            });
        </script>

        <?php

    }

}


// Food List - Edit item on food list
if ((isset($_POST['edit-confirm']) && isset($_POST['name']) && isset($_POST['calories']) && isset($_POST['carbohydrates']) && isset($_POST['fat']) && isset($_POST['protein']))) {
    $name = $_POST['name'];
    $calories = $_POST['calories'];
    $carbohydrates = $_POST['carbohydrates'];
    $fat = $_POST['fat'];
    $protein = $_POST['protein'];

    $pattern = '/^\d+(\.\d+)?$/';

    if (!preg_match($pattern, $calories) || !preg_match($pattern, $carbohydrates) || !preg_match($pattern, $fat) || !preg_match($pattern, $protein)) {
        $insert_output = failed("Macronutrients should only contain numbers.");
    }

    $update_result = $conn->query(
        "UPDATE `food_items` SET name='$name', calories='$calories', carbohydrates='$carbohydrates', fat='$fat', protein='$protein' WHERE user_id='$user_id' AND name='$name'"
    );

    if ($update_result) {
        $edit_output = completed("Successfully edited the food item");
    } else {
        $edit_output = failed("There was an error while editing this food item.");
    }
}


// Food List - Add to Today's List
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add-todays-list']) && isset($_POST['checkbox'])) {
    $checkbox = $_POST['checkbox']; // Get all checkbox elements

    foreach ($checkbox as $name) {
        $select_item_query = $conn->query("SELECT * FROM food_items WHERE user_id='$user_id' AND name='$name'");

        if ($select_item_query) {
            $row = mysqli_fetch_assoc($select_item_query);
            $name = $row['name'];
            $calories = $row['calories'];
            $carbohydrates = $row['carbohydrates'];
            $fat = $row['fat'];
            $protein = $row['protein'];

            $insert_query = $conn->query(
                    "INSERT INTO todays_items (user_id, name, calories, carbohydrates, fat, protein) VALUES ('$user_id','$name','$calories','$carbohydrates','$fat','$protein')");

            if ($insert_query) {
                $add_to_todays_list_output = completed("Successfully added items to today's list.");
            } else {
                $add_to_todays_list_output = failed("There was an error while adding the following items to today's list.");
            }

        } else {
            $add_to_todays_list_output = failed("There was an error while adding the following items to today's list.");
        }
    }
}


// Today's List - Remove from Today's List
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove-todays-items']) && isset($_POST['checkbox'])) {
        $checkbox = $_POST['checkbox']; // Get all checkbox elements

        foreach ($checkbox as $name) {
            $remove_query = $conn->query("DELETE FROM todays_items WHERE user_id='$user_id' AND name='$name'");

            if ($remove_query) {
                $remove_from_todays_list_output = completed("Successfully removed items from today's list.");
            } else {
                $remove_from_todays_list_output = failed("There was an error while removing the following items from today's list.");
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="Scriptfiles/dashboard.js"></script>
</head>
<body>
<div class="head">
    <div class="date-time-box">
        <h2 id="date-time"></h2>
    </div>
</div>
<div class="box">
    <nav>
        <ul>
            <li>
                <a href="#" class="logo">
                    <img src="media/dashboard_logo.png" alt="">
                    <span class="nav-item">FoodTracker</span>
                </a>
            </li>
            <li>
                <a id="dashboard-button" class="selected sidebar-item" href="dashboard.php?page=dashboard">
                    <span class="side-item material-icons-sharp">home</span>
                    <span class="nav-item">Dashboard</span>
                </a>
            </li>
            <li>
                <a id="food-list-button" class="sidebar-item" href="dashboard.php?page=food-list">
                    <span class="side-item material-icons-sharp">fastfood</span>
                    <span class="nav-item">Food List</span>
                </a>
            </li>
            <li>
                <a id="todays-list-button" class="sidebar-item" href="dashboard.php?page=todays-list">
                    <span class="side-item material-icons-sharp">calendar_today</span>
                    <span class="nav-item">Today</span>
                </a>
            </li>
            <li>
                <a id="charts-button" class="sidebar-item" href="dashboard.php?page=charts">
                    <span class="side-item material-icons-sharp">bar_chart</span>
                    <span class="nav-item">Chart</span>
                </a>
            </li>
            <li>
                <a class="sidebar-item" href="dashboard.php?logout=true">
                    <span class="side-item material-icons-sharp">exit_to_app</span>
                    <span class="nav-item">Log out</span>
                </a>
            </li>
        </ul>
    </nav>

    <div id="dashboard">
        <h1 id="welcome-back">Welcome back <?php echo $user_name ?>!</h1>
        <p class="paragraph">FoodTracker is a premium diet dashboard, giving you insight into the nutritional information
            of the food you eat, as well as a daily plan for your meals. You can navigate the dashboard
            on the left of the screen.</p>
    </div>

    <div id="food-list">
        <div id="food-content">
            <div id="left-side">
                <div class="form-box">
                    <form id="add-item-form" method="post" action="dashboard.php?page=food-list">
                        <input type="text" name="name" value="" placeholder="Name">
                        <input type="text" name="calories" value="" placeholder="Calories">
                        <input type="text" name="carbohydrates" value="" placeholder="Carbohydrates">
                        <input type="text" name="fat" value="" placeholder="Fat">
                        <input type="text" name="protein" value="" placeholder="Protein">
                        <br>
                        <?php echo $insert_output; ?>
                        <br>
                        <input id="add-food-button" class="modify-button" type="submit" name="add" value="Add Item">
                    </form>
                </div>

                <div class="form-box">
                    <form id="edit-item-form" method="post" action="dashboard.php?page=food-list">
                        <input id="edit-name" readonly="readonly" type="text" name="name" value="" placeholder="Name">
                        <input id="edit-calories" readonly="readonly" type="text" name="calories" value=""
                               placeholder="Calories">
                        <input id="edit-carbohydrates" readonly="readonly" type="text" name="carbohydrates" value=""
                               placeholder="Carbohydrates">
                        <input id="edit-fat" readonly="readonly" type="text" name="fat" value="" placeholder="Fat">
                        <input id="edit-protein" readonly="readonly" type="text" name="protein" value=""
                               placeholder="Protein">
                        <br>
                        <?php echo $edit_output; ?>
                        <br>
                        <input id="edit-food-button" class="modify-button" type="submit" name="edit-confirm"
                               value="Edit Item">
                    </form>
                </div>
            </div>

            <div id="right-side">
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
                                    <td colspan="8" class="light-red">No food items added!</td>
                                </tr>
                                <?php
                            }

                            if ($select_output) {
                                while ($row = mysqli_fetch_assoc($select_output)) { ?>
                                    <tr class="food-table-item">
                                        <td><input type="checkbox" name="checkbox[<?php echo $row['name']; ?>]"
                                                   value="<?php echo $row['name']; ?>"/></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['calories']; ?></td>
                                        <td><?php echo $row['carbohydrates']; ?>g</td>
                                        <td><?php echo $row['fat']; ?>g</td>
                                        <td><?php echo $row['protein']; ?>g</td>
                                        <td>
                                            <a class="edit-button button"
                                               href="dashboard.php?page=food-list&edit=<?php echo $row['name']; ?>"><span
                                                        class="side-item material-icons-sharp">edit</span></a>
                                            <a class="modify-button red button"
                                               href="dashboard.php?page=food-list&delete=<?php echo $row['name']; ?>"><span
                                                        class="side-item material-icons-sharp">delete</span></a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            <input id="add-todays-list" class="button" type="submit" name="add-todays-list"
                                   value="Added Checked Items to Today's List">
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
                                <td colspan="6" class="light-red">No food items added!</td>
                            </tr>
                            <?php
                        }

                        while ($row = mysqli_fetch_assoc($select_output)) { ?>
                            <tr class="food-table-item">
                                <td><input type="checkbox" name="checkbox[<?php echo $row['name']; ?>]"
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
                        <td colspan="2">Totals</td>
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
            <canvas id="macronutrients" width="800" height="800"></canvas>
            <script>
                var totalCalories = <?php echo $total_calories; ?>;
                var totalCarbs = <?php echo $total_carbs; ?>;
                var totalFat = <?php echo $total_fat; ?>;
                var totalProtein = <?php echo $total_protein; ?>;

                const totalMacronutrients = totalCarbs + totalFat + totalProtein;
                const proteinPercentage = (totalProtein / totalMacronutrients) * 100;
                const carbsPercentage = (totalCarbs / totalMacronutrients) * 100;
                const fatPercentage = (totalFat / totalMacronutrients) * 100;

                const labels = ["Protein", "Carbohydrates", "Fat"];
                const percentages = [proteinPercentage, carbsPercentage, fatPercentage];
                const colours = ["Red", "Blue", "Green"];

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
                                text: "Macronutrient Distribution"
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