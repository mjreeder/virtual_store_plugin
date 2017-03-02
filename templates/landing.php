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
 $persona_info = $wpdb->get_results($wpdb->prepare('SELECT * FROM dcvs_persona LEFT JOIN dcvs_user_persona ON dcvs_persona.id=dcvs_user_persona.persona_id WHERE user_id = %d', $current_user_ID));
 $var = dcvs_get_option('warehouse_end_date', 0);

 // var_dump($warehouse_end_shopping_date);

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

        <p>time left until shopping begins : <b id="remaining-time"> <script>getTime("<?php echo $var.""; ?>")</script></b></p>

    </header>

    <div class="mainContent">


        <aside class="helpVideoList">

            <figure>
                <img src="../assets/images/bg.jpg"></iframe>
                <figcaption>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi malesuadxa nibh eu pellentesque interdum.</figcaption>
            </figure>

            <ol>
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

                    <button class="button">WAREHOUSE</button>
                    <p><?php echo $business_info[0]->description ?>
                        <br>
                        <br><b>budget: $<?php echo $business_info[0]->money ?></b>
                    </p>
                </div>
                <div class="myStoreRight">

                    <button class="button btnStore">EDIT STORE</button>
                    <!-- <button class="button btnStore">VIEW STORE</button> -->
                    <a href="<?php echo $business_info[0]->url ?>" class="button">VIEW STORE</a>
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

                    <p><?php echo $persona_info[0]->description ?>
                        <br>
                        <br><b>persona budget: $<?php echo $persona_info[0]->money ?></b></p>
                    <button class="button personaSmall one">SHOP</button>
                    <button class="button personaSmall one">STATS</button>

                </div>

                <div>

                    <div class="persona two">
                        <h3>PERSONA #2</h3>
                        <img src="../assets/images/personaBlue.png" alt="">
                    </div>

                    <p><?php echo $persona_info[1]->description ?>
                        <br>
                        <br><b>persona budget: $<?php echo $persona_info[1]->money ?></b></p>
                    <button class="button personaSmall two">SHOP</button>
                    <button class="button personaSmall two">STATS</button>

                </div>

            </section>

        </main>

    </div>




</body>
