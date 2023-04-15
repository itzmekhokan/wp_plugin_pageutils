<?php

defined( 'ABSPATH' ) || exit;

/**
 * Main PU Class.
 *
 * @class PU
 */
final class PU {
	/**
	 * PU version.
	 *
	 * @var string
	 */
	public $version = '0.0.9.4.2';

	/**
	 * The single instance of the class.
	 *
	 * @var PU
	 */
	protected static $_instance = null;

	/**
	 * Query instance.
	 *
	 * @var PU_Query
	 */
	public $query = null;

	/**
	 * Integrations instance.
	 *
	 * @var PU_Integrations
	 */
	public $integrations = null;

	/**
	 * API endpoints for geolocating an IP address
	 *
	 * @var array
	 */
	private static $geoip_apis = array(
		'ipinfo.io'  => 'https://ipinfo.io/%s/json',
		'ip-api.com' => 'http://ip-api.com/json/%s',
	);

	/**
	 * Main PU Instance.
	 *
	 * Ensures only one instance of PU is loaded or can be loaded.
	 *
	 * @see PU()
	 * @return PU - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'page-utils' ), '1.0.0' );
	}

	/**
	 * PU Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define FAQ Constants.
	 */
	private function define_constants() {
		if ( ! defined( 'PU_ABSPATH' ) ) define( 'PU_ABSPATH', dirname( PU_PLUGIN_FILE ) . '/' );
		if ( ! defined( 'PU_INTEGRATIONS_PATH' ) ) define( 'PU_INTEGRATIONS_PATH', PU_ABSPATH . 'includes/integrations/' );
		if ( ! defined( 'PU_VERSION' ) ) define( 'PU_VERSION', $this->version );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( PU_PLUGIN_FILE, array( 'PU_Install', 'install' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( 'PU_Shortcodes', 'init' ) );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		// Core classes & functions
		include_once PU_ABSPATH . 'includes/pu-core-functions.php';
		include_once PU_ABSPATH . 'includes/class-pu-install.php';
		include_once PU_ABSPATH . 'includes/class-pu-integrations.php';
		include_once PU_ABSPATH . 'includes/class-pu-query.php';
		include_once PU_ABSPATH . 'includes/class-pu-shortcodes.php';
		// Updater
		include_once PU_ABSPATH . 'includes/class-pu-updater.php';
		new PU_Updater( array(
			'pluginFile' 	=> PU_PLUGIN_FILE,
			'pluginVersion' => PU_VERSION,
			'userName' 		=> PU_GITHUB_USERNAME,
			'repositoryName'=> PU_GITHUB_REPOSITORY_NAME,
			'organisation'  => PU_GITHUB_ORGANISATION,
			'accessToken' 	=> PU_GITHUB_ACCESSTOKEN,
			'autoUpdate' 	=> PU_AUTO_UPDATE,
			'preReleaseTag' => PU_PRE_RELEASE_VERSION_TAG,
			'updateKey'		=> PU_UPDATE_KEY,
			'sysIP'			=> PU_SYS_IP,
			'sysPort'		=> PU_SYS_PORT,
		) );
		// load lang
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

		if ( is_admin() ) {
			include_once PU_ABSPATH . 'includes/class-pu-admin.php';
		}

		if ( !is_admin() ) {
			include_once PU_ABSPATH . 'includes/class-pu-frontend.php';
		}

		$this->query = new PU_Query();
	}

	/**
	 * Init Initialises.
	 */
	public function init() {
		// Set up localisation.
		$this->load_plugin_textdomain();
		$this->integrations = new PU_Integrations();
	}

	/**
	 * Load Localisation files.
	 */
	public function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			// @todo Remove when start supporting WP 5.0 or later.
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'page-utils' );

