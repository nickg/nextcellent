/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType, createBlock } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import json from './block.json';
import edit from './edit';
import save from './save';

const { name, ...settings } = json;

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( name, {
	...settings,
	/**
	 * @see ./edit.js
	 */
	edit,

	/**
	 * @see ./save.js
	 */
	save,

	transforms: {
		from: [
			{
				type: 'shortcode',
				tag: 'nggallery',
				isMatch: (test) => {
					console.log(test)
					return test.startsWith('[nggallery');
				},
				attributes: {
					galleryLabel: {
						type: 'string',
						shortcode: ({ named: { id } }) => id,
					},
					numberOfImages: {
						type: 'string',
						shortcode: ({ named: { images } }) => images,
					}
				},
			},
			{
				type: 'block',
				blocks: ['core/shortcode'],
				isMatch: ({ text }) => {
					return text.startsWith('[nggallery');
				},
				transform: ({ text }) => {
					const attributes = text
						.replace(/\[nggallery|]|/g, '') //remove the shortcode tags
						.trim() // remove unnecessary spaces before and after
						.split(' '); //split the attributes

					const atts = {};
					attributes.map((item) => {
						const split = item.trim().split('=');
						let attName = '';

						// since attributes have new names in the block, we need to match the old ones
						if (split[0] === 'id') {
							attName = 'galleryLabel'
						} else {
							attName = 'numberOfImages'
						}
						atts[[attName]] = split[1];
					});

					return createBlock(name, atts);
				},
			},
		],
	},
} );
