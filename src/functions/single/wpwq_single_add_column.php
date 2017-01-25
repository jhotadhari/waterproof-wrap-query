<?php
/*
	grunt.concat_in_order.declare('wpwq_single_add_column');
	grunt.concat_in_order.require('init');
*/

add_action( 'admin_init', 'wpwq_single_add_column' );

// add coloumn
function wpwq_single_add_column_column( $columns ){
	return array_merge( $columns, array( 'wpwq_single' => __( 'Has single view?', 'wpwq' ) ) );
}

// add content to desc column
function wpwq_single_add_column_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wpwq_single' :
			
			$has_single = get_post_meta( $post_id, 'wpwq_opt_has_single' , true);
			
			switch ( $has_single ) {
				case 'no':
					$content = '-';
					break;
				default :	// yes
					$content = '<span style="font-size: large;">&#x2713</style>';
			}
			break;
	}		
	echo $content;
}

// loop post_types and hook/filter for each
function wpwq_single_add_column(){
	$wpwq_options_metabox = wpwq_get_option( 'wpwq_options_metabox' );
	if ( gettype($wpwq_options_metabox) != 'array') return;
	if ( ! in_array('opt_single_view', $wpwq_options_metabox)) return;
	
	$post_types = wpwq_get_post_types('array_key_val');
	
	if ( count($post_types) > 0 ){
		foreach ( $post_types as $k => $v ) {
			add_filter( 'manage_' . $k . '_posts_columns', 'wpwq_single_add_column_column' );
			add_action( 'manage_' . $k . '_posts_custom_column' ,  'wpwq_single_add_column_column_content', 10, 2 );
		}	
	}
}



?>