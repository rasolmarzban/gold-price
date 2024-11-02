<?php

class ShowGoldPrice
{
    public function __construct()
    {
        // Hook into WooCommerce before displaying the single product
        add_action('woocommerce_before_single_product', [$this, 'init']);
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
        $product_id = $product->get_id();
        $subtotal_price = get_post_meta($product_id, 'wp_gold_price_subtotal', true);
        $regular_price = get_post_meta($product_id, '_regular_price', true);

        $before_text = '<span class="before-price">مبلغ پیش پرداخت: </span>';
        $after_text = '<span class="after-price"> قیمت حدودی: </span>';

        // Check if it's a variable product
        if ($product->is_type('variable')) {
            $variation_ids = $product->get_children(); // Get all variation IDs
            if (!empty($variation_ids)) {
                // Assume the first variation for demonstration
                $variation_id = $variation_ids[0];

                $subtotal_var_price = get_post_meta($variation_id, 'wp_gold_price_subtotal', true);
                $regular_var_price = get_post_meta($variation_id, '_regular_price', true);

                return $before_text . number_format($regular_var_price, 0, ',', '.') . ' تومان ' . '</br>' . $after_text . number_format($subtotal_var_price, 0, ',', '.') . ' تومان ';
            }
        }

        // For simple products
        return $before_text . number_format($regular_price, 0, ',', '.') . ' تومان ' . '</br>' . $after_text . number_format($subtotal_price, 0, ',', '.') . ' تومان ';
    }
}

// Create an instance of the class
new ShowGoldPrice();
