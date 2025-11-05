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
 * SPS_Shopify_API class
 */
class SPS_Shopify_API {
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
		$this->shop_url      = get_option( 'sps_shopify_url', '' );
		$this->access_token  = get_option( 'sps_shopify_access_token', '' );

		// Initialize hooks.
		add_action( 'wp_ajax_sps_search_shopify_products', array( $this, 'ajax_search_products' ) );
		add_action( 'wp_ajax_sps_search_shopify_collections', array( $this, 'ajax_search_collections' ) );
		add_action( 'wp_ajax_sps_get_shopify_product', array( $this, 'ajax_get_product' ) );
		add_action( 'wp_ajax_sps_get_shopify_collection', array( $this, 'ajax_get_collection' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only enqueue on block editor screens.
		$screen = get_current_screen();
		if ( ! $screen || ! method_exists( $screen, 'is_block_editor' ) || ! $screen->is_block_editor() ) {
			return;
		}

		// Enqueue jQuery UI for autocomplete.
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_style(
			'jquery-ui-style',
			SPS_PLUGIN_URL . 'assets/css/vendor/jquery-ui.min.css',
			array(),
			'1.13.2'
		);

		// Enqueue admin script.
		wp_enqueue_script(
			'sps-admin',
			SPS_PLUGIN_URL . 'assets/admin/admin.js',
			array( 'jquery', 'jquery-ui-autocomplete' ),
			SPS_VERSION,
			true
		);

		// Pass variables to script.
		wp_localize_script(
			'sps-admin',
			'spsAdminVars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'sps_shopify_search' ),
				'shop_url' => $this->shop_url,
			)
		);
	}

