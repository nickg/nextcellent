import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

// Load external dependency.
import './numberOfImages.scss'

/**
 *
 * @param setAttribute function to set the number of images attribute
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function NumberOfImages( {
  value,
  onChange,
  ...props
} ) {

   // Unique ID for the id.
  const inputId = `nextcellent-block-number-of-images`;

  // Function to handle the onChange event.
  const onChangeValue = ( event ) => {
    onChange(event.target.value);
  };


  // Return the fieldset.
	return  (
    <div className="nextcellent-number-of-images">
      { /* Label for the input. */ }
      <label htmlFor={ inputId }>
        { __("Number of images", 'nggallery') }
      </label>

      { /* Input field. */ }
      <input
        id={inputId}
        type="number"
        min="0"
        step="1"
        value={ value }
        onChange={ onChangeValue }
      />
      <p>{__("The number of images before pagination is applied. Leave empty or 0 for the default from the settings.", 'nggallery')}</p>
    </div>
	);
};

export default NumberOfImages;
