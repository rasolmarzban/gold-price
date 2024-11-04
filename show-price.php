<?php
defined('ABSPATH') || exit;

class ShowGoldPrice
{
    public function __construct()
    {
        // Hook into WooCommerce before displaying the single product
        add_action('woocommerce_before_single_product', [$this, 'init']);
        // Register the custom shortcode
        add_shortcode('product_gold_price', [$this, 'gold_price_shortcode']);
        //add custome script for getting variation id by selected product
        add_action('wp_enqueue_scripts', [$this, 'enqueue_show_price_scripts']);
        add_action('wp_ajax_show_price_variation', [$this, 'show_price_variation_script']);
        add_action('wp_ajax_nopriv_show_price_variation', [$this, 'show_price_variation_script']);
    }

    // Initialize function to check product details
    public function init()
    {
        global $product;

        // Check if $product is set and is an instance of WC_Product
        if (!isset($product) || !$product instanceof WC_Product) {
            return; // Exit if no valid product
        }

        $product_id = $product->get_id();
        $is_preorder = get_post_meta($product_id, '_stock_status', true);

        // Get the main product's variable information
        $product_var = wc_get_product($product_id);
        if ($product_var) {
            $variation_ids = $product_var->get_children(); // Get all variation IDs
            $is_var_preorder = false;

            // Loop through each variation to check for preorder status
            foreach ($variation_ids as $variation_id) {
                $is_var_preorder = get_post_meta($variation_id, '_stock_status', true) == 'onbackorder' ? true : $is_var_preorder;
            }

            // Add custom price filter if the product or any variation is on backorder
            if (is_single() || is_shop()) {
                if ($is_preorder == 'onbackorder' || $is_var_preorder) {
                    add_filter('woocommerce_get_price_html', [$this, 'custom_price_text'], 10, 2);
                }
            }
        }
    }

    public function custom_price_text($price, $product)
    {
        return $this->get_price_text($product);
    }

    // Method to generate the price text based on the product
    public function get_price_text($product)
    {
        $product_id = $product->get_id();
        $regular_price = floatval(get_post_meta($product_id, '_regular_price', true));
        $price_after_discount  = floatval(0.1 * $regular_price);
        //error_log($price_after_discount);
        $pre_order_fee = floatval($regular_price - $price_after_discount);

        $before_text = '<span class="before-price">مبلغ پیش پرداخت: </span>';

        // Check if it's a variable product
        if ($product->is_type('variable')) {
            $variation_ids = $product->get_children(); // Get all variation IDs
            if (!empty($variation_ids)) {
                // Assume the first variation for demonstration
                $variation_id = $variation_ids[0];

                $regular_var_price = floatval(get_post_meta($variation_id, '_regular_price', true));
                $price_var_after_discount  = floatval(0.1 * $regular_var_price);
                //error_log($price_after_discount);
                $pre_var_order_fee = $regular_var_price - $price_var_after_discount;

                return $before_text . number_format($pre_var_order_fee, 0, ',', '.') . ' تومان ';
            }
        }

        // For simple products
        return $before_text . number_format($pre_order_fee, 0, ',', '.') . ' تومان ';
    }

    // Shortcode function to display the price
    public function gold_price_shortcode($atts)
    {
        global $product;

        // Check if there's a product context
        if (!$product instanceof WC_Product) {
            return 'No product found.';
        }
        return $this->get_price_text($product);
    }
    public function show_price_variation_script()
    {
        $variation_ajax_id = intval($_POST['selected_id']);
        return $variation_ajax_id;
    }
    public function enqueue_show_price_scripts()
    {
        wp_enqueue_script('show-gold-price-ajax', GLP_URL . 'assets/js/show-gold-price-script.js', array('jquery'), null, true);
        wp_localize_script('show-gold-price-ajax', 'ajaxurl', admin_url('admin-ajax.php'));
    }
}

// Create an instance of the class
new ShowGoldPrice();
