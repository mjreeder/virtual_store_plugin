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

switch_to_blog(1);

$shopping_evaluation_id = 2;
$storeFieldIdentifier = 12;

$form = GFAPI::get_form($shopping_evaluation_id);

$args = array(
	'field_filters' => array(
		array(
			'key' => $storeFieldIdentifier,
			'value' => DCVS_Store_Management::get_store_by_user($current_user_id)
		)
	)
);
$entries = GFAPI::get_entries($shopping_evaluation_id, $args);

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
			<?php
			echo "<pre>";
			var_dump($form['fields']);
			echo "</pre>";
			foreach($entries as $entry): ksort($entry); ?>
				<li id="#entry<?= $entry['id']; ?>">
					<h2><?= $entry['date_created']; ?></h2>
					<dl>
						<?php foreach($form['fields'] as $question): ?>
							<dt><?= $question->label; ?></dt>
							<dd><?= dcvs_get_answers_based_on_question_id($entry, (string) $question->id); ?></dd>
						<?php endforeach; ?>
					</dl>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</body>
</html>
