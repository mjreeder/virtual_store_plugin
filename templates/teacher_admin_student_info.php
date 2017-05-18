<?php
?>
	<aside class="studentList">
		<div class="searchBar">
			<img src="<?php echo plugins_url( 'assets/images/search.svg', dirname(__FILE__)); ?>" rel="stylesheet" alt="">
			<input type="search" id='search' placeholder="search" oninput="studentSearch()" autocomplete="off">
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
	if (isset($_REQUEST['student_id'])) {
		$users = DCVS_Store_Management::get_active_users();
		if (!in_array($_REQUEST['student_id'], $users)) {
			$currentDisplayStudent = NULL;
			$display_name = NULL;
			$warehouse_purchase_sum = NULL;
			echo '<div class="noUsers"><h1>No Users In Sytem, Go to Manage Students Tab to Create Students</h1></div>';
			return;
		} else {
			$currentDisplayStudent = $_REQUEST['student_id'];
			$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudent));
			$business_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business LEFT JOIN dcvs_user_business ON dcvs_business.id=dcvs_user_business.business_id WHERE user_id = %d', $currentDisplayStudent));
			$warehouse_purchase_sum = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_warehouse_purchase WHERE user_id = %d', $currentDisplayStudent));
		}
	}

	if (isset($warehouse_purchase_sum[0]) && isset($business_info[0])) {
		$budget_remaining = $business_info[0]->money - get_value_from_stdClass($warehouse_purchase_sum[0]);
	}

	$persona_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $currentDisplayStudent));
	if (isset($business_info[0])) {
		$number_of_shoppers = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT business_id) FROM dcvs_business_purchase WHERE business_id = %d', $business_info[0]->id));
		$totalSpentOnWarehouse = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_warehouse_purchase WHERE user_id = %d', $currentDisplayStudent));
		$revenue = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase WHERE business_id = %d', $business_info[0]->id));
		$profit = (get_value_from_stdClass($revenue[0])) - (get_value_from_stdClass($totalSpentOnWarehouse[0]));
	}
	if (isset($persona_info[0])) {
		$persona_one_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $persona_info[0]->persona_id));
		$persona_one_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $currentDisplayStudent, $persona_info[0]->persona_id));
	}

	if (isset($persona_info[1])) {
		$persona_two_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $persona_info[1]->persona_id));
		$persona_two_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $currentDisplayStudent, $persona_info[1]->persona_id));

	}

	$warehouse_evaluation_id = 1;
	$shopping_evaluation_id = 2;
	$end_of_shopping_evaluation_id = 3;
	$personal_store_evaluation_id = 4;
	$shopping_evaluation_persona_key = 13;
	$end_of_shopping_evaluation_persona_key = 3;


	?>
	<section class="studentInfo" id='mainView'>
	<div class="scrollable">
	<?php if (isset($display_name[0])) {
	?>
	<h1 class="title"><?php echo $display_name[0]->display_name ?></h1>
	<?php
} else {
	?>
	<h1 class="title"><?php echo "User not set" ?></h1>
	<?php
}
	?>
	<section class="merchandiserInfo">
		<h2 class="subTitle">Buyer</h2>
		<?php
		if (isset($business_info[0])) {
			$user_blog_id = intval(get_user_blog_id($business_info[0]->user_id));
			switch_to_blog($user_blog_id);
			$site_name = get_bloginfo('name');
			restore_current_blog();
			?>
			<h3><?php echo stripslashes_deep($site_name); ?></h3>
			<?php
		} else {
			?>
			<h3><?php echo "Business not set for user" ?></h3>
			<?php
		}

		?>

		<section>
			<aside class="merchandiserLeft">
				<?php
				if (isset($business_info[0])) {
					?>
					<a href="<?php echo $business_info[0]->url ?>" class="button">PERSONAL SITE</a>
					<?php
				} else {
					?>
					<a href="#" class="button unavailable">PERSONAL SITE</a>
					<?php
				}
				?>
				<?php
				$search_criteria = array('field_filters' => array());
				$search_criteria['field_filters'][] = array(
					'key' => 'created_by',
					'value' => $currentDisplayStudent
				);
				$entries = GFAPI::get_entries($personal_store_evaluation_id, $search_criteria);

				if ($entries) {
					echo '<a href="' . get_site_url() . '/wp-admin/admin.php?page=gf_entries&view=entry&id=' . $personal_store_evaluation_id . '&lid=' . $entries[0]["id"] . '" class="button">FINAL SURVEY</a>';
				} else {
					echo '<a href="" class="button unavailable">FINAL SURVEY</a>';
				}
				$entries = GFAPI::get_entries($warehouse_evaluation_id, $search_criteria);
				?>
				<div class="">
					<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $currentDisplayStudent . '&section=surveys&form_id=' . $warehouse_evaluation_id; ?>"
					   class="button">WAREHOUSE SURVEYS</a>
					<a href="<?php echo plugins_url( 'templates/store_survey_list.php', dirname(__FILE__)) ?>"><button class="button btnStore">STORE FEEDBACK</button></a>
				</div>

				<!-- TODO get remaining budget-->
				<?php
				if (isset($budget_remaining)) {
					?>
					<span><b>BUDGET REMAINING:</b> $<?php echo number_format($budget_remaining, 2) ?></span>
					<?php
				} else {
					?>
					<span>No Budget</span>
					<?php
				}

				?>

			</aside>
			<aside class="merchandiserRight">
				<section class="facts">
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
						?> alt="">
						<?php
						if (isset($profit)) {
							if ($profit >= 0) {
								?>
								<p>$<?php echo number_format($profit, 2) ?> <br>PROFIT</p>
								<?php
							} else {
								?>
								<p class="negativeProfit">$<?php echo number_format($profit, 2) ?> <br>PROFIT</p>
								<?php
							}
						} else {
							?>
							<p>$0 <br>PROFIT</p>
							<?php
						}

						?>

					</div>
					<div class="fact">
						<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
						?>  alt="">
						<p><?php
							if (isset($number_of_shoppers[0])) {
								echo get_value_from_stdClass($number_of_shoppers[0]);
							} else {
								echo "0";
							}

							?> <br>SHOPPERS</p>
					</div>
				</section>
				<!-- TODO comparison page -->
				<?php
				if (isset($currentDisplayStudent) && $currentDisplayStudent != '') {
					?>
					<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $_REQUEST['student_id'] . '&section=stats&user_id=' . $currentDisplayStudent ?>"
					   class="button">
						STATISTICS
					</a>
					<?php
				} else {
					?>
					<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $_REQUEST['student_id'] . '&section=stats&user_id=' . $currentDisplayStudent ?>"
					   class="unavailable">
						STATISTICS
					</a>
					<?php
				}
				?>
			</aside>
		</section>
	</section>
	<section class="shopperInfo">

	<aside class="shopperOne">
	<h2 class="subTitle">Consumer #1</h2>
	<?php
	if (isset($persona_info[0])) {
		?>
		<h3><?php echo stripslashes_deep($persona_info[0]->name); ?></h3>
		<?php
	} else {
		?>
		<h3><?php echo "Persona One not set"; ?></h3>
		<?php
	}

	if (isset($persona_info[0])) {
		?>
		<?php
		if (isset($currentDisplayStudent)) {
			?>
			<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $_REQUEST['student_id'] . '&section=order_history&user_id=' . $currentDisplayStudent . '&persona_id=' . $persona_info[0]->persona_id; ?>"
			   class="button">
				ORDER HISTORY
			</a>
			<?php
		} else {
			?>
			<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $_REQUEST['student_id'] . '&section=order_history&user_id=' . $currentDisplayStudent . '&persona_id=' . $persona_info[0]->persona_id; ?>"
			   class="unavailable">
				ORDER HISTORY
			</a>
			<?php
		}

	} else {
		?>
		<a href="#" class="button unavailable">
			NO CONSUMER
		</a>
		<?php
	}
	?>

	<?php
	$search_criteria = array('field_filters' => array());
	$search_criteria['field_filters'][] = array(
		'key' => 'created_by',
		'value' => $currentDisplayStudent
	);
	if (isset($persona_info[0])) {
		$search_criteria['field_filters'][] = array(
			'key' => $end_of_shopping_evaluation_persona_key,
			'value' => $persona_info[0]->id
		);
	} else {
		$search_criteria['field_filters'][] = array(
			'key' => $end_of_shopping_evaluation_persona_key,
			'value' => -1
		);
	}

	$entries = GFAPI::get_entries($end_of_shopping_evaluation_id, $search_criteria);

	if ($entries) {
		echo '<a href="' . get_site_url() . '/wp-admin/admin.php?page=gf_entries&view=entry&id=' . $end_of_shopping_evaluation_id . '&lid=' . $entries[0]["id"] . '" class="button">FINAL SURVEY</a>';
	} else {
		echo '<a href="" class="button unavailable">FINAL SURVEY</a>';
	}
	if (isset($persona_info[0])) {
		$search_criteria['field_filters'][1] = array(
			'key' => $shopping_evaluation_persona_key,
			'value' => $persona_info[0]->persona_id
		);
	} else {
		$search_criteria['field_filters'][1] = array(
			'key' => $shopping_evaluation_persona_key,
			'value' => -1
		);
		$entries = GFAPI::get_entries($warehouse_evaluation_id, $search_criteria);
		?>
		<div class="">
			<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $currentDisplayStudent . '&section=surveys&form_id=' . $warehouse_evaluation_id; ?>"
			   class="button">WAREHOUSE SURVEYS</a>
			<a href="<?php echo plugins_url('templates/store_survey_list.php', dirname(__FILE__)) ?>">
				<button class="button btnStore">STORE FEEDBACK</button>
			</a>
		</div>

		<!-- TODO get remaining budget-->
		<?php
		if (isset($budget_remaining)) {
			?>
			<span><b>BUDGET REMAINING:</b> $<?php echo number_format($budget_remaining, 2) ?></span>
			<?php
		} else {
			?>
			<span>No Budget</span>
			<?php
		}

		$entries = GFAPI::get_entries($shopping_evaluation_id, $search_criteria);

		if (count($entries)) {
			echo "<a href='" . get_site_url() . "/wp-admin/admin.php?page=dcvs_teacher&student_id=" . $currentDisplayStudent . "&section=surveys&form_id=" . $shopping_evaluation_id . "&persona_field_key=" . $shopping_evaluation_persona_key . "&persona_id=" . $persona_info[0]->persona_id . "' class='button' > SHOPPING SURVEYS </a >";
		} else {
			echo "<a href='" . get_site_url() . "/wp-admin/admin.php?page=dcvs_teacher&student_id=" . $currentDisplayStudent . "&section=surveys&form_id=" . $shopping_evaluation_id . "&persona_field_key=" . $shopping_evaluation_persona_key . "&persona_id=" . $persona_info[0]->persona_id . "' class='button unavailable' > SHOPPING SURVEYS </a >";
		}
		?>
		<section class="facts">
			<div class="fact">
				<img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
				?> alt="">
				<p><?php
					if (isset($persona_one_total_money[0])) {
						$difference = get_value_from_stdClass($persona_one_total_money[0]) - get_value_from_stdClass($persona_one_money_spent[0]);
						echo '$' . number_format($difference, 2);
					} else {
						echo "N/A";
					}
					?>
					<br>Remaining</p>
			</div>
			<div class="fact">
				<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
				?> alt="">
				<p><?php
					if (isset($persona_info[0])) {
						$persona_one_purchase_count_sql = $wpdb->get_results($wpdb->prepare('SELECT COUNT(user_persona_id) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id=dcvs_user_persona.id WHERE user_id=%d AND persona_id=%d ', $currentDisplayStudent, $persona_info[0]->id));
						$persona_one_purchase_count = get_value_from_stdClass($persona_one_purchase_count_sql[0]);
						if ($persona_one_purchase_count >= 1) {
							echo get_value_from_stdClass($persona_one_purchase_count_sql[0]);
						} else {
							echo "0";
						}
					} else {
						echo "0";
					}
					?> <br>PURCHASES</p>
			</div>
		</section>
		</aside>

		<aside class="shopperTwo">
			<h2 class="subTitle">Consumer #2</h2>
			<?php
			if (isset($persona_info[1])) {
				?>
				<h3><?php echo stripslashes_deep($persona_info[1]->name); ?></h3>
				<?php
			} else {
				?>
				<h3><?php echo "Persona One not set" ?></h3>
				<?php
			}

			if (isset($persona_info[1])) {
				?>
				<?php
				if (isset($currentDisplayStudent)) {
					?>
					<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $_REQUEST['student_id'] . '&section=order_history&user_id=' . $currentDisplayStudent . '&persona_id=' . $persona_info[1]->persona_id ?>"
					   class="button">
						ORDER HISTORY
					</a>
					<?php
				} else {
					?>
					<a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $_REQUEST['student_id'] . '&section=order_history&user_id=' . $currentDisplayStudent . '&persona_id=' . $persona_info[1]->persona_id; ?>"
					   class="unavailable">
						ORDER HISTORY
					</a>
					<?php
				}
			} else {
				?>
				<a href="#" class="button unavailable">
					NO CONSUMER
				</a>
				<?php
			}
			?>

			<?php
			$search_criteria = array('field_filters' => array());
			$search_criteria['field_filters'][] = array(
				'key' => 'created_by',
				'value' => $currentDisplayStudent
			);
			if (isset($persona_info[1])) {
				$search_criteria['field_filters'][] = array(
					'key' => $end_of_shopping_evaluation_persona_key,
					'value' => $persona_info[1]->id
				);
			} else {
				$search_criteria['field_filters'][] = array(
					'key' => $end_of_shopping_evaluation_persona_key,
					'value' => -1
				);
			}

			$entries = GFAPI::get_entries($end_of_shopping_evaluation_id, $search_criteria);
			if ($entries) {
				echo '<a href="' . get_site_url() . '/wp-admin/admin.php?page=gf_entries&view=entry&id=' . $end_of_shopping_evaluation_id . '&lid=' . $entries[0]["id"] . '" class="button">FINAL SURVEY</a>';
			} else {
				echo '<a href="" class="button unavailable">FINAL SURVEY</a>';
			}
			if (isset($persona_info[1])) {
				$search_criteria['field_filters'][1] = array(
					'key' => $shopping_evaluation_persona_key,
					'value' => $persona_info[1]->persona_id
				);
			} else {
				$search_criteria['field_filters'][1] = array(
					'key' => $shopping_evaluation_persona_key,
					'value' => -1
				);
			}

			$entries = GFAPI::get_entries($shopping_evaluation_id, $search_criteria);
			if (count($entries)) {
				echo "<a href='" . get_site_url() . "/wp-admin/admin.php?page=dcvs_teacher&student_id=" . $currentDisplayStudent . "&section=surveys&form_id=" . $shopping_evaluation_id . "&persona_field_key=" . $shopping_evaluation_persona_key . "&persona_id=" . $persona_info[1]->persona_id . "' class='button' > SHOPPING SURVEYS </a >";
			} else {
				echo "<a href='" . get_site_url() . "/wp-admin/admin.php?page=dcvs_teacher&student_id=" . $currentDisplayStudent . "&section=surveys&form_id=" . $shopping_evaluation_id . "&persona_field_key=" . $shopping_evaluation_persona_key . "&persona_id=" . $persona_info[1]->persona_id . "' class='button unavailable' > SHOPPING SURVEYS </a >";
			}
			?>
			<section class="facts">
				<div class="fact">
					<img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
					?> alt="">
					<p><?php
						if (isset($persona_two_total_money[0])) {
							$difference = get_value_from_stdClass($persona_two_total_money[0]) - get_value_from_stdClass($persona_two_money_spent[0]);
							echo '$' . number_format($difference, 2);
						} else {
							echo "N/A";
						}
						?>
						<br>Remaining</p>
				</div>
				<div class="fact">
					<img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
					?> alt="">
					<p><?php
						if (isset($persona_info[1])) {
							$persona_two_purchase_count_sql = $wpdb->get_results($wpdb->prepare('SELECT COUNT(user_persona_id) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id=dcvs_user_persona.id WHERE user_id=%d AND persona_id=%d ', $currentDisplayStudent, $persona_info[1]->id));
							$persona_one_purchase_count = get_value_from_stdClass($persona_two_purchase_count_sql[0]);
							if ($persona_one_purchase_count >= 1) {
								echo get_value_from_stdClass($persona_two_purchase_count_sql[0]);
							} else {
								echo "0";
							}
						} else {
							echo "0";
						}
						?> <br>PURCHASES</p>
				</div>
			</section>
		</aside>

		</section>
		</div>
		<!-- END OF SCROLLABLE DIV  -->
		</section>


		<?php

	}
}
?>
