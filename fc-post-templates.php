<?php
/*
Plugin Name: FC Post Templates
Description: Adds Page-like custom template support for Posts and Custom Post Types.
Author: Federico Cingolani
Version: 0.1.0
Author URI: http://fcingolani.com.ar/
License: GPLv2
*/


function fcpt_get_post_template_slug( $post_id = null ) {
	$post     = get_post( $post_id );
	$template = get_post_meta( $post->ID, '_fcpt_post_template', true );
	if ( ! $template || 'default' == $template )
		return '';
	return $template;
}

function fcpt_post_template_meta_box( $post ) {
	if ( post_type_supports( $post->post_type, 'custom-template' )	) {
		$template = get_post_meta( $post->ID, '_fcpt_post_template', true ) ?: false;

		wp_nonce_field( plugin_basename( __FILE__ ), 'fcpt_nonce' );
		?>
		<select name="page_template" id="page_template">
		<option value='default'><?php _e( 'Default Template' ); ?></option>
		<?php page_template_dropdown( $template ); ?>
		</select>
		<?php
	}
}

function fcpt_add_meta_boxes()
{
	global $post;

	if ( post_type_supports( $post->post_type, 'custom-template' ) )
		add_meta_box( 'posttemplatediv', __( 'Template' ), 'fcpt_post_template_meta_box', null, 'side', 'core' );
}

function fcpt_save_post()
{
	global $post;

	$post_type     = $_POST['post_type'];
	$post_type_obj = get_post_type_object( $post_type );

	if ( ! post_type_supports( $post->post_type, 'custom-template' ) || ! current_user_can( $post_type_obj->cap->edit_post, $post_id ) )
		return;

	if ( ! isset( $_POST['fcpt_nonce'] ) || ! wp_verify_nonce( $_POST['fcpt_nonce'], plugin_basename( __FILE__ ) ) )
		return;

	$post_ID = $_POST['post_ID'];

	$page_template = sanitize_text_field( $_POST['page_template'] );

	add_post_meta( $post_ID, '_fcpt_post_template', $page_template, true ) or
	update_post_meta( $post_ID, '_fcpt_post_template', $page_template );
}


function fcpt_set_template( $template ) {
	global $post;

	return locate_template( fcpt_get_post_template_slug( $post->ID ) ) ?: $template;
}

add_action( 'add_meta_boxes', 'fcpt_add_meta_boxes' );
add_action( 'save_post', 'fcpt_save_post' );
add_action( 'single_template', 'fcpt_set_template' );
