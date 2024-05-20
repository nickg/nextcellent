<?php
if ( !class_exists('nggImage') ) :
/**
* Image PHP class for the WordPress plugin NextGEN Gallery
* 
* @author Alex Rabe 
*  
*/
class nggImage{
	
	/**** Public variables ****/	
	public string $name = '';			// Gallery/image and album name
	private string $errmsg = '';			// Error message to display, if any
	public bool $error = FALSE; 		// Error state
	public string $imageURL = '';			// URL Path to the image
	public string $thumbURL = '';			// URL Path to the thumbnail
	public string $imagePath = '';			// Server Path to the image
	public string $thumbPath = '';			// Server Path to the thumbnail
	public string $href = '';			// A href link code

	// TODO: remove thumbPrefix and thumbFolder (constants)
	private string $thumbPrefix = 'thumbs_';	// FolderPrefix to the thumbnail
	private string $thumbFolder = '/thumbs/';	// Foldername to the thumbnail

	/**** Image Data ****/
	public int $galleryid = 0;			// Gallery ID
	public int $pid = 0;			// Image ID
	public string $filename = '';			// Image filename
	public string $description = '';			// Image description
	public string $alttext = '';			// Image alttext
	public string $imagedate = '';			// Image date/time
	public string $exclude = '';			// Image exclude
	public string $thumbcode = '';			// Image effect code
	public string|array $tags;
	public $image_slug;
	public string $imageHTML;
	public string $thumbHTML;
	public bool $hidden;
	public string $style;
	public string $pidlink;
	public string $url;
	public string $thumbnailURL;
	public string $size;
	public string $caption;
	public string $href_link;
	public string $previous_image_link;
	public string $next_image_link;
	public int $previous_pid;
	public string $next_pid;
	public int $number;
	public int $total;
	public string $linktitle;
	public string $anchor;

	/**** Gallery Data ****/
	public string $path = '';			// Gallery path	
	public string $title = '';			// Gallery title
	public int $pageid = 0;			// Gallery page ID
	public int $previewpic = 0;			// Gallery preview pic		
	public int $gid;
	public $galdesc;
	public string $abspath;
	public string $permalink = '';
	public $post_id;
	public $sortorder;
	public $meta_data;
	public $slug;
	public int $author;

	/**** Album Data ****/
	public array $gallery_ids;
	public int $id;
	public string $albumdesc;

		
	/**
	 * Constructor
	 * 
	 * @param object $gallery The nggGallery object representing the gallery containing this image
	 * @return void
	 */
	function __construct($gallery) {			
			
		//This must be an object
		$gallery = (object) $gallery;

		// Build up the object
		foreach ($gallery as $key => $value)
			$this->$key = $value ;
		
		// Finish initialisation
		$this->name			= $gallery->name;
		$this->path			= $gallery->path;
		$this->title		= stripslashes($gallery->title);
		$this->pageid		= $gallery->pageid;		
		$this->previewpic	= $gallery->previewpic;
	
		// set urls and paths
        //20140217:this->path can't be used, because it can contain parameters
        //for example: mysite/mycategory?lang=en causing improper URL format and also plugin incompatibility (Qtranslate for example.
		$this->imageURL		= site_url() . '/' . $this->path . '/' . $this->filename;
		$this->thumbURL 	= site_url() . '/' . $this->path . '/thumbs/thumbs_' . $this->filename;
		$this->imagePath	= WINABSPATH.$this->path . '/' . $this->filename;
		$this->thumbPath	= WINABSPATH.$this->path . '/thumbs/thumbs_' . $this->filename;
        $this->meta_data	= @unserialize($this->meta_data) ;
		$this->imageHTML	= $this->get_href_link();
		$this->thumbHTML	= $this->get_href_thumb_link();
		
		do_action_ref_array('ngg_get_image', array(&$this));
        
        // Note wp_cache_add will increase memory needs (4-8 kb)
		//wp_cache_add($this->pid, $this, 'ngg_image');
		// Get tags only if necessary
		unset($this->tags);
	}
	
	/**
	* Get the thumbnail code (to add effects on thumbnail click)
	*
	* Applies the filter 'ngg_get_thumbcode'
	*/
	function get_thumbcode($galleryname = '') {
	   
        // clean up the name
        $galleryname = sanitize_title( $galleryname );
        
		// read the option setting
		$ngg_options = get_option('ngg_options');
		
		// get the effect code
		if ($ngg_options['thumbEffect'] != "none")
			$this->thumbcode = stripslashes($ngg_options['thumbCode']);		
		
		// for highslide to a different approach	
		if ($ngg_options['thumbEffect'] == "highslide") {
			$this->thumbcode = str_replace("%GALLERY_NAME%", "'" . $galleryname . "'", $this->thumbcode);
		} else {
			$this->thumbcode = str_replace("%GALLERY_NAME%", $galleryname, $this->thumbcode);
		}

		/**
		 * The list of available variables:
		 * - %GALLERY_NAME% - The name of the gallery
		 * - %IMG_WIDTH% - The width of the full image.
		 * - %IMG_HEIGHT% - The height of the full image.
		 */
		if (isset($this->meta_data['width']) && isset($this->meta_data['width'])) {
			$this->thumbcode = str_replace(array('%IMG_WIDTH%', '%IMG_HEIGHT%'), array($this->meta_data['width'], $this->meta_data['height']), $this->thumbcode);
		}

		return apply_filters('ngg_get_thumbcode', $this->thumbcode, $this);
	}
	
