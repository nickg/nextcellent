import { __ } from "@wordpress/i18n";

// Load external dependency.
import "./CustomTemplateInput.scss";

/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function CustomTemplateInput({ value, onChange, ...props }) {
	// Unique ID for the input.
	const inputId = `nextcellent-custom-template`;

	// Function to handle the onChange event.
	const onChangeValue = (event) => {
		onChange(event.target.value);
	};

	// Return the fieldset.
	return (
		<div className="nextcellent-custom-template">
			{/* Label for the input. */}
			<label htmlFor={inputId}>{__("Template name", "nggallery")}</label>

			{/* Input field. */}
			<input id={inputId} value={value} onChange={onChangeValue} />
		</div>
	);
}

export default CustomTemplateInput;
