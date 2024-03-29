<?php
/**
 * @author Alex Rabe, Vincent Prat
 *
 * @since 1.0.0
 * @description Use WordPress Shortcode API for more features
 * @Docs http://codex.wordpress.org/Shortcode_API
 */

class NextGEN_shortcodes {

	function __construct() {
		//Long posts should require a higher limit, see http://core.trac.wordpress.org/ticket/8553
		@ini_set( 'pcre.backtrack_limit', 500000 );

		// convert the old shortcode
		add_filter( 'the_content', array(&$this, 'convert_shortcode' ) );
		add_filter( 'loop_start', array(&$this, 'reset_globals' ) );

		// do_shortcode on the_excerpt could causes several unwanted output. Uncomment it on your own risk
		// add_filter('the_excerpt', array(&$this, 'convert_shortcode'));
		// add_filter('the_excerpt', 'do_shortcode', 11);

		add_shortcode( 'singlepic', array(&$this, 'show_singlepic' ) );
		add_shortcode( 'album', array(&$this, 'show_album' ) );
		add_shortcode( 'nggalbum', array(&$this, 'show_album' ) );
		add_shortcode( 'nggallery', array(&$this, 'show_gallery' ) );
		add_shortcode( 'imagebrowser', array(&$this, 'show_imagebrowser' ) );
		add_shortcode( 'slideshow', array(&$this, 'show_slideshow' ) );
		add_shortcode( 'nggtags', array(&$this, 'show_tags' ) );
		add_shortcode( 'thumb', array(&$this, 'show_thumbs' ) );
		add_shortcode( 'random', array(&$this, 'show_random' ) );
		add_shortcode( 'recent', array(&$this, 'show_recent' ) );
		add_shortcode( 'tagcloud', array(&$this, 'show_tagcloud' ) );
	}

	function reset_globals() {
		unset( $GLOBALS['subalbum'] );
		unset( $GLOBALS['nggShowGallery'] );
	}

