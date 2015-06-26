<?php
/**
 * This page manages the various actions that can be done when using the gallery overview.
 *
 * @access private
 */

require_once( '../../ngg-config.php');
require_once( NGGALLERY_ABSPATH . '/lib/image.php' );

if ( !is_user_logged_in() || !current_user_can('NextGEN Manage gallery')) {
	wp_die( __( 'Cheatin&#8217; uh?' ) );
}

$id = (int) $_GET['id'];

/**
 * Change the output based on which action the user wants to do.
 */
switch($_GET['cmd']) {
	case "rotate":
		ngg_rotate($id);
		break;
	case "edit_thumb":
		ngg_edit_thumbnail($id);
		break;
	case "show_meta":
		ngg_show_meta($id);
		break;
	default:
		//Do nothing.
		break;
}

/**
 * Display the page to rotate an image.
 *
 * @param $id int The ID of the image.
 */
function ngg_rotate($id) {
	//Include the graphics library
	include_once( nggGallery::graphic_library() );

	//Get the image data
	$picture = nggdb::find_image($id);
	$thumb = new ngg_Thumbnail($picture->imagePath, TRUE);
	$thumb->resize(350,350);

	// we need the new dimension
	$resizedPreviewInfo = $thumb->newDimensions;
	$thumb->destruct();

	$preview_image = trailingslashit( home_url() ) . 'index.php?callback=image&pid=' . $picture->pid . '&width=500&height=500';

	?>

	<script type="text/javascript">
		/**
		 * When pressed, send an AJAX request to rotate the image.
		 */
		doAction = function() {
			var rotate_angle = jQuery('input[name=ra]:checked').val();

			jQuery.ajax({
				url: ajaxurl,
				type : "POST",
				data:  {action: 'rotateImage', id: <?php echo $id ?>, ra: rotate_angle},
				cache: false,
				success: function (msg) { showMessage('<?php _e('Image rotated', 'nggallery'); ?>') },
				error: function (msg, status, errorThrown) { showMessage('<?php _e('Error rotating thumbnail', 'nggallery'); ?>') }
			});
		};
	</script>
	<p><?php _e('Select how you would like to rotate the image on the left.', 'nggallery'); ?></p>
	<table align="center" width="90%">
		<tr>
			<td style="text-align: center; vertical-align: middle">
				<img src="<?php echo esc_url( $preview_image ); ?>" alt="" id="imageToEdit" />
			</td>
			<td style="width: 200px;">
				<label><input type="radio" name="ra" value="cw"><?php esc_html_e('90&deg; clockwise', 'nggallery'); ?></label><br>
				<label><input type="radio" name="ra" value="ccw"><?php esc_html_e('90&deg; anticlockwise', 'nggallery'); ?></label><br>
				<label><input type="radio" name="ra" value="fv"><?php esc_html_e('Flip horizontally', 'nggallery'); ?></label><br>
				<label><input type="radio" name="ra" value="fh"><?php esc_html_e('Flip vertically', 'nggallery'); ?></label>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="thumbMsg" style="display : none; float:right; width:60%; height:2em; line-height:2em;"></div>
			</td>
		</tr>
	</table>
<?php
}

/**
 * Show meta data about an image.
 *
 * @param $id int The ID of the image.
 */
