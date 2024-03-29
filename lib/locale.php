<?php
if ( ! class_exists( 'ngg_locale' ) ) :
	/**
	 * Install locale files from WordPress.org plugin repository
	 * 
	 * @version 1.0.0
	 * @author Alex Rabe
	 * 
	 * @package NextGEN Gallery
	 * @since 1.5.0
	 */

	class ngg_locale {

		/**
		 * Current locale
		 *
		 * @var string
		 */
		private string $locale = '';

		/**
		 * Plugin domain name
		 *
		 * @var string
		 */
		private string $domain = 'nggallery';

		/**
		 * URL to the translation files
		 *
		 * @var string
		 */
		private string $remote_locale_url = 'http://nextgen-gallery.googlecode.com/files/';

		/**
		 * Plugin path to the langauage files 
		 *
		 * @var string
		 */
		private string $plugin_locale_path = 'lang';

		/**
		 * Server path to the locale file on the server
		 *
		 * @var string
		 */
		private string $mo_file = '';

		/**
		 * URL to the locale file from the remote server
		 *
		 * @var string
		 */
		private string $mo_url = '';

		/**
		 * Repsonse code for request
		 *
		 * @var array|WP_Error
		 */
		private $repsonse = '';

		/**
		 * Init the Database Abstraction layer for NextGEN Gallery
		 * 
		 */
		function __construct() {
			$this->plugin_locale_path = NGGALLERY_ABSPATH . 'lang/';
			$this->locale = get_locale();

			$this->mo_file = trailingslashit( $this->plugin_locale_path ) . $this->domain . '-' . $this->locale . '.mo';
			$this->mo_url = trailingslashit( $this->remote_locale_url ) . $this->domain . '-' . $this->locale . '.mo';
		}

		/**
		 * This functions checks if a translation is at wp.org available
		 * Please note, if a language file is already loaded it exits as well
		 *
		 * @return string result of check ( default | installed | not_exist | available )
		 */
		function check() {

			// we do not need to check for translation if you use english
			if ( ( $this->locale == 'en_US' ) )
				return 'default';

			$this->response = wp_remote_get( $this->mo_url, array( 'timeout' => 300 ) );

			// if a language file exist, do not load it again
			if ( is_readable( $this->mo_file ) )
				return 'installed';

			// if no translation file exists exit the check
			if ( is_wp_error( $this->response ) || $this->response['response']['code'] != '200' )
				return 'not_exist';

			return 'available';
		}

		/**
		 * Downloads a locale to the plugin folder using the WordPress HTTP Class.
		 *
		 * @author taken from WP core 
		 * @return mixed WP_Error on failure, true on success.
		 */
		function download_locale() {

			$url = $this->mo_url;

			if ( ! $url )
				return new WP_Error( 'http_no_url', __( 'Invalid URL Provided.' ) );

			$filename = $this->mo_file;
			if ( ! $filename )
				return new WP_Error( 'http_no_file', __( 'Could not create Temporary file.' ) );

			$handle = @fopen( $filename, 'wb' );
			if ( ! $handle )
				return new WP_Error( 'http_no_file', __( 'Could not create Temporary file.' ) );

			$response = wp_remote_get( $url, array( 'timeout' => 300 ) );

			if ( is_wp_error( $response ) ) {
				fclose( $handle );
				unlink( $filename );
				return $response;
			}

			if ( $response['response']['code'] != '200' ) {
				fclose( $handle );
				unlink( $filename );
				return new WP_Error( 'http_404', trim( $response['response']['message'] ) );
			}

			fwrite( $handle, $response['body'] );
			fclose( $handle );

			return true;
		}

	}
endif;
?>