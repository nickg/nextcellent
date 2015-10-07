<?php

include_once( dirname( dirname( __FILE__ ) ) . '/interface-ngg-displayable.php' );
include_once( 'class-ngg-upgrader.php' );

class NGG_Upgrade_Page implements NGG_Displayable {

	/**
	 * @var int $new_version The new version from code.
	 */
	private $new_version;

	/**
	 * @param int $new_version The new version from code.
	 */
	public function __construct( $new_version ) {
		$this->new_version = $new_version;
	}

	public function display() {

		$file_path = admin_url() . 'admin.php?page=' . $_GET['page']; //default upgrade path
		if ( is_network_admin() ) {                                  //unless if it is network  administrator...
			$file_path = network_admin_url() . 'admin.php?page=' . $_GET['page'];
		}

		if ( isset( $_GET['upgrade'] ) && $_GET['upgrade'] == 'now' ) {
			$this->doing_upgrade_page( $file_path );
		} else {
			$this->start_upgrade_page( $file_path );
		}
	}

	private function start_upgrade_page( $file_path ) {
		?>
		<div class="wrap">
			<h2><?php _e( 'Upgrade NextCellent Gallery', 'nggallery' ); ?></h2>

			<p><?php _e( 'You\'re upgrading from an older version. To enable the newest features, we sometimes need to do a database upgrade.',
					'nggallery' ); ?></p>

			<p class="description">
				<span style="margin-right:5px;" class="dashicons dashicons-info"></span>
				<?php _e( 'Normally you should be able to downgrade without any problems, but if you really want to play safe, you should make a backup of your database.',
					'nggallery' ); ?>
			</p>
			<br/>
			<a class="button button-primary"
			   href="<?php echo $file_path; ?>&upgrade=now"><?php _e( 'Start upgrade now', 'nggallery' ); ?></a>
		</div>
		<?php
	}

	private function doing_upgrade_page( $file_path ) {
		?>
		<div class="wrap">
			<h2><?php _e( 'Upgrade NextCellent Gallery', 'nggallery' ); ?></h2>

			<p><?php
				try {
					echo __( 'Upgrading database…', 'nggallery' );
					$upgrader = new NGG_Upgrader( $this->new_version );
					$upgrader->upgrade();
					echo '<span class="dashicons dashicons-yes"></span><br>';
				} catch ( Upgrade_Exception $e ) {
					echo __( "Oh no! Something went wrong while updating the database", 'nggallery' ) . ": " . $e->getMessage();
				}
				?></p>

			<p class="finished"><?php _e( 'Upgrade complete.', 'nggallery' ); ?></p>
			<br/><a class="finished button button-primary"
			        href="<?php echo $file_path; ?>"><?php _e( 'Continue to NextCellent', 'nggallery' ); ?></a>
		</div>
		<?php
	}
}