<?php
/**
 * Upgrade the NextCellent database.
 * @throws Exception If the database could not be found.
 */
function ngg_upgrade() {

	global $wpdb;


	// in multisite environment the pointer $wpdb->nggpictures need to be set again
	$wpdb->nggpictures = $wpdb->prefix . 'ngg_pictures';
	$wpdb->nggallery   = $wpdb->prefix . 'ngg_gallery';
	$wpdb->nggalbum    = $wpdb->prefix . 'ngg_album';

	// Be sure that the tables exist, avoid case sensitive : http://dev.mysql.com/doc/refman/5.1/en/identifier-case-sensitivity.html
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->nggpictures'" ) ) {

		$wpdb->show_errors();

		$installed_ver = get_option( 'ngg_db_version' );
		$ngg_options   = get_option( 'ngg_options' );

		// v1.8.1 -> v1.8.2
		if ( version_compare( $installed_ver, '1.8.2', '<' ) ) {

			//update them
			$ngg_options['CSSfile'] = NGGALLERY_ABSPATH . 'css/nggallery.css';

			//save them
			update_option( 'ngg_options', $ngg_options );
		}

		// v1.8.2 -> v1.8.3
		if ( version_compare( $installed_ver, '1.8.3', '<' ) ) {

			$booleans = array(
				'deleteImg',
				'swfUpload',
				'usePermalinks',
				'useMediaRSS',
				'usePicLens',
				'activateTags',
				'thumbfix',
				'imgBackup',
				'imgAutoResize',
				'galImages',
				'galShowSlide',
				'galNoPages',
				'galImgBrowser',
				'galHiddenImg',
				'galAjaxNav'
			);

			foreach ( $booleans as $value ) {
				//Convert strings to primitive types in database to booleans
				if ( $ngg_options[ $value ] === "1" ) {
					$ngg_options[ $value ] = true;
				}
			}

			$ngg_options['galImages'] = (int) $ngg_options['galImages'];


			//Add new slideshow parameters
			$ngg_options['irLoop']           = true;
			$ngg_options['irDrag']           = true;
			$ngg_options['irNavigation']     = false;
			$ngg_options['irNavigationDots'] = false;
			$ngg_options['irAutoplay']       = true;
			$ngg_options['irAutoDim']        = false;
			$ngg_options['irAutoplayHover']  = true;
			$ngg_options['irNumber']         = 20;
			$ngg_options['irClick']          = true;
			$ngg_options['silentUpgrade']    = false;
			$ngg_options['thumbDifferentSize'] = false;


			//Convert color
			$ngg_options['wmColor'] = '#' . $ngg_options['wmColor'];

			//Delete the old ones
			unset( $ngg_options['enableIR'], $ngg_options['irURL'], $ngg_options['irXHTMLvalid'], $ngg_options['irAudio'], $ngg_options['irShuffle'], $ngg_options['irLinkfromdisplay'], $ngg_options['irShownavigation'], $ngg_options['irShowicons'], $ngg_options['irWatermark'], $ngg_options['irOverstretch'], $ngg_options['irTransition'], $ngg_options['irKenburns'], $ngg_options['irBackcolor'], $ngg_options['irFrontcolor'], $ngg_options['irLightcolor'], $ngg_options['irScreencolor'] );

			//Update the options.
			update_option( 'ngg_options', $ngg_options );

		}

		// update the database version
		update_option( "ngg_db_version", NGG_DBVERSION );

	} else {
		throw new Exception( __( 'Could not find NextCellent Gallery database tables, upgrade failed!', 'nggallery' ) );
	}
}

/**
 * Show the first step to update the database.
 */
function nggallery_upgrade_page()  {

	$filepath = admin_url() . 'admin.php?page=' . $_GET['page']; //default upgrade path
	if ( is_network_admin() ) {                                  //unless if it is network  administrator...
		$filepath = network_admin_url() . 'admin.php?page=' . $_GET['page'];
	}

	if ( isset($_GET['upgrade']) && $_GET['upgrade'] == 'now') {
		doing_update_output($filepath);
		return;
	}
	?>
	<div class="wrap">
		<h2><?php _e( 'Upgrade NextCellent Gallery', 'nggallery' ); ?></h2>

		<p><?php _e( 'You\'re upgrading from an older version. To enable the newest features, we sometimes need to do a database upgrade.', 'nggallery' ); ?></p>

		<p class="description">
			<span style="margin-right:5px;" class="dashicons dashicons-info"></span>
			<?php _e( 'Normally you should be able to downgrade without any problems, but if you really want to play safe, you should make a backup of your database.', 'nggallery' ); ?>
		</p>
		<br/>
		<a class="button button-primary"
		   href="<?php echo $filepath; ?>&amp;upgrade=now"><?php _e( 'Start upgrade now', 'nggallery' ); ?></a>
	</div>
	<?php
}

/**
 * Actually update the database, with a UI.
 *
 * @param $filepath string The URL to the overview page.
 */
function doing_update_output( $filepath ) {
	?>
	<div class="wrap">
		<h2><?php _e( 'Upgrade NextCellent Gallery', 'nggallery' ); ?></h2>

		<p><?php
			try {
				echo __( 'Upgrading database…', 'nggallery' );
				ngg_upgrade();
				echo '<span class="dashicons dashicons-yes"></span><br />';
			} catch ( ErrorException $e ) {
				echo __( "Oh no! Something went wrong while updating the database", 'nggallery' ) . ": " . $e;
			}
			?></p>

		<p class="finished"><?php _e( 'Upgrade complete.', 'nggallery' ); ?></p>
		<br/><a class="finished button button-primary"
		        href="<?php echo $filepath; ?>"><?php _e( 'Continue to NextCellent', 'nggallery' ); ?></a>
	</div>
	<?php
}

?>