<?php
/*
Plugin Name: Доставка Почтой России
Plugin URI: https://example.com/
Description: Плагин для добавления доставки Почтой России в интернет-магазин на WooCommerce
Version: 1.0
Author: Ваше имя
Author URI: https://example.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Подключаем функции для работы с доставкой Почты России
require_once( plugin_dir_path( __FILE__ ) . 'pochta-shipping-functions.php' );

// Регистрируем метод доставки Почтой России в WooCommerce
add_filter( 'woocommerce_shipping_methods', 'add_pochta_shipping_method' );
function add_pochta_shipping_method( $methods ) {
    $methods['pochta_shipping'] = 'Pochta_Shipping_Method';
    return $methods;
}

// Создаем класс метода доставки Почтой России
class Pochta_Shipping_Method extends WC_Shipping_Method {

    /**
     * Конструктор метода доставки
     */
    public function __construct() {
        $this->id                 = 'pochta_shipping'; // Уникальный идентификатор метода доставки
        $this->method_title       = 'Доставка Почтой России'; // Название метода доставки
        $this->method_description = 'Доставка Почтой России'; // Описание метода доставки
        $this->enabled            = 'yes'; // Метод доставки включен по умолчанию
        $this->title              = 'Доставка Почтой России'; // Название метода доставки в корзине и на странице оформления заказа

        // Можно задать настройки метода доставки, например, стоимость или ограничения по весу, высоте и т.д.
        $this->init();
    }

    /**
     * Инициализация настроек метода доставки
     */
    public function init() {
        $this->init_form_fields();
        $this->init_settings();

        $this->cost = $this->get_option( 'cost' );
        $this->fee = $this->get_option( 'fee' );
        $this->tax_status = $this->get_option( 'tax_status' );
        $this->minimum_weight = $this->get_option( 'minimum_weight' );
        $this->maximum_weight = $this->get_option( 'maximum_weight' );
        $this->minimum_height = $this->get_option( 'minimum_height' );
        $this->maximum_height = $this->get_option( 'maximum_height' );
$this->minimum_length = $this->get_option( 'minimum_length' );
$this->maximum_length = $this->get_option( 'maximum_length' );
$this->shipping_class = $this->get_option( 'shipping_class' );
    // Обновляем настройки метода доставки при сохранении в админке
    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
}

/**
 * Инициализация настроек формы настроек метода доставки
 */
public function init_form_fields() {
    $this->form_fields = array(
        'enabled' => array(
            'title'       => 'Включить/отключить',
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => 'Название метода доставки',
            'type'        => 'text',
            'description' => 'Название метода доставки, которое будет отображаться на странице оформления заказа.',
            'default'     => 'Доставка Почтой России',
        ),
        'cost' => array(
            'title'       => 'Стоимость доставки',
            'type'        => 'text',
            'description' => 'Стоимость доставки, которая будет добавлена к стоимости заказа. Можно задать фиксированную сумму или процент от стоимости заказа. Для бесплатной доставки оставьте это поле пустым.',
            'default'     => '',
        ),
        'fee' => array(
            'title'       => 'Надбавка за обработку',
            'type'        => 'text',
            'description' => 'Надбавка за обработку заказа. Можно задать фиксированную сумму или процент от стоимости заказа. Например, для покрытия расходов на упаковку и т.д.',
            'default'     => '',
        ),
        'tax_status' => array(
            'title'       => 'Ставка налога',
            'type'        => 'select',
            'description' => 'Ставка налога, которая будет применена к стоимости доставки.',
            'default'     => 'taxable',
            'options'     => array(
                'taxable'      => 'Облагаемая налогом',
                'none'         => 'Не облагаемая налогом',
                'shipping'     => 'Облагаемая налогом, но не оплачиваемая клиентом',
            ),
        ),
        'minimum_weight' => array(
            'title'       => 'Минимальный вес заказа',
            'type'        => 'text',
            'description' => 'Минимальный вес заказа для применения этого метода доставки. Если оставить поле пустым, то ограничения по весу не будут применяться.',
'placeholder' => '0',
),
'maximum_weight' => array(
'title' => 'Максимальный вес заказа',
'type' => 'text',
'description' => 'Максимальный вес заказа для применения этого метода доставки. Если оставить поле пустым, то ограничения по весу не будут применяться.',
'placeholder' => '0',
),
'minimum_length' => array(
'title' => 'Минимальная длина посылки',
'type' => 'text',
'description' => 'Минимальная длина посылки в сантиметрах для применения этого метода доставки. Если оставить поле пустым, то ограничения по размерам не будут применяться.',
'placeholder' => '0',
),
'maximum_length' => array(
'title' => 'Максимальная длина посылки',
'type' => 'text',
'description' => 'Максимальная длина посылки в сантиметрах для применения этого метода доставки. Если оставить поле пустым, то ограничения по размерам не будут применяться.',
'placeholder' => '0',
),
'maximum_width' => array(
'title' => 'Максимальная ширина посылки',
'type' => 'text',
'description' => 'Максимальная ширина посылки в сантиметрах для применения этого метода доставки. Если оставить поле пустым, то ограничения по размерам не будут применяться.',
'placeholder' => '0',
),
'maximum_height' => array(
'title' => 'Максимальная высота посылки',
'type' => 'text',
'description' => 'Максимальная высота посылки в сантиметрах для применения этого метода доставки. Если оставить поле пустым, то ограничения по размерам не будут применяться.',
'placeholder' => '0',
),
'shipping_class' => array(
'title' => 'Класс доставки',
'type' => 'select',
'description' => 'Класс доставки, который будет применен к этому методу доставки.',
'default' => '',
'options' => $this->get_shipping_classes_options(),
),
);
}
/**
 * Рассчитывает стоимость и срок доставки для заказа
 *
 * @param array $package Массив с информацией о товарах в заказе
 *
 * @return void
 */
