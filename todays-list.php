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
            <input id="remove_today_chart" class="button" type="submit" name="remove-todays-items"
                   value="Click here to delete items from the todays chart">
        </form>

        <div id="remove-todays-items-output">
            <?php echo $remove_from_todays_list_output; ?>
        </div>
    </div>
</div>
