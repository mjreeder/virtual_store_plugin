<?php
$SUPER_ADMIN_IDs = [];

add_action("user_register", "dcvs_add_store_on_register");
add_action("init", "dcvs_get_super_admins");

function dcvs_add_store_on_register($user_id){
	global $SUPER_ADMIN_IDs;
	$user = get_userdata($user_id);

	$blogID = wpmu_create_blog('localhost','/virtual_store/'.date('Y').'/'.$user->data->user_login, $user->data->user_login."’s Store", $user_id);
	add_blog_option($blogID, 'primary_owner', $user_id); //set this for future use when archiving/deleting
	add_user_meta($user_id, 'store_id',$blogID);

	foreach($SUPER_ADMIN_IDs as $superID):
		add_user_to_blog($blogID, $superID,'administrator');
	endforeach;

	//http://wordpress.stackexchange.com/a/73951
	//fires hook: wpmu_new_blog
	//lots of stuff here: https://markwilkinson.me/2014/01/activate-wordpress-plugins-site-creation-using-multisite/
	//http://gregorygrubbs.com/wordpress/how-to-give-wpmu-blogs-default-properties-theme-and-pages/
	//https://premium.wpmudev.org/forums/topic/firing-actions-after-create-a-new-blog-using-pro-sites
	//https://developer.wordpress.org/reference/hooks/wpmu_activate_blog/
}

function dcvs_get_super_admins(){
	global $wpdb, $SUPER_ADMIN_IDs;

	$supers = get_super_admins(); //these are just usernames; seriously, that's all WP stores to differentiate superadmins...
	$supersWHERE = "user_login='".implode("' OR user_login='", $supers)."'";

	$SUPER_ADMIN_IDs = $wpdb->get_col("SELECT id FROM $wpdb->users WHERE $supersWHERE"); //storing this globally to prevent repeated queries when adding multiple users at once
}