<?php

/**
 * Image METADATA PHP class for the WordPress plugin NextGEN Gallery
 * nggmeta.lib.php
 *
 * @author Alex Rabe
 *
 *
 */

class nggMeta {

	/**** Image Data ****/
	public nggImage $image;		// The image object
	/**
	 * @var array|bool
	 */
	private $size = false;	// The image size
	/**
	 * @var array|bool
	 */
	private $exif_data = false;	// EXIF data array
	/**
	 * @var array|bool
	 */
	private $iptc_data = false;	// IPTC data array
	/**
	 * @var array|bool
	 */
	private $xmp_data = false;	// XMP data array
	/**
	 * @var array|bool
	 */
	/**** Filtered Data ****/
	private $exif_array = false;	// EXIF data array
	/**
	 * @var array|bool
	 */
	private $iptc_array = false;	// IPTC data array
	/**
	 * @var array|bool
	 */
	private $xmp_array = false;	// XMP data array
	private bool $sanitize = false;  // sanitize meta data on request

	/**
	 * Parses the nggMeta data only if needed
	 * @param int $image path to a image
	 * @param bool $onlyEXIF parse only exif if needed
	 */
	function __construct( $pic_id, $onlyEXIF = false ) {

		//get the path and other data about the image
		$this->image = nggdb::find_image( $pic_id );

		$this->image = apply_filters( 'ngg_find_image_meta', $this->image );

		if ( file_exists( $this->image->imagePath ) ) {

			$this->size = @getimagesize( $this->image->imagePath, $metadata );

			if ( $this->size && is_array( $metadata ) ) {

				// get exif - data
				if ( is_callable( 'exif_read_data' ) )
					$this->exif_data = @exif_read_data( $this->image->imagePath, 0, true );

				// stop here if we didn't need other meta data
				if ( ! $onlyEXIF ) {
					// get the iptc data - should be in APP13
					if ( is_callable( 'iptcparse' ) && isset( $metadata['APP13'] ) )
						$this->iptc_data = @iptcparse( $metadata['APP13'] );

					// get the xmp data in a XML format
					if ( is_callable( 'xml_parser_create' ) )
						$this->xmp_data = $this->extract_XMP( $this->image->imagePath );
				}
			}
		}
	}

	/**
	 * return the saved meta data from the database
	 *
	 * @since 1.4.0
	 * @param string $object (optional)
	 * @return array|mixed return either the complete array or the single object
	 */
	function get_saved_meta( $object = false ) {

		$meta = $this->image->meta_data;

		//check if we already import the meta data to the database
		if ( ! is_array( $meta ) || ( $meta['saved'] != true ) )
			return false;

		// return one element if requested
		if ( $object )
			return $meta[ $object ];

		//removed saved parameter we don't need that to show
		unset( $meta['saved'] );

		// and remove empty tags or arrays
		foreach ( $meta as $key => $value ) {
			if ( empty( $value ) or is_array( $value ) )
				unset( $meta[ $key ] );
		}

		// on request sanitize the output
		if ( $this->sanitize == true )
			array_walk( $meta, function (&$value) {
				$value = esc_html( $value );
			} );

		return $meta;
	}

