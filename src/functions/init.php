<?php
/*
	grunt.concat_in_order.declare('init');
*/


// load_plugin_textdomain
function wpwq_load_textdomain(){
	
	load_plugin_textdomain(
		'wpwq',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'wpwq_load_textdomain' );



?>