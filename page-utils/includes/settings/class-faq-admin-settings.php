<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * FAQ_Admin_Settings class.
 */
class FAQ_Admin_Settings {

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
		$this->options = get_option( 'faq_settings_options' );
		add_action( 'admin_init', array( $this, 'settings_page_init' ) );
	}

	/**
     * Register and add settings
     */
    public function settings_page_init() {        
        register_setting(
            'faq_settings_option_group', // Option group
            'faq_settings_options', // Option name
            array( $this, 'sanitize_settings' ) // Sanitize
        );

        add_settings_section(
            'faq_general_settings', // ID
            __( 'General', 'page-utils' ), // Title
            array( $this, 'faq_general_settings_section_info' ), // Callback
            'faq-settings-admin' // Page
        );  

        add_settings_field(
            'custom_css', // ID
            __( 'Custom CSS', 'page-utils' ), // Title 
            array( $this, 'custom_css_field_callback' ), // Callback
            'faq-settings-admin', // Page
            'faq_general_settings' // Section           
        );  

        add_settings_field(
            'webp_supports', // ID
            __( 'Tick to enable webp support', 'page-utils' ), // Title 
            array( $this, 'webp_supports_field_callback' ), // Callback
            'faq-settings-admin', // Page
            'faq_general_settings' // Section           
        );         
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_settings( $input ) {
        $new_input = array();
        if( isset( $input['custom_css'] ) )
            $new_input['custom_css'] = $input['custom_css'];
        if( isset( $input['webp_supports'] ) )
            $new_input['webp_supports'] = $input['webp_supports'];

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function faq_general_settings_section_info() {
        print __( 'Enter your settings below:', 'page-utils' );
    }

    /** 
     * Get settings fields
     */
    public function custom_css_field_callback() {
        printf(
            '<textarea id="custom_css" name="faq_settings_options[custom_css]" rows="12" class="large-text">%s</textarea><p class="description">%s</p>',
            isset( $this->options['custom_css'] ) ? $this->options['custom_css'] : '',
            __('In this field, you can write your custom CSS code for shortcodes.', 'page-utils')
        );
        // FAQ shortcode docs
        $this->faq_shortcodes_docs_callback();
    }

    /** 
     * Get settings fields
     */
    public function webp_supports_field_callback() {
    	$webp_supports = isset( $this->options['webp_supports'] ) ? $this->options['webp_supports'] : '';
        printf(
            "<input name='faq_settings_options[webp_supports]'' type='checkbox' id='webp_supports' value='enabled' %s /><p class='description'>%s</p>",
            checked( 'enabled', $webp_supports, false ),
            __('Provide webp support to browsers that do not support webp.', 'page-utils')
        );
    }

    /** 
     * Get FAQ shortcodes documentations
     */
    public function faq_shortcodes_docs_callback() {
    	?>
    	<h4 class="title"><?php _e( 'Shortcode - [faq]', 'page-utils' ); ?></h4>
		<table class="widefat striped" style="width:auto">
			<thead>
				<tr>
					<td><?php esc_html_e( 'Attributes', 'page-utils' ); ?></td>
					<td><?php esc_html_e( 'Values', 'page-utils' ); ?></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code contenteditable>id</code></td>
					<td><?php esc_html_e( 'The id of a FAQ', 'page-utils' ); ?></td>
				</tr>
				<tr>
					<td><code contenteditable>order</code></td>
					<td><?php esc_html_e( 'FAQ ordering is ascending (ASC) or descending (DESC). Defaults to ASC', 'page-utils' ); ?></td>
				</tr>
			</tbody>
		</table>

    	<?php
    }

}
return new FAQ_Admin_Settings();