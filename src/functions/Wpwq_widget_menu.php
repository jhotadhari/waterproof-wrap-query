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
		$args = $atts['args'];
		
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
		foreach ( $_post_ids as $_post_id ){
			$_post = get_post($_post_id);
			$l .= sprintf( '<li><a href="%s"%s>%s</a>%s</li>',
				get_permalink($_post->ID),
				( $_post->ID === get_the_ID() ) ? ' class="current"' : '',
				apply_filters('the_title', $_post->post_title),
				self::walk_subs( $_post_id, $opt_single_view )
			);
		}
		$list = self::str_wrap( $l, '<ul>', '</ul>');
		if ( strlen( $list ) == 0 ) return;
				
		
		// start widget
		$widget = '';

		// Before widget hook
		$widget .= array_key_exists( 'before_widget', $args) ? $args['before_widget'] : '';

		// Title
		if ( array_key_exists( 'title', $instance) && $instance['title'] ){
			$widget .= array_key_exists( 'before_title', $args) ? $args['before_title'] : '';
			$widget .= esc_html( $instance['title'] );
			$widget .= array_key_exists( 'after_title', $args) ? $args['after_title'] : '';
		}

		// add list
		$widget .= $list;
		
		// After widget hook
		$widget .= array_key_exists( 'after_widget', $args) ? $args['after_widget'] : '';
		
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
	
	protected static function walk_subs( $_post_id, $opt_single_view ){

		// get uniques
		$wpwq_uq = get_post_meta( $_post_id, 'wpwq_uq', true);
		

		$r = '';
		if (! empty( $wpwq_uq ) ){
			foreach( $wpwq_uq as $uq ){
			
				if ( $uq['has_link'] != 'true' ) break;
				
				
				switch ( $uq['query_obj'] ) {
					case 'post':
						// if option is set to hide singles, add meta_query
						$uq['query_args'] = self::query_args_hide_singles( $uq['query_args'], $opt_single_view );
						
						$uq_ids = get_posts( $uq['query_args'] );
		
						foreach( $uq_ids as $uq_id ){
					
							$uq = get_post($uq_id);
							
							$r .= sprintf( '<li><a href="%s"%s>%s</a>%s</li>',
								get_permalink($uq->ID),
								( $uq->ID === get_the_ID() ) ? ' class="current"' : '',
								apply_filters('the_title', $uq->post_title),
								self::walk_subs( $uq_id, $opt_single_view )
							);
						}
					break;
					case 'term':
						$uq_ids = get_terms( $uq['query_args'] );
		
						foreach( $uq_ids as $uq_id ){
					
							$uq = get_term($uq_id);
							
							$r .= sprintf( '<li><a href="%s">%s</a></li>',
								get_term_link($uq),
								apply_filters('the_title', $uq->name)
							);
						}
						break;
					default:
						// silence ...
				}
			}
		}
		
		return self::str_wrap( $r, '<ul>', '</ul>');
	}
	
	protected static function str_wrap( $str, $open = null, $close = null ){
		return strlen( $str ) > 0 ? $open . $str . $close : '';
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