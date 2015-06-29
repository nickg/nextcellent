<?php
include_once( 'class-ngg-gallery-list-table.php' );
include_once( 'class-ngg-manager.php' );

/**
 * Class NGG_Gallery_Manager
 *
 * Display the gallery managing page.
 */
class NGG_Gallery_Manager extends NGG_Manager {

	/**
	 * Display the page.
	 */
	public function display() {

		parent::display();

		/**
		 * Add a gallery.
		 */
		if ( isset( $_POST['gallery_name'] ) ) {
			$this->handle_add_gallery();
		}

		/**
		 * Display the actual table.
		 */
		$table = new NGG_Gallery_List_Table( self::BASE );
		$table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Galleries', 'nggallery' ); ?>
				<?php if ( current_user_can( 'NextGEN Upload images' ) && nggGallery::current_user_can( 'NextGEN Add new gallery' ) ) { ?>
					<a class="add-new-h2" id="new-gallery" href="#"><?php _e( 'Add new gallery', 'nggallery' ) ?></a>
				<?php }; ?>
			</h2>

			<form method="get">
				<input type="hidden" name="page" value="nggallery-manage">
				<input type="hidden" name="mode" value="search">
				<?php $table->search_box( 'Search images', 'nggallery' ); ?>
			</form>

			<form method="post">
				<input type="hidden" id="page_type" name="page_type" value="gallery"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
		$this->print_dialogs();
		$this->print_scripts();
	}

	protected function print_scripts() {
		parent::print_scripts();
		?>
		<script type="text/javascript">
			jQuery(function() {
				jQuery("#new-gallery").click(function() {
					addGalleryDialog();
					return false;
				});
			});

			function addGalleryDialog() {
				showDialog("#add_gallery_dialog", '<?php echo esc_js(__('Add a new gallery','nggallery')); ?>');
			}
		</script>
		<?php
	}

	protected function print_dialogs() {
		parent::print_dialogs();

		$options = get_option( 'ngg_options' );
		?>
		<div class="ngg-dialog-container">
			<!-- Add Gallery -->
			<form id="add_gallery_dialog" method="POST" accept-charset="utf-8">
				<?php wp_nonce_field( 'ngg_add_gallery' ); ?>
				<label>
					<strong><?php _e( 'Name', 'nggallery' ); ?>: </strong>
					<input id="gallery_name" type="text" class="regular-text" name="gallery_name">
				</label>
				<br>
				<?php if ( ! is_multisite() ) { ?>
					<?php _e( 'Create a new , empty gallery below the folder', 'nggallery' ); ?>
					<strong><?php echo $options['gallerypath']; ?></strong><br>
				<?php } ?>
				<p class="description">
					<?php printf( __( 'Allowed characters for file and folder names are %s', 'nggallery' ), 'a-z, A-Z, 0-9, -, _' ) ?>
				</p>
				<?php do_action( 'ngg_add_new_gallery_form' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add a new gallery.
	 */
	private function handle_add_gallery() {

		if ( wp_verify_nonce( $_POST['_wpnonce'],
				'ngg_add_gallery' ) === false || ! nggGallery::current_user_can( 'NextGEN Add new gallery' )
		) {
			nggGallery::show_error( __( 'You waited too long, or you cheated.', 'nggallery' ) );

			return;
		}

		$options = get_option( 'ngg_options' );

		// get the default path for a new gallery
		$default_path = $options['gallerypath'];
		$new_gallery  = esc_attr( $_POST['gallery_name'] );
		if ( ! empty( $new_gallery ) ) {
			nggAdmin::create_gallery( $new_gallery, $default_path );
		}

		do_action( 'ngg_update_addgallery_page' );
	}
}