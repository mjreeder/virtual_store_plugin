<?php
global $wpdb;
$currentDisplayStudent = $_REQUEST['student_id'];
$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudent));
$user_id = $_REQUEST['user_id'];
$persona_id = $_REQUEST['persona_id'];
$user_persona_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $user_id, $persona_id));
 ?>
<section class="orderHistory" id='history'>
<h1 class="title"><?php echo $display_name[0]->display_name ?></h1>

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
          ?>
          <tr>
              <td><?php echo $user_persona_order_history[$i]->items ?></td>
              <td class="desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi malesuadxa nibh eu pellentesque interdum. Sed pulvinar orci lacus</td>
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
