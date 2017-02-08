<?php
add_action("admin_init", "dcvs_admin_init");
add_action("admin_menu", "dcvs_admin_menu_init");

function dcvs_admin_init(){
    //admin intialization
}

function dcvs_admin_menu_init(){
    //The create_sites capability is only for super admins
    // so effectively using that capability means that these menus only show up for super admins
    add_menu_page("Virtual Store", "Virtual Store", "create_sites", "dcvs_virtual_store", "dcvs_admin_menu_draw");
    add_submenu_page("dcvs_virtual_store","Personas","Personas","create_sites","dcvs_personas", "dcvs_admin_personas_settings");
    add_submenu_page("dcvs_virtual_store","Businesses","Businesses","create_sites", "dcvs_businesses", "dcvs_admin_businesses_settings");
}

//This function should be used for the default page when the super admin clicks virutal store
function dcvs_admin_menu_draw(){
    if($_SERVER['REQUEST_METHOD']=="POST" && $_POST['dcvs_admin_changes']==1){
        $defaultPersonaMoney = $_POST['default_persona_money'];
        if(!money_is_number($defaultPersonaMoney)){
            echo('default persona money must be a number');
            return;
        }
        dcvs_set_option("default_persona_money", $defaultPersonaMoney);

    }
    ?>
    <form action="" method="post">
        <input type="hidden" name="dcvs_admin_changes" value="1">
        <label>Default Persona Money</label> <input name="default_persona_money" type="text" value="<?php dcvs_echo_option("default_persona_money",0); ?>">
        <input type="submit">
    </form>
    <?php
}

function dcvs_admin_personas_settings(){
  if($_SERVER['REQUEST_METHOD']=="POST"){
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
      <input class="button-primary" type="submit">
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
          <input class="button-primary" type="submit" value="Update">
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

function dcvs_admin_businesses_settings(){
    if($_SERVER['REQUEST_METHOD']=="POST"){
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
    }
    ?>
    <h3>Add New Business</h3>
    <form action="" method="post">
        <input type="hidden" name="dcvs_add_new_business" value="1">
        <label>Business</label><input name="business_title" type="text" value="Business Title">
        <label></label><input name="business_description" type="text" value="Description">
        <label>$</label><input name="business_money" type="text" value="<?php dcvs_echo_option("default_business_money", 0.00); ?>">
        <label></label><input name="business_url" type="text" value="url">
        <input type="submit">
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
        <form action="" method="post">
            <input type="hidden" name="dcvs_change_business" value="1">
            <input type="hidden" name="business_id" value="<?php echo $businessarray["id"]; ?>">
            <table class="form-table">
              <tbody>
                <tr>
                  <th scope="row">
                    <label for="business_title">Business</label>
                  </th>
                  <td>
                    <input id="business_title" name="business_title" type="text" value="<?php echo $businessarray["title"]; ?>">
                  </td>
                  <label></label><input name="business_description" type="text" value="<?php echo $businessarray["description"]; ?>">
                  <label>$</label><input name="business_money" type="text" value="<?php echo $businessarray["money"]; ?>">
                </tr>
                <tr>
                  <label></label><input name="business_url" type="text" value="<?php echo $businessarray["url"]; ?>">
                </tr>
              </tbody>
            </table>
            <input type="submit" value="Update">
        </form>
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
