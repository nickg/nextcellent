<?php
/*
Plugin Name: NextCellent Gallery
Plugin URI: http://www.wpgetready.com/nextcellent-gallery
Description: A Photo Gallery for WordPress providing NextGEN legacy compatibility from version 1.9.13
Author: WPGReady, Niknetniko based on Alex Rabe & PhotoCrati work.
Author URI: http://www.wpgetready.com
Version: 1.9.35

Copyright (c) 2007-2011 by Alex Rabe & NextGEN DEV-Team
Copyright (c) 2012 Photocrati Media
Copyright (c) 2013-2014 WPGetReady
Copyright (c) 2014-2016 WPGetReady, Niknetniko

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * Indicates that a clean exit occured. Handled by set_exception_handler
 */
if (!class_exists('E_Clean_Exit')) {
    class E_Clean_Exit extends RuntimeException
    {

    }
}

//If NextGEN is activated, deactivate this plugin, and warn about it!
check_nextgen::nextgen_activated();

/**
 * Loads the NextGEN plugin
 */
if (!class_exists('nggLoader')) {

    /**
     * Class nggLoader
     */
    class nggLoader {

		var $version = '1.9.35';
		var $dbversion   = '1.8.3';
		var $minimum_WP  = '4.0';
		var $options     = '';
		var $manage_page;
		var $add_PHP5_notice = false;

        /**
         * class constructor
         */
        function __construct() {

			// Stop the plugin if we missed the requirements
			if ( ( !$this->required_version() ) || ( !$this->check_memory_limit() )  )
				return;

			// Set error handler
			set_exception_handler(array(&$this, 'exception_handler'));

			// Get some constants first
			$this->load_options();
			$this->define_constant();
			$this->define_tables();
			$this->load_dependencies();

			$this->plugin_name = basename(dirname(__FILE__)).'/'.basename(__FILE__);

			// Init options & tables during activation & deregister init option
			register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
			register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );

			// Register a uninstall hook to remove all tables & option automatic
			register_uninstall_hook( $this->plugin_name, array(__CLASS__, 'uninstall') );

			// Start this plugin once all other plugins are fully loaded
			add_action( 'plugins_loaded', array(&$this, 'start_plugin') );

			// Register_taxonomy must be used during the init
			add_action( 'init', array(&$this, 'register_taxonomy') );
			add_action( 'wpmu_new_blog', array(&$this, 'multisite_new_blog'), 10, 6);

			// Add a message for PHP4 Users, can disable the update message later on
			if (version_compare(PHP_VERSION, '5.0.0', '<'))
				add_filter('transient_update_plugins', array(&$this, 'disable_upgrade'));

			//Add some links on the plugin page
			add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);

			// Check for the header / footer
			add_action( 'init', array(&$this, 'test_head_footer_init' ) );

			// Show NextGEN version in header
			add_action('wp_head', array('nggGallery', 'nextgen_version') );

			// Handle upload requests
			add_action('init', array(&$this, 'handle_upload_request'));
		}

	    function show_upgrade_message() {
		    if( is_network_admin() ) {
			    $url = network_admin_url('admin.php?page=' . NGGFOLDER);
		    } else {
			    $url = admin_url('admin.php?page=' . NGGFOLDER);
		    }
		    ?>
			<div id="message" class="update-nag">
				<p><strong><?php _e('NextCellent Gallery requires a database upgrade.', "nggallery") ?> <a href="<?php echo $url ?>"><?php _e('Upgrade now.', 'nggallery'); ?></a></strong></p>
			</div>
			<?php
	    }

        /**
         * Main start invoked after all plugins are loaded.
         */
        function start_plugin() {

			// Load the language file
            load_plugin_textdomain('nggallery', false, NGGFOLDER . '/lang');

			// All credits to the translator
			$this->translator  = '<p class="hint">'. __('<strong>Translation by : </strong><a target="_blank" href="http://alexrabe.de/wordpress-plugins/nextgen-gallery/languages/">See here</a>', 'nggallery') . '</p>';
			$this->translator .= '<p class="hint">'. __('<strong>This translation is not yet updated for Version 1.9.0</strong>. If you would like to help with translation, download the current po from the plugin folder and read <a href="http://alexrabe.de/wordpress-plugins/wordtube/translation-of-plugins/">here</a> how you can translate the plugin.', 'nggallery') . '</p>';

			// Content Filters
			add_filter('ngg_gallery_name', 'sanitize_title');

			// Check if we are in the admin area
			if ( is_admin() ) {

				// Pass the init check or show a message
				if (get_option( 'ngg_init_check' ) != false )
					add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>' . get_option( "ngg_init_check" ) . '</strong></p></div>\';') );

			} else {

				// Add MRSS to wp_head
				if ( $this->options['useMediaRSS'] )
					add_action('wp_head', array('nggMediaRss', 'add_mrss_alternate_link'));

				// Look for XML request, before page is render
				add_action('parse_request',  array(&$this, 'check_request') );

				// Add the script and style files
				add_action('wp_enqueue_scripts', array(&$this, 'load_scripts') );
				add_action('wp_enqueue_scripts', array(&$this, 'load_styles') );

			}

	        if( get_option( 'ngg_db_version' ) != NGG_DBVERSION && isset($_GET['page']) != "nextcellent" ) {

		        $ngg_options = get_option('ngg_options');

		        /**
		         * If the silentUpgrade option is not empty, we try and do the upgrade now.
		         */
		        if ( !empty( $ngg_options['silentUpgrade'] ) ) {
			        include_once( dirname( __FILE__ ) . '/admin/functions.php' );
			        include_once( dirname( __FILE__ ) . '/admin/upgrade.php' );
			        try {
				        ngg_upgrade();
			        } catch (Exception $e) {
				        add_action( 'admin_notices', create_function( '', 'echo \'<div id="message" class="error"><p><strong>' . __( 'Something went wrong while upgrading NextCellent Gallery.', "nggallery" ) . '</strong></p></div>\';' ) );
			        }
		        } else {
			        add_action( 'all_admin_notices', array($this,'show_upgrade_message') );
		        }
	        }
		}

        /**
         * Look for XML request
         * @param $wp
         * 20170920: Deprecated imagerotator.php
         */
        function check_request( $wp ) {

			if ( !array_key_exists('callback', $wp->query_vars) )
				return;



			if ( $wp->query_vars['callback'] == 'json') {
				require_once (dirname (__FILE__) . '/xml/json.php');
				exit();
			}

			if ( $wp->query_vars['callback'] == 'image') {
				require_once (dirname (__FILE__) . '/nggshow.php');
				exit();
			}

			//TODO:see trac #12400 could be an option for WP3.0
			if ( $wp->query_vars['callback'] == 'ngg-ajax') {
				require_once (dirname (__FILE__) . '/xml/ajax.php');
				exit();
			}
		}

        /**
         * Check WP version . Return false if not supported, otherwise true
         * Display msg in case not supported
         * @return bool
         */
        function required_version() {
			global $wp_version;

			// Check for WP version installation
			$wp_ok  =  version_compare($wp_version, $this->minimum_WP, '>=');

			if ( ($wp_ok == FALSE) ) {
				add_action(
					'admin_notices',
					create_function(
						'',
						'global $ngg; printf (\'<div id="message" class="error"><p><strong>\' . __(\'Sorry, NextGEN Gallery works only under WordPress %s or higher\', "nggallery" ) . \'</strong></p></div>\', $ngg->minimum_WP );'
					)
				);
				return false;
			}
			return true;
		}

        /**
         * Checks if there is enough memory to perform the plugin
         * Inner working: get memory value from memory_limit. If -1 there is no memory limit
         * If there is 16MB or less, send msg
         * Returns false if there is enough memory, otherwise false.
         * @return bool
         */
        function check_memory_limit() {

			// get the real memory limit before some increase it
			$this->memory_limit = ini_get('memory_limit');

			// PHP docs : Note that to have no memory limit, set this directive to -1.
			if ($this->memory_limit == -1 ) return true;

			// Yes, we reached Gigabyte limits, so check if it's a megabyte limit
			if (strtolower( substr($this->memory_limit, -1) ) == 'm') {

				$this->memory_limit = (int) substr( $this->memory_limit, 0, -1);

				//This works only with enough memory, 16MB is silly, wordpress requires already 16MB :-)
				if ( ($this->memory_limit != 0) && ($this->memory_limit < 16 ) ) {
					add_action(
						'admin_notices',
						create_function(
							'',
							'echo \'<div id="message" class="error"><p><strong>' . __('Sorry, NextCellent Gallery works only with a Memory Limit of 16 MB or higher', 'nggallery') . '</strong></p></div>\';'
						)
					);
					return false;
				}
			}
			return true;
		}

        /**
         * add dynamic properties to global wpdb object.
         */

        function define_tables() {
			global $wpdb;

			// add database pointer
			$wpdb->nggpictures					= $wpdb->prefix . 'ngg_pictures';
			$wpdb->nggallery					= $wpdb->prefix . 'ngg_gallery';
			$wpdb->nggalbum						= $wpdb->prefix . 'ngg_album';

		}


		function register_taxonomy() {
			global $wp_rewrite;

			// Register the NextGEN taxonomy
			$args = array(
					'label' => __('Picture tag', 'nggallery'),
					'template' => __('Picture tag: %2$l.', 'nggallery'),
					'helps' => __('Separate picture tags with commas.', 'nggallery'),
					'sort' => true,
					'args' => array('orderby' => 'term_order')
					);

			register_taxonomy( 'ngg_tag', 'nggallery', $args );
		}

        /**
         * Define several constants
         * 20140517 - Suppressed unused constant
         */
        function define_constant() {

			global $wp_version;

			// Minimum required database version
			define('NGG_DBVERSION', $this->dbversion);

			// required for Windows & XAMPP
			define('WINABSPATH', str_replace("\\", "/", ABSPATH) );
			define('NGG_CONTENT_DIR', str_replace("\\","/", WP_CONTENT_DIR) );

			// define URL
			define('NGGFOLDER', basename( dirname(__FILE__) ) );

			define('NGGALLERY_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . NGGFOLDER ) ) );
			define('NGGALLERY_URLPATH', trailingslashit( plugins_url( NGGFOLDER ) ) );

			// look for imagerotator
			define('NGGALLERY_IREXIST', !empty( $this->options['irURL'] ));

			// get value for safe mode
			if ( (gettype( ini_get('safe_mode') ) == 'string') ) {
				// if sever did in in a other way
				if ( ini_get('safe_mode') == 'off' ) define('SAFE_MODE', FALSE);
				else define( 'SAFE_MODE', ini_get('safe_mode') );
			} else
			define( 'SAFE_MODE', ini_get('safe_mode') );

			if ( version_compare($wp_version, '3.2.999', '>') )
				define('IS_WP_3_3', TRUE);
		}

        /**
         * Load libraries
         */
        function load_dependencies() {

			// Load global libraries												// average memory usage (in bytes)
			require_once (dirname (__FILE__) . '/lib/core.php');					//  94.840
			require_once (dirname (__FILE__) . '/lib/ngg-db.php');					// 132.400
			require_once (dirname (__FILE__) . '/lib/image.php');					//  59.424
			require_once (dirname (__FILE__) . '/lib/tags.php');				    // 117.136
			require_once (dirname (__FILE__) . '/lib/post-thumbnail.php');			//  n.a.
	        require_once( dirname( __FILE__ ) . '/widgets/class-ngg-slideshow-widget.php' );
	        require_once( dirname( __FILE__ ) . '/widgets/class-ngg-media-rss-widget.php' );
	        require_once( dirname( __FILE__ ) . '/widgets/class-ngg-gallery-widget.php' );
			require_once (dirname (__FILE__) . '/lib/multisite.php');
			require_once (dirname (__FILE__) . '/lib/sitemap.php');

			// Load frontend libraries
			require_once (dirname (__FILE__) . '/lib/navigation.php');		        // 242.016
			require_once (dirname (__FILE__) . '/nggfunctions.php');		        // n.a.
			require_once (dirname (__FILE__) . '/lib/shortcodes.php'); 		        // 92.664

			// Add to the toolbar
			add_action( 'admin_bar_menu', array( &$this, 'admin_bar_menu' ), 999 );

			//Just needed if you access remote to WordPress
			if ( defined('XMLRPC_REQUEST') )
				require_once (dirname (__FILE__) . '/lib/xmlrpc.php');

			// We didn't need all stuff during a AJAX operation
			if ( defined('DOING_AJAX') )
				require_once (dirname (__FILE__) . '/admin/ajax.php');
			else {
				require_once (dirname (__FILE__) . '/lib/meta.php');				// 131.856
				require_once (dirname (__FILE__) . '/lib/media-rss.php');			//  82.768
				require_once (dirname (__FILE__) . '/lib/rewrite.php');				//  71.936
				include_once (dirname (__FILE__) . '/admin/tinymce/tinymce.php'); 	//  22.408

				// Load backend libraries
				if ( is_admin() ) {
					require_once (dirname (__FILE__) . '/admin/class-ngg-admin-launcher.php');
					require_once (dirname (__FILE__) . '/admin/media-upload.php');
					$this->nggAdminPanel = new NGG_Admin_Launcher();
				}
			}
		}

		/**
		 * Add NextCellent to the WordPress toolbar.
		 *
		 * @since 1.9.24 Moved from admin.php
		 */
		function admin_bar_menu() {
			// If the current user can't write posts, this is all of no use, so let's not output an admin menu
			if ( ! current_user_can( 'NextGEN Gallery overview' ) ) {
				return;
			}

			global $wp_admin_bar;

			if ( current_user_can( 'NextGEN Upload images' ) ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'new-content',
					'id'     => 'ngg-menu-add-gallery',
					'title'  => __( 'NextCellent Gallery / Images', 'nggallery' ),
					'href'   => admin_url( 'admin.php?page=nggallery-add-gallery' )
				) );
			}

			//If the user is in the admin screen, there is no need to display this.
			if ( !is_admin() ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'site-name',
					'id'     => 'ngg-menu-overview',
					'title'  => __( 'NextCellent', 'nggallery' ),
					'href'   => admin_url( 'admin.php?page=' . NGGFOLDER )
				) );
				if ( current_user_can( 'NextGEN Manage gallery' ) ) {
					$wp_admin_bar->add_node( array(
						'parent' => 'ngg-menu-overview',
						'id'     => 'ngg-menu-manage-gallery',
						'title'  => __( 'Gallery', 'nggallery' ),
						'href'   => admin_url( 'admin.php?page=nggallery-manage' )
					) );
				}
				if ( current_user_can( 'NextGEN Edit album' ) ) {
					$wp_admin_bar->add_node( array(
						'parent' => 'ngg-menu-overview',
						'id'     => 'ngg-menu-manage-album',
						'title'  => __( 'Albums', 'nggallery' ),
						'href'   => admin_url( 'admin.php?page=nggallery-manage-album' )
					) );
				}
				if ( current_user_can( 'NextGEN Manage tags' ) ) {
					$wp_admin_bar->add_node( array(
						'parent' => 'ngg-menu-overview',
						'id'     => 'ngg-menu-tags',
						'title'  => __( 'Tags', 'nggallery' ),
						'href'   => admin_url( 'admin.php?page=nggallery-tags' )
					) );
				}
				if ( current_user_can( 'NextGEN Change options' ) ) {
					$wp_admin_bar->add_node( array(
						'parent' => 'ngg-menu-overview',
						'id'     => 'ngg-menu-options',
						'title'  => __( 'Settings', 'nggallery' ),
						'href'   => admin_url( 'admin.php?page=nggallery-options' )
					) );
				}
				if ( current_user_can( 'NextGEN Change style' ) ) {
					$wp_admin_bar->add_node( array(
						'parent' => 'ngg-menu-overview',
						'id'     => 'ngg-menu-style',
						'title'  => __( 'Style', 'nggallery' ),
						'href'   => admin_url( 'admin.php?page=nggallery-style' )
					) );
				}
			}
		}

        /**
         * Load scripts depending options defined
         * 20150106: Added js for Qunit
         * 20150107: jquery is almost mandatory... Should it be enqueued only when lightbox is activated?
         */
        function load_scripts() {

			// if you want to prevent Nextcellent load the scripts (for testing or development purposes), add this constant
			if ( defined('NGG_SKIP_LOAD_SCRIPTS') )
				return;

			//	activate Thickbox
			if ($this->options['thumbEffect'] == 'thickbox') {
				wp_enqueue_script( 'thickbox' );
				// Load the thickbox images after all other scripts
				add_action( 'wp_footer', array(&$this, 'load_thickbox_images'), 11 );

			}

			// activate jquery.lightbox
			if ($this->options['thumbEffect'] == 'lightbox') {
				wp_enqueue_script('jquery');
			}

			// activate modified Shutter reloaded if not use the Shutter plugin
			if ( ($this->options['thumbEffect'] == "shutter") && !function_exists('srel_makeshutter') ) {
				wp_register_script('shutter', NGGALLERY_URLPATH .'shutter/shutter-reloaded.js', false ,'1.3.3');
				wp_localize_script('shutter', 'shutterSettings', array(
							'msgLoading' => __('L O A D I N G', 'nggallery'),
							'msgClose' => __('Click to Close', 'nggallery'),
							'imageCount' => '1'
				) );
				wp_enqueue_script( 'shutter' );
			}

			// required for the slideshow
			if ( NGGALLERY_IREXIST == true && $this->options['enableIR'] == '1' && nggGallery::detect_mobile_phone() === false )
				wp_enqueue_script('swfobject');
			else {
				wp_enqueue_script('owl', NGGALLERY_URLPATH .'js/owl.carousel.min.js', array('jquery'), '2');
			}

			// Load AJAX navigation script, works only with shutter script as we need to add the listener
			if ( $this->options['galAjaxNav'] ) {
				if ( ($this->options['thumbEffect'] == "shutter") || function_exists('srel_makeshutter') ) {
					wp_enqueue_script ( 'ngg_script', NGGALLERY_URLPATH . 'js/ngg.js', array('jquery', 'jquery-ui-tooltip'), '2.1');
					wp_localize_script( 'ngg_script', 'ngg_ajax', array('path'		=> NGGALLERY_URLPATH,
																		'callback'  => trailingslashit( home_url() ) . 'index.php?callback=ngg-ajax',
																		'loading'	=> __('loading', 'nggallery'),
					) );
				}
			}

			// If activated, add PicLens/Cooliris javascript to footer
			if ( $this->options['usePicLens'] )
				nggMediaRss::add_piclens_javascript();

			// Added Qunit for javascript unit testing
            $nxc=isset($_GET['nextcellent'])?$_GET['nextcellent']:"";
			if ($nxc) {
				wp_enqueue_script( "qunit-init"       , NGGALLERY_URLPATH . "js/nxc.main.js"        , array ('jquery')); //main q-unit call
				wp_enqueue_script( "qunit"            , NGGALLERY_URLPATH . "js/qunit-1.16.0.js"    , array ('jquery')); //qunit core
				wp_enqueue_script( "nextcellent-test" , NGGALLERY_URLPATH . "js/nxc.test.js", array ('jquery')); //unit testing specific for nextcellent
			}

		}

		function load_thickbox_images() {
			// WP core reference relative to the images. Bad idea
			echo "\n" . '<script type="text/javascript">tb_pathToImage = "' . site_url() . '/wp-includes/js/thickbox/loadingAnimation.gif";tb_closeImage = "' . site_url() . '/wp-includes/js/thickbox/tb-close.png";</script>'. "\n";
		}

		/**
		* Load styles based on options defined
		* 20150106: added style for Qunit
		*/
		function load_styles() {

            //Notice stylesheet selection has this priority:
            //1-sytlesheet loaded from filter ngg_load_stylesheet
            //2-nggalery.css on folder's current theme
            //3-active stylesheet defined on styles.

			if ( $css_file = nggGallery::get_theme_css_file() ) {
				wp_enqueue_style('NextGEN', $css_file , false, '1.0.0', 'screen');
				//load the framework
				wp_enqueue_style('NextCellent-Framework', NGGALLERY_URLPATH . 'css/framework-min.css', false, '1.0.1', 'screen');
			} elseif ($this->options['activateCSS']) {
				//convert the path to an URL
				$replace = content_url();
				$path = str_replace( NGG_CONTENT_DIR , $replace, $this->options['CSSfile']); 
				wp_enqueue_style('NextGEN', $path, false, '1.0.0', 'screen');
				//load the framework
				wp_enqueue_style('NextCellent-Framework', NGGALLERY_URLPATH . 'css/framework-min.css', false, '1.0.1', 'screen');
			}


			//	activate Thickbox
			if ($this->options['thumbEffect'] == 'thickbox')
				wp_enqueue_style( 'thickbox');

			// activate modified Shutter reloaded if not use the Shutter plugin
			if ( ($this->options['thumbEffect'] == 'shutter') && !function_exists('srel_makeshutter') )
				wp_enqueue_style('shutter', NGGALLERY_URLPATH .'shutter/shutter-reloaded.css', false, '1.3.4', 'screen');

			// add qunit style if activated. I put 1.0.0 as formula, but it would mean nothing.

            $nxc=isset($_GET['nextcellent'])?$_GET['nextcellent']:"";
			if ($nxc) {
				wp_enqueue_style ( "qunit", NGGALLERY_URLPATH . "css/qunit-1.16.0.css" , false, '1.0.0' , 'screen' );
			}

		}

		function load_options() {
			// Load the options
			$this->options = get_option('ngg_options');
		}

		// THX to Shiba for the code
		// See: http://shibashake.com/wordpress-theme/write-a-plugin-for-wordpress-multi-site
		function multisite_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			global $wpdb;

			include_once (dirname (__FILE__) . '/admin/class-ngg-installer.php');

			if (is_plugin_active_for_network( $this->plugin_name )) {
				$current_blog = $wpdb->blogid;
				switch_to_blog($blog_id);
				NGG_Installer::install();
				switch_to_blog($current_blog);
			}
		}

		/**
		 * Removes all transients created by NextGEN. Called during activation
		 * and deactivation routines
		 */
		static function remove_transients()
		{
			global $wpdb, $_wp_using_ext_object_cache;

			// Fetch all transients
			$query = "
				SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE '%ngg_request%'
			";
			$transient_names = $wpdb->get_col($query);;

			// Delete all transients in the database
			$query = "
				DELETE FROM {$wpdb->options}
				WHERE option_name LIKE '%ngg_request%'
			";
			$wpdb->query($query);

			// If using an external caching mechanism, delete the cached items
			if ($_wp_using_ext_object_cache) {
				foreach ($transient_names as $transient) {
					wp_cache_delete($transient, 'transient');
					wp_cache_delete(substr($transient, 11), 'transient');
				}
			}
		}

        /**
         * Activation hook
         * register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
         * Disable Plugin if PHP version is lower than 5.2
         * However, why the plugin spread initial validation over so different places? Not need to do that...
         */
        function activate() {
			global $wpdb;
			//Starting from version 1.8.0 it's works only with PHP5.2
			if (version_compare(PHP_VERSION, '5.2.0', '<')) {
					deactivate_plugins($this->plugin_name); // Deactivate ourself
					wp_die("Sorry, but you can't run this plugin, it requires PHP 5.2 or higher.");
					return;
			}

			// Clean up transients
			self::remove_transients();

			include_once (dirname (__FILE__) . '/admin/class-ngg-installer.php');

			if (is_multisite()) {
				$network=isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:"";
				$activate=isset($_GET['action'])?$_GET['action']:"";
				$isNetwork=($network=='/wp-admin/network/plugins.php')?true:false;
				$isActivation=($activate=='deactivate')?false:true;

				if ($isNetwork and $isActivation){
					$old_blog = $wpdb->blogid;
					$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs", NULL));
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						NGG_Installer::install();
					}
					switch_to_blog($old_blog);
					return;
				}
			}

			// check for tables
			NGG_Installer::install();
			// remove the update message
			delete_option( 'ngg_update_exists' );

		}

        /**
         * delete init options and transients
         */
        function deactivate() {

			// remove & reset the init check option
			delete_option( 'ngg_init_check' );
			delete_option( 'ngg_update_exists' );

			// Clean up transients
			self::remove_transients();
		}

        /**
         * Uninstall procedure. Pay attention this method is static on the class
         * See register_uninstall_hook( $this->plugin_name, array(__CLASS__, 'uninstall') );
         */
        static function uninstall() {
			// Clean up transients
			self::remove_transients();

			include_once (dirname (__FILE__) . '/admin/class-ngg-installer.php');
			NGG_Installer::uninstall();
		}

        /**
         * @param $option
         * @return mixed
         */
        function disable_upgrade($option){

			// PHP5.2 is required for NGG V1.4.0
			if ( version_compare($option->response[ $this->plugin_name ]->new_version, '1.4.0', '>=') )
				return $option;

			if( isset($option->response[ $this->plugin_name ]) ){
				//Clear it''s download link
				$option->response[ $this->plugin_name ]->package = '';

				//Add a notice message
				if ($this->add_PHP5_notice == false){
					add_action( "in_plugin_update_message-$this->plugin_name", create_function('', 'echo \'<br /><span style="color:red">Please update to PHP5.2 as soon as possible, the plugin is not tested under PHP4 anymore</span>\';') );
					$this->add_PHP5_notice = true;
				}
			}
			return $option;
		}

		// Add links to Plugins page
		function add_plugin_links($links, $file) {

			if ( $file == $this->plugin_name ) {
				$plugin_name = plugin_basename(NGGALLERY_ABSPATH);
				$links[] = "<a href='admin.php?page={$plugin_name}'>" . __('Overview', 'nggallery') . '</a>';
				$links[] = '<a href="http://wordpress.org/support/plugin/nextcellent-gallery-nextgen-legacy">' . __('Get help', 'nggallery') . '</a>';
				//$links[] = '<a href="">' . __('Contribute', 'nggallery') . '</a>';
			}
			return $links;
		}

		// Check for the header / footer, parts taken from Matt Martz (http://sivel.net/)
		function test_head_footer_init() {

			// If test-head query var exists hook into wp_head
			if ( isset( $_GET['test-head'] ) )
				add_action( 'wp_head', create_function('', 'echo \'<!--wp_head-->\';'), 99999 );

			// If test-footer query var exists hook into wp_footer
			if ( isset( $_GET['test-footer'] ) )
				add_action( 'wp_footer', create_function('', 'echo \'<!--wp_footer-->\';'), 99999 );
		}

		/**
		* Handles upload requests
		*/
		function handle_upload_request()
		{
			if (isset($_GET['nggupload'])) {
				require_once(implode(DIRECTORY_SEPARATOR, array(
					NGGALLERY_ABSPATH,
					'admin',
					'upload.php'
				)));
				throw new E_Clean_Exit();
			}
		}

		/**
		* Handles clean exits gracefully. Re-raises anything else
		* @param Exception $ex
		*/
		function exception_handler($ex)
		{
			if (get_class($ex) != 'E_Clean_Exit') throw $ex;
		}
	}

	// Let's start the holy plugin
	global $ngg;
	$ngg = new nggLoader();
}



