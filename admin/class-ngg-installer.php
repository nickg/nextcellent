<?php

/**
 * Class NGG_Installer
 *
 * Installs and removes the NextCellent database tables and options.
 */
class NGG_Installer {

	/**
	 * Create all tables and options.
	 *
	 * This function is called during the activation hook.
	 */
	public static function install() {

		global $wpdb;

		// Check for capability
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Set the capabilities for the administrator
		$role = get_role( 'administrator' );
		// We need this role, no other chance
		if ( empty( $role ) ) {
			update_option( "ngg_init_check",
				__( 'Sorry, NextCellent Gallery works only with a role called administrator', "nggallery" ) );

			return;
		}

		$role->add_cap( 'NextGEN Gallery overview' );
		$role->add_cap( 'NextGEN Use TinyMCE' );
		$role->add_cap( 'NextGEN Upload images' );
		$role->add_cap( 'NextGEN Manage gallery' );
		$role->add_cap( 'NextGEN Manage tags' );
		$role->add_cap( 'NextGEN Manage others gallery' );
		$role->add_cap( 'NextGEN Edit album' );
		$role->add_cap( 'NextGEN Change style' );
		$role->add_cap( 'NextGEN Change options' );

		// upgrade function changed in WordPress 2.3
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// add charset & collate like wp core
		$charset_collate = '';

		if ( version_compare( $wpdb->get_var( "SELECT VERSION() AS `mysql_version`" ), '4.1.0', '>=' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}

		$nggpictures = $wpdb->prefix . 'ngg_pictures';
		$nggallery   = $wpdb->prefix . 'ngg_gallery';
		$nggalbum    = $wpdb->prefix . 'ngg_album';

		// Create pictures table
		$sql = "CREATE TABLE " . $nggpictures . " (
	pid BIGINT(20) NOT NULL AUTO_INCREMENT ,
	image_slug VARCHAR(255) NOT NULL ,
	post_id BIGINT(20) DEFAULT '0' NOT NULL ,
	galleryid BIGINT(20) DEFAULT '0' NOT NULL ,
	filename VARCHAR(255) NOT NULL ,
	description MEDIUMTEXT NULL ,
	alttext MEDIUMTEXT NULL ,
	imagedate DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	exclude TINYINT NULL DEFAULT '0' ,
	sortorder BIGINT(20) DEFAULT '0' NOT NULL ,
	meta_data LONGTEXT,
	PRIMARY KEY  (pid),
	KEY post_id (post_id)
	) $charset_collate;";
		dbDelta( $sql );

		// Create gallery table
		$sql = "CREATE TABLE " . $nggallery . " (
	gid BIGINT(20) NOT NULL AUTO_INCREMENT ,
	name VARCHAR(255) NOT NULL ,
	slug VARCHAR(255) NOT NULL ,
	path MEDIUMTEXT NULL ,
	title MEDIUMTEXT NULL ,
	galdesc MEDIUMTEXT NULL ,
	pageid BIGINT(20) DEFAULT '0' NOT NULL ,
	previewpic BIGINT(20) DEFAULT '0' NOT NULL ,
	author BIGINT(20) DEFAULT '0' NOT NULL  ,
	PRIMARY KEY  (gid)
	) $charset_collate;";
		dbDelta( $sql );

