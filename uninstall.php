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
function prodshow_remove_options() {
	// Remove new options
	delete_option( 'prodshow_shopify_url' );
	delete_option( 'prodshow_shopify_access_token' );
	delete_option( 'prodshow_cache_duration' );
	delete_option( 'prodshow_utm_source' );
	delete_option( 'prodshow_utm_medium' );
	delete_option( 'prodshow_utm_campaign' );
	delete_option( 'prodshow_migrated_api_version' );
	
	// Clean up legacy options if they exist
	delete_option( 'sps_shopify_url' );
	delete_option( 'sps_shopify_access_token' );
	delete_option( 'sps_cache_duration' );
	delete_option( 'sps_utm_source' );
	delete_option( 'sps_utm_medium' );
	delete_option( 'sps_utm_campaign' );
	delete_option( 'sps_api_version' );
	delete_option( 'sps_migrated_api_version' );
}

/**
 * Clear all cached Shopify data
 */
function prodshow_clear_transients() {
	global $wpdb;
	
	// Delete all transients with new prefix
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache during uninstall, no caching needed.
	$wpdb->query( 
		"DELETE FROM {$wpdb->options} 
		WHERE option_name LIKE '_transient_prodshow_shopify_%' 
		OR option_name LIKE '_transient_timeout_prodshow_shopify_%'"
	);
	
	// Clean up legacy transients if they exist
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
prodshow_remove_options();
prodshow_clear_transients();

// Optional: Remove user meta data (if any)
// delete_metadata( 'user', 0, 'prodshow_user_preference', '', true );

// Flush rewrite rules.
flush_rewrite_rules();

