<?php
/**
 * Shopify API Integration
 *
 * Handles all Shopify GraphQL API interactions and admin AJAX handlers
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PRODSHOW_Shopify_API class
 */
class PRODSHOW_Shopify_API {
	/**
	 * GraphQL selection set shared by the single-product and batched
	 * product queries. Collections use a lighter variant (fewer images
	 * and variants) to stay within Shopify's per-query cost limit.
	 */
	const PRODUCT_FIELDS = '
		id
		title
		handle
		description
		productType
		hasOutOfStockVariants
		status
		priceRangeV2 {
			minVariantPrice {
				amount
				currencyCode
			}
		}
		options {
			id
			name
			values
			optionValues {
				swatch {
					color
					image {
						alt
						image {
							url
						}
					}
				}
			}
		}
		images(first: 10) {
			edges {
				node {
					id
					url
					altText
				}
			}
		}
		variants(first: 50) {
			edges {
				node {
					id
					title
					price
					compareAtPrice
					sku
					image {
						url
						altText
					}
					selectedOptions {
						name
						value
					}
				}
			}
		}';

	/**
	 * How many products to request per batched nodes() call.
	 *
	 * Shopify caps the cost of a single GraphQL query; with 50 variants and
	 * 10 images per product, five products stay comfortably under the cap.
	 */
	const BATCH_SIZE = 5;

	/**
	 * Shopify API credentials and endpoints
	 *
	 * @var string
	 */
	private $shop_url;
	private $access_token;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Load credentials from WordPress options.
		$this->shop_url      = get_option( 'prodshow_shopify_url', '' );
		$this->access_token  = get_option( 'prodshow_shopify_access_token', '' );

