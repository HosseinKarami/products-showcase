/**
 * Admin JavaScript for Shopify Product Showcase
 * Handles product/collection search autocomplete in ACF fields
 */

(function($) {
    'use strict';

    // Initialize when ACF is ready
    if (typeof acf !== 'undefined') {
        acf.addAction('ready', function() {
            initializeShopifySearch();
        });
        
        acf.addAction('append', function() {
            initializeShopifySearch();
        });
    } else {
        $(document).ready(function() {
            initializeShopifySearch();
        });
    }

    function initializeShopifySearch() {
        // Product Search
        $('.prodshow-product-search input[type="text"]').each(function() {
            var $input = $(this);
            
            // Skip if already initialized
            if ($input.data('prodshow-initialized')) {
                return;
            }
            
            $input.data('prodshow-initialized', true);
            
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: prodshowAdminVars.ajax_url,
                        dataType: 'json',
                        data: {
                            action: 'prodshow_search_shopify_products',
                            nonce: prodshowAdminVars.nonce,
                            term: request.term
                        },
                        success: function(data) {
                            if (data.success && data.data.products) {
                                response($.map(data.data.products, function(product) {
                                    return {
                                        label: product.title,
                                        value: product.title,
                                        id: product.id,
                                        handle: product.handle,
                                        image: product.image,
                                        price: product.price,
                                        currency: product.currency
                                    };
                                }));
                            } else {
                                response([]);
                            }
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    var $row = $input.closest('.acf-row');
                    $row.find('.prodshow-product-id input').val(ui.item.id);
                    $row.find('.prodshow-product-handle input').val(ui.item.handle);
                    $row.find('.prodshow-product-image input').val(ui.item.image);
                    
                    // Show preview
                    showProductPreview($row, ui.item);
                    
                    return true;
                }
            }).autocomplete('instance')._renderItem = function(ul, item) {
                return $('<li>')
                    .append('<div class="ui-menu-item">' +
                        (item.image ? '<img src="' + item.image + '" alt="">' : '') +
                        '<div>' +
                            '<div class="ui-menu-item-title">' + item.label + '</div>' +
                            '<div class="ui-menu-item-meta">' + item.price + ' ' + item.currency + '</div>' +
                        '</div>' +
                    '</div>')
                    .appendTo(ul);
            };
        });

        // Collection Search
        $('.prodshow-collection-search input[type="text"]').each(function() {
            var $input = $(this);
            
            // Skip if already initialized
            if ($input.data('prodshow-initialized')) {
                return;
            }
            
            $input.data('prodshow-initialized', true);
            
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: prodshowAdminVars.ajax_url,
                        dataType: 'json',
                        data: {
                            action: 'prodshow_search_shopify_collections',
                            nonce: prodshowAdminVars.nonce,
                            term: request.term
                        },
                        success: function(data) {
                            if (data.success && data.data.collections) {
                                response($.map(data.data.collections, function(collection) {
                                    return {
                                        label: collection.title,
                                        value: collection.title,
                                        id: collection.id,
                                        handle: collection.handle,
                                        image: collection.image
                                    };
                                }));
                            } else {
                                response([]);
                            }
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    var $field = $input.closest('.acf-fields');
                    $field.find('.prodshow-collection-id input').val(ui.item.id);
                    $field.find('.prodshow-collection-handle input').val(ui.item.handle);
                    $field.find('.prodshow-collection-image input').val(ui.item.image);
                    
                    // Show preview
                    showCollectionPreview($field, ui.item);
                    
                    return true;
                }
            }).autocomplete('instance')._renderItem = function(ul, item) {
                return $('<li>')
                    .append('<div class="ui-menu-item">' +
                        (item.image ? '<img src="' + item.image + '" alt="">' : '') +
                        '<div>' +
                            '<div class="ui-menu-item-title">' + item.label + '</div>' +
                            '<div class="ui-menu-item-meta">' + item.handle + '</div>' +
                        '</div>' +
                    '</div>')
                    .appendTo(ul);
            };
        });
    }

    function showProductPreview($row, item) {
        // Remove existing preview
        $row.find('.prodshow-product-preview').remove();
        
        // Create preview
        var $preview = $('<div class="prodshow-product-preview">' +
            (item.image ? '<img src="' + item.image + '" class="prodshow-preview-image" alt="">' : '') +
            '<div class="prodshow-preview-info">' +
                '<div class="prodshow-preview-title">' + item.label + '</div>' +
                '<div class="prodshow-preview-handle">' + item.handle + '</div>' +
            '</div>' +
            '<button type="button" class="prodshow-preview-remove">Remove</button>' +
        '</div>');
        
        $row.find('.prodshow-product-search .acf-input').append($preview);
        
        // Handle remove
        $preview.find('.prodshow-preview-remove').on('click', function() {
            $row.find('.prodshow-product-search input').val('');
            $row.find('.prodshow-product-id input').val('');
            $row.find('.prodshow-product-handle input').val('');
            $row.find('.prodshow-product-image input').val('');
            $preview.remove();
        });
    }

    function showCollectionPreview($field, item) {
        // Remove existing preview
        $field.find('.prodshow-collection-preview').remove();
        
        // Create preview
        var $preview = $('<div class="prodshow-collection-preview">' +
            (item.image ? '<img src="' + item.image + '" class="prodshow-preview-image" alt="">' : '') +
            '<div class="prodshow-preview-info">' +
                '<div class="prodshow-preview-title">' + item.label + '</div>' +
                '<div class="prodshow-preview-handle">' + item.handle + '</div>' +
            '</div>' +
            '<button type="button" class="prodshow-preview-remove">Remove</button>' +
        '</div>');
        
        $field.find('.prodshow-collection-search .acf-input').append($preview);
        
        // Handle remove
        $preview.find('.prodshow-preview-remove').on('click', function() {
            $field.find('.prodshow-collection-search input').val('');
            $field.find('.prodshow-collection-id input').val('');
            $field.find('.prodshow-collection-handle input').val('');
            $field.find('.prodshow-collection-image input').val('');
            $preview.remove();
        });
    }

    /**
     * Settings Page Enhancements
     */
    $(document).ready(function() {
        // Smooth scroll for navigation links
        $('.prodshow-menu nav a').on('click', function(e) {
            var href = $(this).attr('href');
            // Only handle if it's a fragment link
            if (href && href.indexOf('#') !== -1) {
                e.preventDefault();
                var target = $(href);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 50
                    }, 300);
                }
            }
        });

        // Auto-hide success notices after 5 seconds
        $('.prodshow-connection-status.success').delay(5000).fadeOut(400);

        // Form validation for settings page
        $('#prodshow-settings form').on('submit', function(e) {
            console.log('PRODSHOW: Form submit event triggered');
            
            var shopifyUrl = $('#prodshow_shopify_url').val();
            var accessToken = $('#prodshow_shopify_access_token').val();

            console.log('PRODSHOW: Shopify URL:', shopifyUrl);
            console.log('PRODSHOW: Access Token length:', accessToken ? accessToken.length : 0);

            // Basic validation
            if (shopifyUrl && !shopifyUrl.match(/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/)) {
                console.log('PRODSHOW: Validation FAILED - Invalid Shopify URL format');
                e.preventDefault();
                alert('Please enter a valid Shopify store URL (e.g., your-store.myshopify.com)');
                $('#prodshow_shopify_url').focus();
                return false;
            }
            
            console.log('PRODSHOW: Validation PASSED - Form will submit');
            
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

})(jQuery);

