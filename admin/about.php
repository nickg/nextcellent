<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	function nggallery_admin_about()  {

	?>
	<div class="wrap">
    <?php screen_icon( 'nextgen-gallery' ); ?>
	<h2><?php _e('About', 'nggallery') ;?></h2>
	<div id="poststuff">
		<div class="postbox">
			<h3 class="hndle"><span><?php _e('Contributors', 'nggallery'); ?></span></h3>
		    <div class="inside">
				<p><?php _e('This plugin is made possible by the great work of a lot of people. A special thanks the following people:', 'nggallery') ;?></p>
				<ul class="ngg-list">
				<li><a href="http://wordpress.org" target="_blank">The WordPress Team</a> <?php _e('for their great documented code', 'nggallery') ;?></li>
				<li><a href="http://jquery.com" target="_blank">The jQuery Team</a> <?php _e('for jQuery, which is the best Web2.0 framework', 'nggallery') ;?></li>
				<li><a href="http://www.gen-x-design.com" target="_blank">Ian Selby</a> <?php _e('for the fantastic PHP Thumbnail Class', 'nggallery') ;?></li>
				<li><a href="http://www.lesterchan.net/" target="_blank">GaMerZ</a> <?php _e('for a lot of very useful plugins and ideas', 'nggallery') ;?></li>
				<li><a href="http://www.laptoptips.ca/" target="_blank">Andrew Ozz</a> <?php _e('for Shutter Reloaded, a real lightweight image effect', 'nggallery') ;?></li>
				<li><a href="http://www.jeroenwijering.com/" target="_blank">Jeroen Wijering</a> <?php _e('for the best Media Flash Scripts on earth', 'nggallery') ;?></li>
				<li><a href="http://field2.com" target="_blank">Ben Dunkle</a> <?php _e('for the Gallery Icon', 'nggallery') ;?></li>
				<li><a href="http://watermark.malcherek.com/" target="_blank">Marek Malcherek</a> <?php _e('for the Watermark plugin', 'nggallery') ;?></li>
				<li><a href="http://wpgetready.com/" target="_blank">WPGetReady</a> <?php _e('for maintaining this fork of NextGen Gallery', 'nggallery') ;?></li>
				<li><?php _e('The original translators for NextGen Gallery, who made the translations', 'nggallery') ;?></li>
				</ul>
				<div>Icons made by <a href="http://www.freepik.com" alt="Freepik.com" title="Freepik.com">Freepik</a> from i.a. <a href="http://www.flaticon.com/packs/layout-icons" title="Flaticon">www.flaticon.com</a></div>
			</div>
		</div>
        <div class="postbox">
            <h3 class="hndle"><span><?php _e('NextCellent', 'nggallery'); ?></span></h3>
            <div class="inside">
                <p><?php _e('NextCellent Gallery is based on the 1.9.13 version of the NextGen Gallery by Photocrati Media, which is the succesor to the work by Alex Rabe.', 'nggallery') ;?></p>
                <h4><?php _e('What do you get with NextCellent Gallery?', 'nggallery') ;?></h4>
				<ul class="ngg-list">
				<li><?php _e('Backward compatibility with NextGEN plugin version (1.9.13)', 'nggallery') ;?></li>
				<li><?php _e('Slow evolving code path, made only by NextGEN user\'s suggestions. Yep, you read it right: slow in counterpart as fast. Older code is good enough to keep a community and it worked (and works) for most people.', 'nggallery') ;?></li>
				<li><?php _e('A reliable way to work with already installed NextGEN galleries.', 'nggallery') ;?></li>
				<li><?php _e('A place for updating the plugin without using FTP manual updates, but WordPress plugin repository.', 'nggallery') ;?></li>
				<li><?php _e('Alternative path preserving backward compatibility (while possible).', 'nggallery') ;?></li>
				</li>
				</ul>
            </div>
        </div>
		<div class="postbox">
			<h3 class="hndle"><span><?php _e('How to support us?', 'nggallery'); ?></span></h3>
			<div class="inside">
				<p><?php _e('There are several ways to contribute:', 'nggallery') ;?></p>
				<ul class="ngg-list">
					<li><strong><?php _e('Send us bugfixes / code changes', 'nggallery') ;?></strong><br /><?php _e('The most motivated support for this plugin are your ideas and brain work.', 'nggallery') ;?></li>
					<li><strong><?php _e('Translate the plugin', 'nggallery') ;?></strong><br /><?php _e('To help people to work with this plugin, we would like to have it in all available languages.', 'nggallery') ;?></li>
					<li><strong><?php _e('Place a link to the plugin in your blog/webpage', 'nggallery') ;?></strong><br /><?php _e('Yes, sharing and linking are also supportive and helpful.', 'nggallery') ;?></li>
				</ul>
			</div>
		</div>

	</div>
	</div>
	<?php } ?>