<?php
/**
 * Accepts file uploads from swfupload.
 *
 * @package NextGEN-Gallery
 * @subpackage Administration
 */

// Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
// We then have to validate the cookie manually. NOTE: WordPress functions, like
// get_current_user_id() and the like are NOT available in this file.
if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
	$_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
	$_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];
if ( empty($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_REQUEST['logged_in_cookie']) )
	$_COOKIE[LOGGED_IN_COOKIE] = $_REQUEST['logged_in_cookie'];

header('Content-Type: text/plain; charset=' . get_option('blog_charset'));

$logged_in = FALSE;

if (wp_validate_auth_cookie()) {
	$results = wp_parse_auth_cookie();
	if (isset($results['username']) && isset($results['expiration'])) {
		if (time() < floatval($results['expiration'])) {
			if (($userdata = get_user_by('login', $results['username'])))
				$logged_in = $userdata->ID;
		}
	}
}

if (!$logged_in)
    die("Login failure. -1");
else if (!user_can($logged_in, 'NextGEN Upload images'))
    die('You do not have permission to upload files. -2');

//check for nggallery
if ( !defined('NGGALLERY_ABSPATH') )
	die('NextCellent Gallery not available. -3');

include_once (NGGALLERY_ABSPATH. 'admin/functions.php');

// get the gallery
$galleryID = (int) $_POST['galleryselect'];

echo nggAdmin::swfupload_image($galleryID);

?>