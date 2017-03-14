<?php
/*
	grunt.concat_in_order.declare('Wpwq_widget_menu');
	grunt.concat_in_order.require('init');
*/


// https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/widgets/widget-example.php

/**
 * @todo Properly hook in JS events, etc. Fields which require JS are not working.
 * @todo Fix css styling. Probably needs a sep. CSS file enqueued for widgets.
 */


class Wpwq_widget_menu extends WP_Widget {

	/**
	 * Unique identifier for this widget.
	 *
	 * Will also serve as the widget class.
	 *
	 * @var string
	 */
	protected $widget_slug = 'wpwq-widget-menu';

	/**
	 * This widget's CMB2 instance.
	 *
	 * @var CMB2
	 */
	protected $cmb2 = null;

	/**
	 * Array of default values for widget settings.
	 *
	 * @var array
	 */
	protected static $defaults = array();

	/**
	 * Store the instance properties as property
	 *
	 * @var array
	 */
	protected $_instance = array();

	/**
	 * Array of CMB2 fields args.
	 *
	 * @var array
	 */
	protected $cmb2_fields = array();
	
	
	protected static $atts = array();

	/**
	 * Contruct widget.
	 */
	public function __construct() {

		parent::__construct(
			$this->widget_slug,
			esc_html__( 'Waterproof Widget Menu', 'wpwq' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => esc_html__( 'A menu based on "wrap_query" shortcode.', 'wpwq' ),
			)
		);

		self::$defaults = array(
			'title' => '',
			'post_type' => '',
			'depth' => '',
			'steps_hide' => '0',
			'add_q_args' => '',
		);

		$this->cmb2_fields = array(
			array(
				'name'   => __('Title','wpwq'),
				'id_key' => 'title',
				'id'     => 'title',
				'type'   => 'text',
				'attributes' => array(
					'class' => 'cmb2-qtranslate',
					'data-cmb2-qtranslate' => true
				)
			),

			array(
				'name'   => __('Post Type','wpwq'),
				'id_key' => 'post_type',
				'id'     => 'post_type',
				'type'   => 'select',
				'options_cb' => 'wpwq_get_post_types_arr',
			),
			
			
			array(
				'name'   => __('Depth','wpwq'),
				'id_key' => 'depth',
				'id'     => 'depth',
				'type'   => 'text',
				'desc' => __('How many steps this menu should walk? [integer]','wpwq'),
				'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*',
				)
			),
			
			array(
				'name'   => __('Hide Steps','wpwq'),
				'id_key' => 'steps_hide',
				'id'     => 'steps_hide',
				'type'   => 'text',
				'desc' => __('Hide the first steps? How many? [integer]','wpwq'),
				'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*',
				)
			),
			
			array(
				'name'   => __('Additional query arguments','wpwq'),
				'desc' => __('Accepts an JSON formated array to set/change args for get_posts function.<br>
					Example to include by id: {"include":"17,18"}<br>
					Example to include only top posts: {"post_parent":"0"}','wpwq'),
				'id_key' => 'add_q_args',
				'id'     => 'add_q_args',
				'type'   => 'textarea_small'
			),
			
		);

		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	/**
	 * Delete this widget's cache.
	 *
	 * Note: Could also delete any transients
	 * delete_transient( 'some-transient-generated-by-this-widget' );
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->id, 'widget' );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param  array  $args      The widget arguments set up when a sidebar is registered.
	 * @param  array  $instance  The widget settings as set by user.
	 */
	public function widget( $args, $instance ) {

		echo self::get_widget( array(
			'args'     => $args,
			'instance' => $instance,
			//'cache_id' => $this->id, // whatever the widget id is
		) );
		
		add_action( 'wp_footer', array( $this, 'styles_scripts_frontend' ), 1 );

	}
	
	
	public function styles_scripts_frontend() {
		wp_enqueue_script( 'wpwq_widget', plugin_dir_url( __FILE__ ) . 'js/wpwq_widget.min.js', array('jquery') , false , true);
	}	
	

