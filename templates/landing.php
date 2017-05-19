<?php
/**
 * Created by PhpStorm.
 * User: bryonoconner
 * Date: 1/22/17
 * Time: 10:57 PM.
 */
require_once __DIR__.'/../../../../wp-blog-header.php';
date_default_timezone_set('UTC');

global $current_user;


if( isset( $current_user ) && !empty($current_user->roles) ){
    if(!in_array('administrator', $current_user->roles)) {
        wp_redirect( get_site_url() . '/wp-admin' );
        exit;
    }
} else {
    wp_redirect( get_site_url() . '/wp-admin' );
    exit;
}


$current_user_ID = wp_get_current_user()->ID;
$business_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business LEFT JOIN dcvs_user_business ON dcvs_business.id=dcvs_user_business.business_id WHERE user_id = %d', $current_user_ID));
$business_expense = dcvs_get_business_expenses( $current_user_ID );
$consumer_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $current_user_ID));
$business_category = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_category WHERE id = (SELECT category_id FROM dcvs_business_category WHERE business_id = %d)', $business_info[0]->id));
if(isset($consumer_info[0])){
  $consumer_1_expense = dcvs_get_persona_expenses($current_user_ID, $consumer_info[0]->persona_id);
}
else{
  $consumer_1_expense = 0;
}
if(isset($consumer_info[1])){
  $consumer_2_expense = dcvs_get_persona_expenses($current_user_ID, $consumer_info[1]->persona_id);
}
else{
  $consumer_2_expense = 0;
}

$var = dcvs_get_option('warehouse_end_date', 0);
?>

<!doctype HTML>
<html>

<head>
    <title>Virtual Store</title>
    <!-- CSS -->
    <link href="../assets/css/dashboard.css" rel="stylesheet" type="text/css">
    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
	<script type="text/javascript">
		var videos = <?php echo json_encode(dcvs_get_video_js()); ?>;
		var ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
		var lastVideo = '<?php echo get_user_meta(get_current_user_id(), 'video_progress', true); ?>';
	</script>
	<script src="<?php echo '../js/landing.js'; ?>" rel="stylesheet"></script>
</head>

