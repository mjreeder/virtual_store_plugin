<?php
/**
 * Plugin Name: Digital Corps Menu Cleanup
 * Description: Hides and removes unnecessary WordPress menu items; should be "network activated"
 * Version: 1.0
 * Author: Digital Corps, Ball State University.
 */

if( !class_exists('DCVS_Admin_Menu_Simplification') ) {

	class DCVS_Admin_Menu_Simplification {

		public function __construct() {
			add_action('admin_init', array($this, 'admin_init'));
			add_action('init', array($this, 'public_init'));
		}

		//not so fast. this stuff has to be in here and not __construct to ensure that the WP bootstrapping is complete
		public function admin_init(){
			if( !current_user_can('create_sites') ){
				add_action( 'admin_bar_menu', array($this, 'toolbar_cleanup'), 999 );
				add_action( 'admin_bar_menu', array($this, 'toolbar_dashboard_button'), 1 );
				add_action( 'admin_head', array($this, 'toolbar_dashboard_button_styles'), 999 );
				add_action( 'admin_init', array($this, 'menu_cleanup'), 999 );
				add_action( 'load-index.php', array($this, 'dashboard_redirect'));
				add_filter( 'contextual_help', array($this, 'remove_help_drawer'), 999, 3 );
			}
		}

		public function public_init(){
			if( !current_user_can('create_sites') ){
				add_action( 'admin_bar_menu', array($this, 'toolbar_cleanup'), 999 );
				add_action( 'admin_bar_menu', array($this, 'toolbar_dashboard_button'), 1 );
				add_action( 'wp_head', array($this, 'toolbar_dashboard_button_styles'), 999 );
			}
		}

		public function toolbar_dashboard_button($wp_admin_bar){
			$args = array(
				'id'    => 'dcvs-dashboard',
				'title' => 'Virtual Store Dashboard',
				//TODO add the link to the student dashboard here
				'href'  => '',
				'meta'  => array( 'class' => 'dcvs-dashboard-button' )
			);
			$wp_admin_bar->add_node( $args );
		}

		public function toolbar_dashboard_button_styles(){
			?>
			<style type="text/css">
				#wp-admin-bar-dcvs-dashboard {
					background:#EDB867 !important;
				}
				#wp-admin-bar-dcvs-dashboard:hover a.ab-item {
					background:#EDB867 !important;
					color:black !important;
				}
				#wp-admin-bar-dcvs-dashboard:hover a.ab-item::before {
					color:black !important;
				}
				#wp-admin-bar-dcvs-dashboard a.ab-item {
					color:black;
				}
				#wp-admin-bar-dcvs-dashboard > .ab-item::before {
					color:black;
					content: "\f312";
				}
			</style>
			<?php
		}

		//note: as of WP v3.3, it's now called the "toolbar" instead of the "admin bar"
		public function toolbar_cleanup($wp_admin_bar){
			$wp_admin_bar->remove_node( 'wp-logo' );
			$wp_admin_bar->remove_node( 'my-sites' );
			$wp_admin_bar->remove_node( 'comments' );
			$wp_admin_bar->remove_node( 'forms' );
			$wp_admin_bar->remove_node( 'new-content' );
		}

		public function menu_cleanup(){
			//echo '<pre>' . print_r( $GLOBALS[ 'menu' ], TRUE) . '</pre>'; //quick listing of all the menu options
			$slugs = array(
				'index.php', //dashboard
				'separator1', //line under dashboard
				'edit.php', //posts
				'edit-comments.php',
				'gf_edit_forms', //gravity forms
				'plugins.php',
				'users.php',
				'tools.php'
			);
			foreach($slugs as $slug){
				remove_menu_page( $slug );
			}

			$submenuSlugs = array(
				'edit.php?post_type=product'=>array(
					'product_attributes',
					'edit-tags.php?taxonomy=product_tag&amp;post_type=product' //product tags
				),
				'woocommerce'=>array(
					'wc-status', //system status
					'wc-addons', //extensions
					'wc-settings'
				),
				'options-general.php'=>array(
					'options-permalink.php',
					'options-discussion.php',
					'options-writing.php',
					'options-media.php',
					'options-reading.php',
					'easy-google-fonts',
					'svg-support'
				)
			);

			foreach($submenuSlugs as $menu=>$submenus){
				foreach($submenus as $submenu){
					remove_submenu_page($menu, $submenu);
				}
			}
		}

		public function dashboard_redirect(){
			wp_redirect(admin_url('edit.php?post_type=product'));
		}

		public function remove_help_drawer($old_help, $screen_id, $screen ){
			$screen->remove_help_tabs();
			return $old_help;
		}

	}

	new DCVS_Admin_Menu_Simplification();

}