public function calculate_shipping( $package = array() ) {
    // Проверяем, что метод доставки включен
    if ( ! $this->is_enabled() ) {
        return;
    }

    // Проверяем, что все обязательные поля настроек заполнены
$required_settings = array(
'api_key',
'sender_postcode',
'shipping_method',
'maximum_weight',
);
foreach ( $required_settings as $setting ) {
if ( empty( $this->settings[ $setting ] ) ) {
return;
}
}
    // Создаем экземпляр класса для работы с API Почты России
    $client = new SoapClient( $this->api_url );

    // Формируем массив параметров для запроса
    $params = array(
        'apiKey'    => $this->settings['api_key'],
        'method'    => $this->settings['shipping_method'],
        'fromIndex' => $this->settings['sender_postcode'],
        'toIndex'   => $package['destination']['postcode'],
        'weight'    => $package['cart_subtotal_weight'],
    );

    // Добавляем ограничения по размерам, если они заданы
    if ( ! empty( $this->settings['minimum_length'] ) || ! empty( $this->settings['maximum_length'] ) ||
         ! empty( $this->settings['maximum_width'] ) || ! empty( $this->settings['maximum_height'] ) ) {
        $params['mail'] = array(
            'dimension' => array(
                'length' => max( $this->settings['minimum_length'], $package['cart_subtotal_length'] ),
                'width'  => max( $this->settings['maximum_width'], $package['cart_subtotal_width'] ),
                'height' => max( $this->settings['maximum_height'], $package['cart_subtotal_height'] ),
            ),
        );
    }

    // Выполняем запрос к API Почты России
    $response = $client->__soapCall( 'calculate', array( $params ) );

    // Проверяем, что запрос выполнен успешно
    if ( ! is_object( $response ) || ! isset( $response->result ) || $response->result !== 'ok' ) {
        return;
    }

    // Получаем стоимость и срок доставки из ответа API Почты России
    $cost       = $response->price;
    $delivery_time = $response->deliveryTime;

    // Добавляем метод доставки в список доступных методов
    $method_data = array(
        'id'       => $this->id,
        'label'    => $this->title,
        'cost'     => $cost,
        'calc_tax' => 'per_order',
        'meta_data' => array(
            'delivery_time' => $delivery_time,
        ),
    );

    $this->add_rate( $method_data );
}

/**
 * Возвращает список классов доставки
 *
 * @return array Массив со списком классов доставки
 */
public function get_shipping_classes_options() {
    $shipping_classes = WC()->shipping->get_shipping_classes();
    $options = array(
        '' => __( 'No shipping class', 'shipping_pochta' ),
    );

    foreach ( $shipping_classes as $shipping_class ) {
        $options[ $shipping_class->term_id ] = $shipping_class->name;
    }

    return $options;
}
}

