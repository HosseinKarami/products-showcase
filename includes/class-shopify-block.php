<?php
/**
 * Shopify Block Registration
 *
 * Handles native Gutenberg block registration (no ACF required)
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SPS_Shopify_Block class
 */
class SPS_Shopify_Block {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the Shopify Products block
	 */
	public function register_block() {
		// Register the block using block.json from build directory
		register_block_type(
			SPS_PLUGIN_DIR . 'build',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render block callback (server-side rendering)
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML output.
	 */
	public function render_block( $attributes ) {
		// Get attribute values with defaults
		$title                  = $attributes['title'] ?? '';
		$description            = $attributes['description'] ?? '';
		$content_type           = $attributes['contentType'] ?? 'products';
		$product_list           = $attributes['productList'] ?? array();
		$collection_id          = $attributes['collectionId'] ?? '';
		$product_limit          = $attributes['productLimit'] ?? 12;
		$disable_global_padding = $attributes['disableGlobalPadding'] ?? false;
		$cta_button             = $attributes['ctaButton'] ?? array();
		$colors                 = $attributes['colors'] ?? array();
		
		// Extract CTA button properties
		$cta_button_url         = $cta_button['url'] ?? '';
		$cta_button_text        = $cta_button['title'] ?? '';
		$cta_button_new_tab     = $cta_button['opensInNewTab'] ?? false;

		// Get Shopify API instance
		$shopify_api = new SPS_Shopify_API();

		// Fetch product data
		$products            = array();
		$collection_products = array();

		if ( 'products' === $content_type && ! empty( $product_list ) ) {
			// Fetch individual products
			foreach ( $product_list as $item ) {
				$product_id = $item['productId'] ?? '';
				
				if ( empty( $product_id ) ) {
					continue;
				}
				
				$product_data = $shopify_api->fetch_product_data( $product_id );
				
				if ( $product_data ) {
					$products[] = $product_data;
				}
			}
		} elseif ( 'collection' === $content_type && ! empty( $collection_id ) ) {
			// Fetch collection products
			$collection_products = $shopify_api->fetch_collection_products( $collection_id, $product_limit );
		}

		// Display logic
		$display_products = ( 'products' === $content_type ) ? $products : $collection_products;

		// Don't filter out products in editor preview - show all for testing
		if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			// Filter out inactive or out-of-stock products (only on frontend)
			// Note: ARCHIVED products are allowed through since they may still be useful to display
			$display_products = array_filter(
				$display_products,
				function( $product ) {
					$status = $product['status'] ?? 'ACTIVE';
					// Allow ACTIVE and ARCHIVED, but not DRAFT
					return in_array( $status, array( 'ACTIVE', 'ARCHIVED' ), true );
				}
			);
		}

		if ( empty( $display_products ) ) {
			// Return preview placeholder in editor, nothing on frontend
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return $this->render_placeholder_preview( $title, $description, $content_type, $cta_button );
			}
			return '';
		}

		// Enqueue frontend assets
		$this->enqueue_block_assets();

		// Create unique ID for this block instance
		$block_id = 'sps-block-' . wp_unique_id();

		// Start output buffering
		ob_start();

		// Include template
		include SPS_PLUGIN_DIR . 'templates/block-template.php';

		return ob_get_clean();
	}

	/**
	 * Enqueue block assets
	 */
	private function enqueue_block_assets() {
		// Enqueue Embla Carousel (bundled locally for WordPress.org compliance)
		wp_enqueue_script(
			'embla-carousel',
			SPS_PLUGIN_URL . 'assets/js/vendor/embla-carousel.umd.js',
			array(),
			'8.6.0',
			true
		);

		// Note: Block frontend script (view.js) and styles (style-index.css) are 
		// automatically enqueued via WordPress block registration from build/ directory.
		// See block.json for viewScript and style configuration.
	}

