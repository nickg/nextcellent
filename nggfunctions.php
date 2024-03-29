<?php

class NGG_Not_Found extends Exception {

}

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Return a slideshow.
 *
 * @param int $galleryID ID of the gallery.
 * @param array $args (optional) An array of options.
 *
 * @return string The HTML code for the slideshow.
 * @throws NGG_Not_Found
 */
function nggShowSlideshow( $galleryID, $args = null ) {

	global $slideCounter, $nggdb, $ngg;
	$ngg_options = $ngg->options;

	// remove media file from RSS feed
	if ( is_feed() ) {
		$out = '[' . nggGallery::i18n( $ngg_options['galTextSlide'] ) . ']';
		return $out;
	}

	// we need to know the current page id
	if ( ! get_the_ID() ) {
		$current_page = rand( 5, 15 );
	} else {
		$current_page = get_the_ID();
	}
	// look for a other slideshow instance
	if ( ! isset( $slideCounter ) )
		$slideCounter = 0;

	// create unique anchor
	$anchor = 'ngg_slideshow' . $galleryID . $current_page . $slideCounter++;

	$param = wp_parse_args( $args, array(
		'width' => $ngg_options['irWidth'],
		'height' => $ngg_options['irHeight'],
		'class' => 'ngg-slideshow',
		'anchor' => 'ngg_slideshow' . $galleryID . $current_page . $slideCounter++,
		'time' => $ngg_options['irRotatetime'] * 1000,
		'loop' => $ngg_options['irLoop'],
		'drag' => $ngg_options['irDrag'],
		'nav' => $ngg_options['irNavigation'],
		'nav_dots' => $ngg_options['irNavigationDots'],
		'autoplay' => $ngg_options['irAutoplay'],
		'hover' => $ngg_options['irAutoplayHover'],
		'effect' => $ngg_options['slideFx'],
		'click' => $ngg_options['irClick'],
		'autodim' => $ngg_options['irAutoDim'],
		'number' => $ngg_options['irNumber']
	) );

	/**
	 * Edit the args for a NextCellent slideshow.
	 *
	 * @since 1.9.25beta2
	 *
	 * @param array $args {
	 *     All the arguments.
	 *
	 *     @var int $width The width of the slideshow. Will be ignored if $autodim is true.
	 *     @var int $height The height of the slideshow. Will be ignored if $autodim is set.
	 *     @var string $class The class that will be assigned to the div containing the slideshow.
	 *     @var string $anchor The id that will be assigned to the div containing the slideshow.
	 *     @var int $time The duration of a slide in milliseconds.
	 *     @var bool $loop If the slideshow should loop.
	 *     @var bool $drag If the user can drag through the images.
	 *     @var bool $nav If the navigation elements (next/previous) should be shown.
	 *     @var bool $nav_dots If the navigation dots should be shown.
	 *     @var bool $autoplay If the slideshow should automatically start.
	 *     @var bool $hover If the slideshow should pause when hovering over it.
	 *     @var string $effect With which effect the slideshow should use.
	 *     @var bool $click If the slideshow should go to the next image on click.
	 *     @var bool $autodim If the slideshow should automatically fit (responsive). When true, this will
	 *                        ignore the $width and $height.
	 *     @var int $number The number of images that should be displayed. Only works when the gallery ID
	 *                      is set to random or recent.
	 * }
	 */
	$param = apply_filters( 'ngg_slideshow_args', $param );

	//Get the images
	if ( $galleryID == 'random' ) {
		$images = nggdb::get_random_images( $param['number'] ); //random images
	} elseif ( $galleryID == 'recent' ) {
		$images = nggdb::find_last_images( 0, $param['number'] ); //the last images
	} else {
		$images = $nggdb->get_gallery( $galleryID ); //a gallery
	}

	if ( empty( $images ) ) {
		throw new NGG_Not_Found( __( "The gallery was not found.", 'nggallery' ) );
	}

	$out = '<div class="slideshow"><div id="' . $param['anchor'] . '" class="' . $param['class'] . ' owl-carousel owl-theme" ';

	if ( ! $param['autodim'] ) {
		$out .= 'style="max-width: ' . $param['width'] . 'px; max-height: ' . $param['height'] . 'px;"';
	}
	$out .= '>';

	foreach ( $images as $image ) {
		if ( ! $param['autodim'] ) {
			$out .= '<img src="' . $image->imageURL . '" alt="' . $image->alttext . '" style="max-width: ' . $param['width'] . 'px; max-height: ' . $param['height'] . 'px; width: auto; height:auto; margin:auto">';
		} else {
			$out .= '<img src="' . $image->imageURL . '" alt="' . $image->alttext . '" >';
		}
	}

	$out .= '</div></div>' . "\n";
	$out .= "\n" . '<script type="text/javascript">';
	$out .= "jQuery(document).ready(function($) {
			var owl = $('#" . $param['anchor'] . "');
			owl.owlCarousel({
				items: 1,
				autoHeight: " . var_export( $param['autodim'], true ) . ",";
	if ( $param['nav'] ) {
		$out .= "nav: true,
				navText: ['" . __( 'previous', 'nggallery' ) . "','" . __( 'next', 'nggallery' ) . "'],";
	}
	$out .= "
	            dots: " . var_export( $param['nav_dots'], true ) . ",
	            autoplay: " . var_export( $param['autoplay'], true ) . ",
				margin: 10,
				autoplayTimeout: " . $param['time'] . ",
				autoplayHoverPause: " . var_export( $param['hover'], true ) . ",
				animateIn: '" . $param['effect'] . "',
				animateOut: 'fadeOut',
				loop: " . var_export( $param['loop'], true ) . ",
				mouseDrag: " . var_export( $param['drag'], true ) . ",
				touchDrag: " . var_export( $param['drag'], true ) . "
			});";
	if ( $param['click'] ) {
		$out .= "\n" . "$('.owl-item').click( function () {
                    owl.trigger( 'next.owl.carousel' );
                } );";
	}
	$out .= "});";

	$out .= "\n" . '</script>';

	return $out;
}

