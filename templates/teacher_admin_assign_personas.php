<?php

$users_unformatted = DCVS_Store_Management::get_active_users();
$users = [];
foreach ($users_unformatted as $user) {
    $users[] = $user;
}

$toast = null;
$date_now = date("Y-m-d H:i:s");
$shopping_start_date = date_create(dcvs_get_option('shopping_start_date', 0));
$shopping_start_date = $shopping_start_date->format("Y-m-d H:i:s");
$shopping_has_started = $date_now >= $shopping_start_date;

if ($_SERVER['REQUEST_METHOD']=="POST") {
    if (isset($_POST['assign_personas'])) {
        dcvs_deal_with_persona_assigning( $users );
        $toast = DCVS_Toast::create_new_toast( 'All Personas Assigned!' );
    } else if (isset($_POST['persona_id'])) {
        $user_id = $_POST['id'];
        $new_persona_id = $_POST['persona_id'];
        $old_persona_id = $_POST['old_persona_id'];


        $new_persona_category = dcvs_get_persona_category($new_persona_id);
        $new_persona_category_id = isset($new_persona_category[0]['category_id']) ? $new_persona_category[0]['category_id'] : -1;

        $other_persona = dcvs_get_second_persona($old_persona_id, $user_id);
        $other_persona_id = isset($other_persona[0]['persona_id']) ? $other_persona[0]['persona_id'] : -1;

        $business_category = dcvs_get_user_business_category( $user_id );
        $business_category_id = isset($business_category[0]['id']) ? $business_category[0]['id'] : -1;

        if ($new_persona_id == $other_persona_id && $new_persona_id != -1 && $other_persona_id != -1) {
            $toast = $toast = DCVS_Toast::create_new_toast( 'Assigned personas cannot be the same!', true );
        } else if (dcvs_check_personas_same_category($new_persona_id, $other_persona_id)) {
            $toast = $toast = DCVS_Toast::create_new_toast( 'Assigned personas cannot have the same category!', true );
        } else if ($business_category_id == $new_persona_category_id && $business_category_id != -1 && $new_persona_category_id != -1) {
            $toast = $toast = DCVS_Toast::create_new_toast( 'Assigned persona cannot have the same category as student business!', true );
        } else if ($new_persona_id == -1) {
            dcvs_remove_user_persona($user_id,$old_persona_id);
            $toast = DCVS_Toast::create_new_toast( 'Persona Unassigned!' );
        } else if ($old_persona_id != -1) {
            dcvs_update_user_persona($user_id, $old_persona_id, $new_persona_id);
            dcvs_set_user_persona($user_id, $new_persona_id);
            $toast = DCVS_Toast::create_new_toast( 'Persona Assigned!' );
        } else {
            dcvs_set_user_persona($user_id, $new_persona_id);
            $toast = DCVS_Toast::create_new_toast( 'Persona Assigned!' );
        }

    }
}
?>

