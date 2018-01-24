<?php
/*
	grunt.concat_in_order.declare('Wpwq_wrap_query_shortcode');
	grunt.concat_in_order.require('init');
	
	grunt.concat_in_order.require('wpwq_get_post_types');
	grunt.concat_in_order.require('Wpwq_wrapper');
	grunt.concat_in_order.require('Wpwq_wrapper_single');
	grunt.concat_in_order.require('wpwq_wrapper_init');
	grunt.concat_in_order.require('wpwq_options_page');
	
*/

/*
	requires
		src/sass/wpwq_desc_metabox.scss
*/


class Wpwq_wrap_query_shortcode {
	
	protected $shortcode_name;
	protected $shortcode_atts;
	protected $wrapper_args = array();
	protected $query_args = array();
	
	protected $curr_uniques_in_meta_unused = array();
	
	
	function __construct(){
		$this->shortcode_name = 'wrap_query';
		$this->init_shortcode_atts();
		
		add_action( 'cmb2_admin_init', array( $this, 'desc_metabox' ) );
		$prefix = 'wpwq_wrap_';
		add_action( 'cmb2_after_post_form_' . $prefix . 'desc', array( $this, 'desc_metabox_enqueue_style'), 10, 2 );
		
		add_shortcode( $this->shortcode_name, array( $this, 'shortcode_function' ) );
		
		add_action( 'save_post', array( $this, 'update_post_meta_wpwq_uq'), 80, 3 );
	}
	
	protected function init_shortcode_atts(){
		$this->shortcode_atts = array(
			'unique' => array(
					'accepts' => '[string]',
					'default' => '',
					'desc' => __('<span class="required">required!</span> Unique identifier. Just some kind of simple string. Please no special signs','wpwq')
				),
			'query_obj' => array(
					'accepts' => '"post"|"term"|"link"',
					'default' => 'post',
					'desc' => __('type of queried object','wpwq')
				),
			'query_args' => array(
					'accepts' => 'JSON',
					'default' => '',
					'desc' => __('<span class="required">required!</span> A JSON formatted get_posts($args) arguments array','wpwq')
				),			
			'wrapper' => array(
					'accepts' => 'string',
					'default' => '',
					'desc' => __('<span class="required">required!</span> Name of the wrapper.','wpwq')
				),					
			'wrapper_args' => array(
					'accepts' => 'JSON',
					'default' => '',
					'desc' => __('<span class="required">required!</span> A JSON formatted properties array for the wrapper object','wpwq')
				),					
			'debug' => array(
					'accepts' => 'bool',
					'default' => 'false',
					'desc' => __('Just for debugging. Will output the query_args and wrapper_args','wpwq')
				),	
		);
	}
	
	protected function get_shortcode_atts_list( $arr, $drop = null ){
	
		$return = '<ul class="attr">';
		
		foreach( $arr as $attkey => $attval ){
			
			if ( $attkey !== $drop ){
				$return .= '<li>' . $attkey;
				$return .= '<ul>';
				foreach ( $attval as $key => $val ){
					switch ($key) {
						case 'accepts':
							$attr_key = __('Accepts', 'wpwq');
							break;
						case 'default':
							$attr_key = __('Default', 'wpwq');
							break;
						case 'desc':
							$attr_key = __('Description', 'wpwq');
							break;
						default:
							$attr_key = $key;
					}
					$return .= '<li>' . $attr_key . ': ' . $val;
				}
				$return .= '</ul>';
			}
		}
		$return .= '</ul>';
		
		return $return;
	}
	
