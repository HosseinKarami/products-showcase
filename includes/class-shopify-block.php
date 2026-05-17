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
 * PRODSHOW_Shopify_Block class
 */
class PRODSHOW_Shopify_Block {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'init', array( $this, 'register_shortcode' ) );
	}

	/**
	 * Register the [products_showcase] shortcode
	 *
	 * Allows the carousel to be used in the Classic editor, widgets,
	 * and page builders without the block editor.
	 */
	public function register_shortcode() {
		add_shortcode( 'products_showcase', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Register the Shopify Products block
	 */
	public function register_block() {
		// Register the block using block.json from build directory
		register_block_type(
			PRODSHOW_PLUGIN_DIR . 'build',
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
		$shopify_api = new PRODSHOW_Shopify_API();

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
		$block_id = 'prodshow-block-' . wp_unique_id();
		
		// Generate unique class for button styles
		$button_class = 'prodshow-cta-button-' . substr( md5( $block_id ), 0, 8 );
		
		// Add inline styles for custom colors
		$this->add_block_inline_styles( $block_id, $colors, $button_class );

		// Start output buffering
		ob_start();

		// Include template
		include PRODSHOW_PLUGIN_DIR . 'templates/block-template.php';

		return ob_get_clean();
	}

	/**
	 * Render the [products_showcase] shortcode
	 *
	 * Maps shortcode attributes onto the block attribute structure and
	 * reuses the existing server-side block renderer.
	 *
	 * Examples:
	 *   [products_showcase collection="123456789"]
	 *   [products_showcase products="111,222,333" title="Featured"]
	 *
	 * Supported attributes:
	 *   collection      Shopify collection ID (numeric or full gid).
	 *   products        Comma-separated Shopify product IDs.
	 *   limit           Max products to show in collection mode (1-50, default 12).
	 *   title           Optional heading shown above the carousel.
	 *   description     Optional description shown below the title.
	 *   button_text     Optional call-to-action button label.
	 *   button_url      Call-to-action button link.
	 *   button_new_tab  Open the button link in a new tab (yes/no).
	 *   disable_padding Remove the default outer padding (yes/no).
	 *   background           Block background color (CSS color).
	 *   text_color           Block text color (CSS color).
	 *   button_bg            CTA button background color.
	 *   button_text_color    CTA button text color.
	 *   button_bg_hover      CTA button background color on hover.
	 *   button_text_hover    CTA button text color on hover.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Rendered carousel HTML.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'collection'        => '',
				'products'          => '',
				'limit'             => 12,
				'title'             => '',
				'description'       => '',
				'button_text'       => '',
				'button_url'        => '',
				'button_new_tab'    => 'no',
				'disable_padding'   => 'no',
				'background'        => '',
				'text_color'        => '',
				'button_bg'         => '',
				'button_text_color' => '',
				'button_bg_hover'   => '',
				'button_text_hover' => '',
			),
			$atts,
			'products_showcase'
		);

		// Decide content type from the provided attributes.
		$collection_id = trim( (string) $atts['collection'] );
		$products_raw  = trim( (string) $atts['products'] );

		if ( '' !== $collection_id ) {
			$content_type = 'collection';
			$product_list = array();
		} else {
			$content_type = 'products';
			$product_list = array();

			if ( '' !== $products_raw ) {
				$product_ids = array_filter( array_map( 'trim', explode( ',', $products_raw ) ) );
				foreach ( $product_ids as $product_id ) {
					$product_list[] = array( 'productId' => $product_id );
				}
			}
		}

		// Clamp the collection product limit to the same bounds as the block.
		$limit = (int) $atts['limit'];
		$limit = max( 1, min( 50, $limit ) );

		$attributes = array(
			'title'                => sanitize_text_field( $atts['title'] ),
			'description'          => sanitize_text_field( $atts['description'] ),
			'contentType'          => $content_type,
			'productList'          => $product_list,
			'collectionId'         => $collection_id,
			'productLimit'         => $limit,
			'disableGlobalPadding' => $this->shortcode_bool( $atts['disable_padding'] ),
			'ctaButton'            => array(
				'url'           => esc_url_raw( $atts['button_url'] ),
				'title'         => sanitize_text_field( $atts['button_text'] ),
				'opensInNewTab' => $this->shortcode_bool( $atts['button_new_tab'] ),
			),
			'colors'               => array(
				'backgroundColor'       => sanitize_text_field( $atts['background'] ),
				'textColor'             => sanitize_text_field( $atts['text_color'] ),
				'buttonBackground'      => sanitize_text_field( $atts['button_bg'] ),
				'buttonText'            => sanitize_text_field( $atts['button_text_color'] ),
				'buttonBackgroundHover' => sanitize_text_field( $atts['button_bg_hover'] ),
				'buttonTextHover'       => sanitize_text_field( $atts['button_text_hover'] ),
			),
		);

		return $this->render_block( $attributes );
	}

	/**
	 * Interpret a shortcode attribute as a boolean.
	 *
	 * @param string $value Raw attribute value.
	 * @return bool
	 */
	private function shortcode_bool( $value ) {
		return in_array( strtolower( trim( (string) $value ) ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Enqueue block assets
	 */
	private function enqueue_block_assets() {
		// Enqueue Embla Carousel (bundled locally for WordPress.org compliance)
		wp_enqueue_script(
			'embla-carousel',
			PRODSHOW_PLUGIN_URL . 'assets/js/vendor/embla-carousel.umd.js',
			array(),
			'8.6.0',
			true
		);

		// When the carousel is rendered as a block, WordPress auto-enqueues the
		// block stylesheet and view script from the build/ directory. When it is
		// rendered via the [products_showcase] shortcode, that auto-enqueue does
		// NOT happen (no block is present in the content), so the markup loads
		// unstyled and without carousel JS. Enqueue the registered handles
		// explicitly here so both paths behave identically. Enqueuing an already
		// enqueued handle is a no-op, so this is safe for the block path too.
		$style_handle = 'products-showcase-products-style';
		if ( wp_style_is( $style_handle, 'registered' ) ) {
			wp_enqueue_style( $style_handle );
		}

		$view_handle = 'products-showcase-products-view-script';
		if ( wp_script_is( $view_handle, 'registered' ) ) {
			wp_enqueue_script( $view_handle );
			wp_localize_script(
				$view_handle,
				'prodshowBlockVars',
				array(
					'shopUrl' => get_option( 'prodshow_shopify_url', '' ) ? 'https://' . get_option( 'prodshow_shopify_url', '' ) : '',
				)
			);
		}
	}

	/**
	 * Add inline styles for block-specific customizations
	 *
	 * @param string $block_id Unique block ID.
	 * @param array  $colors Color settings.
	 * @param string $button_class Button class name.
	 */
	private function add_block_inline_styles( $block_id, $colors, $button_class ) {
		// Check if we have any button colors
		$has_button_colors = ! empty( $colors['buttonBackground'] ) || ! empty( $colors['buttonText'] ) || ! empty( $colors['buttonBackgroundHover'] ) || ! empty( $colors['buttonTextHover'] );
		
		if ( ! $has_button_colors ) {
			return;
		}

		$custom_css = '';
		
		// Build button styles
		if ( ! empty( $colors['buttonBackground'] ) || ! empty( $colors['buttonText'] ) ) {
			$custom_css .= '#' . esc_attr( $block_id ) . ' .' . esc_attr( $button_class ) . ' {';
			if ( ! empty( $colors['buttonBackground'] ) ) {
				$custom_css .= 'background-color: ' . esc_attr( $colors['buttonBackground'] ) . ' !important;';
			}
			if ( ! empty( $colors['buttonText'] ) ) {
				$custom_css .= 'color: ' . esc_attr( $colors['buttonText'] ) . ' !important;';
			}
			$custom_css .= '}';
		}
		
		// Build hover styles
		if ( ! empty( $colors['buttonBackgroundHover'] ) || ! empty( $colors['buttonTextHover'] ) ) {
			$custom_css .= '#' . esc_attr( $block_id ) . ' .' . esc_attr( $button_class ) . ':hover {';
			if ( ! empty( $colors['buttonBackgroundHover'] ) ) {
				$custom_css .= 'background-color: ' . esc_attr( $colors['buttonBackgroundHover'] ) . ' !important;';
			}
			if ( ! empty( $colors['buttonTextHover'] ) ) {
				$custom_css .= 'color: ' . esc_attr( $colors['buttonTextHover'] ) . ' !important;';
			}
			$custom_css .= '}';
		}
		
		// Add inline styles to the main block stylesheet
		// Since block styles are auto-registered, we need to add to view style handle
		$style_handle = 'products-showcase-products-style';
		if ( wp_style_is( $style_handle, 'registered' ) || wp_style_is( $style_handle, 'enqueued' ) ) {
			wp_add_inline_style( $style_handle, $custom_css );
		} else {
			// Fallback: if style isn't registered yet, register a dummy one
			wp_register_style( 'prodshow-block-inline-styles', false, array(), PRODSHOW_VERSION );
			wp_enqueue_style( 'prodshow-block-inline-styles' );
			wp_add_inline_style( 'prodshow-block-inline-styles', $custom_css );
		}
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
		<section class="prodshow-shopify-block" style="position: relative;">
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
			<div class="prodshow-container" style="opacity: 0.4; pointer-events: none;">
				<div class="prodshow-header">
					<div>
						<h2 class="prodshow-title">
							<?php echo esc_html( $default_title ); ?>
						</h2>
						<?php if ( $description ) : ?>
							<p class="prodshow-description">
								<?php echo esc_html( $default_desc ); ?>
							</p>
						<?php endif; ?>
					</div>
					
					<?php if ( ! empty( $cta_text ) ) : ?>
						<span class="prodshow-cta-button" style="cursor: not-allowed;">
							<?php echo esc_html( $cta_text ); ?>
						</span>
					<?php endif; ?>
				</div>

				<div class="prodshow-carousel">
					<div class="prodshow-carousel-viewport">
						<div class="prodshow-carousel-container">
							<?php for ( $i = 0; $i < 4; $i++ ) : ?>
							<div class="prodshow-product-card">
								<div class="prodshow-product-image-wrapper">
									<div class="prodshow-product-image-placeholder" style="
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

								<div class="prodshow-product-content">
									<h3 class="prodshow-product-title">
										<span style="
											display: block;
											height: 20px;
											background: #e5e7eb;
											border-radius: 4px;
											width: 80%;
										"></span>
									</h3>

									<div class="prodshow-product-meta">
										<div class="prodshow-product-swatches">
											<?php for ( $j = 0; $j < 3; $j++ ) : ?>
											<span class="prodshow-swatch" style="background-color: #e5e7eb;"></span>
											<?php endfor; ?>
										</div>

										<p class="prodshow-product-price">
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

					<div class="prodshow-carousel-controls">
						<button class="prodshow-carousel-btn prodshow-carousel-prev" disabled>
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none">
								<rect width="40" height="40" rx="20" fill="#F5F5F5"/>
								<path d="M23 13L16 20L23 27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
						<button class="prodshow-carousel-btn prodshow-carousel-next" disabled>
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
