const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils' );
const { resolve } = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		...getWebpackEntryPoints(),
		'hooks/index': resolve( process.cwd(), 'src/hooks', 'index.js' ),
	},
};
