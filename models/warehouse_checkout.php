<?php

if ( ! class_exists( 'WarehouseCheckout' ) ) {

	class WarehouseCheckout {

		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
		}

		public function init() {
			add_action( "woocommerce_checkout_order_processed", array( $this, "dcvs_export_cart" ) );
		}

		function dcvs_export_cart( $order_id ) {
			$table_prefix = self::dcvs_get_table_prefix();
			self::dcvs_export_attributes();
			self::dcvs_export_variations();
			self::dcvs_export_products();
		}

		function dcvs_get_table_prefix() {
			global $wpdb;
			$user_id = get_current_user_id();
			$business_url = $wpdb->get_var( "SELECT url FROM dcvs_business WHERE id = (SELECT business_id FROM dcvs_user_business WHERE user_id = " . esc_sql( $user_id ) . ")" );
			preg_match( "/.*(\\/.*\\/)$/", $business_url, $blog_match );
			$blog_path = $blog_match[1];
			$table_prefix = $wpdb->get_var( "SELECT blog_id FROM wp_blogs WHERE path = '" . esc_sql( $blog_path ) . "'" );
			return $table_prefix;
		}

		function dcvs_export_attributes() {

		}

		function dcvs_export_variations() {

		}

		function dcvs_export_products() {

		}

	}

	new WarehouseCheckout();

}