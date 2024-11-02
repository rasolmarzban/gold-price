<?php
defined('ABSPATH') || exit;
require_once GLP_DIR . 'fetch-price.php';

function gold_calculator_box()
{
    ob_start();
    $get_gold_price = new GetGoldPrice();
    $gold_price = $get_gold_price->fetch_gold_price(); // Store the fetched gold price
?>
    <h1><?php echo $gold_price ?></h1>
    <div class="wrap">
        <div id="gold-calculator">
            <div class="gold-calculator-title">
                <h3>محاسبه قیمت طلا محصول</h3>
            </div>
            <div class="gold-calculator-form">
                <label class="gold-calculator-label" for="gold-price">قیمت طلا:</label>
                <input class="gold-calculator-input" type="number" id="gold-price" name="gold-price" value="<?php echo $gold_price; ?>" required><br><br>

                <label class="gold-calculator-label" for="weight">وزن:</label>
                <input class="gold-calculator-input" type="number" id="weight" name="weight" required><br><br>

                <label class="gold-calculator-label" for="wages">اجرت ساخت:</label>
                <input class="gold-calculator-input" type="number" id="wages" name="wages" required><br><br>

                <label class="gold-calculator-label" for="profit">سود:</label>
                <input class="gold-calculator-input" type="number" id="profit" name="profit" required><br><br>

                <label class="gold-calculator-label" for="tax">مالیات:</label>
                <input class="gold-calculator-input" type="number" id="tax" name="tax" required><br><br>

                <div id="result" style="margin-top: 20px;"></div>
            </div>
        </div>
    </div>

    <script>
        // Wait for the DOM to load
        document.addEventListener("DOMContentLoaded", function() {
            // Add onchange event listeners to all input fields
            const inputs = document.querySelectorAll('#gold-price, #weight, #wages, #profit, #tax');
            inputs.forEach(input => {
                input.addEventListener('change', calculateResults);
            });

            // Initial calculation
            calculateResults(); // Calculate once on load
        });

        // Function to calculate the results
        // Function to calculate the results
        function calculateResults() {
            const goldPrice = parseFloat(document.getElementById("gold-price").value) || 0;
            const weight = parseFloat(document.getElementById("weight").value) || 0;
            const wages = parseFloat(document.getElementById("wages").value) || 0;
            const profit = parseFloat(document.getElementById("profit").value) || 0;
            const tax = parseFloat(document.getElementById("tax").value) || 0;

            // Example calculation
            const productPrice = weight * goldPrice; // Use the variable instead of PHP echo
            const totalWages = productPrice * (wages / 100);
            const totalProfit = (productPrice + totalWages) * (profit / 100);
            const totalTax = (totalWages + totalProfit) * (tax / 100);
            const finalPrice = productPrice + totalWages + totalProfit + totalTax;

            // Function to format numbers with commas
            function formatNumber(num) {
                return Math.round(num).toLocaleString(); // Round number and format
            }

            // Function to round to the nearest thousand
            function roundToNearestThousand(num) {
                return Math.round(num / 1000) * 1000;
            }

            // Display results with formatted numbers
            document.getElementById("result").innerHTML = `
        <h4>جزئیات قیمت:</h4>
        <p>قیمت طلا محصول: تومان ${formatNumber(productPrice)}</p>
        <p>اجرت ساخت: تومان ${formatNumber(totalWages)}</p>
        <p>سود: تومان ${formatNumber(totalProfit)}</p>
        <p>مالیات: تومان ${formatNumber(totalTax)}</p>
        <p>قیمت نهایی محصول: تومان ${formatNumber(roundToNearestThousand(finalPrice))}</p>
    `;
        }
    </script>
<?php
    return ob_get_clean();
}

// Shortcode to use in posts/pages
add_shortcode('gold_calculator', 'gold_calculator_box');
function enqueue_custom_styles()
{
    // Ensure your path to the CSS file is correct
    wp_enqueue_style('custom-styles', GLP_URL . 'assets/css/calculator-style.css');
}

add_action('wp_enqueue_scripts', 'enqueue_custom_styles');
