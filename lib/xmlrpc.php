<?php
/**
 * XML-RPC protocol support for NextGEN Gallery
 *
 * @package NextGEN Gallery
 * @author Alex Rabe
 *
 */
class nggXMLRPC{

	/**
	 * Init the methods for the XMLRPC hook
	 *
	 */
	function __construct() {

		add_filter('xmlrpc_methods', array(&$this, 'add_methods') );
	}

	function add_methods($methods) {

		$methods['ngg.installed'] = array(&$this, 'nggInstalled');
        // Image methods
	    $methods['ngg.getImage'] = array(&$this, 'getImage');
	    $methods['ngg.getImages'] = array(&$this, 'getImages');
	    $methods['ngg.uploadImage'] = array(&$this, 'uploadImage');
        $methods['ngg.editImage'] = array(&$this, 'editImage');
        $methods['ngg.deleteImage'] = array(&$this, 'deleteImage');
        // Gallery methods
	    $methods['ngg.getGallery'] = array(&$this, 'getGallery');
	    $methods['ngg.getGalleries'] = array(&$this, 'getGalleries');
	    $methods['ngg.newGallery'] = array(&$this, 'newGallery');
        $methods['ngg.editGallery'] = array(&$this, 'editGallery');
        $methods['ngg.deleteGallery'] = array(&$this, 'deleteGallery');
        // Album methods
	    $methods['ngg.getAlbum'] = array(&$this, 'getAlbum');
   	    $methods['ngg.getAlbums'] = array(&$this, 'getAlbums');
        $methods['ngg.newAlbum'] = array(&$this, 'newAlbum');
	    $methods['ngg.editAlbum'] = array(&$this, 'editAlbum');
        $methods['ngg.deleteAlbum'] = array(&$this, 'deleteAlbum');

		return $methods;
	}

	/**
	 * Check if it's an csv string, then serialize it.
	 *
     * @since 1.9.2
	 * @param string $data
	 * @return serialized string
	 */
	function is_serialized( $data ) {

        // if it isn't a string, we don't serialize it.
        if ( ! is_string( $data ) )
            return false;

        if ($data && !strpos( $data , '{')) {
        	$items = explode(',', $data);
        	return serialize($items);
		}

		return $data;
	}

	/**
	 * Check if NextGEN Gallery is installed
	 *
	 * @since 1.4
	 *
	 * @param none
	 * @return string version number
	 */
	function nggInstalled($args) {
		global $ngg;
		return array( 'version' => $ngg->version );
	}

	/**
	 * Log user in.
	 *
	 * @since 2.8
	 *
	 * @param string $username User's username.
	 * @param string $password User's password.
	 * @return mixed WP_User object if authentication passed, false otherwise
	 */
	function login($username, $password) {
		global $wp_version;

		if (version_compare($wp_version,"3.5","<")) {
			if ( !get_option( 'enable_xmlrpc' ) ) {
				$this->error = new IXR_Error( 405, sprintf( __('XML-RPC services are disabled on this blog.  An admin user can enable them at %s'),  admin_url('options-writing.php') ) );
				return false;
			}
		}

        $user = wp_authenticate($username, $password);

		if (is_wp_error($user)) {
			$this->error = new IXR_Error(403, __('Bad login/pass combination.'));
			return false;
		}

		set_current_user( $user->ID );
		return $user;
	}

