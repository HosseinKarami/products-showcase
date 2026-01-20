=== Products Showcase – Shopify Integration ===
Contributors: hosseinkarami
Donate link: https://buymeacoffee.com/hosseinkarami
Tags: shopify, ecommerce, products, gutenberg, blocks
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.1.1
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Shopify products and collections in beautiful carousels using native Gutenberg blocks.

== Description ==

Display your Shopify products on WordPress with beautiful, responsive carousels. Built as a native Gutenberg block with OAuth authentication for easy setup.

[![Watch Video Tutorial](https://img.youtube.com/vi/Ucg95zZiZwk/maxresdefault.jpg)](https://www.youtube.com/watch?v=Ucg95zZiZwk)

= Features =

* **Native Gutenberg Block** - Built with React, zero external dependencies
* **Beautiful Carousels** - Touch-friendly product carousels powered by Embla Carousel
* **Smart Product Display** - Show individual products or entire collections
* **Color Swatches** - Visual product color options with hover effects
* **Fully Responsive** - Optimized for all screen sizes and devices
* **Performance Optimized** - Smart caching and lazy loading
* **Live Search** - Real-time product/collection search in block editor
* **Stock Filtering** - Automatically hides out-of-stock items
* **OAuth Authentication** - Secure one-click connection to Shopify

= Use Cases =

* Showcase featured products on your blog
* Display seasonal collections on landing pages
* Add product carousels to content-heavy sites
* Bridge content and commerce seamlessly

= Requirements =

* WordPress 6.0+, PHP 8.1+
* A Shopify store with Admin API access

= Links =

* [GitHub Repository](https://github.com/HosseinKarami/products-showcase) - Source code & developer docs
* [Documentation](https://github.com/HosseinKarami/products-showcase/blob/main/INSTALLATION.md) - Full installation guide

== External Services ==

This plugin connects to the **Shopify Admin API** to fetch your product data.

**Service Provider**: Shopify Inc.
**Data Transmitted**: Store URL, OAuth credentials (one-time), and product queries
**When**: During setup, when searching products in editor, and when displaying products (cached for 1 hour)

**Privacy**: No visitor data is sent to Shopify. All API calls are server-side. Product data is cached locally.

**Shopify Legal**:
* [Terms of Service](https://www.shopify.com/legal/terms)
* [Privacy Policy](https://www.shopify.com/legal/privacy)
* [API Terms](https://www.shopify.com/legal/api-terms)

== Installation ==

= Video Tutorial =

Watch the complete setup walkthrough on YouTube: [How to Connect Shopify to WordPress](https://youtu.be/Ucg95zZiZwk)

= Standard Installation =

1. Upload the plugin files to `/wp-content/plugins/products-showcase/`, or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Shopify Products to configure your API credentials
4. Add your Shopify store URL and Admin API access token
5. Create or edit a page/post and add the "Shopify Products" block
6. Search and select products or a collection to display

= Development Installation =

For active development with hot reloading:

1. Clone the repository: `git clone https://github.com/HosseinKarami/products-showcase.git`
2. Navigate to plugin directory: `cd products-showcase`
3. Install dependencies: `npm install`
4. Start development server: `npm start` (watches for changes)
5. Activate the plugin in WordPress

= Creating Your Shopify App =

**Step 1: Create the App**

1. Go to Shopify Admin → Settings → Apps and sales channels → Develop apps → Build apps in Dev Dashboard
2. Click the "Create app" button (top right corner)
3. In the "Start from Dev Dashboard" section (right side), enter an app name and click "Create"

**Step 2: Configure App Version**

1. In the Versions section, click "New version"
2. In the Access section, add `read_products` to the Scopes field
3. In the Redirect URLs field, paste the Redirect URL from your WordPress plugin settings
4. Click "Release", then confirm by clicking "Release" again in the popup

**Step 3: Set Up Distribution**

1. Click "Distribution" in the left sidebar
2. Select "Custom distribution" and click "Select"
3. Enter your store domain (e.g., your-store.myshopify.com)
4. Click "Generate link" and confirm

**Step 4: Install the App**

1. Copy the generated install link and open it
2. On the Shopify Admin install page, click "Install"

**Step 5: Get Credentials**

1. Click "Settings" in the left sidebar
2. Copy the **Client ID** and **Secret** from the Credentials section

== Configuration ==

= Easy OAuth Setup =

1. Navigate to **Shopify Products** in WordPress admin
2. Enter your **Shopify Store URL** (e.g., `your-store.myshopify.com`)
3. Paste your **Client ID** from Shopify
4. Paste your **Client Secret** from Shopify
5. Click **"Connect to Shopify"**
6. You'll be redirected to Shopify to authorize the connection
7. After authorizing, you're automatically redirected back - done!

The plugin automatically obtains the access token via secure OAuth and detects the latest Shopify API version.

== Usage ==

1. Edit any page or post in WordPress
2. Click "+" to add a block and search for "Shopify Products"
3. Choose **Products Mode** (select individual products) or **Collection Mode** (display a collection)
4. Use the live search to find and add products
5. Customize title, description, and product limit as needed

The block displays as a touch-friendly carousel on mobile and with arrow navigation on desktop. Single products get a special two-column featured layout.

== Customization ==

For custom CSS classes, template overrides, hooks & filters, and developer documentation, see our [GitHub README](https://github.com/HosseinKarami/products-showcase#-customization).

== Frequently Asked Questions ==

= Do I need a Shopify account? =

Yes, you need an active Shopify store to use this plugin. The plugin fetches product data from your Shopify store via the Admin API.

= Does this plugin process payments? =

No, this plugin only displays products. When users click on products, they're redirected to your Shopify store to complete the purchase.

= How often does product data update? =

Product data is cached for 1 hour by default (configurable in settings). You can manually clear the cache from the settings page at any time to force a refresh.

= Can I customize the appearance? =

Yes! The plugin includes CSS classes that you can target with custom CSS. Advanced users can also override the template files by copying them to their theme.

= Does it work with headless WordPress? =

Yes! If you have WPGraphQL installed, the plugin automatically registers GraphQL types for querying product data in headless/decoupled WordPress setups.

= Will this slow down my site? =

No. The plugin uses intelligent caching to minimize API calls and only loads assets on pages where the block is used. Images are lazy-loaded for optimal performance.

= Can I display products from multiple Shopify stores? =

Currently, the plugin supports one Shopify store per WordPress installation. If you need multi-store support, please open a feature request on GitHub.

= What happens if my Shopify API credentials change? =

Click the "Disconnect" button in Shopify Products settings, then reconnect using the OAuth flow with your new credentials. The plugin will automatically obtain a new access token.

= Can I filter out certain products? =

Out-of-stock products are automatically filtered by default. Developers can use the `prodshow_filter_products` filter hook to implement custom product filtering logic.

== Troubleshooting ==

= Products Not Showing =

1. Check API credentials in Settings → Shopify Products
2. Verify connection shows green checkmark
3. Clear cache using the button in settings
4. Ensure products are ACTIVE and in stock in Shopify

= Can't Configure Shopify App =

1. In Dev Dashboard, click "New version" to edit settings
2. Add scopes in the "Access" section
3. Click "Release" to activate your configuration

For detailed troubleshooting, see our [GitHub documentation](https://github.com/HosseinKarami/products-showcase#-troubleshooting).

== Screenshots ==

1. Plugin settings page with Shopify API configuration
2. Shopify Products block in Gutenberg editor with search interface
3. Product carousel display on frontend with navigation
4. Single product two-column layout
5. Product search autocomplete in block editor
6. Color swatches display with hover effects

== Changelog ==

= 1.1.1 =
* Updated setup instructions for Shopify Dev Dashboard (new app creation flow)
* Improved documentation with step-by-step guidance for Versions, Distribution, and Credentials
* Updated admin Quick Start guide to reflect current Shopify interface

= 1.1.0 =
* **NEW: OAuth 2.0 Authentication** - Easy one-click connection to Shopify using secure OAuth flow
* **NEW: Auto API Version Detection** - Automatically detects and uses the latest Shopify API version
* **NEW: Simplified Setup** - No more manual access token copying - just enter Client ID & Secret and click Connect
* **NEW: Disconnect/Reconnect** - Easy way to change Shopify credentials
* **NEW: Refresh API Version** - Button to manually refresh the detected API version
* Improved admin UI with better connection status display
* Enhanced security with OAuth state validation

= 1.0.0 =
* Initial release
* Native Gutenberg block integration
* Product carousel display with Embla Carousel
* Collection support with product limits
* Color swatches with visual options
* Live search autocomplete in editor
* Intelligent caching system (1 hour default)
* Responsive design for all devices
* Stock filtering (hides out-of-stock items)
* REST API endpoints for product/collection search
* WPGraphQL integration for headless WordPress
* Template override support
* Hooks and filters for developers

== Upgrade Notice ==

= 1.1.1 =
Updated documentation for the new Shopify Dev Dashboard app creation flow. If you're setting up a new connection, follow the updated instructions in the Quick Start guide.

= 1.1.0 =
Major update! Now featuring OAuth 2.0 authentication - no more manual token copying. Just enter your Client ID & Secret and click Connect. Existing connections will continue to work.

= 1.0.0 =
Initial release of Products Showcase – Shopify Integration. Display your Shopify products beautifully on WordPress!

== Credits ==

* Built with [Embla Carousel](https://www.embla-carousel.com/) for smooth, touch-friendly carousels
* Powered by [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)

== Support ==

* [Plugin Support Forum](https://wordpress.org/support/plugin/products-showcase/)
* [GitHub Issues](https://github.com/HosseinKarami/products-showcase/issues)

== Privacy Policy ==

This plugin connects to Shopify's API to fetch product data. Product information is cached locally. The plugin does NOT collect or transmit any visitor data. See [Shopify's privacy policy](https://www.shopify.com/legal/privacy) for their data handling practices.