		// Initialize hooks.
		add_action( 'wp_ajax_prodshow_search_shopify_products', array( $this, 'ajax_search_products' ) );
		add_action( 'wp_ajax_prodshow_search_shopify_collections', array( $this, 'ajax_search_collections' ) );
		add_action( 'wp_ajax_prodshow_get_shopify_product', array( $this, 'ajax_get_product' ) );
		add_action( 'wp_ajax_prodshow_get_shopify_collection', array( $this, 'ajax_get_collection' ) );
	}

	/**
	 * AJAX handler for searching Shopify products
	 */
	public function ajax_search_products() {
		// Security check.
		check_ajax_referer( 'prodshow_shopify_search', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized access' );
		}

		$search_term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Query Shopify API.
		$results = $this->search_products( $search_term );

		wp_send_json_success( $results );
	}

	/**
	 * AJAX handler for searching Shopify collections
	 */
	public function ajax_search_collections() {
		// Security check.
		check_ajax_referer( 'prodshow_shopify_search', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized access' );
		}

		$search_term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Query Shopify API.
		$results = $this->search_collections( $search_term );

		wp_send_json_success( $results );
	}

	/**
	 * AJAX handler for getting a single Shopify product by ID
	 */
	public function ajax_get_product() {
		// Security check.
		check_ajax_referer( 'prodshow_shopify_search', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized access' );
		}

		$product_id = isset( $_GET['product_id'] ) ? sanitize_text_field( wp_unslash( $_GET['product_id'] ) ) : '';

		if ( empty( $product_id ) ) {
			wp_send_json_error( array( 'error' => 'Product ID is required' ) );
		}

		// Get product data.
		$product_data = $this->fetch_product_data( $product_id );

		if ( $product_data ) {
			$result = array(
				'product' => array(
					'id'       => $product_id,
					'title'    => $product_data['title'] ?? '',
					'handle'   => $product_data['handle'] ?? '',
					'image'    => isset( $product_data['images']['edges'][0]['node']['originalSrc'] ) ? $product_data['images']['edges'][0]['node']['originalSrc'] : '',
				),
			);
		} else {
			$result = array( 'error' => 'Product not found or API error' );
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX handler for getting a single Shopify collection by ID
	 */
	public function ajax_get_collection() {
		// Security check.
		check_ajax_referer( 'prodshow_shopify_search', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized access' );
		}

		$collection_id = isset( $_GET['collection_id'] ) ? sanitize_text_field( wp_unslash( $_GET['collection_id'] ) ) : '';

		if ( empty( $collection_id ) ) {
			wp_send_json_error( array( 'error' => 'Collection ID is required' ) );
		}

		$collection_data = $this->fetch_collection_data( $collection_id );

		if ( $collection_data ) {
			wp_send_json_success( array( 'collection' => $collection_data ) );
		} else {
			wp_send_json_error( array( 'error' => 'Collection not found' ) );
		}
	}

	/**
	 * Search products via Shopify GraphQL API
	 *
	 * @param string $search_term The search term to use.
	 * @return array|WP_Error Array of matched products or WP_Error.
	 */
	public function search_products( $search_term ) {
		// Exit early if no credentials.
		if ( empty( $this->shop_url ) || empty( $this->access_token ) ) {
			return new WP_Error(
				'missing_credentials',
				__( 'Shopify API credentials not configured. Please configure them in Settings.', 'products-showcase' )
			);
		}

		// Strip quotes/backslashes so the term can't break Shopify's search syntax;
		// the term itself is passed as a GraphQL variable, never interpolated.
		$search_term = trim( str_replace( array( '"', '\\' ), '', $search_term ) );

		$query = 'query ProdshowSearchProducts($search: String!) {
			products(first: 10, query: $search) {
				edges {
					node {
						id
						title
						handle
						featuredImage {
							url(transform: {maxWidth: 50, maxHeight: 50})
						}
						priceRangeV2 {
							minVariantPrice {
								amount
								currencyCode
							}
						}
					}
				}
			}
		}';

		$response = $this->execute_graphql_query( $query, array( 'search' => 'title:*' . $search_term . '*' ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$products = array();
		if ( isset( $response['data']['products']['edges'] ) && is_array( $response['data']['products']['edges'] ) ) {
			foreach ( $response['data']['products']['edges'] as $edge ) {
				$node       = $edge['node'];
				$products[] = array(
					'id'       => $node['id'],
					'title'    => $node['title'],
					'handle'   => $node['handle'],
					'image'    => isset( $node['featuredImage']['url'] ) ? $node['featuredImage']['url'] : '',
					'price'    => isset( $node['priceRangeV2']['minVariantPrice']['amount'] ) ? $node['priceRangeV2']['minVariantPrice']['amount'] : '',
					'currency' => isset( $node['priceRangeV2']['minVariantPrice']['currencyCode'] ) ? $node['priceRangeV2']['minVariantPrice']['currencyCode'] : '',
				);
			}
		}

		return $products;
	}

	/**
	 * Search collections via Shopify GraphQL API
	 *
	 * @param string $search_term The search term to use.
	 * @return array|WP_Error Array of matched collections or WP_Error.
	 */
	public function search_collections( $search_term ) {
		// Exit early if no credentials.
		if ( empty( $this->shop_url ) || empty( $this->access_token ) ) {
			return new WP_Error(
				'missing_credentials',
				__( 'Shopify API credentials not configured. Please configure them in Settings.', 'products-showcase' )
			);
		}

		// Strip quotes/backslashes so the term can't break Shopify's search syntax;
		// the term itself is passed as a GraphQL variable, never interpolated.
		$search_term = trim( str_replace( array( '"', '\\' ), '', $search_term ) );

		// Note: productsCount is a Count object in API 2025-10+, needs subfield selection.
		$query = 'query ProdshowSearchCollections($search: String!) {
			collections(first: 10, query: $search) {
				edges {
					node {
						id
						title
						handle
						image {
							url(transform: {maxWidth: 50, maxHeight: 50})
						}
						productsCount {
							count
						}
					}
				}
			}
		}';

		$response = $this->execute_graphql_query( $query, array( 'search' => 'title:*' . $search_term . '*' ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$collections = array();
		if ( isset( $response['data']['collections']['edges'] ) && is_array( $response['data']['collections']['edges'] ) ) {
			foreach ( $response['data']['collections']['edges'] as $edge ) {
				$node          = $edge['node'];
				// In API 2025-10+, productsCount is a Count object with a 'count' field
				$products_count = isset( $node['productsCount']['count'] ) 
					? $node['productsCount']['count'] 
					: ( isset( $node['productsCount'] ) ? $node['productsCount'] : 0 );
				
				$collections[] = array(
					'id'            => $node['id'],
					'title'         => $node['title'],
					'handle'        => $node['handle'],
					'image'         => isset( $node['image']['url'] ) ? $node['image']['url'] : '',
					'productsCount' => $products_count,
				);
			}
		}

		return $collections;
	}

	/**
	 * Fetch product data from Shopify
	 *
	 * @param string $product_id The Shopify product ID.
	 * @return array|null Product data or null if not found.
	 */
	public function fetch_product_data( $product_id ) {
		if ( empty( $product_id ) ) {
			return null;
		}

		// Ensure product ID is properly formatted.
		if ( strpos( $product_id, 'gid://' ) === false ) {
			$product_id = "gid://shopify/Product/{$product_id}";
		}

		// Check for cached data.
		$cache_key   = $this->get_cache_key( 'product_' . md5( $product_id ) );
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		// Fetch from API. The id is passed as a GraphQL variable, never
		// interpolated into the query string.
		$query = 'query ProdshowGetProduct($id: ID!) {
			product(id: $id) {' . self::PRODUCT_FIELDS . '
			}
		}';

		$response = $this->execute_graphql_query( $query, array( 'id' => $product_id ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		if ( empty( $response['data']['product'] ) ) {
			return null;
		}

		$result = $this->normalize_product( $response['data']['product'] );

		// Cache the result.
		set_transient( $cache_key, $result, $this->get_cache_duration() );

		return $result;
	}

	/**
	 * Fetch multiple products in batched nodes() calls
	 *
	 * Products already in the cache are served from it; the rest are fetched
	 * in batches of BATCH_SIZE per API call instead of one call per product.
	 *
	 * @param string[] $product_ids Shopify product IDs (numeric or full gid).
	 * @return array Normalized product data arrays, in the requested order.
	 */
	public function fetch_products_data( $product_ids ) {
		// Normalize to gids, drop empties and duplicates, keep the requested order.
		$gids = array();
		foreach ( (array) $product_ids as $product_id ) {
			if ( empty( $product_id ) || ! is_string( $product_id ) ) {
				continue;
			}

			if ( strpos( $product_id, 'gid://' ) === false ) {
				$product_id = "gid://shopify/Product/{$product_id}";
			}

			if ( ! in_array( $product_id, $gids, true ) ) {
				$gids[] = $product_id;
			}
		}

		if ( empty( $gids ) ) {
			return array();
		}

		$products = array();
		$missing  = array();

		foreach ( $gids as $gid ) {
			$cached = get_transient( $this->get_cache_key( 'product_' . md5( $gid ) ) );

			if ( false !== $cached ) {
				$products[ $gid ] = $cached;
			} else {
				$missing[] = $gid;
			}
		}

		foreach ( array_chunk( $missing, self::BATCH_SIZE ) as $chunk ) {
			$fetched = $this->fetch_products_batch( $chunk );

			if ( false === $fetched ) {
				// Batch failed (e.g. cost limit on unusually heavy products):
				// fall back to fetching this chunk one product at a time.
				foreach ( $chunk as $gid ) {
					$product_data = $this->fetch_product_data( $gid );
					if ( $product_data ) {
						$products[ $gid ] = $product_data;
					}
				}
				continue;
			}

			$cache_duration = $this->get_cache_duration();

			foreach ( $fetched as $gid => $product_data ) {
				$products[ $gid ] = $product_data;
				set_transient( $this->get_cache_key( 'product_' . md5( $gid ) ), $product_data, $cache_duration );
			}
		}

		// Return in the requested order, skipping products that don't exist.
		$ordered = array();
		foreach ( $gids as $gid ) {
			if ( isset( $products[ $gid ] ) ) {
				$ordered[] = $products[ $gid ];
			}
		}

		return $ordered;
	}

	/**
	 * Fetch one batch of products via nodes()
	 *
	 * @param string[] $gids Product gids.
	 * @return array|false Map of gid => normalized product data, false on API error.
	 */
	private function fetch_products_batch( $gids ) {
		$query = 'query ProdshowGetProducts($ids: [ID!]!) {
			nodes(ids: $ids) {
				... on Product {' . self::PRODUCT_FIELDS . '
				}
			}
		}';

		$response = $this->execute_graphql_query( $query, array( 'ids' => array_values( $gids ) ) );

		if ( is_wp_error( $response ) || ! isset( $response['data']['nodes'] ) || ! is_array( $response['data']['nodes'] ) ) {
			return false;
		}

		$products = array();

		foreach ( $response['data']['nodes'] as $node ) {
			// Unknown ids come back as null, non-product ids as empty objects.
			if ( empty( $node['id'] ) ) {
				continue;
			}

			$products[ $node['id'] ] = $this->normalize_product( $node );
		}

		return $products;
	}

	/**
	 * Normalize a raw GraphQL product node into the plugin's product shape
	 *
	 * Single source of truth for the structure consumed by the templates,
	 * used by the single, batched, and collection fetch paths alike.
	 *
	 * @param array $product_data Raw product node from the GraphQL response.
	 * @return array Normalized product data.
	 */
	private function normalize_product( $product_data ) {
		$result = array(
			'id'                    => $product_data['id'] ?? '',
			'title'                 => $product_data['title'] ?? '',
			'handle'                => $product_data['handle'] ?? '',
			'description'           => $product_data['description'] ?? '',
			'productType'           => $product_data['productType'] ?? '',
			'hasOutOfStockVariants' => $product_data['hasOutOfStockVariants'] ?? false,
			'status'                => $product_data['status'] ?? '',
			'priceRange'            => $product_data['priceRangeV2'] ?? array(),
			'options'               => $product_data['options'] ?? array(),
			'images'                => array(
				'edges' => array(),
			),
			'variants'              => array(
				'edges' => array(),
			),
		);

		// Process images.
		if ( ! empty( $product_data['images']['edges'] ) ) {
			foreach ( $product_data['images']['edges'] as $edge ) {
				$result['images']['edges'][] = array(
					'node' => array(
						'id'          => $edge['node']['id'] ?? '',
						'originalSrc' => $edge['node']['url'] ?? '',
						'altText'     => $edge['node']['altText'] ?? '',
					),
				);
			}
		}

		// Variant prices come back as bare Money strings in the shop currency;
		// take the currency code from the product's price range.
		$variant_currency = $product_data['priceRangeV2']['minVariantPrice']['currencyCode'] ?? 'USD';

		// Process variants.
		if ( ! empty( $product_data['variants']['edges'] ) ) {
			foreach ( $product_data['variants']['edges'] as $edge ) {
				$node    = $edge['node'];
				$variant = array(
					'id'              => $node['id'] ?? '',
					'title'           => $node['title'] ?? '',
					'price'           => array(
						'amount'       => $node['price'] ?? '0',
						'currencyCode' => $variant_currency,
					),
					'compareAtPrice'  => null,
					'sku'             => $node['sku'] ?? '',
					'image'           => null,
					'selectedOptions' => $node['selectedOptions'] ?? array(),
				);

				if ( ! empty( $node['compareAtPrice'] ) ) {
					$variant['compareAtPrice'] = array(
						'amount'       => $node['compareAtPrice'],
						'currencyCode' => $variant_currency,
					);
				}

				if ( ! empty( $node['image']['url'] ) ) {
					$variant['image'] = array(
						'url'     => $node['image']['url'],
						'altText' => $node['image']['altText'] ?? '',
					);
				}

				$result['variants']['edges'][] = array( 'node' => $variant );
			}
		}

		return $result;
	}

	/**
	 * Fetch collection products from Shopify
	 *
	 * @param string $collection_id The Shopify collection ID.
	 * @param int    $limit Number of products to fetch.
	 * @return array Array of products.
	 */
	public function fetch_collection_products( $collection_id, $limit = 12 ) {
		if ( empty( $collection_id ) ) {
			return array();
		}

		// Ensure collection ID is properly formatted.
		if ( strpos( $collection_id, 'gid://' ) === false ) {
			$collection_id = "gid://shopify/Collection/{$collection_id}";
		}

		// Check for cached data.
		$cache_key   = $this->get_cache_key( 'collection_products_' . md5( $collection_id . $limit ) );
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		// Lighter selection than PRODUCT_FIELDS (fewer images/variants) so a
		// full collection page stays within Shopify's per-query cost limit.
		$query = 'query ProdshowGetCollectionProducts($id: ID!, $first: Int!) {
			collection(id: $id) {
				products(first: $first) {
					edges {
						node {
							id
							title
							handle
							description
							productType
							hasOutOfStockVariants
							status
							priceRangeV2 {
								minVariantPrice {
									amount
									currencyCode
								}
							}
							images(first: 5) {
								edges {
									node {
										id
										url
										altText
									}
								}
							}
							options {
								id
								name
								values
								optionValues {
									swatch {
										color
										image {
											alt
											image {
												url
											}
										}
									}
								}
							}
							variants(first: 20) {
								edges {
									node {
										id
										title
										price
										compareAtPrice
										image {
											url
											altText
										}
										selectedOptions {
											name
											value
										}
									}
								}
							}
						}
					}
				}
			}
		}';

		$response = $this->execute_graphql_query(
			$query,
			array(
				'id'    => $collection_id,
				'first' => absint( $limit ),
			)
		);

		if ( is_wp_error( $response ) || empty( $response['data']['collection']['products']['edges'] ) ) {
			return array();
		}

		$products = array();
		foreach ( $response['data']['collection']['products']['edges'] as $edge ) {
			$products[] = $this->normalize_product( $edge['node'] );
		}

		// Cache the result.
		set_transient( $cache_key, $products, $this->get_cache_duration() );

		return $products;
	}

	/**
	 * Fetch collection data from Shopify
	 *
	 * @param string $collection_id The Shopify collection ID.
	 * @return array|null Collection data or null if not found.
	 */
	private function fetch_collection_data( $collection_id ) {
		if ( empty( $collection_id ) ) {
			return null;
		}

		// Ensure collection ID is properly formatted.
		if ( strpos( $collection_id, 'gid://' ) === false ) {
			$collection_id = "gid://shopify/Collection/{$collection_id}";
		}

		$query = 'query ProdshowGetCollection($id: ID!) {
			collection(id: $id) {
				id
				title
				handle
				image {
					url
				}
			}
		}';

		$response = $this->execute_graphql_query( $query, array( 'id' => $collection_id ) );

		if ( is_wp_error( $response ) || empty( $response['data']['collection'] ) ) {
			return null;
		}

		$collection = $response['data']['collection'];

		return array(
			'id'     => $collection['id'],
			'title'  => $collection['title'],
			'handle' => $collection['handle'],
			'image'  => isset( $collection['image']['url'] ) ? $collection['image']['url'] : '',
		);
	}

	/**
	 * Get the store's primary (storefront) domain
	 *
	 * Returns the custom domain connected to the store when one exists,
	 * otherwise the myshopify.com domain. Shopify redirects myshopify.com
	 * storefront URLs to the primary domain anyway; linking to it directly
	 * keeps the visible URL on the merchant's own domain. The Admin API
	 * itself always uses the stored myshopify.com URL.
	 *
	 * @return string Domain host without scheme (e.g. "shop.example.com"),
	 *                or an empty string when no store is configured.
	 */
	public function get_primary_domain() {
		if ( empty( $this->shop_url ) ) {
			return '';
		}

		// Key includes the store so a reconnect to a different shop can't
		// serve a stale domain. Prefixed prodshow_shopify_ so the existing
		// cache-clear and uninstall routines cover it.
		$cache_key = $this->get_cache_key( 'primary_domain_' . md5( $this->shop_url ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$domain   = $this->shop_url;
		$duration = HOUR_IN_SECONDS; // Short retry window when the lookup fails.

		$response = $this->execute_graphql_query( '{ shop { primaryDomain { host } } }' );

		if ( ! is_wp_error( $response ) && ! empty( $response['data']['shop']['primaryDomain']['host'] ) ) {
			$domain   = $response['data']['shop']['primaryDomain']['host'];
			$duration = DAY_IN_SECONDS;
		}

		set_transient( $cache_key, $domain, $duration );

		return $domain;
	}

	/**
	 * Build a versioned cache (transient) key
	 *
	 * Keys embed a version number that clear_cache() increments. Incrementing
	 * invalidates every cached entry at once — including on hosts with an
	 * external object cache (Redis/Memcached), where deleting transient rows
	 * from the options table has no effect. Old entries expire via their TTL.
	 *
	 * @param string $suffix Key suffix identifying the cached resource.
	 * @return string Transient name.
	 */
	private function get_cache_key( $suffix ) {
		$version = (int) get_option( 'prodshow_cache_version', 1 );

		return 'prodshow_shopify_v' . $version . '_' . $suffix;
	}

	/**
	 * Clear all cached Shopify data
	 *
	 * Works on every host type: bumping the cache version invalidates
	 * object-cached transients, and the direct deletes immediately free up
	 * the options table on database-backed hosts.
	 */
	public static function clear_cache() {
		$version = (int) get_option( 'prodshow_cache_version', 1 );
		update_option( 'prodshow_cache_version', $version + 1, true );

		// The connection-status cache has a fixed name; delete it explicitly
		// so a reconnect or cache clear re-tests immediately.
		delete_transient( 'prodshow_shopify_connection_status' );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache, no caching needed.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_prodshow_shopify_%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache, no caching needed.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_prodshow_shopify_%'" );
	}

	/**
	 * Get the product cache duration in seconds
	 *
	 * @return int Cache duration in seconds.
	 */
	private function get_cache_duration() {
		/**
		 * Filter how long fetched Shopify product data is cached.
		 *
		 * @param int $duration Cache duration in seconds. Defaults to the
		 *                      value of the settings option (1 hour if unset).
		 */
		return max( MINUTE_IN_SECONDS, (int) apply_filters( 'prodshow_cache_duration', get_option( 'prodshow_cache_duration', HOUR_IN_SECONDS ) ) );
	}

	/**
	 * Execute a GraphQL query against the Shopify Admin API
	 *
	 * @param string $query     The GraphQL query to execute.
	 * @param array  $variables Optional GraphQL variables. Dynamic values must
	 *                          always be passed here, never interpolated into
	 *                          the query string.
	 * @return array|WP_Error The response data or WP_Error on failure.
	 */
	private function execute_graphql_query( $query, $variables = array() ) {
		// Validate credentials first
		if ( empty( $this->shop_url ) || empty( $this->access_token ) ) {
			return new WP_Error( 'missing_credentials', __( 'Shopify API credentials not configured', 'products-showcase' ) );
		}

		$url = "https://{$this->shop_url}/admin/api/" . PRODSHOW_SHOPIFY_API_VERSION . "/graphql.json";

		$payload = array( 'query' => $query );
		if ( ! empty( $variables ) ) {
			$payload['variables'] = $variables;
		}

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'X-Shopify-Access-Token' => $this->access_token,
					'Content-Type'           => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error(
				'http_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'HTTP Error: %d', 'products-showcase' ),
					$status_code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'invalid_json', __( 'Invalid JSON response from Shopify API', 'products-showcase' ) );
		}

		if ( isset( $data['errors'] ) ) {
			$error_message = is_array( $data['errors'] ) && isset( $data['errors'][0]['message'] )
				? $data['errors'][0]['message']
				: __( 'Unknown GraphQL error', 'products-showcase' );

			return new WP_Error( 'graphql_error', $error_message );
		}

		return $data;
	}
}

