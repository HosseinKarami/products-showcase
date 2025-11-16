/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * Product List Component
 * Displays the list of selected products with drag-and-drop reordering
 */
export default function ProductList({ products, onRemove, onReorder }) {
	if (products.length === 0) {
		return (
			<p className="prodshow-empty-list">
				{__('No products added yet. Search and add products above.', 'products-showcase')}
			</p>
		);
	}

	const handleMoveUp = (index) => {
		if (index === 0) return;
		const newList = [...products];
		[newList[index - 1], newList[index]] = [newList[index], newList[index - 1]];
		onReorder(newList);
	};

	const handleMoveDown = (index) => {
		if (index === products.length - 1) return;
		const newList = [...products];
		[newList[index], newList[index + 1]] = [newList[index + 1], newList[index]];
		onReorder(newList);
	};

	return (
		<div className="prodshow-product-list">
			<p className="prodshow-list-label">
				<strong>
					{__('Selected Products:', 'products-showcase')} ({products.length})
				</strong>
			</p>
			<div className="prodshow-product-items">
				{products.map((product, index) => (
					<div key={product.productId} className="prodshow-product-item">
						{product.productImage && (
							<img
								src={product.productImage}
								alt={product.productTitle}
								className="prodshow-item-image"
							/>
						)}
						<div className="prodshow-item-info">
							<strong>{product.productTitle}</strong>
							{product.productHandle && (
								<span className="prodshow-item-handle">{product.productHandle}</span>
							)}
						</div>
						<div className="prodshow-item-actions">
							<Button
								icon="arrow-up-alt2"
								label={__('Move up', 'products-showcase')}
								disabled={index === 0}
								onClick={() => handleMoveUp(index)}
								isSmall
							/>
							<Button
								icon="arrow-down-alt2"
								label={__('Move down', 'products-showcase')}
								disabled={index === products.length - 1}
								onClick={() => handleMoveDown(index)}
								isSmall
							/>
							<Button
								icon="trash"
								label={__('Remove', 'products-showcase')}
								isDestructive
								onClick={() => onRemove(product.productId)}
								isSmall
							/>
						</div>
					</div>
				))}
			</div>
		</div>
	);
}

