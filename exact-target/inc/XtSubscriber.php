<?php
/**
* Exact Target Subscriber Class
*
* @author     Joe Sexton <joe.sexton@bigideas.com>
* @package    WordPress
* @subpackage exact-target
*/
class XtSubscriber {

	const XT_WSDL      = 'https://webservice.exacttarget.com/etframework.wsdl';
	const XT_NAMESPACE = 'http://exacttarget.com/wsdl/partnerAPI';

	const ACTIVE       = 'Active';
	const BOUNCED      = 'Bounced';
	const HELD         = 'Held';
	const UNSUBSCRIBED = 'Unsubscribed';
	const DELETED      = 'Deleted';

	/**
	 * @var ExactTargetSoapClient
	 */
	protected $client;

	/**
	 * @var array
	 */
	public $subscriberData = array(
		"ID"                      => null,
		"SubscriberKey"           => null,
		"EmailAddress"            => null,
		"PartnerKey"              => null,
		"CreatedDate"             => null,
		"UnsubscribedDate"        => null,
		"Status"                  => null,
		"Attributes"              => array(),
		"Lists"                   => array()
	);

	/**
	 * constructor
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   string $api
	 * @param   string $username
	 * @param   string $password
	 */
	public function __construct( $api, $username, $password )
	{
		require_once $api;

		// exact target client
		$this->client = new ExactTargetSoapClient( self::XT_WSDL, array( 'trace' => 1 ) );
		$this->client->username = get_option( 'xt_username' );
		$this->client->password = get_option( 'xt_password' );

		$this->initSubscriber();
	}

	/**
	 * initSubscriber
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 */
	public function initSubscriber() {

		$this->subscriberData = array(
			"ID"                      => null,
			"SubscriberKey"           => null,
			"EmailAddress"            => null,
			"PartnerKey"              => null,
			"CreatedDate"             => null,
			"UnsubscribedDate"        => null,
			"Status"                  => null,
			"Attributes"              => array(),
			"Lists"                   => array()
		);
	}

	/**
	 * find by
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   string $filterProperty
	 * @param   string $filterValue
	 * @return  Object | false
	 */
	public function findBy( $filterProperty, $filterValue ) {

		try	{
			// create request
			$request = new ExactTarget_RetrieveRequest();
			$request->ObjectType = 'Subscriber';
			$request->Properties = $this->_getPropertiesToRetrieve();

			// filter
			$filter = new ExactTarget_SimpleFilterPart();
			$filter->SimpleOperator = ExactTarget_SimpleOperators::equals;
			$filter->Property       = $filterProperty;
			$filter->Value          = $filterValue;
			$request->Filter        = new SoapVar( $filter, SOAP_ENC_OBJECT, "SimpleFilterPart", self::XT_NAMESPACE );

			$requestMsg = new ExactTarget_RetrieveRequestMsg();
			$requestMsg->RetrieveRequest = $request;

			$results = $this->client->Retrieve( $requestMsg );

		} catch ( SoapFault $e ) {

			return false;
		}

		if( $results->OverallStatus === 'OK' && isset( $results->Results ) )
		{
			$this->_updateSubscriberData( $results->Results );

			return true;
		}

		return false;
	}

	/**
	 * save
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  boolean
	 */
	public function save() {

		try	{
			// Subscriber
			$subscriber = new ExactTarget_Subscriber();
			$subscriber->SubscriberKey = $this->subscriberData['SubscriberKey'];
			$subscriber->EmailAddress  = $this->subscriberData['EmailAddress'];

			// Attributes
			if ( !empty( $this->subscriberData['Attributes'] ) ) {

				foreach ( $this->subscriberData['Attributes'] as $attribute ) {

					$xtAttribute        = new ExactTarget_Attribute();
					$xtAttribute->Name  = $attribute->Name;
					$xtAttribute->Value = $attribute->Value;

					$subscriber->Attributes[] = $xtAttribute;
				}
			}

			// Mailing Lists
			$subscriber->Lists = $this->subscriberData['Lists'];

			// Persist
			$saveOption = new ExactTarget_SaveOption();
			$saveOption->PropertyName = "Subscriber";
			$saveOption->SaveAction = "UpdateAdd";

			$requestOptions = new ExactTarget_CreateOptions();
			$requestOptions->SaveOptions[] = new SoapVar( $saveOption, SOAP_ENC_OBJECT, 'SaveOption', self::XT_NAMESPACE );

			$request = new ExactTarget_CreateRequest();
			$request->Options = new SoapVar( $requestOptions, SOAP_ENC_OBJECT, 'CreateOptions', self::XT_NAMESPACE );
			$request->Objects = array( new SoapVar( $subscriber, SOAP_ENC_OBJECT, 'Subscriber', self::XT_NAMESPACE ) );

			$results = $this->client->Create( $request );

		} catch ( SoapFault $e ) {

			return false;
		}

		if ( $results->OverallStatus === 'OK' ) {

			$this->_updateSubscriberData( $results->Results->Object );

			return true;

		} else {

			return false;
		}
	}