	public function desc_metabox(){
	
		$prefix = 'wpwq_wrap_';
		
		// Initiate the metabox
		$cmb = new_cmb2_box( array(
			'id'			=> $prefix . 'desc',
			'title'			=> __( 'Waterproof [wrap_query] shortcode docs', 'wpwq' ),
			'object_types'	=> wpwq_get_post_types(),
			'context'      => 'normal',		//  'normal', 'advanced', or 'side'
			'priority'     => 'high',		//  'high', 'core', 'default' or 'low'
			'show_names'   => true,			// Show field names on the left
			'show_on_cb'    => 'wpwq_desc_metabox_show_on_cb'
		) );
		
		$cmb->add_field( array(
			'id'   => $prefix . 'no_wrappers_installed',
			'type' => 'title',
			'render_row_cb' => 'wpwq_render_row_cb_no_wrappers_installed'
		) );
		
		$cmb->add_field( array(
			'id'   => $prefix . 'advertising',
			'type' => 'title',
			'render_row_cb' => 'wpwq_render_row_cb_advertising'
		) );
		
		// add fields
		$cmb->add_field( array(
			'desc' => __('Place a [wrap_query] shortcode somewhere inside the content. It will query the database, wraps each result in something fancy and pastes the listing in the content.<br>','wpwq'),
			'type' => 'title',
			'id'   => $prefix . 'desc_1',
		) );
		
		$cmb->add_field( array(
			'desc' => __('The "query_obj" attribute specifies the type of queried object. It accepts "post"|"term"|"link."','wpwq'),
			'type' => 'title',
			'id'   => $prefix . 'desc_2',
		) );	
		
		$cmb->add_field( array(
			'desc' => __('The "wrapper_args" attribute accepts an JSON formatted array, holding the parameters to fetch posts using the functions: "get_posts()", "get_terms()" and "get_bookmarks()".<br>
			Check <a title="Class Reference/WP Query" target="_blank" href="https://codex.wordpress.org/Class_Reference/WP_Query#Parameters">the parameters section</a> of the "WP_Query class" documentation and <a title="Template Tags/get posts" target="_blank" href="https://codex.wordpress.org/Template_Tags/get_posts#Parameters">the parameters section</a> of the "get_posts Template Tag" documentation for a list of parameters.<br>
			Addittionaly it accepts "this__" (double underscore) as a part of value to refeer to the current post object (exp: use "this__ID" to refeer to the current posts ID ).','wpwq'),
			'type' => 'title',
			'id'   => $prefix . 'desc_3',
		) );

		$cmb->add_field( array(
			'desc' => __('Full example with bxslider wrapper:','wpwq') . '<br>' . 
			'[wrap_query unique=\'somestring\' wrapper=\'bxslider\' query_args=\'{"post_type":"post","posts_per_page":"20"}\' wrapper_args=\'{"has_link":"true","content":"excerpt","bx_options":{"startSlide":"0","mode":"horizontal"}}\']',
			'type' => 'title',
			'id'   => $prefix . 'desc_4',
		) );
		
		
		
		// add group for atts_description
		$atts_box = $cmb->add_field( array(
			'id'				=> $prefix . 'props_box',
			'type'	 			=> 'group',
			'repeatable'		=> false,
			'options'			=> array(
				'group_title'	=> 'All shortcode attributes',
				'closed'     	=> true,
			),
		) );		
		
		$cmb->add_group_field( $prefix . 'props_box' , array(
			'desc' => __('
				Example usage for the "query_args" attribute: You want to query 10 posts of the current posts post_type? Paste this attribute in the [wrap_query] shortcode:<br>
				query_args=\'{"post_type":"this__post_type","posts_per_page":"10"}\'','wpwq'),
			'type' => 'title',
			'id'   => $prefix . 'atts_description',
		) );
		
		$cmb->add_group_field( $prefix . 'props_box' , array(
			'desc' => $this->get_shortcode_atts_list($this->shortcode_atts),
			'type' => 'title',
			'id'   => $prefix . 'atts_listing',
		) );
		
		$cmb->add_field( array(
			'desc' => __('Wrappers are configured with the "wrapper_args" shortcode attribute.<br>
				"Wrapper_args" accepts an JSON formatted array.<br>
				All wrappers share the "arguments for all" and have some additional arguments.<br>
				This are all available wrappers:','wpwq'),
			'type' => 'title',
			'id'   => $prefix . 'wrapper_list_header',
		) );
		
		// add group for each wrapper
		$wrappers = array_replace(array_flip(array('wpwq_wrapper')), $GLOBALS['wpwq_wrapper_types']->get_types() );
		
		foreach ( $wrappers as $wrapper_name => $values) {

			$atts_box = $cmb->add_field( array(
				'id'				=> $prefix . 'wrapper_' . $wrapper_name,
				'type'	 			=> 'group',
				'repeatable'		=> false,
				'options'			=> array(
					'group_title'	=> ( $wrapper_name != 'wpwq_wrapper' ? $wrapper_name .' wrapper' : 'arguments for all' ),
					'closed'     	=> true,
				),
			) );			
			
			if ( isset($values['desc'])){
				$cmb->add_group_field( $prefix . 'wrapper_' . $wrapper_name , array(
					'desc' => $values['desc'],
					'type' => 'title',
					'id'   => $prefix . 'wrapper' . $wrapper_name,
				) );
			}
			
			if ( isset($values['args'])){
				$cmb->add_group_field( $prefix . 'wrapper_' . $wrapper_name , array(
					'desc' => $this->get_shortcode_atts_list($values['args'], 'count_total'),
					'type' => 'title',
					'id'   => $prefix . 'wrapper' . $wrapper_name . '_args_list',
				) );
			}

		}
		
	}
	
	public function desc_metabox_enqueue_style( $post_id, $cmb ) {
		wp_register_style( 'wpwq_desc_metabox', plugin_dir_url( __FILE__ ) . 'css/wpwq_desc_metabox.css', false );
		wp_enqueue_style( 'wpwq_desc_metabox' );
	}

	protected function extract_shortcode_atts( $atts ) {
		
		// extract atts and store it in $this->shortcode_atts array
		$atts_defaults = array();
		foreach( $this->shortcode_atts as $key => $value) {
			// $atts_defaults[$key] = $value[1];
			$atts_defaults[$key] = $value['default'];
			$this->shortcode_atts[$key]['prop'] = shortcode_atts( $atts_defaults, $atts, $this->shortcode_name )[$key];
		}
		
		// extract wrapper_args and query_args from $this->shortcode_atts array
		$att_keys = array(
			'wrapper_args', 
			'query_args'
		);
		foreach( $att_keys as $att_key){
			$this->$att_key = array();
			
			if ( strpos( $this->shortcode_atts[$att_key]['prop'], '{'  ) === 0 ){
				// is json
				$json = strip_tags(str_replace( ' ', '', $this->shortcode_atts[$att_key]['prop']));
				$this->$att_key = ( null !== json_decode( $json, true ) ? json_decode( $json, true ) : array() );
			}
		}
		

		// exchange recursive in $this->query_args: 'true'/'false' with bool
		$query_args = $this->query_args;
		array_walk_recursive ( $query_args , function( &$val, $key ){
			$val = ( $val === 'true' ) ? true : $val;
			$val = ( $val === 'false' ) ? false : $val;
		});
		$this->query_args = $query_args;
		
		// exchange recursive 'this__'$s in $this->query_args with $GLOBALS['post']->$s
		$query_args = $this->query_args;
		array_walk_recursive ( $query_args , function( &$val, $key ){
			if ( strpos( $val, 'this__' ) === 0 ){
				$s = str_replace( 'this__', '', $val );
				$val = $GLOBALS['post']->$s;
			}
		});
		$this->query_args = $query_args;
		
		// change query_args retrurn only 'ids'
		if ( $this->shortcode_atts['query_obj']['prop'] != 'link' ){
			$this->query_args['fields'] = 'ids';
		}

		// add unique to wrapper_args
		if (! array_key_exists( 'unique', $this->wrapper_args ) ){
			$this->wrapper_args['unique'] = $this->shortcode_atts['unique']['prop'];
		}
		
		// fallback ... if query_obj is term and "taxonomy" field is empty, set to "category"
		if ( $this->shortcode_atts['query_obj']['prop'] == 'term' && ! array_key_exists('taxonomy', $this->query_args) ){
			$this->query_args['taxonomy'] = 'category';
		}
		
		$this->query_args = apply_filters('wpwq_query_args', $this->query_args, $this->shortcode_atts['query_obj']['prop'] );
		$this->query_args = apply_filters('wpwq_query_args' . $atts['unique'], $this->query_args, $this->shortcode_atts['query_obj']['prop'] );
		
	}

	public function shortcode_function( $atts ) {
		global $post;
		
		$this->extract_shortcode_atts( $atts );
		$atts = shortcode_atts( array_map( function($i){return $i['prop']; }, $this->shortcode_atts ), $atts, $this->shortcode_name);

		if ( strlen($atts['unique']) == 0 ) return;
		
		if ( is_admin()){
				$wpwq_uq = (get_post_meta(get_the_ID(), 'wpwq_uq', true) ? get_post_meta(get_the_ID(), 'wpwq_uq', true) : array());
				$wpwq_uq[$atts['unique']] = array(
					'unique' => $atts['unique'],
					'query_obj' => $atts['query_obj'],
					'query_args' => $this->query_args,
					'has_link' => $this->wrapper_args['has_link']
				);
				update_post_meta(get_the_ID(), 'wpwq_uq', $wpwq_uq);
			
		} else {
			
			switch ( $atts['query_obj'] ) {
				case 'post':
					$objs = apply_filters( 'wpwq_wrapper_objs_' . $atts['unique'], get_posts( $this->query_args ));
					$this->wrapper_args['count_total'] = count($objs);
					break;
				case 'term':
					$objs = apply_filters( 'wpwq_wrapper_objs_' . $atts['unique'], get_terms( $this->query_args ));
					$this->wrapper_args['count_total'] = count($objs);
					break;
				case 'link':
					$objs = apply_filters( 'wpwq_wrapper_objs_' . $atts['unique'], get_bookmarks( $this->query_args ));
					$this->wrapper_args['count_total'] = count($objs);
					break;
				default:
					// silence ...
			}
			
			if ( $atts['debug'] == 'true' ){
				print('<pre>');
					print_r('query_args ');
					print_r($this->query_args);
					print('<br>');		
					print_r('wrapper_args ');
					print_r($this->wrapper_args);
					print('<br>');		
				print('</pre>');			
			}
		

			// return the wrapped query
			return wpwq_get_wrapper( $atts['wrapper'], $atts['query_obj'], $objs, $this->wrapper_args );
			wp_reset_postdata();
		}
		
		
	}
	
	public function update_post_meta_wpwq_uq( $post_id, $post, $update ) {
		
		$post_content_raw = $post->post_content;
		
		if ( empty( $post_content_raw ) )
			return;
		
		// find wrap_query shortcodes in content
		preg_match_all( "/(\[wrap_query)[^\]]*/", $post_content_raw, $matches );
		if (! $matches ) return;
		$curr_shortcodes = array_map( function($v){ 
				return $v . ']';
			}, ( array_key_exists('0', $matches) ? $matches[0] : array() ));
		
		// exec each shortcode and store the uniques in $curr_uniques
		// during shortcode exec, the unique will be added to the meta
		$curr_uniques = array();
		foreach( $curr_shortcodes as $short_code ){
			do_shortcode($short_code);
			preg_match_all( "/(unique=')[^']*/", $short_code, $matches );
			preg_match_all( '/(unique=")[^"]*/', $short_code, $matches_2 );
			$matches = array_key_exists('0', $matches) ? $matches[0] : array();
			$matches_2 = array_key_exists('0', $matches_2) ? $matches_2[0] : array();
			$curr_uniques[] = array_map( function($v){ 
						$v = str_replace("unique='", '', $v );
						$v = str_replace('unique="', '', $v );
						return $v;
					}, ( array_merge($matches, $matches_2) ));		
		}
		$curr_uniques = array_map( function($v){ 
			return $v[0];
		}, $curr_uniques);
		
		// get the uniques from post meta
		$wpwq_uq = get_post_meta( $post->ID, 'wpwq_uq', true);
		
		if ( ! empty( $wpwq_uq ) ) {
		
			$curr_uniques_in_meta = array_map( function($k, $v){
						return $k;
					},  (array) array_keys( $wpwq_uq ), $wpwq_uq);	
			
			// compare the curr_uniques and the curr_uniques_in_meta 
			// store the unused meta uniques
			$this->curr_uniques_in_meta_unused = array_diff( (array) $curr_uniques_in_meta, $curr_uniques );
			
			// filter the meta uniques
			// we only need the uniques taht are in use 
			$post_meta_uniques_filtered = array_filter( (array) $wpwq_uq, function($val, $key) {
				return ( in_array( $key, (array) $this->curr_uniques_in_meta_unused ) ? false : true );
			}, ARRAY_FILTER_USE_BOTH);			
		
		} else {
			$post_meta_uniques_filtered = $curr_uniques;
		
		}
		
		// update post meta with filtered array
		update_post_meta($post_id, 'wpwq_uq', $post_meta_uniques_filtered);
		
	}
}



// init shortcode
function wpwq_init_wrap_query_shortcode(){
	$wrap_query_shortcode = new Wpwq_wrap_query_shortcode();
}
add_action( 'admin_init', 'wpwq_init_wrap_query_shortcode' );
add_action( 'init', 'wpwq_init_wrap_query_shortcode' );


// show_on_cb desc_metabox
function wpwq_desc_metabox_show_on_cb(){
	$prefix = 'wpwq_';
	$display_docs = wpwq_get_option( $prefix . 'display_docs' );
	
	switch ( $display_docs ) {
		case 'display_all':
			return true;
			break;
		case 'display_admin':
			return ( current_user_can('administrator') ? true : false );
			break;
		case 'hide_all':
			return false;
			break;
		default:
			return true;
	}
}







?>