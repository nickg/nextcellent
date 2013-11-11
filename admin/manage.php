<?php  

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 	die('You are not allowed to call this page directly.'); }

class nggManageGallery {

	var $mode = 'main';
	var $gid = false;
	var $pid = false;
	var $base_page = 'admin.php?page=nggallery-manage-gallery';
	var $search_result = false;
	
	// initiate the manage page
	function nggManageGallery() {
        
		// GET variables
		if( isset($_GET['gid']) )
			$this->gid  = (int) $_GET['gid'];
		if( isset($_GET['pid']) )
			$this->pid  = (int) $_GET['pid'];	
		if( isset($_GET['mode']) )
			$this->mode = trim ($_GET['mode']);
        // Check for pagination request, avoid post process of other submit button, exclude search results
        if ( isset($_POST['post_paged']) && !isset($_GET['s'] ) ) {
            if ( $_GET['paged'] != $_POST['post_paged'] ) {		
                $_GET['paged'] = absint( $_POST['post_paged'] );		
                return;		
            }		
        }                        
        // Should be only called via manage galleries overview
		if ( isset($_POST['page']) && $_POST['page'] == 'manage-galleries' )
			$this->post_processor_galleries();
		// Should be only called via a edit single gallery page	
		if ( isset($_POST['page']) && $_POST['page'] == 'manage-images' )
			$this->post_processor_images();
		// Should be called via a publish dialog	
		if ( isset($_POST['page']) && $_POST['page'] == 'publish-post' )
			$this->publish_post();
		//Look for other POST process
		if ( !empty($_POST) || !empty($_GET) )
			$this->processor();
	
	}

	function controller() {

		switch($this->mode) {
			case 'sort':
				include_once (dirname (__FILE__) . '/manage-sort.php');
				nggallery_sortorder($this->gid);
			break;
			case 'edit':
				include_once (dirname (__FILE__) . '/manage-images.php');
				nggallery_picturelist();	
			break;
			case 'main':
			default:
				include_once (dirname (__FILE__) . '/manage-galleries.php');
				nggallery_manage_gallery_main();
			break;
		}
	}

	function processor() {
	
		global $wpdb, $ngg, $nggdb;
        
		// Delete a picture
		if ($this->mode == 'delpic') {

			//TODO:Remove also Tag reference
			check_admin_referer('ngg_delpicture');
			$image = $nggdb->find_image( $this->pid );
			if ($image) {
				if ($ngg->options['deleteImg']) {
					@unlink($image->imagePath);
					@unlink($image->thumbPath);	
					@unlink($image->imagePath . '_backup' );
				} 
				do_action('ngg_delete_picture', $this->pid);
                $result = nggdb::delete_image ( $this->pid );
            }
                                
			if ($result)
				nggGallery::show_message( __('Picture','nggallery').' \''.$this->pid.'\' '.__('deleted successfully','nggallery') );
            
		 	$this->mode = 'edit'; // show pictures
	
		}
		
		// Recover picture from backup
		if ($this->mode == 'recoverpic') {

			check_admin_referer('ngg_recoverpicture');
			$image = $nggdb->find_image( $this->pid );
            // bring back the old image
			nggAdmin::recover_image($image);
            nggAdmin::create_thumbnail($image);
            
            nggGallery::show_message(__('Operation successful. Please clear your browser cache.',"nggallery"));
				
		 	$this->mode = 'edit'; // show pictures
	
		}
				
		// will be called after a ajax operation
		if (isset ($_POST['ajax_callback']))  {
				if ($_POST['ajax_callback'] == 1)
					nggGallery::show_message(__('Operation successful. Please clear your browser cache.',"nggallery"));
		}
		
		// show sort order
		if ( isset ($_POST['sortGallery']) )
			$this->mode = 'sort';
		
		if ( isset ($_GET['s']) )	
			$this->search_images();
		
	}
	
