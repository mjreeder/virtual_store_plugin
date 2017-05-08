<?php
require_once __DIR__.'/../../../../wp-blog-header.php';
global $wpdb;
if ( !is_user_logged_in() ) {
    wp_redirect( get_site_url() . '/wp-admin' );
    exit;
}

$current_user_id = get_current_user_id();
if(!filter_var($current_user_id, FILTER_VALIDATE_INT) || $current_user_id == 0){
    wp_die("invalid logged in user");
}

?>
<!DOCTYPE html>
<html>
<head></head>
<body>
hello
</body>
</html>
