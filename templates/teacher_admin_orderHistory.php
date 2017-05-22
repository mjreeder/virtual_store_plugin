<?php
global $wpdb;
$currentDisplayStudent = $_REQUEST['student_id'];
$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudent));
$user_id = $_REQUEST['user_id'];
$persona_id = $_REQUEST['persona_id'];
$user_persona_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $user_id, $persona_id));

//echo "<pre>";var_dump($user_persona_order_history);die("</pre>");

$second = array_slice(get_blogs_of_user( $currentDisplayStudent), 1, 1);

$blog_id = get_value_from_stdClass($second[0])["userblog_id"];
function get_value_from_stdClass($obj){
	$array = get_object_vars($obj);
	reset($array);
	$first_key = key($array);
  return $array;
}
 ?>
<section class="orderHistory" id='history'>
	<div class="scrollable">
<h1 class="title"><?php echo $display_name[0]->display_name ?></h1>
<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id']?>" class="backButton"><p>BACK TO STUDENT INFO</p></a>

<div class="tableWrapper">

        <?php
        if (sizeOf($user_persona_order_history) >= 1) {
        for ($i = 0; $i < sizeOf($user_persona_order_history); ++$i) {?>
        <table class="virtualTable orderTable">
        <tr>
            <th>ITEMS</th>
            <th>Size</th>
            <th>Color</th>
            <th>DESCRIPTION</th>
            <th>Quantity</th>
            <th>PRICE</th>
        </tr>
        <?php
          $wp_blogID = 'wp_'.$blog_id.'_posts';
          $items = unserialize($user_persona_order_history[$i]->items);
          for($j = 0; $j < count($items); $j++) {
              $variation_id = $items[$j]["item_meta"]["_variation_id"];

              $post = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wp_blogID WHERE ID = %d", $variation_id[0]));

              $post = get_value_from_stdClass($post[0]);
              if (isset($post['post_parent'])) {
                  $postParentDescriptionSql = $wpdb->get_results($wpdb->prepare("SELECT post_content FROM $wp_blogID WHERE id = %d", $post["post_parent"]));
                  if (isset($postParentDescriptionSql[0])) {
                      $studentProductDescriptionText = get_value_from_stdClass($postParentDescriptionSql[0])["post_content"];
                  } else {
                      $studentProductDescriptionText = $post['post_content'];
                  }
              } else {
                  $studentProductDescriptionText = '';
              }

                $size = isset($items[$j]['item_meta']["pa_size"][0]) ? $items[$j]['item_meta']["pa_size"][0]:"N/A";
                $color = isset($items[$j]['item_meta']["pa_color"][0]) ? $items[$j]['item_meta']["pa_color"][0]:"N/A";

              ?>

              <tr>
                  <td><?php echo $items[$j]["name"]; ?></td>
                  <td><?php echo $size?></td>
                  <td><?php echo $color;?></td>
                  <td class="desc"><?php echo $studentProductDescriptionText ?></td>
                  <td><?php echo $items[$j]['item_meta']["_qty"][0];?></td>
                  <td>$ <?php echo $items[$j]['item_meta']['_line_total'][0] ?></td>
              </tr>
              <?php
          }
        ?></table><br><?php
        }
      }
      else{
        ?>
    <table class="virtualTable orderTable">
        <tr>
            <th>ITEMS</th>
            <th>Size</th>
            <th>Color</th>
            <th>DESCRIPTION</th>
            <th>Quantity</th>
            <th>PRICE</th>
        </tr>
        <tr>
            <td>NO ITEMS PURCHASED</td>
            <td>N/A</td>
            <td>N/A</td>
            <td class="desc">NO ITEMS PURCHASED</td>
            <td>0</td>
            <td>$0.00</td>
        </tr>
    </table>
        <?php
      }
        ?>

</div>
</div>
<!-- END OF SCROLLABLE DIV -->
</section>
