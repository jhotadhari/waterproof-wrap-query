<?php
/*
	grunt.concat_in_order.declare('init');
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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
<?php
/*
	grunt.concat_in_order.declare('wpwq_options_metabox');
	grunt.concat_in_order.require('init');
*/

// ??? option to not show this metabox

// ??? widget to generate menu without hidden posts


function wpwq_options_metabox() {
	
	// Start with an underscore to hide fields from custom fields list
	$prefix = 'wpwq_opt_';
	
	// Initiate the metabox
	$cmb = new_cmb2_box( array(
		'id'			=> $prefix . 'single_options',
		'title'			=> __( 'Wpwq Options', 'wpwq' ),
		'object_types'	=> wpwq_get_post_types(),
		'context'		=> 'side',
		'priority'		=> 'default',
		'show_names'	=> true,
		'show_on_cb'	=> 'wpwq_options_metabox_show_on_cb',
	) );

	$cmb->add_field( array(
		'id'   => $prefix . 'meta_desc',
		'type'    => 'title',
		'desc'    => __('Metabox added by <a title="Settings -> Waterproof Wrap Query Plugin" target="_blank" href="' . get_admin_url() . 'options-general.php?page=wpwq_options">Waterproof Wrap Query Plugin</a>','wpwq'),
	) );
	
	$cmb->add_field( array(
		'name' => __('Has Single', 'wpwq'),
		'id'   => $prefix . 'has_single',
		'type'    => 'radio_inline',
		'desc'    => __('Is this posty thing viewable as single? If not, user will be redirected to parent page or home. But this post can still be used for listings.','wpwq'),
		'default' => 'yes',
		'options' => array(
			'yes' => __( 'Yes', 'wpwq' ),
			'no'   => __( 'No', 'wpwq' ),
		),
	) );
	
}
add_action( 'cmb2_admin_init', 'wpwq_options_metabox' );



// show on cb	???
function wpwq_options_metabox_show_on_cb(){
	$prefix = 'wpwq_';
	return count( wpwq_get_option( $prefix . 'options_metabox' ) ) > 0 ? true : false;
}



// load styles
function wpwq_options_metabox_enqueue_style( $post_id, $cmb ) {
	wp_enqueue_style( 'wpwq_options_metabox', plugin_dir_url( __FILE__ ) . 'css/wpwq_options_metabox.css', false );
}
add_action( 'cmb2_after_post_form_' . 'wpwq_opt_' . 'single_options', 'wpwq_options_metabox_enqueue_style', 10, 2 );


?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_single_add_column');
	grunt.concat_in_order.require('init');
*/

add_action( 'admin_init', 'wpwq_single_add_column' );

// add coloumn
function wpwq_single_add_column_column( $columns ){
	return array_merge( $columns, array( 'wpwq_single' => __( 'Has single view?', 'wpwq' ) ) );
}

// add content to desc column
function wpwq_single_add_column_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wpwq_single' :
			
			$has_single = get_post_meta( $post_id, 'wpwq_opt_has_single' , true);
			
			switch ( $has_single ) {
				case 'no':
					$content = '-';
					break;
				default :	// yes
					$content = '<span style="font-size: large;">&#x2713</style>';
			}
			echo $content;
			break;
	}
}

// loop post_types and hook/filter for each
function wpwq_single_add_column(){
	$wpwq_options_metabox = wpwq_get_option( 'wpwq_options_metabox' );
	if ( gettype($wpwq_options_metabox) != 'array') return;
	if ( ! in_array('opt_single_view', $wpwq_options_metabox)) return;
	
	$post_types = wpwq_get_post_types('array_key_val');
	
	if ( count($post_types) > 0 ){
		foreach ( $post_types as $k => $v ) {
			add_filter( 'manage_' . $k . '_posts_columns', 'wpwq_single_add_column_column' );
			add_action( 'manage_' . $k . '_posts_custom_column' ,  'wpwq_single_add_column_column_content', 10, 2 );
		}	
	}
}



