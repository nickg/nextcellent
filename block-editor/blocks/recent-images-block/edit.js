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
import Template from "../../gerneral-components/template-radio-group/Template";
import ModeSelect from "../../gerneral-components/mode-select/ModeSelect";

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
	const [number, setNumber] = useState(
		attributes?.numberOfImages ? attributes.numberOfImages : "0"
	);
	const [galleryTemplate, setGalleryTemplate] = useState(
		attributes?.galleryTemplate ? attributes.galleryTemplate : "gallery"
	);
	const [mode, setMode] = useState(attributes?.mode ? attributes.mode : "");

	const handleAutocompleteSelect = (value) => {
		if (value?.label !== gallery) {
			setGallery(value?.label);
		}
	};

	const handleNumberOfImagesChange = (value) => {
		if (value !== number) {
			setNumber(value);
		}
	};

	const handleModeChange = (value) => {
		if (value !== mode) {
			setMode(value);
		}
	};

	const handleGalleryTemplateSelection = (value) => {
		setGalleryTemplate(value);
	};

	const attributeSetter = (e) => {
		e.stopPropagation();
		e.preventDefault();

		let newAttributes = {};

		if (gallery) {
			newAttributes["galleryLabel"] = gallery;
		}

		if (number) {
			newAttributes["numberOfImages"] = number;
		}

		if (mode) {
			newAttributes["mode"] = mode;
		}

		if (galleryTemplate) {
			newAttributes["galleryTemplate"] = galleryTemplate;
		}

		setAttributes(newAttributes);
	};

	return (
		<div {...useBlockProps()}>
			<InspectorControls
				key="setting"
				id="nextcellent-recent-images-block-controlls"
			>
				<PanelBody title={__("Basics", "nggallery")}>
					<fieldset>
						<NumberOfImages
							value={number}
							onChange={handleNumberOfImagesChange}
						></NumberOfImages>
					</fieldset>
				</PanelBody>
				<PanelBody title={__("Type options", "nggallery")}>
					<fieldset>
						<ModeSelect
							type="recent"
							value={mode}
							onChange={handleModeChange}
						></ModeSelect>
						<p>
							{__(
								"In what order the images are shown. Upload order uses the ID's, date taken uses the EXIF data and user defined is the sort mode from the settings.",
								"nggallery"
							)}
						</p>
						<Autocomplete
							label={__("Select a gallery:", "nggallery")}
							preSelected={gallery}
							onSelect={handleAutocompleteSelect}
							fetch={fetchGallerys}
						/>
						<p>
							{__(
								"If a gallery is selected, only images from that gallery will be shown.",
								"nggallery"
							)}
						</p>
						<Template
							type="recent"
							value={galleryTemplate}
							onChecked={handleGalleryTemplateSelection}
						></Template>
					</fieldset>
				</PanelBody>

				<button
					id="nextcellent-block-set-button"
					className="components-button editor-post-publish-button editor-post-publish-button__button is-primary"
					onClick={attributeSetter}
					disabled={number <= 0}
				>
					Set
				</button>
			</InspectorControls>

			{attributes.numberOfImages > 0 && (
				<ServerSideRender
					className="nextcellent-recent-images-block-render"
					block="nggallery/recent-images-block"
					attributes={attributes}
				/>
			)}

			{!attributes.numberOfImages && (
				<p>{__("You need to select a number of images.", "nggallery")}</p>
			)}
		</div>
	);
}
