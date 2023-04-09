<?php
/**
 * Plugin Name: Почта России доставка
 * Description: Плагин доставки для Вукомерс почта России
 * Version: 1.0.1
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
                    ),
                    'pochta_delivery_type' => array(
                    'title'       => __( 'Тип доставки', 'woocommerce' ),
                    'type'        => 'select',
                    'description' => __( 'Выберите тип доставки', 'woocommerce' ),
                    'default'     => 'courier',
                    'options'     => array(
                    'courier' => __( 'Курьером', 'woocommerce' ),
                    'pvz'     => __( 'До ПВЗ', 'woocommerce' ),
                    )
                );
            }
        // Existing code for the class
        /**
         * Calculate shipping cost to PVZ
         *
         * @param array $package Package information
         * @return void
         */

                private function calculate_shipping_to_pvz( $package ) {
                    // Определяем параметры доставки
                    $weight = $this->get_package_weight( $package );
                    $volume = $this->get_package_volume( $package );

                    // Формируем тело запроса к API Почты России
                    $request = array(
                        'weight' => $weight,
                        'length' => $volume['length'],
                        'width'  => $volume['width'],
                        'height' => $volume['height'],
                        'pvz'    => true,
                    );

                    // Отправляем запрос к API Почты России
                    $response = wp_remote_post( 'https://otpravka-api.pochta.ru/1.0/tariff', array(
                        'headers' => array(
                            'Content-Type' => 'application/json',
                            'Authorization' => 'AccessToken ' . $this->settings['pochta_token'],
                        ),
                        'body' => json_encode( $request ),
                    ) );

                    // Обрабатываем ответ от API Почты России
                    if ( is_wp_error( $response ) ) {
                        return false;
                    } else {
                        $data = json_decode( wp_remote_retrieve_body( $response ), true );
                        if ( isset( $data['total-rate'] ) ) {
                            $rate = $data['total-rate'];
                            $label = 'Доставка до ПВЗ Почты России (' . $data['delivery-time'] . ' дней)';
                            $this->add_rate( array(
                                'id'    => $this->id . '_pvz',
                                'label' => $label,
                                'cost'  => $rate,
                            ) );
                        } else {
                            return false;
                        }
                    }
                }
                    
            /**
             * Calculate shipping cost
             *
             * @access public
             * @param mixed $package
             * @return void
             */
                    
            public function calculate_shipping( $package = array() ) {
                $meta_data = array();

                if ( $this->settings['enabled'] !== 'yes' ) {
                    return;
                }

                if ( empty( $this->settings['pochta_token'] ) || empty( $this->settings['pochta_index_from'] ) ) {
                    return;
                }

                if ( $this->settings['pochta_delivery_type'] === 'pvz' ) {
                    $cost = $this->calculate_shipping_to_pvz( $package );
                } else {
                    $cost = $this->calculate_shipping_by_weight( $package );
                }

                $label = $this->title;
                if ( $this->settings['show_delivery_time'] === 'yes' ) {
                    $delivery_time = $this->get_delivery_time();
                    if ( $delivery_time ) {
                        $meta_data[] = array(
                            'key'   => 'Delivery time',
                            'value' => $delivery_time,
                        );
                        $label .= ' (' . $delivery_time . ')';
                    }
                }

                return array(
                    'id'        => $this->id,
                    'label'     => $label,
                    'cost'      => $cost,
                    'package'   => $package,
                    'meta_data' => $meta_data,
                );
            }

    /**/
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