?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_single_redirect');
	grunt.concat_in_order.require('init');
*/

function wpwq_single_redirect() {
	global $post;
	if ( !is_singular() || is_front_page()) return;
	
	$has_single = get_post_meta( $post->ID, 'wpwq_opt_has_single', true );
	
	if ( $has_single == 'no' ) {
	
		$parent = get_post_ancestors( $post );
		
		if ( empty( $parent ) ) {
			$link = home_url('/');



		} else {
			$link = get_permalink( get_post( $parent[0] ) );
		}

		
		wp_redirect( $link, 301 );
		exit();
	}
}
add_action( 'template_redirect', 'wpwq_single_redirect' );

?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_options_page');
	grunt.concat_in_order.require('init');
*/
/**
 * CMB2 Plugin Options
 * @version 0.1.0
 */
class Wpwq_options_page {

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'wpwq_options';

	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'wpwq_option_metabox';

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Holds an instance of the object
	 *
	 * @var Wpwq_options_page
	 */
	protected static $instance = null;

	/**
	 * Returns the running object
	 *
	 * @return Wpwq_options_page
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	protected function __construct() {
		// Set our title
		$this->title = __( 'Waterproof Wrap Query', 'wpwq' );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
		add_action( 'cmb2_after_options-page_form_' . $this->metabox_id, array( $this, 'enqueue_style'), 10, 2 );
	}

	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_submenu_page( 'options-general.php', $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	function add_options_page_metabox() {

		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$prefix = 'wpwq_';
		
		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		do_action('wpwq_options_before', $cmb );
		
		$cmb->add_field( array(
			'id'   => $prefix . 'advertising',
			'type' => 'title',
			'render_row_cb' => 'wpwq_render_row_cb_advertising'
		) );
		
		$cmb->add_field( array(
			'id'   => $prefix . 'no_wrappers_installed',
			'type' => 'title',
			'render_row_cb' => 'wpwq_render_row_cb_no_wrappers_installed'
		) );
		
		$cmb->add_field( array(
			'name'             => __('Display shortcode docs', 'wpwq'),
			'desc'             => __('Display the "Waterproof [wrap_query] shortcode docs" Metabox on post edit screens?', 'wpwq'),
			'id'               => $prefix . 'display_docs',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => 'display_all',
			'options'          => array(
				'display_all' => __( 'Display for all', 'wpwq' ),
				'display_admin'   => __( 'Display for admin only', 'wpwq' ),
				'hide_all'     => __( 'Hide for all', 'wpwq' ),
			),
		) );

		$cmb->add_field( array(
			'name' => __('Extra options for single posts', 'wpwq'),
			'desc' => __('Adds a metabox to post edit screens with extra options for single posts.', 'wpwq'),
			'id'   => $prefix . 'options_metabox',
			'type'    => 'multicheck',
			'options' => array(
				'opt_single_view' => __( 'Add option to disable single view', 'wpwq' )
			),
		) );
		
		$cmb->add_field( array(
			'name' => __('Extra fields for term/category edit screens', 'wpwq'),
			'desc' => __('The extra fields values will be used for wrap_query shortcode. If no extra fields wanted, the wordpress core ones will be used.', 'wpwq'),
			'id'   => $prefix . 'term_fields',
			'type'    => 'multicheck',
			'options' => array(
				'desc' => __( 'Description (wysiwyg editor)
					<p class="cmb2-metabox-description">The original Term description textarea will still be there, but the new description wysiwyg will be used as description in wrap_query shortcodes.</p>', 'wpwq' ),
				'desc_short' => __( 'Shortdescription (wysiwyg editor)
					<p class="cmb2-metabox-description">Will be used as description in wrap_query shortcodes.</p>', 'wpwq' ),
				'image_featured' => __( 'Featured Image.', 'wpwq' )
			),
		) ); 
		
		$cmb->add_field( array(
			'name' => __('Display Advertising', 'wpwq'),
			'desc' => __('I don\'t like advertising. Do you want to see the Waterproof-Webdesign ads here and there in backend?', 'wpwq'),
			'id'   => $prefix . 'display_ads',
			'type'    => 'radio_inline',
			'default' => 'yes',
			'options' => array(
				'yes' => __( 'yes', 'wpwq' ),
				'no' => __( 'no', 'wpwq' ),
			),
		) );  
		
		do_action('wpwq_options', $cmb );

	}
	
	public function enqueue_style( $post_id, $cmb ) {
		wp_enqueue_style( 'wpwq_options', plugin_dir_url( __FILE__ ) . 'css/wpwq_options_page.css', false );
		
		wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', false );
		
		wp_enqueue_script( 'jquery-ui-accordion' );

		wp_enqueue_script( 'wpwq_script_admin', plugin_dir_url( __FILE__ ) . 'js/script_admin.min.js', array('jquery'));
	}

	/**
	 * Register settings notices for display
	 *
	 * @since  0.1.0
	 * @param  int   $object_id Option key
	 * @param  array $updated   Array of updated fields
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'wpwq' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  0.1.0
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

/**
 * Helper function to get/return the Wpwq_options_page object
 * @since  0.1.0
 * @return Wpwq_options_page object
 */
function wpwq_admin() {
	return Wpwq_options_page::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function wpwq_get_option( $key = '', $default = null ) {
	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( wpwq_admin()->key, $key, $default );
	}

	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( wpwq_admin()->key, $key, $default );

	$val = $default;

	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}

// Get it started
wpwq_admin();

?>
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
		$queried_object = get_queried_object();
		if ( gettype( $queried_object ) !== 'object' ) return false;
		
