/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { 
	useBlockProps, 
	InspectorControls, 
	PanelColorSettings,
	InspectorAdvancedControls
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	SelectControl,
	RangeControl,
	ToggleControl,
	Button,
	Spinner,
	Notice,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import ProductSearch from './components/ProductSearch';
import CollectionSearch from './components/CollectionSearch';
import ProductList from './components/ProductList';

/**
 * Edit component for Shopify Products block
 */
export default function Edit({ attributes, setAttributes, clientId }) {
	const {
		title,
		description,
		contentType,
		productList,
		collectionId,
		collectionHandle,
		collectionTitle,
		productLimit,
		disableGlobalPadding,
		ctaButton,
		colors,
	} = attributes;

	const blockProps = useBlockProps({
		className: 'prodshow-block-editor',
	});

	// State for API connection status
	const [isConnected, setIsConnected] = useState(null);
	const [isCheckingConnection, setIsCheckingConnection] = useState(true);

	// Check API connection on mount
	useEffect(() => {
		checkConnection();
	}, []);

	const checkConnection = async () => {
		setIsCheckingConnection(true);
		try {
			const response = await apiFetch({
				path: '/prodshow-shopify/v1/connection-status',
			});
			setIsConnected(response.connected);
		} catch (error) {
			setIsConnected(false);
		} finally {
			setIsCheckingConnection(false);
		}
	};

	// Handler for adding product
	const handleAddProduct = (product) => {
		const newProduct = {
			productId: product.id,
			productHandle: product.handle,
			productTitle: product.title,
			productImage: product.image,
		};

		// Check if product already exists
		const exists = productList.some((p) => p.productId === product.id);
		if (!exists) {
			setAttributes({
				productList: [...productList, newProduct],
			});
		}
	};

	// Handler for removing product
	const handleRemoveProduct = (productId) => {
		setAttributes({
			productList: productList.filter((p) => p.productId !== productId),
		});
	};

	// Handler for reordering products
	const handleReorderProducts = (newList) => {
		setAttributes({ productList: newList });
	};

	// Handler for selecting collection
	const handleSelectCollection = (collection) => {
		setAttributes({
			collectionId: collection.id,
			collectionHandle: collection.handle,
			collectionTitle: collection.title,
			collectionImage: collection.image,
		});
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Block Settings', 'products-showcase')} initialOpen={true}>
					<TextControl
						label={__('Title', 'products-showcase')}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						help={__('Optional heading for the block', 'products-showcase')}
					/>
					<TextareaControl
						label={__('Description', 'products-showcase')}
						value={description}
						onChange={(value) => setAttributes({ description: value })}
						rows={3}
						help={__('Optional description text', 'products-showcase')}
					/>
					<hr style={{ margin: '20px 0', border: 'none', borderTop: '1px solid #ddd' }} />
					<h3 style={{ fontSize: '13px', fontWeight: '500', marginBottom: '12px' }}>
						{__('CTA Button', 'products-showcase')}
					</h3>
					<TextControl
						label={__('Button Text', 'products-showcase')}
						value={ctaButton?.title || ''}
						onChange={(value) => setAttributes({ 
							ctaButton: { ...ctaButton, title: value } 
						})}
						placeholder={__('View All Products', 'products-showcase')}
					/>
					<TextControl
						label={__('Button URL', 'products-showcase')}
						value={ctaButton?.url || ''}
						onChange={(value) => setAttributes({ 
							ctaButton: { ...ctaButton, url: value } 
						})}
						placeholder="https://"
						type="url"
					/>
					<ToggleControl
						label={__('Open in new tab', 'products-showcase')}
						checked={ctaButton?.opensInNewTab || false}
						onChange={(value) => setAttributes({ 
							ctaButton: { ...ctaButton, opensInNewTab: value } 
						})}
					/>
					<hr style={{ margin: '20px 0', border: 'none', borderTop: '1px solid #ddd' }} />
					<ToggleControl
						label={__('Disable Global Padding', 'products-showcase')}
						checked={disableGlobalPadding}
						onChange={(value) => setAttributes({ disableGlobalPadding: value })}
						help={__('Remove the has-global-padding class to allow full-width content', 'products-showcase')}
					/>
				</PanelBody>

				<PanelBody title={__('Content Type', 'products-showcase')} initialOpen={true}>
					<SelectControl
						label={__('Display', 'products-showcase')}
						value={contentType}
						options={[
							{
								label: __('Individual Products', 'products-showcase'),
								value: 'products',
							},
							{
								label: __('Collection', 'products-showcase'),
								value: 'collection',
							},
						]}
						onChange={(value) => setAttributes({ contentType: value })}
						help={__(
							'Choose whether to display individual products or a collection',
							'products-showcase'
						)}
					/>
				</PanelBody>

				{contentType === 'products' && (
					<PanelBody
						title={__('Select Products', 'products-showcase')}
						initialOpen={true}
					>
						{isConnected === false && (
							<Notice status="error" isDismissible={false}>
								{__(
									'Shopify API not connected. Please configure your settings.',
									'products-showcase'
								)}
							</Notice>
						)}
						{isConnected && (
							<>
								<ProductSearch onSelect={handleAddProduct} />
								<ProductList
									products={productList}
									onRemove={handleRemoveProduct}
									onReorder={handleReorderProducts}
								/>
							</>
						)}
					</PanelBody>
				)}

				{contentType === 'collection' && (
					<PanelBody
						title={__('Select Collection', 'products-showcase')}
						initialOpen={true}
					>
						{isConnected === false && (
							<Notice status="error" isDismissible={false}>
								{__(
									'Shopify API not connected. Please configure your settings.',
									'products-showcase'
								)}
							</Notice>
						)}
						{isConnected && (
							<>
								<CollectionSearch
									onSelect={handleSelectCollection}
									selectedId={collectionId}
									selectedTitle={collectionTitle}
								/>
								<RangeControl
									label={__('Product Limit', 'products-showcase')}
									value={productLimit}
									onChange={(value) => setAttributes({ productLimit: value })}
									min={1}
									max={50}
									help={__(
										'Number of products to display from the collection',
										'products-showcase'
									)}
								/>
							</>
						)}
					</PanelBody>
				)}
			</InspectorControls>

			<InspectorControls group="styles">
				<PanelColorSettings
					title={__('Color', 'products-showcase')}
					initialOpen={true}
					colorSettings={[
						{
							value: colors?.backgroundColor,
							onChange: (value) => setAttributes({ 
								colors: { ...colors, backgroundColor: value } 
							}),
							label: __('Background', 'products-showcase'),
						},
						{
							value: colors?.textColor,
							onChange: (value) => setAttributes({ 
								colors: { ...colors, textColor: value } 
							}),
							label: __('Text', 'products-showcase'),
						},
					]}
				/>

				<PanelColorSettings
					title={__('Button', 'products-showcase')}
					initialOpen={false}
					colorSettings={[
						{
							value: colors?.buttonBackground,
							onChange: (value) => setAttributes({ 
								colors: { ...colors, buttonBackground: value } 
							}),
							label: __('Background', 'products-showcase'),
						},
						{
							value: colors?.buttonText,
							onChange: (value) => setAttributes({ 
								colors: { ...colors, buttonText: value } 
							}),
							label: __('Text', 'products-showcase'),
						},
						{
							value: colors?.buttonBackgroundHover,
							onChange: (value) => setAttributes({ 
								colors: { ...colors, buttonBackgroundHover: value } 
							}),
							label: __('Background (Hover)', 'products-showcase'),
						},
						{
							value: colors?.buttonTextHover,
							onChange: (value) => setAttributes({ 
								colors: { ...colors, buttonTextHover: value } 
							}),
							label: __('Text (Hover)', 'products-showcase'),
						},
					]}
				/>
			</InspectorControls>

			<div {...blockProps}>
				{isCheckingConnection ? (
					<div className="prodshow-loading">
						<Spinner />
						<p>{__('Checking Shopify connection...', 'products-showcase')}</p>
					</div>
				) : isConnected === false ? (
					<Notice status="warning" isDismissible={false}>
						<p>
							<strong>{__('Shopify API Not Connected', 'products-showcase')}</strong>
						</p>
						<p>
							{__(
								'Please configure your Shopify credentials in Settings → Products Showcase.',
								'products-showcase'
							)}
						</p>
						<Button isSecondary onClick={checkConnection}>
							{__('Retry Connection', 'products-showcase')}
						</Button>
					</Notice>
				) : (
					<ServerSideRender
						block="products-showcase/products"
						attributes={attributes}
						LoadingResponsePlaceholder={() => (
							<div className="prodshow-loading">
								<Spinner />
								<p>{__('Loading products...', 'products-showcase')}</p>
							</div>
						)}
						ErrorResponsePlaceholder={({ response }) => (
							<Notice status="error" isDismissible={false}>
								<p>
									<strong>{__('Error loading products', 'products-showcase')}</strong>
								</p>
								<p>{response?.message || __('Unknown error', 'products-showcase')}</p>
							</Notice>
						)}
						EmptyResponsePlaceholder={() => (
							<Notice status="info" isDismissible={false}>
								<p>
									{contentType === 'products'
										? __(
												'No products selected. Use the sidebar to search and add products.',
												'products-showcase'
										  )
										: __(
												'No collection selected. Use the sidebar to search for a collection.',
												'products-showcase'
										  )}
								</p>
							</Notice>
						)}
					/>
				)}
			</div>
		</>
	);
}

