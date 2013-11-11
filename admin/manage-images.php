<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {	die('You are not allowed to call this page directly.');}

function nggallery_picturelist() {
// *** show picture list
	global $wpdb, $nggdb, $user_ID, $ngg;

	// Look if its a search result
	$is_search = isset ($_GET['s']) ? true : false;
	$counter	= 0;

    $wp_list_table = new _NGG_Images_List_Table('nggallery-manage-images');

    if ($is_search) {

		// fetch the imagelist
		$picturelist = $ngg->manage_page->search_result;

		// we didn't set a gallery or a pagination
		$act_gid     = 0;
		$_GET['paged'] = 1;
		$page_links = false;

	} else {

		// GET variables
		$act_gid    = $ngg->manage_page->gid;

		// Load the gallery metadata
		$gallery = $nggdb->find_gallery($act_gid);

		if (!$gallery) {
			nggGallery::show_error(__('Gallery not found.', 'nggallery'));
			return;
		}

		// Check if you have the correct capability
		if (!nggAdmin::can_manage_this_gallery($gallery->author)) {
			nggGallery::show_error(__('Sorry, you have no access here', 'nggallery'));
			return;
		}

		// look for pagination
        $_GET['paged'] = isset($_GET['paged']) && ($_GET['paged'] > 0) ? absint($_GET['paged']) : 1;

		$start = ( $_GET['paged'] - 1 ) * 50;

		// get picture values
		$picturelist = $nggdb->get_gallery($act_gid, $ngg->options['galSort'], $ngg->options['galSortDir'], false, 50, $start );

		// get the current author
		$act_author_user    = get_userdata( (int) $gallery->author );

	}

		// list all galleries
		$gallerylist = $nggdb->find_all_galleries();

		//get the columns
		$image_columns   = $wp_list_table->get_columns();
		$hidden_columns  = get_hidden_columns('nggallery-manage-images');
		$num_columns     = count($image_columns) - count($hidden_columns);

		$attr = (nggGallery::current_user_can( 'NextGEN Edit gallery options' )) ? '' : 'disabled="disabled"';

?>
<script type="text/javascript">
<!--
function showDialog( windowId, title ) {
	var form = document.getElementById('updategallery');
	var elementlist = "";
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]")
				if(form.elements[i].checked == true)
					if (elementlist == "")
						elementlist = form.elements[i].value
					else
						elementlist += "," + form.elements[i].value ;
		}
	}
	jQuery("#" + windowId + "_bulkaction").val(jQuery("#bulkaction").val());
	jQuery("#" + windowId + "_imagelist").val(elementlist);
    // now show the dialog
	jQuery( "#" + windowId ).dialog({
		width: 640,
        resizable : false,
		modal: true,
        title: title
	});
    jQuery("#" + windowId + ' .dialog-cancel').click(function() { jQuery( "#" + windowId ).dialog("close"); });
}

jQuery(function (){
    // load a content via ajax
    jQuery('a.ngg-dialog').click(function() {
        if ( jQuery( "#spinner" ).length == 0)
            jQuery("body").append('<div id="spinner"></div>');
        var $this = jQuery(this);
        var results = new RegExp('[\\?&]w=([^&#]*)').exec(this.href);
	    var width  = ( results ) ? results[1] : 600;
        var results = new RegExp('[\\?&]h=([^&#]*)').exec(this.href);
	    var height = ( results ) ? results[1] : 440;
        jQuery('#spinner').fadeIn();
        var dialog = jQuery('<div style="display:hidden"></div>').appendTo('body');
        // load the remote content
        dialog.load(
            this.href,
            {},
            function () {
                jQuery('#spinner').hide();
                dialog.dialog({
                    title: ($this.attr('title')) ? $this.attr('title') : '',
                    width: width,
                    height: height,
                    modal: true,
                    resizable: false,
                    close: function() { dialog.remove(); }
                }).width(width - 30).height(height - 30);
            }
        );
        //prevent the browser to follow the link
        return false;
    });
});

function checkAll(form)
{
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]") {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
}

function getNumChecked(form)
{
	var num = 0;
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]")
				if(form.elements[i].checked == true)
					num++;
		}
	}
	return num;
}

