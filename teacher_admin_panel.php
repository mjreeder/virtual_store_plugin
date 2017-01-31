<?php
add_action('admin_init', 'dcvs_admin_init');
add_action('admin_menu', 'dcvs_admin_menu_init');

function dcvs_admin_init()
{
    //admin intialization
}

function dcvs_admin_menu_init()
{
    //The create_sites capability is only for super admins
    // so effectively using that capability means that these menus only show up for super admins
    add_menu_page('Virtual Store', 'Virtual Store', 'create_sites', 'dcvs_virtual_store', 'dcvs_admin_menu_draw');
    add_submenu_page('dcvs_virtual_store', 'Personas', 'Personas', 'create_sites', 'dcvs_personas', 'dcvs_admin_personas_settings');
    add_submenu_page('dcvs_virtual_store', 'Businesses', 'Businesses', 'create_sites', 'dcvs_businesses', 'dcvs_admin_businesses_settings');
    add_submenu_page('dcvs_virtual_store', 'Businesses', 'Teacher', 'create_sites', 'dcvs_teacher', 'dvcs_admin_teacher_settings');
}

//This function should be used for the default page when the super admin clicks virutal store
function dcvs_admin_menu_draw()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['dcvs_admin_changes'] == 1) {
        $defaultPersonaMoney = $_POST['default_persona_money'];
        if (!filter_var($defaultPersonaMoney, FILTER_VALIDATE_FLOAT)) {
            echo 'default persona money must be a number';

            return;
        }
        dcvs_set_option('default_persona_money', $defaultPersonaMoney);
    }
    ?>
    <form action="" method="post">
        <input type="hidden" name="dcvs_admin_changes" value="1">
        <label>Default Persona Money</label> <input name="default_persona_money" type="text" value="<?php dcvs_echo_option('default_persona_money', 0);
    ?>">
        <input type="submit">

    </form>
    <?php

}

function dcvs_admin_personas_settings()
{
    ?>
    yo dawg
    <?php

}

function dcvs_admin_businesses_settings()
{
    ?>
    INSERT WITTY SAYING
    <?php

}

function dvcs_admin_teacher_settings()
{
    ?>
    <h1>Teacher Admin</h1>
    <?php
    global $wpdb;
    $results = $wpdb->get_results('SELECT * FROM dcvs_user_business', OBJECT);
    for ($i = 0; $i < sizeof($results); ++$i) {
        $display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $results[$i]->user_id));
        $business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $results[$i]->business_id));
        $personas = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.id WHERE user_id = %d', $results[$i]->user_id));
        ?>
      <div>
        <label name="student_name" type="text"><?php echo $display_name[0]->display_name;
        ?></label>
          <div><?php echo $business[0]->title;
        ?></div>
        <div><?php echo $personas[0]->name;
        ?></div>
      <div><?php echo $personas[1]->name;
        ?></div>
      </div>
        <?php

    }
}
