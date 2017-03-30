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
	$persona_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $currentDisplayStudent));
	$number_of_shoppers = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT business_id) FROM dcvs_business_purchase WHERE business_id = %d', $business_info[0]->id));
	$persona_one_purchase_count = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT user_persona_id) FROM dcvs_business_purchase LEFT JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id=dcvs_user_persona.id WHERE persona_id = %d', $persona_info[0]->persona_id));
	$persona_two_purchase_count = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT user_persona_id) FROM dcvs_business_purchase LEFT JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id=dcvs_user_persona.id WHERE persona_id = %d', $persona_info[1]->persona_id));
	$persona_one_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $persona_info[0]->persona_id));
	$persona_two_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $persona_info[1]->persona_id));
	$persona_one_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $currentDisplayStudent, $persona_info[0]->persona_id));
	$persona_two_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $currentDisplayStudent, $persona_info[1]->persona_id));
	// var_dump($persona_info[1]);
	?>
		<section class="studentInfo" id='mainView'>
		<h1><?php echo $display_name[0]->display_name ?></h1>
		<section class="merchandiserInfo">
			<h2 class="subTitle">buyer</h2>
			<h3><?php echo $business_info[0]->title ?></h3>
			<section>
				<aside class="merchandiserLeft">
					<a href="<?php echo $business_info[0]->url ?>" class="button">Personal Site</a>
					<button class="button">FINAL SURVEY</button>
					<!-- TODO get remaining budget-->
					<span><b>BUDGET REMAINING:</b> $12,000</span>
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
							echo get_value_from_stdClass($number_of_shoppers[0]);
							?> <br>SHOPPERS</p>
						</div>
					</section>
					<!-- TODO comparison page -->
					<a href="../views/comparison.html"><button class="button">STATISTICS</button></a>
				</aside>
			</section>
		</section>
		<section class="shopperInfo">

			<aside class="shopperOne">
				<h2 class="subTitle">consumer #1</h2>
				<h3><?php echo $persona_info[0]->name ?></h3>
				<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id'] .'&section=order_history&user_id='.$currentDisplayStudent.'&persona_id='.$persona_info[0]->persona_id ?>">
					<button class="button one">ORDER HISTORY</button>
				</a>
				<button class="button one">FINAL SURVEY</button>
				<section class="facts">
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
						?> alt="">
						<p><?php
						 $difference = get_value_from_stdClass($persona_one_total_money[0]) - get_value_from_stdClass($persona_one_money_spent[0]);
						 echo '$'.$difference?>
						 <br>Remaining</p>
					</div>
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
						?> alt="">
						<p><?php
						echo get_value_from_stdClass($persona_one_purchase_count[0])
						?> <br>PURCHASES</p>
					</div>
				</section>
			</aside>

			<aside class="shopperTwo">
				<h2 class="subTitle">consumer #2</h2>
				<h3><?php echo $persona_info[1]->name ?></h3>
				<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id'] .'&section=order_history&user_id='.$currentDisplayStudent.'&persona_id='.$persona_info[1]->persona_id ?>">
					<button class="button one">ORDER HISTORY</button>
				</a>
				<button class="button two">FINAL SURVEY</button>
				<section class="facts">
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
						?> alt="">
						<!-- TODO get profit -->
						<p><?php
						 if(isset($persona_two_money_spent[0])){
							 $difference = get_value_from_stdClass($persona_two_total_money[0]) - get_value_from_stdClass($persona_two_money_spent[0]);
							 echo '$'.$difference;
						 }
						 else{
							 echo get_value_from_stdClass($persona_two_total_money[0]);
						 }
						 ?>
						 <br>Remaining</p>
					</div>
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
						?> alt="">
						<!-- TODO get numb of shopperes -->
						<p><?php
						echo get_value_from_stdClass($persona_two_purchase_count[0])
						?> <br>PURCHASES</p>
					</div>
				</section>

			</aside>

		</section>

	</section>


	<?php

}
