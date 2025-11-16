<?php
/**
 * Block Template
 *
 * Template for rendering the Shopify Product Showcase block
 *
 * @package ProductsShowcase
 * @var array $display_products Array of products to display
 * @var string $title Block title
 * @var string $description Block description
 * @var string $block_id Unique block ID
 * @var bool $content_type True for products, false for collection
 * @var bool $disable_global_padding Whether to disable global padding
 * @var string $cta_button_text CTA button text
 * @var string $cta_button_url CTA button URL
 * @var bool $cta_button_new_tab Whether CTA opens in new tab
 * @var array $colors Color settings
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Template variables are local scope, not global variables.

$has_single_product = count( $display_products ) === 1;
$shop_url           = get_option( 'prodshow_shopify_url', '' );
$shop_base_url      = $shop_url ? 'https://' . $shop_url : 'https://shop.example.com';

// Build wrapper classes
$wrapper_classes = array( 'prodshow-shopify-block' );
if ( ! empty( $disable_global_padding ) ) {
	$wrapper_classes[] = 'prodshow-no-global-padding';
}
$wrapper_class_string = implode( ' ', $wrapper_classes );

// Build inline styles
$wrapper_styles = array();
if ( ! empty( $colors['backgroundColor'] ) ) {
	$wrapper_styles[] = 'background-color: ' . esc_attr( $colors['backgroundColor'] ) . ' !important';
}
if ( ! empty( $colors['textColor'] ) ) {
	$wrapper_styles[] = 'color: ' . esc_attr( $colors['textColor'] ) . ' !important';
	$wrapper_styles[] = '--text-color: ' . esc_attr( $colors['textColor'] );
}
$wrapper_style_string = ! empty( $wrapper_styles ) ? implode( '; ', $wrapper_styles ) : '';

// Note: $button_class is now passed from the render_block() method
// Button styles are handled via wp_add_inline_style() in class-shopify-block.php
?>

<section class="<?php echo esc_attr( $wrapper_class_string ); ?>" 
         id="<?php echo esc_attr( $block_id ); ?>"
         <?php if ( $wrapper_style_string ) : ?>style="<?php echo esc_attr( $wrapper_style_string ); ?>"<?php endif; ?>>
	
	<div class="prodshow-container">
		
		<?php if ( ! $has_single_product ) : ?>
			<!-- Multiple Products/Collection View -->
			<div class="prodshow-header">
				<div>
					<?php if ( $title ) : ?>
						<h2 class="prodshow-title">
							<?php echo esc_html( $title ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( $description ) : ?>
						<p class="prodshow-description">
							<?php echo esc_html( $description ); ?>
						</p>
					<?php endif; ?>
				</div>
				
				<?php if ( ! empty( $cta_button_text ) && ! empty( $cta_button_url ) ) : ?>
					<a href="<?php echo esc_url( $cta_button_url ); ?>" 
					   class="prodshow-cta-button <?php echo esc_attr( $button_class ); ?>" 
					   <?php if ( $cta_button_new_tab ) : ?>
					   target="_blank" 
					   rel="noopener noreferrer"
					   <?php endif; ?>>
						<?php echo esc_html( $cta_button_text ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="prodshow-carousel" data-carousel="<?php echo esc_attr( $block_id ); ?>">
				<div class="prodshow-carousel-viewport">
					<div class="prodshow-carousel-container">
						<?php 
						foreach ( $display_products as $product ) : 
							include PRODSHOW_PLUGIN_DIR . 'templates/product-card.php'; 
						endforeach; 
						?>
					</div>
				</div>

				<?php if ( count( $display_products ) > 1 ) : ?>
					<div class="prodshow-carousel-controls">
						<button class="prodshow-carousel-btn prodshow-carousel-prev" aria-label="<?php esc_attr_e( 'Previous', 'products-showcase' ); ?>">
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none">
								<rect width="40" height="40" rx="20" fill="#F5F5F5"/>
								<path d="M23 13L16 20L23 27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
						<button class="prodshow-carousel-btn prodshow-carousel-next" aria-label="<?php esc_attr_e( 'Next', 'products-showcase' ); ?>">
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none">
								<rect width="40" height="40" rx="20" fill="#F5F5F5"/>
								<path d="M17 13L24 20L17 27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
					</div>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<!-- Single Product View -->
			<div class="prodshow-single-product">
				<div class="prodshow-single-info">
					<div class="prodshow-single-info-content">
					<?php if ( $title ) : ?>
						<h2 class="prodshow-title"><?php echo esc_html( $title ); ?></h2>
					<?php endif; ?>
					
					<?php if ( $description ) : ?>
						<p class="prodshow-description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
					</div>
					
					<a href="<?php echo esc_url( $shop_base_url ); ?>" 
					   class="prodshow-cta-button" 
					   target="_blank" 
					   rel="noopener noreferrer">
						<?php esc_html_e( 'Visit Store', 'products-showcase' ); ?>
					</a>
				</div>

				<div class="prodshow-single-product-card">
					<?php
					$product = $display_products[0];
					include PRODSHOW_PLUGIN_DIR . 'templates/product-card.php';
					?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>

