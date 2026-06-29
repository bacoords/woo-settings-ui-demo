const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

const settingsUIRequest = '@woocommerce/settings-ui';

module.exports = {
	...defaultConfig,
	entry: {
		'settings-ui-demo': path.resolve(
			process.cwd(),
			'src/settings-ui-demo/index.js'
		),
	},
	output: {
		...defaultConfig.output,
		filename: '[name].js',
		path: path.resolve( process.cwd(), 'assets' ),
	},
	plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin( {
			requestToExternal( request ) {
				if ( settingsUIRequest === request ) {
					return [ 'wc', 'settingsUi' ];
				}
			},
			requestToHandle( request ) {
				if ( settingsUIRequest === request ) {
					return 'wc-settings-ui';
				}
			},
		} ),
	],
};
