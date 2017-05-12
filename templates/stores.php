<?php

require_once __DIR__.'/../../../../wp-blog-header.php';

if ( !is_user_logged_in() ) {
	wp_redirect( get_site_url() . '/wp-admin' );
	exit;
}

if (isset($_REQUEST['persona_id'])) {
	dcvs_set_current_consumer(get_current_user_id(),$_REQUEST['persona_id']);
}

$businesses = dcvs_get_all_available_businesses();

function dcvs_set_current_consumer($user_id, $consumer_id)
{
	global $wpdb;
	$result = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_current_persona WHERE user_id = %d', $user_id));
	if (sizeOf($result) > 0) {
		$wpdb->get_results($wpdb->prepare('UPDATE dcvs_current_persona set current_persona_id = %d WHERE user_id = %d', $consumer_id, $user_id));
	} else {
		$wpdb->insert('dcvs_current_persona', ['user_id' => $user_id, 'current_persona_id' => $consumer_id]);
	}
}

function dcvs_get_all_available_businesses()
{
	global $wpdb;
	$user_id = get_current_user_id();
	$active_user_ids = DCVS_Store_Management::get_active_users();
	$formatted_user_ids = implode(",",$active_user_ids);
	$sql = $wpdb->prepare('SELECT dcvs_business.*, dcvs_user_business.user_id FROM dcvs_business LEFT JOIN dcvs_user_business ON dcvs_business.id = dcvs_user_business.business_id  WHERE id != (SELECT business_id FROM dcvs_user_business WHERE user_id = %d) AND dcvs_user_business.user_id IN ('.$formatted_user_ids.')' , $user_id);
	$businesses = $wpdb->get_results($sql, ARRAY_A);
	return $businesses;

}

?>

<?php

$user_id = get_current_user_id();

$user_persona_ids = dcvs_get_user_persona_ids( $user_id );
$persona_one_id = get_object_vars($user_persona_ids[0])["persona_id"];

$persona = dcvs_get_current_persona($user_id);

$persona_category = dcvs_get_persona_category( $persona['id'] );

$category = isset($persona_category[0]['category_id']) ? dcvs_get_category_by_id($persona_category[0]['category_id']) : null;
$category_name = isset($category[0]['name']) ? $category[0]['name'] : "Not Set";

$persona_name = $persona['name'];
$persona_description = $persona['description'];
$persona_budget = $persona['money'];
$persona_expense = dcvs_get_persona_expenses($user_id, $persona['id']);

$current_budget = $persona_budget - $persona_expense ;

$placeholder_image = "../assets/images/dollar-sign.jpg";

?>
<!doctype HTML>
<html>

<head>
	<title>Virtual Store</title>
	<!-- CSS -->
	<link href="../assets/css/storeList.css" rel="stylesheet" type="text/css">
	<link href="../assets/css/budgetBar.css" rel="stylesheet" type="text/css">
	<!-- FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">

</head>

<body>

	<header class="header">

		<h1>virtual store</h1>

	</header>

	<div class="mainContent">

		<ul class="storeList">
			<?php
			foreach ($businesses as $business) {
				$user_blog_id = intval(get_user_blog_id( $business['user_id'] ));
				$user_site_icon = get_site_icon_url(512, '', $user_blog_id);
				switch_to_blog( $user_blog_id );
				$site_name = get_bloginfo('name');
				restore_current_blog();
			?>
				<li>
					<?php
					if($user_site_icon == "") {
						?>
						<img src="<?php echo $placeholder_image; ?>" onclick="window.location='<?php echo $business['url'] . 'shop'; ?>'"/>
						<?php
					} else {
						?>
						<img src="<?php echo $user_site_icon; ?>" onclick="window.location='<?php echo $business['url'] . 'shop';?>'">
						<?php
					}
					?>
					<p><?php echo $site_name; ?></p>
				</li>
			<?php
			}
			?>
		</ul>

	</div>

	<?php

	if ($persona_one_id == $persona['id']) {
		?>
		<footer class="budgetBar personaOneDark" id="bar">

			<div class="bar personaOne" onclick="toggleHeight()">
				<div class="barLeft personaOneDark"><span><h1><?php echo stripslashes_deep($persona_name); ?></h1></span></div>
				<div class="barRight">
					<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
					<?php if ($current_budget < 0) { ?>
						<div class="warningMessage">
							<img src="<?php echo plugins_url("../assets/images/stop.svg", __FILE__); ?>" alt="">
							<p>stop! you're over budget!</p>
						</div>
					<?php } ?>
					<a href="<?php echo dcvs_get_landing_page_url(); ?>" onclick="preventToggleHeight(event)"><span>Back to Dashboard</span></a>
				</div>

			</div>

			<div class="barSummary">
<!--				<h2>Category: --><?php //echo stripslashes_deep($category_name);?><!--</h2>-->
				<p><?php echo stripslashes_deep($persona_description); ?></p>
			</div>
		</footer>
		<?php
	} else {
		?>
		<footer class="budgetBar personaTwoDark" id="bar">

			<div class="bar personaTwo" onclick="toggleHeight()">
				<div class="barLeft personaTwoDark"><span><h1><?php echo stripslashes_deep($persona_name); ?></h1></span></div>
				<div class="barRight">
					<h3>current budget: <span>$<?php echo number_format( $current_budget, 2 ); ?><span></h3>
					<?php if ($current_budget < 0) { ?>
						<div class="warningMessage">
							<img src="<?php echo plugins_url("../assets/images/stop.svg", __FILE__); ?>" alt="">
							<p>stop! you're over budget!</p>
						</div>
					<?php } ?>
					<a href="<?php echo dcvs_get_landing_page_url(); ?>" onclick="preventToggleHeight(event)"><span>Back to Dashboard</span></a>
				</div>
			</div>

			<div class="barSummary">
<!--				<h2>Category: <span>--><?php //echo stripslashes_deep($category_name);?><!--</span></h2>-->
				<p><?php echo stripslashes_deep($persona_description); ?></p>
			</div>
		</footer>
		<?php
	}

	?>

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

		var preventToggleHeight = function(event) {
				event.stopPropagation();
		};
	</script>

</body>

</html>
<?php
?>
