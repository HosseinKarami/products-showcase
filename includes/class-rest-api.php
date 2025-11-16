<?php
/**
 * REST API Endpoints
 *
 * Handles REST API endpoints for block editor interactions
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PRODSHOW_REST_API class
 */
class PRODSHOW_REST_API {
	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $namespace = 'prodshow-shopify/v1';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		// Connection status endpoint.
		register_rest_route(
			$this->namespace,
			'/connection-status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_connection_status' ),
				'permission_callback' => array( $this, 'check_editor_permission' ),
			)
		);

		// Search products endpoint.
		register_rest_route(
			$this->namespace,
			'/search-products',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'search_products' ),
				'permission_callback' => array( $this, 'check_editor_permission' ),
				'args'                => array(
					'query' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function( $param ) {
							return strlen( $param ) >= 2;
						},
					),
				),
			)
		);

		// Search collections endpoint.
		register_rest_route(
			$this->namespace,
			'/search-collections',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'search_collections' ),
				'permission_callback' => array( $this, 'check_editor_permission' ),
				'args'                => array(
					'query' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function( $param ) {
							return strlen( $param ) >= 2;
						},
					),
				),
			)
		);

		// Clear cache endpoint.
		register_rest_route(
			$this->namespace,
			'/clear-cache',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'clear_cache' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Get cache status endpoint.
		register_rest_route(
			$this->namespace,
			'/cache-status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_cache_status' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Check if user can edit posts (for block editor)
	 *
	 * @return bool
	 */
	public function check_editor_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if user has admin permissions
	 *
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get Shopify connection status
	 *
	 * @return WP_REST_Response
	 */
	public function get_connection_status() {
		$shop_url     = get_option( 'prodshow_shopify_url' );
		$access_token = get_option( 'prodshow_shopify_access_token' );

		if ( empty( $shop_url ) || empty( $access_token ) ) {
			return new WP_REST_Response(
				array(
					'connected' => false,
					'message'   => __( 'Shopify credentials not configured.', 'products-showcase' ),
				),
				200
			);
		}

		$url = "https://{$shop_url}/admin/api/" . PRODSHOW_SHOPIFY_API_VERSION . "/graphql.json";

		$query = '{
			shop {
				name
			}
		}';

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'X-Shopify-Access-Token' => $access_token,
					'Content-Type'           => 'application/json',
				),
				'body'    => wp_json_encode( array( 'query' => $query ) ),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array(
					'connected' => false,
					'message'   => $response->get_error_message(),
				),
				200
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['data']['shop']['name'] ) ) {
			return new WP_REST_Response(
				array(
					'connected' => true,
					'shop_name' => $body['data']['shop']['name'],
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'connected' => false,
				'message'   => __( 'Unable to connect to Shopify.', 'products-showcase' ),
			),
			200
		);
	}

	/**
	 * Search products
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function search_products( $request ) {
		$query = $request->get_param( 'query' );

		$shopify_api = new PRODSHOW_Shopify_API();
		$products    = $shopify_api->search_products( $query );

		if ( is_wp_error( $products ) ) {
			return new WP_REST_Response(
				array(
					'error'    => true,
					'message'  => $products->get_error_message(),
					'products' => array(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'products' => $products,
			),
			200
		);
	}

	/**
	 * Search collections
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function search_collections( $request ) {
		$query = $request->get_param( 'query' );

		$shopify_api = new PRODSHOW_Shopify_API();
		$collections = $shopify_api->search_collections( $query );

		if ( is_wp_error( $collections ) ) {
			return new WP_REST_Response(
				array(
					'error'       => true,
					'message'     => $collections->get_error_message(),
					'collections' => array(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'collections' => $collections,
			),
			200
		);
	}

	/**
	 * Clear cache
	 *
	 * @return WP_REST_Response
	 */
	public function clear_cache() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache, no caching needed.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sps_shopify_%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache, no caching needed.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_sps_shopify_%'" );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Cache cleared successfully.', 'products-showcase' ),
			),
			200
		);
	}

	/**
	 * Get cache status
	 *
	 * @return WP_REST_Response
	 */
	public function get_cache_status() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reading cache metadata, no caching needed for cache status check.
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_sps_shopify_%'"
		);

		return new WP_REST_Response(
			array(
				'cached_items' => (int) $count,
			),
			200
		);
	}
}

