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
			<path d="M19 6H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h13c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-4.1 1.5v10H10v-10h4.9zM5.5 17V8c0-.3.2-.5.5-.5h2.5v10H6c-.3 0-.5-.2-.5-.5zm14 0c0 .3-.2.5-.5.5h-2.6v-10H19c.3 0 .5.2.5.5v9z"></path>
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
				tag: "slideshow",
				attributes: {
					galleryLabel: {
						type: "string",
						shortcode: ({ named: { id } }) => id,
					},
					width: {
						type: "string",
						shortcode: ({ named: { w } }) => w,
					},
					height: {
						type: "string",
						shortcode: ({ named: { h } }) => h,
					},
				},
			},
			{
				type: "block",
				blocks: ["core/shortcode"],
				isMatch: ({ text }) => {
					return text?.startsWith("[slideshow");
				},
				transform: ({ text }) => {
					const attributes = text
						.replace(/\[slideshow|]|/g, "") //remove the shortcode tags
						.trim() // remove unnecessary spaces before and after
						.split(" "); //split the attributes

					const atts = {};
					attributes.map((item) => {
						const split = item.trim().split("=");
						let attName = "";

						// since attributes have new names in the block, we need to match the old ones
						if (split[0] === "id") {
							attName = "galleryLabel";
						} else if (split[0] == "w") {
							attName = "width";
						} else if (split[0] == "h") {
							attName = "height";
						}

						atts[[attName]] = split[1];
					});

					return createBlock(name, atts);
				},
			},
		],
	},
});
