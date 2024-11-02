<?php
defined('ABSPATH') || exit;
class AdminGoldPrice
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu_gold_price']);
    }

    public function admin_menu_gold_price()
    {
        add_menu_page(
            'Gold_price_Menu',
            'Gold Price Admin',
            'manage_options',
            'gold_price_menu_slug',
            [$this, 'gold_price_menu_callback']
        );
    }

    public function gold_price_menu_callback()
    {

        include GLP_TMP . 'admin/admin-menu-tmp.php';
    }
}
new AdminGoldPrice();
