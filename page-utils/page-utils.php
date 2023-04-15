<?php
/**
 * Plugin Name: Page Utils
 * Description: A toolkit that helps you to add Page Utils likes Frequently Asked Questions, WP SVG Icon, Star rating etc.
 * Version: 0.0.9.4.2
 * Author: itzmekhokan
 * Text Domain: page-utils
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PU_PLUGIN_FILE' ) ) {
	define( 'PU_PLUGIN_FILE', __FILE__ );
}

// Include config constants
include_once 'config.php';

// Include the main PU class.
if ( ! class_exists( 'PU', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-pu.php';
}

/**
 * Returns the main instance of PU.
 */
function PU() { 
	return PU::instance();
}

// Global page-utils
$GLOBALS['page-utils'] = PU();