<?php

// TODO: Add functionality to edit to template

?>

<section class="settings">

	<h1 class="title">General Settings</h1>

	<h2 class="subTitle">budgets</h2>

	<section class="settingsSub">

		<div>
			<h3>DEFAULT BUYER BUDGET</h3>
			<div class="budgetInput">
				<p>$</p>
				<input type="text" placeholder="10,000" value="<?php dcvs_echo_option('default_business_money')?>">

			</div>
		</div>

		<div>
			<h3>DEFAULT CONSUMER BUDGET</h3>
			<div class="budgetInput">
				<p>$</p>
				<input type="text" placeholder="10,000" value="<?php dcvs_echo_option('default_persona_money')?>">

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
					<input type="date" placeholder="2 / 17">
				</div>
				<div class="dateInput">
					<p>END</p>
					<input type="date" value="<?php dcvs_echo_option('warehouse_end_date')?>">
				</div>
			</div>
		</div>
		<div>
			<h3>CONSUMER TIMES</h3>
			<div>
				<div class="dateInput">
					<p>START</p>
					<input type="date" placeholder="2 / 17">
				</div>
				<div class="dateInput">
					<p>END</p>
					<input type="date" value="<?php dcvs_echo_option('shopping_end_date')?>">
				</div>
			</div>

		</div>

	</section>

</section>
