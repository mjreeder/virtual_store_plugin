<?php

require_once(ABSPATH . 'wp-admin/includes/image.php');

if ( ! class_exists( 'WarehouseCheckout' ) ) {

	class WarehouseCheckout {

		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
		}

		public function init() {
			add_action( "woocommerce_checkout_order_processed", array( $this, "dcvs_export_cart" ) );
		}

		function dcvs_export_cart( $order_id ) {
			if (get_current_blog_id() == 1) {
				$table_prefix = self::dcvs_get_table_prefix();
				self::dcvs_export_attributes( $table_prefix );
				self::dcvs_export_attribute_terms( $table_prefix );
				self::dcvs_export_products( $order_id, $table_prefix );
				self::dcvs_add_warehouse_purchase($order_id);
			} else if (self::dcvs_get_user_blog_id(get_current_user_id()) != get_current_blog_id()) {
				self::dcvs_add_business_purchase( $order_id );
			}

		}

		function dcvs_get_table_prefix() {
			global $wpdb;

			$user_id = get_current_user_id();
			$business_url = $wpdb->get_var( "SELECT url FROM dcvs_business WHERE id = (SELECT business_id FROM dcvs_user_business WHERE user_id = " . esc_sql( $user_id ) . ")" );
			$parsed_url = parse_url( $business_url );
			$blog_path = $parsed_url['path'];
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

		// TODO: CLEAN UP BELOW FUNCTIONS

		function dcvs_export_products( $order_id, $table_prefix ) {
			global $wpdb;

			$business_id = dcvs_business_id_from_user(get_current_user_id());

			$order_items = WC()->order_factory->get_order($order_id)->get_items();

			$post_ids = array();
			$new_ids = array();

			$tracked_objects = array();

			$product_id = -1;

			foreach ($order_items as $order_item) {
				$temp_product_id = $order_item['item_meta']['_product_id'][0];
				$temp_variation_id = $order_item['item_meta']['_variation_id'][0];
				$temp_quantity = $order_item['item_meta']['_qty'][0];

				if (!in_array( $temp_product_id, $post_ids )) {
					$sql = $wpdb->prepare("SELECT * FROM wp_posts WHERE post_type = 'attachment' and post_parent = '%d'", [$temp_product_id]);
					$attachments = $wpdb->get_results($sql, ARRAY_A);

					$default_thumbnail_id = get_post_thumbnail_id($temp_product_id);

					$post_ids[] = $temp_product_id;
					$product_id = self::dcvs_add_new_product( $temp_product_id, $table_prefix, $temp_quantity  );
					$new_ids[] = $product_id;

					if (!self::dcvs_check_warehouse_business_product_exists($business_id, $temp_product_id, $product_id) && $temp_variation_id == '0') {
						self::dcvs_create_warehouse_business_product($business_id, $temp_product_id, $product_id);
					}


					$tracking_object =  new stdClass();
					$tracking_object->product_id = $product_id;
					$tracking_object->variation_ids = array();
					$tracking_object->thumbnail_ids = array();
					$tracking_object->default_thumbnail_id = $default_thumbnail_id;
					$tracking_object->variation_post_meta_array = array();
					$tracking_object->variation_attachments_array = array();
					$tracking_object->quantities = array();
					$tracking_object->product_attributes = array();
					$tracking_object->attachments = $attachments;

					$product = wc_get_product($temp_product_id);
					$product_post_meta = get_post_meta($product_id);
					$tracking_object->product_post_meta = $product_post_meta;

					$attributes = $product->get_attributes();
					foreach ( $attributes as $attribute ) {
						$lowercase_attribute_name = strtolower( $attribute['name'] );
						$tracking_object->product_attributes[$lowercase_attribute_name] = array();
					}


					$tracked_objects[$product_id] = $tracking_object;

				}

				if (!in_array( $temp_variation_id, $tracked_objects[$product_id]->variation_ids  ) && $temp_variation_id != '0' && $product_id != -1) {

					$tracked_objects[$product_id]->variation_ids[] = $temp_variation_id;

					$tracked_objects[$product_id]->thumbnail_ids[] = get_post_thumbnail_id($temp_variation_id);

					$tracked_objects[$product_id]->quantities[] = $temp_quantity;

					$variation_post_meta = get_post_meta($temp_variation_id);

					$tracked_objects[$product_id]->variation_post_meta_array[$temp_variation_id] = $variation_post_meta;

					$product_attribute_keys = array_keys($tracked_objects[$product_id]->product_attributes);

					for ($x = 0; $x < count($product_attribute_keys); $x++) {

						$formatted_attribute_name = 'attribute_' . $product_attribute_keys[$x];

						if (!in_array( $variation_post_meta[$formatted_attribute_name][0], $tracked_objects[$product_id]->product_attributes[$product_attribute_keys[$x]] )) {
							array_push($tracked_objects[$product_id]->product_attributes[$product_attribute_keys[$x]], $variation_post_meta[$formatted_attribute_name][0]);
						}

					}


				}

			}

			switch_to_blog( $table_prefix );


			for ($i = 0; $i < count($new_ids); $i++) {
				$tracked_object = $tracked_objects[$new_ids[$i]];

				$product = wc_get_product($tracked_object->product_id);

				$parent_product_post_data = get_post( $tracked_object->product_id);
				$tracked_object->parent_product_post_data = $parent_product_post_data;
				
				$new_variation_ids =  self::dcvs_add_variations( $tracked_object, $product, $business_id );

				self::dcvs_add_attachments( $tracked_object, $new_variation_ids, $table_prefix );

				$current_product_variations = self::dcvs_get_current_variations( $product );

				$product_attribute_keys = array_keys($tracked_object->product_attributes);

				for ($z = 0; $z < count($current_product_variations); $z++) {

					for ($x = 0; $x < count($product_attribute_keys); $x++) {

						$formatted_attribute_name = 'attribute_' . $product_attribute_keys[$x];

						array_push($tracked_object->product_attributes[$product_attribute_keys[$x]], $current_product_variations[$z][$formatted_attribute_name]);

					}

				}

				for ($t = 0; $t < count($product_attribute_keys); $t++) {

					wp_set_object_terms( $tracked_object->product_id, $tracked_object->product_attributes[$product_attribute_keys[$t]], $product_attribute_keys[$t] );

				}

				delete_transient( 'wc_product_children_' . $tracked_object->product_id );
			}

			restore_current_blog();

		}

		function dcvs_add_new_product($warehouse_product_id, $table_prefix, $quantity) {
			global $wpdb;

			$post_data = get_post($warehouse_product_id);
			$post_meta = get_post_meta($warehouse_product_id);
			$product_type_term = get_the_terms( $warehouse_product_id, 'product_type');

			$new_post_data = array('post_title' => $post_data->post_title,
			                       'post_content' => $post_data->post_content,
			                       'post_status' => 'publish',
			                       'post_type' => "product");

			switch_to_blog( $table_prefix );

			$escaped_title = esc_sql( $post_data->post_title );

			$existing_id = $wpdb->get_var( "SELECT ID FROM wp_" . $table_prefix . "_posts WHERE post_title =  '$escaped_title'" );

			if ( $existing_id !== NULL ) {
				$existing_post_meta = get_post_meta($existing_id);
				$old_stock = $existing_post_meta['_stock'][0];
				update_post_meta( $existing_id, '_stock', $old_stock +  $quantity);
				restore_current_blog();
				return $existing_id;
			}

			$new_product_post_id = wp_insert_post( $new_post_data );


			wp_set_object_terms( $new_product_post_id, $product_type_term[0]->name, 'product_type' );

			update_post_meta( $new_product_post_id, '_visibility', $post_meta['_visibility'][0] );
			update_post_meta( $new_product_post_id, '_stock_status', $post_meta['_stock_status'][0]);
			update_post_meta( $new_product_post_id, 'total_sales', '0' );
			update_post_meta( $new_product_post_id, '_downloadable', $post_meta['_downloadable'][0] );
			update_post_meta( $new_product_post_id, '_virtual', $post_meta['_virtual'][0] );
			update_post_meta( $new_product_post_id, '_regular_price', $post_meta['_regular_price'][0] );
			update_post_meta( $new_product_post_id, '_sale_price', $post_meta['_sale_price'][0] );
			update_post_meta( $new_product_post_id, '_purchase_note', $post_meta['_purchase_note'][0] );
			update_post_meta( $new_product_post_id, '_featured', 'no' );
			update_post_meta( $new_product_post_id, '_weight', $post_meta['_weight'][0] );
			update_post_meta( $new_product_post_id, '_length', $post_meta['_length'][0] );
			update_post_meta( $new_product_post_id, '_width', $post_meta['_width'][0] );
			update_post_meta( $new_product_post_id, '_height', $post_meta['_height'][0] );
			update_post_meta( $new_product_post_id, '_sku', $post_meta['_sku'][0] );
			update_post_meta( $new_product_post_id, '_product_attributes', unserialize( $post_meta['_product_attributes'][0] ));
			update_post_meta( $new_product_post_id, '_sale_price_dates_from', '' );
			update_post_meta( $new_product_post_id, '_sale_price_dates_to', '' );
			update_post_meta( $new_product_post_id, '_price', $post_meta['_price'][0] );
			update_post_meta( $new_product_post_id, '_sold_individually', $post_meta['_sold_individually'][0] );
			update_post_meta( $new_product_post_id, '_backorders', 'no' );

			if ($product_type_term[0]->name == 'simple') {
				update_post_meta( $new_product_post_id, '_manage_stock', 'yes' );
				update_post_meta( $new_product_post_id, '_stock', $quantity );
			} else {
				update_post_meta( $new_product_post_id, '_manage_stock', 'no' );
				update_post_meta( $new_product_post_id, '_stock', '' );

			}

			restore_current_blog();

			return $new_product_post_id;
		}

		function dcvs_add_attachments($tracked_object, $new_variation_ids, $table_prefix) {
			global $wpdb;

			$attachments = $tracked_object->attachments;
			$product_default_thumbnail_set = false;
			$product_has_existing_thumbnail = false;

			if (get_post_thumbnail_id($tracked_object->product_id) != '') {
				$product_default_thumbnail_set = true;
				$product_has_existing_thumbnail = true;
			}

			for ($y = 0; $y < count($attachments); $y++) {

				$index = array_search( $attachments[$y]['ID'], $tracked_object->thumbnail_ids );

				if ( $index !== false ) {
					$escaped_title = esc_sql( $attachments[$y]['post_title'] );

					$existing_attach_id = $wpdb->get_var( "SELECT ID FROM wp_" . $table_prefix . "_posts WHERE post_title = '$escaped_title' and post_type = 'attachment'" );

					if ( $existing_attach_id !== NULL ) {
						$attach_id = $existing_attach_id;
					} else {
						$attach_id = self::dcvs_create_attachment( $attachments[$y], $tracked_object->product_id );
					}

					update_post_meta( $new_variation_ids[$index], '_thumbnail_id', $attach_id );
					if (!$product_default_thumbnail_set) {
						set_post_thumbnail( $tracked_object->product_id, $attach_id );
						$product_default_thumbnail_set = true;
					}
				}

				if (intval($attachments[$y]['ID']) == intval($tracked_object->default_thumbnail_id) && !$product_has_existing_thumbnail) {
					$attach_id = self::dcvs_create_attachment( $attachments[$y], $tracked_object->product_id );
					update_post_meta( $tracked_object->product_id, '_thumbnail_id', $attach_id );
					$product_default_thumbnail_set = true;
				}

			}

		}

		function dcvs_create_attachment($attachment, $product_id) {
			$upload_dir = wp_upload_dir(); // Set upload folder
			$image_data = file_get_contents($attachment['guid']); // Get image data
			$filename   = basename($attachment['guid']); // Create image file name

			// Check folder permission and define file location
			if( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			// Create the image  file on the server
			file_put_contents( $file, $image_data );

			// Check image file type
			$wp_filetype = wp_check_filetype( $filename, null );

			// Set attachment data
			$attachment_data = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => $attachment['post_title'],
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			// Create the attachment
			$attach_id = wp_insert_attachment( $attachment_data, $file, $product_id );

			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

			// Assign metadata to attachment
			wp_update_attachment_metadata( $attach_id, $attach_data );

			return $attach_id;
		}

		function dcvs_add_variations($tracking_object, WC_Product $product, $business_id) {

			$new_variation_ids = array();


			for($y = 0; $y < count($tracking_object->variation_ids); $y++) {

				$new_variation = self::dcvs_get_new_variation( $tracking_object, $y );

				$current_product_variations = self::dcvs_get_and_update_current_variations($product, $new_variation, $tracking_object->quantities[$y] );

				if ( in_array( $new_variation, $current_product_variations ) ) {
					continue;
				}

				$new_variation_data = array(
					'post_title'  => 'Product #' . $tracking_object->product_id . " Variation",
					'post_status' => 'publish',
					'post_type'   => "product_variation",
					'post_parent' => $tracking_object->product_id,
					'guid'        => $tracking_object->parent_product_post_data->guid
				);

				$new_post_id = wp_insert_post( $new_variation_data );
				$new_variation_ids[] = $new_post_id;

				update_post_meta( $new_post_id, '_stock_status', 'instock' );
				update_post_meta( $new_post_id, '_sale_price_dates_to', $tracking_object->variation_post_meta_array[$tracking_object->variation_ids[$y]]['_sale_price_dates_to'][0] );
				update_post_meta( $new_post_id, '_sale_price_dates_from', $tracking_object->variation_post_meta_array[$tracking_object->variation_ids[$y]]['_sale_price_dates_from'][0] );
				update_post_meta( $new_post_id, '_sale_price', $tracking_object->variation_post_meta_array[$tracking_object->variation_ids[$y]]['_sale_price'][0] );
				update_post_meta( $new_post_id, '_regular_price', $tracking_object->variation_post_meta_array[$tracking_object->variation_ids[$y]]['_regular_price'][0] );
				update_post_meta( $new_post_id, '_price', $tracking_object->variation_post_meta_array[$tracking_object->variation_ids[$y]]['_price'][0] );
				update_post_meta( $new_post_id, 'is_visible', '1' );
				update_post_meta( $new_post_id, 'is_variation', '1' );
				update_post_meta( $new_post_id, 'is_taxonomy', '1' );
				update_post_meta( $new_post_id, '_manage_stock', 'yes' );
				update_post_meta( $new_post_id, '_backorders', 'yes' );
				update_post_meta( $new_post_id, '_stock', $tracking_object->quantities[$y] );

				$product_attribute_keys = array_keys($tracking_object->product_attributes);

				for ($x = 0; $x < count($product_attribute_keys); $x++) {

					$formatted_attribute_name = 'attribute_' . $product_attribute_keys[$x];

					update_post_meta( $new_post_id, $formatted_attribute_name, $tracking_object->variation_post_meta_array[$tracking_object->variation_ids[$y]][$formatted_attribute_name][0] );

				}

				self::dcvs_create_warehouse_business_product($business_id, $tracking_object->variation_ids[$y], $new_post_id);

			}

			return $new_variation_ids;

		}

		function dcvs_get_new_variation($tracking_object, $variation_index) {
			$new_variation = array();

			$product_attribute_keys = array_keys($tracking_object->product_attributes);


			for ($x = 0; $x < count($product_attribute_keys); $x++) {

				$formatted_attribute_name = 'attribute_' . $product_attribute_keys[$x];

				$new_variation[$formatted_attribute_name] = $tracking_object->variation_post_meta_array[$tracking_object->variation_ids[$variation_index]][$formatted_attribute_name][0];

			}

			return $new_variation;
		}

		function dcvs_get_and_update_current_variations(WC_Product $_product, $new_variation, $quantity) {
			$current_variations = array();

			foreach( $_product->get_children() as $child_id ) {
				$child = $_product->get_child( $child_id );

				if ( ! empty( $child->variation_id ) ) {
					$variation = $child->get_variation_attributes();
					$current_variations[] = $variation;
					if ($variation == $new_variation) {
						$variation_post_meta = get_post_meta( $child_id );
						$old_stock = $variation_post_meta['_stock'][0];
						update_post_meta( $child_id, '_stock', $old_stock +  $quantity);
					}
				}
			}

			return $current_variations;
		}


		function dcvs_get_current_variations(WC_Product $_product) {
			$current_variations = array();

			foreach( $_product->get_children() as $child_id ) {
				$child = $_product->get_child( $child_id );

				if ( ! empty( $child->variation_id ) ) {
					$current_variations[] = $child->get_variation_attributes();
				}
			}

			return $current_variations;
		}

		function dcvs_add_warehouse_purchase($order_id){
			global $wpdb;

			$order = WC()->order_factory->get_order($order_id);
			$order_items = $order->get_items();

			$user_id = get_current_user_id();
			$cost = floatval( substr(preg_replace( '#[^\d.]#', '', $order->get_formatted_order_total()), 2));
			$order_item_names = [];
			foreach ($order_items as $item){
				$order_item_names[] = $item;
			}
			$items = serialize($order_item_names);

			$wpdb->insert( "dcvs_warehouse_purchase", [ "user_id" => $user_id, "cost" => $cost, "items" => $items ] );
		}

		function dcvs_add_business_purchase($order_id){
			global $wpdb;
			$order = WC()->order_factory->get_order($order_id);
			$order_items = $order->get_items();

			$user_id = get_current_user_id();
			$current_persona_id = self::dcvs_get_current_persona_id( $user_id );
			$business_id = self::dcvs_get_current_business_id();
			$cost = floatval( substr(preg_replace( '#[^\d.]#', '', $order->get_formatted_order_total()), 2));
			$order_item_names = [];
			foreach ($order_items as $item){
				$order_item_names[] = $item;
				$id = $item['variation_id'] != '0' ? $item['variation_id'] : $item['product_id'];
				$quantity =  $item['qty'];
				$subtotal = $item['line_subtotal'];
				$item_price = number_format($subtotal/$quantity,2);
				if(!self::dcvs_check_business_product_price_exists($business_id, $id, $item_price)) {
					self::dcvs_create_business_product_price($business_id, $id, $item_price, $quantity);
				} else {
					self::dcvs_update_business_product_price($business_id, $id, $item_price, $quantity);
				}
			}
			$items = serialize($order_item_names);

			$wpdb->insert( "dcvs_business_purchase", [ "user_persona_id" => $current_persona_id, "business_id" => $business_id, "cost" => $cost, "items" => $items ] );
		}

		function dcvs_get_current_persona_id($user_id) {
			global $wpdb;

			$current_persona_id = $wpdb->get_var("SELECT current_persona_id FROM dcvs_current_persona WHERE user_id =  '" . esc_sql( $user_id ) . "'");
			return $current_persona_id;
		}

		function dcvs_get_current_business_id() {
			global $wpdb;

			$store_url = get_site_url(get_current_blog_id()) . "/";
			$business_id = $wpdb->get_var("SELECT id FROM dcvs_business WHERE url = '" . esc_sql( $store_url ) . "'");
			return $business_id;

		}

		function dcvs_get_user_blog_id($user_id) {
			global $wpdb;
			$business_url = $wpdb->get_var( "SELECT url FROM dcvs_business WHERE id = (SELECT business_id FROM dcvs_user_business WHERE user_id = " . esc_sql( $user_id ) . ")" );
			$parsed_url = parse_url( $business_url );
			$blog_path = $parsed_url['path'];
			$blog_id = $wpdb->get_var( "SELECT blog_id FROM wp_blogs WHERE path = '" . esc_sql( $blog_path ) . "'" );

			return $blog_id;

		}

		function dcvs_check_warehouse_business_product_exists($business_id, $warehouse_product_id, $business_product_id ) {
			global $wpdb;
			$sql = $wpdb->prepare("SELECT * FROM dcvs_warehouse_business_product WHERE business_id = '%d' and warehouse_product_id = '%d' and business_product_id = '%d'", [$business_id, $warehouse_product_id, $business_product_id]);
			$rows = $wpdb->get_results($sql, ARRAY_A);

			if (count($rows) < 1) {
				return false;
			} else {
				return true;
			}
		}

		function dcvs_create_warehouse_business_product($business_id, $warehouse_product_id, $business_product_id) {
			global $wpdb;
			$wpdb->insert( "dcvs_warehouse_business_product", [ "business_id" => $business_id, "warehouse_product_id" => $warehouse_product_id, "business_product_id" => $business_product_id ] );
		}

		function dcvs_check_business_product_price_exists($business_id, $business_product_id, $price) {
			global $wpdb;

			$sql = $wpdb->prepare("SELECT * FROM dcvs_business_product_price WHERE business_id = '%d' and business_product_id = '%d' and price = '%d'", [$business_id, $business_product_id, $price]);
			$rows = $wpdb->get_results($sql, ARRAY_A);

			if (count($rows) < 1) {
				return false;
			} else {
				return true;
			}

		}

		function dcvs_create_business_product_price($business_id, $business_product_id, $price, $number_bought) {
			global $wpdb;
			$wpdb->insert( "dcvs_business_product_price", [ "business_id" => $business_id, "business_product_id" => $business_product_id, "price" => $price, "number_bought" => $number_bought ] );
		}

		function dcvs_update_business_product_price($business_id, $business_product_id, $price, $number_bought) {
			global $wpdb;
			$sql = $wpdb->prepare("UPDATE dcvs_business_product_price SET number_bought = number_bought + '%d' WHERE business_id = '%d' and business_product_id = '%d' and price = '%d'", [$number_bought, $business_id, $business_product_id, $price]);
			$wpdb->get_results($sql, ARRAY_A);
		}


	}

	new WarehouseCheckout();

}