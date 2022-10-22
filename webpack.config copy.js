const FileManagerPlugin = require('filemanager-webpack-plugin');
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const glob = require("glob");
const path = require("path");

const buildPath = path.resolve(__dirname, 'build');
const nextcellentBuildPath = buildPath + '/nextcellent-gallery-nextgen-legacy/nextcellent-gallery-nextgen-legacy';
const zipPath = path.resolve(__dirname, 'nextcellent-gallery-nextgen-legacy.zip');

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
		...defaultConfig.plugins,
		new FileManagerPlugin({
		  events: {
			  onStart: {
				delete: [zipPath, buildPath]
			  },
			onEnd: {
				mkdir: [nextcellentBuildPath],
				copy: [
					{ source: path.resolve(__dirname, 'admin'), destination: nextcellentBuildPath + '/admin' },
					{ source: path.resolve(__dirname, 'block-editor/blocks'), destination: nextcellentBuildPath + '/block-editor/blocks' },
					{ source: path.resolve(__dirname, 'css'), destination: nextcellentBuildPath + '/css' },
					{ source: path.resolve(__dirname, 'fonts'), destination: nextcellentBuildPath + '/fonts' },
					{ source: path.resolve(__dirname, 'images'), destination: nextcellentBuildPath + '/images' },
					{ source: path.resolve(__dirname, 'js'), destination: nextcellentBuildPath + '/js' },
					{ source: path.resolve(__dirname, 'lang'), destination: nextcellentBuildPath + '/lang' },
					{ source: path.resolve(__dirname, 'lib'), destination: nextcellentBuildPath + '/lib' },
					{ source: path.resolve(__dirname, 'public'), destination: nextcellentBuildPath + '/public' },
					{ source: path.resolve(__dirname, 'shutter'), destination: nextcellentBuildPath + '/shutter' },
					{ source: path.resolve(__dirname, 'view'), destination: nextcellentBuildPath + '/view' },
					{ source: path.resolve(__dirname, 'widgets'), destination: nextcellentBuildPath + '/widgets' },
					{ source: path.resolve(__dirname, 'xml'), destination: nextcellentBuildPath + '/xml' },
					{ source: path.resolve(__dirname, 'index.html'), destination: nextcellentBuildPath + '/index.html' },
					{ source: path.resolve(__dirname, 'nggallery.php'), destination: nextcellentBuildPath + '/nggallery.php' },
					{ source: path.resolve(__dirname, 'nggfunctions.php'), destination: nextcellentBuildPath + '/nggfunctions.php' },
					{ source: path.resolve(__dirname, 'nggshow.php'), destination: nextcellentBuildPath + '/nggshow.php' },
					{ source: path.resolve(__dirname, 'readme.txt'), destination: nextcellentBuildPath + '/readme.txt' },
					{ source: path.resolve(__dirname, 'screenshot-1.jpg'), destination: nextcellentBuildPath + '/screenshot-1.jpg' },
					{ source: path.resolve(__dirname, 'screenshot-2.jpg'), destination: nextcellentBuildPath + '/screenshot-2.jpg' },
					{ source: path.resolve(__dirname, 'screenshot-3.jpg'), destination: nextcellentBuildPath + '/screenshot-3.jpg' },
					{ source: path.resolve(__dirname, 'screenshot-4.jpg'), destination: nextcellentBuildPath + '/screenshot-4.jpg' },
					{ source: path.resolve(__dirname, 'screenshot-5.jpg'), destination: nextcellentBuildPath + '/screenshot-5.jpg' },
					{ source: path.resolve(__dirname, 'screenshot-6.jpg'), destination: nextcellentBuildPath + '/screenshot-6.jpg' },
				  ],
				  archive: [
					{ source: path.resolve(__dirname, 'build/nextcellent-gallery-nextgen-legacy'), destination: zipPath },
				  ],
				
				delete: [buildPath],
			  
			},
		  },
		}),
	  ],
};
