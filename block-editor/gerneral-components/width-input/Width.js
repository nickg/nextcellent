import { __ } from "@wordpress/i18n";

// Load external dependency.
import "./width.scss";

/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function Width({ value, onChange, ...props }) {
	// Unique ID for the input.
	const inputId = `nextcellent-image-width`;

	// Function to handle the onChange event.
	const onChangeValue = (event) => {
		onChange(event.target.value);
	};

	// Return the fieldset.
	return (
		<div className="nextcellent-image-width">
			{/* Label for the input. */}
			<label htmlFor={inputId}>{__("Width of image", "nggallery")}</label>

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

export default Width;
