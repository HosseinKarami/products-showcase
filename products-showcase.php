<?php
/**
 * Plugin Name: Products Showcase – Shopify Integration
 * Plugin URI: https://github.com/HosseinKarami/products-showcase
 * Description: Display Shopify products and collections in beautiful carousels using native Gutenberg blocks. Features product filtering, color swatches, and responsive design.
 * Version: 1.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Hossein Karami
 * Author URI: https://hosseinkarami.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: products-showcase
 * Domain Path: /languages
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'PRODSHOW_VERSION', '1.1.0' );
define( 'PRODSHOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODSHOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODSHOW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'PRODSHOW_SHOPIFY_API_VERSION_FALLBACK', '2025-10' ); // Fallback Shopify Admin API version.

/**
 * Get the Shopify API version to use
 * 
 * Returns the dynamically detected version if available, otherwise falls back to default.
 *
 * @return string The API version to use (e.g., '2025-10')
 */
function prodshow_get_api_version() {
	$stored_version = get_option( 'prodshow_shopify_api_version', '' );
	
	if ( ! empty( $stored_version ) ) {
		return $stored_version;
	}
	
	return PRODSHOW_SHOPIFY_API_VERSION_FALLBACK;
}

// For backwards compatibility, define the constant using the dynamic function
// Note: This runs early, so it will use the stored value or fallback
if ( ! defined( 'PRODSHOW_SHOPIFY_API_VERSION' ) ) {
	// We can't call prodshow_get_api_version() before plugins_loaded in some cases,
	// so we check the option directly here
	$_prodshow_api_version = get_option( 'prodshow_shopify_api_version', '' );
	if ( empty( $_prodshow_api_version ) ) {
		$_prodshow_api_version = PRODSHOW_SHOPIFY_API_VERSION_FALLBACK;
	}
	define( 'PRODSHOW_SHOPIFY_API_VERSION', $_prodshow_api_version );
	unset( $_prodshow_api_version );
}

/**
 * Initialize plugin
 */
function prodshow_init() {
	// Load plugin files.
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-shopify-api.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-shopify-block.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-shopify-graphql-types.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-shopify-oauth.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-admin-settings.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-enqueue-assets.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-rest-api.php';

	// Initialize classes.
	new PRODSHOW_Shopify_API();
	new PRODSHOW_Shopify_Block();
	new PRODSHOW_Shopify_GraphQL_Types();
	new PRODSHOW_Shopify_OAuth();
	new PRODSHOW_Admin_Settings();
	new PRODSHOW_Enqueue_Assets();
	new PRODSHOW_REST_API();

	// Auto-detect API version if connected but no version stored yet
	add_action( 'admin_init', 'prodshow_maybe_detect_api_version' );
}
add_action( 'plugins_loaded', 'prodshow_init' );

/**
 * Detect and store API version if connected but not yet detected
 * 
 * This runs once when an existing connection doesn't have a stored API version.
 */
function prodshow_maybe_detect_api_version() {
	// Only run once per day to avoid excessive API calls
	$last_check = get_transient( 'prodshow_api_version_check' );
	if ( $last_check ) {
		return;
	}

	// Check if we have a connection but no stored API version
	$shop_url = get_option( 'prodshow_shopify_url', '' );
	$access_token = get_option( 'prodshow_shopify_access_token', '' );
	$stored_version = get_option( 'prodshow_shopify_api_version', '' );

	// If we have credentials but no stored version, try to detect it
	if ( ! empty( $shop_url ) && ! empty( $access_token ) && empty( $stored_version ) ) {
		$detected_version = PRODSHOW_Shopify_OAuth::get_latest_api_version( $shop_url, $access_token );
		
		if ( $detected_version ) {
			update_option( 'prodshow_shopify_api_version', $detected_version );
		}
	}

	// Set transient to prevent checking again for 24 hours
	set_transient( 'prodshow_api_version_check', true, DAY_IN_SECONDS );
}

/**
 * Plugin activation
 */
function prodshow_activate() {
	// Set default options.
	add_option( 'prodshow_shopify_url', '' );
	add_option( 'prodshow_shopify_access_token', '' );
	add_option( 'prodshow_cache_duration', HOUR_IN_SECONDS );

	// Clear permalinks.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'prodshow_activate' );

/**
 * Plugin deactivation
 */
function prodshow_deactivate() {
	// Clear all cached Shopify data.
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache during deactivation, no caching needed.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_prodshow_shopify_%'" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache during deactivation, no caching needed.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_prodshow_shopify_%'" );

	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'prodshow_deactivate' );

/**
 * Add settings link on plugin page
 *
 * @param array $links Plugin action links.
 * @return array
 */
function prodshow_plugin_action_links( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=products-showcase' ) . '">' . __( 'Settings', 'products-showcase' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . PRODSHOW_PLUGIN_BASENAME, 'prodshow_plugin_action_links' );
