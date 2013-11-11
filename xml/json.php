<?php
/**
* REST Application Programming Interface PHP class for the WordPress plugin NextGEN Gallery
* Should emulate some kind of Flickr JSON callback : ?callback=json&format=json&api_key=1234567890&method=search&term=myterm
* 
* @version      1.1.0
* @author Alex Rabe 
* 
* @require		PHP 5.2.0 or higher
* 
*/

class nggAPI {

	/**
	  *	$_GET Variables 
	  * 
	  * @since 1.5.0
	  * @access private
	  * @var string
	  */
    var $format		=	false;		// $_GET['format'] 	: Return a XML oder JSON output
	var $api_key	=	false;		// $_GET['api_key']	: Protect the access via a random key (required if user is not logged into backend)
	var $method		=	false;		// $_GET['method']	: search | gallery | image | album | tag | autocomplete
	var $term		=	false;		// $_GET['term']   	: The search term (required for method search | tag)
	var $id			=	false;		// $_GET['id']	  	: object id (required for method gallery | image | album )
	var $limit		=	false;		// $_GET['limit']	: maximum of images which we request
    var $type		=	false;		// $_GET['type']	: gallery | image | album (required for method autocomplete)
    
	/**
	 * Contain the final output
	 *
	 * @since 1.5.0
	 * @access private
	 * @var string
	 */	
	var $output		=	'';

	/**
	 * Holds the requested information as array
	 *
	 * @since 1.5.0
	 * @access private
	 * @var array
	 */	
	var $result		=	'';
	
	/**
	 * Init the variables
	 * 
	 */	
	function __construct() {
		
        if ( !defined('ABSPATH') )
            die('You are not allowed to call this page directly.');

		if ( !function_exists('json_encode') )
			wp_die('Json_encode not available. You need to use PHP 5.2');
		
		// Read the parameter on init
		$this->format 	= isset($_GET['format']) ? strtolower( $_GET['format'] ) : false;
		$this->api_key 	= isset($_GET['api_key'])? $_GET['api_key'] : false; 
		$this->method 	= isset($_GET['method']) ? strtolower( $_GET['method'] ) : false; 
		$this->term		= isset($_GET['term'])   ? urldecode( $_GET['term'] ) : false; 
		$this->id 		= isset($_GET['id'])     ? (int) $_GET['id'] : 0;
		$this->limit 	= isset($_GET['limit'])  ? (int) $_GET['limit'] : 0;
        $this->type		= isset($_GET['type'])   ? strtolower( $_GET['type'] ) : false; 		
		$this->result	= array();
        $this->list     = false;
		
		$this->start_process();
		$this->render_output();
	}

	function start_process() {
	   
        global $ngg;
		
		if ( !$this->valid_access() ) 
			return;
		
		switch ( $this->method ) {
			case 'search' :
				//search for some images
				$this->result['images'] = array_merge( (array) nggdb::search_for_images( $this->term ), (array) nggTags::find_images_for_tags( $this->term , 'ASC' ));
			break;
			case 'album' :
				//search for some album  //TODO : Get images for each gallery, could end in a big db query
				$this->result['album'] = nggdb::find_album( $this->id );
			break;            
			case 'gallery' :
				//search for some gallery
				$this->result['images'] = ($this->id == 0) ? nggdb::find_last_images( 0 , 100 ) : nggdb::get_gallery( $this->id, $ngg->options['galSort'], $ngg->options['galSortDir'], true, 0, 0, true );
			break;
			case 'image' :
				//search for some image
				$this->result['images'] = nggdb::find_image( $this->id );
			break;
			case 'tag' :
				//search for images based on tags
				$this->result['images'] = nggTags::find_images_for_tags( $this->term , 'ASC' );
			break;
			case 'recent' :
				//search for images based on tags
				$this->result['images'] = nggdb::find_last_images( 0 , $this->limit );
			break;
			case 'autocomplete' :
				//return images, galleries or albums for autocomplete drop down list
				return $this->autocomplete();                
			break;
			case 'version' :
				$this->result = array ('stat' => 'ok', 'version' => $ngg->version);
				return;           
			break;
			default :
				$this->result = array ('stat' => 'fail', 'code' => '98', 'message' => 'Method not known.');
				return false;	
			break;		
		}

		// result should be fine	
		$this->result['stat'] = 'ok';	
	}
	
