<?php

/** 
*	Rebrickable API User Profile
*
*	Display necessary fields on the user profile page to allow a user to authenticate themselves with Rebrickable.
*	Take the user hash and save it in the usermeta table.
*
*	@author		Nate Jacobs
*	@date		4/3/13
*	@since		1.0
*/
class RebrickAPIUserProfile extends RebrickAPIUtilities
{
	/** 
	*	Construct Method
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/3/13
	*	@since		1.0
	*
	*	@param		
	*/
	public function __construct()
	{
		add_action( 'show_user_profile', array( $this, 'add_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_user_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'set_rebrick_user_hash' ) );
		add_action( 'edit_user_profile_update', array( $this, 'set_rebrick_user_hash' ) );
	}

	/** 
	*	Add Rebrick Login Fields
	*
	*	Add Rebrickable email, password, and userHash fields to the profile page.
	*
	*	@author		Nate Jacobs
	*	@date		4/3/13
	*	@since		1.0
	*
	*	@param		object	$user
	*	@return		null
	*/
	public function add_user_profile_fields( $user)
	{
		$user_hash = $this->get_user_hash( $user->ID );
		?>
		<h3><?php _e( 'Rebrickable Login Information', 'rebrick_api' ); ?></h3>
		<span><?php _e( 'If the Rebrickable Identifier is filled you do not need to add your email and password unless you have changed your password on Rebrickable.', 'rebrick_api' ); ?></span>
		<table class="form-table">
		<tr>
			<th><label for="rebrick_email"><?php _e( 'Rebrickable Email', 'rebrick_api' ); ?></label></th>
			<td><input type="text" name="rebrick_email" id="rebrick_email" value="" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="rebrick_password"><?php _e( 'Rebrickable Password', 'rebrick_api' ); ?></label></th>
			<td><input type="password" name="rebrick_password" id="rebrick_password" value="" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="rebrick_user_hash"><?php _e( 'Rebrickable Identifier', 'rebrick_api' ); ?></label></th>
			<td><input type="text" readonly name="rebrick_user_hash" id="rebrick_user_hash" value="<?php echo $user_hash; ?>" class="regular-text" /></td>
		</tr>
		</table>
		<?php
	}
	
	/** 
	*	Save User Profile Fields
	*
	*	Takes the entered Rebrickable email and password and gets the userHash.
	*
	*	@author		Nate Jacobs
	*	@date		4/3/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*	@return		null
	*/
	public function set_rebrick_user_hash( $user_id )
	{
		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;
		
		if( !empty( $_POST['rebrick_email'] ) && !empty( $_POST['rebrick_password'] ) )
		{
			$response = $this->rebrick_login( $user_id, $_POST['rebrick_email'], $_POST['rebrick_password'] );
		
			if( is_wp_error( $response ) )
			{
				wp_die( $response->get_error_message(), 'rebrick-login-error', array( 'back_link' => true ) );
			}
		}
		else
		{
			// do nothing	
		}
	}
}

$rebrick_user_profile = new RebrickAPIUserProfile;