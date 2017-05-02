<?php

require_once __DIR__.'/../../../../wp-blog-header.php';
global $wpdb;
if ( !is_user_logged_in() ) {
    wp_redirect( get_site_url() . '/wp-admin' );
    exit;
}

$current_persona_id = $_REQUEST['persona_id'];

if(!filter_var($current_persona_id, FILTER_VALIDATE_INT)){
    wp_die("persona_id must be an integer");
}
// check if current persona id belongs to the logged in user or super admin

$current_user_id = get_current_user_id();
if(!filter_var($current_user_id, FILTER_VALIDATE_INT) || $current_user_id == 0){
    wp_die("invalid logged in user");
}
$result = $wpdb->get_row("SELECT * FROM dcvs_user_persona WHERE user_id = ".$current_user_id." AND id=".$current_persona_id);

if($result == null){
    wp_die("persona_id does not match logged in user");
}




//pull purchase data

$purchaseResults = $wpdb->get_results("SELECT items, cost, dcvs_business.title as business_title, dcvs_business.description as business_description, dcvs_persona.name as persona_name, dcvs_persona.description as persona_description FROM dcvs_business_purchase INNER JOIN dcvs_business ON dcvs_business.id = dcvs_business_purchase.business_id INNER JOIN dcvs_user_persona ON dcvs_user_persona.id = dcvs_business_purchase.user_persona_id INNER JOIN dcvs_persona ON dcvs_persona.id = dcvs_user_persona.persona_id WHERE user_persona_id = ".$current_persona_id, OBJECT);

//deserialize the items

foreach($purchaseResults as $purchaseResult){
    $purchaseResult->items = unserialize($purchaseResult->items);
}

$persona = $wpdb->get_row("SELECT * FROM dcvs_persona INNER JOIN dcvs_user_persona ON dcvs_user_persona.persona_id = dcvs_persona.id WHERE dcvs_user_persona.id = ".$current_persona_id, OBJECT);

if($persona == null){
    wp_die("Persona is not valid or set");
}

//echo "<pre>";
//var_dump($purchaseResults);
//echo "</pre>";


// display the data

?>

<!doctype HTML>
<html>

<head>
    <title>Virtual Store</title>
    <!-- CSS -->
    <link href="../assets/css/dashboard.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/budgetBar.css" rel="stylesheet" type="text/css">
    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">
</head>

<body>

<header class="header">

    <h1>virtual store</h1>

</header>

<div class="stats">

    <h1><?php echo stripslashes($persona->name); ?> Stats</h1>

    <?php foreach($purchaseResults as $purchase): ?>
    <div class="tableWrapper">
        <table class="virtualTable">
            <tr>
                <th>ITEMS</th>
                <th>SIZE</th>
                <th>COLOR</th>
                <th>#PURCH</th>
                <th>TOTAL</th>
            </tr>

            <?php foreach($purchase->items as $item):
                $item_name = $item['name'];
                $color = "";
                $size = "";
                if(isset($item['item_meta']['pa_size'][0])){
                    $size=$item['item_meta']['pa_size'][0];
                }
                if(isset($item['item_meta']['pa_color'][0])){
                    $color=$item['item_meta']['pa_color'][0];
                }
                $quantity = $item['qty'];
                $total = $item['line_total'];
            ?>
                <tr>
                    <td><?php echo $item_name; ?></td>
                    <td><?php echo $size; ?></td>
                    <td><?php echo $color; ?></td>
                    <td><?php echo $quantity; ?></td>
                    <td>$<?php echo $total; ?></td>
                </tr>

            <?php endforeach; ?>
        </table>
    </div>
    <?php endforeach; ?>

</div>

</body>

</html>