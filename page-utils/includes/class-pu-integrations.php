<?php

defined( 'ABSPATH' ) || exit;

/**
 * Integrations class.
 */
class PU_Integrations {

	/**
	 * Array of integrations.
	 *
	 * @var array
	 */
	public $integrations = array();

	/**
	 * Initialize integrations.
	 */
	public function __construct() {

		do_action( 'pu_integrations_init' );

		include_once PU_INTEGRATIONS_PATH . 'class-pu-gdpr-cookie.php';

		$load_integrations = apply_filters( 'pu_integrations', array(
			'gdpr_cookie' => 'PU_Integrations_GDPR_Cookie',
		) );

		// Load integration classes.
		foreach ( $load_integrations as $id => $integration ) {

			$load_integration = new $integration();

			$this->integrations[ $id ] = $load_integration;
		}
	}

	/**
	 * Return loaded integrations.
	 *
	 * @return array
	 */
	public function get_integrations() {
		return $this->integrations;
	}
}
