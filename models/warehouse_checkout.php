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
			self::dcvs_export_attributes( $table_prefix );
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

		function dcvs_export_attributes( $table_prefix ) {
			global $wpdb;
			$delete_sql = $wpdb->prepare( "DELETE FROM wp_" . $table_prefix . "_woocommerce_attribute_taxonomies WHERE attribute_name NOT IN (SELECT attribute_name FROM wp_woocommerce_attribute_taxonomies)", [ ] );
			$wpdb->query( $delete_sql );
			$insert_sql = $wpdb->prepare( "INSERT INTO wp_" . $table_prefix . "_woocommerce_attribute_taxonomies (attribute_name, attribute_label, attribute_type, attribute_orderby, attribute_public) SELECT attribute_name, attribute_label, attribute_type, attribute_orderby, attribute_public FROM wp_woocommerce_attribute_taxonomies WHERE attribute_name NOT IN (SELECT attribute_name FROM wp_" . $table_prefix . "_woocommerce_attribute_taxonomies)", [ ] );
			$wpdb->query( $insert_sql );
		}

		function dcvs_export_variations() {

		}

		function dcvs_export_products() {

		}

	}

	new WarehouseCheckout();

}