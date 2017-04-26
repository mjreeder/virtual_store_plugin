<?php

?>

<section class="users">
	<h1 class="title">Create Users</h1>

	<div class="messages">
		<?php foreach(DCVS_Store_Management::$messages as $message): ?>
			<p><?php echo $message; ?></p>
		<?php endforeach; ?>
	</div>

	<div class="addUsers">
		<h2 class="subTitle">add users</h2>
		<p>paste a list of student email addresses here, separated by a comma or a new line.</p>
		<form action="" method="post">
			<?php wp_nonce_field( DCVS_Store_Management::ADD_USERS_BY_EMAIL_POST_KEY ); ?>
			<textarea name="<?php echo DCVS_Store_Management::ADD_USERS_BY_EMAIL_POST_KEY; ?>" rows="8" cols="80"></textarea>
			<button type="submit" class="saveButton add">ADD</button>
		</form>

	</div>
	<div class="userLists">
		<section class="userLeft">
			<h2 class="subTitle">active users</h2>
			<form action="" class="warning" method="post" onsubmit="return confirm('Archive All Stores?');">
				<?php wp_nonce_field( DCVS_Store_Management::ARCHIVE_ALL_STORES_POST_KEY ); ?>
				<button type="submit" name="<?php echo DCVS_Store_Management::ARCHIVE_ALL_STORES_POST_KEY; ?>">ARCHIVE ALL</button>
			</form>
			<ul>
				<?php foreach(DCVS_Store_Management::get_active_stores() as $site):
					$store = get_blog_details($site->blog_id);
					$user = get_userdata(DCVS_Store_Management::get_user_by_store($site->blog_id));
					?>
					<li>
						<p><?php echo $user->user_email; ?></p>
						<p><?php echo $store->blogname; ?></p>
						<p>Created: <?php echo date('F Y',strtotime($store->registered)); ?></p>
						<form action="" class="warning" method="post" onsubmit="return confirm('Archive <?php echo $store->blogname ?>?');">
							<?php wp_nonce_field( DCVS_Store_Management::ARCHIVE_STORE_POST_KEY.$site->blog_id ); ?>
							<input type="hidden" name="site_id" value="<?php echo $site->blog_id; ?>"/>
							<button type="submit" name="<?php echo DCVS_Store_Management::ARCHIVE_STORE_POST_KEY; ?>">ARCHIVE</button>
						</form>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<section class="userRight">
			<h2 class="subTitle">archived users</h2>
			<ul>
				<?php foreach(DCVS_Store_Management::get_archived_stores() as $site):
					$store = get_blog_details($site->blog_id);
					$user = get_userdata(DCVS_Store_Management::get_user_by_store($site->blog_id));
					?>
					<li>
						<p><?php echo $user->user_email; ?></p>
						<p><?php echo $store->blogname; ?></p>
						<p>Created: <?php echo date('F Y',strtotime($store->registered)); ?></p>
						<form action="" class="warning" method="post" onsubmit="return confirm('Un-Archive <?php echo $store->blogname ?>?');">
							<?php wp_nonce_field( DCVS_Store_Management::UNARCHIVE_STORE_POST_KEY.$site->blog_id ); ?>
							<input type="hidden" name="site_id" value="<?php echo $site->blog_id; ?>"/>
							<button type="submit" name="<?php echo DCVS_Store_Management::UNARCHIVE_STORE_POST_KEY; ?>">UN-ARCHIVE</button>
						</form>
						<form action="" class="warning" method="post" onsubmit="return confirm('Delete <?php echo $store->blogname ?>?');">
							<?php wp_nonce_field( DCVS_Store_Management::DELETE_STORE_POST_KEY.$site->blog_id ); ?>
							<input type="hidden" name="site_id" value="<?php echo $site->blog_id; ?>"/>
							<button type="submit" name="<?php echo DCVS_Store_Management::DELETE_STORE_POST_KEY; ?>"><img src="<?php echo plugins_url("../assets/images/trash.svg", __FILE__); ?>" alt=""></button>
						</form>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	</div>

</section>
