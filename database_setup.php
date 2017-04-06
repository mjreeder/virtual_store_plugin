<?php
/**
 * This file is used to setup the database and database changes between versions.
 * Here is the wordpress documentation regarding this
 * https://codex.wordpress.org/Creating_Tables_with_Plugins
 *
 * here is some additional info on how to use wpdb
 * https://codex.wordpress.org/Class_Reference/wpdb
 */
defined( 'ABSPATH' ) or die( 'invalid access' );
define("DCVS_DATABASE_VERSION", 0.45);

$dcvs_current_version = dcvs_get_option("dcvs_database_version");
if($dcvs_current_version != DCVS_DATABASE_VERSION){
    require_once  ABSPATH."/wp-admin/includes/upgrade.php";
    $charset_collate = $wpdb->get_charset_collate();
    if($dcvs_current_version < 0.1){
        $personaTable = "CREATE TABLE dcvs_persona(
            id BIGINT(10) NOT NULL AUTO_INCREMENT,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            money DOUBLE(8,2) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";
        dbDelta($personaTable);

        $userPersonaTable = "CREATE TABLE dcvs_user_persona(
          id BIGINT(10) NOT NULL AUTO_INCREMENT,
          user_id BIGINT(10) NOT NULL,
          persona_id BIGINT(10),
          PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($userPersonaTable);

        $currentPersonaTable = "CREATE TABLE dcvs_current_persona(
            user_id BIGINT(10) NOT NULL,
            persona_id BIGINT(10)
        ) $charset_collate;";

        dbDelta($currentPersonaTable);

        $businessTable = "CREATE TABLE dcvs_business(
          id BIGINT(10) NOT NULL AUTO_INCREMENT,
          title VARCHAR(400) NOT NULL,
          description TEXT NOT NULL,
          money DOUBLE(8,2) NOT NULL,
          url VARCHAR(500) NOT NULL,
          PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta($businessTable);

        $userBusinessTable = "CREATE TABLE dcvs_user_business(
            user_id BIGINT(10) NOT NULL,
            business_id BIGINT(10) NOT NULL
        ) $charset_collate;";
        dbDelta($userBusinessTable);

        $warehousePurchaseTable = "CREATE TABLE dcvs_warehouse_purchase(
            id BIGINT(10) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(10) NOT NULL,
            cost DOUBLE(8,2) NOT NULL,
            items TEXT NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($warehousePurchaseTable);

        $optionsTable = "CREATE TABLE dcvs_options(
            id BIGINT(10) NOT NULL AUTO_INCREMENT,
            option_key VARCHAR(200) NOT NULL,
            option_value LONGTEXT NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($optionsTable);

    } if($dcvs_current_version < 0.25){
        $purchaseTable = "CREATE TABLE dcvs_business_purchase(
            id BIGINT(10) NOT NULL AUTO_INCREMENT,
            user_persona_id BIGINT(10) NOT NULL,
            business_id BIGINT(10) NOT NULL,
            items TEXT NOT NULL,
            cost DOUBLE(8,2) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($purchaseTable);
    } if($dcvs_current_version < 0.37){
       $wpdb->query("DROP TABLE dcvs_current_persona;");
      $currentPersona = "CREATE TABLE dcvs_current_persona(
          user_id BIGINT(10) NOT NULL,
          current_persona_id BIGINT(10)
      ) $charset_collate;";

      dbDelta($currentPersona);
    } if($dcvs_current_version < 0.45){
        $warehouseBusinessProductTable = "CREATE TABLE dcvs_warehouse_business_product(
          business_id BIGINT(10) NOT NULL,
          warehouse_product_id BIGINT(10) NOT NULL,
          business_product_id BIGINT(10) NOT NULL
      ) $charset_collate;";
        dbDelta($warehouseBusinessProductTable);
    }

    dcvs_set_option("dcvs_database_version", DCVS_DATABASE_VERSION);
}
