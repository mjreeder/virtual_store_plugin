<?php
/**
 * Plugin Name: Digital Corps Virtual Store Plugin
 * Description: Plugin used for class
 * Version: 0.1
 * Author: Digital Corps, Ball State University
 */
defined( 'ABSPATH' ) or die( 'invalid access' );

add_action("woocommerce_review_order_before_payment", "dcvs_before_cart_contents");
add_action("woocommerce_review_order_after_payment", "dcvs_after_cart_contents");
add_action("init", "dcvs_plugin_init");


register_activation_hook( __FILE__, "dcvs_activation_plugin" );
function dcvs_activation_plugin(){
    include "database_setup.php";
}

function dcvs_plugin_init(){
    //if the user is not logged in redirect them to do so
    if ( !is_user_logged_in() && $GLOBALS['pagenow'] !== 'wp-login.php'  ) {
        auth_redirect();
        exit;
    }
    include "database_setup.php";
}


function dcvs_before_cart_contents(){
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
function dcvs_after_cart_contents(){
    ?>
        </div>
    <?php
}

function dcvs_get_option($key, $default_value=false){
    global $wpdb;

    $result = $wpdb->get_var("SELECT option_value FROM dcvs_options WHERE option_key='".esc_sql($key)."'");
    return $result == NULL ? $default_value : $result;
}

function dcvs_set_option($key, $value){
    global $wpdb;
    if(dcvs_get_option($key)!= NULL){
        $wpdb->update("dcvs_options", array("option_value"=>$value), array("option_key"=>$key));
    }else{
        $wpdb->insert("dcvs_options", ["option_key"=>$key, "option_value"=>$value] );
    }
}

?>