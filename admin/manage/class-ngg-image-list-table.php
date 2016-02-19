<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class NGG_List_Table
 *
 * This class represents the listing of the galleries in the admin menu.
 *
 * This class was written with WP_List_Table from WordPress 4.3.
 * If this doesn't work anymore in the future, it's because that class has changed.
 */
class NGG_Image_List_Table extends WP_List_Table {

	private $base;

	public function __construct( $base, $screen = null ) {

		parent::__construct( array( 'screen' => $screen, 'plural' => 'ngg-manager' ) );

		$this->base = $base;

		add_filter( 'manage_' . $this->screen->id . '_columns', array( $this, 'get_columns' ), 0 );
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @param bool|string $search The search string, or false if we don't search.
	 */
	public function prepare_items($search = false) {

		/**
		 * @global $nggdb nggdb
		 */
		global $nggdb;

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		if( $search ) {
			// look now for the images
			$search_for_images = (array) $nggdb->search_for_images( $search );
			$search_for_tags   = (array) nggTags::find_images_for_tags( $search , 'ASC' );

			// finally merge the two results together
			$this->items = array_merge( $search_for_images , $search_for_tags );

			$this->set_pagination_args( array(
				'total_items' => count( $this->items )
			) );

		} else {

			/**
			 * Do the pagination.
			 */
			$currentPage = $this->get_pagenum();
			$perPage     = $this->get_items_per_page('ngg_images_per_page', 50);

			$options    = get_option( 'ngg_options' );
			$gallery_id = (int) $_GET['gid'];

			$start       = ( $currentPage - 1 ) * $perPage;

			$this->items = $nggdb->get_gallery( $gallery_id, $options['galSort'], $options['galSortDir'], false,
				$perPage,
				$start );

			$totalItems = (int) nggdb::count_images_in_gallery( $gallery_id );

			$this->set_pagination_args( array(
				'total_items' => $totalItems,
				'per_page'    => $perPage
			) );
		}
	}

	/**
	 * Override to add a button if on top.
	 *
	 * @param string $which
	 */
	protected function bulk_actions($which = '') {
		parent::bulk_actions($which);

		if($which === 'top') {
			?>
			<button type="submit" class="button-primary action manager-save" name="update_images">
				<?php _e( "Save Changes", 'nggallery' ); ?>
			</button>
			<?php
		}
	}

	/**
	 * Get the hidden columns from the screen options.
	 */
	public function get_hidden_columns() {
		return (array) get_user_option( 'manage' . $this->screen->id . 'columnshidden' );;
	}

	/**
	 * The checkbox column.
	 *
	 * @param nggImage $item
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {
		return '<input name="doaction[]" type="checkbox" value="' . $item->pid . '" />';
	}

	/**
	 * @param nggImage $item
	 *
	 * @return string
	 */
	protected function column_thumbnail( $item ) {
		$out = '<a href="' . esc_url( add_query_arg( 'i', mt_rand(),
				$item->imageURL ) ) . '" class="shutter" title="' . esc_attr( $item->filename ) . '">';
		$out .= '<img class="thumb" src="' . esc_url( add_query_arg( 'i', mt_rand(),
				$item->thumbURL ) ) . '" id="thumb' . $item->pid . '" /></a>';

		return $out;
	}

	/**
	 * @param nggImage $item
	 *
	 * @return string
	 */
	protected function column_filename( $item ) {
		$date = mysql2date( get_option( 'date_format' ), $item->imagedate );
		ob_start();
		?>
		<a href="<?php echo esc_url( $item->imageURL ) ?>" class="thickbox" title="<?php esc_attr_e( $item->filename ) ?>">
			<strong>
				<?php esc_html_e( $item->filename ) ?>
			</strong>
		</a>
		<br>
		<span class="date"><?php echo $date ?></span>
		<input type="text" class="datepicker" value="<?php echo $date ?>"/>
		<span class="change"> <?php _e( 'Change Date', 'nggallery' ); ?></span>
		<input type="hidden" class="rawdate" name="date[<?php echo $item->pid ?>]" value="<?php echo $item->imagedate ?>"/>
		<?php if ( ! empty( $item->meta_data ) ) { ?>
			<br><?php echo $item->meta_data['width'] ?> x <?php echo $item->meta_data['height'] ?><?php _e( 'pixel',
				'nggallery' ) ?>
		<?php } ?>
		<p>
			<?php
			$actions      = $this->get_row_actions( $item );
			$action_count = count( $actions );
			$i            = 0;
			echo '<div class="row-actions">';
			foreach ( $actions as $action => $link ) {
				++ $i;
				( $i == $action_count ) ? $sep = '' : $sep = ' | ';
				echo "<span class='$action'>$link$sep</span>";
			}
			echo '</div>';
			?>
		</p>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param $item nggImage
	 *
	 * @return string
	 */
	protected function column_alt_title_desc( $item ) {
		$img_alt_text    = nggGallery::suppress_injection( $item->alttext );
		$img_description = nggGallery::suppress_injection( $item->description );

		$out = '<input placeholder="' . __( "Alt & title text",
				'nggallery' ) . '" name="alttext[' . $item->pid . ']" type="text" style="width:95%; margin-bottom: 2px;" value="' . $img_alt_text . '"/>';
		$out .= '<br>';
		$out .= '<textarea placeholder="' . __( "Description",
				'nggallery' ) . '" name="description[' . $item->pid . ']" style="width:95%; margin: 1px;" rows="2">' . $img_description . '</textarea>';

		return $out;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  nggImage $item      Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return '<input type="hidden" name="pid[]" value="' . $item->pid . '">' . $item->pid;
			case 'tags':
				$item->tags = wp_get_object_terms( $item->pid, 'ngg_tag', 'fields=names' );
				if ( is_array( $item->tags ) ) {
					$item->tags = implode( ', ', $item->tags );
				}

				return '<textarea placeholder="' . __( "Separated by commas",
					'nggallery' ) . '" name="tags[' . $item->pid . ']" style="width:95%;" rows="2">' . $item->tags . '</textarea>';
			case 'exclude':
				return '<input name="exclude[' . $item->pid . ']" type="checkbox" value="1" ' . checked( $item->exclude, true, false ) . '/>';
			default:
				ob_start();
				//The old action needs a pid.
				do_action( 'ngg_manage_image_custom_column', $column_name, $item->pid );

				//We pass the whole object to new action.
				do_action( 'ncg_manage_image_custom_column', $column_name, $item );

				return ob_get_clean();
		}
	}

	/**
	 * With this we can register the columns in the screen options API.
	 */
	public static function get_columns_static() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'id'             => __( 'ID', 'nggallery' ),
			'thumbnail'      => __( 'Thumbnail', 'nggallery' ),
			'filename'       => __( 'Filename', 'nggallery' ),
			'alt_title_desc' => __( 'Alt & Title Text', 'nggallery' ) . '/' . __( 'Description', 'nggallery' ),
			'tags'           => __( 'Tags', 'nggallery' ),
			'exclude'        => __( 'Exclude', 'nggallery' )
		);

