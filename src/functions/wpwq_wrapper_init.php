<?php
/*
	grunt.concat_in_order.declare('wpwq_wrapper_init');
	grunt.concat_in_order.require('init');
	
	grunt.concat_in_order.require('Wpwq_wrapper_types');
	grunt.concat_in_order.require('Wpwq_wrapper');
	grunt.concat_in_order.require('Wpwq_wrapper_single');
*/

function wpwq_get_wrapper( $type = null, $query_obj = 'post', $objs = null, $wrapper_args = null) {
	
	if ($type === null || ! array_key_exists( $type, $GLOBALS['wpwq_wrapper_types']->get_types())){
		return '';	
	}
	
	$wrapper_type_name = 'Wpwq_wrapper' . '_' . $type;
	
	$wpwq_wrapper = new $wrapper_type_name( $query_obj, $objs, $wrapper_args );
	return $wpwq_wrapper->get_wrapper();
}



?>