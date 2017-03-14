<?php
/*
	grunt.concat_in_order.declare('wpwq_term_is_child');
	grunt.concat_in_order.require('init');
                                      
*/

function wpwq_term_is_child( $parent_id = false, $object_type = false, $resource_type = 'taxonomy' ) {
	if (! $parent_id ) return false;
	if (! $object_type ) return false;
	
	$term = get_queried_object();
	if (! $term || ! property_exists ( $term , 'term_id' ) ) return false;
	
	$ancestors = isset( $term->term_id ) ? get_ancestors( $term->term_id, $object_type, $resource_type ) : array();
	
	return in_array( $parent_id, $ancestors );
};

?>