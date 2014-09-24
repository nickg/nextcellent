<?php
/**
 * ngg_upgrade() - update routine for older version
 * 
 * @return Success message
 */
function ngg_upgrade() {
	
	global $wpdb, $user_ID, $nggRewrite;

	// get the current user ID
	get_currentuserinfo();
    
    // in multisite environment the pointer $wpdb->nggpictures need to be set again
	$wpdb->nggpictures					= $wpdb->prefix . 'ngg_pictures';
	$wpdb->nggallery					= $wpdb->prefix . 'ngg_gallery';
	$wpdb->nggalbum						= $wpdb->prefix . 'ngg_album';
    
    // Be sure that the tables exist, avoid case sensitive : http://dev.mysql.com/doc/refman/5.1/en/identifier-case-sensitivity.html
	if( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->nggpictures'" ) ) {

		echo __('Upgrading database…', 'nggallery');
		$wpdb->show_errors();

		$installed_ver = get_option( 'ngg_db_version' );
		
		// v1.8.1 -> v1.8.2
		if (version_compare($installed_ver, '1.8.2', '<')) {
			//get options
			$ngg_options = get_option('ngg_options');
			
			//update them
			$ngg_options['CSSfile']		= NGGALLERY_ABSPATH . 'css/nggallery.css'; 
			
			//save them
			update_option('ngg_options', $ngg_options);
		} 
      
		// update the database version
		update_option( "ngg_db_version", NGG_DBVERSION );
		echo '<span class="dashicons dashicons-yes"></span><br />';
        
        // better to flush rewrite rules after upgrades
        $nggRewrite->flush();
		return;
	}
    
    echo __('Could not find NextCellent Gallery database tables, upgrade failed!', 'nggallery');
    
    return;
}

/**
 * nggallery_upgrade_page() - This page shows up , when the database version doesn't fit the script NGG_DBVERSION constant.
 * 
 * @return Upgrade Message
 */
function nggallery_upgrade_page()  {
    
	$filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];
	
	if ( isset($_GET['upgrade']) && $_GET['upgrade'] == 'now') {
		nggallery_start_upgrade($filepath);
		return;
	}
?>
<div class="wrap">
	<h2><?php _e('Upgrade NextCellent Gallery', 'nggallery') ;?></h2>
	<p><?php _e('You\'re upgrading from an older version. To enable the newest features, we sometimes need to do a database upgrade.', 'nggallery'); ?></p>
	<p class="description"><span style="margin-right:5px;" class="dashicons dashicons-info"></span><?php _e('Normally you should be able to downgrade without any problems, but if you really want to play safe, you should make a backup of your database.', 'nggallery') ;?></p>
	<br /><a class="button button-primary" href="<?php echo $filepath;?>&amp;upgrade=now"><?php _e('Start upgrade now', 'nggallery'); ?></a>
</div>
<?php
}

/**
 * nggallery_start_upgrade() - Proceed the upgrade routine
 * 
 * @param mixed $filepath
 * @return void
 */
function nggallery_start_upgrade($filepath) {
?>
<div class="wrap">
	<h2><?php _e('Upgrade NextCellent Gallery', 'nggallery') ;?></h2>
	<p><?php ngg_upgrade();?></p>
	<p class="finished"><?php _e('Upgrade complete.', 'nggallery') ;?></p>
	<br /><a class="finished button button-primary" href="<?php echo $filepath;?>"><?php _e('Continue to NextCellent', 'nggallery'); ?></a>
</div>
<?php
}
?>