<section class="assign">
<form action="" method="post">
  <input type="hidden" name="dcvs_admin_changes" value="1">
    <div>
        <h1 class="title">Assign Consumers</h1>
        <?php if(!$shopping_has_started): ?>
            <button class="headerButton randomize" name="assign_personas" type="submit" <?php echo dcvs_enough_distinct_persona_categories() ? "": "disabled"?>  onclick="return confirm('Randomize All Consumers?');">RANDOMIZE CONSUMERS</button>
        <?php endif; ?>
    </div>

    <div class="tableWrapper">
        <table class="virtualTable orderTable">
            <tr>
                <th>STUDENT</th>
                <th>BUYER CATEGORY</th>
                <th>CONSUMER #1</th>
                <th>CONSUMER #2</th>
            </tr>
            <?php
            global $wpdb;
            $allPersonas = $wpdb->get_results("SELECT dcvs_persona.*,dcvs_persona_category.category_id,  dcvs_category.name as category_name FROM dcvs_persona LEFT JOIN dcvs_persona_category ON dcvs_persona_category.persona_id = dcvs_persona.id LEFT JOIN dcvs_category ON dcvs_persona_category.category_id = dcvs_category.id ORDER BY dcvs_persona.name", ARRAY_A);
            $persona_split_by_category = array();
            foreach($allPersonas as $val){
                $persona_split_by_category[$val['category_name']][] = (array)$val;
            }
            uksort( $persona_split_by_category, 'strcasecmp' );
            $persona_categories = array_keys( $persona_split_by_category );
            for($i = 0; $i < count($users); $i++) {
              $id = $users[$i];
              $usermeta = get_user_meta($id);
              $personas = dcvs_get_user_personas($id);
              $business_category = dcvs_get_user_business_category($id);

                $student_info = get_userdata($id);
                $student_meta_info = get_user_meta( $id );
                $student_display_name = ($student_meta_info['first_name'][0] != "" && $student_meta_info['last_name'][0] != "") ? $student_meta_info['first_name'][0] . ' ' . $student_meta_info['last_name'][0] : $student_info->user_email;

                if (count($business_category) > 0) {
                    $business_category_name = $business_category[0]['name'];
                    $business_category_id = $business_category[0]['id'];
                } else {
                    $business_category_name = '<i>NOT SET</i>';
                    $business_category_id = -1;
                }

              $persona_1_id = isset( $personas['Persona 1 id'] ) ? $personas['Persona 1 id'] : -1;
              $persona_2_id = isset( $personas['Persona 2 id'] ) ? $personas['Persona 2 id'] : -1;
              ?>
              <tr>
                <td><?php echo stripslashes_deep($student_display_name); ?></td>

                <!-- Business -->
                <td>
                  <?php echo stripslashes_deep($business_category_name); ?>
                </td>

                <!-- Persona 1 -->
                <td class = "select">
                  <form action="" method="post">
                    <input type="hidden" name="dcvs_admin_changes" value="1">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="old_persona_id" value="<?php echo $persona_1_id; ?>">
                    <select onchange="this.form.submit()" name="persona_id" class="dropdown" id="assignOne">
                        <option value="-1" disabled <?php echo $persona_1_id == -1 ? "selected" : ""  ?>>- Select A Consumer -</option>
                        <?php
                        for($j = 0; $j < count($persona_categories); $j++) {
                            ?>
                            <optgroup label="<?php echo ($persona_categories[$j] != '') ?  $persona_categories[$j] : 'NO CATEGORY'?>">
                            <?php
                            for($z = 0; $z < count($persona_split_by_category[$persona_categories[$j]]); $z++) {
                                $persona = $persona_split_by_category[$persona_categories[$j]][$z];
                                ?>
                                <option value="<?php echo $persona["id"]; ?>" <?php echo $persona_1_id == $persona["id"] ? "selected": "";?> ><?php echo stripslashes_deep($persona["name"]); ?></option>
                                <?php
                            }
                            ?>
                            </optgroup>
                            <?php
                        }
                        ?>
                      <option value="" disabled>──────────</option>
                      <option value="-1">Unset Consumer 1</option>
                    </select>
                  </form>
                </td>

                <!-- Persona 2 -->
                <td class = "select">
                  <form action="" method="post">
                    <input type="hidden" name="dcvs_admin_changes" value="1">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="old_persona_id" value="<?php echo $persona_2_id; ?>">
                    <select onchange="this.form.submit()" name="persona_id" class="dropdown" id="assignTwo">
                        <option value="-1" disabled <?php echo $persona_2_id == -1 ? "selected" : ""  ?>>- Select A Consumer -</option>
                        <?php
                        for($j = 0; $j < count($persona_categories); $j++) {
                            ?>
                            <optgroup label="<?php echo ($persona_categories[$j] != '') ?  $persona_categories[$j] : 'NO CATEGORY'?>">
                                <?php
                                for($z = 0; $z < count($persona_split_by_category[$persona_categories[$j]]); $z++) {
                                    $persona = $persona_split_by_category[$persona_categories[$j]][$z];
                                    ?>
                                    <option value="<?php echo $persona["id"]; ?>" <?php echo $persona_2_id == $persona["id"] ? "selected": "";?> ><?php echo stripslashes_deep($persona["name"]); ?></option>
                                    <?php
                                }
                                ?>
                            </optgroup>
                            <?php
                        }
                        ?>
                        <option value="-2" disabled>──────────</option>
                        <option value="-1">Unset Consumer 2</option>
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

<?php

if ($toast != null) {
    echo $toast;

    ?>

    <script src="<?php echo plugins_url( 'js/toast.js', dirname(__FILE__)); ?>"></script>

    <?php
}

function dcvs_deal_with_persona_assigning( $users ) {
    if (dcvs_all_personas_assigned( $users )) {
        dcvs_reset_and_assign_user_personas($users);
    } else {
        for ($i = 0; $i < count($users); $i++) {
            dcvs_assign_persona($users[$i]);
        }
    }
}

function dcvs_all_personas_assigned( $users ) {
    global $wpdb;
    $user_personas = $wpdb->get_results("SELECT * FROM dcvs_user_persona");
    if (sizeof($user_personas) >= 2*count($users)) {
        return true;
    } else {
        return false;
    }
}

function dcvs_reset_and_assign_user_personas($users) {
    global $wpdb;
    for($i = 0; $i < count($users); $i++) {
        $wpdb->delete('dcvs_user_persona', array('user_id'=>$users[$i]));
        dcvs_assign_persona($users[$i]);
    }
}

function dcvs_assign_persona($userId) {
    global $wpdb;
    $user_persona_ids = dcvs_get_user_persona_ids($userId);

    if (sizeof($user_persona_ids) >= 2) {
        return;
    } else if (sizeof($user_persona_ids) == 1 ) {
        $persona_one_id = get_object_vars($user_persona_ids[0])["persona_id"];
        $user_business_category_response = dcvs_get_user_business_category( $userId );
        if (count( $user_business_category_response ) != 0) {
            $user_business_category_id = $user_business_category_response[0]['id'];
        } else {
            $user_business_category_id = -1;
        }
        $sql = $wpdb->prepare("SELECT id FROM dcvs_persona WHERE id != '%d' AND id NOT IN (SELECT persona_id FROM dcvs_persona_category WHERE category_id IN (SELECT category_id FROM dcvs_persona_category WHERE persona_id = '%d') AND category_id != '%d')", [$persona_one_id, $persona_one_id, $user_business_category_id]);
        $available_persona_ids = $wpdb->get_results($sql, ARRAY_A);
        $random_id = $available_persona_ids[array_rand($available_persona_ids)]["id"];
        $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $random_id]);
        return;
    } else {
        $user_business_category_response = dcvs_get_user_business_category( $userId );
        if (count( $user_business_category_response ) != 0) {
            $user_business_category_id = $user_business_category_response[0]['id'];
        } else {
            $user_business_category_id = -1;
        }
        // 1st persona
        $sql = $wpdb->prepare("SELECT id FROM dcvs_persona WHERE (id IN (SELECT persona_id FROM dcvs_persona_category WHERE category_id != '%d') OR id IN (SELECT id FROM dcvs_persona WHERE id NOT IN (SELECT persona_id FROM dcvs_persona_category)))", [$user_business_category_id]);
        $available_persona_ids = $wpdb->get_results($sql, ARRAY_A);
        $random_index = array_rand($available_persona_ids);
        $random_persona = $available_persona_ids[$random_index];
        $random_id = $random_persona["id"];
        $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $random_id]);

        // 2nd persona
        $sql = $wpdb->prepare("SELECT id FROM dcvs_persona WHERE id != '%d' AND (id IN (SELECT persona_id FROM dcvs_persona_category WHERE category_id NOT IN (SELECT category_id FROM dcvs_persona_category WHERE persona_id = '%d') AND category_id != '%d') OR id IN (SELECT id FROM dcvs_persona WHERE id NOT IN (SELECT persona_id FROM dcvs_persona_category)))", [$random_id, $random_id, $user_business_category_id]);
        $available_persona_ids = $wpdb->get_results($sql, ARRAY_A);
        $random_persona = $available_persona_ids[array_rand($available_persona_ids)];
        $random_id = $random_persona["id"];
        $wpdb->insert("dcvs_user_persona", ["user_id" => $userId, "persona_id" => $random_id]);

        return;
    }
}

