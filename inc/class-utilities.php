<?php

/** 
*	Rebrickable API Utilities
*
*	
*
*	@author		Nate Jacobs
*	@date		4/1/13
*	@since		1.0
*/
class RebrickAPIUtilities
{
/** 
	 *	Remote Request
	 *
	 *	Send the api request to Brickset. Returns an XML formatted response.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		string	$extra_url (url needed after base url)
	 *	@param		string	$params (query parameters)
	 *
	 *	@return		object	WP_Error
	 *	@return		array	$response_body
	 */
	protected function remote_request( $type, $extra_url, $params = '' )
	{
		$api_url = 'http://rebrickable.com/api';	

		if( 'get' == $type )
		{
//wp_die( $api_url.'/'.$extra_url.'?'.$params );
			$response = wp_remote_get( $api_url.'/'.$extra_url.'?'.$params ); //'?key='.self::get_api_key().'&'.$params.'&format=json'
		}
		elseif( 'post' == $type )
		{
			$response = wp_remote_post( $api_url.'/'.$extra_url, $params );
		}
		else
		{
			return new WP_Error( 'no-type-specified', __( 'Specify a type of request: get or post', 'bs_api') );
		}
		
		// Did the HTTP request fail?
		if( is_wp_error( $response ) )
			return $response;
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if( 200 != $response_code && ! empty( $response_message ) )
		{
			return new WP_Error( $response_code, __( 'Don\'t Panic! Something went wrong and Rebrickable didn\'t reply.', 'rebrick_api' ) );
		}
		elseif( 200 != $response_code )
		{
			return new WP_Error( $response_code, __( 'Unknown error occurred', 'rebrick_api') );
		}
		elseif( $extra_url != 'login' && 300 > strlen( $response_body ) && $type == 'get' )
		{
				return new WP_Error( 'rebrick-no-data', __( 'Sorry, no parts/sets were found for that query', 'rebrick_api' ) );
		}
		else
		{
			return $response_body;
		}
	}
	
	private function get_api_key()
	{
		$settings = (array) get_option( 'rebrick-api-settings' );
		
		return (isset( $settings['api_key'] ) ? $settings['api_key'] : '');
	}
	
	/** 
	*	Build Query
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/2/13
	*	@since		1.0
	*
	*	@param		
	*/
	protected function build_rebrick_query( $args = array() )
	{
		$default = array( 
			'key' 		=> 	self::get_api_key(),
			'format'	=>	'json',
		);
		
		if( !empty( $args ) )
		{
			$args = array_merge( $default, $args  );
		}
		else
		{
			$args = $default;
		}
		
		$params = build_query( urlencode_deep( $args ) );
//wp_die( $params );			
		return $params;
	}
	
	/** 
	*	Validate String
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/2/13
	*	@since		1.0
	*
	*	@param		
	*/
	protected function validate_string( $string )
	{
		if( !is_string( $string ) )
		{
			return new WP_Error( 'invalid-string', __( 'The set or part ID specified is not a valid string.', 'rebrick_api' ) );
		}
		else
		{
			return sanitize_text_field( $string );
		}
	}
	
	/** 
	*	Validate Set Number
	*
	*	Checks if the set number passed has a variant, if not, one is added
	*	The search query requires sets in the format of 9999-9
	*
	*	@author		Nate Jacobs
	*	@date		2/9/13
	*	@since		1.0
	*
	*	@param		string	$set_number
	*
	*	@return		string	$set_number
	*/
	protected function validate_set_number( $set_number )
	{
		// If no set is passed, get out
		if( empty( $set_number ) )
			return;
		
		// Get set numbers into an array
		$number_check = explode( '-', $set_number );

		// If no variant present, add the -1
		return ( empty( $number_check[1] ) ? $number_check[0].'-1' : $set_number );	
	}
	
	/** 
	*	Check Transient
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/2/13
	*	@since		1.0
	*
	*	@param		
	*/
	protected function check_transient( $transient, $args = array(), $function = '', $type = 'get' )
	{
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$params = $this->build_rebrick_query( $args );

			$response = $this->remote_request( $type, $function, $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, WEEK_IN_SECONDS );
		}
		
		return json_decode( get_transient( $transient ) );
	}
}