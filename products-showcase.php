<?php
/**
 * Plugin Name: Products Showcase – Shopify Integration
 * Plugin URI: https://github.com/HosseinKarami/products-showcase
 * Description: Display Shopify products and collections in beautiful carousels using native Gutenberg blocks. Features product filtering, color swatches, and responsive design.
 * Version: 1.0.0
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
define( 'PRODSHOW_VERSION', '1.0.0' );
define( 'PRODSHOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODSHOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODSHOW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'PRODSHOW_SHOPIFY_API_VERSION', '2025-10' ); // Shopify Admin API version.

/**
 * Initialize plugin
 */
function prodshow_init() {
	// Load plugin files.
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-shopify-api.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-shopify-block.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-shopify-graphql-types.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-admin-settings.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-enqueue-assets.php';
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-rest-api.php';

	// Initialize classes.
	new PRODSHOW_Shopify_API();
	new PRODSHOW_Shopify_Block();
	new PRODSHOW_Shopify_GraphQL_Types();
	new PRODSHOW_Admin_Settings();
	new PRODSHOW_Enqueue_Assets();
	new PRODSHOW_REST_API();
}
add_action( 'plugins_loaded', 'prodshow_init' );

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
