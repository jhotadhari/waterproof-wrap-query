<?php
/*
	grunt.concat_in_order.declare('wpwq_render_row_cb');
	grunt.concat_in_order.require('init');
*/

function wpwq_render_row_cb_advertising( $field_args, $field ) {
	
	$prefix_opt = 'wpwq_';
	echo ( wpwq_get_option( $prefix_opt . 'display_ads', 'yes') == 'yes' 
		? '<div class="waterproof-webdesign-logo"><a title="Waterproof-Webdesign" target="_blank" href="http://waterproof-webdesign.info/"><img src="' . plugin_dir_url( __FILE__ ) . '/images/waterproof-webdesign-logo.png" alt="Waterproof-Webdesign Logo"></a></div>' 
		: '' );	

	// return $field;
}

function wpwq_render_row_cb_no_wrappers_installed( $field_args, $field ) {
	global $wpwq_wrapper_types;
	
	echo ( count( $wpwq_wrapper_types->get_types() ) == 1 
		? '<span style="color:#f00;">' . __('No Wrappers installed','wpwq') . ' ' . sprintf( __('Check the <a title="WordPress Plugin Repository" target="_blank" href="%s">WordPress Plugin Repository</a> for Waterproof Wrapper Plugins.', 'wpwq') , 'https://wordpress.org/plugins/') . '</span>'
		: '' );	

	// return $field;
}
?>