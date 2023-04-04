<?php
/**
 * Plugin Name: Russian Post Shipping Method
 * Plugin URI: https://example.com
 * Description: Custom shipping method for Russian Post
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://example.com
 */

add_filter('woocommerce_shipping_methods', 'add_pochta_shipping_method');

function add_pochta_shipping_method($methods)
{
    $methods['pochta'] = 'Pochta_Shipping_Method';
    return $methods;
}

add_filter('woocommerce_shipping_method_tag_class', 'pochta_shipping_method_tag_class', 10, 4);

function pochta_shipping_method_tag_class($class, $tag, $meta, $method)
{
    if ($method->id === 'pochta') {
        $class .= ' pochta';
    }
    return $class;
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
                $this->method_title = __('Russian Post');
                $this->method_description = __('Custom shipping method for Russian Post');
                $this->enabled = 'yes';
                $this->title = $this->get_option('title', __('Russian Post'));
                $this->init_form_fields();
                $this->init_settings();
                $this->supports = [
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                ];
                $this->is_test_mode = $this->get_option('test_mode');
                add_filter('http_request_args', [$this, 'add_pochta_api_key']);
                add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'pochta_shipping_method_full_label'], 10, 2);
            }

            public function init_form_fields()
            {
                $this->instance_form_fields = [
                    'title' => [
                        'title' => __('Title', 'pochta'),
                        'type' => 'text',
                        'description' => __('Enter a title for this shipping method', 'pochta'),
                        'default' => __('Russian Post', 'pochta'),
                        'desc_tip' => true,
                    ],
                    'test_mode' => [
                        'title' => __('Test mode', 'pochta'),
                        'type' => 'checkbox',
                        'description' => __('Enable test mode', 'pochta'),
                        'default' => 'yes',
                        'desc_tip' => true,
                    ],
                ];
            }

            public function calculate_shipping($package = [])
            {
                $cost = $this->get_pochta_shipping_cost($package);
                $this->add_rate([
                    'id' => $this->id,
                    'label' => $this->title,
                    'cost' => $cost,
                ]);
            }

public function add_pochta_api_key($args)
{
    $api_key = base64_decode('YWRtaW5Ad2VhcG9uLnN1OnZsYWRrcmFjazIyMzQ1NjI='); // replace with your actual API key
    $args['headers']['Authorization'] = 'Bearer ' . $api_key;
    return $args;
}


            public function pochta_shipping_method_full_label($label, $method)
            {
                if ($method->id === $this->id) {
                    $cost = $this->get_pochta_shipping_cost();
                    $label .= ' - ' . wc_price($cost);
                }
                return $label;
            }

            public function get_pochta_shipping_cost($package = [])
            {
                $weight = $this->get_package_weight($package);
                $from = $this->get_option('from');
                $to = $this->get_option('to');
                $dimensions = $this->get_package_dimensions($package);
                $length = isset($dimensions['length']) ? $dimensions['length'] : '';
                $width = isset($dimensions['width']) ? $dimensions['width'] : '';
                $height = isset($dimensions['height']) ? $dimensions['height'] : '';
                
                // Build the request data
                $request_data = [
                    'weight' => $weight,
                    'from' => $from,
                    'to' => $to,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                ];

                // Call the Russian Post API to get the shipping cost
                $url = 'https://delivery.russianpost.ru/api/service/calculate_delivery_cost';
                $response = wp_remote_post($url, [
                    'body' => json_encode($request_data),
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]);

                // If there was an error, return 0 as the cost
                if (is_wp_error($response)) {
                    return 0;
                }

                $body = json_decode(wp_remote_retrieve_body($response), true);

                // If there was an error in the response, return 0 as the cost
                if (isset($body['error'])) {
                    return 0;
                }

                // Otherwise, return the cost from the response
                return isset($body['data']['total']) ? $body['data']['total'] : 0;
            }

            private function get_package_weight($package)
            {
                $weight = 0;
                foreach ($package['contents'] as $item) {
                    $product = $item->get_product();
                    if ($product->get_weight()) {
                        $weight += $product->get_weight() * $item->get_quantity();
                    }
                }
                return wc_get_weight($weight, 'kg');
            }

            private function get_package_dimensions($package)
            {
                $length = 0;
                $width = 0;
                $height = 0;
                foreach ($package['contents'] as $item) {
                    $product = $item->get_product();
                    if ($product->get_length()) {
                        $length += $product->get_length() * $item->get_quantity();
                    }
                    if ($product->get_width()) {
                        $width += $product->get_width() * $item->get_quantity();
                    }
                    if ($product->get_height()) {
                        $height += $product->get_height() * $item->get_quantity();
                    }
                }
                return [
                    'length' => wc_get_dimension($length, 'cm'),
                    'width' => wc_get_dimension($width, 'cm'),
                    'height' => wc_get_dimension($height, 'cm'),
                ];
            }
        }
    }
}
add_filter('woocommerce_shipping_methods', 'add_pochta_shipping_method');