/**
 * nggShowGallery() - return a gallery
 *
 * @access public
 * @param int|string ID or slug from a gallery
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @param int $images (optional) number of images per page
 * @return string content
 */
function nggShowGallery( $galleryID, $template = '', $images = false ) {

	global $nggRewrite, $nggdb;

	$ngg_options = nggGallery::get_option( 'ngg_options' );

	//Set sort order value, if not used (upgrade issue)
	$ngg_options['galSort'] = ( $ngg_options['galSort'] ) ? $ngg_options['galSort'] : 'pid';
	$ngg_options['galSortDir'] = ( $ngg_options['galSortDir'] == 'DESC' ) ? 'DESC' : 'ASC';

	// get gallery values
	//TODO: Use pagination limits here to reduce memory needs
	//20130106:shouldn't call it statically if is not...
	//$picturelist = nggdb::get_gallery($galleryID, $ngg_options['galSort'], $ngg_options['galSortDir']);
	//array of nggImage objects returned
	$picturelist = $nggdb->get_gallery( $galleryID, $ngg_options['galSort'], $ngg_options['galSortDir'] );

	if ( ! $picturelist )
		return __( '[Gallery not found]', 'nggallery' );

	// If we have we slug instead the id, we should extract the ID from the first image
	if ( ! is_numeric( $galleryID ) ) {
		$pictureIterator = ( new ArrayObject( $picturelist ) )->getIterator();
		$first_image = $pictureIterator->current();
		$galleryID = intval( $first_image->gid );
	}

	// $_GET from wp_query
	$show = get_query_var( 'show' );
	$pid = get_query_var( 'pid' );
	$pageid = get_query_var( 'pageid' );

	// set $show if slideshow first
	if ( empty( $show ) and ( $ngg_options['galShowOrder'] == 'slide' ) ) {
		if ( is_home() )
			$pageid = get_the_ID();

		$show = 'slide';
	}

	// filter to call up the imagebrowser instead of the gallery
	// use in your theme : add_action( 'ngg_show_imagebrowser_first', function () { return true; } );
	if ( apply_filters( 'ngg_show_imagebrowser_first', false, $galleryID ) && $show != 'thumbnails' ) {
		$out = nggShowImageBrowser( $galleryID, $template );
		return $out;
	}

	// go on only on this page
	if ( ! is_home() || $pageid == get_the_ID() ) {

		// 1st look for ImageBrowser link
		if ( ! empty( $pid ) && $ngg_options['galImgBrowser'] && ( $template != 'carousel' ) ) {
			$out = nggShowImageBrowser( $galleryID, $template );
			return $out;
		}

		// 2nd look for slideshow
		if ( $show == 'slide' ) {
			$args['show'] = "gallery";
			$out = '<div class="ngg-galleryoverview">';
			$out .= '<div class="slideshowlink"><a class="slideshowlink" href="' . $nggRewrite->get_permalink( $args ) . '">' . nggGallery::i18n( $ngg_options['galTextGallery'] ) . '</a></div>';
			$out .= nggShowSlideshow( $galleryID );
			$out .= '</div>' . "\n";
			$out .= '<div class="ngg-clear"></div>' . "\n";
			return $out;
		}
	}

	// get all picture with this galleryid
	if ( is_array( $picturelist ) )
		$out = nggCreateGallery( $picturelist, $galleryID, $template, $images );

	$out = apply_filters( 'ngg_show_gallery_content', $out, intval( $galleryID ) );
	return $out;
}

/**
 * Build a gallery output
 *
 * @access internal
 * @param nggImage[] $picturelist
 * @param bool $galleryID, if you supply a gallery ID, you can add a slideshow link
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @param int $images (optional) number of images per page
 * @return string content
 */
