
<h1>Teacher Admin</h1>
<?php
get_shopping_end_date();
get_warehouse_end_date();
display_some_random_user_data();


function get_store_info($business_id)
{
    global $wpdb;
    $business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $business_id));
    $current_money = $business->money;
    $description = $business->description;
    $display_name = $business->display_name;
}

function display_some_random_user_data()
{
    global $wpdb;
    $results = $wpdb->get_results('SELECT * FROM dcvs_user_business', OBJECT);
    for ($i = 0; $i < sizeof($results); ++$i) {
        $display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $results[$i]->user_id));
        $business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $results[$i]->business_id));
        // $user_personas =
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
      <?php get_user_persona_order_history($results[$i]->user_id, $personas[0]->id);
        ?></div>
      <?php get_user_persona_order_history($results[$i]->user_id, $personas[1]->id);
        ?></div>
    </div>
      <?php

    }
}

function get_user_persona_order_history($user_id, $persona_id)
{
    global $wpdb;

    $user_persona_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_business_purchase LEFT JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND user_persona_id = %d', $user_id, $persona_id));
    for ($i=0; $i <sizeOf($user_persona_order_history) ; $i++) {
      var_dump($user_persona_order_history[$i]->cost, $user_persona_order_history[$i]->items);
    }
}

function get_shopping_end_date()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['dcvs_admin_changes'] == 1 && isset($_POST['shopping_end_date'])) {
        $shopping_end_date = $_POST['shopping_end_date'];
        if (!DateTime::createFromFormat('Y-m-d', $shopping_end_date)) {
            echo 'shopping end date must be a date in the format of yyyy-mm-dd';

            return;
        }
        dcvs_set_option('shopping_end_date', $shopping_end_date);
    }
    ?>
  <!-- template -->
  <form action="" method="post">
      <input type="hidden" name="dcvs_admin_changes" value="1">
      <label>Shopping end date</label> <input name="shopping_end_date" type="text" value="<?php dcvs_echo_option('shopping_end_date', 0);
    ?>">
      <input type="submit">

  </form>
  <?php

}

function get_warehouse_end_date()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['dcvs_admin_changes'] == 1 && isset($_POST['warehouse_end_date'])) {
        $warehouse_end_date = $_POST['warehouse_end_date'];
        if (!DateTime::createFromFormat('Y-m-d', $warehouse_end_date)) {
            echo 'warehouse end date must be a date in the format of yyyy-mm-dd';

            return;
        }
        dcvs_set_option('warehouse_end_date', $warehouse_end_date);
    }
    ?>
  <!-- template -->
  <form action="" method="post">
      <input type="hidden" name="dcvs_admin_changes" value="1">
      <label>warehouse end date</label> <input name="warehouse_end_date" type="text" value="<?php dcvs_echo_option('warehouse_end_date', 0);
    ?>">
      <input type="submit">

  </form>
  <?php

}
