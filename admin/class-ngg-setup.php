<?php  

include_once('class-ngg-post-admin-page.php');

class NGG_Setup extends NGG_Post_Admin_Page {

	public function display() {
		parent::display();

		global $wpdb;

		?>
		<div class="wrap">
			<h2><?php _e('Reset options', 'nggallery') ;?></h2>
			<form name="resetsettings" method="post">
				<?php wp_nonce_field('ngg_uninstall') ?>
				<p><?php _e('Reset all options/settings to the default installation.', 'nggallery') ;?></p>
				<div align="center">
					<input type="submit" class="button" id="reset-to-default" name="resetdefault" value="<?php _e('Reset settings', 'nggallery') ;?>">
				</div>
			</form>
		</div>
		<?php if ( !is_multisite() || is_super_admin() ) : ?>
			<div class="wrap">
				<h2><?php _e('Uninstall plugin tables', 'nggallery') ;?></h2>

				<form name="resetsettings" method="post">
					<div>
						<?php wp_nonce_field('ngg_uninstall') ?>
						<p><?php _e('You don\'t like NextCellent Gallery?', 'nggallery') ;?></p>
						<p><?php _e('With this button you can clear all database tables. This should also happen if you uninstall the normal way, but it can be useful for manually uninstalling NextCellent completely.', 'nggallery') ;?>
					</div>
					<p style="color: red">
						<strong><?php _e('WARNING:', 'nggallery') ;?></strong>
						<br>
						<?php _e('Once uninstalled, this cannot be undone. You should use a Database Backup plugin of WordPress to backup all the tables first. NextCellent gallery is stored in the tables', 'nggallery') ;?> <strong><?php echo $wpdb->nggpictures; ?></strong>, <strong><?php echo $wpdb->nggalbum; ?></strong> <?php _e('and', 'nggallery') ;?> <strong><?php echo $wpdb->nggalbum; ?></strong>.
					</p>
					<div align="center">
						<input type="button" name="show_button" id="show-button" class="button" value="<?php _e('Show uninstall button', 'nggallery') ?>">
						<input style="display: none; color: red" id="delete-button" type="submit" name="uninstall" class="button delete" value="<?php _e('Uninstall plugin', 'nggallery') ?>">
					</div>
				</form>
			</div>
		<?php endif; ?>

		<script type="text/javascript">
			document.getElementById('reset-to-default').addEventListener('click', function(event) {
				var check = confirm(
					'<?php echo esc_js( __( 'Reset all options to default settings?', 'nggallery' ) ) ?>' +
					'\n\n' +
					'<?php echo esc_js( __( 'Choose [Cancel] to Stop, [OK] to proceed.', 'ngallery') ) ?>'
				);
				if(!check) {
					event.preventDefault();
				}
			}, false);

			document.getElementById('show-button').addEventListener('click', function() {
				document.getElementById('delete-button').style.display = "block";
			}, false);

			document.getElementById('delete-button').addEventListener('click', function() {
				var check = confirm(
					'<?php echo esc_js( __( 'You are about to uninstall this plugin from WordPress. This action is not reversible.', 'nggallery' ) ) ?>' +
					'\n\n' +
					'<?php echo esc_js( __( 'Choose [Cancel] to Stop, [OK] to proceed.', 'ngallery') ) ?>'
				);
				if(!check) {
					event.preventDefault();
				}
			}, false);



		</script>

		<?php


	}

	/**
	 * Handle the POST updates. This functions is called by the display() function, if used properly.
	 */
	protected function processor() {
		global $ngg;

		check_admin_referer('ngg_uninstall');

		include_once ( dirname (__FILE__).  '/class-ngg-installer.php');

		if (isset($_POST['resetdefault'])) {

			NGG_Installer::set_default_options();
			$ngg->load_options();

			nggGallery::show_message(__('Reset all settings to the default parameters.','nggallery'));
		}

		if (isset($_POST['uninstall'])) {

			NGG_Installer::uninstall();

			nggGallery::show_message(__('Uninstall successful! Now delete the plugin and enjoy your life!','nggallery'));
		}
	}
}