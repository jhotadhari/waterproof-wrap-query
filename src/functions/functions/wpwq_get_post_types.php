<?php
/*
	grunt.concat_in_order.declare('wpwq_get_post_types');
	grunt.concat_in_order.require('init');
*/


// get post types ... _builtin + custom
function wpwq_get_post_types( $return_type = null, $exclude = null){
	if ($return_type == null){
		$post_types = array('post', 'page');

		foreach ( get_post_types( array( '_builtin' => false), 'names' ) as $post_type ) {
		   array_push($post_types, $post_type);
		}

		if ( $exclude == null){
			return $post_types;

		} else {
			if ( gettype( $exclude ) != 'array')
				$exclude = array($exclude);
			
			return array_filter( $post_types, function( $val ) use ( $exclude ){
					return ( in_array( $val, $exclude ) ? false : true );
				} );
		}
	}
	
	if ($return_type == 'array_key_val'){
	
		$post_types = array(
			'post' => __('Post','para_text'),
			'page' => __('Page','para_text')
			);
		
		foreach ( get_post_types( array( '_builtin' => false), 'objects' ) as $post_type ) {

		   $post_types[$post_type->name] =  __($post_type->labels->name,'para_text');
		}

		if ( $exclude == null){
			return $post_types;

		} else {
			if ( gettype( $exclude ) != 'array')
				$exclude = array($exclude);
			
			return array_filter( $post_types, function( $key ) use ( $exclude ){
					return ( in_array( $key, $exclude ) ? false : true );
				}, ARRAY_FILTER_USE_KEY );
		}
	}
	
}

?>