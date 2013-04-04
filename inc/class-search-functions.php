<?php

/** 
*	Rebrickable API Search Functions
*
*	
*
*	@author		Nate Jacobs
*	@date		4/1/13
*	@since		1.0
*/
class RebrickAPISearch extends RebrickAPIUtilities
{
	/** 
	*	Construct Method
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/1/13
	*	@since		1.0
	*
	*	@param		
	*/
	public function __construct()
	{
		
	}

	/** 
	*	Get Set Parts
	*
	*	Use this service to get a list of all parts in a set. It includes both normal and spare parts.
	*
	*	@author		Nate Jacobs
	*	@date		4/1/13
	*	@since		1.0
	*
	*	@param		string	$set_id
	*/
	public function get_set_parts( $set_id )
	{
		// Is the set_id valid?
		$safe_set_id = $this->validate_string( $set_id );
		if( is_wp_error( $safe_set_id ) )
			return $safe_set_id;
		
		$safe_set_id = $this->validate_set_number( $safe_set_id );
		$transient = str_replace( '-', '', $safe_set_id );	
		
		// Have we stored a transient?
		return $this->check_transient( $transient, array( 'set' => $safe_set_id ), 'get_set_parts'  );
	}
	
	/** 
	*	Get Part
	*
	*	Use this service to get details about a part, such as its name, number of sets it appears in, which colors it appears in, etc.
	*
	*	@author		Nate Jacobs
	*	@date		4/1/13
	*	@since		1.0
	*
	*	@param		string	$part_id
	*/
	public function get_part( $part_id )
	{
		// Is the part_id valid?
		$safe_part_id = $this->validate_string( $part_id );
		if( is_wp_error( $safe_part_id ) )
			return $safe_part_id;
		
		// Have we stored a transient?
		return $this->check_transient( 'rebrick_sets_part-'.$safe_part_id, array( 'part_id' => $safe_part_id ), 'get_part'  );
	}
	
	/** 
	*	Get Sets with Part
	*
	*	Use this service to get a list of all sets that a specific part/color combination appears in.
	*
	*	@author		Nate Jacobs
	*	@date		4/2/13
	*	@since		1.0
	*
	*	@param		string	$part_id
	*	@param		int	$color_id LDRAW based Color ID
	*/
	public function get_sets_with_part( $part_id, $color_id )
	{
		// Was a color_id specified?
		if( !isset( $color_id ) )
			return new WP_Error( 'no-color-id', __( 'No color id specified.', 'rebrick_api' ) );
			
		// Is the color_id an integer?	
		if( !is_int( $color_id ) )
			return new WP_Error( 'color-id-not-valid', __( 'The color id is not an integer.', 'rebrick_api' ) );
			
		// Is the part_id valid?
		$safe_part_id = $this->validate_string( $part_id );
		if( is_wp_error( $safe_part_id ) )
			return $safe_part_id;
		
		$transient = 'rebrick_sets_part-'.$safe_part_id.'_color-'.$color_id;
		
		// Have we stored a transient?
		return $this->check_transient( $transient, array( 'part_id' => $safe_part_id, 'color_id' => $color_id ), 'get_part_sets'  );
	}
	
	/** 
	*	Get Colors
	*
	*	Use this service to get a list of all the colors used by parts in the database.
	*
	*	@author		Nate Jacobs
	*	@date		4/2/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function get_colors()
	{
		// Have we stored a transient?
		return $this->check_transient( 'rebrick_colors', '', 'get_colors'  );
	}
}