function add_pochta_shipping_method($methods)
{
    $methods['pochta'] = 'Pochta_Shipping_Method';
    return $methods;
}

add_filter('woocommerce_shipping_method_tag_class', 'pochta_shipping_method_tag_class', 10, 4);

function pochta_shipping_method_tag_class($class, $tag, $meta, $method)
{
    if ($method->id === 'pochta') {
        $class .= ' pochta';
    }
    return $class;
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
                $this->method_title = __('Russian Post');
                $this->method_description = __('Custom shipping method for Russian Post');
                $this->enabled = 'yes';
                $this->title = $this->get_option('title', __('Russian Post'));
                $this->init_form_fields();
                $this->init_settings();
                $this->supports = [
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                ];
                $this->is_test_mode = $this->get_option('test_mode');
                add_filter('http_request_args', [$this, 'add_pochta_api_key']);
                add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'pochta_shipping_method_full_label'], 10, 2);
            }

            public function init_form_fields()
            {
                $this->instance_form_fields = [
                    'title' => [
                        'title' => __('Title', 'pochta'),
                        'type' => 'text',
                        'description' => __('Enter a title for this shipping method', 'pochta'),
                        'default' => __('Russian Post', 'pochta'),
                        'desc_tip' => true,
                    ],
                    'test_mode' => [
                        'title' => __('Test mode', 'pochta'),
                        'type' => 'checkbox',
                        'description' => __('Enable test mode', 'pochta'),
                        'default' => 'yes',
                        'desc_tip' => true,
                    ],
                ];
            }

            public function calculate_shipping($package = [])
            {
                $cost = $this->get_pochta_shipping_cost($package);
                $this->add_rate([
                    'id' => $this->id,
                    'label' => $this->title,
                    'cost' => $cost,
                ]);
            }

            public function add_pochta_api_key($args)
            {
                $api_key = base64_decode('YWRtaW5Ad2VhcG9uLnN1OnZsYWRrcmFjazIyMzQ1NjI='); // replace with your actual API key
                $args['headers']['Authorization'] = 'AccessToken ' . $api_key;
                return $args;
            }

		public function get_pochta_shipping_cost($package)
		{
		    $weight = 0;
		    foreach ($package['contents'] as $item) {
		        $product = $item->get_product();
		        if ($product) {
		            $weight += $product->get_weight() * $item->get_quantity();
		        }
		    }
		    $cost_per_kg = 100; // replace with your actual cost per kg
		    $cost = $weight * $cost_per_kg;
		    if ($this->is_test_mode) {
		        $cost *= 0.5; // apply 50% discount in test mode
		    }
		    return $cost;
		}}
		if (!class_exists('Pochta_Shipping_Method')) {
    class Pochta_Shipping_Method extends WC_Shipping_Method
    {
        public function __construct()
        {
            $this->id = 'pochta';
            $this->method_title = __('Russian Post');
            $this->method_description = __('Custom shipping method for Russian Post');
            $this->enabled = 'yes';
            $this->title = $this->get_option('title', __('Russian Post'));
            $this->init_form_fields();
            $this->init_settings();
            $this->supports = [
                'shipping-zones',
                'instance-settings',
                'instance-settings-modal',
            ];
            $this->is_test_mode = $this->get_option('test_mode');
            add_filter('http_request_args', [$this, 'add_pochta_api_key']);
            add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'pochta_shipping_method_full_label'], 10, 2);
        }

        public function init_form_fields()
        {
            $this->instance_form_fields = [
                'title' => [
                    'title' => __('Title', 'pochta'),
                    'type' => 'text',
                    'description' => __('Enter a title for this shipping method', 'pochta'),
                    'default' => __('Russian Post', 'pochta'),
                    'desc_tip' => true,
                ],
                'test_mode' => [
                    'title' => __('Test mode', 'pochta'),
                    'type' => 'checkbox',
                    'description' => __('Enable test mode', 'pochta'),
                    'default' => 'yes',
                    'desc_tip' => true,
                ],
            ];
        }

        public function calculate_shipping($package = [])
        {
            $cost = $this->get_pochta_shipping_cost($package);
            $this->add_rate([
                'id' => $this->id,
                'label' => $this->title,
                'cost' => $cost,
            ]);
        }

        public function add_pochta_api_key($args)
        {
            $api_key = base64_decode('YWRtaW5Ad2VhcG9uLnN1OnZsYWRrcmFjazIyMzQ1NjI='); // replace with your actual API key
            $args['headers']['Authorization'] = 'AccessToken ' . $api_key;
            return $args;
        }

    public function get_pochta_shipping_cost($package)
    {
        $weight = 0;
        foreach ($package['contents'] as $item) {
            $product = $item->get_product();
            if ($product) {
                $weight += $product->get_weight() * $item->get_quantity();
            }
        }

        $url = 'https://example.com/pochta/shipping/calculate?weight=' . $weight;
        if ($this->is_test_mode) {
            $url .= '&test_mode=true';
        }

        $args = [];
        $response = wp_remote_get($url, $args);
        $cost = 0;
        if (!is_wp_error($response)) {
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            if (isset($response_data['cost'])) {
                $cost = $response_data['cost'];
            }
        }

        return $cost;
    }
