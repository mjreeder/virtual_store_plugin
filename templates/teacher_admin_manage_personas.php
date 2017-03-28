<?php

// TODO: Add functionality to create/edit/delete to template

$pencil_image = plugins_url( 'assets/images/pencil.svg', dirname(__FILE__));
$trash_image = plugins_url( 'assets/images/trash.svg', dirname(__FILE__));

$personas = dcvs_get_all_personas();

function dcvs_get_all_personas() {
	global $wpdb;
	$personas = $wpdb->get_results("SELECT * FROM dcvs_persona", ARRAY_A);
	return $personas;
}

?>

<section class="manage">

	<h1 class="title">Manage Personas</h1>

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
			?>
				<tr>
					<td><?php echo $persona['name']; ?></td>
					<td>$<?php echo $persona['money']; ?></td>
					<td class="desc"><?php echo $persona['description']; ?></td>
					<td><img src="<?php echo $pencil_image; ?>" alt="edit persona button"></td>
					<td><img src="<?php echo $trash_image; ?>" alt="delete persona button" onclick=""></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>

</section>
