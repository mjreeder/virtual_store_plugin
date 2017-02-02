
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
  </div>
    <?php

}
