<?php
global $wpdb;
if(!isset($_REQUEST['student_id'])){
  $user_results = $wpdb->get_results('SELECT * FROM dcvs_user_business', OBJECT);
  header('Location: '. get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='.$user_results[0]->user_id);
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
function dcvs_enqueue_teacher_admin_style() {

    wp_enqueue_style( 'adminPanel_css', plugins_url( 'assets/css/adminPanel.css', dirname(__FILE__)) );
}

add_action( 'wp_enqueue_scripts', 'dcvs_enqueue_teacher_admin_style' );
function display_admin_panel()
{
    ?>
  <head>
      <title>Virtual Store Admin Panel</title>
      <link href="<?php echo plugins_url( 'assets/css/adminPanel.css', dirname(__FILE__));
    ?>" rel="stylesheet" type="text/css">

      <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.js"></script>
      <script src="https://code.jquery.com/jquery-3.1.1.min.js"
        integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
        crossorigin="anonymous"></script>
      <script src="<?php echo plugins_url( 'js/teacherAdminPanel.js', dirname(__FILE__));
    ?>" rel="stylesheet"></script>

      <link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">
  </head>
  <body>

      <header class="header">
          <h1>virtual store admin panel</h1>

      </header>

      <main class="admin">

          <nav class="sidebar">
              <ul>
                  <li id="selectedTab">STUDENT INFO</li>
                  <a href="../views/merchandiser.html">
                      <li>MERCHANDISER</li>
                  </a>
                  <a href="../views/assign.html">
                      <li>ASSIGN PERSONAS</li>
                  </a>
                  <a href="../views/manage.html">
                      <li>MANAGE PERSONAS</li>
                  </a>
                  <a href="../views/settings.html">
                      <li>GENERAL SETTINGS</li>
                  </a>
              </ul>
          </nav>
          <aside class="studentList">
              <div class="searchBar">
                  <img src="<?php echo plugins_url( 'assets/images/search.svg', dirname(__FILE__));
    ?>" rel="stylesheet" alt="">
                  <input type="text" id='search' placeholder="search" oninput="studentSearch()">
              </div>
              <ul id="students">
              <?php
                display_student_list();
    ?>          </ul>
          </aside>
          <?php
            display_current_student_info();
    ?>
      </main>

  </body>

  <?php

}
function display_student_list()
{
    global $wpdb;
    $user_results = $wpdb->get_results('SELECT * FROM dcvs_user_business', OBJECT);
    ?>
    <?php
    for ($i = 0; $i < sizeof($user_results); ++$i) {
        $display_name = $wpdb->get_results($wpdb->prepare('SELECT * FROM wp_users WHERE id = %d', $user_results[$i]->user_id));
        $business = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business WHERE id = %d', $user_results[$i]->business_id));
        $personas = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.id WHERE user_id = %d', $user_results[$i]->user_id));
        ?>

      <li id="<?php echo $user_results[$i]->user_id;
        ?>" name="student_name" type="text"><a href="<?php echo $_SERVER['REQUEST_URI'].'&student_id='.$user_results[$i]->user_id;
        ?>"><?php echo $display_name[0]->display_name;
        ?></a></li>

      <?php

    }
}

function display_current_student_info()
{
    global $wpdb;
    $currentDisplayStudent = $_REQUEST['student_id'];
    $display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudent));
    $business_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business LEFT JOIN dcvs_user_business ON dcvs_business.id=dcvs_user_business.business_id WHERE user_id = %d', $currentDisplayStudent));
    $persona_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $currentDisplayStudent));
    $number_of_shoppers = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT business_id) FROM dcvs_business_purchase WHERE business_id = %d', $business_info[0]->id));
    $persona_one_purchase_count = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT user_persona_id) FROM dcvs_business_purchase WHERE user_persona_id = %d', $persona_info[0]->id));
    $persona_two_purchase_count = $wpdb->get_results($wpdb->prepare('SELECT COUNT(DISTINCT user_persona_id) FROM dcvs_business_purchase WHERE user_persona_id = %d', $persona_info[1]->id));
    ?>

  <section class="studentInfo">
      <h1><?php echo $display_name[0]->display_name ?></h1>
      <section class="merchandiserInfo">
          <h2 class="subTitle">buyer</h2>
          <h3><?php echo $business_info[0]->title ?></h3>
          <section>
              <aside class="merchandiserLeft">
                  <a href="<?php echo $business_info[0]->url ?>" class="button">Personal Site</a>
                  <button class="button">FINAL SURVEY</button>
                  <!-- TODO get remaining budget-->
                  <span><b>BUDGET REMAINING:</b> $12,000</span>
              </aside>
              <aside class="merchandiserRight">
                  <section class="facts">
                      <div class="fact">
                          <img src=<?php echo plugins_url( "assets/images/dollarSign.svg", dirname(__FILE__));
                        ?> alt="">
                          <!-- TODO get profit-->
                          <p>$450 <br>PROFIT</p>
                      </div>
                      <div class="fact">
                          <img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
                        ?>  alt="">
                          <p><?php echo count(get_object_vars($number_of_shoppers[0])) ?> <br>SHOPPERS</p>
                      </div>
                  </section>
                  <!-- TODO comparison page -->
                  <a href="../views/comparison.html"><button class="button">STATISTICS</button></a>
              </aside>
          </section>
      </section>
      <section class="shopperInfo">

          <aside class="shopperOne">
              <h2 class="subTitle">consumer #1</h2>
              <h3><?php echo $persona_info[0]->name ?></h3>
              <?php
              if ($_GET) {
                  if (isset($_POST['insert_one'])) {
                      get_user_persona_order_history($currentDisplayStudent, $persona_info[0]->id);
                  }
              }
    ?>
              <form action="" method="post">
                <button class="button buttonSmall" name="insert_one">ORDER HISTORY</button>
              </form>
              <button class="button one">FINAL SURVEY</button>
              <section class="facts">
                <div class="fact">
                    <img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
                  ?> alt="">
                    <p>$450 <br>PROFIT</p>
                </div>
                <div class="fact">
                    <img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
                  ?> alt="">
                    <p>52 <br>SHOPPERS</p>
                </div>
              </section>
          </aside>

          <aside class="shopperTwo">
              <h2 class="subTitle">consumer #2</h2>
              <h3><?php echo $persona_info[1]->name ?></h3>
              <?php
              if ($_GET) {
                  if (isset($_POST['insert_two'])) {
                      get_user_persona_order_history($currentDisplayStudent, $persona_info[1]->id);
                  }
              }
        ?>
              <form action="" method="post">

                <button class="button buttonSmall" name="insert_two">ORDER HISTORY</button>
              </form>

              <button class="button two">FINAL SURVEY</button>
              <section class="facts">
                <div class="fact">
                    <img src=<?php echo plugins_url("assets/images/dollarSign.svg", dirname(__FILE__));
                  ?> alt="">
                    <!-- TODO get profit -->
                    <p>$450 <br>PROFIT</p>
                </div>
                <div class="fact">
                    <img src=<?php echo plugins_url("assets/images/shoppingBag.svg", dirname(__FILE__));
                  ?> alt="">
                    <!-- TODO get numb of shopperes -->
                    <p>52 <br>SHOPPERS</p>
                </div>
              </section>

          </aside>

      </section>

  </section>
  <?php

}

function get_user_persona_order_history($user_id, $persona_id)
{
    global $wpdb;
    $user_persona_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_business_purchase LEFT JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND user_persona_id = %d', $user_id, $persona_id));
    if (sizeOf($user_persona_order_history) > 1) {
        for ($i = 0; $i < sizeOf($user_persona_order_history); ++$i) {
            var_dump($user_persona_order_history[$i]->cost, $user_persona_order_history[$i]->items);
        }
    } else {
        var_dump('user has not ordered anything');
    }
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