	function post_processor_galleries() {
		global $wpdb, $ngg, $nggdb;
		
		// bulk update in a single gallery
		if (isset ($_POST['bulkaction']) && isset ($_POST['doaction']))  {

			check_admin_referer('ngg_bulkgallery');
			
			switch ($_POST['bulkaction']) {
				case 'no_action';
				// No action
					break;
				case 'recover_images':
				// Recover images from backup
					// A prefix 'gallery_' will first fetch all ids from the selected galleries
					nggAdmin::do_ajax_operation( 'gallery_recover_image' , $_POST['doaction'], __('Recover from backup','nggallery') );
					break;
				case 'set_watermark':
				// Set watermark
					// A prefix 'gallery_' will first fetch all ids from the selected galleries
					nggAdmin::do_ajax_operation( 'gallery_set_watermark' , $_POST['doaction'], __('Set watermark','nggallery') );
					break;
				case 'import_meta':
				// Import Metadata
					// A prefix 'gallery_' will first fetch all ids from the selected galleries
					nggAdmin::do_ajax_operation( 'gallery_import_metadata' , $_POST['doaction'], __('Import metadata','nggallery') );
					break;
				case 'delete_gallery':
				// Delete gallery
					if ( is_array($_POST['doaction']) ) {
                        $deleted = false;
						foreach ( $_POST['doaction'] as $id ) {
                			// get the path to the gallery
                			$gallery = nggdb::find_gallery($id);
                			if ($gallery){
                				//TODO:Remove also Tag reference, look here for ids instead filename
                				$imagelist = $wpdb->get_col("SELECT filename FROM $wpdb->nggpictures WHERE galleryid = '$gallery->gid' ");
                				if ($ngg->options['deleteImg']) {
                					if (is_array($imagelist)) {
                						foreach ($imagelist as $filename) {
                							@unlink(WINABSPATH . $gallery->path . '/thumbs/thumbs_' . $filename);
                							@unlink(WINABSPATH . $gallery->path .'/'. $filename);
                                            @unlink(WINABSPATH . $gallery->path .'/'. $filename . '_backup');
                						}
                					}
                					// delete folder
               						@rmdir( WINABSPATH . $gallery->path . '/thumbs' );
               						@rmdir( WINABSPATH . $gallery->path );
                				}
                			}
                            do_action('ngg_delete_gallery', $id);                	
                			$deleted = nggdb::delete_gallery( $id );
  						}
                        
						if($deleted)
                            nggGallery::show_message(__('Gallery deleted successfully ', 'nggallery'));
							
					}
					break;
			}
		}

		if (isset ($_POST['addgallery']) && isset ($_POST['galleryname'])){
			
			check_admin_referer('ngg_addgallery');

			if ( !nggGallery::current_user_can( 'NextGEN Add new gallery' ))
				wp_die(__('Cheatin&#8217; uh?'));			

			// get the default path for a new gallery
			$defaultpath = $ngg->options['gallerypath'];
			$newgallery = esc_attr( $_POST['galleryname']);
			if ( !empty($newgallery) )
				nggAdmin::create_gallery($newgallery, $defaultpath);
            
            do_action( 'ngg_update_addgallery_page' );
		}

		if (isset ($_POST['TB_bulkaction']) && isset ($_POST['TB_ResizeImages']))  {
			
			check_admin_referer('ngg_thickbox_form');
			
			//save the new values for the next operation
			$ngg->options['imgWidth']  = (int) $_POST['imgWidth'];
			$ngg->options['imgHeight'] = (int) $_POST['imgHeight'];
			// What is in the case the user has no if cap 'NextGEN Change options' ? Check feedback
			update_option('ngg_options', $ngg->options);
			
			$gallery_ids  = explode(',', $_POST['TB_imagelist']);
			// A prefix 'gallery_' will first fetch all ids from the selected galleries
			nggAdmin::do_ajax_operation( 'gallery_resize_image' , $gallery_ids, __('Resize images','nggallery') );
		}

		if (isset ($_POST['TB_bulkaction']) && isset ($_POST['TB_NewThumbnail']))  {
			
			check_admin_referer('ngg_thickbox_form');
			
			//save the new values for the next operation
			$ngg->options['thumbwidth']  = (int)  $_POST['thumbwidth'];
			$ngg->options['thumbheight'] = (int)  $_POST['thumbheight'];
			$ngg->options['thumbfix']    = isset ($_POST['thumbfix']) ? true : false; 
			// What is in the case the user has no if cap 'NextGEN Change options' ? Check feedback
			update_option('ngg_options', $ngg->options);
			
			$gallery_ids  = explode(',', $_POST['TB_imagelist']);
			// A prefix 'gallery_' will first fetch all ids from the selected galleries
			nggAdmin::do_ajax_operation( 'gallery_create_thumbnail' , $gallery_ids, __('Create new thumbnails','nggallery') );
		}

	}

