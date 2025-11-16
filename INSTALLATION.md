# Products Showcase – Shopify Integration for WordPress - Installation Guide

## Quick Start (5 Minutes)

### Step 1: Install Dependencies

```bash
cd /path/to/wordpress/wp-content/plugins/products-showcase
composer install --no-dev
```

### Step 2: Activate Plugin

1. Go to WordPress Admin → Plugins
2. Find "Products Showcase – Shopify Integration for WordPress"
3. Click "Activate"

### Step 3: Get Shopify API Credentials

1. Log in to your Shopify Admin
2. Go to **Settings → Apps and sales channels**
3. Click **"Develop apps"** (or "Manage private apps" on older Shopify)
4. Click **"Create an app"** or **"Create a new app"**
5. Name it "WordPress Integration" (or anything you like)
6. Click **"Configure Admin API scopes"**
7. Enable these scopes:
   - ☑️ `read_products`
8. Click **"Save"**
9. Click **"Install app"**
10. Reveal and copy the **Admin API access token**

### Step 4: Configure Plugin

1. Go to WordPress Admin → **Settings → Shopify Products**
2. Enter your **Shopify Store URL**
   - Example: `your-store.myshopify.com`
   - Don't include `https://`
3. Paste your **Admin API Access Token**
4. Select **API Version** (use latest: 2024-10)
5. Set **Cache Duration** (default 1 hour is recommended)
6. Click **"Save Settings"**
7. You should see a green "Connected to Shopify!" message

## Using the Block

### Add to Page/Post

1. Edit any page or post
2. Click the **(+)** button to add a block
3. Search for "Shopify" or "Products"
4. Click **"Products Showcase"** block

### Configure Block - Products Mode

1. **Toggle** to "Products" (blue/on position)
2. Enter a **Title** (optional, e.g., "Featured Products")
3. Enter a **Description** (optional)
4. Click **"Add Product"**
5. In the search field, type a product name
6. Select from autocomplete dropdown
7. Repeat to add more products
8. Drag to reorder products

### Configure Block - Collection Mode

1. **Toggle** to "Collection" (gray/off position)
2. Enter a **Title** (optional, e.g., "Winter Collection")  
3. In **"Search Collection"**, type collection name
4. Select from autocomplete
5. Set **"Product Limit"** (1-50, default 12)

### Preview & Publish

1. Click **"Preview"** to see how it looks
2. Click **"Update"** or **"Publish"** when satisfied

## Troubleshooting

### "No products selected or available"

**Cause**: Products are out of stock or inactive in Shopify

**Solution**:
1. Go to Shopify Admin → Products
2. Make sure products are set to "Active"
3. Check inventory levels
4. Clear cache in plugin settings

### "Shopify API credentials not configured"

**Cause**: Missing or incorrect API settings

**Solution**:
1. Go to Settings → Shopify Products
2. Verify Store URL is correct (no https://, no trailing slash)
3. Verify Access Token is correct
4. Click "Save Settings"
5. Look for connection confirmation

### Autocomplete not working in editor

**Cause**: JavaScript conflict or permissions issue

**Solution**:
1. Check browser console for errors (F12)
2. Disable other plugins temporarily
3. Switch to default WordPress theme
4. Clear browser cache
5. Try different browser

### Products showing but carousel not sliding

**Cause**: Embla Carousel not loading

**Solution**:
1. Check if `embla-carousel` script is loaded (View Source)
2. Clear all caches (WordPress, CDN, browser)
3. Check browser console for JavaScript errors
4. Ensure block.js is present in /build/ folder

### Slow performance

**Solution**:
1. Increase cache duration to 6-12 hours
2. Reduce product limit for collections
3. Use a WordPress caching plugin
4. Optimize images in Shopify
5. Use a CDN for assets

## Advanced Configuration

### Custom Styling

Add to your theme's `style.css` or Customizer CSS:

```css
/* Change title color */
.prodshow-title {
    color: #your-brand-color;
}

/* Adjust product card spacing */
.prodshow-product-card {
    min-width: 250px;
}

/* Style the CTA button */
.prodshow-cta-button {
    background-color: #your-brand-color;
}
```

### Template Overrides

Create these files in your theme:

```
wp-content/themes/your-theme/
  products-showcase/
    block-template.php
    product-card.php
```

Copy from plugin's `/templates/` folder and customize.

### Performance Tuning

Add to `wp-config.php` or use Code Snippets plugin:

```php
// Increase cache to 6 hours
add_filter('prodshow_cache_duration', function() {
    return 6 * HOUR_IN_SECONDS;
});

// Modify product filtering
add_filter('prodshow_filter_products', function($products) {
    // Your custom filtering logic
    return $products;
});
```

## Updating the Plugin

### Manual Update

1. Deactivate plugin
2. Delete old plugin folder
3. Upload new version
4. Run `composer install --no-dev`
5. Reactivate plugin

### Via WordPress.org (when published)

1. Go to Dashboard → Updates
2. Check "Products Showcase – Shopify Integration for WordPress"
3. Click "Update Plugins"

## Uninstalling

### Clean Uninstall

1. Go to Plugins
2. Deactivate "Products Showcase – Shopify Integration for WordPress"
3. Click "Delete"
4. This will remove:
   - All plugin files
   - Plugin settings
   - Cached product data

### Manual Cleanup (if needed)

```sql
DELETE FROM wp_options WHERE option_name LIKE 'prodshow_%';
DELETE FROM wp_options WHERE option_name LIKE '_transient_sps_%';
DELETE FROM wp_options WHERE option_name LIKE '_transient_timeout_sps_%';
```

## Support

- **Documentation**: See README.md
- **Issues**: Open on GitHub
- **Questions**: WordPress.org support forum

## Next Steps

1. ✅ Plugin installed and configured
2. ✅ First block added to page
3. 📚 Read full documentation in README.md
4. 🎨 Customize styling if needed
5. 🚀 Launch and enjoy!

---

Need help? Check the full README.md or open an issue on GitHub.

