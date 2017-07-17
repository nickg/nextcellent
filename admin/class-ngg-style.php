<?php  

include_once('class-ngg-post-admin-page.php');

class NGG_Style extends NGG_Post_Admin_Page {
	
	/**
	 * Find stylesheets.
	 *
	 * @since 1.9.22
	 * 
	 * @param array $directions Absolute paths to the folders that contain stylesheets.
	 *
	 *@return array Absolute paths to the stylesheets.
	 */
	static function ngg_get_cssfiles( $directions ) {

		$plugin_files = array ();

		foreach ($directions as $direction) {
			$plugins_dir = dir($direction);
					if ($plugins_dir) {
				while (($file = $plugins_dir->read()) !== false) {
					if (preg_match('|^\.+$|', $file))
						{continue;}
					if (is_dir($direction.'/'.$file)) {
						$plugins_subdir = dir($direction.'/'.$file);
						if ($plugins_subdir) {
							while (($subfile = $plugins_subdir->read()) !== false) {
								if (preg_match('|^\.+$|', $subfile))
									{continue;}
								if (preg_match('|\.css$|', $subfile))
									{$plugin_files[] = "$direction/$file/$subfile";}
							}
						}
					} else {
						if (preg_match('|\.css$|', $file))
							{$plugin_files[] = $direction . '/' . $file;}
					}
				}
			}
		}

		return $plugin_files;
	}
	
	/**
	 * Parse stylesheet information.
	 *
	 * @since 1.9.22
	 * 
	 * @param string $plugin_file Absolute path to the stylesheet.
	 *
	 *@return array The information about the stylesheet.
	 */
	static function ngg_get_cssfiles_data($plugin_file) {
	
		$css_data = implode('', file($plugin_file));
		$folder = basename(dirname($plugin_file));

		preg_match("|CSS Name:(.*)|i", $css_data, $plugin_name);
		preg_match("|Description:(.*)|i", $css_data, $description);
		preg_match("|Author:(.*)|i", $css_data, $author_name);
		
		if (preg_match("|Version:(.*)|i", $css_data, $version))
			{$version = trim($version[1]);}
		else
			{$version = '';}

		$description = wptexturize(trim($description[1]));

		$name = trim($plugin_name[1]);
		$author = trim($author_name[1]);

		return array ('Name' => $name, 'Description' => $description, 'Author' => $author, 'Version' => $version, 'Folder' => $folder );
	}

	/**
	 * Output a set of options for a select element.
	 *
	 * @since 1.9.26
	 *
	 * @param array $css_list The paths to the stylesheets.
	 * @param string $act_css_file The path to the current active stylesheet.
	 */
	static function output_css_files_dropdown( $css_list, $act_css_file ) {
		foreach ( $css_list as $file) {
			$a_cssfile = NGG_Style::ngg_get_cssfiles_data($file);
			$css_name = esc_attr( $a_cssfile['Name'] );
			$css_folder = esc_attr( $a_cssfile['Folder'] );
			if ( $css_name != '' ) {
				echo '<option value="' . $file . '" ' . selected( $file, $act_css_file ) . '>' . $css_name . ' (' . $css_folder . ')</option>';
			}
		}
	}
	
