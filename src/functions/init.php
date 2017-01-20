<?php
/*
	grunt.concat_in_order.declare('init');
*/


// load_plugin_textdomain
function wpwq_load_textdomain(){
	
	load_plugin_textdomain(
		'wpwq',
		false,
		dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
	);
}
add_action( 'plugins_loaded', 'wpwq_load_textdomain' );


// // Enqueue admin styles
// function wpwq_styles_admin() {
// 	wp_register_style( 'wpwq_style_admin', plugin_dir_url( __FILE__ ) . 'css/style_admin.css', false );
// 	wp_enqueue_style( 'wpwq_style_admin' );
// }
// add_action('admin_enqueue_scripts', 'wpwq_styles_admin');	

// // Enqueue admin scripts
// function wpwq_scripts_admin() {
// 	wp_register_script('wpwq_script_admin', plugin_dir_url( __FILE__ ) . '/js/script_admin.min.js', array('jquery'));
// 	wp_enqueue_script( 'wpwq_script_admin');
// }
// add_action('admin_enqueue_scripts', 'wpwq_scripts_admin');	

// // Enqueue frontend styles
// function wpwq_styles_frontend() {
// 	wp_enqueue_style( 'wpwq_style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
// }
// add_action( 'wp_enqueue_scripts', 'wpwq_styles_frontend' );

// // Register frontend scripts
// function wpwq_scripts_frontend_register() {
// 	wp_register_script( 'wpwq_script', plugin_dir_url( __FILE__ ) . '/js/script.min.js', apply_filters('wpwq_script_frontend_deps', array('jquery') ), false , true);
// }
// add_action( 'wp_enqueue_scripts', 'wpwq_scripts_frontend_register' );


// // Print frontend scripts
// function wpwq_scripts_frontend_print() {
// 	global $wpwq_localize;
	
// 	// hook
// 	do_action('wpwq_print_script');
	
// 	// wpwq_script
// 	$parse_data = $wpwq_localize->get_datas();
// 	wp_localize_script( 'wpwq_script', 'parse_data', $parse_data );
// 	wp_print_scripts('wpwq_script');
	
// }
// add_action('wp_footer', 'wpwq_scripts_frontend_print');	

?>