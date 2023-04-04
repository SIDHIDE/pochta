<?php
/*
Plugin Name: Pochta Shipping Method
Plugin URI: https://example.com/
Description: Add Pochta shipping method to WooCommerce
Version: 1.0
Author: Your Name
Author URI: https://example.com/
*/

add_filter('woocommerce_shipping_methods', 'add_pochta_shipping_method');

function add_pochta_shipping_method($methods)
{
    $methods['pochta'] = 'Pochta_Shipping_Method';
    return $methods;
}

add_action('woocommerce_shipping_init', 'pochta_shipping_method_init');

function pochta_shipping_method_init()
{
    if (!class_exists('Pochta_Shipping_Method')) {
        class Pochta_Shipping_Method extends WC_Shipping_Method
        {
            public function __construct()
            {
                $this->id = 'pochta';
                $this->method_title = __('Pochta', 'woocommerce');
                $this->method_description = __('Pochta shipping method', 'woocommerce');
                $this->enabled = 'yes';
                $this->init();
            }

            public function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Enable Pochta shipping method', 'woocommerce'),
                        'default' => 'yes',
                    ),
                    'api_key' => array(
                        'title' => __('API Key', 'woocommerce'),
                        'type' => 'password',
                        'description' => __('Enter your Pochta API key.', 'woocommerce'),
                        'default' => '',
                        'desc_tip' => true,
                    ),
                );
            }

            public function calculate_shipping($package = array())
            {
                $rate = array(
                    'id' => $this->id,
                    'label' => $this->method_title,
                    'cost' => 0,
                    'calc_tax' => 'per_item'
                );
                $this->add_rate($rate);
            }
        }
    }
}

function add_pochta_api_key($headers)
{
    $api_key = base64_decode(' '); // replace with your actual API key
    $headers['Authorization'] = 'AccessToken ' . $api_key;
    return $headers;
}

add_filter('woocommerce_shipping_methods', 'add_pochta_shipping_method');
add_filter('http_request_args', 'add_pochta_api_key');
