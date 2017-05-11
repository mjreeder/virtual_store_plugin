<?php

$toast = null;
$error = false;

// TODO: Update feedback label once we have a design

if($_SERVER['REQUEST_METHOD']=="POST" && isset($_REQUEST['submit'])) {

	if ($_REQUEST['submit'] == "SAVE") {
		$name = $_REQUEST['name'];
		$budget = $_REQUEST['budget'];
		$description = $_REQUEST['description'];
		$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : -1;
		$existing_persona_with_name = dcvs_get_persona_by_name($name);
		$toast = dcvs_insert_new_persona($name, $description, $budget);
		$created_persona = dcvs_get_persona_by_name($name);
		if (count($existing_persona_with_name) == 0 && $category_id != -1) {
			dcvs_insert_new_persona_category($created_persona[0]['id'], $_REQUEST['category_id']);
		}
	} else if ($_REQUEST['submit'] == "UPDATE") {
		$persona_id = $_REQUEST['persona_id'];
		$name = $_REQUEST['name'];
		$budget = $_REQUEST['budget'];
		$description = $_REQUEST['description'];
		$category_id = $_REQUEST['category_id'];
		$current_persona_category_id = $_REQUEST['current_persona_category_id'];


		if (dcvs_business_have_category($persona_id, $category_id)) {
			$error = true;
			$toast = DCVS_Toast::create_new_toast( 'Selected category already assigned to a persona associated with a students business', true );
		}
		if (!$error) {
			$toast = dcvs_update_persona( $persona_id, $name, $description, $budget );
			$updated_persona = dcvs_get_persona_by_id($persona_id);
			$perona_name = count($updated_persona) != 0 ? $updated_persona[0]['name'] : '';
			if ($category_id != -1 && $current_persona_category_id != -1 && $perona_name == $name) {
				dcvs_update_persona_category($persona_id, $current_persona_category_id, $category_id);
			} else if ($category_id != -1 && $current_persona_category_id == -1 && $perona_name == $name){
				dcvs_insert_new_persona_category($persona_id, $category_id);
			} else if ($category_id == -1 && $current_persona_category_id != -1 && $perona_name == $name) {
				dcvs_delete_persona_category($persona_id, $current_persona_category_id);
			}
		}
	} else if($_REQUEST['submit'] == "DELETE") {
		$persona_id = $_REQUEST['persona_id'];
		//make sure this consumer is not assigned to any user
        global $wpdb;
        $sql = $wpdb->prepare("SELECT wp_users.* FROM dcvs_user_persona JOIN wp_users ON wp_users.ID = dcvs_user_persona.user_id WHERE persona_id = %d", array($persona_id));
        $results = $wpdb->get_results($sql, OBJECT);
        if(count($results)>0){
            $message = "<ul>";
            foreach($results as $user){
                $message.="<li>";
                $message.=$user->user_email."</li>";
            }
            $message.="</ul>";
            $toast=DCVS_Toast::create_new_toast( "<div class='dcvs-cannot-delete'>you cannot delete this consumer because the following current/archived users are associated with this consumer $message",true );
//            echo("<div class='dcvs-cannot-delete'>you cannot delete this consumer because the following current/archived users are associated with this consumer $message");
        }else{
            $toast = dcvs_delete_persona( $persona_id );
        }

	}
}

$pencil_image = plugins_url( 'assets/images/pencil.svg', dirname(__FILE__));
$trash_image = plugins_url( 'assets/images/trash.svg', dirname(__FILE__));

