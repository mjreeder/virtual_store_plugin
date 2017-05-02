<?php
/**
 * Plugin Name: Digital Corps Virtual Store Plugin
 * Description: Plugin used for class
 * Version: 0.1
 * Author: Digital Corps, Ball State University.
 */
defined('ABSPATH') or die('invalid access');

require_once __DIR__ . "/models/warehouse_checkout.php";
require_once __DIR__ . "/models/dcvs_toast.php";
require_once __DIR__."/teacher_admin_panel.php";
require_once __DIR__."/landing_page.php";
require_once __DIR__."/purchase_functions.php";
require_once __DIR__."/personas_admin_settings.php";
require_once __DIR__."/businesses_admin_settings.php";
require_once __DIR__."/user_persona_assignment.php";
require_once __DIR__."/money_bar.php";
require_once __DIR__."/evaluations.php";
require_once __DIR__."/store_management.php";

add_action('admin_init', 'dcvs_remove_footer');
add_action('woocommerce_review_order_before_payment', 'dcvs_before_cart_contents');
add_action('woocommerce_review_order_after_payment', 'dcvs_after_cart_contents');
add_action('woocommerce_checkout_before_customer_details', 'dcvs_before_billing_form');
add_action('woocommerce_checkout_after_customer_details', 'dcvs_after_billing_form');
add_action('admin_enqueue_scripts', 'dcvs_enqueue_admin_script' );
add_action('init', 'dcvs_plugin_init');
add_action('current_screen', 'dcvs_new_post_redirect');
add_action('current_screen', 'dcvs_enqueue_product_edit_styles');

add_filter('woocommerce_checkout_fields' , 'dcvs_override_checkout_fields');
add_filter('woocommerce_coupons_enabled', 'dcvs_hide_coupon_field_on_cart');
add_filter('woocommerce_coupons_enabled', 'dcvs_hide_coupon_field_on_checkout');
add_filter('woocommerce_is_purchasable', 'dvcs_is_purchasable', 10, 2);
add_filter('woocommerce_variation_is_purchasable', 'dvcs_is_purchasable', 10, 2);
add_filter('gettext', 'dvcs_customize_product_variation_message', 10, 3);
add_filter('woocommerce_product_data_tabs', 'dcvs_remove_product_tabs', 10, 1);
add_filter("woocommerce_attribute_taxonomies", 'dcvs_woocommerce_taxonomies');

register_activation_hook(__FILE__, 'dcvs_activation_plugin');
function dcvs_activation_plugin()
{
    include __DIR__.'/database_setup.php';
}

function dcvs_plugin_init()
{
    register_taxonomy("pa_size", "product");
    register_taxonomy("pa_color", "product");
    //if the user is not logged in redirect them to do so
    if (!is_user_logged_in() && $GLOBALS['pagenow'] !== 'wp-login.php') {
        auth_redirect();
        exit;
    }
    include __DIR__.'/database_setup.php';
    //create the landing page if it doesn't exist already
    $landingPage = get_page_by_path('virtual-store-landing');
    if ($landingPage == null) {
        wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Virtual Store Landing Page', 'post_name' => 'virtual-store-landing'));
    } elseif ($landingPage->post_status == 'trash') {
        wp_update_post(array('ID' => $landingPage->ID, 'status' => 'publish'));
    }
    // set default options if they are not currently set
    dcvs_set_default_options();

}

function dcvs_woocommerce_taxonomies($array){
	global $wpdb;
	return $attribute_taxonomies = $wpdb->get_results( "SELECT * FROM wp_woocommerce_attribute_taxonomies order by attribute_name ASC;" );
}

function dcvs_new_post_redirect() {
    if (is_user_logged_in()) {
        if (!is_super_admin(get_current_user_id())) {
            $screen = get_current_screen();
            if ($screen->action == "add" && $screen->id == "product") {
                wp_safe_redirect( get_home_url().'/wp-admin/edit.php?post_type=product' );
            }
        }
    }
}

function dcvs_remove_footer()
{
    add_filter( 'admin_footer_text', '__return_false', 11 );
    add_filter( 'update_footer', '__return_false', 11 );
}

function dcvs_before_cart_contents()
{
    ?>
    <div class="cart-export">
        <?php
//            echo '<pre>';
//            echo var_dump(WC()->cart->get_cart());
//            echo '</pre>';
        ?>
    </div>
    <!--
    This combined with dc_after_cart_contents create a hidden
    div that removes the payment processing section of checkout
    -->
<!--    <div class="hide-payment" style="display:none;">-->
    <?php
}

//This function is used to close the hidden div that hides the payment processing section of checkout
function dcvs_after_cart_contents()
{
    ?>
<!--        </div>-->
    <?php

}

