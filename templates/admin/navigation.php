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

/**
 * Generate admin page URL for Shopify Product Showcase.
 *
 * @param string $page The page slug.
 * @return string The full admin URL.
 */
function sps_get_admin_page_url( string $page ): string {
	return admin_url( "admin.php?page={$page}" );
}

/**
 * Check if we're on a specific SPS settings page.
 *
 * @param string $page The page slug to check (without prefix).
 * @return bool True if on the specified page.
 */
function sps_is_settings_page( string $page = '' ): bool {
	$screen = get_current_screen();
	
	if ( ! $screen ) {
		return false;
	}

	// Check if we're on any SPS settings page.
	if ( empty( $page ) ) {
		return strpos( $screen->id, 'products-showcase' ) !== false;
	}

	// Check for specific page.
	$page_map = array(
		'settings' => 'toplevel_page_products-showcase',
		'products' => 'shopify-products_page_sps-products',
	);

	return isset( $page_map[ $page ] ) && $screen->id === $page_map[ $page ];
}

/**
 * Menu items configuration.
 */
$menu_items = array(
	'settings' => array(
		'title' => __( 'Settings', 'products-showcase' ),
		'url'   => sps_get_admin_page_url( 'products-showcase' ),
	),
	// Add more menu items here in the future.
	// 'products' => array(
	// 	'title' => __( 'Products', 'products-showcase' ),
	// 	'url'   => sps_get_admin_page_url( 'sps-products' ),
	// ),
);

/**
 * Generate a menu item HTML.
 *
 * @param array  $item Menu item configuration.
 * @param string $page Page slug.
 * @return string HTML for the menu item.
 */
function sps_generate_menu_item( array $item, string $page ): string {
	$class = sps_is_settings_page( $page ) ? 'active' : '';
	return sprintf(
		'<a href="%s" class="%s">%s</a>',
		esc_url( $item['url'] ),
		esc_attr( $class ),
		esc_html( $item['title'] )
	);
}
?>

<div class="sps-menu">
	<nav>
		<?php foreach ( $menu_items as $slug => $item ) : ?>
			<?php echo sps_generate_menu_item( $item, $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endforeach; ?>
	</nav>
</div>

