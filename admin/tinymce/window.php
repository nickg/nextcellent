<?php

if ( !defined('ABSPATH') )
    die('You are not allowed to call this page directly.');

global $wpdb, $nggdb;

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

// Get WordPress scripts and styles
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
global $wp_scripts;
if (!isset($wp_scripts->registered['jquery-ui-autocomplete'])) {
	wp_register_script( 'jquery-ui-autocomplete', NGGALLERY_URLPATH .'admin/js/jquery.ui.autocomplete.min.js', array('jquery-ui-core'), '1.8.15');
}
wp_enqueue_script('jquery-ui-autocomplete');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>NextCellent Gallery</title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<?php wp_print_scripts() ?>
    <script language="javascript" type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/js/ngg.autocomplete.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/tinymce/tinymce.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo NGGALLERY_URLPATH ?>admin/css/jquery.ui.css" media="all" />
    <base target="_self" />
</head>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery("#gallerytag").nggAutocomplete( {
        type: 'gallery',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
    });
    jQuery("#albumtag").nggAutocomplete( {
        type: 'album',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
    });
    jQuery("#singlepictag").nggAutocomplete( {
        type: 'image',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
    });
});
</script>
<body class="nextgen_tinymce_window" id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="NextGEN" action="#">
	<div class="tabs">
		<ul>
			<li id="gallery_tab" class="current"><span><a href="javascript:mcTabs.displayTab('gallery_tab','gallery_panel');" onmousedown="return false;"><?php echo _n( 'Gallery', 'Galleries', 1, 'nggallery' ) ?></a></span></li>
			<li id="album_tab"><span><a href="javascript:mcTabs.displayTab('album_tab','album_panel');" onmousedown="return false;"><?php echo _n( 'Album', 'Albums', 1, 'nggallery' ) ?></a></span></li>
			<li id="singlepic_tab"><span><a href="javascript:mcTabs.displayTab('singlepic_tab','singlepic_panel');" onmousedown="return false;"><?php _e('Picture', 'nggallery'); ?></a></span></li>
		</ul>
	</div>

	<div class="panel_wrapper">
		<!-- gallery panel -->
		<div id="gallery_panel" class="panel current">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="gallerytag"><?php _e("Gallery", 'nggallery'); ?></label></td>
            <td><select id="gallerytag" name="gallerytag" style="width: 200px">
                <option value="0" selected="selected"><?php _e("Select or enter gallery", 'nggallery'); ?></option>
                </select>
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap" valign="top"><label for="showtype"><?php _e("Show as", 'nggallery'); ?></label></td>
            <td><label><input name="showtype" type="radio" value="nggallery" checked="checked" /> <?php _e('Image list', 'nggallery') ;?></label><br />
			<label><input name="showtype" type="radio" value="slideshow"  /> <?php _e('Slideshow', 'nggallery') ;?></label><br />
			<label><input name="showtype" type="radio" value="imagebrowser"  /> <?php _e('Imagebrowser', 'nggallery') ;?></label></td>
          </tr>
        </table>
		</div>
		<!-- gallery panel -->

		<!-- album panel -->
		<div id="album_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="albumtag"><?php _e("Album", 'nggallery'); ?></label></td>
            <td><select id="albumtag" name="albumtag" style="width: 200px">
                    <option value="0" selected="selected"><?php _e("Select or enter album", 'nggallery'); ?></option>
                </select>
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap" valign="top"><label for="showtype"><?php _e("Show as", 'nggallery'); ?></label></td>
            <td><label><input name="albumtype" type="radio" value="extend" checked="checked" /> <?php _e('Extended version', 'nggallery') ;?></label><br />
			<label><input name="albumtype" type="radio" value="compact"  /> <?php _e('Compact version', 'nggallery') ;?></label></td>
          </tr>
        </table>
		</div>
		<!-- album panel -->

		<!-- single pic panel -->
		<div id="singlepic_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="singlepictag"><?php _e("Picture", 'nggallery'); ?></label></td>
            <td><select id="singlepictag" name="singlepictag" style="width: 200px">
                <option value="0" selected="selected"><?php _e("Select or enter picture", 'nggallery'); ?></option>
                </select>
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap"><?php _e("Width x Height", 'nggallery'); ?></td>
            <td><input type="text" size="5" id="imgWidth" name="imgWidth" value="320" /> x <input type="text" size="5" id="imgHeight" name="imgHeight" value="240" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap" valign="top"><?php _e("Effect", 'nggallery'); ?></td>
            <td>
				<label><select id="imgeffect" name="imgeffect">
					<option value="none"><?php _e("No effect", 'nggallery'); ?></option>
					<option value="watermark"><?php _e("Watermark", 'nggallery'); ?></option>
					<option value="web20"><?php _e("Web 2.0", 'nggallery'); ?></option>
				</select></label>
			</td>
          </tr>
          <tr>
            <td nowrap="nowrap" valign="top"><?php _e("Float", 'nggallery'); ?></td>
            <td>
				<label><select id="imgfloat" name="imgfloat">
					<option value=""><?php _e("No float", 'nggallery'); ?></option>
					<option value="left"><?php _e("Left", 'nggallery'); ?></option>
					<option value="center"><?php _e("Center", 'nggallery'); ?></option>
					<option value="right"><?php _e("Right", 'nggallery'); ?></option>
				</select></label>
			</td>
          </tr>

        </table>
		</div>
		<!-- single pic panel -->
	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'nggallery'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'nggallery'); ?>" onclick="insertNGGLink();" />
		</div>
	</div>
</form>
</body>
</html>