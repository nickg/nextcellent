import {
	render, //test renderer
	cleanup, //resets the JSDOM
	fireEvent,
	waitFor,
	userEvent,
	act, //fires events on nodes
 } from "@testing-library/react";
import { fetchGallerys } from "../../../block-editor/api";
 import Autocomplete from '../../../block-editor/gerneral-components/autocomplete/Autocomplete'

describe("Autocomplete component", () => {
	global.nggData = {siteUrl: 'test'};

	global.fetch = jest.fn(() => Promise.resolve({
		json: () => Promise.resolve([{label: "test1"}, {label: "test2"}]),
	  }))

	beforeEach(() => {
		fetch.mockClear();
	})

	afterEach(cleanup); //reset JSDOM after each test

	//It handles having no saved attribute
	it("matches snapshot", () => {
		expect(
		  render(
			<Autocomplete
				label="Test"
				value={""}
				onChange ={jest.fn()}
				onFocus ={jest.fn()}
				onSelect ={jest.fn()}
				fetch = {async () => {return []}}
			/>
		  )
		).toMatchSnapshot();
	});

	it("calls the onChange function", async () => {
		//mock function to test with
		const onChange = jest.fn();
		//Render component and get back getByLabelText()
		const {getByLabelText} = render(
			<Autocomplete
			label="Test"
			value={""}
			onChange ={onChange}
			onFocus ={jest.fn()}
			onSelect ={jest.fn()}
			fetch = {async () => {return []}}
		/>
		);
		//Get the input by label text
		const input = getByLabelText('Test');

			fireEvent.input(input, {
				target: { value: 'Test' }
			  });

		await waitFor(() => {expect(onChange).toHaveBeenCalled()});
	})

	it("gets the value from the on change event", async () => {
		//mock function to test with
		const onChange = jest.fn();
		//Render component and get back getByLabelText()
		const {getByLabelText} = render(
			<Autocomplete
			label="Test"
			value={""}
			onChange ={onChange}
			onFocus ={jest.fn()}
			onSelect ={jest.fn()}
			fetch = {async () => {return []}}
		/>
		);
		//Get the input by label text
		const input = getByLabelText('Test');

			fireEvent.input(input, {
				target: { value: 'Test' }
			  });

		await waitFor(() => {expect(onChange).toHaveBeenCalledWith('Test')});
	})
	it("gets the value from the on change event", async () => {
		//mock function to test with
		const onChange = jest.fn();
		//Render component and get back getByLabelText()
		const {getByLabelText} = render(
			<Autocomplete
			label="Test"
			value={""}
			onChange ={onChange}
			onFocus ={jest.fn()}
			onSelect ={jest.fn()}
			fetch = {async () => {return []}}
		/>
		);
		//Get the input by label text
		const input = getByLabelText('Test');

			fireEvent.input(input, {
				target: { value: 'Test' }
			  });

		await waitFor(() => {expect(onChange).toHaveBeenCalledWith('Test')});
	})

	it("calls the fetch method", async () => {
		//mock function to test with
		const onChange = jest.fn();
		const fetch = jest.fn()
		fetch.mockReturnValue([{label: "Option1"}, {label: "Option2"}])
		//Render component and get back getByLabelText()
		const {getByLabelText} = render(
			<Autocomplete
			label="Test"
			value={""}
			onChange ={onChange}
			onFocus ={jest.fn()}
			onSelect ={jest.fn()}
			fetch = {fetch}
		/>
		);

		//Get the input by label text
		const input = getByLabelText('Test');

			fireEvent.input(input, {
				target: { value: 'Test' }
			  });

		await waitFor(() => {expect(fetch).toHaveBeenCalled()});
	})
})
