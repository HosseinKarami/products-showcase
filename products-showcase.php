<?php
/**
 * Plugin Name: Products Showcase – Shopify Integration
 * Plugin URI: https://prodshowcase.com/
 * Description: Display Shopify products and collections in carousels or grids with a native Gutenberg block or shortcode. Sale badges, color swatches, OAuth setup.
 * Version: 1.6.1
 * Requires at least: 6.3
 * Requires PHP: 7.4
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
define( 'PRODSHOW_VERSION', '1.6.1' );
define( 'PRODSHOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODSHOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODSHOW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'PRODSHOW_SHOPIFY_API_VERSION_FALLBACK', '2026-07' ); // Fallback Shopify Admin API version (2025-10 retires 2026-10-16).

/**
 * Get the Shopify API version to use
 * 
 * Returns the dynamically detected version if available, otherwise falls back to default.
 *
 * @return string The API version to use (e.g., '2026-07')
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
	$prodshow_api_version = get_option( 'prodshow_shopify_api_version', '' );
	if ( empty( $prodshow_api_version ) ) {
		$prodshow_api_version = PRODSHOW_SHOPIFY_API_VERSION_FALLBACK;
	}
	define( 'PRODSHOW_SHOPIFY_API_VERSION', $prodshow_api_version );
	unset( $prodshow_api_version );
}

/**
 * Locate a plugin template, allowing theme overrides
 *
 * Themes can override the plugin's templates by copying them into a
 * `products-showcase/` folder, e.g.:
 *
 *   your-theme/products-showcase/product-card.php
 *   your-theme/products-showcase/block-template.php
 *
 * Child theme templates take precedence over parent theme templates,
 * which take precedence over the plugin's bundled templates.
 *
 * @param string $template_name Template file name relative to the plugin's templates/ directory.
 * @return string Full path of the template file to include.
 */
function prodshow_locate_template( $template_name ) {
	$theme_template = locate_template( 'products-showcase/' . $template_name );

	if ( $theme_template ) {
		return $theme_template;
	}

	return PRODSHOW_PLUGIN_DIR . 'templates/' . $template_name;
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
	require_once PRODSHOW_PLUGIN_DIR . 'includes/class-review-request.php';

	// Initialize classes.
	new PRODSHOW_Shopify_API();
	new PRODSHOW_Shopify_Block();
	new PRODSHOW_Shopify_GraphQL_Types();
	new PRODSHOW_Shopify_OAuth();
	new PRODSHOW_Admin_Settings();
	new PRODSHOW_Enqueue_Assets();
	new PRODSHOW_REST_API();
	new PRODSHOW_Review_Request();

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
	if ( class_exists( 'PRODSHOW_Shopify_API' ) ) {
		PRODSHOW_Shopify_API::clear_cache();
	}

	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'prodshow_deactivate' );

/**
 * Add settings and donate links on plugin page
 *
 * @param array $links Plugin action links.
 * @return array
 */
function prodshow_plugin_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=products-showcase' ) ) . '">' . __( 'Settings', 'products-showcase' ) . '</a>';
	array_unshift( $links, $settings_link );

	if ( apply_filters( 'prodshow_show_donate_link', true ) ) {
		$donate_url = add_query_arg(
			array(
				'utm_source'   => 'wp-plugin',
				'utm_medium'   => 'plugins-list',
				'utm_campaign' => 'products-showcase',
				'utm_content'  => 'plugin-row-donate',
			),
			'https://buymeacoffee.com/hosseinkarami'
		);
		$links[] = '<a href="' . esc_url( $donate_url ) . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr__( 'Donate to support Products Showcase', 'products-showcase' ) . '">' . esc_html__( 'Donate', 'products-showcase' ) . '</a>';
	}

	return $links;
}
add_filter( 'plugin_action_links_' . PRODSHOW_PLUGIN_BASENAME, 'prodshow_plugin_action_links' );
