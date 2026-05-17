# Products Showcase – Shopify Integration

A powerful WordPress plugin that displays Shopify products and collections in beautiful, responsive carousels using **native Gutenberg blocks**.

![Version](https://img.shields.io/badge/version-1.1.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-8.1%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPLv2-green.svg)

## ✨ Features

- **🎨 Native Gutenberg Block** - Built with React, zero external dependencies
- **🎠 Beautiful Carousels** - Touch-friendly product carousels powered by Embla
- **🎯 Smart Product Display** - Show individual products or entire collections
- **🌈 Color Swatches** - Visual product color options with hover effects  
- **📱 Fully Responsive** - Optimized for all screen sizes
- **⚡ Performance Optimized** - Smart caching and lazy loading
- **🔍 Live Search** - Real-time product/collection search in block editor
- **🚫 Stock Filtering** - Automatically hides out-of-stock items
- **🔧 Customizable** - Control product limits and display options
- **🛠️ Modern Development** - Built with @wordpress/scripts and webpack

## 📋 Requirements

- WordPress 6.0 or higher
- PHP 8.1 or higher
- Node.js 18+ and npm 8+ (for development)
- A Shopify store with Admin API access

## 🌐 External Services

This plugin relies on the **Shopify Admin API** (a third-party external service) to function. The plugin connects to your Shopify store's GraphQL API to retrieve product and collection information for display on your WordPress website.

### What is the service and what is it used for?

**Service Name**: Shopify Admin API (GraphQL endpoint)  
**Service Provider**: Shopify Inc.  
**Purpose**: To fetch product and collection data from your Shopify store for display on your WordPress site

The Shopify API is the core service that provides all product information including:
* Product titles, descriptions, and pricing
* Product images and media
* Product variants and options (sizes, colors, etc.)
* Product availability and stock status
* Collection information and organization

### What data is sent and when?

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

### Privacy and Data Protection

**No Visitor Data Transmitted**: This plugin does NOT send any personal information about your WordPress site visitors to Shopify. The only data transmitted is:
* Your store's administrative credentials (configured by you)
* Product search queries (initiated by site administrators)

**Server-Side Only**: All API requests are made server-side from your WordPress installation. No client-side (browser) connections to Shopify are made.

**Data Storage**: Product information retrieved from Shopify is cached locally in your WordPress database. No data is sent back to Shopify.

### Service Links and Legal Information

By using this plugin, you are subject to Shopify's terms of service and privacy policies:

* [Shopify Terms of Service](https://www.shopify.com/legal/terms)
* [Shopify Privacy Policy](https://www.shopify.com/legal/privacy)
* [Shopify API Terms](https://www.shopify.com/legal/api-terms)
* [API Documentation](https://shopify.dev/api/admin-graphql)

**API Endpoint Format**: `https://{your-store}.myshopify.com/admin/api/2025-10/graphql.json`  
(The API version `2025-10` may be updated in future plugin releases to maintain compatibility)

> [!NOTE]
> The domain `shop.example.com` may appear in the plugin as a placeholder fallback when no store is configured. This is a non-functional example domain—you must configure your actual Shopify store URL in Settings → Shopify Products for the plugin to work.

## 🚀 Installation

### Standard Installation

1. **Download** the plugin or clone this repository:
   ```bash
   git clone https://github.com/HosseinKarami/products-showcase.git
   cd products-showcase
   ```

2. **Install Dependencies**:
   ```bash
   # Install npm dependencies
   npm install
   
   # Build the block assets
   npm run build
   
   # (Optional) Install PHP dependencies if using Composer autoloader
   composer install --no-dev
   ```

3. **Upload** to `/wp-content/plugins/products-showcase/`

4. **Activate** the plugin through WordPress admin

5. **Configure** settings at Settings → Products Showcase

### Development Installation

For active development with hot reloading:

```bash
# Clone repository
git clone https://github.com/HosseinKarami/products-showcase.git
cd products-showcase

# Install dependencies
npm install

# Start development server (watches for changes)
npm start

# In another terminal, run linting
npm run lint:js
npm run lint:css
```

## ⚙️ Configuration

### 1. Create a Shopify Custom App

1. Log in to your **Shopify Admin**
2. Navigate to **Settings → Apps and sales channels**
3. Click **"Develop apps"**
4. Click **"Create an app"** and give it a name (e.g., "WordPress Integration")

### 2. Configure API Access

1. In your app, go to the **"Configuration"** tab
2. Under **"Admin API integration"**, click **"Configure"**
3. Enable the following scope:
   - ✅ `read_products` (required)
4. Click **"Save"**
5. **Important**: Under **"Allowed redirection URL(s)"**, add the Redirect URL shown in your WordPress plugin settings

### 3. Get Your Credentials

1. Go to the **"API credentials"** tab in your Shopify app
2. Copy the **Client ID**
3. Copy the **Client secret**

### 4. Connect via OAuth (Easy Setup!)

1. Go to **Shopify Products** in WordPress admin
2. Enter your **Shopify Store URL** (e.g., `your-store.myshopify.com`)
3. Paste your **Client ID**
4. Paste your **Client Secret**
5. Click **"Connect to Shopify"**
6. You'll be redirected to Shopify to authorize the connection
7. After authorizing, you'll be redirected back to WordPress - done! ✅

The plugin automatically:
- Obtains the access token via secure OAuth
- Detects the latest supported Shopify API version
- Tests the connection and displays your store name

## 📖 Usage

### Adding the Block

1. **Edit** any page or post
2. Click **"+"** to add a block
3. Search for **"Shopify Products"**
4. Add the block to your content

### Block Settings

All settings are in the **block sidebar** (Inspector Controls):

#### Basic Settings
- **Title**: Optional heading for the block
- **Description**: Optional description text

#### Content Type
- **Individual Products**: Select specific products manually
- **Collection**: Display all products from a collection

#### Products Mode
- **Search Products**: Type to search and select products
- Products display with image preview
- **Reorder**: Use up/down arrows to change order
- **Remove**: Delete products from selection

#### Collection Mode
- **Search Collection**: Type to find collections
- Shows collection name and product count
- **Product Limit**: Control how many products to show (1-50)

### Display Modes

**Multiple Products** - Displays as a carousel:
- Touch/swipe enabled on mobile
- Arrow navigation on desktop
- Automatically hides nav if only 1 product

**Single Product** - Special two-column layout:
- Product image + info on left
- Product details on right
- Perfect for featured products

### Using the Shortcode (Classic Editor & Page Builders)

If you're not using the block editor, you can display the carousel anywhere
with the `[products_showcase]` shortcode. It works in the Classic editor,
text widgets, and most page builders.

**Show a collection:**

```text
[products_showcase collection="123456789"]
```

**Show specific products:**

```text
[products_showcase products="111111111,222222222,333333333"]
```

**With a title and a call-to-action button:**

```text
[products_showcase collection="123456789" title="Featured" button_text="Shop all" button_url="https://example.com/shop"]
```

You can find a product or collection ID in its Shopify admin URL. IDs may be
the plain number or the full `gid://shopify/...` value; both work.

**Available attributes**

| Attribute | Description | Default |
|-----------|-------------|---------|
| `collection` | Shopify collection ID. Use this **or** `products`. | — |
| `products` | Comma-separated Shopify product IDs. | — |
| `limit` | Max products in collection mode (1–50). | `12` |
| `title` | Optional heading above the carousel. | — |
| `description` | Optional description below the title. | — |
| `button_text` | Call-to-action button label. | — |
| `button_url` | Call-to-action button link. | — |
| `button_new_tab` | Open the button link in a new tab (`yes`/`no`). | `no` |
| `disable_padding` | Remove the default outer padding (`yes`/`no`). | `no` |
| `background` | Block background color. | — |
| `text_color` | Block text color. | — |
| `button_bg` | CTA button background color. | — |
| `button_text_color` | CTA button text color. | — |
| `button_bg_hover` | CTA button background color on hover. | — |
| `button_text_hover` | CTA button text color on hover. | — |

If neither `collection` nor `products` is provided, nothing is displayed on the
front end.

## 🎨 Customization

### Custom CSS

Target these classes for styling:

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

### Template Overrides

Copy templates to your theme for customization:

```
your-theme/
  products-showcase/
    block-template.php
    product-card.php
```

The plugin will use your theme templates if they exist.

## 🔧 Developer Documentation

### Source Code

This plugin uses build tools (npm and webpack via @wordpress/scripts) to compile and minify JavaScript and CSS assets. The **human-readable source code** is available in the following locations:

- **JavaScript Source**: `/src/` directory (React components and block registration)
- **CSS/SCSS Source**: `/src/editor.scss` and `/src/style.scss`
- **PHP Source**: `/includes/` directory (all PHP classes)
- **Templates**: `/templates/` directory (PHP templates)

The compiled/minified production files are located in `/build/` directory. To review or modify the source code:

1. Clone the repository from [GitHub](https://github.com/HosseinKarami/products-showcase)
2. Install dependencies: `npm install`
3. Make changes to files in `/src/` directory
4. Rebuild assets: `npm run build`

For more information about the build process, see the [Build Scripts](#build-scripts) section below.

### Project Structure

```
products-showcase/
├── .editorconfig          # Editor configuration
├── .gitignore            # Git ignore rules
├── package.json          # npm dependencies & scripts
├── phpcs.xml.dist        # PHP CodeSniffer config
├── composer.json         # PHP dependencies (minimal)
├── uninstall.php         # Proper cleanup on uninstall
│
├── src/                  # Source files (React/SCSS)
│   ├── block.json       # Block metadata
│   ├── index.js         # Block registration
│   ├── edit.js          # Block editor component
│   ├── editor.scss      # Editor styles
│   ├── style.scss       # Frontend styles
│   └── components/      # React components
│       ├── ProductSearch.js
│       ├── CollectionSearch.js
│       └── ProductList.js
│
├── build/               # Compiled assets (gitignored)
│   ├── index.js
│   ├── index.asset.php
│   └── *.css
│
├── includes/            # PHP classes
│   ├── class-shopify-api.php
│   ├── class-shopify-block.php
│   ├── class-rest-api.php
│   ├── class-admin-settings.php
│   ├── class-enqueue-assets.php
│   └── class-shopify-graphql-types.php
│
├── assets/              # Static assets
│   ├── admin/          # Admin CSS/JS
│   └── css/            # Frontend styles
│
└── templates/           # PHP templates
    ├── block-template.php
    └── product-card.php
```

### Build Scripts

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

### REST API Endpoints

The plugin registers these REST API endpoints:

```
GET  /wp-json/prodshow-shopify/v1/connection-status
GET  /wp-json/prodshow-shopify/v1/search-products?query=shirt
GET  /wp-json/prodshow-shopify/v1/search-collections?query=summer
POST /wp-json/prodshow-shopify/v1/clear-cache
GET  /wp-json/prodshow-shopify/v1/cache-status
```

### Hooks & Filters

```php
// Modify cache duration
add_filter('prodshow_cache_duration', function($duration) {
    return 2 * HOUR_IN_SECONDS;
});

// Customize product data before display
add_filter('prodshow_product_data', function($product) {
    // Modify product data
    return $product;
}, 10, 1);

// Add custom product filtering
add_filter('prodshow_filter_products', function($products) {
    // Filter products array
    return $products;
}, 10, 1);
```

### Programmatic Usage

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

## 🐛 Troubleshooting

### Products Not Showing

1. **Check API credentials** in Settings → Shopify Products
2. **Verify connection** - Green checkmark should appear
3. **Clear cache** using button in settings
4. **Check product status** - Only ACTIVE products show
5. **Check stock** - Out of stock products are filtered

### Block Not Appearing

1. **Rebuild assets**: Run `npm run build`
2. **Check console** for JavaScript errors
3. **Clear browser cache** and WordPress cache
4. **Verify file permissions** on build directory

### Search Not Working

1. **Check REST API** - Visit `/wp-json/prodshow-shopify/v1/connection-status`
2. **Verify API credentials** are correct
3. **Check browser console** for errors
4. **Test with WordPress REST API Handbook**

### Build Errors

1. **Node version**: Ensure Node.js 18+ is installed
2. **Clean install**:
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   npm run build
   ```
3. **Check @wordpress/scripts** version compatibility


## 🌍 Translation

The plugin is translation-ready:

1. Use [Loco Translate](https://wordpress.org/plugins/loco-translate/) or [Poedit](https://poedit.net/)
2. Text domain: `products-showcase`
3. Translation files go in `/languages/` directory


## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Run linting: `npm run lint:js && npm run lint:css`
4. Commit your changes (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

### Development Guidelines

- Follow WordPress Coding Standards
- Write JSDoc comments for JavaScript
- Test on WordPress 6.0+
- Ensure mobile responsiveness
- Update README if adding features

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 💖 Credits

- Built with [Embla Carousel](https://www.embla-carousel.com/)
- Powered by [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)
- Inspired by modern e-commerce UX patterns

## 🙏 Support

- **Issues**: [GitHub Issues](https://github.com/HosseinKarami/products-showcase/issues)


**Made with ❤️ by [Hossein Karami](https://hosseinkarami.com)**