	/**
	 * Return the widget output
	 *
	 * @param  array  $atts Array of widget attributes/args
	 * @return string       Widget output
	 */
	public static function get_widget( $atts ) {
		
		$opt_single_view = in_array( 'opt_single_view', wpwq_get_option( 'wpwq_options_metabox' ));
		
		$instance = $atts['instance'];
		self::$atts = $atts['args'];
		$depth = $instance['depth'] + 1;
		$steps_hide = $instance['steps_hide'];
		
		// get option post_type
		$post_type = array_key_exists( 'post_type', $instance) && $instance['post_type'] ? $instance['post_type'] : false;
		
		// parse add_q_args
		if ( array_key_exists( 'add_q_args', $instance)
			&& $instance['add_q_args']
			&& strpos( $instance['add_q_args'], '{'  ) === 0 )
		{
			$json = strip_tags(str_replace( ' ', '', $instance['add_q_args'] ));
			$add_q_args = ( null !== json_decode( $json, true ) ? json_decode( $json, true ) : array() );
		}
		
		// set query_args
		$q_args = array(
			'post_type' => $post_type, 
			'posts_per_page' => -1,
			'fields' => 'ids',
		);
		
		// exchange query_args with parsed add_q_args
		foreach ( $add_q_args as $k => $v ) {
			$q_args[$k] = $v;
		}
		
		// if option is set to hide singles, add meta_query
		$q_args = self::query_args_hide_singles( $q_args, $opt_single_view );
		
		
		// get posts
		$_post_ids = get_posts( $q_args );
		if (! $_post_ids ) return;

		
		// build list
		$l = '';    
		$level = 1;

		if ( $level > $depth ) return;
		
		
		foreach ( $_post_ids as $_post_id ){
			$_post = get_post($_post_id);
			
			$current_id = apply_filters('wpwq_widget_current_id', get_the_ID() );
			$is_current = ($_post->ID === $current_id);

			$args = array(
				'_post_id' => $_post_id,
				'opt_single_view' => $opt_single_view,
				'level_from' => $level,
				'depth' => $depth,
				'steps_hide' => $steps_hide,
				'is_current' => $is_current,
			);
			
			if ( $steps_hide >= $level ){
				$l .= self::walk_subs( $args );
				
			} else {
				
				$classes = array();
				$classes[] = 'post-' . $_post->ID;
				$classes = array_diff($classes, array(''));
				$classes_attr = count($classes) > 0 ? ' class="' . implode( ' ', $classes ). '"' : '';
				
				$l .= sprintf( '<li %s><a href="%s"%s>%s</a>%s</li>',
					$classes_attr,
					get_permalink($_post->ID),
					( $_post->ID === $current_id ) ? ' class="current"' : '',
					apply_filters('the_title', $_post->post_title),
					self::walk_subs( $args )
				);
				
			}
		}
		
		// hide this step (walk through), or wrap in ul
		if ( $steps_hide >= $level ){
			$list = $l;
		} else {
			$classes = array();
			$classes[] = 'level-' . $level;
			$classes = array_diff($classes, array(''));
			$classes_attr = count($classes) > 0 ? ' class="' . implode( ' ', $classes ). '"' : '';
			$list = self::str_wrap( $l, '<ul ' . $classes_attr . '>', '</ul>');
		}
				
		if ( strlen( $list ) == 0 ) return;
		
		
		// start widget
		$widget = '';

		// Before widget hook
		$widget .= array_key_exists( 'before_widget', self::$atts) ? self::$atts['before_widget'] : '';

		// Title
		if ( array_key_exists( 'title', $instance) && $instance['title'] ){
			$widget .= array_key_exists( 'before_title', self::$atts) ? self::$atts['before_title'] : '';
			$widget .= esc_html( $instance['title'] );
			$widget .= array_key_exists( 'after_title', self::$atts) ? self::$atts['after_title'] : '';
		}

		// add list
		$widget .= $list;
		
		// After widget hook
		$widget .= array_key_exists( 'after_widget', self::$atts) ? self::$atts['after_widget'] : '';
		
		return $widget;
	}
	
	
	protected static function query_args_hide_singles( $query_args, $opt_single_view = false ){
		if ( $opt_single_view ) {
			$query_args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => 'wpwq_opt_has_single',
					'value'   => 'no',
					'compare' => '!=',
				),
				array(
					'key'     => 'wpwq_opt_has_single',
					'compare' => 'NOT EXISTS',
					'value'   => '1',     // <-- just some value as a pre 3.9 bugfix (Codex)
				)
			);
		}
	
		return $query_args;
	}
	
	protected static function walk_subs( $args, $parent = false ){
		
		$args = wp_parse_args( $args, array(
			'is_current' => false,
		));

		$level = $args['level_from'] + 1;
		if ( $level > $args['depth'] ) return;
		
		// get uniques
		if ( ! $parent || ( $parent && $parent['query_obj'] == 'post' ) ) {
			$wpwq_uq = apply_filters('wpwq_widget_wpwq_uq', get_post_meta( $args['_post_id'], 'wpwq_uq', true), $args, $parent );
		} elseif ( $parent && $parent['query_obj'] == 'term' ) {
			$wpwq_uq = array( $parent );
			$wpwq_uq[0]['query_args']['parent'] = $args['_post_id'];
			$wpwq_uq = apply_filters('wpwq_widget_wpwq_uq', $wpwq_uq, $args, $parent );
		}
		
		
		$return = '';
		$r = '';
		if (! empty( $wpwq_uq ) ){
			

			foreach( $wpwq_uq as $uq ){

				if ( !isset($uq['has_link']) || $uq['has_link'] != 'true' ) break;
				
				switch ( $uq['query_obj'] ) {
					case 'post':
						// if option is set to hide singles, add meta_query
						$uq['query_args'] = self::query_args_hide_singles( $uq['query_args'], $args['opt_single_view'] );
						
						$uq_ids = get_posts( $uq['query_args'] );
		
						foreach( $uq_ids as $uq_id ){
							$_post = get_post( $uq_id );
							
							$is_current = self::is_current( $_post, $parent );
							
							
							$_args = wp_parse_args( array(
								'_post_id' => $_post->ID,
								'level_from' => $level,
								'is_current' => $is_current,
							), $args );
							
							
							// hide this step (walk through), or wrap in ul
							if ( $args['steps_hide'] >= $level ){
								$r .= self::walk_subs( $_args, $uq );
							} else {
								$classes = array();
								$classes[] = 'post-' . $_post->ID;
								$classes[] = 'unique-' . $uq['unique'];
								$classes = array_diff($classes, array(''));
								$classes_attr = count($classes) > 0 ? ' class="' . implode( ' ', $classes ). '"' : '';
								
								$r .= sprintf( '<li %s ><a href="%s"%s>%s</a>%s</li>',
									$classes_attr,
									get_permalink($_post->ID),
									$is_current ? ' class="current"' : '',
									apply_filters('the_title', $_post->post_title),
									self::walk_subs( $_args, $uq )
								);
							}
							
							
						}
						
					break;
					case 'term':
						$uq_ids = get_terms( $uq['query_args'] );
		
						foreach( $uq_ids as $uq_id ){
							
							$_term = get_term( $uq_id );
							
							$is_current = self::is_current( $_term, $parent );
							
							$_args = wp_parse_args( array(
								'_post_id' => $_term->term_id,
								'level_from' => $level,
								'is_current' => $is_current,
							), $args );
							
							// hide this step (walk through), or wrap in ul
							if ( $args['steps_hide'] >= $level ){
								$r .= self::walk_subs( $_args, $uq );
							} else {
								$classes = array();
								$classes[] = 'post-' . $_term->term_id;
								$classes[] = 'unique-' . $uq['unique'];
								$classes = array_diff($classes, array(''));
								$classes_attr = count($classes) > 0 ? ' class="' . implode( ' ', $classes ). '"' : '';
								
								$r .= sprintf( '<li %s ><a href="%s"%s>%s</a>%s</li>',
									$classes_attr,
									get_term_link($_term->term_id),
									$is_current ? ' class="current"' : '',
									apply_filters('the_title', $_term->name),
									self::walk_subs( $_args, $uq )
								);
							}
							
						}
						break;
					default:
						// silence ...
				}
				
			}
		}	
		
		$classes = array();
		$classes[] = 'level-' . $level;
		$classes[] = ( $args['is_current']  ? 'current' : '' );
		$classes = array_diff($classes, array(''));
		$classes_attr = count($classes) > 0 ? ' class="' . implode( ' ', $classes ). '"' : '';
		
		$return .= strlen($r) > 0 ? self::str_wrap( $r, '<ul ' . $classes_attr . '>', '</ul>') : '';		
		
		return $return;
	}
	
	protected static function str_wrap( $str, $open = null, $close = null ){
		return strlen( $str ) > 0 ? $open . $str . $close : '';
	}
	
	protected static function is_current( $_obj, $parent ){
		if ( gettype( $_obj ) !== 'object' ) return false;
		
		$is_current = false;
		
		if ( property_exists( get_queried_object() , 'ID' ) && isset( get_queried_object()->ID ) ){
			$current_id = apply_filters('wpwq_widget_current_id', get_queried_object()->ID );
			$is_current = $_obj->ID === $current_id;
		} elseif ( property_exists( get_queried_object() , 'term_id' ) && isset( get_queried_object()->term_id ) ){
			$current_id = apply_filters('wpwq_widget_current_id', get_queried_object()->term_id );
			$is_current = $_obj->term_id === $current_id || wpwq_term_is_child( $_obj->term_id, $parent['query_args']['taxonomy'] );
		}
		
		return $is_current;
	}

	/**
	 * Update form values as they are saved.
	 *
	 * @param  array  $new_instance  New settings for this instance as input by the user.
	 * @param  array  $old_instance  Old settings for this instance.
	 * @return array  Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$this->flush_widget_cache();
		$sanitized = $this->cmb2( true )->get_sanitized_values( $new_instance );
		return $sanitized;
	}
	/**
	 * Back-end widget form with defaults.
	 *
	 * @param  array  $instance  Current settings.
	 */
	public function form( $instance ) {
		// If there are no settings, set up defaults
		$this->_instance = wp_parse_args( (array) $instance, self::$defaults );

		$cmb2 = $this->cmb2();

		$cmb2->object_id( $this->option_name );
		CMB2_hookup::enqueue_cmb_css();
		CMB2_hookup::enqueue_cmb_js();
		$cmb2->show_form();
	}

	/**
	 * Creates a new instance of CMB2 and adds some fields
	 * @since  0.1.0
	 * @return CMB2
	 */
	public function cmb2( $saving = false ) {

		// Create a new box in the class
		$cmb2 = new CMB2( array(
			'id'      => $this->option_name .'_box', // Option name is taken from the WP_Widget class.
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page', // Tells CMB2 to handle this as an option
				'value' => array( $this->option_name )
			),
		), $this->option_name );

		foreach ( $this->cmb2_fields as $field ) {

			if ( ! $saving ) {
				$field['id'] = $this->get_field_name( $field['id'] );
			}

			$field['default_cb'] = array( $this, 'default_cb' );

			$cmb2->add_field( $field );
		}

		return $cmb2;
	}

	/**
	 * Sets the field default, or the field value.
	 *
	 * @param  array      $field_args CMB2 field args array
	 * @param  CMB2_Field $field CMB2 Field object.
	 *
	 * @return mixed      Field value.
	 */
	public function default_cb( $field_args, $field ) {
		return isset( $this->_instance[ $field->args( 'id_key' ) ] )
			? $this->_instance[ $field->args( 'id_key' ) ]
			: null;
	}

}

/**
 * Register this widget with WordPress.
 */
function wpwq_widget_menu_register() {
	register_widget( 'Wpwq_widget_menu' );
}
add_action( 'widgets_init', 'wpwq_widget_menu_register' );





?>