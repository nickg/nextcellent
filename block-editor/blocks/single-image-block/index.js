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
				tag: "singlepic",
				attributes: {
					imageLabel: {
						type: "string",
						shortcode: ({ named: { id } }) => id,
					},
					mode: {
						type: "string",
						shortcode: ({ named: { mode } }) => mode,
					},
					width: {
						type: "string",
						shortcode: ({ named: { w } }) => w,
					},
					height: {
						type: "string",
						shortcode: ({ named: { h } }) => h,
					},
					float: {
						type: "string",
						shortcode: ({ named: { float } }) => float,
					},
					link: {
						type: "string",
						shortcode: ({ named: { link } }) => link,
					},
					description: {
						type: "string",
						shortcode: (test) => {
							console.log(test);
							return "";
						},
					},
				},
			},
			{
				type: "block",
				blocks: ["core/shortcode"],
				isMatch: ({ text }) => {
					return text?.startsWith("[singlepic");
				},
				transform: ({ text }) => {
					const atts = {};

					const idStr = text.match(/id=\d+/);

					if (idStr && idStr[0]) {
						const id = idStr[0].split("=")[1];
						atts["imageLabel"] = id;
					}

					const widthStr = text.match(/w=(\d+)/);

					if (widthStr && widthStr[1]) {
						atts["width"] = widthStr[1];
					}

					const heightStr = text.match(/h=(\d+)/);

					if (heightStr && heightStr[1]) {
						atts["height"] = heightStr[1];
					}

					const modeStr = text.match(/(mode=(.*?))(?= )/);

					if (modeStr && modeStr[1]) {
						atts["mode"] = modeStr[1];
					}

					const floatStr = text.match(/(float=(.*?))(?= )/);

					if (floatStr && floatStr[1]) {
						atts["float"] = floatStr[1];
					}

					const linkStr = text.match(/(link=(.*?))(?=])/);

					if (linkStr && linkStr[1]) {
						atts["link"] = linkStr[1];
					}

					const descriptionStr = text.match(/(?<=\])(.*)(?=\[)/);

					if (descriptionStr && descriptionStr[1]) {
						atts["description"] = descriptionStr[1];
					}

					return createBlock(name, atts);
				},
			},
		],
	},
});
