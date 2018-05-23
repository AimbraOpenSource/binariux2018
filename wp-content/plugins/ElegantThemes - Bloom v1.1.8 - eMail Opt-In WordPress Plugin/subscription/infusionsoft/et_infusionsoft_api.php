<?php

/**
 * Class for the API calls to infusionsoft server
 * Includes basic functions such as database_query, database_count, add_to_list, opt_in_email, add_contact
 */
class ET_Infusionsoft {
	private $_api_key  = '';
	private $_app_name = '';

	private $_xml_template = '';

	public function __construct( $api_key = '', $app_name = '' ) {
		$this->_api_key  = sanitize_text_field( $api_key );
		$this->_app_name = sanitize_text_field( $app_name );

		$this->_create_xml_template();
	}

	/**
	 * Create XML request template
	 * @return void
	 */
	private function _create_xml_template() {
		$xml_template =
			'<?xml version="1.0" encoding="UTF-8"?>
			<methodCall>
				<methodName>%1$s</methodName>
				<params>
					<param>';

		$xml_template .= sprintf( '<value><string>%1$s</string></value>', esc_html( $this->_api_key ) );

		$xml_template .= '
					</param>
					%2$s
				</params>
			</methodCall>';

		$this->_xml_template = $xml_template;
	}

	/**
	 * Create XML request string
	 *
	 * @param  array  $args API Action ( string ) / Request parameters ( array ) / Independent parameters ( boolean )
	 * @return string
	 */
	public function create_xml_request( $args ) {
		$parameters = '';

		$use_independent_parameters = isset( $args['independent_parameters'] ) && $args['independent_parameters'];

		$param_template =
			'<param>
				%1$s
			</param>';

		$value_template = '<value><%2$s>%1$s</%2$s></value>';

		$whitelisted_types = array( 'string', 'int', 'array', 'struct' );
		$fallback_type     = 'string';

		foreach ( $args['params'] as $name => $settings ) {
			$is_no_value_tags = isset( $settings['no_value_tags'] ) && $settings['no_value_tags'];

			// array key simply acts as a label, if independent parameters are used,
			// $settings value should be used instead
			$value = ! $use_independent_parameters ? $name : $settings['value'];

			$type = isset( $settings['type'] ) ? $settings['type'] : $fallback_type;

			if ( ! in_array( $type, $whitelisted_types ) ) {
				$type = $fallback_type;
			}

			if ( 'array' === $type ) {
				$value = sprintf(
					'<data>
						%1$s
					</data>',
					$value
				);
			}

			if ( ! $is_no_value_tags ) {
				$value = sprintf(
					$value_template,
					$value,
					sanitize_text_field( $type )
				);
			}

			$parameters .= sprintf(
				$param_template,
				$value
			);

			if ( ! $use_independent_parameters ) {
				$value = ! $is_no_value_tags
					? sprintf(
						$value_template,
						sanitize_text_field( $settings['value'] ),
						sanitize_text_field( $type )
					)
					: sanitize_text_field( $settings['value'] );

				$parameters .= sprintf(
					$param_template,
					$value
				);
			}
		}

		$request = sprintf(
			$this->_xml_template,
			esc_html( $args['action'] ),
			$parameters
		);

		return $request;
	}

	/**
	 * Perform the test request to infusionsoft server to check the API key and App ID
	 */
	public function connection_check() {
		$params = array(
			'Application' => array(
				'value' => 'enabled',
			),
		);

		$data = $this->create_xml_request( array(
			'action' => 'DataService.getAppSetting',
			'params' => $params,
		) );

		$result = $this->make_request( $data );

		if ( 0 === $result || empty( $result ) ) {
			return esc_html__( 'Invalid App ID', 'bloom' );
		}

		if ( isset( $result->fault ) ) {
			return esc_html__( 'Invalid Api Key', 'bloom' );
		}

		return;
	}

