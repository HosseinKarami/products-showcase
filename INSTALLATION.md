# Products Showcase – Shopify Integration for WordPress - Installation Guide

## Video Tutorial

Watch the complete setup walkthrough on YouTube: **[How to Connect Shopify to WordPress](https://youtu.be/Ucg95zZiZwk)**

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

### Step 3: Create a Shopify App

1. Go to **Shopify Admin → Settings → Apps and sales channels → Develop apps → Build apps in Dev Dashboard**
   - This opens the Shopify Dev Dashboard
2. Click the **"Create app"** button (top right corner)
3. On the "Create an app" page, look for the **"Start from Dev Dashboard"** section on the right side
4. Enter an app name (e.g., "WordPress Integration") and click **"Create"**

### Step 4: Configure App Version

1. You'll be taken to your app's Home page
2. In the **Versions** section (right side), click **"New version"**
3. Scroll down to the **Access** section:
   - In the **Scopes** field, type or select `read_products`
4. In the **Redirect URLs** field, paste the Redirect URL from your WordPress plugin settings
   - To find this: Go to WordPress Admin → **Shopify Products** and copy the Redirect URL shown
5. Click the **"Release"** button
6. In the popup, optionally enter a version name (e.g., "V1"), then click **"Release"**

   > ⚠️ **Important:** You must click "Release" — otherwise the scopes and redirect URL won't be active!

### Step 5: Set Up Distribution

1. In the left sidebar, click **"Distribution"** (or from the Home page, click "Select distribution method")
2. Select **"Custom distribution"** and click **"Select"**
3. Enter your store domain (e.g., `your-store.myshopify.com`)
4. Click **"Generate link"**
5. Confirm in the popup by clicking **"Generate link"** again

### Step 6: Install App on Your Store

1. Copy the generated **Install link** and open it in your browser
2. You'll be redirected to your Shopify Admin with an "Install app" page
3. Review the permissions and click **"Install"**

### Step 7: Get Credentials

1. In the left sidebar, click **"Settings"**
2. In the **Credentials** section, you'll find:
   - **Client ID** — click the copy button to copy it
   - **Secret** — click the eye icon to reveal, then copy it

### Step 8: Connect to WordPress

1. Go to WordPress Admin → **Shopify Products**
2. Enter your **Shopify Store URL**
   - Example: `your-store.myshopify.com`
   - Don't include `https://`
3. Paste your **Client ID** (from Step 7)
4. Paste your **Client Secret** (from Step 7)
5. Click **"Connect to Shopify"**
6. You'll be redirected to Shopify to authorize
7. After authorizing, you're redirected back to WordPress
8. You should see a green "Connected to Shopify!" message

The plugin automatically detects the latest Shopify API version - no manual configuration needed!

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

### Can't configure scopes or redirect URLs

If you're having trouble configuring your app in the Shopify Dev Dashboard:

1. **Click "New version"**: From your app's Home page, find the Versions section on the right side and click "New version". You cannot edit an existing released version.

2. **Look in the Access section**: Scroll down on the version page to find the "Access" section where you can add scopes and redirect URLs.

3. **Remember to Release**: After configuring, you must click the "Release" button and confirm in the popup. Without releasing, your configuration won't be active.

4. **Check your permissions**: Only account owners or users with appropriate permissions can manage apps.

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
1. Go to Shopify Products settings
2. Verify Store URL is correct (no https://, no trailing slash)
3. Verify Client ID and Client Secret are correct
4. Make sure the Redirect URL is added to your Shopify app's "Allowed redirection URL(s)"
5. Click "Connect to Shopify" and complete the OAuth flow
6. Look for connection confirmation

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
DELETE FROM wp_options WHERE option_name LIKE '_transient_prodshow_shopify_%';
DELETE FROM wp_options WHERE option_name LIKE '_transient_timeout_prodshow_shopify_%';
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

