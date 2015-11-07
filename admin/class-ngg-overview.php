<?php

include_once('interface-ngg-displayable.php');

/**
 * Class to display the overview.
 * @since 1.9.24
 */
class NGG_Overview implements NGG_Displayable {

	public function __construct() {

		add_meta_box( 'overview', __( 'At a Glance', 'nggallery' ), array(
			$this,
			'stats'
		), 'ngg_overview', 'normal', 'core' );
		add_meta_box( 'ngg_meta_box', __( 'Help me help YOU!', 'nggallery' ), array(
			$this,
			'like_this'
		), 'ngg_overview', 'side', 'core' );
		add_meta_box( 'dashboard_primary', __( 'Latest News', 'nggallery' ), array(
			$this,
			'js_loading'
		), 'ngg_overview', 'normal', 'core' );
		if ( ! is_multisite() || is_super_admin() ) {
			add_meta_box( 'ngg_plugin_check', __( 'Plugin Check', 'nggallery' ), array(
				$this,
				'ngg_plugin_check'
			), 'ngg_overview', 'side', 'core' );
			add_meta_box( 'ngg_server', __( 'Server Settings', 'nggallery' ), array(
				$this,
				'ngg_overview_server'
			), 'ngg_overview', 'side', 'core' );
			add_meta_box( 'dashboard_plugins', __( 'Related plugins', 'nggallery' ), array(
				$this,
				'js_loading'
			), 'ngg_overview', 'normal', 'core' );
		}
		add_meta_box( 'dashboard_contributors', __( 'Contributors', 'nggallery' ), array( $this, 'contributors' ), 'ngg_overview', 'normal', 'core' );
	}

