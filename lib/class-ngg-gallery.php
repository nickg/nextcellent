<?php
/**
 * A NextCellent Gallery object.
 *
 * @since 1.9.25
 */

class NGG_Gallery {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var array {
	 *      @type string $slug
	 *      @type string $path The relative path.
	 *      @type string $title
	 *      @type string $galdesc The description.
	 *      @type int $pageid The ID of the page for this gallery.
	 *      @type int $previewpic The ID of the preview picture.
	 *      @type int $author The ID of the author.
	 *      @type int extras_post_id
	 *  }
	 */
	private $data;

	/**
	 * @param int|string $id The ID or slug of the gallery.
	 *
	 * @throws NGG_Not_Found If the gallery was not found.
	 */
	public function __construct( $id ) {

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		if( is_numeric( $id )) {
			$this->data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->nggallery WHERE gid = %d", $id ), ARRAY_A );
		} else {
			$this->data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->nggallery WHERE slug = %s", $id ), ARRAY_A );
		}

		if( is_null( $this->data ) ) {
			throw new NGG_Not_Found( printf( __( 'The gallery with id %s could not be found.', 'nggallery' ), $id ) );
		}

		$this->id = $this->data['gid'];
		unset( $this->data['gid'], $this->data['name'] );
	}

	/**
	 * @return array @see $data
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Delete this gallery.
	 *
	 * @param bool $images Should the images be deleted as well?
	 */
	public function delete_gallery( $images = true ) {

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		$wpdb->delete( $wpdb->nggallery, array( 'gid' => $this->id ) );
		$wpdb->delete( $wpdb->nggpictures, array( 'galleryid' => $this->id ) );

		global $nggdb;

		//Update the galleries to remove the deleted ID's
		//TODO: convert to OOP.
		$albums = $nggdb->find_all_album();

		foreach ($albums as $album) {
			$albumid = $album->id;
			$galleries = $album->galleries;
			$deleted = array_search($this->id, $galleries);

			unset($galleries[$deleted]);

			$new_galleries = serialize($galleries);

			nggdb::update_album($albumid, false, false, false, $new_galleries);
		}

	}

	/**
	 * Get all the images in this gallery.
	 *
	 * @param string $order_by By what they should be sorted. Must be a valid image property.
	 * @param string $order_dir The sort direction. Should be a valid sort order.
	 * @param bool $exclude If the excluded images should be excluded or not.
	 *
	 * @return array Array containing all image IDs.
	 */
	public function get_image_ids( $order_by = 'sortorder', $order_dir = 'ASC', $exclude = true) {

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		// Check for the exclude setting
		$exclude_clause = ($exclude) ? ' AND tt.exclude<>1 ' : '';

		// Say no to any other value
		if ( $order_dir == 'DESC' ) {
			$order_dir = 'DESC';
		} else {
			$order_dir = 'ASC';
		}

		$result = $wpdb->get_col( $wpdb->prepare( "SELECT tt.pid FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = %d $exclude_clause ORDER BY tt.{$order_by} $order_dir", $this->id ) );

		return $result;
	}

	/**
	 * Update the gallery with new data.
	 *
	 * @param array $data Contains the updated data. (@see data).
	 *
	 * @throws NGG_Database_Fail If the gallery could not be updated.
	 */
	public function update_gallery( $data ) {

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb, $nggdb;

		if( isset( $data['title'] ) ) {
			$data['slug'] = $nggdb::get_unique_slug( sanitize_title( $data['title'] ), 'gallery' );
		}

		$data = wp_parse_args($data, $this->data );

		if( !$wpdb->update( $wpdb->nggallery, $data, array( 'gid' => $this->id ) ) ) {
			throw new NGG_Database_Fail( printf( __( 'Could not update gallery %s', 'nggallery' ), $data['title'] ) );
		}
	}

	/**
	 * Add a new gallery to the database.
	 *
	 * @param string $title The title.
	 * @param string $path The path.
	 * @param string $description A description.
	 * @param int $page_id The page ID of the page for the gallery.
	 * @param int $preview_picture The ID of the preview picture.
	 * @param int $author The ID of the author.
	 *
	 * @return int The new ID of the gallery.
	 * @throws NGG_Database_Fail
	 *
	 */
	public static function add_gallery( $title, $path, $description = '', $page_id = 0, $preview_picture = 0, $author = 0 ) {

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		$data = array(
			'title'         => $title,
			'path'          => $path,
			'galdesc'       => $description,
			'pageid'        => $page_id,
			'previewpic'    => $preview_picture,
			'author'        => $author,
			'slug'          => nggdb::get_unique_slug( sanitize_title( $title ), 'gallery' )
		);

		if( !$wpdb->insert( $wpdb->nggallery, $data ) ) {
			throw new NGG_Database_Fail( printf( __( 'Could not create gallery %s', 'nggallery' ), $data['title'] ) );
		}

		return $wpdb->insert_id;
	}

}