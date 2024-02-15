import { __ } from "@wordpress/i18n";
import "./floatSelect.scss";

/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function FloatSelect({ value, onChange, ...props }) {
	// Unique ID for the input.
	const inputId = `nextcellent-image-float-select`;

	// Function to handle the onChange event.
	const onChangeValue = (event) => {
		onChange(event.target.value);
	};

	// Return the fieldset.
	return (
		<div className="nextcellent-image-float-select">
			{/* Label for the input. */}
			<label htmlFor={inputId}>{__("Float", "nggallery")}</label>
			{/* Select field. */}
			<select name="modes" id={inputId} onChange={onChangeValue} value={value}>
				<option value="">{__("No Float", "nggallery")}</option>
				<option value="left">{__("Left", "nggallery")}</option>
				<option value="center">{__("Center", "nggallery")}</option>
				<option value="right">{__("Right", "nggallery")}</option>
			</select>
		</div>
	);
}

export default FloatSelect;
