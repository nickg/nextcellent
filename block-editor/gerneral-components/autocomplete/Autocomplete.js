/**
 * A very simple autocomplete component
 *
 * This is to replace the OOTB Gutenberg Autocomplete component because it is
 * currently broken as of v4.5.1.
 *
 * See Github issue: https://github.com/WordPress/gutenberg/issues/10542
 */

// Load external dependency.
import { useEffect, useState } from "@wordpress/element";
import "./autocomplete.scss";

/**
 * Note: The options array should be an array of objects containing labels; i.e.:
 *   [
 *     { labels: 'first' },
 *     { labels: 'second' }
 *   ]
 *
 * @param label Label for the autocomplete
 * @param onChange function to handle onchange event
 * @param options array of objects containing labels
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function Autocomplete({
	label,
	preSelected,
	fetch = async () => {
		return [];
	},
	onFocus = () => {},
	onChange = () => {},
	onSelect = () => {},
	...props
}) {
	const [value, setValue] = useState(preSelected ? preSelected : "");
	const [listFocus, setListFocus] = useState(0);
	const [listFocusOption, setListFocusOption] = useState(undefined);
	const [open, setOpen] = useState(false);
	const [internalOptions, setOptions] = useState([]);
	const [isLoading, setIsLoading] = useState(false);

	// Unique ID for the input.
	const inputId = `nextcellent-autocomplete-input`;

	/**
	 * Effect executed on load of the component and change of open to reset list focus start
	 */
	useEffect(() => {
		if (open) {
			setListFocus(0);
		}
	}, [open]);

	const onClick = async (event) => {
		setOpen(true);
		setIsLoading(true);

		const json = await fetch("");
		setOptions(json);

		if (json.length > 0) {
			setIsLoading(false);
		} else {
			setOpen(false);
			setIsLoading(false);
		}
	};

	/**
	 * Function to handle the onChange event.
	 *
	 * @param {Event} event
	 */
	const onChangeValue = async (event) => {
		setValue(event.target.value);

		setOpen(true);
		setIsLoading(true);

		const json = await fetch(value);
		setOptions(json);

		if (json.length > 0) {
			setIsLoading(false);
		} else {
			setOpen(false);
			setIsLoading(false);
		}

		onChange(event.target.value);
	};

	/**
	 * Function to handle the selection of an option
	 *
	 * @param {Event} event
	 */
	const optionSelect = (event) => {
		event.stopPropagation();
		event.preventDefault();

		const option = internalOptions[event.target.dataset.option];

		setValue(option.label);
		setOpen(false);
		onSelect(option);
	};

	/**
	 * Method that has common funtionality for the arrow key handling
	 *
	 * @param {[HTMLLIElement]} children
	 * @param {string} key
	 */
	const handleArrowKey = (children, key) => {
		const target = children[listFocus];

		target.classList.add("focus");

		setListFocusOption(internalOptions[listFocus]);
	};

	/**
	 * Method to handle enter and arrow keys
	 *
	 * @param {Event} event
	 */
	const handleKeys = (event) => {
		const key = ["ArrowDown", "ArrowUp", "Enter"];

		if (key.includes(event.key)) {
			event.stopPropagation();
			event.preventDefault();

			const list = document.getElementsByClassName(
				"nextcellent-autocomplete-options"
			)[0];
			const children = list.childNodes;

			if (event.key === "ArrowDown" && list && list.childElementCount > 0) {
				if (listFocus !== 0) {
					const targetBefore = children[listFocus - 1];
					targetBefore.classList.remove("focus");
				} else if (listFocus === 0) {
					const targetBefore = children[list.childElementCount - 1];
					targetBefore.classList.remove("focus");
				}

				handleArrowKey(children, event.key);

				if (listFocus < list.childElementCount - 1) {
					setListFocus(listFocus + 1);
				} else {
					setListFocus(0);
				}
			}

			if (event.key === "ArrowUp" && list && list.childElementCount > 0) {
				setListFocus(list.childElementCount - 1);

				if (listFocus !== list.childElementCount - 1) {
					const targetBefore = children[listFocus + 1];
					targetBefore.classList.remove("focus");
				} else if (listFocus === list.childElementCount - 1) {
					const targetBefore = children[0];
					targetBefore.classList.remove("focus");
				}

				handleArrowKey(children, event.key);

				if (listFocus - 1 > 0) {
					setListFocus(listFocus - 1);
				} else {
					setListFocus(list.childElementCount - 1);
				}
			}

			if (event.key === "Enter") {
				if (listFocusOption) {
					setValue(listFocusOption.label);
					onSelect(listFocusOption);
				}
				setOpen(false);
			}
		}
	};

	// Return the autocomplete.
	return (
		<div className="nextcellent-autocomplete-content">
			{/* Label for the autocomplete. */}
			<label htmlFor={inputId}>{label}</label>

			{/* Input field. */}
			<input
				id={inputId}
				role="combobox"
				aria-autocomplete="list"
				aria-expanded="true"
				aria-owns="nextcellent-autocomplete-option-popup"
				type="text"
				list={inputId}
				value={value}
				onClick={onClick}
				onFocus={onFocus}
				onChange={onChangeValue}
				onKeyDown={handleKeys}
			/>

			{/* List of all of the autocomplete options. */}
			{open && (
				<ul
					aria-live="polite"
					id="nextcellent-autocomplete-option-popup"
					className="nextcellent-autocomplete-options"
				>
					{isLoading && internalOptions.length <= 0 && (
						<li className="loading" />
					)}
					{!isLoading &&
						internalOptions?.map((option, index) => (
							<li
								id={`nextcellent-autocomplete-option-${index}`}
								tabIndex="-1"
								className="option"
								onClick={optionSelect}
								key={index}
								data-option={index}
							>
								{option.label}
							</li>
						))}
				</ul>
			)}
		</div>
	);
}

export default Autocomplete;
