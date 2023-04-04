<?php
/**
 * Plugin Name: Почта России доставка
 * Description: Плагин доставки для Вукомерс почта России
 * Version: 1.0.0
 */

add_action('woocommerce_shipping_init', 'postrussia_shipping_method');

function postrussia_shipping_method()
{
    if (!class_exists('WC_PostRussia_Shipping_Method')) {
        class WC_PostRussia_Shipping_Method extends WC_Shipping_Method
        {
            /**
             * Constructor for your shipping class
             *
             * @access public
             * @return void
             */
            public function __construct()
            {
                $this->id                 = 'postrussia';
                $this->method_title       = __('Почта России', 'woocommerce');
                $this->method_description = __('Доставка Почтой России', 'woocommerce');
                $this->enabled            = 'yes';
                $this->title              = __('Почта России', 'woocommerce');

                $this->init();
            }

            /**
             * Initialize shipping method
             *
             * @access public
             * @return void
             */
            public function init()
            {
                $this->init_form_fields();
                $this->init_settings();

                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Define settings field for this shipping
             * @return void
             */
            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title'   => __('Включить/Выключить', 'woocommerce'),
                        'type'    => 'checkbox',
                        'label'   => __('Включить доставку Почтой России', 'woocommerce'),
                        'default' => 'yes'
                    )
                );
            }

            /**
             * Calculate shipping cost
             *
             * @access public
             * @param mixed $package
             * @return void
             */
            public function calculate_shipping( $package = array() ) {

    // Определение параметров заказа
    $weight = $this->get_cart_weight();
    $volume = $this->get_cart_volume();
    $length = $this->get_cart_length();
    $width = $this->get_cart_width();
    $height = $this->get_cart_height();
    $destination = $package['destination'];
    
    // Получение ключа доступа к API Почты России
    $access_token = 'YOUR_ACCESS_TOKEN';
    
    // Создание экземпляра клиента Guzzle
    $client = new \GuzzleHttp\Client();
    
    // Отправка запроса к API Почты России
    $response = $client->request('POST', 'https://otpravka-api.pochta.ru/1.0/tariff', [
        'headers' => [
            'Authorization' => 'AccessToken ' . $access_token,
            'Content-Type' => 'application/json;charset=UTF-8',
        ],
        'json' => [
            'object' => [
                'weight' => $weight,
                'volume' => $volume,
                'length' => $length,
                'width' => $width,
                'height' => $height,
            ],
            'destination' => $destination,
        ],
    ]);

    // Обработка ответа от API Почты России
    $data = json_decode( $response->getBody()->getContents(), true );
    $price = $data['total-rate'];
    
    // Установка стоимости доставки
    $rate = array(
        'id' => $this->id,
        'label' => $this->title,
        'cost' => $price,
        'calc_tax' => 'per_order'
    );
    
    $this->add_rate( $rate );
}
    /**/
            public function calculate_shipping($package = array())
            {
                // Not implemented in this example
            }
        }
    }
    
    // Add the shipping method to WooCommerce
    add_filter('woocommerce_shipping_methods', 'add_postrussia_shipping_method');
    function add_postrussia_shipping_method($methods)
    {
        $methods[] = 'WC_PostRussia_Shipping_Method';
        return $methods;
    }
}
