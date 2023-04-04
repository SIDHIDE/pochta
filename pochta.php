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
