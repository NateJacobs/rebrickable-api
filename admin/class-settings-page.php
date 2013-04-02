<?php

class RebrickAPISettingsPage
{
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_rebrickable_submenu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}
	
	/** 
	*	Add Rebrickable Submenu
	*
	*	Adds the submenu to the default WordPress settings menu.
	*
	*	@author		Nate Jacobs
	*	@date		04/1/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function add_rebrickable_submenu()
	{
		add_options_page( 
			__( 'Rebrickable API Settings', 'rebrick_api' ), 
			__( 'Rebrickable API', 'rebrick_api' ), 
			'manage_options', 
			'rebrickable-api-options', 
			array( $this, 'rebrick_api_options_callback' ) 
		);
	}
	
	/** 
	*	Rebrickable Options Page
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/1/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function rebrick_api_options_callback()
	{
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php _e( 'Rebrickable API Settings', 'bs_api' ); ?></h2>
			<?php //settings_errors(); ?>
			<form method="post" action="options.php">
				<?php settings_fields( 'rebrick_api_options' ); ?>
				<?php do_settings_sections( 'rebrick-api-options' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
	/** 
	*	Settings Init
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/1/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function settings_init()
	{
		add_settings_section( 
			'rebrick-webservice-settings', 
			__( 'API Key', 'rebrick_api' ), 
			array( $this, 'webservice_settings_callback' ), 
			'rebrick-api-options'	 
		);
		add_settings_field( 
			'rebrick-api-key', 
			__( 'Enter your API Key', 'rebrick_api' ), 
			array( $this, 'apikey_callback' ), 
			'rebrick-api-options', 
			'rebrick-webservice-settings'
		);

		register_setting( 
			'rebrick_api_options', 
			'rebrick-api-settings'
		);
	}
	
	/** 
	*	API Key
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		4/1/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function webservice_settings_callback()
	{
		echo __( 'You may obtain a key at ', 'rebrick_api' )."<a href='http://rebrickable.com/contact/'>Rebrickable.com</a>";

	}
	
	public function apikey_callback() 
	{
 		$settings = (array) get_option( 'rebrick-api-settings' );
		$api_key = isset( $settings['api_key'] ) ? esc_attr( $settings['api_key'] ) : '';

		echo "<input type='text' name='rebrick-api-settings[api_key]' value='$api_key' />";
		
 	}
}

$rebrick_settings = new RebrickAPISettingsPage();