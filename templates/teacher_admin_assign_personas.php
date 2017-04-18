<?php
$users = get_users();
if($_SERVER['REQUEST_METHOD']=="POST") {
  if(isset($_POST['assignall'])) {
    dcvs_deal_with_persona_assigning();
    dcvs_deal_with_business_assigning();
  } else if(isset($_POST['assignpersonas'])) {
    dcvs_deal_with_persona_assigning();
 } else if (isset($_POST['assignbusinesses'])) {
  dcvs_deal_with_business_assigning();
 } else if (isset($_POST['personaid'])) {
    $newid = $_POST['personaid'];
    $userid = $_POST['id'];
    $oldid = $_POST['oldid'];
    if ($newid == "-1") {
      dcvs_remove_user_persona($userid,$oldid);
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
    } else {
      dcvs_set_user_persona($userid, $newid, $oldid);
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
  }
}
?>

<section class="assign">
<form action="" method="post">
  <input type="hidden" name="dcvs_admin_changes" value="1">
    <div>
        <h1 class="title">Assign Personas</h1>
        <button class="headerButton randomize" name="assignall" type="submit">RANDOMIZE ALL</button>
        <button class="headerButton randomize" name="assignbusinesses" type="submit">RANDOMIZE BUYERS</button>
        <button class="headerButton randomize" name="assignpersonas" type="submit">RANDOMIZE CONSUMERS</button>
    </div>

    <div class="tableWrapper">
        <table class="virtualTable orderTable">
            <tr>
                <th>STUDENT</th>
                <th>BUYER</th>
                <th>CONSUMER #1</th>
                <th>CONSUMER #2</th>
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
                    <select onchange="this.form.submit()" name="businessid" class="dropdown">
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
                    <select onchange="this.form.submit()" name="personaid" class="dropdown" id="assignOne">
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
                    <select onchange="this.form.submit()" name="personaid" class="dropdown" id="assignTwo">
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
    </div>
</form>
</section>
