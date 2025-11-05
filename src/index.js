/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';
import './editor.scss';
import './style.scss';

/**
 * Register the Shopify Products block
 */
registerBlockType(metadata.name, {
	...metadata,
	edit: Edit,
	// Server-side rendered block, no save function needed
	save: () => null,
});

