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
                    <br><br>
                    <?php echo $insert_output; ?>
                    <br><br>
                    <input id="add_button" class="modify-button" type="submit" name="add" value="Add Food">
                </form>
            </div>

            <!-- Form to edit existing food items -->
            <div class="form">
                <form id="edit-item-form" method="post" action="dashboard.php?page=food-list">
                    <label for="edit-name"></label><input id="edit-name" readonly="readonly" type="text" name="name" value="" placeholder="Name">
                    <label for="edit-calories"></label><input id="edit-calories" readonly="readonly" type="text" name="calories" value="" placeholder="Calories">
                    <label for="edit-carbohydrates"></label><input id="edit-carbohydrates" readonly="readonly" type="text" name="carbohydrates" value="" placeholder="Carbohydrates">
                    <label for="edit-fat"></label><input id="edit-fat" readonly="readonly" type="text" name="fat" value="" placeholder="Fat">
                    <label for="edit-protein"></label><input id="edit-protein" readonly="readonly" type="text" name="protein" value="" placeholder="Protein">
                    <br><br>
                    <?php echo $edit_output; ?>
                    <br><br>
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
                                    <td><input type="checkbox" class="select-checkbox" name="checkbox[<?php echo $row['name']; ?>]" value="<?php echo $row['name']; ?>"/></td>
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
                        </thead>
                    </table>
                    <input id="add_todays_chart" class="button" type="submit" name="add-todays-list" value="Click Here to Add Selected Items to The Today's Chart">
                </form>
            </div>

            <!-- Output section for adding items to today's list -->
            <div id="add-todays-list-output">
                <?php echo $add_to_todays_list_output ?>
            </div>
        </div>
    </div>
</div>
