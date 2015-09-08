<?php

/**
 * The NextCellent Gallery Widget
 */
class NGG_Gallery_Widget extends WP_Widget {

	/**
	 * Register the widget.
	 */
	public function __construct() {
		parent::__construct( 'ngg-images', __( 'NextCellent Gallery Widget', 'nggallery' ), array(
			'classname'   => 'ngg_images',
			'description' => __( 'Add recent or random images from the galleries', 'nggallery' )
		) );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['items'] = (int) $new_instance['items'];

		//Validate type
		if ( $new_instance['type'] == 'random' ) {
			$instance['type'] = 'random';
		} else {
			$instance['type'] = 'recent';
		}

		//Validate show
		if ( $new_instance['show'] == "thumbnail" ) {
			$instance['show'] = 'thumbnail';
		} else {
			$instance['show'] = 'original';
		}

		$instance['width']    = (int) $new_instance['width'];
		$instance['height']   = (int) $new_instance['height'];

		if( $new_instance['exclude'] == 'denied' ) {
			$instance['exclude'] = 'denied';
		} elseif ( $new_instance['exclude'] == 'allow' ) {
			$instance['exclude'] = 'allow';
		} else {
			$instance['exclude'] = 'all';
		}

		$temp_array = explode(",", $new_instance['list']);
		array_walk( $temp_array, 'intval' );
		$instance['list']  = implode(",", $temp_array);

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance ) {

		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title'    => 'Gallery',
			'items'    => '4',
			'type'     => 'random',
			'show'     => 'thumbnail',
			'height'   => '50',
			'width'    => '75',
			'exclude'  => 'all',
			'list'     => '',
			'webslice' => true
		) );
		$title    = esc_attr( $instance['title'] );
		$items    = intval( $instance['items'] );
		$height   = esc_attr( $instance['height'] );
		$width    = esc_attr( $instance['width'] );

		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'nggallery' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" class="widefat"
			       value="<?php echo $title; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'items' ); ?>"><?php _e( 'Show:', 'nggallery' ); ?></label><br/>
			<input style="width: 60px;" id="<?php echo $this->get_field_id( 'items' ); ?>"
			       name="<?php echo $this->get_field_name( 'items' ); ?>" type="number" min="0"
			       value="<?php echo $items; ?>"/>
			<select id="<?php echo $this->get_field_id( 'show' ); ?>"
			        name="<?php echo $this->get_field_name( 'show' ); ?>">
				<option <?php selected( "thumbnail", $instance['show'] ); ?>
					value="thumbnail"><?php _e( 'Thumbnails', 'nggallery' ); ?></option>
				<option <?php selected( "original", $instance['show'] ); ?>
					value="original"><?php _e( 'Original images', 'nggallery' ); ?></option>
			</select>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'type' ); ?>_random"
			       name="<?php echo $this->get_field_name( 'type' ); ?>" type="radio"
			       value="random" <?php checked( "random", $instance['type'] ); ?> />
			<label
				for="<?php echo $this->get_field_id( 'type' ); ?>_random"><?php _e( 'random', 'nggallery' ); ?></label>
			<br/>
			<input id="<?php echo $this->get_field_id( 'type' ); ?>_recent"
			       name="<?php echo $this->get_field_name( 'type' ); ?>" type="radio"
			       value="recent" <?php checked( "recent", $instance['type'] ); ?> />
			<label
				for="<?php echo $this->get_field_id( 'type' ); ?>_recent"><?php _e( 'recent added ', 'nggallery' ); ?></label>
		</p>
		<table>
			<tr>
				<td><label
						for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width:', 'nggallery' ); ?></label>
				</td>
				<td><input style="width: 60px; padding:3px;" id="<?php echo $this->get_field_id( 'width' ); ?>"
				           name="<?php echo $this->get_field_name( 'width' ); ?>" type="number" min="0"
				           value="<?php echo $width; ?>"/> px
				</td>
			</tr>
			<tr>
				<td><label
						for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height:', 'nggallery' ); ?></label>
				</td>
				<td><input style="width: 60px; padding:3px;" id="<?php echo $this->get_field_id( 'height' ); ?>"
				           name="<?php echo $this->get_field_name( 'height' ); ?>" type="number" min="0"
				           value="<?php echo $height; ?>"/> px
				</td>
			</tr>
		</table>
		<p>
			<label for="<?php echo $this->get_field_id( 'exclude' ); ?>"><?php _e( 'Select:', 'nggallery' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'exclude' ); ?>"
			        name="<?php echo $this->get_field_name( 'exclude' ); ?>" class="widefat">
				<option <?php selected( "all", $instance['exclude'] ); ?>
					value="all"><?php _e( 'All galleries', 'nggallery' ); ?></option>
				<option <?php selected( "denied", $instance['exclude'] ); ?>
					value="denied"><?php _e( 'Only which are not listed', 'nggallery' ); ?></option>
				<option <?php selected( "allow", $instance['exclude'] ); ?>
					value="allow"><?php _e( 'Only which are listed', 'nggallery' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'list' ); ?>"><?php _e( 'Gallery ID:', 'nggallery' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'list' ); ?>"
			       name="<?php echo $this->get_field_name( 'list' ); ?>" type="text" class="widefat"
			       value="<?php echo $instance['list']; ?>"/>
			<label class="description"
			       for="<?php echo $this->get_field_id( 'list' ); ?>"><?php _e( 'Gallery IDs, separated by commas.', 'nggallery' ); ?></label>
		</p>

	<?php

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @todo Needs to be better, without the mess to call to the database, but this requires a better database API.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from the database.
	 */
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '&nbsp;' : $instance['title'], $instance, $this->id_base );

		global $wpdb;

		$items    = min( $instance['items'], $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->nggpictures WHERE exclude != 1 " ) );
		$exclude  = $instance['exclude'];
		$list     = $instance['list'];

		$exclude_list = '';

		// THX to Kay Germer for the idea & addon code
		if ( !empty( $list ) && $exclude != 'all' ) {
			$list = explode( ',', $list );
			// Prepare for SQL
			$list = "'" . implode( "', '", $list ) . "'";

			if ( $exclude == 'denied' ) {
				$exclude_list = "AND NOT (t.gid IN ($list))";
			}

			if ( $exclude == 'allow' ) {
				$exclude_list = "AND t.gid IN ($list)";
			}
		}

		if ( $instance['type'] == 'random' ) {
			$imageList = $wpdb->get_results( "SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 $exclude_list ORDER by rand() limit {$items}" );
		} else {
			$imageList = $wpdb->get_results( "SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 $exclude_list ORDER by pid DESC limit 0,$items" );
		}

		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];
		echo "\n" . '<div class="ngg-widget entry-content">' . "\n";

		if ( is_array( $imageList ) ) {
			foreach ( $imageList as $image ) {
				// get the URL constructor
				$image = new nggImage( $image );

				// get the effect code
				$thumbcode = $image->get_thumbcode( $args['widget_id'] );

				// enable i18n support for alttext and description
				$alttext     = htmlspecialchars( stripslashes( nggGallery::i18n( $image->alttext, 'pic_' . $image->pid . '_alttext' ) ) );
				$description = htmlspecialchars( stripslashes( nggGallery::i18n( $image->description, 'pic_' . $image->pid . '_description' ) ) );

				//TODO:For mixed portrait/landscape it's better to use only the height setting, if widht is 0 or vice versa
				$out = '<a href="' . $image->imageURL . '" title="' . $description . '" ' . $thumbcode . '>';

				if ( $instance['show'] == 'original' ) {
					$out .= '<img src="' . trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $image->pid . '&amp;width=' . $instance['width'] . '&amp;height=' . $instance['height'] . '" title="' . $alttext . '" alt="' . $alttext . '" />';
				} else {
					$out .= '<img src="' . $image->thumbURL . '" width="' . $instance['width'] . '" height="' . $instance['height'] . '" title="' . $alttext . '" alt="' . $alttext . '" />';
				}

				echo $out . '</a>' . "\n";

			}
		}

		echo '</div>' . "\n";
		echo $args['after_widget'];

	}
}

add_action('widgets_init',
	create_function('', 'return register_widget("NGG_Gallery_Widget");')
);