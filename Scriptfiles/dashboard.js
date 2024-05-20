//A function to display the page properly
function viewPage(page) {
    setTimeout(function () {
        showHide(page);
    }, 55); // Creates a delay so the elements dont show up by accident

    showHide(page);
}

//A function to hide and show the respect elements and their pages depending on what is clicked by the user
function showHide(page) {
    $(document).ready(function () {
        $("#dashboard").hide();
        $("#dashboard-button").removeClass('selected');

        $("#food-list").hide();
        $("#food-list-button").removeClass('selected');

        $("#todays-list").hide();
        $("#todays-list-button").removeClass('selected');

        $("#charts").hide();
        $("#charts-button").removeClass('selected');

        $(page).show();
        $(page + "-button").addClass('selected');
    });
}


//A function to display the date and time at the top of the dashboard page
window.onload = insertDateTime();

$(document).ready(function () {
    // Reload food table with new values
    $(document).on('click', '.modify-button', function () {
        $.ajax({
            url: '../dashboard.php',
            method: "GET",
            dataType: 'json',
            success: function (response) {
                $('#food-table').html(response);
            }
        });
    });
});


// Actually updates and shows the date and time accordingly and properly
function insertDateTime() {
    setTimeout(function () {
        function updateDateTime() {
            const dateTimeElement = document.getElementById("date-time");
            const options = {year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric'};
            const date = new Date();

            dateTimeElement.textContent = date.toLocaleDateString('en-US', options);
        }

        updateDateTime();

        setInterval(updateDateTime, 1000);
    }, 50);
}
