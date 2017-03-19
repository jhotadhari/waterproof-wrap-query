<?php
/*
Plugin Name: Waterproof Wrap Query
Plugin URI: http://waterproof-webdesign.info/plugins/waterproof-wrap-query
Description: Wrap your posts or terms in something fancy!
Version: 0.0.3
Author: jhotadhari
Author URI: http://waterproof-webdesign.info/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpwq
Domain Path: /languages
Tags: shortcode,wrapper,widget,get_posts,get_terms,lists,listing
*/

/*
	grunt.concat_in_order.declare('_plugin_info');
*/

?>
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
<?php
/*
	grunt.concat_in_order.declare('cmb2_init');
	grunt.concat_in_order.require('init');
*/



//cmb2 init
if (! function_exists('wpwq_cmb2_init')){
	function wpwq_cmb2_init() {
		include_once plugin_dir_path( __FILE__ ) . 'includes/webdevstudios/cmb2/init.php';
	}
}
add_action('admin_init', 'wpwq_cmb2_init', 3);
add_action('init', 'wpwq_cmb2_init', 3);


//cmb2-taxonomy init
function wpwq_cmb2_tax_init() {

	if (! class_exists('CMB2_Taxonomy')) {
		include_once plugin_dir_path( __FILE__ ) . 'includes/jcchavezs/cmb2-taxonomy/init.php';

	}
}
add_action('admin_init', 'wpwq_cmb2_tax_init', 3);
add_action('init', 'wpwq_cmb2_tax_init', 3);



//cmb2-qtranslate init
function wpwq_cmb2_init_qtranslate() {
		
	wp_register_script('cmb2_qtranslate_main', plugin_dir_url( __FILE__ ) . '/includes/jmarceli/integration-cmb2-qtranslate/dist/scripts/main.js', array('jquery'));
	wp_enqueue_script('cmb2_qtranslate_main');
}
add_action('admin_enqueue_scripts', 'wpwq_cmb2_init_qtranslate');
//add_action('wp_enqueue_scripts', 'wpwq_cmb2_init_qtranslate');



?>