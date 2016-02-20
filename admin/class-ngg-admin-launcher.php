<?php

/**
 * NGG_Admin_Launcher - Admin Section for NextGEN Gallery
 *
 * @since   1.0.0
 */
class NGG_Admin_Launcher {

	/**
	 * The admin launcher isn't more than a bunch of functions that run when certain actions/filters are executed.
	 */
	public function __construct() {

		// Add the admin menu
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		//Add the network menu
		add_action( 'network_admin_menu', array( $this, 'add_network_admin_menu' ) );

		// Add the script and style files
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_styles' ) );

		add_filter( 'current_screen', array( $this, 'edit_current_screen' ) );

		// Add WPML hook to register description / alt text for translation
		add_action( 'ngg_image_updated', array( 'nggGallery', 'RegisterString' ) );

		add_filter( 'set-screen-option', array( $this, 'save_options' ), 10, 3 );

	}

	/**
	 * Enable dash icons for WP latest versions.
	 * @see https://developer.wordpress.org/resource/dashicons/#format-gallery
	 *
	 * @param string $wp_version The WordPress version. Defaults to the current one.
	 *
	 * @return string The icon string.
	 */

	private function get_icon_gallery( $wp_version = '' ) {
		if ( empty( $wp_version ) ) {
			$wp_version = get_bloginfo( 'version' ); //get WP Version
		}
		if ( $wp_version >= 3.8 ) {
			return 'dashicons-format-gallery'; //new style
		}

		//older style
		return path_join( NGGALLERY_URLPATH, 'admin/images/nextgen_16_color.png' );
	}

	/**
	 * Add all menu pages to the WordPress menu.
	 */
	public function add_menu() {
		add_menu_page( __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ),
			'NextGEN Gallery overview', NGGFOLDER, array( $this, 'show_menu' ), $this->get_icon_gallery() );

