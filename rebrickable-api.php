<?php

/**
 *	Plugin Name: Rebrickable API
 *	Plugin URI: https://github.com/NateJacobs/rebrickable-api
 *	Description: 	
 *	Version: 1.0
 *	Date: 4/1/13
 *	Author: Nate Jacobs
 *	Author URI: http://natejacobs.org
 *	License: GPL V2
 *
 */
 
 /** 
 *	Rebrickable API Load
 *
 *	
 *
 *	@author		Nate Jacobs
 *	@date		4/1/13
 *	@since		1.0
 */
 class RebrickAPILoad
 {
	/** 
	 *	Load Plugin 
	 *
	 *	Hook into the necessary actions to load the constants and call
	 *	the includes and admin classes.
	 *
	 *	@author		Nate Jacobs
	 *	@date		4/1/13
	 *	@since		1.0
	 */
	public function __construct()
	{
		add_action('init', array( $this, 'localization' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'constants' ), 2 );
		add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );
		add_action( 'plugins_loaded', array( $this, 'admin' ), 4 );
		add_filter ( 'http_request_timeout', array ( $this, 'http_request_timeout' ) );
	}
	
	/** 
	 *	Define Constants
	 *
	 *	Define the constants used through out the plugin.
	 *
	 *	@author		Nate Jacobs
	 *	@date		4/1/13
	 *	@since		1.0
	 */
	public function constants() 
	{
		define( 'REBRICKABLE_API_VERSION', '1.2' );
		define( 'REBRICKABLE_API_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'REBRICKABLE_API_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'REBRICKABLE_API_INCLUDES', REBRICKABLE_API_DIR . trailingslashit( 'inc' ) );
		define( 'REBRICKABLE_API_ADMIN', REBRICKABLE_API_DIR . trailingslashit( 'admin' ) );
	}
	
	/** 
	 *	Load Include Classes
	 *
	 *	Load the files containing the classes in the includes folder.
	 *
	 *	@author		Nate Jacobs
	 *	@date		4/1/13
	 *	@since		1.0
	 */
	public function includes()
	{
		//require_once( REBRICKABLE_API_INCLUDES . 'class-utilities.php' );
		//require_once( REBRICKABLE_API_INCLUDES . 'class-search-functions.php' );
		//require_once( REBRICKABLE_API_INCLUDES . 'class-oembed.php' );
		//require_once( REBRICKABLE_API_INCLUDES . 'class-widgets.php' );
		//require_once( REBRICKABLE_API_INCLUDES . 'class-template-tags.php' );
		//require_once( REBRICKABLE_API_INCLUDES . 'class-shortcodes.php' );
		//require_once( REBRICKABLE_API_INCLUDES . 'class-update-functions.php' );
	}
	
	/** 
	 *	Load Admin Classes
	 *
	 *	Load the files containing the classes in the admin folder
	 *
	 *	@author		Nate Jacobs
	 *	@date		4/1/13
	 *	@since		1.0
	 */
	public function admin()
	{
		if ( is_admin() ) 
		{
			require_once( REBRICKABLE_API_ADMIN . 'class-settings-page.php' );
			//require_once( REBRICKABLE_API_ADMIN . 'class-users-profile.php' );
		}
	}
	
	/** 
	 *	Localization
	 *
	 *	Declare text domain to use in translation.
	 *
	 *	@author		Nate Jacobs
	 *	@date		4/1/13
	 *	@since		1.0
	 */
	public function localization() {
  		load_plugin_textdomain( 'rebrick_api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
	
	/** 
	*	HTTP Request Timeout
	*
	*	Sometimes requests take longer than 5 seconds
	*
	*	@author		Nate Jacobs
	*	@date		4/1/13
	*	@since		1.0
	*
	*	@param		int	$seconds
	*/
	function http_request_timeout ( $seconds ) 
	{
		return $seconds < 10 ? 15 : $seconds;
	}
 }
 $rebrick_load = new RebrickAPILoad();