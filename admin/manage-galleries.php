<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 	die('You are not allowed to call this page directly.'); }

// *** show main gallery list
function nggallery_manage_gallery_main() {

	global $ngg, $nggdb, $wp_query;

	//Build the pagination for more than 25 galleries
    $_GET['paged'] = isset($_GET['paged']) && ($_GET['paged'] > 0) ? absint($_GET['paged']) : 1;

    $items_per_page = 25;

	$start = ( $_GET['paged'] - 1 ) * $items_per_page;

    $order = ( isset ( $_GET['order'] ) && $_GET['order'] == 'desc' ) ? 'DESC' : 'ASC';
    $orderby = ( isset ( $_GET['orderby'] ) && ( in_array( $_GET['orderby'], array('gid', 'title', 'author') )) ) ? $_GET['orderby'] : 'gid';

	$gallerylist = $nggdb->find_all_galleries( $orderby, $order , TRUE, $items_per_page, $start, false);
	$wp_list_table = new _NGG_Galleries_List_Table('nggallery-manage-gallery');

	?>
	<script type="text/javascript">
	<!--
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

        if (typeof document.activeElement == "undefined" && document.addEventListener) {
        	document.addEventListener("focus", function (e) {
        		document.activeElement = e.target;
        	}, true);
        }

        if ( document.activeElement.name == 'post_paged' )
            return true;

		var numchecked = getNumChecked(document.getElementById('editgalleries'));

		if(numchecked < 1) {
			alert('<?php echo esc_js(__('No images selected', 'nggallery')); ?>');
			return false;
		}

		actionId = jQuery('#bulkaction').val();

		switch (actionId) {
			case "resize_images":
                showDialog('resize_images', '<?php echo esc_js(__('Resize images','nggallery')); ?>');
				return false;
				break;
			case "new_thumbnail":
				showDialog('new_thumbnail', '<?php echo esc_js(__('Create new thumbnails','nggallery')); ?>');
				return false;
				break;
		}

		return confirm('<?php echo sprintf(esc_js(__("You are about to start the bulk edit for %s galleries \n \n 'Cancel' to stop, 'OK' to proceed.",'nggallery')), "' + numchecked + '") ; ?>');
	}

	function showDialog( windowId, title ) {
		var form = document.getElementById('editgalleries');
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

	function showAddGallery() {
    	jQuery( "#addGallery").dialog({
    		width: '70%',
            resizable : false,
    		modal: true,
            title: '<?php echo esc_js(__('Add new gallery','nggallery')); ?>'
    	});
        jQuery("#addGallery .dialog-cancel").click(function() { jQuery( "#addGallery" ).dialog("close"); });
	}
	//-->
	</script>
	<div class="wrap">
		<?php screen_icon( 'nextgen-gallery' ); ?>
		<h2><?php echo _e( 'Galleries', 'nggallery');?> 
			<form id="addgalleries" class="nggform add-new-form" method="POST" action="<?php echo $ngg->manage_page->base_page . '&amp;paged=' . $_GET['paged']; ?>" accept-charset="utf-8"><?php if ( current_user_can('NextGEN Upload images') && nggGallery::current_user_can( 'NextGEN Add new gallery' ) ) : ?>
					<input name="doaction" class="add-new-h2" type="submit" onclick="showAddGallery(); return false;" value="<?php _e('Add new gallery', 'nggallery') ?>"/>
			<?php endif; ?></form></h2>
		<form class="search-form" action="" method="get">
		<p class="search-box">
			<label class="hidden" for="media-search-input"><?php _e( 'Search Images', 'nggallery' ); ?>:</label>
			<input type="hidden" id="page-name" name="page" value="nggallery-manage-gallery" />
			<input type="text" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
			<input type="submit" value="<?php _e( 'Search Images', 'nggallery' ); ?>" class="button" />
		</p>
		</form>
		<form id="editgalleries" class="nggform" method="POST" action="<?php echo $ngg->manage_page->base_page . '&amp;paged=' . $_GET['paged']; ?>" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_bulkgallery') ?>
		<input type="hidden" name="page" value="manage-galleries" />

		<div class="tablenav top">

			<div class="alignleft actions">
				<?php if ( function_exists('json_encode') ) : ?>
				<select name="bulkaction" id="bulkaction">
					<option value="no_action" ><?php _e("Actions",'nggallery'); ?></option>
					<option value="delete_gallery" ><?php _e("Delete",'nggallery'); ?></option>
                    <option value="set_watermark" ><?php _e("Set watermark",'nggallery'); ?></option>
					<option value="new_thumbnail" ><?php _e("Create new thumbnails",'nggallery'); ?></option>
					<option value="resize_images" ><?php _e("Resize images",'nggallery'); ?></option>
					<option value="import_meta" ><?php _e("Import metadata",'nggallery'); ?></option>
					<option value="recover_images" ><?php _e("Recover from backup",'nggallery'); ?></option>
				</select>
				<input name="showThickbox" class="button-secondary" type="submit" value="<?php _e('Apply','nggallery'); ?>" onclick="if ( !checkSelected() ) return false;" />
				<?php endif; ?>
			</div>


        <?php $ngg->manage_page->pagination( 'top', $_GET['paged'], $nggdb->paged['total_objects'], $nggdb->paged['objects_per_page']  ); ?>

		</div>
		<table class="wp-list-table widefat fixed" cellspacing="0">
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

if($gallerylist) {
    //get the columns
	$gallery_columns = $wp_list_table->get_columns();
	$hidden_columns  = get_hidden_columns('nggallery-manage-gallery');
	$num_columns     = count($gallery_columns) - count($hidden_columns);

	foreach($gallerylist as $gallery) {
		$alternate = ( !isset($alternate) || $alternate == 'class="alternate"' ) ? '' : 'class="alternate"';
		$gid = $gallery->gid;
		$name = (empty($gallery->title) ) ? $gallery->name : $gallery->title;
		$author_user = get_userdata( (int) $gallery->author );
		?>
		<tr id="gallery-<?php echo $gid ?>" <?php echo $alternate; ?> >
		<?php
		foreach($gallery_columns as $gallery_column_key => $column_display_name) {
			$class = "class=\"$gallery_column_key column-$gallery_column_key\"";

			$style = '';
			if ( in_array($gallery_column_key, $hidden_columns) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ($gallery_column_key) {
				case 'cb' :
					?>
        			<th scope="row" class="column-cb check-column">
        				<?php if (nggAdmin::can_manage_this_gallery($gallery->author)) { ?>
        					<input name="doaction[]" type="checkbox" value="<?php echo $gid ?>" />
        				<?php } ?>
        			</th>
        			<?php
    			break;
    			case 'id' :
    			    ?>
					<td <?php echo $attributes ?>><?php echo $gid; ?></td>
					<?php
    			break;
    			case 'title' :
    			    ?>
        			<td class="title column-title">
        				<?php if (nggAdmin::can_manage_this_gallery($gallery->author)) { ?>
        					<a href="<?php echo wp_nonce_url( $ngg->manage_page->base_page . '&amp;mode=edit&amp;gid=' . $gid, 'ngg_editgallery')?>" class='edit' title="<?php _e('Edit'); ?>" >
        						<?php echo esc_html( nggGallery::i18n($name) ); ?>
        					</a>
        				<?php } else { ?>
        					<?php echo esc_html( nggGallery::i18n($gallery->title) ); ?>
        				<?php } ?>
                        <div class="row-actions"></div>
        			</td>
        			<?php
    			break;
    			case 'description' :
    			    ?>
					<td <?php echo $attributes ?>><?php echo esc_html( nggGallery::i18n($gallery->galdesc) ); ?>&nbsp;</td>
					<?php
    			break;
    			case 'author' :
    			    ?>
					<td <?php echo $attributes ?>><?php echo esc_html( $author_user->display_name ); ?></td>
					<?php
    			break;
    			case 'page_id' :
    			    ?>
        			<td <?php echo $attributes ?>><?php echo $gallery->pageid; ?></td>
        			<?php
    			break;
    			case 'quantity' :
    			    ?>
        			<td <?php echo $attributes ?>><?php echo $gallery->counter; ?></td>
        			<?php
    			break;
    			default :
					?>
					<td <?php echo $attributes ?>><?php do_action('ngg_manage_gallery_custom_column', $gallery_column_key, $gid); ?></td>
					<?php
				break;
				}
	        } ?>
		</tr>
		<?php
	}
} else {
	echo '<tr><td colspan="7" align="center"><strong>' . __('No entries found', 'nggallery') . '</strong></td></tr>';
}
?>
			</tbody>
		</table>
        <div class="tablenav bottom">
		<?php $ngg->manage_page->pagination( 'bottom', $_GET['paged'], $nggdb->paged['total_objects'], $nggdb->paged['objects_per_page']  ); ?>
        </div>
		</form>
	</div>
	<!-- #addGallery -->
	<div id="addGallery" style="display: none; background: white;" >
		<form id="form-tags" method="POST" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_addgallery'); ?>
		<input type="hidden" name="page" value="manage-galleries" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
		  	<tr>
		    	<td>
					<strong><?php _e('New Gallery', 'nggallery') ;?>:</strong> <input type="text" size="35" name="galleryname" value="" /><br />
					<?php if(!is_multisite()) { ?>
					<?php _e('Create a new , empty gallery below the folder', 'nggallery') ;?>  <strong><?php echo $ngg->options['gallerypath']; ?></strong><br />
					<?php } ?>
					<i>(<?php _e('Allowed characters for file and folder names are', 'nggallery') ;?>: a-z, A-Z, 0-9, -, _)</i>
				</td>
		  	</tr>
            <?php do_action('ngg_add_new_gallery_form'); ?>
		  	<tr align="right">
		    	<td class="submit">
		    		<input class="button-primary" type="submit" name="addgallery" value="<?php _e('OK','nggallery'); ?>" />
		    		&nbsp;
		    		<input class="button-secondary dialog-cancel" type="reset" value="&nbsp;<?php _e('Cancel', 'nggallery'); ?>&nbsp;" />
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#addGallery -->

	<!-- #resize_images -->
	<div id="resize_images" style="display: none;" >
		<form id="form-resize-images" method="POST" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_thickbox_form') ?>
		<input type="hidden" id="resize_images_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="resize_images_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="page" value="manage-galleries" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
			<tr valign="top">
				<td>
					<strong><?php _e('Resize Images to', 'nggallery'); ?>:</strong>
				</td>
				<td>
					<label for="imgWidth"><?php _e('Width','nggallery') ?></label>
					<input type="number" step="1" min="0" class="small-text" name="imgWidth" class="small-text" value="<?php echo $ngg->options['imgWidth']; ?>" />
					<label for="imgHeight"><?php _e('Height','nggallery') ?></label>
					<input type="number" step="1" min="0" type="text" size="5" name="imgHeight" class="small-text" value="<?php echo $ngg->options['imgHeight']; ?>">
					<p class="description"><?php _e('Width and height (in pixels). NextCellent Gallery will keep the ratio size.','nggallery') ?></p>
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
		<input type="hidden" name="page" value="manage-galleries" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
			<tr valign="top">
				<th align="left"><?php _e('Size','nggallery') ?></th>
				<td><label for="thumbwidth"><?php _e('Width','nggallery') ?> </label><input class="small-text" type="number" step="1" min="0" name="thumbwidth" value="<?php echo $ngg->options['thumbwidth']; ?>" /> <label for="thumbheight"><?php _e('Height','nggallery') ?> </label><input class="small-text" type="number" step="1" min="0" name="thumbheight" value="<?php echo $ngg->options['thumbheight']; ?>" />
				<p class="description"><?php _e('These values are maximum values ','nggallery') ?></p></td>
			</tr>
			<tr valign="top">
					<th align="left"><?php _e('Fixed size','nggallery'); ?></th>
					<td><input type="checkbox" name="thumbfix" value="1" <?php checked('1', $ngg->options['thumbfix']); ?> />
					<?php _e('This will ignore the aspect ratio, so no portrait thumbnails','nggallery') ?></td>
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
class _NGG_Galleries_List_Table extends WP_List_Table {
	var $_screen;
	var $_columns;

	function _NGG_Galleries_List_Table( $screen ) {
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

    	$columns['cb'] = '<input name="checkall" type="checkbox" onclick="checkAll(document.getElementById(\'editgalleries\'));" />';
    	$columns['id'] = __('ID');
    	$columns['title'] = __( 'Title', 'nggallery');
    	$columns['description'] = __('Description', 'nggallery');
    	$columns['author'] = __('Author', 'nggallery');
    	$columns['page_id'] = __('Page ID', 'nggallery');
		$columns['quantity'] = __( 'Images', 'nggallery' );

    	$columns = apply_filters('ngg_manage_gallery_columns', $columns);

    	return $columns;
	}

	function get_sortable_columns() {
		return array(
			'id'    => array( 'gid', true ),
			'title'   => 'title',
			'author'   => 'author'
		);
	}
}
?>