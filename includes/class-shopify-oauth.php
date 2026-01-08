<?php
/**
 * Shopify OAuth Handler
 *
 * Handles OAuth 2.0 Authorization Code Grant flow for Shopify
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PRODSHOW_Shopify_OAuth class
 */
class PRODSHOW_Shopify_OAuth
{
	/**
	 * OAuth state transient prefix
	 */
	const STATE_TRANSIENT_PREFIX = 'prodshow_oauth_state_';

	/**
	 * Required Shopify API scopes
	 */
	const REQUIRED_SCOPES = 'read_products';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Handle OAuth callback
		add_action('admin_init', array($this, 'handle_oauth_callback'));
		
		// AJAX handlers
		add_action('wp_ajax_prodshow_initiate_oauth', array($this, 'ajax_initiate_oauth'));
		add_action('wp_ajax_prodshow_disconnect_shopify', array($this, 'ajax_disconnect_shopify'));
		add_action('wp_ajax_prodshow_refresh_api_version', array($this, 'ajax_refresh_api_version'));
	}

	/**
	 * Get the OAuth redirect URI
	 *
	 * @return string The redirect URI for OAuth callback
	 */
	public static function get_redirect_uri()
	{
		return admin_url('admin.php?page=products-showcase&prodshow_oauth_callback=1');
	}

	/**
	 * AJAX handler to initiate OAuth flow
	 */
	public function ajax_initiate_oauth()
	{
		// Security check
		check_ajax_referer('prodshow_oauth_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('Unauthorized access', 'products-showcase')));
		}

		// Get and validate required fields
		$shop_url = isset($_POST['shop_url']) ? sanitize_text_field(wp_unslash($_POST['shop_url'])) : '';
		$client_id = isset($_POST['client_id']) ? sanitize_text_field(wp_unslash($_POST['client_id'])) : '';
		$client_secret = isset($_POST['client_secret']) ? sanitize_text_field(wp_unslash($_POST['client_secret'])) : '';

		if (empty($shop_url) || empty($client_id) || empty($client_secret)) {
			wp_send_json_error(array('message' => __('Please fill in all required fields: Store URL, Client ID, and Client Secret.', 'products-showcase')));
		}

		// Validate shop URL format
		$shop_url = $this->sanitize_shop_url($shop_url);
		if (!$shop_url) {
			wp_send_json_error(array('message' => __('Invalid Shopify store URL. Please enter a valid URL like "your-store.myshopify.com"', 'products-showcase')));
		}

		// Save credentials temporarily (will be confirmed after successful OAuth)
		update_option('prodshow_shopify_url', $shop_url);
		update_option('prodshow_shopify_client_id', $client_id);
		update_option('prodshow_shopify_client_secret', $client_secret);

		// Generate state for CSRF protection
		$state = wp_generate_password(32, false);
		set_transient(self::STATE_TRANSIENT_PREFIX . $state, array(
			'shop_url' => $shop_url,
			'client_id' => $client_id,
			'timestamp' => time(),
		), 600); // 10 minutes expiry

		// Build authorization URL
		$auth_url = $this->build_authorization_url($shop_url, $client_id, $state);

		wp_send_json_success(array(
			'redirect_url' => $auth_url,
		));
	}

	/**
	 * Build the Shopify OAuth authorization URL
	 *
	 * @param string $shop_url The Shopify store URL
	 * @param string $client_id The app's Client ID
	 * @param string $state The CSRF protection state
	 * @return string The authorization URL
	 */
	private function build_authorization_url($shop_url, $client_id, $state)
	{
		$params = array(
			'client_id' => $client_id,
			'scope' => self::REQUIRED_SCOPES,
			'redirect_uri' => self::get_redirect_uri(),
			'state' => $state,
		);

		return "https://{$shop_url}/admin/oauth/authorize?" . http_build_query($params);
	}

	/**
	 * Handle the OAuth callback from Shopify
	 */
	public function handle_oauth_callback()
	{
		// Check if this is an OAuth callback
		if (!isset($_GET['page']) || $_GET['page'] !== 'products-showcase' || !isset($_GET['prodshow_oauth_callback'])) {
			return;
		}

		// Check for errors from Shopify
		if (isset($_GET['error'])) {
			$error_description = isset($_GET['error_description']) ? sanitize_text_field(wp_unslash($_GET['error_description'])) : __('Unknown error', 'products-showcase');
			$this->redirect_with_error($error_description);
			return;
		}

		// Get the authorization code and state
		$code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
		$state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';

		if (empty($code) || empty($state)) {
			$this->redirect_with_error(__('Invalid OAuth response. Missing authorization code or state.', 'products-showcase'));
			return;
		}

		// Validate state (CSRF protection)
		$state_data = get_transient(self::STATE_TRANSIENT_PREFIX . $state);
		if (!$state_data) {
			$this->redirect_with_error(__('OAuth session expired or invalid. Please try again.', 'products-showcase'));
			return;
		}

		// Delete the used state
		delete_transient(self::STATE_TRANSIENT_PREFIX . $state);

		// Exchange authorization code for access token
		$result = $this->exchange_code_for_token($code, $state_data);

		if (is_wp_error($result)) {
			$this->redirect_with_error($result->get_error_message());
			return;
		}

		// Save the access token
		update_option('prodshow_shopify_access_token', $result['access_token']);

		// Fetch and store the latest API version
		$this->fetch_and_store_api_version($state_data['shop_url'], $result['access_token']);

		// Clear any cached data
		$this->clear_shopify_cache();

		// Redirect with success
		$this->redirect_with_success();
	}

	/**
	 * Exchange authorization code for access token
	 *
	 * @param string $code The authorization code
	 * @param array $state_data The stored state data
	 * @return array|WP_Error The token response or error
	 */
	private function exchange_code_for_token($code, $state_data)
	{
		$shop_url = $state_data['shop_url'];
		$client_id = get_option('prodshow_shopify_client_id', '');
		$client_secret = get_option('prodshow_shopify_client_secret', '');

		if (empty($client_id) || empty($client_secret)) {
			return new WP_Error('missing_credentials', __('Client credentials not found. Please try again.', 'products-showcase'));
		}

		$token_url = "https://{$shop_url}/admin/oauth/access_token";

		$response = wp_remote_post($token_url, array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			),
			'body' => wp_json_encode(array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'code' => $code,
			)),
			'timeout' => 30,
		));

		if (is_wp_error($response)) {
			return new WP_Error('request_failed', sprintf(
				/* translators: %s: Error message */
				__('Failed to connect to Shopify: %s', 'products-showcase'),
				$response->get_error_message()
			));
		}

		$status_code = wp_remote_retrieve_response_code($response);
		$body = json_decode(wp_remote_retrieve_body($response), true);

		if ($status_code !== 200) {
			$error_message = isset($body['error_description']) ? $body['error_description'] : (isset($body['error']) ? $body['error'] : __('Unknown error from Shopify', 'products-showcase'));
			return new WP_Error('token_error', $error_message);
		}

		if (empty($body['access_token'])) {
			return new WP_Error('no_token', __('No access token received from Shopify.', 'products-showcase'));
		}

		return array(
			'access_token' => $body['access_token'],
			'scope' => isset($body['scope']) ? $body['scope'] : '',
		);
	}

	/**
	 * AJAX handler to refresh API version
	 */
	public function ajax_refresh_api_version()
	{
		check_ajax_referer('prodshow_oauth_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('Unauthorized access', 'products-showcase')));
		}

		// Clear any rate-limiting transient
		delete_transient('prodshow_api_version_check');

		$new_version = self::refresh_api_version();

		if ($new_version) {
			wp_send_json_success(array(
				'message' => __('API version updated successfully!', 'products-showcase'),
				'version' => $new_version,
			));
		} else {
			wp_send_json_error(array(
				'message' => __('Could not detect API version. Please check your connection.', 'products-showcase'),
			));
		}
	}

	/**
	 * AJAX handler to disconnect from Shopify
	 */
	public function ajax_disconnect_shopify()
	{
		check_ajax_referer('prodshow_oauth_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('Unauthorized access', 'products-showcase')));
		}

		// Clear all Shopify-related options
		delete_option('prodshow_shopify_access_token');
		delete_option('prodshow_shopify_client_id');
		delete_option('prodshow_shopify_client_secret');
		delete_option('prodshow_shopify_api_version');
		// Keep the shop URL for convenience if user wants to reconnect

		// Clear cache
		$this->clear_shopify_cache();

		wp_send_json_success(array(
			'message' => __('Successfully disconnected from Shopify.', 'products-showcase'),
		));
	}

	/**
	 * Sanitize and validate shop URL
	 *
	 * @param string $url The shop URL to sanitize
	 * @return string|false Sanitized URL or false if invalid
	 */
	private function sanitize_shop_url($url)
	{
		// Remove protocol if present
		$url = preg_replace('#^https?://#', '', $url);
		
		// Remove trailing slash
		$url = rtrim($url, '/');
		
		// Validate format
		if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $url)) {
			return false;
		}

		return $url;
	}

	/**
	 * Redirect with error message
	 *
	 * @param string $error The error message
	 */
	private function redirect_with_error($error)
	{
		set_transient('prodshow_oauth_error', $error, 60);
		wp_safe_redirect(admin_url('admin.php?page=products-showcase&oauth_error=1'));
		exit;
	}

	/**
	 * Redirect with success message
	 */
	private function redirect_with_success()
	{
		set_transient('prodshow_oauth_success', true, 60);
		wp_safe_redirect(admin_url('admin.php?page=products-showcase&oauth_success=1'));
		exit;
	}

	/**
	 * Clear all Shopify cache
	 */
	private function clear_shopify_cache()
	{
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_prodshow_shopify_%'");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_prodshow_shopify_%'");
	}

	/**
	 * Check if OAuth is configured (has client credentials)
	 *
	 * @return bool
	 */
	public static function has_oauth_credentials()
	{
		return !empty(get_option('prodshow_shopify_client_id')) && !empty(get_option('prodshow_shopify_client_secret'));
	}

	/**
	 * Check if connected via OAuth
	 *
	 * @return bool
	 */
	public static function is_connected_via_oauth()
	{
		return self::has_oauth_credentials() && !empty(get_option('prodshow_shopify_access_token'));
	}

	/**
	 * Fetch and store the latest supported API version from Shopify
	 *
	 * @param string $shop_url The Shopify store URL
	 * @param string $access_token The access token
	 * @return string|false The API version or false on failure
	 */
	private function fetch_and_store_api_version($shop_url, $access_token)
	{
		$api_version = $this->get_latest_api_version($shop_url, $access_token);
		
		if ($api_version) {
			update_option('prodshow_shopify_api_version', $api_version);
			return $api_version;
		}
		
		return false;
	}

	/**
	 * Get the latest supported API version from Shopify
	 *
	 * Uses GraphQL introspection to get available API versions
	 *
	 * @param string $shop_url The Shopify store URL
	 * @param string $access_token The access token
	 * @return string|false The latest stable API version or false on failure
	 */
	public static function get_latest_api_version($shop_url, $access_token)
	{
		// First try the publicApiVersions query via GraphQL
		$version = self::get_api_version_via_graphql($shop_url, $access_token);
		if ($version) {
			return $version;
		}

		// Fallback: try the /admin/api.json endpoint
		$version = self::get_api_version_via_rest($shop_url, $access_token);
		if ($version) {
			return $version;
		}

		return false;
	}

	/**
	 * Get API version via GraphQL publicApiVersions query
	 *
	 * @param string $shop_url The Shopify store URL
	 * @param string $access_token The access token
	 * @return string|false The API version or false on failure
	 */
	private static function get_api_version_via_graphql($shop_url, $access_token)
	{
		// Use a known working API version to query for available versions
		$url = "https://{$shop_url}/admin/api/" . PRODSHOW_SHOPIFY_API_VERSION_FALLBACK . "/graphql.json";

		$query = '{
			publicApiVersions {
				handle
				displayName
				supported
			}
		}';

		$response = wp_remote_post($url, array(
			'headers' => array(
				'X-Shopify-Access-Token' => $access_token,
				'Content-Type' => 'application/json',
			),
			'body' => wp_json_encode(array('query' => $query)),
			'timeout' => 15,
		));

		if (is_wp_error($response)) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code($response);
		if ($status_code !== 200) {
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (empty($body['data']['publicApiVersions']) || !is_array($body['data']['publicApiVersions'])) {
			return false;
		}

		// Find the latest supported stable version
		$latest_stable_version = null;

		foreach ($body['data']['publicApiVersions'] as $version) {
			// Skip unstable version
			if ($version['handle'] === 'unstable') {
				continue;
			}

			// Only consider supported versions
			if (empty($version['supported'])) {
				continue;
			}

			// Stable versions are in format YYYY-MM (e.g., 2025-01)
			if (preg_match('/^\d{4}-\d{2}$/', $version['handle'])) {
				if ($latest_stable_version === null || $version['handle'] > $latest_stable_version) {
					$latest_stable_version = $version['handle'];
				}
			}
		}

		return $latest_stable_version;
	}

	/**
	 * Get API version via REST /admin/api.json endpoint
	 *
	 * @param string $shop_url The Shopify store URL
	 * @param string $access_token The access token
	 * @return string|false The API version or false on failure
	 */
	private static function get_api_version_via_rest($shop_url, $access_token)
	{
		$url = "https://{$shop_url}/admin/api.json";

		$response = wp_remote_get($url, array(
			'headers' => array(
				'X-Shopify-Access-Token' => $access_token,
				'Accept' => 'application/json',
			),
			'timeout' => 15,
		));

		if (is_wp_error($response)) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code($response);
		if ($status_code !== 200) {
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (empty($body['supported_api_versions']) || !is_array($body['supported_api_versions'])) {
			return false;
		}

		// Find the latest supported stable version
		$latest_version = null;
		$latest_stable_version = null;

		foreach ($body['supported_api_versions'] as $version) {
			// Skip unstable version
			if ($version['handle'] === 'unstable') {
				continue;
			}

			// Check if this is marked as the latest supported version
			if (!empty($version['latest_supported']) && $version['latest_supported'] === true) {
				$latest_version = $version['handle'];
				break;
			}

			// Track the highest numbered stable version as fallback
			if (preg_match('/^\d{4}-\d{2}$/', $version['handle'])) {
				if ($latest_stable_version === null || $version['handle'] > $latest_stable_version) {
					$latest_stable_version = $version['handle'];
				}
			}
		}

		return $latest_version ?: $latest_stable_version;
	}

	/**
	 * Refresh the stored API version
	 * 
	 * Can be called to update the API version without reconnecting
	 *
	 * @return string|false The new API version or false on failure
	 */
	public static function refresh_api_version()
	{
		$shop_url = get_option('prodshow_shopify_url', '');
		$access_token = get_option('prodshow_shopify_access_token', '');

		if (empty($shop_url) || empty($access_token)) {
			return false;
		}

		$api_version = self::get_latest_api_version($shop_url, $access_token);

		if ($api_version) {
			update_option('prodshow_shopify_api_version', $api_version);
			return $api_version;
		}

		return false;
	}

	/**
	 * Get the currently stored API version
	 *
	 * @return string The API version
	 */
	public static function get_stored_api_version()
	{
		return get_option('prodshow_shopify_api_version', PRODSHOW_SHOPIFY_API_VERSION_FALLBACK);
	}
}
