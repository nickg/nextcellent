<?php
include_once( 'NGG_Image_List_Table.php' );

/**
 * Class NGG_Gallery_Manager
 *
 * Display the gallery managing page.
 */
class NGG_Image_Manager {


	const BASE = 'admin.php?page=nggallery-manage-gallery';

	private $gallery;
	private $id;

	public function __construct() {
		$this->id = (int) $_GET['gid'];
		/**
		 * @global $nggdb nggdb
		 */
		global $nggdb;

		$this->gallery = $nggdb->find_gallery($this->id);
	}

	/**
	 * Display the page.
	 */
	public function display() {

		/**
		 * Do a bulk action.
		 */
		if ((isset($_POST['action']) || isset($_POST['action2'])) && isset ($_POST['doaction']))  {
			$this->handle_bulk_actions();
		}

		/**
		 * Add a gallery.
		 */
		if(  isset($_POST['gallery_name'])) {
			$this->handle_add_gallery();
		}

		/**
		 * Do the operations with a dialog.
		 */
		if(isset($_POST['TB_bulkaction']) && isset($_POST['TB_action'])) {
			$this->handle_dialog_actions();
		}

		/**
		 * Display the actual table.
		 */
		$table = new NGG_Image_List_Table( self::BASE );
		$table->prepare_items();
		?>
		<div class="wrap">
			<?php $this->print_gallery_overview($table->items) ?>
			<form method="post">
				<input type="hidden" id="page-name" name="page" value="nggallery-manage-gallery2"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
		$this->print_dialogs();
		$this->print_scripts();
	}