<body>

    <header class="header">

        <h1>virtual store</h1>
        <?php
          $ware_house_start_date = date_create(dcvs_get_option('warehouse_start_date', 0));
          $ware_house_end_date = date_create(dcvs_get_option('warehouse_end_date', 0));
          $shopping_start_date = date_create(dcvs_get_option('shopping_start_date', 0));
          $shopping_end_date = date_create(dcvs_get_option('shopping_end_date', 0));
          $date_now = date("Y-m-d H:i:s");

          $ware_house_shopping_over = false;
          $shopping_over = false;
          $shopping_started = false;


          date_add(date_create(date($ware_house_start_date->format("Y-m-d H:i:s"))), date_interval_create_from_date_string("23 hours 59 minutes 59 seconds"));
          $ware_house_start_date = $ware_house_start_date->format("Y-m-d H:i:s");

          date_add(date_create(date($ware_house_end_date->format("Y-m-d H:i:s"))), date_interval_create_from_date_string("23 hours 59 minutes 59 seconds"));
          $ware_house_end_date = $ware_house_end_date->format("Y-m-d H:i:s");

          date_add(date_create(date($shopping_start_date->format("Y-m-d H:i:s"))), date_interval_create_from_date_string("23 hours 59 minutes 59 seconds"));
          $shopping_start_date = $shopping_start_date->format("Y-m-d H:i:s");

          date_add(date_create(date($shopping_end_date->format("Y-m-d H:i:s"))), date_interval_create_from_date_string("23 hours 59 minutes 59 seconds"));
          $shopping_end_date = $shopping_end_date->format("Y-m-d H:i:s");

          if($date_now <= $ware_house_start_date){
            // DISPLAY TIME TO WAREHOUSE SHOPPING BEGINS
            ?>
            <div>
              <p>time left until warehouse shopping Begins :</p>
              <b id="remaining-time"> <script>getTime("<?php echo $ware_house_start_date.''; ?>")</script></b>
            </div>

            <?php
          }
          elseif ($date_now >= $ware_house_start_date && $date_now <= $ware_house_end_date) {
            # code...
            ?>
            <div>
              <p>time left until warehouse shopping Ends :</p>
              <b id="remaining-time"> <script>getTime("<?php echo $ware_house_end_date.''; ?>")</script></b>
            </div>

            <?php
          }
          elseif ($date_now <= $shopping_start_date && $date_now >= $ware_house_end_date) {
            # code...
            $ware_house_shopping_over = true;
            ?>
            <div>
              <p>time left until shopping starts :</p>
              <b id="remaining-time"> <script>getTime("<?php echo $shopping_start_date.''; ?>")</script></b>
            </div>

            <?php
          }
          elseif($date_now >= $shopping_start_date && $date_now < $shopping_end_date){
            $shopping_started = true;
            $ware_house_shopping_over = true;
            ?>
            <div>
              <p>time left until shopping ends :</p>
              <b id="remaining-time"> <script>getTime("<?php echo $shopping_end_date.''; ?>")</script></b>
            </div>

            <?php
          }
          else{
            $shopping_over = true;
            $ware_house_shopping_over = true;
            ?>
            <p>
              Shopping over
            </p>
            <?php
          }
         ?>

        <a href="<?php echo wp_logout_url(); ?>"><button class="logout">LOGOUT</button></a>
    </header>

    <div class="mainContent">


        <aside class="helpVideoList">

            <figure>
                <div id="video"></div>
                <figcaption id="caption"></figcaption>
            </figure>

			<h2>Video Tutorials</h2>
			<p>Refer to these videos when you need help setting up your store and shopping for your consumer roles.</p>
            <ol id="videos">
            </ol>

        </aside>

        <main class="dashboard">
            <?php
              if(isset($business_info[0])){
                  $user_blog_id = intval(get_user_blog_id( $business_info[0]->user_id ));
                  $site_name = get_bloginfo('name');
                ?>
                  <h1><?php echo $site_name ?></h1>
                  <h2>BUYER ACTIVITIES</h2>
                <?php
              }

              else{
                ?>
                <h1><?php echo "Business not set" ?></h1>
                <?php
              }

             ?>

            <!-- <hr> -->
            <section class="myStore">


                <div class="myStoreLeft">
                    <?php
                    if($ware_house_shopping_over == false){
                      ?>
                      <a href="<?php echo network_home_url() .'shop' ?>"><button class="button">WAREHOUSE</button></a>
                      <?php
                    }
                    else{
                      ?>
                      <a href="<?php echo network_home_url() .'shop'  ?>" class="unavailable"><button class="button">WAREHOUSE</button></a>
                      <?php
                    }

                     ?>




                    <?php
                      if(isset($business_info[0])){
                          if(isset($business_category[0])){
                        ?>
                            <b><?php echo "Your business should target: " . stripslashes_deep($business_category[0]->name); ?></b>
                          <?php } ?>
                            <br>
                            <p><?php echo stripslashes_deep($business_info[0]->description); ?></p>
                            <br>
                            <br>
                            <?php
                            if (($business_info[0]->money - $business_expense) < 0) {
                              ?>
                              <b class="negative">warehouse budget: $<?php echo number_format($business_info[0]->money - $business_expense, 2) ?></b>
                              <p class="negative">Warning! You're Over Budget!</p>
                              <?php
                            } else {
                              ?>
                              <b>warehouse budget: $<?php echo number_format($business_info[0]->money - $business_expense, 2) ?></b>
                              <?php
                            }
                            ?>


                        <?php
                      }

                      else{
                        ?>
                        <p><?php echo "Business not set" ?>
                            <br>
                            <br><b>budget: $<?php echo "Business not set" ?></b>
                        </p>
                        <?php
                      }

                     ?>


                </div>
                <div class="myStoreRight">


                    <div class="storeButtons">
                      <a href="<?php echo get_home_url().'/wp-admin/edit.php?post_type=product'; ?>"><button class="button btnStore">EDIT STORE</button></a>
                      <a href="<?php echo $business_info[0]->url ?>"><button class="button btnStore">VIEW STORE</button></a>
                    </div>
                    <?php
                    if ($ware_house_shopping_over == false) {
                      ?>
                        <a href="<?php echo get_home_url().'/wp-admin/admin.php?page=wc-reports'; ?>" class="unavailable"><button class="button btnStore">STORE STATS</button></a>
                        <a href="<?php echo plugins_url( 'templates/store_survey_list.php', dirname(__FILE__)) ?>" class="unavailable"><button class="button btnStore">STORE FEEDBACK</button></a>
                      <?php
                    } else {
                      ?>
                        <a href="<?php echo get_home_url().'/wp-admin/admin.php?page=wc-reports'; ?>"><button class="button btnStore">STORE STATS</button></a>
                        <a href="<?php echo plugins_url( 'templates/store_survey_list.php', dirname(__FILE__)) ?>"><button class="button btnStore">STORE FEEDBACK</button></a>
                      <?php
                    }
                    ?>

                    <?php
                        $search_criteria = array('field_filters' => array());
                        $search_criteria['field_filters'][] = array(
                            'key' => 'created_by',
                            'value' => $current_user_ID
                        );
                        $entries = GFAPI::get_entries(4, $search_criteria);

                        if($shopping_over && !count($entries)) {
                            ?>
                            <a href=" <?php echo get_site_url(1) . '/personal-store-evaluation'; ?>">
                                <button class="button btnStore">FINAL SURVEY</button>
                            </a>
                            <?php
                        } else {
                            ?>
                            <a href=" <?php echo get_site_url(1) . '/personal-store-evaluation'; ?>" class="unavailable">
                                <button class="button btnStore">FINAL SURVEY</button>
                            </a>
                            <?php
                        }

                    ?>


                </div>


            </section>

            <h2>CONSUMER ACTIVITIES</h2>
            <!-- <hr> -->
            <section class="goShopping">


                <div>
                        <h3>CONSUMER 1</h3>
                    <?php
                    if(isset($consumer_info[0])){
                        ?>
                        <h4><?php echo $consumer_info[0]->name ?></h4>

                        <p><?php echo stripslashes_deep($consumer_info[0]->description) ?>
                            <br>
                            <br>
                            <b>consumer budget: $<?php echo number_format($consumer_info[0]->money - $consumer_1_expense, 2) ?></b>
                        </p>
                        <?php
                      }

                      else{
                        ?>
                        <p><?php echo "Consumer 1 info not set" ?>
                            <br>
                            <br>
                            <b>consumer budget: $<?php ?></b>
                        </p>
                        <?php
                      }

                     ?>

                    <div class="personaButtons">
                      <?php
                      if($shopping_over == false && $shopping_started == true && isset($consumer_info[0])){
                        ?>
                        <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[0]->id ?>">
                          <button class="button personaSmall one" name="shop_as_consumer_one">SHOP</button>
                          </a>
                        <?php

                      }
                      else{
                        ?>
                        <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[0]->id ?>" class="unavailable">
                          <button class="button personaSmall one" name="shop_as_consumer_one">SHOP</button>
                          </a>
                        <?php
                      }

                           ?>


                      <br>
                      <a href="<?php echo plugins_url( 'templates/consumer_stats.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[0]->id ?>"><button class="button personaSmall one">STATS</button></a>
                        <?php
                        $persona_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $current_user_ID));

                        $search_criteria = array('field_filters' => array());
                        $search_criteria['field_filters'][] = array(
                            'key' => 'created_by',
                            'value' => $current_user_ID
                        );
                        if (isset($persona_info[0])) {
                            $search_criteria['field_filters'][] = array(
                                'key' => 3,
                                'value' => $persona_info[0]->id
                            );
                        } else {
                            $search_criteria['field_filters'][] = array(
                                'key' => 3,
                                'value' => -1
                            );
                        }
                        $entries = GFAPI::get_entries(3, $search_criteria);

                        if($shopping_over && !count($entries)) {
                            ?>
                            <a href="<?php echo get_site_url(1) . '/end-of-shopping-evaluation?persona_id=' . $consumer_info[0]->id ?>">
                                <button class="button personaSmall one">FINAL SURVEY</button>
                            </a>
                            <?php
                        } else {
                            ?>
                            <a href="<?php echo get_site_url(1) . '/end-of-shopping-evaluation'?>" class="unavailable">
                                <button class="button personaSmall one">FINAL SURVEY</button>
                            </a>
                            <?php
                        }

                        ?>
                    </div>


                </div>

                <div>
                        <h3>CONSUMER 2</h3>
                    <?php
                    if(isset($consumer_info[1])){
                        ?>
                        <h4><?php echo $consumer_info[1]->name ?></h4>

                        <p><?php echo stripslashes_deep($consumer_info[1]->description); ?>
                            <br>
                            <br>
                            <!-- TODO fix -->
                            <b>consumer budget: $<?php echo number_format($consumer_info[1]->money - $consumer_2_expense, 2) ?></b>
                        </p>
                        <?php
                      }

                      else{
                        ?>
                        <p><?php echo "Consumer 1 info not set" ?>
                            <br>
                            <br>
                            <b>consumer budget: $<?php ?></b>
                        </p>
                        <?php
                      }

                     ?>
                    <div class="personaButtons">
                      <?php
                      if($shopping_over == false && $shopping_started == true && isset($consumer_info[1])){
                        ?>
                        <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[1]->id ?>">
                          <button class="button personaSmall two" name="shop_as_consumer_one">SHOP</button>
                          </a>
                        <?php

                      }
                      else{
                        ?>
                        <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[1]->id ?>" class="unavailable">
                          <button class="button personaSmall two" name="shop_as_consumer_one">SHOP</button>
                          </a>
                        <?php
                      }

                           ?>

                      <br>
                      <a href="<?php echo plugins_url( 'templates/consumer_stats.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[1]->id ?>"><button class="button personaSmall two">STATS</button></a>
                      <?php
                      $search_criteria = array('field_filters' => array());
                      $search_criteria['field_filters'][] = array(
                          'key' => 'created_by',
                          'value' => $current_user_ID
                      );
                      if (isset($persona_info[1])) {
                          $search_criteria['field_filters'][] = array(
                              'key' => 3,
                              'value' => $persona_info[1]->id
                          );
                      } else {
                          $search_criteria['field_filters'][] = array(
                              'key' => 3,
                              'value' => -1
                          );
                      }
                      $entries = GFAPI::get_entries(3, $search_criteria);

                      if($shopping_over && !count($entries)) {
                            ?>
                            <a href="<?php echo get_site_url(1) . '/end-of-shopping-evaluation?persona_id=' . $consumer_info[1]->id ?>">
                                <button class="button personaSmall two">FINAL SURVEY</button>
                            </a>
                            <?php
                        } else {
                            ?>
                            <a href="<?php echo get_site_url(1) . '/end-of-shopping-evaluation'?>" class="unavailable">
                                <button class="button personaSmall two">FINAL SURVEY</button>
                            </a>
                            <?php
                        }

                      ?>
                    </div>


                </div>

            </section>

        </main>

    </div>




</body>

<?php

?>
