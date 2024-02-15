<?php
/**
 * Requires at least: 5.6
 * Requires PHP:      7.0
 * Author:            Kavboy
 *
 * @since   1.9.35
 * @package           create-block
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add block category for Nextcellent blocks.
 *
 * @param array  $categories the array of block categories.
 * @param object $post the post object.
 */
function nextcellent_blocks_block_category( $categories, $post ) {
	return array_merge(
		array(
			array(
				'slug'  => 'nextcellent-blocks',
				'title' => __( 'Nextcellent Gallery Blocks', 'nggallery' ),
			),
		),
		$categories
	);
}

/**
 * Add old categories filter if needed.
 */
function nextcellent_blocks_check_for_old_wp_block_category_filter() {
	if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
		add_filter( 'block_categories', 'nextcellent_blocks_block_category', 10, 2 );
	}
}
add_action( 'init', 'nextcellent_blocks_check_for_old_wp_block_category_filter' );
/**
 * Add block category for Nextcellent blocks.
 *
 * @param array  $categories the array of block categories.
 * @param WP_Block_Editor_Context $block_editor_context The current block editor context.
 */
function nextcellent_blocks_block_category_all( $categories, $block_editor_context ) {
	return array_merge(
		array(
			array(
				'slug'  => 'nextcellent-blocks',
				'title' => __( 'Nextcellent Gallery Blocks', 'nggallery' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'nextcellent_blocks_block_category_all', 10, 2 );

/**
 * Initialise the blocks
 */
function nextcellent_blocks_init() {

	$dir = plugin_dir_path( __FILE__ ) . '/block-editor/blocks';
	$handle = opendir($dir);
	while($name = readdir($handle)) {
		if(is_dir("$dir/$name")) {
			if($name != '.' && $name != '..') {


				$asset_file = include( plugin_dir_path( __FILE__ ) . "public/blocks/$name/$name.asset.php");

				$scriptName = "$name-editor-script";

				wp_register_script(
					$scriptName,
					plugins_url( "public/blocks/$name/$name.js", __FILE__ ),
					$asset_file['dependencies'],
					$asset_file['version']
				);

				wp_localize_script(
					$scriptName,
					'nggData',
					[
						'siteUrl'       => get_site_url(),
						'pluginUrl'		=> NGGALLERY_URLPATH,
					]
				);

				register_block_type("$dir/$name", array('editor_script' => $scriptName ,'render_callback' => 'nextcellent_render_block'));

				wp_set_script_translations( $scriptName, 'nggallery', plugin_dir_path( __FILE__ ) . 'block-editor/lang/' );

			}
		}
	}
	closedir($handle);
}
add_action( 'init', 'nextcellent_blocks_init' );

/**
 * Enqueue nextcellent styles for the blocks editor and editor styles
 */
function nextcellent_block_plugin_editor_scripts() {
	global $ngg;

	$dir = plugin_dir_path( __FILE__ ) . '/block-editor/blocks';
	$handle = opendir($dir);
	while($name = readdir($handle)) {
		if(is_dir("$dir/$name")) {
			if($name != '.' && $name != '..') {
				wp_enqueue_style(
					'editor-css',
					plugins_url( "/public/blocks/$name/$name.css" , __FILE__ ),
					[ 'wp-components', 'wp-edit-blocks' ],
					filemtime( plugin_dir_path( __FILE__ ) . "/public/blocks/$name/$name.css" )
				);
			}
		}
	}
	closedir($handle);

	$stylesheet = $ngg->options['CSSfile'];

	$temp = explode('/', $stylesheet);

	$stylename = $temp[count($temp) - 1];

    // Enqueue block editor styles
    wp_enqueue_style(
        'nextcellent-custom-css',
        plugins_url( '/css/' . $stylename, __FILE__ ),
        ['wp-components', 'wp-edit-blocks' ],
        filemtime( plugin_dir_path( __FILE__ ) . '/css/' . $stylename )
    );

}

// Hook the enqueue functions into the editor
add_action( 'enqueue_block_editor_assets', 'nextcellent_block_plugin_editor_scripts' );



/**
 * Callback function for the blocks
 */
function nextcellent_render_block($attributes, $content, $block) {

	$blockName = $block->parsed_block['blockName'];



	ob_start();

	if ( $blockName == 'nggallery/gallery-block') {
		nextcellent_handle_gallery_block($attributes);
	} else if ($blockName == 'nggallery/single-image-block') {
		nextcellent_handle_single_image_block($attributes);
	} else if ($blockName == 'nggallery/image-browser-block') {
		nextcellent_handle_image_browser_block($attributes);
	} else if ($blockName == 'nggallery/slideshow-block') {
		nextcellent_handle_slideshow_block($attributes);
	} else if ($blockName == 'nggallery/album-block') {
		nextcellent_handle_album_block($attributes);
	} else if ($blockName == 'nggallery/recent-images-block') {
		nextcellent_handle_recent_images_block($attributes);
	} else if ($blockName == 'nggallery/random-images-block') {
		nextcellent_handle_random_images_block($attributes);
	}

	return ob_get_clean();

}

/**
 * Returns the id number from a given label string
 */
function getId($string) {
	$str = '';
	if ($string) {
		$str = ' id=';
		$str .= array_map('trim', explode('-', $string))[0];
	}

	return $str;
}

/**
 * Returns the number of images for the shortcode
 */
function getNumberOfImages($attributes) {
	$str = '';
	if (isset($attributes['numberOfImages'])) {
		$str = ' images=';
		$str .= $attributes['numberOfImages'];
	}

	return $str;
}

/**
 * Returns the max number of images for the shortcode
 */
function getMaxNumberOfImages($attributes) {
	$str = '';
	if (isset($attributes['numberOfImages'])) {
		$str = ' max=';
		$str .= $attributes['numberOfImages'];
	}

	return $str;
}

/**
 * Returns the width for the shortcode
 */
function getWidth($attributes) {
	$str = '';
	if (isset($attributes['width']) && $attributes['width'] > 0) {
		$str = ' w=';
		$str .= $attributes['width'];
	}

	return $str;
}

/**
 * Returns the height for the shortcode
 */
function getHeight($attributes) {
	$str = '';
	if (isset($attributes['height']) && $attributes['height'] > 0 ) {
		$str = ' h=';
		$str .= $attributes['height'];
	}

	return $str;
}

/**
 * Returns the mode for the shortcode
 */
function getMode($attributes) {
	$str = '';
	if (isset($attributes['mode'])) {
		$str = ' mode=';
		$str .= $attributes['mode'];
	}

	return $str;
}

/**
 * Returns the float for the shortcode
 */
function getFloat($attributes) {
	$str = '';
	if (isset($attributes['float'])) {
		$str = ' float=';
		$str .= $attributes['float'];
	}

	return $str;
}

/**
 * Returns the link for the shortcode
 */
function getLink($attributes) {
	$str = '';
	if (isset($attributes['link'])) {
		$str = ' link=';
		$str .= $attributes['link'];
	}

	return $str;
}

/**
 * Returns the album template for the shortcode
 */
function getAlbumTemplate($attributes) {
	$str = '';

	if (isset($attributes['albumTemplate'])) {
		$str = ' template=';
		$str .= $attributes['albumTemplate'];
	}

	return $str;
}

/**
 * Returns the gallery template for the shortcode
 */
function getGalleryTemplate($attributes, $type = 'gallery') {
	$str = '';

	if ($type == 'albumGallery' || $type == 'recent') {
		if (isset($attributes['galleryTemplate']) && $attributes['galleryTemplate'] !== 'gallery') {
			$str = ' gallery=';
			$str .= $attributes['galleryTemplate'];
		}
	} else {
		if (isset($attributes['template']) && $attributes['template'] !== 'gallery' && $attributes['template'] !== 'other' ) {
			$str = ' template=';
			$str .= $attributes['template'];
		} else if (isset($attributes['template']) &&  $attributes['template'] == 'other' && isset($attributes['customTemplate']) ) {
			$str = ' template=';
			$str .= $attributes['customTemplate'];
		}
	}

	return $str;
}

/**
 * Handler function for the gallery shortcode
 */
function nextcellent_handle_gallery_block($attributes) {

	if ( isset($attributes['galleryLabel'])) {

		$str = '[nggallery';

		$str .= getId($attributes['galleryLabel']);

		$str .= getNumberOfImages($attributes);

		$str .= getGalleryTemplate($attributes);

		$str .= ']';

		echo do_shortcode($str);
	}
}

/**
 * Handler function for the album shortcode
 */
function nextcellent_handle_album_block($attributes) {

	if ( isset($attributes['albumLabel'])) {

		$str = '[nggalbum';

		$str .= getId($attributes['albumLabel']);

		$str .= getAlbumTemplate($attributes);

		$str .= getGalleryTemplate($attributes, 'albumGallery');

		$str .= ']';

		echo do_shortcode($str);
	}
}

/**
 * Handler function for the image browser shortcode
 */
function nextcellent_handle_image_browser_block($attributes) {

	if ( isset($attributes['galleryLabel'])) {

		$str = '[imagebrowser';

		$str .= getId($attributes['galleryLabel']);

		$str .= ']';

		echo do_shortcode($str);
	}
}

/**
 * Handler function for the slidehow shortcode
 */
function nextcellent_handle_slideshow_block($attributes) {

	if ( isset($attributes['galleryLabel'])) {

		$str = '[slideshow';

		$str .= getId($attributes['galleryLabel']);

		$str .= getWidth($attributes);

		$str .= getHeight($attributes);

		$str .= ']';

		echo do_shortcode($str);
	}
}

/**
 * Handler function for the single picture shortcode
 */
function nextcellent_handle_single_image_block($attributes) {

	if(isset($attributes['imageLabel'])) {
		$str = '[singlepic';

		$str .= getId($attributes['imageLabel']);

		$str .= getWidth($attributes);

		$str .= getHeight($attributes);

		$str .= getFloat($attributes);

		$str .= getMode($attributes);

		$str .= getLink($attributes);

		$str .= ']';

		if(isset($attributes['description'])) {
			$str .= $attributes['description'];
			$str .= '[/singlepic]';
		}

		echo do_shortcode($str);
	}
}

/**
 * Handler function for the recent images shortcode
 */
function nextcellent_handle_recent_images_block($attributes) {

	if ( isset($attributes['numberOfImages'])) {

		$str = '[recent';

		$str .= getMaxNumberOfImages($attributes);

		if (isset($attributes['galleryLabel'])) {
			$str .= getId($attributes['galleryLabel']);
		}

		$str .= getMode($attributes);

		$str .= getGalleryTemplate($attributes, 'recent');

		$str .= ']';

		echo do_shortcode($str);
	}
}

/**
 * Handler function for the random images shortcode
 */
function nextcellent_handle_random_images_block($attributes) {

	if ( isset($attributes['numberOfImages'])) {

		$str = '[random';

		$str .= getMaxNumberOfImages($attributes);

		if (isset($attributes['galleryLabel'])) {
			$str .= getId($attributes['galleryLabel']);
		}

		$str .= getMode($attributes);

		$str .= getGalleryTemplate($attributes, 'recent');

		$str .= ']';

		echo do_shortcode($str);
	}
}






