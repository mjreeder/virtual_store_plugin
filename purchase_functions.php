<?php

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

function dcvs_delete_persona_business_purchases($user_persona_id){
    // deletes all purchases for specific persona from business purchase table
    global $wpdb;
    $purchases = $wpdb->get_results("SELECT * FROM dcvs_business_purchase WHERE user_persona_id='".esc_sql($user_persona_id)."'");
    if(sizeof($purchases) != 0) {
        $wpdb->delete("dcvs_business_purchase", array("user_persona_id"=>$user_persona_id));
    }else{
        echo("User persona has not made any business purchases");
    }
}

function dcvs_delete_user_warehouse_purchases($user_id){
    // deletes all purchases for specific user from warehouse purchase table
    global $wpdb;
    $purchases = $wpdb->get_results("SELECT * FROM dcvs_warehouse_purchase WHERE user_id='".esc_sql($user_id)."'");
    if(sizeof($purchases) != 0) {
        $wpdb->delete("dcvs_warehouse_purchase", array("user_id"=>$user_id));
    }else{
        echo("User has not made any warehouse purchases");
    }
}