		if ( property_exists( $queried_object , 'ID' ) && isset( $queried_object->ID ) ){
			$current_id = apply_filters('wpwq_widget_current_id', $queried_object->ID );
			$is_current = $_obj->ID === $current_id;
		} elseif ( property_exists( $queried_object , 'term_id' ) && isset( $queried_object->term_id ) ){
			$current_id = apply_filters('wpwq_widget_current_id', $queried_object->term_id );
			$is_current = $_obj->term_id === $current_id || wpwq_term_is_child( $_obj->term_id, $parent['query_args']['taxonomy'] );
		} else {
			$is_current = false;
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
<?php
/*
	grunt.concat_in_order.declare('wpwq_wrapper_add_type');
	grunt.concat_in_order.require('init');
*/


/**
* Add type name to the $wpwq_wrapper_types object
*/
function wpwq_wrapper_add_type(){
	global $wpwq_wrapper_types;
	$wpwq_wrapper_types->add_type( array(
		'wpwq_wrapper' => array(
			'desc' => __('Following arguments will work with all wrappers.<br><br>
				Example: You want to strip all tags and shortcodes from the content and limit it to 55 characters? Paste this attribute in the [wrap_query] shortcode:<br>
				wrapper_args=\'{"content_strip":"full","content_limit":"55"}\'','wpwq'),
			'args' => array(
				'count_total' => array(
					'accepts' => 'int',
					'default' => '',
					'desc' => __('set by class','wpwq')
					),
				'content' => array(
					'accepts' => '"none"|"excerpt"|"full"',
					'default' => 'excerpt', 
					'desc' => __('Whats the content?','wpwq')
					),
				'content_strip' => array(
					'accepts' => 'string|"full"',
					'default' => '"shortcodes"',
					'desc' => __('Accepts a string with comma seperated values: , "none", "shortcodes", "style", "tags".<br>
						Or a string with sinlge value "full" to strip all three.<br>
						Notice: Even when unstripped ("none"), the [wrap_query] shortcode will be stripped.','wpwq')
					),
				'content_limit' => array(
					'accepts' => 'false|int',
					'default' => 'false', 
					'desc' => __('Number of characters in content. HTML tags will not be counted and the HTML Structure will be preserved.','wpwq')
					)
				)
			)
		)
	);
}
add_action( 'admin_init', 'wpwq_wrapper_add_type' );
add_action( 'init', 'wpwq_wrapper_add_type' );



?>
<?php
/*
	grunt.concat_in_order.declare('Wpwq_wrapper_types');
	grunt.concat_in_order.require('init');
*/

/**
* Wpwq_wrapper_types class
*
* Class for the wpwq_wrapper_types object
* Object to manage the available display types
*
*/
class Wpwq_wrapper_types {
	protected $types = array();
	
	public function add_type( $arr ){
		foreach ( $arr as $key => $val ){
			$types = $this->types;

			$types[$key] = $val;

		}
		$this->types = $types;
	}
	
	public function get_types( $return_type = null, $wrapper_type = 'all' ){
		if ( $return_type == 'types_string' ){
			// return 'types_string'
			$types_string = '';
			
			// add each type to string
			if ( gettype($this->types) == 'array' ){
				foreach ( $this->types as $key => $val ){
					
					if ( $wrapper_type == 'all' || $wrapper_type == $key){
						$types_string .= $key . ', ';

					}
					
				}
				// remove last seperator
				if ( strlen($types_string) > 0 ){
					$types_string = rtrim ( $types_string, ', ' );
				}
			}
			return $types_string;

		} elseif ( $return_type == 'array_key_val' ){
			// return 'array_key_val'
			$array_key_val = array();
			
			// add each type to array
			if ( gettype($this->types) == 'array' ){
				foreach ( $this->types as $key => $val ){
					if ( $wrapper_type == 'all' || $wrapper_type == $key){
						$array_key_val[$key] = $key;
					}
				}
			}
			return $array_key_val;
			
		} else {
			$full_arr = array();
			
			foreach ( $this->types as $key => $val ){
				if ( $wrapper_type == 'all' || $wrapper_type == $key){
					$full_arr[$key] = $val;
				}
			}
			return $full_arr;
		}
	}
}

/**
* initialize the wpwq_wrapper_types object
*/
function wpwq_init_wrapper_types(){
	global $wpwq_wrapper_types;
	$wpwq_wrapper_types = new Wpwq_wrapper_types();
}
add_action( 'admin_init', 'wpwq_init_wrapper_types' , 2);
add_action( 'init', 'wpwq_init_wrapper_types' , 2);

?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_term_is_child');
	grunt.concat_in_order.require('init');
                                      
*/

function wpwq_term_is_child( $parent_id = false, $object_type = false, $resource_type = 'taxonomy' ) {
	if (! $parent_id ) return false;
	if (! $object_type ) return false;
	
	$term = get_queried_object();
	if (! $term || ! property_exists ( $term , 'term_id' ) ) return false;
	
	$ancestors = isset( $term->term_id ) ? get_ancestors( $term->term_id, $object_type, $resource_type ) : array();
	
	return in_array( $parent_id, $ancestors );
};

?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_get_image_id');
	grunt.concat_in_order.require('init');

*/

function wpwq_get_image_id($attachment_url = '' ) {
	global $wpdb;
	$attachment_id = false;
 
	if ( '' == $attachment_url )
		return;
 
	$upload_dir_paths = wp_upload_dir();
 
	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
		// Remove the upload path base directory from the attachment URL
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
	}
 
	return $attachment_id;
}

?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_get_post_by_slug');
	grunt.concat_in_order.require('init');

*/


function wpwq_get_post_by_slug( $slug, $return = 'post' ) {

	if (!$slug) return false;
	
	$posts = get_posts(array(
            'name' => $slug,
            'posts_per_page' => 1,
            'post_type' => wpwq_get_post_types()
    ));
    
    if( $posts ) {
    	if ( $return == 'post' ) {
    		return $posts[0];
    	} elseif ( $return == 'id' ) {
    		return $posts[0]->ID;
    	}
    } else {
    	return false;
    }

};

?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_get_post_types');
	grunt.concat_in_order.require('init');
*/


// get post types ... _builtin + custom
function wpwq_get_post_types( $return_type = null, $exclude = null){
	if ($return_type == null){
		$post_types = array('post', 'page');

		foreach ( get_post_types( array( '_builtin' => false), 'names' ) as $post_type ) {
		   array_push($post_types, $post_type);
		}

		if ( $exclude == null){
			return $post_types;

		} else {
			if ( gettype( $exclude ) != 'array')
				$exclude = array($exclude);
			
			return array_filter( $post_types, function( $val ) use ( $exclude ){
					return ( in_array( $val, $exclude ) ? false : true );
				} );
		}
	}
	
	if ($return_type == 'array_key_val'){
	
		$post_types = array(
			'post' => __('Post','para_text'),
			'page' => __('Page','para_text')
			);
		
		foreach ( get_post_types( array( '_builtin' => false), 'objects' ) as $post_type ) {

		   $post_types[$post_type->name] =  __($post_type->labels->name,'para_text');
		}

		if ( $exclude == null){
			return $post_types;

		} else {
			if ( gettype( $exclude ) != 'array')
				$exclude = array($exclude);
			
			return array_filter( $post_types, function( $key ) use ( $exclude ){
					return ( in_array( $key, $exclude ) ? false : true );
				}, ARRAY_FILTER_USE_KEY );
		}
	}
	
}

// wrapper function to use as options_cb
function wpwq_get_post_types_arr(){
	 return wpwq_get_post_types( 'array_key_val' );
}


?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_get_taxs');
	grunt.concat_in_order.require('init');

*/

// find taxs and exclude some
function wpwq_get_taxs( $excl_arr = null ){
	$taxs = array();
	
	$taxs_all = get_taxonomies( array() , 'objects'); 
	if  ( $taxs_all ) {
		foreach ( $taxs_all as $tax ) {
			array_push( $taxs, $tax->name );
		}
	}
	
	$excl_arr = $excl_arr != null ? $excl_arr : array(
		'post_tag',
		'nav_menu',
		'link_category',
		'post_format',
	);
	$excl_arr = apply_filters('wpwq_get_taxs_exclude', $excl_arr );
	
	$taxs = array_diff( $taxs, $excl_arr );
	
	return $taxs;
}

?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_render_row_cb');
	grunt.concat_in_order.require('init');
*/

function wpwq_render_row_cb_advertising( $field_args, $field ) {
	
	$prefix_opt = 'wpwq_';
	echo ( wpwq_get_option( $prefix_opt . 'display_ads', 'yes') == 'yes' 
		? '<div class="waterproof-webdesign-logo"><a title="Waterproof-Webdesign" target="_blank" href="http://waterproof-webdesign.info/"><img src="' . plugin_dir_url( __FILE__ ) . '/images/waterproof-webdesign-logo.png" alt="Waterproof-Webdesign Logo"></a></div>' 
		: '' );	

	// return $field;
}

function wpwq_render_row_cb_no_wrappers_installed( $field_args, $field ) {
	global $wpwq_wrapper_types;
	
	echo ( count( $wpwq_wrapper_types->get_types() ) == 1 
		? '<span style="color:#f00;">' . __('No Wrappers installed','wpwq') . ' ' . sprintf( __('Check the <a title="WordPress Plugin Repository" target="_blank" href="%s">WordPress Plugin Repository</a> for Waterproof Wrapper Plugins.', 'wpwq') , 'https://wordpress.org/plugins/') . '</span>'
		: '' );	

	// return $field;
}
?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_slugify');
	grunt.concat_in_order.require('init');

*/

function wpwq_slugify($text){
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);		// replace non letter or digits by -
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);	// transliterate
	$text = preg_replace('~[^-\w]+~', '', $text);		 // remove unwanted characters
	$text = trim($text, '-');		// trim
	$text = preg_replace('~-+~', '-', $text);		// remove duplicate -
	$text = strtolower($text);		// lowercase
	
