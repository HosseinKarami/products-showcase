/**
 * Admin JavaScript for Shopify Product Showcase
 * Settings page behaviour (connection, cache, OAuth buttons)
 */

(function($) {
    'use strict';

    /**
     * Settings Page Enhancements
     */
    $(document).ready(function() {
        // Move WP's Help / Screen Options bar to sit directly beneath the hero.
        prodshowRelocateScreenMeta();

        // Auto-hide success notices after 5 seconds
        $('.prodshow-connection-status.success').delay(5000).fadeOut(400);

        // Form validation for settings page
        $('#prodshow-settings form').on('submit', function(e) {
            var shopifyUrl = $('#prodshow_shopify_url').val();

            // Basic validation
            if (shopifyUrl && !shopifyUrl.match(/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/)) {
                e.preventDefault();
                alert('Please enter a valid Shopify store URL (e.g., your-store.myshopify.com)');
                $('#prodshow_shopify_url').focus();
                return false;
            }
            
            // Add visual feedback to button
            var $btn = $(this).find('.button-primary');
            $btn.val('Saving...');
            
            return true; // Allow form to submit
        });

        // Clear Cache button handler
        $('#prodshow-clear-cache-btn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear the cache? This will fetch fresh data from Shopify on the next request.')) {
                return;
            }
            
            var $btn = $(this);
            var originalText = $btn.text();
            var nonce = $('#prodshow-clear-cache-nonce').val();
            
            // Disable button and show loading state
            $btn.prop('disabled', true).text('Clearing...');
            
            // Create and submit a hidden form
            var $form = $('<form method="post" action="">')
                .append($('<input type="hidden" name="prodshow_clear_cache" value="1">'))
                .append($('<input type="hidden" name="_wpnonce" value="' + nonce + '">'))
                .appendTo('body');
            
            // Submit the form which will reload the page with success message
            $form.submit();
        });

        /**
         * OAuth Connect to Shopify
         */
        $('#prodshow-connect-btn').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $spinner = $('.prodshow-oauth-spinner');
            var nonce = $('#prodshow-oauth-nonce').val();
            
            // Get form values
            var shopUrl = $('#prodshow_shopify_url').val().trim();
            var clientId = $('#prodshow_shopify_client_id').val().trim();
            var clientSecret = $('#prodshow_shopify_client_secret').val().trim();
            
            // Validate fields
            if (!shopUrl) {
                alert('Please enter your Shopify store URL.');
                $('#prodshow_shopify_url').focus();
                return;
            }
            
            if (!clientId) {
                alert('Please enter your Client ID.');
                $('#prodshow_shopify_client_id').focus();
                return;
            }
            
            if (!clientSecret) {
                alert('Please enter your Client Secret.');
                $('#prodshow_shopify_client_secret').focus();
                return;
            }
            
            // Validate shop URL format
            if (!shopUrl.match(/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/)) {
                alert('Please enter a valid Shopify store URL (e.g., your-store.myshopify.com)');
                $('#prodshow_shopify_url').focus();
                return;
            }
            
            // Show loading state
            $btn.prop('disabled', true);
            $spinner.addClass('is-active');
            
            // Make AJAX request to initiate OAuth
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'prodshow_initiate_oauth',
                    nonce: nonce,
                    shop_url: shopUrl,
                    client_id: clientId,
                    client_secret: clientSecret
                },
                success: function(response) {
                    if (response.success && response.data.redirect_url) {
                        // Redirect to Shopify for authorization
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert(response.data.message || 'Failed to initiate connection. Please try again.');
                        $btn.prop('disabled', false);
                        $spinner.removeClass('is-active');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('OAuth Error:', error);
                    alert('Connection error. Please try again.');
                    $btn.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });

        /**
         * Disconnect from Shopify
         */
        $('#prodshow-disconnect-btn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to disconnect from Shopify? You will need to reconnect to continue using the plugin.')) {
                return;
            }
            
            var $btn = $(this);
            var nonce = $('#prodshow-oauth-nonce').val() || $('input[name="_wpnonce"]').val();
            
            // If no nonce found, create one from the hidden field
            if (!nonce) {
                // Try to get it from the page
                nonce = $('#prodshow-clear-cache-nonce').val();
            }
            
            $btn.prop('disabled', true).text('Disconnecting...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'prodshow_disconnect_shopify',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Reload the page to show disconnected state
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'Failed to disconnect. Please try again.');
                        $btn.prop('disabled', false).text('Disconnect');
                    }
                },
                error: function() {
                    alert('Connection error. Please try again.');
                    $btn.prop('disabled', false).text('Disconnect');
                }
            });
        });

        /**
         * Copy Redirect URL to clipboard
         */
        $('#prodshow-copy-redirect-url').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var redirectUrl = $('#prodshow-redirect-url').text();
            
            // Copy to clipboard
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(redirectUrl).then(function() {
                    showCopySuccess($btn);
                }).catch(function() {
                    fallbackCopy(redirectUrl, $btn);
                });
            } else {
                fallbackCopy(redirectUrl, $btn);
            }
        });

        function fallbackCopy(text, $btn) {
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            try {
                document.execCommand('copy');
                showCopySuccess($btn);
            } catch (err) {
                alert('Failed to copy. Please copy manually.');
            }
            $temp.remove();
        }

        function showCopySuccess($btn) {
            var $icon = $btn.find('.dashicons');
            $icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
            $btn.addClass('copied');
            
            setTimeout(function() {
                $icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
                $btn.removeClass('copied');
            }, 2000);
        }

        /**
         * Refresh API Version
         */
        $('#prodshow-refresh-api-version').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $icon = $btn.find('.dashicons');
            var nonce = $('#prodshow-api-version-nonce').val() || $('#prodshow-oauth-nonce').val() || $('#prodshow-clear-cache-nonce').val();
            
            // Show loading state
            $btn.prop('disabled', true);
            $icon.addClass('prodshow-spin');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'prodshow_refresh_api_version',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update the displayed version
                        var $display = $('.prodshow-api-version-display');
                        var newHtml = response.data.version + 
                            ' <span class="prodshow-auto-detected">(' + 'auto-detected' + ')</span>' +
                            ' <button type="button" id="prodshow-refresh-api-version" class="button button-small" title="Refresh API Version">' +
                            '<span class="dashicons dashicons-update"></span></button>';
                        $display.html(newHtml);
                        
                        // Show success feedback
                        alert(response.data.message);
                        
                        // Reload to update all references
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'Failed to refresh API version.');
                        $btn.prop('disabled', false);
                        $icon.removeClass('prodshow-spin');
                    }
                },
                error: function() {
                    alert('Connection error. Please try again.');
                    $btn.prop('disabled', false);
                    $icon.removeClass('prodshow-spin');
                }
            });
        });

    });

    /**
     * Move WP's #screen-meta (the Help / Screen Options slide-down panel) and
     * #screen-meta-links (the Help / Screen Options buttons) from above the
     * page into a slot directly after the branded hero, so the collapse bar
     * appears below the hero instead of floating over the top of the page.
     *
     * Preserves WP's native DOM order (panel before buttons) so the slide-down
     * behaviour keeps working. CSS keeps the bar hidden until the body gets the
     * prodshow-meta-ready class added here, avoiding a flash in its default
     * position.
     */
    function prodshowRelocateScreenMeta() {
        if (!document.body.classList.contains('prodshow-active')) {
            return;
        }

        var hero = document.querySelector('.prodshow-hero');
        if (!hero) {
            return;
        }

        var meta = document.getElementById('screen-meta');
        var metaLinks = document.getElementById('screen-meta-links');

        var anchor = hero;
        if (meta) {
            anchor.after(meta);
            anchor = meta;
        }
        if (metaLinks) {
            anchor.after(metaLinks);
        }

        document.body.classList.add('prodshow-meta-ready');
    }

})(jQuery);

