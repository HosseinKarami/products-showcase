<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled.
 *
 * @package ProductsShowcase
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove all plugin options
 */
function sps_remove_options() {
	delete_option( 'sps_shopify_url' );
	delete_option( 'sps_shopify_access_token' );
	delete_option( 'sps_cache_duration' );
	// Clean up legacy option if it exists.
	delete_option( 'sps_api_version' );
}

/**
 * Clear all cached Shopify data
 */
function sps_clear_transients() {
	global $wpdb;
	
	// Delete all transients with our prefix.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache during uninstall, no caching needed.
	$wpdb->query( 
		"DELETE FROM {$wpdb->options} 
		WHERE option_name LIKE '_transient_sps_shopify_%' 
		OR option_name LIKE '_transient_timeout_sps_shopify_%'"
	);
}

/**
 * Remove plugin data on uninstall
 */
sps_remove_options();
sps_clear_transients();

// Optional: Remove user meta data (if any)
// delete_metadata( 'user', 0, 'sps_user_preference', '', true );

// Flush rewrite rules.
flush_rewrite_rules();