function dcvs_set_default_options() {
    if (dcvs_get_option( 'default_business_money' ) == null) {
        dcvs_set_option( 'default_business_money', '2500' );
    }
    if (dcvs_get_option( 'default_persona_money' ) == null) {
        dcvs_set_option( 'default_persona_money', '1500' );
    }
    if (dcvs_get_option( 'warehouse_start_date' ) == null) {
        dcvs_set_option( 'warehouse_start_date', '2017-01-01' );
    }
    if (dcvs_get_option( 'warehouse_end_date' ) == null) {
        dcvs_set_option( 'warehouse_end_date', '2017-01-02' );
    }
    if (dcvs_get_option( 'shopping_start_date' ) == null) {
        dcvs_set_option( 'shopping_start_date', '2017-01-03' );
    }
    if (dcvs_get_option( 'shopping_end_date' ) == null) {
        dcvs_set_option( 'shopping_end_date', '2017-01-04' );
    }
}

function dcvs_get_option($key, $default_value = null)
{
    global $wpdb;

    $result = $wpdb->get_var("SELECT option_value FROM dcvs_options WHERE option_key='".esc_sql($key)."'");

    return $result == null ? $default_value : $result;
}

function dcvs_set_option($key, $value)
{
    global $wpdb;
    if (dcvs_get_option($key) != null) {
        $wpdb->update('dcvs_options', array('option_value' => $value), array('option_key' => $key));
    } else {
        $wpdb->insert('dcvs_options', ['option_key' => $key, 'option_value' => $value]);
    }
}

function dcvs_echo_option($key, $default_value = false)
{
    echo dcvs_get_option($key, $default_value);
}

function calculate_spent($costs) {
  $spent = 0;
  for($i = 0; $i < sizeof($costs); $i++) {
    $costObject = get_object_vars($costs[$i]);
    $cost = $costObject["cost"];
    $spent+=$cost;
  }
  return $spent;
}

function money_is_number($money) {
  return filter_var($money, FILTER_VALIDATE_FLOAT);
}

function fields_are_blank($array) {
  foreach ($array as $s) {
    if($s == "") {
      return true;
    }
  }
  return false;
}

function landing_page_widget_display() {
    $landing_page_url = dcvs_get_landing_page_url();
    ?>

    <a href="<?php echo $landing_page_url ?>"><?php echo $landing_page_url ?></a>

    <?php
}

function dcvs_get_landing_page_url($user_id = NULL) {
    $plugin_basename = plugin_basename( __FILE__ );
    $split_basename = explode("/",$plugin_basename);
    $plugin_name = $split_basename[0];

    if ($user_id == NULL) {
        $user_id = get_current_user_id();
    }

    $user_business = dcvs_get_business_by_user_id( $user_id );
    $site_url = $user_business['url'];
    $landing_page_url = $site_url . 'wp-content/plugins/' . $plugin_name . '/templates/landing.php';


    return $landing_page_url;
}

function dcvs_get_store_list_url() {
    $plugin_basename = plugin_basename( __FILE__ );
    $split_basename = explode("/",$plugin_basename);
    $plugin_name = $split_basename[0];

    $user_business = dcvs_get_business_by_user_id( get_current_user_id() );
    $site_url = $user_business['url'];
    $store_list_url = $site_url . '/wp-content/plugins/' . $plugin_name . '/templates/stores.php';
    return $store_list_url;
}

function dcvs_before_billing_form()
{
    ?>

    <div style="display:none;">
    <?php
}

function dcvs_after_billing_form()
{
    ?>
    </div>
    <?php

}

