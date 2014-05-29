<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * Rebuild slugs for albums, galleries and images via AJAX request
 *
 * @sine 1.7.0
 * @access internal
 */
class ngg_rebuild_unique_slugs {

	function start_rebuild() {
        global $wpdb;

        $total = array();
        // get the total number of images
		$total['images'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures") );
        $total['gallery'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggallery") );
        $total['album'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggalbum") );

		$messages = array(
			'images' => __( 'Rebuild image structure : %s / %s images', 'nggallery' ),
			'gallery' => __( 'Rebuild gallery structure : %s / %s galleries', 'nggallery' ),
            'album' => __( 'Rebuild album structure : %s / %s albums', 'nggallery' ),
		);

        foreach ( array_keys( $messages ) as $key ) {

    		$message = sprintf( $messages[ $key ] ,
    			"<span class='ngg-count-current'>0</span>",
    			"<span class='ngg-count-total'>" . $total[ $key ] . "</span>"
    		);

    		echo "<div class='$key updated'><p class='ngg'>$message</p></div>";
        }

		$ajax_url = add_query_arg( 'action', 'ngg_rebuild_unique_slugs', admin_url( 'admin-ajax.php' ) );
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	var ajax_url = '<?php echo $ajax_url; ?>',
		_action = 'images',
		images = <?php echo $total['images']; ?>,
		gallery = <?php echo $total['gallery']; ?>,
        album = <?php echo $total['album']; ?>,
        total = 0,
        offset = 0,
		count = 50;

	var $display = $('.ngg-count-current');
    $('.finished, .gallery, .album').hide();
    total = images;

	function call_again() {
		if ( offset > total ) {
		    offset = 0;
            // 1st run finished
            if (_action == 'images') {
                _action = 'gallery';
                total = gallery;
                $('.images, .gallery').toggle();
                $display.html(offset);
                call_again();
                return;
            }
            // 2nd run finished
            if (_action == 'gallery') {
                _action = 'album';
                total = album;
                $('.gallery, .album').toggle();
                $display.html(offset);
                call_again();
                return;
            }
            // 3rd run finished, exit now
            if (_action == 'album') {
    			$('.ngg')
    				.html('<?php esc_html_e( 'Done.', 'nggallery' ); ?>')
    				.parent('div').hide();
                $('.finished').show();
    			return;
            }
		}

		$.post(ajax_url, {'_action': _action, 'offset': offset}, function(response) {
			$display.html(offset);

			offset += count;
			call_again();
		});
	}

	call_again();
});
</script>
<?php
	}
}

class nggOptions {

    /**
     * nggOptions::__construct()
     *
     * @return void
     */
    function __construct() {

       	// same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
	   $this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];

  		//Look for POST updates
		if ( !empty($_POST) )
			$this->processor();
    }

	/**
	 * Save/Load options and add a new hook for plugins
	 *
	 * @return void
	 */
	function processor() {

    	global $ngg, $nggRewrite;

    	$old_state = $ngg->options['usePermalinks'];
        $old_slug  = $ngg->options['permalinkSlug'];

    	if ( isset($_POST['irDetect']) ) {
    		check_admin_referer('ngg_settings');
    		$ngg->options['irURL'] = ngg_search_imagerotator();
    		update_option('ngg_options', $ngg->options);
    	}

    	if ( isset($_POST['updateoption']) ) {
    		check_admin_referer('ngg_settings');
    		// get the hidden option fields, taken from WP core
    		if ( $_POST['page_options'] )
    			$options = explode(',', stripslashes($_POST['page_options']));

    		if ($options) {
    			foreach ($options as $option) {
    				$option = trim($option);
    				$value = isset($_POST[$option]) ? trim($_POST[$option]) : false;
    		//		$value = sanitize_option($option, $value); // This does stripslashes on those that need it
    				$ngg->options[$option] = $value;
    			}

                // do not allow a empty string
                if ( empty ( $ngg->options['permalinkSlug'] ) )
                    $ngg->options['permalinkSlug'] = 'nggallery';

        		// the path should always end with a slash
        		$ngg->options['gallerypath']    = trailingslashit($ngg->options['gallerypath']);
        		$ngg->options['imageMagickDir'] = trailingslashit($ngg->options['imageMagickDir']);

        		// the custom sortorder must be ascending
        		$ngg->options['galSortDir'] = ($ngg->options['galSort'] == 'sortorder') ? 'ASC' : $ngg->options['galSortDir'];
    		}
    		// Save options
    		update_option('ngg_options', $ngg->options);

    		// Flush Rewrite rules
    		if ( $old_state != $ngg->options['usePermalinks'] || $old_slug != $ngg->options['permalinkSlug'] )
    			$nggRewrite->flush();

    	 	nggGallery::show_message(__('Settings updated successfully','nggallery'));
    	}

    	if ( isset($_POST['clearcache']) ) {
    		check_admin_referer('ngg_settings');

    		$path = WINABSPATH . $ngg->options['gallerypath'] . 'cache/';

    		if (is_dir($path))
    	    	if ($handle = opendir($path)) {
    				while (false !== ($file = readdir($handle))) {
    			    	if ($file != '.' && $file != '..') {
    			          @unlink($path . '/' . $file);
    	          		}
    	        	}
    	      		closedir($handle);
    			}

    		nggGallery::show_message(__('Cache cleared','nggallery'));
    	}

    	if ( isset($_POST['createslugs']) ) {
    		check_admin_referer('ngg_settings');
            ngg_rebuild_unique_slugs::start_rebuild();
    	}

        do_action( 'ngg_update_options_page' );

    }

