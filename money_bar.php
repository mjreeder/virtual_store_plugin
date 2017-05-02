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

	$persona = dcvs_get_current_persona($user_id);

	$user_persona_ids = dcvs_get_user_persona_ids( $user_id );
	$persona_one_id = get_object_vars($user_persona_ids[0])["persona_id"];

	if (get_current_blog_id() == 1) {
		global $wpdb;

		$business = dcvs_get_business_by_user_id( $user_id );

		$business_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business LEFT JOIN dcvs_user_business ON dcvs_business.id=dcvs_user_business.business_id WHERE user_id = %d', $user_id));

		$user_blog_id = intval(get_user_blog_id( $business_info[0]->user_id ));
		switch_to_blog( $user_blog_id );
		$site_name = get_bloginfo('name');
		restore_current_blog();

		$business_category = dcvs_get_user_business_category( $user_id );
		var_dump( $business_category[0]['name'] );
		$category_name = isset($business_category[0]['name']) ? $business_category[0]['name'] : "Not Set";

		$business_description = $business['description'];
		$business_budget = $business['money'];
		$business_expense = dcvs_get_business_expenses($user_id);

		$current_budget = $business_budget - $business_expense - $cart_cost;

		$landing_page_url = dcvs_get_landing_page_url();

		?>
		<!-- FONTS -->
		<head>
			<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">
		</head>

		<footer class="budgetBar mainButtonDark" id="bar">

			<div class="bar mainButton" onclick="toggleHeight()">
				<div class="barLeft mainButtonDark"><span><h1><?php echo $site_name ?></h1></span></div>
				<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
				<?php if ($current_budget < 0) { ?>
					<div class="warningMessage">
						<img src="<?php echo plugins_url("./assets/images/warning.svg", __FILE__); ?>" alt="">
						<p>warning! you're over budget!</p>
					</div>
				<?php } ?>
				<a href="<?php echo $landing_page_url ?>"><span>Back to Dashboard</span></a>
			</div>
			<div class="barSummary">
				<h2>Category: <?php echo stripslashes_deep($category_name);?></h2>
				<p><?php echo stripslashes_deep($business_description); ?></p>
			</div>
		</footer>

		<script>
			var bar = document.getElementById('bar');

			var toggleHeight = function() {
				var style = window.getComputedStyle(bar);
				var bottom = style.getPropertyValue('bottom');
				if(bottom == "0px") {
					bar.style.bottom ="-120px";
				}
				else {
					bar.style.bottom ="0";
				}
			};
		</script>
		<?php
	} else if (get_user_blog_id( $user_id ) != get_current_blog_id() && $persona['id'] == $persona_one_id) {
		$persona_category = dcvs_get_persona_category( $persona['id'] );
		$category = isset($persona_category[0]['category_id']) ? dcvs_get_category_by_id($persona_category[0]['category_id']) : null;
		$category_name = isset($category[0]['name']) ? $category[0]['name'] : "Not Set";

		$persona_name = $persona['name'];
		$persona_description = $persona['description'];
		$persona_budget = $persona['money'];
		$persona_expense = dcvs_get_persona_expenses($user_id, $persona['id']);

		$current_budget = $persona_budget - $persona_expense - $cart_cost;

		$store_list_url = dcvs_get_store_list_url();

		?>
		<head>
			<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">
		</head>

		<footer class="budgetBar personaOneDark" id="bar">

			<div class="bar personaOne" onclick="toggleHeight()">
				<div class="barLeft personaOneDark"><span><h1><?php echo stripslashes_deep($persona_name); ?></h1></span></div>
				<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
				<?php if ($current_budget < 0) { ?>
					<div class="warningMessage">
						<img src="<?php echo plugins_url("./assets/images/stop.svg", __FILE__); ?>" alt="">
						<p>warning! you're over budget!</p>
					</div>
				<?php } ?>
				<a href="<?php echo $store_list_url ?>"><span>Back To Store List</span></a>
			</div>

			<div class="barSummary">
				<h2>Category: <?php echo stripslashes_deep($category_name);?></h2>
				<p><?php echo stripslashes_deep($persona_description); ?></p>
			</div>
		</footer>

		<script>
			var bar = document.getElementById('bar');

			var toggleHeight = function() {
				var style = window.getComputedStyle(bar);
				var bottom = style.getPropertyValue('bottom');
				if(bottom == "0px") {
					bar.style.bottom ="-120px";
				}
				else {
					bar.style.bottom ="0";
				}
			};
		</script>
		<?php
	} else {
		$persona_category = dcvs_get_persona_category( $persona['id'] );
		$category = isset($persona_category[0]['category_id']) ? dcvs_get_category_by_id($persona_category[0]['category_id']) : null;
		$category_name = isset($category[0]['name']) ? $category[0]['name'] : "Not Set";

		$persona_name = $persona['name'];
		$persona_description = $persona['description'];
		$persona_budget = $persona['money'];
		$persona_expense = dcvs_get_persona_expenses($user_id, $persona['id']);

		$current_budget = $persona_budget - $persona_expense - $cart_cost;

		$store_list_url = dcvs_get_store_list_url();

		?>

		<head>
			<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">
		</head>

		<footer class="budgetBar personaTwoDark" id="bar">

			<div class="bar personaTwo" onclick="toggleHeight()">
				<div class="barLeft personaTwoDark"><span><h1><?php echo stripslashes_deep($persona_name); ?></h1></span></div>
				<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
				<?php if ($current_budget < 0) { ?>
					<div class="warningMessage">
						<img src="<?php echo plugins_url("./assets/images/stop.svg", __FILE__); ?>" alt="">
						<p>warning! you're over budget!</p>
					</div>
				<?php } ?>
				<a href="<?php echo $store_list_url ?>"><span>Back To Store List</span></a>
			</div>

			<div class="barSummary">
				<h2>Category: <?php echo stripslashes_deep($category_name);?></h2>
				<p><?php echo stripslashes_deep($persona_description); ?></p>
			</div>
		</footer>

		<script>
			var bar = document.getElementById('bar');

			var toggleHeight = function() {
				var style = window.getComputedStyle(bar);
				var bottom = style.getPropertyValue('bottom');
				if(bottom == "0px") {
					bar.style.bottom ="-120px";
				}
				else {
					bar.style.bottom ="0";
				}
			};
		</script>
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

function dcvs_get_category_by_id($category_id) {
	global $wpdb;
	$sql = $wpdb->prepare("SELECT * FROM dcvs_category WHERE id = '%d'", [$category_id]);
	$response = $wpdb->get_results($sql, ARRAY_A);
	return $response;
}