	if (empty($text)) {
		return 'n-a';
	}
	
	return $text;
}

?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_str_limit_html');
	grunt.concat_in_order.require('init');
*/

/**
 * Limit string without break html tags.
 * Supports UTF8
 * 
 * http://stackoverflow.com/questions/2398725/using-php-substr-and-strip-tags-while-retaining-formatting-and-without-break
 * 
 * @param string $value
 * @param int $limit Default 100
 */
function wpwq_str_limit_html($value, $limit = 100, $append = '')
{
	if ( strlen( $value ) == 0 ) {
		return $value;
	}
    if (mb_strwidth($value, 'UTF-8') <= $limit) {
        return $value;
    }

    // Strip text with HTML tags, sum html len tags too.
    // Is there another way to do it?
    do {
        $len          = mb_strwidth($value, 'UTF-8');
        $len_stripped = mb_strwidth(strip_tags($value), 'UTF-8');
        $len_tags     = $len - $len_stripped;

        $value = mb_strimwidth($value, 0, $limit + $len_tags, '', 'UTF-8');
    } while ($len_stripped > $limit);
    
    $value .= strlen( $append ) > 0 ? $append : '';
     
    // Load as HTML ignoring errors
    $dom = new DOMDocument();
    
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$value, LIBXML_HTML_NODEFDTD);

    // Fix the html errors
    $value = $dom->saveHtml($dom->getElementsByTagName('body')->item(0));

    // Remove body tag
    $value = mb_strimwidth($value, 6, mb_strwidth($value, 'UTF-8') - 13, '', 'UTF-8'); // <body> and </body>
    // Remove empty tags
    $value = preg_replace('/<(\w+)\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\1\s*>/', '', $value);
   
    return $value;
}
?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_strip_shortcode');
	grunt.concat_in_order.require('init');
