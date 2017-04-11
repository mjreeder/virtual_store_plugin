<?php

global $current_user;

add_action('woocommerce_thankyou','dcvs_survey_time');
function dcvs_survey_time($order_id){
	if( get_current_blog_id() == 1 ){
		wp_redirect(network_site_url('/warehouse-evaluation'));
	} elseif( persona_shopping_complete(1) && persona_shopping_complete(1) ){
		wp_redirect(network_site_url('/shopping-evaluation?store_id='.get_current_blog_id()));
	} else {
		wp_redirect(network_site_url('/final-evaluation'));
	}
}

add_action( 'gform_after_submission', 'dcvs_redirect_to_dashboard_after_evaluation' );
function dcvs_redirect_to_dashboard_after_evaluation($entry){
	global $wpdb;
	if( isset( $current_user ) && !empty($current_user->roles) ){
		if(!in_array('administrator', $current_user->roles)) {
			wp_redirect( get_site_url() . '//wp-content/plugins/virtual_store_plugin/templates/landing.php' );
			exit;
		}
	} else {
		wp_redirect( get_site_url() . '//wp-content/plugins/virtual_store_plugin/templates/landing.php' );
		exit;
	}
}

add_filter( 'gform_field_value_user_id', 'dcvs_add_user_id_to_form' );
function dcvs_add_user_id_to_form( $value ) {
	return get_current_user_id();
}