	function post_processor_images() {
		global $wpdb, $ngg, $nggdb;
		
		// bulk update in a single gallery
		if (isset ($_POST['bulkaction']) && isset ($_POST['doaction']))  {
			
			check_admin_referer('ngg_updategallery');
			
			switch ($_POST['bulkaction']) {
				case 'no_action';
					break;
				case 'rotate_cw':
					nggAdmin::do_ajax_operation( 'rotate_cw' , $_POST['doaction'], __('Rotate images', 'nggallery') );
					break;
				case 'rotate_ccw':
					nggAdmin::do_ajax_operation( 'rotate_ccw' , $_POST['doaction'], __('Rotate images', 'nggallery') );
					break;			
				case 'recover_images':
					nggAdmin::do_ajax_operation( 'recover_image' , $_POST['doaction'], __('Recover from backup', 'nggallery') );
					break;
				case 'set_watermark':
					nggAdmin::do_ajax_operation( 'set_watermark' , $_POST['doaction'], __('Set watermark', 'nggallery') );
					break;
				case 'delete_images':
					if ( is_array($_POST['doaction']) ) {
						foreach ( $_POST['doaction'] as $imageID ) {
							$image = $nggdb->find_image( $imageID );
							if ($image) {
								if ($ngg->options['deleteImg']) {
									@unlink($image->imagePath);
									@unlink($image->thumbPath);
									@unlink($image->imagePath."_backup");	
								} 
                                do_action('ngg_delete_picture', $image->pid);
								$delete_pic = nggdb::delete_image( $image->pid );
							}
						}
						if($delete_pic)
							nggGallery::show_message(__('Pictures deleted successfully ', 'nggallery'));
					}
					break;
				case 'import_meta':
					nggAdmin::do_ajax_operation( 'import_metadata' , $_POST['doaction'], __('Import metadata', 'nggallery') );
					break;
			}
		}

		if (isset ($_POST['TB_bulkaction']) && isset ($_POST['TB_ResizeImages']))  {
			
			check_admin_referer('ngg_thickbox_form');
			
			//save the new values for the next operation
			$ngg->options['imgWidth']  = (int) $_POST['imgWidth'];
			$ngg->options['imgHeight'] = (int) $_POST['imgHeight'];
			
			update_option('ngg_options', $ngg->options);
			
			$pic_ids  = explode(',', $_POST['TB_imagelist']);
			nggAdmin::do_ajax_operation( 'resize_image' , $pic_ids, __('Resize images','nggallery') );
		}

		if (isset ($_POST['TB_bulkaction']) && isset ($_POST['TB_NewThumbnail']))  {
			
			check_admin_referer('ngg_thickbox_form');
			
			//save the new values for the next operation
			$ngg->options['thumbwidth']  = (int) $_POST['thumbwidth'];
			$ngg->options['thumbheight'] = (int) $_POST['thumbheight'];
			$ngg->options['thumbfix']    = isset ( $_POST['thumbfix'] ) ? true : false; 
			update_option('ngg_options', $ngg->options);
			
			$pic_ids  = explode(',', $_POST['TB_imagelist']);
			nggAdmin::do_ajax_operation( 'create_thumbnail' , $pic_ids, __('Create new thumbnails','nggallery') );
		}
		
		if (isset ($_POST['TB_bulkaction']) && isset ($_POST['TB_SelectGallery']))  {
			
			check_admin_referer('ngg_thickbox_form');
			
			$pic_ids  = explode(',', $_POST['TB_imagelist']);
			$dest_gid = (int) $_POST['dest_gid'];
			
			switch ($_POST['TB_bulkaction']) {
				case 'copy_to':
				// Copy images
					nggAdmin::copy_images( $pic_ids, $dest_gid );
					break;
				case 'move_to':
				// Move images
					nggAdmin::move_images( $pic_ids, $dest_gid );
					break;
			}
		}
		
		if (isset ($_POST['TB_bulkaction']) && isset ($_POST['TB_EditTags']))  {
			// do tags update
	
			check_admin_referer('ngg_thickbox_form');
	
			// get the images list		
			$pic_ids = explode(',', $_POST['TB_imagelist']);
			$taglist = explode(',', $_POST['taglist']);
			$taglist = array_map('trim', $taglist);
			
			if (is_array($pic_ids)) {

				foreach($pic_ids as $pic_id) {
					
					// which action should be performed ?
					switch ($_POST['TB_bulkaction']) {
						case 'no_action';
						// No action
							break;
						case 'overwrite_tags':
						// Overwrite tags
							wp_set_object_terms($pic_id, $taglist, 'ngg_tag');
							break;					
						case 'add_tags':
						// Add / append tags
							wp_set_object_terms($pic_id, $taglist, 'ngg_tag', TRUE);
							break;
						case 'delete_tags':
						// Delete tags
							$oldtags = wp_get_object_terms($pic_id, 'ngg_tag', 'fields=names');
							// get the slugs, to vaoid  case sensitive problems
							$slugarray = array_map('sanitize_title', $taglist);
							$oldtags = array_map('sanitize_title', $oldtags);
							// compare them and return the diff
							$newtags = array_diff($oldtags, $slugarray);
							wp_set_object_terms($pic_id, $newtags, 'ngg_tag');
							break;
					}
				}
		
				nggGallery::show_message( __('Tags changed', 'nggallery') );
			}
		}
	
		if (isset ($_POST['updatepictures']) )  {
		// Update pictures	
		
			check_admin_referer('ngg_updategallery');
			
			if ( nggGallery::current_user_can( 'NextGEN Edit gallery options' )  && !isset ($_GET['s']) ) {
				
				if ( nggGallery::current_user_can( 'NextGEN Edit gallery title' )) {
				    // don't forget to update the slug
				    $slug = nggdb::get_unique_slug( sanitize_title( $_POST['title'] ), 'gallery', $this->gid );
				    $wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET title= '%s', slug= '%s' WHERE gid = %d", esc_attr($_POST['title']), $slug, $this->gid) );				    
				}
				if ( nggGallery::current_user_can( 'NextGEN Edit gallery path' ))
					$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET path= '%s' WHERE gid = %d", untrailingslashit ( str_replace('\\', '/', trim( stripslashes($_POST['path']) )) ), $this->gid ) );
				if ( nggGallery::current_user_can( 'NextGEN Edit gallery description' ))
					$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET galdesc= '%s' WHERE gid = %d", esc_attr( $_POST['gallerydesc'] ), $this->gid) );
				if ( nggGallery::current_user_can( 'NextGEN Edit gallery page id' ))	
					$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET pageid= '%d' WHERE gid = %d", (int) $_POST['pageid'], $this->gid) );
				if ( nggGallery::current_user_can( 'NextGEN Edit gallery preview pic' ))
					$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET previewpic= '%d' WHERE gid = %d", (int) $_POST['previewpic'], $this->gid) );
				if ( isset ($_POST['author']) && nggGallery::current_user_can( 'NextGEN Edit gallery author' ) ) 
					$wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggallery SET author= '%d' WHERE gid = %d", (int) $_POST['author'], $this->gid) );
                
                wp_cache_delete($this->gid, 'ngg_gallery');                    
		
			}
		
			$this->update_pictures();
	
			//hook for other plugin to update the fields
			do_action('ngg_update_gallery', $this->gid, $_POST);
	
			nggGallery::show_message(__('Update successful',"nggallery"));
		}
	
		if (isset ($_POST['scanfolder']))  {
		// Rescan folder
			check_admin_referer('ngg_updategallery');
		
			$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$this->gid' ");
			nggAdmin::import_gallery($gallerypath);
		}
	
		if (isset ($_POST['addnewpage']))  {
		// Add a new page
		
			check_admin_referer('ngg_updategallery');
			
			$parent_id      = esc_attr($_POST['parent_id']);
			$gallery_title  = esc_attr($_POST['title']);
			$gallery_name   = $wpdb->get_var("SELECT name FROM $wpdb->nggallery WHERE gid = '$this->gid' ");
			
			// Create a WP page
			global $user_ID;
	
			$page['post_type']    = 'page';
			$page['post_content'] = '[nggallery id=' . $this->gid . ']';
			$page['post_parent']  = $parent_id;
			$page['post_author']  = $user_ID;
			$page['post_status']  = 'publish';
			$page['post_title']   = $gallery_title == '' ? $gallery_name : $gallery_title;
			$page = apply_filters('ngg_add_new_page', $page, $this->gid);
	
			$gallery_pageid = wp_insert_post ($page);
			if ($gallery_pageid != 0) {
				$result = $wpdb->query("UPDATE $wpdb->nggallery SET title= '$gallery_title', pageid = '$gallery_pageid' WHERE gid = '$this->gid'");
				wp_cache_delete($this->gid, 'ngg_gallery');
                nggGallery::show_message( __('New gallery page ID','nggallery'). ' ' . $gallery_pageid . ' -> <strong>' . $gallery_title . '</strong> ' .__('created','nggallery') );
			}
            
            do_action('ngg_gallery_addnewpage', $this->gid);
		}
	}
    
