<style type="text/css">
	.store-list {
		width:50%;
		float: left;
	}
	textarea {
		width:200px;
		height:200px;
	}
</style>
<h1>matt & corey should put some stuff here</h1>
<div class="messages">
	<?php foreach(DCVS_Store_Management::$messages as $message): ?>
		<p><?php echo $message; ?></p>
	<?php endforeach; ?>
</div>
<h2>Add Users</h2>
<p>Paste a list of student email addresses here. Separate users with new lines or commas.</p>
<form action="" method="post">
	<textarea name="<?php echo DCVS_Store_Management::ADD_USERS_BY_EMAIL_POST_KEY; ?>"></textarea>
	<br>
	<input type="submit" value="Add Users"/>
</form>
<hr>
<div class="active store-list">
	<h2>Active Users’ Stores</h2>
	<form action="" class="warning" method="post"><input type="submit" name="<?php echo DCVS_Store_Management::ARCHIVE_ALL_STORES_POST_KEY; ?>" value="Archive All Stores"/></form>
	<ul>
		<?php foreach(DCVS_Store_Management::get_active_stores() as $site):
			$store = get_blog_details($site->blog_id);
			$user = get_userdata(DCVS_Store_Management::get_user_by_store($site->blog_id));
			?>
			<li>
				<h4><?php echo $user->user_email; ?> | <?php echo $store->blogname; ?> | Created: <?php echo date('F Y',strtotime($store->registered)); ?></h4>
				<form action="" class="warning" method="post"><input type="hidden" name="site_id" value="<?php echo $site->blog_id; ?>"/><input type="submit" name="<?php echo DCVS_Store_Management::ARCHIVE_STORE_POST_KEY; ?>" value="Archive Store"/></form>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<div class="archived store-list">
	<h2>Archived Users’ Stores</h2>
	<ul>
		<?php foreach(DCVS_Store_Management::get_archived_stores() as $site):
			$store = get_blog_details($site->blog_id);
			$user = get_userdata(DCVS_Store_Management::get_user_by_store($site->blog_id));
		?>
			<li>
				<h4><?php echo $user->user_email; ?> | <?php echo $store->blogname; ?> | Created: <?php echo date('F Y',strtotime($store->registered)); ?></h4>
				<form action="" class="warning" method="post"><input type="hidden" name="site_id" value="<?php echo $site->blog_id; ?>"/><input type="submit" name="<?php echo DCVS_Store_Management::UNARCHIVE_STORE_POST_KEY; ?>" value="Un-Archive Store"/></form>
				<form action="" class="warning" method="post"><input type="hidden" name="site_id" value="<?php echo $site->blog_id; ?>"/><input type="submit" name="<?php echo DCVS_Store_Management::DELETE_STORE_POST_KEY; ?>" value="Delete Store and User"/></form>
			</li>
		<?php endforeach; ?>
	</ul>
</div>