// this function check for a the number of selected images, sumbmit false when no one selected
function checkSelected() {

	var numchecked = getNumChecked(document.getElementById('updategallery'));

    if (typeof document.activeElement == "undefined" && document.addEventListener) {
    	document.addEventListener("focus", function (e) {
    		document.activeElement = e.target;
    	}, true);
    }

    if ( document.activeElement.name == 'post_paged' )
        return true;

	if(numchecked < 1) {
		alert('<?php echo esc_js(__('No images selected', 'nggallery')); ?>');
		return false;
	}

	actionId = jQuery('#bulkaction').val();

	switch (actionId) {
		case "copy_to":
			showDialog('selectgallery', '<?php echo esc_js(__('Copy image to...','nggallery')); ?>');
			return false;
			break;
		case "move_to":
			showDialog('selectgallery', '<?php echo esc_js(__('Move image to...','nggallery')); ?>');
			return false;
			break;
		case "add_tags":
			showDialog('entertags', '<?php echo esc_js(__('Add new tags','nggallery')); ?>');
			return false;
			break;
		case "delete_tags":
			showDialog('entertags', '<?php echo esc_js(__('Delete tags','nggallery')); ?>');
			return false;
			break;
		case "overwrite_tags":
			showDialog('entertags', '<?php echo esc_js(__('Overwrite','nggallery')); ?>');
			return false;
			break;
		case "resize_images":
			showDialog('resize_images', '<?php echo esc_js(__('Resize images','nggallery')); ?>');
			return false;
			break;
		case "new_thumbnail":
			showDialog('new_thumbnail', '<?php echo esc_js(__('Create new thumbnails','nggallery')); ?>');
			return false;
			break;
	}

	return confirm('<?php echo sprintf(esc_js(__("You are about to start the bulk edit for %s images \n \n 'Cancel' to stop, 'OK' to proceed.",'nggallery')), "' + numchecked + '") ; ?>');
}

jQuery(document).ready( function() {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	postboxes.add_postbox_toggles('ngg-manage-gallery');
});

//-->
</script>
<div class="wrap">
<?php include('templates/social_media_buttons.php'); ?>
<?php screen_icon( 'nextgen-gallery' ); ?>
<?php if ($is_search) :?>
<h2><?php printf( __('Search results for &#8220;%s&#8221;', 'nggallery'), esc_html( get_search_query() ) ); ?></h2>
<form class="search-form" action="" method="get">
<p class="search-box">
	<label class="hidden" for="media-search-input"><?php _e( 'Search Images', 'nggallery' ); ?>:</label>
	<input type="hidden" id="page-name" name="page" value="nggallery-manage-gallery" />
	<input type="text" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
	<input type="submit" value="<?php _e( 'Search Images', 'nggallery' ); ?>" class="button" />
</p>
</form>

<br style="clear: both;" />

<form id="updategallery" class="nggform" method="POST" action="<?php echo $ngg->manage_page->base_page . '&amp;mode=edit&amp;s=' . get_search_query(); ?>" accept-charset="utf-8">
<?php wp_nonce_field('ngg_updategallery') ?>
<input type="hidden" name="page" value="manage-images" />

<?php else :?>
<h2><?php echo _n( 'Gallery', 'Galleries', 1, 'nggallery' ); ?> : <?php echo esc_html ( nggGallery::i18n($gallery->title) ); ?></h2>

<br style="clear: both;" />

<form id="updategallery" class="nggform" method="POST" action="<?php echo $ngg->manage_page->base_page . '&amp;mode=edit&amp;gid=' . $act_gid . '&amp;paged=' . $_GET['paged']; ?>" accept-charset="utf-8">
<?php wp_nonce_field('ngg_updategallery') ?>
<input type="hidden" name="page" value="manage-images" />

