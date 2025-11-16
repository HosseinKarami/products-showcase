<?php
/**
 * Product Card Template
 *
 * Template for rendering a single product card
 *
 * @package ProductsShowcase
 * @var array $product Product data
 * @var string $shop_base_url Shopify store base URL
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Template variables are local scope, not global variables.

// Build product URL with UTM parameters.
$product_url = $shop_base_url . '/products/' . ( $product['handle'] ?? '' );
$utm_params  = '';
if ( class_exists( 'PRODSHOW_Admin_Settings' ) ) {
	$utm_params = PRODSHOW_Admin_Settings::get_utm_parameters();
	$product_url .= $utm_params;
}

$image_url   = '';
$image_alt   = $product['title'] ?? '';
$image_url_2 = ''; // Second image for hover effect
$image_alt_2 = $product['title'] ?? '';

// Get primary image.
if ( ! empty( $product['images']['edges'][0]['node']['originalSrc'] ) ) {
	$image_url = $product['images']['edges'][0]['node']['originalSrc'];
	$image_alt = $product['images']['edges'][0]['node']['altText'] ?? $image_alt;
}

// Get second image for hover effect.
if ( ! empty( $product['images']['edges'][1]['node']['originalSrc'] ) ) {
	$image_url_2 = $product['images']['edges'][1]['node']['originalSrc'];
	$image_alt_2 = $product['images']['edges'][1]['node']['altText'] ?? $image_alt;
}

// Get price.
$price     = '0.00';
$currency  = 'USD';
if ( ! empty( $product['priceRange']['minVariantPrice']['amount'] ) ) {
	$price    = $product['priceRange']['minVariantPrice']['amount'];
	$currency = $product['priceRange']['minVariantPrice']['currencyCode'] ?? 'USD';
}

$formatted_price = number_format( floatval( $price ), 2 );

// Get currency symbol
$currency_symbols = array(
	'USD' => '$',
	'EUR' => '€',
	'GBP' => '£',
	'CAD' => 'CA$',
	'AUD' => 'A$',
	'JPY' => '¥',
	'CNY' => '¥',
	'INR' => '₹',
	'BRL' => 'R$',
	'MXN' => 'MX$',
	'CHF' => 'CHF',
	'SEK' => 'kr',
	'NOK' => 'kr',
	'DKK' => 'kr',
	'NZD' => 'NZ$',
	'SGD' => 'S$',
	'HKD' => 'HK$',
);
$currency_symbol = isset( $currency_symbols[ $currency ] ) ? $currency_symbols[ $currency ] : $currency;

// Get color options if they exist and build variant image map.
$color_option = null;
$variant_images = array(); // Map color values to their variant images

if ( ! empty( $product['options'] ) ) {
	foreach ( $product['options'] as $option ) {
		if ( strtolower( $option['name'] ?? '' ) === 'color' ) {
			$color_option = $option;
			break;
		}
	}
}

// Build variant image map for color swatches
if ( $color_option && ! empty( $product['variants']['edges'] ) ) {
	foreach ( $product['variants']['edges'] as $variant_edge ) {
		$variant = $variant_edge['node'];
		if ( ! empty( $variant['selectedOptions'] ) ) {
			foreach ( $variant['selectedOptions'] as $selected_option ) {
				if ( strtolower( $selected_option['name'] ?? '' ) === 'color' ) {
					$color_value = $selected_option['value'];
					if ( ! empty( $variant['image']['url'] ) && ! isset( $variant_images[ $color_value ] ) ) {
						$variant_images[ $color_value ] = array(
							'url' => $variant['image']['url'],
							'alt' => $variant['image']['altText'] ?? $product['title'],
						);
					}
					break;
				}
			}
		}
	}
}
?>
<div class="prodshow-product-card">
	<a href="<?php echo esc_url( $product_url ); ?>" 
	   class="prodshow-product-link" 
	   target="_blank" 
	   rel="noopener noreferrer"
	   aria-label="<?php echo esc_attr( $product['title'] ); ?>">
		
		<div class="prodshow-product-image-wrapper">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" 
					 alt="<?php echo esc_attr( $image_alt ); ?>" 
					 class="prodshow-product-image prodshow-product-image-primary"
					 loading="lazy">
				<?php if ( $image_url_2 ) : ?>
					<img src="<?php echo esc_url( $image_url_2 ); ?>" 
						 alt="<?php echo esc_attr( $image_alt_2 ); ?>" 
						 class="prodshow-product-image prodshow-product-image-secondary"
						 loading="lazy">
				<?php endif; ?>
			<?php else : ?>
				<div class="prodshow-product-image-placeholder"></div>
			<?php endif; ?>
		</div>
	</a>

	<div class="prodshow-product-content">
		<h3 class="prodshow-product-title">
			<a href="<?php echo esc_url( $product_url ); ?>" 
			   target="_blank" 
			   rel="noopener noreferrer">
				<?php echo esc_html( $product['title'] ); ?>
			</a>
		</h3>

		<div class="prodshow-product-meta">
			<?php if ( $color_option && ! empty( $color_option['values'] ) ) : ?>
				<div class="prodshow-product-swatches">
					<?php
					$max_swatches = 5;
					$colors       = array_slice( $color_option['values'], 0, $max_swatches );
					foreach ( $colors as $index => $color_value ) :
						$swatch_color = null;
						$swatch_image = null;

						// Get swatch data if available.
						if ( ! empty( $color_option['optionValues'][ $index ]['swatch'] ) ) {
							$swatch       = $color_option['optionValues'][ $index ]['swatch'];
							$swatch_color = $swatch['color'] ?? null;
							$swatch_image = $swatch['image']['image']['url'] ?? null;
						}

						if ( $swatch_color || $swatch_image ) :
							$style = '';
							if ( $swatch_image ) {
								$style = 'background-image: url(' . esc_url( $swatch_image ) . ');';
							} elseif ( $swatch_color ) {
								$style = 'background-color: ' . esc_attr( $swatch_color ) . ';';
							}
							
							// Get variant image for this color
							$variant_image_url = isset( $variant_images[ $color_value ] ) ? $variant_images[ $color_value ]['url'] : '';
							$variant_image_alt = isset( $variant_images[ $color_value ] ) ? $variant_images[ $color_value ]['alt'] : '';
							?>
							<span class="prodshow-swatch" 
								  style="<?php echo esc_attr( $style ); ?>" 
								  title="<?php echo esc_attr( $color_value ); ?>"
								  aria-label="<?php echo esc_attr( $color_value ); ?>"
								  <?php if ( $variant_image_url ) : ?>
								  data-variant-image="<?php echo esc_attr( $variant_image_url ); ?>"
								  data-variant-alt="<?php echo esc_attr( $variant_image_alt ); ?>"
								  <?php endif; ?>></span>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php if ( count( $color_option['values'] ) > $max_swatches ) : ?>
						<span class="prodshow-swatch-more">
							+<?php echo count( $color_option['values'] ) - $max_swatches; ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<p class="prodshow-product-price">
				<?php echo esc_html( $currency_symbol . $formatted_price ); ?>
			</p>
		</div>
	</div>
</div>