	/**
	 * Save, change and move the css files and options.
	 *
	 * @since 1.9.22
	 *
	 */
	protected function processor() {
		global $ngg;
		$i = 0;

		if ( isset( $_POST['activate'] ) ) {
			check_admin_referer('ngg_style');
			$file = sanitize_text_field($_POST['css']);
			$activate = sanitize_text_field($_POST['activateCSS']);

			// save option now
			$ngg->options['activateCSS'] = $activate;
			$ngg->options['CSSfile'] = $file;
			update_option('ngg_options', $ngg->options);
			
			if ( isset($activate) ) {
				nggGallery::show_message(__('Successfully selected CSS file.','nggallery') );
			} else {
				nggGallery::show_message(__('No CSS file will be used.','nggallery') );
			}
		}

		if (isset($_POST['updatecss'])) {
			
			check_admin_referer('ngg_style');

			if ( !current_user_can('edit_themes') )
				{wp_die('<p>'.__('You do not have sufficient permissions to edit templates for this blog.').'</p>');}

			$newcontent = stripslashes($_POST['newcontent']);
			$old_path = sanitize_title($_POST['file']);
			$folder = sanitize_title($_POST['folder']);
			
			//if the file is in the css folder, copy it.
			if ($folder === 'css') {
				$filename = basename ($old_path, '.css');
				$new_path = NGG_CONTENT_DIR . "/ngg_styles/" . $filename . ".css";
				//check for duplicate files
				while ( file_exists( $new_path ) ) {
					$i++;
					$new_path = NGG_CONTENT_DIR . "/ngg_styles/"  . $filename . "-" . $i . ".css";
				}
				//check if ngg_styles exist or not
				if ( !file_exists(NGG_CONTENT_DIR . "/ngg_styles") ) {
					wp_mkdir_p( NGG_CONTENT_DIR . "/ngg_styles" );
				}
				//copy the file
				if ( copy($old_path, $new_path) ) {
					//set option to new file
					$ngg->options['CSSfile'] = $new_path;
					update_option('ngg_options', $ngg->options);
				} else {
					nggGallery::show_error(__('Could not move file.','nggallery'));
					return;
				}
			}
	
			if ( file_put_contents($old_path, $newcontent) ) {
				nggGallery::show_message(__('CSS file successfully updated.','nggallery'));
			} else {
				nggGallery::show_error(__('Could not save file.','nggallery'));
			}
		}
		
		if (isset($_POST['movecss'])) {

			if ( !current_user_can('edit_themes') )
				{wp_die('<p>'.__('You do not have sufficient permissions to edit templates for this blog.').'</p>');}
			
			$old_path = sanitize_text_field($_POST['oldpath']);
			$new_path = NGG_CONTENT_DIR . "/ngg_styles/nggallery.css";
			
			//check for duplicate files
			while ( file_exists( $new_path ) ) {
				$i++;
				$new_path = NGG_CONTENT_DIR . "/ngg_styles/nggallery-" . $i . ".css";
			}
			
			//move file
			if ( rename( $old_path, $new_path) ) {
				nggGallery::show_message(__('CSS file successfully moved.','nggallery'));
				//set option to new file
				$ngg->options['CSSfile'] = $new_path;
				update_option('ngg_options', $ngg->options);
			} else {
				nggGallery::show_error(__('Could not move the CSS file.','nggallery'));
			}
		}
	}