	function get_href_link() {
		// create the a href link from the picture
		$this->href  = "\n".'<a href="'.$this->imageURL.'" title="'.htmlspecialchars( stripslashes( nggGallery::i18n($this->description, 'pic_' . $this->pid . '_description') ) ).'" '.$this->get_thumbcode($this->name).'>'."\n\t";
		$this->href .= '<img alt="'.$this->alttext.'" src="'.$this->imageURL.'"/>'."\n".'</a>'."\n";

		return $this->href;
	}

	function get_href_thumb_link() {
		// create the a href link with the thumbanil
		$this->href  = "\n".'<a href="'.$this->imageURL.'" title="'.htmlspecialchars( stripslashes( nggGallery::i18n($this->description, 'pic_' . $this->pid . '_description') ) ).'" '.$this->get_thumbcode($this->name).'>'."\n\t";
		$this->href .= '<img alt="'.$this->alttext.'" src="'.$this->thumbURL.'"/>'."\n".'</a>'."\n";

		return $this->href;
	}
	
	/**
	 * This function creates a cache for all singlepics to reduce the CPU load
	 * 
	 * @param int $width
	 * @param int $height
	 * @param string $mode could be watermark | web20 | crop
	 * @return the url for the image or false if failed 
	 */
	function cached_singlepic_file($width = '', $height = '', $mode = '' ) {

		$ngg_options = get_option('ngg_options');
		
		include_once( nggGallery::graphic_library() );
		
		// cache filename should be unique
		$cachename   	= $this->pid . '_' . $mode . '_'. $width . 'x' . $height . '_' . $this->filename;
		$cachefolder 	= WINABSPATH .$ngg_options['gallerypath'] . 'cache/';
		$cached_url  	= site_url() . '/' . $ngg_options['gallerypath'] . 'cache/' . $cachename;
		$cached_file	= $cachefolder . $cachename;
		
		// check first for the file
		if ( file_exists($cached_file) )
			return $cached_url;
		
		// create folder if needed
		if ( !file_exists($cachefolder) )
			if ( !wp_mkdir_p($cachefolder) )
				return false;
		
		$thumb = new ngg_Thumbnail($this->imagePath, TRUE);
		// echo $thumb->errmsg;
		
		if (!$thumb->error) {
            if ($mode == 'crop') {
        		// calculates the new dimentions for a downsampled image
                list ( $ratio_w, $ratio_h ) = wp_constrain_dimensions($thumb->currentDimensions['width'], $thumb->currentDimensions['height'], $width, $height);
                // check ratio to decide which side should be resized
                ( $ratio_h <  $height || $ratio_w ==  $width ) ? $thumb->resize(0, $height) : $thumb->resize($width, 0);
                // get the best start postion to crop from the middle    
                $ypos = ($thumb->currentDimensions['height'] - $height) / 2;
        		$thumb->crop(0, $ypos, $width, $height);	               
            } else
                $thumb->resize($width , $height);
			
			if ($mode == 'watermark') {
				if ($ngg_options['wmType'] == 'image') {
					$thumb->watermarkImgPath = $ngg_options['wmPath'];
					$thumb->watermarkImage($ngg_options['wmPos'], $ngg_options['wmXpos'], $ngg_options['wmYpos']); 
				}
				if ($ngg_options['wmType'] == 'text') {
					$thumb->watermarkText = $ngg_options['wmText'];
					$thumb->watermarkCreateText($ngg_options['wmFont'], $ngg_options['wmColor'], $ngg_options['wmSize'], $ngg_options['wmOpaque']);
					$thumb->watermarkImage($ngg_options['wmPos'], $ngg_options['wmXpos'], $ngg_options['wmYpos']);  
				}
			}
			
			if ($mode == 'web20') {
				$thumb->createReflection(40,40,50,false,'#a4a4a4');
			}
			
			// save the new cache picture
			$thumb->save($cached_file,$ngg_options['imgQuality']);
		}
		$thumb->destruct();
		
		// check again for the file
		if (file_exists($cached_file))
			return $cached_url;
		
		return false;
	}
	
	/**
	 * Get the tags associated to this image
	 */
	function get_tags() {
		if ( !isset($this->tags) )
			$this->tags = wp_get_object_terms($this->pid, 'ngg_tag', 'fields=all');

		return $this->tags;
	}
	
	/**
	 * Get the permalink to the image
	 * TODO Get a permalink to a page presenting the image
	 */
	function get_permalink() {
		if ($this->permalink == '')
			$this->permalink = $this->imageURL;

		return $this->permalink; 
	}
    
    function __destruct() {

    }
}
endif;
?>
