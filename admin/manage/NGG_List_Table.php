<?php

if( !class_exists( 'WP_List_Table' ) ) {
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
class NGG_List_Table extends WP_List_Table {

	private $base;

	public function __construct( $base, $screen = null ) {

		parent::__construct(array('screen' => $screen, 'plural' => 'ngg-gallery-manager'));

		$this->base = $base;

		add_filter( 'manage_' . $this->screen->id . '_columns', array( $this, 'get_columns' ), 0 );
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items()	{
		/**
		 * @global $nggdb nggdb
		 */
		global $nggdb;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		/**
		 * Do the pagination.
		 */
		$currentPage = $this->get_pagenum();
		$perPage = 25;

		/**
		 * Sorting
		 */
		if ( ( isset ( $_GET['order'] ) && $_GET['order'] == 'desc' ) ) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		if ( ( isset ( $_GET['orderby'] ) && ( in_array( $_GET['orderby'], array( 'gid', 'title', 'author' ) ) ) ) ) {
			$order_by = $_GET['orderby'];
		} else {
			$order_by = 'gid';
		}

		$start = ($currentPage - 1) * $perPage;
		$this->items = $nggdb->find_all_galleries($order_by, $order, true, $perPage, $start, true);

		$totalItems = (int) $nggdb->count_galleries();

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

		/**
		 * Sort the items/
		 */
		usort($this->items, array($this, 'sort'));
	}

	/**
	 * Get the hidden columns.
	 */
	function get_hidden_columns() {
		return array();
	}

	/**
	 * The checkbox column.
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		if ( nggAdmin::can_manage_this_gallery( $item->author ) ) {
			return '<input name="doaction[]" type="checkbox" value="' . $item->gid . '" />';
		} else {
			return "";
		}
	}

	public function column_title($item) {
		if (nggAdmin::can_manage_this_gallery($item->author)) {
			$out = '<a href="' . wp_nonce_url( $this->base . '&mode=edit&amp;gid=' . $item->gid, 'ngg_editgallery') .  '" class="edit" title="' . __('Edit') . '">';
			$out .= esc_html($item->title);
			$out .= "</a>";
		} else {
			$out = esc_html($item->title);
		}
		$out .= '<div class="row-actions"></div>';
		return $out;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  nggGallery $item        Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name )
	{
		switch( $column_name ) {
			case 'id':
				return $item->gid;
			case 'description':
				return $item->galdesc;
			case 'author':
				return $item->author;
			case 'page_id':
				return $item->pageid;
			case 'quantity':
				return $item->counter;
			default:
				$columns = apply_filters('ngg_manage_gallery_columns_content', array($item, $column_name));
				return $columns;
		}
	}

	/**
	 * Get the columns.
	 */
	public function get_columns() {

		$columns = array(
			'cb'            => '<input name="checkall" type="checkbox" onclick="checkAll(document.getElementById(\'editgalleries\'));" />',
			'id'            => __('ID', 'nggallery'),
			'title'         => __( 'Title', 'nggallery'),
			'description'   => __('Description', 'nggallery'),
			'author'        => __('Author', 'nggallery'),
			'page_id'       => __('Page ID', 'nggallery'),
			'quantity'      => __( 'Images', 'nggallery' )
		);

		/**
		 * Apply a filter to the columns.
		 */
		$columns = apply_filters('ngg_manage_gallery_columns', $columns);

		return $columns;
	}

	/**
	 * Get the sortable columns.
	 */
	protected function get_sortable_columns() {
		return array(
			'id'    => array( 'gid', true ),
			'title'   => array('title', false),
			'author'   => array('author', false)
		);
	}

	private function sort($a, $b) {

		if(isset($_GET['orderby']) && $_GET['orderby'] === 'title') {
			$result = strnatcmp($a->title, $b->title);
		} else {
			if(isset($_GET['orderby'])) {
				$orderby = $_GET['orderby'];
			} else {
				$orderby = 'gid';
			}
			$result = $a->{$orderby} - $b->{$orderby};
		}

		if(!isset($_GET['order']) || $_GET['order'] === 'asc') {
			return $result;
		} else {
			return - $result;
		}
	}

	protected function get_bulk_actions() {
		return array(
			'delete_gallery'    => __('Delete','nggallery'),
			'set_watermark'     => __('Set watermark', 'nggallery'),
			'new_thumbnail'     => __('Create new thumbnails','nggallery'),
			'resize_images'     => __('Resize images','nggallery'),
			'import_meta'       => __('Import metadata','nggallery'),
			'recover_images'    => __('Recover from backup','nggallery'),
		);
	}
}