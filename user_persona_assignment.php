<?php
function dcvs_admim_persona_assignments() {
  $users = get_users();
  if($_SERVER['REQUEST_METHOD']=="POST") {// && $_POST['dcvs_admin_changes']==1) {
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
    } else {
      $newid = $_POST['personaid'];
      $userid = $_POST['id'];
      $oldid = $_POST['oldid'];
      if ($newid == "-1") {
        dcvs_remove_user_persona($userid,$oldid);
      } else {
        dcvs_set_user_persona($userid, $newid, $oldid);
      }
    }
  }
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
<h2>Student Personas</h2>
<form action="" method="post">
  <input type="hidden" name="dcvs_admin_changes" value="1">
  <input class="button-primary" type="submit" name="assignpersonas" value="Assign Personas">
  <input class="button-secondary" type="submit" name="unsetpersonas" value="Unset All Personas">
</form>
<br>
  <table>
    <tr>
      <th>Name</th>
      <!-- <th>Business</th> -->
      <th>Persona 1</th>
      <th>Persona 2</th>
    </tr>
  <?php
  global $wpdb;
  $allPersonas = $wpdb->get_results("SELECT name, id FROM dcvs_persona");
  for($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    $usermeta = get_user_meta($id);
    $personas = dcvs_get_user_personas($id);
    ?>
    <tr>
      <td><?php echo $usermeta["first_name"][0]." ".$usermeta["last_name"][0]; ?></td>
      <!-- <td><?php echo dcvs_get_user_business($id); ?></td> -->
      <td class = "select">
        <form action="" method="post">
          <input type="hidden" name="dcvs_admin_changes" value="1">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <input type="hidden" name="oldid" value="<?php echo $personas["Persona 1 id"]; ?>">
          <select onchange="this.form.submit()" name="personaid" style="width:100%;">
            <option value="<?php echo $personas["Persona 1 id"]; ?>"><?php echo $personas["Persona 1"]; ?></option>
            <?php
            for($j = 0; $j < sizeof($allPersonas); $j++) {
              $persona = get_object_vars($allPersonas[$j]);
              ?>
              <option value="<?php echo $persona["id"]; ?>"><?php echo $persona["name"]; ?></option>
              <?php
            }
            ?>
            <option value="-1">Unset Persona 1</option>
          </select>
        </form>
      </td>
      <td class = "select">
        <form action="" method="post">
          <input type="hidden" name="dcvs_admin_changes" value="1">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <input type="hidden" name="oldid" value="<?php echo $personas["Persona 2 id"]; ?>">
          <select onchange="this.form.submit()" name="personaid" style="width:100%;">
            <option value="<?php echo $personas["Persona 2 id"]; ?>"><?php echo $personas["Persona 2"]; ?></option>
            <?php
            for($j = 0; $j < sizeof($allPersonas); $j++) {
              $persona = get_object_vars($allPersonas[$j]);
              ?>
              <option value="<?php echo $persona["id"]; ?>"><?php echo $persona["name"]; ?></option>
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

function dcvs_get_user_business($userId) {
  global $wpdb;
  $businessId = $wpdb->get_var("SELECT business_id FROM dcvs_user_business WHERE user_id = ".esc_sql($userId)."");
  $businessName = $wpdb->get_var("SELECT title FROM dcvs_business WHERE id = ".esc_sql($businessId)."");
  return $businessName;
}

function dcvs_get_user_persona_ids($userId) {
  global $wpdb;
  $personaIds = $wpdb->get_results("SELECT persona_id FROM dcvs_user_persona WHERE user_id = ".esc_sql($userId)."");
  return $personaIds;
}

function dcvs_get_user_personas($userId) {
  global $wpdb;
  $personaIds = dcvs_get_user_persona_ids($userId);
  $personas = array();
  for($i = 0; $i < sizeof($personaIds); $i++) {
    $personaId = get_object_vars($personaIds[$i])["persona_id"];
    $personas["Persona ".($i+1)." id"] = $personaId;
    $personaName = $wpdb->get_var("SELECT name FROM dcvs_persona WHERE id = ".esc_sql($personaId)."");
    $personas["Persona ".($i+1)] = $personaName;
  }
  return $personas;
}

function dcvs_assign_persona($userId) {
  global $wpdb;
  $userPersonaIds = dcvs_get_user_persona_ids($userId);

  if (sizeof($userPersonaIds) >= 2) {
    return;
  } else if (sizeof($userPersonaIds) == 1 ) {
    $persona1id = get_object_vars($userPersonaIds[0])["persona_id"];
    $availablePersonaIds = $wpdb->get_results("SELECT id FROM dcvs_persona WHERE id != ".$persona1id."");
    $randId = get_object_vars($availablePersonaIds[array_rand($availablePersonaIds)])["id"];
    $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $randId]);
    return;
  } else {
    // 1st persona
    $allPersonaIds = $wpdb->get_results("SELECT id FROM dcvs_persona");
    $randIndex = array_rand($allPersonaIds);
    $rand = get_object_vars($allPersonaIds[$randIndex]);
    $randId = $rand["id"];
    $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $randId]);

    // 2nd persona
    unset($allPersonaIds[$randIndex]);
    $availablePersonaIds = array_values($allPersonaIds);
    $rand = get_object_vars($availablePersonaIds[array_rand($availablePersonaIds)]);
    $randId = $rand["id"];
    $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $randId]);
    return;
  }
}

