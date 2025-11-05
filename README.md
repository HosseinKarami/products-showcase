# Products Showcase – Shopify Integration for WordPress

A powerful WordPress plugin that displays Shopify products and collections in beautiful, responsive carousels using **native Gutenberg blocks**.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
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

### 1. Get Shopify API Credentials

1. Log in to your **Shopify Admin**
2. Navigate to **Settings → Apps and sales channels**
3. Click **"Develop apps"**
4. **Create a new app** or select existing
5. Configure **Admin API scopes**:
   - ✅ `read_products` (required)
6. **Install the app** to your store
7. Copy the **Admin API access token**

### 2. Configure Plugin Settings

1. Go to **Settings → Shopify Products** in WordPress admin
2. Enter your **Shopify Store URL** (e.g., `your-store.myshopify.com`)
3. Paste your **Admin API Access Token**
4. Set **Cache Duration** (default: 1 hour)
5. Click **Save Settings**

The plugin will test the connection and show you a success message if configured correctly.

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

## 🎨 Customization

### Custom CSS

Target these classes for styling:

```css
/* Container */
.wp-block-products-showcase-products { }
.sps-shopify-block { }
.sps-container { }

/* Header */
.sps-title { }
.sps-description { }
.sps-cta-button { }

/* Carousel */
.sps-carousel { }
.sps-carousel-viewport { }
.sps-carousel-container { }
.sps-carousel-btn { }

/* Product Cards */
.sps-product-card { }
.sps-product-image { }
.sps-product-title { }
.sps-product-price { }
.sps-product-swatches { }
.sps-swatch { }

/* Single Product Layout */
.sps-single-product { }
.sps-single-info { }
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
GET  /wp-json/sps-shopify/v1/connection-status
GET  /wp-json/sps-shopify/v1/search-products?query=shirt
GET  /wp-json/sps-shopify/v1/search-collections?query=summer
POST /wp-json/sps-shopify/v1/clear-cache
GET  /wp-json/sps-shopify/v1/cache-status
```

### Hooks & Filters

```php
// Modify cache duration
add_filter('sps_cache_duration', function($duration) {
    return 2 * HOUR_IN_SECONDS;
});

// Customize product data before display
add_filter('sps_product_data', function($product) {
    // Modify product data
    return $product;
}, 10, 1);

// Add custom product filtering
add_filter('sps_filter_products', function($products) {
    // Filter products array
    return $products;
}, 10, 1);
```

### Programmatic Usage

```php
// Get Shopify API instance
$shopify_api = new SPS_Shopify_API();

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

1. **Check REST API** - Visit `/wp-json/sps-shopify/v1/connection-status`
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

