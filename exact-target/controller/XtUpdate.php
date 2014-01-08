<?php
/**
 * Exact Target Update Class
 *
 * @author     Joe Sexton <joe.sexton@bigideas.com>
 * @package    WordPress
 * @subpackage exact-target
 */
class XtUpdate extends WpBaseController {

	/**
	 * @var XtSubscriber
	 */
	protected $subscriber;

	/**
	 * @var boolean
	 */
	protected $subscriberKeyEnabled = false;

	/**
	 * @var boolean
	 */
	protected $pushProfileUpdates = false;

	/**
	 * @var array
	 */
	protected $mailingLists = array();

	/**
	 * @var array
	 */
	protected $subscriberAttributes = array();

	/**
	 * init
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	function _init() {

		$xtApi    = $this->modelPath . 'exact_target_api/exacttarget_soap_client.php';
		$username = get_option( 'xt_username' );
		$password = get_option( 'xt_password' );
		$this->subscriber = new XtSubscriber( $xtApi, $username, $password );

		// misc
		$this->mailingLists         = get_option( 'xt_mailing_lists' );
		$this->subscriberKeyEnabled = (bool)get_option( 'xt_subscriber_key_enabled' );
		$this->pushProfileUpdates   = (bool)get_option( 'xt_push_user_profile_updates' );

		// update user
		add_action( 'user_register', array( $this, 'registerUser' ), 10, 1 );
		add_action( 'profile_update', array( $this, 'updateProfile' ), 10, 2 );
	}

	/**
	 * register subscriber
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   int $id
	 */
	public function registerUser( $id ) {

		$this->_pushUserData( $id );
	}

	/**
	 * update profile
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   int $id
	 * @param   object $oldUser raw user object not a WP_User
	 */
	public function updateProfile( $id, $oldUser ) {

		if ( $this->pushProfileUpdates ) {

			$this->_pushUserData( $id, $oldUser );
		}
	}

	/**
	 * pushUserData
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   int $id
	 * @param   object $oldUser raw user object not a WP_User
	 */
	protected function _pushUserData( $id, $oldUser = null ) {

		$user = get_userdata( $id );
		if ( ! $user )
			return false;

		// find
		$subscriberFound = $this->_findSubscriber( $user, $oldUser );

		// update
		$this->_updateSubscriber( $user, $subscriberFound );

		// save
		$success = $this->subscriber->save();

		// update user meta
		if ( $success && $this->subscriberKeyEnabled ) {
			update_user_meta( $id, 'xt-subscriber-key', $this->subscriber->subscriberData['SubscriberKey'] );
		}
	}


	/**
	 * findSubscriber
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   WP_User $user
	 * @param   object $oldUser raw user object not a WP_User
	 * @return  boolean
	 */
	protected function _findSubscriber( WP_User $user, $oldUser = null ) {

		$found = false;

		// if using subscriber key
		if ( $this->subscriberKeyEnabled ) {

			$key = get_the_author_meta( 'xt-subscriber-key', $user->ID );
			$key = ( !empty( $key ) ) ? $key : $user->user_email;

			$found = $this->subscriber->findBy( 'SubscriberKey', $key );
		}

		// if not found by subscriber key or subscriber key not enabled
		if ( !$found ) {

			$found = $this->subscriber->findBy( 'EmailAddress', $user->user_email );
		}

		// if not found yet, try looking by the user's old email, if the user's email has been updated
		if ( !$found && !empty( $oldUser ) && $oldUser->user_email !== $user->user_email ) {

			$found = $this->subscriber->findBy( 'EmailAddress', $oldUser->user_email );
		}

		return $found;
	}

	/**
	 * updateSubscriber
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   WP_User $user
	 * @param   boolean $subscriberFound
	 */
	protected function _updateSubscriber( $user, $subscriberFound ) {

		// if subscriber found but not using subscriber key, set id specified so email can be updated
		if ( $subscriberFound && ! $this->subscriberKeyEnabled ) {

			$this->subscriber->subscriberData['IDSpecified'] = true;
		}

		// if not found, create subscriber key
		if ( ! $subscriberFound ) {

			$this->subscriber->subscriberData['SubscriberKey'] = $user->user_email;
		}

		$this->subscriber->subscriberData['EmailAddress'] = $user->user_email;

		$this->subscriber->addSubscriberLists( $this->mailingLists );

		$this->_updateAttributes( $user );
	}

	/**
	 * updateAttributes
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   WP_User $user
	 */
	protected function _updateAttributes( WP_User $user ) {

		$this->_initAttributes( $user );

		// update attributes
		foreach ( $this->subscriberAttributes as $newAttribute ) {

			$attributeExists = false;

			// find and update existing attribute
			foreach ( $this->subscriber->subscriberData['Attributes'] as &$currentAttribute ) {

				if ( strtolower( $currentAttribute->Name ) === strtolower( $newAttribute['Name'] ) ) {

					$currentAttribute->Value = $newAttribute['Value'];
					$attributeExists = true;
				}
			}

			// if subscriber doesn't have the attribute already create one
			if ( !$attributeExists ) {

				$attribute = new StdClass();
				$attribute->Name = $newAttribute['Name'];
				$attribute->Value = $newAttribute['Value'];

				$this->subscriber->subscriberData['Attributes'][] = $attribute;
			}
		}
	}

	/**
	 * init subscriber attributes
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   WP_User $user
	 */
	protected function _initAttributes( WP_User $user ) {

		$userProperties = array(
			'xt_attribute_id'           => $user->ID,
			'xt_attribute_username'     => $user->user_login,
			'xt_attribute_email'        => $user->user_email,
			'xt_attribute_first_name'   => $user->user_firstname,
			'xt_attribute_last_name'    => $user->user_lastname,
			'xt_attribute_display_name' => $user->display_name,
			'xt_attribute_nice_name'    => $user->user_nicename,
			'xt_attribute_nickname'     => $user->nickname,
			'xt_attribute_description'  => $user->description,
			'xt_attribute_url'          => $user->user_url,
			'xt_attribute_registered'   => $user->user_registered,
		);

		foreach ( $userProperties as $optionName => $userValue ) {

			$xtAttributeName = get_option( $optionName, '' );
			if ( !empty( $xtAttributeName ) ) {

				$this->subscriberAttributes[] = array(
					'Name'  => $xtAttributeName,
					'Value' => $userValue,
				);
			}
		}
	}
}