function nggCreateGallery( array $picturelist, $galleryID = false, $template = '', $images = false ) {
	global $nggRewrite;

	require_once ( dirname( __FILE__ ) . '/lib/media-rss.php' );

	$ngg_options = nggGallery::get_option( 'ngg_options' );

	//the shortcode parameter will override global settings, TODO: rewrite this to a class
	$ngg_options['galImages'] = ( $images === false ) ? $ngg_options['galImages'] : (int) $images;

	$current_pid = false;

	// $_GET from wp_query
	$nggpage = get_query_var( 'nggpage' );
	$pageid = get_query_var( 'pageid' );
	$pid = get_query_var( 'pid' );

	// in case of permalinks the pid is a slug, we need the id
	if ( ! is_numeric( $pid ) && ! empty( $pid ) ) {
		$picture = nggdb::find_image( $pid );
		$pid = $picture->pid;
	}

	// we need to know the current page id
	$current_page = ( get_the_ID() == false ) ? 0 : get_the_ID();

	if ( ! is_array( $picturelist ) )
		$picturelist = array( $picturelist );

	$pictureIterator = ( new ArrayObject( $picturelist ) )->getIterator();

	// Populate galleries values from the first image
	$first_image = $pictureIterator->current();
	$gallery = new stdclass;
	$gallery->ID = (int) $galleryID;
	$gallery->show_slideshow = false;
	$gallery->show_piclens = false;
	$gallery->name = stripslashes( $first_image->name );
	$gallery->title = stripslashes( $first_image->title );
	$gallery->description = html_entity_decode( stripslashes( $first_image->galdesc ) );
	$gallery->pageid = $first_image->pageid;
	$gallery->anchor = 'ngg-gallery-' . $galleryID . '-' . $current_page;
	$pictureIterator->rewind();

	$maxElement = $ngg_options['galImages'];
	$thumbwidth = $ngg_options['thumbwidth'];
	$thumbheight = $ngg_options['thumbheight'];

	// fixed width if needed
	$gallery->columns = intval( $ngg_options['galColumns'] );
	$gallery->imagewidth = ( $gallery->columns > 0 ) ? 'style="width:' . floor( 100 / $gallery->columns ) . '%;"' : '';

	// obsolete in V1.4.0, but kept for compat reason
	// pre set thumbnail size, from the option, later we look for meta data.
	$thumbsize = ( $ngg_options['thumbfix'] ) ? $thumbsize = 'width="' . $thumbwidth . '" height="' . $thumbheight . '"' : '';

	// show slideshow link
	if ( $galleryID ) {
		if ( $ngg_options['galShowSlide'] ) {
			$gallery->show_slideshow = true;
			$gallery->slideshow_link = $nggRewrite->get_permalink( array( 'show' => 'slide' ) );
			$gallery->slideshow_link_text = nggGallery::i18n( $ngg_options['galTextSlide'] );
		}

		if ( $ngg_options['usePicLens'] ) {
			$gallery->show_piclens = true;
			$gallery->piclens_link = "javascript:PicLensLite.start({feedUrl:'" . htmlspecialchars( nggMediaRss::get_gallery_mrss_url( $gallery->ID ) ) . "'});";
		}
	}

	// check for page navigation
	if ( $maxElement > 0 ) {

		if ( ! is_home() || $pageid == $current_page )
			$page = ( ! empty( $nggpage ) ) ? (int) $nggpage : 1;
		else
			$page = 1;

		$start = $offset = ( $page - 1 ) * $maxElement;

		$total = count( $picturelist );

		//we can work with display:hidden for some javascript effects
		if ( ! $ngg_options['galHiddenImg'] ) {
			// remove the element if we didn't start at the beginning
			if ( $start > 0 )
				array_splice( $picturelist, 0, $start );

			// return the list of images we need
			array_splice( $picturelist, $maxElement );
		}

		$nggNav = new nggNavigation;
		$navigation = $nggNav->create_navigation( $page, $total, $maxElement );
	} else {
		$navigation = '<div class="ngg-clear"></div>';
	}

	//we cannot use the key as index, cause it's filled with the pid
	$index = 0;
	foreach ( $picturelist as $key => $picture ) {

		//needed for hidden images (THX to Sweigold for the main idea at : http://wordpress.org/support/topic/228743/ )
		$picturelist[ $key ]->hidden = false;
		$picturelist[ $key ]->style = $gallery->imagewidth;

		if ( $maxElement > 0 && $ngg_options['galHiddenImg'] ) {
			if ( ( $index < $start ) || ( $index > ( $start + $maxElement - 1 ) ) ) {
				//FZSM Check: dinamically created nggImage doesn't have this properties
				$picturelist[ $key ]->hidden = true;
				$picturelist[ $key ]->style = ( $gallery->columns > 0 ) ? 'style="width:' . floor( 100 / $gallery->columns ) . '%;display: none;"' : 'style="display: none;"';
			}
			$index++;
		}

		// get the effect code
		if ( $galleryID )
			$thumbcode = ( $ngg_options['galImgBrowser'] ) ? '' : $picture->get_thumbcode( 'set_' . $galleryID );
		else
			$thumbcode = ( $ngg_options['galImgBrowser'] ) ? '' : $picture->get_thumbcode( get_the_title() );

		// create link for imagebrowser and other effects
		$args['nggpage'] = empty( $nggpage ) || ( $template != 'carousel' ) ? false : $nggpage;  // only needed for carousel mode
		$args['pid'] = ( $ngg_options['usePermalinks'] ) ? $picture->image_slug : $picture->pid;
		$picturelist[ $key ]->pidlink = $nggRewrite->get_permalink( $args );

		// generate the thumbnail size if the meta data available
		if ( isset( $picturelist[ $key ]->meta_data['thumbnail'] ) && is_array( $size = $picturelist[ $key ]->meta_data['thumbnail'] ) )
			$thumbsize = 'width="' . $size['width'] . '" height="' . $size['height'] . '"';

		// choose link between imagebrowser or effect
		$link = ( $ngg_options['galImgBrowser'] ) ? $picturelist[ $key ]->pidlink : $picture->imageURL;
		// bad solution : for now we need the url always for the carousel, should be reworked in the future
		$picturelist[ $key ]->url = $picture->imageURL;
		// add a filter for the link
		$picturelist[ $key ]->imageURL = apply_filters( 'ngg_create_gallery_link', $link, $picture );
		$picturelist[ $key ]->thumbnailURL = $picture->thumbURL;
		$picturelist[ $key ]->size = $thumbsize;
		$picturelist[ $key ]->thumbcode = $thumbcode;
		$picturelist[ $key ]->caption = ( empty( $picture->description ) ) ? '&nbsp;' : html_entity_decode( stripslashes( nggGallery::i18n( $picture->description, 'pic_' . $picture->pid . '_description' ) ) );
		$picturelist[ $key ]->description = ( empty( $picture->description ) ) ? ' ' : htmlspecialchars( stripslashes( nggGallery::i18n( $picture->description, 'pic_' . $picture->pid . '_description' ) ) );
		$picturelist[ $key ]->alttext = ( empty( $picture->alttext ) ) ? ' ' : htmlspecialchars( stripslashes( nggGallery::i18n( $picture->alttext, 'pic_' . $picture->pid . '_alttext' ) ) );

		// filter to add custom content for the output
		$picturelist[ $key ] = apply_filters( 'ngg_image_object', $picturelist[ $key ], $picture->pid );

		//check if $pid is in the array
		if ( $picture->pid == $pid )
			$current_pid = $picturelist[ $key ];
	}
	$pictureIterator->rewind();

	//for paged galleries, take the first image in the array if it's not in the list
	$current_pid = ( empty( $current_pid ) ) ? $pictureIterator->current() : $current_pid;

	// look for gallery-$template.php or pure gallery.php
	$filename = ( empty( $template ) ) ? 'gallery' : 'gallery-' . $template;

	//filter functions for custom addons
	$gallery = apply_filters( 'ngg_gallery_object', $gallery, $galleryID );
	$picturelist = apply_filters( 'ngg_picturelist_object', $picturelist, $galleryID );

	//additional navigation links
	$next = ( empty( $nggNav->next ) ) ? false : $nggNav->next;
	$prev = ( empty( $nggNav->prev ) ) ? false : $nggNav->prev;

	// create the output
	$out = nggGallery::capture( $filename, array( 'gallery' => $gallery, 'images' => $picturelist, 'pagination' => $navigation, 'current' => $current_pid, 'next' => $next, 'prev' => $prev ) );

	// apply a filter after the output
	$out = apply_filters( 'ngg_gallery_output', $out, $picturelist );

	return $out;
}

