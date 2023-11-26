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
import { fetchImages } from "../../api";
import ModeSelect from "../../gerneral-components/mode-select/ModeSelect";
import Width from "../../gerneral-components/width-input/Width";
import Height from "../../gerneral-components/height-input/Height";
import FloatSelect from "../../gerneral-components/float-select/FloatSelect";
import LinkInput from "../../gerneral-components/link-input/LinkInput";
import DescriptionInput from "../../gerneral-components/description-input/DescriptionInput";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const [image, setImage] = useState(
		attributes?.imageLabel ? attributes.imageLabel : ""
	);
	const [mode, setMode] = useState(attributes?.mode ? attributes.mode : "");
	const [float, setFloat] = useState(attributes?.float ? attributes.float : "");
	const [width, setWidth] = useState(attributes?.width ? attributes.width : 0);
	const [height, setHeight] = useState(
		attributes?.height ? attributes.height : 0
	);
	const [link, setLink] = useState(attributes?.link ? attributes.link : "");
	const [description, setDescription] = useState(
		attributes?.description ? attributes.description : ""
	);

	const handleAutocompleteSelect = (value) => {
		if (value?.label !== image) {
			setImage(value?.label);
		}
	};

	const handleModeChange = (value) => {
		if (value !== mode) {
			setMode(value);
		}
	};

	const handleFloatChange = (value) => {
		if (value !== float) {
			setFloat(value);
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

	const handleLinkChange = (value) => {
		if (value !== link) {
			setLink(value);
		}
	};

	const handleDescriptionChange = (value) => {
		if (value !== description) {
			setDescription(value);
		}
	};

	const attributeSetter = (e) => {
		e.stopPropagation();
		e.preventDefault();

		let newAttributes = {};

		if (image) {
			newAttributes["imageLabel"] = image;
		}

		if (mode) {
			newAttributes["mode"] = mode;
		}

		if (float) {
			newAttributes["float"] = float;
		}

		if (width !== undefined && width !== null) {
			newAttributes["width"] = width;
		}

		if (height !== undefined && height !== null) {
			newAttributes["height"] = height;
		}

		if (link) {
			newAttributes["link"] = link;
		}

		if (description) {
			newAttributes["description"] = description;
		}

		setAttributes(newAttributes);
	};

	return (
		<div {...useBlockProps()}>
			<InspectorControls
				key="setting"
				id="nextcellent-single-image-block-controlls"
			>
				<PanelBody title={__("Basics", "nggallery")}>
					<fieldset>
						<Autocomplete
							label={__("Select an image:", "nggallery")}
							preSelected={image}
							onSelect={handleAutocompleteSelect}
							fetch={fetchImages}
						/>
					</fieldset>
				</PanelBody>
				<PanelBody title={__("Type options", "nggallery")}>
					<fieldset>
						<Width value={width} onChange={handleWidthChange}></Width>
						<Height value={height} onChange={handleHeightChange}></Height>
						<ModeSelect
							value={mode}
							onChange={handleModeChange}
							type="img"
						></ModeSelect>
						<FloatSelect
							value={float}
							onChange={handleFloatChange}
						></FloatSelect>
						<LinkInput value={link} onChange={handleLinkChange}></LinkInput>
						<DescriptionInput
							value={description}
							onChange={handleDescriptionChange}
						></DescriptionInput>
					</fieldset>
				</PanelBody>

				<button
					id="nextcellent-block-set-button"
					className="components-button editor-post-publish-button editor-post-publish-button__button is-primary"
					onClick={attributeSetter}
					disabled={image == ""}
				>
					Set
				</button>
			</InspectorControls>

			{attributes.imageLabel && (
				<ServerSideRender
					className="nextcellent-single-image-block-image"
					block="nggallery/single-image-block"
					attributes={attributes}
				/>
			)}
			{!attributes.imageLabel && (
				<p>{__("Please select an image", "nggallery")}</p>
			)}
		</div>
	);
}
