<?php

defined( 'ABSPATH' ) || exit;

/**
 * PU_Install Class.
 */
class PU_Install {

	/**
	 * Constructor.
	 */
	public function __construct() {
	
	}

	/**
	 * Install PU.
	 */
	public static function install() {

		self::create_tables();
		self::set_default_settings();

		do_action( 'page_utils_installed' );
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}

	/**
	 * Set default settings for plugin.
	 */
	private static function set_default_settings() {
		if( !pu_get_settings( 'exclude_current_post_sitemap' ) ) {
			$available_options = ( get_option( 'pu_settings_options' ) ) ? get_option( 'pu_settings_options' ) : array();
			$options = array_merge( $available_options, array(
				'exclude_current_post_sitemap' => 'enabled',
			) );
			update_option( 'pu_settings_options', $options );
		}
		if( !pu_get_settings( 'enable_gdpr_cookie_for_eu', 'gdpr_cookie' ) ) {
			$available_options = ( get_option( 'gdpr_cookie_settings_options' ) ) ? get_option( 'gdpr_cookie_settings_options' ) : array();
			$options = array_merge( $available_options, array(
				'enable_gdpr_cookie_for_eu' => 'enabled',
			) );
			update_option( 'gdpr_cookie_settings_options', $options );
		}
	}

	/**
	 * Get Table schema.
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
		CREATE TABLE {$wpdb->prefix}faq_headers (
		  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		  faq_name varchar(200) NOT NULL,
		  PRIMARY KEY  (id)
		) $collate;

		CREATE TABLE {$wpdb->prefix}faq_question_answer (
		  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		  faq_id BIGINT UNSIGNED NOT NULL,
  		  faq_question longtext NULL,
  		  faq_answer longtext NULL,
  		  faq_order BIGINT UNSIGNED NOT NULL DEFAULT 0,
 	 	  PRIMARY KEY  (id),
  		  KEY faq_id (faq_id)
		) $collate;
		";

		return $tables;
	}

}

return new PU_Install();