<?php

require_once( dirname( dirname(__FILE__) ) . '/ngg-config.php');
require_once( NGGALLERY_ABSPATH . '/lib/image.php' );

if ( !is_user_logged_in() )
	die(__('Cheatin&#8217; uh?'));
	
if ( !current_user_can('NextGEN Manage gallery') ) 
	die(__('Cheatin&#8217; uh?'));

if ( !current_user_can( 'publish_posts' ) )
    die(__('Cheatin&#8217; uh?'));

global $wpdb;

$id = (int) $_GET['id'];

// let's get the image data
$picture = nggdb::find_image($id);

// use defaults the first time
$width  = empty ($ngg->options['publish_width'])  ? $ngg->options['thumbwidth'] : $ngg->options['publish_width'];
$height = empty ($ngg->options['publish_height']) ? $ngg->options['thumbheight'] : $ngg->options['publish_height'];
$align  = empty ($ngg->options['publish_align'])  ? 'none' : $ngg->options['publish_align'];

?>

<form id="form-publish-post" method="POST" accept-charset="utf-8">
<?php wp_nonce_field('publish-post') ?>
<input type="hidden" name="page" value="publish-post" />
<input type="hidden" name="pid" value="<?php echo $picture->pid; ?>" />
<table width="100%" border="0" cellspacing="3" cellpadding="3" >
	<tr valign="top">
		<th align="left"><?php _e('Post title','nggallery') ?></th>
		<td><input type="text" size="70" name="post_title" value="<?php echo esc_attr( $picture->alttext);  ?>" />
		<br /><small><?php _e('Enter the post title ','nggallery') ?></small></td>
	</tr>
	<tr valign="top">
		<th align="left"><?php _e('Width x height (in pixel)','nggallery') ?></th>
		<td><input type="text" size="5" maxlength="5" name="width" value="<?php echo $width; ?>" /> x <input type="text" size="5" maxlength="5" name="height" value="<?php echo $height; ?>" />
		<br /><small><?php _e('Size of the image','nggallery') ?></small></td>
	</tr>
	<tr valign="top">
		<th align="left"><?php _e('Alignment','nggallery') ?></th>
		<td><input type="radio" value="none" <?php checked('none', $align); ?> id="image-align-none" name="align"/>
            <label class="align" for="image-align-none"><?php _e('None','nggallery'); ?></label>
            <input type="radio" value="left" <?php checked('left', $align); ?> id="image-align-left" name="align"/>
            <label class="align" for="image-align-left"><?php _e('Left','nggallery'); ?></label>
            <input type="radio" value="center" <?php checked('center', $align); ?> id="image-align-center" name="align"/>
            <label class="align" for="image-align-center"><?php _e('Center','nggallery'); ?></label>
            <input type="radio" value="right" <?php checked('right', $align); ?> id="image-align-right" name="align"/>
            <label class="align" for="image-align-right"><?php _e('Right','nggallery'); ?></label>
        </td>
	</tr>
  	<tr align="right">
    	<td colspan="2" class="submit">
    		<input class="button-primary" type="submit" name="publish" value="<?php _e('Publish', 'nggallery');?>" />
    		&nbsp;
    		<input class="button-secondary" type="submit" name="draft" value="&nbsp;<?php _e('Draft', 'nggallery'); ?>&nbsp;" />
    	</td>
	</tr>
</table>
</form>