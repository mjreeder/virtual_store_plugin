<?php

function dcvs_add_money_bar() {

	echo '<p>Money Bar Here</p>';
	
}

add_action( 'wp_footer', 'dcvs_add_money_bar' );