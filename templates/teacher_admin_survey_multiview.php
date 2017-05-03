<?php
	$formID = filter_var($_GET['form_id'], FILTER_SANITIZE_NUMBER_INT);
	$studentID = filter_var($_GET['student_id'], FILTER_SANITIZE_NUMBER_INT);
	$personaID = ( isset($_GET['persona_id']) ) ? filter_var($_GET['student_id'], FILTER_SANITIZE_NUMBER_INT) : null;
	$personaKey = ( isset($_GET['persona_field_key']) ) ? filter_var($_GET['student_id'], FILTER_SANITIZE_NUMBER_INT) : null;

	$form = GFAPI::get_form($formID);

	$args = array(
		'field_filters' => array(
			array(
				'key' => 'created_by',
				'value' => $studentID
			)
		)
	);
	if( $personaID && $personaKey ){
		$args['field_filters'][] = array(
			'key' => $personaKey,
			'value' => $personaID
		);
	}
	$entries = GFAPI::get_entries($formID, $args);
?>
<main class="admin survey-multiview">
	<h1><?= $form['title']; ?></h1>
	<div class="entry-wrapper">
		<ul class="menu">
			<li><b>Entries</b></li>
			<?php foreach($entries as $entry): ?>
				<li><a href="#entry<?= $entry['id']; ?>"><?= $entry['date_created']; ?></a></li>
			<?php endforeach; ?>
		</ul>
		<div class="entries">
			<ul>
				<?php foreach($entries as $entry): ksort($entry); ?>
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
	</div>
</main>