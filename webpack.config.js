const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

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
		new WooCommerceDependencyExtractionWebpackPlugin(),
	],
};
