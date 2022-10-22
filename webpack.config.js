const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const glob = require("glob");
const path = require("path");

// Get entrypoints for block folders
const entry = glob.sync('block-editor/**/**/index.js').reduce(function(obj, el){
	const name = path.dirname(el).split(/[\/ | \\]/).pop()

	obj['blocks/' + name + '/' + name] = './' + path.dirname(el);
	return obj
 },{});

module.exports = {
	...defaultConfig,
	entry,
	output: {
		path: path.resolve(__dirname, 'public'),
		filename: '[name].js'
	},
	plugins: [
		...defaultConfig.plugins
	  ],
};
