<?php

// TODO: Add some kind of confirmation that save was completed

if(isset($_REQUEST['submit'])) {
	$default_business_money = $_REQUEST['default_business_money'];
	$default_persona_money = $_REQUEST['default_persona_money'];
	$warehouse_start_date = $_REQUEST['warehouse_start_date'];
	$warehouse_end_date = $_REQUEST['warehouse_end_date'];
	$shopping_start_date = $_REQUEST['shopping_start_date'];
	$shopping_end_date = $_REQUEST['shopping_end_date'];

	if (isset($default_business_money)) {
		dcvs_set_option( 'default_business_money', $default_business_money );
	}
	if (isset($default_persona_money)) {
		dcvs_set_option( 'default_persona_money', $default_persona_money );
	}
	if (isset($warehouse_start_date)) {
		dcvs_set_option( 'warehouse_start_date', $warehouse_start_date );
	}
	if (isset($warehouse_end_date)) {
		dcvs_set_option( 'warehouse_end_date', $warehouse_end_date );
	}
	if (isset($shopping_start_date)) {
		dcvs_set_option( 'shopping_start_date', $shopping_start_date );
	}
	if (isset($shopping_end_date)) {
		dcvs_set_option( 'shopping_end_date', $shopping_end_date );
	}
}

?>

<section class="settings">
	<form>

		<input type="hidden" name="page" value="dcvs_teacher">
		<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
		<input type="hidden" name="section" value="settings">


		<h1 class="title">General Settings</h1>

		<h2 class="subTitle">budgets</h2>

		<section class="settingsSub">

			<div>
				<h3>DEFAULT BUYER BUDGET</h3>
				<div class="budgetInput">
					<p>$</p>
					<input type="text" placeholder="10,000" name="default_business_money" value="<?php dcvs_echo_option('default_business_money')?>">

				</div>
			</div>

			<div>
				<h3>DEFAULT CONSUMER BUDGET</h3>
				<div class="budgetInput">
					<p>$</p>
					<input type="text" placeholder="10,000" name="default_persona_money" value="<?php dcvs_echo_option('default_persona_money')?>">

				</div>
			</div>

		</section>

		<h2 class="subTitle">shopping time</h2>

		<section class="settingsSub">

			<div>
				<h3>BUYER TIMES</h3>
				<div>
					<div class="dateInput">
						<p>START</p>
						<input type="date" name="warehouse_start_date" value="<?php dcvs_echo_option('warehouse_start_date')?>">
					</div>
					<div class="dateInput">
						<p>END</p>
						<input type="date" name="warehouse_end_date" value="<?php dcvs_echo_option('warehouse_end_date')?>">
					</div>
				</div>
			</div>
			<div>
				<h3>CONSUMER TIMES</h3>
				<div>
					<div class="dateInput">
						<p>START</p>
						<input type="date" name="shopping_start_date" value="<?php dcvs_echo_option('shopping_start_date')?>">
					</div>
					<div class="dateInput">
						<p>END</p>
						<input type="date" name="shopping_end_date" value="<?php dcvs_echo_option('shopping_end_date')?>">
					</div>
				</div>

			</div>

		</section>


		<button class="saveButton" type="submit" name="submit">SAVE</button>

	</form>

</section>
