/**
 * Custom Webpack Configuration
 * Extends @wordpress/scripts webpack config to handle viewScript
 */

const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		index: path.resolve(process.cwd(), 'src', 'index.js'),
		view: path.resolve(process.cwd(), 'src', 'view.js'),
	},
};

