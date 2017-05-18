<?php

// TODO: Add some kind of confirmation that save was completed

$toast = null;

if(isset($_REQUEST['submit'])) {
	$error = false;
	$default_business_money = isset($_REQUEST['default_business_money']) ? $_REQUEST['default_business_money'] : dcvs_get_option( 'default_business_money' );
	$default_persona_money = isset($_REQUEST['default_persona_money']) ? $_REQUEST['default_persona_money'] : dcvs_get_option( 'default_persona_money' );
	$warehouse_start_date = isset($_REQUEST['warehouse_start_date']) ? $_REQUEST['warehouse_start_date'] : dcvs_get_option( 'warehouse_start_date' );
	$warehouse_end_date = isset($_REQUEST['warehouse_end_date']) ? $_REQUEST['warehouse_end_date'] : dcvs_get_option( 'warehouse_end_date' );
	$shopping_start_date = isset($_REQUEST['shopping_start_date']) ? $_REQUEST['shopping_start_date'] : dcvs_get_option( 'shopping_start_date' );
	$shopping_end_date = isset($_REQUEST['shopping_end_date']) ? $_REQUEST['shopping_end_date'] : dcvs_get_option( 'shopping_end_date' );

	if ( !is_numeric( $default_business_money ) || !is_numeric( $default_business_money )) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( 'Money must be a number!', true );
	}
	if ( !dcvs_validate_Date( $warehouse_start_date ) && !$error ) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( 'Dates must be in yyyy-mm-dd format!', true );
	}
	if ( !dcvs_validate_Date( $warehouse_end_date ) && !$error ) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( 'Dates must be in yyyy-mm-dd format!', true );
	}
	if ( !dcvs_validate_Date( $shopping_start_date ) && !$error ) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( 'Dates must be in yyyy-mm-dd format!', true );
	}
	if ( !dcvs_validate_Date( $shopping_end_date ) && !$error ) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( 'Dates must be in yyyy-mm-dd format!', true );
	}
	if (strtotime( $warehouse_start_date )  >= strtotime( $warehouse_end_date ) && !$error ) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( '"Buyer Start Time" must be set before "Buyer End Time"', true );
	}
	if (strtotime( $warehouse_end_date )  >= strtotime( $shopping_start_date ) && !$error ) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( '"Buyer End Time" must be set before "Consumer Start Time"', true );
	}
	if (strtotime( $shopping_start_date )  >= strtotime( $shopping_end_date ) && !$error ) {
		$error = true;
		$toast = DCVS_Toast::create_new_toast( '"Consumer Start Time" must be set before "Consumer End Time"', true );
	}

	if (!$error) {
		dcvs_set_option( 'default_business_money', $default_business_money );
		dcvs_set_option( 'default_persona_money', $default_persona_money );
		dcvs_set_option( 'warehouse_start_date', $warehouse_start_date );
		dcvs_set_option( 'warehouse_end_date', $warehouse_end_date );
		dcvs_set_option( 'shopping_start_date', $shopping_start_date );
		dcvs_set_option( 'shopping_end_date', $shopping_end_date );
		$toast = DCVS_Toast::create_new_toast( 'Options Updated!' );
	}

}

//http://stackoverflow.com/questions/19271381/correctly-determine-if-date-string-is-a-valid-date-in-that-format

function dcvs_validate_Date($date)
{
	$d = DateTime::createFromFormat('Y-m-d', $date);
	return $d && $d->format('Y-m-d') === $date;
}

?>

<section class="settings">
	<div class="scrollable">

	<form>

		<input type="hidden" name="page" value="dcvs_teacher">
		<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
		<input type="hidden" name="section" value="settings">


		<h1 class="title">General Settings</h1>

		<h2 class="subTitle">BUDGETS</h2>

		<section class="settingsSub">

			<div>
				<h3>DEFAULT BUYER BUDGET</h3>
				<div class="budgetInput">
					<p>$</p>
					<input type="text" placeholder="10,000" name="default_business_money" value="<?php echo isset($default_business_money) ? $default_business_money : dcvs_get_option('default_business_money')?>" required oninvalid="this.setCustomValidity('Cannot be empty.')" oninput="setCustomValidity('')">

				</div>
			</div>

			<div>
				<h3>DEFAULT CONSUMER BUDGET</h3>
				<div class="budgetInput">
					<p>$</p>
					<input type="text" placeholder="10,000" name="default_persona_money" value="<?php echo isset($default_persona_money) ? $default_persona_money : dcvs_get_option('default_persona_money')?>" required oninvalid="this.setCustomValidity('Cannot be empty.')" oninput="setCustomValidity('')">

				</div>
			</div>

		</section>

		<h2 class="subTitle">SHOPPING TIME</h2>

		<section class="settingsSub">

			<div>
				<h3>BUYER TIMES</h3>
				<div>
					<div class="dateInput">
						<p>START</p>
						<input type="date" name="warehouse_start_date" value="<?php echo isset($warehouse_start_date) ? $warehouse_start_date : dcvs_get_option('warehouse_start_date')?>">
					</div>
					<div class="dateInput">
						<p>END</p>
						<input type="date" name="warehouse_end_date" value="<?php echo isset($warehouse_end_date) ? $warehouse_end_date :  dcvs_get_option('warehouse_end_date')?>">
					</div>
				</div>
			</div>
			<div>
				<h3>CONSUMER TIMES</h3>
				<div>
					<div class="dateInput">
						<p>START</p>
						<input type="date" name="shopping_start_date" value="<?php echo isset($shopping_start_date) ? $shopping_start_date : dcvs_get_option('shopping_start_date')?>">
					</div>
					<div class="dateInput">
						<p>END</p>
						<input type="date" name="shopping_end_date" value="<?php echo isset($shopping_end_date) ? $shopping_end_date : dcvs_get_option('shopping_end_date')?>">
					</div>
				</div>

			</div>

		</section>


		<button class="saveButton" type="submit" name="submit">SAVE</button>

	</form>
	<!-- END OF SCROLLABLE DIV -->
</div>
</section>

<?php

if ($toast != null) {
	echo $toast;

	?>

	<script src="<?php echo plugins_url( 'js/toast.js', dirname(__FILE__)); ?>"></script>

	<?php
}
