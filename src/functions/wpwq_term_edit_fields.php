<?php
/*
	grunt.concat_in_order.declare('wpwq_term_edit_fields');
	grunt.concat_in_order.require('init');
*/


function wpwq_term_edit_fields_metabox( array $meta_boxes ) {

	$prefix = 'wpwq_';

	$meta_boxes['wpwq_'] = array(
		'id'            => $prefix . '_term_fields',
		'title'         => $prefix . '_term_fields',
		'object_types'  => wpwq_get_taxs(),
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
		'fields'        => array(
			
			array(
				'name'    => __( 'Description', 'wpwq' ),
				'desc'    =>
					__('Field added by <a title="Settings -> Waterproof Wrap Query Plugin" target="_blank" href="' . get_admin_url() . 'options-general.php?page=wpwq_options">Waterproof Wrap Query Plugin</a>','wpwq')
					. '<br>' .
					__('Description will be used in some waterproof wrappers but but other wordpress functions and plugins will still use the standard term description.', 'wpwq')
					. '<br>' .
					__('Use language tags for multilanguage', 'wpwq') . ': ' . '<span>[:de]Text in deutscher Sprache[:en]Text in english language</span>',
				'id'      => $prefix . 'desc',
				'type'    => 'wysiwyg',
				'options' => array(
					'textarea_rows' => 5,
				),
				'show_on_cb' => 'wpwq_term_edit_fields_show_on_cb'
			),

			array(
				'name'    => __( 'Shortdescription', 'wpwq' ),
				'desc'    =>
					__('Field added by <a title="Settings -> Waterproof Wrap Query Plugin" target="_blank" href="' . get_admin_url() . 'options-general.php?page=wpwq_options">Waterproof Wrap Query Plugin</a>','wpwq')
					. '<br>' .
					__('Shortdescription will be used in some waterproof wrappers.', 'wpwq')
					. '<br>' .
					__('Use language tags for multilanguage', 'wpwq') . ': ' . '<span>[:de]Text in deutscher Sprache[:en]Text in english language</span>',
				'id'      => $prefix . 'desc_short',
				'type'    => 'wysiwyg',
				'options' => array(
					'textarea_rows' => 5,
				),
				'show_on_cb' => 'wpwq_term_edit_fields_show_on_cb'
			),
			
			array(
				'name'    => __( 'Featured Image', 'wpwq' ),
				'desc'    =>
					__('Field added by <a title="Settings -> Waterproof Wrap Query Plugin" target="_blank" href="' . get_admin_url() . 'options-general.php?page=wpwq_options">Waterproof Wrap Query Plugin</a>','wpwq')
					. '<br>' .
					__('Featured Image will be used in some waterproof wrappers.', 'wpwq'),
				'id'      => $prefix . 'image_featured',
				'type'    => 'file',
				'options' => array(
					'url' => false,
					'add_upload_file_text' => 'Add Image',
				),
				'query_args'   => array(
					'type' => 'image',
				),
				'show_on_cb' => 'wpwq_term_edit_fields_show_on_cb'
			),
			
		),
	);

	return $meta_boxes;
}
add_filter('cmb2-taxonomy_meta_boxes', 'wpwq_term_edit_fields_metabox');



// show on cb
function wpwq_term_edit_fields_show_on_cb( $field ) {
	$prefix = 'wpwq_';
	$field_id_raw = str_replace( $prefix, '' , $field->args( 'id' ));
	$term_fields = wpwq_get_option( $prefix . 'term_fields' );
	
	if ( gettype($term_fields) != 'array') return false;
	
	return in_array(
		$field_id_raw,
		$term_fields
	);
}


?>