<?php if ( nggGallery::current_user_can( 'NextGEN Edit gallery options' )) : ?>
<div id="poststuff">
	<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
	<div id="gallerydiv" class="postbox <?php echo postbox_classes('gallerydiv', 'ngg-manage-gallery'); ?>" >
		<h3><?php _e('Gallery settings', 'nggallery') ?><small> (<?php _e('Click here for more settings', 'nggallery') ?>)</small></h3>
		<div class="inside">
			<table class="form-table" >
				<tr>
					<th align="left"><?php _e('Title') ?>:</th>
					<th align="left"><input <?php nggGallery::current_user_can_form( 'NextGEN Edit gallery title' ); ?> type="text" size="50" name="title" value="<?php echo $gallery->title; ?>"  /></th>
					<th align="right"><?php _e('Page Link to', 'nggallery') ?>:</th>
					<th align="left">
					<select <?php nggGallery::current_user_can_form( 'NextGEN Edit gallery page id' ); ?>  name="pageid" style="width:95%">
						<option value="0" ><?php _e('Not linked', 'nggallery') ?></option>
						<?php $err = error_reporting(0); ?>
						<?php parent_dropdown(intval($gallery->pageid)); ?>
						<?php error_reporting($err); ?>
					</select>
					</th>
				</tr>
				<tr>
					<th align="left"><?php _e('Description') ?>:</th>
					<th align="left"><textarea  <?php nggGallery::current_user_can_form( 'NextGEN Edit gallery description' ); ?> name="gallerydesc" cols="30" rows="3" style="width: 95%" ><?php echo $gallery->galdesc; ?></textarea></th>
					<th align="right"><?php _e('Preview image', 'nggallery') ?>:</th>
					<th align="left">
						<select <?php nggGallery::current_user_can_form( 'NextGEN Edit gallery preview pic' ); ?> name="previewpic" style="width:95%" >
							<option value="0" ><?php _e('No Picture', 'nggallery') ?></option>
							<?php
                                // ensure that a preview pic from a other page is still shown here
                                if ( intval($gallery->previewpic) != 0) {
                                    if ( !array_key_exists ($gallery->previewpic, $picturelist )){
                                        $previewpic = $nggdb->find_image($gallery->previewpic);
                                        if ($previewpic)
                                            echo '<option value="'.$previewpic->pid.'" selected="selected" >'.$previewpic->pid.' - ' . esc_attr( $previewpic->filename ) . '</option>'."\n";
                                    }
                                }
								if(is_array($picturelist)) {
									foreach($picturelist as $picture) {
                                        if ($picture->exclude) continue;
										$selected = ($picture->pid == $gallery->previewpic) ? 'selected="selected" ' : '';
										echo '<option value="'.$picture->pid.'" '.$selected.'>'.$picture->pid.' - ' . esc_attr( $picture->filename ) . '</option>'."\n";
									}
								}
							?>
						</select>
					</th>
				</tr>
				<tr>
					<th align="left"><?php _e('Path', 'nggallery') ?>:</th>
					<th align="left"><input <?php if ( is_multisite() ) echo 'readonly = "readonly"'; ?> <?php nggGallery::current_user_can_form( 'NextGEN Edit gallery path' ); ?> type="text" size="50" name="path" value="<?php echo $gallery->path; ?>"  /></th>
					<th align="right"><?php _e('Author', 'nggallery'); ?>:</th>
					<th align="left">
					<?php
						$editable_ids = $ngg->manage_page->get_editable_user_ids( $user_ID );
						if ( $editable_ids && count( $editable_ids ) > 1 && nggGallery::current_user_can( 'NextGEN Edit gallery author')  )
							wp_dropdown_users( array('include' => $editable_ids, 'name' => 'author', 'selected' => empty( $gallery->author ) ? 0 : $gallery->author ) );
						else
							echo $act_author_user->display_name;
					?>
					</th>
				</tr>
				<?php if(current_user_can( 'publish_pages' )) : ?>
				<tr>
					<th align="left">&nbsp;</th>
					<th align="left">&nbsp;</th>
					<th align="right"><?php _e('Create new page', 'nggallery') ?>:</th>
					<th align="left">
					<select name="parent_id" style="width:95%">
						<option value="0"><?php _e ('Main page (No parent)', 'nggallery'); ?></option>
						<?php if (get_post()): ?>
						<?php parent_dropdown (); ?>
						<?php endif ?>
					</select>
					<input class="button-secondary action" type="submit" name="addnewpage" value="<?php _e ('Add page', 'nggallery'); ?>" id="group"/>
					</th>
				</tr>
				<?php endif; ?>
                <?php do_action('ngg_manage_gallery_settings', $act_gid); ?>

			</table>

			<div class="submit">
				<input type="submit" class="button-secondary" name="scanfolder" value="<?php _e("Scan Folder for new images",'nggallery'); ?> " />
				<input type="submit" class="button-primary action" name="updatepictures" value="<?php _e("Save Changes",'nggallery'); ?>" />
			</div>

		</div>
	</div>
