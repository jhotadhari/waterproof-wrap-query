<?php
/*
	grunt.concat_in_order.declare('Wpwq_wrapper');
	grunt.concat_in_order.require('init');
	

	grunt.concat_in_order.require('wpwq_str_limit_html');
	grunt.concat_in_order.require('wpwq_strip_shortcode');
	grunt.concat_in_order.require('Wpwq_wrapper_types');
	grunt.concat_in_order.require('wpwq_wrapper_add_type');
	grunt.concat_in_order.require('wpwq_get_image_id');
*/

/**
* Wpwq_wrapper class
*
* Class to initialize a Wpwq_wrapper object
*
//???
//* @param	string	$template_type	The type oh header 'single', '404', 'author' ...
//* @param	Post	$post			The post object
//* @param	int		$sectioncount	The consecutively number of the section
*/
class Wpwq_wrapper {

	protected $type_name = '';
	
	protected $query_prepared = array();
      
	protected $wrapper_open = '';
	protected $wrapper_inner = '';
	protected $wrapper_close = '';
	
	protected $args = array();
	protected $args_single = array();

	function __construct( $query_obj = null , $objs = null, $args = null ) {
		$this->set_name( '' );
		$this->set_args( $args );

		$this->query_prepare( $query_obj, $objs );
		
		$this->set_wrapper_open();
		$this->set_wrapper_close();
	}

	protected function set_name( $type_name ) {
		$this->type_name = $type_name;
	}
	public function get_name() {
		return $this->type_name;
	}	
	protected function set_args( $args ) {
		$this->args = $args;
	}
	public function get_args() {
		return $this->args;
	}
	
	
	protected function set_args_single( $args_single ) {
		$this->args_single = $args_single;
	}
	public function get_args_single() {
		return $this->args_single;
	}		
	
	
	protected function query_prepare( $query_obj, $objs ){
	
		$query_prepared = array();
		
		foreach( $objs as $obj ){

			switch ( $query_obj ) {
				case 'post':			
				
					$obj_id = ( gettype($obj) == 'object' ? $obj->ID : $obj);
					$obj = get_post($obj_id);
		
					$obj_image_url = '';
		
					$obj_image = '';
					$obj_slug = $obj->post_name;
					
					$obj_link = get_permalink( $obj_id ) . '#' . $obj_slug;
					// $obj_str_title = ___($obj->post_title);
					$obj_str_title = __(get_the_title( $obj_id ));
					
					
					// obj_str_inner
					if ( array_key_exists('content', $this->args ) && $this->args['content'] == 'none' ){
						$obj_str_inner = '';
					} else {
					
						$content_strip = ( array_key_exists('content_strip', $this->args ) ? $this->args['content_strip'] : 'shortcodes' );
						
						$content_full = $obj->post_content;
						$content_full = wpwq_strip_shortcode( $content_full, 'wrap_query' );
						$content_full = $this->content_strip( $content_full, $content_strip );
						$content_full = apply_filters( 'the_content', $content_full );
						$content_full = __( $content_full );
		
						$content_excerpt = $obj->post_excerpt;
						$content_excerpt = wpwq_strip_shortcode( $content_excerpt, array('wrap_query') );
						$content_excerpt = $this->content_strip( $content_excerpt, $content_strip );
						$content_excerpt = apply_filters( 'the_excerpt', $content_excerpt );
						$content_excerpt = __( $content_excerpt );
						
						if ( array_key_exists('content', $this->args ) && $this->args['content'] == 'full' ){
							$obj_str_inner = ( strlen($content_full) > 0 ? $content_full : $content_excerpt );
						} else {
							$obj_str_inner = ( strlen($content_excerpt) > 0 ? $content_excerpt : $content_full );
						}
						
					}
					
					
		
					$obj_str_link = apply_filters('wpwq_trans_more_information',__( 'More information', 'wpwq'));
		
					if ( has_post_thumbnail( $obj_id ) ) {
						$obj_image_url = get_the_post_thumbnail_url(  $obj_id, 'thumbnail' );
						$obj_image = get_the_post_thumbnail(  $obj_id, 'thumbnail' );
					}
					

					break;
				case 'term':
					//$obj = get_term( $atts['id'], $atts['taxonomy']);
					
					$obj_id = ( gettype($obj) == 'object' ? $obj->term_id : $obj);
					$obj = get_term($obj_id);

					$obj_image_url = '';

					$obj_image = '';
					$obj_link = get_term_link( $obj ) . '#' . $obj->slug;
					$obj_slug = $obj->slug;
					$obj_str_title = __($obj->name);
					
					// obj_str_inner
					$content_type = ( array_key_exists('content', $this->args ) ? $this->args['content'] : 'excerpt' );
					
					$content_full = ( ! empty(get_term_meta($obj_id, '_cmb2_term_basics_desc')) ? get_term_meta($obj_id, '_cmb2_term_basics_desc', true) : '' );
					$content_full = apply_filters( 'the_content', $content_full );

					$content_full = __($content_full);

					
					$content_excerpt = ( ! empty(get_term_meta($obj_id, '_cmb2_term_basics_desc_short')) ? get_term_meta($obj_id, '_cmb2_term_basics_desc_short')[0] : '' );
					$content_excerpt = apply_filters( 'the_excerpt', $content_excerpt );
					$content_excerpt = __($content_excerpt );
					
					switch ( $content_type ) { 
						case 'full':
							$obj_str_inner = ( strlen($content_full) > 0 ? $content_full : $content_excerpt );
							break;
						case 'none':
							$obj_str_inner = '';
							break;
						default:
							$obj_str_inner = ( strlen($content_excerpt) > 0 ? $content_excerpt : $content_full );
					}
					
					$obj_str_link = apply_filters('wpwq_trans_more_information',__( 'More information', 'wpwq'));
					
					// ???
					// $term_image = theme_get_term_image($obj_id, 'featured', false);
					// if ( strlen($term_image) > 0 ) {
					// 	$obj_image_url = wp_get_attachment_image_src( wpwq_get_image_id($term_image), 'thumbnail')[0];
					// 	$obj_image = '<img width="150" height="150" class="wp-post-image" src="' . $obj_image_url .'">';
					// }
					break;
				case 'link':
					$obj_id = $obj->link_id;
	
					$obj_image_url = '';
					$obj_image = '';
					
					$obj_slug = '';
	
					$obj_link = $obj->link_url;
					$obj_str_title = $obj->link_name;
					
					
					if ( array_key_exists('content', $this->args ) && $this->args['content'] != 'none' ){
						$obj_str_inner = $obj->link_description;
					} else {
						$obj_str_inner = '';
					}
					
					$obj_str_link = apply_filters('wpwq_trans_more_information',__( 'More information', 'wpwq'));
					
					if ( $obj->link_image ) {
						$obj_image_url = $obj->link_image;
						$obj_image = '<img width="150" height="150" class="wp-post-image" src="' . $obj_image_url .'">';
					}
					
					break;
				default:
					// silence ...
			}
			
			// obj_str_inner	limit
			$content_limit = ( array_key_exists('content_limit', $this->args ) ? intval($this->args['content_limit']) : false );
			if ( gettype($content_limit) == 'integer' ){
				// $obj_str_inner = wp_trim_words (  $obj_str_inner, $content_limit  );
				$obj_str_inner = wpwq_str_limit_html (  $obj_str_inner, $content_limit, ' ...' );
			}
			
			// filter hook obj_str_inner
			if ( array_key_exists('unique', $this->args)){
				$obj_str_inner = apply_filters('wpwq_wrapper_str_inner_' . $this->args['unique'], $obj_str_inner, $query_obj, $obj);
			}
			
			$obj_arr = array(
				'id' => $obj_id,
				'image' => $obj_image,
				'image_url' => $obj_image_url,
				'link' => $obj_link,
				'str_title' => $obj_str_title,
				'str_inner' => $obj_str_inner,
				'str_link' => $obj_str_link,
			);
			
			$this->query_prepared[] = $obj_arr;

		}	
	
	}		
	
