<?php
/**
 *
 * The WC_State_Tax_Report_Admin class has functions for displying admin reports the plugin.
 *
 * @package WC_State_Tax_Report
 * @since 1.0.0
 */

/**
 * WC_State_Tax_Report_Admin class.
 */
class WC_State_Tax_Report_Admin {
    
    
    /**
     * Singleton class instance.
     *
     * @var WC_State_Tax_Report_Admin
     */
    private static $instance;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_init', array( &$this, 'init') );
        add_filter( 'woocommerce_admin_reports', array( &$this, 'admin_add_report_orders_tab' ) );
    }
    
	/**
	 * init
	 */
    public function init() {
        wp_enqueue_script('jquery-ui-datepicker');

        wp_enqueue_script( 'state-tax-plugin-admin', WC_STATE_TAX_REPORT_ASSETS_URL . "js/admin.js", array(), WC_STATE_TAX_REPORT_VERSION, true ) ;

        add_action( 'admin_footer', array( &$this, 'add_datepicker' ), 10 ) ;

        add_filter( 'woocommerce_form_field', array( &$this, 'modify_state_field' ), 10, 4 ) ;

    }

    /**
     * Modify the state field
     */
    public function modify_state_field( $field, $key, $args, $value ) {
        if ( $key === 'state' ) {
            
            $field = '' ;

            if ( $args['required'] ) {
                // hidden inputs are the only kind of inputs that don't need an `aria-required` attribute.
                // checkboxes apply the `custom_attributes` to the label - we need to apply the attribute on the input itself, instead.
                if ( ! in_array( $args['type'], array( 'hidden', 'checkbox' ), true ) ) {
                    $args['custom_attributes']['aria-required'] = 'true';
                }
    
                $args['class'][] = 'validate-required';
                $required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
            } else {
                $required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
            }

            $custom_attributes         = array();
            $args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

            if ( $args['maxlength'] ) {
                $args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
            }

            if ( $args['minlength'] ) {
                $args['custom_attributes']['minlength'] = absint( $args['minlength'] );
            }

            if ( ! empty( $args['autocomplete'] ) ) {
                $args['custom_attributes']['autocomplete'] = $args['autocomplete'];
            }

            if ( true === $args['autofocus'] ) {
                $args['custom_attributes']['autofocus'] = 'autofocus';
            }

            if ( $args['description'] ) {
                $args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
            }

            if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
                foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }

            $label_id        = $args['id'];
            $sort            = $args['priority'] ? $args['priority'] : '';
            $field_container = '<span class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</span>';
            
            /* Get country this state field is representing */
            $for_country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $key ? 'billing_country' : 'shipping_country' );
            $states      = WC()->countries->get_states( $for_country );

            if ( is_array( $states ) && empty( $states ) ) {

                $field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';

                $field .= '<input type="hidden" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" readonly="readonly" data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';

            } elseif ( ! is_null( $for_country ) && is_array( $states ) ) {
                $data_label = ! empty( $args['label'] ) ? 'data-label="' . esc_attr( $args['label'] ) . '"' : '';

                $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_html__( 'Select an option&hellip;', 'woocommerce' ) ) . '"  data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . $data_label . '>
                    <option value="">' . esc_html__( 'Select a State&hellip;', 'woocommerce' ) . '</option>';

                foreach ( $states as $ckey => $cvalue ) {
                    $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
                }

                $field .= '</select>';

            } else {

                $field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';

            }

            if ( ! empty( $field ) ) {
                $field_html = '';
    
                if ( $args['label'] && 'checkbox' !== $args['type'] ) {
                    $field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . wp_kses_post( $args['label'] ) . $required . '</label>';
                }
    
                $field_html .= '<span class="woocommerce-input-wrapper">' . $field;
    
                if ( $args['description'] ) {
                    $field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
                }
    
                $field_html .= '</span>';
    
                $container_class = esc_attr( implode( ' ', $args['class'] ) );
                $container_id    = esc_attr( $args['id'] ) . '_field';
                $field           = sprintf( $field_container, $container_class, $container_id, $field_html );
            }
        }

        return $field;

    }
    
	/**
	 * add report tab
	 */
    public function admin_add_report_orders_tab( $reports ) { 
 
        $array = array(
            'sales_by_state' => array(
                'title' => 'Sales by State',
                'description' => '',
                'hide_title' => 1,
                'callback' => array( &$this, 'yearly_sales_by_state')
            )
        );
        
        if( isset( $reports['orders'] ) && is_array($reports['orders']['reports']) ) {
            $reports['orders']['reports'] = array_merge($reports['orders']['reports'], $array);
        }
        else {
            $reports['orders']['reports'] = $array ;
        }
        
        return $reports; 
    }

	/**
	 * year sales
	 */
    public function yearly_sales_by_state() {

        $current_year = date("Y") ; 
        $totalWithTaxShip = 0 ; 
        $totalCartValue = 0 ;
        $totalTaxCollected = 0 ;
        $totalNoTaxNoShip = 0 ;

        // db query
        $date_query = false ;
        $from_date = false ;
        $to_date = false ;
        
        if(isset($_GET['fromDate']) || isset($_GET['toDate'])) {
            $date_query = array( array('inclusive' => true) ) ;
            if(isset($_GET['fromDate'])) {
                $from_date = sanitize_text_field($_GET['fromDate']) ;
                if($this->validate_date($from_date)) {
                    $date_query[0]['after'] = $from_date ;
                }
            }
            
            if(isset($_GET['toDate'])) {
                $to_date = sanitize_text_field($_GET['toDate']) ;
                if($this->validate_date($to_date)) {
                    $date_query[0]['before'] = $to_date ;
                }
            }
            
            if($from_date == false && $to_date == false) {
                $date_query = false ;
            }
        }
        
        $args = [
            'post_type' => 'shop_order',
            'posts_per_page' => '-1',
            'post_status' => ['wc-completed', 'wc-processing']
        ];
        
        if($date_query) {
            $args['date_query'] = $date_query ;
        }
        else {
            $args['year'] = $current_year ;
        }
        
        $orders = wc_get_orders($args) ;
        
        $selected_state = ( isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '' ) ;
        
        // report form
        $states = woocommerce_form_field('state', array(
                    'type'       => 'state',
                    'return'     => true,
                    'country'    => 'US',
                    'placeholder'    => __('Select a State')
                    ), esc_html($selected_state)
                );
        
        $form = '<form class="sales-tax-form-filter" method="GET">' ;
        
        foreach($_GET as $key => $val) {
            $form .= '<input type="hidden" name="' . esc_html($key) . '" value="' . esc_html($val) . '" />';
        }

        $form .= '<input name="fromDate" id="fromDate" type="text" value="' . esc_html($from_date) . '" placeholder="Order Date From" />' ;
        $form .= '<input name="toDate" id="toDate" type="text" value="' . esc_html($to_date) . '" placeholder="Order Date To" />' ;
        $form .= $states ;
        $form .= '<input type="submit" class="button" value="FILTER" />' ;
        
        $form .= '</form>' ;
        
        echo '<p>' . $form . '</p>' ;
        
        // start report table
        $rows = array() ;
        $tax_rate_array = array() ;
        
        // loop every order in the query
        foreach ($orders as $order => $value) {

            // get order datas
            $order_id = $value->ID;
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();
            $cart_value = number_format( (float) $order->get_total() - $order->get_total_tax() - $order->get_shipping_total() - $order->get_shipping_tax(), wc_get_price_decimals(), '.', '' );
            
            //get order tax rate
            $tax_rates = $order->get_items('tax') ;
            $tax_rate_code = 'NONE (0%)' ;
            
            if( is_array( $tax_rates ) ) {
                $rate_data = end( $tax_rates ) ;
                
                if($rate_data) {
                    $data = $rate_data->get_data() ;
                    $tax_rate_code = $data['rate_code'] . ' (' . $data['rate_percent'] . '%)' ;
                }
            }
            
            $tax_rate_key = preg_replace('/ /', '-', $tax_rate_code) ;
            $tax_rate_array[$tax_rate_key]['rate_total'] += $order->get_total_tax() ;
            $tax_rate_array[$tax_rate_key]['sales_total'] += $cart_value ;
            
            // if state filter
            if($_GET['state']){ 

                // if state filter match
                if ( $order_data['shipping']['state'] === $_GET['state'] ) {
                    
                    $rows[] = array(
                        'state' => $order_data['shipping']['state'],
                        'order_id' => $order_id,
                        'date' => date('Y-m-d g:ia', strtotime($order->order_date)),
                        'total' => $order->get_total(),
                        'shipping' => $order->get_shipping_total(),
                        'shipping_tax' => $order->get_shipping_tax(),
                        'tax_rate' => $tax_rate_code,
                        'tax' => $order->get_total_tax(),
                        'cart_value' => $cart_value
                    ) ;
                    
                    // tally total order values
                    $totalWithTaxShip += $order->get_total();
                    $totalNoTaxNoShip += $cart_value;
                    $totalTaxCollected += $order->get_total_tax();
                }
                
            } else {
                // all states report
                $rows[] = array(
                    'state' => $order_data['shipping']['state'],
                    'order_id' => $order_id,
                    'date' => date('Y-m-d g:ia', strtotime($order->order_date)),
                    'total' => $order->get_total(),
                    'shipping' => $order->get_shipping_total(),
                    'shipping_tax' => $order->get_shipping_tax(),
                    'tax_rate' => $tax_rate_code,
                    'tax' => $order->get_total_tax(),
                    'cart_value' => $cart_value
                ) ;

                // tally total order values
                $totalWithTaxShip += $order->get_total();
                $totalNoTaxNoShip += $cart_value;
                $totalTaxCollected += $order->get_total_tax();
            }
            
        }
        
        include WC_STATE_TAX_REPORT_TEMPLATES . 'tax_table.php' ;

        
    }
    
    /**
	 * Add settings link on plugin page.
	 *
	 * @param array $links An array of existing links for the plugin.
	 * @return array The new array of links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=wc_state_tax_report_settings">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
    
    /**
	 * Add link to admin menu
	 */
	public function add_page_to_menu() {
		global $wc_state_tax_report_hook ;
		$wc_state_tax_report_hook = 'wc_state_tax_report' ;

		add_menu_page(
			'State Taxes',
			'State Taxes',
			'manage_options',
			$wc_state_tax_report_hook,
			array( &$this, 'render_page' ),
			//MYSTYLE_ASSETS_URL . '/images/mystyle-icon.png',
			'56'
		);

		add_submenu_page(
			$wc_state_tax_report_hook,
			'Reports',
			'Reports',
			'manage_options',
			$wc_state_tax_report_hook,
			array( &$this, 'render_page' ),
			99
		);

	}
    
    public function add_datepicker(){ 
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('#fromDate').datepicker({
                    dateFormat : 'yy-mm-dd'
                });
                jQuery('#toDate').datepicker({
                    dateFormat : 'yy-mm-dd'
                });
            });
        </script>
        <?php
    }
    
    public function validate_date($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    
    public function render_page() {
        // placeholder, unused for now
    }
    
    /**
     * Gets the singleton instance.
     *
     * @return WC_State_Tax_Report_Admin Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}