		// Create albums table
		$sql = "CREATE TABLE " . $nggalbum . " (
	id BIGINT(20) NOT NULL AUTO_INCREMENT ,
	name VARCHAR(255) NOT NULL ,
	slug VARCHAR(255) NOT NULL ,
	previewpic BIGINT(20) DEFAULT '0' NOT NULL ,
	albumdesc MEDIUMTEXT NULL ,
	sortorder LONGTEXT NOT NULL,
	pageid BIGINT(20) DEFAULT '0' NOT NULL,
	PRIMARY KEY  (id)
	) $charset_collate;";
		dbDelta( $sql );

		// check one table again, to be sure
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$nggpictures'" ) ) {
			update_option( "ngg_init_check",
				__( 'NextCellent Gallery : Tables could not be created, please check your database settings',
					"nggallery" ) );

			return;
		}

		$options = get_option( 'ngg_options' );
		// set the default settings, if we didn't upgrade
		if ( empty( $options ) ) {
			NGG_Installer::set_default_options();
		}

		// if all is passed , save the DBVERSION
		add_option( "ngg_db_version", NGG_DBVERSION );

	}

	/**
	 * Setup the default option array for NextCellent.
	 * When adding new options, an upgrade should be made for existing users as well.
	 * @see NGG_Upgrader
	 */
	public static function set_default_options() {

		global $blog_id;

		$ngg_options['gallerypath']    = 'wp-content/gallery/';        // set default path to the gallery
		$ngg_options['deleteImg']      = true;                            // delete Images
		$ngg_options['swfUpload']      = true;                            // activate the batch upload
		$ngg_options['usePermalinks']  = false;                        // use permalinks for parameters
		$ngg_options['permalinkSlug']  = 'nggallery';                  // the default slug for permalinks
		$ngg_options['graphicLibrary'] = 'gd';                            // default graphic library
		$ngg_options['imageMagickDir'] = '/usr/local/bin/';            // default path to ImageMagick
		$ngg_options['useMediaRSS']    = false;                        // activate the global Media RSS file
		$ngg_options['usePicLens']     = false;                        // activate the PicLens Link for galleries
		$ngg_options['silentUpdate']   = false;                        //If the database should be updated silently.

		// Tags / categories
		$ngg_options['activateTags'] = false;                        // append related images
		$ngg_options['appendType']   = 'tags';                        // look for category or tags
		$ngg_options['maxImages']    = 7;                            // number of images toshow

		// Thumbnail Settings
		$ngg_options['thumbwidth']   = 100;                        // Thumb Width
		$ngg_options['thumbheight']  = 75;                            // Thumb height
		$ngg_options['thumbfix']     = true;                            // Fix the dimension
		$ngg_options['thumbquality'] = 100;                        // Thumb Quality

		// Image Settings
		$ngg_options['imgWidth']      = 800;                        // Image Width
		$ngg_options['imgHeight']     = 600;                        // Image height
		$ngg_options['imgQuality']    = 85;                            // Image Quality
		$ngg_options['imgBackup']     = true;                            // Create a backup
		$ngg_options['imgAutoResize'] = false;                        // Resize after upload

		// Gallery Settings
		$ngg_options['galImages']         = 20;                            // Number of images per page
		$ngg_options['galPagedGalleries'] = 0;                            // Number of galleries per page (in a album)
		$ngg_options['galColumns']        = 0;                            // Number of columns for the gallery
		$ngg_options['galShowSlide']      = true;                            // Show slideshow
		$ngg_options['galTextSlide']      = __( '[Show as slideshow]', 'nggallery' ); // Text for slideshow
		$ngg_options['galTextGallery']    = __( '[Show picture list]', 'nggallery' ); // Text for gallery
		$ngg_options['galShowOrder']      = 'gallery';                    // Show order
		$ngg_options['galSort']           = 'sortorder';                    // Sort order
		$ngg_options['galSortDir']        = 'ASC';                        // Sort direction
		$ngg_options['galNoPages']        = true;                            // use no subpages for gallery
		$ngg_options['galImgBrowser']     = false;                        // Show ImageBrowser, instead effect
		$ngg_options['galHiddenImg']      = false;                        // For paged galleries we can hide image
		$ngg_options['galAjaxNav']        = false;                        // AJAX Navigation for Shutter effect

		// Thumbnail Effect
		$ngg_options['thumbEffect'] = 'shutter';                    // select effect
		$ngg_options['thumbCode']   = 'class="shutterset_%GALLERY_NAME%"';

		// Watermark settings
		$ngg_options['wmPos']    = 'botRight';                    // Postion
		$ngg_options['wmXpos']   = 5;                            // X Pos
		$ngg_options['wmYpos']   = 5;                            // Y Pos
		$ngg_options['wmType']   = 'text';                        // Type : 'image' / 'text'
		$ngg_options['wmPath']   = '';                            // Path to image
		$ngg_options['wmFont']   = 'arial.ttf';                // Font type
		$ngg_options['wmSize']   = 10;                            // Font Size
		$ngg_options['wmText']   = get_option( 'blogname' );        // Text
		$ngg_options['wmColor']  = '000000';                    // Font Color
		$ngg_options['wmOpaque'] = '100';                        // Font Opaque

		// Slideshow settings
		$ngg_options['slideFx']          = 'fadeIn';                     //The effect
		$ngg_options['irWidth']          = 320;                          //Width (in px)
		$ngg_options['irHeight']         = 240;                          //Height (in px)
		$ngg_options['irAutoDim']        = true;                         //Automatically set the dimensions.
		$ngg_options['irRotatetime']     = 3;                            //Duration (in seconds)
		$ngg_options['irLoop']           = true;                         //Loop or not
		$ngg_options['irDrag']           = true;                         //Enable drag or not
		$ngg_options['irNavigation']     = false;                        //Show navigation
		$ngg_options['irNavigationDots'] = false;                        //Show navigation dots
		$ngg_options['irAutoplay']       = true;                         //Autoplay
		$ngg_options['irAutoplayHover']  = true;                         //Pause on hover
		$ngg_options['irNumber']         = 20;                           //Number of images when random or latest
		$ngg_options['irClick']          = true;                         //Go to next on click.

		// CSS Style
		$ngg_options['activateCSS'] = true;                            // activate the CSS file
		$ngg_options['CSSfile']     = NGGALLERY_ABSPATH . 'css/nggallery.css';            // set default css filename

		// special overrides for WPMU
		if ( is_multisite() ) {
			// get the site options
			$ngg_wpmu_options = get_site_option( 'ngg_options' );

			// get the default value during first installation
			if ( ! is_array( $ngg_wpmu_options ) ) {
				$ngg_wpmu_options['gallerypath']  = 'wp-content/blogs.dir/%BLOG_ID%/files/';
				$ngg_wpmu_options['wpmuCSSfile']  = 'nggallery.css';
				$ngg_wpmu_options['silentUpdate'] = false;
				update_site_option( 'ngg_options', $ngg_wpmu_options );
			}

			$ngg_options['gallerypath'] = str_replace( "%BLOG_ID%", $blog_id, $ngg_wpmu_options['gallerypath'] );
			$ngg_options['CSSfile']     = $ngg_wpmu_options['wpmuCSSfile'];
		}
        $ngg_options['silentUpgrade']    = false;
        $ngg_options['thumbDifferentSize']    = false;

		update_option( 'ngg_options', $ngg_options );

	}

	/**
	 * Deregister a capability from all classic roles
	 *
	 * @access internal
	 *
	 * @param string $capability name of the capability which should be deregister
	 *
	 * @return void
	 */
	private static function ngg_remove_capability( $capability ) {
		// this function remove the $capability only from the classic roles
		$check_order = array( "subscriber", "contributor", "author", "editor", "administrator" );

		foreach ( $check_order as $role ) {

			$role = get_role( $role );
			$role->remove_cap( $capability );
		}

	}

	/**
	 * Uninstall all tables, settings and capabilities.
	 *
	 * This function is called during the uninstall hook and from the setup page.
	 */
	public static function uninstall() {
		global $wpdb;

		//First remove all tables.
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ngg_pictures" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ngg_gallery" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ngg_album" );

		//Then remove all options.
		delete_option( 'ngg_options' );
		delete_option( 'ngg_db_version' );
		delete_option( 'ngg_update_exists' );
		delete_option( 'ngg_next_update' );

		//As last item, we remove all capabilities.
		NGG_Installer::ngg_remove_capability( "NextGEN Gallery overview" );
		NGG_Installer::ngg_remove_capability( "NextGEN Use TinyMCE" );
		NGG_Installer::ngg_remove_capability( "NextGEN Upload images" );
		NGG_Installer::ngg_remove_capability( "NextGEN Manage gallery" );
		NGG_Installer::ngg_remove_capability( "NextGEN Edit album" );
		NGG_Installer::ngg_remove_capability( "NextGEN Change style" );
		NGG_Installer::ngg_remove_capability( "NextGEN Change options" );
	}

}