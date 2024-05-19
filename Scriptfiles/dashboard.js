function viewPage(page) {
    setTimeout(function () {
        showHide(page);
    }, 60); // Delay necessary for elements to hide properly after post

    showHide(page);
}

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


// Insert the date and time at the top right of dashboard
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
