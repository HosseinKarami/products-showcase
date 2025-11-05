/**
 * Products Showcase – Shopify Integration for WordPress - Frontend View Script
 * 
 * Handles carousel functionality using Embla Carousel
 * and product image swatch hover interactions
 * 
 * @package ProductsShowcase
 */

/**
 * Initialize product image hover interactions
 */
function initProductHovers() {
	const productCards = document.querySelectorAll('.sps-product-card');

	productCards.forEach(function(card) {
		const imageWrapper = card.querySelector('.sps-product-image-wrapper');
		const primaryImage = card.querySelector('.sps-product-image-primary');
		const swatches = card.querySelectorAll('.sps-swatch');

		if (!imageWrapper || !primaryImage) {
			return;
		}

		// Store original image data after image loads
		let originalSrc = primaryImage.src;
		let originalAlt = primaryImage.alt;

		// Update original values once image is loaded
		if (primaryImage.complete) {
			originalSrc = primaryImage.src;
			originalAlt = primaryImage.alt;
		} else {
			primaryImage.addEventListener('load', function() {
				originalSrc = primaryImage.src;
				originalAlt = primaryImage.alt;
			});
		}

		// Add hover listeners to each swatch
		swatches.forEach(function(swatch) {
			const variantImage = swatch.getAttribute('data-variant-image');
			const variantAlt = swatch.getAttribute('data-variant-alt');

			// Only add listeners if this swatch has variant image data
			if (!variantImage) {
				return;
			}

			// Prevent link click when hovering on swatch
			swatch.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
			});

			swatch.addEventListener('mouseenter', function(e) {
				e.stopPropagation();
				// Update primary image to show variant image
				primaryImage.src = variantImage;
				primaryImage.alt = variantAlt || originalAlt;
			});

			swatch.addEventListener('mouseleave', function(e) {
				e.stopPropagation();
				// Restore original image
				primaryImage.src = originalSrc;
				primaryImage.alt = originalAlt;
			});
		});

		// Ensure original image is restored when mouse leaves the product content
		const productContent = card.querySelector('.sps-product-content');
		if (productContent) {
			productContent.addEventListener('mouseleave', function() {
				primaryImage.src = originalSrc;
				primaryImage.alt = originalAlt;
			});
		}
	});
}

/**
 * Initialize carousel for a single block instance
 * 
 * @param {HTMLElement} carouselElement - The carousel container
 */
function initCarousel(carouselElement) {
	if (!carouselElement || typeof EmblaCarousel === 'undefined') {
		// Silently fail if carousel element or library not found
		return;
	}

	const viewport = carouselElement.querySelector('.sps-carousel-viewport');
	const prevBtn = carouselElement.querySelector('.sps-carousel-prev');
	const nextBtn = carouselElement.querySelector('.sps-carousel-next');

	if (!viewport) {
		// Silently fail if viewport not found
		return;
	}

	// Initialize Embla Carousel
	const embla = EmblaCarousel(viewport, {
		align: 'start',
		loop: false,
		skipSnaps: false,
		slidesToScroll: 1,
		containScroll: 'trimSnaps',
		breakpoints: {
			'(min-width: 768px)': { 
				slidesToScroll: 2 
			},
			'(min-width: 1024px)': { 
				slidesToScroll: 3 
			}
		}
	});

	// Update button states
	function updateButtons() {
		if (!prevBtn || !nextBtn) return;

		if (embla.canScrollPrev()) {
			prevBtn.removeAttribute('disabled');
			prevBtn.style.opacity = '1';
		} else {
			prevBtn.setAttribute('disabled', 'true');
			prevBtn.style.opacity = '0.3';
		}

		if (embla.canScrollNext()) {
			nextBtn.removeAttribute('disabled');
			nextBtn.style.opacity = '1';
		} else {
			nextBtn.setAttribute('disabled', 'true');
			nextBtn.style.opacity = '0.3';
		}
	}

	// Attach button click handlers
	if (prevBtn) {
		prevBtn.addEventListener('click', function() {
			embla.scrollPrev();
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function() {
			embla.scrollNext();
		});
	}

	// Update buttons on scroll and init
	embla.on('select', updateButtons);
	embla.on('init', updateButtons);
	embla.on('reInit', updateButtons);

	// Initial button state
	updateButtons();

	// Handle window resize
	let resizeTimer;
	window.addEventListener('resize', function() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function() {
			embla.reInit();
		}, 250);
	});
}

/**
 * Initialize all carousels on the page
 */
function initAllCarousels() {
	const carousels = document.querySelectorAll('.sps-carousel');
	
	if (carousels.length === 0) {
		return;
	}

	carousels.forEach(function(carousel) {
		initCarousel(carousel);
	});
}

/**
 * Initialize all functionality
 */
function initAll() {
	initAllCarousels();
	initProductHovers();
}

/**
 * Initialize when DOM is ready
 */
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initAll);
} else {
	// DOM is already loaded
	initAll();
}

/**
 * Re-initialize on Gutenberg block updates (for editor preview)
 */
if (typeof wp !== 'undefined' && wp.domReady) {
	wp.domReady(initAll);
}