function dcvs_override_checkout_fields( $fields )
{
    unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    unset($fields['order']['order_comments']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_email']);
    unset($fields['billing']['billing_city']);
    return $fields;
}

function dcvs_hide_coupon_field_on_cart( $enabled )
{
    if ( is_cart() ) {
        $enabled = false;
    }
    return $enabled;
}

function dcvs_hide_coupon_field_on_checkout( $enabled )
{
    if ( is_checkout() ) {
        $enabled = false;
    }
    return $enabled;
}

function dvcs_is_purchasable($is_purchasable, $product)
{
    if (get_current_blog_id() == get_user_blog_id( get_current_user_id())) {
        return false;
    } else {
        return true;
    }
}

function dvcs_customize_product_variation_message( $translated_text, $untranslated_text, $domain )
{
    if ($untranslated_text == 'Sorry, this product is unavailable. Please choose a different combination.') {
        if (get_current_blog_id() == get_user_blog_id( get_current_user_id())) {
            $translated_text = __( "You can't buy products from your own store! \n\nPlease choose another store!", $domain );
        }
    } else if ($untranslated_text == 'Please select some product options before adding this product to your cart.') {
        if (get_current_blog_id() == get_user_blog_id( get_current_user_id())) {
            $translated_text = __( "You can't buy products from your own store! \n\nPlease choose another store!", $domain );
        }
    }
    return $translated_text;
}

function dcvs_remove_product_tabs($tabs)
{

    if (get_current_blog_id() != 1) {
        unset($tabs['inventory']);
        unset($tabs['shipping']);
         unset($tabs['attribute']);
        unset($tabs['advanced']);
    }

    return($tabs);

}

function dcvs_enqueue_admin_script()
{
    if (get_current_blog_id() != 1) {
        wp_register_script( 'dcvs_product_edit_script', plugins_url( '/js/editProduct.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
        wp_enqueue_script( 'dcvs_product_edit_script' );
    }
    wp_enqueue_style( 'dcvs_wordpress_dashboard_style', plugins_url( '/assets/css/dcvsWordpressDashboard.css', __FILE__ ) );
}

function dcvs_enqueue_product_edit_styles() {
    if (is_user_logged_in()) {
        if (!is_super_admin(get_current_user_id())) {
            $screen = get_current_screen();
            if ($screen->post_type == "product") {
                wp_enqueue_style( 'dcvs_product_edit_style', plugins_url( '/assets/css/editProduct.css', __FILE__ ) );
            }
        }
    }
}

//https://trickspanda.com/force-users-login-viewing-wordpress/

function dcvs_getUrl() {
    $url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
    $url .= '://' . $_SERVER['SERVER_NAME'];
    $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
    $url .= $_SERVER['REQUEST_URI'];
    return $url;
}
function dcvs_forcelogin() {
    if( !is_user_logged_in() ) {
        $url = dcvs_getUrl();
        $whitelist = apply_filters('dcvs_forcelogin_whitelist', array());
        $redirect_url = apply_filters('dcvs_forcelogin_redirect', $url);
        if( preg_replace('/\?.*/', '', $url) != preg_replace('/\?.*/', '', wp_login_url()) && !in_array($url, $whitelist) ) {
            wp_safe_redirect( wp_login_url( $redirect_url ), 302 ); exit();
        }
    }
}
add_action('init', 'dcvs_forcelogin');

function dcvs_redirect_to_dashboard($redirect_to, $request, $user){
    if( !is_wp_error($user) && !is_super_admin($user->ID) ){
        //we're supposed to just be able to return a changed $redirect_to,
        //but it appears something else is taking control there, so we'll just do it directly.

        wp_redirect(dcvs_get_landing_page_url($user->ID));
        exit; //wp_redirect doesn't exit by itself
    }
    return $redirect_to;
}

add_filter( 'login_redirect', 'dcvs_redirect_to_dashboard', 10, 3 );

function dcvs_redirect_if_no_current_persona() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        if (!is_super_admin($user_id)) {
            $persona = dcvs_get_current_persona( $user_id );
            $user_business_id = get_user_blog_id( $user_id );

            if (get_current_blog_id() != $user_business_id && get_current_blog_id() != 1) {
                if ($persona == null) {
                    wp_redirect(dcvs_get_landing_page_url($user_id));
                    exit;
                }
            }
        }
    }
}
add_action('init', 'dcvs_redirect_if_no_current_persona');

function dcvs_get_user_persona_ids($user_id) {
    global $wpdb;
    $persona_ids = $wpdb->get_results("SELECT persona_id FROM dcvs_user_persona WHERE user_id = '".esc_sql($user_id)."'");
    return $persona_ids;
}

function dcvs_get_persona_category($persona_id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM dcvs_persona_category WHERE persona_id = '%d'", [$persona_id]);
    $response = $wpdb->get_results($sql, ARRAY_A);
    return $response;
}

function dcvs_get_user_business_category($user_id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM dcvs_category WHERE id IN (SELECT category_id FROM dcvs_business_category WHERE business_id IN (SELECT business_id FROM dcvs_user_business WHERE user_id ='%d'))", [$user_id]);
    $response = $wpdb->get_results($sql, ARRAY_A);
    return $response;
}

add_action('wp_footer', 'dcvs_disable_checkout_button');

function dcvs_disable_checkout_button() {
    global $post;

    if ($post != null) {
        if ( $post->post_name == 'checkout' ) {


            $user_id = get_current_user_id();

            $cart_cost = dcvs_get_cart_cost();

            $persona = dcvs_get_current_persona($user_id);

            $user_persona_ids = dcvs_get_user_persona_ids( $user_id );

            if (get_current_blog_id() != 1 && get_user_blog_id( $user_id ) != get_current_blog_id()) {
                $persona_budget = $persona['money'];
                $persona_expense = dcvs_get_persona_expenses($user_id, $persona['id']);

                $current_budget = $persona_budget - $persona_expense - $cart_cost;

                if ($current_budget < 0) {
                    wp_enqueue_style( 'dcvs_checkout_button_style', plugins_url( '/assets/css/checkoutButton.css', __FILE__ ) );
                }

            }

        }
    }

}
