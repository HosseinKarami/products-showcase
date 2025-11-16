/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { SearchControl, Spinner, Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * Collection Search Component
 */
export default function CollectionSearch({ onSelect, selectedId, selectedTitle }) {
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
				path: `/prodshow-shopify/v1/search-collections?query=${encodeURIComponent(value)}`,
			});
			setResults(response.collections || []);
			setSearchPerformed(true);
		} catch (error) {
			// Silently handle search errors
			setResults([]);
			setSearchPerformed(true);
		} finally {
			setIsSearching(false);
		}
	};

	const handleSelectCollection = (collection) => {
		onSelect(collection);
		setSearchTerm('');
		setResults([]);
		setSearchPerformed(false);
	};

	return (
		<div className="prodshow-collection-search">
			{selectedId ? (
				<div className="prodshow-selected-collection">
					<p>
						<strong>{__('Selected Collection:', 'products-showcase')}</strong>
					</p>
					<p>{selectedTitle}</p>
					<Button
						isDestructive
						isSmall
						onClick={() =>
							onSelect({
								id: '',
								handle: '',
								title: '',
								image: '',
							})
						}
					>
						{__('Clear Selection', 'products-showcase')}
					</Button>
				</div>
			) : (
				<>
					<SearchControl
						label={__('Search Collections', 'products-showcase')}
						value={searchTerm}
						onChange={handleSearch}
						placeholder={__('Type to search collections...', 'products-showcase')}
						help={__('Search by collection name or handle', 'products-showcase')}
					/>

					{isSearching && (
						<div className="prodshow-search-loading">
							<Spinner />
							<span>{__('Searching...', 'products-showcase')}</span>
						</div>
					)}

					{!isSearching && searchPerformed && results.length === 0 && (
						<p className="prodshow-no-results">
							{__(
								'No collections found. Try a different search term.',
								'products-showcase'
							)}
						</p>
					)}

					{results.length > 0 && (
						<div className="prodshow-search-results">
							{results.map((collection) => (
								<div key={collection.id} className="prodshow-search-result-item">
									{collection.image && (
										<img
											src={collection.image}
											alt={collection.title}
											className="prodshow-result-image"
										/>
									)}
									<div className="prodshow-result-info">
										<strong>{collection.title}</strong>
										{collection.handle && (
											<span className="prodshow-result-handle">{collection.handle}</span>
										)}
										{collection.productsCount !== undefined && (
											<span className="prodshow-result-count">
												{collection.productsCount}{' '}
												{__('products', 'products-showcase')}
											</span>
										)}
									</div>
									<Button
										isPrimary
										isSmall
										onClick={() => handleSelectCollection(collection)}
									>
										{__('Select', 'products-showcase')}
									</Button>
								</div>
							))}
						</div>
					)}
				</>
			)}
		</div>
	);
}