$personas = dcvs_get_all_personas();
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
		<h1 class="title">Manage Consumers</h1>
		<button class="headerButton createNew" id="createNew">CREATE NEW</button>
	</div>

	<section class="createModal" id="createModal">
		<h1>CREATE NEW</h1>
		<form action="" method="post">

			<input type="hidden" name="page" value="dcvs_teacher">
			<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
			<input type="hidden" name="section" value="manage">

			<input type="text" name="name" placeholder="name" required oninvalid="this.setCustomValidity('Name cannot be empty.')" oninput="setCustomValidity('')">
			<input type="text" name="budget" placeholder="budget" required oninvalid="this.setCustomValidity('Budget cannot be empty.')" oninput="setCustomValidity('')">
			<textarea rows="5" cols="36" name="description" placeholder="description"></textarea>
			<select name="category_id">
				<option value="-1" disabled selected>Select a category</option>
				<?php
				foreach ($categories as $category) {
					$category_id   = $category['id'];
					$category_name = $category['name'];
				?>
					<option value="<?php echo $category_id ?>"><?php echo stripslashes_deep($category_name); ?></option>
				<?php
				}
				?>
				<option value="-1">Unset Category</option>
			</select>
			<input type="submit" name="submit" value="SAVE">

		</form>
	</section>

	<section class="createModal" id="editModal">
		<h1>Edit Consumer</h1>
		<form action="" method="post">

			<input type="hidden" name="page" value="dcvs_teacher">
			<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
			<input type="hidden" name="section" value="manage">
			<input type="hidden" name="persona_id" value="" id="persona_id">
			<input type="hidden" name="current_persona_category_id" value="" id="current_persona_category_id">

			<input type="text" name="name" placeholder="name" id="name" required oninvalid="this.setCustomValidity('Name cannot be empty.')" oninput="setCustomValidity('')">
			<input type="text" name="budget" placeholder="budget" id="budget" required oninvalid="this.setCustomValidity('Budget cannot be empty.')" oninput="setCustomValidity('')">
			<textarea rows="5" cols="36" name="description" placeholder="description" id="description"></textarea>
			<select id="categorySelect" name="category_id">
				<option value="-1" selected>Select a category</option>
				<?php
				foreach ($categories as $category) {
					$category_id   = $category['id'];
					$category_name = $category['name'];
					?>
					<option value="<?php echo $category_id ?>"><?php echo stripslashes_deep($category_name); ?></option>
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
				<th>CONSUMER</th>
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
					<td><?php echo stripslashes_deep($persona_name); ?></td>
					<td><?php echo stripslashes_deep($persona_category_name); ?></td>
					<td>$<?php echo $persona_money; ?></td>
					<td class="desc"><?php echo stripslashes_deep($persona_description); ?></td>
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

if ($toast != null) {
	echo $toast;

	?>

	<script src="<?php echo plugins_url( 'js/toast.js', dirname(__FILE__)); ?>"></script>

	<?php
}



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

function dcvs_get_persona_by_name($name) {
	global $wpdb;
	$persona = $wpdb->get_results("SELECT * FROM dcvs_persona WHERE name = '" . esc_sql( $name ) . "'" , ARRAY_A);
	return $persona;
}

function dcvs_get_persona_by_id($id) {
	global $wpdb;
	$persona = $wpdb->get_results("SELECT * FROM dcvs_persona WHERE id = '" . esc_sql( $id ) . "'" , ARRAY_A);
	return $persona;
}

function dcvs_business_have_category($persona_id, $category_id) {
	global $wpdb;
	$sql = $wpdb->prepare("SELECT * FROM dcvs_business_category WHERE category_id = '%d' AND business_id IN (SELECT business_id FROM dcvs_user_business WHERE user_id IN (SELECT user_id FROM dcvs_user_persona WHERE persona_id = '%d'))", [$category_id, $persona_id]);
	$response = $wpdb->get_results($sql, ARRAY_A);
	if (count( $response ) == 0) {
		return false;
	} else {
		return true;
	}
}

function dcvs_insert_new_persona($name, $description, $money) {
	global $wpdb;
	$id = dcvs_get_persona_by_name($name);
	if ($id != NULL) {
		return DCVS_Toast::create_new_toast( "That name is already taken", true );
	} else if (!is_numeric($money)) {
		return DCVS_Toast::create_new_toast( "Budget must be a number", true );
	} else {
		$wpdb->insert("dcvs_persona", ["name"=>$name, "description"=>$description, "money"=>$money] );
		return DCVS_Toast::create_new_toast( "Persona Created!" );
	}
}

function dcvs_update_persona($id, $name, $description, $money) {
	global $wpdb;
	// check if name is taken BY A DIFFERENT ID
	// check if money is a floatval
	$persona = dcvs_get_persona_by_name($name);
	$checked_persona_id = isset($persona[0]['id']) ? $persona[0]['id'] : NUll;
	if ($checked_persona_id != NULL && $checked_persona_id != $id) {
		return DCVS_Toast::create_new_toast( "That name is already taken", true );
	} else if (!money_is_number($money)) {
		return DCVS_Toast::create_new_toast( "Budget must be a number", true );
	} else {
		$wpdb->update("dcvs_persona", array("name"=>$name, "description"=>$description,"money"=>$money), array("id"=>$id));
		return DCVS_Toast::create_new_toast( "Persona Updated!" );
	}
}

function dcvs_delete_persona($id) {
	global $wpdb;
	//check to see if the persona is assigned to anyone
    $sql = $wpdb->prepare("SELECT wp_users.* FROM dcvs_user_persona JOIN wp_users ON wp_users.ID = dcvs_user_persona.user_id WHERE persona_id = %d", array($id));
    $results = $wpdb->get_results($sql, OBJECT);
    if(count($results)>0){
        return false;
    }

	$wpdb->delete("dcvs_persona", array("id"=>$id));
	return DCVS_Toast::create_new_toast( "Persona Deleted!" );
}

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
