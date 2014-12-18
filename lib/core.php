<?php
/**
* Main PHP class for the WordPress plugin NextGEN Gallery
*
* @author Alex Rabe
*
*
*/
class nggGallery {

	/**
	* Show a error messages
	*/
	static function show_error($message) {
		echo '<div class="error" id="error"><p>' . $message . '</p></div>';
	}

	/**
	* Show a system messages
	*/
	static function show_message($message) {
		echo '<div class="updated fade" id="message"><p>' . $message . '</p></div>';
	}

	/**
	* get the thumbnail url to the image
	*/
	static function get_thumbnail_url($imageID, $picturepath = '', $fileName = ''){

		// get the complete url to the thumbnail
		global $wpdb;

		// safety first
		$imageID = (int) $imageID;

		// get gallery values
		if ( empty($fileName) ) {
			list($fileName, $picturepath ) = $wpdb->get_row("SELECT p.filename, g.path FROM $wpdb->nggpictures AS p INNER JOIN $wpdb->nggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ", ARRAY_N);
		}

		if ( empty($picturepath) ) {
			$picturepath = $wpdb->get_var("SELECT g.path FROM $wpdb->nggpictures AS p INNER JOIN $wpdb->nggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ");
		}

		// set gallery url
		$folder_url 	= site_url() . '/' . $picturepath.nggGallery::get_thumbnail_folder($picturepath, FALSE);
		$thumbnailURL	= $folder_url . 'thumbs_' . $fileName;

		return $thumbnailURL;
	}

	/**
	* get the complete url to the image
	*/
	static function get_image_url($imageID, $picturepath = '', $fileName = '') {
		global $wpdb;

		// safety first
		$imageID = (int) $imageID;

		// get gallery values
		if (empty($fileName)) {
			list($fileName, $picturepath ) = $wpdb->get_row("SELECT p.filename, g.path FROM $wpdb->nggpictures AS p INNER JOIN $wpdb->nggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ", ARRAY_N);
		}

		if (empty($picturepath)) {
			$picturepath = $wpdb->get_var("SELECT g.path FROM $wpdb->nggpictures AS p INNER JOIN $wpdb->nggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ");
		}

		// set gallery url
		$imageURL 	= site_url() . '/' . $picturepath . '/' . $fileName;

		return $imageURL;
	}

	/**
	* nggGallery::get_thumbnail_folder()
	*
	* @param mixed $gallerypath
	* @param bool $include_Abspath
	* @return string $foldername
	*/
	static function create_thumbnail_folder($gallerypath, $include_Abspath = TRUE) {
		if (!$include_Abspath) {
			$gallerypath = WINABSPATH . $gallerypath;
		}

		if (!file_exists($gallerypath)) {
			return FALSE;
		}

		if (is_dir($gallerypath . '/thumbs/')) {
			return '/thumbs/';
		}

		if (is_admin()) {
			if (!is_dir($gallerypath . '/thumbs/')) {
				if ( !wp_mkdir_p($gallerypath . '/thumbs/') ) {
					if (SAFE_MODE) {
						nggAdmin::check_safemode($gallerypath . '/thumbs/');
					} else {
						nggGallery::show_error(__('Unable to create directory ', 'nggallery') . $gallerypath . '/thumbs !');
					}
					return FALSE;
				}
				return '/thumbs/';
			}
		}

		return FALSE;

	}

	/**
	* nggGallery::get_thumbnail_folder()
	*
	* @param mixed $gallerypath
	* @param bool $include_Abspath
	* @deprecated use create_thumbnail_folder() if needed;
	* @return string $foldername
	*/
	static function get_thumbnail_folder($gallerypath, $include_Abspath = TRUE) {
		return nggGallery::create_thumbnail_folder($gallerypath, $include_Abspath);
	}

	/**
	* nggGallery::get_thumbnail_prefix() - obsolete
	*
	* @param string $gallerypath
	* @param bool   $include_Abspath
	* @deprecated prefix is now fixed to "thumbs_";
	* @return string  "thumbs_";
	*/
	static function get_thumbnail_prefix($gallerypath, $include_Abspath = TRUE) {
		return 'thumbs_';
	}