	/**
	 * Render placeholder preview for editor
	 * Shows mockup of what the block will look like with products
	 *
	 * @param string $title Block title.
	 * @param string $description Block description.
	 * @param string $content_type Content type (products or collection).
	 * @param array  $cta_button CTA button data.
	 * @return string Placeholder HTML.
	 */
	private function render_placeholder_preview( $title, $description, $content_type, $cta_button ) {
		$default_title = $title ?: 'Your Product Showcase';
		$default_desc  = $description ?: 'Add products or a collection to see them displayed here';
		$cta_text      = $cta_button['title'] ?? 'View All';
		
		// Enqueue the actual frontend CSS so placeholder matches real styling
		$this->enqueue_block_assets();
		
		ob_start();
		?>
		<section class="sps-shopify-block" style="position: relative;">
			<!-- Instructions Overlay -->
			<div style="
				position: absolute;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background: rgba(255, 255, 255, 0.95);
				border: 2px dashed #cbd5e1;
				border-radius: 8px;
				padding: 24px 32px;
				z-index: 10;
				text-align: center;
				box-shadow: 0 4px 12px rgba(0,0,0,0.15);
				max-width: 90%;
			">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" style="display: block; margin: 0 auto 12px;">
					<circle cx="12" cy="12" r="10" stroke="#3b82f6" stroke-width="2"/>
					<path d="M12 16v-4M12 8h.01" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<p style="
					font-size: 14px;
					color: #1a202c;
					margin: 0 0 8px;
					font-weight: 600;
				">
					No products selected yet
				</p>
				<p style="
					font-size: 13px;
					color: #4a5568;
					margin: 0;
					line-height: 1.5;
				">
					<?php if ( 'products' === $content_type ) : ?>
						Use the sidebar to search and add products →
					<?php else : ?>
						Use the sidebar to select a collection →
					<?php endif; ?>
				</p>
			</div>

			<!-- Actual Block Structure (dimmed) -->
			<div class="sps-container" style="opacity: 0.4; pointer-events: none;">
				<div class="sps-header">
					<div>
						<h2 class="sps-title">
							<?php echo esc_html( $default_title ); ?>
						</h2>
						<?php if ( $description ) : ?>
							<p class="sps-description">
								<?php echo esc_html( $default_desc ); ?>
							</p>
						<?php endif; ?>
					</div>
					
					<?php if ( ! empty( $cta_text ) ) : ?>
						<span class="sps-cta-button" style="cursor: not-allowed;">
							<?php echo esc_html( $cta_text ); ?>
						</span>
					<?php endif; ?>
				</div>

				<div class="sps-carousel">
					<div class="sps-carousel-viewport">
						<div class="sps-carousel-container">
							<?php for ( $i = 0; $i < 4; $i++ ) : ?>
							<div class="sps-product-card">
								<div class="sps-product-image-wrapper">
									<div class="sps-product-image-placeholder" style="
										background: linear-gradient(135deg, #F5F5F5 0%, #E5E5E5 100%);
										display: flex;
										align-items: center;
										justify-content: center;
									">
										<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
											<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
											<circle cx="8.5" cy="8.5" r="1.5"/>
											<polyline points="21 15 16 10 5 21"/>
										</svg>
									</div>
								</div>

								<div class="sps-product-content">
									<h3 class="sps-product-title">
										<span style="
											display: block;
											height: 20px;
											background: #e5e7eb;
											border-radius: 4px;
											width: 80%;
										"></span>
									</h3>

									<div class="sps-product-meta">
										<div class="sps-product-swatches">
											<?php for ( $j = 0; $j < 3; $j++ ) : ?>
											<span class="sps-swatch" style="background-color: #e5e7eb;"></span>
											<?php endfor; ?>
										</div>

										<p class="sps-product-price">
											<span style="
												display: inline-block;
												height: 14px;
												width: 60px;
												background: #e5e7eb;
												border-radius: 4px;
												vertical-align: middle;
											"></span>
										</p>
									</div>
								</div>
							</div>
							<?php endfor; ?>
						</div>
					</div>

					<div class="sps-carousel-controls">
						<button class="sps-carousel-btn sps-carousel-prev" disabled>
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none">
								<rect width="40" height="40" rx="20" fill="#F5F5F5"/>
								<path d="M23 13L16 20L23 27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
						<button class="sps-carousel-btn sps-carousel-next" disabled>
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none">
								<rect width="40" height="40" rx="20" fill="#F5F5F5"/>
								<path d="M17 13L24 20L17 27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
					</div>
				</div>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