		add_submenu_page( NGGFOLDER, __( 'Overview', 'nggallery' ), __( 'Overview', 'nggallery' ),
			'NextGEN Gallery overview',
			NGGFOLDER, array( $this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Add Gallery / Images', 'nggallery' ),
			__( 'Add Gallery / Images', 'nggallery' ), 'NextGEN Upload images', 'nggallery-add-gallery',
			array( $this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ),
			'NextGEN Manage gallery', 'nggallery-manage',
			array( $this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Albums', 'nggallery' ), __( 'Albums', 'nggallery' ), 'NextGEN Edit album',
			'nggallery-manage-album',
			array( $this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Tags', 'nggallery' ), __( 'Tags', 'nggallery' ), 'NextGEN Manage tags',
			'nggallery-tags',
			array( $this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Settings', 'nggallery' ), __( 'Settings', 'nggallery' ),
			'NextGEN Change options', 'nggallery-options',
			array( $this, 'show_menu' ) );

		if ( wpmu_enable_function( 'wpmuStyle' ) ) {
			add_submenu_page( NGGFOLDER, __( 'Style', 'nggallery' ), __( 'Style', 'nggallery' ), 'NextGEN Change style',
				'nggallery-style',
				array( $this, 'show_menu' ) );
		}
		if ( wpmu_enable_function( 'wpmuRoles' ) || is_super_admin() ) {
			add_submenu_page( NGGFOLDER, __( 'Roles', 'nggallery' ), __( 'Roles', 'nggallery' ), 'activate_plugins',
				'nggallery-roles',
				array( $this, 'show_menu' ) );
		}
	}

	/**
	 * Add the network pages to the network menu.
	 */
	public function add_network_admin_menu() {
		add_menu_page( __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ), 'nggallery-wpmu',
			NGGFOLDER, array( $this, 'show_network_settings' ), $this->get_icon_gallery() );

		add_submenu_page( NGGFOLDER, __( 'Network settings', 'nggallery' ), __( 'Network settings', 'nggallery' ),
			'nggallery-wpmu',
			NGGFOLDER, array( $this, 'show_network_settings' ) );

		add_submenu_page( NGGFOLDER, __( 'Reset / Uninstall', 'nggallery' ), __( 'Reset / Uninstall', 'nggallery' ),
			'activate_plugins',
			'nggallery-setup', array( $this, 'show_menu' ) );
	}

	/**
	 * Maybe show an upgrade page.
	 */
	private function show_upgrade_page() {

		// check for upgrade and show upgrade screen
		if ( get_option( 'ngg_db_version' ) != NGG_DBVERSION ) {
			include_once( dirname( __FILE__ ) . '/functions.php' );
			include_once( dirname( __FILE__ ) . '/upgrade.php' );
			nggallery_upgrade_page();
			exit;
		}
	}

	/**
	 * Show the network pages.
	 */
	public function show_network_settings() {
		$this->show_upgrade_page();
		include_once( dirname( __FILE__ ) . '/class-ngg-style.php' );
		include_once( dirname( __FILE__ ) . '/wpmu.php' );
		nggallery_wpmu_setup();
	}

	// load the script for the defined page and load only this code
	//20140515: removed donation code (not in use)
	public function show_menu() {

		//Show the upgrade page if needed.
		$this->show_upgrade_page();

		$options = get_option( 'ngg_options' );

		// Set installation date
		if ( empty( $options['installDate'] ) ) {
			$options['installDate'] = time();
			update_option( 'ngg_options', $options );
		}

		/**
		 * @var NGG_Displayable $page
		 */
		$page = null;

		switch ( $_GET['page'] ) {
			case "nggallery-add-gallery" :
				include_once( dirname( __FILE__ ) . '/functions.php' );
				include_once( dirname( __FILE__ ) . '/class-ngg-adder.php' );
				$page = new NGG_Adder();
				break;
			case "nggallery-manage":
				include_once( dirname( __FILE__ ) . '/functions.php' );
				$page = $this->get_manager();
				break;
			case "nggallery-manage-album" :
				include_once( dirname( __FILE__ ) . '/class-ngg-album-manager.php' );
				$page = new NGG_Album_Manager ();
				break;
			case "nggallery-options" :
				include_once( dirname( __FILE__ ) . '/class-ngg-options.php' );
				$page = new NGG_Options();
				break;
			case "nggallery-tags" :
				include_once( dirname( __FILE__ ) . '/class-ngg-tag-manager.php' );
				$page = new NGG_Tag_Manager();
				break;
			case "nggallery-style" :
				include_once( dirname( __FILE__ ) . '/class-ngg-style.php' );
				$page = new NGG_Style();
				break;
			case "nggallery-roles" :
				include_once( dirname( __FILE__ ) . '/class-ngg-roles.php' );
				$page = new NGG_Roles();
				break;
			case "nggallery":
			default:
				//Display the overview
				include_once( dirname( __FILE__ ) . '/class-ngg-overview.php' );
				$page = new NGG_Overview();
		}

		/**
		 * Display the page.
		 */
		if ( $page != null ) {
			$page->display();
		}

	}

	/**
	 * Switch between the different management modes:
	 * - a list of all galleries,
	 * - a list of all images in a gallery,
	 * - sort mode of a gallery,
	 * - search mode.
	 *
	 * @return NGG_Displayable The correct managing page or null if the page could not be found.
	 */
	private function get_manager() {

		if ( ! isset( $_GET['mode'] ) || $_GET['mode'] === 'gallery' ) {

			//Display the normal page.
			include_once( 'manage/class-ngg-gallery-manager.php' );

			return new NGG_Gallery_Manager();

		} elseif ( $_GET['mode'] == 'image' ) {

			//Display overview of a gallery.
			include_once( 'manage/class-ngg-image-manager.php' );

			return new NGG_Image_Manager();

		} elseif ( $_GET['mode'] == 'sort' ) {

			//Display sort page.
			include_once( 'manage/class-ngg-sort-manager.php' );

			return new NGG_Sort_Manager();

		} elseif ( $_GET['mode'] == 'search' ) {

			//Display search results.
			include_once( 'manage/class-ngg-search-manager.php' );

			return new NGG_Search_Manager();
		} else {
			return null;
		}
	}

	/**
	 * Load the scripts on the admin pages.
	 */
	public function load_scripts() {
		// no need to go on if it's not a plugin page
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		wp_register_script( 'ngg-ajax', plugins_url( 'js/ngg.ajax.js', __FILE__), array( 'jquery' ), '1.4.1' );
		wp_localize_script( 'ngg-ajax', 'nggAjaxSetup', array(
			'url'        => admin_url( 'admin-ajax.php' ),
			'action'     => 'ngg_ajax_operation',
			'nonce'      => wp_create_nonce( 'ngg-ajax' ),
			'permission' => __( 'You do not have the correct permission', 'nggallery' ),
			'error'      => __( 'Unexpected Error', 'nggallery' ),
			'failure'    => __( 'A failure occurred', 'nggallery' )
		) );
		wp_register_script( 'ngg-plupload-handler', plugins_url( 'js/plupload.handler.js', __FILE__), array( 'plupload-all' ), '0.0.1' );
		wp_localize_script( 'ngg-plupload-handler', 'pluploadL10n', array(
			'queue_limit_exceeded'      => __( 'You have attempted to queue too many files.' ),
			'file_exceeds_size_limit'   => __( 'This file exceeds the maximum upload size for this site.' ),
			'zero_byte_file'            => __( 'This file is empty. Please try another.' ),
			'invalid_filetype'          => __( 'This file type is not allowed. Please try another.' ),
			'not_an_image'              => __( 'This file is not an image. Please try another.' ),
			'image_memory_exceeded'     => __( 'Memory exceeded. Please try another smaller file.' ),
			'image_dimensions_exceeded' => __( 'This is larger than the maximum size. Please try another.' ),
			'default_error'             => __( 'An error occurred in the upload. Please try again later.' ),
			'missing_upload_url'        => __( 'There was a configuration error. Please contact the server administrator.' ),
			'upload_limit_exceeded'     => __( 'You may only upload 1 file.' ),
			'http_error'                => __( 'HTTP error.' ),
			'upload_failed'             => __( 'Upload failed.' ),
			'io_error'                  => __( 'IO error.' ),
			'security_error'            => __( 'Security error.' ),
			'file_cancelled'            => __( 'File canceled.' ),
			'upload_stopped'            => __( 'Upload stopped.' ),
			'dismiss'                   => __( 'Dismiss' ),
			'crunching'                 => __( 'Crunching&hellip;' ),
			'deleted'                   => __( 'moved to the trash.' ),
			'error_uploading'           => __( '&#8220;%s&#8221; has failed to upload due to an error' ),
			'no_gallery'                => __( 'You didn\'t select a gallery!', 'nggallery' )
		) );
		wp_register_script( 'ngg-progressbar', plugins_url( 'js/ngg.progressbar.js', __FILE__), array( 'jquery' ),
			'2.0.1' );
		wp_register_script( 'ngg-autocomplete', plugins_url( 'js/ngg.autocomplete.js', __FILE__ ), array( 'jquery-ui-autocomplete' ), '1.1' );

		switch ( $_GET['page'] ) {
			case NGGFOLDER :
				add_thickbox();
				wp_enqueue_script( 'postbox' );
				break;
			case "nggallery-manage":
				wp_enqueue_script( 'postbox' );
				wp_enqueue_script( 'ngg-ajax' );
				wp_enqueue_script( 'ngg-progressbar' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script( 'ngg-autocomplete' );
				wp_enqueue_script( 'ngg-cropper', plugins_url('js/cropper/cropper.min.js', __FILE__), '2.2.5' );
				wp_register_script( 'shutter', plugins_url('shutter/shutter-reloaded.js', __DIR__), false, '1.3.2' );
				wp_localize_script( 'shutter', 'shutterSettings', array(
					'msgLoading' => __( 'L O A D I N G', 'nggallery' ),
					'msgClose'   => __( 'Click to Close', 'nggallery' ),
					'imageCount' => '1'
				) );
				wp_enqueue_script( 'shutter' );
				break;
			case "nggallery-manage-album" :
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'ngg-autocomplete' );
				break;
			case "nggallery-options" :
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'ngg-autocomplete' );
				break;
			case "nggallery-add-gallery" :
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'ngg-plupload-handler' );
				wp_enqueue_script( 'ngg-ajax' );
				wp_enqueue_script( 'ngg-progressbar' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jqueryFileTree', plugins_url( 'js/jqueryFileTree/jqueryFileTree.js', __FILE__), array( 'jquery' ), '1.0.1' );
				break;
			case "nggallery-style" :
				wp_enqueue_script( 'codepress' );
				break;

		}
	}

	/**
	 * Load the CSS files.
	 */
	public function load_styles() {
		wp_register_style( 'nggadmin', plugins_url( 'css/nggadmin.css', __FILE__), false, '2.8.1', 'screen' );
		wp_register_style( 'ngg-jqueryui', plugins_url( 'css/jquery.ui.css', __FILE__), false, '1.8.5', 'screen' );

		// no need to go on if it's not a plugin page
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		switch ( $_GET['page'] ) {
			case NGGFOLDER :
				wp_enqueue_style( 'nggadmin' );
				wp_enqueue_style( 'thickbox' );
				break;
			case "nggallery-add-gallery" :
				wp_enqueue_style( 'ngg-jqueryui' );
				wp_enqueue_style( 'jqueryFileTree', plugins_url( 'js/jqueryFileTree/jqueryFileTree.css', __FILE__), false, '1.0.1', 'screen' );
			case "nggallery-options" :
				wp_enqueue_style( 'nggtabs', plugins_url( 'css/jquery.ui.tabs.css', __FILE__), false, '2.5.0',
					'screen' );
				wp_enqueue_style( 'nggadmin' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'ngg-jqueryui' );
				break;
			case "nggallery-manage":
				wp_enqueue_style( 'ngg-cropper', plugins_url( 'js/cropper/cropper.min.css', __FILE__), '2.2.5' );
				wp_enqueue_style( 'shutter', plugins_url('shutter/shutter-reloaded.css', __DIR__), false, '1.3.2',
					'screen' );
				wp_enqueue_style( 'datepicker', plugins_url('css/jquery.ui.datepicker.css', __FILE__), false,
					'1.8.2', 'screen' );
			case "nggallery-roles" :
			case "nggallery-manage-album" :
				wp_enqueue_style( 'ngg-jqueryui' );
				wp_enqueue_style( 'nggadmin' );
				break;
			case "nggallery-tags" :
				wp_enqueue_style( 'nggadmin' );
				break;
			case "nggallery-style" :
				break;
		}
	}

	/**
	 * Save the screen options.
	 */
	public static function save_options( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Add help and options to the correct screens
	 *
	 * @since 1.9.24
	 *
	 * @param WP_Screen $screen The current screen.
	 *
	 * @return WP_Screen $screen The current screen.
	 */
	public function edit_current_screen( $screen ) {

		// menu title is localized, so we need to change the toplevel name
		$i18n = strtolower( __( 'Galleries', 'nggallery' ) );

		switch ( $screen->id ) {
			case 'toplevel_page_' . NGGFOLDER :
				//The tab content
				$help = '<p>' . __( 'Welcome to your NextCellent Dashboard! This screen gives you all kinds of information about NextCellent at glance. You can get help for any screen by clicking the Help tab in the upper corner.' ) . '</p>';
				//Add the tab
				$screen->add_help_tab( array(
					'id'      => $screen->id . '-welcome',
					'title'   => 'Overview',
					'content' => $help
				) );

				//The tab content
				$help = '<p>' . __( 'The boxes on your overview screen are:', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'At a Glance',
						'nggallery' ) . '</strong> - ' . __( 'Shows some general information about your site, such as the number of pictures, albums and galleries.',
						'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Latest News',
						'nggallery' ) . '</strong> - ' . __( 'The latest NextCellent news.', 'nggallery' ) . '</p>';
				if ( ! is_multisite() || is_super_admin() ) {
					$help .= '<p><strong>' . __( 'Related plugins',
							'nggallery' ) . '</strong> - ' . __( 'Shows plugins that extend NextCellent.',
							'nggallery' ) . ' <strong>' . __( 'Pay attention',
							'nggallery' ) . '</strong>: ' . __( 'third parties plugins that are compatible with NGG may not be 100% compatible with NextCellent Gallery!',
							'nggallery' ) . '</p>';
				}
				$help .= '<p><strong>' . __( 'Help me help YOU!',
						'nggallery' ) . '</strong> - ' . __( 'Shows general information about he plugin and some links.',
						'nggallery' ) . '</p>';
				if ( ! ( get_locale() == 'en_US' ) ) {
					$help .= '<p><strong>' . __( 'Translation',
							'nggallery' ) . '</strong> - ' . __( 'View information about the current translation.' ) . '</p>';
				}
				if ( ! is_multisite() || is_super_admin() ) {
					$help .= '<p><strong>' . __( 'Server Settings',
							'nggallery' ) . '</strong> - ' . __( 'Show all the server settings!.',
							'nggallery' ) . '</p>';
					$help .= '<p><strong>' . __( 'Plugin Check',
							'nggallery' ) . '</strong> - ' . __( 'Check if there are known errors in your installation.',
							'nggallery' ) . '</p>';
				}
				//Add the tab
				$screen->add_help_tab( array(
					'id'      => $screen->id . '-content',
					'title'   => 'Content',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-add-gallery" :

				/**
				 * @global nggdb $nggdb
				 */
				global $nggdb;
				$gallerylist = $nggdb->find_all_galleries( 'gid', 'DESC' ); //look for galleries

				$help = '<p>' . __( 'On this page you can add galleries and pictures to those galleries.',
						'nggallery' ) . '</p>';
				if ( nggGallery::current_user_can( 'NextGEN Add new gallery' ) ) {
					$help .= '<p><strong>' . __( 'New gallery',
							'nggallery' ) . '</strong> - ' . __( 'Add new galleries to NextCellent.',
							'nggallery' ) . '</p>';
				}
				if ( empty ( $gallerylist ) ) {
					$help .= '<p><strong>' . __( 'You must add a gallery before adding images!',
							'nggallery' ) . '</strong>';
				} else {
					$help .= '<p><strong>' . __( 'Images',
							'nggallery' ) . '</strong> - ' . __( 'Add new images to a gallery.', 'nggallery' ) . '</p>';
				}
				if ( wpmu_enable_function( 'wpmuZipUpload' ) && nggGallery::current_user_can( 'NextGEN Upload a zip' ) && ! empty ( $gallerylist ) ) {
					$help .= '<p><strong>' . __( 'ZIP file',
							'nggallery' ) . '</strong> - ' . __( 'Add images from a ZIP file.', 'nggallery' ) . '</p>';
				}
				if ( wpmu_enable_function( 'wpmuImportFolder' ) && nggGallery::current_user_can( 'NextGEN Import image folder' ) ) {
					$help .= '<p><strong>' . __( 'Import folder',
							'nggallery' ) . '</strong> - ' . __( 'Import a folder from the server as a new gallery.',
							'nggallery' ) . '</p>';
				}

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Add things',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-manage" :

				$option = 'per_page';

				if ( ! isset( $_GET['mode'] ) || $_GET['mode'] === 'gallery' ) {
					include_once( 'manage/class-ngg-gallery-list-table.php' );
					add_filter( 'manage_' . $screen->id . '_columns',
						array( 'NGG_Gallery_List_Table', 'get_columns_static' ), 0 );
					$args = array(
						'label'   => __( 'Galleries', 'nggallery' ),
						'default' => 25,
						'option'  => 'ngg_galleries_per_page'
					);
				} else {
					include_once( 'manage/class-ngg-image-list-table.php' );
					add_filter( 'manage_' . $screen->id . '_columns',
						array( 'NGG_Image_List_Table', 'get_columns_static' ), 0 );
					$args = array(
						'label'   => __( 'Images', 'nggallery' ),
						'default' => 50,
						'option'  => 'ngg_images_per_page'
					);

					$help = '<p>' . __( 'This box contains information and the various options a gallery had.', 'nggallery') . '</p>';

					$screen->add_help_tab( array(
						'id'      => $screen->id . '-general',
						'title'   => __( 'Overview', 'nggallery'),
						'content' => $help
					) );

					$help = '<p>' . __( 'Manage a single gallery and the images it contains:', 'nggallery' ) . '</p>';
					$help .= '<dl class="ncg-dl">';

					$help .= '<dt>' . __( 'Title', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'The title of the gallery. This can be visible to the users of the website. This has no effect on the gallery path.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Description', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'The description of the gallery. Albums using the "extend" template may display this on the website. The description cannot contain HTML.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Path', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'The path on the server to the folder containing this gallery. If you change this, NextCellent will not move the gallery for you.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Gallery ID', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'The internal ID used by NextCellent to represent this gallery. This information can be useful for developers. A gallery ID should never change.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Page Link', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'With this option you can select the behavior when an user clicks on a gallery in an album. If the option is set to "not linked", the gallery will be displayed on the same page. If you do select a page, the user will be redirected to that page.', 'nggallery');
					$help .= ' '. sprintf( __( 'More information about this is available on this webpage: %s', 'nggallery'), '<a target="_blank" href="http://www.nextgen-gallery.com/link-to-page/">' . __('page', 'nggallery') . '</a>') . '</dd>';

					$help .= '<dt>' . __( 'Preview image', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'This image will be shown when the gallery is shown on the website and it needs a preview, e.g. an album. If you do not select a preview image, NextCellent will use the last uploaded image of the gallery.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Author', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'The user who created this gallery.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Create new page', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'This will create a new page with the same name as the gallery, and include a shortcode for this gallery in it.', 'nggallery') .'</dd>';
					$help .= '</dl>';

					$screen->add_help_tab( array(
						'id'      => $screen->id . '-options',
						'title'   => __( 'Gallery settings', 'nggallery'),
						'content' => $help
					) );

					$help = '<p>' . __( 'There are three buttons:', 'nggallery') . '</p>';
					$help .= '<dl class="ncg-dl">';

					$help .= '<dt>' . __( 'Sort gallery', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'Allows you to manually set the order of the images in the gallery. This will only be enabled if you have selected the option "Custom sort order" in the NextCellent settings.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Scan folder for new images', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'Scan the folder (the path of the gallery) for new images and add them to the gallery. <strong>Warning!</strong> This will normalize and rename the images that are added, e.g. spaces are removed.', 'nggallery') .'</dd>';

					$help .= '<dt>' . __( 'Save', 'ngallery') . '</dt>';
					$help .= '<dd>' . __( 'Save changes you have made to the gallery options.', 'nggallery') .'</dd>';

					$help .= '</dl>';

					$screen->add_help_tab( array(
						'id'      => $screen->id . '-buttons',
						'title'   => __( 'Buttons', 'nggallery'),
						'content' => $help
					) );
				}

				$screen->add_option( $option, $args );

				break;
			case "{$i18n}_page_nggallery-manage-album" :
				$help = '<p>' . __( 'Organize your galleries into albums.',
						'nggallery' ) . '</p><p>' . __( 'First select an album from the dropdown and then drag the galleries you want to add or remove from the selected album.',
						'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Organize everything',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-tags" :
				$help = '<p>' . __( 'Organize your pictures with tags.',
						'nggallery' ) . '</p><p>' . __( 'Rename, delete and edit tags. Use the rename function to merge tags.',
						'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Organize pictures',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-options" :
				$help = '<p>' . __( 'Edit all of NextCellent\'s options. The options are sorted in multiple categories.',
						'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'General',
						'nggallery' ) . '</strong> - ' . __( 'General NextCellent options. Contains options for permalinks and related images.',
						'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Images',
						'nggallery' ) . '</strong> - ' . __( 'All image-related options. Also contains options for thumbnails.',
						'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Gallery',
						'nggallery' ) . '</strong> - ' . __( 'Everything about galleries. From sorting options to the number of images, it\'s all in here.',
						'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Effects',
						'nggallery' ) . '</strong> - ' . __( 'Make your gallery look beautiful.',
						'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Watermark',
						'nggallery' ) . '</strong> - ' . __( 'Who doesn\'t want theft-proof images?',
						'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Slideshow',
						'nggallery' ) . '</strong> - ' . __( 'Edit options for the slideshow.', 'nggallery' ) . '</p>';
				$help .= '<p>' . __( 'Don\'t forget to press save!', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Edit options',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-style" :
				$help = '<p>' . __( 'You can edit the css file to adjust how your gallery looks.',
						'nggallery' ) . '</p>';
				$help .= '<p>' . __( 'When you save an edited file, NextCellent automatically saves it as a copy in the folder ngg_styles. This protects your changes from upgrades.',
						'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Style your gallery',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-roles" :
				$help = '<p>' . __( 'You can assign the lowest user role that has access to a certain feature. Needless to say, all greater user roles will also have access to that feature.',
						'nggallery' ) . '</p>';
				$help .= '<p>' . __( 'NextCellent also works with various plugins that extend the default roles capabilities.',
						'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Grant permissions',
					'content' => $help
				) );
				break;
		}

		//Set the sidebar (same on all pages)
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'nggallery' ) . '</strong></p>' .
			'<p><a href="http://codex.wordpress.org/Plugins_Editor_Screen" target="_blank">' . __( 'Support Forums',
				'nggallery' ) . '</a></p>' .
			'<p><a href="https://bitbucket.org/wpgetready/nextcellent" target="_blank">' . __( 'Source Code',
				'nggallery' ) . '</a></p>'
		);

		return $screen;
	}
}

/**
 * Check if a function is enabled on multisite.
 *
 * @param string $value
 *
 * @todo Move from here
 *
 * @return bool If it's enabled or not.
 */
function wpmu_enable_function( $value ) {
	if ( is_multisite() ) {
		$ngg_options = get_site_option( 'ngg_options' );

		if(isset($ngg_options[ $value ])) {
			return $ngg_options[ $value ];
		} else {
			return false;
		}
	}

	// if this is not WPMU, enable it !
	return true;
}
