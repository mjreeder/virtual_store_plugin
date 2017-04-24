<?php

$toast = null;

if($_SERVER['REQUEST_METHOD']=="POST" && isset($_REQUEST['submit'])) {

	if ($_REQUEST['submit'] == "UPDATE") {
		$error = false;
		$business_id = $_REQUEST['business_id'];
		$title = $_REQUEST['title'];
		$budget = $_REQUEST['budget'];
		$description = $_REQUEST['description'];
		$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : -1;
		$current_business_category_id = $_REQUEST['current_business_category_id'];
		if (dcvs_personas_have_category($business_id, $category_id)) {
			$error = true;
			$toast = DCVS_Toast::create_new_toast( 'Selected category already assigned to a persona,', true );
		}
		if (!$error) {
			$toast = dcvs_update_business( $business_id, $title, $description, $budget );
			if ($category_id != -1 && $current_business_category_id != -1) {
				dcvs_update_business_category($business_id, $current_business_category_id, $category_id);
			} else if ($category_id != -1 && $current_business_category_id == -1){
				dcvs_insert_new_business_category($business_id, $category_id);
			} else if ($category_id == -1 && $current_business_category_id != -1) {
				dcvs_delete_business_category($business_id, $current_business_category_id);
			}
		}
	}
}

$pencil_image = plugins_url( 'assets/images/pencil.svg', dirname(__FILE__));

$user_args = array( 'role' => 'Customer');
$users = get_users($user_args);

$user_ids = [];

for ($i = 0; $i < count($users); $i++){
	$user_ids[] = $users[$i]->data->ID;
}

$formatted_user_ids = implode(',', $user_ids);

$businesses = dcvs_get_all_student_businesses( $formatted_user_ids );
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

	function editBusiness(businessID, title, budget, description, categoryID) {
		$('#business_id').val(businessID);
		$('#title').val(title);
		$('#budget').val(budget);
		$('#description').val(description);
		$('#categorySelect').val(categoryID);
		$('#current_business_category_id').val(categoryID);
		$('#editModal').show();
		$('#backdrop').show();
		window.scrollTo(0, 0);
	}

</script>

<section class="manage">

	<div>
		<h1 class="title">Manage Businesses</h1>
	</div>

	<section class="createModal" id="editModal">
		<h1>EDIT BUSINESS</h1>
		<form action="" method="post">

			<input type="hidden" name="page" value="dcvs_teacher">
			<input type="hidden" name="student_id" value="<?php echo $_REQUEST['student_id'] ?>">
			<input type="hidden" name="section" value="business">
			<input type="hidden" name="business_id" value="" id="business_id">
			<input type="hidden" name="current_business_category_id" value="" id="current_business_category_id">

			<input type="text" name="title" placeholder="title" id="title" required oninvalid="this.setCustomValidity('Title cannot be empty.')" oninput="setCustomValidity('')">
			<input type="text" name="budget" placeholder="budget" id="budget" required oninvalid="this.setCustomValidity('Budget cannot be empty.')" oninput="setCustomValidity('')">
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
				<th>STUDENT</th>
				<th>CATEGORY</th>
				<th>TITLE</th>
				<th>BUDGET</th>
				<th>DESCRIPTION</th>
				<th></th>
			</tr>
			<?php
			foreach ($businesses as $business) {
				$business_id = $business['id'];
				$business_title = $business['title'];
				$business_category_name = isset($business['category_name']) ? $business['category_name'] : "<i>NOT SET</i>";
				$business_category_id = isset($business['category_id']) ? $business['category_id'] : -1;
				$business_money = $business['money'];
				$business_description = $business['description'];
				$student_info = get_userdata($business['user_id']);
				$student_meta_info = get_user_meta( $business['user_id'] );
				$student_display_name = ($student_meta_info['first_name'][0] != "" && $student_meta_info['last_name'][0] != "") ? $student_meta_info['first_name'][0] . ' ' . $student_meta_info['last_name'][0] : $student_info->user_email;
				?>
				<tr>
					<td><?php echo $student_display_name; ?></td>
					<td><?php echo $business_category_name; ?></td>
					<td><?php echo $business_title ?></td>
					<td>$<?php echo $business_money ?></td>
					<td class="desc"><?php echo $business_description != "" ? $business_description : '<i>NOT SET</i>'; ?></td>
					<td><img src="<?php echo $pencil_image; ?>" alt="edit persona button" onclick="editBusiness('<?php echo $business_id ?>', '<?php echo $business_title ?>', '<?php echo $business_money ?>', '<?php echo $business_description ?>', '<?php echo $business_category_id ?>')"></td>
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

function dcvs_get_all_student_businesses($formatted_user_ids) {
	global $wpdb;
	$businesses = $wpdb->get_results("SELECT dcvs_business.*, dcvs_business_category.category_id as category_id, dcvs_category.name as category_name, dcvs_user_business.user_id as user_id
		FROM dcvs_business
		LEFT JOIN dcvs_user_business
		ON dcvs_user_business.user_id = (SELECT dcvs_user_business.user_id from dcvs_user_business WHERE dcvs_user_business.business_id = dcvs_business.id)
		LEFT JOIN dcvs_business_category
		ON dcvs_business.id = dcvs_business_category.business_id
		LEFT JOIN dcvs_category
		ON dcvs_business_category.category_id = dcvs_category.id
		WHERE dcvs_business.id IN (SELECT dcvs_user_business.business_id FROM dcvs_user_business WHERE dcvs_user_business.user_id IN ($formatted_user_ids))", ARRAY_A);
	return $businesses;
}

function dcvs_update_business($id, $title, $description, $money) {
	global $wpdb;
	$checkid = dcvs_get_business($title);
	if ($checkid != NULL && $checkid != $id) {
		return DCVS_Toast::create_new_toast( "You've entered a title that's already taken", true );
	} else if (!is_numeric($money)) {
		return DCVS_Toast::create_new_toast( "Budget must be a number", true );
	} else {
		$wpdb->update("dcvs_business", array("title"=>$title, "description"=>$description,"money" =>$money), array("id"=>$id));
		return DCVS_Toast::create_new_toast( "Business Updated" );
	}
}

function dcvs_personas_have_category($business_id, $category_id) {
	global $wpdb;
	$user_id = dcvs_user_id_from_business( $business_id );
	$sql = $wpdb->prepare("SELECT * FROM dcvs_persona_category WHERE category_id = '%d' and  persona_id IN (SELECT persona_id FROM dcvs_user_persona WHERE user_id = '%d')", [$category_id,$user_id]);
	$response = $wpdb->get_results($sql, ARRAY_A);
	if (count( $response ) == 0) {
		return false;
	} else {
		return true;
	}
}

function dcvs_insert_new_business_category($business_id, $category_id) {
	global $wpdb;
	$wpdb->insert("dcvs_business_category", ["category_id"=>$category_id, "business_id"=>$business_id] );
}

function dcvs_update_business_category($business_id, $current_business_category_id, $new_category_id) {
	global $wpdb;
	$wpdb->update("dcvs_business_category", array("category_id"=>$new_category_id), array("business_id"=>$business_id, "category_id"=>$current_business_category_id));
}

function dcvs_delete_business_category($business_id, $category_id) {
	global $wpdb;
	$wpdb->delete("dcvs_business_category", array("business_id"=>$business_id, "category_id"=>$category_id));
}

?>