    /**
     * Render the page content
     *
     * @return void
     */
    function controller() {

        // get list of tabs
        $tabs = $this->tabs_order();

	?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
		    jQuery('html,body').scrollTop(0);
            jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' });
			jQuery('#slider').css('display', 'block');
		});

		function insertcode(value) {
			var effectcode;
			switch (value) {
			  case "none":
			    effectcode = "";
			    jQuery('#tbImage').hide("slow");
			    break;
			  case "thickbox":
			    effectcode = 'class="thickbox" rel="%GALLERY_NAME%"';
			    jQuery('#tbImage').show("slow");
			    break;
			  case "lightbox":
			    effectcode = 'rel="lightbox[%GALLERY_NAME%]"';
			    jQuery('#tbImage').hide("slow");
			    break;
			  case "highslide":
			    effectcode = 'class="highslide" onclick="return hs.expand(this, { slideshowGroup: %GALLERY_NAME% })"';
			    jQuery('#tbImage').hide("slow");
			    break;
			  case "shutter":
			    effectcode = 'class="shutterset_%GALLERY_NAME%"';
			    jQuery('#tbImage').hide("slow");
			    break;
			  default:
			    break;
			}
			jQuery("#thumbCode").val(effectcode);
		};

		function setcolor(fileid, color) {
			jQuery(fileid).css("background-color", '#' + color );
		};
	</script>
	<div class="wrap ngg-wrap">
	<?php screen_icon( 'nextgen-gallery' ); ?>
	<h2><?php esc_html_e('Settings', 'nggallery') ?></h2>
	</div>
	<div id="slider" class="wrap" style="display: none;">
        <ul id="tabs">
            <?php
        	foreach($tabs as $tab_key => $tab_name) {
        	   echo "\n\t\t<li><a class='nav-tab' href='#$tab_key'>$tab_name</a></li>";
            }
            ?>
		</ul>
        <?php
        foreach($tabs as $tab_key => $tab_name) {
            echo "\n\t<div id='$tab_key'>\n";
            // Looks for the internal class function, otherwise enable a hook for plugins
            if ( method_exists( $this, "tab_$tab_key" ))
                call_user_func( array( &$this , "tab_$tab_key") );
            else
                do_action( 'ngg_tab_content_' . $tab_key );
             echo "\n\t</div>";
        }
        ?>
    </div>
    <?php

    }

    /**
     * Create array for tabs and add a filter for other plugins to inject more tabs
     *
     * @return array $tabs
     */
    function tabs_order() {

    	$tabs = array();

    	$tabs['generaloptions'] = __('General', 'nggallery');
    	$tabs['images'] = __('Images', 'nggallery');
    	$tabs['gallery'] = __( 'Gallery', 'nggallery' );
    	$tabs['effects'] = __('Effects', 'nggallery');
    	$tabs['watermark'] = __('Watermark', 'nggallery');
    	$tabs['slideshow'] = __('Slideshow', 'nggallery');

    	$tabs = apply_filters('ngg_settings_tabs', $tabs);

    	return $tabs;

    }

    function tab_generaloptions() {
        global $ngg;

    ?>
        <!-- General Options -->
		<h3><?php esc_html_e('General settings','nggallery'); ?></h3>
		<form name="generaloptions" method="post" action="<?php echo $this->filepath; ?>">
		<?php wp_nonce_field('ngg_settings') ?>
		<input type="hidden" name="page_options" value="gallerypath,deleteImg,useMediaRSS,usePicLens,usePermalinks,permalinkSlug,graphicLibrary,imageMagickDir,activateTags,appendType,maxImages" />
			<table class="form-table ngg-options">
				<tr valign="top">
					<th align="left"><?php esc_html_e('Gallery path','nggallery'); ?></th>
					<td><input <?php if (is_multisite()) echo 'readonly = "readonly"'; ?> type="text" class="regular-text code" name="gallerypath" value="<?php echo $ngg->options['gallerypath']; ?>" />
					<p class="description"><?php esc_html_e('This is the default path for all galleries','nggallery') ?></p></td>
				</tr>
				<tr valign="top">
					<th align="left"><?php esc_html_e('Image files','nggallery'); ?></th>
					<td><input <?php if (is_multisite()) echo 'readonly = "readonly"'; ?> type="checkbox" name="deleteImg" value="1" <?php checked('1', $ngg->options['deleteImg']); ?> />
					<?php esc_html_e('Delete files when removing a gallery from the database','nggallery'); ?></td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Select graphic library','nggallery'); ?></th>
					<td><label><input name="graphicLibrary" type="radio" value="gd" <?php checked('gd', $ngg->options['graphicLibrary']); ?> /> <?php esc_html_e('GD Library', 'nggallery') ;?></label><br />
					<label><input name="graphicLibrary" type="radio" value="im" <?php checked('im', $ngg->options['graphicLibrary']); ?> /> <?php esc_html_e('ImageMagick (Experimental)', 'nggallery') ;?></label><br/>
					<?php esc_html_e('Path to the library:', 'nggallery') ;?>&nbsp;
					<input <?php if (is_multisite()) echo 'readonly = "readonly"'; ?> type="text" class="regular-text code" name="imageMagickDir" value="<?php echo $ngg->options['imageMagickDir']; ?>" />
					</td>
				</tr>
				<tr>
					<th align="left"><?php esc_html_e('Media RSS feed','nggallery'); ?></th>
					<td><input type="checkbox" name="useMediaRSS" value="1" <?php checked('1', $ngg->options['useMediaRSS']); ?> />
					<span><?php esc_html_e('Add a RSS feed to you blog header. Useful for CoolIris/PicLens','nggallery') ?></span></td>
				</tr>
				<tr>
					<th align="left"><?php esc_html_e('PicLens/CoolIris','nggallery'); ?> (<a href="http://www.cooliris.com">CoolIris</a>)</th>
					<td><input type="checkbox" name="usePicLens" value="1" <?php checked('1', $ngg->options['usePicLens']); ?> />
					<?php esc_html_e('Include support for PicLens and CoolIris','nggallery') ?>
					<p class="description"><?php esc_html_e('When activated, JavaScript is added to your site footer. Make sure that wp_footer is called in your theme.','nggallery') ?></p></td>
					</td>
				</tr>
				</table>
				<h3><?php esc_html_e('Permalinks','nggallery') ?></h3>
				<table class="form-table ngg-options">
				<tr valign="top">
					<th align="left"><?php esc_html_e('Use permalinks','nggallery'); ?></th>
					<td><input type="checkbox" name="usePermalinks" value="1" <?php checked('1', $ngg->options['usePermalinks']); ?> />
					<?php esc_html_e('Adds a static link to all images','nggallery'); ?>
					<p class="description"><?php esc_html_e('When activating this option, you need to update your permalink structure once','nggallery'); ?></p></td>
				</tr>
				<tr>
					<td>
                    <p><?php esc_html_e('Gallery slug:','nggallery'); ?></p></td>
                    <td><input type="text" class="regular-text code" name="permalinkSlug" value="<?php echo $ngg->options['permalinkSlug']; ?>" /></td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Recreate URLs','nggallery'); ?></th>
					<td><input type="submit" name="createslugs" class="button-secondary"  value="<?php esc_attr_e('Start now','nggallery') ;?> &raquo;"/>
					<p class="description"><?php esc_html_e('If you\'ve changed these settings, you\'ll have to recreate the URLs.','nggallery'); ?></p></td>
				</tr>
			</table>
			<h3><?php esc_html_e('Related images','nggallery'); ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th valign="top"><?php esc_html_e('Add related images','nggallery'); ?></th>
					<td><input name="activateTags" type="checkbox" value="1" <?php checked('1', $ngg->options['activateTags']); ?> />
					<?php esc_html_e('This will add related images to every post','nggallery'); ?>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Match with','nggallery'); ?></th>
					<td><label><input name="appendType" type="radio" value="category" <?php checked('category', $ngg->options['appendType']); ?> /> <?php esc_html_e('Categories', 'nggallery') ;?></label><br />
					<label><input name="appendType" type="radio" value="tags" <?php checked('tags', $ngg->options['appendType']); ?> /> <?php esc_html_e('Tags', 'nggallery') ;?></label>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Max. number of images','nggallery'); ?></th>
					<td><input name="maxImages" type="number" step="1" min="1" value="<?php echo $ngg->options['maxImages']; ?>" class="small-text" />
					<p class="description"><?php esc_html_e('0 will show all images','nggallery'); ?></p>
					</td>
				</tr>
			</table>
		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php esc_attr_e('Save Changes'); ?>"/></div>
		</form>
    <?php
    }

    function tab_images() {
        global $ngg;
    ?>
		<!-- Image settings -->
		<h3><?php esc_html_e('Image settings','nggallery'); ?></h3>
		<form name="imagesettings" method="POST" action="<?php echo $this->filepath.'#images'; ?>" >
		<?php wp_nonce_field('ngg_settings') ?>
		<input type="hidden" name="page_options" value="imgResize,imgWidth,imgHeight,imgQuality,imgBackup,imgAutoResize,thumbwidth,thumbheight,thumbfix,thumbquality" />
			<table class="form-table ngg-options">
				<tr valign="top">
					<th valign="top"><?php esc_html_e('Resize images','nggallery') ?></th>
					<td><label for="imgWidth"><?php esc_html_e('Width','nggallery') ?></label>
					<input type="number" step="1" min="0" class="small-text" name="imgWidth" class="small-text" value="<?php echo $ngg->options['imgWidth']; ?>" />
					<label for="imgHeight"><?php esc_html_e('Height','nggallery') ?></label>
					<input type="number" step="1" min="0" type="text" size="5" name="imgHeight" class="small-text" value="<?php echo $ngg->options['imgHeight']; ?>">
					<p class="description"><?php esc_html_e('Width and height (in pixels). NextCellent Gallery will keep the ratio size.','nggallery') ?></p></td>
				</tr>
				<tr valign="top">
					<th valign="top"><?php esc_html_e('Image quality','nggallery'); ?></th>
					<td><input type="number" step="1" min="0" max="100" class="small-text" name="imgQuality" value="<?php echo $ngg->options['imgQuality']; ?>" />
					<label for="imgQuality">%</label></td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Backup original','nggallery'); ?></th>
					<td><input type="checkbox" name="imgBackup" value="1"<?php echo ($ngg->options['imgBackup'] == 1) ? ' checked ="chechked"' : ''; ?>/>
					<span><?php esc_html_e('Create a backup for the resized images','nggallery'); ?></span></td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Automatically resize','nggallery'); ?></th>
					<td><input type="checkbox" name="imgAutoResize" value="1"<?php echo ($ngg->options['imgAutoResize'] == 1) ? ' checked ="chechked"' : ''; ?>/>
					<span><?php esc_html_e('Automatically resize images on upload.','nggallery') ?></span></td>
				</tr>
			</table>
		<!-- Thumbnail settings -->
		<h3><?php esc_html_e('Thumbnail settings','nggallery'); ?></h3>
			<p class="description"><?php esc_html_e('Please note: if you change these settings, you need to recreate the thumbnails under -> Manage Gallery .', 'nggallery') ?></p>
			<table class="form-table ngg-options">
				<tr valign="top">
					<th align="left"><?php esc_html_e('Thumbnail size','nggallery'); ?></th>
					<td>
					<label for="thumbwidth"><?php esc_html_e('Width','nggallery') ?></label>
					<input type="number" step="1" min="0" class="small-text" name="thumbwidth" value="<?php echo $ngg->options['thumbwidth']; ?>" />
					<label for="thumbheight"><?php esc_html_e('Height','nggallery') ?></label>
					<input type="number" step="1" min="0" class="small-text" name="thumbheight" value="<?php echo $ngg->options['thumbheight']; ?>" />
					<p class="description"><?php esc_html_e('These values are maximum values ','nggallery'); ?></p></td>
				</tr>
				<tr valign="top">
					<th align="left"><?php esc_html_e('Fixed size','nggallery'); ?></th>
					<td><input type="checkbox" name="thumbfix" value="1" <?php checked('1', $ngg->options['thumbfix']); ?> />
					<?php esc_html_e('This will ignore the aspect ratio, so no portrait thumbnails','nggallery') ?></td>
				</tr>
				<tr valign="top">
					<th align="left"><?php esc_html_e('Thumbnail quality','nggallery'); ?></th>
					<td><input type="number" step="1" min="0" max="100" class="small-text" name="thumbquality" value="<?php echo $ngg->options['thumbquality']; ?>" /><label for="thumbquality">%</label></td>
				</tr>
			</table>
			<h3><?php esc_html_e('Single picture','nggallery') ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th valign="top"><?php esc_html_e('Clear cache folder','nggallery'); ?></th>
					<td><input type="submit" name="clearcache" class="button-secondary"  value="<?php esc_attr_e('Proceed now','nggallery') ;?> &raquo;"/></td>
				</tr>
			</table>
		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php esc_attr_e('Save Changes') ;?>"/></div>
		</form>

    <?php
    }

    function tab_gallery() {
        global $ngg;
    ?>
		<!-- Gallery settings -->
		<h3><?php esc_html_e('Gallery settings','nggallery'); ?></h3>
		<form name="galleryform" method="POST" action="<?php echo $this->filepath.'#gallery'; ?>" >
		<?php wp_nonce_field('ngg_settings') ?>
		<input type="hidden" name="page_options" value="galNoPages,galImages,galColumns,galShowSlide,galTextSlide,galTextGallery,galShowOrder,galImgBrowser,galSort,galSortDir,galHiddenImg,galAjaxNav" />
			<table class="form-table ngg-options">
				<tr>
					<th valign="top"><?php esc_html_e('Inline gallery','nggallery') ?></th>
					<td><input name="galNoPages" type="checkbox" value="1" <?php checked('1', $ngg->options['galNoPages']); ?> />
					<?php esc_html_e('Galleries will not be shown on a subpage, but on the same page.','nggallery') ?>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Images per page','nggallery') ?></th>
					<td><input type="number" step="1" min="0" class="small-text" name="galImages" value="<?php echo $ngg->options['galImages']; ?>" />
					<label for="galImages">images</label>
					<p class="description"><?php esc_html_e('0 will disable pagination, all images on one page','nggallery') ?></p>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Columns','nggallery'); ?></th>
					<td><input type="number" step="1" min="0" class="small-text" name="galColumns" value="<?php echo $ngg->options['galColumns']; ?>" />
					<label for="galColumns">columns per page</label>
					<p class="description"><?php esc_html_e('0 will display as much columns as possible. This is normally only required for captions below the images.','nggallery') ?></p>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Slideshow','nggallery'); ?></th>
					<td><input name="galShowSlide" type="checkbox" value="1" <?php checked('1', $ngg->options['galShowSlide']); ?> /> <?php esc_html_e('Enable slideshow','nggallery'); ?><br/><?php esc_html_e('Text to show:','nggallery'); ?>
						<input type="text" class="regular-text" name="galTextSlide" value="<?php echo $ngg->options['galTextSlide'] ?>" />
						<input type="text" name="galTextGallery" value="<?php echo $ngg->options['galTextGallery'] ?>" class="regular-text"/>
						<p class="description"> <?php esc_html_e('This is the text the visitors will have to click to switch between display modes.','nggallery'); ?></p>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Show first','nggallery'); ?></th>
					<td><label><input name="galShowOrder" type="radio" value="gallery" <?php checked('gallery', $ngg->options['galShowOrder']); ?> /> <?php esc_html_e('Thumbnails', 'nggallery') ;?></label><br />
					<label><input name="galShowOrder" type="radio" value="slide" <?php checked('slide', $ngg->options['galShowOrder']); ?> /> <?php esc_html_e('Slideshow', 'nggallery') ;?></label>
					<p class="description">Choose what the visitors will see first.</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('ImageBrowser','nggallery'); ?></th>
					<td><input name="galImgBrowser" type="checkbox" value="1" <?php checked('1', $ngg->options['galImgBrowser']); ?> />
					<?php esc_html_e('Use ImageBrowser instead of another effect.', 'nggallery'); ?>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Hidden images','nggallery'); ?></th>
					<td><input name="galHiddenImg" type="checkbox" value="1" <?php checked('1', $ngg->options['galHiddenImg']); ?> />
					<?php esc_html_e('Loads all images for the modal window, when pagination is used (like Thickbox, Lightbox etc.).','nggallery'); ?>
					<p class="description"> <?php esc_html_e('Note: this increases the page load (possibly a lot)', 'nggallery'); ?>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('AJAX pagination','nggallery'); ?></th>
					<td><input name="galAjaxNav" type="checkbox" value="1" <?php checked('1', $ngg->options['galAjaxNav']); ?> />
					<?php esc_html_e('Use AJAX pagination to browse images without reloading the page.','nggallery'); ?> 
					<p class="description"><?php esc_html_e('Note: works only in combination with the Shutter effect.', 'nggallery'); ?> </p>
					</td>
				</tr>
			</table>
			<h3><?php esc_html_e('Sort options','nggallery') ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th valign="top"><?php esc_html_e('Sort thumbnails','nggallery') ?></th>
					<td>
					<label><input name="galSort" type="radio" value="sortorder" <?php checked('sortorder', $ngg->options['galSort']); ?> /> <?php esc_html_e('Custom order', 'nggallery') ;?></label><br />
					<label><input name="galSort" type="radio" value="pid" <?php checked('pid', $ngg->options['galSort']); ?> /> <?php esc_html_e('Image ID', 'nggallery') ;?></label><br />
					<label><input name="galSort" type="radio" value="filename" <?php checked('filename', $ngg->options['galSort']); ?> /> <?php esc_html_e('File name', 'nggallery') ;?></label><br />
					<label><input name="galSort" type="radio" value="alttext" <?php checked('alttext', $ngg->options['galSort']); ?> /> <?php esc_html_e('Alt / Title text', 'nggallery') ;?></label><br />
					<label><input name="galSort" type="radio" value="imagedate" <?php checked('imagedate', $ngg->options['galSort']); ?> /> <?php esc_html_e('Date / Time', 'nggallery') ;?></label>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Sort direction','nggallery') ?></th>
					<td><label><input name="galSortDir" type="radio" value="ASC" <?php checked('ASC', $ngg->options['galSortDir']); ?> /> <?php esc_html_e('Ascending', 'nggallery') ;?></label><br />
					<label><input name="galSortDir" type="radio" value="DESC" <?php checked('DESC', $ngg->options['galSortDir']); ?> /> <?php esc_html_e('Descending', 'nggallery') ;?></label>
					</td>
				</tr>
			</table>
		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php esc_attr_e('Save Changes') ;?>"/></div>
		</form>
    <?php
    }

    function tab_effects() {
        global $ngg;
    ?>
		<!-- Effects settings -->
		<h3><?php esc_html_e('Effects','nggallery'); ?></h3>
		<form name="effectsform" method="POST" action="<?php echo $this->filepath.'#effects'; ?>" >
		<?php wp_nonce_field('ngg_settings') ?>
		<input type="hidden" name="page_options" value="thumbEffect,thumbCode" />
		<p><?php esc_html_e('Here you can select the thumbnail effect, NextCellent Gallery will integrate the required HTML code in the images. Please note that only the Shutter and Thickbox effect will automatic added to your theme.','nggallery'); ?>
		<?php esc_html_e('With the placeholder','nggallery'); ?><strong> %GALLERY_NAME% </strong> <?php esc_html_e('you can activate a navigation through the images (depend on the effect). Change the code line only , when you use a different thumbnail effect or you know what you do.','nggallery'); ?></p>
			<table class="form-table ngg-options">
				<tr valign="top">
					<th><?php esc_html_e('JavaScript Thumbnail effect','nggallery') ?></th>
					<td>
					<select size="1" id="thumbEffect" name="thumbEffect" onchange="insertcode(this.value)">
						<option value="none" <?php selected('none', $ngg->options['thumbEffect']); ?> ><?php esc_html_e('None', 'nggallery') ;?></option>
						<option value="thickbox" <?php selected('thickbox', $ngg->options['thumbEffect']); ?> ><?php esc_html_e('Thickbox', 'nggallery') ;?></option>
						<option value="lightbox" <?php selected('lightbox', $ngg->options['thumbEffect']); ?> ><?php esc_html_e('Lightbox', 'nggallery') ;?></option>
						<option value="highslide" <?php selected('highslide', $ngg->options['thumbEffect']); ?> ><?php esc_html_e('Highslide', 'nggallery') ;?></option>
						<option value="shutter" <?php selected('shutter', $ngg->options['thumbEffect']); ?> ><?php esc_html_e('Shutter', 'nggallery') ;?></option>
						<option value="custom" <?php selected('custom', $ngg->options['thumbEffect']); ?> ><?php esc_html_e('Custom', 'nggallery') ;?></option>
					</select>
					</td>
				</tr>
				<tr valign="top">
					<th><?php esc_html_e('Link Code line','nggallery') ?></th>
					<td><textarea id="thumbCode" name="thumbCode" cols="50" rows="5"><?php echo htmlspecialchars(stripslashes($ngg->options['thumbCode'])); ?></textarea></td>
				</tr>
			</table>
		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php esc_attr_e('Save Changes') ;?>"/></div>
		</form>

    <?php
    }

    function tab_watermark() {

        global $wpdb, $ngg;

        // take the first image as sample
        $imageID  = $wpdb->get_var("SELECT MIN(pid) FROM $wpdb->nggpictures");
        $imageURL = ($imageID) ? $imageURL = '<img src="'. home_url('index.php') . '?callback=image&amp;pid=' . intval ($imageID) . '&amp;mode=watermark&amp;width=300&amp;height=250" />' : '';

	?>
	<!-- Watermark settings -->
		<h3><?php esc_html_e('Watermark','nggallery'); ?></h3>
		<p><?php esc_html_e('Please note : you can only activate the watermark under -> Manage Galleries . This action cannot be undone.', 'nggallery') ?></p>
		<form name="watermarkform" method="POST" action="<?php echo $this->filepath.'#watermark'; ?>" >
		<?php wp_nonce_field('ngg_settings') ?>
		<input type="hidden" name="page_options" value="wmPos,wmXpos,wmYpos,wmType,wmPath,wmFont,wmSize,wmColor,wmText,wmOpaque" />
		<div id="wm-preview">
			<h3><?php esc_html_e('Preview','nggallery') ?></h3>
			<p style="text-align:center;"><?php echo $imageURL; ?></p>
			<h3><?php esc_html_e('Position','nggallery') ?></h3>
			<div>
			    <table id="wm-position">
				<tr>
					<td valign="top">
						<strong><?php esc_html_e('Position','nggallery') ?></strong>
						<table border="1">
							<tr>
								<td><input type="radio" name="wmPos" value="topLeft" <?php checked('topLeft', $ngg->options['wmPos']); ?> /></td>
								<td><input type="radio" name="wmPos" value="topCenter" <?php checked('topCenter', $ngg->options['wmPos']); ?> /></td>
								<td><input type="radio" name="wmPos" value="topRight" <?php checked('topRight', $ngg->options['wmPos']); ?> /></td>
							</tr>
							<tr>
								<td><input type="radio" name="wmPos" value="midLeft" <?php checked('midLeft', $ngg->options['wmPos']); ?> /></td>
								<td><input type="radio" name="wmPos" value="midCenter" <?php checked('midCenter', $ngg->options['wmPos']); ?> /></td>
								<td><input type="radio" name="wmPos" value="midRight" <?php checked('midRight', $ngg->options['wmPos']); ?> /></td>
							</tr>
							<tr>
								<td><input type="radio" name="wmPos" value="botLeft" <?php checked('botLeft', $ngg->options['wmPos']); ?> /></td>
								<td><input type="radio" name="wmPos" value="botCenter" <?php checked('botCenter', $ngg->options['wmPos']); ?> /></td>
								<td><input type="radio" name="wmPos" value="botRight" <?php checked('botRight', $ngg->options['wmPos']); ?> /></td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<strong><?php esc_html_e('Offset','nggallery') ?></strong>
						<table border="0">
							<tr>
								<td>x:</td>
								<td><input type="number" step="1" min="0" class="small-text" name="wmXpos" value="<?php echo $ngg->options['wmXpos'] ?>" /><label for="wmXpos">px</label></td>
							</tr>
							<tr>
								<td>y:</td>
								<td><input type="number" step="1" min="0" class="small-text" name="wmYpos" value="<?php echo $ngg->options['wmYpos'] ?>" /><label for="wmYpos">px</label></td>
							</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
		</div>
			<h3><label><input type="radio" name="wmType" value="image" <?php checked('image', $ngg->options['wmType']); ?> /> <?php esc_html_e('Use image as watermark','nggallery') ?></label></h3>
			<table class="wm-table form-table">
				<tr>
					<th><?php esc_html_e('URL to file','nggallery') ?></th>
					<td><input type="text" size="40" name="wmPath" value="<?php echo $ngg->options['wmPath']; ?>" /><br />
					<?php if(!ini_get('allow_url_fopen')) esc_html_e('The accessing of URL files is disabled at your server (allow_url_fopen)','nggallery') ?> </td>
				</tr>
			</table>
			<h3><label><input type="radio" name="wmType" value="text" <?php checked('text', $ngg->options['wmType']); ?> /> <?php esc_html_e('Use text as watermark','nggallery') ?></label></h3>
			<table class="wm-table form-table">
				<tr>
					<th><?php esc_html_e('Font','nggallery') ?></th>
					<td><select name="wmFont" size="1">	<?php
							$fontlist = ngg_get_TTFfont();
							foreach ( $fontlist as $fontfile ) {
								echo "\n".'<option value="'.$fontfile.'" '.ngg_input_selected($fontfile, $ngg->options['wmFont']).' >'.$fontfile.'</option>';
							}
							?>
						</select><br /><span>
						<?php if ( !function_exists('ImageTTFBBox') )
								_e('This function will not work, cause you need the FreeType library','nggallery');
							  else
							  	_e('You can upload more fonts in the folder <strong>nggallery/fonts</strong>','nggallery'); ?>
                        </span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e('Size','nggallery') ?></th>
					<td><input type="number" step="1" min="0" class="small-text" name="wmSize" value="<?php echo $ngg->options['wmSize']; ?>"/><label for="wmSize">px</label></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Color','nggallery') ?></th>
					<td><input class="picker" type="text" size="6" maxlength="6" id="wmColor" name="wmColor" onchange="setcolor('#previewText', this.value)" value="<?php echo $ngg->options['wmColor'] ?>" />
					<input type="text" size="1" readonly="readonly" id="previewText" style="background-color: #<?php echo $ngg->options['wmColor']; ?>" /> <?php esc_html_e('(hex w/o #)','nggallery') ?></td>
				</tr>
				<tr>
					<th valign="top"><?php esc_html_e('Text','nggallery') ?></th>
					<td><textarea name="wmText" cols="40" rows="4"><?php echo $ngg->options['wmText'] ?></textarea></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Opaque','nggallery') ?></th>
					<td><input type="number" step="1" min="0" max="100" class="small-text" name="wmOpaque" value="<?php echo $ngg->options['wmOpaque'] ?>"/><label for="wmOpaque">%</label></td>
				</tr>
			</table>
		<div class="clear"> &nbsp; </div>
		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php esc_attr_e('Save Changes') ;?>"/></div>
		</form>
    <?php
    }

    function tab_slideshow() {

        global $ngg;
    ?>
    	<!-- Slideshow settings -->
    	<form name="player_options" method="POST" action="<?php echo $this->filepath.'#slideshow'; ?>" >
    	<?php wp_nonce_field('ngg_settings'); ?>
    	<input type="hidden" name="page_options" value="slideFx,enableIR,irURL,irWidth,irHeight,irShuffle,irLinkfromdisplay,irShownavigation,irShowicons,irWatermark,irOverstretch,irRotatetime,irTransition,irKenburns,irBackcolor,irFrontcolor,irLightcolor,irScreencolor,irAudio,irXHTMLvalid" />
    	<h3><?php esc_html_e('Slideshow','nggallery'); ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th><?php esc_html_e('Default size','nggallery') ?></th>
					 <td><label for="irWidth"><?php esc_html_e('Width','nggallery') ?></label>
					<input type="number" step="1" min="0" class="small-text" name="irWidth" value="<?php echo $ngg->options['irWidth']; ?> " />
					<label for="irHeight"><?php esc_html_e('Height','nggallery') ?></label>
					<input type="number" step="1" min="0" class="small-text" name="irHeight" value="<?php echo $ngg->options['irHeight']; ?>" />
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e('Duration','nggallery') ?></th>
					<td><input type="number" type="number" step="1" min="0" class="small-text" name="irRotatetime" value="<?php echo $ngg->options['irRotatetime'] ?>" /> <label for="irRotatetime"><?php esc_html_e('sec.', 'nggallery') ;?></label></td>
				</tr>
				<tr>
				    <th><?php esc_html_e('Transition / Fade effect','nggallery') ?></th>
					<td>
					<select size="1" name="slideFx">
						<option value="fade" <?php selected('fade', $ngg->options['slideFx']); ?> ><?php esc_html_e('fade', 'nggallery') ;?></option>
						<option value="blindX" <?php selected('blindX', $ngg->options['slideFx']); ?> ><?php esc_html_e('blindX', 'nggallery') ;?></option>
						<option value="cover" <?php selected('cover', $ngg->options['slideFx']); ?> ><?php esc_html_e('cover', 'nggallery') ;?></option>
						<option value="scrollUp" <?php selected('scrollUp', $ngg->options['slideFx']); ?> ><?php esc_html_e('scrollUp', 'nggallery') ;?></option>
						<option value="scrollDown" <?php selected('scrollDown', $ngg->options['slideFx']); ?> ><?php esc_html_e('scrollDown', 'nggallery') ;?></option>
						<option value="shuffle" <?php selected('shuffle', $ngg->options['slideFx']); ?> ><?php esc_html_e('shuffle', 'nggallery') ;?></option>
						<option value="toss" <?php selected('toss', $ngg->options['slideFx']); ?> ><?php esc_html_e('toss', 'nggallery') ;?></option>
						<option value="wipe" <?php selected('wipe', $ngg->options['slideFx']); ?> ><?php esc_html_e('wipe', 'nggallery') ;?></option>
					</select>
                    <p class="description"><?php esc_html_e('See here for more information about the effects :', 'nggallery') ?> <a href="http://jquery.malsup.com/cycle/browser.html" target="_blank" >jQuery Cycle</a></p>
                    </td>
				</tr>
 			    </table>
    			<h3><?php esc_html_e('Settings for the JW Image Rotator','nggallery') ?></h3>
				<p><?php esc_html_e('NextCellent Gallery flash slideshows use the JW Image Rotator Version 3.17 by', 'nggallery') ;?> <a target='_blank' href='http://www.longtailvideo.com/players/jw-image-rotator/'>Long Tail Video</a>.
					 <?php esc_html_e('Press the button below to search for it automatically. For earlier versions of NextCellent Gallery, you\'ll need to
					upload the file manually to the', 'nggallery') ;?> <a href='http://codex.wordpress.org/Uploading_Files' target='_blank'>WordPress Uploads directory</a>.</p>
            	<?php if (empty($ngg->options['irURL']) && ($ngg->options['enableIR'] == '1')) { ?>
        			<div id="message" class="error inline">
        			<p>
        				<?php esc_html_e('The path to JW Image Rotator is not defined, the slideshow will not work.','nggallery'); ?><br />
        				 <?php esc_html_e('Press the button below to search for the file.','nggallery'); ?></p>
        			</div>
            	<?php }?>
    			<table class="form-table ngg-options">
				<tr>
					<th><?php esc_html_e('Enable flash slideshow','nggallery') ?></th>
					<td><input name="enableIR" type="checkbox" value="1" <?php checked('1', $ngg->options['enableIR']); ?> />
                    <span><?php esc_html_e('Integrate the flash based slideshow for all flash supported devices','nggallery') ?></span></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Path to the JW Image Rotator (URL)','nggallery') ?></th>
					<td>
						<input type="text" class="regular-text code" id="irURL" name="irURL" value="<?php echo $ngg->options['irURL']; ?>" />
						<input type="submit" name="irDetect" class="button-secondary"  value="<?php esc_html_e('Search now','nggallery') ;?> &raquo;"/>
						<br /><span><?php esc_html_e('Press the button below to search for the JW Image Rotator','nggallery') ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e('Shuffle mode','nggallery') ?></th>
					<td><input name="irShuffle" type="checkbox" value="1" <?php checked('1', $ngg->options['irShuffle']); ?> /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Show next image on click','nggallery') ?></th>
					<td><input name="irLinkfromdisplay" type="checkbox" value="1" <?php checked('1', $ngg->options['irLinkfromdisplay']); ?> /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Show navigation bar','nggallery') ?></th>
					<td><input name="irShownavigation" type="checkbox" value="1" <?php checked('1', $ngg->options['irShownavigation']); ?> /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Show loading icon','nggallery') ?></th>
					<td><input name="irShowicons" type="checkbox" value="1" <?php checked('1', $ngg->options['irShowicons']); ?> /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Use watermark logo','nggallery') ?></th>
					<td><input name="irWatermark" type="checkbox" value="1" <?php checked('1', $ngg->options['irWatermark']); ?> />
					<span><?php esc_html_e('You can change the logo at the watermark settings','nggallery') ?></span></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Stretch image','nggallery') ?></th>
					<td>
					<select size="1" name="irOverstretch">
						<option value="true" <?php selected('true', $ngg->options['irOverstretch']); ?> ><?php esc_html_e('true', 'nggallery') ;?></option>
						<option value="false" <?php selected('false', $ngg->options['irOverstretch']); ?> ><?php esc_html_e('false', 'nggallery') ;?></option>
						<option value="fit" <?php selected('fit', $ngg->options['irOverstretch']); ?> ><?php esc_html_e('fit', 'nggallery') ;?></option>
						<option value="none" <?php selected('none', $ngg->options['irOverstretch']); ?> ><?php esc_html_e('none', 'nggallery') ;?></option>
					</select>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e('Transition / Fade effect','nggallery') ?></th>
					<td>
					<select size="1" name="irTransition">
						<option value="fade" <?php selected('fade', $ngg->options['irTransition']); ?> ><?php esc_html_e('fade', 'nggallery') ;?></option>
						<option value="bgfade" <?php selected('bgfade', $ngg->options['irTransition']); ?> ><?php esc_html_e('bgfade', 'nggallery') ;?></option>
						<option value="slowfade" <?php selected('slowfade', $ngg->options['irTransition']); ?> ><?php esc_html_e('slowfade', 'nggallery') ;?></option>
						<option value="circles" <?php selected('circles', $ngg->options['irTransition']); ?> ><?php esc_html_e('circles', 'nggallery') ;?></option>
						<option value="bubbles" <?php selected('bubbles', $ngg->options['irTransition']); ?> ><?php esc_html_e('bubbles', 'nggallery') ;?></option>
						<option value="blocks" <?php selected('blocks', $ngg->options['irTransition']); ?> ><?php esc_html_e('blocks', 'nggallery') ;?></option>
						<option value="fluids" <?php selected('fluids', $ngg->options['irTransition']); ?> ><?php esc_html_e('fluids', 'nggallery') ;?></option>
						<option value="flash" <?php selected('flash', $ngg->options['irTransition']); ?> ><?php esc_html_e('flash', 'nggallery') ;?></option>
						<option value="lines" <?php selected('lines', $ngg->options['irTransition']); ?> ><?php esc_html_e('lines', 'nggallery') ;?></option>
						<option value="random" <?php selected('random', $ngg->options['irTransition']); ?> ><?php esc_html_e('random', 'nggallery') ;?></option>
					</select>
                    </td>
				</tr>
				<tr>
					<th><?php esc_html_e('Use slow zooming effect','nggallery') ?></th>
					<td><input name="irKenburns" type="checkbox" value="1" <?php checked('1', $ngg->options['irKenburns']); ?> /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Background Color','nggallery') ?></th>
					<td><input class="picker" type="text" size="6" maxlength="6" id="irBackcolor" name="irBackcolor" onchange="setcolor('#previewBack', this.value)" value="<?php echo $ngg->options['irBackcolor'] ?>" />
					<input type="text" size="1" readonly="readonly" id="previewBack" style="background-color: #<?php echo $ngg->options['irBackcolor'] ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Texts / Buttons Color','nggallery') ?></th>
					<td><input class="picker" type="text" size="6" maxlength="6" id="irFrontcolor" name="irFrontcolor" onchange="setcolor('#previewFront', this.value)" value="<?php echo $ngg->options['irFrontcolor'] ?>" />
					<input type="text" size="1" readonly="readonly" id="previewFront" style="background-color: #<?php echo $ngg->options['irFrontcolor'] ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Rollover / Active Color','nggallery') ?></th>
					<td><input class="picker" type="text" size="6" maxlength="6" id="irLightcolor" name="irLightcolor" onchange="setcolor('#previewLight', this.value)" value="<?php echo $ngg->options['irLightcolor'] ?>" />
					<input type="text" size="1" readonly="readonly" id="previewLight" style="background-color: #<?php echo $ngg->options['irLightcolor'] ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Screen Color','nggallery') ?></th>
					<td><input class="picker" type="text" size="6" maxlength="6" id="irScreencolor" name="irScreencolor" onchange="setcolor('#previewScreen', this.value)" value="<?php echo $ngg->options['irScreencolor'] ?>" />
					<input type="text" size="1" readonly="readonly" id="previewScreen" style="background-color: #<?php echo $ngg->options['irScreencolor'] ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Background music (URL)','nggallery') ?></th>
					<td><input type="text" class="regular-text code" id="irAudio" name="irAudio" value="<?php echo $ngg->options['irAudio'] ?>" /></td>
				</tr>
				<tr>
					<th ><?php esc_html_e('Try XHTML validation (with CDATA)','nggallery') ?></th>
					<td><input name="irXHTMLvalid" type="checkbox" value="1" <?php checked('1', $ngg->options['irXHTMLvalid']); ?> />
					<span><?php esc_html_e('Important : Could causes problem with some browser. Please recheck your page.','nggallery') ?></span></td>
				</tr>
				</table>
			<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php esc_attr_e('Save Changes') ;?>"/></div>
	</form>
    <?php
    }
}

