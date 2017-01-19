<?php
/**
 * Plugin Name: Digital Corps Woocommerce Exporter
 * Description: Used to export a Woocommerce cart
 * Version: 0.1
 * Author: Digital Corps, Ball State University
 */
defined( 'ABSPATH' ) or die( 'invalid access' );

add_action("woocommerce_review_order_before_payment", "dc_before_cart_contents");
add_action("woocommerce_review_order_after_payment", "dc_after_cart_contents");
add_action("init", "dc_plugin_init");

function dc_plugin_init(){
    //if the user is not logged in redirect them to do so
    if ( !is_user_logged_in() && $GLOBALS['pagenow'] !== 'wp-login.php'  ) {
        auth_redirect();
        exit;
    }
}


function dc_before_cart_contents(){
    ?>
    <div class="cart-export">
        <?php
            echo "<pre>";
            echo(var_dump(WC()->cart->get_cart()));
            echo "</pre>";
        ?>
    </div>
    <!--
    This combined with dc_after_cart_contents create a hidden
    div that removes the payment processing section of checkout
    -->
    <div class="hide-payment" style="display:none;">
    <?php

}

//This function is used to close the hidden div that hides the payment processing section of checkout
function dc_after_cart_contents(){
    ?>
        </div>
    <?php
}

?>