<?php

include_once( 'interface-ngg-displayable.php' );

/**
 * Class NGG_Post_Admin_Page
 *
 * Represents a simple admin page.
 */
abstract class NGG_Post_Admin_Page implements NGG_Displayable {

	protected $page;

	public function __construct() {

		$this->page = admin_url() . 'admin.php?page=' . $_GET['page'];

		//Handle the post updates.
		if ( isset( $_POST ) ) {
			$this->processor();
		}

	}

	/**
	 * Handle the POST updates.
	 */
	protected abstract function processor();
}