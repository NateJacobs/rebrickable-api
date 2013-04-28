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
	*	It will first delete any existing parts, so you should always pass in the complete list. 
	*	If any parts cannot be found in the database, they will be silently ignored. 
	*
	*	@author		Nate Jacobs
	*	@date		4/28/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*	@param		array	$parts
	*/
	public function update_user_parts( $user_id, $parts = array() )
	{
		
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
		
	}
	
	/** 
	*	Update User Sets
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
		
	}
}