function ngg_get_TTFfont() {

	$ttf_fonts = array ();

	// Files in wp-content/plugins/nggallery/fonts directory
	$plugin_root = NGGALLERY_ABSPATH . 'fonts';

	$plugins_dir = @ dir($plugin_root);
	if ($plugins_dir) {
		while (($file = $plugins_dir->read()) !== false) {
			if (preg_match('|^\.+$|', $file))
				continue;
			if (is_dir($plugin_root.'/'.$file)) {
				$plugins_subdir = @ dir($plugin_root.'/'.$file);
				if ($plugins_subdir) {
					while (($subfile = $plugins_subdir->read()) !== false) {
						if (preg_match('|^\.+$|', $subfile))
							continue;
						if (preg_match('|\.ttf$|', $subfile))
							$ttf_fonts[] = "$file/$subfile";
					}
				}
			} else {
				if (preg_match('|\.ttf$|', $file))
					$ttf_fonts[] = $file;
			}
		}
	}

	return $ttf_fonts;
}

function ngg_search_imagerotator() {
	global $wpdb;

	$upload = wp_upload_dir();

	// look first at the old place and move it to wp-content/uploads
	if ( file_exists( NGGALLERY_ABSPATH . 'imagerotator.swf' ) )
		@rename(NGGALLERY_ABSPATH . 'imagerotator.swf', $upload['basedir'] . '/imagerotator.swf');

	// This should be the new place
	if ( file_exists( $upload['basedir'] . '/imagerotator.swf' ) )
		return $upload['baseurl'] . '/imagerotator.swf';

	// Find the path to the imagerotator via the media library
	if ( $path = $wpdb->get_var( "SELECT guid FROM {$wpdb->posts} WHERE guid LIKE '%imagerotator.swf%'" ) )
		return $path;

	// maybe it's located at wp-content
	if ( file_exists( WP_CONTENT_DIR . '/imagerotator.swf' ) )
		return WP_CONTENT_URL . '/imagerotator.swf';

	// or in the plugin folder
	if ( file_exists( WP_PLUGIN_DIR . '/imagerotator.swf' ) )
		return WP_PLUGIN_URL . '/imagerotator.swf';

	// this is deprecated and will be ereased during a automatic upgrade
	if ( file_exists( NGGALLERY_ABSPATH . 'imagerotator.swf' ) )
		return NGGALLERY_URLPATH . 'imagerotator.swf';

	return '';
}

/**********************************************************/
// taken from WP Core

function ngg_input_selected( $selected, $current) {
	if ( $selected == $current)
		return ' selected="selected"';
}

function ngg_input_checked( $checked, $current) {
	if ( $checked == $current)
		return ' checked="checked"';
}
?>