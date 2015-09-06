<?php

/**
 * The NextCellent Slideshow widget.
 */
class NGG_Slideshow_Widget extends WP_Widget {

	/**
	 * Register the widget.
	 */
	public function __construct() {
		parent::__construct( 'slideshow', __( 'NextCellent Slideshow', 'nggallery' ), array(
			'classname'   => 'widget_slideshow',
			'description' => __( 'Show a NextCellent Gallery Slideshow', 'nggallery' )
		) );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from the database.
	 */
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Slideshow', 'nggallery' ) : $instance['title'], $instance, $this->id_base );

		try {
			$out = nggShowSlideshow( $instance['galleryid'], $instance );
		} catch ( NGG_Not_Found $e ) {
			$out = $e->getMessage();
		}

		if ( ! empty( $out ) ) {
			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			echo '<div class="ngg_slideshow widget">' . $out . '</div>';
			echo $args['after_widget'];
		}

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
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		if ( is_numeric( $new_instance['galleryid'] ) ) {
			$instance['galleryid'] = (int) $new_instance['galleryid'];
		} elseif ( $new_instance['galleryid'] == 'recent' ) {
			$instance['galleryid'] = 'recent';
		} else {
			$instance['galleryid'] = 'random';
		}

		$instance['height'] = (int) $new_instance['height'];
		$instance['width']  = (int) $new_instance['width'];
		$instance['autodim'] = (bool) $new_instance['autodim'];

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
	function form( $instance ) {

		global $ngg;
		$ngg_options = $ngg->options;

		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title'     => __( 'Slideshow', 'nggallery' ),
			'galleryid' => 'random',
			'width'     => $ngg_options['irWidth'],
			'height'    => $ngg_options['irHeight'],
			'class'     => 'ngg-widget-slideshow',
			'nav'       => false,
			'autoplay'  => true,
			'autodim'   => $ngg_options['irAutoDim'],
		) );
		$height   = esc_attr( $instance['height'] );
		$width    = esc_attr( $instance['width'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php esc_attr_e( $instance['title'] ); ?>"/>
		</p>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'galleryid' ); ?>"><?php _e( 'Select a gallery:', 'nggallery' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'galleryid' ); ?>"
			        id="<?php echo $this->get_field_id( 'galleryid' ); ?>" class="widefat">
				<option
					value="random" <?php selected( $instance['galleryid'], 'random' ); ?> ><?php _e( 'Random images', 'nggallery' ); ?></option>
				<option
					value="recent" <?php selected( $instance['galleryid'], 'recent' ); ?> ><?php _e( 'Recent images', 'nggallery' ); ?></option>
				<?php $this->print_gallery_select( $instance['galleryid'] ); ?>
			</select>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'autodim' ); ?>"
			       name="<?php echo $this->get_field_name( 'autodim' ); ?>" type="checkbox"
			       value="true" <?php checked( true, $instance['autodim'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'autodim' ); ?>">
				<?php _e( "Let the slideshow fit in the available space.", 'nggallery' ); ?>
			</label>
			<br><span class="description"><?php _e( "The given width and height are ignored when this is selected.", 'nggallery' ); ?></span>
		</p>
		<table>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width:', 'nggallery' ); ?></label>
				</td>
				<td>
					<input id="<?php echo $this->get_field_id( 'width' ); ?>"
				           name="<?php echo $this->get_field_name( 'width' ); ?>" type="number" min="0"
				           style="padding: 3px; width: 60px;" value="<?php echo $width; ?>"/> px
				</td>
			</tr>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height:', 'nggallery' ); ?></label>
				</td>
				<td>
					<input id="<?php echo $this->get_field_id( 'height' ); ?>"
				           name="<?php echo $this->get_field_name( 'height' ); ?>" type="number" min="0"
				           style="padding: 3px; width: 60px;" value="<?php echo $height; ?>"/> px
				</td>
			</tr>
		</table>
	<?php
	}

	private function print_gallery_select( $gallery_id ) {
		global $nggdb;

		$galleries = $nggdb->find_all_galleries();

		if ( $galleries ) {
			foreach ( $galleries as $gallery ) {
				$out = '<option value="' . $gallery->gid . '" ';
				$out .= selected( $gallery_id, $gallery->gid, false );
				$out .= '>' . esc_attr( $gallery->name ) . '</option>';
				echo $out;
			}
		}
	}

}

add_action('widgets_init',
	create_function('', 'return register_widget("NGG_Slideshow_Widget");')
);