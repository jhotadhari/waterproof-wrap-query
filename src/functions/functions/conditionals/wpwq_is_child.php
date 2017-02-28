<?php
/*
	grunt.concat_in_order.declare('wpwq_is_child');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('wpwq_get_post_by_slug');
                                      
*/

function wpwq_is_child( $pid ) {
	if (! $pid ) return false;
	if ( is_page() ) return false;
	global $post;
	if (! $post ) return false;
	
	if (! is_numeric($pid) ) {
		$pid = wpwq_get_post_by_slug( $pid, 'id' );
	}
	
	$ancestors = get_post_ancestors( $post->$pid );
	
	if( in_array( $pid, $ancestors )) {
		return true;
	} else {
		return false;
	}
};

?>