	/**
	 * NextGEN_shortcodes::convert_shortcode()
	 * convert old shortcodes to the new WordPress core style
	 * [gallery=1]  ->> [nggallery id=1]
	 *
	 * @param string $content Content to search for shortcodes
	 * @return string Content with new shortcodes.
	 */
	static function convert_shortcode( $content ) {

		$ngg_options = nggGallery::get_option( 'ngg_options' );

		if ( stristr( $content, '[singlepic' ) ) {
			$search = "@\[singlepic=(\d+)(|,\d+|,)(|,\d+|,)(|,watermark|,web20|,)(|,right|,center|,left|,)\]@i";
			if ( preg_match_all( $search, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {
					// remove the comma
					$match[2] = ltrim( $match[2], ',' );
					$match[3] = ltrim( $match[3], ',' );
					$match[4] = ltrim( $match[4], ',' );
					$match[5] = ltrim( $match[5], ',' );
					$replace = "[singlepic id=\"{$match[1]}\" w=\"{$match[2]}\" h=\"{$match[3]}\" mode=\"{$match[4]}\" float=\"{$match[5]}\" ]";
					$content = str_replace( $match[0], $replace, $content );
				}
			}
		}

		if ( stristr( $content, '[album' ) ) {
			$search = "@(?:<p>)*\s*\[album\s*=\s*(\w+|^\+)(|,extend|,compact)\]\s*(?:</p>)*@i";
			if ( preg_match_all( $search, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {
					// remove the comma
					$match[2] = ltrim( $match[2], ',' );
					$replace = "[album id=\"{$match[1]}\" template=\"{$match[2]}\"]";
					$content = str_replace( $match[0], $replace, $content );
				}
			}
		}

		if ( stristr( $content, '[gallery' ) ) {
			$search = "@(?:<p>)*\s*\[gallery\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
			if ( preg_match_all( $search, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {
					$replace = "[nggallery id=\"{$match[1]}\"]";
					$content = str_replace( $match[0], $replace, $content );
				}
			}
		}

		if ( stristr( $content, '[imagebrowser' ) ) {
			$search = "@(?:<p>)*\s*\[imagebrowser\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
			if ( preg_match_all( $search, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {
					$replace = "[imagebrowser id=\"{$match[1]}\"]";
					$content = str_replace( $match[0], $replace, $content );
				}
			}
		}

		if ( stristr( $content, '[slideshow' ) ) {
			$search = "@(?:<p>)*\s*\[slideshow\s*=\s*(\w+|^\+)(|,(\d+)|,)(|,(\d+))\]\s*(?:</p>)*@i";
			if ( preg_match_all( $search, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {
					// remove the comma
					$match[3] = ltrim( $match[3], ',' );
					$match[5] = ltrim( $match[5], ',' );
					$replace = "[slideshow id=\"{$match[1]}\" w=\"{$match[3]}\" h=\"{$match[5]}\"]";
					$content = str_replace( $match[0], $replace, $content );
				}
			}
		}

		if ( stristr( $content, '[tags' ) ) {
			$search = "@(?:<p>)*\s*\[tags\s*=\s*(.*?)\s*\]\s*(?:</p>)*@i";
			if ( preg_match_all( $search, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {
					//$replace = "[nggtags gallery=\"{$match[1]}\"]";
					$replace = "[nggtags gallery=\"{$match[1]}\" template=\"{$match[2]}\"]";
					$content = str_replace( $match[0], $replace, $content );
				}
			}
		}

		if ( stristr( $content, '[albumtags' ) ) {
			$search = "@(?:<p>)*\s*\[albumtags\s*=\s*(.*?)\s*\]\s*(?:</p>)*@i";
			if ( preg_match_all( $search, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {
					$replace = "[nggtags album=\"{$match[1]}\"]";
					$content = str_replace( $match[0], $replace, $content );
				}
			}
		}

		// attach related images based on category or tags
		if ( $ngg_options['activateTags'] )
			$content .= nggShowRelatedImages();

		return $content;
	}

	/**
	 * Function to show a single picture:
	 *
	 *     [singlepic id="10" float="none|left|right" width="" height="" mode="none|watermark|web20" link="url" "template="filename" /]
	 *
	 * where
	 *  - id is one picture id
	 *  - float is the CSS float property to apply to the thumbnail
	 *  - width is width of the single picture you want to show (original width if this parameter is missing)
	 *  - height is height of the single picture you want to show (original height if this parameter is missing)
	 *  - mode is one of none, watermark or web20 (transformation applied to the picture)
	 *  - link is optional and could link to a other url instead the full image
	 *  - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
	 *
	 * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
	 *      [singlepic id="10"]This is an additional caption[/singlepic]
	 * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture
	 * and the additional caption specified in the tag.
	 *
	 * @param array $atts
	 * @param string $caption text
	 * @return string the content
	 */
	function show_singlepic( $atts, $content = '' ) {

		extract( shortcode_atts( array(
			'id' => 0,
			'w' => '',
			'h' => '',
			'mode' => '',
			'float' => '',
			'link' => '',
			'template' => ''
		), $atts ) );

		$out = nggSinglePicture( $id, $w, $h, $mode, $float, $template, $content, $link );

		return $out;
	}

	/**
	 * Function to show a collection of galleries:
	 *
	 * [album id="1,2,4,5,..." template="filename" gallery="filename" /]
	 * where
	 * - id of a album
	 * - template is a name for a album template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
	 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
	 *
	 * @param array $atts
	 * @return string the content
	 */
	function show_album( $atts ) {

		extract( shortcode_atts( array(
			'id' => 0,
			'template' => 'extend',
			'gallery' => ''
		), $atts ) );

		$out = nggShowAlbum( $id, $template, $gallery );

		return $out;
	}
	/**
	 * Function to show a thumbnail or a set of thumbnails with shortcode of type:
	 *
	 * [gallery id="1,2,4,5,..." template="filename" images="number of images per page" /]
	 * where
	 * - id of a gallery
	 * - images is the number of images per page (optional), 0 will show all images
	 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
	 *
	 * @param array $atts
	 * @return string the content
	 */
	function show_gallery( $atts ) {

		global $wpdb;

		extract( shortcode_atts( array(
			'id' => 0,
			'template' => '',
			'images' => false
		), $atts ) );

		// backward compat for user which uses the name instead, still deprecated
		if ( ! is_numeric( $id ) )
			$id = $wpdb->get_var( $wpdb->prepare( "SELECT gid FROM $wpdb->nggallery WHERE name = '%s' ", $id ) );

		$out = nggShowGallery( $id, $template, $images );

		return $out;
	}

	function show_imagebrowser( $atts ) {

		global $wpdb;

		extract( shortcode_atts( array(
			'id' => 0,
			'template' => ''
		), $atts ) );

		$out = nggShowImageBrowser( $id, $template );

		return $out;
	}

	/**
	 * Render a slideshow.
	 *
	 * @since 1.9.25 Don't use extract anymore. @see https://core.trac.wordpress.org/ticket/22400
	 * @param $atts array The shortcode attributes.
	 *
	 * @return string The output that will be displayed on the page.
	 */
	function show_slideshow( $atts ) {

		$data = shortcode_atts( array(
			'id' => 'random',
			'w' => null,
			'h' => null,
			'dots' => null
		), $atts );

		array_map( 'esc_attr', $data );

		if ( isset( $data['w'] ) || isset( $data['h'] ) ) {
			$data['autodim'] = false;
			$data['width'] = $data['w'];
			$data['height'] = $data['h'];
		} else {
			unset( $data['w'], $data['h'] );
		}

		if ( $data['dots'] == null ) {
			unset( $data['dots'] );
		} else {
			$data['nav_dots'] = $data['dots'];
		}
		try {
			return nggShowSlideshow( $data['id'], $data );
		} catch (NGG_Not_Found $e) {
			return $e->getMessage();
		}
	}

	/**
	 * nggtags shortcode implementation
	 * 20140120: Improved: template option.
	 * Reference: based on improvement of Tony Howden's code
	 * http://howden.net.au/thowden/2012/12/nextgen-gallery-wordpress-nggtags-template-caption-option/
	 * Included template to galleries and albums
	 * Included sorting mode: ASC/DESC/RAND
	 * @param $atts
	 * @return $out
	 */
	function show_tags( $atts ) {

		extract( shortcode_atts( array(
			'gallery' => '',
			'album' => '',
			'template' => '',
			'sort' => ''
		), $atts ) );

		//gallery/album contains tag list comma separated of terms to filtering out.
		//Counterintuitive: I'd like something like tags='red,green' and then to specify album/gallery instead.
		$modes = array( 'ASC', 'DESC', 'RAND' );

		$sorting = strtoupper( $sort );

		if ( ! in_array( strtoupper( $sorting ), $modes ) ) {
			$sorting = 'NOTSET';
		}

		if ( ! empty( $album ) )
			$out = nggShowAlbumTags( $album, $template, $sorting );
		else
			$out = nggShowGalleryTags( $gallery, $template, $sorting );
		return $out;
	}

	/**
	 * Function to show a thumbnail or a set of thumbnails with shortcode of type:
	 *
	 * [thumb id="1,2,4,5,..." template="filename" /]
	 * where
	 * - id is one or more picture ids
	 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
	 *
	 * @param array $atts
	 * @return string the content
	 */
	function show_thumbs( $atts ) {

		extract( shortcode_atts( array(
			'id' => '',
			'template' => ''
		), $atts ) );

		// make an array out of the ids
		$pids = explode( ',', $id );

		// Some error checks
		if ( count( $pids ) == 0 )
			return __( '[Pictures not found]', 'nggallery' );

		$picturelist = nggdb::find_images_in_list( $pids );

		// show gallery
		if ( is_array( $picturelist ) )
			$out = nggCreateGallery( $picturelist, false, $template );
		return $out;
	}

	/**
	 * Function to show a gallery of random or the most recent images with shortcode of type:
	 *
	 * [random max="7" template="filename" id="2" /]
	 * [recent max="7" template="filename" id="3" mode="date" /]
	 * where
	 * - max is the maximum number of random or recent images to show
	 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
	 * - id is the gallery id, if the recent/random pictures shall be taken from a specific gallery only
	 * - mode is either "id" (which takes the latest additions to the databse, default)
	 *               or "date" (which takes the latest pictures by EXIF date)
	 *               or "sort" (which takes the pictures by user sort order)
	 *
	 * @param array $atts
	 * @return string the content
	 */
	function show_random( $atts ) {

		extract( shortcode_atts( array(
			'max' => '',
			'template' => '',
			'id' => 0
		), $atts ) );

		$out = nggShowRandomRecent( 'random', $max, $template, $id );

		return $out;
	}

	function show_recent( $atts ) {

		extract( shortcode_atts( array(
			'max' => '',
			'template' => '',
			'id' => 0,
			'mode' => 'id'
		), $atts ) );

		$out = nggShowRandomRecent( $mode, $max, $template, $id );

		return $out;
	}

	/**
	 * Shortcode for the Image tag cloud
	 * Usage : [tagcloud template="filename" /]
	 *
	 * @param array $atts
	 * @return string the content
	 */
	function show_tagcloud( $atts ) {

		extract( shortcode_atts( array(
			'template' => ''
		), $atts ) );

		$out = nggTagCloud( '', $template );

		return $out;
	}

}

// let's use it
$nggShortcodes = new NextGEN_Shortcodes;

?>