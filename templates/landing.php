<?php
/**
 * Created by PhpStorm.
 * User: bryonoconner
 * Date: 1/22/17
 * Time: 10:57 PM.
 */
require_once '/Users/coreyh/virtual_store/wp-blog-header.php';

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
if(isset($consumer_info[0])){
  $consumer_1_expense = dcvs_get_persona_expenses($current_user_ID, $consumer_info[0]->persona_id);

}
if(isset($consumer_info[1])){
  $consumer_2_expense = dcvs_get_persona_expenses($current_user_ID, $consumer_info[1]->persona_id);
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
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"
      integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
      crossorigin="anonymous"></script>
    <script src="<?php echo '../js/landing.js';
  ?>" rel="stylesheet"></script>

</head>

<body>

    <header class="header">

        <h1>virtual store</h1>
        <?php
          $ware_house_start_date = dcvs_get_option('warehouse_start_date', 0);
          $ware_house_end_date = dcvs_get_option('warehouse_end_date', 0);
          $shopping_start_date = dcvs_get_option('shopping_start_date', 0);
          $shopping_end_date = dcvs_get_option('shopping_end_date', 0);
          $ware_house_shopping_over = false;
          $shopping_over = false;
          $date_now = date("Y-m-d");

          if($date_now <= $ware_house_start_date){
            // DISPLAY TIME TO WAREHOUSE SHOPPING BEGINS
            ?>
            <p>
              time left until warehouse shopping Begins :
            <b id="remaining-time"> <script>getTime("<?php echo $ware_house_start_date.''; ?>")</script></b></p>
            <?php
          }
          elseif ($date_now >= $ware_house_start_date && $date_now <= $ware_house_end_date) {
            # code...
            ?>
            <p>
              time left until warehouse shopping Ends :
            <b id="remaining-time"> <script>getTime("<?php echo $ware_house_end_date.''; ?>")</script></b></p>
            <?php
          }
          elseif ($date_now <= $shopping_start_date && $date_now >= $ware_house_end_date) {
            # code...
            $ware_house_shopping_over = true;
            ?>
            <p>
              time left until shopping starts :
            <b id="remaining-time"> <script>getTime("<?php echo $shopping_start_date.''; ?>")</script></b></p>
            <?php
          }
          elseif($date_now >= $shopping_start_date && $date_now < $shopping_end_date){
            ?>
            <p>
              time left until shopping ends :
            <b id="remaining-time"> <script>getTime("<?php echo $shopping_end_date.''; ?>")</script></b></p>
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
    </header>

    <div class="mainContent">


        <aside class="helpVideoList">

            <figure>
                <?php

                $opts = array(
                  'http'=>array(
                    'method'=>"GET",
                    'header'=>"Authorization: Bearer 2f31f4053bb21a971bad92c108b253bf"
                  )
                );

                $context = stream_context_create($opts);

                // Open the file using the HTTP headers set above
                $file = json_decode(file_get_contents('https://api.vimeo.com/users/10466342/albums/4481462/videos', false, $context), $assoc_array = false );
                $currently_playing_video = $file->data[0]->embed->html;
                $current_playing_caption = $file->data[0]->description;
                ?>
                <!-- ORIGINAL DESIGN IMAGE BELOW -->
                <!-- <img src="../assets/images/bg.jpg"> -->
                <div id="video"></div>
                <figcaption id="caption"></figcaption>
                <script>setDisplayVideo('<?php echo $currently_playing_video?>', '<?php echo $current_playing_caption?>')</script>

            </figure>

            <ol>
              <li class="currentlyPlaying" id="<?php echo 0; ?>">
                <?php
                $framestring = $file->data[0]->embed->html;
                $descriptionString = $file->data[0]->description;
                $descriptionString = str_replace("\n", "\\n",$file->data[0]->description);

                echo "<script>frameString".'0'." = '$framestring'</script>";
                echo "<script>descriptionString".'0'." = '$descriptionString'</script>";
                ?>
                  <p onclick="setDisplayVideo(frameString<?php echo 0 ?>, descriptionString<?php echo 0 ?>, <?php echo 0; ?>)"><?php echo $file->data[0]->description;?></p><span><?php echo gmdate("i:s", $file->data[0]->duration); ?></span>
              </li>
                <?php

                for ($i=1; $i < sizeof($file->data); $i++) {
                  ?>
                  <li id="<?php echo $i ?>">
                    <?php
                    $framestring = $file->data[$i]->embed->html;
                    $descriptionString = $file->data[$i]->description;
                    $descriptionString = str_replace("\n", "\\n",$file->data[$i]->description);

                    echo "<script>frameString".$i." = '$framestring'</script>";
                    echo "<script>descriptionString".$i." = '$descriptionString'</script>";
                    ?>
                      <p onclick="setDisplayVideo(frameString<?php echo $i ?>, descriptionString<?php echo $i ?>, <?php echo $i ?>)"><?php echo $file->data[$i]->description;?></p><span><?php echo gmdate("i:s", $file->data[$i]->duration); ?></span>
                  </li>
                  <?php
                }
                 ?>
            </ol>

        </aside>

        <main class="dashboard">
            <?php
              if(isset($business_info[0])){
                ?>
                <h1><?php echo $business_info[0]->title ?></h1>
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
                      <a href="<?php echo network_home_url() ?>"><button class="button">WAREHOUSE</button></a>
                      <?php
                    }
                    else{
                      ?>
                      <a href="<?php echo network_home_url() ?>" class="unavailable"><button class="button">WAREHOUSE</button></a>
                      <?php
                    }

                     ?>




                    <?php
                      if(isset($business_info[0])){
                        ?>
                        <p><?php echo $business_info[0]->description ?>
                            <br>
                            <br><b>budget: $<?php echo $business_info[0]->money - $business_expense ?></b>
                        </p>
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

                    <a href="<?php echo get_home_url().'/wp-admin/edit.php?post_type=product'; ?>"><button class="button btnStore">EDIT STORE</button></a>
                    <a href="<?php echo $business_info[0]->url ?>"><button class="button btnStore">VIEW STORE</button></a>
                    <a href="<?php echo get_home_url().'/wp-admin/admin.php?page=wc-reports'; ?>"><button class="button btnStore">STORE STATS</button></a>

                </div>


            </section>

            <h1>go shopping</h1>
            <!-- <hr> -->
            <section class="goShopping">


                <div>

                    <div class="persona one">
                        <h3>PERSONA #1</h3>

                        <img src=<?php echo plugins_url( '/assets/images/', dirname(__FILE__)) .'personaOne.svg' ?> alt="">
                    </div>

                    <?php
                      if(isset($consumer_info[0])){
                        ?>
                        <p><?php echo $consumer_info[0]->description ?>
                            <br>
                            <br>
                            <b>persona budget: $<?php echo $consumer_info[0]->money - $consumer_1_expense ?></b>
                        </p>
                        <?php
                      }

                      else{
                        ?>
                        <p><?php echo "Consumer 1 info not set" ?>
                            <br>
                            <br>
                            <b>persona budget: $<?php ?></b>
                        </p>
                        <?php
                      }

                     ?>

                    <div class="personaButtons">
                      <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[0]->id ?>">
                          <?php
                          if($shopping_over == false|| isset($consumer_info[0])){
                            ?>
                            <button class="button personaSmall one" name="shop_as_consumer_one">SHOP</button>
                            <?php
                          }
                          else{
                            ?>
                            <button class="button personaSmall one" name="shop_as_consumer_one" disabled>SHOP</button>
                            <?php
                          }


                           ?>

                      </a>
                      <br>
                      <a href=""><button class="button personaSmall one">STATS</button></a>
                    </div>


                </div>

                <div>

                    <div class="persona two">
                        <h3>PERSONA #2</h3>
                        <img src=<?php echo plugins_url( '/assets/images/', dirname(__FILE__)) .'personaTwo.svg' ?> alt="">
                    </div>

                    <?php
                      if(isset($consumer_info[1])){
                        ?>
                        <p><?php echo $consumer_info[1]->description ?>
                            <br>
                            <br>
                            <b>persona budget: $<?php echo $consumer_info[1]->money - $consumer_1_expense ?></b>
                        </p>
                        <?php
                      }

                      else{
                        ?>
                        <p><?php echo "Consumer 1 info not set" ?>
                            <br>
                            <br>
                            <b>persona budget: $<?php ?></b>
                        </p>
                        <?php
                      }

                     ?>
                    <div class="personaButtons">
                      <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[1]->id ?>">

                          <?php
                          if($shopping_over == false || isset($consumer_info[1])){
                            ?>
                            <button class="button personaSmall two" name="shop_as_consumer_two">SHOP</button>
                            <?php
                          }
                          else{
                            ?>
                            <button class="button personaSmall two" name="shop_as_consumer_two" disabled>SHOP</button>
                            <?php
                          }


                           ?>
                      </a>

                      <br>
                      <a href=""><button class="button personaSmall two">STATS</button></a>
                    </div>


                </div>

            </section>

        </main>

    </div>




</body>

<?php

?>