	/**
	* nggGallery::get_option() - get the options and overwrite them with custom meta settings
	*
	* @param string $key
	* @return array $options
	*/
	static function get_option($key) {
        global $post;

		// get first the options from the database
		$options = get_option($key);

        if ( $post == null )
            return $options;

		// Get all key/value data for the current post.
		$meta_array = get_post_custom();

		// Ensure that this is a array
		if ( !is_array($meta_array) )
			$meta_array = array($meta_array);

		// assign meta key to db setting key
		$meta_tags = array(
			'string' => array(
				'ngg_gal_ShowOrder' 		=> 'galShowOrder',
				'ngg_gal_Sort' 				=> 'galSort',
				'ngg_gal_SortDirection' 	=> 'galSortDir',
				'ngg_gal_ShowDescription'	=> 'galShowDesc',
				'ngg_ir_Audio' 				=> 'irAudio',
				'ngg_ir_Overstretch'		=> 'irOverstretch',
				'ngg_ir_Transition'			=> 'irTransition',
				'ngg_ir_Backcolor' 			=> 'irBackcolor',
				'ngg_ir_Frontcolor' 		=> 'irFrontcolor',
				'ngg_ir_Lightcolor' 		=> 'irLightcolor',
                'ngg_slideshowFX'			=> 'slideFx',
			),

			'int' => array(
				'ngg_gal_Images' 			=> 'galImages',
				'ngg_gal_Columns'			=> 'galColumns',
				'ngg_paged_Galleries'		=> 'galPagedGalleries',
				'ngg_ir_Width' 				=> 'irWidth',
				'ngg_ir_Height' 			=> 'irHeight',
				'ngg_ir_Rotatetime' 		=> 'irRotatetime'
			),

			'bool' => array(
				'ngg_gal_ShowSlide'			=> 'galShowSlide',
				'ngg_gal_ShowPiclense'		=> 'usePicLens',
				'ngg_gal_ImageBrowser' 		=> 'galImgBrowser',
				'ngg_gal_HideImages' 		=> 'galHiddenImg',
				'ngg_ir_Shuffle' 			=> 'irShuffle',
				'ngg_ir_LinkFromDisplay' 	=> 'irLinkfromdisplay',
				'ngg_ir_ShowNavigation'		=> 'irShownavigation',
				'ngg_ir_ShowWatermark' 		=> 'irWatermark',
				'ngg_ir_Kenburns' 			=> 'irKenburns'
			)
		);

		foreach ($meta_tags as $typ => $meta_keys){
			foreach ($meta_keys as $key => $db_value){
				// if the kex exist overwrite it with the custom field
				if (array_key_exists($key, $meta_array)){
					switch ($typ) {
					case 'string':
						$options[$db_value] = (string) esc_attr($meta_array[$key][0]);
						break;
					case 'int':
						$options[$db_value] = (int) $meta_array[$key][0];
						break;
					case 'bool':
						$options[$db_value] = (bool) $meta_array[$key][0];
						break;
					}
				}
			}
		}

		return $options;
	}

	/**
	* nggGallery::scale_image() - Scale down a image
	*
	* @param mixed $location (filename)
	* @param int $maxw - max width
	* @param int $maxh -  max height
	* @return array (width, heigth)
	*/
	static function scale_image($location, $maxw = 0, $maxh = 0){
		$img = @getimagesize($location);
		if ($img){
			$w = $img[0];
			$h = $img[1];

			$dim = array('w','h');
			foreach($dim AS $val) {
				$max = "max{$val}";
				if(${$val} > ${$max} && ${$max}){
					$alt = ($val == 'w') ? 'h' : 'w';
					$ratio = ${$alt} / ${$val};
					${$val} = ${$max};
					${$alt} = ${$val} * $ratio;
				}
			}

			return array( $w, $h );
		}
		return false;
	}

