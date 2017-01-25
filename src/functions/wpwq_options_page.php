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