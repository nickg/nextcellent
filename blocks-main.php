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
	}

	return ob_get_clean();

}

/**
 * Returns the number of images for the shortcode
 */
function addNumberOfImages($attributes) {
	$str = '';
	if ($attributes['numberOfImages']) {
		$str = ' images=';
		$str .= $attributes['numberOfImages'];
	}

	return $str;

}

/**
 * Handler function for the gallery shortcode
 */
function nextcellent_handle_gallery_block($attributes) {
	global $ngg;

	if ( $attributes['galleryLabel']) {
		$id = array_map('trim', explode('-', $attributes['galleryLabel']))[0];


		$str = '[nggallery id=';

		$str .= $id;

		$str .= addNumberOfImages($attributes);

		$str .= ']';

		echo do_shortcode($str);
	}
}






