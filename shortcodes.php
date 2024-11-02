<?php

defined('ABSPATH') || exit;

include_once 'fetch-price.php';

class GoldShortCodes extends GetGoldPrice
{
    public function __construct() // Corrected constructor name
    {
        // Usage: You can use the following in a shortcode or wherever needed
        add_shortcode('gold_price', array($this, 'get_gold_price')); // Callback to the method correctly
        add_shortcode('product_wages', array($this, 'get_product_wages'));
        add_shortcode('display_profit', array($this, 'display_profit_shortcode'));
        add_shortcode('display_wages', array($this, 'display_wages_shortcode'));
        add_shortcode('display_tax', array($this, 'display_tax_shortcode'));
        add_shortcode('display_addons', array($this, 'display_addons_shortcode'));
        add_shortcode('displays_regular_profit', array($this, 'displays_regular_profit_shortcode'));
        add_shortcode('displays_regular_wages', array($this, 'displays_regular_wages_shortcode'));
        add_shortcode('displays_regular_tax', array($this, 'displays_regular_tax_shortcode'));
        add_shortcode('displays_regular_addons', array($this, 'displays_regular_addons_shortcode'));

        // ajax request
        add_action('wp_ajax_get_profit_variation', [$this, 'fetch_profit_variation']);
        add_action('wp_ajax_nopriv_get_profit_variation', [$this, 'fetch_profit_variation']);
        add_action('wp_ajax_get_wages_variation', [$this, 'fetch_wages_variation']);
        add_action('wp_ajax_nopriv_get_wages_variation', [$this, 'fetch_wages_variation']);
        add_action('wp_ajax_get_tax_variation', [$this, 'fetch_tax_variation']);
        add_action('wp_ajax_nopriv_get_tax_variation', [$this, 'fetch_tax_variation']);

        //add ajax action
        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_scripts']);
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

    public function fetch_profit_variation()
    {
        if (!isset($_POST['variation_id'])) {
            wp_send_json_error('No Variation ID provided.');
            wp_die();
        }

        $variation_id = intval($_POST['variation_id']);


        $profit = get_post_meta($variation_id, 'custom_field_profit_variation', true);


        if ($profit) {
            echo esc_html($profit);
        } else {
            echo esc_html__('0', 'profit_variation_not_available');
        }

        wp_die();
    }

    public function fetch_wages_variation()
    {
        if (!isset($_POST['variation_id'])) {
            wp_send_json_error('No Variation ID provided.');
            wp_die();
        }

        $variation_id = intval($_POST['variation_id']);

        $wages = get_post_meta($variation_id, 'custom_field_wages_variation', true);

        if ($wages) {
            echo esc_html($wages);
        } else {
            echo esc_html__('0', 'wages_variation_not_available');
        }

        wp_die();
    }

    public function fetch_tax_variation()
    {
        if (!isset($_POST['variation_id'])) {
            wp_send_json_error('No Variation ID provided.');
            wp_die();
        }

        $variation_id = intval($_POST['variation_id']);


        $tax = get_post_meta($variation_id, 'custom_field_tax_variation', true);


        if ($tax) {
            echo esc_html($tax);
        } else {
            echo esc_html__('0', 'tax_variation_not_available');
        }

        wp_die();
    }

    public function fetch_addons_variation()
    {
        if (!isset($_POST['variation_id'])) {
            wp_send_json_error('No Variation ID provided.');
            wp_die();
        }

        $variation_id = intval($_POST['variation_id']);

        $this->display_profit_shortcode($variation_id);

        $addons = get_post_meta($variation_id, 'custom_field_addons_variation', true);


        if ($addons) {
            echo esc_html($addons);
        } else {
            echo esc_html__('0', 'addon_variation_not_available');
        }
        wp_die();
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

    public function enqueue_custom_scripts()
    {
        wp_enqueue_script('custom-ajax', GLP_URL . 'assets/js/gold-shortcode-script.js', array('jquery'), null, true);
        wp_localize_script('custom-ajax', 'ajaxurl', admin_url('admin-ajax.php'));
    }
    public function display_profit_shortcode()
    {
        $product_id = get_the_ID();
        $product_var_id = wc_get_product($product_id);
        if ($product_var_id->is_type('variable')) {
            return 'سود: <p id="profit-display"></p> درصد'; // Shortcode will just produce the HTML element for updating via AJAX
        }
    }

    public function display_wages_shortcode()
    {
        $product_id = get_the_ID();
        $product_var_id = wc_get_product($product_id);
        if ($product_var_id->is_type('variable')) {
            return 'اجرت ساخت: <p id="wages-display"></p> درصد';
        }
    }

    public function display_tax_shortcode()
    {
        $product_id = get_the_ID();
        $product_var_id = wc_get_product($product_id);
        if ($product_var_id->is_type('variable')) {
            return 'مالیات: <p id="tax-display"></p> درصد';
        }
    }

    public function display_addons_shortcode()
    {
        $product_id = get_the_ID();
        $product_var_id = wc_get_product($product_id);
        if ($product_var_id->is_type('variable')) {
            return 'متعلقات اضافی (سنگ، مروارید،چرم و...): <p id="addons-display"></p> تومان';
        }
    }
    public function displays_regular_profit_shortcode()
    {

        $product_id = get_the_ID(); // if you’re sure it’s a product page
        $profit = get_post_meta($product_id, 'custom_field_profit', true);
        if ($profit) {
            echo '<p>سود : ' . $profit . ' درصد</p>';
        }
        return "";
    }
    public function displays_regular_wages_shortcode()
    {

        $product_id = get_the_ID(); // if you’re sure it’s a product page
        $wages = get_post_meta($product_id, 'custom_field_wages', true);
        if ($wages) {
            echo '<p>اجرت ساخت : ' . $wages . ' درصد</p>';
        }
        return "";
    }
    public function displays_regular_tax_shortcode()
    {

        $product_id = get_the_ID(); // if you’re sure it’s a product page

        $tax = get_post_meta($product_id, 'custom_field_tax', true);
        if ($tax) {
            echo '<p>مالیات : ' . $tax . ' درصد</p>';
        }
        return "";
    }
    public function displays_regular_addons_shortcode()
    {

        $product_id = get_the_ID(); // if you’re sure it’s a product page

        $addons = get_post_meta($product_id, 'custom_field_addons', true);
        if ($addons === 0) {
            return "";
        }
        echo '<p>متعلقات اضافی: ' . $addons . ' تومان</p>';
    }
}
// Initialize the shortcode class
new GoldShortCodes();