	protected function content_strip( $str, $content_strip = '' ) {
		if ( $content_strip == '' )
				return $str;
		
		if ( gettype( $content_strip ) == 'string' ) {
			if ($content_strip == 'full'){
				$content_strip = array('shortcodes', 'style', 'tags');
			} else {
				$content_strip = explode( ',', $content_strip );
			}
		} 
			
		if ( gettype( $content_strip ) == 'array') {
			
			foreach ( $content_strip as $strip ){
				switch ( $strip ) {
					//case 'shortcodes_content':
					//	$str = wpwq_strip_shortcode( $str );
					//	break;
					case 'shortcodes':
						$str = strip_shortcodes( $str );
						break;
					case 'style':
						$str = wpwq_strip_tags_content( $str, '<style>', true );
						break;
					case 'tags':
						$str = strip_tags( $str );
						break;
					case 'none':
						// silence ...
						break;
					default:
						// silence ...
				}
			}
		}
		
		return $str;
	}

	protected function set_wrapper_open() {
		$this->wrapper_open = '<div class="wpwq-query-wrapper clearfix postcontent" >';
	}
	protected function get_wrapper_open() {
		return $this->wrapper_open;                
	}

	protected function set_wrapper_close() {
		$this->wrapper_close = '</div>';
	}
	protected function get_wrapper_close() {
		return $this->wrapper_close;
	}	
	
	protected function set_wrapper_inner() {
		$this->wrapper_inner = '';
	}	
	protected function get_wrapper_inner() {
		return $this->wrapper_inner;
	}	
	
	public function get_wrapper() {
		$return_str = $this->get_wrapper_open();
			$return_str .= $this->get_wrapper_inner();
		$return_str .= $this->get_wrapper_close();
	
		return $return_str;
	}	

}

?>