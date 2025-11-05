<?php
/**
 * Admin Settings Page
 *
 * Handles the WordPress admin settings page for Shopify API configuration
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SPS_Admin_Settings class
 */
class SPS_Admin_Settings {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'admin_footer_text', array( $this, 'custom_admin_footer_text' ) );
	}

	/**
	 * Add settings page to WordPress admin
	 */
	public function add_settings_page() {
		// Custom Shopify icon as SVG data URI.
		$icon_svg = $this->get_menu_icon();

		// Add top-level menu item positioned below Settings (position 81).
		add_menu_page(
			__( 'Products Showcase', 'products-showcase' ), // Page title.
			__( 'Shopify Products', 'products-showcase' ), // Menu title.
			'manage_options', // Capability.
			'products-showcase', // Menu slug.
			array( $this, 'render_settings_page' ), // Callback function.
			$icon_svg, // Custom Shopify icon.
			81 // Position (right after Settings which is at 80).
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		// Main settings
		register_setting(
			'sps_settings',
			'sps_shopify_url',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			)
		);

		register_setting(
			'sps_settings',
			'sps_shopify_access_token',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			)
		);

		register_setting(
			'sps_settings',
			'sps_cache_duration',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => HOUR_IN_SECONDS,
				'show_in_rest'      => false,
			)
		);

		// UTM Parameters
		register_setting(
			'sps_settings',
			'sps_utm_source',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			)
		);

		register_setting(
			'sps_settings',
			'sps_utm_medium',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			)
		);

		register_setting(
			'sps_settings',
			'sps_utm_campaign',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Updated hook for top-level menu page.
		if ( 'toplevel_page_products-showcase' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'sps-admin',
			SPS_PLUGIN_URL . 'assets/admin/admin.css',
			array(),
			SPS_VERSION
		);

		wp_enqueue_script(
			'sps-admin',
			SPS_PLUGIN_URL . 'assets/admin/admin.js',
			array( 'jquery' ),
			SPS_VERSION,
			true
		);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Disable admin notices from other plugins on our settings page.
		$this->disable_admin_notices();

		// Handle cache clearing.
		if ( isset( $_POST['sps_clear_cache'] ) && check_admin_referer( 'sps_clear_cache' ) ) {
			$this->clear_cache();
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Cache cleared successfully!', 'products-showcase' ) . '</p></div>';
		}

		// Show custom settings saved notice (and suppress WordPress default)
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) {
			// Suppress WordPress core "Settings saved." notice
			global $wp_settings_errors;
			$wp_settings_errors = array();
			
			// Show our custom styled notice
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully!', 'products-showcase' ) . '</p></div>';
		}

		// Test connection.
		$connection_status = $this->test_shopify_connection();

		// Include the header.
		require_once SPS_PLUGIN_DIR . 'templates/admin/header.php';
		?>
		<div class="wrap sps-settings-wrap" id="sps-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php 
			// Only show settings_errors if NOT showing custom notice
			if ( ! isset( $_GET['settings-updated'] ) ) {
				settings_errors(); 
			}
			?>

			<!-- Getting Started Guide (show only if not connected) -->
			<?php if ( ! $connection_status['success'] && ( empty( get_option( 'sps_shopify_url' ) ) || empty( get_option( 'sps_shopify_access_token' ) ) ) ) : ?>
				<div class="sps-section">
					<div class="sps-section-header">
						<h2><?php esc_html_e( '🚀 Quick Start', 'products-showcase' ); ?></h2>
						<p><?php esc_html_e( 'Connect your Shopify store and start displaying products:', 'products-showcase' ); ?></p>
					</div>
					<div class="sps-banner-content">
						<ol class="sps-steps">
							<li>
								<strong><?php esc_html_e( 'Create Custom App', 'products-showcase' ); ?></strong>
								<p><?php esc_html_e( 'Shopify Admin → Settings → Apps → Develop apps → Create an app', 'products-showcase' ); ?></p>
							</li>
							<li>
								<strong><?php esc_html_e( 'Enable API Access', 'products-showcase' ); ?></strong>
								<p><?php esc_html_e( 'Configure Admin API → Enable "read_products" scope → Install app', 'products-showcase' ); ?></p>
							</li>
							<li>
								<strong><?php esc_html_e( 'Enter Credentials', 'products-showcase' ); ?></strong>
								<p><?php esc_html_e( 'Copy your store URL and access token, paste below, and save', 'products-showcase' ); ?></p>
							</li>
							<li>
								<strong><?php esc_html_e( 'Add Products to Your Site', 'products-showcase' ); ?></strong>
								<p><?php esc_html_e( 'Edit any page/post → Click + to add block → Search "Shopify Product Showcase" → Select product or collection', 'products-showcase' ); ?></p>
							</li>
						</ol>
						<div class="sps-banner-links">
							<a href="https://shopify.dev/docs/apps/auth/admin-app-access-tokens" target="_blank" rel="noopener noreferrer" class="sps-link-primary">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="12" r="10"></circle>
									<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
									<line x1="12" y1="17" x2="12.01" y2="17"></line>
								</svg>
								<?php esc_html_e( 'Shopify Documentation', 'products-showcase' ); ?>
							</a>
							<a href="https://github.com/HosseinKarami/products-showcase/wiki" target="_blank" rel="noopener noreferrer" class="sps-link-secondary">
								<?php esc_html_e( 'Plugin Wiki', 'products-showcase' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $connection_status['success'] ) : ?>
				<div class="sps-connection-status-card success">
					<div class="sps-status-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
							<polyline points="22 4 12 14.01 9 11.01"></polyline>
						</svg>
					</div>
					<div class="sps-status-content">
						<div class="sps-status-header">
							<h3><?php esc_html_e( 'Connected to Shopify', 'products-showcase' ); ?></h3>
							<span class="sps-status-badge"><?php esc_html_e( 'Active', 'products-showcase' ); ?></span>
						</div>
						<div class="sps-status-details">
							<?php if ( ! empty( $connection_status['shop_name'] ) ) : ?>
								<div class="sps-status-item">
									<span class="sps-status-label"><?php esc_html_e( 'Store Name:', 'products-showcase' ); ?></span>
									<span class="sps-status-value"><?php echo esc_html( $connection_status['shop_name'] ); ?></span>
								</div>
							<?php endif; ?>
							<div class="sps-status-item">
								<span class="sps-status-label"><?php esc_html_e( 'Store URL:', 'products-showcase' ); ?></span>
								<span class="sps-status-value"><?php echo esc_html( get_option( 'sps_shopify_url' ) ); ?></span>
							</div>
							<div class="sps-status-item">
								<span class="sps-status-label"><?php esc_html_e( 'API Version:', 'products-showcase' ); ?></span>
								<span class="sps-status-value"><?php echo esc_html( SPS_SHOPIFY_API_VERSION ); ?></span>
							</div>
						</div>
					</div>
				</div>
			<?php elseif ( ! empty( get_option( 'sps_shopify_url' ) ) && ! empty( get_option( 'sps_shopify_access_token' ) ) ) : ?>
				<div class="sps-connection-status-card error">
					<div class="sps-status-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="8" x2="12" y2="12"></line>
							<line x1="12" y1="16" x2="12.01" y2="16"></line>
						</svg>
					</div>
					<div class="sps-status-content">
						<div class="sps-status-header">
							<h3><?php esc_html_e( 'Connection Failed', 'products-showcase' ); ?></h3>
							<span class="sps-status-badge error"><?php esc_html_e( 'Error', 'products-showcase' ); ?></span>
						</div>
						<div class="sps-status-details">
							<div class="sps-status-item">
								<span class="sps-status-label"><?php esc_html_e( 'Error Message:', 'products-showcase' ); ?></span>
								<span class="sps-status-value"><?php echo esc_html( $connection_status['message'] ); ?></span>
							</div>
							<div class="sps-status-item">
								<span class="sps-status-label"><?php esc_html_e( 'Store URL:', 'products-showcase' ); ?></span>
								<span class="sps-status-value"><?php echo esc_html( get_option( 'sps_shopify_url' ) ); ?></span>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'sps_settings' ); ?>

				<!-- Shopify API Configuration Section -->
				<div class="sps-section">
					<div class="sps-section-header">
						<h2><?php esc_html_e( 'Shopify API Configuration', 'products-showcase' ); ?></h2>
						<p><?php esc_html_e( 'Connect your WordPress site to your Shopify store by providing the necessary API credentials.', 'products-showcase' ); ?></p>
					</div>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									<label for="sps_shopify_url"><?php esc_html_e( 'Shopify Store URL', 'products-showcase' ); ?></label>
								</th>
								<td>
									<input type="text"
										   id="sps_shopify_url"
										   name="sps_shopify_url"
										   value="<?php echo esc_attr( get_option( 'sps_shopify_url' ) ); ?>"
										   class="regular-text"
										   placeholder="your-store.myshopify.com">
									<p class="description">
										<?php esc_html_e( 'Your Shopify store URL (without https://). Example: my-store.myshopify.com', 'products-showcase' ); ?>
									</p>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="sps_shopify_access_token"><?php esc_html_e( 'Admin API Access Token', 'products-showcase' ); ?></label>
								</th>
								<td>
									<input type="password"
										   id="sps_shopify_access_token"
										   name="sps_shopify_access_token"
										   value="<?php echo esc_attr( get_option( 'sps_shopify_access_token' ) ); ?>"
										   class="regular-text"
										   autocomplete="off">
									<p class="description">
										<?php
										echo wp_kses(
											__( 'Generate from <strong>Shopify Admin → Settings → Apps and sales channels → Develop apps</strong>. Required scopes: <code>read_products</code>', 'products-showcase' ),
											array(
												'strong' => array(),
												'code'   => array(),
											)
										);
										?>
										<br>
										<a href="https://shopify.dev/docs/apps/auth/admin-app-access-tokens" target="_blank" rel="noopener noreferrer">
											<?php esc_html_e( 'Learn how to create an access token →', 'products-showcase' ); ?>
										</a>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- General Settings Section -->
				<div class="sps-section">
					<div class="sps-section-header">
						<h2><?php esc_html_e( 'General Settings', 'products-showcase' ); ?></h2>
						<p><?php esc_html_e( 'Configure caching and performance options for your Shopify product data.', 'products-showcase' ); ?></p>
					</div>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									<label for="sps_cache_duration"><?php esc_html_e( 'Cache Duration', 'products-showcase' ); ?></label>
								</th>
								<td>
									<select id="sps_cache_duration" name="sps_cache_duration">
										<?php
										$current_duration = get_option( 'sps_cache_duration', HOUR_IN_SECONDS );
										$durations        = array(
											15 * MINUTE_IN_SECONDS => __( '15 Minutes', 'products-showcase' ),
											30 * MINUTE_IN_SECONDS => __( '30 Minutes', 'products-showcase' ),
											HOUR_IN_SECONDS        => __( '1 Hour', 'products-showcase' ),
											2 * HOUR_IN_SECONDS    => __( '2 Hours', 'products-showcase' ),
											6 * HOUR_IN_SECONDS    => __( '6 Hours', 'products-showcase' ),
											12 * HOUR_IN_SECONDS   => __( '12 Hours', 'products-showcase' ),
											DAY_IN_SECONDS         => __( '24 Hours', 'products-showcase' ),
										);

										foreach ( $durations as $seconds => $label ) {
											printf(
												'<option value="%d" %s>%s</option>',
												esc_attr( $seconds ),
												selected( $current_duration, $seconds, false ),
												esc_html( $label )
											);
										}
										?>
									</select>
									<p class="description">
										<?php esc_html_e( 'How long to cache product data from Shopify. Lower values = more API calls but fresher data.', 'products-showcase' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label><?php esc_html_e( 'Clear Cache', 'products-showcase' ); ?></label>
								</th>
								<td>
									<button type="button" id="sps-clear-cache-btn" class="button">
										<?php esc_html_e( 'Clear Cache Now', 'products-showcase' ); ?>
									</button>
									<input type="hidden" id="sps-clear-cache-nonce" value="<?php echo esc_attr( wp_create_nonce( 'sps_clear_cache' ) ); ?>">
									<p class="description">
										<?php esc_html_e( 'Clear cached product data to fetch fresh data from your Shopify store immediately.', 'products-showcase' ); ?>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- UTM Parameters Section -->
				<div class="sps-section">
					<div class="sps-section-header">
						<h2><?php esc_html_e( 'UTM Parameters', 'products-showcase' ); ?></h2>
						<p><?php esc_html_e( 'Add UTM parameters to track product link clicks in your analytics. These parameters will be automatically appended to all product URLs.', 'products-showcase' ); ?></p>
					</div>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									<label for="sps_utm_source"><?php esc_html_e( 'UTM Source', 'products-showcase' ); ?></label>
								</th>
								<td>
									<input type="text"
										   id="sps_utm_source"
										   name="sps_utm_source"
										   value="<?php echo esc_attr( get_option( 'sps_utm_source' ) ); ?>"
										   class="regular-text"
										   placeholder="wordpress">
									<p class="description">
										<?php esc_html_e( 'Identifies which site sent the traffic (e.g., wordpress, blog, website)', 'products-showcase' ); ?>
									</p>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="sps_utm_medium"><?php esc_html_e( 'UTM Medium', 'products-showcase' ); ?></label>
								</th>
								<td>
									<input type="text"
										   id="sps_utm_medium"
										   name="sps_utm_medium"
										   value="<?php echo esc_attr( get_option( 'sps_utm_medium' ) ); ?>"
										   class="regular-text"
										   placeholder="referral">
									<p class="description">
										<?php esc_html_e( 'Identifies the medium (e.g., referral, cpc, email, social)', 'products-showcase' ); ?>
									</p>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="sps_utm_campaign"><?php esc_html_e( 'UTM Campaign', 'products-showcase' ); ?></label>
								</th>
								<td>
									<input type="text"
										   id="sps_utm_campaign"
										   name="sps_utm_campaign"
										   value="<?php echo esc_attr( get_option( 'sps_utm_campaign' ) ); ?>"
										   class="regular-text"
										   placeholder="product-showcase">
									<p class="description">
										<?php esc_html_e( 'Identifies a specific campaign (e.g., product-showcase, summer-sale)', 'products-showcase' ); ?>
									</p>
								</td>
							</tr>
						</tbody>
					</table>

					<div class="sps-info-box" style="margin: 0 20px 20px 20px;">
						<p style="margin: 0 0 8px 0;"><strong><?php esc_html_e( 'Example URL with UTM parameters:', 'products-showcase' ); ?></strong></p>
						<code style="display: block; padding: 8px; background: #fff; border-radius: 4px; font-size: 12px; word-break: break-all;">
							<?php
							$shop_url = get_option( 'sps_shopify_url', 'your-store.myshopify.com' );
							if ( empty( $shop_url ) ) {
								$shop_url = 'your-store.myshopify.com';
							}
							echo esc_html( "https://{$shop_url}/products/example-product?utm_source=wordpress&utm_medium=referral&utm_campaign=product-showcase" );
							?>
						</code>
						
						<?php
						// Debug: Show current UTM parameters
						$current_utm = self::get_utm_parameters();
						if ( ! empty( $current_utm ) ) :
							?>
							<p style="margin: 12px 0 4px 0;"><strong><?php esc_html_e( 'Current UTM Parameters:', 'products-showcase' ); ?></strong></p>
							<code style="display: block; padding: 8px; background: #fff; border-radius: 4px; font-size: 12px; word-break: break-all; color: #46b450;">
								<?php echo esc_html( $current_utm ); ?>
							</code>
						<?php else : ?>
							<p style="margin: 12px 0 0 0; color: #dc3232;">
								<strong>⚠️ <?php esc_html_e( 'No UTM parameters are currently set. Fill in the fields above and save to activate tracking.', 'products-showcase' ); ?></strong>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Save Button -->
				<div class="sps-save-button-container">
					<?php submit_button( __( 'Save All Settings', 'products-showcase' ), 'primary large', 'submit', false ); ?>
				</div>
			</form>

			<!-- Help & Support Section -->
			<div class="sps-help-section">
				<h2><?php esc_html_e( '🆘 Help & Support', 'products-showcase' ); ?></h2>
				<div class="sps-help-grid">
					<div class="sps-help-card">
						<div class="sps-help-icon">🐛</div>
						<h3><?php esc_html_e( 'Report Issues', 'products-showcase' ); ?></h3>
						<p><?php esc_html_e( 'Found a bug? Let us know!', 'products-showcase' ); ?></p>
						<a href="https://github.com/HosseinKarami/products-showcase/issues" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Report Bug →', 'products-showcase' ); ?></a>
					</div>
					<div class="sps-help-card">
						<div class="sps-help-icon">💬</div>
						<h3><?php esc_html_e( 'Get Support', 'products-showcase' ); ?></h3>
						<p><?php esc_html_e( 'Need help? Contact the author', 'products-showcase' ); ?></p>
						<a href="mailto:hi@hosseinkarami.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contact →', 'products-showcase' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Disable admin notices from other plugins on our settings pages
	 */
	private function disable_admin_notices() {
		$screen = get_current_screen();
		
		if ( ! $screen ) {
			return;
		}

		// Check if we're on any of our settings pages.
		// NOTE: We don't remove admin_notices completely because we need to show
		// the settings-updated notice from WordPress when settings are saved.
		if ( strpos( $screen->id, 'products-showcase' ) !== false ) {
			remove_all_actions( 'user_admin_notices' );
			// Don't remove admin_notices - we need it for settings saved confirmation
		}
	}

	/**
	 * Test Shopify connection
	 *
	 * @return array Connection status.
	 */
	private function test_shopify_connection() {
		$shop_url      = get_option( 'sps_shopify_url' );
		$access_token  = get_option( 'sps_shopify_access_token' );

		if ( empty( $shop_url ) || empty( $access_token ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please configure your Shopify credentials.', 'products-showcase' ),
			);
		}

		$url = "https://{$shop_url}/admin/api/" . SPS_SHOPIFY_API_VERSION . "/graphql.json";

		$query = '{
			shop {
				name
			}
		}';

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'X-Shopify-Access-Token' => $access_token,
					'Content-Type'           => 'application/json',
				),
				'body'    => wp_json_encode( array( 'query' => $query ) ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['errors'] ) ) {
			return array(
				'success' => false,
				'message' => isset( $body['errors'][0]['message'] ) ? $body['errors'][0]['message'] : __( 'Unknown error', 'products-showcase' ),
			);
		}

		if ( isset( $body['data']['shop']['name'] ) ) {
			return array(
				'success'   => true,
				'shop_name' => $body['data']['shop']['name'],
			);
		}

		return array(
			'success' => false,
			'message' => __( 'Unable to verify connection.', 'products-showcase' ),
		);
	}

	/**
	 * Clear all Shopify cache
	 */
	private function clear_cache() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache, no caching needed.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sps_shopify_%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally clearing cache, no caching needed.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_sps_shopify_%'" );
	}

	/**
	 * Get menu icon as SVG data URI
	 *
	 * @return string SVG data URI for the Shopify icon.
	 */
	private function get_menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" fill="none" viewBox="0 0 512 512"><path fill="#fff" d="M331.838 73.704s-5.357 1.532-14.158 4.214c-1.53-4.98-3.826-10.728-6.887-16.858-9.949-19.156-24.872-29.501-42.474-29.501-1.148 0-2.296 0-3.827.383-.382-.766-1.148-1.15-1.53-1.916-7.653-8.428-17.602-12.26-29.464-11.877-22.959.767-45.918 17.241-64.285 46.743-13.01 20.689-22.959 46.742-25.638 67.048-26.402 8.046-44.77 13.793-45.152 14.176-13.393 4.214-13.776 4.597-15.306 17.241C80.82 172.935 46 443.043 46 443.043l289.282 50.191V72.937c-1.53.383-2.678.383-3.444.767m-66.963 20.689c-15.306 4.597-32.143 9.961-48.596 14.942 4.591-18.007 13.775-36.014 24.489-47.892 4.209-4.214 9.949-9.195 16.454-12.26 6.505 13.793 8.036 32.566 7.653 45.21M233.88 33.475q8.036 0 13.776 3.448c-6.123 3.065-12.245 8.046-17.985 13.793-14.54 15.708-25.637 40.229-30.229 63.6-13.775 4.214-27.551 8.429-40.178 12.26 8.036-36.398 39.03-91.952 74.616-93.101m-44.769 210.723c1.53 24.52 66.198 29.884 70.024 87.737 2.679 45.593-24.107 76.626-62.754 78.925-46.683 3.065-72.321-24.52-72.321-24.52l9.949-42.145s25.638 19.54 46.301 18.007c13.392-.766 18.367-11.877 17.984-19.539-1.913-32.183-54.719-30.268-58.162-83.14-3.062-44.06 26.02-88.887 90.305-93.101 24.872-1.533 37.499 4.597 37.499 4.597l-14.541 55.171s-16.453-7.662-35.969-6.13c-28.316 1.916-28.698 19.923-28.315 24.138m91.07-154.403c0-11.494-1.531-27.968-6.888-41.761 17.602 3.448 26.02 22.988 29.847 34.865q-10.332 2.873-22.959 6.896m64.667 401.906L465 461.817s-51.658-349.8-52.04-352.099c-.383-2.299-2.296-3.831-4.209-3.831-1.914 0-35.587-.767-35.587-.767s-20.663-19.922-28.316-27.585z"/></svg>';

		// Encode as base64 data URI.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Build UTM parameters query string
	 *
	 * @return string UTM parameters query string (empty if no parameters set).
	 */
	public static function get_utm_parameters() {
		$utm_params = array();

		$utm_source   = get_option( 'sps_utm_source', '' );
		$utm_medium   = get_option( 'sps_utm_medium', '' );
		$utm_campaign = get_option( 'sps_utm_campaign', '' );

		if ( ! empty( $utm_source ) ) {
			$utm_params['utm_source'] = $utm_source;
		}
		if ( ! empty( $utm_medium ) ) {
			$utm_params['utm_medium'] = $utm_medium;
		}
		if ( ! empty( $utm_campaign ) ) {
			$utm_params['utm_campaign'] = $utm_campaign;
		}

		if ( empty( $utm_params ) ) {
			return '';
		}

		return '?' . http_build_query( $utm_params );
	}

	/**
	 * Custom admin footer text for plugin pages
	 *
	 * @param string $footer_text The existing footer text.
	 * @return string Modified footer text.
	 */
	public function custom_admin_footer_text( $footer_text ) {
		$screen = get_current_screen();
		
		// Only change footer text on our plugin pages.
		if ( $screen && strpos( $screen->id, 'products-showcase' ) !== false ) {
			$footer_text = sprintf(
				/* translators: 1: Plugin name, 2: GitHub link */
				__( 'Shopify Product Showcase by %2$s.', 'products-showcase' ),
				'<strong>' . esc_html__( 'Shopify Product Showcase', 'products-showcase' ) . '</strong>',
				'<a href="https://hosseinkarami.com?utm_source=wp-plugin&utm_medium=plugin&utm_campaign=products-showcase" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Hossein Karami', 'products-showcase' ) . '</a>'
			);
		}
		
		return $footer_text;
	}
}