function all_personas_assigned() {
  global $wpdb;
  $users = get_users();
  $userPersonas = $wpdb->get_results("SELECT * FROM dcvs_user_persona");
  if (sizeof($userPersonas) >= 2*sizeof($users)) {
    return true;
  } else {
    return false;
  }
}

function num_personas_assigned($userid) {
  global $wpdb;
  $personas = $wpdb->get_results("SELECT id FROM dcvs_user_persona WHERE user_id = ".esc_sql($userid)."");
  return sizeof($personas);
}

function dcvs_set_user_persona($userid, $newid, $oldid){
  global $wpdb;
  $number = num_personas_assigned($userid);
  if ($number == 2) {
    $otherid = $wpdb->get_var("SELECT persona_id FROM dcvs_user_persona WHERE user_id = ".esc_sql($userid)." AND persona_id != ".esc_sql($oldid)."");
    if ($newid != $otherid) {
      $wpdb->update('dcvs_user_persona', array('persona_id'=>$newid), array('user_id'=>$userid,'persona_id'=>$oldid));
    } else {
      echo "User is already using that persona";
    }
  } else if ($number == 1) {
    if ($oldid != NULL) {
      $wpdb->update('dcvs_user_persona', array('persona_id'=>$newid), array('user_id'=>$userid,'persona_id'=>$oldid));
    } else {
      $otherid = $wpdb->get_var("SELECT persona_id FROM dcvs_user_persona WHERE user_id = ".esc_sql($userid)."");
      if ($newid != $otherid) {
        $wpdb->insert('dcvs_user_persona', array('persona_id'=>$newid,'user_id'=>$userid));
      } else {
        echo "User is already using that persona";
      }
    }
  } else if ($number == 0) {
    $wpdb->insert('dcvs_user_persona', array('persona_id'=>$newid,'user_id'=>$userid));
  }
}

function dcvs_reset_all_user_personas($users) {
  global $wpdb;
  for($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    $wpdb->delete('dcvs_user_persona', array('user_id'=>$id));
  }
}

function dcvs_reset_and_assign_user_personas($users) {
  global $wpdb;
  for($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    $wpdb->delete('dcvs_user_persona', array('user_id'=>$id));
    dcvs_assign_persona($id);
  }
}

function dcvs_remove_user_persona($userid, $personaid) {
  global $wpdb;
  $wpdb->delete('dcvs_user_persona', array('user_id'=>$userid,'persona_id'=>$personaid));
}
?>
