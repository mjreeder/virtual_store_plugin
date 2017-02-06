
<h1>Teacher Admin</h1>
<?php
global $wpdb;
$results = $wpdb->get_results('SELECT * FROM dcvs_user_business', OBJECT);
for ($i = 0; $i < sizeof($results); ++$i) {
    $display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $results[$i]->user_id));
    $business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $results[$i]->business_id));
    $personas = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.id WHERE user_id = %d', $results[$i]->user_id));
    ?>
  <div>
    <label name="student_name" type="text"><?php echo $display_name[0]->display_name;
    ?></label>
      <div><?php echo $business[0]->url;
    ?></div>
    <div><?php echo $personas[0]->name;
    ?></div>
  <div><?php echo $personas[1]->name;
    ?></div>
    <?php var_dump(get_persona_cart($results[$i]->user_id));
      ?></div>
  </div>

    <?php

}
?>
change end shopping time<input type='text'/>
<button>update</button>
<?php


function get_store_info($business_id){
  global $wpdb;
  $business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $business_id));
  $current_money = $business->money;
  $description = $business->description;
  $display_name = $business->display_name;

}

function get_persona_cart($user_id){
  global $wpdb;
  $user_cart_meta_data = $business = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM wp_usermeta WHERE user_id = %d AND meta_key = '_woocommerce_persistent_cart'", $user_id));
  return $user_cart_meta_data;
}

function get_time_shopping_time_remaining(){
  global $wpdb;
  $result = $wpdb->get_var("SELECT option_value FROM dcvs_options WHERE option_key='shopping_end_date'");
  var_dump($result);
}
