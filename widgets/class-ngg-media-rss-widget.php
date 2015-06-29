<?php

/**
 * The NextCellent Media RSS widget.
 */
class NGG_Media_RSS_Widget extends WP_Widget {

	/**
	 * Register the widget.
	 */
	public function __construct() {
		parent::__construct( 'ngg-mrssw', __( 'NextCellent Media RSS', 'nggallery' ), array(
			'classname'   => 'ngg_mrssw',
			'description' => __( 'Widget that displays a Media RSS links for NextCellent Gallery.', 'nggallery' )
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

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '&nbsp;' : $instance['title'], $instance, $this->id_base );

		$out = $args['before_widget'];
		$out .= $args['before_title'] . $title . $args['after_title'];
		$out .= "<div class='ngg-media-rss-widget'>";
		$out .= "<a href='" . nggMediaRss::get_mrss_url() . "' title='" . $instance['mrss_title'] . "' class='ngg-media-rss-link'>";
		if ( $instance['show_icon'] ) {
			$out .= '<span class="dashicons dashicons-rss" style="padding-right: 1.5em"></span>';
		}
		if ( $instance['show_global_mrss'] ) {
			$out .= $instance['mrss_text'];
		}
		$out .= "</a></div>";
		$out .= $args['after_widget'];
		echo $out;
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

		$instance['title']            = sanitize_text_field( $new_instance['title'] );
		$instance['show_global_mrss'] = (bool) $new_instance['show_global_mrss'];
		$instance['show_icon']        = (bool) $new_instance['show_icon'];
		$instance['mrss_text']        = sanitize_text_field( $new_instance['mrss_text'] );
		$instance['mrss_title']       = sanitize_text_field( $new_instance['mrss_title'] );

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

		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title'            => __( 'Media RSS', 'nggallery' ),
			'show_global_mrss' => true,
			'mrss_text'        => __( 'Media RSS', 'nggallery' ),
			'mrss_title'       => __( 'Link to the main image feed', 'nggallery' ),
			'show_icon'        => true
		) );

		// The widget form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'nggallery' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php esc_attr_e( $instance['title'] ); ?>">
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_icon' ); ?>"
			       name="<?php echo $this->get_field_name( 'show_icon' ); ?>"
			       type="checkbox" value="true" <?php checked( true, $instance['show_icon'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_icon' ); ?>">
				<?php _e( 'Show the RSS icon', 'nggallery' ); ?>
			</label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_global_mrss' ); ?>"
			       name="<?php echo $this->get_field_name( 'show_global_mrss' ); ?>" type="checkbox"
			       value="true" <?php checked( true, $instance['show_global_mrss'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_global_mrss' ); ?>">
				<?php _e( 'Show the link text', 'nggallery' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'mrss_text' ); ?>">
				<?php _e( 'Text for Media RSS link', 'nggallery' ); ?>:
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'mrss_text' ); ?>"
			       name="<?php echo $this->get_field_name( 'mrss_text' ); ?>" type="text"
			       value="<?php esc_attr_e( $instance['mrss_text'] ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'mrss_title' ); ?>">
				<?php _e( 'Tooltip text for Media RSS link', 'nggallery' ); ?>:
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'mrss_title' ); ?>"
			       name="<?php echo $this->get_field_name( 'mrss_title' ); ?>" type="text"
			       value="<?php esc_attr_e( $instance['mrss_title'] ); ?>">
		</p>

	<?php
	}
}

add_action('widgets_init',
	create_function('', 'return register_widget("NGG_Media_RSS_Widget");')
);