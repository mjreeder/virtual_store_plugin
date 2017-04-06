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
$consumer_1_expense = dcvs_get_persona_expenses($current_user_ID, $consumer_info[0]->persona_id);
$consumer_2_expense = dcvs_get_persona_expenses($current_user_ID, $consumer_info[1]->persona_id);
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

        <p>time left until shopping begins : <b id="remaining-time"> <script>getTime("<?php echo $var.''; ?>")</script></b></p>

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
                <?php
                for ($i=0; $i < sizeof($file->data); $i++) {
                  ?>
                  <li class="finished">
                    <?php
                    $framestring = $file->data[$i]->embed->html;
                    $descriptionString = $file->data[$i]->description;
                    $descriptionString = str_replace("\n", "\\n",$file->data[$i]->description);

                    echo "<script>frameString".$i." = '$framestring'</script>";
                    echo "<script>descriptionString".$i." = '$descriptionString'</script>";
                    ?>
                      <p onclick="setDisplayVideo(frameString<?php echo $i ?>, descriptionString<?php echo $i ?>)"><?php echo $file->data[$i]->description;?></p><span><?php echo gmdate("H:i:s", $file->data[$i]->duration); ?></span>
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

            <h1><?php echo $business_info[0]->title ?></h1>
            <!-- <hr> -->
            <section class="myStore">


                <div class="myStoreLeft">

                    <a href="<?php echo get_site_url() ?>"><button class="button">WAREHOUSE</button></a>

                    <p><?php echo $business_info[0]->description ?>
                        <br>
                        <br><b>budget: $<?php echo $business_info[0]->money - $business_expense ?></b>
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

                    <p><?php echo $consumer_info[0]->description ?>
                        <br>
                        <br>
                        <b>persona budget: $<?php echo $consumer_info[0]->money - $consumer_1_expense ?></b>
                    </p>
                    <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[0]->id ?>">
                        <button class="button personaSmall one" name="shop_as_consumer_one">SHOP</button>
                    </a>
                    <br>
                    <button class="button personaSmall one">STATS</button>

                </div>

                <div>

                    <div class="persona two">
                        <h3>PERSONA #2</h3>
                        <img src="../assets/images/personaBlue.png" alt="">
                    </div>

                    <p><?php echo $consumer_info[1]->description ?>
                        <br>
                        <br>
                        <b>persona budget: $<?php echo $consumer_info[1]->money - $consumer_2_expense ?></b>
                    </p>
                    <a href="<?php echo plugins_url( 'templates/stores.php', dirname(__FILE__)) . '?persona_id=' . $consumer_info[1]->id ?>">
                        <button class="button personaSmall two" name="shop_as_consumer_two">SHOP</button>
                    </a>

                    <br>
                    <button class="button personaSmall two">STATS</button>

                </div>

            </section>

        </main>

    </div>




</body>

<?php

?>
