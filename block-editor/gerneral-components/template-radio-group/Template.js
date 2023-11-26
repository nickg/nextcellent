import { __ } from "@wordpress/i18n";
import { Fragment } from "@wordpress/element";

// Load external dependency.
import "./template.scss";

/**
 *
 * @param type album | gallery
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function Template({ id, type, value, onChecked, ...props }) {
	// Unique ID for the input group.
	const inputId = id ? id : `nextcellent-block-template-radio`;

	// Function to handle the onChange event.
	const onCheckedInput = (event) => {
		onChecked(event.target.value);
	};

	// Return the fieldset.
	return (
		<fieldset aria-role="radiogroup" className="nextcellent-template-radio">
			{(type == "gallery" || type == "albumGallery") && (
				<Fragment>
					<input
						id="nextcellent-template-type-gallery"
						type="radio"
						name={inputId}
						checked={value == "gallery"}
						onChange={onCheckedInput}
						value="gallery"
					></input>
					<label htmlFor="nextcellent-template-type-gallery">
						<img
							className="nextcellent-template-type-img"
							src={nggData.pluginUrl + "admin/images/gallery.svg"}
							alt="gallery"
						></img>
						<p>{__("Gallery", "nggallery")}</p>
					</label>

					<input
						id="nextcellent-template-type-caption"
						type="radio"
						name={inputId}
						checked={value == "caption"}
						onChange={onCheckedInput}
						value="caption"
					></input>
					<label htmlFor="nextcellent-template-type-caption">
						<img
							className="nextcellent-template-type-img"
							src={nggData.pluginUrl + "admin/images/caption.svg"}
							alt="caption"
						></img>
						<p>{__("Caption", "nggallery")}</p>
					</label>

					<input
						id="nextcellent-template-type-carousel"
						type="radio"
						name={inputId}
						checked={value == "carousel"}
						onChange={onCheckedInput}
						value="carousel"
					></input>
					<label htmlFor="nextcellent-template-type-carousel">
						<img
							className="nextcellent-template-type-img"
							src={nggData.pluginUrl + "admin/images/carousel.svg"}
							alt="carousel"
						></img>
						<p>{__("Carousel", "nggallery")}</p>
					</label>
				</Fragment>
			)}
			{type == "gallery" && (
				<Fragment>
					<input
						id="nextcellent-template-type-other"
						type="radio"
						name={inputId}
						checked={value == "other"}
						onChange={onCheckedInput}
						value="other"
					></input>
					<label htmlFor="nextcellent-template-type-other">
						<img
							className="nextcellent-template-type-img"
							src={nggData.pluginUrl + "admin/images/other.svg"}
							alt="other"
						></img>
						<p>{__("Other", "nggallery")}</p>
					</label>
				</Fragment>
			)}

			{type == "album" && (
				<Fragment>
					<input
						id="nextcellent-template-type-compact"
						type="radio"
						name={inputId}
						checked={value == "compact"}
						onChange={onCheckedInput}
						value="compact"
					></input>
					<label htmlFor="nextcellent-template-type-compact">
						<img
							className="nextcellent-template-type-img"
							src={nggData.pluginUrl + "admin/images/compact.svg"}
							alt="compact"
						></img>
						<p>{__("Compact", "nggallery")}</p>
					</label>

					<input
						id="nextcellent-template-type-extend"
						type="radio"
						name={inputId}
						checked={value == "extend"}
						onChange={onCheckedInput}
						value="extend"
					></input>
					<label htmlFor="nextcellent-template-type-extend">
						<img
							className="nextcellent-template-type-img"
							src={nggData.pluginUrl + "admin/images/compact.svg"}
							alt="compact"
						></img>
						<p>{__("Extend", "nggallery")}</p>
					</label>
				</Fragment>
			)}
		</fieldset>
	);
}

export default Template;
