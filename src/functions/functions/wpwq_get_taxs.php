<?php
/*
	grunt.concat_in_order.declare('wpwq_get_taxs');
	grunt.concat_in_order.require('init');

*/

// find taxs and exclude some
function wpwq_get_taxs( $excl_arr = null ){
	$taxs = array();
	
	$taxs_all = get_taxonomies( array() , 'objects'); 
	if  ( $taxs_all ) {
		foreach ( $taxs_all as $tax ) {
			array_push( $taxs, $tax->name );
		}
	}
	
	$excl_arr = $excl_arr != null ? $excl_arr : array(
		'post_tag',
		'nav_menu',
		'link_category',
		'post_format',
	);
	$excl_arr = apply_filters('wpwq_get_taxs_exclude', $excl_arr );
	
	$taxs = array_diff( $taxs, $excl_arr );
	
	return $taxs;
}

?>