	/**
	 * Show a summary of the usage.
	 */
	public function stats() {
		global $wpdb;
		//TODO: Move to database.
		$images    = intval( $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->nggpictures" ) );
		$galleries = intval( $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->nggallery" ) );
		$albums    = intval( $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->nggalbum" ) );
		?>
		<div id="overview_right_now" class="main">
			<p><?php _e( 'Here you can control your images, galleries and albums.', 'nggallery' ) ?></p>
			<ul>
				<li class="image-count"><a href="admin.php?page=nggallery-add-gallery">
					<?php echo $images . ' ' . _n( 'Image', 'Images', $images, 'nggallery' ); ?></a>
				</li>
				<li class="gallery-count"><a href="admin.php?page=nggallery-manage">
					<?php echo $galleries . ' ' . _n( 'Gallery', 'Galleries', $galleries, 'nggallery' ); ?></a>
				</li>
				<li class="album-count"><a href="admin.php?page=nggallery-manage-album">
					<?php echo $albums . ' ' . _n( 'Album', 'Albums', $albums, 'nggallery' ); ?></a>
				</li>
			</ul>
		</div>
		<?php if ( current_user_can( 'NextGEN Upload images' ) ) {
			echo '<a class="button button-primary" href="admin.php?page=nggallery-add-gallery">' . __( 'Add new pictures', 'nggallery' ) . '</a>';
		}
		if ( is_multisite() ) {
			$this->ngg_dashboard_quota();
		}
	}

	/**
	 * Output the quote used.
	 */
	private function ngg_dashboard_quota() {

		if ( get_site_option( 'upload_space_check_disabled' || ! wpmu_enable_function( 'wpmuQuotaCheck' ) ) ) {
			return;
		}

		$quota = get_space_allowed();
		$used  = get_dirsize( BLOGUPLOADDIR ) / 1024 / 1024;

		if ( $used > $quota ) {
			$percent_used = '100';
		} else {
			$percent_used = ( $used / $quota ) * 100;
		}
		if ( $percent_used < 70 ) {
			if ( $percent_used >= 40 ) {
				$used_color = ( 'waiting' );
			} else {
				$used_color = ( 'approved' );
			}
		} else {
			$used_color = 'spam';
		}
		$used         = round( $used, 2 );
		$percent_used = number_format( $percent_used );

		?>
		<h4><?php _e( 'Storage Space' ); ?></h4>
		<table>
			<tr>
				<td><?php _e( 'Allowed' ); ?></td>
				<td><?php printf( __( '<a href="%1$s" title="Manage Uploads">%2$s MB</a>' ), esc_url( admin_url( 'admin.php?page=nggallery-manage' ) ), $quota ); ?></td>
			</tr>
			<tr>
				<td class="<?php echo $used_color; ?>"><?php _e( 'Used' ); ?></td>
				<td><?php printf( __( '<a href="%1$s" title="Manage Uploads">%2$s MB (%3$s%%)</a>' ), esc_url( admin_url( 'admin.php?page=nggallery-manage' ) ), $used, $percent_used ); ?></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Output the actual RSS news.
	 */
	static public function ngg_overview_news() {

		$rss = fetch_feed( 'http://wpgetready.com/feed/' );

		if ( is_wp_error( $rss ) ) {
			echo '<p>' . sprintf( __( 'The newsfeed could not be loaded.  Check the <a href="%s">front page</a> to check for updates.', 'nggallery' ), 'http://www.wpgetready.com/' ) . '</p>';
		} else {
			echo '<div class="rss-widget">';
			foreach ( $rss->get_items( 0, 3 ) as $item ) {
				$link = $item->get_link();
				while ( stristr( $link, 'http' ) != $link ) {
					$link = substr( $link, 1 );
				}
				$link  = esc_url( strip_tags( $link ) );
				$title = esc_attr( strip_tags( $item->get_title() ) );
				if ( empty( $title ) ) {
					$title = __( 'Untitled' );
				}

				$desc = str_replace( array(
					"\n",
					"\r"
				), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) ) ) ) );
				$desc = wp_html_excerpt( $desc, 360 );

				// Append ellipsis. Change existing [...] to [&hellip;].
				if ( '[...]' == substr( $desc, - 5 ) ) {
					$desc = substr( $desc, 0, - 5 ) . '[&hellip;]';
				} elseif ( '[&hellip;]' != substr( $desc, - 10 ) ) {
					$desc .= ' [&hellip;]';
				}

				$desc = esc_html( $desc );

				$date = $item->get_date();
				$diff = '';

				if ( $date ) {

					$diff = human_time_diff( strtotime( $date, time() ) );

					if ( $date_stamp = strtotime( $date ) ) {
						$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date_stamp ) . '</span>';
					} else {
						$date = '';
					}
				}
				echo '<ul><li>';
				echo '<a class="rsswidget" target="_blank" href="' . $link . '" >' . $title . '</a>';
				echo '<span class="rss-date">' . $date . '</span>';
				echo '<div class="rssSummary"><strong>' . $diff . '</strong> - ' . $desc . '</div></li></ul>';
			}
			echo '</div>';
		}
	}

	/**
	 * Check for compatability.
	 */
	public function ngg_plugin_check() {

		global $ngg;
		?>
		<script type="text/javascript">
			(function ($) {
				nggPluginCheck = {

					settings: {
						img_run:  '<img src="<?php echo esc_url( admin_url( 'images/spinner.gif' ) ); ?>" class="icon" alt="started"/>',
						img_ok: '<img src="<?php echo esc_url( admin_url( 'images/yes.png' ) ); ?>" class="icon" alt="ok"/>',
						img_fail: '<img src="<?php echo esc_url( admin_url( 'images/no.png' ) ); ?>" class="icon" alt="failed" />',
						domain: '<?php echo esc_url( home_url('index.php', is_ssl() ? 'https' : 'http') ); ?>'
					},

					run: function (index, state) {
						ul = $('#plugin_check');
						s = this.settings;
						var step = 1;
						switch (index) {
							case 1:
								this.check1();
								break;
							case 2:
								this.check2(step);
								break;
							case 3:
								this.check3();
								break;
						}
					},

					// this function check if the json API will work with your theme & plugins
					check1: function () {
						this.start(1);
						var req = $.ajax({
							dataType: 'json',
							url: s.domain,
							data: 'callback=json&format=json&method=version',
							cache: false,
							timeout: 10000,
							success: function (msg) {
								if (msg.version == '<?php echo $ngg->version; ?>')
									nggPluginCheck.success(1);
								else
									nggPluginCheck.failed(1);
							},
							error: function (msg) {
								nggPluginCheck.failed(1);
							},
							complete: function () {
								nggPluginCheck.run(2);
							}
						});

					},

					// this function check if GD lib can create images & thumbnails
					check2: function (step) {
						if (step == 1) this.start(2);
						var stop = false;
						var req = $.ajax({
							type: "POST",
							url: ajaxurl,
							data: "action=ngg_image_check&step=" + step,
							cache: false,
							timeout: 10000,
							success: function (msg) {
								if (msg.stat == 'ok') {
									nggPluginCheck.success(2, msg.message);
								} else {
									if (step == 1)
										nggPluginCheck.failed(2);
									stop = true;
								}

							},
							error: function (msg) {
								if (step == 1)
									nggPluginCheck.failed(2);
								stop = true;
							},
							complete: function () {
								step++;
								if (step <= 11 && stop == false)
									nggPluginCheck.check2(step);
								else
									nggPluginCheck.run(3);
							}
						});
					},

					// this function check if wp_head / wp_footer is avaiable
					check3: function () {
						this.start(3);
						var req = $.ajax({
							type: "POST",
							url: ajaxurl,
							data: "action=ngg_test_head_footer",
							cache: false,
							timeout: 10000,
							success: function (msg) {
								if (msg == 'success')
									nggPluginCheck.success(3);
								else
									nggPluginCheck.failed(3, msg);
							},
							error: function (msg) {
								nggPluginCheck.failed(3);
							}
						});
					},

					start: function (id) {

						s = this.settings;
						var field = "#check" + id;

						if (ul.find(field + " img").length == 0)
							$(field).prepend(s.img_run);
						else
							$(field + " img").replaceWith(s.img_run);

						$(field + " .success").hide();
						$(field + " .failed").hide();
						$(field + " .default").replaceWith('<p class="default message"><?php echo esc_js( __('Running...', 'nggallery') ); ?></p> ');
					},

					success: function (id, msg) {

						s = this.settings;
						var field = "#check" + id;

						if (ul.find(field + " img").length == 0)
							$(field).prepend(s.img_ok);
						else
							$(field + " img").replaceWith(s.img_ok);

						$(field + " .default").hide();
						if (msg)
							$(field + " .success").replaceWith('<p class="success message">' + msg + ' </p> ');
						else
							$(field + " .success").show();

					},

					failed: function (id, msg) {

						s = this.settings;
						var field = "#check" + id;

						if (ul.find(field + " img").length == 0)
							$(field).prepend(s.img_fail);
						else
							$(field + " img").replaceWith(s.img_fail);

						$(field + " .default").hide();
						if (msg)
							$(field + " .failed").replaceWith('<p class="failed message">' + msg + ' </p> ');
						else
							$(field + " .failed").show();

					}

				};
			})(jQuery);
		</script>
		<ul id="plugin_check" class="settings">
			<li id="check1">
				<strong><?php _e( 'Check plugin/theme conflict', 'nggallery' ); ?></strong>
				<p class="default message"><?php _e( 'Not tested', 'nggallery' ); ?></p>
				<p class="success message" style="display: none;"><?php _e( 'No conflict could be detected', 'nggallery' ); ?></p>
				<p class="failed message"  style="display: none;"><?php _e( 'Test failed, disable other plugins & switch to default theme', 'nggallery' ); ?></p>
			</li>
			<li id="check2">
				<strong><?php _e( 'Test image function', 'nggallery' ); ?></strong>
				<p class="default message"><?php _e( 'Not tested', 'nggallery' ); ?></p>
				<p class="success message" style="display: none;"><?php _e( 'The plugin could create images.', 'nggallery' ); ?></p>
				<p class="failed message" style="display: none;"><?php _e( 'Could not create image, check your memory limit.', 'nggallery' ); ?></p>
			</li>
			<li id="check3">
				<strong><?php _e( 'Check theme compatibility', 'nggallery' ); ?></strong>
				<p class="default message"><?php _e( 'Not tested', 'nggallery' ); ?></p>
				<p class="success message" style="display: none;"><?php _e( 'Your theme should work fine with NextCellent Gallery', 'nggallery' ); ?></p>
				<p class="failed message" style="display: none;"><?php _e( 'wp_head()/wp_footer() is missing, contact the theme author', 'nggallery' ); ?></p>
			</li>
		</ul>
		<p class="textright">
			<input type="button" name="update" value="<?php _e( 'Check plugin', 'nggallery' ); ?>" onclick="nggPluginCheck.run(1);" class="button-secondary"/>
		</p>
	<?php
	}

	/**
	 * Show the server settings.
	 */
	public function ngg_overview_server() {
		?>
		<div id="dashboard_server_settings" class="dashboard-widget-holder wp_dashboard_empty">
			<div class="ngg-dashboard-widget">
				<div class="dashboard-widget-content">
					<ul class="settings">
						<?php $this->ngg_get_serverinfo(); ?>
					</ul>
					<p><strong><?php _e( 'Graphic Library', 'nggallery' ); ?></strong></p>
					<ul class="settings">
						<?php $this->ngg_gd_info(); ?>
					</ul>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Show GD Library version information.
	 */
	private function ngg_gd_info() {

		if ( function_exists( "gd_info" ) ) {
			$info = gd_info();
			$keys = array_keys( $info );
			for ( $i = 0; $i < count( $keys ); $i ++ ) {
				if ( is_bool( $info[ $keys[ $i ] ] ) ) {
					echo "<li> " . $keys[ $i ] . ": <span>" . $this->ngg_gd_yesNo( $info[ $keys[ $i ] ] ) . "</span></li>\n";
				} else {
					echo "<li> " . $keys[ $i ] . ": <span>" . $info[ $keys[ $i ] ] . "</span></li>\n";
				}
			}
		} else {
			_e( 'There is no GD support', 'nggallery' );
		}
	}

	/**
	 * Return localized Yes or No.
	 *
	 * @param bool $bool
	 *
	 * @return string 'Yes'|'No'
	 */
	private function ngg_gd_yesNo( $bool ) {
		if ( $bool ) {
			return __( 'Yes', 'nggallery' );
		} else {
			return __( 'No', 'nggallery' );
		}
	}

	/**
	 * Show some server information.
	 *
	 * @see GamerZ (http://www.lesterchan.net)
	 */
	private function ngg_get_serverinfo() {

		global $wpdb, $ngg;
		// Get MYSQL Version
		$sqlversion = $wpdb->get_var( "SELECT VERSION() AS version" );
		// GET SQL Mode
		$mysqlinfo = $wpdb->get_results( "SHOW VARIABLES LIKE 'sql_mode'" );
		if ( is_array( $mysqlinfo ) ) {
			$sql_mode = $mysqlinfo[0]->Value;
		}
		if ( empty( $sql_mode ) ) {
			$sql_mode = __( 'Not set', 'nggallery' );
		}
		// Get PHP allow_url_fopen
		if ( ini_get( 'allow_url_fopen' ) ) {
			$allow_url_fopen = __( 'On', 'nggallery' );
		} else {
			$allow_url_fopen = __( 'Off', 'nggallery' );
		}
		// Get PHP Max Upload Size
		if ( ini_get( 'upload_max_filesize' ) ) {
			$upload_max = ini_get( 'upload_max_filesize' );
		} else {
			$upload_max = __( 'N/A', 'nggallery' );
		}
		// Get PHP Output buffer Size
		if ( ini_get( 'pcre.backtrack_limit' ) ) {
			$backtrack_limit = ini_get( 'pcre.backtrack_limit' );
		} else {
			$backtrack_limit = __( 'N/A', 'nggallery' );
		}
		// Get PHP Max Post Size
		if ( ini_get( 'post_max_size' ) ) {
			$post_max = ini_get( 'post_max_size' );
		} else {
			$post_max = __( 'N/A', 'nggallery' );
		}
		// Get PHP Max execution time
		if ( ini_get( 'max_execution_time' ) ) {
			$max_execute = ini_get( 'max_execution_time' );
		} else {
			$max_execute = __( 'N/A', 'nggallery' );
		}
		// Get PHP Memory Limit
		if ( ini_get( 'memory_limit' ) ) {
			$memory_limit = $ngg->memory_limit;
		} else {
			$memory_limit = __( 'N/A', 'nggallery' );
		}
		// Get actual memory_get_usage
		if ( function_exists( 'memory_get_usage' ) ) {
			$memory_usage = round( memory_get_usage() / 1024 / 1024, 2 ) . __( ' MB', 'nggallery' );
		} else {
			$memory_usage = __( 'N/A', 'nggallery' );
		}
		// required for EXIF read
		if ( is_callable( 'exif_read_data' ) ) {
			$exif = __( 'Yes', 'nggallery' ) . " ( v" . substr( phpversion( 'exif' ), 0, 4 ) . ")";
		} else {
			$exif = __( 'No', 'nggallery' );
		}
		// required for meta data
		if ( is_callable( 'iptcparse' ) ) {
			$iptc = __( 'Yes', 'nggallery' );
		} else {
			$iptc = __( 'No', 'nggallery' );
		}
		// required for meta data
		if ( is_callable( 'xml_parser_create' ) ) {
			$xml = __( 'Yes', 'nggallery' );
		} else {
			$xml = __( 'No', 'nggallery' );
		}

		?>
		<li><?php _e( 'Operating System', 'nggallery' ); ?>: <span><?php echo PHP_OS; ?>
				(<?php echo( PHP_INT_SIZE * 8 ) ?> Bit)</span></li>
		<li><?php _e( 'Server', 'nggallery' ); ?>: <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
		<li><?php _e( 'Memory Usage', 'nggallery' ); ?>: <span><?php echo $memory_usage; ?></span></li>
		<li><?php _e( 'MYSQL Version', 'nggallery' ); ?>: <span><?php echo $sqlversion; ?></span></li>
		<li><?php _e( 'SQL Mode', 'nggallery' ); ?>: <span><?php echo $sql_mode; ?></span></li>
		<li><?php _e( 'PHP Version', 'nggallery' ); ?>: <span><?php echo PHP_VERSION; ?></span></li>
		<li><?php _e( 'PHP Allow URL fopen', 'nggallery' ); ?>: <span><?php echo $allow_url_fopen; ?></span></li>
		<li><?php _e( 'PHP Memory Limit', 'nggallery' ); ?>: <span><?php echo $memory_limit; ?></span></li>
		<li><?php _e( 'PHP Max Upload Size', 'nggallery' ); ?>: <span><?php echo $upload_max; ?></span></li>
		<li><?php _e( 'PHP Max Post Size', 'nggallery' ); ?>: <span><?php echo $post_max; ?></span></li>
		<li><?php _e( 'PCRE Backtracking Limit', 'nggallery' ); ?>: <span><?php echo $backtrack_limit; ?></span></li>
		<li><?php _e( 'PHP Max Script Execute Time', 'nggallery' ); ?>: <span><?php echo $max_execute; ?>s</span></li>
		<li><?php _e( 'PHP EXIF Support', 'nggallery' ); ?>: <span><?php echo $exif; ?></span></li>
		<li><?php _e( 'PHP IPTC Support', 'nggallery' ); ?>: <span><?php echo $iptc; ?></span></li>
		<li><?php _e( 'PHP XML Support', 'nggallery' ); ?>: <span><?php echo $xml; ?></span></li>
	<?php
	}

	/**
	 * Show the JS loading spinner.
	 */
	public function js_loading() {
		echo '<p class="widget-loading hide-if-no-js"><img style="vertical-align:middle; margin: 5px 10px 5px 5px;" src="' . admin_url( "images/spinner.gif" ) . '"/><span>' . __( 'Loading&#8230;' ) . '</span></p><p class="describe hide-if-js">' . __('This widget requires JavaScript.') . '</p>';
	}

	/**
	 * Show related plugins.
	 * Based on class-wp-plugin-install-list-table.php
	 */
	static public function ngg_related_plugins() {
		include( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		//Check for the transient.
		$plugins = (array) get_transient( 'ngg_related_plugins' );
		if ( !$plugins || count($plugins) <= 1 ) {

			// Additional info http://dd32.id.au/projects/wordpressorg-plugin-information-api-docs/
			if ( is_wp_error( $api = plugins_api( 'query_plugins', array( 'search' => 'nextgen' ) ) ) ) {
				return;
			}
			$plugins = (array) $api->plugins;
			shuffle( $plugins );
			//var_dump("ok");
			set_transient( 'ngg_related_plugins', $plugins, 60 * 60 * 24 ); //enable to check within a day.
		}

		echo '<div class="error form-invalid"><p>';
		_e( '<strong>Note</strong>: third parties plugins that are compatible with NGG may not be 100&#37; compatible with NextCellent Gallery!', 'nggallery' );
		echo '</p></div><div id="the-list" class="widefat" style="overflow: auto">';

		//List of suppressed plugin on the list.
		$blacklist = array( 'nextgen-gallery', 'nextcellent-gallery-nextgen-legacy' );

		$plugins_allowedtags = array(
			'a'       => array( 'href' => array(), 'title' => array(), 'target' => array() ),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'pre'     => array(),
			'em'      => array(),
			'strong'  => array(),
			'ul'      => array(),
			'ol'      => array(),
			'li'      => array(),
			'p'       => array(),
			'br'      => array()
		);

		$displayed = 0;
		for( $i = 0; $displayed < 3; $i++ ) {

			$plugin = (array) $plugins[ $i ];

			if ( in_array( $plugin['slug'], $blacklist ) ) {
				continue;
			} else {
				$displayed++;
			}

			$title = wp_kses( $plugin['name'], $plugins_allowedtags );

			// Remove any HTML from the description.
			$description = strip_tags( $plugin['short_description'] );
			$version     = wp_kses( $plugin['version'], $plugins_allowedtags );

			$name = strip_tags( $title . ' ' . $version );

			$author = wp_kses( $plugin['author'], $plugins_allowedtags );
			if ( ! empty( $author ) ) {
				$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';
			}

			$action_links = array();

			if ( !is_multisite() && (current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' )) ) {
				$status = install_plugin_install_status( $plugin );

				switch ( $status['status'] ) {
					case 'install':
						if ( $status['url'] ) {
							/* translators: 1: Plugin name and version. */
							$action_links[] = '<a class="install-now button" href="' . $status['url'] . '" aria-label="' . esc_attr( sprintf( __( 'Install %s now' ), $name ) ) . '">' . __( 'Install Now' ) . '</a>';
						}

						break;
					case 'update_available':
						if ( $status['url'] ) {
							/* translators: 1: Plugin name and version */
							$action_links[] = '<a class="button" href="' . $status['url'] . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '">' . __( 'Update Now' ) . '</a>';
						}

						break;
					case 'latest_installed':
					case 'newer_installed':
						$installed = true;
						$action_links[] = '<span class="button button-disabled" title="' . esc_attr__( 'This plugin is already installed and is up to date' ) . ' ">' . _x( 'Installed', 'plugin' ) . '</span>';
						break;
				}
			}

			if( is_multisite() ) {
				$details_link = "https://wordpress.org/plugins/" . $plugin['slug'] . "/";
			} else {
				$details_link = self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] . '&amp;TB_iframe=true&amp;width=600&amp;height=550' );
			}

			/* translators: 1: Plugin name and version. */
			if( is_multisite() ) {
				$action_links[] = '<a href="' . esc_url( $details_link ) . '" target="_blank" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';
			} else {
				$action_links[] = '<a href="' . esc_url( $details_link ) . '" class="thickbox" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';
			}


			?>
			<div class="plugin-card">
				<div class="plugin-card-top">
					<div class="name column-name">
						<h4>
						<?php if( is_multisite() ) {
							echo("<a href='" . esc_url( $details_link ) . "' target='_blank'>" . $title . "</a>");
						} else {
							echo("<a href='" . esc_url( $details_link ) . "' class='thickbox'>" . $title . "</a>");
						} ?>
						</h4>
					</div>
					<div class="action-links">
						<?php
						if ( $action_links ) {
							echo '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
						} ?>
					</div>
					<div class="desc column-description">
						<p><?php echo $description; ?></p>
						<p class="authors"><?php echo $author; ?></p>
					</div>
				</div>
				<div class="plugin-card-bottom">
					<div class="vers column-rating">
						<?php wp_star_rating( array(
							'rating' => $plugin['rating'],
							'type'   => 'percent',
							'number' => $plugin['num_ratings']
						) ); ?>
						<span class="num-ratings">(<?php echo number_format_i18n( $plugin['num_ratings'] ); ?>)</span>
					</div>
					<div class="column-compatibility">
						<?php
						if ( ! empty( $plugin['tested'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['tested'] ) ), $plugin['tested'], '>' ) ) {
							echo '<span class="compatibility-untested">' . __( 'Untested with your version of WordPress' ) . '</span>';
						} elseif ( ! empty( $plugin['requires'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['requires'] ) ), $plugin['requires'], '<' ) ) {
							echo '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of WordPress' ) . '</span>';
						} else {
							echo '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of WordPress' ) . '</span>';
						} ?>
					</div>
				</div>
			</div>
		<?php
		}
		?>
		</div>
		<script type="text/javascript">
			var tb_position;
			jQuery( document ).ready( function( $ ) {
				tb_position = function() {
					var tbWindow = $( '#TB_window' ),
						width = $( window ).width(),
						H = $( window ).height() - ( ( 792 < width ) ? 60 : 20 ),
						W = ( 792 < width ) ? 772 : width - 20;

					if ( tbWindow.size() ) {
						tbWindow.width( W ).height( H );
						$( '#TB_iframeContent' ).width( W ).height( H );
						tbWindow.css({
							'margin-left': '-' + parseInt( ( W / 2 ), 10 ) + 'px'
						});
						if ( typeof document.body.style.maxWidth !== 'undefined' ) {
							tbWindow.css({
								'top': '30px',
								'margin-top': '0'
							});
						}
					}

					return $( 'a.thickbox' ).each( function() {
						var href = $( this ).attr( 'href' );
						if ( ! href ) {
							return;
						}
						href = href.replace( /&width=[0-9]+/g, '' );
						href = href.replace( /&height=[0-9]+/g, '' );
						$(this).attr( 'href', href + '&width=' + W + '&height=' + ( H ) );
					});
				};

				jQuery( window ).resize( function() {
					tb_position();
				});

				jQuery( '.install-now' ).click( function() {
					return confirm( '" . __( "Are you sure you want to install this?", "nggallery" ) . "' );
				});
			});

		</script>
		<?php
	}

	/**
	 * Like me!
	 */
	public function like_this() {

		?>
		<p>
			<?php _e( 'This plugin is a branch from NextGen Gallery, version 1.9.13.', 'nggallery' ); ?><br>
			<?php _e( 'Developed & maintained by <a href="http://www.wpgetready.com" target="_blank">WPGetReady.com</a>', 'nggallery' ); ?>
		</p>
		<table>
			<tr>
				<td><span class="dashicons dashicons-star-filled"></span></td>
				<td><a href="http://www.wordpress.org/plugins/nextcellent-gallery-nextgen-legacy/" target="_blank">
					<?php _e( 'You can contribute by giving this plugin a good rating! Thanks a lot!', 'nggallery' ); ?></a>
				</td>
			</tr>
			<tr>
				<td><span class="dashicons dashicons-admin-home"></span></td>
				<td><a href="http://www.wpgetready.com" target="_blank"><?php _e( "Visit the plugin homepage", 'nggallery' ); ?></a></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Display the page.
	 */
	public function display() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Welcome to NextCellent Gallery!', 'nggallery' ) ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div class="postbox-container" id="postbox-container-2"> <!--style="width:75%;"-->
						<?php do_meta_boxes( 'ngg_overview', 'normal', '' ); ?>
					</div>
					<div class="postbox-container" id="postbox-container-1"> <!--style="width:24%;"-->
						<?php do_meta_boxes( 'ngg_overview', 'side', '' ); ?>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			var ajaxWidgets, ajaxPopulateWidgets;

			jQuery(document).ready(function ($) {
				// These widgets are sometimes populated via ajax
				ajaxWidgets = [
					'dashboard_primary',
					'dashboard_plugins'
				];

				ajaxPopulateWidgets = function (el) {
					show = function (id, i) {
						var p, e = $('#' + id + ' div.inside:visible').find('.widget-loading');
						if (e.length) {
							p = e.parent();
							setTimeout(function () {
								p.load('admin-ajax.php?action=ngg_dashboard&jax=' + id, '', function () {
									p.hide().slideDown('normal', function () {
										$(this).css('display', '');
										if ('dashboard_plugins' == id && $.isFunction(tb_init))
											tb_init('#dashboard_plugins a.thickbox');
									});
								});
							}, i * 500);
						}
					};
					if (el) {
						el = el.toString();
						if ($.inArray(el, ajaxWidgets) != -1)
							show(el, 0);
					} else {
						$.each(ajaxWidgets, function (i) {
							show(this, i);
						});
					}
				};
				ajaxPopulateWidgets();
			});

			jQuery(document).ready(function ($) {
				// postboxes setup
				postboxes.add_postbox_toggles('ngg-overview');
			});
			//]]>
		</script>
	<?php
	}

	public function contributors() {
		?>
		<div class="ngg-dashboard-widget">
			<p><?php _e( 'This plugin is made possible by the great work of a lot of people:', 'nggallery' ); ?></p>
			<ul class="ngg-list">
				<li><?php _e('Alex Rabe and Photocrati for the original NextGen Gallery', 'nggallery')?></li>
				<li><a href="http://wpgetready.com/"
				       target="_blank">WPGetReady</a> <?php _e( 'for maintaining this fork of NextGen Gallery', 'nggallery' ); ?>
				</li>
				<li><a href="https://plus.google.com/u/0/+NikoStrijbol/posts" target="_blank">Niko
						Strijbol</a> <?php _e( 'for helping maintain the plugin', 'nggallery' ); ?></li>
				<li><a href="https://bitbucket.org/leap_dev" target="_blank">Richard
						Bale</a> <?php _e( 'for his implementation of changing file the upload date using jQuery', 'nggallery' ); ?>
				</li>
				<li><a href="http://howden.net.au/thowden/" target="_blank">Tony
						Howden</a> <?php _e( 'for his his code suggestions regarding nggtags shortcodes', 'nggallery' ); ?>
				</li>
				<li><a href="http://gfxproductions.com/" target="_blank">Stefano
						Sudati</a> <?php _e( 'for his his suggestions on templates', 'nggallery' ); ?></li>
				<li><p><?php _e( 'Also a big thank you to the new translators: ', 'nggallery' ); ?>
						<br><?php $this->list_contributors(); ?></p>
				</li>
			</ul>
		</div>
	<?php
	}

	private function list_contributors() {

		$contributors = $this->new_contributors();

		ksort( $contributors );
		$i = count( $contributors );
		foreach ( $contributors as $name => $data ) {
			if ( $data[1] ) {
				echo '<a href="' . $data[1] . '" target="_blank">' . $name . '</a> (' . $data[0] . ')';
			} else {
				echo $name;
			}
			$i --;
			if ( $i == 1 ) {
				echo " & ";
			} elseif ( $i ) {
				echo ", ";
			}
		}
	}

	/* New contributors. */
	private function new_contributors() {
		return array(
			'Vladimir Vasilenko'    => array( 'Russian translation', 'http://shumbely.com/' ),
			'Niko Strijbol'         => array( 'Dutch translation', 'https://plus.google.com/u/0/+NikoStrijbol' ),
			'Vesa Tiirikainen'      => array( 'Finnish translation', 'mailto:vesa@tiirikainen.fi' ),
			'Thomas Blomberg Hansen'=> array( 'Danish translation' , 'mailto:thomasdk81@gmail.com')
		);
	}
}