<?php

defined('ABSPATH') or wp_die('You are not allowed to call this page directly.');

/**
 * This file is used to display the correct manager page.
 *
 * The correct mode is set with the $_GET['mode'] variable. If it is not set, the gallery manager will be
 * displayed.
 */
global $ngg;

if(isset($_GET['mode'])) {

	switch($_GET['mode']) {
		case 'image':
			include_once( 'class-ngg-image-manager.php' );
			$ngg->image_page = new NGG_Image_Manager();
			$ngg->image_page->display();
			break;
		default:
			ngg_display_gallery_manager();
	}

} else {
	ngg_display_gallery_manager();
}

function ngg_display_gallery_manager() {
	global $ngg;
	include_once( 'class-ngg-gallery-manager.php' );    // nggallery_admin_manage_gallery
	// Initate the Manage Gallery page
	$ngg->manage_page = new NGG_Gallery_Manager();
	$ngg->manage_page->display();
}