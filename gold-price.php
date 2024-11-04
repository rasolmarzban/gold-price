<?php

/**
 * Plugin Name: My Gold Price Plugin
 * Description: A plugin to fetch and display the latest gold prices using an API.
 * Version: 1.0
 * Author: rasolMarzban
 */
defined('ABSPATH') || exit;
class GoldPrice
{
    public function __construct()
    {
        $this->define_constants();
        $this->init();
        new CalculateRegular;
    }

    public function define_constants()
    {
        define('GLP_DIR', plugin_dir_path(__FILE__));
        define('GLP_URL', plugin_dir_url(__FILE__));
        define('GLP_TMP', GLP_DIR . 'tmp/');
        define('GLP_INC', GLP_DIR . 'inc/');
        define('GLP_ASSETS', GLP_DIR . 'assets/');
    }

    public function init()
    {
        include 'fetch-price.php';
        include 'calculate-gold.php';
        include 'gold-var-price.php';
        include 'shortcodes.php';
        include 'refresh-price.php';
        include 'show-price.php';
        include 'add-to-cart-price.php';
        include GLP_TMP . 'users/calculator-gold-price.php';
        if (is_admin()) {
            include GLP_INC . 'admin/admin-menu.php';
        } else {
            return;
        }
    }

    public function activation() {}
    public function deactivation() {}
}

new GoldPrice();
