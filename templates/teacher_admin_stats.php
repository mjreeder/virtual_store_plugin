<?php
global $wpdb;
$currentDisplayStudentID = $_REQUEST['student_id'];
$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudentID));
$user_id = $_REQUEST['user_id'];
$ware_house_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_warehouse_purchase  WHERE user_id = %d', $user_id));
// $purchase_log = $wpdb->get_results($wpdb->prepare("SLECT * FROM dcvs_business_product_purchase WHERE  "))
$items = unserialize(get_value_from_stdClass($ware_house_order_history[0])['items']);

function get_value_from_stdClass($obj){
	$array = get_object_vars($obj);
	reset($array);
	$first_key = key($array);
  return $array;
}
echo "<pre>";
var_dump($items);
echo "</pre>";
// $user_persona_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_business_purchase JOIN dcvs_user_persona ON dcvs_business_purchase.user_persona_id = dcvs_user_persona.id WHERE user_id = %d AND persona_id = %d', $user_id, $persona_id));
// var_dump(unserialize($user_persona_order_history[0]->items));
 ?>
        <main class="admin">

            <section class="comparison">

                <h1 class="title">Ryan Bitzegaio Statistics</h1>

                <div class="tableWrapper">
                    <table class="virtualTable stats">
                        <tr>
                            <th>ITEMS</th>
                            <th>#PURCH</th>
                            <th>#SOLD</th>
                            <th>OLD PRICE</th>
                            <th>NEW PRICE</th>
                            <th>NEW DESC.</th>
                        </tr>
                        <tr>
                          <?php
														for ($j=0; $j < sizeof($ware_house_order_history) ; $j++) {
															$items = unserialize(get_value_from_stdClass($ware_house_order_history[$j])['items']);
															for ($i=0; $i < sizeof($items) ; $i++) {
	                                ?>
	                                <td><?php echo $items[$i]['name']?></td>
	                                <td><?php echo $items[$i]["item_meta"]["_qty"][0]?></td>
	                                <td><?php ?></td>
	                                <td><?php ?><?php echo $items[$i]["item_meta"]["_line_subtotal"][0]?></td>
	                                <td><?php ?></td>
	                                <td class="desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi malesuadxa nibh eu pellentesque interdum. Sed pulvinar orci lacus</td>
	                              </tr>
	                                <?php
	                            }
														}


                          ?>


                    </table>
                </div>


            </section>

        </main>
    </div>


</body>
