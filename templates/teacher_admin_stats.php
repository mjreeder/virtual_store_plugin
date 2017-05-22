<?php
global $wpdb;
$currentDisplayStudentID = $_REQUEST['student_id'];
$display_name = $wpdb->get_results($wpdb->prepare('SELECT display_name FROM wp_users WHERE id = %d', $currentDisplayStudentID));
$ware_house_order_history = $wpdb->get_results($wpdb->prepare('SELECT items, cost FROM dcvs_warehouse_purchase WHERE user_id = %d', $currentDisplayStudentID));
function get_value_from_stdClass($obj)
{
    $array = get_object_vars($obj);
    reset($array);
    $first_key = key($array);
    return $array;
}

$second = array_slice(get_blogs_of_user($currentDisplayStudentID), 1, 1);

$blog_id = get_value_from_stdClass($second[0])["userblog_id"];

$orderMap = array();
if ($ware_house_order_history) {

    for ($j = 0; $j < sizeof($ware_house_order_history); $j++) {
        $items = unserialize(get_value_from_stdClass($ware_house_order_history[$j])['items']);
        for ($i = 0; $i < sizeof($items); $i++) {
            $id = $items[$i]['item_meta']['_variation_id'][0];
            if (isset($orderMap[0])) {
                $found = false;
                for ($z = 0; $z < sizeof($orderMap); $z++) {
                    if (isset($orderMap[$z][$id])) {
                        $found = true;
                        $orderMap[$z][$id]["item_meta"]["_qty"][0] += $items[$i]['item_meta']["_qty"][0];
                        $orderMap[$z][$id]["item_meta"]["_line_subtotal"][0] += $items[$i]["item_meta"]["_line_subtotal"][0];
                    }
                }
                if ($found == false) {
                    $orderMap[] = array($id => $items[$i]);
                }
            } else {
                $orderMap[] = array($id => $items[$i]);
            }
        }
    }
}
?>
<main class="admin">

    <section class="comparison">

        <div class="scrollable">

            <h1 class="title"><?php echo get_value_from_stdClass($display_name[0])['display_name'] ?></h1>
            <a href="<?php echo get_site_url() . '/wp-admin/admin.php?page=dcvs_teacher&student_id=' . $_REQUEST['student_id'] ?>"
               class="backButton"><p>BACK TO STUDENT INFO</p></a>
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
                        //get user business ID
                        $businessID = $wpdb->get_var($wpdb->prepare("SELECT business_id FROM dcvs_user_business WHERE user_id = %d",[$currentDisplayStudentID]));
                        if (sizeof($orderMap) >= 1){

                        for ($i = 0;
                        $i < sizeof($orderMap);
                        $i++) {
                        $itemInformation = reset($orderMap[$i]);

                        $id = $itemInformation["item_meta"]['_variation_id'][0];
                        $productInfo = $wpdb->get_results($wpdb->prepare("SELECT price, number_bought FROM dcvs_business_product_price JOIN dcvs_warehouse_business_product ON dcvs_business_product_price.business_product_id=dcvs_warehouse_business_product.business_product_id WHERE warehouse_product_id = %d AND dcvs_business_product_price.business_id = %d", [$id, $businessID]));
                        $wp_blogID = 'wp_' . $blog_id . '_posts';

                        $post = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wp_blogID WHERE ID = %d", $id[0]));

                        $post = get_value_from_stdClass($post[0]);

                        if (isset($post['post_parent'])) {

                            $postParentDescriptionSql = $wpdb->get_results($wpdb->prepare("SELECT post_content FROM $wp_blogID WHERE id = %d", $post["post_parent"]));
                            if (isset($postParentDescriptionSql[0])) {

                                $studentProductDescriptionText = get_value_from_stdClass($postParentDescriptionSql[0])["post_content"];
                            } else {

                                $studentProductDescriptionText = $post['post_content'];
                                $studentProductDescriptionSql = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wp_blogID JOIN dcvs_warehouse_business_product ON dcvs_warehouse_business_product.business_product_id=$wp_blogID.id WHERE warehouse_product_id = %d AND dcvs_warehouse_business_product.business_id = %d", [$id, $businessID]));
                                if (isset($studentProductDescriptionSql[0])) {
                                    $studentProductDescriptionArray = get_value_from_stdClass($studentProductDescriptionSql[0]);

                                    if (isset($studentProductDescriptionArray["post_parent"])) {
                                        $postParentDescriptionSql = $wpdb->get_results($wpdb->prepare("SELECT post_content FROM $wp_blogID WHERE id = %d", $studentProductDescriptionArray["post_parent"]));
                                        $studentProductDescriptionText = get_value_from_stdClass($postParentDescriptionSql[0])["post_content"];
                                    } else {

                                        $studentProductDescriptionText = $studentProductDescriptionArray['post_content'];
                                    }
                                }
                            }

                        }

                        $userProductDescription = $wpdb->get_results($wpdb->prepare("SELECT price, number_bought FROM dcvs_business_product_price JOIN dcvs_warehouse_business_product ON dcvs_business_product_price.business_product_id=dcvs_warehouse_business_product.business_product_id WHERE warehouse_product_id = %d AND dcvs_business_product_price.business_id = %d", [$id, $businessID]));

                        $productDescription = '';
                        $terms = $wpdb->get_results("SELECT DISTINCT wp_term_taxonomy.taxonomy FROM wp_terms JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id JOIN wp_termmeta ON wp_terms.term_id = wp_termmeta.term_id
																		WHERE wp_terms.term_id IN (SELECT term_id FROM wp_term_taxonomy WHERE taxonomy IN (SELECT CONCAT('pa_',attribute_name) FROM wp_woocommerce_attribute_taxonomies))", ARRAY_A);
                        $global_attributes = array();
                        for ($j = 0; $j < sizeof($terms); $j++) {

                            if (isset($itemInformation[$terms[$j]["taxonomy"]])) {
                                $productDescription = $productDescription . ' ' . $itemInformation[$terms[$j]["taxonomy"]];
                                $global_attributes[] = $terms[$j]['taxonomy'];
                            }
                        }
                        $item_meta = $itemInformation["item_meta"];
                        $non_custom_attributes = array('_qty', '_tax_class', '_product_id', '_variation_id', '_line_subtotal', '_line_total', '_line_subtotal_tax', '_line_tax', '_line_tax_data');
                        $custom_description = "";
                        foreach ($item_meta as $key => $value) {
                            if (!in_array($key, $non_custom_attributes) && !in_array($key, $global_attributes)) {
                                $custom_description = $custom_description . ' ' . $value[0];
                            }
                        }
                        $productDescription = $productDescription . ' ' . $custom_description;

                        $saleInfo = null;
                        for ($w = 0; $w < sizeof($productInfo); $w++) {
                            $saleInfo = [];
                            if (isset($productInfo[0])) {
                                $saleInfo[] = get_value_from_stdClass($productInfo[$w]);
                            } else {
                                $saleInfo = NUll;
                            }
                        }
                        // echo "<pre>";
                        // var_dump($saleInfo);
                        // echo "</pre>";
                        // die();
                        ?>
                        <td><?php echo $productDescription . ' ' . $itemInformation['name']; ?></td>
                        <td><?php echo $itemInformation["item_meta"]["_qty"][0]; ?></td>
                        <td><?php

                            if (isset($saleInfo)) {

                                $numberBought = 0;
                                for ($s = 0; $s < sizeof($saleInfo); $s++) {
                                    $numberBought += $saleInfo[$s]["number_bought"];
                                }
                                ?>
                                <span><?php echo $numberBought; ?></span>
                                <?php
                            } else {
                                echo 0;
                            }
                            ?></td>
                        <td><?php echo $itemInformation["item_meta"]['_line_subtotal'][0] / $itemInformation["item_meta"]["_qty"][0]; ?></td>
                        <td class="own"><?php
                            if (isset($saleInfo)) {
                                ?>
                                <span><?php echo $saleInfo[sizeof($saleInfo) - 1]["price"]; ?></span>
                                <?php
                                if (sizeof($saleInfo) > 1) {
                                    ?>
                                    <span>*</span>
                                    <span class="price_change">
																						<?php
                                                                                        for ($r = 0; $r < sizeof($saleInfo); $r++) {
                                                                                            ?>
                                                                                            <?php echo $saleInfo[$r]["number_bought"] . " bought at ";
                                                                                            echo "$" . $saleInfo[$r]["price"] . "<br>"; ?>
                                                                                            <?php
                                                                                        }
                                                                                        ?>
																						</span>
                                    <?php
                                }
                            } else {
                                echo "not yet sold";
                            }
                            ?></td>

                        <td class="desc"><?php echo $studentProductDescriptionText; ?></td>
                    </tr>

                    <?php

                    }
                    }
                    else {
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
        </div>
        <!-- END OF SCROLLABLE DIV -->
    </section>
</main>
</div>
</body>
