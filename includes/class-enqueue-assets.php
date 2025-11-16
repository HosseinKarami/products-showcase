<?php
/**
 * Asset Enqueue Handler
 *
 * Handles enqueuing of frontend CSS and JavaScript assets
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PRODSHOW_Enqueue_Assets class
 */
class PRODSHOW_Enqueue_Assets {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'register_view_script_dependencies' ) );
	}

	/**
	 * Register view script with proper dependencies
	 */
	public function register_view_script_dependencies() {
		// Get the auto-generated asset file
		$asset_file = PRODSHOW_PLUGIN_DIR . 'build/view.asset.php';
		
		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;
			
			// Add embla-carousel as a dependency
			if ( ! in_array( 'embla-carousel', $asset['dependencies'], true ) ) {
				$asset['dependencies'][] = 'embla-carousel';
			}
			
			// Re-register the script with updated dependencies
			wp_register_script(
				'products-showcase-products-view-script',
				PRODSHOW_PLUGIN_URL . 'build/view.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);
		}
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Check if we're on a page with the Shopify block.
		global $post;
		if ( ! $post || ! has_block( 'acf/shopify-block', $post ) ) {
			return;
		}

		// Enqueue Embla Carousel (bundled locally for WordPress.org compliance).
		wp_enqueue_script(
			'embla-carousel',
			PRODSHOW_PLUGIN_URL . 'assets/js/vendor/embla-carousel-8.0.0.umd.js',
			array(),
			'8.0.0',
			true
		);

		// Frontend script (view.js) is automatically enqueued from build/view.js
		// via WordPress block registration (see src/view.js and block.json viewScript)
		// The script handles carousel initialization and product hover interactions.

		// Block styles are automatically enqueued from build/style-index.css
		// via WordPress block registration (see src/style.scss)

		// Pass shop URL to frontend script
		if ( wp_script_is( 'products-showcase-products-view-script', 'registered' ) ) {
			wp_localize_script(
				'products-showcase-products-view-script',
				'prodshowBlockVars',
				array(
					'shopUrl' => get_option( 'prodshow_shopify_url', '' ) ? 'https://' . get_option( 'prodshow_shopify_url', '' ) : '',
				)
			);
		}
	}
}