	/**
	* Renders a section of user display code.  The code is first checked for in the current theme display directory
	* before defaulting to the plugin
	* Call the function :	nggGallery::render ('template_name', array ('var1' => $var1, 'var2' => $var2));
	*
	* @autor John Godley
	* @param string $template_name Name of the template file (without extension)
	* @param string $vars Array of variable name=>value that is available to the display code (optional)
	* @param bool $callback In case we check we didn't find template we tested it one time more (optional)
	* @return void
	**/
	static function render($template_name, $vars = array (), $callback = false) {
		foreach ($vars AS $key => $val) {
			$$key = $val;
		}

		// hook into the render feature to allow other plugins to include templates
		$custom_template = apply_filters( 'ngg_render_template', false, $template_name );

		if ( ( $custom_template != false ) &&  file_exists ($custom_template) ) {
			include ( $custom_template );
		//search in theme folder
		} elseif (file_exists (get_stylesheet_directory() . "/nggallery/$template_name.php")) {
			include (get_stylesheet_directory() . "/nggallery/$template_name.php");
		//search in WP_CONTENT_DIR
		} elseif (file_exists (WP_CONTENT_DIR . "/ngg_styles/$template_name.php")) {
			include (WP_CONTENT_DIR . "/ngg_styles/$template_name.php");
		//use defaults
		} elseif (file_exists (NGGALLERY_ABSPATH . "/view/$template_name.php")) {
			include (NGGALLERY_ABSPATH . "/view/$template_name.php");
		} elseif ( $callback === true ) {
            echo "<p>Rendering of template $template_name.php failed</p>";
		} else {
            //test without the "-template" name one time more

            $explode = explode('-', $template_name, 2);
            $template_name = array_shift($explode);
            nggGallery::render ($template_name, $vars , true);
		}
	}

	/**
	* Captures an section of user display code.
	*
	* @autor John Godley
	* @param string $template_name Name of the template file (without extension)
	* @param string $vars Array of variable name=>value that is available to the display code (optional)
	* @return string
	**/
	static function capture ($template_name, $vars = array ()) {
		ob_start ();
		nggGallery::render ($template_name, $vars);
		$output = ob_get_contents ();
		ob_end_clean ();

		return $output;
	}

	/**
	 * nggGallery::graphic_library() - switch between GD and ImageMagick
	 *
	 * @return path to the selected library
	 */
	static function graphic_library() {

		$ngg_options = get_option('ngg_options');

		if ( $ngg_options['graphicLibrary'] == 'im')
			return NGGALLERY_ABSPATH . '/lib/imagemagick.inc.php';
		else
			return NGGALLERY_ABSPATH . '/lib/gd.thumbnail.inc.php';

	}


    /**
     * Find where is the gallery stylesheet located.
     *
     * Combines several techniques to find out where is the current
     * gallery stylesheet located.
     * 20140924: Simplification & Documentation
     * 20140924: included
     *
     * @since 1.9.13
     * @access static
     *
     * @see (none)
     * @link Filter List http://wpgetready.com/wiki/nextcellent-plugin/nextcellent-filter-list/
     * @global type $varname Short description.
     *
     * @param (none)
     *
     * @return bool|mixed|string|void
     */

	static function get_theme_css_file() {
  		//Allow to include a custom stylesheet, return stylesheet uri
		$stylesheet = apply_filters( 'ngg_load_stylesheet', false );
        //Is there a stylsheet customization?
		if ( $stylesheet !== false ) {
            return ($stylesheet); //Yes, return it
        }
        //No filter customization. Is the user putting a custom stylesheet on his/her theme?
        if ( file_exists (get_stylesheet_directory() . '/nggallery.css') ) {
            return get_stylesheet_directory_uri() . '/nggallery.css'; //Yes, return uri to the custom style
        }
        //No filter and no custom style. Bye bye
		return false;
	}

