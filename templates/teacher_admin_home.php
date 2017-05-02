<?php
global $wpdb;
if (!isset($_REQUEST['student_id'])) {
    $users = DCVS_Store_Management::get_active_users();
    reset($users);
    $first_key = key($users);
    header('Location: '.get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$users[$first_key]);
}
display_admin_panel();

function get_store_info($business_id)
{
    global $wpdb;
    $business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $business_id));
    $current_money = $business->money;
    $description = $business->description;
    $display_name = $business->display_name;
}
function dcvs_enqueue_teacher_admin_style()
{
    wp_enqueue_style('adminPanel_css', plugins_url('assets/css/adminPanel.css', dirname(__FILE__)));
}

add_action('wp_enqueue_scripts', 'dcvs_enqueue_teacher_admin_style');
function display_admin_panel()
{
    ?>
  <head>
      <title>Virtual Store Admin Panel</title>
      <link href="<?php echo plugins_url('assets/css/adminPanel.css', dirname(__FILE__));
    ?>" rel="stylesheet" type="text/css">

      <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.js"></script>
      <script src="https://code.jquery.com/jquery-3.1.1.min.js"
        integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
        crossorigin="anonymous"></script>
      <script src="<?php echo plugins_url('js/teacherAdminPanel.js', dirname(__FILE__));
    ?>" rel="stylesheet"></script>

      <link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">
  </head>
  <body>
  <div class="wrapper">

      <div id="backdrop"></div>

      <header class="header">
          <h1>virtual store admin panel</h1>

      </header>

      <main class="admin">

          <nav class="sidebar">
              <ul>
				  <li <?php echo !isset($_REQUEST['section']) ? "class='selected-tab'" : ''?>><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$_REQUEST['student_id'] ?>">STUDENT INFO</a></li>
				  <li <?php echo (isset($_REQUEST['section']) && $_REQUEST['section'] == 'users') ? "class='selected-tab'" : ''?> ><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$_REQUEST['student_id'].'&section=users' ?>">1. USERS</a></li>
				  <li <?php echo (isset($_REQUEST['section']) && $_REQUEST['section'] == 'settings') ? "class='selected-tab'" : ''?>><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$_REQUEST['student_id'].'&section=settings' ?>">2. GENERAL SETTINGS</a></li>
				  <li <?php echo (isset($_REQUEST['section']) && $_REQUEST['section'] == 'categories') ? "class='selected-tab'" : ''?>><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$_REQUEST['student_id'].'&section=categories' ?>">3. CATEGORIES</a></li>
				  <li <?php echo (isset($_REQUEST['section']) && $_REQUEST['section'] == 'business') ? "class='selected-tab'" : ''?>><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$_REQUEST['student_id'].'&section=business' ?>">4. MANAGE BUSINESSES</a></li>
				  <li <?php echo (isset($_REQUEST['section']) && $_REQUEST['section'] == 'manage') ? "class='selected-tab'" : ''?>><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$_REQUEST['student_id'].'&section=manage' ?>">5. MANAGE PERSONAS</a></li>
				  <li <?php echo (isset($_REQUEST['section']) && $_REQUEST['section'] == 'assign') ? "class='selected-tab'" : ''?>><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$_REQUEST['student_id'].'&section=assign' ?>">6. ASSIGN PERSONAS</a></li>
			  </ul>
          </nav>
          <?php
          if (!isset($_REQUEST['section'])) {
              require_once __DIR__.'/teacher_admin_student_info.php';
          } elseif ($_REQUEST['section'] == 'manage') {
              require_once __DIR__.'/teacher_admin_manage_personas.php';
          } elseif ($_REQUEST['section'] == 'settings') {
              require_once __DIR__.'/teacher_admin_general_settings.php';
          } elseif ($_REQUEST['section'] == 'assign') {
              require_once __DIR__.'/teacher_admin_assign_personas.php';
          } elseif ($_REQUEST['section'] == 'order_history') {
              require_once __DIR__.'/teacher_admin_orderHistory.php';
          } elseif ($_REQUEST['section'] == 'categories') {
              require_once __DIR__.'/teacher_admin_categories.php';
          } elseif ($_REQUEST['section'] == 'business') {
              require_once __DIR__.'/teacher_admin_manage_businesses.php';
          } elseif ($_REQUEST['section'] == 'users') {
              require_once __DIR__.'/teacher_admin_users.php';
          } elseif ($_REQUEST['section'] == 'stats') {
              require_once __DIR__.'/teacher_admin_stats.php';
          }

    ?>
      </main>

  </div>

  </body>

  <?php

}

function get_shopping_end_date()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['dcvs_admin_changes'] == 1 && isset($_POST['shopping_end_date'])) {
        $shopping_end_date = $_POST['shopping_end_date'];
        if (!DateTime::createFromFormat('Y-m-d', $shopping_end_date)) {
            echo 'shopping end date must be a date in the format of yyyy-mm-dd';

            return;
        }
        dcvs_set_option('shopping_end_date', $shopping_end_date);
    }
    ?>
  <!-- template -->
  <form action="" method="post">
      <input type="hidden" name="dcvs_admin_changes" value="1">
      <label>Shopping end date</label> <input name="shopping_end_date" type="text" value="<?php dcvs_echo_option('shopping_end_date', 0);
    ?>">
      <input type="submit">
  </form>
  <?php

}

function get_warehouse_end_date()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['dcvs_admin_changes'] == 1 && isset($_POST['warehouse_end_date'])) {
        $warehouse_end_date = $_POST['warehouse_end_date'];
        if (!DateTime::createFromFormat('Y-m-d', $warehouse_end_date)) {
            echo 'warehouse end date must be a date in the format of yyyy-mm-dd';

            return;
        }
        dcvs_set_option('warehouse_end_date', $warehouse_end_date);
    }
    ?>
  <!-- template -->
  <form action="" method="post">
      <input type="hidden" name="dcvs_admin_changes" value="1">
      <label>warehouse end date</label> <input name="warehouse_end_date" type="text" value="<?php dcvs_echo_option('warehouse_end_date', 0);
    ?>">
      <input type="submit">

  </form>
  <?php

}

function dcvs_get_all_categories()
{
    global $wpdb;
    $categories = $wpdb->get_results('SELECT * FROM dcvs_category', ARRAY_A);

    return $categories;
}
