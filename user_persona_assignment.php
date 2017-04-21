<?php
// set up page/handle form submit
function dcvs_admim_persona_assignments() {
  $users = get_users();
  if($_SERVER['REQUEST_METHOD']=="POST") {
    if(isset($_POST['assignpersonas'])){
      if (all_personas_assigned()) {
        // echo "All Personas Already Assigned";
        dcvs_reset_and_assign_user_personas($users);
      } else {
        for ($i = 0; $i < sizeof($users); $i++) {
          $user = get_object_vars($users[$i]);
          $id = $user["ID"];
          dcvs_assign_persona($id);
        }
      }
    } else if (isset($_POST['unsetpersonas'])) {
      dcvs_reset_all_user_personas($users);
    } else if (isset($_POST['personaid'])) {
      $newid = $_POST['personaid'];
      $userid = $_POST['id'];
      $oldid = $_POST['oldid'];
      if ($newid == "-1") {
        dcvs_remove_user_persona($userid,$oldid);
      } else {
        dcvs_set_user_persona($userid, $newid, $oldid);
      }
    } else if (isset($_POST['assignbusinesses'])) {
      if(all_businesses_assigned()) {
        dcvs_remove_all_user_businesses();
        dcvs_assign_all_user_businesses();
      } else {
        dcvs_assign_all_user_businesses();
      }
    } else if (isset($_POST['businessid'])) {
      $oldid = $_POST['businessId'];
      $userid = $_POST['id'];
      $newid = $_POST['businessid'];
      if ($newid == -1 && $oldid != NULL) {
        dcvs_remove_user_business($userid);
      } else if ($oldid == $newid) {
        echo "it's the same lol";
      } else if (dcvs_user_id_from_business($newid)) {
        echo "TAKEN";
      } else {
        dcvs_set_user_business($userid, $newid);
      }
    } else if (isset($_POST['unsetbusinesses'])) {
      dcvs_remove_all_user_businesses();
    }
  }

  // Styles
  ?>
  <style>
table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
}
tr:nth-child(even) {
    background-color: #dddddd;
}
</style>

<h2>User Assignments</h2>
<form action="" method="post">
  <input type="hidden" name="dcvs_admin_changes" value="1">
  <input class="button-primary" type="submit" name="assignbusinesses" value="Assign Businesses">
  <input class="button-secondary" type="submit" name="unsetbusinesses" value="Unset All Businesses">
  <br/><br/>
  <input class="button-primary" type="submit" name="assignpersonas" value="Assign Personas">
  <input class="button-secondary" type="submit" name="unsetpersonas" value="Unset All Personas">
</form>
<br>
  <table>
    <tr>
      <th>Name</th>
      <th>Business</th>
      <th>Persona 1</th>
      <th>Persona 2</th>
    </tr>
  <?php
  global $wpdb;
  $allPersonas = $wpdb->get_results("SELECT name, id FROM dcvs_persona");
  $allBusinesses = $wpdb->get_results("SELECT title, id FROM dcvs_business");
  for($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    $usermeta = get_user_meta($id);
    $personas = dcvs_get_user_personas($id);
    $business = dcvs_get_user_business($id);

    $user_business_id = isset($business['id']) ? $business['id'] : "";
    $persona_1_id = isset( $personas['Persona 1 id'] ) ? $personas['Persona 1 id'] : null;
    $persona_2_id = isset( $personas['Persona 2 id'] ) ? $personas['Persona 2 id'] : null;
    ?>
    <tr>
      <td><?php echo $usermeta["first_name"][0]." ".$usermeta["last_name"][0]; ?></td>

      <!-- Business -->
      <td class = "select">
        <form action="" method="post">
          <input type="hidden" name="dcvs_admin_changes" value="1">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <input type="hidden" name="businessId" value="<?php echo $business["id"]; ?>">
          <select onchange="this.form.submit()" name="businessid" style="width:100%;">
            <option value="-1"></option>
            <?php
            for($j = 0; $j < sizeof($allBusinesses); $j++) {
              $business = get_object_vars($allBusinesses[$j]);
              ?>
              <option value="<?php echo $business["id"]; ?>" <?php echo $user_business_id == $business["id"] ? "selected": "";?> ><?php echo $business["title"]; ?></option>
              <?php
            }
            ?>
            <option value="-1">Unset Business</option>
          </select>
        </form>
      </td>

      <!-- Persona 1 -->
      <td class = "select">
        <form action="" method="post">
          <input type="hidden" name="dcvs_admin_changes" value="1">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <input type="hidden" name="oldid" value="<?php echo $persona_1_id; ?>">
          <select onchange="this.form.submit()" name="personaid" style="width:100%;">
            <option value="-1"></option>
            <?php
            for($j = 0; $j < sizeof($allPersonas); $j++) {
              $persona = get_object_vars($allPersonas[$j]);
              ?>
              <option value="<?php echo $persona["id"]; ?>" <?php echo $persona_1_id == $persona["id"] ? "selected": "";?> ><?php echo $persona["name"]; ?></option>
              <?php
            }
            ?>
            <option value="-1">Unset Persona 1</option>
          </select>
        </form>
      </td>

      <!-- Persona 2 -->
      <td class = "select">
        <form action="" method="post">
          <input type="hidden" name="dcvs_admin_changes" value="1">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <input type="hidden" name="oldid" value="<?php echo $persona_2_id; ?>">
          <select onchange="this.form.submit()" name="personaid" style="width:100%;">
            <option value="-1"></option>
            <?php
            for($j = 0; $j < sizeof($allPersonas); $j++) {
              $persona = get_object_vars($allPersonas[$j]);
              ?>
              <option value="<?php echo $persona["id"]; ?>" <?php echo $persona_2_id == $persona["id"] ? "selected": "";?> ><?php echo $persona["name"]; ?></option>
              <?php
            }
            ?>
            <option value="-1">Unset Persona 2</option>
          </select>
        </form>
      </td>

    </tr>
    <?php
  }
  ?>
  </table>
  <?php
}