*/

function wpwq_strip_shortcode( $content, $shortcodes = null) {
	if ( gettype( $shortcodes ) == 'string' ){
		$shortcodes = explode(',', str_replace(' ','',$shortcodes));
	}
	
	$pattern = get_shortcode_regex($shortcodes);
 
	if ( preg_match_all( '/'. $pattern .'/s', $content, $matches )
		&& array_key_exists(0, $matches )) {
	
		foreach( $matches[0] as $match ){
			$content = str_replace( $match, '', $content );

		}
	}
	
	return $content;
}

?>
<?php
/*
	grunt.concat_in_order.declare('Wpwq_wrapper');
	grunt.concat_in_order.require('init');
	

	grunt.concat_in_order.require('wpwq_str_limit_html');
	grunt.concat_in_order.require('wpwq_strip_shortcode');
	grunt.concat_in_order.require('Wpwq_wrapper_types');
	grunt.concat_in_order.require('wpwq_wrapper_add_type');
	grunt.concat_in_order.require('wpwq_get_image_id');
	grunt.concat_in_order.require('wpwq_options_metabox');
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
					
					$obj_link =  in_array('opt_single_view', wpwq_get_option( 'wpwq_options_metabox' )) && get_post_meta( $obj_id , 'wpwq_opt_has_single', true ) == 'no' ? '' : get_permalink( $obj_id ) ;
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
					$obj_id = ( gettype($obj) == 'object' ? $obj->term_id : $obj);
					$obj = get_term($obj_id);
					
					$term_fields = wpwq_get_option( 'wpwq_term_fields' );
					
					$obj_image_url = '';

					$obj_image = '';
					$obj_link = get_term_link( $obj );
					$obj_slug = $obj->slug;
					$obj_str_title = __($obj->name);
					
					// obj_str_inner
					$content_type = ( array_key_exists('content', $this->args ) 
						? $this->args['content']
						: 'excerpt' );
					
					$content_full = in_array( 'desc', $term_fields )
						&& ! empty(get_term_meta($obj_id, 'wpwq_desc'))
						? apply_filters( 'the_content', get_term_meta($obj_id, 'wpwq_desc')[0])
						: apply_filters( 'the_content', term_description($obj->term_id));
					
					$content_excerpt = in_array( 'desc_short', $term_fields )
						&& ! empty(get_term_meta($obj_id, 'wpwq_desc_short'))
						? apply_filters( 'the_content', get_term_meta($obj_id, 'wpwq_desc_short')[0])
						: '';
						
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
					
					$term_image = in_array( 'image_featured', $term_fields )
						&& ! empty(get_term_meta($obj->term_id, 'wpwq_image_featured')) 
						? get_term_meta($obj->term_id, 'wpwq_image_featured')[0]
						: '';
					if ( strlen($term_image) > 0 ) {
						$obj_image_url = wp_get_attachment_image_src( wpwq_get_image_id($term_image), 'thumbnail')[0];
						$obj_image = '<img class="wp-post-image" src="' . $obj_image_url .'">';
					}
					
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
						$obj_image = '<img class="wp-post-image" src="' . $obj_image_url .'">';
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
<?php
/*
	grunt.concat_in_order.declare('wpwq_is_child');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('wpwq_get_post_by_slug');
                                      
*/