</div> <!-- poststuff -->
<?php endif; ?>

<?php endif; ?>

<div class="tablenav top ngg-tablenav">
    <?php $ngg->manage_page->pagination( 'top', $_GET['paged'], $nggdb->paged['total_objects'], $nggdb->paged['objects_per_page']  ); ?>
	<div class="alignleft actions">
	<select id="bulkaction" name="bulkaction">
		<option value="no_action" ><?php _e("Bulk actions",'nggallery'); ?></option>
		<option value="set_watermark" ><?php _e("Set watermark",'nggallery'); ?></option>
		<option value="new_thumbnail" ><?php _e("Create new thumbnails",'nggallery'); ?></option>
		<option value="resize_images" ><?php _e("Resize images",'nggallery'); ?></option>
		<option value="recover_images" ><?php _e("Recover from backup",'nggallery'); ?></option>
		<option value="delete_images" ><?php _e("Delete images",'nggallery'); ?></option>
		<option value="import_meta" ><?php _e("Import metadata",'nggallery'); ?></option>
		<option value="rotate_cw" ><?php _e("Rotate images clockwise",'nggallery'); ?></option>
		<option value="rotate_ccw" ><?php _e("Rotate images counter-clockwise",'nggallery'); ?></option>
		<option value="copy_to" ><?php _e("Copy to...",'nggallery'); ?></option>
		<option value="move_to"><?php _e("Move to...",'nggallery'); ?></option>
		<option value="add_tags" ><?php _e("Add tags",'nggallery'); ?></option>
		<option value="delete_tags" ><?php _e("Delete tags",'nggallery'); ?></option>
		<option value="overwrite_tags" ><?php _e("Overwrite tags",'nggallery'); ?></option>
	</select>
	<input class="button-secondary" type="submit" name="showThickbox" value="<?php _e('Apply', 'nggallery'); ?>" onclick="if ( !checkSelected() ) return false;" />

	<?php if (($ngg->options['galSort'] == "sortorder") && (!$is_search) ) { ?>
		<input class="button-secondary" type="submit" name="sortGallery" value="<?php _e('Sort gallery', 'nggallery');?>" />
	<?php } ?>

	<input type="submit" name="updatepictures" class="button-primary action"  value="<?php _e('Save Changes', 'nggallery');?>" />
	</div>
</div>

<table id="ngg-listimages" class="widefat fixed" cellspacing="0" >

	<thead>
	<tr>
<?php $wp_list_table->print_column_headers(true); ?>
	</tr>
	</thead>
	<tfoot>
	<tr>
<?php $wp_list_table->print_column_headers(false); ?>
	</tr>
	</tfoot>
	<tbody id="the-list">
