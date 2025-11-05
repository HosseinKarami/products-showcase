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

$has_single_product = count( $display_products ) === 1;
$shop_url           = get_option( 'sps_shopify_url', '' );
$shop_base_url      = $shop_url ? 'https://' . $shop_url : 'https://shop.example.com';

// Build wrapper classes
$wrapper_classes = array( 'sps-shopify-block' );
if ( ! empty( $disable_global_padding ) ) {
	$wrapper_classes[] = 'sps-no-global-padding';
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

// Generate unique class for button styles
$button_class = 'sps-cta-button-' . substr( md5( $block_id ), 0, 8 );

// Check if we have any button colors
$has_button_colors = ! empty( $colors['buttonBackground'] ) || ! empty( $colors['buttonText'] ) || ! empty( $colors['buttonBackgroundHover'] ) || ! empty( $colors['buttonTextHover'] );
?>

<section class="<?php echo esc_attr( $wrapper_class_string ); ?>" 
         id="<?php echo esc_attr( $block_id ); ?>"
         <?php if ( $wrapper_style_string ) : ?>style="<?php echo esc_attr( $wrapper_style_string ); ?>"<?php endif; ?>>
	
	<?php if ( $has_button_colors ) : ?>
	<style>
		#<?php echo esc_attr( $block_id ); ?> .<?php echo esc_attr( $button_class ); ?> {
			<?php if ( ! empty( $colors['buttonBackground'] ) ) : ?>
			background-color: <?php echo esc_attr( $colors['buttonBackground'] ); ?> !important;
			<?php endif; ?>
			<?php if ( ! empty( $colors['buttonText'] ) ) : ?>
			color: <?php echo esc_attr( $colors['buttonText'] ); ?> !important;
			<?php endif; ?>
		}
		#<?php echo esc_attr( $block_id ); ?> .<?php echo esc_attr( $button_class ); ?>:hover {
			<?php if ( ! empty( $colors['buttonBackgroundHover'] ) ) : ?>
			background-color: <?php echo esc_attr( $colors['buttonBackgroundHover'] ); ?> !important;
			<?php endif; ?>
			<?php if ( ! empty( $colors['buttonTextHover'] ) ) : ?>
			color: <?php echo esc_attr( $colors['buttonTextHover'] ); ?> !important;
			<?php endif; ?>
		}
	</style>
	<?php endif; ?>
	
	<div class="sps-container">
		
		<?php if ( ! $has_single_product ) : ?>
			<!-- Multiple Products/Collection View -->
			<div class="sps-header">
				<div>
					<?php if ( $title ) : ?>
						<h2 class="sps-title">
							<?php echo esc_html( $title ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( $description ) : ?>
						<p class="sps-description">
							<?php echo esc_html( $description ); ?>
						</p>
					<?php endif; ?>
				</div>
				
				<?php if ( ! empty( $cta_button_text ) && ! empty( $cta_button_url ) ) : ?>
					<a href="<?php echo esc_url( $cta_button_url ); ?>" 
					   class="sps-cta-button <?php echo esc_attr( $button_class ); ?>" 
					   <?php if ( $cta_button_new_tab ) : ?>
					   target="_blank" 
					   rel="noopener noreferrer"
					   <?php endif; ?>>
						<?php echo esc_html( $cta_button_text ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="sps-carousel" data-carousel="<?php echo esc_attr( $block_id ); ?>">
				<div class="sps-carousel-viewport">
					<div class="sps-carousel-container">
						<?php 
						foreach ( $display_products as $product ) : 
							include SPS_PLUGIN_DIR . 'templates/product-card.php'; 
						endforeach; 
						?>
					</div>
				</div>

				<?php if ( count( $display_products ) > 1 ) : ?>
					<div class="sps-carousel-controls">
						<button class="sps-carousel-btn sps-carousel-prev" aria-label="<?php esc_attr_e( 'Previous', 'products-showcase' ); ?>">
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none">
								<rect width="40" height="40" rx="20" fill="#F5F5F5"/>
								<path d="M23 13L16 20L23 27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
						<button class="sps-carousel-btn sps-carousel-next" aria-label="<?php esc_attr_e( 'Next', 'products-showcase' ); ?>">
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
			<div class="sps-single-product">
				<div class="sps-single-info">
					<div class="sps-single-info-content">
					<?php if ( $title ) : ?>
						<h2 class="sps-title"><?php echo esc_html( $title ); ?></h2>
					<?php endif; ?>
					
					<?php if ( $description ) : ?>
						<p class="sps-description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
					</div>
					
					<a href="<?php echo esc_url( $shop_base_url ); ?>" 
					   class="sps-cta-button" 
					   target="_blank" 
					   rel="noopener noreferrer">
						<?php esc_html_e( 'Visit Store', 'products-showcase' ); ?>
					</a>
				</div>

				<div class="sps-single-product-card">
					<?php
					$product = $display_products[0];
					include SPS_PLUGIN_DIR . 'templates/product-card.php';
					?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>

