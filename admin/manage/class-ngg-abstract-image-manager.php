<?php

include_once( 'class-ngg-manager.php' );
include_once( 'class-ngg-image-list-table.php' );

/**
 * Class NGG_Abstract_Image_Manager
 *
 * Contains some common methods to use when displaying images.
 */
abstract class NGG_Abstract_Image_Manager extends NGG_Manager {

	public function display() {

		parent::display();

		if ( isset ( $_POST['update_images'] ) ) {
			$this->handle_update_images();
		}
	}

	/**
	 * @todo Attempting to make WP Ajax Standard
	 */
	protected function print_scripts() {
		parent::print_scripts();
		?>
		<script type="text/javascript">


			var defaultAction = function(dialog) {
				jQuery(dialog).dialog('close');
			};

			var doAction = defaultAction;

			/**
			 * Load the content with AJAX.
			 */
			jQuery('a.ngg-dialog').click(function() {
				//Get the spinner.
				var $spinner = jQuery("#spinner");
				var $this = jQuery(this);
				var current_cmd = $this.data("action");
				var current_id = $this.data("id");

				if (!$spinner.length) {
					jQuery("body").append('<div id="spinner"></div>');
				}

				$spinner.fadeIn();

				var dialog = jQuery('<div style="display:none" class="ngg-load-dialog"></div>').appendTo('body');
				// load the remote content
				jQuery.post({
					url: ajaxurl,
					data: {action:"image_manager", cmd: current_cmd, id: current_id},
					success: function(response){
						dialog.append(response);
						$spinner.hide(); 						//jQuery('#spinner').hide();
						showDialog(dialog, ($this.attr('title')) ? $this.attr('title') : '', doAction);//doAction function must be defined in the actions.php
					}
				});

				//prevent the browser to follow the link
				return false;
			});

			/**
			 * Show a message on the image action modal window.
			 *
			 * @param message string The message.
			 */
			function showMessage(message) {
				jQuery('#thumbMsg').html(message).css({'display': 'block'});
				setTimeout(function() {
					jQuery('#thumbMsg').fadeOut('slow');
				}, 1500);

				var d = new Date();
				var $image = jQuery("#imageToEdit");
				var newUrl = $image.attr("src") + "?" + d.getTime();
				$image.attr("src", newUrl);
			}
		</script>

		<?php
	}


/*************************************/

	/**
	 * @todo Make this better.
	 */
	protected function print_scripts_old() {
		parent::print_scripts();
		?>
		<script type="text/javascript">

			var defaultAction = function(dialog) {
				jQuery(dialog).dialog('close');
			};

			var doAction = defaultAction;

			/**
			 * Load the content with AJAX.
			 */
			jQuery('a.ngg-dialog').click(function() {
				//Get the spinner.
				var $spinner = jQuery("#spinner");
				var $this = jQuery(this);
				var action = $this.data("action");
				var id = $this.data("id");
				var base_url = "<?php echo plugins_url('actions.php?cmd=', __FILE__) ?>";

				if (!$spinner.length) {
					jQuery("body").append('<div id="spinner"></div>');
				}

				$spinner.fadeIn();

				var dialog = jQuery('<div style="display:none" class="ngg-load-dialog"></div>').appendTo('body');
				// load the remote content
				dialog.load(
					base_url + action + "&id=" + id,
					{},
					function() {
						jQuery('#spinner').hide();
						//The doAction function must be defined in the actions.php file.
						showDialog(dialog, ($this.attr('title')) ? $this.attr('title') : '', doAction);
					}
				);
				//prevent the browser to follow the link
				return false;
			});

			/**
			 * Show a message on the image action modal window.
			 *
			 * @param message string The message.
			 */
			function showMessage(message) {
				jQuery('#thumbMsg').html(message).css({'display': 'block'});
				setTimeout(function() {
					jQuery('#thumbMsg').fadeOut('slow');
				}, 1500);

				var d = new Date();
				var $image = jQuery("#imageToEdit");
				var newUrl = $image.attr("src") + "?" + d.getTime();
				$image.attr("src", newUrl);
			}
		</script>

		<?php
	}

	/**
	 * @todo Make a real DAO system for NextCellent.
	 * @todo Make this a lot faster by merging all these database commands
	 */
	private function handle_update_images() {

		if ( wp_verify_nonce( $_POST['_ngg_nonce_images'], 'ngg-update-images' ) === false ) {
			nggGallery::show_error( __( 'You waited too long, or you cheated.', 'nggallery' ) );

			return;
		}

		global $wpdb, $nggdb;

		//TODO:Error message when update failed
/*
		$description = isset ( $_POST['description'] ) ? sanitize_text_field( $_POST['description']) : array();
		$alttext     = isset ( $_POST['alttext'] ) ? sanitize_text_field($_POST['alttext']) : array();
		$exclude     = isset ( $_POST['exclude'] ) ? sanitize_text_field($_POST['exclude']) : false;
		$taglist     = isset ( $_POST['tags'] ) ? sanitize_text_field($_POST['tags']) : false;
		$pictures    = isset ( $_POST['pid'] ) ? sanitize_text_field($_POST['pid']) : false;
		$date        = isset ( $_POST['date'] ) ? sanitize_text_field($_POST['date']) : "NOW()"; //Not sure if NOW() will work or not but in theory it should
*/
		$description = isset ( $_POST['description'] ) ?  $_POST['description'] : array();
		$alttext     = isset ( $_POST['alttext'] ) ? $_POST['alttext'] : array();
		$exclude     = isset ( $_POST['exclude'] ) ? $_POST['exclude'] : false;
		$taglist     = isset ( $_POST['tags'] ) ? $_POST['tags'] : false;
		$pictures    = isset ( $_POST['pid'] ) ? $_POST['pid'] : false;
		$date        = isset ( $_POST['date'] ) ? $_POST['date']: "NOW()"; //Not sure if NOW() will work or not but in theory it should

		if ( is_array( $pictures ) ) {
			foreach ( $pictures as $pid ) {
				$image = $nggdb->find_image( $pid );
				if ( $image ) {
					// description field
					$image->description = $description[ $image->pid ];
					$image->date        = $date[ $image->pid ];
					// only uptade this field if someone change the alttext
					if ( $image->alttext != $alttext[ $image->pid ] ) {
						$image->alttext    = $alttext[ $image->pid ];
						$image->image_slug = nggdb::get_unique_slug( sanitize_title( $image->alttext ), 'image',
							$image->pid );
					}

					// set exclude flag
					if ( is_array( $exclude ) ) {
						$image->exclude = ( array_key_exists( $image->pid, $exclude ) ) ? 1 : 0;
					} else {
						$image->exclude = 0;
					}

					// update the database
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->nggpictures SET image_slug = '%s', alttext = '%s', description = '%s', exclude = %d, imagedate = %s WHERE pid = %d",
						$image->image_slug, $image->alttext, $image->description, $image->exclude, $image->date,
						$image->pid ) );
					// remove from cache
					wp_cache_delete( $image->pid, 'ngg_image' );

					// hook for other plugins after image is updated
					do_action( 'ngg_image_updated', $image );
				}

			}

			//This is for backwards compatibility.
			do_action( 'ngg_update_gallery', (int) $_GET['gid'], $_POST);
		}

		//TODO: This produce 300-400 queries !
		if ( is_array( $taglist ) ) {
			foreach ( $taglist as $key => $value ) {
				$tags = explode( ',', $value );
				wp_set_object_terms( $key, $tags, 'ngg_tag' );
			}
		}

		nggGallery::show_message( __( 'Update successful', "nggallery" ) );
	}
}