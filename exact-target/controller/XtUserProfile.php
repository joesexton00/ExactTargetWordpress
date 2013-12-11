<?php
/**
 * Exact Target User Profile Class
 *
 * @author     Joe Sexton <joe.sexton@bigideas.com>
 * @package    WordPress
 * @subpackage exact-target
 */
class XtUserProfile extends WpBaseController {

	/**
	 * constructor
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	function __construct()
	{
		parent::__construct();

		$this->subscriberKeyEnabled = (bool)get_option( 'xt_subscriber_key_enabled' );

		// show admin profile
		add_action( 'show_user_profile', array( $this, 'renderProfileFields' ) );
		add_action( 'edit_user_profile', array( $this, 'renderProfileFields' ) );

		// save admin profile
		add_action( 'user_profile_update_errors', array( $this, 'validateUserProfileFields' ), 10, 3 );
		add_action( 'edit_user_profile_update', array( $this, 'saveUserProfileFields' ) );
		add_action( 'personal_options_update', array( $this, 'saveUserProfileFields' ) );
	}

	/**
	 * render user profile fields
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   WP_User $user
	 */
	public function renderProfileFields( WP_User $user ) {

		if ( $this->subscriberKeyEnabled ) {

			$this->render( 'extra_profile_fields', array( 'user' => $user ) );
		}
	}

	/**
	 * validate user profile fields
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   WP_Error $errors
	 * @param   boolean $update
	 * @param   object $user raw user object not a WP_User
	 */
	public function validateUserProfileFields( WP_Error &$errors, $update, &$user )
	{
		if ( ! $this->subscriberKeyEnabled ) {

			return $errors;
		}

		// validate input fields
		if ( strlen( $_POST['xt-subscriber-key'] ) > 255 && !empty( $_POST['xt-subscriber-key'] ) )
			$errors->add( 'xt-subscriber-key', "<strong>ERROR</strong>: The maximum subscriber key length is 255 characters." );

		return $errors;
	}

	/**
	 * save user profile fields
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   int $id
	 */
	public function saveUserProfileFields( $id ) {

		if ( !current_user_can( 'edit_user', $id ) ){

			return false;
		}

		if ( isset( $_POST['xt-subscriber-key'] ) )
			update_user_meta( $id, 'xt-subscriber-key', $_POST['xt-subscriber-key'] );
	}
}