	function valid_access() {
		
		// if we are logged in, then we can go on
		if ( is_user_logged_in() )
			return true;
		
		//TODO:Implement an API KEY check later
		if 	($this->api_key != false)
			return true;
		
		$this->result = array ('stat' => 'fail', 'code' => '99', 'message' => 'Insufficient permissions. Method requires read privileges; none granted.');
		return false;
	}

	/**
	 * return search result for autocomplete request from backend
	 * 
     * @since 1.7.0
	 * @return void
	 */
	function autocomplete() {
        global $nggdb;
        
        switch ( $this->type ) {
			case 'image' :
            
                // return the last entries in case of an empty search string
                if ( empty($this->term) )
				    $list = $nggdb->find_last_images(0, $this->limit, false);
                else
                    $list = $nggdb->search_for_images($this->term, $this->limit);
                    
                if( is_array($list) ) {
        			foreach($list as $image) {
                        // reorder result to array-object
                        $obj = new stdClass();
                        $obj->id = $image->pid;
                        $name = ( empty($image->alttext) ? $image->filename : $image->alttext );
                        //TODO : need to rework save/load 
                        $name = stripslashes( htmlspecialchars_decode($name, ENT_QUOTES));
                        $obj->label = $image->pid . ' - ' . $name;
                        $obj->value = $name;
                        $this->result[] = $obj;
        			}
        		}

                return $this->result;
            break;
			case 'gallery' :
            
                if ( empty($this->term) )
                    $list = $nggdb->find_all_galleries('gid', 'DESC', false, $this->limit );
                else
                    $list = $nggdb->search_for_galleries($this->term, $this->limit);   
                     
                if( is_array($list) ) {
        			foreach($list as $gallery) {
                        // reorder result to array-object
                        $obj = new stdClass();
                        $obj->id = $gallery->gid;
                        $name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
                        $name = stripslashes( htmlspecialchars_decode($name, ENT_QUOTES));
                        $obj->label = $gallery->gid . ' - ' . $name;
                        $obj->value = $name;
                        $this->result[] = $obj;
        			}
        		}
                return $this->result;
            break;
			case 'album' :
            
                if ( empty($this->term) )
                    $list = $nggdb->find_all_album('id', 'DESC', $this->limit );
                else
                    $list = $nggdb->search_for_albums($this->term, $this->limit); 
                                    
                if( is_array($list) ) {
        			foreach($list as $album) {
                        // reorder result to array-object            			 
                        $obj = new stdClass();
                        $obj->id = $album->id;
                        $album->name = stripslashes( htmlspecialchars_decode($album->name, ENT_QUOTES));
                        $obj->label = $album->id . ' - ' . $album->name;
                        $obj->value = $album->name;
                        $this->result[] = $obj;
        			}
        		}
                return $this->result;
            break;
			default :
				$this->result = array ('stat' => 'fail', 'code' => '98', 'message' => 'Type not known.');
				return false;	
			break;	
        }
    }

    /**
     * Iterates through a multidimensional array
     * 
     * @author Boris Glumpler
     * @param array $arr
     * @return void
     */
    function create_xml_array( &$arr )
    {
        $xml = '';
        
        if( is_object( $arr ) )
            $arr = get_object_vars( $arr );

        foreach( (array)$arr as $k => $v ) {
            if( is_object( $v ) )
                $v = get_object_vars( $v );
            //nodes must contain letters   
            if( is_numeric( $k ) )
                $k = 'id-'.$k;                
            if( is_array( $v ) )
                $xml .= "<$k>\n". $this->create_xml_array( $v ). "</$k>\n";
            else
                $xml .= "<$k>$v</$k>\n";
        }
        
        return $xml;
    }
	
	function render_output() {
		
		if ($this->format == 'json') {
			header('Content-Type: application/json; charset=' . get_option('blog_charset'), true);
			$this->output = json_encode($this->result);
		} else {
			header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
			$this->output  = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n";
			$this->output .= "<nextgen-gallery>" . $this->create_xml_array( $this->result )  . "</nextgen-gallery>\n";
		}	
		
	}

	/**
	 * PHP5 style destructor and will run when the class is finished.
	 *
	 * @return output
	 */
	function __destruct() {
		echo $this->output;
	}

}

// let's use it
$nggAPI = new nggAPI;