<?php
if($picturelist) {

	$thumbsize 	= '';

	if ($ngg->options['thumbfix'])
		$thumbsize = 'width="' . $ngg->options['thumbwidth'] . '" height="' . $ngg->options['thumbheight'] . '"';

	foreach($picturelist as $picture) {

		//for search result we need to check the capatibiliy
		if ( !nggAdmin::can_manage_this_gallery($picture->author) && $is_search )
			continue;

		$counter++;
		$pid       = (int) $picture->pid;
		$alternate = ( !isset($alternate) || $alternate == 'alternate' ) ? '' : 'alternate';
		$exclude   = ( $picture->exclude ) ? 'checked="checked"' : '';
		$date = mysql2date(get_option('date_format'), $picture->imagedate);
		$time = mysql2date(get_option('time_format'), $picture->imagedate);

		?>
		<tr id="picture-<?php echo $pid ?>" class="<?php echo $alternate ?> iedit"  valign="top">
			<?php
			foreach($image_columns as $image_column_key => $column_display_name) {
				$class = "class='$image_column_key column-$image_column_key'";

				$style = '';
				if ( in_array($image_column_key, $hidden_columns) )
					$style = ' style="display:none;"';

				$attributes = $class . $style;

				switch ($image_column_key) {
					case 'cb' :
                        $attributes = 'class="column-cb check-column"' . $style;
						?>
						<th <?php echo $attributes ?> scope="row"><input name="doaction[]" type="checkbox" value="<?php echo $pid ?>" /></th>
						<?php
					break;
					case 'id' :
						?>
						<td <?php echo $attributes ?> style=""><?php echo $pid; ?>
							<input type="hidden" name="pid[]" value="<?php echo $pid ?>" />
						</td>
						<?php
					break;
					case 'filename' :
                        $attributes = 'class="title column-filename column-title"' . $style;
						?>
						<td <?php echo $attributes ?>>
							<strong><a href="<?php echo esc_url( $picture->imageURL ); ?>" class="thickbox" title="<?php echo esc_attr ($picture->filename); ?>">
								<?php echo ( empty($picture->alttext) ) ? esc_html( $picture->filename ) : esc_html( stripslashes(nggGallery::i18n($picture->alttext)) ); ?>
							</a></strong>
							<br /><?php echo $date; ?>
							<?php if ( !empty($picture->meta_data) ): ?>
							<br /><?php echo $picture->meta_data['width']; ?> x <?php echo $picture->meta_data['height']; ?> <?php _e('pixel', 'nggallery'); ?>

							<?php endif; ?>
							<p>
							<?php
							$actions = array();
							$actions['view']   = '<a class="shutter" href="' . esc_url( $picture->imageURL ) . '" title="' . esc_attr( sprintf(__('View "%s"'), sanitize_title ($picture->filename) )) . '">' . __('View', 'nggallery') . '</a>';
							$actions['meta']   = '<a class="ngg-dialog" href="' . NGGALLERY_URLPATH . 'admin/showmeta.php?id=' . $pid . '" title="' . __('Show Meta data','nggallery') . '">' . __('Meta', 'nggallery') . '</a>';
							$actions['custom_thumb']   = '<a class="ngg-dialog" href="' . NGGALLERY_URLPATH . 'admin/edit-thumbnail.php?id=' . $pid . '" title="' . __('Customize thumbnail','nggallery') . '">' . __('Edit thumb', 'nggallery') . '</a>';
							$actions['rotate'] = '<a class="ngg-dialog" href="' . NGGALLERY_URLPATH . 'admin/rotate.php?id=' . $pid . '" title="' . __('Rotate','nggallery') . '">' . __('Rotate', 'nggallery') . '</a>';
							if ( current_user_can( 'publish_posts' ) )
                                $actions['publish'] = '<a class="ngg-dialog" href="' . NGGALLERY_URLPATH . 'admin/publish.php?id=' . $pid . '&h=230" title="' . __('Publish this image','nggallery') . '">' . __('Publish', 'nggallery') . '</a>';
							if ( file_exists( $picture->imagePath . '_backup' ) )
                                $actions['recover']   = '<a class="confirmrecover" href="' .wp_nonce_url("admin.php?page=nggallery-manage-gallery&amp;mode=recoverpic&amp;gid=" . $act_gid . "&amp;pid=" . $pid, 'ngg_recoverpicture'). '" title="' . __('Recover','nggallery') . '" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Recover "%s" ?' , 'nggallery'), $picture->filename)). '\');if(check==false) return false;">' . __('Recover', 'nggallery') . '</a>';
							$actions['delete'] = '<a class="submitdelete" href="' . wp_nonce_url("admin.php?page=nggallery-manage-gallery&amp;mode=delpic&amp;gid=" . $act_gid . "&amp;pid=" . $pid, 'ngg_delpicture'). '" class="delete column-delete" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Delete "%s" ?' , 'nggallery'), $picture->filename)). '\');if(check==false) return false;">' . __('Delete') . '</a>';
							$action_count = count($actions);
							$i = 0;
							echo '<div class="row-actions">';
							foreach ( $actions as $action => $link ) {
								++$i;
								( $i == $action_count ) ? $sep = '' : $sep = ' | ';
								echo "<span class='$action'>$link$sep</span>";
							}
							echo '</div>';
							?></p>
						</td>
						<?php
					break;
					case 'thumbnail' :
                        $attributes = 'class="id column-thumbnail media-icon"' . $style;
						?>
						<td <?php echo $attributes ?>><a href="<?php echo esc_url ( add_query_arg('i', mt_rand(), $picture->imageURL) ); ?>" class="shutter" title="<?php echo $picture->filename ?>">
								<img class="thumb" src="<?php echo esc_url ( add_query_arg('i', mt_rand(), $picture->thumbURL) ); ?>" id="thumb<?php echo $pid ?>" />
							</a>
						</td>
						<?php
					break;
					case 'alt_title_desc' :
						?>
						<td <?php echo $attributes ?>>
							<input name="alttext[<?php echo $pid ?>]" type="text" style="width:95%; margin-bottom: 2px;" value="<?php echo stripslashes($picture->alttext) ?>" /><br/>
							<textarea name="description[<?php echo $pid ?>]" style="width:95%; margin-top: 2px;" rows="2" ><?php echo stripslashes($picture->description) ?></textarea>
						</td>
						<?php
					break;
					case 'exclude' :
						?>
						<td <?php echo $attributes ?>><input name="exclude[<?php echo $pid ?>]" type="checkbox" value="1" <?php echo $exclude ?> /></td>
						<?php
					break;
					case 'tags' :
						$picture->tags = wp_get_object_terms($pid, 'ngg_tag', 'fields=names');
						if (is_array ($picture->tags) ) $picture->tags = implode(', ', $picture->tags);
						?>
						<td <?php echo $attributes ?>><textarea name="tags[<?php echo $pid ?>]" style="width:95%;" rows="2"><?php echo $picture->tags ?></textarea></td>
						<?php
					break;
					default :
						?>
						<td <?php echo $attributes ?>><?php do_action('ngg_manage_image_custom_column', $image_column_key, $pid); ?></td>
						<?php
					break;
				}
			?>
			<?php } ?>
		</tr>
		<?php
	}
}