	/**
     * Render the page content.
	 *
	 * @since 1.9.22
     *
     */
	public function display() {

		parent::display();

		global $ngg;
		
		//the directions containing the css files
		if ( file_exists(NGG_CONTENT_DIR . "/ngg_styles") ) {
			$dir = array(NGGALLERY_ABSPATH . "css", NGG_CONTENT_DIR . "/ngg_styles");
		} else {
			$dir = array(NGGALLERY_ABSPATH . "css");
		}
		
		//support for legacy location (in theme folder)
		if ( $theme_css_exists = file_exists (get_stylesheet_directory() . "/nggallery.css") ) {
			$act_cssfile = get_stylesheet_directory() . "/nggallery.css";
		}
		
		//if someone uses the filter, don't display this page.
		if ( !$theme_css_exists && $set_css_file = nggGallery::get_theme_css_file() ) {
			nggGallery::show_error( __('Your CSS file is set by a theme or another plugin.','nggallery') . "<br><br>" . __('This CSS file will be applied:','nggallery') . "<br>" . $set_css_file);
			return;
		}
		
		//load all files
		if ( !isset($act_cssfile) ) {
			$csslist = NGG_Style::ngg_get_cssfiles($dir);
			$act_cssfile = $ngg->options['CSSfile'];
		}
		
		//get the data from the file
		$act_css_data = NGG_Style::ngg_get_cssfiles_data($act_cssfile);
		$act_css_name = $act_css_data['Name'];
		$act_css_description = $act_css_data['Description'];
		$act_css_author = $act_css_data['Author'];
		$act_css_version = $act_css_data['Version'];
		$act_css_folder = $act_css_data['Folder'];
		
		
		// get the content of the file
		$error = ( !is_file($act_cssfile) );

		if (!$error && filesize($act_cssfile) > 0) {
			$f = fopen($act_cssfile, 'r');
			$content = fread($f, filesize($act_cssfile));
			$content = htmlspecialchars($content); 
		} 
		?>
		<div class="wrap">
			<div class="bordertitle">
				<h2><?php _e('Style Editor','nggallery') ?></h2>
				<div class="fileedit-sub">
				<?php if(!$theme_css_exists): //no need if there is a theme css?>
					<div class="alignright">
						<form id="themeselector" name="cssfiles" method="post">
							<?php wp_nonce_field('ngg_style') ?>
							<strong><?php _e('Activate and use style sheet:','nggallery') ?></strong>
							<input type="checkbox" name="activateCSS" value="1" <?php checked('1', $ngg->options['activateCSS']); ?> />							
							<select name="css" id="theme" style="margin: 0pt; padding: 0pt;">
								<?php self::output_css_files_dropdown($csslist, $act_cssfile); ?>
							</select>
							<input class="button" type="submit" name="activate" value="<?php _e('Activate','nggallery') ?> &raquo;" class="button" />
						</form>
					</div>
				<?php endif; ?>
				<?php if (!is_multisite() || is_super_admin() ) { ?>
					<div class="alignleft">
						<?php
						$title = '<h3>';
						if ( is_writeable($act_cssfile) ) {
							$title .= sprintf(__('Editing %s','nggallery'), $act_css_name);
						} else {
							$title .= sprintf(__('Browsing %s','nggallery'), $act_css_name);
						}
						if ( $theme_css_exists )
							{$title .= ' ' . __('(from the theme folder)','nggallery');}
						$title .= '</h3>';
						echo $title
						?>
					</div>
					<br class="clear" />
				</div> <!-- fileedit-sub -->
				<div id="templateside">
				<?php if ( $theme_css_exists ) : ?>
					<form id="filemover" name="filemover" method="post" style="background:white; padding: 1px 10px 10px;">
						<p><?php _e('To ensure your css file stays safe during upgrades, please move it to the right folder.','nggallery') ?></p>
						<input type="hidden" name="movecss" value="movecss" />
						<input type="hidden" name="oldpath" value="<?php echo $act_cssfile ?>" />
						<input class="button-primary action" type="submit" name="submit" value="<?php _e('Move file','nggallery') ?>" />
					</form>
					<br class="clear" />
				<?php endif; ?>
					<ul>
						<li><strong><?php _e('Author','nggallery') ?>:</strong> <?php echo $act_css_author ?></li>
						<li><strong><?php _e('Version','nggallery') ?>:</strong> <?php echo $act_css_version ?></li>
					</ul>
					<p><strong><?php _e('Description','nggallery') ?>:</strong></p>
					<p class="description"><?php echo $act_css_description ?></p>
					<p><strong><?php _e('File location','nggallery') ?>:</strong></p>
					<p class="description"><?php echo $act_cssfile; ?></p>
				</div>
				<?php if ( !$error ) { ?>
				<form name="template" id="template" method="post">
					<?php wp_nonce_field('ngg_style') ?>
					<div>
						<textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1"  class="codepress css"><?php echo $content ?></textarea>
						<input type="hidden" name="updatecss" value="updatecss" />
						<input type="hidden" name="folder" value="<?php echo $act_css_folder ?>" />
						<input type="hidden" name="file" value="<?php echo $act_cssfile ?>" />
					</div>
					<?php if ( is_writeable($act_cssfile) ) : ?>
					<p class="submit"><input class="button-primary action" type="submit" name="submit" value="<?php _e('Update File','nggallery') ?>" tabindex="2" /></p>
					<?php else : ?>
					<p><em><?php _e('If this file were writable you could edit it.','nggallery'); ?></em></p>
					<?php endif; ?>
				</form>
				<?php 
					} else {
						echo '<div class="error"><p>' . __('This file does not exist. Double check the name and try again.','nggallery') . '</p></div>';
					} 
				?>
				<div class="clear"> &nbsp; </div>
			</div> <!-- wrap-->
			<?php
				} //end if ( !is_multisite() || is_super_admin() )
	}
}