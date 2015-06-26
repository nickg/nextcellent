<?php

defined('ABSPATH') or wp_die('You are not allowed to call this page directly.');

/**
 * This file is used to display the correct manager page. It acts as a routing system of kinds.
 *
 * The correct mode is set with the $_GET['mode'] variable. If it is not set, the gallery manager will be
 * displayed.
 *
 * @access private
 */
global $ngg;

if(isset($_GET['mode'])) {

	switch($_GET['mode']) {
		case 'image':
			/**
			 * Display an overview for a specific gallery. Which gallery should be passed in the $_GET['gid']
			 * parameter.
			 *
			 * @access private
			 */
			include_once( 'class-ngg-image-manager.php' );
			$ngg->image_page = new NGG_Image_Manager();
			$ngg->image_page->display();
			break;
		default:
			/**
			 * When nothing is set, or it is something we do not recognize.
			 */
			ngg_display_gallery_manager();
	}

} else {
	ngg_display_gallery_manager();
}

/**
 * Display the gallery overview page. This is a list of all galleries.
 *
 * @access private
 *
 * @todo What does the $ngg->manage_page do?
 */
function ngg_display_gallery_manager() {
	global $ngg;
	include_once( 'class-ngg-gallery-manager.php' );
	$ngg->manage_page = new NGG_Gallery_Manager();
	$ngg->manage_page->display();
}