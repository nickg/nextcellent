<?php

include_once( 'class-ngg-manager.php' );
include_once( NGGALLERY_ABSPATH . '/admin/interface-ngg-displayable.php' );

/**
 * Class NGG_Sort_Manager
 *
 * This class represents the page where a user can sort the gallery.
 */
class NGG_Sort_Manager implements NGG_Displayable {

	/**
	 * @todo We also use this in NGG_Manager. Should we make another superclass or a trait for one line?
	 */
	const BASE = 'admin.php?page=nggallery-manage';

	private $id;

	public function __construct() {
		$this->id = (int) $_GET['gid'];
	}

	function display() {

		/**
		 * Check if sorting is actually allowed.
		 */
		$options = get_option( 'ngg_options' );

		if ( $options['galSort'] != "sortorder" ) {
			//Disable sort button and provide feedback why is disabled
			nggGallery::show_error( __( 'To enable manual Sort set Custom Order Sort. See Settings->Gallery Settings->Sort Options',
				'nggallery' ) );
			echo '<a href="' . self::BASE . '&mode=image&gid=' . $this->id . '">' . __( 'Go back',
					'nggallery' ) . '</a>';

			return;
		}

		if ( isset( $_POST['update_sort_order'] ) ) {
			$this->handle_update_sort_order();
		}

		/**
		 * @global $nggdb nggdb
		 */
		global $nggdb;

		// look for presort args
		if ( isset( $_GET['presort'] ) ) {
			$presort = $_GET['presort'];
		} else {
			$presort = false;
		}
		if ( ( isset( $_GET['dir'] ) && $_GET['dir'] == 'DESC' ) ) {
			$dir = 'DESC';
		} else {
			$dir = 'ASC';
		}
		$sort_items = array( 'pid', 'filename', 'alttext', 'imagedate' );
		// ensure that nobody added some evil sorting :-)
		if ( in_array( $presort, $sort_items ) ) {
			$picturelist = $nggdb->get_gallery( $this->id, $presort, $dir, false );
		} else {
			$picturelist = $nggdb->get_gallery( $this->id, 'sortorder', $dir, false );
		}

		//This is the url without any presort variable
		$clean_url = self::BASE . '&mode=sort&gid=' . $this->id;
		//If we go back, then the mode should be edit
		$back_url = self::BASE . '&mode=image&gid=' . $this->id;

		//In case of presort, then we take this url.
		if ( isset( $_GET['dir'] ) || isset( $_GET['presort'] ) ) {
			$base_url = $_SERVER['REQUEST_URI'];
		} else {
			$base_url = $clean_url;
		}

		?>
		<div class="wrap">
			<h2><?php _e( 'Sort Gallery', 'nggallery' ) ?></h2>

			<form id="sort_gallery" method="POST" action="<?php echo $clean_url ?>" accept-charset="utf-8">
				<div class="tablenav">
					<div class="alignleft actions">
						<?php wp_nonce_field( 'ngg-update-sort' ) ?>
						<a href="<?php echo esc_url( $back_url ); ?>" class="button"><?php _e( 'Back to gallery',
								'nggallery' ); ?></a>
						<input class="button-primary action" type="submit" name="update_sort_order" onclick="saveImageOrder()" value="<?php _e( 'Update Sort Order',
							'nggallery' ) ?>">
					</div>
				</div>
				<input name="sort_order" type="hidden" id="sort_order">
				<ul class="subsubsub">
					<li><?php _e( 'Presort', 'nggallery' ) ?> :</li>
					<li><a href="<?php echo esc_attr( remove_query_arg( 'presort',
							$base_url ) ); ?>" <?php if ( $presort == '' ) {
							echo 'class="current"';
						} ?>><?php _e( 'Unsorted', 'nggallery' ) ?></a> |
					</li>
					<li><a href="<?php echo esc_attr( add_query_arg( 'presort', 'pid',
							$base_url ) ); ?>" <?php if ( $presort == 'pid' ) {
							echo 'class="current"';
						} ?>><?php _e( 'Image ID', 'nggallery' ) ?></a> |
					</li>
					<li><a href="<?php echo esc_attr( add_query_arg( 'presort', 'filename',
							$base_url ) ); ?>" <?php if ( $presort == 'filename' ) {
							echo 'class="current"';
						} ?>><?php _e( 'Filename', 'nggallery' ) ?></a> |
					</li>
					<li><a href="<?php echo esc_attr( add_query_arg( 'presort', 'alttext',
							$base_url ) ); ?>" <?php if ( $presort == 'alttext' ) {
							echo 'class="current"';
						} ?>><?php _e( 'Alt/Title text', 'nggallery' ) ?></a> |
					</li>
					<li><a href="<?php echo esc_attr( add_query_arg( 'presort', 'imagedate',
							$base_url ) ); ?>" <?php if ( $presort == 'imagedate' ) {
							echo 'class="current"';
						} ?>><?php _e( 'Date/Time', 'nggallery' ) ?></a> |
					</li>
					<li><a href="<?php echo esc_attr( add_query_arg( 'dir', 'ASC',
							$base_url ) ); ?>" <?php if ( $dir == 'ASC' ) {
							echo 'class="current"';
						} ?>><?php _e( 'Ascending', 'nggallery' ) ?></a> |
					</li>
					<li><a href="<?php echo esc_attr( add_query_arg( 'dir', 'DESC',
							$base_url ) ); ?>" <?php if ( $dir == 'DESC' ) {
							echo 'class="current"';
						} ?>><?php _e( 'Descending', 'nggallery' ) ?></a></li>
				</ul>
			</form>
			<br style="clear:both">

			<div class='sortable'>
				<?php
				if ( $picturelist ) {
					foreach ( $picturelist as $picture ) {
						?>
						<div class="image-box" id="pid-<?php echo $picture->pid ?>" data-id="<?php echo $picture->pid ?>">
							<img src="<?php echo esc_url( $picture->thumbURL ); ?>">

							<p><?php echo esc_html( stripslashes( $picture->alttext ) ); ?></p>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(".sortable").sortable({items: 'div.image-box'});
				jQuery("#sort_gallery").submit(function() {
					var ids = [];

					jQuery(".image-box").each(function() {
						ids.push(jQuery(this).data('id'));
					});

					jQuery('#sort_order').val(ids.join(','));
				})
			});
		</script>
		<?php
	}

	/**
	 * Update the sort order if the user wants to.
	 *
	 * @access private
	 */
	private function handle_update_sort_order() {

		//Check the nonce.
		if ( wp_verify_nonce( $_POST['_wpnonce'], 'ngg-update-sort' ) === false ) {
			nggGallery::show_error( __( 'You waited too long, or you cheated.', 'nggallery' ) );

			return;
		}

		global $wpdb;

		$ids = $integerIDs = array_map( 'intval', explode( ',', $_POST['sort_order'] ) );

		if ( is_array( $ids ) ) {

			/**
			 * Prepare the SQL statement. Preparing isn't necessary here: we are sure we have int:
			 * PHP's internal counter and an array we have run intval over. Preparing would cost too much for no gain.
			 */
			$sql = "UPDATE $wpdb->nggpictures SET sortorder = CASE";

			foreach ( $ids as $key => $pic_id ) {
				$sql .= " WHEN pid = $pic_id THEN $key";
			}

			$string = join( ',', $ids );

			$sql .= " END WHERE pid IN ($string)";

			$wpdb->query( $sql );

			do_action( 'ngg_gallery_sort', $this->id );

			nggGallery::show_message( __( 'Sort order changed', 'nggallery' ) );
		}
	}
}