   	/**
   	 * Publish a new post with the shortcode from the selected image
     * 
   	 * @since 1.7.0
   	 * @return void
   	 */
   	function publish_post() {
   	    
   	    check_admin_referer('publish-post');

		// Create a WP page
		global $user_ID, $ngg;
        
		$ngg->options['publish_width']  = (int) $_POST['width'];
		$ngg->options['publish_height'] = (int) $_POST['height'];
		$ngg->options['publish_align'] = $_POST['align'];
        $align = ( $ngg->options['publish_align'] == 'none') ? '' : 'float='.$ngg->options['publish_align']; 

		//save the new values for the next operation
		update_option('ngg_options', $ngg->options);

		$post['post_type']    = 'post';
		$post['post_content'] = '[singlepic id=' . intval($_POST['pid']) . ' w=' . $ngg->options['publish_width'] . ' h=' . $ngg->options['publish_height'] . ' ' . $align . ']';
		$post['post_author']  = $user_ID;
		$post['post_status']  = isset ( $_POST['publish'] ) ? 'publish' : 'draft';
		$post['post_title']   = $_POST['post_title'];
		$post = apply_filters('ngg_add_new_post', $post, $_POST['pid']);

		$post_id = wp_insert_post ($post);
        
		if ($post_id != 0)
            nggGallery::show_message( __('Published a new post','nggallery') );

    }
	
