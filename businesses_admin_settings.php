<?php
function dcvs_admin_businesses_settings(){
    if($_SERVER['REQUEST_METHOD']=="POST"){
      if(isset($_POST['submit'])) {
        $title = $_POST['business_title'];
        $description = $_POST['business_description'];
        $money = $_POST['business_money'];
        $url = $_POST['business_url'];
        if ($_POST['dcvs_add_new_business'] == 1) {
            dcvs_insert_new_business($title, $description, $money, $url);
        } else if ($_POST['dcvs_change_business'] == 1) {
            $id = $_POST['business_id'];
            dcvs_update_business($id, $title, $description, $money, $url);
        }
      } else if(isset($_POST['delete'])) {
        $id = $_POST['business_id'];
        dcvs_delete_business($id);
      }
    }
    ?>
    <h3>Add New Business</h3>
    <form action="" method="post">
      <table class="form-table">
        <tbody>
        <input type="hidden" name="dcvs_add_new_business" value="1">
        <tr>
          <th scope="row">
            <label for="business_title">Title</label>
          </th>
          <td>
            <input id="business_title" name="business_title" type="text">
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="business_description">Description</label>
          </th>
          <td>
            <textarea id="business_description" name="business_description" type="textarea" rows="5" cols="50"></textarea>
          </td>
        </tr>
        <tr>
          <th>
            <label for="business_money">Money</label>
          </th>
          <td>
            <input id="business_money" name="business_money" type="text" value="<?php dcvs_echo_option("default_business_money", 0); ?>">
          </td>
        </tr>
        <tr>
          <th>
            <label for="business_url">URL</label>
          </th>
          <td>
            <input id="business_url" name="business_url" type="text">
          </td>
        </tr>
      </tbody>
      </table>
        <input class="button-primary" name="submit" type="submit">
        <input type="reset">
    </form>
    <h3>Update Existing Business</h3>
    <?php

    dcvs_get_all_businesses();
}

function dcvs_get_all_businesses() {
    global $wpdb;
    $businesses = $wpdb->get_results("SELECT * FROM dcvs_business");

    for ($i = 0; $i < sizeof($businesses); $i++) {
        $businessarray = get_object_vars($businesses[$i])
        ?>
        <div class="postbox" id="boxid">
          <div class="inside">
            <h3><span> Business <?php echo $i+1 ?></span></h3>
        <form action="" method="post">
          <table class="form-table">
            <tbody>
              <input type="hidden" name="dcvs_change_business" value="1">
              <input type="hidden" name="business_id" value="<?php echo $businessarray["id"]; ?>">
              <tr>
                <th scope="row">
                  <label for="business_title">Title</label>
                </th>
                <td>
                  <input id="business_title" name="business_title" type="text" value="<?php echo $businessarray["title"]; ?>">
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label for="business_description">Description</label>
                </th>
                <td>
                  <textarea id="business_description" name="business_description" type="text" rows="4" cols="50"><?php echo $businessarray["description"]; ?></textarea>
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label for="business_money">Money</label>
                </th>
                <td>
                  <input id="business_money" name="business_money" type="text" value="<?php echo $businessarray["money"]; ?>">
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label for="business_url">URL</label>
                </th>
                <td>
                  <input id="business_url" name="business_url" type="text" value="<?php echo $businessarray["url"]; ?>">
                </td>
              </tr>
            </tbody>
          </table>
          <input class="button-primary" type="submit" name="submit" value="Update">
          <input class="button-secondary delete" type="submit" name="delete" value="Delete">
          <input type="reset">
        </form>
      </div>
      </div>
        <?php
    }
}

function dcvs_insert_new_business($title, $description, $money, $url) {
    global $wpdb;
    $id = dcvs_get_business($title);
    if ($id != NULL) {
        echo "That title is already taken";
    } else if (!money_is_number($money)) {
        echo "Money must be a number";
    } else {
        $wpdb->insert("dcvs_business", ["title"=>$title, "description"=>$description, "money"=>$money, "url"=>$url] );
    }
}

function dcvs_get_business($title, $default_value=false){
    global $wpdb;

    $result = $wpdb->get_var("SELECT id FROM dcvs_business WHERE title='".esc_sql($title)."'");
    return $result == NULL ? $default_value : $result;
}

function dcvs_update_business($id, $title, $description, $money, $url) {
    global $wpdb;
    $checkid = dcvs_get_business($title);
    if ($checkid != NULL && $checkid != $id) {
        echo "You've entered a title that's already taken";
     } else if (!money_is_number($money)) {
         echo "Money must be a number";
     } else {
         $wpdb->update("dcvs_business", array("title"=>$title, "description"=>$description,"money"
         =>$money, "url"=>$url), array("id"=>$id));
     }
}

function dcvs_delete_business($id) {
  //check that no user_business matches the business_id
  //if not, delete the business, otherwise give user warning
  global $wpdb;
  $users = $wpdb->get_results("SELECT * FROM dcvs_user_business WHERE business_id='".esc_sql($id)."'");
  if(sizeof($users) != 0) {
    echo "At least one student is using this business, so it cannot be deleted";
  } else {
    $wpdb->delete("dcvs_business", array("id"=>$id));
    echo "Business deleted";
  }
}

function dcvs_get_user_business_money($user_id) {
  global $wpdb;
  $business_id = $wpdb->get_var("SELECT business_id FROM dcvs_user_business WHERE user_id='".esc_sql($user_id)."'");
  $money = $wpdb->get_var("SELECT money FROM dcvs_business WHERE id='".esc_sql($business_id)."'");
  $costs = $wpdb->get_results("SELECT cost FROM dcvs_warehouse_purchase WHERE user_id='".esc_sql($user_id)."'");
  $spent = calculate_spent($costs);
  $moneyLeft = $money - $spent;
  return $moneyLeft;
}

function dcvs_get_user_business_profit($user_id) {
  global $wpdb;
  $business_id = $wpdb->get_var("SELECT business_id FROM dcvs_user_business WHERE user_id='".esc_sql($user_id)."'");
  $costs = $wpdb->get_results("SELECT cost FROM dcvs_warehouse_purchase WHERE user_id='".esc_sql($user_id)."'");
  $spent = calculate_spent($costs);
  $sales = $wpdb->get_results("SELECT cost FROM dcvs_business_purchase WHERE business_id='".esc_sql($business_id)."'");
  $gained = calculate_spent($sales);
  $profit = $gained - $spent;
  return $profit;
}
 ?>
