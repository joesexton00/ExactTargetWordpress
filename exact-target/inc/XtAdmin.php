<?php
/**
 * Exact Target Admin Class
 *
 * @author     Joe Sexton <joe.sexton@bigideas.com>
 * @package    WordPress
 * @subpackage exact-target
 */
class XtAdmin {

	/**
	 * @var string
	 */
	protected $pluginDir = '';

	/**
	 * constructor
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   string $pluginDir
	 */
	function __construct( $pluginDir )
	{
		$this->pluginDir = $pluginDir;

		register_activation_hook( __FILE__, array( $this, 'on_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'on_deactivation' ) );

		add_action( 'admin_menu', array( $this, 'add_admin_menu_page' ) );
		add_action( 'admin_init', array( $this, 'admin_settings_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * on activation
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function on_activation() {

		// add options
		add_option( 'xt_username' );
		add_option( 'xt_password' );
		add_option( 'xt_mailing_lists' );
		add_option( 'xt_subscriber_key_enabled' );
		add_option( 'xt_push_user_profile_updates' );

		add_option( 'xt_attribute_id' );
		add_option( 'xt_attribute_email' );
		add_option( 'xt_attribute_username' );
		add_option( 'xt_attribute_first_name' );
		add_option( 'xt_attribute_last_name' );
		add_option( 'xt_attribute_display_name' );
		add_option( 'xt_attribute_nice_name' );
		add_option( 'xt_attribute_nickname' );
		add_option( 'xt_attribute_description' );
		add_option( 'xt_attribute_url' );
		add_option( 'xt_attribute_registered' );
	}

	/**
	 * on deactivation
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function on_deactivation() {

		// remove options ?
		// delete_option( 'xt_username' );
		// delete_option( 'xt_password' );
		// delete_option( 'xt_mailing_lists' );
		// delete_option( 'xt_subscriber_key_enabled' );
		// delete_option( 'xt_push_user_profile_updates' );
		//
		// delete_option( 'xt_attribute_id' );
		// delete_option( 'xt_attribute_email' );
		// delete_option( 'xt_attribute_username' );
		// delete_option( 'xt_attribute_first_name' );
		// delete_option( 'xt_attribute_last_name' );
		// delete_option( 'xt_attribute_display_name' );
		// delete_option( 'xt_attribute_nice_name' );
		// delete_option( 'xt_attribute_nickname' );
		// delete_option( 'xt_attribute_description' );
		// delete_option( 'xt_attribute_url' );
		// delete_option( 'xt_attribute_registered' );
	}

	/**
	 * enqueue scripts
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	function enqueue_scripts() {

		wp_register_script( 'xt-admin-script', plugin_dir_url( __FILE__ ) . '../assets/js/admin.js', array( 'jquery' ), 'alpha', true );
		wp_enqueue_script( 'xt-admin-script' );
	}

	/**
	 * add admin menu page
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function add_admin_menu_page(){

	    add_options_page( 'Exact Target User Integration', 'Exact Target', 'manage_options', 'xt-admin', array( $this, 'render_admin_page' ) );
	}

	/**
	 * render admin page
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function render_admin_page() {

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		include_once( $this->pluginDir . 'views/admin.php' );
	}

	/**
	 * admin settings init
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function admin_settings_init() {

		// register options
		register_setting( 'exact_target', 'xt_username', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_password', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_mailing_lists', array( $this, 'validate_mailing_list_fields' ) );

		register_setting( 'exact_target', 'xt_attribute_id', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_username', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_email', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_first_name', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_last_name', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_display_name', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_nice_name', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_nickname', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_description', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_url', array( $this, 'sanitize_input_field' ) );
		register_setting( 'exact_target', 'xt_attribute_registered', array( $this, 'sanitize_input_field' ) );

		register_setting( 'exact_target', 'xt_subscriber_key_enabled' );
		register_setting( 'exact_target', 'xt_push_user_profile_updates' );

		// add form sections
		add_settings_section( 'xt_credentials', "Credentials", array( $this, 'render_credentials_section_text' ),  "xt-admin" );
		add_settings_section( 'xt_mailing_lists', "Mailing Lists", array( $this, 'render_mailing_list_section_text' ),  "xt-admin" );
		add_settings_section( 'xt_subscriber_attributes', "Subscriber Attributes", array( $this, 'render_attributes_section_text' ),  "xt-admin" );
		add_settings_section( 'xt_misc', "Miscellaneous", null,  "xt-admin" );

		// add form fields
		add_settings_field( 'xt_username', 'Username', array( $this, 'render_text_input' ),  "xt-admin", 'xt_credentials', array(
			'slug'     => 'xt_username',
			'required' => true
		));
		add_settings_field( 'xt_password', 'Password', array( $this, 'render_text_input' ),  "xt-admin", 'xt_credentials', array(
			'slug'     => 'xt_password',
			'required' => true
		));
		add_settings_field( 'xt_mailing_lists', 'Mailing List Ids', array( $this, 'render_mailing_list_inputs' ),  "xt-admin", 'xt_mailing_lists', array(
			'slug' => 'xt_mailing_lists',
		));

		add_settings_field( 'xt_attribute_id', 'User ID', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_id',
		));
		add_settings_field( 'xt_attribute_username', 'Username', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_username',
		));
		add_settings_field( 'xt_attribute_email', 'Email', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_email',
		));
		add_settings_field( 'xt_attribute_first_name', 'First Name', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_first_name',
		));
		add_settings_field( 'xt_attribute_last_name', 'Last Name', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_last_name',
		));
		add_settings_field( 'xt_attribute_display_name', 'Display Name', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_display_name',
		));
		add_settings_field( 'xt_attribute_nice_name', 'Nice Name', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_nice_name',
		));
		add_settings_field( 'xt_attribute_nickname', 'Nickname', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_nickname',
		));
		add_settings_field( 'xt_attribute_description', 'Description', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_description',
		));
		add_settings_field( 'xt_attribute_url', 'URL', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_url',
		));
		add_settings_field( 'xt_attribute_registered', 'Registered', array( $this, 'render_text_input' ),  "xt-admin", 'xt_subscriber_attributes', array(
			'slug'     => 'xt_attribute_registered',
		));
		add_settings_field( 'xt_subscriber_key_enabled', 'Use Subscriber Key', array( $this, 'render_checkbox_input' ),  "xt-admin", 'xt_misc', array(
			'slug'     => 'xt_subscriber_key_enabled',
			'message'  => 'does your implementation of Exact Target use the subscriber key feature?'
		));
		add_settings_field( 'xt_push_user_profile_updates', 'Push Profile Updates to XT', array( $this, 'render_checkbox_input' ),  "xt-admin", 'xt_misc', array(
			'slug'     => 'xt_push_user_profile_updates',
			'message'  => 'if enabled, all user profile updates will be sent to Exact Target, if not enabled, user data will only be sent on user registration',
		));
	}

	/**
	 * render credentials section text
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function render_credentials_section_text() {
		echo "Enter the credentials for an Exat Target administrator with Web API privileges";
	}

	/**
	 * render mailing list section text
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function render_mailing_list_section_text() {
		echo "Users will be automatically subscribed to these mailing lists when they are registered with Wordpress.";
	}

	/**
	 * render attributes section text
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function render_attributes_section_text() {
		echo "Enter the name of the Exact Target subscriber attribute that corresponds with each user property.  If the field is left blank the property will not be sent to Exact Target.";
	}

	/**
	 * render text input
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   array $args
	 */
	public function render_text_input( $args ) {
		$slug = $args['slug'];

		if ( isset( $args['required'] ) && $args['required'] === true ) {

			$required = 'required="required"';
		} else {
			$required = '';
		}
		$fieldVal = get_option($slug);

		echo '<input type="text" id="'.$slug.'" name="'.$slug.'" value="'.$fieldVal.'" '.$required.' >';
	}

	/**
	 * render mailing list inputs
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   array $args
	 */
	public function render_mailing_list_inputs( $args ) {
		$slug   = $args['slug'];
		$fields = get_option($slug);

		echo '<ul class="xt_mailing_lists">';
		foreach ( $fields as $key => $val ) {

			echo '<li><input type="text" id="'.$slug.'['.$key.']" name="'.$slug.'['.$key.']" value="'.$fields[$key].'" ><a href="#" class="xt_remove_mailing_list">(remove)</a></li>';
		}

		// start off with an empty input element
		if ( empty( $fields ) ) {

			echo '<li><input type="text" id="'.$slug.'[0]" name="'.$slug.'[0]" value="" ><a href="#" class="xt_remove_mailing_list">(remove)</a></li>';
		}


		echo '<li><a href="#" class="xt_add_mailing_list">Add Mailing List</a></li></ul>';
	}

	/**
	 * render checkbox input
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   array $args
	 */
	function render_checkbox_input( $args ) {
		$slug = $args['slug'];

 		echo '<input name="'.$slug.'" id="'.$slug.'" type="checkbox" value="1" ' . checked( 1, get_option( $slug ), false ) . ' >';

 		if ( !empty( $args['message'] ) ) {

 			echo ' ' . $args['message'];
 		}
 	}

	/**
	 * validate input field
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   string $field
	 * @return  string
	 */
	public function sanitize_input_field( $field ) {

		$field = filter_var( $field, FILTER_SANITIZE_STRING );

		return $field;
	}

	/**
	 * validate mailing list fields
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   array $fields
	 * @return  array
	 */
	public function validate_mailing_list_fields( $fields ) {

		// remove empty mailing list fields
		foreach ( $fields as $key => $val ) {

			if ( !is_numeric( $val ) ) {

				add_settings_error( 'xt_mailing_lists', 'xt_error', 'Mailing list ids must be numeric', $type = 'error' );
				unset( $fields[$key] );
			}

			if ( empty( $val ) ) {
				unset( $fields[$key] );
			}
		}

		$fields = array_values( $fields );

		return $fields;
	}
}