// In the case you have no capaptibility to see the search result
if ( $counter == 0 )
	echo '<tr><td colspan="' . $num_columns . '" align="center"><strong>'.__('No entries found','nggallery').'</strong></td></tr>';

?>

		</tbody>
	</table>
    <div class="tablenav bottom">
    <input type="submit" class="button-primary action" name="updatepictures" value="<?php _e('Save Changes', 'nggallery'); ?>" />
    <?php $ngg->manage_page->pagination( 'bottom', $_GET['paged'], $nggdb->paged['total_objects'], $nggdb->paged['objects_per_page']  ); ?>
    </div>
	</form>
	<br class="clear"/>
	</div><!-- /#wrap -->

	<!-- #entertags -->
	<div id="entertags" style="display: none;" >
		<form id="form-tags" method="POST" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_thickbox_form') ?>
		<input type="hidden" id="entertags_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="entertags_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="page" value="manage-images" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
		  	<tr>
		    	<th><?php _e("Enter the tags",'nggallery'); ?> : <input name="taglist" type="text" style="width:90%" value="" /></th>
		  	</tr>
		  	<tr align="right">
		    	<td class="submit">
		    		<input class="button-primary" type="submit" name="TB_EditTags" value="<?php _e("OK",'nggallery'); ?>" />
		    		&nbsp;
		    		<input class="button-secondary dialog-cancel" type="reset" value="&nbsp;<?php _e("Cancel",'nggallery'); ?>&nbsp;" />
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#entertags -->

	<!-- #selectgallery -->
	<div id="selectgallery" style="display: none;" >
		<form id="form-select-gallery" method="POST" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_thickbox_form') ?>
		<input type="hidden" id="selectgallery_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="selectgallery_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="page" value="manage-images" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
		  	<tr>
		    	<th>
		    		<?php _e('Select the destination gallery:', 'nggallery'); ?>&nbsp;
		    		<select name="dest_gid" style="width:90%" >
		    			<?php
		    				foreach ($gallerylist as $gallery) {
		    					if ($gallery->gid != $act_gid) {
		    			?>
						<option value="<?php echo $gallery->gid; ?>" ><?php echo $gallery->gid; ?> - <?php echo esc_attr( stripslashes($gallery->title) ); ?></option>
						<?php
		    					}
		    				}
		    			?>
		    		</select>
		    	</th>
		  	</tr>
		  	<tr align="right">
		    	<td class="submit">
		    		<input type="submit" class="button-primary" name="TB_SelectGallery" value="<?php _e("OK",'nggallery'); ?>" />
		    		&nbsp;
		    		<input class="button-secondary dialog-cancel" type="reset" value="<?php _e("Cancel",'nggallery'); ?>" />
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#selectgallery -->

	<!-- #resize_images -->
	<div id="resize_images" style="display: none;" >
		<form id="form-resize-images" method="POST" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_thickbox_form') ?>
		<input type="hidden" id="resize_images_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="resize_images_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="page" value="manage-images" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
			<tr valign="top">
				<td>
					<strong><?php _e('Resize Images to', 'nggallery'); ?>:</strong>
				</td>
				<td>
					<input type="text" size="5" name="imgWidth" value="<?php echo $ngg->options['imgWidth']; ?>" /> x <input type="text" size="5" name="imgHeight" value="<?php echo $ngg->options['imgHeight']; ?>" />
					<br /><small><?php _e('Width x height (in pixel). NextGEN Gallery will keep ratio size','nggallery') ?></small>
				</td>
			</tr>
		  	<tr align="right">
		    	<td colspan="2" class="submit">
		    		<input class="button-primary" type="submit" name="TB_ResizeImages" value="<?php _e('OK', 'nggallery'); ?>" />
		    		&nbsp;
		    		<input class="button-secondary dialog-cancel" type="reset" value="&nbsp;<?php _e('Cancel', 'nggallery'); ?>&nbsp;" />
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#resize_images -->

	<!-- #new_thumbnail -->
	<div id="new_thumbnail" style="display: none;" >
		<form id="form-new-thumbnail" method="POST" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_thickbox_form') ?>
		<input type="hidden" id="new_thumbnail_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="new_thumbnail_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="page" value="manage-images" />
        <table width="100%" border="0" cellspacing="3" cellpadding="3" >
			<tr valign="top">
				<th align="left"><?php _e('Width x height (in pixel)','nggallery') ?></th>
				<td><input type="text" size="5" maxlength="5" name="thumbwidth" value="<?php echo $ngg->options['thumbwidth']; ?>" /> x <input type="text" size="5" maxlength="5" name="thumbheight" value="<?php echo $ngg->options['thumbheight']; ?>" />
				<br /><small><?php _e('These values are maximum values ','nggallery') ?></small></td>
			</tr>
			<tr valign="top">
				<th align="left"><?php _e('Set fix dimension','nggallery') ?></th>
				<td><input type="checkbox" name="thumbfix" value="1" <?php checked('1', $ngg->options['thumbfix']); ?> />
				<br /><small><?php _e('Ignore the aspect ratio, no portrait thumbnails','nggallery') ?></small></td>
			</tr>
		  	<tr align="right">
		    	<td colspan="2" class="submit">
		    		<input class="button-primary" type="submit" name="TB_NewThumbnail" value="<?php _e('OK', 'nggallery');?>" />
		    		&nbsp;
		    		<input class="button-secondary dialog-cancel" type="reset" value="&nbsp;<?php _e('Cancel', 'nggallery'); ?>&nbsp;" />
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#new_thumbnail -->

	<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function(){columns.init('nggallery-manage-images');});
	/* ]]> */
	</script>
	<?php
}

