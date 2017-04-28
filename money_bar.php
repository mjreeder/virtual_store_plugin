<?php

function dcvs_include_money_bar()
{
	if (is_user_logged_in()) {
		if (!is_super_admin(get_current_user_id())) {
			add_action( 'wp_enqueue_scripts', 'dcvs_enqueue_money_bar_style' );
			add_action( 'wp_footer', 'dcvs_add_money_bar' );
		}
	}
}
add_action('init', 'dcvs_include_money_bar');


function dcvs_enqueue_money_bar_style() {
	wp_enqueue_style( 'budgetBar_css', plugins_url('assets/css/budgetBar.css', __FILE__) );
}

function dcvs_add_money_bar() {

	$user_id = get_current_user_id();

	$cart_cost = dcvs_get_cart_cost();

	if (get_current_blog_id() == 1) {

		$business = dcvs_get_business_by_user_id( $user_id );

		$business_title = $business['title'];
		$business_description = $business['description'];
		$business_budget = $business['money'];
		$business_expense = dcvs_get_business_expenses($user_id);

		$current_budget = $business_budget - $business_expense - $cart_cost;

		$landing_page_url = dcvs_get_landing_page_url();

		?>
		<!-- FONTS -->
		<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">

		<footer class="budgetBar">

			<div class="bar">
				<div class="barLeft"><span><h1><?php echo $business_title ?></h1></span></div>
				<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
				<a href="<?php echo $landing_page_url ?>"><span>Back to Dashboard</span></a>
			</div>
			<div class="barSummary">
				<h2>you are:</h2>
				<p><?php echo $business_description ?></p>
			</div>
		</footer>
		<?php
	} else if (get_user_blog_id( $user_id ) != get_current_blog_id()) {

		$persona = dcvs_get_current_persona($user_id);

		$persona_name = $persona['name'];
		$persona_description = $persona['description'];
		$persona_budget = $persona['money'];
		$persona_expense = dcvs_get_persona_expenses($user_id, $persona['id']);

		$current_budget = $persona_budget - $persona_expense - $cart_cost;

		$store_list_url = dcvs_get_store_list_url();
		
		?>
		<footer class="budgetBar">

			<div class="bar">
				<div class="barLeft"><span><h1><?php echo $persona_name ?></h1></span></div>
				<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
				<a href="<?php echo $store_list_url ?>"><span>Back To Store List</span></a>
			</div>

			<div class="barSummary">
				<h2>you are:</h2>
				<p><?php echo $persona_description ?></p>
			</div>
		</footer>
		<?php
	} else {
		$business = dcvs_get_business_by_user_id( $user_id );

		$business_description = $business['description'];
		$business_budget = $business['money'];
		$business_expense = dcvs_get_business_expenses($user_id);

		$current_budget = $business_budget - $business_expense - $cart_cost;

		$landing_page_url = dcvs_get_landing_page_url();

		?>
		<!-- FONTS -->
		<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">

		<footer class="budgetBar">

			<div class="bar">
				<div class="barLeft"><span><h1>Your Store</h1></span></div>
				<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
				<a href="<?php echo $landing_page_url ?>"><span>Back to Dashboard</span></a>
			</div>
			<div class="barSummary">
				<h2>you are:</h2>
				<p><?php echo $business_description ?></p>
			</div>
		</footer>
		<?php
	}
}


function get_user_blog_id($user_id) {
	global $wpdb;
	$business_url = $wpdb->get_var( "SELECT url FROM dcvs_business WHERE id = (SELECT business_id FROM dcvs_user_business WHERE user_id = " . esc_sql( $user_id ) . ")" );
	$parsed_url = parse_url( $business_url );
	$blog_path = $parsed_url['path'];
	$blog_id = $wpdb->get_var( "SELECT blog_id FROM wp_blogs WHERE path = '" . esc_sql( $blog_path ) . "'" );

	return $blog_id;

}

function dcvs_get_business_by_user_id($user_id) {
	global $wpdb;
	$sql = $wpdb->prepare("SELECT * FROM dcvs_business WHERE id = (SELECT business_id FROM dcvs_user_business WHERE user_id = '%d')", [$user_id]);
	$response = $wpdb->get_results($sql, ARRAY_A);
	$business = $response[0];
	return $business;
}

function dcvs_get_business_expenses($user_id){
	global $wpdb;
	$result = $wpdb->get_var("SELECT SUM(cost) FROM dcvs_warehouse_purchase WHERE user_id = " . esc_sql( $user_id ));
	return $result;
}

function dcvs_get_cart_cost(){
	$items = WC()->cart->get_cart();
	$total_cart_cost = 0;
	foreach ($items as $item) {
		$total_cart_cost = $total_cart_cost + $item["line_total"];
	}
	return $total_cart_cost;
}

function dcvs_get_current_persona($user_id) {
	global $wpdb;
	$sql = $wpdb->prepare("SELECT * FROM dcvs_persona WHERE id = (SELECT persona_id FROM dcvs_user_persona WHERE id = ( SELECT current_persona_id FROM dcvs_current_persona WHERE user_id = '%d'))", [$user_id]);
	$response = $wpdb->get_results($sql, ARRAY_A);
	if (count($response) < 1) {
		return null;
	}
	$persona = $response[0];
	return $persona;
}

function dcvs_get_persona_expenses($user_id, $persona_id){
	global $wpdb;
	$result = $wpdb->get_var("SELECT SUM(cost) FROM dcvs_business_purchase WHERE user_persona_id = (SELECT id FROM dcvs_user_persona WHERE user_id =". esc_sql( $user_id ) ." AND persona_id = ". esc_sql( $persona_id ) .")");
	return $result;
}

