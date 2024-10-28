<?php
include_once 'fetch-price.php';

class GoldShortCodes extends GetGoldPrice
{
    public function __construct() // Corrected constructor name
    {
        // Usage: You can use the following in a shortcode or wherever needed
        add_shortcode('gold_price', array($this, 'get_gold_price')); // Callback to the method correctly
        add_shortcode('product_wages', array($this, 'get_product_wages'));
        add_shortcode('product_profit', array($this, 'get_product_profit'));

        // ajax request
        add_action('wp_ajax_get_product_profit', [$this, 'handle_get_product_profit']);
        add_action('wp_ajax_nopriv_get_product_profit', [$this, 'handle_get_product_profit']);

        //add ajax action
        add_action('wp_enqueue_scripts', [$this, 'enqueue_gold_price_shortcode_scripts']);
    }

    public function get_gold_price($atts)
    {
        // Extract parameters from shortcode attributes
        $atts = shortcode_atts(array(
            'show_price' => '1',
            'show_date_time' => '1',
        ), $atts);

        // Get the price from transient or perhaps from another property that the class has
        $geram18_price = $this->fetch_total_price_from_api(); // Ensure this is set properly in your class
        error_log($this->fetch_total_price_from_api());
        $geram18time = get_transient('gold_time_geram18');

        $output = '';

        // Display price if show_price is enabled
        if ($atts['show_price'] == '1' && $geram18_price) {
            $output .= "طلای ۱۸ عیار (گرم): " . esc_html(number_format($geram18_price)) . " تومان";
        }

        // Display date/time if show_date_time is enabled
        if ($atts['show_date_time'] == '1' && $geram18time) {
            $output .= !empty($output) ? ' - ' : ''; // Add separator if price is displayed
            $output .= esc_html($geram18time);
        }

        // If neither price nor date/time is available, show default message
        if (empty($output)) {
            return 'Price data not available.';
        }

        return $output;
    }

    // product get wages price

    public function get_product_wages()
    {

        // Fetch all published product IDs
        $product_ids = $this->get_all_products_ids();

        foreach ($product_ids as $product_id) {


            $custom_field_wages = floatval(get_post_meta($product_id, 'custom_field_wages', true));

            // Get the product object
            $product_var_id = wc_get_product($product_id);

            // Check if the product is a variable product
            if ($product_var_id && $product_var_id->is_type('variable')) {
                $variation_ids = $product_var_id->get_children(); // Get all variation IDs

                // Loop through each variation
                foreach ($variation_ids as $variation_id) {

                    $custom_field_wages = get_post_meta($variation_id, 'custom_field_wages_variation', true);
                    return $custom_field_wages;
                }
            }
            return $custom_field_wages;
        }
    }
    public function get_product_profit()
    {
        // Initialize an array to hold all profits
        $profits = [];

        // Fetch all published product IDs
        $product_ids = $this->get_all_products_ids();

        foreach ($product_ids as $product_id) {
            // Get the product object
            $product = wc_get_product($product_id);

            // Check if the product is a variable product
            if ($product && $product->is_type('variable')) {
                $variation_ids = $product->get_children(); // Get all variation IDs

                // Loop through each variation
                foreach ($variation_ids as $variation_id) {
                    // Get the current variation object
                    $variation = wc_get_product($variation_id);;
                    //var_dump($variation_id);
                    // Get weight and profit for the variation
                    $weight = $variation->get_weight();
                    // $attributes = $variation->get_attributes();

                    // $attributes = $variation->get_attributes();

                    // // Assuming $attributes contains the attribute data
                    // $attributes = $variation->get_attributes();
                    // foreach ($attributes as $attribute_name => $attribute_value) {
                    //     var_dump(sanitize_key($attribute_value));
                    // }

                    //var_dump($default_attributes);

                    $custom_field_profit_variation = get_post_meta($variation_id, 'custom_field_profit_variation', true);

                    // Store variation profits keyed by weight if needed
                    if (!empty($weight)) { // Ensure weight is not empty
                        $profits[$weight] = $custom_field_profit_variation;
                        if (isset($weight))
                            //var_dump($profits[$weight]);
                            echo $profits[$weight];
                        // Debug output

                    }
                }
            } else {
                // For simple products or other types, retrieve profit
                $custom_field_wages = get_post_meta($product_id, 'custom_field_profit', true);
                $profits[$product_id] = $custom_field_wages;
            }
        }

        return $profits; // Return all profits collected
    }

    public function handle_get_product_profit()
    {
        // Check for received product_id
        if (isset($_POST['product_id'])) {
            $product_id = intval($_POST['product_id']);

            // Compute profit using your existing function
            $profits = $this->get_product_profit(); // You might want to modify this to directly return profit for the $product_id
            var_dump($profits);
            // Return profit for the corresponding product_id
            if (isset($profits[$product_id])) {
                echo esc_html($profits[$product_id]);
            } else {
                echo 'No profit data available.';
            }
        }
        wp_die(); // Always include this to properly end the AJAX request
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

    public function enqueue_gold_price_shortcode_scripts()
    {
        wp_enqueue_script('my-ajax-script', GLP_URL . 'assets/js/gold-shortcode-script.js', array('jquery'), null, true);

        // Localize the script to make `ajaxurl` available
        wp_localize_script('my-ajax-script', 'ajaxurl', admin_url('admin-ajax.php'));
    }
}

// Initialize the shortcode class
new GoldShortCodes();