/**
 * Checks if there is a NextGEN version running. If so, it deactivates itself
 * TODO: Has to be improved. error msg needs to be translated.
 */
class check_nextgen {

    static function nextgen_activated() {

        if (!function_exists('get_plugin_data')) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $nextcellent_plugin= plugin_basename(__FILE__);

        $plugin_list = get_plugins();

        //Loop over all the active plugins
        foreach ($plugin_list as $plugin_file=>$plugin_data) {
            //If we found nextcellent, skip it
            if ($plugin_file==$nextcellent_plugin) continue;
            //If the plugin is deactivated ignore it.
            if (!is_plugin_active($plugin_file)) continue;
            if (strpos($plugin_file,'nggallery.php')!==FALSE) {
                $version = $plugin_data['Version'];
                //Check if effectively could be nextgen
                $is_nextgen= (strpos(strtolower($plugin_data['Name']),'nextgen') !==FALSE);
                if ($is_nextgen) { //is it?
                    //Yes, display msg on admin console
                    add_action(
                        'admin_notices',
                        create_function(
                            '',
                            'echo \'<div id="message" class="error"><p><strong>' . __('Sorry, NextCellent Gallery is deactivated: NextGEN version ' . $version . ' was detected. Deactivate it before running NextCellent!', 'nggallery') . '</strong></p></div>\';'
                        )
                    );
                    //Deactivate this plugin
                    deactivate_plugins($nextcellent_plugin);
                    return true;
               }
            }
        }
        return false;
    }
}
?>