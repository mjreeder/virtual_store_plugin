<?php

if( !class_exists('DCVS_Store_Management') ) {
	class DCVS_Store_Management {
		private $wpdb;
		private $network_site;
		private $student_site_defaults;

		const ADD_USERS_BY_EMAIL_POST_KEY = 'dcvs_new_student_emails';
		const ARCHIVE_STORE_POST_KEY = 'dcvs_archive_store';
		const ARCHIVE_ALL_STORES_POST_KEY = 'dcvs_archive_all';
		const UNARCHIVE_STORE_POST_KEY = 'dcvs_unarchive_store';
		const DELETE_STORE_POST_KEY = 'dcvs_delete_store';

		const DEFAULT_THEME = 'storefront';
		const DEFAULT_PASSWORD = 'password';
		const USER_ASSOCIATION_KEY = 'primary_owner_id';
		const STORE_ASSOCIATION_KEY = 'generated_store_id';

		public static $SUPER_ADMIN_IDs; //not a constant in case of an older version of PHP
		public static $messages;

		function __construct() {
			add_action("admin_init", array($this,"init"));
			add_action("admin_init", array($this,"process_new_users"));
			add_action("admin_init", array($this,"process_store_archival"));
			add_action("admin_init", array($this,"process_store_unarchival"));
			add_action("admin_init", array($this,"process_store_deletion"));
			add_action("admin_menu", array($this, "register_submenus"));
		}

		//not so fast. this stuff has to be in here and not __construct to ensure that the WP bootstrapping is complete
		public function init(){
			global $wpdb;
			$this->wpdb = $wpdb;

			self::$messages = [];

			$this->network_site = get_current_site();

			self::$SUPER_ADMIN_IDs = $this->get_super_admins();
		}

		public function register_submenus(){
			add_submenu_page('dcvs_virtual_store', 'Manage Stores', 'Manage Stores', 'create_sites', 'dcvs_manage_stores', array($this, 'add_manage_stores_page'));
		}

		public function add_manage_stores_page(){
			require_once(__DIR__."/templates/manage_users.php");
		}

		public function process_new_users(){
			if( !current_user_can('create_sites') || !isset($_POST[self::ADD_USERS_BY_EMAIL_POST_KEY]) ){
				return;
			}

			check_admin_referer( self::ADD_USERS_BY_EMAIL_POST_KEY );

			$count = 0;
			$rawEmails = filter_var($_POST[self::ADD_USERS_BY_EMAIL_POST_KEY], FILTER_SANITIZE_STRING);
			$emails = array_filter(preg_split('/[\,\s]/um', $rawEmails));

			if( empty($emails) ){
				return;
			}

			$this->student_site_defaults = $this->get_site_defaults();

			foreach($emails as $email):
				if( $this->register_user_from_email($email) ) {
					$count++;
				} else {
					self::$messages[] = 'Error adding '.$email;
				}
			endforeach;

			self::$messages[] = 'Successfully Added '.$count.' Users';
		}

		private function get_site_defaults(){
			global $wpdb;

			$siteSettings = [
				'public'=>1,
				'blogdescription'=>'Store tagline goes here',
				'template'=>self::DEFAULT_THEME,
				'stylesheet'=>self::DEFAULT_THEME,
				'timezone_string'=>'America/Indiana/Indianapolis'
			];

			//TODO figure out a way to determine this? maybe just a class constant; maybe lookup a specific store by name
			$defaultStoreID = 1;

			$desiredOptions = $wpdb->get_col("SELECT option_name FROM wp_options WHERE `option_name` LIKE '%woocommerce%' and `option_name` NOT LIKE '%transient%'and `option_name` not LIKE '%page_id%'");

			$desiredOptions[] = 'bodhi_svgs_admin_notice_dismissed';

			$options = array_map(function($key) use ($defaultStoreID){
				return get_blog_option($defaultStoreID, $key);
			}, $desiredOptions);

			$options = array_combine($desiredOptions, $options);

			$defaults = array_merge($siteSettings, $options);

			return $defaults;
		}

		public function process_store_archival(){
			if( current_user_can('create_sites') && isset($_POST[self::ARCHIVE_ALL_STORES_POST_KEY]) ){
				check_admin_referer( self::ARCHIVE_ALL_STORES_POST_KEY );

				$count = 0;
				$stores = self::get_active_stores();
				foreach($stores as $store):
					$this->archive_site($store->blog_id);
					$count++;
				endforeach;
				self::$messages[] = 'Successfully Archived '.$count.' stores';
			}

			if( current_user_can('create_sites') && isset($_POST['site_id'], $_POST[self::ARCHIVE_STORE_POST_KEY]) ){
				$siteID = filter_var($_POST['site_id'], FILTER_SANITIZE_NUMBER_INT);
				check_admin_referer( self::ARCHIVE_STORE_POST_KEY.$siteID );
				$this->archive_site($siteID);
				self::$messages[] = 'Successfully Archived Store';
			}
		}

		public function process_store_unarchival(){
			if( current_user_can('create_sites') && isset($_POST['site_id'], $_POST[self::UNARCHIVE_STORE_POST_KEY]) ){
				$siteID = filter_var($_POST['site_id'], FILTER_SANITIZE_NUMBER_INT);
				check_admin_referer( self::UNARCHIVE_STORE_POST_KEY.$siteID );
				$this->unarchive_site($siteID);
				self::$messages[] = 'Successfully Un-Archived Store';
			}
		}

		public function process_store_deletion(){
			if( current_user_can('create_sites') && isset($_POST['site_id'], $_POST[self::DELETE_STORE_POST_KEY]) ){
				$siteID = filter_var($_POST['site_id'], FILTER_SANITIZE_NUMBER_INT);
				check_admin_referer( self::DELETE_STORE_POST_KEY.$siteID );
				$this->delete_site($siteID);
				self::$messages[] = 'Successfully Deleted Store and User';
			}
		}

		private function archive_site($siteID){
			update_archived($siteID, true);
		}

		private function unarchive_site($siteID){
			update_archived($siteID, false);
		}

		private function delete_site($siteID){
			$userID = self::get_user_by_store($siteID);
			wpmu_delete_blog($siteID, true);
			wpmu_delete_user($userID);
		}

		public function register_user_from_email($email){
			if( !filter_var($email, FILTER_VALIDATE_EMAIL) ){
				return false;
			}

			$username = preg_replace('/@.+/', '', $email);
			$user_id = wp_create_user($username, self::DEFAULT_PASSWORD, $email);
			if( is_wp_error($user_id) ){
				return false;
			}

			$storeID = $this->add_store_on_register($user_id);
			$this->finish_site_setup($storeID);

			add_user_to_blog(get_main_network_id(), $user_id,'customer');

			return true;
		}

		private function add_store_on_register($user_id){
			$user = get_userdata($user_id);

			$blogID = wpmu_create_blog($this->network_site->domain,$this->network_site->path.$user->data->user_login, $user->data->user_login."’s Store", $user_id, $this->student_site_defaults);
			add_blog_option($blogID, self::USER_ASSOCIATION_KEY, $user_id); //set this for future use when archiving/deleting
			add_user_meta($user_id, self::STORE_ASSOCIATION_KEY,$blogID);

			$business_id = self::add_business_on_store_creation( $blogID, $user->data->user_login."’s Store" );

			self::add_user_business_relationship_on_business_creation( $user_id, $business_id );

			return $blogID;
		}

		private function add_business_on_store_creation( $blog_id, $business_title ) {
			global $wpdb;

			$business_url = get_site_url($blog_id) . '/';
			$business_budget = dcvs_get_option( 'default_business_money' );

			$wpdb->insert('dcvs_business', ['title'=>$business_title, 'description'=>'', 'money'=>$business_budget, 'url'=>$business_url] );

			return $wpdb->insert_id;
		}

		private function add_user_business_relationship_on_business_creation($user_id, $business_id) {
			global $wpdb;

			$wpdb->insert('dcvs_user_business', ['user_id'=>$user_id, 'business_id'=>$business_id] );
		}

		private function finish_site_setup($blog_id){
			//add all superadmins as admins to the new site
			foreach(self::$SUPER_ADMIN_IDs as $superID):
				add_user_to_blog($blog_id, $superID,'administrator');
			endforeach;

			//use the built-in WooCommerce function to install and setup the pages needed to run WooCommerce (ex: cart, checkout, etc.)
			if( class_exists('WC_Install') ){
				switch_to_blog( $blog_id );
				WC_Install::install();
				WC_Install::create_pages();
				restore_current_blog();
			}
		}

		private function get_super_admins(){
			$supers = get_super_admins(); //these are just usernames; seriously, that's all WP stores to differentiate superadmins...
			$supersWHERE = "user_login='".implode("' OR user_login='", $supers)."'";

			return $this->wpdb->get_col("SELECT id FROM {$this->wpdb->users} WHERE $supersWHERE"); //storing this globally to prevent repeated queries when adding multiple users at once
		}

		public static function get_store_by_user($userID){
			return get_user_meta($userID, self::STORE_ASSOCIATION_KEY, true);
		}

		public static function get_user_by_store($storeID){
			return get_blog_option($storeID, self::USER_ASSOCIATION_KEY);
		}

		public static function get_archived_stores(){
			$sites = array_filter(get_sites(['archived'=>1, 'orderby'=>'path']), function($site){
				return ( DCVS_Store_Management::get_user_by_store($site->blog_id) );
			});
			return $sites;
		}

		public static function get_active_stores(){
			$sites = array_filter(get_sites(['archived'=>0, 'orderby'=>'path']), function($site){
				return ( DCVS_Store_Management::get_user_by_store($site->blog_id) !== false );
			});
			return $sites;
		}

		public static function get_active_users(){
			$stores = self::get_active_stores();
			$users = array_map(function($store){
				return self::get_user_by_store($store->blog_id);
			}, $stores);
			return $users;
		}
	}

	new DCVS_Store_Management();
}
