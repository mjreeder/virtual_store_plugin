<?php
/**
 * Created by PhpStorm.
 * User: bryonoconner
 * Date: 1/22/17
 * Time: 10:57 PM.
 */
 require_once __DIR__.'/../../../../wp-blog-header.php';
 date_default_timezone_set('UTC');
 $current_user_ID = wp_get_current_user()->ID;
 $business_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_business LEFT JOIN dcvs_user_business ON dcvs_business.id=dcvs_user_business.business_id WHERE user_id = %d', $current_user_ID));
 $user_total_money = $business_info[0]->money;
 $money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_warehouse_purchase WHERE user_id = %d', $current_user_ID));
 $consumer_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $current_user_ID));
 $persona_one_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $consumer_info[0]->id));
 $persona_two_total_money = $wpdb->get_results($wpdb->prepare('SELECT money FROM dcvs_persona WHERE id = %d', $consumer_info[1]->id));
 $persona_one_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $current_user_ID, $consumer_info[0]->id));
 $persona_two_money_spent = $wpdb->get_results($wpdb->prepare('SELECT sum(cost) FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $current_user_ID, $consumer_info[1]->id));
 $var = dcvs_get_option('warehouse_end_date', 0);

 function get_value_from_stdClass($obj)
 {
     $array = get_object_vars($obj);
     reset($array);
     $first_key = key($array);
     if (intval($array[$first_key]) > 0) {
         return $array[$first_key];
     } else {
         return 0;
     }
 }

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

        <p>time left until shopping begins : <b id="remaining-time"> <script>getTime("<?php echo $var.''; ?>")</script></b></p>

    </header>

    <div class="mainContent">


        <aside class="helpVideoList">

            <figure>
                <?php

                $opts = array(
                  'http' => array(
                    'method' => 'GET',
                    'header' => 'Authorization: Bearer 2f31f4053bb21a971bad92c108b253bf',
                  ),
                );

                $context = stream_context_create($opts);

                // Open the file using the HTTP headers set above
                $file = json_decode(file_get_contents('https://api.vimeo.com/users/10466342/albums/4481462/videos', false, $context), $assoc_array = false);
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
                <?php
                for ($i = 0; $i < sizeof($file->data); ++$i) {
                    ?>
                  <li class="finished">
                    <?php
                    $framestring = $file->data[$i]->embed->html;
                    $descriptionString = $file->data[$i]->description;
                    $descriptionString = str_replace("\n", '\\n', $file->data[$i]->description);

                    echo '<script>frameString'.$i." = '$framestring'</script>";
                    echo '<script>descriptionString'.$i." = '$descriptionString'</script>";
                    ?>
                      <p onclick="setDisplayVideo(frameString<?php echo $i ?>, descriptionString<?php echo $i ?>)"><?php echo $file->data[$i]->description;
                    ?></p><span><?php echo gmdate('H:i:s', $file->data[$i]->duration);
                    ?></span>
                  </li>
                  <?php

                }
                 ?>
                <li class="finished">
                    <p>Do the first thing</p><span>1:45</span>
                </li>
                <li class="currentlyPlaying">
                    <p>Do the second thing</p><span>1:45</span>
                </li>
                <li>
                    <p>This is a super long video title that's gonna tell you a bunch of stuff to do.</p><span>1:45</span>
                </li>
                <li>
                    <p>Go to this other place</p><span>1:45</span>
                </li>
            </ol>

        </aside>

        <main class="dashboard">

            <h1>my store</h1>
            <!-- <hr> -->
            <section class="myStore">


                <div class="myStoreLeft">

                    <a href="<?php echo get_site_url() ?>"><button class="button">WAREHOUSE</button></a>

                    <p><?php echo $business_info[0]->description ?>
                        <br>
                        <br><b>budget: $<?php echo $user_total_money - get_value_from_stdClass($money_spent[0]) ?></b>
                    </p>
                </div>
                <div class="myStoreRight">

                    <button class="button btnStore">EDIT STORE</button>
                    <a href="<?php echo $business_info[0]->url ?>"><button class="button btnStore">VIEW STORE</button></a>

                    <button class="button btnStore">STORE STATS</button>

                </div>


            </section>

            <h1>go shopping</h1>
            <!-- <hr> -->
            <section class="goShopping">


                <div>

                    <div class="persona one">
                        <h3>PERSONA #1</h3>
                        <img src="../assets/images/personaRed.png" alt="">
                    </div>

                    <?php

                      if ($_POST) {
                          if (isset($_POST['shop_as_consumer_one'])) {
                              set_current_consumer($current_user_ID, $consumer_info[0]->id);
                          }
                      }

                      ?>

                    <p><?php echo $consumer_info[0]->description ?>
                        <br>
                        <br><b>persona budget: <?php
            							 $difference = get_value_from_stdClass($persona_one_total_money[0]) - get_value_from_stdClass($persona_one_money_spent[0]);
            							 echo '$'.$difference;
            						 ?></b></p>

                        <form action="" method="post">
                            <button class="button personaSmall one" name="shop_as_consumer_one">SHOP</button>
                        </form>
                    <button class="button personaSmall one">STATS</button>

                </div>

                <div>

                    <div class="persona two">
                        <h3>PERSONA #2</h3>
                        <img src="../assets/images/personaBlue.png" alt="">
                    </div>

                    <?php

                      if ($_POST) {
                          if (isset($_POST['shop_as_consumer_two'])) {
                              set_current_consumer($current_user_ID, $consumer_info[1]->id);
                          }
                      }

                      ?>
                    <p><?php echo $consumer_info[1]->description ?>
                        <br>
                        <br><b>persona budget: <?php
            							 $difference = get_value_from_stdClass($persona_two_total_money[0]) - get_value_from_stdClass($persona_two_money_spent[0]);
            							 echo '$'.$difference;
            						 ?></b></p>
                        <form action="" method="post">
                          <button class="button personaSmall two" name="shop_as_consumer_two">SHOP</button>
                        </form>

                    <button class="button personaSmall two">STATS</button>

                </div>

            </section>

        </main>

    </div>




</body>

<?php
function set_current_consumer($user_id, $consumer_id)
{
    global $wpdb;
    $result = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_current_persona WHERE user_id = %d', $user_id));
    if (sizeOf($result) > 0) {
        $wpdb->get_results($wpdb->prepare('UPDATE dcvs_current_persona set current_persona_id = %d WHERE user_id = %d', $consumer_id, $user_id));
    } else {
        $wpdb->insert('dcvs_current_persona', ['user_id' => $user_id, 'current_persona_id' => $consumer_id]);
    }
}

?>
