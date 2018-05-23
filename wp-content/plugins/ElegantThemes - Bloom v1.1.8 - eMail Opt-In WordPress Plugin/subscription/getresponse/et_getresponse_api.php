<?php

/**
 * Class for the API calls to getresponse server
 * Includes basic functions such as get_campaigns, get_contacts, add_contact
 */
class ET_Getresponse {
	public $api_key = '';

	public function __construct( $api_key = '' ) {
		$this->api_key = $api_key;
	}

	/**
	 * Get a list of all active campaigns
	 */
	public function get_campaigns()	{
		$request_data = json_encode(
			array(
				'method' => 'get_campaigns',
				'params' => array(
					sanitize_text_field( $this->api_key ),
					array(
						'name' => array( 'CONTAINS' => '%' ),
					),
				),
				'id'     => null,
			)
		);

		return $this->getresponse_request( $request_data );
	}

	/**
	 * Get a list of contacts filtered by campaigns
	 */
	public function get_contacts( $campaigns = '' )	{
		$request_data = json_encode(
			array(
				'method' => 'get_contacts',
				'params' => array(
					$this->api_key,
					array(
						'name'      => array( 'CONTAINS' => '%' ),
						'campaigns' => $campaigns,
					),
				),
				'id'     => null,
			)
		);

		return $this->getresponse_request( $request_data );
	}

	/**
	 * Add contact with all the contact details to specified list
	 */
	public function add_contact( $contact_deails = array() ) {
		$request_data = json_encode(
			array(
				'method' => 'add_contact',
				'params' => array(
					$this->api_key,
					$contact_deails,
				),
				'id'     => null,
			)
		);

		return $this->getresponse_request( $request_data );
	}

	/**
	 * Performs API call to getresponse server
	 */
	private function getresponse_request( $request_data ) {
		$request_url = 'http://api2.getresponse.com';

		// Get cURL resource
		$curl = curl_init();

		// Set some options
		curl_setopt_array( $curl, array(
			CURLOPT_POST           => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $request_url,
			CURLOPT_HEADER         => 'Content-type: application/json',
			CURLOPT_SSL_VERIFYPEER => FALSE, //we need this option since we perform request to https
			CURLOPT_POSTFIELDS     => $request_data,
		) );

		// Send the request & save response to $resp
		$resp = curl_exec( $curl );

		// Close request to clear up some resources
		curl_close( $curl );

		$result = json_decode( $resp, true );

		return $result;
	}
}