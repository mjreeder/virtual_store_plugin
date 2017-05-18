<?php
global $wpdb;
$currentDisplayStudent = $_REQUEST['student_id'];
$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudent));
$user_id = $_REQUEST['user_id'];
$persona_id = $_REQUEST['persona_id'];
$user_persona_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $user_id, $persona_id));
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
<h1 class="title"><?php echo $display_name[0]->display_name ?></h1>
<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id']?>" class="backButton"><p>BACK TO STUDENT INFO</p></a>

<div class="tableWrapper">
    <table class="virtualTable orderTable">
        <tr>
            <th>ITEMS</th>
            <th>DESCRIPTION</th>
            <th>PRICE</th>
        </tr>
        <?php
        if (sizeOf($user_persona_order_history) >= 1) {
        for ($i = 0; $i < sizeOf($user_persona_order_history); ++$i) {
          $wp_blogID = 'wp_'.$blog_id.'_posts';
          $variation_id = unserialize($user_persona_order_history[$i]->items)[0]["item_meta"]["_variation_id"];

          $post = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wp_blogID WHERE ID = %d", $variation_id[0]));

          $post = get_value_from_stdClass($post[0]);
          if(isset($post['post_parent'])){
             $postParentDescriptionSql = $wpdb->get_results($wpdb->prepare("SELECT post_content FROM $wp_blogID WHERE id = %d", $post["post_parent"]));
             if (isset($postParentDescriptionSql[0])) {
               $studentProductDescriptionText = get_value_from_stdClass($postParentDescriptionSql[0])["post_content"];
             }
             else{
               $studentProductDescriptionText = $post['post_content'];
             }
          }
          else{
            $studentProductDescriptionText = '';
          }

          ?>
          <tr>
              <td><?php echo unserialize($user_persona_order_history[$i]->items)[0]["name"];  ?></td>
              <td class="desc"><?php echo $studentProductDescriptionText ?></td>
              <td>$ <?php echo $user_persona_order_history[$i]->cost ?></td>
          </tr>
          <?php
        }
      }
      else{
        ?>
        <tr>
            <td>NO ITEMS PURCHASED</td>
            <td class="desc">NO ITEMS PURCHASED</td>
            <td>$0.00</td>
        </tr>
        <?php
      }
        ?>
    </table>
</div>
</section>