	function update_pictures() {
		global $wpdb, $nggdb;

		//TODO:Error message when update failed
		
		$description = 	isset ( $_POST['description'] ) ? $_POST['description'] : array();
		$alttext = 		isset ( $_POST['alttext'] ) ? $_POST['alttext'] : array();
		$exclude = 		isset ( $_POST['exclude'] ) ? $_POST['exclude'] : false;
		$taglist = 		isset ( $_POST['tags'] ) ? $_POST['tags'] : false;
		$pictures = 	isset ( $_POST['pid'] ) ? $_POST['pid'] : false;

		if ( is_array($pictures) ){
			foreach( $pictures as $pid ){
                $image = $nggdb->find_image( $pid );
                if ($image) {
                    // description field
                    $image->description = $description[$image->pid];
                    
                    // only uptade this field if someone change the alttext
                    if ( $image->alttext != $alttext[$image->pid] ) {
                        $image->alttext = $alttext[$image->pid];
                        $image->image_slug = nggdb::get_unique_slug( sanitize_title( $image->alttext ), 'image', $image->pid );                        
                    }
                    
                    // set exclude flag
                    if ( is_array($exclude) )
    					$image->exclude = ( array_key_exists($image->pid, $exclude) )? 1 : 0;
    				else
    					$image->exclude = 0;
                        
                    // update the database
                    $wpdb->query( $wpdb->prepare ("UPDATE $wpdb->nggpictures SET image_slug = '%s', alttext = '%s', description = '%s', exclude = %d WHERE pid = %d", 
                                                                                 $image->image_slug, $image->alttext, $image->description, $image->exclude, $image->pid) );    
                    // remove from cache    
                    wp_cache_delete($image->pid, 'ngg_image');
                    
                    // hook for other plugins after image is updated
                    do_action('ngg_image_updated', $image); 
                }
                
            }
        }
        
        //TODO: This produce 300-400 queries !
		if ( is_array($taglist) ){
			foreach($taglist as $key=>$value) {
				$tags = explode(',', $value);
				wp_set_object_terms($key, $tags, 'ngg_tag');
			}
		}
        
		return;
	}

