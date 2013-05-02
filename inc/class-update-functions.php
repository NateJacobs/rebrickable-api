<?php

/** 
*	Rebrickable API Update Functions
*
*	
*
*	@author		Nate Jacobs
*	@date		4/28/13
*	@since		1.0
*/
class RebrickAPIUpdate extends RebrickAPIUtilities
{
	/** 
	*	Construct Method
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/28/13
	*	@since		1.0
	*
	*	@param		
	*/
	public function __construct()
	{
		
	}

	/** 
	*	Update User Parts
	*
	*	Use this service to set the list of loose parts saved in the specified user's My Parts list. 
	*	If any parts cannot be found in the database, they will be silently ignored. 
	*
	*	@author		Nate Jacobs
	*	@date		4/28/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*	@param		array	$parts
	*	@param		bool	$complete
	*
	*	@return		bool|object	$response|WP_Error
	*/
	public function update_user_parts( $user_id, $parts = array(), $complete = false )
	{
		// is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// make sure complete is a boolean value
		if( !is_bool( $complete ) )
			return new WP_Error( 'parts-list', __( 'Set $complete to true or false.', 'rebrick_api' ) );
		
		// is the full parts list passed in an array
		if( false === $complete )
		{
			// delete the stored data to make sure we have the most up-to-date parts list
			delete_transient( 'rebrick_get_user_parts-'.$user_id );
			
			// retrieve the existing parts
			$search = RebrickAPISearch::get_user_parts( $user_id );
			
			if( !empty( $search->parts ) )
			{
			
				// turn returned object into array
				foreach( $search->parts as $key => $old_parts )
				{
					$existing_parts[$key] = (array) $old_parts;
				}
				
				// loop through and rename keys
				foreach ( $existing_parts as $k => $v )
				{
				  	$existing_parts[$k]['part'] = $existing_parts[$k]['part_id'];
				  	unset( $existing_parts[$k]['part_id'] );
				  	
				  	$existing_parts[$k]['color'] = $existing_parts[$k]['ldraw_color_id'];
				  	unset( $existing_parts[$k]['ldraw_color_id'] );
	
				}
			}
			else
			{
				// create empty holding array
				$existing_parts = array();
				
				// set flag to false
				$flag = false;
			}
			
			// search the existing parts for any matches in new parts
			foreach( $parts as $new_key => $part )
			{
				foreach( $existing_parts as $key => $old_part )
				{
					$flag = false;
					
					// testing for matches
					if( $existing_parts[$key]['part'] == $parts[$new_key]['part'] )
					{
						// found a match, update the quantity and color
						$existing_parts[$key]['qty'] = $parts[$new_key]['qty'];
						$existing_parts[$key]['color'] = $parts[$new_key]['color'];
						$flag = true;
						break;
					}
				}
				
				// no match found so add the new pieces to the existing parts
				if( false === $flag )
					$existing_parts[] = $parts[$new_key];
			}
		}
		// the full parts list has been passed
		elseif( true === $complete )
		{
			// change the name of the array
			$existing_parts = $parts;
		}
		
		// if there are no parts get out so no request is processed
		if( empty( $existing_parts ) )
			return new WP_Error( 'parts-list', __( 'No parts have been provided.', 'rebrick_api' ) );
		
		// create string for each part
		foreach( $existing_parts as $key => $parts_value )
		{
			$update_parts[] = $parts_value['part'].' '.$parts_value['color'].' '.$parts_value['qty'];
		}

		// get all the parts into one comma separated string
		$updated_parts_list = implode( ',', $update_parts );
		
		// create the post body
		$params = array( 'body' => array( 'key' => $this->get_api_key(), 'hash' => $this->get_user_hash( $user_id ), 'parts' => $updated_parts_list ) );
		
		// send off the request
		$response = $this->remote_request( 'post', 'set_user_parts', $params );
		
		// check if there is an error
		if( is_wp_error( $response ) )
		{
			return $response;
		}
		
		// if success is the string, return true indicating the parts were successfully updated
		if( 'SUCCESS' === substr( $response, strrpos( $response, ')' )+2, 7 ) )
		{
			return true;
		}
		else
		{
			return new WP_Error( 'parts-list', __( 'An error has occurred and Rebrickable did not process your request successfully.', 'rebrick_api' ) );
		}
	}
	
	/** 
	*	Update User Set
	*
	*	Use this service to set the quantity of a single set saved in the specified user's My Sets list. 
	*	Use 0 to delete the set.	
	*
	*	@author		Nate Jacobs
	*	@date		4/28/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*	@param		sting	$set_id
	*	@param		int	$quantity
	*/
	public function update_user_set( $user_id, $set_id, $quantity )
	{
		// is the set_id a string?
		$safe_set_id = $this->validate_string( $set_id );
		if( is_wp_error( $safe_set_id ) )
			return $safe_set_id;
		
		// is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// is the quantity an integer?
		if( !is_int( $quantity ) )
			return new WP_Error( 'no-integer', __( 'The quantity is not an integer.', 'rebrick_api' ) );
		
		$safe_set_id = $this->validate_set_number( $safe_set_id );
		
		// create the post body
		$params = array( 'body' => array( 'key' => $this->get_api_key(), 'hash' => $this->get_user_hash( $user_id ), 'set' => $safe_set_id, 'qty' => $quantity ) );
		
		// send off the request
		$response = $this->remote_request( 'post', 'set_user_set', $params );
		
		// check if there is an error
		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( 'SUCCESS' === $response )
		{
			// if success is the string, return true indicating the set was successfully updated or added
			return true;
		}
	}
	
	/** 
	*	Do not use - currently the API does not allow for decreasing the number of sets a user owns using a bulk change
	*
	*	Use this service to set the Rebrickable sets saved in the specified user's My Sets list. 
	*	It will merge these sets with any existing sets. If any sets cannot be found in the database, they will be silently ignored. 
	*
	*	@author		Nate Jacobs
	*	@date		4/28/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*	@param		array	$sets (set_id quantity, set_id quantity, etc)
	*/
	public function update_user_sets( $user_id, $sets = array() )
	{
		return new WP_Error( 'sets-list', __( 'This is not a valid function at this time.', 'rebrick_api' ) );
		
		// is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// if there are no sets get out so no request is processed
		if( empty( $sets ) )
			return new WP_Error( 'sets-list', __( 'No sets have been provided.', 'rebrick_api' ) );
		
		// create string for each set
		foreach( $sets as $key => $sets_value )
		{
			$safe_set_id = $this->validate_set_number( $sets_value['set'] );
			
			$update_sets[] = $safe_set_id.' '.$sets_value['qty'];
		}
		
		// get all the sets into one comma separated string
		$updated_set_list = implode( ',', $update_sets );
		
		// create the post body
		$params = array( 'body' => array( 'key' => $this->get_api_key(), 'hash' => $this->get_user_hash( $user_id ), 'sets' => $updated_set_list ) );
		
		// send off the request
		$response = $this->remote_request( 'post', 'set_user_sets', $params );
		
		// check if there is an error
		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( 'SUCCESS' === substr( $response, 0, 7 ) )
		{
			// if success is the string, return true indicating the parts were successfully updated
			return true;
		}
		else
		{
			return new WP_Error( 'sets-list', __( 'An error has occurred and Rebrickable did not process your request successfully.', 'rebrick_api' ) );
		}
	}
}