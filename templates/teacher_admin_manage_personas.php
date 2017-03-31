<?php

// TODO: Add functionality to create/edit/delete to template

if($_SERVER['REQUEST_METHOD']=="POST" && isset($_REQUEST['submit'])) {

	if ($_REQUEST['submit'] == "SAVE") {
		$name = $_REQUEST['name'];
		$budget = $_REQUEST['budget'];
		$description = $_REQUEST['description'];
		dcvs_insert_new_persona($name, $description, $budget);
	} else if ($_REQUEST['submit'] == "UPDATE") {
		$persona_id = $_REQUEST['persona_id'];
		$name = $_REQUEST['name'];
		$budget = $_REQUEST['budget'];
		$description = $_REQUEST['description'];
		dcvs_update_persona( $persona_id, $name, $description, $budget );
	} else if($_REQUEST['submit'] == "DELETE") {
		$persona_id = $_REQUEST['persona_id'];
		dcvs_delete_persona( $persona_id );
	}
}

$pencil_image = plugins_url( 'assets/images/pencil.svg', dirname(__FILE__));
$trash_image = plugins_url( 'assets/images/trash.svg', dirname(__FILE__));

$personas = dcvs_get_all_personas();

function dcvs_get_all_personas() {
	global $wpdb;
	$personas = $wpdb->get_results("SELECT * FROM dcvs_persona", ARRAY_A);
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
		var container = $("#createModal");

		if (!container.is(e.target) && container.has(e.target).length === 0) {
			container.hide();
			$('#backdrop').hide();
		}

		container = $("#editModal");

		if (!container.is(e.target) && container.has(e.target).length === 0) {
			container.hide();
			$('#backdrop').hide();
		}
	});

	function editPersona(personaID, name, budget, description) {
		$('#persona_id').val(personaID);
		$('#name').val(name);
		$('#budget').val(budget);
		$('#description').val(description);
		$('#editModal').show();
		$('#backdrop').show();
	}

</script>

<section class="manage">

	<div>
		<h1 class="title">Manage Personas</h1>
		<button class="headerButton">CONSUMER</button>
		<button class="headerButton selectedFilter">BUYER</button>
		<button class="headerButton createNew" id="createNew">CREATE NEW</button>
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

			<input type="text" name="name" placeholder="name" id="name">
			<input type="text" name="budget" placeholder="budget" id="budget">
			<textarea rows="5" cols="36" name="description" placeholder="description" id="description"></textarea>
			<input type="submit" name="submit" value="UPDATE">

		</form>
	</section>

	<div class="tableWrapper">
		<table class="virtualTable orderTable">
			<tr>
				<th>TITLE</th>
				<th>BUDGET</th>
				<th>DESCRIPTION</th>
				<th></th>
				<th></th>
			</tr>
			<?php
			foreach ($personas as $persona) {
				$persona_id = $persona['id'];
				$persona_name = $persona['name'];
				$persona_money = $persona['money'];
				$persona_description = $persona['description'];
			?>
				<tr>
					<td><?php echo $persona_name; ?></td>
					<td>$<?php echo $persona_money; ?></td>
					<td class="desc"><?php echo $persona_description; ?></td>
					<td><img src="<?php echo $pencil_image; ?>" alt="edit persona button" onclick="editPersona('<?php echo $persona_id ?>', '<?php echo $persona_name ?>', '<?php echo $persona_money ?>', '<?php echo $persona_description ?>')"></td>
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
