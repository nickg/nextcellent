import ServerSideRender from "@wordpress/server-side-render";

//import Autocomplete  from '../../gerneral-components/autocomplete/Autocomplete'
import NumberOfImages from "../../gerneral-components/number-of-images-input/NumberOfImages";

import { PanelBody } from "@wordpress/components";

import { useState } from "@wordpress/element";

import { useBlockProps, InspectorControls } from "@wordpress/block-editor";

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from "@wordpress/i18n";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./editor.scss";
import Autocomplete from "../../gerneral-components/autocomplete/Autocomplete";
import { fetchGallerys } from "../../api";
import Width from "../../gerneral-components/width-input/Width";
import Height from "../../gerneral-components/height-input/Height";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const [gallery, setGallery] = useState(
		attributes?.galleryLabel ? attributes.galleryLabel : ""
	);
	const [width, setWidth] = useState(attributes?.width ? attributes.width : 0);
	const [height, setHeight] = useState(
		attributes?.height ? attributes.height : 0
	);

	const handleAutocompleteSelect = (value) => {
		if (value?.label !== gallery) {
			setGallery(value?.label);
		}
	};

	const handleWidthChange = (value) => {
		if (value !== width) {
			if (value === "") {
				value = 0;
			}
			setWidth(value);
		}
	};

	const handleHeightChange = (value) => {
		if (value !== width) {
			if (value === "") {
				value = 0;
			}
			setHeight(value);
		}
	};

	const attributeSetter = (e) => {
		e.stopPropagation();
		e.preventDefault();

		let newAttributes = {};

		if (gallery) {
			newAttributes["galleryLabel"] = gallery;
		}

		if (width !== undefined && width !== null) {
			newAttributes["width"] = width;
		}

		if (height !== undefined && height !== null) {
			newAttributes["height"] = height;
		}

		setAttributes(newAttributes);
	};

	return (
		<div {...useBlockProps()}>
			<InspectorControls key="setting" id="nextcellent-gallery-block-controlls">
				<PanelBody title={__("Basics", "nggallery")}>
					<fieldset>
						<Autocomplete
							label={__("Select a gallery:", "nggallery")}
							preSelected={gallery}
							onSelect={handleAutocompleteSelect}
							fetch={fetchGallerys}
						/>
					</fieldset>
				</PanelBody>
				<PanelBody title={__("Type options", "nggallery")}>
					<fieldset>
						<Width value={width} onChange={handleWidthChange}></Width>
						<Height value={height} onChange={handleHeightChange}></Height>
					</fieldset>
				</PanelBody>

				<button
					id="nextcellent-block-set-button"
					className="components-button editor-post-publish-button editor-post-publish-button__button is-primary"
					onClick={attributeSetter}
					disabled={gallery == ""}
				>
					Set
				</button>
			</InspectorControls>

			{attributes.galleryLabel && (
				<ServerSideRender
					className="nextcellent-slideshow-block-render"
					block="nggallery/slideshow-block"
					attributes={attributes}
				/>
			)}
			{!attributes.galleryLabel && (
				<p>{__("Please select a gallery", "nggallery")}</p>
			)}
		</div>
	);
}