function ngg_show_meta($id) {
	include_once(NGGALLERY_ABSPATH . '/lib/meta.php');


	// let's get the meta data'
	$meta = new nggMeta($id);
	$dbdata = $meta->get_saved_meta();
	$exifdata = $meta->get_EXIF();
	$iptcdata = $meta->get_IPTC();
	$xmpdata = $meta->get_XMP();
	$class = '';

	?>
	<!-- META DATA -->
	<?php if ($dbdata) { ?>
		<table id="the-list-x" style="width: 100%">
			<thead style="text-align: left;">
			<tr>
				<th scope="col"><?php _e('Name','nggallery'); ?></th>
				<th scope="col"><?php _e('Value','nggallery'); ?></th>
			</tr>
			</thead>
			<?php
			foreach ($dbdata as $key => $value){
				if ( is_array($value) ) continue;
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr '.$class.'>
						<td style="width:230px">'. esc_html( $meta->i8n_name($key) ).'</td>
						<td>' . esc_html( $value ) . '</td>
					</tr>';
			}
			?>
		</table>
	<?php  } else echo "<strong>" . __('No meta data saved','nggallery') . "</strong>"; ?>

	<!-- EXIF DATA -->
	<?php if ($exifdata) { ?>
		<h3><?php _e('EXIF Data','nggallery'); ?></h3>
		<?php if ($exifdata) { ?>
			<table id="the-list-x" width="100%">
				<thead style="text-align: left;">
				<tr>
					<th scope="col"><?php _e('Name','nggallery'); ?></th>
					<th scope="col"><?php _e('Value','nggallery'); ?></th>
				</tr>
				</thead>
				<?php
				foreach ($exifdata as $key => $value){
					$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
					echo '<tr '.$class.'>
						<td style="width:230px">' . esc_html ( $meta->i8n_name($key) ) . '</td>
						<td>' . esc_html( $value ) .'</td>
					</tr>';
				}
				?>
			</table>
		<?php  } else echo "<strong>". __('No exif data','nggallery'). "</strong>"; ?>
	<?php  } ?>

	<!-- IPTC DATA -->
	<?php if ($iptcdata) { ?>
		<h3><?php _e('IPTC Data','nggallery'); ?></h3>
		<table id="the-list-x" width="100%">
			<thead style="text-align: left;">
			<tr>
				<th scope="col"><?php _e('Name','nggallery'); ?></th>
				<th scope="col"><?php _e('Value','nggallery'); ?></th>
			</tr>
			</thead>
			<?php
			foreach ($iptcdata as $key => $value){
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr '.$class.'>
						<td style="width:230px">' . esc_html( $meta->i8n_name($key) ) . '</td>
						<td>' . esc_html( $value ) . '</td>
					</tr>';
			}
			?>
		</table>
	<?php  } ?>

	<!-- XMP DATA -->
	<?php if ($xmpdata) { ?>
		<h3><?php _e('XMP Data','nggallery'); ?></h3>
		<table id="the-list-x" width="100%">
			<thead>
			<tr>
				<th scope="col"><?php _e('Name','nggallery'); ?></th>
				<th scope="col"><?php _e('Value','nggallery'); ?></th>
			</tr>
			</thead>
			<?php
			foreach ($xmpdata as $key => $value){
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr '.$class.'>
						<td style="width:230px">' . esc_html( $meta->i8n_name($key) ) . '</td>
						<td>' . esc_html( $value ) . '</td>
					</tr>';
			}
			?>
		</table>
	<?php
	}
}

/**
 * Show the interface to edit a thumbnail.
 *
 * @param $id int The ID of the image.
 *
 * @todo This needs to be remade with a new JavaScript plugin.
 */