// dcvs_business
function dcvs_deal_with_business_assigning() {
  if(all_businesses_assigned()) {
    dcvs_remove_all_user_businesses();
    dcvs_assign_all_user_businesses();
  } else {
    dcvs_assign_all_user_businesses();
  }
}

// READ
function dcvs_get_user_business($userId) {
  global $wpdb;
  $businessId = $wpdb->get_var("SELECT business_id FROM dcvs_user_business WHERE user_id = '".esc_sql($userId)."'");
  $businessName = $wpdb->get_var("SELECT title FROM dcvs_business WHERE id = '".esc_sql($businessId)."'");
  return array("title"=>$businessName,"id"=>$businessId);
}

function dcvs_user_id_from_business($business_id) {
  global $wpdb;
  $userid = $wpdb->get_var("SELECT user_id FROM dcvs_user_business WHERE business_id = '".esc_sql($business_id)."'");
  return $userid;
}

function dcvs_business_id_from_user($userid) {
  global $wpdb;
  $businessid = $wpdb->get_var("SELECT business_id FROM dcvs_user_business WHERE user_id = '".esc_sql($userid)."'");
  return $businessid;
}

function all_businesses_assigned() {
  global $wpdb;
  $businessesAssigned = $wpdb->get_results("SELECT business_id FROM dcvs_user_business");
  $users = get_users();
  if(sizeof($businessesAssigned) == sizeof($users)) {
    return true;
  } else {
    return false;
  }
}

// CREATE

function dcvs_assign_all_user_businesses() {
  $users = get_users();
  for ($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    dcvs_asign_user_business($id);
  }
}

function dcvs_set_user_business($userid,$businessid) {
  global $wpdb;
  if(dcvs_business_id_from_user($userid)) {
    $wpdb->update("dcvs_user_business", array('business_id'=>$businessid), array('user_id'=>$userid));
  } else {
    $wpdb->insert("dcvs_user_business", array('user_id'=>$userid,'business_id'=>$businessid));
  }
}

function dcvs_asign_user_business($userid) {
  global $wpdb;
  if(!dcvs_business_id_from_user($userid)) {
    $allBusinesses = $wpdb->get_results("SELECT id FROM dcvs_business");
    $randIndex = array_rand($allBusinesses);
    $rand = get_object_vars($allBusinesses[$randIndex]);
    $randId = $rand["id"];
    while(dcvs_user_id_from_business($randId)) {
      unset($allBusinesses[$randIndex]);
      $allBusinesses = array_values($allBusinesses);
      $randIndex = array_rand($allBusinesses);
      $rand = get_object_vars($allBusinesses[$randIndex]);
      $randId = $rand["id"];
    }
    $wpdb->insert('dcvs_user_business', ['user_id'=>$userid,'business_id'=>$randId]);
  }
}

//DELETE
function dcvs_remove_user_business($userid) {
  global $wpdb;
  $wpdb->delete('dcvs_user_business', array('user_id'=>$userid));
}

function dcvs_remove_all_user_businesses() {
  global $wpdb;
  $users = get_users();
  for($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    $wpdb->delete('dcvs_user_business', array('user_id'=>$id));
  }
}

// dcvs_persona

// READ

function dcvs_get_user_personas($userId) {
  global $wpdb;
  $personaIds = dcvs_get_user_persona_ids($userId);
  $personas = array();
  for($i = 0; $i < sizeof($personaIds); $i++) {
    $personaId = get_object_vars($personaIds[$i])["persona_id"];
    $personas["Persona ".($i+1)." id"] = $personaId;
    $personaName = $wpdb->get_var("SELECT name FROM dcvs_persona WHERE id = '".esc_sql($personaId)."'");
    $personas["Persona ".($i+1)] = $personaName;
  }
  return $personas;
}

function num_personas_assigned($userid) {
  global $wpdb;
  $personas = $wpdb->get_results("SELECT id FROM dcvs_user_persona WHERE user_id = '".esc_sql($userid)."'");
  return sizeof($personas);
}

// CREATE

// DELETE
?>