function wpwq_is_child( $parent_id, $object_type = 'post', $resource_type = 'post_type') {
	if (! $parent_id ) return false;
	
	global $post;
	if (! $post ) return false;
	
	if (! is_numeric($parent_id) )
		$parent_id = wpwq_get_post_by_slug( $parent_id, 'id' )->ID;
	
	$ancestors = get_ancestors( $post->ID, $object_type, $resource_type );

	
	return in_array( $parent_id, $ancestors );
};


?>
<?php
/*
	grunt.concat_in_order.declare('Wpwq_wrapper_single');
	grunt.concat_in_order.require('Wpwq_wrapper');
*/



class Wpwq_wrapper_single {

	protected $name;
	protected $args;
	protected $args_single;
	protected $inner;                            
		
	function __construct( $name = null, $query_single_obj = null , $args = null, $args_single = null , $single_count = null) {
		$this->set_name( $name );
		$this->set_args( $args, $args_single, $single_count );
	}
	protected function set_name( $name ){
		$this->name = $name;
	}
	public function get_name(){
		return $this->name;
	}		
	protected function set_inner( $query_single_obj ){
		$this->inner = '';
	}
		
	public function get_inner(){
		return $this->inner;
	}
	protected function set_args( $args, $args_single, $single_count = null ){
		$this->args = ( null !== $args ? $args : array() );
		$this->args_single = ( null !== $args_single ? $args_single : array() );
		if ( $single_count !== null ){
			$this->args_single['single_count'] = $single_count;
		}
	}
	public function get_args(){
		return $this->inner;
	}
}


?>
<?php
/*
	grunt.concat_in_order.declare('wpwq_wrapper_init');
	grunt.concat_in_order.require('init');
	
	grunt.concat_in_order.require('Wpwq_wrapper_types');
	grunt.concat_in_order.require('Wpwq_wrapper');
	grunt.concat_in_order.require('Wpwq_wrapper_single');
*/

function wpwq_get_wrapper( $type = null, $query_obj = 'post', $objs = null, $wrapper_args = null) {
	
	if ($type === null || ! array_key_exists( $type, $GLOBALS['wpwq_wrapper_types']->get_types())){
		return '';	
	}
	
	$wrapper_type_name = 'Wpwq_wrapper' . '_' . $type;
	
	$wpwq_wrapper = new $wrapper_type_name( $query_obj, $objs, $wrapper_args );
	return $wpwq_wrapper->get_wrapper();
}



?>
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