<?php
/**
 * This page manages the various actions that can be done when using the gallery overview.
 *
 * @access private
 */

require_once( NGGALLERY_ABSPATH . '/lib/image.php' );

if ( ! is_user_logged_in() || ! current_user_can( 'NextGEN Manage gallery' ) ) {
	wp_die( __( 'Cheatin&#8217; uh?' ) );
}

$id = (int) $_POST['id'];


/**
 * Change the output based on which action the user wants to do.
 * If you need scripts, you should register them with the parent page.
 */
switch ( $_POST['cmd'] ) {
	case "rotate":
		ngg_rotate( $id );
		break;
	case "edit_thumb":
		ngg_edit_thumbnail( $id );
		break;
	case "show_meta":
		ngg_show_meta( $id );
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
function ngg_rotate( $id ) {
	//Include the graphics library
	include_once( nggGallery::graphic_library() );

	//Get the image data
	$picture = nggdb::find_image( $id );
	$thumb   = new ngg_Thumbnail( $picture->imagePath, true );
	$thumb->resize( 350, 350 );

	// we need the new dimension
	$resizedPreviewInfo = $thumb->newDimensions;
	$thumb->destruct();

	$preview_image = trailingslashit( home_url() ) . 'index.php?callback=image&pid=' . $picture->pid . '&width=500&height=500';

	?>
	<p><?php _e( 'Select how you would like to rotate the image on the left.', 'nggallery' ); ?></p>
	<table align="center" width="90%">
		<tr>
			<td style="text-align: center; vertical-align: middle">
				<img src="<?php echo esc_url( $preview_image ); ?>" alt="" id="imageToEdit"/>
			</td>
			<td style="width: 200px;">
				<label><input type="radio" name="ra" value="cw"><?php esc_html_e( '90&deg; clockwise', 'nggallery' ); ?>
				</label><br>
				<label><input type="radio" name="ra" value="ccw"><?php esc_html_e( '90&deg; anticlockwise',
						'nggallery' ); ?></label><br>
				<label><input type="radio" name="ra" value="fv"><?php esc_html_e( 'Flip horizontally', 'nggallery' ); ?>
				</label><br>
				<label><input type="radio" name="ra" value="fh"><?php esc_html_e( 'Flip vertically', 'nggallery' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="thumbMsg" style="display : none; float:right; width:60%; height:2em; line-height:2em;"></div>
			</td>
		</tr>
	</table>
	<script type="text/javascript">
		/**
		 * When pressed, send an AJAX request to rotate the image.
		 */
		doAction = function() {
			var rotate_angle = jQuery('input[name=ra]:checked').val();

			jQuery.ajax({
				url: ajaxurl,
				type: "POST",
				data: {action: 'rotateImage', id: <?php echo $id ?>, ra: rotate_angle},
				cache: false,
				success: function() {
					showMessage('<?php _e('Image rotated', 'nggallery'); ?>')
				},
				error: function() {
					showMessage('<?php _e('Error rotating thumbnail', 'nggallery'); ?>')
				}
			});
		};
	</script>
	<?php
}

/**
 * Show meta data about an image.
 *
 * @param $id int The ID of the image.
 */
function ngg_show_meta( $id ) {
	include_once( NGGALLERY_ABSPATH . '/lib/meta.php' );


	// let's get the meta data'
	$meta     = new nggMeta( $id );
	$dbdata   = $meta->get_saved_meta();
	$exifdata = $meta->get_EXIF();
	$iptcdata = $meta->get_IPTC();
	$xmpdata  = $meta->get_XMP();
	$class    = '';

	?>
	<!-- META DATA -->
	<?php if ( $dbdata ) { ?>
		<table id="the-list-x" style="width: 100%">
			<thead style="text-align: left;">
			<tr>
				<th scope="col"><?php _e( 'Name', 'nggallery' ); ?></th>
				<th scope="col"><?php _e( 'Value', 'nggallery' ); ?></th>
			</tr>
			</thead>
			<?php
			foreach ( $dbdata as $key => $value ) {
				if ( is_array( $value ) ) {
					continue;
				}
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr ' . $class . '>
						<td style="width:230px">' . esc_html( $meta->i8n_name( $key ) ) . '</td>
						<td>' . esc_html( $value ) . '</td>
					</tr>';
			}
			?>
		</table>
	<?php } else {
		echo "<strong>" . __( 'No meta data saved', 'nggallery' ) . "</strong>";
	} ?>

	<!-- EXIF DATA -->
	<?php if ( $exifdata ) { ?>
		<h3><?php _e( 'EXIF Data', 'nggallery' ); ?></h3>
		<?php if ( $exifdata ) { ?>
			<table id="the-list-x" width="100%">
				<thead style="text-align: left;">
				<tr>
					<th scope="col"><?php _e( 'Name', 'nggallery' ); ?></th>
					<th scope="col"><?php _e( 'Value', 'nggallery' ); ?></th>
				</tr>
				</thead>
				<?php
				foreach ( $exifdata as $key => $value ) {
					$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
					echo '<tr ' . $class . '>
						<td style="width:230px">' . esc_html( $meta->i8n_name( $key ) ) . '</td>
						<td>' . esc_html( $value ) . '</td>
					</tr>';
				}
				?>
			</table>
		<?php } else {
			echo "<strong>" . __( 'No exif data', 'nggallery' ) . "</strong>";
		} ?>
	<?php } ?>

	<!-- IPTC DATA -->
	<?php if ( $iptcdata ) { ?>
		<h3><?php _e( 'IPTC Data', 'nggallery' ); ?></h3>
		<table id="the-list-x" width="100%">
			<thead style="text-align: left;">
			<tr>
				<th scope="col"><?php _e( 'Name', 'nggallery' ); ?></th>
				<th scope="col"><?php _e( 'Value', 'nggallery' ); ?></th>
			</tr>
			</thead>
			<?php
			foreach ( $iptcdata as $key => $value ) {
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr ' . $class . '>
						<td style="width:230px">' . esc_html( $meta->i8n_name( $key ) ) . '</td>
						<td>' . esc_html( $value ) . '</td>
					</tr>';
			}
			?>
		</table>
	<?php } ?>

	<!-- XMP DATA -->
	<?php if ( $xmpdata ) { ?>
		<h3><?php _e( 'XMP Data', 'nggallery' ); ?></h3>
		<table id="the-list-x" width="100%">
			<thead>
			<tr>
				<th scope="col"><?php _e( 'Name', 'nggallery' ); ?></th>
				<th scope="col"><?php _e( 'Value', 'nggallery' ); ?></th>
			</tr>
			</thead>
			<?php
			foreach ( $xmpdata as $key => $value ) {
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr ' . $class . '>
						<td style="width:230px">' . esc_html( $meta->i8n_name( $key ) ) . '</td>
						<td>' . esc_html( $value ) . '</td>
					</tr>';
			}
			?>
		</table>
		<?php
	} ?>
	<script type="text/javascript">
		doAction = defaultAction;
	</script>
	<?php
}

/**
 * Show the interface to edit a thumbnail.
 *
 * @param $id int The ID of the image.
 *
 * @since 1.9.27 Totally remade with Fengyuan Chen's Cropper plugin.
 * @see   https://github.com/fengyuanchen/cropper
 */
function ngg_edit_thumbnail( $id ) {

	/**
	 * @var $picture nggImage
	 */
	$picture = nggdb::find_image( $id );

	$width  = $picture->meta_data['width'];
	$height = $picture->meta_data['height'];

	$ngg_options = get_option('ngg_options');

	$differentSizes = false;
	if(isset($ngg_options['thumbDifferentSize'])) {
		$differentSizes = (bool) $ngg_options['thumbDifferentSize'];
	}

	?>
	<table style="width: 100%">
		<tr>
			<td style="text-align: center; vertical-align: middle; width: 60%">
				<div style="padding: 10px">
					<button class="crop-action button button-small" data-method="zoom" data-option="0.1" type="button" title="<?php _e( 'Zoom In',
						'nggallery' ); ?>">
						<span class="dashicons dashicons-plus"></span>
					</button>
					<button class="crop-action button button-small" data-method="zoom" data-option="-0.1" type="button" title="<?php _e( 'Zoom Out',
						'nggallery' ); ?>">
						<span class="dashicons dashicons-minus"></span>
					</button>
					<button class="crop-action button button-small" data-method="rotate" data-option="-90" type="button" title="<?php _e( 'Rotate Left',
						'nggallery' ); ?>">
						<span class="dashicons dashicons-image-rotate-left"></span>
					</button>
					<button class="crop-action button button-small" data-method="rotate" data-option="90" type="button" title="<?php _e( 'Rotate Right',
						'nggallery' ); ?>">
						<span class="dashicons dashicons-image-rotate-right"></span>
					</button>
					<button class="crop-action button button-small" data-method="reset" type="button" title="<?php _e( 'Reset',
						'nggallery' ); ?>">
						<span class="dashicons dashicons-update"></span>
					</button>
					<button id="center-selection" class="button button-small" data-method="reset" type="button" title="<?php _e( 'Center selection',
						'nggallery' ); ?>">
						<span class="dashicons dashicons-align-center"></span>
					</button>
				</div>
				<img src="<?php echo esc_url( $picture->imageURL ); ?>" alt="" id="imageToEdit" style="max-width: 60%; max-height: 60%; width: auto; height: auto;"/>
			</td>
			<td>
				<div class="thumb-preview" style="max-width: 100%; width: 300px; height: 150px; overflow: hidden; margin-bottom: 10px; margin-left: auto; margin-right: auto; border: 1px solid black">

				</div>
				<!--<div id="actualThumb">
					<img src="<?php echo esc_url( $picture->thumbURL ); ?>?<?php echo time() ?>" />
				</div>-->
				<table style="padding: 20px; width: 100%">
					<tr>
						<th colspan="2">
							<?php _e( 'The parameters', 'nggallery' ); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php /* translators: x position on a grid */ ?>
							<label for="dataX"><?php _e( 'X', 'nggallery' ) ?></label>
						</td>
						<td style="text-align: right">
							<?php /* translators: a measurement unit, stand for pixels */ ?>
							<input id="dataX" type="number" placeholder="0"> <?php _e( 'px', 'nggallery' ) ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php /* translators: y position on a grid */ ?>
							<label for="dataY"><?php _e( 'Y', 'nggallery' ) ?></label>
						</td>
						<td style="text-align: right">
							<input id="dataY" type="number" placeholder="0"> <?php _e( 'px', 'nggallery' ) ?>
						</td>
					</tr>
					<?php if($differentSizes): ?>
						<tr>
							<td>
								<label for="dataWidth"><?php _e( 'Width', 'nggallery' ) ?></label>
							</td>
							<td style="text-align: right">
								<input id="dataWidth" type="number" placeholder="<?php echo $width ?>"> <?php _e( 'px', 'nggallery' ) ?>
							</td>
						</tr>
						<tr>
							<td>
								<label for="dataHeight"><?php _e( 'Height', 'nggallery' ) ?></label>
							</td>
							<td style="text-align: right">
								<input id="dataHeight" type="number" placeholder="<?php echo $height ?>"> <?php _e( 'px', 'nggallery' ) ?>
							</td>
						</tr>
						<tr>
							<td>
								<label for="dataRotate"><?php _e( 'Rotation', 'nggallery' ) ?></label>
							</td>
							<td style="text-align: right">
								<?php /* translators: stands for degrees, as in a rotation. Should be pretty short. */ ?>
								<input id="dataRotate" type="number" placeholder="0"> <?php _e( 'deg', 'nggallery' ) ?>
							</td>
						</tr>
					<?php endif; ?>
					<tr>
						<td colspan="2" style="text-align: right">
							<button class="button button-secondary" type="button" id="apply-data" title="<?php _e( 'Apply the parameters', 'nggallery' ); ?>">
								<?php _e( 'Apply', 'nggallery' ); ?>
							</button>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="thumbMsg" style="display : none; float:right; width:60%; height:2em; line-height:2em;"></div>
			</td>
		</tr>
	</table>
	<script type="text/javascript">
		jQuery(document).ready(function() {

			//Some common elements we need multiple times.
			var $image = jQuery('#imageToEdit');
			var $dataX = jQuery("#dataX");
			var $dataY = jQuery("#dataY");
			var $dataHeight = jQuery("#dataHeight");
			var $dataWidth = jQuery("#dataWidth");
			var $dataRotate = jQuery("#dataRotate");

			/**
			 * Try and submit the new thumbnail.
			 */
			doAction = function() {
				jQuery.ajax({
					url: ajaxurl,
					type: "POST",
					data: {action: 'new_thumbnail', id: <?php echo $id ?>, newData: $image.cropper('getData', true)},
					cache: false,
					success: function(lol, hhh, xhr) {
						console.log(lol);
						jQuery(".wrap").append(lol);
						showMessage('<?php _e('Thumbnail updated', 'nggallery'); ?>');
					},
					error: function(xhr) {
						console.log(xhr.responseText);
						showMessage('<?php _e('Error updating thumbnail', 'nggallery'); ?>');
					}
				});
			};

			/**
			 * Properly destroy the cropper before destroying the dialog, or this gives errors.
			 */
			jQuery(".ngg-load-dialog").on("dialogbeforeclose", function() {
				$image.cropper('destroy');
			});

			/**
			 * Set the action buttons.
			 */
			jQuery(".crop-action").click(function() {
				var $element = jQuery(this);
				$image.cropper($element.data('method'), $element.data('option'));
			});

			/**
			 * Allow manual apply of the data.
			 */
			jQuery("#apply-data").click(function() {
				$image.cropper('setData', {
					"x": parseInt($dataX.val()),
					"y": parseInt($dataY.val()),
					"width": parseInt($dataWidth.val()),
					"height": parseInt($dataHeight.val()),
					"rotate": parseInt($dataRotate.val())
				});
			});

			/**
			 * Center the selection.
			 */
			jQuery("#center-selection").click(function() {

				<?php if($differentSizes): ?>
				var width = parseInt($dataWidth.val());
				var height = parseInt($dataHeight.val());
				<?php else: ?>
				var width = <?php echo esc_js( $ngg_options['thumbwidth'] ) ?>;
				var height = <?php echo esc_js( $ngg_options['thumbheight'] ) ?>;
				<?php endif; ?>
				var img_width = <?php echo esc_js( $width ) ?>;
				var img_height = <?php echo esc_js( $height ) ?>;

				var x = Math.round((img_width - width) / 2);
				var y = Math.round((img_height - height) / 2);

				$image.cropper('setData', {
					"x": x,
					"y": y
				});
			});

			/**
			 * Enable the cropper.
			 */
			$image.cropper({
				preview: ".thumb-preview",
				crop: function(data) {
					$dataX.val(Math.round(data.x));
					$dataY.val(Math.round(data.y));
					$dataHeight.val(Math.round(data.height));
					$dataWidth.val(Math.round(data.width));
					$dataRotate.val(Math.round(data.rotate));
				},
				<?php if(!$differentSizes): ?>
				aspectRatio: <?php echo esc_js( $ngg_options['thumbwidth'] ) ?> / <?php echo esc_js( $ngg_options['thumbheight'] ) ?>
				<?php endif; ?>
			});
		});
	</script>
	<?php
}