/**
 * nggShowAlbum() - return a album based on the id
 *
 * @access public
 * @param int | string $albumID
 * @param string (optional) $template
 * @param string (optional) $gallery_template
 * @return string content
 */
function nggShowAlbum( $albumID, $template = 'extend', $gallery_template = '' ) {

	// $_GET from wp_query
	$gallery = get_query_var( 'gallery' );
	$album = get_query_var( 'album' );

	// in the case somebody uses the '0', it should be 'all' to show all galleries
	$albumID = ( $albumID == 0 ) ? 'all' : $albumID;

	// first look for gallery variable
	if ( ! empty( $gallery ) ) {

		// subalbum support only one instance, you can't use more of them in one post
		//TODO: causes problems with SFC plugin, due to a second filter callback
		global $wp_current_filter;
		if ( isset( $GLOBALS['subalbum'] ) || isset( $GLOBALS['nggShowGallery'] ) )
			return '';

		// if gallery is submit , then show the gallery instead
		$out = nggShowGallery( $gallery, $gallery_template );
		$GLOBALS['nggShowGallery'] = true;

		return $out;
	}

	if ( ( empty( $gallery ) ) && ( isset( $GLOBALS['subalbum'] ) ) )
		return '';

	//redirect to subalbum only one time
	if ( ! empty( $album ) ) {
		$GLOBALS['subalbum'] = true;
		$albumID = $album;
	}

	// lookup in the database
	$album = nggdb::find_album( $albumID );

	// still no success ? , die !
	if ( ! $album )
		return __( '[Album not found]', 'nggallery' );

	// ensure to set the slug for "all" albums
	$album->slug = ( $albumID == 'all' ) ? $album->id : $album->slug;

	if ( is_array( $album->gallery_ids ) )
		$out = nggCreateAlbum( $album->gallery_ids, $template, $album );

	$out = apply_filters( 'ngg_show_album_content', $out, $album->id );

	return $out;
}

/**
 * create a gallery overview output
 *
 * @access internal
 * @param array $galleriesID
 * @param string (optional) $template name for a template file, look for album-$template
 * @param object (optional) $album result from the db
 * @return string content
 */
