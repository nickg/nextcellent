<?php

/**
 * @author Alex Rabe
 * 
 */

function nggallery_sortorder($galleryID = 0){
	global $wpdb, $ngg, $nggdb;
	
	if ($galleryID == 0) return;

	$galleryID = (int) $galleryID;
	
	if (isset ($_POST['updateSortorder']))  {
		check_admin_referer('ngg_updatesortorder');
		// get variable new sortorder 
		parse_str($_POST['sortorder']);
		if (is_array($sortArray)){ 
			$neworder = array();
			foreach($sortArray as $pid) {		
				$pid = substr($pid, 4); // get id from "pid-x"
				$neworder[] = (int) $pid;
			}
			$sortindex = 1;
			foreach($neworder as $pic_id) {
				$wpdb->query("UPDATE $wpdb->nggpictures SET sortorder = '$sortindex' WHERE pid = $pic_id");
				$sortindex++;
			}

			do_action('ngg_gallery_sort', $galleryID);

			nggGallery::show_message(__('Sort order changed','nggallery'));
		} 
	}
	
	// look for presort args	
	$presort = isset($_GET['presort']) ? $_GET['presort'] : false;
	$dir = ( isset($_GET['dir']) && $_GET['dir'] == 'DESC' ) ? 'DESC' : 'ASC';
	$sortitems = array('pid', 'filename', 'alttext', 'imagedate');
	// ensure that nobody added some evil sorting :-)
	if (in_array( $presort, $sortitems) )
		$picturelist = $nggdb->get_gallery($galleryID, $presort, $dir, false);
	else	
		$picturelist = $nggdb->get_gallery($galleryID, 'sortorder', $dir, false);
		
	//this is the url without any presort variable
	$clean_url = 'admin.php?page=nggallery-manage-gallery&amp;mode=sort&amp;gid=' . $galleryID;
	//if we go back , then the mode should be edit
	$back_url  = 'admin.php?page=nggallery-manage-gallery&amp;mode=edit&amp;gid=' . $galleryID;
	
	// In the case somebody presort, then we take this url
	if ( isset($_GET['dir']) || isset($_GET['presort']) )
		$base_url = $_SERVER['REQUEST_URI'];
	else		
		$base_url = $clean_url;
	
?>
	<script type="text/javascript">
		// seralize the ImageOrder
		function saveImageOrder()
		{
			var serial = "";
			var objects = document.getElementsByTagName('div');
			for(var no=0;no<objects.length;no++){
				if(objects[no].className=='imageBox' || objects[no].className=='imageBoxHighlighted'){
					if (serial.length > 0)	serial = serial + '&'
					serial = serial + "sortArray[]=" + objects[no].id;
				}			
			}
			jQuery('input[name=sortorder]').val(serial);
			// debug( 'This is the new order of the images(IDs) : <br>' + orderString );
			
		}
		jQuery(document).ready(function($) {
			$(".jqui-sortable").sortable({items: 'div.imageBox'});
		});
	</script>	
	<div class="wrap">
		<form id="sortGallery" method="POST" action="<?php echo $clean_url ?>" onsubmit="saveImageOrder()" accept-charset="utf-8">
			<h2><?php _e('Sort Gallery', 'nggallery') ?></h2>
			<div class="tablenav">
				<div class="alignleft actions">
					<?php wp_nonce_field('ngg_updatesortorder') ?>
					<input class="button-primary action" type="submit" name="updateSortorder" onclick="saveImageOrder()" value="<?php _e('Update Sort Order', 'nggallery') ?>" />
				</div>
				<div class="alignright actions">
					<a href="<?php echo esc_url( $back_url ); ?>" class="button"><?php _e('Back to gallery', 'nggallery'); ?></a>
				</div>
			</div>	
			<input name="sortorder" type="hidden" />
			<ul class="subsubsub">
				<li><?php _e('Presort', 'nggallery') ?> :</li>
				<li><a href="<?php echo esc_attr(remove_query_arg('presort', $base_url)); ?>" <?php if ($presort == '') echo 'class="current"'; ?>><?php _e('Unsorted', 'nggallery') ?></a> |</li>
				<li><a href="<?php echo esc_attr(add_query_arg('presort', 'pid', $base_url)); ?>" <?php if ($presort == 'pid') echo 'class="current"'; ?>><?php _e('Image ID', 'nggallery') ?></a> |</li>
				<li><a href="<?php echo esc_attr(add_query_arg('presort', 'filename', $base_url)); ?>" <?php if ($presort == 'filename') echo 'class="current"'; ?>><?php _e('Filename', 'nggallery') ?></a> |</li>
				<li><a href="<?php echo esc_attr(add_query_arg('presort', 'alttext', $base_url)); ?>" <?php if ($presort == 'alttext') echo 'class="current"'; ?>><?php _e('Alt/Title text', 'nggallery') ?></a> |</li>
				<li><a href="<?php echo esc_attr(add_query_arg('presort', 'imagedate', $base_url)); ?>" <?php if ($presort == 'imagedate') echo 'class="current"'; ?>><?php _e('Date/Time', 'nggallery') ?></a> |</li>
				<li><a href="<?php echo esc_attr(add_query_arg('dir', 'ASC', $base_url)); ?>" <?php if ($dir == 'ASC') echo 'class="current"'; ?>><?php _e('Ascending', 'nggallery') ?></a> |</li>
				<li><a href="<?php echo esc_attr(add_query_arg('dir', 'DESC', $base_url)); ?>" <?php if ($dir == 'DESC') echo 'class="current"'; ?>><?php _e('Descending', 'nggallery') ?></a></li>
			</ul>
		</form>
		<div id="debug" style="clear:both"></div>
		<div class='jqui-sortable'>
			<?php 
			if($picturelist) {
				foreach($picturelist as $picture) {
					?>
					<div class="imageBox" id="pid-<?php echo $picture->pid ?>">
						<div class="imageBox_theImage" style="background-image:url('<?php echo esc_url( $picture->thumbURL ); ?>')"></div>	
						<div class="imageBox_label"><span><?php echo esc_html( stripslashes($picture->alttext) ); ?></span></div>
					</div>
					<?php
				}
			}
			?>
		</div>
	</div>
	
<?php
}
?>
