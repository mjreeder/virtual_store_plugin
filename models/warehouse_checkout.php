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
			self::dcvs_export_attribute_terms( $table_prefix );
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
			$delete_affected_rows =  $wpdb->query( $delete_sql );

			$insert_sql = $wpdb->prepare( "INSERT INTO wp_" . $table_prefix . "_woocommerce_attribute_taxonomies (attribute_name, attribute_label, attribute_type, attribute_orderby, attribute_public) SELECT attribute_name, attribute_label, attribute_type, attribute_orderby, attribute_public FROM wp_woocommerce_attribute_taxonomies WHERE attribute_name NOT IN (SELECT attribute_name FROM wp_" . $table_prefix . "_woocommerce_attribute_taxonomies)", [ ] );
			$insert_affected_rows = $wpdb->query( $insert_sql );

			if (($insert_affected_rows !== false && $delete_affected_rows !== false) && ($delete_affected_rows > 0 || $insert_affected_rows > 0)) {
				$array_attribute_objects = self::dcvs_create_array_of_attribute_objects( $table_prefix );
				update_blog_option( $table_prefix, "_transient_wc_attribute_taxonomies", $array_attribute_objects );
			}

		}

		function dcvs_create_array_of_attribute_objects( $table_prefix ) {
			global $wpdb;

			$sql = $wpdb->prepare("SELECT * FROM wp_" . $table_prefix . "_woocommerce_attribute_taxonomies", []);
			$attributes = $wpdb->get_results($sql, ARRAY_A);

			$array_attribute_objects = array();

			foreach ($attributes as $attribute) {
				$object = new stdClass;
				$object->attribute_id = $attribute['attribute_id'];
				$object->attribute_name = $attribute['attribute_name'];
				$object->attribute_label = $attribute['attribute_label'];
				$object->attribute_type = $attribute['attribute_type'];
				$object->attribute_orderby = $attribute['attribute_orderby'];
				$object->attribute_public = $attribute['attribute_public'];
				$array_attribute_objects[] = $object;
			}

			return $array_attribute_objects;
		}

//		TODO: UPDATE TERM COUNTS

		function dcvs_export_attribute_terms( $table_prefix ) {
			
			self::dcvs_create_new_attribute_terms( $table_prefix );

			self::dcvs_delete_old_attribute_terms( $table_prefix );

		}

		function dcvs_create_new_attribute_terms( $table_prefix ) {
			global $wpdb;

			$sql = $wpdb->prepare("SELECT wp_terms.*,wp_term_taxonomy.taxonomy,wp_termmeta.meta_key, wp_termmeta.meta_value  FROM wp_terms JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id JOIN wp_termmeta ON wp_terms.term_id = wp_termmeta.term_id
				WHERE wp_terms.term_id IN (SELECT term_id FROM wp_term_taxonomy WHERE taxonomy IN (SELECT CONCAT('pa_',attribute_name) FROM wp_woocommerce_attribute_taxonomies)) AND wp_terms.name NOT IN (SELECT name FROM wp_". $table_prefix . "_terms);", []);
			$terms = $wpdb->get_results($sql, ARRAY_A);

			foreach ($terms as $term) {
				$wpdb->insert( "wp_". $table_prefix . "_terms", [ "name" => $term['name'], "slug" => $term['slug'] ] );
				$term_id = $wpdb->insert_id;
				$wpdb->insert( "wp_". $table_prefix . "_termmeta", [ "term_id" => $term_id, "meta_key" => $term['meta_key'] ] );
				$wpdb->insert( "wp_". $table_prefix . "_term_taxonomy", [ "term_id" => $term_id, "taxonomy" => $term['taxonomy'] ] );
			}
		}

		function dcvs_delete_old_attribute_terms( $table_prefix ) {
			global $wpdb;

			$sql = $wpdb->prepare("SELECT wp_". $table_prefix . "_terms.term_id FROM wp_". $table_prefix . "_terms 
				JOIN wp_". $table_prefix . "_term_taxonomy ON wp_". $table_prefix . "_terms.term_id = wp_". $table_prefix . "_term_taxonomy.term_id 
				WHERE wp_". $table_prefix . "_terms.name NOT IN (SELECT name FROM wp_terms)", []);
			$old_terms = $wpdb->get_results($sql, ARRAY_A);

			foreach ($old_terms as $old_term) {
				$delete_term_sql = $wpdb->prepare( "DELETE FROM wp_" . $table_prefix . "_terms WHERE term_id ="  . esc_sql( $old_term['term_id'] ), [ ] );
				$wpdb->query( $delete_term_sql );

				$delete_termmeta_sql = $wpdb->prepare( "DELETE FROM wp_" . $table_prefix . "_termmeta WHERE term_id ="  . esc_sql( $old_term['term_id'] ), [ ] );
				$wpdb->query( $delete_termmeta_sql );

				$delete_term_tax_sql = $wpdb->prepare( "DELETE FROM wp_" . $table_prefix . "_term_taxonomy WHERE term_id ="  . esc_sql( $old_term['term_id'] ), [ ] );
				$wpdb->query( $delete_term_tax_sql );
			}
		}

		function dcvs_export_variations() {

		}

		function dcvs_export_products() {

		}

	}

	new WarehouseCheckout();

}