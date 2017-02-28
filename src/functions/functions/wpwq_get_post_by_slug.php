<?php
/*
	grunt.concat_in_order.declare('wpwq_get_post_by_slug');
	grunt.concat_in_order.require('init');

*/


function wpwq_get_post_by_slug( $slug, $return = 'post' ) {

	if (!$slug) return false;
	
	$posts = get_posts(array(
            'name' => $slug,
            'posts_per_page' => 1,
            'post_type' => wpwq_get_post_types()
    ));
    
    if( $posts ) {
    	if ( $return == 'post' ) {
    		return $posts[0];
    	} elseif ( $return == 'id' ) {
    		return $posts[0]->ID;
    	}
    } else {
    	return false;
    }

};

?>