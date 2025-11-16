=== Products Showcase – Shopify Integration ===
Contributors: hosseinkarami
Tags: shopify, ecommerce, products, gutenberg, blocks
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Shopify products and collections in beautiful carousels using Gutenberg blocks.

== Source Code ==

This plugin includes compiled JavaScript and CSS files. The source code is fully available:

* **Source code repository**: https://github.com/HosseinKarami/products-showcase
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

Products Showcase – Shopify Integration is a powerful plugin that allows you to display your Shopify products and collections directly on your WP website using beautiful, responsive carousels.

= Features =

* **Easy Setup**: Connect your Shopify store with just an API key
* **Gutenberg Block**: Native WordPress block editor integration
* **Product Carousel**: Display multiple products in a touch-friendly carousel
* **Collection Support**: Show entire collections from your Shopify store
* **Color Swatches**: Display product color options with visual swatches
* **Responsive Design**: Looks great on all devices
* **Smart Caching**: Reduces API calls with intelligent caching
* **Stock Filtering**: Automatically hides out-of-stock products
* **Customizable**: Control how many products to display
* **Performance Optimized**: Lazy loading images and efficient code

= Requirements =

* WordPress 6.0 or higher
* PHP 8.1 or higher
* A Shopify store with Admin API access

= Optional =

* [WPGraphQL](https://wordpress.org/plugins/wp-graphql/) plugin (for headless/decoupled WordPress setups)

== External Services ==

This plugin connects to the Shopify Admin API to fetch and display product information from your Shopify store.

**Service Provider**: Shopify Inc.
**Service Used**: Shopify Admin GraphQL API
**Endpoint**: https://[your-store].myshopify.com/admin/api/2025-10/graphql.json

**What data is sent**:
* Your Shopify store URL (configured in plugin settings)
* Admin API access token (configured in plugin settings)
* GraphQL queries requesting product and collection data

**When data is sent**:
* When you search for products or collections in the WordPress block editor
* When displaying products on your website frontend (cached for performance)
* When manually clearing the plugin cache from settings

**Why this service is required**:
This plugin's core functionality is to display Shopify products on your WordPress site. The Shopify API connection is essential to fetch real-time product information, including titles, prices, images, and availability.

**User Privacy**:
No personal data from your WordPress site visitors is transmitted to Shopify through this plugin. Only administrative product queries are made using your store's API credentials.

**Legal Information**:
* Shopify Terms of Service: https://www.shopify.com/legal/terms
* Shopify Privacy Policy: https://www.shopify.com/legal/privacy
* Shopify API Terms: https://www.shopify.com/legal/api-terms

= Use Cases =

* Showcase featured products on your blog
* Display seasonal collections on landing pages
* Add product carousels to content-heavy sites
* Create product highlights in editorial content
* Bridge content and commerce seamlessly

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/products-showcase/`, or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Shopify Products to configure your API credentials
4. Add your Shopify store URL and Admin API access token
5. Create or edit a page/post and add the "Shopify Products" block
6. Search and select products or a collection to display

= Getting Your Shopify API Credentials =

1. Log in to your Shopify Admin
2. Go to Settings → Apps and sales channels
3. Click "Develop apps"
4. Create a new app or select an existing one
5. Configure Admin API scopes (required: `read_products`)
6. Install the app to your store
7. Copy the Admin API access token
8. Paste it into the plugin settings

== Frequently Asked Questions ==

= Do I need a Shopify account? =

Yes, you need an active Shopify store to use this plugin.

= Does this plugin process payments? =

No, this plugin only displays products. When users click on products, they're redirected to your Shopify store to complete the purchase.

= How often does product data update? =

Product data is cached for 1 hour by default (configurable in settings). You can manually clear the cache from the settings page.

= Can I customize the appearance? =

Yes! The plugin includes CSS classes that you can target with custom CSS. Advanced users can also override the template files.

= Does it work with headless WordPress? =

Yes! If you have WPGraphQL installed, the plugin registers GraphQL types for querying product data.

= Will this slow down my site? =

No. The plugin uses caching to minimize API calls and loads assets only on pages where the block is used.

== Screenshots ==

1. Plugin settings page
2. Shopify block in Gutenberg editor
3. Product carousel on frontend
4. Single product display
5. Product search autocomplete
6. Color swatches display

== Changelog ==

= 1.0.0 =
* Initial release
* Product carousel display
* Collection support
* Color swatches
* Search autocomplete
* Caching system
* Responsive design

== Upgrade Notice ==

= 1.0.0 =
Initial release of Products Showcase – Shopify Integration.

== Credits ==

* Built with [Embla Carousel](https://www.embla-carousel.com/)
* Uses [ACF Builder](https://github.com/StoutLogic/acf-builder) for field registration

== Support ==

For support, please visit the [plugin support forum](https://wordpress.org/support/plugin/products-showcase/) or open an issue on [GitHub](https://github.com/HosseinKarami/products-showcase).

== Privacy Policy ==

This plugin connects to Shopify's API to fetch product data. Product information is cached on your WordPress server. No personal data is collected or transmitted by this plugin. Please review Shopify's privacy policy for information about how Shopify handles data.

