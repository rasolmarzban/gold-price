<?php

use Automattic\WooCommerce\Admin\Marketing\Price;

class CalculateRegular extends GetGoldPrice
{
    public $settings = [];
    public $current_post_id;
    public $product;
    public $weight;
    public $use_api;

    public function __construct()
    {
        // Add checkbox and additional fields to WooCommerce settings
        add_filter('woocommerce_product_options_general_product_data', [$this, 'custom_price_calculator_api_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_price_calculator_fields']);
        add_action('save_post', [$this, 'save_custom_price_calculator_field']);
    }

    public function custom_price_calculator_api_fields()
    {
        // Create input fields for settings
        woocommerce_wp_checkbox([
            'id' => 'custom_price_calculator_use_api',
            'label' => __('Use API for Price', 'woocommerce'),
            'description' => __('Check this box to use the API for fetching the total price.', 'woocommerce'),
        ]);

        $this->add_price_input_field('custom_field_wages', __('Custom Field Wages (%)', 'woocommerce'));
        $this->add_price_input_field('custom_field_profit', __('Custom Field Profit (%)', 'woocommerce'));
        $this->add_price_input_field('custom_field_tax', __('Custom Field Tax (%)', 'woocommerce'));
    }

    private function add_price_input_field($id, $label)
    {
        woocommerce_wp_text_input([
            'id' => $id,
            'label' => $label,
            'description' => __('Enter the value as a percentage (e.g., 7 for 7%).', 'woocommerce'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => ['step' => '0.01'],
        ]);
    }

    public function save_custom_price_calculator_fields($post_id)
    {
        // Save checkbox value
        $use_api = isset($_POST['custom_price_calculator_use_api']) ? 'yes' : 'no';
        update_post_meta($post_id, 'custom_price_calculator_use_api', $use_api);

        // Save custom fields
        $fields = ['custom_field_wages', 'custom_field_profit', 'custom_field_tax'];
        foreach ($fields as $field) {
            $value = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
            update_post_meta($post_id, $field, $value);
        }
    }

    public function save_custom_price_calculator_field($post_id)
    {
        if (get_post_type($post_id) !== 'product') {
            return;
        }

        $this->current_post_id = $post_id;
        $this->product = wc_get_product($post_id);
        $this->use_api = get_post_meta($post_id, 'custom_price_calculator_use_api', true);
        $this->weight = $this->product->get_weight();

        // Combine prices to calculate the total
        $subtotal = round($this->calculation_current_product_gold() + $this->calculation_calculate_wages() + $this->calculation_calculate_profit() + $this->calculation_calculate_tax(), -3);

        $this->product->set_regular_price($subtotal);
        $this->product->save();
    }

    public function get_wages_percent()
    {
        return $this->get_percent_meta('custom_field_wages');
    }

    public function get_profit_percent()
    {
        return $this->get_percent_meta('custom_field_profit');
    }

    public function get_tax_percent()
    {
        return $this->get_percent_meta('custom_field_tax');
    }

    private function get_percent_meta($key)
    {
        if ($this->use_api === 'yes' && $this->weight) {
            if ($this->fetch_total_price_from_api()) {
                return floatval(get_post_meta($this->current_post_id, $key, true)) / 100;
            }
        }
        return 0; // Return 0 for insurance against non-valid cases
    }

    public function calculation_current_product_gold()
    {
        return $this->weight * $this->fetch_total_price_from_api();
    }

    public function calculation_calculate_wages()
    {
        return intval($this->calculation_current_product_gold() * $this->get_wages_percent());
    }

    public function calculation_calculate_profit()
    {
        return intval(($this->calculation_current_product_gold() + $this->calculation_calculate_wages()) * $this->get_profit_percent());
    }

    public function calculation_calculate_tax()
    {
        return intval(($this->calculation_calculate_profit() + $this->calculation_calculate_wages()) * $this->get_tax_percent());
    }

    public function getWeightProduct()
    {
        // Implement your logic here if needed
    }
}
