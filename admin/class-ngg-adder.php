<?php

include_once('class-ngg-post-admin-page.php');

/**
 * Class NGG_Adder
 *
 * Add new stuff to NextCellent.
 *
 * @todo The whole system with the plupload needs a rework.
 */
class NGG_Adder extends NGG_Post_Admin_Page {

	/**
	 * Perform the upload and add a new hook for plugins
	 *
	 * @return void
	 */
	protected function processor() {

		/**
		 * @global nggdb $nggdb
		 */
		global $nggdb;

		$options = get_option( 'ngg_options' );

		$default_path = $options['gallerypath'];

		if ( isset( $_POST['addgallery'] ) ) {
			check_admin_referer( 'ngg_addgallery' );

			if ( ! nggGallery::current_user_can( 'NextGEN Add new gallery' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			$new_gallery = esc_attr( $_POST['galleryname'] );
			$description = esc_attr( $_POST['gallerydesc'] );
			if ( ! empty( $new_gallery ) ) {
				nggAdmin::create_gallery( $new_gallery, $default_path, true, $description );
			}
		}

		if ( isset( $_POST['zipupload'] ) ) {
			check_admin_referer( 'ngg_addgallery' );

			if ( ! nggGallery::current_user_can( 'NextGEN Upload a zip' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			if ( $_FILES['zipfile']['error'] == 0 || ( ! empty( $_POST['zipurl'] ) ) ) {
				nggAdmin::import_zipfile( intval( $_POST['zipgalselect'] ) );
			} else {
				nggGallery::show_error( __( 'Upload failed!', 'nggallery' ) );
			}
		}

		/* 20170702: Import folder suppressed due exploit. This needs to be redone.
		if ( isset( $_POST['importfolder'] ) ) {
			check_admin_referer( 'ngg_addgallery' );

			if ( ! nggGallery::current_user_can( 'NextGEN Import image folder' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			$galleryfolder = $_POST['galleryfolder'];
			if ( ( ! empty( $galleryfolder ) ) AND ( $default_path != $galleryfolder ) ) {
				nggAdmin::import_gallery( $galleryfolder );
			}
		}
		*/

		if ( isset( $_POST['uploadimage'] ) ) {
			check_admin_referer( 'ngg_addgallery' );

			if ( ! nggGallery::current_user_can( 'NextGEN Upload in all galleries' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			if ( $_FILES['imagefiles']['error'][0] == 0 ) {
				nggAdmin::upload_images();
			} else {
				nggGallery::show_error( __( 'Upload failed! ' . nggAdmin::decode_upload_error( $_FILES['imagefiles']['error'][0] ),
					'nggallery' ) );
			}
		}

		if ( isset( $_POST['swf_callback'] ) ) {
			if ( (int) $_POST['galleryselect'] == 0 ) {
				nggGallery::show_error( __( 'You didn\'t select a gallery!', 'nggallery' ) );
			} else {
				if ( (int) $_POST['swf_callback'] == -1 ) {
					nggGallery::show_error( __( 'Upload failed!', 'nggallery' ) );
				} else {
					$gallery = $nggdb->find_gallery( (int) $_POST['galleryselect'] );
					nggAdmin::import_gallery( $gallery->path );
				}
			}
		}

		if ( isset( $_POST['use_simple'] ) ) {
			check_admin_referer( 'ngg_addgallery' );
			$options['swfUpload'] = false;
			update_option( 'ngg_options', $options );
		}

		if ( isset( $_POST['use_advanced'] ) ) {
			check_admin_referer( 'ngg_addgallery' );
			$options['swfUpload'] = true;
			update_option( 'ngg_options', $options );
		}

		do_action( 'ngg_update_addgallery_page' );

	}

	/**
	 * Render the page content
	 *
	 * @return void
	 */
	public function display() {

		parent::display();

		/**
		 * @global nggdb $nggdb
		 */
		global $nggdb;

		$args = array(
			'max_size'  => nggGallery::check_memory_limit(),
			'galleries' => $nggdb->find_all_galleries('gid', 'DESC'),
			'options'   => get_option('ngg_options')

		);

		// link for the flash file
		$swf_upload_link = admin_url('/?nggupload');

		// get list of tabs
		$tabs = $this->tabs_order($args);

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

		$post_params_str = json_encode($post_params);
		?>
		<div class="wrap">
			<h2><?php _e('Add Gallery / Images', 'nggallery') ?></h2>
			<div id="slider" style="display: none;">
				<ul id="tabs">
					<?php
					foreach($tabs as $tab_key => $tab_name) {
						echo "<li><a class='nav-tab' href='#$tab_key'>$tab_name</a></li>";
					}
					?>
				</ul>
				<?php
				foreach($tabs as $tab_key => $tab_name) {
					echo "<div id='$tab_key'>";
					// Looks for the internal class function, otherwise enable a hook for plugins
					if ( method_exists( $this, "tab_$tab_key" ))
						call_user_func( array( &$this , "tab_$tab_key"), $args );
					else
						do_action( 'ngg_tab_content_' . $tab_key );
					echo "</div>";
				}
				?>
			</div>
		</div>
		<script type="text/javascript">
		<?php if( !empty ($args['galleries']) ) { ?>
			<?php if( $args['options']['swfUpload'] ) { ?>
				// plupload script
				jQuery(document).ready(function($) {
					window.uploader = new plupload.Uploader({
						browse_button: 'plupload-browse-button',
						container: 'plupload-upload-ui',
						drop_element: 'uploadimage',
						file_data_name: 'Filedata',
						url: '<?php echo esc_js( $swf_upload_link ); ?>',
						flash_swf_url: '<?php echo esc_js( includes_url('js/plupload/plupload.flash.swf') ); ?>',
						silverlight_xap_url: '<?php echo esc_js( includes_url('js/plupload/plupload.silverlight.xap') ); ?>',
						filters: {
							mime_types : [
								{title: '<?php echo esc_js( __('Image Files', 'nggallery') ); ?>', extensions: '<?php echo esc_js( str_replace( array('*.', ';'), array('', ','), $file_types)  ); ?>'}
							],
							max_file_size: '<?php echo round( (int) wp_max_upload_size() / 1024 ); ?>kb'
						},
						multipart: true,
						urlstream_upload: true,
						multipart_params : <?php echo $post_params_str; ?>,
						<?php if ($args['options']['imgAutoResize'] == true) { ?>
						resize: {
							width: <?php echo esc_js( $args['options']['imgWidth'] ); ?>,
							height: <?php echo esc_js( $args['options']['imgHeight'] ); ?>,
							quality: <?php echo esc_js( $args['options']['imgQuality'] ); ?>
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
			<?php } else { ?>
				// Browser upload script
					var selDiv = "";

					document.addEventListener("DOMContentLoaded", init, false);

					function init() {
						document.querySelector('#imagefiles').addEventListener('change', handleFileSelect, false);
						selDiv = document.querySelector("#uploadQueue");
					}

					function handleFileSelect(e) {
						if(!e.target.files) return;
						selDiv.innerHTML = "";
						var files = e.target.files;
						for(var i=0; i<files.length; i++) {
							var f = files[i];
							selDiv.innerHTML += f.name + "<br/>";
						}
					}

					jQuery("#uploadimage_btn").click(function() {
						checkForm('galleryselect');
						checkImgFile();
					});
			<?php }
			}	?>
		//jQuery Tabs script
			jQuery(document).ready(function(){
				jQuery('html,body').scrollTop(0);
				jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' }).css({ 'display': 'block' });

				jQuery("#zip-upload").click(function() {
					checkZipFile();
				});

				jQuery("#import-folder").click(function() {
					return confirm(
						'<?php echo esc_js(__("This will change folder and file names (e.g. remove spaces, special characters, ...)","nggallery") )?>' +
						'\n\n' +
						'<?php echo esc_js( __("You will need to update your URLs if you link directly to the images.","nggallery") )?>' +
						'\n\n' +
						'<?php echo esc_js( __("Press OK to proceed, and Cancel to stop.","nggallery") )?>'
					);
				});
			});

			// File Tree implementation
			jQuery(function() {
				jQuery("span.browsefiles").show().click(function(){
					var browser = jQuery("#file_browser");
					browser.fileTree({
						script: "admin-ajax.php?action=ngg_file_browser&nonce=<?php echo wp_create_nonce( 'ngg-ajax' ) ;?>",
						root: jQuery("#galleryfolder").val()
					}, function(folder) {
						jQuery("#galleryfolder").val( folder );
					});
					browser.show('slide');
				});
			});

			//Check for a selected gallery on basic uploader and zip upload
			function checkForm(buttonID) {
				var e = document.getElementById(buttonID);
				var strUser = e.options[e.selectedIndex].value;
				if (strUser == "0") {
					alert("<?php _e('You didn\'t select a gallery!','nggallery')?>");
					event.preventDefault();
				}
			}

			//Check if the user has selected a zip file
			function checkZipFile() {
				if( !(document.getElementById('zipfile').value || document.getElementById("zipurl").value) ) {
					alert("<?php _e('You didn\'t select a file!','nggallery')?>");
					event.preventDefault();
				}
			}

			//Check if the user has selected an image file
			function checkImgFile() {
				if( !document.getElementById('imagefiles').value ) {
					alert("<?php _e('You didn\'t select a file!','nggallery')?>");
					event.preventDefault();
				}
			}
		</script>
		<?php

	}

	/**
	 * Create array for tabs and add a filter for other plugins to inject more tabs
	 *
	 * @return array $tabs
	 * 20170702: suppressed tab Import Folder in this version due vulnerability
	 */
	private function tabs_order($args) {

		$tabs = array();

		if ( nggGallery::current_user_can( 'NextGEN Add new gallery' ))
			$tabs['addgallery'] = __('New gallery', 'nggallery');

		if ( !empty ($args['galleries']) )
			$tabs['uploadimage'] = __( 'Images', 'nggallery' );

		if ( wpmu_enable_function('wpmuZipUpload') && nggGallery::current_user_can( 'NextGEN Upload a zip' ) )
			$tabs['zipupload'] = __('ZIP file', 'nggallery');

		/*
		if ( wpmu_enable_function('wpmuImportFolder') && nggGallery::current_user_can( 'NextGEN Import image folder' ) )
			$tabs['importfolder'] = __('Import folder', 'nggallery');
*/
		$tabs = apply_filters('ngg_addgallery_tabs', $tabs);

		return $tabs;

	}

	private function tab_addgallery($args) {
		?>
		<!-- create gallery -->
		<h3><?php _e('Add a new gallery', 'nggallery') ;?></h3>
		<form name="addgallery" id="addgallery_form" method="POST" action="<?php echo $this->page; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
				<tr>
					<th><label for="gallery-name"><?php _e('Name', 'nggallery') ;?>:</label></th>
					<td>
						<input type="text" class="regular-text" name="galleryname" id="gallery-name" value="">
						<br>
						<p>
							<?php printf( __('Create a new, empty gallery in the folder <strong>%s</strong>', 'nggallery'), $args['options']['gallerypath'] ); ?>
						</p>
						<p class="description">
							<?php printf( __('Allowed characters for file and folder names are "%s".', 'nggallery'), 'a-z, A-Z, 0-9, -, _' ) ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="gallerydesc"><?php _e('Description', 'nggallery') ;?>:</label></th>
					<td>
						<textarea name="gallerydesc" id="gallerydesc" cols="50" rows="3"></textarea>
						<p class="description">
							<?php _e('Add a description. This is optional and can be changed later.', 'nggallery') ;?>
						</p>
					</td>
				</tr>
				<?php do_action('ngg_add_new_gallery_form'); ?>
			</table>
			<div class="submit">
				<input class="button-primary" type="submit" name= "addgallery" value="<?php _e('Add gallery', 'nggallery') ;?>">
			</div>
		</form>
		<?php
	}

	private function tab_zipupload($args) {
		?>
		<!-- zip-file operation -->
		<h3><?php _e('Upload a ZIP File', 'nggallery') ;?></h3>
		<form name="zipupload" id="zipupload_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->page.'#zipupload'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
				<tr>
					<th><?php _e('Select ZIP file', 'nggallery') ;?>:</th>
					<td>
						<input type="file" name="zipfile" id="zipfile" class="uploadform">
						<p class="description">
							<?php _e('Upload a ZIP file with images', 'nggallery') ;?>
						</p>
					</td>
				</tr>
				<?php if (function_exists('curl_init')) : ?>
					<tr>
						<th><?php _e('or enter URL', 'nggallery') ;?>:</th>
						<td>
							<input type="text" name="zipurl" id="zipurl" class="regular-text code uploadform">
							<p class="description">
								<?php _e('Import a ZIP file from a URL', 'nggallery') ;?>
							</p>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<th><?php _e('in to', 'nggallery') ;?></th>
					<td>
						<select name="zipgalselect" id="zipgalselect">
							<option value="0" ><?php _e('a new gallery', 'nggallery') ?></option>
							<?php $this->print_galleries($args['galleries']); ?>
						</select>
						<br><?php echo $args['max_size']; ?>
						<p class="description">
							<?php printf( __('Note: the upload limit on your server is <strong>%s MB</strong>.', 'nggallery'), wp_max_upload_size() / (1024 * 1024)); ?>
						</p>
						<br>
						<?php if ( is_multisite() && wpmu_enable_function('wpmuQuotaCheck') ) {
							display_space_usage();
						}  ?>
					</td>
				</tr>
			</table>
			<div class="submit">
				<input class="button-primary" type="submit" name="zipupload" id="zip-upload" value="<?php _e('Start upload', 'nggallery') ;?>">
			</div>
		</form>
		<?php
	}

	private function tab_importfolder($args) {
		?>
		<!-- import folder -->
		<h3><?php _e('Import an image folder', 'nggallery') ;?></h3>
		<form name="importfolder" id="importfolder_form" method="POST" action="<?php echo $this->page.'#importfolder'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
				<tr>
					<th><?php _e('Import from server:', 'nggallery') ;?></th>
					<td>
						<input type="text" id="galleryfolder" class="regular-text code" name="galleryfolder" value="<?php echo $args['options']['gallerypath']; ?>">
						<span class="browsefiles button" style="display:none"><?php _e('Browse...', 'nggallery'); ?></span>
						<br>
						<div id="file_browser"></div>
						<p class="description"><?php _e('Note: you can change the default path in the gallery settings', 'nggallery') ;?></p>
						<br><?php echo $args['max_size']; ?>
					</td>
				</tr>
			</table>
			<div class="submit">
				<input class="button-primary" type="submit" name= "importfolder" id="import-folder" value="<?php _e('Import folder', 'nggallery') ;?>">
			</div>
		</form>
		<?php
	}

	private function tab_uploadimage($args) {
		?>
		<!-- upload images -->
		<h3><?php _e('Upload images', 'nggallery') ;?></h3>
		<form name="uploadimage" id="uploadimage_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->page.'#uploadimage'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
				<tr>
					<td class="gallery-selector">
						<?php _e('in to', 'nggallery') ;?>
						<select name="galleryselect" id="galleryselect">
							<option value="0" ><?php _e('Choose gallery', 'nggallery') ?></option>
							<?php $this->print_galleries($args['galleries']); ?>
						</select>
						<br>
						<?php echo $args['max_size']; ?>
						<br>
						<?php if ((is_multisite()) && wpmu_enable_function('wpmuQuotaCheck')) display_space_usage(); ?>
					</td>
				</tr>
				<tr>
					<?php if ($args['options']['swfUpload'] ) { ?>
						<td colspan="2">
							<div id="plupload-upload-ui">
								<div id="drag-drop-area">
									<div class="drag-drop-inside">
										<p class="ngg-dragdrop-info drag-drop-info" >
											<?php _e('Drop your files in this window', 'nggallery'); ?>
										</p>
										<p><?php _e('Or', 'nggallery'); ?></p>
										<p class="drag-drop-buttons">
											<input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files', 'nggallery'); ?>" class="button">
										</p>
									</div>
								</div>
							</div>
						</td>
					<?php } else { ?>
						<td><span id='spanButtonPlaceholder'></span>
							<input type="file" name="imagefiles[]" id="imagefiles" class="imagefiles" multiple></td>
					<?php } ?>
				</tr>
				<tr>
					<td>
						<div id='uploadQueue'></div>
					</td>
				</tr>
			</table>
			<div class="submit">
				<?php if ($args['options']['swfUpload']) { ?>
					<input class="button action" type="submit" name="use_simple" id="use-simple" title="<?php _e('Click here to use the browser upload instead','nggallery') ?>" value="<?php _e('Use basic uploader', 'nggallery') ;?>">
				<?php } else { ?>
					<input class="button action" type="submit" name="use_advanced" id="use-advanced" title="<?php _e('Advanced uploading','nggallery') ?>" value="<?php _e('Use advanced uploader', 'nggallery') ;?>">
				<?php } ?>
				<input class="button-primary" type="submit" name="uploadimage" id="uploadimage_btn" value="<?php _e('Upload images', 'nggallery') ;?>">
				<?php if ($args['options']['imgAutoResize'] == true) { ?>
					<span class="description">
						<?php printf( __( 'Your images will be rescaled to max width %1$dpx or max height %2$dpx.', 'nggallery' ), (int) $args['options']['imgWidth' ], (int) $args['options']['imgHeight'] ); ?>
					</span>
				<?php } ?>
			</div>
		</form>
		<?php
	}

	/**
	 * @param array $galleries
	 */
	private function print_galleries($galleries) {
		foreach($galleries as $gallery) {
			if ( current_user_can( 'NextGEN Upload in all galleries' ) ||  nggAdmin::can_manage_this_gallery($gallery->author) ) {
				$name = ( empty( $gallery->title ) ) ? $gallery->name : $gallery->title;
				echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . esc_attr( $name ) . '</option>';
			}
		}
	}
}