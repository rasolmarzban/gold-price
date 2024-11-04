<?php
defined('ABSPATH') || exit;

class AddToCartPreOrder
{
    public function __construct()
    {
        add_action('woocommerce_cart_calculate_fees', [$this, 'add_custom_fee']);
    }

    public function add_custom_fee()
    {
        if (WC()->cart->is_empty()) {
            return; // Early return if the cart is empty
        }

        $total_discount = 0;

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id']; // Get the product ID from the cart item

            $product = wc_get_product($product_id); // Get the product object

            // Check if it's a simple product or a variable product
            if ($product->is_type('variable')) {
                // Loop through variations
                $variations = $product->get_children(); // Get variation IDs
                foreach ($variations as $variation_id) {
                    $is_pre_order_var = get_post_meta($variation_id, '_stock_status', true);
                    if ($is_pre_order_var == 'onbackorder') {
                        $regular_var_price = get_post_meta($variation_id, '_regular_price', true);
                        $discount_var_pre_order = $regular_var_price * 0.1; // 10% discount
                        $total_discount += $discount_var_pre_order * $cart_item['quantity']; // Accumulate discount
                    }
                }
            } elseif ($product->is_type('simple')) {
                // Check if it is a simple product
                $is_pre_order = get_post_meta($product_id, '_stock_status', true);
                if ($is_pre_order == 'onbackorder') {
                    $regular_price = get_post_meta($product_id, '_regular_price', true);
                    $discount_pre_order = $regular_price * 0.1; // 10% discount
                    $total_discount += $discount_pre_order * $cart_item['quantity']; // Accumulate discount
                }
            }
        }

        // Add discount fee if total discount is calculated
        if ($total_discount > 0) {
            wc()->cart->add_fee(__('کسر برای پیش پرداخت:', 'text-domain'), -$total_discount, false);
        }
    }
}

new AddToCartPreOrder();
