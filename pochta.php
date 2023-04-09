<?php
/**
 * Plugin Name: Доставка Почтой России (API)
 * Description: Плагин для доставки Почтой России с расчетом стоимости до пункта выдачи через API
 * Version: 1.0
 * Author: ChatGPT
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_filter( 'woocommerce_shipping_methods', 'add_pochta_shipping_method' );

function add_pochta_shipping_method( $methods ) {
    $methods[] = 'WC_Pochta_Shipping_Method_API';
    return $methods;
}

class WC_Pochta_Shipping_Method_API extends WC_Shipping_Method {
    /**
     * @var array
     */
    public $settings;

    /**
     * @var string
     */
    public $endpoint = 'https://otpravka-api.pochta.ru/';

    /**
     * @var string
     */
    public $token = 'uDdxTLqYDbilkL2hA3QLslXMafQkAmAh';

    public function __construct() {
        $this->id                 = 'pochta_api';
        $this->title              = 'Почта России (API)';
        $this->method_description = 'Доставка Почтой России с расчетом стоимости до пункта выдачи через API';
        $this->supports           = array( 'shipping-zones', 'instance-settings' );
        $this->init();
    }

    public function init() {
        $this->init_form_fields();
        $this->init_settings();
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'pochta_delivery_price' => array(
                'title'       => 'Стоимость доставки',
                'type'        => 'number',
                'description' => 'Введите стоимость доставки',
                'default'     => '0',
                'desc_tip'    => true,
            ),
            'pochta_token' => array(
                'title'       => 'Токен Почты России',
                'type'        => 'text',
                'description' => 'Введите токен Почты России для доступа к API',
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    public function calculate_shipping( $package = array() ) {
        $cost = 0;

        if ( ! empty( $package['destination']['postcode'] ) ) {
            $pvz_list = $this->get_pvz_list( $package['destination']['postcode'] );
            if ( $pvz_list ) {
                $cost = $this->settings['pochta_delivery_price'];
            }
        }

        $meta_data = array();
        if ( isset( $package['destination']['postcode'] ) ) {
            $meta_data['postcode'] = $package['destination']['postcode'];
        }

        $this->add_rate( array(
            'id'        => $this->id,
            'label'     => $this->title,
            'cost'      => $cost,
            'package'   => $package,
            'meta_data' => $meta_data,
        ) );

/**
 * Рассчитывает стоимость доставки до пункта выдачи (ПВЗ) через API Почты России
 */

public function calculate_shipping_to_pvz( $package ) {
    $shipping_address = $package['destination'];
    $post_office = $this->get_selected_post_office( $shipping_address );
    if ( ! $post_office ) {
        return 0;
    }

    // Рассчитываем стоимость доставки через API Почты России
    $cost = $this->get_shipping_cost_to_pvz( $post_office, $package );

    return $cost;
}

/**
 * Получает список ближайших пунктов выдачи (ПВЗ) через API Почты России
 *
 * @param  array $shipping_address
 * @return array|null
 */
private function get_post_offices( $shipping_address ) {
    $client = new SoapClient( 'https://tariff.pochta.ru/tariff/v1/deliverypoints.wsdl' );

    $result = $client->getDeliveryPointsByAddress([
        'address' => [
            'index'      => $shipping_address['postcode'],
            'region'     => $shipping_address['state'],
            'settlement' => $shipping_address['city'],
            'street'     => $shipping_address['address'],
            'house'      => $shipping_address['address_2'],
        ],
        'topCount' => 10,
    ]);

    if ( isset( $result->return ) && is_array( $result->return ) ) {
        return $result->return;
    }

    return null;
}

/**
 * Получает ближайший пункт выдачи (ПВЗ) через API Почты России
 *
 * @param  array $shipping_address
 * @return array|null
 */
private function get_selected_post_office( $shipping_address ) {
    $post_offices = $this->get_post_offices( $shipping_address );

    if ( ! $post_offices ) {
        return null;
    }

    return $post_offices[0];
}

/**
 * Рассчитывает стоимость доставки до пункта выдачи (ПВЗ) через API Почты России
 *
 * @param  array $post_office
 * @param  array $package
 * @return float
 */
private function get_shipping_cost_to_pvz( $post_office, $package ) {
    $client = new SoapClient( 'https://tariff.pochta.ru/tariff/v1/calculate/tariff-calculate.wsdl' );

    $params = [
        'index-from'      => $this->postcode,
        'index-to'        => $post_office['postcode'],
        'mail-category'   => 'ORDINARY',
        'mail-direct'     => true,
        'mail-type'       => 'POSTAL_PARCEL',
        'mass'            => $this->get_package_weight( $package ),
        'payment-method'  => 'CASHLESS',
        'transport-type'  => 'SURFACE',
        'with-order-of-notice' => true,
        'with-simple-notice'   => true,
    ];

    try {
        $response = $client->calculateTariff( $params );
        $cost = $response->totalRateWithNds;

        // Convert cost from kopeks to rubles
        $cost = $cost / 100;

        return $cost;
    } catch ( SoapFault $e ) {
        error_log( 'Error getting shipping cost: ' . $e->getMessage() );
        return false;
    }
}
?>
