<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PU_Integrations_GDPR_Cookie class.
 */
class PU_Integrations_GDPR_Cookie {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 99 );
		add_action( 'wp_footer', array( $this, 'add_gdpr_cookie_scripts' ) );
		add_filter( 'pu_enabled_gdpr_cookie', array( $this, 'pu_enabled_gdpr_cookie' ), 10, 2 );
	}

	public function pu_enabled_gdpr_cookie( $enabled, $settings ) {
		$only_for_EU = ( isset( $settings['enable_gdpr_cookie_for_eu'] ) && $settings['enable_gdpr_cookie_for_eu'] == 'enabled' ) ? true : false;
		if( $only_for_EU ) {
			$visitor_geolocate_ip = PU()::geolocate_ip();
			$visitor_country_code = isset( $visitor_geolocate_ip['country'] ) ? $visitor_geolocate_ip['country'] : '';
			$enabled = ( $visitor_country_code && isEUCountry( $visitor_country_code ) ) ? true : false;
		}
		return $enabled;
	}

	public function wp_enqueue_scripts() {
		$settings = pu_get_settings( '', 'gdpr_cookie' );
		$enabled_gdpr_cookie = apply_filters( 'pu_enabled_gdpr_cookie', isset($settings['enable_gdpr_cookie']) ? true : false, $settings );
		if( $enabled_gdpr_cookie ){
			wp_enqueue_script( 'gdpr_cookie_js', PU()->plugin_url() . '/assets/js/gdpr-cookie.js', array( 'jquery' ), PU_VERSION, true );
			
			$script_settings = array(
			    'animate_speed_hide'      	=> 500,
			    'animate_speed_show'      	=> 500,
			    'cookie_expire_time'		=> ( isset($settings['gdpr_cookie_expire_time']) ) ? $settings['gdpr_cookie_expire_time'] : (30 * 24 * 60 * 60 * 1000),
			    'background'          		=> $settings['gdpr_cookie_message_bar_color'],
			    'border'            		=> '#b1a6a6c2',
			    'border_on'           		=> false, //$settings['border_on'],
			    'button_1_button_colour'    => $settings['accept_button_bg_color'],
			    'button_1_button_hover'     => $settings['accept_button_bg_color'],
			    'button_1_link_colour'      => $settings['accept_button_text_color'],
			    'button_1_as_button'      	=> ( isset($settings['accept_button_show_as']) && $settings['accept_button_show_as'] === 'true' ) ? 1 : 0,
			    'button_1_new_win'      	=> ( isset($settings['accept_button_link_target_blank']) && $settings['accept_button_link_target_blank'] == 'enabled' ) ? 1 : 0,
			    'button_2_button_colour'    => ( isset($settings['read_more_link_bg_color']) && !empty($settings['read_more_link_bg_color']) ) ? $settings['read_more_link_bg_color'] : "#333",
			    'button_2_button_hover'     => ( isset($settings['read_more_link_bg_color']) && !empty($settings['read_more_link_bg_color']) ) ? $settings['read_more_link_bg_color'] : "#333",
			    'button_2_link_colour'      => ( isset($settings['read_more_link_text_color']) && !empty($settings['read_more_link_text_color']) ) ? $settings['read_more_link_text_color'] : "#444",
			    'button_2_as_button'      	=> ( isset($settings['read_more_link_show_as']) && $settings['read_more_link_show_as'] === 'true' ) ? 1 : 0,
			    'button_2_hidebar'		 	=> '',
			    'font_family'         		=> 'inherit',
			    'header_fix'                => '',
			    'notify_animate_hide'     	=> 1,
			    'notify_animate_show'     	=> 1,
			    'notify_div_id'         	=> '#cookie-law-info-bar',
			    'notify_position_horizontal'=> 'right',
			    'notify_position_vertical'  => 'bottom',
			    'scroll_close'              => '',
			    'scroll_close_reload'       => '',
			    'accept_close_reload'       => '',
			    'reject_close_reload'       => '',
			    'enabled_sticky_bar'		=> ( isset($settings['gdpr_cookie_has_sticky']) && $settings['gdpr_cookie_has_sticky'] === 'enabled' ) ? 1 : 0,
			    'showagain_tab'         	=> apply_filters( 'pu_gdpr_cookie_show_always_sticky_bar', false ),
			    'showagain_background'      => '#fff',
			    'showagain_border'        	=> '#000',
			    'showagain_div_id'        	=> '#cookie-law-info-again',
			    'showagain_x_position'      => '100px',
			    'text'              		=> '#000',
			    'show_once_yn'          	=> '',
			    'show_once'           		=> '10000',
			    'logging_on'				=> '',
			    'as_popup'					=> '',
			    'popup_overlay'				=> 1,
			    'bar_heading_text'			=> $settings['gdpr_cookie_message_heading'],
			    'cookie_bar_as'				=> 'banner',
				'popup_showagain_position'	=> 'bottom-right',
				'widget_position'			=> 'left',
				'delete_cookie'				=> ( isset($_GET['pu_gdpr_cookie']) && $_GET['pu_gdpr_cookie'] == 'delete' ) ? true : false,
		  	);
			wp_localize_script( 'gdpr_cookie_js', 'gdpr_script_data', array('settings' => $script_settings) );
		}
	}

	public function add_gdpr_cookie_scripts() {
		$settings = pu_get_settings( '', 'gdpr_cookie' );
		$enabled_gdpr_cookie = apply_filters( 'pu_enabled_gdpr_cookie', isset($settings['enable_gdpr_cookie']) ? true : false, $settings );
		if( $enabled_gdpr_cookie ){
			// Output the HTML in the footer:
			$notify_message = $settings['gdpr_cookie_message_body'];
			$bar_heading_text = $settings['gdpr_cookie_message_heading'];
			$show_again = $settings['gdpr_cookie_sticky_heading'];

			$message =nl2br($notify_message);
	    	$str = stripslashes( $message );
	        $head = $bar_heading_text;
	        $head = trim(stripslashes($head)); 
	        $msg_heading_tag = ( $settings['gdpr_cookie_message_heading_tag'] ) ? $settings['gdpr_cookie_message_heading_tag'] : 'h4';
	        $show_as = ( isset($settings['accept_button_show_as']) && $settings['accept_button_show_as'] === 'true' ) ? 'button' : '';    
	        $accept_open_url = ( $settings['accept_button_action'] && $settings['accept_button_action'] == 'open_url' ) ? true : false;
	        $accept_btn_attr = '';
	        if( $accept_open_url ){
	        	$accept_btn_attr .= (isset($settings['accept_button_link_url']) && !empty($settings['accept_button_link_url']) ) ? 'href="'.$settings['accept_button_link_url'].'"' : ''; 
	        	$accept_btn_attr .= (isset($settings['accept_button_link_target_blank']) && $settings['accept_button_link_target_blank'] == 'enabled' ) ? ' target="_blank"' : ' target="_self"'; 
	        }
	        $accept_btn_attr .= ' style="';
	        $accept_btn_attr .= (isset($settings['accept_button_text_color']) ) ? 'color: '.$settings['accept_button_text_color'].';' : ''; 
	        $accept_btn_attr .= (isset($settings['accept_button_bg_color']) && $show_as === 'button' ) ? 'background-color: '.$settings['accept_button_bg_color'].';' : '';
	        $accept_btn_attr .= '"';
	        $accept_btn = ( $settings['accept_button_text'] ) ? '<a class="pu-gdpr-cookie-btn cli_action_button '.$show_as.' cli-accept-button medium cli-plugin-button" data-cli_action="accept" '.$accept_btn_attr.' >' . stripslashes($settings['accept_button_text']) . '</a>' : '';
	        $show_as = ( isset($settings['read_more_link_show_as']) && $settings['read_more_link_show_as'] === 'true' ) ? 'button' : '';
	        $readmore_btn_attr = ''; 
        	$readmore_btn_attr .= (isset($settings['read_more_link_target_blank']) && $settings['read_more_link_target_blank'] == 'enabled' ) ? ' target="_blank"' : ' target="_self"'; 
	        $readmore_btn_attr .= ' style="';
	        $readmore_btn_attr .= (isset($settings['read_more_link_text_color']) ) ? 'color: '.$settings['read_more_link_text_color'].';' : ''; 
	        $readmore_btn_attr .= (isset($settings['read_more_link_bg_color']) && $show_as === 'button' ) ? 'background-color: '.$settings['read_more_link_bg_color'].';' : '';
	        $readmore_btn_attr .= '"';    
	        $readmore_btn = ( $settings['read_more_link_text'] ) ? '<a href="'.$settings['read_more_link_url'].'" id="pu-readmore-cookie" target="_blank" class="pu-gdpr-cookie-readmore-btn  cli-read-more-link '.$show_as.'" '.$readmore_btn_attr.' data-wpel-link="external" rel="nofollow">'.stripslashes($settings['read_more_link_text']) .'</a>' : '';
		    $notify_html = '<div id="cookie-law-info-bar">'.
		    ($head!="" ? '<'.$msg_heading_tag.' class="cli_messagebar_head">'.$head.'</'.$msg_heading_tag.'>' : '')
		    .'<div class="cookie-msg-wrap"><span class="cookie-msg">' . $str ." ". $readmore_btn . '</span><div class="pu-cookie-button">'. $accept_btn . '</div></div></div>';
		    	
	      	$notify_html .= '<div id="cookie-law-info-again" ><span id="cookie_hdr_showagain">'.$show_again.'</span></div>';
	      	echo $notify_html;
		}

	}
}