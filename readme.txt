=== Products Showcase – Shopify Integration ===
Contributors: hosseinkarami
Tags: shopify, ecommerce, products, gutenberg, blocks
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Shopify products and collections in beautiful carousels using native Gutenberg blocks.

== Source Code ==

This plugin includes compiled JavaScript and CSS files. The source code is fully available:

* **Source code repository**: [GitHub - HosseinKarami/products-showcase](https://github.com/HosseinKarami/products-showcase)
* **Source files location**: The `src/` directory contains all uncompiled source code (React components, SCSS files)
* **Build tool**: The plugin uses @wordpress/scripts (Webpack) for compilation
* **Development guide**: See README.md for complete build instructions

To build from source:
```
npm install
npm run build
```

For development with hot reloading:
```
npm start
```

== Description ==

Products Showcase – Shopify Integration is a powerful WordPress plugin that allows you to display your Shopify products and collections directly on your WordPress website using beautiful, responsive carousels built with native Gutenberg blocks.

= Features =

* **Native Gutenberg Block** - Built with React, zero external dependencies for block functionality
* **Beautiful Carousels** - Touch-friendly product carousels powered by Embla Carousel
* **Smart Product Display** - Show individual products or entire collections
* **Color Swatches** - Visual product color options with hover effects
* **Fully Responsive** - Optimized for all screen sizes and devices
* **Performance Optimized** - Smart caching and lazy loading for optimal performance
* **Live Search** - Real-time product/collection search in block editor
* **Stock Filtering** - Automatically hides out-of-stock items
* **Customizable Display** - Control product limits and display options
* **Two Display Modes** - Carousel for multiple products, special layout for single product
* **Modern Development** - Built with @wordpress/scripts and webpack

= Requirements =

* WordPress 6.0 or higher
* PHP 8.1 or higher
* Node.js 18+ and npm 8+ (for development only)
* A Shopify store with Admin API access

= Optional =

* [WPGraphQL](https://wordpress.org/plugins/wp-graphql/) plugin (for headless/decoupled WordPress setups)

== External Services ==

This plugin relies on the **Shopify Admin API** (a third-party external service) to function. The plugin connects to your Shopify store's GraphQL API to retrieve product and collection information for display on your WordPress website.

= What is the service and what is it used for? =

**Service Name**: Shopify Admin API (GraphQL endpoint)
**Service Provider**: Shopify Inc.
**Purpose**: To fetch product and collection data from your Shopify store for display on your WordPress site

The Shopify API is the core service that provides all product information including:
* Product titles, descriptions, and pricing
* Product images and media
* Product variants and options (sizes, colors, etc.)
* Product availability and stock status
* Collection information and organization

= What data is sent and when? =

**Data Transmitted to Shopify**:
1. **Shopify Store URL** - Your store's domain (e.g., `your-store.myshopify.com`) configured in plugin settings
2. **OAuth Credentials** - Client ID and Client Secret are used during the one-time OAuth authorization flow
3. **Admin API Access Token** - Obtained automatically via OAuth and used for all subsequent API requests
4. **GraphQL Queries** - Specific queries requesting product and collection data

**When Data is Transmitted**:
* **In WordPress Admin**: When you search for products or collections while editing content in the block editor
* **On Frontend**: When your website displays products to visitors (first view only, then cached)
* **During Testing**: When you test your API connection in the plugin settings page
* **Manual Cache Clear**: When you manually clear cached product data from the settings page

**Important**: The plugin includes smart caching (default: 1 hour) to minimize API requests. After the initial data fetch, subsequent page loads use cached data and do NOT make additional API calls until the cache expires.

= Privacy and Data Protection =

**No Visitor Data Transmitted**: This plugin does NOT send any personal information about your WordPress site visitors to Shopify. The only data transmitted is:
* Your store's administrative credentials (configured by you)
* Product search queries (initiated by site administrators)

**Server-Side Only**: All API requests are made server-side from your WordPress installation. No client-side (browser) connections to Shopify are made.

**Data Storage**: Product information retrieved from Shopify is cached locally in your WordPress database. No data is sent back to Shopify.

= Service Links and Legal Information =

By using this plugin, you are subject to Shopify's terms of service and privacy policies:

* [Shopify Terms of Service](https://www.shopify.com/legal/terms)
* [Shopify Privacy Policy](https://www.shopify.com/legal/privacy)
* [Shopify API Terms](https://www.shopify.com/legal/api-terms)
* [API Documentation](https://shopify.dev/api/admin-graphql)

**API Endpoint Format**: `https://{your-store}.myshopify.com/admin/api/2025-10/graphql.json`
(The API version `2025-10` may be updated in future plugin releases to maintain compatibility)

**Note**: The domain `shop.example.com` may appear in the plugin as a placeholder fallback when no store is configured. This is a non-functional example domain—you must configure your actual Shopify store URL in Settings → Shopify Products for the plugin to work.

= Use Cases =

* Showcase featured products on your blog
* Display seasonal collections on landing pages
* Add product carousels to content-heavy sites
* Create product highlights in editorial content
* Bridge content and commerce seamlessly

== Installation ==

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

1. Log in to your Shopify Admin
2. Go to Settings → Apps and sales channels
3. Click "Develop apps"
4. Click "Create an app" and name it (e.g., "WordPress Integration")
5. Go to the "Configuration" tab
6. Under "Admin API integration", click "Configure"
7. Enable the `read_products` scope and save
8. Under "Allowed redirection URL(s)", add the Redirect URL shown in your WordPress plugin settings
9. Go to the "API credentials" tab
10. Copy the **Client ID** and **Client secret**

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

= Adding the Block =

1. Edit any page or post in WordPress
2. Click the "+" button to add a new block
3. Search for "Shopify Products"
4. Add the block to your content

= Block Settings =

All settings are available in the block sidebar (Inspector Controls):

**Basic Settings**
* Title: Optional heading for the block
* Description: Optional description text

**Content Type**
* Individual Products: Select specific products manually
* Collection: Display all products from a collection

**Products Mode**
* Search Products: Type to search and select products
* Products display with image preview
* Reorder: Use up/down arrows to change order
* Remove: Delete products from selection

**Collection Mode**
* Search Collection: Type to find collections
* Shows collection name and product count
* Product Limit: Control how many products to show (1-50)

= Display Modes =

**Multiple Products** - Displays as a carousel:
* Touch/swipe enabled on mobile devices
* Arrow navigation on desktop
* Automatically hides navigation if only 1 product

**Single Product** - Special two-column layout:
* Product image + info on left
* Product details on right
* Perfect for featured products

== Customization ==

= Custom CSS Classes =

Target these CSS classes for custom styling:

```css
/* Container */
.wp-block-products-showcase-products { }
.prodshow-shopify-block { }
.prodshow-container { }

/* Header */
.prodshow-title { }
.prodshow-description { }
.prodshow-cta-button { }

/* Carousel */
.prodshow-carousel { }
.prodshow-carousel-viewport { }
.prodshow-carousel-container { }
.prodshow-carousel-btn { }

/* Product Cards */
.prodshow-product-card { }
.prodshow-product-image { }
.prodshow-product-title { }
.prodshow-product-price { }
.prodshow-product-swatches { }
.prodshow-swatch { }

/* Single Product Layout */
.prodshow-single-product { }
.prodshow-single-info { }
```

= Template Overrides =

Copy templates to your theme for customization:

```
your-theme/
  products-showcase/
    block-template.php
    product-card.php
```

The plugin will automatically use your theme templates if they exist.

== Developer Documentation ==

= REST API Endpoints =

The plugin registers these REST API endpoints:

* `GET /wp-json/prodshow-shopify/v1/connection-status` - Check API connection
* `GET /wp-json/prodshow-shopify/v1/search-products?query=shirt` - Search products
* `GET /wp-json/prodshow-shopify/v1/search-collections?query=summer` - Search collections
* `POST /wp-json/prodshow-shopify/v1/clear-cache` - Clear product cache
* `GET /wp-json/prodshow-shopify/v1/cache-status` - Get cache status

= Hooks & Filters =

Modify cache duration:
```php
add_filter('prodshow_cache_duration', function($duration) {
    return 2 * HOUR_IN_SECONDS;
});
```

Customize product data before display:
```php
add_filter('prodshow_product_data', function($product) {
    // Modify product data
    return $product;
}, 10, 1);
```

Add custom product filtering:
```php
add_filter('prodshow_filter_products', function($products) {
    // Filter products array
    return $products;
}, 10, 1);
```

= Programmatic Usage =

```php
// Get Shopify API instance
$shopify_api = new PRODSHOW_Shopify_API();

// Search products
$products = $shopify_api->search_products('shirt');

// Search collections
$collections = $shopify_api->search_collections('summer');

// Fetch product data
$product = $shopify_api->fetch_product_data('gid://shopify/Product/123456');

// Fetch collection products  
$products = $shopify_api->fetch_collection_products('gid://shopify/Collection/789', 12);
```

= Build Scripts =

```bash
# Development - watch for changes
npm start

# Production build
npm run build

# Linting
npm run lint:js        # Lint JavaScript
npm run lint:css       # Lint styles
npm run lint:pkg-json  # Lint package.json

# Formatting
npm run format         # Auto-fix code style

# Create plugin zip
npm run plugin-zip
```

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
2. Verify connection - Green checkmark should appear after saving
3. Clear cache using button in settings
4. Check product status - Only ACTIVE products are displayed
5. Check stock - Out of stock products are automatically filtered

= Block Not Appearing in Editor =

1. Rebuild assets: Run `npm run build` in plugin directory
2. Check browser console for JavaScript errors
3. Clear browser cache and WordPress cache
4. Verify file permissions on build directory

= Search Not Working =

1. Check REST API - Visit `/wp-json/prodshow-shopify/v1/connection-status`
2. Verify API credentials are correct
3. Check browser console for errors
4. Ensure your WordPress REST API is accessible

= Build Errors During Development =

1. Ensure Node.js 18+ is installed
2. Clean install:
   ```
   rm -rf node_modules package-lock.json
   npm install
   npm run build
   ```
3. Check @wordpress/scripts version compatibility

== Screenshots ==

1. Plugin settings page with Shopify API configuration
2. Shopify Products block in Gutenberg editor with search interface
3. Product carousel display on frontend with navigation
4. Single product two-column layout
5. Product search autocomplete in block editor
6. Color swatches display with hover effects

== Changelog ==

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

= 1.1.0 =
Major update! Now featuring OAuth 2.0 authentication - no more manual token copying. Just enter your Client ID & Secret and click Connect. Existing connections will continue to work.

= 1.0.0 =
Initial release of Products Showcase – Shopify Integration. Display your Shopify products beautifully on WordPress!

== Credits ==

* Built with [Embla Carousel](https://www.embla-carousel.com/) for smooth, touch-friendly carousels
* Powered by [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) for modern build tooling
* Inspired by modern e-commerce UX patterns

== Support ==

For support, please visit:
* [Plugin Support Forum](https://wordpress.org/support/plugin/products-showcase/)
* [GitHub Issues](https://github.com/HosseinKarami/products-showcase/issues)
* [Documentation](https://github.com/HosseinKarami/products-showcase/blob/main/README.md)

== Privacy Policy ==

This plugin connects to Shopify's API to fetch product data. Product information is cached locally on your WordPress server. The plugin does NOT collect or transmit any personal data about your site visitors.

**Third-Party Service**: This plugin uses the Shopify Admin API. Please review [Shopify's privacy policy](https://www.shopify.com/legal/privacy) for information about how Shopify handles data.

**Data Stored Locally**: Product titles, descriptions, images, prices, and variants are cached in your WordPress database to improve performance and reduce API calls.

**No Tracking**: This plugin does not use cookies, does not track users, and does not send any analytics data to third parties.

== Translation ==

The plugin is translation-ready:

* Text domain: `products-showcase`
* Translation files location: `/languages/` directory
* Use [Loco Translate](https://wordpress.org/plugins/loco-translate/) or [Poedit](https://poedit.net/) to create translations

== Contributing ==

Contributions are welcome! Please visit [GitHub](https://github.com/HosseinKarami/products-showcase) to:
* Report bugs
* Suggest features
* Submit pull requests
* Review the development guidelines

Made with ❤️ by Hossein Karami
