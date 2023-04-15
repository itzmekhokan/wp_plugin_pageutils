<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Hreflang_Tags_Admin_Settings class.
 */
class Hreflang_Tags_Admin_Settings {

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
		$this->options = get_option( 'hreflang_tags_settings_options' );
		add_action( 'admin_init', array( $this, 'settings_page_init' ) );
        add_action( 'after_hreflang_tags_settings_form', array( $this, 'after_hreflang_tags_settings_form' ) );
	}

	/**
     * Register and add settings
     */
    public function settings_page_init() {        
        register_setting(
            'hreflang_tags_settings_option_group', // Option group
            'hreflang_tags_settings_options', // Option name
            array( $this, 'sanitize_settings' ) // Sanitize
        );

        add_settings_section(
            'hreflang_tags_general_settings', // ID
            __( 'General', 'page-utils' ), // Title
            array( $this, 'hreflang_tags_general_settings_section_info' ), // Callback
            'hreflang_tags-settings-admin' // Page
        );  

        add_settings_field(
            'hreflang_tags_content_types', // ID
            __( 'Content Types', 'page-utils' ), // Title 
            array( $this, 'hreflang_tags_content_types_field_callback' ), // Callback
            'hreflang_tags-settings-admin', // Page
            'hreflang_tags_general_settings' // Section           
        );  

        add_settings_field(
            'supported_base_url_langs', // ID
            __( 'Add site base url in available lang', 'page-utils' ), // Title 
            array( $this, 'supported_base_url_langs_field_callback' ), // Callback
            'hreflang_tags-settings-admin', // Page
            'hreflang_tags_general_settings' // Section           
        );  

        add_settings_field(
            'enable_x_default', // ID
            __( 'Enable X-default lang', 'page-utils' ), // Title 
            array( $this, 'enable_x_default_field_callback' ), // Callback
            'hreflang_tags-settings-admin', // Page
            'hreflang_tags_general_settings' // Section           
        );         
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_settings( $input ) {
        $new_input = array();

        if( isset( $input['hreflang_tags_content_types'] ) )
            $new_input['hreflang_tags_content_types'] = $input['hreflang_tags_content_types'];
        if( isset( $input['supported_lang_site_url'] ) )
            $new_input['supported_lang_site_url'] = $input['supported_lang_site_url'];
        if( isset( $input['supported_lang'] ) )
            $new_input['supported_lang'] = $input['supported_lang'];
        if( isset( $input['enable_x_default'] ) )
            $new_input['enable_x_default'] = $input['enable_x_default'];

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function hreflang_tags_general_settings_section_info() {
        print __( 'Enter your settings below:', 'page-utils' );
    }

    /** 
     * Get settings fields
     */
    public function hreflang_tags_content_types_field_callback() {
        $post_types = get_post_types( array( 'public' => true, 'exclude_from_search' => false ),'objects');
        $hreflang_tags_content_post_types = isset( $this->options['hreflang_tags_content_types']['post_type'] ) ? $this->options['hreflang_tags_content_types']['post_type'] : array();
        $hreflang_tags_content_taxonomy_types = isset( $this->options['hreflang_tags_content_types']['taxonomy'] ) ? $this->options['hreflang_tags_content_types']['taxonomy'] : array();
        if ( is_array( $post_types ) && $post_types !== array() ) {
            foreach ( $post_types as $post_type ) {
                echo '<input type="checkbox" name="hreflang_tags_settings_options[hreflang_tags_content_types][post_type][]" id="hreflang_tags_content_types_'.$post_type->name.'" value="'.$post_type->name.'"';
                if (is_array($hreflang_tags_content_post_types)) { 
                    if (in_array($post_type->name, $hreflang_tags_content_post_types)) {
                        echo ' checked="checked"';
                    }
                } 
                echo '/>
                <label for="hreflang_tags_content_types_'.$post_type->name.'">'.$post_type->label.'</label>
                <br>';
            }
        }
        $taxonomies = get_taxonomies( array( 'public' => true),'objects');
        if (is_array($taxonomies) && $taxonomies !== array()) {
            foreach ($taxonomies as $taxonomy) {
                echo '<input type="checkbox" name="hreflang_tags_settings_options[hreflang_tags_content_types][taxonomy][]" id="hreflang_tags_content_types_'.$taxonomy->name.'" value="'.$taxonomy->name.'"';
                if (is_array($hreflang_tags_content_taxonomy_types)) { 
                    if (in_array($taxonomy->name, $hreflang_tags_content_taxonomy_types)) {
                        echo ' checked="checked"';
                    }
                } 
                echo '/>
                <label for="hreflang_tags_content_types_'.$taxonomy->name.'">'.$taxonomy->label.'</label>
                <br>';
            }
        }
        printf(
            '<p class="description">%s</p>',
            __('Add supports for hreflang tags in above content types', 'page-utils')
        );

    }

    /** 
     * Get settings fields
     */
    public function enable_x_default_field_callback() {
        $enable_x_default = isset( $this->options['enable_x_default'] ) ? $this->options['enable_x_default'] : '';
        printf(
            "<input name='hreflang_tags_settings_options[enable_x_default]'' type='checkbox' id='enable_x_default' value='enabled' %s />",
            checked( 'enabled', $enable_x_default, false )
        );
    }

    /** 
     * Get settings fields
     */
    public function supported_base_url_langs_field_callback() {
        $supported_lang_site_url = ( isset($this->options['supported_lang_site_url']) && !empty($this->options['supported_lang_site_url']) ) ? $this->options['supported_lang_site_url'] : array();
        $supported_lang = ( isset($this->options['supported_lang']) && !empty($this->options['supported_lang']) ) ? $this->options['supported_lang'] : array();
        if( $supported_lang_site_url || $supported_lang ) { 
            for( $i = 0; $i < count( $supported_lang_site_url ); $i++) {
                if( !empty( $supported_lang_site_url[$i] ) || !empty( $supported_lang[$i] ) ) { 
                    $selected_site_url = ( isset($supported_lang_site_url[$i]) && !empty($supported_lang_site_url[$i]) ) ? $supported_lang_site_url[$i] : '';
                    $selected_lang = ( isset($supported_lang[$i]) && !empty($supported_lang[$i]) ) ? $supported_lang[$i] : '';
                ?>
                <p class="supported-base-langs-field_wrapper">
                    <label for="supported_lang_site_url">  
                        <input type="text" class="regular-text" name="hreflang_tags_settings_options[supported_lang_site_url][]" placeholder="<?php _e('Site URL', 'page-utils'); ?>: <?php echo site_url(); ?>" value="<?php echo $selected_site_url; ?>" />
                    </label>
                    <label for="supported_lang"> 
                        <select name="hreflang_tags_settings_options[supported_lang][]">
                            <option value=""><?php _e('Select your language', 'page-utils'); ?></option>
                            <?php 
                            $translations = wp_get_available_translations();
                            foreach ( $translations as $translation ) {
                                echo sprintf(
                                    '<option value="%s" lang="%s"%s>%s</option>',
                                    esc_attr( $translation['language'] ),
                                    esc_attr( current( $translation['iso'] ) ),
                                    selected( $translation['language'], $selected_lang, false ),
                                    esc_html( $translation['english_name'] . ' - '. $translation['native_name'] )
                                );
                            }
                            ?>
                        </select>
                    </label>
                    <a href="javascript:void(0);" class="button remove_langs_support" title="<?php _e('Remove', 'page-utils'); ?>"><?php _e('Remove', 'page-utils'); ?></a>
                </p>            
                <?php 
                }
            }
        ?>
        <p class="add-supported-lang-btn_wrapper">
            <a href="javascript:void(0);" class="button add_more_langs_support" title="<?php _e('Add more', 'page-utils'); ?>"><?php _e('Add more', 'page-utils'); ?></a>
        </p>
        <?php } else{ ?>
        <p class="supported-base-langs-field_wrapper">
            <label for="supported_lang_site_url">  
                <input type="text" class="regular-text" name="hreflang_tags_settings_options[supported_lang_site_url][]" placeholder="<?php _e('Site URL', 'page-utils'); ?>: <?php echo site_url(); ?>" />
            </label>
            <label for="supported_lang">  
                <select name="hreflang_tags_settings_options[supported_lang][]">
                    <option value=""><?php _e('Select your language', 'page-utils'); ?></option>
                    <?php 
                    $translations = wp_get_available_translations();
                    foreach ( $translations as $translation ) {
                        echo sprintf(
                            '<option value="%s" lang="%s"%s>%s</option>',
                            esc_attr( $translation['language'] ),
                            esc_attr( current( $translation['iso'] ) ),
                            selected( $translation['language'], '', false ),
                            esc_html( $translation['english_name'] . ' - '. $translation['native_name'] )
                        );
                    }
                    ?>
                </select>
            </label>
        </p>
        <p class="add-supported-lang-btn_wrapper">
            <a href="javascript:void(0);" class="button add_more_langs_support" title="<?php _e('Add more', 'page-utils'); ?>"><?php _e('Add more', 'page-utils'); ?></a>
        </p>

        <?php
        }
    }

    /** 
     * Get hidden settings fields
     */
    public function after_hreflang_tags_settings_form() {
    	?>
        <!-- copy of input fields group -->
        <p class="supported-base-langs-field_wrapperCopy" style="display: none;">
            <label for="supported_lang_site_url">  
                <input type="text" class="regular-text" name="hreflang_tags_settings_options[supported_lang_site_url][]" placeholder="<?php _e('Site URL', 'page-utils'); ?>: <?php echo site_url(); ?>" />
            </label>
            <label for="supported_lang">
                <select name="hreflang_tags_settings_options[supported_lang][]">
                    <option value=""><?php _e('Select your language', 'page-utils'); ?></option>
                    <?php 
                    $translations = wp_get_available_translations();
                    foreach ( $translations as $translation ) {
                        echo sprintf(
                            '<option value="%s" lang="%s">%s</option>',
                            esc_attr( $translation['language'] ),
                            esc_attr( current( $translation['iso'] ) ),
                            esc_html( $translation['english_name'] . ' - '. $translation['native_name'] )
                        );
                    }
                    ?>
                </select>
            </label>
            <a href="javascript:void(0);" class="button remove_langs_support" title="<?php _e('Remove', 'page-utils'); ?>"><?php _e('Remove', 'page-utils'); ?></a>
        </p>
        <?php
    }


}
return new Hreflang_Tags_Admin_Settings();