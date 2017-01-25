<?php
/*
	grunt.concat_in_order.declare('wpwq_options_metabox');
	grunt.concat_in_order.require('init');
*/

// ??? option to not show this metabox

// ??? widget to generate menu without hidden posts


function wpwq_options_metabox() {
	
	// Start with an underscore to hide fields from custom fields list
	$prefix = 'wpwq_opt_';
	
	// Initiate the metabox
	$cmb = new_cmb2_box( array(
		'id'			=> $prefix . 'single_options',
		'title'			=> __( 'Wpwq Options', 'wpwq' ),
		'object_types'	=> wpwq_get_post_types(),
		'context'		=> 'side',
		'priority'		=> 'default',
		'show_names'	=> true,
		'show_on_cb'	=> 'wpwq_options_metabox_show_on_cb',
	) );

	$cmb->add_field( array(
		'id'   => $prefix . 'meta_desc',
		'type'    => 'title',
		'desc'    => __('Metabox added by <a title="Settings -> Waterproof Wrap Query Plugin" target="_blank" href="' . get_admin_url() . 'options-general.php?page=wpwq_options">Waterproof Wrap Query Plugin</a>','wpwq'),
	) );
	
	$cmb->add_field( array(
		'name' => __('Has Single', 'wpwq'),
		'id'   => $prefix . 'has_single',
		'type'    => 'radio_inline',
		'desc'    => __('Is this posty thing viewable as single? If not, user will be redirected to parent page or home. But this post can still be used for listings.','wpwq'),
		'default' => 'yes',
		'options' => array(
			'yes' => __( 'Yes', 'wpwq' ),
			'no'   => __( 'No', 'wpwq' ),
		),
	) );
	
}
add_action( 'cmb2_admin_init', 'wpwq_options_metabox' );



// show on cb	???
function wpwq_options_metabox_show_on_cb(){
	$prefix = 'wpwq_';
	return count( wpwq_get_option( $prefix . 'options_metabox' ) ) > 0 ? true : false;
}



// load styles
function wpwq_options_metabox_enqueue_style( $post_id, $cmb ) {
	wp_enqueue_style( 'wpwq_options_metabox', plugin_dir_url( __FILE__ ) . 'css/wpwq_options_metabox.css', false );
}
add_action( 'cmb2_after_post_form_' . 'wpwq_opt_' . 'single_options', 'wpwq_options_metabox_enqueue_style', 10, 2 );


?>