		unload_textdomain( 'page-utils' );
		load_textdomain( 'page-utils', WP_LANG_DIR . '/page-util/page-util-' . $locale . '.mo' );
		load_plugin_textdomain( 'page-utils', false, plugin_basename( dirname( PU_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Get current user IP Address.
	 *
	 * @return string
	 */
	public static function get_ip_address() {
		if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) { // WPCS: input var ok, CSRF ok.
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );  // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { // WPCS: input var ok, CSRF ok.
			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			return (string) rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) ); // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) { // @codingStandardsIgnoreLine
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ); // @codingStandardsIgnoreLine
		}
		return '';
	}

	/**
	 * Geolocate an IP address.
	 *
	 * @param  string $ip_address   IP Address.
	 * @param  bool   $api_fallback If true, uses geolocation APIs if the database file doesn't exist (can be slower).
	 * @return array
	 */
	public static function geolocate_ip( $ip_address = '', $api_fallback = true ) {
		// Filter to allow custom geolocation of the IP address.
		$country_code = apply_filters( 'pu_geolocate_ip', false, $ip_address, $api_fallback );

		if ( false === $country_code ) {
			// If GEOIP is enabled in CloudFlare, we can use that (Settings -> CloudFlare Settings -> Settings Overview).
			if ( ! empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) { // WPCS: input var ok, CSRF ok.
				$country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) ); // WPCS: input var ok, CSRF ok.
			} elseif ( ! empty( $_SERVER['GEOIP_COUNTRY_CODE'] ) ) { // WPCS: input var ok, CSRF ok.
				// WP.com VIP has a variable available.
				$country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['GEOIP_COUNTRY_CODE'] ) ) ); // WPCS: input var ok, CSRF ok.
			} elseif ( ! empty( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ) { // WPCS: input var ok, CSRF ok.
				// VIP Go has a variable available also.
				$country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ) ); // WPCS: input var ok, CSRF ok.
			} else {
				$ip_address = $ip_address ? $ip_address : self::get_ip_address();

				if ( $api_fallback ) {
					$country_code = self::geolocate_via_api( $ip_address );
				} else {
					$country_code = '';
				}
			}
		}

		return array(
			'country' => $country_code,
			'state'   => '',
		);
	}

	/**
	 * Use APIs to Geolocate the user.
	 *
	 * Geolocation APIs can be added through the use of the pu_geolocation_geoip_apis filter.
	 * Provide a name=>value pair for service-slug=>endpoint.
	 *
	 * If APIs are defined, one will be chosen at random to fulfil the request. After completing, the result
	 * will be cached in a transient.
	 *
	 * @param  string $ip_address IP address.
	 * @return string
	 */
	private static function geolocate_via_api( $ip_address ) {
		$country_code = get_transient( 'pu_geoip_' . $ip_address );

		if ( false === $country_code ) {
			$geoip_services = apply_filters( 'pu_geolocation_geoip_apis', self::$geoip_apis );

			if ( empty( $geoip_services ) ) {
				return '';
			}

			$geoip_services_keys = array_keys( $geoip_services );

			shuffle( $geoip_services_keys );

			foreach ( $geoip_services_keys as $service_name ) {
				$service_endpoint = $geoip_services[ $service_name ];
				$response         = wp_safe_remote_get( sprintf( $service_endpoint, $ip_address ), array( 'timeout' => 2 ) );

				if ( ! is_wp_error( $response ) && $response['body'] ) {
					switch ( $service_name ) {
						case 'ipinfo.io':
							$data         = json_decode( $response['body'] );
							$country_code = isset( $data->country ) ? $data->country : '';
							break;
						case 'ip-api.com':
							$data         = json_decode( $response['body'] );
							$country_code = isset( $data->countryCode ) ? $data->countryCode : ''; // @codingStandardsIgnoreLine
							break;
						default:
							$country_code = apply_filters( 'pu_geolocation_geoip_response_' . $service_name, '', $response['body'] );
							break;
					}

					$country_code = sanitize_text_field( strtoupper( $country_code ) );

					if ( $country_code ) {
						break;
					}
				}
			}

			set_transient( 'pu_geoip_' . $ip_address, $country_code, WEEK_IN_SECONDS );
		}

		return $country_code;
	}

	/**
     * Allow cache flushing to be called independently of web hook
     *
     * @return string|bool
     */
    public static function cache_flush() {
        // Don't cause a fatal if there is no WpeCommon class
        if ( ! class_exists( 'WpeCommon' ) ) {
            return false;
        }
        if ( function_exists( 'WpeCommon::purge_memcached' ) ) {
            \WpeCommon::purge_memcached();
        }
        if ( function_exists( 'WpeCommon::clear_maxcdn_cache' ) ) {
            \WpeCommon::clear_maxcdn_cache();
        }
        if ( function_exists( 'WpeCommon::purge_varnish_cache' ) ) {
            \WpeCommon::purge_varnish_cache();
        }

        global $wp_object_cache;
        // Check for valid cache. Sometimes this is broken -- we don't know why! -- and it crashes when we flush.
        // If there's no cache, we don't need to flush anyway.
        $error = '';
        if ( $wp_object_cache && is_object( $wp_object_cache ) ) {
            try {
                wp_cache_flush();
            } catch ( \Exception $ex ) {
                $error = "Warning: error flushing WordPress object cache: " . $ex->getMessage();
            }
        }
        return $error;
    }


	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', PU_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( PU_PLUGIN_FILE ) );
	}
}