<?php
/*
	grunt.concat_in_order.declare('wpwq_strip_shortcode');
	grunt.concat_in_order.require('init');
*/

function wpwq_strip_shortcode( $content, $shortcodes = null) {
	if ( gettype( $shortcodes ) == 'string' ){
		$shortcodes = explode(',', str_replace(' ','',$shortcodes));
	}
	
	$pattern = get_shortcode_regex($shortcodes);
 
	if ( preg_match_all( '/'. $pattern .'/s', $content, $matches )
		&& array_key_exists(0, $matches )) {
	
		foreach( $matches[0] as $match ){
			$content = str_replace( $match, '', $content );

		}
	}
	
	return $content;
}

?>