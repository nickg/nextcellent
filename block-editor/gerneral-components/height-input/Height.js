import { __ } from "@wordpress/i18n";

// Load external dependency.
import "./height.scss";

/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function Height({ value, onChange, ...props }) {
	// Unique ID for the input.
	const inputId = `nextcellent-image-height`;

	// Function to handle the onChange event.
	const onChangeValue = (event) => {
		onChange(event.target.value);
	};

	// Return the fieldset.
	return (
		<div className="nextcellent-image-height">
			{/* Label for the input. */}
			<label htmlFor={inputId}>{__("Height of image", "nggallery")}</label>

			{/* Input field. */}
			<input
				id={inputId}
				type="number"
				min="0"
				step="1"
				value={value}
				onChange={onChangeValue}
			/>
		</div>
	);
}

export default Height;