function nggCreateAlbum( $galleriesID, $template = 'extend', $album = 0 ) {
	global $wpdb, $nggRewrite, $nggdb, $ngg;

	// $_GET from wp_query
	$nggpage = get_query_var( 'nggpage' );

	// Get options
	$ngg_options = $ngg->options;

	//this option can currently only set via the custom fields
	$maxElement = (int) $ngg_options['galPagedGalleries'];

	$sortorder = $galleriesID;
	$galleries = array();

	// get the galleries information
	foreach ( $galleriesID as $i => $value )
		$galleriesID[ $i ] = addslashes( $value );

	$unsort_galleries = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->nggallery . ' WHERE gid IN (\'' . implode( '\',\'', $galleriesID ) . '\')', OBJECT_K );

	//TODO: Check this, problem exist when previewpic = 0
	//$galleries = $wpdb->get_results('SELECT t.*, tt.* FROM '.$wpdb->nggallery.' AS t INNER JOIN '.$wpdb->nggpictures.' AS tt ON t.previewpic = tt.pid WHERE t.gid IN (\''.implode('\',\'', $galleriesID).'\')', OBJECT_K);

	// get the counter values
	$picturesCounter = $wpdb->get_results( 'SELECT galleryid, COUNT(*) as counter FROM ' . $wpdb->nggpictures . ' WHERE galleryid IN (\'' . implode( '\',\'', $galleriesID ) . '\') AND exclude != 1 GROUP BY galleryid', OBJECT_K );
	if ( is_array( $picturesCounter ) ) {
		foreach ( $picturesCounter as $key => $value )
			$unsort_galleries[ $key ]->counter = $value->counter;
	}

	// get the id's of the preview images
	$imagesID = array();
	if ( is_array( $unsort_galleries ) ) {
		foreach ( $unsort_galleries as $gallery_row )
			$imagesID[] = $gallery_row->previewpic;
	}
	$albumPreview = $wpdb->get_results( 'SELECT pid, filename FROM ' . $wpdb->nggpictures . ' WHERE pid IN (\'' . implode( '\',\'', $imagesID ) . '\')', OBJECT_K );

	// re-order them and populate some
	foreach ( $sortorder as $key ) {

		// Create a gallery object
		if ( isset( $unsort_galleries[ $key ] ) )
			$galleries[ $key ] = $unsort_galleries[ $key ];
		else
			$galleries[ $key ] = new stdClass;

		//if we have a prefix 'a' then it's a subalbum, instead a gallery
		if ( substr( $key, 0, 1 ) == 'a' ) {
			if ( ( $subalbum = nggdb::find_album( substr( $key, 1 ) ) ) ) {
				$galleries[ $key ]->counter = count( $subalbum->gallery_ids );
				if ( $subalbum->previewpic > 0 ) {
					$image = $nggdb->find_image( $subalbum->previewpic );
					$galleries[ $key ]->previewurl = isset( $image->thumbURL ) ? $image->thumbURL : '';
				}
				$galleries[ $key ]->previewpic = $subalbum->previewpic;
				$galleries[ $key ]->previewname = $subalbum->name;

				//link to the subalbum
				$args['album'] = ( $ngg_options['usePermalinks'] ) ? $subalbum->slug : $subalbum->id;
				$args['gallery'] = false;
				$args['nggpage'] = false;
				$pageid = ( isset( $subalbum->pageid ) ? $subalbum->pageid : 0 );
				$galleries[ $key ]->pagelink = ( $pageid > 0 ) ? get_permalink( $pageid ) : $nggRewrite->get_permalink( $args );
				$galleries[ $key ]->galdesc = html_entity_decode( nggGallery::i18n( $subalbum->albumdesc ) );
				$galleries[ $key ]->title = esc_attr( html_entity_decode( nggGallery::i18n( $subalbum->name ) ) );
			}
		} elseif ( isset( $unsort_galleries[ $key ] ) ) {
			$galleries[ $key ] = $unsort_galleries[ $key ];

			// No images found, set counter to 0
			if ( ! isset( $galleries[ $key ]->counter ) ) {
				$galleries[ $key ]->counter = 0;
				$galleries[ $key ]->previewurl = '';
			}

			// add the file name and the link
			if ( $galleries[ $key ]->previewpic != 0 ) {
				$galleries[ $key ]->previewname = $albumPreview[ $galleries[ $key ]->previewpic ]->filename;
				$galleries[ $key ]->previewurl = site_url() . '/' . $galleries[ $key ]->path . '/thumbs/thumbs_' . $albumPreview[ $galleries[ $key ]->previewpic ]->filename;
			} else {
				$first_image = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->nggpictures . ' WHERE exclude != 1 AND galleryid = ' . $key . ' ORDER by pid DESC limit 0,1' );
				if ( isset( $first_image ) ) {
					$galleries[ $key ]->previewpic = $first_image->pid;
					$galleries[ $key ]->previewname = $first_image->filename;
					$galleries[ $key ]->previewurl = site_url() . '/' . $galleries[ $key ]->path . '/thumbs/thumbs_' . $first_image->filename;
				}
			}

			// choose between variable and page link
			if ( $ngg_options['galNoPages'] ) {
				$args['album'] = ( $ngg_options['usePermalinks'] ) ? $album->slug : $album->id;
				$args['gallery'] = ( $ngg_options['usePermalinks'] ) ? $galleries[ $key ]->slug : $key;
				$args['nggpage'] = false;
				$galleries[ $key ]->pagelink = $nggRewrite->get_permalink( $args );

			} else {
				$galleries[ $key ]->pagelink = get_permalink( $galleries[ $key ]->pageid );
			}

			// description can contain HTML tags
			$galleries[ $key ]->galdesc = html_entity_decode( nggGallery::i18n( stripslashes( $galleries[ $key ]->galdesc ), 'gal_' . $galleries[ $key ]->gid . '_description' ) );

			// i18n
			$galleries[ $key ]->title = esc_attr( html_entity_decode( nggGallery::i18n( $galleries[ $key ]->title, 'gal_' . $galleries[ $key ]->gid . '_title' ) ) );
		}

		// apply a filter on gallery object before the output
		$galleries[ $key ] = apply_filters( 'ngg_album_galleryobject', $galleries[ $key ] );
	}

	// apply a filter on gallery object before paging starts
	$galleries = apply_filters( 'ngg_album_galleries_before_paging', $galleries, $album );

	// check for page navigation
	if ( $maxElement > 0 ) {
		if ( ! is_home() || $pageid == get_the_ID() ) {
			$page = ( ! empty( $nggpage ) ) ? (int) $nggpage : 1;
		} else
			$page = 1;

		$start = $offset = ( $page - 1 ) * $maxElement;

		$total = count( $galleries );

		// remove the element if we didn't start at the beginning
		if ( $start > 0 )
			array_splice( $galleries, 0, $start );

		// return the list of images we need
		array_splice( $galleries, $maxElement );

		$nggNav = new nggNavigation;
		$navigation = $nggNav->create_navigation( $page, $total, $maxElement );
	} else {
		$navigation = '<div class="ngg-clear"></div>';
	}

	// apply a filter on $galleries before the output
	$galleries = apply_filters( 'ngg_album_galleries', $galleries );

	// if sombody didn't enter any template , take the extend version
	$filename = ( empty( $template ) ) ? 'album-extend' : 'album-' . $template;

	// create the output
	$out = nggGallery::capture( $filename, array( 'album' => $album, 'galleries' => $galleries, 'pagination' => $navigation ) );

	return $out;

}

/**
 * nggShowImageBrowser()
 *
 * @access public
 * @param int|string $galleryID or gallery name
 * @param string $template (optional) name for a template file, look for imagebrowser-$template
 * @return string content
 */
function nggShowImageBrowser( $galleryID, $template = '' ) {

	global $wpdb, $nggdb;

	$ngg_options = nggGallery::get_option( 'ngg_options' );

	//Set sort order value, if not used (upgrade issue)
	$ngg_options['galSort'] = ( $ngg_options['galSort'] ) ? $ngg_options['galSort'] : 'pid';
	$ngg_options['galSortDir'] = ( $ngg_options['galSortDir'] == 'DESC' ) ? 'DESC' : 'ASC';

	// get the pictures
	//20140106:shouldn't call it statically if is not...
	//$picturelist = nggdb::get_gallery($galleryID, $ngg_options['galSort'], $ngg_options['galSortDir']);
	//return array of nggImages
	$picturelist = $nggdb->get_gallery( $galleryID, $ngg_options['galSort'], $ngg_options['galSortDir'] );

	if ( is_array( $picturelist ) )
		$out = nggCreateImageBrowser( $picturelist, $template );
	else
		$out = __( '[Gallery not found]', 'nggallery' );

	$out = apply_filters( 'ngg_show_imagebrowser_content', $out, $galleryID );

	return $out;

}

/**
 * nggCreateImageBrowser()
 *
 * @access internal
 * @param array $picturelist
 * @param string $template (optional) name for a template file, look for imagebrowser-$template
 * @return string content
 */
