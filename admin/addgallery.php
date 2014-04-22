<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class nggAddGallery {

    /**
     * nggAddGallery::__construct()
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
	 * Perform the upload and add a new hook for plugins
	 *
	 * @return void
	 */
	function processor() {
        global $wpdb, $ngg, $nggdb;

    	$defaultpath = $ngg->options['gallerypath'];

    	if ( isset($_POST['addgallery']) ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Add new gallery' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		$newgallery = esc_attr( $_POST['galleryname']);
    		if ( !empty($newgallery) )
    			nggAdmin::create_gallery($newgallery, $defaultpath);
    	}

    	if ( isset($_POST['zipupload']) ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Upload a zip' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		if ($_FILES['zipfile']['error'] == 0 || (!empty($_POST['zipurl'])))
    			nggAdmin::import_zipfile( intval( $_POST['zipgalselect'] ) );
    		else
    			nggGallery::show_error( __('Upload failed!','nggallery') );
    	}

    	if ( isset($_POST['importfolder']) ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Import image folder' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		$galleryfolder = $_POST['galleryfolder'];
    		if ( ( !empty($galleryfolder) ) AND ($defaultpath != $galleryfolder) )
    			nggAdmin::import_gallery($galleryfolder);
    	}

    	if ( isset($_POST['uploadimage']) ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Upload in all galleries' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		if ( $_FILES['imagefiles']['error'][0] == 0 )
    			 nggAdmin::upload_images();
    		else
    			nggGallery::show_error( __('Upload failed! ' . nggAdmin::decode_upload_error( $_FILES['imagefiles']['error'][0]),'nggallery') );
    	}

    	if ( isset($_POST['swf_callback']) ){
    		if ($_POST['galleryselect'] == '0' )
    			nggGallery::show_error(__('You didn\'t select a gallery!','nggallery'));
    		else {
                if ($_POST['swf_callback'] == '-1' )
                    nggGallery::show_error( __('Upload failed!','nggallery') );
                else {
                    $gallery = $nggdb->find_gallery( (int) $_POST['galleryselect'] );
                    nggAdmin::import_gallery( $gallery->path );
                }
            }
    	}

    	if ( isset($_POST['disable_flash']) ){
    		check_admin_referer('ngg_addgallery');
    		$ngg->options['swfUpload'] = false;
    		update_option('ngg_options', $ngg->options);
    	}

    	if ( isset($_POST['enable_flash']) ){
    		check_admin_referer('ngg_addgallery');
    		$ngg->options['swfUpload'] = true;
    		update_option('ngg_options', $ngg->options);
    	}

        do_action( 'ngg_update_addgallery_page' );

    }

    /**
     * Render the page content
     *
     * @return void
     */
    function controller() {
        global $ngg, $nggdb;

    	// check for the max image size
    	$this->maxsize    = nggGallery::check_memory_limit();

    	//get all galleries (after we added new ones)
    	$this->gallerylist = $nggdb->find_all_galleries('gid', 'DESC');

        $this->defaultpath = $ngg->options['gallerypath'];

        // link for the flash file
		$swf_upload_link = admin_url('/?nggupload');

        // get list of tabs
        $tabs = $this->tabs_order();

        // with this filter you can add custom file types
        $file_types = apply_filters( 'ngg_swf_file_types', '*.jpg;*.jpeg;*.gif;*.png;*.JPG;*.JPEG;*.GIF;*.PNG' );

        // Set the post params, which plupload will post back with the file, and pass them through a filter.
        $post_params = array(
        		"auth_cookie" => (is_ssl() ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE]),
        		"logged_in_cookie" => $_COOKIE[LOGGED_IN_COOKIE],
        		"_wpnonce" => wp_create_nonce('ngg_swfupload'),
        		"galleryselect" => "0",
        );
        $p = array();

        foreach ( $post_params as $param => $val ) {
        	$val = esc_js( $val );
        	$p[] = "'$param' : '$val'";
        }

        $post_params_str = implode( ',', $p ). "\n";
	?>
	<div class="wrap ngg-wrap">
	<?php screen_icon( 'nextgen-gallery' ); ?>
	<h2><?php _e('Add Gallery / Images', 'nggallery') ?></h2>
	</div>

	<?php if($ngg->options['swfUpload'] && !empty ($this->gallerylist) ) { ?>
    <?php if ( defined('IS_WP_3_3') ) { ?>
    <!-- plupload script -->
    <script type="text/javascript">
    //<![CDATA[

    jQuery(document).ready(function($) {
    	window.uploader = new plupload.Uploader({
    		runtimes: '<?php echo apply_filters('plupload_runtimes', 'html5,flash,silverlight,html4,'); ?>',
    		browse_button: 'plupload-browse-button',
    		container: 'plupload-upload-ui',
    		drop_element: 'uploadimage',
    		file_data_name: 'Filedata',
    		max_file_size: '<?php echo round( (int) wp_max_upload_size() / 1024 ); ?>kb',
    		url: '<?php echo esc_js( $swf_upload_link ); ?>',
    		flash_swf_url: '<?php echo esc_js( includes_url('js/plupload/plupload.flash.swf') ); ?>',
    		silverlight_xap_url: '<?php echo esc_js( includes_url('js/plupload/plupload.silverlight.xap') ); ?>',
    		filters: [
    			{title: '<?php echo esc_js( __('Image Files', 'nggallery') ); ?>', extensions: '<?php echo esc_js( str_replace( array('*.', ';'), array('', ','), $file_types)  ); ?>'}
    		],
    		multipart: true,
    		urlstream_upload: true,
    		multipart_params : {
    			<?php echo $post_params_str; ?>
    		},
			<?php if ($ngg->options['imgAutoResize'] == 1) { ?>
			resize: {
				width: <?php echo esc_js( $ngg->options['imgWidth'] ); ?>,
				height: <?php echo esc_js( $ngg->options['imgHeight'] ); ?>,
				quality: 100
			},
			<?php } ?>
            debug: false,
            preinit : {
    			Init: function(up, info) {
    				debug('[Init]', 'Info :', info,  'Features :', up.features);
                    if (navigator.appVersion.indexOf("MSIE 10") > -1) {
                        up.features.triggerDialog = true;
                    }
                    initUploader();
    			}
            },
			i18n : {
				'remove' : '<?php _e('remove', 'nggallery') ;?>',
				'browse' : '<?php _e('Browse...', 'nggallery') ;?>',
				'upload' : '<?php _e('Upload images', 'nggallery') ;?>'
			}
    	});

    	uploader.bind('FilesAdded', function(up, files) {
    		$.each(files, function(i, file) {
    			fileQueued(file);
    		});

    		up.refresh();
    	});

    	uploader.bind('BeforeUpload', function(up, file) {
            uploadStart(file);
    	});

    	uploader.bind('UploadProgress', function(up, file) {
    		uploadProgress(file, file.loaded, file.size);
    	});

    	uploader.bind('Error', function(up, err) {
    		uploadError(err.file, err.code, err.message);

    		up.refresh();
    	});

    	uploader.bind('FileUploaded', function(up, file, response) {
    		uploadSuccess(file, response);
    	});

    	uploader.bind('UploadComplete', function(up, file) {
    		uploadComplete(file);
    	});

		// on load change the upload to plupload
		uploader.init();

		nggAjaxOptions = {
		  	header: "<?php _e('Upload images', 'nggallery') ;?>",
		  	maxStep: 100
		};

    });
    //]]>
    </script>
    <?php } else { ?>
	<!-- SWFUpload script -->
	<script type="text/javascript">
		var ngg_swf_upload;

		window.onload = function () {
			ngg_swf_upload = new SWFUpload({
				// Backend settings
				upload_url : "<?php echo esc_js( $swf_upload_link ); ?>",
				flash_url : "<?php echo esc_js( includes_url('js/swfupload/swfupload.swf') ); ?>",

				// Button Settings
				button_placeholder_id : "spanButtonPlaceholder",
				button_width: 300,
				button_height: 27,
			  button_text_top_padding: 3,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,

				// File Upload Settings
				file_size_limit : "<?php echo wp_max_upload_size(); ?>b",
				file_types : "<?php echo $file_types; ?>",
				file_types_description : "<?php _e('Image Files', 'nggallery') ;?>",

				// Queue handler
				file_queued_handler : fileQueued,

				// Upload handler
				upload_start_handler : uploadStart,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,

				post_params : {
					"auth_cookie" : "<?php echo (is_ssl() ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE]); ?>",
                    "logged_in_cookie": "<?php echo $_COOKIE[LOGGED_IN_COOKIE]; ?>",
                    "_wpnonce" : "<?php echo wp_create_nonce('ngg_swfupload'); ?>",
					"galleryselect" : "0"
				},

				// i18names
				custom_settings : {
					"remove" : "<?php _e('remove', 'nggallery') ;?>",
					"browse" : "<?php _e('Browse...', 'nggallery') ;?>",
					"upload" : "<?php _e('Upload images', 'nggallery') ;?>"
				},

				// Debug settings
				debug: false

			});

			// on load change the upload to swfupload
			initSWFUpload();

			nggAjaxOptions = {
			  	header: "<?php _e('Upload images', 'nggallery') ;?>",
			  	maxStep: 100
			};

		};
	</script>
    <?php } ?>
	<?php } else { ?>
	<!-- MultiFile script -->
	<script type="text/javascript">
	/* <![CDATA[ */
		jQuery(document).ready(function(){
			jQuery('#imagefiles').MultiFile({
				STRING: {
			    	remove:'[<?php _e('remove', 'nggallery') ;?>]'
  				}
		 	});
		});
	/* ]]> */
	</script>
	<?php } ?>
	<!-- jQuery Tabs script -->
	<script type="text/javascript">
	/* <![CDATA[ */
		jQuery(document).ready(function(){
            jQuery('html,body').scrollTop(0);
			jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' }).css({ 'display': 'block', 'margin': '4px 15px 0 0' });
		});

		// File Tree implementation
		jQuery(function() {
		    jQuery("span.browsefiles").show().click(function(){
    		    jQuery("#file_browser").fileTree({
    		      script: "admin-ajax.php?action=ngg_file_browser&nonce=<?php echo wp_create_nonce( 'ngg-ajax' ) ;?>",
                  root: jQuery("#galleryfolder").val()
    		    }, function(folder) {
    		        jQuery("#galleryfolder").val( folder );
    		    });
		    	jQuery("#file_browser").show('slide');
		    });
		});
	/* ]]> */
	</script>
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
		
		if ( nggGallery::current_user_can( 'NextGEN Add new gallery' ))
			$tabs['addgallery'] = __('New gallery', 'nggallery');

    	if ( !empty ($this->gallerylist) )
			$tabs['uploadimage'] = __( 'Images', 'nggallery' );

        if ( wpmu_enable_function('wpmuZipUpload') && nggGallery::current_user_can( 'NextGEN Upload a zip' ) )
			$tabs['zipupload'] = __('ZIP file', 'nggallery');

        if ( wpmu_enable_function('wpmuImportFolder') && nggGallery::current_user_can( 'NextGEN Import image folder' ) )
			$tabs['importfolder'] = __('Import folder', 'nggallery');

    	$tabs = apply_filters('ngg_addgallery_tabs', $tabs);

    	return $tabs;

    }

    function tab_addgallery() {
    ?>
		<!-- create gallery -->
		<h3><?php _e('Add a new gallery', 'nggallery') ;?></h3>
		<form name="addgallery" id="addgallery_form" method="POST" action="<?php echo $this->filepath; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Name', 'nggallery') ;?>:</th>
				<td><input type="text" size="35" name="galleryname" value="" /><br />
				<?php if(!is_multisite()) { ?>
				<?php _e('Create a new, empty gallery in the folder', 'nggallery') ;?>  <strong><?php echo $this->defaultpath ?></strong>
				<?php } ?>
				<p class="description"><?php _e('Allowed characters for file and folder names are', 'nggallery') ;?>: a-z, A-Z, 0-9, -, _</p></td>
			</tr>
			<?php do_action('ngg_add_new_gallery_form'); ?>
			</table>
			<div class="submit"><input class="button-primary" type="submit" name= "addgallery" value="<?php _e('Add gallery', 'nggallery') ;?>"/></div>
		</form>
    <?php
    }

    function tab_zipupload() {
    ?>
		<!-- zip-file operation -->
		<h3><?php _e('Upload a ZIP File', 'nggallery') ;?></h3>
		<form name="zipupload" id="zipupload_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->filepath.'#zipupload'; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Select ZIP file', 'nggallery') ;?>:</th>
				<td><input type="file" name="zipfile" id="zipfile" size="35" class="uploadform"/><p class="description">
				<?php _e('Upload a ZIP file with images', 'nggallery') ;?></p></td>
			</tr>
			<?php if (function_exists('curl_init')) : ?>
			<tr valign="top">
				<th scope="row"><?php _e('or enter URL', 'nggallery') ;?>:</th>
				<td><input type="text" name="zipurl" id="zipurl" size="35" class="uploadform"/>
				<p class="description"><?php _e('Import a ZIP file from a URL', 'nggallery') ;?></p></td>
			</tr>
			<?php endif; ?>
			<tr valign="top">
				<th scope="row"><?php _e('in to', 'nggallery') ;?></th>
				<td><select name="zipgalselect">
				<option value="0" ><?php _e('a new gallery', 'nggallery') ?></option>
				<?php
					foreach($this->gallerylist as $gallery) {
						if ( !nggAdmin::can_manage_this_gallery($gallery->author) )
							continue;
						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . esc_attr( $name ). '</option>' . "\n";
					}
				?>
				</select>
				<br /><?php echo $this->maxsize; ?>
				<p class="description"><?php echo _e('Note: the upload limit on your server is ','nggallery') . "<strong>" . ini_get('upload_max_filesize') . "B</strong>\n"; ?></p>
				<br /><?php if ( (is_multisite()) && wpmu_enable_function('wpmuQuotaCheck') ) display_space_usage(); ?></td>
			</tr>
			</table>
			<div class="submit"><input class="button-primary" type="submit" name= "zipupload" value="<?php _e('Start upload', 'nggallery') ;?>"/></div>
		</form>
    <?php
    }

    function tab_importfolder() {
    ?>
	<!-- import folder -->
	<h3><?php _e('Import an image folder', 'nggallery') ;?></h3>
		<form name="importfolder" id="importfolder_form" method="POST" action="<?php echo $this->filepath.'#importfolder'; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Import from server:', 'nggallery') ;?></th>
				<td><input type="text" size="35" id="galleryfolder" name="galleryfolder" value="<?php echo $this->defaultpath; ?>" /><span class="browsefiles button" style="display:none"><?php _e('Browse...', 'nggallery'); ?></span><br />
				<div id="file_browser"></div>
				<p class="description"><?php _e('Note: you can change the default path in the gallery settings', 'nggallery') ;?></p>
				<br /><?php echo $this->maxsize; ?>
				<?php if (SAFE_MODE) {?><p class="description"><?php _e('Please note: If safe-mode is ON, you need to add the subfolder with thumbs manually', 'nggallery') ;?></p><?php }; ?></td>
			</tr>
			</table>
			<div class="submit"><input class="button-primary" type="submit" name= "importfolder" value="<?php _e('Import folder', 'nggallery') ;?>"/></div>
		</form>
    <?php
    }

    function tab_uploadimage() {
        global $ngg;
        // check the cookie for the current setting
        $checked = get_user_setting('ngg_upload_resize') ? ' checked="true"' : '';
    ?>
    	<!-- upload images -->
    	<h3><?php _e('Upload images', 'nggallery') ;?></h3>
		<form name="uploadimage" id="uploadimage_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->filepath.'#uploadimage'; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Upload image', 'nggallery') ;?></th>
                <?php if ($ngg->options['swfUpload'] && defined('IS_WP_3_3') ) { ?>
				<td>
                <div id="plupload-upload-ui">
                	<div>
                    	<?php _e( 'Choose files to upload' ); ?>
                    	<input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button" />
                	</div>
                	<p class="ngg-dragdrop-info howto" style="display:none;" ><?php _e('Or you can drop the files into this window.'); ?></p>
                    <div id='uploadQueue'></div>
                 </div>
                </td>
                <?php } else { ?>
				<td><span id='spanButtonPlaceholder'></span><input type="file" name="imagefiles[]" id="imagefiles" size="35" class="imagefiles"/></td>
                <?php } ?>
            </tr>
			<tr valign="top">
				<th scope="row"><?php _e('in to', 'nggallery') ;?></th>
				<td><select name="galleryselect" id="galleryselect">
				<option value="0" ><?php _e('Choose gallery', 'nggallery') ?></option>
				<?php
					foreach($this->gallerylist as $gallery) {
						//special case : we check if a user has this cap, then we override the second cap check
						if ( !current_user_can( 'NextGEN Upload in all galleries' ) )
							if ( !nggAdmin::can_manage_this_gallery($gallery->author) )
								continue;

						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . esc_attr( $name ) . '</option>' . "\n";
					}					?>
				</select>
				<br /><?php echo $this->maxsize; ?>
				<br /><?php if ((is_multisite()) && wpmu_enable_function('wpmuQuotaCheck')) display_space_usage(); ?></td>
			</tr>
			</table>
			<div class="submit">
				<?php if ($ngg->options['swfUpload']) { ?>
				<input class="button action" type="submit" name="disable_flash" id="disable_flash" title="<?php _e('The batch upload requires Adobe Flash 10, disable it if you have problems','nggallery') ?>" value="<?php _e('Disable flash upload', 'nggallery') ;?>" />
				<?php } else { ?>
				<input class="button action" type="submit" name="enable_flash" id="enable_flash" title="<?php _e('Upload multiple files at once by ctrl/shift-selecting in dialog','nggallery') ?>" value="<?php _e('Enable flash based upload', 'nggallery') ;?>" />
				<?php } ?>
				<input class="button-primary" type="submit" name="uploadimage" id="uploadimage_btn" value="<?php _e('Upload images', 'nggallery') ;?>" />
			</div>
		</form>
    <?php
    }
}
?>