function dcvs_remove_user_persona($user_id, $persona_id) {
    global $wpdb;
    $wpdb->delete('dcvs_user_persona', array('user_id'=>$user_id,'persona_id'=>$persona_id));
}

function dcvs_set_user_persona($user_id, $new_persona_id){
    global $wpdb;
    $wpdb->insert('dcvs_user_persona', array('persona_id'=>$new_persona_id,'user_id'=>$user_id));
}

function dcvs_update_user_persona($user_id, $old_persona_id, $new_persona_id){
    global $wpdb;
    $wpdb->update('dcvs_user_persona', array('persona_id'=>$new_persona_id), array('user_id'=>$user_id,'persona_id'=>$old_persona_id));
}

function dcvs_enough_distinct_persona_categories() {
    global $wpdb;
    $sql = "SELECT DISTINCT category_id FROM dcvs_persona_category";
    $response_one = $wpdb->get_results($sql, ARRAY_A);
    $sql = "SELECT * FROM dcvs_persona WHERE id NOT IN (SELECT persona_id FROM dcvs_persona_category)";
    $response_two = $wpdb->get_results($sql, ARRAY_A);
    if ((count($response_one) + count($response_two)) < 3) {
        return false;
    } else {
        return true;
    }
}

function dcvs_check_personas_same_category($persona_id_one, $persona_id_two) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM dcvs_persona_category WHERE persona_id = '%d' OR persona_id = '%d'", [$persona_id_one, $persona_id_two]);
    $response = $wpdb->get_results($sql, ARRAY_A);
    if (count($response) < 2) {
        return false;
    } else {
        $persona_one_category_id = $response[0]['category_id'];
        $persona_two_category_id = $response[1]['category_id'];
        if ($persona_one_category_id == $persona_two_category_id) {
            return true;
        } else {
            return false;
        }
    }
}

function dcvs_get_second_persona($persona_id_one, $user_id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM dcvs_user_persona WHERE user_id = '%d' AND persona_id != '%d'", [$user_id, $persona_id_one]);
    $response = $wpdb->get_results($sql, ARRAY_A);
    return $response;
}