function nggCreateImageBrowser( $picturelist, $template = '' ) {

	global $nggRewrite, $ngg;

	require_once ( dirname( __FILE__ ) . '/lib/meta.php' );

	// $_GET from wp_query
	$pid = get_query_var( 'pid' );

	// we need to know the current page id
	$current_page = ( get_the_ID() == false ) ? 0 : get_the_ID();

	// create a array with id's for better walk inside
	$picarray = array();
	foreach ( $picturelist as $picture ) {
		$picarray[] = $picture->pid;
	}

	$picarrayIterator = ( new ArrayObject( $picarray ) )->getIterator();

	$total = count( $picarray );

	if ( ! empty( $pid ) ) {
		if ( is_numeric( $pid ) )
			$act_pid = intval( $pid );
		else {
			// in the case it's a slug we need to search for the pid
			foreach ( $picturelist as $key => $picture ) {
				if ( $picture->image_slug == $pid ) {
					$act_pid = $key;
					break;
				}
			}
		}
	} else {
		$picarrayIterator->rewind();
		$act_pid = $picarrayIterator->current();
	}

	// get ids for back/next
	$key = array_search( $act_pid, $picarray );
	if ( ! $key ) {
		$picarrayIterator->rewind();
		$act_pid = $picarrayIterator->current();
		$key = $picarrayIterator->key();
	}

	if ( $key >= 1 ) {
		$back_pid = $picarray[ $key - 1 ];
	} else {
		// set iterator to last element		
		if ( count( $picarray ) - 1 > 0 ) {
			while ( $picarrayIterator->key() < count( $picarray ) - 1 ) {
				$picarrayIterator->next();
			}

			$back_pid = $picarray[ count( $picarray ) - 1 ];


		} else {
			$back_pid = 0;
		}
	}

	if ( $key < ( $total - 1 ) ) {
		$next_pid = $picarray[ $key + 1 ];
	} else {
		$picarrayIterator->rewind();
		$next_pid = $picarrayIterator->current();
	}


	// get the picture data
	$picture = nggdb::find_image( $act_pid );

	// if we didn't get some data, exit now
	if ( $picture == null )
		return '';
	// add more variables for render output
	$picture->href_link = $picture->get_href_link();
	$args['pid'] = ( $ngg->options['usePermalinks'] ) ? $picturelist[ $back_pid ]->image_slug : $back_pid;
	$picture->previous_image_link = $nggRewrite->get_permalink( $args );
	$picture->previous_pid = $back_pid;
	$args['pid'] = ( $ngg->options['usePermalinks'] ) ? $picturelist[ $next_pid ]->image_slug : $next_pid;
	$picture->next_image_link = $nggRewrite->get_permalink( $args );
	$picture->next_pid = $next_pid;
	$picture->number = $key + 1;
	$picture->total = $total;
	$picture->linktitle = ( empty( $picture->description ) ) ? ' ' : htmlspecialchars( stripslashes( nggGallery::i18n( $picture->description, 'pic_' . $picture->pid . '_description' ) ) );
	$picture->alttext = ( empty( $picture->alttext ) ) ? ' ' : html_entity_decode( stripslashes( nggGallery::i18n( $picture->alttext, 'pic_' . $picture->pid . '_alttext' ) ) );
	$picture->description = ( empty( $picture->description ) ) ? ' ' : html_entity_decode( stripslashes( nggGallery::i18n( $picture->description, 'pic_' . $picture->pid . '_description' ) ) );
	$picture->anchor = 'ngg-imagebrowser-' . $picture->galleryid . '-' . $current_page;

	// filter to add custom content for the output
	$picture = apply_filters( 'ngg_image_object', $picture, $act_pid );

	// let's get the meta data
	$meta = new nggMeta( $act_pid );
	$meta->sanitize();
	$exif = $meta->get_EXIF();
	$iptc = $meta->get_IPTC();
	$xmp = $meta->get_XMP();
	$db = $meta->get_saved_meta();

	//if we get no exif information we try the database
	$exif = ( $exif == false ) ? $db : $exif;

	// look for imagebrowser-$template.php or pure imagebrowser.php
	$filename = ( empty( $template ) ) ? 'imagebrowser' : 'imagebrowser-' . $template;

	// create the output
	$out = nggGallery::capture( $filename, array( 'image' => $picture, 'meta' => $meta, 'exif' => $exif, 'iptc' => $iptc, 'xmp' => $xmp, 'db' => $db ) );

	return $out;

}

/**
 * nggSinglePicture() - show a single picture based on the id
 *
 * @access public
 * @param int $imageID, db-ID of the image
 * @param int (optional) $width, width of the image
 * @param int (optional) $height, height of the image
 * @param string $mode (optional) could be none, watermark, web20
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for singlepic-$template
 * @param string $caption (optional) additional caption text
 * @param string $link (optional) link to a other url instead the full image
 * @return string content
 */
