<?php
/*
	grunt.concat_in_order.declare('wpwq_wrapper_add_type');
	grunt.concat_in_order.require('init');
*/


/**
* Add type name to the $wpwq_wrapper_types object
*/
function wpwq_wrapper_add_type(){
	global $wpwq_wrapper_types;
	$wpwq_wrapper_types->add_type( array(
		'wpwq_wrapper' => array(
			'desc' => __('Following arguments will work with all wrappers.<br><br>
				Example: You want to strip all tags and shortcodes from the content and limit it to 55 characters? Paste this attribute in the [wrap_query] shortcode:<br>
				wrapper_args=\'{"content_strip":"full","content_limit":"55"}\'','wpwq'),
			'args' => array(
				'count_total' => array(
					'accepts' => 'int',
					'default' => '',
					'desc' => __('set by class','wpwq')
					),
				'content' => array(
					'accepts' => '"none"|"excerpt"|"full"',
					'default' => 'excerpt', 
					'desc' => __('Whats the content?','wpwq')
					),
				'content_strip' => array(
					'accepts' => 'string|"full"',
					'default' => '"shortcodes"',
					'desc' => __('Accepts a string with comma seperated values: , "none", "shortcodes", "style", "tags".<br>
						Or a string with sinlge value "full" to strip all three.<br>
						Notice: Even when unstripped ("none"), the [wrap_query] shortcode will be stripped.','wpwq')
					),
				'content_limit' => array(
					'accepts' => 'false|int',
					'default' => 'false', 
					'desc' => __('Number of characters in content. HTML tags will not be counted and the HTML Structure will be preserved.','wpwq')
					)
				)
			)
		)
	);
}
add_action( 'admin_init', 'wpwq_wrapper_add_type' );
add_action( 'init', 'wpwq_wrapper_add_type' );



?>