	/**
	 * Method "ngg.uploadImage"
	 * Uploads a image to a gallery
	 *
	 * @since 1.4
	 *
	 * @copyright addapted from WP Core
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- struct data
	 *	          o string name
	 *            o string type (optional)
	 *	          o base64 bits
	 *	          o bool overwrite (optional)
	 *			  o int gallery
	 *			  o int image_id  (optional)
	 * @return array with image meta data
	 */
	function uploadImage($args) {
		global $wpdb;

		require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions
		require_once ( 'meta.php' );			// meta data import

		$blog_ID	= (int) $args[0];
		$username	= $wpdb->escape($args[1]);
		$password	= $wpdb->escape($args[2]);
		$data		= $args[3];

		$name = $data['name'];
		$type = $data['type'];
		$bits = $data['bits'];

		// gallery & image id
		$gid  	= (int) $data['gallery'];  // required field
		$pid  	= (int) $data['image_id']; // optional but more foolproof of overwrite
		$image	= false; // container for the image object

		logIO('O', '(NGG) Received '.strlen($bits).' bytes');

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		// Check if you have the correct capability for upload
		if ( !current_user_can('NextGEN Upload images') ) {
			logIO('O', '(NGG) User does not have upload_files capability');
			$this->error = new IXR_Error(401, __('You are not allowed to upload files to this site.'));
			return $this->error;
		}

		// Look for the gallery , could we find it ?
		if ( !$gallery = nggdb::find_gallery($gid) )
			return new IXR_Error(404, __('Could not find gallery ' . $gid ));

		// Now check if you have the correct capability for this gallery
		if ( !nggAdmin::can_manage_this_gallery($gallery->author) ) {
			logIO('O', '(NGG) User does not have upload_files capability');
			$this->error = new IXR_Error(401, __('You are not allowed to upload files to this gallery.'));
			return $this->error;
		}

		//clean filename and extract extension
		$filepart = nggGallery::fileinfo( $name );
		$name = $filepart['basename'];

		// check for allowed extension and if it's an image file
		$ext = array('jpg', 'png', 'gif');
		if ( !in_array($filepart['extension'], $ext) ){
			logIO('O', '(NGG) Not allowed file type');
			$this->error = new IXR_Error(401, __('This is no valid image file.','nggallery'));
			return $this->error;
		}

		// in the case you would overwrite the image, let's delete the old one first
		if(!empty($data["overwrite"]) && ($data["overwrite"] == true)) {

			// search for the image based on the filename, if it's not already provided
			if ($pid == 0)
				$pid = $wpdb->get_col(" SELECT pid FROM {$wpdb->nggpictures} WHERE filename = '{$name}' AND galleryid = '{$gid}' ");

			if ( !$image = nggdb::find_image( $pid ) )
				return new IXR_Error(404, __('Could not find image id ' . $pid ));

			// sync the gallery<->image parameter, otherwise we may copy it to the wrong gallery
			$gallery = $image;

			// delete now the image
			if ( !@unlink( $image->imagePath ) ) {
				$errorString = sprintf(__('Failed to delete image %1$s ','nggallery'), $image->imagePath);
				logIO('O', '(NGG) ' . $errorString);
				return new IXR_Error(500, $errorString);
			}
		}

		// upload routine from wp core, load first the image to the upload folder, $upload['file'] contain the path
		$upload = wp_upload_bits($name, $type, $bits);
		if ( ! empty($upload['error']) ) {
			$errorString = sprintf(__('Could not write file %1$s (%2$s)'), $name, $upload['error']);
			logIO('O', '(NGG) ' . $errorString);
			return new IXR_Error(500, $errorString);
		}

		// this is the dir to the gallery
		$path = WINABSPATH . $gallery->path;

		// check if the filename already exist, if not add a counter index
		$filename = wp_unique_filename( $path, $name );
		$destination = $path . '/'. $filename;

		// Move files to gallery folder
		if ( !@rename($upload['file'], $destination ) ) {
			$errorString = sprintf(__('Failed to move image %1$s to %2$s','nggallery'), '<strong>' . $upload['file'] . '</strong>', $destination);
			logIO('O', '(NGG) ' . $errorString);
			return new IXR_Error(500, $errorString);
		}

		//add to database if it's a new image
		if(empty($data["overwrite"]) || ($data["overwrite"] == false)) {
			$pid_array = nggAdmin::add_Images( $gallery->gid, array( $filename ) );
			// the first element is our new image id
			if (count($pid_array) == 1)
				$pid = $pid_array[0];
		}

		//get all information about the image, in the case it's a new one
		if (!$image)
			$image = nggdb::find_image( $pid );

		// create again the thumbnail, should return a '1'
		nggAdmin::create_thumbnail( $image );

		return apply_filters( 'ngg_upload_image', $image );

	}

