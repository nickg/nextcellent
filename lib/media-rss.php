<?php
/**
 * Class to produce Media RSS nodes
 * 
 * @author 		Vincent Prat
 * @copyright 	Copyright 2008-2011
 */
class nggMediaRss {

	/**
	 * Function called by the wp_head action to output the RSS link for medias
	 */
	static function add_mrss_alternate_link() {
		echo "<link id='MediaRSS' rel='alternate' type='application/rss+xml' title='NextGEN Gallery RSS Feed' href='" . nggMediaRss::get_mrss_url() . "' />\n";
	}

	/**
	 * Add the javascript required to enable PicLens/CoolIris support 
	 */
	static function add_piclens_javascript() {
		if ( is_ssl() )
			wp_enqueue_script( 'piclens', 'https://lite.piclens.com/current/piclens_optimized.js', array(), false, true );
		else
			wp_enqueue_script( 'piclens', 'http://lite.piclens.com/current/piclens_optimized.js', array(), false, true );
	}

	/**
	 * Get the URL of the general media RSS
	 */
	static function get_mrss_url() {
		return NGGALLERY_URLPATH . 'xml/media-rss.php';
	}

	/**
	 * Get the URL of a gallery media RSS
	 */
	static function get_gallery_mrss_url( $gid, $prev_next = false ) {
		return nggMediaRss::get_mrss_url() . '?' . ( 'gid=' . $gid . ( $prev_next ? '&prev_next=true' : '' ) . '&mode=gallery' );
	}

	/**
	 * Get the URL of an album media RSS
	 */
	static function get_album_mrss_url( $aid ) {
		return nggMediaRss::get_mrss_url() . '?' . ( 'aid=' . $aid . '&mode=album' );
	}

	/**
	 * Get the URL of the media RSS for last pictures
	 */
	static function get_last_pictures_mrss_url( $page = 0, $show = 30 ) {
		return nggMediaRss::get_mrss_url() . '?' . ( 'show=' . $show . '&page=' . $page . '&mode=last_pictures' );
	}

	/**
	 * Get the XML <rss> node corresponding to the last pictures registered
	 *
	 * @param int The current page (defaults to 0)
	 * @param int The number of pictures to include in one field (default 30) 
	 */
	static function get_last_pictures_mrss( $page = 0, $show = 30 ) {
		$images = nggdb::find_last_images( $page, $show );

		$title = stripslashes( get_option( 'blogname' ) );
		$description = stripslashes( get_option( 'blogdescription' ) );
		$link = site_url();
		$prev_link = ( $page > 0 ) ? nggMediaRss::get_last_pictures_mrss_url( $page - 1, $show ) : '';
		$next_link = count( $images ) != 0 ? nggMediaRss::get_last_pictures_mrss_url( $page + 1, $show ) : '';

		return nggMediaRss::get_mrss_root_node( $title, $description, $link, $prev_link, $next_link, $images );
	}

	/**
	 * Get the XML <rss> node corresponding to a gallery
	 *
	 * @param $gallery (object) The gallery to include in RSS
	 * @param $prev_gallery (object) The previous gallery to link in RSS (null if none)
	 * @param $next_gallery (object) The next gallery to link in RSS (null if none)
	 */
	static function get_gallery_mrss( $gallery, $prev_gallery = null, $next_gallery = null ) {
		global $nggdb;

		$ngg_options = nggGallery::get_option( 'ngg_options' );
		//Set sort order value, if not used (upgrade issue)
		$ngg_options['galSort'] = ( $ngg_options['galSort'] ) ? $ngg_options['galSort'] : 'pid';
		$ngg_options['galSortDir'] = ( $ngg_options['galSortDir'] == 'DESC' ) ? 'DESC' : 'ASC';

		$title = stripslashes( nggGallery::i18n( $gallery->title ) );
		$description = stripslashes( nggGallery::i18n( $gallery->galdesc ) );
		$link = nggMediaRss::get_permalink( $gallery->pageid );
		$prev_link = ( $prev_gallery != null ) ? nggMediaRss::get_gallery_mrss_url( $prev_gallery->gid, true ) : '';
		$next_link = ( $next_gallery != null ) ? nggMediaRss::get_gallery_mrss_url( $next_gallery->gid, true ) : '';
		//20140106:shouldn't call it statically when is not...
		//$images = nggdb::get_gallery($gallery->gid, $ngg_options['galSort'], $ngg_options['galSortDir']);
		$images = $nggdb->get_gallery( $gallery->gid, $ngg_options['galSort'], $ngg_options['galSortDir'] );

		return nggMediaRss::get_mrss_root_node( $title, $description, $link, $prev_link, $next_link, $images );
	}

	/**
	 * Get the XML <rss> node corresponding to an album
	 *
	 * @param $album The album to include in RSS
	 */
	static function get_album_mrss( $album ) {

		$title = stripslashes( nggGallery::i18n( $album->name ) );
		$description = '';
		$link = nggMediaRss::get_permalink( 0 );
		$prev_link = '';
		$next_link = '';
		$images = nggdb::find_images_in_album( $album->id );

		return nggMediaRss::get_mrss_root_node( $title, $description, $link, $prev_link, $next_link, $images );
	}