	/**
	 * addSubscriberLists
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   array $lists
	 * @return  Boolean
	 */
	public function addSubscriberLists( $lists ) {

		// load current subscriber lists
		$currentExactTargetLists = $this->_getCurrentLists( $this->subscriberData['SubscriberKey'] );

		// update the subscription status of each list and add list if not in exact target yet
		foreach ( $lists as $list ) {

			$list = (int)$list;

			$subscriberList = $this->_findListById( $list, $currentExactTargetLists );

			// if the user is not on the ET list, add them
			if ( empty( $subscriberList ) ) {
				$this->_updateListStatus( $list, self::ACTIVE, true );
			}

		}

		return true;
	}

	/**
	 * getCurrentLists
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   string $subscriberKey
	 * @return  Object|null
	 */
	protected function _getCurrentLists( $subscriberKey )
	{
		$request = new ExactTarget_RetrieveRequest();
		$request->ObjectType = 'ListSubscriber';
		$request->Properties = array( "ListID", "SubscriberKey", "Status" );

		$filter = new ExactTarget_SimpleFilterPart();
		$filter->Property = 'SubscriberKey';
		$filter->Value = array( $subscriberKey );
		$filter->SimpleOperator = ExactTarget_SimpleOperators::equals;

		$request->Filter = new \SoapVar( $filter, SOAP_ENC_OBJECT, 'SimpleFilterPart', self::XT_NAMESPACE );

		// Setup and execute request
		$requestMessage = new ExactTarget_RetrieveRequestMsg();
		$requestMessage->RetrieveRequest = $request;
		$results = $this->client->Retrieve( $requestMessage );

		if ( !empty( $results->Results ) ) {

			return $results->Results;

		} else {

			return null;
		}
	}

	/**
	 * findListById
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   integer $id
	 * @param   array $lists
	 * @return  EtListSubscriber | null
	 */
	protected function _findListById( $id, $lists ) {

		if ( empty( $lists ) ) {

			return null;
		}

		if ( is_array( $lists ) ) {
			foreach ( $lists as $list ) {
				if ( $list->ListID === $id ) {

					return $list;
				}
			}
		} else if ( property_exists( $lists, 'ListID' ) && $lists->ListID === $id ) {

			return $lists;
		}

		return null;
	}

	/**
	 * updateListStatus
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   int $listId EtList->getID()
	 * @param   string $status
	 * @param   boolean $new
	 */
	protected function _updateListStatus( $listId, $status = null, $new = true ) {

		$slist = new ExactTarget_SubscriberList();
		$slist->ID = $listId;
		$slist->Status = $status;

		if ( $new === true ) {
			$slist->Action = "create";
		} else {
			$slist->Action = "Update";
		}

		$this->subscriberData['Lists'][] = new \SoapVar( $slist, SOAP_ENC_OBJECT, 'SubscriberList', self::XT_NAMESPACE );
	}


	/**
	 * updateSubscriberData
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   array $data
	 */
	protected function _updateSubscriberData( $data ) {

		foreach ( $data as $key => $val ) {

			if ( array_key_exists( $key, $this->subscriberData ) ) {

				$this->subscriberData[$key] = $val;
			}
		}
	}

	/**
	 * _getPropertiesToRetrieve
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  array
	 */
	protected function _getPropertiesToRetrieve() {

		// get all proeprties except Lists and Attributes
		// XT will throw an error if we ask for those in this context
		$properties = array_keys( $this->subscriberData );
		foreach ( $properties as $key => $val ) {

			if ( $val == 'Attributes' || $val == 'Lists' )
				unset( $properties[$key] );
		}

		return $properties;
	}
}