function nggSinglePicture( $imageID, $width = 250, $height = 250, $mode = '', $float = '', $template = '', $caption = '', $link = '' ) {
	global $post;

	$ngg_options = nggGallery::get_option( 'ngg_options' );

	// get picturedata
	$picture = nggdb::find_image( $imageID );

	// if we didn't get some data, exit now
	if ( $picture == null )
		return __( '[SinglePic not found]', 'nggallery' );

	// add float to img
	switch ( $float ) {

		case 'left':
			$float = ' ngg-left';
			break;

		case 'right':
			$float = ' ngg-right';
			break;

		case 'center':
			$float = ' ngg-center';
			break;

		default:
			$float = '';
			break;
	}

	// clean mode if needed
	$mode = ( preg_match( '/(web20|watermark)/i', $mode ) ) ? $mode : '';

	//let's initiate the url
	$picture->thumbnailURL = false;

	// check fo cached picture
	if ( $post->post_status == 'publish' )
		$picture->thumbnailURL = $picture->cached_singlepic_file( $width, $height, $mode );

	// if we didn't use a cached image then we take the on-the-fly mode
	if ( ! $picture->thumbnailURL )
		$picture->thumbnailURL = trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $imageID . '&amp;width=' . $width . '&amp;height=' . $height . '&amp;mode=' . $mode;

	// add more variables for render output
	$picture->imageURL = ( empty( $link ) ) ? $picture->imageURL : $link;
	$picture->href_link = $picture->get_href_link();
	$picture->alttext = html_entity_decode( stripslashes( nggGallery::i18n( $picture->alttext, 'pic_' . $picture->pid . '_alttext' ) ) );
	$picture->linktitle = htmlspecialchars( stripslashes( nggGallery::i18n( $picture->description, 'pic_' . $picture->pid . '_description' ) ) );
	$picture->description = html_entity_decode( stripslashes( nggGallery::i18n( $picture->description, 'pic_' . $picture->pid . '_description' ) ) );
	$picture->classname = 'ngg-singlepic' . $float;
	$picture->thumbcode = $picture->get_thumbcode( 'singlepic' . $imageID );
	$picture->height = (int) $height;
	$picture->width = (int) $width;
	$picture->caption = nggGallery::i18n( $caption );

	// filter to add custom content for the output
	$picture = apply_filters( 'ngg_image_object', $picture, $imageID );

	// let's get the meta data
	$meta = new nggMeta( $imageID );
	$meta->sanitize();
	$exif = $meta->get_EXIF();
	$iptc = $meta->get_IPTC();
	$xmp = $meta->get_XMP();
	$db = $meta->get_saved_meta();

	//if we get no exif information we try the database
	$exif = ( $exif == false ) ? $db : $exif;

	// look for singlepic-$template.php or pure singlepic.php
	$filename = ( empty( $template ) ) ? 'singlepic' : 'singlepic-' . $template;

	// create the output
	$out = nggGallery::capture( $filename, array( 'image' => $picture, 'meta' => $meta, 'exif' => $exif, 'iptc' => $iptc, 'xmp' => $xmp, 'db' => $db ) );

	$out = apply_filters( 'ngg_show_singlepic_content', $out, $picture );

	return $out;
}
/**
 * nggShowGalleryTags() - create a gallery based on the tags
 *
 * @access public
 * @param string $taglist list of tags as csv
 * @return string content
 */
function nggShowGalleryTags( $taglist, $template = '', $sorting = 'ASC' ) {

	// $_GET from wp_query
	$pid = get_query_var( 'pid' );
	$pageid = get_query_var( 'pageid' );

	// get now the related images
	$picturelist = nggTags::find_images_for_tags( $taglist, $sorting );

	// look for ImageBrowser if we have a $_GET('pid')
	if ( $pageid == get_the_ID() || ! is_home() )
		if ( ! empty( $pid ) ) {
			$out = nggCreateImageBrowser( $picturelist, $template );
			return $out;
		}

	// go on if not empty
	if ( empty( $picturelist ) )
		return '';

	// show gallery
	if ( is_array( $picturelist ) )
		$out = nggCreateGallery( $picturelist, false, $template );

	$out = apply_filters( 'ngg_show_gallery_tags_content', $out, $taglist );
	return $out;
}


/**
 * nggShowRelatedGallery() - create a gallery based on the tags
 *
 * @access public
 * @param string $taglist list of tags as csv
 * @param integer $maxImages (optional) limit the number of images to show. 0=no limit
 * @return string content
 */
function nggShowRelatedGallery( $taglist, $maxImages = 0 ) {

	$ngg_options = nggGallery::get_option( 'ngg_options' );

	// get now the related images
	$picturelist = nggTags::find_images_for_tags( $taglist, 'RAND' );

	// go on if not empty
	if ( empty( $picturelist ) )
		return '';

	// cut the list to maxImages
	if ( $maxImages > 0 )
		array_splice( $picturelist, $maxImages );

	// *** build the gallery output
	$out = '<div class="ngg-related-gallery">';
	foreach ( $picturelist as $picture ) {

		// get the effect code
		$thumbcode = $picture->get_thumbcode( __( 'Related images for', 'nggallery' ) . ' ' . get_the_title() );

		$out .= '<a href="' . $picture->imageURL . '" title="' . stripslashes( nggGallery::i18n( $picture->description, 'pic_' . $picture->pid . '_description' ) ) . '" ' . $thumbcode . ' >';
		$out .= '<img title="' . stripslashes( nggGallery::i18n( $picture->alttext, 'pic_' . $picture->pid . '_alttext' ) ) . '" alt="' . stripslashes( nggGallery::i18n( $picture->alttext, 'pic_' . $picture->pid . '_alttext' ) ) . '" src="' . $picture->thumbURL . '" />';
		$out .= '</a>' . "\n";
	}
	$out .= '</div>' . "\n";

	$out = apply_filters( 'ngg_show_related_gallery_content', $out, $taglist );

	return $out;
}

/**
 * nggShowAlbumTags() - create a gallery based on the tags
 * 20140119: Added template and sort
 * @access public
 * @param string $taglist list of tags as csv
 * @return string content
 */
function nggShowAlbumTags( $taglist, $template = '', $sorting = 'ASC' ) {

	global $wpdb, $nggRewrite;

	// $_GET from wp_query
	$tag = get_query_var( 'gallerytag' );
	$pageid = get_query_var( 'pageid' );

	// look for gallerytag variable
	if ( $pageid == get_the_ID() || ! is_home() ) {
		if ( ! empty( $tag ) ) {

			// avoid this evil code $sql = 'SELECT name FROM wp_ngg_tags WHERE slug = \'slug\' union select concat(0x7c,user_login,0x7c,user_pass,0x7c) from wp_users WHERE 1 = 1';
			$slug = esc_attr( $tag );
			$tagname = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $wpdb->terms WHERE slug = %s", $slug ) );
			$out = '<div id="albumnav"><span><a href="' . get_permalink() . '" title="' . __( 'Overview', 'nggallery' ) . ' ">' . __( 'Overview', 'nggallery' ) . '</a> | ' . $tagname . '</span></div>';
			$out .= nggShowGalleryTags( $slug, $template, $sorting );
			return $out;

		}
	}

	// get now the related images
	$picturelist = nggTags::get_album_images( $taglist );

	// go on if not empty
	if ( empty( $picturelist ) )
		return '';

	// re-structure the object that we can use the standard template
	foreach ( $picturelist as $key => $picture ) {
		$picturelist[ $key ]->previewpic = $picture->pid;
		$picturelist[ $key ]->previewname = $picture->filename;
		$picturelist[ $key ]->previewurl = site_url() . '/' . $picture->path . '/thumbs/thumbs_' . $picture->filename;
		$picturelist[ $key ]->counter = $picture->count;
		$picturelist[ $key ]->title = $picture->name;
		$picturelist[ $key ]->pagelink = $nggRewrite->get_permalink( array( 'gallerytag' => $picture->slug ) );
	}

	//TODO: Add pagination later
	$navigation = '<div class="ngg-clear"></div>';

	// create the output
	$out = nggGallery::capture( 'album-compact', array( 'album' => 0, 'galleries' => $picturelist, 'pagination' => $navigation ) );

	$out = apply_filters( 'ngg_show_album_tags_content', $out, $taglist );

	return $out;
}

