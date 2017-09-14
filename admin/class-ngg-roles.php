<?php

include_once('class-ngg-post-admin-page.php');


/**
 * The roles admin screen
 */
class NGG_Roles extends NGG_Post_Admin_Page {

	public function display() {
		parent::display();

		?>
		<div class="wrap">
			<h2><?php _e('Roles / capabilities', 'nggallery') ;?></h2>
			<p><?php _e('Select the lowest role which should be able to access the following capabilities. NextCellent Gallery supports the standard roles from WordPress.', 'nggallery') ?> <br />
				<?php _e('For a more flexible user management you can use the', 'nggallery') ?> <a href="http://wordpress.org/extend/plugins/capsman/" target="_blank">Capability Manager</a>.</p>
			<form name="addroles" id="addroles" method="POST" accept-charset="utf-8" >
				<?php wp_nonce_field('ngg_addroles') ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="general"><?php _e('NextCellent Gallery overview', 'nggallery') ;?></label></th>
						<td><select name="general" id="general"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Gallery overview') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="tinymce"><?php _e('Use TinyMCE Button / Add Media', 'nggallery') ;?></label></th>
						<td><select name="tinymce" id="tinymce"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Use TinyMCE') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="add_gallery"><?php _e('Add gallery / Upload images', 'nggallery') ;?></label></th>
						<td><select name="add_gallery" id="add_gallery"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Upload images') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="manage_gallery"><?php _e('Manage gallery', 'nggallery') ;?></label></th>
						<td><select name="manage_gallery" id="manage_gallery"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Manage gallery') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="manage_others"><?php _e('Manage others gallery', 'nggallery') ;?></label></th>
						<td><select name="manage_others" id="manage_others"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Manage others gallery') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="manage_tags"><?php _e('Manage tags', 'nggallery') ;?></label></th>
						<td><select name="manage_tags" id="manage_tags"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Manage tags') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="edit_album"><?php _e('Edit Album', 'nggallery') ;?></label></th>
						<td><select name="edit_album" id="edit_album"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Edit album') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="change_style"><?php _e('Change style', 'nggallery') ;?></label></th>
						<td><select name="change_style" id="change_style"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Change style') ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="change_options"><?php _e('Change options', 'nggallery') ;?></label></th>
						<td><select name="change_options" id="change_options"><?php wp_dropdown_roles( $this->ngg_get_role('NextGEN Change options') ); ?></select></td>
					</tr>
				</table>
				<div class="submit"><input type="submit" class="button-primary" name= "update_cap" value="<?php _e('Update capabilities', 'nggallery') ;?>"/></div>
			</form>
		</div>
		<?php
	}


	protected function processor() {
		if ( isset($_POST['update_cap']) ) {

			check_admin_referer('ngg_addroles');

			// now set or remove the capability
			$this->ngg_set_capability(sanitize_text_field($_POST['general']),"NextGEN Gallery overview");
			$this->ngg_set_capability(sanitize_text_field($_POST['tinymce']),"NextGEN Use TinyMCE");
			$this->ngg_set_capability(sanitize_text_field($_POST['add_gallery']),"NextGEN Upload images");
			$this->ngg_set_capability(sanitize_text_field($_POST['manage_gallery']),"NextGEN Manage gallery");
			$this->ngg_set_capability(sanitize_text_field($_POST['manage_others']),"NextGEN Manage others gallery");
			$this->ngg_set_capability(sanitize_text_field($_POST['manage_tags']),"NextGEN Manage tags");
			$this->ngg_set_capability(sanitize_text_field($_POST['edit_album']),"NextGEN Edit album");
			$this->ngg_set_capability(sanitize_text_field($_POST['change_style']),"NextGEN Change style");
			$this->ngg_set_capability(sanitize_text_field($_POST['change_options']),"NextGEN Change options");

			nggGallery::show_message(__('Updated capabilities',"nggallery"));
		}
	}

	private function ngg_get_sorted_roles() {
		// This function returns all roles, sorted by user level (lowest to highest)
		global $wp_roles;
		$roles = $wp_roles->role_objects;
		$sorted = array();

		if( class_exists('RoleManager') ) {
			foreach( $roles as $role_key => $role_name ) {
				$role = get_role($role_key);
				if( empty($role) ) continue;
				$role_user_level = array_reduce(array_keys($role->capabilities), array('WP_User', 'level_reduction'), 0);
				$sorted[$role_user_level] = $role;
			}
			$sorted = array_values($sorted);
		} else {
			$role_order = array("subscriber", "contributor", "author", "editor", "administrator");
			foreach($role_order as $role_key) {
				$sorted[$role_key] = get_role($role_key);
			}
		}
		return $sorted;
	}

	private function ngg_get_role($capability){
		// This function return the lowest roles which has the capabilities
		$check_order = $this->ngg_get_sorted_roles();

		$args = array_slice(func_get_args(), 1);
		$args = array_merge(array($capability), $args);

		foreach ($check_order as $check_role) {
			if ( empty($check_role) )
				return false;

			if (call_user_func_array(array(&$check_role, 'has_cap'), $args))
				return $check_role->name;
		}
		return false;
	}

	private function ngg_set_capability($lowest_role, $capability){
		// This function set or remove the $capability
		$check_order = $this->ngg_get_sorted_roles();

		$add_capability = false;

		foreach ($check_order as $the_role) {
			$role = $the_role->name;

			if ( $lowest_role == $role )
				$add_capability = true;

			// If you rename the roles, then please use a role manager plugin

			if ( empty($the_role) )
				continue;

			$add_capability ? $the_role->add_cap($capability) : $the_role->remove_cap($capability) ;
		}
	}
}