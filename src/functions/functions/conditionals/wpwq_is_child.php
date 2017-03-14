<?php
/*
	grunt.concat_in_order.declare('wpwq_is_child');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('wpwq_get_post_by_slug');
                                      
*/

function wpwq_is_child( $parent_id, $object_type = 'post', $resource_type = 'post_type') {
	if (! $parent_id ) return false;
	
	global $post;
	if (! $post ) return false;
	
	if (! is_numeric($parent_id) )
		$parent_id = wpwq_get_post_by_slug( $parent_id, 'id' )->ID;
	
	$ancestors = get_ancestors( $post->ID, $object_type, $resource_type );

	
	return in_array( $parent_id, $ancestors );
};


?>