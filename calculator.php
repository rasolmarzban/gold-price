<?php
defined('ABSPATH') || exit;
class CalculatorGoldPrice extends GetGoldPrice
{
    public function __construct() {}

    public function calculate() {}
    public function get_gold_price()
    {
        // Fetch gold price from API
        $goldprice = fetch_total_price_from_api();
        return $goldprice;
    }
    public function get_wages() {}
    public function get_profit() {}
    public function get_tax() {}
}
