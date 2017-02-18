<?php

// include 'purchase_functions.php'; needs to be added to files that utilize purchasing

function dcvs_insert_warehouse_purchase($user_id, $cost, $items){
    global $wpdb;
    $end_date = dcvs_get_option("warehouse_end_date");
    $date = date('Y-m-d H:i:s');
    if($end_date >= $date){
        $wpdb->insert("dcvs_warehouse_purchase", ["user_id"=>$user_id, "cost"=>$cost, "items"=>$items]);
    } else{
        echo("invalid shopping time");
    }
}

function dcvs_insert_business_purchase($user_persona_id, $business_id, $items, $cost){
    global $wpdb;
    if(dcvs_user_persona_can_purchase($user_persona_id, $cost)){
        $wpdb->insert("dcvs_business_purchase", ["user_persona_id"=>$user_persona_id, "business_id"=>$business_id, "items"=>$items,
            "cost"=>$cost]);
    }else{
        echo("Purchase failed, insufficient funds.");
    }
}