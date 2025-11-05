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
        $('.sps-product-search input[type="text"]').each(function() {
            var $input = $(this);
            
            // Skip if already initialized
            if ($input.data('sps-initialized')) {
                return;
            }
            
            $input.data('sps-initialized', true);
            
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: spsAdminVars.ajax_url,
                        dataType: 'json',
                        data: {
                            action: 'sps_search_shopify_products',
                            nonce: spsAdminVars.nonce,
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
                    $row.find('.sps-product-id input').val(ui.item.id);
                    $row.find('.sps-product-handle input').val(ui.item.handle);
                    $row.find('.sps-product-image input').val(ui.item.image);
                    
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
        $('.sps-collection-search input[type="text"]').each(function() {
            var $input = $(this);
            
            // Skip if already initialized
            if ($input.data('sps-initialized')) {
                return;
            }
            
            $input.data('sps-initialized', true);
            
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: spsAdminVars.ajax_url,
                        dataType: 'json',
                        data: {
                            action: 'sps_search_shopify_collections',
                            nonce: spsAdminVars.nonce,
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
                    $field.find('.sps-collection-id input').val(ui.item.id);
                    $field.find('.sps-collection-handle input').val(ui.item.handle);
                    $field.find('.sps-collection-image input').val(ui.item.image);
                    
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
        $row.find('.sps-product-preview').remove();
        
        // Create preview
        var $preview = $('<div class="sps-product-preview">' +
            (item.image ? '<img src="' + item.image + '" class="sps-preview-image" alt="">' : '') +
            '<div class="sps-preview-info">' +
                '<div class="sps-preview-title">' + item.label + '</div>' +
                '<div class="sps-preview-handle">' + item.handle + '</div>' +
            '</div>' +
            '<button type="button" class="sps-preview-remove">Remove</button>' +
        '</div>');
        
        $row.find('.sps-product-search .acf-input').append($preview);
        
        // Handle remove
        $preview.find('.sps-preview-remove').on('click', function() {
            $row.find('.sps-product-search input').val('');
            $row.find('.sps-product-id input').val('');
            $row.find('.sps-product-handle input').val('');
            $row.find('.sps-product-image input').val('');
            $preview.remove();
        });
    }

    function showCollectionPreview($field, item) {
        // Remove existing preview
        $field.find('.sps-collection-preview').remove();
        
        // Create preview
        var $preview = $('<div class="sps-collection-preview">' +
            (item.image ? '<img src="' + item.image + '" class="sps-preview-image" alt="">' : '') +
            '<div class="sps-preview-info">' +
                '<div class="sps-preview-title">' + item.label + '</div>' +
                '<div class="sps-preview-handle">' + item.handle + '</div>' +
            '</div>' +
            '<button type="button" class="sps-preview-remove">Remove</button>' +
        '</div>');
        
        $field.find('.sps-collection-search .acf-input').append($preview);
        
        // Handle remove
        $preview.find('.sps-preview-remove').on('click', function() {
            $field.find('.sps-collection-search input').val('');
            $field.find('.sps-collection-id input').val('');
            $field.find('.sps-collection-handle input').val('');
            $field.find('.sps-collection-image input').val('');
            $preview.remove();
        });
    }

    /**
     * Settings Page Enhancements
     */
    $(document).ready(function() {
        // Smooth scroll for navigation links
        $('.sps-menu nav a').on('click', function(e) {
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
        $('.sps-connection-status.success').delay(5000).fadeOut(400);

        // Form validation for settings page
        $('#sps-settings form').on('submit', function(e) {
            console.log('SPS: Form submit event triggered');
            
            var shopifyUrl = $('#sps_shopify_url').val();
            var accessToken = $('#sps_shopify_access_token').val();

            console.log('SPS: Shopify URL:', shopifyUrl);
            console.log('SPS: Access Token length:', accessToken ? accessToken.length : 0);

            // Basic validation
            if (shopifyUrl && !shopifyUrl.match(/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/)) {
                console.log('SPS: Validation FAILED - Invalid Shopify URL format');
                e.preventDefault();
                alert('Please enter a valid Shopify store URL (e.g., your-store.myshopify.com)');
                $('#sps_shopify_url').focus();
                return false;
            }
            
            console.log('SPS: Validation PASSED - Form will submit');
            
            // Add visual feedback to button
            var $btn = $(this).find('.button-primary');
            $btn.val('Saving...');
            
            return true; // Allow form to submit
        });

        // Clear Cache button handler
        $('#sps-clear-cache-btn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear the cache? This will fetch fresh data from Shopify on the next request.')) {
                return;
            }
            
            var $btn = $(this);
            var originalText = $btn.text();
            var nonce = $('#sps-clear-cache-nonce').val();
            
            // Disable button and show loading state
            $btn.prop('disabled', true).text('Clearing...');
            
            // Create and submit a hidden form
            var $form = $('<form method="post" action="">')
                .append($('<input type="hidden" name="sps_clear_cache" value="1">'))
                .append($('<input type="hidden" name="_wpnonce" value="' + nonce + '">'))
                .appendTo('body');
            
            // Submit the form which will reload the page with success message
            $form.submit();
        });

    });

})(jQuery);

