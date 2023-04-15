<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get FAQ settings
 *
 */
function pu_get_settings( $key = '', $mod = '' ) {
	$settings = ( $mod ) ? get_option( "{$mod}_settings_options" ) : get_option( 'pu_settings_options' );
	if( $key )
		return ( isset( $settings[$key] ) ) ? $settings[$key] : false;
	else
		return $settings;
}

/**
 * Get Hreflang_Tag settings
 *
 */
function hreflang_tags_get_settings( $key = '' ) {
	$settings = get_option( 'hreflang_tags_settings_options' );
	if( $key )
		return ( isset( $settings[$key] ) ) ? $settings[$key] : false;
	else
		return $settings;
}

/**
 * Get FAQ messages
 *
 */
function faq_get_message() {
	$messages = array();
	// 0 = unused. Messages start at index 1.
	$messages['updated']= array(
		0 => '',
		1 => __( 'FAQ added.', 'page-utils' ),
		2 => __( 'FAQ updated.', 'page-utils' ),
		3 => __( 'FAQ deleted.', 'page-utils' ),
		4 => __( 'QNA added.', 'page-utils' ),
		5 => __( 'QNA updated.', 'page-utils' ),
		6 => __( 'QNA deleted.', 'page-utils' ),
	);
	$messages['error']= array(
		0 => '',
		1 => __( 'FAQ not added.', 'page-utils' ),
		2 => __( 'FAQ not updated.', 'page-utils' ),
		3 => __( 'FAQ deleted.', 'page-utils' ),
		4 => __( 'QNA not added.', 'page-utils' ),
		5 => __( 'QNA not updated.', 'page-utils' ),
		6 => __( 'QNA deleted.', 'page-utils' ),
	);
	$messages = apply_filters( 'faq_get_messages', $messages );
	$message = false;
	if ( isset( $_REQUEST['message'] ) && ( $msg = (int) $_REQUEST['message'] ) ) {
		$status = ( isset( $_REQUEST['error'] ) ) ? 'error' : 'updated';
		if ( isset( $messages[ $status ][ $msg ] ) ) {
			$message = $messages[ $status ][ $msg ];
		}
	}
	return $message;
}

/**
 * Get output of FAQ templates.
 *
 */
function get_pu_template_html( $template_name, $atts = array() ) {
	
	$html = '';
	ob_start();
	include PU_ABSPATH . "/templates/{$template_name}.php";
	$html = ob_get_clean();
	
	return apply_filters( 'get_faq_template_html', $html, $atts );
}

/**
 * Get all supported lang base URL
 *
 */
function get_all_supported_lang_base_url( $lang = '' ) {
	$settings = hreflang_tags_get_settings();
	$supported_lang_base_url = array();
	$supported_lang_site_url = $settings['supported_lang_site_url'];
    $supported_lang = $settings['supported_lang'];
    if( $supported_lang_site_url || $supported_lang ){
    	for( $i = 0; $i < count( $supported_lang_site_url ); $i++) {
			if( !empty( $supported_lang_site_url[$i] ) || !empty( $supported_lang[$i] ) ) { 
	            $selected_site_url = ( isset($supported_lang_site_url[$i]) && !empty($supported_lang_site_url[$i]) ) ? $supported_lang_site_url[$i] : '';
	            $selected_lang = ( isset($supported_lang[$i]) && !empty($supported_lang[$i]) ) ? $supported_lang[$i] : '';
	            $supported_lang_base_url[$selected_lang] = trailingslashit($selected_site_url);
	        }
		}
    }else{
    	$site_lang = 'x-default';
    	$supported_lang_base_url[$site_lang] = trailingslashit(site_url());
    }

    if( $lang ) {
    	return isset($supported_lang_base_url[$lang]) ? $supported_lang_base_url[$lang] : '';
    }else{
    	return $supported_lang_base_url;
    }
	
}

/**
 * Get output of FAQ templates.
 *
 */
function check_has_default_hreflang_site_url() {
	$supported_lang_site_url = hreflang_tags_get_settings('supported_lang_site_url');
	$has_default_url = false;
	if( $supported_lang_site_url ){
		foreach ($supported_lang_site_url as $key => $url) {
			if( $url ) $has_default_url = true;
		}
	}
	return $has_default_url;
}

/**
 * Get available wordpress translations.
 *
 */
function pu_get_available_translations() {

	require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
	$translations = wp_get_available_translations();

	return $translations;
}

/**
 * Get language ISO locale translations.
 *
 */
function pu_get_lang_locale( $locale = '' ) {
	$lang_locales = hreflang_tags_get_settings('supported_lang_code');
	if( $locale )
		return ( isset( $lang_locales[$locale] ) ) ? $lang_locales[$locale] : false;
	else
		return $lang_locales;
}

/**
 * Check given country code is European Union (EU) countries or not.
 *
 */
function isEUCountry( $countrycode ){
    $eu_countrycodes = array(
    	'AT', 
    	'BE', 
    	'BG', 
    	'HR', 
    	'CY', 
    	'CZ', 
    	'DE', 
    	'DK', 
    	'EE', 
    	'EL',
    	'ES', 
    	'FI', 
    	'FR', 
    	'GB', 
    	'HU', 
    	'IE', 
    	'IT', 
    	'LT', 
    	'LU', 
    	'LV',
    	'MT', 
    	'NL', 
    	'PL', 
    	'PT', 
    	'RO', 
    	'SE', 
    	'SI', 
    	'SK',
    );
    return( in_array( $countrycode, $eu_countrycodes ) );
}	