	/**
	 * nggMeta::get_EXIF()
	 * See also http://trac.wordpress.org/changeset/6313
	 *
	 * @return bool|array EXIF data
	 */
	function get_EXIF( $object = false ) {

		if ( ! $this->exif_data )
			return false;

		if ( ! is_array( $this->exif_array ) ) {

			$meta = array();

			if ( isset( $this->exif_data['EXIF'] ) ) {
				$exif = $this->exif_data['EXIF'];

				if ( ! empty( $exif['FNumber'] ) )
					$meta['aperture'] = 'F ' . round( $this->exif_frac2dec( $exif['FNumber'] ), 2 );
				if ( ! empty( $exif['Model'] ) )
					$meta['camera'] = trim( $exif['Model'] );
				if ( ! empty( $exif['DateTimeDigitized'] ) )
					$meta['created_timestamp'] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $this->exif_date2ts( $exif['DateTimeDigitized'] ) );
				else if ( ! empty( $exif['DateTimeOriginal'] ) )
					$meta['created_timestamp'] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $this->exif_date2ts( $exif['DateTimeOriginal'] ) );
				if ( ! empty( $exif['FocalLength'] ) )
					$meta['focal_length'] = $this->exif_frac2dec( $exif['FocalLength'] ) . __( ' mm', 'nggallery' );
				if ( ! empty( $exif['ISOSpeedRatings'] ) )
					$meta['iso'] = $exif['ISOSpeedRatings'];
				if ( ! empty( $exif['ExposureTime'] ) ) {
					$meta['shutter_speed'] = $this->exif_frac2dec( $exif['ExposureTime'] );
					$meta['shutter_speed'] = ( $meta['shutter_speed'] > 0.0 and $meta['shutter_speed'] < 1.0 ) ? ( '1/' . round( 1 / $meta['shutter_speed'], -1 ) ) : ( $meta['shutter_speed'] );
					$meta['shutter_speed'] .= __( ' sec', 'nggallery' );
				}
				//Bit 0 indicates the flash firing status
				if ( ! empty( $exif['Flash'] ) )
					$meta['flash'] = ( $exif['Flash'] & 1 ) ? __( 'Fired', 'nggallery' ) : __( 'Not fired', ' nggallery' );
			}

			// additional information
			if ( isset( $this->exif_data['IFD0'] ) ) {
				$exif = $this->exif_data['IFD0'];

				if ( ! empty( $exif['Model'] ) )
					$meta['camera'] = $exif['Model'];
				if ( ! empty( $exif['Make'] ) )
					$meta['make'] = $exif['Make'];
				if ( ! empty( $exif['ImageDescription'] ) )
					$meta['title'] = mb_convert_encoding( $exif['ImageDescription'], 'UTF-8' );
				if ( ! empty( $exif['Orientation'] ) )
					$meta['Orientation'] = $exif['Orientation'];
			}

			// this is done by Windows
			if ( isset( $this->exif_data['WINXP'] ) ) {
				$exif = $this->exif_data['WINXP'];

				if ( ! empty( $exif['Title'] ) && empty( $meta['title'] ) )
					$meta['title'] = mb_convert_encoding( $exif['Title'], 'UTF-8' );
				if ( ! empty( $exif['Author'] ) )
					$meta['author'] = mb_convert_encoding( $exif['Author'], 'UTF-8' );
				if ( ! empty( $exif['Keywords'] ) )
					$meta['tags'] = mb_convert_encoding( $exif['Keywords'], 'UTF-8' );
				if ( ! empty( $exif['Subject'] ) )
					$meta['subject'] = mb_convert_encoding( $exif['Subject'], 'UTF-8' );
				if ( ! empty( $exif['Comments'] ) )
					$meta['caption'] = mb_convert_encoding( $exif['Comments'], 'UTF-8' );
			}