	// Check if user can select a author
	function get_editable_user_ids( $user_id, $exclude_zeros = true ) {
		global $wpdb;
	
		$user = new WP_User( $user_id );
	
		if ( ! $user->has_cap('NextGEN Manage others gallery') ) {
			if ( $user->has_cap('NextGEN Manage gallery') || $exclude_zeros == false )
				return array($user->id);
			else
				return false;
		}
	
		$level_key = $wpdb->prefix . 'user_level';
		$query = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '$level_key'";
		if ( $exclude_zeros )
			$query .= " AND meta_value != '0'";
	
		return $wpdb->get_col( $query );
	}

	function search_images() {
		global $nggdb;
		
		if ( empty($_GET['s']) )
			return;
		//on what ever reason I need to set again the query var
		set_query_var('s', $_GET['s']);
		$request = get_search_query();
		
        // look now for the images
        $search_for_images = (array) $nggdb->search_for_images( $request );
        $search_for_tags   = (array) nggTags::find_images_for_tags( $request , 'ASC' );

        // finally merge the two results together
        $this->search_result = array_merge( $search_for_images , $search_for_tags );

        // TODO: Currently we didn't support a proper pagination
        $nggdb->paged['total_objects'] = $nggdb->paged['objects_per_page'] = count ($this->search_result) ;
        $nggdb->paged['max_objects_per_page'] = 1;        
        
		// show pictures page
		$this->mode = 'edit'; 
	}
    
	/**
	 * Display the pagination.
	 *
	 * @since 1.8.0
     * @author taken from WP core (see includes/class-wp-list-table.php)
	 * @return string echo the html pagination bar
	 */
	function pagination( $which, $current, $total_items, $per_page ) {

        $total_pages = ($per_page > 0) ? ceil( $total_items / $per_page ) : 1;

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='%s' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				esc_attr( 'post_paged' ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$output .= "\n<span class='pagination-links'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages )
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		$pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $pagination;
	}

}
?>