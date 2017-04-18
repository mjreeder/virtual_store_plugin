<?php

global $wpdb;

// TODO: Update feedback label once we have a design

$user_message = "";

if($_SERVER['REQUEST_METHOD']=="POST" && isset($_REQUEST['submit'])) {

	if ($_REQUEST['submit'] == "SAVE") {
		$name = $_REQUEST['name'];
		$budget = $_REQUEST['budget'];
		$description = $_REQUEST['description'];
		$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : -1;
		dcvs_insert_new_persona($name, $description, $budget);
		if ($wpdb->insert_id && $category_id != -1) {
			dcvs_insert_new_persona_category($wpdb->insert_id, $_REQUEST['category_id']);
		}
		$user_message = "Created!";
	} else if ($_REQUEST['submit'] == "UPDATE") {
		$persona_id = $_REQUEST['persona_id'];
		$name = $_REQUEST['name'];
		$budget = $_REQUEST['budget'];
		$description = $_REQUEST['description'];
		$category_id = $_REQUEST['category_id'];
		$current_persona_category_id = $_REQUEST['current_persona_category_id'];
		dcvs_update_persona( $persona_id, $name, $description, $budget );
		if ($category_id != -1 && $current_persona_category_id != -1) {
			dcvs_update_persona_category($persona_id, $current_persona_category_id, $category_id);
		} else if ($category_id != -1 && $current_persona_category_id == -1){
			dcvs_insert_new_persona_category($persona_id, $category_id);
		} else if ($category_id == -1 && $current_persona_category_id != -1) {
			dcvs_delete_persona_category($persona_id, $current_persona_category_id);
		}
		$user_message = "Updated!";
	} else if($_REQUEST['submit'] == "DELETE") {
		$persona_id = $_REQUEST['persona_id'];
		dcvs_delete_persona( $persona_id );
		$user_message = "Deleted!";
	}
}

$pencil_image = plugins_url( 'assets/images/pencil.svg', dirname(__FILE__));
$trash_image = plugins_url( 'assets/images/trash.svg', dirname(__FILE__));

$personas = dcvs_get_all_personas();
$categories = dcvs_get_all_categories();

function dcvs_get_all_personas() {
	global $wpdb;
	$personas = $wpdb->get_results("SELECT dcvs_persona.*, dcvs_persona_category.category_id as category_id, dcvs_category.name as category_name
		FROM dcvs_persona
		LEFT JOIN dcvs_persona_category
		ON dcvs_persona.id = dcvs_persona_category.persona_id
		LEFT JOIN dcvs_category
		ON dcvs_persona_category.category_id = dcvs_category.id", ARRAY_A);
	return $personas;
}

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

	function editPersona(personaID, name, budget, description, categoryID) {
		$('#persona_id').val(personaID);
		$('#name').val(name);
		$('#budget').val(budget);
		$('#description').val(description);
		$('#categorySelect').val(categoryID);
		$('#current_persona_category_id').val(categoryID);
		$('#editModal').show();
		$('#backdrop').show();
	}

</script>

<section class="manage">

	<div>
		<h1 class="title">Manage Personas</h1>
		<button class="headerButton createNew" id="createNew">CREATE NEW</button>
		<label><?php echo $user_message; ?></label>
	</div>

	<section class="createModal" id="createModal">
		<h1>CREATE NEW</h1>
		<form action="" method="post">

			<input type="hidden" name="page" value="dcvs_teacher">
			<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
			<input type="hidden" name="section" value="manage">

			<input type="text" name="name" placeholder="name">
			<input type="text" name="budget" placeholder="budget">
			<textarea rows="5" cols="36" name="description" placeholder="description"></textarea>
			<select name="category_id">
				<option value="-1" disabled selected>Select a category</option>
				<?php
				foreach ($categories as $category) {
					$category_id   = $category['id'];
					$category_name = $category['name'];
				?>
					<option value="<?php echo $category_id ?>"><?php echo $category_name ?></option>
				<?php
				}
				?>
				<option value="-1">Unset Category</option>
			</select>
			<input type="submit" name="submit" value="SAVE">

		</form>
	</section>

	<section class="createModal" id="editModal">
		<h1>Edit Persona</h1>
		<form action="" method="post">

			<input type="hidden" name="page" value="dcvs_teacher">
			<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
			<input type="hidden" name="section" value="manage">
			<input type="hidden" name="persona_id" value="" id="persona_id">
			<input type="hidden" name="current_persona_category_id" value="" id="current_persona_category_id">

			<input type="text" name="name" placeholder="name" id="name">
			<input type="text" name="budget" placeholder="budget" id="budget">
			<textarea rows="5" cols="36" name="description" placeholder="description" id="description"></textarea>
			<select id="categorySelect" name="category_id">
				<option value="-1" disabled selected>Select a category</option>
				<?php
				foreach ($categories as $category) {
					$category_id   = $category['id'];
					$category_name = $category['name'];
					?>
					<option value="<?php echo $category_id ?>"><?php echo $category_name ?></option>
					<?php
				}
				?>
				<option value="-1">Unset Category</option>
			</select>
			<input type="submit" name="submit" value="UPDATE">

		</form>
	</section>

	<div class="tableWrapper">
		<table class="virtualTable orderTable">
			<tr>
				<th>TITLE</th>
				<th>CATEGORY</th>
				<th>BUDGET</th>
				<th>DESCRIPTION</th>
				<th></th>
				<th></th>
			</tr>
			<?php
			foreach ($personas as $persona) {
				$persona_id = $persona['id'];
				$persona_name = $persona['name'];
				$persona_category_name = isset($persona['category_name']) ? $persona['category_name'] : "NOT SET";
				$persona_category_id = isset($persona['category_id']) ? $persona['category_id'] : -1;
				$persona_money = $persona['money'];
				$persona_description = $persona['description'];
			?>
				<tr>
					<td><?php echo $persona_name; ?></td>
					<td><?php echo $persona_category_name; ?></td>
					<td>$<?php echo $persona_money; ?></td>
					<td class="desc"><?php echo $persona_description; ?></td>
					<td><img src="<?php echo $pencil_image; ?>" alt="edit persona button" onclick="editPersona('<?php echo $persona_id ?>', '<?php echo $persona_name ?>', '<?php echo $persona_money ?>', '<?php echo $persona_description ?>', '<?php echo $persona_category_id ?>')"></td>
					<td>
						<form action="" method="post">
							<input type="hidden" name="page" value="dcvs_teacher">
							<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
							<input type="hidden" name="section" value="manage">
							<input type="hidden" name="persona_id" value="<?php echo $persona_id ?>">

							<input type="image" src="<?php echo $trash_image; ?>" alt="delete persona button" name="submit" value="DELETE" onclick="this.form.submit()">
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

function dcvs_insert_new_persona_category($persona_id, $category_id) {
	global $wpdb;
	$wpdb->insert("dcvs_persona_category", ["category_id"=>$category_id, "persona_id"=>$persona_id] );
}

function dcvs_update_persona_category($persona_id, $current_persona_category_id, $new_category_id) {
	global $wpdb;
	$wpdb->update("dcvs_persona_category", array("category_id"=>$new_category_id), array("persona_id"=>$persona_id, "category_id"=>$current_persona_category_id));
}

function dcvs_delete_persona_category($persona_id, $category_id) {
	global $wpdb;
	$wpdb->delete("dcvs_persona_category", array("persona_id"=>$persona_id, "category_id"=>$category_id));
}

?>
