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
<head>
  <title>Virtual Store</title>
  <!-- CSS -->
  <link href="../assets/css/dashboard.css" rel="stylesheet" type="text/css">
  <link href="../assets/css/budgetBar.css" rel="stylesheet" type="text/css">
  <!-- FONTS -->
  <link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700" rel="stylesheet">
</head>
<body>

  <header class="header">

      <h1>virtual store</h1>

  </header>
  <h1 class="title">Store Feedback</h1>
  <a href="<?php echo dcvs_get_landing_page_url(); ?>" class="backButton"><p>Back to Dashboard</p></a>
  <div class="entries">
  			<ul>
  									<li id="#entry10">
  						<h2>2017-05-03 13:09:59</h2>
  						<dl>
  															<dt>Was it Fun?</dt>
  								<dd><ul><li>Aw hell no</li></ul></dd>
  															<dt>What was the most exciting part?</dt>
  								<dd><ul><li>The Fashion</li><li>The Drawings</li></ul></dd>
  															<dt>User ID</dt>
  								<dd><ul><li>72</li></ul></dd>
  													</dl>
  					</li>
  									<li id="#entry9">
  						<h2>2017-05-03 13:09:55</h2>
  						<dl>
  															<dt>Was it Fun?</dt>
  								<dd><ul><li>Yes</li></ul></dd>
  															<dt>What was the most exciting part?</dt>
  								<dd><ul><li>The Fashion</li></ul></dd>
  															<dt>User ID</dt>
  								<dd><ul><li>72</li></ul></dd>
  													</dl>
  					</li>
  									<li id="#entry8">
  						<h2>2017-05-03 13:09:50</h2>
  						<dl>
  															<dt>Was it Fun?</dt>
  								<dd><ul><li>Yes</li></ul></dd>
  															<dt>What was the most exciting part?</dt>
  								<dd><ul><li>The Fashion</li></ul></dd>
  															<dt>User ID</dt>
  								<dd><ul><li>72</li></ul></dd>
  													</dl>
  					</li>
  							</ul>
  		</div>
</body>
</html>
