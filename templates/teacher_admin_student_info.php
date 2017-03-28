<?php

?>
	<aside class="studentList">
		<div class="searchBar">
			<img src="<?php echo plugins_url( 'assets/images/search.svg', dirname(__FILE__)); ?>" rel="stylesheet" alt="">
			<input type="text" id='search' placeholder="search" oninput="studentSearch()">
		</div>
		<ul id="students">
			<?php display_student_list(); ?>
		</ul>
	</aside>

<?php display_current_student_info();

function display_student_list()
{
	global $wpdb;
	$user_results = $wpdb->get_results('SELECT * FROM dcvs_user_business', OBJECT);
	?>
	<?php
	for ($i = 0; $i < sizeof($user_results); ++$i) {
		$display_name = $wpdb->get_results($wpdb->prepare('SELECT * FROM wp_users WHERE id = %d', $user_results[$i]->user_id));
		$business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $user_results[$i]->business_id));
		$personas = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.id WHERE user_id = %d', $user_results[$i]->user_id));
		?>

		<li id="<?php echo $user_results[$i]->user_id; ?>" name="student_name" type="text">
			<a href="<?php echo $_SERVER['REQUEST_URI'].'&student_id='.$user_results[$i]->user_id; ?>">
				<?php echo $display_name[0]->display_name; ?>
			</a>
		</li>

		<?php

	}
}

function display_current_student_info()
{
	global $wpdb;
	$currentDisplayStudent = $_REQUEST['student_id'];
	$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudent));
	$business_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business LEFT JOIN dcvs_user_business ON dcvs_business.id=dcvs_user_business.business_id WHERE user_id = %d', $currentDisplayStudent));
	$persona_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $currentDisplayStudent));
	?>

	<section class="studentInfo">
		<h1><?php echo $display_name[0]->display_name ?></h1>
		<section class="merchandiserInfo">
			<h2><?php echo $business_info[0]->title ?></h2>
			<p><strong>you are:</strong> <?php echo $business_info[0]->description ?></p>
			<div>
				<a href="<?php echo $business_info[0]->url ?>" class="button">Personal Site</a>
				<button class="button">COMPARISON</button>
				<button class="button">FINAL SURVEY</button>
			</div>

		</section>
		<section class="shopperInfo">
			<h2>shopper</h2>
			<aside class="shopperOne">
				<div>
					<h3><?php echo $persona_info[0]->name ?></h3>
					<img src="<?php echo plugins_url( 'assets/images/personaRed.png', dirname(__FILE__)); ?>" alt="">
				</div>
				<p><strong>you are:</strong> <?php echo $persona_info[0]->description ?></p>

				<?php
				if ($_GET) {
					if (isset($_POST['insert_one'])) {
						get_user_persona_order_history($currentDisplayStudent, $persona_info[0]->id);
					}
				}
				?>
				<form action="" method="post">
					<button class="button buttonSmall" name="insert_one">ORDER HISTORY</button>
					<button class="button buttonSmall" type="reset" value="Reset">SURVEY</button>
				</form>

			</aside>
			<aside class="shopperTwo">
				<div>
					<h3><?php echo $persona_info[1]->name ?></h3>
					<img src="<?php echo plugins_url( 'assets/images/personaBlue.png', dirname(__FILE__)); ?>" alt="">
				</div>
				<p><strong>you are:</strong> <?php echo $persona_info[1]->description ?></p>
				<?php
				if ($_GET) {
					if (isset($_POST['insert_two'])) {
						get_user_persona_order_history($currentDisplayStudent, $persona_info[1]->id);
					}
				}
				?>
				<form action="" method="post">

					<button class="button buttonSmall" name="insert_two">ORDER HISTORY</button>
					<button class="button buttonSmall" type="reset" value="Reset">SURVEY</button>
				</form>

			</aside>
		</section>

	</section>
	<?php

}

function get_user_persona_order_history($user_id, $persona_id)
{
	global $wpdb;
	$user_persona_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_business_purchase LEFT JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND user_persona_id = %d', $user_id, $persona_id));
	if (sizeOf($user_persona_order_history) > 1) {
		for ($i = 0; $i < sizeOf($user_persona_order_history); ++$i) {
			var_dump($user_persona_order_history[$i]->cost, $user_persona_order_history[$i]->items);
		}
	} else {
		var_dump('user has not ordered anything');
	}
}