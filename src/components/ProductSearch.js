/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { SearchControl, Spinner, Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * Product Search Component
 */
export default function ProductSearch({ onSelect }) {
	const [searchTerm, setSearchTerm] = useState('');
	const [results, setResults] = useState([]);
	const [isSearching, setIsSearching] = useState(false);
	const [searchPerformed, setSearchPerformed] = useState(false);

	const handleSearch = async (value) => {
		setSearchTerm(value);

		if (value.length < 2) {
			setResults([]);
			setSearchPerformed(false);
			return;
		}

		setIsSearching(true);
		setSearchPerformed(false);

		try {
			const response = await apiFetch({
				path: `/sps-shopify/v1/search-products?query=${encodeURIComponent(value)}`,
			});
			setResults(response.products || []);
			setSearchPerformed(true);
		} catch (error) {
			// Silently handle search errors
			setResults([]);
			setSearchPerformed(true);
		} finally {
			setIsSearching(false);
		}
	};

	const handleSelectProduct = (product) => {
		onSelect(product);
		setSearchTerm('');
		setResults([]);
		setSearchPerformed(false);
	};

	return (
		<div className="sps-product-search">
			<SearchControl
				label={__('Search Products', 'products-showcase')}
				value={searchTerm}
				onChange={handleSearch}
				placeholder={__('Type to search products...', 'products-showcase')}
				help={__('Search by product name or handle', 'products-showcase')}
			/>

			{isSearching && (
				<div className="sps-search-loading">
					<Spinner />
					<span>{__('Searching...', 'products-showcase')}</span>
				</div>
			)}

			{!isSearching && searchPerformed && results.length === 0 && (
				<p className="sps-no-results">
					{__('No products found. Try a different search term.', 'products-showcase')}
				</p>
			)}

			{results.length > 0 && (
				<div className="sps-search-results">
					{results.map((product) => (
						<div key={product.id} className="sps-search-result-item">
							{product.image && (
								<img src={product.image} alt={product.title} className="sps-result-image" />
							)}
							<div className="sps-result-info">
								<strong>{product.title}</strong>
								{product.handle && <span className="sps-result-handle">{product.handle}</span>}
							</div>
							<Button
								isSecondary
								isSmall
								onClick={() => handleSelectProduct(product)}
							>
								{__('Add', 'products-showcase')}
							</Button>
						</div>
					))}
				</div>
			)}
		</div>
	);
}