	/**
	 * Get the XML <rss> node
	 */
	static function get_mrss_root_node( $title, $description, $link, $prev_link, $next_link, $images ) {

		if ( $prev_link != '' || $next_link != '' )
			$out = "<rss version='2.0' xmlns:media='http://search.yahoo.com/mrss/' xmlns:atom='http://www.w3.org/2005/Atom'>\n";
		else
			$out = "<rss version='2.0' xmlns:media='http://search.yahoo.com/mrss/'>\n";

		$out .= "\t<channel>\n";

		$out .= nggMediaRss::get_generator_mrss_node();
		$out .= nggMediaRss::get_title_mrss_node( $title );
		$out .= nggMediaRss::get_description_mrss_node( $description );
		$out .= nggMediaRss::get_link_mrss_node( $link );

		if ( $prev_link != '' || $next_link != '' )
			$out .= nggMediaRss::get_self_node( nggMediaRss::get_mrss_url() );
		if ( $prev_link != '' ) {
			$out .= nggMediaRss::get_previous_link_mrss_node( $prev_link );
		}
		if ( $next_link != '' ) {
			$out .= nggMediaRss::get_next_link_mrss_node( $next_link );
		}

		foreach ( $images as $image ) {
			$out .= nggMediaRss::get_image_mrss_node( $image );
		}

		$out .= "\t</channel>\n";
		$out .= "</rss>\n";

		return $out;
	}

	/**
	 * Get the XML <generator> node
	 */
	static function get_generator_mrss_node( $indent = "\t\t" ) {
		return $indent . "<generator><![CDATA[NextGEN Gallery [http://nextgen-gallery.com]]]></generator>\n";
	}

	/**
	 * Get the XML <title> node
	 */
	static function get_title_mrss_node( $title, $indent = "\t\t" ) {
		return $indent . "<title>" . $title . "</title>\n";
	}

	/**
	 * Get the XML <description> node
	 */
	static function get_description_mrss_node( $description, $indent = "\t\t" ) {
		return $indent . "<description>" . $description . "</description>\n";
	}

	/**
	 * Get the XML <link> node
	 */
	static function get_link_mrss_node( $link, $indent = "\t\t" ) {
		return $indent . "<link><![CDATA[" . htmlspecialchars( $link ) . "]]></link>\n";
	}

	/**
	 * Get the XML <atom:link self> node
	 */
	static function get_self_node( $link, $indent = "\t\t" ) {
		return $indent . "<atom:link rel='self' href='" . htmlspecialchars( $link ) . "' type='application/rss+xml' />\n";
	}

	/**
	 * Get the XML <atom:link previous> node
	 */
	static function get_previous_link_mrss_node( $link, $indent = "\t\t" ) {
		return $indent . "<atom:link rel='previous' href='" . htmlspecialchars( $link ) . "' />\n";
	}

	/**
	 * Get the XML <atom:link next> node
	 */
	static function get_next_link_mrss_node( $link, $indent = "\t\t" ) {
		return $indent . "<atom:link rel='next' href='" . htmlspecialchars( $link ) . "' />\n";
	}

	/**
	 * Get the XML <item> node corresponding to one single image
	 *
	 * @param $image The image object
	 */
	static function get_image_mrss_node( $image, $indent = "\t\t" ) {
		$ngg_options = nggGallery::get_option( 'ngg_options' );

		$tags = $image->get_tags();
		$tag_names = '';
		foreach ( $tags as $tag ) {
			$tag_names .= ( $tag_names == '' ? $tag->name : ', ' . $tag->name );
		}

		$title = html_entity_decode( stripslashes( $image->alttext ) );
		$desc = html_entity_decode( stripslashes( $image->description ) );

		$thumbwidth = $ngg_options['thumbwidth'];
		$thumbheight = ( $ngg_options['thumbfix'] ? $ngg_options['thumbheight'] : $thumbwidth );

		$out = $indent . "<item>\n";
		$out .= $indent . "\t<title><![CDATA[" . nggGallery::i18n( $title, 'pic_' . $image->pid . '_alttext' ) . "]]></title>\n";
		$out .= $indent . "\t<description><![CDATA[" . nggGallery::i18n( $desc, 'pic_' . $image->pid . '_description' ) . "]]></description>\n";
		$out .= $indent . "\t<link><![CDATA[" . $image->get_permalink() . "]]></link>\n";
		$out .= $indent . "\t<guid>image-id:" . $image->pid . "</guid>\n";
		$out .= $indent . "\t<media:content url='" . esc_url( $image->imageURL ) . "' medium='image' />\n";
		$out .= $indent . "\t<media:title><![CDATA[" . nggGallery::i18n( $title, 'pic_' . $image->pid . '_alttext' ) . "]]></media:title>\n";
		$out .= $indent . "\t<media:description><![CDATA[" . nggGallery::i18n( $desc, 'pic_' . $image->pid . '_description' ) . "]]></media:description>\n";
		$out .= $indent . "\t<media:thumbnail url='" . esc_url( $image->thumbURL ) . "' width='" . $thumbwidth . "' height='" . $thumbheight . "' />\n";
		$out .= $indent . "\t<media:keywords><![CDATA[" . nggGallery::i18n( $tag_names ) . "]]></media:keywords>\n";
		$out .= $indent . "\t<media:copyright><![CDATA[Copyright (c) " . get_option( "blogname" ) . " (" . site_url() . ")]]></media:copyright>\n";
		$out .= $indent . "</item>\n";

		return $out;
	}

	static function get_permalink( $page_id ) {
		if ( $page_id == 0 )
			$permalink = site_url();
		else
			$permalink = get_permalink( $page_id );

		return $permalink;
	}

}

?>