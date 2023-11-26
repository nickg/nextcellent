import ServerSideRender from "@wordpress/server-side-render";

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

	const handleAutocompleteSelect = (value) => {
		if (value?.label !== gallery) {
			setGallery(value?.label);
		}
	};

	const attributeSetter = (e) => {
		e.stopPropagation();
		e.preventDefault();

		setAttributes({ galleryLabel: gallery });
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
					className="nextcellent-image-browser-block-render"
					block="nggallery/image-browser-block"
					attributes={attributes}
				/>
			)}
			{!attributes.galleryLabel && (
				<p>{__("Please select a gallery", "nggallery")}</p>
			)}
		</div>
	);
}
