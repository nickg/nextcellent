import {
	render, //test renderer
	cleanup, //resets the JSDOM
	fireEvent //fires events on nodes
 } from "@testing-library/react";
import NumberOfImages from '../../../block-editor/gerneral-components/numberOfImages/NumberOfImages'

describe("Number of images component", () => {
	afterEach(cleanup); //reset JSDOM after each test

	//It handles having no saved attribute
	it("matches snapshot", () => {
		expect(
		  render(
			<NumberOfImages
				value={""}
				onChange ={jest.fn()}
			/>
		  )
		).toMatchSnapshot();
	});

	it("calls the onChange function", () => {
		//mock function to test with
		const onChange = jest.fn();
		//Render component and get back getByLabelText()
		const {getByLabelText} = render(
		  <NumberOfImages
			onChange={onChange}
			value={""}
		  />
		);
		//Get the input by label text
		const input = getByLabelText('Number of images');

		fireEvent.input(input, {
		  target: { value: '0' }
		});

		expect(onChange).toHaveBeenCalledTimes(1);
	})

	it("passes the right value to onChange", () => {
		const onChange = jest.fn();
		const {getByLabelText} = render(
		  <NumberOfImages
			onChange={onChange}
			value={""}
		  />
		);
		const input = getByLabelText('Number of images');
		//Fire a change event on the input
		fireEvent.change(input, {
		  target: { value: 0 }
		});
		//Was the new value -- not event object -- sent?
		expect(onChange).toHaveBeenCalledWith("0");
	  });

	  it("does not accept text inputs", () => {
		const onChange = jest.fn();
		const {getByLabelText} = render(
		  <NumberOfImages
			onChange={onChange}
			value={""}
		  />
		);
		const input = getByLabelText('Number of images');

		fireEvent.change(input, {
		  target: { value: "Test" }
		});

		expect(onChange).not.toHaveBeenCalled();
	  })

	  it("accepts numbers as inputs", () => {
		const onChange = jest.fn();
		const {getByLabelText} = render(
		  <NumberOfImages
			onChange={onChange}
			value={""}
		  />
		);
		const input = getByLabelText('Number of images');

		fireEvent.change(input, {
		  target: { value: "0" }
		});

		expect(onChange).toHaveBeenCalledWith('0');

		fireEvent.change(input, {
		  target: { value: 0 }
		});

		expect(onChange).toHaveBeenCalledWith('0');
	  })
 })