public function init()
{
    add_filter('woocommerce_shipping_methods', array($this, 'add_pochta_shipping_method'));
}

function add_pochta_shipping_method($methods)
{
    $methods['pochta'] = 'Pochta_Shipping_Method';
    return $methods;
}
public function init()
{
add_filter('woocommerce_shipping_method_tag_class', 'pochta_shipping_method_tag_class', 10, 4);
}
function pochta_shipping_method_tag_class($class, $tag, $meta, $method)
{
    if ($method->id === 'pochta') {
        $class .= ' pochta';
    }
    return $class;
}
public function init()
{
add_action('woocommerce_shipping_init', 'pochta_shipping_method_init');
}
function pochta_shipping_method_init()
{
    if (!class_exists('Pochta_Shipping_Method')) {
        class Pochta_Shipping_Method extends WC_Shipping_Method
        {
            public function __construct()
            {
                $this->id = 'pochta';
                $this->method_title = __('Russian Post');
                $this->method_description = __('Custom shipping method for Russian Post');
                $this->enabled = 'yes';
                $this->title = $this->get_option('title', __('Russian Post'));
                $this->init_form_fields();
                $this->init_settings();
                $this->supports = [
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                ];
                $this->is_test_mode = $this->get_option('test_mode');
                add_filter('http_request_args', [$this, 'add_pochta_api_key']);
                add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'pochta_shipping_method_full_label'], 10, 2);
            }

            public function init_form_fields()
            {
                $this->instance_form_fields = [
                    'title' => [
                        'title' => __('Title', 'pochta'),
                        'type' => 'text',
                        'description' => __('Enter a title for this shipping method', 'pochta'),
                        'default' => __('Russian Post', 'pochta'),
                        'desc_tip' => true,
                    ],
                    'test_mode' => [
                        'title' => __('Test mode', 'pochta'),
                        'type' => 'checkbox',
                        'description' => __('Enable test mode', 'pochta'),
                        'default' => 'yes',
                        'desc_tip' => true,
                    ],
                ];
            }

            public function calculate_shipping($package = [])
            {
                $cost = $this->get_pochta_shipping_cost($package);
                $this->add_rate([
                    'id' => $this->id,
                    'label' => $this->title,
                    'cost' => $cost,
                ]);
            }

            public function add_pochta_api_key($args)
            {
                $api_key = base64_decode('YWRtaW5Ad2VhcG9uLnN1OnZsYWRrcmFjazIyMzQ1NjI='); // replace with your actual API key
                $args['headers']['Authorization'] = 'AccessToken ' . $api_key;
                return $args;
            }
            
public function get_pochta_shipping_cost($package)
{
    $weight = 0;
    foreach ($package['contents'] as $item) {
        $product = $item->get_product();
        if ($product) {
            $weight += $product->get_weight() * $item->get_quantity();
        }
    }
    $weight = wc_get_weight($weight, 'kg');
    $destination = $package['destination'];
    $url = 'https://example.com/shipping/calculate-cost?weight=' . $weight . '&destination=' . $destination['postcode'];
    $args = [
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'timeout' => 30,
    ];
    $response = wp_remote_get($url, $args);
    $cost = 0;
    if (!is_wp_error($response)) {
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        if (isset($data['success']) && $data['success'] === true && isset($data['cost'])) {
            $cost = $data['cost'];
        }
    }
    return $cost;
}
// Check for errors
if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    error_log($error_message);
    return false;
}

// Parse the response and get the cost
$response_body = wp_remote_retrieve_body($response);
$response_data = json_decode($response_body, true);

if (isset($response_data['errors']) || empty($response_data['price'])) {
    error_log('Error: Failed to get shipping cost from Pochta API');
    return false;
}

return (float) $response_data['price'];
