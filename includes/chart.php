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