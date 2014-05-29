<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * nggallery_admin_overview()
 * 20130410: FZSM: as part of initiative, avoid implementing spaghuetti code.
 *                 Introducing class to deal with code and functions to provide backward compatibility 100%
 * Add the admin overview the dashboard style
 * @return mixed content
 */
function nggallery_admin_overview()  {
	?>
	<div class="wrap ngg-wrap">
        <?php screen_icon( 'nextgen-gallery' ); ?>
		<h2><?php _e('NextCellent Gallery Overview', 'nggallery') ?></h2>
        <?php if (version_compare(PHP_VERSION, '5.0.0', '<')) ngg_check_for_PHP5(); ?>
		<div id="dashboard-widgets-container" class="ngg-overview">
		    <div id="dashboard-widgets" class="metabox-holder">
				<div id="post-body">
					<div id="dashboard-widgets-main-content">
						<div class="postbox-container" id="main-container" style="width:75%;">
							<?php do_meta_boxes('ngg_overview', 'left', ''); ?>
						</div>
			    		<div class="postbox-container" id="side-container" style="width:24%;">
							<?php do_meta_boxes('ngg_overview', 'right', ''); ?>
						</div>
					</div>
				</div>
		    </div>
		</div>
	</div>
	<script type="text/javascript">
		//<![CDATA[
        var ajaxWidgets, ajaxPopulateWidgets;

        jQuery(document).ready( function($) {
        	// These widgets are sometimes populated via ajax
        	ajaxWidgets = [
        		'dashboard_primary',
        		'ngg_locale',
        		'dashboard_plugins'
        	];

        	ajaxPopulateWidgets = function(el) {
        		show = function(id, i) {
        			var p, e = $('#' + id + ' div.inside:visible').find('.widget-loading');
        			if ( e.length ) {
        				p = e.parent();
        				setTimeout( function(){
        					p.load('admin-ajax.php?action=ngg_dashboard&jax=' + id, '', function() {
        						p.hide().slideDown('normal', function(){
        							$(this).css('display', '');
        							if ( 'dashboard_plugins' == id && $.isFunction(tb_init) )
        								tb_init('#dashboard_plugins a.thickbox');
        						});
        					});
        				}, i * 500 );
        			}
        		}
        		if ( el ) {
        			el = el.toString();
        			if ( $.inArray(el, ajaxWidgets) != -1 )
        				show(el, 0);
        		} else {
        			$.each( ajaxWidgets, function(i) {
        				show(this, i);
        			});
        		}
        	};
        	ajaxPopulateWidgets();
        } );

		jQuery(document).ready( function($) {
			// postboxes setup
			postboxes.add_postbox_toggles('ngg-overview');
		});
		//]]>
	</script>
	<?php
}

/**
 * Load the meta boxes
 *
 */
add_meta_box('dashboard_overview', __('Welcome to NextCellent Gallery !', 'nggallery'), 'ngg_overview_right_now', 'ngg_overview', 'left', 'core');
add_meta_box('ngg_meta_box', __('Help me help YOU!', 'nggallery'), 'nextcellent_overview::likeThisMetaBox', 'ngg_overview', 'right', 'core');
if ( !(get_locale() == 'en_US') )
	add_meta_box('ngg_locale', __('Translation', 'nggallery'), 'ngg_widget_locale', 'ngg_overview', 'right', 'core');
add_meta_box('dashboard_primary', __('Latest News', 'nggallery'), 'ngg_widget_overview_news', 'ngg_overview', 'left', 'core');
if ( !is_multisite() || is_super_admin() ) {
    add_meta_box('ngg_plugin_check', __('Plugin Check', 'nggallery'), 'ngg_plugin_check', 'ngg_overview', 'right', 'core');
    add_meta_box('ngg_server', __('Server Settings', 'nggallery'), 'ngg_overview_server', 'ngg_overview', 'right', 'core');
    add_meta_box('dashboard_plugins', __('Related plugins', 'nggallery'), 'ngg_widget_related_plugins', 'ngg_overview', 'left', 'core');
}



/**
 * Ajax Check for conflict with other plugins/themes
 *
 * @return void
 */