		/**
		 * Apply a filter to the columns.
		 */
		$columns = apply_filters( 'ngg_manage_images_columns', $columns );

		return $columns;
	}

	/**
	 * Get the columns.
	 */
	public function get_columns() {

		return self::get_columns_static();
	}

	/**
	 * @param $item nggImage
	 *
	 * @return array|mixed|void
	 */
	private function get_row_actions( $item ) {

		if(isset($_GET['paged'])) {
			$paged = '&paged=' . $_GET['paged'];
		} else {
			$paged = '';
		}

		$url = $this->base . '&mode=image&gid=' .  $_GET['gid'] . $paged;

		$actions = array(
			'view'         => '<a class="shutter" href="' . esc_url( $item->imageURL ) . '" title="' . esc_attr( sprintf( __( 'View "%s"' ),
					sanitize_title( $item->filename ) ) ) . '">' . __( 'View', 'nggallery' ) . '</a>',
			'meta'         => '<a class="ngg-dialog" data-action="show_meta" data-id="' . $item->pid . '" href="#" title="' . __( 'Show Meta data',
					'nggallery' ) . '">' . __( 'Meta', 'nggallery' ) . '</a>',
			'custom_thumb' => '<a class="ngg-dialog" data-action="edit_thumb" data-id="' . $item->pid . '" href="#" title="' . __( 'Customize thumbnail',
					'nggallery' ) . '">' . __( 'Edit thumb', 'nggallery' ) . '</a>',
			'rotate'       => '<a class="ngg-dialog" data-action="rotate" data-id="' . $item->pid . '" href="#" title="' . __( 'Rotate',
					'nggallery' ) . '">' . __( 'Rotate', 'nggallery' ) . '</a>',
		);
		if ( file_exists( $item->imagePath . '_backup' ) ) {
			$actions['recover'] = '<a class="confirm_recover" href="' . wp_nonce_url( $url . "&action=recover&pid=" . $item->pid, 'ngg_row_action' ) .
			                      '" data-file="' . esc_attr($item->filename) . '">' .
			                      __( 'Recover', 'nggallery' ) . '</a>';
		}
		$actions['delete'] = '<a class="confirm_delete" href="' . wp_nonce_url( $url . "&action=delete&pid=" . $item->pid, 'ngg_row_action' ) .
		                     '" class="delete column-delete" data-file="' . esc_attr($item->filename) . '">' .
		                     __( 'Delete' ) . '</a>';

		$actions = apply_filters( 'ngg_manage_images_actions', $actions );

		return $actions;
	}

	/**
	 * Get the sortable columns.
	 */
	protected function get_sortable_columns() {
		return array();
	}

	protected function get_bulk_actions() {
		return array(
			'set_watermark'  => __( 'Set watermark', 'nggallery' ),
			'new_thumbnail'  => __( 'Create new thumbnails', 'nggallery' ),
			'resize_images'  => __( 'Resize images', 'nggallery' ),
			'import_meta'    => __( 'Import metadata', 'nggallery' ),
			'recover_images' => __( 'Recover from backup', 'nggallery' ),
			'delete_images'  => __( 'Delete images', 'nggallery' ),
			'rotate_cw'      => __( 'Rotate images clockwise', 'nggallery' ),
			'rotate_ccw'     => __( 'Rotate images counter-clockwise', 'nggallery' ),
			'copy_to'        => __( 'Copy to...', 'nggallery' ),
			'move_to'        => __( 'Move to...', 'nggallery' ),
			'add_tags'       => __( 'Add tags', 'nggallery' ),
			'delete_tags'    => __( 'Delete tags', 'nggallery' ),
			'overwrite_tags' => __( 'Overwrite tags', 'nggallery' ),
			'set_title'      => __( 'Set alt & title text', 'nggallery' ),
			'set_descr'      => __( 'Set description', 'nggallery' ),
		);
	}
}