// Добавляем новый метод доставки в WooCommerce
function add_pochta_shipping_method( $methods ) {
$methods[] = 'WC_Pochta_Shipping_Method';
return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'add_pochta_shipping_method' );

// Добавляем настройки для метода доставки
function add_pochta_shipping_method_settings( $settings ) {
$settings[] = array(
'title' => __( 'Почта России', 'shipping_pochta' ),
'desc' => __( 'Configure the Почта России shipping method.', 'shipping_pochta' ),
'id' => 'shipping_pochta_settings',
'type' => 'title',
'desc_tip' => true,
);
$settings[] = array(
    'title'    => __( 'API Key', 'shipping_pochta' ),
    'id'       => 'pochta_api_key',
    'type'     => 'text',
    'desc'     => __( 'Enter your Почта России API key.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Sender Postcode', 'shipping_pochta' ),
    'id'       => 'pochta_sender_postcode',
    'type'     => 'text',
    'desc'     => __( 'Enter your postcode.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Shipping Method', 'shipping_pochta' ),
    'id'       => 'pochta_shipping_method',
    'type'     => 'select',
    'options'  => array(
        'ems'     => __( 'EMS', 'shipping_pochta' ),
        'standart' => __( 'Standart', 'shipping_pochta' ),
    ),
    'desc'     => __( 'Select the Почта России shipping method.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Maximum Weight', 'shipping_pochta' ),
    'id'       => 'pochta_maximum_weight',
    'type'     => 'number',
    'desc'     => __( 'Enter the maximum weight (in grams) for this shipping method.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Minimum Length', 'shipping_pochta' ),
    'id'       => 'pochta_minimum_length',
    'type'     => 'number',
    'desc'     => __( 'Enter the minimum length (in centimeters) for the package.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Maximum Length', 'shipping_pochta' ),
    'id'       => 'pochta_maximum_length',
    'type'     => 'number',
    'desc'     => __( 'Enter the maximum length (in centimeters) for the package.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Maximum Width', 'shipping_pochta' ),
    'id'       => 'pochta_maximum_width',
    'type'     => 'number',
    'desc'     => __( 'Enter the maximum width (in centimeters) for the package.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Maximum Height', 'shipping_pochta' ),
    'id'       => 'pochta_maximum_height',
    'type'     => 'number',
    'desc'     => __( 'Enter the maximum height (in centimeters) for the package.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'title'    => __( 'Handling Fee', 'shipping_pochta' ),
    'id'       => 'pochta_handling_fee',
    'type'     => 'number',
    'desc'     => __( 'Enter a handling fee (in rubles) to charge for this shipping method.', 'shipping_pochta' ),
    'desc_tip' => true,
);

$settings[] = array(
    'type' => 'sectionend',
    'id'   => 'shipping_pochta_settings',
);

return $settings;

add_filter( 'woocommerce_shipping_methods_settings', 'add_pochta_shipping_method_settings' );

// Рассчитываем стоимость и сроки доставки
function calculate_pochta_shipping( $package ) {
$destination = array(
'postcode' => $package['destination']['postcode'],
'country' => $package['destination']['country'],
);
$total_weight = 0;
foreach ( $package['contents'] as $item_id => $values ) {
$product = $values['data'];
$weight = $product->get_weight();
if ( ! $weight ) {
$weight = 1;
}
$total_weight += $weight * $values['quantity'];
}
$dimensions = array(
'length' => $package['package_length'],
'width' => $package['package_width'],
'height' => $package['package_height'],
);
$pochta_settings = get_option( 'woocommerce_shipping_pochta_settings' );
$pochta_api_key = $pochta_settings['pochta_api_key'];
$pochta_sender_postcode = $pochta_settings['pochta_sender_postcode'];
$pochta_shipping_method = $pochta_settings['pochta_shipping_method'];
$pochta_maximum_weight = $pochta_settings['pochta_maximum_weight'];
$pochta_minimum_length = $pochta_settings['pochta_minimum_length'];
$pochta_maximum_length = $pochta_settings['pochta_maximum_length'];
$pochta_maximum_width = $pochta_settings['pochta_maximum_width'];
$pochta_maximum_height = $pochta_settings['pochta_maximum_height'];
$pochta_handling_fee = $pochta_settings['pochta_handling_fee'];
$result = array(
'id' => 'pochta_shipping',
'label' => 'Почта России',
'cost' => 0,
'calc_tax' => 'per_order',
);
$pochta_client = new PochtaClient( $pochta_api_key );
$calculation_params = array(
'from' => $pochta_sender_postcode,
'to' => $destination['postcode'],
'weight' => $total_weight,
'length' => $dimensions['length'],
'width' => $dimensions['width'],
'height' => $dimensions['height'],
'type' => $pochta_shipping_method,
'service' => 'EMS',
'max_weight' => $pochta_maximum_weight,
'min_length' => $pochta_minimum_length,
'max_length' => $pochta_maximum_length,
'max_width' => $pochta_maximum_width,
'max_height' => $pochta_maximum_height,
);
$response = $pochta_client->calculateDelivery( $calculation_params );
if ( isset( $response['errors'] ) ) {
error_log( 'Error calculating Pochta shipping: ' . print_r( $response['errors'], true ) );
return false;
}
if ( isset( $response['total-rate'] ) ) {
$result['cost'] = $response['total-rate'] + $pochta_handling_fee;
}
if ( isset( $response['delivery-time'] ) ) {
$result['label'] .= ' - ' . $response['delivery-time'] . ' ' . __( 'days', 'shipping_pochta' );
}
return $result;
}
add_filter( 'woocommerce_shipping_methods', 'add_pochta_shipping_method' );

// Добавляем метод доставки в список методов
function add_pochta_shipping_method( $methods ) {
$methods['pochta_shipping'] = 'WC_Pochta_Shipping_Method';
return $methods;
}

// Добавляем стоимость и сроки доставки в корзину
function add_pochta_shipping_to_cart( $cart_object ) {
if ( ! is_admin() ) {
foreach ( $cart_object->get_shipping_packages() as $package_key => $package ) {
$shipping_method = $package['rates']['pochta_shipping'];
if ( isset( $shipping_method ) ) {
$label = $shipping_method->get_label();
$cost = $shipping_method->get_cost();
$cart_object->add_fee( $label, $cost, false );
}
}
}
}
add_action( 'woocommerce_cart_calculate_fees', 'add_pochta_shipping_to_cart' );
// Добавляем данные метода доставки в заказ
function save_pochta_shipping_data( $order ) {
$shipping_method = $order->get_shipping_method();
if ( strpos( $shipping_method, 'pochta_shipping' ) !== false ) {
$chosen_method = explode( ':', $shipping_method );
if ( isset( $chosen_method[1] ) ) {
$order->update_meta_data( 'Pochta Shipping Method', $chosen_method[1] );
}
$order->update_meta_data( 'Pochta Handling Fee', get_option( 'pochta_handling_fee' ) );
}
}
add_action( 'woocommerce_checkout_create_order', 'save_pochta_shipping_data' );

// Отображаем данные метода доставки в админке заказа
function display_pochta_shipping_data_in_admin( $order ) {
$shipping_method = $order->get_meta( 'Pochta Shipping Method', true );
$handling_fee = $order->get_meta( 'Pochta Handling Fee', true );
if ( $shipping_method ) {
echo '<p><strong>' . __( 'Pochta Shipping Method', 'shipping_pochta' ) . ':</strong> ' . $shipping_method . '</p>';
}
if ( $handling_fee ) {
echo '<p><strong>' . __( 'Pochta Handling Fee', 'shipping_pochta' ) . ':</strong> ' . wc_price( $handling_fee ) . '</p>';
}
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'display_pochta_shipping_data_in_admin', 10, 1 );
// Ограничиваем метод доставки для товаров, превышающих максимальный вес
function validate_pochta_shipping_method( $package ) {
$pochta_maximum_weight = get_option( 'pochta_maximum_weight' );
$cart_weight = $package['contents_weight'];
if ( $cart_weight > $pochta_maximum_weight ) {
return false;
}
return true;
}
add_filter( 'woocommerce_shipping_' . $pochta_shipping_method . '_is_available', 'validate_pochta_shipping_method' );

// Ограничиваем метод доставки для товаров, не вписывающихся в максимальные габариты
function validate_pochta_shipping_method_size( $package ) {
$pochta_maximum_length = get_option( 'pochta_maximum_length' );
$pochta_maximum_width = get_option( 'pochta_maximum_width' );
$pochta_maximum_height = get_option( 'pochta_maximum_height' );
$cart_items = $package['contents'];
foreach ( $cart_items as $item_id => $item ) {
$product = $item->get_product();
$product_length = $product->get_length();
$product_width = $product->get_width();
$product_height = $product->get_height();
if ( $product_length > $pochta_maximum_length || $product_width > $pochta_maximum_width || $product_height > $pochta_maximum_height ) {
return false;
}
}
return true;
}
add_filter( 'woocommerce_shipping_' . $pochta_shipping_method . '_is_available', 'validate_pochta_shipping_method_size' );
// Рассчитываем стоимость и срок доставки при оформлении заказа
function calculate_pochta_shipping_cost( $package ) {
$pochta_api_url = 'https://api.pochta.ru/v2';
$pochta_token = get_option( 'pochta_api_token' );
$pochta_sender_index = get_option( 'pochta_sender_index' );
$pochta_service_code = get_option( 'pochta_service_code' );
$pochta_handling_fee = get_option( 'pochta_handling_fee' );
$pochta_packaging_type = get_option( 'pochta_packaging_type' );
// Получаем данные о получателе из формы оформления заказа
$shipping_country = $package['destination']['country'];
$shipping_postcode = $package['destination']['postcode'];
$shipping_state = $package['destination']['state'];
$shipping_city = $package['destination']['city'];
$shipping_address = $package['destination']['address'];

// Получаем данные о отправителе
$sender_index = $pochta_sender_index;

// Получаем данные о товарах в корзине
$cart_items = $package['contents'];
$cart_total = $package['contents_cost'];

// Считаем вес корзины
$cart_weight = $package['contents_weight'];

// Считаем стоимость доставки с помощью API Почты России
$request_data = array(
    'object' => array(
        'weight' => $cart_weight * 1000, // Вес в граммах
        'index-from' => $sender_index,
        'index-to' => $shipping_postcode,
        'mail-category' => $pochta_service_code,
        'pack-type' => $pochta_packaging_type,
        'fragile' => false,
        'envelope-type' => 'B4',
        'value' => $cart_total,
        'transport-type' => 'AVIA', // Тип транспортировки (авиа, ж/д, авто)
    ),
);
$request_headers = array(
    'Content-Type' => 'application/json',
    'Authorization' => 'AccessToken ' . $pochta_token,
);
$response = wp_remote_post( $pochta_api_url . '/price', array(
    'headers' => $request_headers,
    'body' => json_encode( $request_data ),
) );
if ( is_wp_error( $response ) ) {
    return;
}
$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
if ( isset( $response_body['error'] ) ) {
    return;
}

$pochta_shipping_cost = $response_body['total-rate'] + $pochta_handling_fee;
$pochta_shipping_days = $response_body['delivery-time'] + 1; // Плюс один день на обработку заказа

// Обновляем стоимость и срок доставки в заказе
WC()->session->set( 'pochta_shipping_cost', $pochta_shipping_cost );
WC()->session->set( 'pochta_shipping_days', $pochta_shipping_days );
}
add_action( 'woocommerce_review_order_before_cart_contents', 'calculate_pochta_shipping_cost', 10, 1 );
// Добавляем метод доставки в список доступных методов доставки
function add_pochta_shipping_method( $methods ) {
$methods['pochta_shipping'] = 'WC_Pochta_Shipping_Method';
return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'add_pochta_shipping_method' );

// Определяем класс метода доставки
class WC_Pochta_Shipping_Method extends WC_Shipping_Method {
public function __construct() {
$this->id = 'pochta_shipping';
$this->method_title = 'Почта России';
$this->method_description = 'Доставка Почтой России';
$this->supports = array(
'shipping-zones',
'instance-settings',
);
$this->init_form_fields();
$this->init_settings();
$this->enabled = $this->get_option( 'enabled' );
$this->title = $this->get_option( 'title' );
$this->handling_fee = $this->get_option( 'handling_fee' );
$this->default_service_code = $this->get_option( 'default_service_code' );
$this->default_packaging_type = $this->get_option( 'default_packaging_type' );
$this->default_delivery_time = $this->get_option( 'default_delivery_time' );
$this->default_price = $this->get_option( 'default_price' );
$this->default_weight_limit = $this->get_option( 'default_weight_limit' );
$this->default_dimensions_limit = $this->get_option( 'default_dimensions_limit' );
    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
}

public function calculate_shipping( $package = array() ) {
    $pochta_shipping_cost = WC()->session->get( 'pochta_shipping_cost' );
    $pochta_shipping_days = WC()->session->get( 'pochta_shipping_days' );

    $rate = array(
        'id' => $this->id,
        'label' => $this->title,
        'cost' => $pochta_shipping_cost,
        'calc_tax' => 'per_order',
        'meta_data' => array(
            'Срок доставки' => $pochta_shipping_days . ' дн.',
        ),
    );

    $this->add_rate( $rate );
}

public function init_form_fields() {
    $this->instance_form_fields = array(
        'enabled' => array(
            'title' => __( 'Включить/Отключить', 'woocommerce' ),
            'type' => 'checkbox',
            'label' => __( 'Включить доставку Почтой России', 'woocommerce' ),
            'default' => 'yes',
        ),
        'title' => array(
            'title' => __( 'Заголовок', 'woocommerce' ),
            'type' => 'text',
            'description' => __( 'Это название, которое пользователь видит во время оформления заказа.', 'woocommerce' ),
            'default' => __( 'Почта России', 'woocommerce' ),
            'desc_tip' => true,
        ),
        'handling_fee' => array(
            'title' => __( 'Стоимость обработки', 'woocommerce' ),
            'type' => 'price',
            'description' => __( 'Эта стоимость добавляется к стоимости доставки. Оставьте 0, если не нужно.', 'woocommerce' ),
            'default' => 0,
            'desc_tip' => true,
        ),
        'default_service_code' => array(
            'title' => __( 'Код услуги', 'woocommerce' ),
            'type' => 'text',
            'description' => __( 'Код услуги Почты России, который будет использоваться для расчета стоимости и сроков доставки по умолчанию. Если не знаете, оставьте пустым.', 'woocommerce' ),
            'default' => '',
            'desc_tip' => true,
        ),
        'default_packaging_type' => array(
            'title' => __( 'Тип упаковки', 'woocommerce' ),
            'type' => 'text',
            'description' => __( 'Тип упаковки, который будет использоваться для расчета стоимости и сроков доставки по умолчанию. Если не знаете, оставьте пустым.', 'woocommerce' ),
            'default' => '',
            'desc_tip' => true,
        ),
        'default_delivery_time' => array(
            'title' => __( 'Срок доставки по умолчанию', 'woocommerce' ),
            'type' => 'text',
            'description' => __( 'Срок доставки в днях, который будет использоваться для расчета стоимости и сроков доставки по умолчанию. Если не знаете, оставьте пустым.', 'woocommerce' ),
            'default' => '',
            'desc_tip' => true,
        ),
        'default_price' => array(
            'title' => __( 'Стоимость доставки по умолчанию', 'woocommerce' ),
            'type' => 'text',
            'description' => __( 'Стоимость доставки по умолчанию, которая будет использоваться для расчета стоимости и сроков доставки по умолчанию. Если не знаете, оставьте пустым.', 'woocommerce' ),
            'default' => '',
            'desc_tip' => true,
        ),
        'default_weight_limit' => array(
            'title' => __( 'Ограничение веса по умолчанию', 'woocommerce' ),
            'type' => 'text',
            'description' => __( 'Максимальный вес посылки для данной доставки, который будет использоваться для расчета стоимости и сроков доставки по умолчанию. Если не знаете, оставьте пустым.', 'woocommerce' ),
            'default' => '',
            'desc_tip' => true,
        ),

        'default_dimensions_limit' => array(
            'title' => __( 'Ограничение размеров по умолчанию', 'woocommerce' ),
            'type' => 'title',
            'description' => __( 'Максимальные габариты посылки для данной доставки (длина, ширина и высота)', 'woocommerce' ),
        ),
        'default_max_length' => array(
            'title' => __( 'Максимальная длина', 'woocommerce' ),
            'type' => 'number',
            'description' => __( 'Максимальная длина посылки', 'woocommerce' ),
            'default' => '100',
            'desc_tip' => true,
        ),
        'default_max_width' => array(
            'title' => __( 'Максимальная ширина', 'woocommerce' ),
            'type' => 'number',
            'description' => __( 'Максимальная ширина посылки', 'woocommerce' ),
            'default' => '50',
            'desc_tip' => true,
        ),
        'default_max_height' => array(
            'title' => __( 'Максимальная высота', 'woocommerce' ),
            'type' => 'number',
            'description' => __( 'Максимальная высота посылки', 'woocommerce' ),
            'default' => '30',
            'desc_tip' => true,
        ));
    	}
	}
}
