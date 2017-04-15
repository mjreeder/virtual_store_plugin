<?php

$user_message = "";

if($_SERVER['REQUEST_METHOD']=="POST" && isset($_REQUEST['submit'])) {

	if ($_REQUEST['submit'] == "SAVE") {
		$name = $_REQUEST['name'];
		$description = $_REQUEST['description'];
		$user_message = dcvs_insert_new_category($name, $description);
	} else if ($_REQUEST['submit'] == "UPDATE") {
		$category_id = $_REQUEST['category_id'];
		$name = $_REQUEST['name'];
		$description = $_REQUEST['description'];
		$user_message = dcvs_update_category( $category_id, $name, $description );
	} else if($_REQUEST['submit'] == "DELETE") {
		$category_id = $_REQUEST['category_id'];
		$user_message = dcvs_delete_category( $category_id );
	}
}

$pencil_image = plugins_url( 'assets/images/pencil.svg', dirname(__FILE__));
$trash_image = plugins_url( 'assets/images/trash.svg', dirname(__FILE__));

$categories = dcvs_get_all_categories();

?>

<script>
	$(document).ready(function() {

		$('#createModal').hide();

		$('#createNew').click(function () {

			$('#createModal').show();

			$('#backdrop').show();

		});

	});

	//	http://stackoverflow.com/questions/1403615/use-jquery-to-hide-a-div-when-the-user-clicks-outside-of-it
	$(document).mouseup(function (e) {
		var createModal = $("#createModal");
		var editModal = $("#editModal");

		var clickedOutsideCreateModal = !createModal.is(e.target) && createModal.has(e.target).length === 0;
		var clickedOutsideEditModal = !editModal.is(e.target) && editModal.has(e.target).length === 0;

		if (clickedOutsideCreateModal) {
			createModal.hide();
			if (clickedOutsideEditModal) {
				$('#backdrop').hide();
			}
		}

		if (clickedOutsideEditModal) {
			editModal.hide();
			if (clickedOutsideCreateModal) {
				$('#backdrop').hide();
			}
		}
	});

	function editCategory(categoryID, name, description) {
		$('#category_id').val(categoryID);
		$('#name').val(name);
		$('#description').val(description);
		$('#editModal').show();
		$('#backdrop').show();
	}

</script>

<section class="manage">

	<div>
		<h1 class="title">Manage Categories</h1>
		<button class="headerButton createNew" id="createNew">CREATE NEW</button>
		<label><?php echo $user_message; ?></label>
	</div>

	<section class="createModal" id="createModal">
		<h1>CREATE NEW</h1>
		<form action="" method="post">

			<input type="hidden" name="page" value="dcvs_teacher">
			<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
			<input type="hidden" name="section" value="categories">

			<input type="text" name="name" placeholder="name">
			<textarea rows="5" cols="36" name="description" placeholder="description"></textarea>
			<input type="submit" name="submit" value="SAVE">

		</form>
	</section>

	<section class="createModal" id="editModal">
		<h1>Edit Category</h1>
		<form action="" method="post">

			<input type="hidden" name="page" value="dcvs_teacher">
			<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
			<input type="hidden" name="section" value="categories">
			<input type="hidden" name="category_id" value="" id="category_id">

			<input type="text" name="name" placeholder="name" id="name">
			<textarea rows="5" cols="36" name="description" placeholder="description" id="description"></textarea>
			<input type="submit" name="submit" value="UPDATE">

		</form>
	</section>

	<div class="tableWrapper">
		<table class="virtualTable orderTable">
			<tr>
				<th>TITLE</th>
				<th>DESCRIPTION</th>
				<th></th>
				<th></th>
			</tr>
			<?php
			foreach ($categories as $category) {
				$category_id = $category['id'];
				$category_name = $category['name'];
				$category_description = $category['description'];
			?>

				<tr>
					<td><?php echo $category_name; ?></td>
					<td class="desc"><?php echo $category_description; ?></td>
					<td><img src="<?php echo $pencil_image; ?>" alt="edit category button" onclick="editCategory('<?php echo $category_id ?>', '<?php echo $category_name ?>', '<?php echo $category_description ?>')"></td>
					<td>
						<form action="" method="post">
							<input type="hidden" name="page" value="dcvs_teacher">
							<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
							<input type="hidden" name="section" value="categories">
							<input type="hidden" name="category_id" value="<?php echo $category_id ?>">

							<input type="image" src="<?php echo $trash_image; ?>" alt="delete category button" name="submit" value="DELETE" onclick="this.form.submit()">
						</form>
					</td>
				</tr>

			<?php
			}
			?>
		</table>
	</div>

</section>

<?php

function dcvs_get_category($name){
	global $wpdb;

	$result = $wpdb->get_var("SELECT id FROM dcvs_category WHERE name='".esc_sql($name)."'");
	return $result;
}

function dcvs_insert_new_category($name, $description) {
	global $wpdb;
	$id = dcvs_get_category($name);
	if ($id != NULL) {
		return "That title is already taken";
	} else {
		$wpdb->insert("dcvs_category", ["name"=>$name, "description"=>$description] );
		return "Created!";
	}
}

function dcvs_update_category($id, $name, $description) {
	global $wpdb;
	$check_id = dcvs_get_category($name);
	if ($check_id != NULL && $check_id != $id) {
		return "You've entered a title that's already taken";
	} else {
		$wpdb->update("dcvs_category", array("name"=>$name, "description"=>$description), array("id"=>$id));
		return "Updated!";
	}
}

function dcvs_delete_category($id) {
	global $wpdb;
	$business_categories = $wpdb->get_results("SELECT * FROM dcvs_business_category WHERE category_id='".esc_sql($id)."'");
	$persona_categories = $wpdb->get_results("SELECT * FROM dcvs_persona_category WHERE category_id='".esc_sql($id)."'");
	if(sizeof($business_categories) != 0 || sizeof($persona_categories) != 0) {
		return "At least one business or persona is using this category, so it cannot be deleted";
	} else {
		$wpdb->delete("dcvs_category", array("id"=>$id));
		return "Deleted!";
	}
}

?>