	/**
	 * Perform the database query to specified table with specific conditions
	 */
	public function database_query( $table_name = '', $limit, $page, $search_fields, $return_fields ) {
		$search_fields_xml = $return_fields_xml = '';

		$value_xml_template = '<value><string>%1$s</string></value>';

		if ( ! empty( $search_fields ) ) {
			foreach( $search_fields as $field_name => $value ) {
				$value = sprintf(
					$value_xml_template,
					esc_html( $value )
				);

				$search_fields_xml .= sprintf(
					'<value>
						<struct>
							<member>
								<name>%1$s</name>
								%2$s
							</member>
						</struct>
					</value>',
					esc_html( $field_name ),
					$value
				);
			}
		}

		if ( ! empty( $return_fields ) ) {
			foreach( $return_fields as $field_name ) {
				$return_fields_xml .= sprintf(
					$value_xml_template,
					esc_html( $field_name )
				);
			}
		}

		$params = array(
			'Table Name'        => array(
				'value' => $table_name,
			),
			"Limit"             => array(
				'value' => $limit,
				'type'  => 'int',
			),
			"Page"              => array(
				'value' => $page,
				'type'  => 'int',
			),
			"Search Fields XML" => array(
				'value'         => $search_fields_xml,
				'no_value_tags' => true,
			),
			"Return Fields XML" => array(
				'value' => $return_fields_xml,
				'type'  => 'array',
			),
		);

		$data = $this->create_xml_request( array(
			'action'                 => 'DataService.query',
			'params'                 => $params,
			'independent_parameters' => true,
		) );

		$result = $this->make_request( $data );

		return (array) $result->params->param->value;
	}

	/**
	 * Perform the database count query to specified table with specific conditions
	 */
	public function database_count( $table_name = '', $search_fields ) {
		$search_fields_xml = '';

		if ( ! empty( $search_fields ) ) {
			foreach( $search_fields as $field_name => $value ) {
				$search_fields_xml .= sprintf(
					'<value><struct>
						<member>
							<name>%1$s</name>
							<value><string>%2$s</string></value>
						</member>
					</struct></value>',
					esc_html( $field_name ),
					esc_html( $value )
				);
			}
		}

		$params = array(
			'Table Name'        => array(
				'value' => $table_name,
			),
			"Search Fields XML" => array(
				'value'         => $search_fields_xml,
				'no_value_tags' => true,
			),
		);

		$data = $this->create_xml_request( array(
			'action'                 => 'DataService.count',
			'params'                 => $params,
			'independent_parameters' => true,
		) );

		$result = $this->make_request( $data );

		return (array) $result->params->param->value;
	}

	/**
	 * Add contact to the specified list ( tag in terms of infusionsoft )
	 */
	function add_to_list( $contact_id, $list_id ) {
		$params = array(
			'Contact ID' => array(
				'value' => $contact_id,
				'type'  => 'int',
			),
			'List ID'    => array(
				'value' => $list_id,
				'type'  => 'int',
			),
		);

		$data = $this->create_xml_request( array(
			'action'                 => 'ContactService.addToGroup',
			'params'                 => $params,
			'independent_parameters' => true,
		) );

		return $this->make_request( $data );
	}

	/**
	 * Opt in email to make the new customer marketable
	 */
	function opt_in_email( $email, $reason ) {
		$params = array(
			'email'  => array(
				'value' => $email,
			),
			'reason' => array(
				'value' => $reason,
			),
		);

		$data = $this->create_xml_request( array(
			'action'                 => 'APIEmailService.optIn',
			'params'                 => $params,
			'independent_parameters' => true,
		) );

		return $this->make_request( $data );
	}

	/**
	 * Add contact based on provided details
	 */
	function add_contact( $contact_details ) {
		$contact_fields = '';

		if ( ! empty( $contact_details ) ) {
			foreach( $contact_details as $field_name => $value ) {
				$contact_fields .= sprintf(
					'<member>
						<name>%1$s</name>
						<value>
							<string>%2$s</string>
						</value>
					</member>',
					$field_name,
					$value
				);
			}
		}

		$params = array(
			'Contact Fields' => array(
				'value' => $contact_fields,
				'type'  => 'struct',
			),
		);

		$data = $this->create_xml_request( array(
			'action'                 => 'ContactService.add',
			'params'                 => $params,
			'independent_parameters' => true,
		) );

		$result = $this->make_request( $data );

		return isset( $result->params->param->value->i4 ) ? $result->params->param->value->i4 : '';
	}

	/**
	 * Perform the request to InfusionSoft API and handle the response
	 * @return object
	 */
	function make_request( $postargs ) {
		if ( ! function_exists( 'curl_init' ) ) {
			return  esc_html__( 'curl_init is not defined ', 'bloom' );
		}

		$api_url = 'https://' . $this->_app_name . '.infusionsoft.com/api/xmlrpc';

		$headers = array(
			"Content-Type: text/xml",
			"Accept-Charset: UTF-8,ISO-8859-1,US-ASCII",
		);

		$response  = '';

		// Get cURL resource
		$curl = curl_init( esc_url_raw( $api_url ) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $postargs );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

		// Send the request & save response to $response
		$response = curl_exec( $curl );

		// Close request to clear up some resources
		curl_close( $curl );

		$response = simplexml_load_string( $response );

		return $response;
	}
}