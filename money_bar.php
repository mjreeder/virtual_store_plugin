<?php

function dcvs_add_money_bar() {
	wp_enqueue_style( 'budgetBar_css', plugins_url('assets/css/budgetBar.css', __FILE__) );
	
	?>
	<!-- FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">

	<footer class="budgetBar">

		<div class="bar">
			<div class="barLeft"><span><h1>persona #1</h1></span></div>
			<h3>current budget: <span>$10,000<span></h3>
			<a href=""><span>back to store list</span></a>
		</div>

		<h2>you are:</h2>
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi malesuada nibh eu pellentesque interdum. Sed pulvinar orci lacus, vel hendrerit tortor blandit quis. Nullam tempus dolor id tempus volutpat. Ut ultrices vel est et vulputate.</p>

	</footer>
<?php
}

add_action( 'wp_footer', 'dcvs_add_money_bar' );

