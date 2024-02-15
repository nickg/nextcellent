/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType, createBlock } from "@wordpress/blocks";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./style.scss";

/**
 * Internal dependencies
 */
import json from "./block.json";
import edit from "./edit";
import save from "./save";

const { name, ...settings } = json;

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(name, {
	...settings,

	icon: (
		<svg
			viewBox="0 0 24 24"
			xmlns="http://www.w3.org/2000/svg"
			width="24"
			height="24"
			aria-hidden="true"
			focusable="false"
		>
			<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM5 4.5h14c.3 0 .5.2.5.5v8.4l-3-2.9c-.3-.3-.8-.3-1 0L11.9 14 9 12c-.3-.2-.6-.2-.8 0l-3.6 2.6V5c-.1-.3.1-.5.4-.5zm14 15H5c-.3 0-.5-.2-.5-.5v-2.4l4.1-3 3 1.9c.3.2.7.2.9-.1L16 12l3.5 3.4V19c0 .3-.2.5-.5.5z"></path>
		</svg>
	),

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
				type: "shortcode",
				tag: "recent",
				attributes: {
					galleryLabel: {
						type: "string",
						shortcode: ({ named: { id } }) => id,
					},
					numberOfImages: {
						type: "string",
						shortcode: ({ named: { max } }) => max,
					},
					mode: {
						type: "string",
						shortcode: ({ named: { mode } }) => mode,
					},
					galleryTemplate: {
						type: "string",
						shortcode: ({ named: { template } }) => template,
					},
				},
			},
			{
				type: "block",
				blocks: ["core/shortcode"],
				isMatch: ({ text }) => {
					return text?.startsWith("[recent");
				},
				transform: ({ text }) => {
					const attributes = text
						.replace(/\[recent|]|/g, "") //remove the shortcode tags
						.trim() // remove unnecessary spaces before and after
						.split(" "); //split the attributes

					const atts = {};
					attributes.map((item) => {
						const split = item.trim().split("=");
						let attName = "";

						// since attributes have new names in the block, we need to match the old ones
						if (split[0] === "id") {
							attName = "galleryLabel";
						} else if (split[0] === "max") {
							attName = "numberOfImages";
						} else if (split[0] === "mode") {
							attName = "mode";
						} else if (split[0] === "template") {
							attName = "galleryTemplate";
						}

						atts[[attName]] = split[1];
					});

					return createBlock(name, atts);
				},
			},
		],
	},
});
