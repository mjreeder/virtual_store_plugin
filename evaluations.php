<?php

global $current_user;

add_action('woocommerce_thankyou','dcvs_survey_time');
function dcvs_survey_time($order_id){
	global $wpdb;
	$current_persona = dcvs_get_current_persona(get_current_user_id());
	if( get_current_blog_id() == 1 ){
		wp_safe_redirect(network_site_url('/warehouse-evaluation'));
		exit;
	} else {
		wp_safe_redirect(network_site_url('/shopping-evaluation?store_id='.get_current_blog_id().'&persona_id='.$current_persona[0]->id));
		exit;
	}
}

add_action( 'gform_after_submission', 'dcvs_redirect_to_dashboard_after_evaluation' );
function dcvs_redirect_to_dashboard_after_evaluation($entry){
	if (is_user_logged_in()) {
		if (!is_super_admin(get_current_user_id())) {
			wp_redirect( dcvs_get_landing_page_url(get_current_user_id()) );
			exit;
		}
	} else {
		wp_safe_redirect( site_url() );
		exit;
	}
}

add_filter( 'gform_field_value_user_id', 'dcvs_add_user_id_to_form' );
function dcvs_add_user_id_to_form( $value ) {
	return get_current_user_id();
}

function timeIsLaterThan($date) {
	$date_time = DateTime::createFromFormat("Y-m-d", $date);
	$timestamp = $date_time->getTimestamp();
	if (time() >= $timestamp){
		return true;
	}
	return false;
}

function dcvs_get_answers_based_on_question_id($entry, $questionID){
	$answers = [];
	foreach($entry as $key=>$value){
		if( strpos($key,$questionID) === 0 && !empty($value) ){
			$answers[] = $value;
		}
	}
	return '<ul><li>'.implode('</li><li>', $answers).'</li></ul>';
}
