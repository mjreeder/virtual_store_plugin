<?php
add_action("user_register", "dcvs_add_store_on_register");

function dcvs_add_store_on_register($user_id){
	$meta = get_user_meta($user_id);

	wpmu_create_blog('localhost','/virtual_store/robotblog','Robot-Generated Blog',$user_id);

	//http://wordpress.stackexchange.com/a/73951
	//fires hook: wpmu_new_blog
	//lots of stuff here: https://markwilkinson.me/2014/01/activate-wordpress-plugins-site-creation-using-multisite/
	//http://gregorygrubbs.com/wordpress/how-to-give-wpmu-blogs-default-properties-theme-and-pages/
	//https://premium.wpmudev.org/forums/topic/firing-actions-after-create-a-new-blog-using-pro-sites
	//https://developer.wordpress.org/reference/hooks/wpmu_activate_blog/
}