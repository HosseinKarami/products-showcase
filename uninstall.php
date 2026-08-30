<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled. Removes every option and transient
 * the plugin creates, on every site of a multisite network.
 *
 * @package ProductsShowcase
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove all plugin data for the current site
 */
function prodshow_uninstall_site() {
	$options = array(
		// Connection.
		'prodshow_shopify_url',
		'prodshow_shopify_access_token',
		'prodshow_shopify_client_id',
		'prodshow_shopify_client_secret',
		'prodshow_shopify_api_version',
		// Settings.
		'prodshow_cache_duration',
		'prodshow_cache_version',
		'prodshow_utm_source',
		'prodshow_utm_medium',
		'prodshow_utm_campaign',
		// Review request state.
		'prodshow_first_connected_at',
		'prodshow_first_render_at',
		'prodshow_review_status',
		'prodshow_review_snooze_until',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Delete all plugin transients: cached product/collection data, the
	// primary-domain cache, the API version check flag, and any pending
	// OAuth state transients. They all share the prodshow_ prefix.
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing all plugin transients during uninstall, no caching needed.
	$wpdb->query(
		"DELETE FROM {$wpdb->options} 
		WHERE option_name LIKE '_transient_prodshow_%' 
		OR option_name LIKE '_transient_timeout_prodshow_%'"
	);
}

if ( is_multisite() ) {
	$prodshow_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $prodshow_site_ids as $prodshow_site_id ) {
		switch_to_blog( $prodshow_site_id );
		prodshow_uninstall_site();
		restore_current_blog();
	}

	unset( $prodshow_site_ids, $prodshow_site_id );
} else {
	prodshow_uninstall_site();
}

// Flush rewrite rules.
flush_rewrite_rules();
