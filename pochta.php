add_filter( 'woocommerce_shipping_methods', function( $methods ) {
    $methods[] = 'WC_Pochta_Shipping_Method';
    return $methods;
} );

class WC_Pochta_Shipping_Method extends WC_Shipping_Method {
    /**
     * @var array
     */
    private $settings;

    public function __construct() {
        $this->id                 = 'pochta';
        $this->title              = 'Почта России';
        $this->method_description = 'Доставка Почтой России';
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
            'pochta_delivery_type' => array(
                'title'       => 'Тип доставки',
                'type'        => 'select',
                'options'     => array(
                    'pvz' => 'Доставка до пункта выдачи',
                    'courier' => 'Курьерская доставка',
                ),
                'description' => 'Выберите тип доставки',
                'default'     => 'pvz',
            ),
            'pochta_delivery_price' => array(
                'title'       => 'Стоимость доставки',
                'type'        => 'number',
                'description' => 'Введите стоимость доставки',
                'default'     => '0',
                'desc_tip'    => true,
            ),
        );
    }

    public function calculate_shipping( $package = array() ) {
        if ( $this->settings['pochta_delivery_type'] === 'pvz' ) {
            $cost = $this->calculate_shipping_to_pvz( $package );
        } else {
            $cost = $this->calculate_shipping_by_weight( $package );
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
    }

    private function calculate_shipping_to_pvz( $package ) {
        // Здесь должен быть код расчёта стоимости доставки до пункта выдачи
        return $this->settings['pochta_delivery_price'];
    }

    private function calculate_shipping_by_weight( $package ) {
        $weight = $this->get_package_weight( $package );
        $cost = $weight * $this->settings['pochta_delivery_price'];
        return $cost;
    }

    private function get_package_weight( $package ) {
        $weight = 0;
        foreach ( $package['contents'] as $item ) {
            if ( $item['data']->get_weight() ) {
                $weight += $item['quantity'] * $item['data']->get_weight();
            }
        }
        return $