/**
 * Construtor class to create the table layout
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 1.8.0
 * @access private
 */
class _NGG_Images_List_Table extends WP_List_Table {
	var $_screen;
	var $_columns;

	function _NGG_Images_List_Table( $screen ) {
		if ( is_string( $screen ) )
			$screen = convert_to_screen( $screen );

		$this->_screen = $screen;
		$this->_columns = array() ;

		add_filter( 'manage_' . $screen->id . '_columns', array( &$this, 'get_columns' ), 0 );
	}

	function get_column_info() {

		$columns = get_column_headers( $this->_screen );
		$hidden = get_hidden_columns( $this->_screen );
		$_sortable = $this->get_sortable_columns();
        $sortable = array();

		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}

		return array( $columns, $hidden, $sortable );
	}

    // define the columns to display, the syntax is 'internal name' => 'display name'
	function get_columns() {
    	$columns = array();

    	$columns['cb'] = '<input name="checkall" type="checkbox" onclick="checkAll(document.getElementById(\'updategallery\'));" />';
    	$columns['id'] = __('ID');
    	$columns['thumbnail'] = __('Thumbnail', 'nggallery');
    	$columns['filename'] = __('Filename', 'nggallery');
    	$columns['alt_title_desc'] = __('Alt &amp; Title Text', 'nggallery') . ' / ' . __('Description', 'nggallery');
    	$columns['tags'] = __('Tags (comma separated list)', 'nggallery');
    	$columns['exclude'] = __('exclude', 'nggallery');

    	$columns = apply_filters('ngg_manage_images_columns', $columns);

    	return $columns;
	}

	function get_sortable_columns() {
		return array();
	}
}

?>