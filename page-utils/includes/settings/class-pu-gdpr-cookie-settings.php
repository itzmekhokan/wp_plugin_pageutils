<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PU_GDPR_Cookie_Admin_Settings class.
 */
class PU_GDPR_Cookie_Admin_Settings {

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
		$this->options = get_option( 'gdpr_cookie_settings_options' );
		add_action( 'admin_init', array( $this, 'settings_page_init' ) );
        //add_action( 'update_option_gdpr_cookie_settings_options', array( $this, 'gdpr_cookie_settings_options_save' ), 99, 2 );
	}

	/**
     * Register and add settings
     */
    public function settings_page_init() {        
        register_setting(
            'gdpr_cookie_settings_option_group', // Option group
            'gdpr_cookie_settings_options', // Option name
            array( $this, 'sanitize_settings' ) // Sanitize
        );

        add_settings_section(
            'gdpr_cookie_general_settings', // ID
            __( 'General', 'page-utils' ), // Title
            array( $this, '__return_false' ), // Callback
            'gdpr_cookie-settings-admin' // Page
        );  

        add_settings_field(
            'enable_gdpr_cookie', // ID
            __( 'Tick to enabled GDPR Cookie', 'page-utils' ), // Title 
            array( $this, 'enable_gdpr_cookie_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section           
        );  
        add_settings_field(
            'enable_gdpr_cookie_for_eu', // ID
            __( 'Show only for EU Visitors', 'page-utils' ), // Title 
            array( $this, 'enable_gdpr_cookie_for_eu_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section           
        );
        add_settings_field(
            'gdpr_cookie_message_heading', // ID
            __( 'Message heading', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_message_heading_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        ); 
        add_settings_field(
            'gdpr_cookie_message_heading_tag', // ID
            __( 'Message heading tag', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_message_heading_tag_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        ); 
        add_settings_field(
            'gdpr_cookie_message_body', // ID
            __( 'Message body', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_message_body_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        );
        add_settings_field(
            'gdpr_cookie_has_sticky', // ID
            __( 'Tick to enable Sticky heading', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_has_sticky_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        ); 
        add_settings_field(
            'gdpr_cookie_sticky_heading', // ID
            __( 'Sticky heading', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_sticky_heading_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        ); 
        add_settings_field(
            'gdpr_cookie_message_bar_color', // ID
            __( 'Message bar colour', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_message_bar_color_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        ); 
        add_settings_field(
            'gdpr_cookie_message_text_color', // ID
            __( 'Message text colour', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_message_text_color_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        ); 
        add_settings_field(
            'gdpr_cookie_expire_time', // ID
            __( 'Cookie expire time', 'page-utils' ), // Title 
            array( $this, 'gdpr_cookie_expire_time_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_general_settings' // Section                  
        ); 

        add_settings_section(
            'gdpr_cookie_accept_button_settings', // ID
            __( 'Accept Button', 'page-utils' ), // Title
            array( $this, '__return_false' ), // Callback
            'gdpr_cookie-settings-admin' // Page
        ); 
        add_settings_field(
            'accept_button_text', // ID
            __( 'Button text', 'page-utils' ), // Title 
            array( $this, 'accept_button_text_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_accept_button_settings' // Section                  
        ); 
        add_settings_field(
            'accept_button_bg_color', // ID
            __( 'Background colour', 'page-utils' ), // Title 
            array( $this, 'accept_button_bg_color_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_accept_button_settings' // Section                  
        ); 
        add_settings_field(
            'accept_button_text_color', // ID
            __( 'Text colour', 'page-utils' ), // Title 
            array( $this, 'accept_button_text_color_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_accept_button_settings' // Section                  
        ); 
        add_settings_field(
            'accept_button_show_as', // ID
            __( 'Show as', 'page-utils' ), // Title 
            array( $this, 'accept_button_show_as_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_accept_button_settings' // Section                  
        ); 
        add_settings_field(
            'accept_button_action', // ID
            __( 'Action', 'page-utils' ), // Title 
            array( $this, 'accept_button_action_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_accept_button_settings' // Section                  
        ); 
        add_settings_field(
            'accept_button_link_url', // ID
            __( 'URL', 'page-utils' ), // Title 
            array( $this, 'accept_button_link_url_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_accept_button_settings' // Section                  
        ); 
        add_settings_field(
            'accept_button_link_target_blank', // ID
            __( 'Tick to open link in new window', 'page-utils' ), // Title 
            array( $this, 'accept_button_link_target_blank_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_accept_button_settings' // Section                  
        ); 

        add_settings_section(
            'gdpr_cookie_read_more_link_settings', // ID
            __( 'Read More Link', 'page-utils' ), // Title
            array( $this, '__return_false' ), // Callback
            'gdpr_cookie-settings-admin' // Page
        ); 
        add_settings_field(
            'read_more_link_text', // ID
            __( 'Read More text', 'page-utils' ), // Title 
            array( $this, 'read_more_link_text_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_read_more_link_settings' // Section                  
        ); 
        add_settings_field(
            'read_more_link_bg_color', // ID
            __( 'Background colour', 'page-utils' ), // Title 
            array( $this, 'read_more_link_bg_color_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_read_more_link_settings' // Section                  
        ); 
        add_settings_field(
            'read_more_link_text_color', // ID
            __( 'Text colour', 'page-utils' ), // Title 
            array( $this, 'read_more_link_text_color_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_read_more_link_settings' // Section                  
        ); 
        add_settings_field(
            'read_more_link_show_as', // ID
            __( 'Show as', 'page-utils' ), // Title 
            array( $this, 'read_more_link_show_as_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_read_more_link_settings' // Section                  
        ); 
        add_settings_field(
            'read_more_link_url', // ID
            __( 'URL', 'page-utils' ), // Title 
            array( $this, 'read_more_link_url_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_read_more_link_settings' // Section                  
        ); 
        add_settings_field(
            'read_more_link_target_blank', // ID
            __( 'Tick to open link in new window', 'page-utils' ), // Title 
            array( $this, 'read_more_link_target_blank_field_callback' ), // Callback
            'gdpr_cookie-settings-admin', // Page
            'gdpr_cookie_read_more_link_settings' // Section                  
        ); 
    
    }

    public function __return_false(){
        echo "";
    }

    // public function gdpr_cookie_settings_options_save( $olddata, $newdata ) {
    //     print_r($olddata);die;
    // }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_settings( $input ) {
        $new_input = array();

        if( isset( $input['enable_gdpr_cookie'] ) )
            $new_input['enable_gdpr_cookie'] = $input['enable_gdpr_cookie'];
        if( isset( $input['enable_gdpr_cookie_for_eu'] ) )
            $new_input['enable_gdpr_cookie_for_eu'] = $input['enable_gdpr_cookie_for_eu'];
        if( isset( $input['gdpr_cookie_message_heading'] ) )
            $new_input['gdpr_cookie_message_heading'] = $input['gdpr_cookie_message_heading'];
        if( isset( $input['gdpr_cookie_message_heading_tag'] ) )
            $new_input['gdpr_cookie_message_heading_tag'] = $input['gdpr_cookie_message_heading_tag'];
        if( isset( $input['gdpr_cookie_message_body'] ) )
            $new_input['gdpr_cookie_message_body'] = $input['gdpr_cookie_message_body'];
        if( isset( $input['gdpr_cookie_has_sticky'] ) )
            $new_input['gdpr_cookie_has_sticky'] = $input['gdpr_cookie_has_sticky'];
        if( isset( $input['gdpr_cookie_sticky_heading'] ) )
            $new_input['gdpr_cookie_sticky_heading'] = $input['gdpr_cookie_sticky_heading'];
        if( isset( $input['gdpr_cookie_message_bar_color'] ) )
            $new_input['gdpr_cookie_message_bar_color'] = $input['gdpr_cookie_message_bar_color'];
        if( isset( $input['gdpr_cookie_message_text_color'] ) )
            $new_input['gdpr_cookie_message_text_color'] = $input['gdpr_cookie_message_text_color'];
        if( isset( $input['gdpr_cookie_expire_time'] ) )
            $new_input['gdpr_cookie_expire_time'] = $input['gdpr_cookie_expire_time'];

        if( isset( $input['accept_button_text'] ) )
            $new_input['accept_button_text'] = $input['accept_button_text'];
        if( isset( $input['accept_button_bg_color'] ) )
            $new_input['accept_button_bg_color'] = $input['accept_button_bg_color'];
        if( isset( $input['accept_button_text_color'] ) )
            $new_input['accept_button_text_color'] = $input['accept_button_text_color'];
        if( isset( $input['accept_button_show_as'] ) )
            $new_input['accept_button_show_as'] = $input['accept_button_show_as'];
        if( isset( $input['accept_button_action'] ) )
            $new_input['accept_button_action'] = $input['accept_button_action'];
        if( isset( $input['accept_button_link_url'] ) )
            $new_input['accept_button_link_url'] = $input['accept_button_link_url'];
        if( isset( $input['accept_button_link_target_blank'] ) )
            $new_input['accept_button_link_target_blank'] = $input['accept_button_link_target_blank'];

        if( isset( $input['read_more_link_text'] ) )
            $new_input['read_more_link_text'] = $input['read_more_link_text'];
        if( isset( $input['read_more_link_bg_color'] ) )
            $new_input['read_more_link_bg_color'] = $input['read_more_link_bg_color'];
        if( isset( $input['read_more_link_text_color'] ) )
            $new_input['read_more_link_text_color'] = $input['read_more_link_text_color'];
        if( isset( $input['read_more_link_show_as'] ) )
            $new_input['read_more_link_show_as'] = $input['read_more_link_show_as'];
        if( isset( $input['read_more_link_url'] ) )
            $new_input['read_more_link_url'] = $input['read_more_link_url'];
        if( isset( $input['read_more_link_target_blank'] ) )
            $new_input['read_more_link_target_blank'] = $input['read_more_link_target_blank'];
        
        // flush cache
        PU()::cache_flush();

        return $new_input;
    }

    /** 
     * Get settings fields
     */
    public function enable_gdpr_cookie_field_callback() {
        $enable_gdpr_cookie = isset( $this->options['enable_gdpr_cookie'] ) ? $this->options['enable_gdpr_cookie'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[enable_gdpr_cookie]' type='checkbox' id='enable_gdpr_cookie' value='enabled' %s /><p class='description'>%s</p>",
            checked( 'enabled', $enable_gdpr_cookie, false ),
            __('Allow you to add GDPR cookie message for your site', 'page-utils')
        );
    }

    /** 
     * Get settings fields
     */
    public function enable_gdpr_cookie_for_eu_field_callback() {
        $enable_gdpr_cookie_for_eu = isset( $this->options['enable_gdpr_cookie_for_eu'] ) ? $this->options['enable_gdpr_cookie_for_eu'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[enable_gdpr_cookie_for_eu]' type='checkbox' id='enable_gdpr_cookie_for_eu' value='enabled' %s /><p class='description'>%s</p>",
            checked( 'enabled', $enable_gdpr_cookie_for_eu, false ),
            __('Allow you to enabled GDPR cookie consent only for European Union (EU) visitors', 'page-utils')
        );
    }

    /** 
     * Get settings fields
     */
    public function gdpr_cookie_message_heading_field_callback() {
        $gdpr_cookie_message_heading = isset( $this->options['gdpr_cookie_message_heading'] ) ? $this->options['gdpr_cookie_message_heading'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[gdpr_cookie_message_heading]' class='regular-text' type='text' id='gdpr_cookie_message_heading' value='%s' /><p class='description'>%s</p>",
            $gdpr_cookie_message_heading,
            __( 'Leave it blank, If you do not need a heading', 'page-utils' )
        );
    }

    /** 
     * Get settings fields
     */
    public function gdpr_cookie_message_heading_tag_field_callback() {
        $gdpr_cookie_message_heading_tag = isset( $this->options['gdpr_cookie_message_heading_tag'] ) ? $this->options['gdpr_cookie_message_heading_tag'] : '';
        echo '<select name="gdpr_cookie_settings_options[gdpr_cookie_message_heading_tag]" id="gdpr_cookie_message_heading_tag" class="gdpr_message_heading_tag">';
        echo '<option value="h4" '.selected( 'h4', $gdpr_cookie_message_heading_tag, false ) .'>'. __( 'h4', 'page-utils' ).'</option>';
        echo '<option value="h5" '.selected( 'h5', $gdpr_cookie_message_heading_tag, false ) .'>'. __( 'h5', 'page-utils' ).'</option>';
        echo '<option value="h6" '.selected( 'h6', $gdpr_cookie_message_heading_tag, false ) .'>'. __( 'h6', 'page-utils' ).'</option>';
        echo '<option value="p" '.selected( 'p', $gdpr_cookie_message_heading_tag, false ) .'>'. __( 'p', 'page-utils' ).'</option>';
        echo '</select>';
        printf(
            "<p class='description'>%s</p>",
            __( 'Choose GDPR cookie message heading tag', 'page-utils' )
        );
    }


    /** 
     * Get settings fields
     */
    public function gdpr_cookie_message_body_field_callback() {
        printf(
            '<textarea id="custom_css" name="gdpr_cookie_settings_options[gdpr_cookie_message_body]" rows="12" class="large-text">%s</textarea><p class="description">%s</p>',
            isset( $this->options['gdpr_cookie_message_body'] ) ? $this->options['gdpr_cookie_message_body'] : '',
            __('In this field, you can write your custom CSS code for shortcodes.', 'page-utils')
        );
    }

    /** 
     * Get settings fields
     */
    public function gdpr_cookie_has_sticky_field_callback() {
        $gdpr_cookie_has_sticky = isset( $this->options['gdpr_cookie_has_sticky'] ) ? $this->options['gdpr_cookie_has_sticky'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[gdpr_cookie_has_sticky]' type='checkbox' id='gdpr_cookie_has_sticky' value='enabled' %s /><p class='description'>%s</p>",
            checked( 'enabled', $gdpr_cookie_has_sticky, false ),
            __('Allow you to add GDPR cookie sticky heading at bottom right of your site for GDPR cookie consent', 'page-utils')
        );
    }

    /** 
     * Get settings fields
     */
    public function gdpr_cookie_sticky_heading_field_callback() {
        $gdpr_cookie_sticky_heading = isset( $this->options['gdpr_cookie_sticky_heading'] ) ? $this->options['gdpr_cookie_sticky_heading'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[gdpr_cookie_sticky_heading]' class='regular-text' type='text' id='gdpr_cookie_sticky_heading' value='%s' /><p class='description'>%s</p>",
            $gdpr_cookie_sticky_heading,
            __( 'Add your sticky GDPR heading that open message bar.', 'page-utils' )
        );
    }

    /** 
     * Get settings fields
     */
    public function gdpr_cookie_message_bar_color_field_callback() {
        $gdpr_cookie_message_bar_color = isset( $this->options['gdpr_cookie_message_bar_color'] ) ? $this->options['gdpr_cookie_message_bar_color'] : '';
        printf(
            "<input type='text' name='gdpr_cookie_settings_options[gdpr_cookie_message_bar_color]' id='cli-colour-background' value='%s' class='pu-color-picker' data-default-color='#fff' />",
            $gdpr_cookie_message_bar_color
        );
    }

    /** 
     * Get settings fields
     */
    public function gdpr_cookie_message_text_color_field_callback() {
        $gdpr_cookie_message_text_color = isset( $this->options['gdpr_cookie_message_text_color'] ) ? $this->options['gdpr_cookie_message_text_color'] : '';
        printf(
            "<input type='text' name='gdpr_cookie_settings_options[gdpr_cookie_message_text_color]' id='cli-colour-background' value='%s' class='pu-color-picker' data-default-color='#000' />",
            $gdpr_cookie_message_text_color
        );
    }

    /** 
     * Get settings fields
     */
    public function gdpr_cookie_expire_time_field_callback() {
        $gdpr_cookie_expire_time = isset( $this->options['gdpr_cookie_expire_time'] ) ? $this->options['gdpr_cookie_expire_time'] : '';
        $expire_times = array(
            (365 * 24 * 60 * 60 * 1000) => __( '1 year', 'page-utils' ),
            (180 * 24 * 60 * 60 * 1000) => __( '6 months', 'page-utils' ),
            (60 * 24 * 60 * 60 * 1000) => __( '2 months', 'page-utils' ),
            (30 * 24 * 60 * 60 * 1000) => __( '1 month', 'page-utils' ),
            (15 * 24 * 60 * 60 * 1000) => __( '15 days', 'page-utils' ),
            (24 * 60 * 60 * 1000) => __( '1 day', 'page-utils' ),
            (12 * 60 * 60 * 1000) => __( '12 hours', 'page-utils' ),
            (1 * 60 * 60 * 1000) => __( '1 hour', 'page-utils' ),
            (30 * 60 * 1000) => __( '30 minutes', 'page-utils' ),
            (5 * 60 * 1000) => __( '5 minutes', 'page-utils' ),
        );
        echo '<select name="gdpr_cookie_settings_options[gdpr_cookie_expire_time]">';
        foreach ($expire_times as $key => $label) {
            echo '<option value="'.$key.'" '.selected( $key, $gdpr_cookie_expire_time, false ) .'>'. $label.'</option>';
        }
        echo '</select>';
    }

    /** 
     * Get settings fields
     */
    public function accept_button_text_field_callback() {
        $accept_button_text = isset( $this->options['accept_button_text'] ) ? $this->options['accept_button_text'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[accept_button_text]' class='regular-text' type='text' id='accept_button_text' value='%s' /><p class='description'>%s</p>",
            $accept_button_text,
            __( 'Add your Accept button text.', 'page-utils' )
        );
    }

    /** 
     * Get settings fields
     */
    public function accept_button_bg_color_field_callback() {
        $accept_button_bg_color = isset( $this->options['accept_button_bg_color'] ) ? $this->options['accept_button_bg_color'] : '';
        printf(
            "<input type='text' name='gdpr_cookie_settings_options[accept_button_bg_color]' id='accept_button_bg_color' value='%s' class='pu-color-picker' data-default-color='#fff' />",
            $accept_button_bg_color
        );
    }

    /** 
     * Get settings fields
     */
    public function accept_button_text_color_field_callback() {
        $accept_button_text_color = isset( $this->options['accept_button_text_color'] ) ? $this->options['accept_button_text_color'] : '';
        printf(
            "<input type='text' name='gdpr_cookie_settings_options[accept_button_text_color]' id='accept_button_text_color' value='%s' class='pu-color-picker' data-default-color='#000' />",
            $accept_button_text_color
        );
    }

    /** 
     * Get settings fields
     */
    public function accept_button_show_as_field_callback() {
        $accept_button_show_as = isset( $this->options['accept_button_show_as'] ) ? $this->options['accept_button_show_as'] : '';
        echo '<select name="gdpr_cookie_settings_options[accept_button_show_as]">';
        echo '<option value="true" '.selected( 'true', $accept_button_show_as, false ) .'>'. __( 'Button', 'page-utils' ).'</option>';
        echo '<option value="false" '.selected( 'false', $accept_button_show_as, false ) .'>'. __( 'Link', 'page-utils' ).'</option>';
        echo '</select>';
    }

    /** 
     * Get settings fields
     */
    public function accept_button_action_field_callback() {
        $accept_button_action = isset( $this->options['accept_button_action'] ) ? $this->options['accept_button_action'] : '';
        echo '<select name="gdpr_cookie_settings_options[accept_button_action]" id="accept_button_action">';
        echo '<option value="close_header" '.selected( 'close_header', $accept_button_action, false ) .'>'. __( 'Close Header', 'page-utils' ).'</option>';
        echo '<option value="open_url" '.selected( 'open_url', $accept_button_action, false ) .'>'. __( 'Open URL', 'page-utils' ).'</option>';
        echo '</select>';
    }

    /** 
     * Get settings fields
     */
    public function accept_button_link_url_field_callback() {
        $accept_button_link_url = isset( $this->options['accept_button_link_url'] ) ? $this->options['accept_button_link_url'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[accept_button_link_url]' class='regular-text accept-btn-link' type='text' id='accept_button_link_url' value='%s' /><p class='description'>%s</p>",
            $accept_button_link_url,
            __( 'Add your Accept button link URL.', 'page-utils' )
        );
    }

    /** 
     * Get settings fields
     */
    public function accept_button_link_target_blank_field_callback() {
        $accept_button_link_target_blank = isset( $this->options['accept_button_link_target_blank'] ) ? $this->options['accept_button_link_target_blank'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[accept_button_link_target_blank]' type='checkbox' id='accept_button_link_target_blank' class='accept-btn-link' value='enabled' %s /><p class='description'>%s</p>",
            checked( 'enabled', $accept_button_link_target_blank, false ),
            __('Allow you to open accept link in new window.', 'page-utils')
        );
    }

    /** 
     * Get settings fields
     */
    public function read_more_link_text_field_callback() {
        $read_more_link_text = isset( $this->options['read_more_link_text'] ) ? $this->options['read_more_link_text'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[read_more_link_text]' class='regular-text' type='text' id='read_more_link_text' value='%s' /><p class='description'>%s</p>",
            $read_more_link_text,
            __( 'Add your Read more text.', 'page-utils' )
        );
    }

    /** 
     * Get settings fields
     */
    public function read_more_link_bg_color_field_callback() {
        $read_more_link_bg_color = isset( $this->options['read_more_link_bg_color'] ) ? $this->options['read_more_link_bg_color'] : '';
        printf(
            "<input type='text' name='gdpr_cookie_settings_options[read_more_link_bg_color]' id='read_more_link_bg_color' value='%s' class='pu-color-picker' data-default-color='#fff' />",
            $read_more_link_bg_color
        );
    }

    /** 
     * Get settings fields
     */
    public function read_more_link_text_color_field_callback() {
        $read_more_link_text_color = isset( $this->options['read_more_link_text_color'] ) ? $this->options['read_more_link_text_color'] : '';
        printf(
            "<input type='text' name='gdpr_cookie_settings_options[read_more_link_text_color]' id='read_more_link_text_color' value='%s' class='pu-color-picker' data-default-color='#000' />",
            $read_more_link_text_color
        );
    }

    /** 
     * Get settings fields
     */
    public function read_more_link_show_as_field_callback() {
        $read_more_link_show_as = isset( $this->options['read_more_link_show_as'] ) ? $this->options['read_more_link_show_as'] : '';
        echo '<select name="gdpr_cookie_settings_options[read_more_link_show_as]">';
        echo '<option value="true" '.selected( 'true', $read_more_link_show_as, false ) .'>'. __( 'Button', 'page-utils' ).'</option>';
        echo '<option value="false" '.selected( 'false', $read_more_link_show_as, false ) .'>'. __( 'Link', 'page-utils' ).'</option>';
        echo '</select>';
    }

    /** 
     * Get settings fields
     */
    public function read_more_link_url_field_callback() {
        $read_more_link_url = isset( $this->options['read_more_link_url'] ) ? $this->options['read_more_link_url'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[read_more_link_url]' class='regular-text read-more-btn-link' type='text' id='read_more_link_url' value='%s' /><p class='description'>%s</p>",
            $read_more_link_url,
            __( 'Add your read more link URL.', 'page-utils' )
        );
    }

    /** 
     * Get settings fields
     */
    public function read_more_link_target_blank_field_callback() {
        $read_more_link_target_blank = isset( $this->options['read_more_link_target_blank'] ) ? $this->options['read_more_link_target_blank'] : '';
        printf(
            "<input name='gdpr_cookie_settings_options[read_more_link_target_blank]' type='checkbox' id='read_more_link_target_blank' class='read-more-btn-link' value='enabled' %s /><p class='description'>%s</p>",
            checked( 'enabled', $read_more_link_target_blank, false ),
            __('Allow you to open read more link in new window.', 'page-utils')
        );
    }

}
return new PU_GDPR_Cookie_Admin_Settings();