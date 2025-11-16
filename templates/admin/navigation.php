<?php
/**
 * Admin Navigation Template
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Template variables are local scope, not global variables.

/**
 * Generate admin page URL for Shopify Product Showcase.
 *
 * @param string $page The page slug.
 * @return string The full admin URL.
 */
function prodshow_get_admin_page_url( string $page ): string {
	return admin_url( "admin.php?page={$page}" );
}

/**
 * Check if we're on a specific Products Showcase settings page.
 *
 * @param string $page The page slug to check (without prefix).
 * @return bool True if on the specified page.
 */
function prodshow_is_settings_page( string $page = '' ): bool {
	$screen = get_current_screen();
	
	if ( ! $screen ) {
		return false;
	}

	// Check if we're on any Products Showcase settings page.
	if ( empty( $page ) ) {
		return strpos( $screen->id, 'products-showcase' ) !== false;
	}

	// Check for specific page.
	$page_map = array(
		'settings' => 'toplevel_page_products-showcase',
		'products' => 'shopify-products_page_prodshow-products',
	);

	return isset( $page_map[ $page ] ) && $screen->id === $page_map[ $page ];
}

/**
 * Menu items configuration.
 */
$menu_items = array(
	'settings' => array(
		'title' => __( 'Settings', 'products-showcase' ),
		'url'   => prodshow_get_admin_page_url( 'products-showcase' ),
	),
	// Add more menu items here in the future.
	// 'products' => array(
	// 	'title' => __( 'Products', 'products-showcase' ),
	// 	'url'   => prodshow_get_admin_page_url( 'prodshow-products' ),
	// ),
);
?>

<div class="prodshow-menu">
	<nav>
		<?php foreach ( $menu_items as $slug => $item ) : ?>
			<?php
			// Generate menu item with proper escaping
			$class = prodshow_is_settings_page( $slug ) ? 'active' : '';
			?>
			<a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo esc_attr( $class ); ?>">
				<?php echo esc_html( $item['title'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</div>