function ngg_plugin_check() {

    global $ngg;
?>
<script type="text/javascript">
(function($) {
	nggPluginCheck = {

		settings: {
				img_run:  '<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="icon" alt="started"/>',
                img_ok:   '<img src="<?php echo esc_url( admin_url( 'images/yes.png' ) ); ?>" class="icon" alt="ok"/>',
                img_fail: '<img src="<?php echo esc_url( admin_url( 'images/no.png' ) ); ?>" class="icon" alt="failed" />',
                domain:   '<?php echo esc_url( home_url('index.php', is_ssl() ? 'https' : 'http') ); ?>'
		},

        run: function( index, state ) {
 			ul = $('#plugin_check');
            s = this.settings;
            var step = 1;
            switch ( index ) {
                case 1:
                    this.check1();
                    break;
                case 2:
                    this.check2( step );
                    break;
                case 3:
                    this.check3();
                    break;
            }
        },

        // this function check if the json API will work with your theme & plugins
        check1 : function() {
            this.start(1);
			var req = $.ajax({
                dataType: 'json',
			   	url: s.domain,
			   	data:'callback=json&format=json&method=version',
			   	cache: false,
			   	timeout: 10000,
			   	success: function(msg){
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
        check2 : function( step ) {
            if (step == 1) this.start(2);
            var stop = false;
			var req = $.ajax({
                type: "POST",
			   	url: ajaxurl,
			   	data:"action=ngg_image_check&step=" + step,
			   	cache: false,
			   	timeout: 10000,
			   	success: function(msg){
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
        check3 : function() {
            this.start(3);
			var req = $.ajax({
                type: "POST",
			   	url: ajaxurl,
			   	data:"action=ngg_test_head_footer",
			   	cache: false,
			   	timeout: 10000,
			   	success: function(msg){
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

		start: function( id ) {

            s = this.settings;
            var field = "#check" + id;

            if ( ul.find(field + " img").length == 0)
                $(field).prepend( s.img_run );
			else
			    $(field + " img").replaceWith( s.img_run );

            $(field + " .success").hide();
            $(field + " .failed").hide();
            $(field + " .default").replaceWith('<p class="default message"><?php echo esc_js( __('Running...', 'nggallery') ); ?></p> ');
		},

		success: function( id, msg ) {

            s = this.settings;
            var field = "#check" + id;

            if ( ul.find(field + " img").length == 0)
                $(field).prepend( s.img_ok );
			else
			    $(field + " img").replaceWith( s.img_ok );

            $(field + " .default").hide();
            if (msg)
                $(field + " .success").replaceWith('<p class="success message">' + msg +' </p> ');
            else
                $(field + " .success").show();

		},

		failed: function( id, msg ) {

            s = this.settings;
            var field = "#check" + id;

            if ( ul.find(field + " img").length == 0)
                $(field).prepend( s.img_fail );
			else
			    $(field + " img").replaceWith( s.img_fail );

            $(field + " .default").hide();
            if (msg)
                $(field + " .failed").replaceWith('<p class="failed message">' + msg +' </p> ');
            else
                $(field + " .failed").show();

		}

	};
})(jQuery);
</script>
<div class="dashboard-widget-holder wp_dashboard_empty">
	<div class="ngg-dashboard-widget">
	  	<div class="dashboard-widget-content">
      		<ul id="plugin_check" class="settings">
                <li id="check1">
                    <strong><?php _e('Check plugin/theme conflict', 'nggallery'); ?></strong>
                    <p class="default message"><?php _e('Not tested', 'nggallery'); ?></p>
                    <p class="success message" style="display: none;"><?php _e('No conflict could be detected', 'nggallery'); ?></p>
                    <p class="failed message" style="display: none;"><?php _e('Test failed, disable other plugins & switch to default theme', 'nggallery'); ?></p>
                </li>
                <li id="check2">
                    <strong><?php _e('Test image function', 'nggallery'); ?></strong>
                    <p class="default message"><?php _e('Not tested', 'nggallery'); ?></p>
                    <p class="success message" style="display: none;"><?php _e('The plugin could create images', 'nggallery'); ?></p>
                    <p class="failed message" style="display: none;"><?php _e('Couldn\'t create image, check your memory limit', 'nggallery'); ?></p>
                </li>
                <li id="check3">
                    <strong><?php _e('Check theme compatibility', 'nggallery'); ?></strong>
                    <p class="default message"><?php _e('Not tested', 'nggallery'); ?></p>
                    <p class="success message" style="display: none;"><?php _e('Your theme should work fine with NextCellent Gallery', 'nggallery'); ?></p>
                    <p class="failed message" style="display: none;"><?php _e('wp_head()/wp_footer() is missing, contact the theme author', 'nggallery'); ?></p>
                </li>
            </ul>
 			<p class="textright">
                <input type="button" name="update" value="<?php _e('Check plugin', 'nggallery'); ?>" onclick="nggPluginCheck.run(1);" class="button-secondary" />
			</p>
		</div>
    </div>
</div>
<?php
}

/**
 * Show the server settings in a dashboard widget
 *
 * @return void
 */
function ngg_overview_server() {
?>
<div id="dashboard_server_settings" class="dashboard-widget-holder wp_dashboard_empty">
	<div class="ngg-dashboard-widget">
	  	<div class="dashboard-widget-content">
      		<ul class="settings">
      		<?php ngg_get_serverinfo(); ?>
            </ul>
            <p><strong><?php _e('Graphic Library', 'nggallery'); ?></strong></p>
            <ul class="settings">
            <?php ngg_gd_info(); ?>
	   		</ul>
		</div>
    </div>
</div>
<?php
}


/**
 * Show the latest NextGEN Gallery news
 *
 * @return void
 */
function ngg_widget_overview_news() {
    echo '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="describe hide-if-js">' . __('This widget requires JavaScript.') . '</p>';
}
function ngg_overview_news(){

?>
<div class="rss-widget">
    <?php
    $rss = @fetch_feed( 'http://wpgetready.com/feed/' );

    if ( is_object($rss) ) {

        if ( is_wp_error($rss) ) {
            echo '<p>' . sprintf(__('Newsfeed could not be loaded.  Check the <a href="%s">front page</a> to check for updates.', 'nggallery'), 'http://www.nextgen-gallery.com/') . '</p>';
    		return;
        }

        echo '<ul>';
		foreach ( $rss->get_items(0, 3) as $item ) {
    		$link = $item->get_link();
    		while ( stristr($link, 'http') != $link )
    			$link = substr($link, 1);
    		$link = esc_url(strip_tags($link));
    		$title = esc_attr(strip_tags($item->get_title()));
    		if ( empty($title) )
    			$title = __('Untitled');

    		$desc = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option('blog_charset') ) ) ) );
    		$desc = wp_html_excerpt( $desc, 360 );

    		// Append ellipsis. Change existing [...] to [&hellip;].
    		if ( '[...]' == substr( $desc, -5 ) )
    			$desc = substr( $desc, 0, -5 ) . '[&hellip;]';
    		elseif ( '[&hellip;]' != substr( $desc, -10 ) )
    			$desc .= ' [&hellip;]';

    		$desc = esc_html( $desc );

			$date = $item->get_date();
            $diff = '';

			if ( $date ) {

                $diff = human_time_diff( strtotime($date, time()) );

				if ( $date_stamp = strtotime( $date ) )
					$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date_stamp ) . '</span>';
				else
					$date = '';
			}
        ?>
          <li><a class="rsswidget" title="" target="_blank" href='<?php echo $link; ?>'><?php echo $title; ?></a>
		  <span class="rss-date"><?php echo $date; ?></span>
          <div class="rssSummary"><strong><?php echo $diff; ?></strong> - <?php echo $desc; ?></div></li>
        <?php
        }
        echo '</ul>';
      }
    ?>
</div>
<?php
}

/**
 * Show a summary of the used images
 *
 * @return void
 */
function ngg_overview_right_now() {
	global $wpdb;
	$images    = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures") );
	$galleries = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggallery") );
	$albums    = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggalbum") );
?>
<div class="table table_content">
	<h4><?php _e('At a Glance', 'nggallery'); ?></h4>
	<table>
		<tbody>
			<tr class="first">
				<td class="first b"><a href="admin.php?page=nggallery-add-gallery"><?php echo $images; ?></a></td>
				<td class="t"><a href="admin.php?page=nggallery-add-gallery"><?php echo _n( 'Image', 'Images', $images, 'nggallery' ); ?></a></td>
				<td class="b"></td>
				<td class="last"></td>
			</tr>
			<tr>
				<td class="first b"><a href="admin.php?page=nggallery-manage-gallery"><?php echo $galleries; ?></a></td>
				<td class="t"><a href="admin.php?page=nggallery-manage-gallery"><?php echo _n( 'Gallery', 'Galleries', $galleries, 'nggallery' ); ?></a></td>
				<td class="b"></td>
				<td class="last"></td>
			</tr>
			<tr>
				<td class="first b"><a href="admin.php?page=nggallery-manage-album"><?php echo $albums; ?></a></td>
				<td class="t"><a href="admin.php?page=nggallery-manage-album"><?php echo _n( 'Album', 'Albums', $albums, 'nggallery' ); ?></a></td>
				<td class="b"></td>
				<td class="last"></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="versions" style="padding-top:14px">
    <p>
	<?php if(current_user_can('NextGEN Upload images')): ?><a class="button rbutton" href="admin.php?page=nggallery-add-gallery"><?php _e('Upload pictures', 'nggallery') ?></a><?php endif; ?>
	<?php _e('Here you can control your images, galleries and albums.', 'nggallery') ?>
	</p>
<br class="clear" />
</div>
<?php
if ( is_multisite() )
    ngg_dashboard_quota();
}

// Display File upload quota on dashboard
function ngg_dashboard_quota() {

	if ( get_site_option( 'upload_space_check_disabled' ) )
		return;

    if ( !wpmu_enable_function('wpmuQuotaCheck') )
        return;

	$quota = get_space_allowed();
	$used = get_dirsize( BLOGUPLOADDIR ) / 1024 / 1024;

	if ( $used > $quota )
		$percentused = '100';
	else
		$percentused = ( $used / $quota ) * 100;
	$used_color = ( $percentused < 70 ) ? ( ( $percentused >= 40 ) ? 'waiting' : 'approved' ) : 'spam';
	$used = round( $used, 2 );
	$percentused = number_format( $percentused );

	?>
	<p class="sub musub" style="position:static" ><?php _e( 'Storage Space' ); ?></p>
	<div class="table table_content musubtable">
	<table>
		<tr class="first">
			<td class="first b b-posts"><?php printf( __( '<a href="%1$s" title="Manage Uploads" class="musublink">%2$sMB</a>' ), esc_url( admin_url( 'admin.php?page=nggallery-manage-gallery' ) ), $quota ); ?></td>
			<td class="t posts"><?php _e( 'Space Allowed' ); ?></td>
		</tr>
	</table>
	</div>
	<div class="table table_discussion musubtable">
	<table>
		<tr class="first">
			<td class="b b-comments"><?php printf( __( '<a href="%1$s" title="Manage Uploads" class="musublink">%2$sMB (%3$s%%)</a>' ), esc_url( admin_url( 'admin.php?page=nggallery-manage-gallery' ) ), $used, $percentused ); ?></td>
			<td class="last t comments <?php echo $used_color;?>"><?php _e( 'Space Used' );?></td>
		</tr>
	</table>
	</div>
	<br class="clear" />
	<?php
}

/**
 * Looks up for translation file
 *
 * @return void
 */
function ngg_widget_locale() {

	require_once(NGGALLERY_ABSPATH . '/lib/locale.php');

	$locale = new ngg_locale();

	$overview_url = admin_url() . 'admin.php?page=' . NGGFOLDER;

	// Check if someone would like to update the translation file
	if ( isset($_GET['locale']) && $_GET['locale'] == 'update' ) {
		check_admin_referer('ngg_update_locale');

		$result = $locale->download_locale();

		if ($result == true) {
		?>
		<p class="hint"><?php _e('Translation file successful updated. Please reload page.', 'nggallery'); ?></p>
		<p class="textright">
			<a class="button" href="<?php echo esc_url(strip_tags($overview_url)); ?>"><?php _e('Reload page', 'nggallery'); ?></a>
		</p>
		<?php
		} else {
		?>
		<p class="hint"><?php _e('Translation file couldn\'t be updated', 'nggallery'); ?></p>
		<?php
		}

		return;
	}

    echo '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="describe hide-if-js">' . __('This widget requires JavaScript.') . '</p>';
}

function ngg_locale() {
	global $ngg;

	require_once(NGGALLERY_ABSPATH . '/lib/locale.php');

	$locale = new ngg_locale();
	$overview_url = admin_url() . 'admin.php?page=' . NGGFOLDER;
    $result = $locale->check();
	$update_url    = wp_nonce_url ( $overview_url . '&amp;locale=update', 'ngg_update_locale');

	//Translators can change this text via gettext
	if ($result == 'installed') {
		echo $ngg->translator;
		if ( !is_wp_error($locale->response) && $locale->response['response']['code'] == '200') {
		?>
		<p class="textright">
			<a class="button" href="<?php echo esc_url( strip_tags($update_url) ); ?>"><?php _e('Update', 'nggallery'); ?></a>
		</p>
		<?php
		}
	}

	//Translators can change this text via gettext
	if ($result == 'available') {
		?>
		<p><strong>Download now your language file !</strong></p>
		<p class="textright">
			<a class="button" href="<?php echo esc_url( strip_tags($update_url) ); ?>"><?php _e('Download', 'nggallery'); ?></a>
		</p>
		<?php
	}


	if ($result == 'not_exist')
		echo '<p class="hint">'. sprintf( '<strong>Would you like to help translating this plugin?</strong> <a target="_blank" href="%s">Download</a> the current pot file and read <a href="http://www.nextgen-gallery.com/translating-nextgen-gallery/">here</a> how you can translate the plugin.', NGGALLERY_URLPATH . 'lang/nggallery.pot').'</p>';

}

/**
 * Show GD Library version information
 *
 * @return void
 */
function ngg_gd_info() {

	if(function_exists("gd_info")){
		$info = gd_info();
		$keys = array_keys($info);
		for($i=0; $i<count($keys); $i++) {
			if(is_bool($info[$keys[$i]]))
				echo "<li> " . $keys[$i] ." : <span>" . ngg_gd_yesNo($info[$keys[$i]]) . "</span></li>\n";
			else
				echo "<li> " . $keys[$i] ." : <span>" . $info[$keys[$i]] . "</span></li>\n";
		}
	}
	else {
		echo '<h4>'.__('No GD support', 'nggallery').'!</h4>';
	}
}

/**
 * Return localized Yes or no
 *
 * @param bool $bool
 * @return return 'Yes' | 'No'
 */
function ngg_gd_yesNo( $bool ){
	if($bool)
		return __('Yes', 'nggallery');
	else
		return __('No', 'nggallery');
}


/**
 * Show up some server infor's
 * @author GamerZ (http://www.lesterchan.net)
 *
 * @return void
 */
function ngg_get_serverinfo() {

	global $wpdb, $ngg;
	// Get MYSQL Version
	$sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
	// GET SQL Mode
	$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
	if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
	if (empty($sql_mode)) $sql_mode = __('Not set', 'nggallery');
	// Get PHP Safe Mode
	if(ini_get('safe_mode')) $safe_mode = __('On', 'nggallery');
	else $safe_mode = __('Off', 'nggallery');
	// Get PHP allow_url_fopen
	if(ini_get('allow_url_fopen')) $allow_url_fopen = __('On', 'nggallery');
	else $allow_url_fopen = __('Off', 'nggallery');
	// Get PHP Max Upload Size
	if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');
	else $upload_max = __('N/A', 'nggallery');
	// Get PHP Output buffer Size
	if(ini_get('pcre.backtrack_limit')) $backtrack_limit = ini_get('pcre.backtrack_limit');
	else $backtrack_limit = __('N/A', 'nggallery');
	// Get PHP Max Post Size
	if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
	else $post_max = __('N/A', 'nggallery');
	// Get PHP Max execution time
	if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
	else $max_execute = __('N/A', 'nggallery');
	// Get PHP Memory Limit
	if(ini_get('memory_limit')) $memory_limit = $ngg->memory_limit;
	else $memory_limit = __('N/A', 'nggallery');
	// Get actual memory_get_usage
	if (function_exists('memory_get_usage')) $memory_usage = round(memory_get_usage() / 1024 / 1024, 2) . __(' MByte', 'nggallery');
	else $memory_usage = __('N/A', 'nggallery');
	// required for EXIF read
	if (is_callable('exif_read_data')) $exif = __('Yes', 'nggallery'). " ( V" . substr(phpversion('exif'),0,4) . ")" ;
	else $exif = __('No', 'nggallery');
	// required for meta data
	if (is_callable('iptcparse')) $iptc = __('Yes', 'nggallery');
	else $iptc = __('No', 'nggallery');
	// required for meta data
	if (is_callable('xml_parser_create')) $xml = __('Yes', 'nggallery');
	else $xml = __('No', 'nggallery');

?>
	<li><?php _e('Operating System', 'nggallery'); ?> : <span><?php echo PHP_OS; ?>&nbsp;(<?php echo (PHP_INT_SIZE * 8) ?>&nbsp;Bit)</span></li>
	<li><?php _e('Server', 'nggallery'); ?> : <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
	<li><?php _e('Memory usage', 'nggallery'); ?> : <span><?php echo $memory_usage; ?></span></li>
	<li><?php _e('MYSQL Version', 'nggallery'); ?> : <span><?php echo $sqlversion; ?></span></li>
	<li><?php _e('SQL Mode', 'nggallery'); ?> : <span><?php echo $sql_mode; ?></span></li>
	<li><?php _e('PHP Version', 'nggallery'); ?> : <span><?php echo PHP_VERSION; ?></span></li>
	<li><?php _e('PHP Safe Mode', 'nggallery'); ?> : <span><?php echo $safe_mode; ?></span></li>
	<li><?php _e('PHP Allow URL fopen', 'nggallery'); ?> : <span><?php echo $allow_url_fopen; ?></span></li>
	<li><?php _e('PHP Memory Limit', 'nggallery'); ?> : <span><?php echo $memory_limit; ?></span></li>
	<li><?php _e('PHP Max Upload Size', 'nggallery'); ?> : <span><?php echo $upload_max; ?></span></li>
	<li><?php _e('PHP Max Post Size', 'nggallery'); ?> : <span><?php echo $post_max; ?></span></li>
	<li><?php _e('PCRE Backtracking Limit', 'nggallery'); ?> : <span><?php echo $backtrack_limit; ?></span></li>
	<li><?php _e('PHP Max Script Execute Time', 'nggallery'); ?> : <span><?php echo $max_execute; ?>s</span></li>
	<li><?php _e('PHP Exif support', 'nggallery'); ?> : <span><?php echo $exif; ?></span></li>
	<li><?php _e('PHP IPTC support', 'nggallery'); ?> : <span><?php echo $iptc; ?></span></li>
	<li><?php _e('PHP XML support', 'nggallery'); ?> : <span><?php echo $xml; ?></span></li>
<?php
}

/**
 * Inform about the end of PHP4
 *
 * @return void
 */
function ngg_check_for_PHP5() {
    ?>
	<div class="updated">
		<p><?php _e('NextCellent Gallery contains some functions which are only available under PHP 5.2. You are using the old PHP 4 version, upgrade now! It\'s no longer supported by the PHP group. Many shared hosting providers offer both PHP 4 and PHP 5, running simultaneously. Ask your provider if they can do this.', 'nggallery'); ?></p>
	</div>
    <?php
}

/**
 * ngg_get_phpinfo() - Extract all of the data from phpinfo into a nested array
 *
 * @author jon@sitewizard.ca
 * @return array
 */
function ngg_get_phpinfo() {

	ob_start();
	phpinfo();
	$phpinfo = array('phpinfo' => array());

	if ( preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER) )
	    foreach($matches as $match) {
	        if(strlen($match[1]))
	            $phpinfo[$match[1]] = array();
	        elseif(isset($match[3]))
	            $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
	        else
	            $phpinfo[end(array_keys($phpinfo))][] = $match[2];
	    }

	return $phpinfo;
}

/**
 * Show NextGEN Gallery related plugins. Fetch plugins from wp.org which have added 'nextgen-gallery' as tag in readme.txt
 *
 * @return postbox output
 */
function ngg_widget_related_plugins() {
    echo '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="describe hide-if-js">' . __('This widget requires JavaScript.') . '</p>';
}

/**
 * Backward compatibility, scheduled to delete on future iteration.
 */
function ngg_related_plugins() {
    nextcellent_overview::related_plugins() ;
}

/**
 * Provide utilities functions for creating metaboxes on overview
 * Class nextcellent_overview
 */
class nextcellent_overview {
    const URL_WPGETREADY = 'http://www.wpgetready.com';

    /**
     * Display a list of related plugins, gathered from Wordpress Repository
     * Optional parameter to display more or less plugins.
     * To improve: filter obsolete plugins (last_updated less than two years from now)
     */
    static function related_plugins($how_many=4) {
        include(ABSPATH . 'wp-admin/includes/plugin-install.php');
        //If transient vaporized, refresh it
        if ( false === ( $api = get_transient( 'ngg_related_plugins' ) ) ) {
            // Adittional info http://dd32.id.au/projects/wordpressorg-plugin-information-api-docs/
            $api = plugins_api('query_plugins', array('search' => 'nextgen') );
            if ( is_wp_error($api) ) return;
            set_transient( 'ngg_related_plugins', $api, 60*60*24 ); //enable to check within a day.
        }

        echo '<div style="margin-bottom:10px;padding:8px;font-size:110%;background:#eebbaa;"><b>'; _e('Pay attention','nggallery'); echo '</b>:'; _e('third parties plugins that are compatible with NGG may not be 100% compatible with NextCellent Gallery!','nggallery'); echo '</div>';
        //List of suppressed plugin on the list.
        $blacklist = array('nextgen-gallery','nextcellent-gallery-nextgen-legacy');

	$i = 0;
	while ( $i < $how_many ) {

        // pick them randomly
        if ( 0 == count($api->plugins) ) return;

        $key = array_rand($api->plugins);
        $plugin = $api->plugins[$key];

        // don't forget to remove them
        unset($api->plugins[$key]);

        if ( !isset($plugin->name) ) continue;

        if ( in_array($plugin->slug , $blacklist ) ) continue;

        $link   = esc_url( $plugin->homepage );
        $title  = esc_html( $plugin->name );

        $description = esc_html( strip_tags(@html_entity_decode($plugin->short_description, ENT_QUOTES, get_option('blog_charset'))) );

        $ilink = wp_nonce_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin->slug, 'install-plugin_' . $plugin->slug) .
            '&amp;TB_iframe=true&amp;width=600&amp;height=800';

        echo "<h5><a href='{$link}' target='_blank'>{$title}</a></h5>&nbsp;<span>(<a href='$ilink' class='thickbox' title='$title'>" . __( 'Install' ) . "</a>)</span>\n";
        echo "<p>$description<strong> " . __( 'Author' ) . " : </strong>$plugin->author</p>\n";

        $i++;
    }

}

    /**
     * Like Metabox over right
     */
    static function likeThisMetaBox() {

        echo '<p>';
        echo sprintf(__('This plugin is a branch from NGG stable version 1.9.13.<br> Developed & maintained by <a href="%s" target="_blank">WPGetReady.com</a>', 'nggallery'), self::URL_WPGETREADY);

        echo '</p><ul>';

        $url = 'http://www.wordpress.org/plugins/nextcellent-gallery-nextgen-legacy/' ;
        echo "<li style='padding-left: 38px; background:transparent url(" . NGGALLERY_URLPATH . "admin/images/icon-rating.png ) no-repeat scroll center left; background-position: 16px 50%; text-decoration: none;'><a href='{$url}' target='_blank'>";
        _e('You can contribute by giving this plugin a good rating! Thanks a lot!', 'nggallery');
        echo "</a></li>";

        $url = self::URL_WPGETREADY;
        echo "<li style='padding-left: 38px; background:transparent url(" . NGGALLERY_URLPATH . "admin/images/nextgen.png ) no-repeat scroll center left; background-position: 16px 50%; text-decoration: none;'><a href='{$url}' target='_blank'>";
        _e("Visit the plugin homepage", 'nggallery');
        echo '</a></li></ul>';
    }
}
?>
