<?php
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove-todays-items'])) {
    if (isset($_POST['checkbox'])) {
        $checkbox = $_POST['checkbox'];

        // Check if checkboxes are selected
        if (!empty($checkbox)) {
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
            $remove_from_todays_list_output = failed("No items selected.");
        }
    } else {
        $remove_from_todays_list_output = failed("No checkboxes submitted.");
    }
} else {
    $remove_from_todays_list_output = ""; // No action required when opening the page
}