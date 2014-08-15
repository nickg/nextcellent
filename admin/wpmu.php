<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	function nggallery_wpmu_setup()  {	
	
	//to be sure
	if ( !is_super_admin() )
 		die('You are not allowed to call this page.');

    $messagetext = '';

	// get the options
	$ngg_options = get_site_option('ngg_options');

	if ( isset($_POST['updateoption']) ) {	
		check_admin_referer('ngg_wpmu_settings');
		// get the hidden option fields, taken from WP core
		if ( $_POST['page_options'] )	
			$options = explode(',', stripslashes($_POST['page_options']));
		if ($options) {
			foreach ($options as $option) {
				$option = trim($option);
				$value = isset($_POST[$option]) ? trim($_POST[$option]) : false;
		//		$value = sanitize_option($option, $value); // This does strip slashes on those that need it
				$ngg_options[$option] = $value;
			}
		}

        // the path should always end with a slash	
        $ngg_options['gallerypath']    = trailingslashit($ngg_options['gallerypath']);
		update_site_option('ngg_options', $ngg_options);
        
	 	$messagetext = __('Update successfully','nggallery');
	}		

    // Show donation message only one time.
        /*
    if (isset ( $_GET['hideSupportInfo']) ) {
    	$ngg_options['hideSupportInfo'] = true;
    	update_site_option('ngg_options', $ngg_options);			
    }
        */
	
	// message windows
	if( !empty($messagetext) ) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }
	
	?>

	<div class="wrap">
		<h2><?php _e('Network Options','nggallery'); ?></h2>
		<form name="generaloptions" method="post">
		<?php wp_nonce_field('ngg_wpmu_settings') ?>
		<input type="hidden" name="page_options" value="gallerypath,wpmuQuotaCheck,wpmuZipUpload,wpmuImportFolder,wpmuStyle,wpmuRoles,wpmuCSSfile" />
			<table class="form-table">
				<tr valign="top">
					<th align="left"><?php _e('Gallery path','nggallery') ?></th>
					<td><input type="text" size="50" name="gallerypath" value="<?php echo $ngg_options['gallerypath']; ?>" />
					<p class="description"><?php _e('This is the default path for all blogs. With the placeholder %BLOG_ID% you can organize the folder structure better.','nggallery') ?>
                    <?php echo str_replace('%s', '<code>wp-content/blogs.dir/%BLOG_ID%/files/</code>', __('The default setting should be %s', 'nggallery')); ?>.</p>
                    </td>
				</tr>
				<tr>
					<th valign="top"><?php _e('Enable upload quota check','nggallery') ?>:</th>
					<td><input name="wpmuQuotaCheck" id="wpmuQuotaCheck" type="checkbox" value="1" <?php checked('1', $ngg_options['wpmuQuotaCheck']); ?> />
					<label for="wpmuQuotaCheck"><?php _e('Should work if the gallery is bellow the blog.dir','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php _e('Enable zip upload option','nggallery') ?>:</th>
					<td><input name="wpmuZipUpload" id="wpmuZipUpload" type="checkbox" value="1" <?php checked('1', $ngg_options['wpmuZipUpload']); ?> />
					<label for="wpmuZipUpload"><?php _e('Allow users to upload zip folders.','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php _e('Enable import function','nggallery') ?>:</th>
					<td><input name="wpmuImportFolder" id="wpmuImportFolder" type="checkbox" value="1" <?php checked('1', $ngg_options['wpmuImportFolder']); ?> />
					<label for="wpmuImportFolder"><?php _e('Allow users to import images folders from the server.','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php _e('Enable style selection','nggallery') ?>:</th>
					<td><input name="wpmuStyle" id="wpmuStyle" type="checkbox" value="1" <?php checked('1', $ngg_options['wpmuStyle']); ?> />
					<label for="wpmuStyle"><?php _e('Allow users to choose a style for the gallery.','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php _e('Enable roles/capabilities','nggallery') ?>:</th>
					<td><input name="wpmuRoles" id="wpmuRoles" type="checkbox" value="1" <?php checked('1', $ngg_options['wpmuRoles']); ?> />
					<label for="wpmuRoles"><?php _e('Allow users to change the roles for other blog authors.','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php _e('Default style','nggallery') ?>:</th>
					<td>
					<select name="wpmuCSSfile">
					<?php
						$csslist = ngg_get_cssfiles();
						foreach ($csslist as $key =>$a_cssfile) {
							$css_name = $a_cssfile['Name'];
							if ($key == $ngg_options['wpmuCSSfile']) {
								$file_show = $key;
								$selected = " selected='selected'";
							}
							else $selected = '';
							$css_name = esc_attr($css_name);
							echo "\n\t<option value=\"$key\" $selected>$css_name</option>";
						}
					?>
					</select>
					<p class="description"><?php _e('Choose the default style for the galleries.','nggallery') ?></p>
					</td>
				</tr>
			</table> 				
			<div class="submit"><input type="submit" name="updateoption" class="button button-primary" value="<?php _e('Save Changes') ;?>"/></div>
		</form>	
	</div>	

	<?php
}
