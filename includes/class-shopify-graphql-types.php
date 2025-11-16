<?php
/**
 * Shopify GraphQL Types
 *
 * Registers GraphQL types for WPGraphQL integration (optional)
 * Note: This is only needed if you want to expose Shopify data via GraphQL
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PRODSHOW_Shopify_GraphQL_Types class
 */
class PRODSHOW_Shopify_GraphQL_Types {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Only register if WPGraphQL is active.
		if ( ! class_exists( 'WPGraphQL' ) ) {
			return;
		}

		add_action( 'graphql_register_types', array( $this, 'register_types' ) );
	}

	/**
	 * Register GraphQL types
	 */
	public function register_types() {
		// Register basic Shopify types for GraphQL queries
		// This allows headless/decoupled WordPress setups to query Shopify data

		// Note: Due to the extensive nature of GraphQL types, this is a simplified version
		// For full WPGraphQL integration, you would register all the Shopify types here
		// similar to how they were registered in ShopifyBlockType.php

		/* Example registration:
		register_graphql_object_type(
			'ShopifyProduct',
			array(
				'description' => __( 'A Shopify product', 'products-showcase' ),
				'fields'      => array(
					'id'     => array( 'type' => 'String' ),
					'title'  => array( 'type' => 'String' ),
					'handle' => array( 'type' => 'String' ),
					// ... more fields
				),
			)
		);
		*/

		// Since this plugin uses ACF blocks primarily, GraphQL integration is optional
		// and can be extended based on your needs.
	}
}