	private function print_gallery_overview($images) {
		/**
		 * @global $nggdb nggdb
		 */
		global $nggdb;
		?>
		<h2><?php _e( 'Gallery', 'nggallery' ) ?> <?php esc_html_e($this->gallery->title) ?></h2>

		<form id="updategallery" class="nggform" method="POST" action="<?php echo self::BASE . '&mode=image&gid=' . $this->id . '&paged=' . $_GET['paged']; ?>" accept-charset="utf-8">
			<?php wp_nonce_field('ngg_update_gallery') ?>
			<?php if ( nggGallery::current_user_can( 'NextGEN Edit gallery options' )) { ?>
			<div id="poststuff">
				<div id="gallerydiv" class="postbox <?php echo postbox_classes('gallery_div', 'ngg-manage'); ?>" >
				<h3 class="hndle"><?php _e('Gallery settings', 'nggallery') ?></h3>
				<div class="inside">
					<table class="form-table" id="gallery-properties">
						<tr>
							<td align="left"><label for="title"><?php _e('Title') ?></label></td>
							<td align="left"><input type="text" id="title" name="title" class="regular-text" value="<?php esc_attr_e($this->gallery->title) ?>"/></td>
							<td align="right"><label for="pageid"><?php _e('Page Link', 'nggallery') ?></label></td>
							<td align="left">
								<select id="pageid" name="pageid">
									<option value="0" ><?php _e('Not linked', 'nggallery') ?></option>
									<?php parent_dropdown(intval($this->gallery->pageid)); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td align="left"><label for="gallery_desc"><?php _e('Description') ?></label></td>
							<td align="left"><textarea name="gallery_desc" id="gallery_desc" cols="46" rows="3" ><?php echo $this->gallery->galdesc; ?></textarea></td>
							<td align="right"><label for="preview_pic"><?php _e('Preview image', 'nggallery') ?></label></td>
							<td align="left">
								<select name="preview_pic" id="preview_pic">
									<option value="0" ><?php _e('No Picture', 'nggallery') ?></option>
									<?php
		                                // ensure that a preview pic from a other page is still shown here
		                                if ( intval($this->gallery->previewpic) != 0) {
		                                    if ( !array_key_exists ($this->gallery->previewpic, $images )){
		                                        $previewpic = $nggdb->find_image($this->gallery->previewpic);
		                                        if ($previewpic)
		                                            echo '<option value="'.$previewpic->pid.'" selected>'.$previewpic->pid.' - ' . esc_attr( $previewpic->filename ) . '</option>'."\n";
		                                    }
		                                }
										if(is_array($images)) {
											foreach($images as $picture) {
		                                        if ($picture->exclude) continue;
												$selected = ($picture->pid == $this->gallery->previewpic) ? 'selected' : '';
												echo '<option value="'.$picture->pid.'" '.$selected.' >'.$picture->pid.' - ' . esc_attr( $picture->filename ) . '</option>'."\n";
											}
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td align="left"><label for="path"><?php _e('Path', 'nggallery') ?></label></td>
							<td align="left">
								<input <?php if ( is_multisite() ) echo 'readonly = "readonly"'; ?> type="text" name="path" class="regular-text code" id="path" value="<?php echo $this->gallery->path; ?>"/>
							</td>
							<td align="right"><label for="author"><?php _e('Author', 'nggallery'); ?></label></td>
							<td align="left"><?php echo get_userdata( (int) $this->gallery->author )->display_name ?></td>
						</tr>
						<tr>
							<td align="left"><?php _e('Gallery ID', 'nggallery') ?>:</td>
							<td align="right"><?php echo $this->gallery->gid; ?></td>
							<?php if(current_user_can( 'publish_pages' )) { ?>
							<td align="right"><label for="parent_id"><?php _e('Create new page', 'nggallery') ?></label></td>
							<td align="left">
							<select name="parent_id" id="parent_id">
								<option value="0"><?php _e ('Main page (No parent)', 'nggallery'); ?></option>
								<?php if (get_post()) {
									parent_dropdown();
								 } ?>
							</select>
							<input class="button-secondary action" type="submit" name="addnewpage" value="<?php _e ('Add page', 'nggallery'); ?>" id="group"/>
							</td>
							<?php } ?>
						</tr>
                        <?php do_action('ngg_manage_gallery_settings', $this->id); ?>
					</table>
					<div class="submit">
						<!-- To remove in future versions -->
						<input type="submit" onclick="return confirm('<?php _e("This will change folder and file names (e.g. remove spaces, special characters, ...)","nggallery")?>\n\n<?php _e("You will need to update your URLs if you link directly to the images.","nggallery")?>\n\n<?php _e("Press OK to proceed, and Cancel to stop.","nggallery")?>')" class="button-secondary" name="scanfolder" value="<?php _e("Scan folder for new images",'nggallery'); ?> " />
						<input type="submit" class="button-primary action" name="updatepictures" value="<?php _e("Save Changes",'nggallery'); ?>" />
					</div>
				</div>
			</div>
		</div> <!-- poststuff -->
	<?php }
    }

	/**
	 * Print the HTML for the dialogs.
	 */
	private function print_dialogs() {
		?>
		<style>
			.ngg-dialog-container {
				display: none;
			}
		</style>
		<div class="ngg-dialog-container">
			<!-- Add Gallery -->
			<form id="add_gallery_dialog" method="POST" accept-charset="utf-8">
				<?php wp_nonce_field( 'ngg_add_gallery' ); ?>
				<label>
					<strong><?php _e( 'Name', 'nggallery' ); ?>: </strong>
					<input id="gallery_name" type="text" class="regular-text" name="gallery_name"/>
				</label>
				<br>
				<?php if ( ! is_multisite() ) { ?>
					<?php _e( 'Create a new , empty gallery below the folder', 'nggallery' ); ?>
					<strong><?php echo get_option('ngg_options')['gallerypath']; ?></strong><br/>
				<?php } ?>
				<p class="description">
					<?php _e( 'Allowed characters for file and folder names are', 'nggallery' ); ?>: a-z, A-Z, 0-9, -, _
				</p>
				<?php do_action( 'ngg_add_new_gallery_form' ); ?>
			</form>
			<!-- /Add Gallery -->
			<!-- #resize_images -->
			<form id="resize_images_dialog" method="POST" accept-charset="utf-8">
				<?php wp_nonce_field('ngg_resize_images') ?>
				<input type="hidden" id="resize_images_imagelist" name="TB_imagelist" value="" />
				<input type="hidden" id="resize_images_bulkaction" name="TB_bulkaction" value="" />
				<input type="hidden" name="TB_action" value="resize_images" />
				<table width="100%">
					<tr valign="top">
						<td>
							<strong><?php _e('Resize Images to', 'nggallery'); ?>:</strong>
						</td>
						<td>
							<label for="imgWidth"><?php _e('Width','nggallery') ?></label>
							<input type="number" min="0" class="small-text" id="imgWidth" name="imgWidth" value="<?php echo get_option('ngg_options')['imgWidth']; ?>" />
							<label for="imgHeight"><?php _e('Height','nggallery') ?></label>
							<input type="number" min="0" size="5" name="imgHeight" id="imgHeight" class="small-text" value="<?php echo get_option('ngg_options')['imgHeight']; ?>">
							<p class="description"><?php _e('Width and height (in pixels). NextCellent Gallery will keep the ratio size.','nggallery') ?></p>
						</td>
					</tr>
				</table>
			</form>
			<!-- /#resize_images -->
			<!-- #new_thumbnail -->
			<form id="new_thumbnail_dialog" method="POST" accept-charset="utf-8">
				<?php wp_nonce_field('ngg_new_thumbnail') ?>
				<input type="hidden" id="new_thumbnail_imagelist" name="TB_imagelist" value="" />
				<input type="hidden" id="new_thumbnail_bulkaction" name="TB_bulkaction" value="" />
				<input type="hidden" name="TB_action" value="new_thumbnails" />
				<table width="100%">
					<tr valign="top">
						<th align="left"><?php _e('Size','nggallery') ?></th>
						<td>
							<label for="thumbwidth"><?php _e('Width','nggallery') ?></label>
							<input id="thumbwidth" class="small-text" type="number" min="0" name="thumbwidth" value="<?php echo get_option('ngg_options')['thumbwidth']; ?>" />
							<label for="thumbheight"><?php _e('Height','nggallery') ?></label>
							<input id="thumbheight" class="small-text" type="number" step="1" min="0" name="thumbheight" value="<?php echo get_option('ngg_options')['thumbheight']; ?>" />
							<p class="description"><?php _e('These values are maximum values ','nggallery') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th align="left">
							<label for="thumbfix">
								<?php _e('Fixed size','nggallery'); ?>
							</label>
						</th>
						<td>
							<input id="thumbfix" type="checkbox" name="thumbfix" value="1" <?php checked('1', get_option('ngg_options')['thumbfix']); ?> />
							<?php _e('This will ignore the aspect ratio, so no portrait thumbnails','nggallery') ?>
						</td>
					</tr>
				</table>
			</form>
			<!-- /#new_thumbnail -->
		</div>
		<?php
	}

	/**
	 * Print the Javascript.
	 */
	private function print_scripts() {
		?>
		<script type="text/javascript">
			jQuery(function () {
				jQuery("#new-gallery").click(function () {
					addGalleryDialog();
					return false;
				});
				jQuery("[id^=doaction]").click(function (event) {
					return handleBulkActions(event);
				});
			});

			var doActionToSelector = {
				doaction: "#bulk-action-selector-top",
				doaction2: "#bulk-action-selector-bottom"
			};

			function handleBulkActions(event) {
				var caller = event.target;
				var $selector = jQuery(doActionToSelector[caller.id]);
				var $selected = jQuery("input[name^=doaction]:checkbox:checked");

				if ($selected.length < 1) {
					alert('<?php echo esc_js(__('No images selected', 'nggallery')); ?>');
					return false;
				}
				switch ($selector.val()) {
					case "-1":
						alert('<?php echo esc_js(__('No action selected.', 'nggallery')); ?>');
						break;
					case 'resize_images':
						bulkDialog('resize_images', '<?php echo esc_js(__('Resize images','nggallery')); ?>', $selected);
						break;
					case 'new_thumbnail':
						bulkDialog('new_thumbnail', '<?php echo esc_js(__('Create new thumbnails','nggallery')); ?>', $selected);
						break;
					case 'import_meta':
						ajaxOperation('gallery_import_metadata', '<?php echo esc_js(__('Import metadata','nggallery')); ?>', $selected, true);
						break;
					case 'recover_images':
						ajaxOperation('gallery_recover_image', '<?php echo esc_js(__('Recover from backup','nggallery')); ?>', $selected, true);
						break;
					case 'set_watermark':
						ajaxOperation('gallery_set_watermark', '<?php echo esc_js(__('Set watermark','nggallery')); ?>', $selected, true);
						break;
					default:
						var images = $selected.map(function () {
							return this.value;
						}).get();
						var message = '<?php echo sprintf(esc_js(__("You are about to start bulk edits for %s galleries\n\n 'Cancel' to stop, 'OK' to proceed.", 'nggallery' )), "' + images.length + '") ?>';
						return confirm(message);
				}
				return false;
			}

			function ajaxOperation(command, title, $selected, warning) {

				var images = $selected.map(function () {
					return this.value;
				}).get();

				if (warning) {
					var message = '<?php echo sprintf(esc_js(__("You are about to start bulk edits for %s galleries\n\n 'Cancel' to stop, 'OK' to proceed.", 'nggallery' )), "' + images.length + '") ?>';

					if (!confirm(message)) {
						return false;
					}
				}

				var ajaxOptions = {
					operation: command,
					ids: images,
					header: title,
					maxStep: images.length
				};

				nggProgressBar.init(ajaxOptions);
				nggAjax.init(ajaxOptions);
			}

			function bulkDialog(id, title, $selected) {
				jQuery('#' + id + "_bulkaction").val(id);
				jQuery('#' + id + "_imagelist").val($selected.map(function () {
					return this.value;
				}).get().join(','));
				showDialog('#' + id + "_dialog", title);
			}

			function addGalleryDialog() {
				showDialog("#add_gallery_dialog", '<?php echo esc_js(__('Add a new gallery','nggallery')); ?>');
			}

			function showDialog(id, title) {
				jQuery(id).dialog({
					width: '50%',
					resizable: false,
					modal: true,
					title: title,
					buttons: [
						{
							text: "<?php echo esc_js(__('Annuleren','nggallery')); ?>",
							'class': "button dialog-cancel",
							'type': "reset",
							click: function () {
								jQuery(this).dialog('close');
							}
						},
						{
							text: "<?php echo esc_js(__('OK','nggallery')); ?>",
							'class': "button-primary",
							'type': "submit",
							click: function () {
								jQuery(this).submit();
							}
						}
					]
				});
			}
		</script>
		<?php
	}

	/**
	 * Handle the actions that require a dialog.
	 */
	private function handle_dialog_actions() {

		$ngg_options = get_option('ngg_options');

		switch($_POST['TB_action']) {
			case 'resize_images':
				check_admin_referer('ngg_resize_images');

				$ngg_options['imgWidth']  = (int) $_POST['imgWidth'];
				$ngg_options['imgHeight'] = (int) $_POST['imgHeight'];

				$command = 'gallery_resize_image';
				$title = __('Resize images','nggallery');
				break;
			case 'new_thumbnails':
				check_admin_referer('ngg_new_thumbnail');

				$ngg_options['thumbwidth']  = (int)  $_POST['thumbwidth'];
				$ngg_options['thumbheight'] = (int)  $_POST['thumbheight'];
				$ngg_options['thumbfix']    = isset ($_POST['thumbfix']) ? true : false;

				$command = 'gallery_create_thumbnail';
				$title = __('Create new thumbnails','nggallery');
				break;
			default:
				return;
		}

		//TODO What is in the case the user has no if cap 'NextGEN Change options' ? Check feedback
		update_option('ngg_options', $ngg_options);
		$gallery_ids  = explode(',', $_POST['TB_imagelist']);
		nggAdmin::do_ajax_operation( $command , $gallery_ids, $title );
	}

	/**
	 * Handle the bulk actions.
	 */
	private function handle_bulk_actions() {
		//Check the nonce.
		if(wp_verify_nonce($_POST['_wpnonce'], 'bulk-ngg-gallery-manager') === false) {
			nggGallery::show_error(__('You waited too long, or you cheated.','nggallery'));
			return;
		}

		global $wpdb, $ngg;

		if($_POST['action'] !== "-1" && $_POST['action2'] !== "-1" && !($_POST['action'] === "delete_gallery" || $_POST['action2'] === "delete_gallery")) {
			return;
		}

		// Delete gallery
		if ( is_array($_POST['doaction']) ) {
			$deleted = false;
			foreach ( $_POST['doaction'] as $id ) {
				// get the path to the gallery
				$gallery = nggdb::find_gallery($id);
				if ($gallery){
					//TODO:Remove also Tag reference, look here for ids instead filename
					$imagelist = $wpdb->get_col("SELECT filename FROM $wpdb->nggpictures WHERE galleryid = '$gallery->gid' ");
					if ($ngg->options['deleteImg']) {
						if (is_array($imagelist)) {
							foreach ($imagelist as $filename) {
								@unlink(WINABSPATH . $gallery->path . '/thumbs/thumbs_' . $filename);
								@unlink(WINABSPATH . $gallery->path .'/'. $filename);
								@unlink(WINABSPATH . $gallery->path .'/'. $filename . '_backup');
							}
						}
						// delete folder
						@rmdir( WINABSPATH . $gallery->path . '/thumbs' );
						@rmdir( WINABSPATH . $gallery->path );
					}
				}
				do_action('ngg_delete_gallery', $id);
				$deleted = nggdb::delete_gallery( $id );
			}

			if($deleted) {
				nggGallery::show_message( __( 'Gallery deleted successfully.', 'nggallery' ) );
			} else {
				nggGallery::show_error( __( 'Something went wrong.', 'nggallery' ) );
			}

		}
	}

	/**
	 * Add a new gallery.
	 */
	private function handle_add_gallery() {

		if( wp_verify_nonce($_POST['_wpnonce'], 'ngg_add_gallery') === false || !nggGallery::current_user_can( 'NextGEN Add new gallery' )) {
			nggGallery::show_error(__('You waited too long, or you cheated.','nggallery'));
			return;
		}

		// get the default path for a new gallery
		$default_path = get_option('ngg_options')['gallerypath'];
		$new_gallery = esc_attr( $_POST['gallery_name']);
		if ( !empty($new_gallery) ) {
			nggAdmin::create_gallery($new_gallery, $default_path);
		}

		do_action( 'ngg_update_addgallery_page' );
	}
}