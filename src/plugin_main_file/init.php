<?php
/*
	grunt.concat_in_order.declare('init');
	grunt.concat_in_order.require('_plugin_info');
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function wpwq_get_required_php_ver() {
	return '5.6';
}

function wpwq_plugin_activate(){
    if ( version_compare( PHP_VERSION, wpwq_get_required_php_ver(), '<') ) {
        wp_die( wpwq_get_admin_notice() . '<br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}
register_activation_hook( __FILE__, 'wpwq_plugin_activate' );

function wpwq_load_functions(){
	if ( ! version_compare( PHP_VERSION, wpwq_get_required_php_ver(), '<') ){
		include_once(plugin_dir_path( __FILE__ ) . 'functions.php');
	} else {
		add_action( 'admin_notices', 'wpwq_print_admin_notice' );
	}
}
add_action( 'plugins_loaded', 'wpwq_load_functions', 1 );

function wpwq_print_admin_notice() {
	echo '<strong><span style="color:#f00;">' . wpwq_get_admin_notice() . '</span></strong>';
};


function wpwq_get_admin_notice() {
	$plugin_title = 'Waterproof Wrap Query';
	return sprintf(esc_html__( '"%s" plugin requires PHP version greater %s!', 'wpwq' ), $plugin_title, wpwq_get_required_php_ver());
}
?>