<?php

define('WP_USE_THEMES', false);

/** Loads the WordPress Environment and Template */
ob_start();
require_once('../../../wp-load.php');
require_once( ABSPATH . 'wp-admin/includes/admin.php' );
ob_end_clean();


//echo '<pre>'; print_r($_POST); echo '</pre>';

// $_POST contains:
// 	$_POST['fb_id']
// 	$_POST['fb_name']
// 	$_POST['fb_email']

// get userId from FaceBook-id
$fbUid = (int)$_POST['fb_id'];

// get id of user
global $wpdb;
global $current_page_city;
$usermeta = $wpdb->prefix . 'usermeta';
$select_user = "SELECT user_id FROM $usermeta WHERE meta_key = 'fbuid' AND meta_value = '$fbUid'";

$user_id = $wpdb->get_var($wpdb->prepare($select_user));

$user_name = $_POST['fb_name'];
$user_email = $_POST['fb_email'];
if ($user_name != 'admin' && (is_null($user_id) || !$user_id)) {
	
	// create new user
	$user_id = username_exists( $user_name );
	if ( !$user_id ) {
		$random_password = wp_generate_password( 12, false );
		$user_id = wp_create_user( $user_name, $random_password, $user_email );
	} else {
		$random_password = __('User already exists.  Password inherited.');
	}
	
	add_user_meta( $user_id, 'fbuid', $fbUid, true );
}

echo '<html><head><title>upload</title></head><body>';
if (isset($_FILES['video'])) {
	// Get the type of the uploaded file. This is returned as "type/extension"
	$arr_file_type = wp_check_filetype(basename($_FILES['video']['name']));
	$uploaded_file_type = $arr_file_type['type'];
	echo 'uploaded file type: '.$arr_file_type['type'].'<br />';

	// Set an array containing a list of acceptable formats
	$allowed_file_types = array('video/quicktime');

	// If the uploaded file is the right format

	// TODO: for now we accept all mime types, this should be changed!
	//if(in_array($uploaded_file_type, $allowed_file_types)) {

		// Options array for the wp_handle_upload function. 'test_upload' => false
		$upload_overrides = array( 'test_form' => false ); 

		// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
		$uploaded_file = wp_handle_upload($_FILES['video'], $upload_overrides);

		echo 'uploaded file info: <pre>'.print_r($uploaded_file,1).'</pre><br />';
		// If the wp_handle_upload call returned a local path for the image
		if(isset($uploaded_file['file'])) {

			// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
			$file_name_and_location = $uploaded_file['file'];

			// Generate a title for the image that'll be used in the media library
			$file_title_for_media_library = 'Video Upload';

			// create post
			$post = array(
				'post_title' => 'Video post '.date('d.m.Y G:i')
				,'post_content' => '<video src="'.$uploaded_file['url'].'" controls />'
				,'post_status' => 'publish'
				,'post_author' => $user_id
				//,'post_category' => array(8,39)
			);

			kses_remove_filters();
			$post_id = wp_insert_post($post);
			kses_init_filters();
			
			echo 'created post '.$post_id.'<br />';

			// Set up options array to add this file as an attachment
			$attachment = array(
				'post_mime_type' => $uploaded_file_type,
				'post_title' => 'Video post '.date('d.m.Y G:i'), //'Uploaded video ' . addslashes($file_title_for_media_library),
				'post_status' => 'inherit'
			);

			// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
			$attach_id = wp_insert_attachment( $attachment, $file_name_and_location, $post_id );

			echo 'created attachment: '.$attach_id.'<br />';

			require_once(ABSPATH . 'wp-admin/includes/image.php');

			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
			echo 'attachment data: <pre>'.print_r($attach_data,1).'</pre><br />';

			wp_update_attachment_metadata($attach_id,  $attach_data);

			// Now, update the post meta to associate the new image with the post
			update_post_meta($post_id,'_hybreb_attached_image',$attach_id);

			// Set the feedback flag to false, since the upload was successful
			$upload_feedback = false;

		} else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.

			$upload_feedback = 'There was a problem with your upload.';
			update_post_meta($post_id,'_hybreb_attached_image',$attach_id);

		}

	//} else { // wrong file type
	//	$upload_feedback = 'Please upload only image files (jpg, gif or png).';
	//	update_post_meta($post_id,'_xxxx_attached_image',$attach_id);
	//}

} else { // No file was passed

	$upload_feedback = false;
}

// Update the post meta with any feedback
update_post_meta($post_id,'_hybreb_attached_image_upload_feedback',$upload_feedback);

echo 'Feedback: '.$upload_feedback.'<br/></body></html>';