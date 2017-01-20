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