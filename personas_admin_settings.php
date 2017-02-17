<?php
function dcvs_admin_personas_settings(){
  if($_SERVER['REQUEST_METHOD']=="POST") {
    if (isset($_POST['submit'])) {
      $name = $_POST['persona_name'];
      $description = $_POST['persona_description'];
      $money = $_POST['persona_money'];
      if(!fields_are_blank(array($name, $description, $money))){
        if ($_POST['dcvs_add_new_persona'] == 1) {
          dcvs_insert_new_persona($name, $description, $money);
        } else if ($_POST['dcvs_change_persona'] == 1) {
          $id = $_POST['persona_id'];
          dcvs_update_persona($id, $name, $description, $money);
        }
      } else {
        echo "Empty field";
      }
    } if (isset($_POST['delete'])) {
      $id = $_POST['persona_id'];
      dcvs_delete_persona($id);
    }
  }
    ?>
    <h3>Add New Persona</h3>
    <form action="" method="post">
      <table class="form-table">
        <tbody>
          <input type="hidden" name="dcvs_add_new_persona" value="1">
          <tr>
            <th scope="row">
              <label for="persona_name">Name</label>
            </th>
            <td>
              <input id="persona_name" name="persona_name" type="text" value="">
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="persona_description">Description</label>
            </th>
            <td>
              <textarea id="persona_description" name="persona_description" type="textarea" rows="5" cols="50"></textarea>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="persona_money">Money</label>
            </th>
            <td><input id="persona_money" name="persona_money" type="text" value="<?php dcvs_echo_option("default_persona_money", 0); ?>">
            </td>
          </tr>
        </tbody>
      </table>
      <input class="button-primary" type="submit" name="submit">
      <input type="reset">
    </form>
    <h3>Update Existing Personas</h3>
    <?php

    dcvs_get_all_personas();
}

function dcvs_get_all_personas() {
  global $wpdb;
  $personas = $wpdb->get_results("SELECT * FROM dcvs_persona");

  for ($i = 0; $i < sizeof($personas); $i++) {
    $personaarray = get_object_vars($personas[$i])
    ?>
    <div class="postbox" id="boxid">
      <div class="inside">
        <h3><span>Persona <?php echo $i+1 ?></span></h3>
        <form action="" method="post">
          <table class="form-table">
            <tbody>
              <input type="hidden" name="dcvs_change_persona" value="1">
              <input type="hidden" name="persona_id" value="<?php echo $personaarray["id"]; ?>">
              <tr>
                <th scope="row">
                  <label for="persona_name">Name</label>
                </th>
                <td>
                  <input id="persona_name" name="persona_name" type="text" value="<?php echo $personaarray["name"]; ?>">
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label for="persona_description">Description</label>
                </th>
                <td>
                  <textarea id="persona_description" name="persona_description" type="text" rows="4" cols="50"><?php echo $personaarray["description"]; ?></textarea>
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label for="persona_money">Money</label>
                </th>
                <td>
                  <input id="persona_money" name="persona_money" type="text" value="<?php echo $personaarray["money"]; ?>">
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

function dcvs_get_persona($name, $default_value=false){
    global $wpdb;

    $result = $wpdb->get_var("SELECT id FROM dcvs_persona WHERE name='".esc_sql($name)."'");
    return $result == NULL ? $default_value : $result;
}

function dcvs_insert_new_persona($name, $description, $money) {
  global $wpdb;
  $id = dcvs_get_persona($name);
  if ($id != NULL) {
    echo "That name is already taken";
  } else if (!money_is_number($money)) {
    echo "Money must be a number";
  } else {
    $wpdb->insert("dcvs_persona", ["name"=>$name, "description"=>$description, "money"=>$money] );
  }
}

function dcvs_update_persona($id, $name, $description, $money) {
  global $wpdb;
  // check if name is taken BY A DIFFERENT ID
  // check if money is a floatval
  $checkid = dcvs_get_persona($name);
  if ($checkid != NULL && $checkid != $id) {
    echo "You've entered a name that's already taken";
  } else if (!money_is_number($money)) {
    echo "Money must be a number";
  } else {
    $wpdb->update("dcvs_persona", array("name"=>$name, "description"=>$description,"money"
=>$money), array("id"=>$id));
  }
}

function dcvs_delete_persona($id) {
  //check that no user_persona matches the persona_id
  //if not, delete the persona, otherwise give user warning
  global $wpdb;
  $users = $wpdb->get_results("SELECT * FROM dcvs_user_persona WHERE persona_id='".esc_sql($id)."'");
  if(sizeof($users) != 0) {
    echo "At least one student is using this persona, so it cannot be deleted";
  } else {
    $wpdb->delete("dcvs_persona", array("id"=>$id));
    echo "Persona deleted";
  }
}

function dcvs_get_user_persona_money($user_persona_id) {
  global $wpdb;
  $persona_id = $wpdb->get_var("SELECT persona_id FROM dcvs_user_persona WHERE id='".esc_sql($user_persona_id)."'");
  $persona_money = $wpdb->get_var("SELECT money FROM dcvs_persona WHERE id='".esc_sql($persona_id)."'");
  $costs = $wpdb->get_results("SELECT cost FROM dcvs_business_purchase WHERE user_persona_id='".esc_sql($user_persona_id)."'");
  $spent = calculate_spent($costs);
  $moneyLeft = $persona_money - $spent;
  return $moneyLeft;
}

function dcvs_user_persona_can_purchase($user_persona_id, $purchase_amount){
    $money = dcvs_get_user_persona_money($user_persona_id);
    if($money >= $purchase_amount){
        return true;
    }
    return false;
}

 ?>