	/**
	 * Method "ngg.deleteImage"
	 * Delete a Image from the database and gallery
	 *
	 * @since 1.7.3
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- int image_id
	 * @return true
	 */
	function deleteImage($args) {

		global $nggdb, $ngg;

        require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
        $id    	    = (int) $args[3];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if ( !$image = nggdb::find_image($id) )
			return(new IXR_Error(404, __("Invalid image ID")));

		if ( !current_user_can( 'NextGEN Manage gallery' ) && !nggAdmin::can_manage_this_gallery($image->author) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to edit this image' ) );

		if ($ngg->options['deleteImg']) {
            @unlink($image->imagePath);
            @unlink($image->thumbPath);
            @unlink($image->imagePath . "_backup" );
        }

        nggdb::delete_image ( $id );

		return true;

	}

	/**
	 * Method "ngg.editImage"
	 * Edit a existing Image
	 *
	 * @since 1.7.3
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- int Image ID
	 *	    	- string alt/title text
	 *	    	- string description
	 *	    	- int exclude from gallery (0 or 1)
	 * @return true if success
	 */
	function editImage($args) {

		global $ngg;

		require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$id      	= (int) $args[3];
        $alttext    = $args[4];
        $description= $args[5];
        $exclude    = (int) $args[6];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if ( !$image = nggdb::find_image($id)  )
			return(new IXR_Error(404, __( 'Invalid image ID' )));

        if ( !current_user_can( 'NextGEN Manage gallery' ) && !nggAdmin::can_manage_this_gallery($image->author) )
            return new IXR_Error( 401, __( 'Sorry, you must be able to edit this image' ) );

		if ( !empty( $id ) )
			$result = nggdb::update_image($id, false, false, $description, $alttext, $exclude);

		if ( !$result )
			return new IXR_Error(500, __('Sorry, could not update the image'));

		return true;

	}

	/**
	 * Method "ngg.newGallery"
	 * Create a new gallery
	 *
	 * @since 1.4
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- string new gallery name
	 * @return int with new gallery ID
	 */
	function newGallery($args) {

		global $ngg;

		require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$name   	= $args[3];
		$id 		= false;

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if( !current_user_can( 'NextGEN Manage gallery' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage galleries' ) );

		if ( !empty( $name ) )
			$id = nggAdmin::create_gallery($name, $ngg->options['gallerypath'], false);

		if ( !$id )
			return new IXR_Error(500, __('Sorry, could not create the gallery'));

		return($id);

	}

	/**
	 * Method "ngg.editGallery"
	 * Edit a existing gallery
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- int gallery ID
	 *	    	- string gallery name
	 *	    	- string title
	 *	    	- string description
     *          - int ID of the preview picture
	 * @return true if success
	 */
	function editGallery($args) {

		global $ngg;

		require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$id      	= (int) $args[3];
		$name 		= $args[4];
        $title      = $args[5];
        $description= $args[6];
        $previewpic = (int) $args[7];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if ( !$gallery = nggdb::find_gallery($id)  )
			return(new IXR_Error(404, __("Invalid gallery ID")));

        if ( !current_user_can( 'NextGEN Manage gallery' ) && !nggAdmin::can_manage_this_gallery($gallery->author) )
            return new IXR_Error( 401, __( 'Sorry, you must be able to manage this gallery' ) );

		if ( !empty( $name ) )
			$result = nggdb::update_gallery($id, $name, false, $title, $description, false, $previewpic);

		if ( !$result )
			return new IXR_Error(500, __('Sorry, could not update the gallery'));

		return true;

	}

	/**
	 * Method "ngg.newAlbum"
	 * Create a new album
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- string new album name
     *          - int id of preview image
     *          - string description
     *          - string serialized array of galleries or a comma-separated string of gallery IDs
	 * @return int with new album ID
	 */
	function newAlbum($args) {

		global $ngg;

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$name   	= $args[3];
		$preview   	= (int) $args[4];
        $description= $args[5];
        $galleries 	= $this->is_serialized($args[6]);
        $id 		= false;

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if( !current_user_can( 'NextGEN Edit album' ) || !nggGallery::current_user_can( 'NextGEN Add/Delete album' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage albums' ) );

		if ( !empty( $name ) )
			$id = $result = nggdb::add_album( $name, $preview, $description, $galleries );

		if ( !$id )
			return new IXR_Error(500, __('Sorry, could not create the album'));

		return($id);

	}

	/**
	 * Method "ngg.editAlbum"
	 * Edit a existing Album
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- int album ID
	 *	    	- string album name
     *          - int id of preview image
     *          - string description
     *          - string serialized array of galleries or a comma-separated string of gallery IDs
	 * @return true if success
	 */
	function editAlbum($args) {

		global $ngg;

		require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$id      	= (int) $args[3];
		$name   	= $args[4];
		$preview   	= (int) $args[5];
        $description= $args[6];
        $galleries 	= $this->is_serialized($args[7]);

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if ( !$album = nggdb::find_album($id) )
			return(new IXR_Error(404, __("Invalid album ID")));

		if( !current_user_can( 'NextGEN Edit album' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage albums' ) );

		if ( !empty( $name ) )
			$result = nggdb::update_album($id, $name, $preview, $description, $galleries);

		if ( !$result )
			return new IXR_Error(500, __('Sorry, could not update the album'));

		return true;

	}

	/**
	 * Method "ngg.deleteAlbum"
	 * Delete a album from the database
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- int album id
	 * @return true
	 */
	function deleteAlbum($args) {

		global $nggdb;

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
        $id    	    = (int) $args[3];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if ( !$album = nggdb::find_album($id) )
			return(new IXR_Error(404, __("Invalid album ID")));

		if( !current_user_can( 'NextGEN Edit album' ) && !nggGallery::current_user_can( 'NextGEN Add/Delete album' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage albums' ) );

		$nggdb->delete_album($id);

		return true;

	}

	/**
	 * Method "ngg.deleteGallery"
	 * Delete a gallery from the database, including all images
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- int gallery_id
	 * @return true
	 */
	function deleteGallery($args) {

		global $nggdb;

        require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
        $id    	    = (int) $args[3];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if ( !$gallery = nggdb::find_gallery($id) )
			return(new IXR_Error(404, __("Invalid gallery ID")));

		if ( !current_user_can( 'NextGEN Manage gallery' ) && !nggAdmin::can_manage_this_gallery($gallery->author) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage galleries' ) );

		$nggdb->delete_gallery($id);

		return true;

	}

	/**
	 * Method "ngg.getAlbums"
	 * Return the list of all albums
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 * @return array with all galleries
	 */
	function getAlbums($args) {

		global $nggdb;

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if( !current_user_can( 'NextGEN Edit album' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage albums' ) );

		$album_list = $nggdb->find_all_album('id', 'ASC', 0, 0 );

		return($album_list);

	}

	/**
	 * Method "ngg.getAlbum"
	 * Return the specified album
	 *
	 * @since 1.9.2
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *          - int album_id
	 * @return array with the album object
	 */
	function getAlbum($args) {


        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$id         = (int) $args[3];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if( !current_user_can( 'NextGEN Edit album' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage albums' ) );

		$album = nggdb::find_album( $id );

		return($album);

	}

	/**
	 * Method "ngg.getGalleries"
	 * Return the list of all galleries
	 *
	 * @since 1.4
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 * @return array with all galleries
	 */
	function getGalleries($args) {

		global $nggdb;

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if( !current_user_can( 'NextGEN Manage gallery' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage galleries' ) );

		$gallery_list = $nggdb->find_all_galleries('gid', 'asc', true, 0, 0, false);

		return($gallery_list);

	}

	/**
	 * Method "ngg.getGallery"
	 * Return the specified gallery
	 *
	 * @since 1.9.2
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *          - int gallery_id
	 * @return array with the gallery object
	 */
	function getGallery($args) {

		global $nggdb;

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$gid		= (int) $args[3];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		if( !current_user_can( 'NextGEN Manage gallery' ) )
			return new IXR_Error( 401, __( 'Sorry, you must be able to manage galleries' ) );

		$gallery = $nggdb->find_gallery($gid);

		return($gallery);

	}

	/**
	 * Method "ngg.getImages"
	 * Return the list of all images inside a gallery
	 *
	 * @since 1.4
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *	    	- int gallery_id
	 * @return array with all images
	 */
	function getImages($args) {

		global $nggdb;

		require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$gid    	= (int) $args[3];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		// Look for the gallery , could we find it ?
		if ( !$gallery = nggdb::find_gallery( $gid ) )
			return new IXR_Error(404, __('Could not find gallery ' . $gid ));

		// Now check if you have the correct capability for this gallery
		if ( !nggAdmin::can_manage_this_gallery($gallery->author) ) {
			logIO('O', '(NGG) User does not have upload_files capability');
			$this->error = new IXR_Error(401, __('You are not allowed to upload files to this gallery.'));
			return $this->error;
		}

		// get picture values
		$picture_list = $nggdb->get_gallery( $gid, 'pid', 'ASC', false );

		return($picture_list);

	}

	/**
	 * Method "ngg.getImage"
	 * Return a single image inside a gallery
	 *
	 * @since 1.9.2
	 *
	 * @param array $args Method parameters.
	 * 			- int blog_id
	 *	    	- string username
	 *	    	- string password
	 *          - int picture_id
	 * @return array with image properties
	 */
	function getImage($args) {

		global $nggdb;

		require_once ( dirname ( dirname( __FILE__ ) ). '/admin/functions.php' );	// admin functions

        $this->escape($args);
		$blog_ID    = (int) $args[0];
		$username	= $args[1];
		$password	= $args[2];
		$pid    	= (int) $args[3];

		if ( !$user = $this->login($username, $password) )
			return $this->error;

		// get picture
		$image = $nggdb->find_image( $pid );

		if ($image) {
			$gid = $image->galleryid;

			// Look for the gallery , could we find it ?
			if ( !$gallery = nggdb::find_gallery( $gid ) )
				return new IXR_Error(404, __('Could not find gallery ' . $gid ));

			// Now check if you have the correct capability for this gallery
			if ( !nggAdmin::can_manage_this_gallery($gallery->author) ) {
				logIO('O', '(NGG) User does not have upload_files capability');
				$this->error = new IXR_Error(401, __('You are not allowed to upload files to this gallery.'));
				return $this->error;
			}
		}

		return($image);

	}

	/**
	 * Sanitize string or array of strings for database.
	 *
	 * @since 1.7.0
     * @author WordPress Core
     * @filesource inludes/class-wp-xmlrpc-server.php
	 *
	 * @param string|array $array Sanitize single string or array of strings.
	 * @return string|array Type matches $array and sanitized for the database.
	 */
	function escape(&$array) {
		global $wpdb;

		if (!is_array($array)) {
			return($wpdb->escape($array));
		} else {
			foreach ( (array) $array as $k => $v ) {
				if ( is_array($v) ) {
					$this->escape($array[$k]);
				} else if ( is_object($v) ) {
					//skip
				} else {
					$array[$k] = $wpdb->escape($v);
				}
			}
		}
	}

	/**
	 * PHP5 style destructor and will run when database object is destroyed.
	 *
	 * @return bool Always true
	 */
	function __destruct() {

	}
}

$nggxmlrpc = new nggXMLRPC();
