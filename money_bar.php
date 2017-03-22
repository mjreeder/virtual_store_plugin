<?php

function dcvs_enqueue_money_bar_style() {
	wp_enqueue_style( 'budgetBar_css', plugins_url('assets/css/budgetBar.css', __FILE__) );
}

function dcvs_add_money_bar() {

	$user_id = get_current_user_id();

	if (get_current_blog_id() == 1) {

		$business = dcvs_get_business_by_user_id( $user_id );

		$business_title = $business['title'];
		$business_description = $business['description'];
		$business_budget = $business['money'];
		$business_url = $business['url'];
		$business_expense = dcvs_get_business_expenses($user_id);

		$cart_cost = dcvs_get_cart_cost();

		$current_budget = $business_budget - $business_expense - $cart_cost;

		?>
		<!-- FONTS -->
		<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">

		<footer class="budgetBar">

			<div class="bar">
				<div class="barLeft"><span><h1><?php echo $business_title ?></h1></span></div>
				<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
				<a href="<?php echo $business_url ?>"><span>back to your store</span></a>
			</div>

			<h2>you are:</h2>
			<p><?php echo $business_description ?></p>

		</footer>
		<?php
	} else if (get_user_blog_id( $user_id ) != get_current_blog_id()) {
		?>
		<footer class="budgetBar">

			<div class="bar">
				<div class="barLeft"><span><h1>Persona #</h1></span></div>
				<h3>current budget: <span>$1234567.89<span></h3>
				<a href=""><span>back to store list</span></a>
			</div>

			<h2>you are:</h2>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi malesuada nibh eu pellentesque interdum. Sed pulvinar orci lacus, vel hendrerit tortor blandit quis. Nullam tempus dolor id tempus volutpat. Ut ultrices vel est et vulputate.</p>

		</footer>
		<?php
	}
}

add_action( 'wp_enqueue_scripts', 'dcvs_enqueue_money_bar_style' );
add_action( 'wp_footer', 'dcvs_add_money_bar' );

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

