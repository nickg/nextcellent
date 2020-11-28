<?php
if ( !defined('ABSPATH') )
	die('You are not allowed to call this page directly.');

global $wpdb, $nggdb, $wp_scripts;

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

// Get scripts and styles
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_style('wp-admin');
wp_enqueue_script( 'ngg-autocomplete', NGGALLERY_URLPATH . 'admin/js/ngg.autocomplete.js', array( 'jquery-ui-autocomplete' ), '1.1' );
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>NextCellent</title>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>" />
        <meta charset="<?php echo get_option('blog_charset'); ?>">
		<script type="text/javascript">
			var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
		</script>
		<?php wp_print_scripts(); ?>
		<script language="javascript" type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/tinymce/tiny_mce_popup.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/tinymce/utils/mctabs.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/tinymce/tinymce.js"></script>
		<?php wp_print_styles(); ?>
		<style>
			.nggwrap {
				padding:10px;
				overflow-y: scroll;
				height: calc(100% - 51px);
			}
			body {
				height: calc(100% - 36px);
			}
			.display-type, .album-type, .recent-type, .random-type {
				float: left;
				width: 10%;
				min-width: 150px;
				text-align: center;
			}
			.display-type-img {
				width: 100%;
				height: auto;
				 }
			.type {
				height: auto !important;
			}
			label {
				vertical-align: initial;
			}
			.description {
				padding-left: 10px;
			}
			h3 {
				margin-bottom: 0;
			}
			.mceActionPanel {
				position: fixed;
				background: white;
				bottom: 0;
				left: 0;
				right: 0;
				overflow: hidden;
				padding: 1em;
				border-top: 1px solid #dfdfdf;
			}
			#insert:hover {
				background: #1e8cbe;
				border-color: #0074a2;
				box-shadow: inset 0 1px 0 rgba(120,200,230,.6);
				color: #fff;
			}
			#insert:active {
				background: #1b7aa6;
				border-color: #005684;
				color: rgba(255,255,255,.95);
				box-shadow: inset 0 1px 0 rgba(0,0,0,.1);
				vertical-align: top;
			}
			#insert {
				background: #2ea2cc;
				border-color: #0074a2;
				-webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15);
				box-shadow: inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15);
				color: #fff;
				text-decoration: none;
			}
			#cancel:hover {
				background: #fafafa;
				border-color: #999;
				color: #222;
			}
			#cancel {
				color: #555;
				border-color: #ccc;
				background: #f7f7f7;
				-webkit-box-shadow: inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);
				box-shadow: inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);
				vertical-align: top;
			}
			#cancel:active {
				background: #eee;
				border-color: #999;
				color: #333;
				-webkit-box-shadow: inset 0 2px 5px -3px rgba(0,0,0,.5);
				box-shadow: inset 0 2px 5px -3px rgba(0,0,0,.5);
			}
		</style>
		<base target="_self" />
	</head>
	<body class="nextgen_tinymce_window wp-admin wp-core-ui" id="link" style="display: none">
		<div class="nggwrap">
			<form id="select-type">
				<label for="types"><?php _e("To add something, select what you would want to display", 'nggallery'); ?>:</label>
				<select id="types">
					<option value="gallery_panel" selected="selected"><?php _e("Gallery", 'nggallery'); ?></option>
					<option value="album_panel"><?php _e("Album", 'nggallery'); ?></option>
					<option value="singlepic_panel"><?php _e("One picture", 'nggallery'); ?></option>
					<option value="recent_panel"><?php _e("Recent pictures", 'nggallery'); ?></option>
					<option value="random_panel"><?php _e("Random pictures", 'nggallery'); ?></option>
				</select>
			</form>
			<form name="NextGEN" id="ngg-tinymce" action="#">
				<!-- This is the initial panel that's showed -->
				<div id="gallery_panel" class="type current" style="display: initial;">
					<table border="0" cellpadding="4" cellspacing="0" style="font-size:1em;">
						<tr>
							<td colspan="2"><h3><?php _e("Basics", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td nowrap="nowrap"><label for="gallerytag"><?php _e("Select a gallery:", 'nggallery'); ?></label></td>
							<td>
								<select id="gallerytag" name="gallerytag" style="width: 200px">
									<option value="0" selected="selected"><?php _e("Select or search for a gallery", 'nggallery'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><?php _e("Display types", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td nowrap="nowrap" colspan="2" valign="top"><?php _e("Select how you want to display your gallery", 'nggallery'); ?>:</td>
						</tr>
						<tr>
							<td colspan="2" style="overflow:hidden;">
								<div class="display-type">
									<label for="type-nggallery">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/gallery.svg'; ?>" alt="gallery">
										<br /><input name="showtype" class="radiotype" type="radio" value="nggallery" id="type-nggallery" checked="checked" /><?php _e('Gallery', 'nggallery') ;?>
									</label>
								</div>
								<div class="display-type">
									<label for="type-slideshow">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/slideshow.svg'; ?>" alt="slideshow">
										<br /><input name="showtype" class="radiotype" type="radio" value="slideshow" id="type-slideshow" /><?php _e('Slideshow', 'nggallery') ;?>
									</label>
								</div>
								<div class="display-type">
									<label for="type-imagebrowser">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/imagebrowser.svg'; ?>" alt="browser">
										<br /><input name="showtype" class="radiotype" type="radio" value="imagebrowser" id="type-imagebrowser" /><?php _e('Imagebrowser', 'nggallery') ;?>
									</label>
								</div>
								<div class="display-type">
									<label for="type-carousel">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/carousel.svg'; ?>" alt="carousel">
										<br /><input name="showtype" class="radiotype" type="radio" value="carousel" id="type-carousel" /><?php _e('Carousel', 'nggallery') ;?>
									</label>
								</div>
								<div class="display-type">
									<label for="type-caption">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/caption.svg'; ?>" alt="caption">
										<br /><input name="showtype" class="radiotype" type="radio" value="caption" id="type-caption" /><?php _e('Caption', 'nggallery') ;?>
									</label>
								</div>
								<?php //TODO: add some kind of filter here	?>
								<div class="display-type">
									<label for="type-other">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/other.svg'; ?>" alt="carousel">
										<br /><input name="showtype" class="radiotype" type="radio" value="other" id="type-other" /><?php _e('Custom', 'nggallery') ;?>
									</label>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><?php _e("Type options", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td colspan="2">
								<span class="nggallery-options carousel-options caption-options type-options" style="display: initial;">
									<table style="font-size:1em;">
										<tr>
											<td><label for="nggallery-images"><?php _e("Number of images", 'nggallery'); ?>:</label></td>
											<td>
												<input id="nggallery-images" type="number">
												<span class="description"><?php _e("The number of images before pagination is applied. Leave empty for the default from the settings.", 'nggallery'); ?></span>
											</td>
										</tr>
									</table>
								</span>
								<span class="slideshow-options type-options" style="display: none;">
									<table style="font-size:1em;">
										<tr>
											<td><?php _e("Slideshow dimensions", 'nggallery'); ?>:</td>
											<td>
												<label for="slide-width"><?php esc_html_e('Width','nggallery') ?></label><input id="slide-width" type="number" step="1" min="0"/>
												<label for="slide-height"><?php esc_html_e('Height','nggallery') ?></label><input id="slide-height" type="number" step="1" min="0" type="number">
											</td>
										</tr>
									</table>
								</span>
								<span class="other-options type-options" style="display: none;">
									<table style="font-size:1em;">
										<tr>
											<td><label for="other-name"><?php _e("Template name", 'nggallery'); ?>:</label></td>
											<td><input id="other-name"type="text"></td>
										</tr>
									</table>
								</span>
								<span class="imagebrowser-options type-options" style="display: none;">No options.</span>
							</td>
						</tr>
					</table>
				</div> <!-- Gallery Panel -->

				<div id="album_panel" class="type" style="display:none;">
					<table border="0" cellpadding="4" cellspacing="0" style="font-size:1em;">
						<tr>
							<td colspan="2"><h3><?php _e("Basics", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td nowrap="nowrap"><label for="albumtag"><?php _e("Album", 'nggallery'); ?></label></td>
							<td>
								<select id="albumtag" name="albumtag" style="width: 200px">
									<option value="0" selected="selected"><?php _e("Select or enter album", 'nggallery'); ?></option>
								</select>
								<span class="description"><?php _e("Leave this empty to display all galleries.", 'nggallery'); ?></span>
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><?php _e("Album display types", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td colspan="2"><?php _e("Select how you want to display the albums", 'nggallery'); ?></td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="album-type">
									<label for="album-type-compact">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/compact.svg'; ?>" alt="compact">
										<br /><input name="albumtype" type="radio" value="compact" id="album-type-compact" checked="checked" /><?php _e('Compact version', 'nggallery') ;?>
									</label>
								</div>
								<div class="album-type">
									<label for="album-type-extend">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/extend.svg'; ?>" alt="extend">
										<br /><input name="albumtype" type="radio" value="extend" id="album-type-extend" /><?php _e("Extended version", 'nggallery') ;?>
									</label>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><?php _e("Gallery display types", 'nggallery') ;?></h3></td>
						</tr>
						<tr>
							<td colspan="2"><?php _e("Select a template for the galleries (displayed after you click on an album)", 'nggallery'); ?>:</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="album-type">
									<label for="albumtype-nggallery">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/gallery.svg'; ?>" alt="gallery">
										<br /><input name="album-showtype" class="radiotype" type="radio" value="nggallery" id="albumtype-nggallery" checked="checked" /><?php _e('Gallery', 'nggallery') ;?>
									</label>
								</div>
								<div class="album-type">
									<label for="albumtype-carousel">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/carousel.svg'; ?>" alt="carousel">
										<br /><input name="album-showtype" class="radiotype" type="radio" value="carousel" id="albumtype-carousel" /><?php _e('Carousel', 'nggallery') ;?>
									</label>
								</div>
								<div class="album-type">
									<label for="albumtype-caption">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/caption.svg'; ?>" alt="caption">
										<br /><input name="album-showtype" class="radiotype" type="radio" value="caption" id="albumtype-caption" /><?php _e('Caption', 'nggallery') ;?>
									</label>
								</div>
							</td>
						</tr>
					</table>
				</div> <!-- Album Panel -->
		
				<div id="singlepic_panel" class="type" style="display:none;">
					<table style="font-size:1em;">
						<tr>
							<td colspan="2"><h3><?php _e("Basics", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td>
								<label for="singlepictag"><?php _e("Select a picture", 'nggallery'); ?></label>
							</td>
							<td>
								<select id="singlepictag" name="singlepictag" style="width: 200px">
									<option value="0" selected="selected"><?php _e("Select or enter picture", 'nggallery'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><?php _e("Options", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td><?php _e("Dimensions", 'nggallery'); ?></td>
							<td>
								<label for="imgWidth"><?php _e('Width','nggallery') ?></label>
								<input type="number" min="0" id="imgWidth" name="imgWidth" />
								<label for="imgHeight"><?php _e('Height','nggallery') ?></label>
								<input type="number" min="0" id="imgHeight" name="imgHeight" />
							</td>
						</tr>
						<tr>
							<td><label for="imgeffect"><?php _e("Effect", 'nggallery'); ?></label></td>
							<td>
								<select id="imgeffect" name="imgeffect">
									<option value="0"><?php _e("No effect", 'nggallery'); ?></option>
									<option value="watermark"><?php _e("Watermark", 'nggallery'); ?></option>
									<option value="web20"><?php _e("Web 2.0", 'nggallery'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><label for="imgfloat"><?php _e("Alignment", 'nggallery'); ?></label></td>
							<td>
								<select id="imgfloat" name="imgfloat">
									<option value="0"><?php _e("No float", 'nggallery'); ?></option>
									<option value="left"><?php _e("Left", 'nggallery'); ?></option>
									<option value="center"><?php _e("Center", 'nggallery'); ?></option>
									<option value="right"><?php _e("Right", 'nggallery'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><label for="imglink"><?php _e("Link", 'nggallery'); ?></label></td>
							<td><input type="text" name="imglink" id="imglink"><span class="description"><?php _e("Add an optional link to the image. Leave blank for no link.", 'nggallery'); ?></td>
						</tr>
						<tr>
							<td><label for="imgcaption"><?php _e("Caption", 'nggallery'); ?></label></td>
							<td><input type="text" name="imgcaption" id="imgcaption"><span class="description"><?php _e("Add an optional caption to the image. Leave blank for no caption.", 'nggallery'); ?></td>
						</tr>
					</table>
				</div> <!-- SinglePic Panel -->
				<div id="recent_panel" class="type" style="display:none;">
					<table style="font-size:1em;">
						<tr>
							<td colspan="2"><h3><?php _e("Basics", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td><label for="recent-images"><?php _e("Number of images", 'nggallery'); ?>:</label></td>
							<td>
								<input id="recent-images" type="number" required>
								<span class="description"><?php _e("The number of images that should be displayed.", 'nggallery'); ?></span>
							</td>
						</tr>
						<tr>
							<td><label for="sortmode"><?php _e("Sort the images", 'nggallery'); ?></label></td>
							<td>
								<select id="sortmode" name="sortmode">
									<option value="0"><?php _e("Upload order", 'nggallery'); ?></option>
									<option value="date"><?php _e("Date taken", 'nggallery'); ?></option>
									<option value="sort"><?php _e("User defined", 'nggallery'); ?></option>
								</select>
								<span class="description"><?php _e("In what order the images are shown. Upload order uses the ID's, date taken uses the EXIF data and user defined is the sort mode from the settings.", 'nggallery'); ?></span>
							</td>
						</tr>
						<tr>
							<td><label for="recentgallery"><?php _e("Select a gallery:", 'nggallery'); ?></label></td>
							<td>
								<select id="recentgallery" name="recentgallery" style="width: 200px">
									<option value="0" selected="selected"><?php _e("Select or search for a gallery", 'nggallery'); ?></option>
								</select>
								<span class="description"><?php _e("If a gallery is selected, only images from that gallery will be shown.", 'nggallery'); ?></span>
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><?php _e("Options", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td colspan="2"><?php _e("Select a template to display the images", 'nggallery'); ?>:</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="recent-type">
									<label for="recenttype-nggallery">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/gallery.svg'; ?>" alt="gallery">
										<br /><input name="recent-showtype" class="radiotype" type="radio" value="nggallery" id="recenttype-nggallery" checked="checked" /><?php _e('Gallery', 'nggallery') ;?>
									</label>
								</div>
								<div class="recent-type">
									<label for="recenttype-carousel">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/carousel.svg'; ?>" alt="carousel">
										<br /><input name="recent-showtype" class="radiotype" type="radio" value="carousel" id="recenttype-carousel" /><?php _e('Carousel', 'nggallery') ;?>
									</label>
								</div>
								<div class="recent-type">
									<label for="recenttype-caption">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/caption.svg'; ?>" alt="caption">
										<br /><input name="recent-showtype" class="radiotype" type="radio" value="caption" id="recenttype-caption" /><?php _e('Caption', 'nggallery') ;?>
									</label>
								</div>
							</td>
						</tr>
					</table>
				</div> <!-- Recent Panel -->
				<div id="random_panel" class="type" style="display:none;">
					<table style="font-size:1em;">
						<tr>
							<td colspan="2"><h3><?php _e("Basics", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td><label for="random-images"><?php _e("Number of images", 'nggallery'); ?>:</label></td>
							<td>
								<input id="random-images" type="number" required>
								<span class="description"><?php _e("The number of images that should be displayed.", 'nggallery'); ?></span>
							</td>
						</tr>
						<tr>
							<td><label for="randomgallery"><?php _e("Select a gallery:", 'nggallery'); ?></label></td>
							<td>
								<select id="randomgallery" name="randomgallery" style="width: 200px">
									<option value="0" selected="selected"><?php _e("Select or search for a gallery", 'nggallery'); ?></option>
								</select>
								<span class="description"><?php _e("If a gallery is selected, only images from that gallery will be shown.", 'nggallery'); ?></span>
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><?php _e("Options", 'nggallery'); ?></h3></td>
						</tr>
						<tr>
							<td colspan="2"><?php _e("Select a template to display the images", 'nggallery'); ?>:</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="random-type">
									<label for="randomtype-nggallery">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/gallery.svg'; ?>" alt="gallery">
										<br /><input name="random-showtype" class="radiotype" type="radio" value="nggallery" id="randomtype-nggallery" checked="checked" /><?php _e('Gallery', 'nggallery') ;?>
									</label>
								</div>
								<div class="random-type">
									<label for="randomtype-carousel">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/carousel.svg'; ?>" alt="carousel">
										<br /><input name="random-showtype" class="radiotype" type="radio" value="carousel" id="randomtype-carousel" /><?php _e('Carousel', 'nggallery') ;?>
									</label>
								</div>
								<div class="random-type">
									<label for="randomtype-caption">
										<img class="display-type-img" src="<?php echo NGGALLERY_URLPATH . 'admin/images/caption.svg'; ?>" alt="caption">
										<br /><input name="random-showtype" class="radiotype" type="radio" value="caption" id="randomtype-caption" /><?php _e('Caption', 'nggallery') ;?>
									</label>
								</div>
							</td>
						</tr>
					</table>
				</div> <!-- Random Panel -->
			</form>
		</div>
		<div class="mceActionPanel">
			<div style="float: left">
				<input form="ngg-tinymce" type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'nggallery'); ?>" onclick="tinyMCEPopup.close();" />
			</div>
			<div style="float: right">
				<input form="ngg-tinymce" type="submit" id="insert" name="insert" value="<?php _e("Insert", 'nggallery'); ?>" onclick="checkValues();" />
			</div>
		</div>
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
				jQuery("#recentgallery").nggAutocomplete( {
					type: 'gallery',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
				});
				jQuery("#randomgallery").nggAutocomplete( {
					type: 'gallery',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
				});
				jQuery('#types').change(function(){
					jQuery('#' + jQuery(this).val()).show('fast').addClass('current').siblings('.type').hide().removeClass('current');
				});
				jQuery('.radiotype').change(function(){
					jQuery('.' + jQuery(this).val() + '-options').show('fast').siblings('.type-options').hide();
				});
			});
			function checkValues() {
				//Check to see that the required forms are completed
				var active = document.getElementsByClassName('current')[0].id;
				var required;
				var message;
				
				switch (active) {
					case 'gallery_panel' :
						required = document.getElementById('gallerytag').value;
						message = "<?php _e('You need to select a gallery.','nggallery')?>";
						break;
					case 'singlepic_panel' :
						required = document.getElementById('singlepictag').value;
						message = "<?php _e('You need to select a picture.','nggallery')?>";
						break;
					case 'recent_panel' :
						required = document.getElementById('recent-images').value;
						message = "<?php _e('You need to select a number of images.','nggallery')?>";
						break;
					case 'random_panel' :
						required = document.getElementById('random-images').value;
						message = "<?php _e('You need to select a number of images.','nggallery')?>";
						break;
					default:
						required = 1;
				}
				if (required == 0) {
					event.preventDefault();
					alert(message);
				} else {
					insertNGGLink();
				}
			}
		</script>
	</body>
</html>