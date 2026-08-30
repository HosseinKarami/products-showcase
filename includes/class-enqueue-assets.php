<?php
/**
 * Asset Enqueue Handler
 *
 * Registers the block view script with the Embla Carousel dependency so the
 * carousel library always loads before view.js. The actual frontend enqueues
 * happen at render time in PRODSHOW_Shopify_Block::enqueue_block_assets().
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

}

