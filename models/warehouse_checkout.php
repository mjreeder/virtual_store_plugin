<?php

if (!class_exists('WarehouseCheckout')) {

    class WarehouseCheckout {

        public function __construct() {
            add_action('init', array($this, 'init'));
        }

        public function init() {
	        add_action( "woocommerce_checkout_order_processed", array( $this, "dcvs_export_cart" ) );
        }

	    function dcvs_export_cart($order_id) {
		    self::dcvs_export_attributes();
		    self::dcvs_export_variations();
		    self::dcvs_export_products();
	    }

	    function dcvs_export_attributes() {

	    }

	    function dcvs_export_variations() {
		    
	    }

	    function dcvs_export_products() {

	    }

    }

	new WarehouseCheckout();

}