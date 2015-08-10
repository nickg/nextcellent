<?php

/**
 * nggAdminPanel - Admin Section for NextGEN Gallery
 *
 * @package NextGEN Gallery
 * @author Alex Rabe
 *
 * @since 1.0.0
 */
class nggAdminPanel {

	// constructor
	function __construct() {

		// Add the admin menu
		add_action( 'admin_menu', array( &$this, 'add_menu' ) );
		add_action( 'network_admin_menu', array( &$this, 'add_network_admin_menu' ) );

		// Add the script and style files
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_styles' ) );

		// Try to detect plugins that embed their own jQuery and jQuery UI
		// libraries and load them in NGG's admin pages
		add_action( 'admin_enqueue_scripts', array( &$this, 'buffer_scripts' ), 0 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'output_scripts' ), PHP_INT_MAX );

		add_filter( 'current_screen', array( &$this, 'edit_current_screen' ) );

		// Add WPML hook to register description / alt text for translation
		add_action( 'ngg_image_updated', array( 'nggGallery', 'RegisterString' ) );

	}

	/**
	 * If a NGG page is being requested, we buffer any rendering of <script>
	 * tags to detect conflicts and remove them if need be
	 */
	function buffer_scripts() {
		// Is this a NGG admin page?
		if ( isset( $_REQUEST['page'] ) && strpos( $_REQUEST['page'], 'nggallery' ) !== false ) {
			ob_start();
		}
	}

	function output_scripts() {
		// Is this a NGG admin page?
		if ( isset( $_REQUEST['page'] ) && strpos( $_REQUEST['page'], 'nggallery' ) !== false ) {
			$plugin_folder = NGGFOLDER;
			$skipjs_count  = 0;
			$html          = ob_get_contents();
			ob_end_clean();

			if ( ! defined( 'NGG_JQUERY_CONFLICT_DETECTION' ) ) {
				define( 'NGG_JQUERY_CONFLICT_DETECTION', true );
			}

			if ( NGG_JQUERY_CONFLICT_DETECTION ) {
				// Detect custom jQuery script
				if ( preg_match_all( "/<script.*wp-content.*jquery[-_\.](min\.)?js.*<\script>/", $html, $matches, PREG_SET_ORDER ) ) {
					foreach ( $matches as $match ) {
						$old_script = array_shift( $match );
						if ( strpos( $old_script, NGGFOLDER ) === false ) {
							$html = str_replace( $old_script, '', $html );
						}
					}
				}

				// Detect custom jQuery UI script and remove
				if ( preg_match_all( "/<script.*wp-content.*jquery[-_\.]ui.*<\/script>/", $html, $matches, PREG_SET_ORDER ) ) {
					$detected_jquery_ui = true;
					foreach ( $matches as $match ) {
						$old_script = array_shift( $match );
						if ( strpos( $old_script, NGGFOLDER ) === false ) {
							$html = str_replace( $old_script, '', $html );
						}
					}
				}

				if ( isset( $_REQUEST['skipjs'] ) ) {
					foreach ( $_REQUEST['skipjs'] as $js ) {
						$js = preg_quote( $js );
						if ( preg_match_all( "#<script.*{$js}.*</script>#", $html, $matches, PREG_SET_ORDER ) ) {
							foreach ( $matches as $match ) {
								$old_script = array_shift( $match );
								if ( strpos( $old_script, NGGFOLDER ) === false ) {
									$html = str_replace( $old_script, '', $html );
								}
							}
						}
					}
					$skipjs_count = count( $_REQUEST['skipjs'] );
				}


				// Use WordPress built-in version of jQuery
				$jquery_url = includes_url( 'js/jquery/jquery.js' );
				$html       = implode( '', array(
					"<script type='text/javascript' src='{$jquery_url}'></script>\n",
					"<script type='text/javascript'>
					window.onerror = function(msg, url, line){
						if (url.match(/\.js$|\.js\?/)) {
							if (window.location.search.length > 0) {
								if (window.location.search.indexOf(url) == -1)
									window.location.search += '&skipjs[{$skipjs_count}]='+url;
							}
							else {
								window.location.search = '?skipjs[{$skipjs_count}]='+url;
							}
						}
						return true;
					};</script>\n",
					$html
				) );
			}

			echo $html;
		}
	}



    /**
     * Enable dash icons for WP latest versions. See https://developer.wordpress.org/resource/dashicons/#format-gallery
     * @param $wp_version  defaults to current WP version
     * @return string
     */

    function get_icon_gallery($wp_version='') {
        if (empty($wp_version)) {
            $wp_version= get_bloginfo( 'version' ) ; //get WP Version
        }
        if ( $wp_version >= 3.8 ) {
            return 'dashicons-format-gallery'; //new style
        }
        //older style
        return path_join( NGGALLERY_URLPATH, 'admin/images/nextgen_16_color.png' );
    }
    /**
     * Integrate the menu
     *
     */
	function add_menu() {
        add_menu_page( __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ),
                       'NextGEN Gallery overview', NGGFOLDER, array(&$this,'show_menu'),  $this->get_icon_gallery());

		add_submenu_page( NGGFOLDER, __( 'Overview', 'nggallery' ), __( 'Overview', 'nggallery' ), 'NextGEN Gallery overview',
            NGGFOLDER, array(&$this,'show_menu'		) );

		add_submenu_page( NGGFOLDER, __( 'Add Gallery / Images', 'nggallery' ), __( 'Add Gallery / Images', 'nggallery' ), 'NextGEN Upload images' , 'nggallery-add-gallery',
            array( &$this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Galleries', 'nggallery' )           , __( 'Galleries', 'nggallery' )           , 'NextGEN Manage gallery', 'nggallery-manage-gallery',
            array( &$this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Albums', 'nggallery' )              , __( 'Albums', 'nggallery' )              , 'NextGEN Edit album'    , 'nggallery-manage-album',
            array( &$this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Tags', 'nggallery' )                , __( 'Tags', 'nggallery' )                , 'NextGEN Manage tags'   , 'nggallery-tags',
            array( &$this, 'show_menu' ) );

		add_submenu_page( NGGFOLDER, __( 'Settings', 'nggallery' )            , __( 'Settings', 'nggallery' )            , 'NextGEN Change options', 'nggallery-options',
            array( &$this, 'show_menu' ) );

		if ( wpmu_enable_function( 'wpmuStyle' ) ) {
			add_submenu_page( NGGFOLDER, __( 'Style', 'nggallery' ), __( 'Style', 'nggallery' ), 'NextGEN Change style', 'nggallery-style',
            array( &$this, 'show_menu'	) );
		}
		if ( wpmu_enable_function( 'wpmuRoles' ) || is_super_admin() ) {
			add_submenu_page( NGGFOLDER, __( 'Roles', 'nggallery' ), __( 'Roles', 'nggallery' ), 'activate_plugins', 'nggallery-roles',
                array( &$this, 'show_menu' ) );
		}

		if ( ! is_multisite() || is_super_admin() ) {
			add_submenu_page( NGGFOLDER, __( 'Reset / Uninstall', 'nggallery' ), __( 'Reset / Uninstall', 'nggallery' ), 'activate_plugins', 'nggallery-setup',
                array( &$this, 'show_menu' ) );
		}

		//register the column fields
		$this->register_columns();
	}

	// integrate the network menu
	function add_network_admin_menu() {
        add_menu_page( __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ), 'nggallery-wpmu',
                       NGGFOLDER, array(&$this,'show_network_settings'), $this->get_icon_gallery() );

		add_submenu_page( NGGFOLDER, __( 'Network settings', 'nggallery' ), __( 'Network settings', 'nggallery' ), 'nggallery-wpmu',
                          NGGFOLDER, array(&$this, 'show_network_settings' ) );

		add_submenu_page( NGGFOLDER, __( 'Reset / Uninstall', 'nggallery' ), __( 'Reset / Uninstall', 'nggallery' ), 'activate_plugins',
                        'nggallery-setup', array(&$this, 'show_menu') );
	}

	/**
	 * Maybe show an upgrade page.
	 */
	private function show_upgrade_page() {

		global $ngg;

		// check for upgrade and show upgrade screen
		if ( get_option( 'ngg_db_version' ) != NGG_DBVERSION ) {
			include_once( dirname( __FILE__ ) . '/functions.php' );
			include_once( dirname( __FILE__ ) . '/upgrade.php' );
			nggallery_upgrade_page();

			exit;
		}
	}

	// show the network page
	function show_network_settings() {
		$this->show_upgrade_page();
		include_once( dirname( __FILE__ ) . '/style.php' );
		include_once( dirname( __FILE__ ) . '/wpmu.php' );
		nggallery_wpmu_setup();
	}

	// load the script for the defined page and load only this code
	//20140515: removed donation code (not in use)
	function show_menu() {

		global $ngg;

		$this->show_upgrade_page();

		// Set installation date
		if ( empty( $ngg->options['installDate'] ) ) {
			$ngg->options['installDate'] = time();
			update_option( 'ngg_options', $ngg->options );
		}

		switch ( $_GET['page'] ) {
			case "nggallery-add-gallery" :
				include_once( dirname( __FILE__ ) . '/functions.php' );        // admin functions
				include_once( dirname( __FILE__ ) . '/addgallery.php' );    // nggallery_admin_add_gallery
				$ngg->addgallery_page = new nggAddGallery ();
				$ngg->addgallery_page->controller();
				break;
			case "nggallery-manage-gallery" :
				include_once( dirname( __FILE__ ) . '/functions.php' );    // admin functions
				include_once( dirname( __FILE__ ) . '/manage.php' );    // nggallery_admin_manage_gallery
				// Initate the Manage Gallery page
				$ngg->manage_page = new nggManageGallery ();
				// Render the output now, because you cannot access a object during the constructor is not finished
				$ngg->manage_page->controller();
				break;
			case "nggallery-manage-album" :
				include_once( dirname( __FILE__ ) . '/album.php' );        // nggallery_admin_manage_album
				$ngg->manage_album = new nggManageAlbum ();
				$ngg->manage_album->controller();
				break;
			case "nggallery-options" :
				include_once( dirname( __FILE__ ) . '/settings.php' );    // nggallery_admin_options
				$ngg->option_page = new nggOptions ();
				$ngg->option_page->show_page();
				break;
			case "nggallery-tags" :
				include_once( dirname( __FILE__ ) . '/tags.php' );        // nggallery_admin_tags
				break;
			case "nggallery-style" :
				include_once( dirname( __FILE__ ) . '/style.php' );        // nggallery_admin_style
				$ngg->nggallery_style = new NGG_Style ();
				$ngg->nggallery_style->controller();
				break;
			case "nggallery-setup" :
				include_once( dirname( __FILE__ ) . '/setup.php' );        // nggallery_admin_setup
				nggallery_admin_setup();
				break;
			case "nggallery-roles" :
				include_once( dirname( __FILE__ ) . '/roles.php' );        // nggallery_admin_roles
				nggallery_admin_roles();
				break;
			case "nggallery-import" :
				include_once( dirname( __FILE__ ) . '/myimport.php' );    // nggallery_admin_import
				nggallery_admin_import();
				break;
			case "nggallery" :
			default :
				include_once( dirname( __FILE__ ) . '/overview.php' );    // nggallery_admin_overview
				$output = new Overview_Display();
				$output->display();
				break;
		}
	}

	function load_scripts() {
		global $wp_version;

		// no need to go on if it's not a plugin page
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		wp_register_script( 'ngg-ajax', NGGALLERY_URLPATH . 'admin/js/ngg.ajax.js', array( 'jquery' ), '1.4.1' );
		wp_localize_script( 'ngg-ajax', 'nggAjaxSetup', array(
			'url'        => admin_url( 'admin-ajax.php' ),
			'action'     => 'ngg_ajax_operation',
			'operation'  => '',
			'nonce'      => wp_create_nonce( 'ngg-ajax' ),
			'ids'        => '',
			'permission' => __( 'You do not have the correct permission', 'nggallery' ),
			'error'      => __( 'Unexpected Error', 'nggallery' ),
			'failure'    => __( 'A failure occurred', 'nggallery' )
		) );
		wp_register_script( 'ngg-plupload-handler', NGGALLERY_URLPATH . 'admin/js/plupload.handler.js', array( 'plupload-all' ), '0.0.1' );
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
		wp_register_script( 'ngg-progressbar', NGGALLERY_URLPATH . 'admin/js/ngg.progressbar.js', array( 'jquery' ), '2.0.1' );
        wp_register_script( 'ngg-autocomplete', NGGALLERY_URLPATH . 'admin/js/ngg.autocomplete.js', array( 'jquery-ui-autocomplete' ), '1.1' );

		switch ( $_GET['page'] ) {
			case NGGFOLDER :
				add_thickbox();
				wp_enqueue_script( 'postbox' );
				break;
			case "nggallery-manage-gallery" :
				wp_enqueue_script( 'postbox' );
				wp_enqueue_script( 'ngg-ajax' );
				wp_enqueue_script( 'ngg-progressbar' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_register_script( 'shutter', NGGALLERY_URLPATH . 'shutter/shutter-reloaded.js', false, '1.3.2' );
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
                wp_enqueue_script( 'ngg-autocomplete');
				break;
			case "nggallery-add-gallery" :
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'ngg-plupload-handler' );
				wp_enqueue_script( 'ngg-ajax' );
				wp_enqueue_script( 'ngg-progressbar' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jqueryFileTree', NGGALLERY_URLPATH . 'admin/js/jqueryFileTree/jqueryFileTree.js', array( 'jquery' ), '1.0.1' );
				break;
			case "nggallery-style" :
				wp_enqueue_script( 'codepress' );
				break;

		}
	}

    /**
     * Load the icon for the navigation menu
     */
	function load_styles() {
		wp_register_style( 'nggadmin'    , NGGALLERY_URLPATH . 'admin/css/nggadmin.css', false, '2.8.1', 'screen' );
		wp_register_style( 'ngg-jqueryui', NGGALLERY_URLPATH . 'admin/css/jquery.ui.css', false, '1.8.5', 'screen' );

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
				wp_enqueue_style( 'jqueryFileTree', NGGALLERY_URLPATH . 'admin/js/jqueryFileTree/jqueryFileTree.css', false, '1.0.1', 'screen' );
			case "nggallery-options" :
				wp_enqueue_style( 'nggtabs', NGGALLERY_URLPATH . 'admin/css/jquery.ui.tabs.css', false, '2.5.0', 'screen' );
				wp_enqueue_style( 'nggadmin' );
				wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_style( 'ngg-jqueryui' );
				break;
			case "nggallery-manage-gallery" :
				wp_enqueue_style( 'shutter', NGGALLERY_URLPATH . 'shutter/shutter-reloaded.css', false, '1.3.2', 'screen' );
				wp_enqueue_style( 'datepicker', NGGALLERY_URLPATH . 'admin/css/jquery.ui.datepicker.css', false, '1.8.2', 'screen' );
			case "nggallery-roles" :
			case "nggallery-manage-album" :
				wp_enqueue_style( 'ngg-jqueryui' );
				wp_enqueue_style( 'nggadmin' );
				break;
			case "nggallery-tags" :
				wp_enqueue_style( 'nggtags', NGGALLERY_URLPATH . 'admin/css/tags-admin.css', false, '2.6.1', 'screen' );
				break;
			case "nggallery-style" :
				break;
		}
	}

	/**
	 * Add help and options to the correct screens
	 *
	 * @since 1.9.24
	 *
	 * @param object $screen The current screen.
	 *
	 * @return object $screen The current screen.
	 */
	function edit_current_screen( $screen ) {

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
				$help .= '<p><strong>' . __( 'At a Glance', 'nggallery' ) . '</strong> - ' . __( 'Shows some general information about your site, such as the number of pictures, albums and galleries.', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Latest News', 'nggallery' ) . '</strong> - ' . __( 'The latest NextCellent news.', 'nggallery' ) . '</p>';
				if ( ! is_multisite() || is_super_admin() ) {
					$help .= '<p><strong>' . __( 'Related plugins', 'nggallery' ) . '</strong> - ' . __( 'Shows plugins that extend NextCellent.', 'nggallery' ) . ' <strong>' . __( 'Pay attention', 'nggallery' ) . '</strong>: ' . __( 'third parties plugins that are compatible with NGG may not be 100% compatible with NextCellent Gallery!', 'nggallery' ) . '</p>';
				}
				$help .= '<p><strong>' . __( 'Help me help YOU!', 'nggallery' ) . '</strong> - ' . __( 'Shows general information about he plugin and some links.', 'nggallery' ) . '</p>';
				if ( ! ( get_locale() == 'en_US' ) ) {
					$help .= '<p><strong>' . __( 'Translation', 'nggallery' ) . '</strong> - ' . __( 'View information about the current translation.' ) . '</p>';
				}
				if ( ! is_multisite() || is_super_admin() ) {
					$help .= '<p><strong>' . __( 'Server Settings', 'nggallery' ) . '</strong> - ' . __( 'Show all the server settings!.', 'nggallery' ) . '</p>';
					$help .= '<p><strong>' . __( 'Plugin Check', 'nggallery' ) . '</strong> - ' . __( 'Check if there are known errors in your installation.', 'nggallery' ) . '</p>';
				}
				//Add the tab
				$screen->add_help_tab( array(
					'id'      => $screen->id . '-content',
					'title'   => 'Content',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-add-gallery" :

				global $nggdb;
				$gallerylist = $nggdb->find_all_galleries( 'gid', 'DESC' ); //look for galleries

				$help = '<p>' . __( 'On this page you can add galleries and pictures to those galleries.', 'nggallery' ) . '</p>';
				if ( nggGallery::current_user_can( 'NextGEN Add new gallery' ) ) {
					$help .= '<p><strong>' . __( 'New gallery', 'nggallery' ) . '</strong> - ' . __( 'Add new galleries to NextCellent.', 'nggallery' ) . '</p>';
				}
				if ( empty ( $gallerylist ) ) {
					$help .= '<p><strong>' . __( 'You must add a gallery before adding images!', 'nggallery' ) . '</strong>';
				} else {
					$help .= '<p><strong>' . __( 'Images', 'nggallery' ) . '</strong> - ' . __( 'Add new images to a gallery.', 'nggallery' ) . '</p>';
				}
				if ( wpmu_enable_function( 'wpmuZipUpload' ) && nggGallery::current_user_can( 'NextGEN Upload a zip' ) && ! empty ( $gallerylist ) ) {
					$help .= '<p><strong>' . __( 'ZIP file', 'nggallery' ) . '</strong> - ' . __( 'Add images from a ZIP file.', 'nggallery' ) . '</p>';
				}
				if ( wpmu_enable_function( 'wpmuImportFolder' ) && nggGallery::current_user_can( 'NextGEN Import image folder' ) ) {
					$help .= '<p><strong>' . __( 'Import folder', 'nggallery' ) . '</strong> - ' . __( 'Import a folder from the server as a new gallery.', 'nggallery' ) . '</p>';
				}

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Add things',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-manage-gallery" :
				// we would like to have screen option only at the manage images / gallery page
				if ( ( isset( $_GET['mode'] ) && $_GET['mode'] == 'edit' ) || isset ( $_POST['backToGallery'] ) ) {
					$screen->base = $screen->id = 'nggallery-manage-images';
				} else {
					$screen->base = $screen->id = 'nggallery-manage-gallery';
				}

				$help = '<p>' . __( 'Manage your images and galleries.', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Manage everything',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-manage-album" :
				$help = '<p>' . __( 'Organize your galleries into albums.', 'nggallery' ) . '</p><p>' . __( 'First select an album from the dropdown and then drag the galleries you want to add or remove from the selected album.', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Organize everything',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-tags" :
				$help = '<p>' . __( 'Organize your pictures with tags.', 'nggallery' ) . '</p><p>' . __( 'Rename, delete and edit tags. Use the rename function to merge tags.', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Organize pictures',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-options" :
				$help = '<p>' . __( 'Edit all of NextCellent\'s options. The options are sorted in multiple categories.', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'General', 'nggallery' ) . '</strong> - ' . __( 'General NextCellent options. Contains options for permalinks and related images.', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Images', 'nggallery' ) . '</strong> - ' . __( 'All image-related options. Also contains options for thumbnails.', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Gallery', 'nggallery' ) . '</strong> - ' . __( 'Everything about galleries. From sorting options to the number of images, it\'s all in here.', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Effects', 'nggallery' ) . '</strong> - ' . __( 'Make your gallery look beautiful.', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Watermark', 'nggallery' ) . '</strong> - ' . __( 'Who doesn\'t want theft-proof images?', 'nggallery' ) . '</p>';
				$help .= '<p><strong>' . __( 'Slideshow', 'nggallery' ) . '</strong> - ' . __( 'Edit options for the slideshow.', 'nggallery' ) . '</p>';
				$help .= '<p>' . __( 'Don\'t forget to press save!', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Edit options',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-style" :
				$help = '<p>' . __( 'You can edit the css file to adjust how your gallery looks.', 'nggallery' ) . '</p>';
				$help .= '<p>' . __( 'When you save an edited file, NextCellent automatically saves it as a copy in the folder ngg_styles. This protects your changes from upgrades.', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Style your gallery',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-roles" :
				$help = '<p>' . __( 'You can assign the lowest user role that has access to a certain feature. Needless to say, all greater user roles will also have access to that feature.', 'nggallery' ) . '</p>';
				$help .= '<p>' . __( 'NextCellent also works with various plugins that extend the default roles capabilities.', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Grant permissions',
					'content' => $help
				) );
				break;
			case "{$i18n}_page_nggallery-setup" :
				$help = '<p>' . __( 'If \'someone\' messed with your settings (yeah, definitely not you), you can reset them here.', 'nggallery' ) . '</p>';
				$help .= '<p><b>' . __( 'Attention!', 'nggallery' ) . '</b> ' .  __( 'You should not use the Uninstall Plugin button, unless you know what you\'re doing! It should never be necessary to press it.', 'nggallery' ) . '</p>';

				$screen->add_help_tab( array(
					'id'      => $screen->id . '-general',
					'title'   => 'Reset',
					'content' => $help
				) );
				break;
		}

		//Set the sidebar (same on all pages)
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'nggallery' ) . '</strong></p>' .
			'<p><a href="http://codex.wordpress.org/Plugins_Editor_Screen" target="_blank">' . __( 'Support Forums', 'nggallery' ) . '</a></p>' .
			'<p><a href="https://bitbucket.org/wpgetready/nextcellent" target="_blank">' . __( 'Source Code', 'nggallery' ) . '</a></p>'
		);

		return $screen;
	}

	/**
	 * We need to register the columns at a very early point
	 *
	 * @return void
	 */
	function register_columns() {
		include_once( dirname( __FILE__ ) . '/manage-images.php' );

		$wp_list_table = new _NGG_Images_List_Table( 'nggallery-manage-images' );

		include_once( dirname( __FILE__ ) . '/manage-galleries.php' );

		$wp_list_table = new _NGG_Galleries_List_Table( 'nggallery-manage-gallery' );
	}
}

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
