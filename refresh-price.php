<?php
defined('ABSPATH') || exit;
require_once 'calculate-gold.php'; // Ensure this file is available
require_once 'gold-var-price.php'; // Ensure this file is available

class Refreshed
{
    public function __construct()
    {
        // Hook into the 'init' action to set up scheduled events
        add_action('init', [$this, 'setup_price_refresh_schedule']);

        // Add a filter for custom cron schedules
        add_filter('cron_schedules', [$this, 'add_ten_minutes_cron_schedule']);

        // Hook your custom function to the scheduled event
        add_action('refresh_product_prices_event', [$this, 'refresh_product_prices']);
    }

    public function setup_price_refresh_schedule()
    {
        // Schedule the event if it’s not already scheduled
        if (!wp_next_scheduled('refresh_product_prices_event')) {
            wp_schedule_event(time(), 'ten_minutes', 'refresh_product_prices_event');
        }
    }

    public function add_ten_minutes_cron_schedule($schedules)
    {
        $schedules['ten_minutes'] = array(
            'interval' => 60, // 600 seconds = 10 minutes
            'display' => __('Every 10 Minutes')
        );
        return $schedules;
    }

    public function refresh_product_prices()
    {
        // Fetch all published product IDs
        $product_ids = $this->get_all_products_ids();

        foreach ($product_ids as $product_id) {
            // Call your price update method here for regular prices
            $calculate = new CalculateRegular();
            $calculate->save_custom_price_calculator_field($product_id);

            // Get the product object
            $product_var_id = wc_get_product($product_id);

            // Check if the product is a variable product
            if ($product_var_id && $product_var_id->is_type('variable')) {
                $variation_ids = $product_var_id->get_children(); // Get all variation IDs

                // Loop through each variation
                foreach ($variation_ids as $variation_id) {
                    // Update each variation's price
                    $goldvarprice = new GoldVarPrice();
                    $goldvarprice->save_custom_variation_price_calculator_fields($product_var_id->get_id(), $variation_id);

                    // Log the individual variation being processed
                    //error_log('Updating variation ID: ' . $variation_id);
                }

                // Log after processing all variations for this product
                //error_log('Updated all variations for product ID ' . $product_id . ': ' . implode(', ', $variation_ids));
            }
        }

        // Log the refresh event
        // error_log('Product prices refreshed for products: ' . implode(',', $product_ids));
    }

    public function get_all_products_ids()
    {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'numberposts' => -1 // Get all published products
        );
        $products = get_posts($args);
        return wp_list_pluck($products, 'ID'); // Get an array of product IDs
    }
}

// Initialize the Refreshed class
new Refreshed();
