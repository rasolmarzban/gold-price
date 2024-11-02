<?php
defined('ABSPATH') || exit;
class GetGoldPrice
{
    // public $url = 'https://www.tala.ir/banner/?rnd=ZHkacfabyC&ids=,&is-mobile=0&android=0&ios=0&rnd=1263&h=1080&w=1920'; // Ensure the URL is correct
    public $url = 'https://call1.tgju.org/ajax.json?rev=Pm5mS24NxLXVmDha6pHrMAQjdbTe0eNKQFMBWQ2HbiXmzqjNGTT7HnMkJ9BN';
    public $body = null;
    public $data = null;
    public $response = null;
    public $total_price = null;

    public function __construct()
    {
        add_action('fetch_gold_price_event', [$this, 'fetch_gold_price']);
        $this->fetch_gold_price();
        $this->fetch_total_price_from_api();
        //$this->get18gramPrice();
    }

    public function fetch_gold_price()
    {
        $this->decodeJson();
        // $this->get18gramPrice();
    }

    public function decodeJson()
    {
        $this->response = wp_remote_get($this->url);

        if (is_wp_error($this->response)) {
            error_log('Error fetching gold price: ' . $this->response->get_error_message());
            return; // Handle error as necessary
        }

        $this->body = wp_remote_retrieve_body($this->response);
        $this->data = json_decode($this->body, true);
    }

    public function fetch_total_price_from_api()
    {
        if (isset($this->data['current']['geram18'])) {
            set_transient('gold_price_geram18', $this->data['current']['geram18']['p'], 60); // 10 minutes
            set_transient('gold_time_geram18', $this->data['current']['geram18']['t-g'], 60);
        }

        $gold_price_current = intval(str_replace(',', '', get_transient('gold_price_geram18')));
        $total_price = $gold_price_current / 10;

        return $total_price;
    }
}

new GetGoldPrice();
