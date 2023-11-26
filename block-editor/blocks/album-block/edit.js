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
import { fetchAlbums } from "../../api";
import Template from "../../gerneral-components/template-radio-group/Template";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const [album, setAlbum] = useState(
		attributes?.albumLabel ? attributes.albumLabel : ""
	);
	const [galleryTemplate, setGalleryTemplate] = useState(
		attributes?.galleryTemplate ? attributes.galleryTemplate : "gallery"
	);
	const [albumTemplate, setAlbumTemplate] = useState(
		attributes?.albumTemplate ? attributes.albumTemplate : "compact"
	);

	const handleAutocompleteSelect = (value) => {
		if (value?.label !== album) {
			setAlbum(value?.label);
		}
	};

	const handleGalleryTemplateSelection = (value) => {
		setGalleryTemplate(value);
	};

	const handleAlbumTemplateSelection = (value) => {
		setAlbumTemplate(value);
	};

	const attributeSetter = (e) => {
		e.stopPropagation();
		e.preventDefault();

		let newAttributes = {};

		if (album) {
			newAttributes["albumLabel"] = album;
		}

		if (albumTemplate) {
			newAttributes["albumTemplate"] = albumTemplate;
		}

		if (galleryTemplate) {
			newAttributes["galleryTemplate"] = galleryTemplate;
		}

		setAttributes(newAttributes);
	};

	return (
		<div {...useBlockProps()}>
			<InspectorControls key="setting" id="nextcellent-album-block-controlls">
				<PanelBody title={__("Basics", "nggallery")}>
					<fieldset>
						<Autocomplete
							label={__("Select an album:", "nggallery")}
							preSelected={album}
							onSelect={handleAutocompleteSelect}
							fetch={fetchAlbums}
						/>
					</fieldset>
				</PanelBody>
				<PanelBody title={__("Type options", "nggallery")}>
					<fieldset>
						<Template
							id="nextcellent-block-template-album"
							type="album"
							value={albumTemplate}
							onChecked={handleAlbumTemplateSelection}
						></Template>
						<Template
							id="nextcellent-block-template-gallery"
							type="albumGallery"
							value={galleryTemplate}
							onChecked={handleGalleryTemplateSelection}
						></Template>
					</fieldset>
				</PanelBody>

				<button
					id="nextcellent-block-set-button"
					className="components-button editor-post-publish-button editor-post-publish-button__button is-primary"
					onClick={attributeSetter}
					disabled={album == ""}
				>
					Set
				</button>
			</InspectorControls>

			{attributes.albumLabel && (
				<ServerSideRender
					className="nextcellent-album-block-render"
					block="nggallery/album-block"
					attributes={attributes}
				/>
			)}
			{!attributes.albumLabel && (
				<p>{__("Please select an album", "nggallery")}</p>
			)}
		</div>
	);
}
