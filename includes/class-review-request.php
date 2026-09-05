<?php
/**
 * Review Request
 *
 * Asks for a wordpress.org review once the plugin has demonstrably worked
 * for a while: 14+ days after both the first successful store connection
 * and the first frontend block render.
 *
 * @package ProductsShowcase
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PRODSHOW_Review_Request class
 */
class PRODSHOW_Review_Request {

	/**
	 * Option storing the timestamp of the first successful store connection.
	 */
	const OPTION_FIRST_CONNECTED = 'prodshow_first_connected_at';

	/**
	 * Option storing the timestamp of the first successful frontend render.
	 */
	const OPTION_FIRST_RENDER = 'prodshow_first_render_at';

	/**
	 * Option storing the review status ('done' once rated, dismissed with the X, or "I already did").
	 */
	const OPTION_STATUS = 'prodshow_review_status';

	/**
	 * Option storing the timestamp until which the notice is snoozed.
	 */
	const OPTION_SNOOZE_UNTIL = 'prodshow_review_snooze_until';

	/**
	 * Nonce action for the notice's links.
	 */
	const NONCE_ACTION = 'prodshow_review_action';

	/**
	 * Minimum age (seconds) of both milestones before the notice may appear.
	 */
	const WAIT_PERIOD = 14 * DAY_IN_SECONDS;

	/**
	 * How long (seconds) "Maybe later" hides the notice.
	 */
	const SNOOZE_PERIOD = 30 * DAY_IN_SECONDS;

	/**
	 * The wordpress.org review form URL.
	 */
	const REVIEW_URL = 'https://wordpress.org/support/plugin/products-showcase/reviews/#new-post';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_action' ) );
		add_action( 'admin_init', array( $this, 'maybe_backfill_first_connection' ) );
		add_action( 'admin_notices', array( $this, 'maybe_render_notice' ) );
	}

	/**
	 * Record the first successful store connection (no-op when already recorded)
	 */
	public static function record_first_connection() {
		if ( ! get_option( self::OPTION_FIRST_CONNECTED ) ) {
			add_option( self::OPTION_FIRST_CONNECTED, time() );
		}
	}

	/**
	 * Record the first successful frontend block render (no-op when already recorded)
	 */
	public static function record_first_render() {
		if ( ! get_option( self::OPTION_FIRST_RENDER ) ) {
			add_option( self::OPTION_FIRST_RENDER, time() );
		}
	}

	/**
	 * Backfill the connection milestone for existing installs
	 *
	 * Sites connected before this feature existed have no recorded connection
	 * date; treat the first admin visit after upgrading as the milestone.
	 */
	public function maybe_backfill_first_connection() {
		if ( get_option( self::OPTION_FIRST_CONNECTED ) ) {
			return;
		}

		if ( get_option( 'prodshow_shopify_access_token' ) ) {
			self::record_first_connection();
		}
	}

	/**
	 * Handle the notice's action links (rate / maybe later / already did)
	 */
	public function handle_action() {
		if ( ! isset( $_GET['prodshow_review'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$nonce = isset( $_GET['_prodshow_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_prodshow_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		$action = sanitize_key( wp_unslash( $_GET['prodshow_review'] ) );

		if ( 'done' === $action ) {
			update_option( self::OPTION_STATUS, 'done', false );
		} elseif ( 'later' === $action ) {
			update_option( self::OPTION_SNOOZE_UNTIL, time() + self::SNOOZE_PERIOD, false );
		}

		// Background fetch() calls from the notice ignore this; regular
		// clicks get the page back without the action query args.
		wp_safe_redirect( remove_query_arg( array( 'prodshow_review', '_prodshow_nonce' ) ) );
		exit;
	}

	/**
	 * Whether the notice should render on the current screen
	 *
	 * @return bool
	 */
	private function should_show() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( 'done' === get_option( self::OPTION_STATUS ) ) {
			return false;
		}

		// Never ask on a site that is no longer connected to a store.
		if ( ! get_option( 'prodshow_shopify_access_token' ) ) {
			return false;
		}

		if ( (int) get_option( self::OPTION_SNOOZE_UNTIL, 0 ) > time() ) {
			return false;
		}

		$first_connected = (int) get_option( self::OPTION_FIRST_CONNECTED, 0 );
		$first_render    = (int) get_option( self::OPTION_FIRST_RENDER, 0 );

		// Both milestones must exist: the store connected AND the block
		// actually rendered for visitors. Only then is the user happy
		// enough to be worth asking.
		if ( ! $first_connected || ! $first_render ) {
			return false;
		}

		if ( max( $first_connected, $first_render ) + self::WAIT_PERIOD > time() ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		// Only low-noise screens: dashboard, plugins list, and our own pages.
		return in_array( $screen->id, array( 'dashboard', 'plugins' ), true )
			|| false !== strpos( $screen->id, 'products-showcase' );
	}

	/**
	 * Render the review request notice
	 */
	public function maybe_render_notice() {
		if ( ! $this->should_show() ) {
			return;
		}

		$done_url  = wp_nonce_url( add_query_arg( 'prodshow_review', 'done' ), self::NONCE_ACTION, '_prodshow_nonce' );
		$later_url = wp_nonce_url( add_query_arg( 'prodshow_review', 'later' ), self::NONCE_ACTION, '_prodshow_nonce' );
		?>
		<div class="notice notice-info is-dismissible prodshow-review-notice" data-done-url="<?php echo esc_url( $done_url ); ?>">
			<p>
				<strong><?php esc_html_e( 'Enjoying Products Showcase?', 'products-showcase' ); ?></strong>
				<?php esc_html_e( 'Your Shopify products have been live on this site for a while now. If the plugin has been useful, a quick review helps other WordPress + Shopify users find it — and keeps it going.', 'products-showcase' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( self::REVIEW_URL ); ?>" class="button button-primary prodshow-review-rate" data-done-url="<?php echo esc_url( $done_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Rate it on WordPress.org', 'products-showcase' ); ?>
				</a>
				<a href="<?php echo esc_url( $later_url ); ?>" style="margin-left: 8px;">
					<?php esc_html_e( 'Maybe later', 'products-showcase' ); ?>
				</a>
				<a href="<?php echo esc_url( $done_url ); ?>" style="margin-left: 8px;">
					<?php esc_html_e( 'I already did', 'products-showcase' ); ?>
				</a>
			</p>
		</div>
		<script>
		( function () {
			var notice = document.querySelector( '.prodshow-review-notice' );
			if ( ! notice ) {
				return;
			}

			// "Rate it" opens wordpress.org in a new tab; mark done in the background.
			var rate = notice.querySelector( '.prodshow-review-rate' );
			if ( rate ) {
				rate.addEventListener( 'click', function () {
					fetch( rate.getAttribute( 'data-done-url' ), { credentials: 'same-origin' } );
					notice.remove();
				} );
			}

			// The core dismiss (X) button dismisses the request permanently.
			// Only the explicit "Maybe later" link snoozes it for 30 days.
			notice.addEventListener( 'click', function ( event ) {
				if ( event.target.closest( '.notice-dismiss' ) ) {
					fetch( notice.getAttribute( 'data-done-url' ), { credentials: 'same-origin' } );
				}
			} );
		} )();
		</script>
		<?php
	}
}
