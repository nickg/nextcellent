import { __ } from "@wordpress/i18n";

// Load external dependency.
import "./linkInput.scss";

/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function LinkInput({ value, onChange, ...props }) {
	// Unique ID for the input.
	const inputId = `nextcellent-image-link`;

	// Function to handle the onChange event.
	const onChangeValue = (event) => {
		onChange(event.target.value);
	};

	// Return the fieldset.
	return (
		<div className="nextcellent-image-link">
			{/* Label for the input. */}
			<label htmlFor={inputId}>{__("Link", "nggallery")}</label>

			{/* Input field. */}
			<input
				id={inputId}
				pattern="((https?:\/\/)?[^\s.]+\.[\w][^\s]+)"
				value={value}
				onChange={onChangeValue}
				title={__("Http link", "nggallery")}
			/>
		</div>
	);
}

export default LinkInput;
