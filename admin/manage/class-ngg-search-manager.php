<?php

include_once( 'class-ngg-abstract-image-manager.php' );

/**
 * Class NGG_Search_Manager
 *
 * Represents an image search page.
 */
class NGG_Search_Manager extends NGG_Abstract_Image_Manager {

	private $search;

	/**
	 * Select the search parameter and add the gallery ID column.
	 */
	public function __construct() {
		$this->search = $_GET['s'];

		add_filter( 'ngg_manage_images_columns', array( $this, 'add_column' ) );
		add_action( 'ncg_manage_image_custom_column', array( $this, 'add_column_content' ), 10, 2 );
	}

	public function display() {

		parent::display();

		set_query_var( 's', $this->search );
		$request = get_search_query();

		/**
		 * Display the actual table.
		 */
		$table = new NGG_Image_List_Table( self::BASE );
		$table->prepare_items( $request );
		?>
		<div class="wrap">
			<h2><?php printf( __( 'Image results for %s', 'nggallery' ), $this->search ) ?></h2>
			<form id="update_images" class="nggform" method="POST" action="<?php echo self::BASE . '&mode=search&s=' . $this->search; ?>" accept-charset="utf-8">
				<?php wp_nonce_field( 'ngg-update-images', '_ngg_nonce_images' ); ?>
				<input type="hidden" id="page_type" name="page_type" value="image"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
		$this->print_dialogs();
		$this->print_scripts();

	}

	/**
	 * Add the gallery ID column.
	 *
	 * @access private
	 *
	 * @param $columns
	 *
	 * @return array|bool
	 */
	public function add_column( $columns ) {

		$key   = 'gid';
		$value = '<span class="dashicons dashicons-format-gallery" title="' . __( 'Gallery ID', 'nggallery' ) . '"></span>';

		return $this->array_insert_after( 'id', $columns, $key, $value );
	}

	/**
	 * Add the gallery id.
	 *
	 * @access private
	 *
	 * @param string $name
	 * @param nggImage $item
	 */
	public function add_column_content( $name, $item ) {
		if ( $name === "gid" ) {
			echo $item->galleryid;
		}
	}

	/*
	 * Inserts a new key/value after the key in the array.
	 *
	 * @param $key The key to insert after.
	 * @param $array array An array to insert in to.
	 * @param $new_key The key to insert.
	 * @param $new_value An value to insert.
	 *
	 * @return array|bool The new array if the key exists, FALSE otherwise.
	 *
	 * @see http://eosrei.net/articles/2011/11/php-arrayinsertafter-arrayinsertbefore
	 */
	private function array_insert_after( $key, array $array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				$new[ $k ] = $value;
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
			}

			return $new;
		}

		return false;
	}
}