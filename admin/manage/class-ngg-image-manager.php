<?php
include_once( 'NGG_Image_List_Table.php' );
include_once( 'class-ngg-manager.php' );

/**
 * Class NGG_Gallery_Manager
 *
 * Display the gallery managing page.
 */
class NGG_Image_Manager extends NGG_Manager {

	private $gallery;
	private $id;

	public function __construct() {
		$this->id = (int) $_GET['gid'];
	}

	/**
	 * Display the page.
	 */
	public function display() {

		var_dump($_POST);

		parent::display();

		if (isset ($_POST['update_images']) )  {
			$this->handle_update_images();
		}

		/**
		 * @global $nggdb nggdb
		 */
		global $nggdb;

		$this->gallery = $nggdb->find_gallery($this->id);

		/**
		 * Display the actual table.
		 */
		$table = new NGG_Image_List_Table( self::BASE );
		$table->prepare_items();
		?>
		<div class="wrap">

			<form id="updategallery" class="nggform" method="POST" action="<?php echo self::BASE . '&mode=image&gid=' . $this->id . '&paged=' . $_GET['paged']; ?>" accept-charset="utf-8">
				<?php wp_nonce_field('ngg-update-gallery', '_ngg_nonce_gallery'); ?>
				<?php $this->print_gallery_overview($table->items) ?>
				<input type="hidden" id="page-name" name="page" value="nggallery-manage-gallery2"/>
				<input type="hidden" id="page_type" name="page_type" value="image"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
		$this->print_dialogs();
		$this->print_scripts();
	}

	/**
	 * @todo Make this better.
	 */
	protected function print_scripts() {
		parent::print_scripts();
		?>
		<script type="text/javascript">
			jQuery(function () {
				// load a content via ajax
				jQuery('a.ngg-dialog').click(function () {
					var $spinner = jQuery("#spinner");
					if ($spinner.length == 0)
						jQuery("body").append('<div id="spinner"></div>');
					var $this = jQuery(this);
					$spinner.fadeIn();
					var dialog = jQuery('<div style="display:none"></div>').appendTo('body');
					// load the remote content
					dialog.load(
						this.href,
						{},
						function () {
							jQuery('#spinner').hide();
							dialog.dialog({
								title: ($this.attr('title')) ? $this.attr('title') : '',
								width: 'auto',
								height: 'auto',
								modal: true,
								resizable: true,
								position: {my: "center", at: "center", of: window},
								close: function () {
									dialog.remove();
								}
							});
						}
					);
					//prevent the browser to follow the link
					return false;
				});
			});
		</script>

		<?php
	}