	/**
	 * AJAX handler for searching Shopify products
	 */
	public function ajax_search_products() {
		// Security check.
		check_ajax_referer( 'sps_shopify_search', 'nonce' );

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
		check_ajax_referer( 'sps_shopify_search', 'nonce' );

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
		check_ajax_referer( 'sps_shopify_search', 'nonce' );

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
		check_ajax_referer( 'sps_shopify_search', 'nonce' );

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

		$search_term = str_replace( '"', '', $search_term );

		$query = sprintf(
			'{
				products(first: 10, query: "title:*%s*") {
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
			}',
			esc_html( $search_term )
		);

		$response = $this->execute_graphql_query( $query );

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

		// Sanitize search term - remove quotes and escape for GraphQL
		$search_term = str_replace( array( '"', '\\' ), '', $search_term );
		$search_term = trim( $search_term );

		// Build GraphQL query with proper escaping
		// Note: productsCount is a Count object in API 2025-10+, needs subfield selection
		$query = sprintf(
			'{
				collections(first: 10, query: "title:*%s*") {
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
			}',
			addslashes( $search_term )
		);

		$response = $this->execute_graphql_query( $query );

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
		$cache_key   = 'sps_shopify_product_' . md5( $product_id );
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		// Fetch from API.
		$query = sprintf(
			'{
				product(id: "%s") {
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
					}
				}
			}',
			esc_html( $product_id )
		);

		$response = $this->execute_graphql_query( $query );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		if ( empty( $response['data']['product'] ) ) {
			return null;
		}

		$product_data = $response['data']['product'];

		// Transform to expected format.
		$result = array(
			'id'                    => $product_data['id'],
			'title'                 => $product_data['title'],
			'handle'                => $product_data['handle'],
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

		// Process variants.
		if ( ! empty( $product_data['variants']['edges'] ) ) {
			foreach ( $product_data['variants']['edges'] as $edge ) {
				$node    = $edge['node'];
				$variant = array(
					'id'              => $node['id'] ?? '',
					'title'           => $node['title'] ?? '',
					'price'           => array(
						'amount'       => $node['price'] ?? '0',
						'currencyCode' => 'USD',
					),
					'compareAtPrice'  => null,
					'sku'             => $node['sku'] ?? '',
					'image'           => null,
					'selectedOptions' => $node['selectedOptions'] ?? array(),
				);

				if ( ! empty( $node['compareAtPrice'] ) ) {
					$variant['compareAtPrice'] = array(
						'amount'       => $node['compareAtPrice'],
						'currencyCode' => 'USD',
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

		// Cache the result.
		$cache_duration = get_option( 'sps_cache_duration', HOUR_IN_SECONDS );
		set_transient( $cache_key, $result, $cache_duration );

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
		$cache_key   = 'sps_shopify_collection_products_' . md5( $collection_id . $limit );
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$query = sprintf(
			'{
				collection(id: "%s") {
					products(first: %d) {
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
			}',
			esc_html( $collection_id ),
			absint( $limit )
		);

		$response = $this->execute_graphql_query( $query );

		if ( is_wp_error( $response ) || empty( $response['data']['collection']['products']['edges'] ) ) {
			return array();
		}

		$products = array();
		foreach ( $response['data']['collection']['products']['edges'] as $edge ) {
			$product_data = $edge['node'];

			// Transform to expected format (similar to fetch_product_data).
			$product = array(
				'id'                    => $product_data['id'],
				'title'                 => $product_data['title'],
				'handle'                => $product_data['handle'],
				'description'           => $product_data['description'] ?? '',
				'productType'           => $product_data['productType'] ?? '',
				'hasOutOfStockVariants' => $product_data['hasOutOfStockVariants'] ?? false,
				'status'                => $product_data['status'] ?? '',
				'priceRange'            => $product_data['priceRangeV2'] ?? array(),
				'options'               => $product_data['options'] ?? array(),
				'images'                => array( 'edges' => array() ),
				'variants'              => array( 'edges' => array() ),
			);

			// Process images.
			if ( ! empty( $product_data['images']['edges'] ) ) {
				foreach ( $product_data['images']['edges'] as $img_edge ) {
					$product['images']['edges'][] = array(
						'node' => array(
							'id'          => $img_edge['node']['id'] ?? '',
							'originalSrc' => $img_edge['node']['url'] ?? '',
							'altText'     => $img_edge['node']['altText'] ?? '',
						),
					);
				}
			}

			// Process variants.
			if ( ! empty( $product_data['variants']['edges'] ) ) {
				foreach ( $product_data['variants']['edges'] as $var_edge ) {
					$var_node = $var_edge['node'];
					$variant  = array(
						'id'              => $var_node['id'] ?? '',
						'title'           => $var_node['title'] ?? '',
						'price'           => array(
							'amount'       => $var_node['price'] ?? '0',
							'currencyCode' => 'USD',
						),
						'image'           => null,
						'selectedOptions' => $var_node['selectedOptions'] ?? array(),
					);

					if ( ! empty( $var_node['image']['url'] ) ) {
						$variant['image'] = array(
							'url'     => $var_node['image']['url'],
							'altText' => $var_node['image']['altText'] ?? '',
						);
					}

					$product['variants']['edges'][] = array( 'node' => $variant );
				}
			}

			$products[] = $product;
		}

		// Cache the result.
		$cache_duration = get_option( 'sps_cache_duration', HOUR_IN_SECONDS );
		set_transient( $cache_key, $products, $cache_duration );

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

		$query = sprintf(
			'{
				collection(id: "%s") {
					id
					title
					handle
					image {
						url
					}
				}
			}',
			esc_html( $collection_id )
		);

		$response = $this->execute_graphql_query( $query );

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
	 * Execute a GraphQL query against the Shopify Admin API
	 *
	 * @param string $query The GraphQL query to execute.
	 * @return array|WP_Error The response data or WP_Error on failure.
	 */
	private function execute_graphql_query( $query ) {
		// Validate credentials first
		if ( empty( $this->shop_url ) || empty( $this->access_token ) ) {
			return new WP_Error( 'missing_credentials', __( 'Shopify API credentials not configured', 'products-showcase' ) );
		}

		$url = "https://{$this->shop_url}/admin/api/" . SPS_SHOPIFY_API_VERSION . "/graphql.json";

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'X-Shopify-Access-Token' => $this->access_token,
					'Content-Type'           => 'application/json',
				),
				'body'    => wp_json_encode( array( 'query' => $query ) ),
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

