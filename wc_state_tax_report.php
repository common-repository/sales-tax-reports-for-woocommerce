<?php
/**
 * Plugin Name: Sales Tax Reports For WooCommerce
 * Plugin URI: http://www.mystyleplatform.com
 * Description: State and Sales Tax Reports For WooCommerce Plugin.
 * Version: 1.0.9
 * WC requires at least: 2.2.0
 * WC tested up to: 9.3.3
 * Author: mystyleplatform
 * Author URI: www.mystyleplatform.com
 * License: GPL v3
 *
 * Sales Tax Reports For WooCommerce
 * Copyright (c) 2024 MyStyle <contact@mystyleplatform.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package WC_State_Tax_report
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_State_Tax_Report' ) ) :

	/**
	 * Main WC_State_Tax_Report Class.
	 */
	final class WC_State_Tax_Report {
        
        
        /**
		 * Singleton class instance.
		 *
		 * @var WC_State_Tax_Report
		 */
		private static $instance;

		/**
		 * Our WooCommerce interface.
		 *
		 * @var WC_State_Tax_Report_WC_Interface
		 */
		private $wc;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();
			$this->init_singletons();
		}
        
        private function define_constants() {
            
            define( 'WC_STATE_TAX_REPORT_PATH', plugin_dir_path( __FILE__ ) );
			define( 'WC_STATE_TAX_REPORT_INCLUDES', WC_STATE_TAX_REPORT_PATH . 'includes/' );
			define( 'WC_STATE_TAX_REPORT_BASENAME', plugin_basename( __FILE__ ) );
			define( 'WC_STATE_TAX_REPORT_URL', plugins_url( '/', __FILE__ ) );
			define( 'WC_STATE_TAX_REPORT_ASSETS_URL', WC_STATE_TAX_REPORT_URL . 'assets/' );
			define( 'WC_STATE_TAX_REPORT_TEMPLATES', WC_STATE_TAX_REPORT_PATH . 'templates/' );

			if ( ! defined( 'WC_STATE_TAX_REPORT_VERSION' ) ) {
				define( 'WC_STATE_TAX_REPORT_VERSION', '1.0.9' );
			}
			if ( ! defined( 'WC_STATE_TAX_REPORT_TEMPLATE_DEBUG_MODE' ) ) {
				define( 'WC_STATE_TAX_REPORT_TEMPLATE_DEBUG_MODE', false );
			}

			define( 'WC_STATE_TAX_REPORT_OPTIONS_NAME', 'wc_state_tax_report_options' );
			define( 'WC_STATE_TAX_REPORT_NOTICES_NAME', 'wc_state_tax_report_notices' );
			define( 'WC_STATE_TAX_REPORT_NOTICES_DISMISSED_NAME', 'wc_state_tax_report_notices_dismissed' );
            
        }
        
        /**
		 * Include required core files used in admin and on the frontend.
		 */
        private function includes() {
            
            require_once WC_STATE_TAX_REPORT_INCLUDES . 'class-wc-state-tax-report-admin.php' ;
            
        }

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks()
		{

			add_action('init',
				array($this, 'check_version'),
				10,
				0
			);

			// Add the action before_woocommerce_init here
			add_action('before_woocommerce_init', array($this, 'before_woocommerce_init_action'));
		}

		/**
		 * Action to be executed before WooCommerce init.
		 */
		public function before_woocommerce_init_action()
		{
			if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
			}
		}
        
        /**
		 * Init our singletons (registers hooks, etc).
		 */
		private function init_singletons() {
            WC_State_Tax_Report_Admin::get_instance() ;
        }
        
        /**
		 * Sets the current version against the version in the db and handles any
		 * updates.
		 *
		 * @todo Add unit testing for this function.
		 */
		public function check_version() {
			$options      = get_option( WC_STATE_TAX_REPORT_OPTIONS_NAME, array() );
			$data_version = ( array_key_exists( 'version', $options ) ) ? $options['version'] : null;
			if ( WC_STATE_TAX_REPORT_VERSION !== $data_version ) {
				$options['version'] = WC_STATE_TAX_REPORT_VERSION;
				update_option( WC_STATE_TAX_REPORT_OPTIONS_NAME, $options );
				if ( ! is_null( $data_version ) ) {  // Skip if not an upgrade.
					// Run the upgrader.
					
				}
			}
		}

        
        /**
         * Gets the singleton instance.
         *
         * @return WC_State_Tax_Report Returns the singleton instance of
         * this class.
         */
        public static function get_instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }
        
    }
	
endif;

/**
 * Main instance of WC_State_Tax_Report.
 *
 * Returns the main instance of WC_State_Tax_Report to prevent the need to use globals.
 *
 * @return WC_State_Tax_Report
 * @codingStandardsIgnoreStart (ignoring incorrect case function name).
 */
function WC_State_Tax_Report() {
	// @codingStandardsIgnoreStart
	return WC_State_Tax_Report::get_instance();
}

// Init the MyStyle singleton.
WC_State_Tax_Report();