function ngg_edit_thumbnail($id) {
// let's get the image data
$picture = nggdb::find_image($id);

include_once( nggGallery::graphic_library() );
$ngg_options=get_option('ngg_options');

$thumb = new ngg_Thumbnail($picture->imagePath, TRUE);
$thumb->resize(350,350);
// we need the new dimension
$resizedPreviewInfo = $thumb->newDimensions;
$thumb->destruct();

$preview_image		= NGGALLERY_URLPATH . 'nggshow.php?pid=' . $picture->pid . '&amp;width=350&amp;height=350';
$imageInfo			= @getimagesize($picture->imagePath);
$rr = round($imageInfo[0] / $resizedPreviewInfo['newWidth'], 2);

if ( ($ngg_options['thumbfix'] == 1) ) {

	$WidthHtmlPrev  = $ngg_options['thumbwidth'];
	$HeightHtmlPrev = $ngg_options['thumbheight'];

} else {
	// H > W
	if ($imageInfo[1] > $imageInfo[0]) {

		$HeightHtmlPrev =  $ngg_options['thumbheight'];
		$WidthHtmlPrev  = round($imageInfo[0] / ($imageInfo[1] / $ngg_options['thumbheight']),0);

	} else {

		$WidthHtmlPrev  =  $ngg_options['thumbwidth'];
		$HeightHtmlPrev = round($imageInfo[1] / ($imageInfo[0] / $ngg_options['thumbwidth']),0);

	}
}

?>
<script src="<?php echo NGGALLERY_URLPATH; ?>/admin/js/Jcrop/js/jquery.Jcrop.js"></script>
<link rel="stylesheet" href="<?php echo NGGALLERY_URLPATH; ?>/admin/js/Jcrop/css/jquery.Jcrop.css" type="text/css" />

<script type="text/javascript">
	//<![CDATA[
	var status = 'start';
	var xT, yT, wT, hT, selectedCoords;
	var selectedImage = "thumb<?php echo $id ?>";

	function showPreview(coords) {

		if (status != 'edit') {
			jQuery('#actualThumb').hide();
			jQuery('#previewNewThumb').show();
			status = 'edit';
		}

		var rx = <?php echo $WidthHtmlPrev; ?> / coords.w;
		var ry = <?php echo $HeightHtmlPrev; ?> / coords.h;

		jQuery('#imageToEditPreview').css({
			width: Math.round(rx * <?php echo $resizedPreviewInfo['newWidth']; ?>) + 'px',
			height: Math.round(ry * <?php echo $resizedPreviewInfo['newHeight']; ?>) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' + Math.round(ry * coords.y) + 'px'
		});

		xT = coords.x;
		yT = coords.y;
		wT = coords.w;
		hT = coords.h;

		jQuery("#sizeThumb").html(xT+" "+yT+" "+wT+" "+hT);

	}

	doAction = function() {

		if ( (wT == 0) || (hT == 0) || (wT == undefined) || (hT == undefined) ) {
			alert("<?php _e('Select with the mouse the area for the new thumbnail', 'nggallery'); ?>");
			return false;
		}

		jQuery.ajax({
			url: ajaxurl,
			type : "POST",
			data:  {x: xT, y: yT, w: wT, h: hT, action: 'createNewThumb', id: <?php echo $id; ?>, rr: <?php echo str_replace(',','.',$rr); ?>},
			cache: false,
			success: function () { showMessage('<?php _e('Thumbnail updated', 'nggallery'); ?>') },
			error: function () { showMessage('<?php _e('Error updating thumbnail', 'nggallery'); ?>') }
		});

	};
	//]]>
</script>
<p><?php _e('Select the area for the thumbnail from the picture on the left.', 'nggallery'); ?></p>
<table width="98%" align="center">
	<tr>
		<td valign="middle" align="center" width="350">
			<img src="<?php echo esc_url( $preview_image ); ?>" alt="" id="imageToEdit" />
		</td>
		<td align="center" width="300" height="319">
			<div id="previewNewThumb" style="display:none;width:<?php echo $WidthHtmlPrev; ?>px;height:<?php echo $HeightHtmlPrev; ?>px;overflow:hidden; margin-left:5px;">
				<img src="<?php echo esc_url( $preview_image ); ?>" id="imageToEditPreview" />
			</div>
			<div id="actualThumb">
				<img src="<?php echo esc_url( $picture->thumbURL ); ?>?<?php echo time()?>" />
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="thumbMsg" style="display : none; float:right; width:60%; height:2em; line-height:2em;"></div>
		</td>
	</tr>
</table>

<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function(){
		jQuery('#imageToEdit').Jcrop({
			onChange: showPreview,
			onSelect: showPreview,
			aspectRatio: <?php echo str_replace(',', '.', round($WidthHtmlPrev/$HeightHtmlPrev, 3)); ?>
		});
	});
	//]]>
</script>
<?php
}