			$this->exif_array = $meta;
		}

		// return one element if requested
		if ( $object == true ) {
			$value = isset( $this->exif_array[ $object ] ) ? $this->exif_array[ $object ] : false;
			return $value;
		}

		// on request sanitize the output
		if ( $this->sanitize == true )
			array_walk( $this->exif_array, function (&$value) {
				$value = esc_html( $value );
			} );

		return $this->exif_array;

	}

	// convert a fraction string to a decimal
	function exif_frac2dec( $str ) {
		@list( $n, $d ) = explode( '/', $str );
		if ( ! empty( $d ) )
			return $n / $d;
		return $str;
	}

	// convert the exif date format to a unix timestamp
	function exif_date2ts( $str ) {
		// seriously, who formats a date like 'YYYY:MM:DD hh:mm:ss'?
		@list( $date, $time ) = explode( ' ', trim( $str ) );
		@list( $y, $m, $d ) = explode( ':', $date );

		return strtotime( "{$y}-{$m}-{$d} {$time}" );
	}

	/**
	 * nggMeta::readIPTC() - IPTC Data Information for EXIF Display
	 *
	 * @param mixed $output_tag
	 * @return string[]|bool IPTC-tags
	 */
	function get_IPTC( $object = false ) {

		if ( ! $this->iptc_data )
			return false;

		if ( ! is_array( $this->iptc_array ) ) {

			// --------- Set up Array Functions --------- //
			$iptcTags = array(
				"2#005" => 'title',
				"2#007" => 'status',
				"2#012" => 'subject',
				"2#015" => 'category',
				"2#025" => 'keywords',
				"2#055" => 'created_date',
				"2#060" => 'created_time',
				"2#080" => 'author',
				"2#085" => 'position',
				"2#090" => 'city',
				"2#092" => 'location',
				"2#095" => 'state',
				"2#100" => 'country_code',
				"2#101" => 'country',
				"2#105" => 'headline',
				"2#110" => 'credit',
				"2#115" => 'source',
				"2#116" => 'copyright',
				"2#118" => 'contact',
				"2#120" => 'caption'
			);

			$meta = array();
			foreach ( $iptcTags as $key => $value ) {
				if ( isset( $this->iptc_data[ $key ] ) )
					$meta[ $value ] = trim( mb_convert_encoding( implode( ", ", $this->iptc_data[ $key ] ), 'UTF-8' ) );

			}
			$this->iptc_array = $meta;
		}

		// return one element if requested
		if ( $object )
			return ( isset( $this->iptc_array[ $object ] ) ) ? $this->iptc_array[ $object ] : NULL;

		// on request sanitize the output
		if ( $this->sanitize == true )
			array_walk( $this->iptc_array, function (&$value) {
				$value = esc_html( $value );
			} );

		return $this->iptc_array;
	}

	/**
	 * nggMeta::extract_XMP()
	 * get XMP DATA
	 * code by Pekka Saarinen http://photography-on-the.net
	 *
	 * @param mixed $filename
	 * @return string|bool XML data
	 */
	function extract_XMP( $filename ) {

		//TODO:Require a lot of memory, could be better
		ob_start();
		@readfile( $filename );
		$source = ob_get_contents();
		ob_end_clean();

		$start = strpos( $source, "<x:xmpmeta" );
		$end = strpos( $source, "</x:xmpmeta>" );
		if ( ( ! $start === false ) && ( ! $end === false ) ) {
			$lenght = $end - $start;
			$xmp_data = substr( $source, $start, $lenght + 12 );
			unset( $source );
			return $xmp_data;
		}

		unset( $source );
		return false;
	}

	/**
	 * nggMeta::get_XMP()
	 *
	 * @package Taken from http://php.net/manual/en/function.xml-parse-into-struct.php
	 * @author Alf Marius Foss Olsen & Alex Rabe
	 * @return string[]|bool XML Array or object
	 *
	 */
	function get_XMP( $object = false ) {

		if ( ! $this->xmp_data )
			return false;

		if ( ! is_array( $this->xmp_array ) ) {

			$parser = xml_parser_create();
			xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 ); // Dont mess with my cAsE sEtTings
			xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 ); // Dont bother with empty info
			xml_parse_into_struct( $parser, $this->xmp_data, $values );
			xml_parser_free( $parser );

			$xmlarray = array();	// The XML array
			$this->xmp_array = array();	// The returned array
			$stack = array();	// tmp array used for stacking
			$list_array = array();	// tmp array for list elements
			$list_element = false;	// rdf:li indicator

			foreach ( $values as $val ) {

				if ( $val['type'] == "open" ) {
					array_push( $stack, $val['tag'] );

				} elseif ( $val['type'] == "close" ) {
					// reset the compared stack
					if ( $list_element == false )
						array_pop( $stack );
					// reset the rdf:li indicator & array
					$list_element = false;
					$list_array = array();

				} elseif ( $val['type'] == "complete" ) {
					if ( $val['tag'] == "rdf:li" ) {
						// first go one element back
						if ( $list_element == false )
							array_pop( $stack );
						$list_element = true;
						// do not parse empty tags
						if ( empty( $val['value'] ) )
							continue;
						// save it in our temp array
						$list_array[] = $val['value'];
						// in the case it's a list element we seralize it
						$value = implode( ",", $list_array );
						$this->setArrayValue( $xmlarray, $stack, $value );
					} else {
						array_push( $stack, $val['tag'] );
						// do not parse empty tags
						if ( ! empty( $val['value'] ) )
							$this->setArrayValue( $xmlarray, $stack, $val['value'] );
						array_pop( $stack );
					}
				}

			} // foreach

			// don't parse a empty array
			if ( empty( $xmlarray ) || empty( $xmlarray['x:xmpmeta'] ) )
				return false;

			// cut off the useless tags
			$xmlarray = $xmlarray['x:xmpmeta']['rdf:RDF']['rdf:Description'];

			// --------- Some values from the XMP format--------- //
			$xmpTags = array(
				'xap:CreateDate' => 'created_timestamp',
				'xap:ModifyDate' => 'last_modfied',
				'xap:CreatorTool' => 'tool',
				'dc:format' => 'format',
				'dc:title' => 'title',
				'dc:creator' => 'author',
				'dc:subject' => 'keywords',
				'dc:description' => 'caption',
				'photoshop:AuthorsPosition' => 'position',
				'photoshop:City' => 'city',
				'photoshop:Country' => 'country'
			);

			foreach ( $xmpTags as $key => $value ) {
				// if the kex exist
				if ( isset( $xmlarray[ $key ] ) ) {
					switch ( $key ) {
						case 'xap:CreateDate':
						case 'xap:ModifyDate':
							$this->xmp_array[ $value ] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $xmlarray[ $key ] ) );
							break;
						default:
							$this->xmp_array[ $value ] = $xmlarray[ $key ];
					}
				}
			}

		}

		// return one element if requested
		if ( $object != false )
			return isset( $this->xmp_array[ $object ] ) ? $this->xmp_array[ $object ] : false;

		// on request sanitize the output
		if ( $this->sanitize == true )
			array_walk( $this->xmp_array, function (&$value) {
				$value = esc_html( $value );
			} );

		return $this->xmp_array;
	}

	function setArrayValue( &$array, $stack, $value ) {
		if ( $stack ) {
			$key = array_shift( $stack );
			$this->setArrayValue( $array[ $key ], $stack, $value );
			return $array;
		} else {
			$array = $value;
		}
	}

	/**
	 * nggMeta::get_META() - return a meta value form the available list
	 *
	 * @param string $object
	 * @return mixed $value
	 */
	function get_META( $object = false ) {

		// defined order first look into database, then XMP, IPTC and EXIF.
		if ( $value = $this->get_saved_meta( $object ) )
			return $value;
		if ( $value = $this->get_XMP( $object ) )
			return $value;
		if ( $value = $this->get_IPTC( $object ) )
			return $value;
		if ( $value = $this->get_EXIF( $object ) )
			return $value;

		// nothing found ?
		return false;
	}

	/**
	 * nggMeta::i8n_name() -  localize the tag name
	 *
	 * @param mixed $key
	 * @return string translated $key
	 */
	function i8n_name( $key ) {

		$tagnames = array(
			'aperture' => __( 'Aperture', 'nggallery' ),
			'credit' => __( 'Credit', 'nggallery' ),
			'camera' => __( 'Camera', 'nggallery' ),
			'caption' => __( 'Caption', 'nggallery' ),
			'created_timestamp' => __( 'Date/Time', 'nggallery' ),
			'copyright' => __( 'Copyright', 'nggallery' ),
			'focal_length' => __( 'Focal length', 'nggallery' ),
			'iso' => __( 'ISO', 'nggallery' ),
			'shutter_speed' => __( 'Shutter speed', 'nggallery' ),
			'title' => __( 'Title', 'nggallery' ),
			'author' => __( 'Author', 'nggallery' ),
			'tags' => __( 'Tags', 'nggallery' ),
			'subject' => __( 'Subject', 'nggallery' ),
			'make' => __( 'Make', 'nggallery' ),
			'status' => __( 'Edit Status', 'nggallery' ),
			'category' => __( 'Category', 'nggallery' ),
			'keywords' => __( 'Keywords', 'nggallery' ),
			'created_date' => __( 'Date Created', 'nggallery' ),
			'created_time' => __( 'Time Created', 'nggallery' ),
			'position' => __( 'Author Position', 'nggallery' ),
			'city' => __( 'City', 'nggallery' ),
			'location' => __( 'Location', 'nggallery' ),
			'state' => __( 'Province/State', 'nggallery' ),
			'country_code' => __( 'Country code', 'nggallery' ),
			'country' => __( 'Country', 'nggallery' ),
			'headline' => __( 'Headline', 'nggallery' ),
			'source' => __( 'Source', 'nggallery' ),
			'copyright' => __( 'Copyright Notice', 'nggallery' ),
			'contact' => __( 'Contact', 'nggallery' ),
			'last_modfied' => __( 'Last modified', 'nggallery' ),
			'tool' => __( 'Program tool', 'nggallery' ),
			'format' => __( 'Format', 'nggallery' ),
			'width' => __( 'Image Width', 'nggallery' ),
			'height' => __( 'Image Height', 'nggallery' ),
			'flash' => __( 'Flash', 'nggallery' )
		);

		if ( isset( $tagnames[ $key ] ) )
			$key = $tagnames[ $key ];

		return ( $key );

	}

	/**
	 * Return the Timestamp from the image , if possible it's read from exif data
	 *
	 * @return
	 */
	function get_date_time() {

		$date_time = time();

		// get exif - data
		if ( isset( $this->exif_data['EXIF'] ) ) {

			// try to read the date / time from the exif
			foreach ( array( 'DateTimeDigitized', 'DateTimeOriginal', 'FileDateTime' ) as $key ) {
				if ( isset( $this->exif_data['EXIF'][ $key ] ) ) {
					$date_time = strtotime( $this->exif_data['EXIF'][ $key ] );
					break;
				}
			}
		} else {
			// if no other date available, get the filetime
			$date_time = @filectime( $this->image->imagePath );
		}

		// Return the MySQL format
		$date_time = date( 'Y-m-d H:i:s', $date_time );

		return $date_time;
	}

	/**
	 * This function return the most common metadata, via a filter we can add more
	 * Reason : GD manipulation removes that options
	 *
	 * @since V1.4.0
	 * @return array|boolean
	 */
	function get_common_meta() {
		global $wpdb;

		$meta = array(
			'aperture' => 0,
			'credit' => '',
			'camera' => '',
			'caption' => '',
			'created_timestamp' => 0,
			'copyright' => '',
			'focal_length' => 0,
			'iso' => 0,
			'shutter_speed' => 0,
			'flash' => 0,
			'title' => '',
			'keywords' => ''
		);

		$meta = apply_filters( 'ngg_read_image_metadata', $meta );

		// meta should be still an array
		if ( ! is_array( $meta ) )
			return false;

		foreach ( $meta as $key => $value ) {
			$meta[ $key ] = $this->get_META( $key );
		}

		//let's add now the size of the image
		$meta['width'] = $this->size[0];
		$meta['height'] = $this->size[1];

		return $meta;
	}

	/**
	 * If needed sanitize each value before output
	 *
	 * @return void
	 */
	function sanitize() {
		$this->sanitize = true;
	}

}
