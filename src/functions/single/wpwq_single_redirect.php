<?php
/*
	grunt.concat_in_order.declare('wpwq_single_redirect');
	grunt.concat_in_order.require('init');
*/

function wpwq_single_redirect() {
	global $post;
	if ( !is_singular() || is_front_page()) return;
	
	$has_single = get_post_meta( $post->ID, 'wpwq_opt_has_single', true );
	
	if ( $has_single == 'no' ) {
	
		$parent = get_post_ancestors( $post );
		
		if ( empty( $parent ) ) {
			$link = home_url('/');



		} else {
			$link = get_permalink( get_post( $parent[0] ) );
		}

		
		wp_redirect( $link, 301 );
		exit();
	}
}
add_action( 'template_redirect', 'wpwq_single_redirect' );

?>