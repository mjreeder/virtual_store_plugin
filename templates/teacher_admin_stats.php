<?php
global $wpdb;
$currentDisplayStudentID = $_REQUEST['student_id'];
$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudentID));
$user_id = $_REQUEST['user_id'];
$ware_house_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_warehouse_purchase WHERE user_id = %d', $user_id));
function get_value_from_stdClass($obj){
	$array = get_object_vars($obj);
	reset($array);
	$first_key = key($array);
  return $array;
}
$orderMap = array();
if($ware_house_order_history){
	for ($j=0; $j < sizeof($ware_house_order_history) ; $j++) {
		$items = unserialize(get_value_from_stdClass($ware_house_order_history[$j])['items']);
		for ($i=0; $i < sizeof($items) ; $i++) {
			$id = $items[$i]['item_meta']['_variation_id'][0];
			if(isset($orderMap[0][$id])){
				$orderMap[0][$id]["item_meta"]["_qty"][0] += $items[$i]['item_meta']["_qty"][0];
				$orderMap[0][$id]["item_meta"]["_line_subtotal"][0] += $items[$i]["item_meta"]["_line_subtotal"][0];
			}
			else{
				$orderMap[] = array($id => $items[$i]);
			}
		}
	}
}
 ?>
        <main class="admin">

            <section class="comparison">

                <h1 class="title"><?php echo get_value_from_stdClass($display_name[0])['display_name'] ?></h1>
								<a href="<?php echo get_site_url().'/wp-admin/admin.php?page=dcvs_teacher&student_id='. $_REQUEST['student_id']?>"><p>Back to Student Info</p></a>
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
													$terms = $wpdb->get_results("SELECT DISTINCT wp_term_taxonomy.taxonomy FROM wp_terms JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id JOIN wp_termmeta ON wp_terms.term_id = wp_termmeta.term_id
														WHERE wp_terms.term_id IN (SELECT term_id FROM wp_term_taxonomy WHERE taxonomy IN (SELECT CONCAT('pa_',attribute_name) FROM wp_woocommerce_attribute_taxonomies))", ARRAY_A);
														if(sizeof($orderMap) >=1){
															for ($i=0; $i <sizeof($orderMap) ; $i++) {
																		$itemInformation = reset($orderMap[$i]);
																		$id = $itemInformation["item_meta"]['_variation_id'][0];
																		$productInfo = $wpdb->get_results($wpdb->prepare("SELECT price, number_bought FROM dcvs_business_product_price JOIN dcvs_warehouse_business_product ON dcvs_business_product_price.business_product_id=dcvs_warehouse_business_product.business_product_id WHERE warehouse_product_id = %d", $id));
																		if(isset($productInfo[0])){
																			$saleInfo = get_value_from_stdClass($productInfo[0]);
																			$productDescription = '';
																			for ($j=0; $j <sizeof($terms) ; $j++) {
																				$productDescription = $productDescription.' '.$itemInformation[$terms[$j]["taxonomy"]];
																			}
																		}
																		else{
																			$saleInfo = NUll;
																		}
																		?>
																		 <td><?php echo $productDescription.' '.$itemInformation['name'] ;?></td>
																		 <td><?php echo $itemInformation["item_meta"]["_qty"][0]; ?></td>
																		 <td><?php
																		 if($saleInfo != NULL){
																			 echo $saleInfo['number_bought'];
																		 }
																		 else{
																			 echo '';
																		 }
																		 ?></td>
																		 <td><?php echo $itemInformation["item_meta"]['_line_subtotal'][0]; ?></td>
																		 <td><?php
																		 if($saleInfo != NULL){
																			 echo $saleInfo['price'];
																		 }
																		 else{
																			 echo '';
																		 }
																		 ?></td>

																		 <td class="desc"><?php echo "derp"; ?></td>
																	</tr>
																		<?php
															}
														}
														else{
															?>
															<td>No Activity</td>
															<td>No Activity</td>
															<td>No Activity</td>
															<td>No Activity</td>
															<td>No Activity</td>
															<td>No Activity</td>
															<?php
														}

													 ?>

                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