	/**
	 * Support for i18n with wpml, polyglot or qtrans
	 *
	 * @param string $in
	 * @param string $name (optional) required for wpml to determine the type of translation
	 * @return string $in localized
	 */
	static function i18n($in, $name = null) {

		if ( function_exists( 'langswitch_filter_langs_with_message' ) )
			$in = langswitch_filter_langs_with_message($in);

		if ( function_exists( 'polyglot_filter' ))
			$in = polyglot_filter($in);

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ))
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($in);

        if (is_string($name) && !empty($name) && function_exists('icl_translate'))
            $in = icl_translate('plugin_ngg', $name, $in, true);

		$in = apply_filters('localization', $in);

		return $in;
	}

    /**
     * This function register strings for the use with WPML plugin (see http://wpml.org/ )
     *
     * @param object $image
     * @return void
     */
    static function RegisterString($image) {
        if (function_exists('icl_register_string')) {
            global $wpdb;
            icl_register_string('plugin_ngg', 'pic_' . $image->pid . '_description', $image->description, TRUE);
            icl_register_string('plugin_ngg', 'pic_' . $image->pid . '_alttext', $image->alttext, TRUE);
        }
    }

	/**
	 * Check the memory_limit and calculate a recommended memory size
	 *
	 * @since V1.2.0
	 * @return string message about recommended image size
	 */
	static function check_memory_limit() {

		if ( (function_exists('memory_get_usage')) && (ini_get('memory_limit')) ) {

			// get memory limit
			$memory_limit = ini_get('memory_limit');
			if ($memory_limit != '')
				$memory_limit = substr($memory_limit, 0, -1) * 1024 * 1024;

			// calculate the free memory
			$freeMemory = $memory_limit - memory_get_usage();

			// build the test sizes
			$sizes = array();
			$sizes[] = array ( 'width' => 800,  'height' => 600);
			$sizes[] = array ( 'width' => 1024, 'height' => 768);
			$sizes[] = array ( 'width' => 1280, 'height' => 960);  // 1MP
			$sizes[] = array ( 'width' => 1600, 'height' => 1200); // 2MP
			$sizes[] = array ( 'width' => 2016, 'height' => 1512); // 3MP
			$sizes[] = array ( 'width' => 2272, 'height' => 1704); // 4MP
			$sizes[] = array ( 'width' => 2560, 'height' => 1920); // 5MP

			// test the classic sizes
			foreach ($sizes as $size){
				// very, very rough estimation
				if ($freeMemory < round( $size['width'] * $size['height'] * 5.09 )) {
                	$result = sprintf(  __( 'Note : Based on your server memory limit you should not upload larger images then <strong>%d x %d</strong> pixel', 'nggallery' ), $size['width'], $size['height']);
					return $result;
				}
			}
		}
		return;
	}

	/**
	 * Slightly modfifed version of pathinfo(), clean up filename & rename jpeg to jpg
	 *
	 * @param string $name The name being checked.
	 * @return array containing information about file
	 */
	static function fileinfo( $name ) {

		//Sanitizes a filename replacing whitespace with dashes
		$name = sanitize_file_name($name);

		//get the parts of the name
		$filepart = pathinfo ( strtolower($name) );

		if ( empty($filepart) )
			return false;

		// required until PHP 5.2.0
		if ( empty($filepart['filename']) )
			$filepart['filename'] = substr($filepart['basename'],0 ,strlen($filepart['basename']) - (strlen($filepart['extension']) + 1) );

		$filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );

		//extension jpeg will not be recognized by the slideshow, so we rename it
		$filepart['extension'] = ($filepart['extension'] == 'jpeg') ? 'jpg' : $filepart['extension'];

		//combine the new file name
		$filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];

		return $filepart;
	}

	/**
	 * Check for extended capabilites. Must previously registers with add_ngg_capabilites()
	 *
	 * @since 1.5.0
	 * @param string $capability
	 * @return bool $result of capability check
	 */
	static function current_user_can( $capability ) {

		global $_ngg_capabilites;

		if ( is_array($_ngg_capabilites) )
			if ( in_array($capability , $_ngg_capabilites) )
				return current_user_can( $capability );

		return true;
	}

	/**
	 * Check for extended capabilites and echo disabled="disabled" for input form
	 *
	 * @since 1.5.0
	 * @param string $capability
	 * @return void
	 */
	static function current_user_can_form( $capability ) {

		if ( !nggGallery::current_user_can( $capability ))
			echo 'disabled="disabled"';
	}

	/**
	 * Register more capabilities for custom use and add it to the administrator
	 *
	 * @since 1.5.0
	 * @param string $capability
	 * @param bool $register the new capability automatic to the admin role
	 * @return void
	 */
	static function add_capabilites( $capability , $register = true ) {
		global $_ngg_capabilites;

		if ( !is_array($_ngg_capabilites) )
			$_ngg_capabilites = array();

		$_ngg_capabilites[] = $capability;

		if ( $register ) {
			$role = get_role('administrator');
			if ( !empty($role) )
				$role->add_cap( $capability );
		}

	}

    /**
     * Check for mobile user agent
     *
     * @since 1.6.0
     * @author Part taken from WPtouch plugin (http://www.bravenewcode.com)
     * @return bool $result of  check
     */
    static function detect_mobile_phone() {

        $useragents = array();

        // Check if WPtouch is running
        if ( function_exists('bnc_wptouch_get_user_agents') )
            $useragents = bnc_wptouch_get_user_agents();
        else {
        	$useragents = array(
                "iPhone",  			 // Apple iPhone
        		"iPod", 			 // Apple iPod touch
        		"Android", 			 // 1.5+ Android
        		"dream", 		     // Pre 1.5 Android
        		"CUPCAKE", 			 // 1.5+ Android
        		"blackberry9500",	 // Storm
        		"blackberry9530",	 // Storm
        		"blackberry9520",	 // Storm	v2
        		"blackberry9550",	 // Storm v2
        		"blackberry9800",	 // Torch
        		"webOS",			 // Palm Pre Experimental
        		"incognito", 		 // Other iPhone browser
        		"webmate" 			 // Other iPhone browser
        	);

        	asort( $useragents );
         }

        // Godfather Steve says no to flash
        if ( is_array($useragents) )
            $useragents[] = "iPad";  // Apple iPad;

        // WPtouch User Agent Filter
        $useragents = apply_filters( 'wptouch_user_agents', $useragents );

 		foreach ( $useragents as $useragent ) {
			if ( preg_match( "#$useragent#i", $_SERVER['HTTP_USER_AGENT'] ) )
				return true;
		}

        return false;
    }

    /**
     * get_memory_usage
     *
     * @access only for debug purpose
     * @since 1.8.3
     * @param string $text
     * @return void
     */
    static function get_memory( $text = '' ) {
        global $memory;

        $memory_peak = memory_get_usage();
        $diff = 0;

		if ( isset($memory) )
            $diff = $memory_peak - $memory;

        $exp = ($diff < 0) ? '-' : '';
        $diff = ($exp == '-') ? 0 - $diff : $diff;

        $memory = $memory_peak;

        $unit = array('b','kb','mb','gb','tb','pb');
        $rounded = @round($diff/pow(1024,($i=floor(log($diff,1024)))),2).' '.$unit[$i];

        echo $text . ': ' . $exp . $rounded .'<br />';

    }

    /**
     * Show NextGEN Version in header
     * @since 1.9.0
     *
     * @return void
     */
    static function nextgen_version() {
        global $ngg;
        echo apply_filters('show_nextgen_version', '<!-- <meta name="NextGEN" version="'. $ngg->version . '" /> -->' . "\n");
    }

    /**
     * Prevents injection filtering HTML Code
     * 20140604: Improved based on suggestions of jayque9
     * http://wordpress.org/support/topic/prevent-removal-of-html-code-from-image-descriptions
     */
    static function suppress_injection
    ($html_text) {
        global $allowedposttags;
        return wp_kses(stripslashes($html_text),$allowedposttags);
    }
}
?>
