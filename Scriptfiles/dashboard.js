
// Function to display the page properly
function displayPage(page) {
    // Delay to prevent accidental element display
    setTimeout(function () {
        toggleVisibility(page);
    }, 90);
    toggleVisibility(page);
}


// Function to hide and show elements and their pages based on user clicks
function toggleVisibility(page) {
    $(document).ready(function () {

        // Hide all pages and remove 'selected' class from buttons
        $("#dashboard").hide();
        $("#dashboard-button").removeClass('selected');

        $("#food-list").hide();
        $("#food-list-button").removeClass('selected');

        $("#todays-list").hide();
        $("#todays-list-button").removeClass('selected');

        $("#charts").hide();
        $("#charts-button").removeClass('selected');


        // Show selected page and add 'selected' class to its button
        $(page).show();
        $(page + "-button").addClass('selected');
    });
}


// Function to display the date and time at the top of the dashboard page
window.onload = includeClock();


$(document).ready(function () {
    // Reload food table with new values
    $(document).on('click', '.modify-button', function () {
        // Utilizing ajax to fetch new data
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


// Function to update and display the date and time properly
function includeClock() {
    setTimeout(function () {
        // Function to update date and time
        function refreshClock() {
            const dateTimeElement = document.getElementById("date-time");
            const dateOptions = { year: 'numeric', month: 'numeric', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: 'numeric', second: 'numeric' };
            const date = new Date();

            // Display formatted date and time including seconds
            dateTimeElement.textContent = date.toLocaleDateString('en-US', dateOptions) + ' ' + date.toLocaleTimeString('en-US', timeOptions);
        }

        // Initial update
        refreshClock();

        // Periodic update every second
        setInterval(refreshClock, 1000);
    }, 50);
}







