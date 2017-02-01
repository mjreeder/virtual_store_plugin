<?php

if (!class_exists('WarehouseCheckout')) {
    class WarehouseCheckout {

        public function __construct() {
            add_action('init', array($this, 'init'));
        }

        public function init() {

        }
    }
	
	new WarehouseCheckout();

}