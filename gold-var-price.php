<?php
require_once 'calculate-gold.php';
class GoldVarPrice extends GetGoldPrice
{
    public $settings = [];
    public $use_api_child;
    public $variation_child_id;
    public $weight_child;
    public function __construct()
    {
        // Add custom fields to variation product data
        add_action('woocommerce_variation_options_pricing', [$this, 'custom_price_calculator_variation_fields'], 10, 3);
        add_action('woocommerce_save_product_variation', [$this, 'save_custom_variation_price_calculator_fields'], 10, 2);
    }

    // Display custom fields for each variation
    public function custom_price_calculator_variation_fields($loop, $variation_data, $variation)
    {
        // Get meta values
        // $use_api = get_post_meta($variation->ID, 'custom_price_calculator_use_api_variation', true);
        // $custom_field_wages = get_post_meta($variation->ID, 'custom_field_wages_variation', true);
        // $custom_field_profit = get_post_meta($variation->ID, 'custom_field_profit_variation', true);
        // $custom_field_tax = get_post_meta($variation->ID, 'custom_field_tax_variation', true);

        $use_api = get_post_meta($variation->ID, 'custom_price_calculator_use_api_variation', true);

        //$this->use_api_checkbox = $use_api;

        $custom_field_wages = floatval(get_post_meta($variation->ID, 'custom_field_wages_variation', true)); // Get as float
        $custom_field_profit = floatval(get_post_meta($variation->ID, 'custom_field_profit_variation', true)); // Get as float
        $custom_field_tax = floatval(get_post_meta($variation->ID, 'custom_field_tax_variation', true)); // Get as float
        // Log the meta values
        // error_log('Variation ID: ' . $variation->ID);
        // error_log('Use API: ' . $use_api);
        // error_log('Custom Field Wages: ' . $custom_field_wages);
        // error_log('Custom Field Profit: ' . $custom_field_profit);
        // error_log('Custom Field Tax: ' . $custom_field_tax);

        woocommerce_wp_checkbox(array(
            'id' => 'custom_price_calculator_use_api_variation_' . $loop,
            'label' => __('Use API for Price', 'woocommerce'),
            'description' => __('Check this box to use the API for fetching the total price.', 'woocommerce'),
            'value' => $use_api,
        ));

        // // Checkbox for using API for price
        // woocommerce_wp_checkbox(array(
        //     'id' => 'custom_price_calculator_use_api_variation_' . $loop,
        //     'label' => __('Use API for Price', 'woocommerce'),
        //     'description' => __('Check this box to use the API for fetching the total price.', 'woocommerce'),
        //     'value' => $use_api,
        // ));

        // Additional input field for wages percentage
        woocommerce_wp_text_input(array(
            'id' => 'custom_field_wages_variation_' . $loop,
            'label' => __('Custom Field Wages (%)', 'woocommerce'),
            'description' => __('Enter the wages as a percentage (e.g., 7 for 7%).', 'woocommerce'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => array('step' => '0.01'),
            'value' => $custom_field_wages,
        ));

        // Additional input field for profit percentage
        woocommerce_wp_text_input(array(
            'id' => 'custom_field_profit_variation_' . $loop,
            'label' => __('Custom Field Profit (%)', 'woocommerce'),
            'description' => __('Enter the profit as a percentage (e.g., 10 for 10%).', 'woocommerce'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => array('step' => '0.01'),
            'value' => $custom_field_profit,
        ));

        // Additional input field for tax percentage
        woocommerce_wp_text_input(array(
            'id' => 'custom_field_tax_variation_' . $loop,
            'label' => __('Custom Field Tax (%)', 'woocommerce'),
            'description' => __('Enter the tax as a percentage (e.g., 10 for 10%).', 'woocommerce'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => array('step' => '0.01'),
            'value' => $custom_field_tax,
        ));
    }

    // Save custom fields and perform calculations on save


    public function save_custom_variation_price_calculator_fields($post_id, $i)
    {

        $get_use_api = isset($_POST['custom_price_calculator_use_api_variation_' . $i]) ? 'yes' : 'no';
        //error_log('Use API checked: ' . $use_api);

        // Use floatval for numeric fields to ensure they are stored correctly
        $custom_field_get_wages = isset($_POST['custom_field_wages_variation_' . $i]) ? floatval($_POST['custom_field_wages_variation_' . $i]) : 0.0;
        $custom_field_get_profit = isset($_POST['custom_field_profit_variation_' . $i]) ? floatval($_POST['custom_field_profit_variation_' . $i]) : 0.0;
        $custom_field_get_tax = isset($_POST['custom_field_tax_variation_' . $i]) ? floatval($_POST['custom_field_tax_variation_' . $i]) : 0.0;

        update_post_meta($post_id, 'custom_price_calculator_use_api_variation', $get_use_api);
        update_post_meta($post_id, 'custom_field_wages_variation', $custom_field_get_wages);
        update_post_meta($post_id, 'custom_field_profit_variation', $custom_field_get_profit);
        update_post_meta($post_id, 'custom_field_tax_variation', $custom_field_get_tax);

        // Now handle the price calculation and assignment logic here
        $product_variation = wc_get_product($i);
        $this->variation_child_id = $product_variation;


        $use_api = get_post_meta($i, 'custom_price_calculator_use_api_variation', true);
        $this->use_api_child = $use_api;
        error_log('teeeeeessssssssst:' . $use_api);


        if ($use_api === 'yes') {
            $weight = $product_variation->get_weight();
            $this->weight_child = $weight;
            // Check if weight is present
            if (!$weight) {
                error_log('Weight not set for variation ID: ' . $post_id);
                return; // Early exit if weight is not set
            }

            if (!$this->fetch_total_price_from_api()) {
                error_log('Failed to fetch total price from API or got invalid response.');
                return; // Exit if total price fetching fails
            }


            // $use_api = get_post_meta($post_id, 'custom_price_calculator_use_api_variation', true);
            $custom_field_wages = floatval(get_post_meta($i, 'custom_field_wages_variation', true)); // Get as float
            $custom_field_profit = floatval(get_post_meta($i, 'custom_field_profit_variation', true)); // Get as float
            $custom_field_tax = floatval(get_post_meta($i, 'custom_field_tax_variation', true)); // Get as float

            // Fetch and convert custom fields to float
            $wages_percentage = $custom_field_wages / 100;
            $profit_percentage = $custom_field_profit / 100;
            $tax_percentage = $custom_field_tax / 100;

            error_log('fetch price : ' . $this->fetch_total_price_from_api());
            error_log('wages percentage : ' . $wages_percentage);

            // Calculate the base price
            $calculation_product_gold = $weight * $this->fetch_total_price_from_api();

            error_log('get weight : ' . $weight);
            error_log('calculation_product_gold =====' . $calculation_product_gold);

            // Calculate wages, profit, and tax based on calculations
            $final_wages = floatval($calculation_product_gold * $wages_percentage);
            $final_profit = floatval(($calculation_product_gold + $final_wages) * $profit_percentage);
            $final_tax = floatval(($final_wages + $final_profit) * $tax_percentage);

            error_log('final_profit: ' . $final_profit);
            error_log('final_wages: ' . $final_wages);
            error_log('final_tax: ' . $final_tax);

            // Final subtotal
            $subtotal = round($calculation_product_gold + $final_wages + $final_profit + $final_tax, -3);

            error_log('subtotal: ' . $subtotal);
            // Set the variation prices

            $product_variation->set_regular_price($subtotal); // Set the regular price
            //$product_variation->set_sale_price(''); // Optionally set a sale price
            //error_log('subtotal: ' . $product_variation->set_regular_price($subtotal));

            $product_variation->save(); // Save the variation




            //Log the saved price
            //error_log('Saved price: ' . round($subtotal) . ' for variation ID: ' . $post_id);
        } else {
            //error_log('API usage not enabled for variation ID: ' . $post_id);
        }
    }

    //     public function get_wages_percent()
    //     {
    //         return $this->get_percent_meta('custom_field_wages');
    //     }

    //     public function get_profit_percent()
    //     {
    //         return $this->get_percent_meta('custom_field_profit');
    //     }

    //     public function get_tax_percent()
    //     {
    //         return $this->get_percent_meta('custom_field_tax');
    //     }

    //     private function get_percent_meta($key)
    //     {
    //         if ($this->use_api_child === 'yes' && $this->weight_child) {
    //             if ($this->fetch_total_price_from_api()) {
    //                 return floatval(get_post_meta($this->variation_child_id, $key, true)) / 100;
    //             }
    //         }
    //         return 0; // Return 0 for insurance against non-valid cases
    //     }

    //     public function calculation_current_product_gold()
    //     {
    //         return $this->weight_child * $this->fetch_total_price_from_api();
    //     }

    //     public function calculation_calculate_wages()
    //     {
    //         return intval($this->calculation_current_product_gold() * $this->get_wages_percent());
    //     }

    //     public function calculation_calculate_profit()
    //     {
    //         return intval(($this->calculation_current_product_gold() + $this->calculation_calculate_wages()) * $this->get_profit_percent());
    //     }

    //     public function calculation_calculate_tax()
    //     {
    //         return intval(($this->calculation_calculate_profit() + $this->calculation_calculate_wages()) * $this->get_tax_percent());
    //     }
}

// Initialize your class
new GoldVarPrice();
