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
    add_submenu_page("dcvs_virtual_store","User Assignments","User Assignments", "create_sites", "dcvs_user_assignments", "dcvs_admim_user_assignments");
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

function dcvs_admim_user_assignments() {
  $users = get_users();
  ?>
  <table>
    <tr>
      <th>Name</th>
      <th>Business</th>
      <th>Persona 1</th>
      <th>Persona 2</th>
    </tr>
  <?php
  for($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    $usermeta = get_user_meta($id);
    $personas = dcvs_get_user_personas($id);
    ?>
    <tr>
      <td><?php echo $usermeta["first_name"][0]." ".$usermeta["last_name"][0]; ?></td>
      <td><?php echo dcvs_get_user_business($id); ?></td>
      <td><?php echo $personas["Persona 1"]; ?></td>
      <td><?php echo $personas["Persona 2"]; ?></td>
    </tr>
    <?php
  }
  ?>
  </table>
  <?php
}

function dcvs_get_user_business($userId) {
  global $wpdb;
  $businessId = $wpdb->get_var("SELECT business_id FROM dcvs_user_business WHERE user_id = ".esc_sql($userId)."");
  $businessName = $wpdb->get_var("SELECT title FROM dcvs_business WHERE id = ".esc_sql($businessId)."");
  return $businessName;
}

function dcvs_get_user_personas($userId) {
  global $wpdb;
  $personaIds = $wpdb->get_results("SELECT persona_id FROM dcvs_user_persona WHERE user_id = ".esc_sql($userId)."");
  $personas = array();
  for($i = 0; $i < sizeof($personaIds); $i++) {
    $personaId = get_object_vars($personaIds[$i])["persona_id"];
    $personaName = $wpdb->get_var("SELECT name FROM dcvs_persona WHERE id = ".esc_sql($personaId)."");
    $personas["Persona ".($i+1)] = $personaName;
  }
  return $personas;
}
