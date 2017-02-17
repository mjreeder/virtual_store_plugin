<?php
function dcvs_admim_persona_assignments() {
  $users = get_users();
  if($_SERVER['REQUEST_METHOD']=="POST" && $_POST['dcvs_admin_changes']==1) {
    if (all_personas_assigned()) {
      echo "All Personas Already Assigned";
    } else {
      for ($i = 0; $i < sizeof($users); $i++) {
        $user = get_object_vars($users[$i]);
        $id = $user["ID"];
        dcvs_assign_persona($id);
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
  <input class="button-primary" type="submit" value="Assign Personas">
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
  for($i = 0; $i < sizeof($users); $i++) {
    $user = get_object_vars($users[$i]);
    $id = $user["ID"];
    $usermeta = get_user_meta($id);
    $personas = dcvs_get_user_personas($id);
    ?>
    <tr>
      <td><?php echo $usermeta["first_name"][0]." ".$usermeta["last_name"][0]; ?></td>
      <!-- <td><?php echo dcvs_get_user_business($id); ?></td> -->
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
    $allPersonaIds = $wpdb->get_results("SELECT id FROM dcvs_persona");
    $persona1 = get_object_vars($userPersonaIds[0])["persona_id"];
    if(($key = array_search((object)array("id"=>$persona1), $allPersonaIds)) !== false) {
      unset($allPersonaIds[$key]);
    }
    $randId = get_object_vars($allPersonaIds[array_rand($allPersonaIds)])["id"];
    $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $randId]);
    return;
  } else {
    $allPersonaIds = $wpdb->get_results("SELECT id FROM dcvs_persona");
    $rand = get_object_vars($allPersonaIds[array_rand($allPersonaIds)]);
    $randId = $rand["id"];
    $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $randId]);
    if(($key = array_search((object)array("id"=>$rand), $allPersonaIds)) !== false) {
      unset($allPersonaIds[$key]);
    }
    $rand = get_object_vars($allPersonaIds[array_rand($allPersonaIds)]);
    $randId = $rand["id"];
    $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $randId]);
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
?>