	private function handle_update_images() {

		if(wp_verify_nonce($_POST['_ngg_nonce_gallery'], 'ngg-update-gallery') === false) {
			nggGallery::show_error(__('You waited too long, or you cheated.','nggallery'));
			return;
		}

		global $wpdb;

		if ( nggGallery::current_user_can( 'NextGEN Edit gallery options' ) ) {

			if ( nggGallery::current_user_can( 'NextGEN Edit gallery title' )) {
			    // don't forget to update the slug
			    $slug = nggdb::get_unique_slug( sanitize_title( $_POST['title'] ), 'gallery', $this->id );
			    $wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET title= '%s', slug= '%s' WHERE gid = %d", esc_attr($_POST['title']), $slug, $this->id) );
			}
			if ( nggGallery::current_user_can( 'NextGEN Edit gallery path' ))
				{$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET path= '%s' WHERE gid = %d", untrailingslashit ( str_replace('\\', '/', trim( stripslashes($_POST['path']) )) ), $this->id ) );}
			if ( nggGallery::current_user_can( 'NextGEN Edit gallery description' ))
				{$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET galdesc= '%s' WHERE gid = %d", esc_attr( $_POST['gallery_desc'] ), $this->id) );}
			if ( nggGallery::current_user_can( 'NextGEN Edit gallery page id' ))
				{$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET pageid= '%d' WHERE gid = %d", (int) $_POST['page_id'], $this->id) );}
			if ( nggGallery::current_user_can( 'NextGEN Edit gallery preview pic' ))
				{$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET previewpic= '%d' WHERE gid = %d", (int) $_POST['preview_pic'], $this->id) );}
			if ( isset ($_POST['author']) && nggGallery::current_user_can( 'NextGEN Edit gallery author' ) )
				{$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET author= '%d' WHERE gid = %d", (int) $_POST['author'], $this->id) );}

            wp_cache_delete($this->id, 'ngg_gallery');

		}

		$this->update_pictures();

		//hook for other plugin to update the fields
		do_action('ngg_update_gallery', $this->id, $_POST);

		nggGallery::show_message(__('Update successful',"nggallery"));
    }

    /**
	 * @todo Make a real DAO system for NextCellent.
	 */
    private function update_pictures() {
		global $wpdb, $nggdb;

		//TODO:Error message when update failed

		$description = 	isset ( $_POST['description'] ) ? $_POST['description'] : array();
		$alttext = 		isset ( $_POST['alttext'] ) ? $_POST['alttext'] : array();
		$exclude = 		isset ( $_POST['exclude'] ) ? $_POST['exclude'] : false;
		$taglist = 		isset ( $_POST['tags'] ) ? $_POST['tags'] : false;
		$pictures = 	isset ( $_POST['pid'] ) ? $_POST['pid'] : false;
		$date =  		isset ( $_POST['date'] ) ? $_POST['date'] : "NOW()"; //Not sure if NOW() will work or not but in theory it should

		if ( is_array($pictures) ){
			foreach( $pictures as $pid ){
                $image = $nggdb->find_image( $pid );
                if ($image) {
                    // description field
                    $image->description = $description[$image->pid];
                    $image->date = $date[$image->pid];
                    // only uptade this field if someone change the alttext
                    if ( $image->alttext != $alttext[$image->pid] ) {
                        $image->alttext = $alttext[$image->pid];
                        $image->image_slug = nggdb::get_unique_slug( sanitize_title( $image->alttext ), 'image', $image->pid );
                    }

                    // set exclude flag
                    if ( is_array($exclude) )
    					{$image->exclude = ( array_key_exists($image->pid, $exclude) )? 1 : 0;}
    				else
    					{$image->exclude = 0;}

                    // update the database
                    $wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggpictures SET image_slug = '%s', alttext = '%s', description = '%s', exclude = %d, imagedate = %s WHERE pid = %d",
                                                                                 $image->image_slug, $image->alttext, $image->description, $image->exclude, $image->date, $image->pid) );
                    // remove from cache
                    wp_cache_delete($image->pid, 'ngg_image');

                    // hook for other plugins after image is updated
                    do_action('ngg_image_updated', $image);
                }

            }
        }

        //TODO: This produce 300-400 queries !
		if ( is_array($taglist) ) {
			foreach($taglist as $key=>$value) {
				$tags = explode(',', $value);
				wp_set_object_terms($key, $tags, 'ngg_tag');
			}
		}
	}

	private function print_gallery_overview($images) {
		/**
		 * @global $nggdb nggdb
		 */
		global $nggdb;
		?>
		<h2><?php _e( 'Gallery', 'nggallery' ) ?> <?php esc_html_e($this->gallery->title) ?></h2>
		<?php if ( nggGallery::current_user_can( 'NextGEN Edit gallery options' )) { ?>
		<div id="poststuff">
			<div id="gallerydiv" class="postbox <?php echo postbox_classes('gallery_div', 'ngg-manage'); ?>" >
			<h3 class="hndle"><?php _e('Gallery settings', 'nggallery') ?></h3>
			<div class="inside">
				<table class="form-table" id="gallery-properties">
					<tr>
						<td align="left"><label for="title"><?php _e('Title') ?></label></td>
						<td align="left"><input type="text" id="title" name="title" class="regular-text" value="<?php esc_attr_e($this->gallery->title) ?>"/></td>
						<td align="right"><label for="page_id"><?php _e('Page Link', 'nggallery') ?></label></td>
						<td align="left">
							<select id="page_id" name="page_id">
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
	                                            {echo '<option value="'.$previewpic->pid.'" selected>'.$previewpic->pid.' - ' . esc_attr( $previewpic->filename ) . '</option>'."\n";}
	                                    }
	                                }
									if(is_array($images)) {
										foreach($images as $picture) {
	                                        if ($picture->exclude) {continue;}
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
							<input <?php if ( is_multisite() ) {echo 'readonly = "readonly"';} ?> type="text" name="path" class="regular-text code" id="path" value="<?php echo $this->gallery->path; ?>"/>
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
					<input type="submit" class="button-primary action" name="update_images" value="<?php _e("Save Changes",'nggallery'); ?>" />
				</div>
			</div>
			</div>
		</div> <!-- poststuff -->
		<?php
		}
    }
}