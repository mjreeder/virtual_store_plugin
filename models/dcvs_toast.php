<?php

if( !class_exists('DCVS_Toast') ) {

	class DCVS_Toast {

		function __construct() {
			add_action("admin_init", array($this,"init"));
		}

		public function init() {
		}

		public static function create_new_toast( $message, $error = false ) {
			if (!$error) {
				$class = 'toastSuccess';
			} else {
				$class = 'toastError';
			}

			ob_start();
			?>

			<link href="<?php echo plugins_url( 'assets/css/toast.css', dirname(__FILE__)); ?>" rel="stylesheet" type="text/css">
			<div id="toast" class="<?php echo $class; ?>"><?php echo $message; ?></div>

			<?php
			return ob_get_clean();
		}

	}

	new DCVS_Toast();

}