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
    add_submenu_page('dcvs_virtual_store', 'Businesses', 'Teacher', 'create_sites', 'dcvs_teacher', 'dvcs_admin_teacher_settings');
    add_submenu_page("dcvs_virtual_store","User Assignments","User Assignments", "create_sites", "dcvs_persona_assignments", "dcvs_admim_persona_assignments");
}

//This function should be used for the default page when the super admin clicks virtual store
function dcvs_admin_menu_draw(){
    if($_SERVER['REQUEST_METHOD']=="POST" && $_POST['dcvs_admin_changes']==1){
        $defaultPersonaMoney = $_POST['default_persona_money'];
        if(!money_is_number($defaultPersonaMoney)){
            echo('default persona money must be a number');
            return;
        }
        dcvs_set_option("default_persona_money", $defaultPersonaMoney);
    }
    if($_SERVER['REQUEST_METHOD']=="POST" && $_POST['dcvs_admin_changes']==2) {
        $defaultBusinessMoney = $_POST['default_business_money'];
        if (!money_is_number($defaultBusinessMoney)) {
            echo('default business money must be a number');
            return;
        }
        dcvs_set_option("default_business_money", $defaultBusinessMoney);
    }
    ?>
    <form action="" method="post">
        <input type="hidden" name="dcvs_admin_changes" value="1">
        <label>Default Persona Money</label> <input name="default_persona_money" type="text" value="<?php dcvs_echo_option("default_persona_money",0); ?>">
        <input type="submit">
    </form>
    <form action="" method="post">
        <input type="hidden" name="dcvs_admin_changes" value="2">
        <label>Default Business Money</label> <input name="default_business_money" type="text" value="<?php dcvs_echo_option("default_business_money",0); ?>">
        <input type="submit">
    </form>
    <?php
}

function dvcs_admin_teacher_settings()
{
    require_once __DIR__."/templates/teacher_admin_home.php";
}
