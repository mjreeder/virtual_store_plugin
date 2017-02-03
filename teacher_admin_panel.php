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
    if ($_POST['dcvs_add_new_persona'] == 1) {
      dcvs_insert_new_persona($name, $description, $money);
    } else if ($_POST['dcvs_change_persona'] == 1) {
      $id = $_POST['persona_id'];
      dcvs_update_persona($id, $name, $description, $money);
    }
  }
    ?>
    <h3>Add New Persona</h3>
    <form action="" method="post">
        <input type="hidden" name="dcvs_add_new_persona" value="1">
        <label>Persona</label><input name="persona_name" type="text" value="Name">
        <label></label><input name="persona_description" type="text" value="Description">
        <label>$</label><input name="persona_money" type="text" value="<?php dcvs_echo_option("default_persona_money", 0); ?>">
        <input type="submit">
    </form>
    <h3>Update Exsisting Personas</h3>
    <?php

    dcvs_get_all_personas();
}

function dcvs_get_all_personas() {
  global $wpdb;
  $personas = $wpdb->get_results("SELECT * FROM dcvs_persona");

  for ($i = 0; $i < sizeof($personas); $i++) {
    $personaarray = get_object_vars($personas[$i])
    ?>
    <form action="" method="post">
        <input type="hidden" name="dcvs_change_persona" value="1">
        <input type="hidden" name="persona_id" value="<?php echo $personaarray["id"]; ?>">
        <label>Persona</label><input name="persona_name" type="text" value="<?php echo $personaarray["name"]; ?>">
        <label></label><input name="persona_description" type="text" value="<?php echo $personaarray["description"]; ?>">
        <label></label><input name="persona_money" type="text" value="<?php echo $personaarray["money"]; ?>">
        <input type="submit" value="Update">
    </form>
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

function dcvs_admin_businesses_settings(){
    ?>
    INSERT WITTY SAYING
    <?php
}
