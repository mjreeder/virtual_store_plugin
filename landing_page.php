<?php
add_filter("the_content", "dcvs_landing_page_content");

function dcvs_landing_page_content($content){
    if(is_page("virtual-store-landing")){
        ob_start();
        include  __DIR__ . "/templates/landing.php";

        $pluginContent = ob_get_contents();
        ob_end_clean();
        return $pluginContent;

    }
    return $content;
}

function dcvs_get_videos(){
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Authorization: Bearer 2f31f4053bb21a971bad92c108b253bf"
		)
	);

	$context = stream_context_create($opts);

	// Open the file using the HTTP headers set above
	$file = json_decode(file_get_contents('https://api.vimeo.com/users/10466342/albums/4481462/videos', false, $context), $assoc_array = false );

	return $file->data;
}

function dcvs_get_video_js(){
	$videos = dcvs_get_videos();
	$data = array_map(function($video){
		$id = preg_replace('/\D+/','', $video->uri);
		return [
			'id'=>$id,
			'title'=>$video->name,
			'description'=>$video->description,
			'duration'=>gmdate("i:s", $video->duration),
			'embed'=>$video->embed->html
		];
	}, $videos);
	return $data;
}

function dcvs_get_video_progress_for_user(){
	get_user_meta(get_current_user_id(), 'video_progress', true);
}

function dcvs_update_video_progress_for_user(){
	$videoID = filter_var($_POST['data']['videoID'], FILTER_SANITIZE_NUMBER_INT);
	update_user_meta(get_current_user_id(), 'video_progress', $videoID);
	die('');
}

add_action('wp_ajax_video_progress', 'dcvs_update_video_progress_for_user');