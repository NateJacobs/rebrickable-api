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
	 *	Send the api request to Rebrickable. Returns an XML formatted response.
	 *
	 *	@author		Nate Jacobs
	 *	@date		4/2/13
	 *	@since		1.0
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
			$response = wp_remote_get( $api_url.'/'.$extra_url.'?'.$params ); 
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
		elseif( ctype_upper( $response_body ) )
		{
			if( 'INVALIDKEY' === $response_body )
				return new WP_Error( 'rebrick-invalid-key', __( 'The API Key is invalid.', 'rebrick_api' ) );
			
			if( 'INVALIDUSERPASS' === $response_body )
				return new WP_Error( 'rebrick-invalid-user-pass', __( 'Invalid user email or password.', 'rebrick_api' ) );
				
			if( 'NOUSER' === $response_body )
				return new WP_Error( 'rebrick-no-user', __( 'The Rebrickable user does not exist.', 'rebrick_api' ) );
				
			if( 'INVALIDPASS' === $response_body )
				return new WP_Error( 'rebrick-invalid-pass', __( 'The password does not match the user.', 'rebrick_api' ) );
				
			if( 'INVALIDHASH' === $response_body )
				return new WP_Error( 'rebrick-invalid-hash', __( 'The hash key does not match a user/password.', 'rebrick_api' ) );
				
			if( 'NOSET' === $response_body )
				return new WP_Error( 'rebrick-no-set', __( 'No sets were found for that query.', 'rebrick_api' ) );
				
			if( 'NOPART' === $response_body )
				return new WP_Error( 'rebrick-no-part', __( 'No parts or colors were found for that query.', 'rebrick_api' ) );
				
			if( 'SUCCESS' === $response_body )
				return $response_body;
		}
		else
		{
			return $response_body;
		}
	}
	
	/** 
	 *	Login Service Method
	 *
	 *	Authenticates a user with Rebrick and returns a hash.
	 *	The hash is then stored as a meta value with the key of 'rebrick_user_hash'
	 *	in the *_usersmeta table.
	 *
	 *	@author		Nate Jacobs
	 *	@date		4/3/13
	 *	@since		1.0
	 *	@updated	1.0
	 *
	 *	@param	int 	$user_id
	 *	@param	string 	$email
	 *	@param	string	$password
	 *
	 *	@return	array	$response (if there is an error, a WP_Error array is returned)
	 */
	protected function rebrick_login( $user_id, $email, $password )
	{
		// Which user is this?
		$user = get_userdata( $user_id );
		
		// Build the parameters
		$params = 'key='.self::get_api_key().'&email='.urlencode( $email ).'&pass='.urlencode( $password );
		
		// Send it off
		$response = $this->remote_request( 'get', 'get_user_hash', $params );
		
		if( is_wp_error( $response ) )
		{
			return $response;
		}
		else
		{
			update_user_meta( $user->ID, 'rebrick_user_hash',  (string) $response );
		}
	}
	
	private function get_api_key()
	{
		$settings = (array) get_option( 'rebrick-api-settings' );
		
		return (isset( $settings['api_key'] ) ? $settings['api_key'] : '');
	}
	
	/** 
	*	Get UserHash
	*
	*	Returns the Rebrick userHash from user_meta
	*
	*	@author		Nate Jacobs
	*	@date		4/3/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*
	*	@return		string	$user_hash
	*/
	protected function get_user_hash( $user_id )
	{
		return get_user_meta( $user_id, 'rebrick_user_hash', true );
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
		
		if( array_key_exists( 'user_id', $args ) )
		{
			$args['hash'] = $this->get_user_hash( $args['user_id'] );
			unset( $args['user_id'] );
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
	*	Validate User ID
	*
	*	Takes a user ID and determines if it is an integer and is a valid user in the site
	*
	*	@author		Nate Jacobs
	*	@date		4/28/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*
	*	@return		object	WP_Error (if not a user or an int)
	*	@return		bool	true (if a valid user and an int)
	*/
	protected function validate_user( $user_id )
	{
		// Is there a user?
		if( empty( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'rebrick_api' ) );
			
		// Is it an integer?
		if( !is_int( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'rebrick_api' ) );
		
		// Does the user_id specified exist on this site?
		if( !get_user_by( 'id', $user_id ) )
			return new WP_Error( 'not-valid-user', __( 'The user ID passed is not a valid user.', 'rebrick_api' ) );
		
		$user_hash = $this->get_user_hash( $user_id );
			
		if( empty( $user_hash ) )
			return new WP_Error( 'no-user-hash', __( 'The user ID passed does not have a Brickset API identifier on file.', 'rebrick_api' ) );	
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
				return $response;
			
			// check which api call was requested
			if( 'get_user_set' === $function )
			{
				// set the quanity to 0 if the response is empty or NULL
				// otherwise set quantity to the number returned
				$quantity = empty( $response ) ? '0' : $response;
				
				$response = json_encode( array( 'qty' =>  $quantity ) );
			}
			
			set_transient( $transient, $response, WEEK_IN_SECONDS );
		}
		
		// get the transient into an object
		$return = json_decode( get_transient( $transient ) );
		
		// check if the object is wrapped in an array with a key of [0] and that is the only key/value pair
		$rebrick_data = array_key_exists( 0, $return ) && 1 === count( $return ) ? $return[0] : $return;
		
		return $rebrick_data;
	}
}