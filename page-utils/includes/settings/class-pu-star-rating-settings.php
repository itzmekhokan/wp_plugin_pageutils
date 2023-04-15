<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PU_Star_Rating_Admin_Settings class.
 */
class PU_Star_Rating_Admin_Settings {

	/**
	 * FAQ settings options.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->options = get_option( 'star_rating_settings_options' );
		add_action( 'admin_init', array( $this, 'settings_page_init' ) );
	}

	/**
     * Register and add settings
     */
    public function settings_page_init() {        
        register_setting(
            'star_rating_settings_option_group', // Option group
            'star_rating_settings_options', // Option name
            array( $this, 'sanitize_settings' ) // Sanitize
        );

        add_settings_section(
            'star_rating_general_settings', // ID
            __( 'General', 'page-utils' ), // Title
            array( $this, 'star_rating_general_settings_section_info' ), // Callback
            'star_rating-settings-admin' // Page
        );  

        add_settings_field(
            'star_rating_star_color', // ID
            __( 'Star Color', 'page-utils' ), // Title 
            array( $this, 'star_rating_star_color_field_callback' ), // Callback
            'star_rating-settings-admin', // Page
            'star_rating_general_settings' // Section           
        );  
        add_settings_field(
            'star_rating_star_size', // ID
            __( 'Adjust Star Size', 'page-utils' ), // Title 
            array( $this, 'star_rating_star_size_field_callback' ), // Callback
            'star_rating-settings-admin', // Page
            'star_rating_general_settings' // Section           
        ); 
    
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_settings( $input ) {
        $new_input = array();

        if( isset( $input['star_rating_star_color'] ) )
            $new_input['star_rating_star_color'] = $input['star_rating_star_color'];
        if( isset( $input['star_rating_star_size'] ) )
            $new_input['star_rating_star_size'] = $input['star_rating_star_size'];

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function star_rating_general_settings_section_info() {
        print __( 'Enter your settings below:', 'page-utils' );
    }

    /** 
     * Get settings fields
     */
    public function star_rating_star_color_field_callback() {
        $star_rating_star_color = isset( $this->options['star_rating_star_color'] ) ? $this->options['star_rating_star_color'] : '';
        printf(
            "<input name='star_rating_settings_options[star_rating_star_color]' class='regular-text' type='text' id='star_rating_star_color' value='%s' /><p class='description'>%s</p>",
            $star_rating_star_color,
            __( 'Please enter in the Hex color. (Default: #FCAE00)', 'page-utils' )
        );
    }

    /** 
     * Get settings fields
     */
    public function star_rating_star_size_field_callback() {
        $star_rating_star_size = isset( $this->options['star_rating_star_size'] ) ? $this->options['star_rating_star_size'] : '';
        printf(
            "<label for='star_rating_star_size'><input name='star_rating_settings_options[star_rating_star_size]' type='checkbox' id='star_rating_star_size' value='enabled' %s /> %s</label><p class='description'>%s</p>",
            checked( 'enabled', $star_rating_star_size, false ),
            __( 'Fit in a perent box', 'page-utils' ),
            __( 'If there is no check, 20px font size is set.', 'page-utils' )
        );
    }

}
return new PU_Star_Rating_Admin_Settings();