/**
 * nggShowRelatedImages() - return related images based on category or tags
 *
 * @access public
 * @param string $type could be 'tags' or 'category'
 * @param integer $maxImages of images
 * @return string related gallery output or empty string if not tags/categories
 * 20150309: fix: error when no tags in site.
 * Few simplifications
 */
function nggShowRelatedImages( $type = '', $maxImages = 0 ) {
	$ngg_options = nggGallery::get_option( 'ngg_options' );

	if ( $type == '' ) {
		$type = $ngg_options['appendType'];
		$maxImages = $ngg_options['maxImages'];
	}

	$sluglist = array();

	switch ( $type ) {
		case 'tags':
			$taglist = get_the_tags(); //Return array of tag objects, false on failure or empty
			//This is a tag list for posts non Nextcellent tag lists.
			if ( ! $taglist )
				return "";
			foreach ( $taglist as $tag ) {
				$sluglist[] = $tag->slug;
			}
			break;

		case 'category':
			$catlist = get_the_category(); //return array (empty if no categories)
			if ( empty( $catlist ) )
				return "";
			foreach ( $catlist as $cat ) {
				$sluglist[] = $cat->category_nicename;
			}
			break;
	}
	$sluglist = implode( ',', $sluglist );
	$out = nggShowRelatedGallery( $sluglist, $maxImages );
	return $out;
}

/**
 * Template function for theme authors
 *
 * @access public
 * @param string  (optional) $type could be 'tags' or 'category'
 * @param integer (optional) $maxNumbers of images
 * @return void
 */
function the_related_images( $type = 'tags', $maxNumbers = 7 ) {
	echo nggShowRelatedImages( $type, $maxNumbers );
}

/**
 * nggShowRandomRecent($type, $maxImages, $template, $galleryId) - return recent or random images
 *
 * @access public
 * @param string $type 'id' (for latest addition to DB), 'date' (for image with the latest date), 'sort' (for image sorted by user order) or 'random'
 * @param integer $maxImages of images
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @param int $galleryId Limit to a specific gallery
 * @return string content
 */
function nggShowRandomRecent( $type, $maxImages, $template = '', $galleryId = 0 ) {

	// $_GET from wp_query
	$pid = get_query_var( 'pid' );
	$pageid = get_query_var( 'pageid' );

	// get now the recent or random images
	switch ( $type ) {
		case 'random':
			$picturelist = nggdb::get_random_images( $maxImages, $galleryId );
			break;
		case 'id':
			$picturelist = nggdb::find_last_images( 0, $maxImages, true, $galleryId, 'id' );
			break;
		case 'date':
			$picturelist = nggdb::find_last_images( 0, $maxImages, true, $galleryId, 'date' );
			break;
		case 'sort':
			$picturelist = nggdb::find_last_images( 0, $maxImages, true, $galleryId, 'sort' );
			break;
		default:
			// default is by pid
			$picturelist = nggdb::find_last_images( 0, $maxImages, true, $galleryId, 'id' );
	}

	// look for ImageBrowser if we have a $_GET('pid')
	if ( $pageid == get_the_ID() || ! is_home() )
		if ( ! empty( $pid ) ) {
			$out = nggCreateImageBrowser( $picturelist );
			return $out;
		}

	// go on if not empty
	if ( empty( $picturelist ) )
		return '';

	// show gallery
	if ( is_array( $picturelist ) )
		$out = nggCreateGallery( $picturelist, false, $template );

	$out = apply_filters( 'ngg_show_images_content', $out, $picturelist );

	return $out;
}

/**
 * nggTagCloud() - return a tag cloud based on the wp core tag cloud system
 *
 * @param array $args
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @return string content
 */
function nggTagCloud( $args = '', $template = '' ) {
	global $nggRewrite;

	// $_GET from wp_query
	$tag = get_query_var( 'gallerytag' );
	$pageid = get_query_var( 'pageid' );

	// look for gallerytag variable
	if ( $pageid == get_the_ID() || ! is_home() ) {
		if ( ! empty( $tag ) ) {

			$slug = esc_attr( $tag );
			$out = nggShowGalleryTags( $slug );
			return $out;
		}
	}

	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
		'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
		'exclude' => '', 'include' => '', 'link' => 'view', 'taxonomy' => 'ngg_tag'
	);
	$args = wp_parse_args( $args, $defaults );

	$tags = get_terms( $args['taxonomy'], array_merge( $args, array( 'orderby' => 'count', 'order' => 'DESC' ) ) ); // Always query top tags

	foreach ( $tags as $key => $tag ) {

		$tags[ $key ]->link = $nggRewrite->get_permalink( array( 'gallerytag' => $tag->slug ) );
		$tags[ $key ]->id = $tag->term_id;
	}

	$out = '<div class="ngg-tagcloud">' . wp_generate_tag_cloud( $tags, $args ) . '</div>';

	return $out;
}
?>