<?php
// require_once('store_management.php');

// var_dump(sizeof($users));
// die('de');
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

<?php
display_current_student_info();
function get_value_from_stdClass($obj){
	$array = get_object_vars($obj);
	reset($array);
	$first_key = key($array);
	if (intval($array[$first_key]) > 0) {
		return $array[$first_key];
	}else{
		return 0;
	}
}
function display_student_list()
{
	global $wpdb;
	$user_results = $wpdb->get_results('SELECT * FROM dcvs_user_business', OBJECT);
	$users = DCVS_Store_Management::get_active_users();

	// $user_info = get_users( $users[0] );
	// var_dump($user_info);
	// die('derp');
	?>
	<?php
	foreach ($users as $id) {
		$display_name = $wpdb->get_results($wpdb->prepare('SELECT * FROM wp_users WHERE id = %d', $id));
		$business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business JOIN dcvs_user_business WHERE user_id = %d', $id));
		$personas = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.id WHERE user_id = %d', $id));
		?>

		<li id="<?php echo $id; ?>" name="student_name" type="text">
			<a href="<?php echo $_SERVER['REQUEST_URI'].'&student_id='.$id; ?>">
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
	$warehouse_purchase_sum = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_warehouse_purchase WHERE user_id = %d', $currentDisplayStudent));
	if(isset($warehouse_purchase_sum[0]) && isset($business_info[0])){
		$budget_remaining = $business_info[0]->money - get_value_from_stdClass($warehouse_purchase_sum[0]);
	}

	$persona_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $currentDisplayStudent));
	if(isset($business_info[0])){
		$number_of_shoppers = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT business_id) FROM dcvs_business_purchase WHERE business_id = %d', $business_info[0]->id));
	}
	if(isset($persona_info[0])){
		$persona_one_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $persona_info[0]->persona_id));
		$persona_one_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $currentDisplayStudent, $persona_info[0]->persona_id));
	}

	if (isset($persona_info[1])) {
		$persona_two_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $persona_info[1]->persona_id));
		$persona_two_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $currentDisplayStudent, $persona_info[1]->persona_id));
	}


	?>
		<section class="studentInfo" id='mainView'>
		<h1><?php echo $display_name[0]->display_name ?></h1>
		<section class="merchandiserInfo">
			<h2 class="subTitle">buyer</h2>
			<?php
			if(isset($business_info[0])){
				?>
				<h3><?php echo $business_info[0]->title ?></h3>
				<?php
			}
			else{
				?>
				<h3><?php echo "Business not set for user" ?></h3>
				<?php
			}

			 ?>

			<section>
				<aside class="merchandiserLeft">
					<?php
					if(isset($business_info[0])){
						?>
						<a href="<?php echo $business_info[0]->url ?>" class="button">Personal Site</a>
						<?php
					}
					else{
						?>
						<a href="<?php echo "Business not set for user" ?>" class="button">Personal Site</a>
						<?php
					}
					 ?>

					<a href="" class="button">FINAL SURVEY</a>
					<!-- TODO get remaining budget-->
					<?php
					if (isset($budget_remaining)) {
						?>
						<span><b>BUDGET REMAINING:</b> $<?php echo $budget_remaining ?></span>
						<?php
					}
					else{
						?>
						<span>No Budget</span>
						<?php
					}

					 ?>

				</aside>
				<aside class="merchandiserRight">
					<section class="facts">
						<div class="fact">
							<img src=<?php echo plugins_url( "assets/images/dollarSign.svg", dirname(__FILE__));
							?> alt="">
							<!-- TODO get profit-->
							<p>$450 <br>PROFIT</p>
						</div>
						<div class="fact">
							<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
							?>  alt="">
							<p><?php
							if(isset($number_of_shoppers[0])){
								echo get_value_from_stdClass($number_of_shoppers[0]);
							}
							else{
								echo "0";
							}

							?> <br>SHOPPERS</p>
						</div>
					</section>
					<!-- TODO comparison page -->
					<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id'] .'&section=stats&user_id='.$currentDisplayStudent?>">
						<button class="button">STATISTICS</button>
					</a>
				</aside>
			</section>
		</section>
		<section class="shopperInfo">

			<aside class="shopperOne">
				<h2 class="subTitle">consumer #1</h2>
				<?php
					if (isset($persona_info[0])) {
						?>
						<h3><?php echo $persona_info[0]->name ?></h3>
						<?php
					}
					else{
						?>
						<h3><?php echo "Persona One not set" ?></h3>
						<?php
					}
				 ?>

				<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id'] .'&section=order_history&user_id='.$currentDisplayStudent.'&persona_id='.$persona_info[0]->persona_id ?>">
					<a href="" class="button one">ORDER HISTORY</button>
				</a>
				<button class="button one">FINAL SURVEY</button>
				<section class="facts">
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
						?> alt="">
						<p><?php
							if(isset($persona_one_total_money[0])){
								$difference = get_value_from_stdClass($persona_one_total_money[0]) - get_value_from_stdClass($persona_one_money_spent[0]);
	 						 echo '$'.$difference;
							}
							else{
								echo "N/A";
							}
						 ?>
						 <br>Remaining</p>
					</div>
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
						?> alt="">
						<p><?php
						if(isset($persona_one_purchase_count[0])){
							echo get_value_from_stdClass($persona_one_purchase_count[0]);
						}
						else{
							echo "0";
						}

						?> <br>PURCHASES</p>
					</div>
				</section>
			</aside>

			<aside class="shopperTwo">
				<h2 class="subTitle">consumer #2</h2>
				<?php
					if (isset($persona_info[1])) {
						?>
						<h3><?php echo $persona_info[1]->name ?></h3>
						<?php
					}
					else{
						?>
						<h3><?php echo "Persona One not set" ?></h3>
						<?php
					}
				 ?>

				<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id'] .'&section=order_history&user_id='.$currentDisplayStudent.'&persona_id='.$persona_info[1]->persona_id ?>">
					<a href="" class="button one">ORDER HISTORY</button>
				</a>
				<button class="button one">FINAL SURVEY</button>
				<section class="facts">
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
						?> alt="">
						<p><?php
							if(isset($persona_one_total_money[1])){
								$difference = get_value_from_stdClass($persona_one_total_money[1]) - get_value_from_stdClass($persona_one_money_spent[1]);
							 echo '$'.$difference;
							}
							else{
								echo "N/A";
							}
						 ?>
						 <br>Remaining</p>
					</div>
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
						?> alt="">
						<p><?php
						if(isset($persona_one_purchase_count[1])){
							echo get_value_from_stdClass($persona_one_purchase_count[1]);
						}
						else{
							echo "0";
						}

						?> <br>PURCHASES</p>
					</div>
				</section>
			</aside>

		</section>

	</section>


	<?php

}
