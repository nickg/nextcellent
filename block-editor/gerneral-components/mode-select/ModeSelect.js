import { __ } from "@wordpress/i18n";
import "./mode.scss";

/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function ModeSelect({ type = "img", value, onChange, ...props }) {
	// Unique ID for the input.
	const inputId = `nextcellent-image-mode-select`;

	// Function to handle the onChange event.
	const onChangeValue = (event) => {
		onChange(event.target.value);
	};

	// Return the autocomplete.
	return (
		<div className="nextcellent-image-mode-select">
			{/* Label for the input. */}
			{type == "img" && (
				<label htmlFor={inputId}>{__("Effect", "nggallery")}</label>
			)}
			{type == "recent" && (
				<label htmlFor={inputId}>{__("Sort the images", "nggallery")}</label>
			)}
			{/* Select field. */}
			{type == "img" && (
				<select
					name="modes"
					id={inputId}
					onChange={onChangeValue}
					value={value}
				>
					<option value="">{__("No effect", "nggallery")}</option>
					<option value="watermark">{__("Watermark", "nggallery")}</option>
					<option value="web20">{__("Web 2.0", "nggallery")}</option>
				</select>
			)}
			{(type == "recent" || type == "random") && (
				<select
					name="modes"
					id={inputId}
					onChange={onChangeValue}
					value={value}
				>
					<option value="">{__("Upload order", "nggallery")}</option>
					<option value="date">{__("Date taken", "nggallery")}</option>
					<option value="sort">{__("User defined", "nggallery")}</option>
				</select>
			)}
		</div>
	);
}

export default ModeSelect;
