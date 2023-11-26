import { __ } from "@wordpress/i18n";

// Load external dependency.
import "./descriptionInput.scss";

/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function DescriptionInput({ value, onChange, ...props }) {
	// Unique ID for the input.
	const inputId = `nextcellent-image-description`;

	// Function to handle the onChange event.
	const onChangeValue = (event) => {
		onChange(event.target.value);
	};

	// Return the fieldset.
	return (
		<div className="nextcellent-image-description">
			{/* Label for the input. */}
			<label htmlFor={inputId}>{__("Description", "nggallery")}</label>

			{/* Input field. */}
			<input id={inputId} value={value} onChange={onChangeValue} />
		</div>
	);
}

export default DescriptionInput;
