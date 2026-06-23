<?php
/**
 * Admin Header Template — branded hero.
 *
 * Full-bleed dark hero band shown at the top of the plugin's settings screen.
 * Replaces the previous light topbar + tab navigation. The WP Help / Screen
 * Options bar is relocated to sit directly beneath this hero by admin.js.
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="prodshow-hero">
	<div class="prodshow-hero__logo" aria-hidden="true">
		<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" fill="none" viewBox="0 0 512 512"><rect width="512" height="512" fill="#384246" rx="64"/><g><path fill="#fff" d="M314.348 115.746s-4.13 1.183-10.914 3.252c-1.18-3.843-2.95-8.277-5.31-13.006-7.669-14.78-19.173-22.76-32.742-22.76-.885 0-1.77 0-2.95.296-.295-.591-.885-.887-1.18-1.478-5.899-6.503-13.569-9.459-22.713-9.163-17.699.59-35.397 13.301-49.556 36.061-10.03 15.961-17.699 36.061-19.764 51.727-20.353 6.207-34.512 10.641-34.807 10.936-10.324 3.252-10.619 3.547-11.799 13.301C120.843 192.302 94 400.688 94 400.688l223.003 38.721V115.155c-1.18.296-2.065.296-2.655.591m-51.621 15.962c-11.799 3.547-24.778 7.685-37.462 11.527 3.54-13.892 10.619-27.784 18.878-36.947 3.245-3.252 7.67-7.094 12.684-9.46 5.015 10.642 6.195 25.125 5.9 34.88M238.834 84.71q6.194 0 10.619 2.66c-4.72 2.365-9.439 6.207-13.864 10.641-11.209 12.119-19.763 31.036-23.303 49.067-10.619 3.251-21.239 6.503-30.973 9.459 6.195-28.081 30.088-70.94 57.521-71.827m-34.513 162.57c1.18 18.918 51.032 23.056 53.981 67.689 2.065 35.174-18.583 59.116-48.376 60.89-35.987 2.364-55.751-18.918-55.751-18.918l7.67-32.514s19.763 15.075 35.692 13.893c10.324-.591 14.159-9.163 13.864-15.075-1.475-24.829-42.182-23.351-44.837-64.141-2.359-33.992 20.059-68.575 69.615-71.827 19.174-1.182 28.908 3.547 28.908 3.547l-11.209 42.564s-12.684-5.912-27.728-4.729c-21.829 1.478-22.124 15.37-21.829 18.621m70.205-119.119c0-8.868-1.18-21.578-5.31-32.219 13.569 2.66 20.059 17.735 23.009 26.898q-7.965 2.217-17.699 5.321m49.851 310.066L417 415.171s-39.822-269.867-40.117-271.64c-.295-1.774-1.77-2.956-3.245-2.956s-27.433-.591-27.433-.591-15.928-15.37-21.828-21.282z"/></g></svg>
	</div>
	<div class="prodshow-hero__body">
		<h1 class="prodshow-hero__title">
			<?php esc_html_e( 'Products Showcase', 'products-showcase' ); ?>
		</h1>
		<p class="prodshow-hero__subtitle">
			<?php esc_html_e( 'Shopify Integration for WordPress', 'products-showcase' ); ?>
		</p>
	</div>
	<div class="prodshow-hero__actions">
		<a href="<?php echo esc_url( PRODSHOW_Admin_Settings::support_url( 'admin-header-support' ) ); ?>" class="button prodshow-hero__btn" target="_blank" rel="noopener noreferrer">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="10"/>
				<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
				<line x1="12" y1="17" x2="12.01" y2="17"/>
			</svg>
			<?php esc_html_e( 'Support', 'products-showcase' ); ?>
		</a>
		<a href="<?php echo esc_url( PRODSHOW_Admin_Settings::donate_url( 'admin-header-donate' ) ); ?>" class="button prodshow-hero__btn" target="_blank" rel="noopener noreferrer">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M17 8h1a4 4 0 0 1 0 8h-1"/>
				<path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
				<line x1="6" y1="2" x2="6" y2="4"/>
				<line x1="10" y1="2" x2="10" y2="4"/>
				<line x1="14" y1="2" x2="14" y2="4"/>
			</svg>
			<?php esc_html_e( 'Donate', 'products-showcase' ); ?>
		</a>
	</div>
</div>
